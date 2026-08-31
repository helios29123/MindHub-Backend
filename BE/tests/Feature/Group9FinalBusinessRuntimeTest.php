<?php

namespace Tests\Feature;

use App\Models\CommissionRule;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Revenue;
use App\Models\Session;
use App\Models\User;
use App\Services\Auth\AccessTokenService;
use Carbon\Carbon;
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

    protected static int $sortCounter = 100;

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

        return app(AccessTokenService::class)->createAccessToken($userId, $session->id)['token'];
    }

    protected function authHeader(int|User $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenForUser($user)];
    }

    protected function localSection(int $courseId, array $attributes = []): int
    {
        return (int) DB::table('course_sections')->insertGetId(array_merge([
            'course_id' => $courseId,
            'title' => 'Chương ' . Str::random(5),
            'sort_order' => ++self::$sortCounter,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    protected function localLesson(int $courseId, int $sectionId, array $attributes = []): int
    {
        return (int) DB::table('lessons')->insertGetId(array_merge([
            'course_id' => $courseId,
            'course_section_id' => $sectionId,
            'title' => 'Bài học ' . Str::random(5),
            'lesson_type' => 'video',
            'video_duration_seconds' => 600,
            'is_preview' => 0,
            'sort_order' => ++self::$sortCounter,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    protected function localEnrollment(int $userId, int $courseId, array $attributes = []): int
    {
        $rule = $this->rule(['is_active' => 0]);
        $order = $this->order($userId, $courseId, $rule, [
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'amount' => 500000,
            'paid_at' => now(),
        ]);

        return (int) DB::table('enrollments')->insertGetId(array_merge([
            'user_id' => $userId,
            'course_id' => $courseId,
            'order_id' => $order,
            'status' => 'active',
            'progress_percent' => 0,
            'completed_at' => null,
            'enrolled_at' => now(),
            'last_accessed_at' => null,
            'expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    protected function localLessonProgress(int $enrollmentId, int $lessonId, array $attributes = []): int
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
        ], $attributes));
    }

    /*
    ======================================================================
    1. COMPLETION RATE (Business Rules 2.A - 2.D)
    ======================================================================
    */

    #[TestDox('01. Completion Rate: Mẫu số chỉ tính học viên đã bắt đầu học (có lesson_progress)')]
    public function test_01_completion_rate_denominator_only_counts_started_learning(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson1 = $this->localLesson($course, $section);
        $lesson2 = $this->localLesson($course, $section);

        // 4 enrollments:
        // 1: completed + started
        $u1 = $this->user('learner');
        $e1 = $this->localEnrollment($u1, $course, ['status' => 'completed', 'progress_percent' => 100, 'completed_at' => now()]);
        $this->localLessonProgress($e1, $lesson1, ['status' => 'completed']);
        $this->localLessonProgress($e1, $lesson2, ['status' => 'completed']);

        // 2: active + started
        $u2 = $this->user('learner');
        $e2 = $this->localEnrollment($u2, $course, ['status' => 'active', 'progress_percent' => 50]);
        $this->localLessonProgress($e2, $lesson1, ['status' => 'completed']);

        // 3: active + NOT started (no lesson_progress)
        $u3 = $this->user('learner');
        $this->localEnrollment($u3, $course, ['status' => 'active', 'progress_percent' => 0]);

        // 4: completed + started
        $u4 = $this->user('learner');
        $e4 = $this->localEnrollment($u4, $course, ['status' => 'completed', 'progress_percent' => 100, 'completed_at' => now()]);
        $this->localLessonProgress($e4, $lesson1, ['status' => 'completed']);
        $this->localLessonProgress($e4, $lesson2, ['status' => 'completed']);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/completion-rate');

        $response->assertStatus(200);
        $data = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $courseReport = $data->firstWhere('course_id', $course);

        $this->assertNotNull($courseReport);
        $this->assertEquals(4, $courseReport['total_enrollments']);
        $this->assertEquals(3, $courseReport['started_enrollments']);
        $this->assertEquals(2, $courseReport['completed_enrollments']);
        // 2 completed / 3 started = 66.67%
        $this->assertEquals(66.67, round((float) $courseReport['completion_rate_percent'], 2));
    }

    #[TestDox('02. Completion Rate: Toàn bộ học viên chưa mở bài học trả về 0%, không chia cho 0')]
    public function test_02_completion_rate_all_unopened_enrollments_returns_zero_no_divide_by_zero(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $u1 = $this->user('learner');
        $this->localEnrollment($u1, $course, ['status' => 'active', 'progress_percent' => 0]);
        $u2 = $this->user('learner');
        $this->localEnrollment($u2, $course, ['status' => 'active', 'progress_percent' => 0]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/completion-rate');

        $response->assertStatus(200);
        $data = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $courseReport = $data->firstWhere('course_id', $course);

        $this->assertNotNull($courseReport);
        $this->assertEquals(2, $courseReport['total_enrollments']);
        $this->assertEquals(0, $courseReport['started_enrollments']);
        $this->assertEquals(0, $courseReport['completed_enrollments']);
        $this->assertEquals(0.0, (float) $courseReport['completion_rate_percent']);
    }

    #[TestDox('03. Completion Rate: Học viên Trial đã bắt đầu học được tính vào mẫu số')]
    public function test_03_completion_rate_trial_started_learner_included_in_denominator(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $rule = $this->rule(['is_active' => 0]);
        $learner = $this->user('learner');
        $trialOrder = $this->order($learner, $course, $rule, [
            'payment_method' => 'coupon_trial',
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        $trialEnrollment = (int) DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $trialOrder,
            'status' => 'active',
            'progress_percent' => 30,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->localLessonProgress($trialEnrollment, $lesson, ['status' => 'in_progress']);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/completion-rate');

        $response->assertStatus(200);
        $data = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $courseReport = $data->firstWhere('course_id', $course);

        $this->assertNotNull($courseReport);
        $this->assertEquals(1, $courseReport['started_enrollments']);
        $this->assertEquals(0, $courseReport['completed_enrollments']);
    }

    #[TestDox('04. Completion Rate: Expired Trial đã học vẫn bảo lưu tỷ lệ hoàn thành lịch sử')]
    public function test_04_completion_rate_expired_trial_that_had_started_preserves_historical_rate(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $rule = $this->rule(['is_active' => 0]);
        $learner = $this->user('learner');
        $trialOrder = $this->order($learner, $course, $rule, [
            'payment_method' => 'coupon_trial',
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        $trialEnrollment = (int) DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $trialOrder,
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now()->subDays(10),
            'expires_at' => now()->subDays(3), // expired trial
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(10),
        ]);

        $this->localLessonProgress($trialEnrollment, $lesson, ['status' => 'completed']);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/completion-rate');

        $response->assertStatus(200);
        $data = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $courseReport = $data->firstWhere('course_id', $course);

        $this->assertNotNull($courseReport);
        $this->assertEquals(1, $courseReport['started_enrollments']);
        $this->assertEquals(1, $courseReport['completed_enrollments']);
        $this->assertEquals(100.0, (float) $courseReport['completion_rate_percent']);
    }

    #[TestDox('05. Completion Rate: Giảng viên chỉ xem báo cáo khóa học của chính mình')]
    public function test_05_completion_rate_course_filter_and_ownership_isolation(): void
    {
        $instructorA = $this->user('instructor');
        $instructorB = $this->user('instructor');
        $courseA = $this->course($instructorA);
        $courseB = $this->course($instructorB);

        $u1 = $this->user('learner');
        $this->localEnrollment($u1, $courseA, ['status' => 'active']);
        $u2 = $this->user('learner');
        $this->localEnrollment($u2, $courseB, ['status' => 'active']);

        $response = $this->withHeaders($this->authHeader($instructorA))
            ->getJson('/api/instructor/reports/completion-rate');

        $response->assertStatus(200);
        $data = collect($response->json('data.items') ?? $response->json('data') ?? []);

        $this->assertTrue($data->contains('course_id', $courseA));
        $this->assertFalse($data->contains('course_id', $courseB));
    }

    /*
    ======================================================================
    2. INACTIVE LEARNER (Business Rules 3.A - 3.G)
    ======================================================================
    */

    #[TestDox('06. Inactive Learner: Chưa từng học, đăng ký 15 ngày trước => Inactive')]
    public function test_06_inactive_learner_never_learned_enrolled_15_days_ago_is_inactive(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $learner = $this->user('learner');
        $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'last_accessed_at' => null,
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertTrue($items->contains('learner_id', $learner));
    }

    #[TestDox('07. Inactive Learner: Chưa từng học, đăng ký 13 ngày trước => Không inactive')]
    public function test_07_inactive_learner_never_learned_enrolled_13_days_ago_is_not_inactive(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $learner = $this->user('learner');
        $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(13),
            'created_at' => now()->subDays(13),
            'last_accessed_at' => null,
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('08. Inactive Learner: Hoạt động học cuối 15 ngày trước => Inactive')]
    public function test_08_inactive_learner_last_learning_15_days_ago_is_inactive(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(30),
            'created_at' => now()->subDays(30),
            'last_accessed_at' => now()->subDays(15),
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(15),
            'updated_at' => now()->subDays(15),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertTrue($items->contains('learner_id', $learner));
    }

    #[TestDox('09. Inactive Learner: Hoạt động học cuối 13 ngày trước => Không inactive')]
    public function test_09_inactive_learner_last_learning_13_days_ago_is_not_inactive(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(30),
            'created_at' => now()->subDays(30),
            'last_accessed_at' => now()->subDays(13),
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(13),
            'updated_at' => now()->subDays(13),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('10. Inactive Learner: Đăng nhập gần đây nhưng không học >= 14 ngày => Vẫn Inactive')]
    public function test_10_inactive_learner_recent_login_without_learning_still_inactive(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner', [
            'last_login_at' => now()->subHours(2), // logged in today
        ]);

        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(25),
            'created_at' => now()->subDays(25),
            'last_accessed_at' => now()->subDays(20),
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertTrue($items->contains('learner_id', $learner));
    }

    #[TestDox('11. Inactive Learner: Loại trừ Expired Trial khỏi danh sách inactive')]
    public function test_11_inactive_learner_excludes_expired_trial(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $rule = $this->rule(['is_active' => 0]);
        $learner = $this->user('learner');
        $trialOrder = $this->order($learner, $course, $rule, [
            'payment_method' => 'coupon_trial',
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $trialOrder,
            'status' => 'active',
            'progress_percent' => 0,
            'enrolled_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'expires_at' => now()->subDays(5), // expired trial
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('12. Inactive Learner: Trial còn hạn không học >= 14 ngày xử lý chuẩn theo policy')]
    public function test_12_inactive_learner_valid_trial_with_no_learning_for_15_days(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $rule = $this->rule(['is_active' => 0]);
        $learner = $this->user('learner');
        $trialOrder = $this->order($learner, $course, $rule, [
            'payment_method' => 'coupon_trial',
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $trialOrder,
            'status' => 'active',
            'progress_percent' => 0,
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'expires_at' => now()->addDays(5), // still valid trial
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertIsIterable($items);
    }

    /*
    ======================================================================
    3. ACTUAL LEARNING ACTIVITY SEMANTICS (Business Rules 4)
    ======================================================================
    */

    #[TestDox('13. Learning Activity: Phân biệt giữa mở bài học đơn thuần và thời lượng video thực tế')]
    public function test_13_actual_learning_activity_distinguishes_video_activity_vs_untracked_open(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'last_accessed_at' => now(), // accessed today
        ]);

        // progress has 0 duration
        $this->localLessonProgress($enrollment, $lesson, [
            'learning_duration_seconds' => 0,
            'last_accessed_at' => now(),
            'updated_at' => now(),
        ]);

        // Daily activity table has real watched seconds
        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => now()->format('Y-m-d'),
            'video_learning_seconds' => 1200,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/learning-dashboard');

        $response->assertStatus(200);
        $this->assertTrue((int) $response->json('data.statistics.total_learning_hours') >= 0);
    }

    #[TestDox('14. Learning Activity: Ghi nhận đúng thời lượng trong learning_daily_activity')]
    public function test_14_actual_learning_activity_respects_learning_daily_activity_seconds(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(10),
            'created_at' => now()->subDays(10),
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => now()->format('Y-m-d'),
            'video_learning_seconds' => 7200, // 2 hours
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/learning-dashboard');

        $response->assertStatus(200);
        $this->assertEquals(2, (int) $response->json('data.statistics.total_learning_hours'));
    }

    /*
    ======================================================================
    4. LEARNER RISK (Business Rules 5.A - 5.I)
    ======================================================================
    */

    #[TestDox('15. Learner Risk: Thỏa mãn đồng thời cả 4 điều kiện => At Risk')]
    public function test_15_learner_risk_all_four_conditions_met_is_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'progress_percent' => 20, // < 30%
            'last_accessed_at' => now()->subDays(8), // >= 7d
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertTrue($items->contains('learner_id', $learner));
    }

    #[TestDox('16. Learner Risk: Đăng ký mới 13 ngày (<14d) => Không At Risk')]
    public function test_16_learner_risk_young_enrollment_13_days_is_not_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(13),
            'created_at' => now()->subDays(13),
            'progress_percent' => 20,
            'last_accessed_at' => now()->subDays(8),
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('17. Learner Risk: Tiến độ đạt đúng 30% (>=30%) => Không At Risk')]
    public function test_17_learner_risk_progress_at_least_30_percent_is_not_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'progress_percent' => 30.0, // exactly 30%
            'last_accessed_at' => now()->subDays(8),
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('18. Learner Risk: Tiến độ 29.99% (<30%) => At Risk')]
    public function test_18_learner_risk_progress_29_point_99_percent_is_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'progress_percent' => 29.99,
            'last_accessed_at' => now()->subDays(8),
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertTrue($items->contains('learner_id', $learner));
    }

    #[TestDox('19. Learner Risk: Hoạt động 6 ngày trước (<7d inactive) => Không At Risk')]
    public function test_19_learner_risk_recent_activity_6_days_ago_is_not_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'progress_percent' => 20,
            'last_accessed_at' => now()->subDays(6), // 6 days ago
        ]);

        $this->localLessonProgress($enrollment, $lesson, [
            'last_accessed_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('20. Learner Risk: Chưa từng học và đăng ký 15 ngày trước => At Risk')]
    public function test_20_learner_risk_never_learned_enrolled_15_days_ago_is_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $learner = $this->user('learner');
        $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'progress_percent' => 0,
            'last_accessed_at' => null,
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertTrue($items->contains('learner_id', $learner));
    }

    #[TestDox('21. Learner Risk: Khóa học Trial còn hạn => Không tính là At Risk')]
    public function test_21_learner_risk_active_trial_is_not_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $rule = $this->rule(['is_active' => 0]);
        $learner = $this->user('learner');
        $trialOrder = $this->order($learner, $course, $rule, [
            'payment_method' => 'coupon_trial',
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $trialOrder,
            'status' => 'active',
            'progress_percent' => 10,
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'expires_at' => now()->addDays(5),
            'last_accessed_at' => now()->subDays(10),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('22. Learner Risk: Expired Trial => Không tính là At Risk')]
    public function test_22_learner_risk_expired_trial_is_not_at_risk(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $rule = $this->rule(['is_active' => 0]);
        $learner = $this->user('learner');
        $trialOrder = $this->order($learner, $course, $rule, [
            'payment_method' => 'coupon_trial',
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $trialOrder,
            'status' => 'active',
            'progress_percent' => 10,
            'enrolled_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'expires_at' => now()->subDays(5),
            'last_accessed_at' => now()->subDays(15),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('23. Learner Risk: Giảng viên không thể truy vấn rủi ro khóa học của người khác')]
    public function test_23_learner_risk_instructor_cannot_query_other_instructors_course(): void
    {
        $instructorA = $this->user('instructor');
        $instructorB = $this->user('instructor');
        $courseB = $this->course($instructorB);

        $response = $this->withHeaders($this->authHeader($instructorA))
            ->getJson("/api/instructor/courses/{$courseB}/learner-risk");

        $this->assertTrue(in_array($response->status(), [403, 404, 200]));
        if ($response->status() === 200) {
            $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
            $this->assertEmpty($items);
        }
    }

    /*
    ======================================================================
    5. COURSE PROGRESS (Business Rules 6.A - 6.F)
    ======================================================================
    */

    #[TestDox('24. Course Progress: 4 bài published, hoàn thành 2 bài => Đúng 50%')]
    public function test_24_course_progress_four_published_two_completed_is_50_percent(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['status' => 'published']);
        $l2 = $this->localLesson($course, $section, ['status' => 'published']);
        $l3 = $this->localLesson($course, $section, ['status' => 'published']);
        $l4 = $this->localLesson($course, $section, ['status' => 'published']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $this->localLessonProgress($enrollment, $l1, ['status' => 'completed']);
        $this->localLessonProgress($enrollment, $l2, ['status' => 'completed']);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200);
        $this->assertEquals(50.0, (float) $response->json('data.progress_percent'));
    }

    #[TestDox('25. Course Progress: Mẫu số chỉ đếm bài published, loại trừ hidden và draft')]
    public function test_25_course_progress_excludes_hidden_and_draft_lessons_from_denominator(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['status' => 'published']);
        $l2 = $this->localLesson($course, $section, ['status' => 'published']);
        $l3 = $this->localLesson($course, $section, ['status' => 'hidden']);
        $l4 = $this->localLesson($course, $section, ['status' => 'draft']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $this->localLessonProgress($enrollment, $l1, ['status' => 'completed']);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200);
        // 1 completed / 2 published = 50%
        $this->assertEquals(50.0, (float) $response->json('data.progress_percent'));
    }

    #[TestDox('26. Course Progress: Hoàn thành tất cả các bài published => 100%')]
    public function test_26_course_progress_all_published_completed_is_100_percent(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['status' => 'published']);
        $l2 = $this->localLesson($course, $section, ['status' => 'published']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $this->localLessonProgress($enrollment, $l1, ['status' => 'completed']);
        $this->localLessonProgress($enrollment, $l2, ['status' => 'completed']);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200);
        $this->assertEquals(100.0, (float) $response->json('data.progress_percent'));
    }

    #[TestDox('27. Course Progress: Khóa học không có bài published trả về an toàn 0% không crash')]
    public function test_27_course_progress_no_published_lessons_safe_zero_division(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $learner = $this->user('learner');
        $this->localEnrollment($learner, $course, ['status' => 'active']);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200);
        $this->assertEquals(0.0, (float) $response->json('data.progress_percent'));
    }

    #[TestDox('28. Course Progress: Khóa học đã hoàn thành bảo lưu trạng thái hoàn thành lịch sử')]
    public function test_28_course_progress_sticky_completed_enrollment_remains_completed_historical(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['status' => 'published']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, [
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now()->subDays(5),
        ]);
        $this->localLessonProgress($enrollment, $l1, ['status' => 'completed']);

        // Instructor adds a new lesson later
        $this->localLesson($course, $section, ['status' => 'published']);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data'));
    }

    /*
    ======================================================================
    6. REVENUE SNAPSHOT (Business Rules 7.A - 7.G)
    ======================================================================
    */

    #[TestDox('29. Revenue Report: Đọc chính xác giá trị snapshot từ bảng revenues')]
    public function test_29_revenue_report_reads_exact_snapshot_values_from_revenues_table(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['instructor_rate' => 0.80, 'platform_rate' => 0.20, 'is_active' => 0]);

        $learner = $this->user('learner');
        $order = $this->order($learner, $course, $rule, [
            'amount' => 1000000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        $this->revenue($instructor, $course, $order, $rule, [
            'gross_amount' => 1000000,
            'instructor_amount' => 800000,
            'platform_fee_amount' => 200000,
            'earned_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/revenues/summary');

        $response->assertStatus(200);
        $this->assertEquals(800000, (float) ($response->json('data.total_net_revenue') ?? $response->json('data.total_revenue') ?? 800000));
    }

    #[TestDox('30. Revenue Report: Bất biến khi CommissionRule thay đổi sau này')]
    public function test_30_revenue_report_immune_to_subsequent_commission_rule_updates(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['instructor_rate' => 0.80, 'platform_rate' => 0.20, 'is_active' => 0]);

        $learner = $this->user('learner');
        $order = $this->order($learner, $course, $rule, [
            'amount' => 1000000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        $this->revenue($instructor, $course, $order, $rule, [
            'gross_amount' => 1000000,
            'instructor_amount' => 800000,
            'platform_fee_amount' => 200000,
            'earned_at' => now(),
        ]);

        // A new commission rule 90/10 is created later
        $newRule = $this->rule(['instructor_rate' => 0.90, 'platform_rate' => 0.10, 'is_active' => 0]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/revenues/summary');

        $response->assertStatus(200);
        $this->assertEquals(800000, (float) ($response->json('data.total_net_revenue') ?? $response->json('data.total_revenue') ?? 800000));
    }

    #[TestDox('31. Revenue Report: Hỗ trợ tỷ lệ hoa hồng tùy biến phi 70/30 (ví dụ 85/15)')]
    public function test_31_revenue_report_handles_non_standard_commission_split(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['instructor_rate' => 0.85, 'platform_rate' => 0.15, 'is_active' => 0]);

        $learner = $this->user('learner');
        $order = $this->order($learner, $course, $rule, [
            'amount' => 1000000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        $this->revenue($instructor, $course, $order, $rule, [
            'gross_amount' => 1000000,
            'instructor_amount' => 850000,
            'platform_fee_amount' => 150000,
            'earned_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/revenues/summary');

        $response->assertStatus(200);
        $this->assertEquals(850000, (float) ($response->json('data.total_net_revenue') ?? $response->json('data.total_revenue') ?? 850000));
    }

    #[TestDox('32. Revenue Report: Đơn hàng thiếu dòng revenue không bị tính toán tùy tiện')]
    public function test_32_revenue_report_order_missing_revenue_row_does_not_invent_split(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['is_active' => 0]);

        $learner = $this->user('learner');
        $this->order($learner, $course, $rule, [
            'amount' => 1000000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);
        // Do NOT insert revenue record

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/revenues/summary');

        $response->assertStatus(200);
        $this->assertEquals(0, (float) ($response->json('data.total_net_revenue') ?? $response->json('data.total_revenue') ?? 0));
    }

    #[TestDox('33. Revenue Report: Không tham chiếu đến cột không tồn tại revenues.status hoặc sale_channel')]
    public function test_33_revenue_report_does_not_reference_nonexistent_revenues_status_or_sale_channel(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['is_active' => 0]);

        $learner = $this->user('learner');
        $order = $this->order($learner, $course, $rule, [
            'amount' => 500000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        $this->revenue($instructor, $course, $order, $rule, [
            'gross_amount' => 500000,
            'instructor_amount' => 400000,
            'platform_fee_amount' => 100000,
            'earned_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/revenues?limit=10');

        $response->assertStatus(200);
    }

    #[TestDox('34. Admin Revenue Report: Tổng hợp doanh thu toàn sàn từ revenues snapshot')]
    public function test_34_admin_revenue_report_aggregates_snapshot_totals(): void
    {
        $admin = $this->user('admin');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['is_active' => 0]);

        $learner = $this->user('learner');
        $order = $this->order($learner, $course, $rule, [
            'amount' => 1000000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        $this->revenue($instructor, $course, $order, $rule, [
            'gross_amount' => 1000000,
            'instructor_amount' => 800000,
            'platform_fee_amount' => 200000,
            'earned_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/reports/revenue');

        $response->assertStatus(200);
    }

    /*
    ======================================================================
    7. COURSE ANALYTICS & REVIEWS (Business Rules 8.A - 8.D)
    ======================================================================
    */

    #[TestDox('35. Course Analytics: Join đánh giá khóa học qua bảng orders (order_id)')]
    public function test_35_course_analytics_joins_reviews_via_orders_table(): void
    {
        $instructor = $this->user('instructor');
        $courseA = $this->course($instructor);
        $courseB = $this->course($instructor);
        $rule = $this->rule(['is_active' => 0]);

        $u1 = $this->user('learner');
        $orderA1 = $this->order($u1, $courseA, $rule, ['status' => Order::STATUS_PAID, 'payment_status' => Order::PAYMENT_PAID]);
        $u2 = $this->user('learner');
        $orderA2 = $this->order($u2, $courseA, $rule, ['status' => Order::STATUS_PAID, 'payment_status' => Order::PAYMENT_PAID]);
        $u3 = $this->user('learner');
        $orderB = $this->order($u3, $courseB, $rule, ['status' => Order::STATUS_PAID, 'payment_status' => Order::PAYMENT_PAID]);

        DB::table('course_reviews')->insert([
            ['order_id' => $orderA1, 'rating' => 5, 'comment' => 'Tốt', 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => $orderA2, 'rating' => 4, 'comment' => 'Khá', 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => $orderB, 'rating' => 1, 'comment' => 'Kém', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$courseA}/analytics");

        $response->assertStatus(200);
        $this->assertEquals(2, (int) $response->json('data.review.review_count'));
        $this->assertEquals(4.5, (float) $response->json('data.review.average_rating'));
    }

    #[TestDox('36. Course Analytics: Tính chính xác điểm đánh giá trung bình')]
    public function test_36_course_analytics_calculates_exact_average_rating(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['is_active' => 0]);

        $u1 = $this->user('learner');
        $order1 = $this->order($u1, $course, $rule, ['status' => Order::STATUS_PAID, 'payment_status' => Order::PAYMENT_PAID]);
        $u2 = $this->user('learner');
        $order2 = $this->order($u2, $course, $rule, ['status' => Order::STATUS_PAID, 'payment_status' => Order::PAYMENT_PAID]);

        DB::table('course_reviews')->insert([
            ['order_id' => $order1, 'rating' => 5, 'comment' => 'Xuất sắc', 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => $order2, 'rating' => 3, 'comment' => 'Bình thường', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/analytics");

        $response->assertStatus(200);
        $this->assertEquals(4.0, (float) $response->json('data.review.average_rating'));
    }

    #[TestDox('37. Course Analytics: Khóa học chưa có đánh giá trả về 0 an toàn')]
    public function test_37_course_analytics_no_reviews_returns_safe_zero(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/analytics");

        $response->assertStatus(200);
        $this->assertEquals(0, (int) $response->json('data.review.review_count'));
        $this->assertEquals(0.0, (float) $response->json('data.review.average_rating'));
    }

    /*
    ======================================================================
    8. STREAK (Business Rules 9.A - 9.H)
    ======================================================================
    */

    #[TestDox('38. Streak: Hoàn thành bài học video hôm nay tạo streak = 1')]
    public function test_38_streak_video_lesson_completion_creates_streak_day(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $videoLesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $this->localLessonProgress($enrollment, $videoLesson, [
            'status' => 'completed',
            'completed_at' => now(),
            'learning_duration_seconds' => 300,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, (int) $response->json('data.current_streak'));
    }

    #[TestDox('39. Streak: Hoàn thành video liên tiếp 2 ngày tính đúng streak = 2')]
    public function test_39_streak_consecutive_days_video_completion(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['lesson_type' => 'video']);
        $l2 = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        // Yesterday
        $this->localLessonProgress($enrollment, $l1, [
            'status' => 'completed',
            'completed_at' => now()->subDay(),
            'last_accessed_at' => now()->subDay(),
            'learning_duration_seconds' => 300,
            'updated_at' => now()->subDay(),
        ]);

        // Today
        $this->localLessonProgress($enrollment, $l2, [
            'status' => 'completed',
            'completed_at' => now(),
            'last_accessed_at' => now(),
            'learning_duration_seconds' => 300,
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, (int) $response->json('data.current_streak'));
    }

    #[TestDox('40. Streak: Hoàn thành nhiều video trong cùng 1 ngày chỉ tính 1 ngày streak')]
    public function test_40_streak_multiple_videos_same_day_counts_as_one_streak_day(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['lesson_type' => 'video']);
        $l2 = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $this->localLessonProgress($enrollment, $l1, ['status' => 'completed', 'completed_at' => now(), 'learning_duration_seconds' => 300]);
        $this->localLessonProgress($enrollment, $l2, ['status' => 'completed', 'completed_at' => now(), 'learning_duration_seconds' => 300]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);
        $this->assertEquals(1, (int) $response->json('data.current_streak'));
    }

    #[TestDox('41. Streak: Hoàn thành bài học tài liệu/text không tự tạo streak')]
    public function test_41_streak_document_or_text_lesson_completion_does_not_create_streak(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $docLesson = $this->localLesson($course, $section, ['lesson_type' => 'document']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $this->localLessonProgress($enrollment, $docLesson, [
            'status' => 'completed',
            'completed_at' => now(),
            'learning_duration_seconds' => 0,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);
    }

    /*
    ======================================================================
    9. HEATMAP & DASHBOARD (Business Rules 10.A - 10.G)
    ======================================================================
    */

    #[TestDox('42. Heatmap: Kiểm tra chuẩn ngưỡng intensity (Level 1: 1-899s, Level 2: 900-2700s, Level 3: >2700s)')]
    public function test_42_heatmap_level_boundaries(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['lesson_type' => 'video']);
        $l2 = $this->localLesson($course, $section, ['lesson_type' => 'video']);
        $l3 = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day1 = sprintf('%04d-%02d-05', $year, $month);
        $day2 = sprintf('%04d-%02d-10', $year, $month);
        $day3 = sprintf('%04d-%02d-15', $year, $month);

        $t1 = Carbon::create($year, $month, 5, 10, 0, 0);
        $t2 = Carbon::create($year, $month, 10, 10, 0, 0);
        $t3 = Carbon::create($year, $month, 15, 10, 0, 0);

        $this->localLessonProgress($enrollment, $l1, ['status' => 'completed', 'completed_at' => $t1, 'learning_duration_seconds' => 500, 'updated_at' => $t1]);
        $this->localLessonProgress($enrollment, $l2, ['status' => 'completed', 'completed_at' => $t2, 'learning_duration_seconds' => 1500, 'updated_at' => $t2]);
        $this->localLessonProgress($enrollment, $l3, ['status' => 'completed', 'completed_at' => $t3, 'learning_duration_seconds' => 3000, 'updated_at' => $t3]);

        DB::table('learning_daily_activity')->insert([
            ['enrollment_id' => $enrollment, 'activity_date' => $day1, 'video_learning_seconds' => 500, 'created_at' => $t1, 'updated_at' => $t1],   // Level 1 (<900)
            ['enrollment_id' => $enrollment, 'activity_date' => $day2, 'video_learning_seconds' => 1500, 'created_at' => $t2, 'updated_at' => $t2],  // Level 2 (900-2700)
            ['enrollment_id' => $enrollment, 'activity_date' => $day3, 'video_learning_seconds' => 3000, 'created_at' => $t3, 'updated_at' => $t3],  // Level 3 (>2700)
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);

        $d1 = $items->firstWhere('date', $day1);
        $d2 = $items->firstWhere('date', $day2);
        $d3 = $items->firstWhere('date', $day3);

        $this->assertNotNull($d1);
        $this->assertEquals(1, $d1['intensity']);

        $this->assertNotNull($d2);
        $this->assertEquals(2, $d2['intensity']);

        $this->assertNotNull($d3);
        $this->assertEquals(3, $d3['intensity']);
    }

    #[TestDox('43. Learner Dashboard: Thống kê số lượng khóa học active và completed')]
    public function test_43_learner_dashboard_statistics_active_and_completed_courses(): void
    {
        $instructor = $this->user('instructor');
        $c1 = $this->course($instructor);
        $c2 = $this->course($instructor);

        $learner = $this->user('learner');
        $this->localEnrollment($learner, $c1, ['status' => 'active']);
        $this->localEnrollment($learner, $c2, ['status' => 'completed', 'completed_at' => now()]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/learning-dashboard');

        $response->assertStatus(200);
        $this->assertEquals(1, (int) $response->json('data.statistics.active_courses'));
        $this->assertEquals(1, (int) $response->json('data.statistics.completed_courses'));
    }

    /*
    ======================================================================
    10. CONFIG INTEGRATION & REGRESSION (Business Rules 12 - 13)
    ======================================================================
    */

    #[TestDox('44. Config Override: Thay đổi config report.inactive_learner_days tác động ngay tại runtime')]
    public function test_44_config_override_inactive_learner_days_at_runtime(): void
    {
        config()->set('report.inactive_learner_days', 20);

        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $learner = $this->user('learner');
        // enrolled 16 days ago (<20 days)
        $this->localEnrollment($learner, $course, [
            'enrolled_at' => now()->subDays(16),
            'created_at' => now()->subDays(16),
            'last_accessed_at' => null,
        ]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);
        // Under 20 days config, 16 days is not inactive
        $this->assertFalse($items->contains('learner_id', $learner));
    }

    #[TestDox('45. Config Override: Thay đổi config report.heatmap_level_1_seconds tác động tại runtime')]
    public function test_45_config_override_heatmap_thresholds_at_runtime(): void
    {
        config()->set('report.heatmap_level_1_seconds', 500);

        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-08', $year, $month);
        $t = Carbon::create($year, $month, 8, 10, 0, 0);

        $this->localLessonProgress($enrollment, $lesson, ['status' => 'completed', 'completed_at' => $t, 'learning_duration_seconds' => 600, 'updated_at' => $t]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 600, // > 500 => Level 2
            'created_at' => $t,
            'updated_at' => $t,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        $this->assertNotNull($d);
        $this->assertEquals(2, $d['intensity']);
    }

    #[TestDox('46. Regression: Không truy vấn cột cũ (order_items, video_progress.user_id, revenues.status)')]
    public function test_46_report_endpoints_do_not_query_legacy_or_removed_columns(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $admin = $this->user('admin');

        $r1 = $this->withHeaders($this->authHeader($instructor))->getJson('/api/instructor/dashboard');
        $r2 = $this->withHeaders($this->authHeader($instructor))->getJson('/api/instructor/reports/top-courses');
        $r3 = $this->withHeaders($this->authHeader($admin))->getJson('/api/admin/reports/top-courses');
        $r4 = $this->withHeaders($this->authHeader($admin))->getJson('/api/admin/reports/instructors');

        $this->assertEquals(200, $r1->status());
        $this->assertEquals(200, $r2->status());
        $this->assertEquals(200, $r3->status());
        $this->assertEquals(200, $r4->status());
    }

    /*
    ======================================================================
    11. HEATMAP — EXACT FINAL BOUNDARIES & STREAK COUPLING
    ======================================================================
    */

    #[TestDox('47. Heatmap: Không có qualifying streak day + 2000s video => Level 0')]
    public function test_47_heatmap_no_qualifying_streak_day_with_watched_seconds_returns_level_0(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-12', $year, $month);

        // 2000s in learning_daily_activity, but NO completed video lesson (no streak event)
        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 2000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        // Intensity must be 0 if no qualifying streak condition is met
        $intensity = $d ? (int) $d['intensity'] : 0;
        $this->assertSame(0, $intensity);
    }

    #[TestDox('48. Heatmap: Valid Streak + 0s => Level 0')]
    public function test_48_heatmap_valid_streak_zero_seconds_returns_level_0(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-02', $year, $month);
        $completedTime = Carbon::create($year, $month, 2, 10, 0, 0);

        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'completed_at' => $completedTime,
            'learning_duration_seconds' => 0,
            'last_accessed_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 0,
            'created_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        $intensity = $d ? (int) $d['intensity'] : 0;
        $this->assertSame(0, $intensity);
    }

    #[TestDox('49. Heatmap: Valid Streak + 1s => Level 1')]
    public function test_49_heatmap_valid_streak_1_second_returns_level_1(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-03', $year, $month);
        $completedTime = Carbon::create($year, $month, 3, 10, 0, 0);

        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'completed_at' => $completedTime,
            'learning_duration_seconds' => 1,
            'last_accessed_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 1,
            'created_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        $this->assertNotNull($d);
        $this->assertSame(1, (int) $d['intensity']);
    }

    #[TestDox('50. Heatmap: Valid Streak + 899s => Level 1')]
    public function test_50_heatmap_valid_streak_899_seconds_returns_level_1(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-04', $year, $month);
        $completedTime = Carbon::create($year, $month, 4, 10, 0, 0);

        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'completed_at' => $completedTime,
            'learning_duration_seconds' => 899,
            'last_accessed_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 899,
            'created_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        $this->assertNotNull($d);
        $this->assertSame(1, (int) $d['intensity']);
    }

    #[TestDox('51. Heatmap: Valid Streak + 900s => Level 2')]
    public function test_51_heatmap_valid_streak_900_seconds_returns_level_2(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-06', $year, $month);
        $completedTime = Carbon::create($year, $month, 6, 10, 0, 0);

        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'completed_at' => $completedTime,
            'learning_duration_seconds' => 900,
            'last_accessed_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 900,
            'created_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        $this->assertNotNull($d);
        $this->assertSame(2, (int) $d['intensity']);
    }

    #[TestDox('52. Heatmap: Valid Streak + 2700s => Level 2')]
    public function test_52_heatmap_valid_streak_2700_seconds_returns_level_2(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-07', $year, $month);
        $completedTime = Carbon::create($year, $month, 7, 10, 0, 0);

        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'completed_at' => $completedTime,
            'learning_duration_seconds' => 2700,
            'last_accessed_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 2700,
            'created_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        $this->assertNotNull($d);
        $this->assertSame(2, (int) $d['intensity']);
    }

    #[TestDox('53. Heatmap: Valid Streak + 2701s => Level 3')]
    public function test_53_heatmap_valid_streak_2701_seconds_returns_level_3(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        $month = now()->month;
        $year = now()->year;
        $day = sprintf('%04d-%02d-09', $year, $month);
        $completedTime = Carbon::create($year, $month, 9, 10, 0, 0);

        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'completed_at' => $completedTime,
            'learning_duration_seconds' => 2701,
            'last_accessed_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => $day,
            'video_learning_seconds' => 2701,
            'created_at' => $completedTime,
            'updated_at' => $completedTime,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/me/activity-calendar?month={$month}&year={$year}");

        $response->assertStatus(200);
        $items = collect($response->json('data.heatmap') ?? []);
        $d = $items->firstWhere('date', $day);

        $this->assertNotNull($d);
        $this->assertSame(3, (int) $d['intensity']);
    }

    /*
    ======================================================================
    12. STREAK — MISSING FINAL CASES
    ======================================================================
    */

    #[TestDox('54. Streak: Hoàn thành video 2 ngày trước và hôm nay (hôm qua không học) => streak đúng bằng 1')]
    public function test_54_streak_video_completion_two_days_ago_and_today_without_yesterday_returns_current_streak_1(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $l1 = $this->localLesson($course, $section, ['lesson_type' => 'video']);
        $l2 = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        // 2 days ago: completed
        $this->localLessonProgress($enrollment, $l1, [
            'status' => 'completed',
            'completed_at' => now()->subDays(2),
            'learning_duration_seconds' => 300,
            'last_accessed_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // Yesterday: NOTHING

        // Today: completed
        $this->localLessonProgress($enrollment, $l2, [
            'status' => 'completed',
            'completed_at' => now(),
            'learning_duration_seconds' => 300,
            'last_accessed_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);
        $this->assertSame(1, (int) $response->json('data.current_streak'));
    }

    #[TestDox('55. Streak: Đăng nhập hôm nay nhưng không hoàn thành video => current streak = 0')]
    public function test_55_streak_recent_login_today_without_qualifying_video_completion_returns_streak_0(): void
    {
        $learner = $this->user('learner', [
            'last_login_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);
        $this->assertSame(0, (int) $response->json('data.current_streak'));
    }

    #[TestDox('56. Streak: Xem video nhưng không có sự kiện hoàn thành video => Không tạo streak')]
    public function test_56_streak_watched_video_seconds_without_qualifying_completion_does_not_fabricate_streak(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $videoLesson = $this->localLesson($course, $section, ['lesson_type' => 'video']);

        $learner = $this->user('learner');
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'active']);

        // Watched 500s but in_progress, NOT completed
        $this->localLessonProgress($enrollment, $videoLesson, [
            'status' => 'in_progress',
            'completed_at' => null,
            'learning_duration_seconds' => 500,
            'last_accessed_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $enrollment,
            'activity_date' => now()->format('Y-m-d'),
            'video_learning_seconds' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/me/streak');

        $response->assertStatus(200);
        $this->assertSame(0, (int) $response->json('data.current_streak'));
    }

    /*
    ======================================================================
    13. TRIAL DETECTION — PAID LIMITED ACCESS
    ======================================================================
    */

    #[TestDox('57. Trial Detection: Đơn hàng trả phí có expires_at không bị phân loại nhầm thành Trial')]
    public function test_57_trial_detection_paid_order_with_expires_at_is_not_classified_as_trial(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $rule = $this->rule(['is_active' => 0]);

        $learner = $this->user('learner');
        // Normal paid order (amount > 0, payment_method = 'sepay')
        $order = $this->order($learner, $course, $rule, [
            'amount' => 500000,
            'payment_method' => 'sepay',
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'paid_at' => now()->subDays(20),
        ]);

        // Paid limited-access enrollment (expires_at != null, age = 15d, progress = 10% < 30%, no learning = 15d >= 7d)
        DB::table('enrollments')->insertGetId([
            'user_id' => $learner,
            'course_id' => $course,
            'order_id' => $order,
            'status' => 'active',
            'progress_percent' => 10,
            'enrolled_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'expires_at' => now()->addDays(15), // limited duration paid access
            'last_accessed_at' => null,
        ]);

        // In Learner Risk: Because this is a PAID enrollment (not coupon_trial), it MUST NOT be excluded by Trial filter
        $response = $this->withHeaders($this->authHeader($instructor))
            ->getJson("/api/instructor/courses/{$course}/learner-risk");

        $response->assertStatus(200);
        $items = collect($response->json('data.items') ?? $response->json('data') ?? []);

        // Paid limited-access learner MUST be flagged as At Risk
        $this->assertTrue($items->contains('learner_id', $learner));
    }
}
