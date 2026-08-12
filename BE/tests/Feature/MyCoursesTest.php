<?php

use App\Models\User;
use App\Models\Enrollment;

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

test('unauthenticated user cannot view purchased courses', function () {
    $response = $this->getJson('/api/me/courses');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized role (admin) is blocked from viewing purchased courses', function () {
    $headers = getAuthHeadersForUser('admin@mindhub.test');

    $response = $this->getJson('/api/me/courses', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('unauthorized role (instructor) is blocked from viewing purchased courses', function () {
    $headers = getAuthHeadersForUser('instructor1@mindhub.test');

    $response = $this->getJson('/api/me/courses', $headers);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện thao tác này.',
        ]);
});

test('learner can successfully retrieve their purchased courses', function () {
    // learner1@mindhub.test has active enrollment for course 1, 7 (Git and Laravel)
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/me/courses', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Lấy danh sách khóa học đã mua thành công.',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'status',
                    'progress_percent',
                    'enrolled_at',
                    'completed_at',
                    'last_accessed_at',
                    'course' => [
                        'id',
                        'title',
                        'slug',
                        'short_description',
                        'thumbnail_url',
                        'price',
                        'sale_price',
                        'level',
                        'language',
                        'total_duration_seconds',
                        'instructor' => [
                            'id',
                            'full_name',
                        ]
                    ]
                ]
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]
        ]);

    // Check that learner1 has 2 courses in progress/completed (Laravel REST API Cơ Bản and Git Cơ Bản Miễn Phí)
    $data = $response->json('data');
    $this->assertCount(2, $data);
});

test('learner can filter purchased courses by status active', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/me/courses?status=active', $headers);

    $response->assertStatus(200);
    $data = $response->json('data');
    
    foreach ($data as $item) {
        $this->assertEquals('active', $item['status']);
    }
});

test('learner can filter purchased courses by status completed', function () {
    // learner.completed@mindhub.test has completed enrollment
    $headers = getAuthHeadersForUser('learner.completed@mindhub.test');

    $response = $this->getJson('/api/me/courses?status=completed', $headers);

    $response->assertStatus(200);
    $data = $response->json('data');
    
    foreach ($data as $item) {
        $this->assertEquals('completed', $item['status']);
    }
});

test('invalid status filter returns 422 validation error', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/me/courses?status=invalid_status', $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'status'
            ]
        ]);
});

test('invalid pagination parameters return 422 validation error', function () {
    $headers = getAuthHeadersForUser('learner1@mindhub.test');

    $response = $this->getJson('/api/me/courses?per_page=abc', $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
        ]);
});
