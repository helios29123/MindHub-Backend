<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TopCoursesReportTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $learner;
    private string $adminToken;
    private string $learnerToken;

    protected function setUp(): void
    {
        parent::setUp();
        
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

        $this->admin = User::create([
            'full_name' => 'Admin Test',
            'email' => 'admin_test_' . uniqid() . '@mindhub.test',
            'password_hash' => \Hash::make('12345678'),
            'role' => 'admin',
            'status' => 'active'
        ]);
        $this->learner = User::create([
            'full_name' => 'Learner Test',
            'email' => 'learner_test_' . uniqid() . '@mindhub.test',
            'password_hash' => \Hash::make('12345678'),
            'role' => 'learner',
            'status' => 'active'
        ]);

        $adminSession = \App\Models\Session::create([
            'user_id' => $this->admin->id,
            'refresh_token_hash' => 'dummy_' . uniqid(),
            'expires_at' => now()->addDays(1),
        ]);
        $learnerSession = \App\Models\Session::create([
            'user_id' => $this->learner->id,
            'refresh_token_hash' => 'dummy_' . uniqid(),
            'expires_at' => now()->addDays(1),
        ]);

        $tokenService = $this->app->make(\App\Services\Auth\AccessTokenService::class);
        $this->adminToken = $tokenService->createAccessToken($this->admin->id, $adminSession->id)['token'];
        $this->learnerToken = $tokenService->createAccessToken($this->learner->id, $learnerSession->id)['token'];
    }

    public function test_unauthenticated_returns_401()
    {
        $response = $this->getJson('/api/admin/reports/top-courses');
        $response->assertStatus(401);
    }

    public function test_learner_returns_403()
    {
        $response = $this->withToken($this->learnerToken)->getJson('/api/admin/reports/top-courses');
        $response->assertStatus(403);
    }

    public function test_admin_returns_200()
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/admin/reports/top-courses');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['summary', 'items'], 'meta']);
    }

    public function test_no_data_returns_empty_items_and_0_metrics()
    {
        $course = Course::create([
            'title' => "Course 999",
            'slug' => "course-999",
            'price' => 100,
            'status' => 'published',
            'instructor_id' => $this->admin->id,
        ]);
        $response = $this->withToken($this->adminToken)->getJson('/api/admin/reports/top-courses?course_id=' . $course->id . '&date_from=2030-01-01');
        $response->assertStatus(200);
        $items = $response->json('data.items');
        
        // Due to the business logic filtering out courses with trending_score = 0, this should be empty.
        $this->assertCount(0, $items);
    }

    public function test_pagination_returns_correct_meta()
    {
        for ($i = 0; $i < 10; $i++) {
            $course = Course::create([
                'title' => "Course $i",
                'slug' => "course-$i",
                'price' => 100,
                'status' => 'published',
                'instructor_id' => $this->admin->id, // dummy instructor
            ]);
            
            // To appear in the Top Courses report without a date filter, a course needs >= 10 enrollments
            for ($j = 0; $j < 10; $j++) {
                $dummyUser = \App\Models\User::create([
                    'full_name' => "Dummy $i $j",
                    'email' => "dummy_$i" . "_$j@mindhub.test",
                    'password_hash' => \Hash::make('12345678'),
                    'role' => 'learner',
                    'status' => 'active'
                ]);
                $orderId = \Illuminate\Support\Facades\DB::table('orders')->insertGetId([
                    'user_id' => $dummyUser->id,
                    'course_id' => $course->id,
                    'order_code' => 'TEST-ORD-' . uniqid(),
                    'amount' => 100,
                    'status' => 'paid',
                    'payment_status' => 'paid',
                ]);
                
                \Illuminate\Support\Facades\DB::table('enrollments')->insert([
                    'course_id' => $course->id,
                    'user_id' => $dummyUser->id,
                    'order_id' => $orderId,
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);
            }
        }
        $response = $this->withToken($this->adminToken)->getJson('/api/admin/reports/top-courses?page=1&per_page=5');
        $response->assertStatus(200);
        $this->assertEquals(5, count($response->json('data.items')));
        $this->assertEquals(5, $response->json('meta.per_page'));
    }

    public function test_per_page_over_100_returns_422()
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/admin/reports/top-courses?per_page=999');
        $response->assertStatus(422);
    }

    public function test_invalid_date_range_returns_422()
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/admin/reports/top-courses?date_from=2030-01-01&date_to=2020-01-01');
        $response->assertStatus(422);
    }

    public function test_invalid_sort_field_returns_422()
    {
        $response = $this->withToken($this->adminToken)->getJson('/api/admin/reports/top-courses?sort_by=password_hash');
        $response->assertStatus(422);
    }

    public function test_response_does_not_contain_sensitive_info()
    {
        Course::create([
            'title' => "Course 1",
            'slug' => "course-1",
            'price' => 100,
            'status' => 'published',
            'instructor_id' => $this->admin->id,
        ]);
        $response = $this->withToken($this->adminToken)->getJson('/api/admin/reports/top-courses');
        $response->assertStatus(200);
        
        $json = $response->getContent();
        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString('token', $json);
    }
}
