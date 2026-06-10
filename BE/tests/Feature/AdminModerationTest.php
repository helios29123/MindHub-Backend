<?php

use App\Models\User;
use App\Models\Comment;
use App\Models\CourseReview;
use Illuminate\Support\Facades\DB;

function getAuthHeadersForAdminTest(string $email): array
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

afterEach(function () {
    // Restore original status of comments (id <= 5)
    Comment::where('id', 1)->update(['status' => 'visible']);
    Comment::where('id', 2)->update(['status' => 'visible']);
    Comment::where('id', 3)->update(['status' => 'hidden']);
    Comment::where('id', 4)->update(['status' => 'deleted']);
    Comment::where('id', 5)->update(['status' => 'visible']);

    // Restore original deleted_at of course reviews (id <= 4)
    CourseReview::withTrashed()->whereIn('id', [1, 2, 3, 4])->update(['deleted_at' => null]);
    
    // Delete any comments created during tests
    Comment::where('id', '>', 5)->delete();
    
    // Delete any reviews created during tests
    CourseReview::withTrashed()->where('id', '>', 4)->forceDelete();
});

test('admin can moderate a comment status to hidden', function () {
    $headers = getAuthHeadersForAdminTest('admin@mindhub.test');

    $response = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'comment',
        'status' => 'hidden',
        'reason' => 'Nội dung chứa từ ngữ nhạy cảm',
    ], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => json_encode(['id' => 1, 'status' => 'hidden']),
        ]);

    $this->assertDatabaseHas('comments', [
        'id' => 1,
        'status' => 'hidden',
    ]);
});

test('admin can moderate a comment status to visible', function () {
    $headers = getAuthHeadersForAdminTest('admin@mindhub.test');

    $response = $this->patchJson('/api/admin/moderation/items/3', [
        'target_type' => 'comment',
        'status' => 'visible',
    ], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => json_encode(['id' => 3, 'status' => 'visible']),
        ]);

    $this->assertDatabaseHas('comments', [
        'id' => 3,
        'status' => 'visible',
    ]);
});

test('admin can soft delete a course review', function () {
    $headers = getAuthHeadersForAdminTest('admin@mindhub.test');

    $response = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'review',
        'status' => 'deleted',
        'reason' => 'Đánh giá rác',
    ], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => json_encode(['id' => 1, 'status' => 'deleted']),
        ]);

    $this->assertSoftDeleted('course_reviews', [
        'id' => 1,
    ]);
});

test('admin can restore a soft-deleted course review', function () {
    $headers = getAuthHeadersForAdminTest('admin@mindhub.test');

    // 1. Soft delete review 2 first
    CourseReview::find(2)->delete();

    // 2. Perform restoration moderation
    $response = $this->patchJson('/api/admin/moderation/items/2', [
        'target_type' => 'review',
        'status' => 'visible',
    ], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => json_encode(['id' => 2, 'status' => 'visible']),
        ]);

    $this->assertDatabaseHas('course_reviews', [
        'id' => 2,
        'deleted_at' => null,
    ]);
});

test('unauthenticated users are blocked from moderation', function () {
    $response = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'comment',
        'status' => 'hidden',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized roles (instructor/learner) are blocked from moderation', function () {
    // 1. Test learner
    $headersLearner = getAuthHeadersForAdminTest('learner1@mindhub.test');
    $responseLearner = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'comment',
        'status' => 'hidden',
    ], $headersLearner);
    $responseLearner->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);

    // 2. Test instructor
    $headersInstructor = getAuthHeadersForAdminTest('instructor1@mindhub.test');
    $responseInstructor = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'comment',
        'status' => 'hidden',
    ], $headersInstructor);
    $responseInstructor->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('moderating non-existent item returns 404', function () {
    $headers = getAuthHeadersForAdminTest('admin@mindhub.test');

    // Non-existent comment
    $responseComment = $this->patchJson('/api/admin/moderation/items/999', [
        'target_type' => 'comment',
        'status' => 'hidden',
    ], $headers);
    $responseComment->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);

    // Non-existent review
    $responseReview = $this->patchJson('/api/admin/moderation/items/999', [
        'target_type' => 'review',
        'status' => 'deleted',
    ], $headers);
    $responseReview->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('validation fails on invalid inputs', function () {
    $headers = getAuthHeadersForAdminTest('admin@mindhub.test');

    // Missing target_type
    $responseMissingType = $this->patchJson('/api/admin/moderation/items/1', [
        'status' => 'hidden',
    ], $headers);
    $responseMissingType->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);

    // Invalid target_type value
    $responseInvalidType = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'invalid',
        'status' => 'hidden',
    ], $headers);
    $responseInvalidType->assertStatus(422);

    // Invalid status value
    $responseInvalidStatus = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'comment',
        'status' => 'invalid',
    ], $headers);
    $responseInvalidStatus->assertStatus(422);

    // Reason too long (> 500 chars)
    $responseLongReason = $this->patchJson('/api/admin/moderation/items/1', [
        'target_type' => 'comment',
        'status' => 'hidden',
        'reason' => str_repeat('A', 501),
    ], $headers);
    $responseLongReason->assertStatus(422);
});

test('validation fails on invalid path parameter id', function () {
    $headers = getAuthHeadersForAdminTest('admin@mindhub.test');

    // Path ID is negative or 0
    $responseZero = $this->patchJson('/api/admin/moderation/items/0', [
        'target_type' => 'comment',
        'status' => 'hidden',
    ], $headers);
    $responseZero->assertStatus(422);
});
