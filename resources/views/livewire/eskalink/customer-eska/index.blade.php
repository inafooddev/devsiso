<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Data Customer Eska</x-slot>

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
                            <x-heroicon-s-identification class="w-5 h-5 text-primary" />
                        </div>
                        <div>
                            <h2 class="text-lg md:text-xl font-bold text-base-content leading-none">Master Customer Eska</h2>
                            <p class="text-xs text-base-content/60 mt-1">Data master customer yang terintegrasi melalui Eskalink</p>
                        </div>
                    </div>
                </div>

                {{-- Action Area --}}
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    {{-- Search --}}
                    @if ($isFiltered)
                        <x-ui.search-input wire:model.live.debounce.500ms="search" placeholder="Cari nama/no customer..." />
                    @endif
                    
                    {{-- Filter Button --}}
                    <x-ui.action-button type="filter" wire:click="$set('isFilterModalOpen', true)" :active="$isFiltered" />
                    
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    
                    {{-- Export --}}
                    @if ($isFiltered)
                    @canExport('customer-eska.index')
                    <x-ui.action-button type="export" wire:click="export" wire:loading.attr="disabled">
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs ml-1"></span>
                    </x-ui.action-button>
                    @endcanExport
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
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Filter Belum Diterapkan</h3>
                    <p class="text-sm text-center max-w-xs">Silakan tentukan Region, Area, dan Distributor untuk memuat data customer.</p>
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
                                <th class="text-xs">Kode Cabang</th>
                                <th class="text-xs">Distributor</th>
                                <th class="w-32 text-xs">Cust No</th>
                                <th class="text-xs">Nama Customer</th>
                                <th class="text-xs">Alamat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $index => $row)
                                <tr class="hover:bg-base-200/50 transition-colors text-sm">
                                    <td class="text-center font-medium text-base-content/50">
                                        {{ $customers->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="font-bold text-base-content/90">{{ $row->region_name }}</div>
                                        <div class="text-[11px] text-base-content/50 font-semibold uppercase tracking-wider mt-0.5">{{ $row->area_name }}</div>
                                    </td>
                                    <td>
                                        <span class="font-mono text-base-content/70">{{ $row->kodecabang }}</span>
                                    </td>
                                    <td>
                                        <span class="font-medium text-base-content/80">{{ $row->distributor_name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm badge-outline border-primary/30 text-primary font-mono rounded-lg">
                                            {{ $row->custno }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-bold text-base-content/80">{{ $row->custname }}</span>
                                    </td>
                                    <td>
                                        <div class="max-w-xs truncate text-xs text-base-content/50" title="{{ $row->custadd1 }}">
                                            {{ $row->custadd1 ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-base-content/50">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-inbox class="w-8 h-8 text-base-content/30" />
                                            <span>Tidak ada data customer yang ditemukan untuk distributor ini.</span>
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Data Customer</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Tentukan wilayah distributor</p>
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
                            <option value="">-- Pilih Region --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                        @error('regionFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Area --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                        <select wire:model.live="areaFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$regionFilter) disabled @endif>
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                            @endforeach
                        </select>
                        @error('areaFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Distributor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
                        <select wire:model.live="distributorFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$areaFilter) disabled @endif>
                            <option value="">-- Pilih Distributor --</option>
                            @foreach($distributors as $distributor)
                                <option value="{{ $distributor->distributor_code }}">{{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}</option>
                            @endforeach
                        </select>
                        @error('distributorFilter') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
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
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Tampilkan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
