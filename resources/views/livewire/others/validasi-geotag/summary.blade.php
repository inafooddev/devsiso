<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-5 w-full h-full" 
    x-data="{ showKpi: true }"
>
    <x-slot name="title">Summary Validasi Geotag Titik Koordinat Toko</x-slot>

    {{-- TABS MENU (SUMMARY & DETAIL) --}}
    <x-ui.tab-menu>
        <x-ui.tab-item href="{{ route('others.validasi-geotag.summary') }}" :active="true">
            <span class="flex items-center gap-1.5 font-bold">
                <x-heroicon-s-chart-bar class="w-3.5 h-3.5" />
                Summary
            </span>
        </x-ui.tab-item>
        <x-ui.tab-item href="{{ route('others.validasi-geotag') }}" :active="false">
            <span class="flex items-center gap-1.5 font-bold">
                <x-heroicon-s-list-bullet class="w-3.5 h-3.5" />
                Detail
            </span>
        </x-ui.tab-item>

        <x-slot:actions>
            <button 
                type="button" 
                @click="showKpi = !showKpi" 
                class="btn btn-xs btn-ghost gap-1.5 rounded-lg border border-base-300 text-xs font-semibold hover:bg-base-200 transition-all duration-200"
                :class="{ 'bg-primary/10 text-primary border-primary/30 shadow-xs': showKpi, 'text-base-content/60': !showKpi }"
                title="Sembunyikan / Tampilkan Ringkasan Metrik"
            >
                <x-heroicon-o-chart-bar class="w-3.5 h-3.5" />
                <span x-text="showKpi ? 'Sembunyikan Metrik' : 'Tampilkan Metrik'">Tampilkan Metrik</span>
                <span class="inline-block transition-transform duration-200" :class="{ 'rotate-180': showKpi }">
                    <x-heroicon-o-chevron-down class="w-3 h-3" />
                </span>
            </button>

            @if($canExport)
                <button 
                    type="button" 
                    wire:click="export" 
                    wire:loading.attr="disabled"
                    class="btn btn-xs btn-success gap-1.5 rounded-lg text-white font-semibold shadow-xs hover:bg-emerald-600 transition-all duration-200"
                    title="Export Rekap Matriks ke Excel (.xlsx)"
                >
                    <span wire:loading.remove wire:target="export" class="flex items-center gap-1.5">
                        <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" />
                        Export Excel
                    </span>
                    <span wire:loading wire:target="export" class="flex items-center gap-1.5">
                        <span class="loading loading-spinner loading-xs"></span>
                        Mengekspor...
                    </span>
                </button>
            @endif
        </x-slot:actions>
    </x-ui.tab-menu>

    {{-- HEADER PAGE TITLE --}}
    <div class="bg-base-100 p-3.5 rounded-xl border border-base-300 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-primary/10 text-primary shrink-0">
                <x-heroicon-s-chart-pie class="w-6 h-6" />
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-bold text-base-content leading-tight">
                    Summary Validasi Geotag Titik Koordinat Toko
                </h1>
                <p class="text-xs text-base-content/60 mt-0.5">
                    Matriks ringkasan progres verifikasi fisik toko, kelengkapan titik, dan deteksi anomali per wilayah
                </p>
            </div>
        </div>

        {{-- Grouping Buttons (Distributor, Area, Region) --}}
        <div class="flex items-center gap-1.5 self-start sm:self-auto bg-base-200/80 p-1 rounded-xl border border-base-300">
            <span class="text-[11px] font-bold text-base-content/60 px-2 uppercase tracking-wider">Kelompokkan:</span>
            <button 
                type="button" 
                wire:click="setGroupBy('distributor')" 
                class="btn btn-xs font-bold {{ $groupBy === 'distributor' ? 'btn-primary shadow-xs' : 'btn-ghost text-base-content/70' }}"
            >
                🏢 Per Distributor
            </button>
            <button 
                type="button" 
                wire:click="setGroupBy('area')" 
                class="btn btn-xs font-bold {{ $groupBy === 'area' ? 'btn-primary shadow-xs' : 'btn-ghost text-base-content/70' }}"
            >
                📍 Per Area
            </button>
            <button 
                type="button" 
                wire:click="setGroupBy('region')" 
                class="btn btn-xs font-bold {{ $groupBy === 'region' ? 'btn-primary shadow-xs' : 'btn-ghost text-base-content/70' }}"
            >
                🗺️ Per Region
            </button>
        </div>
    </div>

    {{-- 4 CONSOLIDATED SUMMARY CARDS (KPI METRICS) --}}
    <div x-show="showKpi" x-transition.duration.200ms class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 shrink-0">
        {{-- Card 1: Total Populasi Toko Target --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-base-content/60 uppercase tracking-wider">Populasi Toko</span>
                    <div class="text-2xl font-black text-base-content mt-1">
                        {{ number_format($kpi['total_stores']) }}
                    </div>
                    <div class="text-[11px] text-base-content/60 mt-0.5">Toko Aktif Rute Terdaftar</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-building-storefront class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-base-content/60 font-medium">Sisa Belum Divalidasi:</span>
                <span class="badge badge-warning badge-sm font-bold text-amber-950">
                    {{ number_format($kpi['unvalidated_stores']) }} ({{ $kpi['unvalidated_pct'] }}%)
                </span>
            </div>
        </div>

        {{-- Card 2: Status Validasi Fisik --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-base-content/60 uppercase tracking-wider">Hasil Validasi</span>
                    <div class="text-2xl font-black text-success mt-1">
                        {{ number_format($kpi['valid_stores']) }}
                    </div>
                    <div class="text-[11px] text-success/80 mt-0.5 font-medium">Toko Koordinat VALID</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-badge class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-base-content/60 font-medium">TIDAK VALID (Fiktif):</span>
                <span class="badge badge-error badge-sm font-bold text-white">{{ number_format($kpi['invalid_stores']) }}</span>
            </div>
        </div>

        {{-- Card 3: Kelengkapan Koordinat --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-base-content/60 uppercase tracking-wider">Kelengkapan Geotag</span>
                    <div class="text-2xl font-black text-info mt-1">
                        {{ number_format($kpi['has_coordinate']) }}
                    </div>
                    <div class="text-[11px] text-info/80 mt-0.5 font-medium">Toko Memiliki Koordinat</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-map-pin class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-base-content/60 font-medium flex items-center gap-1">
                    <x-heroicon-o-no-symbol class="w-3.5 h-3.5" />
                    Belum Ada Titik (Dummy):
                </span>
                <span class="badge badge-ghost badge-sm font-bold">{{ number_format($kpi['no_coordinate']) }}</span>
            </div>
        </div>

        {{-- Card 4: Deteksi Anomali Prioritas --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-rose-300/60 bg-gradient-to-br from-base-100 to-rose-50/30 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1">
                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                        Pusat Deteksi Anomali
                    </span>
                    <div class="text-2xl font-black text-rose-600 mt-1">
                        {{ number_format($kpi['at_distributor'] + $kpi['duplicate_stores'] + $kpi['drift_stores']) }}
                    </div>
                    <div class="text-[11px] text-base-content/60 mt-0.5">Total Temuan Lapangan</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <x-heroicon-s-shield-exclamation class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-rose-100 flex items-center gap-1.5 flex-wrap">
                <span class="badge badge-xs badge-success font-bold text-white shadow-xs" title="Toko bersih bebas anomali">
                    ✅ Siap: {{ number_format($kpi['normal_stores'] ?? 0) }}
                </span>
                <span class="badge badge-xs badge-error font-bold text-white" title="Titik toko berada di kantor/gudang distributor">
                    Depo: {{ number_format($kpi['at_distributor']) }}
                </span>
                <span class="badge badge-xs badge-warning font-bold text-amber-950" title="Titik kembar di lapangan">
                    Duplikat: {{ number_format($kpi['duplicate_stores']) }}
                </span>
                <span class="badge badge-xs badge-secondary font-bold text-white" title="Titik bergeser dari titik sah">
                    Drift: {{ number_format($kpi['drift_stores']) }}
                </span>
            </div>
        </div>
    </div>

    {{-- FILTER TOOLBAR SECTION --}}
    <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col gap-2 shrink-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
            {{-- Search Box --}}
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari wilayah / cabang / distributor..." 
                    class="input input-sm input-bordered w-full pl-8 text-xs font-medium"
                />
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50 absolute left-2.5 top-2.5" />
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-2.5 top-2.5 text-base-content/40 hover:text-base-content">
                        <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif
            </div>

            {{-- Region Filter --}}
            <div>
                <select wire:model.live="regionFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="">Semua Region</option>
                    @foreach($regions as $rCode => $rName)
                        <option value="{{ $rCode }}">{{ $rName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Area Filter --}}
            <div>
                <select wire:model.live="areaFilter" class="select select-sm select-bordered w-full text-xs font-medium" {{ empty($regionFilter) && count($areas) === 0 ? 'disabled' : '' }}>
                    <option value="">Semua Area</option>
                    @foreach($areas as $aCode => $aName)
                        <option value="{{ $aCode }}">{{ $aName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-2">
                <button 
                    type="button" 
                    wire:click="resetFilters" 
                    class="btn btn-sm btn-ghost text-xs text-error font-semibold hover:bg-error/10 gap-1"
                >
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    Reset
                </button>

                @if($canExport)
                    <button 
                        type="button" 
                        wire:click="export" 
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-outline btn-success text-xs font-bold gap-1.5 hover:!text-white"
                        title="Download Rekap Matriks ke File Excel (.xlsx)"
                    >
                        <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" wire:loading.remove wire:target="export" />
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export Excel
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- MAIN SUMMARY MATRIX CARD --}}
    <div class="flex-1 min-h-0 bg-base-100 rounded-xl shadow-xs border border-base-300 flex flex-col overflow-hidden">
        {{-- Table Scrollable Area --}}
        <div class="flex-1 overflow-auto">
            <table class="table table-xs table-zebra w-full text-xs whitespace-nowrap border-separate border-spacing-0">
                <thead 
                    x-data="{ row1Height: 31 }" 
                    x-init="$nextTick(() => { if ($refs.row1) row1Height = $refs.row1.offsetHeight; })" 
                    @resize.window="if ($refs.row1) row1Height = $refs.row1.offsetHeight"
                >
                    {{-- Row 1: Group Headers --}}
                    <tr x-ref="row1" class="bg-base-200 text-base-content/80 text-[11px] font-bold uppercase tracking-wider">
                        <th rowspan="2" class="sticky top-0 z-30 bg-base-200 w-10 text-center align-middle border-b border-r border-base-300 shadow-xs">No</th>
                        @if($groupBy === 'region')
                            <th rowspan="2" class="sticky top-0 z-30 bg-base-200 align-middle border-b border-r border-base-300">Region</th>
                        @elseif($groupBy === 'area')
                            <th rowspan="2" class="sticky top-0 z-30 bg-base-200 align-middle border-b border-r border-base-300">Region</th>
                            <th rowspan="2" class="sticky top-0 z-30 bg-base-200 align-middle border-b border-r border-base-300">Area</th>
                        @else
                            <th rowspan="2" class="sticky top-0 z-30 bg-base-200 align-middle border-b border-r border-base-300">Region</th>
                            <th rowspan="2" class="sticky top-0 z-30 bg-base-200 align-middle border-b border-r border-base-300">Area</th>
                            <th rowspan="2" class="sticky top-0 z-30 bg-base-200 align-middle border-b border-r border-base-300">Cabang / Distributor</th>
                        @endif
                        <th rowspan="2" class="sticky top-0 z-30 bg-base-300/80 text-center align-middle border-b border-r border-base-300">Total Toko</th>
                        <th colspan="2" class="sticky top-0 z-30 bg-base-200 text-center border-b border-r border-base-300 text-info">Kelengkapan Geotag</th>
                        <th colspan="4" class="sticky top-0 z-30 bg-base-200 text-center border-b border-r border-base-300 text-rose-700 dark:text-rose-400">Temuan Anomali & Kesiapan</th>
                        <th colspan="3" class="sticky top-0 z-30 bg-base-200 text-center border-b border-r border-base-300 text-success">Status Validasi</th>
                        <th rowspan="2" class="sticky top-0 z-30 bg-base-200 text-center align-middle border-b border-r border-base-300 w-28">Progress Validasi</th>
                        @if($groupBy === 'distributor')
                            <th rowspan="2" class="sticky top-0 z-30 bg-base-200 w-20 text-center align-middle border-b border-base-300">Aksi</th>
                        @endif
                    </tr>
                    {{-- Row 2: Sub-headers (Properly pinned beneath Row 1) --}}
                    <tr class="bg-base-200 text-base-content/70 text-[10px] font-bold uppercase">
                        <th class="sticky z-20 text-center bg-base-200 text-info/90 border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`">Ada Titik</th>
                        <th class="sticky z-20 text-center bg-base-200 text-base-content/60 border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`">Tanpa Titik</th>
                        <th class="sticky z-20 text-center bg-base-200 text-rose-600 border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`" title="Titik di Depo (<=100m)">Di Depo</th>
                        <th class="sticky z-20 text-center bg-base-200 text-amber-600 border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`" title="Duplikasi Koordinat Kembar di Lapangan">Duplikat</th>
                        <th class="sticky z-20 text-center bg-base-200 text-purple-600 border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`" title="Titik Bergeser (Drift)">Drift</th>
                        <th class="sticky z-20 text-center bg-base-200 text-emerald-600 font-bold border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`" title="Toko Bersih Siap Validasi">✅ Siap</th>
                        <th class="sticky z-20 text-center bg-base-200 text-success border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`">VALID</th>
                        <th class="sticky z-20 text-center bg-base-200 text-error border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`">TIDAK VALID</th>
                        <th class="sticky z-20 text-center bg-base-200 text-base-content/60 border-b border-r border-base-300" style="top: 31px;" :style="`top: ${row1Height}px;`">Belum</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totStores = 0;
                        $totHasCoord = 0;
                        $totNoCoord = 0;
                        $totAtDist = 0;
                        $totDup = 0;
                        $totDrift = 0;
                        $totNormal = 0;
                        $totValid = 0;
                        $totInvalid = 0;
                        $totUnvalidated = 0;
                    @endphp

                    @forelse($matrix as $idx => $row)
                        @php
                            $totStores += $row->total_stores;
                            $totHasCoord += $row->has_coordinate;
                            $totNoCoord += $row->no_coordinate;
                            $totAtDist += $row->at_distributor;
                            $totDup += $row->duplicate_stores;
                            $totDrift += $row->drift_stores;
                            $totNormal += $row->normal_stores;
                            $totValid += $row->valid_stores;
                            $totInvalid += $row->invalid_stores;
                            $totUnvalidated += $row->unvalidated_stores;

                            $pct = (float) $row->validation_progress_pct;
                        @endphp
                        <tr class="hover:bg-base-200/60 transition-colors">
                            <td class="text-center font-mono text-[11px] text-base-content/60 border-r border-base-200">
                                {{ $idx + 1 }}
                            </td>

                            {{-- Region --}}
                            <td class="font-semibold text-base-content border-r border-base-200">
                                {{ $row->region_name ?: '-' }}
                            </td>

                            {{-- Area --}}
                            @if($groupBy !== 'region')
                                <td class="text-base-content/80 border-r border-base-200">
                                    {{ $row->area_name ?: '-' }}
                                </td>
                            @endif

                            {{-- Distributor --}}
                            @if($groupBy === 'distributor')
                                <td class="border-r border-base-200">
                                    <div class="flex items-center gap-1.5">
                                        <span class="badge badge-ghost font-mono text-[10px] font-bold">{{ $row->kodecabang }}</span>
                                        <span class="font-bold text-base-content">{{ $row->distributor_name }}</span>
                                    </div>
                                </td>
                            @endif

                            {{-- Total Toko --}}
                            <td class="text-center font-mono font-bold text-base-content border-r border-base-300 bg-base-200/30">
                                {{ number_format($row->total_stores) }}
                            </td>

                            {{-- Ada Titik vs Tanpa Titik --}}
                            <td class="text-center font-mono text-info font-medium border-r border-base-200">
                                {{ number_format($row->has_coordinate) }}
                            </td>
                            <td class="text-center font-mono text-base-content/40 border-r border-base-300">
                                {{ number_format($row->no_coordinate) }}
                            </td>

                            {{-- Temuan Anomali --}}
                            <td class="text-center font-mono border-r border-base-200">
                                @if($row->at_distributor > 0)
                                    <span class="badge badge-error badge-xs text-white font-bold">{{ number_format($row->at_distributor) }}</span>
                                @else
                                    <span class="text-base-content/20">-</span>
                                @endif
                            </td>
                            <td class="text-center font-mono border-r border-base-200">
                                @if($row->duplicate_stores > 0)
                                    <span class="badge badge-warning badge-xs font-bold text-amber-950">{{ number_format($row->duplicate_stores) }}</span>
                                @else
                                    <span class="text-base-content/20">-</span>
                                @endif
                            </td>
                            <td class="text-center font-mono border-r border-base-200">
                                @if($row->drift_stores > 0)
                                    <span class="badge badge-secondary badge-xs font-bold">{{ number_format($row->drift_stores) }}</span>
                                @else
                                    <span class="text-base-content/20">-</span>
                                @endif
                            </td>
                            <td class="text-center font-mono border-r border-base-300">
                                <span class="text-success font-bold">{{ number_format($row->normal_stores) }}</span>
                            </td>

                            {{-- Status Validasi --}}
                            <td class="text-center font-mono border-r border-base-200">
                                @if($row->valid_stores > 0)
                                    <span class="badge badge-success badge-xs font-bold text-white">{{ number_format($row->valid_stores) }}</span>
                                @else
                                    <span class="text-base-content/20">-</span>
                                @endif
                            </td>
                            <td class="text-center font-mono border-r border-base-200">
                                @if($row->invalid_stores > 0)
                                    <span class="badge badge-error badge-xs font-bold text-white">{{ number_format($row->invalid_stores) }}</span>
                                @else
                                    <span class="text-base-content/20">-</span>
                                @endif
                            </td>
                            <td class="text-center font-mono text-base-content/60 border-r border-base-300">
                                {{ number_format($row->unvalidated_stores) }}
                            </td>

                            {{-- Progress Validasi Bar --}}
                            <td class="text-center border-r border-base-300 px-3">
                                <div class="flex items-center gap-2 justify-center">
                                    <div class="w-16 bg-base-300 rounded-full h-2 overflow-hidden">
                                        <div 
                                            class="h-full rounded-full {{ $pct >= 80 ? 'bg-success' : ($pct >= 40 ? 'bg-info' : 'bg-warning') }}" 
                                            style="width: {{ min(100, $pct) }}%"
                                        ></div>
                                    </div>
                                    <span class="font-mono text-[11px] font-bold text-base-content/70 w-9 text-right">
                                        {{ $pct }}%
                                    </span>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            @if($groupBy === 'distributor')
                                <td class="text-center">
                                    <a 
                                        href="{{ route('others.validasi-geotag', ['distributorFilter' => $row->kodecabang]) }}" 
                                        class="btn btn-xs btn-primary gap-1 shadow-xs hover:scale-105"
                                        title="Buka daftar toko distributor {{ $row->kodecabang }} di tab Detail"
                                        wire:navigate
                                    >
                                        <x-heroicon-s-arrow-top-right-on-square class="w-3 h-3" />
                                        Detail
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $groupBy === 'distributor' ? 15 : ($groupBy === 'area' ? 14 : 13) }}" class="text-center py-10 text-base-content/50">
                                <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-base-content/30 mb-2" />
                                <div class="font-semibold text-sm">Tidak ada data ringkasan yang sesuai</div>
                                <div class="text-xs text-base-content/40 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter wilayah.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- TFOOT TOTALS ROW --}}
                @if(count($matrix) > 0)
                    @php
                        $totValidated = $totValid + $totInvalid;
                        $totalProgressPct = $totStores > 0 ? round(($totValidated / $totStores) * 100, 1) : 0;
                    @endphp
                    <tfoot class="bg-base-300 text-base-content font-bold text-xs uppercase border-t-2 border-base-300 sticky bottom-0 z-20 shadow-lg">
                        <tr>
                            <td colspan="{{ $groupBy === 'distributor' ? 4 : ($groupBy === 'area' ? 3 : 2) }}" class="sticky bottom-0 z-20 bg-base-300 text-right pr-4 font-black border-r border-base-300">
                                TOTAL KESELURUHAN ({{ count($matrix) }} {{ $groupBy === 'distributor' ? 'Distributor' : ($groupBy === 'area' ? 'Area' : 'Region') }}):
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono font-black text-sm text-primary border-r border-base-300">
                                {{ number_format($totStores) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-info border-r border-base-200">
                                {{ number_format($totHasCoord) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-base-content/50 border-r border-base-300">
                                {{ number_format($totNoCoord) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-rose-600 border-r border-base-200">
                                {{ number_format($totAtDist) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-amber-600 border-r border-base-200">
                                {{ number_format($totDup) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-purple-600 border-r border-base-200">
                                {{ number_format($totDrift) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-success font-black border-r border-base-300">
                                {{ number_format($totNormal) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-success border-r border-base-200">
                                {{ number_format($totValid) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-error border-r border-base-200">
                                {{ number_format($totInvalid) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center font-mono text-base-content/60 border-r border-base-300">
                                {{ number_format($totUnvalidated) }}
                            </td>
                            <td class="sticky bottom-0 z-20 bg-base-300 text-center border-r border-base-300 px-3">
                                <div class="flex items-center gap-2 justify-center">
                                    <div class="w-16 bg-base-100 rounded-full h-2.5 overflow-hidden">
                                        <div 
                                            class="h-full rounded-full bg-primary" 
                                            style="width: {{ min(100, $totalProgressPct) }}%"
                                        ></div>
                                    </div>
                                    <span class="font-mono text-xs font-black text-primary w-9 text-right">
                                        {{ $totalProgressPct }}%
                                    </span>
                                </div>
                            </td>
                            @if($groupBy === 'distributor')
                                <td class="sticky bottom-0 z-20 bg-base-300"></td>
                            @endif
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Footer Info --}}
        <div class="shrink-0 p-2.5 bg-base-100 border-t border-base-300 flex items-center justify-between text-xs text-base-content/60">
            <div>
                Menampilkan <b>{{ number_format(count($matrix)) }}</b> baris ringkasan ({{ $groupBy === 'distributor' ? 'Per Distributor' : ($groupBy === 'area' ? 'Per Area' : 'Per Region') }})
            </div>
            <div class="text-[11px] text-base-content/50">
                *Klik tombol <b>Detail</b> pada baris distributor untuk langsung membuka dan memvalidasi toko-toko di cabang tersebut.
            </div>
        </div>
    </div>
</div>
