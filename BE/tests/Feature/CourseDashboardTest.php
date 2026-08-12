<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        \Schema::disableForeignKeyConstraints();
        if (!\Schema::hasColumn('sessions', 'refresh_token_hash')) {
            \Schema::dropIfExists('sessions');
            \Schema::create('sessions', function ($table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('refresh_token_hash', 255)->nullable();
                $table->string('device_name', 255)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    private function generateAuthToken(User $user)
    {
        $session = \App\Models\AuthSession::create([
            'user_id' => $user->id,
            'refresh_token_hash' => 'dummy',
            'expires_at' => now()->addDays(1),
        ]);
        
        $tokenService = $this->app->make(\App\Services\Auth\AccessTokenService::class);
        return $tokenService->createAccessToken((int) $user->id, (int) $session->id)['token'];
    }

    private function createUser($role = 'instructor')
    {
        $id = DB::table('users')->insertGetId([
            'full_name' => 'User ' . uniqid(),
            'email' => uniqid() . '@example.com',
            'password_hash' => bcrypt('12345678'),
            'role' => $role,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($id);
    }

    public function test_unauthenticated_cannot_access()
    {
        $response = $this->getJson('/api/instructor/courses/1/dashboard');
        $response->assertStatus(401);
    }

    public function test_learner_admin_cannot_access()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/dashboard');
        $this->withoutExceptionHandling(); $response->assertStatus(403);

        $admin = $this->createUser('admin');
        $token2 = $this->generateAuthToken($admin);
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/instructor/courses/1/dashboard');
        $response2->assertStatus(403);
    }

    public function test_instructor_owns_course_can_access()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => 'Test Course',
            'slug' => 'test-course',
            'price' => 1000,
            'status' => 'published'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/instructor/courses/{$courseId}/dashboard");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'course',
                    'summary',
                    'revenue',
                    'enrollment',
                    'activity'
                ]
            ]);
    }

    public function test_instructor_does_not_own_course()
    {
        $instructor = $this->createUser('instructor');
        $otherInstructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $otherInstructor->id,
            'title' => 'Test Course',
            'slug' => 'test-course',
            'price' => 1000,
            'status' => 'published'
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/instructor/courses/{$courseId}/dashboard");
        
        $response->assertStatus(403);
    }

    public function test_course_not_found()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/999999/dashboard');
        
        $response->assertStatus(404);
    }

    public function test_date_filters_validation()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/dashboard?date_from=2026-06-15&date_to=2026-06-01');
        $response->assertStatus(422);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/dashboard?month=13');
        $response2->assertStatus(422);

        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/dashboard?year=abc');
        $response3->assertStatus(422);
    }
}
