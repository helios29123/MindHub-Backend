<?php

namespace Database\Seeders\Data;

final class MindHubDemoFixtureCatalog
{
    public static function getCommissionRule(): array
    {
        return [
            'name' => 'Quy tắc hoa hồng tiêu chuẩn MindHub 2026',
            'description' => 'Tỷ lệ chia sẻ doanh thu mặc định: 70% Giảng viên, 30% Nền tảng sàn MindHub',
            'instructor_rate' => 0.7000,
            'platform_rate' => 0.3000,
            'is_active' => true,
        ];
    }

    public static function getCategories(): array
    {
        return [
            [
                'name' => 'Lập trình Backend',
                'slug' => 'lap-trinh-backend',
                'description' => 'Các khóa học chuyên sâu về kiến trúc hệ thống, RESTful API, tối ưu hiệu năng và cơ sở dữ liệu backend.',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Laravel Framework', 'slug' => 'laravel-framework', 'description' => 'Lập trình ứng dụng Web & API hiện đại với Laravel', 'sort_order' => 1],
                    ['name' => 'Microservices & Clean Architecture', 'slug' => 'microservices-clean-architecture', 'description' => 'Kiến trúc dịch vụ phân tán và mô hình Domain-Driven Design', 'sort_order' => 2],
                ]
            ],
            [
                'name' => 'Lập trình Frontend',
                'slug' => 'lap-trinh-frontend',
                'description' => 'Xây dựng giao diện Single Page Application hiện đại với React, TypeScript và UI libraries.',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'React & Next.js', 'slug' => 'react-nextjs', 'description' => 'Phát triển SPA & SSR chất lượng cao với React', 'sort_order' => 1],
                    ['name' => 'TypeScript Chuyên sâu', 'slug' => 'typescript-chuyen-sau', 'description' => 'Type-safe frontend development', 'sort_order' => 2],
                ]
            ],
            [
                'name' => 'Cơ sở dữ liệu & Cloud',
                'slug' => 'co-so-du-lieu-cloud',
                'description' => 'Thiết kế cơ sở dữ liệu quan hệ, tối ưu truy vấn Indexing và hạ tầng điện toán đám mây.',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'MySQL & Database Optimization', 'slug' => 'mysql-database-optimization', 'description' => 'Tối ưu cơ sở dữ liệu quan hệ dung lượng lớn', 'sort_order' => 1],
                    ['name' => 'Cloud & DevOps', 'slug' => 'cloud-devops', 'description' => 'Triển khai máy chủ Linux, Docker và CI/CD', 'sort_order' => 2],
                ]
            ],
            [
                'name' => 'Kiểm thử & Chất lượng Phần mềm',
                'slug' => 'kiem-thu-chat-luong-phan-mem',
                'description' => 'Tự động hóa kiểm thử API, kiểm thử hiệu năng và quy trình QA/QC chuyên nghiệp.',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'API Testing & Automation', 'slug' => 'api-testing-automation', 'description' => 'Kiểm thử API tự động với Postman', 'sort_order' => 1],
                ]
            ],
            [
                'name' => 'Sản phẩm & Phát triển Sự nghiệp',
                'slug' => 'san-pham-phat-trien-su-nghiep',
                'description' => 'Đóng gói sản phẩm MVP tinh gọn, quản lý dự án Agile và định hướng sự nghiệp IT.',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Quản lý Dự án & MVP', 'slug' => 'quan-ly-du-an-mvp', 'description' => 'Phát triển sản phẩm web từ ý tưởng đến ra mắt', 'sort_order' => 1],
                    ['name' => 'Phát triển Kỹ năng & Sự nghiệp', 'slug' => 'phat-trien-ky-nang-su-nghiep', 'description' => 'Lộ trình nghề nghiệp và kỹ năng phỏng vấn IT', 'sort_order' => 2],
                ]
            ],
        ];
    }

    public static function getInstructors(): array
    {
        return [
            'live_instructor' => [
                'persona_code' => 'INSTRUCTOR-LIVE-01',
                'full_name' => 'ThS. Lê Hoàng Nam',
                'phone' => '0912345601',
                'bio' => 'Chuyên gia Kiến trúc Phần mềm và Giảng viên cấp cao với hơn 8 năm kinh nghiệm thực chiến phát triển các hệ thống E-Learning và Fintech quy mô lớn.',
                'expertise' => 'Backend Architecture, Laravel & Cloud Computing',
                'experience_years' => 8,
                'instructor_rank' => 'gold',
            ],
            'history_instructor' => [
                'persona_code' => 'INSTRUCTOR-HISTORY-01',
                'email' => 'tran.minhduc@mindhub.local',
                'full_name' => 'Trần Minh Đức',
                'phone' => '0912345602',
                'bio' => 'Principal Database Architect tại các tập đoàn công nghệ lớn. Chuyên gia đào tạo hơn 5000 kỹ sư phần mềm về tối ưu hóa cơ sở dữ liệu MySQL và High Performance Web Apps.',
                'expertise' => 'Database Design, MySQL Performance Tuning & High Concurrency',
                'experience_years' => 12,
                'instructor_rank' => 'diamond',
            ],
            'instructor_2' => [
                'persona_code' => 'INSTRUCTOR-SEED-02',
                'email' => 'nguyen.haiduong@mindhub.local',
                'full_name' => 'Nguyễn Hải Dương',
                'phone' => '0912345603',
                'bio' => 'Lead Software QA & Automation Engineer với chứng chỉ quốc tế ISTQB. Tác giả nhiều khóa học kiểm thử tự động hóa API và quản lý chất lượng phần mềm.',
                'expertise' => 'API Automation Testing, Postman, CI/CD Pipelines & Quality Assurance',
                'experience_years' => 8,
                'instructor_rank' => 'gold',
            ],
            'instructor_3' => [
                'persona_code' => 'INSTRUCTOR-SEED-03',
                'email' => 'pham.thanhhang@mindhub.local',
                'full_name' => 'Phạm Thanh Hằng',
                'phone' => '0912345604',
                'bio' => 'Senior Frontend Architect & UI/UX Specialist. Từng dẫn dắt đội ngũ kỹ sư xây dựng hệ thống Design System và Dashboard phân tích cho các ứng dụng đa quốc gia.',
                'expertise' => 'React 19, TypeScript, Frontend Architecture & Web Analytics',
                'experience_years' => 7,
                'instructor_rank' => 'gold',
            ],
            'instructor_4' => [
                'persona_code' => 'INSTRUCTOR-SEED-04',
                'email' => 'vo.quocviet@mindhub.local',
                'full_name' => 'Võ Quốc Việt',
                'phone' => '0912345605',
                'bio' => 'Product Owner & DevOps Practitioner. Đã đồng hành cùng hơn 20 startup trong việc thiết kế sản phẩm MVP tinh gọn và vận hành hạ tầng Cloud Linux.',
                'expertise' => 'MVP Product Framework, Agile PM, Linux AAPanel & Docker Systems',
                'experience_years' => 5,
                'instructor_rank' => 'silver',
            ],
        ];
    }

    public static function getLearners(): array
    {
        return [
            'learner_learn_01' => [
                'persona_code' => 'LEARNER-LEARN-01',
                'email' => 'doan.khanhlinh@mindhub.local',
                'full_name' => 'Đoàn Khánh Linh',
                'phone' => '0901234001',
            ],
            'learner_trial_valid_01' => [
                'persona_code' => 'LEARNER-TRIAL-VALID-01',
                'email' => 'tran.thanhson@mindhub.local',
                'full_name' => 'Trần Thanh Sơn',
                'phone' => '0901234002',
            ],
            'learner_trial_expired_01' => [
                'persona_code' => 'LEARNER-TRIAL-EXPIRED-01',
                'email' => 'hoang.ducthang@mindhub.local',
                'full_name' => 'Hoàng Đức Thắng',
                'phone' => '0901234003',
            ],
            'learner_review_01' => [
                'persona_code' => 'LEARNER-REVIEW-01',
                'email' => 'dinh.quanghuy@mindhub.local',
                'full_name' => 'Đinh Quang Huy',
                'phone' => '0901234004',
            ],
            'learner_history_05' => [
                'persona_code' => 'LEARNER-HIST-05',
                'email' => 'vu.hoanglong@mindhub.local',
                'full_name' => 'Vũ Hoàng Long',
                'phone' => '0901234005',
            ],
            'learner_history_06' => [
                'persona_code' => 'LEARNER-HIST-06',
                'email' => 'nguyen.baongoc@mindhub.local',
                'full_name' => 'Nguyễn Bảo Ngọc',
                'phone' => '0901234006',
            ],
            'learner_history_07' => [
                'persona_code' => 'LEARNER-HIST-07',
                'email' => 'le.thuytrang@mindhub.local',
                'full_name' => 'Lê Thùy Trang',
                'phone' => '0901234007',
            ],
            'learner_history_08' => [
                'persona_code' => 'LEARNER-HIST-08',
                'email' => 'bui.maianh@mindhub.local',
                'full_name' => 'Bùi Mai Anh',
                'phone' => '0901234008',
            ],
        ];
    }

    public static function getCoursesDefinition(): array
    {
        return [
            [
                'fixture_role' => 'Course Revenue Fixture',
                'slug' => 'laravel-12-thuc-chien-rest-api',
                'title' => 'Laravel 12 thực chiến: Xây dựng REST API cho hệ thống bán khóa học',
                'short_description' => 'Khóa học lập trình Backend toàn diện với Laravel 12, Clean Architecture, Redis Cache và RESTful API chuẩn quốc tế.',
                'description' => 'Học viên sẽ tự tay xây dựng hệ thống backend e-learning thương mại điện tử hoàn chỉnh từ con số 0 với Laravel 12. Nội dung bao gồm kiến trúc Repository & Service pattern, authentication Sanctum/JWT, Redis caching, xử lý hàng đợi Queue, cổng thanh toán trực tuyến và tối ưu hóa truy vấn cơ sở dữ liệu.',
                'price' => 899000,
                'sale_price' => 699000,
                'course_level' => 'all_levels',
                'status' => 'published',
                'is_featured' => true,
                'instructor_key' => 'live_instructor',
                'category_slug' => 'laravel-framework',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                'bunny_group' => 'Laravel Rest API',
                'bunny_offset' => 0,
                'bunny_count' => 10,
                'pdf_filename' => 'mindhub-laravel-api-cheatsheet.pdf',
                'pdf_title' => 'Tài liệu Tóm tắt Cấu trúc REST API & Service Layer trong Laravel 12 (PDF)',
            ],
            [
                'fixture_role' => 'Course Purchase Live',
                'slug' => 'mysql-thuc-chien-thiet-ke-database',
                'title' => 'MySQL thực chiến: Thiết kế Database cho ứng dụng Web',
                'short_description' => 'Làm chủ thiết kế cơ sở dữ liệu quan hệ MySQL, chuẩn hóa dữ liệu, Indexing B-Tree và tối ưu truy vấn.',
                'description' => 'Khóa học chuyên sâu về tư duy thiết kế cơ sở dữ liệu, phân tích quan hệ thực thể, chuẩn hóa 1NF/2NF/3NF, quan hệ 1-N, N-N, Indexing B-Tree, Explain Query, Deadlock handling và kỹ thuật phân vùng Partitioning cho hệ thống hàng triệu bản ghi.',
                'price' => 799000,
                'sale_price' => 599000,
                'course_level' => 'all_levels',
                'status' => 'published',
                'is_featured' => true,
                'instructor_key' => 'history_instructor',
                'category_slug' => 'mysql-database-optimization',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&q=80',
                'bunny_group' => 'MySQL Database Design',
                'bunny_offset' => 0,
                'bunny_count' => 10,
                'pdf_filename' => 'mindhub-mysql-optimization-guide.pdf',
                'pdf_title' => 'Cẩm nang Tối ưu hóa Truy vấn & Thiết kế Indexing MySQL (PDF)',
            ],
            [
                'fixture_role' => 'Background Learning',
                'slug' => 'react-typescript-admin-dashboard',
                'title' => 'React 19 & TypeScript: Xây dựng Admin Dashboard từ đầu',
                'short_description' => 'Xây dựng giao diện Single Page Application hiện đại với React 19, TypeScript, TailwindCSS và Redux Toolkit.',
                'description' => 'Nắm vững tư duy component-driven, hooks tùy biến (Custom Hooks), Type-safe API calls với Axios & TanStack Query, quản lý state toàn cục và triển khai Dashboard quản trị chuyên nghiệp.',
                'price' => 999000,
                'sale_price' => 749000,
                'course_level' => 'intermediate',
                'status' => 'published',
                'is_featured' => true,
                'instructor_key' => 'history_instructor',
                'category_slug' => 'react-nextjs',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&q=80',
                'bunny_group' => 'React E-Learning',
                'bunny_offset' => 0,
                'bunny_count' => 10,
                'pdf_filename' => 'mindhub-react-typescript-handbook.pdf',
                'pdf_title' => 'Sổ tay Lập trình React 19 & TypeScript Best Practices (PDF)',
            ],
            [
                'fixture_role' => 'Background Trial Active',
                'slug' => 'kiem-thu-tu-dong-hoa-api-postman',
                'title' => 'Kiểm thử & Tự động hóa API Toàn diện với Postman',
                'short_description' => 'Thành thạo công cụ Postman để viết test scripts, tự động hóa test collection và tích hợp CI/CD.',
                'description' => 'Học cách tổ chức Collections, Environments, Variables, viết JavaScript test assertions, mô phỏng Mock Servers, kiểm thử tải và tích hợp vào quy trình phát triển chuyên nghiệp.',
                'price' => 499000,
                'sale_price' => 0,
                'course_level' => 'beginner',
                'status' => 'published',
                'is_featured' => false,
                'instructor_key' => 'instructor_2',
                'category_slug' => 'api-testing-automation',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
                'bunny_group' => 'Postman API Testing',
                'bunny_offset' => 0,
                'bunny_count' => 8,
                'pdf_filename' => 'mindhub-postman-testing-cheatsheet.pdf',
                'pdf_title' => 'Bảng tra cứu Câu lệnh Assertion & Scripts Kiểm thử Postman (PDF)',
            ],
            [
                'fixture_role' => 'Background Review',
                'slug' => 'trien-khai-vps-linux-aapanel',
                'title' => 'Triển khai & Vận hành VPS Linux với AAPanel & Docker',
                'short_description' => 'Tự tay cấu hình máy chủ Linux Ubuntu, cài đặt AAPanel, SSL, Nginx reverse proxy và Docker container.',
                'description' => 'Khóa học hướng dẫn quản trị máy chủ thực chiến từ thiết lập firewall UFW, cấu hình Nginx, bảo mật SSH, tự động gia hạn chứng chỉ SSL Let\'s Encrypt và triển khai ứng dụng qua Docker Compose.',
                'price' => 850000,
                'sale_price' => 650000,
                'course_level' => 'intermediate',
                'status' => 'published',
                'is_featured' => false,
                'instructor_key' => 'instructor_3',
                'category_slug' => 'cloud-devops',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?w=800&q=80',
                'bunny_group' => 'Deploy VPS AAPanel',
                'bunny_offset' => 0,
                'bunny_count' => 8,
                'pdf_filename' => 'mindhub-vps-deployment-checklist.pdf',
                'pdf_title' => 'Checklist Triển khai & Bảo mật Máy chủ VPS Linux Production (PDF)',
            ],
            [
                'fixture_role' => 'Course Draft Live',
                'slug' => 'kien-truc-microservices-chuyen-sau-laravel-12-docker',
                'title' => 'Kiến trúc Microservices chuyên sâu với Laravel 12 & Docker',
                'short_description' => 'Xây dựng hệ thống phân tán chịu tải cao với kiến trúc Event-Driven, RabbitMQ, Redis và Docker Swarm.',
                'description' => 'Khóa học chuyên sâu dành cho Senior Developer muốn làm chủ kiến trúc Microservices. Bao gồm thiết kế API Gateway, service discovery, distributed transaction với Saga pattern, message broker RabbitMQ và giám sát hệ thống với Prometheus & Grafana.',
                'price' => 999000,
                'sale_price' => 799000,
                'course_level' => 'advanced',
                'status' => 'draft',
                'is_featured' => false,
                'instructor_key' => 'live_instructor',
                'category_slug' => 'microservices-clean-architecture',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&q=80',
                'bunny_group' => 'Deploy VPS AAPanel',
                'bunny_offset' => 8,
                'bunny_count' => 8,
                'pdf_filename' => 'mindhub-microservices-docker-guide.pdf',
                'pdf_title' => 'Tài liệu Thiết kế Kiến trúc Event-Driven Microservices (PDF)',
            ],
            [
                'fixture_role' => 'Background Catalog',
                'slug' => 'xay-dung-san-pham-web-mvp',
                'title' => 'Xây dựng Sản phẩm Web MVP: Từ Ý tưởng đến Ra mắt Thực tế',
                'short_description' => 'Học cách đóng gói ý tưởng kinh doanh thành sản phẩm Web MVP khả thi trong 2 đến 4 tuần.',
                'description' => 'Khóa học hướng dẫn quy trình tinh gọn từ nghiên cứu thị trường, chốt tính năng cốt lõi, thiết kế wireframe, lựa chọn tech stack phù hợp và thu thập phản hồi người dùng đầu tiên.',
                'price' => 650000,
                'sale_price' => 499000,
                'course_level' => 'all_levels',
                'status' => 'published',
                'is_featured' => false,
                'instructor_key' => 'instructor_4',
                'category_slug' => 'quan-ly-du-an-mvp',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80',
                'bunny_group' => 'MVP Web Product',
                'bunny_offset' => 0,
                'bunny_count' => 8,
                'pdf_filename' => 'mindhub-mvp-product-framework.pdf',
                'pdf_title' => 'Khung Quy trình Phát triển Sản phẩm MVP Tinh gọn (PDF)',
            ],
            [
                'fixture_role' => 'Background Catalog',
                'slug' => 'quan-ly-du-an-web-thuc-chien',
                'title' => 'Quản lý Dự án Web Thực chiến cho Team nhỏ',
                'short_description' => 'Phương pháp quản lý tiến độ, phân bổ nguồn lực, theo dõi sprint và xử lý rủi ro bàn giao dự án.',
                'description' => 'Nắm vững kỹ năng lập timeline, phân tách task, quản trị scope creep, giao tiếp khách hàng và áp dụng quy trình Scrum/Kanban vào các dự án gia công web thực tế.',
                'price' => 599000,
                'sale_price' => 450000,
                'course_level' => 'beginner',
                'status' => 'published',
                'is_featured' => false,
                'instructor_key' => 'instructor_2',
                'category_slug' => 'quan-ly-du-an-mvp',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&q=80',
                'bunny_group' => 'Web Project Management',
                'bunny_offset' => 0,
                'bunny_count' => 8,
                'pdf_filename' => 'mindhub-project-management-template.pdf',
                'pdf_title' => 'Mẫu Kế hoạch & Checklist Quản lý Dự án Web Agile (PDF)',
            ],
            [
                'fixture_role' => 'Background Catalog',
                'slug' => 'web-analytics-ab-testing',
                'title' => 'Web Analytics & A/B Testing Tối ưu Tỷ lệ Chuyển đổi',
                'short_description' => 'Đo lường hành vi người dùng, thiết lập tracking sự kiện và triển khai thử nghiệm A/B trên website.',
                'description' => 'Học cách sử dụng Google Analytics 4, Hotjar, Google Tag Manager, phân tích phễu chuyển đổi (Funnel analysis) và thực hiện các thử nghiệm A/B Testing để tối ưu hóa doanh số.',
                'price' => 699000,
                'sale_price' => 520000,
                'course_level' => 'intermediate',
                'status' => 'published',
                'is_featured' => false,
                'instructor_key' => 'instructor_3',
                'category_slug' => 'san-pham-phat-trien-su-nghiep',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80',
                'bunny_group' => 'Web Analytics A/B Testing',
                'bunny_offset' => 0,
                'bunny_count' => 8,
                'pdf_filename' => 'mindhub-abtesting-conversion-guide.pdf',
                'pdf_title' => 'Hướng dẫn Phân tích Phễu & Thiết lập A/B Testing (PDF)',
            ],
            [
                'fixture_role' => 'Background Trial Expired',
                'slug' => 'lo-trinh-su-nghiep-web-developer',
                'title' => 'Lộ trình Phát triển Sự nghiệp Web Developer Toàn diện',
                'short_description' => 'Định hướng lộ trình từ Junior lên Senior Developer, xây dựng Portfolio ấn tượng và kỹ năng đàm phán lương.',
                'description' => 'Định vị bản thân trên thị trường tuyển dụng IT, phương pháp tự học công nghệ mới hiệu quả, kỹ năng làm việc nhóm, xây dựng thương hiệu cá nhân GitHub và chuẩn bị hồ sơ ứng tuyển chuyên nghiệp.',
                'price' => 399000,
                'sale_price' => 0,
                'course_level' => 'all_levels',
                'status' => 'published',
                'is_featured' => false,
                'instructor_key' => 'instructor_4',
                'category_slug' => 'phat-trien-ky-nang-su-nghiep',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80',
                'bunny_group' => 'Career Webdev',
                'bunny_offset' => 0,
                'bunny_count' => 8,
                'pdf_filename' => 'mindhub-webdev-career-roadmap.pdf',
                'pdf_title' => 'Bản đồ Lộ trình & Kỹ năng Kỹ sư Phần mềm Web (PDF)',
            ],
        ];
    }

    public static function getCoupons(): array
    {
        return [
            [
                'code' => 'TRIALFREE',
                'course_slug' => 'kiem-thu-tu-dong-hoa-api-postman',
                'campaign_type' => 'trial',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'status' => 'active',
                'start_at' => now()->subDays(30),
                'end_at' => now()->addDays(60),
                'usage_limit' => 1000,
                'used_count' => 1,
            ],
            [
                'code' => 'TRIALCAREER',
                'course_slug' => 'lo-trinh-su-nghiep-web-developer',
                'campaign_type' => 'trial',
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'status' => 'active',
                'start_at' => now()->subDays(30),
                'end_at' => now()->addDays(60),
                'usage_limit' => 1000,
                'used_count' => 1,
            ],
            [
                'code' => 'MINDHUB20',
                'course_slug' => 'laravel-12-thuc-chien-rest-api',
                'campaign_type' => 'discount',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'max_discount_amount' => 200000,
                'status' => 'active',
                'start_at' => now()->subDays(30),
                'end_at' => now()->addDays(60),
                'usage_limit' => 500,
                'used_count' => 15,
            ]
        ];
    }

    public static function getBanners(): array
    {
        return [
            [
                'title' => 'Nền tảng Học Lập trình Thực chiến Hàng đầu MindHub',
                'image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1600&q=80',
                'target_url' => '/courses',
                'position' => 'home_hero',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Làm chủ Công nghệ Backend & Frontend Hiện đại',
                'image_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1600&q=80',
                'target_url' => '/courses?category=lap-trinh-backend',
                'position' => 'home_hero',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Trải nghiệm Học thử Khóa học Miễn phí',
                'image_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1600&q=80',
                'target_url' => '/courses?trial=true',
                'position' => 'home_hero',
                'sort_order' => 3,
                'status' => 'active',
            ],
        ];
    }

    public static function getFaqs(): array
    {
        return [
            [
                'question' => 'Khóa học trên MindHub có thời hạn sử dụng bao lâu?',
                'answer' => 'Sau khi thanh toán thành công khóa học trả phí, bạn sẽ sở hữu quyền truy cập trọn đời vĩnh viễn vào toàn bộ video bài giảng và tài liệu học tập cập nhật.',
                'type' => 'general',
                'source' => 'system',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'question' => 'Chính sách học thử (Trial) hoạt động như thế nào?',
                'answer' => 'Đối với các khóa học có chương trình học thử, bạn được trải nghiệm toàn bộ nội dung trong thời hạn quy định của giảng viên mà không mất bất kỳ chi phí nào.',
                'type' => 'general',
                'source' => 'system',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'question' => 'Tôi có thể tải tài liệu đính kèm của bài học về máy không?',
                'answer' => 'Có. Mỗi bài học đều có file tài liệu định dạng PDF, Cheatsheet hoặc mã nguồn mẫu đính kèm trong mục Tài liệu để bạn tải về học tập ngoại tuyến.',
                'type' => 'general',
                'source' => 'system',
                'sort_order' => 3,
                'status' => 'active',
            ],
            [
                'question' => 'Làm thế nào để đăng ký trở thành Giảng viên trên MindHub?',
                'answer' => 'Bạn chỉ cần truy cập vào mục "Trở thành giảng viên" trên menu cá nhân, điền thông tin chuyên môn và hồ sơ. Đội ngũ quản trị viên MindHub sẽ kiểm duyệt và kích hoạt tài khoản trong vòng 24 giờ.',
                'type' => 'general',
                'source' => 'system',
                'sort_order' => 4,
                'status' => 'active',
            ],
        ];
    }
}
