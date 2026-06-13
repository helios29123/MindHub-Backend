<?php
use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth.session', 'role:instructor'])
    ->prefix('instructor')
    ->group(function (): void {
        Route::post('/course-announcements', [MarketingController::class, 'courseAnnouncements']);
        Route::get('/coupons', [MarketingController::class, 'indexCoupons']);
        Route::post('/coupons', [MarketingController::class, 'storeCoupon']);
        Route::get('/coupons/{id}', [MarketingController::class, 'showCoupon'])->whereNumber('id');
        Route::patch('/coupons/{id}', [MarketingController::class, 'updateCoupon'])->whereNumber('id');
        Route::delete('/coupons/{id}', [MarketingController::class, 'destroyCoupon'])->whereNumber('id');
    });