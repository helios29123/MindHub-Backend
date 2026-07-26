<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private string $password = '12345678';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'full_name' => 'Auth Test User',
            'email' => 'authtest_' . uniqid() . '@example.com',
            'password_hash' => Hash::make($this->password),
            'role' => User::ROLE_LEARNER,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'locked' => false,
        ]);
    }

    /**
     * TEST 1: Login success creates session and returns user data
     */
    public function test_login_success_with_valid_credentials_and_creates_session()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => $this->password,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', $this->user->email);

        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * TEST 2: Login fails with invalid password (401)
     */
    public function test_login_fails_with_invalid_password()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /**
     * TEST 3: Login fails when account is inactive or locked (403)
     */
    public function test_login_fails_when_account_inactive_or_locked()
    {
        $this->user->update(['status' => User::STATUS_INACTIVE]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => $this->password,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * TEST 4: GET /api/auth/me returns authenticated user from session
     */
    public function test_current_user_me_returns_authenticated_user_from_session()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $this->user->id)
            ->assertJsonPath('data.user.email', $this->user->email);
    }

    /**
     * TEST 5: GET /api/auth/me returns 401 when unauthenticated
     */
    public function test_current_user_me_returns_401_when_unauthenticated()
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /**
     * TEST 6: POST /api/auth/logout invalidates session
     */
    public function test_logout_invalidates_session()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertGuest();
    }

    /**
     * TEST 7: GET /api/auth/google/redirect returns 503 when Google OAuth is not configured
     */
    public function test_google_redirect_returns_503_when_not_configured()
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->getJson('/api/auth/google/redirect');

        $response->assertStatus(503)
            ->assertJsonPath('success', false);
    }

    /**
     * TEST 8: GET /api/auth/google/redirect returns Google OAuth URL when configured
     */
    public function test_google_redirect_returns_authorization_url()
    {
        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-client-secret',
            'services.google.redirect' => 'http://localhost:8000/auth/google/callback',
        ]);

        $response = $this->getJson('/api/auth/google/redirect');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $url = $response->json('data.url');
        $this->assertStringContainsString('https://accounts.google.com/o/oauth2/v2/auth', $url);
        $this->assertStringContainsString('test-google-client-id', $url);
        $this->assertStringContainsString('redirect_uri=' . urlencode('http://localhost:8000/auth/google/callback'), $url);
    }

    /**
     * TEST 8: POST /api/auth/forgot-password returns generic success
     */
    public function test_forgot_password_returns_generic_success()
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
