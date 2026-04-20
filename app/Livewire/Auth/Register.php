<?php


namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\MasterDistributor;
use Spatie\Permission\Models\Role; // <-- Import model Role Spatie
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guest')] // <-- Diubah menyesuaikan struktur folder Anda
class Register extends Component
{
    // Properti Form
    public $userid;
    public $name;
    public $email;
    public $password;
    public $password_confirmation; // Untuk konfirmasi password
    public $role = ''; // <-- Properti role baru
    public $region_code = []; 

    // Validasi form khusus register
    protected $rules = [
        'userid' => 'required|string|unique:users,userid',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed', // Pastikan ada konfirmasi
        'role' => 'required|string|exists:roles,name', // Validasi role harus ada di tabel
    ];

    public function render()
    {
        // Mengambil data region
        $availableRegions = MasterDistributor::select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name')
            ->get();

        // Mengambil semua role yang tersedia
        $roles = Role::orderBy('name')->get();

        return view('livewire.auth.register', [
            'availableRegions' => $availableRegions,
            'roles' => $roles, // Kirim ke view
        ]);
    }

    public function register()
    {
        $this->validate();

        // 1. Buat User Baru
        $user = User::create([
            'userid' => $this->userid,
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'region_code' => empty($this->region_code) ? null : $this->region_code,
        ]);

        // 2. Assign Role Spatie (Berdasarkan pilihan user di form)
        $user->assignRole($this->role);

        // 3. Login otomatis setelah register
        Auth::login($user);

        // 4. Redirect ke dashboard atau halaman utama
        return redirect()->route('dashboard'); 
    }
}