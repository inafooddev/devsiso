<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-base-content">Manajemen Access Group (View)</h2>
                <p class="text-sm text-base-content/70 mt-1">Kelola data grup yang mengatur visibilitas menu sidebar untuk user.</p>
            </div>
            <x-ui.button variant="primary" icon="plus" wire:click="create">
                Tambah Grup
            </x-ui.button>
        </div>

    @if (session()->has('message'))
        <div class="mb-6">
            <x-ui.notif type="success" dismissible="true">
                {{ session('message') }}
            </x-ui.notif>
        </div>
    @endif

    <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 overflow-hidden">
        <x-ui.table>
            <x-slot:head>
                <tr>
                    <th class="w-16">ID</th>
                    <th>NAMA GRUP</th>
                    <th>DESKRIPSI</th>
                    <th class="text-right">AKSI</th>
                </tr>
            </x-slot:head>
            
            @foreach($groups as $group)
            <tr>
                <td>{{ $group->id }}</td>
                <td class="font-bold text-base-content">{{ strtoupper($group->name) }}</td>
                <td class="text-base-content/70">{{ $group->description ?? '-' }}</td>
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

        <div class="mt-4 p-4">
            @if(method_exists($groups, 'links'))
                {{ $groups->links() }}
            @endif
        </div>
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
        <p class="text-sm text-base-content/70 mb-4 border-b border-base-200 pb-2">Centang menu yang akan muncul di sidebar untuk pengguna dalam grup ini.</p>
        
        <form wire:submit.prevent="storeMenuAccess" id="form-akses-menu">
            <div class="max-h-[60vh] overflow-y-auto pr-2">
                @if(count($allMenus) > 0)
                    <div class="space-y-4">
                        @foreach($allMenus as $menu)
                            <div class="bg-base-200/50 rounded-lg p-3 border border-base-300">
                                <!-- Level 1 -->
                                <label class="flex items-center cursor-pointer font-bold text-base-content">
                                    <input type="checkbox" wire:model="selectedMenus" value="{{ $menu['id'] }}" class="checkbox checkbox-primary checkbox-sm mr-3">
                                    {!! $menu['icon'] ?? '' !!} <span class="ml-2">{{ $menu['name'] }}</span>
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
    </div>
</div>
