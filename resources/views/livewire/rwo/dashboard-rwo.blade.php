<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
     x-data="rwoDashboardCharts()"
     x-init="initCharts(@js($metrics), @js($chartData))"
     @dashboard-updated.window="updateCharts($event.detail.metrics, $event.detail.chartData)">
    
    <x-slot name="title">Dashboard RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.dashboard') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Dashboard</a>
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Pencapaian</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SKB</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Plan Kunjungan</a>
            <a href="{{ route('rwo.monitoring-pareto') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Monitoring Visit</a>
        </div>
    </div>

    {{-- Toolbar / Filters --}}
    <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-300 shrink-0 flex flex-wrap items-end gap-3">
        <div class="form-control min-w-[130px]">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Kuartal</span></label>
            <select wire:model.live="kuartal" class="select select-bordered select-sm w-full bg-base-100">
                <option value="">Semua Kuartal</option>
                @foreach($kuartals as $q)
                    <option value="{{ $q->quarter }}">Kuartal {{ $q->quarter }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-control min-w-[150px]">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Bulan (Prorata)</span></label>
            <select wire:model.live="bulan" class="select select-bordered select-sm w-full bg-base-100" {{ empty($bulans) ? 'disabled' : '' }}>
                <option value="">Semua Bulan</option>
                @foreach($bulans as $b)
                    <option value="{{ $b['value'] }}">{{ $b['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-control min-w-[150px]">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Region</span></label>
            <select wire:model.live="region" class="select select-bordered select-sm w-full bg-base-100">
                <option value="">Semua Region</option>
                @foreach($regions as $r)
                    <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-control min-w-[150px]">
            <label class="label pt-0 pb-1"><span class="label-text text-xs font-semibold">Area</span></label>
            <select wire:model.live="area" class="select select-bordered select-sm w-full bg-base-100" {{ empty($areas) ? 'disabled' : '' }}>
                <option value="">Semua Area</option>
                @foreach($areas as $a)
                    <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Info Periode Prorata Aktif --}}
        @if(!empty($periodInfo))
            <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20 text-xs mb-0.5">
                <span class="font-bold text-primary">{{ $periodInfo['prorataBadge'] }}</span>
                <span class="text-base-content/40">•</span>
                <span class="text-base-content/70 font-medium">Kumulatif: {{ $periodInfo['periodLabel'] }}</span>
            </div>
        @endif
        
        <div class="ml-auto flex items-center gap-2">
            <button type="button"
                wire:click="resetFilters"
                class="btn btn-sm btn-ghost border border-base-300 text-xs font-semibold"
                title="Reset filter ke bulan & kuartal berjalan">
                Reset
            </button>
        </div>
    </div>

    {{-- Dashboard Content --}}
    <div class="flex-1 min-h-0 min-w-0 overflow-y-auto pb-8 relative">
        {{-- Smooth Loading Overlay --}}
        <div wire:loading.flex wire:target="kuartal, bulan, region, area, resetFilters, drillDown, drillUp"
             class="absolute inset-0 bg-base-100/60 backdrop-blur-[1px] z-30 items-center justify-center rounded-xl">
            <div class="bg-base-100 px-4 py-2.5 rounded-xl shadow-lg border border-base-300 flex items-center gap-3">
                <span class="loading loading-spinner loading-sm text-primary"></span>
                <span class="text-xs font-semibold text-base-content/80">Memperbarui dashboard...</span>
            </div>
        </div>
        
        @php
            $totalToko = (int) ($metrics['total_toko'] ?? 0);
            $tokoTransaksi = (int) ($metrics['toko_transaksi'] ?? 0);
            $tokoTransaksiPct = $totalToko > 0 ? ($tokoTransaksi / $totalToko) * 100 : 0;

            $fullTarget = (float) ($metrics['total_target'] ?? 0);
            $totalTarget = (float) ($metrics['target_prorata'] ?? 0);
            $totalAchievement = (float) ($metrics['total_achievement'] ?? 0);
            $overallPct = $totalTarget > 0 ? ($totalAchievement / $totalTarget) * 100 : 0;

            $tokoHijau = (int) ($metrics['toko_hijau'] ?? 0);
            $tokoHijauPct = $totalToko > 0 ? ($tokoHijau / $totalToko) * 100 : 0;

            $tokoKuning = (int) ($metrics['toko_kuning'] ?? 0);
            $tokoKuningPct = $totalToko > 0 ? ($tokoKuning / $totalToko) * 100 : 0;

            $tokoMerah = (int) ($metrics['toko_merah'] ?? 0);
            $tokoMerahPct = $totalToko > 0 ? ($tokoMerah / $totalToko) * 100 : 0;

            $totalJks = (int) ($metrics['total_jks'] ?? 0);
            $jksPct = $totalToko > 0 ? ($totalJks / $totalToko) * 100 : 0;

            $totalTokoCb = (int) ($metrics['total_toko_cb'] ?? 0);
            $sudahSkb = (int) ($metrics['sudah_skb'] ?? 0);
            $skbApprove = (int) ($metrics['skb_approve'] ?? 0);
            $skbReject = (int) ($metrics['skb_reject'] ?? 0);
            $belumSkb = (int) ($metrics['belum_skb'] ?? max(0, $totalTokoCb - $sudahSkb));
            $skbPct = $totalTokoCb > 0 ? ($sudahSkb / $totalTokoCb) * 100 : 0;

            $dataLengkap = (int) ($metrics['data_lengkap'] ?? 0);
            $dataBelum = (int) ($metrics['data_belum'] ?? 0);
            $totalDataTarget = $dataLengkap + $dataBelum;
            $dataLengkapPct = $totalDataTarget > 0 ? ($dataLengkap / $totalDataTarget) * 100 : 0;
        @endphp

        {{-- Top Stat Cards (Performa Target & Transaksi) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-3">
            
            {{-- Card 1: Toko & Transaksi --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <span class="text-indigo-100 text-[10px] font-bold uppercase tracking-wider">Total Transaksi / Toko</span>
                    <div class="mt-1 text-2xl font-extrabold">{{ number_format($tokoTransaksi, 0, ',', '.') }}</div>
                    <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                        <div class="flex justify-between items-center text-indigo-100">
                            <span>Total Toko: {{ number_format($totalToko, 0, ',', '.') }}</span>
                            <span>{{ number_format($tokoTransaksiPct, 1, ',', '.') }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-0.5">
                            <div class="bg-white h-1 rounded-full" style="width: {{ min($tokoTransaksiPct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Target Prorata & Pencapaian --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-blue-100 text-[10px] font-bold uppercase tracking-wider">Pencapaian vs Prorata</span>
                        <span class="badge badge-xs bg-white/20 border-none text-white font-bold">{{ number_format($overallPct, 1, ',', '.') }}%</span>
                    </div>
                    <div class="mt-1 text-lg sm:text-xl font-extrabold truncate" title="Pencapaian: Rp {{ number_format($totalAchievement, 0, ',', '.') }}">Rp {{ number_format($totalAchievement, 0, ',', '.') }}</div>
                    <div class="mt-auto pt-2 flex flex-col gap-0.5 text-[10px] font-medium">
                        <div class="flex justify-between items-center text-blue-100">
                            <span>Prorata: Rp {{ number_format($totalTarget, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-blue-200/80 text-[9px] truncate">Full Target: Rp {{ number_format($fullTarget, 0, ',', '.') }}</div>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-0.5">
                            <div class="bg-white h-1 rounded-full" style="width: {{ min($overallPct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Toko Hijau --}}
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <span class="text-emerald-100 text-[10px] font-bold uppercase tracking-wider">Toko Hijau (&ge;100%)</span>
                    <div class="mt-1 text-2xl font-extrabold">{{ number_format($tokoHijau, 0, ',', '.') }}</div>
                    <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                        <div class="flex justify-between items-center text-emerald-100">
                            <span>Dari Total Toko</span>
                            <span>{{ number_format($tokoHijauPct, 1, ',', '.') }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-0.5">
                            <div class="bg-white h-1 rounded-full" style="width: {{ min($tokoHijauPct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Toko Kuning --}}
            <div class="bg-gradient-to-br from-amber-500 to-amber-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <span class="text-amber-100 text-[10px] font-bold uppercase tracking-wider">Toko Kuning (80-99%)</span>
                    <div class="mt-1 text-2xl font-extrabold">{{ number_format($tokoKuning, 0, ',', '.') }}</div>
                    <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                        <div class="flex justify-between items-center text-amber-100">
                            <span>Dari Total Toko</span>
                            <span>{{ number_format($tokoKuningPct, 1, ',', '.') }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-0.5">
                            <div class="bg-white h-1 rounded-full" style="width: {{ min($tokoKuningPct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 5: Toko Merah --}}
            <div class="bg-gradient-to-br from-rose-500 to-rose-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <span class="text-rose-100 text-[10px] font-bold uppercase tracking-wider">Toko Merah (&lt;80%)</span>
                    <div class="mt-1 text-2xl font-extrabold">{{ number_format($tokoMerah, 0, ',', '.') }}</div>
                    <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                        <div class="flex justify-between items-center text-rose-100">
                            <span>Dari Total Toko</span>
                            <span>{{ number_format($tokoMerahPct, 1, ',', '.') }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-0.5">
                            <div class="bg-white h-1 rounded-full" style="width: {{ min($tokoMerahPct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Secondary Operational Cards (Masuk JKS, Status SKB, Kelengkapan Data) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            {{-- Masuk JKS --}}
            <div class="bg-base-100 p-3.5 rounded-xl shadow-sm border border-base-300 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">Masuk JKS (Kumulatif)</span>
                    <span class="badge badge-info badge-sm font-bold text-white">{{ number_format($jksPct, 1, ',', '.') }}%</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-xl font-extrabold text-info">{{ number_format($totalJks, 0, ',', '.') }} <span class="text-xs font-medium text-base-content/60">Toko</span></span>
                    <span class="text-xs text-base-content/60">Belum JKS: <strong class="text-base-content">{{ number_format(max(0, $totalToko - $totalJks), 0, ',', '.') }}</strong></span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-1.5">
                    <div class="bg-info h-1.5 rounded-full transition-all duration-300" style="width: {{ min($jksPct, 100) }}%"></div>
                </div>
            </div>

            {{-- Progres SKB (Khusus CB) --}}
            <div class="bg-base-100 p-3.5 rounded-xl shadow-sm border border-base-300 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">Status SKB (Khusus CB)</span>
                    <span class="badge badge-primary badge-sm font-bold">{{ number_format($skbPct, 1, ',', '.') }}% ({{ number_format($sudahSkb, 0, ',', '.') }}/{{ number_format($totalTokoCb, 0, ',', '.') }})</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-xl font-extrabold text-base-content">{{ number_format($sudahSkb, 0, ',', '.') }} <span class="text-xs font-medium text-base-content/60">Sudah SKB</span></span>
                    <div class="flex items-center gap-1.5 text-[11px]">
                        <span class="text-success font-bold">App: {{ number_format($skbApprove, 0, ',', '.') }}</span>
                        <span class="text-base-content/30">|</span>
                        <span class="text-error font-bold">Rej: {{ number_format($skbReject, 0, ',', '.') }}</span>
                        <span class="text-base-content/30">|</span>
                        <span class="text-base-content/70 font-semibold">Blm: {{ number_format($belumSkb, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="w-full bg-base-200 rounded-full h-1.5">
                    <div class="bg-primary h-1.5 rounded-full transition-all duration-300" style="width: {{ min($skbPct, 100) }}%"></div>
                </div>
            </div>

            {{-- Kelengkapan Data Outlet --}}
            <div class="bg-base-100 p-3.5 rounded-xl shadow-sm border border-base-300 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">Kelengkapan Data Toko</span>
                    <span class="badge badge-success badge-sm font-bold text-white">{{ number_format($dataLengkapPct, 1, ',', '.') }}%</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <span class="text-xl font-extrabold text-success">{{ number_format($dataLengkap, 0, ',', '.') }} <span class="text-xs font-medium text-base-content/60">Lengkap</span></span>
                    <span class="text-xs text-error font-semibold">Belum: {{ number_format($dataBelum, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-1.5">
                    <div class="bg-success h-1.5 rounded-full transition-all duration-300" style="width: {{ min($dataLengkapPct, 100) }}%"></div>
                </div>
            </div>
        </div>

        @if($totalToko == 0)
            {{-- Empty State --}}
            <div class="bg-base-100 p-8 rounded-xl border border-base-300 text-center my-6 flex flex-col items-center justify-center">
                <svg class="h-12 w-12 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-base-content">Tidak Ada Data Potensi</h3>
                <p class="mt-1 text-xs text-base-content/60 max-w-md">Tidak ditemukan toko potensi untuk filter Kuartal/Bulan/Region/Area yang dipilih. Coba ganti filter Anda.</p>
            </div>
        @endif

        {{-- Charts Section (kept in DOM via class toggle to prevent ApexCharts missing container bug) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 {{ $totalToko == 0 ? 'hidden' : '' }}">
            
            {{-- Main Bar Chart --}}
            <div class="lg:col-span-2 bg-base-100 p-4 rounded-xl shadow-md border border-base-200 flex flex-col">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base-content font-bold text-sm">Target Prorata vs Achievement (<span x-text="groupName"></span>)</h3>
                        <span x-show="groupName !== 'Supervisor'" x-cloak class="badge badge-ghost badge-xs text-[10px] text-base-content/60">
                            Klik bar untuk drill-down
                        </span>
                    </div>
                    @if(!empty($region) || !empty($area))
                        <button type="button"
                                wire:click="drillUp"
                                class="btn btn-xs btn-ghost border border-base-300 gap-1 text-[11px] font-semibold">
                            <span>&larr;</span>
                            <span>Kembali ke {{ !empty($area) ? 'Area' : 'Region' }}</span>
                        </button>
                    @endif
                </div>
                <div id="bar-chart"
                     class="w-full flex-1 min-h-[350px] lg:min-h-0"
                     :class="groupName !== 'Supervisor' ? '[&_.apexcharts-bar-area]:cursor-pointer' : ''"
                     wire:ignore></div>
            </div>

            {{-- Donut Charts Container --}}
            <div class="flex flex-col gap-4">
                {{-- Donut 1: Status Toko --}}
                <div class="bg-base-100 p-4 rounded-xl shadow-md border border-base-200 flex-1 flex flex-col">
                    <h3 class="text-base-content font-bold mb-1 text-sm">Distribusi Status Toko</h3>
                    <div id="donut-toko" class="w-full h-[180px] mt-auto" wire:ignore></div>
                </div>

                {{-- Donut 2: Status SKB --}}
                <div class="bg-base-100 p-4 rounded-xl shadow-md border border-base-200 flex-1 flex flex-col">
                    <h3 class="text-base-content font-bold mb-1 text-sm">Distribusi Status SKB (Khusus CB)</h3>
                    <div id="donut-skb" class="w-full h-[180px] mt-auto" wire:ignore></div>
                </div>
            </div>

        </div>
    </div>

    {{-- Inline Script for ApexCharts (SPA wire:navigate compatible) --}}
    <script>
        window.rwoDashboardCharts = function () {
            return {
                barChart: null,
                donutToko: null,
                donutSkb: null,
                groupName: 'Region',
                themeObserver: null,

                get isDarkMode() {
                    const theme = document.documentElement.getAttribute('data-theme') || 'neon-dark';
                    return theme.includes('dark');
                },

                get textColor() {
                    return this.isDarkMode ? '#cbd5e1' : '#475569';
                },

                formatShortRupiah(val) {
                    const num = Number(val);
                    if (isNaN(num)) return val;
                    const abs = Math.abs(num);
                    if (abs >= 1000000000) {
                        return 'Rp ' + (num / 1000000000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M';
                    }
                    if (abs >= 1000000) {
                        return 'Rp ' + (num / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' Jt';
                    }
                    if (abs >= 1000) {
                        return 'Rp ' + (num / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' Rb';
                    }
                    return 'Rp ' + num.toLocaleString('id-ID');
                },

                initCharts(metrics, chartData) {
                    this.destroyCharts();

                    if (this.themeObserver) {
                        this.themeObserver.disconnect();
                    }
                    this.themeObserver = new MutationObserver(() => {
                        this.handleThemeChange();
                    });
                    this.themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

                    if (parseInt(metrics.total_toko) === 0) {
                        return;
                    }

                    this.groupName = chartData.groupName || 'Region';
                    this.renderBarChart(chartData);
                    this.renderDonutToko(metrics);
                    this.renderDonutSkb(metrics);
                },

                destroy() {
                    if (this.themeObserver) {
                        this.themeObserver.disconnect();
                        this.themeObserver = null;
                    }
                    this.destroyCharts();
                },

                updateCharts(metrics, chartData) {
                    this.$nextTick(() => {
                        if (parseInt(metrics.total_toko) === 0) {
                            this.destroyCharts();
                            return;
                        }

                        this.groupName = chartData.groupName || 'Region';

                        // Recreate bar chart to smoothly switch horizontal/vertical orientations & drill-down codes
                        if (this.barChart) {
                            try { this.barChart.destroy(); } catch (e) {}
                            this.barChart = null;
                            const el = document.querySelector("#bar-chart");
                            if (el) el.innerHTML = '';
                        }
                        this.renderBarChart(chartData);

                        if (this.donutToko) {
                            this.donutToko.updateSeries([
                                parseInt(metrics.toko_hijau) || 0,
                                parseInt(metrics.toko_kuning) || 0,
                                parseInt(metrics.toko_merah) || 0
                            ]);
                        } else {
                            this.renderDonutToko(metrics);
                        }

                        if (this.donutSkb) {
                            const total_cb = parseInt(metrics.total_toko_cb) || 0;
                            const approved = parseInt(metrics.skb_approve) || 0;
                            const rejected = parseInt(metrics.skb_reject) || 0;
                            const pending = Math.max(0, total_cb - (approved + rejected));
                            this.donutSkb.updateSeries([approved, rejected, pending]);
                        } else {
                            this.renderDonutSkb(metrics);
                        }
                    });
                },

                handleThemeChange() {
                    const textColor = this.textColor;
                    const gridColor = this.isDarkMode ? '#334155' : '#e2e8f0';
                    const tooltipTheme = this.isDarkMode ? 'dark' : 'light';

                    if (this.barChart) {
                        this.barChart.updateOptions({
                            xaxis: { labels: { style: { colors: textColor } } },
                            yaxis: { labels: { style: { colors: textColor } } },
                            grid: { borderColor: gridColor },
                            tooltip: { theme: tooltipTheme },
                            legend: { labels: { colors: textColor } }
                        }, false, false);
                    }

                    const donutUpdateOptions = {
                        plotOptions: {
                            pie: {
                                donut: {
                                    labels: {
                                        value: { color: textColor },
                                        total: { color: textColor }
                                    }
                                }
                            }
                        },
                        legend: { labels: { colors: textColor } },
                        tooltip: { theme: tooltipTheme }
                    };

                    if (this.donutToko) {
                        this.donutToko.updateOptions(donutUpdateOptions, false, false);
                    }
                    if (this.donutSkb) {
                        this.donutSkb.updateOptions(donutUpdateOptions, false, false);
                    }
                },

                destroyCharts() {
                    if (this.barChart) {
                        try { this.barChart.destroy(); } catch (e) {}
                        this.barChart = null;
                    }
                    if (this.donutToko) {
                        try { this.donutToko.destroy(); } catch (e) {}
                        this.donutToko = null;
                    }
                    if (this.donutSkb) {
                        try { this.donutSkb.destroy(); } catch (e) {}
                        this.donutSkb = null;
                    }
                    document.querySelectorAll('#bar-chart, #donut-toko, #donut-skb').forEach(el => {
                        if (el) el.innerHTML = '';
                    });
                },

                renderBarChart(data) {
                    const isHorizontal = data.categories.length > 6;
                    const self = this;
                    var options = {
                        series: [
                            { name: 'Target Prorata', data: data.target },
                            { name: 'Achievement', data: data.achievement }
                        ],
                        chart: {
                            type: 'bar',
                            height: '100%',
                            fontFamily: 'inherit',
                            toolbar: { show: true, tools: { download: true, selection: false, zoom: false, pan: false } },
                            animations: { enabled: true, easing: 'easeinout', speed: 600 },
                            events: {
                                dataPointSelection: function (event, chartContext, config) {
                                    const idx = config.dataPointIndex;
                                    const code = data.codes ? data.codes[idx] : null;
                                    if (code && (data.groupName === 'Region' || data.groupName === 'Area')) {
                                        self.$wire.drillDown(data.groupName, String(code));
                                    }
                                }
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: isHorizontal,
                                columnWidth: isHorizontal ? undefined : '45%',
                                barHeight: isHorizontal ? '70%' : undefined,
                                endingShape: 'rounded',
                                borderRadius: 4,
                                dataLabels: { position: 'top' }
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: isHorizontal ? 1 : 3, colors: ['transparent'] },
                        xaxis: {
                            categories: data.categories,
                            labels: {
                                style: { colors: this.textColor, cssClass: 'text-xs font-sans font-medium' },
                                formatter: isHorizontal ? (val) => self.formatShortRupiah(val) : undefined
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: this.textColor },
                                formatter: isHorizontal ? undefined : (val) => self.formatShortRupiah(val)
                            }
                        },
                        grid: {
                            borderColor: this.isDarkMode ? '#334155' : '#e2e8f0',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: isHorizontal } },
                            yaxis: { lines: { show: !isHorizontal } }
                        },
                        fill: { opacity: 1 },
                        colors: ['#94a3b8', '#3b82f6'],
                        tooltip: {
                            theme: this.isDarkMode ? 'dark' : 'light',
                            y: {
                                formatter: function (val, { seriesIndex, dataPointIndex }) {
                                    const num = Number(val || 0);
                                    const formatted = "Rp " + num.toLocaleString('id-ID');
                                    if (seriesIndex === 1) {
                                        const targetVal = Number(data.target?.[dataPointIndex] || 0);
                                        const pct = targetVal > 0
                                            ? ((num / targetVal) * 100).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                                            : '0,0';
                                        return `${formatted} (${pct}%)`;
                                    }
                                    return formatted;
                                }
                            }
                        },
                        legend: {
                            labels: { colors: this.textColor },
                            position: 'top',
                            horizontalAlign: 'left'
                        }
                    };
                    const container = document.querySelector("#bar-chart");
                    if (container) {
                        this.barChart = new ApexCharts(container, options);
                        this.barChart.render();
                    }
                },

                renderDonutToko(metrics) {
                    var options = {
                        series: [
                            parseInt(metrics.toko_hijau) || 0,
                            parseInt(metrics.toko_kuning) || 0,
                            parseInt(metrics.toko_merah) || 0
                        ],
                        chart: {
                            type: 'donut',
                            height: 180,
                            fontFamily: 'inherit',
                            animations: { enabled: true }
                        },
                        labels: ['Hijau (≥100%)', 'Kuning (80-99%)', 'Merah (<80%)'],
                        colors: ['#22c55e', '#eab308', '#ef4444'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        name: { show: false },
                                        value: {
                                            show: true,
                                            fontSize: '18px',
                                            fontWeight: 'bold',
                                            color: this.textColor,
                                            formatter: function (val) { return Number(val).toLocaleString('id-ID'); }
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: 'Total Toko',
                                            color: this.textColor,
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                expandOnClick: false
                            }
                        },
                        stroke: { width: 0 },
                        dataLabels: { enabled: false },
                        legend: {
                            position: 'bottom',
                            fontSize: '11px',
                            labels: { colors: this.textColor },
                            markers: { radius: 12 }
                        },
                        tooltip: {
                            theme: this.isDarkMode ? 'dark' : 'light',
                            y: {
                                formatter: function (val, { w }) {
                                    const total = (w?.globals?.seriesTotals || []).reduce((a, b) => a + b, 0);
                                    const pct = total > 0
                                        ? ((val / total) * 100).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                                        : '0,0';
                                    return `${Number(val).toLocaleString('id-ID')} Toko (${pct}%)`;
                                }
                            }
                        }
                    };
                    const container = document.querySelector("#donut-toko");
                    if (container) {
                        this.donutToko = new ApexCharts(container, options);
                        this.donutToko.render();
                    }
                },

                renderDonutSkb(metrics) {
                    const total_cb = parseInt(metrics.total_toko_cb) || 0;
                    const approved = parseInt(metrics.skb_approve) || 0;
                    const rejected = parseInt(metrics.skb_reject) || 0;
                    const pending = Math.max(0, total_cb - (approved + rejected));

                    var options = {
                        series: [approved, rejected, pending],
                        chart: {
                            type: 'donut',
                            height: 180,
                            fontFamily: 'inherit',
                            animations: { enabled: true }
                        },
                        labels: ['Approved', 'Rejected', 'Pending/Belum (CB)'],
                        colors: ['#3b82f6', '#ef4444', '#94a3b8'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        name: { show: false },
                                        value: {
                                            show: true,
                                            fontSize: '18px',
                                            fontWeight: 'bold',
                                            color: this.textColor,
                                            formatter: function (val) { return Number(val).toLocaleString('id-ID'); }
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: 'Wajib SKB (CB)',
                                            color: this.textColor,
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                expandOnClick: false
                            }
                        },
                        stroke: { width: 0 },
                        dataLabels: { enabled: false },
                        legend: {
                            position: 'bottom',
                            fontSize: '11px',
                            labels: { colors: this.textColor },
                            markers: { radius: 12 }
                        },
                        tooltip: {
                            theme: this.isDarkMode ? 'dark' : 'light',
                            y: {
                                formatter: function (val, { w }) {
                                    const total = (w?.globals?.seriesTotals || []).reduce((a, b) => a + b, 0);
                                    const pct = total > 0
                                        ? ((val / total) * 100).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })
                                        : '0,0';
                                    return `${Number(val).toLocaleString('id-ID')} Toko (${pct}%)`;
                                }
                            }
                        }
                    };
                    const container = document.querySelector("#donut-skb");
                    if (container) {
                        this.donutSkb = new ApexCharts(container, options);
                        this.donutSkb.render();
                    }
                }
            };
        };
    </script>
</div>
