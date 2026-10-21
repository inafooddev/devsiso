<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full"
     x-data="{
         showKpi: localStorage.getItem('se_tracking_show_kpi') !== null ? localStorage.getItem('se_tracking_show_kpi') === 'true' : true,
         toggleKpi() {
             this.showKpi = !this.showKpi;
             localStorage.setItem('se_tracking_show_kpi', this.showKpi);
         },
         detailModalOpen: false,
         activeStore: null,
         activeWeekNum: null,
         activeWeekData: null,
         openDetail(store, weekNum, weekData) {
             this.activeStore = store;
             this.activeWeekNum = weekNum;
             this.activeWeekData = weekData;
             this.detailModalOpen = true;
         },
         closeDetail() {
             this.detailModalOpen = false;
             this.activeStore = null;
             this.activeWeekNum = null;
             this.activeWeekData = null;
         }
     }"
     @keydown.escape.window="closeDetail()">

    <x-slot name="title">Tracking Mingguan Temuan Toko SE</x-slot>

    {{-- Standar Tab Menu (6 Tab) --}}
    @php
        $shared = session('analisa_kunjungan_se_shared_filter', []);
        $tabParams = array_filter([
            'region' => $appliedRegion ?: $selectedRegion,
            'area' => $appliedArea ?: $selectedArea,
            'distributor' => $appliedDistributor ?: $selectedDistributor,
            'salesman' => $appliedSalesman ?: $selectedSalesman,
            'startDate' => $shared['startDate'] ?? (isset($appliedYear, $appliedMonth) ? sprintf('%04d-%02d-01', $appliedYear, $appliedMonth) : null),
            'endDate' => $shared['endDate'] ?? (isset($appliedYear, $appliedMonth) ? \Carbon\Carbon::create($appliedYear, $appliedMonth, 1)->endOfMonth()->format('Y-m-d') : null),
        ], fn($v) => !empty($v));
    @endphp
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.index', $tabParams)" :navigate="true">
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
        <x-ui.tab-item :active="true" :href="route('report.analisa-kunjungan-se.tracking', $tabParams)" :navigate="true">
            <x-heroicon-o-clipboard-document-check class="w-4 h-4 inline-block mr-1 text-primary" /> Tracking Toko
        </x-ui.tab-item>
        <x-ui.tab-item :active="false" :href="route('report.analisa-kunjungan-se.raport', $tabParams)" :navigate="true">
            <x-heroicon-o-academic-cap class="w-4 h-4 inline-block mr-1 text-emerald-500" /> Raport SE
        </x-ui.tab-item>

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                @if($appliedRegion && $appliedArea)
                    <span class="badge badge-primary text-white font-bold text-xs gap-1 py-2.5 px-3 shadow-2xs">
                        <x-heroicon-s-building-storefront class="w-4 h-4" />
                        {{ number_format($metrics->total_with_anomalies) }} Toko Bermasalah
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

    {{-- 4 Executive Summary KPI Cards --}}
    <div x-show="showKpi"
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 -translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 -translate-y-2" 
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3 shrink-0 mb-3 md:mb-4">
        
        {{-- Card 1: Total Toko Pernah Anomali --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-amber-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-base-content/60 uppercase">
                <span>Toko Ada Temuan Anomali</span>
                <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                    <x-heroicon-s-building-storefront class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-base-content">
                    {{ number_format($metrics->total_with_anomalies) }}
                </span>
                <span class="text-[11px] font-semibold text-base-content/50">
                    dari {{ number_format($metrics->total_stores) }} Toko Jadwal
                </span>
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Toko dengan &ge; 1 kali insiden pelanggaran SOP PJP
            </div>
        </div>

        {{-- Card 2: Kronis / Berulang (High Priority) --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-rose-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-rose-600 uppercase">
                <span>🔴 Kronis / Berulang</span>
                <div class="w-7 h-7 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-600 shrink-0">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-rose-600">
                    {{ number_format($metrics->recurrent_count) }}
                </span>
                <span class="badge badge-error badge-xs text-white font-bold">Butuh Tindakan</span>
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Anomali berulang &ge; 2 minggu & kunjungan terakhir belum patuh
            </div>
        </div>

        {{-- Card 3: Anomali Baru (Early Warning) --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-amber-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-amber-600 uppercase">
                <span>🟡 Anomali Baru</span>
                <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                    <x-heroicon-s-exclamation-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-amber-600">
                    {{ number_format($metrics->new_count) }}
                </span>
                <span class="badge badge-warning badge-xs text-warning-content font-bold">Early Warning</span>
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Baru pertama kali terjadi anomali di kunjungan minggu terakhir
            </div>
        </div>

        {{-- Card 4: Membaik / Solved (Improvement Target) --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between group hover:border-emerald-500/50 transition-colors">
            <div class="flex items-center justify-between text-[0.6875rem] font-bold tracking-wider text-emerald-600 uppercase">
                <span>🟢 Membaik (Solved)</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black font-mono text-emerald-600">
                    {{ number_format($metrics->solved_count) }}
                </span>
                <span class="badge badge-success badge-xs text-white font-bold">Perbaikan Sukses</span>
            </div>
            <div class="text-[10px] text-base-content/60 mt-1">
                Pernah anomali, namun di minggu terakhir telah normal patuh SOP
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base md:text-lg font-bold flex items-center gap-1.5">
                        <span>Monitoring & Evaluasi Mingguan Temuan Toko SE</span>
                    </h2>

                    @if($appliedYear && $appliedMonth)
                        <span class="badge badge-neutral text-neutral-content font-mono text-xs font-semibold">
                            📅 {{ $monthOptions[$appliedMonth] ?? $appliedMonth }} {{ $appliedYear }}
                        </span>
                    @endif

                    @if($appliedRegion && $appliedArea)
                        <span class="badge badge-outline text-xs font-medium text-base-content/70">
                            📍 {{ $appliedArea }}
                        </span>
                        @if(!empty($appliedDistributor))
                            @php
                                $dName = collect($distributors)->firstWhere('distributor_code', $appliedDistributor)?->distributor_name ?? $appliedDistributor;
                            @endphp
                            <span class="badge badge-outline text-xs font-medium text-base-content/70" title="{{ $dName }}">
                                🏢 {{ \Illuminate\Support\Str::limit($dName, 22) }}
                            </span>
                        @else
                            <span class="badge badge-outline text-xs font-medium text-primary">
                                🏢 Semua Distributor
                            </span>
                        @endif
                        @if(!empty($appliedSalesman))
                            @php
                                $sName = collect($salesmen)->firstWhere('salesman_code', $appliedSalesman)?->salesman_name ?? $appliedSalesman;
                            @endphp
                            <span class="badge badge-outline text-xs font-medium text-base-content/70" title="{{ $sName }}">
                                👤 {{ \Illuminate\Support\Str::limit($sName, 18) }}
                            </span>
                        @endif

                        <span class="badge badge-info badge-outline text-[11px] font-semibold" title="Berdasarkan pembagian resmi tabel master_calender (week_month)">
                            Kalender: M1 &ndash; M{{ count($weeksMap) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">
                    Evaluasi Progres Perbaikan Temuan Toko per Minggu &bull; Validasi Geofence 50m, Titik Depo & Koordinat Kembar
                </p>
            </div>
            
            {{-- Filter Bar (Urutan Standar: Region -> Area -> Distributor -> Salesman -> Periode Bulan/Tahun -> Aksi) --}}
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 w-full lg:w-auto">
                
                {{-- Filter Region --}}
                @if(count($regions) > 1 || $accessLevel === 'nasional')
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[125px] text-xs">
                        <option value="">Pilih Region</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Area --}}
                @if(count($areas) > 1 || !in_array($accessLevel, ['area', 'supervisor']))
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[125px] text-xs" @disabled(empty($selectedRegion))>
                        <option value="">Pilih Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Filter Distributor --}}
                <select wire:model.live="selectedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[150px] text-xs" @disabled(empty($selectedArea))>
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $d)
                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Salesman --}}
                <select wire:model.live="selectedSalesman" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[140px] text-xs" @disabled(empty($selectedArea))>
                    <option value="">Semua Salesman</option>
                    @foreach($salesmen as $s)
                        <option value="{{ $s->salesman_code }}">{{ $s->salesman_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Periode (Bulan & Tahun) --}}
                <div class="flex items-center gap-1 bg-base-100 px-1.5 py-0.5 rounded-xl border border-base-300 shadow-2xs w-full sm:w-auto">
                    <select wire:model.live="selectedMonth" class="select select-xs bg-transparent border-none text-xs focus:outline-none min-w-[95px] font-medium" title="Pilih Bulan Evaluasi">
                        @foreach($monthOptions as $mVal => $mLabel)
                            <option value="{{ $mVal }}">{{ $mLabel }}</option>
                        @endforeach
                    </select>

                    <span class="text-xs font-semibold text-base-content/30">/</span>

                    <select wire:model.live="selectedYear" class="select select-xs bg-transparent border-none text-xs font-mono focus:outline-none min-w-[70px] font-medium" title="Pilih Tahun Evaluasi">
                        @foreach($yearOptions as $yVal)
                            <option value="{{ $yVal }}">{{ $yVal }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons: Terapkan, Reset, Excel --}}
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" 
                        wire:click="applyFilter" 
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-primary rounded-xl gap-1 text-xs font-semibold shadow-xs">
                        <span wire:loading.remove wire:target="applyFilter" class="flex items-center gap-1">
                            <x-heroicon-m-funnel class="w-4 h-4" />
                            <span>Terapkan</span>
                        </span>
                        <span wire:loading wire:target="applyFilter" class="loading loading-spinner loading-xs"></span>
                    </button>

                    <button type="button" 
                        wire:click="resetFilters" 
                        class="btn btn-sm btn-ghost border border-base-300 rounded-xl text-base-content/70 hover:text-base-content text-xs"
                        title="Reset semua filter">
                        <x-heroicon-m-arrow-path class="w-4 h-4" />
                    </button>

                    @if($canExport)
                        <button type="button" 
                            wire:click="exportExcel" 
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1 text-xs font-semibold shadow-xs"
                            title="Download Laporan Matriks Mingguan ke format Excel (.xlsx)">
                            <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1.5">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span>Excel</span>
                            </span>
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sub-Toolbar: Filter Status Tren & Search Bar --}}
        <div class="px-3 md:px-4 py-2 border-b border-base-300 bg-base-100 flex flex-wrap items-center justify-between gap-2 text-xs">
            
            {{-- Status Filter Pills --}}
            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto py-0.5">
                <span class="text-base-content/50 font-bold uppercase text-[10px] mr-1">Status Tren:</span>

                <button type="button" 
                    wire:click="setStatusFilter('all')"
                    class="btn btn-xs rounded-lg transition-all {{ $statusFilter === 'all' ? 'btn-neutral text-white font-bold shadow-2xs' : 'btn-ghost text-base-content/70 border border-base-300 hover:bg-base-200' }}">
                    Semua
                </button>

                <button type="button" 
                    wire:click="setStatusFilter('recurrent')"
                    class="btn btn-xs rounded-lg transition-all {{ $statusFilter === 'recurrent' ? 'btn-error text-white font-bold shadow-2xs' : 'btn-ghost text-rose-600 border border-rose-300 hover:bg-rose-50' }}"
                    title="Anomali berulang di >= 2 minggu dan kunjungan terakhir masih anomali">
                    🔴 Kronis ({{ $metrics->recurrent_count }})
                </button>

                <button type="button" 
                    wire:click="setStatusFilter('new')"
                    class="btn btn-xs rounded-lg transition-all {{ $statusFilter === 'new' ? 'btn-warning text-warning-content font-bold shadow-2xs' : 'btn-ghost text-amber-600 border border-amber-300 hover:bg-amber-50' }}"
                    title="Baru pertama kali muncul anomali di minggu terakhir">
                    🟡 Anomali Baru ({{ $metrics->new_count }})
                </button>

                <button type="button" 
                    wire:click="setStatusFilter('solved')"
                    class="btn btn-xs rounded-lg transition-all {{ $statusFilter === 'solved' ? 'btn-success text-white font-bold shadow-2xs' : 'btn-ghost text-emerald-600 border border-emerald-300 hover:bg-emerald-50' }}"
                    title="Pernah anomali sebelumnya, tapi minggu terakhir sudah normal (terdapat perbaikan)">
                    🟢 Membaik ({{ $metrics->solved_count }})
                </button>

                <button type="button" 
                    wire:click="setStatusFilter('normal')"
                    class="btn btn-xs rounded-lg transition-all {{ $statusFilter === 'normal' ? 'btn-accent text-white font-bold shadow-2xs' : 'btn-ghost text-teal-600 border border-teal-300 hover:bg-teal-50' }}"
                    title="Kunjungan selalu patuh SOP di semua minggu yang dikunjungi">
                    ✅ Normal ({{ $metrics->normal_count }})
                </button>
            </div>

            {{-- Toggle Show All Stores & Search Box --}}
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                
                {{-- Toggle: Tampilkan Semua Toko vs Toko Temuan Saja --}}
                <label class="cursor-pointer label gap-1.5 py-0 px-2 rounded-lg border border-base-300 bg-base-200/50 hover:bg-base-200 transition-colors" title="Jika dicentang, akan menampilkan seluruh toko PJP termasuk yang tidak ada anomali">
                    <input type="checkbox" 
                           wire:click="toggleShowAllStores" 
                           @checked($showAllStores)
                           class="checkbox checkbox-xs checkbox-primary rounded" />
                    <span class="label-text text-[11px] font-medium text-base-content/80 select-none">Tampilkan Semua Toko</span>
                </label>

                {{-- Search Box --}}
                <div class="relative w-full sm:w-auto">
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari Toko / Kode / SE..."
                           class="input input-xs input-bordered rounded-xl bg-base-100 border-base-300 text-xs pl-7 pr-2 w-full sm:w-48 focus:outline-none focus:border-primary" />
                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 absolute left-2 top-1/2 -translate-y-1/2 text-base-content/40" />
                </div>
            </div>
        </div>

        {{-- Matriks Table Container --}}
        <div class="flex-1 min-h-0 overflow-auto bg-base-100 flex flex-col">
            @if(empty($appliedRegion) || empty($appliedArea))
                {{-- State: Filter Belum Diterapkan --}}
                <div class="h-full flex flex-col items-center justify-center p-8 text-center my-auto">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary mb-3 shadow-inner">
                        <x-heroicon-o-funnel class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-base-content">Terapkan Filter Terlebih Dahulu</h3>
                    <p class="text-xs text-base-content/60 max-w-md mt-1">
                        Pilih <strong>Bulan, Tahun, Region, dan Area</strong> di atas, lalu klik tombol 
                        <span class="inline-flex items-center font-semibold text-primary">Terapkan</span> 
                        untuk memuat matriks evaluasi mingguan toko berdasarkan kalender kerja.
                    </p>
                </div>
            @elseif($stores->isEmpty())
                {{-- State: Data Kosong --}}
                <div class="h-full flex flex-col items-center justify-center p-8 text-center my-auto">
                    <div class="w-16 h-16 rounded-2xl bg-base-200 flex items-center justify-center text-base-content/40 mb-3">
                        <x-heroicon-o-check-badge class="w-8 h-8 text-success" />
                    </div>
                    <h3 class="text-base font-bold text-base-content">Tidak Ada Data Toko Ditemukan</h3>
                    <p class="text-xs text-base-content/60 max-w-md mt-1">
                        @if(!$showAllStores)
                            Tidak ada toko yang memiliki temuan anomali pada filter terpilih. Centang opsi 
                            <strong>"Tampilkan Semua Toko"</strong> untuk melihat seluruh riwayat kunjungan toko normal.
                        @else
                            Tidak ada catatan kunjungan toko PJP yang cocok dengan kata kunci pencarian atau kriteria filter saat ini.
                        @endif
                    </p>
                </div>
            @else
                {{-- Matriks Mingguan Table --}}
                <table class="table table-xs table-pin-rows table-pin-cols w-full border-collapse">
                    <thead class="text-xs uppercase font-bold tracking-wider text-base-content/70 bg-base-200 border-b border-base-300">
                        <tr>
                            <th class="w-10 text-center bg-base-200">#</th>
                            <th class="w-48 bg-base-200">Salesman (SE)</th>
                            <th class="min-w-[240px] bg-base-200">Toko / Outlet & Status Master</th>
                            
                            {{-- Dynamic Weeks from master_calender (M1..M4/M5) --}}
                            @foreach($weeksMap as $wNum => $wInfo)
                                <th class="text-center min-w-[150px] bg-base-200 border-l border-base-300/60">
                                    <div class="font-bold text-base-content">{{ $wInfo['label'] }}</div>
                                    <div class="text-[10px] font-mono font-normal text-base-content/60 lowercase tracking-normal">{{ $wInfo['range_label'] }}</div>
                                </th>
                            @endforeach

                            <th class="text-center w-36 bg-base-200 border-l border-base-300/60">Status Tren</th>
                            <th class="text-right w-32 bg-base-200 border-l border-base-300/60">Total Order (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-base-200">
                        @foreach($stores as $idx => $store)
                            <tr class="hover:bg-base-200/50 transition-colors {{ $store->trend_status === 'recurrent' ? 'bg-rose-500/5' : '' }}">
                                {{-- Index No --}}
                                <th class="text-center font-mono text-base-content/40 bg-base-100">
                                    {{ ($stores->currentPage() - 1) * $stores->perPage() + $idx + 1 }}
                                </th>

                                {{-- Salesman & Distributor --}}
                                <td class="bg-base-100">
                                    <div class="font-bold text-base-content truncate max-w-[180px]" title="{{ $store->salesman_name }}">
                                        {{ $store->salesman_name }}
                                    </div>
                                    <div class="text-[10px] text-base-content/60 truncate max-w-[180px]" title="{{ $store->distributor_name }}">
                                        {{ $store->distributor_name }}
                                    </div>
                                </td>

                                {{-- Toko & GPS Master Info --}}
                                <td class="bg-base-100">
                                    <div class="font-semibold text-base-content truncate max-w-[240px]" title="{{ $store->custname }}">
                                        {{ $store->custname }}
                                    </div>
                                    <div class="text-[10px] text-base-content/50 font-mono flex items-center gap-1.5 flex-wrap mt-0.5">
                                        <span>{{ $store->custno }}</span>
                                        <span class="text-base-content/30">&bull;</span>
                                        
                                        @if($store->has_master_gps)
                                            <span class="badge badge-xs badge-success badge-outline gap-0.5 text-[9px] font-semibold" title="Titik GPS Master terdaftar valid: ({{ number_format($store->master_lat, 5) }}, {{ number_format($store->master_lon, 5) }})">
                                                <span>📍 Master Ada</span>
                                            </span>
                                        @else
                                            <span class="badge badge-xs badge-error text-white gap-0.5 text-[9px] font-bold shadow-2xs" title="PERINGATAN: Koordinat Master Toko KOSONG (0, 0). Geofence mobile tidak dapat memblokir check-in toko ini!">
                                                <span>🚨 Master Kosong (0,0)</span>
                                            </span>
                                        @endif
                                    </div>
                                    @if(!empty($store->address))
                                        <div class="text-[10px] text-base-content/40 truncate max-w-[240px] mt-0.5" title="{{ $store->address }}">
                                            {{ $store->address }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Dynamic Week Columns (M1..M4/M5) --}}
                                @foreach($weeksMap as $wNum => $wInfo)
                                    @php
                                        $wData = $store->weeks[$wNum] ?? null;
                                    @endphp
                                    <td class="text-center align-middle border-l border-base-200/70 p-2">
                                        @if(!$wData || $wData->status === 'skip')
                                            {{-- Tidak ada jadwal / tidak dikunjungi --}}
                                            <div class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[10px] font-mono text-base-content/30 bg-base-200/40" title="Tidak ada kunjungan pada minggu ini">
                                                <span>&minus; Tidak Ada</span>
                                            </div>
                                        @elseif($wData->status === 'normal')
                                            {{-- Normal & Patuh SOP --}}
                                            <div class="inline-flex flex-col items-center gap-0.5">
                                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 shadow-2xs" 
                                                     title="{{ $wData->detail }} • Kunjungan Sah">
                                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                                                    <span>{{ $wData->label }}</span>
                                                </div>
                                                @if($wData->total_val_order > 0)
                                                    <span class="text-[10px] font-mono text-emerald-600 font-semibold">
                                                        Rp {{ number_format($wData->total_val_order, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            {{-- Ada Temuan Anomali --}}
                                            <div class="inline-flex flex-col items-center gap-1">
                                                @php
                                                    $btnClass = match($wData->severity) {
                                                        'high' => 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-500/30 hover:bg-rose-500/25',
                                                        'medium' => 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30 hover:bg-amber-500/25',
                                                        default => 'bg-slate-500/15 text-slate-700 dark:text-slate-300 border-slate-500/30 hover:bg-slate-500/25'
                                                    };
                                                    $iconColor = match($wData->severity) {
                                                        'high' => 'text-rose-600',
                                                        'medium' => 'text-amber-600',
                                                        default => 'text-slate-500'
                                                    };
                                                    $storeSummary = [
                                                        'custname' => $store->custname,
                                                        'custno' => $store->custno,
                                                        'salesman_name' => $store->salesman_name,
                                                        'distributor_name' => $store->distributor_name,
                                                        'has_master_gps' => $store->has_master_gps,
                                                    ];
                                                @endphp
                                                <button type="button" 
                                                    @click="openDetail(@js($storeSummary), {{ $wNum }}, @js($wData))"
                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-bold border transition-all cursor-pointer shadow-2xs group text-left {{ $btnClass }}" 
                                                    title="{{ $wData->detail }} (Klik untuk rincian temuan)">
                                                    
                                                    @if($wData->type === 'at_distributor')
                                                        <x-heroicon-s-building-office class="w-3.5 h-3.5 shrink-0 {{ $iconColor }} group-hover:scale-110 transition-transform" />
                                                    @elseif($wData->type === 'duplicate_coord')
                                                        <x-heroicon-s-document-duplicate class="w-3.5 h-3.5 shrink-0 {{ $iconColor }} group-hover:scale-110 transition-transform" />
                                                    @elseif($wData->type === 'quick_visit')
                                                        <x-heroicon-s-clock class="w-3.5 h-3.5 shrink-0 {{ $iconColor }} group-hover:scale-110 transition-transform" />
                                                    @elseif($wData->type === 'out_of_radius')
                                                        <x-heroicon-s-map-pin class="w-3.5 h-3.5 shrink-0 {{ $iconColor }} group-hover:scale-110 transition-transform" />
                                                    @else
                                                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5 shrink-0 {{ $iconColor }} group-hover:scale-110 transition-transform" />
                                                    @endif

                                                    <span class="truncate max-w-[110px]">{{ $wData->label }}</span>

                                                    @if(count($wData->anomalies) > 1)
                                                        <span class="badge badge-xs {{ $wData->severity === 'high' ? 'badge-error' : 'badge-warning' }} text-white font-mono px-1 text-[9px]">
                                                            +{{ count($wData->anomalies) - 1 }}
                                                        </span>
                                                    @endif
                                                </button>

                                                @if($wData->total_val_order > 0)
                                                    <span class="text-[10px] font-mono text-primary font-semibold">
                                                        Rp {{ number_format($wData->total_val_order, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Status Tren Perbaikan --}}
                                <td class="text-center align-middle border-l border-base-200/70 p-2">
                                    @if($store->trend_status === 'solved')
                                        <span class="badge badge-sm badge-success text-white font-bold gap-1 shadow-2xs" title="Sebelumnya anomali, namun di minggu terakhir telah normal (Perbaikan Berhasil)">
                                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                            <span>🟢 Membaik</span>
                                        </span>
                                    @elseif($store->trend_status === 'recurrent')
                                        <span class="badge badge-sm badge-error text-white font-bold gap-1 shadow-2xs" title="Anomali berulang di >= 2 minggu dan belum diperbaiki">
                                            <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                            <span>🔴 Kronis</span>
                                        </span>
                                    @elseif($store->trend_status === 'new')
                                        <span class="badge badge-sm badge-warning text-warning-content font-bold gap-1 shadow-2xs" title="Baru terdeteksi ada anomali di kunjungan minggu terakhir">
                                            <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" />
                                            <span>🟡 Baru</span>
                                        </span>
                                    @elseif($store->trend_status === 'normal')
                                        <span class="badge badge-sm badge-ghost text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 font-semibold gap-1" title="Seluruh minggu kunjungan patuh SOP">
                                            <x-heroicon-s-shield-check class="w-3.5 h-3.5 text-emerald-600" />
                                            <span>✅ Normal</span>
                                        </span>
                                    @else
                                        <span class="badge badge-sm badge-ghost text-base-content/40 font-mono">
                                            <span>⚪ Lewat</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Total Order (Rp) --}}
                                <td class="text-right font-mono align-middle border-l border-base-200/70 p-2">
                                    @if($store->total_val_order > 0)
                                        <span class="font-bold text-primary">
                                            Rp {{ number_format($store->total_val_order, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-base-content/30 font-normal">Rp 0</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Footer Toolbar & Pagination --}}
        @if(!empty($appliedRegion) && !empty($appliedArea) && $stores->isNotEmpty())
            <div class="p-3 border-t border-base-300 bg-base-200/40 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-base-content/70">
                    <span>Menampilkan <strong class="font-bold text-base-content">{{ $stores->firstItem() }} - {{ $stores->lastItem() }}</strong> dari <strong class="font-bold text-base-content">{{ number_format($stores->total()) }}</strong> Toko</span>
                    <span class="text-base-content/30">&bull;</span>
                    <div class="flex items-center gap-1">
                        <span>Per halaman:</span>
                        <select wire:model.live="perPage" class="select select-xs select-bordered rounded-lg bg-base-100 border-base-300">
                            <option value="15">15 Toko</option>
                            <option value="25">25 Toko</option>
                            <option value="50">50 Toko</option>
                            <option value="100">100 Toko</option>
                        </select>
                    </div>
                </div>

                <div>
                    {{ $stores->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Detail Anomali & Kunjungan Modal (Alpine) --}}
    <div class="modal" :class="{'modal-open': detailModalOpen}">
        <div class="modal-box w-11/12 max-w-2xl rounded-2xl p-0 overflow-hidden border border-base-300 shadow-2xl flex flex-col max-h-[90vh]">
            
            {{-- Modal Header --}}
            <div class="p-4 border-b border-base-300 bg-base-200/60 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                        <x-heroicon-s-clipboard-document-check class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-base-content flex items-center gap-2">
                            <span>Detail Temuan Minggu Ke-</span>
                            <span class="badge badge-primary font-mono font-bold" x-text="activeWeekNum ? 'M' + activeWeekNum : ''"></span>
                        </h3>
                        <p class="text-xs text-base-content/60" x-text="activeStore ? activeStore.custname + ' (' + activeStore.custno + ')' : ''"></p>
                    </div>
                </div>
                <button type="button" @click="closeDetail()" class="btn btn-sm btn-ghost btn-circle">✕</button>
            </div>

            {{-- Modal Body --}}
            <div class="p-4 overflow-y-auto space-y-4 text-xs">
                
                {{-- Info Toko & Salesman Card --}}
                <div class="bg-base-200/40 p-3 rounded-xl border border-base-300 grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-base-content/50 block">Salesman (SE)</span>
                        <span class="font-bold text-base-content text-sm" x-text="activeStore ? activeStore.salesman_name : '-'"></span>
                        <div class="text-base-content/60 text-[11px]" x-text="activeStore ? activeStore.distributor_name : '-'"></div>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-base-content/50 block">Status Master GPS Toko</span>
                        <template x-if="activeStore && activeStore.has_master_gps">
                            <div class="flex items-center gap-1 text-emerald-600 font-semibold mt-0.5">
                                <x-heroicon-s-check-circle class="w-4 h-4 shrink-0" />
                                <span>Master GPS Terdaftar</span>
                            </div>
                        </template>
                        <template x-if="activeStore && !activeStore.has_master_gps">
                            <div class="flex items-center gap-1 text-rose-600 font-bold mt-0.5">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4 shrink-0" />
                                <span>Master Kosong (0, 0) - Perlu Titik Baru!</span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Ringkasan Kunjungan Terakhir di Minggu Ini --}}
                <template x-if="activeWeekData && activeWeekData.latest_visit">
                    <div class="bg-base-100 p-3 rounded-xl border border-base-300 space-y-2">
                        <div class="font-bold text-base-content uppercase tracking-wider text-[10px]">Data Check-in Kunjungan</div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                            <div class="bg-base-200/50 p-2 rounded-lg">
                                <span class="text-[10px] text-base-content/50 block">Tanggal</span>
                                <span class="font-mono font-bold" x-text="activeWeekData.latest_visit.tanggal"></span>
                            </div>
                            <div class="bg-base-200/50 p-2 rounded-lg">
                                <span class="text-[10px] text-base-content/50 block">Jam Masuk - Keluar</span>
                                <span class="font-mono font-bold" x-text="activeWeekData.latest_visit.time_in + ' - ' + activeWeekData.latest_visit.time_out"></span>
                            </div>
                            <div class="bg-base-200/50 p-2 rounded-lg">
                                <span class="text-[10px] text-base-content/50 block">Durasi Toko</span>
                                <span class="font-mono font-bold text-primary" x-text="activeWeekData.latest_visit.time_consume || '-'"></span>
                            </div>
                            <div class="bg-base-200/50 p-2 rounded-lg">
                                <span class="text-[10px] text-base-content/50 block">Nilai Order (Rp)</span>
                                <span class="font-mono font-bold text-emerald-600" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(activeWeekData.latest_visit.val_order || 0)"></span>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Daftar Pelanggaran / Anomali --}}
                <div class="space-y-2">
                    <div class="font-bold text-base-content uppercase tracking-wider text-[10px] flex items-center justify-between">
                        <span>Daftar Pelanggaran SOP Terdeteksi</span>
                        <span class="badge badge-xs badge-neutral" x-text="activeWeekData && activeWeekData.anomalies ? activeWeekData.anomalies.length + ' Indikasi' : '0'"></span>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(anom, idx) in (activeWeekData ? activeWeekData.anomalies : [])" :key="idx">
                            <div class="p-3 rounded-xl border flex items-start gap-3 transition-colors"
                                 :class="anom.severity === 'high' ? 'bg-rose-500/5 border-rose-500/30' : (anom.severity === 'medium' ? 'bg-amber-500/5 border-amber-500/30' : 'bg-slate-500/5 border-slate-500/30')">
                                
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                                     :class="anom.severity === 'high' ? 'bg-rose-500/10 text-rose-600' : (anom.severity === 'medium' ? 'bg-amber-500/10 text-amber-600' : 'bg-slate-500/10 text-slate-600')">
                                    <template x-if="anom.severity === 'high'">
                                        <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                                    </template>
                                    <template x-if="anom.severity !== 'high'">
                                        <x-heroicon-s-exclamation-circle class="w-5 h-5" />
                                    </template>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-base-content" x-text="anom.label"></span>
                                        <span class="badge badge-xs text-white uppercase font-bold"
                                              :class="anom.severity === 'high' ? 'badge-error' : (anom.severity === 'medium' ? 'badge-warning text-warning-content' : 'badge-neutral')"
                                              x-text="anom.severity === 'high' ? 'Kritis' : (anom.severity === 'medium' ? 'Sedang' : 'Rendah')">
                                        </span>
                                    </div>
                                    <p class="text-xs text-base-content/80 mt-1 font-medium" x-text="anom.detail"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Evaluasi & Tindakan yang Disarankan --}}
                <div class="p-3 rounded-xl bg-blue-500/5 border border-blue-500/20 text-blue-900 dark:text-blue-200">
                    <div class="font-bold flex items-center gap-1.5 text-xs text-blue-700 dark:text-blue-300">
                        <x-heroicon-s-information-circle class="w-4 h-4 shrink-0" />
                        <span>Rekomendasi Tindak Lanjut untuk Supervisor (SPV):</span>
                    </div>
                    <ul class="list-disc list-inside mt-1.5 space-y-1 text-[11px] text-base-content/80">
                        <li>Jika anomali <strong>Di Depo</strong> dengan <strong>Master Kosong</strong>, minta SE/Admin segera melakukan retagging titik koordinat toko fisik.</li>
                        <li>Jika anomali <strong>Koordinat Kembar</strong>, pastikan SE mendatangi langsung lokasi toko fisik dan tidak melakukan check-in borongan dari satu tempat.</li>
                        <li>Jika anomali <strong>Kunjungan Kilat (&lt; 2 menit)</strong>, evaluasi kepatuhan proses selling, pengecekan stok display, dan interaksi dengan pemilik toko.</li>
                    </ul>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="p-3 border-t border-base-300 bg-base-200/50 flex justify-end shrink-0">
                <button type="button" @click="closeDetail()" class="btn btn-sm btn-ghost rounded-xl">Tutup</button>
            </div>
        </div>
    </div>
</div>
