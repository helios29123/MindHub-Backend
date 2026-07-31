<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CategoryScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $roots = [
                ['Lập trình', 'lap-trinh', 'active'],
                ['Thiết kế', 'thiet-ke', 'active'],
                ['Marketing', 'marketing', 'inactive'],
                ['Kinh doanh', 'kinh-doanh', 'active'],
                ['Ngoại ngữ', 'ngoai-ngu', 'inactive'],
                ['Kỹ năng mềm', 'ky-nang-mem', 'active'],
                ['Dữ liệu', 'du-lieu', 'active'],
                ['Danh mục có khóa học', 'co-khoa-hoc', 'active'],
            ];

            $rootIds = [];

            foreach ($roots as $index => [$label, $slugPart, $status]) {
                $slug = 'test-category-root-' . $slugPart;
                DB::table('categories')->updateOrInsert(
                    ['slug' => $slug],
                    [
                        'parent_id' => null,
                        'name' => 'TEST CATEGORY - ' . $label,
                        'description' => 'Dữ liệu kiểm thử trang quản lý danh mục. Có thể chạy lại seeder an toàn.',
                        'sort_order' => $index + 1,
                        'status' => $status,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]
                );

                $rootIds[$slugPart] = (int) DB::table('categories')->where('slug', $slug)->value('id');
            }

            $children = [
                ['lap-trinh', 'PHP Laravel', 'php-laravel', 'active'],
                ['lap-trinh', 'React TypeScript', 'react-typescript', 'active'],
                ['lap-trinh', 'Node.js', 'nodejs', 'inactive'],
                ['thiet-ke', 'UI UX', 'ui-ux', 'active'],
                ['thiet-ke', 'Figma', 'figma', 'active'],
                ['thiet-ke', 'Thiết kế đồ họa', 'do-hoa', 'inactive'],
                ['marketing', 'SEO', 'seo', 'active'],
                ['marketing', 'Content Marketing', 'content-marketing', 'inactive'],
                ['marketing', 'Quảng cáo', 'quang-cao', 'active'],
                ['kinh-doanh', 'Bán hàng', 'ban-hang', 'active'],
                ['kinh-doanh', 'Khởi nghiệp', 'khoi-nghiep', 'active'],
                ['kinh-doanh', 'Quản trị', 'quan-tri', 'inactive'],
                ['ngoai-ngu', 'Tiếng Anh', 'tieng-anh', 'active'],
                ['ngoai-ngu', 'Tiếng Nhật', 'tieng-nhat', 'inactive'],
                ['ngoai-ngu', 'Tiếng Hàn', 'tieng-han', 'active'],
                ['ky-nang-mem', 'Giao tiếp', 'giao-tiep', 'active'],
                ['ky-nang-mem', 'Làm việc nhóm', 'lam-viec-nhom', 'active'],
                ['ky-nang-mem', 'Quản lý thời gian', 'quan-ly-thoi-gian', 'inactive'],
                ['du-lieu', 'SQL', 'sql', 'active'],
                ['du-lieu', 'Phân tích dữ liệu', 'phan-tich-du-lieu', 'active'],
                ['du-lieu', 'Machine Learning', 'machine-learning', 'inactive'],
                ['co-khoa-hoc', 'Danh mục con có khóa học', 'child-co-khoa-hoc', 'active'],
            ];

            $childOrder = [];
            foreach ($children as [$parentKey, $label, $slugPart, $status]) {
                $childOrder[$parentKey] = ($childOrder[$parentKey] ?? 0) + 1;
                DB::table('categories')->updateOrInsert(
                    ['slug' => 'test-category-child-' . $slugPart],
                    [
                        'parent_id' => $rootIds[$parentKey],
                        'name' => 'TEST CATEGORY - ' . $label,
                        'description' => 'Danh mục con phục vụ filter, phân trang và kéo thả.',
                        'sort_order' => $childOrder[$parentKey],
                        'status' => $status,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]
                );
            }

            foreach ([1, 2, 3, 4] as $index) {
                DB::table('categories')->updateOrInsert(
                    ['slug' => 'test-category-deleted-' . $index],
                    [
                        'parent_id' => null,
                        'name' => 'TEST CATEGORY - Đã xóa ' . $index,
                        'description' => 'Dữ liệu kiểm thử bộ lọc danh mục đã xóa và khôi phục.',
                        'sort_order' => 90 + $index,
                        'status' => $index % 2 === 0 ? 'inactive' : 'active',
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => $now,
                    ]
                );
            }

            if (Schema::hasTable('courses') && Schema::hasTable('course_categories')) {
                $courseId = DB::table('courses')->whereNull('deleted_at')->orderBy('id')->value('id');
                if ($courseId !== null) {
                    DB::table('course_categories')->updateOrInsert(
                        ['course_id' => $courseId, 'category_id' => $rootIds['co-khoa-hoc']],
                        ['created_at' => $now]
                    );
                }
            }
        });

        $this->command?->info('Đã seed 34 danh mục TEST CATEGORY; không xóa dữ liệu cũ.');
    }
}