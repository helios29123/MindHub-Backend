<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('banners')->insert([
            [
                'title' => 'Trang chủ - Hero Banner',
                'image_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788251125/mindhub/banners/hero_banner.png',
                'image_public_id' => 'mindhub/banners/hero_banner',
                'target_url' => '/courses',
                'position' => 'home_hero',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Khóa học - Hero Banner',
                'image_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788251128/mindhub/banners/courses_hero.jpg',
                'image_public_id' => 'mindhub/banners/courses_hero',
                'target_url' => null,
                'position' => 'courses_hero',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'FAQ - Hero Banner',
                'image_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788251134/mindhub/banners/faq_hero.jpg',
                'image_public_id' => 'mindhub/banners/faq_hero',
                'target_url' => null,
                'position' => 'faq_hero',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
