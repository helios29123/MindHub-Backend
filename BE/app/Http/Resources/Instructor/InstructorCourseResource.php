<?php

namespace App\Http\Resources\Instructor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InstructorCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = data_get($this->resource, 'status');

        return [
            'id' => (int) data_get($this->resource, 'id'),
            'title' => data_get($this->resource, 'title'),
            'slug' => data_get($this->resource, 'slug'),
            'short_description' => data_get($this->resource, 'short_description'),
            'thumbnail_url' => data_get($this->resource, 'thumbnail_url'),
            'intro_video_url' => data_get($this->resource, 'intro_video_url'),
            'price' => $this->moneyValue(data_get($this->resource, 'price')),
            'sale_price' => $this->moneyValue(data_get($this->resource, 'sale_price')),
            'discount_percent' => data_get($this->resource, 'discount_percent') !== null ? (int) data_get($this->resource, 'discount_percent') : null,
            'has_discount' => data_get($this->resource, 'discount_percent') !== null && (int) data_get($this->resource, 'discount_percent') > 0,
            'level' => data_get($this->resource, 'level'),
            'language' => data_get($this->resource, 'language'),
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'categories' => $this->categories(),
            'enrollment_count' => (int) data_get($this->resource, 'enrollment_count', 0),
            'enrollments_count' => (int) data_get($this->resource, 'enrollment_count', 0),
            'revenue' => (string) number_format((float) data_get($this->resource, 'revenue', 0), 2, '.', ''),
            'rating' => (float) data_get($this->resource, 'rating', 0),
            'review_count' => (int) data_get($this->resource, 'review_count', 0),
            'reviews_count' => (int) data_get($this->resource, 'review_count', 0),
            'created_at' => $this->dateValue(data_get($this->resource, 'created_at')),
            'updated_at' => $this->dateValue(data_get($this->resource, 'updated_at')),
        ];
    }

    private function categories(): array
    {
        $categories = data_get($this->resource, 'categories');

        if (!$categories) {
            return [];
        }

        return collect($categories)->map(fn ($category): array => [
            'id' => (int) data_get($category, 'id'),
            'name' => data_get($category, 'name'),
        ])->values()->all();
    }

    private function moneyValue(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->toDateTimeString();
        }

        return (string) $value;
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