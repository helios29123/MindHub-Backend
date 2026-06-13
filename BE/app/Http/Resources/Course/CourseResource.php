<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasAccess = $this->additional['has_access'] ?? false;
        $isEnrolled = $this->additional['is_enrolled'] ?? false;
        $enrollmentStatus = $this->additional['enrollment_status'] ?? null;
        $isInWishlist = $this->additional['is_in_wishlist'] ?? false;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'intro_video_url' => $this->intro_video_url,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'level' => $this->level,
            'language' => $this->language,
            'requirements' => json_decode($this->requirements) ?? $this->requirements,
            'outcomes' => json_decode($this->outcomes) ?? $this->outcomes,
            'is_featured' => (bool) $this->is_featured,
            'total_duration_seconds' => (int) $this->total_duration_seconds,
            'published_at' => optional($this->published_at)->toISOString(),

            // Personalization metadata
            'is_enrolled' => (bool) $isEnrolled,
            'enrollment_status' => $enrollmentStatus,
            'is_in_wishlist' => (bool) $isInWishlist,

            // Instructor relationship
            'instructor' => $this->whenLoaded('instructor', fn () => [
                'id' => $this->instructor->id,
                'full_name' => $this->instructor->full_name,
                'bio' => $this->instructor->instructorProfile?->bio,
                'expertise' => $this->instructor->instructorProfile?->expertise,
                'experience_years' => $this->instructor->instructorProfile?->experience_years,
                'level' => $this->instructor->instructorProfile?->level,
            ]),

            // Outline: sections and lessons
            'sections' => $this->whenLoaded('sections', function () use ($hasAccess) {
                return CourseSectionResource::collection($this->sections)->map(function ($sectionResource) use ($hasAccess) {
                    return $sectionResource->additional(['has_access' => $hasAccess]);
                });
            }),

            // Reviews relationship
            'reviews' => CourseReviewResource::collection($this->whenLoaded('reviews')),

            // FAQs relationship
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
        ];
    }
}
