<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full" 
        x-data="{ showPhotoModal: false, photoUrl: '', showRejectModal: false, showMapModal: false, mapDist: 0, showReasonModal: false, reasonText: '', copyToast: { show: false, message: '' }, showSummaryModal: false }"
        x-on:open-summary-modal.window="showSummaryModal = true"
        x-on:open-map-modal.window="
        if (!window.L) {
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { type: 'error', message: 'Pustaka Peta (Leaflet) sedang memuat. Coba lagi.' } }));
            return;
        }
        showMapModal = true; 
        mapDist = $event.detail.dist;
        setTimeout(() => {
            if (!window.leafletMap) {
                window.leafletMap = L.map('leaflet-map-container').setView([$event.detail.lat2, $event.detail.lon2], 15);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20 }).addTo(window.leafletMap);
            }
            window.leafletMap.invalidateSize();
            
            if (window.mapLayers) {
                window.mapLayers.forEach(l => window.leafletMap.removeLayer(l));
            }
            window.mapLayers = [];
            
            let bounds = [];
            
            if ($event.detail.lat1 && $event.detail.lon1) {
                let m1 = L.marker([$event.detail.lat1, $event.detail.lon1], { opacity: 0.6 }).addTo(window.leafletMap).bindPopup('<b>Titik Master (Lama)</b>');
                window.mapLayers.push(m1);
                bounds.push([$event.detail.lat1, $event.detail.lon1]);
            }
            
            let m2 = L.marker([$event.detail.lat2, $event.detail.lon2]).addTo(window.leafletMap).bindPopup('<b>Titik Baru (Perbaikan)</b>');
            window.mapLayers.push(m2);
            bounds.push([$event.detail.lat2, $event.detail.lon2]);
            
            if ($event.detail.lat1 && $event.detail.lon1) {
                let line = L.polyline([[$event.detail.lat1, $event.detail.lon1], [$event.detail.lat2, $event.detail.lon2]], {color: '#f43f5e', dashArray: '5, 5', weight: 2}).addTo(window.leafletMap);
                window.mapLayers.push(line);
            }
            
            if (bounds.length > 0) {
                window.leafletMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 18 });
            }
        }, 300);
    "
>
    <x-slot name="title">Perbaikan Tikor Toko</x-slot>

    {{-- Listen for Livewire events --}}
    <div class="hidden" x-on:open-reject-modal.window="showRejectModal = true" x-on:close-reject-modal.window="showRejectModal = false"></div>

    {{-- Toast Notification sekarang menggunakan Vanilla JS --}}

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('others.perbaikantikor') }}" class="tab tab-xs px-4 {{ request()->routeIs('others.perbaikantikor') ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content transition-colors' }}" wire:navigate>SE (Reguler)</a>
            <a href="{{ route('others.perbaikantikor.elite') }}" class="tab tab-xs px-4 {{ request()->routeIs('others.perbaikantikor.elite') ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content transition-colors' }}" wire:navigate>SPV (Tim Elite)</a>
        </div>
    </div>

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- Total --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-gradient-to-br from-primary/10 to-primary/5 transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest truncate pr-2 mt-1">Total Pengajuan</h3>
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 shadow-sm">
                    <x-heroicon-s-document-text class="w-5 h-5 drop-shadow-sm" />
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-black tracking-tight mt-2 relative z-10 text-primary drop-shadow-sm">{{ number_format($kpi['total']) }}</div>
        </div>
        
        {{-- Pending --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-gradient-to-br from-warning/10 to-warning/5 transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest truncate pr-2 mt-1">Menunggu Persetujuan</h3>
                <div class="w-10 h-10 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0 shadow-sm">
                    <x-heroicon-s-clock class="w-5 h-5 drop-shadow-sm" />
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-black tracking-tight mt-2 relative z-10 text-warning drop-shadow-sm">{{ number_format($kpi['pending']) }}</div>
        </div>

        {{-- Approved --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-gradient-to-br from-success/10 to-success/5 transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest truncate pr-2 mt-1">Telah Disetujui</h3>
                <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0 shadow-sm">
                    <x-heroicon-s-check-circle class="w-5 h-5 drop-shadow-sm" />
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-black tracking-tight mt-2 relative z-10 text-success drop-shadow-sm">{{ number_format($kpi['approved']) }}</div>
        </div>

        {{-- Rejected --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-gradient-to-br from-error/10 to-error/5 transition-transform duration-500 group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-xs font-bold text-base-content/60 uppercase tracking-widest truncate pr-2 mt-1">Ditolak</h3>
                <div class="w-10 h-10 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0 shadow-sm">
                    <x-heroicon-s-x-circle class="w-5 h-5 drop-shadow-sm" />
                </div>
            </div>
            <div class="text-2xl md:text-3xl font-black tracking-tight mt-2 relative z-10 text-error drop-shadow-sm">{{ number_format($kpi['rejected']) }}</div>
        </div>
    </div>

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-lg md:text-xl font-black tracking-tight text-base-content">Data Perbaikan Koordinat Toko</h2>
                <p class="text-xs text-base-content/50 font-bold uppercase tracking-widest mt-1">Daftar usulan perbaikan tikor dari Sales</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative">
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari Toko/Sales..." class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 pl-8 w-full sm:w-auto" />
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-2.5 text-base-content/50" />
                </div>
                
                {{-- Filter Date Range --}}
                <div class="flex items-center gap-1">
                    <input wire:model.live="dateStart" type="date" class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 w-[115px]" title="Mulai Tanggal" />
                    <span class="text-xs font-bold text-base-content/40">-</span>
                    <input wire:model.live="dateEnd" type="date" class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 w-[115px]" title="Sampai Tanggal" />
                </div>

                {{-- Filter Status --}}
                <select wire:model.live="statusFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>

                {{-- Actions Button (Optional Export dll) --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    @if($this->selectedPendingCount > 0)
                        <button type="button" wire:click="bulkApprove" wire:confirm="Setujui {{ $this->selectedPendingCount }} usulan perbaikan terpilih?" class="btn btn-sm btn-success text-white shadow-sm" wire:loading.attr="disabled" wire:target="bulkApprove">
                            <span wire:loading wire:target="bulkApprove" class="loading loading-spinner loading-xs mr-1"></span>
                            <x-heroicon-s-check-circle class="w-4 h-4" wire:loading.remove wire:target="bulkApprove" /> Terima Terpilih ({{ $this->selectedPendingCount }})
                        </button>
                    @endif
                    <x-ui.action-button type="export" wire:click="export" />
                    <button type="button" wire:click="loadSummary" class="btn btn-sm btn-info text-white shadow-sm" wire:loading.attr="disabled" wire:target="loadSummary">
                        <span wire:loading wire:target="loadSummary" class="loading loading-spinner loading-xs mr-1"></span>
                        <x-heroicon-s-chart-bar class="w-4 h-4" wire:loading.remove wire:target="loadSummary" /> Summary
                    </button>
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-10 text-center px-2">
                            <input type="checkbox" wire:model.live="selectAll" class="checkbox checkbox-sm checkbox-success rounded" title="Pilih Semua (Select All)" />
                        </th>
                        <th class="w-12">No</th>

                        <th>Distributor</th>
                        <th>Kode Sales</th>
                        <th>Nama Sales</th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th>Alamat</th>
                        <th>Koordinat</th>
                        <th class="text-center">Akurasi</th>
                        <th>Map</th>
                        <th class="text-center">Status</th>
                        <th>Waktu Pengajuan</th>
                        <th>Waktu Proses</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $index => $item)
                    <tr class="transition-colors {{ in_array($item->id, $selectedIds) ? 'bg-success/10 hover:bg-success/15' : 'hover:bg-base-200/50' }}">
                        <th class="text-center px-2">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $item->id }}" class="checkbox checkbox-sm checkbox-success rounded" />
                        </th>
                        <th class="text-base-content/60">{{ $data->firstItem() + $index }}</th>

                        <td>
                            <div class="font-bold text-xs truncate max-w-[150px]" title="{{ $item->distributorImplementasiEskalink->distributor_name ?? $item->distributor_code }}">
                                {{ $item->distributorImplementasiEskalink->distributor_name ?? $item->distributor_code }}
                            </div>
                        </td>
                        <td class="font-mono">{{ $item->sales_code }}</td>
                        <td>
                            <div class="font-bold truncate max-w-[150px]" title="{{ $item->sales_name ?? '-' }}">
                                {{ $item->sales_name ?? '-' }}
                            </div>
                        </td>
                        <td class="font-mono">{{ $item->customer_code }}</td>
                        <td class="font-bold {{ in_array($item->distributor_code . '_' . $item->customer_code, $duplicates) ? 'text-rose-600' : '' }}">
                            {{ $item->exact_customer->custname ?? 'N/A' }}
                            @if(in_array($item->distributor_code . '_' . $item->customer_code, $duplicates))
                                <span class="badge badge-[10px] badge-error text-[9px] text-white ml-1 px-1 py-0 h-4" title="Toko ini diajukan lebih dari sekali">Berulang</span>
                            @endif
                        </td>
                        <td class="text-xs max-w-[200px] truncate" title="{{ $item->exact_customer->custadd1 ?? '-' }}">
                            {{ $item->exact_customer->custadd1 ?? '-' }}
                        </td>
                        <td class="font-mono">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                    @click="
                                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                            navigator.clipboard.writeText('{{ $item->latitude }}, {{ $item->longitude }}');
                                        } else {
                                            let textArea = document.createElement('textarea');
                                            textArea.value = '{{ $item->latitude }}, {{ $item->longitude }}';
                                            textArea.style.position = 'fixed';
                                            textArea.style.left = '-999999px';
                                            document.body.appendChild(textArea);
                                            textArea.focus();
                                            textArea.select();
                                            try { document.execCommand('copy'); } catch (err) { console.error('Gagal menyalin: ', err); }
                                            document.body.removeChild(textArea);
                                        }
                                        copyToast.message = 'Koordinat disalin!';
                                        copyToast.show = true;
                                        setTimeout(() => copyToast.show = false, 2500);
                                    " 
                                    class="hover:text-info flex items-center gap-1 group transition-colors" 
                                    title="Klik untuk Copy Koordinat"
                                >
                                    {{ $item->latitude }}, {{ $item->longitude }}
                                    <x-heroicon-o-clipboard-document class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                </button>
                                <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="text-error hover:text-error/70 transition-colors" title="Buka di Google Maps">
                                    <x-heroicon-s-map-pin class="w-4 h-4" />
                                </a>
                            </div>
                        </td>
                        <td class="text-center font-bold">
                            @if($item->accuracy)
                                <span class="{{ $item->accuracy <= 15 ? 'text-success' : ($item->accuracy > 100 ? 'text-error' : 'text-warning') }}">
                                    {{ rtrim(rtrim(number_format($item->accuracy, 2, ',', '.'), '0'), ',') }}m
                                </span>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $dist = null;
                                if(isset($item->exact_customer->la) && isset($item->exact_customer->lg) && $item->latitude && $item->longitude) {
                                    $lat1 = $item->exact_customer->la;
                                    $lon1 = $item->exact_customer->lg;
                                    $lat2 = $item->latitude;
                                    $lon2 = $item->longitude;
                                    
                                    if(is_numeric($lat1) && is_numeric($lon1) && is_numeric($lat2) && is_numeric($lon2)) {
                                        $theta = $lon1 - $lon2;
                                        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                                        $dist = max(-1, min(1, $dist));
                                        $dist = acos($dist);
                                        $dist = rad2deg($dist);
                                        $miles = $dist * 60 * 1.1515;
                                        $dist = $miles * 1.609344 * 1000; // in meters
                                    }
                                }
                            @endphp
                            <div class="flex flex-col items-center justify-center gap-1 w-full min-w-[80px]">
                                @if($dist !== null)
                                    @if($dist < 1)
                                        <button type="button" @click="$dispatch('open-map-modal', { lat1: '{{ $item->exact_customer->la ?? '' }}', lon1: '{{ $item->exact_customer->lg ?? '' }}', lat2: '{{ $item->latitude }}', lon2: '{{ $item->longitude }}', dist: '{{ number_format($dist, 0, '', '') }}' })" class="btn btn-xs btn-warning w-full flex items-center justify-center whitespace-nowrap font-bold">
                                            0m (Tetap)
                                        </button>
                                    @else
                                        <button type="button" @click="$dispatch('open-map-modal', { lat1: '{{ $item->exact_customer->la ?? '' }}', lon1: '{{ $item->exact_customer->lg ?? '' }}', lat2: '{{ $item->latitude }}', lon2: '{{ $item->longitude }}', dist: '{{ number_format($dist, 0, '', '') }}' })" class="btn btn-xs btn-outline btn-info w-full flex items-center justify-center whitespace-nowrap">
                                            {{ number_format($dist, 0, ',', '.') . 'm' }}
                                        </button>
                                    @endif
                                @else
                                    <button type="button" @click="$dispatch('open-map-modal', { lat1: '', lon1: '', lat2: '{{ $item->latitude }}', lon2: '{{ $item->longitude }}', dist: '' })" class="btn btn-xs btn-outline btn-info w-full flex items-center justify-center whitespace-nowrap">
                                        Map
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            @if($item->status == 'Approved')
                                <span class="badge badge-sm badge-outline badge-success">Approved</span>
                            @elseif($item->status == 'Rejected')
                                <button type="button" 
                                    @click="reasonText = $el.dataset.keterangan; showReasonModal = true;" 
                                    data-keterangan="{{ $item->keterangan ?? 'Tidak ada keterangan' }}"
                                    class="badge badge-sm badge-outline badge-error cursor-pointer hover:bg-error/10 transition-colors" 
                                    title="Klik untuk lihat alasan"
                                >
                                    Rejected
                                </button>
                            @else
                                <span class="badge badge-sm badge-outline badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-1 font-medium text-xs" title="Waktu Pengajuan (Sales)">
                                <x-heroicon-s-arrow-up-circle class="w-3.5 h-3.5 text-base-content/40" />
                                <span>{{ $item->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            @if($item->status != 'Pending')
                                <div class="flex items-center gap-1 text-xs text-base-content/60" title="Waktu Pengerjaan (Admin)">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5 {{ $item->status == 'Approved' ? 'text-success' : 'text-error' }}" />
                                    <span>{{ $item->updated_at->format('d M Y H:i') }}</span>
                                </div>
                            @else
                                <span class="text-base-content/30 italic text-[10px]">-</span>
                            @endif
                        </td>
                        <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Lihat Foto --}}
                                @if($item->foto)
                                <button type="button" @click="photoUrl = '{{ asset('storage/' . $item->foto) }}'; showPhotoModal = true;" class="btn btn-sm btn-ghost text-info hover:bg-info/10 btn-square" title="Lihat Foto Bukti">
                                    <x-heroicon-o-photo class="w-4 h-4" />
                                </button>
                                @endif

                                @if($item->status == 'Pending')
                                {{-- Setujui --}}
                                <button type="button" wire:click="approve({{ $item->id }})" wire:confirm="Yakin ingin menyetujui perubahan koordinat ini?" class="btn btn-sm btn-ghost text-success hover:bg-success/10 btn-square" title="Setujui" wire:loading.attr="disabled" wire:target="approve({{ $item->id }})">
                                    <x-heroicon-o-check class="w-4 h-4" wire:loading.remove wire:target="approve({{ $item->id }})" />
                                    <span wire:loading wire:target="approve({{ $item->id }})" class="loading loading-spinner loading-xs text-success"></span>
                                </button>
                                {{-- Tolak --}}
                                <button type="button" wire:click="promptReject({{ $item->id }})" class="btn btn-sm btn-ghost text-error hover:bg-error/10 btn-square" title="Tolak" wire:loading.attr="disabled" wire:target="promptReject({{ $item->id }})">
                                    <x-heroicon-o-x-mark class="w-4 h-4" wire:loading.remove wire:target="promptReject({{ $item->id }})" />
                                    <span wire:loading wire:target="promptReject({{ $item->id }})" class="loading loading-spinner loading-xs text-error"></span>
                                </button>
                                @else
                                <span class="text-[10px] text-base-content/50 italic font-normal">Selesai</span>
                                @endif
                            </div>
                        </th>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-base-content/40">
                                <x-heroicon-o-inbox class="w-12 h-12 mb-2" />
                                <p class="text-sm">Tidak ada data pengajuan perbaikan tikor.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs md:text-sm">
            <div class="w-full">
                {{ $data->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW FOTO --}}
    <div x-show="showPhotoModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showPhotoModal = false">
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <h3 class="font-bold text-lg">Foto Bukti Perbaikan Tikor</h3>
                <button type="button" @click="showPhotoModal = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            <div class="p-4 overflow-auto flex-1 flex items-center justify-center bg-base-300/30">
                <img :src="photoUrl" class="max-w-full h-auto max-h-[70vh] rounded-xl shadow-md border border-base-300 object-contain" alt="Foto Bukti" />
            </div>
            <div class="p-4 border-t border-base-300 bg-base-200/50 text-right">
                <button type="button" @click="showPhotoModal = false" class="btn btn-outline">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL PENOLAKAN --}}
    <div x-show="showRejectModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col" @click.outside="showRejectModal = false">
            <div class="p-4 border-b border-error/20 flex items-center justify-between bg-error/10">
                <div class="flex items-center gap-2 text-error">
                    <x-heroicon-s-exclamation-triangle class="w-6 h-6" />
                    <h3 class="font-bold text-lg">Tolak Pengajuan Tikor</h3>
                </div>
                <button type="button" @click="showRejectModal = false" class="btn btn-sm btn-circle btn-ghost text-error">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            <form wire:submit.prevent="reject">
                <div class="p-5 space-y-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-bold">Alasan Penolakan <span class="text-error">*</span></span>
                        </label>
                        <textarea wire:model="keteranganReject" class="textarea textarea-bordered w-full h-24 bg-base-50 focus:border-error focus:ring-error" placeholder="Cth: Foto tidak jelas, koordinat melenceng, dll..." required></textarea>
                        @error('keteranganReject') <span class="text-error text-xs font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="p-4 border-t border-base-300 bg-base-200/50 flex justify-end gap-2">
                    <button type="button" @click="showRejectModal = false" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-error text-white" wire:loading.attr="disabled" wire:target="reject">
                        <span wire:loading wire:target="reject" class="loading loading-spinner loading-xs mr-1"></span>
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ALASAN PENOLAKAN --}}
    <div x-show="showReasonModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col" @click.outside="showReasonModal = false">
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <div class="flex items-center gap-2 text-error">
                    <x-heroicon-s-information-circle class="w-5 h-5" />
                    <h3 class="font-bold text-base">Alasan Penolakan</h3>
                </div>
                <button type="button" @click="showReasonModal = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            <div class="p-5">
                <p class="text-sm text-base-content/80 whitespace-pre-line leading-relaxed" x-text="reasonText"></p>
            </div>
            <div class="p-4 border-t border-base-300 bg-base-200/50 text-right">
                <button type="button" @click="showReasonModal = false" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL SUMMARY --}}
    <div x-show="showSummaryModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-[95vw] overflow-hidden flex flex-col max-h-[90vh]" @click.outside="showSummaryModal = false">
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <x-heroicon-s-chart-bar class="w-5 h-5 text-info" /> Summary Perbaikan Tikor
                </h3>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input wire:model.live.debounce.500ms="summarySearch" type="text" placeholder="Cari..." class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 pl-8 w-48" />
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-2.5 text-base-content/50" />
                    </div>
                    <button type="button" @click="showSummaryModal = false" class="btn btn-sm btn-circle btn-ghost">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
            </div>
            
            <div class="p-0 overflow-auto flex-1 bg-base-100 w-full relative">
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th>No</th>
                            <th>Region</th>
                            <th>Area</th>
                            <th>Distributor</th>
                            <th>Kode Sales</th>
                            <th>Nama Sales</th>
                            <th class="text-center">Total Pengajuan</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Rejected</th>
                            <th class="text-center">Approved</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm relative">
                        <!-- Loading Overlay -->
                        <div wire:loading wire:target="summarySearch" class="absolute inset-0 z-10 bg-base-100/50 backdrop-blur-[1px] flex items-center justify-center">
                            <span class="loading loading-spinner loading-md text-info"></span>
                        </div>
                        
                        @forelse($summaryData as $index => $summary)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="text-base-content/60">{{ $index + 1 }}</td>
                            <td>{{ $summary->region_name ?? $summary->region_code ?? '-' }}</td>
                            <td>{{ $summary->area_name ?? $summary->area_code ?? '-' }}</td>
                            <td class="font-bold text-xs truncate max-w-[150px]" title="{{ $summary->distributor_name ?? '-' }}">{{ $summary->distributor_name ?? '-' }}</td>
                            <td class="font-mono">{{ $summary->sales_code }}</td>
                            <td class="font-bold">{{ $summary->sales_name ?? '-' }}</td>
                            <td class="text-center font-bold text-base-content">{{ $summary->total_pengajuan }}</td>
                            <td class="text-center font-bold text-warning">{{ $summary->pending }}</td>
                            <td class="text-center font-bold text-error">{{ $summary->rejected }}</td>
                            <td class="text-center font-bold text-success">{{ $summary->approved }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-base-content/50">Tidak ada data summary.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="text-sm font-bold bg-base-200/80 border-t-2 border-base-300">
                        <tr>
                            <td colspan="6" class="text-right uppercase tracking-wider">Total Keseluruhan</td>
                            <td class="text-center text-base-content">{{ collect($summaryData)->sum('total_pengajuan') }}</td>
                            <td class="text-center text-warning">{{ collect($summaryData)->sum('pending') }}</td>
                            <td class="text-center text-error">{{ collect($summaryData)->sum('rejected') }}</td>
                            <td class="text-center text-success">{{ collect($summaryData)->sum('approved') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-4 border-t border-base-300 bg-base-200/50 flex justify-end gap-2">
                <button type="button" @click="showSummaryModal = false" class="btn btn-outline">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL MAP --}}
    <div x-show="showMapModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col h-[80vh]" @click.outside="showMapModal = false">
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <x-heroicon-s-map class="w-5 h-5 text-info" /> Peta Perbandingan
                    <template x-if="mapDist">
                        <span class="badge badge-error badge-outline badge-sm ml-2 font-bold" x-text="'Jarak: ' + mapDist + ' meter'"></span>
                    </template>
                </h3>
                <button type="button" @click="showMapModal = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            <div class="p-0 relative bg-base-300 w-full" style="height: 60vh; min-height: 400px;">
                <div id="leaflet-map-container" class="w-full h-full z-0"></div>
            </div>
            <div class="p-4 border-t border-base-300 bg-base-200/50 flex justify-end gap-2">
                <button type="button" @click="setTimeout(() => { if(window.leafletMap) { window.leafletMap.invalidateSize(); let group = L.featureGroup(window.mapLayers); if(group.getBounds().isValid()) { window.leafletMap.fitBounds(group.getBounds(), { padding: [40, 40], maxZoom: 18 }); } } }, 100);" class="btn btn-outline btn-info">
                    <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh Map
                </button>
                <button type="button" @click="showMapModal = false" class="btn btn-outline">Tutup</button>
            </div>
        </div>
        {{-- LOCAL TOAST NOTIFICATION --}}
    <div x-show="copyToast.show" class="toast toast-top toast-center z-[200] mt-16" style="display: none;" x-transition.opacity.duration.300ms>
        <div class="alert alert-success shadow-lg text-white font-bold text-sm px-4 py-2 rounded-xl flex items-center gap-2">
            <x-heroicon-o-check-circle class="w-5 h-5" />
            <span x-text="copyToast.message"></span>
        </div>
    </div>

    {{-- Deleted unused Alpine confirmModal store component --}}

    {{-- Block Vanilla JS, Map, dan Modal diproteksi dari Livewire DOM Morphing --}}
    <div wire:ignore>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .leaflet-container { z-index: 10 !important; }
        
        .v-toast-fade-out {
            animation: toastOut 0.3s ease-in forwards;
        }
        @keyframes toastOut {
            from { transform: scale(1); opacity: 1; }
            to   { transform: scale(0.9); opacity: 0; }
        }
    </style>
    <script>
        // Vanilla JS Toast Handler
        window.addEventListener('show-toast', (e) => {
            let detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
            let type = detail?.type || 'success';
            let message = detail?.message || detail || 'Sukses';
            
            let container = document.getElementById('vanilla-toast-container');
            if(!container) {
                container = document.createElement('div');
                container.id = 'vanilla-toast-container';
                container.className = 'toast toast-top toast-center z-[9999] pt-4';
                document.body.appendChild(container);
            }
            
            let toast = document.createElement('div');
            toast.className = 'flex items-start gap-3 px-5 py-4 mb-2 min-w-[280px] w-auto max-w-[90vw] sm:max-w-md whitespace-normal break-words rounded-2xl shadow-2xl backdrop-blur-md text-sm font-medium ' + 
                (type === 'error' 
                    ? 'bg-error/95 text-error-content shadow-error/20 border border-error/50' 
                    : 'bg-success/95 text-success-content shadow-success/20 border border-success/50');
            
            // Ikon
            let iconSvg = type === 'success' 
                ? '<svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" /></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" class="shrink-0 w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" /></svg>';
            
            toast.innerHTML = iconSvg + '<div class="flex-1 pt-0.5 leading-snug tracking-wide">' + message + '</div>';
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('v-toast-fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        });
    </script>
    </div>{{-- END wire:ignore --}}
</div>
