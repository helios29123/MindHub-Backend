<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class PayoutBatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'period_month' => $this->period_month, 'period_year' => $this->period_year, 'total_amount' => (float)$this->total_amount, 'total_instructors' => $this->total_instructors, 'status' => $this->status, 'paid_at' => $this->paid_at, 'note' => $this->note, 'items' => PayoutItemResource::collection($this->whenLoaded('items')), 'created_at' => $this->created_at];
    }
}
