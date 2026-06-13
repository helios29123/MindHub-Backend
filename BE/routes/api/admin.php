<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminModerationController;
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::get('/course-reviews', [AdminModerationController::class, 'pendingCourses']);
        Route::patch('/courses/{id}/approve', [AdminModerationController::class, 'approveCourse'])
            ->where('id', '[0-9]+');
        Route::patch('/courses/{id}/reject', [AdminModerationController::class, 'rejectCourse'])
            ->where('id', '[0-9]+');
        Route::patch('/moderation/items/{id}', [AdminModerationController::class, 'moderateItem'])
            ->where('id', '[0-9]+');
        Route::match(['get', 'post'], '/campaigns', [MarketingController::class, 'banners']);
        Route::match(['get', 'put', 'patch', 'delete'], '/campaigns/{id}', [MarketingController::class, 'banners'])
            ->where('id', '[0-9]+');
        Route::match(['get', 'post'], '/banners', [AdminController::class, 'banners']);
        Route::match(['get', 'put', 'patch', 'delete'], '/banners/{id}', [AdminController::class, 'banners'])
            ->where('id', '[0-9]+');
    });