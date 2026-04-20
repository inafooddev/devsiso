<?php

namespace App\Livewire;

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

    // Validasi form: nama role wajib diisi dan tidak boleh duplikat
    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
    ];

    public function render()
    {
        return view('livewire.role-management', [
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
        $this->resetFields();
        
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

        $role->delete();
        session()->flash('message', 'Role sistem berhasil dihapus.');
    }

    private function resetFields()
    {
        $this->name = '';
    }
}