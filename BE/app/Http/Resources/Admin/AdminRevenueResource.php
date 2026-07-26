<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class AdminRevenueResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'order_id' => $this->order_id, 'course' => ['id' => $this->course?->id, 'title' => $this->course?->title], 'instructor' => ['id' => $this->instructor?->id, 'name' => $this->instructor?->name ?? $this->instructor?->full_name], 'gross_amount' => (float)$this->gross_amount, 'instructor_amount' => (float)$this->instructor_amount, 'platform_fee_amount' => (float)$this->platform_fee_amount, 'sale_channel' => $this->sale_channel, 'instructor_rate_percent' => (float)($this->instructor_rate_percent ?? 0), 'platform_rate_percent' => (float)($this->platform_rate_percent ?? 0), 'status' => $this->status, 'earned_at' => $this->earned_at];
    }
}
