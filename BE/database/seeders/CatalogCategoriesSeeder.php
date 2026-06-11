<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $this->upsert([
            'parent_id' => null,
            'name' => 'CATEGORY_ACTIVE Programming',
            'slug' => 'cat-category-active-programming',
            'description' => 'Danh mục lập trình active.',
            'sort_order' => 1,
            'status' => 'active',
            'deleted_at' => null,
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'CATEGORY_ACTIVE Design',
            'slug' => 'cat-category-active-design',
            'description' => 'Danh mục design active.',
            'sort_order' => 2,
            'status' => 'active',
            'deleted_at' => null,
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'CATEGORY_ACTIVE Marketing',
            'slug' => 'cat-category-active-marketing',
            'description' => 'Danh mục marketing active.',
            'sort_order' => 3,
            'status' => 'active',
            'deleted_at' => null,
        ], $now);

        $programmingId = (int) DB::table('categories')
            ->where('slug', 'cat-category-active-programming')
            ->value('id');

        $this->upsert([
            'parent_id' => $programmingId,
            'name' => 'CATEGORY_ACTIVE Laravel Child',
            'slug' => 'cat-category-active-laravel-child',
            'description' => 'Danh mục con Laravel active.',
            'sort_order' => 4,
            'status' => 'active',
            'deleted_at' => null,
        ], $now);

        $this->upsert([
            'parent_id' => $programmingId,
            'name' => 'CATEGORY_ACTIVE PHP Child',
            'slug' => 'cat-category-active-php-child',
            'description' => 'Danh mục con PHP active.',
            'sort_order' => 5,
            'status' => 'active',
            'deleted_at' => null,
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'CATEGORY_INACTIVE Hidden',
            'slug' => 'cat-category-inactive-hidden',
            'description' => 'Danh mục inactive, không nên public.',
            'sort_order' => 99,
            'status' => 'inactive',
            'deleted_at' => null,
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'CATEGORY_ACTIVE Soft Deleted Not Public',
            'slug' => 'cat-category-active-soft-deleted',
            'description' => 'Danh mục active nhưng soft deleted.',
            'sort_order' => 100,
            'status' => 'active',
            'deleted_at' => $now,
        ], $now);
    }

    private function upsert(array $data, Carbon $now): void
    {
        DB::table('categories')->updateOrInsert(
            ['slug' => $data['slug']],
            array_merge($data, [
                'created_at' => $now,
                'updated_at' => $now,
            ])
        );
    }
}
