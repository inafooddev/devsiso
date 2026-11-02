<div class="flex flex-col flex-1 w-full h-full min-h-0" x-data="dashboardV2Qty()">
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

    /* Fix DaisyUI table-pin-rows overriding thead th background with white */
    .table-pin-rows thead th,
    .table-pin-rows thead tr th {
        background-color: inherit !important;
        color: inherit !important;
    }

    /* ApexCharts Tooltip Enhancement: ensure tooltips never have transparent ghost background */
    .apexcharts-tooltip {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
</style>

<!-- Global Tab Navigation -->
<x-ui.tab-menu>
    <x-ui.tab-item href="{{ route('dashboard.v2.sellin') }}" :active="false" :navigate="false" icon="o-chart-bar">
        Sell In
    </x-ui.tab-item>
    <x-ui.tab-item href="{{ route('dashboard.v2.sellout') }}" :active="false" :navigate="false" icon="o-shopping-cart">
        Sell Out
    </x-ui.tab-item>
    <x-ui.tab-item href="{{ route('dashboard.v2.qty') }}" :active="true" :navigate="false" icon="o-cube">
        In & Out (Qty)
    </x-ui.tab-item>
</x-ui.tab-menu>

<!-- Scrollable Content Area -->
<div class="flex-1 overflow-y-auto -mx-3 md:-mx-4 lg:-mx-6 -mb-3 md:-mb-4 lg:-mb-6 px-3 md:px-4 lg:px-6 pt-0 pb-10 space-y-6 relative">
    
    <!-- Sticky Header & KPI Wrapper -->
    <div class="sticky top-0 z-50 bg-base-200 -mx-3 md:-mx-4 lg:-mx-6 px-3 md:px-4 lg:px-6 pt-4 pb-4 shadow-md border-b border-base-300 space-y-4">
        
        <!-- Header + Inline Filter Bar -->
        <div class="flex flex-col gap-3 relative z-50">
            <!-- Row 1: Breakdown & Territory Filters -->
            <div class="flex flex-wrap items-center justify-between gap-4 bg-base-100/50 p-2 rounded-xl border border-base-200">
                
                <!-- Kiri: Breakdown Dimension Toggle -->
                <div class="join">
                    @php
                        $level = auth()->user() ? auth()->user()->getAccessLevel() : 'nasional';
                        $tabs = [];
                        if ($level === 'nasional') {
                            $tabs = ['Nasional', 'Region', 'Area', 'Supervisor', 'Cabang'];
                        } elseif ($level === 'region') {
                            $tabs = ['Region', 'Area', 'Supervisor', 'Cabang'];
                        } elseif ($level === 'area') {
                            $tabs = ['Area', 'Supervisor', 'Cabang'];
                        } elseif ($level === 'supervisor') {
                            $tabs = ['Supervisor', 'Cabang'];
                        } else {
                            $tabs = ['Cabang'];
                        }
                    @endphp
                    @foreach($tabs as $opt)
                        <button
                            wire:click="setBreakdownBy('{{ $opt }}')"
                            class="join-item btn btn-sm {{ $breakdownBy === $opt ? 'btn-primary' : 'btn-ghost border border-base-300' }}"
                        >
                            {{ $opt }}
                        </button>
                    @endforeach
                </div>

                <!-- Kanan: Dynamic Filter, Tahun, Bulan -->
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
                            <x-heroicon-o-calendar class="w-4 h-4 mr-2"/>
                            {{ count($selectedMonths) > 0 ? count($selectedMonths) . ' Bulan Terpilih' : 'Semua Bulan' }}
                            <x-heroicon-o-chevron-down class="w-3 h-3 ml-2 opacity-50"/>
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

                </div>
            </div>

            <!-- Row 2: Product Filters (Ribbon Filter) -->
            <div class="flex flex-wrap items-center justify-between gap-3 bg-base-100/40 px-3 py-2 rounded-xl border border-base-200/80">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wide">
                    <x-heroicon-o-funnel class="w-4 h-4 text-primary" />
                    <span>Filter Produk:</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Produk Line Dropdown -->
                    <select wire:model.live="selectedProdukLine" class="select select-xs select-bordered rounded-lg min-w-[120px] font-semibold">
                        <option value="ALL">Semua Line</option>
                        <option value="REG">Regular</option>
                        <option value="FEST">Festival</option>
                    </select>

                    <!-- Kategori Dropdown -->
                    <select wire:model.live="selectedKategory" class="select select-xs select-bordered rounded-lg min-w-[130px] font-semibold">
                        <option value="">Semua Kategori</option>
                        @foreach($listKategories as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>

                    <!-- Top Item Dropdown -->
                    <select wire:model.live="selectedTopitem" class="select select-xs select-bordered rounded-lg min-w-[150px] font-semibold max-w-[200px]">
                        <option value="">Semua Top Item</option>
                        @foreach($listTopitems as $ti)
                            <option value="{{ $ti }}">{{ $ti }}</option>
                        @endforeach
                    </select>

                    <!-- Subbrand Dropdown -->
                    <select wire:model.live="selectedSubbrand" class="select select-xs select-bordered rounded-lg min-w-[160px] font-semibold max-w-[200px]">
                        <option value="">Semua Subbrand</option>
                        @foreach($listSubbrands as $sb)
                            <option value="{{ $sb }}">{{ $sb }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Premium KPI Cards (Identical V2 Gradient Styling) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Card 1: Total Sell In Qty (Indigo Gradient) -->
            <div class="p-4 lg:p-5 rounded-2xl shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #818CF8, #6366F1);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Total Sell In (Qty)</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">
                            {{ number_format($this->kpiData['total_in_ty'] ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold opacity-80">Ctn</span>
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-heroicon-o-arrow-down-tray class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-4 flex items-center gap-2 text-xs font-semibold flex-wrap relative z-10">
                    <!-- YoY Growth In -->
                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md {{ ($this->kpiData['growth_in_pct'] ?? 0) >= 0 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-dynamic-component :component="($this->kpiData['growth_in_pct'] ?? 0) >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down'" class="w-3.5 h-3.5" />
                        <span>{{ ($this->kpiData['growth_in_pct'] ?? 0) > 0 ? '+' : '' }}{{ $this->kpiData['growth_in_pct'] ?? 0 }}%</span>
                        <span class="opacity-70 ml-0.5 text-slate-500">vs LY</span>
                    </div>
                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/90 text-slate-600 shadow-sm">
                        <span>LY: {{ number_format($this->kpiData['total_in_ly'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Sell Out Qty (Cyan Gradient) -->
            <div class="p-4 lg:p-5 rounded-2xl shadow-lg shadow-cyan-500/30 hover:shadow-xl hover:shadow-cyan-500/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #5EEAD4, #20C997);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Total Sell Out (Qty)</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">
                            {{ number_format($this->kpiData['total_out_ty'] ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold opacity-80">Ctn</span>
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-heroicon-o-arrow-up-tray class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-4 flex items-center gap-2 text-xs font-semibold flex-wrap relative z-10">
                    <!-- YoY Growth Out -->
                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md {{ ($this->kpiData['growth_out_pct'] ?? 0) >= 0 ? 'bg-white/90 text-cyan-500' : 'bg-white/90 text-rose-400' }} shadow-sm">
                        <x-dynamic-component :component="($this->kpiData['growth_out_pct'] ?? 0) >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down'" class="w-3.5 h-3.5" />
                        <span>{{ ($this->kpiData['growth_out_pct'] ?? 0) > 0 ? '+' : '' }}{{ $this->kpiData['growth_out_pct'] ?? 0 }}%</span>
                        <span class="opacity-70 ml-0.5 text-slate-500">vs LY</span>
                    </div>
                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/90 text-slate-600 shadow-sm">
                        <span>LY: {{ number_format($this->kpiData['total_out_ly'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Average In / Bulan (Peach/Rose Gradient) -->
            <div class="p-4 lg:p-5 rounded-2xl shadow-lg shadow-rose-400/30 hover:shadow-xl hover:shadow-rose-400/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #FDA4AF, #FB7185);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Avg Sell In / Bulan</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">
                            {{ number_format($this->kpiData['avg_in_monthly'] ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold opacity-80">Ctn</span>
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-heroicon-o-chart-bar class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-4 flex items-center gap-2 text-xs font-semibold relative z-10">
                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/90 text-slate-600 shadow-sm">
                        <x-heroicon-o-clock class="w-3.5 h-3.5 text-rose-500" />
                        <span>Volume Rata-rata Inflow Bulanan</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Average Out / Bulan (Fuchsia/Purple Gradient) -->
            <div class="p-4 lg:p-5 rounded-2xl shadow-lg shadow-purple-500/30 hover:shadow-xl hover:shadow-purple-500/40 transition-all flex flex-col justify-between group relative overflow-hidden" style="background: linear-gradient(135deg, #D8B4FE, #C084FC);">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs font-bold text-white/70 uppercase tracking-wider">Avg Sell Out / Bulan</p>
                        <h3 class="text-xl lg:text-2xl font-black text-white mt-1">
                            {{ number_format($this->kpiData['avg_out_monthly'] ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold opacity-80">Ctn</span>
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <x-heroicon-o-shopping-bag class="w-6 h-6" />
                    </div>
                </div>
                
                <div class="mt-4 flex items-center gap-2 text-xs font-semibold relative z-10">
                    <div class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-white/90 text-slate-600 shadow-sm">
                        <x-heroicon-o-clock class="w-3.5 h-3.5 text-purple-600" />
                        <span>Volume Rata-rata Outflow Bulanan</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Baris 1: 3 Donut Charts (Kontribusi In, Kontribusi Out, In vs Out) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Kontribusi Kategori (Sell In) -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between h-[295px]">
            <div class="mb-1">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">1. Kontribusi {{ !empty($selectedKategory) ? 'Subbrand' : 'Kategori' }} (Sell In)</h3>
                <p class="text-xs text-slate-400 font-normal mt-0.5">Proporsi {{ !empty($selectedKategory) ? 'subbrand' : 'kategori' }} dari total inflow</p>
            </div>
            <div id="chartContribIn" class="w-full flex-1 flex items-center justify-center min-h-0" wire:ignore></div>
            <div class="mt-2 pt-2 border-t border-base-200/80 flex items-center justify-between text-xs px-1 font-semibold">
                <span class="opacity-70 uppercase tracking-wider text-teal-600">Total Sell In</span>
                <span class="font-extrabold text-slate-700 dark:text-slate-200" x-text="fmtQty(contribData.total_in || 0)"></span>
            </div>
        </div>

        <!-- 2. Kontribusi Kategori (Sell Out) -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between h-[295px]">
            <div class="mb-1">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">2. Kontribusi {{ !empty($selectedKategory) ? 'Subbrand' : 'Kategori' }} (Sell Out)</h3>
                <p class="text-xs text-slate-400 font-normal mt-0.5">Proporsi {{ !empty($selectedKategory) ? 'subbrand' : 'kategori' }} dari total outflow</p>
            </div>
            <div id="chartContribOut" class="w-full flex-1 flex items-center justify-center min-h-0" wire:ignore></div>
            <div class="mt-2 pt-2 border-t border-base-200/80 flex items-center justify-between text-xs px-1 font-semibold">
                <span class="opacity-70 uppercase tracking-wider text-indigo-600">Total Sell Out</span>
                <span class="font-extrabold text-slate-700 dark:text-slate-200" x-text="fmtQty(contribData.total_out || 0)"></span>
            </div>
        </div>

        <!-- 3. In vs Out (Total) -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between h-[295px]">
            <div class="mb-1">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">3. In vs Out (Total)</h3>
                <p class="text-xs text-slate-400 font-normal mt-0.5">Rasio perbandingan Total In vs Total Out</p>
            </div>
            <div id="chartContribCompare" class="w-full flex-1 flex items-center justify-center min-h-0" wire:ignore></div>
            <div class="mt-2 pt-2 border-t border-base-200/80 flex items-center justify-between text-xs px-1 font-semibold">
                <span class="opacity-70 uppercase tracking-wider text-slate-500">Rasio In / Out</span>
                <span class="font-extrabold text-slate-700 dark:text-slate-200" x-text="(contribData.total_out > 0 ? ((contribData.total_in / contribData.total_out) * 100).toFixed(1) : 0) + '%'"></span>
            </div>
        </div>
    </div>

    <!-- Baris 2: Volume In vs Out by Breakdown (Rasio 2) & Tren Bulanan In vs Out (Rasio 3) -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Chart 1: Volume In vs Out by Breakdown (Rasio 2 / 40% Lebar / Col-Span-2) -->
        <div class="col-span-1 lg:col-span-2 bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 lg:p-5 shadow-sm relative flex flex-col justify-between h-[310px]">
            <div class="flex justify-between items-start mb-1">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Volume In vs Out by {{ $this->breakdownLabel }}</h3>
                    <p class="text-xs text-slate-400 font-normal mt-0.5">Perbandingan volume per {{ strtolower($this->breakdownLabel) }}</p>
                </div>
                <!-- Custom HTML Legend -->
                <div class="flex items-center gap-3 text-xs font-medium text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background-color: #20C997;"></span> In</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background-color: #6366F1;"></span> Out</div>
                </div>
            </div>
            <div id="chartRegionHBar" class="w-full h-[225px]" wire:ignore></div>
        </div>

        <!-- Chart 2: Tren Bulanan In vs Out (Jan - Des) (Rasio 3 / 60% Lebar / Col-Span-3) -->
        <div class="col-span-1 lg:col-span-3 bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 lg:p-5 shadow-sm flex flex-col justify-between h-[310px]">
            <div class="flex justify-between items-start mb-1">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Tren Bulanan In vs Out (Jan - Des)</h3>
                    <p class="text-xs text-slate-400 font-normal mt-0.5">Volume karton Sell In dan Sell Out per bulan tahun {{ $selectedYear }}</p>
                </div>
                <div class="flex gap-4 text-xs font-medium text-slate-500 dark:text-slate-400">
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background-color: #20C997;"></span> Sell In</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background-color: #6366F1;"></span> Sell Out</div>
                </div>
            </div>
            <div id="chartMonthlyBar" class="w-full h-[225px]" wire:ignore></div>
        </div>
    </div>

    <!-- Baris 3: Tren Volume Tahunan by Month (YoY) -->
    <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 lg:p-5 shadow-sm flex flex-col justify-between h-[310px]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-1">
            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-0.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Tren Volume Tahunan by Month (Jan - Des)</h3>
                <p class="text-xs text-slate-400 font-normal">
                    YoY: 
                    <span class="inline-flex items-center gap-1 font-semibold text-slate-600 dark:text-slate-300">
                        <span class="inline-block w-3.5 h-0.5 bg-slate-500"></span> Solid = {{ $selectedYear }} (TY)
                    </span>
                    <span class="inline-flex items-center gap-1 font-semibold text-slate-600 dark:text-slate-300 ml-1.5">
                        <span class="inline-block w-3.5 border-b border-dashed border-slate-500"></span> Dashed = {{ $selectedYear - 1 }} (LY)
                    </span>
                </p>
            </div>
            <!-- Interactive View Toggle -->
            <div class="flex items-center gap-2">
                <div class="join bg-base-200/60 p-0.5 rounded-lg border border-base-300">
                    <button type="button" 
                            @click="switchTrendView('all')" 
                            :class="trendViewMode === 'all' ? 'btn-primary shadow-sm' : 'btn-ghost text-slate-500'" 
                            class="btn btn-xs rounded-md font-bold px-2.5 text-xs">
                        Semua
                    </button>
                    <button type="button" 
                            @click="switchTrendView('in')" 
                            :class="trendViewMode === 'in' ? 'btn-primary shadow-sm' : 'btn-ghost text-slate-500'" 
                            class="btn btn-xs rounded-md font-bold px-2.5 text-xs">
                        Hanya In
                    </button>
                    <button type="button" 
                            @click="switchTrendView('out')" 
                            :class="trendViewMode === 'out' ? 'btn-primary shadow-sm' : 'btn-ghost text-slate-500'" 
                            class="btn btn-xs rounded-md font-bold px-2.5 text-xs">
                        Hanya Out
                    </button>
                </div>
            </div>
        </div>
        <div id="chartSalesTrend" class="w-full h-[225px] relative" wire:ignore></div>
    </div>

    <!-- Performance Tables Section (4 Columns) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        
        <!-- Top 10 Cabang Sell In -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between h-[340px]">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Top 10 Cabang (Sell In)</h3>
                <span class="inline-block w-2 h-2 rounded-full bg-cyan-500"></span>
            </div>
            <div class="overflow-y-auto flex-1 rounded-xl border border-base-200/80">
                <table class="table table-xs w-full">
                    <thead class="bg-base-200/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 sticky top-0 z-10 border-b border-base-300 dark:border-slate-700">
                        <tr>
                            <th class="uppercase text-slate-700 dark:text-slate-200 font-bold bg-base-200/90 dark:bg-slate-800">Cabang</th>
                            <th class="text-right text-cyan-600 dark:text-cyan-400 font-bold bg-base-200/90 dark:bg-slate-800">In (Ctn)</th>
                            <th class="text-right text-slate-600 dark:text-slate-400 font-medium bg-base-200/90 dark:bg-slate-800">Out (Ctn)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCabangIn as $row)
                        <tr class="border-b border-base-100 hover:bg-base-200/50">
                            <td class="font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[110px]">{{ $row['cabang'] }}</td>
                            <td class="text-right font-bold text-cyan-600 dark:text-cyan-400">{{ number_format($row['in'], 0, ',', '.') }}</td>
                            <td class="text-right text-slate-500 dark:text-slate-400">{{ number_format($row['out'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top 10 Cabang Sell Out -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between h-[340px]">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Top 10 Cabang (Sell Out)</h3>
                <span class="inline-block w-2 h-2 rounded-full bg-indigo-500"></span>
            </div>
            <div class="overflow-y-auto flex-1 rounded-xl border border-base-200/80">
                <table class="table table-xs w-full">
                    <thead class="bg-base-200/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 sticky top-0 z-10 border-b border-base-300 dark:border-slate-700">
                        <tr>
                            <th class="uppercase text-slate-700 dark:text-slate-200 font-bold bg-base-200/90 dark:bg-slate-800">Cabang</th>
                            <th class="text-right text-indigo-600 dark:text-indigo-400 font-bold bg-base-200/90 dark:bg-slate-800">Out (Ctn)</th>
                            <th class="text-right text-slate-600 dark:text-slate-400 font-medium bg-base-200/90 dark:bg-slate-800">In (Ctn)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCabangOut as $row)
                        <tr class="border-b border-base-100 hover:bg-base-200/50">
                            <td class="font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[110px]">{{ $row['cabang'] }}</td>
                            <td class="text-right font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($row['out'], 0, ',', '.') }}</td>
                            <td class="text-right text-slate-500 dark:text-slate-400">{{ number_format($row['in'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top 10 Subbrand -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between h-[340px]">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Top 10 Subbrand Terlaris</h3>
                <span class="inline-block w-2 h-2 rounded-full bg-cyan-500"></span>
            </div>
            <div class="overflow-y-auto flex-1 rounded-xl border border-base-200/80">
                <table class="table table-xs w-full">
                    <thead class="bg-base-200/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 sticky top-0 z-10 border-b border-base-300 dark:border-slate-700">
                        <tr>
                            <th class="uppercase text-slate-700 dark:text-slate-200 font-bold bg-base-200/90 dark:bg-slate-800">Subbrand</th>
                            <th class="text-right text-slate-600 dark:text-slate-400 font-medium bg-base-200/90 dark:bg-slate-800">In (Ctn)</th>
                            <th class="text-right text-cyan-600 dark:text-cyan-400 font-bold bg-base-200/90 dark:bg-slate-800">Out (Ctn)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topSubbrand as $row)
                        <tr class="border-b border-base-100 hover:bg-base-200/50">
                            <td class="font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[110px]" title="{{ $row['subbrand'] }}">{{ $row['subbrand'] }}</td>
                            <td class="text-right text-slate-500 dark:text-slate-400">{{ number_format($row['in'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-cyan-600 dark:text-cyan-400">{{ number_format($row['out'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rekap Kategori Produk -->
        <div class="bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 shadow-sm flex flex-col justify-between h-[340px]">
            <div class="flex items-center justify-between mb-2.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Rekap Kategori Produk</h3>
                <span class="inline-block w-2 h-2 rounded-full bg-purple-500"></span>
            </div>
            <div class="overflow-y-auto flex-1 rounded-xl border border-base-200/80 flex flex-col justify-between">
                <table class="table table-xs w-full">
                    <thead class="bg-base-200/90 dark:bg-slate-800 text-slate-700 dark:text-slate-200 sticky top-0 z-10 border-b border-base-300 dark:border-slate-700">
                        <tr>
                            <th class="uppercase text-slate-700 dark:text-slate-200 font-bold bg-base-200/90 dark:bg-slate-800">Kategori</th>
                            <th class="text-right text-slate-600 dark:text-slate-400 font-medium bg-base-200/90 dark:bg-slate-800">In (Ctn)</th>
                            <th class="text-right text-purple-600 dark:text-purple-400 font-bold bg-base-200/90 dark:bg-slate-800">Out (Ctn)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topKategori as $row)
                        <tr class="border-b border-base-100 hover:bg-base-200/50">
                            <td class="font-semibold text-slate-700 dark:text-slate-200 truncate max-w-[100px]">{{ $row['kategory'] }}</td>
                            <td class="text-right text-slate-500 dark:text-slate-400">{{ number_format($row['in'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-purple-600 dark:text-purple-400">{{ number_format($row['out'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-2.5 bg-base-200/40 border-t border-base-200 dark:border-slate-700 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300 font-medium">
                    <span>Total Kategori:</span>
                    <span class="font-bold text-purple-600 dark:text-purple-400">{{ count($topKategori) }} Kategori</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Hierarchical Pivot Table (Matrix View) -->
    <div class="mt-6 bg-base-100/80 backdrop-blur-md border border-base-200 rounded-2xl p-4 lg:p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs uppercase font-bold text-slate-700 dark:text-slate-200 tracking-wider">Matrix View (Quantity / Karton)</h2>
        </div>
        <div class="overflow-auto rounded-xl max-h-[460px] border border-base-200 shadow-inner">
            <table class="table table-xs w-full text-center whitespace-nowrap">
                <thead class="bg-matrix-header text-white sticky top-0 z-20 shadow-sm">
                    <tr>
                        <th rowspan="2" class="border-r border-matrix-header bg-matrix-header align-middle uppercase text-left pl-4 min-w-[200px]">Subbrand</th>
                        @foreach(range(1, $pivotMaxMonth) as $m)
                            <th colspan="2" class="border-r border-matrix-header">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</th>
                        @endforeach
                        <th colspan="2" class="border-l-4 border-matrix-header bg-matrix-header">{{ !empty($selectedMonths) ? 'TOTAL (FILTER)' : 'YTD TOTAL' }}</th>
                    </tr>
                    <tr>
                        @foreach(range(1, $pivotMaxMonth) as $m)
                            <th class="bg-matrix-header text-right">In (Ctn)</th>
                            <th class="border-r border-matrix-header bg-matrix-header text-right">Out (Ctn)</th>
                        @endforeach
                        <th class="border-l-4 border-matrix-header bg-matrix-header text-right">In (Ctn)</th>
                        <th class="bg-matrix-header text-right">Out (Ctn)</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600">
                    @foreach($pivotData as $sb => $sbData)
                        <tr class="hover:bg-slate-50 border-b border-base-200">
                            <td class="font-semibold border-r border-slate-200 text-left pl-4 uppercase text-slate-700 whitespace-nowrap">{{ $sb }}</td>
                            @foreach(range(1, $pivotMaxMonth) as $m)
                                <td class="text-right">{{ number_format($sbData[$m]['in'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right border-r border-slate-200">{{ number_format($sbData[$m]['out'] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            <td class="text-right border-l-4 border-slate-300 font-bold text-teal-600">{{ number_format($sbData['YTD']['in'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-indigo-600">{{ number_format($sbData['YTD']['out'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <!-- Grand Total Row -->
                    @if(!empty($pivotGrandTotal))
                    <tr class="bg-matrix-header text-white font-black sticky bottom-0 z-10 shadow-lg">
                        <td class="border-r border-matrix-header uppercase text-left pl-4">GRAND TOTAL</td>
                        @foreach(range(1, $pivotMaxMonth) as $m)
                            <td class="text-right">{{ number_format($pivotGrandTotal[$m]['in'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right border-r border-matrix-header">{{ number_format($pivotGrandTotal[$m]['out'] ?? 0, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-right border-l-4 border-matrix-header text-yellow-300">{{ number_format($pivotGrandTotal['YTD']['in'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right text-yellow-300">{{ number_format($pivotGrandTotal['YTD']['out'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts for ApexCharts & Map -->
    @push('scripts')
    <script>
        const registerDashboardV2Qty = () => {
            Alpine.data('dashboardV2Qty', () => ({
                charts: {},
                contribData: {},
                trendViewMode: 'all',
                trendData: {},

                init() {
                    this.contribData = JSON.parse(@this.chartContribution || '{}');
                    this.trendData = JSON.parse(@this.chartSalesTrend || '{}');

                    setTimeout(() => {
                        this.initCharts();
                    }, 150);

                    // Re-render when Livewire dispatches 'charts-updated'
                    Livewire.on('charts-updated', () => {
                        this.contribData = JSON.parse(@this.chartContribution || '{}');
                        this.trendData = JSON.parse(@this.chartSalesTrend || '{}');
                        setTimeout(() => {
                            this.destroyCharts();
                            this.initCharts();
                        }, 50);
                    });

                    // Wire navigate support
                    document.addEventListener('livewire:navigated', () => {
                        this.contribData = JSON.parse(@this.chartContribution || '{}');
                        this.trendData = JSON.parse(@this.chartSalesTrend || '{}');
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

                switchTrendView(mode) {
                    this.trendViewMode = mode;
                    if (!this.charts.trend) return;

                    const d = this.trendData;
                    const ty = d.year_ty || 'TY';
                    const ly = d.year_ly || 'LY';

                    let series = [];
                    let colors = [];
                    let strokeWidth = [];
                    let strokeDash = [];

                    if (mode === 'all') {
                        series = [
                            { name: 'Sell In ' + ty, data: d.in_ty || [] },
                            { name: 'Sell In ' + ly, data: d.in_ly || [] },
                            { name: 'Sell Out ' + ty, data: d.out_ty || [] },
                            { name: 'Sell Out ' + ly, data: d.out_ly || [] }
                        ];
                        colors = ['#20C997', '#20C997', '#6366F1', '#6366F1'];
                        strokeWidth = [3, 2, 3, 2];
                        strokeDash = [0, 5, 0, 5];
                    } else if (mode === 'in') {
                        series = [
                            { name: 'Sell In ' + ty, data: d.in_ty || [] },
                            { name: 'Sell In ' + ly, data: d.in_ly || [] }
                        ];
                        colors = ['#20C997', '#20C997'];
                        strokeWidth = [3, 2];
                        strokeDash = [0, 5];
                    } else if (mode === 'out') {
                        series = [
                            { name: 'Sell Out ' + ty, data: d.out_ty || [] },
                            { name: 'Sell Out ' + ly, data: d.out_ly || [] }
                        ];
                        colors = ['#6366F1', '#6366F1'];
                        strokeWidth = [3, 2];
                        strokeDash = [0, 5];
                    }

                    this.charts.trend.updateOptions({
                        series: series,
                        colors: colors,
                        stroke: {
                            curve: 'smooth',
                            width: strokeWidth,
                            dashArray: strokeDash
                        }
                    });
                },

                getThemeConfig() {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'neon-dark';
                    return {
                        mode: isDark ? 'dark' : 'light',
                        textColor: isDark ? '#a6adbb' : '#4f5d73',
                        gridColor: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)'
                    };
                },

                fmtQty(val) {
                    if (!val || val === 0) return '0 Ctn';
                    if (val >= 1e6) return (val / 1e6).toFixed(1) + 'M Ctn';
                    if (val >= 1e3) return (val / 1e3).toFixed(1) + 'K Ctn';
                    return new Intl.NumberFormat('id-ID').format(val) + ' Ctn';
                },

                initCharts() {
                    const theme = this.getThemeConfig();
                    const commonOptions = {
                        chart: { toolbar: { show: false }, background: 'transparent' },
                        theme: { mode: theme.mode },
                        grid: { borderColor: theme.gridColor, strokeDashArray: 4 },
                        dataLabels: { enabled: false },
                        xaxis: { labels: { style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' } } },
                        yaxis: { labels: { style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' }, formatter: (val) => val >= 1e3 ? (val/1e3).toFixed(0)+'K' : val } }
                    };

                    const self = this;
                    const dataContrib = this.contribData;
                    const contribColors = ['#20C997', '#0EA5E9', '#F59E0B', '#8B5CF6', '#EC4899', '#3B82F6', '#94A3B8'];
                    const compareColors = ['#20C997', '#6366F1'];

                    // 1A. Kontribusi In (Kategori)
                    const elContribIn = document.querySelector("#chartContribIn");
                    if (elContribIn) {
                        this.charts.contribIn = new ApexCharts(elContribIn, {
                            series: dataContrib.series_in || [],
                            labels: dataContrib.labels || [],
                            colors: contribColors,
                            chart: { type: 'donut', height: 215, background: 'transparent', toolbar: { show: false }, animations: { enabled: true, speed: 400 } },
                            stroke: { show: false },
                            dataLabels: { enabled: false },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '72%',
                                        labels: {
                                            show: true,
                                            name: { show: true, fontSize: '11px', fontWeight: 600, color: theme.textColor, offsetY: -4 },
                                            value: { show: true, fontSize: '15px', fontWeight: 800, color: theme.textColor, offsetY: 2, formatter: (val) => self.fmtQty(val) },
                                            total: { show: true, label: 'Total In', fontSize: '11px', fontWeight: 600, color: theme.textColor, formatter: () => self.fmtQty(dataContrib.total_in || 0) }
                                        }
                                    }
                                }
                            },
                            legend: { show: true, position: 'bottom', horizontalAlign: 'center', fontSize: '11px', fontWeight: 500, itemMargin: { horizontal: 5, vertical: 2 }, markers: { width: 7, height: 7, radius: 4 }, labels: { colors: theme.textColor } },
                            tooltip: {
                                custom: function({ series, seriesIndex, w }) {
                                    const label = w.globals.labels[seriesIndex] || '';
                                    const val   = series[seriesIndex] || 0;
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0) || 1;
                                    const pct   = ((val / total) * 100).toFixed(1);
                                    const bg    = theme.mode === 'dark' ? '#1e293b' : '#ffffff';
                                    const border= theme.mode === 'dark' ? '#334155' : '#e2e8f0';
                                    return `<div style="padding:0.4rem 0.65rem;font-size:12px;line-height:1.4;background:${bg};color:${theme.textColor};border:1px solid ${border};border-radius:0.375rem;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
                                        <div style="font-weight:bold;margin-bottom:2px;">${label}</div>
                                        <div style="color:#20C997;font-weight:600;">${self.fmtQty(val)} &nbsp;<span style="opacity:0.7;font-weight:normal;">(${pct}%)</span></div>
                                    </div>`;
                                }
                            }
                        });
                        this.charts.contribIn.render();
                    }

                    // 1B. Kontribusi Out (Kategori)
                    const elContribOut = document.querySelector("#chartContribOut");
                    if (elContribOut) {
                        this.charts.contribOut = new ApexCharts(elContribOut, {
                            series: dataContrib.series_out || [],
                            labels: dataContrib.labels || [],
                            colors: contribColors,
                            chart: { type: 'donut', height: 215, background: 'transparent', toolbar: { show: false }, animations: { enabled: true, speed: 400 } },
                            stroke: { show: false },
                            dataLabels: { enabled: false },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '72%',
                                        labels: {
                                            show: true,
                                            name: { show: true, fontSize: '11px', fontWeight: 600, color: theme.textColor, offsetY: -4 },
                                            value: { show: true, fontSize: '15px', fontWeight: 800, color: theme.textColor, offsetY: 2, formatter: (val) => self.fmtQty(val) },
                                            total: { show: true, label: 'Total Out', fontSize: '11px', fontWeight: 600, color: theme.textColor, formatter: () => self.fmtQty(dataContrib.total_out || 0) }
                                        }
                                    }
                                }
                            },
                            legend: { show: true, position: 'bottom', horizontalAlign: 'center', fontSize: '11px', fontWeight: 500, itemMargin: { horizontal: 5, vertical: 2 }, markers: { width: 7, height: 7, radius: 4 }, labels: { colors: theme.textColor } },
                            tooltip: {
                                custom: function({ series, seriesIndex, w }) {
                                    const label = w.globals.labels[seriesIndex] || '';
                                    const val   = series[seriesIndex] || 0;
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0) || 1;
                                    const pct   = ((val / total) * 100).toFixed(1);
                                    const bg    = theme.mode === 'dark' ? '#1e293b' : '#ffffff';
                                    const border= theme.mode === 'dark' ? '#334155' : '#e2e8f0';
                                    return `<div style="padding:0.4rem 0.65rem;font-size:12px;line-height:1.4;background:${bg};color:${theme.textColor};border:1px solid ${border};border-radius:0.375rem;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
                                        <div style="font-weight:bold;margin-bottom:2px;">${label}</div>
                                        <div style="color:#6366F1;font-weight:600;">${self.fmtQty(val)} &nbsp;<span style="opacity:0.7;font-weight:normal;">(${pct}%)</span></div>
                                    </div>`;
                                }
                            }
                        });
                        this.charts.contribOut.render();
                    }

                    // 1C. In vs Out (Total)
                    const elContribCompare = document.querySelector("#chartContribCompare");
                    if (elContribCompare) {
                        const totalIn = dataContrib.total_in || 0;
                        const totalOut = dataContrib.total_out || 0;
                        this.charts.contribCompare = new ApexCharts(elContribCompare, {
                            series: [totalIn, totalOut],
                            labels: ['Sell In', 'Sell Out'],
                            colors: compareColors,
                            chart: { type: 'donut', height: 215, background: 'transparent', toolbar: { show: false }, animations: { enabled: true, speed: 400 } },
                            stroke: { show: false },
                            dataLabels: { enabled: false },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '72%',
                                        labels: {
                                            show: true,
                                            name: { show: true, fontSize: '11px', fontWeight: 600, color: theme.textColor, offsetY: -4 },
                                            value: { show: true, fontSize: '15px', fontWeight: 800, color: theme.textColor, offsetY: 2, formatter: (val) => self.fmtQty(val) },
                                            total: { show: true, label: 'Total In+Out', fontSize: '11px', fontWeight: 600, color: theme.textColor, formatter: () => self.fmtQty(totalIn + totalOut) }
                                        }
                                    }
                                }
                            },
                            legend: { show: true, position: 'bottom', horizontalAlign: 'center', fontSize: '11px', fontWeight: 500, itemMargin: { horizontal: 5, vertical: 2 }, markers: { width: 7, height: 7, radius: 4 }, labels: { colors: theme.textColor } },
                            tooltip: {
                                custom: function({ series, seriesIndex, w }) {
                                    const label = w.globals.labels[seriesIndex] || '';
                                    const val   = series[seriesIndex] || 0;
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0) || 1;
                                    const pct   = ((val / total) * 100).toFixed(1);
                                    const bg    = theme.mode === 'dark' ? '#1e293b' : '#ffffff';
                                    const border= theme.mode === 'dark' ? '#334155' : '#e2e8f0';
                                    const clr   = seriesIndex === 0 ? '#20C997' : '#6366F1';
                                    return `<div style="padding:0.4rem 0.65rem;font-size:12px;line-height:1.4;background:${bg};color:${theme.textColor};border:1px solid ${border};border-radius:0.375rem;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
                                        <div style="font-weight:bold;margin-bottom:2px;">${label}</div>
                                        <div style="color:${clr};font-weight:600;">${self.fmtQty(val)} &nbsp;<span style="opacity:0.7;font-weight:normal;">(${pct}%)</span></div>
                                    </div>`;
                                }
                            }
                        });
                        this.charts.contribCompare.render();
                    }

                    // 2. Region HBar (In vs Out Side-by-Side)
                    const dataHBar = JSON.parse(@this.chartRegionHBar || '{}');
                    const elHBar = document.querySelector("#chartRegionHBar");
                    if (elHBar) {
                        this.charts.hbar = new ApexCharts(elHBar, {
                            ...commonOptions,
                            series: [
                                { name: 'Sell In (Ctn)', data: dataHBar.in || [] },
                                { name: 'Sell Out (Ctn)', data: dataHBar.out || [] }
                            ],
                            colors: ['#20C997', '#6366F1'],
                            chart: { type: 'bar', height: 225, background: 'transparent', toolbar: { show: false } },
                            plotOptions: { 
                                bar: { 
                                    horizontal: true, 
                                    borderRadius: 3, 
                                    barHeight: '65%',
                                    dataLabels: { position: 'top' } 
                                } 
                            },
                            legend: { show: false },
                            dataLabels: { enabled: false },
                            xaxis: { 
                                categories: dataHBar.labels || [], 
                                labels: { 
                                    style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' },
                                    formatter: (val) => val >= 1e3 ? (val/1e3).toFixed(0)+'K' : val
                                } 
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' }
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                followCursor: true,
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    const label = w.globals.labels[dataPointIndex] || '';
                                    const bg = theme.mode === 'dark' ? '#1e293b' : '#ffffff';
                                    const border = theme.mode === 'dark' ? '#334155' : '#e2e8f0';
                                    const text = theme.mode === 'dark' ? '#f1f5f9' : '#1e293b';

                                    let html = `<div style="padding:8px 12px; background:${bg}; border:1px solid ${border}; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); font-size:11px; min-width:160px; pointer-events:none;">`;
                                    html += `<div style="font-weight:700; color:${text}; margin-bottom:5px; padding-bottom:4px; border-bottom:1px solid ${border};">${label}</div>`;
                                    w.globals.seriesNames.forEach((name, i) => {
                                        const val = series[i] ? series[i][dataPointIndex] : null;
                                        const color = w.globals.colors[i];
                                        if (val !== undefined && val !== null) {
                                            html += `<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:3px;">
                                                <span style="display:flex; align-items:center; gap:5px; color:${text};">
                                                    <span style="width:7px; height:7px; border-radius:50%; background:${color}; display:inline-block;"></span>
                                                    <span>${name}</span>
                                                </span>
                                                <span style="font-weight:700; color:${color}; font-variant-numeric:tabular-nums;">${new Intl.NumberFormat('id-ID').format(val)} Ctn</span>
                                            </div>`;
                                        }
                                    });
                                    html += `</div>`;
                                    return html;
                                }
                            }
                        });
                        this.charts.hbar.render();
                    }

                    // 3. Monthly Bar (In vs Out Side-by-Side)
                    const dataMonthly = JSON.parse(@this.chartMonthlyBar || '{}');
                    const elMonthly = document.querySelector("#chartMonthlyBar");
                    if (elMonthly) {
                        this.charts.monthly = new ApexCharts(elMonthly, {
                            ...commonOptions,
                            series: [
                                { name: 'Sell In (Ctn)', data: dataMonthly.in || [] },
                                { name: 'Sell Out (Ctn)', data: dataMonthly.out || [] }
                            ],
                            colors: ['#20C997', '#6366F1'],
                            chart: { type: 'bar', height: 225, background: 'transparent', toolbar: { show: false } },
                            plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '58%' } },
                            legend: { show: false },
                            xaxis: { 
                                categories: dataMonthly.labels || [], 
                                labels: { style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' } } 
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' },
                                    formatter: (val) => val >= 1e3 ? (val/1e3).toFixed(0)+'K' : val
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                followCursor: true,
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    const monthLabel = dataMonthly.labels ? dataMonthly.labels[dataPointIndex] : (w.globals.labels ? w.globals.labels[dataPointIndex] : '');
                                    const bg = theme.mode === 'dark' ? '#1e293b' : '#ffffff';
                                    const border = theme.mode === 'dark' ? '#334155' : '#e2e8f0';
                                    const text = theme.mode === 'dark' ? '#f1f5f9' : '#1e293b';

                                    let html = `<div style="padding:8px 12px; background:${bg}; border:1px solid ${border}; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); font-size:11px; min-width:160px; pointer-events:none;">`;
                                    html += `<div style="font-weight:700; color:${text}; margin-bottom:5px; padding-bottom:4px; border-bottom:1px solid ${border};">Bulan: ${monthLabel}</div>`;
                                    w.globals.seriesNames.forEach((name, i) => {
                                        const val = series[i] ? series[i][dataPointIndex] : null;
                                        const color = w.globals.colors[i];
                                        if (val !== undefined && val !== null) {
                                            html += `<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:3px;">
                                                <span style="display:flex; align-items:center; gap:5px; color:${text};">
                                                    <span style="width:7px; height:7px; border-radius:50%; background:${color}; display:inline-block;"></span>
                                                    <span>${name}</span>
                                                </span>
                                                <span style="font-weight:700; color:${color}; font-variant-numeric:tabular-nums;">${new Intl.NumberFormat('id-ID').format(val)} Ctn</span>
                                            </div>`;
                                        }
                                    });
                                    html += `</div>`;
                                    return html;
                                }
                            }
                        });
                        this.charts.monthly.render();
                    }

                    // 4. Sales Trend Multi-Line (Year-over-Year In vs Out)
                    const dTrend = this.trendData;
                    const elTrend = document.querySelector("#chartSalesTrend");
                    if (elTrend) {
                        const ty = dTrend.year_ty || 'TY';
                        const ly = dTrend.year_ly || 'LY';

                        let initialSeries = [];
                        let initialColors = [];
                        let initialWidth = [];
                        let initialDash = [];

                        if (this.trendViewMode === 'in') {
                            initialSeries = [
                                { name: 'Sell In ' + ty, data: dTrend.in_ty || [] },
                                { name: 'Sell In ' + ly, data: dTrend.in_ly || [] }
                            ];
                            initialColors = ['#20C997', '#20C997'];
                            initialWidth = [3, 2];
                            initialDash = [0, 5];
                        } else if (this.trendViewMode === 'out') {
                            initialSeries = [
                                { name: 'Sell Out ' + ty, data: dTrend.out_ty || [] },
                                { name: 'Sell Out ' + ly, data: dTrend.out_ly || [] }
                            ];
                            initialColors = ['#6366F1', '#6366F1'];
                            initialWidth = [3, 2];
                            initialDash = [0, 5];
                        } else {
                            initialSeries = [
                                { name: 'Sell In ' + ty, data: dTrend.in_ty || [] },
                                { name: 'Sell In ' + ly, data: dTrend.in_ly || [] },
                                { name: 'Sell Out ' + ty, data: dTrend.out_ty || [] },
                                { name: 'Sell Out ' + ly, data: dTrend.out_ly || [] }
                            ];
                            initialColors = ['#20C997', '#20C997', '#6366F1', '#6366F1'];
                            initialWidth = [3, 2, 3, 2];
                            initialDash = [0, 5, 0, 5];
                        }

                        this.charts.trend = new ApexCharts(elTrend, {
                            ...commonOptions,
                            series: initialSeries,
                            colors: initialColors,
                            chart: { 
                                type: 'line', 
                                height: 225, 
                                background: 'transparent', 
                                toolbar: { show: false },
                                animations: { enabled: true, speed: 400 }
                            },
                            stroke: { curve: 'smooth', width: initialWidth, dashArray: initialDash },
                            legend: { 
                                position: 'top', 
                                horizontalAlign: 'right', 
                                fontSize: '11px', 
                                labels: { colors: theme.textColor },
                                itemMargin: { horizontal: 6, vertical: 0 },
                                offsetY: -4
                            },
                            markers: { size: [3.5, 2.5, 3.5, 2.5], strokeWidth: 1.5, hover: { size: 5.5 } },
                            xaxis: { categories: dTrend.labels || [], labels: { style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' } } },
                            yaxis: {
                                labels: {
                                    style: { colors: theme.textColor, fontSize: '11px', fontFamily: 'inherit' },
                                    formatter: (val) => val >= 1e3 ? (val/1e3).toFixed(0)+'K' : val
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                followCursor: true,
                                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                    const monthName = w.globals.categoryLabels[dataPointIndex] || '';
                                    const bg = theme.mode === 'dark' ? '#1e293b' : '#ffffff';
                                    const border = theme.mode === 'dark' ? '#334155' : '#e2e8f0';
                                    const text = theme.mode === 'dark' ? '#f1f5f9' : '#1e293b';
                                    const subText = theme.mode === 'dark' ? '#94a3b8' : '#64748b';

                                    let html = `<div style="padding:10px 14px; background:${bg}; border:1px solid ${border}; border-radius:10px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); font-size:11px; min-width:190px; pointer-events:none;">`;
                                    html += `<div style="font-weight:700; color:${text}; margin-bottom:6px; padding-bottom:5px; border-bottom:1px solid ${border}; display:flex; justify-content:space-between; align-items:center;">
                                        <span>Bulan: ${monthName}</span>
                                        <span style="font-size:10px; font-weight:normal; color:${subText};">YoY</span>
                                    </div>`;

                                    w.globals.seriesNames.forEach((name, i) => {
                                        const val = series[i] ? series[i][dataPointIndex] : null;
                                        const color = w.globals.colors[i] || '#20C997';
                                        const isDashed = w.config.stroke && w.config.stroke.dashArray && w.config.stroke.dashArray[i] > 0;
                                        const lineStyle = isDashed ? 'dashed' : 'solid';

                                        if (val !== undefined && val !== null) {
                                            html += `<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-top:4px;">
                                                <span style="display:flex; align-items:center; gap:6px; color:${text}; font-weight:500;">
                                                    <span style="width:10px; height:0; border-top:2px ${lineStyle} ${color}; display:inline-block;"></span>
                                                    <span>${name}</span>
                                                </span>
                                                <span style="font-weight:700; color:${color}; font-variant-numeric:tabular-nums;">${new Intl.NumberFormat('id-ID').format(val)} Ctn</span>
                                            </div>`;
                                        }
                                    });

                                    html += `</div>`;
                                    return html;
                                }
                            }
                        });
                        this.charts.trend.render();
                    }
                }
            }));
        };

        if (window.Alpine) {
            registerDashboardV2Qty();
        } else {
            document.addEventListener('alpine:init', registerDashboardV2Qty);
        }
    </script>
    @endpush
</div>
</div>
