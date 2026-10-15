<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Summary Kunjungan SE</x-slot>

    {{-- Standar Tab Menu dari resources/views/components/ui/tab-menu.blade.php --}}
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.index')" :navigate="true">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.summary')" :navigate="true">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.reason')" :navigate="true">
            <x-heroicon-o-clipboard-document-list class="w-4 h-4 inline-block mr-1" /> Summary Reason
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.trend')" :navigate="true">
            <x-heroicon-o-arrow-trending-up class="w-4 h-4 inline-block mr-1" /> Trend Bulanan
        </x-ui.tab-item>
    </x-ui.tab-menu>

    {{-- 5 KPI Cards Section (Sama persis dengan halaman Detail) --}}
    @include('livewire.report.analisa-kunjungan-se.kpi-cards')

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar (Gaya Identik dengan Detail Data) --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base md:text-lg font-bold">Summary Performa SE</h2>
                    
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
                <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Ringkasan Performa & Efektivitas Sales Exclusive (SE)</p>
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

                {{-- Filter Area (Bisa Semua Area) --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs" @disabled(empty($selectedRegion))>
                        <option value="">Semua Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Distributor (Bisa Semua Distributor) --}}
                <div class="relative w-full sm:w-auto">
                    <select wire:model.live="selectedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[150px] text-xs" @disabled(empty($selectedRegion)) wire:target="selectedArea, selectedRegion" wire:loading.class="opacity-60">
                        <option value="">Semua Distributor</option>
                        @foreach($distributors as $d)
                            <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Smart Date Range Filter (Maks. 31 Hari / 1 Bulan) --}}
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
                        <span class="badge badge-xs {{ $diffDays >= 31 ? 'badge-warning text-warning-content' : 'badge-neutral text-neutral-content' }} font-mono font-bold px-1.5 cursor-default">
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
                        <tr class="text-[0.6875rem] font-bold uppercase tracking-wider border-b-2 border-base-content/20">
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
