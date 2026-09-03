<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FullCourseCatalogAndVideoSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/bunny_videos.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("File $jsonPath not found.");
            return;
        }

        $videosData = json_decode(File::get($jsonPath), true);

        // Ensure default instructor exists
        $instructor = User::where('role', User::ROLE_INSTRUCTOR)->first();
        if (!$instructor) {
            $instructor = User::firstOrCreate(
                ['email' => 'instructor1@mindhub.test'],
                [
                    'full_name' => 'Giảng viên MindHub 01',
                    'password_hash' => bcrypt('12345678'),
                    'phone' => '0987654321',
                    'role' => User::ROLE_INSTRUCTOR,
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 10 Full Courses Definition mapped to Bunny Video Groups
        $coursesConfig = [
            [
                'group' => 'Laravel Rest API',
                'title' => 'Laravel 12 thực chiến: Xây dựng REST API cho hệ thống bán khóa học',
                'slug' => 'laravel-12-thuc-chien-rest-api',
                'short_description' => 'Khóa học lập trình Backend toàn diện với Laravel 12, MySQL, Clean Architecture và RESTful API.',
                'description' => 'Học viên sẽ tự tay xây dựng hệ thống backend thương mại điện tử e-learning từ con số 0 với Laravel 12, authentication JWT/Sanctum, caching Redis, queueing, thanh toán trực tuyến và tối ưu hóa truy vấn cơ sở dữ liệu.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                'price' => 699000,
                'sale_price' => 559200,
                'course_level' => 'all_levels',
                'category_name' => 'Lập trình Backend',
            ],
            [
                'group' => 'MySQL Database Design',
                'title' => 'MySQL thực chiến: Thiết kế Database cho ứng dụng Web',
                'slug' => 'mysql-thuc-chien-thiet-ke-database',
                'short_description' => 'Làm chủ thiết kế cơ sở dữ liệu quan hệ MySQL, chuẩn hóa dữ liệu, Indexing và tối ưu truy vấn.',
                'description' => 'Khóa học chuyên sâu về tư duy thiết kế cơ sở dữ liệu, phân tích nghiệp vụ, chuẩn hóa 1NF/2NF/3NF, quan hệ 1-N, N-N, Indexing B-Tree, Explain Query và các kỹ thuật xử lý deadlock trên hệ thống lớn.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&q=80',
                'price' => 699000,
                'sale_price' => 559200,
                'course_level' => 'all_levels',
                'category_name' => 'Cơ sở dữ liệu',
            ],
            [
                'group' => 'React E-Learning',
                'title' => 'React + TypeScript: Xây dựng Admin Dashboard từ đầu',
                'slug' => 'react-typescript-admin-dashboard',
                'short_description' => 'Xây dựng giao diện Single Page Application (SPA) hiện đại với React 19, TypeScript, TailwindCSS và Redux Toolkit.',
                'description' => 'Nắm vững tư duy component-driven, hooks tùy biến (Custom Hooks), Type-safe API calls với Axios & TanStack Query, quản lý state toàn cục và triển khai Dashboard quản trị chuyên nghiệp.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&q=80',
                'price' => 799000,
                'sale_price' => 599250,
                'course_level' => 'all_levels',
                'category_name' => 'Lập trình Frontend',
            ],
            [
                'group' => 'MVP Web Product',
                'title' => 'Xây dựng Sản phẩm Web MVP: Từ Ý tưởng đến Ra mắt Thực tế',
                'slug' => 'xay-dung-san-pham-web-mvp',
                'short_description' => 'Học cách đóng gói ý tưởng kinh doanh thành sản phẩm Web MVP khả thi trong 2 đến 4 tuần.',
                'description' => 'Khóa học hướng dẫn quy trình tinh gọn từ nghiên cứu thị trường, chốt tính năng cốt lõi, thiết kế wireframe, lựa chọn tech stack phù hợp và thu thập phản hồi người dùng đầu tiên.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80',
                'price' => 599000,
                'sale_price' => 479000,
                'course_level' => 'all_levels',
                'category_name' => 'Khởi nghiệp & Sản phẩm',
            ],
            [
                'group' => 'Postman API Testing',
                'title' => 'Kiểm thử & Tự động hóa API Toàn diện với Postman',
                'slug' => 'kiem-thu-tu-dong-hoa-api-postman',
                'short_description' => 'Thành thạo công cụ Postman để viết test scripts, tự động hóa test collection và tích hợp CI/CD.',
                'description' => 'Học cách tổ chức Collections, Environments, Variables, viết JavaScript test assertions, mô phỏng Mock Servers, kiểm thử tải và tích hợp vào quy trình phát triển chuyên nghiệp.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
                'price' => 449000,
                'sale_price' => 359000,
                'course_level' => 'all_levels',
                'category_name' => 'Kiểm thử Phần mềm',
            ],
            [
                'group' => 'Web Analytics A/B Testing',
                'title' => 'Web Analytics & A/B Testing: Tối ưu Chuyển đổi Thực chiến',
                'slug' => 'web-analytics-ab-testing-chuyen-doi',
                'short_description' => 'Đo lường hành vi người dùng trên website, thiết lập sự kiện tracking và tối ưu tỷ lệ chuyển đổi.',
                'description' => 'Thực hành cài đặt Google Analytics 4, Hotjar heatmaps, thiết kế các kịch bản thử nghiệm A/B Testing, đọc biểu đồ phễu chuyển đổi và ra quyết định cải tiến UX dựa trên dữ liệu.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80',
                'price' => 549000,
                'sale_price' => 439000,
                'course_level' => 'all_levels',
                'category_name' => 'Dữ liệu & Phân tích',
            ],
            [
                'group' => 'Deploy VPS AAPanel',
                'title' => 'Triển khai Web lên VPS Linux với AAPanel, Nginx & SSL',
                'slug' => 'trien-khai-web-vps-aapanel-nginx',
                'short_description' => 'Tự tay cấu hình VPS Linux, cài đặt AAPanel, quản lý domain DNS, chứng chỉ bảo mật SSL và Nginx Reverse Proxy.',
                'description' => 'Hướng dẫn chi tiết từ việc thuê Cloud Server, trỏ DNS Cloudflare, phân quyền SSH, cấu hình tường lửa Firewall, bảo trì sao lưu dữ liệu tự động và deploy ứng dụng web mượt mà.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&q=80',
                'price' => 499000,
                'sale_price' => 399000,
                'course_level' => 'all_levels',
                'category_name' => 'DevOps & Hệ thống',
            ],
            [
                'group' => 'Career Webdev',
                'title' => 'Định hướng Nghề nghiệp Web Developer: Xây dựng Portfolio & Phỏng vấn',
                'slug' => 'dinh-huong-nghe-nghiep-web-developer',
                'short_description' => 'Chiến lược xây dựng CV/Portfolio ấn tượng, kỹ năng deal lương và lộ trình thăng tiến nghề lập trình.',
                'description' => 'Bí quyết tạo điểm nhấn khác biệt khi nộp hồ sơ xin việc, cách trình bày dự án thực tế trên GitHub/Portfolio, chuẩn bị trả lời phỏng vấn hành vi (Behavioral) và chuyên môn kỹ thuật.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80',
                'price' => 399000,
                'sale_price' => 299000,
                'course_level' => 'all_levels',
                'category_name' => 'Kỹ năng & Sự nghiệp',
            ],
            [
                'group' => 'Web Project Management',
                'title' => 'Quản lý Dự án Web Tinh gọn: Scope, Báo giá & Bàn giao',
                'slug' => 'quan-ly-du-an-web-tinh-gon',
                'short_description' => 'Quy trình chốt phạm vi dự án (Scope), lập dự toán báo giá, hợp đồng và quản lý tiến độ bàn giao.',
                'description' => 'Học cách tránh nhận dự án vượt quá năng lực, bóc tách tính năng thành các hạng mục, điều khoản hợp đồng thiết yếu, xử lý feedback của khách hàng và quy trình nghiệm thu bàn giao chuẩn mực.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&q=80',
                'price' => 649000,
                'sale_price' => 519000,
                'course_level' => 'all_levels',
                'category_name' => 'Quản lý Dự án',
            ],
            [
                'group' => 'Backend Interview',
                'title' => 'Chinh phục Phỏng vấn Backend Developer: Kiến trúc & Hệ thống',
                'slug' => 'chinh-phuc-phong-van-backend-developer',
                'short_description' => 'Tổng hợp và giải thích chi tiết các câu hỏi phỏng vấn Backend, OOP, System Design, Caching và Database.',
                'description' => 'Tự tin vượt qua các vòng phỏng vấn kỹ thuật từ Fresher đến Senior với kiến thức vững chắc về Design Patterns, ACID trong database, cơ chế Caching nhiều tầng và thiết kế hệ thống chịu tải cao.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=800&q=80',
                'price' => 749000,
                'sale_price' => 599000,
                'course_level' => 'intermediate',
                'category_name' => 'Lập trình Backend',
            ],
        ];

        $totalVideosCount = 0;

        foreach ($coursesConfig as $index => $cfg) {
            $groupName = $cfg['group'];
            $videos = $videosData[$groupName] ?? [];

            // Find or create category
            $catId = null;
            if (!empty($cfg['category_name'])) {
                $category = Category::firstOrCreate(
                    ['name' => $cfg['category_name']],
                    [
                        'slug' => Str::slug($cfg['category_name']),
                        'description' => 'Chuyên mục ' . $cfg['category_name'],
                        'status' => 'active',
                    ]
                );
                $catId = $category->id;
            }

            $discountPercent = 0;
            if (!empty($cfg['price']) && !empty($cfg['sale_price']) && $cfg['price'] > $cfg['sale_price']) {
                $discountPercent = round((($cfg['price'] - $cfg['sale_price']) / $cfg['price']) * 100, 2);
            }

            // Create or update course
            $course = Course::updateOrCreate(
                ['slug' => $cfg['slug']],
                [
                    'instructor_id' => $instructor->id,
                    'title' => $cfg['title'],
                    'short_description' => $cfg['short_description'],
                    'description' => $cfg['description'],
                    'thumbnail_url' => $cfg['thumbnail_url'],
                    'intro_video_url' => null,
                    'price' => $cfg['price'],
                    'discount_percent' => $discountPercent,
                    'course_level' => $cfg['course_level'],
                    'language' => 'vi',
                    'requirements' => json_encode(['Máy tính kết nối Internet', 'Tinh thần học hỏi']),
                    'outcomes' => json_encode(['Nắm vững kiến thức thực chiến', 'Áp dụng vào công việc ngay lập tức']),
                    'status' => 'published',
                    'is_featured' => $index < 4,
                    'published_at' => now()->subDays($index * 2),
                ]
            );

            // Link category
            if ($catId) {
                DB::table('course_categories')->updateOrInsert(
                    ['course_id' => $course->id, 'category_id' => $catId],
                    ['course_id' => $course->id, 'category_id' => $catId]
                );
            }

            // Clean existing sections & lessons for clean seed
            Lesson::where('course_id', $course->id)->delete();
            CourseSection::where('course_id', $course->id)->delete();

            // Create main section
            $section = CourseSection::create([
                'course_id' => $course->id,
                'title' => 'Chương trình giảng dạy chi tiết',
                'description' => 'Danh sách video bài giảng thực hành của khóa học.',
                'sort_order' => 1,
                'status' => CourseSection::STATUS_PUBLISHED,
            ]);

            // Add all Bunny CDN video lessons
            $sortOrder = 1;
            foreach ($videos as $vid) {
                Lesson::create([
                    'course_id' => $course->id,
                    'course_section_id' => $section->id,
                    'title' => $vid['title'],
                    'lesson_type' => Lesson::TYPE_VIDEO,
                    'content' => 'Nội dung video bài giảng thực tế: ' . $vid['title'],
                    'video_url' => null,
                    'video_id' => $vid['video_id'],
                    'video_duration_seconds' => $vid['duration'] ?? 600,
                    'is_preview' => $sortOrder <= 2,
                    'status' => Lesson::STATUS_PUBLISHED,
                    'sort_order' => $sortOrder,
                ]);
                $sortOrder++;
                $totalVideosCount++;
            }

            $this->command?->info("Đã tạo khóa học [{$course->id}] {$cfg['title']} với " . count($videos) . " video Bunny CDN.");
        }

        $this->command?->info("🎉 Đã hoàn tất nạp 10 khóa học với tổng cộng $totalVideosCount bài giảng video Bunny CDN!");
    }
}
