<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $averageRating = $this->getAttribute('average_rating');

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
            'published_at' => $this->published_at,

            'average_rating' => round((float) ($averageRating ?? 0), 1),
            'reviews_count' => (int) ($this->getAttribute('reviews_count') ?? 0),
            'enrollments_count' => (int) ($this->getAttribute('enrollments_count') ?? 0),
            'completed_enrollments_count' => (int) ($this->getAttribute('completed_enrollments_count') ?? 0),
            'average_progress_percent' => round((float) ($this->getAttribute('average_progress_percent') ?? 0), 1),
            'completion_rate' => ((int) ($this->getAttribute('enrollments_count') ?? 0)) > 0
                ? round(((int) ($this->getAttribute('completed_enrollments_count') ?? 0) / (int) $this->getAttribute('enrollments_count')) * 100, 1)
                : 0,

            'is_enrolled' => (bool) ($this->getAttribute('is_enrolled') ?? false),

            'instructor' => $this->whenLoaded('instructor', function () {
                return [
                    'id' => $this->instructor?->id,
                    'full_name' => $this->instructor?->full_name,
                    'avatar_url' => $this->instructor?->avatar_url,
                    'bio' => $this->instructor?->instructorProfile?->bio ?? 'Senior Software Engineer với nhiều năm kinh nghiệm giảng dạy và phát triển sản phẩm phần mềm thực tế.',
                    'headline' => $this->instructor?->instructorProfile?->expertise ?? 'Giảng viên MindHub',
                ];
            }),

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
