<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('auth.verify-email');

    Route::post('verify-email/resend', [AuthController::class, 'resendVerifyEmail']);

    Route::post('google', [AuthController::class, 'googleLogin']);

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth.session');
});

Route::middleware(['auth.session', 'role:admin'])->get('/admin/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Admin access granted',
        'data' => null,
    ]);
});

Route::middleware(['auth.session', 'role:learner'])->get('/learner/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Learner access granted',
        'data' => null,
    ]);
});