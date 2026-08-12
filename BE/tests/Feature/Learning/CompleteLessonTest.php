<?php

use App\Models\User;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\VideoProgress;
use App\Models\Enrollment;
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
    
    // Reset enrollment status for user 4 (learner1) on course 1
    Enrollment::where('user_id', 4)->where('course_id', 1)->update([
        'status' => Enrollment::STATUS_ACTIVE,
        'completed_at' => null,
    ]);
});

test('unauthenticated user cannot mark lesson complete', function () {
    $response = $this->patchJson('/api/learn/lessons/1/complete', [
        'completed' => true,
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from marking lesson complete', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/complete', [
        'completed' => true,
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner with no enrollment is blocked from marking lesson complete', function () {
    // learner2@mindhub.test is not enrolled in course 1
    $headers = getAuthHeadersForUser('learner2@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/complete', [
        'completed' => true,
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);
});

test('marking progress on non-existent lesson returns 404', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/999/complete', [
        'completed' => true,
    ], $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('marking progress on non-published lesson returns 403', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Lesson 5 is hidden
    $response = $this->patchJson('/api/learn/lessons/5/complete', [
        'completed' => true,
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);
});

test('marking progress with missing completed parameter returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->patchJson('/api/learn/lessons/1/complete', [], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});

test('successfully marks lesson as completed and unmarks it', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Hitting complete => true
    $response = $this->patchJson('/api/learn/lessons/1/complete', [
        'completed' => true,
    ], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'progress' => [
                    'status' => 'completed',
                ]
            ]
        ]);

    $data = $response->json('data');
    $this->assertNotNull($data['progress']['completed_at']);

    // Check database
    $this->assertTrue(LessonProgress::where('user_id', 4)->where('lesson_id', 1)->where('status', 'completed')->exists());

    // Hitting complete => false
    $response2 = $this->patchJson('/api/learn/lessons/1/complete', [
        'completed' => false,
    ], $headers);

    $response2->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'progress' => [
                    'status' => 'in_progress',
                    'completed_at' => null,
                ]
            ]
        ]);

    // Check database
    $this->assertTrue(LessonProgress::where('user_id', 4)->where('lesson_id', 1)->where('status', 'in_progress')->exists());
});

test('automatically transitions enrollment to completed when all lessons are finished', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Course 1 has published lessons: 1, 2, 3, 4. (Check seeded database: Lesson 5 is hidden, so only 1, 2, 3, 4 are active published lessons in course 1).
    // Let's mark lessons 1, 2, 3 as completed first.
    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 1,
        'status' => 'completed',
        'started_at' => Carbon::now()->subHour(),
        'completed_at' => Carbon::now()->subHour(),
    ]);

    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 2,
        'status' => 'completed',
        'started_at' => Carbon::now()->subHour(),
        'completed_at' => Carbon::now()->subHour(),
    ]);

    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 3,
        'status' => 'completed',
        'started_at' => Carbon::now()->subHour(),
        'completed_at' => Carbon::now()->subHour(),
    ]);

    // Verify enrollment is still active
    $enrollment = Enrollment::where('user_id', 4)->where('course_id', 1)->first();
    $this->assertEquals(Enrollment::STATUS_ACTIVE, $enrollment->status);
    $this->assertNull($enrollment->completed_at);

    // Complete the final published lesson (lesson 4)
    $response = $this->patchJson('/api/learn/lessons/4/complete', [
        'completed' => true,
    ], $headers);

    $response->assertStatus(200);

    // Verify enrollment is now completed
    $enrollment->refresh();
    $this->assertEquals(Enrollment::STATUS_COMPLETED, $enrollment->status);
    $this->assertNotNull($enrollment->completed_at);

    // Unmark completion of lesson 4
    $response2 = $this->patchJson('/api/learn/lessons/4/complete', [
        'completed' => false,
    ], $headers);

    $response2->assertStatus(200);

    // Verify enrollment reverts to active
    $enrollment->refresh();
    $this->assertEquals(Enrollment::STATUS_ACTIVE, $enrollment->status);
    $this->assertNull($enrollment->completed_at);
});
