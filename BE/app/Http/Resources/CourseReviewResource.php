<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->order?->user;
        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'content' => $this->comment,
            'created_at' => optional($this->created_at)->toISOString(),
            'reviewer_id' => $user?->id,
            'reviewer_name' => $user?->full_name ?? 'Học viên MindHub',
            'reviewer_avatar' => $user?->avatar_url,
            'reviewer' => [
                'id' => $user?->id,
                'full_name' => $user?->full_name ?? 'Học viên MindHub',
                'avatar_url' => $user?->avatar_url,
            ],
        ];
    }
}
