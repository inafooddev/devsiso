<div>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-base-content">Manajemen Menu</h2>
                <p class="text-sm text-base-content/70 mt-1">Kelola data master menu aplikasi (tambah, edit, hapus).</p>
            </div>
            <x-ui.button variant="primary" icon="plus" wire:click="create">  
                Tambah Menu
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

        <!-- List Menu Berbentuk Pohon -->
        <div class="bg-base-100 shadow-sm border border-base-200 rounded-xl overflow-hidden">
            <div class="p-4 border-b border-base-200 bg-base-200/50">
                <h3 class="font-semibold text-base-content">Struktur Menu Sidebar</h3>
            </div>
            <div class="p-4">
                @if(count($menus) > 0)
                    <div class="space-y-4">
                        @foreach($menus as $menu)
                            <div class="bg-base-200/30 rounded-lg p-3 border border-base-300">
                                <!-- Level 1 -->
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center font-bold text-base-content">
                                        @if($menu->icon) {!! $menu->icon !!} @else <span class="w-5 h-5 inline-block"></span> @endif
                                        <span class="ml-2">{{ $menu->name }}</span>
                                        @if($menu->route) <span class="ml-2 text-xs font-normal text-base-content/50 bg-base-300 px-2 rounded">{{ $menu->route }}</span> @endif
                                    </div>
                                    <div class="space-x-1 flex-shrink-0">
                                        <x-ui.button variant="primary" size="xs" outline="true" icon="pencil" wire:click="edit({{ $menu->id }})">Edit</x-ui.button>
                                        <x-ui.button variant="error" size="xs" outline="true" icon="trash" wire:click="delete({{ $menu->id }})" onclick="return confirm('Yakin hapus menu ini beserta anak-anaknya?')">Hapus</x-ui.button>
                                    </div>
                                </div>
                                
                                @if($menu->children->isNotEmpty())
                                    <div class="ml-7 mt-2 space-y-2 border-l-2 border-base-300 pl-3">
                                        @foreach($menu->children as $child1)
                                            <!-- Level 2 -->
                                            <div class="bg-base-100 rounded p-2 border border-base-200">
                                                <div class="flex justify-between items-center">
                                                    <div class="font-medium text-base-content/90 flex items-center">
                                                        <span class="mr-2 text-xs text-base-content/40">L2</span> {{ $child1->name }}
                                                        @if($child1->route) <span class="ml-2 text-[10px] text-base-content/50 bg-base-200 px-1.5 rounded">{{ $child1->route }}</span> @endif
                                                    </div>
                                                    <div class="space-x-1 flex-shrink-0">
                                                        <x-ui.button variant="primary" size="xs" outline="true" wire:click="edit({{ $child1->id }})">Edit</x-ui.button>
                                                        <x-ui.button variant="error" size="xs" outline="true" wire:click="delete({{ $child1->id }})" onclick="return confirm('Yakin hapus?')">Hapus</x-ui.button>
                                                    </div>
                                                </div>
                                                
                                                @if($child1->children->isNotEmpty())
                                                    <div class="ml-6 mt-2 space-y-2 border-l border-base-300 pl-3">
                                                        @foreach($child1->children as $child2)
                                                            <!-- Level 3 -->
                                                            <div class="flex justify-between items-center">
                                                                <div class="text-sm text-base-content/80 flex items-center">
                                                                    <span class="mr-2 text-[10px] text-base-content/40">L3</span> {{ $child2->name }}
                                                                    @if($child2->route) <span class="ml-2 text-[10px] text-base-content/50 bg-base-200 px-1.5 rounded">{{ $child2->route }}</span> @endif
                                                                </div>
                                                                <div class="space-x-1 flex-shrink-0">
                                                                    <button wire:click="edit({{ $child2->id }})" class="text-xs text-primary hover:underline">Edit</button>
                                                                    <button wire:click="delete({{ $child2->id }})" onclick="return confirm('Yakin?')" class="text-xs text-error hover:underline ml-2">Hapus</button>
                                                                </div>
                                                            </div>

                                                            @if($child2->children->isNotEmpty())
                                                                <div class="ml-6 mt-1 space-y-1 pl-3 border-l border-base-300/50">
                                                                    @foreach($child2->children as $child3)
                                                                        <!-- Level 4 -->
                                                                        <div class="flex justify-between items-center">
                                                                            <div class="text-xs text-base-content/70 flex items-center">
                                                                                <span class="mr-2 text-[8px] text-base-content/40">L4</span> {{ $child3->name }}
                                                                                @if($child3->route) <span class="ml-2 text-[10px] text-base-content/40 bg-base-200 px-1.5 rounded">{{ $child3->route }}</span> @endif
                                                                            </div>
                                                                            <div class="space-x-1 flex-shrink-0">
                                                                                <button wire:click="edit({{ $child3->id }})" class="text-[10px] text-primary hover:underline">Edit</button>
                                                                                <button wire:click="delete({{ $child3->id }})" onclick="return confirm('Yakin?')" class="text-[10px] text-error hover:underline ml-2">Hapus</button>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
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
        </div>
    </div>

    <!-- Modal Tambah/Edit Menu -->
    <x-ui.modal id="modal-menu" title="{{ $menuId ? 'Edit Menu' : 'Tambah Menu Baru' }}" icon="{{ $menuId ? 'pencil' : 'plus' }}" size="md" :dismissible="false" :open="$isModalOpen" wire:close="$set('isModalOpen', false)">
        <form wire:submit.prevent="store" id="form-menu">
            <div class="space-y-4 mb-4">
                <div>
                    <label class="label"><span class="label-text font-medium">Nama Menu</span></label>
                    <input type="text" wire:model="name" placeholder="Misal: Penjualan" class="input input-bordered w-full">
                    @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="label"><span class="label-text font-medium">URL / Route</span></label>
                    <input type="text" wire:model="route" placeholder="Misal: sales.index (atau http://...)" class="input input-bordered w-full">
                    <p class="text-[10px] text-base-content/50 mt-1">Kosongkan jika menu ini hanya memiliki sub-menu (parent).</p>
                    @error('route') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="label"><span class="label-text font-medium">Parent Menu</span></label>
                    <select wire:model="parent_id" class="select select-bordered w-full">
                        <option value="">-- Tidak Ada (Jadikan Menu Utama) --</option>
                        @foreach($flatMenus as $fm)
                            @if($fm->id != $menuId)
                                <option value="{{ $fm->id }}">{{ $fm->name }} {{ $fm->parent_id ? '(Sub)' : '' }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('parent_id') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label"><span class="label-text font-medium">Nomor Urut</span></label>
                        <input type="number" wire:model="order_number" min="1" class="input input-bordered w-full">
                        @error('order_number') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div>
                    <label class="label"><span class="label-text font-medium">SVG Icon (Opsional)</span></label>
                    <textarea wire:model="icon" class="textarea textarea-bordered w-full h-24 font-mono text-xs" placeholder='<svg>...</svg>'></textarea>
                    <p class="text-[10px] text-base-content/50 mt-1">Hanya perlu diisi untuk menu utama (Level 1).</p>
                    @error('icon') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot:footer>
            <x-ui.button variant="ghost" type="button" wire:click="$set('isModalOpen', false)">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="button" onclick="document.getElementById('form-menu').requestSubmit()">
                {{ $menuId ? 'Update Menu' : 'Simpan Menu' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
