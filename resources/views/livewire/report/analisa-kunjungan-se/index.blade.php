<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('se_detail_show_kpi') !== null 
             ? localStorage.getItem('se_detail_show_kpi') === 'true' 
             : (window.innerWidth >= 1280),
         autoHidden: (localStorage.getItem('se_detail_show_kpi') === null && window.innerWidth < 1280),
         wasOneRow: (window.innerWidth >= 1280),
         
         isOneRow() {
             if (this.$refs.kpiGrid && this.showKpi) {
                 const cards = Array.from(this.$refs.kpiGrid.children);
                 if (cards.length > 1) {
                     return cards[0].offsetTop === cards[cards.length - 1].offsetTop;
                 }
             }
             return window.matchMedia('(min-width: 1280px)').matches;
         },
         
         toggleKpi() {
             this.showKpi = !this.showKpi;
             this.autoHidden = false;
             localStorage.setItem('se_detail_show_kpi', this.showKpi);
             this.$nextTick(() => {
                 this.wasOneRow = this.isOneRow();
             });
         },
         
         checkKpiWrap() {
             const nowOneRow = this.isOneRow();
             
             // Logika: Jika dari sebelumnya 1 baris berubah menjadi 2 baris (atau lebih) -> otomatis hide kpi card
             if (this.wasOneRow && !nowOneRow) {
                 if (this.showKpi) {
                     this.showKpi = false;
                     this.autoHidden = true;
                     localStorage.setItem('se_detail_show_kpi', 'false');
                 }
             } 
             // Jika kembali ke 1 baris dan sebelumnya disembunyikan otomatis oleh sistem -> tampilkan kembali
             else if (!this.wasOneRow && nowOneRow && this.autoHidden) {
                 this.showKpi = true;
                 this.autoHidden = false;
                 localStorage.setItem('se_detail_show_kpi', 'true');
             }
             
             this.wasOneRow = nowOneRow;
         },
         
         init() {
             this.wasOneRow = this.isOneRow();
             
             const onResize = () => {
                 this.checkKpiWrap();
             };
             
             window.addEventListener('resize', onResize);
             
             if (window.ResizeObserver && this.$refs.kpiGrid) {
                 const ro = new ResizeObserver(() => {
                     if (this.showKpi) {
                         onResize();
                     }
                 });
                 ro.observe(this.$refs.kpiGrid);
             }
         }
     }">
    <x-slot name="title">Analisa Kunjungan SE</x-slot>

    {{-- Standar Tab Menu dari resources/views/components/ui/tab-menu.blade.php --}}
    @php
        $tabParams = array_filter([
            'region' => $appliedRegion ?: $selectedRegion,
            'area' => $appliedArea ?: $selectedArea,
            'distributor' => $appliedDistributor ?: $selectedDistributor,
            'salesman' => $appliedSalesman ?: $selectedSalesman,
            'startDate' => $appliedStartDate ?: $startDate,
            'endDate' => $appliedEndDate ?: $endDate,
        ], fn($v) => !empty($v));
    @endphp
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.index', $tabParams)" :navigate="true">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.summary', $tabParams)" :navigate="true">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.reason', $tabParams)" :navigate="true">
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

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                @if($appliedSalesman && !empty($dailyData['grandTotal']->total_call))
                    <span class="badge badge-primary text-primary-content font-bold text-xs gap-1 py-2.5 px-3 shadow-2xs font-mono">
                        <x-heroicon-s-clipboard-document-check class="w-4 h-4" />
                        {{ number_format($dailyData['grandTotal']->total_call) }} Call
                    </span>
                @endif

                <button type="button" 
                    @click="toggleKpi()" 
                    class="btn btn-xs sm:btn-sm rounded-xl gap-1.5 transition-all font-semibold border shadow-2xs cursor-pointer select-none"
                    :class="showKpi ? 'btn-ghost text-base-content/70 hover:text-base-content border-base-300' : 'btn-primary text-white shadow-sm border-primary'"
                    :title="showKpi ? 'Sembunyikan KPI Card agar ruang tabel lebih lega' : 'Tampilkan kembali KPI Card'">
                    <template x-if="showKpi">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-m-eye-slash class="w-4 h-4 text-base-content/70" />
                            <span class="text-xs hidden sm:inline">Sembunyikan KPI</span>
                        </span>
                    </template>
                    <template x-if="!showKpi">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-m-chart-bar class="w-4 h-4 text-white" />
                            <span class="text-xs">Tampilkan KPI</span>
                        </span>
                    </template>
                </button>
            </div>
        </x-slot>
    </x-ui.tab-menu>

    {{-- 5 KPI Cards Section (Default disembunyikan agar ruang tabel lebih lega) --}}
    <div x-show="showKpi" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 -translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 -translate-y-2" 
         class="shrink-0"
         style="display: none;">
        @include('livewire.report.analisa-kunjungan-se.kpi-cards')
    </div>

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
            },
            openDayMap(tgl) {
                const el = document.getElementById('map-payload-' + tgl);
                if (!el) {
                    console.warn('Map payload not found for date:', tgl);
                    return;
                }
                try {
                    const data = JSON.parse(el.textContent);
                    this.$dispatch('open-all-maps-modal', data);
                } catch (e) {
                    console.error('Failed to parse map payload:', e);
                }
            }
        }">
        
        {{-- Header Card & Actions Toolbar (Standar Kapsul Hybrid Breadcrumb Analisa Kunjungan SE) --}}
        <x-ui.filter-toolbar>
            {{-- 1. Kapsul Hierarki Wilayah (Region & Area Native + Distributor & Salesman Micro-Searchable Popover) --}}
            <x-ui.filter-breadcrumb>
                {{-- Region Segment (jika multi-region/nasional) --}}
                @if(count($regions) > 1 || $accessLevel === 'nasional')
                    <x-ui.filter-select 
                        wire:model.live="selectedRegion" 
                        :options="$regions" 
                        valueField="region_code" 
                        labelField="region_name" 
                        placeholder="Pilih Region" 
                        icon="globe" />
                @endif

                {{-- Area Segment --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <x-ui.filter-select 
                        wire:model.live="selectedArea" 
                        :options="$areas" 
                        valueField="area_code" 
                        labelField="area_name" 
                        placeholder="Pilih Area" 
                        icon="map-pin" 
                        :disabled="empty($selectedRegion)" />
                @endif

                {{-- Distributor Segment (Micro-Searchable Popover) --}}
                <x-ui.filter-searchable 
                    wire:key="dist-capsule-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}"
                    wire:model.live="selectedDistributor" 
                    :options="$distributors" 
                    valueField="distributor_code" 
                    labelField="distributor_name" 
                    placeholder="Pilih Distributor" 
                    searchPlaceholder="Cari nama distributor / kode..."
                    icon="building" 
                    :disabled="empty($selectedArea)" />

                {{-- Salesman Segment (Micro-Searchable Popover) --}}
                <x-ui.filter-searchable 
                    wire:key="sales-capsule-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}-{{ $selectedSalesman }}"
                    wire:model.live="selectedSalesman" 
                    :options="$salesmen" 
                    valueField="salesman_code" 
                    labelField="salesman_name" 
                    placeholder="Pilih SE" 
                    searchPlaceholder="Cari nama salesman (SE)..."
                    icon="user" 
                    :disabled="empty($selectedDistributor) || count($salesmen) === 0" />
            </x-ui.filter-breadcrumb>

            {{-- 2. Kapsul Periode Pintar (Smart Date Range Filter) --}}
            <x-ui.filter-date-range 
                start="startDate" 
                end="endDate" 
                :maxDays="31" />

            <x-slot name="actions">
                @php
                    $canApply = !empty($selectedRegion) && !empty($selectedArea) && !empty($selectedDistributor) && !empty($selectedSalesman);
                    $isMapDisabled = empty($appliedRegion) || empty($appliedArea) || empty($appliedDistributor) || empty($appliedSalesman);
                    $isExportDisabled = empty($appliedRegion) || empty($appliedArea) || empty($appliedDistributor) || empty($appliedSalesman);
                    $hasPendingFilters = $selectedRegion !== $appliedRegion || 
                                         $selectedArea !== $appliedArea || 
                                         $selectedDistributor !== $appliedDistributor || 
                                         $selectedSalesman !== $appliedSalesman || 
                                         $startDate !== $appliedStartDate || 
                                         $endDate !== $appliedEndDate;
                @endphp
                <x-ui.filter-actions 
                    applyAction="applyFilter" 
                    resetAction="resetFilter" 
                    :canApply="$canApply" 
                    disabledTip="Pilih Region, Area, Distributor, dan SE terlebih dahulu">
                    
                    {{-- Tombol Buka Peta Sebaran Kunjungan SE --}}
                    <button type="button" 
                        class="btn btn-sm btn-outline btn-info rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer"
                        wire:click="openAllMapsModal"
                        wire:loading.attr="disabled"
                        wire:target="openAllMapsModal"
                        @disabled($isMapDisabled)
                        title="{{ $isMapDisabled ? 'Terapkan filter terlebih dahulu' : 'Buka Peta Sebaran Kunjungan SE' }}">
                        <span wire:loading.remove wire:target="openAllMapsModal" class="flex items-center gap-1">
                            <x-heroicon-m-map class="w-4 h-4" />
                            <span>Peta</span>
                        </span>
                        <span wire:loading wire:target="openAllMapsModal" class="flex items-center gap-1">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span>Memuat...</span>
                        </span>
                    </button>

                    {{-- Tombol Ekspor Excel --}}
                    @if($canExport)
                        <button type="button" 
                            wire:click="export" 
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer {{ $isExportDisabled || $hasPendingFilters ? 'opacity-50 cursor-not-allowed' : '' }}"
                            @disabled($isExportDisabled || $hasPendingFilters)
                            title="{{ $isExportDisabled || $hasPendingFilters ? 'Terapkan filter terlebih dahulu' : 'Download Detail Kunjungan ke Excel (.xlsx)' }}">
                            <span wire:loading.remove wire:target="export" class="flex items-center gap-1.5">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span>Excel</span>
                            </span>
                            <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </x-ui.filter-actions>
            </x-slot>
        </x-ui.filter-toolbar>

        {{-- Body Card (Tabel Master-Detail: Summary Harian & Expand Detail Toko) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            {{-- Map JSON Data Store per Day (Safely decoupled from HTML attributes) --}}
            <div id="map-payload-store" class="hidden">
                @foreach(($dailyData['summaries'] ?? []) as $day)
                    <script type="application/json" id="map-payload-{{ $day->tanggal }}">{!! $day->map_payload_json ?? '{}' !!}</script>
                @endforeach
            </div>
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
                                    <span class="inline-flex items-center px-1 py-0.2 rounded text-[0.625rem] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 leading-tight">Sabtu</span>
                                @endif
                                @if(count($day->map_points) > 0)
                                    <button type="button" 
                                        @click.stop="openDayMap('{{ $day->tanggal }}')"
                                        class="inline-flex items-center justify-center w-5 h-5 rounded hover:bg-info/20 text-info transition-colors"
                                        title="Buka Peta Rute Hari Ini ({{ $day->formatted_date }})">
                                        <x-heroicon-s-map class="w-3.5 h-3.5" />
                                    </button>
                                @elseif($day->total_visit > 0)
                                    <span class="inline-flex items-center justify-center w-5 h-5 text-base-content/25 cursor-help"
                                        title="Kunjungan tercatat, namun tidak ada titik koordinat GPS valid pada rute PJP (V_LA/V_LG kosong, 0, atau kunjungan Extra Call)">
                                        <x-heroicon-s-map class="w-3.5 h-3.5" />
                                    </span>
                                @endif
                                @if(!empty($day->duplicate_warnings) && count($day->duplicate_warnings) > 0)
                                    <button type="button" 
                                        @click.stop="openDayMap('{{ $day->tanggal }}')"
                                        class="inline-flex items-center justify-center text-amber-500 hover:text-amber-600 hover:scale-110 transition-transform cursor-pointer" 
                                        title="PERINGATAN AUDIT: Terdeteksi {{ count($day->duplicate_warnings) }} pasangan titik kunjungan di koordinat yang sama persis untuk toko berbeda. Klik untuk lihat di peta">
                                        <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                                    </button>
                                @endif
                                @if(!empty($day->distributor_warnings) && count($day->distributor_warnings) > 0)
                                    <button type="button" 
                                        @click.stop="openDayMap('{{ $day->tanggal }}')"
                                        class="inline-flex items-center justify-center text-rose-500 hover:text-rose-600 hover:scale-110 transition-transform cursor-pointer" 
                                        title="PERINGATAN AUDIT: Terdeteksi {{ count($day->distributor_warnings) }} kunjungan di titik distributor (&le;100m). Klik untuk lihat di peta">
                                        <x-heroicon-s-building-office-2 class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </td>

                        {{-- Jam Kerja --}}
                        <td class="text-center whitespace-nowrap font-mono text-xs">
                            @if($day->first_time_in !== '-' || $day->last_time_out !== '-')
                                <span class="font-semibold text-base-content">{{ $day->first_time_in }} - {{ $day->last_time_out }}</span>
                                <span class="block text-[0.625rem] text-base-content/50 font-normal">({{ $day->work_duration_str }})</span>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>

                        {{-- Plan Call --}}
                        <td class="text-center whitespace-nowrap">
                            <span class="font-mono font-bold text-xs">{{ $day->total_call }} Toko</span>
                            <span class="text-[0.625rem] text-base-content/50 block font-mono">{{ $day->pc }} PC • {{ $day->xc }} XC</span>
                        </td>

                        {{-- Actual Call (Merah jika < 100%, Hijau jika 100%) --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="font-mono font-semibold text-xs">{{ $day->total_visit }} / {{ $day->total_call }}</span>
                                @php
                                    $vClass = $day->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';
                                @endphp
                                <span class="badge badge-xs {{ $vClass }} font-bold font-mono px-1.5 text-[0.625rem] leading-tight">
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
                                <span class="badge badge-xs {{ $ecClass }} font-bold font-mono px-1.5 text-[0.625rem] leading-tight">
                                    {{ $day->ec_percent }}%
                                </span>
                            </div>
                        </td>

                        {{-- Total Order (Rp) - Top Sales Highlight --}}
                        <td class="text-right whitespace-nowrap font-mono text-xs">
                            @if($day->total_val_order >= 10000000)
                                <div class="flex items-center justify-end gap-1">
                                    <span class="badge badge-xs badge-success text-white text-[0.625rem] font-bold px-1 uppercase tracking-wider">Top</span>
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
                            <div class="inline-flex flex-col items-center gap-1 font-mono text-xs w-full max-w-[145px]">
                                <div class="inline-flex items-center justify-center gap-1 w-full text-[0.625rem]">
                                    <span class="badge badge-xs badge-ghost border-base-300 gap-0.5 text-[0.625rem] px-1 py-0.5" title="Waktu di Toko: {{ $day->consume_percent }}%">
                                        🏬 {{ $day->consume_str }}
                                    </span>
                                    <span class="badge badge-xs badge-ghost border-base-300 gap-0.5 text-[0.625rem] px-1 py-0.5" title="Waktu di Jalan: {{ $day->travel_percent }}%">
                                        🛵 {{ $day->travel_str }}
                                    </span>
                                </div>
                                <div class="w-full bg-amber-500/20 rounded-full h-1 flex overflow-hidden shadow-2xs" title="Proporsi Waktu: {{ $day->consume_percent }}% di Toko, {{ $day->travel_percent }}% di Jalan">
                                    <div class="bg-success h-full transition-all duration-300" style="width: {{ $day->consume_percent }}%"></div>
                                    <div class="bg-amber-500 h-full transition-all duration-300" style="width: {{ $day->travel_percent }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Kepatuhan Radius (Hanya Dihitung untuk Rute PJP) --}}
                        <td class="text-center whitespace-nowrap">
                            @if($day->out_of_radius_count == 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.625rem] font-semibold bg-success/10 text-success border border-success/30">
                                    ✓ Sesuai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.625rem] font-bold bg-error text-white shadow-2xs" title="{{ $day->out_of_radius_count }} toko rute PJP check-in melebihi 50 meter dari master">
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
                                        <div class="flex items-center gap-1.5 flex-wrap text-[0.625rem]">
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
                                                <span class="badge badge-sm badge-error text-white font-bold" title="{{ $day->out_of_radius_count }} toko rute PJP di luar radius 50m">
                                                    ⚠️ {{ $day->out_of_radius_count }} Toko PJP >50m
                                                </span>
                                            @endif
                                            @if(!empty($day->distributor_warnings) && count($day->distributor_warnings) > 0)
                                                <span class="badge badge-sm bg-rose-600 text-white font-bold animate-pulse" title="{{ count($day->distributor_warnings) }} kunjungan dilakukan di lokasi kantor/gudang distributor (&le;100m)">
                                                    🚨 {{ count($day->distributor_warnings) }} Titik Distributor
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        @if(count($day->map_points) > 0)
                                            <button type="button" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-sky-500 text-white hover:bg-sky-600 transition-colors shadow-2xs cursor-pointer"
                                                @click="openDayMap('{{ $day->tanggal }}')">
                                                <x-heroicon-s-map class="w-3.5 h-3.5" />
                                                Peta Rute Hari Ini
                                            </button>
                                        @elseif($day->total_visit > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700 cursor-help"
                                                title="Kunjungan tercatat, namun tidak ada koordinat GPS valid pada rute PJP (V_LA/V_LG kosong, 0, atau kunjungan Extra Call)">
                                                <x-heroicon-s-map class="w-3.5 h-3.5 text-slate-500" />
                                                Peta (GPS Kosong / XC)
                                            </span>
                                        @endif
                                        <button type="button" @click="toggle('{{ $day->tanggal }}')" class="px-2 py-1 rounded-lg text-xs text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                                            ✕ Tutup
                                        </button>
                                    </div>
                                </div>

                                {{-- Sub-Table Body (Ultra-Compact 1-Line Row Height, High Contrast Sticky Header) --}}
                                <div class="overflow-x-auto max-h-[420px]">
                                    <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs leading-tight [&_th]:py-1.5 [&_th]:px-2 [&_td]:py-1 [&_td]:px-2">
                                        <thead class="sticky top-0 z-20 shadow-sm border-b border-slate-700 uppercase font-bold tracking-wider text-[0.625rem] text-white [&_th]:bg-slate-800 dark:[&_th]:bg-slate-900 [&_th]:text-slate-100 [&_th]:border-b [&_th]:border-slate-700">
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
                                                    @if($item->flag_visit !== 'Y')
                                                        <span class="text-base-content/30 text-[0.625rem]" title="Toko Tidak Dikunjungi">-</span>
                                                    @elseif($item->flag_pjp !== 'R')
                                                        <button type="button" 
                                                            @click="$dispatch('open-map-modal', {masterLat: '{{ $item->master_lat }}', masterLon: '{{ $item->master_lon }}', visitLat: '{{ $item->visit_lat }}', visitLon: '{{ $item->visit_lon }}'})" 
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[0.625rem] font-mono cursor-pointer transition-colors bg-base-200 text-base-content/60 hover:bg-base-300"
                                                            title="Extra Call (Bukan Rute PJP - Jarak: {{ $distStr }}). Klik untuk buka peta">
                                                            <span>📍 {{ $distStr }} <span class="text-[0.625rem] text-base-content/40">(XC)</span></span>
                                                        </button>
                                                    @else
                                                        <button type="button" 
                                                            @click="$dispatch('open-map-modal', {masterLat: '{{ $item->master_lat }}', masterLon: '{{ $item->master_lon }}', visitLat: '{{ $item->visit_lat }}', visitLon: '{{ $item->visit_lon }}'})" 
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[0.625rem] font-mono cursor-pointer transition-colors {{ $item->is_out_of_radius ? 'bg-error text-white font-bold hover:bg-error/85 shadow-2xs' : 'bg-success/15 text-success border border-success/30 hover:bg-success/25 font-semibold' }}"
                                                            title="{{ $item->is_out_of_radius ? 'Luar radius 50m (' . number_format($distM, 0, ',', '.') . 'm). Klik untuk buka peta' : 'Sesuai radius (' . $distM . 'm). Klik untuk buka peta' }}">
                                                            <span>📍 {{ $distStr }}</span>
                                                        </button>
                                                    @endif
                                                    @if(!empty($item->has_duplicate_coord))
                                                        <div class="mt-0.5">
                                                            <span class="inline-flex items-center gap-0.5 px-1 py-0.2 rounded text-[0.625rem] font-bold bg-amber-500 text-white shadow-2xs" 
                                                                  title="PERINGATAN AUDIT: Koordinat visit sama persis dengan toko: {{ $item->duplicate_with_info }}">
                                                                ⚠️ Titik Sama
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if(!empty($item->is_at_distributor))
                                                        <div class="mt-0.5">
                                                            <span class="inline-flex items-center gap-0.5 px-1 py-0.2 rounded text-[0.625rem] font-bold bg-rose-600 text-white shadow-2xs" 
                                                                  title="PERINGATAN AUDIT: Visit dilakukan di lokasi Distributor (Jarak {{ $item->dist_to_distributor }}m &le; 100m)">
                                                                🚨 Titik Distributor ({{ $item->dist_to_distributor }}m)
                                                            </span>
                                                        </div>
                                                    @endif
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
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.625rem] font-medium bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30 hover:bg-amber-500/25 max-w-[140px] truncate cursor-pointer transition-colors"
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
                            <th class="text-center font-mono text-[0.625rem] text-base-content/80 font-normal whitespace-nowrap">
                                Avg {{ $dailyData['grandTotal']->avg_work_str }}/hari
                            </th>
                            <th class="text-center font-mono whitespace-nowrap">
                                {{ $dailyData['grandTotal']->total_call }} Toko
                                <span class="block text-[0.625rem] font-normal text-base-content/60 font-mono">{{ $dailyData['grandTotal']->pc }} PC • {{ $dailyData['grandTotal']->xc }} XC</span>
                            </th>
                            <th class="text-center font-mono whitespace-nowrap">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="font-bold">{{ $dailyData['grandTotal']->total_visit }} Toko</span>
                                    @php
                                        $gtVisitClass = $dailyData['grandTotal']->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';
                                    @endphp
                                    <span class="badge badge-xs {{ $gtVisitClass }} font-bold font-mono px-1.5 text-[0.625rem] leading-tight">
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
                                    <span class="badge badge-xs {{ $gtEcClass }} font-bold font-mono px-1.5 text-[0.625rem] leading-tight">
                                        {{ $gtEcPercent }}%
                                    </span>
                                </div>
                            </th>
                            <th class="text-right font-mono text-success font-bold whitespace-nowrap">
                                Rp {{ number_format($dailyData['grandTotal']->total_val_order, 0, ',', '.') }}
                            </th>
                            <th class="text-center font-mono text-[0.625rem]">
                                <div class="inline-flex flex-col items-center gap-1 w-full max-w-[145px]">
                                    <div class="inline-flex items-center justify-center gap-1.5 w-full text-[0.625rem]">
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
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.625rem] font-bold bg-error text-white shadow-2xs" title="Total toko rute PJP yang check-in melebihi radius 50 meter dari master">
                                        ⚠️ {{ $dailyData['grandTotal']->out_of_radius_count }} Toko PJP >50m
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.625rem] font-semibold bg-success/15 text-success border border-success/30">
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

                    const parseCoord = (val) => {
                        if (val === null || val === undefined) return NaN;
                        const s = String(val).trim().replace(',', '.');
                        return parseFloat(s);
                    };

                    let mLat = parseCoord(data.masterLat);
                    let mLon = parseCoord(data.masterLon);
                    let vLat = parseCoord(data.visitLat);
                    let vLon = parseCoord(data.visitLon);

                    // Deteksi jika koordinat master terbalik
                    if (Math.abs(mLat) > 90 && Math.abs(mLon) <= 90) {
                        const temp = mLat; mLat = mLon; mLon = temp;
                    }
                    // Deteksi jika koordinat visit terbalik
                    if (Math.abs(vLat) > 90 && Math.abs(vLon) <= 90) {
                        const temp = vLat; vLat = vLon; vLon = temp;
                    }

                    const bounds = new maplibregl.LngLatBounds();
                    let hasMaster = !isNaN(mLat) && !isNaN(mLon) && Math.abs(mLat) > 0.000001 && Math.abs(mLon) > 0.000001 && mLat >= -90 && mLat <= 90 && mLon >= -180 && mLon <= 180;
                    let hasVisit = !isNaN(vLat) && !isNaN(vLon) && Math.abs(vLat) > 0.000001 && Math.abs(vLon) > 0.000001 && vLat >= -90 && vLat <= 90 && vLon >= -180 && vLon <= 180;

                    if (hasMaster) {
                        new maplibregl.Marker({ color: '#ef4444' })
                            .setLngLat([mLon, mLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Master Point</strong><br><span class='text-[0.625rem] text-gray-500 font-mono'>📍 ${mLat}, ${mLon}</span></div>`))
                            .addTo(this.mapInstance);
                        bounds.extend([mLon, mLat]);
                    }

                    if (hasVisit) {
                        new maplibregl.Marker({ color: '#3b82f6' })
                            .setLngLat([vLon, vLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Visit Point SE</strong><br><span class='text-[0.625rem] text-gray-500 font-mono'>📍 ${vLat}, ${vLon}</span></div>`))
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

    {{-- Modal All Maps / Route Maps (High-Performance with MapLibre GL) --}}
    <div x-data="{ 
            open: false,
            points: [],
            isRoute: false,
            modalTitle: 'Peta Kunjungan SE',
            duplicateWarnings: [],
            distributorWarnings: [],
            distributorPoint: null,
            mapInstance: null,
            htmlMarkers: [],
            distributorMarker: null,
            hasNoValidPoints: false,
            consumeStr: '-',
            travelStr: '-',
            consumePercent: null,
            travelPercent: null,
            workDurationStr: '-',
            timeRange: null,
            activeSidebarTab: 'audit',
            selectedSeq: null,
            stopSearch: '',
            isSatellite: false,
            initMap(data) {
                const rawPoints = Array.isArray(data) ? data : (data?.points || []);
                this.points = Array.isArray(rawPoints) ? rawPoints : Object.values(rawPoints);
                this.isRoute = !!data?.isRoute;
                this.modalTitle = data?.title || (this.isRoute ? 'Peta Rute Kunjungan SE' : 'Peta Sebaran Titik Kunjungan SE');
                this.duplicateWarnings = data?.duplicateWarnings || [];
                this.distributorWarnings = data?.distributorWarnings || [];
                this.distributorPoint = data?.distributorPoint || null;
                this.consumeStr = data?.consumeStr || '-';
                this.travelStr = data?.travelStr || '-';
                this.consumePercent = (typeof data?.consumePercent === 'number') ? data.consumePercent : null;
                this.travelPercent = (typeof data?.travelPercent === 'number') ? data.travelPercent : null;
                this.workDurationStr = data?.workDurationStr || '-';
                this.timeRange = data?.timeRange || null;
                this.hasNoValidPoints = false;
                this.selectedSeq = null;
                this.stopSearch = '';
                this.isSatellite = false;
                this.activeSidebarTab = (this.distributorWarnings.length > 0 || this.duplicateWarnings.length > 0) ? 'audit' : 'stops';
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
            selectStop(point) {
                if (!this.mapInstance || !point) return;
                this.selectedSeq = point.seq;

                this.htmlMarkers.forEach(m => {
                    const p = m.getPopup();
                    if (p && p.isOpen()) p.remove();
                });

                this.mapInstance.flyTo({
                    center: [point.lon, point.lat],
                    zoom: Math.max(this.mapInstance.getZoom(), 16),
                    essential: true
                });

                if (point._marker) {
                    point._marker.togglePopup();
                }

                this.scrollToSidebarItem(point.seq);
            },
            selectStopBySeq(seq) {
                const target = this.points.find(p => String(p.seq) === String(seq));
                if (target) {
                    this.selectStop(target);
                }
            },
            selectDistributor() {
                if (!this.mapInstance || !this.distributorPoint) return;
                this.selectedSeq = 'distributor';
                this.mapInstance.flyTo({
                    center: [this.distributorPoint.lon, this.distributorPoint.lat],
                    zoom: 18,
                    essential: true
                });
                if (this.distributorMarker) {
                    this.distributorMarker.togglePopup();
                }
            },
            scrollToSidebarItem(seq) {
                this.$nextTick(() => {
                    const itemEl = document.getElementById('sidebar-stop-' + seq);
                    if (itemEl) {
                        itemEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });
            },
            toggleMapLayer() {
                if (!this.mapInstance) return;
                this.isSatellite = !this.isSatellite;
                
                if (this.isSatellite) {
                    if (!this.mapInstance.getSource('esri-satellite')) {
                        this.mapInstance.addSource('esri-satellite', {
                            type: 'raster',
                            tiles: ['https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'],
                            tileSize: 256,
                            maxzoom: 19
                        });
                    }
                    if (!this.mapInstance.getLayer('esri-satellite-layer')) {
                        const beforeLayer = this.mapInstance.getLayer('route-line-casing') 
                            ? 'route-line-casing' 
                            : (this.mapInstance.getLayer('clusters') 
                                ? 'clusters' 
                                : (this.mapInstance.getLayer('unclustered-point') 
                                    ? 'unclustered-point' 
                                    : undefined));
                        this.mapInstance.addLayer({
                            id: 'esri-satellite-layer',
                            type: 'raster',
                            source: 'esri-satellite'
                        }, beforeLayer);
                    }
                } else {
                    if (this.mapInstance.getLayer('esri-satellite-layer')) {
                        this.mapInstance.removeLayer('esri-satellite-layer');
                    }
                }
            },
            getFilteredPoints() {
                if (!this.stopSearch || this.stopSearch.trim() === '') {
                    return this.points;
                }
                const q = this.stopSearch.toLowerCase().trim();
                return this.points.filter(p => {
                    const name = (p.name || '').toLowerCase();
                    const seq = String(p.seq || '');
                    const custno = (p.custno || '').toLowerCase();
                    return name.includes(q) || seq === q || custno.includes(q);
                });
            },
            createGeoJSONCircle(center, radiusInMeters, points = 48) {
                const [lon, lat] = center;
                if (isNaN(lon) || isNaN(lat)) return null;
                const km = radiusInMeters / 1000;
                const ret = [];
                const cosLat = Math.cos((lat * Math.PI) / 180);
                if (Math.abs(cosLat) < 0.000001) return null;
                const distanceX = km / (111.320 * cosLat);
                const distanceY = km / 110.574;

                for (let i = 0; i < points; i++) {
                    const theta = (i / points) * (2 * Math.PI);
                    const x = distanceX * Math.cos(theta);
                    const y = distanceY * Math.sin(theta);
                    ret.push([lon + x, lat + y]);
                }
                ret.push(ret[0]);
                return {
                    type: 'Feature',
                    geometry: {
                        type: 'Polygon',
                        coordinates: [ret]
                    },
                    properties: {}
                };
            },
            renderDistributorOverlay() {
                if (!this.distributorPoint || !this.distributorPoint.lat || !this.distributorPoint.lon) return;

                const parseCoord = (val) => {
                    if (val === null || val === undefined) return NaN;
                    const s = String(val).trim().replace(',', '.');
                    return parseFloat(s);
                };

                let dLat = parseCoord(this.distributorPoint.lat);
                let dLon = parseCoord(this.distributorPoint.lon);
                if (Math.abs(dLat) > 90 && Math.abs(dLon) <= 90) { const tmp = dLat; dLat = dLon; dLon = tmp; }
                if (isNaN(dLat) || isNaN(dLon) || Math.abs(dLat) < 0.000001 || Math.abs(dLon) < 0.000001 || dLat < -90 || dLat > 90 || dLon < -180 || dLon > 180) return;

                // 1. Draw 100-meter buffer circle around distributor
                const circleGeoJson = this.createGeoJSONCircle([dLon, dLat], 100);
                if (circleGeoJson) {
                    this.mapInstance.addSource('distributor-buffer-100m', {
                        type: 'geojson',
                        data: circleGeoJson
                    });

                    this.mapInstance.addLayer({
                        id: 'distributor-buffer-fill',
                        type: 'fill',
                        source: 'distributor-buffer-100m',
                        paint: {
                            'fill-color': '#f43f5e',
                            'fill-opacity': 0.25
                        }
                    });

                    this.mapInstance.addLayer({
                        id: 'distributor-buffer-stroke',
                        type: 'line',
                        source: 'distributor-buffer-100m',
                        paint: {
                            'line-color': '#e11d48',
                            'line-width': 2,
                            'line-opacity': 0.85,
                            'line-dasharray': [2, 2]
                        }
                    });
                }

                // 2. Add Office/Distributor Marker
                const el = document.createElement('div');
                el.className = 'distributor-marker cursor-pointer transition-transform duration-200 hover:scale-125 z-30';
                el.innerHTML = `
                    <div class='flex items-center justify-center w-8 h-8 rounded-full bg-slate-900 text-white border-2 border-rose-500 shadow-xl ring-4 ring-rose-500/30 text-sm select-none'>
                        🏢
                    </div>
                `;

                const popupHtml = `
                    <div class='p-1.5 text-xs text-slate-900 min-w-[210px] max-w-[280px]'>
                        <div class='flex items-center gap-1.5 font-bold text-sm text-slate-950 border-b border-slate-200 pb-1 mb-1'>
                            <span>🏢 Kantor Distributor</span>
                        </div>
                        <div class='font-bold text-slate-900'>${this.distributorPoint.name || this.distributorPoint.code}</div>
                        <div class='text-slate-600 font-mono text-[0.625rem] font-semibold mt-0.5'>Kode: ${this.distributorPoint.code}</div>
                        <div class='mt-2 p-1.5 rounded bg-rose-50 border border-rose-200 text-rose-900 text-xs leading-snug'>
                            📍 <strong>Radius Audit: 100 Meter</strong><br>
                            Area lingkaran merah putus-putus menunjukkan batas radius audit 100m.
                        </div>
                        <div class='mt-1.5'>
                            <a href='https://www.google.com/maps/search/?api=1&query=${dLat},${dLon}' target='_blank' class='text-sky-600 hover:underline font-mono text-[0.625rem] inline-flex items-center gap-1 font-semibold'>
                                📍 Buka di Google Maps &rarr;
                            </a>
                        </div>
                    </div>
                `;

                const popup = new maplibregl.Popup({ offset: 16 }).setHTML(popupHtml);
                const marker = new maplibregl.Marker({ element: el })
                    .setLngLat([dLon, dLat])
                    .setPopup(popup)
                    .addTo(this.mapInstance);

                this.distributorMarker = marker;
                this.htmlMarkers.push(marker);
            },
            clearExisting() {
                if (this.htmlMarkers && this.htmlMarkers.length > 0) {
                    this.htmlMarkers.forEach(m => m.remove());
                    this.htmlMarkers = [];
                }
                this.distributorMarker = null;
                this.isSatellite = false;
                this.selectedSeq = null;
                if (this.mapInstance) {
                    this.mapInstance.remove();
                    this.mapInstance = null;
                }
            },
            closeModal() {
                this.open = false;
                this.clearExisting();
            },
            renderMap() {
                try {
                    this.clearExisting();

                    const parseCoord = (val) => {
                        if (val === null || val === undefined) return NaN;
                        const s = String(val).trim().replace(',', '.');
                        return parseFloat(s);
                    };

                    let validPoints = [];
                    const bounds = new maplibregl.LngLatBounds();

                    this.points.forEach(point => {
                        let lat = parseCoord(point.lat);
                        let lon = parseCoord(point.lon);

                        if (Math.abs(lat) > 90 && Math.abs(lon) <= 90) {
                            const temp = lat; lat = lon; lon = temp;
                        }

                        if (!isNaN(lat) && !isNaN(lon) && Math.abs(lat) > 0.000001 && Math.abs(lon) > 0.000001 && lat >= -90 && lat <= 90 && lon >= -180 && lon <= 180) {
                            bounds.extend([lon, lat]);
                            validPoints.push({
                                ...point,
                                lat: lat,
                                lon: lon
                            });
                        }
                    });

                    let hasValidDistributor = false;
                    let dLat = NaN, dLon = NaN;
                    if (this.distributorPoint && this.distributorPoint.lat && this.distributorPoint.lon) {
                        dLat = parseCoord(this.distributorPoint.lat);
                        dLon = parseCoord(this.distributorPoint.lon);
                        if (Math.abs(dLat) > 90 && Math.abs(dLon) <= 90) { const tmp = dLat; dLat = dLon; dLon = tmp; }
                        if (!isNaN(dLat) && !isNaN(dLon) && Math.abs(dLat) > 0.000001 && Math.abs(dLon) > 0.000001 && dLat >= -90 && dLat <= 90 && dLon >= -180 && dLon <= 180) {
                            bounds.extend([dLon, dLat]);
                            hasValidDistributor = true;
                        }
                    }

                    this.hasNoValidPoints = (validPoints.length === 0 && !hasValidDistributor);

                    // Penentuan center awal peta
                    let defaultCenter = [112.16, -8.1];
                    let defaultZoom = 10;
                    if (hasValidDistributor) {
                        defaultCenter = [dLon, dLat];
                        defaultZoom = 15;
                    } else if (validPoints.length > 0) {
                        defaultCenter = [validPoints[0].lon, validPoints[0].lat];
                    }

                    this.mapInstance = new maplibregl.Map({
                        container: 'all-maps-se',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: defaultCenter,
                        zoom: defaultZoom
                    });

                    this.mapInstance.addControl(new maplibregl.NavigationControl(), 'top-right');

                    if (validPoints.length === 0 && !hasValidDistributor) {
                        return;
                    }

                    if (this.isRoute) {
                        validPoints.sort((a, b) => (parseInt(a.seq) || 0) - (parseInt(b.seq) || 0));
                    }

                    // Deteksi duplikasi koordinat di frontend untuk toko berbeda (SAMA PERSIS / Jarak = 0)
                    const coordGroups = {};
                    validPoints.forEach((p, idx) => {
                        const key = `${p.lat},${p.lon}`;
                        if (!coordGroups[key]) coordGroups[key] = [];
                        coordGroups[key].push({ point: p, idx });
                    });

                    const clientDupWarnings = [];
                    Object.values(coordGroups).forEach(group => {
                        const uniqueStores = new Set(group.map(g => g.point.custno || g.point.name));
                        if (uniqueStores.size > 1) {
                            group.forEach((item, itemIdx) => {
                                item.point.has_duplicate_coord = true;
                                item.point.dup_group_size = group.length;
                                item.point.dup_idx = itemIdx;
                                item.point.duplicate_with = group.filter(g => g.idx !== item.idx).map(o => ({
                                    seq: o.point.seq || (o.idx + 1),
                                    name: o.point.name,
                                    time_in: o.point.time_in
                                }));
                            });

                            for (let i = 0; i < group.length; i++) {
                                for (let j = i + 1; j < group.length; j++) {
                                    const p1 = group[i].point;
                                    const p2 = group[j].point;
                                    if ((p1.custno || p1.name) !== (p2.custno || p2.name)) {
                                        clientDupWarnings.push({
                                            seq1: p1.seq || (group[i].idx + 1),
                                            name1: p1.name,
                                            time1: p1.time_in,
                                            seq2: p2.seq || (group[j].idx + 1),
                                            name2: p2.name,
                                            time2: p2.time_in
                                        });
                                    }
                                }
                            }
                        }
                    });

                    if (!this.duplicateWarnings || this.duplicateWarnings.length === 0) {
                        this.duplicateWarnings = clientDupWarnings;
                    }

                    const setupContent = () => {
                        if (!this.mapInstance) return;

                        if (validPoints.length > 0) {
                            if (this.isRoute) {
                                this.renderRouteView(validPoints);
                            } else {
                                this.renderClusterView(validPoints);
                            }
                        }

                        this.renderDistributorOverlay();

                        if (!bounds.isEmpty()) {
                            this.mapInstance.fitBounds(bounds, { padding: 60, maxZoom: 16, animate: false });
                        }

                        setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 150);
                        setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 400);
                    };

                    if (this.mapInstance.loaded()) {
                        setupContent();
                    } else {
                        this.mapInstance.on('load', setupContent);
                    }

                } catch (e) {
                    console.error('Error rendering map:', e);
                }
            },
            renderRouteView(validPoints) {
                // 1. Draw connecting polyline line
                const coordinates = validPoints.map(p => [p.lon, p.lat]);

                if (coordinates.length > 1) {
                    this.mapInstance.addSource('route-line', {
                        type: 'geojson',
                        data: {
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'LineString',
                                coordinates: coordinates
                            }
                        }
                    });

                    // Outline / casing line (dark blue border for high contrast)
                    this.mapInstance.addLayer({
                        id: 'route-line-casing',
                        type: 'line',
                        source: 'route-line',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#0369a1',
                            'line-width': 6,
                            'line-opacity': 0.8
                        }
                    });

                    // Main route line (vibrant blue)
                    this.mapInstance.addLayer({
                        id: 'route-line-inner',
                        type: 'line',
                        source: 'route-line',
                        layout: {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        paint: {
                            'line-color': '#38bdf8',
                            'line-width': 3.5,
                            'line-opacity': 1
                        }
                    });
                }

                // 2. Add numbered sequence HTML markers
                const totalPoints = validPoints.length;
                validPoints.forEach((point, idx) => {
                    const isStart = idx === 0;
                    const isEnd = idx === totalPoints - 1 && totalPoints > 1;

                    // Slight visual displacement if multiple points have exact same coordinates so both are visible
                    let renderLat = point.lat;
                    let renderLon = point.lon;
                    if (point.has_duplicate_coord && point.dup_group_size > 1) {
                        const angle = (point.dup_idx * (2 * Math.PI / point.dup_group_size));
                        const offsetMeters = 8;
                        renderLat += (offsetMeters * Math.cos(angle)) / 111111;
                        renderLon += (offsetMeters * Math.sin(angle)) / (111111 * Math.cos(point.lat * Math.PI / 180));
                    }

                    const el = document.createElement('div');
                    el.className = 'route-marker-node cursor-pointer transition-transform duration-200 hover:scale-125';

                    let pinColor = 'bg-sky-600 text-white border-white ring-2 ring-sky-400 shadow-md';
                    let badgeLabel = '';
                    let warningTag = '';

                    if (point.has_duplicate_coord) {
                        pinColor = 'bg-amber-500 text-white border-white ring-4 ring-amber-400/90 shadow-xl';
                        warningTag = `<span class='absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-4 h-4 flex items-center justify-center text-[0.625rem] shadow-sm font-bold border border-white' title='Titik Sama dengan Toko Lain!'>!</span>`;
                    }

                    if (point.is_at_distributor) {
                        pinColor = 'bg-rose-600 text-white border-white ring-4 ring-rose-400 shadow-xl animate-pulse';
                        warningTag = `<span class='absolute -top-2 -right-2 bg-slate-900 text-rose-400 rounded-full w-4 h-4 flex items-center justify-center text-[0.625rem] shadow-sm font-bold border border-rose-400' title='Visit di Titik Distributor (${point.dist_to_distributor}m)!'>🚨</span>`;
                    }

                    if (isStart) {
                        if (!point.is_at_distributor && !point.has_duplicate_coord) {
                            pinColor = 'bg-emerald-600 text-white border-white ring-2 ring-emerald-400 shadow-lg';
                        }
                        badgeLabel = `<span class='text-[0.625rem] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 ml-1'>START</span>`;
                    } else if (isEnd) {
                        if (!point.is_at_distributor && !point.has_duplicate_coord) {
                            pinColor = 'bg-rose-600 text-white border-white ring-2 ring-rose-400 shadow-lg';
                        }
                        badgeLabel = `<span class='text-[0.625rem] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 ml-1'>FINISH</span>`;
                    }

                    el.innerHTML = `
                        <div class='flex items-center justify-center w-7 h-7 rounded-full font-bold text-xs border-2 select-none shadow-lg relative ${pinColor}'>
                            <span>${point.seq || (idx + 1)}</span>
                            ${warningTag}
                        </div>
                    `;

                    const distAlertSection = point.is_at_distributor ? `
                        <div class='bg-rose-50 border border-rose-300 rounded-lg p-2 mb-2 text-rose-900'>
                            <div class='font-bold flex items-center gap-1 text-xs text-rose-800 uppercase'>
                                <span>🚨 Audit: Kunjungan di Titik Distributor!</span>
                            </div>
                            <div class='text-[0.625rem] mt-1 text-rose-900 leading-snug'>
                                Check-in toko ini terdeteksi berada di lokasi fisik kantor/gudang distributor (Jarak: <strong>${point.dist_to_distributor ?? 0}m</strong> &le; 100m).
                            </div>
                        </div>
                    ` : '';

                    const warningSection = point.has_duplicate_coord ? `
                        <div class='bg-amber-50 border border-amber-300 rounded-lg p-2 mb-2 text-amber-900'>
                            <div class='font-bold flex items-center gap-1 text-xs text-amber-800 uppercase'>
                                <span>⚠️ Titik Sama Dengan Toko Lain!</span>
                            </div>
                            <div class='text-[0.625rem] mt-1 text-amber-900 leading-snug'>
                                Koordinat visit toko ini sama persis dengan:
                                <div class='mt-1 space-y-0.5'>
                                    ${(point.duplicate_with || []).map(d => `
                                        <div class='font-semibold text-amber-950 bg-amber-100/70 px-1 py-0.5 rounded'>
                                            Stop #${d.seq}: <strong>${d.name}</strong> ${d.time_in ? '(' + d.time_in + ')' : ''}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    ` : '';

                    const popupHtml = `
                        <div class='text-slate-900 text-xs p-1 min-w-[210px] max-w-[290px]'>
                            <div class='flex items-center justify-between gap-1 mb-1 border-b border-slate-200 pb-1'>
                                <span class='font-bold text-xs px-1.5 py-0.5 rounded ${point.is_at_distributor ? 'bg-rose-600 text-white' : (point.has_duplicate_coord ? 'bg-amber-600 text-white' : (isStart ? 'bg-emerald-600 text-white' : (isEnd ? 'bg-rose-600 text-white' : 'bg-sky-600 text-white')))}'>
                                    Kunjungan #${point.seq || (idx + 1)}
                                </span>
                                ${badgeLabel}
                                <span class='text-slate-600 font-mono text-[0.625rem] font-semibold'>${point.time_in ? point.time_in : '-'}</span>
                            </div>
                            ${distAlertSection}
                            ${warningSection}
                            <div class='font-bold text-sm text-slate-950 mb-0.5'>${point.name}</div>
                            <div class='text-slate-700 text-xs mb-1'><strong>SE:</strong> ${point.spv}</div>
                            <div class='grid grid-cols-2 gap-1 text-[0.625rem] bg-slate-50 p-1.5 rounded border border-slate-200 mb-1.5'>
                                <div><span class='text-slate-600 font-medium'>Jam Masuk:</span> <strong class='font-mono text-slate-900'>${point.time_in || '-'}</strong></div>
                                <div><span class='text-slate-600 font-medium'>Jam Keluar:</span> <strong class='font-mono text-slate-900'>${point.time_out || '-'}</strong></div>
                                ${point.time_consume ? `<div class='col-span-2'><span class='text-slate-600 font-medium'>Durasi:</span> <strong class='text-slate-900'>${point.time_consume}</strong></div>` : ''}
                                ${point.order_val > 0 ? `<div class='col-span-2 text-emerald-700 font-bold'>Order: Rp ${Number(point.order_val).toLocaleString('id-ID')}</div>` : ''}
                            </div>
                            <div>
                                <a href='https://www.google.com/maps/search/?api=1&query=${point.lat},${point.lon}' target='_blank' class='text-sky-700 hover:underline font-mono text-[0.625rem] inline-flex items-center gap-1 font-bold'>
                                    📍 Buka di Google Maps &rarr;
                                </a>
                            </div>
                        </div>
                    `;

                    const popup = new maplibregl.Popup({ offset: 16 }).setHTML(popupHtml);

                    const marker = new maplibregl.Marker({ element: el })
                        .setLngLat([renderLon, renderLat])
                        .setPopup(popup)
                        .addTo(this.mapInstance);

                    point._marker = marker;
                    el.addEventListener('click', () => {
                        this.selectedSeq = point.seq;
                        this.scrollToSidebarItem(point.seq);
                    });

                    this.htmlMarkers.push(marker);
                });
            },
            renderClusterView(validPoints) {
                const features = validPoints.map(point => ({
                    type: 'Feature',
                    geometry: { type: 'Point', coordinates: [point.lon, point.lat] },
                    properties: {
                        name: point.name || '-',
                        spv: point.spv || '-',
                        date: point.date || '-',
                        lat: point.lat,
                        lon: point.lon
                    }
                }));

                this.mapInstance.addSource('visits', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: features },
                    cluster: true,
                    clusterMaxZoom: 14,
                    clusterRadius: 40
                });

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
                                <div class='text-slate-500 text-[0.625rem]'><strong>Tgl:</strong> ${props.date}</div>
                                <div class='mt-1.5 pt-1.5 border-t border-slate-200'>
                                    <a href='https://www.google.com/maps/search/?api=1&query=${props.lat},${props.lon}' target='_blank' class='text-sky-600 hover:underline font-mono text-[0.625rem] inline-flex items-center gap-1 font-semibold'>
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
            }
         }"
         @open-all-maps-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-[95vw] xl:max-w-7xl h-[92vh] max-h-[92vh] flex flex-col p-3 md:p-4 bg-base-100 overflow-hidden shadow-2xl">
                {{-- Header Bar --}}
                <div class="flex flex-wrap justify-between items-center gap-2 pb-2 border-b border-slate-200 flex-none">
                    <div class="flex flex-wrap items-center gap-2 min-w-0">
                        <x-heroicon-s-map class="w-5 h-5 text-sky-500 shrink-0" />
                        <h3 class="font-bold text-base md:text-lg text-slate-900 truncate max-w-xs sm:max-w-md" x-text="modalTitle"></h3>
                        <span class="badge badge-primary badge-sm font-mono font-bold" x-text="points.length + (isRoute ? ' Titik Rute' : ' Titik')"></span>
                        <template x-if="isRoute">
                            <span class="badge badge-success badge-sm font-semibold gap-1 text-white">
                                <span>1</span> &rarr; <span x-text="points.length"></span> Urutan Jam Visit
                            </span>
                        </template>
                        <template x-if="distributorWarnings && distributorWarnings.length > 0">
                            <span class="badge badge-error badge-sm font-bold gap-1 text-white animate-pulse">
                                🚨 <span x-text="distributorWarnings.length"></span> Di Distributor
                            </span>
                        </template>
                        <template x-if="duplicateWarnings && duplicateWarnings.length > 0">
                            <span class="badge badge-warning badge-sm font-bold gap-1 text-slate-950 font-mono animate-pulse">
                                ⚠️ <span x-text="duplicateWarnings.length"></span> Titik Duplikat
                            </span>
                        </template>
                        <span class="badge badge-ghost badge-sm text-[0.625rem] text-slate-600 font-medium hidden sm:inline-flex">Rute PJP (Visit: Y)</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Satellite Switcher --}}
                        <button type="button" 
                                class="btn btn-xs rounded-lg gap-1.5 transition-all" 
                                :class="isSatellite ? 'btn-neutral text-amber-300 border-amber-400' : 'btn-outline btn-info'" 
                                @click="toggleMapLayer()" 
                                :title="isSatellite ? 'Beralih ke Peta Jalan' : 'Beralih ke Citra Satelit'">
                            <span x-text="isSatellite ? '🛰️ Satelit' : '🗺️ Jalan'"></span>
                        </button>
                        {{-- Refresh Map --}}
                        <button type="button" class="btn btn-xs btn-outline btn-info rounded-lg gap-1" @click="renderMap()" title="Klik jika peta tidak sejajar/blank">
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5" /> <span class="hidden sm:inline">Refresh</span>
                        </button>
                        {{-- Close Button --}}
                        <button type="button" class="btn btn-xs btn-circle btn-ghost text-slate-400 hover:text-slate-800 dark:hover:text-white" @click="closeModal()" title="Tutup">
                            ✕
                        </button>
                    </div>
                </div>

                {{-- KPI Duration Banner: Total Jam Visit Toko & Jam Perjalanan --}}
                <template x-if="consumeStr !== '-' || travelStr !== '-'">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 px-3 py-1.5 my-2 rounded-xl bg-slate-900 text-white shadow-md border border-slate-700/80 flex-none">
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            {{-- Jam Kerja / Rentang Waktu (Harian) --}}
                            <template x-if="timeRange">
                                <div class="flex items-center gap-1.5 pr-3 border-r border-slate-700/80 font-mono">
                                    <span class="text-slate-400 text-[0.625rem]">🕒 Jam Kerja:</span>
                                    <span class="font-bold text-slate-100 text-xs" x-text="timeRange"></span>
                                    <span class="text-slate-400 text-[0.625rem]" x-text="'(' + workDurationStr + ')'"></span>
                                </div>
                            </template>
                            
                            {{-- Total Jam Visit Toko --}}
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block shadow-sm"></span>
                                <span class="text-slate-300 font-medium text-[0.625rem]">🏬 Total Visit Toko:</span>
                                <span class="font-mono font-bold text-emerald-400 text-xs md:text-sm" x-text="consumeStr"></span>
                                <template x-if="consumePercent !== null">
                                    <span class="badge badge-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-mono font-bold px-1.5" x-text="consumePercent + '%'"></span>
                                </template>
                            </div>

                            <span class="text-slate-600 hidden sm:inline">&bull;</span>

                            {{-- Total Jam Perjalanan --}}
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 inline-block shadow-sm"></span>
                                <span class="text-slate-300 font-medium text-[0.625rem]">🛵 Total Perjalanan:</span>
                                <span class="font-mono font-bold text-amber-400 text-xs md:text-sm" x-text="travelStr"></span>
                                <template x-if="travelPercent !== null">
                                    <span class="badge badge-xs bg-amber-500/20 text-amber-300 border border-amber-500/40 font-mono font-bold px-1.5" x-text="travelPercent + '%'"></span>
                                </template>
                            </div>
                        </div>

                        {{-- Visual Rasio Bar Waktu Toko vs Perjalanan --}}
                        <template x-if="consumePercent !== null && travelPercent !== null">
                            <div class="flex items-center gap-2 text-[0.625rem] font-mono shrink-0 ml-auto">
                                <span class="text-[0.625rem] text-slate-400 font-sans hidden md:inline">Rasio Waktu:</span>
                                <div class="w-28 sm:w-36 h-2 rounded-full overflow-hidden bg-slate-800 flex border border-slate-700 shadow-inner" title="Rasio Waktu: Toko vs Perjalanan">
                                    <div class="bg-emerald-500 h-full transition-all duration-300" :style="'width: ' + consumePercent + '%'"></div>
                                    <div class="bg-amber-500 h-full transition-all duration-300" :style="'width: ' + travelPercent + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- 2-Column Split Dashboard Body --}}
                <div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-3 pt-1 overflow-hidden">
                    {{-- Kolom Kiri: Map View Container --}}
                    <div class="flex-1 h-full min-h-0 relative rounded-xl overflow-hidden border border-base-300 bg-slate-100 flex flex-col">
                        {{-- Alert Banner for Empty / Invalid GPS Coordinates --}}
                        <template x-if="hasNoValidPoints">
                            <div class="absolute top-2 left-2 right-2 z-20 alert alert-warning py-2 px-3 text-xs rounded-xl shadow-lg border border-amber-300 bg-amber-50 dark:bg-amber-950/90 text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" />
                                <div>
                                    <strong>Informasi:</strong> Tidak ada titik koordinat GPS valid untuk ditampilkan pada hari ini (titik GPS kosong atau bernilai 0).
                                </div>
                            </div>
                        </template>

                        {{-- MapLibre Canvas Container --}}
                        <div id="all-maps-se" wire:ignore class="w-full h-full flex-1"></div>

                        {{-- Floating Translucent Legend (Bottom-Left) --}}
                        <template x-if="isRoute && points.length > 1">
                            <div class="absolute bottom-2 left-2 z-10 bg-slate-900/85 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg border border-slate-700/80 shadow-lg text-[0.625rem] flex flex-wrap items-center gap-2.5 max-w-[calc(100%-1rem)] pointer-events-auto">
                                <span class="flex items-center gap-1 font-medium">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block border border-white"></span>
                                    <span>#1 Start</span>
                                </span>
                                <span class="flex items-center gap-1 font-medium">
                                    <span class="w-4 h-0.5 bg-sky-400 rounded inline-block"></span>
                                    <span>Jalur Rute</span>
                                </span>
                                <span class="flex items-center gap-1 font-medium">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block border border-white"></span>
                                    <span>#<span x-text="points.length"></span> Finish</span>
                                </span>
                                <template x-if="distributorPoint">
                                    <span class="flex items-center gap-1 font-medium text-rose-300">
                                        <span class="w-3.5 h-3.5 rounded-full bg-slate-950 inline-flex items-center justify-center text-[0.625rem] border border-rose-500">🏢</span>
                                        <span>Distributor (100m)</span>
                                    </span>
                                </template>
                                <template x-if="distributorWarnings && distributorWarnings.length > 0">
                                    <span class="flex items-center gap-1 font-medium text-rose-400">
                                        <span class="w-3.5 h-3.5 rounded-full bg-rose-600 inline-flex items-center justify-center text-[0.625rem] text-white font-bold border border-white">🚨</span>
                                        <span>&le;100m Distributor</span>
                                    </span>
                                </template>
                                <template x-if="duplicateWarnings && duplicateWarnings.length > 0">
                                    <span class="flex items-center gap-1 font-medium text-amber-300">
                                        <span class="w-3.5 h-3.5 rounded-full bg-amber-500 inline-flex items-center justify-center text-[0.625rem] text-white font-bold border border-white">!</span>
                                        <span>Titik Duplikat</span>
                                    </span>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Kolom Kanan: Interactive Sidebar Panel --}}
                    <div class="w-full lg:w-80 xl:w-96 flex flex-col h-full min-h-0 bg-slate-100/80 rounded-xl border border-slate-200 p-2.5 overflow-hidden shrink-0">
                        {{-- Tabs Navigation --}}
                        <div class="flex items-center gap-1.5 border-b border-slate-300 pb-2 mb-2 flex-none">
                            <button type="button" 
                                    @click="activeSidebarTab = 'audit'" 
                                    class="btn btn-xs flex-1 rounded-lg gap-1.5 font-bold transition-all" 
                                    :class="activeSidebarTab === 'audit' ? 'btn-primary text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300'">
                                <span>🚨 Temuan Audit</span>
                                <template x-if="distributorWarnings.length + duplicateWarnings.length > 0">
                                    <span class="badge badge-xs bg-rose-600 text-white font-mono font-bold" x-text="distributorWarnings.length + duplicateWarnings.length"></span>
                                </template>
                            </button>
                            <button type="button" 
                                    @click="activeSidebarTab = 'stops'" 
                                    class="btn btn-xs flex-1 rounded-lg gap-1.5 font-bold transition-all" 
                                    :class="activeSidebarTab === 'stops' ? 'btn-primary text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300'">
                                <span>🗺️ Urutan Stop</span>
                                <span class="badge badge-xs bg-slate-800 text-white font-mono font-bold" x-text="points.length"></span>
                            </button>
                        </div>

                        {{-- Tab 1: Temuan Audit Content --}}
                        <div x-show="activeSidebarTab === 'audit'" class="flex-1 min-h-0 overflow-y-auto space-y-2 pr-1">
                            {{-- Distributor Point Overview Card --}}
                            <template x-if="distributorPoint">
                                <div class="p-2.5 rounded-xl bg-white border border-slate-300 shadow-sm hover:border-sky-500 transition-all cursor-pointer mb-2"
                                     @click="selectDistributor()"
                                     :class="selectedSeq === 'distributor' ? 'ring-2 ring-rose-500 bg-rose-50/40' : ''">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold flex items-center gap-1 text-slate-900">
                                            <span>🏢</span> Kantor Distributor
                                        </span>
                                        <button type="button" class="btn btn-ghost btn-xs text-rose-600 hover:text-rose-700 px-1 py-0 h-auto font-mono text-[0.625rem] font-bold">
                                            📍 Sorot di Peta
                                        </button>
                                    </div>
                                    <div class="text-xs text-slate-900 font-bold mt-1" x-text="distributorPoint.name || distributorPoint.code"></div>
                                    <div class="text-xs text-slate-600 font-mono flex items-center justify-between mt-1 pt-1 border-t border-slate-200">
                                        <span>Kode: <strong class="text-slate-800" x-text="distributorPoint.code"></strong></span>
                                        <span class="text-rose-600 font-bold">Radius Audit: 100m</span>
                                    </div>
                                </div>
                            </template>

                            {{-- Empty Audit Notice --}}
                            <template x-if="distributorWarnings.length === 0 && duplicateWarnings.length === 0">
                                <div class="flex flex-col items-center justify-center h-48 text-center p-4 text-slate-400">
                                    <x-heroicon-o-check-circle class="w-10 h-10 text-emerald-500 mb-2" />
                                    <div class="font-bold text-xs text-slate-800">Tidak Ada Temuan Audit</div>
                                    <div class="text-xs mt-0.5 text-slate-500">Semua titik kunjungan toko tercatat valid, unik, dan di luar radius distributor.</div>
                                </div>
                            </template>

                            {{-- Distributor Warnings List --}}
                            <template x-if="distributorWarnings && distributorWarnings.length > 0">
                                <div class="space-y-1.5 mb-3">
                                    <div class="flex items-center justify-between text-xs font-bold text-rose-700 uppercase tracking-wide">
                                        <span class="flex items-center gap-1">🚨 Di Titik Distributor (&le;100m)</span>
                                        <span class="badge badge-xs badge-error text-white font-bold font-mono" x-text="distributorWarnings.length"></span>
                                    </div>
                                    <template x-for="dw in distributorWarnings" :key="'dist-' + dw.seq">
                                        <div @click="selectStopBySeq(dw.seq)" 
                                             class="p-2.5 rounded-xl bg-rose-50 border border-rose-300 shadow-sm hover:border-rose-600 hover:shadow transition-all cursor-pointer text-xs"
                                             :class="selectedSeq === dw.seq ? 'ring-2 ring-rose-500 bg-rose-100/50 shadow-md' : ''">
                                            <div class="flex items-center justify-between gap-1 mb-1">
                                                <span class="font-bold text-[0.625rem] px-1.5 py-0.5 rounded bg-rose-600 text-white font-mono">
                                                    Stop #<span x-text="dw.seq"></span>
                                                </span>
                                                <span class="font-mono text-[0.625rem] text-rose-800 font-bold" x-text="dw.time_in ? 'Jam: ' + dw.time_in : '-'"></span>
                                                <button type="button" class="btn btn-ghost btn-xs text-rose-700 hover:text-rose-900 px-1 py-0 h-auto font-mono text-[0.625rem] font-bold ml-auto">
                                                    📍 Sorot
                                                </button>
                                            </div>
                                            <div class="font-bold text-slate-950 text-xs truncate" x-text="dw.name"></div>
                                            <div class="flex items-center justify-between text-[0.625rem] text-rose-900 mt-1.5 font-mono pt-1 border-t border-rose-200">
                                                <span>Jarak: <strong class="text-rose-950" x-text="dw.dist"></strong>m dari gudang</span>
                                                <span class="badge badge-xs bg-rose-200 border border-rose-400 text-rose-950 font-bold">&le; 100 Meter</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Duplicate Coordinates List --}}
                            <template x-if="duplicateWarnings && duplicateWarnings.length > 0">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs font-bold text-amber-800 uppercase tracking-wide">
                                        <span class="flex items-center gap-1">⚠️ Titik Sama / Duplikat (0m)</span>
                                        <span class="badge badge-xs badge-warning text-slate-900 font-bold font-mono" x-text="duplicateWarnings.length"></span>
                                    </div>
                                    <template x-for="(dup, dIdx) in duplicateWarnings" :key="'dup-' + dIdx">
                                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-300 shadow-sm text-xs space-y-1.5">
                                            <div class="text-xs font-bold text-amber-950 flex items-center gap-1">
                                                <span>📍 Koordinat GPS Identik Antar Toko:</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-1.5 p-1.5 rounded-lg bg-white border border-amber-300 cursor-pointer hover:border-amber-600 hover:shadow transition-all"
                                                 @click="selectStopBySeq(dup.seq1)"
                                                 :class="selectedSeq === dup.seq1 ? 'ring-2 ring-amber-500 bg-amber-100/50' : ''">
                                                <div class="truncate flex items-center gap-1.5 min-w-0">
                                                    <span class="font-bold px-1.5 py-0.5 rounded bg-amber-500 text-white text-[0.625rem] shrink-0 font-mono">#<span x-text="dup.seq1"></span></span>
                                                    <strong class="text-slate-950 font-bold text-xs truncate" x-text="dup.name1"></strong>
                                                    <span class="text-slate-600 font-mono text-[0.625rem] shrink-0" x-text="dup.time1 ? '(' + dup.time1 + ')' : ''"></span>
                                                </div>
                                                <span class="text-amber-700 font-bold text-[0.625rem] shrink-0 font-mono hover:underline">📍 Sorot</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-1.5 p-1.5 rounded-lg bg-white border border-amber-300 cursor-pointer hover:border-amber-600 hover:shadow transition-all"
                                                 @click="selectStopBySeq(dup.seq2)"
                                                 :class="selectedSeq === dup.seq2 ? 'ring-2 ring-amber-500 bg-amber-100/50' : ''">
                                                <div class="truncate flex items-center gap-1.5 min-w-0">
                                                    <span class="font-bold px-1.5 py-0.5 rounded bg-amber-500 text-white text-[0.625rem] shrink-0 font-mono">#<span x-text="dup.seq2"></span></span>
                                                    <strong class="text-slate-950 font-bold text-xs truncate" x-text="dup.name2"></strong>
                                                    <span class="text-slate-600 font-mono text-[0.625rem] shrink-0" x-text="dup.time2 ? '(' + dup.time2 + ')' : ''"></span>
                                                </div>
                                                <span class="text-amber-700 font-bold text-[0.625rem] shrink-0 font-mono hover:underline">📍 Sorot</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Tab 2: Urutan Stop Content --}}
                        <div x-show="activeSidebarTab === 'stops'" class="flex-1 min-h-0 flex flex-col overflow-hidden">
                            {{-- Search Filter Input --}}
                            <div class="relative mb-2 flex-none">
                                <input type="text" x-model="stopSearch" placeholder="Cari nama toko, custno, #stop..." class="input input-xs input-bordered w-full pr-7 rounded-lg text-xs bg-white text-slate-900 border-slate-300 placeholder:text-slate-400 focus:border-sky-500 font-medium" />
                                <template x-if="stopSearch">
                                    <button type="button" @click="stopSearch = ''" class="absolute right-2 top-1 text-slate-500 hover:text-slate-800 font-bold text-xs">✕</button>
                                </template>
                            </div>

                            {{-- Scrollable Stops List --}}
                            <div class="flex-1 min-h-0 overflow-y-auto space-y-1.5 pr-1">
                                <template x-for="(pt, idx) in getFilteredPoints()" :key="pt.seq || idx">
                                    <div :id="'sidebar-stop-' + pt.seq" 
                                         @click="selectStop(pt)" 
                                         class="p-2.5 rounded-xl bg-white border transition-all cursor-pointer text-xs" 
                                         :class="selectedSeq === pt.seq ? 'ring-2 ring-primary border-primary bg-primary/5 shadow-md' : 'border-slate-300 hover:border-sky-500 hover:shadow shadow-sm'">
                                        <div class="flex items-center justify-between gap-1 mb-1">
                                            <span class="font-bold text-[0.625rem] px-1.5 py-0.5 rounded text-white font-mono"
                                                  :class="pt.is_at_distributor ? 'bg-rose-600' : (pt.has_duplicate_coord ? 'bg-amber-600' : (idx === 0 ? 'bg-emerald-600' : (idx === points.length - 1 && points.length > 1 ? 'bg-rose-600' : 'bg-sky-600')))">
                                                #<span x-text="pt.seq || (idx + 1)"></span>
                                                <template x-if="idx === 0 && !pt.is_at_distributor && !pt.has_duplicate_coord"><span class="ml-0.5 text-[0.625rem] font-sans font-bold">START</span></template>
                                                <template x-if="idx === points.length - 1 && points.length > 1 && !pt.is_at_distributor && !pt.has_duplicate_coord"><span class="ml-0.5 text-[0.625rem] font-sans font-bold">FINISH</span></template>
                                            </span>
                                            <template x-if="pt.is_at_distributor">
                                                <span class="badge badge-xs badge-error text-white font-bold font-mono">🚨 &le;100m</span>
                                            </template>
                                            <template x-if="pt.has_duplicate_coord">
                                                <span class="badge badge-xs badge-warning text-slate-900 font-bold font-mono">⚠️ Duplikat</span>
                                            </template>
                                            <span class="text-slate-600 font-mono text-[0.625rem] font-semibold ml-auto" x-text="pt.time_in ? pt.time_in : '-'"></span>
                                        </div>
                                        <div class="font-bold text-slate-950 text-xs truncate" x-text="pt.name"></div>
                                        <div class="flex items-center justify-between text-[0.625rem] text-slate-600 mt-1 font-mono">
                                            <span>Jam: <strong class="text-slate-800" x-text="(pt.time_in || '-') + ' &rarr; ' + (pt.time_out || '-')"></strong></span>
                                            <template x-if="pt.time_consume">
                                                <span class="font-semibold text-slate-700 bg-slate-100 px-1 py-0.5 rounded border border-slate-200 text-[0.625rem]" x-text="pt.time_consume"></span>
                                            </template>
                                        </div>
                                        <template x-if="pt.order_val > 0">
                                            <div class="text-[0.625rem] font-bold text-emerald-700 mt-1 font-mono flex items-center justify-between pt-1 border-t border-slate-100">
                                                <span>Order:</span>
                                                <span>Rp <span x-text="Number(pt.order_val).toLocaleString('id-ID')"></span></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-200 mt-2 text-xs flex-none">
                    <div class="text-xs text-slate-600 font-medium hidden sm:inline">
                        💡 Klik titik marker pada peta atau kartu pada daftar untuk navigasi & sorot instan.
                    </div>
                    <button type="button" class="btn btn-sm btn-neutral text-white rounded-xl px-5 ml-auto font-bold shadow-sm" @click="closeModal()">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="closeModal()"></div>
        </div>
    </div>
    </div>
</div>
