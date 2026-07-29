<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCourseResource extends JsonResource
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
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'level' => $this->level,
            'language' => $this->language,
            'requirements' => $this->requirements,
            'outcomes' => $this->outcomes,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'total_duration_seconds' => $this->total_duration_seconds,
            'published_at' => $this->published_at?->toIsoString(),
            'admin_reject_reason' => $this->admin_reject_reason,
            'created_at' => $this->created_at?->toIsoString(),
            'updated_at' => $this->updated_at?->toIsoString(),

            'instructor' => $this->whenLoaded('instructor', function () {
                return [
                    'id' => $this->instructor->id,
                    'full_name' => $this->instructor->full_name,
                    'email' => $this->instructor->email,
                    'phone' => $this->instructor->phone,
                    'role' => $this->instructor->role,
                    'status' => $this->instructor->status,
                ];
            }),

            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'status' => $category->status,
                    ];
                });
            }),
        ];
    }
}
