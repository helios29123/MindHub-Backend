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

        if (! $user) {
            return ApiResponse::error(
                'Unauthenticated.',
                [],
                401
            );
        }

        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $role) {
                $trimmed = trim($role);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                    if ($trimmed === 'learner') {
                        $allowedRoles[] = 'student';
                    } elseif ($trimmed === 'student') {
                        $allowedRoles[] = 'learner';
                    }
                }
            }
        }

        if (! in_array((string) $user->role, $allowedRoles, true)) {
            return ApiResponse::error(
                'Bạn không có quyền thực hiện thao tác này.',
                [],
                403
            );
        }

        return $next($request);
    }
}