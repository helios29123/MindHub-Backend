<?php

namespace App\Repositories\Catalog;

use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CatalogCourseRepository
{
    public function search(array $filters)
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $query = $this->publicCourseQuery();

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $courseQuery) use ($search) {
                $courseQuery->where('courses.title', 'like', "%{$search}%")
                    ->orWhere('courses.short_description', 'like', "%{$search}%")
                    ->orWhere('courses.description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];

            $query->whereHas('categories', function (Builder $categoryQuery) use ($categoryId) {
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
            ->where('courses.is_featured', true)
            ->orderByDesc('enrollments_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('courses.published_at')
            ->orderByDesc('courses.id');

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

    public function suggestions(string $keyword, int $limit = 10): Collection
    {
        $keyword = trim($keyword);
        $limit = min(max($limit, 1), 20);

        if ($keyword === '') {
            return collect();
        }

        $courseLimit = (int) ceil($limit / 2);
        $categoryLimit = $limit - $courseLimit;

        $courses = DB::table('courses')
            ->select([
                'id',
                DB::raw('title as text'),
                'slug',
                DB::raw("'course' as type"),
            ])
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('short_description', 'like', "%{$keyword}%");
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit($courseLimit)
            ->get();

        $categories = DB::table('categories')
            ->select([
                'id',
                DB::raw('name as text'),
                'slug',
                DB::raw("'category' as type"),
            ])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%");
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($categoryLimit)
            ->get();

        return $courses
            ->merge($categories)
            ->take($limit)
            ->values();
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
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                    ->whereColumn('orders.course_id', 'courses.id')
                    ->whereNull('course_reviews.deleted_at')
                    ->selectRaw('COALESCE(AVG(course_reviews.rating), 0)');
            }, 'average_rating')
            ->selectSub(function ($query) {
                $query->from('orders')
                    ->join('course_reviews', 'course_reviews.order_id', '=', 'orders.id')
                    ->whereColumn('orders.course_id', 'courses.id')
                    ->whereNull('course_reviews.deleted_at')
                    ->selectRaw('COUNT(course_reviews.id)');
            }, 'reviews_count')
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
