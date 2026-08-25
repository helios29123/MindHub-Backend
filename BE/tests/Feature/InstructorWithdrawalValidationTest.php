<?php

namespace Tests\Feature;

use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstructorWithdrawalValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        \Schema::disableForeignKeyConstraints();
        if (!\Schema::hasColumn('sessions', 'refresh_token_hash')) {
            \Schema::dropIfExists('sessions');
            \Schema::create('sessions', function ($table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('refresh_token_hash', 255)->nullable();
                $table->string('device_name', 255)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    private function generateAuthToken(User $user): string
    {
        $session = \App\Models\Session::create([
            'user_id' => $user->id,
            'refresh_token_hash' => 'dummy_' . uniqid(),
            'expires_at' => now()->addDays(1),
        ]);

        $tokenService = $this->app->make(\App\Services\Auth\AccessTokenService::class);
        return $tokenService->createAccessToken($user->id, $session->id)['token'];
    }

    private function createUser(): User
    {
        $id = DB::table('users')->insertGetId([
            'full_name' => 'Withdrawal Test User ' . uniqid(),
            'email' => 'withdraw_' . uniqid() . '@mindhub.test',
            'password_hash' => bcrypt('password'),
            'role' => 'instructor',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return User::find($id);
    }

    public function test_instructor_with_zero_balance_cannot_create_withdrawal(): void
    {
        $instructor = $this->createUser();
        $token = $this->generateAuthToken($instructor);

        $payoutAccount = PayoutAccount::create([
            'user_id' => $instructor->id,
            'provider' => 'bank',
            'account_number' => '1234567890',
            'account_name' => 'TEST INSTRUCTOR',
            'status' => 'active',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/instructor/withdrawals', [
                'amount' => 300000,
                'payout_account_id' => $payoutAccount->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_withdrawal_amount_less_than_minimum_200k_is_rejected(): void
    {
        $instructor = $this->createUser();
        $token = $this->generateAuthToken($instructor);

        $payoutAccount = PayoutAccount::create([
            'user_id' => $instructor->id,
            'provider' => 'bank',
            'account_number' => '1234567890',
            'account_name' => 'TEST INSTRUCTOR',
            'status' => 'active',
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/instructor/withdrawals', [
                'amount' => 199999,
                'payout_account_id' => $payoutAccount->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_withdrawal_exceeding_available_balance_is_rejected(): void
    {
        $instructor = $this->createUser();
        $token = $this->generateAuthToken($instructor);

        $payoutAccount = PayoutAccount::create([
            'user_id' => $instructor->id,
            'provider' => 'bank',
            'account_number' => '1234567890',
            'account_name' => 'TEST INSTRUCTOR',
            'status' => 'active',
        ]);

        // Add 500,000 revenue
        Revenue::create([
            'instructor_id' => $instructor->id,
            'course_id' => 1,
            'order_id' => rand(1000000, 9999999),
            'gross_amount' => 500000,
            'instructor_amount' => 500000,
            'platform_fee_amount' => 0,
            'status' => 'available',
            'earned_at' => now(),
        ]);

        // Try to withdraw 600,000
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/instructor/withdrawals', [
                'amount' => 600000,
                'payout_account_id' => $payoutAccount->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_withdrawal_using_another_instructors_payout_account_is_rejected(): void
    {
        $instructor1 = $this->createUser();
        $token1 = $this->generateAuthToken($instructor1);

        $instructor2 = $this->createUser();

        $payoutAccountOther = PayoutAccount::create([
            'user_id' => $instructor2->id,
            'provider' => 'bank',
            'account_number' => '9999999999',
            'account_name' => 'OTHER INSTRUCTOR',
            'status' => 'active',
        ]);

        Revenue::create([
            'instructor_id' => $instructor1->id,
            'course_id' => 1,
            'order_id' => rand(1000000, 9999999),
            'gross_amount' => 1000000,
            'instructor_amount' => 1000000,
            'platform_fee_amount' => 0,
            'status' => 'available',
            'earned_at' => now(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token1"])
            ->postJson('/api/instructor/withdrawals', [
                'amount' => 500000,
                'payout_account_id' => $payoutAccountOther->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payout_account_id']);
    }

    public function test_withdrawal_with_inactive_payout_account_is_rejected(): void
    {
        $instructor = $this->createUser();
        $token = $this->generateAuthToken($instructor);

        $inactiveAccount = PayoutAccount::create([
            'user_id' => $instructor->id,
            'provider' => 'bank',
            'account_number' => '1234567890',
            'account_name' => 'TEST INSTRUCTOR',
            'status' => 'inactive',
        ]);

        Revenue::create([
            'instructor_id' => $instructor->id,
            'course_id' => 1,
            'order_id' => rand(1000000, 9999999),
            'gross_amount' => 1000000,
            'instructor_amount' => 1000000,
            'platform_fee_amount' => 0,
            'status' => 'available',
            'earned_at' => now(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/instructor/withdrawals', [
                'amount' => 500000,
                'payout_account_id' => $inactiveAccount->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payout_account_id']);
    }

    public function test_withdrawal_with_sufficient_balance_and_active_account_succeeds(): void
    {
        $instructor = $this->createUser();
        $token = $this->generateAuthToken($instructor);

        $payoutAccount = PayoutAccount::create([
            'user_id' => $instructor->id,
            'provider' => 'bank',
            'account_number' => '1234567890',
            'account_name' => 'TEST INSTRUCTOR',
            'status' => 'active',
        ]);

        Revenue::create([
            'instructor_id' => $instructor->id,
            'course_id' => 1,
            'order_id' => rand(1000000, 9999999),
            'gross_amount' => 1000000,
            'instructor_amount' => 1000000,
            'platform_fee_amount' => 0,
            'status' => 'available',
            'earned_at' => now(),
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/instructor/withdrawals', [
                'amount' => 500000,
                'payout_account_id' => $payoutAccount->id,
                'note' => 'Test withdrawal note',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('withdraw_requests', [
            'user_id' => $instructor->id,
            'amount' => 500000,
            'status' => 'pending',
        ]);
    }
}
