<?php

namespace Tests\Feature;

use App\Http\Middleware\IdempotentSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class IdempotentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_key_replays_the_first_successful_response_without_reprocessing(): void
    {
        $user = User::factory()->create();
        $key = (string) Str::uuid();
        $calls = 0;
        $middleware = app(IdempotentSubmission::class);
        $request = $this->makeRequest($user, $key, ['amount' => '125.00']);

        $firstResponse = $middleware->handle($request, function () use (&$calls) {
            $calls++;

            return response()->json(['id' => 42], 201);
        });
        $duplicateResponse = $middleware->handle(
            $this->makeRequest($user, $key, ['amount' => '125.00']),
            function () use (&$calls) {
                $calls++;

                return response()->json(['id' => 43], 201);
            },
        );

        $this->assertSame(1, $calls);
        $this->assertSame(201, $duplicateResponse->getStatusCode());
        $this->assertSame($firstResponse->getContent(), $duplicateResponse->getContent());
    }

    public function test_reusing_a_key_with_different_data_is_rejected(): void
    {
        $user = User::factory()->create();
        $key = (string) Str::uuid();
        $middleware = app(IdempotentSubmission::class);
        $middleware->handle(
            $this->makeRequest($user, $key, ['amount' => '125.00']),
            fn () => response()->json(['id' => 42], 201),
        );

        $response = $middleware->handle(
            $this->makeRequest($user, $key, ['amount' => '250.00']),
            fn () => response()->json(['id' => 43], 201),
        );

        $this->assertSame(409, $response->getStatusCode());
    }

    private function makeRequest(User $user, string $key, array $data): Request
    {
        $request = Request::create('/api/orders', 'POST', $data, [], [], [
            'HTTP_IDEMPOTENCY_KEY' => $key,
        ]);
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
