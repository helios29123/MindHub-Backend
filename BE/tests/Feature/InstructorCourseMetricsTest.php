<?php

namespace Tests\Feature;

use App\Models\CourseView;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class InstructorCourseMetricsTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private User $learner;
    private int $courseId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        $suffix = str_replace('.', '-', uniqid('metric_', true));

        $this->instructor = User::query()->create([
            'full_name' => 'Metric Instructor ' . $suffix,
            'email' => 'metric-instructor-' . $suffix . '@mindhub.test',
            'password_hash' => null,
            'role' => 'instructor',
            'status' => 'active',
            'locked' => false,
        ]);

        $this->learner = User::query()->create([
            'full_name' => 'Metric Learner ' . $suffix,
            'email' => 'metric-learner-' . $suffix . '@mindhub.test',
            'password_hash' => null,
            'role' => 'learner',
            'status' => 'active',
            'locked' => false,
        ]);

        $this->courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $this->instructor->id,
            'title' => 'Course Metrics Test ' . $suffix,
            'slug' => 'course-metrics-test-' . $suffix,
            'price' => 500000,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $this->learner->id,
            'course_id' => $this->courseId,
            'order_code' => 'ORD-' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create an enrollment
        DB::table('enrollments')->insert([
            'user_id' => $this->learner->id,
            'course_id' => $this->courseId,
            'order_id' => $orderId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create revenue
        DB::table('revenues')->insert([
            'instructor_id' => $this->instructor->id,
            'course_id' => $this->courseId,
            'order_id' => $orderId,
            'gross_amount' => 500000,
            'instructor_amount' => 350000,
            'platform_fee_amount' => 150000,
            'status' => 'available',
            'earned_at' => now(),
            'created_at' => now(),
        ]);

        // Record a view
        CourseView::create([
            'course_id' => $this->courseId,
            'user_id' => $this->learner->id,
            'viewed_at' => now(),
        ]);
    }

    public function test_instructor_courses_index_returns_metrics(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/courses');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $targetItem = collect($data)->firstWhere('id', $this->courseId);
        $this->assertNotNull($targetItem);

        $this->assertArrayHasKey('enrollment_count', $targetItem);
        $this->assertArrayHasKey('revenue', $targetItem);
        $this->assertArrayHasKey('rating', $targetItem);
        $this->assertArrayHasKey('review_count', $targetItem);

        $this->assertEquals(1, $targetItem['enrollment_count']);
        $this->assertEquals('350000.00', $targetItem['revenue']);
    }

    public function test_instructor_dashboard_returns_real_views(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/dashboard');

        $response->assertStatus(200);

        $interaction = $response->json('data.interaction_summary');
        $this->assertNotNull($interaction);

        $this->assertArrayHasKey('views', $interaction);
        $this->assertGreaterThanOrEqual(1, $interaction['views']);
    }
}
