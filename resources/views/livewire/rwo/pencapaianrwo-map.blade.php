<div>
    <dialog id="map_modal" class="modal modal-bottom sm:modal-middle backdrop-blur-sm" wire:ignore>
        <div class="modal-box w-full !max-w-[95vw] h-[95vh] flex flex-col p-0 bg-base-100 rounded-3xl shadow-2xl relative overflow-hidden border border-base-200">
            
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-base-200 to-base-100 border-b border-base-200 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-xl text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-xl text-base-content">Peta Pencapaian RWO</h3>
                        <p class="text-xs text-base-content/60 mt-0.5">Sebaran outlet berdasarkan filter yang aktif</p>
                    </div>
                </div>
                <button onclick="map_modal.close()" class="btn btn-sm btn-circle btn-ghost bg-base-200 hover:bg-error hover:text-white transition-colors duration-200">
                    ✕
                </button>
            </div>
            
            {{-- Map Body --}}
            <div class="flex-grow w-full relative bg-base-200/50">
                <div id="pencapaian-map" class="absolute inset-0 w-full h-full"></div>
                
                {{-- Floating Legend (Interactive) --}}
                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-10">
                    <div class="bg-base-100/90 backdrop-blur-md px-6 py-3 rounded-full shadow-lg border border-base-200/50 flex items-center gap-6">
                        <div class="flex items-center gap-2 group cursor-pointer transition-all duration-200 legend-item" data-color="#eab308">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#eab308] opacity-20"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-[#eab308] border-2 border-white shadow-sm"></span>
                            </span>
                            <span class="text-xs font-semibold text-base-content/80 group-hover:text-base-content transition-colors">Reward 2.5%</span>
                        </div>
                        <div class="w-px h-4 bg-base-300"></div>
                        <div class="flex items-center gap-2 group cursor-pointer transition-all duration-200 legend-item" data-color="#3b82f6">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#3b82f6] opacity-20"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-[#3b82f6] border-2 border-white shadow-sm"></span>
                            </span>
                            <span class="text-xs font-semibold text-base-content/80 group-hover:text-base-content transition-colors">Reward 2%</span>
                        </div>
                        <div class="w-px h-4 bg-base-300"></div>
                        <div class="flex items-center gap-2 group cursor-pointer transition-all duration-200 legend-item" data-color="#64748b">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-[#64748b] border-2 border-white shadow-sm"></span>
                            </span>
                            <span class="text-xs font-semibold text-base-content/80 group-hover:text-base-content transition-colors">Reward 1.5%</span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <form method="dialog" class="modal-backdrop bg-black/40">
            <button>close</button>
        </form>
    </dialog>
</div>

@push('styles')
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <style>
        .maplibregl-ctrl-group {
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .maplibregl-popup-content {
            border-radius: 16px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .maplibregl-popup-close-button {
            top: 10px;
            right: 10px;
            color: #fff;
            background: rgba(0,0,0,0.2);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .maplibregl-popup-close-button:hover {
            background: rgba(0,0,0,0.4);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let map = null;
            let currentData = [];
            let hiddenColors = new Set();
            let activePopup = null;

            // Set up interactive legend click handlers
            document.querySelectorAll('.legend-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    const color = item.getAttribute('data-color');
                    if (hiddenColors.has(color)) {
                        hiddenColors.delete(color);
                        item.classList.remove('opacity-40', 'grayscale');
                    } else {
                        hiddenColors.add(color);
                        item.classList.add('opacity-40', 'grayscale');
                    }
                    renderPoints(false); // Re-render without resetting bounds
                });
            });

            function renderPoints(fitBounds = true) {
                if (!map || !map.isStyleLoaded()) {
                    setTimeout(() => renderPoints(fitBounds), 100);
                    return;
                }
                
                const bounds = new maplibregl.LngLatBounds();
                let hasValidPoints = false;
                
                // Filter out hidden colors
                const features = currentData
                    .filter(s => s.lng && s.lat && !hiddenColors.has(s.color))
                    .map(store => {
                        bounds.extend([store.lng, store.lat]);
                        hasValidPoints = true;
                        return {
                            type: 'Feature',
                            geometry: { type: 'Point', coordinates: [store.lng, store.lat] },
                            properties: { 
                                code: store.code,
                                color: store.color 
                            }
                        };
                    });

                if (map.getSource('stores')) {
                    map.getSource('stores').setData({
                        type: 'FeatureCollection',
                        features: features
                    });
                } else {
                    map.addSource('stores', {
                        type: 'geojson',
                        data: {
                            type: 'FeatureCollection',
                            features: features
                        }
                    });

                    // Glow layer
                    map.addLayer({
                        id: 'stores-glow-layer',
                        type: 'circle',
                        source: 'stores',
                        paint: {
                            'circle-radius': 12,
                            'circle-color': ['get', 'color'],
                            'circle-opacity': 0.2,
                            'circle-blur': 0.5
                        }
                    });

                    // Core point layer
                    map.addLayer({
                        id: 'stores-layer',
                        type: 'circle',
                        source: 'stores',
                        paint: {
                            'circle-radius': 6,
                            'circle-color': ['get', 'color'],
                            'circle-stroke-width': 2,
                            'circle-stroke-color': '#ffffff'
                        }
                    });
                    
                    // Hover effect logic
                    map.on('mouseenter', 'stores-layer', () => {
                        map.getCanvas().style.cursor = 'pointer';
                    });
                    map.on('mouseleave', 'stores-layer', () => {
                        map.getCanvas().style.cursor = '';
                    });
                    
                    // Click logic for popup
                    map.on('click', 'stores-layer', async (e) => {
                        const coordinates = e.features[0].geometry.coordinates.slice();
                        const props = e.features[0].properties;
                        const code = props.code;

                        // Ensure proper coordinates for popup
                        while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                            coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                        }

                        if (activePopup) activePopup.remove();

                        const skeletonHtml = `
                            <div class="w-72 bg-base-100 flex flex-col">
                                <div class="bg-primary h-16 w-full animate-pulse"></div>
                                <div class="p-4 flex flex-col gap-3">
                                    <div class="h-4 bg-base-300 rounded w-3/4 animate-pulse"></div>
                                    <div class="h-3 bg-base-200 rounded w-1/2 animate-pulse"></div>
                                    <div class="divider my-0"></div>
                                    <div class="flex justify-between">
                                        <div class="h-4 bg-base-200 rounded w-1/3 animate-pulse"></div>
                                        <div class="h-4 bg-base-200 rounded w-1/3 animate-pulse"></div>
                                    </div>
                                    <div class="flex justify-between">
                                        <div class="h-4 bg-base-200 rounded w-1/3 animate-pulse"></div>
                                        <div class="h-4 bg-base-200 rounded w-1/3 animate-pulse"></div>
                                    </div>
                                </div>
                            </div>
                        `;

                        activePopup = new maplibregl.Popup({ offset: 15, maxWidth: '300px' })
                            .setLngLat(coordinates)
                            .setHTML(skeletonHtml)
                            .addTo(map);

                        try {
                            const data = await @this.getStoreDetails(code);
                            
                            if (data) {
                                const gapColor = data.is_gap_negative ? 'text-error' : 'text-success';
                                
                                const html = `
                                    <div class="w-72 bg-base-100 flex flex-col">
                                        <div class="bg-primary p-4 pb-4 flex flex-col text-primary-content rounded-t-2xl pr-10">
                                            <h4 class="font-bold text-base leading-tight">${data.name}</h4>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <span class="text-xs opacity-90 font-mono">${code}</span>
                                                <span class="badge ${data.badge_class} badge-sm font-semibold border-0 shadow-sm text-[10px]">${data.status_skb}</span>
                                            </div>
                                        </div>
                                        <div class="p-4 flex flex-col gap-3">
                                            
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                <div class="flex flex-col">
                                                    <span class="text-base-content/60">Target Total</span>
                                                    <span class="font-bold text-sm">Rp ${data.target}</span>
                                                </div>
                                                <div class="flex flex-col text-right">
                                                    <span class="text-base-content/60">Actual Total</span>
                                                    <span class="font-bold text-sm">Rp ${data.actual}</span>
                                                </div>
                                            </div>

                                            <div class="divider my-0 h-1"></div>

                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-base-content/60">Actual Per Bulan</span>
                                            </div>
                                            <div class="grid grid-cols-3 gap-1 text-center font-mono text-[10px]">
                                                <div class="bg-base-200 p-1 rounded">M1<br><span class="font-bold text-base-content">${data.m1}</span></div>
                                                <div class="bg-base-200 p-1 rounded">M2<br><span class="font-bold text-base-content">${data.m2}</span></div>
                                                <div class="bg-base-200 p-1 rounded">M3<br><span class="font-bold text-base-content">${data.m3}</span></div>
                                            </div>

                                            <div class="divider my-0 h-1"></div>
                                            
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="text-base-content/60 font-semibold">GAP</span>
                                                <span class="font-bold ${gapColor}">Rp ${data.gap}</span>
                                            </div>
                                            
                                            <div class="flex flex-col mt-1">
                                                <span class="text-xs text-base-content/60 font-semibold mb-1">Remarks (Action Detail)</span>
                                                <div class="text-[11px] text-base-content/80 leading-snug bg-base-200/50 p-2 rounded border border-base-200">
                                                    ${data.action_remark}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                activePopup.setHTML(html);
                            } else {
                                activePopup.setHTML('<div class="p-4 text-sm text-center">Data toko tidak ditemukan</div>');
                            }
                        } catch (e) {
                            console.error(e);
                            activePopup.setHTML('<div class="p-4 text-sm text-center text-error">Gagal mengambil data</div>');
                        }
                    });
                }

                if (fitBounds) {
                    if (hasValidPoints) {
                        map.fitBounds(bounds, {
                            padding: 60,
                            maxZoom: 14,
                            duration: 1200,
                            pitchWithRotate: false
                        });
                    } else if (features.length === 0 && currentData.length > 0) {
                        // All filtered out, do not fly
                    } else {
                        map.flyTo({
                            center: [106.827153, -6.175392],
                            zoom: 5,
                            duration: 1000
                        });
                    }
                }
            }

            Livewire.on('open-map-modal', (data) => {
                currentData = data.mapData || (data[0] ? data[0].mapData : []);
                if (!Array.isArray(currentData)) {
                    currentData = [];
                }

                const mapModal = document.getElementById('map_modal');
                mapModal.showModal();

                if (!map) {
                    map = new maplibregl.Map({
                        container: 'pencapaian-map',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: [106.827153, -6.175392], // Default Jakarta
                        zoom: 5,
                        attributionControl: false // Cleaner look
                    });
                    
                    // Add clean attribution
                    map.addControl(new maplibregl.AttributionControl({
                        compact: true
                    }));
                    
                    map.addControl(new maplibregl.NavigationControl({
                        showCompass: false
                    }), 'top-right');

                    map.on('load', () => {
                        map.resize();
                        renderPoints();
                    });
                } else {
                    setTimeout(() => {
                        map.resize();
                        renderPoints();
                    }, 150);
                }
            });
        });
    </script>
@endpush
