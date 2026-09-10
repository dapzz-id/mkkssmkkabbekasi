<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginTurnstileTest extends TestCase
{
    use RefreshDatabase;

    protected string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create requisite Divisi and User for auth testing
        $divisi = Divisi::create([
            'nama_divisi' => 'Teknologi Informasi',
        ]);

        $this->testUser = User::create([
            'id_divisi' => $divisi->id,
            'name' => 'Admin Test',
            'username' => 'admintest',
            'email' => 'admin@mkkssmkbekasi.or.id',
            'password' => Hash::make('secretpassword123'),
            'role' => 'admin',
            'alamat' => 'Bekasi',
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login Admin');
    }

    public function test_successful_login_with_valid_credentials_and_valid_turnstile(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => true,
                'hostname' => 'mkkssmkbekasi.or.id',
                'action' => 'login',
                'challenge_ts' => now()->toIso8601String(),
            ], 200),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@mkkssmkbekasi.or.id',
            'password' => 'secretpassword123',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->testUser);
    }

    public function test_login_fails_when_turnstile_token_is_missing(): void
    {
        Http::fake();

        $response = $this->post('/login', [
            'email' => 'admin@mkkssmkbekasi.or.id',
            'password' => 'secretpassword123',
            // cf-turnstile-response omitted
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_login_fails_when_turnstile_verification_is_rejected(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@mkkssmkbekasi.or.id',
            'password' => 'secretpassword123',
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
    }

    public function test_login_fails_closed_when_cloudflare_times_out(): void
    {
        Http::fake([
            $this->verifyUrl => function () {
                throw new ConnectionException('Cloudflare API timeout');
            },
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@mkkssmkbekasi.or.id',
            'password' => 'secretpassword123',
            'cf-turnstile-response' => 'some-token',
        ]);

        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
    }

    public function test_validation_order_rejects_malformed_input_before_calling_turnstile(): void
    {
        Http::fake();

        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => '',
            'cf-turnstile-response' => 'some-token',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
        // Crucial security requirement: Never invoke Cloudflare Siteverify if input validation fails
        Http::assertNothingSent();
    }

    public function test_login_rate_limiting_throttles_excessive_attempts(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response(['success' => false], 200),
        ]);

        // Attempt 5 requests (the allowed threshold per minute)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'admin@mkkssmkbekasi.or.id',
                'password' => 'wrongpassword',
                'cf-turnstile-response' => 'token-' . $i,
            ]);
            $response->assertStatus(302); // Redirect back on failure
        }

        // 6th attempt should be blocked by rate limiter (HTTP 429 Too Many Requests)
        $response = $this->post('/login', [
            'email' => 'admin@mkkssmkbekasi.or.id',
            'password' => 'wrongpassword',
            'cf-turnstile-response' => 'token-6',
        ]);

        $response->assertStatus(429);
    }
}
