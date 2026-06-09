<?php

namespace App\Repositories\Catalog;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository
{
    public function paginateActive(int $perPage): LengthAwarePaginator
    {
        return Category::query()
            ->with(['parent:id,name,slug'])
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function activeForHome(int $limit): Collection
    {
        return Category::query()
            ->where('status', 'active')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
