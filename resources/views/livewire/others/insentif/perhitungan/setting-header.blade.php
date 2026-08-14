<div class="flex-1 flex flex-col w-full h-full min-h-0 gap-4 lg:gap-6 lg:flex-row">
    
    <!-- Left Panel: FORM -->
    <div class="w-full lg:w-1/3 flex flex-col min-h-0 bg-base-100 rounded-xl shadow-xl border border-base-300 overflow-hidden">
        <div class="p-4 border-b border-base-300 bg-base-200/30 flex justify-between items-center shrink-0">
            <div>
                <h2 class="font-bold text-lg">{{ $headerId ? 'Edit' : 'Buat' }} Header Grup</h2>
                <p class="text-[10px] uppercase font-semibold text-base-content/60 tracking-wider">Mapping Master Produk</p>
            </div>
            @if($headerId)
                <button wire:click="resetForm" class="btn btn-ghost btn-sm btn-square rounded-xl" title="Batal Edit">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            @endif
        </div>
        
        <div class="p-4 overflow-y-auto custom-scrollbar flex-1">
            <form wire:submit.prevent="save" class="space-y-4">
                
                <div>
                    <label class="block text-sm font-medium mb-1.5">Nama Header Grup Baru</label>
                    <input type="text" wire:model.defer="nama_header" class="input input-bordered input-sm w-full rounded-xl" placeholder="Misal: Grup Prioritas A" required>
                    @error('nama_header') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5 flex justify-between items-end">
                        <span>Pilih Raw Product Group 3</span>
                        <span class="text-[10px] text-base-content/50 font-normal">Pilih minimal 1</span>
                    </label>
                    
                    <div class="mb-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="searchGroup" class="input input-bordered input-sm w-full pl-9 rounded-xl" placeholder="Cari grup...">
                        </div>
                    </div>
                    
                    @error('selected_groups') 
                        <div class="alert alert-error py-1.5 px-3 rounded-lg text-xs mb-3 shadow-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="space-y-2 border border-base-300 rounded-xl p-3 bg-base-200/20 max-h-64 overflow-y-auto custom-scrollbar">
                        @forelse($rawGroups as $pg3)
                            @php
                                $isUsedByOther = isset($mappedGroups[$pg3]) && $mappedGroups[$pg3]->first()->insentif_header_grup_id != $headerId;
                            @endphp
                            <div class="flex items-center justify-between p-2 rounded-lg border {{ $isUsedByOther ? 'bg-base-200/50 border-base-200 opacity-60' : 'bg-base-100 border-base-300 hover:border-primary/50' }}">
                                <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                    <input type="checkbox" wire:model.defer="selected_groups" value="{{ $pg3 }}" 
                                        class="checkbox checkbox-sm checkbox-primary rounded-md"
                                        @if($isUsedByOther) disabled @endif>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-semibold truncate {{ $isUsedByOther ? 'line-through text-base-content/50' : '' }}">
                                            {{ $pg3 ?: '(Tanpa Nama Grup)' }}
                                        </span>
                                        @if($isUsedByOther)
                                            <span class="text-[10px] text-error">Sudah Dipakai</span>
                                        @endif
                                    </div>
                                </label>
                                <button type="button" wire:click="openProductModal('{{ $pg3 }}')" class="btn btn-ghost btn-xs text-primary shrink-0 rounded-lg ml-2">
                                    <x-heroicon-o-eye class="w-3.5 h-3.5" /> Liht Prdk
                                </button>
                            </div>
                        @empty
                            <div class="text-center py-4 text-xs text-base-content/50 italic">
                                Belum ada Master Data Produk. Jalankan Job ke-6 terlebih dahulu.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1.5 flex justify-between items-end">
                        <span>Pilih Region Target</span>
                        <span class="text-[10px] text-base-content/50 font-normal">Pilih minimal 1</span>
                    </label>

                    @error('selected_regions') 
                        <div class="alert alert-error py-1.5 px-3 rounded-lg text-xs mb-3 shadow-sm">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid grid-cols-2 gap-2 border border-base-300 rounded-xl p-3 bg-base-200/20 max-h-40 overflow-y-auto custom-scrollbar">
                        @forelse($rawRegions as $reg)
                            <label class="flex items-center gap-2 cursor-pointer p-1.5 hover:bg-base-200/50 rounded-lg transition-colors">
                                <input type="checkbox" wire:model.defer="selected_regions" value="{{ $reg }}" class="checkbox checkbox-sm checkbox-primary rounded-md">
                                <span class="text-xs font-semibold truncate">{{ $reg }}</span>
                            </label>
                        @empty
                            <div class="col-span-2 text-center py-2 text-xs text-base-content/50 italic">
                                Belum ada Region. Jalankan Job Master Distributor.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary btn-sm w-full rounded-xl shadow-sm shadow-primary/20 normal-case">
                        {{ $headerId ? 'Simpan Perubahan' : 'Buat Header Grup' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Panel: TABLE -->
    <div class="w-full lg:w-2/3 flex flex-col min-h-0 bg-base-100 rounded-xl shadow-xl border border-base-300 overflow-hidden">
        <div class="p-4 border-b border-base-300 bg-base-200/30 shrink-0 flex justify-between items-center">
            <h2 class="font-bold text-lg">Daftar Header Grup</h2>
            @if (session()->has('success'))
                <span class="text-xs text-success font-bold bg-success/10 px-2 py-1 rounded-lg animate-pulse">
                    {{ session('success') }}
                </span>
            @endif
        </div>
        
        <div class="flex-1 overflow-x-auto custom-scrollbar">
            <table class="table table-sm table-pin-rows table-pin-cols w-full">
                <thead>
                    <tr class="bg-base-200 text-base-content/70">
                        <th class="w-10 text-center">#</th>
                        <th>Nama Header</th>
                        <th>Product Group 3</th>
                        <th>Region Target</th>
                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($headers as $index => $h)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="text-center text-xs text-base-content/50">{{ $index + 1 }}</td>
                            <td class="font-bold text-sm">{{ $h->nama_header }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1.5 max-w-[200px]">
                                    @foreach($h->details as $d)
                                        <span class="badge badge-sm badge-ghost border-base-300 bg-base-100 rounded-md text-[10px] font-semibold">{{ $d->product_group_3 ?: 'N/A' }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1 max-w-[150px]">
                                    @foreach($h->regions as $r)
                                        <span class="badge badge-sm badge-primary badge-outline rounded-md text-[9px] uppercase font-bold">{{ $r->region_name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <button wire:click="edit({{ $h->id }})" class="btn btn-square btn-ghost btn-xs text-info rounded-lg" title="Edit">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button onclick="confirm('Yakin ingin menghapus Header Grup ini?') || event.stopImmediatePropagation()" 
                                        wire:click="delete({{ $h->id }})" class="btn btn-square btn-ghost btn-xs text-error rounded-lg" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-base-content/50 text-sm">
                                Belum ada Header Grup yang dibuat. Silakan buat di form sebelah kiri.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Lihat Produk -->
    <div x-data="{ open: @entangle('isProductModalOpen') }" x-show="open" x-cloak wire:ignore.self class="fixed z-50 inset-0 flex items-center justify-center">
        <!-- Backdrop -->
        <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" @click="open = false"></div>

        <!-- Modal Box -->
        <div x-show="open"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-2xl shadow-2xl ring-1 ring-base-300 w-full max-w-lg mx-4 flex flex-col max-h-[80vh] overflow-hidden">
            
            <div class="px-5 pt-5 pb-3 border-b border-base-300 shrink-0 flex justify-between items-center bg-base-200/30">
                <div>
                    <h3 class="font-bold text-base">Referensi Produk</h3>
                    <p class="text-xs text-base-content/60 mt-0.5">Isi dari: <span class="font-bold text-primary">{{ $modalProductGroup ?: '(Tanpa Grup)' }}</span></p>
                </div>
                <button @click="open = false" type="button" class="btn btn-ghost btn-sm btn-square rounded-xl">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-5">
                @if(count($modalProducts) > 0)
                    <div class="flex flex-col gap-2">
                        @foreach($modalProducts as $prod)
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-base-200/30 border border-base-300/50 hover:border-primary/30 transition-colors">
                                <x-heroicon-o-cube class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-base-content">{{ $prod['prd_name'] }}</p>
                                    <p class="text-xs text-base-content/60 font-mono mt-0.5">{{ $prod['prd_code'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-sm text-base-content/50 italic">
                        Tidak ada produk di dalam grup ini.
                    </div>
                @endif
            </div>

            <div class="p-4 border-t border-base-300 bg-base-200/30 shrink-0 text-right">
                <button @click="open = false" type="button" class="btn btn-primary btn-sm rounded-xl px-6 normal-case">Tutup</button>
            </div>
        </div>
    </div>
</div>
