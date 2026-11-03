<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-3.5 w-full h-full overflow-y-auto pr-0.5 pb-4">
    <x-slot name="title">Semarak Rejeki - Monitoring & Action Plan Reward SPV</x-slot>

    {{-- Loading Overlay --}}
    <div wire:loading.flex class="fixed inset-0 z-50 bg-base-100/60 backdrop-blur-md items-center justify-center">
        <div class="flex flex-col items-center gap-3 bg-base-100 p-8 rounded-3xl shadow-2xl border border-base-300 animate-in fade-in zoom-in duration-200">
            <span class="loading loading-ring loading-lg text-primary"></span>
            <div class="text-center">
                <span class="text-sm font-bold text-base-content block">Sinkronisasi Data Penjualan</span>
                <span class="text-xs text-base-content/60">Mengkalkulasi realisasi selling out & milestone reward...</span>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    @if ($alertSuccess)
        <div class="alert alert-success shadow-lg text-white text-xs py-2.5 px-4 rounded-2xl flex justify-between items-center bg-gradient-to-r from-success to-emerald-600 border-none" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-semibold text-xs">{{ $alertSuccess }}</span>
            </div>
            <button class="btn btn-ghost btn-xs btn-circle text-white hover:bg-white/20" @click="show = false; $wire.set('alertSuccess', null)">✕</button>
        </div>
    @endif
    @if ($alertError)
        <div class="alert alert-error shadow-lg text-white text-xs py-2.5 px-4 rounded-2xl flex justify-between items-center bg-gradient-to-r from-error to-rose-600 border-none" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <span class="font-semibold text-xs">{{ $alertError }}</span>
            </div>
            <button class="btn btn-ghost btn-xs btn-circle text-white hover:bg-white/20" @click="show = false; $wire.set('alertError', null)">✕</button>
        </div>
    @endif

    {{-- 1. Executive KPI Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        {{-- Card 1: Peserta & Jangkauan --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Total Peserta & Depo</span>
                <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-base-content font-mono">{{ $totalSpv }}</span>
                    <span class="text-xs text-base-content/60 font-semibold">Supervisor</span>
                    <span class="text-xs text-base-content/40">•</span>
                    <span class="text-xs text-emerald-600 font-semibold">{{ $totalActiveDist }} Depo Aktif</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 pt-1 border-t border-base-200">
                @if(in_array('jawa', $allowedWilayahs))
                    <button type="button" wire:click="$set('filterWilayah', '{{ $filterWilayah === 'jawa' ? '' : 'jawa' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterWilayah === 'jawa' ? 'btn-primary' : 'btn-ghost bg-base-200/80 hover:bg-base-200' }}" title="Klik untuk filter wilayah Jawa">
                        Jawa: {{ $jawaCount }}
                    </button>
                @endif
                @if(in_array('pulau', $allowedWilayahs))
                    <button type="button" wire:click="$set('filterWilayah', '{{ $filterWilayah === 'pulau' ? '' : 'pulau' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterWilayah === 'pulau' ? 'btn-primary' : 'btn-ghost bg-base-200/80 hover:bg-base-200' }}" title="Klik untuk filter wilayah Pulau">
                        Pulau: {{ $pulauCount }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Card 2: Status Kelayakan (Quick Drilldown) --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Tingkat Kelayakan</span>
                <div class="w-8 h-8 rounded-lg bg-success/10 text-success flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold font-mono {{ $eligibleCount > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $eligibleCount }}</span>
                    <span class="text-xs text-base-content/60 font-semibold">SPV Lolos</span>
                    <span class="text-xs font-mono font-bold text-base-content/50">({{ $eligiblePct }}%)</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 pt-1 border-t border-base-200">
                <button type="button" wire:click="$set('filterStatus', '{{ $filterStatus === 'on_track' ? 'semua' : 'on_track' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterStatus === 'on_track' ? 'btn-success text-white' : 'bg-emerald-500/15 text-emerald-700 hover:bg-emerald-500/25 border-emerald-500/20' }}" title="Klik untuk filter SPV Aman">
                    {{ $onTrackCount }} Aman
                </button>
                <button type="button" wire:click="$set('filterStatus', '{{ $filterStatus === 'warning' ? 'semua' : 'warning' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterStatus === 'warning' ? 'btn-warning text-white' : 'bg-amber-500/15 text-amber-700 hover:bg-amber-500/25 border-amber-500/20' }}" title="Klik untuk filter SPV Kejar Target">
                    {{ $warningCount }} Kejar
                </button>
                <button type="button" wire:click="$set('filterStatus', '{{ $filterStatus === 'hangus' ? 'semua' : 'hangus' }}')" class="btn btn-xs rounded-lg text-xs font-bold {{ $filterStatus === 'hangus' ? 'btn-error text-white' : 'bg-rose-500/15 text-rose-700 hover:bg-rose-500/25 border-rose-500/20' }}" title="Klik untuk filter SPV Hangus">
                    {{ $hangusCount }} Hangus
                </button>
            </div>
        </div>

        {{-- Card 3: Penjualan Karton & Omset --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Volume & Nilai Penjualan</span>
                <span class="badge badge-info badge-sm font-bold font-mono text-xs">{{ $overallPct }}%</span>
            </div>
            <div>
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-base-content font-mono">{{ number_format($totalActualKarton, 0, ',', '.') }}</span>
                    <span class="text-xs text-base-content/50 font-semibold font-mono">/ {{ number_format($totalTargetKarton, 0, ',', '.') }} Ktn</span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-primary h-1.5 rounded-full" style="width: {{ min(100, $overallPct) }}%"></div>
                </div>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-base-200 text-xs">
                <span class="text-base-content/60 font-medium">Omset:</span>
                <span class="font-mono font-bold text-primary">Rp {{ $totalActualRpFmt }} <span class="text-base-content/40 font-normal">/ {{ $totalTargetRpFmt }}</span></span>
            </div>
        </div>

        {{-- Card 4: Sisa Hari Kerja (Tanpa Minggu) --}}
        <div class="bg-base-100 p-4 rounded-2xl shadow-sm border border-base-300 flex flex-col justify-between gap-2.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Sisa Hari Kerja (Nov)</span>
                <div class="w-8 h-8 rounded-lg bg-warning/10 text-warning flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-warning font-mono">{{ $daysInfo['remaining_days'] }}</span>
                    <span class="text-xs text-base-content/60 font-semibold">Hari Aktif</span>
                    <span class="text-xs text-base-content/40">•</span>
                    <span class="text-xs text-base-content/60 font-semibold">{{ $daysInfo['eval_month_name'] }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-base-200 text-xs">
                <span class="text-base-content/60">Hari ke-{{ $daysInfo['current_working_day'] }} dari {{ $daysInfo['total_working_days'] }} hari kerja</span>
                <span class="badge badge-ghost badge-sm text-xs font-semibold text-base-content/60 border border-base-300">Excl. Minggu</span>
            </div>
        </div>
    </div>

    {{-- 2. Filter Toolbar (Chain Filter: Wilayah Promo -> Region -> Area) --}}
    <div class="bg-base-100 p-3.5 md:p-4 rounded-2xl border border-base-300 shadow-sm flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-3">
        <div class="flex flex-wrap items-center gap-2">
            {{-- Step 1: Wilayah Promo --}}
            <select wire:model.live="filterWilayah"
                    @disabled(count($allowedWilayahs) <= 1)
                    class="select select-sm select-bordered rounded-xl bg-base-100 text-xs font-medium disabled:opacity-60 disabled:cursor-not-allowed">
                @if(count($allowedWilayahs) > 1)
                    <option value="">Semua Wilayah Promo</option>
                @endif
                @foreach($wilayahList as $key => $lbl)
                    <option value="{{ $key }}">{{ $lbl }}</option>
                @endforeach
            </select>

            {{-- Step 2: Region (Tergantung pada Wilayah jika Wilayah dipilih) --}}
            <select wire:model.live="filterRegion"
                    @disabled(count($allowedWilayahs) <= 1 && count($regions) <= 1)
                    class="select select-sm select-bordered rounded-xl bg-base-100 text-xs font-medium w-40 disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">Semua Region</option>
                @foreach($regions as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>

            {{-- Step 3: Area (Aktif setelah Region dipilih) --}}
            <select wire:model.live="filterArea"
                    @disabled(empty($filterRegion) || (count($allowedWilayahs) <= 1 && count($regions) <= 1 && count($areas) <= 1))
                    class="select select-sm select-bordered rounded-xl bg-base-100 text-xs font-medium w-44 disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">{{ empty($filterRegion) ? 'Pilih Region Dulu' : 'Semua Area' }}</option>
                @foreach($areas as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" class="select select-sm select-bordered rounded-xl bg-base-100 text-xs font-medium">
                <option value="semua">Semua Status</option>
                <option value="on_track">🟢 Aman (On Track)</option>
                <option value="warning">🟡 Perlu Kejar Target</option>
                <option value="hangus">🔴 Hangus / Gugur</option>
                <option value="upgrade_ready">🟣 Auto Upgrade Ready</option>
            </select>

            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari SPV / Area / Hadiah..." class="input input-sm input-bordered rounded-xl pl-8 pr-3 text-xs w-48 sm:w-56 bg-base-100" />
                <svg class="w-4 h-4 absolute left-2.5 top-2 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            @if((count($allowedWilayahs) > 1 && !empty($filterWilayah)) || !empty($filterRegion) || !empty($filterArea) || $filterStatus !== 'semua' || !empty($search))
                <button type="button" wire:click="resetFilters" class="btn btn-sm btn-ghost text-error hover:bg-error/10 rounded-xl text-xs font-bold gap-1 px-2.5" title="Reset Semua Filter">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>Reset</span>
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2 self-end lg:self-auto">
            <button wire:click="openTierModal" class="btn btn-sm btn-ghost border border-base-300 rounded-xl font-bold gap-1.5 text-xs hover:bg-base-200">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Master Hadiah</span>
            </button>

            <button wire:click="refreshData" title="Refresh Kalkulasi" class="btn btn-sm btn-ghost border border-base-300 rounded-xl text-xs gap-1 font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <button wire:click="exportExcel" class="btn btn-sm btn-outline btn-success rounded-xl text-xs gap-1 font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="hidden sm:inline">Export Excel</span>
            </button>
        </div>
    </div>

    {{-- 3. MODE 1 BARIS 1 SPV (FULL-WIDTH MODERN CARD ROWS) --}}
    <div class="flex flex-col gap-3">
        @forelse($spvList as $row)
            @php
                $initials = collect(explode(' ', $row['supervisor_name']))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('');
                $isHangus = $row['status_key'] === 'hangus';
                $isUpgrade = $row['status_key'] === 'upgrade_ready';
                $isWarning = $row['gap_milestone'] > 0;
                
                // Border accent and subtle background tint
                if ($isHangus) {
                    $rowBorder = 'border-rose-300 dark:border-rose-900 bg-rose-50/20 dark:bg-rose-950/10';
                    $indicatorColor = 'bg-rose-500';
                } elseif ($isUpgrade) {
                    $rowBorder = 'border-indigo-300 dark:border-indigo-900 bg-indigo-50/20 dark:bg-indigo-950/10';
                    $indicatorColor = 'bg-indigo-600';
                } elseif ($isWarning) {
                    $rowBorder = 'border-amber-300 dark:border-amber-900 bg-amber-50/20 dark:bg-amber-950/10';
                    $indicatorColor = 'bg-amber-500';
                } else {
                    $rowBorder = 'border-emerald-300 dark:border-emerald-900 bg-emerald-50/20 dark:bg-emerald-950/10';
                    $indicatorColor = 'bg-emerald-500';
                }
            @endphp

            {{-- 1 BARIS 1 SPV ROW CARD --}}
            <div class="bg-base-100 rounded-2xl border {{ $rowBorder }} shadow-sm hover:shadow-md transition-all duration-200 p-3.5 md:p-4 flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-3.5 relative overflow-hidden">
                
                {{-- Status Vertical Bar Indicator --}}
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $indicatorColor }}"></div>

                {{-- Bagian 1: Profil SPV & Wilayah (Fixed Width 288px on XL) --}}
                <div class="flex items-center gap-3 w-full xl:w-72 shrink-0 pl-1.5 overflow-hidden">
                    {{-- Avatar Initials --}}
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs text-white shadow-sm shrink-0 {{ $row['kategori_wilayah'] === 'jawa' ? 'bg-primary' : 'bg-emerald-600' }}">
                        {{ $initials }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-sm text-base-content leading-tight truncate" title="{{ $row['supervisor_name'] }}">
                            {{ $row['supervisor_name'] }}
                        </h3>
                        <div class="flex items-center gap-1.5 mt-1 min-w-0">
                            @if($row['kategori_wilayah'] === 'jawa')
                                <span class="badge badge-info badge-outline badge-xs text-xs font-semibold px-1.5 py-2 shrink-0">
                                    Jawa
                                </span>
                            @else
                                <span class="badge badge-success badge-outline badge-xs text-xs font-semibold px-1.5 py-2 shrink-0">
                                    Pulau
                                </span>
                            @endif
                            <span class="text-xs text-base-content/60 font-mono truncate" title="{{ $row['supervisor_code'] }}">
                                {{ $row['supervisor_code'] }}
                            </span>
                        </div>
                        <div class="text-xs font-medium flex items-center gap-1.5 mt-1 truncate">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-emerald-600 font-semibold shrink-0">{{ $row['distributor_count'] }} Depo</span>
                            <span class="text-base-content/30">•</span>
                            <span class="text-base-content/60 truncate" title="{{ $row['area_name'] }}">{{ $row['area_name'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Bagian 2: Paket Awal & Hadiah (Fixed Width 256px on XL) --}}
                @php
                    $cat = $row['reward_category'] ?? 'gift';
                    $catStyle = match($cat) {
                        'motor'  => 'bg-rose-500/15 text-rose-600',
                        'laptop' => 'bg-indigo-500/15 text-indigo-600',
                        'tv'     => 'bg-purple-500/15 text-purple-600',
                        'tablet' => 'bg-cyan-500/15 text-cyan-600',
                        'phone'  => 'bg-blue-500/15 text-blue-600',
                        'watch'  => 'bg-emerald-500/15 text-emerald-600',
                        'audio'  => 'bg-amber-500/15 text-amber-600',
                        default  => 'bg-primary/10 text-primary',
                    };
                @endphp
                <div class="flex items-center gap-2.5 w-full xl:w-64 shrink-0 p-2.5 rounded-xl bg-base-200/50 border border-base-300 self-stretch overflow-hidden">
                    <div class="w-9 h-9 rounded-xl {{ $catStyle }} flex items-center justify-center shrink-0">
                        @if($cat === 'motor')
                            {{-- Icon Motor --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <circle cx="5.5" cy="17.5" r="2.5"></circle>
                                <circle cx="18.5" cy="17.5" r="2.5"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 6h2l3 7.5M5.5 17.5L8 11h5l2 4.5h3.5M8 11l-1.5-3H4"></path>
                            </svg>
                        @elseif($cat === 'laptop')
                            {{-- Icon Laptop --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <rect x="3" y="4" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2 20h20"></path>
                            </svg>
                        @elseif($cat === 'tv')
                            {{-- Icon Smart TV --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <rect x="2" y="4" width="20" height="13" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4"></path>
                            </svg>
                        @elseif($cat === 'tablet')
                            {{-- Icon Tablet --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <rect x="4" y="2" width="16" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01"></path>
                            </svg>
                        @elseif($cat === 'phone')
                            {{-- Icon Smartphone / HP --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <rect x="6" y="2" width="12" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 18h2"></path>
                            </svg>
                        @elseif($cat === 'watch')
                            {{-- Icon Smartwatch --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <rect x="7" y="6" width="10" height="12" rx="2.5"></rect>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6V3h6v3M9 18v3h6v-3M12 10v2l1.5 1.5"></path>
                            </svg>
                        @elseif($cat === 'audio')
                            {{-- Icon Audio / Earbuds / Headphone --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                            </svg>
                        @else
                            {{-- Default Gift Icon --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <div class="flex items-center gap-1 min-w-0">
                                <span class="badge badge-primary badge-sm font-bold text-xs shrink-0">Paket {{ $row['kode_paket_awal'] }}</span>
                                @if($row['is_upgraded'])
                                    <span class="badge badge-accent badge-sm text-white font-bold text-xs shrink-0" title="Auto Upgrade!">
                                        UP: {{ $row['paket_tercapai'] }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-base-content font-mono whitespace-nowrap">{{ number_format($row['target_3bln'], 0, ',', '.') }} Ktn</span>
                        </div>
                        <span class="text-xs font-medium text-base-content/80 truncate block mt-1" title="{{ $row['nama_hadiah_awal'] }}">
                            {{ $row['nama_hadiah_awal'] }}
                        </span>
                    </div>
                </div>

                {{-- Bagian 3: 3-Stage Milestone Stepper (Fixed Width 384px on XL) --}}
                <div class="grid grid-cols-3 gap-2 w-full xl:w-96 shrink-0 self-stretch">
                    {{-- Okt --}}
                    <div class="p-2 rounded-xl border {{ $row['is_m1_pass'] ? 'bg-success/5 border-success/30' : 'bg-error/5 border-error/30' }} text-center flex flex-col justify-center">
                        <div class="flex items-center justify-center gap-1 text-xs font-bold whitespace-nowrap {{ $row['is_m1_pass'] ? 'text-success' : 'text-error' }}">
                            @if($row['is_m1_pass'])
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            @else
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            @endif
                            <span>Okt</span>
                        </div>
                        <div class="text-xs font-bold text-base-content font-mono mt-0.5 whitespace-nowrap">
                            {{ number_format($row['realisasi_m1'], 0, ',', '.') }} Ktn
                        </div>
                        <div class="text-xs font-bold mt-0.5 whitespace-nowrap {{ $row['is_m1_pass'] ? 'text-success' : 'text-error' }}">
                            {{ $row['pct_m1'] }}% <span class="text-base-content/40 font-normal">({{ number_format($row['m1_min_70'], 0, ',', '.') }})</span>
                        </div>
                    </div>

                    {{-- Okt-Nov --}}
                    <div class="p-2 rounded-xl border {{ $row['is_kum2_pass'] ? 'bg-success/5 border-success/30' : 'bg-warning/5 border-warning/30' }} text-center flex flex-col justify-center">
                        <div class="text-xs font-bold whitespace-nowrap {{ $row['is_kum2_pass'] ? 'text-success' : 'text-warning' }}">
                            Okt-Nov
                        </div>
                        <div class="text-xs font-bold text-base-content font-mono mt-0.5 whitespace-nowrap">
                            {{ number_format($row['realisasi_kum2'], 0, ',', '.') }} Ktn
                        </div>
                        <div class="text-xs font-bold mt-0.5 whitespace-nowrap {{ $row['is_kum2_pass'] ? 'text-success' : 'text-warning' }}">
                            {{ $row['pct_kum2'] }}% <span class="text-base-content/40 font-normal">({{ number_format($row['kum2_min_80'], 0, ',', '.') }})</span>
                        </div>
                    </div>

                    {{-- Okt-Des --}}
                    <div class="p-2 rounded-xl border bg-base-200/50 border-base-300 text-center flex flex-col justify-center">
                        <div class="text-xs font-bold text-base-content/60 whitespace-nowrap">Okt-Des</div>
                        <div class="text-xs font-bold text-base-content font-mono mt-0.5 whitespace-nowrap">
                            {{ number_format($row['total_realisasi_3bln'], 0, ',', '.') }} Ktn
                        </div>
                        <div class="text-xs font-bold text-primary mt-0.5 whitespace-nowrap">
                            {{ $row['pct_total'] }}%
                        </div>
                    </div>
                </div>

                {{-- Bagian 4: Action Plan & Prescriptive Guidance --}}
                <div class="flex-1 min-w-0 p-2.5 px-3.5 rounded-xl border text-xs self-stretch flex flex-col justify-center {{ $isHangus ? 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40 text-rose-900 dark:text-rose-200' : ($row['gap_milestone'] > 0 ? 'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/40 text-amber-900 dark:text-amber-200' : 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40 text-emerald-900 dark:text-emerald-200') }}">
                    <div class="flex items-center justify-between gap-2 mb-0.5">
                        <span class="font-bold text-xs uppercase tracking-wide flex items-center gap-1.5 whitespace-nowrap">
                            @if($isHangus)
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span>GUGUR / HANGUS</span>
                            @elseif($row['gap_milestone'] > 0)
                                <svg class="w-4 h-4 text-amber-600 shrink-0 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>PANDUAN KEJAR TARGET</span>
                            @else
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>AMAN (ON TRACK)</span>
                            @endif
                        </span>

                        @if($row['gap_milestone'] > 0)
                            <span class="font-bold text-xs text-rose-600 font-mono whitespace-nowrap">
                                Kurang {{ number_format($row['gap_milestone'], 1, ',', '.') }} Ktn
                            </span>
                        @endif
                    </div>
                    <p class="text-xs leading-snug font-normal line-clamp-2" title="{{ $row['action_advice'] }}">
                        {{ $row['action_advice'] }}
                    </p>
                </div>

                {{-- Bagian 5: Action CTA Buttons --}}
                <div class="flex items-center justify-end gap-1.5 self-end xl:self-center shrink-0">
                    <button wire:click="openActionModal('{{ $row['supervisor_code'] }}')" class="btn btn-sm btn-primary rounded-xl font-bold gap-1 shadow-sm shadow-primary/20 text-xs">
                        <span>Detail</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    @hasanyrole('admin|admspm|spvspm')
                        <button wire:click="openEditTargetModal({{ $row['target_id'] }})" class="btn btn-sm btn-ghost border border-base-300 rounded-xl text-xs" title="Edit Target SPV">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                    @endhasanyrole
                </div>

            </div>
        @empty
            <div class="py-16 text-center bg-base-100 rounded-2xl border border-base-300 p-8 shadow-sm">
                <div class="w-14 h-14 rounded-full bg-base-200 mx-auto flex items-center justify-center text-base-content/40 mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h4 class="font-bold text-sm text-base-content">Tidak Ada Data Supervisor</h4>
                <p class="text-xs text-base-content/60 mt-1">Coba sesuaikan filter wilayah, status, atau kata kunci pencarian Anda.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination Bar --}}
    <div class="p-4 bg-base-100 rounded-2xl border border-base-300 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-3 text-xs">
        <span class="text-base-content/60 font-medium">
            Menampilkan {{ $spvList->firstItem() ?? 0 }} - {{ $spvList->lastItem() ?? 0 }} dari {{ $spvList->total() }} Supervisor
        </span>
        <div>
            {{ $spvList->links() }}
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL 1: SPV ACTION PLAN & DISTRIBUTOR OPPORTUNITY CENTER --}}
    {{-- ======================================================== --}}
    @if($isActionModalOpen && $selectedSpvDetail)
        <div class="modal modal-open items-center justify-center p-2 sm:p-4" style="z-index: 99999 !important;">
            <div class="modal-box w-11/12 max-w-5xl bg-base-100 border border-base-300 shadow-2xl p-0 rounded-2xl overflow-hidden flex flex-col max-h-[92vh]" style="max-width: 64rem !important; width: 95% !important;">
                
                {{-- Modal Header --}}
                <div class="p-5 md:p-6 bg-gradient-to-r from-primary via-indigo-600 to-indigo-700 text-white flex justify-between items-center shrink-0">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge badge-sm bg-white/20 text-white font-bold text-xs uppercase tracking-wider border-none">
                                Action Center
                            </span>
                            <span class="badge badge-sm bg-amber-400 text-amber-950 font-bold font-mono text-xs border-none">
                                Paket {{ $selectedSpvDetail['kode_paket_awal'] }}
                            </span>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mt-1 tracking-tight">{{ $selectedSpvDetail['supervisor_name'] }}</h3>
                        <p class="text-xs text-white/80 mt-0.5 font-medium">
                            {{ $selectedSpvDetail['supervisor_code'] }} • {{ $selectedSpvDetail['area_name'] }} ({{ $selectedSpvDetail['region_name'] }})
                        </p>
                    </div>
                    <button wire:click="closeActionModal" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" aria-label="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 md:p-6 overflow-y-auto flex-1 flex flex-col gap-4 bg-base-200/40">
                    
                    {{-- 1. RAPOR EVALUASI DETAIL 3 TAHAP (Okt, Okt-Nov, Okt-Des) --}}
                    @php
                        $hargaPerKtn = 220500;
                        $diffM1 = $selectedSpvDetail['realisasi_m1'] - $selectedSpvDetail['m1_min_70'];
                        $diffKum2 = $selectedSpvDetail['realisasi_kum2'] - $selectedSpvDetail['kum2_min_80'];
                        $diffTotal = $selectedSpvDetail['total_realisasi_3bln'] - $selectedSpvDetail['target_3bln'];

                        $omsetM1Jt = number_format(($selectedSpvDetail['realisasi_m1'] * $hargaPerKtn) / 1000000, 1, ',', '.');
                        $omsetKum2Jt = number_format(($selectedSpvDetail['realisasi_kum2'] * $hargaPerKtn) / 1000000, 1, ',', '.');
                        $omsetTotalJt = number_format(($selectedSpvDetail['total_realisasi_3bln'] * $hargaPerKtn) / 1000000, 1, ',', '.');
                    @endphp

                    <div class="bg-base-100 rounded-xl border border-base-300 shadow-sm p-4 md:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3.5">
                            <div>
                                <h4 class="font-bold text-sm text-base-content">Rapor Evaluasi 3 Tahap (Okt • Okt-Nov • Okt-Des)</h4>
                                <p class="text-xs text-base-content/60 mt-0.5">Perbandingan target penuh 100%, batas minimal wajib lolos, realisasi, dan selisih karton</p>
                            </div>
                            <span class="badge badge-ghost border border-base-300 text-xs font-semibold">
                                Sisa {{ $daysInfo['remaining_days'] }} Hari Kerja (Nov)
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                            {{-- Tahap 1: Okt (Wajib >= 70%) --}}
                            <div class="rounded-xl border p-3.5 flex flex-col justify-between gap-2.5 {{ $selectedSpvDetail['is_m1_pass'] ? 'bg-success/5 border-success/30' : 'bg-error/5 border-error/30' }}">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">Tahap 1 • Okt</span>
                                        <span class="badge badge-sm font-bold text-xs {{ $selectedSpvDetail['is_m1_pass'] ? 'badge-success text-white' : 'badge-error text-white' }}">
                                            {{ $selectedSpvDetail['is_m1_pass'] ? 'Lolos ≥70%' : 'Gugur <70%' }}
                                        </span>
                                    </div>
                                    <div class="flex items-baseline justify-between mt-2">
                                        <div class="text-lg font-bold font-mono text-base-content">
                                            {{ number_format($selectedSpvDetail['realisasi_m1'], 1, ',', '.') }} <span class="text-xs font-normal text-base-content/60">Ktn</span>
                                        </div>
                                        <span class="text-xs font-bold font-mono {{ $selectedSpvDetail['is_m1_pass'] ? 'text-success' : 'text-error' }}">
                                            {{ $selectedSpvDetail['pct_m1'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-base-200 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $selectedSpvDetail['is_m1_pass'] ? 'bg-success' : 'bg-error' }}" style="width: {{ min(100, $selectedSpvDetail['pct_m1']) }}%"></div>
                                    </div>
                                </div>

                                <div class="space-y-1 pt-2 border-t border-base-300/60 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target 100% (Okt):</span>
                                        <span class="font-mono font-semibold">{{ number_format($selectedSpvDetail['target_m1'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Minimal Wajib (70%):</span>
                                        <span class="font-mono font-bold">{{ number_format($selectedSpvDetail['m1_min_70'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Nilai Omset:</span>
                                        <span class="font-mono font-semibold">Rp {{ $omsetM1Jt }} Jt</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300/40 font-bold {{ $diffM1 >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        <span>{{ $diffM1 >= 0 ? 'Surplus (vs 70%):' : 'Kurang (ke 70%):' }}</span>
                                        <span class="font-mono">{{ $diffM1 >= 0 ? '+' : '' }}{{ number_format(abs($diffM1), 1, ',', '.') }} Ktn</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tahap 2: Okt-Nov (Wajib Kumulatif >= 80%) --}}
                            <div class="rounded-xl border p-3.5 flex flex-col justify-between gap-2.5 {{ $selectedSpvDetail['is_kum2_pass'] ? 'bg-success/5 border-success/30' : 'bg-warning/5 border-warning/30' }}">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">Tahap 2 • Okt-Nov</span>
                                        <span class="badge badge-sm font-bold text-xs {{ $selectedSpvDetail['is_kum2_pass'] ? 'badge-success text-white' : 'badge-warning text-amber-950' }}">
                                            {{ $selectedSpvDetail['is_kum2_pass'] ? 'Lolos ≥80%' : 'Kejar 80%' }}
                                        </span>
                                    </div>
                                    <div class="flex items-baseline justify-between mt-2">
                                        <div class="text-lg font-bold font-mono text-base-content">
                                            {{ number_format($selectedSpvDetail['realisasi_kum2'], 1, ',', '.') }} <span class="text-xs font-normal text-base-content/60">Ktn</span>
                                        </div>
                                        <span class="text-xs font-bold font-mono {{ $selectedSpvDetail['is_kum2_pass'] ? 'text-success' : 'text-amber-600' }}">
                                            {{ $selectedSpvDetail['pct_kum2'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-base-200 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $selectedSpvDetail['is_kum2_pass'] ? 'bg-success' : 'bg-warning' }}" style="width: {{ min(100, $selectedSpvDetail['pct_kum2']) }}%"></div>
                                    </div>
                                </div>

                                <div class="space-y-1 pt-2 border-t border-base-300/60 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target 100% (2 Bln):</span>
                                        <span class="font-mono font-semibold">{{ number_format($selectedSpvDetail['target_kum2'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Minimal Wajib (80%):</span>
                                        <span class="font-mono font-bold">{{ number_format($selectedSpvDetail['kum2_min_80'], 1, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Nilai Omset (2 Bln):</span>
                                        <span class="font-mono font-semibold">Rp {{ $omsetKum2Jt }} Jt</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300/40 font-bold {{ $diffKum2 >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        <span>{{ $diffKum2 >= 0 ? 'Surplus (vs 80%):' : 'Kurang (ke 80%):' }}</span>
                                        <span class="font-mono">
                                            {{ $diffKum2 >= 0 ? '+' : '' }}{{ number_format(abs($diffKum2), 1, ',', '.') }} Ktn
                                            @if($diffKum2 < 0 && $selectedSpvDetail['daily_rate_needed'] > 0)
                                                (~{{ number_format($selectedSpvDetail['daily_rate_needed'], 1, ',', '.') }}/hr)
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tahap 3: Okt-Des (Wajib Total >= 100%) --}}
                            <div class="rounded-xl border p-3.5 flex flex-col justify-between gap-2.5 {{ $selectedSpvDetail['is_total_pass'] ? 'bg-success/5 border-success/30' : 'bg-base-200/50 border-base-300' }}">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">Tahap 3 • Okt-Des</span>
                                        <span class="badge badge-sm font-bold text-xs {{ $selectedSpvDetail['is_total_pass'] ? 'badge-success text-white' : 'badge-primary text-white' }}">
                                            {{ $selectedSpvDetail['is_total_pass'] ? 'Tercapai ≥100%' : 'Target 100%' }}
                                        </span>
                                    </div>
                                    <div class="flex items-baseline justify-between mt-2">
                                        <div class="text-lg font-bold font-mono text-base-content">
                                            {{ number_format($selectedSpvDetail['total_realisasi_3bln'], 1, ',', '.') }} <span class="text-xs font-normal text-base-content/60">Ktn</span>
                                        </div>
                                        <span class="text-xs font-bold font-mono text-primary">
                                            {{ $selectedSpvDetail['pct_total'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-base-200 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ min(100, $selectedSpvDetail['pct_total']) }}%"></div>
                                    </div>
                                </div>

                                <div class="space-y-1 pt-2 border-t border-base-300/60 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Target Paket {{ $selectedSpvDetail['kode_paket_awal'] }} (100%):</span>
                                        <span class="font-mono font-bold">{{ number_format($selectedSpvDetail['target_3bln'], 0, ',', '.') }} Ktn</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Unit Hadiah:</span>
                                        <span class="font-semibold truncate max-w-36" title="{{ $selectedSpvDetail['nama_hadiah_awal'] }}">{{ $selectedSpvDetail['nama_hadiah_awal'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-base-content/60">Nilai Omset Total:</span>
                                        <span class="font-mono font-semibold">Rp {{ $omsetTotalJt }} Jt</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-base-300/40 font-bold {{ $diffTotal >= 0 ? 'text-emerald-600' : 'text-primary' }}">
                                        <span>{{ $diffTotal >= 0 ? 'Surplus (vs 100%):' : 'Kurang (ke 100%):' }}</span>
                                        <span class="font-mono">{{ $diffTotal >= 0 ? '+' : '' }}{{ number_format(abs($diffTotal), 1, ',', '.') }} Ktn</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. SIMULASI TANGGA PAKET HADIAH (TIER PROGRESSION LADDER) --}}
                    @if(!empty($selectedTierLadder))
                        <div class="bg-base-100 rounded-xl border border-base-300 shadow-sm p-4 md:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3.5">
                                <div>
                                    <h4 class="font-bold text-sm text-base-content">Simulasi Tangga Paket Hadiah (Wilayah {{ ucfirst($selectedSpvDetail['kategori_wilayah']) }})</h4>
                                    <p class="text-xs text-base-content/60 mt-0.5">Posisi realisasi SPV saat ini terhadap Paket Awal dan simulasi kekurangan karton menuju paket di atasnya</p>
                                </div>
                                <span class="badge badge-primary badge-outline badge-sm font-bold text-xs">
                                    Paket Awal: Paket {{ $selectedSpvDetail['kode_paket_awal'] }} ({{ number_format($selectedSpvDetail['target_3bln'], 0, ',', '.') }} Ktn)
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                @foreach($selectedTierLadder as $step)
                                    @php
                                        $sCat = $step['reward_category'] ?? 'gift';
                                        $sCatStyle = match($sCat) {
                                            'motor'  => 'bg-rose-500/15 text-rose-600',
                                            'laptop' => 'bg-indigo-500/15 text-indigo-600',
                                            'tv'     => 'bg-purple-500/15 text-purple-600',
                                            'tablet' => 'bg-cyan-500/15 text-cyan-600',
                                            'phone'  => 'bg-blue-500/15 text-blue-600',
                                            'watch'  => 'bg-emerald-500/15 text-emerald-600',
                                            'audio'  => 'bg-amber-500/15 text-amber-600',
                                            default  => 'bg-primary/10 text-primary',
                                        };

                                        if ($step['is_initial']) {
                                            $cardBorder = 'border-2 border-primary bg-primary/5 shadow-sm';
                                        } elseif ($step['is_achieved']) {
                                            $cardBorder = 'border border-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/10';
                                        } else {
                                            $cardBorder = 'border border-base-300 bg-base-100';
                                        }
                                    @endphp

                                    <div class="rounded-xl p-3 flex flex-col justify-between gap-2.5 {{ $cardBorder }}">
                                        <div>
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="badge badge-sm font-bold font-mono text-xs {{ $step['is_initial'] ? 'badge-primary text-white' : ($step['is_achieved'] ? 'badge-success text-white' : 'badge-ghost border-base-300') }}">
                                                    Paket {{ $step['kode_paket'] }}
                                                </span>
                                                <span class="text-xs font-bold {{ $step['is_initial'] ? 'text-primary' : ($step['diff_from_initial'] > 0 ? 'text-indigo-600' : 'text-base-content/50') }}">
                                                    {{ $step['step_label'] }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2 mt-2.5">
                                                <div class="w-8 h-8 rounded-lg {{ $sCatStyle }} flex items-center justify-center shrink-0">
                                                    @if($sCat === 'motor')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><circle cx="5.5" cy="17.5" r="2.5"></circle><circle cx="18.5" cy="17.5" r="2.5"></circle><path stroke-linecap="round" stroke-linejoin="round" d="M15 6h2l3 7.5M5.5 17.5L8 11h5l2 4.5h3.5M8 11l-1.5-3H4"></path></svg>
                                                    @elseif($sCat === 'laptop')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="4" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M2 20h20"></path></svg>
                                                    @elseif($sCat === 'tv')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="2" y="4" width="20" height="13" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4"></path></svg>
                                                    @elseif($sCat === 'tablet')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="4" y="2" width="16" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01"></path></svg>
                                                    @elseif($sCat === 'phone')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="6" y="2" width="12" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M11 18h2"></path></svg>
                                                    @elseif($sCat === 'watch')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="7" y="6" width="10" height="12" rx="2.5"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M9 6V3h6v3M9 18v3h6v-3M12 10v2l1.5 1.5"></path></svg>
                                                    @elseif($sCat === 'audio')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                                    @endif
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-bold text-base-content truncate" title="{{ $step['nama_hadiah'] }}">
                                                        {{ $step['nama_hadiah'] }}
                                                    </div>
                                                    <div class="text-xs font-mono font-semibold text-base-content/60">
                                                        Target: {{ number_format($step['target_karton'], 0, ',', '.') }} Ktn
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-base-300/60 text-xs">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-base-content/60">Progres Saat Ini:</span>
                                                <span class="font-mono font-bold {{ $step['is_achieved'] ? 'text-emerald-600' : 'text-base-content' }}">{{ $step['pct_progress'] }}%</span>
                                            </div>
                                            <div class="w-full bg-base-200 rounded-full h-1.5 overflow-hidden mb-1.5">
                                                <div class="h-1.5 rounded-full {{ $step['is_achieved'] ? 'bg-success' : ($step['is_initial'] ? 'bg-primary' : 'bg-indigo-500') }}" style="width: {{ $step['pct_progress'] }}%"></div>
                                            </div>
                                            <div class="flex justify-between items-center font-bold">
                                                @if($step['is_achieved'])
                                                    <span class="text-emerald-600">✓ Target Tercapai</span>
                                                @else
                                                    <span class="text-base-content/60 font-normal">Kurang:</span>
                                                    <span class="font-mono {{ $step['is_initial'] ? 'text-rose-600' : 'text-indigo-600' }}">
                                                        {{ number_format($step['gap_ktn'], 1, ',', '.') }} Ktn
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 3. Distributor Opportunity Breakdown --}}
                    <div class="bg-base-100 rounded-xl border border-base-300 shadow-sm p-4 md:p-5">
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <h4 class="font-bold text-sm text-base-content">Kontribusi Distributor Aktif</h4>
                                <p class="text-xs text-base-content/60 mt-0.5">Pantau realisasi per depo dan identifikasi depo yang ordernya masih tertinggal</p>
                            </div>
                            <span class="badge badge-success text-white badge-sm font-bold text-xs">
                                {{ count($selectedDistributors) }} Distributor Aktif
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table table-xs w-full">
                                <thead>
                                    <tr class="bg-base-200/70 text-xs uppercase font-bold text-base-content/70 border-b border-base-300">
                                        <th>Distributor & Cabang</th>
                                        <th class="text-right">Okt (Ktn)</th>
                                        <th class="text-right">Nov (Ktn)</th>
                                        <th class="text-right">Total (Ktn)</th>
                                        <th class="w-36 text-center">Kontribusi (%)</th>
                                        <th class="text-center">Status Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedDistributors as $dst)
                                        <tr class="hover:bg-base-200/40">
                                            <td>
                                                <div class="font-bold text-xs text-base-content">{{ $dst['distributor_name'] }}</div>
                                                <div class="text-xs text-base-content/50 font-mono">{{ $dst['distributor_code'] }} • Cabang: {{ $dst['branch_name'] }}</div>
                                            </td>
                                            <td class="text-right font-mono text-xs">{{ number_format($dst['okt_ktn'], 1, ',', '.') }}</td>
                                            <td class="text-right font-mono text-xs font-bold text-primary">{{ number_format($dst['nov_ktn'], 1, ',', '.') }}</td>
                                            <td class="text-right font-mono text-xs font-bold">{{ number_format($dst['total_ktn'], 1, ',', '.') }}</td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-full bg-base-200 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ min(100, $dst['contribution_pct']) }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-mono font-bold text-base-content/70 w-10 text-right">{{ $dst['contribution_pct'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-center text-xs font-semibold">
                                                {{ $dst['status_order'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-6 text-xs text-base-content/40">Tidak ada distributor aktif di bawah supervisi ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 4. RENCANA TINDAK LANJUT & ACTION PLAN SPV --}}
                    @if(!empty($selectedActionPlan))
                        @php
                            $apTheme = $selectedActionPlan['theme'] ?? 'primary';
                            $apContainer = match($apTheme) {
                                'rose'    => 'border-rose-300 dark:border-rose-900 bg-rose-50/40 dark:bg-rose-950/15',
                                'amber'   => 'border-amber-300 dark:border-amber-900 bg-amber-50/40 dark:bg-amber-950/15',
                                'emerald' => 'border-emerald-300 dark:border-emerald-900 bg-emerald-50/40 dark:bg-emerald-950/15',
                                default   => 'border-base-300 bg-base-100',
                            };
                            $apBadge = match($apTheme) {
                                'rose'    => 'badge-error text-white',
                                'amber'   => 'badge-warning text-amber-950',
                                'emerald' => 'badge-success text-white',
                                default   => 'badge-primary text-white',
                            };
                            $apNumBg = match($apTheme) {
                                'rose'    => 'bg-rose-500 text-white',
                                'amber'   => 'bg-amber-500 text-amber-950',
                                'emerald' => 'bg-emerald-600 text-white',
                                default   => 'bg-primary text-white',
                            };
                        @endphp

                        <div class="rounded-xl border {{ $apContainer }} shadow-sm p-4 md:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3.5">
                                <div>
                                    <h4 class="font-bold text-sm text-base-content">Rencana Tindak Lanjut & Action Plan SPV</h4>
                                    <p class="text-xs text-base-content/70 mt-0.5 font-medium">{{ $selectedActionPlan['headline'] }}</p>
                                </div>
                                <span class="badge {{ $apBadge }} badge-sm font-bold text-xs shrink-0">
                                    {{ $selectedActionPlan['badge'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                @foreach($selectedActionPlan['steps'] as $i => $step)
                                    <div class="bg-base-100 rounded-xl border border-base-300/80 p-3.5 flex flex-col gap-1.5 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full {{ $apNumBg }} text-xs font-bold flex items-center justify-center shrink-0">
                                                {{ $i + 1 }}
                                            </span>
                                            <h5 class="font-bold text-xs text-base-content leading-snug">
                                                {{ $step['title'] }}
                                            </h5>
                                        </div>
                                        <p class="text-xs text-base-content/70 leading-relaxed pl-7">
                                            {{ $step['desc'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Disclaimer Banner Acuan Monitoring vs Closing Final Eskalink --}}
                    <div class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-xl px-4 py-3 flex items-start gap-2.5 text-xs text-amber-900 dark:text-amber-200">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="leading-relaxed">
                            <span class="font-bold">Catatan Penting (Disclaimer):</span> Seluruh angka pada dashboard ini disajikan sebagai <strong>acuan monitoring berjalan (estimasi real-time)</strong> untuk membantu pemantauan harian tim di lapangan. Apabila terdapat selisih akibat proses potong retur, koreksi faktur, atau waktu <em>cut-off</em> sinkronisasi, maka penentuan kelulusan <em>milestone</em> dan hak klaim hadiah akhir sepenuhnya mengacu pada <strong>angka <em>closing</em> final resmi dari sistem Eskalink</strong>.
                        </p>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-base-100 border-t border-base-300 flex justify-end">
                    <button wire:click="closeActionModal" class="btn btn-sm btn-ghost rounded-xl font-bold text-xs">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL 2: EDIT TARGET SPV (ADMIN & SPM ONLY)              --}}
    {{-- ======================================================== --}}
    @if($isTargetModalOpen)
        <div class="modal modal-open items-center justify-center p-2 sm:p-4" style="z-index: 99999 !important;">
            <div class="modal-box w-full max-w-lg bg-base-100 border border-base-300 shadow-2xl rounded-2xl p-6" style="max-width: 32rem !important; width: 95% !important;">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="font-bold text-base md:text-lg text-base-content">Edit Target SPV</h3>
                        <p class="text-xs text-base-content/60">Sesuaikan target jika ada revisi kesepakatan</p>
                    </div>
                    <button wire:click="closeTargetModal" class="btn btn-xs btn-circle btn-ghost" aria-label="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-col gap-3.5 text-xs">
                    <div class="p-3 rounded-xl bg-base-200/70 border border-base-300">
                        <span class="font-bold text-sm text-base-content block">{{ $formSpvName }}</span>
                        <span class="font-mono text-xs text-base-content/60">{{ $formSpvCode }} • Wilayah: {{ ucfirst($formWilayah) }}</span>
                    </div>

                    <div>
                        <label class="label py-1"><span class="label-text font-bold text-xs">Target Total 3 Bulan (Karton)</span></label>
                        <input type="number" step="1" wire:model="formTarget3Bln" wire:change="updateTarget3BlnCalculations" class="input input-sm input-bordered rounded-xl w-full font-mono font-bold text-sm" />
                        <span class="text-xs text-base-content/50">Otomatis membagi rata ke Okt, Nov, Des dan mencocokkan kode paket.</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="label py-0.5"><span class="label-text text-xs font-semibold">Okt</span></label>
                            <input type="number" step="0.01" wire:model="formTargetM1" class="input input-sm input-bordered rounded-xl w-full font-mono text-xs" />
                        </div>
                        <div>
                            <label class="label py-0.5"><span class="label-text text-xs font-semibold">Nov</span></label>
                            <input type="number" step="0.01" wire:model="formTargetM2" class="input input-sm input-bordered rounded-xl w-full font-mono text-xs" />
                        </div>
                        <div>
                            <label class="label py-0.5"><span class="label-text text-xs font-semibold">Des</span></label>
                            <input type="number" step="0.01" wire:model="formTargetM3" class="input input-sm input-bordered rounded-xl w-full font-mono text-xs" />
                        </div>
                    </div>

                    <div>
                        <label class="label py-1"><span class="label-text font-semibold text-xs">Kode Paket Awal</span></label>
                        <input type="text" wire:model="formKodePaket" class="input input-sm input-bordered rounded-xl w-full font-bold uppercase text-xs" />
                    </div>

                    <div>
                        <label class="label py-1"><span class="label-text font-semibold text-xs">Catatan / Alasan Revisi</span></label>
                        <textarea wire:model="formCatatan" rows="2" class="textarea textarea-bordered rounded-xl w-full text-xs" placeholder="Contoh: Revisi target disetujui RSM"></textarea>
                    </div>
                </div>

                <div class="modal-action mt-5">
                    <button wire:click="closeTargetModal" class="btn btn-sm btn-ghost rounded-xl text-xs">Batal</button>
                    <button wire:click="saveTarget" class="btn btn-sm btn-primary rounded-xl font-bold text-xs">Simpan Target</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL 3: KELOLA MASTER PAKET HADIAH (ADMIN & SPM ONLY)   --}}
    {{-- ======================================================== --}}
    @if($isTierModalOpen)
        <div class="modal modal-open items-center justify-center p-2 sm:p-4" style="z-index: 99999 !important;">
            <div class="modal-box w-11/12 max-w-5xl bg-base-100 border border-base-300 shadow-2xl p-0 rounded-2xl overflow-hidden flex flex-col max-h-[88vh]" style="max-width: 60rem !important; width: 95% !important;">
                <div class="p-4 md:p-5 bg-base-200 border-b border-base-300 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-base md:text-lg text-base-content">Master Paket & Hadiah Semarak Rejeki</h3>
                        @hasanyrole('admin|admspm|spvspm')
                            <p class="text-xs text-base-content/60">Kelola spesifikasi unit hadiah jika ada penyesuaian barang setara</p>
                        @else
                            <p class="text-xs text-base-content/60">Katalog daftar paket tier hadiah dan target penjualan Semarak Rejeki Q4</p>
                        @endhasanyrole
                    </div>
                    <button wire:click="closeTierModal" class="btn btn-xs btn-circle btn-ghost" aria-label="Tutup">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 md:p-5 flex-1 overflow-y-auto">
                    {{-- Tabs Wilayah (Dibatasi sesuai hak akses wilayah user & view aktif) --}}
                    <div class="tabs tabs-boxed mb-4 bg-base-200/80 p-1 rounded-xl">
                        @if(in_array('jawa', $visibleTierWilayahs))
                            <a wire:click="$set('activeTierWilayah', 'jawa')" class="tab tab-sm font-bold text-xs rounded-lg {{ $activeTierWilayah === 'jawa' ? 'tab-active bg-primary text-white shadow-sm' : '' }}">
                                Regional Jawa (22 Paket)
                            </a>
                        @endif
                        @if(in_array('pulau', $visibleTierWilayahs))
                            <a wire:click="$set('activeTierWilayah', 'pulau')" class="tab tab-sm font-bold text-xs rounded-lg {{ $activeTierWilayah === 'pulau' ? 'tab-active bg-primary text-white shadow-sm' : '' }}">
                                Regional Pulau (17 Paket)
                            </a>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table table-xs md:table-sm w-full">
                            <thead>
                                <tr class="bg-base-200/70 text-xs uppercase font-bold text-base-content/70 border-b border-base-300">
                                    <th class="w-20 text-center py-2.5">Paket</th>
                                    <th class="w-40 py-2.5">Target (Ktn)</th>
                                    <th class="py-2.5">Nama Unit Hadiah</th>
                                    @hasanyrole('admin|admspm|spvspm')
                                        <th class="text-center w-28 py-2.5">Aksi</th>
                                    @endhasanyrole
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tiersData as $tier)
                                    @php
                                        $tCat = $tier->reward_category;
                                        $tCatColor = match($tCat) {
                                            'motor'  => 'bg-rose-500/15 text-rose-600',
                                            'laptop' => 'bg-indigo-500/15 text-indigo-600',
                                            'tv'     => 'bg-purple-500/15 text-purple-600',
                                            'tablet' => 'bg-cyan-500/15 text-cyan-600',
                                            'phone'  => 'bg-blue-500/15 text-blue-600',
                                            'watch'  => 'bg-emerald-500/15 text-emerald-600',
                                            'audio'  => 'bg-amber-500/15 text-amber-600',
                                            default  => 'bg-primary/10 text-primary',
                                        };
                                    @endphp
                                    <tr class="hover:bg-base-200/40">
                                        <td class="text-center font-bold">
                                            <span class="badge badge-primary badge-sm font-mono text-xs">{{ $tier->kode_paket }}</span>
                                        </td>
                                        <td class="font-mono font-bold text-xs">{{ number_format($tier->target_karton, 0, ',', '.') }} Ktn</td>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-lg {{ $tCatColor }} flex items-center justify-center shrink-0">
                                                    @if($tCat === 'motor')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><circle cx="5.5" cy="17.5" r="2.5"></circle><circle cx="18.5" cy="17.5" r="2.5"></circle><path stroke-linecap="round" stroke-linejoin="round" d="M15 6h2l3 7.5M5.5 17.5L8 11h5l2 4.5h3.5M8 11l-1.5-3H4"></path></svg>
                                                    @elseif($tCat === 'laptop')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="4" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M2 20h20"></path></svg>
                                                    @elseif($tCat === 'tv')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="2" y="4" width="20" height="13" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4"></path></svg>
                                                    @elseif($tCat === 'tablet')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="4" y="2" width="16" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01"></path></svg>
                                                    @elseif($tCat === 'phone')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="6" y="2" width="12" height="20" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M11 18h2"></path></svg>
                                                    @elseif($tCat === 'watch')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><rect x="7" y="6" width="10" height="12" rx="2.5"></rect><path stroke-linecap="round" stroke-linejoin="round" d="M9 6V3h6v3M9 18v3h6v-3M12 10v2l1.5 1.5"></path></svg>
                                                    @elseif($tCat === 'audio')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                                    @endif
                                                </div>
                                                @hasanyrole('admin|admspm|spvspm')
                                                    @if($editingTierId === $tier->id)
                                                        <input type="text" wire:model="formTierNamaHadiah" class="input input-xs input-bordered w-full font-semibold rounded-lg text-xs" />
                                                    @else
                                                        <span class="font-medium text-xs">{{ $tier->nama_hadiah }}</span>
                                                    @endif
                                                @else
                                                    <span class="font-medium text-xs">{{ $tier->nama_hadiah }}</span>
                                                @endhasanyrole
                                            </div>
                                        </td>
                                        @hasanyrole('admin|admspm|spvspm')
                                            <td class="text-center">
                                                @if($editingTierId === $tier->id)
                                                    <button wire:click="saveTier" class="btn btn-xs btn-success text-white rounded-lg text-xs font-bold">Simpan</button>
                                                @else
                                                    <button wire:click="editTier({{ $tier->id }})" class="btn btn-xs btn-ghost border border-base-300 rounded-lg text-xs">Edit</button>
                                                @endif
                                            </td>
                                        @endhasanyrole
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-4 bg-base-200 border-t border-base-300 flex justify-end">
                    <button wire:click="closeTierModal" class="btn btn-sm btn-ghost rounded-xl font-bold text-xs">Tutup</button>
                </div>
            </div>
        </div>
    @endif

</div>
