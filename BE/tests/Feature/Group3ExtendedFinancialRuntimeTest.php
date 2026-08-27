<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Services\Payment\CouponApplyService;
use App\Services\Payment\OrderExpirationService;
use App\Services\Payment\OrderService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\RevenueShareService;
use App\Services\Payment\Gateways\SePayGateway;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Group3ExtendedFinancialRuntimeTest extends TestCase
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
            $this->fail('Group3ExtendedFinancialRuntimeTest chỉ được chạy trong APP_ENV=testing.');
        }

        Mail::fake();

        DB::table('commission_rules')->update(['is_active' => 0]);

        $suffix = str_replace('.', '', uniqid('g3x', true));

        $this->instructorId = $this->createUser(
            "g3x.instructor.{$suffix}@mindhub.test",
            'instructor',
            'Giảng viên Group 3 Extended'
        );

        $this->learnerId = $this->createUser(
            "g3x.learner.{$suffix}@mindhub.test",
            'learner',
            'Học viên Group 3 Extended'
        );

        $this->commissionRuleId = (int) DB::table('commission_rules')->insertGetId([
            'name' => "Runtime 70/30 {$suffix}",
            'description' => 'Rule test Group 3 Extended',
            'instructor_rate' => 0.7000,
            'platform_rate' => 0.3000,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->courseId = $this->createCourse(
            $this->instructorId,
            "group3-extended-main-{$suffix}",
            500000,
            20
        );
    }

    // ---------------------------------------------------------------------
    // A. ORDER EXPIRY / PENDING CONFLICT
    // ---------------------------------------------------------------------

    public function test_01_pending_order_past_expires_at_is_expired_by_expiration_service(): void
    {
        $orderId = $this->insertOrder([
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'created_at' => now(),
            'expires_at' => now()->subMinute(),
        ]);

        app(OrderExpirationService::class)->expirePendingOrders(24, false);

        $order = DB::table('orders')->where('id', $orderId)->first();

        $this->assertSame(
            'expired',
            $order->status,
            'Order phải hết hạn dựa trên orders.expires_at, không phải tuổi created_at.'
        );

        $this->assertSame(
            'expired',
            $order->payment_status,
            'Khi order hết hạn, payment_status cũng phải chuyển expired để state pair nhất quán.'
        );
    }

    public function test_02_expired_order_cannot_be_paid_even_if_payment_callback_says_success(): void
    {
        $orderId = $this->insertOrder([
            'status' => 'expired',
            'payment_status' => 'expired',
            'expires_at' => now()->subMinute(),
        ]);

        try {
            $this->paymentService()->storePayment([
                'order_id' => $orderId,
                'payment_method' => 'manual',
                'provider_transaction_id' => $this->providerId('EXPIRED'),
            ], $this->learnerId);

            $this->fail('Expired order không được chuyển thành paid.');
        } catch (BusinessException $e) {
            $this->assertSame(409, $this->businessExceptionStatus($e));
        }

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'expired',
            'payment_status' => 'expired',
        ]);
    }

    public function test_03_active_pending_order_blocks_second_pending_order_for_same_user_and_course(): void
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
                ->where('payment_status', 'pending')
                ->count()
        );
    }

    public function test_04_expired_or_cancelled_old_order_allows_new_order_for_same_course(): void
    {
        $expiredId = $this->insertOrder([
            'status' => 'expired',
            'payment_status' => 'expired',
            'expires_at' => now()->subHour(),
        ]);

        $newAfterExpired = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->assertNotSame($expiredId, (int) $newAfterExpired->id);

        DB::table('orders')->where('id', $newAfterExpired->id)->update([
            'status' => 'cancelled',
            'updated_at' => now(),
        ]);

        $newAfterCancelled = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->assertNotSame((int) $newAfterExpired->id, (int) $newAfterCancelled->id);
    }

    // ---------------------------------------------------------------------
    // B. COUPON
    // ---------------------------------------------------------------------

    public function test_05_database_blocks_second_coupon_for_same_course(): void
    {
        $this->insertCoupon($this->courseId, [
            'code' => $this->couponCode('ONE'),
        ]);

        $rejected = false;

        try {
            $this->insertCoupon($this->courseId, [
                'code' => $this->couponCode('TWO'),
            ]);
        } catch (\Throwable $e) {
            $rejected = true;
        }

        $this->assertTrue(
            $rejected,
            'uq_coupons_course phải chặn coupon thứ hai cho cùng course.'
        );
    }

    public function test_06_percent_coupon_updates_coupon_id_discount_amount_and_final_amount_correctly(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $couponId = $this->insertCoupon($this->courseId, [
            'code' => $this->couponCode('PERCENT25'),
            'discount_type' => 'percent',
            'discount_value' => 25,
            'status' => 'active',
        ]);

        $updated = app(CouponApplyService::class)->apply([
            'order_id' => (int) $order->id,
            'coupon_code' => DB::table('coupons')->where('id', $couponId)->value('code'),
        ], $this->learnerId);

        // Course sale_price = 400,000. Coupon 25% => discount 100,000 => amount 300,000.
        $this->assertSame($couponId, (int) $updated->coupon_id);
        $this->assertEqualsWithDelta(100000.0, (float) $updated->discount_amount, 0.01);
        $this->assertEqualsWithDelta(300000.0, (float) $updated->amount, 0.01);
    }

    public function test_07_fixed_coupon_updates_discount_amount_and_final_amount_correctly(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $couponId = $this->insertCoupon($this->courseId, [
            'code' => $this->couponCode('FIXED50'),
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'status' => 'active',
        ]);

        $updated = app(CouponApplyService::class)->apply([
            'order_id' => (int) $order->id,
            'coupon_code' => DB::table('coupons')->where('id', $couponId)->value('code'),
        ], $this->learnerId);

        $this->assertEqualsWithDelta(50000.0, (float) $updated->discount_amount, 0.01);
        $this->assertEqualsWithDelta(350000.0, (float) $updated->amount, 0.01);
    }

    public function test_08_inactive_expired_used_up_and_out_of_window_coupons_are_rejected(): void
    {
        $cases = [
            'inactive' => [
                'status' => 'inactive',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
                'usage_limit' => 10,
                'used_count' => 0,
            ],
            'expired_status' => [
                'status' => 'expired',
                'start_at' => now()->subDays(2),
                'end_at' => now()->subDay(),
                'usage_limit' => 10,
                'used_count' => 0,
            ],
            'used_up' => [
                'status' => 'used_up',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
                'usage_limit' => 1,
                'used_count' => 1,
            ],
            'before_start' => [
                'status' => 'active',
                'start_at' => now()->addHour(),
                'end_at' => now()->addDays(2),
                'usage_limit' => 10,
                'used_count' => 0,
            ],
            'after_end' => [
                'status' => 'active',
                'start_at' => now()->subDays(2),
                'end_at' => now()->subMinute(),
                'usage_limit' => 10,
                'used_count' => 0,
            ],
        ];

        foreach ($cases as $label => $couponOverrides) {
            $courseId = $this->createCourse(
                $this->instructorId,
                'coupon-case-' . $label . '-' . uniqid(),
                200000,
                0
            );

            $order = $this->orderService()->createOrder(
                ['course_id' => $courseId],
                $this->learnerId
            );

            $couponId = $this->insertCoupon($courseId, array_merge([
                'code' => $this->couponCode(strtoupper($label)),
                'discount_type' => 'percent',
                'discount_value' => 10,
            ], $couponOverrides));

            $couponCode = DB::table('coupons')->where('id', $couponId)->value('code');

            try {
                app(CouponApplyService::class)->apply([
                    'order_id' => (int) $order->id,
                    'coupon_code' => $couponCode,
                ], $this->learnerId);

                $this->fail("Coupon case {$label} phải bị từ chối.");
            } catch (BusinessException $e) {
                $this->assertTrue(
                    in_array($this->businessExceptionStatus($e), [400, 409, 422], true),
                    "Coupon case {$label} phải bị reject bằng business error."
                );
            }
        }
    }

    public function test_09_coupon_for_another_course_cannot_be_applied(): void
    {
        $otherCourseId = $this->createCourse(
            $this->instructorId,
            'other-course-' . uniqid(),
            300000,
            0
        );

        $couponId = $this->insertCoupon($otherCourseId, [
            'code' => $this->couponCode('OTHER'),
        ]);

        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        try {
            app(CouponApplyService::class)->apply([
                'order_id' => (int) $order->id,
                'coupon_code' => DB::table('coupons')->where('id', $couponId)->value('code'),
            ], $this->learnerId);

            $this->fail('Coupon của course khác không được áp dụng.');
        } catch (BusinessException $e) {
            $this->assertTrue(
                in_array($this->businessExceptionStatus($e), [400, 409, 422], true)
            );
        }
    }

    public function test_10_pending_coupon_does_not_increment_used_count_and_cancel_keeps_it_unchanged(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $couponId = $this->insertCoupon($this->courseId, [
            'code' => $this->couponCode('PENDING'),
            'usage_limit' => 2,
            'used_count' => 0,
        ]);

        app(CouponApplyService::class)->apply([
            'order_id' => (int) $order->id,
            'coupon_code' => DB::table('coupons')->where('id', $couponId)->value('code'),
        ], $this->learnerId);

        $this->assertSame(
            0,
            (int) DB::table('coupons')->where('id', $couponId)->value('used_count'),
            'Tạo/apply coupon ở pending không được tăng used_count.'
        );

        $this->orderService()->cancelUserOrder((int) $order->id, $this->learnerId);

        $this->assertSame(
            0,
            (int) DB::table('coupons')->where('id', $couponId)->value('used_count'),
            'Cancel pending không được làm used_count âm hoặc thay đổi sai.'
        );
    }

    public function test_11_coupon_used_count_increments_only_after_successful_payment_and_becomes_used_up_at_limit(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $couponId = $this->insertCoupon($this->courseId, [
            'code' => $this->couponCode('LIMIT1'),
            'usage_limit' => 1,
            'used_count' => 0,
            'status' => 'active',
        ]);

        app(CouponApplyService::class)->apply([
            'order_id' => (int) $order->id,
            'coupon_code' => DB::table('coupons')->where('id', $couponId)->value('code'),
        ], $this->learnerId);

        $this->assertSame(
            0,
            (int) DB::table('coupons')->where('id', $couponId)->value('used_count')
        );

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->providerId('COUPON-PAID'),
        ], $this->learnerId);

        $coupon = DB::table('coupons')->where('id', $couponId)->first();

        $this->assertSame(1, (int) $coupon->used_count);
        $this->assertSame('used_up', $coupon->status);
    }

    // ---------------------------------------------------------------------
    // C. PAYMENT / CALLBACK DATA INTEGRITY
    // ---------------------------------------------------------------------

    public function test_12_provider_transaction_id_is_unique_across_orders(): void
    {
        $providerId = $this->providerId('UNIQUE');

        $firstOrderId = $this->insertOrder([
            'provider_transaction_id' => $providerId,
        ]);

        $otherCourseId = $this->createCourse(
            $this->instructorId,
            'provider-unique-' . uniqid(),
            250000,
            0
        );

        $rejected = false;

        try {
            $this->insertOrder([
                'course_id' => $otherCourseId,
                'order_code' => 'G3X-' . strtoupper(uniqid()),
                'provider_transaction_id' => $providerId,
            ]);
        } catch (\Throwable $e) {
            $rejected = true;
        }

        $this->assertTrue($rejected, 'DB phải chặn provider_transaction_id trùng.');
        $this->assertDatabaseHas('orders', ['id' => $firstOrderId]);
    }

    public function test_13_successful_payment_sets_paid_at_to_current_time(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $before = now()->subSecond();

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->providerId('PAIDAT'),
        ], $this->learnerId);

        $paidAt = DB::table('orders')->where('id', $order->id)->value('paid_at');

        $this->assertNotNull($paidAt);
        $this->assertTrue(
            \Carbon\Carbon::parse($paidAt)->greaterThanOrEqualTo($before)
        );
    }

    public function test_14_generic_webhook_rejects_underpayment_and_overpayment_instead_of_accepting_any_amount_above_expected(): void
    {
        config(['sepay.webhook_secret' => 'group3-test-secret']);

        foreach ([
            'under' => 399000,
            'over' => 401000,
        ] as $label => $amount) {
            $courseId = $this->createCourse(
                $this->instructorId,
                'webhook-amount-' . $label . '-' . uniqid(),
                400000,
                0
            );

            $order = $this->orderService()->createOrder(
                ['course_id' => $courseId],
                $this->learnerId
            );

            $payload = [
                'gateway' => 'MBBank',
                'transactionDate' => now()->format('Y-m-d H:i:s'),
                'transferAmount' => $amount,
                'content' => 'MIND' . $order->id,
                'referenceCode' => $this->providerId(strtoupper($label)),
            ];

            $response = $this->postSignedWebhook('/api/payments/webhook', $payload);

            $response->assertStatus(422);

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'pending_payment',
                'payment_status' => 'pending',
            ]);
        }
    }

    public function test_15_generic_webhook_exact_amount_marks_paid_and_creates_side_effects_once(): void
    {
        config(['sepay.webhook_secret' => 'group3-test-secret']);

        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $payload = [
            'gateway' => 'MBBank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'transferAmount' => 400000,
            'content' => 'MIND' . $order->id,
            'referenceCode' => $this->providerId('WEBHOOK-OK'),
        ];

        $response = $this->postSignedWebhook('/api/payments/webhook', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);

        $this->assertSame(
            1,
            DB::table('enrollments')->where('order_id', $order->id)->count()
        );

        $this->assertSame(
            1,
            DB::table('revenues')->where('order_id', $order->id)->count()
        );
    }

    public function test_16_expired_order_cannot_be_paid_by_valid_signed_webhook(): void
    {
        config(['sepay.webhook_secret' => 'group3-test-secret']);

        $orderId = $this->insertOrder([
            'status' => 'expired',
            'payment_status' => 'expired',
            'amount' => 400000,
            'expires_at' => now()->subMinute(),
        ]);

        $payload = [
            'gateway' => 'MBBank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'transferAmount' => 400000,
            'content' => 'MIND' . $orderId,
            'referenceCode' => $this->providerId('EXPIRED-WEBHOOK'),
        ];

        $response = $this->postSignedWebhook('/api/payments/webhook', $payload);

        $this->assertTrue(
            in_array($response->getStatusCode(), [409, 422], true),
            'Expired order phải bị từ chối kể cả chữ ký và amount hợp lệ.'
        );

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'expired',
            'payment_status' => 'expired',
        ]);
    }

    // ---------------------------------------------------------------------
    // D. REVENUE / ENROLLMENT SIDE EFFECT
    // ---------------------------------------------------------------------

    public function test_17_revenue_amounts_follow_order_amount_and_snapshotted_commission_rule(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->providerId('REV'),
        ], $this->learnerId);

        $freshOrder = DB::table('orders')->where('id', $order->id)->first();
        $revenue = DB::table('revenues')->where('order_id', $order->id)->first();

        $this->assertNotNull($revenue);
        $this->assertEqualsWithDelta((float) $freshOrder->amount, (float) $revenue->gross_amount, 0.01);
        $this->assertEqualsWithDelta(
            (float) $revenue->gross_amount,
            (float) $revenue->instructor_amount + (float) $revenue->platform_fee_amount,
            0.01
        );
        $this->assertEqualsWithDelta(120000.0, (float) $revenue->platform_fee_amount, 0.01);
        $this->assertSame($this->commissionRuleId, (int) $revenue->commission_rule_id);
    }

    public function test_18_revenue_cannot_be_created_for_unpaid_order(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $thrown = false;

        try {
            app(RevenueShareService::class)->createRevenueForPaidOrder((int) $order->id);
        } catch (\Throwable $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertSame(
            0,
            DB::table('revenues')->where('order_id', $order->id)->count()
        );
    }

    public function test_19_enrollment_is_active_matches_order_owner_and_course_and_expires_at_is_null_without_access_duration_policy(): void
    {
        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->providerId('ENROLL'),
        ], $this->learnerId);

        $enrollment = DB::table('enrollments')
            ->where('order_id', $order->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertSame('active', $enrollment->status);
        $this->assertSame($this->learnerId, (int) $enrollment->user_id);
        $this->assertSame($this->courseId, (int) $enrollment->course_id);
        $this->assertNull(
            $enrollment->expires_at,
            'Hiện Course schema chưa có access-duration policy nên expires_at không được tự bịa.'
        );
    }

    // ---------------------------------------------------------------------
    // E. WISHLIST
    // ---------------------------------------------------------------------

    public function test_20_successful_purchase_removes_course_from_wishlist(): void
    {
        DB::table('wishlist')->insert([
            'user_id' => $this->learnerId,
            'course_id' => $this->courseId,
            'created_at' => now(),
        ]);

        $order = $this->orderService()->createOrder(
            ['course_id' => $this->courseId],
            $this->learnerId
        );

        $this->paymentService()->storePayment([
            'order_id' => (int) $order->id,
            'payment_method' => 'manual',
            'provider_transaction_id' => $this->providerId('WISHLIST'),
        ], $this->learnerId);

        $this->assertDatabaseMissing('wishlist', [
            'user_id' => $this->learnerId,
            'course_id' => $this->courseId,
        ]);
    }

    public function test_21_pending_or_failed_payment_does_not_remove_wishlist(): void
    {
        foreach (['pending', 'failed'] as $case) {
            $courseId = $this->createCourse(
                $this->instructorId,
                'wishlist-' . $case . '-' . uniqid(),
                180000,
                0
            );

            DB::table('wishlist')->insert([
                'user_id' => $this->learnerId,
                'course_id' => $courseId,
                'created_at' => now(),
            ]);

            if ($case === 'failed') {
                $this->insertOrder([
                    'course_id' => $courseId,
                    'status' => 'failed',
                    'payment_status' => 'failed',
                ]);
            } else {
                $this->orderService()->createOrder(
                    ['course_id' => $courseId],
                    $this->learnerId
                );
            }

            $this->assertDatabaseHas('wishlist', [
                'user_id' => $this->learnerId,
                'course_id' => $courseId,
            ]);
        }
    }

    // ---------------------------------------------------------------------
    // F. SECURITY
    // ---------------------------------------------------------------------

    public function test_22_order_and_payment_creation_routes_require_auth_session_middleware(): void
    {
        foreach ([
            ['POST', 'api/orders'],
            ['POST', 'api/payments'],
            ['POST', 'api/payments/sepay/create'],
        ] as [$method, $uri]) {
            $route = $this->findRoute($method, $uri);

            $this->assertNotNull($route, "Không tìm thấy route {$method} {$uri}");

            $middleware = $route->gatherMiddleware();

            $this->assertContains(
                'auth.session',
                $middleware,
                "{$method} {$uri} phải có auth.session."
            );
        }
    }

    public function test_23_payment_webhook_routes_are_public_but_dedicated_sepay_webhook_must_reject_missing_signature(): void
    {
        $genericRoute = $this->findRoute('POST', 'api/payments/webhook');
        $sepayRoute = $this->findRoute('POST', 'api/payments/sepay/webhook');

        $this->assertNotNull($genericRoute);
        $this->assertNotNull($sepayRoute);

        $this->assertNotContains('auth.session', $genericRoute->gatherMiddleware());
        $this->assertNotContains('auth.session', $sepayRoute->gatherMiddleware());

        config(['sepay.webhook_secret' => 'group3-test-secret']);

        $response = $this->postJson('/api/payments/sepay/webhook', [
            'gateway' => 'MBBank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'transferAmount' => 400000,
            'content' => 'MIND999999999',
            'referenceCode' => $this->providerId('NO-SIGNATURE'),
        ]);

        $this->assertSame(
            401,
            $response->getStatusCode(),
            'Dedicated SePay webhook public nhưng bắt buộc phải verify signature.'
        );
    }

    public function test_24_generic_webhook_rejects_missing_or_invalid_signature(): void
    {
        config(['sepay.webhook_secret' => 'group3-test-secret']);

        $payload = [
            'gateway' => 'MBBank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'transferAmount' => 400000,
            'content' => 'MIND123',
            'referenceCode' => $this->providerId('SIG'),
        ];

        $missing = $this->postJson('/api/payments/webhook', $payload);
        $this->assertSame(401, $missing->getStatusCode());

        $invalid = $this->withHeader('X-SePay-Signature', 'invalid-signature')
            ->postJson('/api/payments/webhook', $payload);

        $this->assertSame(401, $invalid->getStatusCode());
    }

    public function test_25_valid_signature_is_accepted_by_gateway_verification_layer(): void
    {
        config(['sepay.webhook_secret' => 'group3-test-secret']);

        $payload = [
            'gateway' => 'MBBank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'transferAmount' => 400000,
            'content' => 'MIND' . $this->insertOrder(),
            'referenceCode' => $this->providerId('VALID-SIG'),
        ];

        $response = $this->postSignedWebhook('/api/payments/webhook', $payload);

        $this->assertNotSame(
            401,
            $response->getStatusCode(),
            'Chữ ký HMAC đúng không được bị reject ở security layer.'
        );
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function createUser(string $email, string $role, string $name): int
    {
        return (int) DB::table('users')->insertGetId([
            'full_name' => $name,
            'email' => $email,
            'phone' => null,
            'password_hash' => bcrypt('Runtime123!'),
            'role' => $role,
            'status' => 'active',
            'locked' => false,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCourse(
        int $instructorId,
        string $slug,
        float $price,
        float $discountPercent
    ): int {
        return (int) DB::table('courses')->insertGetId([
            'instructor_id' => $instructorId,
            'title' => 'Course Runtime ' . $slug,
            'slug' => $slug,
            'short_description' => 'Group 3 Extended runtime course',
            'description' => 'Group 3 Extended runtime course',
            'price' => $price,
            'discount_percent' => $discountPercent,
            'course_level' => 'intermediate',
            'language' => 'vi',
            'requirements' => json_encode(['PHP'], JSON_UNESCAPED_UNICODE),
            'outcomes' => json_encode(['API'], JSON_UNESCAPED_UNICODE),
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertCoupon(int $courseId, array $overrides = []): int
    {
        $defaults = [
            'code' => $this->couponCode('DEFAULT'),
            'course_id' => $courseId,
            'discount_type' => 'percent',
            'discount_value' => 10,
            'usage_limit' => 10,
            'used_count' => 0,
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return (int) DB::table('coupons')->insertGetId(
            array_merge($defaults, $overrides)
        );
    }

    private function insertOrder(array $overrides = []): int
    {
        $defaults = [
            'order_code' => 'G3X-' . strtoupper(str_replace('.', '', uniqid('', true))),
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

    private function orderService(): OrderService
    {
        return app(OrderService::class);
    }

    private function paymentService(): PaymentService
    {
        return app(PaymentService::class);
    }

    private function providerId(string $prefix): string
    {
        return $prefix . '-' . strtoupper(str_replace('.', '', uniqid('', true)));
    }

    private function couponCode(string $prefix): string
    {
        return strtoupper(substr($prefix, 0, 20))
            . '-'
            . strtoupper(substr(str_replace('.', '', uniqid('', true)), -10));
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

    private function findRoute(string $method, string $uri)
    {
        return collect(Route::getRoutes()->getRoutes())
            ->first(function ($route) use ($method, $uri) {
                return in_array(strtoupper($method), $route->methods(), true)
                    && $route->uri() === $uri;
            });
    }

    private function postSignedWebhook(string $uri, array $payload)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $signature = hash_hmac(
            'sha256',
            $body,
            (string) config('sepay.webhook_secret')
        );

        return $this->call(
            'POST',
            $uri,
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_SEPAY_SIGNATURE' => $signature,
            ],
            $body
        );
    }
}
