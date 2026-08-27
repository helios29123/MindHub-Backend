<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Services\Payment\OrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Group3FinancialFlowRuntimeTest extends TestCase
{
    use DatabaseTransactions;

    private int $instructorId;
    private int $learnerId;
    private int $courseId;
    private int $commissionRuleId;

    protected function setUp(): void
    {
        parent::setUp();

        if (! app()->environment('testing')) {
            $this->fail('Group3FinancialFlowRuntimeTest chỉ được phép chạy trong APP_ENV=testing.');
        }

        Mail::fake();

        // Tránh trigger "chỉ một commission rule active" bị đụng dữ liệu seed hiện có.
        DB::table('commission_rules')->update(['is_active' => 0]);

        $suffix = str_replace('.', '', uniqid('g3', true));

        $this->instructorId = (int) DB::table('users')->insertGetId([
            'full_name' => 'Giảng viên Runtime Group 3',
            'email' => "g3.instructor.{$suffix}@mindhub.test",
            'phone' => null,
            'password_hash' => bcrypt('Runtime123!'),
            'role' => 'instructor',
            'status' => 'active',
            'locked' => false,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->learnerId = (int) DB::table('users')->insertGetId([
            'full_name' => 'Học viên Runtime Group 3',
            'email' => "g3.learner.{$suffix}@mindhub.test",
            'phone' => null,
            'password_hash' => bcrypt('Runtime123!'),
            'role' => 'learner',
            'status' => 'active',
            'locked' => false,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->commissionRuleId = (int) DB::table('commission_rules')->insertGetId([
            'name' => "Runtime 70/30 {$suffix}",
            'description' => 'Rule dành riêng cho Group 3 runtime test',
            'instructor_rate' => 0.7000,
            'platform_rate' => 0.3000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->courseId = (int) DB::table('courses')->insertGetId([
            'instructor_id' => $this->instructorId,
            'title' => 'Laravel API thực chiến Runtime Group 3',
            'slug' => "laravel-api-runtime-group3-{$suffix}",
            'short_description' => 'Khóa học dùng riêng cho runtime test Group 3.',
            'description' => 'Kiểm thử luồng Order → Payment → Enrollment → Revenue.',
            'price' => 500000,
            'discount_percent' => 20,
            'course_level' => 'intermediate',
            'language' => 'vi',
            'requirements' => json_encode(['Biết PHP cơ bản'], JSON_UNESCAPED_UNICODE),
            'outcomes' => json_encode(['Xây dựng REST API'], JSON_UNESCAPED_UNICODE),
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_01_create_order_snapshots_final_price_and_final_states(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame($this->learnerId, (int) $order->user_id);
        $this->assertSame($this->courseId, (int) $order->course_id);
        $this->assertSame($this->commissionRuleId, (int) $order->commission_rule_id);

        // price=500.000, discount_percent=20% => generated sale_price=400.000
        $this->assertSame(400000.0, (float) $order->price_snapshot);
        $this->assertSame(0.0, (float) $order->discount_amount);
        $this->assertSame(400000.0, (float) $order->amount);

        $this->assertNotEmpty($order->order_code);
        $this->assertNotNull($order->expires_at);
        $this->assertNull($order->paid_at);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'commission_rule_id' => $this->commissionRuleId,
        ]);
    }

    public function test_02_create_order_is_idempotent_while_same_pending_order_exists(): void
    {
        $first = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $second = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->assertSame((int) $first->id, (int) $second->id);

        $this->assertSame(
            1,
            DB::table('orders')
                ->where('user_id', $this->learnerId)
                ->where('course_id', $this->courseId)
                ->where('status', 'pending_payment')
                ->count()
        );
    }

    public function test_03_instructor_cannot_buy_own_course(): void
    {
        try {
            $this->orderService()->createOrder(
                ['course_id' => $this->courseId],
                $this->instructorId
            );

            $this->fail('Instructor không được mua khóa học của chính mình.');
        } catch (BusinessException $e) {
            $this->assertSame(409, $this->businessExceptionStatus($e));
        }
    }

    public function test_04_unpublished_course_cannot_be_purchased(): void
    {
        DB::table('courses')
            ->where('id', $this->courseId)
            ->update([
                'status' => 'draft',
                'updated_at' => now(),
            ]);

        try {
            $this->orderService()->createOrder(
                ['course_id' => $this->courseId],
                $this->learnerId
            );

            $this->fail('Course chưa published không được phép mua.');
        } catch (BusinessException $e) {
            $this->assertSame(403, $this->businessExceptionStatus($e));
        }
    }

    public function test_05_existing_enrollment_blocks_second_purchase(): void
    {
        $paidOrderId = $this->insertOrder([
            'status' => 'paid',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'provider_transaction_id' => $this->uniqueProviderId('EXISTING-ENROLLMENT'),
        ]);

        DB::table('enrollments')->insert([
            'user_id' => $this->learnerId,
            'course_id' => $this->courseId,
            'order_id' => $paidOrderId,
            'status' => 'active',
            'progress_percent' => 0,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->orderService()->createOrder(
                ['course_id' => $this->courseId],
                $this->learnerId
            );

            $this->fail('Learner đã sở hữu/đã thanh toán course không được tạo order mới.');
        } catch (BusinessException $e) {
            $this->assertSame(409, $this->businessExceptionStatus($e));
        }
    }

    public function test_06_existing_paid_order_blocks_second_purchase_even_without_enrollment(): void
    {
        $this->insertOrder([
            'status' => 'paid',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'provider_transaction_id' => $this->uniqueProviderId('EXISTING-PAID'),
        ]);

        try {
            $this->orderService()->createOrder(
                ['course_id' => $this->courseId],
                $this->learnerId
            );

            $this->fail('Paid order cũ phải chặn tạo order mua lại.');
        } catch (BusinessException $e) {
            $this->assertSame(409, $this->businessExceptionStatus($e));
        }
    }

    public function test_07_pending_order_can_be_cancelled(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $cancelled = $this->orderService()->cancelUserOrder(
            (int) $order->id,
            $this->learnerId
        );

        $this->assertSame('cancelled', $cancelled->status);
        $this->assertSame('pending', $cancelled->payment_status);
        $this->assertSame('Người dùng hủy đơn hàng.', $cancelled->cancelled_reason);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ]);
    }

    public function test_08_paid_order_cannot_be_cancelled(): void
    {
        $orderId = $this->insertOrder([
            'status' => 'paid',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'provider_transaction_id' => $this->uniqueProviderId('PAID-CANCEL'),
        ]);

        try {
            $this->orderService()->cancelUserOrder(
                $orderId,
                $this->learnerId
            );

            $this->fail('Paid order không được phép cancel.');
        } catch (BusinessException $e) {
            $this->assertSame(409, $this->businessExceptionStatus($e));
        }
    }

    public function test_09_manual_payment_marks_order_paid_and_creates_exactly_one_enrollment_and_revenue(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $providerId = $this->uniqueProviderId('MANUAL');

        $paid = $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $providerId,
        ], $this->learnerId);

        $this->assertSame('paid', $paid->status);
        $this->assertSame('paid', $paid->payment_status);
        $this->assertSame('manual', $paid->payment_method);
        $this->assertSame($providerId, $paid->provider_transaction_id);
        $this->assertNotNull($paid->paid_at);

        $this->assertSame(
            1,
            DB::table('enrollments')->where('order_id', $order->id)->count()
        );

        $this->assertSame(
            1,
            DB::table('revenues')->where('order_id', $order->id)->count()
        );

        $this->assertDatabaseHas('enrollments', [
            'order_id' => $order->id,
            'user_id' => $this->learnerId,
            'course_id' => $this->courseId,
            'status' => 'active',
        ]);

        $revenue = DB::table('revenues')
            ->where('order_id', $order->id)
            ->first();

        $this->assertNotNull($revenue);
        $this->assertSame($this->instructorId, (int) $revenue->instructor_id);
        $this->assertSame($this->courseId, (int) $revenue->course_id);
        $this->assertSame($this->commissionRuleId, (int) $revenue->commission_rule_id);

        $this->assertEqualsWithDelta(400000.0, (float) $revenue->gross_amount, 0.01);
        $this->assertEqualsWithDelta(280000.0, (float) $revenue->instructor_amount, 0.01);
        $this->assertEqualsWithDelta(120000.0, (float) $revenue->platform_fee_amount, 0.01);

        $this->assertEqualsWithDelta(
            (float) $revenue->gross_amount,
            (float) $revenue->instructor_amount + (float) $revenue->platform_fee_amount,
            0.01
        );
    }

    public function test_10_repeating_payment_request_does_not_duplicate_financial_side_effects(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->uniqueProviderId('FIRST'),
        ], $this->learnerId);

        try {
            $this->paymentService()->storePayment([
                'order_id' => (int) $order->id,
                'payment_method' => 'manual',
                'provider_transaction_id' => $this->uniqueProviderId('SECOND'),
            ], $this->learnerId);

            $this->fail('Lần thanh toán thứ hai phải bị từ chối.');
        } catch (BusinessException $e) {
            $this->assertSame(409, $this->businessExceptionStatus($e));
        }

        $this->assertSame(
            1,
            DB::table('enrollments')->where('order_id', $order->id)->count()
        );

        $this->assertSame(
            1,
            DB::table('revenues')->where('order_id', $order->id)->count()
        );
    }

    public function test_11_revenue_uses_commission_rule_snapshotted_on_order_not_new_active_rule(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->assertSame(
            $this->commissionRuleId,
            (int) $order->commission_rule_id
        );

        DB::table('commission_rules')
            ->where('id', $this->commissionRuleId)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        $newRuleId = (int) DB::table('commission_rules')->insertGetId([
            'name' => 'Runtime 80/20 rule mới',
            'description' => 'Rule được kích hoạt sau khi order cũ đã snapshot.',
            'instructor_rate' => 0.8000,
            'platform_rate' => 0.2000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNotSame($this->commissionRuleId, $newRuleId);

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->uniqueProviderId('SNAPSHOT'),
        ], $this->learnerId);

        $revenue = DB::table('revenues')
            ->where('order_id', $order->id)
            ->first();

        $this->assertNotNull($revenue);

        // Order cũ phải giữ 70/30, không chạy theo rule mới 80/20.
        $this->assertSame($this->commissionRuleId, (int) $revenue->commission_rule_id);
        $this->assertEqualsWithDelta(280000.0, (float) $revenue->instructor_amount, 0.01);
        $this->assertEqualsWithDelta(120000.0, (float) $revenue->platform_fee_amount, 0.01);
    }

    public function test_12_referenced_commission_rate_cannot_be_mutated(): void
    {
        $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $thrown = false;

        try {
            DB::table('commission_rules')
                ->where('id', $this->commissionRuleId)
                ->update([
                    'instructor_rate' => 0.6000,
                    'platform_rate' => 0.4000,
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            $thrown = true;
        }

        $this->assertTrue(
            $thrown,
            'Commission rule đã được order tham chiếu phải immutable về tỷ lệ.'
        );
    }

    public function test_13_cancelled_failed_and_expired_orders_cannot_be_paid(): void
    {
        foreach ([
            ['status' => 'cancelled', 'payment_status' => 'pending'],
            ['status' => 'failed', 'payment_status' => 'failed'],
            ['status' => 'expired', 'payment_status' => 'expired'],
        ] as $case) {
            $orderId = $this->insertOrder([
                'status' => $case['status'],
                'payment_status' => $case['payment_status'],
                'provider_transaction_id' => null,
                'paid_at' => null,
            ]);

            try {
                $this->paymentService()->storePayment([
                    'order_id' => $orderId,
                    'payment_method' => 'manual',
                    'provider_transaction_id' => $this->uniqueProviderId(strtoupper($case['status'])),
                ], $this->learnerId);

                $this->fail("Order {$case['status']} không được phép thanh toán.");
            } catch (BusinessException $e) {
                $this->assertSame(409, $this->businessExceptionStatus($e));
            }

            $this->assertSame(
                0,
                DB::table('enrollments')->where('order_id', $orderId)->count()
            );

            $this->assertSame(
                0,
                DB::table('revenues')->where('order_id', $orderId)->count()
            );
        }
    }

    public function test_14_user_cannot_cancel_another_users_order(): void
    {
        $otherLearnerId = (int) DB::table('users')->insertGetId([
            'full_name' => 'Học viên khác Runtime Group 3',
            'email' => 'g3.other.' . uniqid() . '@mindhub.test',
            'phone' => null,
            'password_hash' => bcrypt('Runtime123!'),
            'role' => 'learner',
            'status' => 'active',
            'locked' => false,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        try {
            $this->orderService()->cancelUserOrder(
                (int) $order->id,
                $otherLearnerId
            );

            $this->fail('User khác không được hủy order không thuộc về mình.');
        } catch (BusinessException $e) {
            $this->assertSame(404, $this->businessExceptionStatus($e));
        }

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending_payment',
        ]);
    }

    public function test_15_removed_learner_confirm_sepay_route_does_not_exist(): void
    {
        $routeExists = collect(Route::getRoutes()->getRoutes())
            ->contains(function ($route): bool {
                return in_array('POST', $route->methods(), true)
                    && $route->uri() === 'api/payments/sepay/confirm';
            });

        $this->assertFalse(
            $routeExists,
            'POST api/payments/sepay/confirm phải bị loại bỏ; learner không được tự xác nhận đã thanh toán.'
        );
    }

    public function test_16_database_constraints_protect_one_enrollment_and_one_revenue_per_order(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->uniqueProviderId('CONSTRAINT'),
        ], $this->learnerId);

        $this->assertSame(
            1,
            DB::table('enrollments')->where('order_id', $order->id)->count()
        );
        $this->assertSame(
            1,
            DB::table('revenues')->where('order_id', $order->id)->count()
        );

        $duplicateEnrollmentRejected = false;

        try {
            DB::table('enrollments')->insert([
                'user_id' => $this->learnerId,
                'course_id' => $this->courseId,
                'order_id' => $order->id,
                'status' => 'active',
                'progress_percent' => 0,
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $duplicateEnrollmentRejected = true;
        }

        $this->assertTrue(
            $duplicateEnrollmentRejected,
            'DB phải chặn duplicate enrollment theo order_id/user_id+course_id.'
        );

        $revenue = DB::table('revenues')->where('order_id', $order->id)->first();

        $duplicateRevenueRejected = false;

        try {
            DB::table('revenues')->insert([
                'instructor_id' => $revenue->instructor_id,
                'course_id' => $revenue->course_id,
                'order_id' => $revenue->order_id,
                'gross_amount' => $revenue->gross_amount,
                'instructor_amount' => $revenue->instructor_amount,
                'platform_fee_amount' => $revenue->platform_fee_amount,
                'commission_rule_id' => $revenue->commission_rule_id,
                'earned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $duplicateRevenueRejected = true;
        }

        $this->assertTrue(
            $duplicateRevenueRejected,
            'DB phải chặn duplicate revenue theo order_id.'
        );
    }

    private function orderService(): OrderService
    {
        return app(OrderService::class);
    }

    private function paymentService(): PaymentService
    {
        return app(PaymentService::class);
    }

    private function insertOrder(array $overrides = []): int
    {
        $defaults = [
            'order_code' => 'G3-' . strtoupper(str_replace('.', '', uniqid('', true))),
            'user_id' => $this->learnerId,
            'course_id' => $this->courseId,
            'coupon_id' => null,
            'commission_rule_id' => $this->commissionRuleId,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'price_snapshot' => 400000,
            'discount_amount' => 0,
            'amount' => 400000,
            'payment_method' => null,
            'provider_transaction_id' => null,
            'paid_at' => null,
            'expires_at' => now()->addDay(),
            'cancelled_reason' => null,
            'failed_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return (int) DB::table('orders')->insertGetId(
            array_merge($defaults, $overrides)
        );
    }

    private function uniqueProviderId(string $prefix): string
    {
        return $prefix . '-' . strtoupper(str_replace('.', '', uniqid('', true)));
    }

    private function businessExceptionStatus(BusinessException $e): int
    {
        foreach (['getStatusCode', 'getStatus', 'getHttpStatusCode'] as $method) {
            if (method_exists($e, $method)) {
                return (int) $e->{$method}();
            }
        }

        return (int) $e->getCode();
    }
}
