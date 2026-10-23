<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('se_summary_show_kpi') !== null 
             ? localStorage.getItem('se_summary_show_kpi') === 'true' 
             : (window.innerWidth >= 1280),
         autoHidden: (localStorage.getItem('se_summary_show_kpi') === null && window.innerWidth < 1280),
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
             localStorage.setItem('se_summary_show_kpi', this.showKpi);
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
                     localStorage.setItem('se_summary_show_kpi', 'false');
                 }
             } 
             // Jika kembali ke 1 baris dan sebelumnya disembunyikan otomatis oleh sistem -> tampilkan kembali
             else if (!this.wasOneRow && nowOneRow && this.autoHidden) {
                 this.showKpi = true;
                 this.autoHidden = false;
                 localStorage.setItem('se_summary_show_kpi', 'true');
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
    <x-slot name="title">Summary Kunjungan SE</x-slot>

    {{-- Standar Tab Menu dari resources/views/components/ui/tab-menu.blade.php --}}
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
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.summary', $tabParams)" :navigate="true">
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
                @if($appliedRegion && !empty($grandTotal->total_salesman))
                    <span class="badge badge-primary text-primary-content font-bold text-xs gap-1 py-2.5 px-3 shadow-2xs font-mono">
                        <x-heroicon-s-user-group class="w-4 h-4" />
                        {{ number_format($grandTotal->total_salesman) }} SE
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

    {{-- 5 KPI Cards Section (Show/Hide Controlled by Alpine showKpi) --}}
    <div x-show="showKpi"
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 -translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 -translate-y-2">
        @include('livewire.report.analisa-kunjungan-se.kpi-cards')
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar (Standar Kapsul Hybrid Breadcrumb Analisa Kunjungan SE) --}}
        <x-ui.filter-toolbar>
            {{-- 1. Kapsul Hierarki Wilayah (Region & Area Native + Distributor Micro-Searchable Popover) --}}
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
                        placeholder="Semua Area" 
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
                    placeholder="Semua Distributor" 
                    :allowAll="true"
                    searchPlaceholder="Cari nama distributor / kode..."
                    icon="building" 
                    :disabled="empty($selectedRegion)" />
            </x-ui.filter-breadcrumb>

            {{-- 2. Kapsul Periode Pintar (Smart Date Range Filter) --}}
            <x-ui.filter-date-range 
                start="startDate" 
                end="endDate" 
                :maxDays="31" />

            {{-- 3. Search Box SE / Distributor --}}
            <div class="relative w-full sm:w-auto">
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Cari SE / Distributor..."
                       class="input input-xs h-7 rounded-lg bg-base-100 border border-base-300 text-xs pl-7 pr-2 w-full sm:w-44 focus:outline-none focus:border-primary shadow-2xs" />
                <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 absolute left-2 top-1/2 -translate-y-1/2 text-base-content/40 pointer-events-none" />
            </div>

            <x-slot name="actions">
                @php $canApply = !empty($selectedRegion); @endphp
                <x-ui.filter-actions 
                    applyAction="applyFilter" 
                    resetAction="resetFilters" 
                    :canApply="$canApply" 
                    disabledTip="Pilih minimal Region terlebih dahulu">
                    
                    @if($canExport)
                        <button type="button" 
                            wire:click="exportExcel" 
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer"
                            title="Download Summary Kunjungan SE ke Excel (.xlsx)">
                            <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1.5">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span>Excel</span>
                            </span>
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </x-ui.filter-actions>
            </x-slot>
        </x-ui.filter-toolbar>

        {{-- Body Card (Tabel Summary Kunjungan SE) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <div wire:loading.flex wire:target="applyFilter, resetFilters, previousMonth, nextMonth, sortByColumn"
                 class="absolute inset-0 bg-base-100/70 backdrop-blur-2xs z-30 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 bg-base-100 px-5 py-4 rounded-xl shadow-lg border border-base-300">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-semibold text-base-content">Memuat Summary Kunjungan SE...</span>
                </div>
            </div>

            @if(empty($appliedRegion))
                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-base-content/40">
                    <x-heroicon-o-funnel class="w-12 h-12 mb-2 text-base-content/30" />
                    <h3 class="text-sm font-bold text-base-content/80">Silakan Tentukan Filter</h3>
                    <p class="text-xs text-base-content/60 max-w-sm mt-0.5">
                        Pilih minimal Region (bisa pilih Semua Area atau Area tertentu), lalu klik tombol <span class="text-primary font-bold">Terapkan</span> untuk menampilkan summary performa Sales Exclusive.
                    </p>
                </div>
            @elseif($rows->isEmpty())
                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-base-content/40">
                    <x-heroicon-o-inbox class="w-12 h-12 mb-2 text-base-content/30" />
                    <h3 class="text-sm font-bold text-base-content/80">Tidak Ada Data Ditemukan</h3>
                    <p class="text-xs text-base-content/60 max-w-sm mt-0.5">
                        Tidak ada catatan kunjungan SE untuk filter dan rentang tanggal yang dipilih.
                    </p>
                </div>
            @else
                <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs border-collapse [&_tbody_td]:py-0.5 [&_tbody_td]:px-1.5 [&_tbody_tr]:h-6">
                    {{-- THEAD Multi-level Sticky Penuh Tanpa Overlap --}}
                    <thead class="sticky top-0 z-20 bg-base-300 text-base-content shadow-sm [&_th]:py-1.5 [&_th]:px-2">
                        {{-- Row 1: Header Grup (Colspan & Rowspan) --}}
                        <tr class="[&>th]:align-middle text-xs uppercase tracking-wider border-b border-base-content/10">
                            {{-- # Spanning 2 Rows --}}
                            <th rowspan="2" class="w-10 text-center align-middle bg-base-300 text-base-content font-bold border-b-2 border-base-content/20">#</th>

                            {{-- Distributor Spanning 2 Rows --}}
                            <th rowspan="2" class="min-w-36 text-left align-middle bg-base-300 text-base-content font-bold border-b-2 border-base-content/20 cursor-pointer select-none hover:text-primary transition-colors pl-3" wire:click="sortByColumn('distributor_name')">
                                <div class="flex items-center gap-1">
                                    <span>Distributor</span>
                                    @if($sortField === 'distributor_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>

                            {{-- Salesman Spanning 2 Rows --}}
                            <th rowspan="2" class="min-w-44 text-left align-middle bg-base-300 text-base-content font-bold border-b-2 border-base-content/20 cursor-pointer select-none hover:text-primary transition-colors pl-3" wire:click="sortByColumn('salesman_name')">
                                <div class="flex items-center gap-1">
                                    <span>Salesman (SE)</span>
                                    @if($sortField === 'salesman_name') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>
                            
                            {{-- Group 1: Total Kunjungan (PC + XC) --}}
                            <th colspan="5" class="text-center bg-base-300 text-base-content font-bold border-b border-base-content/10 border-r border-base-content/10">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-info"></span>
                                    <span class="tracking-wide">Total Kunjungan (All Call)</span>
                                </div>
                            </th>
                            
                            {{-- Group 2: Rute PJP (Murni PC) --}}
                            <th colspan="5" class="text-center bg-base-300 text-base-content font-bold border-b border-base-content/10 border-r border-base-content/10">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <span class="tracking-wide">Rute PJP (Exclude XC)</span>
                                </div>
                            </th>
                            
                            {{-- Group 3: Extra Call --}}
                            <th colspan="5" class="text-center bg-base-300 text-base-content font-bold border-b border-base-content/10 border-r border-base-content/10">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-warning"></span>
                                    <span class="tracking-wide">Extra Call</span>
                                </div>
                            </th>
                            
                            {{-- Total Order Spanning 2 Rows --}}
                            <th rowspan="2" class="min-w-28 text-right align-middle bg-base-300 text-base-content font-bold border-b-2 border-base-content/20 cursor-pointer select-none hover:text-primary transition-colors pr-3" wire:click="sortByColumn('total_order_val')" title="Total Nominal Nilai Pemesanan Toko">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Total Order (Rp)</span>
                                    @if($sortField === 'total_order_val') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                                </div>
                            </th>
                            
                            {{-- Group 4: Waktu Operasional --}}
                            <th colspan="4" class="text-center bg-base-300 text-base-content font-bold border-b border-base-content/10 border-r border-base-content/10">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-success"></span>
                                    <span class="tracking-wide">Waktu Operasional</span>
                                </div>
                            </th>
                            
                            {{-- Aksi Spanning 2 Rows --}}
                            <th rowspan="2" class="w-14 text-center align-middle bg-base-300 text-base-content font-bold border-b-2 border-base-content/20">Aksi</th>
                        </tr>

                        {{-- Row 2: Sub-Header Kolom Spesifik (Scales with rem on Zoom) --}}
                        <tr class="text-[0.625rem] font-bold uppercase tracking-wider border-b-2 border-base-content/20">
                            {{-- Sub-Headers Group 1: Total Kunjungan --}}
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('total_pc')" title="Total Plan Call (Target PC + XC)">
                                <div class="flex items-center justify-end gap-0.5"><span>PC</span>@if($sortField === 'total_pc')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('total_ac')" title="Total Actual Call (Realisasi PC + XC)">
                                <div class="flex items-center justify-end gap-0.5"><span>AC</span>@if($sortField === 'total_ac')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-center bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('total_ac_percent')" title="Persentase Kunjungan Total (% AC / PC)">
                                <div class="flex items-center justify-center gap-0.5"><span>% AC</span>@if($sortField === 'total_ac_percent')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('total_ec')" title="Total Toko yang Menghasilkan Order (Effective Call)">
                                <div class="flex items-center justify-end gap-0.5"><span>EC</span>@if($sortField === 'total_ec')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-center bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('total_ec_percent')" title="Persentase Konversi Order (% EC / AC)">
                                <div class="flex items-center justify-center gap-0.5"><span>% EC</span>@if($sortField === 'total_ec_percent')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>

                            {{-- Sub-Headers Group 2: Rute PJP (Exclude XC) --}}
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('exc_pc')" title="Target Plan Call Rute PJP Murni">
                                <div class="flex items-center justify-end gap-0.5"><span>PC</span>@if($sortField === 'exc_pc')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('exc_ac')" title="Actual Call Rute PJP Murni">
                                <div class="flex items-center justify-end gap-0.5"><span>AC</span>@if($sortField === 'exc_ac')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-center bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('exc_ac_percent')" title="Persentase Realisasi Rute PJP Murni (% AC / PC)">
                                <div class="flex items-center justify-center gap-0.5"><span>% AC</span>@if($sortField === 'exc_ac_percent')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('exc_ec')" title="Effective Call Rute PJP Murni (Toko PJP yang Order)">
                                <div class="flex items-center justify-end gap-0.5"><span>EC</span>@if($sortField === 'exc_ec')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-center bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('exc_ec_percent')" title="Persentase Konversi Order Rute PJP (% EC / AC)">
                                <div class="flex items-center justify-center gap-0.5"><span>% EC</span>@if($sortField === 'exc_ec_percent')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>

                            {{-- Sub-Headers Group 3: Extra Call (Just XC) --}}
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('just_pc')" title="Target Extra Call (XC)">
                                <div class="flex items-center justify-end gap-0.5"><span>XC</span>@if($sortField === 'just_pc')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('just_ac')" title="Actual Call Extra Call (XC)">
                                <div class="flex items-center justify-end gap-0.5"><span>AC</span>@if($sortField === 'just_ac')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-center bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('just_ac_percent')" title="Persentase Realisasi Extra Call (% AC / XC)">
                                <div class="flex items-center justify-center gap-0.5"><span>% AC</span>@if($sortField === 'just_ac_percent')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('just_ec')" title="Effective Call dari Extra Call">
                                <div class="flex items-center justify-end gap-0.5"><span>EC</span>@if($sortField === 'just_ec')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-14 text-center bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('just_ec_percent')" title="Persentase Konversi Order Extra Call (% EC / AC)">
                                <div class="flex items-center justify-center gap-0.5"><span>% EC</span>@if($sortField === 'just_ec_percent')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>

                            {{-- Sub-Headers Group 4: Waktu Operasional --}}
                            <th class="w-20 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('total_consume_sec')" title="Total Waktu Kunjungan di Dalam Toko">
                                <div class="flex items-center justify-end gap-0.5"><span>Total Toko</span>@if($sortField === 'total_consume_sec')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-16 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('avg_consume_sec')" title="Rata-rata Durasi per Kunjungan Toko">
                                <div class="flex items-center justify-end gap-0.5"><span>Avg/Toko</span>@if($sortField === 'avg_consume_sec')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-20 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('total_travel_sec')" title="Total Waktu Perjalanan Antar Toko">
                                <div class="flex items-center justify-end gap-0.5"><span>Total Jalan</span>@if($sortField === 'total_travel_sec')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                            <th class="w-16 text-right pr-2 bg-base-200 text-base-content/80 cursor-pointer hover:bg-base-300 hover:text-base-content transition-colors" wire:click="sortByColumn('avg_travel_sec')" title="Rata-rata Durasi Perjalanan Antar Toko">
                                <div class="flex items-center justify-end gap-0.5"><span>Avg/Jalan</span>@if($sortField === 'avg_travel_sec')<span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</div>
                            </th>
                        </tr>
                    </thead>

                    {{-- TBODY: Strictly 1 Single Line Per Data Row --}}
                    <tbody class="text-xs relative">
                        @foreach($rows as $index => $row)
                            @php
                                // Visit Badge Class: Identik dengan Detail Data
                                $totAcBadge = $row->total_ac_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';
                                $excAcBadge = $row->exc_ac_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';
                                $justAcBadge = $row->just_ac_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';

                                // EC Badge Class: Identik dengan Detail Data (<80% Merah, 80-99% Kuning, >=100% Hijau)
                                $totEcBadge = $row->total_ec_percent >= 100 ? 'badge-success text-white' : ($row->total_ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white');
                                $excEcBadge = $row->exc_ec_percent >= 100 ? 'badge-success text-white' : ($row->exc_ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white');
                                $justEcBadge = $row->just_ec_percent >= 100 ? 'badge-success text-white' : ($row->just_ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white');
                            @endphp
                            <tr class="hover:bg-primary/5 transition-colors border-b border-base-200/60 select-none h-6 [&_td]:py-0.5 [&_td]:px-1.5">
                                {{-- Index No --}}
                                <td class="text-center whitespace-nowrap font-mono text-base-content/60 text-xs font-semibold">{{ $index + 1 }}</td>
                                
                                {{-- Distributor (Single Line) --}}
                                <td class="whitespace-nowrap font-medium text-base-content text-xs truncate max-w-44" title="{{ $row->distributor_name }} ({{ $row->distributor_code }})">
                                    {{ $row->distributor_name }}
                                </td>

                                {{-- Salesman: Strictly 1 Single Line (Nama + Kode + HK) --}}
                                <td class="whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <span class="font-bold text-xs text-base-content truncate max-w-36" title="{{ $row->salesman_name }}">
                                            {{ $row->salesman_name }}
                                        </span>
                                        <span class="text-xs text-base-content/50 font-mono">({{ $row->salesman_code }})</span>
                                        <span class="badge badge-xs badge-neutral font-mono font-semibold px-1 py-0 h-4 min-h-0 text-[0.625rem] shrink-0" title="Hari Kerja Aktif: {{ $row->total_days }} HK">
                                            {{ $row->total_days }} HK
                                        </span>
                                    </div>
                                </td>

                                {{-- Total: PC, AC, %, EC, % --}}
                                <td class="text-right whitespace-nowrap font-mono text-xs text-base-content/90">{{ number_format($row->total_pc) }}</td>
                                <td class="text-right whitespace-nowrap font-mono text-xs font-semibold text-base-content">{{ number_format($row->total_ac) }}</td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="badge badge-xs {{ $totAcBadge }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $row->total_ac_percent }}%
                                    </span>
                                </td>
                                <td class="text-right whitespace-nowrap font-mono text-xs font-semibold text-base-content">{{ number_format($row->total_ec) }}</td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="badge badge-xs {{ $totEcBadge }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $row->total_ec_percent }}%
                                    </span>
                                </td>

                                {{-- Exclude XC: PC, AC, %, EC, % --}}
                                <td class="text-right whitespace-nowrap font-mono text-xs text-base-content/90">{{ number_format($row->exc_pc) }}</td>
                                <td class="text-right whitespace-nowrap font-mono text-xs font-semibold text-base-content">{{ number_format($row->exc_ac) }}</td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="badge badge-xs {{ $excAcBadge }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $row->exc_ac_percent }}%
                                    </span>
                                </td>
                                <td class="text-right whitespace-nowrap font-mono text-xs font-semibold text-base-content">{{ number_format($row->exc_ec) }}</td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="badge badge-xs {{ $excEcBadge }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $row->exc_ec_percent }}%
                                    </span>
                                </td>

                                {{-- Just XC: XC, AC, %, EC, % --}}
                                <td class="text-right whitespace-nowrap font-mono text-xs text-base-content/90">{{ number_format($row->just_pc) }}</td>
                                <td class="text-right whitespace-nowrap font-mono text-xs font-semibold text-base-content">{{ number_format($row->just_ac) }}</td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="badge badge-xs {{ $justAcBadge }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $row->just_ac_percent }}%
                                    </span>
                                </td>
                                <td class="text-right whitespace-nowrap font-mono text-xs font-semibold text-base-content">{{ number_format($row->just_ec) }}</td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="badge badge-xs {{ $justEcBadge }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $row->just_ec_percent }}%
                                    </span>
                                </td>

                                {{-- Total Order (Rp) --}}
                                <td class="text-right whitespace-nowrap font-mono text-xs pr-2">
                                    @if($row->total_order_val >= 10000000)
                                        <span class="text-success font-bold" title="Top Sales: Rp {{ number_format($row->total_order_val, 0, ',', '.') }}">
                                            Rp {{ number_format($row->total_order_val, 0, ',', '.') }}
                                        </span>
                                    @elseif($row->total_order_val > 0)
                                        <span class="text-primary font-semibold">
                                            Rp {{ number_format($row->total_order_val, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>

                                {{-- Waktu Operasional --}}
                                <td class="text-right whitespace-nowrap font-mono text-xs font-medium text-base-content/90">{{ $row->total_jam_toko_str }}</td>
                                <td class="text-right whitespace-nowrap font-mono text-xs text-base-content/60">{{ $row->avg_jam_toko_str }}</td>
                                <td class="text-right whitespace-nowrap font-mono text-xs font-medium text-base-content/90">{{ $row->total_jam_jalan_str }}</td>
                                <td class="text-right whitespace-nowrap font-mono text-xs text-base-content/60">{{ $row->avg_jam_jalan_str }}</td>

                                {{-- Aksi: Shortcut ke Detail SE --}}
                                <td class="text-center whitespace-nowrap">
                                    <a href="{{ route('report.analisa-kunjungan-se.index', [
                                            'region' => $appliedRegion,
                                            'area' => $appliedArea ?: $row->area_code,
                                            'distributor' => $row->distributor_code,
                                            'salesman' => $row->salesman_code,
                                            'startDate' => $appliedStartDate,
                                            'endDate' => $appliedEndDate,
                                       ]) }}"
                                       wire:navigate
                                       class="inline-flex items-center justify-center w-5 h-5 rounded hover:bg-primary/15 text-primary transition-colors"
                                       title="Buka Data Detail Harian SE: {{ $row->salesman_name }}">
                                        <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Sticky Grand Total Footer (Warna Kontras Kuat & Berbeda) --}}
                    @if($grandTotal)
                        <tfoot class="bg-slate-950 dark:bg-black text-white border-t-2 border-primary shadow-[0_-4px_12px_rgba(0,0,0,0.35)] sticky bottom-0 z-20 [&_th]:py-1 [&_th]:px-1.5 divide-x divide-slate-800">
                            <tr class="font-bold text-white [&>th]:align-middle bg-slate-950 dark:bg-black h-7">
                                <th colspan="3" class="text-left pl-3 whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="text-blue-300 font-extrabold tracking-wide">TOTAL {{ $grandTotal->total_se }} SE</span>
                                    <span class="font-normal text-xs text-slate-400 ml-1 font-mono">({{ number_format($grandTotal->total_days) }} HK)</span>
                                </th>

                                {{-- Total Call --}}
                                <th class="text-right whitespace-nowrap font-mono text-xs text-white bg-slate-950 dark:bg-black">{{ number_format($grandTotal->total_pc) }}</th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-blue-200 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->total_ac) }}</th>
                                <th class="text-center whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="badge badge-xs {{ $grandTotal->total_ac_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white' }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $grandTotal->total_ac_percent }}%
                                    </span>
                                </th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-emerald-300 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->total_ec) }}</th>
                                <th class="text-center whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="badge badge-xs {{ $grandTotal->total_ec_percent >= 100 ? 'badge-success text-white' : ($grandTotal->total_ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white') }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $grandTotal->total_ec_percent }}%
                                    </span>
                                </th>

                                {{-- Exclude XC --}}
                                <th class="text-right whitespace-nowrap font-mono text-xs text-slate-300 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->exc_pc) }}</th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-sky-200 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->exc_ac) }}</th>
                                <th class="text-center whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="badge badge-xs {{ $grandTotal->exc_ac_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white' }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $grandTotal->exc_ac_percent }}%
                                    </span>
                                </th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-teal-300 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->exc_ec) }}</th>
                                <th class="text-center whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="badge badge-xs {{ $grandTotal->exc_ec_percent >= 100 ? 'badge-success text-white' : ($grandTotal->exc_ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white') }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $grandTotal->exc_ec_percent }}%
                                    </span>
                                </th>

                                {{-- Just XC --}}
                                <th class="text-right whitespace-nowrap font-mono text-xs text-slate-300 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->just_pc) }}</th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-amber-200 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->just_ac) }}</th>
                                <th class="text-center whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="badge badge-xs {{ $grandTotal->just_ac_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white' }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $grandTotal->just_ac_percent }}%
                                    </span>
                                </th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-amber-300 bg-slate-950 dark:bg-black">{{ number_format($grandTotal->just_ec) }}</th>
                                <th class="text-center whitespace-nowrap bg-slate-950 dark:bg-black">
                                    <span class="badge badge-xs {{ $grandTotal->just_ec_percent >= 100 ? 'badge-success text-white' : ($grandTotal->just_ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white') }} font-bold font-mono px-1 py-0 h-4 min-h-0 text-[0.625rem] leading-none">
                                        {{ $grandTotal->just_ec_percent }}%
                                    </span>
                                </th>

                                {{-- Total Order --}}
                                <th class="text-right whitespace-nowrap font-mono text-xs text-emerald-400 font-extrabold pr-2 bg-slate-950 dark:bg-black">
                                    Rp {{ number_format($grandTotal->total_order_val, 0, ',', '.') }}
                                </th>

                                {{-- Waktu Operasional --}}
                                <th class="text-right whitespace-nowrap font-mono text-xs font-semibold text-slate-100 bg-slate-950 dark:bg-black">{{ $grandTotal->total_jam_toko_str }}</th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-slate-400 font-normal bg-slate-950 dark:bg-black">{{ $grandTotal->avg_jam_toko_str }}</th>
                                <th class="text-right whitespace-nowrap font-mono text-xs font-semibold text-slate-100 bg-slate-950 dark:bg-black">{{ $grandTotal->total_jam_jalan_str }}</th>
                                <th class="text-right whitespace-nowrap font-mono text-xs text-slate-400 font-normal bg-slate-950 dark:bg-black">{{ $grandTotal->avg_jam_jalan_str }}</th>

                                {{-- Aksi --}}
                                <th class="text-center whitespace-nowrap font-mono text-slate-500 bg-slate-950 dark:bg-black">-</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            @endif
        </div>
    </div>
</div>
