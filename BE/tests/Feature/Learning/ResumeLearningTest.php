<?php

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
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
});

test('unauthenticated user cannot resume learning', function () {
    $response = $this->getJson('/api/learn/resume');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from resuming learning', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->getJson('/api/learn/resume', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner with no enrollments gets 403', function () {
    // learner2@mindhub.test is user 5. Delete all their enrollments first
    Enrollment::where('user_id', 5)->delete();
    
    $headers = getAuthHeadersForUser('learner2@mindhub.test');

    $response = $this->getJson('/api/learn/resume', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);
});

test('learner with progress gets the most recently accessed lesson', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Make sure we delete existing progress first
    LessonProgress::where('user_id', 4)->delete();

    // Create progress for lesson 1 (accessed 2 hours ago)
    LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 1,
        'status' => 'in_progress',
        'started_at' => Carbon::now()->subHours(2),
        'last_accessed_at' => Carbon::now()->subHours(2),
    ]);

    // Create progress for lesson 3 (accessed 1 hour ago)
    $latestAccessedTime = Carbon::now()->subHours(1)->microsecond(0);
    $progress3 = LessonProgress::create([
        'user_id' => 4,
        'lesson_id' => 3,
        'status' => 'in_progress',
        'started_at' => Carbon::now()->subHours(1),
        'last_accessed_at' => $latestAccessedTime,
    ]);

    // Create video progress for lesson 3
    VideoProgress::create([
        'user_id' => 4,
        'lesson_id' => 3,
        'current_second' => 250,
    ]);

    $response = $this->getJson('/api/learn/resume', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'course' => [
                    'id' => 1,
                ],
                'lesson' => [
                    'id' => 3,
                ],
                'progress' => [
                    'status' => 'in_progress',
                    'current_second' => 250,
                    'last_accessed_at' => $latestAccessedTime->toISOString(),
                ],
                'current_second' => 250,
            ]
        ]);
});

test('learner with no progress fallback to the first lesson of the latest enrolled course', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Delete all progress for learner1 (user 4)
    LessonProgress::where('user_id', 4)->delete();
    VideoProgress::where('user_id', 4)->delete();

    // Create a new course, section, lesson dynamically so it is the latest enrolled course
    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Latest Enrolled Course ' . $uniqueId,
        'slug' => 'latest-enrolled-' . $uniqueId,
        'price' => 200000,
        'status' => 'published',
    ]);

    $section1 = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Section 1',
        'status' => 'published',
        'sort_order' => 2,
    ]);

    $section2 = CourseSection::create([
        'course_id' => $course->id,
        'title' => 'Section 2 (First by sort order)',
        'status' => 'published',
        'sort_order' => 1,
    ]);

    // Lessons in section 2
    $lessonB = Lesson::create([
        'course_section_id' => $section2->id,
        'course_id' => $course->id,
        'title' => 'Lesson B',
        'slug' => 'lesson-b-' . $uniqueId,
        'lesson_type' => 'text',
        'status' => 'published',
        'sort_order' => 2,
    ]);

    $lessonA = Lesson::create([
        'course_section_id' => $section2->id,
        'course_id' => $course->id,
        'title' => 'Lesson A (First by sort order)',
        'slug' => 'lesson-a-' . $uniqueId,
        'lesson_type' => 'video',
        'video_duration_seconds' => 400,
        'status' => 'published',
        'sort_order' => 1,
    ]);

    // Enroll learner1 in this course with a newer enrolled_at
    $order = Order::create([
        'course_id' => $course->id,
        'user_id' => 4,
        'order_code' => 'TEST-ORDER-' . $uniqueId,
        'status' => Order::STATUS_PAID,
        'price_snapshot' => 200000,
        'payment_method' => 'bank_transfer',
        'amount' => 200000,
        'payment_status' => Order::PAYMENT_PAID,
    ]);

    $enrollment = Enrollment::create([
        'user_id' => 4,
        'course_id' => $course->id,
        'order_id' => $order->id,
        'status' => Enrollment::STATUS_ACTIVE,
        'enrolled_at' => Carbon::now()->addDays(5),
    ]);

    $response = $this->getJson('/api/learn/resume', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                ],
                'lesson' => [
                    'id' => $lessonA->id,
                    'title' => $lessonA->title,
                ],
                'progress' => null,
                'current_second' => 0,
            ]
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
    $lessonA->forceDelete();
    $lessonB->forceDelete();
    $section1->forceDelete();
    $section2->forceDelete();
    $course->forceDelete();
});

test('returns 404 if latest course has no published sections or lessons', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    // Delete all progress for learner1
    LessonProgress::where('user_id', 4)->delete();
    VideoProgress::where('user_id', 4)->delete();

    // Create a published course but NO sections or lessons
    $uniqueId = uniqid();
    $course = Course::create([
        'instructor_id' => 2,
        'title' => 'Empty Course ' . $uniqueId,
        'slug' => 'empty-course-' . $uniqueId,
        'price' => 150000,
        'status' => 'published',
    ]);

    $order = Order::create([
        'course_id' => $course->id,
        'user_id' => 4,
        'order_code' => 'TEST-ORDER-EMPTY-' . $uniqueId,
        'status' => Order::STATUS_PAID,
        'price_snapshot' => 150000,
        'payment_method' => 'bank_transfer',
        'amount' => 150000,
        'payment_status' => Order::PAYMENT_PAID,
    ]);

    $enrollment = Enrollment::create([
        'user_id' => 4,
        'course_id' => $course->id,
        'order_id' => $order->id,
        'status' => Enrollment::STATUS_ACTIVE,
        'enrolled_at' => Carbon::now()->addDays(10), // Enforce this is the newest course
    ]);

    $response = $this->getJson('/api/learn/resume', $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);

    // Cleanup
    $enrollment->delete();
    $order->delete();
    $course->forceDelete();
});
