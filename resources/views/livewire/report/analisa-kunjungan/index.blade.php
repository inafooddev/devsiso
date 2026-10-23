<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: true,
         autoHidden: false,
         manualExpanded: false,
         isMultiline: false,

         detectMultiline() {
             // 1. Cek media query Tailwind: breakpoint xl adalah min-width 1280px.
             // Di bawah 1280px, Tailwind pasti menerapkan lg:grid-cols-3 (2 baris) atau sm:grid-cols-2 (3 baris).
             const isBelowXl = !window.matchMedia('(min-width: 1280px)').matches;
             if (isBelowXl) return true;

             // 2. Cek elemen fisik DOM jika kartu KPI sedang tampil:
             const grid = document.getElementById('kpi-cards-grid');
             if (grid && grid.children.length > 1) {
                 const firstCard = grid.children[0];
                 const lastCard = grid.children[grid.children.length - 1];
                 if (lastCard.offsetTop > firstCard.offsetTop + 10) {
                     return true;
                 }
             }

             // 3. Cek lebar container pembungkus utama:
             // 5 kartu KPI butuh minimum ~1260px container agar muat 1 baris tanpa berdesakan.
             const containerW = this.$el.getBoundingClientRect().width;
             if (containerW < 1260) {
                 return true;
             }

             return false;
         },

         checkKpiWrap() {
             const multiline = this.detectMultiline();
             this.isMultiline = multiline;

             if (multiline) {
                 // Ketika kartu menjadi 2 baris atau lebih:
                 // Jika user belum secara sengaja memaksa buka di zoom ini, otomatis sembunyikan!
                 if (!this.manualExpanded) {
                     if (this.showKpi) {
                         this.showKpi = false;
                         this.autoHidden = true;
                     }
                 }
             } else {
                 // Ketika kembali cukup 1 baris (zoom out / layar lebar):
                 this.manualExpanded = false;
                 // Jika sebelumnya disembunyikan otomatis oleh auto-hide, kembalikan ke tampil
                 if (this.autoHidden) {
                     this.showKpi = true;
                     this.autoHidden = false;
                 }
             }
         },

         toggleKpi() {
             if (!this.showKpi) {
                 // User secara eksplisit mengklik untuk menampilkan KPI
                 this.showKpi = true;
                 this.autoHidden = false;
                 if (this.isMultiline) {
                     this.manualExpanded = true;
                 }
             } else {
                 // User secara eksplisit menyembunyikan KPI
                 this.showKpi = false;
                 this.autoHidden = false;
                 this.manualExpanded = false;
             }
         },

         init() {
             // Cek saat komponen dimuat
             this.$nextTick(() => {
                 this.checkKpiWrap();
             });

             // Listener resize biasa & zoom
             window.addEventListener('resize', () => this.checkKpiWrap());

             // Listener visualViewport (khusus menangkap browser zoom in/out di Chrome/Edge)
             if (window.visualViewport) {
                 window.visualViewport.addEventListener('resize', () => this.checkKpiWrap());
             }

             // Listener perubahan breakpoint media query 1280px
             const mq = window.matchMedia('(min-width: 1280px)');
             if (mq.addEventListener) {
                 mq.addEventListener('change', () => this.checkKpiWrap());
             } else if (mq.addListener) {
                 mq.addListener(() => this.checkKpiWrap());
             }

             // ResizeObserver pada container
             if (window.ResizeObserver) {
                 new ResizeObserver(() => {
                     this.checkKpiWrap();
                 }).observe(this.$el);
             }
         }
     }">
    <x-slot name="title">Analisa Kunjungan</x-slot>

    @push('styles')
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <style>

        #visit-map {
            height: 400px;
            width: 100%;
            z-index: 10;
        }
        #all-visit-map {
            height: 500px;
            width: 100%;
            z-index: 10;
        }
    </style>
    @endpush

    {{-- TABS --}}
    <x-ui.tab-menu class="mb-3 md:mb-4">
        <button wire:click="setTab('summary')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'summary' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary
        </button>
        <button wire:click="setTab('detail')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'detail' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </button>

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                @if($activeTab === 'detail' && $appliedSupervisor && !empty($dailyData['grandTotal']->total_call))
                    <span class="badge badge-primary text-primary-content font-bold text-xs gap-1 py-2.5 px-3 shadow-2xs font-mono">
                        <x-heroicon-s-clipboard-document-check class="w-4 h-4" />
                        {{ number_format($dailyData['grandTotal']->total_call) }} Call
                    </span>
                @endif

                @if($activeTab === 'detail')
                    <button type="button" 
                        @click="toggleKpi()" 
                        class="btn btn-xs sm:btn-sm rounded-xl gap-1.5 transition-all font-semibold border shadow-2xs cursor-pointer select-none"
                        :class="showKpi ? 'btn-ghost text-base-content/70 hover:text-base-content border-base-300' : 'btn-primary text-white shadow-sm border-primary'"
                        :title="showKpi ? 'Sembunyikan KPI Card agar ruang tabel lebih lega' : (autoHidden ? 'KPI otomatis disembunyikan karena layar zoom/sempit (≥2 baris). Klik untuk menampilkan.' : 'Tampilkan kembali KPI Card')">
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
                                <template x-if="autoHidden">
                                    <span class="badge badge-warning text-slate-900 badge-xs font-bold px-1.5 py-0.5 text-xs hidden sm:inline" title="Otomatis disembunyikan agar layar tidak penuh">
                                        Auto-hide
                                    </span>
                                </template>
                            </span>
                        </template>
                    </button>
                @endif
            </div>
        </x-slot>
    </x-ui.tab-menu>

    @if($activeTab === 'detail')
        <div x-show="showKpi" 
             x-cloak
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 -translate-y-2" 
             x-transition:enter-end="opacity-100 translate-y-0" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100 translate-y-0" 
             x-transition:leave-end="opacity-0 -translate-y-2" 
             class="shrink-0">
            @include('livewire.report.analisa-kunjungan.kpi-cards')
        </div>
    @endif

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
    @php
        $user = auth()->user();
        $canAdd = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_add');
        $canEdit = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_edit');
        $canDelete = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_delete');
        $canExport = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_export');
    @endphp
        @if($activeTab === 'detail')
        {{-- Header Card & Actions Toolbar (Standar Kapsul Hybrid Breadcrumb Analisa Kunjungan) --}}
        <x-ui.filter-toolbar>
            {{-- 1. Kapsul Hierarki Wilayah (Region & Area Native + Supervisor Micro-Searchable Popover) --}}
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

                {{-- Supervisor Segment (Micro-Searchable Popover) --}}
                <x-ui.filter-searchable 
                    wire:key="spv-capsule-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedSupervisor }}"
                    wire:model.live="selectedSupervisor" 
                    :options="$supervisors" 
                    valueField="supervisor_code" 
                    labelField="supervisor_name" 
                    placeholder="Pilih Supervisor" 
                    searchPlaceholder="Cari nama supervisor / kode..." 
                    icon="user" 
                    :disabled="empty($selectedArea) || count($supervisors) === 0" />
            </x-ui.filter-breadcrumb>

            {{-- 2. Kapsul Periode Pintar (Smart Date Range Filter) --}}
            <x-ui.filter-date-range 
                start="startDate" 
                end="endDate" 
                :maxDays="31" />

            <x-slot name="actions">
                @php
                    $canApply = !empty($selectedRegion) && !empty($selectedArea) && !empty($selectedSupervisor);
                    $isMapDisabled = empty($appliedSupervisor);
                    $isExportDisabled = empty($appliedSupervisor);
                    $hasPendingFilters = $selectedRegion !== $appliedRegion || 
                                         $selectedArea !== $appliedArea || 
                                         $selectedSupervisor !== $appliedSupervisor || 
                                         $startDate !== $appliedStartDate || 
                                         $endDate !== $appliedEndDate;
                @endphp
                <x-ui.filter-actions 
                    applyAction="applyFilter" 
                    resetAction="resetFilter" 
                    :canApply="$canApply" 
                    disabledTip="Pilih Region, Area, dan Supervisor terlebih dahulu">
                    
                    {{-- Tombol Buka Peta Sebaran Kunjungan --}}
                    <button type="button" 
                        class="btn btn-sm btn-outline btn-info rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer"
                        wire:click="openAllMapsModal"
                        wire:loading.attr="disabled"
                        wire:target="openAllMapsModal"
                        @disabled($isMapDisabled)
                        title="{{ $isMapDisabled ? 'Terapkan filter terlebih dahulu' : 'Buka Peta Sebaran Kunjungan' }}">
                        <span wire:loading.remove wire:target="openAllMapsModal" class="flex items-center gap-1">
                            <x-heroicon-m-map class="w-4 h-4" />
                            <span class="hidden sm:inline">Peta</span>
                        </span>
                        <span wire:loading wire:target="openAllMapsModal" class="flex items-center gap-1">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span class="hidden sm:inline">Memuat...</span>
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
                                <span class="hidden sm:inline">Excel</span>
                            </span>
                            <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </x-ui.filter-actions>
            </x-slot>
        </x-ui.filter-toolbar>
        @elseif($activeTab === 'summary')
        {{-- Header Card & Actions Toolbar (Standar Kapsul Summary) --}}
        <x-ui.filter-toolbar>
            <x-ui.filter-breadcrumb>
                {{-- Region Segment --}}
                <x-ui.filter-select 
                    wire:model.live="summaryRegion" 
                    :options="$regions" 
                    valueField="region_code" 
                    labelField="region_name" 
                    placeholder="Semua Region" 
                    icon="globe" />

                {{-- Level Multi-Select Dropdown --}}
                <div class="dropdown">
                    <div tabindex="0" role="button" class="flex items-center justify-between gap-1.5 px-2 py-1 text-xs cursor-pointer font-medium hover:bg-base-200/50 rounded-lg min-w-[120px]">
                        <div class="flex items-center gap-1 truncate text-base-content/75">
                            <x-heroicon-m-adjustments-horizontal class="w-3.5 h-3.5 text-primary/70 shrink-0" />
                            <span class="truncate font-medium {{ count($summaryLevels) > 0 ? 'text-primary font-bold' : '' }}">
                                @if(count($summaryLevels) === 0)
                                    Semua Level
                                @else
                                    {{ count($summaryLevels) }} Level
                                @endif
                            </span>
                        </div>
                        <x-heroicon-m-chevron-down class="w-3 h-3 opacity-40 shrink-0" />
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[50] menu p-2 shadow-lg bg-base-100 rounded-xl w-44 mt-1 border border-base-300">
                        <li class="menu-title text-xs uppercase font-bold text-base-content/50 px-2 py-1">Pilih Level</li>
                        <li>
                            <label class="label cursor-pointer justify-start gap-2.5 px-2 py-1">
                                <input type="checkbox" wire:model="summaryLevels" value="region" class="checkbox checkbox-xs checkbox-primary rounded" />
                                <span class="label-text text-xs">Region</span>
                            </label>
                        </li>
                        <li>
                            <label class="label cursor-pointer justify-start gap-2.5 px-2 py-1">
                                <input type="checkbox" wire:model="summaryLevels" value="area" class="checkbox checkbox-xs checkbox-primary rounded" />
                                <span class="label-text text-xs">Area</span>
                            </label>
                        </li>
                        <li>
                            <label class="label cursor-pointer justify-start gap-2.5 px-2 py-1">
                                <input type="checkbox" wire:model="summaryLevels" value="supervisor" class="checkbox checkbox-xs checkbox-primary rounded" />
                                <span class="label-text text-xs">Supervisor</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </x-ui.filter-breadcrumb>

            {{-- Smart Date Range Filter --}}
            <x-ui.filter-date-range 
                start="summaryStartDate" 
                end="summaryEndDate" 
                :maxDays="31" 
                :stepper="false" 
                :presets="false" />

            <x-slot name="actions">
                @php
                    $isSummaryExportDisabled = empty($appliedSummaryRegion) && empty($appliedSummaryStartDate) && empty($appliedSummaryEndDate) && empty($appliedSummaryLevels);
                    $hasPendingSummaryFilters = $summaryRegion !== $appliedSummaryRegion || 
                                                $summaryStartDate !== $appliedSummaryStartDate || 
                                                $summaryEndDate !== $appliedSummaryEndDate ||
                                                $summaryLevels !== $appliedSummaryLevels;
                @endphp
                <x-ui.filter-actions 
                    applyAction="applyFilter" 
                    resetAction="resetFilter" 
                    :canApply="true">
                    
                    @if($canExport)
                        <button type="button" 
                            wire:click="exportSummary" 
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer {{ $isSummaryExportDisabled || $hasPendingSummaryFilters ? 'opacity-50 cursor-not-allowed' : '' }}"
                            @disabled($isSummaryExportDisabled || $hasPendingSummaryFilters)
                            title="{{ $isSummaryExportDisabled || $hasPendingSummaryFilters ? 'Klik Terapkan terlebih dahulu' : 'Export Summary ke Excel' }}">
                            <span wire:loading.remove wire:target="exportSummary" class="flex items-center gap-1.5">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span class="hidden sm:inline">Excel</span>
                            </span>
                            <span wire:loading wire:target="exportSummary" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </x-ui.filter-actions>
            </x-slot>
        </x-ui.filter-toolbar>
        @endif

        @if($activeTab === 'detail')
        {{-- Secondary Filter & Search Toolbar (Modern Compact Popover Pattern) --}}
        @php
            $activeFilterCount = 0;
            if ($filterVisit !== 'all') $activeFilterCount++;
            if ($filterEc !== 'all') $activeFilterCount++;
            if ($filterPjp !== 'all') $activeFilterCount++;
            if ($filterDistance !== 'all') $activeFilterCount++;
            if ($filterReason !== 'all') $activeFilterCount++;
            if ($filterDepo !== 'all') $activeFilterCount++;
        @endphp
        <div class="px-3 md:px-4 lg:px-5 py-2.5 bg-base-100 border-b border-base-200 flex flex-wrap items-center justify-between gap-2.5 text-xs shrink-0">
            {{-- Left Side: Search & Filter Popover --}}
            <div class="flex flex-wrap items-center gap-2 grow sm:grow-0">
                {{-- Search Box --}}
                <div class="relative w-full sm:w-56 md:w-64">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40 pointer-events-none" />
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Cari toko, custno, pilar..." 
                           class="input input-xs sm:input-sm input-bordered w-full pl-9 pr-8 rounded-xl bg-base-200/40 focus:bg-base-100 text-xs border-base-300" />
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content" title="Hapus pencarian">
                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>

                {{-- Filter Rincian Popover --}}
                <div class="relative" x-data="{ openFilter: false }" @click.outside="openFilter = false">
                    <button type="button" 
                            @click="openFilter = !openFilter" 
                            class="btn btn-xs sm:btn-sm rounded-xl gap-1.5 transition-all font-semibold border shadow-2xs select-none cursor-pointer"
                            :class="openFilter ? 'btn-primary text-white border-primary shadow-sm' : '{{ $activeFilterCount > 0 ? 'btn-primary text-white border-primary shadow-sm' : 'btn-ghost text-base-content/75 hover:text-base-content border-base-300 bg-base-100' }}'">
                        <x-heroicon-m-funnel class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" />
                        <span>Filter Rincian</span>
                        @if($activeFilterCount > 0)
                            <span class="badge badge-xs font-bold rounded-full px-1.5 py-0.5 bg-white text-primary shrink-0">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                        <span :class="openFilter ? 'rotate-180' : ''" class="transition-transform duration-200 shrink-0 inline-flex items-center">
                            <x-heroicon-m-chevron-down class="w-3 h-3 opacity-60" />
                        </span>
                    </button>

                    {{-- Popover Card --}}
                    <div x-show="openFilter" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute left-0 top-full mt-2 w-[320px] sm:w-[480px] bg-base-100 rounded-2xl shadow-2xl border border-base-300 p-4 z-50 divide-y divide-base-200"
                         style="display: none;">
                        
                        {{-- Popover Header --}}
                        <div class="flex items-center justify-between pb-3">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                                    <x-heroicon-m-adjustments-horizontal class="w-4 h-4" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-base-content">Filter Rincian Kunjungan</h4>
                                    <p class="text-xs text-base-content/60">Saring toko berdasarkan status & kepatuhan rute</p>
                                </div>
                            </div>
                            @if($activeFilterCount > 0)
                                <button type="button" 
                                        wire:click="resetDetailFilters" 
                                        class="text-xs font-semibold text-error hover:underline flex items-center gap-1 cursor-pointer">
                                    <x-heroicon-m-arrow-path class="w-3 h-3" />
                                    Reset Semua
                                </button>
                            @endif
                        </div>

                        {{-- Popover Body: Grid 2 Kolom --}}
                        <div class="py-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            {{-- 1. Status Visit --}}
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-base-content/75 flex items-center gap-1">
                                    <span>Status Kunjungan</span>
                                </label>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-base-200/70 rounded-lg">
                                    <button type="button" wire:click="$set('filterVisit', 'all')" class="btn btn-xs rounded-md border-0 {{ $filterVisit === 'all' ? 'bg-base-100 shadow-2xs font-bold text-primary' : 'btn-ghost text-base-content/70 font-normal' }}">Semua</button>
                                    <button type="button" wire:click="$set('filterVisit', 'Y')" class="btn btn-xs rounded-md border-0 {{ $filterVisit === 'Y' ? 'bg-base-100 shadow-2xs font-bold text-primary' : 'btn-ghost text-base-content/70 font-normal' }}">Visit (Y)</button>
                                    <button type="button" wire:click="$set('filterVisit', 'N')" class="btn btn-xs rounded-md border-0 {{ $filterVisit === 'N' ? 'bg-base-100 shadow-2xs font-bold text-primary' : 'btn-ghost text-base-content/70 font-normal' }}">Belum (N)</button>
                                </div>
                            </div>

                            {{-- 2. Status EC / Order --}}
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-base-content/75 flex items-center gap-1">
                                    <span>Status Order (EC)</span>
                                </label>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-base-200/70 rounded-lg">
                                    <button type="button" wire:click="$set('filterEc', 'all')" class="btn btn-xs rounded-md border-0 {{ $filterEc === 'all' ? 'bg-base-100 shadow-2xs font-bold text-success' : 'btn-ghost text-base-content/70 font-normal' }}">Semua</button>
                                    <button type="button" wire:click="$set('filterEc', 'Y')" class="btn btn-xs rounded-md border-0 {{ $filterEc === 'Y' ? 'bg-base-100 shadow-2xs font-bold text-success' : 'btn-ghost text-base-content/70 font-normal' }}">Order (Y)</button>
                                    <button type="button" wire:click="$set('filterEc', 'N')" class="btn btn-xs rounded-md border-0 {{ $filterEc === 'N' ? 'bg-base-100 shadow-2xs font-bold text-success' : 'btn-ghost text-base-content/70 font-normal' }}">Non-EC (N)</button>
                                </div>
                            </div>

                            {{-- 3. Tipe Rute (PJP) --}}
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-base-content/75 flex items-center gap-1">
                                    <span>Tipe Rute</span>
                                </label>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-base-200/70 rounded-lg">
                                    <button type="button" wire:click="$set('filterPjp', 'all')" class="btn btn-xs rounded-md border-0 {{ $filterPjp === 'all' ? 'bg-base-100 shadow-2xs font-bold text-info' : 'btn-ghost text-base-content/70 font-normal' }}">Semua</button>
                                    <button type="button" wire:click="$set('filterPjp', 'Y')" class="btn btn-xs rounded-md border-0 {{ $filterPjp === 'Y' ? 'bg-base-100 shadow-2xs font-bold text-info' : 'btn-ghost text-base-content/70 font-normal' }}">PJP</button>
                                    <button type="button" wire:click="$set('filterPjp', 'N')" class="btn btn-xs rounded-md border-0 {{ $filterPjp === 'N' ? 'bg-base-100 shadow-2xs font-bold text-info' : 'btn-ghost text-base-content/70 font-normal' }}">Extra Call</button>
                                </div>
                            </div>

                            {{-- 4. Kepatuhan Radius --}}
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-base-content/75 flex items-center gap-1">
                                    <span>Radius Kunjungan</span>
                                </label>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-base-200/70 rounded-lg">
                                    <button type="button" wire:click="$set('filterDistance', 'all')" class="btn btn-xs rounded-md border-0 {{ $filterDistance === 'all' ? 'bg-base-100 shadow-2xs font-bold text-primary' : 'btn-ghost text-base-content/70 font-normal' }}">Semua</button>
                                    <button type="button" wire:click="$set('filterDistance', 'within')" class="btn btn-xs rounded-md border-0 {{ $filterDistance === 'within' ? 'bg-base-100 shadow-2xs font-bold text-success' : 'btn-ghost text-base-content/70 font-normal' }}">&le; 50m</button>
                                    <button type="button" wire:click="$set('filterDistance', 'out')" class="btn btn-xs rounded-md border-0 {{ $filterDistance === 'out' ? 'bg-base-100 shadow-2xs font-bold text-warning' : 'btn-ghost text-base-content/70 font-normal' }}">> 50m</button>
                                </div>
                            </div>

                            {{-- 5. Alasan / Reason --}}
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-base-content/75 flex items-center gap-1">
                                    <span>Alasan (Reason)</span>
                                </label>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-base-200/70 rounded-lg">
                                    <button type="button" wire:click="$set('filterReason', 'all')" class="btn btn-xs rounded-md border-0 {{ $filterReason === 'all' ? 'bg-base-100 shadow-2xs font-bold text-secondary' : 'btn-ghost text-base-content/70 font-normal' }}">Semua</button>
                                    <button type="button" wire:click="$set('filterReason', 'with')" class="btn btn-xs rounded-md border-0 {{ $filterReason === 'with' ? 'bg-base-100 shadow-2xs font-bold text-secondary' : 'btn-ghost text-base-content/70 font-normal' }}">Ada Reason</button>
                                    <button type="button" wire:click="$set('filterReason', 'without')" class="btn btn-xs rounded-md border-0 {{ $filterReason === 'without' ? 'bg-base-100 shadow-2xs font-bold text-secondary' : 'btn-ghost text-base-content/70 font-normal' }}">Tanpa</button>
                                </div>
                            </div>

                            {{-- 6. Lokasi Depo --}}
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-base-content/75 flex items-center gap-1">
                                    <span>Lokasi Depo (&le; 100m)</span>
                                </label>
                                <div class="grid grid-cols-3 gap-1 p-0.5 bg-base-200/70 rounded-lg">
                                    <button type="button" wire:click="$set('filterDepo', 'all')" class="btn btn-xs rounded-md border-0 {{ $filterDepo === 'all' ? 'bg-base-100 shadow-2xs font-bold text-primary' : 'btn-ghost text-base-content/70 font-normal' }}">Semua</button>
                                    <button type="button" wire:click="$set('filterDepo', 'at_depo')" class="btn btn-xs rounded-md border-0 {{ $filterDepo === 'at_depo' ? 'bg-base-100 shadow-2xs font-bold text-error' : 'btn-ghost text-base-content/70 font-normal' }}">🏢 Di Depo</button>
                                    <button type="button" wire:click="$set('filterDepo', 'not_depo')" class="btn btn-xs rounded-md border-0 {{ $filterDepo === 'not_depo' ? 'bg-base-100 shadow-2xs font-bold text-success' : 'btn-ghost text-base-content/70 font-normal' }}">🏬 Luar Depo</button>
                                </div>
                            </div>
                        </div>

                        {{-- Popover Footer --}}
                        <div class="pt-3 flex items-center justify-between">
                            <span class="text-xs text-base-content/60">
                                @if($activeFilterCount > 0)
                                    <strong class="text-primary font-bold">{{ $activeFilterCount }}</strong> filter aktif
                                @else
                                    Semua filter aktif secara default
                                @endif
                            </span>
                            <button type="button" 
                                    @click="openFilter = false" 
                                    class="btn btn-xs btn-primary rounded-xl px-3 cursor-pointer">
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Active Filter Pills / Chips --}}
                <div class="hidden md:flex items-center flex-wrap gap-1.5">
                    @if($filterVisit !== 'all')
                        <span class="badge badge-sm badge-outline badge-primary gap-1 py-2 text-xs font-medium rounded-lg">
                            Visit: {{ $filterVisit === 'Y' ? 'Ya' : 'Belum' }}
                            <button type="button" wire:click="$set('filterVisit', 'all')" class="hover:text-error cursor-pointer" title="Hapus filter visit">
                                <x-heroicon-m-x-mark class="w-3 h-3" />
                            </button>
                        </span>
                    @endif

                    @if($filterEc !== 'all')
                        <span class="badge badge-sm badge-outline badge-success gap-1 py-2 text-xs font-medium rounded-lg">
                            Order: {{ $filterEc === 'Y' ? 'EC' : 'Non-EC' }}
                            <button type="button" wire:click="$set('filterEc', 'all')" class="hover:text-error cursor-pointer" title="Hapus filter order">
                                <x-heroicon-m-x-mark class="w-3 h-3" />
                            </button>
                        </span>
                    @endif

                    @if($filterPjp !== 'all')
                        <span class="badge badge-sm badge-outline badge-info gap-1 py-2 text-xs font-medium rounded-lg">
                            Rute: {{ $filterPjp === 'Y' ? 'PJP' : 'Extra' }}
                            <button type="button" wire:click="$set('filterPjp', 'all')" class="hover:text-error cursor-pointer" title="Hapus filter rute">
                                <x-heroicon-m-x-mark class="w-3 h-3" />
                            </button>
                        </span>
                    @endif

                    @if($filterDistance !== 'all')
                        <span class="badge badge-sm badge-outline {{ $filterDistance === 'within' ? 'badge-success' : 'badge-warning' }} gap-1 py-2 text-xs font-medium rounded-lg">
                            Radius: {{ $filterDistance === 'within' ? '≤50m' : '>50m' }}
                            <button type="button" wire:click="$set('filterDistance', 'all')" class="hover:text-error cursor-pointer" title="Hapus filter radius">
                                <x-heroicon-m-x-mark class="w-3 h-3" />
                            </button>
                        </span>
                    @endif

                    @if($filterReason !== 'all')
                        <span class="badge badge-sm badge-outline badge-secondary gap-1 py-2 text-xs font-medium rounded-lg">
                            Reason: {{ $filterReason === 'with' ? 'Ada' : 'Tanpa' }}
                            <button type="button" wire:click="$set('filterReason', 'all')" class="hover:text-error cursor-pointer" title="Hapus filter reason">
                                <x-heroicon-m-x-mark class="w-3 h-3" />
                            </button>
                        </span>
                    @endif

                    @if($filterDepo !== 'all')
                        <span class="badge badge-sm badge-outline {{ $filterDepo === 'at_depo' ? 'badge-error' : 'badge-neutral' }} gap-1 py-2 text-xs font-medium rounded-lg">
                            Depo: {{ $filterDepo === 'at_depo' ? 'Di Depo' : 'Luar Depo' }}
                            <button type="button" wire:click="$set('filterDepo', 'all')" class="hover:text-error cursor-pointer" title="Hapus filter depo">
                                <x-heroicon-m-x-mark class="w-3 h-3" />
                            </button>
                        </span>
                    @endif

                    @if($activeFilterCount > 1)
                        <button type="button" wire:click="resetDetailFilters" class="text-xs text-error hover:underline ml-1 cursor-pointer font-medium" title="Reset semua filter rincian">
                            Reset Semua
                        </button>
                    @endif
                </div>
            </div>

            {{-- Right Side: Expand / Collapse All Controls --}}
            <div class="flex items-center gap-1.5 ml-auto">
                <div class="join border border-base-300 rounded-xl overflow-hidden shadow-2xs bg-base-100">
                    <button type="button" 
                            @click="expandAll" 
                            class="join-item btn btn-xs btn-ghost hover:bg-base-200/80 gap-1 font-semibold text-base-content/80 text-xs px-2.5 h-8 cursor-pointer" 
                            title="Buka rincian semua hari">
                        <x-heroicon-m-arrows-pointing-out class="w-3.5 h-3.5 text-primary" />
                        <span class="hidden sm:inline">Buka Semua</span>
                    </button>
                    <div class="w-px bg-base-300 h-5 self-center"></div>
                    <button type="button" 
                            @click="collapseAll" 
                            class="join-item btn btn-xs btn-ghost hover:bg-base-200/80 gap-1 font-medium text-base-content/60 hover:text-base-content text-xs px-2.5 h-8 cursor-pointer" 
                            title="Tutup rincian semua hari">
                        <x-heroicon-m-arrows-pointing-in class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Tutup Semua</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if($activeTab === 'detail')
        {{-- Table Container: Master-Detail (Summary Per Hari & Expand Toko) --}}
        <div class="overflow-x-auto min-h-0 flex-1 relative bg-base-100">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap text-xs">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:py-2.5 [&_th]:px-2.5">
                    <tr class="[&>th]:align-middle">
                        <th class="w-12 text-center">No</th>
                        <th>Tanggal & Hari</th>
                        <th class="text-center">Jam Kerja</th>
                        <th class="text-center">Plan Call</th>
                        <th class="text-center">Actual Call</th>
                        <th class="text-center">Effective Call (EC)</th>
                        <th class="text-right">Target (Rp)</th>
                        <th class="text-right">Total Order (Rp)</th>
                        <th class="text-center">Waktu Toko vs Jalan</th>
                        <th class="text-center">Kepatuhan Radius</th>
                    </tr>
                </thead>
                <tbody class="text-xs relative" wire:loading.class="opacity-50 pointer-events-none transition-opacity duration-200" wire:target="applyFilter, resetFilter, search, filterVisit, filterEc, filterPjp, filterDistance, filterReason, filterDepo, resetDetailFilters">
                @php
                    $summaries = $dailyData['summaries'] ?? collect();
                @endphp
                @forelse($summaries as $day)
                    {{-- Master Row: Summary Harian (Clickable to Expand/Collapse) --}}
                    <tr @click="toggle('{{ $day->tanggal }}')" 
                        class="hover:bg-primary/5 transition-colors cursor-pointer border-b border-base-200 select-none [&_td]:py-2.5 [&_td]:px-2.5" 
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
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 leading-none">Sabtu</span>
                                @endif
                                @if(($day->at_depo_count ?? 0) > 0)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30 leading-none" title="{{ $day->at_depo_count }} kunjungan terdeteksi di kantor Depo / Distributor">
                                        🏢 {{ $day->at_depo_count }} Depo
                                    </span>
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
                                <span class="block text-xs text-base-content/50 font-normal">({{ $day->work_duration_str }})</span>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>

                        {{-- Plan Call --}}
                        <td class="text-center whitespace-nowrap">
                            <span class="font-mono font-bold text-xs">{{ $day->total_call }} Toko</span>
                            <span class="text-xs text-base-content/50 block font-mono">{{ $day->pc }} PC • {{ $day->xc }} XC</span>
                        </td>

                        {{-- Actual Call --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="font-mono font-semibold text-xs">{{ $day->total_visit }} / {{ $day->total_call }}</span>
                                @php
                                    $vClass = $day->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white';
                                @endphp
                                <span class="badge badge-xs {{ $vClass }} font-bold font-mono px-1.5 text-xs leading-none">
                                    {{ $day->visit_percent }}%
                                </span>
                            </div>
                        </td>

                        {{-- Effective Call (EC) --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="font-mono font-semibold text-xs">{{ $day->total_ec }} Toko</span>
                                @php
                                    $ecP = $day->ec_percent;
                                    $ecClass = $ecP >= 100 ? 'badge-success text-white' : ($ecP >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white');
                                @endphp
                                <span class="badge badge-xs {{ $ecClass }} font-bold font-mono px-1.5 text-xs leading-none">
                                    {{ $day->ec_percent }}%
                                </span>
                            </div>
                        </td>

                        {{-- Target (Rp) --}}
                        <td class="text-right whitespace-nowrap font-mono text-xs text-base-content/80">
                            Rp {{ number_format($day->total_target, 0, ',', '.') }}
                        </td>

                        {{-- Total Order (Rp) --}}
                        <td class="text-right whitespace-nowrap font-mono text-xs">
                            @if($day->total_val_order > 0)
                                <span class="text-primary font-bold">Rp {{ number_format($day->total_val_order, 0, ',', '.') }}</span>
                            @else
                                <span class="text-base-content/30">Rp 0</span>
                            @endif
                        </td>

                        {{-- Waktu Toko vs Jalan --}}
                        <td class="text-center whitespace-nowrap">
                            <div class="inline-flex flex-col items-center gap-1 font-mono text-xs w-full max-w-[155px]">
                                <div class="inline-flex items-center justify-center gap-1 w-full text-xs">
                                    <span class="badge badge-xs badge-ghost border-base-300 gap-0.5 text-xs px-1.5 py-0.5" title="Waktu di Toko: {{ $day->consume_percent }}%">
                                        🏬 {{ $day->consume_str }}
                                    </span>
                                    <span class="badge badge-xs badge-ghost border-base-300 gap-0.5 text-xs px-1.5 py-0.5" title="Waktu di Jalan: {{ $day->travel_percent }}%">
                                        🛵 {{ $day->travel_str }}
                                    </span>
                                </div>
                                <div class="w-full bg-amber-500/20 rounded-full h-1.5 flex overflow-hidden shadow-2xs" title="Proporsi Waktu: {{ $day->consume_percent }}% di Toko, {{ $day->travel_percent }}% di Jalan">
                                    <div class="bg-success h-full transition-all duration-300" style="width: {{ $day->consume_percent }}%"></div>
                                    <div class="bg-amber-500 h-full transition-all duration-300" style="width: {{ $day->travel_percent }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Kepatuhan Radius --}}
                        <td class="text-center whitespace-nowrap">
                            @if($day->out_of_radius_count == 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-success/10 text-success border border-success/30">
                                    ✓ Sesuai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-error text-white shadow-2xs" title="{{ $day->out_of_radius_count }} toko check-in melebihi 50 meter dari master">
                                    ⚠️ {{ $day->out_of_radius_count }} Toko >50m
                                </span>
                            @endif
                        </td>
                    </tr>

                    {{-- Detail Row: Sub-Tabel Kunjungan Toko (Expandable) --}}
                    <tr x-show="isExpanded('{{ $day->tanggal }}')" x-cloak class="bg-base-200/40">
                        <td colspan="10" class="p-2 sm:p-3">
                            <div class="bg-base-100 rounded-xl border border-base-300 shadow-md overflow-hidden">
                                {{-- Sub-Table Header Bar --}}
                                <div class="px-3.5 py-2 bg-slate-900 text-slate-100 flex flex-wrap items-center justify-between gap-2.5 border-b border-slate-700">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></span>
                                        <span class="font-bold text-xs tracking-wide text-white">Detail Kunjungan: {{ $day->day_name }}, {{ $day->formatted_date }}</span>
                                        <div class="flex items-center gap-1.5 flex-wrap text-xs">
                                            <span class="badge badge-sm badge-neutral font-mono font-bold bg-slate-800 text-slate-200 border border-slate-700">{{ count($day->items) }} Toko</span>
                                            <span class="badge badge-sm {{ $day->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white' }} font-bold font-mono">
                                                {{ $day->total_visit }} Visit ({{ $day->visit_percent }}%)
                                            </span>
                                            <span class="badge badge-sm {{ $day->ec_percent >= 100 ? 'badge-success text-white' : ($day->ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white') }} font-bold font-mono">
                                                {{ $day->total_ec }} EC ({{ $day->ec_percent }}%)
                                            </span>
                                            @if($day->total_target > 0)
                                                <span class="badge badge-sm bg-sky-500/20 text-sky-300 border border-sky-500/30 font-mono font-bold">
                                                    Target: Rp {{ number_format($day->total_target, 0, ',', '.') }}
                                                </span>
                                            @endif
                                            @if($day->total_val_order > 0)
                                                <span class="badge badge-sm bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-mono font-bold">
                                                    Order: Rp {{ number_format($day->total_val_order, 0, ',', '.') }}
                                                </span>
                                            @endif
                                            @if($day->out_of_radius_count > 0)
                                                <span class="badge badge-sm badge-error text-white font-bold" title="{{ $day->out_of_radius_count }} toko rute PJP di luar radius 50m">
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

                                {{-- Sub-Table Body --}}
                                <div class="overflow-x-auto max-h-[420px]">
                                    <table class="table table-xs table-zebra w-full whitespace-nowrap text-xs leading-tight [&_th]:py-1.5 [&_th]:px-2 [&_td]:py-1 [&_td]:px-2">
                                        <thead class="sticky top-0 z-20 shadow-sm border-b border-slate-700 uppercase font-bold tracking-wider text-xs text-white [&_th]:bg-slate-800 dark:[&_th]:bg-slate-900 [&_th]:text-slate-100 [&_th]:border-b [&_th]:border-slate-700">
                                            <tr>
                                                <th class="w-8 text-center">#</th>
                                                <th class="text-center">Jam Kerja</th>
                                                <th class="text-center">Durasi Toko</th>
                                                <th class="text-center">Durasi Jalan</th>
                                                <th class="text-center">Pause</th>
                                                <th>Kode Toko</th>
                                                <th>Nama Toko / Outlet</th>
                                                <th>Pilar</th>
                                                <th class="text-right">Target</th>
                                                <th class="text-center">PJP</th>
                                                <th class="text-center">Visit</th>
                                                <th class="text-center">Order</th>
                                                <th class="text-right">Nilai Order</th>
                                                <th class="text-center">Jarak Master</th>
                                                <th class="text-center">Reason</th>
                                                <th class="text-center">Remark</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-xs">
                                            @foreach($day->items as $item)
                                            <tr class="hover:bg-primary/5 transition-colors border-b border-base-200/60">
                                                <td class="font-mono text-center text-base-content/50">{{ $item->row_num }}</td>
                                                <td class="text-center font-mono font-medium">
                                                    {{ $item->time_in_fmt }} - {{ $item->time_out_fmt }}
                                                </td>
                                                <td class="text-center font-mono font-medium text-base-content/80">
                                                    🏬 {{ $item->time_consume_human }}
                                                </td>
                                                <td class="text-center font-mono text-base-content/60">
                                                    🛵 {{ $item->time_travel_human }}
                                                </td>
                                                <td class="text-center font-mono text-base-content/50">
                                                    {{ $item->time_pause_human !== '-' ? $item->time_pause_human : '-' }}
                                                </td>
                                                <td class="font-mono text-base-content/70">{{ $item->custno }}</td>
                                                <td class="max-w-[200px] truncate font-medium {{ $item->flag_pjp == 'N' ? 'text-error font-bold' : 'text-base-content' }}" title="{{ $item->custname }}">
                                                    {{ $item->custname }}
                                                </td>
                                                <td class="whitespace-nowrap">{{ $item->pilar ?? '-' }}</td>
                                                <td class="text-right font-mono">{{ number_format($item->target ?? 0, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    @if(in_array($item->flag_pjp, ['R', 'Y']))
                                                        <span class="badge badge-xs badge-success text-white font-mono font-bold px-1.5" title="Sesuai Jadwal PJP (Plan Call)">PJP</span>
                                                    @else
                                                        <span class="badge badge-xs badge-warning text-slate-900 font-mono font-bold px-1.5" title="Extra Call (Luar Jadwal PJP)">XC</span>
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
                                                    <button type="button" x-data 
                                                        @click="$dispatch('open-map-modal', {
                                                            masterLat: '{{ $item->master_lat }}',
                                                            masterLon: '{{ $item->master_lon }}',
                                                            visitLat: '{{ $item->visit_lat }}',
                                                            visitLon: '{{ $item->visit_lon }}',
                                                            depoLat: '{{ $item->depo_lat }}',
                                                            depoLon: '{{ $item->depo_lon }}',
                                                            depoName: '{{ addslashes($item->depo_name ?? '') }}',
                                                            distToDepo: '{{ $item->dist_to_depo }}',
                                                            isAtDepo: {{ $item->is_at_depo ? 'true' : 'false' }},
                                                            isBriefingEva: {{ $item->is_briefing_eva ? 'true' : 'false' }},
                                                            custName: '{{ addslashes($item->custname ?? '') }}'
                                                        })" 
                                                        class="btn btn-xs rounded-xl {{ $distM > 50 ? 'btn-error text-white border-none shadow-sm shadow-error/50' : 'btn-outline btn-info' }}"
                                                        title="Jarak Visit ke Master: {{ $distStr }}. Klik untuk buka peta lokasi.">
                                                        {{ $distStr }}
                                                    </button>

                                                    @if(!$item->is_briefing_eva)
                                                        @if($item->is_at_depo)
                                                            <div class="mt-0.5">
                                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30 whitespace-nowrap leading-none" 
                                                                      title="Check-in di kantor depo {{ $item->depo_name }} ({{ $item->dist_to_depo }}m dari depo)">
                                                                    🏢 Depo ({{ $item->dist_to_depo }}m)
                                                                </span>
                                                            </div>
                                                        @elseif(!empty($item->dist_to_depo) && $item->dist_to_depo <= 300)
                                                            <div class="mt-0.5">
                                                                <span class="text-xs text-amber-600 font-mono block whitespace-nowrap leading-none" title="Dekat Depo {{ $item->depo_name }} ({{ $item->dist_to_depo }}m)">
                                                                    🏢 {{ $item->dist_to_depo }}m
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="mt-0.5">
                                                            <span class="text-xs text-base-content/50 font-medium block whitespace-nowrap leading-none" title="Briefing/Evaluasi di Kantor Depo {{ $item->depo_name ?? '' }}">
                                                                🏢 Distributor
                                                            </span>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($item->reason_type || $item->reason_desc)
                                                        <button type="button" x-data="{{ json_encode(['type' => $item->reason_type, 'desc' => $item->reason_desc]) }}" @click="$dispatch('open-reason-modal', {type: type, desc: desc})" class="btn btn-xs btn-outline btn-warning rounded-xl">Detail</button>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-center whitespace-nowrap">
                                                    @if($item->action_remark)
                                                        <div class="flex items-center justify-center gap-1">
                                                            @if($canEdit)
                                                            <button type="button" x-data="{{ json_encode(['remark' => $item->action_remark]) }}" @click="$wire.openRemarkModal('{{ $item->id }}', '{{ $item->supervisor_code }}', '{{ $item->custno }}', '{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}', remark)" class="btn btn-xs btn-ghost text-info p-0 w-6 h-6 rounded-full" title="Lihat/Edit Remark" wire:loading.attr="disabled" wire:target="deleteRemark('{{ $item->id }}', '{{ $item->supervisor_code }}', '{{ $item->custno }}', '{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}')"><x-heroicon-s-eye class="w-4 h-4" /></button>
                                                            @endif
                                                            @if($canDelete)
                                                            <button type="button" wire:click="deleteRemark('{{ $item->id }}', '{{ $item->supervisor_code }}', '{{ $item->custno }}', '{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}')" wire:confirm="Yakin ingin menghapus remark ini?" class="btn btn-xs btn-ghost text-error p-0 w-6 h-6 rounded-full" title="Hapus Remark" wire:loading.attr="disabled">
                                                                <x-heroicon-s-trash class="w-4 h-4" wire:loading.remove wire:target="deleteRemark('{{ $item->id }}', '{{ $item->supervisor_code }}', '{{ $item->custno }}', '{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}')" />
                                                                <span class="loading loading-spinner w-4 h-4" wire:loading wire:target="deleteRemark('{{ $item->id }}', '{{ $item->supervisor_code }}', '{{ $item->custno }}', '{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}')"></span>
                                                            </button>
                                                            @endif
                                                        </div>
                                                    @else
                                                        @if($canAdd)
                                                        <button type="button" wire:click="openRemarkModal('{{ $item->id }}', '{{ $item->supervisor_code }}', '{{ $item->custno }}', '{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}', '')" class="btn btn-xs btn-ghost text-base-content/40 hover:text-primary p-0 w-6 h-6 rounded-full" title="Isi Remark"><x-heroicon-s-pencil class="w-4 h-4" /></button>
                                                        @endif
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
                        <td colspan="10" class="text-center py-12 text-base-content/40">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <x-heroicon-o-inbox class="w-10 h-10" />
                                <p class="text-sm font-medium">Tidak ada data kunjungan ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>

                {{-- Grand Total Footer across all days --}}
                @if(!empty($dailyData['grandTotal']))
                @php
                    $gt = $dailyData['grandTotal'];
                @endphp
                <tfoot class="bg-base-300 font-bold text-xs border-t-2 border-base-content/20 [&_td]:py-2.5 [&_td]:px-2.5">
                    <tr>
                        <td colspan="2" class="text-left font-bold text-xs uppercase tracking-wider">
                            Total {{ $gt->total_days }} Hari
                        </td>
                        <td class="text-center font-mono text-xs">
                            <span class="text-base-content/70">Rata-rata:</span><br>
                            <span>{{ $gt->avg_work_str }}</span>
                        </td>
                        <td class="text-center font-mono text-xs">
                            <span>{{ $gt->total_call }} Toko</span><br>
                            <span class="text-xs text-base-content/50">{{ $gt->pc }} PC • {{ $gt->xc }} XC</span>
                        </td>
                        <td class="text-center font-mono text-xs">
                            <div class="flex items-center justify-center gap-1">
                                <span>{{ $gt->total_visit }}</span>
                                @if(($gt->at_depo_count ?? 0) > 0)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30 leading-none" title="{{ $gt->at_depo_count }} kunjungan terdeteksi di kantor Depo">
                                        🏢 {{ $gt->at_depo_count }} Depo
                                    </span>
                                @endif
                            </div>
                            <span class="badge badge-xs {{ $gt->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white' }} font-bold text-xs">
                                {{ $gt->visit_percent }}%
                            </span>
                        </td>
                        <td class="text-center font-mono text-xs">
                            <span>{{ $gt->total_ec }}</span><br>
                            <span class="badge badge-xs {{ $gt->ec_percent >= 100 ? 'badge-success text-white' : ($gt->ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white') }} font-bold text-xs">
                                {{ $gt->ec_percent }}%
                            </span>
                        </td>
                        <td class="text-right font-mono text-xs text-base-content/80">
                            Rp {{ number_format($gt->total_target, 0, ',', '.') }}
                        </td>
                        <td class="text-right font-mono text-xs text-primary font-bold">
                            Rp {{ number_format($gt->total_val_order, 0, ',', '.') }}
                        </td>
                        <td class="text-center font-mono text-xs">
                            <div class="inline-flex flex-col items-center gap-1 font-mono text-xs w-full max-w-[155px]">
                                <div class="inline-flex items-center justify-center gap-1 w-full text-xs">
                                    <span class="badge badge-xs badge-ghost border-base-300 text-xs px-1.5 py-0.5">
                                        🏬 {{ $gt->consume_str }}
                                    </span>
                                    <span class="badge badge-xs badge-ghost border-base-300 text-xs px-1.5 py-0.5">
                                        🛵 {{ $gt->travel_str }}
                                    </span>
                                </div>
                                <div class="w-full bg-amber-500/20 rounded-full h-1.5 flex overflow-hidden shadow-2xs">
                                    <div class="bg-success h-full" style="width: {{ $gt->consume_percent }}%"></div>
                                    <div class="bg-amber-500 h-full" style="width: {{ $gt->travel_percent }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($gt->out_of_radius_count == 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-success/10 text-success border border-success/30">
                                    ✓ 100% Sesuai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-error text-white shadow-2xs">
                                    ⚠️ {{ $gt->out_of_radius_count }} Toko >50m
                                </span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        <div class="p-2 md:p-3 border-t border-base-300 bg-base-200/30 shrink-0 flex flex-wrap items-center justify-between gap-2 text-xs text-base-content/60">
            @if(empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor))
                <div class="text-center w-full italic py-1">
                    Gunakan filter untuk menampilkan data analisa kunjungan.
                </div>
            @else
                <div class="flex items-center gap-2">
                    <span>Menampilkan <strong>{{ count($summaries) }}</strong> hari kunjungan (<strong>{{ $summaries->sum('total_call') }}</strong> total toko)</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="expandAll" class="btn btn-2xs btn-outline btn-primary rounded-lg font-semibold">
                        Buka Semua Hari
                    </button>
                    <button type="button" @click="collapseAll" class="btn btn-2xs btn-ghost rounded-lg text-base-content/70">
                        Tutup Semua Hari
                    </button>
                </div>
            @endif
        </div>
        @elseif($activeTab === 'summary')
        {{-- Summary Table Container --}}
        <div class="overflow-x-auto min-h-0 flex-1 relative">
            
            {{-- Loading Overlay --}}
            <div wire:loading wire:target="applyFilter, resetFilter, setTab, exportSummary" class="absolute inset-0 bg-base-100/50 backdrop-blur-sm z-50 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 bg-base-100 p-4 rounded-xl shadow-xl border border-base-300">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/70">Memuat Data...</span>
                </div>
            </div>

            <table class="table table-xs table-pin-rows w-full text-xs [&_th]:px-2 [&_td]:px-2 [&_th]:py-1.5 [&_td]:py-1.5">
                <thead class="bg-base-200 text-base-content z-10 sticky top-0 text-xs">
                    <tr>
                        <th class="align-middle text-center" rowspan="2">No</th>
                        <th class="align-middle" rowspan="2">Region</th>
                        <th class="align-middle" rowspan="2">Area</th>
                        <th class="align-middle" rowspan="2">Level</th>
                        <th class="align-middle border-r border-base-300" rowspan="2">Supervisor</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300" colspan="5">Kunjungan (Visit)</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300 bg-base-200/50" colspan="3">Order (Value)</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300" colspan="8">Pilar Actual</th>
                        <th class="align-middle text-center border-b-0 border-base-300 bg-base-200/50" colspan="2">Out of Area <span class="text-xs font-normal opacity-75">(> 50m)</span></th>
                    </tr>
                    <tr>
                        <th class="align-middle text-center bg-base-200/90">PC</th>
                        <th class="align-middle text-center bg-base-200/90">AC</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90">EC</th>
                        <th class="align-middle text-center bg-base-200/90 border-r border-base-300">%</th>
                        <th class="align-middle text-center bg-base-200/70">Target</th>
                        <th class="align-middle text-center bg-base-200/70">Order</th>
                        <th class="align-middle text-center bg-base-200/70 border-r border-base-300">%</th>
                        <th class="align-middle text-center bg-base-200/90">RWO</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90">PNR</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90">NGVO</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90" title="RWO + PNR + NGVO">Pareto</th>
                        <th class="align-middle text-center bg-base-200/90 border-r border-base-300">%</th>
                        <th class="align-middle text-center bg-base-200/70">Toko</th>
                        <th class="align-middle text-center bg-base-200/70">%</th>
                    </tr>
                </thead>
                <tbody class="text-xs relative">
                @php
                    $sumData = $this->summaryData;
                    
                    $totPc = 0; $totAc = 0; $totEc = 0; $totTarget = 0; $totOrder = 0;
                    $totRwo = 0; $totPnr = 0; $totNgvo = 0; $totOoa = 0;
                @endphp
                @forelse($sumData as $i => $row)
                    @php
                        $totPc += $row['pc'];
                        $totAc += $row['ac'];
                        $totEc += $row['ec'];
                        $totTarget += $row['target'];
                        $totOrder += $row['order'];
                        $totRwo += $row['rwo'];
                        $totPnr += $row['pnr'];
                        $totNgvo += $row['ngvo'];
                        $totOoa += $row['out_of_area'];
                    @endphp
                    <tr class="hover">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row['region_name'] }}</td>
                        <td>{{ $row['area_name'] }}</td>
                        <td class="font-semibold">{{ $row['level'] }}</td>
                        <td class="border-r border-base-300">
                            <div class="font-bold uppercase">{{ $row['supervisor_name'] }}</div>
                        </td>
                        <td class="text-center font-medium">{{ number_format($row['pc'], 0, ',', '.') }}</td>
                        <td class="text-center font-medium">{{ number_format($row['ac'], 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($row['pc_ac_pct'] < 50) <span class="text-error font-bold">{{ number_format($row['pc_ac_pct'], 1, ',', '.') }}%</span>
                            @elseif($row['pc_ac_pct'] < 80) <span class="text-warning font-bold">{{ number_format($row['pc_ac_pct'], 1, ',', '.') }}%</span>
                            @else <span class="text-success font-bold">{{ number_format($row['pc_ac_pct'], 1, ',', '.') }}%</span> @endif
                        </td>
                        <td class="text-center font-medium">{{ number_format($row['ec'], 0, ',', '.') }}</td>
                        <td class="text-center border-r border-base-300">
                            @if($row['ec_pct'] < 50) <span class="text-error font-bold">{{ number_format($row['ec_pct'], 1, ',', '.') }}%</span>
                            @elseif($row['ec_pct'] < 80) <span class="text-warning font-bold">{{ number_format($row['ec_pct'], 1, ',', '.') }}%</span>
                            @else <span class="text-success font-bold">{{ number_format($row['ec_pct'], 1, ',', '.') }}%</span> @endif
                        </td>
                        <td class="text-right text-base-content/80 bg-base-200/20">Rp {{ number_format($row['target'], 0, ',', '.') }}</td>
                        <td class="text-right font-medium bg-base-200/20">Rp {{ number_format($row['order'], 0, ',', '.') }}</td>
                        <td class="text-center border-r border-base-300 bg-base-200/20">
                            @if($row['target_order_pct'] < 50) <span class="text-error font-bold">{{ number_format($row['target_order_pct'], 1, ',', '.') }}%</span>
                            @elseif($row['target_order_pct'] < 80) <span class="text-warning font-bold">{{ number_format($row['target_order_pct'], 1, ',', '.') }}%</span>
                            @else <span class="text-success font-bold">{{ number_format($row['target_order_pct'], 1, ',', '.') }}%</span> @endif
                        </td>
                        <td class="text-center">{{ number_format($row['rwo'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $row['rwo_pct'] < 50 ? 'text-error' : ($row['rwo_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['rwo_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center">{{ number_format($row['pnr'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $row['pnr_pct'] < 50 ? 'text-error' : ($row['pnr_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['pnr_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center">{{ number_format($row['ngvo'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $row['ngvo_pct'] < 50 ? 'text-error' : ($row['ngvo_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['ngvo_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center font-bold text-base-content/80">{{ number_format($row['pareto'], 0, ',', '.') }}</td>
                        <td class="text-center border-r border-base-300 font-bold {{ $row['pareto_pct'] < 50 ? 'text-error' : ($row['pareto_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['pareto_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center bg-base-200/20">
                            @if($row['out_of_area'] > 0)
                                <span class="badge badge-error badge-sm text-xs">{{ number_format($row['out_of_area'], 0, ',', '.') }}</span>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                        <td class="text-center font-bold bg-base-200/20 {{ $row['out_of_area_pct'] > 0 ? 'text-error' : 'text-base-content/40' }}">{{ number_format($row['out_of_area_pct'], 1, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center py-10">
                            <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-base-content/20 mb-3" />
                            <p class="text-base-content/50 font-medium">Tidak ada data untuk summary</p>
                            <p class="text-xs text-base-content/40 mt-1">Silakan sesuaikan filter di atas</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
                @if(count($sumData) > 0)
                <tfoot class="bg-base-200/80 text-base-content font-bold sticky bottom-0 z-10 text-xs">
                    <tr>
                        <td colspan="5" class="text-right uppercase tracking-wider text-xs border-r border-base-300">Total Kumulatif</td>
                        <td class="text-center">{{ number_format($totPc, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($totAc, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-xs">{{ $totPc > 0 ? number_format(($totAc / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totEc, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-xs border-r border-base-300">{{ $totPc > 0 ? number_format(($totEc / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-right bg-base-200/50">Rp {{ number_format($totTarget, 0, ',', '.') }}</td>
                        <td class="text-right text-primary text-xs bg-base-200/50">Rp {{ number_format($totOrder, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-xs border-r border-base-300 bg-base-200/50">{{ $totTarget > 0 ? number_format(($totOrder / $totTarget) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totRwo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-xs">{{ $totPc > 0 ? number_format(($totRwo / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totPnr, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-xs">{{ $totPc > 0 ? number_format(($totPnr / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totNgvo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-xs">{{ $totPc > 0 ? number_format(($totNgvo / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totRwo + $totPnr + $totNgvo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-xs border-r border-base-300">{{ $totPc > 0 ? number_format((($totRwo + $totPnr + $totNgvo) / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center text-error bg-base-200/50">{{ number_format($totOoa, 0, ',', '.') }}</td>
                        <td class="text-center text-error text-xs bg-base-200/50">{{ $totAc > 0 ? number_format(($totOoa / $totAc) * 100, 1, ',', '.') : '0,0' }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @endif
    </div>

    {{-- Modal Order --}}
    <div x-data="{ open: false, qty: 0, val: 0 }" 
         @open-order-modal.window="qty = $event.detail.qty; val = $event.detail.val; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Detail Order</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Qty Order</p>
                        <p class="text-xl font-bold" x-text="Number(qty).toLocaleString('id-ID')"></p>
                    </div>
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Val Order</p>
                        <p class="text-xl font-bold font-mono">Rp <span x-text="Number(val).toLocaleString('id-ID')"></span></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Reason --}}
    <div x-data="{ open: false, type: '', desc: '' }"
         @open-reason-modal.window="type = $event.detail.type; desc = $event.detail.desc; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Detail Reason</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Type</p>
                        <p class="text-sm font-medium" x-text="type || '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Desc</p>
                        <p class="text-sm" x-text="desc || '-'"></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Action Remark --}}
    <div x-data="{ open: false }" 
         @open-modal.window="if (Array.isArray($event.detail) ? $event.detail[0] === 'modal-action-remark' : $event.detail === 'modal-action-remark') { open = true; }"
         @close-modal.window="if (Array.isArray($event.detail) ? $event.detail[0] === 'modal-action-remark' : $event.detail === 'modal-action-remark') { open = false; }">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Form Remark</h3>
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">Isi Remark</span>
                    </label>
                    <textarea wire:model="modalRemarkText" class="textarea textarea-bordered h-24 @error('modalRemarkText') textarea-error @enderror" placeholder="Ketik remark di sini..."></textarea>
                    @error('modalRemarkText')
                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-outline" @click="open = false">Batal</button>
                    <button type="button" wire:click="saveRemark" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveRemark" class="loading loading-spinner"></span>
                        Simpan
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Map --}}
    <div x-data="visitMapModal()"
         @open-map-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <div class="mb-3 flex items-start justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="font-bold text-lg flex items-center gap-2">
                            <span>Peta Lokasi: <span x-text="mapData?.custName || 'Toko'"></span></span>
                        </h3>
                        <p class="text-xs text-base-content/60 mt-0.5" x-show="mapData?.depoName">
                            Kantor Depo Terkait: <strong class="text-base-content" x-text="mapData?.depoName"></strong> 
                            <span class="font-mono text-base-content/70" x-show="mapData?.distToDepo">(Jarak: <span x-text="mapData?.distToDepo + 'm'"></span>)</span>
                        </p>
                    </div>
                    <template x-if="mapData?.isAtDepo">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold bg-error text-white shadow-sm animate-pulse">
                            🏢 Terdeteksi Check-in di Depo (<span x-text="mapData?.distToDepo + 'm'"></span>)
                        </span>
                    </template>
                </div>
                <div id="visit-map" wire:ignore class="rounded-xl border border-base-300"></div>
                <div class="flex flex-wrap gap-4 mt-3 items-center text-xs">
                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 bg-red-500 rounded-full"></div> Master Point Toko</div>
                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 bg-blue-500 rounded-full"></div> Titik Kunjungan (GPS Aktual)</div>
                    <template x-if="mapData?.depoName">
                        <div class="flex items-center gap-1.5"><div class="w-3 h-3 bg-amber-500 rounded-full"></div> Kantor Depo / Distributor</div>
                    </template>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-info text-white" @click="initMap()" title="Klik jika peta tidak sejajar/blank">
                        <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh Peta
                    </button>
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal All Maps --}}
    <div x-data="allVisitMapModal()"
         @open-all-maps-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Sebaran Titik Visit Kunjungan</h3>
                <div id="all-visit-map" wire:ignore class="rounded-xl border border-base-300"></div>
                <div class="modal-action">
                    <button type="button" class="btn btn-info text-white" @click="initMap()" title="Klik jika peta tidak sejajar/blank">
                        <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh Peta
                    </button>
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <script>
        function visitMapModal() {
            return {
                open: false,
                mapData: null,
                mapInstance: null,
                initMap(data) {
                    if (data) this.mapData = data;
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
                            container: 'visit-map',
                            style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                            center: [118.0148, -2.5489],
                            zoom: 5
                        });

                        let mLat = parseFloat(data.masterLat);
                        let mLon = parseFloat(data.masterLon);
                        let vLat = parseFloat(data.visitLat);
                        let vLon = parseFloat(data.visitLon);
                        let dLat = parseFloat(data.depoLat);
                        let dLon = parseFloat(data.depoLon);

                        // Deteksi jika koordinat terbalik (lat > 90)
                        if (Math.abs(mLat) > 90) { const temp = mLat; mLat = mLon; mLon = temp; }
                        if (Math.abs(vLat) > 90) { const temp = vLat; vLat = vLon; vLon = temp; }
                        if (Math.abs(dLat) > 90) { const temp = dLat; dLat = dLon; dLon = temp; }

                        const bounds = new maplibregl.LngLatBounds();

                        let hasMaster = !isNaN(mLat) && !isNaN(mLon) && mLat !== 0;
                        let hasVisit = !isNaN(vLat) && !isNaN(vLon) && vLat !== 0;
                        let hasDepo = !isNaN(dLat) && !isNaN(dLon) && dLat !== 0 && dLon !== 0;

                        if (hasMaster) {
                            new maplibregl.Marker({ color: '#ef4444' })
                                .setLngLat([mLon, mLat])
                                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Master Point Toko</strong><br><span class='text-xs text-gray-500 font-mono cursor-pointer hover:text-primary' onclick="window.open('https://www.google.com/maps/search/?api=1&query=${mLat},${mLon}', '_blank')">📍 ${mLat}, ${mLon}</span></div>`))
                                .addTo(this.mapInstance);
                            bounds.extend([mLon, mLat]);
                        }

                        if (hasVisit) {
                            new maplibregl.Marker({ color: '#3b82f6' })
                                .setLngLat([vLon, vLat])
                                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Titik Kunjungan (Visit GPS)</strong><br><span class='text-xs text-gray-500 font-mono cursor-pointer hover:text-primary' onclick="window.open('https://www.google.com/maps/search/?api=1&query=${vLat},${vLon}', '_blank')">📍 ${vLat}, ${vLon}</span></div>`))
                                .addTo(this.mapInstance);
                            bounds.extend([vLon, vLat]);
                        }

                        if (hasDepo) {
                            const depoEl = document.createElement('div');
                            depoEl.className = 'custom-depo-marker flex items-center justify-center';
                            depoEl.innerHTML = `<div style="background-color: #f59e0b; color: white; border: 2px solid white; border-radius: 9999px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); font-size: 13px; cursor: pointer;" title="Kantor Depo">🏢</div>`;

                            new maplibregl.Marker({ element: depoEl })
                                .setLngLat([dLon, dLat])
                                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`
                                    <div class='text-black'>
                                        <strong>🏢 ${data.depoName || 'Kantor Depo'}</strong><br>
                                        <span class='text-xs text-gray-700'>Jarak Kunjungan ke Depo: <strong>${data.distToDepo || '-'} m</strong></span><br>
                                        <span class='text-xs text-gray-500 font-mono cursor-pointer hover:text-primary' onclick="window.open('https://www.google.com/maps/search/?api=1&query=${dLat},${dLon}', '_blank')">📍 ${dLat}, ${dLon}</span>
                                    </div>
                                `))
                                .addTo(this.mapInstance);
                            bounds.extend([dLon, dLat]);
                        }

                        if (!bounds.isEmpty()) {
                            this.mapInstance.fitBounds(bounds, { padding: 60, maxZoom: 18, animate: false });
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

                            if (hasDepo && hasVisit && (data.isAtDepo || (data.distToDepo && parseInt(data.distToDepo) <= 1000 && !data.isBriefingEva))) {
                                this.mapInstance.addSource('route-depo', {
                                    'type': 'geojson',
                                    'data': {
                                        'type': 'Feature',
                                        'geometry': {
                                            'type': 'LineString',
                                            'coordinates': [
                                                [vLon, vLat],
                                                [dLon, dLat]
                                            ]
                                        }
                                    }
                                });
                                this.mapInstance.addLayer({
                                    'id': 'route-depo',
                                    'type': 'line',
                                    'source': 'route-depo',
                                    'layout': {
                                        'line-join': 'round',
                                        'line-cap': 'round'
                                    },
                                    'paint': {
                                        'line-color': '#ef4444',
                                        'line-width': 2.5,
                                        'line-dasharray': [3, 2]
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
            };
        }

        function allVisitMapModal() {
            return {
                open: false,
                mapPoints: null,
                initMap(points) {
                    if (points) this.mapPoints = points;
                    else points = this.mapPoints;
                    
                    this.open = true;

                    if (typeof maplibregl === 'undefined') {
                        const script = document.createElement('script');
                        script.src = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js';
                        script.onload = () => this.initMap(points);
                        document.head.appendChild(script);

                        const css = document.createElement('link');
                        css.rel = 'stylesheet';
                        css.href = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css';
                        document.head.appendChild(css);
                        return;
                    }

                    setTimeout(() => {
                        try {
                            if (window.allVisitMapInstance) {
                                window.allVisitMapInstance.remove();
                                window.allVisitMapInstance = null;
                            }
                            
                            const pointsArray = Array.isArray(points) ? points : (points ? Object.values(points) : []);
                            
                            const features = pointsArray.map(pt => {
                                let lat = parseFloat(pt.lat);
                                let lon = parseFloat(pt.lon);
                                
                                if (Math.abs(lat) > 90) {
                                    const temp = lat;
                                    lat = lon;
                                    lon = temp;
                                }

                                if (isNaN(lat) || isNaN(lon)) return null;
                                
                                let depoInfo = '';
                                if (pt.is_at_depo) {
                                    depoInfo = `<div class='text-rose-600 font-bold text-xs mt-1'>🏢 Terdeteksi di Depo: ${pt.depo_name || ''} (${pt.dist_to_depo || '-'}m)</div>`;
                                } else if (pt.dist_to_depo && pt.dist_to_depo <= 300 && !pt.is_briefing_eva) {
                                    depoInfo = `<div class='text-amber-600 text-xs mt-1'>🏢 Dekat Depo: ${pt.depo_name || ''} (${pt.dist_to_depo}m)</div>`;
                                }

                                return {
                                    'type': 'Feature',
                                    'properties': {
                                        'description': `<div class='text-black'><strong>${pt.name}</strong><br>Tgl: ${pt.date}<br>SPV: ${pt.spv}<br>${depoInfo}<span class='text-xs text-gray-500 font-mono cursor-pointer hover:text-primary' onclick='window.open("https://www.google.com/maps/search/?api=1&query=${lat},${lon}", "_blank");' title='Buka di Google Maps'>📍 ${lat}, ${lon}</span></div>`
                                    },
                                    'geometry': {
                                        'type': 'Point',
                                        'coordinates': [lon, lat]
                                    }
                                };
                            }).filter(f => f !== null);

                            window.allVisitMapInstance = new maplibregl.Map({
                                container: 'all-visit-map',
                                style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                                center: [118.0148, -2.5489],
                                zoom: 5
                            });

                            const initSimpleCluster = () => {
                                try {
                                    if (!window.allVisitMapInstance.getSource('visits')) {
                                        window.allVisitMapInstance.addSource('visits', {
                                            type: 'geojson',
                                            data: {
                                                type: 'FeatureCollection',
                                                features: features
                                            },
                                            cluster: true,
                                            clusterMaxZoom: 14,
                                            clusterRadius: 50
                                        });
                                    }

                                    if (!window.allVisitMapInstance.getLayer('clusters')) {
                                        window.allVisitMapInstance.addLayer({
                                            id: 'clusters',
                                            type: 'circle',
                                            source: 'visits',
                                            filter: ['has', 'point_count'],
                                            paint: {
                                                'circle-color': [
                                                    'step',
                                                    ['get', 'point_count'],
                                                    '#51bbd6',
                                                    10,
                                                    '#f59e0b',
                                                    20,
                                                    '#ef4444'
                                                ],
                                                'circle-radius': [
                                                    'step',
                                                    ['get', 'point_count'],
                                                    20,
                                                    10,
                                                    25,
                                                    20,
                                                    30
                                                ],
                                                'circle-stroke-width': 2,
                                                'circle-stroke-color': '#fff'
                                            }
                                        });
                                    }

                                    if (!window.allVisitMapInstance.getLayer('cluster-count')) {
                                        window.allVisitMapInstance.addLayer({
                                            id: 'cluster-count',
                                            type: 'symbol',
                                            source: 'visits',
                                            filter: ['has', 'point_count'],
                                            layout: {
                                                'text-field': '{point_count_abbreviated}',
                                                'text-font': ['Open Sans Regular'],
                                                'text-size': 12,
                                                'text-allow-overlap': true
                                            }
                                        });
                                    }

                                    if (!window.allVisitMapInstance.getLayer('unclustered-point')) {
                                        window.allVisitMapInstance.addLayer({
                                            id: 'unclustered-point',
                                            type: 'circle',
                                            source: 'visits',
                                            filter: ['!', ['has', 'point_count']],
                                            paint: {
                                                'circle-color': '#3b82f6',
                                                'circle-radius': 8,
                                                'circle-stroke-width': 2,
                                                'circle-stroke-color': '#ffffff'
                                            }
                                        });
                                    }
                                } catch (err) {
                                    console.error('Cluster Error:', err);
                                }
                            };

                            if (window.allVisitMapInstance.loaded()) {
                                initSimpleCluster();
                            } else {
                                window.allVisitMapInstance.on('load', initSimpleCluster);
                            }

                            const bounds = new maplibregl.LngLatBounds();
                            features.forEach(f => bounds.extend(f.geometry.coordinates));

                            if (!bounds.isEmpty()) {
                                window.allVisitMapInstance.fitBounds(bounds, { padding: 50, maxZoom: 18, animate: false });
                            }

                            window.allVisitMapInstance.on('click', 'clusters', (e) => {
                                const mapFeatures = window.allVisitMapInstance.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                                const clusterId = mapFeatures[0].properties.cluster_id;
                                window.allVisitMapInstance.getSource('visits').getClusterExpansionZoom(clusterId).then((zoom) => {
                                    window.allVisitMapInstance.easeTo({
                                        center: mapFeatures[0].geometry.coordinates,
                                        zoom: zoom
                                    });
                                });
                            });

                            window.allVisitMapInstance.on('click', 'unclustered-point', (e) => {
                                const coordinates = e.features[0].geometry.coordinates.slice();
                                const description = e.features[0].properties.description;
                                
                                while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                                    coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                                }

                                new maplibregl.Popup({ offset: 10 })
                                    .setLngLat(coordinates)
                                    .setHTML(description)
                                    .addTo(window.allVisitMapInstance);
                            });

                            window.allVisitMapInstance.on('mouseenter', 'clusters', () => { window.allVisitMapInstance.getCanvas().style.cursor = 'pointer'; });
                            window.allVisitMapInstance.on('mouseleave', 'clusters', () => { window.allVisitMapInstance.getCanvas().style.cursor = ''; });
                            window.allVisitMapInstance.on('mouseenter', 'unclustered-point', () => { window.allVisitMapInstance.getCanvas().style.cursor = 'pointer'; });
                            window.allVisitMapInstance.on('mouseleave', 'unclustered-point', () => { window.allVisitMapInstance.getCanvas().style.cursor = ''; });

                            setTimeout(() => { if (window.allVisitMapInstance) window.allVisitMapInstance.resize(); }, 300);
                            
                        } catch (e) {
                            console.error(e);
                            alert('Terjadi kesalahan saat memuat peta: ' + e.message);
                        }
                    }, 300);
                }
            };
        }
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    @endpush

    @script
    <script>
        $wire.on('notify', (event) => {
            // Handle differences between Livewire 2 (Array) and Livewire 3 (Object) payload
            const data = Array.isArray(event) ? event[0] : event;
            const msg = data?.msg || data?.message || (typeof data === 'string' ? data : JSON.stringify(data));
            const type = data?.type || 'info';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
                    title: msg,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            } else {
                alert(msg);
            }
        });
    </script>
    @endscript
</div>
