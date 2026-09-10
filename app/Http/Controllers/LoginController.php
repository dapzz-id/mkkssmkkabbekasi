<?php

namespace App\Http\Controllers;
use App\Rules\TurnstileRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as Auth;

class LoginController extends Controller
{
    public function login(Request $request) {
        // Step 1: Basic input validation (fail early before invoking external security service)
        $credentials = $request->validate([
            "email" => "required|email",
            "password" => "required"
        ], [
            "email.email" => "Masukkan email yang valid",
            "email.required" => "Masukkan email",
            "password.required" => "Masukkan password"
        ]);

        // Step 2: Server-side Turnstile verification (only invoked if basic inputs are valid)
        $request->validate([
            "cf-turnstile-response" => ["required", new TurnstileRule(action: 'login')],
        ], [
            "cf-turnstile-response.required" => "Verifikasi keamanan diperlukan.",
        ]);

        // Step 3: Attempt authentication
        $remember = $request->boolean('remember');
        
        if(Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended("/dashboard");
        }

        // Generic error without account enumeration
        return back()->with("LoginError", "Login Gagal. Silakan periksa kembali kredensial Anda.");
    }


    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
