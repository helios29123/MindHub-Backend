<?php
namespace App\Http\Resources\Moderation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class CourseRejectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'instructor_id' => (int) $this->instructor_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'status' => $this->status,
            'admin_reject_reason' => $this->admin_reject_reason,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'instructor' => $this->whenLoaded('instructor', function (): array {
                return [
                    'id' => (int) $this->instructor->id,
                    'full_name' => $this->instructor->full_name,
                    'email' => $this->instructor->email,
                ];
            }),
        ];
    }
}