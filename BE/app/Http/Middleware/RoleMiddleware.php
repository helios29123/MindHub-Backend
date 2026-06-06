<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        if (! in_array($user->role, $roles, true)) {
            return ApiResponse::error('Bạn không có quyền thực hiện thao tác này.', [], 403);
        }

        return $next($request);
    }
}
