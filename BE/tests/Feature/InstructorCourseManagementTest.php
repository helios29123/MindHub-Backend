<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InstructorCourseManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private User $otherInstructor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = User::create([
            'full_name' => 'Test Instructor',
            'email' => 'inst_' . uniqid() . '@example.com',
            'password_hash' => bcrypt('password'),
            'role' => User::ROLE_INSTRUCTOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->otherInstructor = User::create([
            'full_name' => 'Other Instructor',
            'email' => 'other_' . uniqid() . '@example.com',
            'password_hash' => bcrypt('password'),
            'role' => User::ROLE_INSTRUCTOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_instructor_can_soft_delete_course_without_dependencies()
    {
        $this->actingAs($this->instructor);

        $course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Unused Test Course',
            'slug' => 'unused-test-course-' . uniqid(),
            'price' => 100000,
            'status' => 'draft',
        ]);

        $response = $this->deleteJson("/api/instructor/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $course->id);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    public function test_instructor_cannot_delete_course_with_enrollments_and_gets_409()
    {
        $this->actingAs($this->instructor);

        $learner = User::create([
            'full_name' => 'Test Learner',
            'email' => 'learner_' . uniqid() . '@example.com',
            'password_hash' => bcrypt('password'),
            'role' => User::ROLE_LEARNER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Course With Student',
            'slug' => 'course-with-student-' . uniqid(),
            'price' => 100000,
            'status' => 'published',
        ]);

        \App\Models\Order::create([
            'user_id' => $learner->id,
            'course_id' => $course->id,
            'order_code' => 'ORD-' . uniqid(),
            'code' => 'ORD-' . uniqid(),
            'amount' => 100000,
            'status' => 'paid',
        ]);

        $response = $this->deleteJson("/api/instructor/courses/{$course->id}");

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Khóa học đã phát sinh học viên hoặc giao dịch nên không thể xóa. Bạn có thể ẩn khóa học thay thế.');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'deleted_at' => null,
        ]);
    }

    public function test_instructor_can_hide_and_unhide_course()
    {
        $this->actingAs($this->instructor);

        $course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Hide Unhide Test Course',
            'slug' => 'hide-unhide-' . uniqid(),
            'price' => 100000,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Hide course
        $hideResponse = $this->patchJson("/api/instructor/courses/{$course->id}/hide");
        $hideResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'hidden');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'status' => 'hidden',
        ]);

        // Unhide course
        $unhideResponse = $this->patchJson("/api/instructor/courses/{$course->id}/unhide");
        $unhideResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'status' => 'published',
        ]);
    }

    public function test_instructor_cannot_modify_other_instructors_course()
    {
        $this->actingAs($this->instructor);

        $otherCourse = Course::create([
            'instructor_id' => $this->otherInstructor->id,
            'title' => 'Other Instructor Course',
            'slug' => 'other-course-' . uniqid(),
            'price' => 100000,
            'status' => 'published',
        ]);

        $response = $this->deleteJson("/api/instructor/courses/{$otherCourse->id}");
        $response->assertStatus(404);

        $hideResponse = $this->patchJson("/api/instructor/courses/{$otherCourse->id}/hide");
        $hideResponse->assertStatus(404);
    }
}
