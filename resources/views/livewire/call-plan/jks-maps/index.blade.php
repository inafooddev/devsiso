<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-2.5 md:gap-3 w-full h-full"
     x-data="jksMapsApp()"
     x-init="init()"
     @toast.window="addToast(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)"
     @refresh-map.window="handleMapRefresh($event)"
     @fly-to-store.window="flyToStore($event)"
>
    <x-slot name="title">Maps JKS</x-slot>

    {{-- Script Data Tag untuk Reaktivitas GeoJSON Livewire --}}
    <script id="jks-map-geojson-data" type="application/json">@json($mapGeoJson)</script>

    {{-- CDN MapLibre GL JS --}}
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>

    <style>
        .maplibregl-popup-content {
            background-color: #FFFFFF !important;
            color: #1F2937 !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            font-family: inherit !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
        }
        .maplibregl-popup-close-button {
            padding: 4px 8px !important;
            font-size: 14px !important;
            color: #6B7280 !important;
        }
        .maplibregl-popup-anchor-bottom .maplibregl-popup-tip { border-top-color: #FFFFFF !important; }
        .maplibregl-popup-anchor-top .maplibregl-popup-tip { border-bottom-color: #FFFFFF !important; }
        .maplibregl-popup-anchor-left .maplibregl-popup-tip { border-right-color: #FFFFFF !important; }
        .maplibregl-popup-anchor-right .maplibregl-popup-tip { border-left-color: #FFFFFF !important; }

        .depot-marker {
            width: 32px;
            height: 32px;
            background-color: #1E293B;
            border: 3px solid #FFFFFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .depot-marker:hover {
            transform: scale(1.15);
        }

        /* Pulsing Beacon Highlight untuk Toko Terpilih */
        @keyframes beacon-ping {
            0% {
                transform: scale(0.6);
                opacity: 0.9;
            }
            70%, 100% {
                transform: scale(2.6);
                opacity: 0;
            }
        }
        @keyframes beacon-pulse-core {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.18);
            }
        }
        .selected-store-beacon-wrap {
            position: relative;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 10;
        }
        .selected-store-beacon-ring {
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background-color: rgba(37, 99, 235, 0.45);
            animation: beacon-ping 1.4s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        .selected-store-beacon-ring.ring-2 {
            animation-delay: 0.45s;
        }
        .selected-store-beacon-core {
            position: relative;
            width: 16px;
            height: 16px;
            border-radius: 9999px;
            background-color: #2563EB;
            border: 2.5px solid #FFFFFF;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.8), 0 2px 4px rgba(0, 0, 0, 0.3);
            animation: beacon-pulse-core 1.4s ease-in-out infinite;
        }
    </style>

    {{-- TAB MENU CALL PLAN --}}
    <x-ui.tab-menu>
        @php
            $pendingApprovalsCount = \App\Livewire\CallPlan\JksApprovals\Index::getPendingCount();
        @endphp
        <a href="#" class="tab">Dashboard</a>
        <a href="{{ route('call-plan.jks-summary') }}" class="tab">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab">Detail</a>
        <a href="{{ route('call-plan.jks-maps') }}" class="tab tab-active bg-primary text-primary-content">Maps</a>
        <a href="{{ route('call-plan.jks-non-route') }}" class="tab">Outlet non JKS</a>
        <a href="{{ route('call-plan.jks-approvals') }}" class="tab">
            Persetujuan
            @if($pendingApprovalsCount > 0)
                <span class="badge badge-sm badge-error ml-2">{{ $pendingApprovalsCount }}</span>
            @endif
        </a>
    </x-ui.tab-menu>

    {{-- Mode Read-Only Alert jika bukan bulan berjalan --}}
    @if(!$this->canManipulate && $appliedDistributor)
        <div class="alert alert-warning py-2 px-3 rounded-xl shadow-xs border border-warning/30 bg-warning/10 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2 text-warning-content">
                <x-heroicon-s-lock-closed class="w-4 h-4 text-warning shrink-0" />
                <div>
                    <span class="font-bold">Mode Terkunci (Read-Only):</span>
                    <span>Anda sedang melihat JKS periode <strong>{{ \Carbon\Carbon::parse($appliedBulan . '-01')->translatedFormat('F Y') }}</strong>. 
                    Manipulasi data (Pindah Salesman, Ubah Jadwal, Tambah Toko) hanya diizinkan untuk periode bulan berjalan (<strong>{{ now()->translatedFormat('F Y') }}</strong>).</span>
                </div>
            </div>
            <span class="badge badge-warning badge-xs font-bold uppercase tracking-wider shrink-0">Read-Only</span>
        </div>
    @endif

    {{-- Main Container Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- FILTER TOOLBAR --}}
        <x-ui.filter-toolbar>
            {{-- Filter Distributor (Enhanced Standout Capsule) --}}
            <div class="inline-flex items-center rounded-xl border transition-all shadow-2xs {{ empty($appliedDistributor) ? 'bg-primary/5 border-primary/40 ring-2 ring-primary/20' : 'bg-base-100 border-base-300 hover:border-primary/50' }}">
                <div class="flex items-center gap-1.5 pl-2.5 pr-2 py-1 border-r border-base-200 shrink-0">
                    <span class="w-2 h-2 rounded-full shrink-0 {{ empty($appliedDistributor) ? 'bg-warning animate-pulse' : 'bg-success' }}"></span>
                    <x-heroicon-m-building-office-2 class="w-4 h-4 text-primary shrink-0" />
                    <span class="text-xs font-bold uppercase tracking-wider text-base-content/80 hidden sm:inline">Distributor</span>
                </div>
                <div class="px-1 py-0.5">
                    <x-ui.filter-searchable 
                        wire:key="maps-dist-capsule"
                        wire:model.live="appliedDistributor" 
                        :options="$distributorOptions" 
                        valueField="distributor_code" 
                        labelField="distributor_name" 
                        placeholder="-- Pilih Distributor --" 
                        searchPlaceholder="Cari nama atau kode distributor..." 
                        width="w-52 sm:w-72" 
                        popoverWidth="w-80 sm:w-96" 
                    />
                </div>
            </div>

            {{-- Month Filter Capsule --}}
            <div class="inline-flex items-center gap-1.5 bg-base-100 px-2.5 py-1 rounded-xl border border-base-300 shadow-2xs text-xs font-medium shrink-0" title="Pilih Periode Bulan">
                <x-heroicon-m-calendar-days class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                <input type="month" 
                       wire:model.live="appliedBulan" 
                       class="bg-transparent border-none focus:outline-none text-xs font-semibold cursor-pointer w-28 text-base-content" />
                @if($this->isCurrentRunningMonth)
                    <span class="badge badge-xs badge-success font-bold hidden xl:inline-flex" title="Periode Bulan Berjalan">Bulan Ini</span>
                @endif
            </div>

            {{-- Week Segmented Pills --}}
            <div class="inline-flex items-center bg-base-200/60 p-0.5 rounded-xl border border-base-300 shadow-2xs text-xs shrink-0">
                <button type="button" wire:click="$set('filterWeek', '')" class="px-2 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ empty($filterWeek) ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}">Semua W</button>
                <button type="button" wire:click="$set('filterWeek', 'w1')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterWeek === 'w1' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}">W1</button>
                <button type="button" wire:click="$set('filterWeek', 'w2')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterWeek === 'w2' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}">W2</button>
                <button type="button" wire:click="$set('filterWeek', 'w3')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterWeek === 'w3' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}">W3</button>
                <button type="button" wire:click="$set('filterWeek', 'w4')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterWeek === 'w4' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}">W4</button>
            </div>

            {{-- Day Segmented Pills --}}
            <div class="inline-flex items-center bg-base-200/60 p-0.5 rounded-xl border border-base-300 shadow-2xs text-xs shrink-0">
                <button type="button" wire:click="$set('filterDay', '')" class="px-2 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ empty($filterDay) ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}">Semua H</button>
                <button type="button" wire:click="$set('filterDay', 'h1')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterDay === 'h1' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Senin (H1)">Sen</button>
                <button type="button" wire:click="$set('filterDay', 'h2')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterDay === 'h2' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Selasa (H2)">Sel</button>
                <button type="button" wire:click="$set('filterDay', 'h3')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterDay === 'h3' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Rabu (H3)">Rab</button>
                <button type="button" wire:click="$set('filterDay', 'h4')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterDay === 'h4' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Kamis (H4)">Kam</button>
                <button type="button" wire:click="$set('filterDay', 'h5')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterDay === 'h5' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Jumat (H5)">Jum</button>
                <button type="button" wire:click="$set('filterDay', 'h6')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterDay === 'h6' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Sabtu (H6)">Sab</button>
                <button type="button" wire:click="$set('filterDay', 'h7')" class="px-1.5 py-0.5 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $filterDay === 'h7' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Minggu (H7)">Min</button>
            </div>

            <x-slot:actions>
                {{-- Mode Pewarnaan (Color Mode) Switcher --}}
                <div class="inline-flex items-center bg-base-200/70 p-0.5 rounded-xl border border-base-300 shadow-2xs text-xs shrink-0" title="Mode Pewarnaan Titik Peta & Legenda">
                    <span class="text-xs font-bold text-base-content/50 uppercase tracking-wider px-2 hidden md:inline-flex items-center gap-1">
                        <x-heroicon-m-swatch class="w-3.5 h-3.5 text-primary" /> Warna:
                    </span>
                    <button type="button" 
                            wire:click="setColorMode('salesman')" 
                            class="px-2 py-0.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer {{ $colorMode === 'salesman' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}"
                            title="Pewarnaan Berdasarkan Salesman (SE)">
                        By SE
                    </button>
                    <button type="button" 
                            wire:click="setColorMode('day')" 
                            class="px-2 py-0.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer {{ $colorMode === 'day' ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}"
                            title="Pewarnaan Berdasarkan Hari Kunjungan (Senin-Minggu)">
                        By Hari
                    </button>
                </div>

                {{-- Layer Toggles Dropdown --}}
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-xs sm:btn-sm btn-ghost border border-base-300 rounded-xl gap-1.5 text-xs cursor-pointer">
                        <x-heroicon-m-squares-2x2 class="w-3.5 h-3.5 text-primary" />
                        <span class="hidden sm:inline">Lapisan</span>
                        <x-heroicon-m-chevron-down class="w-3 h-3 opacity-60" />
                    </label>
                    <div tabindex="0" class="dropdown-content z-30 menu p-3 shadow-xl bg-base-100 rounded-xl border border-base-300 w-56 flex flex-col gap-2 mt-1">
                        <span class="text-xs font-bold text-base-content/70 uppercase tracking-wider px-1">Kontrol Lapisan</span>
                        <label class="label cursor-pointer py-1 px-1 hover:bg-base-200 rounded-lg">
                            <span class="label-text text-xs flex items-center gap-2">
                                <span class="w-2.5 h-0.5 bg-blue-500 rounded-full inline-block"></span>
                                Garis Rute (Polyline)
                            </span>
                            <input type="checkbox" wire:model.live="showRouteLines" class="checkbox checkbox-xs checkbox-primary" />
                        </label>
                        <label class="label cursor-pointer py-1 px-1 hover:bg-base-200 rounded-lg">
                            <span class="label-text text-xs flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full border border-amber-500 border-dashed bg-amber-100 inline-block"></span>
                                Outlet Non-JKS
                            </span>
                            <input type="checkbox" wire:model.live="showNonRoute" class="checkbox checkbox-xs checkbox-warning" />
                        </label>
                        <label class="label cursor-pointer py-1 px-1 hover:bg-base-200 rounded-lg">
                            <span class="label-text text-xs flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-800 inline-block"></span>
                                Gudang Distributor
                            </span>
                            <input type="checkbox" wire:model.live="showDepot" class="checkbox checkbox-xs checkbox-neutral" />
                        </label>
                    </div>
                </div>

                {{-- Reset Button --}}
                <button type="button" class="btn btn-xs sm:btn-sm btn-ghost border border-base-300 rounded-xl text-base-content/70 hover:text-base-content text-xs cursor-pointer" wire:click="resetFilters" title="Reset Filters & Cache">
                    <x-heroicon-m-arrow-path class="w-3.5 h-3.5" />
                    <span class="hidden sm:inline">Reset</span>
                </button>
            </x-slot:actions>
        </x-ui.filter-toolbar>

        {{-- SPLIT-SCREEN WORKSPACE: MAP (LEFT) & INSPECTOR DRAWER (RIGHT) --}}
        <div class="flex-1 min-h-0 flex flex-col lg:flex-row relative">
            
            {{-- SISI KIRI: MAP CANVAS CONTAINER --}}
            <div class="flex-1 min-h-80 lg:min-h-0 relative bg-base-200/50 overflow-hidden">
                
                {{-- MapLibre Container Canvas --}}
                <div id="jks-map" class="w-full h-full" wire:ignore></div>

                {{-- Floating Map Controls (Top Right) --}}
                <div class="absolute top-3 right-3 z-10 flex flex-col gap-1.5 shadow-md rounded-xl overflow-hidden border border-base-300 bg-base-100/90 backdrop-blur-xs p-1">
                    <button type="button" @click="zoomIn()" class="btn btn-xs btn-ghost btn-square text-base-content hover:bg-base-200" title="Perbesar Peta">
                        <x-heroicon-m-plus class="w-4 h-4" />
                    </button>
                    <button type="button" @click="zoomOut()" class="btn btn-xs btn-ghost btn-square text-base-content hover:bg-base-200" title="Perkecil Peta">
                        <x-heroicon-m-minus class="w-4 h-4" />
                    </button>
                    <div class="divider my-0 opacity-30"></div>
                    <button type="button" @click="fitAllBounds()" class="btn btn-xs btn-ghost btn-square text-base-content hover:bg-base-200" title="Fokus Seluruh Toko (Fit Bounds)">
                        <x-heroicon-m-arrows-pointing-out class="w-4 h-4" />
                    </button>
                    <button type="button" @click="centerToDepot()" class="btn btn-xs btn-ghost btn-square text-base-content hover:bg-base-200" title="Fokus Gudang Distributor">
                        <x-heroicon-m-building-office-2 class="w-4 h-4" />
                    </button>
                    <div class="divider my-0 opacity-30"></div>
                    <button type="button" @click="toggleBasemap()" class="btn btn-xs btn-ghost btn-square text-base-content hover:bg-base-200" title="Ganti Gaya Peta CARTO (Light / Voyager)">
                        <x-heroicon-m-map class="w-4 h-4 text-primary" />
                    </button>
                </div>

                {{-- Floating Legend Capsule (Bottom Left) --}}
                <div class="absolute bottom-3 left-3 z-10 max-w-sm sm:max-w-md bg-base-100/90 backdrop-blur-xs rounded-xl border border-base-300 shadow-md p-2 text-xs flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1 font-bold text-base-content/70 text-xs uppercase tracking-wider">
                        <x-heroicon-m-swatch class="w-3.5 h-3.5 text-primary" />
                        <span>{{ $legendData['title'] }}:</span>
                    </div>

                    @if($depot && $showDepot)
                        <div class="flex items-center gap-1 text-xs text-base-content/80">
                            <span class="w-3 h-3 rounded-full bg-slate-800 border border-white shrink-0 inline-block shadow-2xs"></span>
                            <span class="truncate">Gudang</span>
                        </div>
                    @endif
                    @if($showNonRoute)
                        <div class="flex items-center gap-1 text-xs text-base-content/80">
                            <span class="w-3 h-3 rounded-full bg-amber-400 border border-amber-600 border-dashed shrink-0 inline-block"></span>
                            <span class="truncate">Non-JKS</span>
                        </div>
                    @endif

                    @foreach(array_slice($legendData['items'] ?? [], 0, 5) as $item)
                        @if(($item['count'] ?? 0) > 0 || ($item['active'] ?? true))
                            <div class="flex items-center gap-1 text-xs text-base-content/80">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 inline-block shadow-2xs" style="background-color: {{ $item['color'] }}"></span>
                                <span class="truncate max-w-20" title="{{ $item['label'] }}">{{ $item['short'] ?? Str::limit($item['label'], 10) }}</span>
                                @if($colorMode !== 'salesman')
                                    <span class="text-xs text-base-content/50 font-mono">({{ $item['count'] }})</span>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    @if(count($legendData['items'] ?? []) > 5)
                        <span class="text-xs text-base-content/50 font-medium">+{{ count($legendData['items']) - 5 }}</span>
                    @endif
                </div>

                {{-- Empty State Guidance Overlay saat Distributor Belum Dipilih --}}
                @if(empty($appliedDistributor))
                    <div class="absolute inset-0 z-10 flex items-center justify-center p-4 pointer-events-none">
                        <div class="bg-base-100/95 backdrop-blur-md border border-base-300 shadow-2xl rounded-2xl p-6 max-w-sm text-center flex flex-col items-center gap-3 pointer-events-auto">
                            <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
                                <x-heroicon-o-building-office-2 class="w-8 h-8" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <h3 class="text-sm font-bold text-base-content">Pilih Distributor untuk Memuat Peta</h3>
                                <p class="text-xs text-base-content/65 leading-relaxed">
                                    Silakan tentukan distributor pada kotak filter di toolbar atas untuk memuat titik toko, rute kunjungan, dan wilayah salesman.
                                </p>
                            </div>
                            <div class="badge badge-primary badge-outline text-xs font-semibold gap-1.5 py-3 px-3.5 rounded-xl mt-1">
                                <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5" />
                                <span>Gunakan kotak "Distributor" di atas</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Loading Overlay --}}
                <div wire:loading wire:target="appliedDistributor, appliedBulan, filterWeek, filterDay, activeSalesmanCodes, showRouteLines, showNonRoute, showDepot" 
                     class="absolute inset-0 bg-base-100/60 backdrop-blur-xs z-20 flex flex-col items-center justify-center gap-2">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/70">Memperbarui peta spasial...</span>
                </div>
            </div>

            {{-- SISI KANAN: TERRITORY & ROUTE INSPECTOR DRAWER --}}
            <div class="w-full lg:w-96 shrink-0 flex flex-col bg-base-100 border-t lg:border-t-0 lg:border-l border-base-300 overflow-hidden max-h-96 lg:max-h-none">
                
                {{-- Drawer Tab Switcher --}}
                <div class="grid grid-cols-3 p-1.5 bg-base-200/50 border-b border-base-300 shrink-0 text-xs font-semibold">
                    <button type="button" 
                            wire:click="$set('drawerTab', 'salesman')" 
                            class="py-1.5 px-2 rounded-lg transition-all text-center cursor-pointer flex items-center justify-center gap-1 {{ $drawerTab === 'salesman' ? 'bg-base-100 text-primary shadow-xs font-bold' : 'text-base-content/60 hover:text-base-content' }}">
                        <x-heroicon-m-swatch class="w-3.5 h-3.5 text-primary shrink-0" />
                        <span class="truncate">
                            @if($colorMode === 'day')
                                By Hari
                            @else
                                Salesman ({{ count($territories) }})
                            @endif
                        </span>
                    </button>
                    @php
                        $totalAnomaliCount = count($anomalies['at_distributor'] ?? []) 
                            + count($anomalies['missing_coordinate'] ?? []) 
                            + count($anomalies['duplicate'] ?? []) 
                            + count($anomalies['dobel_cover'] ?? []) 
                            + count($anomalies['non_route'] ?? []);
                    @endphp
                    <button type="button" 
                            wire:click="$set('drawerTab', 'anomali')" 
                            class="py-1.5 px-2 rounded-lg transition-all text-center cursor-pointer flex items-center justify-center gap-1 {{ $drawerTab === 'anomali' ? 'bg-base-100 text-warning shadow-xs font-bold' : 'text-base-content/60 hover:text-base-content' }}">
                        Anomali
                        @if($totalAnomaliCount > 0)
                            <span class="badge badge-xs badge-error font-bold">{{ $totalAnomaliCount }}</span>
                        @endif
                    </button>
                    <button type="button" 
                            wire:click="$set('drawerTab', 'store')" 
                            class="py-1.5 px-2 rounded-lg transition-all text-center cursor-pointer {{ $drawerTab === 'store' ? 'bg-base-100 text-primary shadow-xs font-bold' : 'text-base-content/60 hover:text-base-content' }}">
                        Detail Toko
                    </button>
                </div>

                {{-- TAB CONTENT 1: SALESMAN & LEGENDA WARNA --}}
                @if($drawerTab === 'salesman')
                    <div class="flex-1 min-h-0 flex flex-col p-3 overflow-hidden">
                        {{-- Controls Header --}}
                        <div class="flex items-center justify-between pb-2 border-b border-base-200 shrink-0 text-xs gap-2">
                            <div class="flex items-center gap-1 min-w-0">
                                <span class="font-bold text-base-content/70 uppercase tracking-wider text-xs truncate">
                                    {{ $legendData['title'] }}
                                </span>
                            </div>

                            @if($colorMode === 'salesman')
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" wire:click="toggleAllSalesmen(true)" class="btn btn-2xs btn-ghost text-primary hover:underline cursor-pointer">Semua</button>
                                    <span class="text-base-content/30">|</span>
                                    <button type="button" wire:click="toggleAllSalesmen(false)" class="btn btn-2xs btn-ghost text-base-content/60 hover:underline cursor-pointer">Kosongkan</button>
                                </div>
                            @else
                                <button type="button" wire:click="$set('filterDay', '')" class="btn btn-2xs btn-ghost text-primary hover:underline cursor-pointer shrink-0">
                                    Reset Hari
                                </button>
                            @endif
                        </div>

                        {{-- Mode Selector Sub-Bar --}}
                        <div class="py-1.5 border-b border-base-200 flex items-center justify-between text-xs">
                            <span class="text-xs text-base-content/50 font-medium">Mode Warna:</span>
                            <div class="join">
                                <button type="button" 
                                        wire:click="setColorMode('salesman')" 
                                        class="join-item btn btn-2xs {{ $colorMode === 'salesman' ? 'btn-primary font-bold' : 'btn-ghost' }}">
                                    By SE
                                </button>
                                <button type="button" 
                                        wire:click="setColorMode('day')" 
                                        class="join-item btn btn-2xs {{ $colorMode === 'day' ? 'btn-primary font-bold' : 'btn-ghost' }}">
                                    By Hari
                                </button>
                            </div>
                        </div>

                        {{-- Item List (Scrollable) --}}
                        <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-base-200 pr-1 py-1">
                            @if(empty($appliedDistributor))
                                <div class="py-12 flex flex-col items-center justify-center text-center p-4 text-base-content/50 gap-2">
                                    <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center text-base-content/40">
                                        <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary" />
                                    </div>
                                    <span class="text-xs font-bold text-base-content/70">Distributor Belum Dipilih</span>
                                    <span class="text-xs text-base-content/60 leading-relaxed max-w-xs">
                                        Pilih salah satu distributor pada kotak filter di toolbar atas untuk melihat daftar teritori dan rute salesman.
                                    </span>
                                </div>
                            @elseif($colorMode === 'salesman')
                                @forelse($territories as $sCode => $sData)
                                    <div class="py-2 flex items-center justify-between gap-2 hover:bg-base-200/40 px-1.5 rounded-lg transition-colors">
                                        <label class="flex items-center gap-2.5 cursor-pointer flex-1 min-w-0">
                                            <input type="checkbox" 
                                                   wire:click="toggleSalesman('{{ $sCode }}')" 
                                                   {{ in_array($sCode, $activeSalesmanCodes) ? 'checked' : '' }} 
                                                   class="checkbox checkbox-xs checkbox-primary rounded shrink-0" />
                                            <span class="w-3 h-3 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $sData['color'] }}"></span>
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-xs font-bold text-base-content truncate">{{ $sData['name'] }}</span>
                                                <span class="text-xs text-base-content/60 font-mono">{{ $sCode }}</span>
                                            </div>
                                        </label>
                                        <span class="badge badge-sm badge-ghost text-xs font-semibold shrink-0">
                                            {{ $sData['total_stores'] ?? $sData['total_toko'] ?? 0 }} toko
                                        </span>
                                    </div>
                                @empty
                                    <div class="py-8 text-center text-xs text-base-content/50">
                                        Tidak ada data salesman untuk distributor ini.
                                    </div>
                                @endforelse
                            @else
                                {{-- Day Legend Items List --}}
                                @php
                                    $totalLegendStores = array_sum(array_column($legendData['items'] ?? [], 'count'));
                                @endphp
                                @forelse($legendData['items'] ?? [] as $item)
                                    @php
                                        $pct = $totalLegendStores > 0 ? round(($item['count'] / $totalLegendStores) * 100) : 0;
                                        $isSelected = ($filterDay === $item['key']) || (empty($filterDay) && $item['key'] === 'all');
                                    @endphp
                                    <div class="py-2 px-1.5 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors flex flex-col gap-1 {{ $isSelected ? 'bg-primary/5 border border-primary/20' : '' }}"
                                         wire:click="$set('filterDay', '{{ $filterDay === $item['key'] ? '' : ($item['key'] === 'multi' ? '' : $item['key']) }}')"
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-3 h-3 rounded-full shrink-0 shadow-2xs" style="background-color: {{ $item['color'] }}"></span>
                                                <span class="text-xs font-bold text-base-content truncate">{{ $item['label'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span class="badge badge-sm {{ $isSelected ? 'badge-primary text-white font-bold' : 'badge-ghost text-xs font-semibold' }}">
                                                    {{ $item['count'] }} toko
                                                </span>
                                                <span class="text-xs text-base-content/50 font-mono w-9 text-right">{{ $pct }}%</span>
                                            </div>
                                        </div>
                                        {{-- Visual Proportion Bar --}}
                                        <div class="w-full bg-base-200 rounded-full h-1 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $pct }}%; background-color: {{ $item['color'] }}"></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-8 text-center text-xs text-base-content/50">
                                        Tidak ada data jadwal untuk distributor ini.
                                    </div>
                                @endforelse
                            @endif
                        </div>

                        {{-- Territory Quick KPI Summary Footer --}}
                        <div class="mt-2 pt-2.5 border-t border-base-200 shrink-0 grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-base-200/40 p-2 rounded-xl">
                                <div class="text-xs text-base-content/50 uppercase font-bold tracking-wider">Total Toko JKS</div>
                                <div class="text-sm font-bold text-primary mt-0.5">{{ $anomalies['total_stores'] ?? 0 }}</div>
                            </div>
                            <div class="bg-base-200/40 p-2 rounded-xl">
                                <div class="text-xs text-base-content/50 uppercase font-bold tracking-wider">Titik Koordinat</div>
                                <div class="text-sm font-bold text-success mt-0.5">
                                    {{ $anomalies['with_coords'] ?? 0 }} 
                                    <span class="text-xs font-normal text-base-content/60">
                                        ({{ ($anomalies['total_stores'] ?? 0) > 0 ? round((($anomalies['with_coords'] ?? 0) / $anomalies['total_stores']) * 100) : 0 }}%)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- TAB CONTENT 2: ANOMALI & AUDIT --}}
                @if($drawerTab === 'anomali')
                    <div class="flex-1 min-h-0 flex flex-col p-3 overflow-y-auto gap-2.5"
                         x-data="{
                             activeCategory: null,
                             nonRouteFilter: 'all',
                             toggle(key) {
                                 this.activeCategory = (this.activeCategory === key) ? null : key;
                             }
                         }">

                        {{-- Ringkasan Mini KPI Anomali (5 Kategori) --}}
                        <div class="grid grid-cols-5 gap-1 shrink-0">
                            <button type="button" 
                                    class="p-1.5 rounded-xl border bg-base-200/50 border-base-300 flex flex-col items-center justify-center text-center cursor-pointer hover:border-error/50 transition-all"
                                    :class="activeCategory === 'dist' ? 'ring-2 ring-error bg-error/10' : ''"
                                    title="Klik untuk membuka/menutup: Titik di Distributor (<= 100m)"
                                    @click="toggle('dist')">
                                <span class="text-xs text-base-content/60 font-medium truncate w-full">Di Depo</span>
                                <span class="text-xs font-bold {{ count($anomalies['at_distributor'] ?? []) > 0 ? 'text-error' : 'text-base-content/70' }}">
                                    {{ count($anomalies['at_distributor'] ?? []) }}
                                </span>
                            </button>
                            <button type="button" 
                                    class="p-1.5 rounded-xl border bg-base-200/50 border-base-300 flex flex-col items-center justify-center text-center cursor-pointer hover:border-warning/50 transition-all"
                                    :class="activeCategory === 'missing' ? 'ring-2 ring-warning bg-warning/10' : ''"
                                    title="Klik untuk membuka/menutup: Belum Ada Titik (NULL / 0)"
                                    @click="toggle('missing')">
                                <span class="text-xs text-base-content/60 font-medium truncate w-full">No GPS</span>
                                <span class="text-xs font-bold {{ count($anomalies['missing_coordinate'] ?? []) > 0 ? 'text-warning' : 'text-base-content/70' }}">
                                    {{ count($anomalies['missing_coordinate'] ?? []) }}
                                </span>
                            </button>
                            <button type="button" 
                                    class="p-1.5 rounded-xl border bg-base-200/50 border-base-300 flex flex-col items-center justify-center text-center cursor-pointer hover:border-secondary/50 transition-all"
                                    :class="activeCategory === 'dup' ? 'ring-2 ring-secondary bg-secondary/10' : ''"
                                    title="Klik untuk membuka/menutup: Duplikasi Titik (Menumpuk)"
                                    @click="toggle('dup')">
                                <span class="text-xs text-base-content/60 font-medium truncate w-full">Duplikat</span>
                                <span class="text-xs font-bold {{ count($anomalies['duplicate'] ?? []) > 0 ? 'text-secondary' : 'text-base-content/70' }}">
                                    {{ count($anomalies['duplicate'] ?? []) }}
                                </span>
                            </button>
                            <button type="button" 
                                    class="p-1.5 rounded-xl border bg-base-200/50 border-base-300 flex flex-col items-center justify-center text-center cursor-pointer hover:border-pink-500/50 transition-all"
                                    :class="activeCategory === 'dobel' ? 'ring-2 ring-pink-500 bg-pink-500/10' : ''"
                                    title="Klik untuk membuka/menutup: Dobel Cover (>= 2 Salesman)"
                                    @click="toggle('dobel')">
                                <span class="text-xs text-base-content/60 font-medium truncate w-full">Dobel</span>
                                <span class="text-xs font-bold {{ count($anomalies['dobel_cover'] ?? []) > 0 ? 'text-pink-600' : 'text-base-content/70' }}">
                                    {{ count($anomalies['dobel_cover'] ?? []) }}
                                </span>
                            </button>
                            <button type="button" 
                                    class="p-1.5 rounded-xl border bg-base-200/50 border-base-300 flex flex-col items-center justify-center text-center cursor-pointer hover:border-amber-500/50 transition-all"
                                    :class="activeCategory === 'nonroute' ? 'ring-2 ring-amber-500 bg-amber-500/10' : ''"
                                    title="Klik untuk membuka/menutup: Non Rute (Jadwal tidak lengkap / Minggu)"
                                    @click="toggle('nonroute')">
                                <span class="text-xs text-base-content/60 font-medium truncate w-full">Non Rute</span>
                                <span class="text-xs font-bold {{ count($anomalies['non_route'] ?? []) > 0 ? 'text-amber-500' : 'text-base-content/70' }}">
                                    {{ count($anomalies['non_route'] ?? []) }}
                                </span>
                            </button>
                        </div>

                        {{-- KATEGORI 1: TITIK DI DISTRIBUTOR (<= 100 Meter) --}}
                        <div class="border border-base-300 rounded-xl bg-base-100 overflow-hidden shadow-2xs">
                            <div class="p-2.5 flex items-center justify-between cursor-pointer select-none transition-colors"
                                 :class="activeCategory === 'dist' ? 'bg-base-200/80' : 'bg-base-200/40 hover:bg-base-200/70'"
                                 @click="toggle('dist')">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-error/10 text-error flex items-center justify-center shrink-0">
                                        <x-heroicon-m-building-office class="w-3.5 h-3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-base-content flex items-center gap-1.5">
                                            <span>1. Titik di Distributor</span>
                                            <span class="text-xs font-normal text-base-content/50">(&le; 100 m)</span>
                                        </div>
                                        <div class="text-xs text-base-content/60 truncate">Toko dekat kantor/depot distributor</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge badge-sm {{ count($anomalies['at_distributor'] ?? []) > 0 ? 'badge-error font-bold text-white' : 'badge-ghost text-base-content/50' }}">
                                        {{ count($anomalies['at_distributor'] ?? []) }}
                                    </span>
                                    <x-heroicon-m-chevron-down class="w-4 h-4 text-base-content/50 transition-transform duration-200" 
                                                               x-bind:class="activeCategory === 'dist' ? 'rotate-180' : ''" />
                                </div>
                            </div>

                            <div x-show="activeCategory === 'dist'" x-cloak class="border-t border-base-200 p-2 divide-y divide-base-200/70 max-h-96 overflow-y-auto">
                                @if(!$anomalies['has_depot_coords'])
                                    <div class="p-3 text-center text-xs text-warning bg-warning/10 rounded-lg flex flex-col items-center gap-1">
                                        <x-heroicon-m-exclamation-triangle class="w-4 h-4" />
                                        <span>Koordinat gudang belum diset di Master Distributor, jarak tidak dapat dihitung.</span>
                                    </div>
                                @elseif(empty($anomalies['at_distributor']))
                                    <div class="py-4 text-center text-xs text-base-content/50 flex flex-col items-center justify-center gap-1">
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-success" />
                                        <span>Tidak ada toko yang titiknya menempel di distributor (&le; 100m).</span>
                                    </div>
                                @else
                                    @foreach($anomalies['at_distributor'] as $mStore)
                                        <div class="py-2 px-1.5 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors"
                                             wire:click="selectStore('{{ $mStore['customer_code'] }}', 'jks')"
                                             title="Klik untuk melihat detail & fokus peta">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-xs font-bold text-base-content truncate">{{ $mStore['customer_name'] }}</span>
                                                <span class="badge badge-xs badge-error shrink-0 font-bold text-white">{{ $mStore['distance_meters'] }} m</span>
                                            </div>
                                            <div class="text-xs text-base-content/60 truncate mt-0.5">{{ $mStore['alamat'] }}</div>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-base-content/50 font-mono">
                                                <span>{{ $mStore['customer_code'] }}</span>
                                                <span>•</span>
                                                <span class="font-sans font-medium text-primary">{{ $mStore['salesman_name'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- KATEGORI 2: BELUM ADA TITIK --}}
                        <div class="border border-base-300 rounded-xl bg-base-100 overflow-hidden shadow-2xs">
                            <div class="p-2.5 flex items-center justify-between cursor-pointer select-none transition-colors"
                                 :class="activeCategory === 'missing' ? 'bg-base-200/80' : 'bg-base-200/40 hover:bg-base-200/70'"
                                 @click="toggle('missing')">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-warning/10 text-warning flex items-center justify-center shrink-0">
                                        <x-heroicon-m-map-pin class="w-3.5 h-3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-base-content">2. Belum Ada Titik</div>
                                        <div class="text-xs text-base-content/60 truncate">Toko tanpa koordinat GPS (NULL / 0)</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge badge-sm {{ count($anomalies['missing_coordinate'] ?? []) > 0 ? 'badge-warning font-bold' : 'badge-ghost text-base-content/50' }}">
                                        {{ count($anomalies['missing_coordinate'] ?? []) }}
                                    </span>
                                    <x-heroicon-m-chevron-down class="w-4 h-4 text-base-content/50 transition-transform duration-200" 
                                                               x-bind:class="activeCategory === 'missing' ? 'rotate-180' : ''" />
                                </div>
                            </div>

                            <div x-show="activeCategory === 'missing'" x-cloak class="border-t border-base-200 p-2 divide-y divide-base-200/70 max-h-96 overflow-y-auto">
                                @if(empty($anomalies['missing_coordinate']))
                                    <div class="py-4 text-center text-xs text-base-content/50 flex flex-col items-center justify-center gap-1">
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-success" />
                                        <span>Seluruh toko telah memiliki titik koordinat GPS.</span>
                                    </div>
                                @else
                                    @foreach($anomalies['missing_coordinate'] as $mStore)
                                        <div class="py-2 px-1.5 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors"
                                             wire:click="selectStore('{{ $mStore['customer_code'] }}', 'jks')"
                                             title="Klik untuk melihat detail toko">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-xs font-bold text-base-content truncate">{{ $mStore['customer_name'] }}</span>
                                                <span class="badge badge-xs badge-warning shrink-0">No GPS</span>
                                            </div>
                                            <div class="text-xs text-base-content/60 truncate mt-0.5">{{ $mStore['alamat'] }}</div>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-base-content/50 font-mono">
                                                <span>{{ $mStore['customer_code'] }}</span>
                                                <span>•</span>
                                                <span class="font-sans font-medium text-primary">{{ $mStore['salesman_name'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- KATEGORI 3: DUPLIKASI TITIK --}}
                        <div class="border border-base-300 rounded-xl bg-base-100 overflow-hidden shadow-2xs">
                            <div class="p-2.5 flex items-center justify-between cursor-pointer select-none transition-colors"
                                 :class="activeCategory === 'dup' ? 'bg-base-200/80' : 'bg-base-200/40 hover:bg-base-200/70'"
                                 @click="toggle('dup')">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center shrink-0">
                                        <x-heroicon-m-document-duplicate class="w-3.5 h-3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-base-content">3. Duplikasi Titik</div>
                                        <div class="text-xs text-base-content/60 truncate">Titik koordinat persis sama (menumpuk)</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge badge-sm {{ count($anomalies['duplicate'] ?? []) > 0 ? 'badge-secondary font-bold text-white' : 'badge-ghost text-base-content/50' }}">
                                        {{ count($anomalies['duplicate'] ?? []) }}
                                    </span>
                                    <x-heroicon-m-chevron-down class="w-4 h-4 text-base-content/50 transition-transform duration-200" 
                                                               x-bind:class="activeCategory === 'dup' ? 'rotate-180' : ''" />
                                </div>
                            </div>

                            <div x-show="activeCategory === 'dup'" x-cloak class="border-t border-base-200 p-2 divide-y divide-base-200/70 max-h-96 overflow-y-auto">
                                @if(empty($anomalies['duplicate']))
                                    <div class="py-4 text-center text-xs text-base-content/50 flex flex-col items-center justify-center gap-1">
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-success" />
                                        <span>Tidak ditemukan duplikasi titik koordinat di distributor ini.</span>
                                    </div>
                                @else
                                    @foreach($anomalies['duplicate'] as $mStore)
                                        <div class="py-2 px-1.5 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors"
                                             wire:click="selectStore('{{ $mStore['customer_code'] }}', 'jks')"
                                             title="Klik untuk melihat detail & fokus peta">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-xs font-bold text-base-content truncate">{{ $mStore['customer_name'] }}</span>
                                                <span class="badge badge-xs badge-secondary shrink-0 font-bold text-white">{{ $mStore['duplicate_count'] }} Toko</span>
                                            </div>
                                            <div class="text-xs text-base-content/60 truncate mt-0.5">{{ $mStore['alamat'] }}</div>
                                            <div class="flex items-center justify-between gap-2 mt-1 text-xs text-base-content/50">
                                                <div class="flex items-center gap-1 font-mono">
                                                    <span>{{ $mStore['customer_code'] }}</span>
                                                    <span>•</span>
                                                    <span class="font-sans font-medium text-primary">{{ $mStore['salesman_name'] }}</span>
                                                </div>
                                                <span class="font-mono text-xs opacity-70">{{ round($mStore['latitude'], 4) }}, {{ round($mStore['longitude'], 4) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- KATEGORI 4: DOBEL COVER --}}
                        <div class="border border-base-300 rounded-xl bg-base-100 overflow-hidden shadow-2xs">
                            <div class="p-2.5 flex items-center justify-between cursor-pointer select-none transition-colors"
                                 :class="activeCategory === 'dobel' ? 'bg-base-200/80' : 'bg-base-200/40 hover:bg-base-200/70'"
                                 @click="toggle('dobel')">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-pink-500/10 text-pink-600 flex items-center justify-center shrink-0">
                                        <x-heroicon-m-user-group class="w-3.5 h-3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-base-content">4. Dobel Cover</div>
                                        <div class="text-xs text-base-content/60 truncate">1 Toko dijadwalkan &ge; 2 Salesman berbeda</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge badge-sm {{ count($anomalies['dobel_cover'] ?? []) > 0 ? 'badge-error font-bold text-white' : 'badge-ghost text-base-content/50' }}">
                                        {{ count($anomalies['dobel_cover'] ?? []) }}
                                    </span>
                                    <x-heroicon-m-chevron-down class="w-4 h-4 text-base-content/50 transition-transform duration-200" 
                                                               x-bind:class="activeCategory === 'dobel' ? 'rotate-180' : ''" />
                                </div>
                            </div>

                            <div x-show="activeCategory === 'dobel'" x-cloak class="border-t border-base-200 p-2 divide-y divide-base-200/70 max-h-96 overflow-y-auto">
                                @if(empty($anomalies['dobel_cover']))
                                    <div class="py-4 text-center text-xs text-base-content/50 flex flex-col items-center justify-center gap-1">
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-success" />
                                        <span>Tidak ada toko yang mengalami dobel cover salesman.</span>
                                    </div>
                                @else
                                    @foreach($anomalies['dobel_cover'] as $dStore)
                                        <div class="py-2.5 px-1.5 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors"
                                             wire:click="selectStore('{{ $dStore['customer_code'] }}', 'jks')"
                                             title="Klik untuk melihat detail & fokus peta">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-xs font-bold text-base-content truncate">{{ $dStore['customer_name'] }}</span>
                                                <span class="badge badge-xs badge-error shrink-0 font-bold text-white">{{ $dStore['count'] }} Salesman</span>
                                            </div>
                                            <div class="text-xs text-base-content/60 truncate mt-0.5">{{ $dStore['alamat'] }}</div>
                                            
                                            {{-- Rincian Salesman yang Meng-cover --}}
                                            <div class="mt-1.5 flex flex-col gap-1 bg-base-200/50 p-1.5 rounded-lg border border-base-200">
                                                @foreach($dStore['salesmen'] as $sInfo)
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="font-medium text-primary truncate">{{ $sInfo['name'] }}</span>
                                                        <span class="font-mono text-xs text-base-content/70 shrink-0">{{ $sInfo['schedule'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- KATEGORI 5: NON RUTE --}}
                        <div class="border border-base-300 rounded-xl bg-base-100 overflow-hidden shadow-2xs">
                            <div class="p-2.5 flex items-center justify-between cursor-pointer select-none transition-colors"
                                 :class="activeCategory === 'nonroute' ? 'bg-base-200/80' : 'bg-base-200/40 hover:bg-base-200/70'"
                                 @click="toggle('nonroute')">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                        <x-heroicon-m-calendar-days class="w-3.5 h-3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-base-content">5. Non Rute</div>
                                        <div class="text-xs text-base-content/60 truncate">Jadwal tidak lengkap / jadwal hari Minggu</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge badge-sm {{ count($anomalies['non_route'] ?? []) > 0 ? 'badge-warning font-bold' : 'badge-ghost text-base-content/50' }}">
                                        {{ count($anomalies['non_route'] ?? []) }}
                                    </span>
                                    <x-heroicon-m-chevron-down class="w-4 h-4 text-base-content/50 transition-transform duration-200" 
                                                               x-bind:class="activeCategory === 'nonroute' ? 'rotate-180' : ''" />
                                </div>
                            </div>

                            <div x-show="activeCategory === 'nonroute'" x-cloak class="border-t border-base-200 p-2 flex flex-col gap-2 max-h-96 overflow-y-auto">
                                {{-- Sub-Filter Pills untuk 4 Kasus Non Rute --}}
                                <div class="flex items-center gap-1 overflow-x-auto pb-1 shrink-0 text-xs">
                                    <button type="button" 
                                            class="px-2 py-0.5 rounded-lg text-xs font-medium cursor-pointer transition-colors"
                                            :class="nonRouteFilter === 'all' ? 'bg-primary text-white font-bold shadow-2xs' : 'bg-base-200 text-base-content/70 hover:text-base-content'"
                                            @click="nonRouteFilter = 'all'">
                                        Semua ({{ count($anomalies['non_route'] ?? []) }})
                                    </button>
                                    <button type="button" 
                                            class="px-2 py-0.5 rounded-lg text-xs font-medium cursor-pointer transition-colors"
                                            :class="nonRouteFilter === 'no_schedule' ? 'bg-primary text-white font-bold shadow-2xs' : 'bg-base-200 text-base-content/70 hover:text-base-content'"
                                            @click="nonRouteFilter = 'no_schedule'">
                                        Tanpa Jadwal ({{ $anomalies['non_route_counts']['no_schedule'] ?? 0 }})
                                    </button>
                                    <button type="button" 
                                            class="px-2 py-0.5 rounded-lg text-xs font-medium cursor-pointer transition-colors"
                                            :class="nonRouteFilter === 'no_week' ? 'bg-primary text-white font-bold shadow-2xs' : 'bg-base-200 text-base-content/70 hover:text-base-content'"
                                            @click="nonRouteFilter = 'no_week'">
                                        Tanpa Week ({{ $anomalies['non_route_counts']['no_week'] ?? 0 }})
                                    </button>
                                    <button type="button" 
                                            class="px-2 py-0.5 rounded-lg text-xs font-medium cursor-pointer transition-colors"
                                            :class="nonRouteFilter === 'no_day' ? 'bg-primary text-white font-bold shadow-2xs' : 'bg-base-200 text-base-content/70 hover:text-base-content'"
                                            @click="nonRouteFilter = 'no_day'">
                                        Tanpa Hari ({{ $anomalies['non_route_counts']['no_day'] ?? 0 }})
                                    </button>
                                    <button type="button" 
                                            class="px-2 py-0.5 rounded-lg text-xs font-medium cursor-pointer transition-colors"
                                            :class="nonRouteFilter === 'sunday' ? 'bg-primary text-white font-bold shadow-2xs' : 'bg-base-200 text-base-content/70 hover:text-base-content'"
                                            @click="nonRouteFilter = 'sunday'">
                                        Hari Minggu ({{ $anomalies['non_route_counts']['sunday'] ?? 0 }})
                                    </button>
                                </div>

                                @if(empty($anomalies['non_route']))
                                    <div class="py-4 text-center text-xs text-base-content/50 flex flex-col items-center justify-center gap-1">
                                        <x-heroicon-o-check-circle class="w-5 h-5 text-success" />
                                        <span>Seluruh jadwal toko tersusun rapi (tidak ada anomali non-rute).</span>
                                    </div>
                                @else
                                    <div class="divide-y divide-base-200/70">
                                        @foreach($anomalies['non_route'] as $nrStore)
                                            <div class="py-2 px-1.5 hover:bg-base-200/50 rounded-lg cursor-pointer transition-colors"
                                                 x-show="nonRouteFilter === 'all' || 
                                                         (nonRouteFilter === 'no_schedule' && {{ $nrStore['is_no_schedule'] ? 'true' : 'false' }}) || 
                                                         (nonRouteFilter === 'no_week' && {{ $nrStore['is_no_week'] ? 'true' : 'false' }}) || 
                                                         (nonRouteFilter === 'no_day' && {{ $nrStore['is_no_day'] ? 'true' : 'false' }}) || 
                                                         (nonRouteFilter === 'sunday' && {{ $nrStore['is_sunday'] ? 'true' : 'false' }})"
                                                 wire:click="selectStore('{{ $nrStore['customer_code'] }}', 'jks')"
                                                 title="Klik untuk melihat detail & jadwal toko">
                                                <div class="flex items-start justify-between gap-1">
                                                    <span class="text-xs font-bold text-base-content truncate">{{ $nrStore['customer_name'] }}</span>
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        @if($nrStore['is_sunday'])
                                                            <span class="badge badge-xs badge-error font-bold text-white">Hari Minggu</span>
                                                        @endif
                                                        @if($nrStore['is_no_schedule'])
                                                            <span class="badge badge-xs badge-warning font-semibold">Tanpa Jadwal</span>
                                                        @elseif($nrStore['is_no_week'])
                                                            <span class="badge badge-xs badge-info font-semibold">Tanpa Week</span>
                                                        @elseif($nrStore['is_no_day'])
                                                            <span class="badge badge-xs badge-secondary font-semibold">Tanpa Hari</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-xs text-base-content/60 truncate mt-0.5">{{ $nrStore['alamat'] }}</div>
                                                <div class="flex items-center gap-2 mt-1 text-xs text-base-content/50 font-mono">
                                                    <span>{{ $nrStore['customer_code'] }}</span>
                                                    <span>•</span>
                                                    <span class="font-sans font-medium text-primary">{{ $nrStore['salesman_name'] }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Info Footer --}}
                        <div class="mt-auto pt-2 border-t border-base-200 shrink-0 text-xs text-base-content/60 bg-base-200/30 p-2 rounded-lg">
                            💡 Klik salah satu toko di atas untuk melihat detail lengkap dan mengarahkan kamera peta ke titik lokasi toko tersebut.
                        </div>
                    </div>
                @endif

                {{-- TAB CONTENT 3: DETAIL TOKO TERPILIH --}}
                @if($drawerTab === 'store')
                    <div class="flex-1 min-h-0 flex flex-col p-3 overflow-hidden">
                        @if($selectedStore)
                            <div class="flex-1 min-h-0 overflow-y-auto pr-1 flex flex-col gap-3">
                                {{-- Header Toko --}}
                                <div class="pb-2 border-b border-base-200">
                                    <div class="flex items-center justify-between gap-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" 
                                                    wire:click="$set('drawerTab', 'anomali')" 
                                                    class="btn btn-ghost btn-2xs text-xs px-1 text-base-content/70 hover:text-base-content cursor-pointer" 
                                                    title="Kembali ke Daftar Anomali">
                                                <x-heroicon-m-arrow-left class="w-3.5 h-3.5" />
                                            </button>
                                            <span class="badge badge-xs {{ $selectedStoreType === 'jks' ? 'badge-primary' : 'badge-warning' }} uppercase font-bold tracking-wider">
                                                {{ $selectedStoreType === 'jks' ? 'JKS Salesman' : 'Outlet Non-JKS' }}
                                            </span>
                                        </div>
                                        <span class="text-xs font-mono text-base-content/50">{{ $selectedStore->customer_code }}</span>
                                    </div>
                                    <h4 class="text-sm font-bold text-base-content mt-1">{{ $selectedStore->customer_name }}</h4>
                                    <p class="text-xs text-base-content/70 mt-0.5">{{ $selectedStore->alamat ?? '-' }}</p>
                                    @if(!empty($selectedStore->kecamatan) || !empty($selectedStore->desa))
                                        <div class="text-xs text-base-content/50 mt-1">
                                            Kec. {{ $selectedStore->kecamatan ?? '-' }}, Kel. {{ $selectedStore->desa ?? '-' }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Detail Tambahan Toko JKS --}}
                                @if($selectedStoreType === 'jks')
                                    <div class="bg-base-200/50 p-2.5 rounded-xl flex flex-col gap-2 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="text-base-content/60">Salesman Terjadwal:</span>
                                            <span class="font-bold text-primary">{{ $selectedStore->salesman_name }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-base-content/60">Hari Kunjungan:</span>
                                            <div class="flex items-center gap-1 font-mono text-xs">
                                                @foreach(['h1'=>'Sen', 'h2'=>'Sel', 'h3'=>'Rab', 'h4'=>'Kam', 'h5'=>'Jum', 'h6'=>'Sab', 'h7'=>'Min'] as $hk => $hl)
                                                    <span class="px-1.5 py-0.5 rounded text-xs {{ ($selectedStore->{$hk} ?? '') === 'Y' ? 'bg-primary text-white font-bold' : 'bg-base-300 text-base-content/40' }}">{{ $hl }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-base-content/60">Minggu Kunjungan:</span>
                                            <div class="flex items-center gap-1 font-mono text-xs">
                                                @foreach(['w1'=>'W1', 'w2'=>'W2', 'w3'=>'W3', 'w4'=>'W4'] as $wk => $wl)
                                                    <span class="px-2 py-0.5 rounded text-xs {{ ($selectedStore->{$wk} ?? '') === 'Y' ? 'bg-primary text-white font-bold' : 'bg-base-300 text-base-content/40' }}">{{ $wl }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- Non-JKS Detail --}}
                                    <div class="bg-amber-500/10 border border-amber-500/20 p-2.5 rounded-xl flex flex-col gap-2 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="text-base-content/60">Rata-rata Penjualan:</span>
                                            <span class="font-bold text-amber-600">Rp {{ number_format($selectedStore->avg_value_net ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-base-content/60">Status Approval:</span>
                                            @if(!empty($selectedStore->pending_approval_id))
                                                <span class="badge badge-xs badge-warning font-semibold">Menunggu Persetujuan</span>
                                            @else
                                                <span class="badge badge-xs badge-ghost">Siap Diajukan</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- GPS Coordinates --}}
                                @if(!empty($selectedStore->latitude) && !empty($selectedStore->longitude))
                                    <div class="flex items-center justify-between text-xs bg-base-200/30 px-2.5 py-1.5 rounded-lg">
                                        <span class="text-base-content/60 font-mono">📍 {{ round((float)$selectedStore->latitude, 5) }}, {{ round((float)$selectedStore->longitude, 5) }}</span>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $selectedStore->latitude }},{{ $selectedStore->longitude }}" target="_blank" class="text-primary hover:underline font-semibold flex items-center gap-0.5">
                                            Google Maps <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                @else
                                    <div class="text-xs text-error bg-error/10 px-2.5 py-1.5 rounded-lg flex items-center gap-1.5">
                                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5 shrink-0" />
                                        <span>Koordinat GPS toko belum terdaftar.</span>
                                    </div>
                                @endif

                                {{-- Interaktif Actions Buttons (Bulan Berjalan Only) --}}
                                @if($this->canManipulate)
                                    <div class="mt-auto pt-3 border-t border-base-200 flex flex-col gap-2">
                                        <span class="text-xs font-bold text-base-content/50 uppercase tracking-wider">Aksi Interaktif Rute:</span>
                                        @if($selectedStoreType === 'jks')
                                            <button type="button" 
                                                    wire:click="openReassignModal" 
                                                    class="btn btn-sm btn-outline btn-primary rounded-xl w-full justify-start gap-2 text-xs font-semibold cursor-pointer">
                                                <x-heroicon-m-arrow-path-rounded-square class="w-4 h-4" />
                                                Pindahkan ke Salesman Lain
                                            </button>
                                            <button type="button" 
                                                    wire:click="openEditScheduleModal" 
                                                    class="btn btn-sm btn-outline btn-neutral rounded-xl w-full justify-start gap-2 text-xs font-semibold cursor-pointer">
                                                <x-heroicon-m-calendar class="w-4 h-4" />
                                                Ubah Jadwal Hari & Minggu
                                            </button>
                                        @else
                                            <button type="button" 
                                                    wire:click="openAddNonRouteModal" 
                                                    class="btn btn-sm btn-primary text-white rounded-xl w-full gap-2 text-xs font-semibold cursor-pointer">
                                                <x-heroicon-s-plus-circle class="w-4 h-4" />
                                                Tambahkan ke Jadwal JKS
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Placeholder Empty State --}}
                            <div class="flex-1 flex flex-col items-center justify-center text-center p-6 text-base-content/50 gap-2">
                                <div class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center text-base-content/40">
                                    <x-heroicon-o-map-pin class="w-6 h-6" />
                                </div>
                                <span class="text-xs font-bold text-base-content/70">Belum Ada Toko Terpilih</span>
                                <span class="text-xs text-base-content/60 leading-relaxed max-w-xs">
                                    Klik salah satu titik toko di peta atau pilih dari daftar Anomali untuk melihat rincian dan melakukan penyesuaian rute.
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- MODAL 1: REASSIGN SALESMAN --}}
    @if($showReassignModal)
        <div class="modal modal-open z-50">
            <div class="modal-box max-w-md rounded-2xl p-5 border border-base-300 shadow-2xl bg-base-100">
                <div class="flex items-start justify-between pb-3 border-b border-base-200">
                    <div>
                        <h3 class="text-sm font-bold text-base-content">Pindahkan Toko ke Salesman Baru</h3>
                        <p class="text-xs text-base-content/60 mt-0.5">Toko: <span class="font-semibold text-base-content">{{ $selectedStore->customer_name ?? '' }}</span></p>
                    </div>
                    <button type="button" wire:click="$set('showReassignModal', false)" class="btn btn-xs btn-ghost btn-circle">✕</button>
                </div>

                <div class="py-4 flex flex-col gap-3 text-xs">
                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1">Salesman Saat Ini:</label>
                        <div class="p-2 rounded-xl bg-base-200 text-xs font-semibold">{{ $selectedStore->salesman_name ?? '-' }} ({{ $selectedStore->salesman_code ?? '-' }})</div>
                    </div>

                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1">Pilih Salesman Tujuan:</label>
                        <select wire:model="reassignTargetSalesman" class="select select-sm select-bordered w-full rounded-xl text-xs">
                            <option value="">-- Pilih Salesman Baru --</option>
                            @foreach($territories as $sCode => $sInfo)
                                @if($sCode !== ($selectedStore->salesman_code ?? ''))
                                    <option value="{{ $sCode }}">{{ $sInfo['name'] }} ({{ $sCode }}) - {{ $sInfo['total_stores'] ?? $sInfo['total_toko'] ?? 0 }} Toko</option>
                                @endif
                            @endforeach
                        </select>
                        @error('reassignTargetSalesman') <span class="text-error text-xs font-medium mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1">Alasan Perpindahan Rute:</label>
                        <textarea wire:model="reassignReason" rows="2" class="textarea textarea-bordered w-full rounded-xl text-xs" placeholder="Contoh: Efisiensi rute, toko lebih searah dengan salesman tujuan..."></textarea>
                        @error('reassignReason') <span class="text-error text-xs font-medium mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-2.5 rounded-xl bg-primary/10 border border-primary/20 text-xs text-primary-content/80 leading-relaxed">
                        ℹ️ Perubahan ini akan memindahkan alokasi rute untuk periode <strong>{{ \Carbon\Carbon::parse($appliedBulan . '-01')->translatedFormat('F Y') }}</strong>.
                    </div>
                </div>

                <div class="modal-action border-t border-base-200 pt-3">
                    <button type="button" wire:click="$set('showReassignModal', false)" class="btn btn-sm btn-ghost rounded-xl text-xs">Batal</button>
                    <button type="button" wire:click="submitReassignSalesman" class="btn btn-sm btn-primary text-white rounded-xl text-xs font-semibold">Simpan & Ajukan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 2: EDIT JADWAL HARI & MINGGU --}}
    @if($showEditScheduleModal)
        <div class="modal modal-open z-50">
            <div class="modal-box max-w-md rounded-2xl p-5 border border-base-300 shadow-2xl bg-base-100">
                <div class="flex items-start justify-between pb-3 border-b border-base-200">
                    <div>
                        <h3 class="text-sm font-bold text-base-content">Ubah Jadwal Kunjungan Toko</h3>
                        <p class="text-xs text-base-content/60 mt-0.5">Toko: <span class="font-semibold text-base-content">{{ $selectedStore->customer_name ?? '' }}</span></p>
                    </div>
                    <button type="button" wire:click="$set('showEditScheduleModal', false)" class="btn btn-xs btn-ghost btn-circle">✕</button>
                </div>

                <div class="py-4 flex flex-col gap-4 text-xs">
                    {{-- Hari Selection --}}
                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1.5">Hari Kunjungan:</label>
                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5">
                            @foreach(['h1'=>'Sen', 'h2'=>'Sel', 'h3'=>'Rab', 'h4'=>'Kam', 'h5'=>'Jum', 'h6'=>'Sab', 'h7'=>'Min'] as $hk => $hl)
                                <label class="flex flex-col items-center justify-center p-2 rounded-xl border border-base-300 cursor-pointer transition-all hover:bg-base-200 {{ ($editHari[$hk] ?? false) ? 'bg-primary/10 border-primary text-primary font-bold' : 'text-base-content/70' }}">
                                    <input type="checkbox" wire:model="editHari.{{ $hk }}" class="checkbox checkbox-2xs checkbox-primary mb-1" />
                                    <span class="text-xs">{{ $hl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Minggu Selection --}}
                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1.5">Minggu Kunjungan:</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['w1'=>'W1', 'w2'=>'W2', 'w3'=>'W3', 'w4'=>'W4'] as $wk => $wl)
                                <label class="flex items-center justify-center gap-1.5 p-2 rounded-xl border border-base-300 cursor-pointer transition-all hover:bg-base-200 {{ ($editMinggu[$wk] ?? false) ? 'bg-primary/10 border-primary text-primary font-bold' : 'text-base-content/70' }}">
                                    <input type="checkbox" wire:model="editMinggu.{{ $wk }}" class="checkbox checkbox-2xs checkbox-primary" />
                                    <span class="text-xs font-semibold">{{ $wl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1">Alasan Perubahan Jadwal:</label>
                        <textarea wire:model="editReason" rows="2" class="textarea textarea-bordered w-full rounded-xl text-xs" placeholder="Contoh: Mengikuti jadwal buka toko, penyesuaian hari pengiriman..."></textarea>
                        @error('editReason') <span class="text-error text-xs font-medium mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-action border-t border-base-200 pt-3">
                    <button type="button" wire:click="$set('showEditScheduleModal', false)" class="btn btn-sm btn-ghost rounded-xl text-xs">Batal</button>
                    <button type="button" wire:click="submitEditSchedule" class="btn btn-sm btn-primary text-white rounded-xl text-xs font-semibold">Simpan & Ajukan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: TAMBAH OUTLET NON-JKS KE JKS --}}
    @if($showAddNonRouteModal)
        <div class="modal modal-open z-50">
            <div class="modal-box max-w-md rounded-2xl p-5 border border-base-300 shadow-2xl bg-base-100">
                <div class="flex items-start justify-between pb-3 border-b border-base-200">
                    <div>
                        <h3 class="text-sm font-bold text-base-content">Tambah Outlet Non-JKS ke Rute</h3>
                        <p class="text-xs text-base-content/60 mt-0.5">Toko: <span class="font-semibold text-base-content">{{ $selectedStore->customer_name ?? '' }}</span></p>
                    </div>
                    <button type="button" wire:click="$set('showAddNonRouteModal', false)" class="btn btn-xs btn-ghost btn-circle">✕</button>
                </div>

                <div class="py-4 flex flex-col gap-3.5 text-xs">
                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1">Pilih Salesman:</label>
                        <select wire:model="addSalesman" class="select select-sm select-bordered w-full rounded-xl text-xs">
                            <option value="">-- Pilih Salesman --</option>
                            @foreach($territories as $sCode => $sInfo)
                                <option value="{{ $sCode }}">{{ $sInfo['name'] }} ({{ $sCode }})</option>
                            @endforeach
                        </select>
                        @error('addSalesman') <span class="text-error text-xs font-medium mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1">Pilih Hari Kunjungan:</label>
                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-1">
                            @foreach(['1'=>'Sen', '2'=>'Sel', '3'=>'Rab', '4'=>'Kam', '5'=>'Jum', '6'=>'Sab', '7'=>'Min'] as $dVal => $dLabel)
                                <label class="flex flex-col items-center justify-center p-1.5 rounded-lg border border-base-300 cursor-pointer hover:bg-base-200">
                                    <input type="checkbox" wire:model="addHari" value="{{ $dVal }}" class="checkbox checkbox-2xs checkbox-primary mb-1" />
                                    <span class="text-xs">{{ $dLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('addHari') <span class="text-error text-xs font-medium mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label-text font-bold text-base-content/70 block mb-1">Pilih Minggu Kunjungan:</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            @foreach(['1'=>'W1', '2'=>'W2', '3'=>'W3', '4'=>'W4'] as $wVal => $wLabel)
                                <label class="flex items-center justify-center gap-1.5 p-1.5 rounded-lg border border-base-300 cursor-pointer hover:bg-base-200">
                                    <input type="checkbox" wire:model="addMinggu" value="{{ $wVal }}" class="checkbox checkbox-2xs checkbox-primary" />
                                    <span class="text-xs">{{ $wLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('addMinggu') <span class="text-error text-xs font-medium mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-action border-t border-base-200 pt-3">
                    <button type="button" wire:click="$set('showAddNonRouteModal', false)" class="btn btn-sm btn-ghost rounded-xl text-xs">Batal</button>
                    <button type="button" wire:click="submitAddNonRouteToJks" class="btn btn-sm btn-primary text-white rounded-xl text-xs font-semibold">Kirim Pengajuan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- TOAST NOTIFICATIONS --}}
    <div class="toast toast-top toast-end z-50">
        <template x-for="t in toasts" :key="t.id">
            <div :class="{
                'alert alert-success shadow-lg text-white': t.type === 'success',
                'alert alert-error shadow-lg text-white': t.type === 'error',
                'alert alert-info shadow-lg text-white': t.type === 'info',
                'alert alert-warning shadow-lg text-white': t.type === 'warning'
            }" class="rounded-xl py-2 px-3 text-xs flex items-center gap-2">
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>

</div>

{{-- SCRIPT ALPINE & MAPLIBRE INTEGRATION --}}
<script>
function jksMapsApp() {
    return {
        map: null,
        depotMarker: null,
        hoverPopup: null,
        selectedMarker: null,
        selectedPopup: null,
        toasts: [],
        currentBasemap: 'light',
        
        toggleBasemap() {
            if (!this.map) return;
            this.currentBasemap = this.currentBasemap === 'light' ? 'voyager' : 'light';
            if (this.map.getLayer('carto-light-layer')) {
                this.map.setLayoutProperty('carto-light-layer', 'visibility', this.currentBasemap === 'light' ? 'visible' : 'none');
            }
            if (this.map.getLayer('carto-voyager-layer')) {
                this.map.setLayoutProperty('carto-voyager-layer', 'visibility', this.currentBasemap === 'voyager' ? 'visible' : 'none');
            }
            this.addToast({ type: 'info', message: 'Gaya peta CARTO diubah ke: ' + (this.currentBasemap === 'light' ? 'CARTO Light (Positron)' : 'CARTO Voyager') });
        },
        
        addToast(toast) {
            const id = Date.now();
            this.toasts.push({ id, ...toast });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3500);
        },

        init() {
            this.$nextTick(() => {
                this.initMap();
            });
        },

        getGeoData(event) {
            if (event && event.detail) {
                if (event.detail.data) return event.detail.data;
                if (event.detail[0] && event.detail[0].data) return event.detail[0].data;
            }
            const el = document.getElementById('jks-map-geojson-data');
            if (el && el.textContent) {
                try {
                    return JSON.parse(el.textContent);
                } catch(e) {
                    console.error('Failed to parse geojson data tag', e);
                }
            }
            return null;
        },

        initMap() {
            const container = document.getElementById('jks-map');
            if (!container) return;

            // Inisialisasi MapLibre GL JS dengan CARTO Light (Positron) & Voyager raster tiles
            this.map = new maplibregl.Map({
                container: 'jks-map',
                style: {
                    version: 8,
                    sources: {
                        'carto-light': {
                            type: 'raster',
                            tiles: [
                                'https://a.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                'https://b.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                'https://c.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                'https://d.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc'
                            ],
                            tileSize: 256,
                            maxzoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>'
                        },
                        'carto-voyager': {
                            type: 'raster',
                            tiles: [
                                'https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                'https://b.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                'https://c.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc',
                                'https://d.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png?key=cb1_2llg_1_d426d0746a26eebc6607cafc'
                            ],
                            tileSize: 256,
                            maxzoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>'
                        }
                    },
                    layers: [
                        {
                            id: 'carto-light-layer',
                            type: 'raster',
                            source: 'carto-light',
                            minzoom: 0,
                            maxzoom: 24,
                            layout: { visibility: 'visible' }
                        },
                        {
                            id: 'carto-voyager-layer',
                            type: 'raster',
                            source: 'carto-voyager',
                            minzoom: 0,
                            maxzoom: 24,
                            layout: { visibility: 'none' }
                        }
                    ]
                },
                center: [107.6098, -6.9175], // Default Jawa Barat
                zoom: 10,
                maxZoom: 20,
                minZoom: 4
            });

            this.hoverPopup = new maplibregl.Popup({
                closeButton: false,
                closeOnClick: false,
                offset: 12
            });

            this.map.on('load', () => {
                this.setupMapLayers();
                this.handleMapRefresh();
            });
        },

        setupMapLayers() {
            if (!this.map) return;
            const self = this;

            // 1. Source & Layer Route Lines (Polylines)
            this.map.addSource('route-lines', {
                type: 'geojson',
                data: { type: 'FeatureCollection', features: [] },
                maxzoom: 24,
                tolerance: 0
            });
            this.map.addLayer({
                id: 'route-lines-layer',
                type: 'line',
                source: 'route-lines',
                layout: {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                paint: {
                    'line-color': ['get', 'color'],
                    'line-width': 2.5,
                    'line-opacity': 0.75
                }
            });

            // 2. Source & Layer Non-Route Stores
            this.map.addSource('non-route-stores', {
                type: 'geojson',
                data: { type: 'FeatureCollection', features: [] },
                maxzoom: 24,
                tolerance: 0
            });
            this.map.addLayer({
                id: 'non-route-circle',
                type: 'circle',
                source: 'non-route-stores',
                paint: {
                    'circle-radius': [
                        'interpolate', ['linear'], ['zoom'],
                        10, 4,
                        14, 5.5,
                        17, 7.5,
                        20, 10
                    ],
                    'circle-color': '#F59E0B',
                    'circle-stroke-width': 1.5,
                    'circle-stroke-color': '#FFFFFF'
                }
            });

            // 3. Source & Layer JKS Stores (Circle Vector)
            this.map.addSource('jks-stores', {
                type: 'geojson',
                data: { type: 'FeatureCollection', features: [] },
                maxzoom: 24,
                tolerance: 0
            });
            this.map.addLayer({
                id: 'jks-stores-circle',
                type: 'circle',
                source: 'jks-stores',
                paint: {
                    'circle-radius': [
                        'interpolate', ['linear'], ['zoom'],
                        10, 4.5,
                        14, 6.5,
                        17, 9,
                        20, 12
                    ],
                    'circle-color': ['get', 'color'],
                    'circle-stroke-width': 1.5,
                    'circle-stroke-color': '#FFFFFF'
                }
            });

            // Event Listeners: Hover Toko JKS
            this.map.on('mouseenter', 'jks-stores-circle', (e) => {
                this.map.getCanvas().style.cursor = 'pointer';
                const f = e.features[0];
                const coordinates = f.geometry.coordinates.slice();
                const p = f.properties;

                // Jangan timpa jika popup detail terpilih sedang aktif di titik ini
                if (this.selectedPopup && this.selectedPopup.isOpen()) {
                    const selLngLat = this.selectedPopup.getLngLat();
                    if (Math.abs(selLngLat.lng - coordinates[0]) < 0.00001 && Math.abs(selLngLat.lat - coordinates[1]) < 0.00001) {
                        return;
                    }
                }

                const colorMode = p.color_mode || 'salesman';
                let modeBadge = '';
                if (colorMode === 'day') {
                    modeBadge = `<span class="badge badge-xs text-white font-bold" style="background-color: ${p.color};">${p.days_label || 'Tanpa Hari'}</span>`;
                } else {
                    modeBadge = `<span class="badge badge-xs text-white font-mono font-bold" style="background-color: ${p.color};">${p.salesman_code}</span>`;
                }

                const content = `
                    <div class="text-xs">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-mono text-slate-500">${p.customer_code}</span>
                            ${modeBadge}
                        </div>
                        <div class="font-bold text-slate-800 leading-snug">${p.customer_name}</div>
                        <div class="mt-1.5 pt-1 border-t border-slate-100 flex items-center justify-between gap-2">
                            <span class="font-semibold text-primary truncate max-w-28">${p.salesman_name}</span>
                            <span class="text-xs text-slate-500 font-mono">${p.weeks_label || ''} • ${p.days_label || ''}</span>
                        </div>
                    </div>
                `;
                this.hoverPopup.setLngLat(coordinates).setHTML(content).addTo(this.map);
            });

            this.map.on('mouseleave', 'jks-stores-circle', () => {
                this.map.getCanvas().style.cursor = '';
                this.hoverPopup.remove();
            });

            // Event Listener: Click Toko JKS via Livewire Component Reference
            this.map.on('click', 'jks-stores-circle', (e) => {
                const code = e.features[0].properties.customer_code;
                self.$wire.selectStore(code, 'jks');
            });

            // Event Listeners: Hover & Click Toko Non-JKS
            this.map.on('mouseenter', 'non-route-circle', (e) => {
                this.map.getCanvas().style.cursor = 'pointer';
                const f = e.features[0];
                const coordinates = f.geometry.coordinates.slice();
                const p = f.properties;

                // Jangan timpa jika popup detail terpilih sedang aktif di titik ini
                if (this.selectedPopup && this.selectedPopup.isOpen()) {
                    const selLngLat = this.selectedPopup.getLngLat();
                    if (Math.abs(selLngLat.lng - coordinates[0]) < 0.00001 && Math.abs(selLngLat.lat - coordinates[1]) < 0.00001) {
                        return;
                    }
                }

                const content = `
                    <div class="text-xs">
                        <span class="badge badge-xs badge-warning mb-1">Non-JKS</span>
                        <div class="font-bold text-slate-800">${p.customer_name}</div>
                        <div class="text-xs text-slate-500 font-mono">${p.customer_code}</div>
                    </div>
                `;
                this.hoverPopup.setLngLat(coordinates).setHTML(content).addTo(this.map);
            });

            this.map.on('mouseleave', 'non-route-circle', () => {
                this.map.getCanvas().style.cursor = '';
                this.hoverPopup.remove();
            });

            this.map.on('click', 'non-route-circle', (e) => {
                const code = e.features[0].properties.customer_code;
                self.$wire.selectStore(code, 'non_jks');
            });
        },

        handleMapRefresh(event) {
            if (!this.map) return;

            // Tunggu load map selesai
            if (!this.map.isStyleLoaded()) {
                setTimeout(() => this.handleMapRefresh(event), 200);
                return;
            }

            const geoData = this.getGeoData(event);
            if (!geoData) return;

            // Update JKS Stores
            if (this.map.getSource('jks-stores')) {
                this.map.getSource('jks-stores').setData(geoData.jks_geojson || { type: 'FeatureCollection', features: [] });
            }

            // Update Non-Route Stores
            if (this.map.getSource('non-route-stores')) {
                this.map.getSource('non-route-stores').setData(geoData.non_route_geojson || { type: 'FeatureCollection', features: [] });
            }

            // Update Route Lines
            if (this.map.getSource('route-lines')) {
                this.map.getSource('route-lines').setData(geoData.routes_geojson || { type: 'FeatureCollection', features: [] });
            }

            // Update Depot Marker
            if (this.depotMarker) {
                this.depotMarker.remove();
                this.depotMarker = null;
            }

            if (geoData.depot && geoData.depot.coordinates) {
                const el = document.createElement('div');
                el.className = 'depot-marker';
                el.innerHTML = '🏢';
                el.title = `Gudang: ${geoData.depot.name}`;

                this.depotMarker = new maplibregl.Marker({ element: el })
                    .setLngLat(geoData.depot.coordinates)
                    .setPopup(new maplibregl.Popup({ offset: 15 }).setHTML(`
                        <div class="text-xs">
                            <span class="badge badge-xs badge-neutral mb-1">Gudang Distributor</span>
                            <div class="font-bold text-slate-800">${geoData.depot.name}</div>
                            <div class="text-xs text-slate-500 font-mono">${geoData.depot.code}</div>
                        </div>
                    `))
                    .addTo(this.map);
            }

            // Auto fit bounds ke seluruh titik
            this.fitAllBounds(geoData);
        },

        fitAllBounds(data) {
            if (!this.map) return;
            const geoData = data || this.getGeoData();
            if (!geoData) return;

            const bounds = new maplibregl.LngLatBounds();
            let count = 0;

            if (geoData.jks_geojson && geoData.jks_geojson.features) {
                geoData.jks_geojson.features.forEach(f => {
                    if (Array.isArray(f.geometry?.coordinates) && f.geometry.coordinates.length === 2) {
                        bounds.extend(f.geometry.coordinates);
                        count++;
                    }
                });
            }

            if (geoData.non_route_geojson && geoData.non_route_geojson.features) {
                geoData.non_route_geojson.features.forEach(f => {
                    if (Array.isArray(f.geometry?.coordinates) && f.geometry.coordinates.length === 2) {
                        bounds.extend(f.geometry.coordinates);
                        count++;
                    }
                });
            }

            if (geoData.depot && Array.isArray(geoData.depot.coordinates) && geoData.depot.coordinates.length === 2) {
                bounds.extend(geoData.depot.coordinates);
                count++;
            }

            if (count > 0 && !bounds.isEmpty()) {
                this.map.fitBounds(bounds, { padding: 40, maxZoom: 15, duration: 800 });
            }
        },

        centerToDepot() {
            const geoData = this.getGeoData();
            if (geoData && geoData.depot && geoData.depot.coordinates && this.map) {
                this.map.flyTo({
                    center: geoData.depot.coordinates,
                    zoom: 14,
                    duration: 1000
                });
            }
        },

        flyToStore(event) {
            const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            if (!this.map || !data || !data.lng || !data.lat) return;

            const coords = [parseFloat(data.lng), parseFloat(data.lat)];
            if (isNaN(coords[0]) || isNaN(coords[1])) return;

            // 1. Bersihkan marker beacon & popup sebelumnya jika ada
            if (this.selectedMarker) {
                this.selectedMarker.remove();
                this.selectedMarker = null;
            }
            if (this.selectedPopup) {
                this.selectedPopup.remove();
                this.selectedPopup = null;
            }
            if (this.hoverPopup) {
                this.hoverPopup.remove();
            }

            // 2. Buat marker beacon beranimasi pulsa (highlight ring)
            const beaconEl = document.createElement('div');
            beaconEl.className = 'selected-store-beacon-wrap';
            beaconEl.innerHTML = `
                <div class="selected-store-beacon-ring"></div>
                <div class="selected-store-beacon-ring ring-2"></div>
                <div class="selected-store-beacon-core"></div>
            `;

            this.selectedMarker = new maplibregl.Marker({
                element: beaconEl,
                anchor: 'center'
            }).setLngLat(coords).addTo(this.map);

            // 3. Susun Popup info toko terpilih
            const isJks = data.type !== 'non_jks';
            const badgeType = isJks 
                ? `<span class="badge badge-xs badge-primary font-bold">JKS</span>`
                : `<span class="badge badge-xs badge-warning font-bold">Non-JKS</span>`;

            let detailHtml = '';
            if (isJks) {
                detailHtml = `
                    <div class="mt-2 pt-1.5 border-t border-slate-100 flex flex-col gap-1 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Salesman:</span>
                            <span class="font-bold text-primary truncate max-w-36">${data.salesman_name || data.salesman_code || '-'}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Jadwal:</span>
                            <span class="font-mono font-semibold text-slate-700">${data.weeks_label || ''} • ${data.days_label || ''}</span>
                        </div>
                    </div>
                `;
            } else {
                detailHtml = `
                    <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Status:</span>
                        <span class="badge badge-xs badge-warning font-semibold">Non-JKS</span>
                    </div>
                `;
            }

            const popupHtml = `
                <div class="p-1 min-w-[200px] max-w-[260px] text-xs">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        ${badgeType}
                        <span class="font-mono text-xs text-slate-500 font-semibold">${data.customer_code}</span>
                    </div>
                    <div class="font-bold text-slate-900 text-sm leading-snug">${data.customer_name}</div>
                    <div class="text-slate-500 text-xs mt-0.5 line-clamp-2">${data.alamat || '-'}</div>
                    ${detailHtml}
                </div>
            `;

            this.selectedPopup = new maplibregl.Popup({
                offset: 14,
                closeButton: true,
                closeOnClick: false,
                className: 'selected-store-popup'
            })
            .setLngLat(coords)
            .setHTML(popupHtml)
            .addTo(this.map);

            // Bersihkan marker beacon saat popup ditutup user
            this.selectedPopup.on('close', () => {
                if (this.selectedMarker) {
                    this.selectedMarker.remove();
                    this.selectedMarker = null;
                }
            });

            // 4. Kamera terbang ke toko (zoom minimal 16 atau pertahankan jika sudah lebih dekat)
            const targetZoom = Math.max(this.map.getZoom(), 16);
            this.map.flyTo({
                center: coords,
                zoom: targetZoom,
                duration: 900,
                essential: true
            });
        },

        zoomIn() {
            if (this.map) this.map.zoomIn();
        },

        zoomOut() {
            if (this.map) this.map.zoomOut();
        }
    };
}
</script>
