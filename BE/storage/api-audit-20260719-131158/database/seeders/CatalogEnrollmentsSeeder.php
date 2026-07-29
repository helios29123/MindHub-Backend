<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogEnrollmentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            [
                'user_email' => 'cat.learner01@example.com',
                'course_slug' => 'cat-course-published-laravel-api-featured',
                'order_code' => 'CAT-ORDER-LARAVEL-001',
                'status' => 'active',
                'progress_percent' => 35.50,
                'enrolled_at' => $now->copy()->subDays(8),
                'completed_at' => null,
                'last_accessed_at' => $now->copy()->subDay(),
            ],
            [
                'user_email' => 'cat.learner02@example.com',
                'course_slug' => 'cat-course-published-laravel-api-featured',
                'order_code' => 'CAT-ORDER-LARAVEL-002',
                'status' => 'completed',
                'progress_percent' => 100,
                'enrolled_at' => $now->copy()->subDays(7),
                'completed_at' => $now->copy()->subDay(),
                'last_accessed_at' => $now->copy()->subDay(),
            ],
            [
                'user_email' => 'cat.learner01@example.com',
                'course_slug' => 'cat-course-published-php-mysql-best-selling',
                'order_code' => 'CAT-ORDER-PHP-001',
                'status' => 'completed',
                'progress_percent' => 100,
                'enrolled_at' => $now->copy()->subDays(15),
                'completed_at' => $now->copy()->subDays(3),
                'last_accessed_at' => $now->copy()->subDays(3),
            ],
            [
                'user_email' => 'cat.learner02@example.com',
                'course_slug' => 'cat-course-published-php-mysql-best-selling',
                'order_code' => 'CAT-ORDER-PHP-002',
                'status' => 'active',
                'progress_percent' => 60,
                'enrolled_at' => $now->copy()->subDays(14),
                'completed_at' => null,
                'last_accessed_at' => $now->copy()->subDays(2),
            ],
            [
                'user_email' => 'cat.learner03@example.com',
                'course_slug' => 'cat-course-published-php-mysql-best-selling',
                'order_code' => 'CAT-ORDER-PHP-003',
                'status' => 'active',
                'progress_percent' => 20,
                'enrolled_at' => $now->copy()->subDays(13),
                'completed_at' => null,
                'last_accessed_at' => $now->copy()->subDay(),
            ],
            [
                'user_email' => 'cat.learner01@example.com',
                'course_slug' => 'cat-course-published-react-latest',
                'order_code' => 'CAT-ORDER-REACT-001',
                'status' => 'active',
                'progress_percent' => 15,
                'enrolled_at' => $now->copy()->subDay(),
                'completed_at' => null,
                'last_accessed_at' => $now,
            ],
            [
                'user_email' => 'cat.learner02@example.com',
                'course_slug' => 'cat-course-published-free-ui-design',
                'order_code' => 'CAT-ORDER-FREE-UI-001',
                'status' => 'completed',
                'progress_percent' => 100,
                'enrolled_at' => $now->copy()->subDays(2),
                'completed_at' => $now->copy()->subDay(),
                'last_accessed_at' => $now->copy()->subDay(),
            ],
        ];

        foreach ($items as $item) {
            $userId = (int) DB::table('users')->where('email', $item['user_email'])->value('id');
            $courseId = (int) DB::table('courses')->where('slug', $item['course_slug'])->value('id');
            $orderId = (int) DB::table('orders')->where('order_code', $item['order_code'])->value('id');

            if (!$userId || !$courseId || !$orderId) {
                continue;
            }

            DB::table('enrollments')->updateOrInsert(
                ['order_id' => $orderId],
                [
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'order_id' => $orderId,
                    'status' => $item['status'],
                    'progress_percent' => $item['progress_percent'],
                    'enrolled_at' => $item['enrolled_at'],
                    'completed_at' => $item['completed_at'],
                    'last_accessed_at' => $item['last_accessed_at'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
