<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class CommissionRuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'sale_channel' => $this->sale_channel, 'instructor_rate' => (float)$this->instructor_rate, 'platform_rate' => (float)$this->platform_rate, 'description' => $this->description, 'is_active' => (bool)$this->is_active, 'updated_at' => $this->updated_at];
    }
}
