<div>
    <x-slot name="title">Area Sell In Dashboard</x-slot>

    <div class="p-6 space-y-6 bg-base-200 min-h-screen">
        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 bg-base-100 rounded-2xl shadow-xl px-6 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="font-bold text-lg mr-2">Area Sell In Dashboard</h2>
                <span class="badge badge-outline badge-primary">{{ $selectedYear }}</span>
                <span class="badge badge-outline">{{ date('M', mktime(0,0,0,$selectedMonthFrom,1)) }} – {{ date('M', mktime(0,0,0,$selectedMonthTo,1)) }}</span>
                @if($selectedRegFest !== 'ALL')<span class="badge badge-secondary badge-outline">{{ $selectedRegFest }}</span>@endif
                <span class="badge badge-outline badge-info">{{ $regionsOption[$selectedRegion] ?? 'No Region' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="join">
                    <a href="{{ route('dashboard.national-sell-in') }}" class="btn btn-sm join-item {{ request()->routeIs('dashboard.national-sell-in') ? 'btn-neutral' : 'btn-outline' }}">Nasional</a>
                    <a href="{{ route('dashboard.area-sell-in') }}" class="btn btn-sm join-item {{ request()->routeIs('dashboard.area-sell-in') ? 'btn-neutral' : 'btn-outline' }}">Area</a>
                    <a href="{{ route('dashboard.supervisor-sell-in') }}" class="btn btn-sm join-item {{ request()->routeIs('dashboard.supervisor-sell-in') ? 'btn-neutral' : 'btn-outline' }}">Supervisor</a>
                    <a href="{{ route('dashboard.cabang-sell-in') }}" class="btn btn-sm join-item {{ request()->routeIs('dashboard.cabang-sell-in') ? 'btn-neutral' : 'btn-outline' }}">Cabang</a>
                </div>
                <button wire:click="openFilterModal" class="btn btn-primary btn-sm rounded-xl gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filters
                    <span wire:loading wire:target="applyFilter" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>

        {{-- Filter Modal --}}
        @if($showFilterModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeFilterModal"></div>
                <div class="relative z-10 w-full max-w-2xl bg-base-100 rounded-2xl shadow-2xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-base-200">
                        <h3 class="font-bold text-lg">Dashboard Filters</h3>
                        <button wire:click="closeFilterModal" class="btn btn-ghost btn-sm btn-circle">✕</button>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">Year</span></label>
                                <select wire:model="selectedYear" class="select select-bordered select-sm rounded-xl">
                                    @foreach($yearOptions as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">Type</span></label>
                                <select wire:model="selectedRegFest" class="select select-bordered select-sm rounded-xl">
                                    <option value="ALL">All Types</option>
                                    <option value="REG">Regular</option>
                                    <option value="FEST">Festival</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">From Month</span></label>
                                <select wire:model="selectedMonthFrom" class="select select-bordered select-sm rounded-xl">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">To Month</span></label>
                                <select wire:model="selectedMonthTo" class="select select-bordered select-sm rounded-xl">
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold">Select Region</span></label>
                            <select wire:model="selectedRegion" class="select select-bordered select-sm rounded-xl w-full">
                                @foreach($regionsOption as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-base-200 flex justify-end gap-3">
                        <button wire:click="closeFilterModal" class="btn btn-ghost btn-sm rounded-xl">Cancel</button>
                        <button wire:click="applyFilter" class="btn btn-primary btn-sm rounded-xl gap-2">
                            <span wire:loading wire:target="applyFilter" class="loading loading-spinner loading-xs"></span>
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- KPI Cards Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Sales --}}
            <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden border-l-4 border-primary">
                <div class="card-body p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-base-content/70">Total Sales</p>
                            <h2 class="text-2xl font-bold mt-1">Rp {{ number_format($kpiData['total_actual_ty'] ?? 0, 0, ',', '.') }}</h2>
                        </div>
                        <div class="p-2 bg-primary/10 rounded-lg text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center mt-4 gap-2">
                        <span class="badge {{ ($kpiData['growth_pct'] ?? 0) >= 0 ? 'badge-success' : 'badge-error' }} badge-sm font-bold">
                            {{ ($kpiData['growth_pct'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($kpiData['growth_pct'] ?? 0, 1) }}%
                        </span>
                        <span class="text-xs text-base-content/60">vs LY: {{ ($kpiData['gap_vs_ly'] ?? 0) >= 0 ? '+' : '' }}Rp {{ number_format(abs($kpiData['gap_vs_ly'] ?? 0), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Sales vs Target --}}
            <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden border-l-4 {{ ($kpiData['achievement_pct'] ?? 0) >= 100 ? 'border-success' : (($kpiData['achievement_pct'] ?? 0) >= 80 ? 'border-warning' : 'border-error') }}">
                <div class="card-body p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-base-content/70">Sales vs Target</p>
                            <h2 class="text-2xl font-bold mt-1">{{ number_format($kpiData['achievement_pct'] ?? 0, 1) }}%</h2>
                        </div>
                        <div class="p-2 {{ ($kpiData['achievement_pct'] ?? 0) >= 100 ? 'bg-success/10 text-success' : (($kpiData['achievement_pct'] ?? 0) >= 80 ? 'bg-warning/10 text-warning' : 'bg-error/10 text-error') }} rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex justify-between text-xs mb-1">
                            <span>Target: Rp {{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</span>
                            <span class="font-semibold {{ ($kpiData['gap_vs_target'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                                Gap: {{ ($kpiData['gap_vs_target'] ?? 0) >= 0 ? '+' : '' }}Rp {{ number_format($kpiData['gap_vs_target'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <progress class="progress {{ ($kpiData['achievement_pct'] ?? 0) >= 100 ? 'progress-success' : (($kpiData['achievement_pct'] ?? 0) >= 80 ? 'progress-warning' : 'progress-error') }} w-full h-2" value="{{ $kpiData['achievement_pct'] ?? 0 }}" max="100"></progress>
                    </div>
                </div>
            </div>

            {{-- YoY Growth --}}
            <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden border-l-4 border-secondary">
                <div class="card-body p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-base-content/70">YoY Growth</p>
                            <h2 class="text-2xl font-bold mt-1">{{ number_format($kpiData['growth_pct'] ?? 0, 1) }}%</h2>
                        </div>
                        <div class="p-2 bg-secondary/10 rounded-lg text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-xs text-base-content/60">Absolute Growth vs LY:</p>
                        <p class="text-sm font-bold {{ ($kpiData['gap_vs_ly'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                            {{ ($kpiData['gap_vs_ly'] ?? 0) >= 0 ? '+' : '' }}Rp {{ number_format($kpiData['gap_vs_ly'] ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Avg Monthly Sales --}}
            <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden border-l-4 border-accent">
                <div class="card-body p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-base-content/70">Avg Monthly Sales</p>
                            <h2 class="text-2xl font-bold mt-1">Rp {{ number_format($kpiData['avg_monthly_sales'] ?? 0, 0, ',', '.') }}</h2>
                        </div>
                        <div class="p-2 bg-accent/10 rounded-lg text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center mt-4 gap-2">
                        <div class="flex items-center text-xs {{ ($kpiData['avg_monthly_growth'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ ($kpiData['avg_monthly_growth'] ?? 0) >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" />
                            </svg>
                            {{ number_format(abs($kpiData['avg_monthly_growth'] ?? 0), 1) }}%
                        </div>
                        <span class="text-xs text-base-content/60">Trend indicator</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Insights Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($insights as $key => $insight)
                @if($insight)
                    <div class="flex items-center p-4 bg-base-100 rounded-2xl shadow-md border border-base-200">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 
                        {{ $insight['type'] == 'success' ? 'bg-success/20 text-success' : 
                          ($insight['type'] == 'error' ? 'bg-error/20 text-error' : 
                          ($insight['type'] == 'warning' ? 'bg-warning/20 text-warning' : 'bg-info/20 text-info')) }}">
                            @if($insight['type'] == 'success')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @elseif($insight['type'] == 'error')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            @elseif($insight['type'] == 'warning')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">{{ $insight['title'] }}</p>
                            <p class="text-sm font-bold">{{ $insight['value'] }}</p>
                            <p class="text-[10px] text-base-content/50">{{ $insight['sub'] }}</p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Chart data --}}
        <script type="application/json" id="chart-data">
            {
                "contribution": @json(json_decode($chartAreaContribution, true) ?: new stdClass),
                "trend":        @json(json_decode($chartSalesTrend, true)        ?: new stdClass),
                "monthly":      @json(json_decode($chartMonthlyBar, true)        ?: new stdClass),
                "growth":       @json(json_decode($chartGrowthArea, true)        ?: new stdClass),
                "areaHBar":     @json(json_decode($chartAreaHBar, true)          ?: new stdClass),
                "combo":        @json(json_decode($chartCombo, true)             ?: new stdClass)
            }
        </script>

        {{-- Row 1: Area-focused charts --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- 🔥 Area Contribution -->
            <div class="card bg-base-100 shadow-xl rounded-2xl md:col-span-6 xl:col-span-3" wire:ignore>
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-0">Area Contribution</h3>
                    <div id="chartAreaContribution" class="w-full"></div>
                </div>
            </div>

            <!-- 🔥 Area Comparison -->
            <div class="card bg-base-100 shadow-xl rounded-2xl md:col-span-6 xl:col-span-3" wire:ignore>
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-0">Area Comparison</h3>
                    <div id="chartAreaHBar" class="w-full"></div>
                </div>
            </div>

            <!-- 🔥 Performance Overview -->
            <div class="card bg-base-100 shadow-xl rounded-2xl md:col-span-12 xl:col-span-6" wire:ignore>
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-0">Last Year vs Current Year</h3>
                    <div id="chartCombo" class="w-full"></div>
                </div>
            </div>
        </div>

        {{-- Row 2: Time-series charts --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div class="card bg-base-100 shadow-xl rounded-2xl" wire:ignore>
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-3">Sales Trend (TY vs LY)</h3>
                    <div id="chartSalesTrend" class="w-full h-56"></div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-xl rounded-2xl" wire:ignore>
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-3">Target vs Sales per Month</h3>
                    <div id="chartMonthlyBar" class="w-full h-56"></div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-xl rounded-2xl" wire:ignore>
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm mb-3">Growth Trend %</h3>
                    <div id="chartGrowthArea" class="w-full h-56"></div>
                </div>
            </div>
        </div>

        {{-- Tables Section --}}
        <div class="space-y-6">
            {{-- Main Detail Table --}}
            <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 border-b border-base-200 bg-gradient-to-r from-base-100 to-base-200 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-bold text-lg">Sales Performance per Month</h3>
                        <input wire:model.live.debounce.300ms="searchDetail" type="text" placeholder="Search month…" class="input input-bordered input-sm rounded-xl w-full max-w-xs" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>Month</th>
                                    <th class="text-right">Last Year</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-right">Current Year</th>
                                    <th class="text-center">Ach %</th>
                                    <th class="text-center">Growth %</th>
                                    <th class="text-right">Gap vs Target</th>
                                    <th class="text-right">Gap vs LY</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $row)
                                    <tr>
                                        <td class="font-bold text-primary">{{ $row->bulan_label }}</td>
                                        <td class="text-right font-mono">{{ number_format($row->ly_actual, 0, ',', '.') }}</td>
                                        <td class="text-right font-mono">{{ number_format($row->target, 0, ',', '.') }}</td>
                                        <td class="text-right font-mono font-bold">{{ number_format($row->actual, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <div class="badge {{ $row->ach_pct >= 100 ? 'badge-success' : ($row->ach_pct >= 80 ? 'badge-warning' : 'badge-error') }} font-bold">
                                                {{ number_format($row->ach_pct, 1) }}%
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="{{ $row->growth_pct >= 0 ? 'text-success' : 'text-error' }} font-bold text-xs">
                                                {{ $row->growth_pct >= 0 ? '↑' : '↓' }} {{ number_format(abs($row->growth_pct), 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-right font-mono {{ $row->gap_value >= 0 ? 'text-success' : 'text-error' }}">
                                            {{ number_format($row->gap_value, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right font-mono {{ $row->gap_vs_ly >= 0 ? 'text-success' : 'text-error' }}">
                                            {{ number_format($row->gap_vs_ly, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 bg-base-200">
                        {{ $details->links() }}
                    </div>
                </div>
            </div>

            {{-- Grid for Additional Tables --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                {{-- Top Performance by Ach% --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-primary/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-primary font-bold flex items-center text-sm uppercase tracking-tight">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            Top Area by Ach%
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr class="bg-base-200 text-[10px] uppercase">
                                    <th>Area</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-right">Sales</th>
                                    <th class="text-center">%Ach</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topByAchData as $r)
                                    <tr class="hover:bg-base-200 transition-colors">
                                        <td class="font-medium text-xs">{{ $r['area'] }}</td>
                                        <td class="text-right text-xs font-mono">{{ number_format($r['target'], 0, ',', '.') }}</td>
                                        <td class="text-right text-xs font-mono">{{ number_format($r['actual'], 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $r['ach'] >= 100 ? 'badge-success' : ($r['ach'] >= 80 ? 'badge-warning' : 'badge-error') }} badge-xs font-bold">{{ number_format($r['ach'], 1) }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Top Performance by Growth% --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-secondary/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-secondary font-bold flex items-center text-sm uppercase tracking-tight">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            Top Area by Growth%
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr class="bg-base-200 text-[10px] uppercase">
                                    <th>Area</th>
                                    <th class="text-right">Last Year</th>
                                    <th class="text-right">Current Year</th>
                                    <th class="text-center">% Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topByGrowthData as $r)
                                    <tr class="hover:bg-base-200 transition-colors">
                                        <td class="font-medium text-xs">{{ $r['area'] }}</td>
                                        <td class="text-right text-xs font-mono text-base-content/60">{{ number_format($r['ly'], 0, ',', '.') }}</td>
                                        <td class="text-right text-xs font-mono font-bold">{{ number_format($r['ty'], 0, ',', '.') }}</td>
                                        <td class="text-center text-xs font-bold {{ $r['growth'] >= 0 ? 'text-success' : 'text-error' }}">
                                            {{ $r['growth'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($r['growth']), 1) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Gap Sales vs Target --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-info/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-info font-bold flex items-center text-sm uppercase tracking-tight">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                            </svg>
                            Gap Sales vs Target (Area)
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr class="bg-base-200 text-[10px] uppercase">
                                    <th>Area</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-right">Sales</th>
                                    <th class="text-right">Gap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gapVsTargetData as $r)
                                    <tr class="hover:bg-base-200 transition-colors">
                                        <td class="font-medium text-xs">{{ $r['area'] }}</td>
                                        <td class="text-right text-xs font-mono text-base-content/60">{{ number_format($r['target'], 0, ',', '.') }}</td>
                                        <td class="text-right text-xs font-mono">{{ number_format($r['actual'], 0, ',', '.') }}</td>
                                        <td class="text-right text-xs font-mono font-bold {{ $r['gap'] >= 0 ? 'text-success' : 'text-error' }}">
                                            {{ number_format($r['gap'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Gap Last Year vs Current Year --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-warning/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-warning font-bold flex items-center text-sm uppercase tracking-tight">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Gap Last Year vs Current Year (Area)
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr class="bg-base-200 text-[10px] uppercase">
                                    <th>Area</th>
                                    <th class="text-right">Last Year</th>
                                    <th class="text-right">Current Year</th>
                                    <th class="text-right">Gap</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gapYoYData as $r)
                                    <tr class="hover:bg-base-200 transition-colors">
                                        <td class="font-medium text-xs">{{ $r['area'] }}</td>
                                        <td class="text-right text-xs font-mono text-base-content/60">{{ number_format($r['ly'], 0, ',', '.') }}</td>
                                        <td class="text-right text-xs font-mono">{{ number_format($r['ty'], 0, ',', '.') }}</td>
                                        <td class="text-right text-xs font-mono font-bold {{ $r['gap'] >= 0 ? 'text-success' : 'text-error' }}">
                                            {{ number_format($r['gap'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                let charts = {};

                function getThemeConfig() {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'neon-dark';
                    return {
                        isDark,
                        textColor: isDark ? '#94a3b8' : '#64748b',
                        titleColor: isDark ? '#f1f5f9' : '#0f172a',
                        gridColor: isDark ? '#334155' : '#f1f5f9',
                        tooltipTheme: isDark ? 'dark' : 'light',
                        lastYearColor: isDark ? '#cbd5e1' : '#94a3b8'
                    };
                }

                function initCharts() {
                    const raw = document.getElementById('chart-data');
                    if (!raw) return;
                    const d = JSON.parse(raw.textContent || '{}');

                    const { isDark, textColor, titleColor, gridColor, tooltipTheme, lastYearColor } = getThemeConfig();

                    const base = {
                        chart: {
                            fontFamily: 'Inter, sans-serif',
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                animateGradually: { enabled: true, delay: 150 },
                                dynamicAnimation: { enabled: true, speed: 350 }
                            },
                            parentHeightOffset: 0
                        },
                        grid: {
                            borderColor: gridColor,
                            strokeDashArray: 4,
                            padding: { top: 0, right: 10, bottom: 0, left: 10 }
                        },
                        dataLabels: { enabled: false },
                        tooltip: {
                            theme: tooltipTheme,
                            style: { fontSize: '12px' },
                            marker: { show: true }
                        }
                    };

                    const fmt = (val) => {
                        if (val === null || val === undefined || isNaN(val)) return '0';
                        const abs = Math.abs(val);
                        if (abs >= 1e12) return (val / 1e12).toFixed(1) + 'T';
                        if (abs >= 1e9)  return (val / 1e9).toFixed(1) + 'B';
                        if (abs >= 1e6)  return (val / 1e6).toFixed(1) + 'M';
                        if (abs >= 1e3)  return (val / 1e3).toFixed(1) + 'K';
                        return new Intl.NumberFormat('id-ID').format(val);
                    };

                    // Area Contribution — donut with labels
                    const chartElement = document.querySelector('#chartAreaContribution');
                    if (d.contribution && chartElement) {
                        if (charts.contribution && typeof charts.contribution.destroy === 'function') {
                            charts.contribution.destroy();
                        }

                        charts.contribution = new ApexCharts(chartElement, {
                            ...base,
                            chart: {
                                ...base.chart,
                                type: 'donut',
                                height: 320,
                                dropShadow: {
                                    enabled: true,
                                    top: 2,
                                    left: 0,
                                    blur: 6,
                                    opacity: 0.08
                                }
                            },
                            series: Array.isArray(d.contribution.series) ? d.contribution.series.map(Number) : [],
                            labels: d.contribution.labels || [],
                            colors: ['#6366f1', '#38bdf8', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'],
                            stroke: {
                                width: 2,
                                colors: ['#ffffff']
                            },
                            plotOptions: {
                                pie: {
                                    customScale: 1.05,
                                    expandOnClick: true,
                                    donut: {
                                        size: '70%',
                                        labels: {
                                            show: true,
                                            name: {
                                                show: true,
                                                fontSize: '13px',
                                                fontWeight: 500,
                                                color: textColor,
                                                offsetY: -8
                                            },
                                            value: {
                                                show: true,
                                                fontSize: '20px',
                                                fontWeight: 700,
                                                color: titleColor,
                                                formatter: (val) => fmt(val)
                                            },
                                            total: {
                                                show: true,
                                                label: 'Total Area Sales',
                                                fontSize: '12px',
                                                color: textColor,
                                                formatter: (w) => {
                                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                    return fmt(total);
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: (val) => val < 5 ? '' : val.toFixed(1) + '%',
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    colors: ['#fff']
                                },
                                dropShadow: { enabled: false }
                            },
                            legend: {
                                show: false,
                                position: 'bottom',
                                horizontalAlign: 'center',
                                fontSize: '11px',
                                labels: { colors: textColor },
                                markers: { radius: 12 },
                                itemMargin: { horizontal: 10, vertical: 6 }
                            },
                            tooltip: {
                                enabled: true,
                                theme: tooltipTheme,
                                y: {
                                    formatter: function(value) {
                                        return new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            },
                            states: {
                                hover: {
                                    filter: {
                                        type: 'darken',
                                        value: 0.85
                                    }
                                }
                            }
                        });
                        charts.contribution.render();
                    }

                    // Area Comparison — horizontal bar
                    if (d.areaHBar && document.querySelector('#chartAreaHBar')) {
                        if (charts.areaH && typeof charts.areaH.destroy === 'function') {
                            charts.areaH.destroy();
                        }

                        charts.areaH = new ApexCharts(document.querySelector('#chartAreaHBar'), {
                            ...base,
                            chart: {
                                ...base.chart,
                                type: 'bar',
                                height: 320,
                                toolbar: { show: false },
                                animations: {
                                    enabled: true,
                                    easing: 'easeinout',
                                    speed: 700
                                }
                            },
                            series: [
                                { name: 'Actual', data: d.areaHBar.actuals || [] },
                                { name: 'Target', data: d.areaHBar.targets || [] }
                            ],
                            plotOptions: {
                                bar: {
                                    horizontal: true,
                                    borderRadius: 8,
                                    barHeight: '55%',
                                    distributed: false
                                }
                            },
                            dataLabels: { enabled: false },
                            stroke: {
                                show: true,
                                width: 2,
                                colors: ['transparent']
                            },
                            grid: {
                                borderColor: gridColor,
                                strokeDashArray: 4,
                                xaxis: { lines: { show: true } },
                                yaxis: { lines: { show: false } },
                                padding: { top: 0, right: 10, bottom: 0, left: 10 }
                            },
                            xaxis: {
                                categories: d.areaHBar.labels || [],
                                labels: {
                                    style: { fontSize: '11px', colors: textColor },
                                    formatter: fmt
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: {
                                labels: {
                                    style: {
                                        fontSize: '12px',
                                        fontWeight: 500,
                                        colors: titleColor
                                    }
                                }
                            },
                            colors: ['#6366f1', '#e2e8f0'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shade: 'light',
                                    type: 'horizontal',
                                    shadeIntensity: 0.3,
                                    gradientToColors: ['#818cf8', '#cbd5f1'],
                                    inverseColors: false,
                                    opacityFrom: 0.95,
                                    opacityTo: 0.85,
                                    stops: [0, 100]
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                theme: tooltipTheme,
                                style: { fontSize: '12px' },
                                marker: { show: true },
                                x: { show: true },
                                y: {
                                    formatter: (val, { seriesIndex, dataPointIndex, w }) => {
                                        if (seriesIndex === 0) {
                                            const target = w.globals.initialSeries[1].data[dataPointIndex];
                                            const ach = target > 0 ? ((val / target) * 100).toFixed(1) : 0;
                                            return `
                                                <div style="padding:4px 0">
                                                    <div><b>${fmt(val)}</b></div>
                                                    <div style="color:${textColor};font-size:11px">
                                                        ${ach}% achievement
                                                    </div>
                                                </div>
                                            `;
                                        }
                                        return `<b>${fmt(val)}</b>`;
                                    }
                                }
                            },
                            legend: {
                                show: true,
                                position: 'top',
                                horizontalAlign: 'right',
                                fontSize: '11px',
                                labels: { colors: textColor },
                                markers: { radius: 12 },
                                itemMargin: { horizontal: 8 }
                            }
                        });
                        charts.areaH.render();
                    }

                    // Performance Overview — combo (This Year vs Last Year + Growth %)
                    if (d.combo && document.querySelector('#chartCombo')) {
                        if (charts.combo) charts.combo.destroy();

                        charts.combo = new ApexCharts(document.querySelector('#chartCombo'), {
                            ...base,
                            chart: {
                                ...base.chart,
                                type: 'line',
                                height: 300,
                                toolbar: { show: false },
                                zoom: { enabled: false }
                            },
                            series: [
                                { name: 'This Year', type: 'column', data: d.combo.ty || [] },
                                { name: 'Last Year', type: 'column', data: d.combo.ly || [] },
                                { name: 'Growth %', type: 'line', data: d.combo.growth || [] }
                            ],
                            xaxis: {
                                categories: d.combo.labels || [],
                                labels: { style: { fontSize: '11px', colors: textColor } },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            colors: ['#6366f1', '#e2e8f0', '#10b981'],
                            stroke: { width: [0, 0, 3], curve: 'smooth' },
                            markers: { size: 4, strokeWidth: 2, hover: { size: 6 } },
                            grid: { borderColor: gridColor, strokeDashArray: 4 },
                            dataLabels: { enabled: false },
                            yaxis: [
                                {
                                    seriesName: 'This Year',
                                    title: { text: '' },
                                    labels: { formatter: fmt, style: { fontSize: '11px', colors: textColor } }
                                },
                                { seriesName: 'This Year', show: false },
                                {
                                    seriesName: 'Growth %',
                                    opposite: true,
                                    min: -50,
                                    max: 100,
                                    labels: {
                                        formatter: (v) => v == null ? '-' : v.toFixed(1) + '%',
                                        style: { fontSize: '11px', colors: '#10b981' }
                                    }
                                }
                            ],
                            plotOptions: {
                                bar: { borderRadius: 6, columnWidth: '45%', distributed: false }
                            },
                            fill: {
                                type: ['gradient', 'solid', 'gradient'],
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    shadeIntensity: 0.5,
                                    opacityFrom: [0.85, 0.4, 0.7],
                                    opacityTo: [0.6, 0.4, 0.2],
                                    stops: [0, 100]
                                }
                            },
                            legend: {
                                show: true,
                                position: 'top',
                                horizontalAlign: 'right',
                                fontSize: '11px',
                                labels: { colors: textColor },
                                markers: { radius: 12, width: 8, height: 8 }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                theme: tooltipTheme,
                                y: {
                                    formatter: (v, { seriesIndex }) =>
                                        seriesIndex < 2 ? `<b>${fmt(v)}</b>` : `<b>${v == null ? '-' : v.toFixed(1) + '%'}</b>`
                                }
                            }
                        });
                        charts.combo.render();
                    }

                    // Sales Trend — mixed area + dotted line
                    if (d.trend && document.querySelector('#chartSalesTrend')) {
                        if (charts.trend) charts.trend.destroy();
                        charts.trend = new ApexCharts(document.querySelector('#chartSalesTrend'), {
                            ...base,
                            chart: {
                                ...base.chart,
                                type: 'line',
                                stacked: false,
                                toolbar: { show: false },
                                zoom: { enabled: false },
                                dropShadow: {
                                    enabled: true,
                                    top: 2,
                                    left: 1,
                                    blur: 8,
                                    opacity: 0.25
                                }
                            },
                            series: [
                                { name: 'This Year', type: 'area', data: d.trend.ty || [] },
                                { name: 'Last Year', type: 'line', data: d.trend.ly || [] }
                            ],
                            colors: ['#22C55E', '#F59E0B'],
                            stroke: {
                                curve: 'smooth',
                                width: [4, 3],
                                dashArray: [0, 8]
                            },
                            fill: {
                                type: ['gradient', 'solid'],
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    shadeIntensity: 1,
                                    inverseColors: false,
                                    gradientToColors: ['#86EFAC'],
                                    opacityFrom: 0.75,
                                    opacityTo: 0.18,
                                    stops: [0, 60, 100]
                                }
                            },
                            markers: {
                                size: [5, 4],
                                strokeWidth: 2,
                                hover: { size: 7 }
                            },
                            dataLabels: { enabled: false },
                            grid: {
                                borderColor: 'rgba(148,163,184,0.12)',
                                strokeDashArray: 4
                            },
                            xaxis: {
                                categories: d.trend.labels || [],
                                labels: { style: { colors: textColor, fontSize: '11px' } },
                                axisBorder: { color: gridColor },
                                axisTicks: { color: gridColor }
                            },
                            yaxis: {
                                labels: {
                                    formatter: fmt,
                                    style: { colors: textColor }
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                theme: tooltipTheme,
                                y: { formatter: (v) => `<b>${fmt(v)}</b>` }
                            },
                            legend: {
                                show: true,
                                position: 'top',
                                horizontalAlign: 'right',
                                fontSize: '11px',
                                labels: { colors: textColor },
                                markers: { radius: 12 }
                            }
                        });
                        charts.trend.render();
                    }

                    // Target vs Sales per Month
                    if (d.monthly && document.querySelector('#chartMonthlyBar')) {
                        if (charts.monthly) charts.monthly.destroy();
                        charts.monthly = new ApexCharts(document.querySelector('#chartMonthlyBar'), {
                            ...base,
                            chart: { ...base.chart, type: 'line' },
                            series: [
                                { name: 'Actual', type: 'column', data: d.monthly.actuals || [] },
                                { name: 'Target', type: 'column', data: d.monthly.targets || [] },
                                { name: 'Achievement %', type: 'line', data: d.monthly.achievements || [] }
                            ],
                            xaxis: { categories: d.monthly.labels || [] },
                            colors: ['#6366f1', '#e2e8f0', '#f59e0b'],
                            stroke: { width: [0, 0, 3] },
                            yaxis: [
                                { seriesName: 'Actual', labels: { formatter: fmt } },
                                { seriesName: 'Actual', show: false },
                                {
                                    seriesName: 'Achievement %',
                                    opposite: true,
                                    show: false,
                                    title: { text: 'Achievement %' },
                                    min: 0,
                                    max: 150,
                                    labels: { formatter: (v) => v == null ? '-' : v.toFixed(1) + '%' }
                                }
                            ],
                            plotOptions: {
                                bar: {
                                    borderRadius: 6,
                                    columnWidth: '50%',
                                    dataLabels: { position: 'top' }
                                }
                            },
                            fill: {
                                type: ['gradient', 'solid', 'gradient'],
                                gradient: {
                                    shade: 'light',
                                    type: 'vertical',
                                    shadeIntensity: 0.5,
                                    opacityFrom: [0.85, 0.4, 0.7],
                                    opacityTo: [0.6, 0.4, 0.2],
                                    stops: [0, 100]
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                theme: tooltipTheme,
                                y: {
                                    formatter: (v, { seriesIndex }) =>
                                        seriesIndex < 2 ? `<b>${fmt(v)}</b>` : `<b>${v == null ? '-' : v.toFixed(1) + '%'}</b>`
                                }
                            },
                            legend: { show: true, position: 'top', horizontalAlign: 'right', fontSize: '11px', labels: { colors: textColor } }
                        });
                        charts.monthly.render();
                    }

                    // Growth Trend — area
                    if (d.growth && document.querySelector('#chartGrowthArea')) {
                        if (charts.growth) charts.growth.destroy();
                        charts.growth = new ApexCharts(document.querySelector('#chartGrowthArea'), {
                            ...base,
                            chart: { ...base.chart, type: 'area' },
                            series: [{ name: 'Growth %', data: d.growth.growth || [] }],
                            xaxis: { categories: d.growth.labels || [] },
                            colors: ['#10b981'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.4,
                                    opacityTo: 0.1,
                                    stops: [0, 90, 100]
                                }
                            },
                            yaxis: { labels: { formatter: (val) => val == null ? '-' : val.toFixed(1) + '%', style: { colors: textColor } } },
                            tooltip: { theme: tooltipTheme, y: { formatter: (val) => `<b>${val == null ? '-' : val.toFixed(1) + '%'}</b>` } },
                            legend: { show: false }
                        });
                        charts.growth.render();
                    }
                }

                initCharts();

                // MutationObserver to detect theme changes live
                const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                                Object.values(charts).forEach(c => { if (c && typeof c.destroy === 'function') c.destroy(); });
                                charts = {};
                                setTimeout(initCharts, 150);
                            }
                        });
                    });
                    observer.observe(document.documentElement, { attributes: true });

                    Livewire.on('charts-updated', () => {
                        Object.values(charts).forEach(c => { if (c && typeof c.destroy === 'function') c.destroy(); });
                        charts = {};
                        setTimeout(initCharts, 150);
                    });
            });
        </script>
    @endpush
</div>
