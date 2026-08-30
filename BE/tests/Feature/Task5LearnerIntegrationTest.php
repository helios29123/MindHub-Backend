<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Task5LearnerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed database or create specific state
        $this->learner = User::factory()->create(['role' => 'learner', 'email' => 'learner.e2e@mindhub.test']);
        $this->instructor = User::factory()->create(['role' => 'instructor']);
        
        $this->course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'status' => 'published',
            'is_free' => false,
            'price' => 100000
        ]);

        $this->section = Section::factory()->create(['course_id' => $this->course->id, 'is_active' => true]);
        
        $this->lesson1 = Lesson::factory()->create([
            'section_id' => $this->section->id, 
            'type' => 'video', 
            'is_free_preview' => false,
            'is_active' => true
        ]);
        
        $this->lesson2 = Lesson::factory()->create([
            'section_id' => $this->section->id, 
            'type' => 'document', 
            'is_free_preview' => false,
            'is_active' => true
        ]);
    }

    public function test_learner_auth_and_session()
    {
        // Unauthenticated access check
        $response = $this->getJson('/api/me/courses');
        $response->assertStatus(401);

        // Login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'learner.e2e@mindhub.test',
            'password' => 'password' // Factory default
        ]);
        $response->assertStatus(200);
        $token = $response->json('access_token');
        $this->assertNotEmpty($token);

        return $token;
    }

    /**
     * @depends test_learner_auth_and_session
     */
    public function test_home_and_discovery($token)
    {
        $response = $this->getJson('/api/courses/featured');
        $response->assertStatus(200);

        // Check recommendations
        $response = $this->withToken($token)->getJson('/api/me/recommendations/rule-based');
        $response->assertStatus(200);

        // Check alerts
        $response = $this->withToken($token)->getJson('/api/me/dynamic-alerts');
        $response->assertStatus(200);

        // Course detail
        $response = $this->getJson('/api/courses/' . $this->course->id);
        $response->assertStatus(200);
        $response->assertJsonPath('data.title', $this->course->title);

        return $token;
    }

    /**
     * @depends test_home_and_discovery
     */
    public function test_course_learning_and_enrollment($token)
    {
        // Try accessing lesson without enrollment
        $response = $this->withToken($token)->getJson('/api/learn/lessons/' . $this->lesson1->id);
        $response->assertStatus(403); // Forbidden because not enrolled

        // Enroll user
        Enrollment::create([
            'user_id' => $this->learner->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);

        // Try accessing lesson with enrollment
        $response = $this->withToken($token)->getJson('/api/learn/lessons/' . $this->lesson1->id);
        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $this->lesson1->id);

        return $token;
    }

    /**
     * @depends test_course_learning_and_enrollment
     */
    public function test_progress_and_activity($token)
    {
        // Mark lesson 1 as complete
        $response = $this->withToken($token)->postJson('/api/learn/lessons/' . $this->lesson1->id . '/complete');
        $response->assertStatus(200);

        // Check course outline
        $response = $this->withToken($token)->getJson('/api/learn/courses/' . $this->course->id . '/outline');
        $response->assertStatus(200);
        $this->assertTrue($response->json('data.progress.completed_count') >= 1);

        // Check heatmap activity
        $response = $this->withToken($token)->getJson('/api/me/activity-calendar');
        $response->assertStatus(200);

        // Unauthorized check - attempt to complete lesson for a course we don't own
        $otherCourse = Course::factory()->create(['status' => 'published']);
        $otherSection = Section::factory()->create(['course_id' => $otherCourse->id]);
        $otherLesson = Lesson::factory()->create(['section_id' => $otherSection->id]);

        $response = $this->withToken($token)->postJson('/api/learn/lessons/' . $otherLesson->id . '/complete');
        $response->assertStatus(403); // Should be blocked
    }
}
