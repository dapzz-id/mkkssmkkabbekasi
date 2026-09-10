<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileService
{
    protected string $secretKey;
    protected string $verifyUrl;
    protected ?string $expectedHostname;
    protected int $timeout;

    public function __construct(
        ?string $secretKey = null,
        ?string $verifyUrl = null,
        ?string $expectedHostname = null,
        ?int $timeout = null
    ) {
        $this->secretKey = $secretKey ?? (string) config('services.turnstile.secret_key', '');
        $this->verifyUrl = $verifyUrl ?? (string) config('services.turnstile.verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
        $this->expectedHostname = $expectedHostname ?? config('services.turnstile.hostname');
        $this->timeout = $timeout ?? (int) config('services.turnstile.timeout', 5);
    }

    /**
     * Verify a Cloudflare Turnstile token server-side.
     *
     * Returns true on successful verification, false on failure (fail-closed).
     *
     * @param string|null $token The cf-turnstile-response token submitted by client
     * @param string|null $remoteIp Optional client IP address
     * @param string|null $expectedAction Optional expected action name (e.g. 'login')
     * @return bool
     */
    public function verify(?string $token, ?string $remoteIp = null, ?string $expectedAction = null): bool
    {
        // 1. Fail-closed on missing, null, or empty token
        if (empty($token) || !is_string($token) || trim($token) === '') {
            return false;
        }

        // 2. Fail-closed if secret key is missing/unconfigured
        if (empty($this->secretKey)) {
            Log::error('Turnstile verification failed: Secret key is not configured.');
            return false;
        }

        try {
            $payload = [
                'secret' => $this->secretKey,
                'response' => trim($token),
            ];

            if (!empty($remoteIp)) {
                $payload['remoteip'] = $remoteIp;
            }

            // 3. Perform HTTP request with bounded timeout, no blind retries
            $response = Http::asForm()
                ->timeout($this->timeout)
                ->connectTimeout(min($this->timeout, 3))
                ->post($this->verifyUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Turnstile verification rejected: HTTP status ' . $response->status());
                return false;
            }

            $body = $response->json();

            // 4. Validate success flag
            if (!is_array($body) || empty($body['success']) || $body['success'] !== true) {
                // Do NOT log secret key or full response token. Log safe metadata only.
                Log::warning('Turnstile verification rejected: Cloudflare returned success=false', [
                    'error_codes' => $body['error-codes'] ?? [],
                ]);
                return false;
            }

            // 5. Validate hostname if configured
            if (!empty($this->expectedHostname)) {
                // Strip any inline comments if present in env (e.g. "localhost #in prod use:...")
                $cleanExpected = trim(explode('#', $this->expectedHostname)[0]);

                if (!empty($cleanExpected)) {
                    $receivedHostname = strtolower(trim((string) ($body['hostname'] ?? '')));
                    $allowedHostnames = array_map('strtolower', array_map('trim', explode(',', $cleanExpected)));

                    // Support localhost and 127.0.0.1 interchangeably in development
                    if (in_array('localhost', $allowedHostnames, true) && !in_array('127.0.0.1', $allowedHostnames, true)) {
                        $allowedHostnames[] = '127.0.0.1';
                    }
                    if (in_array('127.0.0.1', $allowedHostnames, true) && !in_array('localhost', $allowedHostnames, true)) {
                        $allowedHostnames[] = 'localhost';
                    }

                    // Also accept current request host in local environment
                    if (app()->environment('local')) {
                        $currentHost = strtolower(request()->getHost());
                        if (!empty($currentHost) && !in_array($currentHost, $allowedHostnames, true)) {
                            $allowedHostnames[] = $currentHost;
                        }
                    }

                    if (!in_array($receivedHostname, $allowedHostnames, true)) {
                        Log::warning('Turnstile verification rejected: Hostname mismatch', [
                            'expected' => $cleanExpected,
                            'allowed' => $allowedHostnames,
                            'received' => $receivedHostname,
                        ]);
                        return false;
                    }
                }
            }

            // 6. Validate action if expected
            if (!empty($expectedAction)) {
                $receivedAction = $body['action'] ?? null;
                if ($receivedAction !== null && $receivedAction !== $expectedAction) {
                    Log::warning('Turnstile verification rejected: Action mismatch');
                    return false;
                }
            }

            return true;
        } catch (Throwable $e) {
            // Fail-closed on any timeout, connection error, or unexpected exception
            Log::warning('Turnstile verification error: Network or service exception during verification', [
                'error_type' => get_class($e),
            ]);
            return false;
        }
    }
}
