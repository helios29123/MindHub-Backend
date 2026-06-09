<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CatalogCatDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogUsersSeeder::class,
            CatalogCategoriesSeeder::class,
            CatalogInstructorProfilesSeeder::class,
            CatalogCoursesSeeder::class,
            CatalogCourseCategoriesSeeder::class,
            CatalogCouponsSeeder::class,
            CatalogOrdersSeeder::class,
            CatalogEnrollmentsSeeder::class,
            CatalogCourseReviewsSeeder::class,
            CatalogBannersSeeder::class,
        ]);
    }
}
