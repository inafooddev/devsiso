<div class="p-4" x-data="dashboard()">
    <!-- Header & Filters -->
    <x-header title="Sell-In Unified Dashboard" subtitle="Fresh, Interactive, & Professional" separator>
        <x-slot:actions>
            <x-select 
                label="Role / Entity" 
                :options="$entitiesOption" 
                wire:model.change="selectedEntity" 
                icon="o-user-group" 
                inline 
            />
            <x-select 
                label="Year" 
                :options="$yearOptions" 
                wire:model.change="selectedYear" 
                icon="o-calendar" 
                inline 
            />
            <x-button icon="o-arrow-path" wire:click="applyFilter" class="btn-primary" spinner />
        </x-slot:actions>
    </x-header>

    <!-- KPI Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-stat 
            title="Total Target" 
            value="Rp {{ number_format($kpiData['target'], 0, ',', '.') }}" 
            icon="o-flag" 
            class="bg-base-100/50 backdrop-blur-md border border-base-300 shadow-sm hover:shadow-md transition-shadow" 
        />
        <x-stat 
            title="Total Actual" 
            value="Rp {{ number_format($kpiData['actual'], 0, ',', '.') }}" 
            icon="o-banknotes" 
            class="bg-base-100/50 backdrop-blur-md border border-base-300 shadow-sm hover:shadow-md transition-shadow text-primary" 
        />
        <x-stat 
            title="Achievement" 
            value="{{ $kpiData['achievement'] }}%" 
            icon="o-chart-pie" 
            class="bg-base-100/50 backdrop-blur-md border border-base-300 shadow-sm hover:shadow-md transition-shadow {{ $kpiData['achievement'] >= 100 ? 'text-success' : 'text-warning' }}" 
            description="vs Target" 
        />
        <x-stat 
            title="Growth YoY" 
            value="{{ $kpiData['growth'] }}%" 
            icon="o-arrow-trending-up" 
            class="bg-base-100/50 backdrop-blur-md border border-base-300 shadow-sm hover:shadow-md transition-shadow text-success" 
            description="vs Last Year" 
        />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Main Trend Chart -->
        <div class="col-span-1 lg:col-span-2 bg-base-100/50 backdrop-blur-md border border-base-300 rounded-2xl p-6 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <x-icon name="o-presentation-chart-line" class="w-6 h-6 text-primary" />
                Sales Trend
            </h3>
            <div id="chartSalesTrend" class="w-full h-72"></div>
        </div>

        <!-- Contribution Chart -->
        <div class="col-span-1 bg-base-100/50 backdrop-blur-md border border-base-300 rounded-2xl p-6 shadow-sm">
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                <x-icon name="o-chart-pie" class="w-6 h-6 text-secondary" />
                Contribution Breakdown
            </h3>
            <div id="chartContribution" class="w-full h-72"></div>
        </div>
    </div>

    <!-- Details Table -->
    <div class="bg-base-100/50 backdrop-blur-md border border-base-300 rounded-2xl shadow-sm overflow-hidden p-6">
        <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <x-icon name="o-table-cells" class="w-6 h-6 text-info" />
            Detail Breakdown
        </h3>
        
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Entity Name</th>
                        <th>Role</th>
                        <th class="text-right">Target</th>
                        <th class="text-right">Actual</th>
                        <th class="text-right">Achievement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableData as $row)
                    <tr class="hover">
                        <td class="font-semibold">{{ $row['name'] }}</td>
                        <td>
                            <x-badge :value="$row['role']" class="badge-neutral" />
                        </td>
                        <td class="text-right">Rp {{ number_format($row['target'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($row['actual'], 0, ',', '.') }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span>{{ $row['ach'] }}%</span>
                                <progress class="progress {{ $row['ach'] >= 100 ? 'progress-success' : 'progress-warning' }} w-16" value="{{ $row['ach'] }}" max="100"></progress>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart Script Initialization -->
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                trendChart: null,
                contributionChart: null,

                init() {
                    this.initTrendChart();
                    this.initContributionChart();
                    
                    // Listen for Livewire updates
                    Livewire.on('charts-updated', () => {
                        this.updateCharts();
                    });
                },

                initTrendChart() {
                    const data = @json(json_decode($chartSalesTrend, true));
                    const isDark = document.documentElement.getAttribute('data-theme') === 'neon-dark';

                    const options = {
                        series: [
                            { name: 'Actual', data: data.actuals || [] },
                            { name: 'Target', data: data.targets || [] }
                        ],
                        chart: {
                            type: 'area',
                            height: '100%',
                            toolbar: { show: false },
                            background: 'transparent',
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                            }
                        },
                        colors: ['#321fdb', '#9da5b1'],
                        fill: {
                            type: ['gradient', 'solid'],
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.05,
                                stops: [0, 100]
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: [3, 2], dashArray: [0, 4] },
                        xaxis: {
                            categories: data.labels || [],
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: { style: { colors: isDark ? '#a6adbb' : '#4f5d73' } }
                        },
                        yaxis: {
                            labels: { 
                                formatter: (value) => { return value > 1000000 ? (value/1000000).toFixed(0) + 'M' : value; },
                                style: { colors: isDark ? '#a6adbb' : '#4f5d73' }
                            }
                        },
                        grid: {
                            borderColor: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)',
                            strokeDashArray: 4,
                        },
                        theme: { mode: isDark ? 'dark' : 'light' },
                        legend: { position: 'top', horizontalAlign: 'right' },
                        tooltip: { theme: isDark ? 'dark' : 'light' }
                    };

                    this.trendChart = new ApexCharts(document.querySelector("#chartSalesTrend"), options);
                    this.trendChart.render();
                },

                initContributionChart() {
                    const data = @json(json_decode($chartContribution, true));
                    const isDark = document.documentElement.getAttribute('data-theme') === 'neon-dark';

                    const options = {
                        series: data.series || [],
                        chart: {
                            type: 'donut',
                            height: '100%',
                            background: 'transparent'
                        },
                        labels: data.labels || [],
                        theme: { mode: isDark ? 'dark' : 'light' },
                        stroke: { show: false },
                        dataLabels: { dropShadow: { enabled: false } },
                        legend: { position: 'bottom' },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        name: { show: true },
                                        value: { show: true }
                                    }
                                }
                            }
                        }
                    };

                    this.contributionChart = new ApexCharts(document.querySelector("#chartContribution"), options);
                    this.contributionChart.render();
                },

                updateCharts() {
                    const trendData = JSON.parse(@this.chartSalesTrend);
                    this.trendChart.updateSeries([
                        { name: 'Actual', data: trendData.actuals },
                        { name: 'Target', data: trendData.targets }
                    ]);

                    const contribData = JSON.parse(@this.chartContribution);
                    this.contributionChart.updateSeries(contribData.series);
                }
            }));
        });
    </script>
    @endpush
</div>
