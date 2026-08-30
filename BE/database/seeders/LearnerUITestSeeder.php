<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Order;
use App\Models\CommissionRule;
use Illuminate\Support\Facades\DB;

class LearnerUITestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'learner.active@mindhub.test')->first();
        if (!$user) {
            $this->command->error("Test user not found.");
            return;
        }

        Enrollment::where('user_id', $user->id)->delete();
        Order::where('user_id', $user->id)->delete();

        $courses = Course::with('sections.lessons')->where('status', 'published')->take(4)->get();
        if ($courses->count() < 4) {
            $this->command->error("Need at least 4 published courses.");
            return;
        }

        $ruleId = CommissionRule::first()->id ?? 1;

        $createOrderAndEnrollment = function ($course, $progressPercent, $status, $createdAt, $lastAccessedAt = null) use ($user, $ruleId) {
            $order = Order::create([
                'order_code' => 'TEST-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'course_id' => $course->id,
                'commission_rule_id' => $ruleId,
                'status' => 'paid',
                'payment_status' => 'paid',
                'price_snapshot' => $course->price ?? 0,
                'amount' => $course->price ?? 0,
                'payment_method' => 'vnpay',
                'paid_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            return Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'order_id' => $order->id,
                'status' => 'active',
                'progress_percent' => $progressPercent,
                'last_accessed_at' => $lastAccessedAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        };

        // 1. Enrolled Case (0%)
        $enrollment1 = $createOrderAndEnrollment($courses[0], 0, 'active', now());
        $this->command->info("Seeded Case 1: Enrolled (0%)");

        // 2. Learning Case
        $courseLearning = $courses[1];
        $enrollment2 = $createOrderAndEnrollment($courseLearning, 50.00, 'active', now()->subDays(5), now());
        
        $lessons = collect();
        foreach ($courseLearning->sections as $section) {
            $lessons = $lessons->merge($section->lessons);
        }

        if ($lessons->count() >= 2) {
            LessonProgress::create([
                'enrollment_id' => $enrollment2->id,
                'lesson_id' => $lessons[0]->id,
                'status' => 'completed',
                'started_at' => now()->subDays(2),
                'completed_at' => now()->subDays(1),
                'learning_duration_seconds' => 1200,
                'last_accessed_at' => now()->subDays(1),
            ]);

            LessonProgress::create([
                'enrollment_id' => $enrollment2->id,
                'lesson_id' => $lessons[1]->id,
                'status' => 'in_progress',
                'started_at' => now(),
                'learning_duration_seconds' => 600,
                'last_accessed_at' => now(),
            ]);

            if ($lessons[1]->lesson_type === 'video') {
                DB::table('video_progress')->insert([
                    'enrollment_id' => $enrollment2->id,
                    'lesson_id' => $lessons[1]->id,
                    'current_second' => min(300, (int)$lessons[1]->video_duration_seconds ?: 300),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $dates = [now(), now()->subDay(), now()->subDays(2)];
        foreach ($dates as $date) {
            DB::table('learning_daily_activity')->insert([
                'enrollment_id' => $enrollment2->id,
                'activity_date' => $date->format('Y-m-d'),
                'video_learning_seconds' => rand(900, 3000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->command->info("Seeded Case 2: Learning (Partial + Activity)");

        // 3. Completed Case
        $courseCompleted = $courses[2];
        $enrollment3 = $createOrderAndEnrollment($courseCompleted, 100.00, 'completed', now()->subDays(10), now());
        $enrollment3->update(['status' => 'completed', 'completed_at' => now()]);

        $lessonsC3 = collect();
        foreach ($courseCompleted->sections as $section) {
            $lessonsC3 = $lessonsC3->merge($section->lessons);
        }

        foreach ($lessonsC3 as $l) {
            LessonProgress::create([
                'enrollment_id' => $enrollment3->id,
                'lesson_id' => $l->id,
                'status' => 'completed',
                'started_at' => now()->subDays(9),
                'completed_at' => now()->subDays(rand(1, 8)),
                'learning_duration_seconds' => 1500,
                'last_accessed_at' => now(),
            ]);
        }
        

        $this->command->info("Seeded Case 3: Completed (100%)");
        $this->command->info("Case 4: Empty State - Course ID " . $courses[3]->id . " unenrolled.");
    }
}
