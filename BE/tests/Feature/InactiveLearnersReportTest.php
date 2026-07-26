<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class InactiveLearnersReportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::disableForeignKeyConstraints();

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

        if (!\Schema::hasTable('lesson_progress')) {
            \Schema::create('lesson_progress', function ($table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('lesson_id');
                $table->string('status')->nullable();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!\Schema::hasTable('lessons')) {
            \Schema::create('lessons', function ($table) {
                $table->id();
                $table->foreignId('course_id');
                $table->string('title')->nullable();
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

    private function createUser($role)
    {
        return User::create([
            'full_name' => 'Test User',
            'email' => 'test_' . uniqid() . '@mindhub.test',
            'password_hash' => \Hash::make('12345678'),
            'role' => $role,
            'status' => 'active'
        ]);
    }

    public function test_unauthenticated_returns_401()
    {
        $response = $this->getJson('/api/instructor/reports/inactive-learners');
        $response->assertStatus(401);
    }

    public function test_learner_admin_returns_403()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners');
        $response->assertStatus(403);

        $admin = $this->createUser('admin');
        $tokenAdmin = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenAdmin)
            ->getJson('/api/instructor/reports/inactive-learners');
        $response->assertStatus(403);
    }

    public function test_instructor_returns_200()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
    }

    public function test_no_data_returns_empty_items()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data.items');
    }

    public function test_pagination_and_per_page()
    {
        $instructor = $this->createUser('instructor');
        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => 'Test Course',
            'slug' => 'test-course-' . uniqid(),
            'price' => 1000,
            'status' => 'published'
        ]);
        
        for ($i = 0; $i < 6; $i++) {
            $learner = $this->createUser('learner');
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $learner->id,
                'course_id' => $courseId,
                'order_code' => 'ORD-' . uniqid(),
                'status' => 'paid',
                'amount' => 1000,
                'payment_method' => 'vnpay'
            ]);
            
            DB::table('enrollments')->insert([
                'user_id' => $learner->id,
                'course_id' => $courseId,
                'order_id' => $orderId,
                'status' => 'active',
                'enrolled_at' => now()->subDays(20),
                'last_accessed_at' => now()->subDays(15),
            ]);
        }

        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?page=1&per_page=5');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data.items');
        $response->assertJsonPath('meta.total', 6);
        $response->assertJsonPath('meta.per_page', 5);

        $responseInvalid = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?per_page=999');
        $responseInvalid->assertStatus(422);
    }

    public function test_date_filters()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?date_from=2026-06-01&date_to=2026-06-15');
        $response->assertStatus(200);

        $responseInvalid = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?date_from=2026-06-15&date_to=2026-06-01');
        $responseInvalid->assertStatus(422);
    }

    public function test_course_id_ownership()
    {
        $instructor = $this->createUser('instructor');
        $otherInstructor = $this->createUser('instructor');
        
        $course1Id = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => 'Test Course 1',
            'slug' => 'test-course-1-' . uniqid(),
            'price' => 1000,
            'status' => 'published'
        ]);
        
        $course2Id = DB::table('courses')->insertGetId([
            'instructor_id' => $otherInstructor->id,
            'title' => 'Test Course 2',
            'slug' => 'test-course-2-' . uniqid(),
            'price' => 1000,
            'status' => 'published'
        ]);

        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?course_id=' . $course1Id);
        $response->assertStatus(200);

        $responseOther = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?course_id=' . $course2Id);
        $responseOther->assertStatus(403);

        $responseNotFound = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?course_id=99999');
        $responseNotFound->assertStatus(422);
    }

    public function test_month_year_filters()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?month=6&year=2026');
        $response->assertStatus(200);

        $responseInvalid = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?month=13');
        $responseInvalid->assertStatus(422);
    }

    public function test_inactive_days_filters()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?inactive_days=14');
        $response->assertStatus(200);

        $responseInvalid = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?inactive_days=0');
        $responseInvalid->assertStatus(422);
        
        $responseInvalid2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?inactive_days=9999');
        $responseInvalid2->assertStatus(422);
    }

    public function test_status_and_sorting()
    {
        $instructor = $this->createUser('instructor');
        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?status=active');
        $response->assertStatus(200);

        $responseInvalid = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?status=archived');
        $responseInvalid->assertStatus(422);

        $responseSort = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?sort_by=inactive_days&sort_direction=desc');
        $responseSort->assertStatus(200);

        $responseInvalidSort = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?sort_by=password_hash');
        $responseInvalidSort->assertStatus(422);
    }

    public function test_response_does_not_contain_sensitive_info()
    {
        $instructor = $this->createUser('instructor');
        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => 'Test Course 1',
            'slug' => 'test-course-1-' . uniqid(),
            'price' => 1000,
            'status' => 'published'
        ]);
        $learner = $this->createUser('learner');
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $learner->id,
            'course_id' => $courseId,
            'order_code' => 'ORD-' . uniqid(),
            'status' => 'paid',
            'amount' => 1000,
            'payment_method' => 'vnpay'
        ]);
        
        DB::table('enrollments')->insert([
            'user_id' => $learner->id,
            'course_id' => $courseId,
            'order_id' => $orderId,
            'status' => 'active',
            'enrolled_at' => now()->subDays(20),
            'last_accessed_at' => now()->subDays(15),
        ]);

        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.items');
        
        $item = $response->json('data.items.0');
        $this->assertArrayNotHasKey('password_hash', $item);
        $this->assertArrayNotHasKey('password_reset', $item);
        $this->assertArrayNotHasKey('refresh_token_hash', $item);
        $this->assertArrayHasKey('learner_id', $item);
        $this->assertArrayHasKey('full_name', $item);
        $this->assertArrayHasKey('email', $item);
        $this->assertArrayHasKey('course_id', $item);
    }

    public function test_business_logic_inactive_learners()
    {
        $instructor = $this->createUser('instructor');
        
        $courseId = DB::table('courses')->insertGetId([
            'instructor_id' => $instructor->id,
            'title' => 'Test Course 1',
            'slug' => 'test-course-1-' . uniqid(),
            'price' => 1000,
            'status' => 'published'
        ]);
        
        $lessonId = DB::table('lessons')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Lesson 1'
        ]);
        
        $learnerInactive = $this->createUser('learner');
        $learnerActive = $this->createUser('learner');
        $learnerCompleted = $this->createUser('learner');

        $order1 = DB::table('orders')->insertGetId(['user_id' => $learnerInactive->id, 'course_id' => $courseId, 'order_code' => 'O1', 'status' => 'paid', 'amount' => 1000, 'payment_method' => 'vnpay']);
        $order2 = DB::table('orders')->insertGetId(['user_id' => $learnerActive->id, 'course_id' => $courseId, 'order_code' => 'O2', 'status' => 'paid', 'amount' => 1000, 'payment_method' => 'vnpay']);
        $order3 = DB::table('orders')->insertGetId(['user_id' => $learnerCompleted->id, 'course_id' => $courseId, 'order_code' => 'O3', 'status' => 'paid', 'amount' => 1000, 'payment_method' => 'vnpay']);

        // Inactive learner (last activity 20 days ago)
        DB::table('enrollments')->insert([
            'user_id' => $learnerInactive->id,
            'course_id' => $courseId,
            'order_id' => $order1,
            'status' => 'active',
            'enrolled_at' => now()->subDays(30),
            'last_accessed_at' => now()->subDays(20),
            'completed_at' => null,
        ]);
        DB::table('lesson_progress')->insert([
            'user_id' => $learnerInactive->id,
            'lesson_id' => $lessonId,
            'last_accessed_at' => now()->subDays(20),
        ]);

        // Active learner (last activity 2 days ago)
        DB::table('enrollments')->insert([
            'user_id' => $learnerActive->id,
            'course_id' => $courseId,
            'order_id' => $order2,
            'status' => 'active',
            'enrolled_at' => now()->subDays(30),
            'last_accessed_at' => now()->subDays(2),
            'completed_at' => null,
        ]);
        DB::table('lesson_progress')->insert([
            'user_id' => $learnerActive->id,
            'lesson_id' => $lessonId,
            'last_accessed_at' => now()->subDays(2),
        ]);

        // Completed learner (should not be in report regardless of activity date)
        DB::table('enrollments')->insert([
            'user_id' => $learnerCompleted->id,
            'course_id' => $courseId,
            'order_id' => $order3,
            'status' => 'completed',
            'enrolled_at' => now()->subDays(30),
            'last_accessed_at' => now()->subDays(20),
            'completed_at' => now()->subDays(18),
        ]);

        $token = $this->generateAuthToken($instructor);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/instructor/reports/inactive-learners?inactive_days=14');

        $response->assertStatus(200);
        $data = $response->json('data.items');
        
        // Should only return the inactive learner (not active, not completed)
        $this->assertCount(1, $data);
        $this->assertEquals($learnerInactive->id, $data[0]['learner_id']);
        $this->assertGreaterThanOrEqual(14, $data[0]['inactive_days']);
    }
}
