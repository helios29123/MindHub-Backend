<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RevenueReportTest extends TestCase
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

        if (!\Schema::hasTable('orders')) {
            \Schema::create('orders', function ($table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('course_id');
                $table->string('order_code')->nullable();
                $table->string('status')->nullable();
                $table->string('payment_status')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('payment_method')->nullable();
                $table->timestamps();
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
        $response = $this->getJson('/api/admin/reports/revenue');
        $response->assertStatus(401);
    }

    public function test_learner_or_instructor_cannot_access()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue');
        $response->assertStatus(403);
    }

    public function test_admin_can_access()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'summary',
                    'items',
                ]
            ]);
    }

    public function test_validation_fails()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?date_from=2026-06-10&date_to=2026-06-01');
        $response->assertStatus(422);

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?month=13');
        $response2->assertStatus(422);

        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?year=abc');
        $response3->assertStatus(422);

        $response4 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?course_id=999999');
        $response4->assertStatus(422);

        $response5 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?instructor_id=999999');
        $response5->assertStatus(422);

        $response6 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?group_by=week');
        $response6->assertStatus(422);

        $response7 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?sort_by=password_hash');
        $response7->assertStatus(422);
    }

    public function test_business_logic_group_by_day()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $instructor = $this->createUser('instructor');
        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => 'Test Course',
            'slug' => 'test-course-' . uniqid(),
            'price' => 1000,
            'status' => 'published'
        ]);

        $learner = $this->createUser('learner');

        DB::table('orders')->insert([
            'user_id' => $learner->id,
            'course_id' => $courseId,
            'order_code' => 'ORD-1',
            'status' => 'paid',
            'paid_at' => '2026-06-15 10:00:00',
            'amount' => 1000,
        ]);

        DB::table('orders')->insert([
            'user_id' => $learner->id,
            'course_id' => $courseId,
            'order_code' => 'ORD-2',
            'status' => 'paid',
            'paid_at' => '2026-06-15 12:00:00',
            'amount' => 500,
        ]);

        DB::table('orders')->insert([
            'user_id' => $learner->id,
            'course_id' => $courseId,
            'order_code' => 'ORD-3',
            'status' => 'paid',
            'paid_at' => '2026-06-16 10:00:00',
            'amount' => 2000,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/reports/revenue?group_by=day&sort_by=date&sort_direction=asc');
        
        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertEquals(3500, $data['summary']['total_gross_amount']);
        $this->assertEquals(3, $data['summary']['order_count']);
        
        // 2026-06-15 items should be 1500
        $this->assertEquals('2026-06-15', $data['items'][0]['period']);
        $this->assertEquals(1500, $data['items'][0]['gross_amount']);
        
        $this->assertEquals('2026-06-16', $data['items'][1]['period']);
        $this->assertEquals(2000, $data['items'][1]['gross_amount']);

        // Check no sensitive data
        $this->assertArrayNotHasKey('password_hash', $data['items'][0]);
    }
}
