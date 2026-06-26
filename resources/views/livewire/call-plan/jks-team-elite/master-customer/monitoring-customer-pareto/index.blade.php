<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Monitoring Customer Pareto</x-slot>

    @php
        $getSortIcon = function($column) use ($sortColumn, $sortDirection) {
            if ($sortColumn !== $column) return 'chevron-up-down';
            return $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down';
        };
        $getSortClass = function($column) use ($sortColumn) {
            return $sortColumn === $column ? 'w-4 h-4 text-primary' : 'w-4 h-4 text-base-content/30';
        };
    @endphp

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.master-customer') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.master-customer.monitoring-customer-pareto') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Monitoring Customer Pareto</a>
        </div>
    </div>

    @php
        $stats = $this->kpiStats;
        
        $kpiTotalToko = $stats->total_toko ?? 0;
        $kpiTotalPlan = $stats->total_plan ?? 0;
        $kpiTotalGap = max(0, $kpiTotalToko - $kpiTotalPlan);
        $kpiTotalPercent = $kpiTotalToko > 0 ? round(($kpiTotalPlan / $kpiTotalToko) * 100) : 0;

        $kpiRwoTarget = $stats->total_rwo ?? 0;
        $kpiRwoActual = $stats->plan_rwo ?? 0;
        $kpiRwoGap = max(0, $kpiRwoTarget - $kpiRwoActual);
        $kpiRwoPercent = $kpiRwoTarget > 0 ? round(($kpiRwoActual / $kpiRwoTarget) * 100) : 0;

        $kpiPnrTarget = $stats->total_pnr ?? 0;
        $kpiPnrActual = $stats->plan_pnr ?? 0;
        $kpiPnrGap = max(0, $kpiPnrTarget - $kpiPnrActual);
        $kpiPnrPercent = $kpiPnrTarget > 0 ? round(($kpiPnrActual / $kpiPnrTarget) * 100) : 0;

        $kpiNgvoTarget = $stats->total_ngvo ?? 0;
        $kpiNgvoActual = $stats->plan_ngvo ?? 0;
        $kpiNgvoGap = max(0, $kpiNgvoTarget - $kpiNgvoActual);
        $kpiNgvoPercent = $kpiNgvoTarget > 0 ? round(($kpiNgvoActual / $kpiNgvoTarget) * 100) : 0;
    @endphp

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 shrink-0">
        {{-- KPI: Total Toko --}}
        <div class="bg-base-100 border border-base-300 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
            <x-heroicon-s-building-storefront class="absolute -right-4 -top-2 w-20 h-20 text-purple-50 opacity-60 pointer-events-none" />
            <div>
                <div class="flex items-center gap-2 mb-1 relative z-10">
                    <div class="w-4 h-4 rounded bg-purple-100 text-purple-600 flex items-center justify-center">
                        <x-heroicon-s-building-storefront class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">Total Toko</span>
                </div>
                <div class="text-xl font-extrabold text-purple-600 relative z-10">
                    {{ number_format($kpiTotalPlan) }} <span class="text-xs text-base-content/40 font-semibold">/ {{ number_format($kpiTotalToko) }}</span>
                </div>
            </div>
            <div class="mt-3 relative z-10">
                <div class="w-full h-1 bg-base-200 rounded-full mb-1.5">
                    <div class="h-1 bg-purple-600 rounded-full" style="width: {{ $kpiTotalPercent }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] font-bold">
                    <span class="text-base-content/60">GAP: <span class="{{ $kpiTotalGap > 0 ? 'text-error' : 'text-success' }}">{{ number_format($kpiTotalGap) }}</span></span>
                    <span class="text-purple-600">{{ $kpiTotalPercent }}%</span>
                </div>
            </div>
        </div>

        {{-- KPI: Visit 1. RWO --}}
        <div class="bg-base-100 border border-base-300 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
            <x-heroicon-s-shopping-bag class="absolute -right-4 -top-2 w-20 h-20 text-sky-50 opacity-60 pointer-events-none" />
            <div>
                <div class="flex items-center gap-2 mb-1 relative z-10">
                    <div class="w-4 h-4 rounded bg-sky-100 text-sky-600 flex items-center justify-center">
                        <x-heroicon-s-shopping-bag class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">1. RWO</span>
                </div>
                <div class="text-xl font-extrabold text-sky-600 relative z-10">
                    {{ number_format($kpiRwoActual) }} <span class="text-xs text-base-content/40 font-semibold">/ {{ number_format($kpiRwoTarget) }}</span>
                </div>
            </div>
            <div class="mt-3 relative z-10">
                <div class="w-full h-1 bg-base-200 rounded-full mb-1.5">
                    <div class="h-1 bg-sky-600 rounded-full" style="width: {{ $kpiRwoPercent }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] font-bold">
                    <span class="text-base-content/60">GAP: <span class="{{ $kpiRwoGap > 0 ? 'text-error' : 'text-success' }}">{{ number_format($kpiRwoGap) }}</span></span>
                    <span class="text-sky-600">{{ $kpiRwoPercent }}%</span>
                </div>
            </div>
        </div>

        {{-- KPI: Visit 2. PNR --}}
        <div class="bg-base-100 border border-base-300 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
            <x-heroicon-s-archive-box class="absolute -right-4 -top-2 w-20 h-20 text-slate-100 opacity-60 pointer-events-none" />
            <div>
                <div class="flex items-center gap-2 mb-1 relative z-10">
                    <div class="w-4 h-4 rounded bg-slate-100 text-slate-600 flex items-center justify-center">
                        <x-heroicon-s-archive-box class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">2. PNR</span>
                </div>
                <div class="text-xl font-extrabold text-slate-600 relative z-10">
                    {{ number_format($kpiPnrActual) }} <span class="text-xs text-base-content/40 font-semibold">/ {{ number_format($kpiPnrTarget) }}</span>
                </div>
            </div>
            <div class="mt-3 relative z-10">
                <div class="w-full h-1 bg-base-200 rounded-full mb-1.5">
                    <div class="h-1 bg-slate-600 rounded-full" style="width: {{ $kpiPnrPercent }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] font-bold">
                    <span class="text-base-content/60">GAP: <span class="{{ $kpiPnrGap > 0 ? 'text-error' : 'text-success' }}">{{ number_format($kpiPnrGap) }}</span></span>
                    <span class="text-slate-600">{{ $kpiPnrPercent }}%</span>
                </div>
            </div>
        </div>

        {{-- KPI: Visit 3. NGVO --}}
        <div class="bg-base-100 border border-base-300 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
            <x-heroicon-s-star class="absolute -right-4 -top-2 w-20 h-20 text-orange-50 opacity-60 pointer-events-none" />
            <div>
                <div class="flex items-center gap-2 mb-1 relative z-10">
                    <div class="w-4 h-4 rounded bg-orange-100 text-orange-500 flex items-center justify-center">
                        <x-heroicon-s-star class="w-3 h-3" />
                    </div>
                    <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">3. NGVO</span>
                </div>
                <div class="text-xl font-extrabold text-orange-500 relative z-10">
                    {{ number_format($kpiNgvoActual) }} <span class="text-xs text-base-content/40 font-semibold">/ {{ number_format($kpiNgvoTarget) }}</span>
                </div>
            </div>
            <div class="mt-3 relative z-10">
                <div class="w-full h-1 bg-base-200 rounded-full mb-1.5">
                    <div class="h-1 bg-orange-500 rounded-full" style="width: {{ $kpiNgvoPercent }}%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] font-bold">
                    <span class="text-base-content/60">GAP: <span class="{{ $kpiNgvoGap > 0 ? 'text-error' : 'text-success' }}">{{ number_format($kpiNgvoGap) }}</span></span>
                    <span class="text-orange-500">{{ $kpiNgvoPercent }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col 2xl:flex-row justify-between items-start 2xl:items-center gap-4 bg-base-100">
            <div class="shrink-0 w-full 2xl:w-auto">
                <h2 class="text-base md:text-lg font-bold">Monitoring Customer Pareto</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Memantau customer pareto apakah sudah masuk ke dalam plan visit</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start 2xl:justify-end gap-2 md:gap-3 w-full 2xl:w-auto">
                {{-- Search --}}
                <div class="relative w-full sm:w-64">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/50" />
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode/Nama/Alamat/Pilar..." 
                           class="input input-sm input-bordered w-full pl-9 focus:input-primary bg-base-200/50 hover:bg-base-100 transition-colors">
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- Pilar Filter --}}
                    <select wire:model.live="filterPilar" class="select select-sm select-bordered w-full sm:w-32 bg-base-200/50 hover:bg-base-100 focus:select-primary transition-colors font-medium text-xs" title="Filter Pilar">
                        <option value="">Semua Pilar</option>
                        <option value="1. RWO">1. RWO</option>
                        <option value="2. PNR">2. PNR</option>
                        <option value="3. NGVO">3. NGVO</option>
                    </select>

                    {{-- Status Filter --}}
                    <select wire:model.live="filterStatus" class="select select-sm select-bordered w-full sm:w-36 bg-base-200/50 hover:bg-base-100 focus:select-primary transition-colors font-medium text-xs" title="Filter Status">
                        <option value="">Semua Status</option>
                        <option value="Masuk Plan">Masuk Plan</option>
                        <option value="Belum Diplan">Belum Diplan</option>
                    </select>

                    {{-- Date Range --}}
                    <div class="flex items-center gap-1 bg-base-200/50 hover:bg-base-100 transition-colors px-1 py-0.5 rounded-lg border border-base-300">
                        <input wire:model.live="startDate" type="date" class="input input-sm input-ghost w-[120px] focus:outline-none focus:ring-0 px-2 font-medium text-xs" title="Mulai Tanggal">
                        <span class="text-xs font-bold text-base-content/30">-</span>
                        <input wire:model.live="endDate" type="date" class="input input-sm input-ghost w-[120px] focus:outline-none focus:ring-0 px-2 font-medium text-xs" title="Sampai Tanggal">
                    </div>
                </div>
                
                <div class="flex items-center gap-2 border-l border-base-300 pl-2 md:pl-3 ml-1 md:ml-0">
                    {{-- Filter Modal --}}
                    <x-ui.action-button type="filter" wire:click="openFilterModal" class="relative shrink-0">
                        @if($filterRegion || $filterArea || $filterSupervisor || $filterDistributor || $filterStatus || $filterPilar || $startDate || $endDate)
                            <div class="badge badge-primary badge-xs absolute -top-1 -right-1 ring-2 ring-base-100"></div>
                        @endif
                    </x-ui.action-button>

                    {{-- Export --}}
                    @canExport('call-plan.jks-team-elite.master-customer')
                    <x-ui.action-button type="export" wire:click="export" class="relative shrink-0" title="Export Excel" />
                    @endcanExport
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative group">
            {{-- Loading Overlay --}}
            <div wire:loading.flex class="absolute inset-0 z-50 bg-base-100/60 backdrop-blur-sm flex-col items-center justify-center opacity-0 transition-opacity" style="animation: fadeIn 0.3s forwards">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <span class="mt-3 font-semibold text-sm text-base-content/70">Memuat data...</span>
            </div>
            
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th wire:click="sortBy('md.region_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Region</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('md.region_name')" class="{{ $getSortClass('md.region_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('md.area_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Area</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('md.area_name')" class="{{ $getSortClass('md.area_name') }}" />
                            </div>
                        </th>

                        <th wire:click="sortBy('f.SLSNAME')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Supervisor</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('f.SLSNAME')" class="{{ $getSortClass('f.SLSNAME') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('md.distributor_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Distributor</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('md.distributor_name')" class="{{ $getSortClass('md.distributor_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_code_prc')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Customer Code</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.customer_code_prc')" class="{{ $getSortClass('l.customer_code_prc') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.uniq_kd')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Uniq Kd</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.uniq_kd')" class="{{ $getSortClass('l.uniq_kd') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Customer Name</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.customer_name')" class="{{ $getSortClass('l.customer_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_address')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Address</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.customer_address')" class="{{ $getSortClass('l.customer_address') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.pilar')" class="cursor-pointer hover:bg-base-200 text-center select-none transition-colors">
                            <div class="flex items-center justify-center gap-2">
                                <span>Pilar</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.pilar')" class="{{ $getSortClass('l.pilar') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.target')" class="cursor-pointer hover:bg-base-200 text-right select-none transition-colors">
                            <div class="flex items-center justify-end gap-2">
                                <span>Target</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.target')" class="{{ $getSortClass('l.target') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('rsm')" class="cursor-pointer hover:bg-base-200 text-center select-none transition-colors">
                            <div class="flex items-center justify-center gap-2">
                                <span>RSM</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('rsm')" class="{{ $getSortClass('rsm') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('asm')" class="cursor-pointer hover:bg-base-200 text-center select-none transition-colors">
                            <div class="flex items-center justify-center gap-2">
                                <span>ASM</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('asm')" class="{{ $getSortClass('asm') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('spv')" class="cursor-pointer hover:bg-base-200 text-center select-none transition-colors">
                            <div class="flex items-center justify-center gap-2">
                                <span>SPV</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('spv')" class="{{ $getSortClass('spv') }}" />
                            </div>
                        </th>
                        
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Status</th>
                        @canAdd('call-plan.jks-team-elite.master-customer')
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                        @endcanAdd
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $item)
                    @php
                        // Menentukan status jika ada yg mengunjungi atau tidak
                        $hasVisit = $item->rsm > 0 || $item->asm > 0 || $item->spv > 0;
                    @endphp
                    <tr wire:key="cust-row-{{ $item->customer_code_prc }}-{{ $item->distributor_code }}" class="hover:bg-base-200/50 transition-colors {{ !$hasVisit ? 'bg-error/5' : '' }}">
                        <td class="text-xs text-base-content/70">{{ $item->region_name }}</td>
                        <td class="text-xs text-base-content/70">{{ $item->area_name }}</td>
                        <td class="text-xs text-base-content/70">{{ $item->supervisor_name ?? '-' }}</td>
                        <td class="text-xs">
                            <div class="max-w-[150px] truncate text-base-content/80 font-medium" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</div>
                            <div class="text-[10px] text-base-content/50 font-mono mt-0.5">{{ $item->distributor_code }}</div>
                        </td>
                        <td class="max-w-[120px] truncate font-mono text-xs text-base-content/70" title="{{ $item->customer_code_prc }}">{{ $item->customer_code_prc }}</td>
                        <td class="font-mono text-xs">{{ $item->uniq_kd ?? '-' }}</td>
                        <td class="min-w-[200px] font-bold text-base-content/90">{{ $item->customer_name }}</td>
                        <td class="max-w-[200px] truncate text-xs text-base-content/60" title="{{ $item->customer_address }}">{{ $item->customer_address }}</td>
                        <td class="text-center">
                            @php
                                $badgeColor = match($item->pilar) { 
                                    '1. RWO' => 'error', 
                                    '2. PNR' => 'warning', 
                                    '3. NGVO' => 'success', 
                                    default => 'neutral' 
                                };
                            @endphp
                            <span class="badge badge-sm badge-outline badge-{{ $badgeColor }}">{{ $item->pilar ?? '-' }}</span>
                        </td>
                        <td class="text-right font-mono text-xs">Rp {{ number_format((float)($item->target ?? 0), 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($item->rsm > 0)
                                <div wire:click="showDetailVisits('{{ $item->customer_code_prc }}', '{{ addslashes($item->customer_name) }}', 'REGION')" class="badge badge-success badge-sm cursor-pointer hover:scale-110 transition-transform" title="Lihat Detail Plan (REGION)">{{ $item->rsm }}</div>
                            @else
                                <span class="text-base-content/30 font-semibold text-xs">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->asm > 0)
                                <div wire:click="showDetailVisits('{{ $item->customer_code_prc }}', '{{ addslashes($item->customer_name) }}', 'AREA')" class="badge badge-success badge-sm cursor-pointer hover:scale-110 transition-transform" title="Lihat Detail Plan (AREA)">{{ $item->asm }}</div>
                            @else
                                <span class="text-base-content/30 font-semibold text-xs">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->spv > 0)
                                <div wire:click="showDetailVisits('{{ $item->customer_code_prc }}', '{{ addslashes($item->customer_name) }}', 'SUPERVISOR')" class="badge badge-success badge-sm cursor-pointer hover:scale-110 transition-transform" title="Lihat Detail Plan (SUPERVISOR)">{{ $item->spv }}</div>
                            @else
                                <span class="text-base-content/30 font-semibold text-xs">-</span>
                            @endif
                        </td>
                        <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                            @if($hasVisit)
                                <div class="inline-flex items-center gap-1 text-[10px] font-bold text-success bg-success/15 rounded-lg py-1 px-2">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                    <span>Masuk Plan</span>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1 text-[10px] font-bold text-error bg-error/15 rounded-lg py-1 px-2">
                                    <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                    <span>Belum Diplan</span>
                                </div>
                            @endif
                        </th>
                        @canAdd('call-plan.jks-team-elite.master-customer')
                        <td class="text-center border-l border-base-300 bg-base-100">
                            <x-ui.button variant="primary" size="xs" outline wire:click="openAddPlanModal('{{ $item->customer_code_prc }}', '{{ $item->distributor_code }}')" title="Add Plan">
                                <x-heroicon-o-plus class="w-3 h-3" />
                            </x-ui.button>
                        </td>
                        @endcanAdd
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center py-8 text-base-content/50">Tidak ada data ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($data->hasPages())
            {{-- Footer Card (Pagination) --}}
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL FILTER -->
    <x-ui.modal wire:key="modal-filter-key" id="modal-filter" title="Filter Data" icon="funnel" :open="$isFilterModalOpen" wire:close="closeFilterModal">
        <div class="space-y-4">
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Region</span></label>
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full" @if(count($regions) <= 1) disabled @endif>
                    @if(count($regions) > 1) <option value="">-- Semua Region --</option> @endif
                    @foreach($regions as $r) 
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Area</span></label>
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full" @if(!$filterRegion || count($areas) <= 1) disabled @endif>
                    @if(count($areas) > 1 || count($areas) == 0) <option value="">-- Semua Area --</option> @endif
                    @foreach($areas as $a) 
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Supervisor</span></label>
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered w-full" @if(!$filterArea || count($supervisors) <= 1) disabled @endif>
                    @if(count($supervisors) > 1 || count($supervisors) == 0) <option value="">-- Semua Supervisor --</option> @endif
                    @foreach($supervisors as $s) 
                        <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option> 
                    @endforeach
                </select>
            </div>

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Distributor</span></label>
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered w-full" @if(!$filterRegion || count($distributors) <= 1) disabled @endif>
                    @if(count($distributors) > 1 || count($distributors) == 0) <option value="">-- Semua Distributor --</option> @endif
                    @foreach($distributors as $d) 
                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option> 
                    @endforeach
                </select>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="error" outline wire:click="resetFilter">Reset</x-ui.button>
            <x-ui.button variant="primary" wire:click="applyFilter">Terapkan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL DETAIL VISITS -->
    <x-ui.modal wire:key="modal-detail-key" id="modal-detail" title="Detail Plan Visit ({{ $detailLevel }})" icon="calendar" :open="$isDetailModalOpen" wire:close="closeDetailModal">
        <div class="mb-4">
            <h3 class="font-bold text-base">{{ $detailCustomerName }}</h3>
            <p class="text-xs text-base-content/60">Daftar rencana kunjungan untuk level {{ $detailLevel }}.</p>
        </div>
        
        <div class="overflow-x-auto border border-base-300 rounded-lg max-h-[60vh]">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="bg-base-200 text-xs">
                    <tr>
                        <th>Tanggal Plan</th>
                        <th>Kode Team</th>
                        <th>Nama Team</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailVisits as $visit)
                        <tr>
                            <td class="font-mono text-xs">{{ \Carbon\Carbon::parse($visit->tanggal)->format('d M Y') }}</td>
                            <td class="font-mono text-xs">{{ $visit->kode_team }}</td>
                            <td class="text-xs font-semibold">{{ $visit->nama_team ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-base-content/50 text-xs">Tidak ada detail yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-slot:footer>
            <x-ui.button variant="neutral" outline wire:click="closeDetailModal">Tutup</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL ADD PLAN -->
    <x-ui.modal wire:key="modal-add-plan-key" id="modal-add-plan" title="Tambah Plan Visit" icon="plus" :open="$isAddPlanModalOpen" wire:close="closeAddPlanModal">
        @if($selectedCustomerForPlan)
            <div class="mb-4 bg-base-200/50 p-3 rounded-lg border border-base-300">
                <h3 class="font-bold text-base text-base-content">{{ $selectedCustomerForPlan['customer_name'] }}</h3>
                <p class="text-xs text-base-content/70 font-mono">{{ $selectedCustomerForPlan['customer_code'] }}</p>
                <div class="mt-2 text-[10px] text-base-content/60 space-y-0.5">
                    <p><span class="font-semibold">Region:</span> {{ $selectedCustomerForPlan['region_name'] }}</p>
                    <p><span class="font-semibold">Area:</span> {{ $selectedCustomerForPlan['area_name'] }}</p>
                    <p><span class="font-semibold">Distributor:</span> {{ $selectedCustomerForPlan['distributor_name'] }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-semibold">Tanggal Kunjungan <span class="text-error">*</span></span></label>
                    <input type="date" wire:model="addPlanTanggal" class="input input-sm input-bordered w-full @error('addPlanTanggal') input-error @enderror">
                    @error('addPlanTanggal') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-semibold">Pilih Team Elite <span class="text-error">*</span></span></label>
                    <select wire:model="addPlanKodeTeam" class="select select-sm select-bordered w-full @error('addPlanKodeTeam') select-error @enderror">
                        <option value="">-- Pilih Team --</option>
                        @foreach($availableTeamCodes as $team)
                            <option value="{{ $team->team_elite_code }}">{{ $team->team_name }} ({{ $team->team_elite_code }})</option>
                        @endforeach
                    </select>
                    @if(count($availableTeamCodes) === 0)
                        <span class="text-warning text-[10px] mt-1">Tidak ada team elite yang ter-mapping untuk distributor toko ini.</span>
                    @endif
                    @error('addPlanKodeTeam') <span class="text-error text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif
        <x-slot:footer>
            <x-ui.button variant="neutral" outline wire:click="closeAddPlanModal">Batal</x-ui.button>
            <x-ui.button variant="primary" wire:click="submitPlan" wire:loading.attr="disabled" wire:target="submitPlan" :disabled="empty($availableTeamCodes)">
                <span wire:loading.remove wire:target="submitPlan">Simpan Plan</span>
                <span wire:loading wire:target="submitPlan"><span class="loading loading-spinner loading-xs"></span> Menyimpan...</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Script untuk Custom Toast menggunakan SweetAlert --}}
    @script
    <script>
        $wire.on('swal:toast', (event) => {
            const data = event[0];
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: data.icon || 'success',
                title: data.title,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    container: 'z-[9999]'
                }
            });
        });
    </script>
    @endscript
</div>
