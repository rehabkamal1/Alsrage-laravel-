<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdempotentSubmission
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');
        if (! is_string($key) || ! Str::isUuid($key)) {
            return response()->json([
                'message' => 'مفتاح الإرسال غير صالح. يرجى إعادة المحاولة.',
            ], 428);
        }

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $scopedKey = hash('sha256', implode('|', [
            $user::class,
            $user->getAuthIdentifier(),
            $request->method(),
            $request->path(),
            $key,
        ]));
        $requestHash = $this->requestHash($request);

        try {
            return DB::transaction(function () use ($next, $request, $scopedKey, $requestHash) {
                DB::table('idempotency_requests')->insert([
                    'key_hash' => $scopedKey,
                    'request_hash' => $requestHash,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $response = $next($request);
                if (! $response->isSuccessful()) {
                    DB::table('idempotency_requests')
                        ->where('key_hash', $scopedKey)
                        ->delete();

                    return $response;
                }

                DB::table('idempotency_requests')
                    ->where('key_hash', $scopedKey)
                    ->update([
                        'response_status' => $response->getStatusCode(),
                        'response_body' => $response->getContent(),
                        'response_content_type' => $response->headers->get('Content-Type'),
                        'updated_at' => now(),
                    ]);

                return $response;
            });
        } catch (QueryException $exception) {
            $existing = DB::table('idempotency_requests')
                ->where('key_hash', $scopedKey)
                ->first();

            if (! $existing) {
                throw $exception;
            }

            if (! hash_equals($existing->request_hash, $requestHash)) {
                return response()->json([
                    'message' => 'تغيّرت بيانات الإرسال. يرجى إعادة الحفظ.',
                ], 409);
            }

            if ($existing->response_status === null) {
                return response()->json([
                    'message' => 'ما زال الطلب قيد المعالجة. يرجى الانتظار.',
                ], 409);
            }

            return response(
                $existing->response_body ?? '',
                $existing->response_status,
                $existing->response_content_type
                    ? ['Content-Type' => $existing->response_content_type]
                    : [],
            );
        }
    }

    private function requestHash(Request $request): string
    {
        $normalize = function (mixed $value) use (&$normalize): mixed {
            if ($value instanceof UploadedFile) {
                $path = $value->getRealPath();

                return [
                    'file' => $value->getClientOriginalName(),
                    'size' => $value->getSize(),
                    'sha256' => $path ? hash_file('sha256', $path) : null,
                ];
            }

            if (is_array($value)) {
                ksort($value);
                foreach ($value as $key => $nestedValue) {
                    $value[$key] = $normalize($nestedValue);
                }
            }

            return $value;
        };

        return hash('sha256', json_encode(
            $normalize($request->all()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));
    }
}
