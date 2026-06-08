<?php

use App\Exceptions\BusinessException;
use App\Http\Middleware\AuthenticateSessionToken;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RoleMiddleware;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.session' => AuthenticateSessionToken::class,
            'role' => RoleMiddleware::class,
            'active.user' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (BusinessException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $exception->getMessage(),
                    $exception->getErrors(),
                    $exception->getStatusCode()
                );
            }

            return null;
        });

        $exceptions->render(function (ValidationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'Dữ liệu không hợp lệ.',
                    $exception->errors(),
                    422
                );
            }

            return null;
        });

        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'Unauthenticated.',
                    [],
                    401
                );
            }

            return null;
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'Bạn không có quyền thực hiện thao tác này.',
                    [],
                    403
                );
            }

            return null;
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'Không tìm thấy dữ liệu.',
                    [],
                    404
                );
            }

            return null;
        });

        $exceptions->render(function (Throwable $exception, $request) {
            if ($request->is('api/*')) {
                report($exception);

                return ApiResponse::error(
                    'Có lỗi xảy ra, vui lòng thử lại sau.',
                    [],
                    500
                );
            }

            return null;
        });
    })
    ->create();