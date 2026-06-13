<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LearningLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'course_section_id' => $this->course_section_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lesson_type' => $this->lesson_type,
            'content' => $this->content,
            'video_url' => $this->video_url,
            'video_duration_seconds' => $this->video_duration_seconds !== null ? (int) $this->video_duration_seconds : null,
            'is_preview' => (bool) $this->is_preview,
            'status' => $this->status,
            'sort_order' => (int) $this->sort_order,
            'assets' => $this->assets ? $this->assets->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'title' => $asset->title,
                    'file_url' => $asset->file_url,
                    'file_name' => $asset->file_name,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size !== null ? (int) $asset->file_size : null,
                    'note' => $asset->note,
                ];
            }) : [],
        ];
    }
}
