<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $divisi = Divisi::create(['nama_divisi' => 'Teknologi Informasi']);

        $this->testUser = User::create([
            'divisi_uuid' => $divisi->uuid,
            'name'      => 'Admin Test',
            'username'  => 'admintest',
            'email'     => 'admin@mkkssmkbekasi.or.id',
            'password'  => Hash::make('OldPassword123'),
            'role'      => 'admin',
            'alamat'    => 'Bekasi',
        ]);
    }

    // ── 1. Route accessibility ──────────────────────────────────────

    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Lupa kata sandi');
    }

    public function test_reset_password_page_is_accessible_with_token(): void
    {
        $response = $this->get(route('password.reset', [
            'token' => 'dummy-token',
            'email' => $this->testUser->email,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Atur ulang kata sandi');
    }

    // ── 2. Forgot password — input validation ──────────────────────

    public function test_forgot_password_requires_email(): void
    {
        $response = $this->post(route('password.email'), []);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_forgot_password_requires_valid_email_format(): void
    {
        $response = $this->post(route('password.email'), ['email' => 'not-an-email']);

        $response->assertSessionHasErrors(['email']);
    }

    // ── 3. Forgot password — Turnstile verification ────────────────

    public function test_forgot_password_requires_turnstile_response(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => $this->testUser->email,
            // cf-turnstile-response omitted
        ]);

        $response->assertSessionHasErrors(['cf-turnstile-response']);
    }

    public function test_forgot_password_fails_when_turnstile_is_invalid(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $this->testUser->email,
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors(['cf-turnstile-response']);
    }

    protected function fakeTurnstileSuccess(string $action = 'forgot_password'): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => true,
                'hostname' => 'mkkssmkbekasi.or.id',
                'action' => $action,
                'challenge_ts' => now()->toIso8601String(),
            ], 200),
        ]);
    }

    public function test_forgot_password_fails_when_turnstile_action_mismatches(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => true,
                'hostname' => 'mkkssmkbekasi.or.id',
                'action' => 'login', // mismatched action
            ], 200),
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $this->testUser->email,
            'cf-turnstile-response' => 'some-token',
        ]);

        $response->assertSessionHasErrors(['cf-turnstile-response']);
    }

    // ── 4. Forgot password — security: user enumeration prevention ──

    public function test_forgot_password_returns_generic_response_for_nonexistent_email(): void
    {
        $this->fakeTurnstileSuccess();
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        // SECURITY: Must NOT redirect to error; always redirect with session 'status'
        // to prevent user enumeration (whether email exists or not).
        $response->assertSessionHas('status');
        Notification::assertNothingSent(); // No email sent for nonexistent user
    }

    public function test_forgot_password_sends_notification_for_existing_email(): void
    {
        $this->fakeTurnstileSuccess();
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'email' => $this->testUser->email,
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($this->testUser, \App\Notifications\ResetPasswordNotification::class);
    }

    // ── 4. Reset password — token validation ──────────────────────

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $response = $this->post(route('password.update'), [
            'token'                 => 'completely-invalid-token',
            'email'                 => $this->testUser->email,
            'password'              => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        // Should redirect back with error (invalid token)
        $response->assertSessionHasErrors(['email']);
    }

    // ── 5. Reset password — input validation ──────────────────────

    public function test_reset_password_requires_all_fields(): void
    {
        $response = $this->post(route('password.update'), []);

        $response->assertSessionHasErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_requires_confirmation_match(): void
    {
        $token = Password::broker()->createToken($this->testUser);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $this->testUser->email,
            'password'              => 'NewPassword123',
            'password_confirmation' => 'DifferentPassword456',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_reset_password_requires_minimum_length(): void
    {
        $token = Password::broker()->createToken($this->testUser);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $this->testUser->email,
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // ── 6. Reset password — happy path ────────────────────────────

    public function test_reset_password_succeeds_with_valid_token_and_strong_password(): void
    {
        $token = Password::broker()->createToken($this->testUser);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $this->testUser->email,
            'password'              => 'NewSecure@123',
            'password_confirmation' => 'NewSecure@123',
        ]);

        // Should redirect to login with success status
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // Password must be updated in the database
        $this->testUser->refresh();
        $this->assertTrue(Hash::check('NewSecure@123', $this->testUser->password));
        $this->assertFalse(Hash::check('OldPassword123', $this->testUser->password));
    }

    // ── 7. Token cannot be reused ──────────────────────────────────

    public function test_reset_token_cannot_be_reused_after_successful_reset(): void
    {
        $token = Password::broker()->createToken($this->testUser);

        // First reset — should succeed
        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $this->testUser->email,
            'password'              => 'FirstNew@123',
            'password_confirmation' => 'FirstNew@123',
        ]);

        // Second attempt with the same token — should fail
        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $this->testUser->email,
            'password'              => 'SecondNew@456',
            'password_confirmation' => 'SecondNew@456',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    // ── 8. Rate limiting ──────────────────────────────────────────

    public function test_forgot_password_is_rate_limited(): void
    {
        // Submit 5 times (allowed threshold)
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), [
                'email' => 'test@example.com',
            ]);
        }

        // 6th should be throttled
        $response = $this->post(route('password.email'), [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(429);
    }
}
