<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
    <x-slot name="title">Data Salesman</x-slot>

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0 flex items-start">
            <x-heroicon-s-check-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                <div class="text-[10px]">{{ session('message') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-success/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0 flex items-start">
            <x-heroicon-s-x-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                <div class="text-[10px]">{{ session('error') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-error/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-[11px] md:text-[11px] font-bold">Data Salesman</h2>
                <p class="text-[10px] md:text-[10px] text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data salesman per distributor</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari kode/nama salesman..." />

                {{-- Filter Button --}}
                <button wire:click="$set('isFilterModalOpen', true)"
                        class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                    <x-heroicon-s-funnel class="w-4 h-4" />
                    Filter
                    @if($hasAppliedFilters)
                        <span class="badge badge-xs badge-primary rounded-full">ON</span>
                    @endif
                </button>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    @canEdit('salesmans.index')
                    <x-ui.action-button type="import" wire:click="openImportModal" />
                    <x-ui.action-button type="add" wire:click="openCreateModal" />
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    @endcanEdit
                    
                    @canExport('salesmans.index')
                    <x-ui.action-button type="export" wire:click="export" />
                    @endcanExport
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            @if (!$hasAppliedFilters)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40 h-full">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-funnel class="w-10 h-10" />
                    </div>
                    <h3 class="text-[11px] font-bold text-base-content/60 mb-1">Filter Belum Diterapkan</h3>
                    <p class="text-[11px] text-center max-w-xs">Klik tombol <strong>Filter</strong> untuk memilih region, area, atau distributor dan menampilkan data salesman.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter
                    </button>
                </div>
            @else
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-[10px] uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th class="w-16">No</th>
                            <th>Region</th>
                            <th>Area</th>
                            <th>Distributor</th>
                            <th>Salesman</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Foto KTP</th>
                            <th class="text-center">Foto NPWP</th>
                            <th class="text-center">Foto Bank</th>
                            <th class="text-center">Foto SKB</th>
                            <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px]">
                        @forelse ($salesmans as $index => $salesman)
                            <tr wire:key="salesman-{{ $salesman->salesman_code }}-{{ $salesman->distributor_code }}" class="hover:bg-base-200/50 transition-colors group">
                                <th>{{ $salesmans->firstItem() + $index }}</th>
                                <td>{{ optional(optional($salesman->masterDistributor)->area)->region->region_name ?? '-' }}</td>
                                <td>{{ optional($salesman->masterDistributor)->area->area_name ?? '-' }}</td>
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-base-content/90">{{ optional($salesman->masterDistributor)->distributor_name ?? '-' }}</span>
                                        <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $salesman->distributor_code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-base-content/90 group-hover:text-primary transition-colors">{{ $salesman->salesman_name }}</span>
                                        <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $salesman->salesman_code }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($salesman->is_principle)
                                        <span class="badge badge-sm badge-info/20 text-info border-info/30 px-3 rounded-full">Principal</span>
                                    @else
                                        <span class="badge badge-sm badge-warning/20 text-warning border-warning/30 px-3 rounded-full">Distributor</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($salesman->is_active)
                                        <span class="badge badge-sm badge-success/20 text-success border-success/30 px-3 rounded-full">Aktif</span>
                                    @else
                                        <span class="badge badge-sm badge-error/20 text-error border-error/30 px-3 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($salesman->foto_ktp) <a href="{{ Storage::url($salesman->foto_ktp) }}" target="_blank" class="text-primary hover:text-primary-focus" title="Lihat Foto KTP"><x-heroicon-s-photo class="w-5 h-5 mx-auto" /></a> @else - @endif
                                </td>
                                <td class="text-center">
                                    @if($salesman->foto_npwp) <a href="{{ Storage::url($salesman->foto_npwp) }}" target="_blank" class="text-primary hover:text-primary-focus" title="Lihat Foto NPWP"><x-heroicon-s-photo class="w-5 h-5 mx-auto" /></a> @else - @endif
                                </td>
                                <td class="text-center">
                                    @if($salesman->foto_bank) <a href="{{ Storage::url($salesman->foto_bank) }}" target="_blank" class="text-primary hover:text-primary-focus" title="Lihat Foto Bank"><x-heroicon-s-photo class="w-5 h-5 mx-auto" /></a> @else - @endif
                                </td>
                                <td class="text-center">
                                    @if($salesman->foto_skb) <a href="{{ Storage::url($salesman->foto_skb) }}" target="_blank" class="text-primary hover:text-primary-focus" title="Lihat Foto SKB"><x-heroicon-s-document-text class="w-5 h-5 mx-auto" /></a> @else - @endif
                                </td>
                                <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                    <div class="flex items-center justify-center gap-1">
                                        <x-ui.action-button type="view" wire:click="viewDetail('{{ $salesman->salesman_code }}', '{{ $salesman->distributor_code }}')" class="btn-square" title="Detail" />
                                        @canEdit('salesmans.index')
                                        <x-ui.action-button type="edit" wire:click="edit('{{ $salesman->distributor_code }}', '{{ $salesman->salesman_code }}')" class="btn-square" title="Edit" />
                                        <x-ui.action-button type="delete" wire:click="confirmDelete('{{ $salesman->salesman_code }}', '{{ $salesman->distributor_code }}')" class="btn-square" title="Hapus" />
                                        @endcanEdit
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">
                                    <div class="flex flex-col items-center justify-center py-12 text-base-content/40">
                                        <x-heroicon-o-inbox class="w-12 h-12 mb-3 opacity-20" />
                                        <p class="text-[11px] font-medium">Tidak ada salesman yang cocok dengan kriteria filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if($hasAppliedFilters && $salesmans->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $salesmans->links() }}
            </div>
        @endif
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
                        <h3 class="font-bold text-[11px] leading-none">Filter Salesman</h3>
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
                        <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
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
                        <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
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
                        <label class="text-[11px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
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

                    {{-- Tipe Salesman --}}
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Tipe Salesman</label>
                        <select wire:model="typeFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <option value="">Semua Tipe</option>
                            <option value="1">Principal</option>
                            <option value="0">Distributor</option>
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

    {{-- ========== MODAL FORM SALESMAN (CREATE & EDIT) ========== --}}
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-3xl ring-1 ring-base-content/5 text-base-content">

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
                        <h3 class="font-bold text-[11px] leading-none">{{ $isEditing ? 'Edit Salesman' : 'Tambah Salesman' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data salesman' : 'Isi detail salesman baru' }}</p>
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
                                <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Region <span class="text-error">*</span></label>
                                <select wire:model.live="formRegionFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    <option value="">-- Pilih Region --</option>
                                    @foreach($formRegions as $region)
                                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Area <span class="text-error">*</span></label>
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
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor <span class="text-error">*</span></label>
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

                        {{-- Manual Number --}}
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Nomor Urut Kode <span class="text-error">*</span></label>
                            <input type="text" wire:model.live="manual_number" placeholder="Contoh: 01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('manual_number') input-error @enderror">
                            @error('manual_number') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                            <p class="mt-1 text-[10px] text-base-content/40 italic ml-1">Masukkan angka atau akhiran unik untuk salesman.</p>
                        </div>
                    @else
                        {{-- Distributor Code (read-only saat Edit) --}}
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Distributor</label>
                            <input type="text" wire:model="distributor_code" readonly
                                   class="input input-bordered w-full bg-base-300/50 border-base-300 rounded-2xl font-mono text-base-content/60 cursor-not-allowed focus:ring-0">
                        </div>
                    @endif

                    {{-- Salesman Code --}}
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Salesman <span class="text-error">*</span></label>
                        <input type="text" wire:model="salesman_code" {{ !$isEditing ? 'readonly' : '' }}
                               class="input input-bordered w-full {{ !$isEditing ? 'bg-base-300/50 cursor-not-allowed text-primary font-bold' : 'bg-base-200' }} border-base-300 rounded-2xl font-mono focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('salesman_code') input-error @enderror">
                        @error('salesman_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                    </div>

                    {{-- Salesman Name --}}
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Salesman <span class="text-error">*</span></label>
                        <input type="text" wire:model="salesman_name"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('salesman_name') input-error @enderror">
                        @error('salesman_name') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Status</label>
                        <select wire:model="is_active"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <hr class="border-base-300 my-2">

                    {{-- Join Date & Is Principle --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Join Date</label>
                            <input type="date" wire:model="join_date"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('join_date') input-error @enderror">
                            @error('join_date') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Tipe Sales</label>
                            <select wire:model="is_principle"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                <option value="0">Distributor</option>
                                <option value="1">Principal</option>
                            </select>
                        </div>
                    </div>

                    {{-- Bank Info --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Bank</label>
                            <input type="text" wire:model="bank" list="indonesian-banks" placeholder="Cari / Ketik Nama Bank..."
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('bank') input-error @enderror">
                            <datalist id="indonesian-banks">
                                <option value="BCA (Bank Central Asia)"></option>
                                <option value="BNI (Bank Negara Indonesia)"></option>
                                <option value="BRI (Bank Rakyat Indonesia)"></option>
                                <option value="Bank Mandiri"></option>
                                <option value="BSI (Bank Syariah Indonesia)"></option>
                                <option value="BTN (Bank Tabungan Negara)"></option>
                                <option value="CIMB Niaga"></option>
                                <option value="Bank Permata"></option>
                                <option value="Bank Danamon"></option>
                                <option value="Bank Mega"></option>
                                <option value="Panin Bank"></option>
                                <option value="OCBC NISP"></option>
                                <option value="Maybank Indonesia"></option>
                                <option value="Bank BJB"></option>
                                <option value="Bank DKI"></option>
                                <option value="Bank Muamalat"></option>
                                <option value="Bank Sinarmas"></option>
                                <option value="Bank Bukopin"></option>
                                <option value="Bank Jago"></option>
                                <option value="Seabank"></option>
                                <option value="Blu by BCA Digital"></option>
                                <option value="Bank Neo Commerce"></option>
                                <option value="Allo Bank"></option>
                                <option value="Bank BTPN"></option>
                            </datalist>
                            @error('bank') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Atas Nama Rekening</label>
                            <input type="text" wire:model="bank_name"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('bank_name') input-error @enderror">
                            @error('bank_name') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">No Rekening</label>
                            <input type="text" wire:model="bank_no"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('bank_no') input-error @enderror">
                            @error('bank_no') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Upload Files --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto KTP</label>
                            <input type="file" wire:model="foto_ktp" accept=".jpg,.jpeg,.png,.pdf" id="foto_ktp_{{ $iteration }}"
                                   class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            @if($existing_foto_ktp) 
                            <div class="flex items-center gap-3 mt-1">
                                <a href="{{ Storage::url($existing_foto_ktp) }}" target="_blank" class="text-[11px] text-primary underline font-semibold">Lihat KTP Saat Ini</a>
                                <button type="button" wire:click="deleteExistingPhoto('foto_ktp')" onclick="confirm('Yakin ingin menghapus foto KTP ini?') || event.stopImmediatePropagation()" class="text-[11px] text-error hover:underline font-semibold">Hapus</button>
                            </div>
                            @endif
                            @error('foto_ktp') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto NPWP</label>
                            <input type="file" wire:model="foto_npwp" accept=".jpg,.jpeg,.png,.pdf" id="foto_npwp_{{ $iteration }}"
                                   class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            @if($existing_foto_npwp) 
                            <div class="flex items-center gap-3 mt-1">
                                <a href="{{ Storage::url($existing_foto_npwp) }}" target="_blank" class="text-[11px] text-primary underline font-semibold">Lihat NPWP Saat Ini</a>
                                <button type="button" wire:click="deleteExistingPhoto('foto_npwp')" onclick="confirm('Yakin ingin menghapus foto NPWP ini?') || event.stopImmediatePropagation()" class="text-[11px] text-error hover:underline font-semibold">Hapus</button>
                            </div>
                            @endif
                            @error('foto_npwp') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto Rekening Bank</label>
                            <input type="file" wire:model="foto_bank" accept=".jpg,.jpeg,.png,.pdf" id="foto_bank_{{ $iteration }}"
                                   class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            @if($existing_foto_bank) 
                            <div class="flex items-center gap-3 mt-1">
                                <a href="{{ Storage::url($existing_foto_bank) }}" target="_blank" class="text-[11px] text-primary underline font-semibold">Lihat Rekening Saat Ini</a>
                                <button type="button" wire:click="deleteExistingPhoto('foto_bank')" onclick="confirm('Yakin ingin menghapus foto Rekening Bank ini?') || event.stopImmediatePropagation()" class="text-[11px] text-error hover:underline font-semibold">Hapus</button>
                            </div>
                            @endif
                            @error('foto_bank') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">Foto SKB</label>
                            <input type="file" wire:model="foto_skb" accept=".jpg,.jpeg,.png,.pdf" id="foto_skb_{{ $iteration }}"
                                   class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            @if($existing_foto_skb) 
                            <div class="flex items-center gap-3 mt-1">
                                <a href="{{ Storage::url($existing_foto_skb) }}" target="_blank" class="text-[11px] text-primary underline font-semibold">Lihat SKB Saat Ini</a>
                                <button type="button" wire:click="deleteExistingPhoto('foto_skb')" onclick="confirm('Yakin ingin menghapus dokumen SKB ini?') || event.stopImmediatePropagation()" class="text-[11px] text-error hover:underline font-semibold">Hapus</button>
                            </div>
                            @endif
                            @error('foto_skb') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Salesman' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
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
                <h3 class="text-xl font-bold mb-2 leading-none">Hapus Salesman?</h3>
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Data salesman ini akan dihapus secara <span class="text-error font-bold italic">permanen</span> dan tidak dapat dipulihkan.</p>
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

    {{-- Modal Import --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-success/10 text-success">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-[11px] text-base-content">Import Salesman</h3>
                        <p class="text-[10px] text-base-content/50">Upload file Excel data master salesman</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 bg-base-100">
                    <div class="alert bg-info/10 text-info border-none rounded-xl text-[10px] mb-4">
                        <x-heroicon-s-information-circle class="w-5 h-5 shrink-0" />
                        <div>Pastikan file menggunakan format template standar. Anda bisa mendownload template di bawah ini.</div>
                    </div>

                    <div class="form-control w-full space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-base-content/50 ml-1">File Excel (.xlsx)</label>
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="file-input file-input-bordered file-input-primary w-full bg-base-200 rounded-2xl" required />
                        @error('importFile') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>
                    
                    <button type="button" wire:click="downloadTemplate" class="btn btn-sm btn-outline btn-info w-full mt-4 rounded-xl normal-case">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Download Template Import
                    </button>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="import">Proses Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL VIEW DETAIL ========== --}}
    <div x-data="{ open: @entangle('isViewModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[80] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" wire:click="closeViewModal"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl ring-1 ring-base-content/5 text-base-content flex flex-col max-h-[90vh]">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                        <x-heroicon-s-eye class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-[11px] leading-none">Detail Salesman</h3>
                        <p class="text-[10px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Informasi lengkap salesman</p>
                    </div>
                </div>
                <button wire:click="closeViewModal" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 bg-base-100/50">
                @if($viewingSalesman)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Info Utama --}}
                        <div class="space-y-4">
                            <h4 class="font-bold text-base-content/70 border-b border-base-200 pb-2">Informasi Umum</h4>
                            
                            <div class="grid grid-cols-3 gap-2 text-[11px]">
                                <div class="text-base-content/50">Region</div>
                                <div class="col-span-2 font-semibold">{{ optional(optional($viewingSalesman->masterDistributor)->area)->region->region_name ?? '-' }}</div>
                                
                                <div class="text-base-content/50">Area</div>
                                <div class="col-span-2 font-semibold">{{ optional($viewingSalesman->masterDistributor)->area->area_name ?? '-' }}</div>
                                
                                <div class="text-base-content/50">Distributor</div>
                                <div class="col-span-2 font-semibold">
                                    {{ $viewingSalesman->distributor_code }} - {{ optional($viewingSalesman->masterDistributor)->distributor_name ?? '-' }}
                                </div>

                                <div class="col-span-3 my-1 border-t border-base-200 border-dashed"></div>

                                <div class="text-base-content/50">Kode Salesman</div>
                                <div class="col-span-2 font-mono font-bold text-primary">{{ $viewingSalesman->salesman_code }}</div>
                                
                                <div class="text-base-content/50">Nama</div>
                                <div class="col-span-2 font-semibold">{{ $viewingSalesman->salesman_name }}</div>
                                
                                <div class="text-base-content/50">Tipe</div>
                                <div class="col-span-2">
                                    @if ($viewingSalesman->is_principle)
                                        <span class="badge badge-sm badge-info/20 text-info border-info/30">Principal</span>
                                    @else
                                        <span class="badge badge-sm badge-warning/20 text-warning border-warning/30">Distributor</span>
                                    @endif
                                </div>
                                
                                <div class="text-base-content/50">Status</div>
                                <div class="col-span-2">
                                    @if ($viewingSalesman->is_active)
                                        <span class="badge badge-sm badge-success/20 text-success border-success/30">Aktif</span>
                                    @else
                                        <span class="badge badge-sm badge-error/20 text-error border-error/30">Nonaktif</span>
                                    @endif
                                </div>
                                
                                <div class="text-base-content/50">Join Date</div>
                                <div class="col-span-2 font-semibold">{{ $viewingSalesman->join_date ? \Carbon\Carbon::parse($viewingSalesman->join_date)->translatedFormat('d F Y') : '-' }}</div>
                            </div>
                        </div>

                        {{-- Info Bank --}}
                        <div class="space-y-4">
                            <h4 class="font-bold text-base-content/70 border-b border-base-200 pb-2">Informasi Bank</h4>
                            
                            <div class="grid grid-cols-3 gap-2 text-[11px]">
                                <div class="text-base-content/50">Nama Bank</div>
                                <div class="col-span-2 font-semibold">{{ $viewingSalesman->bank ?? '-' }}</div>
                                
                                <div class="text-base-content/50">A.N. Rekening</div>
                                <div class="col-span-2 font-semibold">{{ $viewingSalesman->bank_name ?? '-' }}</div>
                                
                                <div class="text-base-content/50">No Rekening</div>
                                <div class="col-span-2 font-mono font-semibold">{{ $viewingSalesman->bank_no ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Dokumen --}}
                    <div class="mt-8 space-y-4">
                        <h4 class="font-bold text-base-content/70 border-b border-base-200 pb-2">Dokumen Pendukung</h4>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach([
                                'Foto KTP' => $viewingSalesman->foto_ktp,
                                'Foto NPWP' => $viewingSalesman->foto_npwp,
                                'Buku Rekening' => $viewingSalesman->foto_bank,
                                'SKB' => $viewingSalesman->foto_skb
                            ] as $label => $file)
                                <div class="bg-base-200 rounded-xl p-3 flex flex-col items-center justify-center text-center gap-2 border border-base-300">
                                    <span class="text-[10px] font-bold text-base-content/60">{{ $label }}</span>
                                    @if($file)
                                        @php
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                        @endphp
                                        
                                        @if($isImage)
                                            <a href="{{ Storage::url($file) }}" target="_blank" class="block w-full h-24 rounded-lg overflow-hidden border border-base-300 hover:ring-2 hover:ring-primary transition-all group relative bg-base-300">
                                                <img src="{{ Storage::url($file) }}" alt="{{ $label }}" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <x-heroicon-s-arrows-pointing-out class="w-6 h-6 text-white" />
                                                </div>
                                            </a>
                                        @else
                                            <a href="{{ Storage::url($file) }}" target="_blank" class="flex flex-col items-center justify-center w-full h-24 rounded-lg border border-base-300 bg-base-100 hover:border-primary hover:text-primary transition-all group">
                                                <x-heroicon-s-document-text class="w-8 h-8 text-base-content/30 group-hover:text-primary mb-1" />
                                                <span class="text-[10px] font-semibold text-primary">Lihat Dokumen</span>
                                            </a>
                                        @endif
                                    @else
                                        <div class="flex flex-col items-center justify-center w-full h-24 rounded-lg border border-dashed border-base-300 bg-base-100/50">
                                            <x-heroicon-s-x-circle class="w-6 h-6 text-base-content/20 mb-1" />
                                            <span class="text-[10px] text-base-content/40 italic">Belum ada</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="px-6 py-4 border-t border-base-300 bg-base-200/30 rounded-b-3xl shrink-0 flex justify-end">
                <button wire:click="closeViewModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Tutup</button>
            </div>
        </div>
    </div>
</div>
