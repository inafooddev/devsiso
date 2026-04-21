<div>
    <x-slot name="title">Data Product Sub-Brands</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
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

        <x-card flush title="Product Sub-Brands" icon="puzzle-piece" subtitle="Kelola varian sub-merk produk untuk detail item penjualan" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative group mr-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Sub-Brand..." 
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Tombol Tambah --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah Sub-Brand
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            {{-- Tabel Data --}}
            <x-ui.table loading="{{ false }}" empty="Tidak ada data product sub-brand ditemukan.">
                <x-slot:head>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Kode Sub-Brand</th>
                        <th>Nama Product Sub-Brand</th>
                        <th>Dibuat Pada</th>
                        <th>Update Terakhir</th>
                        <th class="text-center w-32">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($subBrands as $index => $subBrand)
                    <tr wire:key="sub-brand-{{ $subBrand->sub_brand_id }}" class="group text-sm">
                        <td>
                            <span class="text-xs font-semibold text-base-content/40">{{ $subBrands->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-outline border-base-300 text-primary font-mono px-2 py-3 rounded-lg">{{ $subBrand->sub_brand_id }}</span>
                        </td>
                        <td>
                            <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">{{ $subBrand->sub_brand_name }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2 text-base-content/50">
                                <x-heroicon-s-calendar class="w-3.5 h-3.5" />
                                <span>{{ $subBrand->created_at->format('d M Y') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2 text-base-content/50">
                                <x-heroicon-s-clock class="w-3.5 h-3.5" />
                                <span>{{ $subBrand->updated_at->diffForHumans() }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                @unless(auth()->user()->hasRole('guest'))
                                <button wire:click="openEditModal('{{ $subBrand->sub_brand_id }}')" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete('{{ $subBrand->sub_brand_id }}')" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if($subBrands->hasPages())
                <div class="mt-4 px-6">
                    {{ $subBrands->links() }}
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden ring-1 ring-base-content/5 flex flex-col text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Sub-Brand' : 'Tambah Sub-Brand Baru' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui informasi varian sub-merk' : 'Daftarkan varian sub-merk baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-6 bg-base-100">
                    <div class="space-y-1.5">
                        <label for="sub_brand_id" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">ID Sub-Brand</label>
                        <input wire:model.blur="sub_brand_id" type="text" id="sub_brand_id" placeholder="Cth: SBR-001"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('sub_brand_id') input-error @enderror">
                        @error('sub_brand_id') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="sub_brand_name" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Sub-Brand</label>
                        <input wire:model.blur="sub_brand_name" type="text" id="sub_brand_name" placeholder="Cth: Varian Premium"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('sub_brand_name') input-error @enderror">
                        @error('sub_brand_name') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 shrink-0">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Tambah Sub-Brand' }}</span>
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
         class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5">
            
            <div class="p-8 text-center text-base-content">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold mb-2 leading-none text-base-content">Hapus Sub-Brand?</h3>
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Seluruh data yang terkait dengan sub-merk ini akan terdampak. Tindakan ini <span class="text-error font-bold italic">permanen</span>.</p>
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
