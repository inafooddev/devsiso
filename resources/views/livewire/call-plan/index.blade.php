<div>
    {{-- ============================================================
         CSS STYLES & LEAFLET CUSTOMIZATION
         ============================================================ --}}
    @push('styles')
    <style>
        /* Scrollbar kustom untuk sidebar dan modal */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f3f4f6; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* Leaflet UI Overrides */
        .leaflet-div-icon { background: transparent; border: none; }
        .leaflet-popup-content-wrapper { border-radius: 8px; padding: 0; overflow: hidden; }
        .leaflet-popup-content { margin: 12px; width: auto !important; }
        
        /* Tooltip style pada marker */
        .custom-tooltip {
            background: white !important;
            border: 1px solid #3b82f6 !important;
            border-radius: 4px !important;
            padding: 1px 6px !important;
            font-size: 10px !important;
            font-weight: bold !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
            pointer-events: none !important;
        }
        .leaflet-tooltip-top:before { display: none !important; }
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    {{-- ============================================================
         MAIN CONTAINER (Alpine.js Initialization)
         ============================================================ --}}
    <div x-data="mapComponent($wire)" x-init="initMap()" class="flex h-screen w-full overflow-hidden">
        
        <div class="w-[340px] flex flex-col bg-gray-50 border-r border-gray-200 shadow-xl z-20 h-full flex-shrink-0 min-h-0">
            
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="font-bold text-gray-700 text-lg">Data Kunjungan</h1>
                    <button @click="$wire.set('showFilterModal', true)" class="text-blue-600 hover:text-blue-800 text-xs font-semibold flex items-center gap-1 border border-blue-200 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded transition">
                        <i class="fas fa-sliders-h"></i> Filter Area
                    </button>
                </div>

                <div class="mb-3 {{ !$selectedDistributor ? 'opacity-50 pointer-events-none' : '' }}">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Pilih Salesman</label>
                    <select wire:model.live.debounce.250ms="selectedSalesman" class="w-full text-xs border-gray-200 rounded-lg p-2 bg-white border shadow-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Salesman --</option>
                        @if($selectedDistributor)
                            <option value="all" class="font-bold text-blue-600">-- SEMUA SALESMAN --</option>
                        @endif
                        @foreach($salesmen as $sls) 
                            <option value="{{ $sls->slsno }}">{{ $sls->slsname }}</option> 
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Mode Pewarnaan Titik</label>
                    <div class="flex bg-white border border-gray-200 rounded-lg p-1 shadow-sm">
                        <button @click="legendType = 'day'; renderMarkers()" :class="legendType === 'day' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50'" class="flex-1 py-1.5 text-[10px] font-bold rounded-md transition-all">HARI</button>
                        <button @click="legendType = 'week'; renderMarkers()" :class="legendType === 'week' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50'" class="flex-1 py-1.5 text-[10px] font-bold rounded-md transition-all">WEEK</button>
                        <button @click="legendType = 'salesman'; renderMarkers()" :class="legendType === 'salesman' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50'" class="flex-1 py-1.5 text-[10px] font-bold rounded-md transition-all">SE</button>
                    </div>
                </div>



                <div class="space-y-4"> <div class="mb-4">
        <button wire:click="toggleNonRute" 
            class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl border-2 transition-all font-bold text-xs {{ $showNonRute ? 'bg-red-500 border-red-600 text-white shadow-lg' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            <i class="fas {{ $showNonRute ? 'fa-eye' : 'fa-eye-slash' }}"></i>
            {{ $showNonRute ? 'MENAMPILKAN NON-RUTE' : 'LIHAT DATA NON-RUTE' }}
        </button>
    </div>

    <div class="flex gap-2 {{ $showNonRute ? 'opacity-30 pointer-events-none' : '' }}">
        
        <div class="flex-1">
            <div class="text-[10px] text-gray-400 font-bold mb-1 tracking-wider uppercase">Minggu</div>
            <div class="flex flex-col gap-1">
                @foreach($options['weeks'] as $week)
                    <button wire:click="toggleWeek('{{ $week }}')" wire:key="sidebar-week-{{ $week }}"
                        class="relative w-full text-xs py-1.5 px-3 rounded text-left transition border {{ in_array($week, $selectedWeeks) ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300' }}">
                        <span>{{ $week }}</span>
                        @if(in_array($week, $selectedWeeks)) <i class="fas fa-check absolute right-2 top-2 text-[10px]"></i> @endif
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex-1">
            <div class="text-[10px] text-gray-400 font-bold mb-1 tracking-wider uppercase">Hari</div>
            <div class="flex flex-col gap-1">
                @foreach($options['days'] as $day)
                    @php $isSelected = in_array($day, $selectedDays); @endphp
                    <button wire:click="toggleDay('{{ $day }}')" wire:key="sidebar-day-{{ $day }}"
                        class="relative w-full text-xs py-1.5 px-3 rounded text-left transition border overflow-hidden {{ $isSelected ? 'text-white border-transparent shadow-sm' : 'bg-white text-gray-600 border-gray-200' }}"
                        style="{{ $isSelected ? "background: linear-gradient(to right, {$dayColors[$day]['ganjil']}, {$dayColors[$day]['genap']})" : "" }}">
                        <span class="relative z-10">{{ $day }}</span>
                        @if($isSelected) <i class="fas fa-check absolute right-2 top-2 text-[10px] z-10"></i> @endif
                    </button>
                @endforeach
            </div>
        </div>
        
    </div>
</div>

                <div class="mt-3 flex justify-between items-center text-[10px] text-gray-500 pt-2 border-t border-gray-200">
                    <div class="flex flex-col gap-1">
                        <span>Menampilkan: <b>{{ $isFilterApplied ? count($this->filteredStores) : 0 }}</b> toko</span>
                        @if($isFilterApplied && $this->untaggedCount > 0)
                            <span class="flex items-center gap-1 text-red-500 font-semibold">
                                <i class="fas fa-map-marker-alt"></i> Untagged: {{ $this->untaggedCount }} toko
                            </span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="selectAllFilters()" class="text-blue-500 hover:underline font-semibold">Select All</button>
                        <button wire:click="resetFilters()" class="text-red-500 hover:underline font-semibold">Reset</button>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto custom-scroll bg-gray-50 p-2">
                <div class="px-3 py-2 flex items-center gap-2 sticky top-0 bg-white/80 backdrop-blur-sm z-10 border-b border-gray-100 mb-2">
                    <div class="relative flex-1 group">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs transition-colors group-focus-within:text-blue-500"></i>
                        <input wire:model.live.debounce.300ms="searchStore" type="text" placeholder="Cari toko atau alamat..." 
                            class="w-full text-xs pl-9 pr-3 py-2 bg-gray-100 border-transparent rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none border">
                    </div>
                    <button @click="$wire.set('showExportModal', true)" title="Export Excel" class="h-9 w-9 flex items-center justify-center bg-green-50 text-green-600 hover:bg-green-600 hover:text-white rounded-xl transition-all duration-200 border border-green-100 shadow-sm active:scale-95">
                        <i class="fas fa-file-excel text-sm"></i>
                    </button>
                    <button @click="$wire.set('showAddModal', true)" title="Tambah Customer" class="h-9 w-9 flex items-center justify-center bg-blue-600 text-white hover:bg-blue-700 rounded-xl transition-all duration-200 shadow-md shadow-blue-200 active:scale-95">
                        <i class="fas fa-user-plus text-sm"></i>
                    </button>
                </div>

                @if(!$isFilterApplied)
                    <div class="flex flex-col items-center justify-center h-full text-center p-6 bg-white/50 border-2 border-dashed border-gray-200 m-2 rounded-lg">
                        <i class="fas fa-filter text-blue-300 text-3xl mb-3"></i>
                        <p class="text-[11px] text-gray-500">Terapkan filter area dan pilih salesman.</p>
                    </div>
                @else
                    <div wire:loading.class="opacity-50 pointer-events-none">
                        @forelse($filteredStores as $store)
                            <div wire:key="store-row-{{ $store['frute_id'] }}" class="bg-white p-3 rounded border border-gray-200 mb-2 hover:shadow-md hover:border-blue-300 transition group relative">
                                <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l" 
                                        style="background-color: {{ $dayColors[$store['day']]['ganjil'] ?? '#9CA3AF' }}">
                                    </div>
                                
                                <div class="pl-2 flex justify-between items-start">
                                    <div @click="flyToStore(@js($store))" class="cursor-pointer flex-1">
                                        <h3 class="font-bold text-sm text-gray-800">{{ $store['code'] }} - {{ $store['name'] }}</h3>
                                        <p class="text-[10px] text-gray-500 line-clamp-1 mb-1">
                                            <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> {{ $store['address'] }}
                                        </p>
                                        @if(!$store['lat'] || $store['lat'] == 0)
                                            <span class="text-[9px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-bold uppercase">Belum Tagging Lokasi</span>
                                        @endif
                                        <div class="flex items-center gap-2 mt-1.5 text-[10px] text-gray-500">
                                            <span class="flex items-center gap-1"><i class="fas fa-calendar-alt opacity-50"></i>{{ $store['day'] }}</span>
                                            <span class="text-gray-300">|</span>
                                            <span class="flex items-center gap-1"><i class="fas fa-user opacity-50"></i>{{ $store['salesman'] }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="handleEdit(@js($store))" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1.5 rounded transition">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button wire:click="deleteStore({{ $store['frute_id'] }})" wire:confirm="Hapus rute ini?" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded transition">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-400 text-xs">Tidak ada data rute.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <div class="flex-1 relative bg-gray-200">
            <div id="map" class="h-full w-full outline-none" wire:ignore></div>
            
            @if(!$isFilterApplied)
                <div class="absolute inset-0 z-[1001] bg-gray-900/10 backdrop-blur-[2px] flex items-center justify-center pointer-events-none">
                    <div class="bg-white/90 px-6 py-3 rounded-full shadow-xl border border-white flex items-center gap-3 animate-bounce">
                        <i class="fas fa-hand-pointer text-blue-500"></i>
                        <span class="text-sm font-bold text-gray-700">Terapkan filter area untuk memulai</span>
                    </div>
                </div>
            @endif

            <div class="absolute top-4 right-4 z-[1000] bg-white p-3 rounded-lg shadow-lg text-[10px] space-y-2 max-h-[70%] overflow-y-auto custom-scroll w-44" wire:ignore>
                <div class="font-bold border-b pb-1.5 mb-1 text-gray-700 flex justify-between items-center">
                    <span>Legenda Warna</span>
                    <i class="fas fa-info-circle text-gray-300"></i>
                </div>
                
                <template x-if="legendType === 'day'">
                    <div class="space-y-1.5">
                        @foreach($options['days'] as $day)
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: {{ $dayColors[$day]['ganjil'] }}"></span>
                                <span class="text-gray-600">{{ $day }}</span>
                            </div>
                        @endforeach
                    </div>


                </template>

                <template x-if="legendType === 'week'">
                    <div class="space-y-1.5">
                        <template x-for="(color, name) in weekColors" :key="name">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shadow-sm" :style="'background-color:' + color"></span>
                                <span class="text-gray-600" x-text="name"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="legendType === 'salesman'">
                    <div class="space-y-1.5">
                        <template x-for="item in salesmanLegend" :key="item.name">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full shadow-sm" :style="'background-color:' + item.color"></span>
                                <span class="text-gray-600 truncate" x-text="item.name"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- ============================================================
             MODAL SECTION: Add Customer, Export, Edit, Filter
             ============================================================ --}}

        <div x-show="$wire.showAddModal" x-cloak x-transition.opacity class="fixed inset-0 z-[3500] bg-black/60 backdrop-blur-sm flex items-start justify-center pt-10">
            <div class="bg-white rounded-2xl shadow-2xl w-[650px] max-h-[90vh] overflow-hidden flex flex-col" @click.away="$wire.set('showAddModal', false)">
                <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex justify-between items-center">
                    <h3 class="font-bold text-blue-800 flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Tambah Customer ke Rute
                    </h3>
                    <button @click="$wire.set('showAddModal', false)" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times text-lg"></i></button>
                </div>

                <div class="p-6 space-y-6 overflow-y-auto custom-scroll">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 space-y-4">
                        <div class="flex items-center gap-2 mb-2 text-blue-700">
                            <i class="fas fa-map-marked-alt text-xs"></i>
                            <span class="text-[11px] font-bold uppercase tracking-wider">Informasi Wilayah & Salesman</span>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Region</label>
                                <select wire:model.live="newRouteRegion" class="w-full text-xs border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 shadow-sm">
                                    <option value="">Pilih Region</option>
                                    @foreach($regions as $reg) <option value="{{ $reg->region_code }}">{{ $reg->region_name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Area (Cabang)</label>
                                <select wire:model.live="newRouteArea" class="w-full text-xs border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 shadow-sm">
                                    <option value="">Pilih Area</option>
                                    @foreach($exportEntities as $ent) <option value="{{ $ent->area_code }}">{{ $ent->area_name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Distributor</label>
                                <select wire:model.live="newRouteDistributor" class="w-full text-xs border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 shadow-sm">
                                    <option value="">Pilih Distributor</option>
                                    @foreach($exportBranches as $br) <option value="{{ $br->distributor_code }}">{{ $br->distributor_name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-200">
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Pilih Salesman (SE)</label>
                            <select wire:model.live="newRouteSalesman" class="w-full text-xs border-blue-200 rounded-lg p-2 bg-white focus:ring-2 focus:ring-blue-500 font-semibold text-blue-700 shadow-sm">
                                <option value="">-- Pilih Salesman Terlebih Dahulu --</option>
                                @foreach($exportSalesmen as $s) <option value="{{ $s->slsno }}" wire:key="sls-opt-{{ $s->slsno }}">{{ $s->slsname }} ({{ $s->slsno }})</option> @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-5 {{ !$newRouteSalesman ? 'opacity-40 pointer-events-none' : '' }} transition-opacity duration-300">
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-2 text-gray-700"><i class="fas fa-store text-xs"></i><span class="text-[11px] font-bold uppercase tracking-wider">Cari & Pilih Outlet</span></div>
                            <div class="relative group">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input wire:model.live.debounce.300ms="searchCustomer" type="text" class="w-full text-xs pl-9 pr-4 py-2.5 border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 shadow-sm border" placeholder="Cari berdasarkan nama toko atau kode outlet...">
                            </div>
                            @if(count($this->masterCustomers) > 0)
                                <div class="absolute z-[5000] w-full bg-white border border-gray-200 shadow-2xl rounded-xl mt-1 max-h-60 overflow-y-auto custom-scroll">
                                    @foreach($this->masterCustomers as $mc)
                                        <div wire:click="addCustomerToSelection('{{ $mc->custno }}', '{{ $mc->custname }}')" class="p-3 text-xs hover:bg-blue-50 cursor-pointer border-b border-gray-50 flex justify-between items-start group transition">
                                            <div class="flex-1 pr-4">
                                                <div class="font-bold uppercase flex items-center gap-2"><span class="text-blue-600 font-mono tracking-tight">{{ $mc->custno }}</span><span class="text-gray-300 font-light">|</span><span class="text-gray-800 group-hover:text-blue-500 transition-colors">{{ $mc->custname }}</span></div>
                                                <div class="text-[10px] text-gray-500 flex items-center gap-1 mt-0.5"><i class="fas fa-map-marker-alt text-[9px] opacity-70"></i><span class="italic line-clamp-1">{{ $mc->custadd1 ?? 'Alamat tidak tersedia' }}</span></div>
                                            </div>
                                            <div class="self-center bg-blue-50 text-blue-600 p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition shadow-sm"><i class="fas fa-plus"></i></div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2 min-h-[30px]">
                            @foreach($selectedCustomers as $index => $sc)
                                <span class="bg-blue-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg flex items-center gap-2 shadow-sm animate-in fade-in zoom-in duration-200">
                                    {{ $sc['name'] }}
                                    <i wire:click="removeCustomerFromSelection({{ $index }})" class="fas fa-times-circle cursor-pointer hover:text-red-200 transition-colors"></i>
                                </span>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-700 uppercase flex items-center gap-1"><i class="fas fa-calendar-day text-blue-500"></i> Hari Kunjungan</label>
                                <select wire:model="newRouteDay" class="w-full text-xs border-gray-200 rounded-lg p-2 bg-white shadow-sm">
                                    <option value="">Pilih Hari</option>
                                    @foreach($options['days'] as $d) <option value="{{ $d }}">{{ $d }}</option> @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-gray-700 uppercase flex items-center gap-1"><i class="fas fa-calendar-week text-blue-500"></i> Pilih Minggu</label>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    @foreach($options['weeks'] as $w)
                                        <label class="flex items-center gap-2 text-[10px] border border-gray-200 p-2 rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-200 transition shadow-sm bg-white">
                                            <input type="checkbox" wire:model="newRouteWeeks" value="{{ $w }}" class="rounded text-blue-600 focus:ring-blue-500">
                                            <span class="font-medium text-gray-700">{{ $w }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button @click="$wire.set('showAddModal', false)" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
                    <button wire:click="storeCustomRoute" class="bg-blue-600 text-white px-8 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Rute Baru
                    </button>
                </div>
            </div>
        </div>

        <div x-show="$wire.showExportModal" x-cloak x-transition.opacity class="fixed inset-0 z-[3000] bg-black/60 backdrop-blur-sm flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl w-[500px] overflow-hidden" @click.away="$wire.set('showExportModal', false)">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-green-50">
                    <div class="flex items-center gap-2 text-green-700"><i class="fas fa-file-export text-xl"></i><h3 class="font-bold text-lg">Export Call Plan</h3></div>
                    <button @click="$wire.set('showExportModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><i class="fas fa-times text-lg"></i></button>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Region</label>
                        <select wire:model.live="selectedExpRegion" class="w-full text-sm border-gray-200 rounded-lg p-2.5 bg-white border shadow-sm outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            <option value="">Pilih Region</option>
                            @foreach($regions as $reg) <option value="{{ $reg->region_code }}">{{ $reg->region_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="transition-opacity duration-300 {{ !$selectedExpRegion ? 'opacity-40 pointer-events-none' : '' }}">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Pilih Area</label>
                        <select wire:model.live="selectedExpEntity" class="w-full text-sm border-gray-200 rounded-lg p-2.5 bg-white border shadow-sm outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            <option value="">Pilih Area</option>
                            @foreach($exportEntities as $ent) <option value="{{ $ent->area_code }}">{{ $ent->area_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="transition-opacity duration-300 {{ !$selectedExpEntity ? 'opacity-40 pointer-events-none' : '' }}">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Branch (Distributor)</label>
                        <select wire:model.live="selectedExpBranch" class="w-full text-sm border-gray-200 rounded-lg p-2.5 bg-white border shadow-sm outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            <option value="">Pilih Branch</option>
                            @foreach($exportBranches as $br) <option value="{{ $br->distributor_code }}">{{ $br->distributor_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="transition-opacity duration-300 {{ !$selectedExpBranch ? 'opacity-40 pointer-events-none' : '' }}">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pilih Salesman</label>
                            <button wire:click="selectAllExportSls" class="text-[10px] text-blue-600 font-bold hover:text-blue-800 transition-colors uppercase">
                                {{ (count($selectedExpSls) > 0 && count($selectedExpSls) === count($exportSalesmen)) ? 'Unselect All' : 'Select All' }}
                            </button>
                        </div>
                        <div class="max-h-[180px] overflow-y-auto border border-gray-200 rounded-xl p-3 bg-gray-50/50 custom-scroll grid grid-cols-2 gap-2.5">
                            @forelse($exportSalesmen as $sls)
                                <label wire:key="sls-exp-{{ $sls->slsno }}" class="flex items-center gap-3 p-2 bg-white rounded-lg border border-gray-100 cursor-pointer hover:border-green-300 hover:shadow-sm transition-all group">
                                    <input type="checkbox" wire:model="selectedExpSls" value="{{ $sls->slsno }}" class="w-4 h-4 rounded text-green-600 focus:ring-green-500 border-gray-300 transition-all">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[11px] text-gray-700 font-semibold truncate group-hover:text-green-700">{{ $sls->slsname }}</span>
                                        <span class="text-[9px] text-gray-400 font-mono">{{ $sls->slsno }}</span>
                                    </div>
                                </label>
                            @empty
                                <div class="col-span-2 flex flex-col items-center justify-center py-6 text-gray-400"><i class="fas fa-user-slash mb-2 text-xl opacity-20"></i><p class="text-[11px] italic font-medium">Pilih Branch terlebih dahulu</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col gap-2">
                    <button wire:click="exportExcel" wire:loading.attr="disabled" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-green-200 transition-all active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-2"><i class="fas fa-file-excel"></i> Download Excel Report</span>
                        <span wire:loading wire:target="exportExcel" class="flex items-center gap-2"><i class="fas fa-circle-notch fa-spin"></i> Generating Sheets...</span>
                    </button>
                    <p class="text-[9px] text-center text-gray-400">Pastikan semua filter telah terisi sebelum mendownload.</p>
                </div>
            </div>
        </div>

        <div x-show="$wire.showEditScheduleModal" x-cloak x-transition.opacity class="fixed inset-0 z-[2000] bg-black/50 backdrop-blur-sm flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl w-[400px] overflow-hidden" @click.away="$wire.set('showEditScheduleModal', false)">
                @if($editingStore)
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-blue-50">
                        <div><h3 class="font-bold text-gray-800">{{ $editingStore['name'] }}</h3><p class="text-[10px] text-gray-500 font-mono">{{ $editingStore['code'] }}</p></div>
                        <button @click="$wire.set('showEditScheduleModal', false)" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-wider">Pindahkan ke Salesman (SE)</label>
                            <select wire:model="selectedSalesmanInModal" class="w-full border-gray-200 rounded-lg p-2.5 text-sm bg-white border outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Salesman --</option>
                                @foreach($salesmen as $sls) <option value="{{ $sls->slsno }}">{{ $sls->slsname }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Pilih Minggu</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($options['weeks'] as $w)
                                    <label class="flex items-center gap-2 p-2 border rounded cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox" value="{{ $w }}" {{ in_array($w, $editingStore['weeks']) ? 'checked' : '' }} class="week-edit-check accent-blue-600">
                                        <span class="text-xs">{{ $w }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Pilih Hari</label>
                            <select id="edit-day-select" class="w-full border-gray-200 rounded-lg p-2 text-sm bg-white border">
                                @foreach($options['days'] as $d) <option value="{{ $d }}" {{ $d == $editingStore['day'] ? 'selected' : '' }}>{{ $d }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-2">
                        <button @click="$wire.set('showEditScheduleModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-gray-700">Batal</button>
                        <button @click="saveManualEdit()" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg transition">Simpan Jadwal</button>
                    </div>
                @endif
            </div>
        </div>

        <div x-show="$wire.showFilterModal" x-cloak x-transition.opacity class="fixed inset-0 z-[2000] bg-black/50 backdrop-blur-sm flex items-start justify-center pt-10">
            <div class="bg-white rounded-xl shadow-2xl w-[450px] max-w-full m-4 overflow-hidden transform transition-all" @click.away="$wire.set('showFilterModal', false)">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-2"><i class="fas fa-sliders-h text-blue-500"></i><h3 class="font-bold text-gray-800">Filter Wilayah</h3></div>
                    <button @click="$wire.set('showFilterModal', false)" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Region</label>
                        <select wire:model.live="selectedRegion" class="w-full text-sm border-gray-200 rounded-lg p-2.5 bg-white border outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <option value="">Pilih Region</option>
                            @foreach($regions as $reg) <option value="{{ $reg->region_code }}">{{ $reg->region_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="{{ !$selectedRegion ? 'opacity-50 pointer-events-none' : '' }}">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Area</label>
                        <select wire:model.live="selectedArea" class="w-full text-sm border-gray-200 rounded-lg p-2.5 bg-white border outline-none">
                            <option value="">Pilih Area</option>
                            @foreach($areas as $area) <option value="{{ $area->area_code }}">{{ $area->area_name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="{{ !$selectedArea ? 'opacity-50 pointer-events-none' : '' }}">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Distributor</label>
                        <select wire:model.live="selectedDistributor" class="w-full text-sm border-gray-200 rounded-lg p-2.5 bg-white border shadow-sm">
                            <option value="">Pilih Distributor</option>
                            @foreach($distributors as $dist) <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_name }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    <button wire:click="applyAdvancedFilter" class="px-6 py-2 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg transition">Simpan & Pilih Salesman</button>
                </div>
            </div>
        </div>
    </div> {{-- End Main Container --}}

    {{-- ============================================================
         JAVASCRIPT LOGIC: Leaflet & Alpine.js
         ============================================================ --}}
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mapComponent', (wire) => ({
                map: null, 
                layerGroup: null, 
                dayColors: @js($dayColors), 
                options: @js($options),
                legendType: 'day', 
                salesmanLegend: [],
                storesData: [], 
                _hasFitted: false,
                weekColors: {
                    'Week 1': '#2563EB', 
                    'Week 2': '#10B981', 
                    'Week 3': '#F59E0B', 
                    'Week 4': '#E11D48' 
                },

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
                            keepBuffer: 4
                        }).addTo(this.map);

                        L.control.zoom({ position: 'topright' }).addTo(this.map);
                        this.layerGroup = L.layerGroup().addTo(this.map);

                        // Auto-toggle Tooltip on Zoom
                        this.map.on('zoomend', () => {
                            const currentZoom = this.map.getZoom();
                            this.layerGroup.eachLayer((layer) => {
                                if (layer.getTooltip()) {
                                    (currentZoom >= 16) ? layer.openTooltip() : layer.closeTooltip();
                                }
                            });
                        });

                        if (wire.get('isFilterApplied')) {
                            const initialStores = wire.get('filteredStores');
                            if (initialStores) {
                                this.storesData = initialStores;
                                this.renderMarkers();
                            }
                        }
                    }, 100);

                    window.addEventListener('filters-updated', (e) => {
                        this._hasFitted = false;
                        this.map.invalidateSize();
                        this.storesData = e.detail.stores;
                        this.renderMarkers(e.detail.stores);
                    });
                },

                handleEdit(store) {
                    if (!store.lat || !store.lng || store.lat == 0 || store.lng == 0) {
                        wire.editSchedule(store); 
                    } else {
                        this.flyToStore(store);
                    }
                },

                getSalesmanColor(name) {
                    const palette = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#EC4899', '#6366F1', '#14B8A6', '#F97316'];
                    if (!name) return '#9CA3AF';
                    let hash = 0;
                    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
                    return palette[Math.abs(hash) % palette.length];
                },

                getStoreColor(store) {
                    // Jika mode HARI
                    if (this.legendType === 'day') {
                        const conf = this.dayColors[store.day] || { ganjil:'#333', genap:'#000' };
                        const isGanjil = store.weeks.includes('Week 1') || store.weeks.includes('Week 3');
                        return isGanjil ? conf.ganjil : conf.genap;
                    } 
                    
                    // Jika mode WEEK
                    if (this.legendType === 'week') {
                        for(let i=1; i<=4; i++) {
                            if (store.weeks.includes(`Week ${i}`)) return this.weekColors[`Week ${i}`];
                        }
                        return '#9CA3AF';
                    }

                    // Jika mode SALESMAN (SE)
                    // Sekarang langsung mengambil dari properti se_color yang dikirim PHP
                    if (this.legendType === 'salesman') {
                        return store.se_color || '#9CA3AF';
                    }

                    return '#333';
                },

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
                                radius: 7, color: '#ffffff', weight: 2, fillColor: color, fillOpacity: 0.9
                            })
                            .bindTooltip(store.name, { permanent: false, direction: 'top', offset: [0, -10], className: 'custom-tooltip' })
                            .bindPopup(() => this.generatePopupHtml(store, color));

                            this.layerGroup.addLayer(marker);
                            bounds.extend([store.lat, store.lng]);
                            hasValidPoints = true;
                        });

                        if (this.map.getZoom() >= 16) this.layerGroup.eachLayer(l => l.openTooltip());
                        if (!this._hasFitted && hasValidPoints) {
                            this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
                            this._hasFitted = true;
                        }
                    });
                },

                generatePopupHtml(store, color) {
                    const listSalesman = @this.get('salesmen'); 
                    const slsOpts = listSalesman.map(s => `<option value="${s.slsno}" ${s.slsno == store.slsno ? 'selected' : ''}>${s.slsname}</option>`).join('');
                    const dayOpts = this.options.days.map(d => `<option value="${d}" ${d === store.day ? 'selected' : ''}>${d}</option>`).join('');
                    const weekChecks = this.options.weeks.map(w => {
                        const checked = store.weeks.includes(w) ? 'checked' : '';
                        return `<label class="flex items-center gap-1.5 p-1 border rounded bg-gray-50 cursor-pointer">
                                    <input type="checkbox" class="week-check-${store.frute_id}" value="${w}" ${checked}>
                                    <span class="text-[10px]">${w.replace('Week ', 'W')}</span>
                                </label>`;
                    }).join('');

                    return `
                        <div class="min-w-[260px] text-gray-800">
                            <div class="flex items-center gap-3 border-b pb-2 mb-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold" style="background:${color}">${store.day.substr(0,1)}</div>
                                <div><h4 class="font-bold text-xs">${store.name}</h4><div class="text-[9px] text-gray-500 font-mono">${store.code}</div></div>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div><label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Ganti Salesman (SE)</label><select id="sls-${store.frute_id}" class="w-full border rounded p-1.5 bg-white border-blue-200">${slsOpts}</select></div>
                                <div><label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Jadwal Minggu</label><div class="grid grid-cols-2 gap-1.5">${weekChecks}</div></div>
                                <div><label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Hari Kunjungan</label><select id="day-${store.frute_id}" class="w-full border rounded p-1.5 bg-white">${dayOpts}</select></div>
                            </div>
                            <button onclick="window.saveFromPopup(${store.frute_id})" class="w-full mt-3 bg-blue-600 hover:bg-blue-700 text-white py-1.5 rounded font-bold transition">Simpan Perubahan</button>
                        </div>`;
                },

                flyToStore(store) {
                    if (this.map && store.lat && store.lng) {
                        this.map.flyTo([store.lat, store.lng], 13);
                        setTimeout(() => {
                            this.layerGroup.eachLayer(layer => {
                                const latLng = layer.getLatLng();
                                if(latLng.lat === store.lat && latLng.lng === store.lng) layer.openPopup();
                            });
                        }, 500);
                    }
                }
            }));

            // --- GLOBAL HELPERS FOR MARKER ACTIONS ---
            window.saveFromPopup = (fruteId) => {
                const weekChecks = document.querySelectorAll(`.week-check-${fruteId}:checked`);
                const newWeeks = Array.from(weekChecks).map(c => c.value);
                const newDay = document.getElementById(`day-${fruteId}`).value;
                const newSlsNo = document.getElementById(`sls-${fruteId}`).value;
                if (newWeeks.length === 0) { alert('Minimal pilih 1 minggu!'); return; }
                @this.saveStore(fruteId, newWeeks, newDay, newSlsNo);
            };

            window.saveManualEdit = () => {
                const store = @this.get('editingStore');
                if (!store) return;
                const weekChecks = document.querySelectorAll('.week-edit-check:checked');
                const newWeeks = Array.from(weekChecks).map(c => c.value);
                const newDay = document.getElementById('edit-day-select').value;
                const newSlsNo = @this.get('selectedSalesmanInModal');
                if (newWeeks.length === 0) { alert('Minimal pilih 1 minggu!'); return; }
                @this.saveStore(store.frute_id, newWeeks, newDay, newSlsNo);
                @this.set('showEditScheduleModal', false);
            };
        });

        // Helper untuk memanggil fungsi Alpine dari elemen eksternal (sidebar)
        function handleEdit(store) {
            const alpineEl = document.querySelector('[x-data^="mapComponent"]');
            if (alpineEl && alpineEl.__x) alpineEl.__x.$data.handleEdit(store);
        }
    </script>
    @endpush
</div>