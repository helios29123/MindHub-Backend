<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedInstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'bio' => $this->whenLoaded('instructorProfile', fn () => $this->instructorProfile?->bio),
            'expertise' => $this->whenLoaded('instructorProfile', fn () => $this->instructorProfile?->expertise),
            'experience_years' => $this->whenLoaded('instructorProfile', fn () => $this->instructorProfile?->experience_years),
            'level' => $this->whenLoaded('instructorProfile', fn () => $this->instructorProfile?->level),
            'published_courses_count' => (int) ($this->published_courses_count ?? 0),
            'total_enrollments_count' => (int) ($this->total_enrollments_count ?? 0),
            'average_rating' => $this->average_rating !== null ? round((float) $this->average_rating, 1) : null,
        ];
    }
}
