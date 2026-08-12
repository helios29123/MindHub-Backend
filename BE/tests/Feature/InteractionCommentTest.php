<?php

use App\Models\User;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

function getAuthHeadersForCommentTest(string $email = 'instructor1@mindhub.test'): array
{
    $role = str_contains($email, 'learner') ? 'learner' : 'instructor';

    $displayName = match ($email) {
        'instructor1@mindhub.test' => 'Instructor Test 1',
        'instructor2@mindhub.test' => 'Instructor Test 2',
        'learner1@mindhub.test' => 'Learner Test 1',
        'learner2@mindhub.test' => 'Learner Test 2',
        default => 'API Test User',
    };

    $data = [];

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'name')) {
        $data['name'] = $displayName;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
        $data['username'] = str_replace(['@', '.'], '_', $email);
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
        $data['phone'] = '0900000000';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password')) {
        $data['password'] = \Illuminate\Support\Facades\Hash::make('password');
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password_hash')) {
        $data['password_hash'] = \Illuminate\Support\Facades\Hash::make('password');
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $data['role'] = $role;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
        $data['status'] = 'active';
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
        $data['is_active'] = 1;
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
        $data['email_verified_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'created_at')) {
        $data['created_at'] = now();
    }

    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'updated_at')) {
        $data['updated_at'] = now();
    }

    \Illuminate\Support\Facades\DB::table('users')->updateOrInsert(['email' => $email], $data);

    $user = \App\Models\User::where('email', $email)->first();

    if (!$user) {
        throw new \RuntimeException("Cannot create test user {$email}");
    }

    $aliases = app('router')->getMiddleware();

    if (isset($aliases['auth.session'])) {
        test()->withoutMiddleware($aliases['auth.session']);
    }

    if (isset($aliases['active.user'])) {
        test()->withoutMiddleware($aliases['active.user']);
    }

    test()->actingAs($user);

    test()->withSession([
        'user_id' => $user->id,
        'auth_user_id' => $user->id,
        'role' => $role,
        'email' => $email,
        'is_authenticated' => true,
    ]);

    return ['Accept' => 'application/json'];
}

afterEach(function () {
    // Clean up comments created during tests (id > 5)
    Comment::where('id', '>', 5)->delete();
});

test('learner can view lesson comments list', function () {
    // learner1@mindhub.test has active enrollment for course 1, and lesson 2 is published
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    $response = $this->getJson('/api/lessons/2/comments', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Lấy danh sách bình luận thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'comment_id',
                    'parent_id',
                    'user_id',
                    'user' => [
                        'id',
                        'full_name',
                    ],
                    'content',
                    'status',
                    'created_at',
                ]
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ]
        ]);
});

test('learner can post a new comment on lesson', function () {
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    $response = $this->postJson('/api/lessons/2/comments', [
        'content' => 'Đây là một bình luận thử nghiệm tuyệt vời.',
    ], $headers);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'comment_id',
                'status',
            ]
        ]);

    $commentId = $response->json('data.comment_id');
    $this->assertDatabaseHas('comments', [
        'id' => $commentId,
        'user_id' => 4, // learner1
        'lesson_id' => 2,
        'content' => 'Đây là một bình luận thử nghiệm tuyệt vời.',
        'status' => 'visible',
    ]);
});

test('learner can reply to another comment', function () {
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    \App\Models\Comment::where('id', 1)->update(['lesson_id' => 2]);

    $response = $this->postJson('/api/lessons/2/comments', [
        'content' => 'Bình luận trả lời thử nghiệm.',
        'parent_id' => 1,
    ], $headers);

    $response->assertStatus(201);
    
    $commentId = $response->json('data.comment_id');
    $this->assertDatabaseHas('comments', [
        'id' => $commentId,
        'parent_id' => 1,
        'content' => 'Bình luận trả lời thử nghiệm.',
    ]);
});

test('unauthenticated users cannot view or post comments', function () {
    $responseGet = $this->getJson('/api/lessons/2/comments');
    $responseGet->assertStatus(401);

    $responsePost = $this->postJson('/api/lessons/2/comments', [
        'content' => 'Bình luận không hợp lệ.',
    ]);
    $responsePost->assertStatus(401);
});

test('non-learner role is blocked', function () {
    $headers = getAuthHeadersForCommentTest('admin@mindhub.test');

    $response = $this->postJson('/api/lessons/2/comments', [
        'content' => 'Admin không được bình luận.',
    ], $headers);

    $response->assertStatus(403);
});

test('non-existent lesson returns 404', function () {
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    $response = $this->getJson('/api/lessons/999/comments', $headers);
    $response->assertStatus(404);

    $responsePost = $this->postJson('/api/lessons/999/comments', [
        'content' => 'Thử nghiệm.',
    ], $headers);
    $responsePost->assertStatus(404);
});

test('lesson not published returns 403', function () {
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    // Lesson 5 is hidden
    $response = $this->getJson('/api/lessons/5/comments', $headers);
    $response->assertStatus(403);
});

test('learner with no enrollment is blocked', function () {
    // learner2@mindhub.test has no enrollment in course 1
    $headers = getAuthHeadersForCommentTest('learner2@mindhub.test');

    $response = $this->postJson('/api/lessons/2/comments', [
        'content' => 'Tôi chưa mua khóa học này.',
    ], $headers);

    $response->assertStatus(403);
});

test('invalid query parameters return 422', function () {
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    $response = $this->getJson('/api/lessons/2/comments?rating=5', $headers);
    $response->assertStatus(422);
});

test('empty or too long content validation fails', function () {
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    // Empty content
    $responseEmpty = $this->postJson('/api/lessons/2/comments', [
        'content' => '',
    ], $headers);
    $responseEmpty->assertStatus(422);

    // Too long content (>2000 chars)
    $responseLong = $this->postJson('/api/lessons/2/comments', [
        'content' => str_repeat('A', 2001),
    ], $headers);
    $responseLong->assertStatus(422);
});

test('instructor can reply to comment on their own published course lesson', function () {
    $headers = getAuthHeadersForCommentTest('instructor1@mindhub.test');

    \App\Models\Comment::where('id', 1)->update(['lesson_id' => 2]);

    $response = $this->postJson('/api/comments/1/replies', [
        'content' => 'Chào em, đây là câu trả lời từ giảng viên.',
    ], $headers);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'comment_id',
                'status',
            ]
        ]);

    $commentId = $response->json('data.comment_id');
    $this->assertDatabaseHas('comments', [
        'id' => $commentId,
        'parent_id' => 1,
        'user_id' => 2, // instructor1
        'lesson_id' => 2,
        'content' => 'Chào em, đây là câu trả lời từ giảng viên.',
        'status' => 'visible',
    ]);
});

test('unauthenticated users cannot reply to comments', function () {
    $response = $this->postJson('/api/comments/1/replies', [
        'content' => 'Thử trả lời khi chưa đăng nhập.',
    ]);
    $response->assertStatus(401);
});

test('learners cannot reply using the instructor endpoint', function () {
    $headers = getAuthHeadersForCommentTest('learner1@mindhub.test');

    $response = $this->postJson('/api/comments/1/replies', [
        'content' => 'Học viên không được trả lời ở đây.',
    ], $headers);

    $response->assertStatus(403);
});

test('replying to non-existent comment returns 404', function () {
    $headers = getAuthHeadersForCommentTest('instructor1@mindhub.test');

    $response = $this->postJson('/api/comments/999/replies', [
        'content' => 'Bình luận không tồn tại.',
    ], $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('replying to hidden or deleted comment returns 404', function () {
    $headers = getAuthHeadersForCommentTest('instructor1@mindhub.test');

    // Make sure comment 3 is hidden and comment 4 is deleted
    \App\Models\Comment::where('id', 3)->update(['status' => 'hidden']);
    \App\Models\Comment::where('id', 4)->update(['status' => 'deleted']);

    // Comment 3 is hidden, comment 4 is deleted
    $responseHidden = $this->postJson('/api/comments/3/replies', [
        'content' => 'Trả lời bình luận ẩn.',
    ], $headers);
    $responseHidden->assertStatus(404);

    $responseDeleted = $this->postJson('/api/comments/4/replies', [
        'content' => 'Trả lời bình luận đã xóa.',
    ], $headers);
    $responseDeleted->assertStatus(404);
});

test('instructor cannot reply to Q&A of a course they do not own', function () {
    // instructor2@mindhub.test does not own course 1 (which contains lesson 2 and comment 1)
    $headers = getAuthHeadersForCommentTest('instructor2@mindhub.test');

    $response = $this->postJson('/api/comments/1/replies', [
        'content' => 'Tôi không sở hữu khóa học này.',
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không được trả lời Q&A của khóa học này.',
        ]);
});

test('instructor cannot reply if lesson or course is not published', function () {
    $headers = getAuthHeadersForCommentTest('instructor1@mindhub.test');

    // Create a temporary comment on a hidden lesson (lesson 5 is hidden, in course 1 which is published)
    $tempCommentHiddenLesson = Comment::create([
        'parent_id' => null,
        'user_id' => 4,
        'lesson_id' => 5, // hidden lesson
        'content' => 'Bình luận trên bài học bị ẩn.',
        'status' => 'visible',
    ]);

    $responseHiddenLesson = $this->postJson("/api/comments/{$tempCommentHiddenLesson->id}/replies", [
        'content' => 'Trả lời bài học ẩn.',
    ], $headers);
    $responseHiddenLesson->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);

    // Make sure lesson 6 is draft and belongs to course 2 (which is pending_review)
    \App\Models\Lesson::where('id', 6)->update(['status' => 'draft', 'course_id' => 2]);

    // Create a temporary comment on a draft lesson/course (lesson 6 is draft, in course 2 which is pending_review)
    $tempCommentDraft = Comment::create([
        'parent_id' => null,
        'user_id' => 4,
        'lesson_id' => 6, // draft lesson in course pending_review
        'content' => 'Bình luận trên bài học nháp.',
        'status' => 'visible',
    ]);

    $responseDraft = $this->postJson("/api/comments/{$tempCommentDraft->id}/replies", [
        'content' => 'Trả lời bài học nháp.',
    ], $headers);
    $responseDraft->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);
});

test('reply validation fails on invalid body inputs', function () {
    $headers = getAuthHeadersForCommentTest('instructor1@mindhub.test');

    // Empty content
    $responseEmpty = $this->postJson('/api/comments/1/replies', [
        'content' => '',
    ], $headers);
    $responseEmpty->assertStatus(422);

    // Too long content (>2000 chars)
    $responseLong = $this->postJson('/api/comments/1/replies', [
        'content' => str_repeat('A', 2001),
    ], $headers);
    $responseLong->assertStatus(422);
});

