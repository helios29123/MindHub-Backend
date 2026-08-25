<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstructorRevenueTest extends TestCase
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
        $session = \App\Models\Session::create([
            'user_id' => $user->id,
            'refresh_token_hash' => 'dummy_' . uniqid(),
            'expires_at' => now()->addDays(1),
        ]);

        $tokenService = $this->app->make(\App\Services\Auth\AccessTokenService::class);
        return $tokenService->createAccessToken($user->id, $session->id)['token'];
    }

    private function createUser($role = 'instructor')
    {
        return User::create([
            'name' => 'Revenue User ' . uniqid(),
            'full_name' => 'Revenue User Test',
            'email' => 'revenue_' . uniqid() . '@mindhub.test',
            'password_hash' => bcrypt('password'),
            'role' => $role,
            'status' => 'active',
        ]);
    }

    public function test_instructor_can_fetch_revenue_summary()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => 'Test Course Revenue',
            'slug' => 'test-course-revenue-' . uniqid(),
            'price' => 500000,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $instructor->id,
            'course_id' => $courseId,
            'order_code' => 'ORD-TEST-' . uniqid(),
            'amount' => 500000,
            'status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('revenues')->insert([
            'instructor_id' => $instructor->id,
            'course_id' => $courseId,
            'order_id' => $orderId,
            'gross_amount' => 500000,
            'instructor_amount' => 400000,
            'platform_fee_amount' => 100000,
            'instructor_percent' => 80,
            'platform_percent' => 20,
            'status' => 'available',
            'earned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/revenues/summary?preset=month');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.gross_revenue', 500000)
            ->assertJsonPath('data.instructor_revenue', 400000)
            ->assertJsonPath('data.platform_fee', 100000);
    }

    public function test_instructor_can_fetch_revenue_details_and_export()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $resDetails = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/revenues/details?page=1&per_page=5');

        $resDetails->assertStatus(200)
            ->assertJsonPath('success', true);

        $resExport = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/instructor/revenues/export');

        $resExport->assertStatus(200);
        $resExport->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
