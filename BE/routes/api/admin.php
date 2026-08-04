<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminFaqController;
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
        Route::get('/orders/{id}', [AdminController::class, 'showOrder'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Revenues
        |--------------------------------------------------------------------------
        */
        Route::get('/revenues', [AdminController::class, 'revenues']);
        Route::get('/revenues/{id}', [AdminController::class, 'showRevenue'])
            ->whereNumber('id');

        // Health-check kết nối admin (frontend: ApiService.verifyAdminAuthConnection)
        Route::get('/test', fn () => response()->json([
            'success' => true,
            'data' => ['authenticated' => true, 'system_healthy' => true],
        ]));

        /*
        |--------------------------------------------------------------------------
        | Course moderation / review
        |--------------------------------------------------------------------------
        */
        Route::get('/course-reviews', [AdminModerationController::class, 'courseReviews']);

        /*
         * Rule mới:
         * Admin approve/publish thành công thì mới trừ 1 lượt tạo khóa học.
         * Vì vậy route approve/reject chuyển sang AdminCourseApprovalController.
         */
        Route::patch('/courses/{courseId}/approve', [AdminCourseApprovalController::class, 'approve'])
            ->whereNumber('courseId');

        Route::patch('/courses/{courseId}/reject', [AdminCourseApprovalController::class, 'reject'])
            ->whereNumber('courseId');

        Route::get('/moderation/items', [AdminModerationController::class, 'moderationItems']);
        Route::get('/moderation/items/{targetType}/{id}', [AdminModerationController::class, 'moderationItemDetail'])
            ->whereIn('targetType', ['comment', 'review'])
            ->whereNumber('id');

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
        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);

        // Static route must stay before /categories/{id}.
        Route::put('/categories/reorder', [AdminCategoryController::class, 'reorder']);

        Route::post('/categories/{id}/restore', [AdminCategoryController::class, 'restore'])
            ->whereNumber('id');

        Route::get('/categories/{id}', [AdminCategoryController::class, 'show'])
            ->whereNumber('id');

        Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])
            ->whereNumber('id');

        Route::patch('/categories/{id}', [AdminCategoryController::class, 'update'])
            ->whereNumber('id');

        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])
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

        /*
        |--------------------------------------------------------------------------
        | FAQs
        |--------------------------------------------------------------------------
        */
        Route::get('/faqs', [AdminFaqController::class, 'index']);
        Route::get('/faqs/{id}', [AdminFaqController::class, 'show'])
            ->whereNumber('id');
        Route::post('/faqs', [AdminFaqController::class, 'store']);
        Route::patch('/faqs/{id}', [AdminFaqController::class, 'update'])
            ->whereNumber('id');
        Route::delete('/faqs/{id}', [AdminFaqController::class, 'destroy'])
            ->whereNumber('id');
        Route::patch('/faqs/{id}/courses', [AdminFaqController::class, 'syncCourses'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Withdrawals
        |--------------------------------------------------------------------------
        */
        Route::get('/withdrawals', [\App\Http\Controllers\AdminWithdrawalController::class, 'index']);
        Route::get('/withdrawals/{id}', [\App\Http\Controllers\AdminWithdrawalController::class, 'show'])
            ->whereNumber('id');
        Route::patch('/withdrawals/{id}/approve', [\App\Http\Controllers\AdminWithdrawalController::class, 'approve'])
            ->whereNumber('id');
        Route::patch('/withdrawals/{id}/reject', [\App\Http\Controllers\AdminWithdrawalController::class, 'reject'])
            ->whereNumber('id');
        Route::patch('/withdrawals/{id}/mark-paid', [\App\Http\Controllers\AdminWithdrawalController::class, 'markPaid'])
            ->whereNumber('id');

        /*
        |--------------------------------------------------------------------------
        | Payout Accounts
        |--------------------------------------------------------------------------
        */
        Route::get('/payout-accounts', [\App\Http\Controllers\AdminPayoutAccountController::class, 'index']);
        Route::get('/payout-accounts/{id}', [\App\Http\Controllers\AdminPayoutAccountController::class, 'show'])
            ->whereNumber('id');
        Route::patch('/payout-accounts/{id}/approve', [\App\Http\Controllers\AdminPayoutAccountController::class, 'approve'])
            ->whereNumber('id');
        Route::patch('/payout-accounts/{id}/reject', [\App\Http\Controllers\AdminPayoutAccountController::class, 'reject'])
            ->whereNumber('id');
        Route::patch('/payout-accounts/{id}/disable', [\App\Http\Controllers\AdminPayoutAccountController::class, 'disable'])
            ->whereNumber('id');
    });

