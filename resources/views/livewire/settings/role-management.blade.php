<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full">
        <x-slot name="title">Manajemen Role Sistem</x-slot>

        @include('livewire.settings._navigation')

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Data Role (Peran)</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data hak akses role yang dapat diberikan kepada user.</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari role..." />
                    <x-ui.action-button type="add" wire:click="create" label="Tambah Role" />
                </div>
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
        <div class="flex-1 overflow-auto bg-base-100 w-full relative p-3 md:p-4 lg:p-5">
            <x-ui.table empty="Belum ada data role" emptyIcon="shield-check" class="whitespace-nowrap border-0 shadow-none">
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
                <td class="text-right space-x-1">
                    <x-ui.button variant="primary" size="sm" outline="true" icon="key" wire:click="openMenuModal({{ $role->id }})">
                        Akses Menu
                    </x-ui.button>
                    @if($role->name !== 'national')
                        <x-ui.button variant="error" size="sm" outline="true" icon="trash" wire:click="delete({{ $role->id }})" onclick="return confirm('Peringatan: Menghapus role ini mungkin akan berdampak pada hak akses user yang memilikinya. Lanjutkan?')">
                            Hapus
                        </x-ui.button>
                    @else
                        <span class="text-base-content/50 italic text-xs inline-block px-2 py-1">Core Role</span>
                    @endif
                </td>
            </tr>
            @endforeach
            </x-ui.table>
        </div>

        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 bg-base-200/30">
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

    <!-- Modal Akses Menu -->
    <x-ui.modal id="modal-akses-menu" title="Atur Akses Menu Role: {{ $roleNameForMenu }}" icon="key" size="xl" boxClass="!w-11/12 !max-w-5xl" :dismissible="false" :open="$isMenuModalOpen" wire:close="$set('isMenuModalOpen', false)">
        <p class="text-sm text-base-content/70 mb-4 border-b border-base-200 pb-2">Centang menu yang boleh diakses oleh pengguna dengan role ini.</p>
        
        <form wire:submit.prevent="storeMenuAccess" id="form-akses-menu-role">
            <div class="max-h-[60vh] overflow-y-auto pr-2">
                @if(count($allMenus) > 0)
                    <table class="table table-sm w-full bg-base-100 rounded-lg">
                        <thead class="bg-base-200">
                            <tr>
                                <th class="align-top">Menu / Halaman</th>

                                <th class="text-center align-top">
                                    <div class="flex flex-col items-center">
                                        <span>Tambah</span>
                                        <button type="button" wire:click="toggleAll('can_add')" class="text-[10px] bg-base-200 hover:bg-base-300 px-2 py-0.5 rounded mt-1 transition-colors">Check All</button>
                                    </div>
                                </th>
                                <th class="text-center align-top">
                                    <div class="flex flex-col items-center">
                                        <span>Edit</span>
                                        <button type="button" wire:click="toggleAll('can_edit')" class="text-[10px] bg-base-200 hover:bg-base-300 px-2 py-0.5 rounded mt-1 transition-colors">Check All</button>
                                    </div>
                                </th>
                                <th class="text-center align-top">
                                    <div class="flex flex-col items-center">
                                        <span>Hapus</span>
                                        <button type="button" wire:click="toggleAll('can_delete')" class="text-[10px] bg-base-200 hover:bg-base-300 px-2 py-0.5 rounded mt-1 transition-colors">Check All</button>
                                    </div>
                                </th>
                                <th class="text-center align-top">
                                    <div class="flex flex-col items-center">
                                        <span>Import</span>
                                        <button type="button" wire:click="toggleAll('can_import')" class="text-[10px] bg-base-200 hover:bg-base-300 px-2 py-0.5 rounded mt-1 transition-colors">Check All</button>
                                    </div>
                                </th>
                                <th class="text-center align-top">
                                    <div class="flex flex-col items-center">
                                        <span>Export</span>
                                        <button type="button" wire:click="toggleAll('can_export')" class="text-[10px] bg-base-200 hover:bg-base-300 px-2 py-0.5 rounded mt-1 transition-colors">Check All</button>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allMenus as $menu)
                                <tr class="hover:bg-base-200/50">
                                     <td class="font-bold flex items-center">
                                        <div class="w-5 h-5 mr-2 flex justify-center items-center">{!! $menu['icon'] ?? '' !!}</div>
                                        <span>{{ $menu['name'] }}</span>
                                    </td>
                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $menu['id'] }}.can_add" class="checkbox checkbox-primary checkbox-sm"></td>
                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $menu['id'] }}.can_edit" class="checkbox checkbox-secondary checkbox-sm"></td>
                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $menu['id'] }}.can_delete" class="checkbox checkbox-error checkbox-sm"></td>
                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $menu['id'] }}.can_import" class="checkbox checkbox-accent checkbox-sm"></td>
                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $menu['id'] }}.can_export" class="checkbox checkbox-info checkbox-sm"></td>
                                </tr>
                                
                                @if(count($menu['children'] ?? []) > 0)
                                    @foreach($menu['children'] as $child1)
                                        <tr class="hover:bg-base-200/50 border-t border-base-200">
                                            <td class="pl-10 font-medium text-base-content/90">{{ $child1['name'] }}</td>
                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child1['id'] }}.can_add" class="checkbox checkbox-primary checkbox-sm"></td>
                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child1['id'] }}.can_edit" class="checkbox checkbox-secondary checkbox-sm"></td>
                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child1['id'] }}.can_delete" class="checkbox checkbox-error checkbox-sm"></td>
                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child1['id'] }}.can_import" class="checkbox checkbox-accent checkbox-sm"></td>
                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child1['id'] }}.can_export" class="checkbox checkbox-info checkbox-sm"></td>
                                        </tr>
                                        
                                        @if(count($child1['children'] ?? []) > 0)
                                            @foreach($child1['children'] as $child2)
                                                <tr class="hover:bg-base-200/50">
                                                    <td class="pl-16 text-sm text-base-content/80">{{ $child2['name'] }}</td>
                                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child2['id'] }}.can_add" class="checkbox checkbox-primary checkbox-sm"></td>
                                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child2['id'] }}.can_edit" class="checkbox checkbox-secondary checkbox-sm"></td>
                                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child2['id'] }}.can_delete" class="checkbox checkbox-error checkbox-sm"></td>
                                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child2['id'] }}.can_import" class="checkbox checkbox-accent checkbox-sm"></td>
                                                    <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child2['id'] }}.can_export" class="checkbox checkbox-info checkbox-sm"></td>
                                                </tr>

                                                @if(count($child2['children'] ?? []) > 0)
                                                    @foreach($child2['children'] as $child3)
                                                        <tr class="hover:bg-base-200/50 bg-base-200/30">
                                                            <td class="pl-20 text-xs text-base-content/70">{{ $child3['name'] }}</td>
                                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child3['id'] }}.can_add" class="checkbox checkbox-primary checkbox-xs"></td>
                                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child3['id'] }}.can_edit" class="checkbox checkbox-secondary checkbox-xs"></td>
                                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child3['id'] }}.can_delete" class="checkbox checkbox-error checkbox-xs"></td>
                                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child3['id'] }}.can_import" class="checkbox checkbox-accent checkbox-xs"></td>
                                                            <td class="text-center"><input type="checkbox" wire:model="rolePermissions.{{ $child3['id'] }}.can_export" class="checkbox checkbox-info checkbox-xs"></td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
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
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-akses-menu-role').requestSubmit()">
                Simpan Akses
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>