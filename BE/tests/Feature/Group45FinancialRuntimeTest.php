<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\PayoutAccount;
use App\Models\WithdrawRequest;
use App\Services\Payment\RevenueShareService;
use App\Services\Payout\Contracts\PayoutGatewayInterface;
use App\Services\Payout\EarlyWithdrawalService;
use App\Services\Payout\PayoutService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;
use Throwable;

class Group45FinancialRuntimeTest extends TestCase
{
    use DatabaseTransactions;

    private int $instructorId;
    private int $learnerId;
    private int $otherInstructorId;
    private int $courseId;
    private int $commissionRuleId;
    private int $verifiedPayoutAccountId;

    protected function setUp(): void
    {
        parent::setUp();

        if (app()->environment('production')) {
            $this->fail('Group45FinancialRuntimeTest must never run in production.');
        }

        config([
            'revenue.early_withdrawal.enabled' => true,
            'revenue.early_withdrawal.minimum_amount' => 200000,
            'revenue.early_withdrawal.otp_expires_minutes' => 5,
            'revenue.early_withdrawal.otp_max_attempts' => 5,
            'revenue.early_withdrawal.otp_resend_seconds' => 60,
        ]);

        Mail::fake();

        $suffix = str_replace('.', '', uniqid('', true));

        $this->instructorId = $this->createUser(
            "Instructor {$suffix}",
            "inst{$suffix}@example.test",
            'instructor'
        );

        $this->otherInstructorId = $this->createUser(
            "Other Instructor {$suffix}",
            "otherinst{$suffix}@example.test",
            'instructor'
        );

        $this->learnerId = $this->createUser(
            "Learner {$suffix}",
            "learner{$suffix}@example.test",
            'learner'
        );

        $this->commissionRuleId = DB::table('commission_rules')->insertGetId([
            'name' => "G45 {$suffix}",
            'description' => 'Group 4+5 runtime test rule',
            'instructor_rate' => 0.7050,
            'platform_rate' => 0.2950,
            'is_active' => 0,
        ]);

        $this->courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $this->instructorId,
            'title' => "Group45 Course {$suffix}",
            'slug' => "group45-course-{$suffix}",
            'price' => 1000000,
            'discount_percent' => 0,
            'course_level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
            'is_featured' => 0,
            'published_at' => now(),
        ]);

        $this->verifiedPayoutAccountId = DB::table('payout_accounts')->insertGetId([
            'user_id' => $this->instructorId,
            'provider' => 'MB',
            'account_number' => '9704' . substr($suffix, -8),
            'account_name' => 'GROUP45 INSTRUCTOR',
            'status' => 'verified',
            'is_default' => 0,
            'verified_at' => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // REVENUE / COMMISSION
    // -------------------------------------------------------------------------

    #[TestDox('01. Đơn hàng chưa thanh toán thì không được tạo doanh thu')]
    public function test_01_unpaid_order_cannot_create_revenue(): void
    {
        $orderId = $this->createOrder('pending_payment', 'pending', 1000000);

        $thrown = false;

        try {
            app(RevenueShareService::class)->createRevenueForPaidOrder($orderId);
        } catch (Throwable $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Unpaid order must be rejected by RevenueShareService.');
        $this->assertDatabaseMissing('revenues', ['order_id' => $orderId]);
    }

    #[TestDox('02. Đơn hàng đã thanh toán chỉ tạo đúng một bản ghi doanh thu với số tiền chính xác')]
    public function test_02_paid_order_creates_exactly_one_revenue_with_correct_amounts(): void
    {
        $orderId = $this->createOrder('paid', 'paid', 1000000);

        $revenue = app(RevenueShareService::class)
            ->createRevenueForPaidOrder($orderId);

        $this->assertSame($orderId, (int) $revenue->order_id);
        $this->assertSame('1000000.00', (string) $revenue->gross_amount);
        $this->assertSame('705000.00', (string) $revenue->instructor_amount);
        $this->assertSame('295000.00', (string) $revenue->platform_fee_amount);

        $this->assertSame(
            1,
            DB::table('revenues')->where('order_id', $orderId)->count()
        );
    }

    #[TestDox('03. Tạo doanh thu lặp lại không được sinh bản ghi trùng')]
    public function test_03_revenue_creation_is_idempotent(): void
    {
        $orderId = $this->createOrder('paid', 'paid', 500000);

        $service = app(RevenueShareService::class);

        $first = $service->createRevenueForPaidOrder($orderId);
        $second = $service->createRevenueForPaidOrder($orderId);

        $this->assertSame((int) $first->id, (int) $second->id);
        $this->assertSame(
            1,
            DB::table('revenues')->where('order_id', $orderId)->count()
        );
    }

    #[TestDox('04. Doanh thu phải dùng quy tắc hoa hồng đã snapshot trên đơn hàng, không dùng quy tắc mới')]
    public function test_04_revenue_uses_order_commission_snapshot_not_new_active_rule(): void
    {
        $orderId = $this->createOrder('paid', 'paid', 1000000);

        DB::table('commission_rules')->insert([
            'name' => 'Later Rule',
            'description' => 'Must not affect existing order',
            'instructor_rate' => 0.8000,
            'platform_rate' => 0.2000,
            'is_active' => 0,
        ]);

        $revenue = app(RevenueShareService::class)
            ->createRevenueForPaidOrder($orderId);

        $this->assertSame($this->commissionRuleId, (int) $revenue->commission_rule_id);
        $this->assertSame('705000.00', (string) $revenue->instructor_amount);
        $this->assertSame('295000.00', (string) $revenue->platform_fee_amount);
    }

    #[TestDox('05. Phép tính hoa hồng phải làm tròn chính xác và an toàn với số thập phân')]
    public function test_05_commission_rounding_is_decimal_safe(): void
    {
        $orderId = $this->createOrder('paid', 'paid', 10001);

        $revenue = app(RevenueShareService::class)
            ->createRevenueForPaidOrder($orderId);

        $this->assertSame('10001.00', (string) $revenue->gross_amount);
        $this->assertSame(
            round((float) $revenue->gross_amount, 2),
            round(
                (float) $revenue->instructor_amount
                + (float) $revenue->platform_fee_amount,
                2
            )
        );
    }

    #[TestDox('06. Đồng bộ doanh thu còn thiếu phải idempotent, chạy lại không tạo trùng')]
    public function test_06_sync_missing_paid_revenues_is_idempotent(): void
    {
        $orderId = $this->createOrder('paid', 'paid', 450000);

        $service = app(RevenueShareService::class);

        $firstCount = $service->syncMissingPaidOrderRevenues();
        $secondCount = $service->syncMissingPaidOrderRevenues();

        $this->assertGreaterThanOrEqual(1, $firstCount);
        $this->assertSame(0, $secondCount);
        $this->assertSame(
            1,
            DB::table('revenues')->where('order_id', $orderId)->count()
        );
    }

    #[TestDox('07. Ràng buộc UNIQUE của database phải chặn doanh thu thứ hai cho cùng một đơn hàng')]
    public function test_07_database_unique_constraint_blocks_second_revenue_for_same_order(): void
    {
        $orderId = $this->createOrder('paid', 'paid', 400000);

        $this->createRevenue($orderId, 400000, 282000, 118000);

        $this->expectException(QueryException::class);

        $this->createRevenue($orderId, 400000, 282000, 118000);
    }

    #[TestDox('08. Khóa ngoại database phải chặn doanh thu tham chiếu quy tắc hoa hồng không tồn tại')]
    public function test_08_database_fk_blocks_invalid_commission_rule_on_revenue(): void
    {
        $orderId = $this->createOrder('paid', 'paid', 400000);

        $this->expectException(QueryException::class);

        DB::table('revenues')->insert([
            'instructor_id' => $this->instructorId,
            'course_id' => $this->courseId,
            'order_id' => $orderId,
            'gross_amount' => 400000,
            'instructor_amount' => 282000,
            'platform_fee_amount' => 118000,
            'commission_rule_id' => 999999999,
            'earned_at' => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // BALANCE / MANUAL WITHDRAWAL
    // -------------------------------------------------------------------------

    #[TestDox('09. Không có doanh thu thì số dư khả dụng phải bằng 0')]
    public function test_09_no_revenue_means_zero_available_balance(): void
    {
        $summary = app(EarlyWithdrawalService::class)
            ->getPaymentSummary($this->instructorId);

        $this->assertSame(0.0, (float) $summary['available_balance']);
    }

    #[TestDox('10. Số dư khả dụng phải bằng doanh thu trừ phần tiền đang được giữ bởi yêu cầu rút')]
    public function test_10_available_balance_equals_revenue_minus_active_allocation(): void
    {
        $revenueId = $this->seedRevenueAmount(500000);
        $withdrawalId = $this->createWithdrawalDirect('pending', 200000);

        $this->allocate($withdrawalId, $revenueId, 200000);

        $summary = app(EarlyWithdrawalService::class)
            ->getPaymentSummary($this->instructorId);

        $this->assertSame(300000.0, (float) $summary['available_balance']);
        $this->assertSame(200000.0, (float) $summary['reserved_balance']);
    }

    #[TestDox('11. Tài khoản nhận tiền đang chờ xác minh phải bị từ chối')]
    public function test_11_pending_verification_payout_account_is_rejected(): void
    {
        $this->seedRevenueAmount(500000);

        $accountId = DB::table('payout_accounts')->insertGetId([
            'user_id' => $this->instructorId,
            'provider' => 'VCB',
            'account_number' => '001100000011',
            'account_name' => 'PENDING ACCOUNT',
            'status' => 'pending_verification',
            'is_default' => 0,
        ]);

        $this->expectException(ValidationException::class);

        app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                200000,
                $accountId,
                $this->createOtp('123456')
            );
    }

    #[TestDox('12. Tài khoản nhận tiền đã vô hiệu hóa phải bị từ chối')]
    public function test_12_disabled_payout_account_is_rejected(): void
    {
        $this->seedRevenueAmount(500000);

        $accountId = DB::table('payout_accounts')->insertGetId([
            'user_id' => $this->instructorId,
            'provider' => 'ACB',
            'account_number' => '123456789001',
            'account_name' => 'DISABLED ACCOUNT',
            'status' => 'disabled',
            'is_default' => 0,
            'disabled_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                200000,
                $accountId,
                $this->createOtp('123456')
            );
    }

    #[TestDox('13. Không được dùng tài khoản nhận tiền của giảng viên khác')]
    public function test_13_payout_account_of_another_instructor_is_rejected(): void
    {
        $this->seedRevenueAmount(500000);

        $otherAccountId = DB::table('payout_accounts')->insertGetId([
            'user_id' => $this->otherInstructorId,
            'provider' => 'TCB',
            'account_number' => '190300000099',
            'account_name' => 'OTHER INSTRUCTOR',
            'status' => 'verified',
            'is_default' => 0,
            'verified_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                200000,
                $otherAccountId,
                $this->createOtp('123456')
            );
    }

    #[TestDox('14. Số tiền rút thấp hơn mức tối thiểu phải bị từ chối')]
    public function test_14_amount_below_minimum_is_rejected(): void
    {
        $this->seedRevenueAmount(500000);

        $this->expectException(ValidationException::class);

        app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                199999,
                $this->verifiedPayoutAccountId,
                $this->createOtp('123456')
            );
    }

    #[TestDox('15. Số tiền rút lớn hơn số dư khả dụng phải bị từ chối')]
    public function test_15_amount_above_available_balance_is_rejected(): void
    {
        $this->seedRevenueAmount(300000);

        $this->expectException(ValidationException::class);

        app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                400000,
                $this->verifiedPayoutAccountId,
                $this->createOtp('123456')
            );
    }

    #[TestDox('16. Rút đúng bằng toàn bộ số dư khả dụng phải hợp lệ')]
    public function test_16_amount_equal_to_full_balance_is_valid(): void
    {
        $this->seedRevenueAmount(300000);

        $withdrawal = app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                300000,
                $this->verifiedPayoutAccountId,
                $this->createOtp('123456')
            );

        $this->assertSame(WithdrawRequest::STATUS_PENDING, $withdrawal->status);
        $this->assertSame('300000.00', (string) $withdrawal->amount);
        $this->assertSame('300000.00', (string) $withdrawal->available_balance_before);
        $this->assertSame('0.00', (string) $withdrawal->available_balance_after);
    }

    #[TestDox('17. Yêu cầu rút tiền phải snapshot đúng tài khoản nhận tiền và phân bổ đúng số tiền')]
    public function test_17_withdrawal_snapshots_payout_account_and_allocates_exact_amount(): void
    {
        $this->seedRevenueAmount(500000);

        $withdrawal = app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                250000,
                $this->verifiedPayoutAccountId,
                $this->createOtp('123456')
            );

        $this->assertSame('GROUP45 INSTRUCTOR', $withdrawal->account_name_snapshot);
        $this->assertSame('MB', $withdrawal->bank_name_snapshot);
        $this->assertSame('MB', $withdrawal->payout_provider);

        $allocated = (float) DB::table('withdrawal_revenues')
            ->where('withdrawal_id', $withdrawal->id)
            ->sum('allocated_amount');

        $this->assertSame(250000.0, $allocated);
    }

    #[TestDox('18. Snapshot yêu cầu rút tiền không được thay đổi khi tài khoản nhận tiền bị chỉnh sửa sau đó')]
    public function test_18_snapshot_does_not_change_when_payout_account_changes(): void
    {
        $this->seedRevenueAmount(500000);

        $withdrawal = app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                200000,
                $this->verifiedPayoutAccountId,
                $this->createOtp('123456')
            );

        DB::table('payout_accounts')
            ->where('id', $this->verifiedPayoutAccountId)
            ->update([
                'account_name' => 'CHANGED NAME',
                'provider' => 'VCB',
            ]);

        $withdrawal->refresh();

        $this->assertSame('GROUP45 INSTRUCTOR', $withdrawal->account_name_snapshot);
        $this->assertSame('MB', $withdrawal->bank_name_snapshot);
    }

    #[TestDox('19. Một yêu cầu rút tiền có thể phân bổ qua nhiều bản ghi doanh thu')]
    public function test_19_withdrawal_can_allocate_across_multiple_revenues(): void
    {
        $firstRevenueId = $this->seedRevenueAmount(220000);
        $secondRevenueId = $this->seedRevenueAmount(280000);

        $withdrawal = app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                400000,
                $this->verifiedPayoutAccountId,
                $this->createOtp('123456')
            );

        $allocations = DB::table('withdrawal_revenues')
            ->where('withdrawal_id', $withdrawal->id)
            ->orderBy('revenue_id')
            ->get();

        $this->assertSame(2, $allocations->count());
        $this->assertSame(
            400000.0,
            (float) $allocations->sum('allocated_amount')
        );

        $this->assertTrue(
            $allocations->pluck('revenue_id')->contains($firstRevenueId)
            && $allocations->pluck('revenue_id')->contains($secondRevenueId)
        );
    }

    #[TestDox('20. Khi đang có yêu cầu rút tiền hoạt động thì phải chặn yêu cầu rút tiền thứ hai')]
    public function test_20_active_withdrawal_blocks_second_withdrawal(): void
    {
        $this->seedRevenueAmount(600000);

        $service = app(EarlyWithdrawalService::class);

        $service->createEarlyWithdrawal(
            $this->instructorId,
            200000,
            $this->verifiedPayoutAccountId,
            $this->createOtp('111111')
        );

        $this->expectException(BusinessException::class);

        $service->createEarlyWithdrawal(
            $this->instructorId,
            200000,
            $this->verifiedPayoutAccountId,
            $this->createOtp('222222')
        );
    }

    #[TestDox('21. Hủy yêu cầu đang chờ duyệt phải giải phóng phân bổ và hoàn lại số dư khả dụng')]
    public function test_21_cancel_pending_releases_allocations_and_restores_balance(): void
    {
        $this->seedRevenueAmount(500000);

        $service = app(EarlyWithdrawalService::class);

        $withdrawal = $service->createEarlyWithdrawal(
            $this->instructorId,
            200000,
            $this->verifiedPayoutAccountId,
            $this->createOtp('123456')
        );

        $this->assertSame(
            300000.0,
            (float) $service->getPaymentSummary($this->instructorId)['available_balance']
        );

        $this->assertTrue(
            $service->cancelEarlyWithdrawal(
                $this->instructorId,
                $withdrawal->id
            )
        );

        $withdrawal->refresh();

        $this->assertSame(WithdrawRequest::STATUS_CANCELLED, $withdrawal->status);
        $this->assertSame(
            0,
            DB::table('withdrawal_revenues')
                ->where('withdrawal_id', $withdrawal->id)
                ->count()
        );
        $this->assertSame(
            500000.0,
            (float) $service->getPaymentSummary($this->instructorId)['available_balance']
        );
    }

    #[TestDox('22. Yêu cầu rút tiền không còn ở trạng thái chờ duyệt thì không được hủy')]
    public function test_22_non_pending_withdrawal_cannot_be_cancelled(): void
    {
        $this->seedRevenueAmount(500000);

        $withdrawalId = $this->createWithdrawalDirect('approved', 200000);

        $this->expectException(BusinessException::class);

        app(EarlyWithdrawalService::class)
            ->cancelEarlyWithdrawal($this->instructorId, $withdrawalId);
    }

    // -------------------------------------------------------------------------
    // OTP SECURITY
    // -------------------------------------------------------------------------

    #[TestDox('23. OTP hết hạn phải bị từ chối')]
    public function test_23_expired_otp_is_rejected(): void
    {
        $this->seedRevenueAmount(500000);

        DB::table('user_otps')->insert([
            'user_id' => $this->instructorId,
            'purpose' => 'early_withdrawal',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->subSecond(),
            'used_at' => null,
            'attempts' => 0,
        ]);

        $this->expectException(BusinessException::class);

        app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                200000,
                $this->verifiedPayoutAccountId,
                '123456'
            );
    }

    #[TestDox('24. Nhập sai OTP phải tăng số lần thử')]
    public function test_24_wrong_otp_increments_attempts(): void
    {
        $this->seedRevenueAmount(500000);

        $otpId = DB::table('user_otps')->insertGetId([
            'user_id' => $this->instructorId,
            'purpose' => 'early_withdrawal',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'used_at' => null,
            'attempts' => 0,
        ]);

        try {
            app(EarlyWithdrawalService::class)
                ->createEarlyWithdrawal(
                    $this->instructorId,
                    200000,
                    $this->verifiedPayoutAccountId,
                    '999999'
                );

            $this->fail('Wrong OTP must be rejected.');
        } catch (BusinessException $e) {
            $this->assertSame(
                1,
                (int) DB::table('user_otps')->where('id', $otpId)->value('attempts')
            );
        }
    }

    #[TestDox('25. OTP đã sử dụng không được phép dùng lại')]
    public function test_25_used_otp_cannot_be_replayed(): void
    {
        $this->seedRevenueAmount(700000);

        $otp = '123456';
        $this->createOtp($otp);

        $service = app(EarlyWithdrawalService::class);

        $withdrawal = $service->createEarlyWithdrawal(
            $this->instructorId,
            200000,
            $this->verifiedPayoutAccountId,
            $otp
        );

        $service->cancelEarlyWithdrawal($this->instructorId, $withdrawal->id);

        $this->expectException(BusinessException::class);

        $service->createEarlyWithdrawal(
            $this->instructorId,
            200000,
            $this->verifiedPayoutAccountId,
            $otp
        );
    }

    #[TestDox('26. Khi vượt quá số lần nhập OTP cho phép thì dù nhập đúng cũng phải bị chặn')]
    public function test_26_otp_attempt_limit_blocks_even_correct_code_after_limit(): void
    {
        $this->seedRevenueAmount(500000);

        DB::table('user_otps')->insert([
            'user_id' => $this->instructorId,
            'purpose' => 'early_withdrawal',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'used_at' => null,
            'attempts' => 5,
        ]);

        $this->expectException(BusinessException::class);

        app(EarlyWithdrawalService::class)
            ->createEarlyWithdrawal(
                $this->instructorId,
                200000,
                $this->verifiedPayoutAccountId,
                '123456'
            );
    }

    #[TestDox('27. Không được bỏ qua OTP chỉ vì đang chạy môi trường test')]
    public function test_27_missing_otp_must_not_be_bypassed_in_feature_tests(): void
    {
        $this->seedRevenueAmount(500000);

        $thrown = false;

        try {
            app(EarlyWithdrawalService::class)
                ->createEarlyWithdrawal(
                    $this->instructorId,
                    200000,
                    $this->verifiedPayoutAccountId,
                    null
                );
        } catch (BusinessException $e) {
            $thrown = true;
        }

        $this->assertTrue(
            $thrown,
            'OTP cannot be bypassed merely because APP_ENV=testing.'
        );
    }

    // -------------------------------------------------------------------------
    // PAYOUT STATE MACHINE / IDEMPOTENCY
    // -------------------------------------------------------------------------

    #[TestDox('28. Dịch vụ chi tiền không được xử lý yêu cầu rút tiền còn đang chờ admin duyệt')]
    public function test_28_payout_service_must_not_process_pending_withdrawal(): void
    {
        $withdrawal = WithdrawRequest::findOrFail(
            $this->createWithdrawalDirect('pending', 200000)
        );

        $gateway = new class implements PayoutGatewayInterface {
            public int $calls = 0;

            public function processPayout(WithdrawRequest $withdrawal): array
            {
                $this->calls++;

                return [
                    'status' => 'SUCCESS',
                    'message' => 'test',
                    'provider_payout_id' => 'TEST-PENDING-' . $withdrawal->id,
                    'payout_provider' => 'test',
                ];
            }
        };

        $service = new PayoutService(
            $gateway,
            app(EarlyWithdrawalService::class)
        );

        $service->process($withdrawal);

        $withdrawal->refresh();

        $this->assertSame(
            WithdrawRequest::STATUS_PENDING,
            $withdrawal->status,
            'Payout must start only after admin approval.'
        );
        $this->assertSame(0, $gateway->calls);
    }

    #[TestDox('29. Yêu cầu đã được duyệt có thể xử lý thành đã thanh toán đúng một lần')]
    public function test_29_approved_withdrawal_can_process_to_paid_once(): void
    {
        $withdrawal = WithdrawRequest::findOrFail(
            $this->createWithdrawalDirect('approved', 200000)
        );

        $gateway = new class implements PayoutGatewayInterface {
            public int $calls = 0;

            public function processPayout(WithdrawRequest $withdrawal): array
            {
                $this->calls++;

                return [
                    'status' => 'SUCCESS',
                    'message' => 'success',
                    'provider_payout_id' => 'TEST-SUCCESS-' . $withdrawal->id,
                    'payout_provider' => 'test',
                ];
            }
        };

        $service = new PayoutService(
            $gateway,
            app(EarlyWithdrawalService::class)
        );

        $service->process($withdrawal);
        $withdrawal->refresh();

        $this->assertSame(WithdrawRequest::STATUS_PAID, $withdrawal->status);
        $this->assertNotNull($withdrawal->paid_at);
        $this->assertSame(1, $gateway->calls);

        $service->process($withdrawal);
        $this->assertSame(1, $gateway->calls);
    }

    #[TestDox('30. Webhook báo thành công gửi lặp lại phải idempotent và không xử lý trùng')]
    public function test_30_duplicate_success_webhook_is_idempotent(): void
    {
        $withdrawal = WithdrawRequest::findOrFail(
            $this->createWithdrawalDirect('processing', 200000)
        );

        $service = app(PayoutService::class);

        $service->resolveWebhook($withdrawal, 'SUCCESS');
        $firstPaidAt = WithdrawRequest::findOrFail($withdrawal->id)->paid_at;

        $service->resolveWebhook(
            WithdrawRequest::findOrFail($withdrawal->id),
            'SUCCESS'
        );

        $fresh = WithdrawRequest::findOrFail($withdrawal->id);

        $this->assertSame(WithdrawRequest::STATUS_PAID, $fresh->status);
        $this->assertEquals($firstPaidAt, $fresh->paid_at);
    }

    #[TestDox('31. Mã giao dịch chi tiền của nhà cung cấp phải UNIQUE ở mức database')]
    public function test_31_provider_payout_id_is_unique_at_database_level(): void
    {
        DB::table('withdraw_requests')
            ->where('id', $this->createWithdrawalDirect('paid', 200000))
            ->update(['provider_payout_id' => 'UNIQUE-PAYOUT-XYZ']);

        $secondId = $this->createWithdrawalDirect('paid', 200000);

        $this->expectException(QueryException::class);

        DB::table('withdraw_requests')
            ->where('id', $secondId)
            ->update(['provider_payout_id' => 'UNIQUE-PAYOUT-XYZ']);
    }

    // -------------------------------------------------------------------------
    // DB CONSTRAINTS / TRIGGERS
    // -------------------------------------------------------------------------

    #[TestDox('32. Khóa chính kép của withdrawal_revenues phải chặn cặp withdrawal-revenue bị trùng')]
    public function test_32_withdrawal_revenues_composite_pk_blocks_duplicate_pair(): void
    {
        $revenueId = $this->seedRevenueAmount(500000);
        $withdrawalId = $this->createWithdrawalDirect('pending', 200000);

        $this->allocate($withdrawalId, $revenueId, 100000);

        $this->expectException(QueryException::class);

        $this->allocate($withdrawalId, $revenueId, 100000);
    }

    #[TestDox('33. Khóa ngoại withdrawal_revenues phải chặn withdrawal không tồn tại')]
    public function test_33_withdrawal_revenues_fk_blocks_unknown_withdrawal(): void
    {
        $revenueId = $this->seedRevenueAmount(500000);

        $this->expectException(QueryException::class);

        $this->allocate(999999999, $revenueId, 100000);
    }

    #[TestDox('34. Tài khoản nhận tiền mặc định bắt buộc phải ở trạng thái đã xác minh')]
    public function test_34_default_payout_account_must_be_verified(): void
    {
        $this->expectException(QueryException::class);

        DB::table('payout_accounts')->insert([
            'user_id' => $this->otherInstructorId,
            'provider' => 'VCB',
            'account_number' => '001199999999',
            'account_name' => 'INVALID DEFAULT',
            'status' => 'pending_verification',
            'is_default' => 1,
        ]);
    }

    #[TestDox('35. Một người dùng không được có hai tài khoản nhận tiền mặc định cùng lúc')]
    public function test_35_user_cannot_have_two_default_payout_accounts(): void
    {
        DB::table('payout_accounts')->insert([
            'user_id' => $this->otherInstructorId,
            'provider' => 'VCB',
            'account_number' => '001188888881',
            'account_name' => 'DEFAULT ONE',
            'status' => 'verified',
            'is_default' => 1,
            'verified_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('payout_accounts')->insert([
            'user_id' => $this->otherInstructorId,
            'provider' => 'ACB',
            'account_number' => '001188888882',
            'account_name' => 'DEFAULT TWO',
            'status' => 'verified',
            'is_default' => 1,
            'verified_at' => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // STATIC / SCHEMA DEBT
    // -------------------------------------------------------------------------

    #[TestDox('36. Source Group 4+5 không được còn kiến trúc rút tiền tự động hàng tháng')]
    public function test_36_group45_source_has_no_monthly_payout_architecture(): void
    {
        $appRoot = base_path('app');

        foreach ([
            'Services/Payout/InstructorPayoutService.php',
            'Services/Admin/AdminPayoutService.php',
            'Repositories/Admin/AdminPayoutRepository.php',
            'Models/PayoutBatch.php',
            'Models/PayoutItem.php',
        ] as $relativePath) {
            $this->assertFileDoesNotExist(
                $appRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath)
            );
        }

        foreach ([
            'Console/Commands/GenerateMonthlyPayoutsCommand.php',
            'Console/Commands/ProcessReadyPayoutsCommand.php',
        ] as $relativePath) {
            $this->assertFileDoesNotExist(
                $appRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath)
            );
        }
    }

    #[TestDox('37. Source Group 4+5 không được còn field tài chính sai với database FINAL')]
    public function test_37_group45_source_has_no_schema_invalid_financial_fields(): void
    {
        $files = $this->group45PhpFiles();

        $forbidden = [
            'revenues.status',
            "->where('type', WithdrawRequest::TYPE_",
            'PayoutAccount::STATUS_ACTIVE',
            'commission_rule_code',
        ];

        $hits = [];

        foreach ($files as $file) {
            $source = file_get_contents($file);

            foreach ($forbidden as $needle) {
                if (str_contains($source, $needle)) {
                    $hits[] = $file . ' :: ' . $needle;
                }
            }
        }

        $this->assertSame(
            [],
            $hits,
            "Schema-invalid Group4+5 references remain:\n" . implode("\n", $hits)
        );
    }

    #[TestDox('38. AdminWithdrawalController không được ghi các field từ chối không tồn tại trong database FINAL')]
    public function test_38_admin_withdrawal_controller_does_not_write_nonexistent_reject_fields(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/AdminWithdrawalController.php')
        );

        $this->assertStringNotContainsString('->rejected_at', $source);
        $this->assertStringNotContainsString('->rejection_reason', $source);
        $this->assertStringContainsString('->rejected_reason', $source);
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function createUser(string $name, string $email, string $role): int
    {
        return DB::table('users')->insertGetId([
            'full_name' => $name,
            'email' => $email,
            'password_hash' => Hash::make('TestPassword!123'),
            'role' => $role,
            'status' => 'active',
            'locked' => 0,
            'email_verified_at' => now(),
        ]);
    }

    private function createOrder(
        string $status,
        string $paymentStatus,
        float $amount
    ): int {
        $suffix = str_replace('.', '', uniqid('', true));

        return DB::table('orders')->insertGetId([
            'order_code' => 'G45-' . $suffix,
            'user_id' => $this->learnerId,
            'course_id' => $this->courseId,
            'coupon_id' => null,
            'commission_rule_id' => $this->commissionRuleId,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'price_snapshot' => $amount,
            'discount_amount' => 0,
            'amount' => $amount,
            'payment_method' => $status === 'paid' ? 'manual' : null,
            'provider_transaction_id' => $status === 'paid'
                ? 'G45-TXN-' . $suffix
                : null,
            'paid_at' => $status === 'paid' ? now() : null,
            'expires_at' => now()->addHour(),
        ]);
    }

    private function createRevenue(
        int $orderId,
        float $gross,
        float $instructor,
        float $platform
    ): int {
        return DB::table('revenues')->insertGetId([
            'instructor_id' => $this->instructorId,
            'course_id' => $this->courseId,
            'order_id' => $orderId,
            'gross_amount' => $gross,
            'instructor_amount' => $instructor,
            'platform_fee_amount' => $platform,
            'commission_rule_id' => $this->commissionRuleId,
            'earned_at' => now(),
        ]);
    }

    private function seedRevenueAmount(float $instructorAmount): int
    {
        $gross = round($instructorAmount / 0.705, 2);
        $platform = round($gross - $instructorAmount, 2);

        $orderId = $this->createOrder('paid', 'paid', $gross);

        return $this->createRevenue(
            $orderId,
            $gross,
            $instructorAmount,
            $platform
        );
    }

    private function createWithdrawalDirect(
        string $status,
        float $amount
    ): int {
        return DB::table('withdraw_requests')->insertGetId([
            'user_id' => $this->instructorId,
            'payout_account_id' => $this->verifiedPayoutAccountId,
            'amount' => $amount,
            'status' => $status,
            'requested_at' => now(),
            'approved_at' => in_array($status, ['approved', 'processing', 'manual_required', 'paid'], true)
                ? now()
                : null,
            'paid_at' => $status === 'paid' ? now() : null,
            'processed_at' => in_array($status, ['processing', 'manual_required', 'paid', 'failed'], true)
                ? now()
                : null,
            'provider_payout_id' => null,
            'failure_reason' => null,
            'rejected_reason' => null,
            'admin_note' => null,
            'account_number_snapshot' => '970400000001',
            'account_name_snapshot' => 'GROUP45 INSTRUCTOR',
            'available_balance_before' => max($amount, 500000),
            'available_balance_after' => max(max($amount, 500000) - $amount, 0),
            'bank_name_snapshot' => 'MB',
            'payout_provider' => 'test',
        ]);
    }

    private function allocate(
        int $withdrawalId,
        int $revenueId,
        float $amount
    ): void {
        DB::table('withdrawal_revenues')->insert([
            'withdrawal_id' => $withdrawalId,
            'revenue_id' => $revenueId,
            'allocated_amount' => $amount,
            'created_at' => now(),
        ]);
    }

    private function createOtp(string $plainCode): string
    {
        DB::table('user_otps')
            ->where('user_id', $this->instructorId)
            ->where('purpose', 'early_withdrawal')
            ->delete();

        DB::table('user_otps')->insert([
            'user_id' => $this->instructorId,
            'purpose' => 'early_withdrawal',
            'code_hash' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(5),
            'used_at' => null,
            'attempts' => 0,
        ]);

        return $plainCode;
    }

    /**
     * @return array<int, string>
     */
    private function group45PhpFiles(): array
    {
        $recursiveRoots = [
            app_path('Services/Payment'),
            app_path('Services/Payout'),
            app_path('Repositories/Instructor'),
            app_path('Repositories/Admin'),
        ];

        $exactFiles = [
            app_path('Services/Instructor/InstructorWithdrawalService.php'),
            app_path('Services/Admin/AdminRevenueService.php'),
            app_path('Services/Admin/AdminPayoutAccountService.php'),
            app_path('Http/Controllers/AdminWithdrawalController.php'),
            app_path('Http/Controllers/InstructorWithdrawalController.php'),
            app_path('Http/Controllers/AdminPayoutAccountController.php'),
            app_path('Http/Controllers/InstructorPayoutAccountController.php'),
            app_path('Http/Resources/Admin/AdminRevenueResource.php'),
            app_path('Http/Resources/Admin/CommissionRuleResource.php'),
        ];

        $files = array_values(array_filter(
            $exactFiles,
            fn (string $path): bool => is_file($path)
        ));

        foreach ($recursiveRoots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $root,
                    \FilesystemIterator::SKIP_DOTS
                )
            );

            foreach ($iterator as $item) {
                if ($item->isFile() && $item->getExtension() === 'php') {
                    $files[] = $item->getPathname();
                }
            }
        }

        return array_values(array_unique($files));
    }
}

