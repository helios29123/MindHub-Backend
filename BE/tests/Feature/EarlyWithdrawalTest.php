<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Course;
use App\Models\Order;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\UserOtp;
use App\Models\WithdrawRequest;
use App\Services\Payout\EarlyWithdrawalService;
use App\Services\Payout\InstructorPayoutService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EarlyWithdrawalTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private Course $course;
    private PayoutAccount $payoutAccount;
    private EarlyWithdrawalService $earlyWithdrawalService;
    private InstructorPayoutService $instructorPayoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->earlyWithdrawalService = new EarlyWithdrawalService();
        $this->instructorPayoutService = new InstructorPayoutService();

        (new \Database\Seeders\CommissionRuleSeeder())->run();

        $this->instructor = User::create([
            'full_name' => 'Early Inst ' . uniqid(),
            'email' => 'early-inst-' . uniqid() . '@example.com',
            'role' => 'instructor',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Early Course ' . uniqid(),
            'slug' => 'early-course-' . uniqid(),
            'price' => 1000000,
            'status' => 'published',
        ]);

        $this->payoutAccount = PayoutAccount::create([
            'user_id' => $this->instructor->id,
            'provider' => 'Techcombank',
            'account_number' => '19031234567890',
            'account_name' => 'NGUYEN VAN EARLY',
            'status' => PayoutAccount::STATUS_ACTIVE,
            'is_default' => true,
        ]);
        DB::table('payout_accounts')->where('id', $this->payoutAccount->id)->update([
            'updated_at' => now()->subHours(50),
        ]);
    }

    private function createAvailableRevenue(float $instructorAmount = 350000.0): Revenue
    {
        $rule = \App\Models\CommissionRule::create([
            'name' => 'Rule',
            'instructor_rate' => 0.7,
            'platform_rate' => 0.3,
            'is_active' => 1,
            'sale_channel' => 'dummy_' . uniqid(),
        ]);

        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'commission_rule_id' => $rule->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'paid_at' => now()->subDays(35),
        ]);

        return Revenue::create([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_id' => $order->id,
            'gross_amount' => 500000,
            'instructor_amount' => $instructorAmount,
            'platform_fee_amount' => 500000 - $instructorAmount,
            'commission_rule_id' => $rule->id,
            'earned_at' => now()->subDays(35),
        ]);
    }

    /**
     * CASE 1: Minimum amount validation (< 200k throws 422)
     */
    public function test_early_withdrawal_requires_minimum_amount()
    {
        $this->createAvailableRevenue(500000);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage('Số tiền yêu cầu thanh toán sớm tối thiểu là 200.000 VNĐ.');

        $this->earlyWithdrawalService->requestOtp($this->instructor->id, 100000, $this->payoutAccount->id);
    }

    /**
     * CASE 2: Insufficient available balance validation
     */
    public function test_early_withdrawal_fails_when_amount_exceeds_available_balance()
    {
        $this->createAvailableRevenue(300000);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->earlyWithdrawalService->requestOtp($this->instructor->id, 500000, $this->payoutAccount->id);
    }

    /**
     * CASE 3: Step 1 Request OTP & Step 2 Complete Early Withdrawal
     */
    public function test_complete_early_withdrawal_with_otp_and_partial_revenue_allocation()
    {
        $rev1 = $this->createAvailableRevenue(300000);
        $rev2 = $this->createAvailableRevenue(200000);

        // Step 1: Request OTP
        $otpResponse = $this->earlyWithdrawalService->requestOtp($this->instructor->id, 400000, $this->payoutAccount->id);
        $this->assertNotNull($otpResponse['masked_email']);

        // Set known OTP hash for testing
        $otpRecord = UserOtp::where('user_id', $this->instructor->id)
            ->where('purpose', 'early_withdrawal')
            ->first();
        $otpRecord->update(['code_hash' => Hash::make('654321')]);

        // Step 2: Confirm OTP & Create Early Withdrawal
        $withdrawal = $this->earlyWithdrawalService->createEarlyWithdrawal(
            $this->instructor->id,
            400000,
            $this->payoutAccount->id,
            '654321'
        );

        $this->assertNotNull($withdrawal);
        $this->assertEquals(WithdrawRequest::TYPE_EARLY_WITHDRAWAL, $withdrawal->type);
        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $withdrawal->status);
        $this->assertEquals(400000.0, (float) $withdrawal->amount);
        $this->assertEquals('Techcombank', $withdrawal->bank_name);
        $this->assertEquals('19031234567890', $withdrawal->account_number_snapshot);

        // Verify withdrawal_revenues pivot records
        $allocations = DB::table('withdrawal_revenues')
            ->where('withdrawal_id', $withdrawal->id)
            ->get();

        $this->assertCount(2, $allocations);
        $totalAllocated = $allocations->sum('allocated_amount');
        $this->assertEquals(400000.0, (float) $totalAllocated);
    }

    /**
     * CASE 4: Double-Spend Protection (Reserved revenue is excluded from automatic monthly payout generator)
     */
    public function test_reserved_early_withdrawal_revenues_are_excluded_from_automatic_payout()
    {
        $rev = $this->createAvailableRevenue(500000);

        // Create early withdrawal reserving 500k
        $this->earlyWithdrawalService->requestOtp($this->instructor->id, 500000, $this->payoutAccount->id);
        $otpRecord = UserOtp::where('user_id', $this->instructor->id)->first();
        $otpRecord->update(['code_hash' => Hash::make('111222')]);

        $this->earlyWithdrawalService->createEarlyWithdrawal(
            $this->instructor->id,
            500000,
            $this->payoutAccount->id,
            '111222'
        );

        // Run automatic monthly payout generator
        $automaticPayout = $this->instructorPayoutService->generateMonthlyPayout($this->instructor->id, now());

        // Should be null because all available revenues are reserved for the early withdrawal request
        $this->assertNull($automaticPayout);
    }

    /**
     * CASE 5: Cancellation of pending early withdrawal releases revenue allocations
     */
    public function test_cancellation_releases_revenue_allocations()
    {
        $this->createAvailableRevenue(350000);

        $this->earlyWithdrawalService->requestOtp($this->instructor->id, 350000, $this->payoutAccount->id);
        $otpRecord = UserOtp::where('user_id', $this->instructor->id)->first();
        $otpRecord->update(['code_hash' => Hash::make('999888')]);

        $withdrawal = $this->earlyWithdrawalService->createEarlyWithdrawal(
            $this->instructor->id,
            350000,
            $this->payoutAccount->id,
            '999888'
        );

        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $withdrawal->status);

        // Cancel early withdrawal
        $success = $this->earlyWithdrawalService->cancelEarlyWithdrawal($this->instructor->id, $withdrawal->id);
        $this->assertTrue($success);

        $withdrawal->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_CANCELLED, $withdrawal->status);

        // Verify allocations are deleted
        $allocationCount = DB::table('withdrawal_revenues')->where('withdrawal_id', $withdrawal->id)->count();
        $this->assertEquals(0, $allocationCount);

        // Available balance should now be restored
        $summary = $this->earlyWithdrawalService->getPaymentSummary($this->instructor->id);
        $this->assertEquals(350000.0, $summary['early_withdrawable_balance']);
    }

    public function test_cannot_cancel_if_not_pending()
    {
        $this->createAvailableRevenue(350000);

        $this->earlyWithdrawalService->requestOtp($this->instructor->id, 350000, $this->payoutAccount->id);
        $otpRecord = UserOtp::where('user_id', $this->instructor->id)->first();
        $otpRecord->update(['code_hash' => Hash::make('999888')]);

        $withdrawal = $this->earlyWithdrawalService->createEarlyWithdrawal(
            $this->instructor->id,
            350000,
            $this->payoutAccount->id,
            '999888'
        );

        $statuses = [
            WithdrawRequest::STATUS_APPROVED,
            WithdrawRequest::STATUS_PROCESSING,
            WithdrawRequest::STATUS_MANUAL_REQUIRED,
            WithdrawRequest::STATUS_PAID,
        ];

        foreach ($statuses as $status) {
            $withdrawal->update(['status' => $status]);

            try {
                $this->earlyWithdrawalService->cancelEarlyWithdrawal($this->instructor->id, $withdrawal->id);
                $this->fail("Should not be able to cancel when status is {$status}");
            } catch (\App\Exceptions\BusinessException $e) {
                $this->assertEquals('Chỉ có thể hủy yêu cầu thanh toán sớm ở trạng thái chờ duyệt.', $e->getMessage());
            }
        }
    }
}
