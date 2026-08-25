<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class InstructorProfileApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $instructor;
    private User $otherInstructor;
    private User $instructorWithoutProfile;
    private User $learner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        $suffix = str_replace('.', '-', uniqid('profile_', true));

        $this->instructor = $this->createUser(
            'Nguyễn Minh Khoa',
            'profile-instructor-' . $suffix . '@mindhub.test',
            'instructor',
            [
                'phone' => '0900000002',
                'email_verified_at' => now()->subMonth(),
                'last_login_at' => now()->subDay(),
            ]
        );

        $this->otherInstructor = $this->createUser(
            'Other Instructor',
            'other-profile-instructor-' . $suffix . '@mindhub.test',
            'instructor'
        );

        $this->instructorWithoutProfile = $this->createUser(
            'Instructor Empty',
            'empty-profile-instructor-' . $suffix . '@mindhub.test',
            'instructor'
        );

        $this->learner = $this->createUser(
            'Profile Learner',
            'profile-learner-' . $suffix . '@mindhub.test',
            'learner'
        );

        DB::table('instructor_profiles')->insert([
            'user_id' => $this->instructor->id,
            'bio' => 'Backend Developer chuyên Laravel.',
            'expertise' => 'Laravel, PHP, MySQL',
            'experience_years' => 6,
            'level' => 'Senior Backend Instructor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('instructor_profiles')->insert([
            'user_id' => $this->otherInstructor->id,
            'bio' => 'Hồ sơ của giảng viên khác',
            'expertise' => 'Java',
            'experience_years' => 10,
            'level' => 'Senior',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_instructor_can_get_complete_profile(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/profile');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.account.id', $this->instructor->id)
            ->assertJsonPath('data.account.full_name', 'Nguyễn Minh Khoa')
            ->assertJsonPath('data.account.email', $this->instructor->email)
            ->assertJsonPath('data.profile.bio', 'Backend Developer chuyên Laravel.')
            ->assertJsonPath('data.profile.expertise', 'Laravel, PHP, MySQL')
            ->assertJsonPath('data.profile.experience_years', 6)
            ->assertJsonPath('data.profile.level', 'Senior Backend Instructor')
            ->assertJsonPath('data.completion.completed_fields', 4)
            ->assertJsonPath('data.completion.total_fields', 4)
            ->assertJsonPath('data.completion.is_completed', true)
            ->assertJsonPath('data.completion.missing_fields', []);
    }

    public function test_profile_response_does_not_expose_sensitive_fields(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/profile');

        $response->assertOk()
            ->assertJsonMissingPath('data.account.password_hash')
            ->assertJsonMissingPath('data.account.password_reset')
            ->assertJsonMissingPath('data.account.locked')
            ->assertJsonMissingPath('data.account.locked_reason')
            ->assertJsonMissingPath('data.account.oauth_account_login');
    }

    public function test_instructor_without_profile_gets_null_profile_without_database_creation(): void
    {
        $this->assertDatabaseMissing('instructor_profiles', [
            'user_id' => $this->instructorWithoutProfile->id,
        ]);

        $response = $this->actingAs($this->instructorWithoutProfile)
            ->getJson('/api/instructor/profile');

        $response->assertOk()
            ->assertJsonPath('data.account.id', $this->instructorWithoutProfile->id)
            ->assertJsonPath('data.profile.bio', null)
            ->assertJsonPath('data.profile.expertise', null)
            ->assertJsonPath('data.profile.experience_years', null)
            ->assertJsonPath('data.profile.level', null)
            ->assertJsonPath('data.completion.completed_fields', 0)
            ->assertJsonPath('data.completion.is_completed', false)
            ->assertJsonPath('data.completion.missing_fields', [
                'bio',
                'expertise',
                'experience_years',
                'level',
            ]);

        $this->assertDatabaseMissing('instructor_profiles', [
            'user_id' => $this->instructorWithoutProfile->id,
        ]);
    }

    public function test_instructor_can_update_full_name(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/account', [
                'full_name' => 'Nguyễn Minh Khoa Updated',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $this->instructor->id)
            ->assertJsonPath('data.full_name', 'Nguyễn Minh Khoa Updated')
            ->assertJsonPath('data.email', $this->instructor->email)
            ->assertJsonPath('data.role', 'instructor')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('users', [
            'id' => $this->instructor->id,
            'full_name' => 'Nguyễn Minh Khoa Updated',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->otherInstructor->id,
            'full_name' => 'Other Instructor',
        ]);
    }

    public function test_account_update_requires_full_name(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/account', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name']);
    }

    public function test_account_update_rejects_empty_full_name(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/account', [
                'full_name' => '   ',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name']);
    }

    public function test_account_update_rejects_email(): void
    {
        $oldEmail = $this->instructor->email;

        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/account', [
                'full_name' => 'Nguyễn Minh Khoa',
                'email' => 'new-profile-email@mindhub.test',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseHas('users', [
            'id' => $this->instructor->id,
            'email' => $oldEmail,
        ]);
    }

    public function test_account_update_rejects_phone_until_otp_is_available(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/account', [
                'full_name' => 'Nguyễn Minh Khoa',
                'phone' => '0900000099',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseHas('users', [
            'id' => $this->instructor->id,
            'phone' => '0900000002',
        ]);
    }

    public function test_account_update_rejects_sensitive_fields(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/account', [
                'full_name' => 'Nguyễn Minh Khoa',
                'role' => 'admin',
                'status' => 'locked',
                'locked' => true,
                'email_verified_at' => now()->toDateTimeString(),
                'last_login_at' => now()->toDateTimeString(),
                'password_hash' => 'hacked',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'role',
                'status',
                'locked',
                'email_verified_at',
                'last_login_at',
                'password_hash',
            ]);
    }

    public function test_instructor_can_update_existing_introduction(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/introduction', [
                'bio' => 'Tôi là giảng viên Backend Laravel và REST API.',
            ]);

        $response->assertOk()
            ->assertJsonPath(
                'data.bio',
                'Tôi là giảng viên Backend Laravel và REST API.'
            )
            ->assertJsonPath('data.completion.completed_fields', 4)
            ->assertJsonPath('data.completion.is_completed', true);

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructor->id,
            'bio' => 'Tôi là giảng viên Backend Laravel và REST API.',
        ]);
    }

    public function test_introduction_creates_profile_when_missing(): void
    {
        $response = $this->actingAs($this->instructorWithoutProfile)
            ->patchJson('/api/instructor/profile/introduction', [
                'bio' => 'Giới thiệu lần đầu.',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.bio', 'Giới thiệu lần đầu.');

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructorWithoutProfile->id,
            'bio' => 'Giới thiệu lần đầu.',
        ]);
    }

    public function test_instructor_can_clear_introduction(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/introduction', [
                'bio' => null,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.bio', null)
            ->assertJsonPath('data.completion.is_completed', false);

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructor->id,
            'bio' => null,
        ]);
    }

    public function test_introduction_rejects_too_long_bio(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/introduction', [
                'bio' => str_repeat('a', 5001),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['bio']);
    }

    public function test_introduction_rejects_profile_fields_from_other_group(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/introduction', [
                'bio' => 'Giới thiệu',
                'expertise' => 'Laravel',
                'experience_years' => 5,
                'level' => 'Senior',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'expertise',
                'experience_years',
                'level',
            ]);
    }

    public function test_instructor_can_update_expertise(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/expertise', [
                'expertise' => 'Laravel, PHP, MySQL, API Design',
                'experience_years' => 8,
                'level' => 'Lead Backend Instructor',
            ]);

        $response->assertOk()
            ->assertJsonPath(
                'data.expertise',
                'Laravel, PHP, MySQL, API Design'
            )
            ->assertJsonPath('data.experience_years', 8)
            ->assertJsonPath('data.level', 'Lead Backend Instructor')
            ->assertJsonPath('data.completion.completed_fields', 4)
            ->assertJsonPath('data.completion.is_completed', true);

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructor->id,
            'experience_years' => 8,
            'level' => 'Lead Backend Instructor',
        ]);
    }

    public function test_expertise_update_creates_profile_when_missing(): void
    {
        $response = $this->actingAs($this->instructorWithoutProfile)
            ->patchJson('/api/instructor/profile/expertise', [
                'expertise' => 'PHP, Laravel',
                'experience_years' => 2,
                'level' => 'Junior Instructor',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.expertise', 'PHP, Laravel')
            ->assertJsonPath('data.experience_years', 2)
            ->assertJsonPath('data.level', 'Junior Instructor');

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->instructorWithoutProfile->id,
            'expertise' => 'PHP, Laravel',
            'experience_years' => 2,
            'level' => 'Junior Instructor',
        ]);
    }

    public function test_expertise_update_allows_zero_years(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/expertise', [
                'experience_years' => 0,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.experience_years', 0);
    }

    public function test_expertise_update_rejects_negative_years(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/expertise', [
                'experience_years' => -1,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['experience_years']);
    }

    public function test_expertise_update_rejects_more_than_eighty_years(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/expertise', [
                'experience_years' => 81,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['experience_years']);
    }

    public function test_expertise_update_rejects_long_level(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/expertise', [
                'level' => str_repeat('a', 51),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['level']);
    }

    public function test_expertise_update_requires_at_least_one_field(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/expertise', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['payload']);
    }

    public function test_expertise_update_rejects_bio_and_user_id(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/expertise', [
                'expertise' => 'Laravel',
                'bio' => 'Không được sửa từ API này',
                'user_id' => $this->otherInstructor->id,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['bio', 'user_id']);
    }

    public function test_instructor_can_get_completion(): void
    {
        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/profile/completion');

        $response->assertOk()
            ->assertJsonPath('data.completed_fields', 4)
            ->assertJsonPath('data.total_fields', 4)
            ->assertJsonPath('data.is_completed', true)
            ->assertJsonPath('data.missing_fields', []);
    }

    public function test_completion_returns_all_missing_fields_without_profile(): void
    {
        $response = $this->actingAs($this->instructorWithoutProfile)
            ->getJson('/api/instructor/profile/completion');

        $response->assertOk()
            ->assertJsonPath('data.completed_fields', 0)
            ->assertJsonPath('data.total_fields', 4)
            ->assertJsonPath('data.is_completed', false)
            ->assertJsonPath('data.missing_fields.0.field', 'bio')
            ->assertJsonPath('data.missing_fields.1.field', 'expertise')
            ->assertJsonPath('data.missing_fields.2.field', 'experience_years')
            ->assertJsonPath('data.missing_fields.3.field', 'level');
    }

    public function test_completion_detects_partial_profile(): void
    {
        DB::table('instructor_profiles')
            ->where('user_id', $this->instructor->id)
            ->update([
                'bio' => 'Có giới thiệu',
                'expertise' => null,
                'experience_years' => 3,
                'level' => null,
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($this->instructor)
            ->getJson('/api/instructor/profile/completion');

        $response->assertOk()
            ->assertJsonPath('data.completed_fields', 2)
            ->assertJsonPath('data.total_fields', 4)
            ->assertJsonPath('data.is_completed', false)
            ->assertJsonPath('data.missing_fields.0.field', 'expertise')
            ->assertJsonPath('data.missing_fields.1.field', 'level');
    }

    public function test_profile_endpoints_never_update_other_instructor(): void
    {
        $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/introduction', [
                'bio' => 'Bio mới của instructor hiện tại',
            ])
            ->assertOk();

        $this->assertDatabaseHas('instructor_profiles', [
            'user_id' => $this->otherInstructor->id,
            'bio' => 'Hồ sơ của giảng viên khác',
        ]);
    }

    public function test_avatar_upload_updates_users_table_database_column(): void
    {
        $mockCloudinary = \Mockery::mock(\App\Services\Storage\CloudinaryService::class);
        $mockCloudinary->shouldReceive('uploadImage')
            ->once()
            ->andReturn([
                'url' => 'https://example.com/storage/avatars/avatar.jpg',
                'public_id' => 'avatars/avatar_123',
                'width' => 200,
                'height' => 200,
                'format' => 'jpg',
                'bytes' => 1024,
            ]);
        $this->app->instance(\App\Services\Storage\CloudinaryService::class, $mockCloudinary);

        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this->actingAs($this->instructor)
            ->postJson('/api/instructor/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->instructor->refresh();
        $this->assertNotNull($this->instructor->avatar_url);
        $this->assertStringContainsString('storage/avatars/', $this->instructor->avatar_url);

        // Verify auth/me returns the updated avatar_url
        $meResponse = $this->actingAs($this->instructor)->getJson('/api/users/me');
        $meResponse->assertOk()
            ->assertJsonPath('data.avatar_url', $this->instructor->avatar_url);
    }

    public function test_select_avatar_preset_updates_users_table_database_column(): void
    {
        $response = $this->actingAs($this->instructor)
            ->patchJson('/api/instructor/profile/avatar/preset', [
                'preset_id' => 'avatar_01',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->instructor->refresh();
        $this->assertNotNull($this->instructor->avatar_url);
        $this->assertStringContainsString('ui-avatars.com', $this->instructor->avatar_url);
    }

    public function test_delete_avatar_clears_users_table_database_column(): void
    {
        $this->instructor->avatar_url = 'https://ui-avatars.com/api/?name=Test';
        $this->instructor->save();

        $response = $this->actingAs($this->instructor)
            ->deleteJson('/api/instructor/profile/avatar');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->instructor->refresh();
        $this->assertNull($this->instructor->avatar_url);
    }

    private function createUser(
        string $fullName,
        string $email,
        string $role,
        array $overrides = []
    ): User {
        return User::query()->create(array_merge([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => null,
            'phone' => null,
            'oauth_account_login' => null,
            'role' => $role,
            'status' => 'active',
            'email_verified_at' => null,
            'last_login_at' => null,
            'locked' => false,
            'locked_reason' => null,
            'password_reset' => null,
        ], $overrides));
    }
}