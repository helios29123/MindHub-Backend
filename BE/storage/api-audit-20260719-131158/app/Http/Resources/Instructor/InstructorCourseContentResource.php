<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorCourseContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course' => [
                'id' => (int) $this->id,
                'title' => $this->title,
                'slug' => $this->slug,
                'status' => $this->status,
            ],
            'sections' => $this->sections->map(fn ($section): array => [
                'id' => (int) $section->id,
                'course_id' => (int) $section->course_id,
                'title' => $section->title,
                'description' => $section->description,
                'sort_order' => (int) $section->sort_order,
                'status' => $section->status,
                'lessons' => $section->lessons->map(fn ($lesson): array => [
                    'id' => (int) $lesson->id,
                    'course_id' => (int) $lesson->course_id,
                    'course_section_id' => (int) $lesson->course_section_id,
                    'title' => $lesson->title,
                    'slug' => $lesson->slug,
                    'lesson_type' => $lesson->lesson_type,
                    'content' => $lesson->content,
                    'video_url' => $lesson->video_url,
                    'video_duration_seconds' => (int) $lesson->video_duration_seconds,
                    'is_preview' => (bool) $lesson->is_preview,
                    'status' => $lesson->status,
                    'sort_order' => (int) $lesson->sort_order,
                    'assets' => $lesson->assets->map(fn ($asset): array => [
                        'id' => (int) $asset->id,
                        'lesson_id' => (int) $asset->lesson_id,
                        'title' => $asset->title,
                        'file_url' => $asset->file_url,
                        'file_name' => $asset->file_name,
                        'file_type' => $asset->file_type,
                        'file_size' => (int) $asset->file_size,
                        'note' => $asset->note,
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}