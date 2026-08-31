<?php

namespace Tests\Feature;

use App\Models\CommissionRule;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Task5LearnerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $learner;
    protected User $instructor;
    protected Course $course;
    protected CourseSection $section;
    protected Lesson $lesson1;
    protected Lesson $lesson2;
    protected CommissionRule $commissionRule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commissionRule = CommissionRule::create([
            'name' => 'Default 70/30',
            'instructor_rate' => 0.70,
            'platform_rate' => 0.30,
            'is_active' => true,
        ]);

        $this->learner = User::create([
            'full_name' => 'Learner E2E',
            'email' => 'learner.e2e@mindhub.test',
            'password_hash' => bcrypt('password'),
            'role' => 'learner',
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->instructor = User::create([
            'full_name' => 'Instructor E2E',
            'email' => 'instructor.e2e@mindhub.test',
            'password_hash' => bcrypt('password'),
            'role' => 'instructor',
            'status' => 'active',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Khóa học E2E Test',
            'slug' => 'khoa-hoc-e2e-test',
            'status' => 'published',
            'is_free' => false,
            'price' => 100000,
            'is_featured' => true,
        ]);

        $this->section = CourseSection::create([
            'course_id' => $this->course->id,
            'title' => 'Chương 1',
            'sort_order' => 1,
            'status' => CourseSection::STATUS_PUBLISHED,
        ]);

        $this->lesson1 = Lesson::create([
            'course_section_id' => $this->section->id,
            'course_id' => $this->course->id,
            'title' => 'Bài 1: Giới thiệu',
            'lesson_type' => 'video',
            'is_free_preview' => false,
            'status' => 'published',
            'sort_order' => 1,
            'video_provider' => 'bunny',
            'video_id' => 'mock-vid-1',
        ]);

        $this->lesson2 = Lesson::create([
            'course_section_id' => $this->section->id,
            'course_id' => $this->course->id,
            'title' => 'Bài 2: Tài liệu',
            'lesson_type' => 'document',
            'is_free_preview' => false,
            'status' => 'published',
            'sort_order' => 2,
        ]);
    }

    protected function getLearnerToken(): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'learner.e2e@mindhub.test',
            'password' => 'password',
        ]);
        $response->assertStatus(200);

        return (string) ($response->json('data.access_token') ?? $response->json('access_token'));
    }

    public function test_learner_auth_and_session(): void
    {
        $response = $this->getJson('/api/me/courses');
        $response->assertStatus(401);

        $token = $this->getLearnerToken();
        $this->assertNotEmpty($token);
    }

    public function test_home_and_discovery(): void
    {
        $token = $this->getLearnerToken();

        $response = $this->getJson('/api/courses/featured');
        $response->assertStatus(200);

        $response = $this->withToken($token)->getJson('/api/me/recommendations/rule-based');
        $response->assertStatus(200);

        $response = $this->withToken($token)->getJson('/api/me/dynamic-alerts');
        $response->assertStatus(200);

        $response = $this->getJson('/api/courses/' . $this->course->id);
        $response->assertStatus(200);
        $response->assertJsonPath('data.title', $this->course->title);
    }

    public function test_course_learning_and_enrollment(): void
    {
        $token = $this->getLearnerToken();

        $response = $this->withToken($token)->getJson('/api/learn/lessons/' . $this->lesson1->id);
        $response->assertStatus(403);

        $order = Order::create([
            'order_code' => 'ORD-' . Str::random(8),
            'user_id' => $this->learner->id,
            'course_id' => $this->course->id,
            'commission_rule_id' => $this->commissionRule->id,
            'amount' => 100000,
            'price_snapshot' => 100000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        Enrollment::create([
            'user_id' => $this->learner->id,
            'course_id' => $this->course->id,
            'order_id' => $order->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->getJson('/api/learn/lessons/' . $this->lesson1->id);
        $response->assertStatus(200);
        $response->assertJsonPath('data.lesson.id', $this->lesson1->id);
    }

    public function test_progress_and_activity(): void
    {
        $token = $this->getLearnerToken();

        $order = Order::create([
            'order_code' => 'ORD-' . Str::random(8),
            'user_id' => $this->learner->id,
            'course_id' => $this->course->id,
            'commission_rule_id' => $this->commissionRule->id,
            'amount' => 100000,
            'price_snapshot' => 100000,
            'status' => Order::STATUS_PAID,
            'payment_status' => Order::PAYMENT_PAID,
        ]);

        Enrollment::create([
            'user_id' => $this->learner->id,
            'course_id' => $this->course->id,
            'order_id' => $order->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($token)->patchJson('/api/learn/lessons/' . $this->lesson1->id . '/complete', [
            'completed' => true,
        ]);
        $response->assertStatus(200);

        $response = $this->withToken($token)->getJson('/api/learn/courses/' . $this->course->id . '/outline');
        $response->assertStatus(200);

        $response = $this->withToken($token)->getJson('/api/me/activity-calendar');
        $response->assertStatus(200);

        $otherCourse = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Other Course',
            'slug' => 'other-course-' . Str::random(5),
            'status' => 'published',
            'is_free' => false,
            'price' => 200000,
        ]);
        $otherSection = CourseSection::create([
            'course_id' => $otherCourse->id,
            'title' => 'Other Section',
            'status' => CourseSection::STATUS_PUBLISHED,
        ]);
        $otherLesson = Lesson::create([
            'course_section_id' => $otherSection->id,
            'course_id' => $otherCourse->id,
            'title' => 'Other Lesson',
            'status' => 'published',
        ]);

        $response = $this->withToken($token)->patchJson('/api/learn/lessons/' . $otherLesson->id . '/complete', [
            'completed' => true,
        ]);
        $response->assertStatus(403);
    }
}
