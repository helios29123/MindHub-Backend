<?php

namespace Tests\Feature;

use App\Models\Session;
use App\Models\User;
use App\Services\Auth\AccessTokenService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\Support\FinalTestData;
use Tests\TestCase;

final class VideoProgressCompensationTest extends TestCase
{
    use DatabaseTransactions;
    use FinalTestData;

    private int $instructorId;
    private int $courseId;
    private int $sectionId;
    private int $lessonId;
    private int $learnerId;
    private int $enrollmentId;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo instructor & khóa học
        $this->instructorId = $this->user('instructor');
        $this->courseId = $this->course($this->instructorId, ['status' => 'published']);

        // 2. Tạo chương học & bài học video
        $this->sectionId = (int) DB::table('course_sections')->insertGetId([
            'course_id' => $this->courseId,
            'title' => 'Chương 1: Test Video Progress',
            'sort_order' => 0,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->lessonId = (int) DB::table('lessons')->insertGetId([
            'course_id' => $this->courseId,
            'course_section_id' => $this->sectionId,
            'title' => 'Bài học 1: Video Tracking',
            'lesson_type' => 'video',
            'video_duration_seconds' => 600,
            'is_preview' => 0,
            'sort_order' => 0,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Tạo học viên & ghi danh (enrollment)
        $this->learnerId = $this->user('learner', ['timezone' => 'Asia/Ho_Chi_Minh']);
        $ruleId = $this->rule();
        $orderId = $this->order($this->learnerId, $this->courseId, $ruleId);

        $this->enrollmentId = (int) DB::table('enrollments')->insertGetId([
            'user_id' => $this->learnerId,
            'course_id' => $this->courseId,
            'order_id' => $orderId,
            'status' => 'active',
            'progress_percent' => 0.00,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tokenForUser(int $userId): string
    {
        $session = Session::create([
            'user_id' => $userId,
            'refresh_token_hash' => hash('sha256', Str::random(80)),
            'device_name' => 'PHPUnit Test',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'MindHub Test',
            'expires_at' => now()->addDay(),
        ]);

        return app(AccessTokenService::class)->createAccessToken($userId, (int) $session->id)['token'];
    }

    protected function authHeader(int $userId): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->tokenForUser($userId),
            'Accept' => 'application/json',
        ];
    }

    #[Test]
    #[TestDox('a. Cập nhật video progress thông thường (0 -> 30s)')]
    public function test_regular_video_progress_update(): void
    {
        $today = now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d');

        $response = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 30,
                'duration_second' => 600,
                'timezone' => 'Asia/Ho_Chi_Minh',
            ],
            $this->authHeader($this->learnerId)
        );

        $response->assertStatus(200);

        // 1. Kiểm tra video_progress lưu đúng 30s
        $videoProgress = DB::table('video_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertNotNull($videoProgress);
        $this->assertEquals(30, (int) $videoProgress->current_second);

        // 2. Kiểm tra learning_daily_activity lưu 30s vào ngày hôm nay
        $dailyActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('activity_date', $today)
            ->first();
        $this->assertNotNull($dailyActivity);
        $this->assertEquals(30, (int) $dailyActivity->video_learning_seconds);

        // 3. Kiểm tra lesson_progress tăng duration lên 30s
        $lessonProgress = DB::table('lesson_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertNotNull($lessonProgress);
        $this->assertEquals(30, (int) $lessonProgress->learning_duration_seconds);
    }

    #[Test]
    #[TestDox('b. Tua tới hợp lệ (diff <= 30s): 30s -> 45s (diff = 15s)')]
    public function test_forward_seek_within_30s_allowed(): void
    {
        $today = now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d');

        // Bước 1: Xem đến giây 30
        DB::table('video_progress')->insert([
            'enrollment_id' => $this->enrollmentId,
            'lesson_id' => $this->lessonId,
            'current_second' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $this->enrollmentId,
            'activity_date' => $today,
            'video_learning_seconds' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('lesson_progress')->insert([
            'enrollment_id' => $this->enrollmentId,
            'lesson_id' => $this->lessonId,
            'status' => 'in_progress',
            'learning_duration_seconds' => 30,
            'started_at' => now(),
            'last_accessed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bước 2: Xem tiếp đến giây 45 (diff = 15 <= 30s)
        $response = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 45,
                'timezone' => 'Asia/Ho_Chi_Minh',
            ],
            $this->authHeader($this->learnerId)
        );

        $response->assertStatus(200);

        // Vị trí cập nhật 45s
        $videoProgress = DB::table('video_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertEquals(45, (int) $videoProgress->current_second);

        // Giây học cộng dồn: 30 + 15 = 45s
        $dailyActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('activity_date', $today)
            ->first();
        $this->assertEquals(45, (int) $dailyActivity->video_learning_seconds);

        // Lesson progress tăng lên 45s
        $lessonProgress = DB::table('lesson_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertEquals(45, (int) $lessonProgress->learning_duration_seconds);
    }

    #[Test]
    #[TestDox('c. Tua nhanh vượt quá 30s (diff > 30s): 45s -> 100s (diff = 55s)')]
    public function test_fast_forward_jump_over_30s_not_accumulated(): void
    {
        $today = now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d');

        // Khởi tạo mốc 45s
        DB::table('video_progress')->insert([
            'enrollment_id' => $this->enrollmentId,
            'lesson_id' => $this->lessonId,
            'current_second' => 45,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $this->enrollmentId,
            'activity_date' => $today,
            'video_learning_seconds' => 45,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('lesson_progress')->insert([
            'enrollment_id' => $this->enrollmentId,
            'lesson_id' => $this->lessonId,
            'status' => 'in_progress',
            'learning_duration_seconds' => 45,
            'started_at' => now(),
            'last_accessed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tua nhanh lên giây 100
        $response = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 100,
                'timezone' => 'Asia/Ho_Chi_Minh',
            ],
            $this->authHeader($this->learnerId)
        );

        $response->assertStatus(200);

        // Vị trí vẫn được lưu thành 100s
        $videoProgress = DB::table('video_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertEquals(100, (int) $videoProgress->current_second);

        // Thời lượng học KHÔNG TĂNG (vẫn giữ nguyên 45s)
        $dailyActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('activity_date', $today)
            ->first();
        $this->assertEquals(45, (int) $dailyActivity->video_learning_seconds);

        $lessonProgress = DB::table('lesson_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertEquals(45, (int) $lessonProgress->learning_duration_seconds);
    }

    #[Test]
    #[TestDox('d. Tua lùi (diff <= 0): 100s -> 80s (diff = -20s)')]
    public function test_rewind_backwards_not_accumulated(): void
    {
        $today = now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d');

        // Khởi tạo mốc 100s
        DB::table('video_progress')->insert([
            'enrollment_id' => $this->enrollmentId,
            'lesson_id' => $this->lessonId,
            'current_second' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $this->enrollmentId,
            'activity_date' => $today,
            'video_learning_seconds' => 45,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tua lùi về 80s
        $response = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 80,
                'timezone' => 'Asia/Ho_Chi_Minh',
            ],
            $this->authHeader($this->learnerId)
        );

        $response->assertStatus(200);

        // Vị trí không đi lùi (giữ nguyên 100s)
        $videoProgress = DB::table('video_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertEquals(100, (int) $videoProgress->current_second);

        // Giây học không bị thay đổi
        $dailyActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('activity_date', $today)
            ->first();
        $this->assertEquals(45, (int) $dailyActivity->video_learning_seconds);
    }

    #[Test]
    #[TestDox('e. Cơ chế bù (compensation) với force_date hợp lệ trong vòng 24h')]
    public function test_compensation_mechanism_within_24h(): void
    {
        $yesterday = now()->setTimezone('Asia/Ho_Chi_Minh')->subDay()->format('Y-m-d');

        // Giả lập học viên đã lưu 20s vào hôm qua
        DB::table('video_progress')->insert([
            'enrollment_id' => $this->enrollmentId,
            'lesson_id' => $this->lessonId,
            'current_second' => 20,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        DB::table('learning_daily_activity')->insert([
            'enrollment_id' => $this->enrollmentId,
            'activity_date' => $yesterday,
            'video_learning_seconds' => 20,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        // Gửi request bù 5 giây (20 -> 25) kèm force_date = $yesterday
        $response = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 25,
                'force_date' => $yesterday,
                'timezone' => 'Asia/Ho_Chi_Minh',
            ],
            $this->authHeader($this->learnerId)
        );

        $response->assertStatus(200);

        // Vị trí lưu 25s
        $videoProgress = DB::table('video_progress')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('lesson_id', $this->lessonId)
            ->first();
        $this->assertEquals(25, (int) $videoProgress->current_second);

        // Bù thành công: 20 + 5 = 25s vào ngày hôm qua
        $dailyActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('activity_date', $yesterday)
            ->first();
        $this->assertEquals(25, (int) $dailyActivity->video_learning_seconds);
    }

    #[Test]
    #[TestDox('f. Từ chối force_date quá hạn (> 24h / quá khứ xa)')]
    public function test_compensation_rejection_for_expired_force_date(): void
    {
        $expiredDate = now()->setTimezone('Asia/Ho_Chi_Minh')->subDays(5)->format('Y-m-d');
        $today = now()->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d');

        // Khởi tạo mốc 20s
        DB::table('video_progress')->insert([
            'enrollment_id' => $this->enrollmentId,
            'lesson_id' => $this->lessonId,
            'current_second' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Gửi request bù với force_date 5 ngày trước
        $response = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 25,
                'force_date' => $expiredDate,
                'timezone' => 'Asia/Ho_Chi_Minh',
            ],
            $this->authHeader($this->learnerId)
        );

        $response->assertStatus(200);

        // Không có dữ liệu nào được ghi vào ngày quá hạn
        $expiredActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('activity_date', $expiredDate)
            ->first();
        $this->assertNull($expiredActivity);

        // Fallback ghi nhận 5s vào ngày hôm nay
        $todayActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $this->enrollmentId)
            ->where('activity_date', $today)
            ->first();
        $this->assertNotNull($todayActivity);
        $this->assertEquals(5, (int) $todayActivity->video_learning_seconds);
    }

    #[Test]
    #[TestDox('g. Xử lý múi giờ chính xác (America/New_York vs Asia/Tokyo)')]
    public function test_timezone_handling_for_different_timezones(): void
    {
        // 1. Học viên ở New York (UTC-4/5)
        $nyLearnerId = $this->user('learner', ['timezone' => 'America/New_York']);
        $nyOrderId = $this->order($nyLearnerId, $this->courseId, $this->rule());
        $nyEnrollmentId = (int) DB::table('enrollments')->insertGetId([
            'user_id' => $nyLearnerId,
            'course_id' => $this->courseId,
            'order_id' => $nyOrderId,
            'status' => 'active',
            'progress_percent' => 0.00,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nyExpectedDate = now()->setTimezone('America/New_York')->format('Y-m-d');

        $response = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 15,
                'timezone' => 'America/New_York',
            ],
            $this->authHeader($nyLearnerId)
        );

        $response->assertStatus(200);

        $nyActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $nyEnrollmentId)
            ->where('activity_date', $nyExpectedDate)
            ->first();
        $this->assertNotNull($nyActivity, "Dữ liệu phải được ghi vào ngày {$nyExpectedDate} của múi giờ New York");
        $this->assertEquals(15, (int) $nyActivity->video_learning_seconds);

        // 2. Học viên ở Tokyo (UTC+9)
        $tokyoLearnerId = $this->user('learner', ['timezone' => 'Asia/Tokyo']);
        $tokyoOrderId = $this->order($tokyoLearnerId, $this->courseId, $this->rule());
        $tokyoEnrollmentId = (int) DB::table('enrollments')->insertGetId([
            'user_id' => $tokyoLearnerId,
            'course_id' => $this->courseId,
            'order_id' => $tokyoOrderId,
            'status' => 'active',
            'progress_percent' => 0.00,
            'enrolled_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tokyoExpectedDate = now()->setTimezone('Asia/Tokyo')->format('Y-m-d');

        $response2 = $this->patchJson(
            "/api/learn/lessons/{$this->lessonId}/progress",
            [
                'current_second' => 20,
                'timezone' => 'Asia/Tokyo',
            ],
            $this->authHeader($tokyoLearnerId)
        );

        $response2->assertStatus(200);

        $tokyoActivity = DB::table('learning_daily_activity')
            ->where('enrollment_id', $tokyoEnrollmentId)
            ->where('activity_date', $tokyoExpectedDate)
            ->first();
        $this->assertNotNull($tokyoActivity, "Dữ liệu phải được ghi vào ngày {$tokyoExpectedDate} của múi giờ Tokyo");
        $this->assertEquals(20, (int) $tokyoActivity->video_learning_seconds);
    }
}
