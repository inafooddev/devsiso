<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Laporan Salesman Belum Terpetakan</x-slot>

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
                            <x-heroicon-s-user-group class="w-5 h-5 text-primary" />
                        </div>
                        <div>
                            <h2 class="text-lg md:text-xl font-bold text-base-content leading-none">Unmapped Salesmen</h2>
                            <p class="text-xs text-base-content/60 mt-1">Laporan salesman distributor yang belum memiliki pemetaan ke salesman principal</p>
                        </div>
                    </div>
                </div>

                {{-- Action Area --}}
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    {{-- Search --}}
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari kode/nama salesman..." />
                    
                    {{-- Filter Button --}}
                    <x-ui.action-button type="filter" wire:click="$set('isFilterModalOpen', true)" :active="$hasAppliedFilters" />
                    
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    
                    {{-- Export --}}
                    @canExport('mapping.unmapped-salesmans')
                    <x-ui.action-button type="export" wire:click="export" wire:loading.attr="disabled">
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs ml-1"></span>
                    </x-ui.action-button>
                    @endcanExport
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="flex-1 min-h-0 relative overflow-hidden bg-base-200/30 flex flex-col">
            @if (!$hasAppliedFilters)
                <div class="flex-1 flex flex-col items-center justify-center py-20 text-base-content/40">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-funnel class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Filter Belum Diterapkan</h3>
                    <p class="text-sm text-center max-w-xs">Silakan tentukan wilayah dan periode waktu untuk memuat laporan salesman yang belum terpetakan.</p>
                    <button wire:click="$set('isFilterModalOpen', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter Laporan
                    </button>
                </div>
            @else
                <div class="flex-1 overflow-auto h-full">
                    <table class="table table-sm table-pin-rows table-pin-cols w-full">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th class="w-12 text-center text-xs">No</th>
                                <th class="text-xs">Distributor</th>
                                <th class="text-xs">Kode Salesman (Dist)</th>
                                <th class="text-xs">Nama Salesman (Dist)</th>
                                <th class="w-24 text-center text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesmans as $index => $salesman)
                                <tr wire:key="salesman-{{ $salesman->distributor_code }}-{{ $salesman->salesman_code }}" class="hover:bg-base-200/50 transition-colors text-sm">
                                    <td class="text-center font-medium text-base-content/50">
                                        {{ $salesmans->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="font-bold text-base-content/90">{{ $salesman->distributor_name }}</div>
                                        <div class="text-xs text-base-content/50 font-mono mt-0.5">{{ $salesman->distributor_code }}</div>
                                    </td>
                                    <td>
                                        <span class="font-mono text-base-content/70">{{ $salesman->salesman_code }}</span>
                                    </td>
                                    <td>
                                        <span class="font-medium text-base-content/80">{{ $salesman->salesman_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex justify-center">
                                            @canEdit('mapping.unmapped-salesmans')
                                            <button wire:click="openMapModal('{{ $salesman->distributor_code }}', '{{ $salesman->salesman_code }}', '{{ addslashes($salesman->salesman_name) }}')"
                                                    class="btn btn-primary btn-xs rounded-lg normal-case gap-1 shadow-sm shadow-primary/20 transition-all duration-200">
                                                <x-heroicon-s-link class="w-3.5 h-3.5" />
                                                Petakan
                                            </button>
                                            @else
                                            <span class="text-xs text-base-content/50 italic">View Only</span>
                                            @endcanEdit
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-base-content/50">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-heroicon-o-inbox class="w-8 h-8 text-base-content/30" />
                                            <span>Semua salesman untuk periode ini sudah memiliki pemetaan.</span>
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
        @if ($hasAppliedFilters && $salesmans->hasPages())
        <div class="flex-none p-4 border-t border-base-200 bg-base-50">
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
                        <h3 class="font-bold text-lg leading-none">Filter Laporan</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Tentukan wilayah dan periode waktu</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="applyFilters">
                <div class="p-6 space-y-4">
                    {{-- Region & Area --}}
                    <div class="grid grid-cols-2 gap-4">
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
                    </div>

                    {{-- Distributor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
                        <select wire:model="distributorFilter"
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-40"
                                @if(!$areaFilter) disabled @endif>
                            <option value="">Semua Distributor</option>
                            @foreach($distributors as $distributor)
                                <option value="{{ $distributor->distributor_code }}">{{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Bulan</label>
                            <select wire:model="monthFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tahun</label>
                            <select wire:model="yearFilter" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50">
                                @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
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
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL PETAKAN SALESMAN ========== --}}
    <div x-data="{ open: @entangle('isMapModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-xl ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-link class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Petakan Salesman</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Tautkan salesman distributor ke master principal</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="saveMapping">
                <div class="p-6 space-y-6">
                    {{-- Info Salesman Distributor --}}
                    @if($currentSalesmanToMap)
                        <div class="p-5 bg-base-200 rounded-2xl border border-base-300 grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-base-content/40">Salesman Distributor</span>
                                <div class="font-bold text-base-content/80 leading-tight">{{ $currentSalesmanToMap['salesman_name_dist'] }}</div>
                                <div class="text-xs font-mono text-base-content/50">{{ $currentSalesmanToMap['salesman_code_dist'] }}</div>
                            </div>
                            <div class="space-y-1 border-l border-base-300 pl-4">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-base-content/40">Distributor</span>
                                <div class="font-bold text-base-content/80 leading-tight uppercase">{{ $currentSalesmanToMap['distributor_code'] }}</div>
                            </div>
                        </div>
                    @endif

                    {{-- Pemilihan Salesman Principal --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Salesman Principal <span class="text-error">*</span></label>
                        
                        <select wire:model="selectedPrincipalSalesman" 
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('selectedPrincipalSalesman') select-error @enderror">
                            <option value="">-- Pilih Salesman Principal --</option>
                            @foreach($principalSalesmans as $salesman)
                                <option value="{{ $salesman->salesman_code }}">
                                    {{ $salesman->salesman_code }} - {{ $salesman->salesman_name }}
                                </option>
                            @endforeach
                        </select>
                        
                        @error('selectedPrincipalSalesman') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="saveMapping">Simpan Pemetaan</span>
                        <span wire:loading wire:target="saveMapping" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="saveMapping" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
