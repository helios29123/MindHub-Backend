<?php
namespace Tests\Feature;
use App\Models\User;
use Database\Seeders\InstructorLearnerTestSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
final class InstructorLearnerApiTest extends TestCase
{
    use DatabaseTransactions;
    private User $instructorOne;
    private User $instructorTwo;
    private User $emptyInstructor;
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seed(InstructorLearnerTestSeeder::class);
        $this->instructorOne = User::query()->findOrFail(9901);
        $this->instructorTwo = User::query()->findOrFail(9902);
        $this->emptyInstructor = User::query()->findOrFail(9907);
    }
    public function test_instructor_can_get_learner_summary(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners/summary');
        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_enrollments', 3)
            ->assertJsonPath('data.active_enrollments', 2)
            ->assertJsonPath('data.completed_enrollments', 1);
    }
    public function test_one_learner_enrolled_in_two_courses_is_counted_as_two_enrollments(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?search=learner.active.test&per_page=10');
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.learner.email', 'learner.active.test@mindhub.test')
            ->assertJsonPath('data.1.learner.email', 'learner.active.test@mindhub.test');
    }
    public function test_summary_does_not_count_other_instructor_enrollments(): void
    {
        $response = $this->actingAs($this->instructorTwo)
            ->getJson('/api/instructor/learners/summary');
        $response
            ->assertOk()
            ->assertJsonPath('data.total_enrollments', 1)
            ->assertJsonPath('data.active_enrollments', 1)
            ->assertJsonPath('data.completed_enrollments', 0);
    }
    public function test_empty_instructor_gets_zero_summary(): void
    {
        $response = $this->actingAs($this->emptyInstructor)
            ->getJson('/api/instructor/learners/summary');
        $response
            ->assertOk()
            ->assertJsonPath('data.total_enrollments', 0)
            ->assertJsonPath('data.active_enrollments', 0)
            ->assertJsonPath('data.completed_enrollments', 0);
    }
    public function test_empty_instructor_gets_empty_learner_list(): void
    {
        $response = $this->actingAs($this->emptyInstructor)
            ->getJson('/api/instructor/learners');
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }
    public function test_instructor_can_get_learner_list(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?per_page=10');
        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonCount(3, 'data');
    }
    public function test_filter_by_active_status(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?status=active');
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.status', 'active')
            ->assertJsonPath('data.1.status', 'active');
    }
    public function test_filter_by_completed_status(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?status=completed');
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('data.0.learner.email', 'learner.completed.test@mindhub.test');
    }
    public function test_search_by_learner_email(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?search=learner.completed.test');
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.learner.email', 'learner.completed.test@mindhub.test');
    }
    public function test_filter_by_owned_course_id(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?course_id=9902&per_page=10');
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }
    public function test_summary_by_owned_course_id(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners/summary?course_id=9902');
        $response
            ->assertOk()
            ->assertJsonPath('data.total_enrollments', 2)
            ->assertJsonPath('data.active_enrollments', 1)
            ->assertJsonPath('data.completed_enrollments', 1);
    }
    public function test_filter_by_other_instructor_course_returns_404(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?course_id=9903');
        $response->assertStatus(404);
    }
    public function test_summary_by_other_instructor_course_returns_404(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners/summary?course_id=9903');
        $response->assertStatus(404);
    }
    public function test_filter_by_enrolled_date_range(): void
    {
        $from = now()->subDays(15)->format('Y-m-d');
        $to = now()->format('Y-m-d');
        $response = $this->actingAs($this->instructorOne)
            ->getJson("/api/instructor/learners?enrolled_from={$from}&enrolled_to={$to}&per_page=10");
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }
    public function test_invalid_status_returns_422(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?status=dropped');
        $response->assertStatus(422);
    }
    public function test_per_page_greater_than_limit_returns_422(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?per_page=51');
        $response->assertStatus(422);
    }
    public function test_instructor_can_show_owned_enrollment_detail(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/enrollments/9901');
        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.enrollment.id', 9901)
            ->assertJsonPath('data.learner.email', 'learner.active.test@mindhub.test');
    }
    public function test_show_enrollment_can_exclude_lesson_progress(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/enrollments/9901?include_lesson_progress=0');
        $response
            ->assertOk()
            ->assertJsonPath('data.enrollment.id', 9901)
            ->assertJsonPath('data.lesson_progress', []);
    }
    public function test_instructor_cannot_show_other_instructor_enrollment(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/enrollments/9903');
        $response->assertStatus(404);
    }
    public function test_instructor_can_get_lesson_progress_grouped_by_section(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/enrollments/9901/lesson-progress');
        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.enrollment_id', 9901)
            ->assertJsonPath('data.sections.0.section_id', 9901)
            ->assertJsonPath('data.sections.0.lessons.0.lesson_id', 9901);
    }
    public function test_instructor_can_get_lesson_progress_flat(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/enrollments/9901/lesson-progress?group_by_section=0');
        $response
            ->assertOk()
            ->assertJsonPath('data.enrollment_id', 9901)
            ->assertJsonPath('data.sections.0.lesson_id', 9901);
    }
    public function test_list_uses_lesson_progress_as_last_accessed_fallback(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners?course_id=9902&search=learner.active.test');
        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.enrollment_id', 9904);
        $this->assertNotNull($response->json('data.0.last_accessed_at'));
    }
    public function test_course_options_only_returns_current_instructor_courses(): void
    {
        $response = $this->actingAs($this->instructorOne)
            ->getJson('/api/instructor/learners/course-options');
        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }
}