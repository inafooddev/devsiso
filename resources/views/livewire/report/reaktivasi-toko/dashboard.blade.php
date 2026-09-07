<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full" x-data="reaktivasiDashboard()">
    <x-slot name="title">Report Reaktivasi Toko - Dashboard</x-slot>

    <x-ui.tab-menu>
        <a href="#" class="tab tab-active bg-base-100 shadow-sm text-xs md:text-sm font-bold h-8 min-h-8">Dashboard</a>
        <a href="{{ Route::has('report.reaktivasi-toko.summary') ? route('report.reaktivasi-toko.summary') : '#' }}" class="tab text-xs md:text-sm h-8 min-h-8 hover:text-primary transition-colors">Summary</a>
        <a href="{{ Route::has('report.reaktivasi-toko.index') ? route('report.reaktivasi-toko.index') : route('report.reaktivasi-toko') ?? '#' }}" class="tab text-xs md:text-sm h-8 min-h-8 hover:text-primary transition-colors">Detail</a>
    </x-ui.tab-menu>

    {{-- Filter Header Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 p-3 md:p-4 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="shrink-0 w-full sm:w-auto">
            <h2 class="text-base md:text-lg font-bold">Dashboard Reaktivasi Toko</h2>
            <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Visualisasi Data Pencapaian</p>
        </div>
        
        <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
            {{-- Filter Region --}}
            <select wire:model.live="filterRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                <option value="">Semua Region</option>
                @foreach($this->filterOptions['regions'] as $region)
                    <option value="{{ $region }}">{{ $region }}</option>
                @endforeach
            </select>

            {{-- Filter Area --}}
            <select wire:model.live="filterArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                <option value="">Semua Area</option>
                @foreach($this->filterOptions['areas'] as $area)
                    <option value="{{ $area }}">{{ $area }}</option>
                @endforeach
            </select>

            {{-- Filter Supervisor --}}
            <select wire:model.live="filterSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 max-w-[120px]">
                <option value="">Semua SPV</option>
                @foreach($this->filterOptions['supervisors'] as $spv)
                    <option value="{{ $spv }}">{{ Str::limit($spv, 15) }}</option>
                @endforeach
            </select>

            {{-- Filter Distributor --}}
            <select wire:model.live="filterDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 max-w-[150px]">
                <option value="">Semua Distributor</option>
                @foreach($this->filterOptions['distributors'] as $dist)
                    <option value="{{ $dist }}">{{ Str::limit($dist, 15) }}</option>
                @endforeach
            </select>
            
            {{-- Filter Bulan --}}
            <select wire:model.live="filterBulan" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>

            {{-- Filter Tahun --}}
            <select wire:model.live="filterTahun" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
            
            {{-- Reset Filter Button --}}
            <button wire:click="resetFilters" class="btn btn-sm btn-ghost btn-circle text-base-content/60 hover:text-primary" title="Reset Filter" wire:loading.attr="disabled">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </button>
        </div>
    </div>

    @php
        $stats = $this->dashboardData;
        $kpi = $stats['kpi'];
        $types = $stats['types'];
    @endphp

    {{-- Data Tersembunyi untuk Alpine --}}
    <div id="chart-data" class="hidden" data-payload="{{ json_encode($stats) }}"></div>

    <div class="flex-1 overflow-auto flex flex-col gap-4">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 shrink-0">
            <!-- 1. Total Toko -->
            <div class="bg-base-100 rounded-xl shadow-md border-l-4 border-l-base-300 p-4">
                <div class="text-[10px] md:text-xs font-semibold text-base-content/60 uppercase tracking-wide">Total Toko Target</div>
                <div class="text-2xl md:text-3xl font-bold text-base-content mt-1">{{ number_format($kpi['total_toko'], 0, ',', '.') }}</div>
            </div>
            
            <!-- 2. Total Aktif -->
            <div class="bg-base-100 rounded-xl shadow-md border-l-4 border-l-success p-4">
                <div class="text-[10px] md:text-xs font-semibold text-success uppercase tracking-wide">Total Aktif</div>
                <div class="text-2xl md:text-3xl font-bold text-base-content mt-1">{{ number_format($kpi['total_aktif'], 0, ',', '.') }}</div>
                <div class="text-xs text-base-content/60 mt-1 font-semibold">{{ $kpi['pct_aktif'] }}% dari Target</div>
            </div>

            <!-- 3. Gap -->
            <div class="bg-base-100 rounded-xl shadow-md border-l-4 border-l-error p-4">
                <div class="text-[10px] md:text-xs font-semibold text-error uppercase tracking-wide">Total Gap</div>
                <div class="text-2xl md:text-3xl font-bold text-base-content mt-1">{{ number_format($kpi['gap'], 0, ',', '.') }}</div>
                <div class="text-xs text-base-content/60 mt-1 font-semibold">{{ $kpi['pct_gap'] }}% belum aktif</div>
            </div>

            <!-- 4. Total Transaksi -->
            <div class="bg-base-100 rounded-xl shadow-md border-l-4 border-l-primary p-4">
                <div class="text-[10px] md:text-xs font-semibold text-primary uppercase tracking-wide">Total Transaksi</div>
                <div class="text-lg md:text-xl lg:text-2xl font-bold font-mono text-base-content mt-2">{{ number_format($kpi['total_transaksi'], 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Charts Row 1 --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 shrink-0">
            {{-- Pie Chart: Pencapaian Total --}}
            <div class="bg-base-100 rounded-xl shadow-md border border-base-300 p-4 lg:col-span-1">
                <h3 class="text-sm font-bold text-base-content mb-4 uppercase tracking-wider">Status Pencapaian Toko</h3>
                <div class="w-full" x-ref="pieChart"></div>
            </div>

            {{-- Bar Chart: Pencapaian per Tipe --}}
            <div class="bg-base-100 rounded-xl shadow-md border border-base-300 p-4 lg:col-span-2">
                <h3 class="text-sm font-bold text-base-content mb-4 uppercase tracking-wider">Pencapaian Berdasarkan Tipe Toko</h3>
                <div class="w-full" x-ref="typeBarChart"></div>
            </div>
        </div>

        {{-- Charts Row 2 --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 shrink-0 pb-4">
            {{-- Horizontal Bar Chart: Top 5 Distributor --}}
            <div class="bg-base-100 rounded-xl shadow-md border border-base-300 p-4 overflow-hidden">
                <h3 class="text-sm font-bold text-base-content mb-4 uppercase tracking-wider">Top 5 Distributor (% Aktif)</h3>
                <div class="w-full relative overflow-hidden" x-ref="topDistrChart"></div>
            </div>

            {{-- Summary Breakdown --}}
            <div class="bg-base-100 rounded-xl shadow-md border border-base-300 p-4 overflow-auto">
                <h3 class="text-sm font-bold text-base-content mb-4 uppercase tracking-wider">Ringkasan Tabel Performa</h3>
                <table class="table table-xs table-zebra w-full text-right">
                    <thead>
                        <tr>
                            <th class="text-left text-base-content/80">Distributor</th>
                            <th class="text-base-content/80">Target</th>
                            <th class="text-base-content/80">Aktif</th>
                            <th class="text-base-content/80">% Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['top_distributors'] as $dist)
                            <tr>
                                <td class="text-left whitespace-normal font-semibold max-w-[200px] truncate" title="{{ $dist['name'] }}">{{ Str::limit($dist['name'], 30) }}</td>
                                <td>{{ number_format($dist['total'], 0, ',', '.') }}</td>
                                <td class="text-success font-bold">{{ number_format($dist['aktif'], 0, ',', '.') }}</td>
                                <td>
                                    <div class="radial-progress text-xs {{ $dist['pct'] >= 50 ? 'text-success' : 'text-error' }}" style="--value:{{ $dist['pct'] }}; --size:2.5rem; --thickness:3px;" role="progressbar">
                                        {{ $dist['pct'] }}%
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-base-content/50 py-4">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const setupDashboard = () => {
        Alpine.data('reaktivasiDashboard', () => ({
            pieChart: null,
            typeBarChart: null,
            topDistrChart: null,

            init() {
                this.renderCharts();
                
                // Watch for Livewire updates to the payload element
                const el = document.getElementById('chart-data');
                if (el) {
                    const observer = new MutationObserver(() => {
                        this.renderCharts();
                    });
                    observer.observe(el, { attributes: true, attributeFilter: ['data-payload'] });
                }
            },

            renderCharts() {
                const el = document.getElementById('chart-data');
                if(!el) return;
                
                const data = JSON.parse(el.getAttribute('data-payload'));
                
                this.renderPieChart(data.kpi);
                this.renderTypeBarChart(data.types);
                this.renderTopDistrChart(data.top_distributors);
            },

            renderPieChart(kpi) {
                if(this.pieChart) this.pieChart.destroy();
                
                const options = {
                    series: [Number(kpi.total_aktif), Number(kpi.gap)],
                    labels: ['Toko Aktif', 'Gap'],
                    chart: {
                        type: 'donut',
                        height: 300,
                        background: 'transparent',
                        fontFamily: 'inherit',
                        animations: { enabled: false }
                    },
                    colors: ['#00a96e', '#ff5861'], // success and error colors
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: { show: true, fontSize: '14px', color: '#888' },
                                    value: { show: true, fontSize: '24px', fontWeight: 'bold' },
                                    total: { show: true, label: 'Target', color: '#888', formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }}
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { width: 0 },
                    theme: { mode: localStorage.getItem('neon-theme') === 'neon-dark' ? 'dark' : 'light' },
                    legend: { position: 'bottom' }
                };

                this.pieChart = new ApexCharts(this.$refs.pieChart, options);
                this.pieChart.render();
            },

            renderTypeBarChart(types) {
                if(this.typeBarChart) this.typeBarChart.destroy();

                const options = {
                    series: [{
                        name: 'Target',
                        data: [Number(types.SO.total), Number(types.G.total), Number(types.SG.total), Number(types.R.total)]
                    }, {
                        name: 'Aktif',
                        data: [Number(types.SO.aktif), Number(types.G.aktif), Number(types.SG.aktif), Number(types.R.aktif)]
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        background: 'transparent',
                        fontFamily: 'inherit',
                        toolbar: { show: false },
                        animations: { enabled: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 4
                        },
                    },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: {
                        categories: ['TYPE SO', 'TYPE G', 'TYPE SG', 'TYPE R'],
                    },
                    colors: ['#a6adbb', '#00a96e'], // base-content/40 and success
                    fill: { opacity: 1 },
                    theme: { mode: localStorage.getItem('neon-theme') === 'neon-dark' ? 'dark' : 'light' },
                    tooltip: {
                        y: { formatter: function (val) { return val + " Toko" } }
                    },
                    legend: { position: 'top' }
                };

                this.typeBarChart = new ApexCharts(this.$refs.typeBarChart, options);
                this.typeBarChart.render();
            },

            renderTopDistrChart(distributors) {
                if(this.topDistrChart) this.topDistrChart.destroy();

                const categories = distributors.map(d => {
                    return d.name.length > 20 ? d.name.substring(0, 20) + '...' : d.name;
                });
                const seriesData = distributors.map(d => Number(d.pct));

                const options = {
                    series: [{
                        name: '% Aktif',
                        data: seriesData
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        background: 'transparent',
                        fontFamily: 'inherit',
                        toolbar: { show: false },
                        animations: { enabled: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    colors: ['#00b5ff'], // primary color equivalent
                    dataLabels: {
                        enabled: true,
                        textAnchor: 'start',
                        style: { colors: ['#fff'] },
                        formatter: function (val) { return val + "%" },
                        offsetX: 0
                    },
                    xaxis: {
                        categories: categories,
                        max: 100,
                        labels: { formatter: function (val) { return val + "%" } }
                    },
                    grid: {
                        padding: {
                            right: 25
                        }
                    },
                    theme: { mode: localStorage.getItem('neon-theme') === 'neon-dark' ? 'dark' : 'light' },
                };

                this.topDistrChart = new ApexCharts(this.$refs.topDistrChart, options);
                this.topDistrChart.render();
            }
        }));
    };

    if (window.Alpine) {
        setupDashboard();
    } else {
        document.addEventListener('alpine:init', setupDashboard);
    }
</script>
@endpush
