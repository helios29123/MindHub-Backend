<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuizAttemptResultTest extends TestCase
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
        
        // Dynamically create tables for testing since migrations don't exist
        if (!\Schema::hasTable('quizzes')) {
            \Schema::create('quizzes', function ($table) {
                $table->id();
                $table->foreignId('course_id');
                $table->foreignId('lesson_id')->nullable();
                $table->string('title');
                $table->string('status');
                $table->float('passing_score')->default(0);
                $table->timestamps();
            });
        }

        if (!\Schema::hasTable('quiz_questions')) {
            \Schema::create('quiz_questions', function ($table) {
                $table->id();
                $table->foreignId('quiz_id');
                $table->string('question_type');
                $table->text('question_text');
                $table->float('score')->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!\Schema::hasTable('quiz_options')) {
            \Schema::create('quiz_options', function ($table) {
                $table->id();
                $table->foreignId('question_id');
                $table->text('option_text')->nullable();
                $table->boolean('is_correct')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!\Schema::hasTable('quiz_attempts')) {
            \Schema::create('quiz_attempts', function ($table) {
                $table->id();
                $table->foreignId('quiz_id');
                $table->foreignId('user_id');
                $table->integer('attempt_number');
                $table->float('score')->default(0);
                $table->float('total_score')->default(0);
                $table->boolean('passed')->default(false);
                $table->string('status');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
            });
        }

        if (!\Schema::hasTable('quiz_attempt_answers')) {
            \Schema::create('quiz_attempt_answers', function ($table) {
                $table->id();
                $table->foreignId('attempt_id');
                $table->foreignId('question_id');
                $table->foreignId('option_id');
                $table->boolean('is_correct')->default(false);
                $table->float('score_earned')->default(0);
                $table->timestamps();
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

    private function createUser($role = 'learner')
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
        $response = $this->getJson('/api/quiz-attempts/1');
        $response->assertStatus(401);
    }

    public function test_admin_instructor_cannot_access()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/quiz-attempts/1');
        $response->assertStatus(403);

        $instructor = $this->createUser('instructor');
        $token2 = $this->generateAuthToken($instructor);
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/quiz-attempts/1');
        $response2->assertStatus(403);
    }

    public function test_learner_can_view_own_attempt()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);

        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'status' => 'published',
            'price' => 1000,
            'instructor_id' => 1
        ]);

        $quizId = DB::table('quizzes')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Test Quiz',
            'status' => 'published',
            'passing_score' => 50,
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $learner->id,
            'course_id' => $courseId,
            'order_code' => 'ORD-TEST-456',
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

        $attemptId = DB::table('quiz_attempts')->insertGetId([
            'quiz_id' => $quizId,
            'user_id' => $learner->id,
            'attempt_number' => 1,
            'score' => 80,
            'total_score' => 100,
            'passed' => true,
            'status' => 'submitted',
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $questionId = DB::table('quiz_questions')->insertGetId([
            'quiz_id' => $quizId,
            'question_type' => 'single_choice',
            'question_text' => 'Test Question',
            'score' => 10,
            'sort_order' => 1,
        ]);

        $optionId = DB::table('quiz_options')->insertGetId([
            'question_id' => $questionId,
            'option_text' => 'Correct Option',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        DB::table('quiz_attempt_answers')->insert([
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'option_id' => $optionId,
            'is_correct' => true,
            'score_earned' => 10,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/quiz-attempts/{$attemptId}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'attempt_id',
                    'quiz_id',
                    'quiz_title',
                    'attempt_number',
                    'score',
                    'total_score',
                    'passed',
                    'status',
                    'started_at',
                    'submitted_at',
                    'answers' => [
                        '*' => [
                            'question_id',
                            'question_text',
                            'question_type',
                            'selected_option_id',
                            'is_correct',
                            'score_earned',
                            'correct_option_id',
                            'options'
                        ]
                    ]
                ]
            ]);

        $this->assertEquals(80, $response->json('data.score'));
        $this->assertEquals($optionId, $response->json('data.answers.0.correct_option_id'));
    }

    public function test_learner_cannot_view_others_attempt()
    {
        $learner = $this->createUser('learner');
        $otherLearner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);

        $attemptId = DB::table('quiz_attempts')->insertGetId([
            'quiz_id' => 1,
            'user_id' => $otherLearner->id,
            'attempt_number' => 1,
            'score' => 80,
            'total_score' => 100,
            'passed' => true,
            'status' => 'submitted',
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/quiz-attempts/{$attemptId}");
        
        $response->assertStatus(403);
    }

    public function test_attempt_not_found()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/quiz-attempts/999999');
        
        $response->assertStatus(404);
    }
}
