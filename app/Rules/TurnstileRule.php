<?php

namespace App\Rules;

use App\Services\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TurnstileRule implements ValidationRule
{
    protected TurnstileService $service;
    protected ?string $action;

    public function __construct(?TurnstileService $service = null, ?string $action = null)
    {
        $this->service = $service ?? app(TurnstileService::class);
        $this->action = $action;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $token = is_string($value) ? $value : null;
        $ip = request()->ip();

        if (!$this->service->verify($token, $ip, $this->action)) {
            $fail('Verifikasi keamanan gagal. Silakan coba lagi.');
        }
    }
}
