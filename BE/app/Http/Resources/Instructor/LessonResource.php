<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasAccess = $this->resource->is_preview || ($this->additional['has_access'] ?? false);

        return [
            'id' => $this->id,
            'course_section_id' => $this->course_section_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lesson_type' => $this->lesson_type,
            'is_preview' => (bool) $this->is_preview,
            'sort_order' => (int) $this->sort_order,
            'video_duration_seconds' => $this->video_duration_seconds !== null ? (int) $this->video_duration_seconds : null,
            'content' => $hasAccess ? $this->content : null,
            'video_url' => $hasAccess ? $this->video_url : null,
        ];
    }
}
