<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-base-content">Manajemen Role Sistem</h2>
                <p class="text-sm text-base-content/70 mt-1">Kelola data hak akses role yang dapat diberikan kepada user.</p>
            </div>
            <x-ui.button variant="primary" icon="plus" wire:click="create">  
                Tambah Role
            </x-ui.button>
        </div>

        <!-- Alert Sukses -->
        @if (session()->has('message'))
            <div class="mb-6">
                <x-ui.notif type="success" dismissible="true">
                    {{ session('message') }}
                </x-ui.notif>
            </div>
        @endif

        <!-- Alert Error -->
        @if (session()->has('error'))
            <div class="mb-6">
                <x-ui.notif type="error" dismissible="true">
                    {{ session('error') }}
                </x-ui.notif>
            </div>
        @endif

        <!-- Tabel Role -->
        <x-ui.table empty="Belum ada data role" emptyIcon="shield-check">
            <x-slot:head>
                <tr>
                    <th>ID</th>
                    <th>Nama Role (Kode)</th>
                    <th>Total User</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </x-slot:head>
            
            @foreach($roles as $role)
            <tr>
                <td class="text-base-content/70">
                    {{ $role->id }}
                </td>
                <td>
                    <p class="font-bold text-base-content text-primary">{{ strtoupper($role->name) }}</p>
                </td>
                <td class="text-base-content/70">
                    {{ $role->users()->count() }} Akun
                </td>
                <td class="text-right">
                    @if($role->name !== 'national')
                        <x-ui.button variant="error" size="sm" outline="true" icon="trash" wire:click="delete({{ $role->id }})" onclick="return confirm('Peringatan: Menghapus role ini mungkin akan berdampak pada hak akses user yang memilikinya. Lanjutkan?')">
                            Hapus
                        </x-ui.button>
                    @else
                        <span class="text-base-content/50 italic text-xs block px-2 py-1">Core Role</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">
            @if(method_exists($roles, 'links'))
                {{ $roles->links() }}
            @endif
        </div>
    </div>

    <!-- Modal Tambah Role -->
    <x-ui.modal id="modal-tambah-role" title="Buat Role Baru" icon="shield-check" size="md" :dismissible="false" :open="$isModalOpen" wire:close="$set('isModalOpen', false)">
        <p class="text-xs text-base-content/60 mb-4">Role ini nantinya bisa dipilih saat Anda membuat user baru.</p>
        
        <form wire:submit.prevent="store" id="form-tambah-role">
            <div class="mb-4">
                <label class="label"><span class="label-text font-medium">Nama Role</span></label>
                <input type="text" wire:model="name" placeholder="Misal: admin_area" class="input input-bordered w-full">
                @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="text-[10px] text-base-content/50 mt-1">* Sebaiknya gunakan huruf kecil tanpa spasi (gunakan underscore jika perlu).</p>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-tambah-role').requestSubmit()">
                Simpan Role
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>