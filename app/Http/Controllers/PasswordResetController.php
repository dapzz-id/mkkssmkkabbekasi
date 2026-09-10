<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * PasswordResetController
 *
 * Handles the full forgot-password / reset-password flow
 * using Laravel's native Password Broker.
 *
 * Route registration (to be added in routes/web.php):
 *
 *   GET  /forgot-password   → showForgotForm  (name: password.request)
 *   POST /forgot-password   → sendResetLink   (name: password.email)
 *   GET  /reset-password/{token} → showResetForm  (name: password.reset)
 *   POST /reset-password    → resetPassword   (name: password.update)
 */
class PasswordResetController extends Controller
{
    /**
     * GET /forgot-password
     * Show the "Forgot Password" form.
     */
    public function showForgotForm(): View
    {
        return view('admin.login.forgot-password');
    }

    /**
     * POST /forgot-password
     * Validate email, create a secure token, and dispatch the
     * reset-password email via the queue (non-blocking HTTP response).
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        // Rate-limit: max 5 attempts per minute (handled by throttle middleware on route)
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Masukkan alamat email Anda.',
            'email.email'    => 'Masukkan alamat email yang valid.',
        ]);

        // SECURITY: Always return a generic success response regardless of whether
        // the email exists, to prevent user enumeration attacks.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Password::RESET_LINK_SENT is returned even if the email doesn't exist
        // (Laravel does this internally — we mirror the same generic response).
        return back()->with('status', __('auth.password_reset_sent'));
    }

    /**
     * GET /reset-password/{token}
     * Show the password-reset form with the token and email pre-filled.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('admin.login.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * POST /reset-password
     * Validate the token, validate the new password, update it, and
     * invalidate the reset token so it cannot be reused.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)->letters()->mixedCase()->numbers(),
            ],
        ], [
            'token.required'    => 'Token reset tidak valid.',
            'email.required'    => 'Email diperlukan.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi baru diperlukan.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min'      => 'Kata sandi minimal 8 karakter.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                // Hash the password and save — never store plaintext
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', __('auth.password_reset_success'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
