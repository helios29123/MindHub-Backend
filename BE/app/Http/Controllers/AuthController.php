<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\GoogleLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterInstructorRequest;
use App\Http\Requests\Auth\RegisterLearnerRequest;
use App\Http\Requests\Auth\ResendVerifyEmailRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\User\UserResource;
use App\Models\AuthSession;
use App\Services\Auth\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function register(RegisterLearnerRequest $request): JsonResponse
    {
        return $this->registerLearner($request);
    }

    public function registerLearner(RegisterLearnerRequest $request): JsonResponse
    {
        $result = $this->authService->registerLearner($request->validated());

        return ApiResponse::success(
            [
                'user' => new UserResource($result['user']),
                'verify_url' => $result['verify_url'] ?? null,
            ],
            'Đăng ký học viên thành công. Vui lòng xác thực email để kích hoạt tài khoản.',
            201
        );
    }

    public function registerInstructor(RegisterInstructorRequest $request): JsonResponse
    {
        $result = $this->authService->registerInstructor($request->validated());

        return ApiResponse::success(
            [
                'user' => new UserResource($result['user']),
                'verify_url' => $result['verify_url'] ?? null,
                'note' => $result['note'] ?? null,
            ],
            'Đăng ký giảng viên thành công. Vui lòng xác thực email và chờ admin duyệt hồ sơ.',
            201
        );
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
            [
                'user' => new UserResource($user),
            ],
            'Xác thực email thành công.'
        );
    }

    public function resendVerifyEmail(ResendVerifyEmailRequest $request): JsonResponse
    {
        $result = $this->authService->resendVerifyEmail($request->validated());

        return ApiResponse::success(
            $result,
            'Nếu email tồn tại và chưa xác thực, link xác thực đã được tạo.'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $authResult = $this->authService->login(
            $request->validated(),
            $request
        );

        return ApiResponse::success(
            new AuthResource($authResult),
            'Đăng nhập thành công.'
        );
    }

    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $authResult = $this->authService->googleLogin(
            $request->validated(),
            $request
        );

        return ApiResponse::success(
            new AuthResource($authResult),
            'Đăng nhập Google thành công.'
        );
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->forgotPassword($request->validated());

        return ApiResponse::success(
            $result,
            'Nếu email tồn tại, hướng dẫn đặt lại mật khẩu đã được gửi.'
        );
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return ApiResponse::success(
            null,
            'Đặt lại mật khẩu thành công.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $session = $request->attributes->get('auth_session');

        if (! $session instanceof AuthSession) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $this->authService->logout($session);

        return ApiResponse::success(
            null,
            'Đăng xuất thành công.'
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        return ApiResponse::success(
            [
                'user' => new UserResource($user),
            ],
            'Lấy thông tin người dùng thành công.'
        );
    }
}
