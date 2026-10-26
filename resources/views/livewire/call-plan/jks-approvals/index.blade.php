<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
     x-data="{ 
        toasts: [], 
        addToast(toast) {
            const id = Date.now();
            this.toasts.push({ id, ...toast });
            setTimeout(() => {
                this.removeToast(id);
            }, 3000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
        showRejectModal: false,
        showPrcModal: false,
        showDrawer: false,
        drawerData: null,
        openDrawer(payload) {
            this.drawerData = payload;
            this.showDrawer = true;
        },
        expandedGroups: [],
        toggleGroup(key) {
            if (this.expandedGroups.includes(key)) {
                this.expandedGroups = this.expandedGroups.filter(k => k !== key);
            } else {
                this.expandedGroups.push(key);
            }
        },
        expandAll(keys) {
            this.expandedGroups = [...keys];
        },
        collapseAll() {
            this.expandedGroups = [];
        },
        isExpanded(key) {
            return Boolean($wire.search) || this.expandedGroups.includes(key);
        }
    }"
    @toast.window="addToast(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)"
    @open-reject-modal.window="showRejectModal = true"
    @close-reject-modal.window="showRejectModal = false"
    @open-prc-modal.window="showPrcModal = true"
    @close-prc-modal.window="showPrcModal = false"
    @open-dynamic-drawer.window="openDrawer(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)">
    
    <x-slot name="title">Pusat Persetujuan JKS</x-slot>

    <x-ui.tab-menu>
        <a href="#" class="tab">Dashboard</a>
        <a href="{{ route('call-plan.jks-summary') }}" class="tab">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab">Detail</a>
        <a href="#" class="tab">Maps</a>
        <a href="{{ route('call-plan.jks-non-route') }}" class="tab">Outlet non JKS</a>
        <a href="{{ route('call-plan.jks-approvals') }}" class="tab tab-active bg-primary text-primary-content">
            Persetujuan
            @if(($kpi['PENDING'] ?? 0) > 0)
                <span class="badge badge-sm badge-error ml-2">{{ $kpi['PENDING'] }}</span>
            @endif
        </a>
    </x-ui.tab-menu>

    {{-- KPI Cards Section (Tetap statis di atas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- KPI Menunggu --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border {{ $filterStatus === 'PENDING' ? 'border-warning ring-1 ring-warning' : 'border-base-300 hover:border-warning/50' }} flex flex-col relative overflow-hidden group cursor-pointer transition-all" wire:click="$set('filterStatus', 'PENDING')">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Menunggu</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-clock class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">{{ $kpi['PENDING'] ?? 0 }}</div>
        </div>

        {{-- KPI Disetujui --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border {{ $filterStatus === 'APPROVED' ? 'border-success ring-1 ring-success' : 'border-base-300 hover:border-success/50' }} flex flex-col relative overflow-hidden group cursor-pointer transition-all" wire:click="$set('filterStatus', 'APPROVED')">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Disetujui</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ $kpi['APPROVED'] ?? 0 }}</div>
        </div>

        {{-- KPI Ditolak --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border {{ $filterStatus === 'REJECTED' ? 'border-error ring-1 ring-error' : 'border-base-300 hover:border-error/50' }} flex flex-col relative overflow-hidden group cursor-pointer transition-all" wire:click="$set('filterStatus', 'REJECTED')">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Ditolak</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ $kpi['REJECTED'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions Toolbar --}}
        <div class="p-2.5 md:p-3.5 border-b border-base-300 shrink-0 flex flex-wrap items-center justify-between gap-2.5 bg-base-200/30">
            
            {{-- Sisi Kiri: Bulk Actions & Accordion Controls --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Bulk Actions (Hanya muncul jika ada yang dipilih dan user memiliki role yang diizinkan) --}}
                @if($filterStatus === 'PENDING' && count($selected) > 0)
                    @if(auth()->check() && auth()->user()->hasRole(['admin', 'user', 'spm']))
                        <div class="flex items-center gap-2 mr-1">
                            <span class="badge badge-primary">{{ count($selected) }} terpilih</span>
                            <button class="btn btn-sm btn-success text-white" wire:click="bulkApprove" wire:confirm="Setujui {{ count($selected) }} pengajuan?">
                                <x-heroicon-s-check class="w-4 h-4" /> Setujui
                            </button>
                            <button class="btn btn-sm btn-error text-white" wire:click="confirmBulkReject">
                                <x-heroicon-s-x-mark class="w-4 h-4" /> Tolak
                            </button>
                        </div>
                    @else
                        <div class="flex items-center gap-2 mr-1">
                            <span class="badge badge-primary">{{ count($selected) }} terpilih</span>
                            <span class="text-xs text-base-content/50 italic">Tidak ada akses persetujuan</span>
                        </div>
                    @endif
                @endif

                {{-- Expand / Collapse All Buttons --}}
                <div class="flex items-center gap-1 bg-base-100 p-0.5 rounded-xl border border-base-300 shadow-sm">
                    <button type="button" 
                            @click="expandAll({{ json_encode(array_keys($groupedApprovals)) }})" 
                            class="btn btn-xs btn-ghost gap-1 text-base-content/70 hover:text-primary rounded-lg font-medium"
                            title="Buka semua grup salesman">
                        <x-heroicon-m-arrows-pointing-out class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Buka Semua</span>
                    </button>
                    <div class="w-[1px] h-4 bg-base-300"></div>
                    <button type="button" 
                            @click="collapseAll()" 
                            class="btn btn-xs btn-ghost gap-1 text-base-content/70 hover:text-primary rounded-lg font-medium"
                            title="Tutup semua grup salesman">
                        <x-heroicon-m-arrows-pointing-in class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Tutup Semua</span>
                    </button>
                </div>
            </div>

            {{-- Sisi Kanan: Search & Filters & Actions --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 grow sm:grow-0">
                
                {{-- Smart Date Range Filter (Kontekstual: Tgl Pengajuan jika PENDING, Tgl Disetujui/Diperiksa jika APPROVED/REJECTED) --}}
                <div class="flex items-center gap-1 bg-base-100 p-0.5 rounded-xl border border-base-300 shadow-sm">
                    <span class="text-xs uppercase font-bold text-base-content/50 pl-2 hidden xl:inline">
                        Tgl {{ $filterStatus === 'PENDING' ? 'Pengajuan' : ($filterStatus === 'APPROVED' ? 'Disetujui' : 'Ditolak') }}:
                    </span>
                    <x-ui.filter-date-range 
                        start="startDate" 
                        end="endDate" 
                        :maxDays="0" 
                        :presets="true" 
                        :stepper="true" 
                        class="border-none shadow-none bg-transparent" />
                    @if(!empty($startDate) || !empty($endDate))
                        <button type="button" 
                                wire:click="resetDateFilter" 
                                class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error/10 mr-1" 
                                title="Hapus filter tanggal">
                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>

                {{-- Search --}}
                <div class="relative min-w-48 grow sm:grow-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-base-content/50" />
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari data..." class="input input-sm input-bordered w-full pl-9 rounded-xl focus:outline-none focus:border-primary">
                </div>
                
                {{-- Filter Distributor --}}
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 focus:outline-none focus:border-primary">
                    <option value="">Semua Distributor</option>
                    @foreach($distributorOptions as $dist)
                        <option value="{{ $dist }}">{{ $dist }}</option>
                    @endforeach
                </select>
                
                {{-- Filter Type --}}
                <select wire:model.live="filterType" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0 focus:outline-none focus:border-primary">
                    <option value="">Semua Tipe Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}">{{ str_replace('_', ' ', $type) }}</option>
                    @endforeach
                </select>

                {{-- Export Excel (Khusus Admin dan SPM) --}}
                @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm']))
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-sm btn-success text-white rounded-xl gap-1.5 shadow-sm">
                            <span wire:loading.remove wire:target="exportExcel">
                                <x-heroicon-s-document-arrow-down class="w-4 h-4" />
                            </span>
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                            <span>Export Excel</span>
                            <x-heroicon-m-chevron-down class="w-3.5 h-3.5 opacity-70" />
                        </div>
                        <ul tabindex="0" class="dropdown-content z-30 menu p-1.5 shadow-xl bg-base-100 rounded-xl border border-base-300 w-60 text-xs mt-1">
                            <li class="menu-title text-xs uppercase font-bold tracking-wider text-base-content/60 px-2 py-1">Opsi Laporan Excel</li>
                            <li>
                                <a wire:click="exportExcel(false)" class="flex items-center gap-2 py-2 hover:bg-base-200 rounded-lg">
                                    <x-heroicon-o-funnel class="w-4 h-4 text-warning shrink-0" />
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-base-content">Filter Aktif ({{ $filterStatus }})</span>
                                        <span class="text-xs text-base-content/50">Hanya status {{ strtolower($filterStatus) }}</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a wire:click="exportExcel(true)" class="flex items-center gap-2 py-2 hover:bg-base-200 rounded-lg">
                                    <x-heroicon-o-document-duplicate class="w-4 h-4 text-primary shrink-0" />
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-base-content">Semua Status (All)</span>
                                        <span class="text-xs text-base-content/50">Pending, Approved, Rejected</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">Buka</th>
                        <th class="w-72 font-semibold">Distributor</th>
                        <th class="w-64 font-semibold">Salesman</th>
                        <th class="w-48 font-semibold">Pengajuan Outlet</th>
                        <th class="w-40 font-semibold">Waktu Terakhir</th>
                        <th class="w-44 text-center font-semibold bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] sticky right-0">Pemeriksaan</th>
                    </tr>
                </thead>
                <tbody class="text-sm transition-opacity duration-200 relative" wire:loading.class="opacity-50 grayscale pointer-events-none">
                    @forelse($groupedApprovals as $group)
                        {{-- PARENT ROW: Distributor + Salesman Summary --}}
                        <tr class="transition-all duration-150 cursor-pointer border-b group"
                            :class="isExpanded('{{ $group['key'] }}') 
                                ? 'bg-primary/10 border-l-4 border-l-primary border-b-transparent shadow-xs font-semibold' 
                                : 'bg-base-100 hover:bg-base-200/60 border-l-4 border-l-transparent border-base-200'"
                            @click="toggleGroup('{{ $group['key'] }}')">
                            
                            {{-- Chevron Toggle --}}
                            <td class="text-center w-12" @click.stop="toggleGroup('{{ $group['key'] }}')">
                                <button type="button" class="btn btn-xs btn-circle btn-ghost text-base-content/60 group-hover:text-primary"
                                        :class="isExpanded('{{ $group['key'] }}') ? 'text-primary bg-primary/15' : ''">
                                    <x-heroicon-m-chevron-right class="w-4 h-4 transition-transform duration-200" 
                                                                ::class="isExpanded('{{ $group['key'] }}') ? 'rotate-90 text-primary' : ''" />
                                </button>
                            </td>

                            {{-- Distributor --}}
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-sm font-mono font-bold bg-base-200 border-base-300 text-base-content/80">{{ $group['distributor_code'] }}</span>
                                    <span class="font-bold text-sm text-base-content truncate max-w-xs" title="{{ $group['distributor_name'] }}">{{ $group['distributor_name'] }}</span>
                                </div>
                            </td>

                            {{-- Salesman --}}
                            <td class="whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-sm font-mono font-semibold bg-primary/10 text-primary border-primary/20">{{ $group['salesman_code'] }}</span>
                                    <span class="font-semibold text-sm text-base-content truncate max-w-xs" title="{{ $group['salesman_name'] }}">{{ $group['salesman_name'] }}</span>
                                </div>
                            </td>

                            {{-- Pengajuan & Breakdown --}}
                            <td>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="badge badge-sm badge-neutral font-bold text-white shadow-xs">
                                        {{ $group['total_items'] }} Outlet
                                    </span>
                                    @foreach($group['action_counts'] as $act => $cnt)
                                        @php
                                            $actBadgeClass = match($act) {
                                                'DELETE_MASSAL', 'DELETE_INDIVIDU' => 'badge-error text-white',
                                                'TAMBAH_JADWAL' => 'badge-success text-white',
                                                'EDIT_INDIVIDU' => 'badge-info text-white',
                                                default => 'badge-secondary text-white'
                                            };
                                            $actLabel = match($act) {
                                                'DELETE_MASSAL', 'DELETE_INDIVIDU' => 'Hapus',
                                                'TAMBAH_JADWAL' => 'Tambah',
                                                'EDIT_INDIVIDU' => 'Edit',
                                                default => 'Tukar'
                                            };
                                        @endphp
                                        <span class="badge badge-xs {{ $actBadgeClass }} font-semibold">
                                            {{ $cnt }} {{ $actLabel }}
                                        </span>
                                    @endforeach
                                    @if($group['has_prc_issues'])
                                        <span class="badge badge-xs badge-warning font-semibold gap-1 text-warning-content" title="Perlu input Customer Kode PRC">
                                            <x-heroicon-s-exclamation-triangle class="w-3 h-3" /> PRC
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Waktu Terakhir & SLA Aging --}}
                            <td class="text-xs text-base-content/70 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-base-content">{{ \Carbon\Carbon::parse($group['latest_created_at'])->format('d M Y') }}</span>
                                    @if($filterStatus === 'PENDING' && !empty($group['aging']))
                                        <span class="badge badge-xs {{ $group['aging']['badge_class'] }} font-semibold shadow-2xs">
                                            {{ $group['aging']['label'] }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-base-content/50 leading-tight">{{ \Carbon\Carbon::parse($group['latest_created_at'])->format('H:i') }} WIB</div>
                            </td>

                            {{-- Kolom Pemeriksaan / Expand --}}
                            <td class="text-center border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)] sticky right-0 whitespace-nowrap transition-colors"
                                :class="isExpanded('{{ $group['key'] }}') ? 'bg-primary/15' : 'bg-base-200/40'"
                                @click.stop>
                                @if($filterStatus === 'PENDING')
                                    @php
                                        $checkedCount = count(array_intersect(array_map('strval', $group['approval_ids']), array_map('strval', $selected)));
                                    @endphp
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($checkedCount > 0)
                                            <span class="badge badge-xs badge-primary font-bold">{{ $checkedCount }} Dicentang</span>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-xs gap-1 font-semibold rounded-lg shadow-2xs hover:scale-105 transition-all"
                                                :class="isExpanded('{{ $group['key'] }}') ? 'btn-primary text-white shadow-sm' : 'btn-outline btn-primary'"
                                                @click="toggleGroup('{{ $group['key'] }}')">
                                            <x-heroicon-m-chevron-down class="w-3.5 h-3.5 transition-transform duration-200" 
                                                                       ::class="isExpanded('{{ $group['key'] }}') ? 'rotate-180 text-white' : 'text-primary'" />
                                            <span x-text="isExpanded('{{ $group['key'] }}') ? 'Tutup' : 'Periksa ({{ $group['total_items'] }})'"></span>
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="badge badge-sm {{ $filterStatus === 'APPROVED' ? 'badge-success text-white' : 'badge-error text-white' }} font-semibold">
                                            {{ $filterStatus === 'APPROVED' ? 'Disetujui' : 'Ditolak' }}
                                        </span>
                                        <button type="button" 
                                                class="btn btn-xs btn-ghost btn-circle text-base-content/60 hover:text-primary"
                                                @click="toggleGroup('{{ $group['key'] }}')">
                                            <x-heroicon-m-chevron-down class="w-3.5 h-3.5 transition-transform duration-200" 
                                                                       ::class="isExpanded('{{ $group['key'] }}') ? 'rotate-180 text-primary' : ''" />
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>

                        {{-- CHILD ROW: Sub-table for Expanded Outlets --}}
                        <tr x-show="isExpanded('{{ $group['key'] }}')" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="bg-primary/[0.04] border-l-4 border-l-primary border-b-2 border-b-primary/30">
                            <td colspan="6" class="p-2 sm:p-3 sm:pl-5">
                                <div class="rounded-xl border-2 border-primary/20 bg-base-100 shadow-md overflow-hidden">
                                    {{-- Sub-table Header bar (Tombol persetujuan baru aktif saat dicentang) --}}
                                    @php
                                        $groupSelected = array_values(array_intersect(array_map('strval', $group['approval_ids']), array_map('strval', $selected)));
                                    @endphp
                                    <div class="px-3 py-2.5 bg-primary/[0.08] border-b border-primary/20 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/80 flex items-center gap-1.5">
                                                <x-heroicon-m-building-storefront class="w-4 h-4 text-primary" />
                                                Rincian Outlet yang Diajukan ({{ $group['total_items'] }} Outlet)
                                            </span>
                                            <span class="text-xs text-base-content/50">• Salesman: <b class="text-base-content/80">{{ $group['salesman_code'] }} - {{ $group['salesman_name'] }}</b></span>
                                        </div>
                                        @if($filterStatus === 'PENDING')
                                            <div class="flex items-center gap-2">
                                                @if(count($groupSelected) > 0)
                                                    <span class="badge badge-sm badge-primary font-bold text-xs">{{ count($groupSelected) }} dicentang</span>
                                                    @if(auth()->check() && auth()->user()->hasRole(['admin', 'user', 'spm']))
                                                        <button class="btn btn-xs btn-success text-white gap-1 shadow-sm hover:scale-105 transition-transform"
                                                                wire:click="approveGroup({{ json_encode($groupSelected) }})"
                                                                wire:confirm="Setujui {{ count($groupSelected) }} outlet yang dicentang untuk salesman {{ $group['salesman_code'] }} - {{ $group['salesman_name'] }}?">
                                                            <x-heroicon-s-check class="w-3.5 h-3.5" />
                                                            <span>Setujui ({{ count($groupSelected) }})</span>
                                                        </button>
                                                        <button class="btn btn-xs btn-error btn-outline gap-1 hover:text-white"
                                                                wire:click="confirmRejectGroup({{ json_encode($groupSelected) }}, '{{ addslashes($group['salesman_name']) }}')"
                                                                title="Tolak outlet yang dicentang">
                                                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                                                            <span>Tolak ({{ count($groupSelected) }})</span>
                                                        </button>
                                                    @endif
                                                    <button type="button" 
                                                            wire:click="toggleSelectGroup({{ json_encode($group['approval_ids']) }})"
                                                            class="btn btn-xs btn-ghost text-base-content/60 font-medium">
                                                        Batal Pilih
                                                    </button>
                                                @else
                                                    <div class="flex items-center gap-1.5 text-xs text-base-content/60 bg-base-100 px-2.5 py-1 rounded-lg border border-base-300 shadow-2xs">
                                                        <x-heroicon-o-cursor-arrow-rays class="w-4 h-4 text-warning shrink-0" />
                                                        <span>Centang kotak di baris outlet bawah untuk menyetujui</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Sub-table Content --}}
                                    <div class="overflow-x-auto">
                                        <table class="table table-xs table-zebra w-full whitespace-nowrap">
                                            <thead class="text-xs uppercase tracking-wider bg-base-200/60 text-base-content/70 border-b border-base-300">
                                                <tr>
                                                    @if($filterStatus === 'PENDING')
                                                        <th class="w-10 text-center">
                                                            <input type="checkbox" 
                                                                   class="checkbox checkbox-xs rounded" 
                                                                   title="Pilih / Batal semua di grup ini"
                                                                   wire:click="toggleSelectGroup({{ json_encode($group['approval_ids']) }})"
                                                                   {{ count($groupSelected) === count($group['approval_ids']) && count($group['approval_ids']) > 0 ? 'checked' : '' }} />
                                                        </th>
                                                    @endif
                                                    <th class="w-56 font-semibold">Toko / Outlet</th>
                                                    <th class="w-32 font-semibold">Tipe Perubahan</th>
                                                    <th class="w-56 font-semibold">Rincian Jadwal</th>
                                                    <th class="w-auto min-w-48 font-semibold">Alasan Pengajuan</th>
                                                    <th class="w-36 font-semibold">Diajukan Oleh</th>
                                                    @if($filterStatus !== 'PENDING')
                                                        <th class="w-36 font-semibold">Diperiksa Oleh</th>
                                                        @if($filterStatus === 'REJECTED')
                                                            <th class="w-auto min-w-44 font-semibold">Alasan Penolakan</th>
                                                        @endif
                                                    @endif
                                                    <th class="w-24 text-center font-semibold bg-base-200/60 sticky right-0">Aksi Outlet</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-xs">
                                                @foreach($group['items'] as $item)
                                                    <tr class="hover:bg-base-200/70 transition-colors">
                                                        @if($filterStatus === 'PENDING')
                                                            <td class="text-center relative">
                                                                @if($item['has_prc_issue'])
                                                                    <div class="tooltip tooltip-right" data-tip="PRC Bermasalah (Kosong / Duplikat)">
                                                                        <input type="checkbox" class="checkbox checkbox-xs rounded checkbox-error opacity-50 cursor-not-allowed" disabled />
                                                                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5 text-warning absolute -top-0.5 -right-0.5 bg-base-100 rounded-full" />
                                                                    </div>
                                                                @else
                                                                    <input type="checkbox" class="checkbox checkbox-xs rounded" wire:model.live="selected" value="{{ $item['approval_id'] }}" />
                                                                @endif
                                                            </td>
                                                        @endif

                                                        {{-- Toko / Outlet --}}
                                                        <td class="whitespace-normal break-words max-w-xs">
                                                            @if(count($item['all_tokos']) > 1)
                                                                <div class="font-bold text-xs text-base-content flex items-center gap-1">
                                                                    <span>{{ count($item['all_tokos']) }} Toko:</span>
                                                                    <button wire:click="loadDrawerDetail({{ $item['approval_id'] }})" class="btn btn-ghost btn-xs text-primary px-1.5 h-auto py-0.5 font-medium">Lihat Semua</button>
                                                                </div>
                                                                <div class="text-xs text-base-content/80 mt-1 space-y-0.5">
                                                                    @foreach(array_slice($item['all_tokos'], 0, 2) as $tk)
                                                                        <div>• {{ $tk['name'] }} <span class="font-mono text-xs text-primary/80 font-medium">({{ $tk['code'] }})</span></div>
                                                                    @endforeach
                                                                    @if(count($item['all_tokos']) > 2)
                                                                        <div class="text-xs text-base-content/50 italic">+ {{ count($item['all_tokos']) - 2 }} toko lainnya...</div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="font-bold text-xs text-base-content">{{ $item['customer_name'] }}</div>
                                                                <div class="font-mono text-xs text-primary/80 font-medium mt-0.5">{{ $item['customer_code'] }}</div>
                                                            @endif
                                                        </td>

                                                        {{-- Tipe Perubahan --}}
                                                        <td>
                                                            @php
                                                                $actionBadgeClass = match($item['action_type']) {
                                                                    'DELETE_MASSAL' => 'badge-error font-bold text-white',
                                                                    'DELETE_INDIVIDU' => 'badge-error badge-outline',
                                                                    'TAMBAH_JADWAL' => 'badge-success text-white',
                                                                    'EDIT_INDIVIDU' => 'badge-info badge-outline',
                                                                    default => 'badge-ghost border-base-300'
                                                                };
                                                            @endphp
                                                            <span class="badge badge-xs font-semibold {{ $actionBadgeClass }} uppercase tracking-wider py-1.5">
                                                                {{ str_replace('_', ' ', $item['action_type']) }}
                                                            </span>
                                                            @if(in_array($item['action_type'], ['DELETE_INDIVIDU', 'DELETE_MASSAL']))
                                                                <span class="badge badge-xs font-semibold {{ ($item['delete_type'] ?? 'SOFT') === 'HARD' ? 'badge-error text-white' : 'badge-warning' }} uppercase tracking-wider py-1.5 ml-0.5">
                                                                    {{ $item['delete_type'] ?? 'SOFT' }}
                                                                </span>
                                                            @endif
                                                        </td>

                                                        {{-- Rincian Jadwal Kontekstual --}}
                                                        <td>
                                                            @if(in_array($item['action_type'], ['DELETE_INDIVIDU', 'DELETE_MASSAL']))
                                                                {{-- Aksi DELETE: Tampilkan jadwal lama yang akan dihapus dengan coret --}}
                                                                <div class="flex flex-col gap-0.5">
                                                                    <div class="flex items-center gap-1.5">
                                                                        <span class="badge badge-xs badge-error text-white font-bold">Dihapus</span>
                                                                        <span class="text-xs text-error font-semibold italic">(Dikosongkan)</span>
                                                                    </div>
                                                                    <div class="text-xs text-base-content/70 flex flex-wrap items-center gap-x-1.5 mt-0.5">
                                                                        <span class="text-xs text-base-content/50 uppercase font-semibold">Jadwal Lama:</span>
                                                                        <span class="line-through decoration-error text-error/90 font-medium">{{ $item['hari'] }}</span>
                                                                        <span class="text-base-content/30">•</span>
                                                                        <span class="line-through decoration-error text-error/90">{{ $item['minggu'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @elseif($item['action_type'] === 'TAMBAH_JADWAL')
                                                                {{-- Aksi TAMBAH: Tampilkan jadwal baru yang diajukan --}}
                                                                <div class="flex flex-col gap-0.5">
                                                                    <div class="flex items-center gap-1">
                                                                        <span class="badge badge-xs badge-success text-white font-bold">Jadwal Baru</span>
                                                                    </div>
                                                                    <div class="flex flex-wrap items-center gap-x-1.5 text-xs mt-0.5">
                                                                        <span class="text-xs text-base-content/50 uppercase font-semibold">Hari:</span>
                                                                        <span class="font-bold text-success">{{ $item['hari'] }}</span>
                                                                        <span class="text-base-content/30">•</span>
                                                                        <span class="text-xs text-base-content/50 uppercase font-semibold">Minggu:</span>
                                                                        <span class="font-semibold text-base-content/80">{{ $item['minggu'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @elseif($item['action_type'] === 'EDIT_INDIVIDU')
                                                                {{-- Aksi EDIT: Tampilkan perbandingan Jadwal Lama -> Usulan Baru --}}
                                                                <div class="flex flex-col gap-1">
                                                                    <div class="flex items-center gap-1.5">
                                                                        <span class="badge badge-xs badge-info text-white font-bold">Revisi Jadwal</span>
                                                                    </div>
                                                                    @if($item['old_hari'] !== '-' || $item['old_minggu'] !== '-')
                                                                        <div class="flex flex-wrap items-center gap-x-1.5 text-xs text-base-content/60">
                                                                            <span class="text-xs text-base-content/40 uppercase font-semibold">Lama:</span>
                                                                            <span class="line-through decoration-base-content/40">{{ $item['old_hari'] }}</span>
                                                                            <span class="text-base-content/30">•</span>
                                                                            <span class="line-through decoration-base-content/40">{{ $item['old_minggu'] }}</span>
                                                                        </div>
                                                                    @endif
                                                                    <div class="flex flex-wrap items-center gap-x-1.5 text-xs">
                                                                        <span class="text-xs text-primary uppercase font-bold">➔ Baru:</span>
                                                                        <span class="font-bold text-primary">{{ $item['hari'] }}</span>
                                                                        <span class="text-base-content/30">•</span>
                                                                        <span class="font-semibold text-base-content/90">{{ $item['minggu'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @elseif(in_array($item['action_type'], ['TUKAR_HARI', 'TUKAR_MINGGU', 'TUKAR_SALESMAN', 'TUKAR_JADWAL']))
                                                                {{-- Aksi TUKAR: Tampilkan jalur pertukaran --}}
                                                                <div class="flex flex-col gap-0.5">
                                                                    <div class="flex items-center gap-1">
                                                                        <span class="badge badge-xs badge-secondary text-white font-bold">Pertukaran</span>
                                                                    </div>
                                                                    <div class="text-xs font-semibold text-secondary">
                                                                        {{ $item['hari'] }}
                                                                    </div>
                                                                    @if($item['minggu'] !== '-')
                                                                        <div class="text-xs text-base-content/70">
                                                                            {{ $item['minggu'] }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                {{-- Fallback --}}
                                                                <div class="flex flex-col gap-0.5">
                                                                    <div class="flex items-center gap-1 text-xs">
                                                                        <span class="text-xs text-base-content/50 uppercase font-semibold">Hari:</span>
                                                                        <span class="font-semibold text-base-content">{{ $item['hari'] }}</span>
                                                                    </div>
                                                                    <div class="flex items-center gap-1 text-xs">
                                                                        <span class="text-xs text-base-content/50 uppercase font-semibold">Minggu:</span>
                                                                        <span class="text-xs text-base-content/70">{{ $item['minggu'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </td>

                                                        {{-- Alasan Pengajuan (Clean Reason) --}}
                                                        <td class="whitespace-normal break-words max-w-xs text-base-content/85">
                                                            <div class="font-medium text-xs text-base-content leading-snug" title="{{ $item['reason'] }}">
                                                                {{ $item['clean_reason'] }}
                                                            </div>
                                                            @if($item['action_type'] === 'TAMBAH_JADWAL' || in_array($item['action_type'], ['TUKAR_SALESMAN', 'TUKAR_MINGGU', 'TUKAR_HARI', 'EDIT_INDIVIDU', 'DELETE_INDIVIDU', 'DELETE_MASSAL']))
                                                                <button wire:click="loadDrawerDetail({{ $item['approval_id'] }})" 
                                                                        wire:loading.class="opacity-50 loading" 
                                                                        wire:target="loadDrawerDetail({{ $item['approval_id'] }})" 
                                                                        class="btn btn-xs btn-outline btn-primary mt-1 font-medium">
                                                                    Detail Payload
                                                                </button>
                                                            @endif
                                                        </td>

                                                        {{-- Diajukan Oleh & Waktu --}}
                                                        <td class="text-xs text-base-content/70 whitespace-nowrap">
                                                            <div class="font-medium text-base-content">{{ $item['maker_name'] }}</div>
                                                            <div class="text-xs text-base-content/50 flex items-center gap-1 mt-0.5">
                                                                <span>{{ \Carbon\Carbon::parse($item['created_at'])->format('d M Y H:i') }}</span>
                                                                @if($filterStatus === 'PENDING' && !empty($item['aging']))
                                                                    <span class="{{ $item['aging']['text_class'] }}">({{ $item['aging']['label'] }})</span>
                                                                @endif
                                                            </div>
                                                        </td>

                                                        {{-- Jika bukan PENDING --}}
                                                        @if($filterStatus !== 'PENDING')
                                                            <td class="text-xs text-base-content/70">
                                                                <div class="font-medium text-base-content">{{ $item['checker_name'] }}</div>
                                                                <div class="text-xs text-base-content/50 mt-0.5">{{ \Carbon\Carbon::parse($item['updated_at'])->format('d M Y H:i') }}</div>
                                                            </td>
                                                            @if($filterStatus === 'REJECTED')
                                                                <td class="whitespace-normal break-words text-error font-medium text-xs">{{ $item['reject_reason'] }}</td>
                                                            @endif
                                                        @endif

                                                        {{-- Aksi Per Outlet --}}
                                                        <td class="text-center bg-base-200/50 border-l border-base-300 sticky right-0 whitespace-nowrap">
                                                            @if($filterStatus === 'PENDING')
                                                                @if(auth()->check() && auth()->user()->hasRole(['admin', 'user', 'spm']))
                                                                    <div class="flex items-center justify-center gap-1">
                                                                        @if($item['has_prc_issue'])
                                                                            <button class="btn btn-xs btn-square btn-warning text-warning-content shadow-xs" 
                                                                                    title="Lengkapi Kode PRC Toko & Setujui"
                                                                                    wire:click="approve({{ $item['approval_id'] }})">
                                                                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                                                            </button>
                                                                        @else
                                                                            <button class="btn btn-xs btn-square btn-ghost text-success hover:bg-success/20" 
                                                                                    title="Setujui Outlet Ini"
                                                                                    wire:click="approve({{ $item['approval_id'] }})" 
                                                                                    wire:confirm="Apakah Anda yakin ingin menyetujui pengajuan untuk toko {{ $item['customer_name'] }}?">
                                                                                <x-heroicon-s-check class="w-4 h-4" />
                                                                            </button>
                                                                        @endif
                                                                        <button class="btn btn-xs btn-square btn-ghost text-error hover:bg-error/20" 
                                                                                title="Tolak Outlet Ini"
                                                                                wire:click="confirmReject({{ $item['approval_id'] }})">
                                                                            <x-heroicon-s-x-mark class="w-4 h-4" />
                                                                        </button>
                                                                    </div>
                                                                @else
                                                                    <span class="badge badge-xs badge-warning">Menunggu</span>
                                                                @endif
                                                            @else
                                                                <span class="badge badge-xs {{ $filterStatus === 'APPROVED' ? 'badge-success text-white' : 'badge-error text-white' }}">
                                                                    {{ $filterStatus === 'APPROVED' ? 'Disetujui' : 'Ditolak' }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center text-base-content/40">
                                    <x-heroicon-o-inbox class="w-12 h-12 mb-3" />
                                    <p class="font-medium">Tidak ada data pengajuan {{ strtolower($filterStatus) }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Card (Pagination) --}}
        @if($approvals->hasPages())
            <div class="p-3 border-t border-base-300 bg-base-200 shrink-0">
                {{ $approvals->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    {{-- Modal Penolakan --}}
    <div class="modal modal-bottom sm:modal-middle !items-start z-[10000]" :class="showRejectModal ? 'modal-open' : ''">
        <div class="modal-box relative mt-6 sm:mt-16 mb-auto">
            <button @click="showRejectModal = false" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <h3 class="text-lg font-bold text-error flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                Alasan Penolakan
            </h3>
            <p class="py-4 text-sm text-base-content/80">Silakan masukkan alasan mengapa pengajuan ini ditolak. Alasan ini akan diteruskan ke pembuat pengajuan.</p>
            
            <textarea wire:model="rejectReason" class="textarea textarea-bordered w-full h-32 focus:border-error focus:ring-1 focus:ring-error" placeholder="Contoh: Jadwal tidak sesuai dengan rute..."></textarea>
            @error('rejectReason') <span class="text-error text-sm mt-1 block">{{ $message }}</span> @enderror

            <div class="modal-action">
                <button class="btn btn-ghost" @click="showRejectModal = false">Batal</button>
                <button class="btn btn-error text-white" wire:click="executeReject" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="executeReject">Tolak Pengajuan</span>
                    <span wire:loading wire:target="executeReject" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop" @click="showRejectModal = false"></div>
    </div>

    {{-- PRC Input Modal --}}
    <div class="modal modal-bottom sm:modal-middle z-[10000]" :class="showPrcModal ? 'modal-open' : ''">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4 text-warning flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                Customer Kode PRC Bermasalah (Kosong / Duplikat)
            </h3>
            
            <p class="text-sm text-base-content/80 mb-4">
                Terdapat <span class="font-bold text-error">{{ count($missingPrcTokos) }}</span> toko yang belum memiliki Customer Kode PRC <b>atau kodenya sudah terpakai oleh toko lain</b> di distributor ini. Sesuai aturan, Anda wajib mengisi kode yang unik sebelum pengajuan dapat disetujui untuk diinjeksi ke Master Pareto.
            </p>

            <div class="space-y-3 max-h-[50vh] overflow-y-auto p-1 mb-4">
                @foreach($missingPrcTokos as $index => $toko)
                    <div class="bg-base-200/50 p-3 rounded-lg border border-base-300">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div>
                                <div class="text-xs text-base-content/60">Toko:</div>
                                <div class="font-bold text-sm leading-tight">{{ $toko['name'] }}</div>
                                <div class="font-mono text-xs text-primary mt-0.5">{{ $toko['code'] }}</div>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="text" wire:model="missingPrcTokos.{{ $index }}.eska" class="input input-sm input-bordered w-full font-mono @error('missingPrcTokos.'.$index.'.eska') input-error @enderror" placeholder="Input PRC (eska)..." />
                            @error('missingPrcTokos.'.$index.'.eska') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="modal-action flex justify-between items-center">
                @if(auth()->check() && auth()->user()->hasRole(['admin', 'user', 'spm']))
                <button wire:click="confirmReject('{{ $prcApprovalId }}')" class="btn btn-error btn-sm btn-outline">Tolak Pengajuan</button>
                @endif
                <div class="flex gap-2">
                    <button wire:click="closePrcModal" class="btn btn-ghost btn-sm">Batal</button>
                    <button wire:click="submitPrcAndApprove" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="submitPrcAndApprove">
                        <span wire:loading.remove wire:target="submitPrcAndApprove">Simpan & Setujui</span>
                        <span wire:loading wire:target="submitPrcAndApprove" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-backdrop bg-neutral/40" wire:click="closePrcModal"></div>
    </div>

    {{-- Side Drawer (Alpine JS) --}}
    <div class="fixed inset-0 z-[11000] flex justify-end" x-show="showDrawer" style="display: none;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-neutral/40 transition-opacity" 
             x-show="showDrawer" 
             x-transition:enter="ease-in-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in-out duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             @click="showDrawer = false"></div>
             
        {{-- Panel --}}
        <div class="relative w-full max-w-md bg-base-100 shadow-2xl h-full flex flex-col transform transition-transform"
             x-show="showDrawer" 
             x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" 
             x-transition:enter-start="translate-x-full" 
             x-transition:enter-end="translate-x-0" 
             x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" 
             x-transition:leave-start="translate-x-0" 
             x-transition:leave-end="translate-x-full">
            
            <div class="p-4 border-b border-base-300 flex items-center justify-between bg-base-200/50">
                <h3 class="font-bold text-lg">Rincian Pengajuan</h3>
                <button @click="showDrawer = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4">
                <template x-if="drawerData">
                    <div class="space-y-4 text-sm">
                        {{-- Generic rendering of payload based on keys --}}
                        <div class="bg-base-200/30 rounded-lg p-3 border border-base-300 space-y-2">
                            <template x-if="drawerData.salesman_code">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Salesman</span>
                                    <span class="font-mono" x-text="drawerData.salesman_code"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.hari">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Hari</span>
                                    <span class="font-mono uppercase" x-text="(drawerData.hari || []).join(', ')"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.minggu">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Minggu</span>
                                    <span class="font-mono uppercase" x-text="(drawerData.minggu || []).join(', ')"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.salesman_asal">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Salesman Asal</span>
                                    <span class="font-mono" x-text="drawerData.salesman_asal"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.salesman_tujuan">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Salesman Tujuan</span>
                                    <span class="font-mono" x-text="drawerData.salesman_tujuan"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.minggu_asal">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Minggu Asal</span>
                                    <span class="font-mono uppercase" x-text="drawerData.minggu_asal"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.minggu_tujuan">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Minggu Tujuan</span>
                                    <span class="font-mono uppercase" x-text="drawerData.minggu_tujuan"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.hari_asal">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Hari Asal</span>
                                    <span class="font-mono uppercase" x-text="drawerData.hari_asal"></span>
                                </div>
                            </template>
                            <template x-if="drawerData.hari_tujuan">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span class="text-base-content/60 font-semibold">Hari Tujuan</span>
                                    <span class="font-mono uppercase" x-text="drawerData.hari_tujuan"></span>
                                </div>
                            </template>
                        </div>
                        
                        <template x-if="drawerData.tokos && drawerData.tokos.length > 0">
                            <div>
                                <h4 class="font-semibold text-base-content/80 mb-2">Daftar Toko (<span x-text="drawerData.tokos.length"></span>)</h4>
                                <ul class="space-y-2">
                                    <template x-for="toko in drawerData.tokos">
                                        <li class="bg-base-100 p-3 rounded border border-base-300 flex flex-col gap-1">
                                            <div class="flex justify-between items-start">
                                                <span class="font-bold text-sm" x-text="toko.name || toko.code || 'Nama Toko Tidak Diketahui'"></span>
                                                <span class="font-mono text-xs text-primary bg-primary/10 px-1.5 py-0.5 rounded" x-text="toko.code"></span>
                                            </div>
                                            <template x-if="toko.salesman_code && !(drawerData.action_type === 'DELETE_MASSAL' && drawerData.salesman_code)">
                                                <div class="mt-1 text-xs text-base-content/70 flex flex-col gap-1 border-t border-base-200 pt-2 mt-2">
                                                    <div><span class="font-semibold text-base-content/80">Salesman:</span> <span class="font-mono" x-text="toko.salesman_code"></span></div>
                                                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                                                        <div>
                                                            <span class="font-semibold text-base-content/80">Jadwal Hari:</span> 
                                                            <span x-text="['h1','h2','h3','h4','h5','h6','h7'].filter(h => toko[h] === 'Y').map(h => h.toUpperCase()).join(', ') || '-'"></span>
                                                        </div>
                                                        <div>
                                                            <span class="font-semibold text-base-content/80">Jadwal Minggu:</span> 
                                                            <span x-text="['w1','w2','w3','w4'].filter(w => toko[w] === 'Y').map(w => w.toUpperCase()).join(', ') || '-'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        {{-- Fallback UI for hard-deleted records where 'tokos' cannot be fetched from DB anymore, relying purely on the snapshot payload --}}
                        <template x-if="(!drawerData.tokos || drawerData.tokos.length === 0) && drawerData.records && drawerData.records.length > 0">
                            <div>
                                <h4 class="font-semibold text-base-content/80 mb-2">Daftar Jadwal Terdampak (<span x-text="drawerData.records.length"></span>)</h4>
                                <ul class="space-y-2">
                                    <template x-for="record in drawerData.records">
                                        <li class="bg-base-100 p-3 rounded border border-base-300 flex flex-col gap-1">
                                            <div class="flex justify-between items-start">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-sm" x-text="record.customer_name || 'Nama Toko Tidak Diketahui'"></span>
                                                    <span class="text-xs text-base-content/60">ID Record: <span class="font-mono text-primary font-medium" x-text="record.id"></span></span>
                                                </div>
                                                <div class="flex flex-col items-end gap-1">
                                                    <template x-if="record.customer_code">
                                                        <span class="font-mono text-xs text-primary bg-primary/10 px-1.5 py-0.5 rounded" x-text="record.customer_code"></span>
                                                    </template>
                                                    <span class="font-mono text-xs bg-base-200 px-1.5 py-0.5 rounded" x-text="record.bulan"></span>
                                                </div>
                                            </div>
                                            <template x-if="!(drawerData.action_type === 'DELETE_MASSAL' && drawerData.salesman_code)">
                                                <div class="mt-1 text-xs text-base-content/70 flex flex-col gap-1 border-t border-base-200 pt-2 mt-2">
                                                    <div><span class="font-semibold text-base-content/80">Salesman:</span> <span class="font-mono" x-text="record.salesman_code"></span></div>
                                                    <div><span class="font-semibold text-base-content/80">Distributor:</span> <span class="font-mono" x-text="record.distributor_code"></span></div>
                                                </div>
                                            </template>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <template x-if="drawerData.ids && drawerData.ids.length > 0">
                            <div class="mb-4">
                                <h4 class="font-semibold text-base-content/80 mb-2">Peringatan Penghapusan</h4>
                                <div class="bg-error/10 text-error p-3 rounded border border-error/20 flex items-center gap-3">
                                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 flex-shrink-0" />
                                    <div class="text-sm">
                                        <span class="font-bold block">Penghapusan Massal (<span x-text="drawerData.ids.length"></span> Jadwal)</span>
                                        <span>Seluruh jadwal harian dan mingguan untuk toko-toko di atas akan dikosongkan secara permanen.</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="drawerData.action_type === 'DELETE_INDIVIDU'">
                            <div class="mb-4">
                                <h4 class="font-semibold text-base-content/80 mb-2">Peringatan Penghapusan</h4>
                                <div class="bg-error/10 text-error p-3 rounded border border-error/20 flex items-center gap-3">
                                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 flex-shrink-0" />
                                    <div class="text-sm">
                                        <span class="font-bold block">Penghapusan Jadwal</span>
                                        <span>Seluruh jadwal harian dan mingguan untuk toko di atas akan dikosongkan secara permanen.</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="drawerData.update && !drawerData.action_type.includes('DELETE')">
                            <div class="mt-4">
                                <h4 class="font-semibold text-base-content/80 mb-2">Detail Modifikasi Jadwal</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Hari -->
                                    <div class="bg-base-100 p-3 rounded border border-base-300">
                                        <h5 class="font-semibold text-xs text-base-content/60 mb-2">Hari Kunjungan</h5>
                                        <div class="grid grid-cols-7 gap-1">
                                            <template x-for="h in ['h1','h2','h3','h4','h5','h6','h7']">
                                                <div class="text-center p-1 rounded text-xs font-mono"
                                                     :class="drawerData.update[h] === 'Y' ? 'bg-success/20 text-success-content font-bold' : 'bg-base-200 text-base-content/40'">
                                                    <span x-text="h.toUpperCase()"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Minggu -->
                                    <div class="bg-base-100 p-3 rounded border border-base-300">
                                        <h5 class="font-semibold text-xs text-base-content/60 mb-2">Minggu Kunjungan</h5>
                                        <div class="grid grid-cols-4 gap-1">
                                            <template x-for="w in ['w1','w2','w3','w4']">
                                                <div class="text-center p-1 rounded text-xs font-mono"
                                                     :class="drawerData.update[w] === 'Y' ? 'bg-success/20 text-success-content font-bold' : 'bg-base-200 text-base-content/40'">
                                                    <span x-text="w.toUpperCase()"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <template x-if="drawerData.update.reason">
                                    <div class="mt-3 bg-base-100 p-3 rounded border border-base-300">
                                        <h5 class="font-semibold text-xs text-base-content/60 mb-1">Alasan Detail</h5>
                                        <p class="text-sm text-base-content/80 italic" x-text="drawerData.update.reason"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        
                    </div>
                </template>
            </div>
            <div class="p-4 border-t border-base-300 bg-base-200/50 flex justify-end">
                <button @click="showDrawer = false" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast toast-top toast-right z-[9999] mt-16" style="position: fixed;">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-full"
                 class="alert shadow-lg flex items-center justify-between min-w-[300px]"
                 :class="{
                     'alert-success bg-success text-success-content': toast.type === 'success',
                     'alert-error bg-error text-error-content': toast.type === 'error',
                     'alert-warning bg-warning text-warning-content': toast.type === 'warning',
                     'alert-info bg-info text-info-content': toast.type === 'info'
                 }">
                <div class="flex items-center gap-3">
                    <template x-if="toast.type === 'success'">
                        <x-heroicon-o-check-circle class="w-6 h-6 shrink-0" />
                    </template>
                    <template x-if="toast.type === 'error'">
                        <x-heroicon-o-x-circle class="w-6 h-6 shrink-0" />
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0" />
                    </template>
                    <template x-if="toast.type === 'info'">
                        <x-heroicon-o-information-circle class="w-6 h-6 shrink-0" />
                    </template>
                    <span x-text="toast.message" class="text-sm font-medium"></span>
                </div>
                <button @click="removeToast(toast.id)" class="btn btn-ghost btn-xs btn-square">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
        </template>
    </div>
</div>
