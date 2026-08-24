<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RichCourseDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $passwordHash = Hash::make('12345678');

        // 1. Seed Instructors
        $instructorsData = [
            [
                'email' => 'dr.lequockhanh@mindhub.edu.vn',
                'full_name' => 'TS. Lê Quốc Khánh',
                'phone' => '0912345601',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&q=80',
                'bio' => 'Tiến sĩ Khoa học Máy tính, cựu Tech Lead tại các tập đoàn công nghệ hàng đầu. Hơn 10 năm kinh nghiệm kiến trúc hệ thống và đào tạo hơn 20,000 học viên.',
                'expertise' => 'React 19, Next.js, Architecture & Cloud',
                'experience_years' => 10,
                'level' => 'Senior Tech Lead',
            ],
            [
                'email' => 'tranminhanh@mindhub.edu.vn',
                'full_name' => 'Trần Minh Anh',
                'phone' => '0912345602',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&q=80',
                'bio' => 'Principal Frontend Engineer & UI Specialist. Diễn giả tại nhiều hội thảo công nghệ Frontend quốc tế.',
                'expertise' => 'Frontend Mastery, React, TypeScript, Performance',
                'experience_years' => 8,
                'level' => 'Principal Engineer',
            ],
            [
                'email' => 'dothanhlong@mindhub.edu.vn',
                'full_name' => 'Đỗ Thành Long',
                'phone' => '0912345603',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&q=80',
                'bio' => 'DevOps & Cloud Solutions Architect. Đạt chứng chỉ AWS Solutions Architect Professional & CKA Kubernetes Administrator.',
                'expertise' => 'DevOps, Docker, Kubernetes, CI/CD, AWS Cloud',
                'experience_years' => 9,
                'level' => 'Cloud Architect',
            ],
            [
                'email' => 'phamquynhanh@mindhub.edu.vn',
                'full_name' => 'Phạm Quỳnh Anh',
                'phone' => '0912345604',
                'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&q=80',
                'bio' => 'AI Researcher & Data Scientist. Chuyên gia xây dựng hệ thống AI Assistant cá nhân hóa và các giải pháp Large Language Models (LLMs).',
                'expertise' => 'AI, Machine Learning, Python Data Science, LLMs',
                'experience_years' => 7,
                'level' => 'AI Specialist',
            ],
            [
                'email' => 'sarah.nguyen@mindhub.edu.vn',
                'full_name' => 'Sarah Nguyễn',
                'phone' => '0912345605',
                'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&q=80',
                'bio' => 'Head of Design với 8 năm kinh nghiệm xây dựng Design System cho các sản phẩm công nghệ FinTech và E-commerce lớn.',
                'expertise' => 'UI/UX Design, Figma, Design System, Product Thinking',
                'experience_years' => 8,
                'level' => 'Design Director',
            ],
            [
                'email' => 'nguyen.vana@mindhub.edu.vn',
                'full_name' => 'Nguyễn Văn A',
                'phone' => '0912345606',
                'avatar_url' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=300&q=80',
                'bio' => 'Senior Backend Engineer chuyên sâu về hệ sinh thái Laravel, PHP, MySQL, Microservices và High Performance APIs.',
                'expertise' => 'Laravel, PHP, REST API, Database Design',
                'experience_years' => 7,
                'level' => 'Senior Backend Lead',
            ],
        ];

        $instructorIds = [];
        foreach ($instructorsData as $item) {
            DB::table('users')->updateOrInsert(
                ['email' => $item['email']],
                [
                    'full_name' => $item['full_name'],
                    'phone' => $item['phone'],
                    'avatar_url' => $item['avatar_url'],
                    'password_hash' => $passwordHash,
                    'role' => 'instructor',
                    'status' => 'active',
                    'email_verified_at' => $now,
                    'locked' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $uId = (int) DB::table('users')->where('email', $item['email'])->value('id');
            $instructorIds[] = $uId;

            DB::table('instructor_profiles')->updateOrInsert(
                ['user_id' => $uId],
                [
                    'bio' => $item['bio'],
                    'expertise' => $item['expertise'],
                    'experience_years' => $item['experience_years'],
                    'level' => $item['level'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 2. Seed 50 Learners with fast batch insert
        $learnerNames = [
            'Nguyễn Tuấn Anh', 'Trần Thu Hà', 'Lê Minh Quang', 'Phạm Đức Thắng',
            'Hoàng Bảo Ngọc', 'Vũ Tuấn Kiệt', 'Đặng Mai Linh', 'Bùi Quốc Cường',
            'Đỗ Phương Thảo', 'Trịnh Hoài Nam', 'Phan Thanh Trúc', 'Ngô Gia Huy',
            'Dương Diệu Linh', 'Lý Hải Đăng', 'Lương Minh Châu', 'Hồ Tấn Phát',
            'Tạ Quỳnh Nga', 'Đinh Trọng Nghĩa', 'Võ Hoàng Yến', 'Cao Minh Trí',
            'Mai Thanh Sơn', 'Đoàn Bảo Châu', 'Lâm Khánh Toàn', 'Nguyễn Gia Hân',
            'Trần Đình Trọng', 'Phạm Hồng Nhung', 'Lê Văn Tài', 'Hoàng Yến Nhi',
            'Vũ Minh Khang', 'Đặng Thu Thảo', 'Bùi Quang Dũng', 'Đỗ Lan Anh',
            'Trần Quốc Bảo', 'Lê Thùy Dương', 'Nguyễn Trọng Tín', 'Phạm Bích Ngọc',
            'Hoàng Anh Tuấn', 'Vũ Khánh Linh', 'Đặng Hải Nam', 'Bùi Quỳnh Chi',
            'Đỗ Duy Mạnh', 'Trần Thị Mai', 'Nguyễn Văn Phúc', 'Lê Thảo My',
            'Phạm Thành Đạt', 'Hoàng Nhật Minh', 'Vũ Ngọc Hân', 'Đặng Văn Lâm',
            'Bùi Tiến Dũng', 'Đỗ Hùng Dũng',
        ];

        $avatarIds = [
            '1535713875002-d1d0cf377fde', '1494790108377-be9c29b29330', '1570295999919-56ceb5ecca61',
            '1507003211169-0a1dd7228f2d', '1500648767791-00dcc994a43e', '1534528741775-53994a69daeb',
            '1517841905240-472988babdf9', '1539571696357-5a69c17a67c6', '1524504388940-b1c1722653e1',
            '1522075469751-3a6694fb2f61', '1544005313-94ddf0286df2', '1506794778202-cad84cf45f1d',
        ];

        $learnerIds = [];
        foreach ($learnerNames as $idx => $name) {
            $email = 'learner.' . Str::slug($name, '.') . ($idx + 1) . '@mindhub.test';
            $avatarId = $avatarIds[$idx % count($avatarIds)];
            $avatarUrl = "https://images.unsplash.com/photo-{$avatarId}?w=150&q=80";

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'full_name' => $name,
                    'phone' => '098' . str_pad((string)($idx + 1000), 7, '0', STR_PAD_LEFT),
                    'avatar_url' => $avatarUrl,
                    'password_hash' => $passwordHash,
                    'role' => 'learner',
                    'status' => 'active',
                    'email_verified_at' => $now,
                    'locked' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $learnerIds[] = (int) DB::table('users')->where('email', $email)->value('id');
        }

        // 3. Ensure Categories Exist
        $categoriesData = [
            ['name' => 'Lập trình Backend', 'slug' => 'backend-development', 'description' => 'Khóa học Laravel, Node.js, Go, Spring Boot, Databases.'],
            ['name' => 'Lập trình Frontend', 'slug' => 'frontend-development', 'description' => 'Khóa học React, Next.js, Vue, TypeScript, Tailwind.'],
            ['name' => 'Trí tuệ nhân tạo & AI', 'slug' => 'ai-data-science', 'description' => 'Khóa học AI, LLMs, Python Machine Learning, Data.'],
            ['name' => 'DevOps & Điện toán đám mây', 'slug' => 'devops-cloud', 'description' => 'Khóa học Docker, Kubernetes, CI/CD, AWS Cloud.'],
            ['name' => 'Thiết kế UI/UX', 'slug' => 'ui-ux-design', 'description' => 'Khóa học Figma, Thiết kế giao diện & trải nghiệm người dùng.'],
            ['name' => 'Lập trình Di động', 'slug' => 'mobile-development', 'description' => 'Khóa học Flutter, React Native, iOS & Android.'],
        ];

        $categoryMap = [];
        foreach ($categoriesData as $c) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'status' => 'active',
                    'sort_order' => 'a',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $categoryMap[$c['slug']] = (int) DB::table('categories')->where('slug', $c['slug'])->value('id');
        }

        // 4. Vibrant Course Definitions
        $richCourses = [
            [
                'instructor_id' => $instructorIds[5], // Nguyễn Văn A
                'title' => 'Laravel REST API từ cơ bản đến triển khai',
                'slug' => 'laravel-rest-api-tu-co-ban-den-trien-khai',
                'category_slug' => 'backend-development',
                'short_description' => 'Khóa học toàn diện xây dựng RESTful API chuẩn quốc tế với Laravel 11, JWT Auth, Repository Pattern và Docker.',
                'description' => 'Học viên sẽ làm chủ kiến trúc MVC/Service/Repository, tối ưu hóa câu truy vấn SQL, phân quyền RBAC, tích hợp thanh toán và tự động hóa test API với Postman.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
                'price' => 499000,
                'sale_price' => 299000,
                'level' => 'beginner',
                'language' => 'vi',
                'is_featured' => 1,
                'total_duration_seconds' => 45000,
                'published_at' => $now->copy()->subDays(60),
                'target_completion_rate' => 0.85,
            ],
            [
                'instructor_id' => $instructorIds[0], // TS. Lê Quốc Khánh
                'title' => 'Chinh Phục React 19 & Next.js 15: Từ Cơ Bản Đến Cao Cấp',
                'slug' => 'chinh-phuc-react-19-nextjs-15-tu-co-ban-den-cao-cap',
                'category_slug' => 'frontend-development',
                'short_description' => 'Làm chủ React 19 Server Components, Next.js 15 App Router, Server Actions và tối ưu SEO hiệu năng cao.',
                'description' => 'Khóa học đưa bạn từ nền tảng hiện đại nhất của React 19 đến việc xây dựng ứng dụng E-learning & SaaS thực tế với TypeScript, TailwindCSS và Zustand.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&q=80',
                'price' => 799000,
                'sale_price' => 449000,
                'level' => 'intermediate',
                'language' => 'vi',
                'is_featured' => 1,
                'total_duration_seconds' => 62000,
                'published_at' => $now->copy()->subDays(45),
                'target_completion_rate' => 0.92,
            ],
            [
                'instructor_id' => $instructorIds[3], // Phạm Quỳnh Anh
                'title' => 'AI ứng dụng cho học tập cá nhân hóa & Chatbot LLMs',
                'slug' => 'ai-ung-dung-cho-hoc-tap-ca-nhan-hoa',
                'category_slug' => 'ai-data-science',
                'short_description' => 'Xây dựng trợ lý ảo học tập thông minh với OpenAI GPT-4, LangChain, RAG Vector Search và Python FastAPI.',
                'description' => 'Khám phá thế giới Generative AI và cách tích hợp AI vào sản phẩm phần mềm thực chiến để tạo lợi thế cạnh tranh vượt trội.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=800&q=80',
                'price' => 699000,
                'sale_price' => 499000,
                'level' => 'all_levels',
                'language' => 'vi',
                'is_featured' => 1,
                'total_duration_seconds' => 38000,
                'published_at' => $now->copy()->subDays(30),
                'target_completion_rate' => 0.88,
            ],
            [
                'instructor_id' => $instructorIds[1], // Trần Minh Anh
                'title' => 'PHP & MySQL nền tảng vững chắc cho Backend Developer',
                'slug' => 'php-mysql-nen-tang-vung-chac-cho-backend',
                'category_slug' => 'backend-development',
                'short_description' => 'Nắm vững tư duy lập trình backend, thiết kế cơ sở dữ liệu quan hệ và bảo mật ứng dụng web từ đầu.',
                'description' => 'Khóa học nền tảng bắt buộc dành cho bất kỳ ai muốn trở thành Backend Developer chuyên nghiệp, thực hành qua 10 dự án thực tế.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=800&q=80',
                'price' => 399000,
                'sale_price' => 249000,
                'level' => 'beginner',
                'language' => 'vi',
                'is_featured' => 1,
                'total_duration_seconds' => 32000,
                'published_at' => $now->copy()->subDays(75),
                'target_completion_rate' => 0.78,
            ],
            [
                'instructor_id' => $instructorIds[2], // Đỗ Thành Long
                'title' => 'Git & GitHub cho sinh viên làm việc nhóm và đồ án',
                'slug' => 'git-github-cho-sinh-vien-lam-do-an',
                'category_slug' => 'backend-development',
                'short_description' => 'Khóa học thực hành Git branching, Pull Request, giải quyết conflict và tự động hóa GitHub Actions hoàn toàn miễn phí.',
                'description' => 'Trang bị kỹ năng làm việc nhóm chuẩn quốc tế, tạo profile GitHub đẹp mắt thu hút nhà tuyển dụng.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?w=800&q=80',
                'price' => 0,
                'sale_price' => 0,
                'level' => 'beginner',
                'language' => 'vi',
                'is_featured' => 1,
                'total_duration_seconds' => 18000,
                'published_at' => $now->copy()->subDays(90),
                'target_completion_rate' => 0.95,
            ],
            [
                'instructor_id' => $instructorIds[2], // Đỗ Thành Long
                'title' => 'Docker & Kubernetes Thực Chiến Cho Developer & DevOps',
                'slug' => 'docker-kubernetes-thuc-chien-cho-developer',
                'category_slug' => 'devops-cloud',
                'short_description' => 'Container hóa ứng dụng, xây dựng cụm Kubernetes Cluster, quản lý ConfigMap, Secret và triển khai CI/CD pipeline.',
                'description' => 'Thực hành triển khai các kiến trúc microservices thực tế lên cloud, thiết lập giám sát Prometheus & Grafana.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9?w=800&q=80',
                'price' => 899000,
                'sale_price' => 599000,
                'level' => 'advanced',
                'language' => 'vi',
                'is_featured' => 0,
                'total_duration_seconds' => 54000,
                'published_at' => $now->copy()->subDays(5),
                'target_completion_rate' => 0.82,
            ],
            [
                'instructor_id' => $instructorIds[4], // Sarah Nguyễn
                'title' => 'Thiết kế UI/UX Chuyên Nghiệp & Design System với Figma',
                'slug' => 'thiet-ke-ui-ux-chuyen-nghiep-voi-figma',
                'category_slug' => 'ui-ux-design',
                'short_description' => 'Từ Wireframe, Prototype đến Design System hoàn chỉnh cho App & Web, chuẩn bàn giao cho Developer.',
                'description' => 'Học cách xây dựng giao diện người dùng đẹp mắt, thẩm mỹ cao, ứng dụng Auto-layout, Variables và Component Properties nâng cao trong Figma.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=800&q=80',
                'price' => 599000,
                'sale_price' => 299000,
                'level' => 'beginner',
                'language' => 'vi',
                'is_featured' => 0,
                'total_duration_seconds' => 40000,
                'published_at' => $now->copy()->subDays(12),
                'target_completion_rate' => 0.89,
            ],
            [
                'instructor_id' => $instructorIds[1], // Trần Minh Anh
                'title' => 'Lập trình ứng dụng di động đa nền tảng với React Native & Expo',
                'slug' => 'lap-trinh-di-dong-react-native-tu-a-z',
                'category_slug' => 'mobile-development',
                'short_description' => 'Xây dựng ứng dụng iOS & Android native từ một source code duy nhất với React Native, Expo Router và Reanimated.',
                'description' => 'Học cách publish ứng dụng lên App Store và Google Play, tích hợp Push Notifications, Offline mode và Camera.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&q=80',
                'price' => 699000,
                'sale_price' => 349000,
                'level' => 'intermediate',
                'language' => 'vi',
                'is_featured' => 0,
                'total_duration_seconds' => 48000,
                'published_at' => $now->copy()->subDays(18),
                'target_completion_rate' => 0.86,
            ],
            [
                'instructor_id' => $instructorIds[0], // TS. Lê Quốc Khánh
                'title' => 'Fullstack Web Development với Node.js, Express & React',
                'slug' => 'fullstack-web-development-node-react',
                'category_slug' => 'backend-development',
                'short_description' => 'Xây dựng hệ thống thương mại điện tử hoàn chỉnh từ Database, RESTful API đến giao diện tương tác mượt mà.',
                'description' => 'Khóa học trang bị toàn diện kỹ năng Fullstack Web, kết nối Frontend với Backend, xác thực JWT, thanh toán VNPay và deploy VPS.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&q=80',
                'price' => 799000,
                'sale_price' => 399000,
                'level' => 'all_levels',
                'language' => 'vi',
                'is_featured' => 0,
                'total_duration_seconds' => 58000,
                'published_at' => $now->copy()->subDays(25),
                'target_completion_rate' => 0.90,
            ],
            [
                'instructor_id' => $instructorIds[3], // Phạm Quỳnh Anh
                'title' => 'Python Data Science & Machine Learning Thực Chiến',
                'slug' => 'python-data-science-machine-learning',
                'category_slug' => 'ai-data-science',
                'short_description' => 'Phân tích dữ liệu lớn với Pandas, NumPy, trực quan hóa biểu đồ và huấn luyện các mô hình Machine Learning phổ biến.',
                'description' => 'Trang bị tư duy khai phá dữ liệu, làm sạch data bẩn, trích xuất feature và dự đoán xu hướng kinh doanh chính xác.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80',
                'price' => 549000,
                'sale_price' => 349000,
                'level' => 'beginner',
                'language' => 'vi',
                'is_featured' => 0,
                'total_duration_seconds' => 36000,
                'published_at' => $now->copy()->subDays(8),
                'target_completion_rate' => 0.84,
            ],
        ];

        $reviewTemplates = [
            5 => [
                'Khóa học cực kỳ xuất sắc! Giảng viên truyền đạt dễ hiểu, bài tập thực hành sát thực tế 100%.',
                'Nội dung cô đọng, thực chiến. Sau khóa học mình đã tự tin xây dựng được sản phẩm hoàn chỉnh.',
                'Giảng viên hỗ trợ cực kỳ nhiệt tình trong suốt quá trình học. Rất đáng đồng tiền bát gạo!',
                'Chất lượng khóa học tuyệt vời! Video sắc nét, âm thanh rõ ràng, tài liệu source code đầy đủ.',
                'Lộ trình bài bản từ cơ bản đến nâng cao. Đây là khóa học chất lượng nhất mình từng học.',
                'Cảm ơn thầy và đội ngũ MindHub rất nhiều, khóa học đã giúp mình vượt qua vòng phỏng vấn kỹ thuật thành công!',
            ],
            4 => [
                'Khóa học rất tốt, kiến thức cập nhật công nghệ mới nhất. Rất khuyến khích các bạn mới nên học.',
                'Bài giảng hay, nhiều ví dụ thực tế. Sẽ tiếp tục ủng hộ các khóa học tiếp theo của giảng viên.',
                'Nội dung phong phú, giúp mình củng cố lại toàn bộ kiến thức còn hổng.',
            ],
        ];

        foreach ($richCourses as $cData) {
            $targetRate = $cData['target_completion_rate'] ?? 0.85;
            unset($cData['target_completion_rate']);
            $catSlug = $cData['category_slug'];
            unset($cData['category_slug']);

            DB::table('courses')->updateOrInsert(
                ['slug' => $cData['slug']],
                array_merge($cData, [
                    'status' => 'published',
                    'requirements' => 'Máy tính kết nối internet, tinh thần chủ động học tập.',
                    'outcomes' => 'Nắm vững kiến thức và tự tin áp dụng vào các dự án phần mềm thực tế.',
                    'deleted_at' => null,
                    'created_at' => $cData['published_at'] ?? $now,
                    'updated_at' => $now,
                ])
            );

            $courseId = (int) DB::table('courses')->where('slug', $cData['slug'])->value('id');

            // Link Category
            if (isset($categoryMap[$catSlug])) {
                DB::table('course_categories')->updateOrInsert(
                    ['course_id' => $courseId, 'category_id' => $categoryMap[$catSlug]],
                    ['created_at' => $now]
                );
            }

            // Enroll 25-35 learners for each course
            $enrollmentCount = rand(26, 35);
            $selectedLearners = array_slice($learnerIds, 0, $enrollmentCount);
            shuffle($selectedLearners);

            $completedTarget = (int) round($enrollmentCount * $targetRate);

            foreach ($selectedLearners as $i => $learnerId) {
                $isCompleted = ($i < $completedTarget);
                $progressPercent = $isCompleted ? 100.00 : (float) rand(40, 92);
                $enrolledAt = Carbon::parse($cData['published_at'])->addDays(rand(1, 15));
                $completedAt = $isCompleted ? (clone $enrolledAt)->addDays(rand(5, 15)) : null;

                $orderCode = 'ORD-' . strtoupper(Str::random(8)) . "-{$courseId}-{$learnerId}";

                // Insert or ignore Order
                DB::table('orders')->updateOrInsert(
                    [
                        'user_id' => $learnerId,
                        'course_id' => $courseId,
                    ],
                    [
                        'order_code' => $orderCode,
                        'status' => 'paid',
                        'price_snapshot' => $cData['sale_price'] ?? $cData['price'],
                        'amount' => $cData['sale_price'] ?? $cData['price'],
                        'payment_method' => 'vnpay',
                        'payment_status' => 'paid',
                        'paid_at' => $enrolledAt,
                        'created_at' => $enrolledAt,
                        'updated_at' => $now,
                    ]
                );

                $orderId = (int) DB::table('orders')
                    ->where('user_id', $learnerId)
                    ->where('course_id', $courseId)
                    ->value('id');

                // Insert or ignore Enrollment
                DB::table('enrollments')->updateOrInsert(
                    [
                        'user_id' => $learnerId,
                        'course_id' => $courseId,
                    ],
                    [
                        'order_id' => $orderId,
                        'status' => $isCompleted ? 'completed' : 'active',
                        'progress_percent' => $progressPercent,
                        'enrolled_at' => $enrolledAt,
                        'completed_at' => $completedAt,
                        'last_accessed_at' => $now->copy()->subHours(rand(1, 48)),
                        'created_at' => $enrolledAt,
                        'updated_at' => $now,
                    ]
                );

                // Insert Reviews for ~60% of orders
                if ($i < ($enrollmentCount * 0.65) && $orderId) {
                    $rating = (rand(1, 10) <= 8) ? 5 : 4;
                    $commentList = $reviewTemplates[$rating];
                    $comment = $commentList[$i % count($commentList)];

                    DB::table('course_reviews')->updateOrInsert(
                        ['order_id' => $orderId],
                        [
                            'rating' => $rating,
                            'comment' => $comment,
                            'created_at' => $completedAt ?? (clone $enrolledAt)->addDays(rand(2, 5)),
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        }

        // 5. Ensure Active Coupons
        $coupons = [
            ['code' => 'MINDHUB50', 'name' => 'Siêu Ưu Đãi Khai Xuân 50%', 'description' => 'Giảm trực tiếp 50% cho tất cả khóa học công nghệ mới.', 'discount_type' => 'percentage', 'discount_value' => 50, 'status' => 'active'],
            ['code' => 'DEVPRO30', 'name' => 'Ưu Đãi Lập Trình Viên Pro 30%', 'description' => 'Giảm 30% khi đăng ký các khóa học nâng cao.', 'discount_type' => 'percentage', 'discount_value' => 30, 'status' => 'active'],
            ['code' => 'CHUYENGIA', 'name' => 'Voucher Chuyên Gia 20%', 'description' => 'Giảm 20% cho các khóa học cùng Giảng viên tiêu biểu.', 'discount_type' => 'percentage', 'discount_value' => 20, 'status' => 'active'],
            ['code' => 'NEWSTUDENT', 'name' => 'Chào Đón Tân Học Viên 100K', 'description' => 'Giảm ngay 100.000đ cho đơn hàng đầu tiên.', 'discount_type' => 'fixed', 'discount_value' => 100000, 'status' => 'active'],
        ];

        foreach ($coupons as $cp) {
            DB::table('coupons')->updateOrInsert(
                ['code' => $cp['code']],
                array_merge($cp, [
                    'start_at' => $now->copy()->subDays(30),
                    'end_at' => $now->copy()->addDays(180),
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        echo "RichCourseDataSeeder completed successfully!" . PHP_EOL;
    }
}
