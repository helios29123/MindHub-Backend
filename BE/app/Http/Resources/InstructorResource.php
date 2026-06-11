<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'bio' => $this->instructorProfile?->bio,
            'expertise' => $this->instructorProfile?->expertise,
            'experience_years' => $this->instructorProfile !== null ? (int) $this->instructorProfile->experience_years : null,
            'level' => $this->instructorProfile?->level,
            'published_courses_count' => (int) ($this->published_courses_count ?? 0),
            'total_enrollments_count' => (int) ($this->total_enrollments_count ?? 0),
            'average_rating' => $this->average_rating !== null ? round((float) $this->average_rating, 1) : null,
            'courses' => $this->whenLoaded('publishedCourses', function () {
                return CourseResource::collection($this->publishedCourses)->map(function ($courseRes) {
                    return $courseRes->additional([
                        'is_enrolled' => $courseRes->resource->is_enrolled ?? false,
                        'enrollment_status' => $courseRes->resource->enrollment_status ?? null,
                        'is_in_wishlist' => $courseRes->resource->is_in_wishlist ?? false,
                        'has_access' => $courseRes->resource->has_access ?? false,
                    ]);
                });
            }),
        ];
    }
}
