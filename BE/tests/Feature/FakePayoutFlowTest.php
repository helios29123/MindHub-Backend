<?php

namespace Tests\Feature;

use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class FakePayoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;
    private PayoutAccount $payoutAccount;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->instructor = User::factory()->create(['role' => 'instructor']);
        
        $this->payoutAccount = PayoutAccount::create([
            'user_id' => $this->instructor->id,
            'provider' => 'vietqr',
            'bank_name' => 'Test Bank',
            'account_number' => '123456789',
            'account_name' => 'TEST USER',
            'status' => PayoutAccount::STATUS_ACTIVE,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        Config::set('payout.driver', 'fake');
    }

    private function createAvailableRevenue(float $amount): void
    {
        $course = \App\Models\Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Test Course',
            'slug' => 'test-course-' . uniqid(),
            'price' => 1000000,
            'status' => 'published',
        ]);

        // For revenue creation we need an order first to pass schema constraints
        $order = \App\Models\Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
            'paid_at' => now()->subDays(35),
        ]);

        Revenue::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'instructor_id' => $this->instructor->id,
            'gross_amount' => $amount,
            'instructor_amount' => $amount,
            'platform_fee_amount' => 0,
        ]);
    }

    private function createWithdrawalRequest(float $amount, string $status = WithdrawRequest::STATUS_PENDING): WithdrawRequest
    {
        $this->createAvailableRevenue($amount);

        /** @var \App\Services\Payout\EarlyWithdrawalService $service */
        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $service->requestOtp($this->instructor->id, $amount, $this->payoutAccount->id);
        
        // Mock OTP check
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('verifyOtp');
        $method->setAccessible(true);
        // We will just create directly instead of bypassing OTP to be cleaner
        
        $withdrawal = WithdrawRequest::create([
            'user_id' => $this->instructor->id,
            'payout_account_id' => $this->payoutAccount->id,
            'amount' => $amount,
            'status' => $status,
            'type' => WithdrawRequest::TYPE_EARLY_WITHDRAWAL,
            'bank_name' => $this->payoutAccount->bank_name,
            'account_number_snapshot' => $this->payoutAccount->account_number,
            'account_name_snapshot' => $this->payoutAccount->account_name,
            'requested_at' => now(),
        ]);

        // Mock the revenue allocation directly for test isolation if needed, but it's better to let EarlyWithdrawalService do it.
        // Wait, EarlyWithdrawalService::createEarlyWithdrawal handles allocation.
        // So I'll mock Cache for OTP and call it.
        return $withdrawal; // We'll rewrite this properly below
    }

    private function mockOtp(string $otp): void
    {
        $otpRecord = \App\Models\UserOtp::where('user_id', $this->instructor->id)
            ->where('purpose', 'early_withdrawal')
            ->first();
        if ($otpRecord) {
            $otpRecord->update(['code_hash' => \Illuminate\Support\Facades\Hash::make($otp)]);
        }
    }

    private function createRealWithdrawalRequest(float $amount): WithdrawRequest
    {
        $this->createAvailableRevenue($amount);
        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $service->requestOtp($this->instructor->id, $amount, $this->payoutAccount->id);
        
        $this->mockOtp('123456');

        return $service->createEarlyWithdrawal($this->instructor->id, $amount, $this->payoutAccount->id, '123456');
    }

    // A. Approve -> Success
    public function test_approve_success_flow()
    {
        Config::set('payout.fake.result', 'success');
        $withdrawal = $this->createRealWithdrawalRequest(1000000);
        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $withdrawal->status);

        $controller = app(\App\Http\Controllers\AdminWithdrawalController::class);
        $controller->approve($withdrawal->id);

        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PAID, $withdrawal->status);
        
        // B. Provider ID check
        $this->assertEquals('FAKE-WD-' . $withdrawal->id, $withdrawal->provider_payout_id);
    }

    // C. Failed releases balance
    public function test_approve_failed_flow()
    {
        Config::set('payout.fake.result', 'failed');
        $withdrawal = $this->createRealWithdrawalRequest(1000000);
        
        $controller = app(\App\Http\Controllers\AdminWithdrawalController::class);
        $controller->approve($withdrawal->id);

        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_MANUAL_REQUIRED, $withdrawal->status);

        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $summary = $service->getPaymentSummary($this->instructor->id);
        
        $this->assertEquals(0, $summary['early_withdrawable_balance']); // Remains locked
        $this->assertEquals(1, $summary['early_withdrawal_requests_remaining']); // Quota is not returned
    }

    // D. Processing keeps reserve
    public function test_approve_processing_flow()
    {
        Config::set('payout.fake.result', 'processing');
        $withdrawal = $this->createRealWithdrawalRequest(1000000);
        
        $controller = app(\App\Http\Controllers\AdminWithdrawalController::class);
        $controller->approve($withdrawal->id);

        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PROCESSING, $withdrawal->status);

        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $summary = $service->getPaymentSummary($this->instructor->id);
        
        $this->assertEquals(0, $summary['early_withdrawable_balance']);
        $this->assertEquals('FAKE-WD-' . $withdrawal->id, $withdrawal->provider_payout_id);
    }

    // E. Processing -> Success
    public function test_processing_to_success()
    {
        Config::set('payout.fake.result', 'processing');
        $withdrawal = $this->createRealWithdrawalRequest(1000000);
        
        app(\App\Http\Controllers\AdminWithdrawalController::class)->approve($withdrawal->id);
        
        $payoutService = app(\App\Services\Payout\PayoutService::class);
        $payoutService->resolveWebhook($withdrawal, 'success');

        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PAID, $withdrawal->status);
    }

    // F. Processing -> Failed (now manual_required)
    public function test_processing_error_returns_manual_required()
    {
        Config::set('payout.fake.result', 'processing');
        $withdrawal = $this->createRealWithdrawalRequest(1000000);
        
        app(\App\Http\Controllers\AdminWithdrawalController::class)->approve($withdrawal->id);
        
        $payoutService = app(\App\Services\Payout\PayoutService::class);
        $payoutService->resolveWebhook($withdrawal, 'failed', 'Webhook said failed');

        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_MANUAL_REQUIRED, $withdrawal->status);
        $this->assertEquals('Webhook said failed', $withdrawal->failure_reason);

        $summary = app(\App\Services\Payout\EarlyWithdrawalService::class)->getPaymentSummary($this->instructor->id);
        $this->assertEquals(0, $summary['early_withdrawable_balance']);
    }

    // G. Double approve (Idempotency)
    public function test_double_approve()
    {
        Config::set('payout.fake.result', 'processing');
        $withdrawal = $this->createRealWithdrawalRequest(1000000);
        
        $controller = app(\App\Http\Controllers\AdminWithdrawalController::class);
        $controller->approve($withdrawal->id); // 1st
        
        // Change result to success to see if 2nd approve processes it
        Config::set('payout.fake.result', 'success');
        $res = $controller->approve($withdrawal->id); // 2nd
        $this->assertEquals(422, $res->getStatusCode()); // Should fail because not pending

        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PROCESSING, $withdrawal->status); // Remains processing
    }

    // H. Paid retry (Idempotency)
    public function test_paid_retry_webhook()
    {
        Config::set('payout.fake.result', 'success');
        $withdrawal = $this->createRealWithdrawalRequest(1000000);
        app(\App\Http\Controllers\AdminWithdrawalController::class)->approve($withdrawal->id);
        
        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PAID, $withdrawal->status);

        $payoutService = app(\App\Services\Payout\PayoutService::class);
        // Call it again
        $payoutService->resolveWebhook($withdrawal, 'failed'); // try to fail a PAID one
        
        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PAID, $withdrawal->status); // unchanged
    }

    // I. Partial revenue + success
    public function test_partial_allocation_success()
    {
        $this->createAvailableRevenue(2000000);
        
        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $service->requestOtp($this->instructor->id, 1200000, $this->payoutAccount->id);
        
        $this->mockOtp('123456');
        
        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $withdrawal = $service->createEarlyWithdrawal($this->instructor->id, 1200000, $this->payoutAccount->id, '123456');

        Config::set('payout.fake.result', 'success');
        app(\App\Http\Controllers\AdminWithdrawalController::class)->approve($withdrawal->id);

        $revenue = Revenue::first();
        // Since only 1.2M allocated of 2M, revenue is partially allocated.
        // Revenue status doesn't exist in DB final, we rely on withdrawal_revenues pivot.

        $summary = $service->getPaymentSummary($this->instructor->id);
        $this->assertEquals(800000, $summary['early_withdrawable_balance']);
    }

    // J. Partial revenue + failed
    public function test_partial_allocation_failed()
    {
        $this->createAvailableRevenue(2000000);
        
        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $service->requestOtp($this->instructor->id, 1200000, $this->payoutAccount->id);
        
        $this->mockOtp('123456');
        
        $service = app(\App\Services\Payout\EarlyWithdrawalService::class);
        $withdrawal = $service->createEarlyWithdrawal($this->instructor->id, 1200000, $this->payoutAccount->id, '123456');

        Config::set('payout.fake.result', 'failed');
        app(\App\Http\Controllers\AdminWithdrawalController::class)->approve($withdrawal->id);

        $summary = $service->getPaymentSummary($this->instructor->id);
        $this->assertEquals(800000, $summary['early_withdrawable_balance']); // Remains locked
    }
}
