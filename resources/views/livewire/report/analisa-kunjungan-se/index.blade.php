<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Analisa Kunjungan SE</x-slot>

    {{-- Standar Tab Menu dari resources/views/components/ui/tab-menu.blade.php --}}
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.index')" :navigate="true">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.summary')" :navigate="true">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
        </x-ui.tab-item>
    </x-ui.tab-menu>

    {{-- 5 KPI Cards Section (Tetap statis di atas sesuai standar template) --}}
    @include('livewire.report.analisa-kunjungan-se.kpi-cards')

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden"
        x-data="{
            expandedDates: [],
            allDates: {{ json_encode(($dailyData['summaries'] ?? collect())->pluck('tanggal')->values()->toArray()) }},
            toggle(tgl) {
                if (this.expandedDates.includes(tgl)) {
                    this.expandedDates = this.expandedDates.filter(d => d !== tgl);
                } else {
                    this.expandedDates.push(tgl);
                }
            },
            expandAll() {
                this.expandedDates = [...this.allDates];
            },
            collapseAll() {
                this.expandedDates = [];
            },
            isExpanded(tgl) {
                return this.expandedDates.includes(tgl);
            }
        }">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base md:text-lg font-bold">Data Kunjungan SE</h2>
                    {{-- Territory pills for locked/single access (when filters are hidden) --}}
                    @if($accessLevel !== 'nasional' && count($regions) <= 1 && !empty($selectedRegion))
                        @php
                            $currReg = collect($regions)->firstWhere('region_code', $selectedRegion);
                        @endphp
                        @if($currReg)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-primary/10 text-primary border border-primary/20" title="Wilayah Region Anda">
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
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-secondary/10 text-secondary border border-secondary/20" title="Wilayah Area Anda">
                                <x-heroicon-m-building-office class="w-3 h-3" />
                                {{ $currArea->area_name }}
                            </span>
                        @endif
                    @endif
                </div>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar Kunjungan & Transaksi Sales Exclusive (SE)</p>
            </div>
            
            {{-- Filter Bar & Action Buttons --}}
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 w-full lg:w-auto">
                {{-- Filter Region (Hanya tampil jika user memiliki > 1 region / level nasional) --}}
                @if(count($regions) > 1 || $accessLevel === 'nasional')
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs">
                        <option value="">Pilih Region</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Area (Hanya tampil jika user memiliki > 1 area / user di atas level area) --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs" @disabled(empty($selectedRegion))>
                        <option value="">Pilih Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Distributor --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="selectedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[150px] text-xs" @disabled(empty($selectedArea)) wire:target="selectedArea, selectedRegion" wire:loading.class="opacity-60">
                        <option value="" disabled {{ empty($selectedDistributor) ? 'selected' : '' }}>Pilih Distributor</option>
                        @foreach($distributors as $d)
                            <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Salesman (SE) - Wajib Pilih SE Spesifik (Tidak Bisa Pilih Semua) --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="selectedSalesman" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[140px] text-xs" @disabled(empty($selectedDistributor) || count($salesmen) === 0) wire:target="selectedDistributor" wire:loading.class="opacity-60">
                        @if(empty($selectedSalesman))
                            <option value="" disabled selected>{{ empty($selectedDistributor) ? 'Pilih SE' : (count($salesmen) === 0 ? 'Tidak Ada SE' : 'Pilih SE') }}</option>
                        @endif
                        @foreach($salesmen as $s)
                            <option value="{{ $s->salesman_code }}">{{ $s->salesman_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Smart Date Range Filter (Maks. 31 Hari / 1 Bulan) --}}
                <div class="flex items-center gap-1 bg-base-100 px-1 py-0.5 rounded-xl border border-base-300 shadow-2xs w-full sm:w-auto">
                    {{-- Tombol Stepper Bulan Sebelumnya --}}
                    <button type="button" wire:click="previousMonth" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-base-content" title="Bulan Sebelumnya">
                        <x-heroicon-m-chevron-left class="w-3.5 h-3.5" />
                    </button>

                    {{-- Input Tanggal Mulai --}}
                    <input type="date" 
                        wire:model.live="startDate" 
                        max="{{ $endDate ?: date('Y-m-d') }}"
                        min="{{ $endDate ? \Carbon\Carbon::parse($endDate)->subDays(30)->format('Y-m-d') : '' }}"
                        class="input input-xs bg-transparent border-none text-xs w-[110px] focus:outline-none p-0 text-center font-medium" 
                        title="Tanggal Mulai" />

                    <span class="text-[10px] font-semibold text-base-content/40">s/d</span>

                    {{-- Input Tanggal Akhir --}}
                    <input type="date" 
                        wire:model.live="endDate" 
                        min="{{ $startDate ?: '' }}"
                        max="{{ $startDate ? \Carbon\Carbon::parse($startDate)->addDays(30)->format('Y-m-d') : date('Y-m-d') }}"
                        class="input input-xs bg-transparent border-none text-xs w-[110px] focus:outline-none p-0 text-center font-medium" 
                        title="Tanggal Akhir (Maks. 31 hari dari tanggal mulai)" />

                    {{-- Badge Durasi Hari --}}
                    @php
                        $diffDays = ($startDate && $endDate) ? (\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1) : 0;
                    @endphp
                    <div class="tooltip tooltip-bottom" data-tip="{{ $diffDays }} hari dipilih (Maks. 31 hari)">
                        <span class="badge badge-xs {{ $diffDays >= 31 ? 'badge-warning text-warning-content' : 'badge-neutral text-neutral-content' }} font-mono text-[9px] font-bold px-1.5 cursor-default">
                            {{ $diffDays }}h
                        </span>
                    </div>

                    {{-- Tombol Stepper Bulan Berikutnya --}}
                    <button type="button" wire:click="nextMonth" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-base-content" title="Bulan Berikutnya">
                        <x-heroicon-m-chevron-right class="w-3.5 h-3.5" />
                    </button>

                    {{-- Dropdown Pintasan Periode Cepat --}}
                    <div class="dropdown dropdown-end">
                        <button type="button" tabindex="0" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-primary" title="Pintasan Periode Cepat">
                            <x-heroicon-m-calendar-days class="w-3.5 h-3.5" />
                        </button>
                        <ul tabindex="0" class="dropdown-content z-30 menu menu-xs p-1.5 shadow-xl bg-base-100 rounded-xl border border-base-300 w-36 text-xs">
                            <li class="menu-title text-[9px] uppercase tracking-wider text-base-content/50 px-2 py-0.5">Pintasan Periode</li>
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

                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto justify-end">
                    {{-- Terapkan --}}
                    @php
                        $canApply = !empty($selectedRegion) && !empty($selectedArea) && !empty($selectedDistributor) && !empty($selectedSalesman);
                    @endphp
                    @if(!$canApply)
                        <div class="tooltip tooltip-bottom" data-tip="Pilih Region, Area, Distributor, dan SE terlebih dahulu">
                            <x-ui.button variant="primary" size="sm" class="rounded-xl px-3 font-semibold shadow-sm" disabled icon="funnel">
                                Terapkan
                            </x-ui.button>
                        </div>
                    @else
                        <x-ui.button variant="primary" size="sm" class="rounded-xl px-3 font-semibold shadow-sm" wire:click="applyFilter" spinner="applyFilter" icon="funnel">
                            Terapkan
                        </x-ui.button>
                    @endif

                    {{-- Reset --}}
                    <x-ui.button variant="ghost" size="sm" class="rounded-xl px-2.5 text-base-content/70 hover:text-base-content" wire:click="resetFilter" spinner="resetFilter" icon="arrow-path">
                        Reset
                    </x-ui.button>

                    {{-- Peta Semua Titik (On-Demand Loading via Livewire Event) --}}
                    @php
                        $isMapDisabled = empty($appliedRegion) || empty($appliedArea) || empty($appliedDistributor) || empty($appliedSalesman);
                    @endphp
                    <div class="tooltip tooltip-bottom" data-tip="{{ $isMapDisabled ? 'Terapkan filter terlebih dahulu' : 'Buka Peta Sebaran Kunjungan SE' }}">
                        <button type="button" 
                            class="btn btn-sm btn-outline btn-info rounded-xl gap-1 text-xs"
                            wire:click="openAllMapsModal"
                            wire:loading.attr="disabled"
                            wire:target="openAllMapsModal"
                            @disabled($isMapDisabled)>
                            <span wire:loading.remove wire:target="openAllMapsModal" class="flex items-center gap-1">
                                <x-heroicon-s-map class="w-4 h-4" />
                                Peta
                            </span>
                            <span wire:loading wire:target="openAllMapsModal" class="flex items-center gap-1">
                                <span class="loading loading-spinner loading-xs"></span>
                                Memuat...
                            </span>
                        </button>
                    </div>

                    {{-- Export Excel --}}
                    @if($canExport)
                        @php
                            $isExportDisabled = empty($appliedRegion) || empty($appliedArea) || empty($appliedDistributor) || empty($appliedSalesman);
                            $hasPendingFilters = $selectedRegion !== $appliedRegion || 
                                                 $selectedArea !== $appliedArea || 
                                                 $selectedDistributor !== $appliedDistributor || 
                                                 $selectedSalesman !== $appliedSalesman || 
                                                 $startDate !== $appliedStartDate || 
                                                 $endDate !== $appliedEndDate;
                        @endphp
                        <div class="tooltip tooltip-left" data-tip="{{ $isExportDisabled || $hasPendingFilters ? 'Terapkan filter terlebih dahulu' : 'Export Excel' }}">
                            <x-ui.button variant="success" size="sm" class="rounded-xl text-white px-3 shadow-sm font-semibold" wire:click="export" spinner="export" icon="arrow-down-tray" :disabled="$isExportDisabled || $hasPendingFilters">
                                Excel
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Master-Detail: Summary Harian & Expand Detail Toko) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap text-xs">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:py-2.5 [&_th]:px-2.5">
                    <tr class="[&>th]:align-middle">
                        <th class="w-12 text-center">No</th>
                        <th>Tanggal & Hari</th>
                        <th class="text-center">Jam Kerja</th>
                        <th class="text-center">Plan Call</th>
                        <th class="text-center">Actual Call</th>
                        <th class="text-center">Effective Call (EC)</th>
                        <th class="text-right">Total Order (Rp)</th>
                        <th class="text-center">Waktu Toko vs Jalan</th>
                        <th class="text-center">Kepatuhan Radius</th>
                    </tr>
                </thead>
                <tbody class="text-xs relative" wire:loading.class="opacity-50 pointer-events-none transition-opacity duration-200" wire:target="applyFilter, resetFilter">
                @php
                    $summaries = $dailyData['summaries'] ?? collect();
                @endphp
                @forelse($summaries as $day)
                    {{-- Master Row: Summary Harian (Clickable & Clean without separate AKSI column) --}}
                    <tr @click="toggle('{{ $day->tanggal }}')" 
                        class="hover:bg-primary/5 transition-colors cursor-pointer border-b border-base-200 select-none [&_td]:py-2 [&_td]:px-2.5" 
                        :class="isExpanded('{{ $day->tanggal }}') ? '!bg-primary/10 font-medium' : ''">
                        
                        {{-- No & Chevron --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" class="btn btn-2xs btn-ghost btn-circle text-primary hover:bg-primary/10" title="Buka/tutup rincian toko">
                                    <x-heroicon-m-chevron-right class="w-4 h-4 transition-transform duration-200" ::class="isExpanded('{{ $day->tanggal }}') ? 'rotate-90 text-primary' : 'text-base-content/40'" />
                                </button>
                                <span class="font-mono text-base-content/60 text-xs font-semibold">{{ $loop->iteration }}</span>
                            </div>
                        </td>

                        {{-- Tanggal & Hari (dengan Icon Peta Pintasan) --}}
                        <td class="whitespace-nowrap">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-xs {{ $day->is_weekend ? 'text-amber-600 dark:text-amber-400' : 'text-base-content' }}">
                                    {{ $day->day_name }}
                                </span>
                                <span class="text-xs text-base-content/70 font-mono">{{ $day->formatted_date }}</span>
                                @if($day->is_saturday)
                                    <span class="inline-flex items-center px-1 py-0.2 rounded text-[8px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 leading-tight">Sabtu</span>
                                @endif
                                @if(count($day->map_points) > 0)
                                    <button type="button" 
                                        @click.stop="$dispatch('open-all-maps-modal', {{ json_encode($day->map_points) }})"
                                        class="inline-flex items-center justify-center w-5 h-5 rounded hover:bg-info/20 text-info transition-colors"
                                        title="Buka Peta Rute Hari Ini ({{ $day->formatted_date }})">
                                        <x-heroicon-s-map class="w-3.5 h-3.5" />
                                    </button>
                                @endif
                            </div>
                        </td>

                        {{-- Jam Kerja --}}
                        <td class="text-center whitespace-nowrap font-mono text-xs">
                            @if($day->first_time_in !== '-' || $day->last_time_out !== '-')
                                <span class="font-semibold text-base-content">{{ $day->first_time_in }} - {{ $day->last_time_out }}</span>
                                <span class="block text-[10px] text-base-content/50 font-normal">({{ $day->work_duration_str }})</span>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>

                        {{-- Plan Call --}}
                        <td class="text-center whitespace-nowrap">
                            <span class="font-mono font-bold text-xs">{{ $day->total_call }} Toko</span>
                            <span class="text-[10px] text-base-content/50 block font-mono">{{ $day->pc }} PC • {{ $day->xc }} XC</span>
                        </td>

                        {{-- Actual Call (Merah jika < 100%, Hijau jika 100%) --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="font-mono font-semibold text-xs">{{ $day->total_visit }} / {{ $day->total_call }}</span>
                                @php
                                    $vClass = $day->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';
                                @endphp
                                <span class="badge badge-xs {{ $vClass }} font-bold font-mono px-1.5 text-[9px] leading-tight">
                                    {{ $day->visit_percent }}%
                                </span>
                            </div>
                        </td>

                        {{-- Effective Call (EC) - Semantic Color: < 80% Merah, 80-99% Kuning, >= 100% Hijau --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="font-mono font-semibold text-xs">{{ $day->total_ec }} Toko</span>
                                @php
                                    $ecP = $day->ec_percent;
                                    $ecClass = $ecP >= 100 ? 'badge-success text-white' : ($ecP >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white');
                                @endphp
                                <span class="badge badge-xs {{ $ecClass }} font-bold font-mono px-1.5 text-[9px] leading-tight">
                                    {{ $day->ec_percent }}%
                                </span>
                            </div>
                        </td>

                        {{-- Total Order (Rp) - Top Sales Highlight --}}
                        <td class="text-right whitespace-nowrap font-mono text-xs">
                            @if($day->total_val_order >= 10000000)
                                <div class="flex items-center justify-end gap-1">
                                    <span class="badge badge-xs badge-success text-white text-[8px] font-bold px-1 uppercase tracking-wider">Top</span>
                                    <span class="text-success font-bold">Rp {{ number_format($day->total_val_order, 0, ',', '.') }}</span>
                                </div>
                            @elseif($day->total_val_order > 0)
                                <span class="text-primary font-semibold">Rp {{ number_format($day->total_val_order, 0, ',', '.') }}</span>
                            @else
                                <span class="text-base-content/30">Rp 0</span>
                            @endif
                        </td>

                        {{-- Waktu Toko vs Jalan (Mini 2-Color Ratio Bar) --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="inline-flex flex-col items-center gap-1 font-mono text-[11px] w-full max-w-[145px]">
                                <div class="inline-flex items-center justify-center gap-1 w-full text-[10px]">
                                    <span class="badge badge-xs badge-ghost border-base-300 gap-0.5 text-[9px] px-1 py-0.5" title="Waktu di Toko: {{ $day->consume_percent }}%">
                                        🏬 {{ $day->consume_str }}
                                    </span>
                                    <span class="badge badge-xs badge-ghost border-base-300 gap-0.5 text-[9px] px-1 py-0.5" title="Waktu di Jalan: {{ $day->travel_percent }}%">
                                        🛵 {{ $day->travel_str }}
                                    </span>
                                </div>
                                <div class="w-full bg-amber-500/20 rounded-full h-1 flex overflow-hidden shadow-2xs" title="Proporsi Waktu: {{ $day->consume_percent }}% di Toko, {{ $day->travel_percent }}% di Jalan">
                                    <div class="bg-success h-full transition-all duration-300" style="width: {{ $day->consume_percent }}%"></div>
                                    <div class="bg-amber-500 h-full transition-all duration-300" style="width: {{ $day->travel_percent }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Kepatuhan Radius --}}
                        <td class="text-center whitespace-nowrap">
                            @if($day->out_of_radius_count == 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-success/10 text-success border border-success/30">
                                    ✓ Sesuai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-error text-white shadow-2xs" title="{{ $day->out_of_radius_count }} toko check-in melebihi 50 meter dari master">
                                    ⚠️ {{ $day->out_of_radius_count }} Toko >50m
                                </span>
                            @endif
                        </td>
                    </tr>

                    {{-- Detail Row: Sub-Tabel Kunjungan Toko (Expandable) --}}
                    <tr x-show="isExpanded('{{ $day->tanggal }}')" x-cloak class="bg-base-200/40">
                        <td colspan="9" class="p-2 sm:p-3">
                            <div class="bg-base-100 rounded-xl border border-slate-300 dark:border-slate-700 shadow-md overflow-hidden">
                                {{-- Sub-Table Header Bar: Executive Day Summary Strip --}}
                                <div class="px-3.5 py-2 bg-slate-900 text-slate-100 flex flex-wrap items-center justify-between gap-2.5 border-b border-slate-700">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></span>
                                        <span class="font-bold text-xs tracking-wide text-white">Detail Rute: {{ $day->day_name }}, {{ $day->formatted_date }}</span>
                                        <div class="flex items-center gap-1.5 flex-wrap text-[10px]">
                                            <span class="badge badge-sm badge-neutral font-mono font-bold bg-slate-800 text-slate-200 border border-slate-700">{{ count($day->items) }} Toko</span>
                                            <span class="badge badge-sm {{ $day->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white' }} font-bold font-mono">
                                                {{ $day->total_visit }} Visit ({{ $day->visit_percent }}%)
                                            </span>
                                            <span class="badge badge-sm {{ $day->ec_percent >= 100 ? 'badge-success text-white' : ($day->ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white') }} font-bold font-mono">
                                                {{ $day->total_ec }} EC ({{ $day->ec_percent }}%)
                                            </span>
                                            @if($day->total_val_order > 0)
                                                <span class="badge badge-sm bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-mono font-bold">
                                                    Rp {{ number_format($day->total_val_order, 0, ',', '.') }}
                                                </span>
                                            @endif
                                            @if($day->out_of_radius_count > 0)
                                                <span class="badge badge-sm badge-error text-white font-bold">
                                                    ⚠️ {{ $day->out_of_radius_count }} Toko >50m
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        @if(count($day->map_points) > 0)
                                            <button type="button" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-sky-500 text-white hover:bg-sky-600 transition-colors shadow-2xs cursor-pointer"
                                                @click="$dispatch('open-all-maps-modal', {{ json_encode($day->map_points) }})">
                                                <x-heroicon-s-map class="w-3.5 h-3.5" />
                                                Peta Rute Hari Ini
                                            </button>
                                        @endif
                                        <button type="button" @click="toggle('{{ $day->tanggal }}')" class="px-2 py-1 rounded-lg text-xs text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                                            ✕ Tutup
                                        </button>
                                    </div>
                                </div>

                                {{-- Sub-Table Body (Ultra-Compact 1-Line Row Height, High Contrast Sticky Header) --}}
                                <div class="overflow-x-auto max-h-[420px]">
                                    <table class="table table-xs table-zebra w-full whitespace-nowrap text-[11px] leading-tight [&_th]:py-1.5 [&_th]:px-2 [&_td]:py-1 [&_td]:px-2">
                                        <thead class="sticky top-0 z-20 shadow-sm border-b border-slate-700 uppercase font-bold tracking-wider text-[10px] text-white [&_th]:bg-slate-800 dark:[&_th]:bg-slate-900 [&_th]:text-slate-100 [&_th]:border-b [&_th]:border-slate-700">
                                            <tr>
                                                <th class="w-8 text-center">#</th>
                                                <th class="text-center">Jam Kerja</th>
                                                <th class="text-center">Durasi Toko</th>
                                                <th class="text-center">Durasi Jalan</th>
                                                <th>Kode Toko</th>
                                                <th>Nama Toko / Outlet</th>
                                                <th class="text-center">PJP</th>
                                                <th class="text-center">Visit</th>
                                                <th class="text-center">Order</th>
                                                <th class="text-right">Nilai Order</th>
                                                <th class="text-center">Jarak Master</th>
                                                <th class="text-center">Alasan / Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-xs">
                                            @foreach($day->items as $item)
                                            <tr class="hover:bg-primary/5 transition-colors border-b border-base-200/60">
                                                <td class="font-mono text-center text-base-content/50">{{ $item->row_num }}</td>
                                                <td class="text-center font-mono font-medium">
                                                    {{ $item->time_in_fmt }} - {{ $item->time_out_fmt }}
                                                </td>
                                                <td class="text-center font-mono font-medium text-base-content/80" title="{{ $item->time_consume ?? '-' }}">
                                                    🏬 {{ $item->time_consume_human }}
                                                </td>
                                                <td class="text-center font-mono text-base-content/60" title="{{ $item->time_travel ?? '-' }}">
                                                    🛵 {{ $item->time_travel_human }}
                                                </td>
                                                <td class="font-mono text-base-content/70">{{ $item->custno }}</td>
                                                <td class="max-w-[220px] truncate font-medium text-base-content" title="{{ $item->custname }}">
                                                    {{ $item->custname }}
                                                </td>
                                                <td class="text-center">
                                                    @if($item->flag_pjp === 'R')
                                                        <span class="badge badge-xs badge-success text-white font-mono font-bold px-1.5" title="Sesuai Rute PJP">PJP</span>
                                                    @else
                                                        <span class="badge badge-xs badge-warning text-slate-900 font-mono font-bold px-1.5" title="Extra Call (Luar Rute PJP)">XC</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($item->flag_visit === 'Y')
                                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-success/15 text-success font-bold text-xs" title="Toko Dikunjungi">✓</span>
                                                    @else
                                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-error/15 text-error font-bold text-xs" title="Tidak Dikunjungi">✕</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($item->flag_ec === 'Y')
                                                        <button type="button" 
                                                            x-data="{ qty: {{ (float)($item->qty_order ?? 0) }}, val: {{ (float)($item->val_order ?? 0) }} }" 
                                                            @click="$dispatch('open-order-modal', {qty, val})" 
                                                            class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-success text-white font-bold text-xs shadow-2xs hover:scale-110 transition-transform cursor-pointer" 
                                                            title="Ada Order (Klik untuk rincian Qty/Value)">
                                                            ✓
                                                        </button>
                                                    @else
                                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-base-200 text-base-content/30 font-bold text-xs" title="Tanpa Order">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-right font-mono font-medium">
                                                    @if((float)($item->val_order ?? 0) > 0)
                                                        <button type="button" 
                                                            x-data="{ qty: {{ (float)($item->qty_order ?? 0) }}, val: {{ (float)($item->val_order ?? 0) }} }" 
                                                            @click="$dispatch('open-order-modal', {qty, val})" 
                                                            class="text-success font-bold hover:underline cursor-pointer"
                                                            title="Klik untuk rincian order">
                                                            Rp {{ number_format($item->val_order, 0, ',', '.') }}
                                                        </button>
                                                    @else
                                                        <span class="text-base-content/30">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $distM = (int) ($item->distance ?? 0);
                                                        $distStr = $distM >= 1000 ? number_format($distM / 1000, 1, ',', '.') . ' km' : $distM . 'm';
                                                    @endphp
                                                    <button type="button" 
                                                        @click="$dispatch('open-map-modal', {masterLat: '{{ $item->master_lat }}', masterLon: '{{ $item->master_lon }}', visitLat: '{{ $item->visit_lat }}', visitLon: '{{ $item->visit_lon }}'})" 
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono cursor-pointer transition-colors {{ $item->is_out_of_radius ? 'bg-error text-white font-bold hover:bg-error/85 shadow-2xs' : 'bg-success/15 text-success border border-success/30 hover:bg-success/25 font-semibold' }}"
                                                        title="{{ $item->is_out_of_radius ? 'Luar radius 50m (' . number_format($distM, 0, ',', '.') . 'm). Klik untuk buka peta' : 'Sesuai radius (' . $distM . 'm). Klik untuk buka peta' }}">
                                                        <span>📍 {{ $distStr }}</span>
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    @if($item->reason_type || $item->reason_desc)
                                                        @php
                                                            $rawReason = $item->reason_desc ?? $item->reason_type ?? '';
                                                            $cleanReason = trim(preg_replace('/\s*\(.*?\)\s*/', '', $rawReason));
                                                            if (empty($cleanReason)) $cleanReason = $rawReason;
                                                        @endphp
                                                        <button type="button" 
                                                            @click="$dispatch('open-reason-modal', {type: '{{ addslashes($item->reason_type ?? '') }}', desc: '{{ addslashes($item->reason_desc ?? '') }}'})" 
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 hover:bg-amber-500/25 max-w-[140px] truncate cursor-pointer transition-colors"
                                                            title="{{ $rawReason }} (Klik untuk lihat detail)">
                                                            <span>📝 {{ Str::limit($cleanReason, 15) }}</span>
                                                        </button>
                                                    @else
                                                        <span class="text-base-content/30">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-12 text-base-content/40">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <x-heroicon-o-inbox class="w-12 h-12" />
                                @if(empty($appliedRegion) || empty($appliedArea) || empty($appliedDistributor) || empty($appliedSalesman))
                                    <p class="text-sm font-semibold">Silakan pilih Region, Area, Distributor, dan Salesman (SE), lalu klik tombol <span class="text-primary font-bold">Terapkan</span>.</p>
                                @else
                                    <p class="text-sm">Tidak ada data kunjungan ditemukan untuk filter yang dipilih.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>

                {{-- Grand Total Table Footer (Solid, Distinct, Sticky & Highlighted) --}}
                @if(!empty($dailyData['grandTotal']))
                    <tfoot class="bg-base-200 border-t-2 border-primary/40 text-xs shadow-md sticky bottom-0 z-10 [&_th]:py-2.5 [&_th]:px-2.5">
                        <tr class="font-bold text-base-content [&>th]:align-middle">
                            <th class="text-center font-mono">Σ</th>
                            <th class="whitespace-nowrap">Total {{ $dailyData['grandTotal']->total_days }} Hari Kerja</th>
                            <th class="text-center font-mono text-[11px] text-base-content/80 font-normal whitespace-nowrap">
                                Avg {{ $dailyData['grandTotal']->avg_work_str }}/hari
                            </th>
                            <th class="text-center font-mono whitespace-nowrap">
                                {{ $dailyData['grandTotal']->total_call }} Toko
                                <span class="block text-[9px] font-normal text-base-content/60 font-mono">{{ $dailyData['grandTotal']->pc }} PC • {{ $dailyData['grandTotal']->xc }} XC</span>
                            </th>
                            <th class="text-center font-mono whitespace-nowrap">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="font-bold">{{ $dailyData['grandTotal']->total_visit }} Toko</span>
                                    @php
                                        $gtVisitClass = $dailyData['grandTotal']->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';
                                    @endphp
                                    <span class="badge badge-xs {{ $gtVisitClass }} font-bold font-mono px-1.5 text-[9px] leading-tight">
                                        {{ $dailyData['grandTotal']->visit_percent }}%
                                    </span>
                                </div>
                            </th>
                            <th class="text-center font-mono whitespace-nowrap">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="font-bold">{{ $dailyData['grandTotal']->total_ec }} Toko</span>
                                    @php
                                        $gtEcPercent = $dailyData['grandTotal']->ec_percent;
                                        $gtEcClass = $gtEcPercent >= 100 ? 'badge-success text-white' : ($gtEcPercent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white');
                                    @endphp
                                    <span class="badge badge-xs {{ $gtEcClass }} font-bold font-mono px-1.5 text-[9px] leading-tight">
                                        {{ $gtEcPercent }}%
                                    </span>
                                </div>
                            </th>
                            <th class="text-right font-mono text-success font-bold whitespace-nowrap">
                                Rp {{ number_format($dailyData['grandTotal']->total_val_order, 0, ',', '.') }}
                            </th>
                            <th class="text-center font-mono text-[10px]">
                                <div class="inline-flex flex-col items-center gap-1 w-full max-w-[145px]">
                                    <div class="inline-flex items-center justify-center gap-1.5 w-full text-[10px]">
                                        <span>🏬 {{ $dailyData['grandTotal']->consume_str }}</span>
                                        <span>🛵 {{ $dailyData['grandTotal']->travel_str }}</span>
                                    </div>
                                    <div class="w-full bg-amber-500/20 rounded-full h-1 flex overflow-hidden shadow-2xs" title="Proporsi Waktu Keseluruhan: {{ $dailyData['grandTotal']->consume_percent }}% Toko, {{ $dailyData['grandTotal']->travel_percent }}% Jalan">
                                        <div class="bg-success h-full" style="width: {{ $dailyData['grandTotal']->consume_percent }}%"></div>
                                        <div class="bg-amber-500 h-full" style="width: {{ $dailyData['grandTotal']->travel_percent }}%"></div>
                                    </div>
                                </div>
                            </th>
                            <th class="text-center whitespace-nowrap">
                                @if($dailyData['grandTotal']->out_of_radius_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-error text-white shadow-2xs">
                                        ⚠️ {{ $dailyData['grandTotal']->out_of_radius_count }} Toko >50m
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-success/15 text-success border border-success/30">
                                        ✓ Semua Sesuai
                                    </span>
                                @endif
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        
        {{-- Footer Card --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            @if(empty($appliedRegion) || empty($appliedArea) || empty($appliedDistributor) || empty($appliedSalesman))
                <div class="text-base-content/60 text-center sm:text-left italic">
                    Gunakan filter di atas dan klik Terapkan untuk menampilkan data.
                </div>
            @else
                <div class="text-base-content/70 text-center sm:text-left flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-success"></span>
                    <span>Menampilkan <strong class="text-base-content font-bold">{{ $dailyData['grandTotal']->total_days ?? 0 }} hari kerja</strong> (<span class="font-mono">{{ $dailyData['grandTotal']->total_call ?? 0 }}</span> total rencana kunjungan toko)</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="expandAll" class="btn btn-xs btn-outline btn-primary rounded-xl gap-1">
                        <x-heroicon-m-arrows-pointing-out class="w-3.5 h-3.5" />
                        Buka Semua
                    </button>
                    <button type="button" @click="collapseAll" class="btn btn-xs btn-ghost rounded-xl gap-1 text-base-content/70">
                        <x-heroicon-m-arrows-pointing-in class="w-3.5 h-3.5" />
                        Tutup Semua
                    </button>
                </div>
            @endif
        </div>

    </div>

    {{-- Modals Container (Dibuat h-0 agar tidak menambah tinggi flexbox layout) --}}
    <div class="shrink-0 h-0 w-0 overflow-visible">
        {{-- Modal Detail Order --}}
    <div x-data="{ open: false, qty: 0, val: 0 }" 
         @open-order-modal.window="qty = $event.detail.qty; val = $event.detail.val; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Detail Order Kunjungan SE</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Qty Order</p>
                        <p class="text-xl font-bold" x-text="Number(qty).toLocaleString('id-ID')"></p>
                    </div>
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Value Order</p>
                        <p class="text-xl font-bold font-mono text-primary">Rp <span x-text="Number(val).toLocaleString('id-ID')"></span></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Detail Reason --}}
    <div x-data="{ open: false, type: '', desc: '' }"
         @open-reason-modal.window="type = $event.detail.type; desc = $event.detail.desc; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <x-heroicon-s-information-circle class="w-5 h-5 text-warning" />
                    Detail Reason Kunjungan
                </h3>
                <div class="space-y-4">
                    <div class="p-3 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Type</p>
                        <p class="text-sm font-bold text-warning" x-text="type || '-'"></p>
                    </div>
                    <div class="p-3 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Deskripsi</p>
                        <p class="text-sm text-base-content/80 leading-relaxed" x-text="desc || '-'"></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Single Map --}}
    <div x-data="{ 
            open: false,
            mapData: null,
            initMap(data) {
                if(data) this.mapData = data;
                else data = this.mapData;
                
                this.open = true;

                if (typeof maplibregl === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js';
                    script.onload = () => this.initMap(data);
                    document.head.appendChild(script);

                    const css = document.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css';
                    document.head.appendChild(css);
                    return;
                }

                setTimeout(() => {
                    if (this.mapInstance) {
                        this.mapInstance.remove();
                        this.mapInstance = null;
                    }
                    
                    this.mapInstance = new maplibregl.Map({
                        container: 'visit-map-se',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: [118.0148, -2.5489],
                        zoom: 5
                    });

                    let mLat = parseFloat(data.masterLat);
                    let mLon = parseFloat(data.masterLon);
                    let vLat = parseFloat(data.visitLat);
                    let vLon = parseFloat(data.visitLon);

                    // Deteksi jika koordinat master terbalik
                    if (Math.abs(mLat) > 90) {
                        const temp = mLat; mLat = mLon; mLon = temp;
                    }
                    // Deteksi jika koordinat visit terbalik
                    if (Math.abs(vLat) > 90) {
                        const temp = vLat; vLat = vLon; vLon = temp;
                    }

                    const bounds = new maplibregl.LngLatBounds();
                    let hasMaster = !isNaN(mLat) && !isNaN(mLon);
                    let hasVisit = !isNaN(vLat) && !isNaN(vLon);

                    if (hasMaster) {
                        new maplibregl.Marker({ color: '#ef4444' })
                            .setLngLat([mLon, mLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Master Point</strong><br><span class='text-[10px] text-gray-500 font-mono'>📍 ${mLat}, ${mLon}</span></div>`))
                            .addTo(this.mapInstance);
                        bounds.extend([mLon, mLat]);
                    }

                    if (hasVisit) {
                        new maplibregl.Marker({ color: '#3b82f6' })
                            .setLngLat([vLon, vLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Visit Point SE</strong><br><span class='text-[10px] text-gray-500 font-mono'>📍 ${vLat}, ${vLon}</span></div>`))
                            .addTo(this.mapInstance);
                        bounds.extend([vLon, vLat]);
                    }

                    if (!bounds.isEmpty()) {
                        this.mapInstance.fitBounds(bounds, { padding: 50, maxZoom: 18, animate: false });
                    }

                    const initRoute = () => {
                        if (hasMaster && hasVisit) {
                            this.mapInstance.addSource('route', {
                                'type': 'geojson',
                                'data': {
                                    'type': 'Feature',
                                    'geometry': {
                                        'type': 'LineString',
                                        'coordinates': [
                                            [mLon, mLat],
                                            [vLon, vLat]
                                        ]
                                    }
                                }
                            });
                            this.mapInstance.addLayer({
                                'id': 'route',
                                'type': 'line',
                                'source': 'route',
                                'layout': {
                                    'line-join': 'round',
                                    'line-cap': 'round'
                                },
                                'paint': {
                                    'line-color': '#22c55e',
                                    'line-width': 3,
                                    'line-dasharray': [2, 2]
                                }
                            });
                        }
                    };

                    if (this.mapInstance.loaded()) {
                        initRoute();
                    } else {
                        this.mapInstance.on('load', initRoute);
                    }
                    
                    setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 300);
                }, 300);
            }
         }"
         @open-map-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Peta Kunjungan SE vs Master Outlet</h3>
                <div id="visit-map-se" wire:ignore class="rounded-xl border border-base-300 h-[600px] w-full"></div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal All Maps (High-Performance Clustering with MapLibre GL) --}}
    <div x-data="{ 
            open: false,
            points: [],
            mapInstance: null,
            initMap(data) {
                const rawPoints = data?.points || data || [];
                this.points = Array.isArray(rawPoints) ? rawPoints : Object.values(rawPoints);
                this.open = true;

                if (typeof maplibregl === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js';
                    script.onload = () => this.initMap(data);
                    document.head.appendChild(script);

                    const css = document.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css';
                    document.head.appendChild(css);
                    return;
                }

                setTimeout(() => {
                    this.renderMap();
                }, 250);
            },
            renderMap() {
                try {
                    if (this.mapInstance) {
                        this.mapInstance.remove();
                        this.mapInstance = null;
                    }

                    this.mapInstance = new maplibregl.Map({
                        container: 'all-maps-se',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: [112.16, -8.1],
                        zoom: 10
                    });

                    this.mapInstance.addControl(new maplibregl.NavigationControl(), 'top-right');

                    const features = [];
                    const bounds = new maplibregl.LngLatBounds();

                    this.points.forEach(point => {
                        let lat = parseFloat(point.lat);
                        let lon = parseFloat(point.lon);

                        if (Math.abs(lat) > 90) {
                            const temp = lat; lat = lon; lon = temp;
                        }

                        if (!isNaN(lat) && !isNaN(lon) && lat !== 0 && lon !== 0) {
                            bounds.extend([lon, lat]);
                            features.push({
                                type: 'Feature',
                                geometry: { type: 'Point', coordinates: [lon, lat] },
                                properties: {
                                    name: point.name || '-',
                                    spv: point.spv || '-',
                                    date: point.date || '-',
                                    lat: lat,
                                    lon: lon
                                }
                            });
                        }
                    });

                    if (features.length === 0) return;

                    const setupCluster = () => {
                        if (!this.mapInstance) return;

                        try {
                            if (!this.mapInstance.getSource('visits')) {
                                this.mapInstance.addSource('visits', {
                                    type: 'geojson',
                                    data: { type: 'FeatureCollection', features: features },
                                    cluster: true,
                                    clusterMaxZoom: 14,
                                    clusterRadius: 40
                                });
                            }

                            if (!this.mapInstance.getLayer('clusters')) {
                                this.mapInstance.addLayer({
                                    id: 'clusters',
                                    type: 'circle',
                                    source: 'visits',
                                    filter: ['has', 'point_count'],
                                    paint: {
                                        'circle-color': [
                                            'step',
                                            ['get', 'point_count'],
                                            '#0ea5e9',
                                            10,
                                            '#f59e0b',
                                            30,
                                            '#ef4444'
                                        ],
                                        'circle-radius': [
                                            'step',
                                            ['get', 'point_count'],
                                            18,
                                            10,
                                            23,
                                            30,
                                            28
                                        ],
                                        'circle-stroke-width': 2,
                                        'circle-stroke-color': '#ffffff'
                                    }
                                });
                            }

                            if (!this.mapInstance.getLayer('cluster-count')) {
                                this.mapInstance.addLayer({
                                    id: 'cluster-count',
                                    type: 'symbol',
                                    source: 'visits',
                                    filter: ['has', 'point_count'],
                                    layout: {
                                        'text-field': '{point_count_abbreviated}',
                                        'text-font': ['Open Sans Regular'],
                                        'text-size': 11
                                    },
                                    paint: {
                                        'text-color': '#ffffff'
                                    }
                                });
                            }

                            if (!this.mapInstance.getLayer('unclustered-point')) {
                                this.mapInstance.addLayer({
                                    id: 'unclustered-point',
                                    type: 'circle',
                                    source: 'visits',
                                    filter: ['!', ['has', 'point_count']],
                                    paint: {
                                        'circle-color': '#0284c7',
                                        'circle-radius': 7,
                                        'circle-stroke-width': 2,
                                        'circle-stroke-color': '#ffffff'
                                    }
                                });
                            }

                            if (!bounds.isEmpty()) {
                                this.mapInstance.fitBounds(bounds, { padding: 50, maxZoom: 16, animate: false });
                            }

                            this.mapInstance.on('click', 'clusters', (e) => {
                                const mapFeatures = this.mapInstance.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                                const clusterId = mapFeatures[0].properties.cluster_id;
                                this.mapInstance.getSource('visits').getClusterExpansionZoom(clusterId).then((zoom) => {
                                    this.mapInstance.easeTo({
                                        center: mapFeatures[0].geometry.coordinates,
                                        zoom: zoom
                                    });
                                });
                            });

                            this.mapInstance.on('click', 'unclustered-point', (e) => {
                                const coords = e.features[0].geometry.coordinates.slice();
                                const props = e.features[0].properties;
                                new maplibregl.Popup({ offset: 12 })
                                    .setLngLat(coords)
                                    .setHTML(`
                                        <div class='text-slate-800 text-xs p-1'>
                                            <div class='font-bold text-sm text-primary mb-1'>${props.name}</div>
                                            <div class='text-slate-600'><strong>SE:</strong> ${props.spv}</div>
                                            <div class='text-slate-500 text-[10px]'><strong>Tgl:</strong> ${props.date}</div>
                                            <div class='mt-1.5 pt-1.5 border-t border-slate-200'>
                                                <a href='https://www.google.com/maps/search/?api=1&query=${props.lat},${props.lon}' target='_blank' class='text-sky-600 hover:underline font-mono text-[10px] inline-flex items-center gap-1 font-semibold'>
                                                    📍 Buka di Google Maps &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    `)
                                    .addTo(this.mapInstance);
                            });

                            this.mapInstance.on('mouseenter', 'clusters', () => { this.mapInstance.getCanvas().style.cursor = 'pointer'; });
                            this.mapInstance.on('mouseleave', 'clusters', () => { this.mapInstance.getCanvas().style.cursor = ''; });
                            this.mapInstance.on('mouseenter', 'unclustered-point', () => { this.mapInstance.getCanvas().style.cursor = 'pointer'; });
                            this.mapInstance.on('mouseleave', 'unclustered-point', () => { this.mapInstance.getCanvas().style.cursor = ''; });

                        } catch(layerErr) {
                            console.error('Map layer error:', layerErr);
                        }

                        setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 150);
                        setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 400);
                    };

                    if (this.mapInstance.loaded()) {
                        setupCluster();
                    } else {
                        this.mapInstance.on('load', setupCluster);
                    }

                } catch (e) {
                    console.error('Error rendering map:', e);
                }
            }
         }"
         @open-all-maps-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-6xl">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-s-map class="w-5 h-5 text-sky-500" />
                        <h3 class="font-bold text-lg">Peta Sebaran Titik Kunjungan SE</h3>
                        <span class="badge badge-primary badge-sm font-mono font-bold" x-text="points.length + ' Titik'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn btn-xs btn-outline btn-info rounded-lg gap-1" @click="renderMap()" title="Klik jika peta tidak sejajar/blank">
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5" /> Refresh Peta
                        </button>
                    </div>
                </div>
                <div id="all-maps-se" wire:ignore class="rounded-xl border border-base-300 h-[620px] w-full bg-slate-100"></div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>
    </div>
</div>
