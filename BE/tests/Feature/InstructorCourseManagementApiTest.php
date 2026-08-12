<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class InstructorCourseManagementApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private User $otherInstructor;
    private User $learner;

    private int $categoryId;
    private int $courseId;
    private int $otherCourseId;
    private int $softDeletedCourseId;
    private int $sectionId;
    private int $lessonId;
    private int $assetId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        $suffix = str_replace('.', '-', uniqid('course_', true));

        $this->instructor = $this->createUser('Course Instructor', 'course-instructor-' . $suffix . '@mindhub.test', 'instructor');
        $this->otherInstructor = $this->createUser('Other Course Instructor', 'other-course-instructor-' . $suffix . '@mindhub.test', 'instructor');
        $this->learner = $this->createUser('Course Learner', 'course-learner-' . $suffix . '@mindhub.test', 'learner');

        $this->categoryId = DB::table('categories')->insertGetId([
            'parent_id' => null,
            'name' => 'Web Development ' . $suffix,
            'slug' => 'web-development-' . $suffix,
            'description' => null,
            'sort_order' => 1,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $this->courseId = $this->createCourse((int) $this->instructor->id, [
            'title' => 'Laravel REST API ' . $suffix,
            'slug' => 'laravel-rest-api-' . $suffix,
            'short_description' => 'Mô tả ngắn',
            'description' => 'Mô tả chi tiết',
            'thumbnail_url' => '/storage/courses/laravel.jpg',
            'intro_video_url' => '/storage/videos/intro.mp4',
            'price' => 499000,
            'sale_price' => 299000,
            'level' => 'beginner',
            'language' => 'vi',
            'requirements' => 'Biết PHP cơ bản',
            'outcomes' => 'Làm được API Laravel',
            'status' => 'draft',
            'admin_reject_reason' => null,
        ]);

        DB::table('course_categories')->insert([
            'course_id' => $this->courseId,
            'category_id' => $this->categoryId,
            'created_at' => now(),
        ]);

        $this->sectionId = DB::table('course_sections')->insertGetId([
            'course_id' => $this->courseId,
            'title' => 'Chương 1',
            'description' => 'Nội dung chương 1',
            'sort_order' => 1,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $this->lessonId = DB::table('lessons')->insertGetId([
            'course_id' => $this->courseId,
            'course_section_id' => $this->sectionId,
            'title' => 'Bài học 1',
            'slug' => 'bai-hoc-1-' . $suffix,
            'lesson_type' => 'video',
            'content' => 'Nội dung bài học',
            'video_url' => '/storage/videos/lesson.mp4',
            'video_duration_seconds' => 600,
            'is_preview' => true,
            'status' => 'published',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $this->assetId = DB::table('lesson_assets')->insertGetId([
            'lesson_id' => $this->lessonId,
            'title' => 'Slide bài học',
            'file_url' => '/storage/assets/slide.pdf',
            'file_name' => 'slide.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'note' => 'Tài liệu tham khảo',
            'created_at' => now(),
            'deleted_at' => null,
        ]);

        $orderId = $this->createOrder($this->courseId, 299000);

        $this->createEnrollment(
            (int) $this->learner->id,
            $this->courseId,
            $orderId
        );

        DB::table('revenues')->insert([
            'order_id' => $orderId,
            'course_id' => $this->courseId,
            'instructor_id' => $this->instructor->id,
            'gross_amount' => 299000,
            'instructor_amount' => 209300,
            'platform_fee_amount' => 89700,
            'status' => 'available',
            'earned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->otherCourseId = $this->createCourse((int) $this->otherInstructor->id, [
            'title' => 'Other course ' . $suffix,
            'slug' => 'other-course-' . $suffix,
            'status' => 'draft',
        ]);

        $this->softDeletedCourseId = $this->createCourse((int) $this->instructor->id, [
            'title' => 'Deleted course ' . $suffix,
            'slug' => 'deleted-course-' . $suffix,
            'status' => 'draft',
            'deleted_at' => now(),
        ]);
    }

    public function test_instructor_can_create_draft_course_with_minimal_data(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/courses/draft', [
                'title' => 'Khóa học nháp Laravel',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Khóa học nháp Laravel')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.status_label', 'Đang hoàn thiện');

        $this->assertDatabaseHas('courses', [
            'instructor_id' => $this->instructor->id,
            'title' => 'Khóa học nháp Laravel',
            'status' => 'draft',
        ]);
    }

    public function test_create_draft_rejects_client_controlled_fields(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/courses/draft', [
                'title' => 'Khóa học hack',
                'instructor_id' => $this->otherInstructor->id,
                'status' => 'published',
                'is_featured' => true,
            ]);

        $response->assertUnprocessable();
    }

    public function test_instructor_can_create_full_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/courses', [
                'title' => 'Laravel API Full Course',
                'short_description' => 'Khóa học API',
                'description' => 'Mô tả khóa học API',
                'price' => 599000,
                'sale_price' => 399000,
                'level' => 'intermediate',
                'language' => 'vi',
                'requirements' => 'Biết PHP',
                'outcomes' => 'Làm được REST API',
                'category_ids' => [$this->categoryId],
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Laravel API Full Course')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.categories.0.id', $this->categoryId);
    }

    public function test_full_course_create_rejects_status_field(): void
    {
        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/courses', [
                'title' => 'Không được set status',
                'price' => 100000,
                'status' => 'published',
            ]);

        $response->assertUnprocessable();
    }

    public function test_instructor_can_show_course_detail_with_summary(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/courses/' . $this->courseId);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->courseId)
            ->assertJsonPath('data.summary.section_count', 1)
            ->assertJsonPath('data.summary.lesson_count', 1)
            ->assertJsonPath('data.summary.asset_count', 1)
            ->assertJsonPath('data.summary.preview_lesson_count', 1)
            ->assertJsonPath('data.summary.enrollment_count', 1)
            ->assertJsonPath('data.summary.revenue_amount', 209300);
    }

    public function test_show_rejects_other_instructor_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/courses/' . $this->otherCourseId);

        $response->assertNotFound();
    }

    public function test_show_rejects_soft_deleted_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/courses/' . $this->softDeletedCourseId);

        $response->assertNotFound();
    }

    public function test_instructor_can_get_course_content_tree(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/courses/' . $this->courseId . '/content');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.course.id', $this->courseId)
            ->assertJsonPath('data.sections.0.id', $this->sectionId)
            ->assertJsonPath('data.sections.0.lessons.0.id', $this->lessonId)
            ->assertJsonPath('data.sections.0.lessons.0.assets.0.id', $this->assetId);
    }

    public function test_content_rejects_other_instructor_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/courses/' . $this->otherCourseId . '/content');

        $response->assertNotFound();
    }

    public function test_instructor_can_update_draft_course(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/courses/' . $this->courseId . '/draft', [
                'title' => 'Laravel REST API đã sửa',
                'price' => 699000,
                'category_ids' => [$this->categoryId],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Laravel REST API đã sửa')
            ->assertJsonPath('data.price', 699000)
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('courses', [
            'id' => $this->courseId,
            'title' => 'Laravel REST API đã sửa',
            'status' => 'draft',
        ]);
    }

    public function test_update_draft_rejects_pending_review_course(): void
    {
        DB::table('courses')
            ->where('id', $this->courseId)
            ->update([
                'status' => 'pending_review',
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/courses/' . $this->courseId . '/draft', [
                'title' => 'Không được sửa khi đang duyệt',
            ]);

        $response->assertStatus(409);
    }

    public function test_update_course_rejects_status_field(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/courses/' . $this->courseId, [
                'status' => 'published',
            ]);

        $response->assertUnprocessable();
    }

    public function test_course_index_excludes_other_and_soft_deleted_courses(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/courses?per_page=20');

        $response->assertOk();

        $ids = array_column($response->json('data'), 'id');

        $this->assertContains($this->courseId, $ids);
        $this->assertNotContains($this->otherCourseId, $ids);
        $this->assertNotContains($this->softDeletedCourseId, $ids);
    }

    private function createUser(string $fullName, string $email, string $role): User
    {
        return User::query()->create([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => null,
            'role' => $role,
            'status' => 'active',
            'locked' => false,
        ]);
    }

    private function createCourse(int $instructorId, array $overrides = []): int
    {
        $data = array_merge([
            'instructor_id' => $instructorId,
            'title' => 'Course test',
            'slug' => 'course-test-' . uniqid(),
            'short_description' => null,
            'description' => null,
            'thumbnail_url' => null,
            'intro_video_url' => null,
            'price' => 0,
            'sale_price' => null,
            'level' => 'beginner',
            'language' => 'vi',
            'requirements' => null,
            'outcomes' => null,
            'status' => 'draft',
            'is_featured' => false,
            'total_duration_seconds' => 0,
            'published_at' => null,
            'admin_reject_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ], $overrides);

        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn('courses', $column)) {
                unset($data[$column]);
            }
        }

        return DB::table('courses')->insertGetId($data);
    }

    private function createOrder(int $courseId, float $amount): int
    {
        $data = [
            'user_id' => $this->learner->id,
            'course_id' => $courseId,
        ];

        if (Schema::hasColumn('orders', 'order_code')) {
            $data['order_code'] = 'COURSE-ORD-' . uniqid();
        }

        if (Schema::hasColumn('orders', 'order_type')) {
            $data['order_type'] = 'course_purchase';
        }

        if (Schema::hasColumn('orders', 'price_snapshot')) {
            $data['price_snapshot'] = $amount;
        }

        if (Schema::hasColumn('orders', 'price')) {
            $data['price'] = $amount;
        }

        if (Schema::hasColumn('orders', 'discount_amount')) {
            $data['discount_amount'] = 0;
        }

        if (Schema::hasColumn('orders', 'amount')) {
            $data['amount'] = $amount;
        }

        if (Schema::hasColumn('orders', 'final_amount')) {
            $data['final_amount'] = $amount;
        }

        if (Schema::hasColumn('orders', 'payment_method')) {
            $data['payment_method'] = 'manual';
        }

        if (Schema::hasColumn('orders', 'payment_status')) {
            $data['payment_status'] = 'paid';
        }

        if (Schema::hasColumn('orders', 'status')) {
            $data['status'] = 'paid';
        }

        if (Schema::hasColumn('orders', 'provider_transaction_id')) {
            $data['provider_transaction_id'] = 'COURSE-TXN-' . uniqid();
        }

        if (Schema::hasColumn('orders', 'paid_at')) {
            $data['paid_at'] = now();
        }

        if (Schema::hasColumn('orders', 'coupon_id')) {
            $data['coupon_id'] = null;
        }

        if (Schema::hasColumn('orders', 'created_at')) {
            $data['created_at'] = now();
        }

        if (Schema::hasColumn('orders', 'updated_at')) {
            $data['updated_at'] = now();
        }

        return DB::table('orders')->insertGetId($data);
    }

    private function createEnrollment(int $userId, int $courseId, int $orderId): void
    {
        $data = [
            'user_id' => $userId,
            'course_id' => $courseId,
            'order_id' => $orderId,
            'status' => 'active',
            'progress_percent' => 20,
            'enrolled_at' => now(),
            'completed_at' => null,
            'last_accessed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn('enrollments', $column)) {
                unset($data[$column]);
            }
        }

        DB::table('enrollments')->insert($data);
    }
}