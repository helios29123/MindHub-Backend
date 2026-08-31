<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Revenue;
use App\Models\Session;
use App\Models\User;
use App\Services\Auth\AccessTokenService;
use App\Services\Payment\RevenueShareService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\Support\FinalTestData;
use Tests\TestCase;

final class Group9FinalBusinessRuntimeTest extends TestCase
{
    use DatabaseTransactions;
    use FinalTestData;

    protected function tokenForUser(int|User $user): string
    {
        $userId = $user instanceof User ? $user->id : $user;

        $session = Session::create([
            'user_id' => $userId,
            'refresh_token_hash' => hash('sha256', Str::random(80)),
            'device_name' => 'PHPUnit Runtime',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'MindHub Group9 Test',
            'expires_at' => now()->addDay(),
        ]);

        return app(AccessTokenService::class)
            ->createAccessToken((int) $userId, (int) $session->id)['token'];
    }

    protected function authHeader(int|User $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenForUser($user)];
    }

    protected function localSection(int $courseId, array $x = []): int
    {
        return (int) DB::table('course_sections')->insertGetId(array_merge([
            'course_id' => $courseId,
            'title' => 'Chương ' . $this->token('sec'),
            'sort_order' => 0,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localLesson(int $courseId, int $sectionId, array $x = []): int
    {
        static $order = 1;
        return (int) DB::table('lessons')->insertGetId(array_merge([
            'course_id' => $courseId,
            'course_section_id' => $sectionId,
            'title' => 'Bài học ' . $this->token('les'),
            'lesson_type' => 'video',
            'video_duration_seconds' => 600,
            'status' => 'published',
            'sort_order' => $order++,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localEnrollment(int $userId, int $courseId, array $x = []): int
    {
        $ruleId = $this->rule();
        $orderId = $this->order($userId, $courseId, $ruleId, [
            'status' => 'paid',
            'payment_status' => 'paid',
            'amount' => 500000,
            'paid_at' => now(),
        ]);

        return (int) DB::table('enrollments')->insertGetId(array_merge([
            'user_id' => $userId,
            'course_id' => $courseId,
            'order_id' => $orderId,
            'status' => 'active',
            'progress_percent' => 0.0,
            'enrolled_at' => now(),
            'expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localLessonProgress(int $enrollmentId, int $lessonId, array $x = []): int
    {
        return (int) DB::table('lesson_progress')->insertGetId(array_merge([
            'enrollment_id' => $enrollmentId,
            'lesson_id' => $lessonId,
            'status' => 'in_progress',
            'started_at' => now(),
            'completed_at' => null,
            'learning_duration_seconds' => 300,
            'last_accessed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localDailyActivity(int $enrollmentId, array $x = []): int
    {
        return (int) DB::table('learning_daily_activity')->insertGetId(array_merge([
            'enrollment_id' => $enrollmentId,
            'activity_date' => now()->toDateString(),
            'video_learning_seconds' => 1200,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    #[TestDox('01. Completion Rate: Mẫu số chỉ tính enrollments đã thực sự bắt đầu học')]
    public function test_01_completion_rate_denominator_only_counts_started_learning(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        // Learner 1: Started learning and completed
        $learner1 = $this->user('learner');
        $e1 = $this->localEnrollment($learner1, $course, ['status' => 'completed', 'progress_percent' => 100]);
        $this->localLessonProgress($e1, $lesson, ['status' => 'completed', 'completed_at' => now()]);

        // Learner 2: Started learning, not completed
        $learner2 = $this->user('learner');
        $e2 = $this->localEnrollment($learner2, $course, ['status' => 'active', 'progress_percent' => 50]);
        $this->localLessonProgress($e2, $lesson, ['status' => 'in_progress']);

        // Learner 3: Bought but NEVER opened any lesson (no lesson_progress)
        $learner3 = $this->user('learner');
        $this->localEnrollment($learner3, $course, ['status' => 'active', 'progress_percent' => 0]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/completion-rate');

        $response->assertStatus(200);

        $data = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $courseReport = $data->firstWhere('course_id', $course);

        $this->assertNotNull($courseReport);
        $this->assertEquals(3, $courseReport['total_enrollments']);
        $this->assertEquals(2, $courseReport['started_enrollments']);
        $this->assertEquals(1, $courseReport['completed_enrollments']);
        // 1 completed / 2 started = 50% (NOT 1 / 3 = 33.3%)
        $this->assertEquals(50.0, (float) $courseReport['completion_rate_percent']);
    }

    #[TestDox('02. Inactive Learner: Loại trừ Expired Trial và kiểm tra 14 ngày')]
    public function test_02_inactive_learner_excludes_expired_trial_and_checks_14_days(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        // Learner 1: Inactive paid learner (age 20 days, no activity 20 days)
        $learner1 = $this->user('learner');
        $e1 = $this->localEnrollment($learner1, $course, [
            'enrolled_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'last_accessed_at' => now()->subDays(20),
        ]);
        $this->localLessonProgress($e1, $lesson, [
            'last_accessed_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        // Learner 2: Expired Trial (should be excluded)
        $learner2 = $this->user('learner');
        $e2 = $this->localEnrollment($learner2, $course, [
            'enrolled_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'expires_at' => now()->subDays(10), // expired trial
        ]);
        $this->localLessonProgress($e2, $lesson, [
            'last_accessed_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        // Learner 3: Active learner recently studied (studied 2 days ago)
        $learner3 = $this->user('learner');
        $e3 = $this->localEnrollment($learner3, $course, [
            'enrolled_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
        ]);
        $this->localDailyActivity($e3, [
            'activity_date' => now()->subDays(2)->toDateString(),
            'video_learning_seconds' => 1200,
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);

        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $learnerIds = $items->pluck('learner_id')->all();

        $this->assertContains($learner1, $learnerIds);
        $this->assertNotContains($learner2, $learnerIds, 'Expired trial must be excluded from inactive learners');
        $this->assertNotContains($learner3, $learnerIds, 'Active learner must not be listed as inactive');
    }

    #[TestDox('03. Learner Risk: Thỏa mãn đồng thời 3 điều kiện và loại bỏ Trial')]
    public function test_03_learner_risk_simultaneous_conditions_and_no_trial(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        // Learner 1: At Risk (age 16 days, progress 15%, no activity 10 days, not trial)
        $learner1 = $this->user('learner');
        $e1 = $this->localEnrollment($learner1, $course, [
            'enrolled_at' => now()->subDays(16),
            'created_at' => now()->subDays(16),
            'progress_percent' => 15.0,
            'expires_at' => null,
        ]);
        $this->localDailyActivity($e1, [
            'activity_date' => now()->subDays(10)->toDateString(),
            'video_learning_seconds' => 600,
        ]);

        // Learner 2: Trial learner with low progress (Trial must be excluded)
        $learner2 = $this->user('learner');
        $this->localEnrollment($learner2, $course, [
            'enrolled_at' => now()->subDays(16),
            'created_at' => now()->subDays(16),
            'progress_percent' => 10.0,
            'expires_at' => now()->addDays(2), // Trial active
        ]);

        // Learner 3: High progress (progress 60%, age 20 days, no activity 10 days -> NOT at risk)
        $learner3 = $this->user('learner');
        $this->localEnrollment($learner3, $course, [
            'enrolled_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'progress_percent' => 60.0,
            'expires_at' => null,
        ]);

        // Learner 4: New enrollment (age 5 days, progress 0% -> NOT at risk yet)
        $learner4 = $this->user('learner');
        $this->localEnrollment($learner4, $course, [
            'enrolled_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
            'progress_percent' => 0.0,
            'expires_at' => null,
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);

        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $riskLearnerIds = $items->pluck('learner_id')->all();

        $this->assertContains($learner1, $riskLearnerIds, 'Eligible at-risk learner must be found');
        $this->assertNotContains($learner2, $riskLearnerIds, 'Trial learner must be excluded from risk');
        $this->assertNotContains($learner3, $riskLearnerIds, 'Learner with progress >= 30% must not be at risk');
        $this->assertNotContains($learner4, $riskLearnerIds, 'Learner enrolled < 14 days must not be at risk');
    }

    #[TestDox('04. Course Analytics: Join CourseReview qua orders và đọc snapshot revenues')]
    public function test_04_course_analytics_joins_review_and_reads_revenue_snapshot(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $rule = $this->rule(['instructor_rate' => 0.8, 'platform_rate' => 0.2]);
        $order = $this->order($learner, $course, $rule, [
            'status' => 'paid',
            'payment_status' => 'paid',
            'amount' => 1000000,
            'paid_at' => now(),
        ]);
        $this->revenue($instructor, $course, $order, $rule, [
            'gross_amount' => 1000000,
            'instructor_amount' => 800000,
            'platform_fee_amount' => 200000,
            'earned_at' => now(),
        ]);

        $enrollment = (int) DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $order,
            'status' => 'active',
            'progress_percent' => 50.0,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->localLessonProgress($enrollment, $lesson, ['status' => 'in_progress']);

        // Insert review connected via order_id
        DB::table('course_reviews')->insert([
            'order_id' => $order,
            'rating' => 5,
            'comment' => 'Khóa học rất xuất sắc!',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/analytics");

        $response->assertStatus(200);

        $json = $response->json('data');
        $this->assertEquals(800000.0, (float) $json['revenue']['instructor_amount']);
        $this->assertEquals(5.0, (float) $json['review']['average_rating']);
        $this->assertEquals(1, (int) $json['review']['review_count']);
        $this->assertEquals(1, (int) $json['learning']['enrollment_count']);
    }

    #[TestDox('05. Revenue: Auto-sync missing revenue rows via RevenueShareService')]
    public function test_05_revenue_missing_row_can_be_synced_and_read(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $learner = $this->user('learner');
        $rule = $this->rule(['instructor_rate' => 0.85, 'platform_rate' => 0.15]);

        // Paid order without a revenue record initially
        $orderId = $this->order($learner, $course, $rule, [
            'status' => 'paid',
            'payment_status' => 'paid',
            'amount' => 2000000,
            'paid_at' => now(),
        ]);

        $this->assertDatabaseMissing('revenues', ['order_id' => $orderId]);

        // Trigger self-heal sync
        $synced = app(RevenueShareService::class)->syncMissingPaidOrderRevenues();
        $this->assertGreaterThanOrEqual(1, $synced);

        $this->assertDatabaseHas('revenues', [
            'order_id' => $orderId,
            'gross_amount' => 2000000,
            'instructor_amount' => 1700000,
            'platform_fee_amount' => 300000,
        ]);
    }

    #[TestDox('06. Learner Dashboard: Streak & Heatmap tính từ actual learning activity')]
    public function test_06_learner_dashboard_streak_and_heatmap_from_actual_learning(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course);

        // Record activity for today and yesterday (2-day actual streak)
        $this->localDailyActivity($enrollment, [
            'activity_date' => now()->toDateString(),
            'video_learning_seconds' => 1500, // level 2
        ]);
        $this->localDailyActivity($enrollment, [
            'activity_date' => now()->subDay()->toDateString(),
            'video_learning_seconds' => 3000, // level 3
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);

        $streakData = $response->json('data');
        $this->assertEquals(2, (int) $streakData['current_streak']);
        $this->assertTrue($streakData['is_maintaining']);

        // Activity Calendar / Heatmap
        $calendarResponse = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/activity-calendar?month=' . now()->month . '&year=' . now()->year);

        $calendarResponse->assertStatus(200);
        $heatmapData = collect($calendarResponse->json('data.heatmap') ?? []);

        $todayRecord = $heatmapData->firstWhere('date', now()->toDateString());
        $this->assertNotNull($todayRecord);
        $this->assertEquals(2, (int) $todayRecord['intensity']);
    }

    #[TestDox('07. Course Progress: Chỉ đếm published lessons, không đếm draft/hidden')]
    public function test_07_course_progress_counts_published_lessons_only(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);

        $lesson1 = $this->localLesson($course, $section, ['status' => 'published', 'sort_order' => 10]);
        $lesson2 = $this->localLesson($course, $section, ['status' => 'draft', 'sort_order' => 20]);
        $lesson3 = $this->localLesson($course, $section, ['status' => 'hidden', 'sort_order' => 30]);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course);
        $this->localLessonProgress($enrollment, $lesson1, ['status' => 'completed', 'completed_at' => now()]);

        $userModel = User::find($learner);
        $progress = app(\App\Services\Learning\LearningService::class)->getCourseProgress($userModel, $course);

        $this->assertEquals(1, $progress['total_lessons'], 'Total lessons must only count published lessons');
        $this->assertEquals(1, $progress['completed_lessons']);
        $this->assertEquals(100.0, (float) $progress['progress_percent']);
        $this->assertTrue($progress['course_completed']);
    }
}
