<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\WithdrawRequest;
use App\Models\PayoutAccount;

class WithdrawalPayoutModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_payout_sets_payout_provider_to_manual()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        $instructor = User::factory()->create(['role' => 'instructor', 'status' => 'active', 'email_verified_at' => now()]);
        $withdrawal = WithdrawRequest::create([
            'user_id' => $instructor->id,
            'status' => 'manual_required',
            'amount' => 100000,
            'payout_provider' => null,
            'provider_payout_id' => null,
            'type' => 'early_withdrawal'
        ]);

        $this->withoutExceptionHandling();

        /** @var \Illuminate\Contracts\Auth\Authenticatable $admin */
        $response = $this->actingAs($admin)
            ->patchJson("/api/admin/withdrawals/{$withdrawal->id}/mark-paid", [
                'provider_payout_id' => 'BANK-REF-123',
            ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('withdraw_requests', [
            'id' => $withdrawal->id,
            'status' => 'paid',
            'payout_provider' => 'manual',
            'provider_payout_id' => 'BANK-REF-123',
        ]);
    }

    public function test_manual_payout_failed_releases_balance()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        $instructor = User::factory()->create(['role' => 'instructor', 'status' => 'active', 'email_verified_at' => now()]);
        
        $withdrawal = WithdrawRequest::create([
            'user_id' => $instructor->id,
            'status' => 'manual_required',
            'amount' => 100000,
            'payout_provider' => null,
            'provider_payout_id' => null,
            'type' => 'early_withdrawal'
        ]);

        $this->withoutExceptionHandling();

        /** @var \Illuminate\Contracts\Auth\Authenticatable $admin */
        $response = $this->actingAs($admin)
            ->patchJson("/api/admin/withdrawals/{$withdrawal->id}/mark-failed", [
                'reason' => 'Bank account blocked',
            ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('withdraw_requests', [
            'id' => $withdrawal->id,
            'status' => 'failed',
            'failure_reason' => 'Bank account blocked',
        ]);
    }

    public function test_api_returns_payout_mode_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'email_verified_at' => now()]);
        $instructor = User::factory()->create(['role' => 'instructor', 'status' => 'active', 'email_verified_at' => now()]);
        $withdrawal = WithdrawRequest::create([
            'user_id' => $instructor->id,
            'status' => 'paid',
            'amount' => 100000,
            'payout_provider' => 'manual',
            'provider_payout_id' => 'BANK-REF-123',
            'type' => 'early_withdrawal'
        ]);

        /** @var \Illuminate\Contracts\Auth\Authenticatable $admin */
        $response = $this->actingAs($admin)
            ->getJson("/api/admin/withdrawals/{$withdrawal->id}");

        $response->assertSuccessful();
        $response->assertJsonPath('data.payout_provider', 'manual');
        $response->assertJsonPath('data.payout_mode', 'manual');
    }
}
