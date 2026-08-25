<?php

namespace Tests\Feature;

use App\Models\CommissionRule;
use App\Models\Course;
use App\Models\Order;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\WithdrawRequest;
use App\Services\Payment\RevenueShareService;
use App\Services\Payout\EarlyWithdrawalService;
use App\Services\Payout\InstructorPayoutService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstructorPayoutTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private Course $course;
    private CommissionRule $rule;
    private RevenueShareService $revenueShareService;
    private InstructorPayoutService $payoutService;
    private EarlyWithdrawalService $earlyWithdrawalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revenueShareService = new RevenueShareService();
        $this->payoutService = new InstructorPayoutService();
        $this->earlyWithdrawalService = new EarlyWithdrawalService();

        $this->rule = CommissionRule::create([
            'name' => 'Default Rule',
            'instructor_rate' => 0.7,
            'platform_rate' => 0.3,
            'is_active' => 1,
            'sale_channel' => 'marketplace',
        ]);

        $this->instructor = User::create([
            'full_name' => 'Inst ' . uniqid(),
            'email' => 'inst-payout-' . uniqid() . '@example.com',
            'role' => 'instructor',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Payout Course ' . uniqid(),
            'slug' => 'payout-course-' . uniqid(),
            'price' => 500000,
            'status' => 'published',
        ]);
    }

    /**
     * CASE 3: Below minimum payout threshold => Payout BLOCKED with reason minimum_payout_not_reached
     */
    public function test_payout_blocked_when_below_minimum_threshold()
    {
        PayoutAccount::create([
            'user_id' => $this->instructor->id,
            'provider' => 'Vietcombank',
            'account_number' => '1234567890',
            'account_name' => 'Instructor Name',
            'status' => PayoutAccount::STATUS_ACTIVE,
            'is_default' => true,
        ]);

        $revenue = Revenue::create([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_id' => Order::create([
                'user_id' => $this->instructor->id,
                'course_id' => $this->course->id,
                'commission_rule_id' => $this->rule->id,
                'order_code' => 'ORD_' . uniqid(),
                'amount' => 100000,
                'status' => 'paid',
                'payment_status' => 'paid',
            ])->id,
            'gross_amount' => 100000,
            'instructor_amount' => 70000, // 70k < 200k
            'platform_fee_amount' => 30000,
            'commission_rule_id' => $this->rule->id,
            'earned_at' => now(),
        ]);

        $payout = $this->payoutService->generateMonthlyPayout($this->instructor->id, now());

        $this->assertNotNull($payout);
        $this->assertEquals(WithdrawRequest::STATUS_MANUAL_REQUIRED, $payout->status);
        $this->assertEquals('minimum_payout_not_reached', $payout->blocked_reason);
    }

    /**
     * CASE 4: Missing payout account => Payout BLOCKED with reason missing_payout_account
     */
    public function test_payout_blocked_when_payout_account_missing()
    {
        Revenue::create([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_id' => Order::create([
                'user_id' => $this->instructor->id,
                'course_id' => $this->course->id,
                'commission_rule_id' => $this->rule->id,
                'order_code' => 'ORD_' . uniqid(),
                'amount' => 500000,
                'status' => 'paid',
                'payment_status' => 'paid',
            ])->id,
            'gross_amount' => 500000,
            'instructor_amount' => 350000,
            'platform_fee_amount' => 150000,
            'commission_rule_id' => $this->rule->id,
            'earned_at' => now(),
        ]);

        $payout = $this->payoutService->generateMonthlyPayout($this->instructor->id, now());

        $this->assertNotNull($payout);
        $this->assertEquals(WithdrawRequest::STATUS_MANUAL_REQUIRED, $payout->status);
        $this->assertEquals('missing_payout_account', $payout->blocked_reason);
    }

    /**
     * CASE 5: Valid account and >= minimum threshold => Payout created in ready_to_pay status
     */
    public function test_payout_created_as_ready_to_pay_when_valid()
    {
        PayoutAccount::create([
            'user_id' => $this->instructor->id,
            'provider' => 'MB Bank',
            'account_number' => '9876543210',
            'account_name' => 'Inst Name',
            'status' => PayoutAccount::STATUS_ACTIVE,
            'is_default' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'commission_rule_id' => $this->rule->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);

        $revenue = Revenue::create([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_id' => $order->id,
            'gross_amount' => 500000,
            'instructor_amount' => 350000,
            'platform_fee_amount' => 150000,
            'commission_rule_id' => $this->rule->id,
            'earned_at' => now(),
        ]);

        $payout = $this->payoutService->generateMonthlyPayout($this->instructor->id, now());

        $this->assertNotNull($payout);
        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $payout->status);
        $this->assertEquals(350000.0, (float) $payout->amount);
        $this->assertEquals('MB Bank', $payout->bank_name);
        $this->assertEquals('3210', $payout->account_number_snapshot);

        // check withdrawal_revenues pivot
        $allocations = DB::table('withdrawal_revenues')->where('withdrawal_id', $payout->id)->get();
        $this->assertCount(1, $allocations);
        $this->assertEquals($revenue->id, $allocations[0]->revenue_id);
    }

    /**
     * CASE 6: Process ready payouts sets payout status to PAID
     */
    public function test_process_ready_payouts_marks_paid()
    {
        PayoutAccount::create([
            'user_id' => $this->instructor->id,
            'provider' => 'Vietinbank',
            'account_number' => '5566778899',
            'account_name' => 'Inst Name',
            'status' => PayoutAccount::STATUS_ACTIVE,
            'is_default' => true,
        ]);

        Revenue::create([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_id' => Order::create([
                'user_id' => $this->instructor->id,
                'course_id' => $this->course->id,
                'commission_rule_id' => $this->rule->id,
                'order_code' => 'ORD_' . uniqid(),
                'amount' => 500000,
                'status' => 'paid',
                'payment_status' => 'paid',
            ])->id,
            'gross_amount' => 500000,
            'instructor_amount' => 350000,
            'platform_fee_amount' => 150000,
            'commission_rule_id' => $this->rule->id,
            'earned_at' => now(),
        ]);

        $payout = $this->payoutService->generateMonthlyPayout($this->instructor->id, now());
        $processedCount = $this->payoutService->processReadyPayouts();

        $this->assertGreaterThanOrEqual(1, $processedCount);
        $payout->refresh();
        $this->assertEquals(WithdrawRequest::STATUS_PAID, $payout->status);
        $this->assertNotNull($payout->paid_at);
    }

    /**
     * CASE 7: Manual withdrawal without OTP rejected with exception
     */
    public function test_manual_withdrawal_without_otp_rejected()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/withdrawals', [
                'amount' => 500000,
                'payout_account_id' => 1,
            ]);

        $response->assertStatus(422);
    }

    /**
     * CASE 8: Instructor payment summary API returns hybrid summary structure
     */
    public function test_instructor_payment_summary_api_returns_hybrid_structure()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/payments/summary');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'pending_revenue',
                'available_balance',
                'reserved_balance',
                'scheduled_payout',
                'early_withdrawable_balance',
                'total_paid',
                'minimum_payout',
                'minimum_early_withdrawal',
                'has_active_early_withdrawal',
                'early_withdrawal_requests_remaining',
                'automatic_payout_window' => ['from', 'to'],
                'payout_account_verified',
            ]
        ]);
    }

    /**
     * CASE 9: Early withdrawal OTP request and creation flow
     */
    public function test_early_withdrawal_otp_flow()
    {
        $this->withoutMiddleware();

        $account = PayoutAccount::create([
            'user_id' => $this->instructor->id,
            'provider' => 'Techcombank',
            'account_number' => '19031234567890',
            'account_name' => 'Inst Early',
            'status' => PayoutAccount::STATUS_ACTIVE,
            'is_default' => true,
        ]);
        DB::table('payout_accounts')->where('id', $account->id)->update(['updated_at' => now()->subHours(50)]);

        Revenue::create([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_id' => Order::create([
                'user_id' => $this->instructor->id,
                'course_id' => $this->course->id,
                'commission_rule_id' => $this->rule->id,
                'order_code' => 'ORD_' . uniqid(),
                'amount' => 500000,
                'status' => 'paid',
                'payment_status' => 'paid',
            ])->id,
            'gross_amount' => 500000,
            'instructor_amount' => 350000,
            'platform_fee_amount' => 150000,
            'commission_rule_id' => $this->rule->id,
            'earned_at' => now(),
        ]);

        // Request OTP
        $otpData = $this->earlyWithdrawalService->requestOtp($this->instructor->id, 300000, $account->id);
        $this->assertNotNull($otpData['masked_email']);

        // Fetch latest OTP record and set known hash for testing
        $otpRecord = \App\Models\UserOtp::where('user_id', $this->instructor->id)
            ->where('purpose', 'early_withdrawal')
            ->first();
        $this->assertNotNull($otpRecord);
        $otpRecord->update(['code_hash' => \Illuminate\Support\Facades\Hash::make('123456')]);

        // Manually create with service to test allocation
        $withdrawal = $this->earlyWithdrawalService->createEarlyWithdrawal($this->instructor->id, 300000, $account->id, '123456');

        $this->assertNotNull($withdrawal);
        $this->assertEquals(WithdrawRequest::TYPE_EARLY_WITHDRAWAL, $withdrawal->type);
        $this->assertEquals(WithdrawRequest::STATUS_PENDING, $withdrawal->status);
        $this->assertEquals(300000.0, (float) $withdrawal->amount);
    }
}
