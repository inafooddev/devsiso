<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Summary Kunjungan JKS Team Elite</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <button class="tab tab-xs px-4 transition-colors {{ $currentTab === 'summary' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}" wire:click="setTab('summary')">Summary</button>
            <button class="tab tab-xs px-4 transition-colors {{ $currentTab === 'detail' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}" wire:click="setTab('detail')">Detail</button>
        </div>
    </div>

    @if($currentTab === 'summary')
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Summary Kunjungan</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Ringkasan kunjungan JKS Team Elite</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" wire:model.live="startDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-full sm:w-auto" />
                    <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">s/d</span>
                    <input type="date" wire:model.live="endDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-full sm:w-auto" />
                </div>
                <button wire:click="applyFilter" class="btn btn-sm btn-primary rounded-xl">Terapkan</button>
                <button wire:click="resetFilter" class="btn btn-sm btn-ghost rounded-xl">Reset</button>
            </div>
        </div>
        
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra w-full whitespace-nowrap text-xs">
                <thead class="sticky top-0 z-10 text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:bg-base-300">
                    <tr>
                        <th rowspan="2" class="align-middle border-b border-r border-base-200">Region</th>
                        <th rowspan="2" class="align-middle border-b border-r border-base-200">Area</th>
                        <th rowspan="2" class="align-middle border-b border-r border-base-200">Team (Supervisor)</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">Total Toko</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">Value</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">1. RWO</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">2. PNR</th>
                        <th colspan="3" class="text-center border-b border-base-200">3. NGVO</th>
                    </tr>
                    <tr>
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-right border-b border-base-200">Target</th>
                        <th class="text-right border-b border-base-200">Order</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-base-200">%</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                @if($dataSummary->isEmpty())
                    <tr>
                        <td colspan="99">
                            <div class="flex flex-col items-center justify-center py-12 gap-3 text-base-content/40">
                                <x-heroicon-o-inbox class="w-10 h-10" />
                                <p class="text-sm">Silakan pilih filter Region terlebih dahulu untuk menampilkan data.</p>
                            </div>
                        </td>
                    </tr>
                @else
                @foreach($dataSummary as $row)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->nama_region }}</td>
                        <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->nama_area }}</td>
                        <td class="whitespace-nowrap font-medium border-r border-base-200/50">{{ $row->nama_team }}</td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->total_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->total_visit, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $pct = $row->total_plan > 0 ? ($row->total_visit / $row->total_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($pct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->total_target ?? 0, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->total_order ?? 0, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $valPct = $row->total_target > 0 ? ($row->total_order / $row->total_target) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $valPct >= 80 ? 'success' : ($valPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($valPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_actual, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $rwoPct = $row->rwo_plan > 0 ? ($row->rwo_actual / $row->rwo_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $rwoPct >= 80 ? 'success' : ($rwoPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($rwoPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_actual, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $pnrPct = $row->pnr_plan > 0 ? ($row->pnr_actual / $row->pnr_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $pnrPct >= 80 ? 'success' : ($pnrPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($pnrPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_actual, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">
                            @php
                                $ngvoPct = $row->ngvo_plan > 0 ? ($row->ngvo_actual / $row->ngvo_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $ngvoPct >= 80 ? 'success' : ($ngvoPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($ngvoPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                    </tr>
                @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($currentTab === 'detail')
        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0">
            {{-- Card Total Visit --}}
            @php
                $gapVisit = max(0, ($kpiData['total_jks'] ?? 0) - ($kpiData['total_visit'] ?? 0));
                $pctVisit = ($kpiData['total_jks'] ?? 0) > 0 ? round((($kpiData['total_visit'] ?? 0) / $kpiData['total_jks']) * 100) : 0;
            @endphp
            <div class="bg-base-100 rounded-xl p-3 border border-base-300 shadow-sm relative overflow-hidden group hover:border-primary/30 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                    <x-heroicon-s-check-circle class="w-16 h-16 md:w-20 md:h-20 text-primary" />
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-1.5 mb-2">
                        <div class="w-6 h-6 rounded-md bg-primary/10 flex items-center justify-center text-primary">
                            <x-heroicon-s-check-circle class="w-3 h-3" />
                        </div>
                        <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Total Visit</h3>
                    </div>
                    <div class="flex items-baseline gap-1 flex-wrap mb-2">
                        <span class="text-xl font-bold text-primary leading-none">{{ number_format($kpiData['total_visit'] ?? 0) }}</span>
                        <span class="text-sm font-bold text-primary/70 leading-none">/ {{ number_format($kpiData['total_jks'] ?? 0) }}</span>
                    </div>
                    <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                        <div class="bg-primary h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctVisit) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider">
                        <span class="text-base-content/50">Gap: <span class="text-error">{{ number_format($gapVisit) }}</span></span>
                        <span class="text-primary">{{ $pctVisit }}%</span>
                    </div>
                </div>
            </div>

            {{-- Card Total Order --}}
            @php
                $gapOrder = max(0, ($kpiData['total_target'] ?? 0) - ($kpiData['total_order'] ?? 0));
                $pctOrder = ($kpiData['total_target'] ?? 0) > 0 ? round((($kpiData['total_order'] ?? 0) / $kpiData['total_target']) * 100) : 0;
            @endphp
            <div class="bg-base-100 rounded-xl p-3 border border-base-300 shadow-sm relative overflow-hidden group hover:border-success/30 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                    <x-heroicon-s-banknotes class="w-16 h-16 md:w-20 md:h-20 text-success" />
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-1.5 mb-2">
                        <div class="w-6 h-6 rounded-md bg-success/10 flex items-center justify-center text-success">
                            <x-heroicon-s-banknotes class="w-3 h-3" />
                        </div>
                        <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Total Order</h3>
                    </div>
                    <div class="flex items-baseline gap-1.5 flex-wrap mb-2">
                        <span class="text-xl font-bold text-success leading-none">{{ number_format($kpiData['total_order'] ?? 0) }}</span>
                        <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($kpiData['total_target'] ?? 0) }}</span>
                    </div>
                    <div class="w-full bg-base-200 rounded-full h-1 mb-1.5 overflow-hidden">
                        <div class="bg-success h-1 rounded-full transition-all duration-500" style="width: {{ min(100, $pctOrder) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-wider">
                        <span class="text-base-content/50">Gap: <span class="text-error">{{ number_format($gapOrder) }}</span></span>
                        <span class="text-success">{{ $pctOrder }}%</span>
                    </div>
                </div>
            </div>

            {{-- Card Visit 1. RWO --}}
            @php
                $gapRwo = max(0, ($kpiData['total_rwo'] ?? 0) - ($kpiData['visit_rwo'] ?? 0));
                $pctRwo = ($kpiData['total_rwo'] ?? 0) > 0 ? round((($kpiData['visit_rwo'] ?? 0) / $kpiData['total_rwo']) * 100) : 0;
            @endphp
            <div class="bg-base-100 rounded-xl p-3 border border-base-300 shadow-sm relative overflow-hidden group hover:border-info/30 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                    <x-heroicon-s-shopping-bag class="w-16 h-16 md:w-20 md:h-20 text-info" />
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-1.5 mb-2">
                        <div class="w-6 h-6 rounded-md bg-info/10 flex items-center justify-center text-info">
                            <x-heroicon-s-shopping-bag class="w-3 h-3" />
                        </div>
                        <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Visit 1. RWO</h3>
                    </div>
                    <div class="flex items-baseline gap-1.5 flex-wrap mb-2">
                        <span class="text-xl font-bold text-info leading-none">{{ number_format($kpiData['visit_rwo'] ?? 0) }}</span>
                        <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($kpiData['total_rwo'] ?? 0) }}</span>
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

            {{-- Card Visit 2. PNR --}}
            @php
                $gapPnr = max(0, ($kpiData['total_pnr'] ?? 0) - ($kpiData['visit_pnr'] ?? 0));
                $pctPnr = ($kpiData['total_pnr'] ?? 0) > 0 ? round((($kpiData['visit_pnr'] ?? 0) / $kpiData['total_pnr']) * 100) : 0;
            @endphp
            <div class="bg-base-100 rounded-xl p-3 border border-base-300 shadow-sm relative overflow-hidden group hover:border-secondary/30 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                    <x-heroicon-s-archive-box class="w-16 h-16 md:w-20 md:h-20 text-secondary" />
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-1.5 mb-2">
                        <div class="w-6 h-6 rounded-md bg-secondary/10 flex items-center justify-center text-secondary">
                            <x-heroicon-s-archive-box class="w-3 h-3" />
                        </div>
                        <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Visit 2. PNR</h3>
                    </div>
                    <div class="flex items-baseline gap-1.5 flex-wrap mb-2">
                        <span class="text-xl font-bold text-secondary leading-none">{{ number_format($kpiData['visit_pnr'] ?? 0) }}</span>
                        <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($kpiData['total_pnr'] ?? 0) }}</span>
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

            {{-- Card Visit 3. NGVO --}}
            @php
                $gapNgvo = max(0, ($kpiData['total_ngvo'] ?? 0) - ($kpiData['visit_ngvo'] ?? 0));
                $pctNgvo = ($kpiData['total_ngvo'] ?? 0) > 0 ? round((($kpiData['visit_ngvo'] ?? 0) / $kpiData['total_ngvo']) * 100) : 0;
            @endphp
            <div class="bg-base-100 rounded-xl p-3 border border-base-300 shadow-sm relative overflow-hidden group hover:border-warning/30 hover:shadow-md transition-all duration-300">
                <div class="absolute -right-4 -top-4 p-4 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none">
                    <x-heroicon-s-star class="w-16 h-16 md:w-20 md:h-20 text-warning" />
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-1.5 mb-2">
                        <div class="w-6 h-6 rounded-md bg-warning/10 flex items-center justify-center text-warning">
                            <x-heroicon-s-star class="w-3 h-3" />
                        </div>
                        <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-widest">Visit 3. NGVO</h3>
                    </div>
                    <div class="flex items-baseline gap-1.5 flex-wrap mb-2">
                        <span class="text-xl font-bold text-warning leading-none">{{ number_format($kpiData['visit_ngvo'] ?? 0) }}</span>
                        <span class="text-[10px] font-bold text-base-content/40">/ {{ number_format($kpiData['total_ngvo'] ?? 0) }}</span>
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

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full xl:w-auto">
                <h2 class="text-base md:text-lg font-bold">Detail Kunjungan</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Detail kunjungan per toko</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-2">
                    <input type="date" wire:model="startDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                    <span class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">s/d</span>
                    <input type="date" wire:model="endDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                </div>
                
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->kode_region }}">{{ $region->nama_region }}</option>
                        @endforeach
                    </select>
                    
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]" @if(empty($areas)) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->kode_area }}">{{ $area->nama_area }}</option>
                        @endforeach
                    </select>
                    
                    <select wire:model.live="selectedTeam" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]" @if(empty($teams)) disabled @endif>
                        <option value="">Semua Team</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->kode_team }}">{{ $team->nama_team }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <x-ui.button class="rounded-xl" variant="neutral" icon="arrow-path" size="sm" wire:click="resetFilter" spinner="resetFilter">Reset</x-ui.button>
                    <x-ui.button class="rounded-xl" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                </div>
            </div>
        </div>



        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-xs table-zebra table-pin-rows w-full whitespace-nowrap text-[10px]">
                <thead class="text-[10px] uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:bg-base-300">
                    <tr>
                        <th>Tanggal</th>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Team</th>
                        <th>Distributor</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Pilar</th>
                        <th>Target</th>
                        <th>Visit</th>
                        <th>Order Val</th>
                    </tr>
                </thead>
                <tbody class="text-[10px]">
                @if(empty($dataKunjungan))
                    <tr>
                        <td colspan="11" class="text-center py-12 text-base-content/40">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <x-heroicon-o-inbox class="w-10 h-10" />
                                <p class="text-sm">Silakan pilih filter untuk menampilkan data.</p>
                            </div>
                        </td>
                    </tr>
                @else
                @php
                    $groupedData = collect($dataKunjungan)->groupBy('tanggal');
                @endphp
                @foreach($groupedData as $tanggal => $rows)
                    @foreach($rows as $row)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="whitespace-nowrap">{{ $row->tanggal }}</td>
                            <td class="whitespace-nowrap">{{ $row->nama_region }}</td>
                            <td class="whitespace-nowrap">{{ $row->nama_area }}</td>
                            <td class="max-w-[150px] truncate" title="{{ $row->nama_team }}">{{ $row->nama_team }}</td>
                            <td class="max-w-[150px] truncate" title="{{ $row->distributor_code }} - {{ $row->distributor_name }}">{{ $row->distributor_code }} - {{ $row->distributor_name }}</td>
                            <td class="whitespace-nowrap">{{ $row->custno }} - {{ $row->custname }}</td>
                            <td class="max-w-[160px] truncate" title="{{ $row->addres }}">{{ $row->addres }}</td>
                            <td class="whitespace-nowrap text-center">
                                @php
                                    $pilarVal = strtoupper($row->pilar ?? '');
                                    $badgeVariant = 'neutral';
                                    if (str_contains($pilarVal, '1. RWO')) $badgeVariant = 'success';
                                    elseif (str_contains($pilarVal, '2. PNR')) $badgeVariant = 'warning';
                                    elseif (str_contains($pilarVal, '3. NGVO')) $badgeVariant = 'secondary';
                                @endphp
                                <x-ui.badge variant="{{ $badgeVariant }}">{{ $row->pilar }}</x-ui.badge>
                            </td>
                            <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->target ?? 0, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap text-center">
                                @if($row->flag_visit == 'Y')
                                    <x-ui.badge variant="success">Yes</x-ui.badge>
                                @else
                                    <x-ui.badge variant="error">No</x-ui.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->order_val ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="!bg-primary/10 font-bold border-t-2 border-primary/20">
                        <td colspan="8" class="text-right py-2 text-primary">Subtotal Tanggal {{ $tanggal }}:</td>
                        <td class="whitespace-nowrap font-mono text-right text-primary py-2">{{ number_format($rows->sum('target'), 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center text-primary py-2" title="Total Plan / Total Visit">{{ $rows->count() }} / {{ $rows->filter(fn($r) => $r->flag_visit == 'Y')->count() }}</td>
                        <td class="whitespace-nowrap font-mono text-right text-primary py-2">{{ number_format($rows->sum('order_val'), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
