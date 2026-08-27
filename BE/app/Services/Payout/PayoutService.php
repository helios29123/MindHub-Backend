<?php

namespace App\Services\Payout;

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

    public function process(WithdrawRequest $withdrawal): void
    {
        if ($withdrawal->status !== WithdrawRequest::STATUS_APPROVED) {
            Log::info(
                "PayoutService: skipped withdrawal #{$withdrawal->id}; "
                . "expected approved, got {$withdrawal->status}."
            );

            return;
        }

        try {
            $withdrawal->status = WithdrawRequest::STATUS_PROCESSING;
            $withdrawal->processed_at = now();
            $withdrawal->save();

            $response = $this->gateway->processPayout($withdrawal);

            $status = strtoupper((string) ($response['status'] ?? ''));
            $providerPayoutId = $response['provider_payout_id'] ?? null;
            $payoutProvider = $response['payout_provider'] ?? null;

            if ($providerPayoutId !== null && $providerPayoutId !== '') {
                $withdrawal->provider_payout_id = $providerPayoutId;
            }

            if ($payoutProvider !== null && $payoutProvider !== '') {
                $withdrawal->payout_provider = $payoutProvider;
            }

            $withdrawal->save();

            if ($status === 'SUCCESS') {
                $this->finalizeSuccess($withdrawal, $providerPayoutId);
                return;
            }

            if ($status === 'FAILED') {
                $this->finalizeProviderFailure(
                    $withdrawal,
                    (string) ($response['message'] ?? 'Payout failed at provider')
                );
                return;
            }

            Log::info(
                "PayoutService: withdrawal #{$withdrawal->id} remains processing."
            );
        } catch (\Throwable $e) {
            Log::error(
                "PayoutService: exception processing withdrawal #{$withdrawal->id}: "
                . $e->getMessage()
            );

            try {
                $fresh = WithdrawRequest::query()->find($withdrawal->id);

                if (
                    $fresh
                    && $fresh->status !== WithdrawRequest::STATUS_PAID
                    && $fresh->status !== WithdrawRequest::STATUS_MANUAL_REQUIRED
                ) {
                    $fresh->status = WithdrawRequest::STATUS_MANUAL_REQUIRED;
                    $fresh->failure_reason = $e->getMessage();
                    $fresh->processed_at = now();
                    $fresh->save();
                }
            } catch (\Throwable $secondary) {
                Log::error(
                    "PayoutService: failed to escalate withdrawal #{$withdrawal->id}: "
                    . $secondary->getMessage()
                );
            }
        }
    }

    public function resolveWebhook(
        WithdrawRequest $withdrawal,
        string $status,
        ?string $message = null
    ): void {
        $withdrawal->refresh();

        if ($withdrawal->status === WithdrawRequest::STATUS_PAID) {
            return;
        }

        $normalized = strtoupper($status);

        if ($normalized === 'SUCCESS') {
            if (! in_array($withdrawal->status, [
                WithdrawRequest::STATUS_PROCESSING,
                WithdrawRequest::STATUS_MANUAL_REQUIRED,
            ], true)) {
                Log::warning(
                    "PayoutService: ignored SUCCESS webhook for withdrawal "
                    . "#{$withdrawal->id} in status {$withdrawal->status}."
                );

                return;
            }

            $this->finalizeSuccess(
                $withdrawal,
                $withdrawal->provider_payout_id
            );

            return;
        }

        if ($normalized === 'FAILED') {
            if ($withdrawal->status !== WithdrawRequest::STATUS_PROCESSING) {
                return;
            }

            $this->finalizeProviderFailure(
                $withdrawal,
                $message ?? 'Webhook reported failure'
            );
        }
    }

    public function finalizeSuccess(
        WithdrawRequest $withdrawal,
        ?string $providerPayoutId = null
    ): void {
        DB::transaction(function () use ($withdrawal, $providerPayoutId): void {
            $locked = WithdrawRequest::query()
                ->whereKey($withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === WithdrawRequest::STATUS_PAID) {
                return;
            }

            if (! in_array($locked->status, [
                WithdrawRequest::STATUS_PROCESSING,
                WithdrawRequest::STATUS_MANUAL_REQUIRED,
            ], true)) {
                Log::warning(
                    "PayoutService: refused finalizeSuccess for withdrawal "
                    . "#{$locked->id} in status {$locked->status}."
                );

                return;
            }

            $locked->status = WithdrawRequest::STATUS_PAID;
            $locked->paid_at = $locked->paid_at ?? now();
            $locked->processed_at = $locked->processed_at ?? now();

            if ($providerPayoutId !== null && $providerPayoutId !== '') {
                $locked->provider_payout_id = $providerPayoutId;
            }

            $locked->save();
        });

        $withdrawal->refresh();
    }

    private function finalizeProviderFailure(
        WithdrawRequest $withdrawal,
        string $reason
    ): void {
        DB::transaction(function () use ($withdrawal, $reason): void {
            $locked = WithdrawRequest::query()
                ->whereKey($withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $locked->status === WithdrawRequest::STATUS_PAID
                || $locked->status === WithdrawRequest::STATUS_MANUAL_REQUIRED
            ) {
                return;
            }

            if ($locked->status !== WithdrawRequest::STATUS_PROCESSING) {
                return;
            }

            $locked->status = WithdrawRequest::STATUS_MANUAL_REQUIRED;
            $locked->failure_reason = $reason;
            $locked->processed_at = now();
            $locked->save();
        });

        $withdrawal->refresh();
    }
}
