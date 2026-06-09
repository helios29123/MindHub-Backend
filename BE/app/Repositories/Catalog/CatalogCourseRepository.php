<?php

namespace App\Repositories\Catalog;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogCourseRepository
{
    public function paginatePublic(array $filters, int $perPage, ?User $currentUser = null): LengthAwarePaginator
    {
        $query = $this->publicCourseQuery($currentUser);

        $this->applyFilters($query, $filters);
        $this->applySort($query, $filters['sort'] ?? 'newest');

        return $query->paginate($perPage)->withQueryString();
    }

    public function paginateFeatured(int $perPage, ?User $currentUser = null): LengthAwarePaginator
    {
        return $this->publicCourseQuery($currentUser)
            ->where('is_featured', true)
            ->orderByDesc('enrollments_count')
            ->orderByDesc('published_at')
            ->paginate($perPage);
    }

    public function paginateLatest(int $perPage, ?User $currentUser = null): LengthAwarePaginator
    {
        return $this->publicCourseQuery($currentUser)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function featured(int $limit, ?User $currentUser = null): Collection
    {
        return $this->publicCourseQuery($currentUser)
            ->where('is_featured', true)
            ->orderByDesc('enrollments_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function latest(int $limit, ?User $currentUser = null): Collection
    {
        return $this->publicCourseQuery($currentUser)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function suggestions(string $keyword, int $limit): Collection
    {
        $courses = Course::query()
            ->select(['id', 'title', 'slug'])
            ->where('status', 'published')
            ->when($keyword !== '', function (Builder $query) use ($keyword) {
                $query->where('title', 'like', '%' . $keyword . '%');
            })
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Course $course) => [
                'type' => 'course',
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
            ]);

        $remainingLimit = max($limit - $courses->count(), 0);

        if ($remainingLimit === 0) {
            return $courses;
        }

        $categories = \App\Models\Category::query()
            ->select(['id', 'name', 'slug'])
            ->where('status', 'active')
            ->when($keyword !== '', function (Builder $query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->orderBy('sort_order')
            ->limit($remainingLimit)
            ->get()
            ->map(fn ($category) => [
                'type' => 'category',
                'id' => $category->id,
                'title' => $category->name,
                'slug' => $category->slug,
            ]);

        return $courses->concat($categories)->values();
    }

    private function publicCourseQuery(?User $currentUser = null): Builder
    {
        return Course::query()
            ->select('courses.*')
            ->where('courses.status', 'published')
            ->with([
                'instructor:id,full_name,email,role,status',
                'categories' => fn ($query) => $query->where('categories.status', 'active')->select('categories.id', 'categories.name', 'categories.slug'),
            ])
            ->withCount([
                'enrollments',
                'activeEnrollments as learner_enrolled' => function ($query) use ($currentUser) {
                    $query->when($currentUser, fn ($q) => $q->where('user_id', $currentUser->id));
                    $query->when(!$currentUser, fn ($q) => $q->whereRaw('1 = 0'));
                },
            ])
            ->withAvg('reviews', 'rating');
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('short_description', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['category'])) {
            $categorySlug = $filters['category'];
            $query->whereHas('categories', function (Builder $categoryQuery) use ($categorySlug) {
                $categoryQuery->where('categories.slug', $categorySlug)
                    ->where('categories.status', 'active');
            });
        }

        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (!empty($filters['language'])) {
            $query->where('language', $filters['language']);
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'popular' => $query->orderByDesc('enrollments_count')->orderByDesc('published_at'),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('published_at'),
            'rating' => $query->orderByDesc('reviews_avg_rating')->orderByDesc('published_at'),
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) ASC')->orderByDesc('published_at'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) DESC')->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }
}
