<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'created_at' => optional($this->created_at)->toISOString(),
            'reviewer' => $this->when($this->order && $this->order->user, fn () => [
                'id' => $this->order->user->id,
                'full_name' => $this->order->user->full_name,
                'avatar_url' => $this->order->user->avatar_url,
            ]),
        ];
    }
}
