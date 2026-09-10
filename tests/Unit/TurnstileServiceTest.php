<?php

namespace Tests\Unit;

use App\Services\TurnstileService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileServiceTest extends TestCase
{
    protected string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    protected string $secretKey = 'test-secret-key';
    protected string $expectedHostname = 'mkkssmkbekasi.or.id';

    protected function getService(?string $hostname = null): TurnstileService
    {
        return new TurnstileService(
            secretKey: $this->secretKey,
            verifyUrl: $this->verifyUrl,
            expectedHostname: $hostname ?? $this->expectedHostname,
            timeout: 2
        );
    }

    public function test_it_rejects_missing_or_null_token(): void
    {
        $service = $this->getService();

        $this->assertFalse($service->verify(null));
    }

    public function test_it_rejects_empty_or_whitespace_token(): void
    {
        $service = $this->getService();

        $this->assertFalse($service->verify(''));
        $this->assertFalse($service->verify('   '));
    }

    public function test_it_rejects_when_secret_key_is_missing(): void
    {
        $service = new TurnstileService(
            secretKey: '',
            verifyUrl: $this->verifyUrl
        );

        $this->assertFalse($service->verify('any-token'));
    }

    public function test_it_verifies_successfully_with_valid_cloudflare_response(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => true,
                'hostname' => $this->expectedHostname,
                'action' => 'login',
                'challenge_ts' => '2026-09-09T08:00:00.000Z',
            ], 200),
        ]);

        $service = $this->getService();
        $result = $service->verify('valid-token', '127.0.0.1', 'login');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === $this->verifyUrl &&
                $request['secret'] === $this->secretKey &&
                $request['response'] === 'valid-token' &&
                $request['remoteip'] === '127.0.0.1';
        });
    }

    public function test_it_rejects_when_cloudflare_returns_success_false(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $service = $this->getService();
        $result = $service->verify('invalid-token', '127.0.0.1', 'login');

        $this->assertFalse($result);
    }

    public function test_it_rejects_when_cloudflare_returns_http_error_status(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response('Service Unavailable', 503),
        ]);

        $service = $this->getService();
        $result = $service->verify('valid-token');

        $this->assertFalse($result);
    }

    public function test_it_rejects_when_response_is_malformed(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response('NOT-JSON', 200),
        ]);

        $service = $this->getService();
        $result = $service->verify('some-token');

        $this->assertFalse($result);
    }

    public function test_it_rejects_and_fails_closed_on_network_connection_exception(): void
    {
        Http::fake([
            $this->verifyUrl => function () {
                throw new ConnectionException('Could not resolve host');
            },
        ]);

        $service = $this->getService();
        $result = $service->verify('token-before-failure');

        $this->assertFalse($result);
    }

    public function test_it_rejects_when_hostname_mismatches(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => true,
                'hostname' => 'phishing-domain.com',
                'action' => 'login',
            ], 200),
        ]);

        $service = $this->getService('mkkssmkbekasi.or.id');
        $result = $service->verify('valid-token', null, 'login');

        $this->assertFalse($result);
    }

    public function test_it_rejects_when_action_mismatches(): void
    {
        Http::fake([
            $this->verifyUrl => Http::response([
                'success' => true,
                'hostname' => $this->expectedHostname,
                'action' => 'register',
            ], 200),
        ]);

        $service = $this->getService();
        $result = $service->verify('valid-token', null, 'login');

        $this->assertFalse($result);
    }
}
