<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\VideoProgress;
use App\Models\Enrollment;
use App\Models\Order;
use Carbon\Carbon;

if (!function_exists('getAuthHeadersForUser')) {
    function getAuthHeadersForUser(string $email): array
    {
        $response = test()->postJson('/api/auth/login', [
            'email' => $email,
            'password' => '12345678',
            'device_name' => 'testing'
        ]);
        
        $token = $response->json('data.access_token');
        return [
            'Authorization' => "Bearer $token",
        ];
    }
}

afterEach(function () {
    // Cleanup progress created during testing for user 4/5
    LessonProgress::whereIn('user_id', [4, 5])->delete();
    VideoProgress::whereIn('user_id', [4, 5])->delete();
    
    // Reset enrollment status/progress for user 4 on course 1
    Enrollment::where('user_id', 4)->where('course_id', 1)->update([
        'status' => Enrollment::STATUS_ACTIVE,
        'progress_percent' => 0.00,
        'completed_at' => null,
    ]);
});

test('unauthenticated user cannot fetch learning logs', function () {
    $response = $this->getJson('/api/learning-logs/my');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from fetching learning logs', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->getJson('/api/learning-logs/my', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('successfully retrieves learning logs list with correct keys and values', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Create LessonProgress for user 4 on lesson 1
    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 1,
        'status' => 'in_progress',
        'started_at' => Carbon::now()->subMinutes(10),
        'last_accessed_at' => Carbon::now()->subMinutes(10),
        'learning_duration_seconds' => 120,
    ]);

    // Create LessonProgress for user 4 on lesson 2
    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 2,
        'status' => 'completed',
        'started_at' => Carbon::now()->subMinutes(5),
        'completed_at' => Carbon::now(),
        'last_accessed_at' => Carbon::now(),
        'learning_duration_seconds' => 300,
    ]);

    // Create VideoProgress for lesson 1
    VideoProgress::create([
        'user_id' => 4,
        'lesson_id' => 1,
        'current_second' => 45,
    ]);

    $response = $this->getJson('/api/learning-logs/my', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'course' => [
                        'id',
                        'title',
                        'slug',
                        'thumbnail_url',
                    ],
                    'lesson' => [
                        'id',
                        'title',
                        'slug',
                        'lesson_type',
                        'video_duration_seconds',
                    ],
                    'progress' => [
                        'status',
                        'started_at',
                        'completed_at',
                        'learning_duration_seconds',
                        'last_accessed_at',
                        'current_second',
                    ],
                ]
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ]
        ]);

    $data = $response->json('data');
    // It should be sorted by last_accessed_at DESC, so lesson 2 (last_accessed_at now) is first
    $this->assertCount(2, $data);
    $this->assertEquals(2, $data[0]['lesson']['id']);
    $this->assertEquals('completed', $data[0]['progress']['status']);
    
    $this->assertEquals(1, $data[1]['lesson']['id']);
    $this->assertEquals('in_progress', $data[1]['progress']['status']);
    $this->assertEquals(45, $data[1]['progress']['current_second']);
});

test('learner can filter learning logs by status', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 1,
        'status' => 'in_progress',
        'last_accessed_at' => Carbon::now()->subMinutes(10),
    ]);

    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 2,
        'status' => 'completed',
        'last_accessed_at' => Carbon::now(),
    ]);

    // Filter status=completed
    $responseCompleted = $this->getJson('/api/learning-logs/my?status=completed', $headers);
    $responseCompleted->assertStatus(200);
    $dataCompleted = $responseCompleted->json('data');
    $this->assertCount(1, $dataCompleted);
    $this->assertEquals('completed', $dataCompleted[0]['progress']['status']);

    // Filter status=in_progress
    $responseInProgress = $this->getJson('/api/learning-logs/my?status=in_progress', $headers);
    $responseInProgress->assertStatus(200);
    $dataInProgress = $responseInProgress->json('data');
    $this->assertCount(1, $dataInProgress);
    $this->assertEquals('in_progress', $dataInProgress[0]['progress']['status']);
});

test('invalid status returns 422 validation error', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learning-logs/my?status=invalid_status', $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'status'
            ]
        ]);
});

test('invalid pagination parameters return 422 validation error', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learning-logs/my?per_page=abc', $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});

test('learner with no enrollments gets an empty list', function () {
    // learner2 has no enrollments on any course
    $headers = getAuthHeadersForUser('learner2@mindhub.test');

    $response = $this->getJson('/api/learning-logs/my', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});
