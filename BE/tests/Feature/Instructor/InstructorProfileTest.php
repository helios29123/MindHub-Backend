<?php

namespace Tests\Feature\Instructor;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InstructorProfileTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private User $learner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = clone User::factory()->create([
            'role' => 'instructor',
            'status' => 'active',
            'phone' => '0988777666',
        ]);

        $this->learner = clone User::factory()->create([
            'role' => 'learner',
            'status' => 'active',
        ]);
    }

    private function getAuthHeaders(string $email): array
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'password',
            'device_name' => 'testing'
        ]);

        $token = $response->json('data.access_token');
        return [
            'Authorization' => "Bearer $token",
        ];
    }

    public function test_unauthenticated_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/instructor/profile');
        $response->assertStatus(401);

        $response = $this->patchJson('/api/instructor/profile/introduction', [
            'bio' => 'Test',
        ]);
        $response->assertStatus(401);
    }

    public function test_non_instructor_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/instructor/profile', $this->getAuthHeaders($this->learner->email));
        $response->assertStatus(403);

        $response = $this->patchJson('/api/instructor/profile/introduction', [
            'bio' => 'Test',
        ], $this->getAuthHeaders($this->learner->email));
        $response->assertStatus(403);
    }

    public function test_instructor_gets_profile_details(): void
    {
        // When profile is missing, it should still return 200 with profile as null
        $response = $this->getJson('/api/instructor/profile', $this->getAuthHeaders($this->instructor->email));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'profile' => [
                    'bio' => null,
                ]
            ],
        ]);
    }

    public function test_instructor_can_create_profile_via_patch(): void
    {
        $payloadBio = [
            'bio' => 'New Bio',
        ];
        $payloadExpertise = [
            'expertise' => 'PHP, Laravel',
            'experience_years' => 5,
            'level' => 'Senior',
        ];

        $responseBio = $this->patchJson('/api/instructor/profile/introduction', $payloadBio, $this->getAuthHeaders($this->instructor->email));
        $responseBio->assertStatus(200);

        $responseExpertise = $this->patchJson('/api/instructor/profile/expertise', $payloadExpertise, $this->getAuthHeaders($this->instructor->email));
        $responseExpertise->assertStatus(200);

        $response = $this->getJson('/api/instructor/profile', $this->getAuthHeaders($this->instructor->email));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'profile' => [
                    'bio' => 'New Bio',
                    'expertise' => 'PHP, Laravel',
                    'experience_years' => 5,
                    'level' => 'Senior',
                ],
            ],
        ]);

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructor->id,
            'bio' => 'New Bio',
        ]);
    }

    public function test_instructor_can_update_own_profile(): void
    {
        InstructorProfile::factory()->create([
            'user_id' => $this->instructor->id,
            'bio' => 'Old Bio',
        ]);

        $response = $this->patchJson('/api/instructor/profile/introduction', [
            'bio' => 'Updated Bio',
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200);
        $response->assertJsonPath('data.bio', 'Updated Bio');

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructor->id,
            'bio' => 'Updated Bio',
        ]);
    }

    public function test_validation_rejects_invalid_experience_years(): void
    {
        $response = $this->patchJson('/api/instructor/profile/expertise', [
            'experience_years' => -5,
        ], $this->getAuthHeaders($this->instructor->email));
        $response->assertStatus(422);
        
        $response = $this->patchJson('/api/instructor/profile/expertise', [
            'experience_years' => 'abc',
        ], $this->getAuthHeaders($this->instructor->email));
        $response->assertStatus(422);

        $response = $this->patchJson('/api/instructor/profile/expertise', [
            'experience_years' => 100,
        ], $this->getAuthHeaders($this->instructor->email));
        $response->assertStatus(422);
    }

    public function test_payload_with_only_unknown_fields_rejected(): void
    {
        $response = $this->patchJson('/api/instructor/profile/expertise', [
            'avatar' => 'http://example.com/avatar.png',
            'social_links' => 'https://facebook.com',
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payload']);
    }

    public function test_response_hides_sensitive_fields(): void
    {
        InstructorProfile::factory()->create([
            'user_id' => $this->instructor->id,
        ]);

        $response = $this->getJson('/api/instructor/profile', $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        $this->assertArrayHasKey('account', $data);
        $this->assertArrayNotHasKey('password_hash', $data['account']);
        $this->assertArrayNotHasKey('password_reset', $data['account']);
        $this->assertArrayNotHasKey('remember_token', $data['account']);
    }

    public function test_can_update_full_profile_via_patch(): void
    {
        $payload = [
            'full_name' => 'Nguyễn Giảng Viên Mới',
            'phone' => '0988777666',
            'expertise' => 'Lập trình Web',
            'bio' => 'Kinh nghiệm 10 năm phát triển phần mềm.',
            'social_links' => [
                'website' => 'https://example.com',
                'facebook' => 'https://facebook.com/example',
                'linkedin' => 'https://linkedin.com/in/example',
                'youtube' => 'https://youtube.com/@example',
            ],
        ];

        $response = $this->patchJson('/api/instructor/profile', $payload, $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Nguyễn Giảng Viên Mới')
            ->assertJsonPath('data.phone', '0988777666')
            ->assertJsonPath('data.expertise', 'Lập trình Web')
            ->assertJsonPath('data.bio', 'Kinh nghiệm 10 năm phát triển phần mềm.')
            ->assertJsonPath('data.social_links.website', 'https://example.com');
    }

    public function test_can_upload_avatar(): void
    {
        $mockCloudinary = \Mockery::mock(\App\Services\Storage\CloudinaryService::class);
        $mockCloudinary->shouldReceive('uploadImage')
            ->once()
            ->andReturn([
                'url' => 'https://example.com/avatar.jpg',
                'public_id' => 'avatars/avatar_123',
                'width' => 200,
                'height' => 200,
                'format' => 'jpg',
                'bytes' => 1024,
            ]);
        $this->app->instance(\App\Services\Storage\CloudinaryService::class, $mockCloudinary);

        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->postJson('/api/instructor/profile/avatar', [
            'avatar' => $file,
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['avatar', 'avatar_url']]);
    }

    public function test_can_get_and_update_notification_preferences(): void
    {
        $getRes = $this->getJson('/api/instructor/profile/notification-preferences', $this->getAuthHeaders($this->instructor->email));
        $getRes->assertStatus(200);

        $patchRes = $this->patchJson('/api/instructor/profile/notification-preferences', [
            'email_notifications' => false,
            'sms_alerts' => true,
        ], $this->getAuthHeaders($this->instructor->email));

        $patchRes->assertStatus(200)
            ->assertJsonPath('data.email_notifications', false)
            ->assertJsonPath('data.sms_alerts', true);
    }

    public function test_can_change_password(): void
    {
        $otpCode = \App\Models\UserOtp::generateOtp((int) $this->instructor->id, 'change_password');

        $response = $this->patchJson('/api/instructor/profile/password', [
            'current_password' => 'password',
            'new_password' => '87654321',
            'confirm_password' => '87654321',
            'password_confirmation' => '87654321',
            'otp' => $otpCode,
        ], $this->getAuthHeaders($this->instructor->email));

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('87654321', $this->instructor->fresh()->password_hash));
    }
}
