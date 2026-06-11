<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

function getAuthHeadersForUserTest(string $email): array
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
    // Clean up created users
    User::withTrashed()->where('email', 'like', '%@test.com')->forceDelete();
});

test('unauthenticated users are blocked from admin user routes', function () {
    $response = $this->getJson('/api/admin/users');
    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ]);
});

test('unauthorized roles are blocked from admin user routes', function () {
    $headers = getAuthHeadersForUserTest('learner1@mindhub.test');
    
    $response = $this->getJson('/api/admin/users', $headers);
    $response->assertStatus(403);
});

test('admin can list users with pagination', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/users?page=1&per_page=5', $headers);
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'full_name', 'email', 'role', 'status']
            ],
            'meta' => ['current_page', 'per_page', 'total']
        ]);
        
    $this->assertLessThanOrEqual(5, count($response->json('data')));
});

test('admin can filter users by role and status', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/users?role=learner&status=active', $headers);
    $response->assertStatus(200);
    
    foreach ($response->json('data') as $user) {
        expect($user['role'])->toBe('learner');
        expect($user['status'])->toBe('active');
    }
});

test('admin can search users', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/users?search=admin', $headers);
    $response->assertStatus(200);
});

test('admin can sort users', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/users?sort_by=email&sort_direction=asc', $headers);
    $response->assertStatus(200);
});

test('validation fails on invalid query parameters', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/users?role=invalid_role&status=banned&per_page=999', $headers);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role', 'status', 'per_page']);
});

test('admin can get user details', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');
    
    $user = User::factory()->create(['email' => 'get@test.com']);

    $response = $this->getJson("/api/admin/users/{$user->id}", $headers);
    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonMissingPath('data.password_hash')
        ->assertJsonMissingPath('data.password_reset');
});

test('get non-existent user returns 404', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->getJson('/api/admin/users/999999', $headers);
    $response->assertStatus(404);
});

test('admin can create user', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->postJson('/api/admin/users', [
        'full_name' => 'Test Create',
        'email' => 'create@test.com',
        'password' => '12345678',
        'phone' => '0900000000',
        'role' => 'learner',
        'status' => 'active'
    ], $headers);

    $response->assertStatus(201)
        ->assertJsonPath('data.email', 'create@test.com')
        ->assertJsonMissingPath('data.password_hash');
        
    $this->assertDatabaseHas('users', ['email' => 'create@test.com']);
});

test('create user validation fails on missing fields and invalid enums', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->postJson('/api/admin/users', [
        'email' => 'invalid',
        'role' => 'superadmin',
        'status' => 'banned'
    ], $headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['full_name', 'email', 'password', 'role', 'status']);
});

test('create user validation fails on duplicate email', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');

    $response = $this->postJson('/api/admin/users', [
        'full_name' => 'Test Create',
        'email' => 'admin@mindhub.test',
        'password' => '12345678',
        'role' => 'learner',
        'status' => 'active'
    ], $headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('admin can update user', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');
    
    $user = User::factory()->create(['email' => 'update@test.com', 'role' => 'learner']);

    $response = $this->patchJson("/api/admin/users/{$user->id}", [
        'role' => 'instructor',
        'status' => 'inactive'
    ], $headers);

    $response->assertStatus(200)
        ->assertJsonPath('data.role', 'instructor');
        
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'instructor',
        'status' => 'inactive'
    ]);
});

test('update validation fails on empty payload and invalid enums', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');
    $user = User::factory()->create(['email' => 'update_invalid@test.com']);

    $responseEmpty = $this->patchJson("/api/admin/users/{$user->id}", [], $headers);
    $responseEmpty->assertStatus(422)->assertJsonValidationErrors(['payload']);
    
    $responseInvalid = $this->patchJson("/api/admin/users/{$user->id}", [
        'role' => 'owner',
        'status' => 'disabled'
    ], $headers);
    $responseInvalid->assertStatus(422)->assertJsonValidationErrors(['role', 'status']);
});

test('admin cannot update own role or status', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');
    $admin = User::where('email', 'admin@mindhub.test')->first();

    $responseRole = $this->patchJson("/api/admin/users/{$admin->id}", [
        'role' => 'learner'
    ], $headers);
    $responseRole->assertStatus(400);
    
    $responseStatus = $this->patchJson("/api/admin/users/{$admin->id}", [
        'status' => 'locked'
    ], $headers);
    $responseStatus->assertStatus(400);
});

test('admin can soft delete user', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');
    $user = User::factory()->create(['email' => 'delete@test.com']);

    $response = $this->deleteJson("/api/admin/users/{$user->id}", [], $headers);
    $response->assertStatus(200);
    
    $this->assertSoftDeleted('users', [
        'id' => $user->id
    ]);
});

test('admin cannot delete self', function () {
    $headers = getAuthHeadersForUserTest('admin@mindhub.test');
    $admin = User::where('email', 'admin@mindhub.test')->first();

    $response = $this->deleteJson("/api/admin/users/{$admin->id}", [], $headers);
    $response->assertStatus(400);
    
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'deleted_at' => null
    ]);
});
