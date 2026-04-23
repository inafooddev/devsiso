<div>
    <x-slot name="title">Analytics Dashboard</x-slot>

    <div class="p-6 space-y-6 bg-base-200 min-h-screen">
        
        {{-- Toolbar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 bg-base-100 rounded-2xl shadow-xl px-6 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="font-bold text-lg mr-2">Analytics Dashboard</h2>
                <span class="badge badge-outline badge-primary">{{ $selectedYear }}</span>
                <span class="badge badge-outline">{{ date('M', mktime(0,0,0,$selectedMonthFrom,1)) }} – {{ date('M', mktime(0,0,0,$selectedMonthTo,1)) }}</span>
                @if($selectedRegFest !== 'ALL')<span class="badge badge-secondary badge-outline">{{ $selectedRegFest }}</span>@endif
                <span class="badge badge-outline">{{ count($selectedRegions) }} Region(s)</span>
            </div>
            <button wire:click="openFilterModal" class="btn btn-primary btn-sm rounded-xl gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filters
                <span wire:loading wire:target="applyFilter" class="loading loading-spinner loading-xs"></span>
            </button>
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
                                @foreach($yearOptions as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach
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
                                @foreach(range(1,12) as $m)<option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-semibold">To Month</span></label>
                            <select wire:model="selectedMonthTo" class="select select-bordered select-sm rounded-xl">
                                @foreach(range(1,12) as $m)<option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="label-text font-semibold">Regions <span class="text-base-content/50 font-normal">({{ count($selectedRegions) }}/{{ count($regionsOption) }})</span></span>
                            <div class="flex gap-2">
                                <button wire:click="selectAllRegions" class="btn btn-xs btn-ghost text-primary">All</button>
                                <button wire:click="clearAllRegions" class="btn btn-xs btn-ghost text-error">Clear</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 max-h-44 overflow-y-auto p-3 bg-base-200 rounded-xl">
                            @foreach($regionsOption as $reg)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-base-100 rounded-lg p-1.5 transition">
                                <input type="checkbox" wire:model="selectedRegions" value="{{ $reg }}" class="checkbox checkbox-xs checkbox-primary" />
                                <span class="text-xs">{{ $reg }}</span>
                            </label>
                            @endforeach
                        </div>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>
                    <div class="flex items-center mt-4 gap-2">
                        <div class="flex items-center text-xs {{ ($kpiData['avg_monthly_growth'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ ($kpiData['avg_monthly_growth'] ?? 0) >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" /></svg>
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
                        @if($insight['type'] == 'success') <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @elseif($insight['type'] == 'error') <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        @elseif($insight['type'] == 'warning') <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        @else <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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

        {{-- Chart data — Livewire updates this on every render --}}
        <script type="application/json" id="chart-data">
        {
            "contribution": @json(json_decode($chartRegionContribution, true) ?: new stdClass),
            "trend":        @json(json_decode($chartSalesTrend, true)         ?: new stdClass),
            "monthly":      @json(json_decode($chartMonthlyBar, true)         ?: new stdClass),
            "growth":       @json(json_decode($chartGrowthArea, true)         ?: new stdClass),
            "regionHBar":   @json(json_decode($chartRegionHBar, true)         ?: new stdClass),
            "combo":        @json(json_decode($chartCombo, true)              ?: new stdClass)
        }
        </script>

        {{-- Row 1: Region-focused charts --}}
<div class="grid grid-cols-1 md:grid-cols-12 gap-4">
    
    <!-- 🔥 Region Contribution: Lebar 25% (3/12) di layar besar -->
    <div class="card bg-base-100 shadow-xl rounded-2xl md:col-span-6 xl:col-span-3" wire:ignore>
        <div class="card-body p-4">
            <h3 class="font-semibold text-sm mb-0">Region Contribution</h3>
            <div id="chartRegionContribution" class="w-full"></div>
        </div>
    </div>
    
    <!-- 🔥 Region Comparison: Lebar 33% (4/12) di layar besar -->
    <div class="card bg-base-100 shadow-xl rounded-2xl md:col-span-6 xl:col-span-3" wire:ignore>
        <div class="card-body p-4">
            <h3 class="font-semibold text-sm mb-0">Region Comparison</h3>
            <div id="chartRegionHBar" class="w-full"></div>
        </div>
    </div>
    
    <!-- 🔥 Performance Overview: Lebar 42% (5/12) di layar besar (Paling Lebar) -->
    <div class="card bg-base-100 shadow-xl rounded-2xl md:col-span-12 xl:col-span-6" wire:ignore>
        <div class="card-body p-4">
            <h3 class="font-semibold text-sm mb-0">Performance Overview</h3>
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
                        <h3 class="font-bold text-lg">Sales Performance Detail</h3>
                        <input wire:model.live.debounce.300ms="searchDetail" type="text" placeholder="Search region or branch…" class="input input-bordered input-sm rounded-xl w-full max-w-xs" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>Month</th>
                                    <th>Region</th>
                                    <th>Branch</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-right">Sales</th>
                                    <th class="text-center">Achievement %</th>
                                    <th class="text-center">Growth %</th>
                                    <th class="text-right">Gap Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $row)
                                <tr>
                                    <td class="font-medium">{{ $row->bulan_label }}</td>
                                    <td>{{ $row->region }}</td>
                                    <td class="text-xs">{{ $row->cabang }}</td>
                                    <td class="text-right font-mono">{{ number_format($row->target, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono">{{ number_format($row->actual, 0, ',', '.') }}</td>
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
                {{-- Underperforming Regions --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-error/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-error font-bold flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>Underperforming Regions</span>
                        <input wire:model.live.debounce.300ms="searchUnderperform" type="text" placeholder="Search branch…" class="input input-bordered input-sm rounded-xl w-full max-w-xs" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-right">Sales</th>
                                    <th class="text-right">Gap</th>
                                    <th class="text-center">Ach%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($underperformingData as $r)
                                <tr>
                                    <td class="font-medium text-xs">{{ $r['cabang'] }}</td>
                                    <td class="text-right text-xs">{{ number_format($r['target'], 0, ',', '.') }}</td>
                                    <td class="text-right text-xs">{{ number_format($r['actual'], 0, ',', '.') }}</td>
                                    <td class="text-right text-xs text-error">{{ number_format($r['gap'], 0, ',', '.') }}</td>
                                    <td class="text-center"><span class="badge badge-error badge-xs">{{ number_format($r['achievement_pct'], 1) }}%</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Top Performing Regions --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-success/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-success font-bold flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>Top Performing Regions</span>
                        <input wire:model.live.debounce.300ms="searchTop" type="text" placeholder="Search branch…" class="input input-bordered input-sm rounded-xl w-full max-w-xs" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th class="text-right">Sales</th>
                                    <th class="text-center">Growth%</th>
                                    <th class="text-center">Contr%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topRegionsData as $r)
                                <tr>
                                    <td class="font-medium text-xs">{{ $r['cabang'] }}</td>
                                    <td class="text-right text-xs font-bold">{{ number_format($r['actual'], 0, ',', '.') }}</td>
                                    <td class="text-center text-xs {{ $r['growth_pct'] >= 0 ? 'text-success' : 'text-error' }}">{{ number_format($r['growth_pct'], 1) }}%</td>
                                    <td class="text-center text-xs font-semibold">{{ number_format($r['contribution_pct'], 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Monthly Performance Summary --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-info/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-info font-bold flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>Monthly Performance</span>
                        <input wire:model.live.debounce.300ms="searchMonthly" type="text" placeholder="Search month…" class="input input-bordered input-sm rounded-xl w-full max-w-xs" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-right">Sales</th>
                                    <th class="text-right">Target</th>
                                    <th class="text-center">Growth%</th>
                                    <th class="text-center">Ach%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlySummaryData as $r)
                                <tr>
                                    <td class="font-medium">{{ $r['month'] }}</td>
                                    <td class="text-right text-xs">{{ number_format($r['actual'], 0, ',', '.') }}</td>
                                    <td class="text-right text-xs text-base-content/60">{{ number_format($r['target'], 0, ',', '.') }}</td>
                                    <td class="text-center text-xs font-bold {{ $r['growth_pct'] >= 0 ? 'text-success' : 'text-error' }}">{{ number_format($r['growth_pct'], 1) }}%</td>
                                    <td class="text-center"><span class="badge {{ $r['achievement_pct'] >= 100 ? 'badge-success' : 'badge-warning' }} badge-xs">{{ number_format($r['achievement_pct'], 1) }}%</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Gap Analysis Table --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-4 border-b border-base-200 bg-warning/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-warning font-bold flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>Gap Analysis</span>
                        <input wire:model.live.debounce.300ms="searchGap" type="text" placeholder="Search branch or month…" class="input input-bordered input-sm rounded-xl w-full max-w-xs" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Month</th>
                                    <th class="text-right">Gap Value</th>
                                    <th class="text-center">Gap%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gapAnalysisData as $r)
                                <tr>
                                    <td class="font-medium text-[10px]">{{ $r['cabang'] }}</td>
                                    <td class="text-xs">{{ $r['month'] }}</td>
                                    <td class="text-right text-xs {{ $r['gap_value'] >= 0 ? 'text-success' : 'text-error' }}">{{ number_format($r['gap_value'], 0, ',', '.') }}</td>
                                    <td class="text-center text-xs font-bold {{ $r['gap_pct'] >= 0 ? 'text-success' : 'text-error' }}">{{ number_format($r['gap_pct'], 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- YoY Comparison Table --}}
                <div class="card bg-base-100 shadow-xl rounded-2xl overflow-hidden xl:col-span-2">
                    <div class="p-4 border-b border-base-200 bg-secondary/5 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-secondary font-bold flex items-center"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>YoY Monthly Comparison</span>
                        <input wire:model.live.debounce.300ms="searchYoy" type="text" placeholder="Search month…" class="input input-bordered input-sm rounded-xl w-full max-w-xs" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-right">Sales Last Year ({{ $selectedYear - 1 }})</th>
                                    <th class="text-right font-bold">Sales Current Year ({{ $selectedYear }})</th>
                                    <th class="text-right">Growth Value</th>
                                    <th class="text-center">Growth %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($yoyComparisonData as $r)
                                <tr>
                                    <td class="font-bold">{{ $r['month'] }}</td>
                                    <td class="text-right font-mono">{{ number_format($r['sales_ly'], 0, ',', '.') }}</td>
                                    <td class="text-right font-mono font-bold text-primary">{{ number_format($r['sales_ty'], 0, ',', '.') }}</td>
                                    <td class="text-right font-mono {{ ($r['sales_ty'] - $r['sales_ly']) >= 0 ? 'text-success' : 'text-error' }}">
                                        {{ number_format($r['sales_ty'] - $r['sales_ly'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="badge {{ $r['growth_pct'] >= 0 ? 'badge-success' : 'badge-error' }} badge-outline font-bold">
                                            {{ $r['growth_pct'] >= 0 ? '+' : '' }}{{ number_format($r['growth_pct'], 1) }}%
                                        </div>
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

            function initCharts() {
                const raw = document.getElementById('chart-data');
                if (!raw) return;
                const d = JSON.parse(raw.textContent || '{}');
                const base = {
                    chart: { 
                        fontFamily: 'Inter, sans-serif', 
                        toolbar: { show: false }, 
                        zoom: { enabled: false }, 
                        animations: { enabled: true, easing: 'easeinout', speed: 600 },
                        height: 260,
                        parentHeightOffset: 0
                    },
                    dataLabels: { enabled: false },
                    tooltip: { theme: 'dark' }
                };
                // Number formatter: 1,500,000 → 1.5M
                const fmt = (val) => {
                    if (val === null || val === undefined || isNaN(val)) return '0';
                    const abs = Math.abs(val);
                    if (abs >= 1e12) return (val / 1e12).toFixed(1) + 'T';
                    if (abs >= 1e9)  return (val / 1e9).toFixed(1) + 'B';
                    if (abs >= 1e6)  return (val / 1e6).toFixed(1) + 'M';
                    if (abs >= 1e3)  return (val / 1e3).toFixed(1) + 'K';
                    return Number(val).toLocaleString();
                };

                // Region Contribution — donut with labels
                // 1. Simpan referensi selector ke variabel agar lebih cepat dan bersih
const chartElement = document.querySelector('#chartRegionContribution');

if (d.contribution && chartElement) {
    // 2. Gunakan metode destroy yang aman
    if (charts.contribution && typeof charts.contribution.destroy === 'function') {
        charts.contribution.destroy();
    }

    // 3. Inisialisasi Chart
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

        // Pastikan data series adalah angka (ApexCharts sensitif terhadap string)
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
                            color: '#94a3b8',
                            offsetY: -8
                        },
                        value: {
                            show: true,
                            fontSize: '20px',
                            fontWeight: 700,
                            color: '#0f172a',
                            // Formatter nilai di tengah donut
                            formatter: (val) => {
                                const num = parseFloat(val);
                                return isNaN(num) ? val : num.toLocaleString() + '%';
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total Sales',
                            fontSize: '12px',
                            color: '#64748b',
                            formatter: (w) => {
                                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                if (total >= 1e9) return (total / 1e9).toFixed(1) + 'B';
                                if (total >= 1e6) return (total / 1e6).toFixed(1) + 'M';
                                if (total >= 1e3) return (total / 1e3).toFixed(1) + 'K';
                                return total.toFixed(0);
                            }
                        }
                    }
                }
            }
        },

        dataLabels: {
            enabled: true,
            // Sembunyikan label jika slice terlalu kecil agar tidak tumpang tindih
            formatter: (val) => val < 5 ? '' : val.toFixed(1),
            style: {
                fontSize: '11px',
                fontWeight: 600,
                colors: ['#fff']
            },
            dropShadow: { enabled: false }
        },

        legend: {
            show: true, // Ubah ke true jika ingin melihat daftar di bawah
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '11px',
            labels: { colors: '#64748b' },
            markers: { radius: 12 },
            itemMargin: { horizontal: 10, vertical: 6 }
        },

tooltip: {
    enabled: true, // Ensure this is true
    theme: 'light',
    y: {
        formatter: function(val) {
            // Checks if val exists and is a number
            if (typeof val !== 'undefined' && val !== null) {
                return val.toFixed(1) ;
            }
            return "No Data";
        },
        title: {
            formatter: (seriesName) => seriesName + ':'
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

                // Region Comparison — horizontal bar
                if (d.regionHBar && document.querySelector('#chartRegionHBar')) {
                    charts.regionH = new ApexCharts(document.querySelector('#chartRegionHBar'), {
                        ...base,
                        chart: { ...base.chart, type: 'bar' },
                        series: [{ name: 'Actual', data: d.regionHBar.actuals || [] }, { name: 'Target', data: d.regionHBar.targets || [] }],
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%' } },
                        xaxis: { categories: d.regionHBar.labels || [], labels: { formatter: fmt } },
                        colors: ['#6366f1', '#e2e8f0'],
                        tooltip: { y: { formatter: fmt } },
                        legend: { position: 'bottom',show: false, fontSize: '11px', itemMargin: { horizontal: 4 } }
                    });
                    charts.regionH.render();
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
            labels: {
                style: { fontSize: '11px', colors: '#94a3b8' }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },

        colors: ['#6366f1', '#e2e8f0', '#10b981'],

        stroke: {
            width: [0, 0, 3],
            curve: 'smooth'
        },

        markers: {
            size: 4,
            strokeWidth: 2,
            hover: { size: 6 }
        },

        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4
        },

        dataLabels: {
            enabled: false
        },

        yaxis: [
            {
                seriesName: 'This Year',
                title: { text: '' },
                labels: {
                    formatter: fmt,
                    style: { fontSize: '11px', colors: '#64748b' }
                }
            },
            {
                seriesName: 'This Year',
                show: false
            },
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
            bar: {
                borderRadius: 6,
                columnWidth: '45%',
                distributed: false
            }
        },

        fill: {
            type: ['solid', 'solid', 'gradient'],
            gradient: {
                shade: 'light',
                type: 'vertical',
                opacityFrom: 0.7,
                opacityTo: 0.2
            }
        },

        legend: {
            show: true,
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '12px',
            labels: { colors: '#64748b' },
            markers: { radius: 12 }
        },

        tooltip: {
            shared: true,
            intersect: false,
            theme: 'light',
            y: {
                formatter: (v, { seriesIndex }) =>
                    seriesIndex < 2
                        ? fmt(v)
                        : (v == null ? '-' : v.toFixed(1) + '%')
            }
        }
    });

    charts.combo.render();
}

                // Sales Trend — line
                if (d.trend && document.querySelector('#chartSalesTrend')) {
                    charts.trend = new ApexCharts(document.querySelector('#chartSalesTrend'), {
                        ...base,
                        chart: { ...base.chart, type: 'line' },
                        series: [{ name: 'This Year', data: d.trend.ty || [] }, { name: 'Last Year', data: d.trend.ly || [] }],
                        xaxis: { categories: d.trend.labels || [] },
                        colors: ['#6366f1', '#94a3b8'],
                        stroke: { curve: 'smooth', width: 3 },
                        markers: { size: 4 },
                        yaxis: { labels: { formatter: fmt } },
                        tooltip: { y: { formatter: fmt } },
                        legend: { position: 'bottom',show: false, fontSize: '11px', itemMargin: { horizontal: 4 } }
                    });
                    charts.trend.render();
                }

                // Target vs Sales — combo
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
                            { seriesName: 'Actual', title: { text: 'Value' }, labels: { formatter: fmt } },
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
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                        tooltip: { y: { formatter: (v, { seriesIndex }) => seriesIndex < 2 ? fmt(v) : v.toFixed(1) + '%' } },
                        legend: { position: 'bottom',show: false, fontSize: '11px', itemMargin: { horizontal: 4 } }
                    });
                    charts.monthly.render();
                }

                // Growth Trend — area
                if (d.growth && document.querySelector('#chartGrowthArea')) {
                    charts.growth = new ApexCharts(document.querySelector('#chartGrowthArea'), {
                        ...base,
                        chart: { ...base.chart, type: 'area' },
                        series: [{ name: 'Growth %', data: d.growth.growth || [] }],
                        xaxis: { categories: d.growth.labels || [] },
                        colors: ['#10b981'],
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 90, 100] } },
                        yaxis: { labels: { formatter: (val) => val.toFixed(1) + '%' } },
                        legend: { position: 'bottom',show: false, fontSize: '11px', itemMargin: { horizontal: 4 } }
                    });
                    charts.growth.render();
                }
            }

            initCharts();
            Livewire.on('charts-updated', () => {
                Object.values(charts).forEach(c => { if (c && typeof c.destroy === 'function') c.destroy(); });
                charts = {};
                setTimeout(initCharts, 150);
            });
        });
    </script>
    @endpush
</div>
