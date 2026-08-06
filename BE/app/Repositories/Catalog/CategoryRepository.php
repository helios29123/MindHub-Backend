<?php

namespace App\Repositories\Catalog;

use App\Models\Category;

class CategoryRepository
{
    public function getActiveForHome()
    {
        return Category::query()
            ->withCount(['courses' => function ($q) {
                $q->where('courses.status', 'published')->whereNull('courses.deleted_at');
            }])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get();
    }

    public function paginateActive(int $perPage = 50)
    {
        return Category::query()
            ->withCount(['courses' => function ($q) {
                $q->where('courses.status', 'published')->whereNull('courses.deleted_at');
            }])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
