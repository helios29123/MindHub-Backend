<?php

use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

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

afterEach(function () {
    // Clean up attempts created during tests (attempt_number > 2 for user 4)
    QuizAttempt::where('user_id', 4)->where('quiz_id', 1)->where('attempt_number', '>', 2)->delete();
});

test('learner can submit quiz and pass with all correct answers', function () {
    // learner1@mindhub.test has active enrollment for course 1, and quiz 1 is published
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->postJson('/api/quizzes/1/attempts', [
        'answers' => [
            ['question_id' => 1, 'option_id' => 1], // Correct
            ['question_id' => 2, 'option_id' => 5], // Correct
            ['question_id' => 3, 'option_id' => 9], // Correct
        ]
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
                'attempt_id',
                'score',
                'total_score',
                'passed'
            ]
        ]);

    $this->assertEquals(10.00, (float) $response->json('data.score'));
    $this->assertEquals(10.00, (float) $response->json('data.total_score'));
    $this->assertTrue((bool) $response->json('data.passed'));
});

test('learner can submit quiz and fail with wrong answers', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->postJson('/api/quizzes/1/attempts', [
        'answers' => [
            ['question_id' => 1, 'option_id' => 2], // Wrong
            ['question_id' => 2, 'option_id' => 8], // Wrong
            ['question_id' => 3, 'option_id' => 10], // Wrong
        ]
    ], $headers);

    $response->assertStatus(201);
    $this->assertEquals(0.00, (float) $response->json('data.score'));
    $this->assertFalse((bool) $response->json('data.passed'));
});

test('unauthenticated user is blocked', function () {
    $response = $this->postJson('/api/quizzes/1/attempts', [
        'answers' => [
            ['question_id' => 1, 'option_id' => 1]
        ]
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->postJson('/api/quizzes/1/attempts', [
        'answers' => [
            ['question_id' => 1, 'option_id' => 1]
        ]
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('non-existent quiz returns 404', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->postJson('/api/quizzes/999/attempts', [
        'answers' => [
            ['question_id' => 1, 'option_id' => 1]
        ]
    ], $headers);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy dữ liệu.',
        ]);
});

test('quiz not published returns 403', function () {
    // Quiz 3 belongs to course 2 (pending_review) or is draft
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->postJson('/api/quizzes/3/attempts', [
        'answers' => [
            ['question_id' => 1, 'option_id' => 1]
        ]
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Nội dung chưa khả dụng.',
        ]);
});

test('learner with no enrollment is blocked', function () {
    // learner2@mindhub.test has no enrollment in course 1
    $headers = getAuthHeadersForUser('learner2@mindhub.test');

    $response = $this->postJson('/api/quizzes/1/attempts', [
        'answers' => [
            ['question_id' => 1, 'option_id' => 1]
        ]
    ], $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn chưa có quyền truy cập nội dung này.',
        ]);
});

test('option not belonging to question returns 422', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->postJson('/api/quizzes/1/attempts', [
        'answers' => [
            // Option 5 belongs to question 2, not question 1
            ['question_id' => 1, 'option_id' => 5]
        ]
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Đáp án không hợp lệ cho câu hỏi.',
        ]);
});

test('validation error on invalid body inputs', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->postJson('/api/quizzes/1/attempts', [
        'answers' => []
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});
