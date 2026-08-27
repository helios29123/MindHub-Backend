<?php

namespace App\Repositories\Catalog;

use App\Models\Category;

class CategoryRepository
{
    public function getActiveForHome()
    {
        return Category::query()
            ->withCount(['courses' => function ($q) {
                $q->where('courses.status', 'published');
            }])
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(function ($cat) {
                $childCategoryIds = Category::where('parent_id', $cat->id)->pluck('id')->toArray();
                if (! empty($childCategoryIds)) {
                    $allCategoryIds = array_merge([$cat->id], $childCategoryIds);
                    $totalCourses = \App\Models\Course::whereHas('categories', function ($q) use ($allCategoryIds) {
                        $q->whereIn('categories.id', $allCategoryIds);
                    })->where('status', 'published')->distinct('courses.id')->count('courses.id');
                    $cat->courses_count = max($cat->courses_count ?? 0, $totalCourses);
                }
                return $cat;
            });
    }

    public function paginateActive(int $perPage = 50)
    {
        return Category::query()
            ->withCount(['courses' => function ($q) {
                $q->where('courses.status', 'published');
            }])
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}

