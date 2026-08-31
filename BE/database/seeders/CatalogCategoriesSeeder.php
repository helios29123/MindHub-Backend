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
            'name' => 'Lập trình Web & Phần mềm',
            'slug' => 'cat-category-active-programming',
            'description' => 'Khóa học lập trình Fullstack, Frontend, Backend với các công nghệ hiện đại.',
            'sort_order' => 1,
            'status' => 'active',
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'Thiết kế UI/UX & Đồ họa',
            'slug' => 'cat-category-active-design',
            'description' => 'Thiết kế giao diện người dùng, trải nghiệm người dùng với Figma và Design System.',
            'sort_order' => 2,
            'status' => 'active',
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'Digital Marketing & Kinh doanh',
            'slug' => 'cat-category-active-marketing',
            'description' => 'Chiến lược tiếp thị số, SEO, quảng cáo đa kênh và phát triển thương hiệu.',
            'sort_order' => 3,
            'status' => 'active',
        ], $now);

        $programmingId = (int) DB::table('categories')
            ->where('slug', 'cat-category-active-programming')
            ->value('id');

        $this->upsert([
            'parent_id' => $programmingId,
            'name' => 'Khóa học Laravel Framework',
            'slug' => 'cat-category-active-laravel-child',
            'description' => 'Xây dựng ứng dụng Restful API, kiến trúc đa tầng với Laravel Framework.',
            'sort_order' => 1,
            'status' => 'active',
        ], $now);

        $this->upsert([
            'parent_id' => $programmingId,
            'name' => 'Lập trình PHP & Cơ sở dữ liệu',
            'slug' => 'cat-category-active-php-child',
            'description' => 'Nền tảng ngôn ngữ PHP, lập trình hướng đối tượng OOP và tối ưu hóa MySQL.',
            'sort_order' => 2,
            'status' => 'active',
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'Danh mục nội bộ',
            'slug' => 'cat-category-inactive-hidden',
            'description' => 'Danh mục thử nghiệm nội bộ, không public.',
            'sort_order' => 4,
            'status' => 'inactive',
        ], $now);

        $this->upsert([
            'parent_id' => null,
            'name' => 'Danh mục lưu trữ',
            'slug' => 'cat-category-active-soft-deleted',
            'description' => 'Danh mục đã lưu trữ.',
            'sort_order' => 5,
            'status' => 'active',
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
