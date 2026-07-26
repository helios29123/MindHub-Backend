<?php

use App\Http\Controllers\InstructorProfileController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user', 'role:learner,instructor,admin'])
    ->prefix('users')
    ->group(function (): void {
        Route::get('me', [UserProfileController::class, 'me']);
        Route::patch('me', [UserProfileController::class, 'updateMe']);
        Route::patch('me/password', [UserProfileController::class, 'changePassword']);
    });

Route::middleware(['auth.session', 'active.user', 'role:learner,instructor,admin'])
    ->prefix('account')
    ->group(function (): void {
        Route::get('profile', [InstructorProfileController::class, 'show']);
        Route::patch('profile', [InstructorProfileController::class, 'update']);
        Route::post('avatar', [InstructorProfileController::class, 'uploadAvatar']);
        Route::patch('avatar/preset', [InstructorProfileController::class, 'selectAvatarPreset']);
        Route::delete('avatar', [InstructorProfileController::class, 'deleteAvatar']);
    });