<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CourseLearnerTest extends TestCase
{
    use RefreshDatabase;

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
        return $tokenService->createAccessToken($user->id, $session->id)['token'];
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
        $response = $this->getJson('/api/instructor/courses/1/learners');
        $response->assertStatus(401);
    }

    public function test_learner_admin_cannot_access()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/learners');
        $response->assertStatus(403);

        $admin = $this->createUser('admin');
        $token2 = $this->generateAuthToken($admin);
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/instructor/courses/1/learners');
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

        $learner = $this->createUser('learner');
        
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $learner->id,
            'course_id' => $courseId,
            'order_code' => 'ORD-TEST-123',
            'amount' => 1000,
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('enrollments')->insert([
            'course_id' => $courseId,
            'user_id' => $learner->id,
            'order_id' => $orderId,
            'status' => 'active',
            'created_at' => now(),
            'last_accessed_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/instructor/courses/{$courseId}/learners");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'learner_id',
                        'full_name',
                        'email',
                        'learner_status',
                        'enrollment_id',
                        'enrollment_status',
                        'last_accessed_at',
                        'completed_at',
                    ]
                ]
            ]);

        $this->assertCount(1, $response->json('data'));
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
            ->getJson("/api/instructor/courses/{$courseId}/learners");
        
        $response->assertStatus(403);
    }

    public function test_course_not_found()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/999999/learners');
        
        $response->assertStatus(404);
    }

    public function test_filters_validation()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/learners?status=archived');
        $response->assertStatus(422);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/learners?sort_by=password_hash');
        $response2->assertStatus(422);

        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/courses/1/learners?per_page=999');
        $response3->assertStatus(422);
    }
}
