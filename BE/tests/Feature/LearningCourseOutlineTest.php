<?php

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
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
    // Cleanup lesson progress created during testing for user 4/5
    LessonProgress::whereIn('user_id', [4, 5])->delete();
});

test('unauthenticated user cannot fetch course outline', function () {
    $response = $this->getJson('/api/learn/courses/1/outline');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from fetching course outline', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->getJson('/api/learn/courses/1/outline', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner with no enrollment is blocked from fetching course outline', function () {
    // learner2@mindhub.test is not enrolled in course 1
    $headers = getAuthHeadersForUser('learner2@mindhub.test');

    $response = $this->getJson('/api/learn/courses/1/outline', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);
});

test('fetching non-existent course outline returns 404', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/courses/999/outline', $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('fetching draft (non-published) course outline returns 403', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $uniqueId = uniqid();

    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Draft Course ' . $uniqueId,
        'slug' => 'draft-course-' . $uniqueId,
        'price' => 100000,
        'status' => 'draft',
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
        'user_id' => 4, // learner1
        'course_id' => $course->id,
        'order_id' => $order->id,
        'status' => Enrollment::STATUS_ACTIVE,
        'enrolled_at' => Carbon::now(),
    ]);

    $response = $this->getJson("/api/learn/courses/{$course->id}/outline", $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
    $course->forceDelete();
});

test('invalid non-numeric ID path parameter returns 404 due to route pattern', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/courses/abc/outline', $headers);

    $response->assertStatus(404);
});

test('invalid ID path parameter (0) returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/courses/0/outline', $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});

test('enrolled learner can fetch course outline successfully', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/learn/courses/1/outline', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Lấy lộ trình khóa học thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'sort_order',
                    'lessons' => [
                        '*' => [
                            'id',
                            'course_section_id',
                            'title',
                            'slug',
                            'lesson_type',
                            'is_preview',
                            'sort_order',
                            'video_duration_seconds',
                            'progress',
                        ]
                    ]
                ]
            ]
        ]);

    $progressTime = Carbon::now()->subMinutes(10)->microsecond(0);
    $progress = LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 1,
        'status' => 'in_progress',
        'started_at' => $progressTime,
        'last_accessed_at' => $progressTime,
        'learning_duration_seconds' => 300,
    ]);

    $response2 = $this->getJson('/api/learn/courses/1/outline', $headers);
    $response2->assertStatus(200);

    $data = $response2->json('data');
    $foundLesson1 = false;
    foreach ($data as $section) {
        foreach ($section['lessons'] as $lesson) {
            if ($lesson['id'] == 1) {
                $foundLesson1 = true;
                $this->assertNotNull($lesson['progress']);
                $this->assertEquals('in_progress', $lesson['progress']['status']);
                $this->assertEquals($progressTime->toISOString(), $lesson['progress']['started_at']);
                $this->assertEquals(300, $lesson['progress']['learning_duration_seconds']);
            } else {
                $this->assertNull($lesson['progress']);
            }
        }
    }
    $this->assertTrue($foundLesson1);
});
