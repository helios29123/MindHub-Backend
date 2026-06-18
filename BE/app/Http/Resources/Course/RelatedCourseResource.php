<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelatedCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'thumbnail_url' => $this->thumbnail_url,
            'level' => $this->level,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'is_featured' => (bool) $this->is_featured,
            'published_at' => $this->published_at ? $this->published_at->toIso8601String() : null,
            'rating_avg' => $this->rating_avg !== null ? round((float) $this->rating_avg, 1) : null,
            'rating_count' => $this->when(isset($this->rating_count), $this->rating_count),
            'score' => $this->when(isset($this->score), $this->score),
            'reasons' => $this->when(isset($this->reasons), $this->reasons),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                });
            }),
            'instructor' => $this->whenLoaded('instructor', function () {
                return [
                    'id' => $this->instructor->id,
                    'full_name' => $this->instructor->full_name,
                ];
            }),
        ];
    }
}
