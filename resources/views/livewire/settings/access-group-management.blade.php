<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full">
        <x-slot name="title">View Menu (Akses Grup)</x-slot>

        @include('livewire.settings._navigation')

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Manajemen Access Group (View Menu)</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data grup yang mengatur visibilitas menu sidebar untuk user.</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari grup..." />
                    <x-ui.action-button type="add" wire:click="create" label="Tambah Grup" />
                </div>
            </div>

    @if (session()->has('message'))
        <div class="mb-6">
            <x-ui.notif type="success" dismissible="true">
                {{ session('message') }}
            </x-ui.notif>
        </div>
    @endif

    <div class="flex-1 overflow-auto bg-base-100 w-full relative p-3 md:p-4 lg:p-5">
        <x-ui.table class="whitespace-nowrap border-0 shadow-none">
            <x-slot:head>
                <tr>
                    <th class="w-16">ID</th>
                    <th>NAMA GRUP</th>
                    <th>DESKRIPSI</th>
                    <th class="text-center">JUMLAH USER</th>
                    <th class="text-right">AKSI</th>
                </tr>
            </x-slot:head>
            
            @foreach($groups as $group)
            <tr>
                <td>{{ $group->id }}</td>
                <td class="font-bold text-base-content">{{ strtoupper($group->name) }}</td>
                <td class="text-base-content/70">{{ $group->description ?? '-' }}</td>
                <td class="text-center">
                    @if($group->users_count > 0)
                        <button wire:click="openUserModal({{ $group->id }})" class="btn btn-xs btn-outline btn-info rounded-full px-3 shadow-sm hover:scale-105 transition-transform">{{ $group->users_count }} User</button>
                    @else
                        <span class="text-base-content/40 text-xs font-medium bg-base-200 px-2 py-0.5 rounded-full">0</span>
                    @endif
                </td>
                <td class="text-right space-x-1">
                    <x-ui.button variant="primary" size="sm" outline="true" icon="eye" wire:click="openMenuModal({{ $group->id }})">
                        Akses View
                    </x-ui.button>
                    <x-ui.button variant="primary" size="sm" outline="true" icon="pencil" wire:click="edit({{ $group->id }})">
                        Edit
                    </x-ui.button>
                    <x-ui.button variant="error" size="sm" outline="true" icon="trash" wire:click="delete({{ $group->id }})" onclick="return confirm('Yakin ingin menghapus grup ini?')">
                        Hapus
                    </x-ui.button>
                </td>
            </tr>
            @endforeach
        </x-ui.table>
    </div>

    <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 bg-base-200/30">
        @if(method_exists($groups, 'links'))
            {{ $groups->links() }}
        @endif
    </div>

    <!-- Modal Tambah/Edit Grup -->
    <x-ui.modal id="modal-tambah-grup" title="{{ $groupId ? 'Edit Grup' : 'Tambah Grup Baru' }}" icon="{{ $groupId ? 'pencil' : 'plus' }}" size="md" :dismissible="false" :open="$isModalOpen" wire:close="$set('isModalOpen', false)">
        <form wire:submit.prevent="store" id="form-tambah-grup">
            <div class="mb-4">
                <label class="label"><span class="label-text font-medium">Nama Grup</span></label>
                <input type="text" wire:model="name" placeholder="Misal: Finance Division" class="input input-bordered w-full">
                @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="label"><span class="label-text font-medium">Deskripsi</span></label>
                <textarea wire:model="description" class="textarea textarea-bordered w-full" placeholder="Opsional"></textarea>
                @error('description') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-tambah-grup').requestSubmit()">
                {{ $groupId ? 'Update Grup' : 'Simpan Grup' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- Modal Akses View Menu -->
    <x-ui.modal id="modal-akses-menu" title="Atur Visibilitas Menu: {{ $groupNameForMenu }}" icon="eye" size="lg" :dismissible="false" :open="$isMenuModalOpen" wire:close="$set('isMenuModalOpen', false)">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-4 border-b border-base-200 pb-3">
            <p class="text-sm text-base-content/70">Centang menu yang akan muncul di sidebar.</p>
            <div class="space-x-1 shrink-0">
                <button type="button" wire:click="selectAllMenus" class="btn btn-xs btn-outline btn-primary">Pilih Semua</button>
                <button type="button" wire:click="unselectAllMenus" class="btn btn-xs btn-outline btn-error">Hapus Semua</button>
            </div>
        </div>
        
        <form wire:submit.prevent="storeMenuAccess" id="form-akses-menu">
            <div class="max-h-[60vh] overflow-y-auto pr-2">
                @if(count($allMenus) > 0)
                    <div class="space-y-4">
                        @foreach($allMenus as $menu)
                            @php
                                $isGroupHeader = empty($menu['icon']) && empty($menu['route']);
                            @endphp
                            <div class="{{ $isGroupHeader ? 'bg-base-300/50 border-base-300 shadow-sm' : 'bg-base-200/50 border-base-300' }} rounded-lg p-3 border">
                                <!-- Level 1 -->
                                <label class="flex items-center cursor-pointer font-bold {{ $isGroupHeader ? 'text-base-content/70 text-xs uppercase tracking-wider' : 'text-base-content' }}">
                                    <input type="checkbox" wire:model="selectedMenus" value="{{ $menu['id'] }}" class="checkbox checkbox-primary checkbox-sm mr-3">
                                    @if(!empty($menu['icon'])) <span class="w-5 h-5 inline-flex items-center justify-center shrink-0 [&>svg]:w-full [&>svg]:h-full">{!! $menu['icon'] !!}</span> @endif
                                    <span class="{{ !empty($menu['icon']) ? 'ml-2' : '' }}">{{ $menu['name'] }}</span>
                                    @if($isGroupHeader) <span class="ml-3 text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-semibold normal-case tracking-normal">GROUP HEADER</span> @endif
                                </label>
                                
                                @if(count($menu['children'] ?? []) > 0)
                                    <div class="ml-7 mt-2 space-y-2 border-l-2 border-base-300 pl-3">
                                        @foreach($menu['children'] as $child1)
                                            <!-- Level 2 -->
                                            <div>
                                                <label class="flex items-center cursor-pointer font-medium text-base-content/90">
                                                    <input type="checkbox" wire:model="selectedMenus" value="{{ $child1['id'] }}" class="checkbox checkbox-secondary checkbox-sm mr-3">
                                                    {{ $child1['name'] }}
                                                </label>
                                                
                                                @if(count($child1['children'] ?? []) > 0)
                                                    <div class="ml-6 mt-2 space-y-2 border-l border-base-300 pl-3">
                                                        @foreach($child1['children'] as $child2)
                                                            <!-- Level 3 -->
                                                            <div>
                                                                <label class="flex items-center cursor-pointer text-sm text-base-content/80">
                                                                    <input type="checkbox" wire:model="selectedMenus" value="{{ $child2['id'] }}" class="checkbox checkbox-accent checkbox-xs mr-3">
                                                                    {{ $child2['name'] }}
                                                                </label>

                                                                @if(count($child2['children'] ?? []) > 0)
                                                                    <div class="ml-6 mt-1 space-y-1 pl-3 grid grid-cols-1 sm:grid-cols-2 gap-1">
                                                                        @foreach($child2['children'] as $child3)
                                                                            <!-- Level 4 -->
                                                                            <label class="flex items-center cursor-pointer text-xs text-base-content/70">
                                                                                <input type="checkbox" wire:model="selectedMenus" value="{{ $child3['id'] }}" class="checkbox checkbox-xs mr-2">
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

    <!-- Modal Daftar User -->
    <x-ui.modal id="modal-daftar-user" title="Daftar User: {{ $groupNameForUsers }}" icon="users" size="lg" :dismissible="true" :open="$isUserModalOpen" wire:close="$set('isUserModalOpen', false)">
        <div class="max-h-[60vh] overflow-y-auto pr-2">
            @if(count($selectedGroupUsers) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($selectedGroupUsers as $user)
                        <div class="bg-base-200/50 border border-base-300 rounded-lg p-3 flex flex-col hover:bg-base-200 transition-colors">
                            <span class="font-bold text-base-content">{{ $user['name'] }}</span>
                            <span class="text-xs font-mono text-base-content/60 mt-1">ID: {{ $user['userid'] ?? $user['id'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-base-content/50">
                    <x-heroicon-o-users class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>Tidak ada user yang terdaftar di grup ini.</p>
                </div>
            @endif
        </div>
        
        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isUserModalOpen', false)">
                Tutup
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
    </div>
</div>
