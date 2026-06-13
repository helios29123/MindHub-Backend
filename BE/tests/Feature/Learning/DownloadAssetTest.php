<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAsset;
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
    // Reset/Cleanup any custom created assets during testing
    LessonAsset::where('title', 'like', 'Test Asset%')->forceDelete();
});

test('unauthenticated user cannot download asset', function () {
    $response = $this->getJson('/api/learn/assets/1/download');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from downloading asset', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->getJson('/api/learn/assets/1/download', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner with no enrollment is blocked from downloading asset', function () {
    // Create a new published course and a lesson and an asset
    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'New Course ' . $uniqueId,
        'slug' => 'new-course-' . $uniqueId,
        'price' => 100000,
        'status' => 'published',
    ]);

    $section = \App\Models\CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Section ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section->id,
        'title' => 'Lesson ' . $uniqueId,
        'slug' => 'lesson-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
    ]);

    $asset = LessonAsset::create([
        'lesson_id' => $lesson->id,
        'title' => 'Test Asset Enrollment Check ' . $uniqueId,
        'file_url' => 'https://test.com/file.pdf',
        'file_name' => 'file.pdf',
        'file_type' => 'pdf',
        'file_size' => 1024,
    ]);

    // learner1 has no enrollment in this course
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson("/api/learn/assets/{$asset->id}/download", $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);

    // Cleanup
    $asset->forceDelete();
    $lesson->forceDelete();
    $section->forceDelete();
    $course->forceDelete();
});

test('downloading non-existent asset returns 404', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/assets/999/download', $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('downloading asset of non-published course returns 403', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Draft Course ' . $uniqueId,
        'slug' => 'draft-course-' . $uniqueId,
        'price' => 100000,
        'status' => 'draft', // unpublished
    ]);

    $section = \App\Models\CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Section ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section->id,
        'title' => 'Lesson ' . $uniqueId,
        'slug' => 'lesson-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
    ]);

    $asset = LessonAsset::create([
        'lesson_id' => $lesson->id,
        'title' => 'Test Asset Status Check ' . $uniqueId,
        'file_url' => 'https://test.com/file.pdf',
        'file_name' => 'file.pdf',
        'file_type' => 'pdf',
        'file_size' => 1024,
    ]);

    $order = Order::create([
        'course_id' => $course->id,
        'user_id' => 4, // learner1
        'order_code' => 'TEST-ORDER-DRAFT-' . $uniqueId,
        'status' => Order::STATUS_PAID,
        'price_snapshot' => 100000,
        'payment_method' => 'bank_transfer',
        'amount' => 100000,
        'payment_status' => Order::PAYMENT_PAID,
    ]);

    $enrollment = Enrollment::create([
        'user_id' => 4,
        'course_id' => $course->id,
        'order_id' => $order->id,
        'status' => Enrollment::STATUS_ACTIVE,
        'enrolled_at' => Carbon::now(),
    ]);

    $response = $this->getJson("/api/learn/assets/{$asset->id}/download", $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
    $asset->forceDelete();
    $lesson->forceDelete();
    $section->forceDelete();
    $course->forceDelete();
});

test('invalid ID path parameter (0) returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/assets/0/download', $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});

test('successfully retrieves asset download details when user is enrolled', function () {
    // learner1 is enrolled in course 1, lesson 1
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Create an asset for lesson 1 (in course 1, which is published)
    $asset = LessonAsset::create([
        'lesson_id' => 1,
        'title' => 'Test Asset Success Link',
        'file_url' => 'https://test.com/file_success.pdf',
        'file_name' => 'file_success.pdf',
        'file_type' => 'pdf',
        'file_size' => 2048,
        'note' => 'Tài liệu hướng dẫn',
    ]);

    $response = $this->getJson("/api/learn/assets/{$asset->id}/download", $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'id' => $asset->id,
                'lesson_id' => 1,
                'title' => 'Test Asset Success Link',
                'file_name' => 'file_success.pdf',
                'file_type' => 'pdf',
                'file_size' => 2048,
                'file_url' => 'https://test.com/file_success.pdf',
                'note' => 'Tài liệu hướng dẫn',
            ]
        ]);
});
