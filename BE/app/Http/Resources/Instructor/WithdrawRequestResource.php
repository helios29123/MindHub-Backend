<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'user_id' => (int) $this->user_id,
            'payout_account_id' => (int) $this->payout_account_id,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'status' => $this->status,
            'requested_at' => optional($this->requested_at)->toDateTimeString(),
            'approved_at' => optional($this->approved_at)->toDateTimeString(),
            'paid_at' => optional($this->paid_at)->toDateTimeString(),
            'rejected_reason' => $this->rejected_reason,
            'provider_payout_id' => $this->provider_payout_id,
            'account_number_snapshot' => $this->account_number_snapshot,
            'account_name_snapshot' => $this->account_name_snapshot,
            'available_balance_before' => $this->when(
                $this->offsetExists('available_balance_before'),
                $this->available_balance_before
            ),
            'available_balance_after' => $this->when(
                $this->offsetExists('available_balance_after'),
                $this->available_balance_after
            ),
            'payout_account' => $this->whenLoaded('payoutAccount', function (): ?array {
                if (!$this->payoutAccount) {
                    return null;
                }

                return [
                    'id' => (int) $this->payoutAccount->id,
                    'provider' => $this->payoutAccount->provider,
                    'account_number' => $this->payoutAccount->account_number,
                    'account_name' => $this->payoutAccount->account_name,
                    'status' => $this->payoutAccount->status,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}