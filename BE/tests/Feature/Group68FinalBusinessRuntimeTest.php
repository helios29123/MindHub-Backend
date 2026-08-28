<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Session;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\Auth\AuthService;
use App\Services\Auth\AuthSessionService;
use App\Services\Auth\DeviceLimitService;
use App\Services\Auth\OtpService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

/**
 * FILE TEST FINAL GROUP 6 + 8
 *
 * Chạy:
 * php artisan test tests/Feature/Group68FinalBusinessRuntimeTest.php --testdox
 *
 * File này cố tình có các security/regression case có thể FAIL để phát hiện production bug thật.
 * Không sửa test cho xanh nếu production đang sai DATABASE FINAL hoặc sai nghiệp vụ.
 */

/**
 * GROUP 6 + 8 FINAL runtime regression suite.
 *
 * Mục tiêu:
 * - Tự tạo toàn bộ dữ liệu cần thiết trong từng test.
 * - Không phụ thuộc seed hoặc ID có sẵn trong database.
 * - Bám DATABASE FINAL, không thêm lại field legacy.
 * - Test hành vi nghiệp vụ, hạn chế phụ thuộc cách implementation bên trong.
 *
 * Lưu ý:
 * - Course announcement hiện vẫn là stub trả 501 cho course hợp lệ.
 *   Test chỉ xác nhận contract hiện tại, không giả định đã có bảng announcements.
 * - Không test refresh-token endpoint vì audit hiện tại chưa chứng minh có route refresh.
 * - Không invent OTP lock-time vì DATABASE FINAL không có lock_until/locked_at.
 */
final class Group68FinalBusinessRuntimeTest extends TestCase
{
    use DatabaseTransactions;

    private int $seq = 0;

    private function unique(string $prefix): string
    {
        $this->seq++;
        return $prefix . '_' . str_replace('.', '', uniqid((string) $this->seq, true));
    }

    private function createUser(
        string $role = 'learner',
        string $status = 'active',
        bool $locked = false,
        ?string $password = 'Password123!'
    ): User {
        $suffix = $this->unique('u');

        return User::query()->create([
            'full_name' => 'Test User ' . $suffix,
            'email' => $suffix . '@mindhub.test',
            'phone' => '09' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'password_hash' => $password !== null ? Hash::make($password) : null,
            'role' => $role,
            'status' => $status,
            'locked' => $locked,
            'locked_reason' => $locked ? 'Test lock' : null,
            'email_verified_at' => $status === 'active' ? now() : null,
            'last_login_at' => null,
        ]);
    }

    private function createCourse(User $instructor, string $status = 'published'): Course
    {
        $suffix = $this->unique('course');

        return Course::query()->create([
            'instructor_id' => $instructor->id,
            'title' => 'Khóa học test ' . $suffix,
            'slug' => $suffix,
            'price' => 500000,
            'discount_percent' => 0,
            'course_level' => 'beginner',
            'language' => 'vi',
            'status' => $status,
        ]);
    }

    private function createCoupon(
        Course $course,
        string $discountType = 'percent',
        float $discountValue = 20,
        string $status = 'active',
        ?int $usageLimit = 10,
        int $usedCount = 0,
        $startAt = null,
        $endAt = null
    ): Coupon {
        return Coupon::query()->create([
            'code' => strtoupper($this->unique('CPN')),
            'course_id' => $course->id,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'usage_limit' => $usageLimit,
            'used_count' => $usedCount,
            'start_at' => $startAt ?? now()->subMinute(),
            'end_at' => $endAt ?? now()->addDay(),
            'status' => $status,
        ]);
    }

    private function createAuthSession(User $user, string $device = 'test-device'): Session
    {
        return Session::query()->create([
            'user_id' => $user->id,
            'refresh_token_hash' => hash('sha256', $this->unique('refresh')),
            'device_name' => $device,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'expires_at' => now()->addDay(),
            'revoked_at' => null,
        ]);
    }

    // =====================================================================
    // A. AUTH / REGISTER / LOGIN
    // =====================================================================

    #[TestDox('01. Đăng ký learner tạo user inactive, chưa xác thực email và lưu password_hash')]
    public function test_01_register_learner_uses_final_user_fields(): void
    {
        Mail::fake();

        $email = $this->unique('learner') . '@mindhub.test';
        $phone = '08' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

        $response = $this->postJson('/api/auth/register/learner', [
            'full_name' => 'Learner Runtime',
            'email' => $email,
            'phone' => $phone,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated();

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertSame('learner', $user->role);
        $this->assertSame('inactive', $user->status);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Password123!', (string) $user->password_hash));
    }

    #[TestDox('02. Đăng ký instructor tạo user inactive và instructor profile')]
    public function test_02_register_instructor_creates_profile(): void
    {
        Mail::fake();

        $email = $this->unique('instructor') . '@mindhub.test';
        $phone = '07' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

        $response = $this->postJson('/api/auth/register/instructor', [
            'full_name' => 'Instructor Runtime',
            'email' => $email,
            'phone' => $phone,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'bio' => 'Giảng viên chuyên Laravel Backend với hơn ba năm kinh nghiệm thực tế.',
            'expertise' => 'Laravel',
            'experience_years' => 3,
        ]);

        $response->assertCreated();

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertSame('instructor', $user->role);
        $this->assertSame('inactive', $user->status);
        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $user->id,
        ]);
    }

    #[TestDox('03. Không cho đăng ký trùng email')]
    public function test_03_register_rejects_duplicate_email(): void
    {
        $existing = $this->createUser();

        $this->postJson('/api/auth/register/learner', [
            'full_name' => 'Duplicate Email',
            'email' => $existing->email,
            'phone' => '06' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertStatus(409);
    }

    #[TestDox('04. Không cho đăng ký trùng số điện thoại')]
    public function test_04_register_rejects_duplicate_phone(): void
    {
        $existing = $this->createUser();

        $this->postJson('/api/auth/register/learner', [
            'full_name' => 'Duplicate Phone',
            'email' => $this->unique('dup-phone') . '@mindhub.test',
            'phone' => $existing->phone,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertStatus(409);
    }

    #[TestDox('05. Đăng nhập đúng tạo auth session theo DATABASE FINAL')]
    public function test_05_login_creates_final_auth_session(): void
    {
        $user = $this->createUser(password: 'Password123!');

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'device_name' => 'Chrome Test',
        ]);

        $response->assertOk();

        $session = Session::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

        $this->assertNotEmpty($session->refresh_token_hash);
        $this->assertSame('Chrome Test', $session->device_name);
        $this->assertNotNull($session->expires_at);
        $this->assertNull($session->revoked_at);
    }

    #[TestDox('06. Sai mật khẩu không tạo session')]
    public function test_06_login_wrong_password_does_not_create_session(): void
    {
        $user = $this->createUser(password: 'Password123!');

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'WrongPassword!',
        ])->assertStatus(401);

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    #[TestDox('07. User locked không được đăng nhập')]
    public function test_07_locked_user_cannot_login(): void
    {
        $user = $this->createUser(locked: true);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertStatus(403);
    }

    #[TestDox('08. User suspended không được đăng nhập')]
    public function test_08_suspended_user_cannot_login(): void
    {
        $user = $this->createUser(status: 'suspended');

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertStatus(403);
    }

    #[TestDox('09. User inactive không được đăng nhập')]
    public function test_09_inactive_user_cannot_login(): void
    {
        $user = $this->createUser(status: 'inactive');

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertStatus(403);
    }

    // =====================================================================
    // B. OTP / PASSWORD / EMAIL VERIFY
    // =====================================================================

    #[TestDox('10. Forgot password tạo OTP hash, không lưu plaintext')]
    public function test_10_forgot_password_creates_hashed_otp(): void
    {
        Mail::fake();
        $user = $this->createUser();

        app(AuthService::class)->forgotPassword(['email' => $user->email]);

        $otp = UserOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', 'password_reset')
            ->latest('id')
            ->firstOrFail();

        $this->assertNotEmpty($otp->code_hash);
        $this->assertNotNull($otp->expires_at);
        $this->assertNull($otp->used_at);
        $this->assertSame(0, (int) $otp->attempts);
        $this->assertDoesNotMatchRegularExpression('/^\d{6}$/', (string) $otp->code_hash);
    }

    #[TestDox('11. Reset password bằng OTP đúng cập nhật password_hash và consume OTP')]
    public function test_11_reset_password_consumes_otp(): void
    {
        $user = $this->createUser(password: 'OldPassword123!');
        $plain = app(OtpService::class)->generate((int) $user->id, 'password_reset', 300);

        app(AuthService::class)->resetPassword([
            'email' => $user->email,
            'token' => $plain,
            'password' => 'NewPassword123!',
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', (string) $user->password_hash));

        $this->assertNotNull(
            UserOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', 'password_reset')
                ->latest('id')
                ->value('used_at')
        );
    }

    #[TestDox('12. OTP sai bị từ chối và attempts tăng')]
    public function test_12_wrong_otp_increments_attempts(): void
    {
        $user = $this->createUser();
        app(OtpService::class)->generate((int) $user->id, 'password_reset', 300);

        try {
            app(OtpService::class)->verify((int) $user->id, 'password_reset', '000000');
            $this->fail('OTP sai phải bị từ chối.');
        } catch (BusinessException) {
            $attempts = UserOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', 'password_reset')
                ->latest('id')
                ->value('attempts');

            $this->assertSame(1, (int) $attempts);
        }
    }

    #[TestDox('13. OTP hết hạn không được dùng')]
    public function test_13_expired_otp_is_rejected(): void
    {
        $user = $this->createUser();

        UserOtp::query()->create([
            'user_id' => $user->id,
            'purpose' => 'password_reset',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->subSecond(),
            'used_at' => null,
            'attempts' => 0,
        ]);

        $this->expectException(BusinessException::class);
        app(OtpService::class)->verify((int) $user->id, 'password_reset', '123456');
    }

    #[TestDox('14. OTP đã dùng không thể dùng lại')]
    public function test_14_used_otp_cannot_be_replayed(): void
    {
        $user = $this->createUser();
        $plain = app(OtpService::class)->generate((int) $user->id, 'password_reset', 300);

        app(OtpService::class)->verify((int) $user->id, 'password_reset', $plain);

        $this->expectException(BusinessException::class);
        app(OtpService::class)->verify((int) $user->id, 'password_reset', $plain);
    }

    #[TestDox('15. OTP vượt số lần thử cho phép phải bị chặn')]
    public function test_15_otp_max_attempts_is_enforced(): void
    {
        $user = $this->createUser();

        UserOtp::query()->create([
            'user_id' => $user->id,
            'purpose' => 'password_reset',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(5),
            'used_at' => null,
            'attempts' => 5,
        ]);

        $this->expectException(BusinessException::class);
        app(OtpService::class)->verify((int) $user->id, 'password_reset', '123456');
    }

    #[TestDox('16. OTP khác purpose của cùng user không ảnh hưởng nhau')]
    public function test_16_otp_purposes_are_isolated(): void
    {
        $user = $this->createUser();

        $passwordReset = app(OtpService::class)->generate((int) $user->id, 'password_reset', 300);
        app(OtpService::class)->generate((int) $user->id, 'payout_account_change', 300);

        app(OtpService::class)->verify((int) $user->id, 'password_reset', $passwordReset);

        $this->assertNull(
            UserOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', 'payout_account_change')
                ->latest('id')
                ->value('used_at')
        );
    }

    #[TestDox('17. Verify OTP email với mã sai phải bị từ chối và attempts tăng')]
    public function test_17_email_verify_wrong_otp_must_be_rejected(): void
    {
        $user = $this->createUser(status: 'inactive');

        $realOtp = app(OtpService::class)->generate(
            (int) $user->id,
            'email_verification',
            300
        );
        $wrongOtp = $realOtp === '000000' ? '999999' : '000000';

        $this->postJson('/api/auth/verify-otp', [
            'email' => $user->email,
            'otp' => $wrongOtp,
        ])->assertStatus(422);

        $user->refresh();
        $this->assertNull($user->email_verified_at);
        $this->assertSame('inactive', $user->status);

        $this->assertSame(
            1,
            (int) UserOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', 'email_verification')
                ->latest('id')
                ->value('attempts')
        );
    }

    #[TestDox('17B. Verify OTP email với mã đúng kích hoạt tài khoản và consume OTP')]
    public function test_17b_email_verify_correct_otp_activates_user(): void
    {
        $user = $this->createUser(status: 'inactive');

        $realOtp = app(OtpService::class)->generate(
            (int) $user->id,
            'email_verification',
            300
        );

        $this->postJson('/api/auth/verify-otp', [
            'email' => $user->email,
            'otp' => $realOtp,
        ])->assertOk();

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('active', $user->status);

        $this->assertNotNull(
            UserOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', 'email_verification')
                ->latest('id')
                ->value('used_at')
        );
    }

    // =====================================================================
    // C. SESSION MANAGEMENT
    // =====================================================================

    #[TestDox('18. Revoke một session chỉ set revoked_at cho session đó')]
    public function test_18_revoke_one_session_only_revokes_target(): void
    {
        $user = $this->createUser();
        $sessionA = $this->createAuthSession($user, 'A');
        $sessionB = $this->createAuthSession($user, 'B');

        app(AuthSessionService::class)->revokeForCurrentUser(
            (int) $user->id,
            (int) $sessionA->id
        );

        $sessionA->refresh();
        $sessionB->refresh();

        $this->assertNotNull($sessionA->revoked_at);
        $this->assertNull($sessionB->revoked_at);
    }

    #[TestDox('19. Logout all với keep current giữ session hiện tại và revoke session còn lại')]
    public function test_19_logout_all_keep_current(): void
    {
        $user = $this->createUser();
        $current = $this->createAuthSession($user, 'current');
        $other = $this->createAuthSession($user, 'other');

        app(AuthSessionService::class)->logoutAllForCurrentUser(
            (int) $user->id,
            (int) $current->id,
            true
        );

        $current->refresh();
        $other->refresh();

        $this->assertNull($current->revoked_at);
        $this->assertNotNull($other->revoked_at);
    }

    #[TestDox('20. Logout all không giữ current sẽ revoke toàn bộ session')]
    public function test_20_logout_all_without_keep_current(): void
    {
        $user = $this->createUser();
        $a = $this->createAuthSession($user, 'A');
        $b = $this->createAuthSession($user, 'B');

        app(AuthSessionService::class)->logoutAllForCurrentUser(
            (int) $user->id,
            (int) $a->id,
            false
        );

        $this->assertNotNull($a->refresh()->revoked_at);
        $this->assertNotNull($b->refresh()->revoked_at);
    }

    #[TestDox('21. Learner đạt giới hạn session phải bị chặn tạo session mới')]
    public function test_21_learner_session_limit_is_enforced(): void
    {
        config(['mindhub.max_learner_sessions' => 2]);

        $user = $this->createUser(role: 'learner');
        $this->createAuthSession($user, 'A');
        $this->createAuthSession($user, 'B');

        $this->expectException(BusinessException::class);
        app(DeviceLimitService::class)->assertCanCreateSession($user);
    }

    // =====================================================================
    // D. COUPON
    // =====================================================================

    #[TestDox('22. Một khóa học chỉ có tối đa một coupon')]
    public function test_22_one_course_has_at_most_one_coupon(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);

        $this->createCoupon($course);

        $this->expectException(QueryException::class);
        $this->createCoupon($course, discountValue: 10);
    }

    #[TestDox('23. Coupon code không được trùng')]
    public function test_23_coupon_code_is_unique(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $courseA = $this->createCourse($instructor);
        $courseB = $this->createCourse($instructor);

        $coupon = $this->createCoupon($courseA);

        $this->expectException(QueryException::class);
        Coupon::query()->create([
            'code' => $coupon->code,
            'course_id' => $courseB->id,
            'discount_type' => 'percent',
            'discount_value' => 10,
            'usage_limit' => 10,
            'used_count' => 0,
            'start_at' => now()->subMinute(),
            'end_at' => now()->addDay(),
            'status' => 'active',
        ]);
    }

    #[TestDox('24. Coupon active đúng thời gian và còn lượt được chấp nhận')]
    public function test_24_active_coupon_is_valid_now(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);
        $coupon = $this->createCoupon($course);

        $this->assertTrue($coupon->isActiveNow());
    }

    #[TestDox('25. Coupon inactive không được áp dụng')]
    public function test_25_inactive_coupon_is_rejected(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);
        $coupon = $this->createCoupon($course, status: 'inactive');

        $this->assertFalse($coupon->isActiveNow());
    }

    #[TestDox('26. Coupon chưa đến start_at không được áp dụng')]
    public function test_26_future_coupon_is_rejected(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);
        $coupon = $this->createCoupon(
            $course,
            startAt: now()->addHour(),
            endAt: now()->addDay()
        );

        $this->assertFalse($coupon->isActiveNow());
    }

    #[TestDox('27. Coupon quá end_at không được áp dụng')]
    public function test_27_expired_coupon_is_rejected(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);
        $coupon = $this->createCoupon(
            $course,
            startAt: now()->subDays(2),
            endAt: now()->subMinute()
        );

        $this->assertFalse($coupon->isActiveNow());
    }

    #[TestDox('28. Coupon hết usage_limit không được áp dụng')]
    public function test_28_used_up_coupon_is_rejected(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);
        $coupon = $this->createCoupon(
            $course,
            usageLimit: 5,
            usedCount: 5
        );

        $this->assertFalse($coupon->isActiveNow());
    }

    #[TestDox('29. Mã coupon giả không tồn tại database phải bị từ chối')]
    public function test_29_fake_dynamic_coupon_is_rejected(): void
    {
        $this->getJson('/api/coupons/validate?code=NOTREAL50')
            ->assertNotFound();
    }

    #[TestDox('30. Coupon hợp lệ không trả field legacy name, max_order_amount, user_id')]
    public function test_30_coupon_api_does_not_expose_legacy_fields(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);
        $coupon = $this->createCoupon($course);

        $this->getJson(
            '/api/coupons/validate?code=' . urlencode($coupon->code) .
            '&course_id=' . $course->id
        )
            ->assertOk()
            ->assertJsonMissingPath('data.name')
            ->assertJsonMissingPath('data.description')
            ->assertJsonMissingPath('data.max_order_amount')
            ->assertJsonMissingPath('data.user_id');
    }

    // =====================================================================
    // E. BANNER
    // =====================================================================

    #[TestDox('31. Banner model không dùng SoftDeletes/deleted_at')]
    public function test_31_banner_has_no_deleted_at_column(): void
    {
        $columns = DB::getSchemaBuilder()->getColumnListing('banners');

        $this->assertNotContains('deleted_at', $columns);
    }

    #[TestDox('32. Banner active có thể chuyển inactive')]
    public function test_32_banner_can_be_deactivated(): void
    {
        $banner = Banner::query()->create([
            'title' => 'Banner Runtime ' . $this->unique('banner'),
            'image_url' => 'https://example.test/banner.jpg',
            'target_url' => 'https://example.test',
            'position' => 'home_hero',
            'sort_order' => 1,
            'status' => 'active',
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
        ]);

        $banner->update(['status' => 'inactive']);

        $this->assertSame('inactive', $banner->refresh()->status);
    }

    #[TestDox('33. Hard delete banner xóa row thật khỏi database')]
    public function test_33_banner_hard_delete_removes_row(): void
    {
        $banner = Banner::query()->create([
            'title' => 'Banner Delete ' . $this->unique('banner'),
            'image_url' => 'https://example.test/banner-delete.jpg',
            'target_url' => 'https://example.test',
            'position' => 'home_hero',
            'sort_order' => 1,
            'status' => 'inactive',
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
        ]);

        $id = $banner->id;
        $banner->delete();

        $this->assertDatabaseMissing('banners', ['id' => $id]);
    }

    // =====================================================================
    // F. MARKETING ANNOUNCEMENT - CURRENT CONTRACT
    // =====================================================================

    #[TestDox('34. Course announcement hiện là stub 501 cho course thuộc instructor')]
    public function test_34_course_announcement_current_stub_contract(): void
    {
        $instructor = $this->createUser(role: 'instructor');
        $course = $this->createCourse($instructor);

        // actingAs dùng web guard; route middleware/role thực tế vẫn được exercise.
        $this->actingAs($instructor)
            ->postJson('/api/instructor/course-announcements', [
                'course_id' => $course->id,
                'title' => 'Thông báo test',
                'content' => 'Nội dung test',
            ])
            ->assertStatus(501);
    }

    #[TestDox('35. Instructor không được tạo announcement cho course người khác')]
    public function test_35_course_announcement_rejects_unowned_course(): void
    {
        $owner = $this->createUser(role: 'instructor');
        $other = $this->createUser(role: 'instructor');
        $course = $this->createCourse($owner);

        $this->actingAs($other)
            ->postJson('/api/instructor/course-announcements', [
                'course_id' => $course->id,
                'title' => 'Thông báo test',
                'content' => 'Nội dung test',
            ])
            ->assertStatus(403);
    }

    #[TestDox('36. Learner không được gọi API instructor marketing')]
    public function test_36_learner_cannot_use_instructor_marketing_api(): void
    {
        $learner = $this->createUser(role: 'learner');
        $owner = $this->createUser(role: 'instructor');
        $course = $this->createCourse($owner);

        $this->actingAs($learner)
            ->postJson('/api/instructor/course-announcements', [
                'course_id' => $course->id,
                'title' => 'Thông báo test',
                'content' => 'Nội dung test',
            ])
            ->assertStatus(403);
    }
}
