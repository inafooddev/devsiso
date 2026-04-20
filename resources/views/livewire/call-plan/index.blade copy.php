@push('styles')
<style>
.custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f3f4f6; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .leaflet-div-icon { background: transparent; border: none; }
        .leaflet-popup-content-wrapper { border-radius: 8px; padding: 0; overflow: hidden; }
        .leaflet-popup-content { margin: 12px; width: auto !important; }
        [x-cloak] { display: none !important; }
</style>
@endpush

<!-- Content Section -->
    <div x-data="mapComponent()" x-init="initMap()" class="flex h-screen w-full overflow-hidden">


        <!-- A. LEFT SIDEBAR (FILTER & LIST) --> 
        <div class="w-[340px] flex flex-col bg-gray-50 border-r border-gray-200 shadow-xl z-20 h-full flex-shrink-0 min-h-0">
            
            <!-- A1. Header & Filter Utama -->
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="font-bold text-gray-700 text-lg">Data Kunjungan</h1>
                    <button @click="showFilterModal = true" class="text-blue-600 hover:text-blue-800 text-xs font-semibold flex items-center gap-1 border border-blue-200 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded transition">
                        <i class="fas fa-sliders-h"></i> Filter Lanjutan
                    </button>
                </div>

                <!-- Filter Groups -->
                <div class="flex gap-2">
                    <!-- Week Filter -->
                    <div class="flex-1">
                        <div class="text-[10px] text-gray-400 font-bold mb-1 tracking-wider">MINGGU</div>
                        <div class="flex flex-col gap-1">
                            <template x-for="week in options.weeks" :key="week">
                                <button 
                                    @click="toggleFilter('selectedWeeks', week)"
                                    class="relative w-full text-xs py-1.5 px-3 rounded text-left transition border group"
                                    :class="selectedWeeks.includes(week) 
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                                    <span x-text="week"></span>
                                    <i x-show="selectedWeeks.includes(week)" class="fas fa-check absolute right-2 top-2 text-[10px]"></i>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Day Filter -->
                    <div class="flex-1">
                        <div class="text-[10px] text-gray-400 font-bold mb-1 tracking-wider">HARI</div>
                        <div class="flex flex-col gap-1">
                            <template x-for="day in options.days" :key="day">
                                <button 
                                    @click="toggleFilter('selectedDays', day)"
                                    class="relative w-full text-xs py-1.5 px-3 rounded text-left transition border overflow-hidden"
                                    :class="selectedDays.includes(day) 
                                        ? 'text-white border-transparent shadow-sm' 
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                                    :style="selectedDays.includes(day) ? `background: linear-gradient(to right, ${dayColors[day].ganjil}, ${dayColors[day].genap})` : ''">
                                    
                                    <span x-text="day" class="relative z-10 drop-shadow-sm"></span>
                                    <i x-show="selectedDays.includes(day)" class="fas fa-check absolute right-2 top-2 text-[10px] z-10"></i>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Summary Info -->
                <div class="mt-3 flex justify-between items-center text-[10px] text-gray-500 pt-2 border-t border-gray-200">
                    <span>Menampilkan: <b x-text="filteredStores.length"></b> toko</span>

                    <div class="flex gap-2">
                        <!-- SELECT ALL -->
                        <button 
                            @click="selectAllFilters()"
                            class="text-blue-500 hover:underline font-semibold">
                            Select All
                        </button>

                        <!-- RESET -->
                        <button 
                            @click="resetFilters()"
                            class="text-red-500 hover:underline font-semibold">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- A2. Action Button -->
            <div class="px-4 py-2 bg-white border-b border-gray-100">
                <button @click="showAddModal = true" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 rounded shadow-sm text-sm transition flex justify-center items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Tambah Toko Baru
                </button>
            </div>

            <!-- A3. List Data (Table) -->
            <div class="flex-1 overflow-y-auto custom-scroll bg-gray-50 p-2">
                <template x-for="(store, index) in filteredStores" :key="store.id">
                    <div @click="flyToStore(store)" 
                         class="bg-white p-3 rounded border border-gray-200 mb-2 cursor-pointer hover:shadow-md hover:border-blue-300 transition group relative">
                        
                        <!-- Status Bar Kiri (Warna Hari) -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l" 
                             :style="`background-color: ${getStoreColor(store)}`"></div>

                        <div class="pl-2 flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-sm text-gray-800" x-text="store.name"></h3>
                                <div class="text-[10px] font-mono text-gray-500 mt-0.5" x-text="store.code"></div>
                                <div class="flex items-center gap-2 mt-1.5 text-[10px] text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-calendar-alt text-gray-300"></i>
                                        <span x-text="store.day"></span>
                                    </span>
                                    <span class="text-gray-300">|</span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-user text-gray-300"></i>
                                        <span class="truncate max-w-[80px]" x-text="store.salesman"></span>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Action Icons -->
                            <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1.5 rounded"><i class="fas fa-pen text-xs"></i></button>
                                <button class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded"><i class="fas fa-trash text-xs"></i></button>
                            </div>
                        </div>

                        <!-- Badge Weeks -->
                        <div class="mt-2 flex flex-wrap gap-1 pl-2">
                            <template x-for="w in store.weeks">
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 border border-gray-200" x-text="w.replace('Week ', 'W')"></span>
                            </template>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="filteredStores.length === 0" class="text-center py-10 text-gray-400">
                    <i class="fas fa-search-location text-3xl mb-2 opacity-50"></i>
                    <p class="text-xs">Tidak ada toko yang sesuai filter</p>
                </div>
            </div>
        </div>

        <!-- B. RIGHT CONTENT (MAP) -->
        <div class="flex-1 relative bg-gray-200">
            <div id="map" class="h-full w-full outline-none"></div>
            
            <!-- Map Legend / Controls -->
            <div class="absolute top-4 right-4 z-[1000] bg-white p-2 rounded shadow-md text-[10px] space-y-1 opacity-90">
                <div class="font-bold border-b pb-1 mb-1">Legenda Warna</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-red-500"></span> Senin</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-orange-500"></span> Selasa</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-yellow-500"></span> Rabu</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-green-500"></span> Kamis</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-blue-500"></span> Jumat</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-indigo-500"></span> Sabtu</div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-purple-500"></span> Minggu</div>
                <div class="pt-1 mt-1 border-t italic text-gray-400">Gelap = Genap Only</div>
            </div>
        </div>

        <!-- C. MODAL FILTER (ADVANCED) -->
        <div x-show="showFilterModal" 
             style="display: none;"
             x-transition.opacity
             class="fixed inset-0 z-[2000] bg-black/50 backdrop-blur-sm flex items-start justify-center">
            
            <div class="bg-white rounded-xl shadow-2xl w-[400px] max-w-full m-4 overflow-hidden transform transition-all" @click.away="showFilterModal = false">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800">Filter Lanjutan</h3>
                    <button @click="showFilterModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                
                <div class="p-6 space-y-4">
                    <template x-for="(items, label) in advancedFilters" :key="label">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5" x-text="label"></label>
                            <select class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50 border p-2">
                                <option value="">Semua</option>
                                <template x-for="item in items"><option x-text="item"></option></template>
                            </select>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    <button @click="showFilterModal = false" class="px-4 py-2 rounded text-sm font-medium text-gray-600 hover:bg-gray-200 transition">Batal</button>
                    <button @click="showFilterModal = false" class="px-4 py-2 rounded text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition">Terapkan Filter</button>
                </div>
            </div>
        </div>

        <!-- D. MODAL TAMBAH TOKO -->
        <div x-show="showAddModal" 
             style="display: none;"
             x-transition.opacity
             class="fixed inset-0 z-[2000] bg-black/50 backdrop-blur-sm flex items-start justify-center">
            
            <div class="bg-white rounded-xl shadow-2xl w-[500px] max-w-full m-4 overflow-hidden transform transition-all" @click.away="showAddModal = false">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800">Tambah Toko Baru</h3>
                    <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                
                <div class="p-6">
                    <!-- Search Box -->
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input x-model="addStoreSearch" 
                               @input="performStoreSearch()"
                               type="text" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 outline-none transition" 
                               placeholder="Cari nama toko atau kode...">
                    </div>

                    <!-- Search Results -->
                    <div class="mt-4 max-h-[250px] overflow-y-auto custom-scroll border border-gray-100 rounded-lg" x-show="addStoreSearch.length > 0">
                        <template x-for="result in searchResults" :key="result.id">
                            <div class="p-3 hover:bg-emerald-50 border-b border-gray-100 last:border-0 cursor-pointer flex justify-between items-center group transition">
                                <div>
                                    <div class="font-bold text-gray-700 text-sm" x-text="result.name"></div>
                                    <div class="text-[10px] text-gray-500" x-text="result.address"></div>
                                </div>
                                <button @click="addNewStore(result)" class="text-emerald-600 hover:text-emerald-800 bg-white border border-emerald-200 px-3 py-1 rounded text-xs font-bold shadow-sm opacity-0 group-hover:opacity-100 transition">
                                    <i class="fas fa-plus"></i> Pilih
                                </button>
                            </div>
                        </template>
                        <div x-show="searchResults.length === 0" class="p-4 text-center text-gray-500 text-xs">
                            <i class="fas fa-times-circle text-gray-300 text-xl mb-1 block"></i>
                            Tidak ditemukan toko yang cocok.
                        </div>
                    </div>

                    <!-- Empty State for Search -->
                    <div x-show="addStoreSearch.length === 0" class="p-8 text-center text-gray-400">
                        <i class="fas fa-store text-4xl mb-2 opacity-30"></i>
                        <p class="text-xs">Ketik nama toko untuk mencari database master.</p>
                        <p class="text-[10px] text-gray-300 mt-1">(Contoh: "Warung", "Toko", "Sehat")</p>
                    </div>
                </div>
            </div>
        </div>

    </div>


@push('scripts')
<script>
// Simulasi Data
        const db_stores = [
            // SENIN
            { id: 1, code: 'STR-SEN-001', name: 'Toko Berkah Jaya', weeks: ['Week 1', 'Week 3'], day: 'Senin', salesman: 'Budi Santoso', lat: -6.5850, lng: 106.7900 },
            { id: 2, code: 'STR-SEN-002', name: 'Warung Kelontong Madura', weeks: ['Week 2', 'Week 4'], day: 'Senin', salesman: 'Siti Aminah', lat: -6.5890, lng: 106.7950 },
            { id: 3, code: 'STR-SEN-003', name: 'TB. Sinar Abadi', weeks: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], day: 'Senin', salesman: 'Rudi Hermawan', lat: -6.5820, lng: 106.7850 },
            
            // SELASA
            { id: 4, code: 'STR-SEL-001', name: 'Toko Plastik 88', weeks: ['Week 1', 'Week 3'], day: 'Selasa', salesman: 'Dewi Lestari', lat: -6.5920, lng: 106.8000 },
            { id: 5, code: 'STR-SEL-002', name: 'Warung Nasi Padang', weeks: ['Week 2'], day: 'Selasa', salesman: 'Budi Santoso', lat: -6.5950, lng: 106.8050 },
            
            // RABU
            { id: 6, code: 'STR-RAB-001', name: 'Minimarket Kita', weeks: ['Week 1', 'Week 3'], day: 'Rabu', salesman: 'Siti Aminah', lat: -6.6000, lng: 106.7900 },
            { id: 7, code: 'STR-RAB-002', name: 'Toko Obat Sehat', weeks: ['Week 2', 'Week 4'], day: 'Rabu', salesman: 'Rudi Hermawan', lat: -6.6050, lng: 106.7880 },

            // KAMIS
            { id: 8, code: 'STR-KAM-001', name: 'Bengkel Motor Rizky', weeks: ['Week 1', 'Week 3'], day: 'Kamis', salesman: 'Dewi Lestari', lat: -6.5750, lng: 106.7800 },
            { id: 9, code: 'STR-KAM-002', name: 'Toko Cat Warna Warni', weeks: ['Week 2', 'Week 4'], day: 'Kamis', salesman: 'Budi Santoso', lat: -6.5780, lng: 106.7750 },

            // JUMAT
            { id: 10, code: 'STR-JUM-001', name: 'Warung Kopi Pak Asep', weeks: ['Week 1', 'Week 3'], day: 'Jumat', salesman: 'Siti Aminah', lat: -6.6100, lng: 106.7950 },
            { id: 11, code: 'STR-JUM-002', name: 'Toko Sembako Murah', weeks: ['Week 1', 'Week 2', 'Week 3', 'Week 4'], day: 'Jumat', salesman: 'Rudi Hermawan', lat: -6.6150, lng: 106.8000 },

            // SABTU
            { id: 12, code: 'STR-SAB-001', name: 'Distro Gaul', weeks: ['Week 1', 'Week 3'], day: 'Sabtu', salesman: 'Dewi Lestari', lat: -6.5700, lng: 106.7900 },
            { id: 13, code: 'STR-SAB-002', name: 'Toko Sepatu Cibaduyut', weeks: ['Week 2', 'Week 4'], day: 'Sabtu', salesman: 'Budi Santoso', lat: -6.5650, lng: 106.7850 },

            // MINGGU
            { id: 14, code: 'STR-MIN-001', name: 'Pasar Buah Segar', weeks: ['Week 1', 'Week 3'], day: 'Minggu', salesman: 'Siti Aminah', lat: -6.5600, lng: 106.8050 },
            { id: 15, code: 'STR-MIN-002', name: 'Toko Mainan Anak', weeks: ['Week 2', 'Week 4'], day: 'Minggu', salesman: 'Rudi Hermawan', lat: -6.5550, lng: 106.8100 }
        ];

        // Simulasi Database Master Toko (untuk pencarian tambah toko)
        const master_store_db = [
            { id: 101, name: 'Warung Bu Eti', address: 'Jl. Raya Dramaga No. 10', lat: -6.5870, lng: 106.7500 },
            { id: 102, name: 'Toko Elektronik Canggih', address: 'Jl. Merdeka No. 45', lat: -6.5950, lng: 106.7930 },
            { id: 103, name: 'Bakso Mas Kumis', address: 'Jl. Pajajaran No. 12', lat: -6.6020, lng: 106.8100 },
            { id: 104, name: 'Toko Kue Enak', address: 'Jl. Siliwangi No. 8', lat: -6.6150, lng: 106.8050 },
            { id: 105, name: 'Apotek Sehat Selalu', address: 'Jl. Sudirman No. 99', lat: -6.5800, lng: 106.7880 },
            { id: 106, name: 'Toko Buku Pintar', address: 'Jl. Ahmad Yani No. 5', lat: -6.5750, lng: 106.7950 },
            { id: 107, name: 'Warung Sate Madura', address: 'Jl. Pemuda No. 20', lat: -6.5680, lng: 106.8000 },
            { id: 108, name: 'Toko Baju Murah', address: 'Jl. Kebon Pedes No. 3', lat: -6.5600, lng: 106.8050 }
        ];

        // Definisi Logic Alpine (Dipindah ke Head agar terbaca sebelum Alpine Init)
        document.addEventListener('alpine:init', () => {
            Alpine.data('mapComponent', () => ({
                // --- STATE ---
                stores: db_stores,
                filteredStores: [],
                map: null,
                layerGroup: null,
                showFilterModal: false,
                showAddModal: false,

                // Search States (Tambah Toko)
                addStoreSearch: '',
                searchResults: [],
                
                // Filter States
                selectedWeeks: ['Week 1'],
                selectedDays: ['Senin'],
                
                // Config
                options: {
                    weeks: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    days: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                    salesmen: ['Budi Santoso', 'Siti Aminah', 'Rudi Hermawan', 'Dewi Lestari']
                },
                advancedFilters: {
                    'Region': ['Region Jawa Barat', 'Region Jakarta', 'Region Banten'],
                    'Area': ['Bogor Kota', 'Bogor Kabupaten', 'Depok'],
                    'Distributor': ['PT Distribusi Utama', 'PT Mitra Abadi', 'CV Lancar Jaya'],
                    'Salesmen': ['Budi Santoso', 'Siti Aminah', 'Rudi Hermawan', 'Dewi Lestari']
                },
                dayColors: {
                    'Senin': { ganjil: '#EF4444', genap: '#991B1B' },
                    'Selasa': { ganjil: '#F97316', genap: '#9A3412' },
                    'Rabu': { ganjil: '#EAB308', genap: '#854D0E' },
                    'Kamis': { ganjil: '#22C55E', genap: '#14532D' },
                    'Jumat': { ganjil: '#3B82F6', genap: '#1E3A8A' },
                    'Sabtu': { ganjil: '#6366F1', genap: '#312E81' },
                    'Minggu': { ganjil: '#A855F7', genap: '#581C87' }
                },

                // --- INIT ---
                initMap() {
                    window.MapApp = this;
                    this.applyFilters(); 

                    setTimeout(() => {
                        this.map = L.map('map', { zoomControl: false }).setView([-6.5950, 106.7900], 13);
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap',
                            maxZoom: 19
                        }).addTo(this.map);
                        
                        L.control.zoom({ position: 'topright' }).addTo(this.map);
                        
                        this.layerGroup = L.layerGroup().addTo(this.map);
                        this.renderMarkers();
                    }, 100);
                },

                // --- SEARCH LOGIC (TAMBAH TOKO) ---
                performStoreSearch() {
                    if (this.addStoreSearch.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    const keyword = this.addStoreSearch.toLowerCase();
                    // Filter dari master DB
                    this.searchResults = master_store_db.filter(s => 
                        s.name.toLowerCase().includes(keyword) || 
                        s.address.toLowerCase().includes(keyword)
                    );
                },

                addNewStore(masterStore) {
                    // Logic untuk menambahkan toko baru dari master ke list aktif
                    const newId = this.stores.length > 0 ? Math.max(...this.stores.map(s => s.id)) + 1 : 1;
                    
                    const newStore = {
                        id: newId,
                        code: `NEW-${newId.toString().padStart(3, '0')}`,
                        name: masterStore.name,
                        weeks: ['Week 1'], // Default
                        day: 'Senin', // Default
                        salesman: 'Budi Santoso', // Default
                        lat: masterStore.lat,
                        lng: masterStore.lng
                    };

                    this.stores.push(newStore);
                    this.addStoreSearch = '';
                    this.searchResults = [];
                    this.showAddModal = false;
                    
                    // Reset filter agar toko baru terlihat (opsional, atau biarkan filter aktif)
                    // this.resetFilters(); 
                    this.applyFilters();
                    
                    // Fly to new store
                    setTimeout(() => {
                        this.flyToStore(newStore);
                        alert(`Toko "${newStore.name}" berhasil ditambahkan!`);
                    }, 300);
                },

                // --- LOGIC ---
                toggleFilter(type, value) {
                    const arr = this[type];
                    if (arr.includes(value)) {
                        this[type] = arr.filter(item => item !== value);
                    } else {
                        this[type].push(value);
                    }
                    this.applyFilters();
                },

                resetFilters() {
                    this.selectedWeeks = ['Week 1'];
                    this.selectedDays  = ['Senin'];
                    this.applyFilters();
                },

                selectAllFilters() {
                    this.selectedWeeks = [...this.options.weeks];
                    this.selectedDays  = [...this.options.days];
                    this.applyFilters();
                },

                applyFilters() {
                    if (this.selectedWeeks.length === 0 || this.selectedDays.length === 0) {
                        this.filteredStores = [];
                    } else {
                        this.filteredStores = this.stores.filter(store => {
                            const hasWeek = store.weeks.some(w => this.selectedWeeks.includes(w));
                            const hasDay = this.selectedDays.includes(store.day);
                            return hasWeek && hasDay;
                        });
                    }
                    this.renderMarkers();
                },

                getStoreColor(store) {
                    const conf = this.dayColors[store.day] || { ganjil:'#333', genap:'#000' };
                    const isGanjil = store.weeks.includes('Week 1') || store.weeks.includes('Week 3');
                    return isGanjil ? conf.ganjil : conf.genap;
                },

                renderMarkers() {
                    if (!this.layerGroup) return;
                    this.layerGroup.clearLayers();

                    const bounds = L.latLngBounds();

                    this.filteredStores.forEach(store => {
                        const color = this.getStoreColor(store);
                        const weekLabel = store.weeks.map(w => w.replace('Week ', '')).join(',');

                        const icon = L.divIcon({
                            className: '',
                            html: `
                                <div class="relative transition-transform hover:scale-110 hover:-translate-y-1 duration-200">
                                    <i class="fas fa-map-marker-alt text-[32px] drop-shadow-md" style="color: ${color}; -webkit-text-stroke: 1px white;"></i>
                                    <span class="absolute top-1 left-0 w-full text-center text-[9px] font-bold text-white pointer-events-none">${weekLabel}</span>
                                </div>
                            `,
                            iconSize: [32, 42],
                            iconAnchor: [16, 40],
                            popupAnchor: [0, -38]
                        });

                        const popupContent = this.generatePopupHtml(store, color);

                        const marker = L.marker([store.lat, store.lng], { icon: icon })
                            .bindPopup(popupContent);
                        
                        this.layerGroup.addLayer(marker);
                        bounds.extend([store.lat, store.lng]);
                    });

                    if (this.filteredStores.length > 0 && this.map) {
                        this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
                    }
                },

                generatePopupHtml(store, color) {
                    const weekChecks = this.options.weeks.map(w => {
                        const checked = store.weeks.includes(w) ? 'checked' : '';
                        return `
                            <label class="cursor-pointer select-none border border-gray-200 rounded px-2 py-1 bg-gray-50 hover:bg-blue-50 flex items-center gap-1.5 transition">
                                <input type="checkbox" class="week-check-${store.id} accent-blue-600" value="${w}" ${checked}>
                                <span class="text-[10px] text-gray-700 font-medium">${w.replace('Week ', 'W')}</span>
                            </label>
                        `;
                    }).join('');

                    const dayOpts = this.options.days.map(d => 
                        `<option value="${d}" ${d === store.day ? 'selected' : ''}>${d}</option>`
                    ).join('');
                    
                    const salesOpts = this.options.salesmen.map(s => 
                        `<option value="${s}" ${s === store.salesman ? 'selected' : ''}>${s}</option>`
                    ).join('');

                    return `
                        <div class="min-w-[260px] font-sans text-gray-800">
                            <div class="flex items-center gap-3 border-b border-gray-100 pb-3 mb-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-sm" style="background-color: ${color}">
                                    ${store.day.substr(0,1)}
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <h4 class="font-bold text-sm truncate" title="${store.name}">${store.name}</h4>
                                    <div class="text-[10px] text-gray-500 font-mono bg-gray-100 inline-block px-1.5 rounded mt-0.5">${store.code}</div>
                                </div>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block text-gray-500 font-semibold mb-1.5">Jadwal Kunjungan (Minggu)</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        ${weekChecks}
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-gray-500 font-semibold mb-1">Hari</label>
                                        <select id="day-${store.id}" class="w-full border-gray-300 rounded bg-white py-1.5 px-2 focus:ring-1 focus:ring-blue-500 border">
                                            ${dayOpts}
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-500 font-semibold mb-1">Salesman</label>
                                        <select id="sales-${store.id}" class="w-full border-gray-300 rounded bg-white py-1.5 px-2 focus:ring-1 focus:ring-blue-500 border">
                                            ${salesOpts}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-end">
                                <button onclick="window.MapApp.saveStore(${store.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-xs font-bold shadow-sm transition flex items-center gap-2">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                            </div>
                        </div>
                    `;
                },

                saveStore(id) {
                    const store = this.stores.find(s => s.id === id);
                    if(!store) return;

                    const weekChecks = document.querySelectorAll(`.week-check-${id}:checked`);
                    const newWeeks = Array.from(weekChecks).map(c => c.value);
                    const newDay = document.getElementById(`day-${id}`).value;
                    const newSales = document.getElementById(`sales-${id}`).value;

                    if (newWeeks.length === 0) {
                        alert('Minimal pilih 1 minggu kunjungan!');
                        return;
                    }

                    store.weeks = newWeeks;
                    store.day = newDay;
                    store.salesman = newSales;

                    this.applyFilters();
                    
                    setTimeout(() => {
                        this.flyToStore(store);
                    }, 100);
                },

                flyToStore(store) {
                    if (this.map) {
                        this.map.flyTo([store.lat, store.lng], 16);
                        this.layerGroup.eachLayer(layer => {
                            const latLng = layer.getLatLng();
                            if(latLng.lat === store.lat && latLng.lng === store.lng) {
                                layer.openPopup();
                            }
                        });
                    }
                }

            }));
        });
</script>
@endpush