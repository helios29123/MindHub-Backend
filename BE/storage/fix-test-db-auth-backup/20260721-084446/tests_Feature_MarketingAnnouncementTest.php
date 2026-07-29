<?php

use App\Models\User;
use App\Models\Course;

function getAuthHeadersForMarketingTest(string $email): array
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

test('instructor can request course announcement for owned course and receive 501 mock response', function () {
    $headers = getAuthHeadersForMarketingTest('instructor1@mindhub.test');

    // Course 1 is owned by Instructor 1 (user ID 2)
    $response = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 1,
        'title' => 'Thông báo mới về khóa học Laravel',
        'content' => 'Chào mọi người, bài tập mới đã được cập nhật.',
    ], $headers);

    $response->assertStatus(501)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => json_encode(['banner_id' => 1, 'status' => 'active']),
        ]);
});

test('unauthenticated users cannot access course announcements', function () {
    $response = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 1,
        'title' => 'Thông báo test',
        'content' => 'Nội dung test',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized roles (learner) cannot access course announcements', function () {
    $headers = getAuthHeadersForMarketingTest('learner1@mindhub.test');

    $response = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 1,
        'title' => 'Thông báo test',
        'content' => 'Nội dung test',
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('instructor cannot request announcement for course they do not own', function () {
    // instructor2@mindhub.test (user ID 3) does not own course 1 (owned by instructor 1, user ID 2)
    $headers = getAuthHeadersForMarketingTest('instructor2@mindhub.test');

    $response = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 1,
        'title' => 'Thông báo khác',
        'content' => 'Tôi muốn thông báo',
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('validation fails on invalid course announcements inputs', function () {
    $headers = getAuthHeadersForMarketingTest('instructor1@mindhub.test');

    // Missing course_id
    $responseMissingCourse = $this->postJson('/api/instructor/course-announcements', [
        'title' => 'Test',
        'content' => 'Test',
    ], $headers);
    $responseMissingCourse->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);

    // Invalid course_id (non-existent)
    $responseInvalidCourse = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 999,
        'title' => 'Test',
        'content' => 'Test',
    ], $headers);
    $responseInvalidCourse->assertStatus(422);

    // Missing title
    $responseMissingTitle = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 1,
        'content' => 'Test',
    ], $headers);
    $responseMissingTitle->assertStatus(422);

    // Title too long (>255 chars)
    $responseLongTitle = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 1,
        'title' => str_repeat('A', 256),
        'content' => 'Test',
    ], $headers);
    $responseLongTitle->assertStatus(422);

    // Missing content
    $responseMissingContent = $this->postJson('/api/instructor/course-announcements', [
        'course_id' => 1,
        'title' => 'Test',
    ], $headers);
    $responseMissingContent->assertStatus(422);
});
