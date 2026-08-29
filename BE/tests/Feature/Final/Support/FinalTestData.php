<?php

namespace Tests\Feature\Final\Support;

use App\Models\CommissionRule;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait FinalTestData
{
    public function learner(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'full_name' => 'Học viên '.Str::random(8),
            'email' => 'learner_'.Str::uuid().'@mindhub.test',
            'phone' => null,
            'password_hash' => Hash::make('MatKhau123!'),
            'role' => User::ROLE_LEARNER,
            'status' => User::STATUS_ACTIVE,
            'locked' => false,
            'email_verified_at' => now(),
        ], $overrides));
    }

    public function instructor(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'full_name' => 'Giảng viên '.Str::random(8),
            'email' => 'instructor_'.Str::uuid().'@mindhub.test',
            'phone' => null,
            'password_hash' => Hash::make('MatKhau123!'),
            'role' => User::ROLE_INSTRUCTOR,
            'status' => User::STATUS_ACTIVE,
            'locked' => false,
            'email_verified_at' => now(),
        ], $overrides));
    }

    public function admin(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'full_name' => 'Quản trị '.Str::random(8),
            'email' => 'admin_'.Str::uuid().'@mindhub.test',
            'phone' => null,
            'password_hash' => Hash::make('MatKhau123!'),
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'locked' => false,
            'email_verified_at' => now(),
        ], $overrides));
    }

    public function course(?User $instructor = null, array $overrides = []): Course
    {
        $instructor ??= $this->instructor();

        return Course::query()->create(array_merge([
            'instructor_id' => $instructor->id,
            'title' => 'Khóa học '.Str::random(10),
            'slug' => 'khoa-hoc-'.Str::uuid(),
            'price' => 500000,
            'sale_price' => null,
            'course_level' => Course::LEVEL_BEGINNER,
            'language' => 'vi',
            'requirements' => [],
            'outcomes' => [],
            'status' => Course::STATUS_DRAFT,
            'is_featured' => false,
        ], $overrides));
    }

    public function rule(array $overrides = []): CommissionRule
    {
        return CommissionRule::query()->create(array_merge([
            'name' => 'Chia 80/20 '.Str::random(6),
            'description' => 'Rule test',
            'instructor_rate' => 0.8000,
            'platform_rate' => 0.2000,
            'is_active' => true,
        ], $overrides));
    }

    public function coupon(Course $course, array $overrides = []): Coupon
    {
        return Coupon::query()->create(array_merge([
            'code' => 'CP'.now()->format('dmy').strtoupper(Str::random(8)),
            'course_id' => $course->id,
            'campaign_type' => Coupon::CAMPAIGN_DISCOUNT,
            'discount_type' => Coupon::TYPE_PERCENT,
            'discount_value' => 20,
            'max_discount_amount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'start_at' => now()->subMinute(),
            'end_at' => now()->addDay(),
            'status' => Coupon::STATUS_ACTIVE,
        ], $overrides));
    }

    public function order(User $user, Course $course, CommissionRule $rule, array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_code' => 'MH'.now()->format('YmdHis').strtoupper(Str::random(10)),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'coupon_id' => null,
            'commission_rule_id' => $rule->id,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_status' => Order::PAYMENT_PENDING,
            'price_snapshot' => 500000,
            'discount_amount' => 0,
            'amount' => 500000,
            'payment_method' => null,
            'provider_transaction_id' => null,
            'paid_at' => null,
            'expires_at' => now()->addMinutes(30),
        ], $overrides));
    }

    public function paidOrder(User $user, Course $course, CommissionRule $rule, array $overrides = []): Order
    {
        return $this->order($user, $course, $rule, array_merge([
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => 'sepay',
            'provider_transaction_id' => 'TX'.Str::uuid(),
            'paid_at' => now(),
        ], $overrides));
    }

    public function enrollment(Order $order, array $overrides = []): Enrollment
    {
        return Enrollment::query()->create(array_merge([
            'user_id' => $order->user_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'progress_percent' => 0,
            'enrolled_at' => now(),
            'expires_at' => null,
        ], $overrides));
    }

    public function revenue(Order $order, array $overrides = []): Revenue
    {
        return Revenue::query()->create(array_merge([
            'instructor_id' => $order->course->instructor_id,
            'course_id' => $order->course_id,
            'order_id' => $order->id,
            'gross_amount' => $order->amount,
            'instructor_amount' => (float)$order->amount * 0.8,
            'platform_fee_amount' => (float)$order->amount * 0.2,
            'commission_rule_id' => $order->commission_rule_id,
            'earned_at' => now(),
        ], $overrides));
    }

    public function payoutAccount(User $instructor, array $overrides = []): PayoutAccount
    {
        return PayoutAccount::query()->create(array_merge([
            'user_id' => $instructor->id,
            'provider' => 'VCB',
            'account_number' => '001'.random_int(1000000000, 9999999999),
            'account_name' => 'NGUYEN VAN TEST',
            'status' => PayoutAccount::STATUS_VERIFIED,
            'is_default' => false,
            'verified_at' => now(),
            'disabled_at' => null,
        ], $overrides));
    }

    public function withdrawal(User $instructor, PayoutAccount $account, array $overrides = []): WithdrawRequest
    {
        return WithdrawRequest::query()->create(array_merge([
            'user_id' => $instructor->id,
            'payout_account_id' => $account->id,
            'amount' => 200000,
            'status' => WithdrawRequest::STATUS_PENDING,
            'requested_at' => now(),
            'account_number_snapshot' => $account->account_number,
            'account_name_snapshot' => $account->account_name,
            'available_balance_before' => 500000,
            'available_balance_after' => 300000,
            'bank_name_snapshot' => $account->provider,
            'payout_provider' => null,
        ], $overrides));
    }
}
