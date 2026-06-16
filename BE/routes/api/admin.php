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

        Route::get('/categories', [AdminController::class, 'categories']);
        Route::post('/categories', [AdminController::class, 'storeCategory']);
        
        Route::get('/categories/{id}', [AdminController::class, 'showCategory'])
            ->where('id', '[0-9]+');
        
        Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])
            ->where('id', '[0-9]+');
        
        Route::patch('/categories/{id}', [AdminController::class, 'updateCategory'])
            ->where('id', '[0-9]+');
        
        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])
            ->where('id', '[0-9]+');

        Route::get('/courses', [AdminController::class, 'courses']);
        Route::get('/courses/{id}', [AdminController::class, 'showCourse'])
            ->where('id', '[0-9]+');
        Route::patch('/courses/{id}', [AdminController::class, 'updateCourse'])
            ->where('id', '[0-9]+');

        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users', [AdminController::class, 'storeUser']);

        Route::get('/users/{id}', [AdminController::class, 'showUser'])
            ->where('id', '[0-9]+');

        Route::put('/users/{id}', [AdminController::class, 'updateUser'])
            ->where('id', '[0-9]+');

        Route::patch('/users/{id}', [AdminController::class, 'updateUser'])
            ->where('id', '[0-9]+');

        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])
            ->where('id', '[0-9]+');

        Route::get('/roles', [AdminController::class, 'roles']);
        Route::post('/roles', [AdminController::class, 'roles']);

        Route::get('/roles/{id}', [AdminController::class, 'roles'])
            ->where('id', '[0-9]+');

        Route::put('/roles/{id}', [AdminController::class, 'roles'])
            ->where('id', '[0-9]+');

        Route::patch('/roles/{id}', [AdminController::class, 'roles'])
            ->where('id', '[0-9]+');

        Route::delete('/roles/{id}', [AdminController::class, 'roles'])
            ->where('id', '[0-9]+');
    });