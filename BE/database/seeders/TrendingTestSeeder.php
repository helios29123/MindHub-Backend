<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TrendingTestSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for faster insert
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('enrollments')->where('user_id', '>=', 90000)->delete();
        DB::table('course_reviews')->whereIn('order_id', DB::table('orders')->where('user_id', '>=', 90000)->pluck('id'))->delete();
        DB::table('orders')->where('user_id', '>=', 90000)->delete();
        DB::table('course_categories')->where('category_id', '>=', 90000)->delete();
        DB::table('courses')->where('id', '>=', 90000)->delete();
        DB::table('categories')->where('id', '>=', 90000)->delete();
        DB::table('instructor_profiles')->where('user_id', '>=', 90000)->delete();
        DB::table('users')->where('id', '>=', 90000)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();
        $passwordHash = bcrypt('password');

        // 1. Create 5 Instructors
        $instructorIds = [];
        $usersData = [];
        $profilesData = [];
        for ($i = 1; $i <= 5; $i++) {
            $id = 90000 + $i;
            $instructorIds[] = $id;
            $usersData[] = [
                'id' => $id,
                'full_name' => "Trending Instructor $i",
                'email' => "trending_inst$i@example.com",
                'password_hash' => $passwordHash,
                'role' => 'instructor',
                'status' => 'active',
                'created_at' => clone $now,
                'updated_at' => clone $now,
            ];
            $profilesData[] = [
                'user_id' => $id,
                'bio' => 'Test bio',
                'created_at' => clone $now,
                'updated_at' => clone $now,
            ];
        }

        DB::table('users')->insert($usersData);
        DB::table('instructor_profiles')->insert($profilesData);

        // 2. Create 1 Category
        $categoryId = 90000;
        DB::table('categories')->insert([
            'id' => $categoryId,
            'name' => 'Trending Category',
            'slug' => 'trending-category',
            'status' => 'active',
            'created_at' => clone $now,
            'updated_at' => clone $now,
        ]);

        // 3. Define Course Data
        $coursesData = [
            // Course A: Pinned, 0 enrolls
            [
                'id' => 90001,
                'instructor_id' => $instructorIds[0],
                'title' => 'Course A (Pinned)',
                'is_featured' => 1,
                'enroll_count' => 0,
                'completed_count' => 0,
                'rating' => 0,
                'days_ago' => 10,
            ],
            // Course B: E = 50 (max), C = 0.9 (45 completed), Rating 4.8
            [
                'id' => 90002,
                'instructor_id' => $instructorIds[1],
                'title' => 'Course B (New Star)',
                'is_featured' => 0,
                'enroll_count' => 50,
                'completed_count' => 45,
                'rating' => 4.8,
                'days_ago' => 30,
            ],
            // Course C: Quality but < 10 enrolls (9 enrolls), C = 1.0 (9 completed), Rating 5.0
            [
                'id' => 90003,
                'instructor_id' => $instructorIds[2],
                'title' => 'Course C (Under Threshold)',
                'is_featured' => 0,
                'enroll_count' => 9,
                'completed_count' => 9,
                'rating' => 5.0,
                'days_ago' => 15,
            ],
            // Course D: Old star, E = 1000, 120 days ago
            [
                'id' => 90004,
                'instructor_id' => $instructorIds[3],
                'title' => 'Course D (Old Star)',
                'is_featured' => 0,
                'enroll_count' => 1000,
                'completed_count' => 1000,
                'rating' => 4.9,
                'days_ago' => 120, // out of 90 days decay window
            ],
            // Course E: Average, E = 20, C = 0.5 (10 completed), Rating 3.0
            [
                'id' => 90005,
                'instructor_id' => $instructorIds[4],
                'title' => 'Course E (Average)',
                'is_featured' => 0,
                'enroll_count' => 20,
                'completed_count' => 10,
                'rating' => 3.0,
                'days_ago' => 45,
            ]
        ];

        $coursesInsert = [];
        $courseCategoriesInsert = [];

        foreach ($coursesData as $c) {
            $coursesInsert[] = [
                'id' => $c['id'],
                'instructor_id' => $c['instructor_id'],
                'title' => $c['title'],
                'slug' => Str::slug($c['title']),
                'status' => 'published',
                'is_featured' => $c['is_featured'],
                'price' => 500000,
                'published_at' => now()->subDays(150),
                'created_at' => now()->subDays(150),
                'updated_at' => now()->subDays(150),
            ];

            $courseCategoriesInsert[] = [
                'course_id' => $c['id'],
                'category_id' => $categoryId,
                'created_at' => clone $now,
            ];
        }

        DB::table('courses')->insert($coursesInsert);
        DB::table('course_categories')->insert($courseCategoriesInsert);

        // Prepare bulk inserts for enrollments
        $studentsInsert = [];
        $ordersInsert = [];
        $enrollmentsInsert = [];
        $reviewsInsert = [];

        $orderIdCounter = 1000000;
        foreach ($coursesData as $c) {
            $dateEnrolled = now()->subDays($c['days_ago'])->format('Y-m-d H:i:s');
            
            for ($i = 0; $i < $c['enroll_count']; $i++) {
                $uniqueStudentId = 900000 + $c['id'] * 1000 + $i;
                $studentsInsert[] = [
                    'id' => $uniqueStudentId,
                    'full_name' => "Student {$c['id']} - $i",
                    'email' => "stu_{$c['id']}_{$i}@example.com",
                    'password_hash' => $passwordHash,
                    'role' => 'student',
                    'status' => 'active',
                    'created_at' => clone $now,
                    'updated_at' => clone $now,
                ];

                $isCompleted = $i < $c['completed_count'];
                $currentOrderId = $orderIdCounter++;

                $ordersInsert[] = [
                    'id' => $currentOrderId,
                    'order_code' => 'TEST-' . uniqid() . $i,
                    'user_id' => $uniqueStudentId,
                    'course_id' => $c['id'],
                    'amount' => 500000,
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'paid_at' => $dateEnrolled,
                    'created_at' => $dateEnrolled,
                    'updated_at' => $dateEnrolled,
                ];

                $enrollmentsInsert[] = [
                    'user_id' => $uniqueStudentId,
                    'course_id' => $c['id'],
                    'order_id' => $currentOrderId,
                    'status' => $isCompleted ? 'completed' : 'active',
                    'progress_percent' => $isCompleted ? 100 : 0,
                    'enrolled_at' => $dateEnrolled,
                    'created_at' => $dateEnrolled,
                    'updated_at' => $dateEnrolled,
                ];

                if ($i === 0 && $c['rating'] > 0) {
                    $reviewsInsert[] = [
                        'order_id' => $currentOrderId,
                        'rating' => $c['rating'],
                        'comment' => 'Great course',
                        'created_at' => $dateEnrolled,
                        'updated_at' => $dateEnrolled,
                    ];
                }
            }
        }

        // Insert in chunks
        foreach (array_chunk($studentsInsert, 200) as $chunk) {
            DB::table('users')->insert($chunk);
        }
        foreach (array_chunk($ordersInsert, 200) as $chunk) {
            DB::table('orders')->insert($chunk);
        }
        foreach (array_chunk($enrollmentsInsert, 200) as $chunk) {
            DB::table('enrollments')->insert($chunk);
        }
        foreach (array_chunk($reviewsInsert, 200) as $chunk) {
            DB::table('course_reviews')->insert($chunk);
        }
    }
}
