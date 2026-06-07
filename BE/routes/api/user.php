<?php

use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'role:learner,instructor,admin'])
    ->prefix('users')
    ->group(function (): void {
        Route::get('/me', [UserProfileController::class, 'me']);
        Route::patch('/me', [UserProfileController::class, 'updateMe']);
    });