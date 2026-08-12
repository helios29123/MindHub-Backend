<?php

namespace Tests\Feature;

use App\Models\PayoutAccount;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayoutAccountOtpTest extends TestCase
{
    use DatabaseTransactions;

    public function test_instructor_can_request_otp_for_payout_account_change(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
            'email' => 'instructor_otp@mindhub.test',
        ]);

        $account = PayoutAccount::create([
            'user_id' => $user->id,
            'provider' => 'Techcombank (TCB)',
            'account_number' => '1903000000001',
            'account_name' => 'NGUYEN VAN A',
            'status' => 'active',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/instructor/payout-accounts/{$account->id}/send-change-otp", [
                'bank_code' => 'TCB',
                'bank_name' => 'Techcombank',
                'account_number' => '190367896789',
                'account_holder_name' => 'NGUYEN MINH KHOA',
                'branch_name' => 'Chi nhánh Hà Nội',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Mã OTP đã được gửi đến email của bạn.',
            ]);

        $this->assertDatabaseHas('user_otps', [
            'user_id' => $user->id,
            'purpose' => 'payout_account_change',
        ]);
    }

    public function test_instructor_cannot_verify_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
        ]);

        UserOtp::generateOtp($user->id, 'payout_account_change', 300);

        $response = $this->actingAs($user)
            ->postJson('/api/instructor/payout-accounts/0/verify-change', [
                'otp' => '000000',
            ]);

        $response->assertStatus(422);
    }

    public function test_instructor_can_verify_otp_and_update_payout_account(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $account = PayoutAccount::create([
            'user_id' => $user->id,
            'provider' => 'Techcombank',
            'account_number' => '1903000000001',
            'account_name' => 'OLD NAME',
            'status' => 'active',
            'is_default' => true,
        ]);

        // Send OTP
        $this->actingAs($user)
            ->postJson("/api/instructor/payout-accounts/{$account->id}/send-change-otp", [
                'bank_code' => 'TCB',
                'bank_name' => 'Techcombank',
                'account_number' => '1903999999999',
                'account_holder_name' => 'NEW ACCOUNT HOLDER',
            ])
            ->assertStatus(200);

        // Fetch generated OTP from DB
        $otpRecord = UserOtp::where('user_id', $user->id)
            ->where('purpose', 'payout_account_change')
            ->latest()
            ->first();

        $this->assertNotNull($otpRecord);

        // Manually set known OTP hash for verification test
        $otpRecord->update([
            'code_hash' => Hash::make('123456'),
        ]);

        // Verify OTP
        $verifyRes = $this->actingAs($user)
            ->postJson("/api/instructor/payout-accounts/{$account->id}/verify-change", [
                'otp' => '123456',
            ]);

        $verifyRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật tài khoản nhận tiền thành công.',
            ]);

        $this->assertDatabaseHas('payout_accounts', [
            'id' => $account->id,
            'user_id' => $user->id,
            'account_number' => '1903999999999',
            'account_name' => 'NEW ACCOUNT HOLDER',
            'is_default' => 1,
        ]);
    }

    public function test_instructor_can_reveal_payout_account_with_correct_password(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        $account = PayoutAccount::create([
            'user_id' => $user->id,
            'provider' => 'Techcombank',
            'account_number' => '19031234567890',
            'account_name' => 'NGUYEN VAN A',
            'status' => 'active',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/instructor/payout-accounts/{$account->id}/reveal", [
                'password' => 'secret123',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Xác thực thành công.',
                'data' => [
                    'account_number' => '19031234567890',
                    'expires_in' => 30,
                ],
            ]);
    }

    public function test_instructor_cannot_reveal_payout_account_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        $account = PayoutAccount::create([
            'user_id' => $user->id,
            'provider' => 'Techcombank',
            'account_number' => '19031234567890',
            'account_name' => 'NGUYEN VAN A',
            'status' => 'active',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/instructor/payout-accounts/{$account->id}/reveal", [
                'password' => 'wrongpassword',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Mật khẩu xác nhận không chính xác.',
            ]);
    }

    public function test_instructor_cannot_reveal_unowned_payout_account(): void
    {
        $owner = User::factory()->create(['role' => 'instructor', 'status' => 'active']);
        $otherUser = User::factory()->create(['role' => 'instructor', 'status' => 'active', 'password' => Hash::make('secret123')]);

        $account = PayoutAccount::create([
            'user_id' => $owner->id,
            'provider' => 'Techcombank',
            'account_number' => '19031234567890',
            'account_name' => 'OWNER NAME',
            'status' => 'active',
        ]);

        $response = $this->actingAs($otherUser)
            ->postJson("/api/instructor/payout-accounts/{$account->id}/reveal", [
                'password' => 'secret123',
            ]);

        $response->assertStatus(404);
    }

    public function test_instructor_cannot_reveal_inactive_payout_account(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
            'password' => Hash::make('secret123'),
        ]);

        $account = PayoutAccount::create([
            'user_id' => $user->id,
            'provider' => 'Techcombank',
            'account_number' => '19031234567890',
            'account_name' => 'INACTIVE USER',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/instructor/payout-accounts/{$account->id}/reveal", [
                'password' => 'secret123',
            ]);

        $response->assertStatus(409);
    }
}
