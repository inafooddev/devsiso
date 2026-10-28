{{-- 4 EXECUTIVE KPI SUMMARY CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 shrink-0">
    
    {{-- Card 1: Total Sell In --}}
    <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Total Sell In</p>
                @if($unitMode === 'ktn')
                    <h3 class="text-xl lg:text-2xl font-bold tracking-tight text-base-content mt-1.5 tabular-nums">
                        {{ number_format($kpi['total_in_ktn'], 1, ',', '.') }} <span class="text-sm font-semibold text-base-content/60">Ktn</span>
                    </h3>
                @else
                    <h3 class="text-xl lg:text-2xl font-bold tracking-tight text-base-content mt-1.5 tabular-nums">
                        Rp {{ number_format($kpi['total_in_val'], 0, ',', '.') }}
                    </h3>
                @endif
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-base-200/70 flex items-center justify-between gap-2 flex-wrap text-xs">
            @if($unitMode === 'val')
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-md font-semibold {{ ($kpi['ach_in_pct'] ?? 0) >= 100 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-base-200 text-base-content/70' }}">
                        Ach {{ $kpi['ach_in_pct'] ?? 0 }}%
                    </span>
                    @if(isset($kpi['growth_in_mom']) && $kpi['growth_in_mom'] !== null)
                        <span class="px-2 py-0.5 rounded-md font-semibold {{ $kpi['growth_in_mom'] >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                            MoM {{ $kpi['growth_in_mom'] >= 0 ? '+' : '' }}{{ $kpi['growth_in_mom'] }}%
                        </span>
                    @endif
                </div>
                <span class="font-semibold text-base-content/60 tabular-nums">{{ number_format($kpi['total_in_ktn'], 1, ',', '.') }} Ktn</span>
            @else
                <span class="text-base-content/50 font-medium">Nilai Transaksi</span>
                <span class="font-semibold text-base-content/80 tabular-nums">Rp {{ number_format($kpi['total_in_val'], 0, ',', '.') }}</span>
            @endif
        </div>
    </div>

    {{-- Card 2: Total Sell Out --}}
    <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Total Sell Out</p>
                @if($unitMode === 'ktn')
                    <h3 class="text-xl lg:text-2xl font-bold tracking-tight text-base-content mt-1.5 tabular-nums">
                        {{ number_format($kpi['total_out_ktn'], 1, ',', '.') }} <span class="text-sm font-semibold text-base-content/60">Ktn</span>
                    </h3>
                @else
                    <h3 class="text-xl lg:text-2xl font-bold tracking-tight text-base-content mt-1.5 tabular-nums">
                        Rp {{ number_format($kpi['total_out_val'], 0, ',', '.') }}
                    </h3>
                @endif
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-base-200/70 flex items-center justify-between gap-2 flex-wrap text-xs">
            @if($unitMode === 'val')
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-md font-semibold {{ ($kpi['ach_out_pct'] ?? 0) >= 100 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-base-200 text-base-content/70' }}">
                        Ach {{ $kpi['ach_out_pct'] ?? 0 }}%
                    </span>
                    @if(isset($kpi['growth_out_mom']) && $kpi['growth_out_mom'] !== null)
                        <span class="px-2 py-0.5 rounded-md font-semibold {{ $kpi['growth_out_mom'] >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                            MoM {{ $kpi['growth_out_mom'] >= 0 ? '+' : '' }}{{ $kpi['growth_out_mom'] }}%
                        </span>
                    @endif
                </div>
                <span class="font-semibold text-base-content/60 tabular-nums">{{ number_format($kpi['total_out_ktn'], 1, ',', '.') }} Ktn</span>
            @else
                <span class="text-base-content/50 font-medium">Nilai Transaksi</span>
                <span class="font-semibold text-base-content/80 tabular-nums">Rp {{ number_format($kpi['total_out_val'], 0, ',', '.') }}</span>
            @endif
        </div>
    </div>

    {{-- Card 3: Target Depo (Mode Rupiah) / Selisih (Mode Karton) --}}
    @if($unitMode === 'val')
        @php
            $targetGap = $kpi['total_in_val'] - ($kpi['total_target_val'] ?? 0);
        @endphp
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Target Depo</p>
                        <span class="px-1.5 py-0.2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded text-xs font-bold">
                            {{ $selectedRegFest === 'ALL' ? 'ALL' : $selectedRegFest }}
                        </span>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold tracking-tight text-base-content mt-1.5 tabular-nums">
                        Rp {{ number_format($kpi['total_target_val'] ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-flag class="w-5 h-5" />
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-base-200/70 flex items-center justify-between gap-2 text-xs">
                <span class="px-2 py-0.5 rounded-md font-semibold {{ ($kpi['ach_in_pct'] ?? 0) >= 100 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }}">
                    {{ ($kpi['ach_in_pct'] ?? 0) >= 100 ? 'Tercapai' : 'Belum Capai' }} ({{ $kpi['ach_in_pct'] ?? 0 }}%)
                </span>
                <span class="font-semibold tabular-nums {{ $targetGap >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    {{ $targetGap >= 0 ? '+' : '' }}Rp {{ number_format($targetGap, 0, ',', '.') }}
                </span>
            </div>
        </div>
    @else
        <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Selisih (In - Out)</p>
                    <h3 class="text-xl lg:text-2xl font-bold tracking-tight text-base-content mt-1.5 tabular-nums">
                        {{ $kpi['net_gap_ktn'] >= 0 ? '+' : '' }}{{ number_format($kpi['net_gap_ktn'], 1, ',', '.') }} <span class="text-sm font-semibold text-base-content/60">Ktn</span>
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-scale class="w-5 h-5" />
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-base-200/70 flex items-center justify-between gap-2 text-xs">
                <span class="px-2 py-0.5 rounded-md font-semibold {{ $kpi['net_gap_ktn'] >= 0 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-sky-500/10 text-sky-600 dark:text-sky-400' }}">
                    {{ $kpi['net_gap_ktn'] >= 0 ? 'Sell In > Out' : 'Sell Out > In' }}
                </span>
                <span class="font-semibold text-base-content/70 tabular-nums">
                    {{ $kpi['net_gap_val'] >= 0 ? '+' : '' }}Rp {{ number_format($kpi['net_gap_val'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    @endif

    {{-- Card 4: Rasio (Out / In) --}}
    <div class="bg-base-100 rounded-2xl p-5 border border-base-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-base-content/50">Rasio Out / In</p>
                <h3 class="text-xl lg:text-2xl font-bold tracking-tight text-base-content mt-1.5 tabular-nums">
                    {{ $kpi['overall_ratio'] }}%
                </h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center shrink-0">
                <x-heroicon-o-chart-pie class="w-5 h-5" />
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-base-200/70 flex items-center justify-between gap-3 text-xs">
            <span class="px-2 py-0.5 rounded-md font-semibold shrink-0 {{ $kpi['overall_ratio'] >= 75 && $kpi['overall_ratio'] <= 125 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : ($kpi['overall_ratio'] > 125 ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400') }}">
                {{ $kpi['overall_ratio'] >= 75 && $kpi['overall_ratio'] <= 125 ? 'Seimbang' : ($kpi['overall_ratio'] > 125 ? 'Sell Out Tinggi' : 'Sell In Tinggi') }}
            </span>
            <div class="w-full max-w-[100px] bg-base-200 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full {{ $kpi['overall_ratio'] >= 75 && $kpi['overall_ratio'] <= 125 ? 'bg-emerald-500' : ($kpi['overall_ratio'] > 125 ? 'bg-sky-500' : 'bg-amber-500') }}" 
                     style="width: {{ min($kpi['overall_ratio'], 100) }}%"></div>
            </div>
        </div>
    </div>

</div>
