<div>
    <x-slot name="title">Mapping Salesman Distributor</x-slot>

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

        <x-card flush title="Mapping Salesman" icon="user-group" subtitle="Kelola pemetaan salesman distributor ke salesman principal" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                               placeholder="Cari kode/nama salesman..."
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
                    @canImport('salesman-mappings.index')
                    <button wire:click="$set('isImportModalOpen', true)"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import
                    </button>
                    @endcanImport

                    {{-- Export --}}
                    @canExport('salesman-mappings.index')
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export
                    </button>
                    @endcanExport
                    
                    <a href="{{ asset('templates/salesman_mapping_template.xlsx') }}" download 
                       class="btn btn-sm btn-outline btn-warning rounded-xl normal-case gap-2 transition-all duration-200">
                        <x-heroicon-s-document-arrow-down class="w-4 h-4" />
                        Template
                    </a>

                    {{-- Add Button --}}
                    @canEdit('salesman-mappings.index')
                    <button wire:click="openCreateModal"
                       class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-user-plus class="w-4 h-4" />
                        Tambah Manual
                    </button>
                    @endcanEdit
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
                <x-ui.table empty="Tidak ada pemetaan salesman yang cocok dengan filter.">
                    <x-slot:head>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Distributor</th>
                            <th>Salesman Distributor</th>
                            <th>Salesman Principal</th>
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
                                    <span class="font-bold text-base-content/80">{{ $mapping->salesman_name_dist }}</span>
                                    <span class="text-xs text-base-content/40 font-mono">{{ $mapping->salesman_code_dist }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-base-content/80">{{ $mapping->principalSalesman->salesman_name ?? '-' }}</span>
                                    <span class="text-xs text-base-content/40 font-mono">{{ $mapping->salesman_code_prc }}</span>
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
                                    @canEdit('salesman-mappings.index')
                                    <button wire:click="openEditModal('{{ $mapping->id }}')"
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button wire:click="confirmDelete('{{ $mapping->id }}')"
                                            class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                    @else
                                    <span class="text-xs text-base-content/50 italic">View Only</span>
                                    @endcanEdit
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
                            <x-heroicon-s-user-plus class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Pemetaan' : 'Tambah Pemetaan' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data pemetaan' : 'Isi detail pemetaan salesman baru' }}</p>
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
                        {{-- Salesman Distributor --}}
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Salesman (Distributor) <span class="text-error">*</span></label>
                                <input type="text" wire:model="salesman_code_dist" placeholder="Contoh: S001"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl font-mono focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('salesman_code_dist') input-error @enderror">
                                @error('salesman_code_dist') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Salesman (Distributor)</label>
                                <input type="text" wire:model="salesman_name_dist" placeholder="Contoh: John Doe"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('salesman_name_dist') input-error @enderror">
                                @error('salesman_name_dist') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Salesman Principal (Standard Select) --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Salesman Master (Principal) <span class="text-error">*</span></label>
                            
                            <select wire:model="salesman_code_prc" 
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40 @error('salesman_code_prc') select-error @enderror"
                                    @if(!$distributor_code) disabled @endif>
                                <option value="">-- Pilih Salesman Principal --</option>
                                @foreach($principalSalesmans as $salesman)
                                    <option value="{{ $salesman->salesman_code }}">
                                        {{ $salesman->salesman_code }} - {{ $salesman->salesman_name }}
                                    </option>
                                @endforeach
                            </select>
                            
                            @if(!$distributor_code)
                                <p class="text-[10px] text-base-content/40 ml-1 mt-1 italic">Pilih distributor terlebih dahulu untuk memuat daftar salesman.</p>
                            @endif
                            
                            @error('salesman_code_prc') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
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
