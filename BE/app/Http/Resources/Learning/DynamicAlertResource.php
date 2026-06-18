<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->resource['type'],
            'title' => $this->resource['title'],
            'message' => $this->resource['message'],
            'action_url' => $this->resource['action_url'],
            'severity' => $this->resource['severity'] ?? 'info',
            'created_at' => $this->resource['created_at'] ?? now()->toIso8601String(),
        ];
    }
}
