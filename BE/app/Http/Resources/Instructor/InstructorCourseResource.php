<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'instructor_id' => $this->instructor_id,

            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,

            'thumbnail_url' => $this->thumbnail_url,
            'intro_video_url' => $this->intro_video_url,

            'price' => $this->price !== null ? (float) $this->price : 0,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,

            'level' => $this->level,
            'language' => $this->language,

            'requirements' => $this->requirements,
            'outcomes' => $this->outcomes,

            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
            'total_duration_seconds' => (int) $this->total_duration_seconds,

            'published_at' => $this->published_at,
            'admin_reject_reason' => $this->admin_reject_reason,

            'categories' => $this->whenLoaded('categories', function (): array {
                return $this->categories->map(function ($category): array {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'status' => $category->status,
                    ];
                })->values()->all();
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}