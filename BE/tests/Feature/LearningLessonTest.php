<?php

use App\Models\User;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\VideoProgress;
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
    // Cleanup lesson progress and video progress created during testing for user 4/5
    LessonProgress::whereIn('user_id', [4, 5])->whereIn('lesson_id', [1, 2, 3, 5, 8])->delete();
});

test('unauthenticated user cannot fetch lesson details', function () {
    $response = $this->getJson('/api/learn/lessons/1');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from fetching lesson details', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/1', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner with no enrollment is blocked from fetching lesson details', function () {
    // learner2@mindhub.test is not enrolled in course 1
    $headers = getAuthHeadersForUser('learner2@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/1', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);
});

test('fetching non-existent lesson returns 404', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/999', $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('fetching non-published lesson returns 403', function () {
    // Lesson 5 is hidden in course 1
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/5', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);
});

test('invalid non-numeric ID path parameter returns 404 due to route pattern', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/abc', $headers);

    $response->assertStatus(404);
});

test('enrolled learner can fetch lesson details and progress is initialized', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Verify progress doesn't exist yet
    $this->assertFalse(LessonProgress::where('user_id', 4)->where('lesson_id', 1)->exists());

    // Fetch lesson 1 (published video lesson in course 1)
    $response = $this->getJson('/api/learn/lessons/1', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'course' => [
                    'id',
                    'title',
                    'slug'
                ],
                'lesson' => [
                    'id',
                    'course_id',
                    'course_section_id',
                    'title',
                    'slug',
                    'lesson_type',
                    'content',
                    'video_url',
                    'video_duration_seconds',
                    'is_preview',
                    'status',
                    'sort_order',
                    'assets' => [
                        '*' => [
                            'id',
                            'title',
                            'file_url',
                            'file_name',
                            'file_type',
                            'file_size',
                            'note'
                        ]
                    ]
                ],
                'progress' => [
                    'status',
                    'started_at',
                    'completed_at',
                    'learning_duration_seconds',
                    'last_accessed_at',
                    'current_second'
                ]
            ]
        ]);

    $data = $response->json('data');
    $this->assertEquals(1, $data['course']['id']);
    $this->assertEquals(1, $data['lesson']['id']);
    $this->assertEquals('in_progress', $data['progress']['status']);
    $this->assertNotNull($data['progress']['started_at']);
    $this->assertNotNull($data['progress']['last_accessed_at']);

    // Check database
    $this->assertTrue(LessonProgress::where('user_id', 4)->where('lesson_id', 1)->exists());
});

test('re-opening lesson updates last_accessed_at but preserves started_at', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');
    
    // Create initial progress in the past (truncate microseconds)
    $pastTime = Carbon::now()->subHours(2)->microsecond(0);
    $initialProgress = LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 1,
        'status' => 'in_progress',
        'started_at' => $pastTime,
        'last_accessed_at' => $pastTime,
        'learning_duration_seconds' => 120,
    ]);

    // Open lesson again
    $response = $this->getJson('/api/learn/lessons/1', $headers);
    $response->assertStatus(200);

    $data = $response->json('data');
    
    $this->assertEquals($pastTime->toISOString(), $data['progress']['started_at']);
    $this->assertNotEquals($pastTime->toISOString(), $data['progress']['last_accessed_at']);
    $this->assertEquals(120, $data['progress']['learning_duration_seconds']);
});

test('video lesson progress returns the saved current_second from video_progress', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Set mock video progress using updateOrCreate to avoid duplicate entry error
    VideoProgress::updateOrCreate(
        ['user_id' => 4, 'lesson_id' => 1],
        ['current_second' => 320]
    );

    $response = $this->getJson('/api/learn/lessons/1', $headers);
    $response->assertStatus(200);

    $data = $response->json('data');
    $this->assertEquals(320, $data['progress']['current_second']);
    
    // Restore seed value
    VideoProgress::updateOrCreate(
        ['user_id' => 4, 'lesson_id' => 1],
        ['current_second' => 600]
    );
});
