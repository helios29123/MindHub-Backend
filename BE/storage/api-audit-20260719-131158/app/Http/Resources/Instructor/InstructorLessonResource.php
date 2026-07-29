<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class InstructorLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_section_id' => $this->course_section_id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lesson_type' => $this->lesson_type,
            'content' => $this->content,
            'video_url' => $this->video_url,
            'video_duration_seconds' => $this->video_duration_seconds,
            'is_preview' => (bool) $this->is_preview,
            'status' => $this->status,
            'sort_order' => (int) $this->sort_order,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}