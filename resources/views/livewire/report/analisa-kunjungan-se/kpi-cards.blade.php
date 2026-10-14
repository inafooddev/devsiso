@php
    $stats = $kpiStats ?? [];
    
    $totalCall = $stats['total_call'] ?? 0;
    $pc = $stats['pc'] ?? 0;
    $xc = $stats['xc'] ?? 0;

    $totalVisit = $stats['total_visit'] ?? 0;
    $visitAc = $stats['visit_ac'] ?? 0;
    $visitAcPercent = $stats['visit_ac_percent'] ?? 0;
    $visitXc = $stats['visit_xc'] ?? 0;
    $visitXcPercent = $stats['visit_xc_percent'] ?? 0;
    $visitPercent = $stats['total_visit_percent'] ?? 0;
    $visitGap = max(0, $totalCall - $totalVisit);

    $totalEc = $stats['total_ec'] ?? 0;
    $ecPc = $stats['ec_pc'] ?? 0;
    $ecPcPercent = $stats['ec_pc_percent'] ?? 0;
    $ecXc = $stats['ec_xc'] ?? 0;
    $ecXcPercent = $stats['ec_xc_percent'] ?? 0;
    $ecPercent = $stats['total_ec_percent'] ?? 0;

    $totalOrderVal = $stats['total_order_val'] ?? 0;

    $outOfArea = $stats['out_of_area'] ?? 0;
    $outOfAreaPercent = $stats['out_of_area_percent'] ?? 0;
@endphp

{{-- 5 KPI Cards Section Sesuai Standar Template --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0 mb-3 md:mb-4 lg:mb-6">
    {{-- Card 1: Total Plan Call --}}
    <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-slate-500/10 transition-transform group-hover:scale-150"></div>
        <div class="flex items-start justify-between relative z-10">
            <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Plan Call</h3>
            <div class="w-8 h-8 rounded-xl bg-slate-500/10 flex items-center justify-center text-slate-600 shrink-0">
                <x-heroicon-s-clipboard-document-list class="w-4 h-4" />
            </div>
        </div>
        <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-slate-700">
            {{ number_format($totalCall) }} <span class="text-xs text-base-content/40 font-normal">Toko</span>
        </div>
        <div class="mt-2 flex items-center gap-1.5 text-[10px] md:text-[11px] font-semibold relative z-10">
            <span class="text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200" title="Plan Call (Flag PJP = R)">PC: {{ number_format($pc) }}</span>
            <span class="text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200" title="Extra Call (Flag PJP = N)">XC: {{ number_format($xc) }}</span>
        </div>
    </div>

    {{-- Card 2: Actual Visit (AC) --}}
    <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
        <div class="flex items-start justify-between relative z-10">
            <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Actual Visit (AC)</h3>
            <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <x-heroicon-s-check-circle class="w-4 h-4" />
            </div>
        </div>
        <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">
            {{ number_format($totalVisit) }} <span class="text-xs text-base-content/40 font-normal">/ {{ number_format($totalCall) }}</span>
        </div>
        <div class="mt-2 flex flex-col gap-1 relative z-10">
            <div class="w-full h-1.5 bg-base-200 rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all duration-300" style="width: {{ min(100, $visitAcPercent) }}%"></div>
            </div>
            <div class="flex justify-between items-center text-[10px]">
                <span class="text-base-content/50 font-medium">% AC vs PC:</span>
                <span class="text-primary font-bold">{{ $visitAcPercent }}%</span>
            </div>
            <div class="flex items-center gap-1.5 text-[10px] md:text-[11px] font-semibold mt-0.5">
                <span class="text-primary bg-primary/10 px-1.5 py-0.5 rounded border border-primary/20 truncate" title="Actual Call dari PJP ({{ number_format($visitAc) }} dari {{ number_format($pc) }})">AC: {{ number_format($visitAc) }} <span class="font-normal text-[9px] md:text-[10px]">({{ $visitAcPercent }}%)</span></span>
                <span class="text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 truncate" title="Extra Call Visit ({{ number_format($visitXc) }} dari {{ number_format($xc) }})">XC: {{ number_format($visitXc) }} <span class="font-normal text-[9px] md:text-[10px]">({{ $visitXcPercent }}%)</span></span>
            </div>
        </div>
    </div>

    {{-- Card 3: Effective Call (EC) --}}
    <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-emerald-500/10 transition-transform group-hover:scale-150"></div>
        <div class="flex items-start justify-between relative z-10">
            <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Effective Call (EC)</h3>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                <x-heroicon-s-shopping-bag class="w-4 h-4" />
            </div>
        </div>
        <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-emerald-600">
            {{ number_format($totalEc) }} <span class="text-xs text-base-content/40 font-normal">/ {{ number_format($totalVisit) }}</span>
        </div>
        <div class="mt-2 flex flex-col gap-1 relative z-10">
            <div class="w-full h-1.5 bg-base-200 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" style="width: {{ min(100, $ecPercent) }}%"></div>
            </div>
            <div class="flex justify-between items-center text-[10px]">
                <span class="text-base-content/50 font-medium">% EC vs Visit:</span>
                <span class="text-emerald-600 font-bold">{{ $ecPercent }}%</span>
            </div>
            <div class="flex items-center gap-1.5 text-[10px] md:text-[11px] font-semibold mt-0.5">
                <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 truncate" title="Order dari Plan Call ({{ number_format($ecPc) }} dari {{ number_format($visitAc) }})">EC: {{ number_format($ecPc) }} <span class="font-normal text-[9px] md:text-[10px]">({{ $ecPcPercent }}%)</span></span>
                <span class="text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 truncate" title="Order dari Extra Call ({{ number_format($ecXc) }} dari {{ number_format($visitXc) }})">XC: {{ number_format($ecXc) }} <span class="font-normal text-[9px] md:text-[10px]">({{ $ecXcPercent }}%)</span></span>
            </div>
        </div>
    </div>

    {{-- Card 4: Total Value Order --}}
    <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-blue-500/10 transition-transform group-hover:scale-150"></div>
        <div class="flex items-start justify-between relative z-10">
            <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Value Order</h3>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 shrink-0">
                <x-heroicon-s-currency-dollar class="w-4 h-4" />
            </div>
        </div>
        <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-blue-600 font-mono" title="Rp {{ number_format($totalOrderVal, 0, ',', '.') }}">
            Rp {{ number_format($totalOrderVal, 0, ',', '.') }}
        </div>
        <div class="mt-2 text-[10px] text-base-content/50 font-medium relative z-10 truncate">
            Total nominal order SE
        </div>
    </div>

    {{-- Card 5: Out of Area (> 50m) --}}
    <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-rose-500/10 transition-transform group-hover:scale-150"></div>
        <div class="flex items-start justify-between relative z-10">
            <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Out of Area (>50m)</h3>
            <div class="w-8 h-8 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-600 shrink-0">
                <x-heroicon-s-map-pin class="w-4 h-4" />
            </div>
        </div>
        <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-rose-600">
            {{ number_format($outOfArea) }} <span class="text-xs text-base-content/40 font-normal">Check-in</span>
        </div>
        <div class="mt-2 relative z-10">
            <div class="w-full h-1.5 bg-base-200 rounded-full mb-1 overflow-hidden">
                <div class="h-full bg-rose-500 rounded-full transition-all duration-300" style="width: {{ min(100, $outOfAreaPercent) }}%"></div>
            </div>
            <div class="flex justify-between items-center text-[10px]">
                <span class="text-base-content/50 font-medium">% Dari Visit:</span>
                <span class="text-rose-600 font-bold">{{ $outOfAreaPercent }}%</span>
            </div>
        </div>
    </div>
</div>
