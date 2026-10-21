<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('kunjungan_show_kpi') === 'true',
         toggleKpi() {
             this.showKpi = !this.showKpi;
             localStorage.setItem('kunjungan_show_kpi', this.showKpi);
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
            @if($activeTab === 'detail')
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
            @endif
        </x-slot>
    </x-ui.tab-menu>

    @if($activeTab === 'detail')
        <div x-show="showKpi" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 -translate-y-2" 
             x-transition:enter-end="opacity-100 translate-y-0" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100 translate-y-0" 
             x-transition:leave-end="opacity-0 -translate-y-2" 
             class="shrink-0"
             style="display: none;">
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
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full xl:w-auto">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base md:text-lg font-bold">Analisa Kunjungan</h2>
                    @if($activeTab === 'detail')
                        <button type="button" 
                            @click="toggleKpi()" 
                            class="btn btn-xs rounded-lg gap-1 border transition-all font-semibold cursor-pointer shadow-2xs"
                            :class="showKpi ? 'btn-ghost text-base-content/60 hover:text-base-content border-base-300' : 'btn-primary text-white border-primary'"
                            :title="showKpi ? 'Sembunyikan KPI Card agar ruang tabel lebih lega' : 'Tampilkan kembali KPI Card'">
                            <template x-if="showKpi">
                                <span class="flex items-center gap-1">
                                    <x-heroicon-m-chevron-up class="w-3.5 h-3.5" />
                                    <span class="text-[11px] hidden sm:inline">Sembunyikan KPI</span>
                                </span>
                            </template>
                            <template x-if="!showKpi">
                                <span class="flex items-center gap-1">
                                    <x-heroicon-m-chart-bar class="w-3.5 h-3.5" />
                                    <span class="text-[11px]">Tampilkan KPI</span>
                                </span>
                            </template>
                        </button>
                    @endif
                </div>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Filter data analisa kunjungan</p>
            </div>
            
            @if($activeTab === 'detail')
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                {{-- Territory selects --}}
                <div class="flex flex-wrap items-center gap-2">
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs" @disabled(!$selectedRegion)>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[140px] text-xs" @disabled(!$selectedArea)>
                        <option value="">Pilih Supervisor</option>
                        @foreach($supervisors as $spv)
                            <option value="{{ $spv->supervisor_code }}">{{ $spv->supervisor_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Smart Date Range Filter (Maks. 31 Hari) --}}
                <div class="flex items-center gap-1 bg-base-100 px-1 py-0.5 rounded-xl border border-base-300 shadow-2xs w-full sm:w-auto">
                    <button type="button" wire:click="previousMonth" class="btn btn-xs btn-ghost btn-square text-base-content/60 hover:text-base-content" title="Bulan Sebelumnya">
                        <x-heroicon-m-chevron-left class="w-3.5 h-3.5" />
                    </button>

                    <input type="date" 
                        wire:model.live="startDate" 
                        max="{{ $endDate ?: date('Y-m-d') }}"
                        min="{{ $endDate ? \Carbon\Carbon::parse($endDate)->subDays(30)->format('Y-m-d') : '' }}"
                        class="input input-xs bg-transparent border-none text-xs w-[110px] focus:outline-none p-0 text-center font-medium" 
                        title="Tanggal Mulai" />

                    <span class="text-[10px] font-semibold text-base-content/40">s/d</span>

                    <input type="date" 
                        wire:model.live="endDate" 
                        min="{{ $startDate ?: '' }}"
                        max="{{ $startDate ? \Carbon\Carbon::parse($startDate)->addDays(30)->format('Y-m-d') : date('Y-m-d') }}"
                        class="input input-xs bg-transparent border-none text-xs w-[110px] focus:outline-none p-0 text-center font-medium" 
                        title="Tanggal Akhir (Maks. 31 hari dari tanggal mulai)" />

                    @php
                        $diffDays = ($startDate && $endDate) ? (\Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1) : 0;
                    @endphp
                    <div class="tooltip tooltip-bottom" data-tip="{{ $diffDays }} hari dipilih (Maks. 31 hari)">
                        <span class="badge badge-xs {{ $diffDays >= 31 ? 'badge-warning text-warning-content' : 'badge-neutral text-neutral-content' }} font-mono text-[9px] font-bold px-1.5 cursor-default">
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

                {{-- Action Buttons --}}
                <div class="flex items-center gap-1.5">
                    <script type="application/json" id="map-points-data-json">{!! json_encode($this->mapPointsData) !!}</script>

                    @if(empty($selectedSupervisor))
                        <div class="tooltip tooltip-bottom" data-tip="Pilih Supervisor terlebih dahulu">
                            <x-ui.button class="rounded-xl px-3 font-semibold shadow-sm" variant="primary" icon="magnifying-glass" size="sm" disabled>Terapkan</x-ui.button>
                        </div>
                    @else
                        <x-ui.button class="rounded-xl px-3 font-semibold shadow-sm" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                    @endif

                    <div class="tooltip tooltip-bottom" data-tip="Reset Filter">
                        <x-ui.button class="rounded-xl" variant="neutral" icon="arrow-path" size="sm" wire:click="resetFilter" spinner="resetFilter"></x-ui.button>
                    </div>

                    @php
                        $isMapDisabled = empty($appliedSupervisor) || empty($this->mapPointsData);
                    @endphp
                    <div class="tooltip tooltip-bottom" data-tip="{{ $isMapDisabled ? (empty($appliedSupervisor) ? 'Terapkan filter terlebih dahulu' : 'Tidak ada titik peta') : 'Buka Peta Sebaran Kunjungan' }}">
                        <button type="button" 
                            x-data 
                            @click="$dispatch('open-all-maps-modal', JSON.parse(document.getElementById('map-points-data-json').textContent))" 
                            class="btn btn-sm btn-info rounded-xl text-white gap-1 px-3" 
                            @disabled($isMapDisabled)>
                            <x-heroicon-o-map class="w-4 h-4" /> Maps
                        </button>
                    </div>

                    @if($canExport)
                        @php
                            $isDetailExportDisabled = empty($appliedSupervisor);
                            $hasPendingDetailFilters = $selectedRegion !== $appliedRegion || 
                                                       $selectedArea !== $appliedArea || 
                                                       $selectedSupervisor !== $appliedSupervisor || 
                                                       $startDate !== $appliedStartDate || 
                                                       $endDate !== $appliedEndDate;
                        @endphp
                        <div class="tooltip tooltip-left" data-tip="{{ $isDetailExportDisabled || $hasPendingDetailFilters ? 'Klik Terapkan terlebih dahulu' : 'Export ke Excel' }}">
                            <x-ui.button variant="success" size="sm" class="rounded-xl text-white px-3 font-semibold shadow-sm" wire:click="export" spinner="export" icon="arrow-down-tray" :disabled="$isDetailExportDisabled || $hasPendingDetailFilters">
                                Export
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>
            @elseif($activeTab === 'summary')
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-2">
                    <input type="date" wire:model="summaryStartDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                    <span class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">s/d</span>
                    <input type="date" wire:model="summaryEndDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                </div>
                
                <div class="flex items-center gap-2">
                    <select wire:model="summaryRegion" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-sm btn-outline border-base-300 hover:bg-base-200 hover:text-base-content rounded-xl bg-base-100 font-normal w-[140px] flex justify-between px-3">
                            <span class="truncate">
                                @if(count($summaryLevels) === 0)
                                    Semua Level
                                @else
                                    {{ count($summaryLevels) }} Dipilih
                                @endif
                            </span>
                            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 opacity-60 shrink-0" />
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[50] menu p-2 shadow-lg bg-base-100 rounded-xl w-48 mt-1 border border-base-300">
                            <li>
                                <label class="label cursor-pointer justify-start gap-3 px-2 py-1.5">
                                    <input type="checkbox" wire:model="summaryLevels" value="region" class="checkbox checkbox-sm checkbox-primary rounded-md" />
                                    <span class="label-text">Region</span>
                                </label>
                            </li>
                            <li>
                                <label class="label cursor-pointer justify-start gap-3 px-2 py-1.5">
                                    <input type="checkbox" wire:model="summaryLevels" value="area" class="checkbox checkbox-sm checkbox-primary rounded-md" />
                                    <span class="label-text">Area</span>
                                </label>
                            </li>
                            <li>
                                <label class="label cursor-pointer justify-start gap-3 px-2 py-1.5">
                                    <input type="checkbox" wire:model="summaryLevels" value="supervisor" class="checkbox checkbox-sm checkbox-primary rounded-md" />
                                    <span class="label-text">Supervisor</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.button class="rounded-xl" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                    <div class="tooltip tooltip-bottom" data-tip="Reset Filter">
                        <x-ui.button class="rounded-xl" variant="neutral" icon="arrow-path" size="sm" wire:click="resetFilter" spinner="resetFilter"></x-ui.button>
                    </div>
                    @if($canExport)
                        @php
                            $isSummaryExportDisabled = empty($appliedSummaryRegion) && empty($appliedSummaryStartDate) && empty($appliedSummaryEndDate) && empty($appliedSummaryLevels);
                            $hasPendingSummaryFilters = $summaryRegion !== $appliedSummaryRegion || 
                                                        $summaryStartDate !== $appliedSummaryStartDate || 
                                                        $summaryEndDate !== $appliedSummaryEndDate ||
                                                        $summaryLevels !== $appliedSummaryLevels;
                        @endphp
                        <div class="tooltip tooltip-left" data-tip="{{ $isSummaryExportDisabled || $hasPendingSummaryFilters ? 'Klik Terapkan terlebih dahulu' : 'Export Summary ke Excel' }}">
                            <x-ui.button variant="success" size="sm" class="rounded-xl text-white" wire:click="exportSummary" spinner="exportSummary" icon="arrow-down-tray" :disabled="$isSummaryExportDisabled || $hasPendingSummaryFilters">
                                Export
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        @if($activeTab === 'detail')
        {{-- Secondary Filter & Search Toolbar --}}
        <div class="px-3 md:px-4 lg:px-5 py-2.5 bg-base-100 border-b border-base-200 flex flex-wrap items-center justify-between gap-3 text-xs shrink-0">
            {{-- Search Box --}}
            <div class="relative w-full sm:w-64 md:w-72">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40 pointer-events-none" />
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Cari toko, custno, pilar..." 
                       class="input input-xs sm:input-sm input-bordered w-full pl-9 pr-8 rounded-xl bg-base-200/50 focus:bg-base-100 text-xs" />
                @if(!empty($search))
                    <button type="button" wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content" title="Hapus pencarian">
                        <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif
            </div>

            {{-- Filter Dropdowns & Controls --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Filter Visit --}}
                <select wire:model.live="filterVisit" class="select select-xs sm:select-sm select-bordered rounded-xl bg-base-100 text-xs border-base-300">
                    <option value="all">Status Visit: Semua</option>
                    <option value="Y">Visit Saja (Y)</option>
                    <option value="N">Belum Visit (N)</option>
                </select>

                {{-- Filter EC --}}
                <select wire:model.live="filterEc" class="select select-xs sm:select-sm select-bordered rounded-xl bg-base-100 text-xs border-base-300">
                    <option value="all">Status Order: Semua</option>
                    <option value="Y">Order / EC Saja (Y)</option>
                    <option value="N">Non-EC (N)</option>
                </select>

                {{-- Filter PJP --}}
                <select wire:model.live="filterPjp" class="select select-xs sm:select-sm select-bordered rounded-xl bg-base-100 text-xs border-base-300">
                    <option value="all">Tipe Rute: Semua</option>
                    <option value="Y">PJP (Jadwal)</option>
                    <option value="N">Non-PJP (Extra Call)</option>
                </select>

                {{-- Filter Radius --}}
                <select wire:model.live="filterDistance" class="select select-xs sm:select-sm select-bordered rounded-xl bg-base-100 text-xs border-base-300">
                    <option value="all">Radius: Semua</option>
                    <option value="within">&le; 50m (Dalam Radius)</option>
                    <option value="out">> 50m (Luar Radius)</option>
                </select>

                {{-- Filter Reason --}}
                <select wire:model.live="filterReason" class="select select-xs sm:select-sm select-bordered rounded-xl bg-base-100 text-xs border-base-300">
                    <option value="all">Reason: Semua</option>
                    <option value="with">Ada Reason</option>
                    <option value="without">Tanpa Reason</option>
                </select>

                {{-- Filter Lokasi Depo --}}
                <select wire:model.live="filterDepo" class="select select-xs sm:select-sm select-bordered rounded-xl bg-base-100 text-xs border-base-300">
                    <option value="all">Lokasi Depo: Semua</option>
                    <option value="at_depo">🏢 Di Depo (&le; 100m)</option>
                    <option value="not_depo">🏬 Di Luar Depo (> 100m)</option>
                </select>

                {{-- Reset Detail Filters Button --}}
                @if(!empty($search) || $filterVisit !== 'all' || $filterEc !== 'all' || $filterPjp !== 'all' || $filterDistance !== 'all' || $filterReason !== 'all' || $filterDepo !== 'all')
                    <button type="button" wire:click="resetDetailFilters" class="btn btn-xs btn-ghost text-error hover:bg-error/10 gap-1 rounded-xl" title="Reset filter rincian">
                        <x-heroicon-m-x-circle class="w-3.5 h-3.5" />
                        <span>Reset Filter</span>
                    </button>
                @endif

                <div class="hidden sm:block w-px h-5 bg-base-300 mx-0.5"></div>

                {{-- Expand / Collapse All --}}
                <div class="flex items-center gap-1">
                    <button type="button" @click="expandAll" class="btn btn-xs btn-outline btn-primary rounded-xl gap-1 font-semibold" title="Buka rincian semua hari">
                        <x-heroicon-m-arrows-pointing-out class="w-3.5 h-3.5" />
                        <span class="hidden md:inline">Buka Semua</span>
                    </button>
                    <button type="button" @click="collapseAll" class="btn btn-xs btn-ghost rounded-xl gap-1 text-base-content/70 hover:text-base-content" title="Tutup rincian semua hari">
                        <x-heroicon-m-arrows-pointing-in class="w-3.5 h-3.5" />
                        <span class="hidden md:inline">Tutup Semua</span>
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
                                    <span class="inline-flex items-center px-1 py-0.2 rounded text-[8px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 leading-tight">Sabtu</span>
                                @endif
                                @if(($day->at_depo_count ?? 0) > 0)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30" title="{{ $day->at_depo_count }} kunjungan terdeteksi di kantor Depo / Distributor">
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

                        {{-- Actual Call --}}
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

                        {{-- Effective Call (EC) --}}
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
                        <td colspan="10" class="p-2 sm:p-3">
                            <div class="bg-base-100 rounded-xl border border-base-300 shadow-md overflow-hidden">
                                {{-- Sub-Table Header Bar --}}
                                <div class="px-3.5 py-2 bg-slate-900 text-slate-100 flex flex-wrap items-center justify-between gap-2.5 border-b border-slate-700">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></span>
                                        <span class="font-bold text-xs tracking-wide text-white">Detail Kunjungan: {{ $day->day_name }}, {{ $day->formatted_date }}</span>
                                        <div class="flex items-center gap-1.5 flex-wrap text-[10px]">
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
                                    <table class="table table-xs table-zebra w-full whitespace-nowrap text-[11px] leading-tight [&_th]:py-1.5 [&_th]:px-2 [&_td]:py-1 [&_td]:px-2">
                                        <thead class="sticky top-0 z-20 shadow-sm border-b border-slate-700 uppercase font-bold tracking-wider text-[10px] text-white [&_th]:bg-slate-800 dark:[&_th]:bg-slate-900 [&_th]:text-slate-100 [&_th]:border-b [&_th]:border-slate-700">
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
                                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30 whitespace-nowrap" 
                                                                      title="Check-in di kantor depo {{ $item->depo_name }} ({{ $item->dist_to_depo }}m dari depo)">
                                                                    🏢 Depo ({{ $item->dist_to_depo }}m)
                                                                </span>
                                                            </div>
                                                        @elseif(!empty($item->dist_to_depo) && $item->dist_to_depo <= 300)
                                                            <div class="mt-0.5">
                                                                <span class="text-[9px] text-amber-600 font-mono block whitespace-nowrap" title="Dekat Depo {{ $item->depo_name }} ({{ $item->dist_to_depo }}m)">
                                                                    🏢 {{ $item->dist_to_depo }}m
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="mt-0.5">
                                                            <span class="text-[9px] text-base-content/50 font-medium block whitespace-nowrap" title="Briefing/Evaluasi di Kantor Depo {{ $item->depo_name ?? '' }}">
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
                            <span class="text-[10px] text-base-content/50">{{ $gt->pc }} PC • {{ $gt->xc }} XC</span>
                        </td>
                        <td class="text-center font-mono text-xs">
                            <div class="flex items-center justify-center gap-1">
                                <span>{{ $gt->total_visit }}</span>
                                @if(($gt->at_depo_count ?? 0) > 0)
                                    <span class="inline-flex items-center gap-0.5 px-1 py-0.2 rounded text-[8px] font-bold bg-rose-500/15 text-rose-600 border border-rose-500/30" title="{{ $gt->at_depo_count }} kunjungan terdeteksi di kantor Depo">
                                        🏢 {{ $gt->at_depo_count }} Depo
                                    </span>
                                @endif
                            </div>
                            <span class="badge badge-xs {{ $gt->visit_percent >= 100 ? 'badge-success text-white' : 'badge-error text-white' }} font-bold">
                                {{ $gt->visit_percent }}%
                            </span>
                        </td>
                        <td class="text-center font-mono text-xs">
                            <span>{{ $gt->total_ec }}</span><br>
                            <span class="badge badge-xs {{ $gt->ec_percent >= 100 ? 'badge-success text-white' : ($gt->ec_percent >= 80 ? 'badge-warning text-slate-900 font-bold' : 'badge-error text-white') }} font-bold">
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
                            <div class="inline-flex flex-col items-center gap-1 font-mono text-[11px] w-full max-w-[145px]">
                                <div class="inline-flex items-center justify-center gap-1 w-full text-[10px]">
                                    <span class="badge badge-xs badge-ghost border-base-300 text-[9px] px-1 py-0.5">
                                        🏬 {{ $gt->consume_str }}
                                    </span>
                                    <span class="badge badge-xs badge-ghost border-base-300 text-[9px] px-1 py-0.5">
                                        🛵 {{ $gt->travel_str }}
                                    </span>
                                </div>
                                <div class="w-full bg-amber-500/20 rounded-full h-1 flex overflow-hidden shadow-2xs">
                                    <div class="bg-success h-full" style="width: {{ $gt->consume_percent }}%"></div>
                                    <div class="bg-amber-500 h-full" style="width: {{ $gt->travel_percent }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($gt->out_of_radius_count == 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-success/10 text-success border border-success/30">
                                    ✓ 100% Sesuai
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-error text-white shadow-2xs">
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

            <table class="table table-xs table-pin-rows w-full text-[11px] [&_th]:px-2 [&_td]:px-2 [&_th]:py-1.5 [&_td]:py-1.5">
                <thead class="bg-base-200 text-base-content z-10 sticky top-0">
                    <tr>
                        <th class="align-middle text-center" rowspan="2">No</th>
                        <th class="align-middle" rowspan="2">Region</th>
                        <th class="align-middle" rowspan="2">Area</th>
                        <th class="align-middle" rowspan="2">Level</th>
                        <th class="align-middle border-r border-base-300" rowspan="2">Supervisor</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300" colspan="5">Kunjungan (Visit)</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300 bg-base-200/50" colspan="3">Order (Value)</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300" colspan="8">Pilar Actual</th>
                        <th class="align-middle text-center border-b-0 border-base-300 bg-base-200/50" colspan="2">Out of Area <span class="text-[9px] font-normal opacity-75">(> 50m)</span></th>
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
                <tbody class="text-[11px] relative">
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
                                <span class="badge badge-error badge-sm text-[10px]">{{ number_format($row['out_of_area'], 0, ',', '.') }}</span>
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
                            <p class="text-[10px] text-base-content/40 mt-1">Silakan sesuaikan filter di atas</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
                @if(count($sumData) > 0)
                <tfoot class="bg-base-200/80 text-base-content font-bold sticky bottom-0 z-10">
                    <tr>
                        <td colspan="5" class="text-right uppercase tracking-wider text-[11px] border-r border-base-300">Total Kumulatif</td>
                        <td class="text-center">{{ number_format($totPc, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($totAc, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totAc / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totEc, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px] border-r border-base-300">{{ $totPc > 0 ? number_format(($totEc / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-right bg-base-200/50">Rp {{ number_format($totTarget, 0, ',', '.') }}</td>
                        <td class="text-right text-primary text-[11px] bg-base-200/50">Rp {{ number_format($totOrder, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px] border-r border-base-300 bg-base-200/50">{{ $totTarget > 0 ? number_format(($totOrder / $totTarget) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totRwo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totRwo / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totPnr, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totPnr / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totNgvo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totNgvo / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totRwo + $totPnr + $totNgvo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px] border-r border-base-300">{{ $totPc > 0 ? number_format((($totRwo + $totPnr + $totNgvo) / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center text-error bg-base-200/50">{{ number_format($totOoa, 0, ',', '.') }}</td>
                        <td class="text-center text-error text-[11px] bg-base-200/50">{{ $totAc > 0 ? number_format(($totOoa / $totAc) * 100, 1, ',', '.') : '0,0' }}%</td>
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
                                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Master Point Toko</strong><br><span class='text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary' onclick="window.open('https://www.google.com/maps/search/?api=1&query=${mLat},${mLon}', '_blank')">📍 ${mLat}, ${mLon}</span></div>`))
                                .addTo(this.mapInstance);
                            bounds.extend([mLon, mLat]);
                        }

                        if (hasVisit) {
                            new maplibregl.Marker({ color: '#3b82f6' })
                                .setLngLat([vLon, vLat])
                                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Titik Kunjungan (Visit GPS)</strong><br><span class='text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary' onclick="window.open('https://www.google.com/maps/search/?api=1&query=${vLat},${vLon}', '_blank')">📍 ${vLat}, ${vLon}</span></div>`))
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
                                        <span class='text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary' onclick="window.open('https://www.google.com/maps/search/?api=1&query=${dLat},${dLon}', '_blank')">📍 ${dLat}, ${dLon}</span>
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
                                    depoInfo = `<div class='text-amber-600 text-[11px] mt-1'>🏢 Dekat Depo: ${pt.depo_name || ''} (${pt.dist_to_depo}m)</div>`;
                                }

                                return {
                                    'type': 'Feature',
                                    'properties': {
                                        'description': `<div class='text-black'><strong>${pt.name}</strong><br>Tgl: ${pt.date}<br>SPV: ${pt.spv}<br>${depoInfo}<span class='text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary' onclick='window.open("https://www.google.com/maps/search/?api=1&query=${lat},${lon}", "_blank");' title='Buka di Google Maps'>📍 ${lat}, ${lon}</span></div>`
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
