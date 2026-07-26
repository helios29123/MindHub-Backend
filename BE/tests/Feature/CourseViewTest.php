<?php

namespace Tests\Feature;

use App\Models\CourseView;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CourseViewTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private User $otherUser;
    private int $courseId;
    private string $courseSlug;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        $suffix = str_replace('.', '-', uniqid('cv_', true));

        $this->instructor = $this->createUser('Course View Instructor', 'cv-instructor-' . $suffix . '@mindhub.test', 'instructor');
        $this->otherUser = $this->createUser('Course View Learner', 'cv-learner-' . $suffix . '@mindhub.test', 'learner');

        $this->courseSlug = 'course-view-test-' . $suffix;
        $this->courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $this->instructor->id,
            'title' => 'Course View Test ' . $suffix,
            'slug' => $this->courseSlug,
            'short_description' => 'Mô tả ngắn',
            'description' => 'Mô tả chi tiết',
            'thumbnail_url' => '/storage/courses/test.jpg',
            'price' => 199000,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_course_detail_page_logs_view(): void
    {
        $initialCount = CourseView::where('course_id', $this->courseId)->count();

        $response = $this->actingAs($this->otherUser)->getJson("/api/courses/{$this->courseSlug}");

        $response->assertStatus(200);

        $newCount = CourseView::where('course_id', $this->courseId)->count();
        $this->assertEquals($initialCount + 1, $newCount);
    }

    public function test_anti_duplicate_rule_prevents_view_increment_within_30_minutes(): void
    {
        $this->actingAs($this->otherUser)->getJson("/api/courses/{$this->courseSlug}");
        $count1 = CourseView::where('course_id', $this->courseId)->count();

        // Immediate refresh / second request within 30 minutes
        $this->actingAs($this->otherUser)->getJson("/api/courses/{$this->courseSlug}");
        $count2 = CourseView::where('course_id', $this->courseId)->count();

        $this->assertEquals($count1, $count2);
    }

    public function test_instructor_viewing_own_course_does_not_log_view(): void
    {
        $initialCount = CourseView::where('course_id', $this->courseId)->count();

        $this->actingAs($this->instructor)->getJson("/api/courses/{$this->courseSlug}");

        $newCount = CourseView::where('course_id', $this->courseId)->count();
        $this->assertEquals($initialCount, $newCount);
    }

    public function test_explicit_post_view_endpoint(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->postJson("/api/courses/{$this->courseId}/view");

        $response->assertStatus(200)
            ->assertJsonPath('data.recorded', true);

        // Immediate second call should return false due to anti-duplicate
        $response2 = $this->actingAs($this->otherUser)
            ->postJson("/api/courses/{$this->courseId}/view");

        $response2->assertStatus(200)
            ->assertJsonPath('data.recorded', false);
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
}
