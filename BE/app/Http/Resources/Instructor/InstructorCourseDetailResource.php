<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorCourseDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = data_get($this->resource, 'status');

        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'intro_video_url' => $this->intro_video_url,
            'price' => $this->price !== null ? (float) $this->price : null,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'discount_percent' => $this->discount_percent !== null ? (int) $this->discount_percent : null,
            'has_discount' => $this->discount_percent !== null && (int) $this->discount_percent > 0,
            'course_level' => $this->course_level,
            'language' => $this->language,
            'requirements' => $this->requirements,
            'outcomes' => $this->outcomes,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'admin_reject_reason' => $this->admin_reject_reason,
            'categories' => $this->categories->map(fn ($category): array => [
                'id' => (int) $category->id,
                'name' => $category->name,
            ])->values()->all(),
            'summary' => [
                'section_count' => (int) data_get($this->resource, 'section_count', 0),
                'lesson_count' => (int) data_get($this->resource, 'lesson_count', 0),
                'asset_count' => (int) data_get($this->resource, 'asset_count', 0),
                'preview_lesson_count' => (int) data_get($this->resource, 'preview_lesson_count', 0),
                'enrollment_count' => (int) data_get($this->resource, 'enrollment_count', 0),
                'revenue_amount' => (float) data_get($this->resource, 'revenue_amount', 0),
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'draft' => 'Đang hoàn thiện',
            'pending_review' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Bị từ chối',
            'published' => 'Đang công khai',
            'hidden' => 'Đang ẩn',
            default => 'Không xác định',
        };
    }
}