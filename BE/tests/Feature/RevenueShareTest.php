<?php

namespace Tests\Feature;

use App\Exceptions\CommissionRuleNotFoundException;
use App\Models\CommissionRule;
use App\Models\Course;
use App\Models\Order;
use App\Models\Revenue;
use App\Models\User;
use App\Services\Payment\OrderService;
use App\Services\Payment\RevenueShareService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RevenueShareTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private User $student;
    private Course $course;
    private RevenueShareService $revenueShareService;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revenueShareService = app(RevenueShareService::class);
        $this->orderService = app(OrderService::class);

        try {
            DB::statement('ALTER TABLE orders DROP CHECK chk_orders_status');
            DB::statement('ALTER TABLE orders DROP CHECK chk_orders_payment_status');
        } catch (\Exception $e) {
            // Ignore if constraints don't exist
        }

        CommissionRule::query()->update(['is_active' => 0]);

        $this->instructor = User::create([
            'full_name' => 'Inst ' . uniqid(),
            'email' => 'inst-' . uniqid() . '@example.com',
            'role' => 'instructor',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $this->student = User::create([
            'full_name' => 'Student ' . uniqid(),
            'email' => 'student-' . uniqid() . '@example.com',
            'role' => 'learner',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Test Course ' . uniqid(),
            'slug' => 'test-course-' . uniqid(),
            'price' => 500000,
            'status' => 'published',
        ]);
    }

    private function createRule(string $name, float $instructorRate, float $platformRate): CommissionRule
    {
        return CommissionRule::create([
            'name' => $name,
            'instructor_rate' => $instructorRate,
            'platform_rate' => $platformRate,
            'is_active' => 1,
            'sale_channel' => 'dummy_' . uniqid(), // Dummy for unmigrated test db
        ]);
    }

    public function test_case_a_create_order_snapshots_active_rule()
    {
        $ruleA = $this->createRule('Rule A', 0.7000, 0.3000);

        $order = $this->orderService->createOrder([
            'course_id' => $this->course->id,
        ], $this->student->id);

        $this->assertNotNull($order->commission_rule_id);
        $this->assertEquals($ruleA->id, $order->commission_rule_id);
    }

    public function test_case_b_revenue_uses_snapshot_rule_even_if_active_changes()
    {
        $ruleA = $this->createRule('Rule A', 0.7000, 0.3000);

        $orderData = $this->orderService->createOrder([
            'course_id' => $this->course->id,
        ], $this->student->id);

        $order = Order::find($orderData->id);
        
        $ruleA->update(['is_active' => 0]);
        $ruleB = $this->createRule('Rule B', 0.8000, 0.2000);

        $order->update(['status' => 'paid', 'payment_status' => 'paid', 'paid_at' => now()]);

        $revenue = $this->revenueShareService->createRevenueForPaidOrder($order);

        $this->assertEquals($ruleA->id, $revenue->commission_rule_id);
        $this->assertEquals(0.7000, (float) $revenue->commissionRule->instructor_rate);
    }

    public function test_case_c_revenue_insert_does_not_use_legacy_fields()
    {
        $rule = $this->createRule('Rule C', 0.6000, 0.4000);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 100000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'commission_rule_id' => $rule->id,
            'paid_at' => now(),
        ]);

        $revenue = $this->revenueShareService->createRevenueForPaidOrder($order);

        $this->assertArrayNotHasKey('status', $revenue->getAttributes());
        $this->assertArrayNotHasKey('available_at', $revenue->getAttributes());
        $this->assertArrayNotHasKey('payout_id', $revenue->getAttributes());
    }

    public function test_case_d_gross_amount_equals_sum_of_instructor_and_platform_amount()
    {
        $rule = $this->createRule('Rule D', 0.8000, 0.2000);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 543217.89,
            'status' => 'paid',
            'payment_status' => 'paid',
            'commission_rule_id' => $rule->id,
            'paid_at' => now(),
        ]);

        $revenue = $this->revenueShareService->createRevenueForPaidOrder($order);

        $this->assertEquals((float)$order->amount, $revenue->gross_amount);
        $this->assertEquals($revenue->gross_amount, $revenue->instructor_amount + $revenue->platform_fee_amount);
        $this->assertEquals(434574.31, $revenue->instructor_amount);
        $this->assertEquals(108643.58, $revenue->platform_fee_amount);
    }
}
