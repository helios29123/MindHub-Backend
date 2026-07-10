<?php
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'active.user', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        Route::post('/course-announcements', [MarketingController::class, 'courseAnnouncements']);
        /*
        |--------------------------------------------------------------------------
        | Instructor coupon management
        |--------------------------------------------------------------------------
        */
        Route::get('/coupons/summary', [MarketingController::class, 'instructorCouponSummary']);
        Route::get('/coupons/course-options', [MarketingController::class, 'couponCourseOptions']);
        Route::get('/coupons', [MarketingController::class, 'instructorCoupons']);
        Route::post('/coupons', [MarketingController::class, 'storeInstructorCoupon']);
        Route::get('/coupons/{id}', [MarketingController::class, 'showInstructorCoupon'])
            ->whereNumber('id');
        Route::patch('/coupons/{id}', [MarketingController::class, 'updateInstructorCoupon'])
            ->whereNumber('id');
        Route::patch('/coupons/{id}/status', [MarketingController::class, 'updateInstructorCouponStatus'])
            ->whereNumber('id');
        Route::delete('/coupons/{id}', [MarketingController::class, 'destroyInstructorCoupon'])
            ->whereNumber('id');
    });