<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Route Efficiency Analysis</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SISO vs ESKA</a>
            <a href="{{ route('call-plan.jks-team-elite.route-efficiency') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Route Efficiency</a>
            <a href="{{ route('call-plan.jks-team-elite.clustering') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Clustering</a>
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
            
            <div class="w-full sm:w-40">
                <input wire:model.live="filterDate" type="date" class="input input-sm input-bordered w-full rounded-xl bg-base-100">
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    @if(count($optimalRoute) >= 2)
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
    @elseif(count($optimalRoute) == 1)
        <div class="alert alert-warning shadow-sm rounded-xl">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
            <span>Hanya terdapat 1 toko yang memiliki koordinat di Call Plan hari ini. Jarak tidak dapat dihitung.</span>
        </div>
    @else
        <div class="alert shadow-sm rounded-xl bg-base-200">
            <x-heroicon-o-information-circle class="w-6 h-6" />
            <span>Pilih Team dan Tanggal untuk melihat analisis. Pastikan toko memiliki koordinat (Latitude/Longitude).</span>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-4 flex-1 min-h-0">
        {{-- Map Container --}}
        <div class="w-full lg:w-2/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col relative z-0" wire:ignore>
            <div id="route-map" class="w-full h-[400px] lg:h-full z-0"></div>
            
            <div class="absolute bottom-4 right-4 bg-base-100/90 backdrop-blur p-2 rounded-lg border border-base-300 shadow-sm z-[400] text-xs">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-4 h-1 bg-blue-500 rounded-full"></div>
                    <span>Rute Jalan Raya (API)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-1 bg-green-500 border-dashed border border-transparent" style="border-top: 2px dashed #22c55e; background: none;"></div>
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
                        @forelse($optimalRoute as $index => $store)
                            <tr>
                                <td class="font-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-semibold text-primary truncate max-w-[150px]" title="{{ $store['custname'] }}">
                                        {{ $store['custname'] }}
                                    </div>
                                    <div class="text-[0.6rem] text-base-content/60">{{ $store['custno'] }}</div>
                                </td>
                                <td class="text-right">
                                    @if($index < count($optimalRoute) - 1)
                                        <span class="font-mono text-xs">{{ $store['distance_to_next'] }} km</span>
                                        <div class="text-[0.65rem] text-base-content/60">{{ $store['duration_to_next'] ?? '-' }} mnt</div>
                                    @else
                                        <span class="text-[0.6rem] text-base-content/40">Selesai</span>
                                    @endif
                                </td>
                            </tr>
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

{{-- Leaflet Maps Script --}}
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
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
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('livewire:init', () => {
        let map;
        let routeLayerGroup;

        function initMap() {
            if (!map) {
                map = L.map('route-map').setView([-6.200000, 106.816666], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                routeLayerGroup = L.layerGroup().addTo(map);
            }
        }

        function drawRoute(routeData, apiGeometry = null) {
            if (!map) initMap();
            routeLayerGroup.clearLayers();

            if (!routeData || routeData.length === 0) return;

            let latlngs = [];
            let bounds = L.latLngBounds();

            routeData.forEach((store, index) => {
                let lat = parseFloat(store.latitude);
                let lng = parseFloat(store.longitude);

                if (!isNaN(lat) && !isNaN(lng)) {
                    let point = [lat, lng];
                    latlngs.push(point);
                    bounds.extend(point);

                    // Marker dengan nomor urut
                    let icon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div class="store-marker-label">${index + 1}</div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    let dur = store.duration_to_next ? `${store.duration_to_next} mnt` : '-';
                    let popupContent = `
                        <div class="text-xs">
                            <strong class="text-sm">${index + 1}. ${store.custname}</strong><br>
                            Kode: ${store.custno}<br>
                            Alamat: ${store.customer_address ?? '-'}<br>
                            Jarak ke next: ${store.distance_to_next} km (${dur})
                        </div>
                    `;

                    L.marker(point, {icon: icon})
                        .bindPopup(popupContent)
                        .addTo(routeLayerGroup);
                }
            });

            // Polyline
            if (apiGeometry) {
                L.geoJSON(apiGeometry, {
                    style: {
                        color: '#3b82f6',
                        weight: 4,
                        opacity: 0.8
                    }
                }).addTo(routeLayerGroup);
            } else {
                if (latlngs.length > 1) {
                    L.polyline(latlngs, {
                        color: '#22c55e',
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '10, 10',
                        lineJoin: 'round'
                    }).addTo(routeLayerGroup);
                }
            }

            if (latlngs.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        initMap();

        Livewire.on('route-analyzed', (data) => {
            const route = data.route || (data[0] && data[0].route);
            const geometry = data.geometry || (data[0] && data[0].geometry);
            drawRoute(route, geometry);
        });

        // Initialize with initial data if present
        let initialRoute = @json($optimalRoute);
        let initialGeometry = @json($apiGeometry);
        if(initialRoute && initialRoute.length > 0) {
            setTimeout(() => { drawRoute(initialRoute, initialGeometry); }, 500);
        }
    });
</script>
@endpush
