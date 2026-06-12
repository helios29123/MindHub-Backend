<?php
namespace App\Http\Resources\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
final class ReviewNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'admin_reject_reason' => $this->admin_reject_reason,
            'updated_at' => $this->updated_at,
        ];
    }
}