<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">JKS Team Elite</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SISO vs ESKA</a>
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
                            <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Total Toko</h3>
                        </div>
                        <div class="flex items-baseline gap-1 flex-wrap mb-2 cursor-help" title="Format: Terjadwal (Semua Pilar) / Terjadwal (3 Pilar) / Target Pareto (3 Pilar)">
                            <span class="text-xl font-bold text-primary leading-none">{{ number_format($kpi['total_toko_all'] ?? 0) }}</span>
                            <span class="text-sm font-bold text-primary/70 leading-none">/ {{ number_format($kpi['total_toko']) }}</span>
                            <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_toko']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-primary h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctToko) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider">
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
                            <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Total Target</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-success leading-none">{{ number_format($kpi['total_target']) }}</span>
                            <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_target']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-success h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctTarget) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider">
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
                            <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Total RWO</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-info leading-none">{{ number_format($kpi['total_rwo']) }}</span>
                            <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_rwo']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-info h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctRwo) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider">
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
                            <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Total PNR</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-secondary leading-none">{{ number_format($kpi['total_pnr']) }}</span>
                            <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_pnr']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-secondary h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctPnr) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider">
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
                            <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Total NGVO</h3>
                        </div>
                        <div class="flex items-baseline gap-1.5 mb-2">
                            <span class="text-xl font-bold text-warning leading-none">{{ number_format($kpi['total_ngvo']) }}</span>
                            <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($paretoKpi['total_ngvo']) }}</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                            <div class="bg-warning h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctNgvo) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider">
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
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data JKS Team Elite</p>
                </div>
                
                {{-- Menggunakan flex-wrap agar barisan aksi jatuh secara responsif --}}
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    
                    {{-- Filter Team --}}
                    <div class="relative w-full sm:w-48" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" class="select select-sm select-bordered w-full rounded-xl bg-base-100 border-base-300 flex items-center justify-between px-3 text-left" @if(count($teams) <= 1) disabled @endif>
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
                             class="absolute z-50 w-80 mt-1 bg-base-100 border border-base-300 rounded-xl shadow-xl left-0 flex flex-col overflow-hidden">
                             
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
                                        : collect($teams)->filter(fn($t) => stripos($t->nama_team, $searchTeamFilter) !== false || stripos($t->kode_team, $searchTeamFilter) !== false);
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

                        <x-ui.action-button type="default" icon="map" label="Maps" class="text-info bg-info/10 hover:bg-info hover:text-white border-0 shadow-sm" wire:click="showGlobalMap" />

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
                        @endcanExport

                    </div>
                </div>
            </div>

            {{-- Body Card (Tabel Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap text-[11px] [&_th]:text-[11px] [&_td]:text-[11px]">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th>No</th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('tanggal')">
                                <div class="flex items-center gap-1">
                                    Tanggal
                                    @if($sortField === 'tanggal')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('nama_region')">
                                <div class="flex items-center gap-1">
                                    Region
                                    @if($sortField === 'nama_region')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('kode_team')">
                                <div class="flex items-center gap-1">
                                    Kode Team
                                    @if($sortField === 'kode_team')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('nama_team')">
                                <div class="flex items-center gap-1">
                                    Nama Team
                                    @if($sortField === 'nama_team')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th>Hari</th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('week_month')">
                                <div class="flex items-center justify-center gap-1">
                                    Week
                                    @if($sortField === 'week_month')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_toko')">
                                <div class="flex items-center justify-center gap-1">
                                    Total Toko
                                    @if($sortField === 'total_toko')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_rwo')">
                                <div class="flex items-center justify-center gap-1">
                                    Total RWO
                                    @if($sortField === 'total_rwo')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_pnr')">
                                <div class="flex items-center justify-center gap-1">
                                    Total PNR
                                    @if($sortField === 'total_pnr')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center cursor-pointer hover:bg-base-200 transition-colors select-none" wire:click="sortBy('total_ngvo')">
                                <div class="flex items-center justify-center gap-1">
                                    Total NGVO
                                    @if($sortField === 'total_ngvo')
                                        <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                    @endif
                                </div>
                            </th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(empty($filterTeam) || empty($filterStartDate) || empty($filterEndDate))
                            <tr>
                                <td colspan="13" class="text-center py-8 text-base-content/50">
                                    <x-heroicon-o-funnel class="w-12 h-12 mx-auto mb-3 opacity-30" />
                                    Silakan pilih **Team** dan **Range Tanggal** terlebih dahulu untuk menampilkan data.
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
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}')" class="badge {{ $badgeClass }} font-bold cursor-pointer transition-colors">
                                            {{ $record->total_toko }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}', 'RWO')" class="badge badge-info badge-outline font-bold cursor-pointer hover:bg-info hover:text-white transition-colors">
                                            {{ $record->total_rwo ?? 0 }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}', 'PNR')" class="badge badge-secondary badge-outline font-bold cursor-pointer hover:bg-secondary hover:text-white transition-colors">
                                            {{ $record->total_pnr ?? 0 }}
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="showStoreDetails('{{ $record->tanggal }}', '{{ $record->kode_team }}', 'NGVO')" class="badge badge-warning badge-outline font-bold cursor-pointer hover:bg-warning hover:text-white transition-colors">
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
                                    <td colspan="13" class="text-center py-4 text-base-content/50">Tidak ada data ditemukan untuk kriteria tersebut.</td>
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

    {{-- Modal Form (Create/Edit) --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-5xl max-h-[90vh] overflow-hidden ring-1 ring-base-content/5 flex flex-col">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Grup JKS' : 'Tambah JKS Multiple Customer' }}</h3>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="flex-1 overflow-hidden flex flex-col md:flex-row bg-base-100">
                {{-- Kiri: Form Input & Search --}}
                <div class="w-full md:w-1/2 p-6 border-r border-base-300 overflow-y-auto">
                    @if($formError)
                        <div class="alert alert-error shadow-sm rounded-xl border-none bg-error/10 text-error mb-4 flex items-start gap-3">
                            <x-heroicon-s-x-circle class="w-5 h-5 shrink-0 mt-0.5" />
                            <div class="text-sm font-medium">{{ $formError }}</div>
                        </div>
                    @endif
                    <form id="form-jks" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Tanggal --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal</label>
                                <input wire:model.blur="tanggal" type="date" class="input input-sm input-bordered w-full rounded-xl">
                                @error('tanggal') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Team (fsalesman) --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Team</label>
                                <select wire:model.live="selectedTeamCode" class="select select-sm select-bordered w-full rounded-xl">
                                    <option value="">-- Pilih Team --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->kode_team }}">{{ $team->nama_team }}</option>
                                    @endforeach
                                </select>
                                @error('selectedTeamCode') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr class="my-4 border-base-300">

                        {{-- Search Distributor --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Distributor (Opsional)</label>
                            @if($selectedDistributorCode)
                                <div class="flex items-center gap-2 p-2 border border-primary/30 bg-primary/5 rounded-xl text-sm">
                                    <div class="flex-1 font-semibold text-primary">{{ $selectedDistributorCode }} - {{ $searchDistributor }}</div>
                                    <button type="button" wire:click="clearDistributor" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error hover:text-white">
                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @else
                                <input wire:model.live.debounce.300ms="searchDistributor" type="text" placeholder="Ketik nama atau kode distributor..." class="input input-sm input-bordered w-full rounded-xl">
                                
                                @if(count($distributorOptions) > 0)
                                    <ul class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-1">
                                        @foreach($distributorOptions as $dist)
                                            <li>
                                                <button type="button" wire:click="selectDistributor('{{ $dist->distributor_code }}', '{{ addslashes($dist->distributor_name) }}')" class="w-full text-left px-3 py-2 text-sm hover:bg-base-200 rounded-lg">
                                                    <div class="font-bold">{{ $dist->distributor_code }}</div>
                                                    <div class="text-xs opacity-70">{{ $dist->distributor_name }}</div>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>

                        {{-- Search Customer --}}
                        <div class="space-y-1.5 relative">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Customer</label>
                            <div class="relative">
                                <input wire:model.live.debounce.500ms="searchCustomer" type="text" placeholder="Ketik kode, nama, atau alamat..." class="input input-sm input-bordered w-full rounded-xl pr-10">
                                <div wire:loading wire:target="searchCustomer" class="absolute right-3 top-2">
                                    <span class="loading loading-spinner loading-xs text-primary"></span>
                                </div>
                            </div>
                            
                            @if(count($customerOptions) > 0)
                                <ul class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-1">
                                    @foreach($customerOptions as $cust)
                                        <li>
                                            <div class="w-full text-left px-3 py-2 hover:bg-base-200 rounded-lg flex justify-between items-center group cursor-default">
                                                <div class="flex-1">
                                                    <div class="font-bold text-sm">{{ $cust->custno }} - {{ $cust->custname }}</div>
                                                    <div class="text-xs opacity-70 truncate">{{ $cust->distributor_name }} ({{ $cust->distributor_code }})</div>
                                                    <div class="text-[10px] opacity-50 truncate">{{ $cust->addres }}</div>
                                                </div>
                                                <button type="button" wire:click="addCustomerToCart('{{ $cust->custno }}')" class="btn btn-xs btn-primary btn-square opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <x-heroicon-s-plus class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif(strlen($searchCustomer) >= 3)
                                <div class="absolute z-10 w-full mt-1 bg-base-100 border border-base-300 rounded-xl shadow-lg p-3 text-center text-xs text-base-content/50">
                                    <span wire:loading.remove wire:target="searchCustomer">Tidak ditemukan customer yang sesuai.</span>
                                    <span wire:loading wire:target="searchCustomer">Mencari...</span>
                                </div>
                            @endif
                        </div>

                        @error('selectedCustomers') 
                            <div class="alert alert-error bg-error/10 text-error text-xs p-2 rounded-lg mt-4 border-none">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                {{ $message }}
                            </div>
                        @enderror
                    </form>
                </div>

                {{-- Kanan: Daftar Customer (Cart) --}}
                <div class="w-full md:w-1/2 p-0 flex flex-col bg-base-200/20 overflow-y-auto">
                    <div class="p-4 border-b border-base-300 flex justify-between items-center bg-base-100 sticky top-0 z-10">
                        <h4 class="font-bold text-sm uppercase tracking-wide">Daftar Customer Terpilih</h4>
                        <span class="badge badge-primary">{{ count($selectedCustomers) }} Toko</span>
                    </div>
                    
                    <div class="p-4 flex-1">
                        @if(count($selectedCustomers) == 0)
                            <div class="h-full flex flex-col items-center justify-center text-base-content/30 space-y-3">
                                <x-heroicon-o-shopping-bag class="w-16 h-16" />
                                <p class="text-sm">Belum ada customer yang dipilih.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($selectedCustomers as $idx => $cartItem)
                                    <div class="bg-base-100 border border-base-300 rounded-xl p-3 shadow-sm flex items-start gap-3 relative">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ $idx + 1 }}
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <div class="font-bold text-sm">{{ $cartItem['custno'] }} - {{ $cartItem['custname'] }}</div>
                                            <div class="text-xs text-base-content/70 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                                                <span class="flex items-center gap-1"><x-heroicon-s-building-storefront class="w-3 h-3"/> {{ $cartItem['distributor_code'] }}</span>
                                                <span class="flex items-center gap-1"><x-heroicon-s-map-pin class="w-3 h-3"/> {{ $cartItem['nama_area'] }}, {{ $cartItem['nama_region'] }}</span>
                                            </div>
                                            <div class="text-[10px] text-base-content/50 mt-1 truncate">{{ $cartItem['addres'] }}</div>
                                        </div>
                                        <button type="button" wire:click="removeCustomerFromCart('{{ $cartItem['custno'] }}')" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error hover:text-white shrink-0">
                                            <x-heroicon-s-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50 mt-auto">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case">Batal</button>
                <button wire:click="save" type="button" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                    <span wire:loading.remove wire:target="save">Simpan Daftar ({{ count($selectedCustomers) }})</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Import Excel --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <h3 class="font-bold text-lg text-base-content">Import Data Excel</h3>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            <form wire:submit.prevent="import">
                <div class="p-6">
                    @if($importStep === 1)
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Start Date</label>
                                <input type="date" wire:model="importStartDate" class="input input-bordered input-sm w-full rounded-xl">
                                @error('importStartDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">End Date</label>
                                <input type="date" wire:model="importEndDate" class="input input-bordered input-sm w-full rounded-xl">
                                @error('importEndDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-1.5 mb-4">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Import Method</label>
                            <select wire:model="importMethod" class="select select-bordered select-sm w-full rounded-xl">
                                <option value="full_sync">Full Sync (Hapus & Timpa Data Terkait)</option>
                                <option value="partial_update">Partial Update (Hanya Tambah/Update Data Baru)</option>
                            </select>
                            @error('importMethod') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">File Excel (xls, xlsx, csv)</label>
                            <input type="file" wire:model="excel_file" class="file-input file-input-bordered file-input-sm w-full rounded-xl" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            @error('excel_file') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            
                            <div wire:loading wire:target="excel_file" class="text-xs text-info mt-2">Mengunggah...</div>
                        </div>

                        @if(count($importErrors) > 0)
                            <div class="mt-4 bg-error/10 border border-error/20 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-error shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="font-bold text-sm text-error mb-1">Import Gagal</h4>
                                        <p class="text-xs text-base-content/70 mb-3">
                                            Terdapat <strong>{{ count($importErrors) }}</strong> baris data yang bermasalah (kode tidak ditemukan di database atau kosong). Import dibatalkan untuk mencegah data tidak lengkap.
                                        </p>
                                        <button type="button" wire:click="downloadErrorLog" class="btn btn-sm btn-error text-white rounded-lg text-xs gap-2 shadow-sm">
                                            <x-heroicon-s-document-text class="w-4 h-4" />
                                            Download Log Error (.txt)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if($importStep === 2)
                        <div class="mb-5 bg-warning/10 p-4 rounded-xl border border-warning/20">
                            <h4 class="font-bold text-sm text-warning-content mb-3 flex items-center gap-2">
                                <x-heroicon-s-eye class="w-5 h-5 text-warning" />
                                Preview Import
                            </h4>
                            
                            <div class="grid grid-cols-2 gap-y-3 gap-x-6 text-sm text-base-content/80 mb-4">
                                <div>
                                    <span class="block text-xs font-semibold text-base-content/50 uppercase tracking-wider">Method</span>
                                    <span class="font-medium text-base-content">{{ $importMethod === 'full_sync' ? 'Full Sync' : 'Partial Update' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-semibold text-base-content/50 uppercase tracking-wider">Date Range</span>
                                    <span class="font-medium text-base-content">{{ Carbon\Carbon::parse($importStartDate)->format('d M Y') }} - {{ Carbon\Carbon::parse($importEndDate)->format('d M Y') }}</span>
                                </div>
                            </div>

                            <div class="bg-base-100 rounded-lg p-4 border border-base-200 grid grid-cols-3 gap-4 text-center divide-x divide-base-200 mb-4">
                                <div>
                                    <span class="block text-xs text-base-content/50 mb-1">Upload Rows</span>
                                    <span class="text-xl font-bold text-primary">{{ number_format($previewTotalRows) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-base-content/50 mb-1">Affected Teams</span>
                                    <span class="text-xl font-bold text-secondary">{{ number_format($previewTotalTeams) }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-base-content/50 mb-1">Existing DB Rows</span>
                                    <span class="text-xl font-bold text-neutral">{{ number_format($previewExistingRows) }}</span>
                                </div>
                            </div>

                            @if($importMethod === 'full_sync')
                                <div class="flex items-start gap-2 text-xs text-error font-medium bg-error/10 p-3 rounded-lg">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 shrink-0" />
                                    <p><strong>Warning:</strong> Existing schedule data in selected scope will be replaced!</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <div>
                        @if($importStep === 1)
                            <button type="button" wire:click="downloadTemplate" class="btn btn-ghost rounded-xl normal-case text-info hover:bg-info/10">
                                <x-heroicon-s-arrow-down-tray class="w-4 h-4 mr-1" />
                                Download Template
                            </button>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        @if($importStep === 1)
                            <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case">Batal</button>
                            <button type="button" wire:click="previewImport" class="btn btn-primary rounded-xl px-8 normal-case text-white" wire:loading.attr="disabled" wire:target="previewImport, excel_file">
                                <span wire:loading.remove wire:target="previewImport">Preview Import</span>
                                <span wire:loading wire:target="previewImport" class="loading loading-spinner loading-xs"></span>
                            </button>
                        @endif

                        @if($importStep === 2)
                            <button type="button" wire:click="$set('importStep', 1)" class="btn btn-ghost rounded-xl normal-case">Kembali</button>
                            <button type="button" wire:click="executeImport" class="btn btn-success rounded-xl px-8 normal-case text-white" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="executeImport">Execute Import</span>
                                <span wire:loading wire:target="executeImport" class="loading loading-spinner loading-xs"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

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
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl text-white">
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
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

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
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

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

    <!-- Map Modal -->
    <div x-data="{
            map: null,
            markers: [],
            initMap() {
                if (this.map) {
                    this.map.remove();
                }
                this.map = L.map('store-map').setView([-6.200000, 106.816666], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(this.map);
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
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-base-200 bg-base-100/50 backdrop-blur z-10 shrink-0">
                    <h3 class="text-lg font-black text-base-content tracking-tight flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center text-info shadow-inner">
                            <x-heroicon-s-map class="w-5 h-5" />
                        </div>
                        {{ $mapModalTitle }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="initMap(); updateMarkers($wire.mapModalData);" class="btn btn-info btn-sm text-white rounded-xl" title="Klik jika peta tidak sejajar/blank">
                            <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh
                        </button>
                        <button wire:click="$set('isMapModalOpen', false)" class="btn btn-ghost btn-sm btn-square rounded-xl hover:bg-error/10 hover:text-error transition-colors">
                            <x-heroicon-s-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <!-- Content -->
                <div class="flex-1 w-full bg-base-200 relative">
                    <div id="store-map" wire:ignore style="height: 100%; width: 100%; z-index: 1;"></div>
                </div>
            </div>
            <div class="modal-backdrop bg-base-300/80 backdrop-blur-sm" wire:click="$set('isMapModalOpen', false)"></div>
        </div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            window.renderMapMarkers = function(map, markers, data) {
                // clear old markers
                markers.forEach(m => map.removeLayer(m));
                markers.length = 0; // empty array safely
                
                if (!data) return;

                let bounds = L.latLngBounds();
                let hasValidMarker = false;

                let iconBaseUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-';
                let iconShadowUrl = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png';
                
                function getIcon(color) {
                    return new L.Icon({
                        iconUrl: iconBaseUrl + color + '.png',
                        shadowUrl: iconShadowUrl,
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });
                }

                let icons = {
                    'blue': getIcon('blue'),
                    'green': getIcon('green'),
                    'orange': getIcon('orange'),
                    'red': getIcon('red'),
                    'violet': getIcon('violet'),
                    'grey': getIcon('grey'),
                    'black': getIcon('black'),
                    'gold': getIcon('gold'),
                    'yellow': getIcon('yellow')
                };
                
                // Add Legend once
                if (!map.customLegend) {
                    let legend = L.control({position: 'bottomleft'});
                    legend.onAdd = function (map) {
                        let div = L.DomUtil.create('div', 'info legend bg-white p-3 rounded-xl shadow-lg border border-gray-200 text-xs z-[1000] relative');
                        div.innerHTML = `
                            <div class="font-bold mb-2 pb-1 border-b border-gray-200 text-gray-800 tracking-wide">Keterangan Warna</div>
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center gap-2"><img src="${iconBaseUrl}green.png" class="h-4"> <span class="text-gray-700 font-medium">Sudah Terjadwal (JKS)</span></div>
                                <div class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-200"><span class="font-bold text-gray-800">Pareto (Belum Terjadwal)</span></div>
                                <div class="flex items-center gap-2 mt-1"><img src="${iconBaseUrl}blue.png" class="h-4 opacity-80"> <span class="text-gray-600 font-medium italic">Pilar 1 (RWO)</span></div>
                                <div class="flex items-center gap-2 mt-1"><img src="${iconBaseUrl}violet.png" class="h-4 opacity-80"> <span class="text-gray-600 font-medium italic">Pilar 2 (PNR)</span></div>
                                <div class="flex items-center gap-2 mt-1"><img src="${iconBaseUrl}orange.png" class="h-4 opacity-80"> <span class="text-gray-600 font-medium italic">Pilar 3 (NGVO)</span></div>
                                <div class="flex items-center gap-2 mt-1"><img src="${iconBaseUrl}grey.png" class="h-4 opacity-80"> <span class="text-gray-600 font-medium italic">Lainnya</span></div>
                            </div>
                        `;
                        return div;
                    };
                    legend.addTo(map);
                    map.customLegend = legend;
                }
                
                if (data.scheduled) {
                    data.scheduled.forEach(store => {
                        if(store.latitude && store.longitude) {
                            let lat = parseFloat(store.latitude);
                            let lng = parseFloat(store.longitude);
                            
                            let popupHtml = `<b>${store.custname}</b><br>${store.customer_address || ''}<br><span class="text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary" onclick="window.open('https://www.google.com/maps/search/?api=1&query=${lat},${lng}', '_blank');" title="Buka di Google Maps">📍 ${lat}, ${lng}</span><div class="mt-2 flex flex-col gap-1">`;
                            if (store.tgl_format) {
                                popupHtml += `<span class="text-[11px] font-bold text-gray-700 flex items-center gap-1">🕒 ${store.tgl_format} (${store.hari}) - W${store.minggu}</span>`;
                                popupHtml += `<span class="text-[11px] font-bold text-gray-700 flex items-center gap-1">👤 ${store.nama_team}</span>`;
                            }
                            if (store.pilar) {
                                popupHtml += `<span class="badge badge-outline badge-sm font-bold border-gray-300 text-gray-600 shadow-sm mt-1 w-max">Pilar: ${store.pilar}</span>`;
                            }
                            popupHtml += `<span class="badge badge-primary badge-sm font-bold border-none text-white shadow-sm mt-1 w-max">Dijadwalkan JKS</span>`;
                            
                            if (data.isGlobal) {
                                let teamOptions = (data.availableTeams || []).map(t => `<option value="${t.kode_team}" ${t.kode_team == store.kode_team ? 'selected' : ''}>${t.nama_team}</option>`).join('');
                                popupHtml += `
                                    <div class="mt-2 border-t border-gray-200 pt-2">
                                        <div class="form-control w-full">
                                            <label class="label p-0 pb-1"><span class="label-text text-[10px]">Pindah Tanggal</span></label>
                                            <input type="date" id="edit_date_${store.custno}" value="${store.tanggal_ymd}" class="input input-xs input-bordered w-full bg-white text-gray-900 border-gray-300" />
                                        </div>
                                        <div class="form-control w-full mt-1">
                                            <label class="label p-0 pb-1"><span class="label-text text-[10px]">Pindah Team</span></label>
                                            <select id="edit_team_${store.custno}" class="select select-xs select-bordered w-full bg-white text-gray-900 border-gray-300">
                                                ${teamOptions}
                                            </select>
                                        </div>
                                        <div class="flex gap-1 mt-2">
                                            <button type="button" onclick="
                                                let d = document.getElementById('edit_date_${store.custno}').value;
                                                let t = document.getElementById('edit_team_${store.custno}').value;
                                                if(!d || !t) { alert('Tanggal dan Team harus diisi!'); return; }
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
                            
                            let markerIcon = icons['green'];
                            let m = L.marker([lat, lng], {icon: markerIcon}).addTo(map).bindPopup(popupHtml);
                            markers.push(m);
                            bounds.extend([lat, lng]);
                            hasValidMarker = true;
                        }
                    });
                }

                if (data.pareto) {
                    data.pareto.forEach(store => {
                        if(store.latitude && store.longitude) {
                            let lat = parseFloat(store.latitude);
                            let lng = parseFloat(store.longitude);
                            
                            let popupContent = `<b>${store.custname}</b><br>${store.customer_address || ''}<br><span class="text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary" onclick="window.open('https://www.google.com/maps/search/?api=1&query=${lat},${lng}', '_blank');" title="Buka di Google Maps">📍 ${lat}, ${lng}</span><div class="mt-2 flex flex-col gap-1">`;
                            if (store.pilar) {
                                popupContent += `<span class="badge badge-outline badge-sm font-bold border-gray-300 text-gray-600 shadow-sm w-max">Pilar: ${store.pilar}</span>`;
                            }
                            popupContent += `<span class="badge badge-ghost badge-sm border-none bg-base-300 text-base-content font-semibold shadow-sm w-max mt-1 mb-1">Belum Dijadwalkan</span>`;
                            
                            if (!data.isGlobal) {
                                popupContent += `<button type="button" onclick="window.dispatchEvent(new CustomEvent('map-add-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}' } }))" class="btn btn-primary btn-xs text-white">Tambahkan Jadwal</button>`;
                            } else {
                                let teamOptions = data.availableTeams.map(t => `<option value="${t.kode_team}">${t.nama_team}</option>`).join('');
                                popupContent += `
                                    <div class="form-control w-full">
                                        <label class="label p-0 pb-1"><span class="label-text text-[10px]">Tanggal</span></label>
                                        <input type="date" id="date_${store.custno}" class="input input-xs input-bordered w-full bg-white text-gray-900 border-gray-300" />
                                    </div>
                                    <div class="form-control w-full mt-1">
                                        <label class="label p-0 pb-1"><span class="label-text text-[10px]">Team</span></label>
                                        <select id="team_${store.custno}" class="select select-xs select-bordered w-full bg-white text-gray-900 border-gray-300">
                                            ${teamOptions}
                                        </select>
                                    </div>
                                    <button type="button" onclick="
                                        let d = document.getElementById('date_${store.custno}').value;
                                        let t = document.getElementById('team_${store.custno}').value;
                                        if(!d || !t) { alert('Tanggal dan Team harus diisi!'); return; }
                                        window.dispatchEvent(new CustomEvent('global-map-add-store', { detail: { custno: '${store.custno}', dist: '${store.distributor_code}', date: d, team: t } }))
                                    " class="btn btn-primary btn-xs text-white mt-1">Simpan Jadwal</button>
                                `;
                            }
                            
                            popupContent += `</div>`;
                            
                            let paretoIcon = icons['grey'];
                            if (store.pilar) {
                                if (store.pilar.includes('1. RWO')) paretoIcon = icons['blue'];
                                else if (store.pilar.includes('2. PNR')) paretoIcon = icons['violet'];
                                else if (store.pilar.includes('3. NGVO')) paretoIcon = icons['orange'];
                            }
                            
                            let m = L.marker([lat, lng], {icon: paretoIcon}).addTo(map).bindPopup(popupContent);
                            markers.push(m);
                            bounds.extend([lat, lng]);
                            hasValidMarker = true;
                        }
                    });
                }

                if (hasValidMarker) {
                    map.fitBounds(bounds, {padding: [50, 50]});
                }
            }
        </script>
    @endpush
</div>
