@props([
    'action' => 'login',
    'theme' => 'dark',
    'size' => 'normal',
])

@php
    $siteKey = config('services.turnstile.site_key');
@endphp

@if(!empty($siteKey))
    <div class="turnstile-container my-3 flex flex-col items-center justify-center">
        <div
            class="cf-turnstile"
            id="turnstile-widget"
            data-sitekey="{{ $siteKey }}"
            data-action="{{ $action }}"
            data-theme="{{ $theme }}"
            data-size="{{ $size }}"
            data-callback="onTurnstileSuccess"
            data-expired-callback="onTurnstileExpired"
            data-error-callback="onTurnstileError"
        ></div>
        @error('cf-turnstile-response')
            <div class="{{ $theme === 'light' ? 'text-rose-600' : 'text-red-300' }} text-xs mt-1 text-center font-semibold" role="alert">
                {{ $message }}
            </div>
        @enderror
    </div>

    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <script>
            function onTurnstileSuccess(token) {
                const alertEl = document.getElementById('turnstile-waiting-msg');
                if (alertEl) {
                    alertEl.remove();
                }
            }
            function onTurnstileExpired() {
                if (window.turnstile) {
                    window.turnstile.reset('#turnstile-widget');
                }
            }
            function onTurnstileError() {
                if (window.turnstile) {
                    window.turnstile.reset('#turnstile-widget');
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const forms = document.querySelectorAll('form');
                forms.forEach(function(form) {
                    form.addEventListener('submit', function(e) {
                        const widget = form.querySelector('#turnstile-widget');
                        if (widget) {
                            const tokenInput = form.querySelector('[name="cf-turnstile-response"]');
                            if (!tokenInput || !tokenInput.value) {
                                e.preventDefault();
                                let alertEl = document.getElementById('turnstile-waiting-msg');
                                if (!alertEl) {
                                    alertEl = document.createElement('div');
                                    alertEl.id = 'turnstile-waiting-msg';
                                    alertEl.className = '{{ $theme === 'light' ? 'text-amber-700' : 'text-amber-300' }} text-xs mt-1 text-center font-semibold animate-pulse';
                                    widget.parentNode.appendChild(alertEl);
                                }
                                alertEl.textContent = 'Sedang memverifikasi keamanan... Silakan tunggu sejenak sebelum melanjutkan.';
                            }
                        }
                    });
                });
            });
        </script>
    @endonce
@else
    @error('cf-turnstile-response')
        <div class="{{ $theme === 'light' ? 'text-rose-600' : 'text-red-300' }} text-xs mt-1 text-center font-semibold" role="alert">
            {{ $message }}
        </div>
    @enderror
@endif
