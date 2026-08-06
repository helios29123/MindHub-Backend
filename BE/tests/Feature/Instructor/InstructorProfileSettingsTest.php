<?php

namespace Tests\Feature\Instructor;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InstructorProfileSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = clone User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
            'email' => 'instructor_settings_test@mindhub.test',
            'phone' => '0912345678',
            'password_hash' => Hash::make('OldPassword123'),
        ]);
    }

    private function getAuthHeaders(string $email): array
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'OldPassword123',
            'device_name' => 'testing'
        ]);

        $token = $response->json('data.access_token');
        app('auth')->forgetGuards();
        return [
            'Authorization' => "Bearer $token",
        ];
    }

    public function test_send_otp_fails_if_current_password_wrong(): void
    {
        $response = $this->postJson('/api/instructor/profile/password/send-otp', [
            'current_password' => 'WrongPassword',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Mật khẩu hiện tại không chính xác.');
    }

    public function test_send_otp_fails_if_passwords_do_not_match(): void
    {
        $response = $this->postJson('/api/instructor/profile/password/send-otp', [
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'Mismatch123',
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(422);
    }

    public function test_send_otp_fails_if_new_password_same_as_old(): void
    {
        $response = $this->postJson('/api/instructor/profile/password/send-otp', [
            'current_password' => 'OldPassword123',
            'password' => 'OldPassword123',
            'password_confirmation' => 'OldPassword123',
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(422);
    }

    public function test_send_otp_success_creates_otp_and_sends_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/instructor/profile/password/send-otp', [
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['expires_in', 'resend_after', 'masked_email']]);

        $this->assertDatabaseHas('user_otps', [
            'user_id' => $this->instructor->id,
            'purpose' => 'change_password',
        ]);
    }

    public function test_change_password_fails_with_invalid_otp(): void
    {
        $response = $this->patchJson('/api/instructor/profile/password', [
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
            'otp' => '999999',
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(422);
    }

    public function test_change_password_success_with_valid_otp(): void
    {
        $otpCode = UserOtp::generateOtp((int) $this->instructor->id, 'change_password');

        $response = $this->patchJson('/api/instructor/profile/password', [
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
            'otp' => $otpCode,
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('NewPassword123', $this->instructor->fresh()->password_hash));
    }

    public function test_can_get_sessions_list(): void
    {
        $response = $this->getJson('/api/instructor/profile/sessions', $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => [['id', 'device', 'platform', 'ip_address', 'last_activity_at', 'is_current']]]);
    }

    public function test_can_get_and_update_privacy_settings(): void
    {
        $getRes = $this->getJson('/api/instructor/profile/privacy', $this->getAuthHeaders($this->instructor->email));
        $getRes->assertStatus(200);

        $patchRes = $this->patchJson('/api/instructor/profile/privacy', [
            'show_email' => true,
            'show_phone' => false,
            'profile_visibility' => 'students_only',
        ], $this->getAuthHeaders($this->instructor->email));

        $patchRes->assertStatus(200)
            ->assertJsonPath('data.show_email', true)
            ->assertJsonPath('data.profile_visibility', 'students_only');
    }

    public function test_sms_alerts_toggle_fails_if_phone_is_empty(): void
    {
        $userNoPhone = User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
            'phone' => null,
            'password_hash' => Hash::make('OldPassword123'),
        ]);

        $response = $this->patchJson('/api/instructor/profile/notification-preferences', [
            'sms_alerts' => true,
        ], $this->getAuthHeaders($userNoPhone->email));

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Bạn cần cập nhật số điện thoại trước khi bật SMS Alerts.');
    }

    public function test_public_profile_respects_private_visibility(): void
    {
        $this->patchJson('/api/instructor/profile/privacy', [
            'profile_visibility' => 'private',
            'show_email' => false,
            'show_phone' => false,
            'show_social_links' => false,
        ], $this->getAuthHeaders($this->instructor->email));

        $response = $this->getJson("/api/instructors/{$this->instructor->id}");

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Hồ sơ giảng viên này đang ở chế độ riêng tư.');
    }

    public function test_public_profile_hides_email_and_phone_when_disabled(): void
    {
        $this->patchJson('/api/instructor/profile/privacy', [
            'profile_visibility' => 'public',
            'show_email' => false,
            'show_phone' => false,
            'show_social_links' => false,
        ], $this->getAuthHeaders($this->instructor->email));

        $response = $this->getJson("/api/instructors/{$this->instructor->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.phone', null)
            ->assertJsonPath('data.social_links', null);
    }
}
