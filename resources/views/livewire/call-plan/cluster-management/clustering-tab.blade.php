<div class="flex flex-col gap-4 flex-1 min-h-0 w-full">
    {{-- Toolbar --}}
    <div class="bg-base-100 rounded-xl shadow-sm border border-base-200 p-3 shrink-0 relative z-50 mt-2">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 lg:gap-4">
            
            {{-- Title & Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm md:text-base font-bold text-base-content truncate">Balanced Master Clustering</h2>
                    <button onclick="document.getElementById('modal_panduan').showModal()" class="btn btn-xs btn-circle btn-ghost text-info hover:bg-info/10 tooltip tooltip-right shrink-0" data-tip="Panduan & Info">
                        <x-heroicon-o-information-circle class="w-4 h-4" />
                    </button>
                </div>
                <p class="text-[0.65rem] text-base-content/60 leading-tight hidden md:block truncate">Kelompokkan semua toko otomatis berdasarkan kapasitas dan radius</p>
            </div>
            
            {{-- Form Toolbar --}}
            <div class="flex items-center gap-2 flex-wrap lg:flex-nowrap w-full lg:w-auto">
                
                {{-- Distributor Input --}}
                <div class="w-full sm:w-48 relative">
                    <input wire:model.live.debounce.300ms="searchDistributor" type="text" class="input input-sm input-bordered w-full text-xs font-semibold bg-base-100 pr-6 h-8 min-h-0" placeholder="Ketik Distributor...">
                    @if(!empty($selectedDistributorCode))
                        <button wire:click="clearDistributor" class="absolute right-1 top-1 btn btn-xs btn-circle btn-ghost text-base-content/50 hover:bg-base-200 h-6 w-6 min-h-0">✕</button>
                    @endif
                    
                    @if(count($distributorOptions) > 0)
                    <ul class="menu menu-xs bg-base-100 border border-base-200 rounded-box mt-1 max-h-60 overflow-y-auto absolute w-full md:w-80 shadow-lg top-full left-0 z-50 text-[0.7rem]">
                        @foreach($distributorOptions as $res)
                            <li><a wire:click="selectDistributor('{{ $res['distributor_code'] }}', '{{ addslashes($res['distributor_name']) }}')" class="py-2">{{ $res['distributor_code'] }} - <span class="font-bold">{{ $res['distributor_name'] }}</span></a></li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                
                {{-- Parameters Input (Compact) --}}
                <div class="flex items-center gap-1 sm:gap-2 sm:border-l sm:border-base-200 sm:pl-2">
                    <div class="tooltip tooltip-bottom before:text-xs" data-tip="Jumlah Cluster (K)">
                        <div class="relative">
                            <span class="absolute -top-2 left-2 bg-base-100 px-1 text-[0.55rem] font-bold text-accent">K</span>
                            <input wire:model="targetClusters" type="number" class="input input-sm input-bordered w-14 sm:w-16 text-xs text-center px-1 h-8 min-h-0 font-bold" min="1" max="100">
                        </div>
                    </div>
                    
                    <div class="tooltip tooltip-bottom before:text-xs" data-tip="Max Toko / Cluster">
                        <div class="relative">
                            <span class="absolute -top-2 left-2 bg-base-100 px-1 text-[0.55rem] font-bold text-accent">Max T</span>
                            <input wire:model="maxStoresPerCluster" type="number" class="input input-sm input-bordered w-14 sm:w-16 text-xs text-center px-1 h-8 min-h-0 font-bold" min="5" max="100">
                        </div>
                    </div>

                    <div class="tooltip tooltip-bottom before:text-xs" data-tip="Max Radius (Km)">
                        <div class="relative">
                            <span class="absolute -top-2 left-2 bg-base-100 px-1 text-[0.55rem] font-bold text-error">Rad</span>
                            <input wire:model="maxRadiusKm" type="number" step="0.1" class="input input-sm input-bordered w-14 sm:w-16 text-xs text-center px-1 h-8 min-h-0 font-bold text-error" min="1" max="100">
                        </div>
                    </div>
                    
                    <div class="tooltip tooltip-bottom before:text-xs ml-1" data-tip="Prioritas Batas Wilayah">
                        <label class="cursor-pointer label p-0 px-2 border border-base-200 rounded-lg hover:bg-base-200 transition-colors h-8 flex items-center justify-center bg-base-100">
                            <input wire:model="useSpatialPenalty" type="checkbox" class="checkbox checkbox-xs checkbox-primary rounded" />
                        </label>
                    </div>
                </div>

                {{-- Action Button --}}
                <button wire:click="generateMasterClusters" wire:loading.attr="disabled" wire:target="generateMasterClusters" class="btn btn-sm btn-primary ml-auto lg:ml-2 shadow-sm shrink-0 h-8 min-h-0 px-4" @if(!$selectedDistributorCode) disabled @endif>
                    <x-heroicon-s-sparkles class="w-4 h-4 hidden sm:inline" wire:loading.remove wire:target="generateMasterClusters" />
                    <span wire:loading.remove wire:target="generateMasterClusters">Generate</span>
                    <span wire:loading wire:target="generateMasterClusters" class="loading loading-spinner loading-xs"></span>
                </button>
                
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 flex-1 min-h-0">
        {{-- Map Container --}}
        <div class="w-full lg:w-2/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col relative z-0" wire:ignore>
            <div id="route-map" class="w-full h-[500px] lg:h-full z-0"></div>
            
            <div class="absolute bottom-4 right-4 bg-base-100/90 backdrop-blur p-2 rounded-lg border border-base-300 shadow-sm z-[400] text-xs">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                        <span>Unclustered (Out of Range)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-gray-800"></div>
                        <span>Telah Masuk Cluster Lain</span>
                    </div>
                    <div class="text-[0.6rem] text-base-content/50 mt-1">
                        Klik marker toko untuk pindah cluster
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Sidebar --}}
        <div class="w-full lg:w-1/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col">
            <div class="p-3 border-b border-base-300 bg-base-200/50 flex justify-between items-center z-10 shadow-sm">
                <div>
                    <h3 class="font-bold text-sm">Daftar Cluster Toko</h3>
                    <p class="text-[0.65rem] text-base-content/60">{{ count($clusterStores) }} Total Toko</p>
                </div>
                
                @if(count($clusterStores) > 0)
                <button wire:click="openSaveModal" class="btn btn-xs btn-success text-white">Simpan Semua</button>
                @endif
            </div>

            <div class="flex-1 overflow-auto p-2 bg-base-200/30">
                @php
                    $summary = [];
                    foreach($clusterStores as $s) {
                        $cId = $s['cluster_id'];
                        if(!isset($summary[$cId])) {
                            $summary[$cId] = ['count' => 0, 'stores' => []];
                        }
                        $summary[$cId]['count']++;
                        $summary[$cId]['stores'][] = $s;
                    }
                    ksort($summary);
                @endphp

                @if(count($summary) === 0)
                    <div class="text-center py-8 text-base-content/50 text-sm">Belum ada cluster di-generate.</div>
                @else
                    <div class="join join-vertical w-full bg-base-100 shadow-sm">
                        @foreach($summary as $cId => $data)
                            <div class="collapse collapse-arrow join-item border border-base-300">
                                <input type="checkbox" {{ $loop->first ? 'checked' : '' }} /> 
                                <div class="collapse-title p-3 pr-10 min-h-0 flex items-center gap-3 group">
                                    @if($cId == 0)
                                        <div class="w-4 h-4 rounded-full bg-gray-400 shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="font-bold text-sm text-gray-500 leading-tight">Unclustered</div>
                                            <div class="text-[0.65rem] text-base-content/60">Tidak masuk jangkauan</div>
                                        </div>
                                    @elseif($cId == -1)
                                        <div class="w-4 h-4 rounded-full bg-gray-800 shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="font-bold text-sm leading-tight">Telah Disimpan</div>
                                            <div class="text-[0.65rem] text-base-content/60">Masuk cluster lain</div>
                                        </div>
                                    @else
                                        <div class="w-4 h-4 rounded-full shrink-0" style="background-color: hsl({{ ($cId * 137.5) % 360 }}, 70%, 50%);"></div>
                                        <div class="flex-1">
                                            @php
                                                $kecamatanList = [];
                                                foreach($data['stores'] as $st) {
                                                    if(!empty($st['kecamatan'])) {
                                                        $kecamatanList[] = $st['kecamatan'];
                                                    }
                                                }
                                                $kecamatanStr = count($kecamatanList) > 0 ? implode(', ', array_unique($kecamatanList)) : '';
                                            @endphp
                                            <div class="font-bold text-sm leading-tight flex items-center gap-2">
                                                Cluster {{ $cId }}
                                                @if($kecamatanStr)
                                                    <span class="text-xs font-normal opacity-70">({{ $kecamatanStr }})</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-1 opacity-60 hover:opacity-100 transition-opacity relative z-10" onclick="event.stopPropagation()">
                                            <select class="select select-bordered select-xs w-32 font-normal text-[0.65rem]" wire:change="mergeCluster({{ $cId }}, $event.target.value)">
                                                <option value="" disabled selected>Gabung ke...</option>
                                                @foreach($summary as $optId => $optData)
                                                    @if($optId > 0 && $optId != $cId)
                                                        <option value="{{ $optId }}">Cluster {{ $optId }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            
                                            <button type="button" wire:click="dissolveCluster({{ $cId }})" class="btn btn-ghost btn-xs btn-circle text-error" title="Bongkar (Keluarkan semua toko ke Unclustered)">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </div>
                                    @endif
                                    
                                    <div class="badge badge-sm {{ $data['count'] > $maxStoresPerCluster ? 'badge-error' : 'badge-neutral' }} shrink-0">
                                        {{ $data['count'] }} Toko
                                    </div>
                                </div>
                                <div class="collapse-content p-0">
                                    <div class="overflow-x-auto">
                                        <table class="table table-xs table-zebra w-full text-[0.7rem]">
                                            <tbody>
                                                @foreach($data['stores'] as $st)
                                                    @php
                                                        $pilarRaw = (string)($st['pilar'] ?? '');
                                                        $pilarName = '?';
                                                        $pilarClass = 'badge-ghost';
                                                        
                                                        if (str_contains($pilarRaw, '1.')) {
                                                            $pilarName = 'RWO';
                                                            $pilarClass = 'badge-primary text-white';
                                                        } elseif (str_contains($pilarRaw, '2.')) {
                                                            $pilarName = 'PNR';
                                                            $pilarClass = 'badge-secondary text-white';
                                                        } elseif (str_contains($pilarRaw, '3.')) {
                                                            $pilarName = 'NGVO';
                                                            $pilarClass = 'badge-accent text-white';
                                                        } elseif (str_contains($pilarRaw, '4.')) {
                                                            $pilarName = 'GRO';
                                                            $pilarClass = 'badge-info text-white';
                                                        }
                                                    @endphp
                                                    <tr class="hover:bg-base-200/50 transition-colors group">
                                                        <td class="w-10 text-center font-mono opacity-50">{{ $loop->iteration }}</td>
                                                        <td ondblclick="window.focusMapOnStore({{ $st['latitude'] ?? 0 }}, {{ $st['longitude'] ?? 0 }}, {{ $st['id'] }})" class="cursor-pointer" title="Klik ganda untuk fokus di peta">
                                                            <div class="flex items-center gap-2">
                                                                <div class="font-bold">{{ $st['customer_name'] }}</div>
                                                                <button type="button" onclick="window.focusMapOnStore({{ $st['latitude'] ?? 0 }}, {{ $st['longitude'] ?? 0 }}, {{ $st['id'] }})" class="btn btn-ghost btn-xs btn-circle text-info opacity-50 hover:opacity-100" title="Fokus di Peta">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                                                </button>
                                                            </div>
                                                            <div class="opacity-60">{{ $st['customer_code_prc'] }} &bull; {{ $st['kelurahan'] ?? '-' }}</div>
                                                        </td>
                                                        <td class="text-right">
                                                            <span class="badge badge-xs {{ $pilarClass }} border-none px-2 py-2 font-bold shadow-sm">{{ $pilarName }}</span>
                                                        </td>
                                                        <td class="w-32 text-center">
                                                            <select class="select select-bordered select-xs w-full opacity-0 group-hover:opacity-100 transition-opacity font-normal text-xs" wire:change="reassignStore({{ $st['id'] }}, $event.target.value)">
                                                                <option value="" disabled selected>Pindah ke...</option>
                                                                @if($cId > 0)
                                                                    <option value="0" class="text-error">Keluarkan (Unclustered)</option>
                                                                @endif
                                                                @foreach($summary as $optId => $optData)
                                                                    @if($optId > 0 && $optId != $cId)
                                                                        @php
                                                                            $optKecamatan = [];
                                                                            foreach($optData['stores'] as $optSt) {
                                                                                if(!empty($optSt['kecamatan'])) {
                                                                                    $optKecamatan[] = $optSt['kecamatan'];
                                                                                }
                                                                            }
                                                                            $optKecamatanStr = count($optKecamatan) > 0 ? ' (' . implode(', ', array_unique($optKecamatan)) . ')' : '';
                                                                        @endphp
                                                                        <option value="{{ $optId }}">Cluster {{ $optId }}{{ $optKecamatanStr }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Save Modal --}}
    <div class="modal {{ $isSaveModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-backdrop bg-base-300/80 backdrop-blur-sm fixed inset-0" wire:click="closeSaveModal"></div>
        <div class="modal-box rounded-2xl relative z-10 shadow-2xl">
            <button wire:click="closeSaveModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-lg mb-4">Simpan Semua Cluster</h3>
            
            <div class="alert alert-info shadow-sm mb-4 text-xs">
                Toko yang berstatus <strong>Unclustered</strong> (abu-abu) tidak akan ikut tersimpan.
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
                <button wire:click="confirmSaveCluster" wire:loading.attr="disabled" wire:target="confirmSaveCluster" class="btn btn-success rounded-xl text-white">
                    <span wire:loading.remove wire:target="confirmSaveCluster">Konfirmasi Simpan</span>
                    <span wire:loading wire:target="confirmSaveCluster" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Panduan --}}
    <dialog id="modal_panduan" class="modal z-[999]">
        <div class="modal-box rounded-2xl relative w-11/12 max-w-2xl shadow-2xl">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                <x-heroicon-o-information-circle class="w-6 h-6 text-info" />
                Panduan Balanced Clustering
            </h3>
            
            <div class="space-y-4 text-sm max-h-[60vh] overflow-y-auto pr-2">
                <div class="bg-base-200/50 p-4 rounded-xl">
                    <h4 class="font-bold mb-2">Metode K-Means Berbatas:</h4>
                    <p class="mb-2">Sistem ini membagi seluruh toko secara merata berdasarkan 3 parameter:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li><strong>Jumlah Cluster (K):</strong> Jumlah kelompok yang diinginkan (misal 24 hari kerja = 24 cluster).</li>
                        <li><strong>Max Toko:</strong> Kapasitas maksimal agar toko tidak menumpuk di 1 cluster saja.</li>
                        <li><strong>Max Radius:</strong> Batas tarikan maksimal (Km) agar toko terpencil tidak ikut dimasukkan secara paksa.</li>
                    </ul>
                </div>
                <div class="bg-base-200/50 p-4 rounded-xl">
                    <h4 class="font-bold mb-2">Manual Adjustment:</h4>
                    <p class="mb-2">Jika ada titik toko yang ingin dipindahkan ke cluster tetangga, Anda bisa <strong>klik marker toko</strong> di peta, dan pilih cluster tujuan di popup yang muncul.</p>
                </div>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-primary rounded-xl text-white">Mengerti</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-base-300/80 backdrop-blur-sm">
            <button>close</button>
        </form>
    </dialog>
</div>

@script
<script>
    let map;
    let markers = [];
    let resizeObserver = null;

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function getClusterColor(clusterId) {
        if (clusterId == -1) return '#1f2937';
        if (clusterId == 0) return '#9ca3af';
        const hue = (clusterId * 137.5) % 360;
        return `hsl(${hue}, 70%, 50%)`;
    }

    function initRouteMap() {
        if (map) return;
        const container = document.getElementById('route-map');
        if (!container) return;

        map = new maplibregl.Map({
            container: 'route-map',
            style: {
                'version': 8,
                'sources': {
                    'raster-tiles': {
                        'type': 'raster',
                        'tiles': ['https://basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}@2x.png'],
                        'tileSize': 256,
                        'attribution': '&copy; OpenStreetMap contributors, &copy; CARTO'
                    }
                },
                'layers': [{
                    'id': 'simple-tiles',
                    'type': 'raster',
                    'source': 'raster-tiles',
                    'minzoom': 0,
                    'maxzoom': 19
                }]
            },
            center: [118.0149, -2.5489],
            zoom: 5
        });

        map.addControl(new maplibregl.NavigationControl());

        if (resizeObserver) resizeObserver.disconnect();
        resizeObserver = new ResizeObserver(() => {
            if (map) map.resize();
        });
        resizeObserver.observe(container);
    }

    function clearRouteMap() {
        if (markers.length > 0) {
            markers.forEach(m => m.remove());
            markers = [];
        }
    }

    async function fetchAndDrawBoundaries(stores) {
        if (!map || !stores || stores.length === 0) return;
        const kecamatans = [...new Set(stores.map(s => s.kecamatan).filter(Boolean))];
        if (kecamatans.length === 0) return;

        try {
            const response = await fetch('/call-plan/batas-wilayah-geojson', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ kecamatans: kecamatans })
            });
            
            if (!response.ok) throw new Error('Network response error');
            const geojson = await response.json();

            if (map.getSource('batas-wilayah')) {
                map.getSource('batas-wilayah').setData(geojson);
            } else {
                map.addSource('batas-wilayah', {
                    'type': 'geojson',
                    'data': geojson,
                    'generateId': true
                });

                map.addLayer({
                    'id': 'batas-wilayah-fill',
                    'type': 'fill',
                    'source': 'batas-wilayah',
                    'paint': {
                        'fill-color': '#3b82f6',
                        'fill-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 0.2, 0.03]
                    }
                });

                map.addLayer({
                    'id': 'batas-wilayah-line',
                    'type': 'line',
                    'source': 'batas-wilayah',
                    'paint': {
                        'line-color': '#64748b',
                        'line-width': ['case', ['boolean', ['feature-state', 'hover'], false], 2, 1],
                        'line-opacity': 0.7
                    }
                });

                let hoveredStateId = null;
                const hoverPopup = new maplibregl.Popup({
                    closeButton: false,
                    closeOnClick: false,
                    className: 'hover-popup'
                });

                map.on('mousemove', 'batas-wilayah-fill', (e) => {
                    map.getCanvas().style.cursor = 'crosshair';
                    if (e.features.length > 0) {
                        if (hoveredStateId !== null) {
                            map.setFeatureState({ source: 'batas-wilayah', id: hoveredStateId }, { hover: false });
                        }
                        hoveredStateId = e.features[0].id;
                        map.setFeatureState({ source: 'batas-wilayah', id: hoveredStateId }, { hover: true });
                        
                        const props = e.features[0].properties;
                        hoverPopup.setLngLat(e.lngLat)
                            .setHTML(`<div class="text-xs px-1"><div class="font-bold text-primary">${escHtml(props.kelurahan)}</div><div class="text-[0.65rem] opacity-70">${escHtml(props.kecamatan)}</div></div>`)
                            .addTo(map);
                    }
                });

                map.on('mouseleave', 'batas-wilayah-fill', () => {
                    map.getCanvas().style.cursor = '';
                    if (hoveredStateId !== null) {
                        map.setFeatureState({ source: 'batas-wilayah', id: hoveredStateId }, { hover: false });
                    }
                    hoveredStateId = null;
                    hoverPopup.remove();
                });
            }
        } catch (error) {
            console.error('Error fetching batas wilayah:', error);
        }
    }

    window.reassignStoreCluster = function(storeId, selectElement) {
        const newClusterId = selectElement.value;
        $wire.reassignStore(storeId, newClusterId);
    };

    function isValidCoord(lat, lng) {
        if (lat === null || lng === null || isNaN(lat) || isNaN(lng)) return false;
        if (Math.abs(lat) < 0.0001 && Math.abs(lng) < 0.0001) return false;
        if (lat === 0 || lng === 0) return false;
        if (lat < -15 || lat > 15 || lng < 90 || lng > 145) return false;
        return true;
    }

    window.focusMapOnStore = function(lat, lng, storeId) {
        if (!map) return;
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (!isValidCoord(lat, lng)) {
            alert('Koordinat toko ini tidak valid atau bernilai 0.');
            return;
        }
        
        map.flyTo({
            center: [lng, lat],
            zoom: 14,
            essential: true,
            duration: 800
        });

        if (markers && markers.length > 0) {
            markers.forEach(m => {
                if (m.getPopup() && m.getPopup().isOpen()) m.togglePopup();
            });
            const targetMarker = markers.find(m => m.storeId === storeId);
            if (targetMarker && !targetMarker.getPopup().isOpen()) targetMarker.togglePopup();
        }
    };

    function drawClusters(stores) {
        if (!map) return;
        clearRouteMap();
        if (!stores || stores.length === 0) return;

        let bounds = new maplibregl.LngLatBounds();
        let hasPoints = false;
        
        let uniqueClusterIds = new Set();
        let clusterKecamatans = {};
        stores.forEach(s => {
            if (s.cluster_id > 0) {
                uniqueClusterIds.add(s.cluster_id);
                if (s.kecamatan) {
                    if (!clusterKecamatans[s.cluster_id]) clusterKecamatans[s.cluster_id] = new Set();
                    clusterKecamatans[s.cluster_id].add(s.kecamatan);
                }
            }
        });
        
        let sortedClusterIds = Array.from(uniqueClusterIds).sort((a, b) => a - b);

        stores.forEach((store) => {
            const lat = parseFloat(store.latitude);
            const lng = parseFloat(store.longitude);
            
            if (isValidCoord(lat, lng)) {
                let point = [lng, lat]; 
                bounds.extend(point);
                hasPoints = true;

                const clusterId = store.cluster_id || 0;
                const markerColor = getClusterColor(clusterId);

                const el = document.createElement('div');
                el.className = 'cluster-marker';
                el.style.backgroundColor = markerColor;
                el.style.zIndex = clusterId == 0 ? '1' : '10';

                let optionsHtml = `<option value="-1" ${clusterId == -1 ? 'selected' : ''}>Telah Disimpan</option>`;
                optionsHtml += `<option value="0" ${clusterId == 0 ? 'selected' : ''}>Unclustered</option>`;
                
                sortedClusterIds.forEach(id => {
                    let kec = '';
                    if (clusterKecamatans[id] && clusterKecamatans[id].size > 0) {
                        kec = ' (' + Array.from(clusterKecamatans[id]).join(', ') + ')';
                    }
                    optionsHtml += `<option value="${id}" ${clusterId == id ? 'selected' : ''}>Cluster ${id}${kec}</option>`;
                });

                let pilarClass = 'badge-ghost';
                const pilarStr = (store.pilar || '').toString();
                if(pilarStr.includes('1.')) pilarClass = 'badge-primary text-white';
                else if(pilarStr.includes('2.')) pilarClass = 'badge-secondary text-white';
                else if(pilarStr.includes('3.')) pilarClass = 'badge-accent text-white';
                else if(pilarStr.includes('4.')) pilarClass = 'badge-info text-white';

                const popupContent = `
                    <div class="text-xs">
                        <div class="font-bold text-sm mb-1 text-base-content border-b border-base-200 pb-2 flex justify-between items-start gap-3">
                            <span class="leading-tight">${escHtml(store.customer_name)}</span>
                            <span class="badge badge-xs ${pilarClass} border-none shadow-sm font-bold p-2 shrink-0">Pilar ${escHtml(store.pilar || '?')}</span>
                        </div>
                        <div class="text-[0.65rem] text-base-content/60 mb-2 mt-1">
                            ${escHtml(store.customer_code_prc)}<br>
                            ${store.kecamatan ? '<span class="text-primary font-bold">' + escHtml(store.kecamatan) + '</span> - ' + escHtml(store.kelurahan) : ''}
                        </div>
                        
                        <div class="form-control w-full mt-2">
                            <label class="label p-0 pb-1"><span class="label-text text-[0.65rem] font-bold">Pindah Cluster:</span></label>
                            <select class="select select-xs select-bordered w-full" onchange="window.reassignStoreCluster(${store.id}, this)">
                                ${optionsHtml}
                            </select>
                        </div>
                    </div>
                `;

                let popup = new maplibregl.Popup({ offset: 10, closeButton: true }).setHTML(popupContent);

                let marker = new maplibregl.Marker({ element: el })
                    .setLngLat(point)
                    .setPopup(popup)
                    .addTo(map);
                
                marker.storeId = store.id;
                markers.push(marker);
            }
        });

        if (hasPoints) {
            map.fitBounds(bounds, { padding: 50, duration: 1000 });
        }
    }

    setTimeout(() => {
        initRouteMap();
        const initialStores = $wire.clusterStores;
        if (initialStores && initialStores.length > 0) {
            if (map.isStyleLoaded()) {
                drawClusters(initialStores);
                fetchAndDrawBoundaries(initialStores);
            } else {
                map.once('load', () => {
                    drawClusters(initialStores);
                    fetchAndDrawBoundaries(initialStores);
                });
            }
        }
    }, 100);

    Livewire.on('clusters-generated', (data) => {
        const stores = data[0]?.stores || data.stores || [];
        if (!map) initRouteMap();
        setTimeout(() => {
            if (map) map.resize();
            if (map && map.isStyleLoaded()) {
                drawClusters(stores);
                if (stores.length > 0) fetchAndDrawBoundaries(stores);
            } else if (map) {
                map.once('load', () => {
                    drawClusters(stores);
                    if (stores.length > 0) fetchAndDrawBoundaries(stores);
                });
            }
        }, 100);
    });
</script>
@endscript
