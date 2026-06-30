<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Learner order actions
|--------------------------------------------------------------------------
| Chỉ learner được hủy đơn / retry thanh toán đơn mua khóa học.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner'])
    ->group(function (): void {
        Route::patch('/orders/{orderId}/cancel', [PaymentController::class, 'cancelOrder'])
            ->whereNumber('orderId');

        Route::post('/orders/{orderId}/retry-payment', [PaymentController::class, 'retryPayment'])
            ->whereNumber('orderId');
    });

/*
|--------------------------------------------------------------------------
| Course purchase order APIs
|--------------------------------------------------------------------------
| Learner/member tạo đơn mua khóa học, xem đơn, áp coupon.
| Không mở POST /orders cho instructor để tránh instructor mua khóa học qua flow learner.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner,member'])
    ->group(function (): void {
        Route::post('/orders', [PaymentController::class, 'storeOrder']);

        Route::post('/orders/apply-coupon', [PaymentController::class, 'applyCoupon']);

        Route::post('/payments', [PaymentController::class, 'storePayment']);

        Route::get('/orders/my', [PaymentController::class, 'myOrders']);

        Route::get('/orders/{id}', [PaymentController::class, 'showOrder'])
            ->whereNumber('id');
    });

/*
|--------------------------------------------------------------------------
| VNPAY create payment URL
|--------------------------------------------------------------------------
| Cho learner/member thanh toán order mua khóa học.
| Cho instructor thanh toán order mua gói lượt tạo khóa học.
|
| Lưu ý:
| - Controller/Service vẫn phải kiểm tra order thuộc user hiện tại.
| - Learner chỉ nên thanh toán order_type = course_purchase.
| - Instructor chỉ nên thanh toán order_type = instructor_credit.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner,member,instructor'])
    ->group(function (): void {
        Route::post('/payments/vnpay/create', [PaymentController::class, 'createVnpayPayment']);
    });

/*
|--------------------------------------------------------------------------
| Admin payment webhook
|--------------------------------------------------------------------------
*/
Route::middleware(['auth.session', 'active.user', 'role:admin'])
    ->group(function (): void {
        Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
    });

/*
|--------------------------------------------------------------------------
| VNPAY return URL
|--------------------------------------------------------------------------
| VNPAY redirect về URL này nên để public.
*/
Route::get('/payments/vnpay-return', [PaymentController::class, 'vnpayReturn']);
