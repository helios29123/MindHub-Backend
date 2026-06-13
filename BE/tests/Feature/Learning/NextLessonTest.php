<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\CourseSection;
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
    Lesson::where('title', 'like', 'Test Lesson%')->forceDelete();
    CourseSection::where('title', 'like', 'Test Section%')->forceDelete();
    Course::where('title', 'like', 'Test Course%')->forceDelete();
});

test('unauthenticated user cannot get next lesson', function () {
    $response = $this->getJson('/api/learn/lessons/1/next');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from getting next lesson', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/1/next', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner with no enrollment is blocked from getting next lesson', function () {
    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Test Course ' . $uniqueId,
        'slug' => 'test-course-' . $uniqueId,
        'price' => 100000,
        'status' => 'published',
    ]);

    $section = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Test Section ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section->id,
        'title' => 'Test Lesson ' . $uniqueId,
        'slug' => 'test-lesson-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
    ]);

    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson("/api/learn/lessons/{$lesson->id}/next", $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);
});

test('getting next lesson for non-existent lesson returns 404', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/99999/next', $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('getting next lesson of unpublished course or lesson returns 403', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Test Course Unpublished ' . $uniqueId,
        'slug' => 'test-course-unpub-' . $uniqueId,
        'price' => 100000,
        'status' => 'draft',
    ]);

    $section = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Test Section ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section->id,
        'title' => 'Test Lesson ' . $uniqueId,
        'slug' => 'test-lesson-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
    ]);

    $order = Order::create([
        'course_id' => $course->id,
        'user_id' => 4, // learner1
        'order_code' => 'TEST-ORDER-NEXT-' . $uniqueId,
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

    $response = $this->getJson("/api/learn/lessons/{$lesson->id}/next", $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
});

test('invalid ID path parameter (0) returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/lessons/0/next', $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});

test('successfully suggests next lesson in same section', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Test Course Success ' . $uniqueId,
        'slug' => 'test-course-success-' . $uniqueId,
        'price' => 100000,
        'status' => 'published',
    ]);

    $section = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Test Section ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $lesson1 = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section->id,
        'title' => 'Test Lesson 1 ' . $uniqueId,
        'slug' => 'test-lesson-1-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
        'sort_order' => 1,
    ]);

    $lesson2 = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section->id,
        'title' => 'Test Lesson 2 ' . $uniqueId,
        'slug' => 'test-lesson-2-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
        'sort_order' => 2,
    ]);

    $order = Order::create([
        'course_id' => $course->id,
        'user_id' => 4, // learner1
        'order_code' => 'TEST-ORDER-NEXT-SUC1-' . $uniqueId,
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

    $response = $this->getJson("/api/learn/lessons/{$lesson1->id}/next", $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'id' => $lesson2->id,
                'title' => $lesson2->title,
                'slug' => $lesson2->slug,
            ]
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
});

test('successfully suggests next lesson from next section', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Test Course Next Sec ' . $uniqueId,
        'slug' => 'test-course-nextsec-' . $uniqueId,
        'price' => 100000,
        'status' => 'published',
    ]);

    $section1 = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Test Section 1 ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $section2 = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Test Section 2 ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 2,
    ]);

    $lesson1 = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section1->id,
        'title' => 'Test Lesson 1 ' . $uniqueId,
        'slug' => 'test-lesson-1-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
        'sort_order' => 1,
    ]);

    $lesson2 = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section2->id,
        'title' => 'Test Lesson 2 ' . $uniqueId,
        'slug' => 'test-lesson-2-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
        'sort_order' => 1,
    ]);

    $order = Order::create([
        'course_id' => $course->id,
        'user_id' => 4, // learner1
        'order_code' => 'TEST-ORDER-NEXT-SUC2-' . $uniqueId,
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

    $response = $this->getJson("/api/learn/lessons/{$lesson1->id}/next", $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'id' => $lesson2->id,
                'title' => $lesson2->title,
                'slug' => $lesson2->slug,
            ]
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
});

test('returns null if there are no more lessons', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Test Course Last ' . $uniqueId,
        'slug' => 'test-course-last-' . $uniqueId,
        'price' => 100000,
        'status' => 'published',
    ]);

    $section = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Test Section ' . $uniqueId,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    $lesson = Lesson::create([
        'course_id' => $course->id,
        'course_section_id' => $section->id,
        'title' => 'Test Lesson Last ' . $uniqueId,
        'slug' => 'test-lesson-last-' . $uniqueId,
        'status' => 'published',
        'lesson_type' => 'video',
        'sort_order' => 1,
    ]);

    $order = Order::create([
        'course_id' => $course->id,
        'user_id' => 4, // learner1
        'order_code' => 'TEST-ORDER-NEXT-SUC3-' . $uniqueId,
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

    $response = $this->getJson("/api/learn/lessons/{$lesson->id}/next", $headers);

    $response->assertStatus(200)
        ->assertExactJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => null
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
});
