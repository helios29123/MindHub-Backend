<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningOutlineLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $progress = $this->additional['progress'] ?? null;

        return [
            'id' => $this->id,
            'course_section_id' => $this->course_section_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lesson_type' => $this->lesson_type,
            'is_preview' => (bool) $this->is_preview,
            'sort_order' => (int) $this->sort_order,
            'video_duration_seconds' => $this->video_duration_seconds !== null ? (int) $this->video_duration_seconds : null,
            'progress' => $progress ? [
                'status' => $progress->status,
                'started_at' => $progress->started_at ? $progress->started_at->toISOString() : null,
                'completed_at' => $progress->completed_at ? $progress->completed_at->toISOString() : null,
                'learning_duration_seconds' => (int) $progress->learning_duration_seconds,
                'last_accessed_at' => $progress->last_accessed_at ? $progress->last_accessed_at->toISOString() : null,
            ] : null,
        ];
    }
}
