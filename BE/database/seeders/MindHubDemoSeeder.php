<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CommissionRule;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Faq;
use App\Models\InstructorProfile;
use App\Models\Lesson;
use App\Models\LessonAsset;
use App\Models\LessonProgress;
use App\Models\Order;
use App\Models\PayoutAccount;
use App\Models\Revenue;
use App\Models\CourseReview;
use App\Models\User;
use App\Models\VideoProgress;
use App\Models\WithdrawRequest;
use App\Services\Payout\EarlyWithdrawalService;
use Database\Seeders\Data\MindHubDemoFixtureCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

final class MindHubDemoSeeder extends Seeder
{
    private string $passwordHash;
    private array $usersByEmail = [];
    private array $coursesBySlug = [];
    private array $categoryIdsBySlug = [];
    private array $allAllocatedVideoIds = [];

    public function run(): void
    {
        // 1. Safety Guard: Only run in local or testing environments
        if (!app()->environment('local', 'testing')) {
            throw new RuntimeException('MindHubDemoSeeder CHỈ ĐƯỢC CHẠY trong môi trường local hoặc testing!');
        }

        // 2. Read Credentials from ENV (Strictly no fallback defaults)
        $demoPassword = env('DEMO_SEED_PASSWORD');
        $adminEmail = env('DEMO_ADMIN_EMAIL');
        $instructorEmail = env('DEMO_INSTRUCTOR_EMAIL');
        $learnerEmail = env('DEMO_LEARNER_EMAIL');

        if (empty($demoPassword)) {
            throw new RuntimeException('Thiếu biến DEMO_SEED_PASSWORD trong file .env local.');
        }
        if (empty($adminEmail)) {
            throw new RuntimeException('Thiếu biến DEMO_ADMIN_EMAIL trong file .env local. Tuyệt đối không dùng fallback mặc định.');
        }
        if (empty($instructorEmail)) {
            throw new RuntimeException('Thiếu biến DEMO_INSTRUCTOR_EMAIL trong file .env local. Tuyệt đối không dùng fallback mặc định.');
        }
        if (empty($learnerEmail)) {
            throw new RuntimeException('Thiếu biến DEMO_LEARNER_EMAIL trong file .env local. Tuyệt đối không dùng fallback mặc định.');
        }

        $this->passwordHash = Hash::make($demoPassword);

        // 3. Load Bunny Videos JSON
        $bunnyVideosPath = database_path('data/bunny_videos.json');
        if (!File::exists($bunnyVideosPath)) {
            throw new RuntimeException("Không tìm thấy file Bunny videos tại: {$bunnyVideosPath}");
        }
        $bunnyVideosData = json_decode(File::get($bunnyVideosPath), true);
        if (!is_array($bunnyVideosData)) {
            throw new RuntimeException("File bunny_videos.json không đúng định dạng JSON.");
        }

        // 4. Ensure Storage Link and Documents Directory
        $this->ensureStorageAndAssets();

        DB::transaction(function () use ($adminEmail, $instructorEmail, $learnerEmail, $bunnyVideosData) {
            // Step 1: Seed Commission Rule (Single Active Rule)
            $this->seedCommissionRule();

            // Step 2: Seed Users (Admin, Instructors, Learners)
            $this->seedUsers($adminEmail, $instructorEmail, $learnerEmail);

            // Step 3: Seed Categories (2-Level Tree)
            $this->seedCategories();

            // Step 4: Seed Courses, Sections, Lessons (Video/Text) & Assets (PDF)
            $this->seedCoursesAndContent($bunnyVideosData);

            // Step 5: Seed Coupons
            $this->seedCoupons();

            // Step 6: Seed Historical Business Data (Orders, Enrollments, Revenues, Reviews, Comments, Withdrawals)
            $this->seedHistoricalBusinessData();

            // Step 7: Seed Banners & FAQs
            $this->seedBannersAndFaqs();
        });

        // 5. Strict Self-Validation Engine
        $this->validateSeededData($adminEmail, $instructorEmail, $learnerEmail);

        $this->command?->info(' MindHubDemoSeeder đã hoàn tất và tự kiểm tra PASS 100% nghiệp vụ!');
    }

    private function seedCommissionRule(): void
    {
        $ruleData = MindHubDemoFixtureCatalog::getCommissionRule();

        DB::table('commission_rules')->updateOrInsert(
            ['id' => 1],
            [
                'name' => $ruleData['name'],
                'description' => $ruleData['description'],
                'instructor_rate' => $ruleData['instructor_rate'],
                'platform_rate' => $ruleData['platform_rate'],
                'is_active' => $ruleData['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedUsers(string $adminEmail, string $instructorEmail, string $learnerEmail): void
    {
        // 1. Admin Live
        $admin = User::create([
            'full_name' => 'Quản trị viên MindHub',
            'email' => $adminEmail,
            'password_hash' => $this->passwordHash,
            'phone' => '0912345999',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $this->usersByEmail['admin_live'] = $admin;

        // 2. Instructors
        $instructorsData = MindHubDemoFixtureCatalog::getInstructors();

        foreach ($instructorsData as $key => $inst) {
            $email = $key === 'live_instructor' ? $instructorEmail : $inst['email'];

            $user = User::create([
                'full_name' => $inst['full_name'],
                'email' => $email,
                'password_hash' => $this->passwordHash,
                'phone' => $inst['phone'],
                'role' => User::ROLE_INSTRUCTOR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]);

            InstructorProfile::create([
                'user_id' => $user->id,
                'bio' => $inst['bio'],
                'expertise' => $inst['expertise'],
                'experience_years' => $inst['experience_years'],
                'instructor_rank' => $inst['instructor_rank'],
            ]);

            $this->usersByEmail[$key] = $user;
        }

        // 3. Learner Live
        $learnerLive = User::create([
            'full_name' => 'Nguyễn Tuấn Anh',
            'email' => $learnerEmail,
            'password_hash' => $this->passwordHash,
            'phone' => '0901234999',
            'role' => User::ROLE_LEARNER,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $this->usersByEmail['learner_live'] = $learnerLive;

        // 4. Learner Seed Identities
        $learnersData = MindHubDemoFixtureCatalog::getLearners();
        foreach ($learnersData as $key => $learner) {
            $user = User::create([
                'full_name' => $learner['full_name'],
                'email' => $learner['email'],
                'password_hash' => $this->passwordHash,
                'phone' => $learner['phone'],
                'role' => User::ROLE_LEARNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]);
            $this->usersByEmail[$key] = $user;
        }
    }

    private function seedCategories(): void
    {
        $categoriesData = MindHubDemoFixtureCatalog::getCategories();

        foreach ($categoriesData as $parentData) {
            $parent = Category::create([
                'name' => $parentData['name'],
                'slug' => $parentData['slug'],
                'description' => $parentData['description'],
                'parent_id' => null,
                'sort_order' => $parentData['sort_order'],
                'status' => 'active',
            ]);
            $this->categoryIdsBySlug[$parent->slug] = $parent->id;

            foreach ($parentData['children'] as $childData) {
                $child = Category::create([
                    'name' => $childData['name'],
                    'slug' => $childData['slug'],
                    'description' => $childData['description'],
                    'parent_id' => $parent->id,
                    'sort_order' => $childData['sort_order'],
                    'status' => 'active',
                ]);
                $this->categoryIdsBySlug[$child->slug] = $child->id;
            }
        }
    }

    private function seedCoursesAndContent(array $bunnyVideosData): void
    {
        $coursesDef = MindHubDemoFixtureCatalog::getCoursesDefinition();

        foreach ($coursesDef as $index => $cDef) {
            $instructor = $this->usersByEmail[$cDef['instructor_key']];
            $categoryId = $this->categoryIdsBySlug[$cDef['category_slug']] ?? null;

            $course = Course::create([
                'instructor_id' => $instructor->id,
                'title' => $cDef['title'],
                'slug' => $cDef['slug'],
                'short_description' => $cDef['short_description'],
                'description' => $cDef['description'],
                'price' => $cDef['price'],
                'sale_price' => $cDef['sale_price'],
                'course_level' => $cDef['course_level'],
                'status' => $cDef['status'],
                'is_featured' => $cDef['is_featured'],
                'thumbnail_url' => $cDef['thumbnail_url'],
                'published_at' => $cDef['status'] === 'published' ? now()->subDays(30 - $index * 2) : null,
            ]);

            if ($categoryId) {
                DB::table('course_categories')->insert([
                    'course_id' => $course->id,
                    'category_id' => $categoryId,
                ]);
            }

            $this->coursesBySlug[$course->slug] = $course;

            // Section 1 & Section 2
            $section1 = CourseSection::create([
                'course_id' => $course->id,
                'title' => 'Chương 1: Nền tảng, Cài đặt & Thiết lập Dự án',
                'sort_order' => 1,
                'status' => $cDef['status'] === 'published' ? 'published' : 'draft',
            ]);

            $section2 = CourseSection::create([
                'course_id' => $course->id,
                'title' => 'Chương 2: Kiến trúc Thực chiến & Tối ưu Nâng cao',
                'sort_order' => 2,
                'status' => $cDef['status'] === 'published' ? 'published' : 'draft',
            ]);

            // Slice Bunny Videos from Group
            $bunnyGroup = $cDef['bunny_group'];
            $offset = $cDef['bunny_offset'];
            $count = $cDef['bunny_count'];

            if (!isset($bunnyVideosData[$bunnyGroup])) {
                throw new RuntimeException("Nhóm video Bunny '{$bunnyGroup}' không tồn tại trong bunny_videos.json.");
            }

            $videoSlice = array_slice($bunnyVideosData[$bunnyGroup], $offset, $count);
            if (count($videoSlice) < $count) {
                throw new RuntimeException("Nhóm video Bunny '{$bunnyGroup}' không đủ {$count} video tại offset {$offset}.");
            }

            $halfCount = (int) ceil($count / 2);
            $firstLessonId = null;

            foreach ($videoSlice as $vIdx => $vItem) {
                $videoId = $vItem['video_id'];

                if (in_array($videoId, $this->allAllocatedVideoIds, true)) {
                    throw new RuntimeException("Phát hiện trùng lặp video_id: {$videoId} trong khóa học {$cDef['slug']}");
                }
                $this->allAllocatedVideoIds[] = $videoId;

                $targetSection = $vIdx < $halfCount ? $section1 : $section2;
                $sortOrder = ($vIdx % $halfCount) + 1;
                $isPreview = ($vIdx === 0); // First video is preview

                $rawTitle = $vItem['title'];
                // Clean title
                $cleanTitle = trim(preg_replace('/^[A-Za-z0-9\s]+\s\d+\s/', '', $rawTitle));
                if (empty($cleanTitle)) {
                    $cleanTitle = "Bài giảng video " . ($vIdx + 1) . ": " . $cDef['title'];
                }

                $lesson = Lesson::create([
                    'course_section_id' => $targetSection->id,
                    'course_id' => $course->id,
                    'title' => "Bài " . ($vIdx + 1) . ": " . $cleanTitle,
                    'lesson_type' => 'video',
                    'video_id' => $videoId,
                    'video_url' => "https://iframe.mediadelivery.net/embed/724015/{$videoId}?autoplay=false&loop=false&muted=false&preload=true&responsive=true",
                    'video_duration_seconds' => 720,
                    'is_preview' => $isPreview,
                    'status' => $cDef['status'] === 'published' ? 'published' : 'draft',
                    'sort_order' => $sortOrder,
                ]);

                if ($firstLessonId === null) {
                    $firstLessonId = $lesson->id;
                }
            }

            // 1 Text Lesson in Section 1
            Lesson::create([
                'course_section_id' => $section1->id,
                'course_id' => $course->id,
                'title' => 'Tài liệu Đọc & Hướng dẫn Cấu trúc Mã nguồn Dự án',
                'lesson_type' => 'text',
                'content' => "<h3>Hướng dẫn Cấu trúc Mã nguồn & Thực hành</h3><p>Chào mừng các bạn đến với khóa học <strong>{$cDef['title']}</strong>. Trong bài học này, chúng ta sẽ cùng tổng hợp các nguyên tắc thiết kế mã nguồn, cấu hình môi trường và các quy chuẩn code sạch (Clean Code / Best Practices) cần tuân thủ trong toàn bộ lộ trình học.</p><ul><li>Đọc kỹ tài liệu PDF đính kèm trước khi bắt đầu bài tập lớn.</li><li>Cài đặt phiên bản runtime và package theo đúng hướng dẫn.</li><li>Tham gia đặt câu hỏi tại tab Thảo luận nếu gặp khó khăn khi thực hành.</li></ul>",
                'is_preview' => false,
                'status' => $cDef['status'] === 'published' ? 'published' : 'draft',
                'sort_order' => $halfCount + 1,
            ]);

            // 1 PDF Asset attached to the first lesson
            LessonAsset::create([
                'lesson_id' => $firstLessonId,
                'title' => $cDef['pdf_title'],
                'file_url' => '/storage/documents/' . $cDef['pdf_filename'],
                'file_name' => $cDef['pdf_filename'],
                'file_type' => 'application/pdf',
                'file_size' => 1048576,
                'note' => 'Tài liệu tóm tắt lý thuyết và mã nguồn mẫu chính thức từ MindHub',
            ]);
        }
    }

    private function seedCoupons(): void
    {
        $couponsData = MindHubDemoFixtureCatalog::getCoupons();

        foreach ($couponsData as $cp) {
            $courseSlug = $cp['course_slug'];
            $course = $this->coursesBySlug[$courseSlug] ?? null;
            if (!$course) {
                continue;
            }

            Coupon::create([
                'code' => $cp['code'],
                'course_id' => $course->id,
                'campaign_type' => $cp['campaign_type'],
                'discount_type' => $cp['discount_type'],
                'discount_value' => $cp['discount_value'],
                'max_discount_amount' => $cp['max_discount_amount'] ?? null,
                'status' => $cp['status'],
                'start_at' => $cp['start_at'],
                'end_at' => $cp['end_at'],
                'usage_limit' => $cp['usage_limit'],
                'used_count' => $cp['used_count'],
            ]);
        }
    }

    private function seedHistoricalBusinessData(): void
    {
        $rule = CommissionRule::where('is_active', 1)->first();
        $ruleId = $rule->id;

        $revenueFixtureCourse = $this->coursesBySlug['laravel-12-thuc-chien-rest-api'];
        $mysqlCourse = $this->coursesBySlug['mysql-thuc-chien-thiet-ke-database'];
        $reactCourse = $this->coursesBySlug['react-typescript-admin-dashboard'];
        $postmanCourse = $this->coursesBySlug['kiem-thu-tu-dong-hoa-api-postman'];
        $vpsCourse = $this->coursesBySlug['trien-khai-vps-linux-aapanel'];
        $mvpCourse = $this->coursesBySlug['xay-dung-san-pham-web-mvp'];
        $pmCourse = $this->coursesBySlug['quan-ly-du-an-web-thuc-chien'];
        $analyticsCourse = $this->coursesBySlug['web-analytics-ab-testing'];
        $careerCourse = $this->coursesBySlug['lo-trinh-su-nghiep-web-developer'];

        $liveInstructor = $this->usersByEmail['live_instructor'];
        $historyInstructor = $this->usersByEmail['history_instructor'];

        $seedLearners = [
            $this->usersByEmail['learner_learn_01'],
            $this->usersByEmail['learner_review_01'],
            $this->usersByEmail['learner_history_05'],
            $this->usersByEmail['learner_history_06'],
            $this->usersByEmail['learner_history_07'],
            $this->usersByEmail['learner_history_08'],
        ];

        // -------------------------------------------------------------
        // SET 1: Exactly 5 Historical Orders on Course Revenue Fixture
        // Price: 899k, Discount: 200k, Amount: 699k
        // Gross: 699k * 5 = 3,495,000đ
        // Instructor Share (70%): 489,300đ * 5 = 2,446,500đ EXACTLY!
        // -------------------------------------------------------------
        $revLearners = [
            $seedLearners[0], // learner_learn_01
            $seedLearners[2], // learner_history_05
            $seedLearners[3], // learner_history_06
            $seedLearners[4], // learner_history_07
            $seedLearners[5], // learner_history_08
        ];

        foreach ($revLearners as $oIdx => $learner) {
            $paidAt = now()->subDays(20 - $oIdx * 3);
            $orderCode = "MH-" . $paidAt->format('Ymd') . "-100" . ($oIdx + 1);

            $order = Order::create([
                'order_code' => $orderCode,
                'user_id' => $learner->id,
                'course_id' => $revenueFixtureCourse->id,
                'commission_rule_id' => $ruleId,
                'price_snapshot' => 899000,
                'discount_amount' => 200000,
                'amount' => 699000,
                'status' => Order::STATUS_PAID,
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => 'sepay',
                'provider_transaction_id' => 'SEPAY-REV-0' . ($oIdx + 1),
                'paid_at' => $paidAt,
            ]);

            $progressPercent = [35, 50, 75, 80, 100][$oIdx];

            Enrollment::create([
                'user_id' => $learner->id,
                'course_id' => $revenueFixtureCourse->id,
                'order_id' => $order->id,
                'status' => $progressPercent === 100 ? Enrollment::STATUS_COMPLETED : Enrollment::STATUS_ACTIVE,
                'progress_percent' => $progressPercent,
                'enrolled_at' => $paidAt,
                'completed_at' => $progressPercent === 100 ? now()->subDays(2) : null,
                'last_accessed_at' => now()->subDays(1),
            ]);

            Revenue::create([
                'instructor_id' => $liveInstructor->id,
                'course_id' => $revenueFixtureCourse->id,
                'order_id' => $order->id,
                'gross_amount' => 699000,
                'instructor_amount' => 489300.00,
                'platform_fee_amount' => 209700.00,
                'commission_rule_id' => $ruleId,
                'earned_at' => $paidAt,
            ]);

            // Review for first 3 orders
            if ($oIdx < 3) {
                CourseReview::create([
                    'order_id' => $order->id,
                    'rating' => 5,
                    'comment' => [
                        'Khóa học Laravel 12 cực kỳ chi tiết, kiến thức Clean Architecture rất dễ áp dụng vào dự án thực tế!',
                        'Giảng viên giải thích kỹ càng, code mẫu chuẩn chỉ, video Bunny tải rất nhanh và mượt mà.',
                        'Nội dung phần Redis Cache và tối ưu database thực sự đáng đồng tiền bát gạo.',
                    ][$oIdx],
                ]);
            }
        }

        // -------------------------------------------------------------
        // SET 2: Orders on Background Published Courses (MySQL, React, VPS, MVP, PM, Analytics)
        // -------------------------------------------------------------
        $bgCourses = [$mysqlCourse, $reactCourse, $vpsCourse, $mvpCourse, $pmCourse, $analyticsCourse];
        $globalOrderCounter = 10;

        foreach ($bgCourses as $cIdx => $bCourse) {
            foreach ($seedLearners as $lIdx => $sLearner) {
                // Skip if learner_review_01 on VPS course, we will create a dedicated order with 0 review
                if ($bCourse->id === $vpsCourse->id && $sLearner->id === $this->usersByEmail['learner_review_01']->id) {
                    continue;
                }

                $globalOrderCounter++;
                $paidAt = now()->subDays(25 - ($lIdx * 3));
                $price = (float) $bCourse->price;
                $amount = (float) $bCourse->sale_price;
                $discount = $price - $amount;

                $order = Order::create([
                    'order_code' => "MH-" . $paidAt->format('Ymd') . "-20" . $globalOrderCounter,
                    'user_id' => $sLearner->id,
                    'course_id' => $bCourse->id,
                    'commission_rule_id' => $ruleId,
                    'price_snapshot' => $price,
                    'discount_amount' => $discount,
                    'amount' => $amount,
                    'status' => Order::STATUS_PAID,
                    'payment_status' => Order::PAYMENT_PAID,
                    'payment_method' => 'sepay',
                    'provider_transaction_id' => 'SEPAY-HIST-' . $globalOrderCounter,
                    'paid_at' => $paidAt,
                ]);

                $progress = [20, 45, 65, 80, 100, 55][$lIdx % 6];

                $enrollment = Enrollment::create([
                    'user_id' => $sLearner->id,
                    'course_id' => $bCourse->id,
                    'order_id' => $order->id,
                    'status' => $progress === 100 ? Enrollment::STATUS_COMPLETED : Enrollment::STATUS_ACTIVE,
                    'progress_percent' => $progress,
                    'enrolled_at' => $paidAt,
                    'completed_at' => $progress === 100 ? now()->subDays(1) : null,
                    'last_accessed_at' => now()->subDays(1),
                ]);

                // Track progress for LEARNER-LEARN-01 on React Course (TC-02)
                if ($bCourse->id === $reactCourse->id && $sLearner->id === $this->usersByEmail['learner_learn_01']->id) {
                    $enrollment->update(['progress_percent' => 65]);
                    $firstLesson = Lesson::where('course_id', $reactCourse->id)->first();
                    if ($firstLesson) {
                        VideoProgress::create([
                            'enrollment_id' => $enrollment->id,
                            'lesson_id' => $firstLesson->id,
                            'current_second' => 125,
                        ]);
                    }
                }

                $gross = $amount;
                $instAmount = round($gross * 0.70, 2);
                $platAmount = $gross - $instAmount;

                Revenue::create([
                    'instructor_id' => $bCourse->instructor_id,
                    'course_id' => $bCourse->id,
                    'order_id' => $order->id,
                    'gross_amount' => $gross,
                    'instructor_amount' => $instAmount,
                    'platform_fee_amount' => $platAmount,
                    'commission_rule_id' => $ruleId,
                    'earned_at' => $paidAt,
                ]);

                // Random authentic review for selected orders
                if ($lIdx < 3 && $globalOrderCounter % 2 === 0) {
                    CourseReview::create([
                        'order_id' => $order->id,
                        'rating' => 5,
                        'comment' => 'Khóa học chất lượng cao, bài giảng trực quan, giảng viên hỗ trợ nhiệt tình!',
                    ]);
                }
            }
        }

        // Dedicated order for LEARNER-REVIEW-01 on VPS course (with NO review yet)
        $reviewLearner = $this->usersByEmail['learner_review_01'];
        $vpsOrder = Order::create([
            'order_code' => "MH-" . now()->subDays(5)->format('Ymd') . "-9901",
            'user_id' => $reviewLearner->id,
            'course_id' => $vpsCourse->id,
            'commission_rule_id' => $ruleId,
            'price_snapshot' => 850000,
            'discount_amount' => 200000,
            'amount' => 650000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => 'sepay',
            'provider_transaction_id' => 'SEPAY-NOREV-01',
            'paid_at' => now()->subDays(5),
        ]);

        Enrollment::create([
            'user_id' => $reviewLearner->id,
            'course_id' => $vpsCourse->id,
            'order_id' => $vpsOrder->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'progress_percent' => 40,
            'enrolled_at' => now()->subDays(5),
        ]);

        Revenue::create([
            'instructor_id' => $vpsCourse->instructor_id,
            'course_id' => $vpsCourse->id,
            'order_id' => $vpsOrder->id,
            'gross_amount' => 650000,
            'instructor_amount' => 455000.00,
            'platform_fee_amount' => 195000.00,
            'commission_rule_id' => $ruleId,
            'earned_at' => now()->subDays(5),
        ]);

        // -------------------------------------------------------------
        // SET 3: Trial Enrollments
        // -------------------------------------------------------------
        $trialCoupon = Coupon::where('code', 'TRIALFREE')->first();
        $trialCouponId = $trialCoupon ? $trialCoupon->id : null;

        // 1. LEARNER-TRIAL-VALID-01 on Postman course
        $validTrialLearner = $this->usersByEmail['learner_trial_valid_01'];
        $validTrialOrder = Order::create([
            'order_code' => "MH-TRIAL-" . now()->subDays(2)->format('Ymd') . "-01",
            'user_id' => $validTrialLearner->id,
            'course_id' => $postmanCourse->id,
            'coupon_id' => $trialCouponId,
            'commission_rule_id' => $ruleId,
            'price_snapshot' => 499000,
            'discount_amount' => 499000,
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => 'coupon_trial',
            'provider_transaction_id' => 'TRIAL-VAL-01',
            'paid_at' => now()->subDays(2),
            'expires_at' => now()->addDays(5),
        ]);

        Enrollment::create([
            'user_id' => $validTrialLearner->id,
            'course_id' => $postmanCourse->id,
            'order_id' => $validTrialOrder->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'progress_percent' => 25,
            'enrolled_at' => now()->subDays(2),
            'expires_at' => now()->addDays(5),
        ]);

        // 2. LEARNER-TRIAL-EXPIRED-01 on Career course
        $expiredTrialLearner = $this->usersByEmail['learner_trial_expired_01'];
        $expiredTrialOrder = Order::create([
            'order_code' => "MH-TRIAL-" . now()->subDays(10)->format('Ymd') . "-02",
            'user_id' => $expiredTrialLearner->id,
            'course_id' => $careerCourse->id,
            'coupon_id' => $trialCouponId,
            'commission_rule_id' => $ruleId,
            'price_snapshot' => 399000,
            'discount_amount' => 399000,
            'amount' => 0,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => 'coupon_trial',
            'provider_transaction_id' => 'TRIAL-EXP-01',
            'paid_at' => now()->subDays(10),
            'expires_at' => now()->subDays(3),
        ]);

        Enrollment::create([
            'user_id' => $expiredTrialLearner->id,
            'course_id' => $careerCourse->id,
            'order_id' => $expiredTrialOrder->id,
            'status' => Enrollment::STATUS_INACTIVE,
            'progress_percent' => 15,
            'enrolled_at' => now()->subDays(10),
            'expires_at' => now()->subDays(3),
        ]);

        // -------------------------------------------------------------
        // SET 4: Lesson Comments (authentic questions)
        // -------------------------------------------------------------
        $mysqlFirstLesson = Lesson::where('course_id', $mysqlCourse->id)->first();
        if ($mysqlFirstLesson) {
            Comment::create([
                'user_id' => $seedLearners[0]->id,
                'lesson_id' => $mysqlFirstLesson->id,
                'content' => 'Thầy cho em hỏi khi đánh chỉ mục B-Tree trên cột kiểu VARCHAR(255) thì có lưu ý gì về độ dài Prefix Index không ạ?',
                'status' => 'visible',
                'is_official' => false,
            ]);
        }

        $postmanFirstLesson = Lesson::where('course_id', $postmanCourse->id)->first();
        if ($postmanFirstLesson) {
            Comment::create([
                'user_id' => $validTrialLearner->id,
                'lesson_id' => $postmanFirstLesson->id,
                'content' => 'Em đang học thử phần biến môi trường Postman, video rất chi tiết và dễ hiểu ạ!',
                'status' => 'visible',
                'is_official' => false,
            ]);
        }

        // -------------------------------------------------------------
        // SET 5: Historical Withdrawal for INSTRUCTOR-HISTORY-01 (Trần Minh Đức)
        // -------------------------------------------------------------
        $historyPayout = PayoutAccount::create([
            'user_id' => $historyInstructor->id,
            'provider' => 'MBBank',
            'account_number' => '0987654321',
            'account_name' => 'TRAN MINH DUC',
            'status' => 'verified',
            'is_default' => true,
        ]);

        // 1 Completed Withdrawal: 1,500,000đ
        $completedWithdrawal = WithdrawRequest::create([
            'user_id' => $historyInstructor->id,
            'payout_account_id' => $historyPayout->id,
            'amount' => 1500000.00,
            'status' => WithdrawRequest::STATUS_PAID,
            'requested_at' => now()->subDays(12),
            'approved_at' => now()->subDays(11),
            'paid_at' => now()->subDays(11),
            'account_name_snapshot' => 'TRAN MINH DUC',
            'account_number_snapshot' => '0987654321',
            'bank_name_snapshot' => 'MBBank',
            'available_balance_before' => 5000000.00,
            'available_balance_after' => 3500000.00,
            'provider_payout_id' => 'WD-HIST-PAID-01',
            'payout_provider' => 'sepay',
        ]);

        // Allocate 4 revenues of history instructor
        $histRevenues = Revenue::where('instructor_id', $historyInstructor->id)->orderBy('earned_at')->take(4)->get();
        $remainToAlloc = 1500000.00;
        foreach ($histRevenues as $hRev) {
            if ($remainToAlloc <= 0) break;
            $alloc = min($remainToAlloc, (float)$hRev->instructor_amount);
            DB::table('withdrawal_revenues')->insert([
                'withdrawal_id' => $completedWithdrawal->id,
                'revenue_id' => $hRev->id,
                'allocated_amount' => $alloc,
                'created_at' => now()->subDays(12),
            ]);
            $remainToAlloc -= $alloc;
        }

        // 1 Rejected Withdrawal (Audit History)
        WithdrawRequest::create([
            'user_id' => $historyInstructor->id,
            'payout_account_id' => $historyPayout->id,
            'amount' => 800000.00,
            'status' => WithdrawRequest::STATUS_REJECTED,
            'requested_at' => now()->subDays(18),
            'rejected_reason' => 'Thông tin chi nhánh ngân hàng chưa khớp với CCCD',
            'account_name_snapshot' => 'TRAN MINH DUC',
            'account_number_snapshot' => '0987654321',
            'bank_name_snapshot' => 'MBBank',
            'available_balance_before' => 5000000.00,
            'available_balance_after' => 5000000.00,
        ]);
    }

    private function seedBannersAndFaqs(): void
    {
        $banners = MindHubDemoFixtureCatalog::getBanners();
        foreach ($banners as $b) {
            Banner::create($b);
        }

        $faqs = MindHubDemoFixtureCatalog::getFaqs();
        foreach ($faqs as $f) {
            Faq::create($f);
        }
    }

    private function ensureStorageAndAssets(): void
    {
        $docDir = storage_path('app/public/documents');
        if (!File::exists($docDir)) {
            File::makeDirectory($docDir, 0755, true);
        }

        $coursesDef = MindHubDemoFixtureCatalog::getCoursesDefinition();
        foreach ($coursesDef as $cDef) {
            $filename = $cDef['pdf_filename'];
            $filePath = $docDir . DIRECTORY_SEPARATOR . $filename;

            if (!File::exists($filePath)) {
                $titleText = substr(preg_replace('/[^A-Za-z0-9\s]/', '', $cDef['title']), 0, 45);
                $pdfContent = "%PDF-1.4\n1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n4 0 obj << /Length 75 >> stream\nBT\n/F1 16 Tf\n50 720 Td\n(MindHub Document: {$titleText}) Tj\nET\nendstream\nendobj\n5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000228 00000 n \n0000000354 00000 n \ntrailer << /Size 6 /Root 1 0 R >>\nstartxref\n421\n%%EOF\n";
                File::put($filePath, $pdfContent);
            }
        }

        // Run storage link safely
        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
            // Ignore if symlink already exists
        }
    }

    private function validateSeededData(string $adminEmail, string $instructorEmail, string $learnerEmail): void
    {
        // Assertion 1: Total Courses = 10 (9 published, 1 draft)
        $totalCourses = Course::count();
        $publishedCourses = Course::where('status', 'published')->count();
        $draftCourses = Course::where('status', 'draft')->count();

        if ($totalCourses !== 10 || $publishedCourses !== 9 || $draftCourses !== 1) {
            throw new RuntimeException("Validation Thất bại: Số lượng Course không đúng (Tổng: {$totalCourses}, Published: {$publishedCourses}, Draft: {$draftCourses})");
        }

        // Assertion 2: Total Video Lessons = 86 with unique video_id
        $videoLessonsCount = Lesson::where('lesson_type', 'video')->whereNotNull('video_id')->count();
        $distinctVideoIds = Lesson::where('lesson_type', 'video')->distinct('video_id')->count('video_id');

        if ($videoLessonsCount !== 86 || $distinctVideoIds !== 86) {
            throw new RuntimeException("Validation Thất bại: Số lượng Video Lessons không đúng 86 hoặc bị trùng lặp ID (Tổng: {$videoLessonsCount}, Unique: {$distinctVideoIds})");
        }

        // Assertion 3: Check 3 Fixture Courses
        $fPurchase = Course::where('slug', 'mysql-thuc-chien-thiet-ke-database')->first();
        $fRevenue = Course::where('slug', 'laravel-12-thuc-chien-rest-api')->first();
        $fDraft = Course::where('slug', 'kien-truc-microservices-chuyen-sau-laravel-12-docker')->first();

        if (!$fPurchase || $fPurchase->status !== 'published') {
            throw new RuntimeException("Validation Thất bại: Course Purchase Live không tồn tại hoặc sai status.");
        }
        if (!$fRevenue || $fRevenue->status !== 'published') {
            throw new RuntimeException("Validation Thất bại: Course Revenue Fixture không tồn tại hoặc sai status.");
        }
        if (!$fDraft || $fDraft->status !== 'draft') {
            throw new RuntimeException("Validation Thất bại: Course Draft Live không tồn tại hoặc sai status draft.");
        }

        // Assertion 4: Check 3 Live Accounts
        $adminUser = User::where('email', $adminEmail)->where('role', User::ROLE_ADMIN)->first();
        $instUser = User::where('email', $instructorEmail)->where('role', User::ROLE_INSTRUCTOR)->first();
        $learnerUser = User::where('email', $learnerEmail)->where('role', User::ROLE_LEARNER)->first();

        if (!$adminUser || !$instUser || !$learnerUser) {
            throw new RuntimeException("Validation Thất bại: 3 tài khoản Live không tồn tại đúng role.");
        }

        // Assertion 5: LEARNER-LIVE-01 has ZERO enrollment and order on Course Purchase Live
        $learnerOrderCount = Order::where('user_id', $learnerUser->id)->where('course_id', $fPurchase->id)->count();
        $learnerEnrollCount = Enrollment::where('user_id', $learnerUser->id)->where('course_id', $fPurchase->id)->count();

        if ($learnerOrderCount !== 0 || $learnerEnrollCount !== 0) {
            throw new RuntimeException("Validation Thất bại: Learner Live đã bị gán order/enrollment trên Course Purchase Live trước demo.");
        }

        // Assertion 6: INSTRUCTOR-LIVE-01 availableBalance = 2,446,500đ, 0 payout account, 0 pending withdrawal
        $instPayoutCount = PayoutAccount::where('user_id', $instUser->id)->count();
        $instPendingWithdrawCount = WithdrawRequest::where('user_id', $instUser->id)->where('status', WithdrawRequest::STATUS_PENDING)->count();

        if ($instPayoutCount !== 0 || $instPendingWithdrawCount !== 0) {
            throw new RuntimeException("Validation Thất bại: Instructor Live không được có payout account hoặc withdrawal pending trước demo.");
        }

        $earlyService = app(EarlyWithdrawalService::class);
        $summary = $earlyService->getPaymentSummary($instUser->id);
        $availableBalance = (float) ($summary['available_balance'] ?? 0);

        if (abs($availableBalance - 2446500.0) > 0.01) {
            throw new RuntimeException("Validation Thất bại: Available balance của Instructor Live là {$availableBalance}, kỳ vọng đúng 2446500đ.");
        }

        // Assertion 7: Check PDF files existence
        $coursesDef = MindHubDemoFixtureCatalog::getCoursesDefinition();
        foreach ($coursesDef as $cDef) {
            $pdfPath = storage_path('app/public/documents/' . $cDef['pdf_filename']);
            if (!File::exists($pdfPath)) {
                throw new RuntimeException("Validation Thất bại: Thiếu file PDF asset vật lý: {$cDef['pdf_filename']}");
            }
        }
    }
}
