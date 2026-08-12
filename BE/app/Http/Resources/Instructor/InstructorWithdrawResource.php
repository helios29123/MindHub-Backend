<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorWithdrawResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'requested_at' => $this->requested_at,
            'approved_at' => $this->approved_at,
            'paid_at' => $this->paid_at,
            'rejected_reason' => $this->rejected_reason,
            'provider_payout_id' => $this->provider_payout_id,
            'account_number_masked' => $this->account_number_masked,
            'account_name_snapshot' => $this->account_name_snapshot,
        ];
    }
}