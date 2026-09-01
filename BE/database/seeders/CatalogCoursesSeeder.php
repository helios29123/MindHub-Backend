<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogCoursesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $instructor01Id = (int) DB::table('users')->where('email', 'cat.instructor01@example.com')->value('id');
        $instructor02Id = (int) DB::table('users')->where('email', 'cat.instructor02@example.com')->value('id');

        $courses = [
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_PUBLISHED Laravel API Featured',
                'slug' => 'cat-course-published-laravel-api-featured',
                'short_description' => 'Khóa Laravel API public, featured.',
                'description' => 'Học xây dựng REST API với Laravel, MySQL, Resource, Service, Repository.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202401/courses_thumbnail/l5t4rsgcb6gqgf8hvvs8.jpg',
                'intro_video_url' => 'https://example.com/videos/cat-laravel-api.mp4',
                'price' => 1200000,
                
                'course_level' => 'beginner',
                'language' => 'vi',
                'requirements' => json_encode(['']),
                'outcomes' => json_encode(['']),
                'status' => 'published',
                'is_featured' => true,
                
                'published_at' => $now->copy()->subDays(10),
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_PUBLISHED PHP MySQL Best Selling',
                'slug' => 'cat-course-published-php-mysql-best-selling',
                'short_description' => 'Khóa PHP MySQL nhiều enrollment nhất.',
                'description' => 'Học PHP, MySQL, database design.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202405/courses_thumbnail/smc8ke0qldnezy1ete1u.jpg',
                'intro_video_url' => 'https://example.com/videos/cat-php-mysql.mp4',
                'price' => 900000,
                
                'course_level' => 'beginner',
                'language' => 'vi',
                'requirements' => json_encode(['']),
                'outcomes' => json_encode(['']),
                'status' => 'published',
                'is_featured' => false,
                
                'published_at' => $now->copy()->subDays(20),
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor02Id,
                'title' => 'COURSE_PUBLISHED React Latest',
                'slug' => 'cat-course-published-react-latest',
                'short_description' => 'Khóa React mới nhất.',
                'description' => 'Học React, component, state, props.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202408/courses_thumbnail/udqewhfrqn5cjnujcova.jpg',
                'intro_video_url' => 'https://example.com/videos/cat-react-latest.mp4',
                'price' => 1500000,
                
                'course_level' => 'intermediate',
                'language' => 'vi',
                'requirements' => json_encode(['']),
                'outcomes' => json_encode(['']),
                'status' => 'published',
                'is_featured' => true,
                
                'published_at' => $now->copy()->subDay(),
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor02Id,
                'title' => 'COURSE_PUBLISHED Free UI Design',
                'slug' => 'cat-course-published-free-ui-design',
                'short_description' => 'Khóa miễn phí để test sort price_asc.',
                'description' => 'Học UI design căn bản.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202412/courses_thumbnail/wqw9amypfhttkgrpvidv.jpg',
                'intro_video_url' => 'https://example.com/videos/cat-free-ui-design.mp4',
                'price' => 0,
                
                'course_level' => 'all_levels',
                'language' => 'vi',
                'requirements' => json_encode(['']),
                'outcomes' => json_encode(['']),
                'status' => 'published',
                'is_featured' => false,
                
                'published_at' => $now->copy()->subDays(3),
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_DRAFT Not Public',
                'slug' => 'cat-course-draft-not-public',
                'short_description' => 'Course draft, không được public.',
                'description' => 'Dữ liệu test không public.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202389/courses_thumbnail/l5uo0m3ic682p3gl26jg.jpg',
                'intro_video_url' => null,
                'price' => 100000,
                
                'course_level' => 'beginner',
                'language' => 'vi',
                'requirements' => null,
                'outcomes' => null,
                'status' => 'draft',
                'is_featured' => false,
                
                'published_at' => null,
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_HIDDEN Not Public',
                'slug' => 'cat-course-hidden-not-public',
                'short_description' => 'Course hidden, không được public.',
                'description' => 'Dữ liệu test hidden.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202391/courses_thumbnail/qlfyydulmdlvj8l1pg0v.jpg',
                'intro_video_url' => null,
                'price' => 100000,
                
                'course_level' => 'beginner',
                'language' => 'vi',
                'requirements' => null,
                'outcomes' => null,
                'status' => 'hidden',
                'is_featured' => true,
                
                'published_at' => $now->copy()->subDays(2),
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_PENDING_REVIEW Not Public',
                'slug' => 'cat-course-pending-review-not-public',
                'short_description' => 'Course pending_review, không được public.',
                'description' => 'Dữ liệu test pending review.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202393/courses_thumbnail/ld7or31uun6mvbzxd75s.jpg',
                'intro_video_url' => null,
                'price' => 200000,
                
                'course_level' => 'intermediate',
                'language' => 'vi',
                'requirements' => null,
                'outcomes' => null,
                'status' => 'pending_review',
                'is_featured' => false,
                
                'published_at' => null,
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_REJECTED Not Public',
                'slug' => 'cat-course-rejected-not-public',
                'short_description' => 'Course rejected, không được public.',
                'description' => 'Dữ liệu test rejected.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202395/courses_thumbnail/jz1s8pzosx1vzlbbxqfd.jpg',
                'intro_video_url' => null,
                'price' => 300000,
                
                'course_level' => 'advanced',
                'language' => 'vi',
                'requirements' => null,
                'outcomes' => null,
                'status' => 'rejected',
                'is_featured' => false,
                
                'published_at' => null,
                'admin_reject_reason' => 'Nội dung chưa đạt yêu cầu.',
                
            ],
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_APPROVED Not Public',
                'slug' => 'cat-course-approved-not-public',
                'short_description' => 'Course approved nhưng chưa published.',
                'description' => 'Dữ liệu test approved.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202397/courses_thumbnail/zrfvoo3bbt4brvba8wdg.jpg',
                'intro_video_url' => null,
                'price' => 400000,
                
                'course_level' => 'beginner',
                'language' => 'vi',
                'requirements' => null,
                'outcomes' => null,
                'status' => 'approved',
                'is_featured' => false,
                
                'published_at' => null,
                'admin_reject_reason' => null,
                
            ],
            [
                'instructor_id' => $instructor01Id,
                'title' => 'COURSE_PUBLISHED Soft Deleted Not Public',
                'slug' => 'cat-course-published-soft-deleted-not-public',
                'short_description' => 'Course published nhưng soft deleted.',
                'description' => 'Dữ liệu test soft delete course.',
                'thumbnail_url' => 'https://res.cloudinary.com/hcoy6dgr/image/upload/v1788202399/courses_thumbnail/zhxwppkb6xhxgtwhd0jf.jpg',
                'intro_video_url' => null,
                'price' => 500000,
                
                'course_level' => 'beginner',
                'language' => 'vi',
                'requirements' => null,
                'outcomes' => null,
                'status' => 'published',
                'is_featured' => true,
                
                'published_at' => $now->copy()->subDays(5),
                'admin_reject_reason' => null,
                
            ],
        ];

        foreach ($courses as $course) {
            DB::table('courses')->updateOrInsert(
                ['slug' => $course['slug']],
                array_merge($course, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
