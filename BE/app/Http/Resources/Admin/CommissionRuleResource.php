<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

final class CommissionRuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'instructor_rate' => (float) $this->instructor_rate,
            'platform_rate' => (float) $this->platform_rate,
            'is_active' => (bool) $this->is_active,
            'updated_at' => $this->updated_at,
        ];
    }
}
