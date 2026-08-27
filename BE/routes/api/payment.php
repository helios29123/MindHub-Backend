<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order APIs
|--------------------------------------------------------------------------
| learner/member/instructor đều có thể tạo order mua khóa học.
| Instructor được mua khóa học của giảng viên khác.
| Instructor không được mua khóa học của chính mình, rule này xử lý trong service.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner,member,instructor'])
    ->group(function (): void {
        Route::post('/orders', [PaymentController::class, 'storeOrder']);

        Route::post('/orders/apply-coupon', [PaymentController::class, 'applyCoupon']);
        Route::get('/orders/check-coupon', [PaymentController::class, 'checkCoupon']);

        Route::post('/payments', [PaymentController::class, 'storePayment']);

        Route::get('/orders/my', [PaymentController::class, 'myOrders']);

        Route::get('/orders/{id}', [PaymentController::class, 'showOrder'])
            ->whereNumber('id');

        Route::patch('/orders/{orderId}/cancel', [PaymentController::class, 'cancelOrder'])
            ->whereNumber('orderId');

        Route::post('/orders/{orderId}/retry-payment', [PaymentController::class, 'retryPayment'])
            ->whereNumber('orderId');
    });

/*
|--------------------------------------------------------------------------
| Payment create URL (VNPAY & SePay VietQR)
|--------------------------------------------------------------------------
| learner/member/instructor đều được tạo thông tin thanh toán cho order của chính mình.
*/
Route::middleware(['auth.session', 'active.user', 'role:learner,member,instructor'])
    ->group(function (): void {
        Route::post('/payments/vnpay/create', [PaymentController::class, 'createVnpayPayment']);
        Route::post('/payments/sepay/create', [PaymentController::class, 'createSepayPayment']);
    });

/*
|--------------------------------------------------------------------------
| Payment webhooks (Public / Admin)
|--------------------------------------------------------------------------
| SePay Webhook is public and authenticated via SePay API key / payload.
*/
Route::post('/payments/sepay/webhook', [PaymentController::class, 'sepayWebhook']);
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
Route::get('/payments/vnpay-return', [PaymentController::class, 'vnpayReturn']);
Route::match(['get', 'post'], '/coupons/validate', [PaymentController::class, 'validateCoupon']);
