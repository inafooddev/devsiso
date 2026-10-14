@php
    $stats = $kpiStats ?? [];
    
    $totalCall = $stats['total_call'] ?? 0;
    $pc = $stats['pc'] ?? 0;
    $xc = $stats['xc'] ?? 0;
    $pcCallPercent = $stats['pc_call_percent'] ?? 0;
    $xcCallPercent = $stats['xc_call_percent'] ?? 0;

    $totalVisit = $stats['total_visit'] ?? 0;
    $visitAc = $stats['visit_ac'] ?? 0;
    $visitAcPercent = $stats['visit_ac_percent'] ?? 0;
    $visitXc = $stats['visit_xc'] ?? 0;
    $visitXcPercent = $stats['visit_xc_percent'] ?? 0;
    $visitPercent = $stats['total_visit_percent'] ?? 0;

    $totalEc = $stats['total_ec'] ?? 0;
    $ecPc = $stats['ec_pc'] ?? 0;
    $ecPcPercent = $stats['ec_pc_percent'] ?? 0;
    $ecXc = $stats['ec_xc'] ?? 0;
    $ecXcPercent = $stats['ec_xc_percent'] ?? 0;
    $totalEcPercent = $stats['total_ec_percent'] ?? 0;

    $totalOrderVal = $stats['total_order_val'] ?? 0;
    $valPc = $stats['val_pc'] ?? 0;
    $valXc = $stats['val_xc'] ?? 0;
    $aov = $stats['aov'] ?? 0;
    $valPcPercent = $totalOrderVal > 0 ? round(($valPc / $totalOrderVal) * 100, 1) : 0;
    $valXcPercent = $totalOrderVal > 0 ? round(($valXc / $totalOrderVal) * 100, 1) : 0;

    $totalWorkStr = $stats['total_work_str'] ?? '0 Jam';
    $timeInStorePercent = $stats['time_in_store_percent'] ?? 0;
    $consumeBadge = $stats['consume_badge'] ?? '0j (avg 0m)';
    $travelBadge = $stats['travel_badge'] ?? '0j (avg 0m)';

    if (!function_exists('formatCompactRupiah')) {
        function formatCompactRupiah($val) {
            if ($val >= 1000000000) {
                return 'Rp ' . number_format($val / 1000000000, 1, ',', '.') . ' M';
            } elseif ($val >= 1000000) {
                return 'Rp ' . number_format($val / 1000000, 1, ',', '.') . ' Jt';
            } elseif ($val >= 1000) {
                return 'Rp ' . number_format($val / 1000, 0, ',', '.') . ' Rb';
            }
            return 'Rp ' . number_format($val, 0, ',', '.');
        }
    }
@endphp

{{-- 5 KPI Cards Section - Ultra Compact & Symmetrical Enterprise Layout --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2.5 sm:gap-3 md:gap-3.5 shrink-0 mb-3 md:mb-4">
    {{-- Card 1: Total Plan Call --}}
    <div class="bg-base-100 p-2.5 sm:p-3 rounded-xl shadow-sm border border-base-200/80 flex flex-col justify-between relative overflow-hidden group hover:border-slate-300 transition-colors">
        <div class="flex items-center justify-between text-[10px] font-bold tracking-wider text-base-content/50 uppercase">
            <span class="truncate">Total Plan Call</span>
            <div class="w-6 h-6 rounded-lg bg-slate-500/10 flex items-center justify-center text-slate-600 shrink-0">
                <x-heroicon-s-clipboard-document-list class="w-3.5 h-3.5" />
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-1">
            <div class="text-base sm:text-lg font-extrabold tracking-tight text-slate-800">
                {{ number_format($totalCall) }} <span class="text-[10px] font-normal text-base-content/40">Toko</span>
            </div>
            <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">Plan Call</span>
        </div>
        <div class="w-full h-1 bg-base-200 rounded-full my-1.5 overflow-hidden flex">
            <div class="h-full bg-slate-500 rounded-l-full" style="width: {{ $pcCallPercent }}%" title="PC: {{ $pcCallPercent }}%"></div>
            <div class="h-full bg-amber-400 rounded-r-full" style="width: {{ $xcCallPercent }}%" title="XC: {{ $xcCallPercent }}%"></div>
        </div>
        <div class="flex items-center justify-between text-[10px] font-semibold">
            <span class="text-slate-700 flex items-center gap-1" title="Plan Call (Flag PJP = R)"><span class="w-1.5 h-1.5 rounded-full bg-slate-500 inline-block"></span>PC: {{ number_format($pc) }} <span class="font-normal text-[9px] text-base-content/50">({{ $pcCallPercent }}%)</span></span>
            <span class="text-amber-700 flex items-center gap-1" title="Extra Call (Flag PJP = N)"><span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>XC: {{ number_format($xc) }} <span class="font-normal text-[9px] text-base-content/50">({{ $xcCallPercent }}%)</span></span>
        </div>
    </div>

    {{-- Card 2: Actual Visit (AC) --}}
    <div class="bg-base-100 p-2.5 sm:p-3 rounded-xl shadow-sm border border-base-200/80 flex flex-col justify-between relative overflow-hidden group hover:border-primary/40 transition-colors">
        <div class="flex items-center justify-between text-[10px] font-bold tracking-wider text-base-content/50 uppercase">
            <span class="truncate">Actual Visit (AC)</span>
            <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-1">
            <div class="text-base sm:text-lg font-extrabold tracking-tight {{ $visitAcPercent >= 100 ? 'text-primary' : 'text-error' }}">
                {{ number_format($totalVisit) }} <span class="text-[10px] font-normal text-base-content/40">/ {{ number_format($totalCall) }}</span>
            </div>
            @php
                $visitBadgeClass = $visitAcPercent >= 100 ? 'bg-success text-white' : 'bg-error text-white';
            @endphp
            <span class="text-[10px] font-bold {{ $visitBadgeClass }} px-1.5 py-0.5 rounded shadow-2xs font-mono" title="Ketercapaian Total AC vs Total PC">{{ $visitAcPercent }}% AC</span>
        </div>
        <div class="w-full h-1 bg-base-200 rounded-full my-1.5 overflow-hidden">
            <div class="h-full {{ $visitAcPercent >= 100 ? 'bg-primary' : 'bg-error' }} rounded-full transition-all duration-300" style="width: {{ min(100, $visitAcPercent) }}%"></div>
        </div>
        <div class="flex items-center justify-between text-[10px] font-semibold">
            <span class="text-primary truncate" title="Actual Call dari PJP ({{ number_format($visitAc) }} dari {{ number_format($pc) }})">AC: {{ number_format($visitAc) }} <span class="font-normal text-[9px] text-primary/70">({{ $visitAcPercent }}%)</span></span>
            <span class="text-amber-700 truncate" title="Extra Call Visit ({{ number_format($visitXc) }} dari {{ number_format($xc) }})">XC: {{ number_format($visitXc) }} <span class="font-normal text-[9px] text-amber-700/70">({{ $visitXcPercent }}%)</span></span>
        </div>
    </div>

    {{-- Card 3: Effective Call (EC) --}}
    <div class="bg-base-100 p-2.5 sm:p-3 rounded-xl shadow-sm border border-base-200/80 flex flex-col justify-between relative overflow-hidden group hover:border-emerald-400 transition-colors">
        <div class="flex items-center justify-between text-[10px] font-bold tracking-wider text-base-content/50 uppercase">
            <span class="truncate">Effective Call (EC)</span>
            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                <x-heroicon-s-shopping-bag class="w-3.5 h-3.5" />
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-1">
            <div class="text-base sm:text-lg font-extrabold tracking-tight {{ $totalEcPercent >= 100 ? 'text-emerald-600' : ($totalEcPercent >= 80 ? 'text-amber-600' : 'text-error') }}">
                {{ number_format($totalEc) }} <span class="text-[10px] font-normal text-base-content/40">/ {{ number_format($totalVisit) }}</span>
            </div>
            @php
                $ecCardBadgeClass = $totalEcPercent >= 100 ? 'bg-success text-white' : ($totalEcPercent >= 80 ? 'bg-warning text-slate-900 font-bold' : 'bg-error text-white');
            @endphp
            <span class="text-[10px] font-bold {{ $ecCardBadgeClass }} px-1.5 py-0.5 rounded shadow-2xs font-mono" title="Konversi EC terhadap Visit">{{ $totalEcPercent }}% EC</span>
        </div>
        <div class="w-full h-1 bg-base-200 rounded-full my-1.5 overflow-hidden">
            @php
                $ecCardBarClass = $totalEcPercent >= 100 ? 'bg-emerald-500' : ($totalEcPercent >= 80 ? 'bg-warning' : 'bg-error');
            @endphp
            <div class="h-full {{ $ecCardBarClass }} rounded-full transition-all duration-300" style="width: {{ min(100, $totalEcPercent) }}%"></div>
        </div>
        <div class="flex items-center justify-between text-[10px] font-semibold">
            <span class="text-emerald-700 truncate" title="Order dari Plan Call ({{ number_format($ecPc) }} dari {{ number_format($visitAc) }})">EC: {{ number_format($ecPc) }} <span class="font-normal text-[9px] text-emerald-700/70">({{ $ecPcPercent }}%)</span></span>
            <span class="text-amber-700 truncate" title="Order dari Extra Call ({{ number_format($ecXc) }} dari {{ number_format($visitXc) }})">XC: {{ number_format($ecXc) }} <span class="font-normal text-[9px] text-amber-700/70">({{ $ecXcPercent }}%)</span></span>
        </div>
    </div>

    {{-- Card 4: Total Value Order --}}
    <div class="bg-base-100 p-2.5 sm:p-3 rounded-xl shadow-sm border border-base-200/80 flex flex-col justify-between relative overflow-hidden group hover:border-blue-400 transition-colors">
        <div class="flex items-center justify-between text-[10px] font-bold tracking-wider text-base-content/50 uppercase">
            <span class="truncate">Total Value Order</span>
            <div class="w-6 h-6 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-600 shrink-0">
                <x-heroicon-s-currency-dollar class="w-3.5 h-3.5" />
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-1">
            <div class="text-base sm:text-lg font-extrabold tracking-tight text-blue-600 font-mono" title="Rp {{ number_format($totalOrderVal, 0, ',', '.') }}">
                {{ formatCompactRupiah($totalOrderVal) }}
            </div>
            <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded" title="Average Order Value per Toko EC">AOV: {{ formatCompactRupiah($aov) }}</span>
        </div>
        <div class="w-full h-1 bg-base-200 rounded-full my-1.5 overflow-hidden flex">
            <div class="h-full bg-blue-600 rounded-l-full" style="width: {{ $valPcPercent }}%" title="PC: {{ $valPcPercent }}%"></div>
            <div class="h-full bg-amber-400 rounded-r-full" style="width: {{ $valXcPercent }}%" title="XC: {{ $valXcPercent }}%"></div>
        </div>
        <div class="flex items-center justify-between text-[10px] font-semibold">
            <span class="text-blue-700 truncate" title="Nilai Order Plan Call: Rp {{ number_format($valPc, 0, ',', '.') }}"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 inline-block mr-0.5"></span>PC: {{ formatCompactRupiah($valPc) }}</span>
            <span class="text-amber-700 truncate" title="Nilai Order Extra Call: Rp {{ number_format($valXc, 0, ',', '.') }}"><span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block mr-0.5"></span>XC: {{ formatCompactRupiah($valXc) }}</span>
        </div>
    </div>

    {{-- Card 5: Total Waktu Operasional --}}
    <div class="bg-base-100 p-2.5 sm:p-3 rounded-xl shadow-sm border border-base-200/80 flex flex-col justify-between relative overflow-hidden group hover:border-amber-400 transition-colors">
        <div class="flex items-center justify-between text-[10px] font-bold tracking-wider text-base-content/50 uppercase">
            <span class="truncate">Waktu Operasional</span>
            <div class="w-6 h-6 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                <x-heroicon-s-clock class="w-3.5 h-3.5" />
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-1">
            <div class="text-base sm:text-lg font-extrabold tracking-tight text-amber-600">
                {{ $totalWorkStr }} <span class="text-[10px] font-normal text-base-content/40">Kerja</span>
            </div>
            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded" title="Persentase Waktu di Toko">{{ $timeInStorePercent }}% Toko</span>
        </div>
        <div class="w-full h-1 bg-base-200 rounded-full my-1.5 overflow-hidden flex">
            <div class="h-full bg-amber-500 rounded-l-full" style="width: {{ min(100, $timeInStorePercent) }}%" title="Di Toko: {{ $timeInStorePercent }}%"></div>
            <div class="h-full bg-slate-300 rounded-r-full" style="width: {{ max(0, 100 - $timeInStorePercent) }}%" title="Di Jalan: {{ 100 - $timeInStorePercent }}%"></div>
        </div>
        <div class="flex items-center justify-between text-[10px] font-semibold">
            <span class="text-emerald-700 truncate" title="Total durasi dan rata-rata waktu di dalam toko">Toko: {{ $consumeBadge }}</span>
            <span class="text-slate-600 truncate" title="Total durasi dan rata-rata perjalanan antar toko">Jalan: {{ $travelBadge }}</span>
        </div>
    </div>
</div>
