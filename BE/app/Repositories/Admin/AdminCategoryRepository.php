<?php

namespace App\Repositories\Admin;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AdminCategoryRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Category::query()->with('parent')->withCount('courses');
        $status = $filters['status'] ?? null;

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'all_with_deleted') {
            $query->withTrashed();
        } elseif (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (($filters['type'] ?? null) === 'root') {
            $query->whereNull('parent_id');
        } elseif (($filters['type'] ?? null) === 'child') {
            $query->whereNotNull('parent_id');
        }

        if (array_key_exists('parent_id', $filters)) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (in_array((string) ($filters['empty'] ?? ''), ['true', '1'], true)) {
            $query->doesntHave('courses');
        }

        $this->applySort($query, $filters);

        return $query->paginate(min((int) ($filters['per_page'] ?? 20), 100))
            ->appends($filters);
    }

    public function summary(): array
    {
        $categories = Category::query()->withCount('courses')->get();

        return [
            'total_categories' => $categories->count(),
            'active_categories' => $categories->where('status', 'active')->count(),
            'inactive_categories' => $categories->where('status', 'inactive')->count(),
            'root_categories' => $categories->whereNull('parent_id')->count(),
            'empty_categories' => $categories->where('courses_count', 0)->count(),
        ];
    }

    public function find(int $id): ?Category
    {
        return Category::query()->find($id);
    }

    public function findWithRelations(int $id): ?Category
    {
        $category = Category::query()
            ->with(['parent', 'children' => fn($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->withCount('courses')
            ->find($id);

        if (!$category) {
            return null;
        }

        $categoryIds = collect([$category->id])
            ->merge($category->children->pluck('id'))
            ->map(fn($value): int => (int) $value)
            ->all();

        $courses = Course::query()
            ->whereHas('categories', fn(Builder $query) => $query->whereIn('categories.id', $categoryIds))
            ->with('instructor')
            ->orderByDesc('id')
            ->get();

        $courseIds = $courses->pluck('id')->map(fn($value): int => (int) $value)->all();
        $enrollmentCounts = empty($courseIds)
            ? collect()
            : DB::table('enrollments')->whereIn('course_id', $courseIds)
            ->selectRaw('course_id, COUNT(*) as aggregate')
            ->groupBy('course_id')
            ->pluck('aggregate', 'course_id');

        $reviewQuery = DB::table('course_reviews')->whereIn('course_id', $courseIds);
        if (Schema::hasColumn('course_reviews', 'deleted_at')) {
            $reviewQuery;
        }
        $reviewAggregates = empty($courseIds)
            ? collect()
            : DB::table('course_reviews as reviews')
            ->join('orders as orders', 'orders.id', '=', 'reviews.order_id')
            ->whereIn('orders.course_id', $courseIds)
            ->when(
                Schema::hasColumn('course_reviews', 'deleted_at'),
                fn($query) => $query
            )
            ->selectRaw(
                'orders.course_id as course_id,
             COUNT(reviews.id) as review_count,
             AVG(reviews.rating) as average_rating'
            )
            ->groupBy('orders.course_id')
            ->get()
            ->keyBy('course_id');

        $courses->each(function (Course $course) use ($enrollmentCounts, $reviewAggregates): void {
            $review = $reviewAggregates->get($course->id);
            $course->setAttribute('enrollments_count', (int) ($enrollmentCounts->get($course->id) ?? 0));
            $course->setAttribute('reviews_count', (int) ($review->review_count ?? 0));
            $course->setAttribute('reviews_avg_rating', (float) ($review->average_rating ?? 0));
        });

        $reviewCount = (int) $courses->sum('reviews_count');
        $weightedRatingTotal = $courses->sum(
            fn($course): float => (float) ($course->reviews_avg_rating ?? 0) * (int) ($course->reviews_count ?? 0)
        );

        $category->setRelation('adminCourses', $courses);
        $category->setAttribute('category_statistics', [
            'total' => $courses->count(),
            'published' => $courses->where('status', 'published')->count(),
            'pending' => $courses->where('status', 'pending_review')->count(),
            'draft' => $courses->where('status', 'draft')->count(),
            'enrollments' => (int) $courses->sum('enrollments_count'),
            'reviews' => $reviewCount,
            'rating' => $reviewCount > 0 ? round($weightedRatingTotal / $reviewCount, 1) : 'Chưa có dữ liệu',
        ]);

        return $category;
    }

    public function findWithTrashed(int $id): ?Category
    {
        return Category::withTrashed()->find($id);
    }

    public function findOnlyTrashed(int $id): ?Category
    {
        return Category::onlyTrashed()->find($id);
    }

    public function findActiveRoot(int $id): ?Category
    {
        return Category::query()->whereKey($id)->whereNull('parent_id')->first();
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->refresh();
    }

    public function hasChildren(Category $category): bool
    {
        return $category->children()->exists();
    }

    public function hasCourses(Category $category): bool
    {
        return $category->courses()->exists();
    }

    public function nextSortOrder(?int $parentId): string
    {
        $max = Category::query()->where('parent_id', $parentId)->max('sort_order');
        if (!$max) {
            return 'a';
        }
        
        $len = strlen($max);
        $lastChar = $max[$len - 1];
        
        if ($lastChar < 'z') {
            $max[$len - 1] = chr(ord($lastChar) + 1);
            return $max;
        } else {
            return $max . 'n';
        }
    }

    public function allParentMap(): Collection
    {
        return Category::query()->pluck('parent_id', 'id');
    }

    private function applySort(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'sort_order_asc';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        $presetMap = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'sort_order_asc' => ['sort_order', 'asc'],
            'sort_order_desc' => ['sort_order', 'desc'],
            'courses_desc' => ['courses_count', 'desc'],
        ];

        if (isset($presetMap[$sortBy])) {
            [$column, $direction] = $presetMap[$sortBy];
        } else {
            $allowedColumns = ['id', 'name', 'slug', 'status', 'sort_order', 'created_at', 'updated_at'];
            $column = in_array($sortBy, $allowedColumns, true) ? $sortBy : 'sort_order';
            $direction = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'asc';
        }

        $query->orderBy($column, $direction)->orderBy('id', 'desc');
    }
}
