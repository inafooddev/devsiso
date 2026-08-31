<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">JKS Team Elite</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100">Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">SISO vs ESKA</a>
            <a href="{{ route('call-plan.jks-team-elite.route-efficiency') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Route Efficiency</a>
            <a href="{{ route('call-plan.jks-team-elite.clustering') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Clustering</a>
        </div>
    </div>

    <div class="flex flex-col flex-1 min-h-0 h-full">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0 mb-4">
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
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">JKS Team Elite</h2>
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

                    {{-- Filter Date Range --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input wire:model.live.debounce.300ms="filterStartDate" type="date" class="input input-sm input-bordered w-full sm:w-32 rounded-xl bg-base-100 border-base-300">
                        <span class="text-base-content/50 text-sm hidden sm:inline">s/d</span>
                        <input wire:model.live.debounce.300ms="filterEndDate" type="date" class="input input-sm input-bordered w-full sm:w-32 rounded-xl bg-base-100 border-base-300">
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
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                @if($records->hasPages())
                    {{ $records->links() }}
                @else
                    <div class="text-xs md:text-sm text-base-content/60 text-center sm:text-left">
                        Menampilkan seluruh data
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
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
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
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-neutral/40 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-[95%] sm:w-full max-w-4xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <h3 class="font-bold text-lg text-base-content">{{ $storeModalTitle }}</h3>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-0 overflow-hidden">
                <div class="max-h-[60vh] overflow-y-auto">
                    <table class="table table-sm table-pin-rows table-zebra w-full">
                        <thead>
                            <tr class="bg-base-200/50">
                                <th class="whitespace-nowrap">CustNo</th>
                                <th>Nama Toko</th>
                                <th>Distributor</th>
                                <th class="text-center whitespace-nowrap">Pilar</th>
                                <th class="text-right whitespace-nowrap">Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storeModalData as $store)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="font-medium text-base-content/80 whitespace-nowrap">{{ $store['custno'] }}</td>
                                    <td>{{ $store['custname'] }}</td>
                                    <td class="text-base-content/60">{{ $store['distributor_name'] }}</td>
                                    <td class="text-center whitespace-nowrap"><span class="badge badge-sm badge-outline">{{ $store['pilar'] ?? '-' }}</span></td>
                                    <td class="text-right font-medium text-success whitespace-nowrap">{{ $store['target'] ? number_format($store['target']) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-base-content/50">Tidak ada data toko.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($storeModalData) > 0)
                        <tfoot>
                            <tr class="bg-base-300 font-bold text-base-content border-t-2 border-base-content/10">
                                <td colspan="4" class="text-right px-4 py-3 uppercase tracking-wider text-xs">Total Subtotal Target:</td>
                                <td class="text-right px-4 py-3 text-success whitespace-nowrap">
                                    {{ number_format(collect($storeModalData)->sum('target')) }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end px-6 py-4 border-t border-base-300 bg-base-200/50">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal Export ESKA Options --}}
    <div x-data="{ open: @entangle('isExportEskaModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
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
                
                this.map = new maplibregl.Map({
                    container: 'store-map',
                    style: {
                        'version': 8,
                        'sources': {
                            'raster-tiles': {
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
                                'id': 'simple-tiles',
                                'type': 'raster',
                                'source': 'raster-tiles',
                                'minzoom': 0,
                                'maxzoom': 19
                            }
                        ]
                    },
                    center: [106.816666, -6.200000],
                    zoom: 10
                });

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
        <div class="modal" :class="{ 'modal-open': $wire.isMapModalOpen }">
            <div class="modal-box max-w-5xl p-0 overflow-hidden bg-base-100 rounded-2xl shadow-xl flex flex-col h-[90vh] md:h-[80vh] w-[95%] md:w-full">
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

            window.renderMapMarkers = function(map, markers, data) {
                // Wait for map to load
                if (!map || !map.isStyleLoaded()) {
                    setTimeout(() => window.renderMapMarkers(map, markers, data), 200);
                    return;
                }

                // clear old markers
                markers.forEach(m => m.remove());
                markers.length = 0; // empty array safely
                
                if (!data) return;

                let bounds = new maplibregl.LngLatBounds();
                let hasValidMarker = false;

                // Add Legend once
                if (!map.customLegend) {
                    class LegendControl {
                        onAdd(map) {
                            this._map = map;
                            this._container = document.createElement('div');
                            this._container.className = 'maplibregl-ctrl maplibregl-ctrl-group info legend bg-white p-3 rounded-xl shadow-lg border border-gray-200 text-xs';
                            this._container.style.margin = '10px';
                            this._container.innerHTML = `
                                <div class="font-bold mb-2 pb-1 border-b border-gray-200 text-gray-800 tracking-wide">Keterangan Warna</div>
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: #22c55e;"></div>
                                        <span class="text-gray-700 font-medium">Sudah Terjadwal (JKS)</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-200">
                                        <span class="font-bold text-gray-800">Pareto (Belum Terjadwal)</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: #3b82f6;"></div>
                                        <span class="text-gray-600 font-medium italic">Pilar 1 (RWO)</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: #8b5cf6;"></div>
                                        <span class="text-gray-600 font-medium italic">Pilar 2 (PNR)</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: #f97316;"></div>
                                        <span class="text-gray-600 font-medium italic">Pilar 3 (NGVO)</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-3 h-3 rounded-full shadow-sm" style="background-color: #6b7280;"></div>
                                        <span class="text-gray-600 font-medium italic">Lainnya</span>
                                    </div>
                                </div>
                            `;
                            return this._container;
                        }
                        onRemove() {
                            this._container.parentNode.removeChild(this._container);
                            this._map = undefined;
                        }
                    }
                    map.addControl(new LegendControl(), 'bottom-left');
                    map.customLegend = true;
                }
                
                function getPilarPriority(store) {
                    if (!store.pilar) return 1; // Lainnya
                    if (store.pilar.includes('1. RWO')) return 4; // Paling atas
                    if (store.pilar.includes('2. PNR')) return 3;
                    if (store.pilar.includes('3. NGVO')) return 2;
                    return 1;
                }

                // Gambar Pareto terlebih dahulu agar ada di lapisan bawah
                if (data.pareto) {
                    // Sortir berdasar prioritas (nilai terkecil digambar duluan, nilai terbesar digambar terakhir agar di atas)
                    data.pareto.sort((a, b) => getPilarPriority(a) - getPilarPriority(b));
                    
                    data.pareto.forEach(store => {
                        if(store.latitude && store.longitude) {
                            let lat = parseFloat(store.latitude);
                            let lng = parseFloat(store.longitude);
                            
                            // Abaikan koordinat 0,0 (di laut)
                            if (lat !== 0 && lng !== 0 && !isNaN(lat) && !isNaN(lng)) {
                            
                            let popupContent = `<b>${escHtml(store.custname)}</b><br>${escHtml(store.customer_address || '')}<br><span class="text-[0.625rem] text-gray-500 font-mono cursor-pointer hover:text-primary" onclick="window.open('https://www.google.com/maps/search/?api=1&query=${lat},${lng}', '_blank');" title="Buka di Google Maps">📍 ${lat}, ${lng}</span><div class="mt-2 flex flex-col gap-1">`;
                            if (store.pilar) {
                                popupContent += `<span class="badge badge-outline badge-sm font-bold border-gray-300 text-gray-600 shadow-sm w-max">Pilar: ${store.pilar}</span>`;
                            }
                            popupContent += `<span class="badge badge-ghost badge-sm border-none bg-base-300 text-base-content font-semibold shadow-sm w-max mt-1 mb-1">Belum Dijadwalkan</span>`;
                            
                            if (canEditMap) {
                                if (!data.isGlobal) {
                                    popupContent += `<button type="button" onclick="window.dispatchEvent(new CustomEvent('map-add-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}' } }))" class="btn btn-primary btn-xs text-white">Tambahkan Jadwal</button>`;
                                } else {
                                    let teamOptions = data.availableTeams.map(t => `<option value="${escHtml(t.kode_team)}">${escHtml(t.nama_team)}</option>`).join('');
                                    popupContent += `
                                        <div class="form-control w-full">
                                            <label class="label p-0 pb-1"><span class="label-text text-[0.625rem]">Tanggal</span></label>
                                            <input type="date" id="date_${store.custno}" class="input input-xs input-bordered w-full bg-white text-gray-900 border-gray-300" />
                                        </div>
                                        <div class="form-control w-full mt-1">
                                            <label class="label p-0 pb-1"><span class="label-text text-[0.625rem]">Team</span></label>
                                            <select id="team_${store.custno}" class="select select-xs select-bordered w-full bg-white text-gray-900 border-gray-300">
                                                ${teamOptions}
                                            </select>
                                        </div>
                                        <div id="popup_err_${store.custno}" style="display:none;color:#ef4444;font-size:0.75rem;margin-top:4px;">⚠️ Tanggal dan Team wajib diisi!</div>
                                        <button type="button" onclick="
                                            let d = document.getElementById('date_${store.custno}').value;
                                            let t = document.getElementById('team_${store.custno}').value;
                                            if(!d || !t) { 
                                                let errEl = document.getElementById('popup_err_${store.custno}');
                                                if(errEl) { errEl.style.display='block'; setTimeout(()=>errEl.style.display='none',3000); }
                                                return; 
                                            }
                                            window.dispatchEvent(new CustomEvent('global-map-add-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}', date: d, team: t } }))
                                        " class="btn btn-primary btn-xs text-white mt-1">Simpan Jadwal</button>
                                    `;
                                }
                            }
                            
                            popupContent += `</div>`;
                            
                            let markerColor = '#6b7280'; // grey
                            if (store.pilar) {
                                if (store.pilar.includes('1. RWO')) markerColor = '#3b82f6'; // blue
                                else if (store.pilar.includes('2. PNR')) markerColor = '#8b5cf6'; // violet
                                else if (store.pilar.includes('3. NGVO')) markerColor = '#f97316'; // orange
                            }
                            
                            let popup = new maplibregl.Popup({ offset: 25, maxWidth: '300px' }).setHTML(popupContent);
                            let m = new maplibregl.Marker({ color: markerColor })
                                .setLngLat([lng, lat])
                                .setPopup(popup)
                                .addTo(map);
                                
                            markers.push(m);
                            bounds.extend([lng, lat]);
                            hasValidMarker = true;
                            }
                        }
                    });
                }
                
                // Gambar Scheduled setelah Pareto agar ada di paling atas
                if (data.scheduled) {
                    // Sortir juga agar yg penting di atas (walau sama-sama hijau)
                    data.scheduled.sort((a, b) => getPilarPriority(a) - getPilarPriority(b));
                    
                    data.scheduled.forEach(store => {
                        if(store.latitude && store.longitude) {
                            let lat = parseFloat(store.latitude);
                            let lng = parseFloat(store.longitude);
                            
                            // Abaikan koordinat 0,0 (di laut)
                            if (lat !== 0 && lng !== 0 && !isNaN(lat) && !isNaN(lng)) {
                            
                            let popupHtml = `<b>${escHtml(store.custname)}</b><br>${escHtml(store.customer_address || '')}<br><span class="text-[0.625rem] text-gray-500 font-mono cursor-pointer hover:text-primary" onclick="window.open('https://www.google.com/maps/search/?api=1&query=${lat},${lng}', '_blank');" title="Buka di Google Maps">📍 ${lat}, ${lng}</span><div class="mt-2 flex flex-col gap-1">`;
                            if (store.tgl_format) {
                                popupHtml += `<span class="text-[0.6875rem] font-bold text-gray-700 flex items-center gap-1">🕒 ${escHtml(store.tgl_format)} (${escHtml(store.hari)}) - W${escHtml(store.minggu)}</span>`;
                                popupHtml += `<span class="text-[0.6875rem] font-bold text-gray-700 flex items-center gap-1">👤 ${escHtml(store.nama_team)}</span>`;
                            }
                            if (store.pilar) {
                                popupHtml += `<span class="badge badge-outline badge-sm font-bold border-gray-300 text-gray-600 shadow-sm mt-1 w-max">Pilar: ${store.pilar}</span>`;
                            }
                            popupHtml += `<span class="badge badge-primary badge-sm font-bold border-none text-white shadow-sm mt-1 w-max">Dijadwalkan JKS</span>`;
                            
                            if (canEditMap && data.isGlobal) {
                                let teamOptions = (data.availableTeams || []).map(t => `<option value="${escHtml(t.kode_team)}" ${t.kode_team == store.kode_team ? 'selected' : ''}>${escHtml(t.nama_team)}</option>`).join('');
                                popupHtml += `
                                    <div class="mt-2 border-t border-gray-200 pt-2">
                                        <div class="form-control w-full">
                                            <label class="label p-0 pb-1"><span class="label-text text-[0.625rem]">Pindah Tanggal</span></label>
                                            <input type="date" id="edit_date_${store.custno}" value="${store.tanggal_ymd}" class="input input-xs input-bordered w-full bg-white text-gray-900 border-gray-300" />
                                        </div>
                                        <div class="form-control w-full mt-1">
                                            <label class="label p-0 pb-1"><span class="label-text text-[0.625rem]">Pindah Team</span></label>
                                            <select id="edit_team_${store.custno}" class="select select-xs select-bordered w-full bg-white text-gray-900 border-gray-300">
                                                ${teamOptions}
                                            </select>
                                        </div>
                                        <div id="popup_err_edit_${store.custno}" style="display:none;color:#ef4444;font-size:0.75rem;margin-top:4px;width:100%;">⚠️ Tanggal dan Team wajib diisi!</div>
                                        <div class="flex gap-1 mt-2">
                                            <button type="button" onclick="
                                                let d = document.getElementById('edit_date_${store.custno}').value;
                                                let t = document.getElementById('edit_team_${store.custno}').value;
                                                if(!d || !t) { 
                                                    let errEl = document.getElementById('popup_err_edit_${store.custno}');
                                                    if(errEl) { errEl.style.display='block'; setTimeout(()=>errEl.style.display='none',3000); }
                                                    return; 
                                                }
                                                window.dispatchEvent(new CustomEvent('global-map-update-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}', oldDate: '${store.tanggal_ymd}', oldTeam: '${store.kode_team}', newDate: d, newTeam: t } }))
                                            " class="btn btn-warning btn-xs flex-1">Update</button>
                                            
                                            <button type="button" onclick="
                                                if(confirm('Yakin ingin menghapus jadwal ini?')) {
                                                    window.dispatchEvent(new CustomEvent('global-map-delete-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}', date: '${store.tanggal_ymd}', team: '${store.kode_team}' } }))
                                                }
                                            " class="btn btn-error btn-xs text-white px-2" title="Hapus Jadwal">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                `;
                            }
                            popupHtml += `</div>`;
                            
                            let popup = new maplibregl.Popup({ offset: 25, maxWidth: '300px' }).setHTML(popupHtml);
                            let m = new maplibregl.Marker({ color: '#22c55e' }) // green
                                .setLngLat([lng, lat])
                                .setPopup(popup)
                                .addTo(map);
                                
                            markers.push(m);
                            bounds.extend([lng, lat]);
                            hasValidMarker = true;
                            }
                        }
                    });
                }

                if (hasValidMarker) {
                    map.fitBounds(bounds, {padding: 50, duration: 1000});
                    setTimeout(() => map.resize(), 500); // Fix rendering after fitBounds
                }
            }

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
                            'sources': {
                                'raster-tiles': {
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
