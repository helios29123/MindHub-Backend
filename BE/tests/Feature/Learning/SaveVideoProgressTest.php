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
    // Cleanup progress created during testing for user 4/5
    LessonProgress::whereIn('user_id', [4, 5])->delete();
    VideoProgress::whereIn('user_id', [4, 5])->delete();
});

test('unauthenticated user cannot save video progress', function () {
    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 10,
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from saving video progress', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 10,
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner with no enrollment is blocked from saving video progress', function () {
    // learner2@mindhub.test is not enrolled in course 1
    $headers = getAuthHeadersForUser('learner2@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 10,
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);
});

test('saving progress on non-existent lesson returns 404', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/999/progress', [
        'current_second' => 10,
    ], $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('saving progress on non-video lesson type returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Lesson 2 is a text lesson
    $response = $this->patchJson('/api/learn/lessons/2/progress', [
        'current_second' => 10,
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Bài học không phải dạng video.',
        ]);
});

test('saving progress on non-published lesson returns 403', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Lesson 5 is hidden
    $response = $this->patchJson('/api/learn/lessons/5/progress', [
        'current_second' => 10,
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);
});

test('saving negative current_second returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => -5,
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});

test('saving current_second exceeding video_duration_seconds returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Lesson 1 video_duration_seconds = 600
    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 601,
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Tiến độ video không hợp lệ.',
        ]);
});

test('saving current_second exceeding duration_second returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 300,
        'duration_second' => 200,
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Tiến độ video không hợp lệ.',
        ]);
});

test('successfully saves video progress and marks lesson as in_progress', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Send valid playhead position
    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 100,
    ], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'course',
                'lesson',
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
    $this->assertEquals('in_progress', $data['progress']['status']);
    $this->assertEquals(100, $data['progress']['current_second']);

    // Check database
    $this->assertTrue(VideoProgress::where('user_id', 4)->where('lesson_id', 1)->where('current_second', 100)->exists());
    $this->assertTrue(LessonProgress::where('user_id', 4)->where('lesson_id', 1)->where('status', 'in_progress')->exists());
});

test('successfully completes lesson when playhead reaches duration limit', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Lesson 1 duration = 600
    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 600,
    ], $headers);

    $response->assertStatus(200);

    $data = $response->json('data');
    $this->assertEquals('completed', $data['progress']['status']);
    $this->assertNotNull($data['progress']['completed_at']);

    // Check database
    $this->assertTrue(LessonProgress::where('user_id', 4)->where('lesson_id', 1)->where('status', 'completed')->exists());
});

test('successfully completes lesson when is_completed parameter is true', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/progress', [
        'current_second' => 50,
        'is_completed' => true,
    ], $headers);

    $response->assertStatus(200);

    $data = $response->json('data');
    $this->assertEquals('completed', $data['progress']['status']);
    $this->assertNotNull($data['progress']['completed_at']);

    // Check database
    $this->assertTrue(LessonProgress::where('user_id', 4)->where('lesson_id', 1)->where('status', 'completed')->exists());
});
