<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomepageCourseDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Cập nhật thông tin Giảng viên chuyên nghiệp và uy tín
        DB::table('users')->where('id', 8)->update([
            'full_name' => 'Trần Quang Huy',
            'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&q=80',
            'status' => 'active',
            'locked' => 0,
        ]);

        DB::table('users')->where('id', 9)->update([
            'full_name' => 'Lê Bảo Ngọc',
            'avatar_url' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&q=80',
            'status' => 'active',
            'locked' => 0,
        ]);

        DB::table('users')->where('id', 6)->update([
            'full_name' => 'ThS. Đặng Đỗ Minh',
            'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
            'status' => 'active',
            'locked' => 0,
        ]);

        if (DB::table('users')->where('id', 19)->exists()) {
            DB::table('users')->where('id', 19)->update([
                'full_name' => 'Nguyễn Văn An',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&q=80',
                'status' => 'active',
                'locked' => 0,
            ]);
        }

        // 2. Ẩn khóa học mock "COURSE_PUBLISHED Soft Deleted Not Public" để không làm hỏng UI
        DB::table('courses')
            ->where('title', 'like', '%Soft Deleted Not Public%')
            ->update(['status' => 'hidden', 'is_featured' => false]);

        // Cập nhật các khóa học seed cũ thành tên chuyên nghiệp
        DB::table('courses')->where('id', 1)->update([
            'title' => 'Xây dựng RESTful API chuẩn Doanh nghiệp với Laravel 11',
            'short_description' => 'Khóa học thực chiến thiết kế kiến trúc Clean Architecture, Repository Pattern và bảo mật API.',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
            'price' => 1200000,
            'discount_percent' => 25, // Giảm -25%
            'is_featured' => true,
            'status' => 'published',
            'published_at' => $now->copy()->subDays(12),
        ]);

        DB::table('courses')->where('id', 2)->update([
            'title' => 'Lập trình Web Backend toàn diện với PHP & MySQL 8.0',
            'short_description' => 'Nắm vững tư duy lập trình backend, mô hình MVC, tối ưu truy vấn cơ sở dữ liệu lớn.',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&q=80',
            'price' => 1000000,
            'discount_percent' => 40, // Giảm -40%
            'is_featured' => true,
            'status' => 'published',
            'published_at' => $now->copy()->subDays(18),
        ]);

        DB::table('courses')->where('id', 3)->update([
            'title' => 'Lập trình React.js 19 & Next.js 15: Từ Zero đến Production',
            'short_description' => 'Xây dựng ứng dụng Web quy mô lớn với Server Components, TypeScript và Tailwind CSS.',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&q=80',
            'price' => 1600000,
            'discount_percent' => 25, // Giảm -25%
            'is_featured' => true,
            'status' => 'published',
            'published_at' => $now->copy()->subDays(2),
        ]);

        DB::table('courses')->where('id', 4)->update([
            'title' => 'Nhập môn Thiết kế Giao diện UI/UX Cơ bản với Figma',
            'short_description' => 'Khóa học nền tảng miễn phí giúp bạn làm quen với tư duy thiết kế sản phẩm số hiện đại.',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=800&q=80',
            'price' => 0,
            'discount_percent' => 0,
            'is_featured' => true,
            'status' => 'published',
            'published_at' => $now->copy()->subDays(4),
        ]);

        // Cập nhật các khóa ID 17, 18
        if (DB::table('courses')->where('id', 17)->exists()) {
            DB::table('courses')->where('id', 17)->update([
                'title' => 'Khóa học Lập trình Laravel & React Fullstack Chuyên Sâu',
                'short_description' => 'Học làm dự án thực tế từ frontend React đến backend Laravel, thanh toán VNPay và deploy VPS.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&q=80',
                'price' => 1500000,
                'sale_price' => 900000, // Giảm -40%
                'is_featured' => true,
                'status' => 'published',
                'published_at' => $now->copy()->subDays(5),
            ]);
        }

        if (DB::table('courses')->where('id', 18)->exists()) {
            DB::table('courses')->where('id', 18)->update([
                'title' => 'Xây dựng RESTful API chuẩn Microservices với Node & Docker',
                'short_description' => 'Làm chủ kiến trúc phân tán Microservices, message queue RabbitMQ, container Docker và Redis caching.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1607799279861-4dd421887fb3?w=800&q=80',
                'price' => 1200000,
                'sale_price' => 720000, // Giảm -40%
                'is_featured' => true,
                'status' => 'published',
                'published_at' => $now->copy()->subDays(6),
            ]);
        }

        // 3. Danh sách các khóa học chuẩn bị chèn thêm để có bộ dữ liệu hoàn hảo
        $targetCourses = [
            // --- KHÓA HỌC NỔI BẬT & GIẢM GIÁ SÂU ---
            [
                'slug' => 'thiet-ke-do-hoa-truyen-thong-motion-graphics',
                'instructor_id' => 9, // Lê Bảo Ngọc
                'title' => 'Thiết kế Đồ họa Truyền thông Đa phương tiện & Motion Graphics',
                'short_description' => 'Thành thạo Photoshop, Illustrator, After Effects để tạo ấn phẩm truyền thông và video chuyển động đỉnh cao.',
                'description' => 'Khóa học cung cấp lộ trình từ căn bản đến nâng cao về thiết kế nhận diện thương hiệu, banner quảng cáo và hiệu ứng video.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=800&q=80',
                'price' => 1400000,
                'sale_price' => 700000, // Giảm -50%
                'course_level' => 'all_levels',
                'language' => 'vi',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => $now->copy()->subDays(8),
                'category_id' => 2, // UI/UX
            ],
            [
                'slug' => 'digital-marketing-thuc-chien-toi-uu-chuyen-doi',
                'instructor_id' => 9, // Lê Bảo Ngọc
                'title' => 'Chiến lược Digital Marketing Thực chiến & Tối ưu Chuyển đổi 2026',
                'short_description' => 'Bùng nổ doanh số với Facebook Ads, Google Ads, SEO Onpage/Offpage và Marketing Automation thế hệ mới.',
                'description' => 'Khóa học hướng dẫn chi tiết phương pháp lập kế hoạch marketing tổng thể, nghiên cứu khách hàng mục tiêu và tối ưu hóa ngân sách.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80',
                'price' => 1100000,
                'sale_price' => 660000, // Giảm -40%
                'course_level' => 'intermediate',
                'language' => 'vi',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => $now->copy()->subDays(7),
                'category_id' => 3, // Marketing
            ],

            // --- KHÓA HỌC MỚI NHẤT (Vừa ra mắt) ---
            [
                'slug' => 'lap-trinh-mobile-flutter-3-clean-architecture',
                'instructor_id' => 19, // Nguyễn Văn An
                'title' => 'Lập trình Mobile Đa nền tảng với Flutter 3 & Clean Architecture',
                'short_description' => 'Xây dựng ứng dụng iOS và Android mượt mà với Bloc State Management, tích hợp Payment Gateway và Firebase.',
                'description' => 'Tự tay phát triển 3 ứng dụng thực tế: E-commerce app, Chat real-time và Audio streaming app với Flutter.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?w=800&q=80',
                'price' => 1450000,
                'sale_price' => null, // Không giảm
                'course_level' => 'intermediate',
                'language' => 'vi',
                'status' => 'published',
                'is_featured' => false,
                'published_at' => $now->copy()->subHours(4), // Mới xuất bản hôm nay
                'category_id' => 1, // Lập trình
            ],
            [
                'slug' => 'tri-tue-nhan-tao-ung-dung-genai-langchain-openai',
                'instructor_id' => 6, // ThS. Đặng Đỗ Minh
                'title' => 'Trí tuệ Nhân tạo & Xây dựng Ứng dụng GenAI với LangChain & OpenAI',
                'short_description' => 'Tạo chatbot AI thông minh, hệ thống RAG tra cứu tài liệu doanh nghiệp và Agent tự động hoá công việc.',
                'description' => 'Khóa học đón đầu làn sóng AI 2026, ứng dụng mô hình ngôn ngữ lớn (LLM) vào các sản phẩm thực tế.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=800&q=80',
                'price' => 1950000,
                'sale_price' => 1365000, // Giảm -30%
                'course_level' => 'advanced',
                'language' => 'vi',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => $now->copy()->subHours(18), // Mới xuất bản hôm qua
                'category_id' => 1, // Lập trình
            ],
            [
                'slug' => 'devops-thuc-chien-cicd-github-actions-aws',
                'instructor_id' => 6, // ThS. Đặng Đỗ Minh
                'title' => 'DevOps Thực Chiến: CI/CD Pipeline với GitHub Actions & AWS Cloud',
                'short_description' => 'Tự động hóa quy trình build, test, deploy ứng dụng web lên cụm máy chủ AWS EC2, S3 và CloudFront.',
                'description' => 'Nắm vững hạ tầng dưới dạng mã (IaC) với Terraform, giám sát hệ thống với Prometheus và Grafana.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1618401471353-b98aedd04e11?w=800&q=80',
                'price' => 1700000,
                'sale_price' => 1190000, // Giảm -30%
                'course_level' => 'advanced',
                'language' => 'vi',
                'status' => 'published',
                'is_featured' => false,
                'published_at' => $now->copy()->subDays(1), // 1 ngày trước
                'category_id' => 1, // Lập trình
            ],
            [
                'slug' => 'phan-tich-du-lieu-python-pandas-machine-learning',
                'instructor_id' => 19, // Nguyễn Văn An
                'title' => 'Phân tích Dữ liệu Lớn với Python, Pandas & Machine Learning 2026',
                'short_description' => 'Khai phá giá trị từ dữ liệu kinh doanh, trực quan hoá biểu đồ tương tác và huấn luyện mô hình dự báo.',
                'description' => 'Học cách làm sạch dữ liệu lớn, phân tích xu hướng thị trường và xây dựng hệ sinh thái dự báo thông minh.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80',
                'price' => 1500000,
                'sale_price' => null, // Không giảm
                'course_level' => 'beginner',
                'language' => 'vi',
                'status' => 'published',
                'is_featured' => false,
                'published_at' => $now->copy()->subDays(2), // 2 ngày trước
                'category_id' => 1, // Lập trình
            ],
        ];

        foreach ($targetCourses as $item) {
            $catId = $item['category_id'] ?? 1;
            unset($item['category_id']);

            if (isset($item['sale_price']) && !empty($item['price'])) {
                $item['discount_percent'] = round((($item['price'] - $item['sale_price']) / $item['price']) * 100, 2);
            }
            unset($item['sale_price']);

            if ($existingCourse) {
                DB::table('courses')->where('id', $existingCourse->id)->update(array_merge($item, [
                    'updated_at' => $now,
                ]));
                $courseId = $existingCourse->id;
            } else {
                $courseId = DB::table('courses')->insertGetId(array_merge($item, [
                    'requirements' => json_encode(['Máy tính kết nối Internet', 'Tinh thần tự học nghiêm túc']),
                    'outcomes' => json_encode(['Tự tin làm dự án thực tế', 'Được cấp chứng chỉ tốt nghiệp']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }

            // Gán Category
            DB::table('course_categories')->updateOrInsert(
                ['course_id' => $courseId, 'category_id' => $catId],
                ['course_id' => $courseId, 'category_id' => $catId]
            );

            // Bổ sung Enrollments và Reviews thực tế nếu chưa có
            $learnerIds = DB::table('users')->where('role', 'learner')->pluck('id')->toArray();
            if (empty($learnerIds)) {
                $learnerIds = [1];
            }

            $commissionRuleId = DB::table('commission_rules')->value('id') ?? 1;

            $enrollmentCount = DB::table('enrollments')->where('course_id', $courseId)->count();
            if ($enrollmentCount < 2 && !empty($learnerIds)) {
                foreach (array_slice($learnerIds, 0, 3) as $uIdx => $uId) {
                    $orderCode = 'ORDER-SEED-' . $courseId . '-' . $uId . '-' . ($uIdx + 1);
                    $existingOrder = DB::table('orders')->where('order_code', $orderCode)->first();

                    if (!$existingOrder) {
                        $orderPrice = $item['sale_price'] ?? $item['price'];
                        $orderId = DB::table('orders')->insertGetId([
                            'order_code' => $orderCode,
                            'user_id' => $uId,
                            'course_id' => $courseId,
                            'commission_rule_id' => $commissionRuleId,
                            'status' => 'paid',
                            'payment_status' => 'paid',
                            'price_snapshot' => $item['price'],
                            'discount_amount' => $item['sale_price'] ? ($item['price'] - $item['sale_price']) : 0,
                            'amount' => $orderPrice,
                            'payment_method' => 'vnpay',
                            'paid_at' => $now->copy()->subDays(3),
                            'created_at' => $now->copy()->subDays(3),
                            'updated_at' => $now->copy()->subDays(3),
                        ]);
                    } else {
                        $orderId = $existingOrder->id;
                    }

                    // Thêm enrollment
                    DB::table('enrollments')->updateOrInsert(
                        ['user_id' => $uId, 'course_id' => $courseId],
                        [
                            'order_id' => $orderId,
                            'status' => 'active',
                            'progress_percent' => 35 + ($uIdx * 25),
                            'enrolled_at' => $now->copy()->subDays(2),
                            'last_accessed_at' => $now->copy()->subHours(6),
                            'created_at' => $now->copy()->subDays(2),
                            'updated_at' => $now->copy()->subHours(6),
                        ]
                    );

                    // Thêm review
                    DB::table('course_reviews')->updateOrInsert(
                        ['order_id' => $orderId],
                        [
                            'rating' => 5,
                            'comment' => 'Khóa học cực kỳ chất lượng, giảng viên giải thích chi tiết, thực hành bám sát thực tế!',
                            'created_at' => $now->copy()->subDays(1),
                            'updated_at' => $now->copy()->subDays(1),
                        ]
                    );
                }
            }
        }
    }
}
