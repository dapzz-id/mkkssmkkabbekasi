<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lupa Kata Sandi - MKKS SMK Kabupaten Bekasi</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('img/ic_MKKS.png') }}" type="image/png">
    <link rel="icon" href="{{ asset('img/ic_MKKS.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('dist/css/main.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
    <style>
        .auth-page * {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .auth-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 50%, #f4f0ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .auth-card {
            box-shadow:
                0 1px 3px rgba(0,0,0,0.04),
                0 8px 30px rgba(0,0,0,0.08),
                0 30px 60px rgba(0,0,0,0.05);
        }
        .brand-bar {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        }
        .input-icon-wrap {
            position: relative;
        }
        .input-icon-wrap svg.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s;
        }
        .input-icon-wrap input {
            padding-left: 44px;
        }
        .input-icon-wrap:focus-within svg.input-icon { color: #2563eb; }
        .btn-primary {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
        }
        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.45);
            transform: translateY(-1px);
        }
        .btn-primary:active:not(:disabled) { transform: translateY(0); }
        .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-anim { animation: slideInDown 0.25s ease; }
        /* Turnstile container */
        .turnstile-wrap {
            width: 100%;
            min-height: 65px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: visible;
        }
        @media (max-width: 380px) {
            .turnstile-wrap {
                transform: scale(0.88);
                transform-origin: center center;
            }
        }
        .turnstile-wrap > div {
            width: 100%;
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-card w-full max-w-[440px] rounded-2xl overflow-hidden bg-white">

        {{-- Brand bar --}}
        <div class="brand-bar px-6 py-4 flex items-center gap-3">
            <img src="{{ asset('img/ic_MKKS.png') }}" alt="MKKS" class="h-9 w-auto object-contain">
            <div>
                <p class="text-white font-bold text-sm leading-tight">MKKS SMK Kabupaten Bekasi</p>
                <p class="text-blue-200 text-xs">Panel Admin</p>
            </div>
        </div>

        {{-- Form body --}}
        <div class="p-5 sm:p-8">

            {{-- Heading --}}
            <div class="mb-6">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-4 shrink-0">
                    <svg class="w-6 h-6 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Lupa kata sandi?</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1.5 leading-relaxed">
                    Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan aman untuk mengatur ulang kata sandi Anda.
                </p>
            </div>

            {{-- Success / info alert --}}
            @if (session('status'))
                <div class="alert-anim mb-5 flex items-start gap-3 p-3.5 sm:p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm" role="alert">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-bold mb-0.5">Permintaan terkirim</p>
                        <p>{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="alert-anim mb-5 flex items-start gap-3 p-3.5 sm:p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm" role="alert">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li class="font-medium">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('password.email') }}" method="POST" id="forgotForm" novalidate class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        Alamat email <span class="text-rose-500">*</span>
                    </label>
                    <div class="input-icon-wrap">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@contoh.com"
                            class="w-full h-[48px] sm:h-[52px] pr-4 rounded-xl text-sm font-medium text-slate-800 placeholder:text-slate-400 border border-slate-300 bg-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500
                                @error('email') border-rose-400 bg-rose-50/30 @endif"
                        >
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    @error('email')
                        <p class="text-rose-600 text-xs mt-1.5 font-medium" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Security note --}}
                <div class="flex items-start gap-2.5 p-3 sm:p-3.5 rounded-xl bg-amber-50 border border-amber-200">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-amber-600" style="width: 20px; height: 20px; min-width: 20px; max-width: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-amber-800 text-xs leading-relaxed font-medium">
                        Tautan reset kata sandi akan dikirim ke email terdaftar dan <strong>kedaluwarsa dalam 60 menit</strong>.
                    </p>
                </div>

                {{-- Cloudflare Turnstile Verification --}}
                <div class="turnstile-wrap my-1">
                    <x-turnstile action="forgot_password" theme="light" size="flexible" />
                </div>

                <button
                    type="submit"
                    id="btnSubmit"
                    disabled
                    class="btn-primary w-full h-[52px] rounded-xl text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span id="btnSubmitText">Kirim Tautan Reset</span>
                    <svg id="btnSubmitSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </form>

            {{-- Back to login --}}
            <div class="mt-5 text-center">
                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-blue-700 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke halaman masuk
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form    = document.getElementById('forgotForm');
            var btn     = document.getElementById('btnSubmit');
            var btnText = document.getElementById('btnSubmitText');
            var spinner = document.getElementById('btnSubmitSpinner');

            // Turnstile State Management
            var originalOnTurnstileSuccess = window.onTurnstileSuccess;
            var originalOnTurnstileExpired = window.onTurnstileExpired;
            var originalOnTurnstileError = window.onTurnstileError;

            window.onTurnstileSuccess = function(token) {
                if (originalOnTurnstileSuccess) originalOnTurnstileSuccess(token);
                if (btn) btn.disabled = false;
            };

            window.onTurnstileExpired = function() {
                if (originalOnTurnstileExpired) originalOnTurnstileExpired();
                if (btn) btn.disabled = true;
            };

            window.onTurnstileError = function() {
                if (originalOnTurnstileError) originalOnTurnstileError();
                if (btn) btn.disabled = true;
            };

            // Initial state check (e.g. if back-forward cache or turnstile not enabled)
            setTimeout(function() {
                var widget = form ? form.querySelector('#turnstile-widget') : null;
                var initialToken = form ? form.querySelector('[name="cf-turnstile-response"]') : null;
                if (!widget && btn) {
                    btn.disabled = false;
                } else if (initialToken && initialToken.value && btn) {
                    btn.disabled = false;
                }
            }, 500);

            if (form && btn) {
                form.addEventListener('submit', function (e) {
                    var widget = form.querySelector('#turnstile-widget');
                    var currentToken = form.querySelector('[name="cf-turnstile-response"]');
                    if (widget && (!currentToken || !currentToken.value)) {
                        e.preventDefault();
                        return;
                    }
                    btn.disabled = true;
                    btnText.textContent = 'Mengirim...';
                    spinner.classList.remove('hidden');
                });
            }
        });
    </script>
</body>
</html>
