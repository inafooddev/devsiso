{{-- ============================================================
     SCRIPTS: Alpine.js mapComponent + Leaflet Logic
     ============================================================ --}}
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mapComponent', (wire) => ({

            // === STATE ===
            map: null,
            layerGroup: null,
            storesData: [],
            salesmanLegend: [],
            legendType: 'day',
            _hasFitted: false,

            dayColors: @js($dayColors),
            options: @js($options),

            weekColors: {
                'Week 1': '#2563EB',
                'Week 2': '#10B981',
                'Week 3': '#F59E0B',
                'Week 4': '#E11D48',
            },

            // === SYNC DAISY UI <dialog> DENGAN LIVEWIRE STATE ===
            // Dipanggil dari x-effect di container — reactive terhadap $wire.showXxx
            syncDialog(id, isOpen) {
                const el = document.getElementById(id);
                if (!el) return;
                if (isOpen && !el.open) {
                    el.showModal();
                } else if (!isOpen && el.open) {
                    el.close();
                }
            },

            // === INISIALISASI PETA ===
            initMap() {
                setTimeout(() => {
                    this.map = L.map('map', {
                        zoomControl: false,
                        preferCanvas: true,
                        inertia: true,
                        zoomSnap: 0.25,
                    }).setView([-6.5950, 106.7900], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        keepBuffer: 4,
                    }).addTo(this.map);

                    L.control.zoom({ position: 'topright' }).addTo(this.map);
                    this.layerGroup = L.layerGroup().addTo(this.map);

                    // Auto-toggle tooltip berdasarkan zoom
                    this.map.on('zoomend', () => {
                        const currentZoom = this.map.getZoom();
                        this.layerGroup.eachLayer((layer) => {
                            if (layer.getTooltip()) {
                                currentZoom >= 16 ? layer.openTooltip() : layer.closeTooltip();
                            }
                        });
                    });

                    // Load data awal jika filter sudah aktif
                    if (wire.get('isFilterApplied')) {
                        const initialStores = wire.get('filteredStores');
                        if (initialStores) {
                            this.storesData = initialStores;
                            this.renderMarkers();
                        }
                    }
                }, 100);

                // Listener event dari Livewire saat filter diperbarui
                window.addEventListener('filters-updated', (e) => {
                    this._hasFitted = false;
                    this.map.invalidateSize();
                    this.storesData = e.detail.stores;
                    this.renderMarkers(e.detail.stores);
                });
            },

            // === HANDLER EDIT (sidebar) ===
            handleEdit(store) {
                // Jika tidak ada koordinat GPS, buka modal edit
                if (!store.lat || !store.lng || store.lat == 0 || store.lng == 0) {
                    wire.editSchedule(store);
                } else {
                    this.flyToStore(store);
                }
            },

            // === WARNA SALESMAN (hash deterministik) ===
            getSalesmanColor(name) {
                const palette = [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
                    '#8B5CF6', '#06B6D4', '#EC4899', '#6366F1',
                    '#14B8A6', '#F97316',
                ];
                if (!name) return '#9CA3AF';
                let hash = 0;
                for (let i = 0; i < name.length; i++) {
                    hash = name.charCodeAt(i) + ((hash << 5) - hash);
                }
                return palette[Math.abs(hash) % palette.length];
            },

            // === WARNA MARKER BERDASARKAN MODE LEGENDA ===
            getStoreColor(store) {
                if (this.legendType === 'day') {
                    const conf = this.dayColors[store.day] || { ganjil: '#333', genap: '#000' };
                    const isGanjil = store.weeks.includes('Week 1') || store.weeks.includes('Week 3');
                    return isGanjil ? conf.ganjil : conf.genap;
                }

                if (this.legendType === 'week') {
                    for (let i = 1; i <= 4; i++) {
                        if (store.weeks.includes(`Week ${i}`)) return this.weekColors[`Week ${i}`];
                    }
                    return '#9CA3AF';
                }

                if (this.legendType === 'salesman') {
                    return store.se_color || '#9CA3AF';
                }

                return '#333';
            },

            // === RENDER SEMUA MARKER ===
            renderMarkers(manualData = null) {
                if (!this.layerGroup) return;

                this.layerGroup.clearLayers();

                const rawData = manualData || this.storesData;
                if (!rawData || !Array.isArray(rawData) || rawData.length === 0) return;

                const bounds = L.latLngBounds();
                let hasValidPoints = false;

                requestAnimationFrame(() => {
                    rawData.forEach(store => {
                        if (!store.lat || !store.lng || store.lat == 0 || store.lng == 0) return;

                        const color = this.getStoreColor(store);
                        const marker = L.circleMarker([store.lat, store.lng], {
                            radius: 7,
                            color: '#ffffff',
                            weight: 2,
                            fillColor: color,
                            fillOpacity: 0.9,
                        })
                        .bindTooltip(store.name, {
                            permanent: false,
                            direction: 'top',
                            offset: [0, -10],
                            className: 'custom-tooltip',
                        })
                        .bindPopup(() => this.generatePopupHtml(store, color));

                        this.layerGroup.addLayer(marker);
                        bounds.extend([store.lat, store.lng]);
                        hasValidPoints = true;
                    });

                    // Buka tooltip jika zoom tinggi
                    if (this.map.getZoom() >= 16) {
                        this.layerGroup.eachLayer(l => l.openTooltip());
                    }

                    // Auto-fit bounds hanya sekali setelah filter pertama kali diterapkan
                    if (!this._hasFitted && hasValidPoints) {
                        this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
                        this._hasFitted = true;
                    }
                });
            },

            // === POPUP HTML UNTUK MARKER ===
            generatePopupHtml(store, color) {
                const listSalesman = @this.get('salesmen');
                const slsOpts = listSalesman
                    .map(s => `<option value="${s.slsno}" ${s.slsno == store.slsno ? 'selected' : ''}>${s.slsname}</option>`)
                    .join('');

                const dayOpts = this.options.days
                    .map(d => `<option value="${d}" ${d === store.day ? 'selected' : ''}>${d}</option>`)
                    .join('');

                const weekChecks = this.options.weeks.map(w => {
                    const checked = store.weeks.includes(w) ? 'checked' : '';
                    return `
                        <label class="flex items-center gap-1.5 p-1 border rounded bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="week-check-${store.frute_id}" value="${w}" ${checked}>
                            <span class="text-[10px]">${w.replace('Week ', 'W')}</span>
                        </label>`;
                }).join('');

                return `
                    <div class="min-w-[260px] text-gray-800">
                        <div class="flex items-center gap-3 border-b pb-2 mb-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold"
                                 style="background:${color}">${store.day.substr(0, 1)}</div>
                            <div>
                                <h4 class="font-bold text-xs">${store.name}</h4>
                                <div class="text-[9px] text-gray-500 font-mono">${store.code}</div>
                            </div>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Ganti Salesman (SE)</label>
                                <select id="sls-${store.frute_id}" class="w-full border rounded p-1.5 bg-white border-blue-200">${slsOpts}</select>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Jadwal Minggu</label>
                                <div class="grid grid-cols-2 gap-1.5">${weekChecks}</div>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Hari Kunjungan</label>
                                <select id="day-${store.frute_id}" class="w-full border rounded p-1.5 bg-white">${dayOpts}</select>
                            </div>
                        </div>
                        <button onclick="window.saveFromPopup(${store.frute_id})"
                                class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white py-1.5 rounded font-bold transition">
                            Simpan Perubahan
                        </button>
                    </div>`;
            },

            // === FLY TO & BUKA POPUP ===
            flyToStore(store) {
                if (this.map && store.lat && store.lng) {
                    this.map.flyTo([store.lat, store.lng], 13);
                    setTimeout(() => {
                        this.layerGroup.eachLayer(layer => {
                            const latLng = layer.getLatLng();
                            if (latLng.lat === store.lat && latLng.lng === store.lng) {
                                layer.openPopup();
                            }
                        });
                    }, 500);
                }
            },

        })); // end Alpine.data

        // === GLOBAL HELPERS ===

        /** Dipanggil dari tombol "Simpan Perubahan" di dalam popup marker */
        window.saveFromPopup = (fruteId) => {
            const weekChecks = document.querySelectorAll(`.week-check-${fruteId}:checked`);
            const newWeeks  = Array.from(weekChecks).map(c => c.value);
            const newDay    = document.getElementById(`day-${fruteId}`).value;
            const newSlsNo  = document.getElementById(`sls-${fruteId}`).value;

            if (newWeeks.length === 0) { alert('Minimal pilih 1 minggu!'); return; }
            @this.saveStore(fruteId, newWeeks, newDay, newSlsNo);
        };

        /** Dipanggil dari tombol "Simpan Jadwal" di modal edit (sidebar) */
        window.saveManualEdit = () => {
            const store = @this.get('editingStore');
            if (!store) return;

            const weekChecks = document.querySelectorAll('.week-edit-check:checked');
            const newWeeks   = Array.from(weekChecks).map(c => c.value);
            const newDay     = document.getElementById('edit-day-select').value;
            const newSlsNo   = @this.get('selectedSalesmanInModal');

            if (newWeeks.length === 0) { alert('Minimal pilih 1 minggu!'); return; }
            @this.saveStore(store.frute_id, newWeeks, newDay, newSlsNo);
            @this.set('showEditScheduleModal', false);
        };

    }); // end alpine:init

    /** Helper global: panggil handleEdit dari luar Alpine (elemen di sidebar) */
    function handleEdit(store) {
        const alpineEl = document.querySelector('[x-data^="mapComponent"]');
        if (alpineEl && alpineEl.__x) alpineEl.__x.$data.handleEdit(store);
    }
</script>
@endpush
