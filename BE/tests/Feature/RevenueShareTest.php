<?php

namespace Tests\Feature;

use App\Exceptions\CommissionRuleNotFoundException;
use App\Exceptions\CourseInstructorMissingException;
use App\Exceptions\InvalidCommissionRuleException;
use App\Exceptions\InvalidOrderAmountException;
use App\Exceptions\OrderNotPaidException;
use App\Models\CommissionRule;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\Revenue;
use App\Models\User;
use App\Services\Payment\RevenueShareService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RevenueShareTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private Course $course;
    private RevenueShareService $revenueShareService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revenueShareService = new RevenueShareService();

        // Seed rules directly in active transaction
        (new \Database\Seeders\CommissionRuleSeeder())->run();

        // Create instructor
        $this->instructor = User::create([
            'full_name' => 'Inst ' . uniqid(),
            'email' => 'inst-' . uniqid() . '@example.com',
            'role' => 'instructor',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        // Create course
        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Test Course ' . uniqid(),
            'slug' => 'test-course-' . uniqid(),
            'price' => 500000,
            'status' => 'published',
        ]);
    }

    /**
     * CASE 1: marketplace_default 500000 => 70/30
     */
    public function test_calculate_revenue_share_with_marketplace_default()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('marketplace_default', $res['sale_source']);
        $this->assertEquals(70.0, $res['instructor_percent']);
        $this->assertEquals(30.0, $res['platform_percent']);
        $this->assertEquals(350000.0, $res['instructor_amount']);
        $this->assertEquals(150000.0, $res['platform_fee_amount']);
        $this->assertEquals('marketplace_default', $res['rule_code']);
    }

    /**
     * CASE 2: platform_ads 500000 => 37/63
     */
    public function test_calculate_revenue_share_with_platform_ads_source()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'platform_ads',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('platform_ads', $res['sale_source']);
        $this->assertEquals(37.0, $res['instructor_percent']);
        $this->assertEquals(63.0, $res['platform_percent']);
        $this->assertEquals(185000.0, $res['instructor_amount']);
        $this->assertEquals(315000.0, $res['platform_fee_amount']);
        $this->assertEquals('platform_ads', $res['rule_code']);
    }

    /**
     * CASE 3: admin_campaign 500000 => 37/63
     */
    public function test_calculate_revenue_share_with_admin_campaign_source()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'admin_campaign',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('admin_campaign', $res['sale_source']);
        $this->assertEquals(37.0, $res['instructor_percent']);
        $this->assertEquals(63.0, $res['platform_percent']);
        $this->assertEquals(185000.0, $res['instructor_amount']);
        $this->assertEquals(315000.0, $res['platform_fee_amount']);
        $this->assertEquals('admin_campaign', $res['rule_code']);
    }

    /**
     * CASE 4: instructor_coupon 500000 => 97/3
     */
    public function test_calculate_revenue_share_with_instructor_coupon()
    {
        $coupon = Coupon::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'code' => 'INST97_' . uniqid(),
            'name' => 'Inst Coupon',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'status' => 'active',
        ]);

        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'coupon_id' => $coupon->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('instructor_coupon', $res['sale_source']);
        $this->assertEquals(97.0, $res['instructor_percent']);
        $this->assertEquals(3.0, $res['platform_percent']);
        $this->assertEquals(485000.0, $res['instructor_amount']);
        $this->assertEquals(15000.0, $res['platform_fee_amount']);
        $this->assertEquals('instructor_coupon', $res['rule_code']);
    }

    /**
     * CASE 5: admin coupon 500000 => 37/63 (not 97/3)
     */
    public function test_calculate_revenue_share_with_admin_campaign_coupon()
    {
        $coupon = Coupon::create([
            'user_id' => null,
            'course_id' => $this->course->id,
            'code' => 'ADMIN37_' . uniqid(),
            'name' => 'Admin Coupon',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'status' => 'active',
        ]);

        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'coupon_id' => $coupon->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('admin_campaign', $res['sale_source']);
        $this->assertEquals(37.0, $res['instructor_percent']);
        $this->assertEquals(63.0, $res['platform_percent']);
        $this->assertEquals(185000.0, $res['instructor_amount']);
        $this->assertEquals(315000.0, $res['platform_fee_amount']);
        $this->assertEquals('admin_campaign', $res['rule_code']);
    }

    /**
     * CASE 6: instructor_referral 500000 => 97/3
     */
    public function test_calculate_revenue_share_with_instructor_referral_source()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'instructor_referral',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('instructor_referral', $res['sale_source']);
        $this->assertEquals(97.0, $res['instructor_percent']);
        $this->assertEquals(3.0, $res['platform_percent']);
        $this->assertEquals(485000.0, $res['instructor_amount']);
        $this->assertEquals(15000.0, $res['platform_fee_amount']);
        $this->assertEquals('instructor_referral', $res['rule_code']);
    }

    /**
     * CASE 7: invalid source 500000 => marketplace_default 70/30 (not default)
     */
    public function test_calculate_revenue_share_with_invalid_source_fallback()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'unknown_invalid_source',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('marketplace_default', $res['sale_source']);
        $this->assertEquals(70.0, $res['instructor_percent']);
        $this->assertEquals(30.0, $res['platform_percent']);
        $this->assertEquals(350000.0, $res['instructor_amount']);
        $this->assertEquals(150000.0, $res['platform_fee_amount']);
        $this->assertEquals('marketplace_default', $res['rule_code']);
    }

    /**
     * CASE 8: amount = 0 does not crash
     */
    public function test_calculate_revenue_share_with_zero_amount()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 0,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals('marketplace_default', $res['sale_source']);
        $this->assertEquals(70.0, $res['instructor_percent']);
        $this->assertEquals(30.0, $res['platform_percent']);
        $this->assertEquals(0.0, $res['instructor_amount']);
        $this->assertEquals(0.0, $res['platform_fee_amount']);
        $this->assertEquals('marketplace_default', $res['rule_code']);
    }

    /**
     * CASE 9: duplicate callback prevention
     */
    public function test_create_revenue_duplicate_callback_prevention()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
        ]);

        $rev1 = $this->revenueShareService->createRevenueForPaidOrder($order);
        $rev2 = $this->revenueShareService->createRevenueForPaidOrder($order);

        $this->assertEquals($rev1->id, $rev2->id);

        $count = Revenue::where('order_id', $order->id)->count();
        $this->assertEquals(1, $count);
    }

    /**
     * CASE 10: gross consistency
     */
    public function test_gross_consistency_across_all_rules()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 543217.89, // complex float
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'platform_ads',
        ]);

        $res = $this->revenueShareService->calculateForOrder($order);

        $this->assertEquals(
            (float) $order->amount,
            $res['instructor_amount'] + $res['platform_fee_amount']
        );
    }

    /**
     * CASE 11: Throws OrderNotPaidException when order status is pending
     */
    public function test_throws_exception_when_order_is_not_paid()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'sale_source' => 'marketplace_default',
        ]);

        $this->expectException(OrderNotPaidException::class);
        $this->revenueShareService->createRevenueForPaidOrder($order);
    }

    /**
     * CASE 12: Throws InvalidOrderAmountException when amount is negative
     */
    public function test_throws_exception_when_order_amount_is_negative()
    {
        $order = new Order([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => -100000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
        ]);

        $this->expectException(InvalidOrderAmountException::class);
        $this->revenueShareService->calculateForOrder($order);
    }

    /**
     * CASE 13: Throws CourseInstructorMissingException when course is deleted / missing
     */
    public function test_throws_exception_when_course_or_instructor_missing()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
        ]);

        // Force delete course to break relationship
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Course::where('id', $this->course->id)->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->expectException(CourseInstructorMissingException::class);
        $this->revenueShareService->createRevenueForPaidOrder($order);
    }

    /**
     * CASE 14: Throws InvalidCommissionRuleException when custom rule rates do not sum to 100
     */
    public function test_throws_exception_when_commission_rule_rates_do_not_sum_to_100()
    {
        CommissionRule::create([
            'sale_channel' => 'invalid_sum_channel',
            'name' => 'Bad Rule',
            'instructor_rate' => 70.00,
            'platform_rate' => 40.00, // Sum = 110
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'invalid_sum_channel',
        ]);

        $this->expectException(InvalidCommissionRuleException::class);
        $this->revenueShareService->createRevenueForPaidOrder($order);
    }

    /**
     * CASE 15: Updates commission_rule_id and sale_source on Order upon creation
     */
    public function test_order_commission_rule_id_and_sale_source_are_updated_after_revenue_creation()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'platform_ads',
        ]);

        $revenue = $this->revenueShareService->createRevenueForPaidOrder($order);

        $order = Order::find($order->id);
        $this->assertEquals('platform_ads', $order->sale_source);
        $this->assertNotNull($order->commission_rule_id);
        $this->assertEquals($order->commission_rule_id, $revenue->commission_rule_id);
    }

    /**
     * CASE 16: Signature alias calculateForPaidOrder works identically
     */
    public function test_calculate_for_paid_order_alias_works()
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
        ]);

        $revenue = $this->revenueShareService->calculateForPaidOrder($order);

        $this->assertInstanceOf(Revenue::class, $revenue);
        $this->assertEquals(350000.0, $revenue->instructor_amount);
        $this->assertEquals(150000.0, $revenue->platform_fee_amount);
    }

    /**
     * API Test: GET /api/instructor/revenue returns correct metadata
     */
    public function test_instructor_revenue_list_has_metadata_fields()
    {
        $this->withoutMiddleware();

        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'platform_ads',
            'paid_at' => now(),
        ]);

        $this->revenueShareService->createRevenueForPaidOrder($order);

        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/revenue');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'items' => [
                    '*' => [
                        'course_id',
                        'course_title',
                        'month',
                        'gross_amount',
                        'instructor_amount',
                        'platform_fee_amount',
                        'sale_source',
                        'sale_source_label',
                        'commission_rule_code',
                        'instructor_percent',
                        'platform_percent',
                    ]
                ]
            ]
        ]);

        $item = $response->json('data.items.0');
        $this->assertEquals('platform_ads', $item['sale_source']);
        $this->assertEquals('Quảng cáo nền tảng', $item['sale_source_label']);
        $this->assertEquals(37.0, $item['instructor_percent']);
        $this->assertEquals(63.0, $item['platform_percent']);
    }

    /**
     * API Test: GET /api/instructor/revenues/summary returns correct breakdown
     */
    public function test_instructor_revenue_summary_has_source_breakdown()
    {
        $this->withoutMiddleware();

        $order1 = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'platform_ads',
            'paid_at' => now(),
        ]);

        $order2 = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 300000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
            'paid_at' => now(),
        ]);

        $this->revenueShareService->createRevenueForPaidOrder($order1);
        $this->revenueShareService->createRevenueForPaidOrder($order2);

        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/revenues/summary');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'gross_amount',
                'instructor_amount',
                'platform_fee_amount',
                'source_breakdown' => [
                    '*' => [
                        'sale_source',
                        'sale_source_label',
                        'gross_revenue',
                        'instructor_revenue',
                        'platform_fee'
                    ]
                ]
            ]
        ]);

        $breakdown = $response->json('data.source_breakdown');
        $this->assertCount(2, $breakdown);

        $sources = collect($breakdown)->pluck('sale_source')->toArray();
        $this->assertContains('platform_ads', $sources);
        $this->assertContains('marketplace_default', $sources);
    }
}
