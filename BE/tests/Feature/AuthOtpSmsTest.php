<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthOtpSmsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_resend_otp_via_sms_for_phone_number(): void
    {
        $uniquePhone = '09' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        $user = User::factory()->create([
            'email' => 'test_sms_' . time() . '@mindhub.test',
            'phone' => $uniquePhone,
            'email_verified_at' => null,
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/auth/resend-verify-otp', [
            'phone' => $uniquePhone,
            'channel' => 'sms',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.channel', 'sms');
        $this->assertNotEmpty($response->json('data.otp_code'));

        $otpCode = $response->json('data.otp_code');

        // Test verifying with this OTP
        $verifyResponse = $this->postJson('/api/auth/verify-otp', [
            'phone' => $uniquePhone,
            'otp' => $otpCode,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJsonPath('success', true);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
