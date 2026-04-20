<div>
    <x-slot name="title">Dashboard Peta Distributor</x-slot>

    @push('styles')
    {{-- CSS Leaflet (CDN) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #dashboard-map {
            /* Peta sekarang mengisi 100% dari container parent */
            height: 100%;
            width: 100%;
            z-index: 10;
        }
    </style>
    @endpush

    <div class="relative" style="height: calc(90vh - 4rem);">
        
        <!-- [PERUBAHAN] Kolom Filter (Mengambang di KANAN ATAS) -->
        <div class="absolute top-4 end-4 z-20 w-64"
             x-data="{ open: false }">
            <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl overflow-hidden">
                <button @click="open = !open" class="flex items-center justify-between w-full p-4">
                    <h3 class="text-md font-semibold text-gray-700">Filter Peta</h3>
                    <svg :class="{'rotate-180': open}" class="w-5 h-5 text-gray-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="open" x-transition class="p-4 border-t border-gray-200/50">
                    <div class="space-y-3">
                        <!-- Filter Region -->
                        <div>
                            <label for="regionFilter" class="block text-xs font-medium text-gray-700">Region</label>
                            <select wire:model.live="regionFilter" id="regionFilter" class="mt-1 block w-full pl-3 pr-10 py-1.5 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                                <option value="">Semua Region</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Area -->
                        <div>
                            <label for="areaFilter" class="block text-xs font-medium text-gray-700">Area</label>
                            <select wire:model.live="areaFilter" id="areaFilter" class="mt-1 block w-full pl-3 pr-10 py-1.5 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md" @if(!$regionFilter) disabled @endif>
                                <option value="">Semua Area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Status -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status</label>
                            <select wire:model.live="statusFilter" id="statusFilter" class="mt-1 block w-full pl-3 pr-10 py-1.5 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                                <option value="">Semua Status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Peta (Base Layer) -->
        <div id="dashboard-map" class="absolute top-0 left-0" wire:ignore x-data="dashboardMap()" x-init="init()"></div>
    </div>

    @push('scripts')
    {{-- JS untuk Leaflet (CDN) --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardMap', () => ({
                map: null,
                markersLayer: null,
                // [PERUBAHAN] Definisikan semua ikon di sini
                iconList: [],
                inactiveIcon: null,
                regionIconMap: {}, // Map untuk menyimpan region_code -> icon
                iconIndex: 0,

                init() {
                    const waitForLeaflet = setInterval(() => {
                        if (typeof L !== 'undefined') {
                            clearInterval(waitForLeaflet);
                            this.createMap();
                        }
                    }, 100);
                },
                createMap() {
                    if (window.dashboardMapInstance) {
                        window.dashboardMapInstance.remove();
                    }
                    const map = L.map('dashboard-map').setView([-2.548926, 118.0148634], 5);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                    window.dashboardMapInstance = map;
                    this.map = map;
                    this.markersLayer = L.layerGroup().addTo(map);

                    // [PERUBAHAN] Inisialisasi semua ikon
                    const iconBaseUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-';
                    const iconProps = {
                        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                    };
                    
                    this.inactiveIcon = L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}red.png` });
                    
                    // Daftar ikon untuk region aktif
                    this.iconList = [
                        L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}green.png` }),
                        L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}blue.png` }),
                        L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}orange.png` }),
                        L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}yellow.png` }),
                        L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}violet.png` }),
                        L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}grey.png` }),
                        L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}gold.png` })
                    ];
                    this.regionIconMap = {}; // Reset map
                    this.iconIndex = 0; // Reset index

                    this.$wire.on('dataUpdated', (event) => {
                        let data = event[0] || event;
                        if(data.locations) {
                            this.updateMarkers(data.locations);
                        }
                    });
                    
                    setTimeout(() => {
                        this.$wire.dispatch('map:ready'); 
                    }, 150);
                },

                // [PERUBAHAN] Fungsi baru untuk mendapatkan/menetapkan ikon
                getIcon(dist) {
                    if (dist.status !== 'Aktif') {
                        return this.inactiveIcon;
                    }
                    
                    const region = dist.region_code;
                    if (!region) {
                        return this.iconList[0]; // Default ke green jika tidak ada region
                    }

                    // Jika region ini belum punya warna, tetapkan satu
                    if (!this.regionIconMap[region]) {
                        this.regionIconMap[region] = this.iconList[this.iconIndex % this.iconList.length];
                        this.iconIndex++;
                    }

                    // Kembalikan ikon yang sudah ditetapkan untuk region tsb
                    return this.regionIconMap[region];
                },

                updateMarkers(locations) {
                    if (!this.map) return;
                    this.markersLayer.clearLayers(); 
                    if (!locations || locations.length === 0) {
                        this.map.setView([-2.548926, 118.0148634], 5);
                        setTimeout(() => this.map.invalidateSize(), 100);
                        return;
                    }

                    const bounds = [];
                    locations.forEach(dist => {
                        const lat = parseFloat(dist.lat);
                        const lng = parseFloat(dist.lng);
                        if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                            
                            // [PERUBAHAN] Panggil fungsi getIcon
                            const icon = this.getIcon(dist); 
                            
                            const marker = L.marker([lat, lng], {icon: icon})
                                .bindPopup(`<strong>${dist.name}</strong><br>${dist.status}`);
                            this.markersLayer.addLayer(marker);
                            bounds.push([lat, lng]);
                        }
                    });

                    if (bounds.length > 0) {
                        this.map.fitBounds(bounds, { padding: [50, 50] });
                    }
                    setTimeout(() => this.map.invalidateSize(), 100);
                }
            }));
        });
        
        document.addEventListener('livewire:navigated', () => {
            if (window.dashboardMapInstance) {
                 Livewire.dispatch('map:ready');
            }
        });
    </script>
    @endpush
</div>