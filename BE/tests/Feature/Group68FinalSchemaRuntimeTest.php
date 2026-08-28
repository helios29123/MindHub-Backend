<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\User;
use App\Models\UserOtp;
use App\Services\Auth\AuthService;
use App\Services\Auth\OtpService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

final class Group68FinalSchemaRuntimeTest extends TestCase
{
    use DatabaseTransactions;

    #[TestDox('01. OTP FINAL được tạo, xác thực một lần và không thể phát lại')]
    public function test_01_otp_final_is_one_time_use(): void
    {
        $user = User::factory()->create([
            'password_hash' => Hash::make('Password123!'),
            'role' => 'learner',
            'status' => 'active',
        ]);

        $plain = app(OtpService::class)->generate((int) $user->id, 'group68_test', 300);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $plain);

        app(OtpService::class)->verify((int) $user->id, 'group68_test', $plain);

        $this->assertNotNull(
            DB::table('user_otps')
                ->where('user_id', $user->id)
                ->where('purpose', 'group68_test')
                ->value('used_at')
        );

        $this->expectException(\App\Exceptions\BusinessException::class);
        app(OtpService::class)->verify((int) $user->id, 'group68_test', $plain);
    }

    #[TestDox('02. Quên mật khẩu lưu mã trong user_otps, không dùng users.password_reset')]
    public function test_02_forgot_password_uses_user_otps(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'password_hash' => Hash::make('OldPassword123!'),
            'role' => 'learner',
            'status' => 'active',
        ]);

        $result = app(AuthService::class)->forgotPassword(['email' => $user->email]);

        $this->assertDatabaseHas('user_otps', [
            'user_id' => $user->id,
            'purpose' => 'password_reset',
        ]);

        $this->assertArrayHasKey('reset_token', $result);
    }

    #[TestDox('03. Đặt lại mật khẩu tiêu thụ OTP và cập nhật password_hash')]
    public function test_03_reset_password_consumes_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'password_hash' => Hash::make('OldPassword123!'),
            'role' => 'learner',
            'status' => 'active',
        ]);

        $plain = app(OtpService::class)->generate((int) $user->id, 'password_reset', 300);

        app(AuthService::class)->resetPassword([
            'email' => $user->email,
            'token' => $plain,
            'password' => 'NewPassword123!',
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password_hash));
    }

    #[TestDox('04. Coupon hợp lệ chỉ trả các field có trong DATABASE FINAL')]
    public function test_04_coupon_validation_uses_final_schema_only(): void
    {
        $instructor = User::factory()->create([
            'password_hash' => Hash::make('Password123!'),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Group 68 Coupon Course',
            'slug' => 'group-68-coupon-course-' . uniqid(),
            'price' => 500000,
            'discount_percent' => 0,
            'course_level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
        ]);

        Coupon::create([
            'code' => 'G68' . strtoupper(substr(md5((string) microtime(true)), 0, 8)),
            'course_id' => $course->id,
            'discount_type' => 'percent',
            'discount_value' => 20,
            'usage_limit' => 10,
            'used_count' => 0,
            'start_at' => now()->subMinute(),
            'end_at' => now()->addDay(),
            'status' => 'active',
        ]);

        $coupon = Coupon::where('course_id', $course->id)->firstOrFail();

        $response = $this->getJson('/api/coupons/validate?code=' . $coupon->code . '&course_id=' . $course->id);
        $response->assertOk()
            ->assertJsonPath('data.code', $coupon->code)
            ->assertJsonMissingPath('data.name')
            ->assertJsonMissingPath('data.description')
            ->assertJsonMissingPath('data.max_order_amount')
            ->assertJsonMissingPath('data.user_id');
    }

    #[TestDox('05. Mã giảm giá giả không tồn tại DB phải bị từ chối')]
    public function test_05_dynamic_fake_coupon_is_rejected(): void
    {
        $this->getJson('/api/coupons/validate?code=NOTREAL50')
            ->assertNotFound();
    }

    #[TestDox('06. Một khóa học không thể có hai coupon do unique course_id')]
    public function test_06_one_course_has_at_most_one_coupon(): void
    {
        $instructor = User::factory()->create([
            'password_hash' => Hash::make('Password123!'),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Unique Coupon Course',
            'slug' => 'unique-coupon-course-' . uniqid(),
            'price' => 500000,
            'discount_percent' => 0,
            'course_level' => 'beginner',
            'language' => 'vi',
            'status' => 'published',
        ]);

        Coupon::create([
            'code' => 'FIRST' . strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'course_id' => $course->id,
            'discount_type' => 'fixed',
            'discount_value' => 50000,
            'status' => 'active',
        ]);

        $this->expectException(QueryException::class);

        Coupon::create([
            'code' => 'SECOND' . strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'course_id' => $course->id,
            'discount_type' => 'fixed',
            'discount_value' => 25000,
            'status' => 'active',
        ]);
    }
}
