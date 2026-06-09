<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'thumbnail_url' => $this->thumbnail_url,
            'intro_video_url' => $this->intro_video_url,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'level' => $this->level,
            'language' => $this->language,
            'is_featured' => (bool) $this->is_featured,
            'total_duration_seconds' => (int) $this->total_duration_seconds,
            'published_at' => optional($this->published_at)->toISOString(),
            'average_rating' => $this->reviews_avg_rating !== null ? round((float) $this->reviews_avg_rating, 1) : null,
            'enrollments_count' => (int) ($this->enrollments_count ?? 0),
            'is_enrolled' => (bool) ($this->learner_enrolled ?? false),
            'instructor' => $this->whenLoaded('instructor', fn () => [
                'id' => $this->instructor->id,
                'full_name' => $this->instructor->full_name,
            ]),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
