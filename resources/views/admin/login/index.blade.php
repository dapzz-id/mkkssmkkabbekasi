@if (Auth::check())
    @php
        header("Location: /dashboard");
        exit();
    @endphp
@else
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Admin - MKKS SMK Kabupaten Bekasi</title>
    <meta name="description" content="Panel Admin MKKS SMK Kabupaten Bekasi — Masuk dengan akun resmi Anda.">
    <link rel="icon" href="{{ asset('img/ic_MKKS.png') }}" type="image/png">
    <link rel="icon" href="{{ asset('img/ic_MKKS.ico') }}" type="image/x-icon">
    {{-- Use project's compiled Tailwind CSS (PostCSS pipeline via npm run watch) --}}
    <link rel="stylesheet" href="{{ asset('dist/css/main.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
    <style>
        /* ─── Scoped to login page only ─── */
        .login-page * {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 50%, #f4f0ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        /* Soft shimmer on the brand panel gradient */
        .brand-panel {
            background: linear-gradient(145deg, #3b82f6 0%, #2563eb 40%, #1d4ed8 70%, #1e3a8a 100%);
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 30% 20%, rgba(255,255,255,0.12) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 80%, rgba(255,255,255,0.07) 0%, transparent 50%);
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        /* Badge circle decoration */
        .brand-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Floating card shadow */
        .auth-card {
            box-shadow:
                0 1px 3px rgba(0,0,0,0.04),
                0 8px 30px rgba(0,0,0,0.08),
                0 30px 60px rgba(0,0,0,0.05);
        }
        /* Input group icon left */
        .input-icon-wrap {
            position: relative;
        }
        .input-icon-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s;
            flex-shrink: 0;
        }
        .input-icon-wrap input {
            padding-left: 44px;
        }
        .input-icon-wrap input:focus + svg,
        .input-icon-wrap:focus-within svg.input-icon {
            color: #2563eb;
        }
        /* Password toggle button */
        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #94a3b8;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            min-height: 32px;
            transition: color 0.2s, background-color 0.2s;
        }
        .pw-toggle:hover { color: #475569; background: #f1f5f9; }
        .pw-toggle:focus-visible { outline: 2px solid #2563eb; outline-offset: 1px; }
        /* Animated submit button */
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
        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        /* Error alert animation */
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .error-alert { animation: slideInDown 0.25s ease; }

        /* Turnstile container — prevent clipping on mobile */
        .turnstile-wrap {
            width: 100%;
            min-height: 65px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: visible;
        }
        @media (min-width: 1024px) {
            .turnstile-wrap {
                justify-content: flex-start;
            }
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
        /* Checkbox custom style */
        .custom-checkbox {
            accent-color: #2563eb;
            width: 18px;
            height: 18px;
            cursor: pointer;
            min-width: 18px;
        }
    </style>
</head>
<body class="login-page">
    {{-- ─────────────────────────────────────────────
         Auth Card Wrapper
    ───────────────────────────────────────────── --}}
    <div class="auth-card w-full max-w-[900px] rounded-2xl overflow-hidden flex flex-col lg:flex-row bg-white">

        {{-- ── LEFT: Brand Panel (hidden on small screens) ── --}}
        <div class="brand-panel relative flex-shrink-0 lg:w-[360px] hidden lg:flex flex-col justify-between p-8 lg:p-10">
            {{-- Top branding logos --}}
            <div class="relative z-10 flex flex-col gap-5">
                {{-- Disdik Jabar + MKKS logo row --}}
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('img/disdik_icon.png') }}"
                        alt="Logo Dinas Pendidikan Jawa Barat"
                        class="h-12 w-auto object-contain"
                        loading="eager"
                    >
                    <div class="h-8 w-px bg-white/30 hidden sm:block"></div>
                    <img
                        src="{{ asset('img/ic_MKKS.png') }}"
                        alt="Logo MKKS SMK Kabupaten Bekasi"
                        class="h-12 w-auto object-contain"
                        loading="eager"
                    >
                </div>

                {{-- Organization name --}}
                <div class="mt-1">
                    <h2 class="text-white font-bold text-xl leading-tight tracking-tight">
                        MKKS SMK<br>
                        <span class="font-extrabold text-2xl">Kabupaten Bekasi</span>
                    </h2>
                    <p class="text-blue-200 text-sm font-medium mt-1.5 leading-relaxed">
                        Musyawarah Kerja Kepala Sekolah<br>
                        SMK Kabupaten Bekasi
                    </p>
                </div>
            </div>

            {{-- Center illustration area --}}
            <div class="relative z-10 py-8 hidden lg:flex justify-center">
                <div class="brand-circle">
                    <img
                        src="{{ asset('img/ic_MKKS.png') }}"
                        alt="MKKS SMK Kabupaten Bekasi"
                        class="w-12 h-12 object-contain"
                    >
                </div>
            </div>

            {{-- Bottom tagline --}}
            <div class="relative z-10">
                <p class="text-blue-100 text-xs leading-relaxed opacity-80">
                    Panel administrasi resmi MKKS SMK Kabupaten Bekasi. Gunakan akun resmi Anda untuk mengakses sistem.
                </p>
            </div>
        </div>

        {{-- ── Mobile brand strip (visible only < lg) ── --}}
        <div class="lg:hidden flex items-center gap-3 px-5 py-4 bg-[linear-gradient(135deg,#1e3a8a_0%,#2563eb_100%)] border-b border-slate-100">
            <img src="{{ asset('img/ic_MKKS.png') }}" alt="MKKS" class="h-10 w-auto object-contain">
            <div>
                <p class="text-white font-bold text-sm leading-tight">MKKS SMK Kabupaten Bekasi</p>
                <p class="text-blue-200 text-xs">Panel Admin</p>
            </div>
        </div>

        {{-- ── RIGHT: Login Form ── --}}
        <div class="flex-1 flex flex-col justify-center p-6 sm:p-8 lg:p-10 bg-white">

            {{-- Page heading --}}
            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Selamat datang!
                </h1>
                <p class="text-slate-500 text-sm sm:text-base mt-1.5 font-medium">
                    Masuk ke panel admin MKKS SMK Kab. Bekasi
                </p>
            </div>

            {{-- ── Success/Status message (e.g. after password reset) ── --}}
            @if (session('status'))
                <div class="error-alert mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm" role="alert">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold">{{ session('status') }}</p>
                </div>
            @endif

            {{-- ── Error alert (login failure) ── --}}
            @if (session('LoginError'))
                <div class="error-alert mb-5 flex items-start gap-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm" role="alert">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold">{{ session('LoginError') }}</p>
                </div>
            @endif

            {{-- ── Validation errors (field-level) ── --}}
            @if ($errors->any() && !$errors->has('cf-turnstile-response'))
                <div class="error-alert mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm" role="alert">
                    <p class="font-bold mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Terdapat kesalahan:
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Login Form ── --}}
            <form action="/login" method="POST" id="loginForm" novalidate class="space-y-4 sm:space-y-5">
                @csrf

                {{-- Email field --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Email <span class="text-rose-500">*</span>
                    </label>
                    <div class="input-icon-wrap">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            autofocus
                            placeholder="email@contoh.com"
                            class="w-full h-[52px] pr-4 rounded-xl text-sm font-medium text-slate-800 placeholder:text-slate-400 border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500
                                @error('email') border-rose-400 bg-rose-50/30 @else border-slate-300 bg-white @enderror"
                            aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        >
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    @error('email')
                        <p id="email-error" class="text-rose-600 text-xs mt-1.5 font-medium flex items-center gap-1" role="alert">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password field --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Kata sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="input-icon-wrap relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Kata sandi Anda"
                            class="w-full h-[52px] pr-12 rounded-xl text-sm font-medium text-slate-800 placeholder:text-slate-400 border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500
                                @error('password') border-rose-400 bg-rose-50/30 @else border-slate-300 bg-white @enderror"
                            aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        >
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        {{-- Show/hide password toggle --}}
                        <button
                            type="button"
                            id="pwToggle"
                            class="pw-toggle"
                            aria-label="Tampilkan kata sandi"
                            aria-pressed="false"
                            tabindex="0"
                        >
                            {{-- Eye icon (default: hidden) --}}
                            <svg id="iconEye" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{-- Eye-off icon (shown when visible) --}}
                            <svg id="iconEyeOff" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p id="password-error" class="text-rose-600 text-xs mt-1.5 font-medium flex items-center gap-1" role="alert">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember me + Forgot password --}}
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <label class="flex items-center gap-2 cursor-pointer select-none group" for="remember">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                            class="custom-checkbox rounded"
                        >
                        <span class="text-sm text-slate-600 font-medium group-hover:text-slate-800 transition-colors">
                            Ingat saya
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="text-sm text-blue-600 hover:text-blue-800 font-semibold transition-colors hover:underline"
                        >
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>

                {{-- Cloudflare Turnstile — WAJIB SELALU ADA —
                     Uses existing x-turnstile component (theme light, for the white form panel).
                     The component handles: widget rendering, token submission,
                     success/expired/error callbacks, and server-side error display.
                --}}
                <div class="turnstile-wrap">
                    <x-turnstile action="login" theme="light" size="flexible" />
                </div>

                {{-- Submit button --}}
                <button
                    type="submit"
                    id="btnLogin"
                    disabled
                    class="btn-primary w-full h-[52px] rounded-xl text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span id="btnLoginText">Masuk</span>
                    <svg id="btnLoginSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </form>

            {{-- Footer help text --}}
            <p class="mt-6 text-center text-xs text-slate-400 leading-relaxed">
                Hanya akun yang telah diaktivasi oleh admin dapat mengakses panel ini.
            </p>
        </div>
    </div>

    {{-- Turnstile auto-reset on validation error --}}
    @if ($errors->any() || session('LoginError'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (window.turnstile) {
                        window.turnstile.reset('#turnstile-widget');
                    }
                }, 500);
            });
        </script>
    @endif

    {{-- Password show/hide toggle (pure UI — does not affect value or submission) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var pwInput  = document.getElementById('password');
            var pwToggle = document.getElementById('pwToggle');
            var iconEye    = document.getElementById('iconEye');
            var iconEyeOff = document.getElementById('iconEyeOff');

            if (pwInput && pwToggle) {
                pwToggle.addEventListener('click', function () {
                    var isHidden = pwInput.type === 'password';
                    pwInput.type = isHidden ? 'text' : 'password';
                    pwToggle.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                    pwToggle.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    iconEye.classList.toggle('hidden', isHidden);
                    iconEyeOff.classList.toggle('hidden', !isHidden);
                });
            }

            // Turnstile State Management
            var originalOnTurnstileSuccess = window.onTurnstileSuccess;
            var originalOnTurnstileExpired = window.onTurnstileExpired;
            var originalOnTurnstileError = window.onTurnstileError;

            window.onTurnstileSuccess = function(token) {
                if (originalOnTurnstileSuccess) originalOnTurnstileSuccess(token);
                if (btnLogin) btnLogin.disabled = false;
            };

            window.onTurnstileExpired = function() {
                if (originalOnTurnstileExpired) originalOnTurnstileExpired();
                if (btnLogin) btnLogin.disabled = true;
            };

            window.onTurnstileError = function() {
                if (originalOnTurnstileError) originalOnTurnstileError();
                if (btnLogin) btnLogin.disabled = true;
            };

            // Initial state check (in case token is already present via back button or fast load)
            // Note: We use setTimeout to allow Turnstile to render first if it's cached
            setTimeout(function() {
                var initialToken = loginForm ? loginForm.querySelector('[name="cf-turnstile-response"]') : null;
                if (initialToken && initialToken.value && btnLogin) {
                    btnLogin.disabled = false;
                }
            }, 500);

            // Prevent duplicate submit / show spinner on submit
            if (loginForm && btnLogin) {
                loginForm.addEventListener('submit', function (e) {
                    var currentToken = loginForm.querySelector('[name="cf-turnstile-response"]');
                    if (!currentToken || !currentToken.value) {
                        e.preventDefault(); // Extra safety client-side boundary
                        return;
                    }
                    btnLogin.disabled = true;
                    btnText.textContent = 'Memproses...';
                    btnSpin.classList.remove('hidden');
                });
            }
        });
    </script>
</body>
</html>
@endif