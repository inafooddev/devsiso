<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\MasterDistributor; // <-- Import model MasterDistributor
use App\Models\Menu;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('layouts.app')] 
class UserManagement extends Component
{
    use WithPagination;

    // Properti Form
    public $userId, $userid, $name, $email, $password, $role;
    
    // region_code sekarang berbentuk Array karena akan diikat ke Checkbox
    public $region_code = []; 
    
    // State untuk Modal Alpine
    public $isModalOpen = false;
    public $isRoleModalOpen = false;
    public $isMenuModalOpen = false;
    
    // Properti untuk Menu Management
    public $selectedUserId = null;
    public $selectedUserMenus = [];
    public $allMenus = [];
    
    // Properti untuk Tambah Role Baru
    public $newRoleName;

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

    public function edit($id)
    {
        $this->resetFields();
        $user = User::findOrFail($id);
        
        $this->userId = $user->id;
        $this->userid = $user->userid;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()->name ?? '';
        
        // Ensure region_code is always an array for the checkboxes
        if (is_string($user->region_code)) {
            // Check if it's a JSON array string, else put it in an array
            $decoded = json_decode($user->region_code, true);
            if (is_array($decoded)) {
                $this->region_code = $decoded;
            } else {
                $this->region_code = [$user->region_code];
            }
        } elseif (is_array($user->region_code)) {
            $this->region_code = $user->region_code;
        } else {
            $this->region_code = [];
        }

        $this->isModalOpen = true;
    }

    public function store()
    {
        $rules = [
            'userid' => ['required', 'string', Rule::unique('users', 'userid')->ignore($this->userId)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'role' => 'required|string',
        ];

        // Password is required when creating, but optional when editing
        if (!$this->userId) {
            $rules['password'] = 'required|min:6';
        } elseif (!empty($this->password)) {
            $rules['password'] = 'min:6';
        }

        $this->validate($rules);

        $data = [
            'userid' => $this->userid,
            'name' => $this->name,
            'email' => $this->email,
            'region_code' => empty($this->region_code) ? null : $this->region_code,
        ];

        if (!empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            
            // Sync roles Spatie (remove old, attach new)
            $user->syncRoles([$this->role]);
            
            session()->flash('message', 'User berhasil diperbarui.');
        } else {
            $user = User::create($data);
            $user->assignRole($this->role);
            
            session()->flash('message', 'User berhasil ditambahkan.');
        }

        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('message', 'User berhasil dihapus.');
    }

    public function openRoleModal()
    {
        $this->newRoleName = '';
        $this->isRoleModalOpen = true;
    }

    public function storeRole()
    {
        $this->validate([
            'newRoleName' => 'required|string|max:255|unique:roles,name',
        ], [
            'newRoleName.required' => 'Nama role tidak boleh kosong.',
            'newRoleName.unique' => 'Nama role sudah digunakan.',
        ]);

        Role::create([
            'name' => strtolower(trim($this->newRoleName)),
            'guard_name' => 'web'
        ]);

        $this->isRoleModalOpen = false;
        $this->newRoleName = '';
        
        session()->flash('message', 'Role sistem berhasil ditambahkan.');
    }

    public function openMenuModal($id)
    {
        $user = User::findOrFail($id);
        $this->selectedUserId = $user->id;
        $this->name = $user->name; // untuk ditampilkan di judul modal
        
        // Ambil semua menu dengan hierarki
        $this->allMenus = Menu::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('order_number')->with(['children' => function($q2) {
                    $q2->orderBy('order_number')->with(['children' => function($q3) {
                        $q3->orderBy('order_number');
                    }]);
                }]);
            }])->orderBy('order_number')->get()->toArray();
            
        // Ambil ID menu yang saat ini dimiliki user
        $this->selectedUserMenus = $user->menus()->pluck('menus.id')->toArray();
        
        $this->isMenuModalOpen = true;
    }

    public function storeMenuAccess()
    {
        if ($this->selectedUserId) {
            $user = User::findOrFail($this->selectedUserId);
            // Sync menu_user table
            $user->menus()->sync($this->selectedUserMenus);
            
            $this->isMenuModalOpen = false;
            session()->flash('message', 'Akses menu untuk ' . $user->name . ' berhasil diperbarui.');
        }
    }

    private function resetFields()
    {
        $this->userId = null;
        $this->userid = '';
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->region_code = []; // Reset kembali jadi array kosong
    }
}