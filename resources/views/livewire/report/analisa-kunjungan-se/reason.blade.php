<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Summary Reason Kunjungan SE</x-slot>

    {{-- Standar Tab Menu --}}
    @php
        $tabParams = array_filter([
            'region' => $appliedRegion ?: $selectedRegion,
            'area' => $appliedArea ?: $selectedArea,
            'distributor' => $appliedDistributor ?: $selectedDistributor,
            'startDate' => $appliedStartDate ?: $startDate,
            'endDate' => $appliedEndDate ?: $endDate,
        ], fn($v) => !empty($v));
    @endphp
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.index', $tabParams)" :navigate="true">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.summary', $tabParams)" :navigate="true">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.reason', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-list class="w-4 h-4 inline-block mr-1" /> Summary Reason
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.trend', $tabParams)" :navigate="true">
            <x-heroicon-o-arrow-trending-up class="w-4 h-4 inline-block mr-1" /> Trend Bulanan
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.findings', $tabParams)" :navigate="true">
            <x-heroicon-o-exclamation-triangle class="w-4 h-4 inline-block mr-1 text-warning" /> Temuan
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.tracking', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-check class="w-4 h-4 inline-block mr-1 text-primary" /> Tracking Toko
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.raport', $tabParams)" :navigate="true">
            <x-heroicon-o-academic-cap class="w-4 h-4 inline-block mr-1 text-emerald-500" /> Raport SE
        </x-ui.tab-item>
    </x-ui.tab-menu>

    {{-- KPI Cards Section (4 Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 md:gap-3.5 shrink-0 mb-3 md:mb-4">
        {{-- Card 1: Total Kasus Reason --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-primary/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Total Kasus Alasan</span>
                <div class="w-6 h-6 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-clipboard-document-list class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-base-content font-mono">
                    {{ number_format($kpis->total_cases ?? 0) }}
                </span>
                <span class="text-[0.6875rem] font-semibold text-base-content/50 uppercase">Kasus</span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50 truncate">
                Tipe: <span class="font-bold text-primary">{{ $appliedReasonType === 'BUY' ? 'Tidak Beli' : ($appliedReasonType === 'VISIT' ? 'Tidak Dikunjungi' : 'Semua Tipe') }}</span>
            </div>
        </div>

        {{-- Card 2: Total SE Terdampak --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-secondary/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Total SE Terdampak</span>
                <div class="w-6 h-6 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                    <x-heroicon-s-user-group class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-base-content font-mono">
                    {{ number_format($kpis->total_se ?? 0) }}
                </span>
                <span class="text-[0.6875rem] font-semibold text-base-content/50 uppercase">Salesman</span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50 truncate">
                Memiliki catatan alasan di periode ini
            </div>
        </div>

        {{-- Card 3: Alasan Terbanyak (#1) --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-warning/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Alasan Terbanyak (#1)</span>
                <div class="w-6 h-6 rounded-lg bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-fire class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-lg sm:text-xl font-black tracking-tight text-base-content truncate pr-1" title="{{ $kpis->top_reason_name ?? '-' }}">
                    {{ $kpis->top_reason_name ?? '-' }}
                </span>
                <span class="text-xs font-mono font-bold text-warning shrink-0">
                    {{ number_format($kpis->top_reason_count ?? 0) }}x
                </span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50 truncate">
                Frekuensi kemunculan tertinggi
            </div>
        </div>

        {{-- Card 4: Rata-rata per SE --}}
        <div class="bg-base-100 p-3 sm:p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-info/40 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span class="truncate">Rata-rata Kasus per SE</span>
                <div class="w-6 h-6 rounded-lg bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-calculator class="w-3.5 h-3.5" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-xl sm:text-2xl font-black tracking-tight text-base-content font-mono">
                    {{ $kpis->avg_per_se ?? 0 }}
                </span>
                <span class="text-[0.6875rem] font-semibold text-base-content/50 uppercase">Kasus / SE</span>
            </div>
            <div class="mt-1 text-[0.6875rem] text-base-content/50 truncate">
                Rata-rata kejadian per salesman
            </div>
        </div>
    </div>

    {{-- Main Card (Tabel Summary Reason SE) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base md:text-lg font-bold">Summary Reason Kunjungan SE</h2>
                    
                    {{-- Territory pills for locked/single access --}}
                    @if($accessLevel !== 'nasional' && count($regions) <= 1 && !empty($selectedRegion))
                        @php
                            $currReg = collect($regions)->firstWhere('region_code', $selectedRegion);
                        @endphp
                        @if($currReg)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold bg-primary/10 text-primary border border-primary/20" title="Wilayah Region Anda">
                                <x-heroicon-m-map-pin class="w-3 h-3" />
                                {{ $currReg->region_name }}
                            </span>
                        @endif
                    @endif

                    @if(in_array($accessLevel, ['area', 'supervisor']) && count($areas) <= 1 && !empty($selectedArea))
                        @php
                            $currArea = collect($areas)->firstWhere('area_code', $selectedArea);
                        @endphp
                        @if($currArea)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold bg-secondary/10 text-secondary border border-secondary/20" title="Wilayah Area Anda">
                                <x-heroicon-m-building-office class="w-3 h-3" />
                                {{ $currArea->area_name }}
                            </span>
                        @endif
                    @endif

                    @if($appliedStartDate && $appliedEndDate)
                        <span class="badge badge-neutral text-neutral-content font-mono text-xs font-semibold">
                            {{ \Carbon\Carbon::parse($appliedStartDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($appliedEndDate)->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Analisis Frekuensi Alasan Visit & Not-Buy per Sales Exclusive (SE)</p>
            </div>
            
            {{-- Filter Bar & Action Buttons --}}
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 w-full lg:w-auto">
                {{-- Search Box SE / Distributor --}}
                <div class="relative w-full sm:w-auto">
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari SE / Distributor..."
                           class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 text-xs pl-8 pr-2 w-full sm:w-44 focus:outline-none focus:border-primary" />
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40" />
                </div>

                {{-- Filter Region --}}
                @if(count($regions) > 1 || $accessLevel === 'nasional')
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs">
                        <option value="">Pilih Region</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Area --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs" @disabled(empty($selectedRegion))>
                        <option value="">Semua Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Distributor --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="selectedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[150px] text-xs" @disabled(empty($selectedRegion)) wire:target="selectedArea, selectedRegion" wire:loading.class="opacity-60">
                        <option value="">Semua Distributor</option>
                        @foreach($distributors as $d)
                            <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Smart Date Range Filter --}}
                <div class="flex items-center gap-1 bg-base-100 px-1 py-0.5 rounded-xl border border-base-300 shadow-2xs w-full sm:w-auto">
                    <button type="button" wire:click="previousMonth" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-base-content" title="Bulan Sebelumnya">
                        <x-heroicon-m-chevron-left class="w-3.5 h-3.5" />
                    </button>

                    <input type="date" 
                        wire:model.live="startDate" 
                        max="{{ $endDate ?: date('Y-m-d') }}"
                        min="{{ $endDate ? \Carbon\Carbon::parse($endDate)->subDays(30)->format('Y-m-d') : '' }}"
                        class="input input-xs bg-transparent border-none text-xs w-[105px] focus:outline-none p-0 text-center font-medium" 
                        title="Tanggal Mulai" />

                    <span class="text-xs font-semibold text-base-content/40">s/d</span>

                    <input type="date" 
                        wire:model.live="endDate" 
                        min="{{ $startDate ?: '' }}"
                        max="{{ $startDate ? \Carbon\Carbon::parse($startDate)->addDays(30)->format('Y-m-d') : date('Y-m-d') }}"
                        class="input input-xs bg-transparent border-none text-xs w-[105px] focus:outline-none p-0 text-center font-medium" 
                        title="Tanggal Akhir" />

                    @php
                        $diffDays = ($startDate && $endDate) ? (\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1) : 0;
                    @endphp
                    <div class="tooltip tooltip-bottom" data-tip="{{ $diffDays }} hari dipilih (Maks. 31 hari)">
                        <span class="badge badge-xs {{ $diffDays >= 31 ? 'badge-warning text-slate-950' : 'badge-neutral text-neutral-content' }} font-mono font-bold px-1.5 cursor-default">
                            {{ $diffDays }}h
                        </span>
                    </div>

                    <button type="button" wire:click="nextMonth" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-base-content" title="Bulan Berikutnya">
                        <x-heroicon-m-chevron-right class="w-3.5 h-3.5" />
                    </button>

                    {{-- Dropdown Pintasan Periode Cepat --}}
                    <div class="dropdown dropdown-end">
                        <button type="button" tabindex="0" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-primary" title="Pintasan Periode Cepat">
                            <x-heroicon-m-calendar-days class="w-3.5 h-3.5" />
                        </button>
                        <ul tabindex="0" class="dropdown-content z-30 menu menu-xs p-1.5 shadow-xl bg-base-100 rounded-xl border border-base-300 w-36 text-xs">
                            <li class="menu-title text-xs uppercase tracking-wider text-base-content/50 px-2 py-0.5">Pintasan Periode</li>
                            <li><a wire:click="setDatePreset('today')" class="py-1">Hari Ini</a></li>
                            <li><a wire:click="setDatePreset('yesterday')" class="py-1">Kemarin</a></li>
                            <li><a wire:click="setDatePreset('last_7_days')" class="py-1">7 Hari Terakhir</a></li>
                            <li><a wire:click="setDatePreset('this_month')" class="py-1 font-semibold text-primary">Bulan Ini (MTD)</a></li>
                            <li><a wire:click="setDatePreset('last_30_days')" class="py-1">30 Hari Terakhir</a></li>
                            <li><a wire:click="setDatePreset('last_month')" class="py-1">Bulan Lalu</a></li>
                        </ul>
                    </div>
                </div>

                <div class="hidden xl:block w-px h-6 bg-base-300 mx-1"></div>

                {{-- Action Buttons: Terapkan, Reset, Excel --}}
                <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto justify-end">
                    @php
                        $canApply = !empty($selectedRegion);
                    @endphp
                    @if(!$canApply)
                        <div class="tooltip tooltip-bottom" data-tip="Pilih minimal Region terlebih dahulu">
                            <x-ui.button variant="primary" size="sm" class="rounded-xl px-3 font-semibold shadow-sm" disabled icon="funnel">
                                Terapkan
                            </x-ui.button>
                        </div>
                    @else
                        <x-ui.button variant="primary" size="sm" class="rounded-xl px-3 font-semibold shadow-sm" wire:click="applyFilter" spinner="applyFilter" icon="funnel">
                            Terapkan
                        </x-ui.button>
                    @endif

                    <x-ui.button variant="ghost" size="sm" class="rounded-xl px-2.5 text-base-content/70 hover:text-base-content" wire:click="resetFilters" spinner="resetFilters" icon="arrow-path">
                        Reset
                    </x-ui.button>

                    @if($canExport)
                        <x-ui.button variant="success" size="sm" class="rounded-xl text-white px-3 shadow-sm font-semibold" wire:click="exportExcel" spinner="exportExcel" icon="arrow-down-tray">
                            Excel
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Reason Type Filter Pill Bar --}}
        <div class="px-3 md:px-5 py-2.5 bg-base-200/50 border-b border-base-300 shrink-0 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/70 flex items-center gap-1">
                    <x-heroicon-m-funnel class="w-3.5 h-3.5 text-primary" /> Filter Tipe Alasan:
                </span>
                <div class="inline-flex rounded-xl p-1 bg-base-100 border border-base-300 shadow-2xs gap-1">
                    {{-- Button 1: BUY = Not Buy (Tidak Beli) --}}
                    <button type="button"
                            wire:click="setReasonType('BUY')"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $appliedReasonType === 'BUY' ? 'bg-amber-500 text-white shadow-xs' : 'text-base-content/70 hover:text-base-content hover:bg-base-200/60' }}">
                        <span>🛒</span>
                        <span>Tidak Beli (Not Buy)</span>
                        @if($appliedReasonType === 'BUY')
                            <span class="badge badge-xs bg-white text-amber-700 font-mono font-bold">{{ count($columns) }}</span>
                        @endif
                    </button>

                    {{-- Button 2: VISIT = Not Visit (Tidak Dikunjungi) --}}
                    <button type="button"
                            wire:click="setReasonType('VISIT')"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $appliedReasonType === 'VISIT' ? 'bg-rose-500 text-white shadow-xs' : 'text-base-content/70 hover:text-base-content hover:bg-base-200/60' }}">
                        <span>🚫</span>
                        <span>Tidak Dikunjungi (Not Visit)</span>
                        @if($appliedReasonType === 'VISIT')
                            <span class="badge badge-xs bg-white text-rose-700 font-mono font-bold">{{ count($columns) }}</span>
                        @endif
                    </button>

                    {{-- Button 3: ALL = Semua Tipe --}}
                    <button type="button"
                            wire:click="setReasonType('ALL')"
                            class="px-3 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $appliedReasonType === 'ALL' ? 'bg-primary text-primary-content shadow-xs' : 'text-base-content/70 hover:text-base-content hover:bg-base-200/60' }}">
                        <span>📋</span>
                        <span>Semua Tipe</span>
                        @if($appliedReasonType === 'ALL')
                            <span class="badge badge-xs bg-white text-primary font-mono font-bold">{{ count($columns) }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="text-[0.6875rem] text-base-content/60 italic hidden sm:block">
                * Kolom alasan tersusun otomatis berdasarkan frekuensi terbanyak di rentang tanggal & wilayah terpilih.
            </div>
        </div>

        {{-- Body Card (Tabel Summary Reason SE) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <div wire:loading.flex wire:target="applyFilter, resetFilters, previousMonth, nextMonth, setReasonType, sortBy, setDatePreset"
                 class="absolute inset-0 bg-base-100/70 backdrop-blur-2xs z-30 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 bg-base-100 px-5 py-4 rounded-xl shadow-lg border border-base-300">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-semibold text-base-content">Memuat Analisa Reason SE...</span>
                </div>
            </div>

            @if(empty($appliedRegion))
                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-base-content/40">
                    <x-heroicon-o-funnel class="w-12 h-12 mb-2 text-base-content/30" />
                    <h3 class="text-sm font-bold text-base-content/80">Silakan Tentukan Filter</h3>
                    <p class="text-xs text-base-content/60 max-w-sm mt-0.5">
                        Pilih minimal Region (bisa pilih Semua Area atau Area tertentu), lalu klik tombol <span class="text-primary font-bold">Terapkan</span> untuk menampilkan summary alasan kunjungan Sales Exclusive.
                    </p>
                </div>
            @elseif($rows->isEmpty())
                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-base-content/40">
                    <x-heroicon-o-inbox class="w-12 h-12 mb-2 text-base-content/30" />
                    <h3 class="text-sm font-bold text-base-content/80">Tidak Ada Data Ditemukan</h3>
                    <p class="text-xs text-base-content/60 max-w-sm mt-0.5">
                        Tidak ada catatan alasan kunjungan SE untuk filter dan rentang tanggal yang dipilih.
                    </p>
                </div>
            @else
                <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs border-collapse [&_tbody_td]:py-1 [&_tbody_td]:px-2 [&_tbody_tr]:h-7">
                    {{-- THEAD Sticky --}}
                    <thead class="sticky top-0 z-20 bg-base-300 text-base-content shadow-sm [&_th]:py-2 [&_th]:px-2.5">
                        <tr class="[&>th]:align-middle text-xs uppercase tracking-wider border-b-2 border-base-content/20">
                            {{-- # --}}
                            <th class="w-10 text-center font-bold">#</th>

                            {{-- Distributor & Area (Default Sort: Area, then Distributor) --}}
                            <th class="min-w-40 text-left font-bold cursor-pointer select-none hover:text-primary transition-colors" wire:click="sortBy('territory')">
                                <div class="flex items-center gap-1">
                                    <span>Distributor / Area</span>
                                    @if($sortField === 'territory' || $sortField === 'distributor_name' || $sortField === 'area_name')
                                        <span class="text-primary font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>

                            {{-- Salesman (SE) --}}
                            <th class="min-w-44 text-left font-bold cursor-pointer select-none hover:text-primary transition-colors" wire:click="sortBy('salesman_name')">
                                <div class="flex items-center gap-1">
                                    <span>Salesman (SE)</span>
                                    @if($sortField === 'salesman_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>

                            {{-- Dynamic Reason Columns (Sorted by overall frequency desc) --}}
                            @foreach($columns as $col)
                                <th class="text-right font-bold cursor-pointer select-none hover:text-primary transition-colors px-2.5 border-l border-base-content/10"
                                    wire:click="sortBy('{{ addslashes($col->raw_desc) }}')"
                                    title="{{ $col->raw_desc }} (Total: {{ number_format($col->total) }})">
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="max-w-[140px] truncate">{{ $col->short_desc }}</span>
                                        <span class="text-[0.625rem] font-mono px-1 rounded bg-base-content/10 text-base-content/70">
                                            {{ number_format($col->total) }}
                                        </span>
                                        @if($sortField === $col->raw_desc)
                                            <span class="text-primary font-bold">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach

                            {{-- Total Kasus --}}
                            <th class="w-24 text-right font-black cursor-pointer select-none hover:text-primary transition-colors px-3 border-l-2 border-base-content/20"
                                wire:click="sortBy('total_reason')"
                                title="Total Kasus Alasan SE">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Total Kasus</span>
                                    @if($sortField === 'total_reason') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>

                            {{-- Aksi --}}
                            <th class="w-14 text-center font-bold border-l border-base-content/10">Aksi</th>
                        </tr>
                    </thead>

                    {{-- TBODY --}}
                    <tbody class="divide-y divide-base-200">
                        @foreach($rows as $index => $row)
                            <tr class="hover:bg-primary/5 transition-colors">
                                {{-- # --}}
                                <td class="text-center font-mono text-xs text-base-content/50">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- Distributor / Area --}}
                                <td class="text-left font-medium text-base-content">
                                    <div class="font-bold text-xs leading-tight text-base-content">{{ $row->distributor_name }}</div>
                                    <div class="text-[0.6875rem] text-base-content/50 uppercase tracking-wider leading-tight mt-0.5">{{ $row->area_name }}</div>
                                </td>

                                {{-- Salesman --}}
                                <td class="text-left">
                                    <div class="font-bold text-xs text-base-content leading-tight">{{ $row->salesman_name }}</div>
                                    <div class="text-[0.6875rem] font-mono text-base-content/60 leading-tight mt-0.5">{{ $row->salesman_code }}</div>
                                </td>

                                {{-- Dynamic Reason Count Cells --}}
                                @foreach($columns as $col)
                                    @php
                                        $cnt = $row->reasons[$col->raw_desc] ?? 0;
                                        $isMax = ($cnt > 0 && $cnt === $row->max_count);
                                    @endphp
                                    <td class="text-right whitespace-nowrap px-2.5 border-l border-base-200">
                                        @if($cnt > 0)
                                            <button type="button"
                                                    wire:click="openDetailModal('{{ $row->salesman_code }}', '{{ addslashes($col->raw_desc) }}')"
                                                    class="inline-flex items-center justify-end px-2 py-0.5 rounded-md font-mono text-xs transition-all {{ $isMax ? 'font-black bg-amber-200/80 dark:bg-amber-900/60 text-slate-950 dark:text-white border border-amber-400 dark:border-amber-600 shadow-2xs hover:bg-amber-300 dark:hover:bg-amber-800' : 'font-semibold text-slate-800 dark:text-slate-200 hover:bg-base-200' }}"
                                                    title="Klik untuk melihat detail toko dengan alasan ini{{ $isMax ? ' (Alasan terbanyak SE ini)' : '' }}">
                                                <span>{{ number_format($cnt) }}</span>
                                            </button>
                                        @else
                                            <span class="text-base-content/25 font-mono text-xs">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Total Kasus --}}
                                <td class="text-right whitespace-nowrap px-3 border-l-2 border-base-content/20">
                                    <button type="button"
                                            wire:click="openDetailModal('{{ $row->salesman_code }}')"
                                            class="inline-flex items-center justify-end gap-1 font-mono text-xs font-black px-2 py-0.5 rounded-md bg-base-200/80 text-base-content hover:bg-primary/20 hover:text-primary transition-all"
                                            title="Lihat semua alasan untuk salesman ini">
                                        {{ number_format($row->total_reason) }}
                                    </button>
                                </td>

                                {{-- Aksi: Tombol Detail Modal --}}
                                <td class="text-center whitespace-nowrap border-l border-base-200">
                                    <button type="button"
                                            wire:click="openDetailModal('{{ $row->salesman_code }}')"
                                            class="btn btn-ghost btn-xs btn-square text-primary hover:bg-primary/10"
                                            title="Buka Rekap Detail Toko SE: {{ $row->salesman_name }}">
                                        <x-heroicon-m-eye class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Sticky Grand Total Footer --}}
                    @if(!empty($totals))
                        <tfoot class="bg-slate-950 dark:bg-black text-white border-t-2 border-primary shadow-[0_-4px_12px_rgba(0,0,0,0.35)] sticky bottom-0 z-20 [&_th]:py-2 [&_th]:px-2.5 divide-x divide-slate-800">
                            <tr class="font-bold text-white [&>th]:align-middle bg-slate-950 dark:bg-black h-8">
                                <th colspan="3" class="text-left pl-3 whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="text-blue-300 font-extrabold tracking-wide">TOTAL {{ number_format($rows->count()) }} SE</span>
                                    <span class="font-normal text-xs text-slate-400 ml-1">({{ count($columns) }} Alasan Berbeda)</span>
                                </th>

                                {{-- Dynamic Reason Totals --}}
                                @foreach($columns as $col)
                                    <th class="text-right whitespace-nowrap font-mono text-xs text-blue-200 bg-slate-950 dark:bg-black px-2.5">
                                        {{ number_format($totals[$col->raw_desc] ?? 0) }}
                                    </th>
                                @endforeach

                                {{-- Grand Total Kasus --}}
                                <th class="text-right whitespace-nowrap font-mono text-sm text-emerald-400 font-extrabold px-3 bg-slate-950 dark:bg-black">
                                    {{ number_format($totals['__grand_total__'] ?? 0) }}
                                </th>

                                {{-- Aksi Blank --}}
                                <th class="text-center whitespace-nowrap font-mono text-slate-500 bg-slate-950 dark:bg-black">-</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            @endif
        </div>
    </div>

    {{-- Detail Modal Toko / Kunjungan Per SE --}}
    <x-ui.modal id="detail-reason-modal"
                title="Daftar Kunjungan & Alasan: {{ $modalSalesmanName }}"
                icon="clipboard-document-list"
                size="7xl"
                :open="$showDetailModal"
                wire:close="closeDetailModal">
        
        <div class="space-y-4">
            {{-- Modal Header Subtitle / Filter Badges --}}
            <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-base-200/50 border border-base-300">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Salesman:</span>
                    <span class="badge badge-primary text-primary-content font-bold font-mono text-xs">{{ $modalSalesmanCode }}</span>
                    <span class="font-bold text-xs text-base-content">{{ $modalSalesmanName }}</span>

                    @if($modalFilterReason)
                        <div class="hidden sm:block w-px h-4 bg-base-300 mx-1"></div>
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/60">Filter Alasan:</span>
                        <span class="badge bg-amber-300 text-slate-950 border-amber-400 font-bold text-xs">
                            {{ $modalFilterReason }}
                        </span>
                        <button type="button" wire:click="openDetailModal('{{ $modalSalesmanCode }}')" class="btn btn-ghost btn-xs text-xs text-primary underline">
                            Tampilkan Semua Alasan
                        </button>
                    @endif
                </div>

                <div class="text-xs font-mono font-bold text-base-content/70">
                    Total: <span class="text-primary">{{ count($modalStoreList) }}</span> Toko
                </div>
            </div>

            {{-- Store Table --}}
            <div class="overflow-x-auto max-h-[60vh] border border-base-300 rounded-xl">
                <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs">
                    <thead class="sticky top-0 bg-base-300 text-base-content z-10 font-bold uppercase text-[0.6875rem]">
                        <tr>
                            <th class="w-10 text-center">#</th>
                            <th class="w-24 text-left">Tanggal</th>
                            <th class="w-28 text-center">Jam Kunjung</th>
                            <th class="w-28 text-left">Kode Toko</th>
                            <th class="text-left min-w-[200px]">Nama Toko</th>
                            <th class="w-20 text-center">Rute PJP</th>
                            <th class="w-24 text-center">Status</th>
                            <th class="w-24 text-center">Tipe Alasan</th>
                            <th class="min-w-[220px] text-left">Alasan</th>
                            <th class="min-w-[200px] text-left">Catatan Salesman</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-200">
                        @forelse($modalStoreList as $idx => $st)
                            <tr class="hover:bg-primary/5">
                                <td class="text-center font-mono text-base-content/50">{{ $idx + 1 }}</td>
                                <td class="font-mono text-base-content">
                                    {{ \Carbon\Carbon::parse($st->tanggal)->format('d/m/Y') }}
                                </td>
                                <td class="text-center font-mono text-base-content/70 text-[0.6875rem]">
                                    @if(!empty($st->time_in) && !empty($st->time_out))
                                        {{ substr($st->time_in, 0, 5) }} - {{ substr($st->time_out, 0, 5) }}
                                    @elseif(!empty($st->time_in))
                                        {{ substr($st->time_in, 0, 5) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="font-mono font-bold text-base-content">{{ $st->custno }}</td>
                                <td class="font-semibold text-base-content">{{ $st->custname }}</td>
                                <td class="text-center">
                                    @if(strtoupper($st->flag_pjp ?? '') === 'Y' || (int)($st->flag_pjp ?? 0) === 1)
                                        <span class="badge badge-xs badge-info font-bold">PJP</span>
                                    @else
                                        <span class="badge badge-xs badge-ghost font-bold text-base-content/60">Extra</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if((int)($st->flag_visit ?? 0) === 1)
                                        <span class="badge badge-xs badge-success text-white font-bold">Visited</span>
                                    @else
                                        <span class="badge badge-xs badge-error text-white font-bold">Unvisited</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(strtoupper($st->reason_type ?? '') === 'BUY')
                                        <span class="badge badge-xs bg-amber-500 text-white font-bold">Not Buy</span>
                                    @elseif(strtoupper($st->reason_type ?? '') === 'VISIT')
                                        <span class="badge badge-xs bg-rose-500 text-white font-bold">Not Visit</span>
                                    @else
                                        <span class="badge badge-xs badge-neutral">{{ $st->reason_type }}</span>
                                    @endif
                                </td>
                                <td class="font-medium text-base-content max-w-xs truncate" title="{{ $st->reason_desc }}">
                                    {{ $st->reason_desc }}
                                </td>
                                <td class="text-base-content/70 italic max-w-xs truncate" title="{{ $st->reason_note }}">
                                    {{ $st->reason_note ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-base-content/40">
                                    Tidak ada data kunjungan toko untuk alasan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between w-full">
                <span class="text-xs text-base-content/50">Maksimal 300 data kunjungan terbaru ditampilkan di modal.</span>
                <button type="button" wire:click="closeDetailModal" class="btn btn-sm btn-ghost">
                    Tutup
                </button>
            </div>
        </x-slot:footer>
    </x-ui.modal>
</div>
