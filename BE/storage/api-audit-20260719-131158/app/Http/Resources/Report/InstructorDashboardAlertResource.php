<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorDashboardAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->resource['type'],
            'title' => $this->resource['title'],
            'message' => $this->resource['message'],
            'created_at' => $this->resource['created_at'],
            'action_url' => $this->resource['action_url'] ?? null,
            'read_at' => $this->resource['read_at'] ?? null,
        ];
    }
}