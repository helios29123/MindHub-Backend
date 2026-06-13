<?php

namespace Tests\Feature\Instructor;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;
    private User $learner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = clone User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->learner = clone User::factory()->create([
            'role' => 'learner',
            'status' => 'active',
        ]);
    }

    public function test_unauthenticated_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/instructor/profile');
        $response->assertStatus(401);

        $response = $this->patchJson('/api/instructor/profile', [
            'bio' => 'Test',
        ]);
        $response->assertStatus(401);
    }

    public function test_non_instructor_cannot_access_profile(): void
    {
        $response = $this->actingAs($this->learner)->getJson('/api/instructor/profile');
        $response->assertStatus(403);

        $response = $this->actingAs($this->learner)->patchJson('/api/instructor/profile', [
            'bio' => 'Test',
        ]);
        $response->assertStatus(403);
    }

    public function test_instructor_gets_404_if_profile_missing(): void
    {
        $response = $this->actingAs($this->instructor)->getJson('/api/instructor/profile');
        
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
    }

    public function test_instructor_can_create_profile_via_patch(): void
    {
        $payload = [
            'bio' => 'New Bio',
            'expertise' => 'PHP, Laravel',
            'experience_years' => 5,
            'level' => 'Senior',
        ];

        $response = $this->actingAs($this->instructor)->patchJson('/api/instructor/profile', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'user_id' => $this->instructor->id,
                'bio' => 'New Bio',
                'expertise' => 'PHP, Laravel',
                'experience_years' => 5,
                'level' => 'Senior',
            ],
        ]);

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructor->id,
            'bio' => 'New Bio',
        ]);
    }

    public function test_instructor_can_update_own_profile(): void
    {
        InstructorProfile::factory()->create([
            'user_id' => $this->instructor->id,
            'bio' => 'Old Bio',
        ]);

        $response = $this->actingAs($this->instructor)->patchJson('/api/instructor/profile', [
            'bio' => 'Updated Bio',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.bio', 'Updated Bio');

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructor->id,
            'bio' => 'Updated Bio',
        ]);
    }

    public function test_validation_rejects_invalid_experience_years(): void
    {
        $response = $this->actingAs($this->instructor)->patchJson('/api/instructor/profile', [
            'experience_years' => -5,
        ]);
        $response->assertStatus(422);
        
        $response = $this->actingAs($this->instructor)->patchJson('/api/instructor/profile', [
            'experience_years' => 'abc',
        ]);
        $response->assertStatus(422);

        $response = $this->actingAs($this->instructor)->patchJson('/api/instructor/profile', [
            'experience_years' => 100,
        ]);
        $response->assertStatus(422);
    }

    public function test_payload_with_only_unknown_fields_rejected(): void
    {
        $response = $this->actingAs($this->instructor)->patchJson('/api/instructor/profile', [
            'avatar' => 'http://example.com/avatar.png',
            'social_links' => 'https://facebook.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payload']);
    }

    public function test_response_hides_sensitive_fields(): void
    {
        InstructorProfile::factory()->create([
            'user_id' => $this->instructor->id,
        ]);

        $response = $this->actingAs($this->instructor)->getJson('/api/instructor/profile');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayNotHasKey('password_hash', $data['user']);
        $this->assertArrayNotHasKey('password_reset', $data['user']);
        $this->assertArrayNotHasKey('remember_token', $data['user']);
    }
}
