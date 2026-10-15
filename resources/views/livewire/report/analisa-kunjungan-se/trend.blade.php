<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full overflow-y-auto pr-1 pb-16 space-y-4"
     x-data="{
         chartTrend: null,
         monthlyData: @js($trendData),

         init() {
             this.$nextTick(() => {
                 this.renderCharts();
             });
         },

         onDataUpdated(e) {
             const trend = (e.detail && e.detail.trend) 
                 ? e.detail.trend 
                 : (e.detail && e.detail[0] && e.detail[0].trend ? e.detail[0].trend : null);
             if (trend) {
                 this.monthlyData = trend;
             }
             this.$nextTick(() => {
                 this.renderCharts();
             });
         },

         destroyCharts() {
             if (this.chartTrend) {
                 try { this.chartTrend.destroy(); } catch(e){}
                 this.chartTrend = null;
             }
             const elTrend = document.getElementById('chartMainTrend');
             if (elTrend) {
                 elTrend.innerHTML = '';
             }
         },

         renderCharts() {
             const mData = this.monthlyData || [];
             const isDark = document.documentElement.getAttribute('data-theme') === 'neon-dark';
             const textColor = isDark ? '#cbd5e1' : '#475569';
             const gridColor = isDark ? '#334155' : '#e2e8f0';

             const categories = mData.map(d => d.short_name);
             const acPctData = mData.map(d => d.ac_percent);
             const ecPctData = mData.map(d => d.ec_percent);
             const ecPcPctData = mData.map(d => d.ec_pc_percent);

             // Calculate dynamic max (min 100%) in case AC exceeds PC
             const validPcts = [...acPctData, ...ecPctData, ...ecPcPctData].filter(v => typeof v === 'number' && !isNaN(v));
             const peakPct = validPcts.length > 0 ? Math.max(...validPcts) : 100;
             const yAxisMax = Math.max(100, Math.ceil(peakPct / 10) * 10);

             const newSeries = [
                 { name: '% Compliance (AC/PC)', data: acPctData },
                 { name: '% Strike Rate (EC/AC)', data: ecPctData },
                 { name: '% Konversi Rencana (EC/PC)', data: ecPcPctData }
             ];

             const elTrend = document.getElementById('chartMainTrend');
             if (!elTrend) return;

             // Jika instance chart sudah ada, cukup update opsi & series agar tidak terjadi dobel render / canvas bertumpuk
             if (this.chartTrend) {
                 try {
                     this.chartTrend.updateOptions({
                         xaxis: { categories: categories },
                         yaxis: { max: yAxisMax }
                     }, false, false);
                     this.chartTrend.updateSeries(newSeries, true);
                     return;
                 } catch (err) {
                     console.warn('Gagal update chart, render ulang dari awal', err);
                     this.destroyCharts();
                 }
             }

             // Inisialisasi awal atau render ulang bersih
             elTrend.innerHTML = '';
             const self = this;

             this.chartTrend = new ApexCharts(elTrend, {
                 chart: {
                     type: 'line',
                     height: 400,
                     toolbar: { show: true, tools: { download: true, zoom: true, pan: true, reset: true } },
                     dropShadow: { enabled: true, top: 2, left: 1, blur: 4, opacity: 0.12 },
                     background: 'transparent'
                 },
                 theme: { mode: isDark ? 'dark' : 'light' },
                 colors: ['#0284c7', '#10b981', '#8b5cf6'],
                 stroke: { curve: 'smooth', width: [3.5, 3.5, 3.5] },
                 series: newSeries,
                 markers: { size: 5, hover: { size: 7 } },
                 dataLabels: {
                     enabled: true,
                     formatter: function(val) {
                         return (val !== null && val !== undefined) ? val + '%' : '';
                     },
                     offsetY: -6,
                     style: {
                         fontSize: '10px',
                         fontWeight: 700,
                         fontFamily: 'Inter, sans-serif'
                     },
                     background: {
                         enabled: true,
                         foreColor: '#ffffff',
                         padding: 3,
                         borderRadius: 4,
                         borderWidth: 0,
                         opacity: 0.95,
                         dropShadow: {
                             enabled: false
                         }
                     }
                 },
                 xaxis: {
                     categories: categories,
                     labels: { style: { colors: textColor, fontWeight: 600 } }
                 },
                 yaxis: {
                     max: yAxisMax,
                     min: 0,
                     tickAmount: 5,
                     labels: {
                         style: { colors: textColor, fontWeight: 600 },
                         formatter: (v) => v !== null && v !== undefined ? v + '%' : ''
                     }
                 },
                 grid: { borderColor: gridColor, strokeDashArray: 3 },
                 legend: { position: 'top', horizontalAlign: 'right', labels: { colors: textColor, fontWeight: 600 } },
                 tooltip: {
                     shared: true,
                     intersect: false,
                     y: {
                         formatter: function(val, { seriesIndex, dataPointIndex }) {
                             if (val === null || val === undefined) return '-';
                             const item = (self.monthlyData && self.monthlyData[dataPointIndex]) ? self.monthlyData[dataPointIndex] : null;
                             if (!item) return val + '%';
                             if (seriesIndex === 0) {
                                 return val + '% (' + new Intl.NumberFormat('id-ID').format(item.ac) + ' dari ' + new Intl.NumberFormat('id-ID').format(item.pc) + ' PC)';
                             } else if (seriesIndex === 1) {
                                 return val + '% (' + new Intl.NumberFormat('id-ID').format(item.ec) + ' dari ' + new Intl.NumberFormat('id-ID').format(item.ac) + ' AC)';
                             } else {
                                 return val + '% (' + new Intl.NumberFormat('id-ID').format(item.ec) + ' dari ' + new Intl.NumberFormat('id-ID').format(item.pc) + ' PC)';
                             }
                         }
                     }
                 }
             });
             this.chartTrend.render();
         }
     }"
     @chart-data-updated.window="onDataUpdated($event)"
     @chart-reload.window="destroyCharts(); renderCharts()">

    <x-slot name="title">Trend Bulanan Kunjungan SE</x-slot>

    {{-- Standar Tab Menu --}}
    <div class="shrink-0">
        <x-ui.tab-menu class="mb-1">
            <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.index')" :navigate="true">
                <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
            </x-ui.tab-item>
            <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.summary')" :navigate="true">
                <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
            </x-ui.tab-item>
            <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.reason')" :navigate="true">
                <x-heroicon-o-clipboard-document-list class="w-4 h-4 inline-block mr-1" /> Summary Reason
            </x-ui.tab-item>
            <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.trend')" :navigate="true">
                <x-heroicon-o-arrow-trending-up class="w-4 h-4 inline-block mr-1" /> Trend Bulanan
            </x-ui.tab-item>
        </x-ui.tab-menu>
    </div>

    {{-- 5 KPI Ringkasan Tahunan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 sm:gap-3 shrink-0">
        {{-- Card 1: Total Plan Call --}}
        <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-sky-500/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Total Plan Call (PC)</span>
                <div class="w-6 h-6 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-600 shrink-0">
                    <x-heroicon-s-clipboard-document-list class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black font-mono text-base-content">
                    {{ number_format($totals->total_pc) }}
                </span>
                <span class="text-[0.6875rem] font-semibold text-base-content/50 uppercase">Rencana</span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50">
                Target kunjungan tahun {{ $appliedYear }}
            </div>
        </div>

        {{-- Card 2: Total Realisasi (AC) --}}
        <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-amber-500/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Realisasi Kunjung (AC)</span>
                <div class="w-6 h-6 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                    <x-heroicon-s-map-pin class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black font-mono text-base-content">
                    {{ number_format($totals->total_ac) }}
                </span>
                <span class="text-xs font-mono font-bold text-amber-600">
                    {{ $totals->ac_percent }}%
                </span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50">
                Drop Call: <span class="font-bold text-rose-500">{{ number_format($totals->total_drop) }}</span>
            </div>
        </div>

        {{-- Card 3: Total Efektif (EC) --}}
        <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-emerald-500/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Efektif Order (EC)</span>
                <div class="w-6 h-6 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                    <x-heroicon-s-shopping-bag class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black font-mono text-base-content">
                    {{ number_format($totals->total_ec) }}
                </span>
                <span class="text-xs font-mono font-bold text-emerald-600">
                    {{ $totals->ec_percent }}%
                </span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50">
                Strike Rate dari kunjungan AC
            </div>
        </div>

        {{-- Card 4: Total Konversi --}}
        <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-purple-500/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Konversi Rencana (EC/PC)</span>
                <div class="w-6 h-6 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-600 shrink-0">
                    <x-heroicon-s-chart-pie class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black font-mono text-purple-600 dark:text-purple-400">
                    {{ $totals->ec_pc_percent }}%
                </span>
                <span class="text-[0.6875rem] font-semibold text-base-content/50 uppercase">Rasio</span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50 truncate">
                Unproductive: <span class="font-bold text-slate-700 dark:text-slate-300">{{ number_format($totals->total_unproductive) }}</span>
            </div>
        </div>

        {{-- Card 5: Total Order Value --}}
        <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-teal-500/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Total Omset Order</span>
                <div class="w-6 h-6 rounded-lg bg-teal-500/10 flex items-center justify-center text-teal-600 shrink-0">
                    <x-heroicon-s-banknotes class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-base sm:text-lg font-black font-mono text-teal-600 dark:text-teal-400 truncate" title="Rp {{ number_format($totals->total_val, 0, ',', '.') }}">
                    @if($totals->total_val >= 1000000000)
                        Rp {{ number_format($totals->total_val / 1000000000, 2, ',', '.') }} M
                    @elseif($totals->total_val >= 1000000)
                        Rp {{ number_format($totals->total_val / 1000000, 1, ',', '.') }} Jt
                    @else
                        Rp {{ number_format($totals->total_val, 0, ',', '.') }}
                    @endif
                </span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50">
                AOV: <span class="font-bold">Rp {{ number_format($totals->aov, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar Card --}}
    <div class="bg-base-100 rounded-xl shadow-xs border border-base-300 p-3 md:p-4 shrink-0">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                {{-- Dropdown Pilihan Tahun (Full 1 Tahun) --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-base-content/70">Tahun:</span>
                    <select wire:model="selectedYear" class="select select-sm select-bordered rounded-xl bg-base-100 font-bold text-xs">
                        @foreach($yearOptions as $yr)
                            <option value="{{ $yr }}">{{ $yr }} (Full 1 Tahun)</option>
                        @endforeach
                    </select>
                </div>

                <div class="hidden sm:block w-px h-6 bg-base-300 mx-1"></div>

                {{-- Toggle Pilihan Jenis Kunjungan (ALL, PJP, XC) --}}
                <div class="flex items-center gap-1 bg-base-200/60 p-1 rounded-xl border border-base-300">
                    <button type="button"
                            wire:click="setCallType('ALL')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $appliedCallType === 'ALL' ? 'bg-primary text-primary-content shadow-xs' : 'text-base-content/70 hover:text-base-content hover:bg-base-100' }}">
                        🌐 Semua Kunjungan
                    </button>
                    <button type="button"
                            wire:click="setCallType('PJP')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $appliedCallType === 'PJP' ? 'bg-sky-600 text-white shadow-xs' : 'text-base-content/70 hover:text-base-content hover:bg-base-100' }}">
                        📌 Rute PJP (Murni PC)
                    </button>
                    <button type="button"
                            wire:click="setCallType('XC')"
                            class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all {{ $appliedCallType === 'XC' ? 'bg-amber-600 text-white shadow-xs' : 'text-base-content/70 hover:text-base-content hover:bg-base-100' }}">
                        ⚡ Extra Call (XC)
                    </button>
                </div>
            </div>

            {{-- Territory Filters & Actions --}}
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
                {{-- Filter Region --}}
                @if(count($regions) > 1 || $accessLevel === 'nasional')
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 text-xs min-w-[130px]">
                        <option value="">Semua Region (Nasional)</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Area --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 text-xs min-w-[130px]" @disabled(empty($selectedRegion) && $accessLevel === 'nasional')>
                        <option value="">Semua Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Distributor --}}
                <select wire:model="selectedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 text-xs min-w-[140px]" @disabled(empty($selectedRegion) && $accessLevel === 'nasional')>
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $d)
                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-1.5 ml-auto sm:ml-0">
                    <x-ui.button variant="primary" size="sm" class="rounded-xl px-3 font-semibold shadow-sm" wire:click="applyFilter" spinner="applyFilter" icon="funnel">
                        Terapkan
                    </x-ui.button>

                    <x-ui.button variant="ghost" size="sm" class="rounded-xl px-2.5 text-base-content/70 hover:text-base-content" wire:click="resetFilters" spinner="resetFilters" icon="arrow-path">
                        Reset
                    </x-ui.button>

                    @if($canExport)
                        <x-ui.button variant="success" size="sm" class="rounded-xl text-white px-3 shadow-sm font-semibold" wire:click="exportExcel" spinner="exportExcel" icon="arrow-down-tray">
                            Excel
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Trend Chart Card (Full Width) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 p-4 shrink-0 relative overflow-hidden">
        {{-- Loading Overlay Khusus Terapkan Filter --}}
        <div wire:loading.flex wire:target="applyFilter, setCallType, resetFilters"
             class="absolute inset-0 bg-base-100/70 backdrop-blur-2xs z-20 flex items-center justify-center">
            <div class="flex items-center gap-2 bg-base-100 px-4 py-2.5 rounded-xl shadow-lg border border-base-300">
                <span class="loading loading-spinner loading-sm text-primary"></span>
                <span class="text-xs font-semibold text-base-content">Memperbarui grafik tren...</span>
            </div>
        </div>

        <div class="flex items-center justify-between pb-3 border-b border-base-200">
            <div>
                <h3 class="text-base font-bold text-base-content flex items-center gap-2">
                    <x-heroicon-m-arrow-trending-up class="w-5 h-5 text-primary" />
                    Grafik Tren Persentase Performa Kunjungan Bulanan (% AC & % EC)
                </h3>
                <p class="text-xs text-base-content/60 mt-0.5">
                    Periode: <span class="font-bold text-base-content">Januari - Desember {{ $appliedYear }}</span> | Tipe: <span class="badge badge-xs font-bold {{ $appliedCallType === 'PJP' ? 'badge-info text-white' : ($appliedCallType === 'XC' ? 'badge-warning text-slate-950' : 'badge-primary text-white') }}">{{ $appliedCallType === 'PJP' ? 'Rute PJP' : ($appliedCallType === 'XC' ? 'Extra Call' : 'Semua Kunjungan') }}</span>
                </p>
            </div>
        </div>

        <div class="w-full mt-2" wire:ignore>
            <div id="chartMainTrend" class="w-full"></div>
        </div>
    </div>

    {{-- Tabel Ringkasan Bulanan (Januari s/d Desember) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 shrink-0 overflow-hidden flex flex-col mb-4 relative">
        {{-- Loading Overlay Khusus Terapkan Filter --}}
        <div wire:loading.flex wire:target="applyFilter, setCallType, resetFilters"
             class="absolute inset-0 bg-base-100/70 backdrop-blur-2xs z-20 flex items-center justify-center">
            <div class="flex items-center gap-2 bg-base-100 px-4 py-2.5 rounded-xl shadow-lg border border-base-300">
                <span class="loading loading-spinner loading-sm text-primary"></span>
                <span class="text-xs font-semibold text-base-content">Memperbarui matriks bulanan...</span>
            </div>
        </div>

        <div class="p-3 md:p-4 border-b border-base-300 bg-base-200/30 flex items-center justify-between">
            <div>
                <h3 class="text-sm md:text-base font-bold text-base-content">
                    Tabel Matriks Performa Bulanan (Tahun {{ $appliedYear }})
                </h3>
                <p class="text-[0.6875rem] text-base-content/60 uppercase tracking-wider mt-0.5">
                    Rekapitulasi 12 Bulan Plan Call, Realisasi Kunjung, dan Efektivitas Order
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs">
                <thead class="sticky top-0 bg-base-300 text-base-content z-10 text-xs uppercase tracking-wider font-bold">
                    <tr>
                        <th class="w-10 text-center">#</th>
                        <th class="text-left min-w-[110px]">Bulan</th>
                        <th class="text-center w-20">Hari Kerja</th>
                        <th class="text-center w-20">SE Aktif</th>
                        <th class="text-right min-w-[90px] text-sky-700 dark:text-sky-300">Plan (PC)</th>
                        <th class="text-right min-w-[90px] text-amber-700 dark:text-amber-300">Actual (AC)</th>
                        <th class="text-center w-20">% AC</th>
                        <th class="text-right min-w-[80px] text-rose-600">Drop Call</th>
                        <th class="text-right min-w-[90px] text-emerald-700 dark:text-emerald-300">Effective (EC)</th>
                        <th class="text-center w-20">% EC</th>
                        <th class="text-center w-20">% EC/PC</th>
                        <th class="text-right min-w-[90px] text-base-content/70">Unproductive</th>
                        <th class="text-right min-w-[120px] text-teal-700 dark:text-teal-300 pr-4">Total Omset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200 font-mono text-xs">
                    @foreach($trendData as $r)
                        <tr class="hover:bg-primary/5 transition-colors {{ $r->is_future && $r->pc === 0 ? 'opacity-60 bg-base-200/20' : '' }}">
                            <td class="text-center text-base-content/50">{{ $r->month_num }}</td>
                            <td class="text-left font-sans font-bold text-base-content">
                                {{ $r->month_name }}
                                @if($r->is_future && $r->pc === 0)
                                    <span class="text-[0.625rem] font-normal text-base-content/40 italic ml-1">(Belum Ada)</span>
                                @endif
                            </td>
                            <td class="text-center text-base-content/70">{{ $r->active_days > 0 ? $r->active_days . 'h' : '-' }}</td>
                            <td class="text-center text-base-content/70">{{ $r->active_se > 0 ? $r->active_se : '-' }}</td>
                            <td class="text-right font-bold text-sky-700 dark:text-sky-300">
                                {{ $r->pc > 0 ? number_format($r->pc) : '-' }}
                            </td>
                            <td class="text-right font-bold text-amber-700 dark:text-amber-300">
                                {{ $r->ac > 0 ? number_format($r->ac) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($r->ac_percent !== null)
                                    <span class="badge badge-xs {{ $r->ac_percent >= 100 ? 'badge-success text-white' : ($r->ac_percent >= 90 ? 'badge-warning text-slate-950 font-bold' : 'badge-error text-white') }} font-bold">
                                        {{ $r->ac_percent }}%
                                    </span>
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                            <td class="text-right text-rose-600 font-semibold">
                                {{ $r->drop_call > 0 ? number_format($r->drop_call) : ($r->is_future ? '-' : '0') }}
                            </td>
                            <td class="text-right font-bold text-emerald-700 dark:text-emerald-300">
                                {{ $r->ec > 0 ? number_format($r->ec) : '-' }}
                            </td>
                            <td class="text-center">
                                @if($r->ec_percent !== null)
                                    <span class="badge badge-xs {{ $r->ec_percent >= 100 ? 'badge-success text-white' : ($r->ec_percent >= 75 ? 'badge-warning text-slate-950 font-bold' : 'badge-error text-white') }} font-bold">
                                        {{ $r->ec_percent }}%
                                    </span>
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                            <td class="text-center text-purple-600 dark:text-purple-400 font-bold">
                                {{ $r->ec_pc_percent !== null ? $r->ec_pc_percent . '%' : '-' }}
                            </td>
                            <td class="text-right text-base-content/60">
                                {{ $r->unproductive_call > 0 ? number_format($r->unproductive_call) : ($r->is_future ? '-' : '0') }}
                            </td>
                            <td class="text-right font-bold text-teal-700 dark:text-teal-300 pr-4">
                                @if($r->order_val > 0)
                                    Rp {{ number_format($r->order_val, 0, ',', '.') }}
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                {{-- Sticky Grand Total Footer --}}
                <tfoot class="bg-slate-950 dark:bg-black text-white border-t-2 border-primary shadow-[0_-4px_12px_rgba(0,0,0,0.35)] sticky bottom-0 z-20 font-mono text-xs divide-x divide-slate-800">
                    <tr class="font-bold text-white [&>th]:align-middle bg-slate-950 dark:bg-black h-9">
                        <th colspan="2" class="text-left pl-3 whitespace-nowrap bg-slate-950 dark:bg-black font-sans">
                            <span class="text-blue-300 font-extrabold tracking-wide">TOTAL TAHUN {{ $appliedYear }}</span>
                        </th>
                        <th class="text-center bg-slate-950 dark:bg-black text-slate-400 font-normal">-</th>
                        <th class="text-center bg-slate-950 dark:bg-black text-slate-400 font-normal">-</th>
                        <th class="text-right text-sky-300 bg-slate-950 dark:bg-black">{{ number_format($totals->total_pc) }}</th>
                        <th class="text-right text-amber-300 bg-slate-950 dark:bg-black">{{ number_format($totals->total_ac) }}</th>
                        <th class="text-center bg-slate-950 dark:bg-black">
                            <span class="badge badge-xs {{ $totals->ac_percent >= 100 ? 'badge-success text-white' : 'badge-warning text-slate-950 font-bold' }}">
                                {{ $totals->ac_percent }}%
                            </span>
                        </th>
                        <th class="text-right text-rose-400 bg-slate-950 dark:bg-black">{{ number_format($totals->total_drop) }}</th>
                        <th class="text-right text-emerald-300 bg-slate-950 dark:bg-black">{{ number_format($totals->total_ec) }}</th>
                        <th class="text-center bg-slate-950 dark:bg-black">
                            <span class="badge badge-xs {{ $totals->ec_percent >= 100 ? 'badge-success text-white' : 'badge-warning text-slate-950 font-bold' }}">
                                {{ $totals->ec_percent }}%
                            </span>
                        </th>
                        <th class="text-center text-purple-300 bg-slate-950 dark:bg-black">
                            {{ $totals->ec_pc_percent }}%
                        </th>
                        <th class="text-right text-slate-300 bg-slate-950 dark:bg-black">{{ number_format($totals->total_unproductive) }}</th>
                        <th class="text-right text-teal-300 pr-4 bg-slate-950 dark:bg-black">
                            Rp {{ number_format($totals->total_val, 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
