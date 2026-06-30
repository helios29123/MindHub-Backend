<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseApprovalController;
use App\Http\Controllers\AdminCreditPackageController;
use App\Http\Controllers\AdminInstructorCreditController;
use App\Http\Controllers\AdminModerationController;
use App\Http\Controllers\InstructorUpgradeController;
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */
        Route::get('/orders', [AdminController::class, 'orders']);

        /*
        |--------------------------------------------------------------------------
        | Course moderation / review
        |--------------------------------------------------------------------------
        */
        Route::get('/course-reviews', [AdminModerationController::class, 'pendingCourses']);

        /*
         * Rule mới:
         * Admin approve/publish thành công thì mới trừ 1 lượt tạo khóa học.
         * Vì vậy route approve/reject chuyển sang AdminCourseApprovalController.
         */
        Route::patch('/courses/{courseId}/approve', [AdminCourseApprovalController::class, 'approve'])
            ->whereNumber('courseId');

        Route::patch('/courses/{courseId}/reject', [AdminCourseApprovalController::class, 'reject'])
            ->whereNumber('courseId');

        Route::patch('/moderation/items/{id}', [AdminModerationController::class, 'moderateItem'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Credit packages - Admin tạo/sửa/xóa mềm gói lượt
        |--------------------------------------------------------------------------
        */
        Route::get('/credit-packages', [AdminCreditPackageController::class, 'index']);

        Route::post('/credit-packages', [AdminCreditPackageController::class, 'store']);

        Route::patch('/credit-packages/{packageId}', [AdminCreditPackageController::class, 'update'])
            ->whereNumber('packageId');

        Route::delete('/credit-packages/{packageId}', [AdminCreditPackageController::class, 'destroy'])
            ->whereNumber('packageId');

        /*
        |--------------------------------------------------------------------------
        | Instructor credits - Admin xem/cộng/trừ lượt thủ công
        |--------------------------------------------------------------------------
        */
        Route::get('/instructors/{instructorId}/credits', [AdminInstructorCreditController::class, 'show'])
            ->whereNumber('instructorId');

        Route::get('/instructors/{instructorId}/credit-transactions', [AdminInstructorCreditController::class, 'transactions'])
            ->whereNumber('instructorId');

        Route::post('/instructors/{instructorId}/credits/adjust', [AdminInstructorCreditController::class, 'adjust'])
            ->whereNumber('instructorId');

        /*
        |--------------------------------------------------------------------------
        | Marketing / Campaigns / Banners
        |--------------------------------------------------------------------------
        */
        Route::match(['get', 'post'], '/campaigns', [MarketingController::class, 'banners']);

        Route::match(['get', 'put', 'patch', 'delete'], '/campaigns/{id}', [MarketingController::class, 'banners'])
            ->whereNumber('id');

        Route::match(['get', 'post'], '/banners', [AdminController::class, 'banners']);

        Route::match(['get', 'put', 'patch', 'delete'], '/banners/{id}', [AdminController::class, 'banners'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        Route::get('/categories', [AdminController::class, 'categories']);

        Route::post('/categories', [AdminController::class, 'storeCategory']);

        Route::get('/categories/{id}', [AdminController::class, 'showCategory'])
            ->whereNumber('id');

        Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])
            ->whereNumber('id');

        Route::patch('/categories/{id}', [AdminController::class, 'updateCategory'])
            ->whereNumber('id');

        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Courses
        |--------------------------------------------------------------------------
        */
        Route::get('/courses', [AdminController::class, 'courses']);

        Route::get('/courses/{id}', [AdminController::class, 'showCourse'])
            ->whereNumber('id');

        Route::patch('/courses/{id}', [AdminController::class, 'updateCourse'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [AdminController::class, 'users']);

        Route::post('/users', [AdminController::class, 'storeUser']);

        Route::get('/users/{id}', [AdminController::class, 'showUser'])
            ->whereNumber('id');

        Route::put('/users/{id}', [AdminController::class, 'updateUser'])
            ->whereNumber('id');

        Route::patch('/users/{id}', [AdminController::class, 'updateUser'])
            ->whereNumber('id');

        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        Route::get('/roles', [AdminController::class, 'roles']);

        Route::post('/roles', [AdminController::class, 'roles']);

        Route::get('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        Route::put('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        Route::patch('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        Route::delete('/roles/{id}', [AdminController::class, 'roles'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Instructor upgrade requests
        |--------------------------------------------------------------------------
        */
        Route::get('/instructor-upgrade-requests', [InstructorUpgradeController::class, 'adminIndex']);

        Route::get('/instructor-upgrade-requests/{userId}', [InstructorUpgradeController::class, 'adminShow'])
            ->whereNumber('userId');

        Route::patch('/instructor-upgrade-requests/{userId}/approve', [InstructorUpgradeController::class, 'approve'])
            ->whereNumber('userId');

        Route::patch('/instructor-upgrade-requests/{userId}/reject', [InstructorUpgradeController::class, 'reject'])
            ->whereNumber('userId');
    });
