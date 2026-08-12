<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class PayoutItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'payout_batch_id' => $this->payout_batch_id, 'instructor' => ['id' => $this->instructor?->id, 'name' => $this->instructor?->name ?? $this->instructor?->full_name, 'email' => $this->instructor?->email], 'payout_account' => new PayoutAccountResource($this->whenLoaded('payoutAccount')), 'gross_amount' => (float)$this->gross_amount, 'instructor_amount' => (float)$this->instructor_amount, 'platform_fee_amount' => (float)$this->platform_fee_amount, 'paid_amount' => (float)$this->paid_amount, 'status' => $this->status, 'transaction_code' => $this->transaction_code, 'paid_at' => $this->paid_at, 'note' => $this->note, 'revenues' => AdminRevenueResource::collection($this->whenLoaded('revenues'))];
    }
}
