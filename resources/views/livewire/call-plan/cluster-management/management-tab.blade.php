<div class="flex flex-col gap-4 flex-1 min-h-0 w-full mt-2">
    {{-- KPI STATS CARDS --}}
    @php
        $totalStores = count($managementClusterStores);
        $totalClusters = count(array_unique(array_column($managementClusterStores, 'cluster_id')));
        
        $pilarCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];
        foreach($managementClusterStores as $s) {
            $p = (string)($s['pilar'] ?? '');
            if (str_contains($p, '1.')) $pilarCounts['1']++;
            elseif (str_contains($p, '2.')) $pilarCounts['2']++;
            elseif (str_contains($p, '3.')) $pilarCounts['3']++;
            elseif (str_contains($p, '4.')) $pilarCounts['4']++;
        }

        $covPercent = $paretoTotalStores > 0 ? round(($totalStores / $paretoTotalStores) * 100, 1) : 0;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 shrink-0">
        {{-- Total Ter-cluster --}}
        <div class="bg-base-100 border border-base-200/80 rounded-2xl p-3 sm:p-3.5 shadow-xs hover:shadow-md hover:border-primary/40 transition-all duration-300 flex items-center gap-3 relative overflow-hidden group">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.25a.75.75 0 0 1-.75-.75V4.5a.75.75 0 0 1 .75-.75h19.5a.75.75 0 0 1 .75.75v15.75a.75.75 0 0 1-.75.75H13.5Z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.62rem] font-bold text-base-content/60 uppercase tracking-wider truncate">Ter-cluster</div>
                <div class="text-base sm:text-lg font-black text-base-content leading-tight flex items-baseline gap-1">
                    {{ number_format($totalStores) }}
                    <span class="text-[0.65rem] font-semibold text-base-content/40">/ {{ number_format($paretoTotalStores) }}</span>
                </div>
            </div>
        </div>

        {{-- Total Cluster --}}
        <div class="bg-base-100 border border-base-200/80 rounded-2xl p-3 sm:p-3.5 shadow-xs hover:shadow-md hover:border-indigo-500/40 transition-all duration-300 flex items-center gap-3 relative overflow-hidden group">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.82c-.317-.159-.69-.159-1.006 0L3.622 6.257C3.24 6.447 3 6.837 3 7.263v12.417c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.62rem] font-bold text-base-content/60 uppercase tracking-wider truncate">Total Cluster</div>
                <div class="text-base sm:text-lg font-black text-base-content leading-tight">
                    {{ number_format($totalClusters) }}
                    <span class="text-[0.65rem] font-semibold text-base-content/40">Grup</span>
                </div>
            </div>
        </div>

        {{-- Pilar 1 RWO --}}
        @php
            $p1Pareto = $paretoPilarCounts['1'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200/80 rounded-2xl p-3 sm:p-3.5 shadow-xs hover:shadow-md hover:border-primary/40 transition-all duration-300 flex items-center gap-3 relative overflow-hidden group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-blue-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs transition-transform duration-300 group-hover:scale-110">
                P1
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.62rem] font-extrabold text-primary uppercase tracking-wider truncate">Pilar 1 • RWO</div>
                <div class="text-base sm:text-lg font-black text-base-content leading-tight flex items-baseline gap-1">
                    {{ number_format($pilarCounts['1']) }}
                    <span class="text-[0.65rem] font-semibold text-base-content/40">/ {{ number_format($p1Pareto) }}</span>
                </div>
            </div>
        </div>

        {{-- Pilar 2 PNR --}}
        @php
            $p2Pareto = $paretoPilarCounts['2'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200/80 rounded-2xl p-3 sm:p-3.5 shadow-xs hover:shadow-md hover:border-secondary/40 transition-all duration-300 flex items-center gap-3 relative overflow-hidden group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-secondary to-pink-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs transition-transform duration-300 group-hover:scale-110">
                P2
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.62rem] font-extrabold text-secondary uppercase tracking-wider truncate">Pilar 2 • PNR</div>
                <div class="text-base sm:text-lg font-black text-base-content leading-tight flex items-baseline gap-1">
                    {{ number_format($pilarCounts['2']) }}
                    <span class="text-[0.65rem] font-semibold text-base-content/40">/ {{ number_format($p2Pareto) }}</span>
                </div>
            </div>
        </div>

        {{-- Pilar 3 NGVO --}}
        @php
            $p3Pareto = $paretoPilarCounts['3'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200/80 rounded-2xl p-3 sm:p-3.5 shadow-xs hover:shadow-md hover:border-accent/40 transition-all duration-300 flex items-center gap-3 relative overflow-hidden group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent to-emerald-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs transition-transform duration-300 group-hover:scale-110">
                P3
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.62rem] font-extrabold text-accent uppercase tracking-wider truncate">Pilar 3 • NGVO</div>
                <div class="text-base sm:text-lg font-black text-base-content leading-tight flex items-baseline gap-1">
                    {{ number_format($pilarCounts['3']) }}
                    <span class="text-[0.65rem] font-semibold text-base-content/40">/ {{ number_format($p3Pareto) }}</span>
                </div>
            </div>
        </div>

        {{-- Pilar 4 GRO --}}
        @php
            $p4Pareto = $paretoPilarCounts['4'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200/80 rounded-2xl p-3 sm:p-3.5 shadow-xs hover:shadow-md hover:border-info/40 transition-all duration-300 flex items-center gap-3 relative overflow-hidden group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-info to-cyan-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs transition-transform duration-300 group-hover:scale-110">
                P4
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.62rem] font-extrabold text-info uppercase tracking-wider truncate">Pilar 4 • GRO</div>
                <div class="text-base sm:text-lg font-black text-base-content leading-tight flex items-baseline gap-1">
                    {{ number_format($pilarCounts['4']) }}
                    <span class="text-[0.65rem] font-semibold text-base-content/40">/ {{ number_format($p4Pareto) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 flex-1 min-h-0">
        {{-- Map Container --}}
        <div class="w-full lg:w-2/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col relative z-0" wire:ignore x-data="{ mapFilterPilar: 'all' }">
            <div id="management-map" class="w-full h-[500px] lg:h-full z-0"></div>
            
            {{-- Map Filter Controls Bar --}}
            <div class="absolute top-3 left-3 bg-base-100/90 backdrop-blur p-1 rounded-xl border border-base-300 shadow-md z-[400] text-xs">
                <select x-model="mapFilterPilar" @change="window.filterManagementMapMarkers(mapFilterPilar, 'all')" class="select select-xs select-bordered bg-base-100 font-semibold text-[0.65rem] h-7 min-h-0">
                    <option value="all">Semua Pilar</option>
                    <option value="1">Pilar 1 (RWO)</option>
                    <option value="2">Pilar 2 (PNR)</option>
                    <option value="3">Pilar 3 (NGVO)</option>
                    <option value="4">Pilar 4 (GRO)</option>
                </select>
            </div>

            <div class="absolute bottom-4 right-4 bg-base-100/90 backdrop-blur p-2 rounded-lg border border-base-300 shadow-sm z-[400] text-xs">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-warning"></div>
                        <span>Unclustered</span>
                    </div>
                    <div class="text-[0.6rem] text-base-content/50 mt-1">
                        Titik-titik toko dari cluster yang tersimpan
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Sidebar --}}
        <div x-data="{ 
                search: '',
                activeKecPopover: null,
                hiddenClusters: [],
                cardState: 'collapse',
                init() {
                    this.updateMap();
                },
                toggleCluster(cId) {
                    if (this.hiddenClusters.includes(cId)) {
                        this.hiddenClusters = this.hiddenClusters.filter(function(id) { return id !== cId; });
                    } else {
                        this.hiddenClusters.push(cId);
                    }
                    this.updateMap();
                },
                toggleAllMapClusters(visible) {
                    if (visible) {
                        this.hiddenClusters = [];
                    } else {
                        const allCIds = Array.from(document.querySelectorAll('[data-cluster-id]')).map(function(el) { return parseInt(el.getAttribute('data-cluster-id')); });
                        this.hiddenClusters = [...new Set(allCIds)];
                    }
                    this.updateMap();
                },
                updateMap() {
                    if (window.updateMapClusterVisibility) {
                        window.updateMapClusterVisibility(this.hiddenClusters);
                    }
                },
                isClusterVisible(cId) {
                    return !this.hiddenClusters.includes(cId);
                },
                matchesSearch(seq, kec, kecFull, stores) {
                    if (!this.search || this.search.trim() === '') return true;
                    const q = this.search.toLowerCase().trim();
                    const textToMatch = ('cluster ' + seq + ' ' + kec + ' ' + kecFull).toLowerCase();
                    if (textToMatch.includes(q)) return true;
                    if (stores && Array.isArray(stores)) {
                        return stores.some(function(s) { 
                            return (s.customer_name && s.customer_name.toLowerCase().includes(q)) ||
                                   (s.customer_code_prc && s.customer_code_prc.toLowerCase().includes(q)) ||
                                   (s.kelurahan && s.kelurahan.toLowerCase().includes(q)) ||
                                   (s.kecamatan && s.kecamatan.toLowerCase().includes(q));
                        });
                    }
                    return false;
                }
             }" 
             class="w-full lg:w-1/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col relative">
            
            <div class="p-3 border-b border-base-300 bg-base-200/50 flex flex-col gap-2 z-10 shadow-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-sm">Daftar Cluster</h3>
                        <p class="text-[0.65rem] text-base-content/60">{{ count($managementClusterStores) }} Total Toko</p>
                    </div>
                    @if(count($managementClusterStores) > 0)
                        <button type="button" 
                                wire:click="openConfirmDeleteAllClustersModal" 
                                class="btn btn-xs btn-error text-white gap-1 shadow-xs" 
                                title="Hapus Semua Cluster">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            Hapus Semua
                        </button>
                    @endif
                </div>

                {{-- Real-time Search Input --}}
                @if(count($managementClusterStores) > 0)
                <div class="relative w-full">
                    <input type="text" 
                           x-model="search" 
                           placeholder="Cari cluster, kecamatan, atau nama/kode toko..." 
                           class="input input-xs input-bordered w-full pr-7 font-medium text-xs bg-base-100" />
                    <button x-show="search.length > 0" 
                            @click="search = ''" 
                            type="button" 
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content text-xs font-bold">
                        ✕
                    </button>
                </div>
                
                {{-- Quick Controls --}}
                <div class="flex flex-col gap-2 pt-1 border-t border-base-200/60 mt-1">
                    <div class="flex w-full">
                        <div class="join w-1/2 pr-1">
                            <button type="button" @click="toggleAllMapClusters(true)" 
                                    class="btn btn-xs join-item flex-1 font-medium text-[0.6rem] px-1 h-6 min-h-0 btn-outline border-primary/30 text-primary hover:bg-primary hover:text-white" 
                                    title="Tampilkan Semua di Peta">Tampil Peta</button>
                            <button type="button" @click="toggleAllMapClusters(false)" 
                                    class="btn btn-xs join-item flex-1 font-medium text-[0.6rem] px-1 h-6 min-h-0 btn-outline border-base-300 text-base-content/70 hover:bg-base-300" 
                                    title="Sembunyikan Semua dari Peta">Sembunyi Peta</button>
                        </div>
                        <div class="join w-1/2 pl-1">
                            <button type="button" @click="$dispatch('expand-all-m'); cardState = 'expand'" 
                                    :class="cardState === 'expand' ? 'bg-secondary border-secondary text-white hover:bg-secondary/90' : 'btn-outline border-base-300 hover:bg-secondary/10 hover:border-secondary/30 hover:text-secondary text-base-content/70'" 
                                    class="btn btn-xs join-item flex-1 font-medium text-[0.6rem] px-1 h-6 min-h-0" 
                                    title="Buka Semua Card">Buka Card</button>
                            <button type="button" @click="$dispatch('collapse-all-m'); cardState = 'collapse'" 
                                    :class="cardState === 'collapse' ? 'bg-base-300 border-base-300 text-base-content' : 'btn-outline border-base-300 hover:bg-base-200 text-base-content/70'" 
                                    class="btn btn-xs join-item flex-1 font-medium text-[0.6rem] px-1 h-6 min-h-0" 
                                    title="Tutup Semua Card">Tutup Card</button>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="flex-1 overflow-auto p-2 bg-base-200/30">
                @php
                    $mSummary = $clusterSummary ?? [];
                @endphp

                @if(count($mSummary) === 0)
                    <div class="text-center py-10 px-4 text-base-content/50 flex flex-col items-center justify-center gap-3">
                        <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center text-base-content/30 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.82c-.317-.159-.69-.159-1.006 0L3.622 6.257C3.24 6.447 3 6.837 3 7.263v12.417c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>
                        </div>
                        <div class="text-xs font-semibold max-w-[200px] leading-relaxed">
                            @if(empty($managementSelectedTeam))
                                Silakan pilih <span class="text-primary font-bold">Team Sales</span> terlebih dahulu.
                            @else
                                Belum ada cluster tersimpan untuk team ini.
                            @endif
                        </div>
                    </div>
                @else
                    <div class="flex flex-col gap-2 w-full">
                        @foreach($mSummary as $cId => $data)
                            @php
                                $hue = ($cId * 137.5) % 360;
                            @endphp
                            <div wire:key="cluster-card-{{ $cId }}"
                                 data-cluster-id="{{ $cId }}"
                                 x-show="matchesSearch('{{ $data['seq'] }}', '{{ addslashes($data['kec_str']) }}', '{{ addslashes($data['kec_str_full'] ?? '') }}', {{ json_encode($data['stores']) }})"
                                 x-data="{ open: false }"
                                 @expand-all-m.window="open = true"
                                 @collapse-all-m.window="open = false"
                                 x-init="$watch('search', function(val) { if(val.trim().length) open = true; })"
                                 class="border border-base-300 bg-base-100 rounded-xl shadow-xs hover:border-primary/30 transition-all">
                                {{-- Header / Trigger --}}
                                <div class="flex justify-between items-center gap-2 p-2.5 w-full cursor-pointer select-none hover:bg-base-200/60 transition-colors"
                                     :class="open ? 'rounded-t-xl' : 'rounded-xl'"
                                     @click="open = !open">
                                    <div class="flex items-center gap-2 shrink-0">
                                        <input type="checkbox" 
                                               :checked="isClusterVisible({{ $cId }})" 
                                               @change="toggleCluster({{ $cId }})"
                                               @click.stop
                                               class="checkbox checkbox-xs checkbox-primary rounded-full shrink-0" 
                                               title="Tampilkan / Sembunyikan Cluster {{ $data['seq'] }} di Peta" />
                                        <div class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: hsl({{ $hue }}, 70%, 50%);"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="font-bold text-xs sm:text-sm truncate shrink-0">Cluster {{ $data['seq'] }}</span>
                                            <span class="text-[0.7rem] opacity-70 truncate flex-1 min-w-0">{{ $data['kec_str'] }}</span>
                                            @if(!empty($data['kec_str_full']))
                                                <div class="relative z-30 inline-block shrink-0"
                                                     @click.stop
                                                     @click.outside="if (activeKecPopover === {{ $cId }}) activeKecPopover = null">
                                                     <button type="button" 
                                                             class="btn btn-ghost btn-xs btn-circle text-base-content/50 hover:text-info shrink-0 p-0" 
                                                             @click="activeKecPopover = (activeKecPopover === {{ $cId }} ? null : {{ $cId }})"
                                                             title="Daftar Lengkap Kecamatan">
                                                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                                                     </button>
                                                     <div x-show="activeKecPopover === {{ $cId }}" 
                                                          x-transition:enter="transition ease-out duration-150"
                                                          x-transition:enter-start="opacity-0 scale-95"
                                                          x-transition:enter-end="opacity-100 scale-100"
                                                          x-transition:leave="transition ease-in duration-100"
                                                          x-transition:leave-start="opacity-100 scale-100"
                                                          x-transition:leave-end="opacity-0 scale-95"
                                                          class="absolute right-0 top-full mt-1 w-56 p-2.5 bg-base-100 rounded-xl shadow-xl border border-base-300 z-50 text-xs text-base-content normal-case font-normal"
                                                          style="display: none;">
                                                         <div class="font-bold text-[0.7rem] text-primary mb-1 border-b border-base-200 pb-1">Daftar Kecamatan Cluster {{ $data['seq'] }}</div>
                                                         <div class="text-[0.68rem] text-base-content/80 leading-relaxed font-medium">
                                                             {{ trim($data['kec_str_full'], ' ()') }}
                                                         </div>
                                                     </div>
                                                </div>
                                            @endif
                                        </div>
                                        {{-- Pillar Badges (Compact, single line) --}}
                                        <div class="flex items-center gap-2 mt-0.5 text-[0.6rem] font-semibold overflow-hidden whitespace-nowrap">
                                            @if(($data['pilar']['1'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-0.5 text-primary shrink-0" title="Pilar 1 RWO: {{ $data['pilar']['1'] }} Toko">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>{{ $data['pilar']['1'] }} RWO
                                                </span>
                                            @endif
                                            @if(($data['pilar']['2'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-0.5 text-secondary shrink-0" title="Pilar 2 PNR: {{ $data['pilar']['2'] }} Toko">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary shrink-0"></span>{{ $data['pilar']['2'] }} PNR
                                                </span>
                                            @endif
                                            @if(($data['pilar']['3'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-0.5 text-accent shrink-0" title="Pilar 3 NGVO: {{ $data['pilar']['3'] }} Toko">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-accent shrink-0"></span>{{ $data['pilar']['3'] }} NGVO
                                                </span>
                                            @endif
                                            @if(($data['pilar']['4'] ?? 0) > 0)
                                                <span class="inline-flex items-center gap-0.5 text-info shrink-0" title="Pilar 4 GRO: {{ $data['pilar']['4'] }} Toko">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-info shrink-0"></span>{{ $data['pilar']['4'] }} GRO
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="badge badge-xs sm:badge-sm badge-neutral shrink-0">
                                        {{ $data['count'] }} Toko
                                    </div>
                                    <div class="flex items-center gap-0.5 shrink-0 z-20 relative">
                                        {{-- Add to JKS Team Elite --}}
                                        <button type="button" wire:click="openJksModal({{ $cId }})" class="btn btn-xs btn-ghost text-success hover:bg-success/10 p-1" onclick="event.stopPropagation();" title="Tambah ke JKS Team Elite">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        </button>
                                        {{-- Gabung ke Cluster Lain --}}
                                        <button type="button" wire:click="openMergeModal({{ $cId }})" class="btn btn-xs btn-ghost text-info hover:bg-info/10 p-1" onclick="event.stopPropagation();" title="Gabungkan ke Cluster Lain">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m-3-13.5L18 7.5m0 0L13.5 12M18 7.5H4.5" /></svg>
                                        </button>
                                        {{-- Hapus Cluster --}}
                                        <button type="button" wire:click="openConfirmDeleteClusterModal({{ $cId }})" class="btn btn-xs btn-ghost text-error hover:bg-error/10 p-1" onclick="event.stopPropagation();" title="Hapus Cluster">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </div>
                                    {{-- Chevron indicator --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                         class="w-3.5 h-3.5 shrink-0 text-base-content/40 transition-transform duration-200"
                                         :class="{ 'rotate-180': open }">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>

                                {{-- Collapsible content --}}
                                <div x-show="open"
                                     class="border-t border-base-300 rounded-b-xl overflow-hidden">
                                    <div class="overflow-x-auto">
                                        <table class="table table-xs table-zebra w-full text-[0.7rem]">
                                            <thead class="bg-base-200/80 text-[0.65rem] border-b border-base-200 text-base-content/70">
                                                <tr>
                                                    <th class="w-8 text-center py-1 px-1">
                                                        @php
                                                            $cKeys = array_map(fn($s) => 'item-' . $s['item_id'], $data['stores']);
                                                            $isAllSelected = count($cKeys) > 0 && count(array_intersect($cKeys, $selectedStoreIds)) === count($cKeys);
                                                        @endphp
                                                        <input type="checkbox" 
                                                               wire:click="toggleSelectClusterStores({{ $cId }})" 
                                                               {{ $isAllSelected ? 'checked' : '' }} 
                                                               class="checkbox checkbox-xs checkbox-primary rounded-xs" 
                                                               title="Pilih Semua / Batal Pilih Toko Cluster {{ $data['seq'] }}" />
                                                    </th>
                                                    <th class="py-1 px-2 font-semibold">Toko ({{ count($data['stores']) }})</th>
                                                    <th class="py-1 px-2 text-right font-semibold pr-3">Aksi</th>
                                                </tr>
                                            </thead>
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
                                                    <tr wire:key="store-row-{{ $st['item_id'] ?? $st['id'] }}" :class="search.length > 0 && ('{{ strtolower(addslashes($st['customer_name'] ?? '')) }}'.includes(search.toLowerCase().trim()) || '{{ strtolower(addslashes($st['customer_code_prc'] ?? '')) }}'.includes(search.toLowerCase().trim())) ? 'bg-amber-100 text-gray-900 font-bold' : ''" class="hover:bg-base-200/50 transition-colors group">
                                                        <td class="w-8 text-center font-mono opacity-70">
                                                            <input type="checkbox" wire:model.live="selectedStoreIds" value="item-{{ $st['item_id'] }}" class="checkbox checkbox-xs checkbox-primary rounded-xs" />
                                                        </td>
                                                        <td ondblclick="window.focusManagementMapOnStore('{{ $st['latitude'] ?? 0 }}', '{{ $st['longitude'] ?? 0 }}', {{ $st['id'] }})" class="cursor-pointer w-full" title="Klik ganda untuk fokus di peta">
                                                            <div class="flex items-center gap-2">
                                                                <div class="font-bold">{{ $st['customer_name'] }}</div>
                                                                <button type="button" onclick="window.focusManagementMapOnStore('{{ $st['latitude'] ?? 0 }}', '{{ $st['longitude'] ?? 0 }}', {{ $st['id'] }})" class="btn btn-ghost btn-xs btn-circle text-info opacity-50 hover:opacity-100" title="Fokus di Peta">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                                                </button>
                                                            </div>
                                                            <div class="opacity-60">{{ $st['customer_code_prc'] }} &bull; {{ $st['kelurahan'] ?? '-' }}</div>
                                                        </td>
                                                        <td class="text-right whitespace-nowrap">
                                                            <div class="flex items-center justify-end gap-1 relative z-10">
                                                                <span class="badge badge-xs {{ $pilarClass }} border-none px-2 py-2 font-bold shadow-sm">{{ $pilarName }}</span>
                                                                <button type="button" wire:click="openMoveStoreModal({{ $st['item_id'] }}, '{{ addslashes($st['customer_name']) }}')" onclick="event.stopPropagation();" class="btn btn-ghost btn-xs btn-circle text-warning hover:bg-warning/10 relative z-10" title="Pindahkan Toko Ini ke Cluster Lain">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                                                </button>
                                                                <button type="button" wire:click="openConfirmDeleteStoreModal({{ $st['item_id'] }})" onclick="event.stopPropagation();" class="btn btn-ghost btn-xs btn-circle text-error hover:bg-error/10 relative z-10" title="Keluarkan Toko dari Cluster">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                                </button>
                                                            </div>
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

                {{-- Section: Toko Belum Ter-cluster --}}
                @if(count($unclusteredStores) > 0)
                    <div class="mt-3">
                        <div wire:key="unclustered-stores-card" data-cluster-id="0" 
                             x-data="{ open: false }" 
                             @expand-all-m.window="open = true"
                             @collapse-all-m.window="open = false"
                             class="border border-warning/40 bg-warning/5 rounded-xl shadow-xs overflow-hidden">
                            <div class="flex justify-between items-center gap-2 p-2.5 w-full cursor-pointer select-none hover:bg-warning/10 transition-colors" @click="open = !open">
                                <div class="flex items-center gap-2 shrink-0">
                                    <input type="checkbox" 
                                           :checked="isClusterVisible(0)" 
                                           @change="toggleCluster(0)"
                                           @click.stop
                                           class="checkbox checkbox-xs checkbox-warning rounded-full shrink-0" 
                                           title="Tampilkan / Sembunyikan Toko Unclustered di Peta" />
                                    <div class="w-3.5 h-3.5 rounded-full shrink-0 bg-gray-500"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-xs sm:text-sm leading-tight text-warning-content truncate">
                                        Toko Belum Ter-cluster
                                    </div>
                                </div>
                                <div class="badge badge-xs sm:badge-sm badge-warning font-bold shrink-0">
                                    {{ count($unclusteredStores) }} Toko
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                     class="w-3.5 h-3.5 shrink-0 text-base-content/40 transition-transform duration-200"
                                     :class="{ 'rotate-180': open }">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>

                            <div x-show="open" class="border-t border-warning/30 bg-base-100">
                                <div class="overflow-x-auto">
                                    <table class="table table-xs table-zebra w-full text-[0.7rem]">
                                        <thead class="bg-warning/10 text-[0.65rem] border-b border-warning/20 text-warning-content/80">
                                            <tr>
                                                <th class="w-8 text-center py-1 px-1">
                                                    @php
                                                        $uKeys = array_map(fn($s) => 'store-' . $s['id'], $unclusteredStores);
                                                        $isAllUSelected = count($uKeys) > 0 && count(array_intersect($uKeys, $selectedStoreIds)) === count($uKeys);
                                                    @endphp
                                                    <input type="checkbox" 
                                                           wire:click="toggleSelectUnclusteredStores" 
                                                           {{ $isAllUSelected ? 'checked' : '' }} 
                                                           class="checkbox checkbox-xs checkbox-warning rounded-xs" 
                                                           title="Pilih Semua / Batal Pilih Toko Unclustered" />
                                                </th>
                                                <th class="py-1 px-2 font-semibold">Toko Unclustered ({{ count($unclusteredStores) }})</th>
                                                <th class="py-1 px-2 text-right font-semibold pr-3">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($unclusteredStores as $st)
                                                @php
                                                    $pilarRaw = (string)($st['pilar'] ?? '');
                                                    $pilarName = '?';
                                                    $pilarClass = 'badge-ghost';
                                                    
                                                    if (str_contains($pilarRaw, '1.')) { $pilarName = 'RWO'; $pilarClass = 'badge-primary text-white'; }
                                                    elseif (str_contains($pilarRaw, '2.')) { $pilarName = 'PNR'; $pilarClass = 'badge-secondary text-white'; }
                                                    elseif (str_contains($pilarRaw, '3.')) { $pilarName = 'NGVO'; $pilarClass = 'badge-accent text-white'; }
                                                    elseif (str_contains($pilarRaw, '4.')) { $pilarName = 'GRO'; $pilarClass = 'badge-info text-white'; }
                                                @endphp
                                                <tr wire:key="unclustered-row-{{ $st['id'] }}" class="hover:bg-base-200/50 transition-colors group">
                                                    <td class="w-8 text-center font-mono opacity-70">
                                                        <input type="checkbox" wire:model.live="selectedStoreIds" value="store-{{ $st['id'] }}" class="checkbox checkbox-xs checkbox-warning rounded-xs" />
                                                    </td>
                                                    <td ondblclick="window.focusManagementMapOnStore('{{ $st['latitude'] ?? 0 }}', '{{ $st['longitude'] ?? 0 }}', {{ $st['id'] }})" class="cursor-pointer w-full" title="Klik ganda untuk fokus di peta">
                                                        <div class="flex items-center gap-2">
                                                            <div class="font-bold">{{ $st['customer_name'] }}</div>
                                                            <button type="button" onclick="window.focusManagementMapOnStore('{{ $st['latitude'] ?? 0 }}', '{{ $st['longitude'] ?? 0 }}', {{ $st['id'] }})" class="btn btn-ghost btn-xs btn-circle text-info opacity-50 hover:opacity-100" title="Fokus di Peta">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                                            </button>
                                                        </div>
                                                        <div class="opacity-60">{{ $st['customer_code_prc'] }} &bull; {{ $st['kelurahan'] ?? '-' }}</div>
                                                    </td>
                                                    <td class="text-right whitespace-nowrap">
                                                        <div class="flex items-center justify-end gap-1 relative z-10">
                                                            <span class="badge badge-xs {{ $pilarClass }} border-none px-2 py-2 font-bold shadow-sm">{{ $pilarName }}</span>
                                                            {{-- Masukkan ke Cluster Existing --}}
                                                            <button type="button" wire:click="openAssignUnclusteredModal({{ $st['id'] }}, '{{ addslashes($st['customer_name']) }}')" onclick="event.stopPropagation();" class="btn btn-ghost btn-xs btn-circle text-warning hover:bg-warning/10 relative z-10" title="Masukkan Toko Ini ke Cluster Existing">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                                            </button>
                                                            {{-- Buat Cluster Baru dengan Toko Ini --}}
                                                            <button type="button" wire:click="createClusterFromUnclustered({{ $st['id'] }})" onclick="event.stopPropagation();" class="btn btn-ghost btn-xs btn-circle text-success hover:bg-success/10 relative z-10" title="Buat Cluster Baru dengan Toko Ini">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Floating Bulk Action Bar --}}
            @if(count($selectedStoreIds) > 0)
                <div class="p-2.5 bg-neutral text-neutral-content border-t border-neutral-700 shadow-2xl flex items-center justify-between gap-2 z-40 animate-fade-in">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-primary font-bold text-[0.7rem] px-2 py-0.5">{{ count($selectedStoreIds) }} Toko Terpilih</span>
                        <button type="button" wire:click="clearSelectedStores" class="btn btn-ghost btn-xs text-neutral-content/70 hover:text-white underline text-[0.65rem] p-0 h-auto min-h-0">Batal</button>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="openBulkMoveModal" class="btn btn-xs btn-warning text-white font-bold gap-1 shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            Pindahkan
                        </button>
                        <button type="button" wire:click="openConfirmBulkDeleteModal" class="btn btn-xs btn-error text-white font-bold gap-1 shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            Hapus
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal View Cluster Detail --}}
    @if($isViewModalOpen)
    <div class="modal modal-open z-[999]">
        <div class="modal-box w-11/12 max-w-5xl rounded-2xl relative bg-white text-gray-800 shadow-2xl border border-gray-100" data-theme="light">
            <button wire:click="closeViewModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-lg mb-2">Detail Cluster: <span class="text-primary">{{ $selectedCluster?->name }}</span></h3>
            <p class="text-xs text-base-content/70 mb-4">Team: {{ $selectedCluster?->team_sales }} | Center: {{ $selectedCluster?->center_store_id }}</p>
            
            <div class="overflow-x-auto bg-base-200 rounded-xl max-h-[60vh] sidebar-scroll border border-base-content/10">
                <table class="table table-sm table-pin-rows">
                    <thead class="bg-base-300 text-base-content shadow-sm">
                        <tr>
                            <th class="w-12 text-center">Rute Ke-</th>
                            <th class="w-24">Kode Toko</th>
                            <th>Nama Toko</th>
                            <th>Jarak (Km)</th>
                            <th>Durasi (Menit)</th>
                            <th class="text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($selectedClusterItems as $item)
                            <tr class="hover:bg-base-100 transition-colors">
                                <td class="font-bold text-center text-primary">
                                    <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center mx-auto text-xs">
                                        {{ $item['route_order'] }}
                                    </div>
                                </td>
                                <td>{{ $item['toko_id'] }}</td>
                                <td class="font-semibold">{{ $item['toko_name'] }}</td>
                                <td>{{ number_format($item['distance_from_prev_km'], 2) }}</td>
                                <td>{{ number_format($item['duration_from_prev_min'], 1) }}</td>
                                <td>
                                    <button type="button" wire:click="openConfirmDeleteStoreModal({{ $item['item_id'] }})" class="btn btn-xs btn-error btn-circle text-white mx-auto flex" title="Keluarkan Toko dari Cluster">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Data toko tidak ditemukan di dalam cluster ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeViewModal" class="btn btn-neutral">Tutup</button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeViewModal"></div>
    </div>
    @endif

    {{-- Modal 1: Add to JKS Team Elite --}}
    @if($isJksModalOpen)
    <div class="modal modal-open z-[999]">
        <div class="modal-box max-w-md rounded-2xl relative bg-white text-gray-800 shadow-2xl border border-gray-100" data-theme="light">
            <button wire:click="closeJksModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-base mb-1 flex items-center gap-2 text-success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Tambah Cluster {{ $mSummary[$jksClusterId]['seq'] ?? $jksClusterId }} ke JKS Team Elite
            </h3>
            <p class="text-xs text-gray-500 mb-4">Pilih tanggal kunjungan untuk mendaftarkan semua toko dalam cluster ini ke plan JKS Team Elite.</p>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text text-xs font-semibold text-gray-700">Tanggal Plan Kunjungan:</span></label>
                <input type="date" wire:model="jksTanggal" class="input input-bordered input-sm w-full font-medium bg-white text-gray-800" />
                @error('jksTanggal') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div class="modal-action">
                <button wire:click="closeJksModal" class="btn btn-sm btn-ghost">Batal</button>
                <button wire:click="saveClusterToJks" wire:loading.attr="disabled" class="btn btn-sm btn-success text-white font-bold gap-1">
                    <span wire:loading wire:target="saveClusterToJks" class="loading loading-spinner loading-xs"></span>
                    <span>Simpan ke JKS Team Elite</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeJksModal"></div>
    </div>
    @endif

    {{-- Modal 2: Merge / Join Cluster --}}
    @if($isMergeModalOpen)
    <div class="modal modal-open z-[999]">
        <div class="modal-box max-w-md rounded-2xl relative bg-white text-gray-800 shadow-2xl border border-gray-100" data-theme="light">
            <button wire:click="closeMergeModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-base mb-1 flex items-center gap-2 text-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m-3-13.5L18 7.5m0 0L13.5 12M18 7.5H4.5" /></svg>
                Gabungkan Cluster {{ $mSummary[$sourceClusterId]['seq'] ?? $sourceClusterId }}
            </h3>
            <p class="text-xs text-gray-500 mb-4">Pilih cluster tujuan untuk memindahkan seluruh toko dari Cluster {{ $mSummary[$sourceClusterId]['seq'] ?? $sourceClusterId }}.</p>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text text-xs font-semibold text-gray-700">Cluster Tujuan:</span></label>
                <select wire:model.live="targetClusterId" class="select select-bordered select-sm w-full text-xs font-semibold bg-white text-gray-800">
                    <option value="">-- Pilih Cluster Tujuan --</option>
                    @if(isset($mSummary))
                        @foreach($mSummary as $optId => $optData)
                            @if($optId != $sourceClusterId)
                                <option value="{{ $optId }}">Cluster {{ $optData['seq'] }}{{ $optData['kec_str'] }} ({{ $optData['count'] }} Toko)</option>
                            @endif
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeMergeModal" class="btn btn-sm btn-ghost">Batal</button>
                <button wire:click="mergeCluster" wire:loading.attr="disabled" class="btn btn-sm btn-info text-white font-bold gap-1" {{ empty($targetClusterId) ? 'disabled' : '' }}>
                    <span wire:loading wire:target="mergeCluster" class="loading loading-spinner loading-xs"></span>
                    <span>Gabungkan Cluster</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeMergeModal"></div>
    </div>
    @endif

    {{-- Modal 3: Move Single Store --}}
    @if($isMoveStoreModalOpen)
    <div class="modal modal-open z-[999]">
        <div class="modal-box max-w-md rounded-2xl relative bg-white text-gray-800 shadow-2xl border border-gray-100" data-theme="light">
            <button wire:click="closeMoveStoreModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-base mb-1 flex items-center gap-2 text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                Pindahkan Toko
            </h3>
            <p class="text-xs text-gray-500 mb-3">Toko: <span class="font-bold text-gray-900">{{ $movingStoreName }}</span></p>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text text-xs font-semibold text-gray-700">Pindah ke Cluster:</span></label>
                <select wire:model.live="targetClusterForStore" class="select select-bordered select-sm w-full text-xs font-semibold bg-white text-gray-800">
                    <option value="">-- Pilih Cluster Tujuan --</option>
                    @if(isset($mSummary))
                        @foreach($mSummary as $optId => $optData)
                            <option value="{{ $optId }}">Cluster {{ $optData['seq'] }}{{ $optData['kec_str'] }} ({{ $optData['count'] }} Toko)</option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div class="modal-action">
                <button wire:click="closeMoveStoreModal" class="btn btn-sm btn-ghost">Batal</button>
                <button wire:click="moveStoreToCluster" class="btn btn-sm btn-warning text-white font-bold" {{ empty($targetClusterForStore) ? 'disabled' : '' }}>
                    <span wire:loading wire:target="moveStoreToCluster" class="loading loading-spinner loading-xs mr-1"></span>
                    <span>Pindahkan Toko</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeMoveStoreModal"></div>
    </div>
    @endif

    {{-- MODAL 4: CONFIRM DELETE STORE FROM CLUSTER --}}
    @if($isConfirmDeleteStoreOpen)
    <div class="modal modal-open z-[9999]">
        <div class="modal-box max-w-sm rounded-2xl p-5 text-center shadow-xl border border-gray-100 bg-white text-gray-800" data-theme="light">
            <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </div>
            <h3 class="font-bold text-base text-gray-900">Keluarkan Toko?</h3>
            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                Toko ini akan dikeluarkan dari cluster dan statusnya kembali menjadi <span class="font-bold text-amber-600">Unclustered</span>.
            </p>
            <div class="modal-action justify-center gap-2 mt-5">
                <button type="button" wire:click="closeConfirmDeleteStoreModal" class="btn btn-xs sm:btn-sm btn-ghost font-bold">Batal</button>
                <button type="button" wire:click="removeStoreFromCluster" wire:loading.attr="disabled" class="btn btn-xs sm:btn-sm btn-error text-white font-bold px-4 gap-1">
                    <span wire:loading wire:target="removeStoreFromCluster" class="loading loading-spinner loading-xs"></span>
                    <span>Keluarkan</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeConfirmDeleteStoreModal"></div>
    </div>
    @endif

    {{-- MODAL 5: CONFIRM DELETE ALL CLUSTERS --}}
    @if($isConfirmDeleteAllClustersOpen)
    <div class="modal modal-open z-[9999]">
        <div class="modal-box max-w-sm rounded-2xl p-5 text-center shadow-xl border border-gray-100 bg-white text-gray-800" data-theme="light">
            <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            </div>
            <h3 class="font-bold text-base text-gray-900">Hapus Semua Cluster?</h3>
            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                Anda yakin ingin menghapus <span class="font-bold text-red-600">SEMUA cluster</span> untuk tim sales ini? Seluruh toko akan kembali menjadi Unclustered.
            </p>
            <div class="modal-action justify-center gap-2 mt-5">
                <button type="button" wire:click="closeConfirmDeleteAllClustersModal" class="btn btn-xs sm:btn-sm btn-ghost font-bold">Batal</button>
                <button type="button" wire:click="deleteAllClusters" wire:loading.attr="disabled" class="btn btn-xs sm:btn-sm btn-error text-white font-bold px-4 gap-1">
                    <span wire:loading wire:target="deleteAllClusters" class="loading loading-spinner loading-xs"></span>
                    <span>Ya, Hapus Semua</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeConfirmDeleteAllClustersModal"></div>
    </div>
    @endif

    {{-- MODAL 6: CONFIRM DELETE SINGLE CLUSTER --}}
    @if($isConfirmDeleteClusterOpen)
    <div class="modal modal-open z-[9999]">
        <div class="modal-box max-w-sm rounded-2xl p-5 text-center shadow-xl border border-gray-100 bg-white text-gray-800" data-theme="light">
            <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </div>
            <h3 class="font-bold text-base text-gray-900">Hapus Cluster ini?</h3>
            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                Seluruh toko di dalam cluster ini akan dikeluarkan dan statusnya kembali menjadi <span class="font-bold text-amber-600">Unclustered</span>.
            </p>
            <div class="modal-action justify-center gap-2 mt-5">
                <button type="button" wire:click="closeConfirmDeleteClusterModal" class="btn btn-xs sm:btn-sm btn-ghost font-bold">Batal</button>
                <button type="button" wire:click="deleteCluster" wire:loading.attr="disabled" class="btn btn-xs sm:btn-sm btn-error text-white font-bold px-4 gap-1">
                    <span wire:loading wire:target="deleteCluster" class="loading loading-spinner loading-xs"></span>
                    <span>Ya, Hapus Cluster</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeConfirmDeleteClusterModal"></div>
    </div>
    @endif

    {{-- MODAL 7: BULK MOVE STORES --}}
    @if($isBulkMoveModalOpen)
    <div class="modal modal-open z-[9999]">
        <div class="modal-box max-w-md rounded-2xl relative bg-white text-gray-800 shadow-2xl border border-gray-100" data-theme="light">
            <button type="button" wire:click="closeBulkMoveModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-base mb-1 flex items-center gap-2 text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                Pindahkan {{ count($selectedStoreIds) }} Toko Terpilih
            </h3>
            <p class="text-xs text-gray-500 mb-4">Pilih cluster tujuan untuk memindahkan {{ count($selectedStoreIds) }} toko yang telah Anda ceklis.</p>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text text-xs font-semibold text-gray-700">Cluster Tujuan:</span></label>
                <select wire:model.live="bulkTargetClusterId" class="select select-bordered select-sm w-full text-xs font-semibold bg-white text-gray-800">
                    <option value="">-- Pilih Cluster Tujuan --</option>
                    @if(isset($mSummary))
                        @foreach($mSummary as $optId => $optData)
                            <option value="{{ $optId }}">Cluster {{ $optData['seq'] }}{{ $optData['kec_str'] }} ({{ $optData['count'] }} Toko)</option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div class="modal-action">
                <button type="button" wire:click="closeBulkMoveModal" class="btn btn-sm btn-ghost font-bold">Batal</button>
                <button type="button" wire:click="bulkMoveStores" wire:loading.attr="disabled" class="btn btn-sm btn-warning text-white font-bold gap-1" {{ empty($bulkTargetClusterId) ? 'disabled' : '' }}>
                    <span wire:loading wire:target="bulkMoveStores" class="loading loading-spinner loading-xs"></span>
                    <span>Pindahkan Toko</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeBulkMoveModal"></div>
    </div>
    @endif

    {{-- MODAL 8: BULK DELETE STORES --}}
    @if($isConfirmBulkDeleteOpen)
    <div class="modal modal-open z-[9999]">
        <div class="modal-box max-w-sm rounded-2xl p-5 text-center shadow-xl border border-gray-100 bg-white text-gray-800" data-theme="light">
            <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </div>
            <h3 class="font-bold text-base text-gray-900">Keluarkan {{ count($selectedStoreIds) }} Toko Terpilih?</h3>
            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                Toko-toko yang terpilih akan dikeluarkan dari clusternya masing-masing dan statusnya kembali menjadi <span class="font-bold text-amber-600">Unclustered</span>.
            </p>
            <div class="modal-action justify-center gap-2 mt-5">
                <button type="button" wire:click="closeConfirmBulkDeleteModal" class="btn btn-xs sm:btn-sm btn-ghost font-bold">Batal</button>
                <button type="button" wire:click="bulkDeleteStores" wire:loading.attr="disabled" class="btn btn-xs sm:btn-sm btn-error text-white font-bold px-4 gap-1">
                    <span wire:loading wire:target="bulkDeleteStores" class="loading loading-spinner loading-xs"></span>
                    <span>Ya, Keluarkan {{ count($selectedStoreIds) }} Toko</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeConfirmBulkDeleteModal"></div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

@script
<script>
    let mmap;
    let mmarkers = [];
    let mResizeObserver = null;

    window.__getManagementTabWire = function() {
        const container = document.getElementById('management-map');
        if (container) {
            const root = container.closest('[wire\\:id]');
            if (root && window.Livewire) {
                return window.Livewire.find(root.getAttribute('wire:id'));
            }
        }
        return null;
    };

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

    function initManagementMap() {
        const container = document.getElementById('management-map');
        if (!container) return;

        if (mmap) {
            if (!container.contains(mmap.getCanvas()) && !document.body.contains(mmap.getCanvas())) {
                try { mmap.remove(); } catch(e) {}
                mmap = null;
            } else {
                mmap.resize();
                return;
            }
        }

        mmap = new maplibregl.Map({
            container: 'management-map',
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

        mmap.addControl(new maplibregl.NavigationControl());

        if (mResizeObserver) mResizeObserver.disconnect();
        mResizeObserver = new ResizeObserver(() => {
            if (mmap) mmap.resize();
        });
        mResizeObserver.observe(container);
    }

    function clearManagementMap() {
        if (mmarkers.length > 0) {
            mmarkers.forEach(m => m.remove());
            mmarkers = [];
        }
        if (mmap) {
            if (mmap.getLayer('cluster-hulls-fill')) mmap.removeLayer('cluster-hulls-fill');
            if (mmap.getLayer('cluster-hulls-line')) mmap.removeLayer('cluster-hulls-line');
            if (mmap.getSource('cluster-hulls')) mmap.removeSource('cluster-hulls');

            if (mmap.getLayer('cluster-routes-line')) mmap.removeLayer('cluster-routes-line');
            if (mmap.getSource('cluster-routes')) mmap.removeSource('cluster-routes');
        }
    }

    function isValidCoord(lat, lng) {
        if (lat === null || lng === null || isNaN(lat) || isNaN(lng)) return false;
        if (Math.abs(lat) < 0.0001 && Math.abs(lng) < 0.0001) return false;
        if (lat === 0 || lng === 0) return false;
        if (lat < -15 || lat > 15 || lng < 90 || lng > 145) return false;
        return true;
    }

    window.managementHiddenClusters = [];

    window.updateMapClusterVisibility = function(hiddenClusters) {
        window.managementHiddenClusters = hiddenClusters || [];
        const pilarVal = document.querySelector('select[x-model="mapFilterPilar"]')?.value || 'all';
        window.filterManagementMapMarkers(pilarVal, 'all');
    };

    window.filterManagementMapMarkers = function(pilarFilter, clusterFilter) {
        if (!mmarkers || mmarkers.length === 0) return;

        const hidden = window.managementHiddenClusters || [];

        mmarkers.forEach(marker => {
            const store = marker.storeData;
            if (!store) return;

            let matchPilar = true;
            if (pilarFilter !== 'all') {
                const pilarStr = (store.pilar || '').toString();
                matchPilar = pilarStr.includes(pilarFilter + '.');
            }

            let matchCluster = true;
            const storeCId = store.cluster_id || 0;
            if (hidden.includes(storeCId)) {
                matchCluster = false;
            } else if (clusterFilter === 'clustered') {
                matchCluster = (store.cluster_id > 0);
            } else if (clusterFilter === 'unclustered') {
                matchCluster = (!store.cluster_id || store.cluster_id == 0);
            } else if (clusterFilter !== 'all') {
                matchCluster = (store.cluster_id == clusterFilter);
            }

            const el = marker.getElement();
            if (el) {
                el.style.display = (matchPilar && matchCluster) ? 'block' : 'none';
            }
        });

        if (mmap && mmap.getLayer('cluster-hulls-fill')) {
            const hiddenIds = hidden.map(id => parseInt(id));
            if (hiddenIds.length > 0) {
                const filter = ['!', ['in', ['get', 'cluster_id'], ['literal', hiddenIds]]];
                mmap.setFilter('cluster-hulls-fill', filter);
                mmap.setFilter('cluster-hulls-line', filter);
                if (mmap.getLayer('cluster-routes-line')) {
                    mmap.setFilter('cluster-routes-line', filter);
                }
            } else {
                mmap.setFilter('cluster-hulls-fill', null);
                mmap.setFilter('cluster-hulls-line', null);
                if (mmap.getLayer('cluster-routes-line')) {
                    mmap.setFilter('cluster-routes-line', null);
                }
            }
        }
    };

    function drawManagementClusters(stores, isInitialFilter = false) {
        if (!mmap) return;
        clearManagementMap();
        if (!stores || stores.length === 0) return;

        let bounds = new maplibregl.LngLatBounds();
        let hasPoints = false;

        let clusterPoints = {};

        stores.forEach((store) => {
            const lat = parseFloat(store.latitude);
            const lng = parseFloat(store.longitude);

            if (isValidCoord(lat, lng)) {
                let point = [lng, lat];
                bounds.extend(point);
                hasPoints = true;

                const clusterId = store.cluster_id || 0;
                const markerColor = getClusterColor(clusterId);

                // For Convex Hull and Routing
                if (clusterId > 0) {
                    if (!clusterPoints[clusterId]) {
                        clusterPoints[clusterId] = { points: [], color: markerColor };
                    }
                    clusterPoints[clusterId].points.push(point);
                }

                let pilarName = '?';
                let pilarClass = 'badge-ghost';
                let pilarBorderColor = 'white';
                const pilarStr = (store.pilar || '').toString();
                if(pilarStr.includes('1.')) { pilarName = 'RWO'; pilarClass = 'badge-primary text-white'; pilarBorderColor = '#fbbf24'; } // Bright yellow for RWO
                else if(pilarStr.includes('2.')) { pilarName = 'PNR'; pilarClass = 'badge-secondary text-white'; } 
                else if(pilarStr.includes('3.')) { pilarName = 'NGVO'; pilarClass = 'badge-accent text-white'; } 
                else if(pilarStr.includes('4.')) { pilarName = 'GRO'; pilarClass = 'badge-info text-white'; }

                const el = document.createElement('div');
                el.className = 'cluster-marker';
                el.style.backgroundColor = markerColor;
                el.style.borderColor = pilarBorderColor;
                el.style.borderWidth = '2px';
                el.style.zIndex = clusterId == 0 ? '1' : '10';

                if (clusterId > 0) {
                    el.innerHTML = `<span style="font-size: 0.75rem; color: white; font-weight: 900; text-shadow: 1px 1px 2px rgba(0,0,0,0.8); display: block; line-height: 1; margin-top: 1px;">${store.cluster_seq || clusterId}</span>`;
                }

                let clusterBadge = '';
                if (store.cluster_id > 0) {
                    clusterBadge = `<span class="badge badge-xs badge-neutral font-bold shrink-0">Cluster ${store.cluster_seq || store.cluster_id}</span>`;
                } else {
                    clusterBadge = `<span class="badge badge-xs badge-warning font-bold shrink-0">Unclustered</span>`;
                }

                const distDisplay = store.distributor_name || store.distributor_code || '-';

                let featureHtml = '';
                if (store.cluster_id > 0 && store.item_id) {
                    const safeName = escHtml(store.customer_name).replace(/&#039;/g, "\\&#039;").replace(/'/g, "\\'");
                    featureHtml = `
                        <div class="mt-2 pt-2 border-t border-base-200 flex items-center justify-between gap-1.5">
                            <button type="button" onclick="document.querySelector('.maplibregl-popup-close-button')?.click(); window.__getManagementTabWire()?.openMoveStoreModal(${store.item_id}, '${safeName}')" class="btn btn-xs btn-warning text-white flex-1 font-bold">
                                ➔ Pindahkan Toko
                            </button>
                            <button type="button" onclick="document.querySelector('.maplibregl-popup-close-button')?.click(); window.__getManagementTabWire()?.openConfirmDeleteStoreModal(${store.item_id})" class="btn btn-xs btn-outline btn-error font-bold px-2" title="Keluarkan Toko">
                                🗑️
                            </button>
                        </div>
                    `;
                } else {
                    const safeName = escHtml(store.customer_name).replace(/&#039;/g, "\\&#039;").replace(/'/g, "\\'");
                    featureHtml = `
                        <div class="mt-2 pt-2 border-t border-base-200">
                            <button type="button" onclick="document.querySelector('.maplibregl-popup-close-button')?.click(); window.__getManagementTabWire()?.openAddUnclusteredStoreModal(${store.id}, '${safeName}')" class="btn btn-xs btn-primary text-white w-full font-bold">
                                + Masukkan ke Cluster
                            </button>
                        </div>
                    `;
                }

                const popupContent = `
                    <div class="text-xs text-gray-800 bg-white" data-theme="light">
                        {{-- 1. HEADER: Nama Toko & Badge Pilar --}}
                        <div class="font-bold border-b border-gray-200 pb-2 flex justify-between items-start gap-2 pr-6">
                            <span class="leading-tight text-blue-600 font-extrabold text-sm">${escHtml(store.customer_name)}</span>
                            <span class="badge badge-xs ${pilarClass} border-none font-bold p-2 shrink-0">${pilarName}</span>
                        </div>

                        {{-- 2. KOTAK INFORMASI --}}
                        <div class="space-y-1 text-[0.7rem] text-gray-700 mt-2">
                            <div class="flex items-center justify-between gap-2 bg-gray-100 p-1.5 rounded-md">
                                <span class="text-gray-500 shrink-0 font-medium">Cluster:</span>
                                <div class="shrink-0">${clusterBadge}</div>
                            </div>
                            <div class="flex items-center justify-between gap-2 bg-gray-100 p-1.5 rounded-md">
                                <span class="text-gray-500 shrink-0 font-medium">Kode Toko:</span>
                                <span class="font-mono font-bold text-gray-900 shrink-0">${escHtml(store.customer_code_prc || '-')}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2 bg-gray-100 p-1.5 rounded-md">
                                <span class="text-gray-500 shrink-0 font-medium">Dist Name:</span>
                                <span class="font-semibold text-gray-900 text-right truncate max-w-[170px]" title="${escHtml(distDisplay)}">${escHtml(distDisplay)}</span>
                            </div>
                            ${store.customer_address ? `
                                <div class="bg-gray-100 p-1.5 rounded-md leading-tight">
                                    <span class="text-gray-500 block text-[0.65rem] font-medium mb-0.5">Alamat:</span>
                                    <span class="break-words text-gray-900 font-medium">${escHtml(store.customer_address)}</span>
                                </div>
                            ` : ''}
                            <div class="flex items-center justify-between gap-2 bg-gray-100 p-1.5 rounded-md">
                                <span class="text-gray-500 shrink-0 font-medium">Wilayah:</span>
                                <span class="font-semibold text-gray-900 text-right truncate max-w-[170px]" title="${store.kecamatan ? escHtml(store.kecamatan) + (store.kelurahan ? ' • ' + escHtml(store.kelurahan) : '') : '-'}">${store.kecamatan ? escHtml(store.kecamatan) + (store.kelurahan ? ' • ' + escHtml(store.kelurahan) : '') : '-'}</span>
                            </div>
                        </div>

                        {{-- 3. FITUR / AKSI --}}
                        ${featureHtml}
                    </div>
                `;

                let popup = new maplibregl.Popup({ offset: 10, closeButton: true }).setHTML(popupContent);
                let marker = new maplibregl.Marker({ element: el })
                    .setLngLat(point)
                    .setPopup(popup)
                    .addTo(mmap);
                
                marker.storeId = store.id;
                marker.storeData = store;
                mmarkers.push(marker);
            }
        });

        // Draw Hulls and Routes
        if (typeof turf !== 'undefined') {
            let hullFeatures = [];
            let routeFeatures = [];

            for (const cId in clusterPoints) {
                const data = clusterPoints[cId];
                const pts = data.points;
                
                // Hull requires at least 3 points
                if (pts.length >= 3) {
                    const featureColl = turf.featureCollection(pts.map(p => turf.point(p)));
                    let hull = turf.convex(featureColl);
                    if (hull) {
                        hull.properties = { color: data.color, cluster_id: parseInt(cId) };
                        hullFeatures.push(hull);
                    }
                }
                
                // Route requires at least 2 points
                if (pts.length >= 2) {
                    routeFeatures.push(turf.lineString(pts, { color: data.color, cluster_id: parseInt(cId) }));
                }
            }

            if (hullFeatures.length > 0) {
                mmap.addSource('cluster-hulls', { type: 'geojson', data: turf.featureCollection(hullFeatures) });
                mmap.addLayer({
                    id: 'cluster-hulls-fill',
                    type: 'fill',
                    source: 'cluster-hulls',
                    paint: { 'fill-color': ['get', 'color'], 'fill-opacity': 0.2 }
                }); 
                
                mmap.addLayer({
                    id: 'cluster-hulls-line',
                    type: 'line',
                    source: 'cluster-hulls',
                    paint: { 'line-color': ['get', 'color'], 'line-width': 2 }
                });
            }

            if (routeFeatures.length > 0) {
                mmap.addSource('cluster-routes', { type: 'geojson', data: turf.featureCollection(routeFeatures) });
                mmap.addLayer({
                    id: 'cluster-routes-line',
                    type: 'line',
                    source: 'cluster-routes',
                    paint: { 'line-color': ['get', 'color'], 'line-width': 1.5, 'line-dasharray': [3, 3] }
                });
            }
        }

        if (hasPoints && isInitialFilter) {
            mmap.fitBounds(bounds, { padding: 50, duration: 500 });
        }

        // Always apply active map filter and cluster visibility unconditionally
        const pilarVal = document.querySelector('select[x-model="mapFilterPilar"]')?.value || 'all';
        window.filterManagementMapMarkers(pilarVal, 'all');
    }

    window.focusManagementMapOnStore = function(lat, lng, storeId) {
        if (!mmap) return;
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (!isValidCoord(lat, lng)) {
            alert('Koordinat toko ini tidak valid atau bernilai 0.');
            return;
        }
        
        mmap.flyTo({
            center: [lng, lat],
            zoom: 14,
            essential: true,
            duration: 800
        });

        document.querySelectorAll('.cluster-marker').forEach(el => el.classList.remove('marker-focused'));

        if (mmarkers && mmarkers.length > 0) {
            mmarkers.forEach(m => {
                if (m.getPopup() && m.getPopup().isOpen()) m.togglePopup();
            });
            const targetMarker = mmarkers.find(m => m.storeId === storeId);
            if (targetMarker) {
                const el = targetMarker.getElement();
                if (el) {
                    el.classList.add('marker-focused');
                    setTimeout(() => el.classList.remove('marker-focused'), 3600);
                }
                if (targetMarker.getPopup() && !targetMarker.getPopup().isOpen()) targetMarker.togglePopup();
            }
        }
    };

    setTimeout(() => {
        initManagementMap();
    }, 100);

    Livewire.on('management-clusters-generated', (data) => {
        const stores = data[0]?.stores || data.stores || [];
        const isInitial = data[0]?.isInitialFilter ?? data.isInitialFilter ?? false;

        if (!mmap) initManagementMap();
        setTimeout(() => {
            if (mmap && isInitial) mmap.resize();
            if (mmap && mmap.isStyleLoaded()) {
                drawManagementClusters(stores, isInitial);
            } else if (mmap) {
                mmap.once('load', () => drawManagementClusters(stores, isInitial));
            }
        }, 50);
    });
</script>
@endscript
