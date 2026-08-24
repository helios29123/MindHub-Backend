<?php

namespace App\Services\Payout;

use App\Exceptions\BusinessException;
use App\Models\Revenue;
use App\Models\WithdrawRequest;
use App\Services\Payout\Contracts\PayoutGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    public function __construct(
        private readonly PayoutGatewayInterface $gateway,
        private readonly EarlyWithdrawalService $earlyWithdrawalService
    ) {
    }

    /**
     * Process a payout via the configured gateway.
     */
    public function process(WithdrawRequest $withdrawal): void
    {
        // Guard against duplicate processing
        if (!in_array($withdrawal->status, [WithdrawRequest::STATUS_APPROVED, WithdrawRequest::STATUS_QUEUED])) {
            Log::info("PayoutService: Skipped processing withdrawal #{$withdrawal->id} due to invalid status ({$withdrawal->status}).");
            return;
        }

        try {
            $withdrawal->status = WithdrawRequest::STATUS_PROCESSING;
            $withdrawal->save();

            $response = $this->gateway->processPayout($withdrawal);
            
            $status = strtoupper($response['status'] ?? '');
            $providerPayoutId = $response['provider_payout_id'] ?? null;
            $payoutProvider = $response['payout_provider'] ?? 'fake';
            
            if ($providerPayoutId) {
                $withdrawal->provider_payout_id = $providerPayoutId;
                $withdrawal->payout_provider = $payoutProvider;
                $withdrawal->save();
            }

            if ($status === 'SUCCESS') {
                $this->finalizeSuccess($withdrawal, $providerPayoutId);
            } elseif ($status === 'FAILED') {
                $this->finalizeFailed($withdrawal, $response['message'] ?? 'Payout failed at provider');
            } else {
                // Keep it in PROCESSING state
                Log::info("PayoutService: Withdrawal #{$withdrawal->id} is now processing at provider.");
            }
        } catch (\Exception $e) {
            Log::error("PayoutService: Exception processing withdrawal #{$withdrawal->id} - " . $e->getMessage());
            // Optionally, handle unexpected error (keep processing, or fail it depending on business need).
            // For now we keep it processing to not accidentally release funds on network timeout.
        }
    }

    /**
     * Called when a webhook or manual resolve confirms the payout is successful.
     */
    public function resolveWebhook(WithdrawRequest $withdrawal, string $status, ?string $message = null): void
    {
        if ($withdrawal->status === WithdrawRequest::STATUS_PAID) {
            return; // Idempotent check
        }

        if (strtoupper($status) === 'SUCCESS') {
            $this->finalizeSuccess($withdrawal, $withdrawal->provider_payout_id);
        } elseif (strtoupper($status) === 'FAILED') {
            $this->finalizeFailed($withdrawal, $message ?? 'Webhook reported failure');
        }
    }

    /**
     * Finalizes the withdrawal as SUCCESS/PAID.
     */
    public function finalizeSuccess(WithdrawRequest $withdrawal, ?string $providerPayoutId = null): void
    {
        if ($withdrawal->status === WithdrawRequest::STATUS_PAID) {
            return;
        }

        DB::transaction(function () use ($withdrawal, $providerPayoutId) {
            $withdrawal->status = WithdrawRequest::STATUS_PAID;
            $withdrawal->paid_at = now();
            if ($providerPayoutId) {
                $withdrawal->provider_payout_id = $providerPayoutId;
            }
            $withdrawal->save();

            // Mark associated revenues as paid ONLY IF fully allocated
            foreach ($withdrawal->allocatedRevenues as $revenue) {
                $totalAllocated = DB::table('withdrawal_revenues')
                    ->join('withdraw_requests', 'withdraw_requests.id', '=', 'withdrawal_revenues.withdrawal_id')
                    ->where('withdrawal_revenues.revenue_id', $revenue->id)
                    ->whereIn('withdraw_requests.status', [
                        WithdrawRequest::STATUS_PENDING, WithdrawRequest::STATUS_APPROVED, 
                        WithdrawRequest::STATUS_QUEUED, WithdrawRequest::STATUS_PROCESSING, WithdrawRequest::STATUS_PAID
                    ])
                    ->sum('withdrawal_revenues.allocated_amount');

                if ($totalAllocated >= $revenue->instructor_amount) {
                    $revenue->update([
                        'status' => Revenue::STATUS_PAID,
                        'updated_at' => now()
                    ]);
                }
            }

            // For auto payout fallback
            $withdrawal->revenues()->update([
                'status' => Revenue::STATUS_PAID,
                'updated_at' => now()
            ]);
        });

        // Send Email & Notification to Instructor
        try {
            $user = $withdrawal->user;
            if ($user && $user->email) {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WithdrawalSuccessInstructorMail($withdrawal, $user));

                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'payout_success',
                    'title' => 'Chuyển tiền thành công',
                    'message' => "Yêu cầu rút tiền #" . $withdrawal->id . " số tiền " . number_format($withdrawal->amount, 0, ',', '.') . " đ đã được chuyển thành công vào tài khoản ngân hàng của bạn.",
                    'data' => json_encode(['withdrawal_id' => $withdrawal->id, 'amount' => $withdrawal->amount]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send instructor payout success email/notification: ' . $e->getMessage());
        }
    }

    /**
     * Finalizes the withdrawal as FAILED.
     */
    private function finalizeFailed(WithdrawRequest $withdrawal, string $reason): void
    {
        if ($withdrawal->status === WithdrawRequest::STATUS_FAILED) {
            return;
        }

        DB::transaction(function () use ($withdrawal, $reason) {
            $withdrawal->status = WithdrawRequest::STATUS_FAILED;
            $withdrawal->failure_reason = $reason;
            $withdrawal->save();

            $this->earlyWithdrawalService->releaseAllocations($withdrawal);
        });
    }
}
