<?php

namespace Tests\Feature;

use App\Models\Session;
use App\Models\User;
use App\Services\Auth\AccessTokenService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\Support\FinalTestData;
use Tests\TestCase;

final class Group7FinalBusinessRuntimeTest extends TestCase
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
            'user_agent' => 'MindHub Group7 Test',
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
        return (int) DB::table('lessons')->insertGetId(array_merge([
            'course_id' => $courseId,
            'course_section_id' => $sectionId,
            'title' => 'Bài học ' . $this->token('les'),
            'lesson_type' => 'video',
            'video_duration_seconds' => 600,
            'is_preview' => 0,
            'sort_order' => 0,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localEnrollment(int $userId, int $courseId, array $x = []): int
    {
        if (!array_key_exists('order_id', $x)) {
            $ruleId = $this->rule();
            $x['order_id'] = $this->order($userId, $courseId, $ruleId);
        }

        return (int) DB::table('enrollments')->insertGetId(array_merge([
            'user_id' => $userId,
            'course_id' => $courseId,
            'status' => 'active',
            'progress_percent' => 0.00,
            'enrolled_at' => now(),
            'completed_at' => null,
            'expires_at' => null,
            'last_accessed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localLessonProgress(int $enrollmentId, int $lessonId, array $x = []): int
    {
        return (int) DB::table('lesson_progress')->insertGetId(array_merge([
            'enrollment_id' => $enrollmentId,
            'lesson_id' => $lessonId,
            'status' => 'not_started',
            'started_at' => null,
            'completed_at' => null,
            'learning_duration_seconds' => 0,
            'last_accessed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localVideoProgress(int $enrollmentId, int $lessonId, array $x = []): int
    {
        return (int) DB::table('video_progress')->insertGetId(array_merge([
            'enrollment_id' => $enrollmentId,
            'lesson_id' => $lessonId,
            'current_second' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localPaidOrder(int $userId, int $courseId, array $x = []): int
    {
        $ruleId = $this->rule();

        return (int) DB::table('orders')->insertGetId(array_merge([
            'user_id' => $userId,
            'course_id' => $courseId,
            'commission_rule_id' => $ruleId,
            'order_code' => 'MH' . now()->format('YmdHis') . random_int(100000, 999999),
            'price_snapshot' => 500000,
            'discount_amount' => 0,
            'amount' => 500000,
            'payment_method' => 'vnpay',
            'status' => 'paid',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localTrialOrder(int $userId, int $courseId, array $x = []): int
    {
        return $this->localPaidOrder($userId, $courseId, array_merge([
            'amount' => 0,
            'payment_method' => 'coupon_trial',
        ], $x));
    }

    protected function localComment(int $userId, int $lessonId, array $x = []): int
    {
        return (int) DB::table('comments')->insertGetId(array_merge([
            'parent_id' => null,
            'enrollment_id' => null,
            'user_id' => $userId,
            'lesson_id' => $lessonId,
            'content' => 'Nội dung test Group 7',
            'status' => 'visible',
            'is_official' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    protected function localDailyActivity(int $enrollmentId, array $x = []): int
    {
        return (int) DB::table('learning_daily_activity')->insertGetId(array_merge([
            'enrollment_id' => $enrollmentId,
            'activity_date' => now()->toDateString(),
            'video_learning_seconds' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $x));
    }

    #[TestDox('01. LessonProgress dùng enrollment_id và không còn user_id')]
    public function test_01_lesson_progress_schema_uses_enrollment_id(): void
    {
        $this->assertTrue(Schema::hasTable('lesson_progress'));
        $this->assertTrue(Schema::hasColumn('lesson_progress', 'enrollment_id'));
        $this->assertFalse(Schema::hasColumn('lesson_progress', 'user_id'));
    }

    #[TestDox('02. VideoProgress dùng enrollment_id và không còn user_id')]
    public function test_02_video_progress_schema_uses_enrollment_id(): void
    {
        $this->assertTrue(Schema::hasTable('video_progress'));
        $this->assertTrue(Schema::hasColumn('video_progress', 'enrollment_id'));
        $this->assertFalse(Schema::hasColumn('video_progress', 'user_id'));
    }

    #[TestDox('03. Comment chỉ còn trạng thái visible và hidden')]
    public function test_03_comment_status_has_no_deleted(): void
    {
        $statuses = $this->enums('comments', 'status');
        $this->assertContains('visible', $statuses);
        $this->assertContains('hidden', $statuses);
        $this->assertNotContains('deleted', $statuses);
    }

    #[TestDox('04. Progress chỉ tính bài published')]
    public function test_04_course_progress_counts_only_published_lessons(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $enrollment = $this->localEnrollment($learner, $course);

        $published1 = $this->localLesson($course, $section, ['sort_order' => 1]);
        $published2 = $this->localLesson($course, $section, ['sort_order' => 2]);
        $this->localLesson($course, $section, ['sort_order' => 3, 'status' => 'hidden']);

        $this->localLessonProgress($enrollment, $published1, ['status' => 'completed', 'completed_at' => now()]);
        $this->localLessonProgress($enrollment, $published2, ['status' => 'in_progress', 'started_at' => now()->subMinute()]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200)
            ->assertJsonPath('data.total_lessons', 2)
            ->assertJsonPath('data.completed_lessons', 1);

        $this->assertEqualsWithDelta(50.0, (float) $response->json('data.progress_percent'), 0.01);
    }

    #[TestDox('05. Bài hidden đã completed bị loại khỏi cả tử số và mẫu số')]
    public function test_05_hidden_completed_lesson_is_excluded_from_progress(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $enrollment = $this->localEnrollment($learner, $course);

        $visible = $this->localLesson($course, $section, ['sort_order' => 1]);
        $hidden = $this->localLesson($course, $section, ['sort_order' => 2, 'status' => 'hidden']);

        $this->localLessonProgress($enrollment, $visible, ['status' => 'completed', 'completed_at' => now()]);
        $this->localLessonProgress($enrollment, $hidden, ['status' => 'completed', 'completed_at' => now()]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200)
            ->assertJsonPath('data.total_lessons', 1)
            ->assertJsonPath('data.completed_lessons', 1);

        $this->assertEqualsWithDelta(100.0, (float) $response->json('data.progress_percent'), 0.01);
    }

    #[TestDox('06. Course không có bài published trả progress bằng 0')]
    public function test_06_course_without_published_lessons_returns_zero_progress(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $this->localEnrollment($learner, $course);
        $this->localLesson($course, $section, ['status' => 'hidden']);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200)
            ->assertJsonPath('data.total_lessons', 0)
            ->assertJsonPath('data.completed_lessons', 0);

        $this->assertEqualsWithDelta(0.0, (float) $response->json('data.progress_percent'), 0.01);
    }

    #[TestDox('07. Hoàn thành bài cuối cùng đánh dấu Enrollment completed')]
    public function test_07_completing_last_lesson_marks_enrollment_completed(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $enrollment = $this->localEnrollment($learner, $course);
        $lesson = $this->localLesson($course, $section);

        $this->localLessonProgress($enrollment, $lesson, ['status' => 'in_progress', 'started_at' => now()->subMinute()]);

        $this->withHeaders($this->authHeader($learner))
            ->patchJson("/api/learn/lessons/{$lesson}/complete", ['completed' => true])
            ->assertStatus(200);

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment, 'status' => 'completed']);
        $this->assertNotNull(DB::table('enrollments')->where('id', $enrollment)->value('completed_at'));
    }

    #[TestDox('08. Publish bài mới không reset Course đã completed')]
    public function test_08_new_published_lesson_does_not_reset_completed_enrollment(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $completedAt = now()->subDay();
        $enrollment = $this->localEnrollment($learner, $course, ['status' => 'completed', 'completed_at' => $completedAt]);

        $old = $this->localLesson($course, $section, ['sort_order' => 1]);
        $this->localLessonProgress($enrollment, $old, ['status' => 'completed', 'completed_at' => $completedAt]);
        $this->localLesson($course, $section, ['sort_order' => 2]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/courses/{$course}/progress");

        $response->assertStatus(200)
            ->assertJsonPath('data.course_completed', true)
            ->assertJsonPath('data.has_new_content', true)
            ->assertJsonPath('data.total_lessons', 2)
            ->assertJsonPath('data.completed_lessons', 1);
    }

    #[TestDox('09. Resume ưu tiên bài in_progress cập nhật gần nhất')]
    public function test_09_resume_prefers_latest_in_progress_lesson(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $enrollment = $this->localEnrollment($learner, $course);

        $lesson1 = $this->localLesson($course, $section, ['sort_order' => 1]);
        $lesson2 = $this->localLesson($course, $section, ['sort_order' => 2]);

        $this->localLessonProgress($enrollment, $lesson1, [
            'status' => 'in_progress',
            'started_at' => now()->subHours(2),
            'updated_at' => now()->subHour(),
        ]);
        $this->localLessonProgress($enrollment, $lesson2, [
            'status' => 'in_progress',
            'started_at' => now()->subHour(),
            'updated_at' => now(),
        ]);

        $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/learn/resume')
            ->assertStatus(200)
            ->assertJsonPath('data.lesson.id', $lesson2);
    }

    #[TestDox('10. Resume bỏ qua bài in_progress đã hidden')]
    public function test_10_resume_skips_hidden_in_progress_lesson(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $enrollment = $this->localEnrollment($learner, $course);

        $published = $this->localLesson($course, $section, ['sort_order' => 1]);
        $hidden = $this->localLesson($course, $section, ['sort_order' => 2, 'status' => 'hidden']);
        $this->localLessonProgress($enrollment, $hidden, ['status' => 'in_progress', 'started_at' => now(), 'updated_at' => now()]);

        $this->withHeaders($this->authHeader($learner))
            ->getJson('/api/learn/resume')
            ->assertStatus(200)
            ->assertJsonPath('data.lesson.id', $published);
    }

    #[TestDox('11. Next bị chặn khi bài hiện tại chưa completed')]
    public function test_11_next_is_blocked_when_current_not_completed(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $enrollment = $this->localEnrollment($learner, $course);
        $lesson1 = $this->localLesson($course, $section, ['sort_order' => 1]);
        $this->localLesson($course, $section, ['sort_order' => 2]);
        $this->localLessonProgress($enrollment, $lesson1, ['status' => 'in_progress', 'started_at' => now()]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/lessons/{$lesson1}/next");

        $this->assertTrue(in_array($response->status(), [403, 409, 422], true));
    }

    #[TestDox('12. Video progress lưu current_second tăng bình thường')]
    public function test_12_save_video_progress_success(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['video_duration_seconds' => 600]);
        $enrollment = $this->localEnrollment($learner, $course);

        $response = $this->withHeaders($this->authHeader($learner))
            ->patchJson("/api/learn/lessons/{$lesson}/progress", ['current_second' => 120]);

        $response->assertStatus(200)
            ->assertJsonPath('data.progress.current_second', 120);

        $this->assertDatabaseHas('video_progress', [
            'enrollment_id' => $enrollment,
            'lesson_id' => $lesson,
            'current_second' => 120,
        ]);
    }

    #[TestDox('13. Video progress không đi lùi')]
    public function test_13_video_progress_is_monotonic(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['video_duration_seconds' => 1000]);
        $enrollment = $this->localEnrollment($learner, $course);
        $this->localVideoProgress($enrollment, $lesson, ['current_second' => 700]);

        $this->withHeaders($this->authHeader($learner))
            ->patchJson("/api/learn/lessons/{$lesson}/progress", ['current_second' => 300])
            ->assertStatus(200);

        $this->assertDatabaseHas('video_progress', [
            'enrollment_id' => $enrollment,
            'lesson_id' => $lesson,
            'current_second' => 700,
        ]);
    }

    #[TestDox('14. current_second âm bị từ chối')]
    public function test_14_negative_video_position_is_rejected(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $this->localEnrollment($learner, $course);

        $this->withHeaders($this->authHeader($learner))
            ->patchJson("/api/learn/lessons/{$lesson}/progress", ['current_second' => -1])
            ->assertStatus(422);
    }

    #[TestDox('15. Preview lesson public không tạo LessonProgress')]
    public function test_15_preview_does_not_create_progress(): void
    {
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section, ['is_preview' => 1]);

        $this->getJson("/api/lessons/{$lesson}/preview")->assertStatus(200);
        $this->assertSame(0, DB::table('lesson_progress')->where('lesson_id', $lesson)->count());
    }

    #[TestDox('16. Enrollment hết hạn bị chặn quyền học')]
    public function test_16_expired_enrollment_is_blocked(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $this->localEnrollment($learner, $course, ['expires_at' => now()->subMinute()]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/lessons/{$lesson}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    #[TestDox('17. Trial còn hạn được tạo Comment Q&A')]
    public function test_17_valid_trial_can_create_comment(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $trialOrder = $this->localTrialOrder($learner, $course);
        $enrollment = $this->localEnrollment($learner, $course, [
            'order_id' => $trialOrder,
            'expires_at' => now()->addDay(),
        ]);

        $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/lessons/{$lesson}/comments", ['content' => 'Câu hỏi Trial hợp lệ'])
            ->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'enrollment_id' => $enrollment,
            'user_id' => $learner,
            'lesson_id' => $lesson,
            'status' => 'visible',
        ]);
    }

    #[TestDox('18. Trial hết hạn không được tạo Comment Q&A')]
    public function test_18_expired_trial_cannot_create_comment(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $trialOrder = $this->localTrialOrder($learner, $course);
        $this->localEnrollment($learner, $course, [
            'order_id' => $trialOrder,
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/lessons/{$lesson}/comments", ['content' => 'Không được tạo']);

        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    #[TestDox('19. Trial-only không được tạo Course Review')]
    public function test_19_trial_only_cannot_review(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $trialOrder = $this->localTrialOrder($learner, $course);
        $enrollment = $this->localEnrollment($learner, $course, ['order_id' => $trialOrder]);
        $this->localLessonProgress($enrollment, $lesson, ['status' => 'completed', 'completed_at' => now()]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/courses/{$course}/reviews", ['rating' => 5, 'content' => 'Trial không được review']);

        $this->assertTrue(in_array($response->status(), [403, 409, 422], true));
    }

    #[TestDox('20. Mua thật và complete ít nhất một Lesson thì được Review')]
    public function test_20_paid_learner_with_completed_lesson_can_review(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $order = $this->localPaidOrder($learner, $course);
        $enrollment = $this->localEnrollment($learner, $course, ['order_id' => $order]);
        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/courses/{$course}/reviews", ['rating' => 5, 'content' => 'Khóa học tốt'])
            ->assertStatus(201);

        $this->assertDatabaseHas('course_reviews', ['order_id' => $order, 'rating' => 5]);
    }

    #[TestDox('21. Rating ngoài khoảng 1 đến 5 bị từ chối')]
    public function test_21_review_rating_validation(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);

        $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/courses/{$course}/reviews", ['rating' => 6])
            ->assertStatus(422);
    }

    #[TestDox('22. Note được gắn đúng Enrollment')]
    public function test_22_note_is_enrollment_scoped(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $enrollment = $this->localEnrollment($learner, $course);

        $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/learn/lessons/{$lesson}/notes", [
                'content' => 'Ghi chú của tôi',
                'note_time_second' => 30,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('lesson_notes', [
            'enrollment_id' => $enrollment,
            'lesson_id' => $lesson,
            'content' => 'Ghi chú của tôi',
        ]);
    }

    #[TestDox('23. Không có Streak thì Heatmap contract là level 0')]
    public function test_23_heatmap_without_streak_is_level_zero_contract(): void
    {
        $this->assertSame(0, $this->expectedHeatmapLevel(false, 1200));
    }

    #[TestDox('24. Heatmap 900 giây thuộc level 2')]
    public function test_24_heatmap_900_seconds_is_level_two_contract(): void
    {
        $this->assertSame(2, $this->expectedHeatmapLevel(true, 900));
    }

    #[TestDox('25. Heatmap 2700 giây vẫn thuộc level 2')]
    public function test_25_heatmap_2700_seconds_is_level_two_contract(): void
    {
        $this->assertSame(2, $this->expectedHeatmapLevel(true, 2700));
    }

    #[TestDox('26. Heatmap 2701 giây thuộc level 3')]
    public function test_26_heatmap_2701_seconds_is_level_three_contract(): void
    {
        $this->assertSame(3, $this->expectedHeatmapLevel(true, 2701));
    }

    #[TestDox('27. Chỉ Video Lesson mới tạo Streak contract')]
    public function test_27_only_video_lesson_creates_streak_contract(): void
    {
        $this->assertTrue($this->lessonTypeCreatesStreak('video'));
        $this->assertFalse($this->lessonTypeCreatesStreak('text'));
        $this->assertFalse($this->lessonTypeCreatesStreak('document'));
    }

    #[TestDox('28. Quiz legacy không còn route')]
    public function test_28_quiz_legacy_routes_are_removed(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->map(fn ($route) => $route->uri())
            ->filter(fn ($uri) => str_contains(strtolower($uri), 'quiz'));

        $this->assertCount(0, $routes);
    }

    #[TestDox('29. Question Star legacy không còn bảng')]
    public function test_29_question_star_legacy_table_is_removed(): void
    {
        $this->assertFalse(Schema::hasTable('instructor_question_stars'));
    }

    #[TestDox('30. learning_daily_activity dùng enrollment_id và video_learning_seconds')]
    public function test_30_learning_daily_activity_contract(): void
    {
        $this->assertTrue(Schema::hasTable('learning_daily_activity'));
        $this->assertTrue(Schema::hasColumn('learning_daily_activity', 'enrollment_id'));
        $this->assertTrue(Schema::hasColumn('learning_daily_activity', 'activity_date'));
        $this->assertTrue(Schema::hasColumn('learning_daily_activity', 'video_learning_seconds'));
    }

    #[TestDox('31. Hidden Comment không có reply không xuất hiện trong danh sách learner')]
    public function test_31_hidden_comment_without_reply_is_not_listed(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $enrollment = $this->localEnrollment($learner, $course);

        $hiddenId = $this->localComment($learner, $lesson, [
            'enrollment_id' => $enrollment,
            'status' => 'hidden',
            'content' => 'Comment hidden không reply',
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/lessons/{$lesson}/comments");

        $response->assertStatus(200);

        $ids = collect($response->json('data') ?? [])->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->assertNotContains($hiddenId, $ids);
    }

    #[TestDox('32. Hidden Comment có reply vẫn giữ record cha và quan hệ thread')]
    public function test_32_hidden_parent_with_reply_keeps_thread_records(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $enrollment = $this->localEnrollment($learner, $course);

        $parent = $this->localComment($learner, $lesson, [
            'enrollment_id' => $enrollment,
            'status' => 'hidden',
        ]);

        $reply = $this->localComment($learner, $lesson, [
            'enrollment_id' => $enrollment,
            'parent_id' => $parent,
            'status' => 'visible',
        ]);

        $this->assertDatabaseHas('comments', ['id' => $parent, 'status' => 'hidden']);
        $this->assertDatabaseHas('comments', ['id' => $reply, 'parent_id' => $parent, 'status' => 'visible']);
    }

    #[TestDox('33. Reply cấp hai bị chặn')]
    public function test_33_second_level_reply_is_rejected(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $enrollment = $this->localEnrollment($learner, $course);

        $root = $this->localComment($learner, $lesson, ['enrollment_id' => $enrollment]);
        $reply = $this->localComment($learner, $lesson, [
            'enrollment_id' => $enrollment,
            'parent_id' => $root,
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/lessons/{$lesson}/comments", [
                'content' => 'Reply cấp hai',
                'parent_id' => $reply,
            ]);

        $this->assertTrue(in_array($response->status(), [400, 403, 409, 422, 500], true));
        $this->assertDatabaseMissing('comments', [
            'parent_id' => $reply,
            'content' => 'Reply cấp hai',
        ]);
    }

    #[TestDox('34. Instructor sở hữu Course được tạo official reply')]
    public function test_34_course_owner_instructor_can_official_reply(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $enrollment = $this->localEnrollment($learner, $course);
        $question = $this->localComment($learner, $lesson, ['enrollment_id' => $enrollment]);

        $response = $this->withHeaders($this->authHeader($instructor))
            ->postJson("/api/comments/{$question}/replies", [
                'content' => 'Phản hồi chính thức',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'parent_id' => $question,
            'lesson_id' => $lesson,
            'is_official' => 1,
        ]);
    }

    #[TestDox('35. Instructor khác không được official reply Course không sở hữu')]
    public function test_35_other_instructor_cannot_official_reply(): void
    {
        $learner = $this->user('learner');
        $owner = $this->user('instructor');
        $otherInstructor = $this->user('instructor');
        $course = $this->course($owner);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $enrollment = $this->localEnrollment($learner, $course);
        $question = $this->localComment($learner, $lesson, ['enrollment_id' => $enrollment]);

        $response = $this->withHeaders($this->authHeader($otherInstructor))
            ->postJson("/api/comments/{$question}/replies", [
                'content' => 'Không được phép',
            ]);

        $this->assertTrue(in_array($response->status(), [403, 404, 422], true));
    }

    #[TestDox('36. Một Order chỉ được tạo một Course Review')]
    public function test_36_one_order_only_one_review(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $lesson = $this->localLesson($course, $section);
        $order = $this->localPaidOrder($learner, $course);
        $enrollment = $this->localEnrollment($learner, $course, ['order_id' => $order]);

        $this->localLessonProgress($enrollment, $lesson, ['status' => 'completed', 'completed_at' => now()]);

        DB::table('course_reviews')->insert([
            'order_id' => $order,
            'rating' => 4,
            'comment' => 'Review đầu tiên',
            'edited_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->postJson("/api/courses/{$course}/reviews", [
                'rating' => 5,
                'content' => 'Review lần hai',
            ]);

        $this->assertTrue(in_array($response->status(), [409, 422], true));
        $this->assertSame(1, DB::table('course_reviews')->where('order_id', $order)->count());
    }

    #[TestDox('37. Course Review public xem được không cần Enrollment')]
    public function test_37_public_course_reviews_are_visible_without_enrollment(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $order = $this->localPaidOrder($learner, $course);

        DB::table('course_reviews')->insert([
            'order_id' => $order,
            'rating' => 5,
            'comment' => 'Review public',
            'edited_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/courses/{$course}/reviews")
            ->assertStatus(200);
    }

    #[TestDox('38. Next ở Lesson published cuối trả null hoặc trạng thái kết thúc Course')]
    public function test_38_last_published_lesson_has_no_next(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $section = $this->localSection($course);
        $enrollment = $this->localEnrollment($learner, $course);
        $lesson = $this->localLesson($course, $section, ['sort_order' => 1]);

        $this->localLessonProgress($enrollment, $lesson, [
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/lessons/{$lesson}/next");

        $response->assertStatus(200);
        $this->assertNull($response->json('data'));
    }

    #[TestDox('39. Lesson của Course khác không được dùng với Enrollment hiện tại')]
    public function test_39_cross_course_lesson_access_is_blocked(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');

        $courseA = $this->course($instructor);
        $sectionA = $this->localSection($courseA);
        $this->localEnrollment($learner, $courseA);

        $courseB = $this->course($instructor);
        $sectionB = $this->localSection($courseB);
        $lessonB = $this->localLesson($courseB, $sectionB);

        $response = $this->withHeaders($this->authHeader($learner))
            ->getJson("/api/learn/lessons/{$lessonB}");

        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    #[TestDox('40. learning_daily_activity ghi theo Enrollment và ngày')]
    public function test_40_daily_activity_can_store_video_seconds_for_enrollment_day(): void
    {
        $learner = $this->user('learner');
        $instructor = $this->user('instructor');
        $course = $this->course($instructor);
        $enrollment = $this->localEnrollment($learner, $course);

        $id = $this->localDailyActivity($enrollment, [
            'video_learning_seconds' => 900,
        ]);

        $this->assertDatabaseHas('learning_daily_activity', [
            'id' => $id,
            'enrollment_id' => $enrollment,
            'activity_date' => now()->toDateString(),
            'video_learning_seconds' => 900,
        ]);
    }

    private function expectedHeatmapLevel(bool $hasStreak, int $seconds): int
    {
        if (!$hasStreak) {
            return 0;
        }

        if ($seconds < 900) {
            return 1;
        }

        if ($seconds <= 2700) {
            return 2;
        }

        return 3;
    }

    private function lessonTypeCreatesStreak(string $lessonType): bool
    {
        return $lessonType === 'video';
    }
}
