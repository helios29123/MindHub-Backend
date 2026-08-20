<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\UserOtp;
use App\Models\WithdrawRequest;
use App\Services\Payout\EarlyWithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WithdrawalSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;
    private Course $course;
    private PayoutAccount $payoutAccount;
    private EarlyWithdrawalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EarlyWithdrawalService();

        $this->instructor = User::create([
            'full_name' => 'Test Instructor Snapshot',
            'email' => 'test-snapshot-' . uniqid() . '@example.com',
            'role' => 'instructor',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Test Course',
            'slug' => 'test-course-' . uniqid(),
            'price' => 1000000,
            'status' => 'published',
        ]);

        $this->payoutAccount = PayoutAccount::create([
            'user_id' => $this->instructor->id,
            'bank_name' => 'TCB',
            'account_number' => '123456789',
            'account_name' => 'NGUYEN VAN A',
            'provider' => 'vietqr',
            'status' => PayoutAccount::STATUS_ACTIVE,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        config([
            'revenue.early_withdrawal.enabled' => true,
            'revenue.early_withdrawal.minimum_amount' => 200000,
        ]);
    }

    private function createAvailableRevenue(float $instructorAmount): Revenue
    {
        $order = Order::create([
            'user_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_code' => 'ORD_' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'sale_source' => 'marketplace_default',
            'paid_at' => now()->subDays(35),
        ]);

        return Revenue::create([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->course->id,
            'order_id' => $order->id,
            'gross_amount' => 500000,
            'instructor_amount' => $instructorAmount,
            'platform_fee_amount' => 150000,
            'status' => Revenue::STATUS_AVAILABLE,
            'earned_at' => now()->subDays(35),
            'available_at' => now()->subDays(5),
            'sale_source' => 'marketplace_default',
            'instructor_percent' => 70.0,
            'platform_percent' => 30.0,
        ]);
    }

    private function mockOtp(string $code = '123456'): void
    {
        $otpRecord = UserOtp::where('user_id', $this->instructor->id)
            ->where('purpose', 'early_withdrawal')
            ->first();
        if ($otpRecord) {
            $otpRecord->update(['code_hash' => Hash::make($code)]);
        }
    }

    public function test_snapshots_are_saved_correctly_on_creation()
    {
        $this->createAvailableRevenue(500000);
        
        $this->service->requestOtp($this->instructor->id, 200000, $this->payoutAccount->id);
        $this->mockOtp('123456');
        
        $withdrawal = $this->service->createEarlyWithdrawal($this->instructor->id, 200000, $this->payoutAccount->id, '123456');

        $this->assertEquals(500000, $withdrawal->available_balance_before);
        $this->assertEquals(300000, $withdrawal->available_balance_after);
    }

    public function test_snapshots_are_immutable_when_status_changes()
    {
        $this->createAvailableRevenue(500000);
        
        $this->service->requestOtp($this->instructor->id, 200000, $this->payoutAccount->id);
        $this->mockOtp('123456');
        
        $withdrawal = $this->service->createEarlyWithdrawal($this->instructor->id, 200000, $this->payoutAccount->id, '123456');

        $this->assertEquals(500000, $withdrawal->available_balance_before);
        $this->assertEquals(300000, $withdrawal->available_balance_after);

        $withdrawal->status = WithdrawRequest::STATUS_REJECTED;
        $withdrawal->save();

        $withdrawal->refresh();
        $this->assertEquals(500000, $withdrawal->available_balance_before);
        $this->assertEquals(300000, $withdrawal->available_balance_after);
    }
}
