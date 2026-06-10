<div>
    <!-- TABS -->
    <div class="tabs tabs-boxed mb-4 w-fit bg-base-100 shadow-sm border border-base-200 p-1">
        <button class="tab px-8 {{ $currentTab === 'summary' ? 'tab-active font-bold' : '' }}" wire:click="setTab('summary')">Summary</button>
        <button class="tab px-8 {{ $currentTab === 'detail' ? 'tab-active font-bold' : '' }}" wire:click="setTab('detail')">Detail</button>
    </div>
    @if($isFiltered && $currentTab === 'detail')
    <!-- KPI CARDS -->
    <div class="grid grid-cols-7 gap-4 mb-6 items-stretch">
        
        <!-- Total Visit -->
        <div class="bg-base-100 rounded-xl p-4 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow duration-300 border border-base-200 flex flex-col h-full">
            <div class="text-[10px] text-base-content/50 font-semibold uppercase tracking-wider mb-1" title="Jumlah toko yang telah dikunjungi">Total Visit</div>
            <div class="flex items-center justify-between mb-1">
                <div class="text-xl xl:text-2xl font-extrabold text-base-content tracking-tight">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pct = ($kpiData['total_toko'] ?? 0) > 0 ? (($kpiData['total_visit'] ?? 0) / ($kpiData['total_toko'] ?? 1) * 100) : 0;
                            $pctClass = $pct >= 80 ? 'text-success bg-success/10' : ($pct >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctClass }}">{{ number_format($pct, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-auto pt-2 flex flex-col gap-1">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Total Toko</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['total_toko'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_toko'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 pt-1.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Gap</span>
                    @php
                        $gap = ($kpiData['total_toko'] ?? 0) - ($kpiData['total_visit'] ?? 0);
                        $gapClass = $gap > 0 ? 'text-error' : 'text-success';
                    @endphp
                    <span class="text-[11px] font-bold {{ $gapClass }} text-right" title="{{ number_format($gap, 0, ',', '.') }}">{{ number_format($gap, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Toko Order -->
        <div class="bg-base-100 rounded-xl p-4 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow duration-300 border border-base-200 flex flex-col h-full">
            <div class="text-[10px] text-base-content/50 font-semibold uppercase tracking-wider mb-1" title="Jumlah toko yang melakukan pemesanan (Order > 0)">Toko Order</div>
            <div class="flex items-center justify-between mb-1">
                <div class="text-xl xl:text-2xl font-extrabold text-base-content tracking-tight">{{ number_format($kpiData['total_toko_order'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctOrder = ($kpiData['total_visit'] ?? 0) > 0 ? (($kpiData['total_toko_order'] ?? 0) / ($kpiData['total_visit'] ?? 1) * 100) : 0;
                            $pctOrderClass = $pctOrder >= 80 ? 'text-success bg-success/10' : ($pctOrder >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctOrderClass }}">{{ number_format($pctOrder, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-auto pt-2 flex flex-col gap-1">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Total Visit</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 pt-1.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Gap</span>
                    @php
                        $gapOrder = ($kpiData['total_visit'] ?? 0) - ($kpiData['total_toko_order'] ?? 0);
                        $gapOrderClass = $gapOrder > 0 ? 'text-error' : 'text-success';
                    @endphp
                    <span class="text-[11px] font-bold {{ $gapOrderClass }} text-right" title="{{ number_format($gapOrder, 0, ',', '.') }}">{{ number_format($gapOrder, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total Order -->
        <div class="bg-base-100 rounded-xl p-4 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow duration-300 border border-base-200 flex flex-col h-full">
            <div class="text-[10px] text-base-content/50 font-semibold uppercase tracking-wider mb-1" title="Total nilai pemesanan keseluruhan">Total Order</div>
            <div class="flex items-center justify-between mb-1">
                <div class="text-xl xl:text-2xl font-extrabold text-base-content tracking-tight" title="{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctTarget = ($kpiData['total_target'] ?? 0) > 0 ? (($kpiData['total_order'] ?? 0) / ($kpiData['total_target'] ?? 1) * 100) : 0;
                            $pctTargetClass = $pctTarget >= 80 ? 'text-success bg-success/10' : ($pctTarget >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctTargetClass }}">{{ number_format($pctTarget, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-auto pt-2 flex flex-col gap-1">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Target</span>
                    <span class="text-[9px] font-bold text-base-content/70" title="{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 pt-1.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Gap</span>
                    @php
                        $gapTarget = ($kpiData['total_target'] ?? 0) - ($kpiData['total_order'] ?? 0);
                        $gapTargetClass = $gapTarget > 0 ? 'text-error' : 'text-success';
                    @endphp
                    <span class="text-[11px] font-bold {{ $gapTargetClass }} text-right" title="{{ number_format($gapTarget, 0, ',', '.') }}">{{ number_format($gapTarget, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Visit 1. RWO -->
        <div class="bg-base-100 rounded-xl p-4 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow duration-300 border border-base-200 flex flex-col h-full">
            <div class="text-[10px] text-base-content/50 font-semibold uppercase tracking-wider mb-1" title="Kinerja toko pada pilar 1. RWO">Total 1. RWO</div>
            <div class="flex items-center justify-between mb-1">
                <div class="text-xl xl:text-2xl font-extrabold text-base-content tracking-tight">{{ number_format($kpiData['total_rwo'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctRwo = ($kpiData['total_rwo'] ?? 0) > 0 ? (($kpiData['toko_order_rwo'] ?? 0) / ($kpiData['total_rwo'] ?? 1) * 100) : 0;
                            $pctRwoClass = $pctRwo >= 80 ? 'text-success bg-success/10' : ($pctRwo >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctRwoClass }}">{{ number_format($pctRwo, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-auto pt-2 flex flex-col gap-1">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Toko Order</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['toko_order_rwo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['toko_order_rwo'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 pt-1.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Val Order</span>
                    <span class="text-[11px] font-bold text-success text-right" title="{{ number_format($kpiData['total_order_rwo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order_rwo'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Visit 2. PNR -->
        <div class="bg-base-100 rounded-xl p-4 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow duration-300 border border-base-200 flex flex-col h-full">
            <div class="text-[10px] text-base-content/50 font-semibold uppercase tracking-wider mb-1" title="Kinerja toko pada pilar 2. PNR">Total 2. PNR</div>
            <div class="flex items-center justify-between mb-1">
                <div class="text-xl xl:text-2xl font-extrabold text-base-content tracking-tight">{{ number_format($kpiData['total_pnr'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctPnr = ($kpiData['total_pnr'] ?? 0) > 0 ? (($kpiData['toko_order_pnr'] ?? 0) / ($kpiData['total_pnr'] ?? 1) * 100) : 0;
                            $pctPnrClass = $pctPnr >= 80 ? 'text-success bg-success/10' : ($pctPnr >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctPnrClass }}">{{ number_format($pctPnr, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-auto pt-2 flex flex-col gap-1">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Toko Order</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['toko_order_pnr'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['toko_order_pnr'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 pt-1.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Val Order</span>
                    <span class="text-[11px] font-bold text-success text-right" title="{{ number_format($kpiData['total_order_pnr'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order_pnr'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Visit 3. NGVO -->
        <div class="bg-base-100 rounded-xl p-4 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow duration-300 border border-base-200 flex flex-col h-full">
            <div class="text-[10px] text-base-content/50 font-semibold uppercase tracking-wider mb-1" title="Kinerja toko pada pilar 3. NGVO">Total 3. NGVO</div>
            <div class="flex items-center justify-between mb-1">
                <div class="text-xl xl:text-2xl font-extrabold text-base-content tracking-tight">{{ number_format($kpiData['total_ngvo'] ?? 0, 0, ',', '.') }}</div>
                @php
                            $pctNgvo = ($kpiData['total_ngvo'] ?? 0) > 0 ? (($kpiData['toko_order_ngvo'] ?? 0) / ($kpiData['total_ngvo'] ?? 1) * 100) : 0;
                            $pctNgvoClass = $pctNgvo >= 80 ? 'text-success bg-success/10' : ($pctNgvo >= 50 ? 'text-warning bg-warning/10' : 'text-error bg-error/10');
                        @endphp
                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded {{ $pctNgvoClass }}">{{ number_format($pctNgvo, 1, ',', '.') }}%</span>
            </div>
            
            <div class="mt-auto pt-2 flex flex-col gap-1">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Toko Order</span>
                    <span class="text-[11px] font-bold text-base-content/70" title="{{ number_format($kpiData['toko_order_ngvo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['toko_order_ngvo'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-base-200/60 pt-1.5">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">Val Order</span>
                    <span class="text-[11px] font-bold text-success text-right" title="{{ number_format($kpiData['total_order_ngvo'] ?? 0, 0, ',', '.') }}">{{ number_format($kpiData['total_order_ngvo'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Total NOO -->
        <div class="bg-base-100 rounded-xl p-4 shadow-[0_2px_8px_-2px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow duration-300 border border-base-200 flex flex-col h-full">
            <div class="text-[10px] text-base-content/50 font-semibold uppercase tracking-wider mb-1" title="Jumlah toko berstatus NOO">Total NOO</div>
            <div class="flex items-center justify-between mb-1">
                <div class="text-xl xl:text-2xl font-extrabold text-base-content tracking-tight">{{ number_format($kpiData['total_noo'] ?? 0, 0, ',', '.') }}</div>
            </div>
            
            <div class="mt-auto pt-2 flex flex-col gap-1">
                <div class="flex justify-between items-center opacity-0 pointer-events-none">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">-</span>
                    <span class="text-[8px] font-bold px-1.5 py-0.5 rounded">-</span>
                </div>
                <div class="flex justify-between items-center border-t border-transparent pt-1.5 opacity-0 pointer-events-none">
                    <span class="text-[9px] font-medium text-base-content/40 uppercase">-</span>
                    <span class="text-[11px] font-bold">-</span>
                </div>
            </div>
        </div>

    </div>
    @endif
    <x-card title="Summary Visit Team Elite" icon="document-text" class="mb-4" flush="true">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="selectedRegion" class="select select-sm select-bordered min-w-[150px]">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="selectedLevel" class="select select-sm select-bordered min-w-[150px]">
                    <option value="">Semua Level</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
                @if($currentTab === 'detail')
                <select wire:model.live="selectedTeam" class="select select-sm select-bordered min-w-[150px]" @if(empty($teams)) disabled @endif>
                    <option value="" disabled>Pilih Team...</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->team_code }}">{{ $team->team_name }}</option>
                    @endforeach
                </select>
                
                <select wire:model="selectedKeterangan" class="select select-sm select-bordered">
                    <option value="">Semua Keterangan</option>
                    <option value="RO">RO</option>
                    <option value="NOO">NOO</option>
                </select>
                @endif
                <input type="month" wire:model="selectedMonth" class="input input-sm input-bordered min-w-[150px]" />
                <x-ui.button size="sm" variant="primary" icon="magnifying-glass" wire:click="applyFilter">
                    Filter
                </x-ui.button>
            </div>
        </x-slot:actions>

        @if($isFiltered)
        @if($currentTab === 'summary')
        <x-ui.table striped hover empty="Tidak ada data." class="max-h-[60vh] overflow-y-auto border-x-0 border-b-0 rounded-none shadow-none mt-2 [&_thead]:sticky [&_thead]:top-0 [&_thead]:z-20 [&_thead]:shadow-sm [&_thead_tr]:bg-base-300 [&_tfoot]:sticky [&_tfoot]:bottom-0 [&_tfoot]:z-20 [&_tfoot]:shadow-[0_-1px_3px_rgba(0,0,0,0.1)] [&_th]:!text-[10px] [&_td]:!text-[10px] [&_.badge]:!text-[10px] [&_.badge]:!py-0.5 [&_.badge]:!px-1">
            <x-slot:head>
                <tr>
                    <th rowspan="2" class="align-middle border-b border-r border-base-200">Region</th>
                    <th rowspan="2" class="align-middle border-b border-r border-base-200">Area</th>
                    <th rowspan="2" class="align-middle border-b border-r border-base-200">Team</th>
                    <th colspan="3" class="text-center border-b border-r border-base-200">Total Toko</th>
                    <th colspan="5" class="text-center border-b border-r border-base-200">Value</th>
                    <th colspan="3" class="text-center border-b border-r border-base-200">1. RWO</th>
                    <th colspan="3" class="text-center border-b border-r border-base-200">2. PNR</th>
                    <th colspan="3" class="text-center border-b border-base-200">3. NGVO</th>
                </tr>
                <tr>
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-r border-base-200">%</th>
                    
                    <th class="text-right border-b border-base-200">Target</th>
                    <th class="text-right border-b border-base-200">Order</th>
                    <th class="text-center border-b border-base-200">%</th>
                    <th class="text-right border-b border-base-200">Invoice</th>
                    <th class="text-right border-b border-r border-base-200">Selisih</th>
                    
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-r border-base-200">%</th>
                    
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-r border-base-200">%</th>
                    
                    <th class="text-center border-b border-base-200">JKS</th>
                    <th class="text-center border-b border-base-200">Visit</th>
                    <th class="text-center border-b border-base-200">%</th>
                </tr>
            </x-slot:head>
            
            @foreach($dataSummary as $row)
                <tr>
                    <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->region_name }}</td>
                    <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->area_name }}</td>
                    <td class="whitespace-nowrap font-medium border-r border-base-200/50">{{ $row->team_name }}</td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->total_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->total_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-r border-base-200/50">
                        @php
                            $pct = $row->total_toko > 0 ? ($row->total_visit / $row->total_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($pct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->total_target ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->total_order ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-base-200/50">
                        @php
                            $valPct = $row->total_target > 0 ? ($row->total_order / $row->total_target) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $valPct >= 80 ? 'success' : ($valPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($valPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap font-mono text-right font-bold text-primary">{{ number_format($row->total_invoice ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right border-r border-base-200/50 {{ (($row->total_invoice ?? 0) - ($row->total_order ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($row->total_invoice ?? 0) - ($row->total_order ?? 0), 0, ',', '.') }}</td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-r border-base-200/50">
                        @php
                            $rwoPct = $row->rwo_toko > 0 ? ($row->rwo_visit / $row->rwo_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $rwoPct >= 80 ? 'success' : ($rwoPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($rwoPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center border-r border-base-200/50">
                        @php
                            $pnrPct = $row->pnr_toko > 0 ? ($row->pnr_visit / $row->pnr_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $pnrPct >= 80 ? 'success' : ($pnrPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($pnrPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                    
                    <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_toko, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_visit, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-center">
                        @php
                            $ngvoPct = $row->ngvo_toko > 0 ? ($row->ngvo_visit / $row->ngvo_toko) * 100 : 0;
                        @endphp
                        <x-ui.badge variant="{{ $ngvoPct >= 80 ? 'success' : ($ngvoPct >= 50 ? 'warning' : 'error') }}">
                            {{ number_format($ngvoPct, 1, ',', '.') }}%
                        </x-ui.badge>
                    </td>
                </tr>
            @endforeach
            <x-slot:foot>
                <tr class="bg-base-200 font-bold border-t-2 border-base-300">
                    <td colspan="3" class="text-right border-r border-base-200/50 uppercase tracking-wider">Subtotal</td>
                    <td class="text-center">{{ number_format($kpiData['total_toko'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $pctTotal = ($kpiData['total_toko'] ?? 0) > 0 ? (($kpiData['total_visit'] ?? 0) / ($kpiData['total_toko'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($pctTotal, 1, ',', '.') }}%
                    </td>
                    
                    <td class="text-right font-mono">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-base-200/50">
                        @php
                            $valPctTotal = ($kpiData['total_target'] ?? 0) > 0 ? (($kpiData['total_order'] ?? 0) / ($kpiData['total_target'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($valPctTotal, 1, ',', '.') }}%
                    </td>
                    <td class="text-right font-mono">{{ number_format($kpiData['total_invoice'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono border-r border-base-200/50 {{ (($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0)) < 0 ? 'text-error' : 'text-success' }}">
                        {{ number_format(($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0), 0, ',', '.') }}
                    </td>
                    
                    <td class="text-center">{{ number_format($kpiData['total_rwo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['toko_order_rwo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $rwoPctTotal = ($kpiData['total_rwo'] ?? 0) > 0 ? (($kpiData['toko_order_rwo'] ?? 0) / ($kpiData['total_rwo'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($rwoPctTotal, 1, ',', '.') }}%
                    </td>
                    
                    <td class="text-center">{{ number_format($kpiData['total_pnr'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['toko_order_pnr'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $pnrPctTotal = ($kpiData['total_pnr'] ?? 0) > 0 ? (($kpiData['toko_order_pnr'] ?? 0) / ($kpiData['total_pnr'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($pnrPctTotal, 1, ',', '.') }}%
                    </td>
                    
                    <td class="text-center">{{ number_format($kpiData['total_ngvo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($kpiData['toko_order_ngvo'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center border-r border-base-200/50">
                        @php
                            $ngvoPctTotal = ($kpiData['total_ngvo'] ?? 0) > 0 ? (($kpiData['toko_order_ngvo'] ?? 0) / ($kpiData['total_ngvo'] ?? 1) * 100) : 0;
                        @endphp
                        {{ number_format($ngvoPctTotal, 1, ',', '.') }}%
                    </td>
                </tr>
            </x-slot:foot>
        </x-ui.table>
        @elseif($currentTab === 'detail')
        <x-ui.table striped hover sticky="true" empty="Tidak ada data." class="max-h-[60vh] overflow-y-auto border-x-0 border-b-0 rounded-none shadow-none mt-2 [&_tfoot]:sticky [&_tfoot]:bottom-0 [&_tfoot]:z-20 [&_tfoot]:shadow-[0_-1px_3px_rgba(0,0,0,0.1)] [&_th]:!text-[10px] [&_td]:!text-[10px] [&_.badge]:!text-[10px] [&_.badge]:!py-0.5 [&_.badge]:!px-1">
            <x-slot:head>
                <tr>
                    <th>Region</th>
                    <th>Area</th>
                    <th>Team</th>
                    <th>Cust No</th>
                    <th>Uniq ID</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Ket</th>
                    <th>Visit</th>
                    <th>Pilar</th>
                    <th class="text-right">Target</th>
                    <th class="text-right">Order Val</th>
                    <th class="text-right">Invoice</th>
                    <th class="text-right">Selisih</th>
                </tr>
            </x-slot:head>
            
            @foreach($dataKunjungan as $row)
                <tr class="{{ str_contains(strtoupper($row->keterangan ?? ''), 'NOO') ? '!bg-warning/20 hover:!bg-warning/30' : '' }}">
                    <td class="whitespace-nowrap">{{ $row->region_name }}</td>
                    <td class="whitespace-nowrap">{{ $row->area_name }}</td>
                    <td class="max-w-[150px] truncate" title="{{ $row->team_name }}">{{ $row->team_name }}</td>
                    <td class="max-w-[100px] truncate" title="{{ $row->custno }}">{{ $row->custno }}</td>
                    <td class="max-w-[120px] truncate" title="{{ $row->uniq_id }}">{{ $row->uniq_id }}</td>
                    <td class="max-w-[145px] truncate" title="{{ $row->custname }}">{{ $row->custname }}</td>
                    <td class="max-w-[110px] text-xs truncate" title="{{ $row->address }}">{{ $row->address }}</td>
                    <td class="whitespace-nowrap">{{ $row->keterangan }}</td>
                    <td class="whitespace-nowrap text-center">
                        @if($row->status_visit == 'Y')
                            <x-ui.badge variant="success">Yes</x-ui.badge>
                        @elseif($row->status_visit == 'N')
                            <x-ui.badge variant="error">No</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">{{ $row->status_visit }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-center">
                        @if($row->pilar)
                            <x-ui.badge variant="neutral">{{ $row->pilar }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->target ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->order_val ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->invoice ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right {{ (($row->invoice ?? 0) - ($row->order_val ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($row->invoice ?? 0) - ($row->order_val ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <x-slot:foot>
                <tr class="font-bold border-t-2 border-base-300">
                    <td colspan="10" class="text-right uppercase tracking-wider">Subtotal</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_invoice'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm {{ (($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            </x-slot:foot>
        </x-ui.table>
        @endif
        @else
        <div class="p-8 text-center text-base-content/60">
            <x-heroicon-o-funnel class="w-12 h-12 mx-auto mb-3 opacity-50" />
            @if($currentTab === 'detail')
            <p>Silakan pilih <strong>Team</strong> secara spesifik dan sesuaikan filter lainnya, lalu klik tombol <strong>Filter</strong> untuk menampilkan data.</p>
            @else
            <p>Silakan sesuaikan filter bulan, level atau team, lalu klik tombol <strong>Filter</strong> untuk menampilkan data summary.</p>
            @endif
        </div>
        @endif
    </x-card>
</div>
