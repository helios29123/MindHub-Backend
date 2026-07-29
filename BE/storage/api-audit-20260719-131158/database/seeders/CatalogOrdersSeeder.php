<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $couponId = (int) DB::table('coupons')->where('code', 'CAT10')->value('id');

        $learner01Id = $this->userId('cat.learner01@example.com');
        $learner02Id = $this->userId('cat.learner02@example.com');
        $learner03Id = $this->userId('cat.learner03@example.com');

        $courseLaravelId = $this->courseId('cat-course-published-laravel-api-featured');
        $coursePhpId = $this->courseId('cat-course-published-php-mysql-best-selling');
        $courseReactId = $this->courseId('cat-course-published-react-latest');
        $courseFreeUiId = $this->courseId('cat-course-published-free-ui-design');

        $orders = [
            [
                'coupon_id' => $couponId ?: null,
                'course_id' => $courseLaravelId,
                'user_id' => $learner01Id,
                'order_code' => 'CAT-ORDER-LARAVEL-001',
                'price_snapshot' => 799000,
                'payment_method' => 'vnpay',
                'payment_status' => 'paid',
                'provider_transaction_id' => 'CAT-TXN-LARAVEL-001',
                'amount' => 719100,
                'paid_at' => $now->copy()->subDays(8),
            ],
            [
                'coupon_id' => null,
                'course_id' => $courseLaravelId,
                'user_id' => $learner02Id,
                'order_code' => 'CAT-ORDER-LARAVEL-002',
                'price_snapshot' => 799000,
                'payment_method' => 'momo',
                'payment_status' => 'paid',
                'provider_transaction_id' => 'CAT-TXN-LARAVEL-002',
                'amount' => 799000,
                'paid_at' => $now->copy()->subDays(7),
            ],
            [
                'coupon_id' => null,
                'course_id' => $coursePhpId,
                'user_id' => $learner01Id,
                'order_code' => 'CAT-ORDER-PHP-001',
                'price_snapshot' => 499000,
                'payment_method' => 'vnpay',
                'payment_status' => 'paid',
                'provider_transaction_id' => 'CAT-TXN-PHP-001',
                'amount' => 499000,
                'paid_at' => $now->copy()->subDays(15),
            ],
            [
                'coupon_id' => null,
                'course_id' => $coursePhpId,
                'user_id' => $learner02Id,
                'order_code' => 'CAT-ORDER-PHP-002',
                'price_snapshot' => 499000,
                'payment_method' => 'momo',
                'payment_status' => 'paid',
                'provider_transaction_id' => 'CAT-TXN-PHP-002',
                'amount' => 499000,
                'paid_at' => $now->copy()->subDays(14),
            ],
            [
                'coupon_id' => null,
                'course_id' => $coursePhpId,
                'user_id' => $learner03Id,
                'order_code' => 'CAT-ORDER-PHP-003',
                'price_snapshot' => 499000,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'paid',
                'provider_transaction_id' => 'CAT-TXN-PHP-003',
                'amount' => 499000,
                'paid_at' => $now->copy()->subDays(13),
            ],
            [
                'coupon_id' => null,
                'course_id' => $courseReactId,
                'user_id' => $learner01Id,
                'order_code' => 'CAT-ORDER-REACT-001',
                'price_snapshot' => 1199000,
                'payment_method' => 'vnpay',
                'payment_status' => 'paid',
                'provider_transaction_id' => 'CAT-TXN-REACT-001',
                'amount' => 1199000,
                'paid_at' => $now->copy()->subDay(),
            ],
            [
                'coupon_id' => null,
                'course_id' => $courseFreeUiId,
                'user_id' => $learner02Id,
                'order_code' => 'CAT-ORDER-FREE-UI-001',
                'price_snapshot' => 0,
                'payment_method' => 'free',
                'payment_status' => 'paid',
                'provider_transaction_id' => null,
                'amount' => 0,
                'paid_at' => $now->copy()->subDays(2),
            ],
        ];

        foreach ($orders as $order) {
            if (!$order['course_id'] || !$order['user_id']) {
                continue;
            }

            DB::table('orders')->updateOrInsert(
                ['order_code' => $order['order_code']],
                array_merge($order, [
                    'status' => 'paid',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }

    private function userId(string $email): int
    {
        return (int) DB::table('users')->where('email', $email)->value('id');
    }

    private function courseId(string $slug): int
    {
        return (int) DB::table('courses')->where('slug', $slug)->value('id');
    }
}
