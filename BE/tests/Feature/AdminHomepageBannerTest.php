<?php

use App\Models\User;
use App\Models\Banner;

function getAuthHeadersForAdminHomepageBannerTest(string $email): array
{
    $role = 'learner';
    if (str_contains($email, 'admin')) {
        $role = 'admin';
    } elseif (str_contains($email, 'instructor')) {
        $role = 'instructor';
    }

    $data = [];
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'name')) {
        $data['name'] = 'Test User';
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'full_name')) {
        $data['full_name'] = 'Test User';
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
        $data['username'] = str_replace(['@', '.'], '_', $email);
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
        $data['phone'] = '0900000000';
    }

    $hash = \Illuminate\Support\Facades\Hash::make('password');
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password')) {
        $data['password'] = $hash;
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'password_hash')) {
        $data['password_hash'] = $hash;
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
        $data['role'] = $role;
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
        $data['status'] = 'active';
    }
    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'locked')) {
        $data['locked'] = 0;
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

    $response = test()->postJson('/api/auth/login', [
        'email' => $email,
        'password' => 'password',
        'device_name' => 'testing'
    ]);
    
    $token = $response->json('data.access_token');
    return [
        'Authorization' => "Bearer $token",
    ];
}

afterEach(function () {
    // Delete any banners created during tests (id > 3)
    Banner::withTrashed()->where('id', '>', 3)->forceDelete();
    
    // Restore original status / fields of banners (id <= 3) if modified
    Banner::withTrashed()->where('id', 1)->update([
        'title' => 'Banner khóa Laravel nổi bật',
        'status' => 'active',
        'deleted_at' => null,
    ]);
    
    Banner::withTrashed()->where('id', 2)->update([
        'title' => 'Banner khóa Git miễn phí',
        'status' => 'active',
        'deleted_at' => null,
    ]);
 
    Banner::withTrashed()->where('id', 3)->update([
        'title' => 'Banner inactive để test',
        'status' => 'inactive',
        'deleted_at' => null,
    ]);
});

test('admin can list homepage banners paginated', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/banners', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'banner_id',
                    'title',
                    'image_url',
                    'target_url',
                    'position',
                    'sort_order',
                    'start_at',
                    'end_at',
                    'status',
                    'created_at',
                    'updated_at',
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

test('admin can view a single homepage banner details', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/banners/1', $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => [
                'id' => 1,
                'title' => 'Banner khóa Laravel nổi bật',
                'status' => 'active',
            ]
        ]);
});

test('admin can create a new homepage banner', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $response = $this->postJson('/api/admin/banners', [
        'title' => 'Banner mới cho khóa React',
        'image_url' => 'https://example.com/react-banner.jpg',
        'target_url' => 'https://example.com/courses/react',
        'position' => 'home_middle',
        'sort_order' => 5,
        'start_at' => '2026-06-10 00:00:00',
        'end_at' => '2026-07-10 00:00:00',
        'status' => 'active',
    ], $headers);

    $response->assertStatus(200);
    $bannerId = json_decode($response->json('data'), true)['id'];

    $response->assertJson([
        'success' => true,
        'message' => 'Thao tác thành công',
        'data' => json_encode(['id' => $bannerId, 'status' => 'updated']),
    ]);

    $this->assertDatabaseHas('banners', [
        'id' => $bannerId,
        'title' => 'Banner mới cho khóa React',
        'status' => 'active',
    ]);
});

test('admin can update a homepage banner', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $response = $this->putJson('/api/admin/banners/1', [
        'title' => 'Banner Laravel cập nhật',
        'image_url' => 'https://example.com/laravel-updated.jpg',
        'target_url' => 'https://example.com/courses/laravel-new',
        'position' => 'home',
        'sort_order' => 1,
        'start_at' => '2026-06-10 00:00:00',
        'end_at' => '2026-06-20 00:00:00',
        'status' => 'inactive',
    ], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
            'data' => json_encode(['id' => 1, 'status' => 'updated']),
        ]);

    $this->assertDatabaseHas('banners', [
        'id' => 1,
        'title' => 'Banner Laravel cập nhật',
        'status' => 'inactive',
    ]);
});

test('admin can soft delete a homepage banner', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $response = $this->deleteJson('/api/admin/banners/1', [], $headers);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Thao tác thành công',
        ]);

    $this->assertSoftDeleted('banners', [
        'id' => 1,
    ]);
});

test('unauthenticated users are blocked from admin banner management', function () {
    $this->getJson('/api/admin/banners')->assertStatus(401);
    $this->postJson('/api/admin/banners', [])->assertStatus(401);
});

test('unauthorized roles are blocked from admin banner management', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('instructor1@mindhub.test');

    $this->getJson('/api/admin/banners', $headers)->assertStatus(403);
    $this->postJson('/api/admin/banners', [], $headers)->assertStatus(403);
});

test('non-existent admin banner returns 404', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $this->getJson('/api/admin/banners/999', $headers)->assertStatus(404);
    $this->deleteJson('/api/admin/banners/999', [], $headers)->assertStatus(404);
});

test('validation error on invalid status value for admin banner', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $response = $this->postJson('/api/admin/banners', [
        'title' => 'Banner test',
        'image_url' => 'https://example.com/banner.jpg',
        'position' => 'home',
        'status' => 'invalid_status_value',
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Trạng thái banner không hợp lệ.',
        ]);
});

test('validation error on invalid date sequence for admin banner', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $response = $this->postJson('/api/admin/banners', [
        'title' => 'Banner test',
        'image_url' => 'https://example.com/banner.jpg',
        'position' => 'home',
        'start_at' => '2026-06-10 12:00:00',
        'end_at' => '2026-06-09 12:00:00', // Before start_at
        'status' => 'active',
    ], $headers);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Thời gian banner không hợp lệ.',
        ]);
});

test('validation error on missing path parameter id for admin banner', function () {
    $headers = getAuthHeadersForAdminHomepageBannerTest('admin@mindhub.test');

    $this->putJson('/api/admin/banners/0', [
        'title' => 'Banner test',
        'image_url' => 'https://example.com/banner.jpg',
        'position' => 'home',
        'status' => 'active',
    ], $headers)->assertStatus(422);
});
