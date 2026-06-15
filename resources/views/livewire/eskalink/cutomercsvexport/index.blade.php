<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Data PDAMASTER (SAP Export)</x-slot>

    {{-- Notifikasi --}}
    @if (session()->has('message') || session()->has('error'))
    <div class="shrink-0 mb-2">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success text-sm py-2">
                <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
                <span>{{ session('message') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error text-sm py-2">
                <x-heroicon-s-x-circle class="w-5 h-5 shrink-0" />
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>
    @endif

    {{-- Card Wrapper --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Section --}}
        <div class="flex-none p-4 md:p-6 border-b border-base-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                {{-- Title Area --}}
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                            <x-heroicon-s-document-arrow-down class="w-5 h-5 text-primary" />
                        </div>
                        <div>
                            <h2 class="text-lg md:text-xl font-bold text-base-content leading-none">PDAMASTER SAP Export</h2>
                            <p class="text-xs text-base-content/60 mt-1">Export data master customer dalam format TXT untuk integrasi sistem SAP</p>
                        </div>
                    </div>
                </div>

                {{-- Action Area --}}
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    {{-- Search --}}
                    @if ($isFiltered)
                        <x-ui.search-input wire:model.live.debounce.500ms="search" placeholder="Cari kode/nama customer..." />
                    @endif
                    
                    {{-- Filter Button --}}
                    <x-ui.action-button type="filter" wire:click="$set('isFilterModalOpen', true)" :active="$isFiltered" />
                    
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    
                    {{-- Export --}}
                    @if ($isFiltered)
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-4 h-4" /></span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs ml-1"></span>
                        Export SAP (.txt)
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="flex-1 min-h-0 relative overflow-hidden bg-base-200/30 flex flex-col">
            @if (!$isFiltered)
                <div class="flex-1 flex flex-col items-center justify-center py-20 text-base-content/40">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-funnel class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Data Belum Dimuat</h3>
                    <p class="text-sm text-center max-w-xs">Tentukan periode dan wilayah distributor untuk melihat data master customer.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter Data
                    </button>
                </div>
            @else
                <div class="flex-1 overflow-auto h-full">
                    <table class="table table-sm table-pin-rows table-pin-cols w-full">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th class="w-12 text-center text-xs">No</th>
                                <th class="text-xs">Region / Area</th>
                                <th class="text-xs">Distributor</th>
                                <th class="w-32 text-xs">Cust No</th>
                                <th class="text-xs">Nama Customer</th>
                                <th class="text-xs">Kota</th>
                                <th class="text-xs">Details (SAP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $index => $row)
                                <tr class="hover:bg-base-200/50 transition-colors text-sm group">
                                    <td class="text-center font-medium text-base-content/50">
                                        {{ $customers->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div>
                                            <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">
                                                {{ $row->region_name }}
                                            </span>
                                            <div class="text-[11px] text-base-content/40 font-semibold uppercase tracking-wider mt-0.5">{{ $row->area_name }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-base-content/80">{{ $row->distributor_name }}</span>
                                            <span class="text-[10px] text-base-content/40 font-mono">ID: {{ $row->kodecabang }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm badge-outline border-primary/30 text-primary font-mono rounded-lg">
                                            {{ $row->custno }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-bold text-base-content/80">{{ $row->custname }}</span>
                                            <span class="text-[10px] text-base-content/40 max-w-xs truncate" title="{{ $row->custadd1 }}">{{ $row->custadd1 }}</span>
                                        </div>
                                    </td>
                                    <td><span class="font-medium text-base-content/60 italic text-xs">{{ $row->ccity }}</span></td>
                                    <td>
                                        <div class="flex flex-wrap gap-1">
                                            <span class="badge badge-xs bg-base-200 border-none text-base-content/50" title="Term">T: {{ $row->cterm }}</span>
                                            <span class="badge badge-xs bg-base-200 border-none text-base-content/50" title="Type">Y: {{ $row->typeout }}</span>
                                            <span class="badge badge-xs bg-base-200 border-none text-base-content/50" title="Group">G: {{ $row->grupout }}</span>
                                            <span class="badge badge-xs bg-base-200 border-none text-base-content/50" title="Price">H: {{ $row->gharga }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-base-content/50">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-inbox class="w-8 h-8 text-base-content/30" />
                                            <span>Tidak ada data customer yang ditemukan untuk kriteria filter ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Pagination Section --}}
        @if ($isFiltered && $customers->hasPages())
        <div class="flex-none p-4 border-t border-base-200 bg-base-50">
            {{ $customers->links() }}
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl ring-1 ring-base-content/5 text-base-content flex flex-col max-h-[90vh]">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter PDAMASTER Export</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Tentukan periode dan wilayah export</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters" class="flex flex-col min-h-0">
                <div class="p-6 space-y-6 overflow-visible min-h-0">
                    {{-- Periode --}}
                    <div class="space-y-1.5 shrink-0">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Periode Bulan <span class="text-error">*</span></label>
                        <input type="month" wire:model="monthFilter"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        @error('monthFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Multi-Select Dropdowns (Alpine.js Popovers) --}}
                    <div class="space-y-4 flex-1 min-h-0 pr-2">
                        {{-- Region --}}
                        <div class="space-y-1.5" x-data="{ openDrop: false }">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                            <div class="relative" @click.away="openDrop = false">
                                <button type="button" @click="openDrop = !openDrop"
                                        class="w-full bg-base-200 border border-base-300 rounded-2xl h-12 px-4 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 flex items-center justify-between text-left hover:bg-base-300">
                                    <span class="truncate text-sm font-medium {{ empty($regionFilter) ? 'text-base-content/50' : 'text-base-content' }}">
                                        {{ empty($regionFilter) ? '-- Pilih Region --' : count($regionFilter) . ' Region Dipilih' }}
                                    </span>
                                    <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/50" />
                                </button>

                                <div x-show="openDrop" x-cloak
                                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                                     class="absolute z-[60] w-full mt-2 bg-base-100 rounded-2xl shadow-xl border border-base-300 overflow-hidden ring-1 ring-base-content/5">
                                    <div class="p-2 border-b border-base-200 flex justify-between items-center bg-base-200/50">
                                        <span class="text-[10px] font-bold uppercase text-base-content/50 ml-1 tracking-wider">Opsi Region</span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" wire:click="clearAllRegions" class="text-[10px] font-bold text-error hover:underline px-2 py-1 rounded-lg hover:bg-error/10 transition-colors">Reset</button>
                                            <button type="button" wire:click="selectAllRegions" class="text-[10px] font-bold text-primary hover:underline px-2 py-1 rounded-lg hover:bg-primary/10 transition-colors">Pilih Semua</button>
                                        </div>
                                    </div>
                                    <div class="max-h-56 overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-base-300 flex flex-col gap-1">
                                        @foreach($regionsOption as $r)
                                        <label class="flex items-center gap-3 p-2 hover:bg-base-200 rounded-xl cursor-pointer transition-colors group">
                                            <input type="checkbox" wire:model.live="regionFilter" value="{{ $r->region_code }}" 
                                                   class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-transform group-hover:scale-110">
                                            <span class="text-sm font-medium text-base-content/70 group-hover:text-base-content transition-colors">{{ $r->region_name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('regionFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Area --}}
                        <div class="space-y-1.5" x-data="{ openDrop: false }">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1 {{ empty($regionFilter) ? 'opacity-40' : '' }}">Area</label>
                            <div class="relative" @click.away="openDrop = false">
                                <button type="button" @click="openDrop = !openDrop"
                                        {{ empty($regionFilter) ? 'disabled' : '' }}
                                        class="w-full bg-base-200 border border-base-300 rounded-2xl h-12 px-4 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 flex items-center justify-between text-left disabled:opacity-50 hover:bg-base-300 disabled:hover:bg-base-200">
                                    <span class="truncate text-sm font-medium {{ empty($areaFilter) ? 'text-base-content/50' : 'text-base-content' }}">
                                        {{ empty($areaFilter) ? '-- Pilih Area --' : count($areaFilter) . ' Area Dipilih' }}
                                    </span>
                                    <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/50" />
                                </button>

                                <div x-show="openDrop" x-cloak
                                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                                     class="absolute z-[60] w-full mt-2 bg-base-100 rounded-2xl shadow-xl border border-base-300 overflow-hidden ring-1 ring-base-content/5">
                                    <div class="p-2 border-b border-base-200 flex justify-between items-center bg-base-200/50">
                                        <span class="text-[10px] font-bold uppercase text-base-content/50 ml-1 tracking-wider">Opsi Area</span>
                                        @if(!empty($areasOption))
                                        <div class="flex items-center gap-1">
                                            <button type="button" wire:click="clearAllAreas" class="text-[10px] font-bold text-error hover:underline px-2 py-1 rounded-lg hover:bg-error/10 transition-colors">Reset</button>
                                            <button type="button" wire:click="selectAllAreas" class="text-[10px] font-bold text-primary hover:underline px-2 py-1 rounded-lg hover:bg-primary/10 transition-colors">Pilih Semua</button>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="max-h-56 overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-base-300 flex flex-col gap-1">
                                        @if(!empty($areasOption))
                                            @foreach($areasOption as $a)
                                            <label class="flex items-center gap-3 p-2 hover:bg-base-200 rounded-xl cursor-pointer transition-colors group">
                                                <input type="checkbox" wire:model.live="areaFilter" value="{{ $a->area_code }}" 
                                                       class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-transform group-hover:scale-110">
                                                <span class="text-sm font-medium text-base-content/70 group-hover:text-base-content transition-colors">{{ $a->area_name }}</span>
                                            </label>
                                            @endforeach
                                        @else
                                            <div class="py-6 px-4 text-center text-xs text-base-content/40 italic">
                                                Pilih region terlebih dahulu
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @error('areaFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Distributor --}}
                        <div class="space-y-1.5" x-data="{ openDrop: false }">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1 {{ empty($areaFilter) ? 'opacity-40' : '' }}">Distributor</label>
                            <div class="relative" @click.away="openDrop = false">
                                <button type="button" @click="openDrop = !openDrop"
                                        {{ empty($areaFilter) ? 'disabled' : '' }}
                                        class="w-full bg-base-200 border border-base-300 rounded-2xl h-12 px-4 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 flex items-center justify-between text-left disabled:opacity-50 hover:bg-base-300 disabled:hover:bg-base-200">
                                    <span class="truncate text-sm font-medium {{ empty($distributorFilter) ? 'text-base-content/50' : 'text-base-content' }}">
                                        {{ empty($distributorFilter) ? '-- Pilih Distributor --' : count($distributorFilter) . ' Distributor Dipilih' }}
                                    </span>
                                    <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/50" />
                                </button>

                                <div x-show="openDrop" x-cloak
                                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                                     class="absolute bottom-[calc(100%+0.5rem)] z-[60] w-full bg-base-100 rounded-2xl shadow-xl border border-base-300 overflow-hidden ring-1 ring-base-content/5">
                                    <div class="p-2 border-b border-base-200 flex justify-between items-center bg-base-200/50">
                                        <span class="text-[10px] font-bold uppercase text-base-content/50 ml-1 tracking-wider">Opsi Distributor</span>
                                        @if(!empty($distributorsOption))
                                        <div class="flex items-center gap-1">
                                            <button type="button" wire:click="clearAllDistributors" class="text-[10px] font-bold text-error hover:underline px-2 py-1 rounded-lg hover:bg-error/10 transition-colors">Reset</button>
                                            <button type="button" wire:click="selectAllDistributors" class="text-[10px] font-bold text-primary hover:underline px-2 py-1 rounded-lg hover:bg-primary/10 transition-colors">Pilih Semua</button>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="max-h-56 overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-base-300 flex flex-col gap-1">
                                        @if(!empty($distributorsOption))
                                            @foreach($distributorsOption as $d)
                                            <label class="flex items-center gap-3 p-2 hover:bg-base-200 rounded-xl cursor-pointer transition-colors group">
                                                <input type="checkbox" wire:model="distributorFilter" value="{{ $d->distributor_code }}" 
                                                       class="checkbox checkbox-primary checkbox-sm rounded-lg border-base-content/20 transition-transform group-hover:scale-110 shrink-0">
                                                <span class="text-sm font-medium text-base-content/70 group-hover:text-base-content transition-colors truncate">{{ $d->distributor_name }}</span>
                                            </label>
                                            @endforeach
                                        @else
                                            <div class="py-6 px-4 text-center text-xs text-base-content/40 italic">
                                                Pilih wilayah area untuk memuat distributor.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @error('distributorFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl shrink-0">
                    <button type="button" wire:click="resetFilters" @click="open = false"
                            class="btn btn-ghost rounded-xl normal-case text-error hover:bg-error/10 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" /> Reset
                    </button>
                    <div class="flex gap-2">
                        <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Tampilkan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
