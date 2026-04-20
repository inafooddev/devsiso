<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\MasterDistributor; // <-- Import model MasterDistributor
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')] 
class UserManagement extends Component
{
    use WithPagination;

    // Properti Form
    public $userid, $name, $email, $password, $role;
    
    // region_code sekarang berbentuk Array karena akan diikat ke Checkbox
    public $region_code = []; 
    
    // State untuk Modal Alpine
    public $isModalOpen = false;

    // Validasi form
    protected $rules = [
        'userid' => 'required|string|unique:users,userid',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'role' => 'required|string',
    ];

    public function render()
    {
        // Mengambil daftar region unik dari master_distributors
        $availableRegions = MasterDistributor::select('region_code', 'region_name')
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name')
            ->get();

        return view('livewire.user-management', [
            'users' => User::with('roles')->latest()->paginate(10),
            'roles' => Role::all(),
            'availableRegions' => $availableRegions, // Kirim ke view
        ]);
    }

    public function create()
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate();

        // 1. Buat User Baru
        $user = User::create([
            'userid' => $this->userid,
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            // Jika checkbox tidak ada yang dicentang, simpan null. Jika ada, simpan array-nya.
            'region_code' => empty($this->region_code) ? null : $this->region_code,
        ]);

        // 2. Assign Role Spatie
        $user->assignRole($this->role);

        // 3. Tutup modal & reset form
        $this->isModalOpen = false;
        $this->resetFields();
        
        session()->flash('message', 'User berhasil ditambahkan.');
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('message', 'User berhasil dihapus.');
    }

    private function resetFields()
    {
        $this->userid = '';
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->region_code = []; // Reset kembali jadi array kosong
    }
}