<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rawMeta = $this->locked_reason;
        $meta = ($rawMeta && str_starts_with(trim((string)$rawMeta), '{')) ? json_decode((string)$rawMeta, true) : [];
        $privacy = data_get($meta, 'privacy_settings', [
            'profile_visibility' => 'public',
            'show_email' => false,
            'show_phone' => false,
            'show_social_links' => true,
        ]);

        $currentUser = $request->bearerToken() ? $request->user() : null;
        $isOwner = $currentUser && (int)$currentUser->id === (int)$this->id;

        $showEmail = $isOwner || (bool)($privacy['show_email'] ?? false);
        $showPhone = $isOwner || (bool)($privacy['show_phone'] ?? false);
        $showSocial = $isOwner || (bool)($privacy['show_social_links'] ?? true);
        $avatarUrl = data_get($meta, 'avatar_url');
        $socialLinks = $showSocial ? data_get($meta, 'social_links', null) : null;

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $showEmail ? $this->email : null,
            'phone' => $showPhone ? $this->phone : null,
            'avatar_url' => $avatarUrl,
            'social_links' => $socialLinks,
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
