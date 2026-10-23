<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full" 
        x-data="{ showDetailModal: false, detailData: null, detailPhotoRotation: 0, detailPhotoZoom: 1, detailPhotoPanX: 0, detailPhotoPanY: 0, isPanning: false, panStartX: 0, panStartY: 0, showRejectModal: false, showMapModal: false, mapDist: 0, mapLayerMode: 'carto', showReasonModal: false, reasonText: '', copyToast: { show: false, message: '' }, showSummaryModal: false, showExportModal: false, showKpi: false }"
        x-on:open-summary-modal.window="showSummaryModal = true"
        x-on:open-map-modal.window="
            showMapModal = true; 
            mapDist = $event.detail.dist || '';
            mapLayerMode = 'carto';
            window.comparisonMapDetail = $event.detail;
            setTimeout(() => {
                if (window.renderComparisonMap) {
                    window.renderComparisonMap($event.detail);
                }
            }, 120);
        "
>
>
    <x-slot name="title">Perbaikan Tikor Toko</x-slot>

    {{-- Listen for Livewire events --}}
    <div class="hidden" x-on:open-reject-modal.window="showRejectModal = true" x-on:close-reject-modal.window="showRejectModal = false"></div>

    {{-- Toast Notification sekarang menggunakan Vanilla JS --}}

    {{-- TABS & TOGGLE KPI --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center justify-between shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('others.perbaikantikor') }}" class="tab tab-xs px-4 {{ request()->routeIs('others.perbaikantikor') ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content transition-colors' }}" wire:navigate>SE (Reguler)</a>
            <a href="{{ route('others.perbaikantikor.elite') }}" class="tab tab-xs px-4 {{ request()->routeIs('others.perbaikantikor.elite') ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content transition-colors' }}" wire:navigate>SPV (Tim Elite)</a>
        </div>

        {{-- Toggle KPI Button --}}
        <button 
            type="button" 
            @click="showKpi = !showKpi" 
            class="btn btn-xs btn-ghost gap-1.5 rounded-lg border border-base-300 text-xs font-semibold hover:bg-base-200 transition-all duration-200"
            :class="{ 'bg-primary/10 text-primary border-primary/30 shadow-xs': showKpi, 'text-base-content/60': !showKpi }"
            title="Sembunyikan / Tampilkan Ringkasan KPI"
        >
            <x-heroicon-o-chart-bar class="w-3.5 h-3.5" />
            <span x-text="showKpi ? 'Sembunyikan KPI' : 'Tampilkan KPI'">Tampilkan KPI</span>
            <span class="inline-block transition-transform duration-200" :class="{ 'rotate-180': showKpi }">
                <x-heroicon-o-chevron-down class="w-3 h-3" />
            </span>
        </button>
    </div>

    {{-- KPI Cards Section (Default Tertutup) --}}
    <div 
        x-show="showKpi" 
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        style="display: none;"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0"
    >
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
        
        {{-- Header Card & Actions Toolbar (Standar Filter UI Components) --}}
        <x-ui.filter-toolbar>
            {{-- 1. Kapsul Filter Hierarki (Distributor, SPV/SE, Status) --}}
            <x-ui.filter-breadcrumb>
                {{-- Distributor Segment (Micro-Searchable Popover) --}}
                <x-ui.filter-searchable 
                    wire:key="filter-dist-elite-capsule"
                    wire:model.live="distributorFilter" 
                    :options="$this->distributorOptions" 
                    valueField="distributor_code" 
                    labelField="distributor_name" 
                    placeholder="Semua Distributor" 
                    searchPlaceholder="Cari nama / kode distributor..."
                    icon="distributor" 
                    allowAll="true"
                    allLabel="Semua Distributor"
                    width="w-36 sm:w-48"
                    popoverWidth="w-72 sm:w-80" />

                {{-- Salesman / SPV Segment (Micro-Searchable Popover) --}}
                <x-ui.filter-searchable 
                    wire:key="filter-sales-elite-capsule-{{ $distributorFilter }}"
                    wire:model.live="salesFilter" 
                    :options="$this->salesOptions" 
                    valueField="sales_code" 
                    labelField="sales_name" 
                    placeholder="Semua SPV / SE" 
                    searchPlaceholder="Cari nama / kode SPV / SE..."
                    icon="user" 
                    allowAll="true"
                    allLabel="Semua SPV / SE"
                    width="w-32 sm:w-40"
                    popoverWidth="w-64 sm:w-72"
                    :disabled="count($this->salesOptions) === 0" />

                {{-- Status Segment --}}
                <x-ui.filter-select 
                    wire:model.live="statusFilter" 
                    :options="['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected']" 
                    placeholder="Semua Status" 
                    icon="tag" 
                    width="w-28 sm:w-32" />
            </x-ui.filter-breadcrumb>

            {{-- 2. Kapsul Periode Pintar (Smart Date Range Filter) --}}
            <x-ui.filter-date-range 
                start="dateStart" 
                end="dateEnd" 
                :maxDays="0" />

            {{-- 3. Search Box Input --}}
            <x-ui.search-input 
                wire:model.live.debounce.400ms="search" 
                placeholder="Cari Toko / Sales..." />

            {{-- 4. Actions Slot (Reset, Export, Summary, Bulk Approve) --}}
            <x-slot name="actions">
                @php
                    $hasActiveFilters = !empty($search) || !empty($statusFilter) || !empty($dateStart) || !empty($dateEnd) || !empty($distributorFilter) || !empty($salesFilter);
                @endphp
                <x-ui.filter-actions 
                    :applyAction="false" 
                    resetAction="resetFilters" 
                    :showReset="$hasActiveFilters">
                    
                    {{-- Tombol Ekspor Excel --}}
                    @if($this->canManage)
                        <button type="button" 
                            @click="showExportModal = true" 
                            class="btn btn-sm btn-outline btn-success rounded-xl gap-1.5 text-xs font-semibold shadow-2xs cursor-pointer"
                            title="Ekspor Data ke Excel (.xlsx)">
                            <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                            <span class="hidden sm:inline">Export</span>
                        </button>
                    @endif

                    {{-- Tombol Summary --}}
                    <button type="button" 
                        wire:click="loadSummary" 
                        wire:loading.attr="disabled"
                        wire:target="loadSummary"
                        class="btn btn-sm btn-outline btn-info rounded-xl gap-1.5 text-xs font-semibold shadow-2xs cursor-pointer"
                        title="Buka Ringkasan (Summary)">
                        <span wire:loading.remove wire:target="loadSummary" class="flex items-center gap-1.5">
                            <x-heroicon-m-chart-bar class="w-4 h-4" />
                            <span class="hidden sm:inline">Summary</span>
                        </span>
                        <span wire:loading wire:target="loadSummary" class="loading loading-spinner loading-xs"></span>
                    </button>

                    {{-- Tombol Bulk Approve (jika diaktifkan) --}}
                    @if($enableBulkApprove && $this->canManage && $this->selectedPendingCount > 0)
                        <button type="button" 
                            wire:click="bulkApprove" 
                            wire:confirm="Setujui {{ $this->selectedPendingCount }} usulan perbaikan terpilih?" 
                            class="btn btn-sm btn-success text-white rounded-xl shadow-xs gap-1 text-xs font-semibold cursor-pointer" 
                            wire:loading.attr="disabled" 
                            wire:target="bulkApprove">
                            <span wire:loading wire:target="bulkApprove" class="loading loading-spinner loading-xs mr-1"></span>
                            <x-heroicon-s-check-circle class="w-4 h-4" wire:loading.remove wire:target="bulkApprove" /> Terima Terpilih ({{ $this->selectedPendingCount }})
                        </button>
                    @endif
                </x-ui.filter-actions>
            </x-slot>
        </x-ui.filter-toolbar>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        @if($enableBulkApprove && $this->canManage)
                        <th class="w-10 text-center px-2">
                            <input type="checkbox" wire:model.live="selectAll" class="checkbox checkbox-sm checkbox-success rounded" title="Pilih Semua (Select All)" />
                        </th>
                        @endif
                        <th class="w-12">No</th>

                        <th>Nama Sales</th>
                        <th>Distributor</th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th>Alamat</th>
                        <th>Koordinat</th>
                        <th class="text-center">Akurasi</th>
                        <th>Map</th>
                        <th class="text-center">Status</th>
                        <th>Waktu Pengajuan</th>
                        <th>Waktu Proses</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $index => $item)
                    <tr class="transition-colors {{ ($enableBulkApprove && in_array($item->id, $selectedIds)) ? 'bg-success/10 hover:bg-success/15' : 'hover:bg-base-200/50' }}">
                        @if($enableBulkApprove && $this->canManage)
                        <th class="text-center px-2">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $item->id }}" class="checkbox checkbox-sm checkbox-success rounded" />
                        </th>
                        @endif
                        <th class="text-base-content/60">{{ $data->firstItem() + $index }}</th>

                        <td>
                            <div class="font-bold truncate max-w-[150px]" title="{{ $item->sales_name ?? '-' }}">
                                {{ $item->sales_name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div class="font-bold text-xs truncate max-w-[150px]" title="{{ $item->distributorImplementasiEskalink->distributor_name ?? $item->distributor_code }}">
                                {{ $item->distributorImplementasiEskalink->distributor_name ?? $item->distributor_code }}
                            </div>
                        </td>
                        <td class="font-mono">{{ $item->customer_code }}</td>
                        <td class="font-bold {{ in_array($item->distributor_code . '_' . $item->sales_code . '_' . $item->customer_code, $duplicates) ? 'text-rose-600' : '' }}">
                            <div class="flex items-center gap-1">
                                <span class="truncate max-w-[150px]" title="{{ $item->elite_customer_name ?? 'N/A' }}">
                                    {{ $item->elite_customer_name ?? 'N/A' }}
                                </span>
                                @if(in_array($item->distributor_code . '_' . $item->sales_code . '_' . $item->customer_code, $duplicates))
                                    <span class="badge badge-[10px] badge-error text-[9px] text-white shrink-0 px-1 py-0 h-4" title="Toko ini diajukan lebih dari sekali">Berulang</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-xs max-w-[200px] truncate" title="{{ $item->elite_customer_address ?? '-' }}">
                            {{ $item->elite_customer_address ?? '-' }}
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
                                if($item->elite_lat && $item->elite_long && $item->latitude && $item->longitude) {
                                    $lat1 = $item->elite_lat;
                                    $lon1 = $item->elite_long;
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
                                        <button type="button" @click="$dispatch('open-map-modal', { lat1: '{{ $item->elite_lat ?? '' }}', lon1: '{{ $item->elite_long ?? '' }}', lat2: '{{ $item->latitude }}', lon2: '{{ $item->longitude }}', dist: '{{ number_format($dist, 0, '', '') }}' })" class="btn btn-xs btn-warning w-full flex items-center justify-center whitespace-nowrap font-bold">
                                            0m
                                        </button>
                                    @else
                                        <button type="button" @click="$dispatch('open-map-modal', { lat1: '{{ $item->elite_lat ?? '' }}', lon1: '{{ $item->elite_long ?? '' }}', lat2: '{{ $item->latitude }}', lon2: '{{ $item->longitude }}', dist: '{{ number_format($dist, 0, '', '') }}' })" class="btn btn-xs btn-outline btn-info w-full flex items-center justify-center whitespace-nowrap">
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
                        @php
                            $detailItem = [
                                'id' => $item->id,
                                'customer_code' => $item->customer_code,
                                'customer_name' => $item->elite_customer_name ?? 'N/A',
                                'customer_address' => $item->elite_customer_address ?? '-',
                                'customer_city' => '-',
                                'distributor_code' => $item->distributor_code,
                                'distributor_name' => $item->distributorImplementasiEskalink->distributor_name ?? $item->distributor_code,
                                'sales_code' => $item->sales_code,
                                'sales_name' => $item->sales_name ?? '-',
                                'latitude_old' => $item->elite_lat ?? null,
                                'longitude_old' => $item->elite_long ?? null,
                                'latitude' => $item->latitude,
                                'longitude' => $item->longitude,
                                'accuracy' => $item->accuracy,
                                'distance' => $dist !== null ? round($dist) : null,
                                'status' => $item->status,
                                'keterangan' => $item->keterangan,
                                'foto_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                                'created_at_formatted' => $item->created_at->format('d M Y H:i'),
                                'updated_at_formatted' => $item->updated_at->format('d M Y H:i'),
                            ];
                        @endphp
                        <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                            <div class="flex items-center justify-center">
                                <button 
                                    type="button" 
                                    @click="detailPhotoRotation = 0; detailPhotoZoom = 1; detailPhotoPanX = 0; detailPhotoPanY = 0; detailData = @js($detailItem); showDetailModal = true;" 
                                    class="btn btn-xs btn-primary text-white rounded-lg gap-1 shadow-sm px-2.5 py-1 hover:brightness-110 transition-all font-semibold"
                                    title="Lihat Detail Toko, Foto & Street View"
                                >
                                    <x-heroicon-s-eye class="w-3.5 h-3.5" />
                                    <span>Detail</span>
                                </button>
                            </div>
                        </th>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ ($enableBulkApprove && $this->canManage) ? '14' : '13' }}" class="text-center py-12">
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

    {{-- MODAL DETAIL PERBAIKAN TIKOR (Ukuran Besar, Layout Foto & Street View Maksimal) --}}
    <div x-show="showDetailModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-2 sm:p-3 md:p-4 bg-black/80 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-[97vw] 2xl:max-w-[1700px] h-[96vh] overflow-hidden flex flex-col border border-base-300" @click.outside="showDetailModal = false">
            
            {{-- Header Modal Detail --}}
            <div class="px-4 py-2.5 md:px-5 md:py-3 border-b border-base-300 flex items-center justify-between bg-base-200/60 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                        <x-heroicon-s-building-storefront class="w-4 h-4 md:w-5 md:h-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-black text-sm md:text-base text-base-content leading-tight truncate" x-text="detailData?.customer_name"></h3>
                            <span class="badge badge-sm badge-neutral font-mono font-bold" x-text="detailData?.customer_code"></span>
                            <template x-if="detailData?.status === 'Approved'">
                                <span class="badge badge-sm badge-success text-white font-bold">Approved</span>
                            </template>
                            <template x-if="detailData?.status === 'Pending'">
                                <span class="badge badge-sm badge-warning font-bold">Pending</span>
                            </template>
                            <template x-if="detailData?.status === 'Rejected'">
                                <span class="badge badge-sm badge-error text-white font-bold">Rejected</span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] md:text-xs text-base-content/60 font-medium truncate mt-0.5">
                            <span x-text="detailData?.distributor_name ? detailData.distributor_name + ' (' + detailData.distributor_code + ')' : detailData?.distributor_code"></span >
                            <span>•</span>
                            <span>Sales: <strong class="text-base-content/80" x-text="detailData?.sales_name"></strong> (<span class="font-mono" x-text="detailData?.sales_code || '-'"></span>)</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Body Modal Detail --}}
            <div class="p-3 md:p-3.5 flex-1 flex flex-col gap-2.5 bg-base-100 min-h-0 overflow-y-auto">
                
                {{-- Bar Info Kompak: Alamat, Koordinat Master vs Usulan, Jarak & Akurasi --}}
                <div class="bg-base-200/50 px-3.5 py-2 rounded-xl border border-base-300 shrink-0 text-xs shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3 items-center">
                        
                        {{-- Alamat Toko --}}
                        <div class="md:col-span-5 flex items-center gap-2 min-w-0">
                            <x-heroicon-o-map-pin class="w-4 h-4 text-error shrink-0" />
                            <div class="min-w-0">
                                <span class="text-[9px] uppercase font-bold text-base-content/50 block leading-none">Alamat Toko</span>
                                <p class="font-semibold text-base-content text-xs truncate mt-0.5" :title="detailData?.customer_address" x-text="detailData?.customer_address || '-'"></p>
                            </div>
                        </div>

                        {{-- Titik Master Lama --}}
                        <div class="md:col-span-2 border-t md:border-t-0 md:border-l border-base-300 pt-1 md:pt-0 md:pl-3 min-w-0">
                            <span class="text-[9px] uppercase font-bold text-base-content/50 block leading-none">Titik Lama (Master)</span>
                            <div class="font-mono text-xs font-medium text-base-content/70 truncate mt-0.5">
                                <template x-if="detailData?.latitude_old && detailData?.longitude_old">
                                    <span x-text="detailData.latitude_old + ', ' + detailData.longitude_old" :title="detailData.latitude_old + ', ' + detailData.longitude_old"></span>
                                </template>
                                <template x-if="!detailData?.latitude_old || !detailData?.longitude_old">
                                    <span class="text-base-content/40 italic font-sans text-[11px]">Belum ada</span>
                                </template>
                            </div>
                        </div>

                        {{-- Titik Usulan Baru --}}
                        <div class="md:col-span-3 border-t md:border-t-0 md:border-l border-base-300 pt-1 md:pt-0 md:pl-3 min-w-0">
                            <span class="text-[9px] uppercase font-bold text-primary block leading-none">Titik Baru (Usulan)</span>
                            <div class="font-mono text-xs font-bold text-primary truncate mt-0.5 flex items-center justify-between gap-1">
                                <span x-text="detailData?.latitude + ', ' + detailData?.longitude" :title="detailData?.latitude + ', ' + detailData?.longitude"></span>
                                <a :href="'https://www.google.com/maps?q=' + detailData?.latitude + ',' + detailData?.longitude" target="_blank" class="text-error hover:text-error/70 shrink-0" title="Buka di Google Maps">
                                    <x-heroicon-s-map-pin class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </div>

                        {{-- Jarak & Akurasi --}}
                        <div class="md:col-span-2 border-t md:border-t-0 md:border-l border-base-300 pt-1 md:pt-0 md:pl-3 flex items-center justify-between md:justify-around gap-2">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-base-content/50 block leading-none">Geser</span>
                                <span class="font-extrabold text-info text-xs mt-0.5 block" x-text="detailData?.distance !== null ? detailData.distance + ' m' : '-'"></span>
                            </div>
                            <div class="text-right md:text-left">
                                <span class="text-[9px] uppercase font-bold text-base-content/50 block leading-none">Akurasi HP</span>
                                <span class="font-extrabold text-xs mt-0.5 block" :class="detailData?.accuracy <= 15 ? 'text-success' : 'text-warning'" x-text="detailData?.accuracy ? detailData.accuracy + ' m' : '-'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Jika Status Rejected: Tampilkan Alasan Penolakan Singkat --}}
                <template x-if="detailData?.status === 'Rejected'">
                    <div class="px-3.5 py-1.5 bg-error/10 border border-error/20 rounded-xl text-xs text-error flex items-center gap-2 shrink-0">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 shrink-0" />
                        <span class="font-bold shrink-0">Alasan Penolakan:</span>
                        <span class="font-medium truncate" :title="detailData?.keterangan" x-text="detailData?.keterangan || 'Tidak ada catatan penolakan.'"></span>
                    </div>
                </template>

                {{-- Grid Visual Utama: Foto Bukti Toko & Street View Berukuran Besar --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 flex-1 min-h-[500px]">
                    
                    {{-- SISI KIRI: Foto Bukti Toko --}}
                    <div class="bg-base-200/40 p-2.5 rounded-2xl border border-base-300 flex flex-col h-full min-h-[460px]">
                        <div class="flex items-center justify-between mb-1.5 px-1 shrink-0">
                            <div class="flex items-center gap-1.5">
                                <h4 class="font-extrabold text-xs md:text-sm flex items-center gap-1.5 text-base-content">
                                    <x-heroicon-s-camera class="w-4 h-4 text-primary" />
                                    <span>Foto Fisik Toko</span>
                                </h4>
                                {{-- Badge Zoom --}}
                                <template x-if="detailPhotoZoom !== 1">
                                    <span class="badge badge-xs badge-info/20 text-info font-bold text-[10px]" x-text="Math.round(detailPhotoZoom * 100) + '%'"></span>
                                </template>
                                {{-- Badge Rotasi --}}
                                <template x-if="detailPhotoRotation % 360 !== 0">
                                    <span class="badge badge-xs badge-ghost text-[10px]" x-text="((detailPhotoRotation % 360 + 360) % 360) + '°'"></span>
                                </template>
                            </div>
                            <template x-if="detailData?.foto_url">
                                <div class="flex items-center gap-1">
                                    {{-- Zoom Out --}}
                                    <button type="button" 
                                        @click="detailPhotoZoom = Math.max(0.5, Math.round((detailPhotoZoom - 0.25) * 100) / 100); if(detailPhotoZoom <= 1) { detailPhotoPanX = 0; detailPhotoPanY = 0; }" 
                                        :disabled="detailPhotoZoom <= 0.5"
                                        class="btn btn-xs btn-ghost btn-square text-base-content/70 hover:text-primary hover:bg-base-200 disabled:opacity-30" 
                                        title="Perkecil Foto (Zoom Out)"
                                    >
                                        <x-heroicon-o-magnifying-glass-minus class="w-3.5 h-3.5" />
                                    </button>

                                    {{-- Zoom In --}}
                                    <button type="button" 
                                        @click="detailPhotoZoom = Math.min(3, Math.round((detailPhotoZoom + 0.25) * 100) / 100)" 
                                        :disabled="detailPhotoZoom >= 3"
                                        class="btn btn-xs btn-ghost btn-square text-base-content/70 hover:text-primary hover:bg-base-200 disabled:opacity-30" 
                                        title="Perbesar Foto (Zoom In)"
                                    >
                                        <x-heroicon-o-magnifying-glass-plus class="w-3.5 h-3.5" />
                                    </button>

                                    <div class="h-3 w-px bg-base-300 mx-0.5"></div>

                                    {{-- Putar Kiri --}}
                                    <button type="button" 
                                        @click="detailPhotoRotation -= 90" 
                                        class="btn btn-xs btn-ghost btn-square text-base-content/70 hover:text-primary hover:bg-base-200" 
                                        title="Putar 90° ke Kiri"
                                    >
                                        <x-heroicon-s-arrow-uturn-left class="w-3.5 h-3.5" />
                                    </button>

                                    {{-- Putar Kanan --}}
                                    <button type="button" 
                                        @click="detailPhotoRotation += 90" 
                                        class="btn btn-xs btn-ghost btn-square text-base-content/70 hover:text-primary hover:bg-base-200" 
                                        title="Putar 90° ke Kanan"
                                    >
                                        <x-heroicon-s-arrow-uturn-right class="w-3.5 h-3.5" />
                                    </button>

                                    {{-- Reset Rotasi & Zoom --}}
                                    <template x-if="detailPhotoRotation % 360 !== 0 || detailPhotoZoom !== 1 || detailPhotoPanX !== 0 || detailPhotoPanY !== 0">
                                        <button type="button" 
                                            @click="detailPhotoRotation = 0; detailPhotoZoom = 1; detailPhotoPanX = 0; detailPhotoPanY = 0;" 
                                            class="btn btn-xs btn-ghost text-error text-[10px] px-1.5 h-6 min-h-0 font-bold ml-0.5" 
                                            title="Kembalikan Posisi & Ukuran Normal"
                                        >
                                            Reset
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div class="relative w-full flex-1 rounded-xl overflow-hidden bg-base-300/40 border border-base-300 flex items-center justify-center min-h-[400px]">
                            <template x-if="detailData?.foto_url">
                                <div class="w-full h-full flex items-center justify-center p-2 select-none overflow-hidden"
                                     :class="detailPhotoZoom > 1 ? (isPanning ? 'cursor-grabbing' : 'cursor-grab') : 'cursor-default'"
                                     @mousedown="if (detailPhotoZoom > 1) { isPanning = true; panStartX = $event.clientX - detailPhotoPanX; panStartY = $event.clientY - detailPhotoPanY; }"
                                     @mousemove="if (isPanning && detailPhotoZoom > 1) { detailPhotoPanX = $event.clientX - panStartX; detailPhotoPanY = $event.clientY - panStartY; }"
                                     @mouseup="isPanning = false"
                                     @mouseleave="isPanning = false"
                                     @wheel.prevent="
                                         if ($event.deltaY < 0) { 
                                             detailPhotoZoom = Math.min(3, Math.round((detailPhotoZoom + 0.25) * 100) / 100); 
                                         } else { 
                                             detailPhotoZoom = Math.max(0.5, Math.round((detailPhotoZoom - 0.25) * 100) / 100); 
                                             if (detailPhotoZoom <= 1) { detailPhotoPanX = 0; detailPhotoPanY = 0; } 
                                         }
                                     ">
                                    <img :src="detailData.foto_url" 
                                         draggable="false"
                                         class="max-w-full max-h-full object-contain rounded-lg select-none pointer-events-none" 
                                         :class="isPanning ? 'transition-none' : 'transition-transform duration-200 ease-out'"
                                         :style="`transform: translate(${detailPhotoPanX}px, ${detailPhotoPanY}px) rotate(${detailPhotoRotation}deg) scale(${detailPhotoZoom * (Math.abs(detailPhotoRotation % 180) === 90 ? 0.75 : 1)});`" 
                                         alt="Foto Fisik Toko" />
                                </div>
                            </template>
                            <template x-if="detailPhotoZoom > 1">
                                <div class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm text-white text-[10px] px-2 py-0.5 rounded-md pointer-events-none opacity-75">
                                    Geser (drag) foto untuk melihat bagian lain
                                </div>
                            </template>
                            <template x-if="!detailData?.foto_url">
                                <div class="flex flex-col items-center justify-center text-base-content/40 p-6 text-center">
                                    <x-heroicon-o-photo class="w-12 h-12 mb-2 stroke-1" />
                                    <span class="text-xs font-semibold">Tidak ada foto bukti yang dilampirkan</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- SISI KANAN: Street View dari Titik Perbaikan (Ukuran Maksimal & Interaktif) --}}
                    <div class="bg-base-200/40 p-2.5 rounded-2xl border border-base-300 flex flex-col h-full min-h-[460px]">
                        <div class="flex items-center justify-between mb-1.5 px-1 shrink-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-extrabold text-xs md:text-sm flex items-center gap-1.5 text-base-content">
                                    <x-heroicon-s-map-pin class="w-4 h-4 text-error" />
                                    <span>Street View Titik Usulan (360° Panorama)</span>
                                </h4>
                                <span class="badge badge-xs badge-info text-white font-bold text-[10px]">Interaktif</span>
                            </div>
                            <template x-if="detailData?.latitude && detailData?.longitude">
                                <a :href="'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' + detailData.latitude + ',' + detailData.longitude" target="_blank" class="btn btn-xs btn-ghost text-error gap-1" title="Buka Street View di Tab Baru Google Maps">
                                    <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                    <span>Tab Baru</span>
                                </a>
                            </template>
                        </div>

                        <div class="relative w-full flex-1 rounded-xl overflow-hidden bg-base-300 border border-base-300 min-h-[400px]">
                            <template x-if="detailData?.latitude && detailData?.longitude">
                                <iframe 
                                    class="w-full h-full border-0 absolute inset-0"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    :src="'https://maps.google.com/maps?q=&layer=c&cbll=' + detailData.latitude + ',' + detailData.longitude + '&cbp=11,0,0,0,0&output=svembed'"
                                ></iframe>
                            </template>
                            <template x-if="!detailData?.latitude || !detailData?.longitude">
                                <div class="flex flex-col items-center justify-center text-base-content/40 p-6 text-center h-full">
                                    <x-heroicon-o-map class="w-12 h-12 mb-2 stroke-1" />
                                    <span class="text-xs font-semibold">Koordinat titik perbaikan tidak tersedia</span>
                                </div>
                            </template>
                        </div>
                        
                        <div class="flex items-center justify-between text-[11px] text-base-content/60 mt-1 px-1 shrink-0">
                            <span>*Putar 360° & zoom langsung di dalam panel tanpa perlu buka maps</span>
                            <a :href="'https://www.google.com/maps?q=' + detailData?.latitude + ',' + detailData?.longitude" target="_blank" class="text-primary hover:underline font-semibold flex items-center gap-1 shrink-0">
                                Buka di Google Maps →
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer Modal: Waktu Pengajuan & Action Buttons --}}
            <div class="px-4 py-2.5 md:px-5 md:py-3 border-t border-base-300 bg-base-200/60 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2 text-xs text-base-content/60">
                    <div>Diajukan: <span class="font-semibold text-base-content/80" x-text="detailData?.created_at_formatted"></span></div>
                    <template x-if="detailData?.status !== 'Pending'">
                        <div>• Diproses: <span class="font-semibold text-base-content/80" x-text="detailData?.updated_at_formatted"></span></div>
                    </template>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" @click="showDetailModal = false" class="btn btn-sm btn-ghost rounded-xl">
                        Tutup
                    </button>
                    <button type="button" 
                        @click="showDetailModal = false; $dispatch('open-map-modal', { lat1: detailData?.latitude_old || '', lon1: detailData?.longitude_old || '', lat2: detailData?.latitude, lon2: detailData?.longitude, dist: detailData?.distance || '' })" 
                        class="btn btn-sm btn-outline btn-info rounded-xl gap-1.5"
                        title="Buka Peta Perbandingan Dua Titik"
                    >
                        <x-heroicon-s-map class="w-4 h-4" />
                        <span>Peta Perbandingan</span>
                    </button>

                    @if($this->canManage)
                        <template x-if="detailData?.status === 'Pending'">
                            <div class="flex items-center gap-2">
                                {{-- Tombol Tolak --}}
                                <button 
                                    type="button" 
                                    @click="showDetailModal = false; $wire.promptReject(detailData.id);" 
                                    class="btn btn-sm btn-error text-white rounded-xl gap-1.5 shadow-sm shadow-error/20"
                                    title="Tolak Usulan Perbaikan Ini"
                                >
                                    <x-heroicon-s-x-circle class="w-4 h-4" />
                                    <span>Tolak</span>
                                </button>

                                {{-- Tombol Setujui --}}
                                <button 
                                    type="button" 
                                    @click="if(confirm('Yakin ingin menyetujui perbaikan koordinat toko ' + (detailData.customer_name || detailData.customer_code) + '?')) { showDetailModal = false; $wire.approve(detailData.id); }" 
                                    class="btn btn-sm btn-success text-white rounded-xl gap-1.5 shadow-sm shadow-success/20"
                                    title="Setujui Usulan Perbaikan Ini"
                                >
                                    <x-heroicon-s-check-circle class="w-4 h-4" />
                                    <span>Setujui</span>
                                </button>
                            </div>
                        </template>
                    @endif

                    <template x-if="detailData?.status === 'Approved'">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-success bg-success/10 border border-success/20 px-3 py-1.5 rounded-xl">
                            <x-heroicon-s-check-circle class="w-4 h-4" />
                            <span>Usulan Disetujui</span>
                        </div>
                    </template>

                    <template x-if="detailData?.status === 'Rejected'">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-error bg-error/10 border border-error/20 px-3 py-1.5 rounded-xl">
                            <x-heroicon-s-x-circle class="w-4 h-4" />
                            <span>Usulan Ditolak</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>



    {{-- MODAL PENOLAKAN --}}
    <div x-show="showRejectModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
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
    <div x-show="showReasonModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
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
    <div x-show="showSummaryModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
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
                            <td class="font-mono">{{ $summary->sales_code }}</td>
                            <td class="font-bold">{{ $summary->sales_name ?? '-' }}</td>
                            <td class="text-center font-bold text-base-content">{{ $summary->total_pengajuan }}</td>
                            <td class="text-center font-bold text-warning">{{ $summary->pending }}</td>
                            <td class="text-center font-bold text-error">{{ $summary->rejected }}</td>
                            <td class="text-center font-bold text-success">{{ $summary->approved }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-base-content/50">Tidak ada data summary.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="text-sm font-bold bg-base-200/80 border-t-2 border-base-300">
                        <tr>
                            <td colspan="5" class="text-right uppercase tracking-wider">Total Keseluruhan</td>
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

    {{-- MODAL MAP PERBANDINGAN (MAPLIBRE GL DUAL BASEMAP) --}}
    <div x-show="showMapModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-3 md:p-6 bg-black/70 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-5xl overflow-hidden flex flex-col h-[85vh] border border-base-300" @click.outside="showMapModal = false">
            {{-- Header Modal --}}
            <div class="px-4 py-3 md:px-5 md:py-3.5 border-b border-base-300 flex flex-wrap items-center justify-between gap-3 bg-base-200/60 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-info/10 text-info rounded-xl">
                        <x-heroicon-s-map class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base text-base-content">Peta Perbandingan Titik</h3>
                            <template x-if="mapDist">
                                <span class="badge badge-error text-white font-bold text-xs shadow-sm" x-text="'Geser: ' + mapDist + ' meter'"></span>
                            </template>
                        </div>
                        <p class="text-[11px] text-base-content/60">Perbandingan titik master (lama) vs usulan perbaikan sales</p>
                    </div>
                </div>

                {{-- Layer Switcher: Carto Voyager vs Satelit --}}
                <div class="flex items-center gap-2">
                    <div class="join bg-base-100 p-0.5 border border-base-300 rounded-xl shadow-sm">
                        <button type="button" 
                            @click="mapLayerMode = 'carto'; window.switchComparisonMapLayer && window.switchComparisonMapLayer('carto')" 
                            :class="mapLayerMode === 'carto' ? 'btn-active btn-primary text-white font-bold shadow-sm' : 'btn-ghost text-base-content/70 hover:text-base-content'" 
                            class="btn btn-xs join-item gap-1.5 transition-all">
                            <x-heroicon-s-map class="w-3.5 h-3.5" />
                            <span>Jalan (Carto)</span>
                        </button>
                        <button type="button" 
                            @click="mapLayerMode = 'satellite'; window.switchComparisonMapLayer && window.switchComparisonMapLayer('satellite')" 
                            :class="mapLayerMode === 'satellite' ? 'btn-active btn-primary text-white font-bold shadow-sm' : 'btn-ghost text-base-content/70 hover:text-base-content'" 
                            class="btn btn-xs join-item gap-1.5 transition-all">
                            <x-heroicon-m-globe-asia-australia class="w-3.5 h-3.5" />
                            <span>Satelit (Fisik)</span>
                        </button>
                    </div>

                    <button type="button" @click="showMapModal = false" class="btn btn-sm btn-circle btn-ghost text-base-content/60 hover:text-base-content">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
            </div>

            {{-- Map Container & Floating Quick Action Buttons --}}
            <div class="p-0 relative bg-base-300 w-full flex-1 min-h-[420px] overflow-hidden">
                <div id="maplibre-comparison-container" class="absolute inset-0 w-full h-full z-0"></div>

                {{-- Floating Action Bar on top of map --}}
                <div class="absolute bottom-4 left-4 z-10 flex flex-wrap items-center gap-1.5 bg-base-100/90 backdrop-blur-md p-1.5 rounded-xl border border-base-300 shadow-lg">
                    <button type="button" onclick="window.focusComparisonBoth && window.focusComparisonBoth()" class="btn btn-xs btn-ghost gap-1 font-semibold text-xs text-base-content/80 hover:text-primary" title="Lihat Kedua Titik Bersama">
                        <x-heroicon-m-arrows-pointing-out class="w-3.5 h-3.5" />
                        <span>Lihat Keduanya</span>
                    </button>
                    <div class="divider divider-horizontal m-0 py-1"></div>
                    <button type="button" onclick="window.focusComparisonNew && window.focusComparisonNew()" class="btn btn-xs btn-ghost gap-1.5 font-semibold text-xs text-emerald-600 hover:bg-emerald-50" title="Zoom ke Titik Usulan Baru">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm"></span>
                        <span>Fokus Usulan Baru</span>
                    </button>
                    <button type="button" onclick="window.focusComparisonOld && window.focusComparisonOld()" class="btn btn-xs btn-ghost gap-1.5 font-semibold text-xs text-amber-600 hover:bg-amber-50" title="Zoom ke Titik Master Lama">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm"></span>
                        <span>Fokus Master Lama</span>
                    </button>
                </div>
            </div>

            {{-- Footer Modal: Legend & Close --}}
            <div class="px-4 py-2.5 md:px-5 md:py-3 border-t border-base-300 bg-base-200/50 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm"></span>
                        <span class="font-medium text-base-content/80">Titik Usulan Baru (Sales)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500 shadow-sm"></span>
                        <span class="font-medium text-base-content/80">Titik Master Lama (Distributor)</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-rose-500 font-medium">
                        <span class="w-4 border-t-2 border-dashed border-rose-500"></span>
                        <span>Garis Geser</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="if(window.comparisonMapInstance) { window.comparisonMapInstance.resize(); window.focusComparisonBoth && window.focusComparisonBoth(); }" class="btn btn-sm btn-ghost gap-1.5 text-xs">
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                        <span>Reset View</span>
                    </button>
                    <button type="button" @click="showMapModal = false" class="btn btn-sm btn-primary rounded-xl px-4">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EXPORT --}}
    <div x-show="showExportModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-base-100 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col" @click.outside="showExportModal = false">
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <div class="flex items-center gap-2 text-success">
                    <x-heroicon-s-arrow-down-tray class="w-5 h-5" />
                    <h3 class="font-bold text-base">Export Data (Waktu Proses)</h3>
                </div>
                <button type="button" @click="showExportModal = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="label">
                        <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/70">Mulai Tanggal Proses</span>
                    </label>
                    <input wire:model="exportDateStart" type="date" class="input input-bordered w-full bg-base-50 focus:border-success focus:ring-success" />
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-bold text-xs uppercase tracking-wider text-base-content/70">Sampai Tanggal Proses</span>
                    </label>
                    <input wire:model="exportDateEnd" type="date" class="input input-bordered w-full bg-base-50 focus:border-success focus:ring-success" />
                </div>
                <div class="text-[11px] leading-tight text-base-content/60 italic p-3 bg-info/10 rounded-xl border border-info/20">
                    *Filter ini digunakan untuk mengekspor data yang statusnya <strong class="text-success">Approved</strong> pada rentang tanggal tersebut (berdasarkan update_at).
                </div>
            </div>
            <div class="p-4 border-t border-base-300 bg-base-200/50 flex justify-end gap-2">
                <button type="button" @click="showExportModal = false" class="btn btn-ghost btn-sm rounded-xl">Batal</button>
                <button type="button" wire:click="export" @click="showExportModal = false" class="btn btn-success btn-sm text-white rounded-xl shadow-sm shadow-success/30" wire:loading.attr="disabled" wire:target="export">
                    <span wire:loading wire:target="export" class="loading loading-spinner loading-xs mr-1"></span>
                    Download Excel
                </button>
            </div>
        </div>
    </div>

    {{-- LOCAL TOAST NOTIFICATION --}}
    <div x-show="copyToast.show" class="toast toast-top toast-center z-[100010] mt-16" style="display: none;" x-transition.opacity.duration.300ms>
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
                container.className = 'toast toast-top toast-center z-[100010] pt-4';
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

        // ==========================================
        // MAPLIBRE GL COMPARISON MAP ENGINE
        // ==========================================
        window.comparisonMapInstance = null;
        window.comparisonMapDetail = null;

        function ensureMapLibre(callback) {
            if (typeof maplibregl !== 'undefined') {
                callback();
                return;
            }
            if (!document.getElementById('maplibre-gl-css')) {
                const css = document.createElement('link');
                css.id = 'maplibre-gl-css';
                css.rel = 'stylesheet';
                css.href = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css';
                document.head.appendChild(css);
            }
            if (!document.getElementById('maplibre-gl-js')) {
                const script = document.createElement('script');
                script.id = 'maplibre-gl-js';
                script.src = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js';
                script.onload = () => callback();
                document.head.appendChild(script);
            } else {
                const check = setInterval(() => {
                    if (typeof maplibregl !== 'undefined') {
                        clearInterval(check);
                        callback();
                    }
                }, 100);
            }
        }

        window.renderComparisonMap = function(detail) {
            window.comparisonMapDetail = detail;
            ensureMapLibre(() => {
                const lat1 = parseFloat(detail.lat1);
                const lon1 = parseFloat(detail.lon1);
                const lat2 = parseFloat(detail.lat2);
                const lon2 = parseFloat(detail.lon2);
                const hasOld = !isNaN(lat1) && !isNaN(lon1) && lat1 !== 0 && lon1 !== 0;
                const hasNew = !isNaN(lat2) && !isNaN(lon2) && lat2 !== 0 && lon2 !== 0;

                if (!hasNew && !hasOld) {
                    window.dispatchEvent(new CustomEvent('show-toast', { detail: { type: 'error', message: 'Koordinat tidak valid untuk ditampilkan di peta.' } }));
                    return;
                }

                const container = document.getElementById('maplibre-comparison-container');
                if (!container) return;

                if (window.comparisonMapInstance) {
                    try { window.comparisonMapInstance.remove(); } catch(e){}
                    window.comparisonMapInstance = null;
                }

                const initialCenter = hasNew ? [lon2, lat2] : [lon1, lat1];

                const map = new maplibregl.Map({
                    container: 'maplibre-comparison-container',
                    style: {
                        version: 8,
                        sources: {
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
                                attribution: '&copy; CARTO'
                            },
                            'esri-satellite': {
                                type: 'raster',
                                tiles: [
                                    'https://mt0.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
                                    'https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
                                    'https://mt2.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
                                    'https://mt3.google.com/vt/lyrs=y&x={x}&y={y}&z={z}'
                                ],
                                tileSize: 256,
                                maxzoom: 20,
                                attribution: '&copy; Google Hybrid'
                            }
                        },
                        layers: [
                            {
                                id: 'base-carto',
                                type: 'raster',
                                source: 'carto-voyager',
                                layout: { visibility: 'visible' }
                            },
                            {
                                id: 'base-satellite',
                                type: 'raster',
                                source: 'esri-satellite',
                                layout: { visibility: 'none' }
                            }
                        ]
                    },
                    center: initialCenter,
                    zoom: 16
                });

                window.comparisonMapInstance = map;

                map.addControl(new maplibregl.NavigationControl({
                    showCompass: true,
                    visualizePitch: true
                }), 'top-right');

                map.on('load', () => {
                    map.resize();
                    setTimeout(() => { try { map.resize(); } catch(e){} }, 250);
                    setTimeout(() => { try { map.resize(); } catch(e){} }, 500);

                    // 1. Titik Master Lama
                    if (hasOld) {
                        const elOld = document.createElement('div');
                        elOld.className = 'flex flex-col items-center cursor-pointer select-none group';
                        elOld.innerHTML = `
                            <span class="bg-amber-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-full shadow-lg border border-white whitespace-nowrap mb-1">
                                Titik Master (Lama)
                            </span>
                            <div class="relative w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center shadow-xl border-2 border-white ring-2 ring-amber-500/30">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                            </div>
                        `;
                        new maplibregl.Marker({ element: elOld, anchor: 'bottom' })
                            .setLngLat([lon1, lat1])
                            .setPopup(new maplibregl.Popup({ offset: 15 }).setHTML(`
                                <div class="p-2 text-xs">
                                    <div class="font-bold text-amber-600 mb-1">Titik Master (Lama)</div>
                                    <div class="font-mono text-gray-700">${lat1.toFixed(6)}, ${lon1.toFixed(6)}</div>
                                </div>
                            `))
                            .addTo(map);
                    }

                    // 2. Titik Usulan Baru
                    if (hasNew) {
                        const elNew = document.createElement('div');
                        elNew.className = 'flex flex-col items-center cursor-pointer select-none group';
                        elNew.innerHTML = `
                            <span class="bg-emerald-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-full shadow-lg border border-white whitespace-nowrap mb-1">
                                Titik Usulan (Baru)
                            </span>
                            <div class="relative flex items-center justify-center">
                                <span class="absolute w-8 h-8 rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                                <div class="relative w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xl border-2 border-white ring-2 ring-emerald-600/30">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                        `;
                        new maplibregl.Marker({ element: elNew, anchor: 'bottom' })
                            .setLngLat([lon2, lat2])
                            .setPopup(new maplibregl.Popup({ offset: 15 }).setHTML(`
                                <div class="p-2 text-xs">
                                    <div class="font-bold text-emerald-600 mb-1">Titik Usulan (Baru)</div>
                                    <div class="font-mono text-gray-700">${lat2.toFixed(6)}, ${lon2.toFixed(6)}</div>
                                </div>
                            `))
                            .addTo(map);
                    }

                    // 3. Garis dan Badge Jarak
                    if (hasOld && hasNew) {
                        map.addSource('comparison-line', {
                            type: 'geojson',
                            data: {
                                type: 'Feature',
                                geometry: {
                                    type: 'LineString',
                                    coordinates: [
                                        [lon1, lat1],
                                        [lon2, lat2]
                                    ]
                                }
                            }
                        });

                        map.addLayer({
                            id: 'comparison-line-layer',
                            type: 'line',
                            source: 'comparison-line',
                            paint: {
                                'line-color': '#f43f5e',
                                'line-width': 3,
                                'line-dasharray': [2, 2]
                            }
                        });

                        if (detail.dist) {
                            const midEl = document.createElement('div');
                            midEl.className = 'bg-rose-600 text-white font-bold text-[11px] px-2.5 py-0.5 rounded-full shadow-xl border-2 border-white whitespace-nowrap select-none';
                            midEl.innerText = `${detail.dist}m`;
                            new maplibregl.Marker({ element: midEl, anchor: 'center' })
                                .setLngLat([(lon1 + lon2) / 2, (lat1 + lat2) / 2])
                                .addTo(map);
                        }

                        const bounds = new maplibregl.LngLatBounds();
                        bounds.extend([lon1, lat1]);
                        bounds.extend([lon2, lat2]);
                        map.fitBounds(bounds, { padding: 90, maxZoom: 18, duration: 800 });
                    } else if (hasNew) {
                        map.flyTo({ center: [lon2, lat2], zoom: 17, duration: 800 });
                    } else if (hasOld) {
                        map.flyTo({ center: [lon1, lat1], zoom: 17, duration: 800 });
                    }
                });
            });
        };

        window.switchComparisonMapLayer = function(mode) {
            if (!window.comparisonMapInstance) return;
            const map = window.comparisonMapInstance;
            if (map.getLayer('base-carto') && map.getLayer('base-satellite')) {
                map.setLayoutProperty('base-carto', 'visibility', mode === 'carto' ? 'visible' : 'none');
                map.setLayoutProperty('base-satellite', 'visibility', mode === 'satellite' ? 'visible' : 'none');
            }
        };

        window.focusComparisonBoth = function() {
            if (!window.comparisonMapInstance || !window.comparisonMapDetail) return;
            const d = window.comparisonMapDetail;
            const lat1 = parseFloat(d.lat1);
            const lon1 = parseFloat(d.lon1);
            const lat2 = parseFloat(d.lat2);
            const lon2 = parseFloat(d.lon2);
            const hasOld = !isNaN(lat1) && !isNaN(lon1) && lat1 !== 0 && lon1 !== 0;
            const hasNew = !isNaN(lat2) && !isNaN(lon2) && lat2 !== 0 && lon2 !== 0;

            if (hasOld && hasNew) {
                const bounds = new maplibregl.LngLatBounds();
                bounds.extend([lon1, lat1]);
                bounds.extend([lon2, lat2]);
                window.comparisonMapInstance.fitBounds(bounds, { padding: 90, maxZoom: 18, pitch: 0, bearing: 0, duration: 800 });
            } else if (hasNew) {
                window.comparisonMapInstance.flyTo({ center: [lon2, lat2], zoom: 17, pitch: 0, bearing: 0, duration: 800 });
            } else if (hasOld) {
                window.comparisonMapInstance.flyTo({ center: [lon1, lat1], zoom: 17, pitch: 0, bearing: 0, duration: 800 });
            }
        };

        window.focusComparisonNew = function() {
            if (!window.comparisonMapInstance || !window.comparisonMapDetail) return;
            const d = window.comparisonMapDetail;
            const lat = parseFloat(d.lat2);
            const lon = parseFloat(d.lon2);
            if (!isNaN(lat) && !isNaN(lon) && lat !== 0 && lon !== 0) {
                window.comparisonMapInstance.flyTo({ center: [lon, lat], zoom: 18, pitch: 35, duration: 800 });
            }
        };

        window.focusComparisonOld = function() {
            if (!window.comparisonMapInstance || !window.comparisonMapDetail) return;
            const d = window.comparisonMapDetail;
            const lat = parseFloat(d.lat1);
            const lon = parseFloat(d.lon1);
            if (!isNaN(lat) && !isNaN(lon) && lat !== 0 && lon !== 0) {
                window.comparisonMapInstance.flyTo({ center: [lon, lat], zoom: 18, pitch: 35, duration: 800 });
            }
        };
    </script>
    </div>{{-- END wire:ignore --}}
</div>

@push('styles')
<link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
<style>
    .maplibregl-popup-content {
        border-radius: 12px;
        padding: 8px 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
@endpush
