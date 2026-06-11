<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogCourseReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $reviews = [
            [
                'order_code' => 'CAT-ORDER-LARAVEL-001',
                'rating' => 5,
                'comment' => 'REVIEW_DATA Laravel rất tốt, nội dung dễ hiểu.',
                'deleted_at' => null,
            ],
            [
                'order_code' => 'CAT-ORDER-LARAVEL-002',
                'rating' => 4,
                'comment' => 'REVIEW_DATA Laravel có ví dụ thực tế.',
                'deleted_at' => null,
            ],
            [
                'order_code' => 'CAT-ORDER-PHP-001',
                'rating' => 5,
                'comment' => 'REVIEW_DATA PHP MySQL rất phù hợp người mới.',
                'deleted_at' => null,
            ],
            [
                'order_code' => 'CAT-ORDER-PHP-002',
                'rating' => 5,
                'comment' => 'REVIEW_DATA PHP MySQL dễ học.',
                'deleted_at' => null,
            ],
            [
                'order_code' => 'CAT-ORDER-PHP-003',
                'rating' => 4,
                'comment' => 'REVIEW_DATA PHP MySQL bài tập nhiều.',
                'deleted_at' => null,
            ],
            [
                'order_code' => 'CAT-ORDER-REACT-001',
                'rating' => 3,
                'comment' => 'REVIEW_DATA React mới nhất nhưng cần bổ sung bài tập.',
                'deleted_at' => null,
            ],
            [
                'order_code' => 'CAT-ORDER-FREE-UI-001',
                'rating' => 5,
                'comment' => 'REVIEW_DATA Free UI Design tốt cho người mới.',
                'deleted_at' => null,
            ],
        ];

        foreach ($reviews as $review) {
            $orderId = (int) DB::table('orders')
                ->where('order_code', $review['order_code'])
                ->value('id');

            if (!$orderId) {
                continue;
            }

            DB::table('course_reviews')->updateOrInsert(
                ['order_id' => $orderId],
                [
                    'order_id' => $orderId,
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                    'deleted_at' => $review['deleted_at'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
