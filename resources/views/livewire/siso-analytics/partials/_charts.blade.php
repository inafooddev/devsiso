{{-- APEXCHARTS ANALYTICAL SUITE --}}
<div x-data="{
    chartMonthlyData: @js($chartMonthly),
    chartBrandData: @js($chartBrand),
    chartRegionData: @js($chartRegion),
    chartTopSkuData: @js($chartTopSku),
    unitMode: @js($unitMode),

    chartMonthlyInstance: null,
    chartBrandInstance: null,
    chartRegionInstance: null,
    chartTopSkuInstance: null,

    init() {
        if (typeof ApexCharts === 'undefined') {
            let script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
            script.onload = () => this.renderAllCharts();
            document.head.appendChild(script);
        } else {
            this.$nextTick(() => {
                this.renderAllCharts();
            });
        }

        this.$watch('chartMonthlyData', () => this.renderAllCharts());
        this.$watch('chartBrandData', () => this.renderAllCharts());
        this.$watch('chartRegionData', () => this.renderAllCharts());
        this.$watch('chartTopSkuData', () => this.renderAllCharts());
    },

    formatValue(val) {
        if (this.unitMode === 'val') {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
        }
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(val) + ' Ktn';
    },

    renderAllCharts() {
        if (typeof ApexCharts === 'undefined') return;

        this.renderMonthlyChart();
        this.renderBrandChart();
        this.renderRegionChart();
        this.renderTopSkuChart();
    },

    renderMonthlyChart() {
        if (!this.$refs.monthlyChart) return;
        if (this.chartMonthlyInstance) this.chartMonthlyInstance.destroy();

        const isVal = this.unitMode === 'val';
        const unitLabel = isVal ? 'Rp' : 'Karton';

        let series = [];
        let yaxis = [];
        let colors = [];
        let strokeWidth = [];

        if (isVal) {
            // Mode Rupiah: 3 Kolom Batang (Sell In, Sell Out, Target) + 2 Garis (Pencapaian Target %, Rasio Out/In %)
            series = [
                {
                    name: 'Sell In',
                    type: 'column',
                    data: this.chartMonthlyData.seriesIn
                },
                {
                    name: 'Sell Out',
                    type: 'column',
                    data: this.chartMonthlyData.seriesOut
                },
                {
                    name: 'Target',
                    type: 'column',
                    data: this.chartMonthlyData.seriesTarget || []
                },
                {
                    name: '% Pencapaian Target',
                    type: 'line',
                    data: this.chartMonthlyData.seriesAchIn || []
                },
                {
                    name: 'Rasio Out/In (%)',
                    type: 'line',
                    data: this.chartMonthlyData.seriesRatio
                }
            ];

            colors = ['#3b82f6', '#10b981', '#8b5cf6', '#06b6d4', '#f59e0b'];
            strokeWidth = [0, 0, 0, 3, 2];

            yaxis = [
                {
                    seriesName: 'Sell In',
                    title: {
                        text: 'Nilai Penjualan & Target (' + unitLabel + ')',
                        style: { fontSize: '11px', fontWeight: 600, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }
                    },
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                        formatter: (val) => (val / 1000000).toFixed(0) + ' Jt'
                    }
                },
                {
                    seriesName: 'Sell In',
                    show: false
                },
                {
                    seriesName: 'Sell In',
                    show: false
                },
                {
                    seriesName: '% Pencapaian Target',
                    opposite: true,
                    title: {
                        text: 'Pencapaian & Rasio (%)',
                        style: { fontSize: '11px', fontWeight: 600, color: '#06b6d4', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }
                    },
                    min: 0,
                    max: 200,
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                        formatter: (val) => val != null ? val.toFixed(0) + '%' : '-'
                    }
                },
                {
                    seriesName: '% Pencapaian Target',
                    opposite: true,
                    show: false
                }
            ];
        } else {
            // Mode Karton: 2 Kolom Batang (Sell In, Sell Out) + 1 Garis Rasio
            series = [
                {
                    name: 'Sell In',
                    type: 'column',
                    data: this.chartMonthlyData.seriesIn
                },
                {
                    name: 'Sell Out',
                    type: 'column',
                    data: this.chartMonthlyData.seriesOut
                },
                {
                    name: 'Rasio Out/In (%)',
                    type: 'line',
                    data: this.chartMonthlyData.seriesRatio
                }
            ];

            colors = ['#3b82f6', '#10b981', '#f59e0b'];
            strokeWidth = [0, 0, 3];

            yaxis = [
                {
                    seriesName: 'Sell In',
                    title: {
                        text: 'Volume (' + unitLabel + ')',
                        style: { fontSize: '11px', fontWeight: 600, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }
                    },
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                        formatter: (val) => (val / 1000).toFixed(0) + 'k'
                    }
                },
                {
                    seriesName: 'Sell In',
                    show: false
                },
                {
                    seriesName: 'Rasio Out/In (%)',
                    opposite: true,
                    title: {
                        text: 'Rasio (%)',
                        style: { fontSize: '11px', fontWeight: 600, color: '#f59e0b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }
                    },
                    min: 0,
                    max: 200,
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                        formatter: (val) => val != null ? val.toFixed(0) + '%' : '-'
                    }
                }
            ];
        }

        const options = {
            series: series,
            chart: {
                height: 320,
                type: 'line',
                stacked: false,
                toolbar: { show: true },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif',
                animations: { enabled: true }
            },
            stroke: {
                width: strokeWidth,
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    columnWidth: isVal ? '65%' : '50%',
                    borderRadius: 4
                }
            },
            colors: colors,
            labels: this.chartMonthlyData.categories,
            xaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }
                }
            },
            yaxis: yaxis,
            tooltip: {
                shared: true,
                intersect: false,
                style: {
                    fontSize: '12px',
                    fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
                },
                y: {
                    formatter: (y, { seriesIndex }) => {
                        if (isVal) {
                            if (seriesIndex === 3) {
                                return (y !== null && y !== undefined) ? y.toFixed(1) + '% dari Target' : '-';
                            }
                            if (seriesIndex === 4) {
                                return (y !== null && y !== undefined) ? y.toFixed(1) + '%' : '-';
                            }
                            return this.formatValue(y || 0);
                        } else {
                            if (seriesIndex === 2) {
                                return (y !== null && y !== undefined) ? y.toFixed(1) + '%' : '-';
                            }
                            return this.formatValue(y || 0);
                        }
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '12px',
                fontWeight: 500,
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            annotations: {
                yaxis: [{
                    y: 100,
                    yAxisIndex: isVal ? 3 : 2,
                    borderColor: '#f59e0b',
                    strokeDashArray: 4,
                    label: {
                        borderColor: '#f59e0b',
                        style: {
                            color: '#fff',
                            background: '#f59e0b',
                            fontSize: '11px',
                            fontWeight: 600,
                            fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
                        },
                        text: '100% (Target / Seimbang)'
                    }
                }]
            }
        };

        this.chartMonthlyInstance = new ApexCharts(this.$refs.monthlyChart, options);
        this.chartMonthlyInstance.render();
    },

    renderBrandChart() {
        if (!this.$refs.brandChart) return;
        if (this.chartBrandInstance) this.chartBrandInstance.destroy();

        const options = {
            series: [
                { name: 'Sell In', data: this.chartBrandData.seriesIn },
                { name: 'Sell Out', data: this.chartBrandData.seriesOut }
            ],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    dataLabels: { position: 'top' }
                }
            },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: this.chartBrandData.categories,
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => {
                        if (this.unitMode === 'val') return (val / 1000000).toFixed(0) + ' Jt';
                        return (val / 1000).toFixed(0) + 'k';
                    }
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }
                }
            },
            legend: { position: 'top', fontSize: '12px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
            tooltip: {
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                y: { formatter: (val) => this.formatValue(val) }
            }
        };

        this.chartBrandInstance = new ApexCharts(this.$refs.brandChart, options);
        this.chartBrandInstance.render();
    },

    renderRegionChart() {
        if (!this.$refs.regionChart) return;
        if (this.chartRegionInstance) this.chartRegionInstance.destroy();

        const options = {
            series: [
                { name: 'Sell In', data: this.chartRegionData.seriesIn },
                { name: 'Sell Out', data: this.chartRegionData.seriesOut }
            ],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 3
                }
            },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: this.chartRegionData.categories,
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    rotate: -20
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => {
                        if (this.unitMode === 'val') return (val / 1000000).toFixed(0) + ' Jt';
                        return (val / 1000).toFixed(0) + 'k';
                    }
                }
            },
            legend: { position: 'top', fontSize: '12px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
            tooltip: {
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                y: { formatter: (val) => this.formatValue(val) }
            }
        };

        this.chartRegionInstance = new ApexCharts(this.$refs.regionChart, options);
        this.chartRegionInstance.render();
    },

    renderTopSkuChart() {
        if (!this.$refs.topSkuChart) return;
        if (this.chartTopSkuInstance) this.chartTopSkuInstance.destroy();

        const options = {
            series: [
                { name: 'Sell In', data: this.chartTopSkuData.seriesIn },
                { name: 'Sell Out', data: this.chartTopSkuData.seriesOut }
            ],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    borderRadius: 3
                }
            },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: this.chartTopSkuData.categories,
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    rotate: -25
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => {
                        if (this.unitMode === 'val') return (val / 1000000).toFixed(0) + ' Jt';
                        return (val / 1000).toFixed(0) + 'k';
                    }
                }
            },
            legend: { position: 'top', fontSize: '12px', fontWeight: 500, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
            tooltip: {
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                y: { formatter: (val) => this.formatValue(val) }
            }
        };

        this.chartTopSkuInstance = new ApexCharts(this.$refs.topSkuChart, options);
        this.chartTopSkuInstance.render();
    },

    destroy() {
        if (this.chartMonthlyInstance) this.chartMonthlyInstance.destroy();
        if (this.chartBrandInstance) this.chartBrandInstance.destroy();
        if (this.chartRegionInstance) this.chartRegionInstance.destroy();
        if (this.chartTopSkuInstance) this.chartTopSkuInstance.destroy();
    }
}" 
wire:key="siso-charts-suite-{{ $selectedYear }}-{{ $selectedMonth }}-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}-{{ $selectedBrand }}-{{ $unitMode }}"
class="flex flex-col gap-4 w-full">

    {{-- CHART 1: TREN BULANAN KOMPARASI SELL IN VS SELL OUT (FULL WIDTH) --}}
    <div class="bg-base-100 rounded-xl p-4 md:p-5 border border-base-300 shadow-sm flex flex-col">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-base-200 gap-2">
            <div>
                <h3 class="font-bold text-sm md:text-base text-base-content flex items-center gap-2 tracking-tight">
                    <x-heroicon-s-chart-bar class="w-5 h-5 text-primary" />
                    Tren Bulanan: Sell In vs Sell Out Tahun {{ $selectedYear }}
                    @if($unitMode === 'val')
                        <span class="badge badge-sm badge-outline text-indigo-500 font-semibold border-indigo-400">+ Target</span>
                    @endif
                </h3>
                <p class="text-xs text-base-content/60 font-normal mt-0.5">
                    @if($unitMode === 'val')
                        Komparasi <span class="font-medium text-primary">Sell In</span>, <span class="font-medium text-success">Sell Out</span>, dan <span class="font-medium text-indigo-500">Target</span> per bulan. Garis cyan adalah <span class="font-medium text-cyan-500">% Pencapaian Target</span> dan garis kuning adalah <span class="font-medium text-amber-500">Rasio Out/In</span>.
                    @else
                        Perbandingan volume fisik <span class="font-medium text-primary">Sell In</span> dan <span class="font-medium text-success">Sell Out</span> per bulan. Garis kuning adalah <span class="font-medium text-amber-500">Rasio Out/In</span> (Target hanya tersedia dalam Nilai Rupiah).
                    @endif
                </p>
            </div>
            
            {{-- Toggle Satuan: Karton vs Nilai Rupiah --}}
            <div class="join border border-base-300 shrink-0 self-start sm:self-auto">
                <button wire:click="setUnitMode('ktn')" 
                        class="join-item btn btn-xs {{ $unitMode === 'ktn' ? 'btn-primary font-semibold' : 'btn-ghost text-base-content/70 font-medium' }}">
                    Karton (KTN)
                </button>
                <button wire:click="setUnitMode('val')" 
                        class="join-item btn btn-xs {{ $unitMode === 'val' ? 'btn-primary font-semibold' : 'btn-ghost text-base-content/70 font-medium' }}">
                    Nilai Rupiah (IDR)
                </button>
            </div>
        </div>

        <div class="pt-2 w-full">
            <div wire:ignore x-ref="monthlyChart" class="w-full"></div>
        </div>
    </div>

    {{-- ROW: 2 CHARTS BERDAMPINGAN (BRAND & REGION) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 w-full">
        
        {{-- CHART 2: PER BRAND --}}
        <div class="bg-base-100 rounded-xl p-4 md:p-5 border border-base-300 shadow-sm flex flex-col">
            <div class="pb-3 border-b border-base-200">
                <h4 class="font-bold text-sm text-base-content flex items-center gap-2 tracking-tight">
                    <x-heroicon-s-tag class="w-4 h-4 text-secondary" />
                    Sell In vs Sell Out per Brand (Bulan {{ $months[$selectedMonth] }} {{ $selectedYear }})
                </h4>
                <p class="text-xs text-base-content/60 font-normal mt-0.5">
                    Perbandingan Sell In dan Sell Out untuk setiap brand.
                </p>
            </div>
            <div class="pt-2 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="brandChart" class="w-full"></div>
            </div>
        </div>

        {{-- CHART 3: PER REGION / WILAYAH --}}
        <div class="bg-base-100 rounded-xl p-4 md:p-5 border border-base-300 shadow-sm flex flex-col">
            <div class="pb-3 border-b border-base-200">
                <h4 class="font-bold text-sm text-base-content flex items-center gap-2 tracking-tight">
                    <x-heroicon-s-map-pin class="w-4 h-4 text-accent" />
                    Sell In vs Sell Out per Region (Bulan {{ $months[$selectedMonth] }} {{ $selectedYear }})
                </h4>
                <p class="text-xs text-base-content/60 font-normal mt-0.5">
                    Perbandingan Sell In dan Sell Out di setiap wilayah / region.
                </p>
            </div>
            <div class="pt-2 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="regionChart" class="w-full"></div>
            </div>
        </div>

    </div>

    {{-- CHART 4: TOP 8 SKU PENJUALAN TERBESAR --}}
    <div class="bg-base-100 rounded-xl p-4 md:p-5 border border-base-300 shadow-sm flex flex-col">
        <div class="pb-3 border-b border-base-200">
            <h4 class="font-bold text-sm text-base-content flex items-center gap-2 tracking-tight">
                <x-heroicon-s-fire class="w-4 h-4 text-warning" />
                Top 8 Produk: Sell In vs Sell Out (Bulan {{ $months[$selectedMonth] }} {{ $selectedYear }})
            </h4>
            <p class="text-xs text-base-content/60 font-normal mt-0.5">
                Perbandingan Sell In dan Sell Out untuk 8 produk dengan transaksi terbesar.
            </p>
        </div>
        <div class="pt-2 w-full">
            <div wire:ignore x-ref="topSkuChart" class="w-full"></div>
        </div>
    </div>

</div>
