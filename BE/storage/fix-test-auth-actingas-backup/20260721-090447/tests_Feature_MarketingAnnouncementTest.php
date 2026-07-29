<?php

use App\Models\User;
use App\Models\Course;

function getAuthHeadersForMarketingTest(string $email = 'instructor1@mindhub.test'): array
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        default => 'API Test User',
    };

    $userData = [];

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'name')) {
        $userData['name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'full_name')) {
        $userData['full_name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
        $userData['username'] = str_replace(['@', '.'], '_', $email);
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
        $userData['phone'] = '0900000000';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password')) {
        $userData['password'] = \Illuminate\Support\Facades\Hash::make('password');
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $userData['role'] = $role;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
        $userData['status'] = 'active';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
        $userData['is_active'] = 1;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
        $userData['email_verified_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'updated_at')) {
        $userData['updated_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'created_at')) {
        $userData['created_at'] = now();
    }

    \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(
        ['email' => $email],
        $userData
    );

    $user = \App\Models\User::where('email', $email)->first();

    if (!$user) {
        throw new \RuntimeException("Cannot create test user: {$email}");
    }

    if (
        \Illuminate\Support\Facades\Schema::hasColumn('users', 'password')
        && !\Illuminate\Support\Facades\Hash::check('password', $user->password)
    ) {
        \Illuminate\Support\Facades\DB::table('users')
            ->where('email', $email)
            ->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);
    }

    $response = test()->postJson('/api/auth/login', [
        'email' => $email,
        'password' => 'password',
    ]);

    $response->assertSuccessful();

    $json = $response->json();

    $token = data_get($json, 'data.token')
        ?? data_get($json, 'token')
        ?? data_get($json, 'data.access_token')
        ?? data_get($json, 'access_token');

    $headers = ['Accept' => 'application/json'];

    if ($token) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }

    return $headers;
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
