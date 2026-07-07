<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login mobile (Inertia).
     */
    public function showLogin()
    {
        return Inertia::render('mobile/Pages/Auth/Login');
    }

    /**
     * Tangani request login mobile.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::lower($request->user_id) . '|' . $request->ip();

        // Cek rate limiter
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'user_id' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Coba authentikasi
        if (Auth::attempt(['userid' => $request->user_id, 'password' => $request->password], $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            
            \App\Helpers\ActivityLogger::log('Mobile Login', 'User berhasil login ke sistem mobile.');

            return redirect()->intended('/mobile/home');
        }

        RateLimiter::hit($throttleKey, 60);

        \App\Helpers\ActivityLogger::log('Mobile Login Failed', "Gagal login mobile dengan userid: {$request->user_id} dari IP: " . $request->ip());

        throw ValidationException::withMessages([
            'user_id' => 'User ID atau password salah.',
        ]);
    }

    /**
     * Tangani request logout mobile.
     */
    public function logout(Request $request)
    {
        \App\Helpers\ActivityLogger::log('Mobile Logout', 'User berhasil logout dari sistem mobile.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.login');
    }
}
