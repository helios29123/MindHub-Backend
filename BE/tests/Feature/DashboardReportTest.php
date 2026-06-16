<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardReportTest extends TestCase
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

    private function createUser($role = 'admin')
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
        $response = $this->getJson('/api/admin/dashboard');
        $response->assertStatus(401);
    }

    public function test_learner_or_instructor_cannot_access()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_access()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'summary',
                    'revenue',
                    'course_status',
                    'user_status',
                    'recent'
                ]
            ]);
    }

    public function test_date_validation_fails()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard?date_from=2026-06-10&date_to=2026-06-01');
        $response->assertStatus(422);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard?month=13');
        $response2->assertStatus(422);

        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard?year=abc');
        $response3->assertStatus(422);

        $response4 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/dashboard?course_id=999999');
        $response4->assertStatus(422);
    }
}
