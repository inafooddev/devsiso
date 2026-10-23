<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('se_reason_show_kpi') !== null 
             ? localStorage.getItem('se_reason_show_kpi') === 'true' 
             : (window.innerWidth >= 1024),
         autoHidden: (localStorage.getItem('se_reason_show_kpi') === null && window.innerWidth < 1024),
         wasOneRow: (window.innerWidth >= 1024),
         
         isOneRow() {
             if (this.$refs.kpiGrid && this.showKpi) {
                 const cards = Array.from(this.$refs.kpiGrid.children);
                 if (cards.length > 1) {
                     return cards[0].offsetTop === cards[cards.length - 1].offsetTop;
                 }
             }
             return window.matchMedia('(min-width: 1024px)').matches;
         },
         
         toggleKpi() {
             this.showKpi = !this.showKpi;
             this.autoHidden = false;
             localStorage.setItem('se_reason_show_kpi', this.showKpi);
             this.$nextTick(() => {
                 this.wasOneRow = this.isOneRow();
             });
         },
         
         checkKpiWrap() {
             const nowOneRow = this.isOneRow();
             
             // Logika: Jika dari sebelumnya 1 baris berubah menjadi 2 baris (atau lebih) -> otomatis hide kpi card
             if (this.wasOneRow && !nowOneRow) {
                 if (this.showKpi) {
                     this.showKpi = false;
                     this.autoHidden = true;
                     localStorage.setItem('se_reason_show_kpi', 'false');
                 }
             } 
             // Jika kembali ke 1 baris dan sebelumnya disembunyikan otomatis oleh sistem -> tampilkan kembali
             else if (!this.wasOneRow && nowOneRow && this.autoHidden) {
                 this.showKpi = true;
                 this.autoHidden = false;
                 localStorage.setItem('se_reason_show_kpi', 'true');
             }
             
             this.wasOneRow = nowOneRow;
         },
         
         init() {
             this.wasOneRow = this.isOneRow();
             
             const onResize = () => {
                 this.checkKpiWrap();
             };
             
             window.addEventListener('resize', onResize);
             
             if (window.ResizeObserver && this.$refs.kpiGrid) {
                 const ro = new ResizeObserver(() => {
                     if (this.showKpi) {
                         onResize();
                     }
                 });
                 ro.observe(this.$refs.kpiGrid);
             }
         }
     }">
    <x-slot name="title">Summary Reason Kunjungan SE</x-slot>

    {{-- Standar Tab Menu --}}
    @php
        $tabParams = array_filter([
            'region' => $appliedRegion ?: $selectedRegion,
            'area' => $appliedArea ?: $selectedArea,
            'distributor' => $appliedDistributor ?: $selectedDistributor,
            'startDate' => $appliedStartDate ?: $startDate,
            'endDate' => $appliedEndDate ?: $endDate,
        ], fn($v) => !empty($v));
    @endphp
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.index', $tabParams)" :navigate="true">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.summary', $tabParams)" :navigate="true">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.reason', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-list class="w-4 h-4 inline-block mr-1" /> Summary Reason
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.trend', $tabParams)" :navigate="true">
            <x-heroicon-o-arrow-trending-up class="w-4 h-4 inline-block mr-1" /> Trend Bulanan
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.findings', $tabParams)" :navigate="true">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block mr-1 text-warning" /> Temuan
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.tracking', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-check class="w-4 h-4 inline-block mr-1 text-primary" /> Tracking Toko
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.raport', $tabParams)" :navigate="true">
            <x-heroicon-o-academic-cap class="w-4 h-4 inline-block mr-1 text-emerald-500" /> Raport SE
        </x-ui.tab-item>

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                @if($appliedRegion && !empty($kpis->total_cases))
                    <span class="badge badge-primary text-primary-content font-bold text-xs gap-1 py-2.5 px-3 shadow-2xs">
                        <x-heroicon-s-clipboard-document-list class="w-4 h-4" />
                        {{ number_format($kpis->total_cases) }} Kasus
                    </span>
                @endif

                <button type="button" 
                    @click="toggleKpi()" 
                    class="btn btn-xs sm:btn-sm rounded-xl gap-1.5 transition-all font-semibold border shadow-2xs cursor-pointer select-none"
                    :class="showKpi ? 'btn-ghost text-base-content/70 hover:text-base-content border-base-300' : 'btn-primary text-white shadow-sm border-primary'"
                    :title="showKpi ? 'Sembunyikan KPI Card agar ruang tabel lebih lega' : 'Tampilkan kembali KPI Card'">
                    <template x-if="showKpi">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-m-eye-slash class="w-4 h-4 text-base-content/70" />
                            <span class="text-xs hidden sm:inline">Sembunyikan KPI</span>
                        </span>
                    </template>
                    <template x-if="!showKpi">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-m-chart-bar class="w-4 h-4 text-white" />
                            <span class="text-xs">Tampilkan KPI</span>
                        </span>
                    </template>
                </button>
            </div>
        </x-slot>
    </x-ui.tab-menu>

    {{-- KPI Cards Section (4 Cards) --}}
    <div x-show="showKpi"
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 -translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 -translate-y-2" 
         x-ref="kpiGrid"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 md:gap-3.5 shrink-0 mb-3 md:mb-4">
        {{-- Card 1: Total Kasus Reason --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-primary/40 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Total Kasus Alasan</span>
                <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-clipboard-document-list class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-base-content font-mono">
                    {{ number_format($kpis->total_cases ?? 0) }}
                </span>
                <span class="text-[0.625rem] font-semibold text-base-content/50 uppercase">Kasus</span>
            </div>
            <div class="mt-1 text-[0.625rem] text-base-content/50 truncate">
                Tipe: <span class="font-bold text-primary">{{ $appliedReasonType === 'BUY' ? 'Tidak Beli' : ($appliedReasonType === 'VISIT' ? 'Tidak Dikunjungi' : 'Semua Tipe') }}</span>
            </div>
        </div>

        {{-- Card 2: Total SE Terdampak --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-secondary/40 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Total SE Terdampak</span>
                <div class="w-6 h-6 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                    <x-heroicon-s-user-group class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-base-content font-mono">
                    {{ number_format($kpis->total_se ?? 0) }}
                </span>
                <span class="text-[0.625rem] font-semibold text-base-content/50 uppercase">Salesman</span>
            </div>
            <div class="mt-1 text-[0.625rem] text-base-content/50 truncate">
                Memiliki catatan alasan di periode ini
            </div>
        </div>

        {{-- Card 3: Alasan Terbanyak (#1) --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-warning/40 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Alasan Terbanyak (#1)</span>
                <div class="w-6 h-6 rounded-lg bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-fire class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-lg sm:text-xl font-black tracking-tight text-base-content truncate pr-1" title="{{ $kpis->top_reason_name ?? '-' }}">
                    {{ $kpis->top_reason_name ?? '-' }}
                </span>
                <span class="text-xs font-mono font-bold text-warning shrink-0">
                    {{ number_format($kpis->top_reason_count ?? 0) }}x
                </span>
            </div>
            <div class="mt-1 text-[0.625rem] text-base-content/50 truncate">
                Frekuensi kemunculan tertinggi
            </div>
        </div>

        {{-- Card 4: Rata-rata per SE --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-info/40 transition-colors">
            <div class="flex items-center justify-between text-[0.625rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Rata-rata Kasus per SE</span>
                <div class="w-6 h-6 rounded-lg bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-calculator class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-base-content font-mono">
                    {{ $kpis->avg_per_se ?? 0 }}
                </span>
                <span class="text-[0.625rem] font-semibold text-base-content/50 uppercase">Kasus / SE</span>
            </div>
            <div class="mt-1 text-[0.625rem] text-base-content/50 truncate">
                Rata-rata kejadian per salesman
            </div>
        </div>
    </div>

    {{-- Main Card (Tabel Summary Reason SE) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar (Hanya Filter & Action Buttons agar hemat ruang layar) --}}
        <x-ui.filter-toolbar>
            {{-- 1. Kapsul Hierarki Wilayah (Region & Area Native + Distributor Micro-Searchable Popover) --}}
            <x-ui.filter-breadcrumb>
                {{-- Region Segment (jika multi-region/nasional) --}}
                @if(count($regions) > 1 || $accessLevel === 'nasional')
                    <x-ui.filter-select 
                        wire:model.live="selectedRegion" 
                        :options="$regions" 
                        valueField="region_code" 
                        labelField="region_name" 
                        placeholder="Pilih Region" 
                        icon="globe" />
                @endif

                {{-- Area Segment --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <x-ui.filter-select 
                        wire:model.live="selectedArea" 
                        :options="$areas" 
                        valueField="area_code" 
                        labelField="area_name" 
                        placeholder="Semua Area" 
                        icon="map-pin" 
                        :disabled="empty($selectedRegion)" />
                @endif

                {{-- Distributor Segment (Micro-Searchable Popover) --}}
                <x-ui.filter-searchable 
                    wire:key="dist-capsule-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}"
                    wire:model.live="selectedDistributor" 
                    :options="$distributors" 
                    valueField="distributor_code" 
                    labelField="distributor_name" 
                    placeholder="Semua Distributor" 
                    :allowAll="true"
                    searchPlaceholder="Cari nama distributor / kode..."
                    icon="building" 
                    :disabled="empty($selectedRegion)" />
            </x-ui.filter-breadcrumb>

            {{-- 2. Kapsul Periode Pintar (Smart Date Range Filter) --}}
            <x-ui.filter-date-range 
                start="startDate" 
                end="endDate" 
                :maxDays="31" />

            {{-- 3. Search Box SE / Distributor --}}
            <div class="relative w-full sm:w-auto">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Cari SE / Distributor..."
                       class="input input-xs h-7 rounded-lg bg-base-100 border border-base-300 text-xs pl-7 pr-2 w-full sm:w-44 focus:outline-none focus:border-primary shadow-2xs" />
                <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 absolute left-2 top-1/2 -translate-y-1/2 text-base-content/40 pointer-events-none" />
            </div>

            <x-slot name="actions">
                @php $canApply = !empty($selectedRegion); @endphp
                <x-ui.filter-actions 
                    applyAction="applyFilter" 
                    resetAction="resetFilters" 
                    :canApply="$canApply" 
                    disabledTip="Pilih minimal Region terlebih dahulu">
                    
                    @if($canExport)
                        <button type="button" 
                            wire:click="exportExcel" 
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer"
                            title="Download Summary Reason ke Excel (.xlsx)">
                            <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1.5">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span>Excel</span>
                            </span>
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </x-ui.filter-actions>
            </x-slot>
        </x-ui.filter-toolbar>

        {{-- Reason Type Filter Pill Bar --}}
        <div class="px-3 md:px-5 py-2 bg-base-200/50 border-b border-base-300 shrink-0 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/70 flex items-center gap-1">
                    <x-heroicon-m-funnel class="w-3.5 h-3.5 text-primary" /> Tipe Alasan:
                </span>
                <div class="inline-flex rounded-xl p-0.5 bg-base-100 border border-base-300 shadow-2xs gap-0.5 text-xs">
                    {{-- Button 1: BUY = Not Buy (Tidak Beli) --}}
                    <button type="button"
                            wire:click="setReasonType('BUY')"
                            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer {{ $appliedReasonType === 'BUY' ? 'bg-amber-500 text-white shadow-xs font-bold' : 'text-base-content/70 hover:text-base-content hover:bg-base-200' }}">
                        <span>🛒</span>
                        <span>Tidak Beli (Not Buy)</span>
                        @if($appliedReasonType === 'BUY')
                            <span class="badge badge-xs bg-white text-amber-700 font-mono font-bold">{{ count($columns) }}</span>
                        @endif
                    </button>

                    {{-- Button 2: VISIT = Not Visit (Tidak Dikunjungi) --}}
                    <button type="button"
                            wire:click="setReasonType('VISIT')"
                            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer {{ $appliedReasonType === 'VISIT' ? 'bg-rose-500 text-white shadow-xs font-bold' : 'text-base-content/70 hover:text-base-content hover:bg-base-200' }}">
                        <span>🚫</span>
                        <span>Tidak Dikunjungi (Not Visit)</span>
                        @if($appliedReasonType === 'VISIT')
                            <span class="badge badge-xs bg-white text-rose-700 font-mono font-bold">{{ count($columns) }}</span>
                        @endif
                    </button>

                    {{-- Button 3: ALL = Semua Tipe --}}
                    <button type="button"
                            wire:click="setReasonType('ALL')"
                            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer {{ $appliedReasonType === 'ALL' ? 'bg-primary text-white shadow-xs font-bold' : 'text-base-content/70 hover:text-base-content hover:bg-base-200' }}">
                        <span>📋</span>
                        <span>Semua Tipe</span>
                        @if($appliedReasonType === 'ALL')
                            <span class="badge badge-xs bg-white text-primary font-mono font-bold">{{ count($columns) }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="text-[0.625rem] text-base-content/60 italic hidden sm:block">
                * Kolom alasan tersusun otomatis berdasarkan frekuensi terbanyak di rentang tanggal & wilayah terpilih.
            </div>
        </div>

        {{-- Body Card (Tabel Summary Reason SE) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <div wire:loading.flex wire:target="applyFilter, resetFilters, previousMonth, nextMonth, setReasonType, sortBy, setDatePreset"
                 class="absolute inset-0 bg-base-100/70 backdrop-blur-2xs z-30 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 bg-base-100 px-5 py-4 rounded-xl shadow-lg border border-base-300">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-semibold text-base-content">Memuat Analisa Reason SE...</span>
                </div>
            </div>

            @if(empty($appliedRegion))
                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-base-content/40">
                    <x-heroicon-o-funnel class="w-12 h-12 mb-2 text-base-content/30" />
                    <h3 class="text-sm font-bold text-base-content/80">Silakan Tentukan Filter</h3>
                    <p class="text-xs text-base-content/60 max-w-sm mt-0.5">
                        Pilih minimal Region (bisa pilih Semua Area atau Area tertentu), lalu klik tombol <span class="text-primary font-bold">Terapkan</span> untuk menampilkan summary alasan kunjungan Sales Exclusive.
                    </p>
                </div>
            @elseif($rows->isEmpty())
                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-base-content/40">
                    <x-heroicon-o-inbox class="w-12 h-12 mb-2 text-base-content/30" />
                    <h3 class="text-sm font-bold text-base-content/80">Tidak Ada Data Ditemukan</h3>
                    <p class="text-xs text-base-content/60 max-w-sm mt-0.5">
                        Tidak ada catatan alasan kunjungan SE untuk filter dan rentang tanggal yang dipilih.
                    </p>
                </div>
            @else
                <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs border-collapse [&_tbody_td]:py-1 [&_tbody_td]:px-2 [&_tbody_tr]:h-7">
                    {{-- THEAD Sticky --}}
                    <thead class="sticky top-0 z-20 bg-base-300 text-base-content shadow-sm [&_th]:py-2 [&_th]:px-2.5">
                        <tr class="[&>th]:align-middle text-xs uppercase tracking-wider border-b-2 border-base-content/20">
                            {{-- # --}}
                            <th class="w-10 text-center font-bold">#</th>

                            {{-- Distributor & Area (Default Sort: Area, then Distributor) --}}
                            <th class="min-w-40 text-left font-bold cursor-pointer select-none hover:text-primary transition-colors" wire:click="sortBy('territory')">
                                <div class="flex items-center gap-1">
                                    <span>Distributor / Area</span>
                                    @if($sortField === 'territory' || $sortField === 'distributor_name' || $sortField === 'area_name')
                                        <span class="text-primary font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>

                            {{-- Salesman (SE) --}}
                            <th class="min-w-44 text-left font-bold cursor-pointer select-none hover:text-primary transition-colors" wire:click="sortBy('salesman_name')">
                                <div class="flex items-center gap-1">
                                    <span>Salesman (SE)</span>
                                    @if($sortField === 'salesman_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>

                            {{-- Dynamic Reason Columns (Sorted by overall frequency desc) --}}
                            @foreach($columns as $col)
                                <th class="text-right font-bold cursor-pointer select-none hover:text-primary transition-colors px-2.5 border-l border-base-content/10"
                                    wire:click="sortBy('{{ addslashes($col->raw_desc) }}')"
                                    title="{{ $col->raw_desc }} (Total: {{ number_format($col->total) }})">
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="max-w-[140px] truncate">{{ $col->short_desc }}</span>
                                        <span class="text-[0.625rem] font-mono px-1 rounded bg-base-content/10 text-base-content/70">
                                            {{ number_format($col->total) }}
                                        </span>
                                        @if($sortField === $col->raw_desc)
                                            <span class="text-primary font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach

                            {{-- Total Kasus --}}
                            <th class="w-24 text-right font-black cursor-pointer select-none hover:text-primary transition-colors px-3 border-l-2 border-base-content/20"
                                wire:click="sortBy('total_reason')"
                                title="Total Kasus Alasan SE">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Total Kasus</span>
                                    @if($sortField === 'total_reason') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>

                            {{-- Aksi --}}
                            <th class="w-14 text-center font-bold border-l border-base-content/10">Aksi</th>
                        </tr>
                    </thead>

                    {{-- TBODY --}}
                    <tbody class="divide-y divide-base-200">
                        @foreach($rows as $index => $row)
                            <tr class="hover:bg-primary/5 transition-colors">
                                {{-- # --}}
                                <td class="text-center font-mono text-xs text-base-content/50">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- Distributor / Area --}}
                                <td class="text-left font-medium text-base-content">
                                    <div class="font-bold text-xs leading-tight text-base-content">{{ $row->distributor_name }}</div>
                                    <div class="text-[0.625rem] text-base-content/50 uppercase tracking-wider leading-tight mt-0.5">{{ $row->area_name }}</div>
                                </td>

                                {{-- Salesman --}}
                                <td class="text-left">
                                    <div class="font-bold text-xs text-base-content leading-tight">{{ $row->salesman_name }}</div>
                                    <div class="text-[0.625rem] font-mono text-base-content/60 leading-tight mt-0.5">{{ $row->salesman_code }}</div>
                                </td>

                                {{-- Dynamic Reason Count Cells --}}
                                @foreach($columns as $col)
                                    @php
                                        $cnt = $row->reasons[$col->raw_desc] ?? 0;
                                        $isMax = ($cnt > 0 && $cnt === $row->max_count);
                                    @endphp
                                    <td class="text-right whitespace-nowrap px-2.5 border-l border-base-200">
                                        @if($cnt > 0)
                                            <button type="button"
                                                    wire:click="openDetailModal('{{ $row->salesman_code }}', '{{ addslashes($col->raw_desc) }}')"
                                                    class="inline-flex items-center justify-end px-2 py-0.5 rounded-md font-mono text-xs transition-all {{ $isMax ? 'font-black bg-amber-200/80 dark:bg-amber-900/60 text-slate-950 dark:text-white border border-amber-400 dark:border-amber-600 shadow-2xs hover:bg-amber-300 dark:hover:bg-amber-800' : 'font-semibold text-slate-800 dark:text-slate-200 hover:bg-base-200' }}"
                                                    title="Klik untuk melihat detail toko dengan alasan ini{{ $isMax ? ' (Alasan terbanyak SE ini)' : '' }}">
                                                <span>{{ number_format($cnt) }}</span>
                                            </button>
                                        @else
                                            <span class="text-base-content/25 font-mono text-xs">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Total Kasus --}}
                                <td class="text-right whitespace-nowrap px-3 border-l-2 border-base-content/20">
                                    <button type="button"
                                            wire:click="openDetailModal('{{ $row->salesman_code }}')"
                                            class="inline-flex items-center justify-end gap-1 font-mono text-xs font-black px-2 py-0.5 rounded-md bg-base-200/80 text-base-content hover:bg-primary/20 hover:text-primary transition-all"
                                            title="Lihat semua alasan untuk salesman ini">
                                        {{ number_format($row->total_reason) }}
                                    </button>
                                </td>

                                {{-- Aksi: Tombol Detail Modal --}}
                                <td class="text-center whitespace-nowrap border-l border-base-200">
                                    <button type="button"
                                            wire:click="openDetailModal('{{ $row->salesman_code }}')"
                                            class="btn btn-ghost btn-xs btn-square text-primary hover:bg-primary/10"
                                            title="Buka Rekap Detail Toko SE: {{ $row->salesman_name }}">
                                        <x-heroicon-m-eye class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Sticky Grand Total Footer --}}
                    @if(!empty($totals))
                        <tfoot class="bg-slate-950 dark:bg-black text-white border-t-2 border-primary shadow-[0_-4px_12px_rgba(0,0,0,0.35)] sticky bottom-0 z-20 [&_th]:py-2 [&_th]:px-2.5 divide-x divide-slate-800">
                            <tr class="font-bold text-white [&>th]:align-middle bg-slate-950 dark:bg-black h-8">
                                <th colspan="3" class="text-left pl-3 whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="text-blue-300 font-extrabold tracking-wide">TOTAL {{ number_format($rows->count()) }} SE</span>
                                    <span class="font-normal text-xs text-slate-400 ml-1">({{ count($columns) }} Alasan Berbeda)</span>
                                </th>

                                {{-- Dynamic Reason Totals --}}
                                @foreach($columns as $col)
                                    <th class="text-right whitespace-nowrap font-mono text-xs text-blue-200 bg-slate-950 dark:bg-black px-2.5">
                                        {{ number_format($totals[$col->raw_desc] ?? 0) }}
                                    </th>
                                @endforeach

                                {{-- Grand Total Kasus --}}
                                <th class="text-right whitespace-nowrap font-mono text-sm text-emerald-400 font-extrabold px-3 bg-slate-950 dark:bg-black">
                                    {{ number_format($totals['__grand_total__'] ?? 0) }}
                                </th>

                                {{-- Aksi Blank --}}
                                <th class="text-center whitespace-nowrap font-mono text-slate-500 bg-slate-950 dark:bg-black">-</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            @endif
        </div>
    </div>

    {{-- Detail Modal Toko / Kunjungan Per SE --}}
    <x-ui.modal id="detail-reason-modal"
                title="Daftar Kunjungan & Alasan: {{ $modalSalesmanName }}"
                icon="clipboard-document-list"
                size="7xl"
                :open="$showDetailModal"
                wire:close="closeDetailModal">
        
        <div class="space-y-4">
            {{-- Modal Header Subtitle / Filter Badges --}}
            <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-base-200/50 border border-base-300">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Salesman:</span>
                    <span class="badge badge-primary text-primary-content font-bold font-mono text-xs">{{ $modalSalesmanCode }}</span>
                    <span class="font-bold text-xs text-base-content">{{ $modalSalesmanName }}</span>

                    @if($modalFilterReason)
                        <div class="hidden sm:block w-px h-4 bg-base-300 mx-1"></div>
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Filter Alasan:</span>
                        <span class="badge bg-amber-300 text-slate-950 border-amber-400 font-bold text-xs">
                            {{ $modalFilterReason }}
                        </span>
                        <button type="button" wire:click="openDetailModal('{{ $modalSalesmanCode }}')" class="btn btn-ghost btn-xs text-xs text-primary underline">
                            Tampilkan Semua Alasan
                        </button>
                    @endif
                </div>

                <div class="text-xs font-mono font-bold text-base-content/70">
                    Total: <span class="text-primary">{{ count($modalStoreList) }}</span> Toko
                </div>
            </div>

            {{-- Store Table --}}
            <div class="overflow-x-auto max-h-[60vh] border border-base-300 rounded-xl">
                <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs">
                    <thead class="sticky top-0 bg-base-300 text-base-content z-10 font-bold uppercase text-[0.625rem]">
                        <tr>
                            <th class="w-10 text-center">#</th>
                            <th class="w-24 text-left">Tanggal</th>
                            <th class="w-28 text-center">Jam Kunjung</th>
                            <th class="w-28 text-left">Kode Toko</th>
                            <th class="text-left min-w-[200px]">Nama Toko</th>
                            <th class="w-20 text-center">Rute PJP</th>
                            <th class="w-24 text-center">Kunjungan</th>
                            <th class="w-24 text-center">Tipe Alasan</th>
                            <th class="min-w-[220px] text-left">Alasan</th>
                            <th class="min-w-[200px] text-left">Catatan Salesman</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($modalStoreList as $idx => $st)
                            <tr class="hover:bg-primary/5">
                                <td class="text-center font-mono text-base-content/50">{{ $idx + 1 }}</td>
                                <td class="font-mono text-base-content">
                                    {{ \Carbon\Carbon::parse($st->tanggal)->format('d/m/Y') }}
                                </td>
                                <td class="text-center font-mono text-base-content/70 text-[0.625rem]">
                                    @if(!empty($st->time_in) && !empty($st->time_out))
                                        {{ substr($st->time_in, 0, 5) }} - {{ substr($st->time_out, 0, 5) }}
                                    @elseif(!empty($st->time_in))
                                        {{ substr($st->time_in, 0, 5) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="font-mono font-bold text-base-content">{{ $st->custno }}</td>
                                <td class="font-semibold text-base-content">{{ $st->custname }}</td>
                                <td class="text-center">
                                    @if(in_array(strtoupper($st->flag_pjp ?? ''), ['R', 'Y']) || (int)($st->flag_pjp ?? 0) === 1)
                                        <span class="badge badge-xs badge-info font-bold">PJP</span>
                                    @else
                                        <span class="badge badge-xs badge-ghost font-bold text-base-content/60">Extra</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(strtoupper($st->flag_visit ?? '') === 'Y' || (int)($st->flag_visit ?? 0) === 1)
                                        <span class="badge badge-xs badge-success text-white font-bold">Visited</span>
                                    @else
                                        <span class="badge badge-xs badge-error text-white font-bold">Unvisited</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(strtoupper($st->reason_type ?? '') === 'BUY')
                                        <span class="badge badge-xs bg-amber-500 text-white font-bold">Not Buy</span>
                                    @elseif(strtoupper($st->reason_type ?? '') === 'VISIT')
                                        <span class="badge badge-xs bg-rose-500 text-white font-bold">Not Visit</span>
                                    @else
                                        <span class="badge badge-xs badge-neutral">{{ $st->reason_type }}</span>
                                    @endif
                                </td>
                                <td class="font-medium text-base-content max-w-xs truncate" title="{{ $st->reason_desc }}">
                                    {{ $st->reason_desc }}
                                </td>
                                <td class="text-base-content/70 italic max-w-xs truncate" title="{{ $st->reason_note }}">
                                    {{ $st->reason_note ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-base-content/40">
                                    Tidak ada data kunjungan toko untuk alasan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between w-full">
                <span class="text-xs text-base-content/50">Maksimal 300 data kunjungan terbaru ditampilkan di modal.</span>
                <button type="button" wire:click="closeDetailModal" class="btn btn-sm btn-ghost">
                    Tutup
                </button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
</div>
