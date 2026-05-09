<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-base-content">Manajemen Pengguna</h2>
                <p class="text-sm text-base-content/70 mt-1">Kelola data pengguna, role, dan cakupan wilayah.</p>
            </div>
            <x-ui.button variant="primary" icon="plus" wire:click="create">  
                Tambah User
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

        <!-- Tabel User -->
        <x-ui.table empty="Belum ada data user" emptyIcon="users">
            <x-slot:head>
                <tr>
                    <th>User ID / Nama</th>
                    <th>Role</th>
                    <th>Cakupan Wilayah</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </x-slot:head>
            
            @foreach($users as $user)
            <tr>
                <td>
                    <p class="font-bold text-base-content">{{ $user->userid }}</p>
                    <p class="text-sm text-base-content/70">{{ $user->name }}</p>
                </td>
                <td>
                    <x-ui.badge variant="primary" outline="true">
                        {{ $user->getRoleNames()->first() ?? 'Belum ada role' }}
                    </x-ui.badge>
                </td>
                <td class="text-base-content/70">
                    @if(is_array($user->region_code) && count($user->region_code) > 0) 
                        Region: {{ implode(', ', $user->region_code) }}
                    @elseif(is_string($user->region_code) && !empty($user->region_code))
                        Region: {{ $user->region_code }}
                    @else 
                        Nasional (Semua) 
                    @endif
                </td>
                <td class="text-right space-x-1">
                    <x-ui.button variant="primary" size="sm" outline="true" icon="key" wire:click="openMenuModal({{ $user->id }})">
                        Akses Menu
                    </x-ui.button>
                    <x-ui.button variant="primary" size="sm" outline="true" icon="pencil" wire:click="edit({{ $user->id }})">
                        Edit
                    </x-ui.button>
                    <x-ui.button variant="error" size="sm" outline="true" icon="trash" wire:click="delete({{ $user->id }})" onclick="return confirm('Yakin ingin menghapus user ini?')">
                        Hapus
                    </x-ui.button>
                </td>
            </tr>
            @endforeach
        </x-ui.table>

        <div class="mt-4">
            @if(method_exists($users, 'links'))
                {{ $users->links() }}
            @endif
        </div>

    </div>

    <!-- Modal Tambah/Edit User -->
    <x-ui.modal id="modal-tambah" title="{{ $userId ? 'Edit User' : 'Tambah User Baru' }}" icon="{{ $userId ? 'pencil' : 'user-plus' }}" size="md" :dismissible="false" :open="$isModalOpen" wire:close="$set('isModalOpen', false)">
        <form wire:submit.prevent="store" id="form-tambah-user">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="col-span-1">
                    <label class="label"><span class="label-text font-medium">User ID</span></label>
                    <input type="text" wire:model="userid" placeholder="Misal: admin01" class="input input-bordered w-full">
                    @error('userid') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-1">
                    <label class="label"><span class="label-text font-medium">Nama Lengkap</span></label>
                    <input type="text" wire:model="name" class="input input-bordered w-full">
                    @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-1">
                    <label class="label"><span class="label-text font-medium">Email</span></label>
                    <input type="email" wire:model="email" class="input input-bordered w-full">
                    @error('email') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-1">
                    <label class="label"><span class="label-text font-medium">Password</span></label>
                    <input type="password" wire:model="password" class="input input-bordered w-full">
                    @if($userId)
                        <span class="text-xs text-base-content/50 block mt-1">* Kosongkan jika tidak ingin mengubah password.</span>
                    @endif
                    @error('password') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="border-t border-base-300 pt-4 mb-2">
                <h4 class="text-sm font-bold text-base-content mb-2">Pengaturan Akses & Wilayah</h4>
                
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <label class="label p-0"><span class="label-text font-medium">Role Sistem</span></label>
                        <button type="button" wire:click="openRoleModal" class="text-xs text-primary hover:underline font-semibold flex items-center">
                            <x-heroicon-s-plus class="w-3 h-3 mr-1" /> Buat Role Baru
                        </button>
                    </div>
                    <select wire:model="role" class="select select-bordered w-full">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ strtoupper($r->name) }}</option>
                        @endforeach
                    </select>
                    @error('role') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="mb-2">
                    <label class="label"><span class="label-text font-medium">Cakupan Region</span></label>
                    
                    <div class="max-h-40 overflow-y-auto border border-base-300 rounded-lg p-3 space-y-2 bg-base-200/50">
                        @forelse($availableRegions as $region)
                            <label class="flex items-center w-full cursor-pointer hover:bg-base-300/50 p-2 rounded-lg transition-colors">
                                <input type="checkbox" wire:model="region_code" value="{{ $region->region_code }}" class="checkbox checkbox-primary checkbox-sm">
                                <span class="ml-3 text-sm text-base-content">{{ $region->region_code }} - {{ $region->region_name }}</span>
                            </label>
                        @empty
                            <span class="text-xs text-base-content/50 block p-2">Data region tidak ditemukan.</span>
                        @endforelse
                    </div>

                    <p class="text-xs text-base-content/50 mt-2">* <b>Kosongkan</b> (jangan centang apapun) jika user ini adalah level Nasional / Pusat.</p>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-tambah-user').requestSubmit()">
                {{ $userId ? 'Update User' : 'Simpan User' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- Modal Tambah Role -->
    <x-ui.modal id="modal-tambah-role" title="Buat Role Baru" icon="shield-check" size="md" :dismissible="false" :open="$isRoleModalOpen" wire:close="$set('isRoleModalOpen', false)">
        <p class="text-xs text-base-content/60 mb-4">Role ini akan otomatis tersedia di pilihan role sistem.</p>
        
        <form wire:submit.prevent="storeRole" id="form-tambah-role">
            <div class="mb-4">
                <label class="label"><span class="label-text font-medium">Nama Role</span></label>
                <input type="text" wire:model="newRoleName" placeholder="Misal: admin_area" class="input input-bordered w-full">
                @error('newRoleName') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="text-[10px] text-base-content/50 mt-1">* Gunakan huruf kecil tanpa spasi (gunakan underscore jika perlu).</p>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isRoleModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-tambah-role').requestSubmit()">
                Simpan Role
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- Modal Akses Menu -->
    <x-ui.modal id="modal-akses-menu" title="Atur Akses Menu: {{ $name }}" icon="key" size="lg" :dismissible="false" :open="$isMenuModalOpen" wire:close="$set('isMenuModalOpen', false)">
        <p class="text-sm text-base-content/70 mb-4 border-b border-base-200 pb-2">Centang menu yang boleh diakses oleh pengguna ini.</p>
        
        <form wire:submit.prevent="storeMenuAccess" id="form-akses-menu">
            <div class="max-h-[60vh] overflow-y-auto pr-2">
                @if(count($allMenus) > 0)
                    <div class="space-y-4">
                        @foreach($allMenus as $menu)
                            <div class="bg-base-200/50 rounded-lg p-3 border border-base-300">
                                <!-- Level 1 -->
                                <label class="flex items-center cursor-pointer font-bold text-base-content">
                                    <input type="checkbox" wire:model="selectedUserMenus" value="{{ $menu['id'] }}" class="checkbox checkbox-primary checkbox-sm mr-3">
                                    {!! $menu['icon'] ?? '' !!} <span class="ml-2">{{ $menu['name'] }}</span>
                                </label>
                                
                                @if(count($menu['children'] ?? []) > 0)
                                    <div class="ml-7 mt-2 space-y-2 border-l-2 border-base-300 pl-3">
                                        @foreach($menu['children'] as $child1)
                                            <!-- Level 2 -->
                                            <div>
                                                <label class="flex items-center cursor-pointer font-medium text-base-content/90">
                                                    <input type="checkbox" wire:model="selectedUserMenus" value="{{ $child1['id'] }}" class="checkbox checkbox-secondary checkbox-sm mr-3">
                                                    {{ $child1['name'] }}
                                                </label>
                                                
                                                @if(count($child1['children'] ?? []) > 0)
                                                    <div class="ml-6 mt-2 space-y-2 border-l border-base-300 pl-3">
                                                        @foreach($child1['children'] as $child2)
                                                            <!-- Level 3 -->
                                                            <div>
                                                                <label class="flex items-center cursor-pointer text-sm text-base-content/80">
                                                                    <input type="checkbox" wire:model="selectedUserMenus" value="{{ $child2['id'] }}" class="checkbox checkbox-accent checkbox-xs mr-3">
                                                                    {{ $child2['name'] }}
                                                                </label>

                                                                @if(count($child2['children'] ?? []) > 0)
                                                                    <div class="ml-6 mt-1 space-y-1 pl-3 grid grid-cols-1 sm:grid-cols-2 gap-1">
                                                                        @foreach($child2['children'] as $child3)
                                                                            <!-- Level 4 -->
                                                                            <label class="flex items-center cursor-pointer text-xs text-base-content/70">
                                                                                <input type="checkbox" wire:model="selectedUserMenus" value="{{ $child3['id'] }}" class="checkbox checkbox-xs mr-2">
                                                                                {{ $child3['name'] }}
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/50">
                        <x-heroicon-o-document-text class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>Belum ada data menu di sistem.</p>
                    </div>
                @endif
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isMenuModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-akses-menu').requestSubmit()">
                Simpan Akses
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>