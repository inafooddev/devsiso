<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')] // Menggunakan layout utama aplikasi Anda
class RoleManagement extends Component
{
    use WithPagination;

    public $name; // Input untuk nama role baru
    public $isModalOpen = false;

    // Properti untuk Menu Management
    public $selectedRoleId = null;
    public $rolePermissions = [];
    public $allMenus = [];
    public $isMenuModalOpen = false;
    public $roleNameForMenu = '';

    // Validasi form: nama role wajib diisi dan tidak boleh duplikat
    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
    ];

    public function render()
    {
        return view('livewire.settings.role-management', [
            // Menampilkan semua role dengan paginasi
            'roles' => Role::latest()->paginate(10),
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

        // Menyimpan role baru (otomatis mengubahnya menjadi huruf kecil tanpa spasi berlebih untuk standarisasi)
        Role::create([
            'name' => strtolower(trim($this->name)),
            'guard_name' => 'web' // guard default Laravel
        ]);

        // Tutup modal dan reset input
        $this->isModalOpen = false;
        \App\Helpers\ActivityLogger::log('Create Role', "Membuat role baru: " . strtolower(trim($this->name)));
        
        session()->flash('message', 'Role sistem berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $role = Role::find($id);
        
        // Opsional: Proteksi agar role penting tidak tidak sengaja dihapus
        if ($role->name === 'national') {
            session()->flash('error', 'Role National adalah core system dan tidak boleh dihapus.');
            return;
        }

        $roleName = $role->name;
        $role->delete();
        
        \App\Helpers\ActivityLogger::log('Delete Role', "Menghapus role: {$roleName}");
        session()->flash('message', 'Role sistem berhasil dihapus.');
    }

    public function openMenuModal($id)
    {
        $role = Role::findOrFail($id);
        $this->selectedRoleId = $role->id;
        $this->roleNameForMenu = strtoupper($role->name);
        
        // Ambil semua menu dengan hierarki
        $this->allMenus = \App\Models\Menu::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('order_number')->with(['children' => function($q2) {
                    $q2->orderBy('order_number')->with(['children' => function($q3) {
                        $q3->orderBy('order_number');
                    }]);
                }]);
            }])->orderBy('order_number')->get()->toArray();
            
        // Inisialisasi default array permissions untuk semua menu
        $this->rolePermissions = [];
        $allMenuFlat = \App\Models\Menu::all();
        foreach($allMenuFlat as $m) {
            $this->rolePermissions[$m->id] = [
                'can_edit' => false,
                'can_import' => false,
                'can_export' => false,
                'can_add' => false,
                'can_delete' => false,
            ];
        }
            
        // Ambil data menu dan pivot untuk role ini
        $menus = \App\Models\Menu::whereHas('roles', function($q) use ($role) {
            $q->where('roles.id', $role->id);
        })->with(['roles' => function($q) use ($role) {
            $q->where('roles.id', $role->id);
        }])->get();

        foreach($menus as $m) {
            $pivot = $m->roles->first()->pivot;
            $this->rolePermissions[$m->id] = [
                'can_edit' => (bool) $pivot->can_edit,
                'can_import' => (bool) $pivot->can_import,
                'can_export' => (bool) $pivot->can_export,
                'can_add' => (bool) $pivot->can_add,
                'can_delete' => (bool) $pivot->can_delete,
            ];
        }
        
        $this->isMenuModalOpen = true;
    }

    public function storeMenuAccess()
    {
        if ($this->selectedRoleId) {
            $role = Role::findOrFail($this->selectedRoleId);
            
            // Hapus akses menu lama
            \Illuminate\Support\Facades\DB::table('menu_role')->where('role_id', $role->id)->delete();
            
            // Insert akses menu baru
            $inserts = [];
            foreach($this->rolePermissions as $menuId => $perms) {
                // Jika setidaknya salah satu di-centang, simpan barisnya
                if(!empty($perms['can_edit']) || !empty($perms['can_import']) || !empty($perms['can_export']) || !empty($perms['can_add']) || !empty($perms['can_delete'])) {
                    $inserts[] = [
                        'menu_id' => $menuId, 
                        'role_id' => $role->id, 
                        'can_edit' => !empty($perms['can_edit']) ? 1 : 0,
                        'can_import' => !empty($perms['can_import']) ? 1 : 0,
                        'can_export' => !empty($perms['can_export']) ? 1 : 0,
                        'can_add' => !empty($perms['can_add']) ? 1 : 0,
                        'can_delete' => !empty($perms['can_delete']) ? 1 : 0,
                        'created_at' => now(), 
                        'updated_at' => now()
                    ];
                }
            }

            if(count($inserts) > 0){
                \Illuminate\Support\Facades\DB::table('menu_role')->insert($inserts);
            }
            
            $this->isMenuModalOpen = false;
            
            \App\Helpers\ActivityLogger::log('Update Role Access', "Memperbarui akses menu untuk role: " . strtoupper($role->name));
            session()->flash('message', 'Akses menu untuk role ' . strtoupper($role->name) . ' berhasil diperbarui.');
        }
    }

    public function toggleAll($action)
    {
        // Cek apakah semua sudah tercentang
        $allChecked = true;
        foreach($this->rolePermissions as $perms) {
            if (empty($perms[$action])) {
                $allChecked = false;
                break;
            }
        }

        $newState = !$allChecked; // Jika ada yang belum tercentang, maka centang semua. Jika sudah semua, maka uncheck semua.
        
        foreach($this->rolePermissions as $id => $perms) {
            $this->rolePermissions[$id][$action] = $newState;
        }
    }

    private function resetFields()
    {
        $this->name = '';
    }
}