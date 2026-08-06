<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Form đăng ký học viên
    Route::post('register/learner', [AuthController::class, 'registerLearner']);

    // Form đăng ký giảng viên
    Route::post('register/instructor', [AuthController::class, 'registerInstructor']);

    // Route đăng ký chung
    Route::post('register', [AuthController::class, 'registerLearner']);

    // Xác thực email
    Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('auth.verify-email');

    Route::post('verify-email/resend', [AuthController::class, 'resendVerifyEmail']);

    // Luồng Đăng nhập Email/Password & Google OAuth
    Route::post('login', [AuthController::class, 'login']);
    Route::get('google/redirect', [AuthController::class, 'googleRedirect']);
    Route::get('google/callback', [AuthController::class, 'googleCallback']);
    Route::post('google', [AuthController::class, 'googleLogin']);

    // Quên & Đặt lại mật khẩu
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Session & User Info
    Route::middleware('auth.session')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});
