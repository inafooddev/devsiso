<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Customer Clustering</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SISO vs ESKA</a>
            <a href="{{ route('call-plan.jks-team-elite.route-efficiency') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Route Efficiency</a>
            <a href="{{ route('call-plan.jks-team-elite.clustering') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Clustering</a>
        </div>
    </div>

    {{-- Header / Filter Bar --}}
    @if (session()->has('message'))
        <div class="alert alert-success shadow-sm mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error shadow-sm mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 p-4 shrink-0 flex flex-col gap-4">
        <div>
            <h2 class="text-lg font-bold">Pembuatan Cluster Toko</h2>
            <p class="text-xs text-base-content/60">Buat grup rute efisien berdasarkan titik tengah (Center Store)</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-end gap-3 w-full">
            <div class="w-full sm:w-1/2 relative">
                <label class="label py-1"><span class="label-text text-xs">Cari Center Store (Titik Pusat)</span></label>
                <input wire:model.live.debounce.300ms="searchCenterText" type="text" class="input input-sm input-bordered w-full rounded-xl bg-base-100" placeholder="Ketik Kode/Nama Toko...">
                
                @if(count($searchCenterResults) > 0)
                <ul class="menu bg-base-100 border border-base-200 rounded-box mt-1 max-h-60 overflow-y-auto absolute w-full z-50 shadow-lg top-full left-0">
                    @foreach($searchCenterResults as $res)
                        <li><a wire:click="selectCenterStore({{ $res->id }})">{{ $res->customer_code_prc }} - {{ $res->customer_name }}</a></li>
                    @endforeach
                </ul>
                @endif
            </div>

            <div class="w-full sm:w-24">
                <label class="label py-1"><span class="label-text text-xs">Jumlah Toko</span></label>
                <input wire:model="candidateCount" type="number" class="input input-sm input-bordered w-full rounded-xl bg-base-100" min="2" max="80">
            </div>

            <div class="w-full sm:w-auto">
                <button wire:click="generateCluster" class="btn btn-sm btn-primary rounded-xl w-full" @if(!$centerStore) disabled @endif>
                    Generate Route
                </button>
            </div>
        </div>
    </div>

    {{-- Metrics Cards --}}
    @if(count($clusterStores) >= 2)
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
            <div class="p-3 border-b border-base-300 bg-base-200/50 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-sm">List Toko Cluster</h3>
                    <p class="text-[0.65rem] text-base-content/60">{{ count($clusterStores) }} Toko</p>
                </div>
                
                @if(count($clusterStores) > 0)
                <button wire:click="openSaveModal" class="btn btn-xs btn-success text-white">Simpan Cluster</button>
                @endif
            </div>

            {{-- Manual Add Store --}}
            <div class="p-2 border-b border-base-300 relative">
                <input wire:model.live.debounce.300ms="searchAddText" type="text" class="input input-xs input-bordered w-full" placeholder="Ketik untuk tambah toko...">
                @if(count($searchAddResults) > 0)
                <ul class="menu bg-base-100 border border-base-200 rounded-box mt-1 max-h-60 overflow-y-auto absolute w-full z-50 shadow-lg top-full left-0">
                    @foreach($searchAddResults as $res)
                        <li><a wire:click="selectAddStore({{ $res->id }})">{{ $res->customer_code_prc }} - {{ $res->customer_name }}</a></li>
                    @endforeach
                </ul>
                @endif
            </div>

            <div class="flex-1 overflow-auto p-0">
                <table class="table table-sm table-zebra w-full text-xs">
                    <thead class="bg-base-200/50 sticky top-0">
                        <tr>
                            <th class="w-10">No</th>
                            <th>Toko</th>
                            <th class="w-16">Pilar</th>
                            <th class="text-right w-16">Jarak (Km)</th>
                            <th class="w-10">Act</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clusterStores as $index => $store)
                            <tr>
                                <td class="font-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-semibold text-primary truncate max-w-[120px]" title="{{ $store['customer_name'] }}">
                                        {{ $store['customer_name'] }}
                                    </div>
                                    <div class="text-[0.6rem] text-base-content/60">{{ $store['customer_code_prc'] }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-sm badge-outline text-[0.6rem] whitespace-nowrap">{{ $store['pilar'] ?? '-' }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="font-bold">{{ $store['distance_to_next'] ?? 0 }}</div>
                                </td>
                                <td>
                                    <button wire:click="removeStore({{ $index }})" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error/20">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-base-content/50">Belum ada rute di-generate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Save Modal --}}
    <div class="modal {{ $isSaveModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-box rounded-2xl relative">
            <button wire:click="closeSaveModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-lg mb-4">Simpan Cluster</h3>
            
            <div class="form-control w-full mb-3">
                <label class="label"><span class="label-text">Nama Cluster</span></label>
                <input wire:model="clusterName" type="text" class="input input-bordered w-full" placeholder="Contoh: Cluster Pare 1" />
            </div>

            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text">Pilih Team Sales</span></label>
                <select wire:model="filterTeam" class="select select-bordered w-full">
                    @if(count($teams) == 0)
                        <option value="">-- Tidak ada team --</option>
                    @endif
                    @foreach($teams as $team)
                        <option value="{{ $team->kode_team }}">{{ $team->kode_team }} - {{ $team->nama_team }}</option>
                    @endforeach
                </select>
            </div>

            <div class="modal-action mt-6">
                <button wire:click="closeSaveModal" class="btn btn-ghost rounded-xl">Batal</button>
                <button wire:click="confirmSaveCluster" class="btn btn-success rounded-xl text-white">Konfirmasi Simpan</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            let map;
            let markers = [];
            let currentRouteLayer = null;

            function initMap() {
                if (!document.getElementById('route-map')) return;
                
                if (map) {
                    map.remove();
                }

                // Center Indonesia
                map = L.map('route-map').setView([-2.5489, 118.0149], 5);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19
                }).addTo(map);
            }

            function clearMap() {
                if (markers.length > 0) {
                    markers.forEach(m => map.removeLayer(m));
                    markers = [];
                }
                if (currentRouteLayer) {
                    map.removeLayer(currentRouteLayer);
                    currentRouteLayer = null;
                }
            }

            function drawRoute(routeData, geometry) {
                clearMap();
                if (!map || routeData.length === 0) return;

                let latlngs = [];
                
                routeData.forEach((store, index) => {
                    const lat = parseFloat(store.latitude);
                    const lng = parseFloat(store.longitude);
                    latlngs.push([lat, lng]);

                    // Marker custom style
                    const isFirst = index === 0;
                    const isLast = index === routeData.length - 1;
                    let markerColor = '#3b82f6'; // blue default
                    let zIndex = 1000;
                    
                    if (isFirst) { markerColor = '#22c55e'; zIndex = 2000; } // green first
                    else if (isLast) { markerColor = '#ef4444'; zIndex = 2000; } // red last

                    const markerHtml = `
                        <div class="relative w-8 h-8 flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full opacity-20" style="background-color: ${markerColor}; transform: scale(1.2);"></div>
                            <div class="relative bg-white rounded-full shadow-md border-2 w-7 h-7 flex items-center justify-center" style="border-color: ${markerColor}">
                                <span class="text-[0.65rem] font-bold" style="color: ${markerColor}">${index + 1}</span>
                            </div>
                        </div>
                    `;

                    const icon = L.divIcon({
                        html: markerHtml,
                        className: 'custom-marker',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });

                    const popupContent = `
                        <div class="p-1 min-w-[150px]">
                            <div class="font-bold text-sm mb-1">${index + 1}. ${store.customer_name}</div>
                            <div class="text-xs text-gray-500 mb-1">${store.customer_code_prc}</div>
                            <div class="text-xs font-semibold text-blue-600 border-t pt-1 mt-1">Ke toko selanjutnya: ${store.distance_to_next || 0} Km</div>
                        </div>
                    `;

                    const marker = L.marker([lat, lng], {icon: icon, zIndexOffset: zIndex})
                        .bindPopup(popupContent)
                        .addTo(map);
                        
                    markers.push(marker);
                });

                // Auto fit bounds
                if (latlngs.length > 0) {
                    const bounds = L.latLngBounds(latlngs);
                    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                }

                // Draw line
                if (geometry) {
                    // API GeoJSON
                    currentRouteLayer = L.geoJSON(geometry, {
                        style: {
                            color: '#3b82f6',
                            weight: 5,
                            opacity: 0.7,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }
                    }).addTo(map);
                } else if (latlngs.length > 1) {
                    // Manual Haversine Line
                    currentRouteLayer = L.polyline(latlngs, {
                        color: '#22c55e',
                        weight: 4,
                        opacity: 0.6,
                        dashArray: '10, 10'
                    }).addTo(map);
                }
            }

            // Initialization
            initMap();

            // Listen from Livewire Event
            Livewire.on('route-analyzed', (data) => {
                const routeData = data.route;
                const geometry = data.geometry;
                
                // Jika belum inisialisasi, paksa
                if (!map && document.getElementById('route-map')) {
                    initMap();
                }
                
                drawRoute(routeData, geometry);
            });
        });
    </script>
@endpush
