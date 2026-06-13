<?php
namespace App\Http\Resources\Moderation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class PendingCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'instructor_id' => $this->instructor_id,
            'instructor' => $this->whenLoaded('instructor', function (): array {
                return [
                    'id' => $this->instructor?->id,
                    'full_name' => $this->instructor?->full_name,
                    'email' => $this->instructor?->email,
                    'status' => $this->instructor?->status,
                ];
            }),
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'thumbnail_url' => $this->thumbnail_url,
            'intro_video_url' => $this->intro_video_url,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'level' => $this->level,
            'language' => $this->language,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'total_duration_seconds' => $this->total_duration_seconds,
            'published_at' => $this->published_at?->toDateTimeString(),
            'admin_reject_reason' => $this->admin_reject_reason,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}