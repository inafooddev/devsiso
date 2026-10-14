<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Analisa Kunjungan SE</x-slot>

    {{-- Standar Tab Menu dari resources/views/components/ui/tab-menu.blade.php --}}
    <x-ui.tab-menu class="mb-3 md:mb-4 lg:mb-6">
        <x-ui.tab-item :active="$activeTab === 'detail'" wire:click.prevent="setTab('detail')" :navigate="false">
            <x-heroicon-o-table-cells class="w-4 h-4 inline-block mr-1" /> Detail Data
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'summary'" wire:click.prevent="setTab('summary')" :navigate="false">
            <x-heroicon-o-chart-bar class="w-4 h-4 inline-block mr-1" /> Summary Data
        </x-ui.tab-item>
    </x-ui.tab-menu>

    {{-- 5 KPI Cards Section (Tetap statis di atas sesuai standar template) --}}
    @include('livewire.report.analisa-kunjungan-se.kpi-cards')

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Kunjungan SE</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar Kunjungan & Transaksi Sales Exclusive (SE)</p>
            </div>
            
            {{-- Filter Bar & Action Buttons --}}
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 w-full lg:w-auto">
                {{-- Filter Region --}}
                <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs">
                    <option value="">Pilih Region</option>
                    @foreach($regions as $r)
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Area --}}
                <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs" @disabled(empty($selectedRegion))>
                    <option value="">Pilih Area</option>
                    @foreach($areas as $a)
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Distributor --}}
                <select wire:model.live="selectedDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[150px] text-xs" @disabled(empty($selectedArea))>
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $d)
                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Salesman (SE) --}}
                <select wire:model.live="selectedSalesman" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 w-full sm:w-auto min-w-[130px] text-xs" @disabled(empty($selectedDistributor))>
                    <option value="">Semua SE</option>
                    @foreach($salesmen as $s)
                        <option value="{{ $s->salesman_code }}">{{ $s->salesman_name }}</option>
                    @endforeach
                </select>

                {{-- Rentang Tanggal --}}
                <div class="flex items-center gap-1 w-full sm:w-auto">
                    <input type="date" wire:model="startDate" class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 text-xs w-full sm:w-[130px]" title="Tanggal Mulai" />
                    <span class="text-xs text-base-content/50">-</span>
                    <input type="date" wire:model="endDate" class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 text-xs w-full sm:w-[130px]" title="Tanggal Akhir" />
                </div>

                <div class="hidden xl:block w-px h-6 bg-base-300 mx-1"></div>

                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto justify-end">
                    {{-- Terapkan --}}
                    <x-ui.button variant="primary" size="sm" class="rounded-xl px-3 font-semibold shadow-sm" wire:click="applyFilter" spinner="applyFilter" icon="funnel">
                        Terapkan
                    </x-ui.button>

                    {{-- Reset --}}
                    <x-ui.button variant="ghost" size="sm" class="rounded-xl px-2.5 text-base-content/70 hover:text-base-content" wire:click="resetFilter" spinner="resetFilter" icon="arrow-path">
                        Reset
                    </x-ui.button>

                    {{-- Peta Semua Titik --}}
                    @php
                        $isMapDisabled = empty($appliedRegion) || empty($appliedArea);
                    @endphp
                    <button type="button" 
                        class="btn btn-sm btn-outline btn-info rounded-xl gap-1 text-xs"
                        x-data 
                        @click="$dispatch('open-all-maps-modal', {{ json_encode($mapPointsData ?? []) }})"
                        @disabled($isMapDisabled)>
                        <x-heroicon-s-map class="w-4 h-4" />
                        Peta
                    </button>

                    {{-- Export Excel --}}
                    @php
                        $isExportDisabled = empty($appliedRegion) || empty($appliedArea);
                        $hasPendingFilters = $selectedRegion !== $appliedRegion || 
                                             $selectedArea !== $appliedArea || 
                                             $selectedDistributor !== $appliedDistributor || 
                                             $selectedSalesman !== $appliedSalesman || 
                                             $startDate !== $appliedStartDate || 
                                             $endDate !== $appliedEndDate;
                    @endphp
                    <div class="tooltip tooltip-left" data-tip="{{ $isExportDisabled || $hasPendingFilters ? 'Terapkan filter terlebih dahulu' : 'Export Excel' }}">
                        <x-ui.button variant="success" size="sm" class="rounded-xl text-white px-3 shadow-sm font-semibold" wire:click="export" spinner="export" icon="arrow-down-tray" :disabled="$isExportDisabled || $hasPendingFilters">
                            Excel
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area sesuai standar template) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr class="[&>th]:align-middle">
                        <th class="w-16">No</th>
                        <th>Tanggal</th>
                        <th>Area</th>
                        <th>Distributor</th>
                        <th>SE Code</th>
                        <th>SE Name</th>
                        <th>Cust No</th>
                        <th>Cust Name</th>
                        <th class="text-center">Start</th>
                        <th class="text-center">End</th>
                        <th class="text-center whitespace-normal leading-tight">Minute<br>per outlet</th>
                        <th class="text-center whitespace-normal leading-tight">Time<br>Travel</th>
                        <th class="text-center whitespace-normal leading-tight">Time<br>Pause</th>
                        <th class="text-center">Visit</th>
                        <th class="text-center">Order</th>
                        <th class="text-center">Distance</th>
                        <th class="text-center">Reason</th>
                    </tr>
                </thead>
                <tbody class="text-xs relative" wire:loading.class="opacity-50 pointer-events-none transition-opacity duration-200" wire:target="applyFilter, resetFilter, previousPage, nextPage, gotoPage">
                @php
                    $groupedData = collect($dataKunjungan->items())->groupBy('tanggal');
                    $rowCounter = ($dataKunjungan->currentPage() - 1) * $dataKunjungan->perPage();
                @endphp
                @forelse($groupedData as $tanggal => $rows)
                    @foreach($rows as $row)
                    @php $rowCounter++; @endphp
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td class="font-mono text-base-content/60">{{ $rowCounter }}</td>
                        <td class="font-medium whitespace-nowrap">{{ $row->tanggal }}</td>
                        <td class="whitespace-nowrap">{{ $row->area_name }}</td>
                        <td class="max-w-[180px] truncate" title="{{ $row->distributor_name }}">{{ $row->distributor_name }}</td>
                        <td class="font-mono">{{ $row->salesman_code }}</td>
                        <td class="max-w-[140px] truncate font-medium" title="{{ $row->salesman_name }}">{{ $row->salesman_name }}</td>
                        <td class="font-mono">{{ $row->custno }}</td>
                        <td class="max-w-[180px] truncate {{ $row->flag_pjp == 'N' ? 'text-error font-bold' : '' }}" title="{{ $row->custname }}">
                            {{ $row->custname }}
                            @if($row->flag_pjp == 'N')
                                <span class="badge badge-error badge-xs text-[8px] text-white ml-1">Luar PJP</span>
                            @endif
                        </td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_in ?? '-' }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_out ?? '-' }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_consume ?? '-' }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_travel ?? '-' }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_pause ?? '-' }}</td>
                        
                        {{-- Visit Badge --}}
                        <td class="text-center whitespace-nowrap">
                            @if($row->flag_visit == 'Y')
                                <span class="badge badge-sm badge-success text-white font-bold px-2">Y</span>
                            @else
                                <span class="badge badge-sm badge-error text-white font-bold px-2">N</span>
                            @endif
                        </td>

                        {{-- Order Badge / Action --}}
                        <td class="text-center whitespace-nowrap">
                            @if($row->flag_ec == 'Y')
                                <button type="button" 
                                    x-data="{ qty: {{ (float)($row->qty_order ?? 0) }}, val: {{ (float)($row->val_order ?? 0) }} }" 
                                    @click="$dispatch('open-order-modal', {qty, val})" 
                                    class="btn btn-xs btn-success text-white px-2.5 rounded-lg font-bold shadow-xs"
                                    title="Klik untuk melihat detail order">
                                    Y
                                </button>
                            @else
                                <span class="badge badge-sm badge-error text-white font-bold px-2">N</span>
                            @endif
                        </td>
                        
                        {{-- Distance Button --}}
                        <td class="text-center whitespace-nowrap">
                            @php
                                $dist = \App\Livewire\Report\AnalisaKunjunganSe\Index::calculateDistance($row->master_lat, $row->master_lon, $row->visit_lat, $row->visit_lon);
                            @endphp
                            <button type="button" 
                                x-data 
                                @click="$dispatch('open-map-modal', {masterLat: '{{ $row->master_lat }}', masterLon: '{{ $row->master_lon }}', visitLat: '{{ $row->visit_lat }}', visitLon: '{{ $row->visit_lon }}'})" 
                                class="btn btn-xs rounded-xl {{ $dist > 50 ? 'btn-error text-white border-none shadow-sm' : 'btn-outline btn-info' }}"
                                title="Klik untuk membuka peta">
                                {{ $dist }}m
                            </button>
                        </td>

                        {{-- Reason Button --}}
                        <td class="text-center whitespace-nowrap">
                            @if($row->reason_type || $row->reason_desc)
                                <button type="button" 
                                    x-data="{{ json_encode(['type' => $row->reason_type, 'desc' => $row->reason_desc]) }}" 
                                    @click="$dispatch('open-reason-modal', {type: type, desc: desc})" 
                                    class="btn btn-xs btn-outline btn-warning rounded-xl">
                                    Detail
                                </button>
                            @else
                                <span class="text-base-content/30">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    {{-- Subtotal Row Per Date --}}
                    <tr class="!bg-primary/10 font-bold border-t-2 border-primary/20">
                        <td colspan="8" class="text-right py-2 text-primary">
                            Subtotal Tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }} ({{ count($rows) }} Kunjungan):
                        </td>
                        <td colspan="5"></td>
                        <td class="text-center text-primary font-mono py-2">
                            {{ $rows->where('flag_visit', 'Y')->count() }} Visit
                        </td>
                        <td class="whitespace-nowrap font-mono text-center text-primary py-2" title="Total Value Order">
                            Rp {{ number_format($rows->sum('val_order'), 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="text-center py-12 text-base-content/40">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <x-heroicon-o-inbox class="w-12 h-12" />
                                @if(empty($appliedRegion) || empty($appliedArea))
                                    <p class="text-sm font-semibold">Silakan pilih Region dan Area, lalu klik tombol <span class="text-primary font-bold">Terapkan</span>.</p>
                                @else
                                    <p class="text-sm">Tidak ada data kunjungan ditemukan untuk filter yang dipilih.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination sesuai standar template) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            @if(empty($appliedRegion) || empty($appliedArea))
                <div class="text-base-content/60 text-center sm:text-left italic">
                    Gunakan filter untuk menampilkan data dan paginasi.
                </div>
            @else
                <div class="text-base-content/60 text-center sm:text-left">
                    Menampilkan <span class="font-bold text-base-content">{{ $dataKunjungan->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-base-content">{{ $dataKunjungan->lastItem() ?? 0 }}</span> dari <span class="font-bold text-base-content">{{ number_format($dataKunjungan->total()) }}</span> entri
                </div>
                <div>
                    {{ $dataKunjungan->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Modals Container (Dibuat h-0 agar tidak menambah tinggi flexbox layout) --}}
    <div class="shrink-0 h-0 w-0 overflow-visible">
        {{-- Modal Detail Order --}}
    <div x-data="{ open: false, qty: 0, val: 0 }" 
         @open-order-modal.window="qty = $event.detail.qty; val = $event.detail.val; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Detail Order Kunjungan SE</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Qty Order</p>
                        <p class="text-xl font-bold" x-text="Number(qty).toLocaleString('id-ID')"></p>
                    </div>
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Value Order</p>
                        <p class="text-xl font-bold font-mono text-primary">Rp <span x-text="Number(val).toLocaleString('id-ID')"></span></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Detail Reason --}}
    <div x-data="{ open: false, type: '', desc: '' }"
         @open-reason-modal.window="type = $event.detail.type; desc = $event.detail.desc; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <x-heroicon-s-information-circle class="w-5 h-5 text-warning" />
                    Detail Reason Kunjungan
                </h3>
                <div class="space-y-4">
                    <div class="p-3 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Type</p>
                        <p class="text-sm font-bold text-warning" x-text="type || '-'"></p>
                    </div>
                    <div class="p-3 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Deskripsi</p>
                        <p class="text-sm text-base-content/80 leading-relaxed" x-text="desc || '-'"></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Single Map --}}
    <div x-data="{ 
            open: false,
            mapData: null,
            initMap(data) {
                if(data) this.mapData = data;
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
                        container: 'visit-map-se',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: [118.0148, -2.5489],
                        zoom: 5
                    });

                    let mLat = parseFloat(data.masterLat);
                    let mLon = parseFloat(data.masterLon);
                    let vLat = parseFloat(data.visitLat);
                    let vLon = parseFloat(data.visitLon);

                    // Deteksi jika koordinat master terbalik
                    if (Math.abs(mLat) > 90) {
                        const temp = mLat; mLat = mLon; mLon = temp;
                    }
                    // Deteksi jika koordinat visit terbalik
                    if (Math.abs(vLat) > 90) {
                        const temp = vLat; vLat = vLon; vLon = temp;
                    }

                    const bounds = new maplibregl.LngLatBounds();
                    let hasMaster = !isNaN(mLat) && !isNaN(mLon);
                    let hasVisit = !isNaN(vLat) && !isNaN(vLon);

                    if (hasMaster) {
                        new maplibregl.Marker({ color: '#ef4444' })
                            .setLngLat([mLon, mLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Master Point</strong><br><span class='text-[10px] text-gray-500 font-mono'>📍 ${mLat}, ${mLon}</span></div>`))
                            .addTo(this.mapInstance);
                        bounds.extend([mLon, mLat]);
                    }

                    if (hasVisit) {
                        new maplibregl.Marker({ color: '#3b82f6' })
                            .setLngLat([vLon, vLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Visit Point SE</strong><br><span class='text-[10px] text-gray-500 font-mono'>📍 ${vLat}, ${vLon}</span></div>`))
                            .addTo(this.mapInstance);
                        bounds.extend([vLon, vLat]);
                    }

                    if (!bounds.isEmpty()) {
                        this.mapInstance.fitBounds(bounds, { padding: 50, maxZoom: 18, animate: false });
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
                    };

                    if (this.mapInstance.loaded()) {
                        initRoute();
                    } else {
                        this.mapInstance.on('load', initRoute);
                    }
                    
                    setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 300);
                }, 300);
            }
         }"
         @open-map-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Peta Kunjungan SE vs Master Outlet</h3>
                <div id="visit-map-se" wire:ignore class="rounded-xl border border-base-300 h-[600px] w-full"></div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal All Maps --}}
    <div x-data="{ 
            open: false,
            points: [],
            mapInstance: null,
            initMap(data) {
                this.points = data || [];
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
                        container: 'all-maps-se',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: [118.0148, -2.5489],
                        zoom: 5
                    });

                    const bounds = new maplibregl.LngLatBounds();
                    let validPointsCount = 0;

                    this.points.forEach(point => {
                        let lat = parseFloat(point.lat);
                        let lon = parseFloat(point.lon);

                        if (Math.abs(lat) > 90) {
                            const temp = lat; lat = lon; lon = temp;
                        }

                        if (!isNaN(lat) && !isNaN(lon) && lat !== 0 && lon !== 0) {
                            new maplibregl.Marker({ color: '#3b82f6' })
                                .setLngLat([lon, lat])
                                .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`
                                    <div class='text-black text-xs'>
                                        <strong>${point.name}</strong><br>
                                        <span class='text-gray-500'>SE: ${point.spv}</span><br>
                                        <span class='text-gray-400 text-[10px]'>Tgl: ${point.date}</span><br>
                                        <span class='text-[10px] text-primary font-mono cursor-pointer hover:underline' onclick='window.open(\`https://www.google.com/maps/search/?api=1&query=${lat},${lon}\`, \`_blank\`);'>📍 Buka Google Maps</span>
                                    </div>
                                `))
                                .addTo(this.mapInstance);

                            bounds.extend([lon, lat]);
                            validPointsCount++;
                        }
                    });

                    if (validPointsCount > 0 && !bounds.isEmpty()) {
                        this.mapInstance.fitBounds(bounds, { padding: 50, maxZoom: 15, animate: false });
                    }

                    setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 300);
                }, 300);
            }
         }"
         @open-all-maps-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-6xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg">Peta Semua Titik Kunjungan SE</h3>
                    <span class="badge badge-primary badge-sm" x-text="points.length + ' Titik Kunjungan'"></span>
                </div>
                <div id="all-maps-se" wire:ignore class="rounded-xl border border-base-300 h-[650px] w-full"></div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm rounded-xl" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>
    </div>
</div>
