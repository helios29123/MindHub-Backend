<?php

namespace App\Repositories\Catalog;

use App\Models\Category;

class CategoryRepository
{
    public function getActiveForHome()
    {
        return Category::query()
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
    }

    public function paginateActive(int $perPage = 10)
    {
        return Category::query()
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
