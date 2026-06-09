<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Nếu đã code xong googleLogin thì mới mở route này
// Route::post('/google-login', [AuthController::class, 'googleLogin']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);


Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
