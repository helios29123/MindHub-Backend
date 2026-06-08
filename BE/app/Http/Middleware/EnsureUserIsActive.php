<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                message: 'Unauthenticated.',
                status: 401
            );
        }

        if ($user->status !== 'active' || (bool) $user->locked === true) {
            return ApiResponse::error(
                message: 'Tài khoản đang bị khóa hoặc chưa hoạt động.',
                status: 403
            );
        }

        return $next($request);
    }
}
