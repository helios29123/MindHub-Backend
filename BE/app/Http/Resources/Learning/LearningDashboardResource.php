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
            'statistics' => [
                'active_courses' => (int) ($data['statistics']['active_courses'] ?? 0),
                'completed_courses' => (int) ($data['statistics']['completed_courses'] ?? 0),
                'total_learning_hours' => (int) ($data['statistics']['total_learning_hours'] ?? 0),
                'certificates_count' => (int) ($data['statistics']['certificates_count'] ?? 0),
            ],
            'recent_course' => isset($data['recent_course']) && $data['recent_course'] !== null ? [
                'course_id' => (int) $data['recent_course']['course_id'],
                'title' => $data['recent_course']['title'],
                'thumbnail_url' => $data['recent_course']['thumbnail_url'],
                'category_name' => $data['recent_course']['category_name'],
                'progress_percent' => (float) $data['recent_course']['progress_percent'],
                'current_lesson' => $data['recent_course']['current_lesson'] ? [
                    'lesson_id' => (int) $data['recent_course']['current_lesson']['lesson_id'],
                    'title' => $data['recent_course']['current_lesson']['title'],
                    'index_text' => $data['recent_course']['current_lesson']['index_text'],
                ] : null,
            ] : null,
        ];
    }
}