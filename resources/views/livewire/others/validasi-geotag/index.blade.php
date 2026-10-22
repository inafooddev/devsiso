<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-5 w-full h-full" 
    x-data="{
        cartoApiKey: @js($cartoApiKey),
        showKpi: true,
        showDetailModal: false,
        activeTab: 'streetview',
        storeData: null,
        streetViewUrl: '',
        satelliteUrl: '',
        externalMapUrl: '',
        leafletMap: null,
        mapMarkers: [],
        circleLayer: null,

        openModalWithStore(item) {
            if (!item) return;
            this.storeData = item;
            this.showDetailModal = true;

            let la = item.la ? parseFloat(item.la) : null;
            let lg = item.lg ? parseFloat(item.lg) : null;
            let distLa = item.dist_latitude ? parseFloat(item.dist_latitude) : null;
            let distLg = item.dist_longitude ? parseFloat(item.dist_longitude) : null;

            if (la && lg && !isNaN(la) && !isNaN(lg) && la !== 0 && lg !== 0) {
                this.streetViewUrl = `https://www.google.com/maps?layer=c&cbll=${la},${lg}&output=svembed`;
                this.satelliteUrl = `https://maps.google.com/maps?q=${la},${lg}&t=k&z=19&output=embed`;
                this.externalMapUrl = `https://www.google.com/maps/search/?api=1&query=${la},${lg}`;
                this.activeTab = 'streetview';
            } else {
                this.streetViewUrl = '';
                this.satelliteUrl = '';
                this.externalMapUrl = '';
                this.activeTab = 'leaflet';
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

            this.$nextTick(() => {
                this.setupLeaflet(la, lg, distLa, distLg, item.custname, item.distributor_name);
            });
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
        },

        setupLeaflet(la, lg, distLa, distLg, custName, distName) {
            if (!window.L) return;

            let container = document.getElementById('geotag-leaflet-container');
            if (!container) return;

            try {
                if (!this.leafletMap) {
                    if (container._leaflet_id) {
                        delete container._leaflet_id;
                    }
                    this.leafletMap = L.map(container, {
                        zoomControl: true,
                        attributionControl: true
                    }).setView([-6.2, 106.8], 14);

                    let cartoUrl = this.cartoApiKey 
                        ? `https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=${this.cartoApiKey}`
                        : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

                    L.tileLayer(cartoUrl, {
                        maxZoom: 20,
                        attribution: '&copy; OpenStreetMap &copy; CARTO'
                    }).addTo(this.leafletMap);
                }

                setTimeout(() => {
                    if (this.leafletMap) {
                        this.leafletMap.invalidateSize();
                    }
                }, 200);

                // Clear previous layers
                this.mapMarkers.forEach(m => this.leafletMap.removeLayer(m));
                this.mapMarkers = [];
                if (this.circleLayer) {
                    this.leafletMap.removeLayer(this.circleLayer);
                    this.circleLayer = null;
                }

                let bounds = [];

                // Marker Gudang Distributor
                if (distLa && distLg && !isNaN(distLa) && !isNaN(distLg)) {
                    let distIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div class='w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shadow-lg border-2 border-white ring-2 ring-rose-300'>Depo</div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });

                    let mDist = L.marker([distLa, distLg], { icon: distIcon })
                        .addTo(this.leafletMap)
                        .bindPopup(`<b>Kantor/Gudang Depo:</b><br>${distName || 'Distributor'}`);
                    this.mapMarkers.push(mDist);
                    bounds.push([distLa, distLg]);

                    // Lingkaran radius 100 meter (Zona Larangan Geotag Kantor)
                    this.circleLayer = L.circle([distLa, distLg], {
                        color: '#e11d48',
                        fillColor: '#f43f5e',
                        fillOpacity: 0.2,
                        radius: 100
                    }).addTo(this.leafletMap).bindPopup('Radius 100m Kantor/Gudang Distributor');
                }

                // Marker Toko
                if (la && lg && !isNaN(la) && !isNaN(lg) && la !== 0 && lg !== 0) {
                    let storeIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div class='w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs shadow-lg border-2 border-white ring-2 ring-primary/40'>Toko</div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });

                    let mStore = L.marker([la, lg], { icon: storeIcon })
                        .addTo(this.leafletMap)
                        .bindPopup(`<b>${custName || 'Toko'}</b><br>Lat: ${la}<br>Long: ${lg}`);
                    this.mapMarkers.push(mStore);
                    bounds.push([la, lg]);

                    // Garis jarak toko ke distributor
                    if (distLa && distLg && !isNaN(distLa) && !isNaN(distLg)) {
                        let line = L.polyline([[la, lg], [distLa, distLg]], {
                            color: '#6366f1',
                            dashArray: '6, 6',
                            weight: 2.5
                        }).addTo(this.leafletMap);
                        this.mapMarkers.push(line);
                    }
                }

                if (bounds.length > 0) {
                    this.leafletMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 17 });
                }
            } catch (err) {
                console.error('Leaflet setup error:', err);
            }
        }
    }"
    x-on:open-verification-modal.window="initModal($event.detail)"
    x-on:close-verification-modal.window="showDetailModal = false"
>
    <x-slot name="title">Validasi Geotag Titik Koordinat Toko</x-slot>

    {{-- HEADER & TOGGLE KPI --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2.5 bg-base-100 border-b border-base-300 flex items-center justify-between shadow-sm relative z-10">
        <div class="flex items-center gap-2">
            <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                <x-heroicon-s-map-pin class="w-5 h-5" />
            </div>
            <div>
                <h1 class="text-base font-bold text-base-content leading-tight">Validasi Geotag Titik Koordinat Toko</h1>
                <p class="text-xs text-base-content/60">Verifikasi keabsahan koordinat fisik toko & deteksi anomali geotag salesman</p>
            </div>
        </div>

        {{-- Toggle KPI Button --}}
        <button 
            type="button" 
            @click="showKpi = !showKpi" 
            class="btn btn-xs btn-ghost gap-1.5 rounded-lg border border-base-300 text-xs font-semibold hover:bg-base-200 transition-all duration-200"
            :class="{ 'bg-primary/10 text-primary border-primary/30 shadow-xs': showKpi, 'text-base-content/60': !showKpi }"
            title="Sembunyikan / Tampilkan Ringkasan Metrik"
        >
            <x-heroicon-o-chart-bar class="w-3.5 h-3.5" />
            <span x-text="showKpi ? 'Sembunyikan Ringkasan' : 'Tampilkan Ringkasan'">Tampilkan Ringkasan</span>
            <span class="inline-block transition-transform duration-200" :class="{ 'rotate-180': showKpi }">
                <x-heroicon-o-chevron-down class="w-3 h-3" />
            </span>
        </button>
    </div>

    {{-- 4 CONSOLIDATED SUMMARY CARDS (KPI METRICS) --}}
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

    {{-- FILTER TOOLBAR SECTION --}}
    <div class="bg-base-100 p-3 rounded-xl shadow-xs border border-base-300 flex flex-col gap-2.5 shrink-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
            {{-- Search Box --}}
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.400ms="search" 
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

            {{-- Region Filter --}}
            <div>
                <select wire:model.live="regionFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="">Semua Region</option>
                    @foreach($filterOptions['regions'] as $rCode => $rName)
                        <option value="{{ $rCode }}">{{ $rName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Area Filter --}}
            <div>
                <select wire:model.live="areaFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="">Semua Area</option>
                    @foreach($filterOptions['areas'] as $aCode => $aName)
                        <option value="{{ $aCode }}">{{ $aName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Distributor Filter --}}
            <div>
                <select wire:model.live="distributorFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="">Semua Distributor</option>
                    @foreach($filterOptions['distributors'] as $dCode => $dLabel)
                        <option value="{{ $dCode }}">{{ $dLabel }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Anomali Filter --}}
            <div>
                <select wire:model.live="anomalyFilter" class="select select-sm select-bordered w-full text-xs font-medium text-rose-600 font-semibold">
                    <option value="all" class="text-base-content font-normal">Semua Anomali</option>
                    <option value="at_distributor">⚠️ Titik di Depo (&le;100m)</option>
                    <option value="duplicate">👥 Duplikasi Titik di Cabang</option>
                    <option value="point_changed">🔄 Titik Berubah (Drift)</option>
                    <option value="missing_coordinate">📍 Tanpa Koordinat</option>
                </select>
            </div>

            {{-- Status Validasi Filter --}}
            <div>
                <select wire:model.live="validationFilter" class="select select-sm select-bordered w-full text-xs font-medium">
                    <option value="all">Semua Status Validasi</option>
                    <option value="unvalidated">Belum Divalidasi</option>
                    <option value="VALID">VALID</option>
                    <option value="TIDAK_VALID">TIDAK VALID</option>
                </select>
            </div>
        </div>

        {{-- Baris Filter Tambahan & Reset --}}
        <div class="flex items-center justify-between text-xs text-base-content/60 border-t border-base-200 pt-2">
            <div class="flex items-center gap-2">
                <span>Menampilkan <b>{{ number_format($stores->total()) }}</b> toko target</span>
                @if($search || $regionFilter || $areaFilter || $distributorFilter || $anomalyFilter !== 'all' || $validationFilter !== 'all')
                    <span class="badge badge-ghost badge-sm text-[11px] gap-1">
                        Filter Aktif
                        <button wire:click="resetFilters" class="hover:text-error">
                            <x-heroicon-s-x-mark class="w-3 h-3" />
                        </button>
                    </span>
                @endif
            </div>

            <button wire:click="resetFilters" class="btn btn-xs btn-ghost text-xs text-error font-medium hover:bg-error/10">
                <x-heroicon-o-arrow-path class="w-3 h-3" />
                Reset Filter
            </button>
        </div>
    </div>

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
                                    @if($st->is_at_distributor)
                                        <span class="badge badge-error badge-xs text-[10px] font-bold text-white gap-0.5">
                                            <x-heroicon-s-exclamation-triangle class="w-3 h-3" />
                                            Di Depo (&le;100m)
                                        </span>
                                    @endif

                                    @if($st->duplicate_count > 1)
                                        <span class="badge badge-warning badge-xs text-[10px] font-bold gap-0.5" title="Koordinat kembar dengan {{ $st->duplicate_count }} toko di cabang ini">
                                            <x-heroicon-s-user-group class="w-3 h-3" />
                                            Duplikat ({{ $st->duplicate_count }} toko)
                                        </span>
                                    @endif

                                    @if($st->is_point_changed)
                                        <span class="badge badge-secondary badge-xs text-[10px] font-bold gap-0.5">
                                            <x-heroicon-s-arrow-path class="w-3 h-3" />
                                            Titik Bergeser (Drift)
                                        </span>
                                    @endif

                                    @if($st->is_missing_coordinate)
                                        <span class="badge badge-ghost badge-xs text-[10px] text-base-content/60">
                                            Tanpa Koordinat
                                        </span>
                                    @endif

                                    @if(!$st->is_at_distributor && $st->duplicate_count <= 1 && !$st->is_point_changed && !$st->is_missing_coordinate)
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

            {{-- Anomali Alert Banner (Bila ada) --}}
            <template x-if="storeData?.is_at_distributor">
                <div class="bg-rose-50 border-b border-rose-200 px-5 py-2 flex items-center gap-2 text-rose-800 text-xs font-semibold">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-rose-600 shrink-0" />
                    <span>⚠️ <b>PERINGATAN:</b> Titik koordinat berada tepat di kantor/gudang distributor (<span x-text="storeData?.distance_to_dist_meters"></span> m). Kemungkinan salesman melakukan geotag saat di kantor!</span>
                </div>
            </template>
            <template x-if="storeData?.duplicate_count > 1">
                <div class="bg-amber-50 border-b border-amber-200 px-5 py-2 flex items-center gap-2 text-amber-900 text-xs font-semibold">
                    <x-heroicon-s-user-group class="w-4 h-4 text-amber-600 shrink-0" />
                    <span>👥 <b>DUPLIKASI:</b> Titik koordinat kembar identik digunakan oleh <span x-text="storeData?.duplicate_count"></span> toko berbeda di cabang ini!</span>
                </div>
            </template>
            <template x-if="storeData?.is_point_changed">
                <div class="bg-purple-50 border-b border-purple-200 px-5 py-2 flex items-center gap-2 text-purple-900 text-xs font-semibold">
                    <x-heroicon-s-arrow-path class="w-4 h-4 text-purple-600 shrink-0" />
                    <span>🔄 <b>TITIK BERGESER (DRIFT):</b> Titik lapangan berbeda dari titik sah tervalidasi sebelumnya (<span x-text="storeData?.validated_la + ', ' + storeData?.validated_lg"></span>).</span>
                </div>
            </template>

            {{-- Modal Body: Visualizer & Street View --}}
            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3">
                {{-- Tab Selector --}}
                <div class="flex items-center justify-between border-b border-base-300 pb-2">
                    <div class="tabs tabs-boxed bg-base-200 p-1">
                        <button 
                            type="button" 
                            @click="activeTab = 'streetview'" 
                            class="tab tab-xs font-bold" 
                            :class="{ 'tab-active bg-base-100 text-primary shadow-xs': activeTab === 'streetview' }"
                        >
                            📷 Google Street View
                        </button>
                        <button 
                            type="button" 
                            @click="activeTab = 'satellite'" 
                            class="tab tab-xs font-bold" 
                            :class="{ 'tab-active bg-base-100 text-primary shadow-xs': activeTab === 'satellite' }"
                        >
                            🛰️ Peta Satelit
                        </button>
                        <button 
                            type="button" 
                            @click="activeTab = 'leaflet'; $nextTick(() => leafletMap && leafletMap.invalidateSize())" 
                            class="tab tab-xs font-bold" 
                            :class="{ 'tab-active bg-base-100 text-primary shadow-xs': activeTab === 'leaflet' }"
                        >
                            🗺️ Peta Interaktif (Leaflet)
                        </button>
                    </div>

                    {{-- External Shortcut --}}
                    <template x-if="externalMapUrl">
                        <a :href="externalMapUrl" target="_blank" class="btn btn-xs btn-outline gap-1 text-xs">
                            <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                            Buka di Google Maps
                        </a>
                    </template>
                </div>

                {{-- Visualizer Display Area --}}
                <div class="w-full h-80 rounded-xl overflow-hidden border border-base-300 bg-base-200 relative">
                    {{-- Street View Iframe --}}
                    <div x-show="activeTab === 'streetview'" class="w-full h-full">
                        <template x-if="streetViewUrl">
                            <iframe 
                                :src="streetViewUrl" 
                                class="w-full h-full border-0" 
                                loading="lazy" 
                                allowfullscreen
                            ></iframe>
                        </template>
                        <template x-if="!streetViewUrl">
                            <div class="w-full h-full flex flex-col items-center justify-center text-base-content/50 text-xs">
                                <x-heroicon-o-no-symbol class="w-8 h-8 text-base-content/30 mb-1" />
                                Toko ini belum memiliki koordinat untuk menampilkan Street View.
                            </div>
                        </template>
                    </div>

                    {{-- Satellite Iframe --}}
                    <div x-show="activeTab === 'satellite'" class="w-full h-full">
                        <template x-if="satelliteUrl">
                            <iframe 
                                :src="satelliteUrl" 
                                class="w-full h-full border-0" 
                                loading="lazy"
                            ></iframe>
                        </template>
                        <template x-if="!satelliteUrl">
                            <div class="w-full h-full flex flex-col items-center justify-center text-base-content/50 text-xs">
                                <x-heroicon-o-no-symbol class="w-8 h-8 text-base-content/30 mb-1" />
                                Toko ini belum memiliki koordinat untuk menampilkan Satelit.
                            </div>
                        </template>
                    </div>

                    {{-- Leaflet Map Container --}}
                    <div x-show="activeTab === 'leaflet'" class="w-full h-full">
                        <div id="geotag-leaflet-container" class="w-full h-full z-0"></div>
                    </div>
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
</div>
