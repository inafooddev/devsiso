<div x-data="nationalTrendlineComponent(@js($trendData))" class="bg-base-100 border border-base-300 rounded-2xl shadow-md overflow-hidden mb-6">
    {{-- Card Header --}}
    <div class="p-4 md:p-5 bg-gradient-to-r from-base-200/90 via-base-100 to-base-200/90 border-b border-base-200 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary">
                <x-heroicon-o-arrow-trending-up class="w-6 h-6" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-base md:text-lg tracking-tight text-base-content" 
                        x-text="selectedKpi === 'total' ? 'TRENDLINE PENCAPAIAN TOTAL KPI NASIONAL (% ACH)' : (selectedKpi === 'ipt' ? 'TRENDLINE: ' + selectedKpiLabel.toUpperCase() + ' (NILAI AKTUAL)' : 'TRENDLINE: ' + selectedKpiLabel.toUpperCase() + ' (% ACH)')"></h3>
                    <span class="badge badge-warning badge-sm font-bold">Nasional</span>
                </div>
                <p class="text-xs text-base-content/70 mt-0.5" 
                   x-text="selectedKpi === 'total' ? 'Pergerakan performa gabungan berbobot 100% sepanjang 12 bulan' : (selectedKpi === 'ipt' ? 'Pergerakan nilai absolut item per transaksi terhadap target 8.00' : 'Pergerakan pencapaian indikator terhadap target 100%')"></p>
            </div>
        </div>

        {{-- Stat Pills & Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
            {{-- YTD Avg Stat Pill --}}
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-base-100 border border-base-300 shadow-xs">
                <span class="text-[11px] font-semibold text-base-content/60 uppercase">Rata-rata:</span>
                <span class="text-sm font-black" :class="currentAvgStatusClass" x-text="currentAvgFormatted"></span>
            </div>

            {{-- Highest Month Pill --}}
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-base-100 border border-base-300 shadow-xs">
                <span class="text-[11px] font-semibold text-base-content/60 uppercase">Tertinggi:</span>
                <span class="text-xs font-bold text-success" x-text="currentHighest"></span>
            </div>

            {{-- Reset Button if specific KPI selected --}}
            <template x-if="selectedKpi !== 'total'">
                <button @click="resetToTotal()" class="btn btn-xs btn-outline btn-primary rounded-lg gap-1">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    <span>Reset ke Total KPI</span>
                </button>
            </template>

            {{-- Expand Detail per KPI Button --}}
            <button @click="toggleExpand()" class="btn btn-sm btn-ghost border border-base-300 rounded-xl hover:bg-base-200 transition-colors gap-2">
                <x-heroicon-o-squares-2x2 class="w-4 h-4 text-primary" />
                <span class="text-xs font-bold" x-text="expandKpis ? 'Tutup Detail KPI' : 'Detail 7 KPI'"></span>
                <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-300 text-base-content/70" x-bind:class="expandKpis ? 'rotate-180' : ''" />
            </button>
        </div>
    </div>

    {{-- Main Chart Canvas Area --}}
    <div class="p-4 md:p-5 relative">
        <div class="flex items-center justify-between text-xs text-base-content/60 mb-2 px-1">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5 font-medium">
                    <span class="w-2.5 h-2.5 rounded-full" :style="'background-color: ' + currentChartColor"></span>
                    <span x-text="selectedKpi === 'total' ? 'Total Score (% Ach)' : (selectedKpi === 'ipt' ? selectedKpiLabel + ' (Nilai Aktual)' : selectedKpiLabel + ' (% Ach)')"></span>
                </div>
                <div class="flex items-center gap-1.5 font-medium text-error">
                    <span class="w-3 h-0.5 bg-error border-t border-dashed border-error"></span>
                    <span x-text="selectedKpi === 'ipt' ? 'Target Acuan (8.00)' : 'Target Acuan (100%)'"></span>
                </div>
            </div>
            <span class="text-[11px] italic hidden sm:inline">Pilih KPI di bawah untuk memfokuskan grafik</span>
        </div>

        <div wire:ignore class="w-full">
            <div x-ref="mainTrendChart" class="w-full" style="min-height: 275px;"></div>
        </div>
    </div>

    {{-- Expandable Detail per KPI Section --}}
    <div x-show="expandKpis" x-collapse x-cloak class="border-t border-base-200 bg-base-200/40 p-4 md:p-5">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
            <div>
                <h4 class="font-bold text-sm tracking-tight uppercase flex items-center gap-2">
                    <span>DETAIL TREN PENCAPAIAN PER KPI</span>
                    <span class="badge badge-sm badge-neutral font-semibold">7 Indikator</span>
                </h4>
                <p class="text-xs text-base-content/60 mt-0.5">Klik salah satu card untuk menampilkan grafik detailnya di chart utama</p>
            </div>
            <div class="text-[11px] text-base-content/50 italic">
                *KPI IPT menampilkan angka absolut (nilai aktual), indikator lainnya persentase (% Ach)
            </div>
        </div>

        {{-- Grid of 7 KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($trendData['kpis'] as $kpiKey => $kpi)
                <div @click="selectKpi('{{ $kpiKey }}')" 
                     class="cursor-pointer bg-base-100 border rounded-xl p-4 shadow-xs transition-all duration-200 hover:shadow-md hover:scale-[1.01]"
                     :class="selectedKpi === '{{ $kpiKey }}' ? 'border-primary ring-2 ring-primary/20 bg-primary/5' : 'border-base-300 hover:border-primary/40'">
                    
                    {{-- KPI Card Header --}}
                    <div class="flex justify-between items-start gap-2 mb-2">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-bold text-base-content block truncate" title="{{ $kpi['label'] }}">{{ $kpi['short'] }}</span>
                            <span class="badge badge-xs badge-ghost font-medium text-[10px] mt-0.5">Bobot: {{ $kpi['bobot'] }}%</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-sm font-black {{ $kpi['avg_ach'] >= 100 ? 'text-success' : ($kpi['avg_ach'] >= 80 ? 'text-warning' : 'text-error') }}">
                                @if($kpiKey === 'ipt')
                                    {{ number_format($kpi['avg_actual'] ?? $kpi['avg_ach'], 2, ',', '.') }}
                                @else
                                    {{ $kpi['avg_ach'] }}%
                                @endif
                            </span>
                            <span class="block text-[9px] text-base-content/50 uppercase font-semibold">
                                {{ $kpiKey === 'ipt' ? 'Rata-rata Nilai' : 'Rata-rata' }}
                            </span>
                        </div>
                    </div>

                    {{-- Mini Sparkline Chart --}}
                    <div class="w-full mt-2" wire:ignore>
                        <div id="mini-chart-{{ $kpiKey }}" class="w-full" style="height: 90px;"></div>
                    </div>

                    {{-- Card Footer Details --}}
                    <div class="mt-2 pt-2 border-t border-base-200/60 flex justify-between items-center text-[10px] text-base-content/60 font-medium">
                        @if($kpiKey === 'ipt')
                            <span>Min: {{ number_format(min($kpi['actual_series']), 2, ',', '.') }}</span>
                            <span>Max: {{ number_format(max($kpi['actual_series']), 2, ',', '.') }}</span>
                        @else
                            <span>Min: {{ min($kpi['ach_series']) }}%</span>
                            <span>Max: {{ max($kpi['ach_series']) }}%</span>
                        @endif
                        <span class="text-primary font-bold" x-show="selectedKpi === '{{ $kpiKey }}'">● Terpilih</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    if (typeof window.nationalTrendlineComponent === 'undefined') {
        window.nationalTrendlineComponent = function(trendData) {
            return {
                expandKpis: false,
                mainChart: null,
                miniCharts: {},
                selectedKpi: 'total',

                get selectedKpiLabel() {
                    if (this.selectedKpi === 'total' || !trendData.kpis[this.selectedKpi]) {
                        return 'Total KPI';
                    }
                    return trendData.kpis[this.selectedKpi].short;
                },

                get currentAvgFormatted() {
                    if (this.selectedKpi === 'total' || !trendData.kpis[this.selectedKpi]) {
                        return trendData.ytd_avg + '%';
                    }
                    if (this.selectedKpi === 'ipt') {
                        return Number(trendData.kpis.ipt.avg_actual || 0).toFixed(2);
                    }
                    return trendData.kpis[this.selectedKpi].avg_ach + '%';
                },

                get currentAvgStatusClass() {
                    let ach = (this.selectedKpi === 'total' || !trendData.kpis[this.selectedKpi]) 
                        ? trendData.ytd_avg 
                        : trendData.kpis[this.selectedKpi].avg_ach;
                    if (ach >= 100) return 'text-success';
                    if (ach >= 80) return 'text-warning';
                    return 'text-error';
                },

                get currentHighest() {
                    if (this.selectedKpi === 'total' || !trendData.kpis[this.selectedKpi]) {
                        return trendData.highest_month.month + ' (' + trendData.highest_month.val + '%)';
                    }
                    const kpi = trendData.kpis[this.selectedKpi];
                    let maxVal = -1, maxMonth = '-';
                    let series = (this.selectedKpi === 'ipt') ? kpi.actual_series : kpi.ach_series;
                    series.forEach((val, idx) => {
                        if (val > maxVal) { maxVal = val; maxMonth = trendData.categories[idx]; }
                    });
                    let suffix = (this.selectedKpi === 'ipt') ? '' : '%';
                    let formattedVal = (this.selectedKpi === 'ipt') ? Number(maxVal).toFixed(2) : maxVal;
                    return maxMonth + ' (' + formattedVal + suffix + ')';
                },

                get currentChartColor() {
                    if (this.selectedKpi === 'total' || !trendData.kpis[this.selectedKpi]) {
                        return '#3b82f6';
                    }
                    return trendData.kpis[this.selectedKpi].color;
                },

                init() {
                    this.$nextTick(() => {
                        this.initMainChart();
                    });
                },

                toggleExpand() {
                    this.expandKpis = !this.expandKpis;
                    if (this.expandKpis) {
                        this.$nextTick(() => {
                            setTimeout(() => { this.initMiniCharts(); }, 150);
                        });
                    }
                },

                selectKpi(kpiKey) {
                    if (this.selectedKpi === kpiKey) {
                        this.selectedKpi = 'total';
                    } else {
                        this.selectedKpi = kpiKey;
                    }
                    this.updateMainChart();
                },

                resetToTotal() {
                    this.selectedKpi = 'total';
                    this.updateMainChart();
                },

                initMainChart() {
                    const el = this.$refs.mainTrendChart;
                    if (!el || typeof ApexCharts === 'undefined') return;

                    if (this.mainChart) {
                        this.mainChart.destroy();
                    }

                    this.mainChart = new ApexCharts(el, this.getMainChartOptions());
                    this.mainChart.render();
                },

                updateMainChart() {
                    if (!this.mainChart) return;
                    this.mainChart.updateOptions(this.getMainChartOptions(), true, true);
                },

                getMainChartOptions() {
                    let isIpt = (this.selectedKpi === 'ipt');
                    let seriesName = 'Total Score (% Ach)';
                    let seriesData = trendData.total_score_series;
                    let chartColor = this.currentChartColor;
                    let targetVal = 100;
                    let targetLabel = 'Target 100%';

                    if (this.selectedKpi !== 'total' && trendData.kpis[this.selectedKpi]) {
                        const kpi = trendData.kpis[this.selectedKpi];
                        if (isIpt) {
                            seriesName = kpi.short + ' (Nilai Aktual)';
                            seriesData = kpi.actual_series;
                            targetVal = 8.00;
                            targetLabel = 'Target 8.00';
                        } else {
                            seriesName = kpi.short + ' (% Ach)';
                            seriesData = kpi.ach_series;
                        }
                    }

                    return {
                        chart: {
                            type: 'area',
                            height: 275,
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            fontFamily: 'inherit',
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 400
                            }
                        },
                        series: [{
                            name: seriesName,
                            data: seriesData
                        }],
                        colors: [chartColor],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.05,
                                stops: [0, 95, 100]
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        markers: {
                            size: 4,
                            strokeWidth: 2,
                            hover: { size: 6 }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                if (val === null || val === undefined) return '';
                                return isIpt ? Number(val).toFixed(2) : val + '%';
                            },
                            offsetY: -8,
                            style: {
                                fontSize: '10px',
                                fontFamily: 'inherit',
                                fontWeight: 700,
                                colors: [chartColor]
                            },
                            background: {
                                enabled: true,
                                foreColor: '#ffffff',
                                borderRadius: 4,
                                padding: 3,
                                borderWidth: 0,
                                opacity: 0.9,
                                dropShadow: { enabled: false }
                            }
                        },
                        xaxis: {
                            categories: trendData.categories,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: {
                                    colors: '#64748b',
                                    fontSize: '11px',
                                    fontWeight: 600
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: (val) => isIpt ? Number(val).toFixed(2) : Math.round(val) + '%',
                                style: {
                                    colors: '#64748b',
                                    fontSize: '11px'
                                }
                            }
                        },
                        grid: {
                            borderColor: 'rgba(148, 163, 184, 0.15)',
                            strokeDashArray: 4,
                            padding: { left: 15, right: 15, top: 18, bottom: 0 }
                        },
                        annotations: {
                            yaxis: [{
                                y: targetVal,
                                borderColor: '#ef4444',
                                strokeDashArray: 4,
                                label: {
                                    borderColor: '#ef4444',
                                    style: {
                                        color: '#fff',
                                        background: '#ef4444',
                                        fontSize: '10px',
                                        fontWeight: 600
                                    },
                                    text: targetLabel
                                }
                            }]
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: (val) => isIpt ? Number(val).toFixed(2) : val + '%'
                            }
                        }
                    };
                },

                initMiniCharts() {
                    if (typeof ApexCharts === 'undefined') return;

                    Object.entries(trendData.kpis).forEach(([key, kpi]) => {
                        const el = document.getElementById('mini-chart-' + key);
                        if (!el) return;

                        if (this.miniCharts[key]) {
                            this.miniCharts[key].destroy();
                        }

                        const isIpt = (kpi.key === 'ipt');
                        const seriesData = isIpt ? kpi.actual_series : kpi.ach_series;
                        const seriesName = isIpt ? kpi.short + ' (Nilai)' : kpi.short;

                        const options = {
                            chart: {
                                type: 'area',
                                height: 90,
                                sparkline: { enabled: true },
                                animations: { enabled: true }
                            },
                            series: [{
                                name: seriesName,
                                data: seriesData
                            }],
                            colors: [kpi.color],
                            stroke: {
                                curve: 'smooth',
                                width: 2
                            },
                            markers: {
                                size: 2.5,
                                strokeWidth: 1,
                                hover: { size: 4.5 }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    if (val === null || val === undefined) return '';
                                    return isIpt ? Number(val).toFixed(2) : Math.round(val) + '%';
                                },
                                offsetY: -4,
                                style: {
                                    fontSize: '8px',
                                    fontFamily: 'inherit',
                                    fontWeight: 700,
                                    colors: [kpi.color]
                                },
                                background: {
                                    enabled: true,
                                    foreColor: '#ffffff',
                                    borderRadius: 3,
                                    padding: 2,
                                    borderWidth: 0,
                                    opacity: 0.9,
                                    dropShadow: { enabled: false }
                                }
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.35,
                                    opacityTo: 0.05,
                                    stops: [0, 100]
                                }
                            },
                            grid: {
                                padding: {
                                    top: 15,
                                    bottom: 5,
                                    left: 4,
                                    right: 4
                                }
                            },
                            tooltip: {
                                fixed: { enabled: false },
                                x: {
                                    show: true,
                                    formatter: (val, opts) => trendData.categories[opts.dataPointIndex]
                                },
                                y: {
                                    formatter: (val) => isIpt ? Number(val).toFixed(2) : val + '%'
                                },
                                marker: { show: false }
                            }
                        };

                        this.miniCharts[key] = new ApexCharts(el, options);
                        this.miniCharts[key].render();
                    });
                }
            };
        };
    }
</script>
