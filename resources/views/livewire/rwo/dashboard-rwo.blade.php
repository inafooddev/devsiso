<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
     x-data="dashboardCharts()"
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
        </div>
    </div>

    {{-- Toolbar / Filters --}}
    <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-300 shrink-0 flex flex-wrap items-end gap-3">
        <div class="form-control">
            <label class="label py-1"><span class="label-text text-xs font-medium">Kuartal</span></label>
            <select wire:model.live="kuartal" class="select select-bordered select-sm w-full min-w-[120px] bg-base-100 text-sm">
                @foreach($kuartals as $q)
                    <option value="{{ $q->quarter }}">Q{{ $q->quarter }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-control">
            <label class="label py-1"><span class="label-text text-xs font-medium">Region</span></label>
            <select wire:model.live="region" class="select select-bordered select-sm w-full min-w-[150px] bg-base-100 text-sm">
                <option value="">Semua Region</option>
                @foreach($regions as $r)
                    <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-control">
            <label class="label py-1"><span class="label-text text-xs font-medium">Area</span></label>
            <select wire:model.live="area" class="select select-bordered select-sm w-full min-w-[150px] bg-base-100 text-sm" @if(empty($region)) disabled @endif>
                <option value="">Semua Area</option>
                @foreach($areas as $a)
                    <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Active Month Badge --}}
        @if(!empty($activeMonths))
            <div class="hidden sm:flex items-center gap-1 mb-1">
                <span class="text-xs font-semibold text-base-content/50">Bulan aktif:</span>
                @foreach($activeMonths as $m)
                    <span class="badge badge-sm badge-outline badge-primary font-medium">{{ $m }}</span>
                @endforeach
            </div>
        @endif
        
        <div class="ml-auto flex items-center gap-2">
            <div wire:loading class="loading loading-spinner loading-sm text-primary"></div>
        </div>
    </div>

    {{-- Dashboard Content --}}
    <div class="flex-1 min-h-0 min-w-0 overflow-y-auto pb-8 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none">
        
        @php
            $totalToko = $metrics['total_toko'] ?? 0;
            $tokoTransaksi = $metrics['toko_transaksi'] ?? 0;
            $tokoTransaksiPct = $totalToko > 0 ? ($tokoTransaksi / $totalToko) * 100 : 0;

            $totalTarget = $metrics['target_prorata'] ?? 0;
            $totalAchievement = $metrics['total_achievement'] ?? 0;
            $overallPct = $totalTarget > 0 ? ($totalAchievement / $totalTarget) * 100 : 0;

            $tokoHijau = $metrics['toko_hijau'] ?? 0;
            $tokoHijauPct = $totalToko > 0 ? ($tokoHijau / $totalToko) * 100 : 0;

            $tokoKuning = $metrics['toko_kuning'] ?? 0;
            $tokoKuningPct = $totalToko > 0 ? ($tokoKuning / $totalToko) * 100 : 0;

            $tokoMerah = $metrics['toko_merah'] ?? 0;
            $tokoMerahPct = $totalToko > 0 ? ($tokoMerah / $totalToko) * 100 : 0;
        @endphp

        {{-- Top Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-4">
            
            {{-- Card 1: Toko & Transaksi --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <span class="text-indigo-100 text-[10px] font-bold uppercase tracking-wider">Total Transaksi / Toko</span>
                    <div class="mt-1 text-2xl font-extrabold">{{ number_format($tokoTransaksi, 0, ',', '.') }}</div>
                    <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                        <div class="flex justify-between items-center text-indigo-100">
                            <span>Toko: {{ number_format($totalToko, 0, ',', '.') }}</span>
                            <span>{{ number_format($tokoTransaksiPct, 1, ',', '.') }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1 mt-0.5">
                            <div class="bg-white h-1 rounded-full" style="width: {{ min($tokoTransaksiPct, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Target & Pencapaian --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
                <div class="relative z-10 flex flex-col h-full">
                    <span class="text-blue-100 text-[10px] font-bold uppercase tracking-wider">Target & Pencapaian</span>
                    <div class="mt-1 text-lg sm:text-xl font-extrabold" title="Rp {{ number_format($totalAchievement, 0, ',', '.') }}">Rp {{ number_format($totalAchievement, 0, ',', '.') }}</div>
                    <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                        <div class="flex justify-between items-center text-blue-100">
                            <span>Trg: Rp {{ number_format($totalTarget, 0, ',', '.') }}</span>
                            <span>{{ number_format($overallPct, 1, ',', '.') }}%</span>
                        </div>
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

        @if($totalToko == 0)
            {{-- Empty State --}}
            <div class="bg-base-100 p-8 rounded-xl border border-base-300 text-center my-6 flex flex-col items-center justify-center">
                <svg class="h-12 w-12 text-base-content/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-base-content">Tidak Ada Data Potensi</h3>
                <p class="mt-1 text-xs text-base-content/60 max-w-md">Tidak ditemukan toko potensi untuk filter Kuartal/Region/Area yang dipilih. Coba ganti filter Anda.</p>
            </div>
        @else
            {{-- Charts Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                
                {{-- Main Bar Chart --}}
                <div class="lg:col-span-2 bg-base-100 p-4 rounded-xl shadow-md border border-base-200 flex flex-col">
                    <h3 class="text-base-content font-bold mb-2 text-sm">Target vs Achievement (<span x-text="groupName"></span>)</h3>
                    <div id="bar-chart" class="w-full flex-1 min-h-[350px] lg:min-h-0" wire:ignore></div>
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
                        <h3 class="text-base-content font-bold mb-1 text-sm">Approval SKB</h3>
                        <div id="donut-skb" class="w-full h-[180px] mt-auto" wire:ignore></div>
                    </div>
                </div>

            </div>
        @endif
    </div>

    {{-- Script injection for ApexCharts --}}
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardCharts', () => ({
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

                initCharts(metrics, chartData) {
                    // Clean up before starting
                    this.destroyCharts();

                    // Observe theme change
                    if(this.themeObserver) {
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

                updateCharts(metrics, chartData) {
                    if (parseInt(metrics.total_toko) === 0) {
                        this.destroyCharts();
                        return;
                    }

                    this.groupName = chartData.groupName || 'Region';
                    
                    // Recreate bar chart to smoothly switch horizontal/vertical orientations
                    if (this.barChart) {
                        try { this.barChart.destroy(); } catch(e){}
                        this.barChart = null;
                        const el = document.querySelector("#bar-chart");
                        if(el) el.innerHTML = '';
                    }
                    this.renderBarChart(chartData);
                    
                    if(this.donutToko) {
                        this.donutToko.updateSeries([
                            parseInt(metrics.toko_hijau) || 0, 
                            parseInt(metrics.toko_kuning) || 0, 
                            parseInt(metrics.toko_merah) || 0
                        ]);
                    } else {
                        this.renderDonutToko(metrics);
                    }

                    if(this.donutSkb) {
                        const total_toko = parseInt(metrics.total_toko) || 0;
                        const approved = parseInt(metrics.skb_approve) || 0;
                        const rejected = parseInt(metrics.skb_reject) || 0;
                        const pending = total_toko - (approved + rejected);
                        this.donutSkb.updateSeries([approved, rejected, pending > 0 ? pending : 0]);
                    } else {
                        this.renderDonutSkb(metrics);
                    }
                },

                handleThemeChange() {
                    const textColor = this.textColor;
                    const gridColor = this.isDarkMode ? '#334155' : '#e2e8f0';
                    const tooltipTheme = this.isDarkMode ? 'dark' : 'light';
                    
                    if(this.barChart) {
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

                    if(this.donutToko) {
                        this.donutToko.updateOptions(donutUpdateOptions, false, false);
                    }
                    if(this.donutSkb) {
                        this.donutSkb.updateOptions(donutUpdateOptions, false, false);
                    }
                },

                destroyCharts() {
                    if(this.barChart) {
                        try { this.barChart.destroy(); } catch(e){}
                        this.barChart = null;
                    }
                    if(this.donutToko) {
                        try { this.donutToko.destroy(); } catch(e){}
                        this.donutToko = null;
                    }
                    if(this.donutSkb) {
                        try { this.donutSkb.destroy(); } catch(e){}
                        this.donutSkb = null;
                    }
                    document.querySelectorAll('#bar-chart, #donut-toko, #donut-skb').forEach(el => {
                        if (el) el.innerHTML = '';
                    });
                },

                renderBarChart(data) {
                    const isHorizontal = data.categories.length > 6;
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
                            animations: { enabled: true, easing: 'easeinout', speed: 800 }
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
                                formatter: isHorizontal ? (val) => "Rp " + (val / 1000000).toFixed(0) + "M" : undefined
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: { 
                            labels: { 
                                style: { colors: this.textColor },
                                formatter: isHorizontal ? undefined : (val) => "Rp " + (val / 1000000).toFixed(0) + "M" 
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
                            y: { formatter: function (val) { return "Rp " + val.toLocaleString('id-ID') } }
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
                                            formatter: function (val) { return val }
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: 'Total Toko',
                                            color: this.textColor,
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
                        tooltip: { theme: this.isDarkMode ? 'dark' : 'light' }
                    };
                    const container = document.querySelector("#donut-toko");
                    if (container) {
                        this.donutToko = new ApexCharts(container, options);
                        this.donutToko.render();
                    }
                },

                renderDonutSkb(metrics) {
                    const total_toko = parseInt(metrics.total_toko) || 0;
                    const approved = parseInt(metrics.skb_approve) || 0;
                    const rejected = parseInt(metrics.skb_reject) || 0;
                    const pending = total_toko - (approved + rejected);

                    var options = {
                        series: [approved, rejected, pending > 0 ? pending : 0],
                        chart: { 
                            type: 'donut', 
                            height: 180, 
                            fontFamily: 'inherit',
                            animations: { enabled: true }
                        },
                        labels: ['Approved', 'Rejected', 'Pending/Belum'],
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
                                            formatter: function (val) { return val }
                                        },
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            label: 'Total SKB',
                                            color: this.textColor,
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
                        tooltip: { theme: this.isDarkMode ? 'dark' : 'light' }
                    };
                    const container = document.querySelector("#donut-skb");
                    if (container) {
                        this.donutSkb = new ApexCharts(container, options);
                        this.donutSkb.render();
                    }
                }
            }));
        });
    </script>
    @endpush
</div>
