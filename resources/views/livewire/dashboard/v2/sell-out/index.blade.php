<div class="flex flex-col flex-1 w-full h-full min-h-0" x-data="dashboardV2()">
<style>
    /* Force Tailwind Cyan & Neon Colors in case JIT compiler missed them */
    .bg-cyan-300 { background-color: #5EEAD4 !important; }
    .bg-cyan-400 { background-color: #20C997 !important; }
    .bg-cyan-500 { background-color: #14B8A6 !important; }
    .text-cyan-300 { color: #5EEAD4 !important; }
    .text-cyan-400 { color: #20C997 !important; }
    .text-cyan-500 { color: #06b6d4 !important; }
    .bg-matrix-header { background-color: #20C997 !important; }
    .border-matrix-header { border-color: #1a9f77 !important; }
</style>
<!-- Global Tab Navigation (Component already handles negative margins to touch edges) -->
<x-ui.tab-menu>
    <x-ui.tab-item href="{{ route('dashboard.v2.sellin') }}" :active="false" :navigate="false" icon="o-chart-bar">
        Sell In
    </x-ui.tab-item>
    <x-ui.tab-item href="{{ route('dashboard.v2.sellout') }}" :active="true" :navigate="false" icon="o-shopping-cart">
        Sell Out
    </x-ui.tab-item>
</x-ui.tab-menu>

<!-- Scrollable Content Area -->
<!-- Negate main padding on sides/bottom so scrollbar touches screen edge, then pad inside -->
<div class="flex-1 overflow-y-auto -mx-3 md:-mx-4 lg:-mx-6 -mb-3 md:-mb-4 lg:-mb-6 px-3 md:px-4 lg:px-6 pt-0 pb-10 space-y-6 relative">
    
    <!-- Sticky Header & KPI Wrapper -->
    <div class="sticky top-0 z-50 bg-base-200 -mx-3 md:-mx-4 lg:-mx-6 px-3 md:px-4 lg:px-6 pt-4 pb-4 shadow-md border-b border-base-300 space-y-6">
        
        <!-- Header + Inline Filter Bar -->
        <div class="flex flex-col gap-4 relative z-50">
            <!-- Row 2: Filters -->
            <div class="flex flex-wrap items-center justify-between gap-4 bg-base-100/50 p-2 rounded-xl border border-base-200">
                
                <!-- Kiri: Breakdown -->
                <div class="join">
                    @hasanyrole('admin|spm')
                        <button
                            wire:click="$set('breakdownBy', 'Nasional')"
                            class="join-item btn btn-sm {{ $breakdownBy === 'Nasional' ? 'btn-primary' : 'btn-ghost border border-base-300' }}"
                        >
                            Nasional
                        </button>
                    @endhasanyrole
                    @foreach(['Region','Area','Supervisor','Cabang'] as $opt)
                        <button
                            wire:click="$set('breakdownBy', '{{ $opt }}')"
                            class="join-item btn btn-sm {{ $breakdownBy === $opt ? 'btn-primary' : 'btn-ghost border border-base-300' }}"
                        >
                            {{ $opt }}
                        </button>
                    @endforeach
                </div>

                <!-- Kanan: Dynamic Filter, Tahun, Bulan, Tipe -->
                <div class="flex flex-wrap items-center gap-3">
                    
                    <!-- DYNAMIC CONTEXTUAL FILTER -->
                    @if($breakdownBy === 'Region')
                        <select wire:key="filter-region" wire:model.live="filterRegion" class="select select-sm select-bordered rounded-xl min-w-[150px] font-semibold text-primary border-primary">
                            <option value="">Pilih Region...</option>
                            @foreach($listRegions as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    @elseif($breakdownBy === 'Area')
                        <select wire:key="filter-area" wire:model.live="filterArea" class="select select-sm select-bordered rounded-xl min-w-[150px] font-semibold text-primary border-primary">
                            <option value="">Pilih Area...</option>
                            @foreach($listAreas as $a)
                                <option value="{{ $a }}">{{ $a }}</option>
                            @endforeach
                        </select>
                    @elseif($breakdownBy === 'Supervisor')
                        <select wire:key="filter-supervisor" wire:model.live="filterSupervisor" class="select select-sm select-bordered rounded-xl min-w-[150px] font-semibold text-primary border-primary">
                            <option value="">Pilih Supervisor...</option>
                            @foreach($listSupervisors as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    @elseif($breakdownBy === 'Cabang')
                        <select wire:key="filter-cabang" wire:model.live="filterCabang" class="select select-sm select-bordered rounded-xl min-w-[150px] font-semibold text-primary border-primary">
                            <option value="">Pilih Cabang...</option>
                            @foreach($listCabangs as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    @endif
                    
                    <!-- Year -->
                    <select wire:model.live="selectedYear" class="select select-sm select-bordered rounded-xl min-w-[90px] font-semibold">
                        @foreach($yearOptions as $y)
                            <option value="{{ $y['id'] }}">{{ $y['name'] }}</option>
                        @endforeach
                    </select>

                    <!-- Month Dropdown with Checkboxes -->
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-sm btn-outline rounded-xl border-base-300 px-4">
                            <x-icon name="o-calendar" class="w-4 h-4 mr-2"/>
                            {{ count($selectedMonths) > 0 ? count($selectedMonths) . ' Bulan Terpilih' : 'Semua Bulan' }}
                            <x-icon name="o-chevron-down" class="w-3 h-3 ml-2 opacity-50"/>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow-xl bg-base-100 rounded-box w-52 z-[100] border border-base-200 mt-2 max-h-64 overflow-y-auto block">
                            @foreach($monthOptions as $m)
                                <li>
                                    <label class="label cursor-pointer justify-start gap-3 px-2 py-1.5 rounded-lg hover:bg-base-200">
                                        <input type="checkbox" wire:model.live="selectedMonths" value="{{ $m['id'] }}" class="checkbox checkbox-sm checkbox-primary rounded" />
                                        <span class="label-text font-medium">{{ $m['name'] }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Divider -->
                    <div class="h-6 w-px bg-base-300 hidden sm:block"></div>

                    <!-- Type Reg/Fest -->
                    <div class="join">
                        @foreach([['id'=>'ALL','label'=>'Semua'],['id'=>'REG','label'=>'Regular'],['id'=>'FEST','label'=>'Festival']] as $t)
                            <button
                                wire:click="$set('selectedRegFest', '{{ $t['id'] }}')"
                                class="join-item btn btn-sm {{ $selectedRegFest === $t['id'] ? 'btn-neutral' : 'btn-ghost border border-base-300' }}"
                            >
                                {{ $t['label'] }}
                            </button>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        <!-- Premium KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Card 1: Total Sales (Purple Gradient) -->
            <div class="p-5 rounded-2xl shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #818CF8, #6366F1);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Total Sales</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">Rp {{ number_format($this->kpiData['total_actual_ty'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-icon name="o-banknotes" class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-5 flex items-center gap-3 text-[11px] font-bold flex-wrap relative z-10">
                    <!-- VS LY -->
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md {{ $this->kpiData['growth_pct'] >= 0 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-icon name="{{ $this->kpiData['growth_pct'] >= 0 ? 'o-arrow-trending-up' : 'o-arrow-trending-down' }}" class="w-3.5 h-3.5" />
                        <span>{{ $this->kpiData['growth_pct'] > 0 ? '+' : '' }}{{ $this->kpiData['growth_pct'] }}%</span>
                        <span class="opacity-70 ml-0.5 text-slate-500">vs LY</span>
                    </div>
                    <!-- VS Target -->
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md {{ $this->kpiData['achievement_pct'] >= 100 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-icon name="{{ $this->kpiData['achievement_pct'] >= 100 ? 'o-check-circle' : 'o-exclamation-circle' }}" class="w-3.5 h-3.5" />
                        <span>{{ $this->kpiData['achievement_pct'] }}%</span>
                        <span class="opacity-70 ml-0.5 text-slate-500">vs Target</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Target (Cyan Gradient) -->
            <div class="p-5 rounded-2xl shadow-lg shadow-cyan-500/30 hover:shadow-xl hover:shadow-cyan-500/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #5EEAD4, #20C997);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Total Target</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">Rp {{ number_format($this->kpiData['total_target'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-icon name="o-flag" class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-5 flex items-center gap-3 text-[11px] font-bold flex-wrap relative z-10">
                    <!-- VS Sales -->
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md {{ $this->kpiData['achievement_pct'] >= 100 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-icon name="o-chart-pie" class="w-3.5 h-3.5" />
                        <span>{{ $this->kpiData['achievement_pct'] }}%</span>
                        <span class="opacity-70 ml-0.5 text-slate-500">vs Sales</span>
                    </div>
                    <!-- Gap vs Sales -->
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md {{ $this->kpiData['gap_vs_target'] >= 0 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-icon name="{{ $this->kpiData['gap_vs_target'] >= 0 ? 'o-check-circle' : 'o-arrow-down' }}" class="w-3.5 h-3.5" />
                        <span>Gap: {{ $this->kpiData['gap_vs_target'] > 0 ? '+' : '' }}{{ number_format($this->kpiData['gap_vs_target'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total LY (Peach/Rose Gradient) -->
            <div class="p-5 rounded-2xl shadow-lg shadow-rose-400/30 hover:shadow-xl hover:shadow-rose-400/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #FDA4AF, #FB7185);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Total LY</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">Rp {{ number_format($this->kpiData['total_ly'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-icon name="o-clock" class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-5 flex items-center gap-3 text-[11px] font-bold flex-wrap relative z-10">
                    <!-- Growth -->
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md {{ $this->kpiData['growth_pct'] >= 0 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-icon name="{{ $this->kpiData['growth_pct'] >= 0 ? 'o-arrow-trending-up' : 'o-arrow-trending-down' }}" class="w-3.5 h-3.5" />
                        <span>{{ $this->kpiData['growth_pct'] > 0 ? '+' : '' }}{{ $this->kpiData['growth_pct'] }}%</span>
                        <span class="opacity-70 ml-0.5 text-slate-500">Growth</span>
                    </div>
                    <!-- Gap vs Sales -->
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md {{ $this->kpiData['gap_vs_ly'] >= 0 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-icon name="{{ $this->kpiData['gap_vs_ly'] >= 0 ? 'o-arrow-trending-up' : 'o-arrow-trending-down' }}" class="w-3.5 h-3.5" />
                        <span>Gap: {{ $this->kpiData['gap_vs_ly'] > 0 ? '+' : '' }}{{ number_format($this->kpiData['gap_vs_ly'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Sales Average (Fuchsia/Purple Gradient) -->
            <div class="p-5 rounded-2xl shadow-lg shadow-purple-500/30 hover:shadow-xl hover:shadow-purple-500/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #D8B4FE, #C084FC);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Sales Average</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">Rp {{ number_format($this->kpiData['avg_sales_yoy'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-icon name="o-chart-bar" class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-5 flex items-center gap-2 text-[11px] font-bold relative z-10">
                    <div class="flex items-center gap-1 px-2 py-1 rounded-md {{ $this->kpiData['avg_sales_growth'] >= 0 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-icon name="{{ $this->kpiData['avg_sales_growth'] >= 0 ? 'o-arrow-trending-up' : 'o-arrow-trending-down' }}" class="w-3.5 h-3.5" />
                        <span>{{ $this->kpiData['avg_sales_growth'] > 0 ? '+' : '' }}{{ $this->kpiData['avg_sales_growth'] }}%</span>
                        <span class="opacity-70 ml-0.5 text-slate-500">YoY Trend</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Row 1: Charts (Contribution & Comparison & Combo) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contribution Donut -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm">
            <h3 class="text-[0.75rem] font-bold mb-1 uppercase tracking-wide opacity-70">Sales Contribution</h3>
            <div id="chartContribution" class="w-full" style="height: 250px;" wire:ignore></div>
        </div>

        <!-- Comparison HBar -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-5 shadow-sm relative">
            <div class="flex justify-between items-center mb-0">
                <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80">Sales vs Target</h3>
                <!-- Custom HTML Legend -->
                <div class="flex items-center gap-3 text-[0.6875rem] font-semibold text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-cyan-400"></span> Achieved</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-400"></span> Not Achieved</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span> Target</div>
                </div>
            </div>
            <div id="chartRegionHBar" class="w-full h-72 -mt-2" wire:ignore></div>
        </div>

        <!-- Combo YoY -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-5 shadow-sm relative">
            <div class="flex justify-between items-center mb-0">
                <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80">Current vs Last Year</h3>
                <!-- Custom HTML Legend -->
                <div class="flex items-center gap-3 text-[0.6875rem] font-semibold text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-cyan-400"></span> Growth (+)</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-400"></span> Growth (-)</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span> Last Year</div>
                </div>
            </div>
            <div id="chartCombo" class="w-full h-72 -mt-2" wire:ignore></div>
        </div>
    </div>

    <!-- Row 2: Trend Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend Area -->
        <div class="col-span-1 lg:col-span-2 bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-5 shadow-sm">
            <h3 class="text-[0.75rem] font-bold mb-2 uppercase tracking-wide opacity-80">Trend Wilayah by Month</h3>
            <div id="chartSalesTrend" class="w-full h-80" wire:ignore></div>
        </div>



        <!-- Sales Map Area -->
        <div class="col-span-1 bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-5 shadow-sm flex flex-col">
            <h3 class="text-[0.75rem] font-bold mb-2 uppercase tracking-wide opacity-80">Sebaran Sales (Cabang)</h3>
            <div id="salesMap" class="w-full flex-1 min-h-[300px] z-0 rounded-xl relative overflow-hidden" style="z-index: 1;" wire:ignore></div>
        </div>
    </div>

    <!-- Monthly Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Bar Chart 1 -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-5 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80">Sales vs Target per Month</h3>
                <div class="flex gap-4 text-[0.6875rem] font-semibold text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color: #20C997;"></span> Sales</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span> Target</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span> Ach %</div>
                </div>
            </div>
            <div id="chartMonthlyBar" class="w-full h-80 -mt-2" wire:ignore></div>
        </div>

        <!-- Monthly Bar Chart 2 -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-5 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80">CY vs LY per Month</h3>
                <div class="flex gap-4 text-[0.6875rem] font-semibold text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color: #20C997;"></span> CY</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span> LY</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></span> Growth %</div>
                </div>
            </div>
            <div id="chartMonthlyCyLy" class="w-full h-80 -mt-2" wire:ignore></div>
        </div>
    </div>

    <!-- AO Trend Row -->
    <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm mt-6 mb-6">
        <div class="flex justify-between items-start mb-2">
            <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80">Trend Active Outlet (AO)</h3>
            <div class="flex gap-4 text-[0.6875rem] font-semibold text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color: #20C997;"></span> AO (Current Year)</div>
                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background-color: #cbd5e1;"></span> AO (Last Year)</div>
            </div>
        </div>
        <div id="chartAoTrend" class="w-full h-64 -mt-2" wire:ignore></div>
    </div>

    <!-- Performance Tables Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        
        <!-- Top Ach (%) -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col">
            <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80 mb-3">Top Ach (%)</h3>
            <div class="overflow-x-auto rounded-lg">
                <table class="table table-xs w-full">
                    <thead class="bg-cyan-400 text-white">
                        <tr>
                            <th class="uppercase">{{ strtolower($breakdownBy) }}</th>
                            <th class="text-right">Sales</th>
                            <th class="text-right">Target</th>
                            <th class="text-right">vs Tgt %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topAch as $row)
                        <tr class="border-b border-base-100 hover:bg-base-200/50">
                            <td class="font-semibold text-slate-600 truncate max-w-[100px]">{{ $row['label'] }}</td>
                            <td class="text-right">{{ number_format($row['sales'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['target'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold {{ $row['ach_pct'] >= 100 ? 'text-cyan-400' : 'text-rose-400' }}">
                                {{ $row['ach_pct'] }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Highest Gap vs Target -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col">
            <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80 mb-3">Highest Gap vs Target</h3>
            <div class="overflow-x-auto rounded-lg">
                <table class="table table-xs w-full">
                    <thead class="bg-rose-400 text-white">
                        <tr>
                            <th class="uppercase">{{ strtolower($breakdownBy) }}</th>
                            <th class="text-right">Sales</th>
                            <th class="text-right">Target</th>
                            <th class="text-right">Gap</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gapTarget as $row)
                        <tr class="border-b border-white hover:opacity-90">
                            <td class="font-semibold text-slate-600 truncate max-w-[100px]">{{ $row['label'] }}</td>
                            <td class="text-right">{{ number_format($row['sales'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['target'], 0, ',', '.') }}</td>
                            <td class="text-right text-white font-semibold {{ $row['gap_target'] >= 0 ? 'bg-cyan-400' : 'bg-rose-400' }}">
                                {{ number_format($row['gap_target'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Growth (%) -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col">
            <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80 mb-3">Top Growth (%)</h3>
            <div class="overflow-x-auto rounded-lg">
                <table class="table table-xs w-full">
                    <thead class="bg-cyan-400 text-white">
                        <tr>
                            <th class="uppercase">{{ strtolower($breakdownBy) }}</th>
                            <th class="text-right">Sales</th>
                            <th class="text-right">Last Year</th>
                            <th class="text-right">vs LY%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topGrowth as $row)
                        <tr class="border-b border-base-100 hover:bg-base-200/50">
                            <td class="font-semibold text-slate-600 truncate max-w-[100px]">{{ $row['label'] }}</td>
                            <td class="text-right">{{ number_format($row['sales'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['last_year'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold {{ $row['growth_pct'] >= 0 ? 'text-cyan-400' : 'text-rose-400' }}">
                                {{ $row['growth_pct'] > 0 ? '+' : '' }}{{ $row['growth_pct'] }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Highest Gap vs LY -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col">
            <h3 class="text-[0.75rem] font-bold uppercase tracking-wide opacity-80 mb-3">Highest Gap vs LY</h3>
            <div class="overflow-x-auto rounded-lg">
                <table class="table table-xs w-full">
                    <thead class="bg-rose-400 text-white">
                        <tr>
                            <th class="uppercase">{{ strtolower($breakdownBy) }}</th>
                            <th class="text-right">Sales</th>
                            <th class="text-right">Last Year</th>
                            <th class="text-right">Gap LY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gapGrowth as $row)
                        <tr class="border-b border-white hover:opacity-90">
                            <td class="font-semibold text-slate-600 truncate max-w-[100px]">{{ $row['label'] }}</td>
                            <td class="text-right">{{ number_format($row['sales'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row['last_year'], 0, ',', '.') }}</td>
                            <td class="text-right text-white font-semibold {{ $row['gap_ly'] >= 0 ? 'bg-cyan-400' : 'bg-rose-400' }}">
                                {{ number_format($row['gap_ly'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div> <!-- CLOSING THE GRID CONTAINER -->

    <!-- Hierarchical Pivot Table -->
    <div class="mt-6 bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-[0.75rem] uppercase font-bold text-slate-700 tracking-wide opacity-80">Matrix View</h2>
        </div>
        <div class="overflow-auto rounded-lg max-h-[600px] border border-base-200">
            <table class="table table-xs w-full text-center whitespace-nowrap">
                <thead class="bg-matrix-header text-white sticky top-0 z-20 shadow-sm">
                    <tr>
                        <th colspan="2" class="border-r border-matrix-header bg-matrix-header">Month</th>
                        @foreach(range(1, $pivotMaxMonth) as $m)
                            <th colspan="5" class="border-r border-matrix-header">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</th>
                        @endforeach
                        <th colspan="5" class="border-l-4 border-matrix-header bg-matrix-header">YTD TOTAL</th>
                    </tr>
                    <tr>
                        <th class="border-r border-matrix-header bg-matrix-header">{{ $pivotLevel1Name }}</th>
                        <th class="border-r border-matrix-header bg-matrix-header">{{ $pivotLevel2Name }}</th>
                        @foreach(range(1, $pivotMaxMonth) as $m)
                            <th class="bg-matrix-header">Sales</th>
                            <th class="bg-matrix-header">Target</th>
                            <th class="bg-matrix-header">vs Tgt %</th>
                            <th class="bg-matrix-header">Last Year</th>
                            <th class="border-r border-matrix-header bg-matrix-header">vs LY%</th>
                        @endforeach
                        <th class="border-l-4 border-matrix-header bg-matrix-header">Sales</th>
                        <th class="bg-matrix-header">Target</th>
                        <th class="bg-matrix-header">vs Tgt %</th>
                        <th class="bg-matrix-header">Last Year</th>
                        <th class="bg-matrix-header">vs LY%</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600">
                    @foreach($pivotData as $l1 => $l1Data)
                        @foreach($l1Data['areas'] as $l2 => $l2Data)
                            <tr class="hover:bg-slate-50">
                                @if($loop->first)
                                    <td class="font-bold border-r border-slate-200 align-top uppercase" rowspan="{{ count($l1Data['areas']) + 1 }}">{{ $l1 }}</td>
                                @endif
                                <td class="border-r border-slate-200 uppercase">{{ $l2 }}</td>
                                @foreach(range(1, $pivotMaxMonth) as $m)
                                    <td class="text-right">{{ number_format($l2Data[$m]['sales'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($l2Data[$m]['target'], 0, ',', '.') }}</td>
                                    <td class="text-right font-bold text-white {{ $l2Data[$m]['vs_tgt'] >= 100 ? 'bg-cyan-400' : ($l2Data[$m]['vs_tgt'] >= 80 ? 'bg-amber-400' : 'bg-rose-400') }}">
                                        {{ $l2Data[$m]['vs_tgt'] }}%
                                    </td>
                                    <td class="text-right">{{ number_format($l2Data[$m]['ly'], 0, ',', '.') }}</td>
                                    <td class="border-r border-slate-200 text-right font-bold text-white {{ $l2Data[$m]['vs_ly'] >= 0 ? 'bg-cyan-400' : 'bg-rose-400' }}">
                                        {{ $l2Data[$m]['vs_ly'] }}%
                                    </td>
                                @endforeach
                                <td class="border-l-4 border-slate-300 text-right bg-amber-50 font-semibold">{{ number_format($l2Data['YTD']['sales'], 0, ',', '.') }}</td>
                                <td class="text-right bg-amber-50 font-semibold">{{ number_format($l2Data['YTD']['target'], 0, ',', '.') }}</td>
                                <td class="text-right font-bold text-white {{ $l2Data['YTD']['vs_tgt'] >= 100 ? 'bg-cyan-500' : ($l2Data['YTD']['vs_tgt'] >= 80 ? 'bg-amber-400' : 'bg-rose-400') }}">
                                    {{ $l2Data['YTD']['vs_tgt'] }}%
                                </td>
                                <td class="text-right bg-amber-50 font-semibold">{{ number_format($l2Data['YTD']['ly'], 0, ',', '.') }}</td>
                                <td class="text-right font-bold text-white {{ $l2Data['YTD']['vs_ly'] >= 0 ? 'bg-cyan-500' : 'bg-rose-400' }}">
                                    {{ $l2Data['YTD']['vs_ly'] }}%
                                </td>
                            </tr>
                        @endforeach
                        <!-- Level 1 Total -->
                        <tr class="bg-slate-100 font-bold hover:bg-slate-200">
                            <td class="border-r border-slate-200 text-left">Total</td>
                            @foreach(range(1, $pivotMaxMonth) as $m)
                                <td class="text-right">{{ number_format($l1Data['total'][$m]['sales'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($l1Data['total'][$m]['target'], 0, ',', '.') }}</td>
                                <td class="text-right font-bold text-white {{ $l1Data['total'][$m]['vs_tgt'] >= 100 ? 'bg-cyan-400' : ($l1Data['total'][$m]['vs_tgt'] >= 80 ? 'bg-amber-400' : 'bg-rose-400') }}">
                                    {{ $l1Data['total'][$m]['vs_tgt'] }}%
                                </td>
                                <td class="text-right">{{ number_format($l1Data['total'][$m]['ly'], 0, ',', '.') }}</td>
                                <td class="border-r border-slate-200 text-right font-bold text-white {{ $l1Data['total'][$m]['vs_ly'] >= 0 ? 'bg-cyan-400' : 'bg-rose-400' }}">
                                    {{ $l1Data['total'][$m]['vs_ly'] }}%
                                </td>
                            @endforeach
                            <td class="border-l-4 border-slate-300 text-right bg-amber-100 font-bold">{{ number_format($l1Data['total']['YTD']['sales'], 0, ',', '.') }}</td>
                            <td class="text-right bg-amber-100 font-bold">{{ number_format($l1Data['total']['YTD']['target'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-white {{ $l1Data['total']['YTD']['vs_tgt'] >= 100 ? 'bg-cyan-500' : ($l1Data['total']['YTD']['vs_tgt'] >= 80 ? 'bg-amber-400' : 'bg-rose-400') }}">
                                {{ $l1Data['total']['YTD']['vs_tgt'] }}%
                            </td>
                            <td class="text-right bg-amber-100 font-bold">{{ number_format($l1Data['total']['YTD']['ly'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-white {{ $l1Data['total']['YTD']['vs_ly'] >= 0 ? 'bg-cyan-500' : 'bg-rose-400' }}">
                                {{ $l1Data['total']['YTD']['vs_ly'] }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="sticky bottom-0 z-20">
                    <tr style="background-color: #0B1120 !important; color: #ffffff !important; font-weight: bold;">
                        <td colspan="2" class="border-r border-slate-600 text-left uppercase pl-4" style="background-color: #20C997 !important; color: #ffffff !important;">Grand Total</td>
                        @foreach(range(1, $pivotMaxMonth) as $m)
                            <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">{{ number_format($pivotGrandTotal[$m]['sales'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">{{ number_format($pivotGrandTotal[$m]['target'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">
                                {{ $pivotGrandTotal[$m]['vs_tgt'] }}%
                            </td>
                            <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">{{ number_format($pivotGrandTotal[$m]['ly'], 0, ',', '.') }}</td>
                            <td class="border-r border-slate-600 text-right font-bold text-white" style="background-color: #20C997 !important;">
                                {{ $pivotGrandTotal[$m]['vs_ly'] > 0 ? '+' : '' }}{{ $pivotGrandTotal[$m]['vs_ly'] }}%
                            </td>
                        @endforeach
                        <!-- YTD Grand Total -->
                        <td class="border-l-4 border-slate-600 text-right font-bold text-white" style="background-color: #20C997 !important;">{{ number_format($pivotGrandTotal['YTD']['sales'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">{{ number_format($pivotGrandTotal['YTD']['target'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">
                            {{ $pivotGrandTotal['YTD']['vs_tgt'] }}%
                        </td>
                        <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">{{ number_format($pivotGrandTotal['YTD']['ly'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold text-white" style="background-color: #20C997 !important;">
                            {{ $pivotGrandTotal['YTD']['vs_ly'] > 0 ? '+' : '' }}{{ $pivotGrandTotal['YTD']['vs_ly'] }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Scripts for ApexCharts -->
    @push('scripts')
    <script>
        const registerDashboardV2 = () => {
            Alpine.data('dashboardV2', () => ({
                charts: {
                    contrib: null,
                    region: null,
                    combo: null,
                    trend: null,
                    monthly: null,
                    monthlyCyLy: null
                },
                salesMapInstance: null,
                salesMapLayers: [],

                init() {
                    // Init charts after DOM ready
                    setTimeout(() => { this.initCharts(); }, 150);

                    // When Livewire updates data (filter/tab change), destroy old & re-init fresh
                    // This is the ONLY reliable pattern: Livewire morphs DOM, old ApexCharts refs break
                    Livewire.on('charts-updated', () => {
                        // Small delay to let Livewire finish DOM morphing first
                        setTimeout(() => {
                            this.destroyCharts();
                            this.initCharts();
                        }, 50);
                    });

                    // Re-init when user navigates back to this page (wire:navigate SPA)
                    document.addEventListener('livewire:navigated', () => {
                        setTimeout(() => {
                            this.destroyCharts();
                            this.initCharts();
                        }, 150);
                    });
                },

                destroyCharts() {
                    Object.values(this.charts).forEach(chart => {
                        try { chart.destroy(); } catch(e) {}
                    });
                    this.charts = {};
                },

                getThemeConfig() {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'neon-dark';
                    return {
                        mode: isDark ? 'dark' : 'light',
                        textColor: isDark ? '#a6adbb' : '#4f5d73',
                        gridColor: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)'
                    };
                },

                initCharts() {
                    const theme = this.getThemeConfig();
                    const commonOptions = {
                        chart: { toolbar: { show: false }, background: 'transparent' },
                        theme: { mode: theme.mode },
                        grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
                        dataLabels: { enabled: false },
                        xaxis: { labels: { style: { colors: theme.textColor, fontSize: '0.75rem' } } },
                        yaxis: { labels: { style: { colors: theme.textColor, fontSize: '0.75rem' }, formatter: (val) => val > 1e6 ? (val/1e6).toFixed(0)+'M' : val } }
                    };

                    // Theme Colors matching the requested screenshot (Aqua, Indigo, Mint, Light Purple)
                    const themeColors = ['#6366F1', '#20C997', '#FB7185', '#C084FC', '#FBBF24'];

                    // 1. Contribution — compact donut
                    const dataContrib = JSON.parse(@this.chartContribution);
                    const contribColors = [
                        '#6366f1','#14B8A6','#20C997','#f59e0b','#FB7185',
                        '#8b5cf6','#0ea5e9','#22c55e','#ec4899','#14b8a6',
                        '#a855f7','#3b82f6','#84cc16','#ef4444','#f97316'
                    ];

                    // Format angka jadi singkatan: 58.800.000.000 → 58.8B
                    const fmtSales = (val) => {
                        if (!val || val === 0) return '0';
                        if (val >= 1e12) return (val / 1e12).toFixed(1) + 'T';
                        if (val >= 1e9)  return (val / 1e9).toFixed(1) + 'B';
                        if (val >= 1e6)  return (val / 1e6).toFixed(1) + 'M';
                        if (val >= 1e3)  return (val / 1e3).toFixed(1) + 'K';
                        return val;
                    };

                    const contribTotalSales  = dataContrib.total_sales  || 0;
                    const contribSalesValues = dataContrib.sales_values || [];

                    this.charts.contrib = new ApexCharts(document.querySelector("#chartContribution"), {
                        series: dataContrib.series || [],
                        labels: dataContrib.labels || [],
                        colors: contribColors,
                        chart: {
                            type: 'donut',
                            height: 250,
                            background: 'transparent',
                            toolbar: { show: false },
                            animations: { enabled: true, speed: 400 },
                            sparkline: { enabled: false }
                        },
                        stroke: { show: false },
                        dataLabels: { enabled: false },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '0.75rem',
                                            fontWeight: 700,
                                            color: theme.textColor,
                                            offsetY: -6
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '1rem',
                                            fontWeight: 800,
                                            color: theme.textColor,
                                            offsetY: 4,
                                            formatter: function(val) {
                                                return fmtSales(val);
                                            }
                                        },
                                        total: {
                                            show: true,
                                            label: 'Total Sales',
                                            fontSize: '0.75rem',
                                            fontWeight: 600,
                                            color: theme.textColor,
                                            // Saat tidak ada hover: tampilkan total sales
                                            formatter: function(w) {
                                                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                return fmtSales(total);
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        legend: {
                            show: true,
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '0.6875rem',
                            fontWeight: 500,
                            itemMargin: { horizontal: 4, vertical: 2 },
                            markers: { width: 8, height: 8, radius: 4 },
                            labels: { colors: theme.textColor }
                        },
                        // Tooltip: tampilkan nama, nilai sales, dan persentase
                        tooltip: {
                            custom: function({ series, seriesIndex, w }) {
                                const label    = w.globals.labels[seriesIndex];
                                const salesVal = series[seriesIndex] || 0;
                                const total    = w.globals.seriesTotals.reduce((a, b) => a + b, 0) || 1;
                                const pct      = ((salesVal / total) * 100).toFixed(1);
                                
                                const bg = theme.mode === 'dark' ? '#1e293b' : '#ffffff';
                                const border = theme.mode === 'dark' ? '#334155' : '#e2e8f0';
                                
                                return `<div style="padding:0.5rem 0.75rem;font-size:0.75rem;line-height:1.6;background:${bg};color:${theme.textColor};border:1px solid ${border};border-radius:0.375rem;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
                                    <strong>${label}</strong><br>
                                    ${fmtSales(salesVal)} &nbsp;<span style="opacity:.6">(${pct}%)</span>
                                </div>`;
                            }
                        },
                        theme: { mode: theme.mode }
                    });
                    this.charts.contrib.render();

                    // 2. Comparison HBar
                    const dataHBar = JSON.parse(@this.chartRegionHBar);
                    this.charts.hbar = new ApexCharts(document.querySelector("#chartRegionHBar"), {
                        ...commonOptions,
                        series: [
                            { name: 'Sales', data: dataHBar.actuals || [] },
                            { name: 'Target', data: dataHBar.targets || [] }
                        ],
                        colors: [
                            function({ value, seriesIndex, dataPointIndex, w }) {
                                if (seriesIndex === 0) {
                                    const target = w.config.series[1].data[dataPointIndex] || 0;
                                    return value >= target ? '#20C997' : '#FB7185'; // Emerald if Achieved, Rose if Not
                                }
                                return theme.mode === 'dark' ? '#475569' : '#cbd5e1'; // Slate for Target
                            }
                        ],
                        chart: { type: 'bar', height: '100%', background: 'transparent', toolbar:{show:false} },
                        plotOptions: { bar: { horizontal: true, borderRadius: 0, dataLabels: { position: 'top' } } },
                        legend: { show: false },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val, opts) {
                                if (opts.seriesIndex === 0) {
                                    const targetVal = opts.w.config.series[1].data[opts.dataPointIndex] || 0;
                                    if (targetVal > 0) return ((val / targetVal) * 100).toFixed(1) + '%';
                                }
                                return '';
                            },
                            style: { colors: [theme.textColor], fontSize: '0.75rem', fontWeight: 600 },
                            offsetX: 25,
                            dropShadow: { enabled: false }
                        },
                        xaxis: { 
                            categories: dataHBar.labels || [], 
                            labels: { 
                                style: { colors: theme.textColor, fontSize: '0.75rem' },
                                formatter: (val) => val > 1e6 ? (val/1e6).toFixed(0)+'M' : val
                            } 
                        }
                    });
                    this.charts.hbar.render();

                    // 3. Current vs Last Year (HBar)
                    const dataCombo = JSON.parse(@this.chartCombo);
                    this.charts.combo = new ApexCharts(document.querySelector("#chartCombo"), {
                        ...commonOptions,
                        series: [
                            { name: 'Current', data: dataCombo.current || [] },
                            { name: 'Last Year', data: dataCombo.last_year || [] }
                        ],
                        colors: [
                            function({ value, seriesIndex, dataPointIndex, w }) {
                                if (seriesIndex === 0) {
                                    const ly = w.config.series[1].data[dataPointIndex] || 0;
                                    return value >= ly ? '#20C997' : '#FB7185'; // Emerald if Growth (+), Rose if (-)
                                }
                                return theme.mode === 'dark' ? '#475569' : '#cbd5e1'; // Slate for Last Year
                            }
                        ],
                        chart: { type: 'bar', height: '100%', background: 'transparent', toolbar:{show:false} },
                        plotOptions: { bar: { horizontal: true, borderRadius: 0, dataLabels: { position: 'top' } } },
                        legend: { show: false },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val, opts) {
                                if (opts.seriesIndex === 0) {
                                    const targetVal = opts.w.config.series[1].data[opts.dataPointIndex] || 0;
                                    if (targetVal > 0) {
                                        const growth = ((val - targetVal) / targetVal) * 100;
                                        return (growth > 0 ? '+' : '') + growth.toFixed(1) + '%';
                                    }
                                }
                                return '';
                            },
                            style: { colors: [theme.textColor], fontSize: '0.75rem', fontWeight: 600 },
                            offsetX: 32, // Increase offset to accommodate + or - signs
                            dropShadow: { enabled: false }
                        },
                        xaxis: { 
                            categories: dataCombo.labels || [], 
                            labels: { 
                                style: { colors: theme.textColor, fontSize: '0.75rem' },
                                formatter: (val) => val > 1e6 ? (val/1e6).toFixed(0)+'M' : val
                            } 
                        }
                    });
                    this.charts.combo.render();

                    // 4. Trend Wilayah by Month
                    const dataTrend = JSON.parse(@this.chartSalesTrend);
                    this.charts.trend = new ApexCharts(document.querySelector("#chartSalesTrend"), {
                        ...commonOptions,
                        series: dataTrend.series || [],
                        colors: themeColors, // Array of beautiful colors defined earlier
                        chart: { type: 'area', height: '100%', background: 'transparent', toolbar:{show:false} },
                        stroke: { curve: 'smooth', width: 2 },
                        legend: { position: 'top', horizontalAlign: 'right', fontSize: '0.6875rem', labels: { colors: theme.textColor } },
                        markers: { size: 4, strokeWidth: 2, hover: { size: 6 } },
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
                        xaxis: { categories: dataTrend.labels || [], labels: { style: { colors: theme.textColor, fontSize: '0.75rem' } } },
                        yaxis: { labels: { style: { colors: theme.textColor, fontSize: '0.75rem' }, formatter: (val) => val > 1e6 ? (val/1e6).toFixed(0)+'M' : val } },
                        tooltip: {
                            y: { formatter: function(val) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(val); } }
                        }
                    });
                    this.charts.trend.render();

                    // 5. Monthly Bar (Sales vs Target)
                    const dataMonthly = JSON.parse(@this.chartMonthlyBar);
                    const actualsMonth = dataMonthly.actuals || [];
                    const targetsMonth = dataMonthly.targets || [];
                    
                    const achPercents = actualsMonth.map((act, i) => {
                        const tgt = targetsMonth[i] || 0;
                        if (tgt === 0) return 0;
                        return Number(((act / tgt) * 100).toFixed(1));
                    });

                    const discreteMarkers = [];
                    achPercents.forEach((ach, i) => {
                        discreteMarkers.push({
                            seriesIndex: 2,
                            dataPointIndex: i,
                            fillColor: ach >= 100 ? '#20C997' : '#FB7185', // Green if >= 100%, else Red
                            strokeColor: theme.mode === 'dark' ? '#1e293b' : '#ffffff',
                            size: 5
                        });
                    });

                    this.charts.monthly = new ApexCharts(document.querySelector("#chartMonthlyBar"), {
                        ...commonOptions,
                        series: [
                            { name: 'Sales', type: 'column', data: actualsMonth },
                            { name: 'Target', type: 'column', data: targetsMonth },
                            { name: 'Ach %', type: 'line', data: achPercents }
                        ],
                        colors: ['#20C997', '#cbd5e1', '#94a3b8'], // Aqua for Sales, Slate for Target, Grey for Line
                        chart: { type: 'line', height: '100%', background: 'transparent', toolbar:{show:false}, stacked: false },
                        legend: { show: false },
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                        stroke: { width: [0, 0, 3], curve: 'smooth' }, // 0 for bars, 3 for line
                        markers: {
                            size: [0, 0, 0], // Base size 0, overridden by discrete
                            discrete: discreteMarkers,
                            hover: { size: 7 }
                        },
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [2],
                            formatter: function (val) { return val + '%'; },
                            style: { colors: [theme.textColor], fontSize: '0.75rem', fontWeight: 600 },
                            offsetY: -8,
                            background: { enabled: true, foreColor: theme.mode === 'dark' ? '#000' : '#fff', borderRadius: 4, padding: 4, opacity: 0.8 }
                        },
                        xaxis: { categories: dataMonthly.labels || [], labels: { style: { colors: theme.textColor, fontSize: '0.75rem' } } },
                        yaxis: [
                            { seriesName: 'Sales', labels: { style: { colors: theme.textColor, fontSize: '0.75rem' }, formatter: (val) => val > 1e6 ? (val/1e6).toFixed(0)+'M' : val } },
                            { seriesName: 'Sales', show: false }, // Share axis with Sales
                            { seriesName: 'Ach %', opposite: true, labels: { style: { colors: theme.textColor, fontSize: '0.75rem' }, formatter: (val) => val.toFixed(0) + '%' } }
                        ],
                        tooltip: {
                            y: {
                                formatter: function(val, opts) {
                                    if (opts.seriesIndex === 2) return val + '%';
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    });
                    this.charts.monthly.render();

                    // 6. Monthly CY vs LY
                    const dataMonthlyCyLy = JSON.parse(@this.chartMonthlyCyLy);
                    const currentMonth = dataMonthlyCyLy.current || [];
                    const lastYearMonth = dataMonthlyCyLy.last_year || [];
                    
                    const growthPercents = currentMonth.map((cy, i) => {
                        const ly = lastYearMonth[i] || 0;
                        if (ly === 0) return 0;
                        return Number((((cy - ly) / ly) * 100).toFixed(1));
                    });

                    const growthMarkers = [];
                    growthPercents.forEach((growth, i) => {
                        growthMarkers.push({
                            seriesIndex: 2,
                            dataPointIndex: i,
                            fillColor: growth >= 0 ? '#20C997' : '#FB7185', // Green if >= 0%, else Red
                            strokeColor: theme.mode === 'dark' ? '#1e293b' : '#ffffff',
                            size: 5
                        });
                    });

                    this.charts.monthlyCyLy = new ApexCharts(document.querySelector("#chartMonthlyCyLy"), {
                        ...commonOptions,
                        series: [
                            { name: 'Current Year', type: 'column', data: currentMonth },
                            { name: 'Last Year', type: 'column', data: lastYearMonth },
                            { name: 'Growth %', type: 'line', data: growthPercents }
                        ],
                        colors: ['#20C997', '#94a3b8', '#cbd5e1'], // Aqua for CY, Slate for LY, Lighter Slate for Line
                        chart: { type: 'line', height: '100%', background: 'transparent', toolbar:{show:false}, stacked: false },
                        legend: { show: false },
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                        stroke: { width: [0, 0, 3], curve: 'smooth' },
                        markers: {
                            size: [0, 0, 0],
                            discrete: growthMarkers,
                            hover: { size: 7 }
                        },
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [2],
                            formatter: function (val) { return val > 0 ? '+' + val + '%' : val + '%'; },
                            style: { colors: [theme.textColor], fontSize: '0.75rem', fontWeight: 600 },
                            offsetY: -8,
                            background: { enabled: true, foreColor: theme.mode === 'dark' ? '#000' : '#fff', borderRadius: 4, padding: 4, opacity: 0.8 }
                        },
                        xaxis: { categories: dataMonthlyCyLy.labels || [], labels: { style: { colors: theme.textColor, fontSize: '0.75rem' } } },
                        yaxis: [
                            { seriesName: 'Current Year', labels: { style: { colors: theme.textColor, fontSize: '0.75rem' }, formatter: (val) => val > 1e6 ? (val/1e6).toFixed(0)+'M' : val } },
                            { seriesName: 'Current Year', show: false }, // Share axis with CY
                            { seriesName: 'Growth %', opposite: true, labels: { style: { colors: theme.textColor, fontSize: '0.75rem' }, formatter: (val) => val.toFixed(0) + '%' } }
                        ],
                        tooltip: {
                            y: {
                                formatter: function(val, opts) {
                                    if (opts.seriesIndex === 2) return val + '%';
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    });
                    this.charts.monthlyCyLy.render();

                    // 7. AO Trend Chart
                    const dataAoTrend = JSON.parse(@this.chartAoTrendJson);
                    this.charts.aoTrend = new ApexCharts(document.querySelector("#chartAoTrend"), {
                        chart: { type: 'area', height: 250, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
                        theme: { mode: theme.mode },
                        series: dataAoTrend.series,
                        xaxis: { 
                            categories: dataAoTrend.labels,
                            labels: { style: { colors: theme.textColor, fontSize: '0.75rem' } },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: theme.textColor, fontSize: '0.75rem' },
                                formatter: function (value) {
                                    return new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        },
                        colors: ['#20C997', '#cbd5e1'],
                        stroke: { curve: 'smooth', width: 3 },
                        markers: {
                            size: 4,
                            strokeWidth: 2,
                            hover: { size: 6 }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
                        },
                        dataLabels: { enabled: false },
                        tooltip: {
                            theme: theme.mode,
                            y: { formatter: function (val) { return new Intl.NumberFormat('id-ID').format(val) + " Outlet"; } }
                        },
                        grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
                        legend: { show: false }
                    });
                    this.charts.aoTrend.render();

                    this.initSalesMap();
                },

                initSalesMap() {
                    const mapData = JSON.parse(@this.chartMapData);
                    this.drawMap(mapData);
                },

                updateCharts() {
                    if (!this.$wire) return;

                    const dataContrib = JSON.parse(this.$wire.chartContribution);
                    if(this.charts.contrib) {
                        this.charts.contrib.updateOptions({
                            series: dataContrib.series,
                            labels: dataContrib.labels
                        });
                    }

                    const dataHBar = JSON.parse(this.$wire.chartRegionHBar);
                    if(this.charts.hbar) {
                        this.charts.hbar.updateOptions({
                            series: [
                                { name: 'Sales', data: dataHBar.actuals },
                                { name: 'Target', data: dataHBar.targets }
                            ],
                            xaxis: { categories: dataHBar.labels }
                        });
                    }

                    const dataCombo = JSON.parse(this.$wire.chartCombo);
                    if(this.charts.combo) {
                        this.charts.combo.updateOptions({
                            series: [
                                { name: 'Current', data: dataCombo.current },
                                { name: 'Last Year', data: dataCombo.last_year }
                            ],
                            xaxis: { categories: dataCombo.labels }
                        });
                    }

                    const dataTrend = JSON.parse(this.$wire.chartSalesTrend);
                    if(this.charts.trend) {
                        this.charts.trend.updateOptions({
                            series: dataTrend.series,
                            xaxis: { categories: dataTrend.labels }
                        });
                    }

                    const dataMonthly = JSON.parse(this.$wire.chartMonthlyBar);
                    if(this.charts.monthly) {
                        const actualsM = dataMonthly.actuals || [];
                        const targetsM = dataMonthly.targets || [];
                        const achP = actualsM.map((act, i) => {
                            const tgt = targetsM[i] || 0;
                            return tgt === 0 ? 0 : Number(((act / tgt) * 100).toFixed(1));
                        });
                        
                        const dMarkers = [];
                        achP.forEach((ach, i) => {
                            dMarkers.push({
                                seriesIndex: 2,
                                dataPointIndex: i,
                                fillColor: ach >= 100 ? '#20C997' : '#FB7185',
                                strokeColor: theme.mode === 'dark' ? '#1e293b' : '#ffffff',
                                size: 5
                            });
                        });

                        this.charts.monthly.updateOptions({
                            series: [
                                { name: 'Sales', data: actualsM },
                                { name: 'Target', data: targetsM },
                                { name: 'Ach %', data: achP }
                            ],
                            markers: { discrete: dMarkers },
                            xaxis: { categories: dataMonthly.labels }
                        });
                    }

                    const dataMonthlyCyLy = JSON.parse(this.$wire.chartMonthlyCyLy);
                    if(this.charts.monthlyCyLy) {
                        const cyM = dataMonthlyCyLy.current || [];
                        const lyM = dataMonthlyCyLy.last_year || [];
                        const grP = cyM.map((cy, i) => {
                            const ly = lyM[i] || 0;
                            return ly === 0 ? 0 : Number((((cy - ly) / ly) * 100).toFixed(1));
                        });
                        
                        const gMarkers = [];
                        grP.forEach((growth, i) => {
                            gMarkers.push({
                                seriesIndex: 2,
                                dataPointIndex: i,
                                fillColor: growth >= 0 ? '#20C997' : '#FB7185',
                                strokeColor: theme.mode === 'dark' ? '#1e293b' : '#ffffff',
                                size: 5
                            });
                        });

                        this.charts.monthlyCyLy.updateOptions({
                            series: [
                                { name: 'Current Year', data: cyM },
                                { name: 'Last Year', data: lyM },
                                { name: 'Growth %', data: grP }
                            ],
                            markers: { discrete: gMarkers },
                            xaxis: { categories: dataMonthlyCyLy.labels }
                        });
                    }

                    const mapData = JSON.parse(this.$wire.chartMapData);
                    this.drawMap(mapData);
                },
                
                drawMap(data) {
                    if (!this.salesMapInstance) {
                        this.salesMapInstance = L.map('salesMap').setView([-2.5489, 118.0149], 4); // Center of Indonesia
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 18 }).addTo(this.salesMapInstance);
                    }
                    
                    // Force invalidate size in case container was hidden/resized
                    setTimeout(() => {
                        this.salesMapInstance.invalidateSize();
                    }, 100);

                    // Clear existing layers
                    this.salesMapLayers.forEach(l => this.salesMapInstance.removeLayer(l));
                    this.salesMapLayers = [];
                    
                    if (!data || data.length === 0) return;
                    
                    const maxSales = Math.max(...data.map(d => d.sales));
                    let bounds = [];
                    
                    data.forEach(d => {
                        // Use Math.sqrt so the AREA of the circle (not radius) is proportional to sales
                        const ratio = d.sales / maxSales;
                        const radius = Math.max(4, Math.sqrt(ratio) * 16); 
                        
                        // Color from Red (low) to Green (high)
                        const hue = ratio * 120; // 0 = Red, 120 = Green
                        const bubbleColor = `hsl(${hue}, 90%, 45%)`;

                        const marker = L.circleMarker([d.lat, d.lng], {
                            radius: radius,
                            fillColor: bubbleColor,
                            color: '#ffffff',
                            weight: 1.5,
                            opacity: 0.9,
                            fillOpacity: 0.6
                        });
                        
                        const formatSales = (val) => {
                            if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                            return val;
                        };
                        marker.bindPopup(`<div class="text-center font-sans"><b class="text-sm">${d.name}</b><br><span class="text-xs text-slate-500">Sales: Rp ${formatSales(d.sales)}</span></div>`);
                        
                        marker.addTo(this.salesMapInstance);
                        this.salesMapLayers.push(marker);
                        bounds.push([d.lat, d.lng]);
                    });
                    
                    if (bounds.length > 0) {
                        this.salesMapInstance.fitBounds(bounds, { padding: [20, 20], maxZoom: 10 });
                    }
                }
            }));
        };

        if (window.Alpine) {
            registerDashboardV2();
        } else {
            document.addEventListener('alpine:init', registerDashboardV2);
        }
    </script>
    @endpush
</div>
</div>
