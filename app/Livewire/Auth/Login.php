<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guest')] 
class Login extends Component
{
    public $userid = ''; // Menggunakan userid
    public $password = '';
    public $remember = false;

    protected $rules = [
        'userid' => 'required|string',
        'password' => 'required',
    ];

    public function authenticate()
    {
        $this->validate();

        // Mencoba login menggunakan kolom 'userid'
        if (Auth::attempt(['userid' => $this->userid, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            // Arahkan ke dashboard setelah sukses
            return redirect()->intended('/dashboard/metabase');
        }

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