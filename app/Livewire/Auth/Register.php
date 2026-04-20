<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\MasterDistributor;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guest')]
class Register extends Component
{
    // FORM
    public $userid = 'admin';
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role = 'admin';
    public $region_code = [];

    public $hasSearched = false;

    public function mount()
    {
        $this->userid = 'admin';
        $this->role = 'admin';
    }

    // VALIDATION RULES
    protected $rules = [
        'userid' => 'required|string|max:50',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
    ];

    // REALTIME VALIDATION USERID
    public function updatedUserid()
    {
        if (!$this->userid) return;

        if (User::where('userid', $this->userid)->exists()) {
            $this->addError('userid', 'User ID sudah digunakan.');
        } else {
            $this->resetErrorBag('userid');
        }
    }

    public function render()
    {
        $availableRegions = MasterDistributor::select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name')
            ->get();

        $roles = Role::orderBy('name')->get();

        return view('livewire.auth.register', [
            'availableRegions' => $availableRegions,
            'roles' => $roles,
        ]);
    }

    public function register()
    {
        $this->validate();

        // 🔍 CEK DUPLICATE USERID
        if (User::where('userid', $this->userid)->exists()) {
            $this->addError('userid', 'User ID sudah digunakan.');
            return;
        }

        // ✅ INSERT USER
        $user = User::create([
            'userid' => strtolower($this->userid),
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password, // auto hash dari model
            'region_code' => empty($this->region_code) ? null : $this->region_code,
        ]);

        // ASSIGN ROLE
        $user->assignRole($this->role);

        // AUTO LOGIN
        Auth::login($user);

        // REDIRECT
        return redirect()->route('dashboard');
    }
}