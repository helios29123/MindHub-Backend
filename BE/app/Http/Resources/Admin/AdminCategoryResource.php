<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'parent_id' => $this->parent_id !== null ? (int) $this->parent_id : null,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'course_count' => (int) ($this->getAttribute('courses_count') ?? 0),
            'created_at' => $this->created_at?->toIsoString(),
            'updated_at' => $this->updated_at?->toIsoString(),
            'parent' => $this->whenLoaded('parent', fn (): ?array => $this->parent ? [
                'id' => (int) $this->parent->id,
                'name' => $this->parent->name,
                'slug' => $this->parent->slug,
                'status' => $this->parent->status,
            ] : null),
            'children' => $this->whenLoaded('children', fn () => $this->children->map(fn ($child): array => [
                'id' => (int) $child->id,
                'name' => $child->name,
                'slug' => $child->slug,
                'status' => $child->status,
                'sort_order' => $child->sort_order,
            ])->values()->all()),
            'statistics' => $this->when(
                $this->getAttribute('category_statistics') !== null,
                fn () => $this->getAttribute('category_statistics')
            ),
            'courses' => $this->whenLoaded('adminCourses', fn () => $this->getRelation('adminCourses')->map(fn ($course): array => [
                'id' => (int) $course->id,
                'title' => $course->title,
                'status' => $course->status,
                'instructor_name' => $course->instructor?->full_name ?? 'Chưa rõ',
                'enrollment_count' => (int) ($course->enrollments_count ?? 0),
                'average_rating' => round((float) ($course->reviews_avg_rating ?? 0), 1),
                'review_count' => (int) ($course->reviews_count ?? 0),
            ])->values()->all()),
        ];
    }
}