<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lesson = $this->lesson;
        $course = $lesson?->course;

        return [
            'course' => $course ? [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'thumbnail_url' => $course->thumbnail_url,
            ] : null,
            'lesson' => $lesson ? [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'slug' => $lesson->slug,
                'lesson_type' => $lesson->lesson_type,
                'video_duration_seconds' => $lesson->video_duration_seconds ? (int) $lesson->video_duration_seconds : null,
            ] : null,
            'progress' => [
                'status' => $this->status,
                'started_at' => $this->started_at ? $this->started_at->toISOString() : null,
                'completed_at' => $this->completed_at ? $this->completed_at->toISOString() : null,
                'learning_duration_seconds' => (int) $this->learning_duration_seconds,
                'last_accessed_at' => $this->last_accessed_at ? $this->last_accessed_at->toISOString() : null,
                'current_second' => $this->current_second ?? 0,
            ],
        ];
    }
}
