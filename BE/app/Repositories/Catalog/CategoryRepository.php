<?php

namespace App\Repositories\Catalog;

use App\Models\Category;

class CategoryRepository
{
    public function getActiveForHome()
    {
        $categories = Category::query()
            ->withCount(['courses' => function ($q) {
                $q->where('courses.status', 'published');
            }])
            ->with(['children:id,parent_id'])
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $childToParent = [];
        foreach ($categories as $cat) {
            if ($cat->children && $cat->children->isNotEmpty()) {
                foreach ($cat->children as $child) {
                    $childToParent[$child->id] = $cat->id;
                }
            }
        }

        if (!empty($childToParent)) {
            $childCourseCounts = \Illuminate\Support\Facades\DB::table('course_categories')
                ->join('courses', 'courses.id', '=', 'course_categories.course_id')
                ->where('courses.status', 'published')
                ->whereIn('course_categories.category_id', array_keys($childToParent))
                ->select('course_categories.category_id', 'course_categories.course_id')
                ->distinct()
                ->get();

            $extraCounts = [];
            foreach ($childCourseCounts as $row) {
                $parentId = $childToParent[$row->category_id] ?? null;
                if ($parentId) {
                    $extraCounts[$parentId] = ($extraCounts[$parentId] ?? 0) + 1;
                }
            }

            foreach ($categories as $cat) {
                if (isset($extraCounts[$cat->id])) {
                    $cat->courses_count = max((int) ($cat->courses_count ?? 0), (int) ($cat->courses_count ?? 0) + $extraCounts[$cat->id]);
                }
            }
        }

        return $categories;
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

