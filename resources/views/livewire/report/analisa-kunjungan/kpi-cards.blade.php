        @php
            $kpiTotalVisitTarget = $dataKunjungan->count();
            $kpiTotalVisitActual = $dataKunjungan->where('flag_visit', 'Y')->count();
            $kpiTotalVisitGap = max(0, $kpiTotalVisitTarget - $kpiTotalVisitActual);
            $kpiTotalVisitPercent = $kpiTotalVisitTarget > 0 ? round(($kpiTotalVisitActual / $kpiTotalVisitTarget) * 100) : 0;

            $kpiTotalOrderTarget = $dataKunjungan->sum('target');
            $kpiTotalOrderActual = $dataKunjungan->sum('val_order');
            $kpiTotalOrderGap = max(0, $kpiTotalOrderTarget - $kpiTotalOrderActual);
            $kpiTotalOrderPercent = $kpiTotalOrderTarget > 0 ? round(($kpiTotalOrderActual / $kpiTotalOrderTarget) * 100) : 0;

            $kpiRwoTarget = $dataKunjungan->where('pilar', '1. RWO')->count();
            $kpiRwoActual = $dataKunjungan->where('pilar', '1. RWO')->where('flag_visit', 'Y')->count();
            $kpiRwoGap = max(0, $kpiRwoTarget - $kpiRwoActual);
            $kpiRwoPercent = $kpiRwoTarget > 0 ? round(($kpiRwoActual / $kpiRwoTarget) * 100) : 0;

            $kpiPnrTarget = $dataKunjungan->where('pilar', '2. PNR')->count();
            $kpiPnrActual = $dataKunjungan->where('pilar', '2. PNR')->where('flag_visit', 'Y')->count();
            $kpiPnrGap = max(0, $kpiPnrTarget - $kpiPnrActual);
            $kpiPnrPercent = $kpiPnrTarget > 0 ? round(($kpiPnrActual / $kpiPnrTarget) * 100) : 0;

            $kpiNgvoTarget = $dataKunjungan->where('pilar', '3. NGVO')->count();
            $kpiNgvoActual = $dataKunjungan->where('pilar', '3. NGVO')->where('flag_visit', 'Y')->count();
            $kpiNgvoGap = max(0, $kpiNgvoTarget - $kpiNgvoActual);
            $kpiNgvoPercent = $kpiNgvoTarget > 0 ? round(($kpiNgvoActual / $kpiNgvoTarget) * 100) : 0;
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0 mb-3 md:mb-4 lg:mb-6">
            {{-- KPI: Total Visit --}}
            <div class="bg-base-100 border border-base-200 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
                <x-heroicon-s-check-circle class="absolute -right-4 -top-2 w-20 h-20 text-purple-50 opacity-60 pointer-events-none" />
                <div>
                    <div class="flex items-center gap-2 mb-1 relative z-10">
                        <div class="w-4 h-4 rounded bg-purple-100 text-purple-600 flex items-center justify-center">
                            <x-heroicon-s-check class="w-3 h-3" />
                        </div>
                        <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">Total Visit</span>
                    </div>
                    <div class="text-xl font-extrabold text-purple-600 relative z-10">
                        {{ number_format($kpiTotalVisitActual) }} <span class="text-xs text-base-content/40 font-semibold">/ {{ number_format($kpiTotalVisitTarget) }}</span>
                    </div>
                </div>
                <div class="mt-3 relative z-10">
                    <div class="w-full h-1 bg-base-200 rounded-full mb-1.5">
                        <div class="h-1 bg-purple-600 rounded-full" style="width: {{ $kpiTotalVisitPercent }}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-bold">
                        <span class="text-base-content/60">GAP: <span class="text-error">{{ number_format($kpiTotalVisitGap) }}</span></span>
                        <span class="text-purple-600">{{ $kpiTotalVisitPercent }}%</span>
                    </div>
                </div>
            </div>

            {{-- KPI: Total Order --}}
            <div class="bg-base-100 border border-base-200 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
                <x-heroicon-s-banknotes class="absolute -right-4 -top-2 w-20 h-20 text-emerald-50 opacity-60 pointer-events-none" />
                <div>
                    <div class="flex items-center gap-2 mb-1 relative z-10">
                        <div class="w-4 h-4 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <x-heroicon-s-banknotes class="w-3 h-3" />
                        </div>
                        <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">Total Order</span>
                    </div>
                    <div class="text-xl font-extrabold text-emerald-600 relative z-10">
                        {{ number_format($kpiTotalOrderActual) }} <span class="text-xs text-base-content/40 font-semibold">/ {{ number_format($kpiTotalOrderTarget) }}</span>
                    </div>
                </div>
                <div class="mt-3 relative z-10">
                    <div class="w-full h-1 bg-base-200 rounded-full mb-1.5">
                        <div class="h-1 bg-emerald-600 rounded-full" style="width: {{ $kpiTotalOrderPercent }}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-bold">
                        <span class="text-base-content/60">GAP: <span class="text-error">{{ number_format($kpiTotalOrderGap) }}</span></span>
                        <span class="text-emerald-600">{{ $kpiTotalOrderPercent }}%</span>
                    </div>
                </div>
            </div>

            {{-- KPI: Visit 1. RWO --}}
            <div class="bg-base-100 border border-base-200 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
                <x-heroicon-s-shopping-bag class="absolute -right-4 -top-2 w-20 h-20 text-sky-50 opacity-60 pointer-events-none" />
                <div>
                    <div class="flex items-center gap-2 mb-1 relative z-10">
                        <div class="w-4 h-4 rounded bg-sky-100 text-sky-600 flex items-center justify-center">
                            <x-heroicon-s-shopping-bag class="w-3 h-3" />
                        </div>
                        <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">Visit 1. RWO</span>
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
                        <span class="text-base-content/60">GAP: <span class="text-error">{{ number_format($kpiRwoGap) }}</span></span>
                        <span class="text-sky-600">{{ $kpiRwoPercent }}%</span>
                    </div>
                </div>
            </div>

            {{-- KPI: Visit 2. PNR --}}
            <div class="bg-base-100 border border-base-200 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
                <x-heroicon-s-archive-box class="absolute -right-4 -top-2 w-20 h-20 text-slate-100 opacity-60 pointer-events-none" />
                <div>
                    <div class="flex items-center gap-2 mb-1 relative z-10">
                        <div class="w-4 h-4 rounded bg-slate-100 text-slate-600 flex items-center justify-center">
                            <x-heroicon-s-archive-box class="w-3 h-3" />
                        </div>
                        <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">Visit 2. PNR</span>
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
                        <span class="text-base-content/60">GAP: <span class="text-error">{{ number_format($kpiPnrGap) }}</span></span>
                        <span class="text-slate-600">{{ $kpiPnrPercent }}%</span>
                    </div>
                </div>
            </div>

            {{-- KPI: Visit 3. NGVO --}}
            <div class="bg-base-100 border border-base-200 rounded-xl p-3 relative overflow-hidden flex flex-col justify-between shadow-sm">
                <x-heroicon-s-star class="absolute -right-4 -top-2 w-20 h-20 text-orange-50 opacity-60 pointer-events-none" />
                <div>
                    <div class="flex items-center gap-2 mb-1 relative z-10">
                        <div class="w-4 h-4 rounded bg-orange-100 text-orange-500 flex items-center justify-center">
                            <x-heroicon-s-star class="w-3 h-3" />
                        </div>
                        <span class="text-[10px] font-bold text-base-content/60 tracking-widest uppercase">Visit 3. NGVO</span>
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
                        <span class="text-base-content/60">GAP: <span class="text-error">{{ number_format($kpiNgvoGap) }}</span></span>
                        <span class="text-orange-500">{{ $kpiNgvoPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>
