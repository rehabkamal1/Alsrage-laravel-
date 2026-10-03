<?php

namespace App\Http\Middleware;

use App\Support\PermissionAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotHidden
{
    public function handle(Request $request, Closure $next, string $restriction): Response
    {
        if (PermissionAccess::isHiddenFor($request->user(), $restriction)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية الوصول إلى هذه البيانات.',
            ], 403);
        }

        return $next($request);
    }
}
