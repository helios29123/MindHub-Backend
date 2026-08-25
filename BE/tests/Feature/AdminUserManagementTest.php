<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        \Schema::disableForeignKeyConstraints();
        if (!\Schema::hasColumn('sessions', 'refresh_token_hash')) {
            \Schema::dropIfExists('sessions');
            \Schema::create('sessions', function ($table) {
                $table->id();
                $table->foreignId('user_id');
                $table->string('refresh_token_hash', 255)->nullable();
                $table->string('device_name', 255)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    private function generateAuthToken(User $user)
    {
        $session = \App\Models\Session::create([
            'user_id' => $user->id,
            'refresh_token_hash' => 'dummy_' . uniqid(),
            'expires_at' => now()->addDays(1),
        ]);
        
        $tokenService = $this->app->make(\App\Services\Auth\AccessTokenService::class);
        return $tokenService->createAccessToken($user->id, $session->id)['token'];
    }

    private function createUser($role = 'learner', $status = 'active')
    {
        $id = DB::table('users')->insertGetId([
            'full_name' => 'User ' . uniqid(),
            'email' => uniqid() . '@example.com',
            'password_hash' => bcrypt('12345678'),
            'role' => $role,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($id);
    }

    public function test_unauthenticated_cannot_access()
    {
        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(401);
    }

    public function test_learner_instructor_cannot_access()
    {
        $learner = $this->createUser('learner');
        $token = $this->generateAuthToken($learner);
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users');
        $response->assertStatus(403);

        $instructor = $this->createUser('instructor');
        $token2 = $this->generateAuthToken($instructor);
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/admin/users');
        $response2->assertStatus(403);
    }

    public function test_admin_list_users()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);
        
        $this->createUser('learner');
        $this->createUser('instructor');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users?per_page=5');
            
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'summary',
                    'items' => [
                        '*' => [
                            'id', 'full_name', 'email', 'phone', 'role', 'status',
                            'locked', 'created_at', 'updated_at'
                        ]
                    ]
                ],
                'meta' => ['current_page', 'per_page', 'total']
            ]);
            
        // password_hash should not be present
        $this->assertArrayNotHasKey('password_hash', $response->json('data.items.0'));
        $this->assertArrayNotHasKey('password_reset', $response->json('data.items.0'));
    }

    public function test_per_page_exceeds_max()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users?per_page=999');
            
        $response->assertStatus(422);
    }

    public function test_filter_role_and_status()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);
        
        $this->createUser('learner', 'inactive');
        $this->createUser('instructor', 'active');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users?role=learner&status=inactive');
            
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.items'));
        foreach ($response->json('data.items') as $item) {
            $this->assertEquals('learner', $item['role']);
            $this->assertEquals('inactive', $item['status']);
        }
        
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users?role=invalid_role');
        $response2->assertStatus(422);
        
        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users?status=invalid_status');
        $response3->assertStatus(422);
    }

    public function test_sort_invalid_field()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users?sort_by=password_hash');
            
        $response->assertStatus(422);
    }

    public function test_show_user()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);
        
        $learner = $this->createUser('learner');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users/' . $learner->id);
            
        $response->assertStatus(200);
        $this->assertEquals($learner->id, $response->json('data.id'));
        $this->assertArrayNotHasKey('password_hash', $response->json('data'));
        
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users/999999');
        $response2->assertStatus(404);
    }

    public function test_create_user()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);
        
        $payload = [
            'full_name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => '12345678',
            'role' => 'learner',
            'status' => 'active'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/users', $payload);
            
        $response->assertStatus(201);
        $this->assertEquals('New User', $response->json('data.full_name'));
        $this->assertArrayNotHasKey('password_hash', $response->json('data'));
        
        // Test missing fields
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/users', []);
        $response2->assertStatus(422);
        
        // Test duplicate email
        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/users', $payload);
        $response3->assertStatus(422);
    }

    public function test_update_user()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);
        
        $learner = $this->createUser('learner');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/admin/users/' . $learner->id, [
                'full_name' => 'Updated Name'
            ]);
            
        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $response->json('data.full_name'));
        
        // Empty payload
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/admin/users/' . $learner->id, []);
        $response2->assertStatus(422);
    }

    public function test_admin_cannot_self_downgrade_or_lock()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/admin/users/' . $admin->id, [
                'role' => 'learner'
            ]);
        $response->assertStatus(422);
        
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson('/api/admin/users/' . $admin->id, [
                'status' => 'locked'
            ]);
        $response2->assertStatus(422);
    }

    public function test_delete_user()
    {
        $admin = $this->createUser('admin');
        $token = $this->generateAuthToken($admin);
        
        $learner = $this->createUser('learner');
        $learnerToken = $this->generateAuthToken($learner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/admin/users/' . $learner->id);
            
        $response->assertStatus(200);
        
        // Should be soft deleted
        $this->assertDatabaseHas('users', [
            'id' => $learner->id,
        ]);
        $this->assertNotNull(DB::table('users')->where('id', $learner->id)->value('deleted_at'));
        
        // Session should be revoked
        $this->assertNotNull(DB::table('sessions')->where('user_id', $learner->id)->value('revoked_at'));
        
        // Cannot fetch deleted user
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/users/' . $learner->id);
        $response2->assertStatus(404);
        
        // Cannot self delete
        $response3 = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/admin/users/' . $admin->id);
        $response3->assertStatus(422);
    }
}
