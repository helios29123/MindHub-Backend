<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.session', 'active.user', 'role:learner,member'])->group(function () {
    Route::post('/orders', [PaymentController::class, 'storeOrder']);
    Route::post('/orders/apply-coupon', [PaymentController::class, 'applyCoupon']);
    Route::post('/payments', [PaymentController::class, 'storePayment']);
    Route::get('/orders/my', [PaymentController::class, 'myOrders']);
    Route::get('/orders/{id}', [PaymentController::class, 'showOrder']);
});

Route::middleware(['auth.session', 'role:admin'])->group(function () {
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
});