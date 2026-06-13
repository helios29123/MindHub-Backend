<?php

namespace App\Http\Resources\Learning;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'progress_percent' => $this->progress_percent !== null ? (float) $this->progress_percent : 0.00,
            'enrolled_at' => $this->enrolled_at ? $this->enrolled_at->toISOString() : null,
            'completed_at' => $this->completed_at ? $this->completed_at->toISOString() : null,
            'last_accessed_at' => $this->last_accessed_at ? $this->last_accessed_at->toISOString() : null,
            'course' => [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'slug' => $this->course->slug,
                'short_description' => $this->course->short_description,
                'thumbnail_url' => $this->course->thumbnail_url,
                'price' => (float) $this->course->price,
                'sale_price' => $this->course->sale_price !== null ? (float) $this->course->sale_price : null,
                'level' => $this->course->level,
                'language' => $this->course->language,
                'total_duration_seconds' => (int) $this->course->total_duration_seconds,
                'instructor' => [
                    'id' => $this->course->instructor->id,
                    'full_name' => $this->course->instructor->full_name,
                ],
            ],
        ];
    }
}
