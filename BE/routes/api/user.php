<?php

use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session'])
    ->prefix('users')
    ->group(function (): void {
        Route::get('/me', [UserProfileController::class, 'me']);
        Route::patch('/me', [UserProfileController::class, 'updateMe']);
        Route::patch('/me/password', [UserProfileController::class, 'changePassword']);
    });