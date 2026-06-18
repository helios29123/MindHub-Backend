<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseLearningOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : [];

        return [
            'course_id' => (int) ($data['course_id'] ?? 0),
            'course_title' => $data['course_title'] ?? null,
            'course_thumbnail_url' => $data['course_thumbnail_url'] ?? null,
            'course_status' => $data['course_status'] ?? null,

            'enrollment_id' => isset($data['enrollment_id']) ? (int) $data['enrollment_id'] : null,
            'enrollment_status' => $data['enrollment_status'] ?? null,
            'progress_percent' => (float) ($data['progress_percent'] ?? 0),
            'completed_at' => $data['completed_at'] ?? null,
            'last_accessed_at' => $data['last_accessed_at'] ?? null,

            'total_lessons' => (int) ($data['total_lessons'] ?? 0),
            'completed_lessons' => (int) ($data['completed_lessons'] ?? 0),
            'in_progress_lessons' => (int) ($data['in_progress_lessons'] ?? 0),

            'current_lesson' => $data['current_lesson'] ?? null,
            'next_lesson' => $data['next_lesson'] ?? null,
            'sections' => array_values($data['sections'] ?? []),
        ];
    }
}