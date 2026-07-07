<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

#[Layout('components.layouts.guest')] 
class Login extends Component
{
    public string $userid = '';
    public string $password = '';
    public bool $remember = false;
    public int $lockoutSeconds = 0; // Untuk countdown rate limit di frontend

    protected $rules = [
        'userid' => 'required|string',
        'password' => 'required',
    ];

    /**
     * Dapatkan kunci throttle untuk membatasi login rate.
     */
    protected function throttleKey(): string
    {
        return Str::lower($this->userid).'|'.request()->ip();
    }

    /**
     * Cek rate limit. Return true jika sedang terkunci.
     * Properti $lockoutSeconds di-set untuk countdown frontend.
     */
    protected function isRateLimited(): bool
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $this->lockoutSeconds = 0;
            return false;
        }

        $this->lockoutSeconds = RateLimiter::availableIn($this->throttleKey());
        return true;
    }

    public function authenticate()
    {
        $this->validate();

        // Jika masih dalam masa lockout, hentikan proses (blade tampilkan countdown)
        if ($this->isRateLimited()) {
            return;
        }

        // Mencoba login menggunakan kolom 'userid'
        if (Auth::attempt(['userid' => $this->userid, 'password' => $this->password], $this->remember)) {
            RateLimiter::clear($this->throttleKey());
            $this->lockoutSeconds = 0;
            session()->regenerate();

            \App\Helpers\ActivityLogger::log('Login', 'User berhasil login ke dalam sistem.');

            $user = Auth::user();
            if ($user && $user->hasRole(['admin', 'user'])) {
                return redirect()->intended(route('dashboard.national-sell-in'));
            }

            // Jika role guest atau lainnya, arahkan ke welcome page
            return redirect()->intended('/welcome');
        }

        // Mencatat kegagalan dan memulai penalti rate limit (blokir 60 detik jika limit tercapai)
        RateLimiter::hit($this->throttleKey(), 60);

        // Log kegagalan login untuk audit
        \App\Helpers\ActivityLogger::log('Login Failed', "Gagal login dengan userid: {$this->userid} dari IP: " . request()->ip());

        // Reset state password agar tidak tertinggal di UI browser
        $this->reset('password');

        // Jika gagal
        throw ValidationException::withMessages([
            'userid' => 'UserID atau password salah.',
        ]);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}