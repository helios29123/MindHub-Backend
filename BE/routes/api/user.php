<?php

use App\Http\Controllers\InstructorProfileController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user', 'role:learner,instructor,admin'])
    ->prefix('users')
    ->group(function (): void {
        Route::get('me', [UserProfileController::class, 'me']);
        Route::patch('me', [UserProfileController::class, 'updateMe']);
        Route::post('me/avatar', [UserProfileController::class, 'uploadAvatar']);
        Route::patch('me/avatar/preset', [UserProfileController::class, 'selectAvatarPreset']);
        Route::delete('me/avatar', [UserProfileController::class, 'deleteAvatar']);
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

Route::get('/notifications', [UserNotificationController::class, 'index']);
Route::patch('/notifications/read-all', [UserNotificationController::class, 'readAll']);
Route::delete('/notifications/clear-all', [UserNotificationController::class, 'clearAll']);
Route::patch('/notifications/{id}/read', [UserNotificationController::class, 'read'])->where('id', '[0-9]+');
Route::delete('/notifications/{id}', [UserNotificationController::class, 'destroy'])->where('id', '[0-9]+');
