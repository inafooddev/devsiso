<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-5 w-full h-full" 
    x-data="{
        showKpi: true,
        showDetailModal: false,
        activeTab: 'streetview',
        storeData: null,
        streetViewUrl: '',
        satelliteUrl: '',
        externalMapUrl: '',

        openModalWithStore(item) {
            if (!item) return;
            this.storeData = item;
            this.showDetailModal = true;

            let la = item.la ? parseFloat(item.la) : null;
            let lg = item.lg ? parseFloat(item.lg) : null;

            if (la && lg && !isNaN(la) && !isNaN(lg) && la !== 0 && lg !== 0) {
                // Exact Street View embed structure matching Perbaikan Tikor: maps.google.com with q=&layer=c&cbll=...&cbp=11,0,0,0,0&output=svembed
                this.streetViewUrl = `https://maps.google.com/maps?q=&layer=c&cbll=${la},${lg}&cbp=11,0,0,0,0&output=svembed`;
                this.satelliteUrl = `https://maps.google.com/maps?q=${la},${lg}&t=k&z=19&output=embed`;
                this.externalMapUrl = `https://www.google.com/maps?q=${la},${lg}`;
                this.activeTab = 'streetview'; // Default directly to Street View like Perbaikan Tikor
            } else {
                this.streetViewUrl = '';
                this.satelliteUrl = '';
                this.externalMapUrl = '';
                this.activeTab = 'satellite';
            }

            // Sync data toko terpilih ke Livewire component
            if (this.$wire && this.$wire.setModalStore) {
                this.$wire.setModalStore(
                    item.kodecabang,
                    item.custno,
                    item.validation_notes || '',
                    la,
                    lg,
                    item.validated_la,
                    item.validated_lg
                );
            }
        },

        initModal(payload) {
            let data = payload;
            if (Array.isArray(payload)) {
                data = payload[0];
            }
            let store = data?.store ? data.store : data;
            if (store) {
                this.openModalWithStore(store);
            }
        }
    }"
    x-on:open-verification-modal.window="initModal($event.detail)"
    x-on:close-verification-modal.window="showDetailModal = false"
>
    <x-slot name="title">Validasi Geotag Titik Koordinat Toko</x-slot>

    {{-- TABS MENU (SUMMARY & DETAIL) --}}
    <x-ui.tab-menu>
        <x-ui.tab-item href="{{ route('others.validasi-geotag.summary') }}" :active="false">
            <span class="flex items-center gap-1.5 font-bold">
                <x-heroicon-s-chart-bar class="w-3.5 h-3.5" />
                Summary
            </span>
        </x-ui.tab-item>
        <x-ui.tab-item href="{{ route('others.validasi-geotag') }}" :active="true">
            <span class="flex items-center gap-1.5 font-bold">
                <x-heroicon-s-list-bullet class="w-3.5 h-3.5" />
                Detail
            </span>
        </x-ui.tab-item>

        <x-slot:actions>
            <button 
                type="button" 
                @click="showKpi = !showKpi" 
                class="btn btn-xs btn-ghost gap-1.5 rounded-lg border border-base-300 text-xs font-semibold hover:bg-base-200 transition-all duration-200"
                :class="{ 'bg-primary/10 text-primary border-primary/30 shadow-xs': showKpi, 'text-base-content/60': !showKpi }"
                title="Sembunyikan / Tampilkan Ringkasan Metrik"
            >
                <x-heroicon-o-chart-bar class="w-3.5 h-3.5" />
                <span x-text="showKpi ? 'Sembunyikan Metrik' : 'Tampilkan Metrik'">Tampilkan Metrik</span>
                <span class="inline-block transition-transform duration-200" :class="{ 'rotate-180': showKpi }">
                    <x-heroicon-o-chevron-down class="w-3 h-3" />
                </span>
            </button>

            @if($canExport)
                <button 
                    type="button" 
                    wire:click="export" 
                    wire:loading.attr="disabled"
                    class="btn btn-xs btn-success gap-1.5 rounded-lg text-white font-semibold shadow-xs hover:bg-emerald-600 transition-all duration-200"
                    title="Export Data Toko Sesuai Filter ke Excel (.xlsx)"
                >
                    <span wire:loading.remove wire:target="export" class="flex items-center gap-1.5">
                        <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" />
                        Export Excel
                    </span>
                    <span wire:loading wire:target="export" class="flex items-center gap-1.5">
                        <span class="loading loading-spinner loading-xs"></span>
                        Mengekspor...
                    </span>
                </button>
            @endif
        </x-slot:actions>
    </x-ui.tab-menu>

    {{-- 4 CONSOLIDATED SUMMARY CARDS (KPI METRICS) --}}
    @if($hasAppliedFilters)
    <div 
        x-show="showKpi" 
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 shrink-0"
    >
        {{-- Card 1: Populasi & Antrean --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-primary/40 transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-base-content/60 uppercase tracking-wider">Populasi & Progres</span>
                    <div class="text-2xl font-black text-base-content mt-1">{{ number_format($kpi['total_stores']) }}</div>
                    <div class="text-[11px] text-base-content/60 mt-0.5">Total Toko Aktif Rute</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-building-storefront class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-warning font-semibold flex items-center gap-1">
                    <x-heroicon-s-clock class="w-3.5 h-3.5" />
                    Belum Divalidasi:
                </span>
                <span class="badge badge-warning badge-sm font-bold">{{ number_format($kpi['unvalidated_stores']) }} ({{ $kpi['unvalidated_pct'] }}%)</span>
            </div>
        </div>

        {{-- Card 2: Hasil Keputusan Validasi --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-success/40 transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-base-content/60 uppercase tracking-wider">Keputusan Validasi</span>
                    <div class="text-2xl font-black text-success mt-1">{{ number_format($kpi['valid_stores']) }}</div>
                    <div class="text-[11px] text-success/80 mt-0.5 font-medium">Toko Sah (VALID)</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-error font-semibold flex items-center gap-1">
                    <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                    Tidak Valid / Ditolak:
                </span>
                <span class="badge badge-error badge-sm font-bold text-white">{{ number_format($kpi['invalid_stores']) }}</span>
            </div>
        </div>

        {{-- Card 3: Kelengkapan Koordinat --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-base-300 flex flex-col justify-between relative overflow-hidden group hover:border-info/40 transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-base-content/60 uppercase tracking-wider">Kelengkapan Titik</span>
                    <div class="text-2xl font-black text-info mt-1">{{ number_format($kpi['has_coordinate']) }}</div>
                    <div class="text-[11px] text-info/80 mt-0.5 font-medium">Toko Memiliki Koordinat</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-map-pin class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-base-200 flex items-center justify-between text-xs">
                <span class="text-base-content/60 font-medium flex items-center gap-1">
                    <x-heroicon-o-no-symbol class="w-3.5 h-3.5" />
                    Belum Ada Titik (Dummy):
                </span>
                <span class="badge badge-ghost badge-sm font-bold">{{ number_format($kpi['no_coordinate']) }}</span>
            </div>
        </div>

        {{-- Card 4: Pusat Peringatan & Anomali --}}
        <div class="bg-base-100 p-3.5 rounded-xl shadow-xs border border-rose-300/60 bg-gradient-to-br from-base-100 to-rose-50/30 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1">
                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                        Pusat Deteksi Anomali
                    </span>
                    <div class="text-2xl font-black text-rose-600 mt-1">
                        {{ number_format($kpi['at_distributor'] + $kpi['duplicate_stores'] + $kpi['drift_stores']) }}
                    </div>
                    <div class="text-[11px] text-base-content/60 mt-0.5">Total Temuan Lapangan</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <x-heroicon-s-shield-exclamation class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-rose-100 flex items-center gap-1.5 flex-wrap">
                <button wire:click="setAnomalyFilter('no_anomaly')" class="badge badge-xs {{ $anomalyFilter === 'no_anomaly' ? 'badge-success font-bold text-white shadow-xs' : 'badge-outline text-success hover:badge-success hover:text-white' }}" title="Tampilkan hanya toko yang bersih dari anomali & siap divalidasi">
                    ✅ Siap Validasi: {{ number_format($kpi['normal_stores'] ?? 0) }}
                </button>
                <button wire:click="setAnomalyFilter('at_distributor')" class="badge badge-xs {{ $anomalyFilter === 'at_distributor' ? 'badge-error font-bold text-white' : 'badge-outline text-rose-600 hover:badge-error' }}">
                    Depo (&le;100m): {{ number_format($kpi['at_distributor']) }}
                </button>
                <button wire:click="setAnomalyFilter('duplicate')" class="badge badge-xs {{ $anomalyFilter === 'duplicate' ? 'badge-error font-bold text-white' : 'badge-outline text-rose-600 hover:badge-error' }}">
                    Duplikat: {{ number_format($kpi['duplicate_stores']) }}
                </button>
                <button wire:click="setAnomalyFilter('point_changed')" class="badge badge-xs {{ $anomalyFilter === 'point_changed' ? 'badge-error font-bold text-white' : 'badge-outline text-rose-600 hover:badge-error' }}">
                    Drift: {{ number_format($kpi['drift_stores']) }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- FILTER TOOLBAR SECTION (CASCADING FILTER CHAIN & BUTTON TERAPKAN) --}}
    <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col gap-2.5 shrink-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
            {{-- Search Box --}}
            <div class="relative">
                <input 
                    type="text" 
                    wire:model="search" 
                    wire:keydown.enter="applyFilters"
                    placeholder="Cari toko / custno / alamat..." 
                    class="input input-sm input-bordered w-full pl-8 text-xs font-medium"
                />
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50 absolute left-2.5 top-2.5" />
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-2.5 top-2.5 text-base-content/40 hover:text-base-content">
                        <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif
            </div>

            {{-- 1. Region Filter (Level 1 Chain) --}}
            <div>
                <select wire:model.live="regionFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="">Semua Region</option>
                    @foreach($regions as $rCode => $rName)
                        <option value="{{ $rCode }}">{{ $rName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 2. Area Filter (Level 2 Chain) --}}
            <div>
                <select wire:model.live="areaFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="">Semua Area</option>
                    @foreach($areas as $aCode => $aName)
                        <option value="{{ $aCode }}">{{ $aName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- 3. Distributor Filter (Level 3 Chain) --}}
            <div>
                <select wire:model.live="distributorFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $dCode => $dLabel)
                        <option value="{{ $dCode }}">{{ $dLabel }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Anomali Filter --}}
            <div>
                <select wire:model="anomalyFilter" class="select select-sm select-bordered w-full text-xs font-medium {{ $anomalyFilter === 'no_anomaly' ? 'text-success font-bold' : ($anomalyFilter !== 'all' ? 'text-rose-600 font-bold' : '') }}">
                    <option value="all" class="text-base-content font-normal">Semua Kondisi Titik</option>
                    <option value="no_anomaly" class="text-success font-bold">✅ Siap Validasi (Tanpa Anomali)</option>
                    <option value="has_anomaly" class="text-rose-600 font-bold">⚠️ Semua Anomali</option>
                    <option value="at_distributor" class="text-rose-600">⚠️ Titik di Depo (&le;100m)</option>
                    <option value="duplicate" class="text-amber-600">👥 Duplikasi Titik di Cabang</option>
                    <option value="point_changed" class="text-purple-600">🔄 Titik Berubah (Drift)</option>
                    <option value="missing_coordinate" class="text-base-content/70">📍 Tanpa Koordinat</option>
                </select>
            </div>

            {{-- Status Validasi Filter --}}
            <div>
                <select wire:model="validationFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="all">Semua Status Validasi</option>
                    <option value="unvalidated">Belum Divalidasi</option>
                    <option value="VALID">VALID</option>
                    <option value="TIDAK_VALID">TIDAK VALID</option>
                </select>
            </div>
        </div>

        {{-- Baris Filter Action & Terapkan Button --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-base-content/60 border-t border-base-200 pt-2">
            <div class="flex items-center gap-2 flex-wrap">
                @if($hasAppliedFilters)
                    <span>Menampilkan <b>{{ number_format($stores->total()) }}</b> toko target</span>
                    @if($appliedRegion || $appliedArea || $appliedDistributor || $appliedSearch || $appliedAnomaly !== 'all' || $appliedValidation !== 'all')
                        <span class="badge badge-primary/10 text-primary border border-primary/20 badge-sm text-[11px] gap-1 font-medium">
                            Filter Aktif: 
                            @if($appliedRegion) {{ $regions[$appliedRegion] ?? $appliedRegion }} @endif
                            @if($appliedArea) &bull; {{ $areas[$appliedArea] ?? $appliedArea }} @endif
                            @if($appliedDistributor) &bull; {{ $appliedDistributor }} @endif
                            @if($appliedAnomaly !== 'all')
                                &bull; 
                                @if($appliedAnomaly === 'no_anomaly')
                                    <span class="text-success font-bold">Tanpa Anomali (Siap Validasi)</span>
                                @elseif($appliedAnomaly === 'has_anomaly')
                                    <span class="text-rose-600 font-bold">Semua Anomali</span>
                                @else
                                    {{ $appliedAnomaly }}
                                @endif
                            @endif
                            @if($appliedValidation !== 'all') &bull; Status: {{ $appliedValidation }} @endif
                        </span>
                    @endif
                @else
                    <div class="flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-medium text-xs">
                        <x-heroicon-s-information-circle class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Filter belum diterapkan. Pilih wilayah lalu klik tombol <b>Terapkan Filter</b> untuk memuat data.</span>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                <button 
                    type="button" 
                    wire:click="resetFilters" 
                    class="btn btn-xs btn-ghost text-xs text-error font-medium hover:bg-error/10 gap-1"
                >
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    Reset Filter
                </button>

                <button 
                    type="button" 
                    wire:click="applyFilters" 
                    wire:loading.attr="disabled"
                    class="btn btn-xs sm:btn-sm btn-primary gap-1.5 font-bold shadow-xs px-3"
                >
                    <x-heroicon-s-funnel class="w-3.5 h-3.5" wire:loading.remove wire:target="applyFilters" />
                    <span wire:loading wire:target="applyFilters" class="loading loading-spinner loading-xs"></span>
                    Terapkan Filter
                </button>

                @if($canExport)
                    <button 
                        type="button" 
                        wire:click="export" 
                        wire:loading.attr="disabled"
                        class="btn btn-xs sm:btn-sm btn-outline btn-success gap-1.5 font-bold shadow-xs px-3 hover:!text-white"
                        title="Download Data Toko Sesuai Filter ke File Excel (.xlsx)"
                    >
                        <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" wire:loading.remove wire:target="export" />
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export Excel
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(!$hasAppliedFilters)
        {{-- EMPTY STATE: FILTER BELUM DITERAPKAN --}}
        <div class="flex-1 min-h-[380px] bg-base-100 rounded-xl shadow-xs border border-base-300 flex flex-col items-center justify-center p-8 text-center">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center text-primary mb-3 shadow-inner">
                <x-heroicon-o-funnel class="w-8 h-8" />
            </div>
            <h3 class="text-base font-bold text-base-content mb-1">Filter Belum Diterapkan</h3>
            <p class="text-xs text-base-content/60 max-w-md mb-4 leading-relaxed">
                Tersedia puluhan ribu data toko. Silakan pilih Region, Area, atau Distributor pada baris filter di atas, kemudian klik tombol <b>Terapkan Filter</b> untuk memuat data dan analisis titik koordinat.
            </p>
            <button 
                type="button" 
                wire:click="applyFilters" 
                wire:loading.attr="disabled" 
                class="btn btn-sm btn-primary gap-1.5 font-bold shadow-xs px-4"
            >
                <x-heroicon-s-funnel class="w-4 h-4" wire:loading.remove wire:target="applyFilters" />
                <span wire:loading wire:target="applyFilters" class="loading loading-spinner loading-xs"></span>
                Terapkan Filter Sekarang
            </button>
        </div>
    @else
        {{-- DATA TABLE SECTION --}}
        <div class="flex-1 min-h-0 bg-base-100 rounded-xl shadow-xs border border-base-300 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-auto">
                <table class="table table-xs table-pin-rows table-zebra w-full text-xs">
                    <thead>
                        <tr class="bg-base-200 text-base-content/70 uppercase text-[11px] font-bold">
                            <th class="w-12 text-center">No</th>
                            <th>Cabang / Distributor</th>
                            <th>Custno & Nama Toko</th>
                            <th>Alamat & Kota</th>
                            <th>Koordinat Lapangan</th>
                            <th class="text-center">Jarak ke Depo</th>
                            <th>Status Anomali</th>
                            <th class="text-center">Status Validasi</th>
                            <th class="w-20 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $idx => $st)
                            <tr class="hover:bg-base-200/60 transition-colors">
                                <td class="text-center font-mono text-[11px] text-base-content/60">
                                    {{ $stores->firstItem() + $idx }}
                                </td>
                                <td>
                                    <div class="font-bold text-base-content">{{ $st->kodecabang }}</div>
                                    <div class="text-[11px] text-base-content/60 truncate max-w-[180px]">{{ $st->distributor_name ?: '-' }}</div>
                                </td>
                                <td>
                                    <div class="font-bold text-primary flex items-center gap-1">
                                        {{ $st->custname }}
                                        @if($st->typeout)
                                            <span class="badge badge-ghost badge-xs text-[10px]">{{ $st->typeout }}</span>
                                        @endif
                                    </div>
                                    <div class="font-mono text-[11px] text-base-content/60">{{ $st->custno }}</div>
                                </td>
                                <td>
                                    <div class="truncate max-w-[220px]" title="{{ $st->custadd1 }}">{{ $st->custadd1 }}</div>
                                    <div class="text-[11px] text-base-content/50">{{ $st->ccity }}</div>
                                </td>
                                <td>
                                    @if(!empty($st->la) && !empty($st->lg) && $st->la != 0)
                                        <div class="font-mono text-[11px] text-base-content font-semibold">{{ $st->la }}, {{ $st->lg }}</div>
                                    @else
                                        <span class="badge badge-error badge-outline badge-xs text-[10px]">Belum Ada Titik</span>
                                    @endif

                                    @if($st->validation_status === 'VALID' && $st->is_point_changed)
                                        <div class="text-[10px] text-warning font-semibold flex items-center gap-0.5 mt-0.5">
                                            <x-heroicon-s-arrow-path class="w-3 h-3" />
                                            Sah: {{ $st->validated_la }}, {{ $st->validated_lg }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!is_null($st->distance_to_dist_meters))
                                        @if($st->is_at_distributor)
                                            <span class="badge badge-error badge-sm text-[11px] font-bold text-white shadow-xs">
                                                {{ number_format($st->distance_to_dist_meters) }} m
                                            </span>
                                        @else
                                            <span class="font-mono text-[11px] text-base-content/80">
                                                @if($st->distance_to_dist_meters >= 1000)
                                                    {{ round($st->distance_to_dist_meters / 1000, 1) }} km
                                                @else
                                                    {{ number_format($st->distance_to_dist_meters) }} m
                                                @endif
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-base-content/40 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-col gap-1 items-start">
                                        @if(($st->primary_anomaly ?? null) === 'at_distributor' || $st->is_at_distributor)
                                            <span class="badge badge-error badge-xs text-[10px] font-bold text-white gap-0.5" title="Koordinat berada di kantor/gudang distributor (&le;100m)">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3" />
                                                Di Depo (&le;100m)
                                            </span>
                                        @elseif(($st->primary_anomaly ?? null) === 'missing_coordinate' || $st->is_missing_coordinate)
                                            <span class="badge badge-ghost badge-xs text-[10px] text-base-content/60">
                                                Tanpa Koordinat
                                            </span>
                                        @elseif(($st->primary_anomaly ?? null) === 'duplicate' || $st->duplicate_count > 1)
                                            <button 
                                                type="button" 
                                                wire:click="openDuplicateModal('{{ $st->kodecabang }}', {{ $st->la }}, {{ $st->lg }}, '{{ $st->custno }}', '{{ addslashes($st->custname) }}')"
                                                class="badge badge-warning badge-xs text-[10px] font-bold gap-1 hover:scale-105 active:scale-95 transition-all shadow-xs cursor-pointer border border-amber-400 group text-amber-950" 
                                                title="Klik untuk melihat daftar {{ $st->duplicate_count }} toko yang memiliki koordinat kembar ini"
                                            >
                                                <x-heroicon-s-user-group class="w-3 h-3 group-hover:scale-110" />
                                                <span>Duplikat ({{ $st->duplicate_count }} toko)</span>
                                                <x-heroicon-s-chevron-right class="w-2.5 h-2.5 opacity-70" />
                                            </button>
                                        @elseif(($st->primary_anomaly ?? null) === 'point_changed' || $st->is_point_changed)
                                            <span class="badge badge-secondary badge-xs text-[10px] font-bold gap-0.5">
                                                <x-heroicon-s-arrow-path class="w-3 h-3" />
                                                Titik Bergeser (Drift)
                                            </span>
                                        @else
                                            <span class="text-success text-[11px] font-semibold flex items-center gap-0.5">
                                                <x-heroicon-s-check class="w-3.5 h-3.5" /> Normal
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($st->validation_status === 'VALID')
                                        <span class="badge badge-success badge-sm font-bold text-white">VALID</span>
                                        @if($st->validator_name)
                                            <div class="text-[10px] text-base-content/50 mt-0.5">{{ $st->validator_name }}</div>
                                        @endif
                                    @elseif($st->validation_status === 'TIDAK_VALID')
                                        <span class="badge badge-error badge-sm font-bold text-white">TIDAK VALID</span>
                                        @if($st->validator_name)
                                            <div class="text-[10px] text-base-content/50 mt-0.5">{{ $st->validator_name }}</div>
                                        @endif
                                    @else
                                        <span class="badge badge-ghost badge-sm text-[11px] text-base-content/60">Belum Validasi</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button 
                                        type="button"
                                        @click="openModalWithStore(@js($st))" 
                                        class="btn btn-xs btn-primary gap-1 shadow-xs hover:scale-105 transition-transform"
                                    >
                                        <x-heroicon-s-eye class="w-3 h-3" />
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10 text-base-content/60">
                                    <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-base-content/30 mb-2" />
                                    <div class="font-semibold text-sm">Tidak ada data toko yang cocok dengan filter</div>
                                    <div class="text-xs text-base-content/40 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter wilayah.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION FOOTER --}}
            <div class="shrink-0 p-2.5 bg-base-100 border-t border-base-300 flex items-center justify-between text-xs">
                <div class="text-base-content/60">
                    Menampilkan {{ $stores->firstItem() ?? 0 }} - {{ $stores->lastItem() ?? 0 }} dari {{ number_format($stores->total()) }} toko
                </div>
                <div>
                    {{ $stores->links() }}
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VERIFIKASI MANUAL 1-PER-1 (NO BULK ACTION) --}}
    <div 
        x-show="showDetailModal" 
        style="display: none;"
        class="fixed inset-0 z-[99999] overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div 
            class="bg-base-100 rounded-2xl shadow-2xl border border-base-300 w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden relative"
            @click.away="showDetailModal = false"
        >
            {{-- Modal Header --}}
            <div class="px-5 py-3.5 bg-base-200/80 border-b border-base-300 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-primary badge-sm font-mono" x-text="storeData?.kodecabang"></span>
                        <h2 class="text-base font-bold text-base-content" x-text="storeData?.custname"></h2>
                        <span class="font-mono text-xs text-base-content/60" x-text="'(' + storeData?.custno + ')'"></span>
                    </div>
                    <div class="text-xs text-base-content/60 mt-0.5 truncate max-w-xl" x-text="storeData?.custadd1 + ' - ' + storeData?.ccity"></div>
                </div>

                <button @click="showDetailModal = false" class="btn btn-sm btn-circle btn-ghost text-base-content/60 hover:text-base-content">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Anomali Alert Banner (Prioritas Tunggal: Depo > Duplikat > Drift) --}}
            <template x-if="storeData?.is_at_distributor">
                <div class="bg-rose-50 border-b border-rose-200 px-5 py-2 flex items-center gap-2 text-rose-800 text-xs font-semibold">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-rose-600 shrink-0" />
                    <span>⚠️ <b>PERINGATAN:</b> Titik koordinat berada tepat di kantor/gudang distributor (<span x-text="storeData?.distance_to_dist_meters"></span> m). Kemungkinan salesman melakukan geotag saat di kantor!</span>
                </div>
            </template>
            <template x-if="!storeData?.is_at_distributor && storeData?.duplicate_count > 1">
                <div class="bg-amber-50 border-b border-amber-200 px-5 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-amber-900 text-xs font-semibold">
                    <div class="flex items-center gap-2">
                        <x-heroicon-s-user-group class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>👥 <b>DUPLIKASI:</b> Titik koordinat kembar identik digunakan oleh <span x-text="storeData?.duplicate_count"></span> toko berbeda di cabang ini!</span>
                    </div>
                    <button 
                        type="button" 
                        @click="$wire.openDuplicateModal(storeData?.kodecabang, storeData?.la, storeData?.lg, storeData?.custno, storeData?.custname)"
                        class="btn btn-xs btn-warning text-amber-950 font-bold gap-1 shadow-xs hover:scale-105 active:scale-95 shrink-0"
                    >
                        <x-heroicon-s-list-bullet class="w-3.5 h-3.5" />
                        <span>Lihat Toko Kembar (<span x-text="storeData?.duplicate_count"></span>)</span>
                    </button>
                </div>
            </template>
            <template x-if="!storeData?.is_at_distributor && storeData?.duplicate_count <= 1 && storeData?.is_point_changed">
                <div class="bg-purple-50 border-b border-purple-200 px-5 py-2 flex items-center gap-2 text-purple-900 text-xs font-semibold">
                    <x-heroicon-s-arrow-path class="w-4 h-4 text-purple-600 shrink-0" />
                    <span>🔄 <b>TITIK BERGESER (DRIFT):</b> Titik lapangan berbeda dari titik sah tervalidasi sebelumnya (<span x-text="storeData?.validated_la + ', ' + storeData?.validated_lg"></span>).</span>
                </div>
            </template>

            {{-- Modal Body: Visualizer & Street View --}}
            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3">
                {{-- Tab Selector --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-base-300 pb-2">
                    <div class="tabs tabs-boxed bg-base-200 p-1">
                        <button 
                            type="button" 
                            @click="activeTab = 'streetview'" 
                            class="tab tab-xs font-bold" 
                            :class="{ 'tab-active bg-base-100 text-primary shadow-xs': activeTab === 'streetview' }"
                        >
                            📷 Street View (360° Panorama)
                            <span class="badge badge-xs badge-info text-white font-bold ml-1 text-[10px]">Interaktif</span>
                        </button>
                        <button 
                            type="button" 
                            @click="activeTab = 'satellite'" 
                            class="tab tab-xs font-bold" 
                            :class="{ 'tab-active bg-base-100 text-primary shadow-xs': activeTab === 'satellite' }"
                        >
                            🛰️ Peta Satelit
                        </button>
                    </div>

                    {{-- External Shortcuts --}}
                    <div class="flex items-center gap-1.5 self-end sm:self-auto">
                        <template x-if="externalMapUrl">
                            <a :href="externalMapUrl" target="_blank" class="btn btn-xs btn-outline gap-1 text-xs">
                                <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                Google Maps
                            </a>
                        </template>
                        <template x-if="storeData?.la && storeData?.lg">
                            <a :href="'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' + storeData.la + ',' + storeData.lg" target="_blank" class="btn btn-xs btn-outline btn-primary gap-1 text-xs" title="Buka Street View di Tab Baru Google Maps">
                                <x-heroicon-o-camera class="w-3.5 h-3.5" />
                                Tab Baru
                            </a>
                        </template>
                    </div>
                </div>

                {{-- Visualizer Display Area (Ukuran Maksimal & Interaktif persis Perbaikan Tikor) --}}
                <div class="w-full h-[420px] rounded-xl overflow-hidden border border-base-300 bg-base-300 relative">
                    {{-- 1. Street View Iframe (Exact match with Perbaikan Tikor) --}}
                    <div x-show="activeTab === 'streetview'" class="w-full h-full relative">
                        <template x-if="streetViewUrl">
                            <iframe 
                                :src="streetViewUrl" 
                                class="w-full h-full border-0 absolute inset-0" 
                                loading="lazy" 
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                        </template>
                        <template x-if="!streetViewUrl">
                            <div class="w-full h-full flex flex-col items-center justify-center text-base-content/50 text-xs">
                                <x-heroicon-o-no-symbol class="w-8 h-8 text-base-content/30 mb-1" />
                                Toko ini belum memiliki koordinat untuk menampilkan Street View.
                            </div>
                        </template>
                    </div>

                    {{-- 2. Satellite Iframe --}}
                    <div x-show="activeTab === 'satellite'" class="w-full h-full relative">
                        <template x-if="satelliteUrl">
                            <iframe 
                                :src="satelliteUrl" 
                                class="w-full h-full border-0 absolute inset-0" 
                                loading="lazy"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                        </template>
                        <template x-if="!satelliteUrl">
                            <div class="w-full h-full flex flex-col items-center justify-center text-base-content/50 text-xs">
                                <x-heroicon-o-no-symbol class="w-8 h-8 text-base-content/30 mb-1" />
                                Toko ini belum memiliki koordinat untuk menampilkan Satelit.
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer Hint Visualizer (Matching Perbaikan Tikor) --}}
                <div class="flex items-center justify-between text-[11px] text-base-content/60 px-1 shrink-0">
                    <span x-show="activeTab === 'streetview'">*Putar 360° & zoom langsung di dalam panel tanpa perlu buka maps</span>
                    <span x-show="activeTab === 'satellite'">*Citra satelit Google resolusi tinggi untuk memverifikasi atap dan fisik bangunan</span>

                    <template x-if="externalMapUrl">
                        <a :href="externalMapUrl" target="_blank" class="text-primary hover:underline font-semibold flex items-center gap-1 shrink-0">
                            Buka di Google Maps →
                        </a>
                    </template>
                </div>

                {{-- Detail Info Koordinat & Verifikasi --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-base-200/50 p-3 rounded-xl border border-base-300">
                    <div>
                        <div class="text-base-content/50 font-medium">Titik Lapangan Terkini</div>
                        <div class="font-mono font-bold text-base-content text-sm mt-0.5" x-text="(storeData?.la && storeData?.lg) ? storeData.la + ', ' + storeData.lg : 'Belum ada titik'"></div>
                    </div>
                    <div>
                        <div class="text-base-content/50 font-medium">Jarak ke Kantor Depo</div>
                        <div class="font-semibold text-base-content text-sm mt-0.5" x-text="storeData?.distance_to_dist_meters ? storeData.distance_to_dist_meters + ' Meter' : '-'"></div>
                    </div>
                    <div>
                        <div class="text-base-content/50 font-medium">Status Validasi Saat Ini</div>
                        <div class="mt-0.5">
                            <span class="badge badge-sm font-bold text-white" 
                                  :class="{ 'badge-success': storeData?.validation_status === 'VALID', 'badge-error': storeData?.validation_status === 'TIDAK_VALID', 'badge-ghost text-base-content/60': !storeData?.validation_status }"
                                  x-text="storeData?.validation_status || 'Belum Divalidasi'"></span>
                        </div>
                    </div>
                </div>

                {{-- Input Catatan Verifikator --}}
                @if($canManage)
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-base-content/70">Catatan / Keterangan Verifikator (Opsional):</label>
                        <textarea 
                            wire:model="catatan" 
                            rows="2" 
                            placeholder="Tuliskan catatan verifikasi (misal: Toko fisik sesuai plang nama, atau titik palsu di kantor cabang)..."
                            class="textarea textarea-bordered textarea-sm w-full text-xs font-medium"
                        ></textarea>
                    </div>
                @endif
            </div>

            {{-- Modal Footer: Form Aksi Keputusan 1-per-1 --}}
            <div class="px-5 py-3 bg-base-200/80 border-t border-base-300 flex items-center justify-between">
                <div>
                    @if($canManage)
                        <template x-if="storeData?.is_point_changed">
                            <button 
                                type="button"
                                wire:click="resetToValidated(storeData?.kodecabang, storeData?.custno, storeData?.validated_la, storeData?.validated_lg)" 
                                class="btn btn-sm btn-warning gap-1 text-xs font-bold shadow-xs"
                                title="Kembalikan koordinat toko ke titik sah hasil validasi sebelumnya"
                            >
                                <x-heroicon-s-arrow-path class="w-4 h-4" />
                                Kembalikan ke Titik Sah
                            </button>
                        </template>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showDetailModal = false" class="btn btn-sm btn-ghost text-xs">
                        Tutup
                    </button>

                    @if($canManage)
                        <button 
                            type="button" 
                            wire:click="markAsInvalid(storeData?.kodecabang, storeData?.custno)" 
                            class="btn btn-sm btn-error text-white text-xs font-bold gap-1 shadow-sm"
                            title="Tandai koordinat toko ini sebagai TIDAK VALID / Fiktif"
                        >
                            <x-heroicon-s-x-mark class="w-4 h-4" />
                            Tolak (TIDAK VALID)
                        </button>

                        <button 
                            type="button" 
                            wire:click="markAsValid(storeData?.kodecabang, storeData?.custno, storeData?.la, storeData?.lg)" 
                            class="btn btn-sm btn-success text-white text-xs font-bold gap-1 shadow-sm"
                            title="Setujui dan kunci titik toko ini sebagai VALID"
                        >
                            <x-heroicon-s-check class="w-4 h-4" />
                            Setujui (VALID)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DAFTAR TOKO KOORDINAT DUPLIKAT --}}
    @if($showDuplicateModal)
        <div 
            class="fixed inset-0 z-[100000] overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs"
            x-data
            @keydown.window.escape="$wire.closeDuplicateModal()"
        >
            <div 
                class="bg-base-100 rounded-2xl shadow-2xl border border-base-300 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden relative"
                @click.away="$wire.closeDuplicateModal()"
            >
                {{-- Header Modal Duplikat --}}
                <div class="px-5 py-3.5 bg-amber-500/10 border-b border-amber-500/20 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-amber-500/20 text-amber-700 dark:text-amber-400">
                            <x-heroicon-s-user-group class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-base-content">Daftar Toko Koordinat Duplikat</h3>
                                <span class="badge badge-warning font-mono text-xs font-bold text-amber-950">
                                    {{ count($duplicateStores) }} Toko
                                </span>
                            </div>
                            <div class="text-xs text-base-content/60 flex items-center gap-2 mt-0.5">
                                <span>Cabang: <b class="font-mono text-base-content">{{ $duplicateKodecabang }}</b></span>
                                <span>&bull;</span>
                                <span>Titik: <b class="font-mono text-base-content">{{ $duplicateLa }}, {{ $duplicateLg }}</b></span>
                            </div>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        wire:click="closeDuplicateModal" 
                        class="btn btn-sm btn-circle btn-ghost text-base-content/60 hover:text-base-content"
                    >
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Banner Penjelasan --}}
                <div class="bg-amber-50 dark:bg-amber-950/20 px-5 py-2.5 border-b border-amber-200 dark:border-amber-900/30 text-xs text-amber-900 dark:text-amber-200 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <x-heroicon-s-information-circle class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Semua toko di bawah ini memiliki titik koordinat yang <b>persis sama</b>. Silakan periksa keabsahan fisik toko di lapangan.</span>
                    </div>
                    @if($duplicateLa && $duplicateLg)
                        <a 
                            href="https://www.google.com/maps?q={{ $duplicateLa }},{{ $duplicateLg }}" 
                            target="_blank" 
                            class="text-xs text-primary font-bold hover:underline flex items-center gap-1 shrink-0 ml-2"
                        >
                            <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                            Cek di Google Maps
                        </a>
                    @endif
                </div>

                {{-- Body Modal: Tabel Toko Kembar --}}
                <div class="flex-1 overflow-auto p-4">
                    @if($loadingDuplicates)
                        <div class="flex flex-col items-center justify-center py-12 text-center text-base-content/50">
                            <span class="loading loading-spinner loading-md text-primary mb-2"></span>
                            <span class="text-xs font-semibold">Memuat daftar toko duplikat...</span>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-base-300">
                            <table class="table table-xs table-zebra w-full text-xs">
                                <thead>
                                    <tr class="bg-base-200 text-base-content/70 uppercase text-[11px] font-bold">
                                        <th class="w-10 text-center">No</th>
                                        <th>Cust No & Nama Toko</th>
                                        <th>Alamat & Kota</th>
                                        <th class="text-center">Tipe Outlet</th>
                                        <th class="text-center">Jarak ke Depo</th>
                                        <th class="text-center">Status Validasi</th>
                                        <th class="w-28 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($duplicateStores as $i => $ds)
                                        @php
                                            $isSource = !empty($duplicateSourceCustno) && $duplicateSourceCustno === ($ds['custno'] ?? '');
                                        @endphp
                                        <tr class="hover:bg-base-200/60 {{ $isSource ? 'bg-amber-50/70 dark:bg-amber-950/30' : '' }}">
                                            <td class="text-center font-mono text-[11px] text-base-content/60">
                                                {{ $i + 1 }}
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="font-bold text-base-content {{ $isSource ? 'text-amber-800 dark:text-amber-300' : '' }}">
                                                        {{ $ds['custname'] ?? '-' }}
                                                    </span>
                                                    @if($isSource)
                                                        <span class="badge badge-warning badge-xs text-[10px] font-bold">Toko Terpilih</span>
                                                    @endif
                                                </div>
                                                <div class="font-mono text-[11px] text-base-content/50">
                                                    {{ $ds['custno'] ?? '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="max-w-xs truncate" title="{{ $ds['custadd1'] ?? '-' }}">
                                                    {{ $ds['custadd1'] ?? '-' }}
                                                </div>
                                                <div class="text-[11px] text-base-content/50">
                                                    {{ $ds['ccity'] ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($ds['typeout']))
                                                    <span class="badge badge-ghost badge-xs text-[10px] font-medium">
                                                        {{ $ds['typeout'] }}
                                                    </span>
                                                @else
                                                    <span class="text-base-content/30">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-mono text-[11px]">
                                                @if(isset($ds['distance_to_dist_meters']) && $ds['distance_to_dist_meters'] !== null)
                                                    @if($ds['is_at_distributor'] ?? false)
                                                        <span class="text-rose-600 font-bold" title="Berada di area kantor distributor (&le;100m)">
                                                            {{ number_format($ds['distance_to_dist_meters']) }} m ⚠️
                                                        </span>
                                                    @else
                                                        <span>{{ number_format($ds['distance_to_dist_meters']) }} m</span>
                                                    @endif
                                                @else
                                                    <span class="text-base-content/30">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(($ds['validation_status'] ?? null) === 'VALID')
                                                    <span class="badge badge-success badge-xs font-bold text-white">VALID</span>
                                                @elseif(($ds['validation_status'] ?? null) === 'TIDAK_VALID')
                                                    <span class="badge badge-error badge-xs font-bold text-white">TIDAK VALID</span>
                                                @else
                                                    <span class="badge badge-ghost badge-xs text-base-content/60">Belum Valid</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button 
                                                    type="button" 
                                                    wire:click="inspectStoreFromDuplicate('{{ $ds['kodecabang'] }}', '{{ $ds['custno'] }}')"
                                                    class="btn btn-xs btn-primary gap-1 shadow-xs hover:scale-105"
                                                    title="Buka panel verifikasi dan Street View untuk toko ini"
                                                >
                                                    <x-heroicon-s-eye class="w-3 h-3" />
                                                    Verifikasi
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-6 text-base-content/50">
                                                Tidak ditemukan toko lain dengan koordinat yang sama.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Footer Modal Duplikat --}}
                <div class="px-5 py-3 bg-base-200/80 border-t border-base-300 flex items-center justify-between text-xs">
                    <span class="text-base-content/60 text-[11px]">
                        *Klik tombol <b>Verifikasi</b> pada salah satu toko untuk membuka Street View dan melakukan validasi fisik.
                    </span>
                    <button 
                        type="button" 
                        wire:click="closeDuplicateModal" 
                        class="btn btn-sm btn-ghost text-xs"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
