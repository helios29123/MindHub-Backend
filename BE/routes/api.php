<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('google', [AuthController::class, 'googleLogin']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth.session')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
