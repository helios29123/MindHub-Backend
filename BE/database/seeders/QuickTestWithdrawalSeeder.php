<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuickTestWithdrawalSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Delete previously created test withdrawals if any
            $existingWithdrawalIds = DB::table('withdraw_requests')
                ->whereIn('user_id', [6, 41])
                ->pluck('id');

            if ($existingWithdrawalIds->isNotEmpty()) {
                DB::table('withdrawal_revenues')->whereIn('withdrawal_id', $existingWithdrawalIds)->delete();
                DB::table('withdraw_requests')->whereIn('id', $existingWithdrawalIds)->delete();
            }

            // 1. Withdrawal 1 for instructor 6 (instructor1@mindhub.test): PENDING
            $w1 = DB::table('withdraw_requests')->insertGetId([
                'user_id' => 6,
                'payout_account_id' => 1,
                'amount' => 500000,
                'status' => 'pending',
                'requested_at' => now()->subMinutes(30),
                'account_number_snapshot' => '1903123456789',
                'account_name_snapshot' => 'GIẢNG VIÊN MINDHUB 01',
                'bank_name_snapshot' => 'Techcombank',
                'available_balance_before' => 3832500,
                'available_balance_after' => 3332500,
                'payout_provider' => 'Techcombank',
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ]);

            DB::table('withdrawal_revenues')->insert([
                'withdrawal_id' => $w1,
                'revenue_id' => 24,
                'allocated_amount' => 500000,
                'created_at' => now()->subMinutes(30),
            ]);

            // 2. Withdrawal 2 for instructor 6 (instructor1@mindhub.test): PAID
            $w2 = DB::table('withdraw_requests')->insertGetId([
                'user_id' => 6,
                'payout_account_id' => 1,
                'amount' => 1000000,
                'status' => 'paid',
                'requested_at' => now()->subDays(2),
                'approved_at' => now()->subDays(2)->addHours(1),
                'processed_at' => now()->subDays(2)->addHours(2),
                'paid_at' => now()->subDays(2)->addHours(2),
                'provider_payout_id' => 'VCB_TXN20260901_0098',
                'account_number_snapshot' => '1903123456789',
                'account_name_snapshot' => 'GIẢNG VIÊN MINDHUB 01',
                'bank_name_snapshot' => 'Techcombank',
                'available_balance_before' => 3832500,
                'available_balance_after' => 2832500,
                'payout_provider' => 'manual',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2)->addHours(2),
            ]);

            DB::table('withdrawal_revenues')->insert([
                ['withdrawal_id' => $w2, 'revenue_id' => 25, 'allocated_amount' => 682500, 'created_at' => now()->subDays(2)],
                ['withdrawal_id' => $w2, 'revenue_id' => 26, 'allocated_amount' => 317500, 'created_at' => now()->subDays(2)],
            ]);

            // 3. Revenue & Withdrawal 3 for instructor 41 (giangvien@mindhub.test): MANUAL_REQUIRED
            $rev41 = DB::table('revenues')->where('instructor_id', 41)->value('id');
            if (!$rev41) {
                $orderId41 = DB::table('orders')->insertGetId([
                    'order_code' => 'DEMO-ORDER-WITHDRAWAL-001',
                    'user_id' => 12,
                    'course_id' => 20,
                    'commission_rule_id' => 1,
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'price_snapshot' => 2000000,
                    'discount_amount' => 0,
                    'amount' => 2000000,
                    'payment_method' => 'vnpay',
                    'provider_transaction_id' => 'TXN-DEMO-2026-001',
                    'paid_at' => now()->subDays(3),
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(3),
                ]);

                $rev41 = DB::table('revenues')->insertGetId([
                    'instructor_id' => 41,
                    'course_id' => 20,
                    'order_id' => $orderId41,
                    'gross_amount' => 2000000,
                    'instructor_amount' => 1400000,
                    'platform_fee_amount' => 600000,
                    'commission_rule_id' => 1,
                    'earned_at' => now()->subDays(3),
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(3),
                ]);
            }

            $w3 = DB::table('withdraw_requests')->insertGetId([
                'user_id' => 41,
                'payout_account_id' => 14,
                'amount' => 800000,
                'status' => 'manual_required',
                'requested_at' => now()->subDay(),
                'approved_at' => now()->subDay()->addHours(1),
                'failure_reason' => 'Cổng chuyển khoản tự động Napas timeout; Admin cần đối soát và thực hiện chi trả thủ công.',
                'account_number_snapshot' => '1903123456789',
                'account_name_snapshot' => 'GIANG VIEN MINDHUB',
                'bank_name_snapshot' => 'Techcombank',
                'available_balance_before' => 1400000,
                'available_balance_after' => 600000,
                'payout_provider' => 'manual',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay()->addHours(1),
            ]);

            DB::table('withdrawal_revenues')->insert([
                'withdrawal_id' => $w3,
                'revenue_id' => $rev41,
                'allocated_amount' => 800000,
                'created_at' => now()->subDay(),
            ]);

            echo "Seeded 3 quick test withdrawals successfully: W1={$w1}, W2={$w2}, W3={$w3}\n";
        });
    }
}
