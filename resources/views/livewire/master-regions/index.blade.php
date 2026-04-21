<div>
    <x-slot name="title">Data Master Region</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">
        {{-- Notifikasi --}}
        <div class="mb-6">
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">{{ session('message') }}</div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                </div>
            @endif
        </div>

        <x-card flush title="Master Region" icon="map" subtitle="Kelola data wilayah cakupan operasional" class="pb-6">
            <x-slot:actions>
                <div class="flex items-center gap-3">
                    {{-- Pencarian --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Region..." 
                               class="input input-sm input-bordered pl-10 w-48 sm:w-64 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Tombol Tambah --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            {{-- Tabel Data --}}
            <x-ui.table loading="{{ false }}" empty="Tidak ada data region ditemukan.">
                <x-slot:head>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Kode Region</th>
                        <th>Nama Region</th>
                        <th class="hidden md:table-cell">Dibuat Pada</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($regions as $index => $region)
                    <tr wire:key="region-{{ $region->region_code }}" class="group">
                        <td>
                            <span class="text-xs font-semibold text-base-content/40">{{ $regions->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-sm badge-outline border-base-300 text-primary font-bold px-2 py-3 rounded-lg">{{ $region->region_code }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="font-medium text-base-content/80 group-hover:text-primary transition-colors">{{ $region->region_name }}</span>
                        </td>
                        <td class="hidden md:table-cell">
                            <div class="flex flex-col text-[11px] text-base-content/50">
                                <span class="font-medium">{{ $region->created_at->translatedFormat('d M Y') }}</span>
                                <span>{{ $region->created_at->format('H:i') }} WIB</span>
                            </div>
                        </td>
                        <td>
                            @unless(auth()->user()->hasRole('guest'))
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="openEditModal('{{ $region->region_code }}')" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete('{{ $region->region_code }}')" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                            </div>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if($regions->hasPages())
                <div class="mt-4 px-2">
                    {{ $regions->links() }}
                </div>
            @endif
        </x-card>
    </div>

    {{-- Modal Form (Create/Edit) --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden ring-1 ring-base-content/5">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Region' : 'Tambah Region Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ $isEditing ? 'Perbarui data region yang sudah ada' : 'Daftarkan region baru ke dalam sistem' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-5 bg-base-100">
                    {{-- Kode Region --}}
                    <div class="space-y-1.5">
                        <label for="region_code" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Region</label>
                        <div class="relative group">
                            <input wire:model.blur="region_code" type="text" id="region_code" placeholder="Contoh: INAJWA1"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('region_code') input-error @enderror"
                                   {{ $isEditing ? 'disabled' : '' }}>
                            @if($isEditing)
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                    <x-heroicon-s-lock-closed class="w-4 h-4" />
                                </div>
                            @endif
                        </div>
                        @error('region_code') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    {{-- Nama Region --}}
                    <div class="space-y-1.5">
                        <label for="region_name" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Region</label>
                        <input wire:model.blur="region_name" type="text" id="region_name" placeholder="Contoh: INA JAWA 1"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('region_name') input-error @enderror">
                        @error('region_name') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Tambahkan Region' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5">
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Region?</h3>
                <p class="text-sm text-base-content/60 leading-relaxed px-4">Apakah Anda yakin ingin menghapus region ini? Tindakan ini <span class="text-error font-bold italic">tidak dapat dibatalkan</span> dan data terkait mungkin ikut terhapus.</p>
            </div>

            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case transition-all duration-200">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 transition-all duration-200 text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>
</div>


