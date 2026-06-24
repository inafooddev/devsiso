<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full" 
    x-data="{ showPhotoModal: false, photoUrl: '', showRejectModal: false, showMapModal: false, mapDist: 0, showReasonModal: false, reasonText: '', copyToast: { show: false, message: '' } }"
    x-on:open-map-modal.window="
        showMapModal = true; 
        mapDist = $event.detail.dist;
        setTimeout(() => {
            if (!window.L) return;
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
    <div x-on:open-reject-modal.window="showRejectModal = true" x-on:close-reject-modal.window="showRejectModal = false"></div>

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- Total --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Pengajuan</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-document-text class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($kpi['total']) }}</div>
        </div>
        
        {{-- Pending --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Menunggu Persetujuan</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-clock class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">{{ number_format($kpi['pending']) }}</div>
        </div>

        {{-- Approved --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Telah Disetujui</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ number_format($kpi['approved']) }}</div>
        </div>

        {{-- Rejected --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Ditolak</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ number_format($kpi['rejected']) }}</div>
        </div>
    </div>

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Perbaikan Koordinat Toko</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar usulan perbaikan tikor dari Sales</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative">
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari Toko/Sales..." class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 pl-8 w-full sm:w-auto" />
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-2.5 text-base-content/50" />
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
                    @if(count($selectedIds) > 0)
                        <button type="button" wire:click="bulkApprove" wire:confirm="Setujui {{ count($selectedIds) }} usulan perbaikan terpilih?" class="btn btn-sm btn-success text-white shadow-sm">
                            <x-heroicon-s-check-circle class="w-4 h-4" /> Terima Terpilih ({{ count($selectedIds) }})
                        </button>
                    @endif
                    <x-ui.action-button type="export" />
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-10 text-center px-2">
                            <x-heroicon-o-check class="w-4 h-4 inline-block text-base-content/50" />
                        </th>
                        <th class="w-12">No</th>
                        <th>Tanggal</th>
                        <th>Distributor</th>
                        <th>Kode Sales</th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th>Koordinat</th>
                        <th>Map</th>
                        <th class="text-center">Status</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $index => $item)
                    <tr class="hover:bg-base-200/50 transition-colors {{ in_array($item->id, $selectedIds) ? 'bg-success/5' : '' }}">
                        <th class="text-center px-2">
                            @if($item->status == 'Pending')
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $item->id }}" class="checkbox checkbox-sm checkbox-success rounded" />
                            @endif
                        </th>
                        <th class="text-base-content/60">{{ $data->firstItem() + $index }}</th>
                        <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                        <td class="font-bold">{{ $item->distributorImplementasiEskalink->distributor_name ?? $item->distributor_code }}</td>
                        <td class="font-mono">{{ $item->sales_code }}</td>
                        <td class="font-mono">{{ $item->customer_code }}</td>
                        <td class="font-bold">{{ $item->exact_customer->custname ?? 'N/A' }}</td>
                        <td class="font-mono">
                            <button type="button" @click="navigator.clipboard.writeText('{{ $item->latitude }}, {{ $item->longitude }}'); copyToast.message = 'Koordinat disalin!'; copyToast.show = true; setTimeout(() => copyToast.show = false, 2500);" class="hover:text-info flex items-center gap-1 group transition-colors" title="Klik untuk Copy Koordinat">
                                {{ $item->latitude }}, {{ $item->longitude }}
                                <x-heroicon-o-clipboard-document class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </button>
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
                                <button type="button" @click="$dispatch('open-map-modal', { lat1: '{{ $item->exact_customer->la ?? '' }}', lon1: '{{ $item->exact_customer->lg ?? '' }}', lat2: '{{ $item->latitude }}', lon2: '{{ $item->longitude }}', dist: '{{ $dist !== null ? number_format($dist, 0, '', '') : '' }}' })" class="btn btn-xs btn-outline btn-info w-full flex items-center justify-center whitespace-nowrap">
                                    {{ $dist !== null ? number_format($dist, 0, ',', '.') . 'm' : 'Map' }}
                                </button>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($item->status == 'Approved')
                                <span class="badge badge-sm badge-outline badge-success">Approved</span>
                            @elseif($item->status == 'Rejected')
                                <button type="button" @click="reasonText = '{{ addslashes($item->keterangan ?? 'Tidak ada keterangan') }}'; showReasonModal = true;" class="badge badge-sm badge-outline badge-error cursor-pointer hover:bg-error/10 transition-colors" title="Klik untuk lihat alasan">Rejected</button>
                            @else
                                <span class="badge badge-sm badge-outline badge-warning">Pending</span>
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
                                <button type="button" wire:click="approve({{ $item->id }})" wire:confirm="Yakin ingin menyetujui perubahan koordinat ini?" class="btn btn-sm btn-ghost text-success hover:bg-success/10 btn-square" title="Setujui">
                                    <x-heroicon-o-check class="w-4 h-4" />
                                </button>
                                {{-- Tolak --}}
                                <button type="button" wire:click="promptReject({{ $item->id }})" class="btn btn-sm btn-ghost text-error hover:bg-error/10 btn-square" title="Tolak">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
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
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
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
                    <button type="submit" class="btn btn-error text-white">Konfirmasi Tolak</button>
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

    {{-- MODAL MAP --}}
    <div x-show="showMapModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col h-[80vh]">
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
            <div class="p-4 border-t border-base-300 bg-base-200/50 text-right">
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

</div>

    {{-- Scripts and Styles inside root to prevent Multiple Root Elements Exception --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .leaflet-container { z-index: 10 !important; }
    </style>
</div>
