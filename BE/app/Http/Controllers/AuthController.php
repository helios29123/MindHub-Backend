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
use App\Models\Session;
use App\Services\Auth\AuthService;
use App\Services\Auth\GoogleTokenVerifier;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly GoogleTokenVerifier $googleTokenVerifier
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
                'otp_code' => $result['otp_code'] ?? null,
                'note' => $result['note'] ?? null,
            ],
            'Đăng ký giảng viên thành công. Mã OTP xác thực đã được gửi về email đăng ký.',
            201
        );
    }

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        if (! $request->hasValidSignature()) {
            if ($request->wantsJson()) {
                return ApiResponse::error(
                    'Link xác thực email không hợp lệ hoặc đã hết hạn.',
                    [],
                    403
                );
            }
            return redirect("{$frontendUrl}/login?verified=0&error=invalid_signature");
        }

        $user = $this->authService->verifyEmail($id, $hash);

        if ($request->wantsJson()) {
            return ApiResponse::success(
                [
                    'user' => new UserResource($user),
                ],
                'Xác thực email thành công.'
            );
        }

        return redirect("{$frontendUrl}/login?verified=1");
    }

    public function resendVerifyEmail(ResendVerifyEmailRequest $request): JsonResponse
    {
        $result = $this->authService->resendVerifyEmail($request->validated());

        return ApiResponse::success(
            $result,
            'Nếu email tồn tại và chưa xác thực, link xác thực đã được tạo.'
        );
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|string',
            'phone' => 'nullable|string',
            'otp' => 'required|string',
        ]);

        $identifier = $request->input('email') ?: $request->input('phone');
        if (empty($identifier)) {
            return ApiResponse::error('Vui lòng cung cấp email hoặc số điện thoại để xác thực.', [], 422);
        }

        $user = $this->authService->verifyOtp((string) $identifier, (string) $request->input('otp'));

        return ApiResponse::success(
            [
                'user' => new UserResource($user),
            ],
            'Xác thực OTP thành công. Tài khoản đã được kích hoạt.'
        );
    }

    public function resendVerifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'nullable|string',
            'phone' => 'nullable|string',
            'channel' => 'nullable|in:email,sms,phone',
        ]);

        $channel = $request->input('channel', 'email');
        $identifier = ($channel === 'sms' || $channel === 'phone')
            ? ($request->input('phone') ?: $request->input('email'))
            : ($request->input('email') ?: $request->input('phone'));

        if (empty($identifier)) {
            return ApiResponse::error('Vui lòng cung cấp thông tin liên hệ để gửi mã OTP.', [], 422);
        }

        $result = $this->authService->resendVerifyOtp((string) $identifier, $channel);

        $msg = ($channel === 'sms' || $channel === 'phone')
            ? 'Mã OTP xác thực đã được gửi tới số điện thoại của bạn qua tin nhắn SMS.'
            : 'Mã OTP xác thực đã được gửi tới địa chỉ email của bạn.';

        return ApiResponse::success(
            $result,
            $msg
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

    public function googleRedirect(Request $request): JsonResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect', 'http://localhost:8000/auth/google/callback');

        if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
            return ApiResponse::error('Đăng nhập Google chưa được cấu hình trên máy chủ.', [
                'code' => 'GOOGLE_OAUTH_NOT_CONFIGURED',
            ], 503);
        }

        $scope = urlencode('openid email profile');
        $url = "https://accounts.google.com/o/oauth2/v2/auth?client_id={$clientId}&redirect_uri=" . urlencode($redirectUri) . "&response_type=code&scope={$scope}&prompt=select_account";

        return ApiResponse::success([
            'url' => $url,
        ], 'Tạo URL đăng nhập Google thành công.');
    }

    public function googleCallback(Request $request)
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $code = $request->query('code');

        if (! $code) {
            return redirect("{$frontendUrl}/auth/google/callback?status=error&code=google_auth_failed");
        }

        try {
            $clientId = config('services.google.client_id');
            $clientSecret = config('services.google.client_secret');
            $redirectUri = config('services.google.redirect', 'http://localhost:8000/auth/google/callback');

            $tokenResponse = Http::withoutVerifying()->asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if (! $tokenResponse->successful()) {
                \Illuminate\Support\Facades\Log::error('Google OAuth Token Exchange Failed', [
                    'status' => $tokenResponse->status(),
                    'response' => $tokenResponse->json() ?? $tokenResponse->body(),
                    'redirect_uri' => $redirectUri,
                    'client_id' => $clientId,
                ]);
                $errorDetail = $tokenResponse->json()['error_description'] ?? $tokenResponse->json()['error'] ?? 'google_token_exchange_failed';
                return redirect("{$frontendUrl}/auth/google/callback?status=error&error=" . urlencode($errorDetail));
            }

            $tokenData = $tokenResponse->json();
            $idToken = $tokenData['id_token'] ?? null;

            if (! $idToken) {
                \Illuminate\Support\Facades\Log::error('Google OAuth Missing ID Token', ['token_data' => $tokenData]);
                return redirect("{$frontendUrl}/auth/google/callback?status=error&error=" . urlencode('Thiếu id_token từ Google'));
            }

            $googleUser = $this->googleTokenVerifier->verify($idToken);
            $user = $this->authService->handleGoogleUser($googleUser, $request);
            $authPayload = $this->authService->createAuthenticatedSession($user, 'google_oauth', $request);
            $token = $authPayload['access_token'] ?? '';

            return redirect("{$frontendUrl}/auth/google/callback?status=success&token={$token}");
        } catch (\App\Exceptions\BusinessException $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth BusinessException', ['message' => $e->getMessage()]);
            $errorCode = $e->getStatusCode() === 403 ? 'Tài khoản đã bị vô hiệu hóa.' : $e->getMessage();
            return redirect("{$frontendUrl}/auth/google/callback?status=error&error=" . urlencode($errorCode));
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect("{$frontendUrl}/auth/google/callback?status=error&error=" . urlencode($e->getMessage()));
        }
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
        $this->authService->logout($session instanceof Session ? $session : null, $request);

        return ApiResponse::success(
            null,
            'Đăng xuất thành công.'
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = Auth::guard('web')->user() ?: $request->user();

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
