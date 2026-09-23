<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Monitoring Reward Distributor</x-slot>

    {{-- Loading Overlay for the whole page (optional) --}}
    <div wire:loading.flex class="fixed inset-0 z-50 bg-base-100/50 backdrop-blur-sm items-center justify-center">
        <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>



    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Monitoring Reward</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Tahun {{ $year }} - Semua Periode (Flat View)</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full sm:w-auto max-w-[150px] bg-base-100 text-xs">
                    <option value="">Semua Region</option>
                    @foreach($regions as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full sm:w-auto max-w-[150px] bg-base-100 text-xs">
                    <option value="">Semua Area</option>
                    @foreach($areas as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
                <x-ui.search-input wire:model.live.debounce.300ms="search" />
                <button wire:click="openImportModal" class="btn btn-sm btn-outline btn-primary">
                    <x-heroicon-s-arrow-up-tray class="w-4 h-4" /> Import AR & Stok
                </button>
                <x-ui.action-button type="export" wire:click="export" />
            </div>
        </div>

        {{-- Body Card --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider text-base-content/80 shadow-sm">
                    <tr>
                        {{-- CORNER TOP-LEFT (Rowspan 2) --}}
                        <th rowspan="2" class="sticky top-0 left-0 z-30 bg-base-300 h-10 border-r border-base-200 min-w-[250px] align-middle border-b border-base-300">Info Distributor</th>
                        
                        {{-- HEADER P1 TOP --}}
                        <th colspan="5" class="sticky top-0 z-20 bg-base-300 h-10 text-center border-r border-base-200 align-middle">Periode 1 (Jan-Jun)</th>
                        
                        {{-- HEADER P2 TOP --}}
                        <th colspan="5" class="sticky top-0 z-20 bg-base-300 h-10 text-center border-r border-base-200 align-middle">Periode 2 (Jul-Des)</th>
                        
                        {{-- CORNER TOP-RIGHT (Rowspan 2) --}}
                        <th rowspan="2" class="sticky top-0 right-0 z-30 bg-base-300 h-10 text-right shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] align-middle border-b border-base-300">Total Reward</th>
                    </tr>
                    <tr>
                        {{-- ROW 2 P1 --}}
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right align-middle border-b border-base-300">Target</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right align-middle border-b border-base-300">Sell In</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right align-middle border-b border-base-300">Sell Out</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-center align-middle border-b border-base-300">Syarat (AR&STK)</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right border-r border-base-200 align-middle border-b border-base-300">Reward P1</th>

                        {{-- ROW 2 P2 --}}
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right align-middle border-b border-base-300">Target</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right align-middle border-b border-base-300">Sell In</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right align-middle border-b border-base-300">Sell Out</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-center align-middle border-b border-base-300">Syarat (AR&STK)</th>
                        <th class="sticky top-10 z-20 bg-base-300 h-10 text-right border-r border-base-200 align-middle border-b border-base-300">Reward P2</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $row)
                        <tr class="hover:bg-base-200/50 transition-colors group">
                            <td class="sticky left-0 {{ $loop->even ? 'bg-base-200' : 'bg-base-100' }} group-hover:bg-base-200/50 transition-colors border-r border-base-200/50 z-10 whitespace-normal min-w-[250px] align-middle py-2 px-3 shadow-[1px_0_0_rgba(0,0,0,0.05)]">
                                <div class="flex items-start gap-1.5">
                                    <button wire:click="toggleExpand('{{ $row['distributor_code'] }}')" class="btn btn-xs btn-ghost btn-circle text-base-content/50 hover:text-primary mt-0.5">
                                        @if($expandedDistributor === $row['distributor_code'])
                                            <x-heroicon-s-chevron-up class="w-4 h-4" />
                                        @else
                                            <x-heroicon-s-chevron-down class="w-4 h-4" />
                                        @endif
                                    </button>
                                    <div class="flex-1">
                                        @if($row['distributor'] !== '-')
                                            <div class="font-bold text-primary">{{ $row['distributor'] }}</div>
                                            <div class="text-[10px] text-base-content/60 font-semibold mt-0.5">
                                                {{ $row['region'] }} &bull; {{ $row['area'] }}<br>
                                                <span class="text-base-content/80">{{ $row['cabang'] }}</span>
                                            </div>
                                        @else
                                            <div class="font-bold text-primary">{{ $row['cabang'] }}</div>
                                            <div class="text-[10px] text-base-content/40 italic mt-0.5">Master data belum dilink</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            {{-- ================= PERIODE 1 ================= --}}
                            @php
                                $p1PctIn = $row['p1']['target'] > 0 ? ($row['p1']['sell_in'] / $row['p1']['target']) * 100 : 0;
                                $p1PctOut = $row['p1']['target'] > 0 ? ($row['p1']['sell_out'] / $row['p1']['target']) * 100 : 0;
                                $p1GapIn = max(0, $row['p1']['target'] - $row['p1']['sell_in']);
                                $p1GapOut = max(0, $row['p1']['target'] - $row['p1']['sell_out']);
                            @endphp
                            
                            {{-- Target --}}
                            <td class="text-right font-mono font-medium text-base-content/80 align-middle py-2 px-3">{{ number_format($row['p1']['target']) }}</td>
                            
                            {{-- Sell In & Gap --}}
                            @php $colorP1In = $p1PctIn >= 100 ? 'text-success' : ($p1PctIn >= 80 ? 'text-warning' : 'text-error'); @endphp
                            <td class="text-right align-middle py-2 px-3 min-w-[120px]">
                                <div class="font-mono font-bold text-base-content/90">{{ number_format($row['p1']['sell_in']) }}</div>
                                @if($row['p1']['target'] > 0)
                                    <div class="flex justify-end items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-bold {{ $colorP1In }}">{{ number_format($p1PctIn, 1) }}%</span>
                                    </div>
                                    @if($p1GapIn > 0)
                                        <div class="text-[10px] text-error font-semibold leading-tight mt-0.5">
                                            -{{ number_format($p1GapIn) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            
                            {{-- Sell Out & Gap --}}
                            @php $colorP1Out = $p1PctOut >= 100 ? 'text-success' : ($p1PctOut >= 80 ? 'text-warning' : 'text-error'); @endphp
                            <td class="text-right align-middle py-2 px-3 min-w-[120px]">
                                <div class="font-mono font-bold text-base-content/90">{{ number_format($row['p1']['sell_out']) }}</div>
                                @if($row['p1']['target'] > 0)
                                    <div class="flex justify-end items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-bold {{ $colorP1Out }}">{{ number_format($p1PctOut, 1) }}%</span>
                                    </div>
                                    @if($p1GapOut > 0)
                                        <div class="text-[10px] text-error font-semibold leading-tight mt-0.5">
                                            -{{ number_format($p1GapOut) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            
                            {{-- AR & Stock (Blockers) --}}
                            <td class="text-center align-middle py-2 px-2">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="badge badge-xs border-0 font-semibold {{ $row['p1']['is_ar_achieved'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}" title="AR Late > 7d: {{ $row['p1']['ar_late_count'] }}x">
                                        AR: {{ $row['p1']['ar_late_count'] }}
                                    </div>
                                    <div class="badge badge-xs border-0 font-semibold {{ $row['p1']['is_stock_achieved'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}" title="Avg Stock: {{ number_format($row['p1']['stock_avg'], 1) }}%">
                                        STK: {{ number_format($row['p1']['stock_avg'], 1) }}%
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Reward P1 --}}
                            <td class="text-right border-r-2 border-base-300 align-middle py-2 px-3">
                                @if($row['p1']['is_target_achieved'])
                                    @if(!$row['p1']['is_ar_achieved'] || !$row['p1']['is_stock_achieved'])
                                        <div class="text-warning text-[10px] line-through opacity-70">{{ number_format($row['p1']['base_reward']) }}</div>
                                        <div class="text-error font-bold font-mono text-sm" title="Terkena Penalty 25% karena gagal AR/Stock!">{{ number_format($row['p1']['final_reward']) }}</div>
                                        <div class="text-[9px] text-error mt-0.5 uppercase">-75% Penalti</div>
                                    @else
                                        <div class="text-success font-bold font-mono text-sm">{{ number_format($row['p1']['final_reward']) }}</div>
                                    @endif
                                @else
                                    <div class="text-base-content/30 font-mono text-sm">0</div>
                                @endif
                            </td>

                            {{-- ================= PERIODE 2 ================= --}}
                            @php
                                $p2PctIn = $row['p2']['target'] > 0 ? ($row['p2']['sell_in'] / $row['p2']['target']) * 100 : 0;
                                $p2PctOut = $row['p2']['target'] > 0 ? ($row['p2']['sell_out'] / $row['p2']['target']) * 100 : 0;
                                $p2GapIn = max(0, $row['p2']['target'] - $row['p2']['sell_in']);
                                $p2GapOut = max(0, $row['p2']['target'] - $row['p2']['sell_out']);
                            @endphp
                            
                            {{-- Target --}}
                            <td class="text-right font-mono font-medium text-base-content/80 align-middle py-2 px-3">{{ number_format($row['p2']['target']) }}</td>
                            
                            {{-- Sell In & Gap --}}
                            @php $colorP2In = $p2PctIn >= 100 ? 'text-success' : ($p2PctIn >= 80 ? 'text-warning' : 'text-error'); @endphp
                            <td class="text-right align-middle py-2 px-3 min-w-[120px]">
                                <div class="font-mono font-bold text-base-content/90">{{ number_format($row['p2']['sell_in']) }}</div>
                                @if($row['p2']['target'] > 0)
                                    <div class="flex justify-end items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-bold {{ $colorP2In }}">{{ number_format($p2PctIn, 1) }}%</span>
                                    </div>
                                    @if($p2GapIn > 0)
                                        <div class="text-[10px] text-error font-semibold leading-tight mt-0.5">
                                            -{{ number_format($p2GapIn) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            
                            {{-- Sell Out & Gap --}}
                            @php $colorP2Out = $p2PctOut >= 100 ? 'text-success' : ($p2PctOut >= 80 ? 'text-warning' : 'text-error'); @endphp
                            <td class="text-right align-middle py-2 px-3 min-w-[120px]">
                                <div class="font-mono font-bold text-base-content/90">{{ number_format($row['p2']['sell_out']) }}</div>
                                @if($row['p2']['target'] > 0)
                                    <div class="flex justify-end items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-bold {{ $colorP2Out }}">{{ number_format($p2PctOut, 1) }}%</span>
                                    </div>
                                    @if($p2GapOut > 0)
                                        <div class="text-[10px] text-error font-semibold leading-tight mt-0.5">
                                            -{{ number_format($p2GapOut) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            
                            {{-- AR & Stock (Blockers) --}}
                            <td class="text-center align-middle py-2 px-2">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="badge badge-xs border-0 font-semibold {{ $row['p2']['is_ar_achieved'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}" title="AR Late > 7d: {{ $row['p2']['ar_late_count'] }}x">
                                        AR: {{ $row['p2']['ar_late_count'] }}
                                    </div>
                                    <div class="badge badge-xs border-0 font-semibold {{ $row['p2']['is_stock_achieved'] ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}" title="Avg Stock: {{ number_format($row['p2']['stock_avg'], 1) }}%">
                                        STK: {{ number_format($row['p2']['stock_avg'], 1) }}%
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Reward P2 --}}
                            <td class="text-right border-r border-base-200/50 align-middle py-2 px-3">
                                @if($row['p2']['is_target_achieved'])
                                    @if(!$row['p2']['is_ar_achieved'] || !$row['p2']['is_stock_achieved'])
                                        <div class="text-warning text-[10px] line-through opacity-70">{{ number_format($row['p2']['base_reward']) }}</div>
                                        <div class="text-error font-bold font-mono text-sm" title="Terkena Penalty 25% karena gagal AR/Stock!">{{ number_format($row['p2']['final_reward']) }}</div>
                                        <div class="text-[9px] text-error mt-0.5 uppercase">-75% Penalti</div>
                                    @else
                                        <div class="text-success font-bold font-mono text-sm">{{ number_format($row['p2']['final_reward']) }}</div>
                                    @endif
                                @else
                                    <div class="text-base-content/30 font-mono text-sm">0</div>
                                @endif
                            </td>

                            {{-- ================= TOTAL ================= --}}
                            <td class="text-right font-bold font-mono sticky right-0 {{ $loop->even ? 'bg-base-200' : 'bg-base-100' }} group-hover:bg-base-200/50 transition-colors shadow-[inset_1px_0_0_rgba(0,0,0,0.02)] border-l border-base-300 z-10 align-middle px-4">
                                @if($row['total_reward'] > 0)
                                    <span class="text-primary text-base">Rp {{ number_format($row['total_reward']) }}</span>
                                @else
                                    <span class="text-base-content/30">Rp 0</span>
                                @endif
                            </td>
                        </tr>

                        {{-- EXPANDED MONTHLY SUB-ROWS --}}
                        @if($expandedDistributor === $row['distributor_code'])
                            @foreach($monthlyDetails as $index => $mRow)
                                @php
                                    // Gunakan warna biru super muda agar kontras dengan abu-abu (strip & header)
                                    $subBg = 'bg-blue-50';
                                @endphp
                                <tr class="{{ $subBg }} hover:bg-blue-100 transition-colors">
                                    {{-- Left Sticky Column: Month Names --}}
                                    <td class="sticky left-0 {{ $subBg }} border-r border-base-300 z-10 align-middle py-1.5 px-3 pl-6 shadow-[1px_0_0_rgba(0,0,0,0.05)] border-l-[3px] border-l-primary">
                                        <div class="text-[11px] font-bold text-base-content/70 flex items-center gap-2">
                                            <div class="w-4 border-b-2 border-l-2 border-base-content/20 h-4 -mt-2 rounded-bl-md"></div>
                                            {{ $mRow['p1_month'] }} &amp; {{ $mRow['p2_month'] }}
                                        </div>
                                    </td>

                                    {{-- PERIODE 1 --}}
                                    @php
                                        $p1PctIn = $mRow['p1']['target'] > 0 ? ($mRow['p1']['sell_in'] / $mRow['p1']['target']) * 100 : 0;
                                        $p1PctOut = $mRow['p1']['target'] > 0 ? ($mRow['p1']['sell_out'] / $mRow['p1']['target']) * 100 : 0;
                                        $p1GapIn = max(0, $mRow['p1']['target'] - $mRow['p1']['sell_in']);
                                        $p1GapOut = max(0, $mRow['p1']['target'] - $mRow['p1']['sell_out']);
                                        
                                        $colorP1InSub = $p1PctIn >= 100 ? 'text-success' : ($p1PctIn >= 80 ? 'text-warning' : 'text-error');
                                    @endphp
                                    <td class="text-right font-mono font-medium text-base-content/70 align-middle py-1.5 px-3">{{ number_format($mRow['p1']['target']) }}</td>
                                    
                                    {{-- Sell In P1 --}}
                                    <td class="text-right align-middle py-1.5 px-3">
                                        <div class="font-mono font-bold text-base-content/90">{{ number_format($mRow['p1']['sell_in']) }}</div>
                                        @if($mRow['p1']['target'] > 0)
                                            <div class="flex justify-end items-center gap-1 mt-0.5">
                                                <span class="text-[9px] font-bold {{ $colorP1InSub }}">{{ number_format($p1PctIn, 1) }}%</span>
                                            </div>
                                            @if($p1GapIn > 0)
                                                <div class="text-[9px] text-error font-semibold leading-tight mt-0.5">-{{ number_format($p1GapIn) }}</div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Sell Out P1 --}}
                                    @php $colorP1OutSub = $p1PctOut >= 100 ? 'text-success' : ($p1PctOut >= 80 ? 'text-warning' : 'text-error'); @endphp
                                    <td class="text-right align-middle py-1.5 px-3">
                                        <div class="font-mono font-bold text-base-content/90">{{ number_format($mRow['p1']['sell_out']) }}</div>
                                        @if($mRow['p1']['target'] > 0)
                                            <div class="flex justify-end items-center gap-1 mt-0.5">
                                                <span class="text-[9px] font-bold {{ $colorP1OutSub }}">{{ number_format($p1PctOut, 1) }}%</span>
                                            </div>
                                            @if($p1GapOut > 0)
                                                <div class="text-[9px] text-error font-semibold leading-tight mt-0.5">-{{ number_format($p1GapOut) }}</div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- AR/STK P1 --}}
                                    <td class="text-center align-middle py-1.5 px-2">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <div class="text-[9px] font-semibold {{ $mRow['p1']['ar_value'] <= 7 ? 'text-success' : 'text-warning' }}">
                                                AR: {{ number_format($mRow['p1']['ar_value']) }} Hari
                                            </div>
                                            <div class="text-[9px] font-semibold {{ $mRow['p1']['is_stock_achieved'] ? 'text-success' : 'text-error' }}">
                                                STK: {{ number_format($mRow['p1']['stock_avg'], 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right border-r-2 border-base-300 align-middle py-1.5 px-3">
                                        <div class="text-[10px] text-base-content/30 italic">-</div>
                                    </td>

                                    {{-- PERIODE 2 --}}
                                    @php
                                        $p2PctIn = $mRow['p2']['target'] > 0 ? ($mRow['p2']['sell_in'] / $mRow['p2']['target']) * 100 : 0;
                                        $p2PctOut = $mRow['p2']['target'] > 0 ? ($mRow['p2']['sell_out'] / $mRow['p2']['target']) * 100 : 0;
                                        $p2GapIn = max(0, $mRow['p2']['target'] - $mRow['p2']['sell_in']);
                                        $p2GapOut = max(0, $mRow['p2']['target'] - $mRow['p2']['sell_out']);
                                        
                                        $colorP2InSub = $p2PctIn >= 100 ? 'text-success' : ($p2PctIn >= 80 ? 'text-warning' : 'text-error');
                                    @endphp
                                    <td class="text-right font-mono font-medium text-base-content/70 align-middle py-1.5 px-3">{{ number_format($mRow['p2']['target']) }}</td>
                                    
                                    {{-- Sell In P2 --}}
                                    <td class="text-right align-middle py-1.5 px-3">
                                        <div class="font-mono font-bold text-base-content/90">{{ number_format($mRow['p2']['sell_in']) }}</div>
                                        @if($mRow['p2']['target'] > 0)
                                            <div class="flex justify-end items-center gap-1 mt-0.5">
                                                <span class="text-[9px] font-bold {{ $colorP2InSub }}">{{ number_format($p2PctIn, 1) }}%</span>
                                            </div>
                                            @if($p2GapIn > 0)
                                                <div class="text-[9px] text-error font-semibold leading-tight mt-0.5">-{{ number_format($p2GapIn) }}</div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Sell Out P2 --}}
                                    @php $colorP2OutSub = $p2PctOut >= 100 ? 'text-success' : ($p2PctOut >= 80 ? 'text-warning' : 'text-error'); @endphp
                                    <td class="text-right align-middle py-1.5 px-3">
                                        <div class="font-mono font-bold text-base-content/90">{{ number_format($mRow['p2']['sell_out']) }}</div>
                                        @if($mRow['p2']['target'] > 0)
                                            <div class="flex justify-end items-center gap-1 mt-0.5">
                                                <span class="text-[9px] font-bold {{ $colorP2OutSub }}">{{ number_format($p2PctOut, 1) }}%</span>
                                            </div>
                                            @if($p2GapOut > 0)
                                                <div class="text-[9px] text-error font-semibold leading-tight mt-0.5">-{{ number_format($p2GapOut) }}</div>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- AR/STK P2 --}}
                                    <td class="text-center align-middle py-1.5 px-2">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <div class="text-[9px] font-semibold {{ $mRow['p2']['ar_value'] <= 7 ? 'text-success' : 'text-warning' }}">
                                                AR: {{ number_format($mRow['p2']['ar_value']) }} Hari
                                            </div>
                                            <div class="text-[9px] font-semibold {{ $mRow['p2']['is_stock_achieved'] ? 'text-success' : 'text-error' }}">
                                                STK: {{ number_format($mRow['p2']['stock_avg'], 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right border-r border-base-200/50 align-middle py-1.5 px-3">
                                        <div class="text-[10px] text-base-content/30 italic">-</div>
                                    </td>

                                    {{-- Right Sticky Column: Total --}}
                                    <td class="sticky right-0 {{ $subBg }} border-l border-base-300 z-10 align-middle px-4 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                        {{-- Empty since reward is not monthly --}}
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    @empty
                        <tr>
                            <td colspan="12" class="text-center p-8 text-base-content/50">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-12 h-12 text-base-300" />
                                    <p>Belum ada data target untuk reguler di tahun {{ $year }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>
    {{-- Import Modal --}}
    @if($isImportModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-base-100 rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-4">Import Data AR & Stok</h3>
                
                <div class="alert bg-blue-50 text-blue-800 border border-blue-200 mb-4 p-3 rounded-md text-sm">
                    <div class="flex flex-col gap-2">
                        <span>Pastikan Anda mengisi data menggunakan template terbaru.</span>
                        <button wire:click="downloadTemplate" class="btn btn-xs btn-primary self-start">
                            <x-heroicon-s-arrow-down-tray class="w-3 h-3" /> Download Template
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="processImport" class="flex flex-col gap-4">
                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold">Pilih Bulan & Tahun</span></label>
                        <input type="month" wire:model="importMonth" class="input input-bordered w-full" required />
                        @error('importMonth') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control w-full">
                        <label class="label"><span class="label-text font-semibold">File Excel (.xlsx)</span></label>
                        <input type="file" wire:model="importFile" class="file-input file-input-bordered w-full" accept=".xlsx,.xls,.csv" required />
                        <div wire:loading wire:target="importFile" class="text-xs text-primary mt-1">Mengunggah...</div>
                        @error('importFile') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="modal-action mt-6">
                        <button type="button" wire:click="closeImportModal" class="btn btn-ghost">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="processImport">Proses Import</span>
                            <span wire:loading wire:target="processImport" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
