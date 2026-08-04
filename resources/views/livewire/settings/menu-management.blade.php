<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full">
        <x-slot name="title">Manajemen Menu</x-slot>

        @include('livewire.settings._navigation')

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Struktur Menu Sidebar</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data master menu aplikasi (tambah, edit, hapus).</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <x-ui.action-button type="add" wire:click="create" label="Tambah Menu" />
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

        <!-- List Menu Berbentuk Pohon -->
        <div class="flex-1 overflow-auto bg-base-100 w-full relative p-3 md:p-4 lg:p-5">
                @if(count($menus) > 0)
                    <div class="space-y-4">
                        @foreach($menus as $menu)
                            @php
                                $isGroupHeader = empty($menu->icon) && empty($menu->route);
                            @endphp
                            <div class="{{ $isGroupHeader ? 'bg-base-300/50 border-base-300 shadow-sm' : 'bg-base-200/30 border-base-300' }} rounded-lg p-3 border">
                                <!-- Level 1 -->
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center font-bold {{ $isGroupHeader ? 'text-base-content/70 text-xs uppercase tracking-wider' : 'text-base-content' }}">
                                        @if($menu->icon) <span class="w-5 h-5 inline-flex items-center justify-center shrink-0 [&>svg]:w-full [&>svg]:h-full mr-2">{!! $menu->icon !!}</span> @elseif(!$isGroupHeader) <span class="w-5 h-5 inline-block shrink-0 mr-2"></span> @endif
                                        <span class="mr-2 text-[10px] font-mono text-base-content/50 bg-base-300/50 px-1.5 py-0.5 rounded border border-base-300" title="Nomor Urut">{{ $menu->order_number }}</span>
                                        <span>{{ $menu->name }}</span>
                                        @if($menu->route) <span class="ml-2 text-xs font-normal text-base-content/50 bg-base-300 px-2 rounded">{{ $menu->route }}</span> @endif
                                        @if($isGroupHeader) <span class="ml-3 text-[10px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-semibold">GROUP HEADER</span> @endif
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
                                                        <span class="mr-2 text-xs text-base-content/40">L2</span>
                                                        <span class="mr-2 text-[10px] font-mono text-base-content/50 bg-base-200 px-1.5 py-0.5 rounded border border-base-300" title="Nomor Urut">{{ $child1->order_number }}</span>
                                                        <span>{{ $child1->name }}</span>
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
                                                                    <span class="mr-2 text-[10px] text-base-content/40">L3</span>
                                                                    <span class="mr-2 text-[10px] font-mono text-base-content/50 bg-base-200 px-1.5 py-0.5 rounded border border-base-300" title="Nomor Urut">{{ $child2->order_number }}</span>
                                                                    <span>{{ $child2->name }}</span>
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
                                                                                <span class="mr-2 text-[8px] text-base-content/40">L4</span>
                                                                                <span class="mr-2 text-[10px] font-mono text-base-content/50 bg-base-200 px-1.5 py-0.5 rounded border border-base-300" title="Nomor Urut">{{ $child3->order_number }}</span>
                                                                                <span>{{ $child3->name }}</span>
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
