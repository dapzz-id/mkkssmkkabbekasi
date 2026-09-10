<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Atur Ulang Kata Sandi - MKKS SMK Kabupaten Bekasi</title>
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
        .input-icon-wrap input { padding-left: 44px; }
        .input-icon-wrap:focus-within svg.input-icon { color: #2563eb; }
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
        /* Strength meter */
        .strength-bar {
            height: 4px;
            border-radius: 9999px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-anim { animation: slideInDown 0.25s ease; }
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
        <div class="p-6 sm:p-8">

            {{-- Heading --}}
            <div class="mb-6">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Atur ulang kata sandi</h1>
                <p class="text-slate-500 text-sm mt-1.5 leading-relaxed">
                    Buat kata sandi baru yang kuat untuk akun Anda.
                </p>
            </div>

            {{-- Validation / token errors --}}
            @if ($errors->any())
                <div class="alert-anim mb-5 flex items-start gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm" role="alert">
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

            <form action="{{ route('password.update') }}" method="POST" id="resetForm" novalidate class="space-y-4">
                @csrf

                {{-- Hidden fields: token + email --}}
                <input type="hidden" name="token" value="{{ $token }}">

                {{-- Email (pre-filled and read-only for UX clarity) --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Email
                    </label>
                    <div class="input-icon-wrap">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $email) }}"
                            required
                            autocomplete="email"
                            class="w-full h-[52px] pr-4 rounded-xl text-sm font-medium text-slate-500 bg-slate-50 border border-slate-200 cursor-not-allowed focus:outline-none
                                @error('email') border-rose-400 @endif"
                            readonly
                            aria-label="Alamat email terdaftar"
                        >
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    @error('email')
                        <p class="text-rose-600 text-xs mt-1.5 font-medium" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New password --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Kata sandi baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="input-icon-wrap relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Min. 8 karakter, huruf besar, angka"
                            class="w-full h-[52px] pr-12 rounded-xl text-sm font-medium text-slate-800 placeholder:text-slate-400 border border-slate-300 bg-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500
                                @error('password') border-rose-400 bg-rose-50/30 @endif"
                            aria-describedby="pw-strength-label"
                        >
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <button type="button" id="pwToggle1" class="pw-toggle" aria-label="Tampilkan kata sandi" aria-pressed="false">
                            <svg id="eye1" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeOff1" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    {{-- Password strength indicator --}}
                    <div class="mt-2">
                        <div class="w-full bg-slate-100 rounded-full h-1 overflow-hidden">
                            <div id="strengthBar" class="strength-bar bg-slate-300 w-0"></div>
                        </div>
                        <p id="pw-strength-label" class="text-xs mt-1 font-medium text-slate-400" aria-live="polite"></p>
                    </div>
                    @error('password')
                        <p class="text-rose-600 text-xs mt-1.5 font-medium" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Konfirmasi kata sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="input-icon-wrap relative">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi baru"
                            class="w-full h-[52px] pr-12 rounded-xl text-sm font-medium text-slate-800 placeholder:text-slate-400 border border-slate-300 bg-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500"
                        >
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <button type="button" id="pwToggle2" class="pw-toggle" aria-label="Tampilkan konfirmasi" aria-pressed="false">
                            <svg id="eye2" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeOff2" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    {{-- Client-side match hint --}}
                    <p id="matchHint" class="text-xs mt-1.5 font-medium hidden"></p>
                </div>

                {{-- Requirements note --}}
                <div class="flex items-start gap-2.5 p-3 sm:p-3.5 rounded-xl bg-blue-50 border border-blue-100">
                    <svg class="w-5 h-5 mt-0.5 shrink-0 text-blue-500" style="width: 20px; height: 20px; min-width: 20px; max-width: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-blue-800 text-xs leading-relaxed font-medium">
                        Minimal 8 karakter · Huruf besar &amp; kecil · Mengandung angka
                    </p>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    id="btnReset"
                    class="btn-primary w-full h-[52px] rounded-xl text-white font-bold text-sm sm:text-base flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <span id="btnResetText">Atur Ulang Kata Sandi</span>
                    <svg id="btnResetSpinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
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
            // ─── Show/hide toggles ───
            function makeToggle(btnId, inputId, eyeId, eyeOffId) {
                var btn     = document.getElementById(btnId);
                var input   = document.getElementById(inputId);
                var eye     = document.getElementById(eyeId);
                var eyeOff  = document.getElementById(eyeOffId);
                if (!btn || !input) return;
                btn.addEventListener('click', function () {
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                    btn.setAttribute('aria-label', show ? 'Sembunyikan' : 'Tampilkan');
                    if (eye) eye.classList.toggle('hidden', show);
                    if (eyeOff) eyeOff.classList.toggle('hidden', !show);
                });
            }
            makeToggle('pwToggle1', 'password', 'eye1', 'eyeOff1');
            makeToggle('pwToggle2', 'password_confirmation', 'eye2', 'eyeOff2');

            // ─── Password strength meter ───
            var pwInput     = document.getElementById('password');
            var strengthBar = document.getElementById('strengthBar');
            var strengthLbl = document.getElementById('pw-strength-label');

            function getStrength(pw) {
                var score = 0;
                if (pw.length >= 8)  score++;
                if (pw.length >= 12) score++;
                if (/[A-Z]/.test(pw)) score++;
                if (/[a-z]/.test(pw)) score++;
                if (/[0-9]/.test(pw)) score++;
                if (/[^A-Za-z0-9]/.test(pw)) score++;
                return score;
            }

            var levels = [
                { label: '', color: 'bg-slate-300', width: '0%' },
                { label: 'Sangat lemah', color: '#ef4444', width: '16%' },
                { label: 'Lemah',        color: '#f97316', width: '32%' },
                { label: 'Sedang',       color: '#eab308', width: '50%' },
                { label: 'Cukup kuat',  color: '#22c55e', width: '75%' },
                { label: 'Kuat',         color: '#16a34a', width: '88%' },
                { label: 'Sangat kuat', color: '#15803d', width: '100%' },
            ];

            if (pwInput && strengthBar && strengthLbl) {
                pwInput.addEventListener('input', function () {
                    var v = pwInput.value;
                    if (!v) {
                        strengthBar.style.width = '0';
                        strengthLbl.textContent = '';
                        strengthLbl.style.color = '';
                        return;
                    }
                    var s = Math.min(getStrength(v), 6);
                    var lvl = levels[s];
                    strengthBar.style.width = lvl.width;
                    strengthBar.style.backgroundColor = lvl.color;
                    strengthLbl.textContent = lvl.label;
                    strengthLbl.style.color = lvl.color;
                });
            }

            // ─── Confirm match hint ───
            var confirmInput = document.getElementById('password_confirmation');
            var matchHint    = document.getElementById('matchHint');

            function checkMatch() {
                if (!confirmInput.value) {
                    matchHint.classList.add('hidden');
                    return;
                }
                matchHint.classList.remove('hidden');
                if (confirmInput.value === pwInput.value) {
                    matchHint.textContent = '✓ Kata sandi cocok';
                    matchHint.style.color = '#16a34a';
                } else {
                    matchHint.textContent = '✗ Kata sandi tidak cocok';
                    matchHint.style.color = '#ef4444';
                }
            }
            if (confirmInput) {
                confirmInput.addEventListener('input', checkMatch);
                if (pwInput) pwInput.addEventListener('input', checkMatch);
            }

            // ─── Submit spinner ───
            var resetForm = document.getElementById('resetForm');
            var btnReset  = document.getElementById('btnReset');
            var btnText   = document.getElementById('btnResetText');
            var spinner   = document.getElementById('btnResetSpinner');

            if (resetForm && btnReset) {
                resetForm.addEventListener('submit', function () {
                    btnReset.disabled = true;
                    btnText.textContent = 'Memproses...';
                    spinner.classList.remove('hidden');
                });
            }
        });
    </script>
</body>
</html>
