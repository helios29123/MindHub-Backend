<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                message: 'Unauthenticated.',
                status: 401
            );
        }

        if (! in_array($user->role, $roles, true)) {
            return ApiResponse::error(
                message: 'Bạn không có quyền thực hiện thao tác này.',
                status: 403
            );
        }

        return $next($request);
    }
}