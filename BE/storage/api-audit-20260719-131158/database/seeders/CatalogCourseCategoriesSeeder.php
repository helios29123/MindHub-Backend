<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogCourseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $courseLaravelId = $this->courseId('cat-course-published-laravel-api-featured');
        $coursePhpId = $this->courseId('cat-course-published-php-mysql-best-selling');
        $courseReactId = $this->courseId('cat-course-published-react-latest');
        $courseFreeUiId = $this->courseId('cat-course-published-free-ui-design');
        $courseDraftId = $this->courseId('cat-course-draft-not-public');
        $courseHiddenId = $this->courseId('cat-course-hidden-not-public');

        $categoryProgrammingId = $this->categoryId('cat-category-active-programming');
        $categoryDesignId = $this->categoryId('cat-category-active-design');
        $categoryLaravelId = $this->categoryId('cat-category-active-laravel-child');
        $categoryPhpId = $this->categoryId('cat-category-active-php-child');
        $categoryInactiveId = $this->categoryId('cat-category-inactive-hidden');

        $items = [
            [$courseLaravelId, $categoryProgrammingId],
            [$courseLaravelId, $categoryLaravelId],

            [$coursePhpId, $categoryProgrammingId],
            [$coursePhpId, $categoryPhpId],

            [$courseReactId, $categoryProgrammingId],
            [$courseFreeUiId, $categoryDesignId],

            [$courseDraftId, $categoryInactiveId],
            [$courseHiddenId, $categoryInactiveId],
        ];

        foreach ($items as [$courseId, $categoryId]) {
            if (!$courseId || !$categoryId) {
                continue;
            }

            DB::table('course_categories')->insertOrIgnore([
                'course_id' => $courseId,
                'category_id' => $categoryId,
                'created_at' => $now,
            ]);
        }
    }

    private function courseId(string $slug): int
    {
        return (int) DB::table('courses')->where('slug', $slug)->value('id');
    }

    private function categoryId(string $slug): int
    {
        return (int) DB::table('categories')->where('slug', $slug)->value('id');
    }
}
