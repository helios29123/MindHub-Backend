<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class LessonResource extends JsonResource
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
            'video_duration_seconds' => $this->video_duration_seconds,
            'is_preview' => (bool) $this->is_preview,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'assets' => LessonAssetResource::collection($this->whenLoaded('assets')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
