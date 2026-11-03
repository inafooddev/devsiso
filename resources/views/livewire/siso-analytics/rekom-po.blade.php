<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-2.5 md:gap-3 w-full h-full">

    {{-- 1. TAB MENU NAVIGASI (Shrink-0) --}}
    <div class="shrink-0 flex items-center justify-between gap-3">
        @include('livewire.siso-analytics.partials._tabs')
    </div>

    {{-- 2. BAR FILTER UTAMA (Shrink-0) --}}
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
                wire:key="rekom-po-dist-capsule-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}"
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

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                @php
                    $hasActiveFilters = (!$isRegionLocked && !empty($selectedRegion))
                        || (!$isAreaLocked && !empty($selectedArea))
                        || !empty($selectedDistributor)
                        || $selectedSpeedFilter !== 'ALL'
                        || $selectedStockStatusFilter !== 'ALL'
                        || !empty($search);
                @endphp

                @if($hasActiveFilters)
                    <button type="button" 
                            wire:click="resetFilters" 
                            class="btn btn-sm h-8 min-h-0 px-2.5 btn-ghost text-error hover:bg-error/10 rounded-xl text-xs font-semibold gap-1"
                            title="Reset semua filter">
                        <x-heroicon-s-x-circle class="w-4 h-4" />
                        <span class="hidden sm:inline">Reset</span>
                    </button>
                @endif
            </div>
        </x-slot>
    </x-ui.filter-toolbar>

    {{-- 3. KPI METRICS CARDS (Shrink-0) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 shrink-0">
        {{-- Card 1: Total Distributor --}}
        <div class="bg-base-100 rounded-2xl p-3.5 border border-base-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-base-content/60 uppercase tracking-wider">Distributor Terdata</div>
                <div class="text-xl font-extrabold text-base-content mt-0.5 tabular-nums">
                    {{ number_format($metrics['total_distributor']) }}
                </div>
                <div class="text-[11px] text-base-content/50 mt-1 flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-0.5 text-emerald-600 font-semibold">
                        🚀 {{ $metrics['count_fast'] }} Fast
                    </span>
                    <span>•</span>
                    <span class="text-amber-600 font-semibold">
                        🐢 {{ $metrics['count_slow'] }} Slow
                    </span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <x-heroicon-o-building-storefront class="w-5 h-5" />
            </div>
        </div>

        {{-- Card 2: Distributor Stok Kurang --}}
        <div class="bg-base-100 rounded-2xl p-3.5 border border-base-200 shadow-xs flex items-center justify-between cursor-pointer hover:border-rose-500/40 transition-colors"
             wire:click="setStockStatusFilter('UNDER_STOCK')"
             title="Klik untuk filter distributor stok kurang">
            <div>
                <div class="text-[11px] font-semibold text-base-content/60 uppercase tracking-wider">Stok Kurang (&lt;80%)</div>
                <div class="text-xl font-extrabold text-rose-600 mt-0.5 tabular-nums">
                    {{ number_format($metrics['count_understock']) }} <span class="text-xs font-normal text-base-content/50">Dist</span>
                </div>
                <div class="text-[11px] text-rose-600/80 font-medium mt-1">
                    Rawan kehabisan stok (*stockout*)
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
            </div>
        </div>

        {{-- Card 3: Total Rekomendasi PO (Karton) --}}
        <div class="bg-base-100 rounded-2xl p-3.5 border border-base-200 shadow-xs flex items-center justify-between bg-gradient-to-br from-emerald-500/5 to-transparent">
            <div>
                <div class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Total Rekomendasi PO</div>
                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 tabular-nums">
                    {{ number_format($metrics['sum_rekom_po_ktn']) }} <span class="text-xs font-medium">Ktn</span>
                </div>
                <div class="text-[11px] text-base-content/60 mt-1">
                    {{ number_format($metrics['sum_sku_butuh_po']) }} item SKU butuh order
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <x-heroicon-s-shopping-cart class="w-5 h-5" />
            </div>
        </div>

        {{-- Card 4: Total Estimasi Nilai PO (Rp) --}}
        <div class="bg-base-100 rounded-2xl p-3.5 border border-base-200 shadow-xs flex items-center justify-between bg-gradient-to-br from-indigo-500/5 to-transparent">
            <div>
                <div class="text-[11px] font-semibold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">Estimasi Nilai PO</div>
                <div class="text-xl font-black text-indigo-600 dark:text-indigo-400 mt-0.5 tabular-nums">
                    Rp {{ number_format($metrics['sum_rekom_po_value']) }}
                </div>
                <div class="text-[11px] text-base-content/60 mt-1">
                    Acuan batas maksimal stok 120%
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <x-heroicon-s-banknotes class="w-5 h-5" />
            </div>
        </div>
    </div>

    {{-- 4. SCROLLABLE TABLE CARD (Flex-1 min-h-0) --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 w-full flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">

        {{-- Table Toolbar / Filters (Shrink-0) --}}
        <div class="px-5 py-2.5 border-b border-base-200 flex flex-wrap items-center justify-between gap-2.5 shrink-0">
            <div class="flex items-center gap-2 flex-wrap">
                <div class="text-xs font-bold text-base-content flex items-center gap-1.5">
                    <x-heroicon-s-clipboard-document-list class="w-4 h-4 text-primary" />
                    <span>Summary Stok & Kebutuhan PO per Distributor</span>
                </div>

                @if($selectedSpeedFilter !== 'ALL')
                    <button type="button" 
                            wire:click="setSpeedFilter('ALL')" 
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-xs font-semibold cursor-pointer">
                        <span>Kecepatan: {{ $selectedSpeedFilter }}</span>
                        <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif

                @if($selectedStockStatusFilter !== 'ALL')
                    <button type="button" 
                            wire:click="setStockStatusFilter('ALL')" 
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-rose-500/10 text-rose-600 hover:bg-rose-500/20 transition-colors text-xs font-semibold cursor-pointer">
                        <span>Stok: {{ $selectedStockStatusFilter }}</span>
                        <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto justify-end">
                {{-- Dropdown Filter Kecepatan Penjualan --}}
                <div class="flex items-center">
                    <select wire:model.live="selectedSpeedFilter" 
                            class="select select-sm h-8 min-h-0 text-xs rounded-xl bg-base-100 border-base-300 font-medium focus:border-primary focus:outline-hidden {{ $selectedSpeedFilter !== 'ALL' ? 'border-primary text-primary font-semibold' : 'text-base-content/70' }}" 
                            title="Filter Berdasarkan Kecepatan Penjualan Distributor">
                        <option value="ALL">Semua Kecepatan</option>
                        <option value="FAST">🚀 Fast Moving (DOI &le; 30 hr)</option>
                        <option value="MEDIUM">⚖️ Medium Moving (DOI 31-45 hr)</option>
                        <option value="SLOW">🐢 Slow Moving (DOI &gt; 45 hr)</option>
                    </select>
                </div>

                {{-- Dropdown Filter Status Kesehatan Stok --}}
                <div class="flex items-center">
                    <select wire:model.live="selectedStockStatusFilter" 
                            class="select select-sm h-8 min-h-0 text-xs rounded-xl bg-base-100 border-base-300 font-medium focus:border-primary focus:outline-hidden {{ $selectedStockStatusFilter !== 'ALL' ? 'border-primary text-primary font-semibold' : 'text-base-content/70' }}" 
                            title="Filter Berdasarkan Kesehatan Stok Distributor">
                        <option value="ALL">Semua Status Stok</option>
                        <option value="UNDER_STOCK">🔴 Stok Kurang (&lt; 80%)</option>
                        <option value="SAFE">🟢 Stok Aman (80% - 120%)</option>
                        <option value="OVER_STOCK">🟡 Over Stock (&gt; 120%)</option>
                    </select>
                </div>

                {{-- Search Box --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari distributor / cabang..." />

                {{-- Export Excel Button --}}
                <button wire:click="exportSummary" class="btn btn-sm h-8 min-h-0 px-3 rounded-xl btn-outline border-base-300 hover:border-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 text-xs font-semibold gap-1.5">
                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Export Excel</span>
                </button>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="flex-1 overflow-auto min-h-0 w-full relative">
            <table class="table table-sm table-pin-rows w-full whitespace-nowrap">
                <thead>
                    <tr class="bg-base-200 text-xs font-bold uppercase tracking-wider text-base-content/60 border-b border-base-200 select-none z-10">
                        <th wire:click="sortBy('distributor_name')" class="py-3 pl-5 cursor-pointer hover:text-base-content bg-base-200">
                            Distributor & Depo
                            @if ($sortField === 'distributor_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('region_name')" class="py-3 cursor-pointer hover:text-base-content bg-base-200">
                            Wilayah
                            @if ($sortField === 'region_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_stok_awal')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Stok Awal Fisik Distributor (Upload Closing Bulan Lalu)">
                            Awal (Dist)
                            @if ($sortField === 'total_stok_awal') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_sellin')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Kiriman Pabrik Masuk Bulan Ini">
                            Sell In
                            @if ($sortField === 'total_sellin') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_stok_ketersediaan')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Total Ketersediaan Barang: Awal (Dist) + Sell In">
                            Total Stok
                            @if ($sortField === 'total_stok_ketersediaan') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_sellout')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Penjualan Riil ke Toko Bulan Ini">
                            Sell Out
                            @if ($sortField === 'total_sellout') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_sisa_stok')" class="py-3 text-right cursor-pointer hover:text-primary bg-primary/10 text-primary font-bold border-x border-primary/20" title="Sisa Stok Berjalan: Total Stok - Sell Out">
                            Sisa Stok
                            @if ($sortField === 'total_sisa_stok') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_avg_3lm')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Rata-rata Total Penjualan Bulanan ke Toko (3LM)">
                            AVG 3LM
                            @if ($sortField === 'total_avg_3lm') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('doi_distributor')" class="py-3 text-center cursor-pointer hover:text-base-content bg-base-200" title="Days of Inventory Agregat Distributor">
                            DOI
                            @if ($sortField === 'doi_distributor') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('speed_status')" class="py-3 text-center cursor-pointer hover:text-base-content bg-base-200" title="Kecepatan Perputaran Penjualan Distributor">
                            Kecepatan
                            @if ($sortField === 'speed_status') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('stock_status')" class="py-3 text-center cursor-pointer hover:text-base-content bg-base-200" title="Status Kesehatan Stok Distributor Keseluruhan">
                            Status Stok
                            @if ($sortField === 'stock_status') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('count_sku_butuh_po')" class="py-3 text-center cursor-pointer hover:text-base-content bg-base-200" title="Jumlah SKU yang stoknya di bawah 120% kebutuhan">
                            SKU Butuh PO
                            @if ($sortField === 'count_sku_butuh_po') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_rekom_po_ktn')" class="py-3 text-right cursor-pointer hover:text-primary bg-primary/10 text-primary font-bold border-x border-primary/20" title="Total Kuantitas Rekomendasi PO (Karton) untuk mencapai batas 120%">
                            Rekom PO (Ktn)
                            @if ($sortField === 'total_rekom_po_ktn') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('total_rekom_po_value')" class="py-3 text-right cursor-pointer hover:text-base-content bg-base-200" title="Estimasi Nilai Total Rupiah Rekomendasi PO">
                            Estimasi Nilai (Rp)
                            @if ($sortField === 'total_rekom_po_value') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="py-3 pr-5 text-center bg-base-200">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200/70 text-xs">
                    @forelse ($distributorsList as $d)
                        <tr class="hover:bg-base-200/30 transition-colors">
                            {{-- 1. Distributor & Depo --}}
                            <td class="py-3 pl-5">
                                <div class="font-semibold text-base-content text-xs">{{ $d->distributor_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/50 mt-0.5">
                                    <span class="font-mono tabular-nums font-semibold">{{ $d->distributor_code }}</span> • Depo {{ $d->branch_name ?? '-' }}
                                </div>
                            </td>

                            {{-- 2. Wilayah --}}
                            <td class="py-3">
                                <div class="font-medium text-base-content/80 text-xs">{{ $d->region_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/50 mt-0.5">{{ $d->area_name ?? '-' }}</div>
                            </td>

                            {{-- 3. Awal Dist --}}
                            <td class="py-3 text-right font-medium tabular-nums text-base-content/80">
                                {{ number_format(round($d->total_stok_awal)) }}
                            </td>

                            {{-- 4. Sell In --}}
                            <td class="py-3 text-right font-medium tabular-nums text-base-content/80">
                                {{ number_format(round($d->total_sellin)) }}
                            </td>

                            {{-- 5. Total Stok (Awal + In) --}}
                            <td class="py-3 text-right font-bold tabular-nums text-base-content">
                                {{ number_format(round($d->total_stok_ketersediaan)) }}
                            </td>

                            {{-- 6. Sell Out --}}
                            <td class="py-3 text-right font-medium tabular-nums text-base-content/80">
                                {{ number_format(round($d->total_sellout)) }}
                            </td>

                            {{-- 7. Sisa Stok (Total Stok - Out) --}}
                            <td class="py-3 text-right font-extrabold tabular-nums bg-primary/5 border-x border-primary/10">
                                @if ($d->total_sisa_stok < 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">
                                        {{ number_format(round($d->total_sisa_stok)) }}
                                    </span>
                                @else
                                    <span class="text-base-content font-bold">{{ number_format(round($d->total_sisa_stok)) }}</span>
                                @endif
                            </td>

                            {{-- 8. AVG 3LM --}}
                            <td class="py-3 text-right font-medium tabular-nums text-base-content/70">
                                {{ number_format(round($d->total_avg_3lm)) }}
                            </td>

                            {{-- 5. DOI --}}
                            <td class="py-3 text-center">
                                @php
                                    $doi = $d->doi_distributor;
                                    $avg = $d->total_avg_3lm;
                                    $isMacet = ($doi >= 999 || ($avg <= 0 && $d->total_sisa_stok > 0));
                                @endphp
                                @if ($isMacet)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-600 border border-slate-500/20">Macet</span>
                                @elseif ($doi < 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">Minus</span>
                                @elseif ($doi > 45)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 border border-amber-500/20">{{ round($doi) }} hr</span>
                                @elseif ($doi <= 24)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-600 border border-rose-500/20">{{ round($doi) }} hr</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">{{ round($doi) }} hr</span>
                                @endif
                            </td>

                            {{-- 6. Kecepatan Penjualan (Fast / Med / Slow) --}}
                            <td class="py-3 text-center">
                                @php
                                    [$speedClass, $speedLabel] = match($d->speed_status) {
                                        'FAST'   => ['bg-emerald-500/10 text-emerald-600 border-emerald-500/20', '🚀 Fast'],
                                        'MEDIUM' => ['bg-sky-500/10 text-sky-600 border-sky-500/20', '⚖️ Med'],
                                        default  => ['bg-amber-500/10 text-amber-600 border-amber-500/20', '🐢 Slow'],
                                    };
                                @endphp
                                <button type="button" 
                                        wire:click="setSpeedFilter('{{ $d->speed_status }}')"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border transition-all cursor-pointer {{ $speedClass }}"
                                        title="Klik untuk filter kecepatan">
                                    {{ $speedLabel }}
                                </button>
                            </td>

                            {{-- 7. Status Kesehatan Stok (Kurang / Aman / Over) --}}
                            <td class="py-3 text-center">
                                @php
                                    [$stockClass, $stockLabel] = match($d->stock_status) {
                                        'UNDER_STOCK' => ['bg-rose-500/10 text-rose-600 border-rose-500/20', '🔴 Stok Kurang'],
                                        'OVER_STOCK'  => ['bg-amber-500/10 text-amber-600 border-amber-500/20', '🟡 Over Stock'],
                                        default       => ['bg-emerald-500/10 text-emerald-600 border-emerald-500/20', '🟢 Stok Aman'],
                                    };
                                @endphp
                                <button type="button" 
                                        wire:click="setStockStatusFilter('{{ $d->stock_status }}')"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border transition-all cursor-pointer {{ $stockClass }}"
                                        title="Klik untuk filter status stok">
                                    {{ $stockLabel }}
                                </button>
                            </td>

                            {{-- 8. SKU Butuh PO --}}
                            <td class="py-3 text-center">
                                <span class="font-bold tabular-nums {{ $d->count_sku_butuh_po > 0 ? 'text-primary' : 'text-base-content/40' }}">
                                    {{ $d->count_sku_butuh_po }}
                                </span>
                                <span class="text-base-content/40 text-[11px]">/ {{ $d->total_sku_active }} SKU</span>
                            </td>

                            {{-- 9. Rekom PO (Ktn) --}}
                            <td class="py-3 text-right font-black tabular-nums bg-primary/5 border-x border-primary/10 {{ $d->total_rekom_po_ktn > 0 ? 'text-primary' : 'text-base-content/40' }}">
                                {{ number_format($d->total_rekom_po_ktn) }}
                            </td>

                            {{-- 10. Estimasi Nilai (Rp) --}}
                            <td class="py-3 text-right font-semibold tabular-nums text-base-content">
                                {{ $d->total_rekom_po_value > 0 ? 'Rp ' . number_format($d->total_rekom_po_value) : '-' }}
                            </td>

                            {{-- 11. Tombol Aksi Rekom PO --}}
                            <td class="py-3 pr-5 text-center">
                                <button type="button"
                                        wire:click="openPoDetailModal('{{ $d->distributor_code }}')"
                                        class="btn btn-xs h-7 px-2.5 rounded-xl btn-primary font-semibold gap-1 shadow-2xs hover:scale-102 transition-all cursor-pointer"
                                        title="Klik untuk melihat rincian SKU yang direkomendasikan PO">
                                    <x-heroicon-s-shopping-bag class="w-3.5 h-3.5" />
                                    <span>Rekom PO</span>
                                    @if($d->count_sku_butuh_po > 0)
                                        <span class="badge badge-xs bg-white text-primary font-bold px-1.5 ml-0.5">
                                            {{ $d->count_sku_butuh_po }}
                                        </span>
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-12 text-base-content/50">
                                <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-2 opacity-30" />
                                <p class="font-semibold text-sm text-base-content/70">Tidak ada data distributor yang cocok dengan filter</p>
                                <p class="text-xs mt-0.5">Coba ubah filter wilayah, kecepatan, atau status stok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($distributorsList->hasPages())
            <div class="px-5 py-2.5 border-t border-base-200 bg-base-100 shrink-0">
                {{ $distributorsList->links() }}
            </div>
        @endif

    </div>

    {{-- ======================================================== --}}
    {{-- 5. MODAL DETAIL SKU REKOMENDASI PO PER DISTRIBUTOR --}}
    {{-- ======================================================== --}}
    @if ($showPoModal)
        <div class="fixed inset-0 z-50 bg-base-content/50 backdrop-blur-sm flex items-center justify-center p-3 sm:p-5"
             @keydown.escape.window="$wire.closePoModal()"
             style="z-index: 9999;">
            <div class="fixed inset-0" wire:click="closePoModal"></div>

            <div class="relative bg-base-100 rounded-3xl shadow-2xl max-w-7xl xl:max-w-[92vw] 2xl:max-w-[1550px] w-full border border-base-200 overflow-hidden flex flex-col max-h-[92vh] transform transition-all animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Header Modal --}}
                <div class="px-6 py-4 border-b border-base-200 flex items-center justify-between bg-base-200/40 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-primary text-white flex items-center justify-center shrink-0 shadow-xs">
                            <x-heroicon-s-shopping-cart class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-bold text-sm text-base-content">{{ $selectedDistributorData['distributor_name'] ?? '-' }}</h3>
                                <span class="font-mono text-xs px-2 py-0.5 rounded-md bg-base-200 text-base-content/70 font-semibold">{{ $selectedDistributorData['distributor_code'] ?? '-' }}</span>
                                <span class="text-xs text-base-content/50">• Depo {{ $selectedDistributorData['branch_name'] ?? '-' }}</span>
                            </div>
                            <div class="text-xs text-base-content/60 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span>Wilayah: <strong>{{ $selectedDistributorData['region_name'] ?? '-' }} ({{ $selectedDistributorData['area_name'] ?? '-' }})</strong></span>
                                <span>•</span>
                                <span>Periode: <strong>{{ $currMonthName }} {{ $selectedYear }}</strong></span>
                                <span>•</span>
                                <span class="text-primary font-semibold">Batas Stok Maksimal 120% (1.2 &times; AVG 3LM)</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="exportDistributorPo" class="btn btn-sm h-8 min-h-0 px-3 rounded-xl btn-outline border-base-300 hover:border-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 text-xs font-semibold gap-1.5">
                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 text-emerald-600" />
                            <span>Export PO Excel</span>
                        </button>
                        <button type="button" wire:click="closePoModal" class="btn btn-ghost btn-circle btn-sm text-base-content/60 hover:text-base-content">✕</button>
                    </div>
                </div>

                {{-- Toolbar Modal: Filter Toggle & Mini Metrics --}}
                <div class="px-6 py-2.5 bg-base-200/20 border-b border-base-200 flex flex-wrap items-center justify-between gap-3 shrink-0 text-xs">
                    <div class="inline-flex items-center p-0.5 rounded-xl bg-base-200/80 border border-base-300">
                        <button type="button" 
                                wire:click="setPoDetailFilter('BUTUH_PO')" 
                                class="px-3 py-1 rounded-lg transition-all font-semibold cursor-pointer {{ $poDetailFilter === 'BUTUH_PO' ? 'bg-base-100 text-primary shadow-xs' : 'text-base-content/60 hover:text-base-content' }}">
                            🎯 Hanya Butuh PO (&lt; 120%) ({{ $poDetailSummary['sku_butuh_po'] ?? 0 }})
                        </button>
                        <button type="button" 
                                wire:click="setPoDetailFilter('ALL')" 
                                class="px-3 py-1 rounded-lg transition-all font-semibold cursor-pointer {{ $poDetailFilter === 'ALL' ? 'bg-base-100 text-base-content shadow-xs' : 'text-base-content/60 hover:text-base-content' }}">
                            📋 Semua SKU ({{ $poDetailSummary['total_sku'] ?? 0 }})
                        </button>
                    </div>

                    <div class="flex items-center gap-3 font-medium">
                        <span class="inline-flex items-center gap-1 text-rose-600">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <strong>{{ $poDetailSummary['urgent_count'] ?? 0 }}</strong> Kritis (&le; 10 hr)
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1 text-amber-600">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <strong>{{ $poDetailSummary['reorder_count'] ?? 0 }}</strong> Reorder Normal
                        </span>
                        @if(($poDetailSummary['inactive_count'] ?? 0) > 0)
                            <span>•</span>
                            <span class="inline-flex items-center gap-1 text-slate-500">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                <strong>{{ $poDetailSummary['inactive_count'] }}</strong> Nonaktif (Discontinue)
                            </span>
                        @endif
                        <span>•</span>
                        <span class="text-base-content/80">
                            Total PO: <strong class="text-primary font-bold">{{ number_format($poDetailSummary['total_ktn'] ?? 0) }} Ktn</strong> (Rp {{ number_format($poDetailSummary['total_val'] ?? 0) }})
                        </span>
                    </div>
                </div>

                {{-- Body Table Modal --}}
                <div class="flex-1 overflow-auto min-h-0 w-full relative">
                    <table class="table table-sm table-pin-rows w-full whitespace-nowrap text-xs">
                        <thead>
                            <tr class="bg-base-200 text-xs font-bold uppercase tracking-wider text-base-content/60 border-b border-base-200 select-none z-10">
                                <th class="py-3 pl-5 bg-base-200">Produk / SKU</th>
                                <th class="py-3 text-right bg-base-200" title="Stok Awal Fisik Closing Bulan Lalu">Awal (Dist)</th>
                                <th class="py-3 text-right bg-base-200" title="Penerimaan Kiriman dari Pabrik Bulan Berjalan">Sell In</th>
                                <th class="py-3 text-right bg-base-200" title="Total Ketersediaan Barang: Awal + Sell In">Total Stok</th>
                                <th class="py-3 text-right bg-base-200" title="Penjualan Riil ke Outlet/Toko Bulan Berjalan">Sell Out</th>
                                <th class="py-3 text-right bg-base-200 font-bold text-base-content" title="Sisa Stok Berjalan di Gudang Distributor (Total Stok - Sell Out)">Sisa Stok</th>
                                <th class="py-3 text-right bg-base-200" title="Rata-rata Penjualan 3 Bulan Terakhir">AVG 3LM</th>
                                <th class="py-3 text-right bg-base-200" title="Persentase Stok terhadap Kebutuhan Bulanan">% Stok</th>
                                <th class="py-3 text-center bg-base-200" title="Ketahanan Stok Berjalan (Hari)">DOI</th>
                                <th class="py-3 text-right bg-base-200" title="Batas Maksimal Stok Aman 120% (1.2 x AVG 3LM)">Target 120%</th>
                                <th class="py-3 text-right bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-extrabold border-x border-emerald-500/20" title="Kuantitas Rekomendasi Order untuk Mengisi Stok hingga 120%">Rekom PO (Ktn)</th>
                                <th class="py-3 text-right bg-base-200" title="Harga Satuan per Karton (Master Produk)">Harga Satuan</th>
                                <th class="py-3 text-right bg-base-200" title="Estimasi Nilai Total Rupiah Pesanan">Estimasi Nilai (Rp)</th>
                                <th class="py-3 pr-5 text-center bg-base-200">Urgensi Order</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-200/70">
                            @forelse ($poDetailList as $item)
                                <tr class="hover:bg-base-200/30 transition-colors {{ $item['rekom_po_ktn'] > 0 ? 'bg-emerald-500/5' : '' }}">
                                    {{-- 1. Produk / SKU --}}
                                    <td class="py-2.5 pl-5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold text-base-content">{{ $item['product_name'] }}</span>
                                            @if(!$item['is_active'])
                                                <span class="badge badge-xs bg-slate-500/10 text-slate-500 border-slate-500/20 font-semibold text-[10px]">Nonaktif</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-base-content/50 font-mono mt-0.5">{{ $item['product_id'] }}</div>
                                    </td>

                                    {{-- 2. Awal (Dist) --}}
                                    <td class="py-2.5 text-right font-medium tabular-nums text-base-content/70">
                                        {{ number_format(round($item['stock_awal_ktn'], 1), 1) }}
                                    </td>

                                    {{-- 3. Sell In --}}
                                    <td class="py-2.5 text-right font-medium tabular-nums text-base-content/70">
                                        {{ number_format(round($item['sellin_ktn'], 1), 1) }}
                                    </td>

                                    {{-- 4. Total Stok (Awal + In) --}}
                                    <td class="py-2.5 text-right font-semibold tabular-nums text-base-content/90">
                                        {{ number_format(round($item['total_stok_ketersediaan'], 1), 1) }}
                                    </td>

                                    {{-- 5. Sell Out --}}
                                    <td class="py-2.5 text-right font-medium tabular-nums text-base-content/70">
                                        {{ number_format(round($item['sellout_ktn'], 1), 1) }}
                                    </td>

                                    {{-- 6. Sisa Stok --}}
                                    <td class="py-2.5 text-right font-bold tabular-nums text-base-content">
                                        @if ($item['sisa_stok_berjalan'] < 0)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">
                                                {{ number_format(round($item['sisa_stok_berjalan'], 1), 1) }}
                                            </span>
                                        @else
                                            {{ number_format(round($item['sisa_stok_berjalan'], 1), 1) }}
                                        @endif
                                    </td>

                                    {{-- 7. AVG 3LM --}}
                                    <td class="py-2.5 text-right font-medium tabular-nums text-base-content/70">
                                        {{ number_format(round($item['avg_3lm_sellout'], 1), 1) }}
                                    </td>

                                    {{-- 8. % Stok --}}
                                    <td class="py-2.5 text-right font-medium tabular-nums">
                                        @if ($item['ratio_stok'] < 80)
                                            <span class="text-rose-600 font-bold">{{ round($item['ratio_stok']) }}%</span>
                                        @elseif ($item['ratio_stok'] <= 120)
                                            <span class="text-emerald-600 font-bold">{{ round($item['ratio_stok']) }}%</span>
                                        @else
                                            <span class="text-amber-600 font-bold">{{ round($item['ratio_stok']) }}%</span>
                                        @endif
                                    </td>

                                    {{-- 9. DOI --}}
                                    <td class="py-2.5 text-center">
                                        @php
                                            $doi = $item['doi_days'];
                                            $avg = $item['avg_3lm_sellout'];
                                            $isMacet = ($doi >= 999 || ($avg <= 0 && $item['sisa_stok_berjalan'] > 0));
                                        @endphp
                                        @if ($isMacet)
                                            <span class="text-slate-500 font-medium">Macet</span>
                                        @elseif ($doi < 0)
                                            <span class="text-rose-600 font-bold">Minus</span>
                                        @elseif ($doi <= 10)
                                            <span class="text-rose-600 font-bold">{{ round($doi) }} hr</span>
                                        @elseif ($doi <= 25)
                                            <span class="text-amber-600 font-semibold">{{ round($doi) }} hr</span>
                                        @else
                                            <span class="text-emerald-600">{{ round($doi) }} hr</span>
                                        @endif
                                    </td>

                                    {{-- 10. Target 120% --}}
                                    <td class="py-2.5 text-right font-medium tabular-nums text-base-content/60">
                                        {{ number_format($item['target_stok_120'], 1) }}
                                    </td>

                                    {{-- 11. Rekom PO (Ktn) --}}
                                    <td class="py-2.5 text-right font-black tabular-nums bg-emerald-500/10 border-x border-emerald-500/20 {{ $item['rekom_po_ktn'] > 0 ? 'text-emerald-700 dark:text-emerald-400 text-sm' : 'text-base-content/30' }}">
                                        {{ number_format($item['rekom_po_ktn']) }}
                                    </td>

                                    {{-- 12. Harga Satuan --}}
                                    <td class="py-2.5 text-right font-medium tabular-nums text-base-content/60">
                                        {{ $item['price_zone1'] > 0 ? number_format($item['price_zone1']) : '-' }}
                                    </td>

                                    {{-- 13. Estimasi Nilai (Rp) --}}
                                    <td class="py-2.5 text-right font-semibold tabular-nums text-base-content">
                                        {{ $item['total_rekom_po_value'] > 0 ? 'Rp ' . number_format($item['total_rekom_po_value']) : '-' }}
                                    </td>

                                    {{-- 14. Status Urgensi --}}
                                    <td class="py-2.5 pr-5 text-center">
                                        @php
                                            [$urgClass, $urgLabel] = match($item['urgensi_status']) {
                                                'INACTIVE' => ['bg-slate-500/10 text-slate-500 border-slate-500/20', '⛔ Nonaktif'],
                                                'URGENT'   => ['bg-rose-500/10 text-rose-600 border-rose-500/20', '🔴 Kritis'],
                                                'REORDER'  => ['bg-amber-500/10 text-amber-600 border-amber-500/20', '🟡 Reorder'],
                                                'DEAD'     => ['bg-slate-500/10 text-slate-600 border-slate-500/20', '⛔ Dead SKU'],
                                                default    => ['bg-emerald-500/10 text-emerald-600 border-emerald-500/20', '🟢 Aman'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $urgClass }}">
                                            {{ $urgLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center py-10 text-base-content/50">
                                        Tidak ada produk yang memerlukan order (seluruh stok SKU berada di atas 120%).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (!empty($poDetailList))
                            <tfoot class="bg-base-200/80 font-bold border-t border-base-300 select-none">
                                <tr>
                                    <td class="py-2.5 pl-5 text-left font-bold text-base-content">
                                        Total ({{ count($poDetailList) }} SKU)
                                    </td>
                                    <td class="py-2.5 text-right tabular-nums text-base-content">
                                        {{ number_format(round(collect($poDetailList)->sum('stock_awal_ktn'), 1), 1) }}
                                    </td>
                                    <td class="py-2.5 text-right tabular-nums text-base-content">
                                        {{ number_format(round(collect($poDetailList)->sum('sellin_ktn'), 1), 1) }}
                                    </td>
                                    <td class="py-2.5 text-right tabular-nums text-base-content font-bold">
                                        {{ number_format(round(collect($poDetailList)->sum('total_stok_ketersediaan'), 1), 1) }}
                                    </td>
                                    <td class="py-2.5 text-right tabular-nums text-base-content">
                                        {{ number_format(round(collect($poDetailList)->sum('sellout_ktn'), 1), 1) }}
                                    </td>
                                    <td class="py-2.5 text-right tabular-nums text-base-content font-bold">
                                        {{ number_format(round(collect($poDetailList)->sum('sisa_stok_berjalan'), 1), 1) }}
                                    </td>
                                    <td class="py-2.5 text-right tabular-nums text-base-content">
                                        {{ number_format(round(collect($poDetailList)->sum('avg_3lm_sellout'), 1), 1) }}
                                    </td>
                                    <td colspan="3"></td>
                                    <td class="py-2.5 text-right tabular-nums bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-black border-x border-emerald-500/20 text-sm">
                                        {{ number_format(collect($poDetailList)->sum('rekom_po_ktn')) }}
                                    </td>
                                    <td></td>
                                    <td class="py-2.5 text-right tabular-nums text-base-content font-bold">
                                        Rp {{ number_format(collect($poDetailList)->sum('total_rekom_po_value')) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-3.5 border-t border-base-200 flex items-center justify-between bg-base-200/30 shrink-0 text-xs">
                    <span class="text-base-content/50">
                        * Rekomendasi order hanya dihitung untuk produk aktif guna mengisi sisa stok distributor hingga level aman 120% (1.2 &times; AVG 3LM). Estimasi nilai PO dihitung dari Kuantitas PO &times; Harga Master Produk (Price Zone 1).
                    </span>
                    <button type="button" wire:click="closePoModal" class="btn btn-primary btn-sm rounded-xl px-5 shadow-xs">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
