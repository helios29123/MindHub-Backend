<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : [];

        return [
            'total_courses' => (int) ($data['total_courses'] ?? 0),
            'completed_courses' => (int) ($data['completed_courses'] ?? 0),
            'in_progress_courses' => (int) ($data['in_progress_courses'] ?? 0),
            'average_progress_percent' => (float) ($data['average_progress_percent'] ?? 0),
            'last_accessed_at' => $data['last_accessed_at'] ?? null,
            'recent_learning' => array_values($data['recent_learning'] ?? []),
            'alerts' => array_values($data['alerts'] ?? []),
        ];
    }
}