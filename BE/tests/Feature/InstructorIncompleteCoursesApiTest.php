<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstructorIncompleteCoursesApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $suffix = str_replace('.', '-', uniqid('inc_', true));
        $this->instructor = User::query()->create([
            'full_name' => 'Instructor Test',
            'email' => 'inst-' . $suffix . '@mindhub.test',
            'role' => 'instructor',
            'status' => 'active',
            'locked' => false,
        ]);
    }

    public function test_instructor_can_fetch_incomplete_courses_with_real_completion_percentage(): void
    {
        $suffix = str_replace('.', '-', uniqid('c1_', true));

        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => (int) $this->instructor->id,
            'title' => 'Khóa Học Test Hoàn Thiện Real ' . $suffix,
            'slug' => 'khoa-hoc-test-' . $suffix,
            'short_description' => 'Mô tả ngắn khóa học test',
            'description' => 'Mô tả chi tiết khóa học test',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'price' => 500000,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/dashboard/incomplete-courses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'status',
                        'completion_percentage',
                        'completion_percent',
                        'completed_items',
                        'total_items',
                        'missing_items',
                        'next_step',
                        'action_label',
                    ],
                ],
            ]);

        $items = $response->json('data');
        $this->assertNotEmpty($items);

        $foundCourse = collect($items)->firstWhere('id', $courseId);
        $this->assertNotNull($foundCourse);
        $this->assertEquals(55, $foundCourse['completion_percent']);
        $this->assertEquals('Tiếp tục cập nhật', $foundCourse['action_label']);
        $this->assertNotNull($foundCourse['next_step']);
        $this->assertEquals(1, $foundCourse['next_step']['route_step']);
        $this->assertArrayHasKey('step', $foundCourse['next_step']);
        $this->assertArrayHasKey('focus', $foundCourse['next_step']);
        $this->assertArrayHasKey('anchor', $foundCourse['next_step']);
        $this->assertArrayHasKey('route', $foundCourse['next_step']);
    }

    public function test_fully_completed_published_course_is_excluded_from_incomplete_courses(): void
    {
        $suffix = str_replace('.', '-', uniqid('c2_', true));

        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Cat ' . $suffix,
            'slug' => 'cat-' . $suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => (int) $this->instructor->id,
            'title' => 'Khóa Học Đã Xuất Bản Hoàn Hảo ' . $suffix,
            'slug' => 'khoa-hoc-published-' . $suffix,
            'short_description' => 'Mô tả ngắn',
            'description' => 'Mô tả đầy đủ',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'price' => 200000,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pivotTable = DB::getSchemaBuilder()->hasTable('course_category') ? 'course_category' : 'course_categories';
        DB::table($pivotTable)->insert([
            'course_id' => $courseId,
            'category_id' => $categoryId,
        ]);

        $sectionId = DB::table('course_sections')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Chương 1',
            'status' => 'published',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lessons')->insert([
            'course_id' => $courseId,
            'course_section_id' => $sectionId,
            'title' => 'Bài 1',
            'slug' => 'bai-1-' . $suffix,
            'lesson_type' => 'video',
            'video_url' => 'https://example.com/video.mp4',
            'status' => 'published',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/dashboard/incomplete-courses');

        $response->assertStatus(200);

        $items = $response->json('data');
        $foundCourse = collect($items)->firstWhere('id', $courseId);
        $this->assertNull($foundCourse);
    }
}
