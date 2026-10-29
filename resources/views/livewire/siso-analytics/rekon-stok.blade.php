<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-2.5 md:gap-3 w-full h-full" x-data="{ showDiagnosticInfo: false }">
    <x-slot name="title">SISO Analytics — Rekon Stok & Diagnosa Aliran</x-slot>

    {{-- Loading Overlay --}}
    <div wire:loading wire:target="selectedYear, selectedMonth, viewMode, setViewMode, selectedRegion, selectedArea, selectedDistributor, selectedStatusFilter, setStatusFilter, resetFilters, sortBy, processUpload, recalculateCurrentPeriod, export, downloadTemplate" 
         class="fixed inset-0 z-50 bg-base-300/30 flex items-center justify-center backdrop-blur-[2px] transition-opacity" style="z-index: 9999;">
        <div class="bg-base-100 px-6 py-5 rounded-2xl shadow-2xl flex items-center gap-4 border border-base-200">
            <span class="loading loading-spinner loading-md text-primary"></span>
            <div>
                <h3 class="text-sm font-bold text-base-content">Memperbarui Diagnosa Stok & Aliran</h3>
                <p class="text-xs text-base-content/60">Menyinkronkan stok awal, transaksi berjalan, dan metrik DOI...</p>
            </div>
        </div>
    </div>

    {{-- Global Tab Navigation (Fixed Top) --}}
    @include('livewire.siso-analytics.partials._tabs')

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="alert alert-success shadow-sm rounded-xl py-1.5 px-4 mb-2.5 text-xs font-medium flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn btn-ghost btn-xs" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif
    @if (session()->has('warning'))
        <div class="alert alert-warning shadow-sm rounded-xl py-1.5 px-4 mb-2.5 text-xs font-medium flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <x-heroicon-s-exclamation-triangle class="w-4 h-4 shrink-0" />
                <span>{{ session('warning') }}</span>
            </div>
            <button type="button" class="btn btn-ghost btn-xs" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-error shadow-sm rounded-xl py-1.5 px-4 mb-2.5 text-xs font-medium flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <x-heroicon-s-x-circle class="w-4 h-4 shrink-0" />
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn btn-ghost btn-xs" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    {{-- SPA FIXED FLEX CONTAINER (Hanya Card Tabel yang Scroll) --}}
    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 overflow-hidden">

        {{-- 1. FILTER TOOLBAR (Shrink-0, Tanpa Filter Brand) --}}
        <x-ui.filter-toolbar class="shrink-0 z-30 rounded-2xl shadow-sm border border-base-300 bg-base-200/60 backdrop-blur-md">

            {{-- Kapsul 1: Periode Bulan & Tahun --}}
            <x-ui.filter-breadcrumb>
                <div class="relative flex items-center pl-2 pr-1.5 w-32 sm:w-36 shrink-0 min-w-0" title="Bulan Analisa">
                    <x-heroicon-m-calendar class="w-3.5 h-3.5 text-primary/80 shrink-0 mr-1 pointer-events-none" />
                    <select wire:model.live="selectedMonth" 
                            class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs font-semibold text-primary w-full min-w-0 truncate pl-0 pr-4 py-1 cursor-pointer">
                        @foreach($months as $mNum => $mName)
                            <option value="{{ $mNum }}" class="bg-base-100 text-base-content">{{ $mName }}</option>
                        @endforeach
                    </select>
                    <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2" />
                </div>

                <div class="relative flex items-center pl-2 pr-1.5 w-24 sm:w-28 shrink-0 min-w-0" title="Tahun Analisa">
                    <x-heroicon-m-calendar-days class="w-3.5 h-3.5 text-base-content/50 shrink-0 mr-1 pointer-events-none" />
                    <select wire:model.live="selectedYear" 
                            class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs font-mono font-bold text-base-content w-full min-w-0 truncate pl-0 pr-4 py-1 cursor-pointer">
                        @foreach($years as $y)
                            <option value="{{ $y }}" class="bg-base-100 text-base-content">{{ $y }}</option>
                        @endforeach
                    </select>
                    <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2" />
                </div>
            </x-ui.filter-breadcrumb>

            {{-- Kapsul 2: Hierarki Wilayah (Region > Area > Distributor) --}}
            <x-ui.filter-breadcrumb>
                <x-ui.filter-select 
                    wire:model.live="selectedRegion" 
                    :options="$regionOptions" 
                    placeholder="Semua Region" 
                    icon="globe" 
                    width="w-28 sm:w-36"
                    :disabled="$isRegionLocked || count($regionOptions) <= 1" />

                <x-ui.filter-select 
                    wire:model.live="selectedArea" 
                    :options="$areaOptions" 
                    placeholder="Semua Area" 
                    icon="map-pin" 
                    width="w-28 sm:w-36" 
                    :disabled="$isAreaLocked || empty($selectedRegion) || count($areaOptions) <= 1" />

                <x-ui.filter-searchable 
                    wire:key="rekon-dist-capsule-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}"
                    wire:model.live="selectedDistributor" 
                    :options="$distributorOptions" 
                    valueField="distributor_code" 
                    labelField="distributor_name" 
                    placeholder="Semua Distributor" 
                    :allowAll="true"
                    searchPlaceholder="Cari nama distributor / kode..." 
                    icon="building" 
                    width="w-40 sm:w-56" />
            </x-ui.filter-breadcrumb>

            {{-- Actions Slot: Tombol Aksi Utama & Reset --}}
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <button type="button" 
                            wire:click="openUploadModal" 
                            class="btn btn-sm h-8 min-h-0 px-3 btn-primary rounded-xl text-xs font-semibold gap-1.5 shadow-xs">
                        <x-heroicon-s-arrow-up-tray class="w-3.5 h-3.5" />
                        <span>Upload Closing</span>
                    </button>

                    <button type="button" 
                            wire:click="recalculateCurrentPeriod" 
                            wire:confirm="Sinkronkan ulang stok awal, transaksi berjalan, dan diagnosa DOI untuk periode ini?"
                            class="btn btn-sm h-8 min-h-0 px-2.5 btn-ghost bg-base-100 border border-base-300 hover:bg-base-200 rounded-xl text-xs font-semibold gap-1"
                            title="Sinkronkan ulang data periode ini">
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-base-content/70" />
                        <span class="hidden xl:inline">Sinkron</span>
                    </button>

                    @php
                        $hasActiveFilters = (!$isRegionLocked && !empty($selectedRegion))
                            || (!$isAreaLocked && !empty($selectedArea))
                            || !empty($selectedDistributor)
                            || $selectedStatusFilter !== 'ALL'
                            || $selectedMovementFilter !== 'ALL'
                            || !empty($search);
                    @endphp

                    @if($hasActiveFilters)
                        <button type="button" 
                                wire:click="resetFilters" 
                                class="btn btn-sm h-8 min-h-0 px-2.5 btn-ghost border border-error/30 text-error hover:bg-error/10 hover:border-error rounded-xl text-xs font-semibold gap-1 transition-colors"
                                title="Reset semua filter">
                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                            <span>Reset</span>
                        </button>
                    @endif
                </div>
            </x-slot>

        </x-ui.filter-toolbar>

        {{-- 2. 4 EXECUTIVE DIAGNOSTIC KPI CARDS (Shrink-0) --}}
        @php
            $entityLabel = $viewMode === 'product' ? 'SKU' : 'Dist';
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 shrink-0">

            {{-- Card 1: Penjualan Aneh (Phantom Sales) --}}
            <div wire:click="setStatusFilter('{{ $selectedStatusFilter === 'PHANTOM_SALES' ? 'ALL' : 'PHANTOM_SALES' }}')"
                 class="cursor-pointer bg-base-100 rounded-2xl p-4 border transition-all flex flex-col justify-between relative overflow-hidden {{ $selectedStatusFilter === 'PHANTOM_SALES' ? 'ring-2 ring-rose-500 border-rose-500 shadow-md' : 'border-base-200 shadow-sm hover:shadow-md' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Penjualan Aneh</p>
                        <h3 class="text-lg lg:text-xl font-bold tracking-tight text-rose-600 dark:text-rose-400 mt-1 tabular-nums">
                            {{ number_format($metrics['count_phantom'], 0, ',', '.') }} <span class="text-xs font-semibold text-base-content/60">{{ $entityLabel }}</span>
                        </h3>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-base-200/70 flex items-center justify-between gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-md font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400">
                        Sisa Stok &lt; 0
                    </span>
                    <span class="text-base-content/50 font-medium">Stok Minus</span>
                </div>
            </div>

            {{-- Card 2: Over Stock --}}
            <div wire:click="setStatusFilter('{{ $selectedStatusFilter === 'OVER_STOCK' ? 'ALL' : 'OVER_STOCK' }}')"
                 class="cursor-pointer bg-base-100 rounded-2xl p-4 border transition-all flex flex-col justify-between relative overflow-hidden {{ $selectedStatusFilter === 'OVER_STOCK' ? 'ring-2 ring-amber-500 border-amber-500 shadow-md' : 'border-base-200 shadow-sm hover:shadow-md' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Over Stock</p>
                        <h3 class="text-lg lg:text-xl font-bold tracking-tight text-amber-600 dark:text-amber-400 mt-1 tabular-nums">
                            {{ number_format($metrics['count_overstock'], 0, ',', '.') }} <span class="text-xs font-semibold text-base-content/60">{{ $entityLabel }}</span>
                        </h3>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <x-heroicon-o-archive-box-x-mark class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-base-200/70 flex items-center justify-between gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-md font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        DOI &gt; 50 Hari / Macet
                    </span>
                    <span class="font-semibold text-base-content/70 tabular-nums" title="Estimasi nilai stok mengendap">
                        Rp {{ number_format($metrics['value_stuck_overstock'], 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Card 3: Under Stock --}}
            <div wire:click="setStatusFilter('{{ $selectedStatusFilter === 'UNDER_STOCK' ? 'ALL' : 'UNDER_STOCK' }}')"
                 class="cursor-pointer bg-base-100 rounded-2xl p-4 border transition-all flex flex-col justify-between relative overflow-hidden {{ $selectedStatusFilter === 'UNDER_STOCK' ? 'ring-2 ring-sky-500 border-sky-500 shadow-md' : 'border-base-200 shadow-sm hover:shadow-md' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Under Stock</p>
                        <h3 class="text-lg lg:text-xl font-bold tracking-tight text-sky-600 dark:text-sky-400 mt-1 tabular-nums">
                            {{ number_format($metrics['count_understock'], 0, ',', '.') }} <span class="text-xs font-semibold text-base-content/60">{{ $entityLabel }}</span>
                        </h3>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                        <x-heroicon-o-bolt class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-base-200/70 flex items-center justify-between gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-md font-semibold bg-sky-500/10 text-sky-600 dark:text-sky-400">
                        % Stock &lt; 80%
                    </span>
                    <span class="text-base-content/50 font-medium">Rawan Habis</span>
                </div>
            </div>

            {{-- Card 4: Ideal --}}
            <div wire:click="setStatusFilter('{{ $selectedStatusFilter === 'IDEAL' ? 'ALL' : 'IDEAL' }}')"
                 class="cursor-pointer bg-base-100 rounded-2xl p-4 border transition-all flex flex-col justify-between relative overflow-hidden {{ $selectedStatusFilter === 'IDEAL' ? 'ring-2 ring-emerald-500 border-emerald-500 shadow-md' : 'border-base-200 shadow-sm hover:shadow-md' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Ideal</p>
                        <h3 class="text-lg lg:text-xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">
                            {{ number_format($metrics['count_ideal'], 0, ',', '.') }} <span class="text-xs font-semibold text-base-content/60">{{ $entityLabel }}</span>
                        </h3>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <x-heroicon-o-check-circle class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-base-200/70 flex items-center justify-between gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-md font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        % Stock &ge; 80% • DOI &le; 50 Hr
                    </span>
                    <span class="text-base-content/50 font-medium">Aliran Sehat</span>
                </div>
            </div>
        </div>

        {{-- 3. SCROLLABLE TABLE CARD (Flex-1 min-h-0 overflow-hidden: HANYA BAGIAN INI YANG SCROLL) --}}
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 w-full flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">

            {{-- Header Tabel & Kontrol Terpadu (Shrink-0) --}}
            <div class="px-5 py-2.5 border-b border-base-200 flex flex-wrap items-center justify-between gap-2.5 shrink-0">
                <div class="flex items-center gap-2 flex-wrap">
                    @if($selectedStatusFilter !== 'ALL')
                        <button type="button" 
                                wire:click="setStatusFilter('ALL')" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-xs font-semibold cursor-pointer"
                                title="Klik untuk reset filter status">
                            <span>Status: {{ $statusPills[$selectedStatusFilter]['label'] ?? $selectedStatusFilter }}</span>
                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                        </button>
                    @endif

                    @if($selectedMovementFilter !== 'ALL')
                        @php
                            $mvLabels = [
                                'FAST' => '🚀 Fast Moving',
                                'MEDIUM' => '⚖️ Medium Moving',
                                'SLOW' => '🐢 Slow Moving',
                                'DEAD' => '⛔ Dead SKU',
                            ];
                        @endphp
                        <button type="button" 
                                wire:click="setMovementFilter('ALL')" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20 transition-colors text-xs font-semibold cursor-pointer"
                                title="Klik untuk reset filter kecepatan">
                            <span>Gerak: {{ $mvLabels[$selectedMovementFilter] ?? $selectedMovementFilter }}</span>
                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto justify-end">
                    {{-- Dropdown Filter Status Diagnosa --}}
                    <div class="flex items-center">
                        <select wire:model.live="selectedStatusFilter" class="select select-sm h-8 min-h-0 text-xs rounded-xl bg-base-100 border-base-300 font-medium focus:border-primary focus:outline-hidden {{ $selectedStatusFilter !== 'ALL' ? 'border-primary text-primary font-semibold' : 'text-base-content/70' }}" title="Filter Berdasarkan Status Diagnosa">
                            <option value="ALL">Semua Diagnosa ({{ number_format($metrics['total_records']) }})</option>
                            <option value="PHANTOM_SALES">🔴 Penjualan Aneh ({{ number_format($metrics['count_phantom']) }})</option>
                            <option value="OVER_STOCK">🟡 Over Stock ({{ number_format($metrics['count_overstock']) }})</option>
                            <option value="UNDER_STOCK">🔵 Under Stock ({{ number_format($metrics['count_understock']) }})</option>
                            <option value="IDEAL">🟢 Ideal ({{ number_format($metrics['count_ideal']) }})</option>
                        </select>
                    </div>

                    {{-- Dropdown Filter Kecepatan Gerak (Fast / Slow) --}}
                    <div class="flex items-center">
                        <select wire:model.live="selectedMovementFilter" class="select select-sm h-8 min-h-0 text-xs rounded-xl bg-base-100 border-base-300 font-medium focus:border-primary focus:outline-hidden {{ $selectedMovementFilter !== 'ALL' ? 'border-primary text-primary font-semibold' : 'text-base-content/70' }}" title="Filter Berdasarkan Kecepatan Gerak SKU">
                            <option value="ALL">Semua Kecepatan</option>
                            <option value="FAST">🚀 Fast Moving ({{ number_format($metrics['count_fast']) }})</option>
                            <option value="MEDIUM">⚖️ Medium Moving ({{ number_format($metrics['count_medium']) }})</option>
                            <option value="SLOW">🐢 Slow Moving ({{ number_format($metrics['count_slow']) }})</option>
                            <option value="DEAD">⛔ Dead SKU ({{ number_format($metrics['count_dead']) }})</option>
                        </select>
                    </div>

                    {{-- Segmented Toggle View: Per Produk (SKU) vs Per Distributor --}}
                    <div class="inline-flex items-center p-0.5 rounded-xl bg-base-200/80 border border-base-300">
                        <button type="button"
                                wire:click="setViewMode('product')" 
                                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer {{ $viewMode === 'product' ? 'bg-base-100 text-base-content font-bold shadow-xs' : 'text-base-content/60 hover:text-base-content font-medium' }}">
                            <x-heroicon-o-cube class="w-3.5 h-3.5" />
                            Per Produk (SKU)
                        </button>
                        <button type="button"
                                wire:click="setViewMode('distributor')" 
                                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer {{ $viewMode === 'distributor' ? 'bg-base-100 text-base-content font-bold shadow-xs' : 'text-base-content/60 hover:text-base-content font-medium' }}">
                            <x-heroicon-o-building-storefront class="w-3.5 h-3.5" />
                            Per Distributor
                        </button>
                    </div>

                    {{-- Search Box --}}
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari {{ $viewMode === 'product' ? 'SKU / distributor...' : 'region / area / distributor...' }}" />

                    {{-- Export Excel Button --}}
                    <button wire:click="export" class="btn btn-sm h-8 min-h-0 px-3 rounded-xl btn-outline border-base-300 hover:border-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 text-xs font-semibold gap-1.5">
                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Export Excel</span>
                    </button>
                </div>
            </div>

            {{-- Area Tabel Data (Scrollable Container Internal) --}}
            <div class="flex-1 overflow-auto min-h-0 w-full relative">
                <table class="table table-sm table-pin-rows w-full whitespace-nowrap">
                    <thead>
                        <tr class="bg-base-200 text-xs font-bold uppercase tracking-wider text-base-content/60 border-b border-base-200 select-none z-10">
                            @if($viewMode === 'product')
                                <th wire:click="sortBy('distributor_name')" class="py-3 pl-5 cursor-pointer hover:text-base-content bg-base-200">
                                    Distributor & Depo
                                    @if ($sortField === 'distributor_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </th>
                                <th wire:click="sortBy('product_name')" class="py-3 cursor-pointer hover:text-base-content bg-base-200">
                                    Produk / SKU
                                    @if ($sortField === 'product_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </th>
                            @else
                                <th wire:click="sortBy('region_name')" class="py-3 pl-5 cursor-pointer hover:text-base-content bg-base-200">
                                    Region / Area
                                    @if ($sortField === 'region_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </th>
                                <th wire:click="sortBy('distributor_name')" class="py-3 cursor-pointer hover:text-base-content bg-base-200">
                                    Distributor & Depo
                                    @if ($sortField === 'distributor_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </th>
                            @endif

                            <th wire:click="sortBy('stok_awal_sistem')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Saldo Akhir Sistem Bulan Lalu">
                                Awal (Sistem)
                                @if ($sortField === 'stok_awal_sistem') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('stok_closing_lalu')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Stok Awal Fisik Distributor (Upload Closing Bulan Lalu)">
                                Awal (Dist)
                                @if ($sortField === 'stok_closing_lalu') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('sellin_ktn')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Kiriman Pabrik Masuk Bulan {{ $currMonthName }}">
                                Sell In {{ $currMonthName }}
                                @if ($sortField === 'sellin_ktn') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('total_stok_ketersediaan')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Total Ketersediaan: Awal (Dist) + Sell In {{ $currMonthName }}">
                                Total Stok
                                @if ($sortField === 'total_stok_ketersediaan') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('sellout_ktn')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Penjualan ke Toko Bulan {{ $currMonthName }}">
                                Sell Out {{ $currMonthName }}
                                @if ($sortField === 'sellout_ktn') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('sisa_stok_berjalan')" class="py-3 text-right cursor-pointer hover:text-primary bg-primary/10 text-primary font-bold border-x border-primary/20" title="Sisa Stok: Total Stok - Sell Out {{ $currMonthName }}">
                                Sisa Stok
                                @if ($sortField === 'sisa_stok_berjalan') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('avg_3lm_sellout')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Rata-rata Sell Out 3 Bulan Terakhir (3 Last Months)">
                                AVG Sell Out
                                @if ($sortField === 'avg_3lm_sellout') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('ratio_sisa_avg')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Persentase Sisa Stok terhadap AVG Sell Out Bulanan (%) — Highlight Merah jika < 80%">
                                % Stock
                                @if ($sortField === 'ratio_sisa_avg') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('doi_days')" class="py-3 text-center cursor-pointer hover:text-base-content bg-base-200" title="Days of Inventory (Hari): Sisa Stok ÷ (AVG Sell Out ÷ 30) — Kuning > 30 Hari, Merah > 50 Hari">
                                DOI
                                @if ($sortField === 'doi_days') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </th>
                            <th wire:click="sortBy('movement_class')" class="py-3 text-center cursor-pointer hover:text-base-content bg-base-200" title="Kecepatan Gerak SKU: Fast, Medium, Slow, Dead">
                                <div class="inline-flex items-center justify-center gap-1">
                                    <span>Gerak</span>
                                    @if ($sortField === 'movement_class') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                    <button type="button"
                                            @click.stop="showDiagnosticInfo = true"
                                            class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full bg-emerald-500/15 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all cursor-pointer"
                                            title="Klik untuk melihat kriteria Fast, Medium, Slow & Dead Moving">
                                        <x-heroicon-s-information-circle class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </th>
                            <th class="py-3 pr-5 text-center bg-base-200">
                                <div class="inline-flex items-center justify-center gap-1">
                                    <span>Diagnosa</span>
                                    <button type="button"
                                            @click.stop="showDiagnosticInfo = true"
                                            class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-500/15 text-amber-600 hover:bg-amber-500 hover:text-white transition-all cursor-pointer"
                                            title="Klik untuk melihat mekanisme diagnosa">
                                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200/70 text-xs">
                        @forelse ($stocks as $s)
                            <tr class="hover:bg-base-200/30 transition-colors">
                                {{-- Kolom Identitas (Per Produk: Dist + Produk | Per Distributor: Region/Area + Dist) --}}
                                @if($viewMode === 'product')
                                    <td class="py-3 pl-5">
                                        <div class="font-semibold text-base-content text-xs">{{ $s->distributor_name ?? '-' }}</div>
                                        <div class="text-xs text-base-content/50 mt-0.5">
                                            <span class="font-mono tabular-nums">{{ $s->distributor_code }}</span> • Depo {{ $s->branch_name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="font-semibold text-base-content text-xs">{{ $s->product_name ?? $s->product_id }}</div>
                                        <div class="text-xs text-base-content/50 font-mono tabular-nums mt-0.5">{{ $s->product_id }}</div>
                                    </td>
                                @else
                                    <td class="py-3 pl-5">
                                        <div class="font-semibold text-base-content text-xs">{{ $s->region_name ?? '-' }}</div>
                                        <div class="text-xs text-base-content/50 mt-0.5">{{ $s->area_name ?? '-' }}</div>
                                    </td>
                                    <td class="py-3">
                                        <div class="font-semibold text-base-content text-xs">{{ $s->distributor_name ?? '-' }}</div>
                                        <div class="text-xs text-base-content/50 mt-0.5">
                                            <span class="font-mono tabular-nums">{{ $s->distributor_code }}</span> • Depo {{ $s->branch_name ?? '-' }}
                                        </div>
                                    </td>
                                @endif

                                {{-- 1. Stok Awal (Sistem) --}}
                                <td class="py-3 text-right font-medium text-base-content/70 tabular-nums">
                                    {{ number_format(round($s->stok_awal_sistem), 0, ',', '.') }}
                                </td>

                                {{-- 2. Stok Closing Lalu --}}
                                <td class="py-3 text-right font-semibold text-base-content tabular-nums">
                                    {{ number_format(round($s->stok_closing_lalu), 0, ',', '.') }}
                                </td>

                                {{-- 3. Sell In --}}
                                <td class="py-3 text-right font-semibold text-base-content tabular-nums">
                                    {{ number_format(round($s->sellin_ktn), 0, ',', '.') }}
                                </td>

                                {{-- 5. Total Stok (Closing + In) --}}
                                <td class="py-3 text-right font-bold text-base-content tabular-nums">
                                    {{ number_format(round($s->total_stok_ketersediaan), 0, ',', '.') }}
                                </td>

                                {{-- 6. Sell Out --}}
                                <td class="py-3 text-right font-semibold text-base-content tabular-nums">
                                    {{ number_format(round($s->sellout_ktn), 0, ',', '.') }}
                                </td>

                                {{-- 6. Sisa Stok (Total Stok - Sell Out) --}}
                                <td class="py-3 text-right font-extrabold tabular-nums bg-primary/5 border-x border-primary/10">
                                    @if ($s->sisa_stok_berjalan < 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">
                                            {{ number_format(round($s->sisa_stok_berjalan), 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-base-content font-bold">{{ number_format(round($s->sisa_stok_berjalan), 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                {{-- 7. AVG Sell Out --}}
                                <td class="py-3 text-right font-medium text-base-content/80 tabular-nums">
                                    {{ number_format(round($s->avg_3lm_sellout ?? 0), 0, ',', '.') }}
                                </td>

                                {{-- 8. % (Sisa Stok / Avg Sell Out) --}}
                                @php
                                    $isMacet = ($s->ratio_sisa_avg >= 999999 || ($s->avg_3lm_sellout <= 0 && $s->sisa_stok_berjalan > 0));
                                    $isBelow80 = !$isMacet && ($s->ratio_sisa_avg < 80);
                                @endphp
                                <td class="py-3 text-right tabular-nums">
                                    @if ($isMacet)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-600 border border-slate-500/20">Macet</span>
                                    @elseif ($isBelow80)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">
                                            {{ number_format(round($s->ratio_sisa_avg), 0, ',', '.') }}%
                                        </span>
                                    @else
                                        <span class="font-semibold text-base-content">{{ number_format(round($s->ratio_sisa_avg), 0, ',', '.') }}%</span>
                                    @endif
                                </td>

                                {{-- 9. DOI (Hari) --}}
                                @php
                                    $isMacetDoi = ($s->doi_days >= 999999 || ($s->avg_3lm_sellout <= 0 && $s->sisa_stok_berjalan > 0));
                                @endphp
                                <td class="py-3 text-center tabular-nums">
                                    @if ($isMacetDoi)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-600 border border-slate-500/20">Macet</span>
                                    @elseif ($s->doi_days < 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">Minus</span>
                                    @elseif ($s->doi_days > 50)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ round($s->doi_days) }} hr</span>
                                    @elseif ($s->doi_days > 30)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ round($s->doi_days) }} hr</span>
                                    @elseif ($s->doi_days < 7)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-600 border border-sky-500/20">{{ round($s->doi_days) }} hr</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ round($s->doi_days) }} hr</span>
                                    @endif
                                </td>

                                {{-- 10. Kecepatan Gerak SKU (Fast / Med / Slow / Dead) --}}
                                <td class="py-3 text-center">
                                    @php
                                        [$mvClass, $mvLabel] = match($s->movement_class ?? 'SLOW') {
                                            'FAST' => [
                                                'bg-emerald-500/10 text-emerald-600 border-emerald-500/20 hover:bg-emerald-500/20',
                                                '🚀 Fast'
                                            ],
                                            'MEDIUM' => [
                                                'bg-sky-500/10 text-sky-600 border-sky-500/20 hover:bg-sky-500/20',
                                                '⚖️ Med'
                                            ],
                                            'DEAD' => [
                                                'bg-slate-500/10 text-slate-600 border-slate-500/20 hover:bg-slate-500/20',
                                                '⛔ Dead'
                                            ],
                                            default => [
                                                'bg-amber-500/10 text-amber-600 border-amber-500/20 hover:bg-amber-500/20',
                                                '🐢 Slow'
                                            ],
                                        };
                                    @endphp
                                    <button type="button"
                                            wire:click="setMovementFilter('{{ $s->movement_class }}')"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border transition-all cursor-pointer {{ $mvClass }}"
                                            title="Kecepatan: {{ $mvLabel }} (Klik untuk filter)">
                                        {{ $mvLabel }}
                                    </button>
                                </td>

                                {{-- 11. Diagnosa (Hanya Badge Status) --}}
                                <td class="py-3 pr-5 text-center">
                                    @php
                                        [$badgeClass, $badgeLabel] = match($s->sales_flow_status) {
                                            'PHANTOM_SALES' => [
                                                'bg-rose-500/10 text-rose-600 border-rose-500/20 hover:bg-rose-500/20',
                                                'Penjualan Aneh'
                                            ],
                                            'OVER_STOCK' => [
                                                'bg-amber-500/10 text-amber-600 border-amber-500/20 hover:bg-amber-500/20',
                                                'Over Stock'
                                            ],
                                            'UNDER_STOCK' => [
                                                'bg-sky-500/10 text-sky-600 border-sky-500/20 hover:bg-sky-500/20',
                                                'Under Stock'
                                            ],
                                            default => [
                                                'bg-emerald-500/10 text-emerald-600 border-emerald-500/20 hover:bg-emerald-500/20',
                                                'Ideal'
                                            ],
                                        };
                                    @endphp
                                    <button type="button"
                                            wire:click="setStatusFilter('{{ $s->sales_flow_status }}')"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border transition-all cursor-pointer {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-12 text-base-content/50">
                                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-2 opacity-30" />
                                    <p class="font-semibold text-sm text-base-content/70">Tidak ada data stok & aliran yang cocok dengan filter</p>
                                    <p class="text-xs mt-0.5">Coba ubah filter wilayah, status diagnosa, atau klik tombol Upload Closing di atas.</p>
                                    @if($selectedStatusFilter !== 'ALL')
                                        <button type="button" wire:click="setStatusFilter('ALL')" class="btn btn-xs btn-ghost text-primary mt-2">
                                            Reset Filter Diagnosa
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer (Shrink-0) --}}
            @if($stocks->hasPages())
                <div class="px-5 py-2.5 border-t border-base-200 bg-base-100 shrink-0">
                    {{ $stocks->links() }}
                </div>
            @endif

        </div>

    </div>

    {{-- 4. MODAL UPLOAD STOK DISTRIBUTOR --}}
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 bg-base-content/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-base-100 rounded-3xl shadow-2xl max-w-xl w-full border border-base-200 overflow-hidden transform transition-all">
                
                {{-- Header Modal --}}
                <div class="p-5 border-b border-base-200 flex items-center justify-between bg-base-200/30">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary text-white flex items-center justify-center">
                            <x-heroicon-s-arrow-up-tray class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-base-content">Upload Stok Closing Distributor</h3>
                            <p class="text-xs text-base-content/60">Otomatis menjadi Stok Awal untuk monitoring bulan berikutnya</p>
                        </div>
                    </div>
                    <button wire:click="closeUploadModal" class="btn btn-ghost btn-circle btn-sm">✕</button>
                </div>

                {{-- Body Modal --}}
                <div class="p-6 space-y-4">
                    @if ($uploadErrorMessage)
                        <div class="alert alert-error rounded-2xl text-xs py-2.5 items-start">
                            <x-heroicon-s-x-circle class="w-5 h-5 shrink-0 mt-0.5" />
                            <div class="whitespace-pre-line leading-relaxed font-medium">{{ $uploadErrorMessage }}</div>
                        </div>
                    @endif
                    @if ($uploadSuccessMessage)
                        <div class="alert alert-success rounded-2xl text-xs py-2.5">
                            <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
                            <div>
                                <p class="font-bold">{{ $uploadSuccessMessage }}</p>
                                @if ($uploadSummary)
                                    <div class="text-[11px] mt-1 space-y-0.5">
                                        <p>• Total Baris Diproses: {{ number_format($uploadSummary['total_rows'] ?? 0) }}</p>
                                        <p class="text-emerald-700">• Total Fisik Masuk: {{ number_format($uploadSummary['total_stock_ktn'] ?? 0) }} Karton</p>
                                        <p class="text-primary">• Total Estimasi Nilai: Rp {{ number_format($uploadSummary['total_value_rp'] ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Pilihan Periode --}}
                    <div class="grid grid-cols-2 gap-3 bg-base-200/30 p-3 rounded-2xl border border-base-200">
                        <div class="form-control">
                            <label class="label py-1 text-xs font-semibold text-base-content/70">Bulan Closing</label>
                            <select wire:model="uploadMonth" class="select select-bordered select-sm w-full rounded-xl">
                                @foreach($months as $mNum => $mName)
                                    <option value="{{ $mNum }}">{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label py-1 text-xs font-semibold text-base-content/70">Tahun Closing</label>
                            <select wire:model="uploadYear" class="select select-bordered select-sm w-full rounded-xl">
                                @foreach($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- File Input --}}
                    <div class="form-control">
                        <label class="label py-1 text-xs font-semibold text-base-content/70">Pilih File Excel (.xlsx / .xls)</label>
                        <input type="file" wire:model="stockFile" accept=".xlsx,.xls,.csv" 
                               class="file-input file-input-bordered file-input-sm w-full rounded-xl file-input-primary" />
                        <label class="label py-1 text-[11px] text-base-content/50">
                            Kolom wajib: <code class="text-primary font-bold">Kode Distributor</code>, <code class="text-primary font-bold">Kode Produk</code>, <code class="text-primary font-bold">Stok Akhir Karton</code>
                        </label>
                    </div>

                    {{-- Download Format Template --}}
                    <div class="bg-sky-500/10 rounded-2xl p-3 border border-sky-500/20 flex items-center justify-between text-xs text-base-content">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-information-circle class="w-5 h-5 text-sky-600 shrink-0" />
                            <span>Unduh template resmi Excel untuk diisi distributor</span>
                        </div>
                        <button type="button" wire:click="downloadTemplate" class="btn btn-outline btn-primary btn-xs rounded-lg gap-1">
                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                            Template .xlsx
                        </button>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="p-5 border-t border-base-200 flex items-center justify-end gap-2 bg-base-200/30">
                    <button type="button" wire:click="closeUploadModal" class="btn btn-ghost btn-sm rounded-xl">
                        Tutup
                    </button>
                    <button type="button" wire:click="processUpload" wire:loading.attr="disabled" class="btn btn-primary btn-sm rounded-xl gap-2 shadow-sm">
                        <span wire:loading wire:target="processUpload" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        <span>Proses Upload</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- 5. MODAL PENJELASAN MEKANISME & RUMUS DIAGNOSA --}}
    <div x-show="showDiagnosticInfo"
         x-cloak
         x-transition.opacity
         @keydown.escape.window="showDiagnosticInfo = false"
         class="fixed inset-0 z-50 bg-base-content/50 backdrop-blur-sm flex items-center justify-center p-4"
         style="z-index: 9998;">
        <div @click.outside="showDiagnosticInfo = false"
             class="bg-base-100 rounded-3xl shadow-2xl max-w-3xl w-full max-h-[88vh] border border-base-200 flex flex-col overflow-hidden">

            {{-- Header Modal --}}
            <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between bg-base-200/40 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                        <x-heroicon-s-exclamation-circle class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-base-content">Mekanisme & Parameter Diagnosa Aliran Stok</h3>
                        <p class="text-xs text-base-content/60">Panduan rumus perhitungan dan ambang batas (threshold) klasifikasi status</p>
                    </div>
                </div>
                <button type="button" @click="showDiagnosticInfo = false" class="btn btn-ghost btn-circle btn-sm">✕</button>
            </div>

            {{-- Body Modal (Scrollable) --}}
            <div class="p-6 overflow-y-auto space-y-5 text-xs leading-relaxed">

                {{-- Bagian 1: Rumus Kolom --}}
                <div class="bg-base-200/40 rounded-2xl p-4 border border-base-200 space-y-2.5">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 flex items-center gap-1.5">
                        <x-heroicon-s-calculator class="w-4 h-4 text-primary" />
                        1. Rumus Dasar Perhitungan Kolom
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                        <div class="bg-base-100 p-3 rounded-xl border border-base-200">
                            <div class="font-bold text-base-content">Total Stok (Ketersediaan)</div>
                            <div class="font-mono text-[11px] text-primary mt-0.5">Awal (Dist) + Sell In Bulan Ini</div>
                            <div class="text-base-content/60 mt-1">Total barang yang tersedia di gudang distributor pada bulan berjalan.</div>
                        </div>
                        <div class="bg-base-100 p-3 rounded-xl border border-base-200">
                            <div class="font-bold text-base-content">Sisa Stok</div>
                            <div class="font-mono text-[11px] text-primary mt-0.5">Total Stok - Sell Out Bulan Ini</div>
                            <div class="text-base-content/60 mt-1">Estimasi sisa stok berjalan di gudang distributor saat ini.</div>
                        </div>
                        <div class="bg-base-100 p-3 rounded-xl border border-base-200">
                            <div class="font-bold text-base-content">AVG Sell Out</div>
                            <div class="font-mono text-[11px] text-primary mt-0.5">Total Sell Out 3 Bulan Terakhir ÷ 3</div>
                            <div class="text-base-content/60 mt-1">Rata-rata penjualan ke toko bulanan selama 3 bulan terakhir sebelum periode yang dipilih.</div>
                        </div>
                        <div class="bg-base-100 p-3 rounded-xl border border-base-200">
                            <div class="font-bold text-base-content">DOI (Days of Inventory / Umur Stok)</div>
                            <div class="font-mono text-[11px] text-primary mt-0.5">Sisa Stok ÷ (AVG Sell Out ÷ 30)</div>
                            <div class="text-base-content/60 mt-1">Estimasi berapa hari sisa stok di gudang akan habis dengan kecepatan rata-rata jual harian (AVG Sell Out ÷ 30 hari).</div>
                        </div>
                    </div>
                    <div class="text-[11px] text-base-content/60 pt-1">
                        * <strong>Filter Otomatis:</strong> Baris SKU yang memiliki <code class="font-mono font-semibold">Total Stok = 0</code> dan <code class="font-mono font-semibold">Sell Out = 0</code> (tidak aktif) otomatis disembunyikan agar tidak mengaburkan analisa.
                    </div>
                </div>

                {{-- Bagian 2: Hierarki Urutan Diagnosa --}}
                <div class="space-y-2.5">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 flex items-center gap-1.5">
                        <x-heroicon-s-adjustments-horizontal class="w-4 h-4 text-primary" />
                        2. Urutan Prioritas & 4 Kategori Diagnosa Aliran
                    </h4>
                    <p class="text-base-content/60">
                        Sistem mengevaluasi setiap baris secara berurutan dari prioritas <strong>#1 hingga #4</strong>:
                    </p>

                    <div class="space-y-2">
                        {{-- Prioritas 1 --}}
                        <div class="p-3.5 rounded-2xl border border-rose-500/20 bg-rose-500/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/15 text-rose-600 border border-rose-500/20">1. Penjualan Aneh</span>
                                    <span class="font-mono font-bold text-rose-600">Sisa Stok &lt; 0 (Sell Out &gt; Total Stok)</span>
                                </div>
                                <p class="text-base-content/70">
                                    Penjualan ke toko melebihi total barang yang tersedia. Terjadi karena stok closing belum di-upload, kiriman belum tercatat, atau anomali faktur.
                                </p>
                            </div>
                        </div>

                        {{-- Prioritas 2 --}}
                        <div class="p-3.5 rounded-2xl border border-amber-500/20 bg-amber-500/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/15 text-amber-600 border border-amber-500/20">2. Over Stock</span>
                                    <span class="font-mono font-bold text-amber-600">DOI &gt; 50 Hari atau Macet (0 Out)</span>
                                </div>
                                <p class="text-base-content/70">
                                    Stok menumpuk berlebih di gudang distributor (butuh waktu &gt; 50 hari untuk habis) atau barang macet tanpa penjualan. Rekomendasi: Tahan / atur ritme Sell In.
                                </p>
                            </div>
                        </div>

                        {{-- Prioritas 3 --}}
                        <div class="p-3.5 rounded-2xl border border-sky-500/20 bg-sky-500/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-500/15 text-sky-600 border border-sky-500/20">3. Under Stock</span>
                                    <span class="font-mono font-bold text-sky-600">% Stock &lt; 80% (DOI &lt; 24 Hari)</span>
                                </div>
                                <p class="text-base-content/70">
                                    Stok tersisa kurang dari 80% kebutuhan rata-rata bulanan atau habis padahal serapan toko masih aktif. Rawan kekosongan barang (*lost sales*). Rekomendasi: Jadwalkan Sell In replenishment segera.
                                </p>
                            </div>
                        </div>

                        {{-- Prioritas 4 --}}
                        <div class="p-3.5 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-600 border border-emerald-500/20">4. Ideal</span>
                                    <span class="font-mono font-bold text-emerald-600">% Stock &ge; 80% &amp; DOI &le; 50 Hari</span>
                                </div>
                                <p class="text-base-content/70">
                                    Kondisi stok dan aliran penjualan seimbang sempurna. Stok aman dari risiko kehabisan dan tidak menumpuk berlebih di gudang.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian 3: Klasifikasi Kecepatan Gerak SKU --}}
                <div class="space-y-2.5">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80 flex items-center gap-1.5">
                        <x-heroicon-s-bolt class="w-4 h-4 text-emerald-600" />
                        3. Klasifikasi Kecepatan Gerak SKU (Fast, Medium, Slow, Dead)
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-xl border border-emerald-500/20 bg-emerald-500/5">
                            <span class="font-bold text-emerald-600">🚀 Fast Moving:</span>
                            <span class="text-base-content/70">AVG 3LM &ge; 100 ktn ATAU DOI &le; 25 hari. Barang laris perputaran cepat.</span>
                        </div>
                        <div class="p-2.5 rounded-xl border border-sky-500/20 bg-sky-500/5">
                            <span class="font-bold text-sky-600">⚖️ Medium Moving:</span>
                            <span class="text-base-content/70">AVG 3LM 20 - 99 ktn ATAU DOI 26 - 45 hari. Perputaran teratur dan stabil.</span>
                        </div>
                        <div class="p-2.5 rounded-xl border border-amber-500/20 bg-amber-500/5">
                            <span class="font-bold text-amber-600">🐢 Slow Moving:</span>
                            <span class="text-base-content/70">AVG 3LM &lt; 20 ktn ATAU DOI &gt; 45 hari. Permintaan lambat diserap toko.</span>
                        </div>
                        <div class="p-2.5 rounded-xl border border-slate-500/20 bg-slate-500/5">
                            <span class="font-bold text-slate-700">⛔ Dead SKU:</span>
                            <span class="text-base-content/70">AVG 3LM = 0. Tidak ada penjualan riil ke toko dalam 3 bulan terakhir.</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Modal --}}
            <div class="px-6 py-3.5 border-t border-base-200 flex items-center justify-between bg-base-200/40 shrink-0 text-xs">
                <span class="text-base-content/50">Parameter batas karton &amp; hari DOI dapat dikonfigurasi sesuai arahan Manajemen.</span>
                <button type="button" @click="showDiagnosticInfo = false" class="btn btn-primary btn-sm rounded-xl px-5">
                    Mengerti
                </button>
            </div>

        </div>
    </div>

</div>
