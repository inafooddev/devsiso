<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-2 md:gap-3 w-full h-full -mb-2 md:-mb-3 lg:-mb-4"
     x-data="{
         showKpi: false,
         init() {
             localStorage.removeItem('jks_team_elite_show_kpi');
         },
         toggleKpi() {
             this.showKpi = !this.showKpi;
         }
     }">
    <x-slot name="title">JKS Team Elite</x-slot>

    {{-- TABS & TOGGLE KPI --}}
    @php
        $hasKpiData = !empty($filterTeam) && !empty($filterStartDate) && !empty($filterEndDate);
    @endphp
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center justify-between shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100">Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">SISO vs ESKA</a>
            <a href="{{ route('call-plan.jks-team-elite.route-efficiency') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Route Efficiency</a>
            <a href="{{ route('call-plan.jks-team-elite.clustering') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Clustering</a>
        </div>

        {{-- Toggle KPI Button di Tabs Bar --}}
        <div class="tooltip tooltip-left" data-tip="{{ !$hasKpiData ? 'Pilih Team dan tanggal untuk melihat KPI' : 'Sembunyikan / Tampilkan Ringkasan KPI' }}">
            <button 
                type="button" 
                @click="toggleKpi()" 
                class="btn btn-xs gap-1.5 rounded-lg border transition-all duration-200 font-semibold select-none shadow-2xs"
                :class="showKpi ? 'btn-ghost text-base-content/70 hover:text-base-content border-base-300' : 'bg-primary/10 text-primary border-primary/30 hover:bg-primary/20'"
                @if(!$hasKpiData) disabled @endif
            >
                <x-heroicon-o-chart-bar class="w-3.5 h-3.5" />
                <span x-text="showKpi ? 'Sembunyikan KPI' : 'Tampilkan KPI'">Tampilkan KPI</span>
                <span class="inline-block transition-transform duration-200" :class="{ 'rotate-180': showKpi }">
                    <x-heroicon-o-chevron-down class="w-3 h-3" />
                </span>
            </button>
        </div>
    </div>

    <div class="flex flex-col flex-1 min-h-0">
        {{-- Notifikasi --}}
        @if (session()->has('message') || session()->has('error'))
            <div class="mb-6 mt-4">
                @if (session()->has('message'))
                    <div x-data="{ show: true }" x-show="show" class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success mb-4 flex justify-between items-start">
                        <div class="flex items-start gap-3">
                            <x-heroicon-s-check-circle class="w-6 h-6 shrink-0 mt-0.5" />
                            <div>
                                <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                                <div class="text-sm">{{ session('message') }}</div>
                            </div>
                        </div>
                        <button type="button" @click="show = false" class="btn btn-ghost btn-sm btn-circle shrink-0 hover:bg-success/20">
                            <x-heroicon-s-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div x-data="{ show: true }" x-show="show" class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error flex justify-between items-start">
                        <div class="flex items-start gap-3">
                            <x-heroicon-s-x-circle class="w-6 h-6 shrink-0 mt-0.5" />
                            <div>
                                <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                                <div class="text-sm">{{ session('error') }}</div>
                            </div>
                        </div>
                        <button type="button" @click="show = false" class="btn btn-ghost btn-sm btn-circle shrink-0 hover:bg-error/20">
                            <x-heroicon-s-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                @endif
            </div>
        @endif

        @if(!empty($filterTeam) && !empty($filterStartDate) && !empty($filterEndDate))
            <div 
                x-show="showKpi"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                style="display: none;"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0 mb-4"
            >
                {{-- Card Total Toko --}}
                @php
                    $gapToko = max(0, $paretoKpi['total_toko'] - $kpi['total_toko']);
                    $pctToko = $paretoKpi['total_toko'] > 0 ? round(($kpi['total_toko'] / $paretoKpi['total_toko']) * 100) : 0;
                @endphp
                <div class="bg-base-100 rounded-xl p-3 lg:p-4 border border-base-200 shadow-sm relative overflow-hidden group hover:border-primary/30 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                        <x-heroicon-s-building-storefront class="w-16 h-16 md:w-20 md:h-20 text-primary" />
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-1.5 mb-2">
                            <div class="w-6 h-6 rounded-md bg-primary/10 flex items-center justify-center text-primary">
                                <x-heroicon-s-building-storefront class="w-3 h-3" />
                            </div>
                            <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest">Total Toko</h3>
                        </div>
                        <div class="flex items-baseline gap-1 flex-wrap mb-2 cursor-help" title="Format: Terjadwal (Semua Pilar) / Terjadwal (3 Pilar) / Target Pareto (3 Pilar)">
                            <span class="text-xl font-bold text-primary leading-none">{{ number_format($kpi['total_toko_all'] ?? 0) }}</span>
                            <span class="text-sm font-bold text-primary/70 leading-none">/ {{ number_format($kpi['total_toko']) }}</span>
                            <span class="text-xs font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_toko']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-primary h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctToko) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                            <span class="text-base-content/50">Gap: <span class="text-error">{{ number_format($gapToko) }}</span></span>
                            <span class="text-primary">{{ $pctToko }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Card Total Target --}}
                @php
                    $gapTarget = max(0, $paretoKpi['total_target'] - $kpi['total_target']);
                    $pctTarget = $paretoKpi['total_target'] > 0 ? round(($kpi['total_target'] / $paretoKpi['total_target']) * 100) : 0;
                @endphp
                <div class="bg-base-100 rounded-xl p-3 lg:p-4 border border-base-200 shadow-sm relative overflow-hidden group hover:border-success/30 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                        <x-heroicon-s-banknotes class="w-16 h-16 md:w-20 md:h-20 text-success" />
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-1.5 mb-2">
                            <div class="w-6 h-6 rounded-md bg-success/10 flex items-center justify-center text-success">
                                <x-heroicon-s-banknotes class="w-3 h-3" />
                            </div>
                            <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest">Total Target</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-success leading-none">{{ number_format($kpi['total_target']) }}</span>
                            <span class="text-xs font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_target']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-success h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctTarget) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                            <span class="text-base-content/50">Gap: <span class="text-error">{{ number_format($gapTarget) }}</span></span>
                            <span class="text-success">{{ $pctTarget }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Card Total RWO --}}
                @php
                    $gapRwo = max(0, $paretoKpi['total_rwo'] - $kpi['total_rwo']);
                    $pctRwo = $paretoKpi['total_rwo'] > 0 ? round(($kpi['total_rwo'] / $paretoKpi['total_rwo']) * 100) : 0;
                @endphp
                <div class="bg-base-100 rounded-xl p-3 lg:p-4 border border-base-200 shadow-sm relative overflow-hidden group hover:border-info/30 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                        <x-heroicon-s-shopping-bag class="w-16 h-16 md:w-20 md:h-20 text-info" />
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-1.5 mb-2">
                            <div class="w-6 h-6 rounded-md bg-info/10 flex items-center justify-center text-info">
                                <x-heroicon-s-shopping-bag class="w-3 h-3" />
                            </div>
                            <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest">Total RWO</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-info leading-none">{{ number_format($kpi['total_rwo']) }}</span>
                            <span class="text-xs font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_rwo']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-info h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctRwo) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                            <span class="text-base-content/50">Gap: <span class="text-error">{{ number_format($gapRwo) }}</span></span>
                            <span class="text-info">{{ $pctRwo }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Card Total PNR --}}
                @php
                    $gapPnr = max(0, $paretoKpi['total_pnr'] - $kpi['total_pnr']);
                    $pctPnr = $paretoKpi['total_pnr'] > 0 ? round(($kpi['total_pnr'] / $paretoKpi['total_pnr']) * 100) : 0;
                @endphp
                <div class="bg-base-100 rounded-xl p-3 lg:p-4 border border-base-200 shadow-sm relative overflow-hidden group hover:border-secondary/30 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                        <x-heroicon-s-archive-box class="w-16 h-16 md:w-20 md:h-20 text-secondary" />
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-1.5 mb-2">
                            <div class="w-6 h-6 rounded-md bg-secondary/10 flex items-center justify-center text-secondary">
                                <x-heroicon-s-archive-box class="w-3 h-3" />
                            </div>
                            <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest">Total PNR</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-secondary leading-none">{{ number_format($kpi['total_pnr']) }}</span>
                            <span class="text-xs font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_pnr']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-secondary h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctPnr) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                            <span class="text-base-content/50">Gap: <span class="text-error">{{ number_format($gapPnr) }}</span></span>
                            <span class="text-secondary">{{ $pctPnr }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Card Total NGVO --}}
                @php
                    $gapNgvo = max(0, $paretoKpi['total_ngvo'] - $kpi['total_ngvo']);
                    $pctNgvo = $paretoKpi['total_ngvo'] > 0 ? round(($kpi['total_ngvo'] / $paretoKpi['total_ngvo']) * 100) : 0;
                @endphp
                <div class="bg-base-100 rounded-xl p-3 lg:p-4 border border-base-200 shadow-sm relative overflow-hidden group hover:border-warning/30 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                        <x-heroicon-s-sparkles class="w-16 h-16 md:w-20 md:h-20 text-warning" />
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-1.5 mb-2">
                            <div class="w-6 h-6 rounded-md bg-warning/10 flex items-center justify-center text-warning">
                                <x-heroicon-s-sparkles class="w-3 h-3" />
                            </div>
                            <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest">Total NGVO</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-warning leading-none">{{ number_format($kpi['total_ngvo']) }}</span>
                            <span class="text-xs font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_ngvo']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-warning h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctNgvo) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider">
                            <span class="text-base-content/50">Gap: <span class="text-error">{{ number_format($gapNgvo) }}</span></span>
                            <span class="text-warning">{{ $pctNgvo }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="px-4 py-3 md:px-5 md:py-3.5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 md:gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base md:text-lg font-bold">JKS Team Elite</h2>
                        {{-- Button Toggle KPI di Header Card --}}
                        <div class="tooltip tooltip-right" data-tip="{{ !$hasKpiData ? 'Pilih Team dan tanggal untuk melihat KPI' : 'Sembunyikan / Tampilkan Ringkasan KPI' }}">
                            <button 
                                type="button" 
                                @click="toggleKpi()" 
                                class="btn btn-xs gap-1 rounded-lg border transition-all duration-200 font-semibold cursor-pointer shadow-2xs select-none"
                                :class="showKpi ? 'btn-ghost text-base-content/70 hover:text-base-content border-base-300' : 'bg-primary/10 text-primary border-primary/30 hover:bg-primary/20'"
                                @if(!$hasKpiData) disabled @endif
                            >
                                <x-heroicon-o-chart-bar class="w-3.5 h-3.5" />
                                <span x-text="showKpi ? 'Sembunyikan KPI' : 'Tampilkan KPI'">Tampilkan KPI</span>
                                <span class="inline-block transition-transform duration-200" :class="{ 'rotate-180': showKpi }">
                                    <x-heroicon-o-chevron-down class="w-3 h-3" />
                                </span>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data JKS Team Elite</p>
                </div>
                
                {{-- Menggunakan flex-wrap agar barisan aksi jatuh secara responsif --}}
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    
                    {{-- Filter Team --}}
                    <div class="relative w-full sm:w-48" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="btn btn-sm btn-outline border-base-300 font-normal w-full rounded-xl bg-base-100 flex items-center justify-between px-3 text-left hover:bg-base-200 hover:text-base-content hover:border-base-300" @if(count($teams) <= 1) disabled @endif>
                            <span class="truncate text-base-content/70">
                                @if(count($filterTeam) === 0)
                                    Pilih Team...
                                @elseif(count($filterTeam) === 1)
                                    {{ collect($teams)->firstWhere('kode_team', $filterTeam[0])->nama_team ?? '1 Team' }}
                                @else
                                    {{ count($filterTeam) }} Team Terpilih
                                @endif
                            </span>
                            <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/50" />
                        </button>
                        
                        <div x-show="open" 
                             x-transition
                             x-cloak
                             class="absolute z-50 w-80 mt-1 bg-base-100 border border-base-300 rounded-xl shadow-xl left-0 sm:left-auto sm:right-0 flex flex-col overflow-hidden">
                             
                            <div class="px-3 py-2 border-b border-base-300 bg-base-100 z-10 flex flex-col gap-2 shrink-0">
                                <div class="flex items-center justify-between gap-2">
                                    <button type="button" wire:click="selectAllTeams" class="btn btn-xs btn-ghost text-primary hover:bg-primary/10">Pilih Semua</button>
                                    <button type="button" wire:click="resetTeams" class="btn btn-xs btn-ghost text-error hover:bg-error/10">Reset</button>
                                </div>
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="searchTeamFilter" placeholder="Cari nama/kode team..." class="input input-sm input-bordered w-full rounded-lg pl-8 bg-base-200" />
                                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/50" />
                                </div>
                            </div>
                             
                            <div class="p-2 space-y-1 overflow-y-auto overflow-x-auto max-h-60">
                                @php
                                    $filteredTeams = empty($searchTeamFilter) 
                                        ? collect($teams) 
                                        : collect($teams)->filter(fn($t) => stripos($t->nama_team ?? '', $searchTeamFilter) !== false || stripos($t->kode_team ?? '', $searchTeamFilter) !== false);
                                @endphp

                                @forelse($filteredTeams as $team)
                                    <label class="flex items-center gap-3 p-2 hover:bg-base-200 rounded-lg cursor-pointer transition-colors w-max pr-4">
                                        <input type="checkbox" wire:model.live="filterTeam" value="{{ $team->kode_team }}" class="checkbox checkbox-sm checkbox-primary rounded-md shrink-0" />
                                        <span class="text-sm select-none whitespace-nowrap">{{ $team->nama_team }}</span>
                                    </label>
                                @empty
                                    <div class="text-center py-4 text-sm text-base-content/50">Team tidak ditemukan</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Filter Date Range & Smart Presets --}}
                    <div class="flex items-center gap-1.5 w-full sm:w-auto">
                        <input wire:model.live.debounce.300ms="filterStartDate" type="date" class="input input-sm input-bordered w-full sm:w-32 rounded-xl bg-base-100 border-base-300 text-xs" title="Tanggal Mulai">
                        <span class="text-base-content/50 text-xs hidden sm:inline">s/d</span>
                        <input wire:model.live.debounce.300ms="filterEndDate" type="date" class="input input-sm input-bordered w-full sm:w-32 rounded-xl bg-base-100 border-base-300 text-xs" title="Tanggal Akhir">
                        
                        {{-- Dropdown Pintasan Periode Cerdas --}}
                        <div class="dropdown dropdown-end">
                            <button type="button" tabindex="0" class="btn btn-sm btn-square btn-outline border-base-300 rounded-xl bg-base-100 text-primary hover:bg-base-200 hover:border-base-300" title="Pintasan Periode Cerdas">
                                <x-heroicon-m-calendar-days class="w-4 h-4" />
                            </button>
                            <ul tabindex="0" @click="document.activeElement?.blur()" class="dropdown-content z-30 menu menu-sm p-1.5 shadow-xl bg-base-100 rounded-2xl border border-base-300 w-44 text-xs mt-1">
                                <li class="menu-title text-[10px] uppercase tracking-wider text-base-content/50 px-2 py-1 font-bold">Periode Cepat</li>
                                <li><a wire:click="setDatePreset('today')" class="py-1.5 rounded-lg hover:bg-base-200">Hari Ini</a></li>
                                <li><a wire:click="setDatePreset('yesterday')" class="py-1.5 rounded-lg hover:bg-base-200">Kemarin</a></li>
                                <li><a wire:click="setDatePreset('this_week')" class="py-1.5 rounded-lg hover:bg-base-200">Minggu Ini</a></li>
                                <li><a wire:click="setDatePreset('last_week')" class="py-1.5 rounded-lg hover:bg-base-200">Minggu Lalu</a></li>
                                <div class="divider my-0.5"></div>
                                <li><a wire:click="setDatePreset('this_month')" class="py-1.5 rounded-lg font-semibold text-primary hover:bg-primary/10">Bulan Ini</a></li>
                                <li><a wire:click="setDatePreset('last_month')" class="py-1.5 rounded-lg hover:bg-base-200">Bulan Lalu</a></li>
                                <li><a wire:click="setDatePreset('next_month')" class="py-1.5 rounded-lg hover:bg-base-200">Bulan Depan</a></li>
                                @if(!empty($filterStartDate) || !empty($filterEndDate))
                                    <div class="divider my-0.5"></div>
                                    <li><a wire:click="setDatePreset('clear')" class="py-1.5 rounded-lg text-error hover:bg-error/10">Kosongkan Tanggal</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>



                    {{-- Separator 1 --}}
                    <div class="w-[1px] h-6 bg-base-300 hidden sm:block mx-1"></div>

                    {{-- Actions Button --}}
                    @php
                        $canExportBtn = !empty($filterTeam) && !empty($filterStartDate) && !empty($filterEndDate);
                    @endphp
                    <div class="flex flex-wrap items-center gap-1 md:gap-2 shrink-0">
                        
                        {{-- Import, Tambah, Maps --}}
                        @canImport('jks-team-elite.index')
                        <x-ui.action-button type="import" wire:click="openImportModal" />
                        @endcanImport

                        @canAdd('jks-team-elite.index')
                        <x-ui.action-button type="add" label="Tambah" wire:click="openCreateModal" />
                        @endcanAdd

                        <div class="tooltip tooltip-bottom" data-tip="{{ !$canExportBtn ? 'Pilih Team dan tanggal terlebih dahulu' : 'Lihat Peta' }}">
                            <x-ui.action-button type="default" icon="map" label="Maps" class="text-info bg-info/10 hover:bg-info hover:text-white border-0 shadow-sm" wire:click="showGlobalMap" :disabled="!$canExportBtn" />
                        </div>

                        {{-- Separator 2 --}}
                        <div class="w-[1px] h-6 bg-base-300 hidden sm:block mx-1"></div>

                        {{-- Export, Eska Export --}}
                        @canExport('jks-team-elite.index')
                        <div class="tooltip tooltip-bottom" data-tip="{{ !$canExportBtn ? 'Pilih Team dan tanggal terlebih dahulu' : 'Export data ke Excel' }}">
                            <x-ui.action-button type="export" wire:click="export" :disabled="!$canExportBtn" />
                        </div>
                        <div class="tooltip tooltip-bottom" data-tip="{{ !$canExportBtn ? 'Pilih Team dan tanggal terlebih dahulu' : 'Export data ke Excel format ESKA' }}">
                            <x-ui.action-button type="default" icon="arrow-down-tray" label="ESKA Export" class="text-info bg-info/10 hover:bg-info hover:text-white border-0 shadow-sm" wire:click="openExportEskaModal" :disabled="!$canExportBtn" />
                        </div>
                        <div class="tooltip tooltip-bottom" data-tip="{{ !$canExportBtn ? 'Pilih Team dan tanggal terlebih dahulu' : 'Cetak Data' }}">
                            <x-ui.action-button type="default" icon="printer" label="Print" class="text-secondary bg-secondary/10 hover:bg-secondary hover:text-white border-0 shadow-sm" wire:click="print" :disabled="!$canExportBtn" />
                        </div>
                        @endcanExport

                    </div>
                </div>
            </div>

            {{-- Body Card (Tabel Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap text-xs">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th>No</th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('tanggal')">
                                <div class="flex items-center gap-1">
                                    Tanggal
                                    @if($sortField === 'tanggal')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('nama_region')">
                                <div class="flex items-center gap-1">
                                    Region
                                    @if($sortField === 'nama_region')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('kode_team')">
                                <div class="flex items-center gap-1">
                                    Kode Team
                                    @if($sortField === 'kode_team')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('nama_team')">
                                <div class="flex items-center gap-1">
                                    Nama Team
                                    @if($sortField === 'nama_team')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th>Hari</th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('week_month')">
                                <div class="flex items-center justify-center gap-1">
                                    Week
                                    @if($sortField === 'week_month')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_toko')">
                                <div class="flex items-center justify-center gap-1">
                                    Total Toko
                                    @if($sortField === 'total_toko')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_rwo')">
                                <div class="flex items-center justify-center gap-1">
                                    Total RWO
                                    @if($sortField === 'total_rwo')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_pnr')">
                                <div class="flex items-center justify-center gap-1">
                                    Total PNR
                                    @if($sortField === 'total_pnr')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_ngvo')">
                                <div class="flex items-center justify-center gap-1">
                                    Total NGVO
                                    @if($sortField === 'total_ngvo')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3 text-primary" />
                                    @else
                                        <x-heroicon-o-arrows-up-down class="w-3 h-3 opacity-30" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(empty($filterTeam) || empty($filterStartDate) || empty($filterEndDate))
                            <tr>
                                <td colspan="12" class="text-center py-8 text-base-content/50">
                                    <x-heroicon-o-funnel class="w-12 h-12 mx-auto mb-3 opacity-30" />
                                    Silakan pilih <strong>Team</strong> dan <strong>Range Tanggal</strong> terlebih dahulu untuk menampilkan data.
                                </td>
                            </tr>
                        @else
                            @forelse ($records as $index => $record)
                                <tr wire:key="group-{{ $record->tanggal }}-{{ $record->kode_team }}-{{ $record->kode_region }}" class="group hover">
                                    <td>{{ $records->firstItem() + $index }}</td>
                                    <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($record->tanggal)->format('d M Y') }}</td>
                                    <td>{{ $record->nama_region }} ({{ $record->kode_region }})</td>
                                    <td>{{ $record->kode_team }}</td>
                                    <td><div class="font-bold">{{ $record->nama_team }}</div></td>
                                    <td>{{ \Carbon\Carbon::parse($record->tanggal)->locale('id')->isoFormat('dddd') }}</td>
                                    <td class="text-center font-bold">W-{{ $record->week_month ?? '-' }}</td>
                                    @php
                                        $dayOfWeek = \Carbon\Carbon::parse($record->tanggal)->dayOfWeekIso; // 1 = Senin, ..., 6 = Sabtu
                                        $isRed = false;
                                        if ($dayOfWeek >= 1 && $dayOfWeek <= 5 && $record->total_toko < 10) {
                                            $isRed = true;
                                        } elseif ($dayOfWeek == 6 && $record->total_toko < 5) {
                                            $isRed = true;
                                        }
                                        
                                        $badgeClass = 'badge-primary badge-outline hover:bg-primary hover:text-white';
                                        
                                        if ($isRed) {
                                            $badgeClass = 'badge-error badge-outline hover:bg-error hover:text-white';
                                        } elseif ($record->total_toko_bri_eva == 0) {
                                            $badgeClass = 'badge-primary badge-outline !bg-warning/30 hover:bg-primary hover:text-white';
                                        }
                                    @endphp
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}')" wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none" class="badge {{ $badgeClass }} font-bold cursor-pointer transition-colors">
                                            {{ $record->total_toko }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}', 'RWO')" wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none" class="badge badge-info badge-outline font-bold cursor-pointer hover:bg-info hover:text-white transition-colors">
                                            {{ $record->total_rwo ?? 0 }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}', 'PNR')" wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none" class="badge badge-secondary badge-outline font-bold cursor-pointer hover:bg-secondary hover:text-white transition-colors">
                                            {{ $record->total_pnr ?? 0 }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}', 'NGVO')" wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none" class="badge badge-warning badge-outline font-bold cursor-pointer hover:bg-warning hover:text-white transition-colors">
                                            {{ $record->total_ngvo ?? 0 }}
                                        </button>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-center gap-1">
                                            <x-ui.action-button type="default" icon="map" label="" class="btn-ghost text-info hover:bg-info/10 btn-square" title="Lihat Peta" wire:click="showMap('{{ $record->tanggal }}', '{{ $record->kode_team }}')" />
                                            @canEdit('jks-team-elite.index')
                                            <x-ui.action-button type="edit" class="btn-square" title="Edit Grup" wire:click="openEditModal('{{ $record->tanggal }}', '{{ $record->kode_team }}', '{{ $record->kode_region }}')" />
                                            @endcanEdit
                                            @canDelete('jks-team-elite.index')
                                            <x-ui.action-button type="delete" class="btn-square" title="Hapus Grup" wire:click="confirmDelete('{{ $record->tanggal }}', '{{ $record->kode_team }}', '{{ $record->kode_region }}')" />
                                            @endcanDelete
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-4 text-base-content/50">Tidak ada data ditemukan untuk kriteria tersebut.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Footer Card (Pagination) --}}
            <div class="px-4 py-2.5 sm:py-3 border-t border-base-200 shrink-0 bg-base-100 flex items-center justify-between">
                @if($records->hasPages())
                    <div class="w-full">
                        {{ $records->links() }}
                    </div>
                @else
                    <div class="text-xs text-base-content/60 flex items-center gap-2">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-success"></span>
                        <span>Menampilkan seluruh data ({{ $records->total() }} record)</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <livewire:jks-team-elite.form-modal :teams="$teams" wire:key="form-modal" />
    <livewire:jks-team-elite.import-modal wire:key="import-modal" />

    {{-- Modal Konfirmasi Hapus --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-neutral/40 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden text-center">
            <div class="p-8">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Grup Data?</h3>
                <p class="text-sm text-base-content/60">Tindakan ini akan menghapus semua customer pada grup ini secara permanen.</p>
            </div>
            <div class="flex justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl">Batal</button>
                <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete" class="btn btn-error flex-1 rounded-xl text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Detail Toko --}}
    <div x-data="{ open: @entangle('isStoreModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-neutral/40 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-[95vw] xl:max-w-7xl max-h-[92vh] flex flex-col overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between px-6 py-4 border-b border-base-200 bg-base-100 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <x-heroicon-s-building-storefront class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5">
                            <h3 class="font-bold text-base md:text-lg text-base-content truncate">{{ $storeModalTitle }}</h3>
                            <span class="badge badge-primary badge-sm font-bold text-white shrink-0">{{ count($storeModalData) }} Toko</span>
                        </div>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost hover:bg-base-200 text-base-content/60 hover:text-base-content">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto max-h-[78vh] p-0">
                <table class="table table-sm table-pin-rows table-zebra w-full text-xs">
                    <thead>
                        <tr class="bg-base-200/80 text-base-content/70">
                            <th class="w-12 text-center font-bold">No</th>
                            <th class="whitespace-nowrap font-bold">Distributor</th>
                            <th class="whitespace-nowrap font-bold">CustNo</th>
                            <th class="whitespace-nowrap font-bold">Nama Toko</th>
                            <th class="min-w-[240px] font-bold">Alamat</th>
                            <th class="text-center whitespace-nowrap font-bold">Pilar</th>
                            <th class="text-right whitespace-nowrap font-bold">Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($storeModalData as $index => $store)
                            <tr class="hover:bg-base-200/50 transition-colors">
                                <td class="text-center font-bold text-base-content/50 text-xs">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap font-medium text-base-content/80">{{ $store['distributor_name'] }}</td>
                                <td class="font-mono text-xs text-base-content/70 whitespace-nowrap">{{ $store['custno'] }}</td>
                                <td class="font-bold text-base-content whitespace-nowrap">{{ $store['custname'] }}</td>
                                <td class="text-base-content/80 text-[11px] leading-relaxed">
                                    {{ $store['addres'] ?? '-' }}
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    @if(!empty($store['pilar']))
                                        @php
                                            $pColor = 'badge-ghost';
                                            if (str_contains($store['pilar'], '1. RWO')) $pColor = 'badge-info text-white font-bold';
                                            elseif (str_contains($store['pilar'], '2. PNR')) $pColor = 'badge-secondary text-white font-bold';
                                            elseif (str_contains($store['pilar'], '3. NGVO')) $pColor = 'badge-warning text-white font-bold';
                                            elseif (str_contains($store['pilar'], '4. GRO')) $pColor = 'badge-neutral text-white font-semibold';
                                        @endphp
                                        <span class="badge badge-sm {{ $pColor }}">{{ $store['pilar'] }}</span>
                                    @else
                                        <span class="badge badge-sm badge-ghost text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td class="text-right font-semibold text-success whitespace-nowrap font-mono text-xs">
                                    {{ !empty($store['target']) ? number_format($store['target']) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/50">Tidak ada data toko.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($storeModalData) > 0)
                    <tfoot>
                        <tr class="bg-base-300 font-bold text-base-content border-t-2 border-base-content/10">
                            <td colspan="6" class="text-right px-4 py-3 uppercase tracking-wider text-xs">Total Subtotal Target:</td>
                            <td class="text-right px-4 py-3 text-success whitespace-nowrap font-mono text-sm">
                                {{ number_format(collect($storeModalData)->sum('target')) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <div class="flex items-center justify-between px-6 py-3 border-t border-base-200 bg-base-100 shrink-0">
                <span class="text-xs text-base-content/50 font-medium">Menampilkan {{ count($storeModalData) }} toko terjadwal</span>
                <button type="button" @click="open = false" class="btn btn-ghost btn-sm rounded-xl normal-case">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal Export ESKA Options --}}
    <div x-data="{ open: @entangle('isExportEskaModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-neutral/40 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <h3 class="font-bold text-lg text-base-content">Export ESKA Options</h3>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/50">Flag Delete</span>
                    </label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="selectedFlagDelete" value="Y" class="radio radio-primary" />
                            <span class="text-sm font-semibold">Y (Yes)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="selectedFlagDelete" value="N" class="radio radio-primary" />
                            <span class="text-sm font-semibold">N (No)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case">Batal</button>
                <button wire:click="exportEska" type="button" class="btn btn-primary rounded-xl px-8 normal-case text-white shadow-sm shadow-primary/20">
                    Export
                </button>
            </div>
        </div>
    </div>

    {{-- Map Modal --}}
    <div x-data="{
            map: null,
            markers: [],
            initMap() {
                if (this.map) {
                    this.map.remove();
                }
                
                // If maplibregl is not loaded yet, wait a bit
                if (typeof maplibregl === 'undefined') {
                    setTimeout(() => this.initMap(), 200);
                    return;
                }
                
                const currentBasemap = window._currentBasemapStyle || 'light';
                
                this.map = new maplibregl.Map({
                    container: 'store-map',
                    style: {
                        'version': 8,
                        'glyphs': 'https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf',
                        'sources': {
                            'raster-tiles-light': {
                                'type': 'raster',
                                'tiles': [
                                    'https://a.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://b.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://c.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://d.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc'
                                ],
                                'tileSize': 256,
                                'attribution': '© OpenStreetMap contributors, © CARTO'
                            },
                            'raster-tiles-dark': {
                                'type': 'raster',
                                'tiles': [
                                    'https://a.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://b.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://c.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://d.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc'
                                ],
                                'tileSize': 256,
                                'attribution': '© OpenStreetMap contributors, © CARTO'
                            },
                            'raster-tiles-voyager': {
                                'type': 'raster',
                                'tiles': [
                                    'https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://b.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://c.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                    'https://d.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc'
                                ],
                                'tileSize': 256,
                                'attribution': '© OpenStreetMap contributors, © CARTO'
                            }
                        },
                        'layers': [
                            {
                                'id': 'basemap-light',
                                'type': 'raster',
                                'source': 'raster-tiles-light',
                                'minzoom': 0,
                                'maxzoom': 19,
                                'layout': { 'visibility': currentBasemap === 'light' ? 'visible' : 'none' }
                            },
                            {
                                'id': 'basemap-dark',
                                'type': 'raster',
                                'source': 'raster-tiles-dark',
                                'minzoom': 0,
                                'maxzoom': 19,
                                'layout': { 'visibility': currentBasemap === 'dark' ? 'visible' : 'none' }
                            },
                            {
                                'id': 'basemap-voyager',
                                'type': 'raster',
                                'source': 'raster-tiles-voyager',
                                'minzoom': 0,
                                'maxzoom': 19,
                                'layout': { 'visibility': currentBasemap === 'voyager' ? 'visible' : 'none' }
                            }
                        ]
                    },
                    center: [106.816666, -6.200000],
                    zoom: 10
                });

                window.storeMap = this.map;
                this.map.addControl(new maplibregl.NavigationControl());
                
                // Add resize listener to fix modal sizing issue
                this.map.on('load', () => {
                    this.map.resize();
                });
            },
            updateMarkers(data) {
                if (window.renderMapMarkers) {
                    window.renderMapMarkers(this.map, this.markers, data);
                }
            }
        }"
        @init-map.window="setTimeout(() => { initMap(); updateMarkers($wire.mapModalData); }, 300)"
        @update-map-markers.window="updateMarkers($wire.mapModalData)"
        @map-add-store.window="$wire.addStoreFromMap($event.detail.custno, $event.detail.dist)"
        @global-map-add-store.window="$wire.addStoreFromGlobalMap($event.detail.custno, $event.detail.dist, $event.detail.date, $event.detail.team)"
        @global-map-update-store.window="$wire.updateStoreFromGlobalMap($event.detail.custno, $event.detail.dist, $event.detail.oldDate, $event.detail.oldTeam, $event.detail.newDate, $event.detail.newTeam)"
        @global-map-delete-store.window="$wire.deleteStoreFromGlobalMap($event.detail.custno, $event.detail.dist, $event.detail.date, $event.detail.team)"
    >
        <div class="modal z-[99999]" :class="{ 'modal-open': $wire.isMapModalOpen }">
            <div class="modal-box w-full !max-w-[95vw] h-[92vh] md:h-[90vh] p-0 overflow-hidden bg-base-100 rounded-2xl shadow-2xl flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-base-200 bg-base-100/50 backdrop-blur z-10 shrink-0">
                    <h3 class="text-lg font-black text-base-content tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center text-info shadow-inner">
                            <x-heroicon-s-map class="w-5 h-5" />
                        </div>
                        {{ $mapModalTitle }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="if(map) { map.resize(); updateMarkers($wire.mapModalData); }" class="btn btn-info btn-sm text-white rounded-xl" title="Klik jika peta tidak sejajar/blank">
                            <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh
                        </button>
                        <button wire:click="$set('isMapModalOpen', false)" class="btn btn-ghost btn-sm btn-square rounded-xl hover:bg-error/10 hover:text-error transition-colors">
                            <x-heroicon-s-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {{-- Mini Summary Cards Bar (Total & 3 Pilar Pareto vs JKS) --}}
                @if(!empty($mapModalSummary))
                    <div class="px-4 py-2.5 bg-base-100 border-b border-base-200 shrink-0">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 md:gap-3">
                            {{-- Card 1: Total Toko (3 Pilar) --}}
                            <div class="bg-base-200/50 rounded-xl p-2.5 border border-base-300 flex items-center justify-between gap-2 shadow-xs hover:border-success/40 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-success/10 flex items-center justify-center text-success shrink-0">
                                        <x-heroicon-s-building-storefront class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider truncate">Total Toko (3 Pilar)</div>
                                        <div class="flex items-baseline gap-1 mt-0.5">
                                            <span class="text-sm md:text-base font-black text-success leading-none">{{ number_format($mapModalSummary['total']['jks'] ?? 0) }}</span>
                                            <span class="text-[11px] font-bold text-base-content/40 leading-none">/ {{ number_format($mapModalSummary['total']['pareto'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="badge badge-success badge-sm font-bold text-[10px] text-white px-1.5 py-0">{{ $mapModalSummary['total']['pct'] ?? 0 }}%</div>
                                    <div class="text-[9px] text-base-content/50 font-semibold mt-1">Gap: <span class="text-error font-bold">{{ number_format($mapModalSummary['total']['gap'] ?? 0) }}</span></div>
                                </div>
                            </div>

                            {{-- Card 2: Pilar 1 (RWO) --}}
                            <div class="bg-base-200/50 rounded-xl p-2.5 border border-base-300 flex items-center justify-between gap-2 shadow-xs hover:border-info/40 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center text-info shrink-0">
                                        <x-heroicon-s-shopping-bag class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider truncate">Pilar 1 (RWO)</div>
                                        <div class="flex items-baseline gap-1 mt-0.5">
                                            <span class="text-sm md:text-base font-black text-info leading-none">{{ number_format($mapModalSummary['rwo']['jks'] ?? 0) }}</span>
                                            <span class="text-[11px] font-bold text-base-content/40 leading-none">/ {{ number_format($mapModalSummary['rwo']['pareto'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="badge badge-info badge-sm font-bold text-[10px] text-white px-1.5 py-0">{{ $mapModalSummary['rwo']['pct'] ?? 0 }}%</div>
                                    <div class="text-[9px] text-base-content/50 font-semibold mt-1">Gap: <span class="text-error font-bold">{{ number_format($mapModalSummary['rwo']['gap'] ?? 0) }}</span></div>
                                </div>
                            </div>

                            {{-- Card 3: Pilar 2 (PNR) --}}
                            <div class="bg-base-200/50 rounded-xl p-2.5 border border-base-300 flex items-center justify-between gap-2 shadow-xs hover:border-secondary/40 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                                        <x-heroicon-s-archive-box class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider truncate">Pilar 2 (PNR)</div>
                                        <div class="flex items-baseline gap-1 mt-0.5">
                                            <span class="text-sm md:text-base font-black text-secondary leading-none">{{ number_format($mapModalSummary['pnr']['jks'] ?? 0) }}</span>
                                            <span class="text-[11px] font-bold text-base-content/40 leading-none">/ {{ number_format($mapModalSummary['pnr']['pareto'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="badge badge-secondary badge-sm font-bold text-[10px] text-white px-1.5 py-0">{{ $mapModalSummary['pnr']['pct'] ?? 0 }}%</div>
                                    <div class="text-[9px] text-base-content/50 font-semibold mt-1">Gap: <span class="text-error font-bold">{{ number_format($mapModalSummary['pnr']['gap'] ?? 0) }}</span></div>
                                </div>
                            </div>

                            {{-- Card 4: Pilar 3 (NGVO) --}}
                            <div class="bg-base-200/50 rounded-xl p-2.5 border border-base-300 flex items-center justify-between gap-2 shadow-xs hover:border-warning/40 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-warning/10 flex items-center justify-center text-warning shrink-0">
                                        <x-heroicon-s-sparkles class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider truncate">Pilar 3 (NGVO)</div>
                                        <div class="flex items-baseline gap-1 mt-0.5">
                                            <span class="text-sm md:text-base font-black text-warning leading-none">{{ number_format($mapModalSummary['ngvo']['jks'] ?? 0) }}</span>
                                            <span class="text-[11px] font-bold text-base-content/40 leading-none">/ {{ number_format($mapModalSummary['ngvo']['pareto'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="badge badge-warning badge-sm font-bold text-[10px] text-white px-1.5 py-0">{{ $mapModalSummary['ngvo']['pct'] ?? 0 }}%</div>
                                    <div class="text-[9px] text-base-content/50 font-semibold mt-1">Gap: <span class="text-error font-bold">{{ number_format($mapModalSummary['ngvo']['gap'] ?? 0) }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Toolbar Filter Tanggal / Rute & Legend --}}
                <div class="px-4 py-2 bg-base-100 border-b border-base-200 flex flex-wrap items-center justify-between gap-3 text-xs z-10 shrink-0">
                    <div class="flex items-center gap-2">
                        <label for="map-date-filter" class="font-bold text-base-content/70 flex items-center gap-1.5 shrink-0 text-xs">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Filter Rute / Tanggal:
                        </label>
                        <select id="map-date-filter" onchange="window.filterMapByDate(this.value)" class="select select-xs select-bordered bg-base-200/80 font-semibold rounded-lg focus:outline-emerald-500 text-xs max-w-xs">
                            <option value="all">Semua Hari / Rute</option>
                        </select>
                        <button type="button" onclick="window.resetMapZoom && window.resetMapZoom()" class="btn btn-ghost btn-xs text-base-content/60 hover:text-base-content rounded-lg gap-1" title="Reset Zoom">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            <span>Reset View</span>
                        </button>

                        {{-- Basemap Switcher --}}
                        <div class="flex items-center gap-1.5 ml-1 pl-2 border-l border-base-300">
                            <label for="map-basemap-switcher" class="text-base-content/60 font-bold text-[11px] flex items-center gap-1 shrink-0">
                                <svg class="w-3.5 h-3.5 text-base-content/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                                Basemap:
                            </label>
                            <select id="map-basemap-switcher" onchange="window.setBasemapStyle(this.value)" class="select select-xs select-bordered bg-base-200/80 font-semibold rounded-lg text-xs focus:outline-primary">
                                <option value="light" selected>☀️ Clean Light (Positron)</option>
                                <option value="dark">🌙 Clean Dark (Matter)</option>
                                <option value="voyager">🧭 Voyager (Street)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold text-base-content/75">
                        <div class="flex items-center gap-1.5" title="Toko yang sudah terjadwal di JKS (Nomor adalah tanggal kunjungan)">
                            <span class="w-4 h-4 rounded-full bg-emerald-500 text-white text-[9px] font-black flex items-center justify-center shadow-xs">25</span>
                            <span>Jadwal JKS</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Garis rute menghubungkan urutan kunjungan toko dalam satu hari">
                            <span class="w-4 h-1 bg-emerald-500 rounded"></span>
                            <span>Rute Harian</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Toko Pareto Pilar 1 RWO (Belum Dijadwalkan)">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-xs"></span>
                            <span>Pilar 1 (RWO)</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Toko Pareto Pilar 2 PNR (Belum Dijadwalkan)">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shadow-xs"></span>
                            <span>Pilar 2 (PNR)</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Toko Pareto Pilar 3 NGVO (Belum Dijadwalkan)">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-xs"></span>
                            <span>Pilar 3 (NGVO)</span>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 w-full bg-base-200 relative">
                    <div id="store-map" wire:ignore style="height: 100%; width: 100%; z-index: 1;"></div>
                </div>
            </div>
            <div class="modal-backdrop bg-base-300/80 backdrop-blur-sm" wire:click="$set('isMapModalOpen', false)"></div>
        </div>
    </div>

@once
    @push('styles')
        <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
        <style>
            .maplibregl-popup-content {
                padding: 12px;
                border-radius: 12px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
        <script>
            function escHtml(str) {
                if (!str) return '';
                return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
            }

            const canEditMap = @json(auth()->check() && auth()->user()->hasMenuAccess('jks-team-elite.index', 'can_edit'));

            function buildStorePopupHtml(store, currentData) {
                const lat = store.lat;
                const lng = store.lng;
                const isScheduled = store.type === 'scheduled';
                const isGlobal = currentData?.isGlobal ?? false;
                const availableTeams = currentData?.availableTeams ?? [];
                
                let html = `<div class="p-1 space-y-1.5 text-xs text-gray-800">`;
                html += `<div><b class="text-sm font-bold text-gray-900">${escHtml(store.custname)}</b><span class="text-gray-500 font-mono text-[10px] ml-1">(${escHtml(store.custno)})</span></div>`;
                if (store.customer_address) {
                    html += `<div class="text-[11px] text-gray-600 leading-tight">${escHtml(store.customer_address)}</div>`;
                }
                html += `<div class="text-[10px] text-gray-500 font-mono flex items-center gap-1 cursor-pointer hover:text-primary transition-colors" onclick="window.open('https://www.google.com/maps/search/?api=1&query=${lat},${lng}', '_blank');" title="Buka di Google Maps">📍 ${lat}, ${lng} <span class="underline text-primary">Maps</span></div>`;
                
                if (isScheduled) {
                    if (store.tgl_format) {
                        html += `<div class="bg-gray-50 p-1.5 rounded-lg border border-gray-200 mt-1 space-y-0.5">
                            <div class="text-[11px] font-semibold text-gray-700 flex items-center justify-between">
                                <span>🕒 ${escHtml(store.tgl_format)} (${escHtml(store.hari)})</span>
                                <span class="badge badge-success badge-xs font-bold text-white shadow-xs">#${store.order_in_day || 1}</span>
                            </div>
                            <div class="text-[11px] font-semibold text-gray-700 flex items-center gap-1">👤 ${escHtml(store.nama_team)} - W${escHtml(store.minggu)}</div>
                        </div>`;
                    }
                    if (store.pilar) {
                        html += `<div class="mt-1"><span class="badge badge-outline badge-xs font-bold border-gray-300 text-gray-600 shadow-sm">Pilar: ${escHtml(store.pilar)}</span></div>`;
                    }
                    html += `<div class="mt-1 flex items-center gap-1.5">
                        <span class="badge badge-success badge-xs font-bold text-white shadow-sm">Dijadwalkan JKS</span>
                        ${store.day_number ? `<span class="badge badge-ghost badge-xs font-semibold text-gray-600">Tgl ${escHtml(store.day_number)}</span>` : ''}
                    </div>`;

                    if (store.tanggal_ymd) {
                        html += `
                            <button type="button" onclick="window.filterMapByDate('${store.tanggal_ymd}')" class="btn btn-outline btn-success btn-xs w-full rounded-lg mt-1 gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                Fokus Rute Tgl ${escHtml(store.day_number || '')}
                            </button>
                        `;
                    }
                    
                    if (canEditMap && isGlobal) {
                        let teamOptions = availableTeams.map(t => `<option value="${escHtml(t.kode_team)}" ${t.kode_team == store.kode_team ? 'selected' : ''}>${escHtml(t.nama_team)}</option>`).join('');
                        html += `
                            <div class="mt-2 border-t border-gray-200 pt-2 space-y-1.5">
                                <div class="form-control w-full">
                                    <label class="label p-0 pb-0.5"><span class="label-text text-[10px] font-semibold text-gray-600">Pindah Tanggal</span></label>
                                    <input type="date" id="edit_date_${store.custno}" value="${store.tanggal_ymd}" class="input input-xs input-bordered w-full bg-white text-gray-900 border-gray-300 rounded-lg" />
                                </div>
                                <div class="form-control w-full">
                                    <label class="label p-0 pb-0.5"><span class="label-text text-[10px] font-semibold text-gray-600">Pindah Team</span></label>
                                    <select id="edit_team_${store.custno}" class="select select-xs select-bordered w-full bg-white text-gray-900 border-gray-300 rounded-lg">
                                        ${teamOptions}
                                    </select>
                                </div>
                                <div id="popup_err_edit_${store.custno}" style="display:none;color:#ef4444;font-size:11px;margin-top:2px;">⚠️ Tanggal dan Team wajib diisi!</div>
                                <div class="flex gap-1.5 pt-1">
                                    <button type="button" onclick="
                                        let d = document.getElementById('edit_date_${store.custno}').value;
                                        let t = document.getElementById('edit_team_${store.custno}').value;
                                        if(!d || !t) { 
                                             let errEl = document.getElementById('popup_err_edit_${store.custno}');
                                             if(errEl) { errEl.style.display='block'; setTimeout(()=>errEl.style.display='none',3000); }
                                             return; 
                                        }
                                        window.dispatchEvent(new CustomEvent('global-map-update-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}', oldDate: '${store.tanggal_ymd}', oldTeam: '${store.kode_team}', newDate: d, newTeam: t } }))
                                    " class="btn btn-warning btn-xs flex-1 rounded-lg">Update</button>
                                    
                                    <button type="button" onclick="
                                        if(confirm('Yakin ingin menghapus jadwal ini?')) {
                                            window.dispatchEvent(new CustomEvent('global-map-delete-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}', date: '${store.tanggal_ymd}', team: '${store.kode_team}' } }))
                                        }
                                    " class="btn btn-error btn-xs text-white px-2.5 rounded-lg" title="Hapus Jadwal">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        `;
                    }
                } else {
                    // Pareto store (Belum dijadwalkan - 3 Pilar only)
                    if (store.pilar) {
                        html += `<div class="mt-1"><span class="badge badge-outline badge-xs font-bold border-gray-300 text-gray-600 shadow-sm">Pilar: ${escHtml(store.pilar)}</span></div>`;
                    }
                    html += `<div class="mt-1"><span class="badge badge-ghost badge-xs border-none bg-gray-200 text-gray-700 font-semibold shadow-sm">Belum Dijadwalkan</span></div>`;
                    
                    if (canEditMap) {
                        let activeDate = document.getElementById('map-date-filter')?.value;
                        let defaultDate = (activeDate && activeDate !== 'all') ? activeDate : '';

                        if (!isGlobal) {
                            html += `
                                <div class="mt-2 pt-1 border-t border-gray-200">
                                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('map-add-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}' } }))" class="btn btn-primary btn-xs text-white w-full rounded-lg">Tambahkan Jadwal</button>
                                </div>
                            `;
                        } else {
                            let teamOptions = availableTeams.map(t => `<option value="${escHtml(t.kode_team)}">${escHtml(t.nama_team)}</option>`).join('');
                            html += `
                                <div class="mt-2 border-t border-gray-200 pt-2 space-y-1.5">
                                    <div class="form-control w-full">
                                        <label class="label p-0 pb-0.5"><span class="label-text text-[10px] font-semibold text-gray-600">Tanggal</span></label>
                                        <input type="date" id="date_${store.custno}" value="${defaultDate}" class="input input-xs input-bordered w-full bg-white text-gray-900 border-gray-300 rounded-lg" />
                                    </div>
                                    <div class="form-control w-full">
                                        <label class="label p-0 pb-0.5"><span class="label-text text-[10px] font-semibold text-gray-600">Team</span></label>
                                        <select id="team_${store.custno}" class="select select-xs select-bordered w-full bg-white text-gray-900 border-gray-300 rounded-lg">
                                            ${teamOptions}
                                        </select>
                                    </div>
                                    <div id="popup_err_${store.custno}" style="display:none;color:#ef4444;font-size:11px;margin-top:2px;">⚠️ Tanggal dan Team wajib diisi!</div>
                                    <button type="button" onclick="
                                        let d = document.getElementById('date_${store.custno}').value;
                                        let t = document.getElementById('team_${store.custno}').value;
                                        if(!d || !t) { 
                                            let errEl = document.getElementById('popup_err_${store.custno}');
                                            if(errEl) { errEl.style.display='block'; setTimeout(()=>errEl.style.display='none',3000); }
                                            return; 
                                        }
                                        window.dispatchEvent(new CustomEvent('global-map-add-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}', date: d, team: t } }))
                                    " class="btn btn-primary btn-xs text-white w-full mt-1 rounded-lg">Simpan Jadwal</button>
                                </div>
                            `;
                        }
                    }
                }
                
                html += `</div>`;
                return html;
            }

            window.renderMapMarkers = function(map, markers, data) {
                if (!map) return;
                
                // Wait for style to load before adding source/layers
                if (!map.isStyleLoaded()) {
                    map.once('load', () => window.renderMapMarkers(map, markers, data));
                    return;
                }

                // Clear any active popup
                if (map._activePopup) {
                    map._activePopup.remove();
                    map._activePopup = null;
                }

                // Clear old DOM markers if any exist
                if (Array.isArray(markers)) {
                    markers.forEach(m => m.remove && m.remove());
                    markers.length = 0;
                }
                
                if (!data) return;
                map._currentMapData = data;

                // Populate date filter dropdown
                const dateFilterSelect = document.getElementById('map-date-filter');
                if (dateFilterSelect) {
                    const prevVal = dateFilterSelect.value;
                    let opts = '<option value="all">Semua Hari / Rute</option>';
                    if (Array.isArray(data.availableDates)) {
                        data.availableDates.forEach(d => {
                            opts += `<option value="${d.date}">Tgl ${d.day_number} (${d.formatted}) - ${d.count} Toko</option>`;
                        });
                    }
                    dateFilterSelect.innerHTML = opts;
                    if (prevVal && dateFilterSelect.querySelector(`option[value="${prevVal}"]`)) {
                        dateFilterSelect.value = prevVal;
                    } else {
                        dateFilterSelect.value = 'all';
                    }
                }

                // Sync basemap switcher with active basemap
                const basemapSwitcher = document.getElementById('map-basemap-switcher');
                if (basemapSwitcher) {
                    basemapSwitcher.value = window._currentBasemapStyle || 'light';
                }

                let bounds = new maplibregl.LngLatBounds();
                let hasValidPoint = false;

                function getPilarColor(pilar) {
                    if (!pilar) return '#6b7280';
                    if (pilar.includes('1. RWO')) return '#3b82f6';
                    if (pilar.includes('2. PNR')) return '#8b5cf6';
                    if (pilar.includes('3. NGVO')) return '#f97316';
                    return '#6b7280';
                }

                // 1. Build Route Features (LineStrings per date & team)
                const routeFeatures = [];
                if (Array.isArray(data.routes)) {
                    data.routes.forEach(r => {
                        if (Array.isArray(r.coordinates) && r.coordinates.length >= 2) {
                            routeFeatures.push({
                                type: 'Feature',
                                geometry: {
                                    type: 'LineString',
                                    coordinates: r.coordinates
                                },
                                properties: {
                                    date: String(r.date || ''),
                                    day_number: String(r.day_number || ''),
                                    team: String(r.team || ''),
                                    team_name: String(r.team_name || ''),
                                    store_count: Number(r.store_count || 0)
                                }
                            });
                        }
                    });
                }
                const routesGeojson = {
                    type: 'FeatureCollection',
                    features: routeFeatures
                };

                // 2. Build Point Features (Unclustered: only scheduled + 3 Pilar Pareto)
                const pointFeatures = [];

                // 2a. Pareto stores (Unscheduled, 3 Pilar only)
                if (Array.isArray(data.pareto)) {
                    data.pareto.forEach(store => {
                        let lat = parseFloat(store.latitude);
                        let lng = parseFloat(store.longitude);
                        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                            pointFeatures.push({
                                type: 'Feature',
                                geometry: {
                                    type: 'Point',
                                    coordinates: [lng, lat]
                                },
                                properties: {
                                    type: 'pareto',
                                    custno: String(store.custno || ''),
                                    custname: String(store.custname || ''),
                                    distributor_code: String(store.distributor_code || ''),
                                    customer_address: String(store.customer_address || ''),
                                    pilar: String(store.pilar || ''),
                                    color: getPilarColor(store.pilar),
                                    day_number: '',
                                    lat: lat,
                                    lng: lng
                                }
                            });
                            bounds.extend([lng, lat]);
                            hasValidPoint = true;
                        }
                    });
                }

                // 2b. Scheduled stores (Emerald green with date number)
                if (Array.isArray(data.scheduled)) {
                    data.scheduled.forEach(store => {
                        let lat = parseFloat(store.latitude);
                        let lng = parseFloat(store.longitude);
                        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                            pointFeatures.push({
                                type: 'Feature',
                                geometry: {
                                    type: 'Point',
                                    coordinates: [lng, lat]
                                },
                                properties: {
                                    type: 'scheduled',
                                    custno: String(store.custno || ''),
                                    custname: String(store.custname || ''),
                                    distributor_code: String(store.distributor_code || ''),
                                    customer_address: String(store.customer_address || ''),
                                    pilar: String(store.pilar || ''),
                                    color: '#10b981',
                                    day_number: String(store.day_number || ''),
                                    order_in_day: Number(store.order_in_day || 1),
                                    tgl_format: String(store.tgl_format || ''),
                                    tanggal_ymd: String(store.tanggal_ymd || ''),
                                    hari: String(store.hari || ''),
                                    minggu: String(store.minggu || ''),
                                    kode_team: String(store.kode_team || ''),
                                    nama_team: String(store.nama_team || ''),
                                    lat: lat,
                                    lng: lng
                                }
                            });
                            bounds.extend([lng, lat]);
                            hasValidPoint = true;
                        }
                    });
                }

                const pointsGeojson = {
                    type: 'FeatureCollection',
                    features: pointFeatures
                };
                map._allBounds = bounds;

                // Clean up any legacy cluster layers
                if (map.getLayer('clusters')) map.removeLayer('clusters');
                if (map.getLayer('cluster-count')) map.removeLayer('cluster-count');

                // Source & Layer for Route Lines (Render underneath points)
                const routeSource = map.getSource('store-routes');
                if (routeSource) {
                    routeSource.setData(routesGeojson);
                } else {
                    map.addSource('store-routes', {
                        type: 'geojson',
                        data: routesGeojson
                    });

                    map.addLayer({
                        id: 'route-lines',
                        type: 'line',
                        source: 'store-routes',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#10b981',
                            'line-width': 2.5,
                            'line-opacity': 0.75
                        }
                    });
                }

                // Source & Layers for Store Points (Unclustered: cluster = false)
                const pointSource = map.getSource('store-points');
                if (pointSource) {
                    pointSource.setData(pointsGeojson);
                } else {
                    map.addSource('store-points', {
                        type: 'geojson',
                        data: pointsGeojson,
                        cluster: false
                    });

                    // Layer: Unclustered Circles
                    map.addLayer({
                        id: 'unclustered-point',
                        type: 'circle',
                        source: 'store-points',
                        paint: {
                            'circle-color': ['get', 'color'],
                            'circle-radius': [
                                'case',
                                ['==', ['get', 'type'], 'scheduled'],
                                9.5,
                                6.0
                            ],
                            'circle-stroke-width': 1.5,
                            'circle-stroke-color': '#ffffff'
                        }
                    });

                    // Layer: Date Number Labels on Scheduled Points
                    map.addLayer({
                        id: 'point-labels',
                        type: 'symbol',
                        source: 'store-points',
                        filter: ['==', ['get', 'type'], 'scheduled'],
                        layout: {
                            'text-field': '{day_number}',
                            'text-font': ['Open Sans Semibold'],
                            'text-size': 9.5,
                            'text-allow-overlap': true,
                            'text-ignore-placement': true
                        },
                        paint: {
                            'text-color': '#ffffff'
                        }
                    });
                }

                // Attach click & hover events once to the map instance
                if (!map._pointListenersAttached) {
                    // Open on-demand rich popup on store point click
                    map.on('click', 'unclustered-point', (e) => {
                        const renderedFeatures = map.queryRenderedFeatures(e.point, { layers: ['unclustered-point'] });
                        if (!renderedFeatures.length) return;

                        const coordinates = e.features[0].geometry.coordinates.slice();
                        while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                            coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                        }

                        let popupContent = '';
                        if (renderedFeatures.length === 1) {
                            popupContent = buildStorePopupHtml(renderedFeatures[0].properties, map._currentMapData);
                        } else {
                            popupContent = `<div class="p-1 space-y-2 max-h-72 overflow-y-auto">
                                <div class="text-[11px] font-bold text-gray-700 pb-1 border-b border-gray-200">${renderedFeatures.length} Toko di Titik Ini:</div>`;
                            renderedFeatures.forEach((feat, idx) => {
                                popupContent += `<div class="${idx > 0 ? 'pt-2 border-t border-dashed border-gray-200' : ''}">`;
                                popupContent += buildStorePopupHtml(feat.properties, map._currentMapData);
                                popupContent += `</div>`;
                            });
                            popupContent += `</div>`;
                        }

                        if (map._activePopup) {
                            map._activePopup.remove();
                        }

                        map._activePopup = new maplibregl.Popup({ offset: 12, maxWidth: '320px' })
                            .setLngLat(coordinates)
                            .setHTML(popupContent)
                            .addTo(map);
                    });

                    // Cursor interactions
                    map.on('mouseenter', 'unclustered-point', () => { map.getCanvas().style.cursor = 'pointer'; });
                    map.on('mouseleave', 'unclustered-point', () => { map.getCanvas().style.cursor = ''; });

                    map._pointListenersAttached = true;
                }

                // Fit bounds
                if (hasValidPoint && !bounds.isEmpty()) {
                    map.fitBounds(bounds, { padding: 50, duration: 1000, maxZoom: 15 });
                    setTimeout(() => map.resize(), 500);
                }
            };

            window.filterMapByDate = function(targetDate) {
                if (!window.storeMap || !window.storeMap._currentMapData) return;
                const map = window.storeMap;
                const data = map._currentMapData;

                const selectEl = document.getElementById('map-date-filter');
                if (selectEl && selectEl.value !== targetDate) {
                    selectEl.value = targetDate;
                }

                if (!targetDate || targetDate === 'all') {
                    if (map.getLayer('route-lines')) {
                        map.setFilter('route-lines', null);
                        map.setPaintProperty('route-lines', 'line-opacity', 0.75);
                        map.setPaintProperty('route-lines', 'line-width', 2.5);
                    }
                    if (map.getLayer('unclustered-point')) {
                        map.setFilter('unclustered-point', null);
                    }
                    if (map.getLayer('point-labels')) {
                        map.setFilter('point-labels', ['==', ['get', 'type'], 'scheduled']);
                    }
                    if (map._allBounds && !map._allBounds.isEmpty()) {
                        map.fitBounds(map._allBounds, { padding: 50, duration: 800, maxZoom: 15 });
                    }
                    return;
                }

                // Filter to specific date
                if (map.getLayer('route-lines')) {
                    map.setFilter('route-lines', ['==', ['get', 'date'], targetDate]);
                    map.setPaintProperty('route-lines', 'line-opacity', 1.0);
                    map.setPaintProperty('route-lines', 'line-width', 3.5);
                }

                if (map.getLayer('unclustered-point')) {
                    map.setFilter('unclustered-point', [
                        'any',
                        ['==', ['get', 'type'], 'pareto'],
                        ['all', ['==', ['get', 'type'], 'scheduled'], ['==', ['get', 'tanggal_ymd'], targetDate]]
                    ]);
                }

                if (map.getLayer('point-labels')) {
                    map.setFilter('point-labels', [
                        'all',
                        ['==', ['get', 'type'], 'scheduled'],
                        ['==', ['get', 'tanggal_ymd'], targetDate]
                    ]);
                }

                // Fit bounds to that day's scheduled stores
                let dayBounds = new maplibregl.LngLatBounds();
                let hasDayPoint = false;
                if (Array.isArray(data.scheduled)) {
                    data.scheduled.forEach(s => {
                        if (s.tanggal_ymd === targetDate && s.latitude && s.longitude) {
                            let lat = parseFloat(s.latitude);
                            let lng = parseFloat(s.longitude);
                            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                                dayBounds.extend([lng, lat]);
                                hasDayPoint = true;
                            }
                        }
                    });
                }

                if (hasDayPoint && !dayBounds.isEmpty()) {
                    map.fitBounds(dayBounds, { padding: 70, duration: 800, maxZoom: 16 });
                }
            };

            window.resetMapZoom = function() {
                window.filterMapByDate('all');
            };

            window.setBasemapStyle = function(styleKey) {
                window._currentBasemapStyle = styleKey;
                const selectEl = document.getElementById('map-basemap-switcher');
                if (selectEl && selectEl.value !== styleKey) {
                    selectEl.value = styleKey;
                }
                if (!window.storeMap) return;
                const map = window.storeMap;
                ['light', 'dark', 'voyager'].forEach(k => {
                    const layerId = 'basemap-' + k;
                    if (map.getLayer(layerId)) {
                        map.setLayoutProperty(layerId, 'visibility', k === styleKey ? 'visible' : 'none');
                    }
                });
            };

            // Form Map Logic
            window.formMap = null;
            window.formMarkers = [];

            function initFormMap() {
                try {
                    if (window.formMap) {
                        return;
                    }
                    
                    let mapContainer = document.getElementById('form-map');
                    if (!mapContainer) {
                        console.error('form-map container not found!');
                        return;
                    }
                    
                    window.formMap = new maplibregl.Map({
                        container: mapContainer,
                        style: {
                            'version': 8,
                            'glyphs': 'https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf',
                            'sources': {
                                'raster-tiles': {
                                    'type': 'raster',
                                    'tiles': [
                                        'https://a.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                        'https://b.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                        'https://c.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                        'https://d.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc'
                                    ],
                                    'tileSize': 256,
                                    'attribution': '© OpenStreetMap contributors, © CARTO'
                                }
                            },
                            'layers': [
                                {
                                    'id': 'simple-tiles',
                                    'type': 'raster',
                                    'source': 'raster-tiles',
                                    'minzoom': 0,
                                    'maxzoom': 19
                                }
                            ]
                        },
                        center: [113.9213, -0.7893],
                        zoom: 4,
                        attributionControl: false
                    });

                    window.formMap.addControl(new maplibregl.NavigationControl(), 'top-right');
                } catch (e) {
                    console.error('Error initializing formMap:', e);
                    let mapEl = document.getElementById('form-map');
                    if (mapEl) {
                        mapEl.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#ef4444;font-size:0.8rem;padding:1rem;text-align:center;">⚠️ Gagal memuat peta. Coba tutup dan buka kembali form.</div>';
                    }
                }
            }

            function updateFormMapMarkers(selectedCustomers, recommendedStores) {
                if (!window.formMap) {
                    initFormMap();
                }
                
                // Clear old markers
                window.formMarkers.forEach(m => m.remove());
                window.formMarkers = [];
                
                let bounds = new maplibregl.LngLatBounds();
                let hasValidMarker = false;

                try {
                    // Selected stores (Green) - Render first so they are at the bottom layer
                    if (selectedCustomers) {
                        let selArray = Array.isArray(selectedCustomers) ? selectedCustomers : Object.values(selectedCustomers);
                        selArray.forEach(store => {
                            let lat = parseFloat(store?.latitude);
                            let lng = parseFloat(store?.longitude);
                            if (lat !== 0 && lng !== 0 && !isNaN(lat) && !isNaN(lng)) {
                                let popup = new maplibregl.Popup({ offset: 25 }).setHTML(`<div class="text-xs"><b>${escHtml(store.custno)}</b><br>${escHtml(store.custname)}<br>${escHtml(store.distributor_code)}</div>`);
                                let m = new maplibregl.Marker({ color: '#22c55e' })
                                    .setLngLat([lng, lat])
                                    .setPopup(popup)
                                    .addTo(window.formMap);
                                m.custno = store.custno;
                                window.formMarkers.push(m);
                                bounds.extend([lng, lat]);
                                hasValidMarker = true;
                            }
                        });
                    }

                    function getPilarColor(pilar) {
                        if (!pilar) return '#6b7280';
                        if (pilar.includes('1. RWO')) return '#3b82f6';
                        if (pilar.includes('2. PNR')) return '#8b5cf6';
                        if (pilar.includes('3. NGVO')) return '#f97316';
                        return '#6b7280';
                    }

                    // Recommended stores (By Pilar) - Render last so they are on top
                    if (recommendedStores) {
                        let recArray = Array.isArray(recommendedStores) ? recommendedStores : Object.values(recommendedStores);
                        // Reverse array so Pilar 1 (which comes first alphabetically from DB) is rendered LAST (on top of others)
                        recArray.slice().reverse().forEach(store => {
                            let lat = parseFloat(store?.latitude);
                            let lng = parseFloat(store?.longitude);
                            if (lat !== 0 && lng !== 0 && !isNaN(lat) && !isNaN(lng)) {
                                let pilarColor = getPilarColor(store.pilar);
                                let popupContent = `<div class="text-xs"><b>${escHtml(store.custno)}</b><br>${escHtml(store.custname)}<br>${escHtml(store.distributor_code)}`;
                                if (store.pilar) popupContent += `<br><span class="font-bold text-gray-500">Pilar: ${escHtml(store.pilar)}</span>`;
                                popupContent += `</div>`;
                                
                                let popup = new maplibregl.Popup({ offset: 25 }).setHTML(popupContent);
                                let m = new maplibregl.Marker({ color: pilarColor })
                                    .setLngLat([lng, lat])
                                    .setPopup(popup)
                                    .addTo(window.formMap);
                                m.custno = store.custno;
                                window.formMarkers.push(m);
                                bounds.extend([lng, lat]);
                                hasValidMarker = true;
                            }
                        });
                    }

                    if (hasValidMarker) {
                        window.formMap.fitBounds(bounds, {padding: 30, maxZoom: 15, duration: 1000});
                    } else {
                        window.formMap.jumpTo({center: [113.9213, -0.7893], zoom: 4});
                    }
                    setTimeout(() => window.formMap.resize(), 500);
                } catch (e) {
                    console.error('Error in updateFormMapMarkers:', e);
                }
            }

            function focusFormMap(lat, lng, custno) {
                if (window.formMap && lat !== 0 && lng !== 0) {
                    window.formMap.flyTo({ center: [lng, lat], zoom: 16, duration: 1500 });
                    
                    if (custno) {
                        let marker = window.formMarkers.find(m => m.custno === custno);
                        if (marker) {
                            if (!marker.getPopup().isOpen()) {
                                marker.togglePopup();
                            }
                        }
                    }
                }
            }

            window.addEventListener('open-new-tab', (e) => {
                window.open(e.detail.url, '_blank');
            });
        </script>
    @endpush
@endonce
</div>
