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
        {{-- Total Toko --}}
        <div class="bg-base-100 border border-base-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.25a.75.75 0 0 1-.75-.75V4.5a.75.75 0 0 1 .75-.75h19.5a.75.75 0 0 1 .75.75v15.75a.75.75 0 0 1-.75.75H13.5Z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.65rem] font-medium text-base-content/60 truncate">Total Ter-cluster</div>
                <div class="text-sm font-extrabold text-base-content leading-tight">
                    {{ number_format($totalStores) }} <span class="text-[0.65rem] font-normal text-base-content/50">/ {{ number_format($paretoTotalStores) }}</span>
                </div>
            </div>
        </div>

        {{-- Total Cluster --}}
        <div class="bg-base-100 border border-base-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-neutral/10 text-neutral flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.82c-.317-.159-.69-.159-1.006 0L3.622 6.257C3.24 6.447 3 6.837 3 7.263v12.417c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.65rem] font-medium text-base-content/60 truncate">Total Cluster</div>
                <div class="text-sm font-extrabold text-base-content leading-tight">{{ number_format($totalClusters) }}</div>
            </div>
        </div>

        {{-- Pilar 1 RWO --}}
        @php
            $p1Pareto = $paretoPilarCounts['1'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                P1
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.65rem] font-bold text-primary truncate">Pilar 1 - RWO</div>
                <div class="text-sm font-extrabold text-base-content leading-tight">
                    {{ number_format($pilarCounts['1']) }} <span class="text-[0.65rem] font-normal text-base-content/50">/ {{ number_format($p1Pareto) }}</span>
                </div>
            </div>
        </div>

        {{-- Pilar 2 PNR --}}
        @php
            $p2Pareto = $paretoPilarCounts['2'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-secondary text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                P2
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.65rem] font-bold text-secondary truncate">Pilar 2 - PNR</div>
                <div class="text-sm font-extrabold text-base-content leading-tight">
                    {{ number_format($pilarCounts['2']) }} <span class="text-[0.65rem] font-normal text-base-content/50">/ {{ number_format($p2Pareto) }}</span>
                </div>
            </div>
        </div>

        {{-- Pilar 3 NGVO --}}
        @php
            $p3Pareto = $paretoPilarCounts['3'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-accent text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                P3
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.65rem] font-bold text-accent truncate">Pilar 3 - NGVO</div>
                <div class="text-sm font-extrabold text-base-content leading-tight">
                    {{ number_format($pilarCounts['3']) }} <span class="text-[0.65rem] font-normal text-base-content/50">/ {{ number_format($p3Pareto) }}</span>
                </div>
            </div>
        </div>

        {{-- Pilar 4 GRO --}}
        @php
            $p4Pareto = $paretoPilarCounts['4'] ?? 0;
        @endphp
        <div class="bg-base-100 border border-base-200 rounded-xl p-3 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-info text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                P4
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[0.65rem] font-bold text-info truncate">Pilar 4 - GRO</div>
                <div class="text-sm font-extrabold text-base-content leading-tight">
                    {{ number_format($pilarCounts['4']) }} <span class="text-[0.65rem] font-normal text-base-content/50">/ {{ number_format($p4Pareto) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 flex-1 min-h-0">
        {{-- Map Container --}}
        <div class="w-full lg:w-2/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col relative z-0" wire:ignore>
            <div id="management-map" class="w-full h-[500px] lg:h-full z-0"></div>
            
            <div class="absolute bottom-4 right-4 bg-base-100/90 backdrop-blur p-2 rounded-lg border border-base-300 shadow-sm z-[400] text-xs">
                <div class="flex flex-col gap-1">
                    <div class="text-[0.6rem] text-base-content/50 mt-1">
                        Titik-titik toko dari cluster yang tersimpan
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Sidebar --}}
        <div class="w-full lg:w-1/3 bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden flex flex-col">
            <div class="p-3 border-b border-base-300 bg-base-200/50 flex justify-between items-center z-10 shadow-sm">
                <div>
                    <h3 class="font-bold text-sm">Daftar Cluster</h3>
                    <p class="text-[0.65rem] text-base-content/60">{{ count($managementClusterStores) }} Total Toko</p>
                </div>
                @if(count($managementClusterStores) > 0)
                    <button type="button" 
                            wire:confirm="Anda yakin ingin menghapus SEMUA cluster untuk team ini?" 
                            wire:click="deleteAllClusters" 
                            class="btn btn-xs btn-error text-white gap-1 shadow-xs" 
                            title="Hapus Semua Cluster">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        Hapus Semua
                    </button>
                @endif
            </div>

            <div class="flex-1 overflow-auto p-2 bg-base-200/30">
                @php
                    $mSummary = [];
                    foreach($managementClusterStores as $s) {
                        $cId = $s['cluster_id'];
                        if(!isset($mSummary[$cId])) {
                            $mSummary[$cId] = ['count' => 0, 'stores' => [], 'kecamatan' => [], 'pilar' => ['1'=>0,'2'=>0,'3'=>0,'4'=>0]];
                        }
                        $mSummary[$cId]['count']++;
                        $mSummary[$cId]['stores'][] = $s;
                        if(!empty($s['kecamatan'])) {
                            $mSummary[$cId]['kecamatan'][] = $s['kecamatan'];
                        }
                        
                        $pilarRaw = (string)($s['pilar'] ?? '');
                        if (str_contains($pilarRaw, '1.')) $mSummary[$cId]['pilar']['1']++;
                        elseif (str_contains($pilarRaw, '2.')) $mSummary[$cId]['pilar']['2']++;
                        elseif (str_contains($pilarRaw, '3.')) $mSummary[$cId]['pilar']['3']++;
                        elseif (str_contains($pilarRaw, '4.')) $mSummary[$cId]['pilar']['4']++;
                    }

                    $seq = 1;
                    foreach($mSummary as $cId => &$data) {
                        $data['seq'] = $seq++;
                        $uniqueKec = array_values(array_unique($data['kecamatan']));
                        $totalKec = count($uniqueKec);
                        if ($totalKec > 2) {
                            $data['kec_str'] = ' (' . $uniqueKec[0] . ', ' . $uniqueKec[1] . ' +' . ($totalKec - 2) . ' Kec)';
                        } elseif ($totalKec > 0) {
                            $data['kec_str'] = ' (' . implode(', ', $uniqueKec) . ')';
                        } else {
                            $data['kec_str'] = '';
                        }
                        $data['kec_str_full'] = $totalKec > 0 ? ' (' . implode(', ', $uniqueKec) . ')' : '';
                    }
                    unset($data);
                @endphp

                @if(count($mSummary) === 0)
                    <div class="text-center py-8 text-base-content/50 text-sm">
                        @if(empty($managementSelectedTeam))
                            Silakan pilih Team terlebih dahulu.
                        @else
                            Belum ada cluster tersimpan untuk team ini.
                        @endif
                    </div>
                @else
                    <div class="join join-vertical w-full bg-base-100 shadow-sm">
                        @foreach($mSummary as $cId => $data)
                            @php
                                $hue = ($cId * 137.5) % 360;
                            @endphp
                            <div class="collapse collapse-arrow join-item border border-base-300">
                                <input type="checkbox" /> 
                                <div class="collapse-title p-2.5 pr-9 min-h-0 flex items-center gap-2 group">
                                    <div class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: hsl({{ $hue }}, 70%, 50%);"></div>
                                    <div class="flex-1 min-w-0 flex items-center gap-1 overflow-hidden">
                                        <div class="font-bold text-xs sm:text-sm leading-tight truncate">
                                            Cluster {{ $data['seq'] }}<span class="text-[0.7rem] font-normal opacity-70 ml-1">{{ $data['kec_str'] }}</span>
                                        </div>
                                        @if(!empty($data['kec_str_full']))
                                            <button type="button" 
                                                    class="btn btn-ghost btn-xs btn-circle text-base-content/50 hover:text-info relative z-10 shrink-0 p-0" 
                                                    onclick="event.stopPropagation(); alert('Daftar Lengkap Kecamatan Cluster {{ $data['seq'] }}:\n\n{{ addslashes(trim($data['kec_str_full'], ' ()')) }}');" 
                                                    title="Daftar Lengkap Kecamatan: {{ trim($data['kec_str_full'], ' ()') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="badge badge-xs sm:badge-sm badge-neutral shrink-0">
                                        {{ $data['count'] }} Toko
                                    </div>
                                    <div class="flex items-center gap-0.5 shrink-0 z-10 relative">
                                        {{-- Add to JKS Team Elite --}}
                                        <button type="button" wire:click="openJksModal({{ $cId }})" class="btn btn-xs btn-ghost text-success hover:bg-success/10 p-1" onclick="event.stopPropagation();" title="Tambah ke JKS Team Elite">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        </button>
                                        {{-- Gabung ke Cluster Lain --}}
                                        <button type="button" wire:click="openMergeModal({{ $cId }})" class="btn btn-xs btn-ghost text-info hover:bg-info/10 p-1" onclick="event.stopPropagation();" title="Gabungkan ke Cluster Lain">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m-3-13.5L18 7.5m0 0L13.5 12M18 7.5H4.5" /></svg>
                                        </button>
                                        {{-- Hapus Cluster --}}
                                        <button type="button" wire:confirm="Anda yakin ingin menghapus cluster ini?" wire:click="deleteCluster({{ $cId }})" class="btn btn-xs btn-ghost text-error hover:bg-error/10 p-1" onclick="event.stopPropagation();" title="Hapus Cluster">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
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
                                                        <td class="w-8 text-center font-mono opacity-50">{{ $loop->iteration }}</td>
                                                        <td ondblclick="window.focusManagementMapOnStore({{ $st['latitude'] ?? 0 }}, {{ $st['longitude'] ?? 0 }}, {{ $st['id'] }})" class="cursor-pointer" title="Klik ganda untuk fokus di peta">
                                                            <div class="flex items-center gap-2">
                                                                <div class="font-bold">{{ $st['customer_name'] }}</div>
                                                                <button type="button" onclick="window.focusManagementMapOnStore({{ $st['latitude'] ?? 0 }}, {{ $st['longitude'] ?? 0 }}, {{ $st['id'] }})" class="btn btn-ghost btn-xs btn-circle text-info opacity-50 hover:opacity-100" title="Fokus di Peta">
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
                                                                <button type="button" wire:confirm="Anda yakin ingin mengeluarkan toko ini dari cluster?" wire:click="removeStoreFromCluster({{ $st['item_id'] }})" onclick="event.stopPropagation();" class="btn btn-ghost btn-xs btn-circle text-error hover:bg-error/10 relative z-10" title="Keluarkan Toko dari Cluster">
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
                        <div class="collapse collapse-arrow border border-warning/40 bg-warning/5 rounded-xl shadow-xs">
                            <input type="checkbox" /> 
                            <div class="collapse-title p-2.5 pr-9 min-h-0 flex items-center gap-2 group">
                                <div class="w-3.5 h-3.5 rounded-full shrink-0 bg-gray-500"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-xs sm:text-sm leading-tight text-warning-content truncate">
                                        Toko Belum Ter-cluster
                                    </div>
                                </div>
                                <div class="badge badge-xs sm:badge-sm badge-warning font-bold shrink-0">
                                    {{ count($unclusteredStores) }} Toko
                                </div>
                            </div>
                            
                            <div class="collapse-content p-0 bg-base-100">
                                <div class="overflow-x-auto">
                                    <table class="table table-xs table-zebra w-full text-[0.7rem]">
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
                                                <tr class="hover:bg-base-200/50 transition-colors group">
                                                    <td class="w-8 text-center font-mono opacity-50">{{ $loop->iteration }}</td>
                                                    <td ondblclick="window.focusManagementMapOnStore({{ $st['latitude'] ?? 0 }}, {{ $st['longitude'] ?? 0 }}, {{ $st['id'] }})" class="cursor-pointer" title="Klik ganda untuk fokus di peta">
                                                        <div class="flex items-center gap-2">
                                                            <div class="font-bold">{{ $st['customer_name'] }}</div>
                                                            <button type="button" onclick="window.focusManagementMapOnStore({{ $st['latitude'] ?? 0 }}, {{ $st['longitude'] ?? 0 }}, {{ $st['id'] }})" class="btn btn-ghost btn-xs btn-circle text-info opacity-50 hover:opacity-100" title="Fokus di Peta">
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
        </div>
    </div>

    {{-- Modal View Cluster Detail --}}
    <div class="modal {{ $isViewModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-box w-11/12 max-w-5xl rounded-2xl relative">
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
                                    <button wire:confirm="Yakin ingin mengeluarkan toko ini dari cluster?" wire:click="removeStoreFromCluster({{ $item['item_id'] }})" class="btn btn-xs btn-error btn-circle text-white mx-auto flex" title="Keluarkan Toko">
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

    {{-- Modal 1: Add to JKS Team Elite --}}
    <div class="modal {{ $isJksModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-box max-w-md rounded-2xl relative">
            <button wire:click="closeJksModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-base mb-1 flex items-center gap-2 text-success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Tambah Cluster {{ $mSummary[$jksClusterId]['seq'] ?? $jksClusterId }} ke JKS Team Elite
            </h3>
            <p class="text-xs text-base-content/70 mb-4">Pilih tanggal kunjungan untuk mendaftarkan semua toko dalam cluster ini ke plan JKS Team Elite.</p>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text text-xs font-semibold">Tanggal Plan Kunjungan:</span></label>
                <input type="date" wire:model="jksTanggal" class="input input-bordered input-sm w-full font-medium" />
                @error('jksTanggal') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            
            <div class="modal-action">
                <button wire:click="closeJksModal" class="btn btn-sm btn-ghost">Batal</button>
                <button wire:click="saveClusterToJks" class="btn btn-sm btn-success text-white">
                    Simpan ke JKS Team Elite
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeJksModal"></div>
    </div>

    {{-- Modal 2: Merge / Join Cluster --}}
    <div class="modal {{ $isMergeModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-box max-w-md rounded-2xl relative">
            <button wire:click="closeMergeModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-base mb-1 flex items-center gap-2 text-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m-3-13.5L18 7.5m0 0L13.5 12M18 7.5H4.5" /></svg>
                Gabungkan Cluster {{ $mSummary[$sourceClusterId]['seq'] ?? $sourceClusterId }}
            </h3>
            <p class="text-xs text-base-content/70 mb-4">Pilih cluster tujuan untuk memindahkan seluruh toko dari Cluster {{ $mSummary[$sourceClusterId]['seq'] ?? $sourceClusterId }}.</p>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text text-xs font-semibold">Cluster Tujuan:</span></label>
                <select wire:model.live="targetClusterId" class="select select-bordered select-sm w-full text-xs font-semibold">
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
                <button wire:click="mergeCluster" class="btn btn-sm btn-info text-white" {{ empty($targetClusterId) ? 'disabled' : '' }}>
                    Gabungkan Cluster
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeMergeModal"></div>
    </div>

    {{-- Modal 3: Move Single Store --}}
    <div class="modal {{ $isMoveStoreModalOpen ? 'modal-open' : '' }} z-[999]">
        <div class="modal-box max-w-md rounded-2xl relative">
            <button wire:click="closeMoveStoreModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="font-bold text-base mb-1 flex items-center gap-2 text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                Pindahkan Toko
            </h3>
            <p class="text-xs text-base-content/70 mb-3">Toko: <span class="font-bold text-base-content">{{ $movingStoreName }}</span></p>
            
            <div class="form-control w-full mb-4">
                <label class="label"><span class="label-text text-xs font-semibold">Pindah ke Cluster:</span></label>
                <select wire:model.live="targetClusterForStore" class="select select-bordered select-sm w-full text-xs font-semibold">
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
                <button wire:click="moveStoreToCluster" class="btn btn-sm btn-warning text-white" {{ empty($targetClusterForStore) ? 'disabled' : '' }}>
                    Pindahkan Toko
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-base-content/30 backdrop-blur-sm" wire:click="closeMoveStoreModal"></div>
    </div>
</div>

@script
<script>
    let mmap;
    let mmarkers = [];
    let mResizeObserver = null;

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
        if (mmap) return;
        const container = document.getElementById('management-map');
        if (!container) return;

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
    }

    function isValidCoord(lat, lng) {
        if (lat === null || lng === null || isNaN(lat) || isNaN(lng)) return false;
        if (Math.abs(lat) < 0.0001 && Math.abs(lng) < 0.0001) return false;
        if (lat === 0 || lng === 0) return false;
        if (lat < -15 || lat > 15 || lng < 90 || lng > 145) return false;
        return true;
    }

    function drawManagementClusters(stores) {
        if (!mmap) return;
        clearManagementMap();
        if (!stores || stores.length === 0) return;

        let bounds = new maplibregl.LngLatBounds();
        let hasPoints = false;

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

                let pilarName = '?';
                let pilarClass = 'badge-ghost';
                const pilarStr = (store.pilar || '').toString();
                if(pilarStr.includes('1.')) { pilarName = 'RWO'; pilarClass = 'badge-primary text-white'; }
                else if(pilarStr.includes('2.')) { pilarName = 'PNR'; pilarClass = 'badge-secondary text-white'; }
                else if(pilarStr.includes('3.')) { pilarName = 'NGVO'; pilarClass = 'badge-accent text-white'; }
                else if(pilarStr.includes('4.')) { pilarName = 'GRO'; pilarClass = 'badge-info text-white'; }

                const popupContent = `
                    <div class="text-xs">
                        <div class="font-bold text-sm mb-1 text-base-content border-b border-base-200 pb-2 flex justify-between items-start gap-3">
                            <span class="leading-tight">${escHtml(store.customer_name)}</span>
                            <span class="badge badge-xs ${pilarClass} border-none shadow-sm font-bold p-2 shrink-0">${pilarName}</span>
                        </div>
                        <div class="text-[0.65rem] text-base-content/60 mt-1">
                            ${escHtml(store.customer_code_prc)}<br>
                            ${store.kecamatan ? '<span class="text-primary font-bold">' + escHtml(store.kecamatan) + '</span> - ' + escHtml(store.kelurahan) : ''}
                        </div>
                    </div>
                `;

                let popup = new maplibregl.Popup({ offset: 10, closeButton: true }).setHTML(popupContent);
                let marker = new maplibregl.Marker({ element: el })
                    .setLngLat(point)
                    .setPopup(popup)
                    .addTo(mmap);
                
                marker.storeId = store.id;
                mmarkers.push(marker);
            }
        });

        if (hasPoints) {
            mmap.fitBounds(bounds, { padding: 50, duration: 1000 });
        }
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

        if (mmarkers && mmarkers.length > 0) {
            mmarkers.forEach(m => {
                if (m.getPopup() && m.getPopup().isOpen()) m.togglePopup();
            });
            const targetMarker = mmarkers.find(m => m.storeId === storeId);
            if (targetMarker && !targetMarker.getPopup().isOpen()) targetMarker.togglePopup();
        }
    };

    setTimeout(() => {
        initManagementMap();
        const initialStores = $wire.managementClusterStores;
        if (initialStores && initialStores.length > 0) {
            if (mmap.isStyleLoaded()) {
                drawManagementClusters(initialStores);
            } else {
                mmap.once('load', () => drawManagementClusters(initialStores));
            }
        }
    }, 100);

    Livewire.on('management-clusters-generated', (data) => {
        const stores = data[0]?.stores || data.stores || [];
        if (!mmap) initManagementMap();
        setTimeout(() => {
            if (mmap) mmap.resize();
            if (mmap && mmap.isStyleLoaded()) {
                drawManagementClusters(stores);
            } else if (mmap) {
                mmap.once('load', () => drawManagementClusters(stores));
            }
        }, 100);
    });
</script>
@endscript
