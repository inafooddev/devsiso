{{-- 4 KARTU RINGKASAN SELL IN VS SELL OUT --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 shrink-0">
    
    {{-- Card 1: Total Sell In --}}
    <div class="bg-base-100 rounded-xl p-3.5 md:p-4 border border-base-300 border-l-4 border-l-primary shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                Total Sell In
            </span>
            <span class="badge badge-sm badge-primary font-bold text-primary-content">Sell In</span>
        </div>
        <div class="mt-2.5">
            <p class="text-xs text-base-content/60 uppercase font-medium tracking-wider">Total Nilai (Rp)</p>
            <p class="text-base md:text-xl font-bold tracking-tight text-primary">
                Rp {{ number_format($kpi['total_in_val'], 0, ',', '.') }}
            </p>
            @if($unitMode === 'val')
                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                    <span class="badge badge-xs {{ ($kpi['ach_in_pct'] ?? 0) >= 100 ? 'badge-success text-success-content' : 'badge-ghost text-base-content/70' }} font-semibold">
                        Ach: {{ $kpi['ach_in_pct'] ?? 0 }}%
                    </span>
                    @if(isset($kpi['growth_in_mom']) && $kpi['growth_in_mom'] !== null)
                        <span class="badge badge-xs {{ $kpi['growth_in_mom'] >= 0 ? 'badge-success text-success-content' : 'badge-error text-error-content' }} font-semibold">
                            MoM: {{ $kpi['growth_in_mom'] >= 0 ? '+' : '' }}{{ $kpi['growth_in_mom'] }}%
                        </span>
                    @endif
                </div>
            @endif
        </div>
        <div class="mt-2 pt-2 border-t border-base-200 flex items-center justify-between text-xs">
            <span class="text-base-content/60 font-medium">Volume:</span>
            <span class="font-bold text-base-content">{{ number_format($kpi['total_in_ktn'], 1, ',', '.') }} Ktn</span>
        </div>
    </div>

    {{-- Card 2: Total Sell Out --}}
    <div class="bg-base-100 rounded-xl p-3.5 md:p-4 border border-base-300 border-l-4 border-l-secondary shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-secondary flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-secondary"></span>
                Total Sell Out
            </span>
            <span class="badge badge-sm badge-secondary font-bold text-secondary-content">Sell Out</span>
        </div>
        <div class="mt-2.5">
            <p class="text-xs text-base-content/60 uppercase font-medium tracking-wider">Total Nilai (Rp)</p>
            <p class="text-base md:text-xl font-bold tracking-tight text-secondary">
                Rp {{ number_format($kpi['total_out_val'], 0, ',', '.') }}
            </p>
            @if($unitMode === 'val')
                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                    <span class="badge badge-xs {{ ($kpi['ach_out_pct'] ?? 0) >= 100 ? 'badge-success text-success-content' : 'badge-ghost text-base-content/70' }} font-semibold">
                        Ach: {{ $kpi['ach_out_pct'] ?? 0 }}%
                    </span>
                    @if(isset($kpi['growth_out_mom']) && $kpi['growth_out_mom'] !== null)
                        <span class="badge badge-xs {{ $kpi['growth_out_mom'] >= 0 ? 'badge-success text-success-content' : 'badge-error text-error-content' }} font-semibold">
                            MoM: {{ $kpi['growth_out_mom'] >= 0 ? '+' : '' }}{{ $kpi['growth_out_mom'] }}%
                        </span>
                    @endif
                </div>
            @endif
        </div>
        <div class="mt-2 pt-2 border-t border-base-200 flex items-center justify-between text-xs">
            <span class="text-base-content/60 font-medium">Volume:</span>
            <span class="font-bold text-base-content">{{ number_format($kpi['total_out_ktn'], 1, ',', '.') }} Ktn</span>
        </div>
    </div>

    {{-- Card 3: Target Depo (Mode Rupiah) / Selisih (Mode Karton) --}}
    @if($unitMode === 'val')
        <div class="bg-base-100 rounded-xl p-3.5 md:p-4 border border-base-300 border-l-4 border-l-indigo-500 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-500 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Target Penjualan (Depo)
                </span>
                <span class="badge badge-sm badge-outline text-indigo-500 border-indigo-400 font-bold">Target</span>
            </div>
            <div class="mt-2.5">
                <p class="text-xs text-base-content/60 uppercase font-medium tracking-wider">Total Target Bulan Ini</p>
                <p class="text-base md:text-xl font-bold tracking-tight text-indigo-600">
                    Rp {{ number_format($kpi['total_target_val'] ?? 0, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="text-xs font-semibold {{ ($kpi['total_in_val'] - ($kpi['total_target_val'] ?? 0)) >= 0 ? 'text-success' : 'text-error' }}">
                        Gap: {{ ($kpi['total_in_val'] - ($kpi['total_target_val'] ?? 0)) >= 0 ? '+' : '' }}Rp {{ number_format($kpi['total_in_val'] - ($kpi['total_target_val'] ?? 0), 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-base-content/60 font-medium">Status Target:</span>
                <span class="font-bold {{ ($kpi['ach_in_pct'] ?? 0) >= 100 ? 'text-success' : 'text-warning' }}">
                    {{ ($kpi['ach_in_pct'] ?? 0) >= 100 ? 'Tercapai (' . ($kpi['ach_in_pct'] ?? 0) . '%)' : 'Belum Capai (' . ($kpi['ach_in_pct'] ?? 0) . '%)' }}
                </span>
            </div>
        </div>
    @else
        <div class="bg-base-100 rounded-xl p-3.5 md:p-4 border border-base-300 border-l-4 {{ $kpi['net_gap_ktn'] >= 0 ? 'border-l-warning' : 'border-l-info' }} shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider {{ $kpi['net_gap_ktn'] >= 0 ? 'text-warning' : 'text-info' }} flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full {{ $kpi['net_gap_ktn'] >= 0 ? 'bg-warning' : 'bg-info' }}"></span>
                    Selisih (In - Out)
                </span>
                <span class="badge badge-sm {{ $kpi['net_gap_ktn'] >= 0 ? 'badge-warning' : 'badge-info' }} font-bold">
                    {{ $kpi['net_gap_ktn'] >= 0 ? 'In > Out' : 'Out > In' }}
                </span>
            </div>
            <div class="mt-2.5">
                <p class="text-xs text-base-content/60 uppercase font-medium tracking-wider">Selisih Nilai (Rp)</p>
                <p class="text-base md:text-xl font-bold tracking-tight {{ $kpi['net_gap_val'] >= 0 ? 'text-warning' : 'text-info' }}">
                    {{ $kpi['net_gap_val'] >= 0 ? '+' : '' }}Rp {{ number_format($kpi['net_gap_val'], 0, ',', '.') }}
                </p>
            </div>
            <div class="mt-2 pt-2 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-base-content/60 font-medium">Selisih Volume:</span>
                <span class="font-bold {{ $kpi['net_gap_ktn'] >= 0 ? 'text-warning' : 'text-info' }}">
                    {{ $kpi['net_gap_ktn'] >= 0 ? '+' : '' }}{{ number_format($kpi['net_gap_ktn'], 1, ',', '.') }} Ktn
                </span>
            </div>
        </div>
    @endif

    {{-- Card 4: Rasio (Out / In) --}}
    <div class="bg-base-100 rounded-xl p-3.5 md:p-4 border border-base-300 border-l-4 border-l-accent shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-accent flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-accent"></span>
                Rasio (Out / In)
            </span>
            <span class="badge badge-sm {{ $kpi['overall_ratio'] >= 90 && $kpi['overall_ratio'] <= 110 ? 'badge-success text-success-content' : ($kpi['overall_ratio'] > 110 ? 'badge-info text-info-content' : 'badge-warning text-warning-content') }} font-bold">
                {{ $kpi['overall_ratio'] >= 90 && $kpi['overall_ratio'] <= 110 ? 'Seimbang' : ($kpi['overall_ratio'] > 110 ? 'Sell Out Tinggi' : 'Sell In Tinggi') }}
            </span>
        </div>
        <div class="mt-2.5">
            <p class="text-xs text-base-content/60 uppercase font-medium tracking-wider">Persentase</p>
            <p class="text-base md:text-xl font-bold tracking-tight text-accent">
                {{ $kpi['overall_ratio'] }}%
            </p>
        </div>
        <div class="mt-2 pt-2 border-t border-base-200 flex items-center justify-between text-xs">
            <span class="text-base-content/60 font-medium">Titik 100%:</span>
            <span class="font-bold text-success flex items-center gap-1">
                Sell In = Sell Out
            </span>
        </div>
    </div>

</div>