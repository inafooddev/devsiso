<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Route Efficiency Analysis</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">SISO vs ESKA</a>
            <a href="{{ route('call-plan.jks-team-elite.route-efficiency') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100">Route Efficiency</a>
            <a href="{{ route('call-plan.jks-team-elite.clustering') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors">Clustering</a>
        </div>
    </div>

    {{-- Header / Filter Bar --}}
    <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 p-4 shrink-0 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold">Route Efficiency Analysis</h2>
            <p class="text-xs text-base-content/60">Evaluasi Jarak Rute Call Plan Harian (TSP)</p>
        </div>
        
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="w-full sm:w-48">
                <select wire:model.live="filterTeam" class="select select-sm select-bordered w-full rounded-xl bg-base-100">
                    @if(count($teams) == 0)
                        <option value="">-- Tidak ada team --</option>
                    @endif
                    @foreach($teams as $team)
                        <option value="{{ $team->kode_team }}">{{ $team->kode_team }} - {{ $team->nama_team }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <input wire:model.live="filterStartDate" type="date" class="input input-sm input-bordered w-full sm:w-36 rounded-xl bg-base-100" title="Tanggal Mulai">
                <span class="text-xs text-base-content/50">s/d</span>
                <input wire:model.live="filterEndDate" type="date" class="input input-sm input-bordered w-full sm:w-36 rounded-xl bg-base-100" title="Tanggal Akhir">
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    @if(count($routesByDate) > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 shrink-0">
        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-map class="w-5 h-5 text-primary" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Jarak</p>
            </div>
            <div class="text-base font-bold text-primary">{{ $totalDistance }} <span class="text-xs font-normal">Km</span></div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-arrows-pointing-out class="w-5 h-5 text-info" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Rata-rata</p>
            </div>
            <div class="text-base font-bold text-info">{{ $averageDistance }} <span class="text-xs font-normal">Km</span></div>
        </div>

        @php
            $statusColor = 'success';
            if (str_contains($efficiencyStatus, 'Wajar')) $statusColor = 'warning';
            if (str_contains($efficiencyStatus, 'Tersebar')) $statusColor = 'error';
        @endphp
        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-check-badge class="w-5 h-5 text-{{ $statusColor }}" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Status</p>
            </div>
            <div class="text-sm font-bold text-{{ $statusColor }} truncate">{{ $efficiencyStatus }}</div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-truck class="w-5 h-5 text-secondary" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Di Jalan</p>
            </div>
            <div class="text-sm font-bold text-secondary truncate">{{ $drivingDurationFormatted }}</div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-building-storefront class="w-5 h-5 text-accent" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Visit Toko</p>
            </div>
            <div class="text-sm font-bold text-accent truncate">{{ $visitDurationFormatted }}</div>
        </div>

        <div class="bg-base-100 rounded-xl p-3 border border-base-200 shadow-sm flex flex-col justify-center gap-1">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-base-content" />
                <p class="text-[0.65rem] text-base-content/60 font-semibold uppercase truncate">Est. Total Kerja</p>
            </div>
            <div class="text-sm font-bold text-base-content truncate">{{ $totalDurationFormatted }}</div>
        </div>
    </div>
    @else
        <div class="alert shadow-sm rounded-xl bg-base-200">
            <x-heroicon-o-information-circle class="w-6 h-6" />
            <span>Pilih Team dan Tanggal untuk melihat analisis. Pastikan toko memiliki koordinat (Latitude/Longitude).</span>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-4 flex-1 min-h-0">
        {{-- Map Container --}}
        <div class="w-full lg:w-2/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col relative z-0"
             x-data="routeMapDataInit(@js($routesByDate ?? []))"
             @route-analyzed.window="handleRouteAnalyzed($event.detail)">
            <div id="map-error-diag" class="absolute top-16 left-4 z-[999] bg-error text-white text-xs p-3 rounded-xl max-w-sm hidden shadow-xl border border-white/20">
                <span class="font-bold block mb-1">🚨 Map Diagnostics:</span>
                <span id="map-error-msg" class="font-mono">Initializing...</span>
            </div>
            <div x-ref="mapContainer" class="w-full h-[500px] lg:h-full flex-1 z-0" style="min-height: 500px;" wire:ignore></div>
            
            {{-- Floating Refresh Button --}}
            <button wire:click="analyzeRoute" class="absolute top-4 right-4 z-[400] btn btn-sm btn-primary shadow-lg rounded-xl flex items-center justify-center gap-1" title="Refresh Map" wire:loading.attr="disabled" wire:target="analyzeRoute">
                <x-heroicon-o-arrow-path class="w-4 h-4" wire:loading.class="animate-spin" wire:target="analyzeRoute" />
                <span class="hidden sm:inline">Refresh Map</span>
            </button>

            <div class="absolute bottom-4 right-4 bg-base-100/90 backdrop-blur p-2 rounded-lg border border-base-300 shadow-sm z-[400] text-xs" wire:ignore>
                <div class="font-bold mb-1 border-b border-base-200 pb-1">Tipe Garis</div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-4 h-1 bg-gray-500 rounded-full"></div>
                    <span>Rute Jalan Raya (API)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-1 border-dashed border border-transparent" style="border-top: 2px dashed #6b7280; background: none;"></div>
                    <span>Garis Lurus (Manual)</span>
                </div>
            </div>
        </div>

        {{-- Route Table --}}
        <div class="w-full lg:w-1/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col">
            <div class="p-3 border-b border-base-300 bg-base-200/50">
                <h3 class="font-bold text-sm">Urutan Kunjungan Optimal</h3>
                <p class="text-[0.65rem] text-base-content/60">Berdasarkan pendekatan rute terdekat</p>
            </div>
            <div class="flex-1 overflow-auto p-0">
                <table class="table table-sm table-zebra w-full text-xs">
                    <thead class="bg-base-200/50 sticky top-0">
                        <tr>
                            <th class="w-10">No</th>
                            <th>Toko</th>
                            <th class="text-right">Est. Berikutnya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routesByDate as $dailyRoute)
                            <tr class="bg-base-200/50">
                                <td colspan="3" class="font-bold text-xs" style="color: {{ $dailyRoute['color'] ?? '#3b82f6' }}; border-left: 4px solid {{ $dailyRoute['color'] ?? '#3b82f6' }};">
                                    {{ \Carbon\Carbon::parse($dailyRoute['date'])->format('d M Y') }} 
                                    - {{ count($dailyRoute['route']) }} Toko
                                </td>
                            </tr>
                            @foreach($dailyRoute['route'] as $index => $store)
                                <tr>
                                    <td class="font-bold">
                                        <div class="w-5 h-5 rounded-full flex items-center justify-center text-white text-[10px]" style="background-color: {{ $dailyRoute['color'] ?? '#3b82f6' }};">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-semibold text-primary truncate max-w-[150px]" title="{{ $store['custname'] }}">
                                            {{ $store['custname'] }}
                                        </div>
                                        <div class="text-[0.6rem] text-base-content/60">{{ $store['custno'] }}</div>
                                    </td>
                                    <td class="text-right">
                                        @if($index < count($dailyRoute['route']) - 1)
                                            <span class="font-mono text-xs">{{ $store['distance_to_next'] }} km</span>
                                            <div class="text-[0.65rem] text-base-content/60">{{ $store['duration_to_next'] ?? '-' }} mnt</div>
                                        @else
                                            <span class="text-[0.6rem] text-base-content/40">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-8 text-base-content/50">Data toko tidak tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MapLibre GL JS --}}
@push('styles')
<link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
<style>
    .store-marker-label {
        background-color: #22c55e;
        color: white;
        font-weight: bold;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        font-size: 11px;
        cursor: pointer;
    }
    .maplibregl-popup-content {
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
<script>
    // Global error listener for diagnostics
    window.addEventListener('error', function(e) {
        const diag = document.getElementById('map-error-diag');
        const msg = document.getElementById('map-error-msg');
        if (diag && msg) {
            diag.classList.remove('hidden');
            msg.innerText = e.message + ' (' + e.filename + ':' + e.lineno + ')';
        }
    });

    if (typeof window.routeMapDataInit === 'undefined') {
        window.routeMapDataInit = function(initialRoutes) {
            return {
                map: null,
                markers: [],
                routeLayerIds: [],
                routeSourceIds: [],
                initialRoutesData: initialRoutes,
                resizeObserver: null,

                init() {
                    this.initMap();
                },

                showDiag(text) {
                    const diag = document.getElementById('map-error-diag');
                    const msg = document.getElementById('map-error-msg');
                    if (diag && msg) {
                        diag.classList.remove('hidden');
                        msg.innerText = text;
                    }
                    console.log("[MapDiag] " + text);
                },

                initMap() {
                    if (this.map) return;
                    
                    let container = this.$refs.mapContainer;
                    if (!container) {
                        this.showDiag("Container x-ref not found!");
                        return;
                    }

                    // If maplibregl is not yet loaded from CDN, wait and retry
                    if (typeof maplibregl === 'undefined') {
                        this.showDiag("maplibregl is undefined! Waiting for CDN...");
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }

                    this.showDiag("maplibregl loaded. Initializing map canvas...");

                    try {
                        this.map = new maplibregl.Map({
                            container: container,
                            style: {
                                'version': 8,
                                'sources': {
                                    'raster-tiles': {
                                        'type': 'raster',
                                        'tiles': [
                                            'https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                                            'https://b.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                                            'https://c.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
                                            'https://d.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png'
                                        ],
                                        'tileSize': 256,
                                        'attribution': '© OpenStreetMap contributors, © CARTO'
                                    }
                                },
                                'layers': [
                                    {
                                        'id': 'simple-tiles',
                                        'type': 'raster',
                                        'source': 'raster-tiles',
                                        'minzoom': 0,
                                        'maxzoom': 19
                                    }
                                ]
                            },
                            center: [106.816666, -6.200000],
                            zoom: 10
                        });

                        this.map.addControl(new maplibregl.NavigationControl());

                        this.map.on('load', () => {
                            this.showDiag("Map loaded successfully!");
                            setTimeout(() => {
                                const diag = document.getElementById('map-error-diag');
                                if (diag) diag.classList.add('hidden');
                            }, 1000);

                            this.map.resize();
                            if (this.initialRoutesData && this.initialRoutesData.length > 0) {
                                this.drawRoutes(this.initialRoutesData);
                            }
                        });

                        this.map.on('error', (e) => {
                            this.showDiag("MapLibre GL Error: " + (e.error ? e.error.message : 'Unknown map error'));
                        });

                        // Set up ResizeObserver to handle container size changes
                        if (this.resizeObserver) {
                            this.resizeObserver.disconnect();
                        }
                        this.resizeObserver = new ResizeObserver(() => {
                            if (this.map) {
                                this.map.resize();
                            }
                        });
                        this.resizeObserver.observe(container);

                    } catch (err) {
                        this.showDiag("Error initializing map: " + err.message);
                    }
                },

                destroy() {
                    if (this.resizeObserver) {
                        this.resizeObserver.disconnect();
                        this.resizeObserver = null;
                    }
                    if (this.map) {
                        this.map.remove();
                        this.map = null;
                    }
                },

        handleRouteAnalyzed(detail) {
            let routes = detail.routesByDate || (detail[0] && detail[0].routesByDate) || detail;
            this.drawRoutes(routes);
        },

        clearMap() {
            // Remove markers
            this.markers.forEach(marker => marker.remove());
            this.markers = [];

            // Remove layers
            this.routeLayerIds.forEach(id => {
                if (this.map.getLayer(id)) {
                    this.map.removeLayer(id);
                }
            });
            this.routeLayerIds = [];

            // Remove sources
            this.routeSourceIds.forEach(id => {
                if (this.map.getSource(id)) {
                    this.map.removeSource(id);
                }
            });
            this.routeSourceIds = [];
        },

        drawRoutes(routesData) {
            if (!this.map || !this.map.isStyleLoaded()) {
                // If map is not ready, wait a bit
                setTimeout(() => this.drawRoutes(routesData), 200);
                return;
            }

            this.clearMap();

            if (!routesData || routesData.length === 0) return;

            let bounds = new maplibregl.LngLatBounds();
            let hasPoints = false;

            routesData.forEach((dayData, dayIndex) => {
                let routeData = dayData.route;
                let apiGeometry = dayData.geometry;
                let color = dayData.color || '#3b82f6';
                
                if (!routeData || routeData.length === 0) return;

                let coordinates = [];

                routeData.forEach((store, index) => {
                    let lat = parseFloat(store.latitude);
                    let lng = parseFloat(store.longitude);

                    if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                        let point = [lng, lat]; // MapLibre uses [lng, lat]
                        coordinates.push(point);
                        bounds.extend(point);
                        hasPoints = true;

                        // Create marker element
                        const el = document.createElement('div');
                        el.className = 'store-marker-label';
                        el.style.backgroundColor = color;
                        el.innerHTML = index + 1;

                        let dur = store.duration_to_next ? `${store.duration_to_next} mnt` : '-';
                        let popupContent = `
                            <div class="text-xs min-w-[200px]">
                                <div class="font-bold text-white px-2 py-1 rounded mb-2" style="background-color: ${color}; display: inline-block;">${dayData.date}</div><br>
                                <strong class="text-sm text-base-content">${index + 1}. ${store.custname}</strong><br>
                                <div class="mt-1 text-base-content/80">
                                    Kode: ${store.custno}<br>
                                    Alamat: ${store.customer_address ?? '-'}<br>
                                    Jarak ke next: ${store.distance_to_next} km (${dur})
                                </div>
                            </div>
                        `;

                        let popup = new maplibregl.Popup({ offset: 15, closeButton: false }).setHTML(popupContent);

                        let marker = new maplibregl.Marker({ element: el })
                            .setLngLat(point)
                            .setPopup(popup)
                            .addTo(this.map);
                            
                        this.markers.push(marker);
                    }
                });

                // Add Polyline / GeoJSON Line
                let sourceId = `route-source-${dayIndex}`;
                let layerId = `route-layer-${dayIndex}`;

                if (apiGeometry) {
                    // Convert Leaflet-style GeoJSON to MapLibre
                    // Ensure the geometry is fully valid GeoJSON Feature or Geometry
                    let geojsonData = apiGeometry;
                    if (apiGeometry.type !== 'Feature' && apiGeometry.type !== 'FeatureCollection') {
                        geojsonData = {
                            "type": "Feature",
                            "properties": {},
                            "geometry": apiGeometry
                        };
                    }

                    this.map.addSource(sourceId, {
                        'type': 'geojson',
                        'data': geojsonData
                    });
                } else {
                    if (coordinates.length > 1) {
                        this.map.addSource(sourceId, {
                            'type': 'geojson',
                            'data': {
                                'type': 'Feature',
                                'properties': {},
                                'geometry': {
                                    'type': 'LineString',
                                    'coordinates': coordinates
                                }
                            }
                        });
                    }
                }

                if (this.map.getSource(sourceId)) {
                    this.map.addLayer({
                        'id': layerId,
                        'type': 'line',
                        'source': sourceId,
                        'layout': {
                            'line-join': 'round',
                            'line-cap': 'round'
                        },
                        'paint': {
                            'line-color': color,
                            'line-width': 4,
                            'line-opacity': 0.8,
                            'line-dasharray': apiGeometry ? [1] : [2, 2] // Dashed if no geometry (straight line)
                        }
                    });

                    this.routeSourceIds.push(sourceId);
                    this.routeLayerIds.push(layerId);
                }
            });

            if (hasPoints) {
                this.map.fitBounds(bounds, { padding: 50, duration: 1000 });
                
                // Force resize to fix container dimension issues on SPA transition
                setTimeout(() => {
                    if (this.map) {
                        this.map.resize();
                    }
                }, 400);
            }
        }
    };
};
}
</script>
@endpush
