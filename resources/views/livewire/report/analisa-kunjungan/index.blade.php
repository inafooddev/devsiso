<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Analisa Kunjungan</x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        /* Fix konflik CSS transition antara Tailwind/DaisyUI dan Leaflet saat zoom/pan */
        .leaflet-container * {
            transition-property: none !important;
        }
        .leaflet-zoom-anim .leaflet-zoom-animated {
            transition-property: transform !important;
        }

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

    @include('livewire.report.analisa-kunjungan.kpi-cards')

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full xl:w-auto">
                <h2 class="text-base md:text-lg font-bold">Analisa Kunjungan</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Filter data analisa kunjungan</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-2">
                    <input type="date" wire:model="startDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                    <span class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">s/d</span>
                    <input type="date" wire:model="endDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                </div>
                
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>
                    
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]" @if(empty($areas)) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>
                    
                    <select wire:model.live="selectedSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]" @if(empty($supervisors)) disabled @endif>
                        <option value="">Semua Supervisor</option>
                        @foreach($supervisors as $spv)
                            <option value="{{ $spv->supervisor_code }}">{{ $spv->supervisor_name }}</option>
                        @endforeach
                    </select>
                </div>
                @php
                    $mapPoints = [];
                    if (!empty($dataKunjungan)) {
                        foreach($dataKunjungan as $row) {
                            if (!empty($row->visit_lat) && !empty($row->visit_lon)) {
                                $mapPoints[] = [
                                    'lat' => $row->visit_lat,
                                    'lon' => $row->visit_lon,
                                    'name' => $row->custname,
                                    'spv' => $row->supervisor_name
                                ];
                            }
                        }
                    }
                @endphp
                <div id="map-points-data" data-points="{{ json_encode($mapPoints) }}"></div>
                <div class="flex items-center gap-2">
                    <x-ui.button class="rounded-xl" variant="neutral" icon="arrow-path" size="sm" wire:click="resetFilter" spinner="resetFilter">Reset</x-ui.button>
                    <x-ui.button class="rounded-xl" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                    <button type="button" x-data @click="$dispatch('open-all-maps-modal', JSON.parse(document.getElementById('map-points-data').dataset.points))" class="btn btn-sm btn-info rounded-xl text-white" @if(empty($mapPoints)) disabled @endif>
                        <x-heroicon-o-map class="w-4 h-4" /> Maps
                    </button>
                </div>
            </div>
        </div>


        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-xs table-zebra table-pin-rows w-full whitespace-nowrap text-[10px]">
                <thead class="text-[10px] uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:bg-base-300">
                    <tr class="[&>th]:align-middle">
                        <th class="align-middle">Tanggal</th>
                        <th class="align-middle">SPV Code</th>
                        <th class="align-middle">SPV Name</th>
                        <th class="align-middle">Cust No</th>
                        <th class="align-middle">Cust Name</th>
                        <th class="align-middle">Pilar</th>
                        <th class="align-middle">Target</th>
                        <th class="align-middle text-center min-w-[80px]">Start</th>
                        <th class="align-middle text-center min-w-[80px]">End</th>
                        <th class="align-middle text-center min-w-[80px] whitespace-normal leading-tight">Minute<br>per outlet</th>
                        <th class="align-middle text-center min-w-[80px] whitespace-normal leading-tight">Time<br>Travel</th>
                        <th class="align-middle text-center min-w-[80px] whitespace-normal leading-tight">Time<br>Pause</th>
                        <th class="align-middle text-center">Visit</th>
                        <th class="align-middle text-center">Order</th>
                        <th class="align-middle text-center">Distance</th>
                        <th class="align-middle text-center">Reason</th>
                        <th class="align-middle text-center">Remark</th>
                    </tr>
                </thead>
                <tbody class="text-[10px]">
                @php
                    $groupedData = collect($dataKunjungan)->groupBy('tanggal');
                @endphp
                @forelse($groupedData as $tanggal => $rows)
                    @foreach($rows as $row)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td class="whitespace-nowrap">{{ $row->tanggal }}</td>
                        <td class="whitespace-nowrap">{{ $row->supervisor_code }}</td>
                        <td class="max-w-[150px] truncate" title="{{ $row->supervisor_name }}">{{ $row->supervisor_name }}</td>
                        <td class="whitespace-nowrap">{{ $row->custno }}</td>
                        <td class="max-w-[150px] truncate {{ $row->flag_pjp == 'N' ? 'text-error font-bold' : '' }}" title="{{ $row->custname }}">{{ $row->custname }}</td>
                        <td class="whitespace-nowrap">{{ $row->pilar }}</td>
                        <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->target ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_in }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_out }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_consume }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_travel }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_pause }}</td>
                        
                        <td class="text-center whitespace-nowrap">
                            @if($row->flag_visit == 'Y')
                                <button type="button" class="btn btn-xs btn-success text-white w-8 h-8 rounded-full p-0 cursor-default">Y</button>
                            @else
                                <button type="button" class="btn btn-xs btn-error text-white w-8 h-8 rounded-full p-0 cursor-default">N</button>
                            @endif
                        </td>

                        <td class="text-center whitespace-nowrap">
                            @if($row->flag_ec == 'Y')
                                <button type="button" x-data @click="$dispatch('open-order-modal', {qty: {{ $row->qty_order ?? 0 }}, val: {{ $row->val_order ?? 0 }}})" class="btn btn-xs btn-success text-white w-8 h-8 rounded-full p-0">Y</button>
                            @else
                                <button type="button" class="btn btn-xs btn-error text-white w-8 h-8 rounded-full p-0 cursor-default">N</button>
                            @endif
                        </td>
                        
                        <td class="text-center whitespace-nowrap">
                            @php
                                $dist = $this->getDistance($row->master_lat, $row->master_lon, $row->visit_lat, $row->visit_lon);
                            @endphp
                            <button type="button" x-data @click="$dispatch('open-map-modal', {masterLat: '{{ $row->master_lat }}', masterLon: '{{ $row->master_lon }}', visitLat: '{{ $row->visit_lat }}', visitLon: '{{ $row->visit_lon }}'})" class="btn btn-xs rounded-xl {{ $dist > 50 ? 'btn-error text-white border-none shadow-sm shadow-error/50' : 'btn-outline btn-info' }}">
                                {{ $dist }}m
                            </button>
                        </td>

                        <td class="text-center whitespace-nowrap">
                            @if($row->reason_type || $row->reason_desc)
                            <button type="button" x-data @click="$dispatch('open-reason-modal', {type: '{{ addslashes($row->reason_type) }}', desc: '{{ addslashes($row->reason_desc) }}'})" class="btn btn-xs btn-outline btn-warning rounded-xl">Detail</button>
                            @else
                            -
                            @endif
                        </td>
                        
                        <td class="text-center whitespace-nowrap">
                            @if($row->action_remark)
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" wire:click="openRemarkModal('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}', '{{ addslashes($row->action_remark) }}')" class="btn btn-xs btn-ghost text-info p-0 w-6 h-6 rounded-full" title="Lihat/Edit Remark"><x-heroicon-s-eye class="w-4 h-4" /></button>
                                    <button type="button" wire:click="deleteRemark('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}')" class="btn btn-xs btn-ghost text-error p-0 w-6 h-6 rounded-full" title="Hapus Remark"><x-heroicon-s-trash class="w-4 h-4" /></button>
                                </div>
                            @else
                                <button type="button" wire:click="openRemarkModal('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}', '')" class="btn btn-xs btn-ghost text-base-content/40 hover:text-primary p-0 w-6 h-6 rounded-full" title="Isi Remark"><x-heroicon-s-pencil class="w-4 h-4" /></button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="!bg-primary/10 font-bold border-t-2 border-primary/20">
                        <td colspan="6" class="text-right py-2 text-primary">Subtotal Tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}:</td>
                        <td class="whitespace-nowrap font-mono text-right text-primary py-2">{{ number_format($rows->sum('target'), 0, ',', '.') }}</td>
                        <td colspan="6"></td>
                        <td class="whitespace-nowrap font-mono text-center text-primary py-2" title="Total Value Order">Rp {{ number_format($rows->sum('val_order'), 0, ',', '.') }}</td>
                        <td colspan="3"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center py-12 text-base-content/40">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <x-heroicon-o-inbox class="w-10 h-10" />
                                @if(empty($appliedRegion) && empty($appliedArea) && empty($appliedSupervisor))
                                    <p class="text-sm">Silakan terapkan filter terlebih dahulu untuk menampilkan data.</p>
                                @else
                                    <p class="text-sm">Tidak ada data ditemukan.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
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
                        <span class="label-text">Isi Remark</span>
                    </label>
                    <textarea wire:model.defer="modalRemarkText" class="textarea textarea-bordered h-24" placeholder="Ketik remark di sini..."></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-outline" @click="open = false">Batal</button>
                    <button type="button" wire:click="saveRemark" class="btn btn-primary" spinner="saveRemark">Simpan</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Map --}}
    <div x-data="{ 
            open: false,
            mapInstance: null,
            markersLayer: null,
            initMap(data) {
                this.open = true;
                setTimeout(() => {
                    if (!this.mapInstance) {
                        this.mapInstance = L.map('visit-map');
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.mapInstance);
                        this.markersLayer = L.layerGroup().addTo(this.mapInstance);
                    } else {
                        this.markersLayer.clearLayers();
                    }

                    let mLat = parseFloat(data.masterLat);
                    let mLon = parseFloat(data.masterLon);
                    let vLat = parseFloat(data.visitLat);
                    let vLon = parseFloat(data.visitLon);

                    // Deteksi jika koordinat master terbalik (lat > 90)
                    if (Math.abs(mLat) > 90) {
                        const temp = mLat; mLat = mLon; mLon = temp;
                    }
                    // Deteksi jika koordinat visit terbalik (lat > 90)
                    if (Math.abs(vLat) > 90) {
                        const temp = vLat; vLat = vLon; vLon = temp;
                    }

                    const bounds = [];
                    const iconBaseUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-';
                    const iconProps = { iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] };

                    const redIcon = L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}red.png` });
                    const blueIcon = L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}blue.png` });

                    if (!isNaN(mLat) && !isNaN(mLon)) {
                        L.marker([mLat, mLon], {icon: redIcon}).bindPopup('Master Point').addTo(this.markersLayer);
                        bounds.push([mLat, mLon]);
                    }

                    if (!isNaN(vLat) && !isNaN(vLon)) {
                        L.marker([vLat, vLon], {icon: blueIcon}).bindPopup('Visit Point').addTo(this.markersLayer);
                        bounds.push([vLat, vLon]);
                    }

                    if (bounds.length === 2) {
                        L.polyline(bounds, {color: 'green', weight: 3, dashArray: '5, 5'}).addTo(this.markersLayer);
                    }

                    if (bounds.length > 0) {
                        this.mapInstance.fitBounds(bounds, { padding: [50, 50] });
                    } else {
                        this.mapInstance.setView([-2.5489, 118.0148], 5); // Default Indonesia
                    }
                    
                    this.mapInstance.invalidateSize();
                }, 100);
            }
         }"
         @open-map-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Peta Kunjungan vs Master</h3>
                <div id="visit-map" wire:ignore class="rounded-xl border border-base-300"></div>
                <div class="flex gap-4 mt-4 items-center">
                    <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 bg-red-500 rounded-full"></div> Master Point</div>
                    <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 bg-blue-500 rounded-full"></div> Visit Point</div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal All Maps --}}
    <div x-data="{ 
            open: false,
            mapInstance: null,
            markersLayer: null,
            initMap(points) {
                this.open = true;

                if (typeof L === 'undefined') {
                    alert('Leaflet belum termuat. Silakan refresh halaman.');
                    return;
                }

                if (typeof L.markerClusterGroup === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';
                    script.onload = () => this.initMap(points);
                    document.head.appendChild(script);

                    const css1 = document.createElement('link');
                    css1.rel = 'stylesheet';
                    css1.href = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css';
                    document.head.appendChild(css1);

                    const css2 = document.createElement('link');
                    css2.rel = 'stylesheet';
                    css2.href = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css';
                    document.head.appendChild(css2);
                    return;
                }

                setTimeout(() => {
                    try {
                        if (!this.mapInstance) {
                            this.mapInstance = L.map('all-visit-map');
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; OpenStreetMap contributors'
                            }).addTo(this.mapInstance);
                            this.markersLayer = L.markerClusterGroup({
                                maxClusterRadius: function(zoom) {
                                    // 1 piksel layar dalam meter pada ekuator = 156543 / 2^zoom
                                    let radius = 50 / (156543 / Math.pow(2, zoom));
                                    // Membulatkan dan memastikan minimal 1px agar tidak crash
                                    return Math.max(1, Math.round(radius));
                                },
                                spiderfyOnMaxZoom: true,
                                showCoverageOnHover: true,
                                zoomToBoundsOnClick: true,
                                disableClusteringAtZoom: 18
                            });
                            this.mapInstance.addLayer(this.markersLayer);
                        } else {
                            this.markersLayer.clearLayers();
                        }

                    const bounds = [];
                    const iconBaseUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-';
                    const iconProps = { iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] };
                    const blueIcon = L.icon({ ...iconProps, iconUrl: `${iconBaseUrl}blue.png` });

                    const pointsArray = Array.isArray(points) ? points : (points ? Object.values(points) : []);
                    pointsArray.forEach(pt => {
                        let lat = parseFloat(pt.lat);
                        let lon = parseFloat(pt.lon);
                        
                        // Fix jika koordinat terbalik (latitute tidak boleh lebih dari 90)
                        if (Math.abs(lat) > 90) {
                            const temp = lat;
                            lat = lon;
                            lon = temp;
                        }

                        if (!isNaN(lat) && !isNaN(lon)) {
                            const m = L.marker([lat, lon], {icon: blueIcon})
                                .bindPopup(`<strong>${pt.name}</strong><br>SPV: ${pt.spv}`);
                            this.markersLayer.addLayer(m);
                            bounds.push([lat, lon]);
                        }
                    });

                    if (bounds.length > 0) {
                        this.mapInstance.fitBounds(bounds, { padding: [50, 50] });
                    } else {
                        this.mapInstance.setView([-2.5489, 118.0148], 5); // Default Indonesia
                    }
                    
                    this.mapInstance.invalidateSize();
                    setTimeout(() => { if (this.mapInstance) this.mapInstance.invalidateSize(); }, 300);
                    setTimeout(() => { if (this.mapInstance) this.mapInstance.invalidateSize(); }, 600);
                    
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan saat memuat peta: ' + e.message);
                    }
                }, 100);
            }
         }"
         @open-all-maps-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Sebaran Titik Visit Kunjungan</h3>
                <div id="all-visit-map" wire:ignore class="rounded-xl border border-base-300"></div>
                <div class="modal-action">
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    @endpush
</div>
