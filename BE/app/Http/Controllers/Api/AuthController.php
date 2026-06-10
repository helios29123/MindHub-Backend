<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BusinessException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\ResendVerifyEmailRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\User\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
{
    $result = $this->authService->register($request->validated());

    return ApiResponse::success(
        'Đăng ký tài khoản thành công. Vui lòng xác thực email.',
        [
            'user' => new UserResource($result['user']),
            'verify_url' => $result['verify_url'] ?? null,
        ],
        201
    );
}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $authData = $this->authService->login($request->validated(), $request);

            return ApiResponse::success(
                'Đăng nhập thành công.',
                new AuthResource($authData)
            );
        } catch (BusinessException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->getErrors(),
                $exception->getStatusCode()
            );
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->forgotPassword($request->validated());

            return ApiResponse::success(
                'Nếu email tồn tại, hướng dẫn đặt lại mật khẩu đã được gửi.',
                $result
            );
        } catch (BusinessException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->getErrors(),
                $exception->getStatusCode()
            );
        }
    }
    public function resetPassword(ResetPasswordRequest $request) {
         try {
            $this->authService->resetPassword($request->validated());

            return ApiResponse::success('Đặt lại mật khẩu thành công.');
        } catch (BusinessException $exception) {
            return ApiResponse::error(
                $exception->getMessage(),
                $exception->getErrors(),
                $exception->getStatusCode()
            );
        }
    }
    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
{
    if (! $request->hasValidSignature()) {
        return ApiResponse::error(
            'Link xác thực email không hợp lệ hoặc đã hết hạn.',
            [],
            403
        );
    }

    $user = $this->authService->verifyEmail($id, $hash);

    return ApiResponse::success(
        'Xác thực email thành công.',
        [
            'user' => new UserResource($user),
        ]
    );
}

public function resendVerifyEmail(ResendVerifyEmailRequest $request): JsonResponse
{
    $result = $this->authService->resendVerifyEmail($request->validated());

    return ApiResponse::success(
        'Nếu email tồn tại và chưa xác thực, link xác thực đã được tạo.',
        $result
    );
}


}
