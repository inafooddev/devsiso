{{-- APEXCHARTS ANALYTICAL SUITE (EXECUTIVE CLEAN EDITION - 3 ROWS) --}}
<div x-data="{
    chartMonthlyData: @js($chartMonthly),
    chartSubBrandData: @js($chartSubBrand ?? $chartBrand),
    chartRegionData: @js($chartRegion),
    chartTopSkuData: @js($chartTopSku),
    chartDepoTargetData: @js($chartDepoTarget),
    chartStatusDonutData: @js($chartStatusDonut),
    chartGapBottleneckData: @js($chartGapBottleneck),
    unitMode: @js($unitMode),
    viewMode: @js($viewMode),

    chartMonthlyInstance: null,
    chartSubBrandInstance: null,
    chartRegionInstance: null,
    chartTopSkuInstance: null,
    chartDepoTargetInstance: null,
    chartStatusDonutInstance: null,
    chartGapBottleneckInstance: null,

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
        this.$watch('chartSubBrandData', () => this.renderAllCharts());
        this.$watch('chartRegionData', () => this.renderAllCharts());
        this.$watch('chartTopSkuData', () => this.renderAllCharts());
        this.$watch('chartDepoTargetData', () => this.renderAllCharts());
        this.$watch('chartStatusDonutData', () => this.renderAllCharts());
        this.$watch('chartGapBottleneckData', () => this.renderAllCharts());
    },

    formatValue(val) {
        if (this.unitMode === 'val') {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
        }
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(val) + ' Ktn';
    },

    formatCompact(val) {
        if (val === null || val === undefined || isNaN(val)) return '0';
        const absVal = Math.abs(val);
        const sign = val < 0 ? '-' : '';
        if (this.unitMode === 'val') {
            if (absVal >= 1e9) return sign + (absVal / 1e9).toFixed(1).replace(/\.0$/, '') + ' M';
            if (absVal >= 1e6) return sign + (absVal / 1e6).toFixed(0) + ' Jt';
            if (absVal >= 1e3) return sign + (absVal / 1e3).toFixed(0) + ' Rb';
            return sign + absVal.toFixed(0);
        } else {
            if (absVal >= 1e3) return sign + (absVal / 1e3).toFixed(1).replace(/\.0$/, '') + 'K';
            return sign + absVal.toFixed(0);
        }
    },

    renderAllCharts() {
        if (typeof ApexCharts === 'undefined') return;

        this.renderMonthlyChart();
        this.renderSubBrandChart();
        this.renderTopSkuChart();
        this.renderRegionChart();
        this.renderDepoTargetChart();
        this.renderStatusDonutChart();
        this.renderGapBottleneckChart();
    },

    renderMonthlyChart() {
        if (!this.$refs.monthlyChart) return;
        if (this.chartMonthlyInstance) this.chartMonthlyInstance.destroy();

        const isVal = this.unitMode === 'val';

        let series = [];
        let yaxis = [];
        let colors = [];
        let strokeWidth = [];

        if (isVal) {
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
            strokeWidth = [0, 0, 0, 2.5, 2.5];

            yaxis = [
                {
                    seriesName: 'Sell In',
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                        formatter: (val) => this.formatCompact(val)
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
                    min: 0,
                    max: 200,
                    tickAmount: 4,
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
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
            strokeWidth = [0, 0, 2.5];

            yaxis = [
                {
                    seriesName: 'Sell In',
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                        formatter: (val) => this.formatCompact(val)
                    }
                },
                {
                    seriesName: 'Sell In',
                    show: false
                },
                {
                    seriesName: 'Rasio Out/In (%)',
                    opposite: true,
                    min: 0,
                    max: 200,
                    tickAmount: 4,
                    labels: {
                        style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                        formatter: (val) => val != null ? val.toFixed(0) + '%' : '-'
                    }
                }
            ];
        }

        const options = {
            series: series,
            chart: {
                height: 310,
                type: 'line',
                stacked: false,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif',
                animations: { enabled: true }
            },
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.15)',
                strokeDashArray: 4,
                padding: { top: 0, right: 10, bottom: 0, left: 10 }
            },
            stroke: {
                width: strokeWidth,
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    columnWidth: isVal ? '60%' : '48%',
                    borderRadius: 4
                }
            },
            colors: colors,
            labels: this.chartMonthlyData.categories,
            xaxis: {
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' }
                }
            },
            yaxis: yaxis,
            legend: { show: false },
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
                            fontSize: '10px',
                            fontWeight: 600,
                            fontFamily: 'Inter, system-ui, -apple-system, sans-serif',
                            padding: { left: 6, right: 6, top: 2, bottom: 2 }
                        },
                        text: '100% Benchmark'
                    }
                }]
            }
        };

        this.chartMonthlyInstance = new ApexCharts(this.$refs.monthlyChart, options);
        this.chartMonthlyInstance.render();
    },

    renderSubBrandChart() {
        if (!this.$refs.subBrandChart) return;
        if (this.chartSubBrandInstance) this.chartSubBrandInstance.destroy();

        const options = {
            series: [
                { name: 'Sell In', data: this.chartSubBrandData.seriesIn },
                { name: 'Sell Out', data: this.chartSubBrandData.seriesOut }
            ],
            chart: {
                type: 'bar',
                height: 270,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.15)',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '65%'
                }
            },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: this.chartSubBrandData.categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => this.formatCompact(val)
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#475569', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    maxWidth: 110
                }
            },
            legend: { show: false },
            tooltip: {
                shared: true,
                intersect: false,
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                y: { formatter: (val) => this.formatValue(val) }
            }
        };

        this.chartSubBrandInstance = new ApexCharts(this.$refs.subBrandChart, options);
        this.chartSubBrandInstance.render();
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
                height: 270,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.15)',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '65%'
                }
            },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: this.chartTopSkuData.categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => this.formatCompact(val)
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#475569', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    maxWidth: 110
                }
            },
            legend: { show: false },
            tooltip: {
                shared: true,
                intersect: false,
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                y: { formatter: (val) => this.formatValue(val) }
            }
        };

        this.chartTopSkuInstance = new ApexCharts(this.$refs.topSkuChart, options);
        this.chartTopSkuInstance.render();
    },

    renderRegionChart() {
        if (!this.$refs.regionChart) return;
        if (this.chartRegionInstance) this.chartRegionInstance.destroy();

        const categories = this.chartRegionData.categories || [];
        const fullNames = this.chartRegionData.fullNames || [];
        const highlightIdx = this.chartRegionData.highlightIndex;

        const yAxisColors = categories.map((_, idx) => idx === highlightIdx ? '#2563eb' : '#475569');
        const yAxisWeights = categories.map((_, idx) => idx === highlightIdx ? 700 : 500);

        const options = {
            series: [
                { name: 'Sell In', data: this.chartRegionData.seriesIn },
                { name: 'Sell Out', data: this.chartRegionData.seriesOut }
            ],
            chart: {
                type: 'bar',
                height: 270,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.15)',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '65%'
                }
            },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => this.formatCompact(val)
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: yAxisWeights, colors: yAxisColors, fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    maxWidth: 120
                }
            },
            legend: { show: false },
            tooltip: {
                shared: true,
                intersect: false,
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                x: {
                    formatter: (val, { dataPointIndex }) => fullNames[dataPointIndex] || val
                },
                y: { formatter: (val) => this.formatValue(val) }
            }
        };

        this.chartRegionInstance = new ApexCharts(this.$refs.regionChart, options);
        this.chartRegionInstance.render();
    },

    renderDepoTargetChart() {
        if (!this.$refs.depoTargetChart) return;
        if (this.chartDepoTargetInstance) this.chartDepoTargetInstance.destroy();

        const isVal = this.unitMode === 'val';
        const achList = this.chartDepoTargetData.achInList || [];

        const series = isVal
            ? [
                { name: 'Sell In', data: this.chartDepoTargetData.seriesIn },
                { name: 'Sell Out', data: this.chartDepoTargetData.seriesOut },
                { name: 'Target Depo', data: this.chartDepoTargetData.seriesTarget || [] }
            ]
            : [
                { name: 'Sell In', data: this.chartDepoTargetData.seriesIn },
                { name: 'Sell Out', data: this.chartDepoTargetData.seriesOut }
            ];

        const colors = isVal ? ['#3b82f6', '#10b981', '#8b5cf6'] : ['#3b82f6', '#10b981'];

        const options = {
            series: series,
            chart: {
                type: 'bar',
                height: 270,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.15)',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: isVal ? '72%' : '65%'
                }
            },
            colors: colors,
            dataLabels: { enabled: false },
            xaxis: {
                categories: this.chartDepoTargetData.categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => this.formatCompact(val)
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#475569', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    maxWidth: 110
                }
            },
            legend: { show: false },
            tooltip: {
                shared: true,
                intersect: false,
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                y: {
                    formatter: (val, { seriesIndex, dataPointIndex }) => {
                        const baseText = this.formatValue(val || 0);
                        if (seriesIndex === 0 && achList[dataPointIndex] != null) {
                            return baseText + ' (' + achList[dataPointIndex] + '% Ach)';
                        }
                        return baseText;
                    }
                }
            }
        };

        this.chartDepoTargetInstance = new ApexCharts(this.$refs.depoTargetChart, options);
        this.chartDepoTargetInstance.render();
    },

    renderStatusDonutChart() {
        if (!this.$refs.statusDonutChart) return;
        if (this.chartStatusDonutInstance) this.chartStatusDonutInstance.destroy();

        const keys = this.chartStatusDonutData.keys || [];
        const total = this.chartStatusDonutData.total || 0;
        const balancedPct = this.chartStatusDonutData.balancedPct || 0;
        const entityLabel = this.viewMode === 'product' ? 'SKU' : 'Distributor';

        const options = {
            series: this.chartStatusDonutData.series,
            labels: this.chartStatusDonutData.labels,
            colors: this.chartStatusDonutData.colors,
            chart: {
                type: 'donut',
                height: 225,
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif',
                events: {
                    dataPointSelection: (event, chartContext, config) => {
                        const selectedKey = keys[config.dataPointIndex];
                        if (selectedKey && this.$wire) {
                            this.$wire.setStatus(selectedKey);
                        }
                    }
                }
            },
            stroke: {
                width: 2,
                colors: ['transparent']
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '11px',
                                fontWeight: 600,
                                color: '#64748b',
                                offsetY: -4
                            },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontWeight: 800,
                                color: '#10b981',
                                offsetY: 4,
                                formatter: (val) => val + ' ' + entityLabel
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Seimbang',
                                fontSize: '11px',
                                fontWeight: 600,
                                color: '#64748b',
                                formatter: () => balancedPct + '%'
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            tooltip: {
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                y: {
                    formatter: (val) => {
                        const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                        return val + ' ' + entityLabel + ' (' + pct + '%)';
                    }
                }
            }
        };

        this.chartStatusDonutInstance = new ApexCharts(this.$refs.statusDonutChart, options);
        this.chartStatusDonutInstance.render();
    },

    renderGapBottleneckChart() {
        if (!this.$refs.gapBottleneckChart) return;
        if (this.chartGapBottleneckInstance) this.chartGapBottleneckInstance.destroy();

        const fullNames = this.chartGapBottleneckData.fullNames || [];
        const inVals = this.chartGapBottleneckData.inValues || [];
        const outVals = this.chartGapBottleneckData.outValues || [];

        const options = {
            series: [{
                name: 'Selisih (In - Out)',
                data: this.chartGapBottleneckData.series
            }],
            chart: {
                type: 'bar',
                height: 270,
                toolbar: { show: false },
                fontFamily: 'Inter, system-ui, -apple-system, sans-serif'
            },
            grid: {
                borderColor: 'rgba(148, 163, 184, 0.15)',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 3,
                    barHeight: '62%',
                    colors: {
                        ranges: [
                            { from: -999999999999, to: -0.001, color: '#0ea5e9' },
                            { from: 0, to: 999999999999, color: '#f59e0b' }
                        ]
                    }
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: this.chartGapBottleneckData.categories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#64748b', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    formatter: (val) => this.formatCompact(val)
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 500, colors: '#475569', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                    maxWidth: 115
                }
            },
            annotations: {
                xaxis: [{
                    x: 0,
                    borderColor: '#94a3b8',
                    strokeDashArray: 0
                }]
            },
            legend: { show: false },
            tooltip: {
                style: { fontSize: '12px', fontFamily: 'Inter, system-ui, -apple-system, sans-serif' },
                x: {
                    formatter: (val, { dataPointIndex }) => fullNames[dataPointIndex] || val
                },
                y: {
                    formatter: (val, { dataPointIndex }) => {
                        const sign = val > 0 ? '+' : '';
                        const statusNote = val > 0 ? ' (Tertahan / In > Out)' : ' (Serapan Tinggi / Out > In)';
                        const detail = ' | In: ' + this.formatCompact(inVals[dataPointIndex] || 0) + ', Out: ' + this.formatCompact(outVals[dataPointIndex] || 0);
                        return sign + this.formatValue(val) + statusNote + detail;
                    }
                }
            }
        };

        this.chartGapBottleneckInstance = new ApexCharts(this.$refs.gapBottleneckChart, options);
        this.chartGapBottleneckInstance.render();
    },

    destroy() {
        if (this.chartMonthlyInstance) this.chartMonthlyInstance.destroy();
        if (this.chartSubBrandInstance) this.chartSubBrandInstance.destroy();
        if (this.chartRegionInstance) this.chartRegionInstance.destroy();
        if (this.chartTopSkuInstance) this.chartTopSkuInstance.destroy();
        if (this.chartDepoTargetInstance) this.chartDepoTargetInstance.destroy();
        if (this.chartStatusDonutInstance) this.chartStatusDonutInstance.destroy();
        if (this.chartGapBottleneckInstance) this.chartGapBottleneckInstance.destroy();
    }
}" 
wire:key="siso-charts-suite-{{ $selectedYear }}-{{ $selectedMonth }}-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}-{{ $selectedBrand }}-{{ $selectedRegFest }}-{{ $unitMode }}-{{ $viewMode }}"
class="flex flex-col gap-4 w-full">

    {{-- ROW 1: TREN BULANAN KOMPARASI SELL IN VS SELL OUT (FULL WIDTH) --}}
    <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm flex flex-col">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between pb-3 border-b border-base-200/70 gap-3">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">
                        Tren Bulanan Sell In vs Sell Out ({{ $selectedYear }})
                    </span>
                    @if($selectedRegFest !== 'ALL')
                        <span class="px-2 py-0.5 rounded-md bg-secondary/10 text-secondary text-xs font-semibold">
                            {{ $selectedRegFest }}
                        </span>
                    @endif
                    <span class="px-2 py-0.5 rounded-md bg-base-200 text-base-content/70 text-xs font-medium">
                        {{ $unitMode === 'val' ? 'Nilai Rupiah (IDR)' : 'Volume Karton (KTN)' }}
                    </span>
                </div>
                <p class="text-xs text-base-content/50 mt-0.5">
                    @if($unitMode === 'val')
                        Perbandingan nilai Sell In, Sell Out, dan Target Depo beserta persentase pencapaian bulanan
                    @else
                        Perbandingan volume fisik Sell In dan Sell Out beserta rasio serapan bulanan
                    @endif
                </p>
            </div>

            {{-- Custom Executive Dot Legend --}}
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs font-medium text-base-content/70">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-blue-500"></span>
                    Sell In
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
                    Sell Out
                </span>
                @if($unitMode === 'val')
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-violet-500"></span>
                        Target
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-3 h-0.5 bg-cyan-500 rounded-full"></span>
                        % Ach Target
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-0.5 bg-amber-500 rounded-full"></span>
                    Rasio Out/In (%)
                </span>
            </div>
        </div>

        <div class="pt-2 w-full">
            <div wire:ignore x-ref="monthlyChart" class="w-full"></div>
        </div>
    </div>

    {{-- ROW 2: 3-COLUMN SYMMETRIC BREAKDOWN GRID (SUB BRAND & REGION) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full">
        
        {{-- COL 1: TOP ITEM PER SUB BRAND --}}
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm flex flex-col">
            <div class="flex items-start justify-between pb-3 border-b border-base-200/70 gap-2">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">
                            Top Item per Sub Brand
                        </span>
                        <span class="px-1.5 py-0.5 rounded bg-secondary/10 text-secondary text-xs font-semibold">
                            Top Item
                        </span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-0.5">
                        {{ $months[$selectedMonth] }} {{ $selectedYear }} • Kategori Top Item
                    </p>
                </div>
                <div class="flex items-center gap-2.5 text-xs font-medium text-base-content/60 shrink-0">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-500"></span>In</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-emerald-500"></span>Out</span>
                </div>
            </div>
            <div class="pt-2 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="subBrandChart" class="w-full"></div>
            </div>
        </div>

        {{-- COL 2: TOP 8 SUB BRAND (OVERALL) --}}
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm flex flex-col">
            <div class="flex items-start justify-between pb-3 border-b border-base-200/70 gap-2">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/70 block">
                        Top 8 Sub Brand Terbesar
                    </span>
                    <p class="text-xs text-base-content/50 mt-0.5">
                        {{ $months[$selectedMonth] }} {{ $selectedYear }} • Seluruh Produk
                    </p>
                </div>
                <div class="flex items-center gap-2.5 text-xs font-medium text-base-content/60 shrink-0">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-500"></span>In</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-emerald-500"></span>Out</span>
                </div>
            </div>
            <div class="pt-2 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="topSkuChart" class="w-full"></div>
            </div>
        </div>

        {{-- COL 3: KOMPARASI PER REGION / AREA / DISTRIBUTOR (SMART DRILLDOWN) --}}
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm flex flex-col">
            <div class="flex items-start justify-between pb-3 border-b border-base-200/70 gap-2">
                <div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">
                            {{ $chartRegion['title'] ?? 'Sebaran per Region' }}
                        </span>
                        @if(!empty($chartRegion['rankBadge']))
                            <span class="px-1.5 py-0.5 rounded bg-primary/10 text-primary text-xs font-semibold tabular-nums">
                                {{ $chartRegion['rankBadge'] }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-base-content/50 mt-0.5">
                        {{ $months[$selectedMonth] }} {{ $selectedYear }} • {{ $chartRegion['subtitle'] ?? 'Wilayah Distribusi' }}
                    </p>
                </div>
                <div class="flex items-center gap-2.5 text-xs font-medium text-base-content/60 shrink-0">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-500"></span>In</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-emerald-500"></span>Out</span>
                </div>
            </div>
            <div class="pt-2 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="regionChart" class="w-full"></div>
            </div>
        </div>

    </div>

    {{-- ROW 3: 3-COLUMN DEEP DIAGNOSTIC & ACTIONABLE ANALYTICS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full">

        {{-- COL 1: TARGET VS REALISASI PER DEPO / CABANG --}}
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm flex flex-col">
            <div class="flex items-start justify-between pb-3 border-b border-base-200/70 gap-2">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">
                            Performa Top 8 Depo / Cabang
                        </span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-0.5">
                        {{ $unitMode === 'val' ? 'Sell In vs Sell Out vs Target Depo' : 'Volume Sell In vs Sell Out (+ % Ach di Tooltip)' }}
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-base-content/60 shrink-0">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-500"></span>In</span>
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-emerald-500"></span>Out</span>
                    @if($unitMode === 'val')
                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-violet-500"></span>Tgt</span>
                    @endif
                </div>
            </div>
            <div class="pt-2 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="depoTargetChart" class="w-full"></div>
            </div>
        </div>

        {{-- COL 2: DONUT DISTRIBUSI KESEHATAN SERAPAN --}}
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between pb-3 border-b border-base-200/70 gap-2">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">
                            Kesehatan Serapan {{ $viewMode === 'product' ? 'SKU' : 'Distributor' }}
                        </span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-0.5">
                        Klik segmen grafik atau label untuk memfilter tabel
                    </p>
                </div>
                <span class="px-2 py-0.5 rounded-md bg-base-200 text-base-content/70 text-xs font-semibold tabular-nums">
                    Total: {{ $chartStatusDonut['total'] ?? 0 }}
                </span>
            </div>

            <div class="py-1 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="statusDonutChart" class="w-full"></div>
            </div>

            {{-- Interactive Status Legend Pills --}}
            <div class="pt-2 border-t border-base-200/60 grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-xs">
                @foreach($chartStatusDonut['keys'] ?? [] as $idx => $sKey)
                    @php
                        $sCount = $chartStatusDonut['series'][$idx] ?? 0;
                        $sLabel = $chartStatusDonut['shortLabels'][$idx] ?? $sKey;
                        $sColor = $chartStatusDonut['colors'][$idx] ?? '#64748b';
                        $isActiveStatus = ($selectedStatus ?? 'ALL') === $sKey;
                    @endphp
                    <button type="button"
                            wire:click="setStatus('{{ $sKey }}')"
                            class="flex items-center justify-between px-2 py-1 rounded-lg border text-left transition-all cursor-pointer {{ $isActiveStatus ? 'bg-primary/10 border-primary text-primary font-bold' : 'bg-base-200/40 border-base-200 hover:bg-base-200 text-base-content/70 font-medium' }}">
                        <span class="inline-flex items-center gap-1.5 truncate">
                            <span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $sColor }}"></span>
                            <span class="truncate">{{ $sLabel }}</span>
                        </span>
                        <span class="font-bold tabular-nums ml-1">{{ $sCount }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- COL 3: DIVERGING BAR TOP GAP / BOTTLENECK --}}
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm flex flex-col">
            <div class="flex items-start justify-between pb-3 border-b border-base-200/70 gap-2">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/70 block">
                        Anomali Selisih (In - Out) Terbesar
                    </span>
                    <p class="text-xs text-base-content/50 mt-0.5">
                        Tertahan di Depo (+Gap) vs Serapan Tinggi (-Gap)
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-base-content/60 shrink-0">
                    <span class="inline-flex items-center gap-1" title="Sell In > Sell Out"><span class="w-2 h-2 rounded-sm bg-amber-500"></span>+In</span>
                    <span class="inline-flex items-center gap-1" title="Sell Out > Sell In"><span class="w-2 h-2 rounded-sm bg-sky-500"></span>-Out</span>
                </div>
            </div>
            <div class="pt-2 flex-1 flex items-center justify-center">
                <div wire:ignore x-ref="gapBottleneckChart" class="w-full"></div>
            </div>
        </div>

    </div>

</div>
