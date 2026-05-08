<div>
    <x-slot name="title">Mapping Produk Distributor</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
        {{-- Notifikasi --}}
        <div class="mb-6 space-y-3">
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                     class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">{{ session('message') }}</div>
                    </div>
                </div>
            @endif
            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                </div>
            @endif
        </div>

        <x-card flush title="Mapping Produk" icon="arrows-right-left" subtitle="Kelola pemetaan produk distributor ke produk master / principal" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari kode/nama produk..."
                               class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Filter Button --}}
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        Filter
                        @if($hasAppliedFilters)
                            <span class="badge badge-xs badge-primary rounded-full">ON</span>
                        @endif
                    </button>
                    
                    {{-- Import Button --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="$set('isImportModalOpen', true)"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import
                    </button>
                    @endunless

                    {{-- Export --}}
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export
                    </button>
                    
                    <a href="{{ asset('templates/product_mapping_template.xlsx') }}" download 
                       class="btn btn-sm btn-outline btn-warning rounded-xl normal-case gap-2 transition-all duration-200">
                        <x-heroicon-s-document-arrow-down class="w-4 h-4" />
                        Template
                    </a>

                    {{-- Add Button --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openCreateModal"
                       class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah Manual
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            {{-- State: Filter Belum Diterapkan --}}
            @if (!$hasAppliedFilters)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-funnel class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Filter Belum Diterapkan</h3>
                    <p class="text-sm text-center max-w-xs">Klik tombol <strong>Filter</strong> untuk memilih region, area, atau distributor dan menampilkan data.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter
                    </button>
                </div>
            @else
                {{-- Tabel --}}
                <x-ui.table empty="Tidak ada pemetaan produk yang cocok dengan filter.">
                    <x-slot:head>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Distributor</th>
                            <th>Produk Distributor</th>
                            <th>Produk Principal (Master)</th>
                            <th>Tgl. Dibuat</th>
                            <th class="text-center w-24">Aksi</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($mappings as $index => $mapping)
                        <tr wire:key="mapping-{{ $mapping->id }}" class="group text-sm">
                            <td><span class="text-xs font-semibold text-base-content/40">{{ $mappings->firstItem() + $index }}</span></td>
                            <td>
                                <div>
                                    <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">
                                        {{ $mapping->masterDistributor->distributor_name ?? 'N/A' }}
                                    </span>
                                    <div class="text-xs text-base-content/40 font-mono mt-0.5">{{ $mapping->distributor_code }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-base-content/80">{{ $mapping->product_name_dist }}</span>
                                    <span class="text-xs text-base-content/40 font-mono">{{ $mapping->product_code_dist }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-base-content/80">{{ $mapping->product_name_prc ?? '-' }}</span>
                                    <span class="text-xs text-base-content/40 font-mono">{{ $mapping->product_code_prc }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 text-base-content/50 text-xs">
                                    <x-heroicon-s-calendar class="w-3.5 h-3.5 shrink-0" />
                                    <span>{{ $mapping->created_at->format('d M Y') }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    @unless(auth()->user()->hasRole('guest'))
                                    <button wire:click="openEditModal('{{ $mapping->id }}')"
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="confirmDelete('{{ $mapping->id }}')"
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>

                @if($mappings->hasPages())
                    <div class="mt-4 px-6">{{ $mappings->links() }}</div>
                @endif
            @endif
        </x-card>
    </div>

    {{-- ========== MODAL FILTER ========== --}}
    <div x-data="{ open: @entangle('isFilterModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Pemetaan</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Pilih wilayah untuk menampilkan data</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters">
                <div class="p-6 space-y-4">
                    {{-- Region --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                        <select wire:model.live="regionFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <option value="">Semua Region</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Area --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                        <select wire:model.live="areaFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$regionFilter) disabled @endif>
                            <option value="">Semua Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Distributor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
                        <select wire:model="distributorFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$areaFilter) disabled @endif>
                            <option value="">Semua Distributor</option>
                            @foreach($distributors as $distributor)
                                <option value="{{ $distributor->distributor_code }}"
                                        class="{{ $distributor->is_active ? '' : 'opacity-50' }}">
                                    {{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" wire:click="resetFilters" @click="open = false"
                            class="btn btn-ghost rounded-xl normal-case text-error hover:bg-error/10 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" /> Reset
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                            <x-heroicon-s-funnel class="w-4 h-4" /> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL FORM MAPPING (CREATE & EDIT) ========== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Pemetaan' : 'Tambah Pemetaan' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data pemetaan' : 'Isi detail pemetaan produk baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    @if(!$isEditing)
                        {{-- Region & Area (Hanya saat Create) --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region <span class="text-error">*</span></label>
                                <select wire:model.live="formRegionFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    <option value="">-- Pilih Region --</option>
                                    @foreach($formRegions as $region)
                                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area <span class="text-error">*</span></label>
                                <select wire:model.live="formAreaFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40" @if(!$formRegionFilter) disabled @endif>
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($formAreas as $area)
                                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Distributor --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor <span class="text-error">*</span></label>
                            <select wire:model.live="distributor_code" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40 @error('distributor_code') select-error @enderror" @if(!$formAreaFilter) disabled @endif>
                                <option value="">-- Pilih Distributor --</option>
                                @foreach($formDistributors as $distributor)
                                    <option value="{{ $distributor->distributor_code }}" class="{{ $distributor->is_active ? '' : 'opacity-50 text-error' }}">
                                        {{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('distributor_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                    @else
                        {{-- Distributor Code (read-only saat Edit) --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
                            <input type="text" value="{{ $distributor_code }}" readonly
                                   class="input input-bordered w-full bg-base-300/50 border-base-300 rounded-2xl font-mono text-base-content/60 cursor-not-allowed focus:ring-0">
                        </div>
                    @endif

                    <div class="divider text-[10px] font-bold uppercase tracking-widest text-base-content/20 uppercase">Detail Pemetaan</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Produk Distributor --}}
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Produk (Distributor)</label>
                                <input type="text" wire:model="product_code_dist" placeholder="Contoh: P001"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl font-mono focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('product_code_dist') input-error @enderror">
                                @error('product_code_dist') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Produk (Distributor)</label>
                                <input type="text" wire:model="product_name_dist" placeholder="Contoh: Sabun Mandi"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('product_name_dist') input-error @enderror">
                                @error('product_name_dist') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Produk Principal (Searchable) --}}
                        <div class="space-y-1.5" x-data="{ searchOpen: false }" @click.away="searchOpen = false">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Produk Master (Principal)</label>
                            
                            <div class="relative">
                                {{-- Input Pencarian --}}
                                <input type="text" 
                                    wire:model.live.debounce.300ms="productSearch"
                                    @focus="searchOpen = true"
                                    placeholder="Cari kode/nama produk..."
                                    class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300"
                                    x-show="!$wire.product_code_prc">

                                {{-- Selected Item --}}
                                <div x-show="$wire.product_code_prc" class="flex items-center justify-between p-2.5 bg-primary/10 border border-primary/20 rounded-2xl text-primary font-medium text-sm">
                                    <div class="flex flex-col">
                                        <span class="leading-tight">{{ $selectedProductName }}</span>
                                        <span class="text-[10px] font-mono opacity-60">{{ $product_code_prc }}</span>
                                    </div>
                                    <button type="button" wire:click="selectProduct(null)" class="btn btn-ghost btn-xs btn-circle text-primary hover:bg-primary/20">
                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                                
                                {{-- Dropdown Hasil --}}
                                <div x-show="searchOpen && $wire.productSearch.length >= 2 && !$wire.product_code_prc" x-transition 
                                     class="absolute z-50 w-full mt-2 bg-base-100 border border-base-300 shadow-xl rounded-2xl max-h-60 overflow-y-auto ring-1 ring-base-content/5 py-1">
                                    @forelse($principalProducts as $product)
                                        <div wire:click="selectProduct('{{ $product->product_id }}', '{{ addslashes($product->product_name) }}')" 
                                             @click="searchOpen = false"
                                             class="cursor-pointer px-4 py-2 hover:bg-base-200 text-sm flex flex-col transition-colors">
                                            <span class="font-medium text-base-content">{{ $product->product_name }}</span>
                                            <span class="text-xs font-mono text-base-content/50">{{ $product->product_id }}</span>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-xs text-base-content/50 italic">Tidak ada produk ditemukan.</div>
                                    @endforelse
                                </div>
                            </div>
                            @error('product_code_prc') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Pemetaan' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL IMPORT ========== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md ring-1 ring-base-content/5 text-base-content">
             
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Impor Pemetaan</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Unggah file Excel untuk pemetaan masal</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih File Excel</label>
                        <input type="file" wire:model="file" accept=".xls,.xlsx"
                               class="file-input file-input-bordered file-input-primary w-full bg-base-200 border-base-300 rounded-2xl focus:outline-none transition-all duration-300" />
                        @error('file') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                    </div>
                    <div class="p-4 bg-info/10 rounded-2xl border border-info/20 text-info flex items-start gap-3">
                        <x-heroicon-s-information-circle class="w-5 h-5 shrink-0 mt-0.5" />
                        <div class="text-xs leading-relaxed font-medium">
                            Pastikan format file sesuai dengan template yang telah disediakan untuk menghindari kegagalan impor.
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="import">Unggah & Proses</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL KONFIRMASI HAPUS ========== --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm ring-1 ring-base-content/5 text-base-content">
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold mb-2 leading-none">Hapus Pemetaan?</h3>
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Data pemetaan ini akan dihapus secara <span class="text-error font-bold italic">permanen</span> dan tidak dapat dipulihkan.</p>
            </div>
            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>
</div>
