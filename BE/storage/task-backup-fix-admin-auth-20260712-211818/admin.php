<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard/overview', [AdminController::class, 'dashboardOverview']);
        Route::get('/dashboard/revenue-chart', [AdminController::class, 'dashboardRevenueChart']);
        Route::get('/dashboard/action-required', [AdminController::class, 'dashboardActionRequired']);

        Route::get('/courses', [AdminController::class, 'courses']);
        Route::get('/courses/{course}', [AdminController::class, 'showCourse'])->whereNumber('course');
        Route::patch('/courses/{course}/approve', [AdminController::class, 'approveCourse'])->whereNumber('course');
        Route::patch('/courses/{course}/reject', [AdminController::class, 'rejectCourse'])->whereNumber('course');
        Route::patch('/courses/{course}/hide', [AdminController::class, 'hideCourse'])->whereNumber('course');
        Route::patch('/courses/{course}/publish', [AdminController::class, 'publishCourse'])->whereNumber('course');
        Route::patch('/courses/bulk-approve', [AdminController::class, 'bulkApproveCourses']);

        Route::get('/orders', [AdminController::class, 'orders']);
        Route::get('/orders/{order}', [AdminController::class, 'showOrder'])->whereNumber('order');
        Route::patch('/orders/{order}/mark-paid', [AdminController::class, 'markOrderPaid'])->whereNumber('order');
        Route::patch('/orders/{order}/mark-failed', [AdminController::class, 'markOrderFailed'])->whereNumber('order');
        Route::patch('/orders/{order}/cancel', [AdminController::class, 'cancelOrder'])->whereNumber('order');

        Route::get('/revenues', [AdminController::class, 'revenues']);
        Route::get('/revenues/summary', [AdminController::class, 'revenueSummary']);
        Route::get('/revenues/source-breakdown', [AdminController::class, 'revenueSourceBreakdown']);
        Route::get('/revenues/chart', [AdminController::class, 'revenueChart']);
        Route::get('/revenues/{revenue}', [AdminController::class, 'showRevenue'])->whereNumber('revenue');

        Route::get('/payout-batches', [AdminController::class, 'payoutBatches']);
        Route::post('/payout-batches', [AdminController::class, 'createPayoutBatch']);
        Route::get('/payout-batches/{batch}', [AdminController::class, 'showPayoutBatch'])->whereNumber('batch');
        Route::patch('/payout-batches/{batch}/lock', [AdminController::class, 'lockPayoutBatch'])->whereNumber('batch');
        Route::patch('/payout-items/{item}/mark-paid', [AdminController::class, 'markPayoutItemPaid'])->whereNumber('item');
        Route::patch('/payout-items/{item}/hold', [AdminController::class, 'holdPayoutItem'])->whereNumber('item');
        Route::get('/payout-batches/{batch}/export-bank-list', [AdminController::class, 'exportPayoutBankList'])->whereNumber('batch');

        Route::get('/payout-accounts', [AdminController::class, 'payoutAccounts']);
        Route::get('/payout-accounts/{account}', [AdminController::class, 'showPayoutAccount'])->whereNumber('account');
        Route::patch('/payout-accounts/{account}/approve', [AdminController::class, 'approvePayoutAccount'])->whereNumber('account');
        Route::patch('/payout-accounts/{account}/reject', [AdminController::class, 'rejectPayoutAccount'])->whereNumber('account');
        Route::patch('/payout-accounts/{account}/disable', [AdminController::class, 'disablePayoutAccount'])->whereNumber('account');
        Route::get('/payout-accounts/{account}/logs', [AdminController::class, 'payoutAccountLogs'])->whereNumber('account');

        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{user}', [AdminController::class, 'showUser'])->whereNumber('user');
        Route::patch('/users/{user}/block', [AdminController::class, 'blockUser'])->whereNumber('user');
        Route::patch('/users/{user}/unblock', [AdminController::class, 'unblockUser'])->whereNumber('user');
        Route::patch('/users/{user}/approve-instructor', [AdminController::class, 'approveInstructor'])->whereNumber('user');

        Route::get('/commission-rules', [AdminController::class, 'commissionRules']);
        Route::patch('/commission-rules/{rule}', [AdminController::class, 'updateCommissionRule'])->whereNumber('rule');

        Route::get('/notifications', [AdminController::class, 'notifications']);
        Route::get('/notifications/{notification}', [AdminController::class, 'showNotification'])->whereNumber('notification');
        Route::patch('/notifications/{notification}/mark-read', [AdminController::class, 'markNotificationRead'])->whereNumber('notification');
        Route::patch('/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead']);
        Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
    });
