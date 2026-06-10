<?php

namespace App\Repositories\Catalog;

use App\Models\Course;

class CatalogCourseRepository
{
    public function search(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $this->publicCourseQuery();

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($courseQuery) use ($search) {
                $courseQuery->where('courses.title', 'like', "%{$search}%")
                    ->orWhere('courses.short_description', 'like', "%{$search}%")
                    ->orWhere('courses.description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];

            $query->whereHas('categories', function ($categoryQuery) use ($categoryId) {
                $categoryQuery->where('categories.id', $categoryId)
                    ->where('categories.status', 'active')
                    ->whereNull('categories.deleted_at');
            });
        }

        if (!empty($filters['level'])) {
            $query->where('courses.level', $filters['level']);
        }

        if (!empty($filters['language'])) {
            $query->where('courses.language', $filters['language']);
        }

        if (isset($filters['min_price'])) {
            $query->whereRaw('COALESCE(courses.sale_price, courses.price) >= ?', [
                $filters['min_price'],
            ]);
        }

        if (isset($filters['max_price'])) {
            $query->whereRaw('COALESCE(courses.sale_price, courses.price) <= ?', [
                $filters['max_price'],
            ]);
        }

        $this->applySort($query, $filters['sort'] ?? null);

        return $query->paginate($perPage);
    }

    public function featured(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $this->publicCourseQuery()
            ->where('courses.is_featured', true);

        $query->orderByDesc('enrollments_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('courses.published_at');

        return $query->paginate($perPage);
    }

    public function latest(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $this->publicCourseQuery()
            ->orderByDesc('courses.published_at')
            ->orderByDesc('courses.id');

        return $query->paginate($perPage);
    }

    private function publicCourseQuery()
    {
        return Course::query()
            ->with([
                'instructor:id,full_name',
                'categories:id,parent_id,name,slug,description,sort_order',
            ])
            ->where('courses.status', 'published')
            ->whereNull('courses.deleted_at')
            ->select('courses.*')

            /**
             * Tính điểm đánh giá trung bình.
             *
             * DB của bạn:
             * courses.id -> orders.course_id
             * orders.id -> course_reviews.order_id
             *
             * Không join trực tiếp course_reviews.course_id vì bảng course_reviews không có course_id.
             */
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                    ->whereColumn('orders.course_id', 'courses.id')
                    ->whereNull('course_reviews.deleted_at')
                    ->selectRaw('COALESCE(AVG(course_reviews.rating), 0)');
            }, 'average_rating')

            /**
             * Đếm số lượng review.
             */
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                    ->whereColumn('orders.course_id', 'courses.id')
                    ->whereNull('course_reviews.deleted_at')
                    ->selectRaw('COUNT(course_reviews.id)');
            }, 'reviews_count')

            /**
             * Đếm số lượt học viên đã enroll.
             */
            ->selectSub(function ($query) {
                $query->from('enrollments')
                    ->whereColumn('enrollments.course_id', 'courses.id')
                    ->whereIn('enrollments.status', ['active', 'completed'])
                    ->selectRaw('COUNT(enrollments.id)');
            }, 'enrollments_count');
    }

    private function applySort($query, ?string $sort): void
    {
        match ($sort) {
            'latest' => $query
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'price_asc' => $query
                ->orderByRaw('COALESCE(courses.sale_price, courses.price) ASC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'price_desc' => $query
                ->orderByRaw('COALESCE(courses.sale_price, courses.price) DESC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'rating_desc' => $query
                ->orderByRaw('average_rating DESC')
                ->orderByRaw('reviews_count DESC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'best_selling' => $query
                ->orderByRaw('enrollments_count DESC')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            'featured' => $query
                ->orderByDesc('courses.is_featured')
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),

            default => $query
                ->orderByDesc('courses.published_at')
                ->orderByDesc('courses.id'),
        };
    }
}
