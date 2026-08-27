<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Master Customer JKS (Team Elite)</x-slot>

    @php
        $getSortIcon = function($column) use ($sortColumn, $sortDirection) {
            if ($sortColumn !== $column) return 'chevron-up-down';
            return $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down';
        };
        $getSortClass = function($column) use ($sortColumn) {
            return $sortColumn === $column ? 'w-4 h-4 text-primary' : 'w-4 h-4 text-base-content/30';
        };
    @endphp

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.master-customer') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.master-customer.monitoring-customer-pareto') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Monitoring Customer Pareto</a>
        </div>
    </div>

    <!-- Notifikasi -->
    @if (session()->has('message'))
        <x-ui.notif type="success" dismissible class="shrink-0 mb-0">
            {{ session('message') }}
        </x-ui.notif>
    @endif
    @if (session()->has('error'))
        <x-ui.notif type="error" dismissible class="shrink-0 mb-0">
            {{ session('error') }}
        </x-ui.notif>
    @endif

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 md:gap-4 lg:gap-6 shrink-0">
        <!-- Total Customer -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Customer</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-users class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($kpi->total_customer ?? 0, 0, ',', '.') }}</div>
        </div>
        
        <!-- Total Pareto -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-secondary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Pareto</h3>
                <div class="w-8 h-8 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-secondary">{{ number_format($kpi->total_pareto ?? 0, 0, ',', '.') }}</div>
        </div>

        <!-- Total RWO -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total RWO</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">{{ number_format($kpi->total_rwo ?? 0, 0, ',', '.') }}</div>
        </div>

        <!-- Total PNR -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total PNR</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">{{ number_format($kpi->total_pnr ?? 0, 0, ',', '.') }}</div>
        </div>

        <!-- Total NGVO -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total NGVO</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ number_format($kpi->total_ngvo ?? 0, 0, ',', '.') }}</div>
        </div>

        <!-- Total GRO -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-info/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total GRO</h3>
                <div class="w-8 h-8 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-info">{{ number_format($kpi->total_gro ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Master Customer</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Daftar customer yang tersedia</p>
            </div>
            
            {{-- Menggunakan flex-wrap agar barisan aksi jatuh secara responsif --}}
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative w-full sm:w-64">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/50" />
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode/Nama/Alamat/Pilar..." 
                           class="input input-sm input-bordered w-full pl-9 focus:input-primary">
                </div>
                
                {{-- Filter --}}
                <x-ui.action-button type="filter" wire:click="openFilterModal" class="relative shrink-0">
                    @if($filterRegion || $filterArea || $filterSupervisor || $filterDistributor || $filterPareto || $filterPilar)
                        <div class="badge badge-primary badge-xs absolute -top-1 -right-1"></div>
                    @endif
                </x-ui.action-button>

                {{-- Aksi --}}
                <div class="flex items-center justify-between sm:justify-end gap-1 md:gap-2 shrink-0">
                    <div class="w-[1px] h-6 bg-base-300 hidden sm:block mx-1"></div>

                    @canImport('call-plan.jks-team-elite.master-customer')
                    <x-ui.action-button type="import" wire:click="openImportModal" />
                    @endcanImport
                    
                    @canAdd('call-plan.jks-team-elite.master-customer')
                    <x-ui.action-button type="add" wire:click="openCreateModal" />
                    @endcanAdd

                    <div class="w-[1px] h-6 bg-base-300 hidden sm:block mx-1"></div>

                    @canExport('call-plan.jks-team-elite.master-customer')
                    <x-ui.action-button type="export" wire:click="openExportModal" wire:loading.attr="disabled" wire:target="openExportModal">
                        <span wire:loading wire:target="openExportModal" class="ml-1">
                            <span class="loading loading-spinner loading-xs"></span>
                        </span>
                    </x-ui.action-button>
                    @endcanExport

                    @canEdit('call-plan.jks-team-elite.master-customer')
                    <button type="button" wire:click="openSyncModal" class="btn btn-sm btn-outline btn-info gap-2 hidden sm:inline-flex rounded-lg border-info/30 hover:border-info">
                        <x-heroicon-s-map class="w-4 h-4" />
                        Sync Wilayah
                    </button>
                    @endcanEdit
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th wire:click="sortBy('md.region_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Region</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('md.region_name')" class="{{ $getSortClass('md.region_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('md.area_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Area</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('md.area_name')" class="{{ $getSortClass('md.area_name') }}" />
                            </div>
                        </th>

                        <th wire:click="sortBy('f.SLSNAME')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Supervisor</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('f.SLSNAME')" class="{{ $getSortClass('f.SLSNAME') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('md.distributor_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Distributor</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('md.distributor_name')" class="{{ $getSortClass('md.distributor_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_code_prc')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Customer Code</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.customer_code_prc')" class="{{ $getSortClass('l.customer_code_prc') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.uniq_kd')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Uniq Kd</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.uniq_kd')" class="{{ $getSortClass('l.uniq_kd') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Customer Name</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.customer_name')" class="{{ $getSortClass('l.customer_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_address')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Address</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.customer_address')" class="{{ $getSortClass('l.customer_address') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.kabupaten')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Kabupaten</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.kabupaten')" class="{{ $getSortClass('l.kabupaten') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.kecamatan')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Kecamatan</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.kecamatan')" class="{{ $getSortClass('l.kecamatan') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.desa')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Desa</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.desa')" class="{{ $getSortClass('l.desa') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.channel_outlet')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Channel</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.channel_outlet')" class="{{ $getSortClass('l.channel_outlet') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.classification_outlet')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Classification</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.classification_outlet')" class="{{ $getSortClass('l.classification_outlet') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.segment_outlet')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Segment</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.segment_outlet')" class="{{ $getSortClass('l.segment_outlet') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.pilar')" class="cursor-pointer hover:bg-base-200 text-center select-none transition-colors">
                            <div class="flex items-center justify-center gap-2">
                                <span>Pilar</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.pilar')" class="{{ $getSortClass('l.pilar') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.target')" class="cursor-pointer hover:bg-base-200 text-right select-none transition-colors">
                            <div class="flex items-center justify-end gap-2">
                                <span>Target</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.target')" class="{{ $getSortClass('l.target') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.keterangan')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Remarks SPM</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.keterangan')" class="{{ $getSortClass('l.keterangan') }}" />
                            </div>
                        </th>
                        
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $item)
                    <tr wire:key="cust-row-{{ $item->customer_code }}-{{ $item->distributor_code }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="text-xs text-base-content/70 max-w-[120px] truncate" title="{{ $item->region_name }}">{{ $item->region_name }}</td>
                        <td class="text-xs text-base-content/70 max-w-[120px] truncate" title="{{ $item->area_name }}">{{ $item->area_name }}</td>
                        <td class="text-xs text-base-content/70 max-w-[150px] truncate" title="{{ $item->supervisor_name }}">{{ $item->supervisor_name ?? '-' }}</td>
                        <td class="text-xs">
                            <div class="max-w-[150px] truncate text-base-content/80 font-medium" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</div>
                            <div class="text-[10px] text-base-content/50 font-mono mt-0.5">{{ $item->distributor_code }}</div>
                        </td>
                        <td class="max-w-[120px] truncate font-mono text-xs text-base-content/70" title="{{ $item->customer_code }}">{{ $item->customer_code }}</td>
                        <td class="font-mono text-xs max-w-[100px] truncate" title="{{ $item->uniq_kd }}">{{ $item->uniq_kd ?? '-' }}</td>
                        <td class="min-w-[200px] max-w-[250px] truncate font-bold text-base-content/90" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                        <td class="max-w-[200px] truncate text-xs text-base-content/60" title="{{ $item->customer_address }}">{{ $item->customer_address }}</td>
                        <td class="text-xs text-base-content/70 max-w-[120px] truncate" title="{{ $item->kabupaten }}">{{ $item->kabupaten }}</td>
                        <td class="text-xs text-base-content/70 max-w-[120px] truncate" title="{{ $item->kecamatan }}">{{ $item->kecamatan }}</td>
                        <td class="text-xs text-base-content/70 max-w-[120px] truncate" title="{{ $item->desa }}">{{ $item->desa }}</td>
                        <td class="text-xs text-base-content/70 whitespace-nowrap">{{ $item->channel_outlet ?? '-' }}</td>
                        <td class="text-xs text-base-content/70 whitespace-nowrap">{{ $item->classification_outlet ?? '-' }}</td>
                        <td class="text-xs text-base-content/70 whitespace-nowrap">{{ $item->segment_outlet ?? '-' }}</td>
                        <td class="text-center">
                            @php
                                $badgeColor = match($item->pilar) { 
                                    '1. RWO' => 'error', 
                                    '2. PNR' => 'warning', 
                                    '3. NGVO' => 'success', 
                                    '4. GRO' => 'info', 
                                    default => 'neutral' 
                                };
                            @endphp
                            <span class="badge badge-sm badge-outline badge-{{ $badgeColor }}">{{ $item->pilar ?? '-' }}</span>
                        </td>
                        <td class="text-right font-mono text-xs">Rp {{ number_format((float)($item->target ?? 0), 0, ',', '.') }}</td>
                        <td class="text-xs text-base-content/70 max-w-[150px] truncate" title="{{ $item->keterangan }}">{{ $item->keterangan ?? '-' }}</td>
                        <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" class="btn btn-sm btn-square btn-ghost text-info hover:bg-info/10" title="Detail" wire:click="openDetailModal('{{ $item->distributor_code }}', '{{ $item->uniq_kd }}')">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </button>
                                
                                @canEdit('call-plan.jks-team-elite.master-customer')
                                <x-ui.action-button type="edit" class="btn-square" title="Edit" wire:click="openEditModal('{{ $item->distributor_code }}', '{{ $item->uniq_kd }}')" />
                                @endcanEdit
                                
                                @canDelete('call-plan.jks-team-elite.master-customer')
                                <x-ui.action-button type="delete" class="btn-square" title="Hapus" wire:click="confirmDelete('{{ $item->distributor_code }}', '{{ $item->uniq_kd }}')" />
                                @endcanDelete
                            </div>
                        </th>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="text-center py-8 text-base-content/50">Tidak ada data ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($data->hasPages())
            {{-- Footer Card (Pagination) --}}
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL FILTER -->
    <x-ui.modal wire:key="modal-filter-key" id="modal-filter" title="Filter Data" icon="funnel" :open="$isFilterModalOpen" wire:close="closeFilterModal">
        <div class="space-y-4">
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Region</span></label>
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full" @if(count($regions) <= 1) disabled @endif>
                    @if(count($regions) > 1) <option value="">-- Semua Region --</option> @endif
                    @foreach($regions as $r) 
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Area</span></label>
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full" @if(!$filterRegion || count($areas) <= 1) disabled @endif>
                    @if(count($areas) > 1 || count($areas) == 0) <option value="">-- Semua Area --</option> @endif
                    @foreach($areas as $a) 
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Supervisor</span></label>
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered w-full" @if(!$filterArea || count($supervisors) <= 1) disabled @endif>
                    @if(count($supervisors) > 1 || count($supervisors) == 0) <option value="">-- Semua Supervisor --</option> @endif
                    @foreach($supervisors as $s) 
                        <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option> 
                    @endforeach
                </select>
            </div>

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Distributor</span></label>
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered w-full" @if(!$filterRegion || count($distributors) <= 1) disabled @endif>
                    @if(count($distributors) > 1 || count($distributors) == 0) <option value="">-- Semua Distributor --</option> @endif
                    @foreach($distributors as $d) 
                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option> 
                    @endforeach
                </select>
            </div>

        </div>
        <x-slot:footer>
            <x-ui.button variant="error" outline wire:click="resetFilter">Reset</x-ui.button>
            <x-ui.button variant="primary" wire:click="applyFilter">Terapkan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL TAMBAH CUSTOMER BARU -->
    <x-ui.modal wire:key="modal-create-key" id="modal-create" title="Tambah Customer Baru" icon="plus-circle" size="lg" :open="$isCreateModalOpen" wire:close="$set('isCreateModalOpen', false)">
        <style>
            #modal-create .form-control, #modal-edit .form-control {
                margin-bottom: 0.25rem !important;
            }
            #modal-create .label, #modal-edit .label {
                padding-top: 0.125rem !important;
                padding-bottom: 0.125rem !important;
            }
        </style>
        <form wire:submit.prevent="store">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                <div class="md:col-span-2 form-control relative mb-2" x-data="{ open: false, search: @entangle('searchDistributor'), selectedCode: @entangle('distributor_code') }">
                    <label class="label pb-1">
                        <span class="label-text text-xs font-medium text-base-content/85">Distributor *</span>
                    </label>
                    <div class="relative">
                        <!-- Input field acting as the search box and display of selected item -->
                        <input 
                            type="text" 
                            placeholder="Cari & Pilih Distributor..." 
                            class="input input-sm input-bordered w-full pr-10 focus:input-primary text-xs"
                            wire:model.live.debounce.300ms="searchDistributor"
                            @focus="open = true"
                            @click.away="open = false"
                        />
                        
                        <!-- Clear button if a distributor is selected -->
                        @if($distributor_code)
                            <button 
                                type="button"
                                class="absolute inset-y-0 right-7 flex items-center text-base-content/40 hover:text-error transition-colors"
                                @click="
                                    selectedCode = '';
                                    search = '';
                                "
                                wire:click="$set('distributor_code', ''); $set('searchDistributor', '')"
                            >
                                <x-heroicon-s-x-mark class="w-4 h-4" />
                            </button>
                        @endif

                        <!-- Toggle indicator arrow/icon -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-base-content/40">
                            <x-heroicon-s-chevron-down class="w-4 h-4" />
                        </div>

                        <!-- Floating dropdown list -->
                        <div 
                            x-show="open" 
                            x-transition
                            class="absolute z-[9999] w-full mt-1 bg-base-100 rounded-lg shadow-xl border border-base-200 max-h-60 overflow-y-auto overflow-x-hidden"
                            style="display: none;"
                        >
                            <ul class="p-1 menu menu-sm">
                                @if(count($createDistributors) === 0)
                                    <li class="disabled"><span class="text-xs opacity-50 py-2 px-3">Tidak ada distributor ditemukan</span></li>
                                @else
                                    @foreach($createDistributors as $d)
                                        <li class="mb-0.5 last:mb-0">
                                            <button 
                                                type="button" 
                                                class="w-full text-left flex items-center justify-between gap-2 py-2 px-3 hover:bg-base-200 rounded-md text-xs transition-colors"
                                                @click="
                                                    selectedCode = '{{ $d->distributor_code }}';
                                                    search = '{{ $d->distributor_name }}';
                                                    open = false;
                                                "
                                            >
                                                <span class="font-medium text-base-content truncate pr-1" title="{{ $d->distributor_name }}">{{ $d->distributor_name }}</span>
                                                <span class="font-mono text-[9px] bg-base-300 px-1.5 py-0.5 rounded text-base-content/60 flex-shrink-0">{{ $d->distributor_code }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    @error('distributor_code')
                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
                
                <x-input-text label="Customer Code PRC *" wire:model="customer_code_prc" placeholder="Contoh: CILMG00001" />
                <x-input-text label="Uniq Kd *" wire:model="uniq_kd" placeholder="Contoh: LMG-0001" />
                
                <div class="md:col-span-2">
                    <x-input-text label="Nama Toko *" wire:model="customer_name" />
                </div>
                <div class="md:col-span-2 form-control mb-4">
                    <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Alamat</span></label>
                    <textarea wire:model="customer_address" class="textarea textarea-bordered focus:textarea-primary w-full" rows="2"></textarea>
                </div>
                
                <x-input-text label="Kabupaten" wire:model="kabupaten" />
                <x-input-text label="Kecamatan" wire:model="kecamatan" />
                <x-input-text label="Desa" wire:model="desa" />
                <x-input-text label="Latitude" wire:model="latitude" />
                <x-input-text label="Longitude" wire:model="longitude" />
                <div class="form-control">
                    <label class="label pb-1">
                        <span class="label-text text-xs font-medium text-base-content/85">Pilar *</span>
                    </label>
                    <select wire:model="pilar" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                        <option value="">-- Pilih Pilar --</option>
                        <option value="1. RWO">1. RWO</option>
                        <option value="2. PNR">2. PNR</option>
                        <option value="3. NGVO">3. NGVO</option>
                        <option value="4. GRO">4. GRO</option>
                    </select>
                </div>
                <x-input-text label="Target *" wire:model="target" type="number" step="0.01" />
                <x-input-text label="Remarks SPM" wire:model="keterangan" />
                
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2 mt-2 pt-3 border-t border-base-200">
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Channel Outlet</span></label>
                        <select wire:model="channel_outlet" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                            <option value="">-- Pilih --</option>
                            <option value="GT">GT</option>
                            <option value="MT">MT</option>
                            <option value="LMT">LMT</option>
                            <option value="OTH">OTH</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Classification</span></label>
                        <select wire:model="classification_outlet" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                            <option value="">-- Pilih --</option>
                            <option value="PARETO">PARETO</option>
                            <option value="NON PARETO">NON PARETO</option>
                            <option value="DUMMY BRIEF">DUMMY BRIEF</option>
                            <option value="DUMMY EVALUASI">DUMMY EVALUASI</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Segment</span></label>
                        <select wire:model="segment_outlet" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                            <option value="">-- Pilih --</option>
                            <option value="STAR OUTLET">STAR OUTLET</option>
                            <option value="GROSIR">GROSIR</option>
                            <option value="SEMI-GROSIR">SEMI-GROSIR</option>
                            <option value="RETAIL">RETAIL</option>
                            <option value="PENGRAJIN">PENGRAJIN</option>
                            <option value="TRADER">TRADER</option>
                            <option value="SEASONAL/HAJATAN">SEASONAL/HAJATAN</option>
                        </select>
                    </div>
                </div>
                
                <div class="md:col-span-2 form-control mt-2 pt-3 border-t border-base-200">
                    <label class="label pb-2"><span class="label-text text-xs font-bold text-base-content/70 uppercase tracking-wider">Histori Pilar</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q1</span></label>
                            <select wire:model="pilar_q1" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q2</span></label>
                            <select wire:model="pilar_q2" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q3</span></label>
                            <select wire:model="pilar_q3" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q4</span></label>
                            <select wire:model="pilar_q4" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <x-ui.button type="button" variant="neutral" outline wire:click="$set('isCreateModalOpen', false)">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check-circle">Simpan Customer</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <!-- MODAL EDIT CUSTOMER -->
    <x-ui.modal wire:key="modal-edit-key" id="modal-edit" title="Edit Customer" icon="pencil-square" size="lg" :open="$isEditModalOpen" wire:close="$set('isEditModalOpen', false)">
        <form wire:submit.prevent="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                <div class="md:col-span-2 form-control relative mb-2">
                    <label class="label pb-1">
                        <span class="label-text text-xs font-medium text-base-content/85">Distributor (Terkunci)</span>
                    </label>
                    <select wire:model="distributor_code" class="select select-sm select-bordered w-full" disabled>
                        @foreach($createDistributors as $dist)
                            <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_name }} ({{ $dist->distributor_code }})</option>
                        @endforeach
                    </select>
                    @error('distributor_code') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </div>
                
                <x-input-text label="Customer Code PRC *" wire:model="customer_code_prc" placeholder="Contoh: CILMG00001" />
                <x-input-text label="Uniq Kd *" wire:model="uniq_kd" placeholder="Contoh: LMG-0001" />
                
                <div class="md:col-span-2">
                    <x-input-text label="Nama Toko *" wire:model="customer_name" />
                </div>
                <div class="md:col-span-2 form-control mb-4">
                    <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Alamat</span></label>
                    <textarea wire:model="customer_address" class="textarea textarea-bordered focus:textarea-primary w-full" rows="2"></textarea>
                </div>
                
                <x-input-text label="Kabupaten" wire:model="kabupaten" />
                <x-input-text label="Kecamatan" wire:model="kecamatan" />
                <x-input-text label="Desa" wire:model="desa" />
                <x-input-text label="Latitude" wire:model="latitude" />
                <x-input-text label="Longitude" wire:model="longitude" />
                <div class="form-control">
                    <label class="label pb-1">
                        <span class="label-text text-xs font-medium text-base-content/85">Pilar *</span>
                    </label>
                    <select wire:model="pilar" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                        <option value="">-- Pilih Pilar --</option>
                        <option value="1. RWO">1. RWO</option>
                        <option value="2. PNR">2. PNR</option>
                        <option value="3. NGVO">3. NGVO</option>
                        <option value="4. GRO">4. GRO</option>
                    </select>
                </div>
                <x-input-text label="Target *" wire:model="target" type="number" step="0.01" />
                <x-input-text label="Remarks SPM" wire:model="keterangan" />
                
                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2 mt-2 pt-3 border-t border-base-200">
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Channel Outlet</span></label>
                        <select wire:model="channel_outlet" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                            <option value="">-- Pilih --</option>
                            <option value="GT">GT</option>
                            <option value="MT">MT</option>
                            <option value="LMT">LMT</option>
                            <option value="OTH">OTH</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Classification</span></label>
                        <select wire:model="classification_outlet" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                            <option value="">-- Pilih --</option>
                            <option value="PARETO">PARETO</option>
                            <option value="NON PARETO">NON PARETO</option>
                            <option value="DUMMY BRIEF">DUMMY BRIEF</option>
                            <option value="DUMMY EVALUASI">DUMMY EVALUASI</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label pb-1"><span class="label-text text-xs font-medium text-base-content/85">Segment</span></label>
                        <select wire:model="segment_outlet" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                            <option value="">-- Pilih --</option>
                            <option value="STAR OUTLET">STAR OUTLET</option>
                            <option value="GROSIR">GROSIR</option>
                            <option value="SEMI-GROSIR">SEMI-GROSIR</option>
                            <option value="RETAIL">RETAIL</option>
                            <option value="PENGRAJIN">PENGRAJIN</option>
                            <option value="TRADER">TRADER</option>
                            <option value="SEASONAL/HAJATAN">SEASONAL/HAJATAN</option>
                        </select>
                    </div>
                </div>
                
                <div class="md:col-span-2 form-control mt-2 pt-3 border-t border-base-200">
                    <label class="label pb-2"><span class="label-text text-xs font-bold text-base-content/70 uppercase tracking-wider">Histori Pilar</span></label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q1</span></label>
                            <select wire:model="pilar_q1" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q2</span></label>
                            <select wire:model="pilar_q2" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q3</span></label>
                            <select wire:model="pilar_q3" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label pb-1"><span class="label-text text-xs text-base-content/85">Q4</span></label>
                            <select wire:model="pilar_q4" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                                <option value="">-</option>
                                <option value="1. RWO">1. RWO</option>
                                <option value="2. PNR">2. PNR</option>
                                <option value="3. NGVO">3. NGVO</option>
                                <option value="4. GRO">4. GRO</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <x-ui.button type="button" variant="neutral" outline wire:click="$set('isEditModalOpen', false)">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check-circle">Simpan Perubahan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <!-- MODAL KONFIRMASI HAPUS -->
    <x-ui.modal wire:key="modal-delete-key" id="modal-delete" title="Konfirmasi Hapus" icon="trash" :open="$isDeleteModalOpen" wire:close="$set('isDeleteModalOpen', false)">
        <p class="text-base-content/80 text-sm">Apakah Anda yakin ingin menghapus data customer ini? Data yang sudah dihapus tidak dapat dikembalikan.</p>
        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" outline wire:click="$set('isDeleteModalOpen', false)">Batal</x-ui.button>
            <x-ui.button type="button" variant="error" icon="trash" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                <span wire:loading.remove wire:target="delete">Ya, Hapus Data</span>
                <span wire:loading wire:target="delete" class="flex items-center gap-1">
                    <span class="loading loading-spinner loading-xs"></span> Menghapus...
                </span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL EXPORT CUSTOMER -->
    <x-ui.modal wire:key="modal-export-key" id="modal-export" title="Konfirmasi Export Data" icon="arrow-up-tray" :open="$isExportModalOpen" wire:close="closeExportModal">
        
        <div class="alert alert-warning shadow-sm text-sm p-3 border border-warning/20 flex flex-col items-start gap-2 mb-4">
            <div class="flex items-start gap-2">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0" />
                <span>
                    Anda akan mengekspor <b>{{ number_format($this->getBaseQuery()->count(), 0, ',', '.') }} baris data</b> ke dalam format Excel. 
                    Semakin banyak data, waktu unduh mungkin akan sedikit lebih lama.
                </span>
            </div>
        </div>

        <div class="form-control w-full mb-3">
            <label class="label"><span class="label-text font-semibold">Filter Wilayah (Opsional)</span></label>
            <div class="grid grid-cols-2 gap-2">
                <select wire:model.live="filterRegion" class="select select-bordered select-sm w-full focus:select-primary">
                    <option value="">Semua Region</option>
                    @foreach($regions as $r) <option value="{{ $r->region_code }}">{{ $r->region_name }}</option> @endforeach
                </select>
                <select wire:model.live="filterArea" class="select select-bordered select-sm w-full focus:select-primary">
                    <option value="">Semua Area</option>
                    @foreach($areas as $a) <option value="{{ $a->area_code }}">{{ $a->area_name }}</option> @endforeach
                </select>
            </div>
        </div>
        
        <div class="form-control w-full mb-3">
            <label class="label"><span class="label-text font-semibold">Filter Pengguna (Opsional)</span></label>
            <div class="grid grid-cols-2 gap-2">
                <select wire:model.live="filterSupervisor" class="select select-bordered select-sm w-full focus:select-primary">
                    <option value="">Semua SPV/DSR</option>
                    @foreach($supervisors as $s) <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name ?? $s->supervisor_code }}</option> @endforeach
                </select>
                <select wire:model.live="filterDistributor" class="select select-bordered select-sm w-full focus:select-primary">
                    <option value="">Semua Distributor</option>
                    @foreach($distributors as $d) <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option> @endforeach
                </select>
            </div>
        </div>

        <div class="form-control w-full mb-3">
            <label class="label"><span class="label-text font-semibold">Kategori Toko (Opsional)</span></label>
            <div class="grid grid-cols-2 gap-2">
                <select wire:model.live="filterPareto" class="select select-bordered select-sm w-full focus:select-primary">
                    <option value="">Semua Pareto</option>
                    <option value="PARETO">PARETO</option>
                    <option value="NON PARETO">NON PARETO</option>
                </select>
                <select wire:model.live="filterPilar" class="select select-bordered select-sm w-full focus:select-primary">
                    <option value="">Semua Pilar</option>
                    <option value="1. RWO">1. RWO</option>
                    <option value="2. PNR">2. PNR</option>
                    <option value="3. NGVO">3. NGVO</option>
                    <option value="4. GRO">4. GRO</option>
                </select>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" outline wire:click="closeExportModal">Batal</x-ui.button>
            <x-ui.button type="button" variant="primary" icon="arrow-down-tray" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                <span wire:loading.remove wire:target="export">Mulai Export ({{ number_format($this->getBaseQuery()->count(), 0, ',', '.') }})</span>
                <span wire:loading wire:target="export" class="flex items-center gap-1">
                    <span class="loading loading-spinner loading-xs"></span> Mengunduh...
                </span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL IMPORT CUSTOMER -->
    <x-ui.modal wire:key="modal-import-key" id="modal-import" title="Import Master Customer" icon="arrow-down-on-square" :open="$isImportModalOpen" wire:close="closeImportModal">
        
        @if($isImporting)
        <div class="py-4" wire:poll.1500ms="checkImportProgress">
            <div class="flex flex-col items-center justify-center space-y-4">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <h3 class="font-bold text-lg">Memproses Import...</h3>
                <p class="text-sm text-base-content/70 text-center">Harap tunggu, proses ini berjalan di latar belakang.</p>
                
                <div class="stats shadow w-full max-w-sm mt-4">
                    <div class="stat place-items-center">
                        <div class="stat-title">Sukses</div>
                        <div class="stat-value text-success text-3xl">{{ number_format($liveSuccessCount, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat place-items-center">
                        <div class="stat-title">Dilewati</div>
                        <div class="stat-value text-warning text-3xl">{{ number_format($liveSkipCount, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat place-items-center">
                        <div class="stat-title">Error</div>
                        <div class="stat-value text-error text-3xl">{{ number_format($liveErrorCount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        @elseif($importCompleted)
        <div class="py-4">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-success/20 flex items-center justify-center text-success">
                    <x-heroicon-s-check-circle class="w-10 h-10" />
                </div>
                <h3 class="font-bold text-lg">Import Selesai!</h3>
                
                <div class="stats shadow w-full max-w-sm mt-4">
                    <div class="stat place-items-center">
                        <div class="stat-title">Sukses</div>
                        <div class="stat-value text-success text-3xl">{{ number_format($liveSuccessCount, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat place-items-center">
                        <div class="stat-title">Dilewati</div>
                        <div class="stat-value text-warning text-3xl">{{ number_format($liveSkipCount, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat place-items-center">
                        <div class="stat-title">Error</div>
                        <div class="stat-value text-error text-3xl">{{ number_format($liveErrorCount, 0, ',', '.') }}</div>
                    </div>
                </div>
                
                @if($importLogCount > 0 || $liveSkipCount > 0)
                <p class="text-sm text-center mt-2 px-4 text-base-content/70">Terdapat <b>{{ $importLogCount }}</b> baris gagal dan <b>{{ $liveSkipCount }}</b> baris dilewati.</p>
                
                <div class="w-full text-left mt-2">
                    <div class="bg-base-200 rounded-lg p-3 max-h-48 overflow-y-auto text-xs font-mono">
                        @if(count($importErrorLogs) > 0)
                            <div class="font-bold text-error mb-1 border-b border-base-300 pb-1">Rincian Gagal:</div>
                            <ul class="list-disc pl-4 mb-3 text-error/80">
                                @foreach($importErrorLogs as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if(count($importSkipLogs) > 0)
                            <div class="font-bold text-warning mb-1 border-b border-base-300 pb-1">Rincian Dilewati:</div>
                            <ul class="list-disc pl-4 text-warning/80">
                                @foreach($importSkipLogs as $skip)
                                    <li>{{ $skip }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <a href="{{ $importErrorLogUrl }}" target="_blank" class="btn btn-sm btn-outline btn-warning mt-2 w-full" download>
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" /> Download Full Log (.txt)
                </a>
                @endif
            </div>
        </div>
        @else
        <div class="form-control w-full mb-3">
            <label class="label"><span class="label-text font-semibold">Metode Import</span></label>
            <select wire:model="importMethod" class="select select-bordered select-sm w-full focus:select-primary">
                <option value="upsert">Update & Insert (Timpa data lama)</option>
                <option value="insert_only">Insert Only (Lewati data lama)</option>
            </select>
        </div>
        
        <div class="form-control w-full mb-4">
            <label class="label">
                <span class="label-text font-semibold">Pilih File Excel (.xlsx, .xls)</span>
            </label>
            <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="file-input file-input-bordered file-input-sm w-full focus:file-input-primary" required />
            <div wire:loading wire:target="importFile" class="text-xs text-info mt-1">Mengunggah file...</div>
            @error('importFile') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
        </div>
        
        <div class="alert alert-info shadow-sm text-sm p-3 border border-info/20 flex flex-col items-start gap-2 mb-2">
            <div class="flex items-start gap-2">
                <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                <span>Format file import harus persis sesuai dengan format template. Baris yang memiliki error akan otomatis dilewati dan Anda dapat mengunduh log errornya nanti.</span>
            </div>
            <button type="button" wire:click="downloadTemplate" wire:loading.attr="disabled" wire:target="downloadTemplate" class="btn btn-sm btn-info btn-outline mt-1 bg-white">
                <span wire:loading.remove wire:target="downloadTemplate" class="flex items-center gap-1">
                    <x-heroicon-s-document-arrow-down class="w-4 h-4" /> Download Format Import
                </span>
                <span wire:loading wire:target="downloadTemplate" class="flex items-center gap-1">
                    <span class="loading loading-spinner loading-xs"></span> Mengunduh...
                </span>
            </button>
        </div>
        @endif

        <x-slot:footer>
            @if($isImporting)
                <x-ui.button type="button" variant="neutral" outline wire:click="closeImportModal" disabled>Tutup</x-ui.button>
            @elseif($importCompleted)
                <x-ui.button type="button" variant="neutral" outline wire:click="closeImportModal">Tutup</x-ui.button>
            @else
                <x-ui.button type="button" variant="neutral" outline wire:click="closeImportModal">Batal</x-ui.button>
                <x-ui.button type="button" variant="primary" icon="arrow-up-tray" wire:click="import" wire:loading.attr="disabled" wire:target="import">
                    <span wire:loading.remove wire:target="import">Mulai Import</span>
                    <span wire:loading wire:target="import" class="flex items-center gap-1">
                        <span class="loading loading-spinner loading-xs"></span> Memproses...
                    </span>
                </x-ui.button>
            @endif
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL DETAIL CUSTOMER -->
    <x-ui.modal wire:key="modal-detail-key" id="modal-detail" title="Detail Customer" icon="eye" size="xl" :open="$isDetailModalOpen" wire:close="$set('isDetailModalOpen', false)">
        @if($detailData)
        @php
            $detailBadgeColor = match($detailData->pilar) { 
                '1. RWO' => 'error', '2. PNR' => 'warning', '3. NGVO' => 'success', '4. GRO' => 'info', default => 'neutral' 
            };
            $q1Color = match($detailData->pilar_q1 ?? '') { '1. RWO' => 'badge-error', '2. PNR' => 'badge-warning', '3. NGVO' => 'badge-success', '4. GRO' => 'badge-info', default => 'badge-ghost' };
            $q2Color = match($detailData->pilar_q2 ?? '') { '1. RWO' => 'badge-error', '2. PNR' => 'badge-warning', '3. NGVO' => 'badge-success', '4. GRO' => 'badge-info', default => 'badge-ghost' };
            $q3Color = match($detailData->pilar_q3 ?? '') { '1. RWO' => 'badge-error', '2. PNR' => 'badge-warning', '3. NGVO' => 'badge-success', '4. GRO' => 'badge-info', default => 'badge-ghost' };
            $q4Color = match($detailData->pilar_q4 ?? '') { '1. RWO' => 'badge-error', '2. PNR' => 'badge-warning', '3. NGVO' => 'badge-success', '4. GRO' => 'badge-info', default => 'badge-ghost' };
        @endphp

        <div class="flex flex-col gap-0 -mx-1">

            {{-- ① Hero / Identitas Toko --}}
            <div class="px-1 pb-4 mb-4 border-b border-base-200 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-base-content/40 uppercase tracking-widest mb-1">Nama Toko</p>
                    <h3 class="text-xl font-extrabold text-base-content leading-snug">{{ $detailData->customer_name ?? '-' }}</h3>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                        <span class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold text-base-content/60 bg-base-200 px-2 py-0.5 rounded border border-base-300">
                            <x-heroicon-o-identification class="w-3 h-3" /> {{ $detailData->customer_code_prc ?? '-' }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold text-base-content/60 bg-base-200 px-2 py-0.5 rounded border border-base-300">
                            <x-heroicon-o-key class="w-3 h-3" /> {{ $detailData->uniq_kd ?? '-' }}
                        </span>
                    </div>
                </div>
                <span class="badge badge-lg badge-{{ $detailBadgeColor }} font-bold shrink-0 px-4 py-3 shadow">{{ $detailData->pilar ?? '-' }}</span>
            </div>

            {{-- ② Info Baris (label kiri, value kanan — pola konsisten) --}}
            <div class="flex flex-col gap-0 px-1">
                
                {{-- Group: Organisasi --}}
                <div class="mb-3">
                    <p class="text-[10px] font-black text-base-content/30 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <x-heroicon-s-building-office-2 class="w-3.5 h-3.5" /> Organisasi
                    </p>
                    <div class="rounded-xl border border-base-200 divide-y divide-base-100 overflow-hidden bg-base-100">
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Region</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->region_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Area</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->area_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Supervisor</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->supervisor_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-start px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium pt-0.5">Distributor</span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-base-content/90">{{ $detailData->distributor_name ?? '-' }}</span>
                                <span class="font-mono text-[10px] bg-base-200 border border-base-300 text-base-content/50 px-1.5 py-0.5 rounded">{{ $detailData->distributor_code }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Group: Lokasi --}}
                <div class="mb-3">
                    <p class="text-[10px] font-black text-base-content/30 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <x-heroicon-s-map-pin class="w-3.5 h-3.5" /> Lokasi
                    </p>
                    <div class="rounded-xl border border-base-200 divide-y divide-base-100 overflow-hidden bg-base-100">
                        <div class="flex items-start px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium pt-0.5">Alamat</span>
                            <span class="text-sm font-semibold text-base-content/90 leading-relaxed">{{ $detailData->customer_address ?? '-' }}</span>
                        </div>
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Kabupaten</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->kabupaten ?? '-' }}</span>
                        </div>
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Kecamatan</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->kecamatan ?? '-' }}</span>
                        </div>
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Desa</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->desa ?? '-' }}</span>
                        </div>
                        @if($detailData->latitude || $detailData->longitude)
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Koordinat</span>
                            <span class="font-mono text-xs text-base-content/70 bg-base-200 px-2 py-0.5 rounded">{{ $detailData->latitude ?? '-' }}, {{ $detailData->longitude ?? '-' }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Group: Klasifikasi Outlet --}}
                <div class="mb-3">
                    <p class="text-[10px] font-black text-base-content/30 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <x-heroicon-s-tag class="w-3.5 h-3.5" /> Klasifikasi Outlet
                    </p>
                    <div class="rounded-xl border border-base-200 divide-y divide-base-100 overflow-hidden bg-base-100">
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Channel</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->channel_outlet ?? '-' }}</span>
                        </div>
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Classification</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->classification_outlet ?? '-' }}</span>
                        </div>
                        <div class="flex items-center px-4 py-2.5 gap-4 hover:bg-base-50">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Segment</span>
                            <span class="text-sm font-semibold text-base-content/90">{{ $detailData->segment_outlet ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Group: Target & Pilar --}}
                <div class="mb-3">
                    <p class="text-[10px] font-black text-base-content/30 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <x-heroicon-s-chart-bar class="w-3.5 h-3.5" /> Target & Pilar
                    </p>
                    <div class="rounded-xl border border-base-200 overflow-hidden bg-base-100">
                        <div class="flex items-center px-4 py-3 gap-4 border-b border-base-100">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Target</span>
                            <span class="text-base font-black font-mono text-primary">Rp {{ number_format((float)($detailData->target ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center px-4 py-3 gap-4">
                            <span class="w-28 shrink-0 text-xs text-base-content/40 font-medium">Histori Pilar</span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-[9px] font-bold text-base-content/30 uppercase">Q1</span>
                                    <span class="badge badge-sm {{ $q1Color }} badge-outline font-bold">{{ $detailData->pilar_q1 ?? '-' }}</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-[9px] font-bold text-base-content/30 uppercase">Q2</span>
                                    <span class="badge badge-sm {{ $q2Color }} badge-outline font-bold">{{ $detailData->pilar_q2 ?? '-' }}</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-[9px] font-bold text-base-content/30 uppercase">Q3</span>
                                    <span class="badge badge-sm {{ $q3Color }} badge-outline font-bold">{{ $detailData->pilar_q3 ?? '-' }}</span>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-[9px] font-bold text-base-content/30 uppercase">Q4</span>
                                    <span class="badge badge-sm {{ $q4Color }} badge-outline font-bold">{{ $detailData->pilar_q4 ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Remarks SPM --}}
                @if(!empty($detailData->keterangan))
                <div class="bg-warning/10 border border-warning/20 p-3.5 rounded-xl text-sm flex gap-3">
                    <x-heroicon-s-chat-bubble-left-ellipsis class="w-4 h-4 text-warning shrink-0 mt-0.5" />
                    <div>
                        <span class="block text-[10px] font-black text-warning uppercase tracking-wider mb-0.5">Remarks SPM</span>
                        <p class="text-base-content/80 leading-relaxed text-sm">{{ $detailData->keterangan }}</p>
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif
        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" outline wire:click="$set('isDetailModalOpen', false)">Tutup</x-ui.button>
        </x-slot:footer>
        </x-ui.modal>

        {{-- MODAL SYNC WILAYAH GEOSPASIAL --}}
        <x-ui.modal id="modal-sync" :open="$isSyncModalOpen" wire:close="$set('isSyncModalOpen', false)" size="md">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-info/10 flex items-center justify-center shrink-0">
                        <x-heroicon-s-map class="w-6 h-6 text-info" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-base-content">Sinkronisasi Wilayah Geospasial</h3>
                        <p class="text-sm text-base-content/60 mt-1">Mengisi Kabupaten, Kecamatan, dan Desa otomatis berdasarkan titik koordinat (PostGIS).</p>
                    </div>
                </div>

                @if(!$isSyncing && !$syncCompleted)
                <div class="bg-base-200/50 rounded-xl p-4 mb-6 border border-base-200">
                    <p class="text-sm text-base-content/80 text-center">
                        Sistem akan menjalankan kueri spasial untuk seluruh data toko yang memiliki latitude & longitude, lalu menimpanya dengan data dari Peta Batas Wilayah <b>(Overwrite All)</b>.<br><br>
                        Proses ini mungkin memakan waktu beberapa saat tergantung jumlah data.
                    </p>
                </div>
                
                @if($syncMessage)
                    <div class="alert alert-error mb-4 shadow-sm text-sm p-3">
                        <x-heroicon-s-x-circle class="w-5 h-5 shrink-0"/>
                        <span>{{ $syncMessage }}</span>
                    </div>
                @endif
                
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" wire:click="closeSyncModal" class="btn btn-ghost btn-sm">Batal</button>
                    <button type="button" wire:click="startSync" class="btn btn-info btn-sm gap-2">
                        <x-heroicon-s-play class="w-4 h-4" />
                        Mulai Sync
                    </button>
                </div>
                
                @elseif($isSyncing)
                <div wire:poll.2s="checkSyncProgress">
                    <div class="text-center py-4">
                        <span class="loading loading-spinner loading-lg text-info mb-4"></span>
                        <h4 class="font-bold text-base-content">Sinkronisasi Sedang Berjalan</h4>
                        <p class="text-sm text-base-content/60 mt-1 mb-4">{{ $syncMessage }}</p>
                        
                        <div class="bg-base-100 rounded-lg p-3 text-center border border-base-200 shadow-sm mx-auto w-3/4 mb-4">
                            <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-1">Diproses</div>
                            <div class="text-2xl font-bold text-info">{{ number_format($syncProcessed, 0, ',', '.') }} <span class="text-sm text-base-content/40 font-normal">/ {{ number_format($syncTotal, 0, ',', '.') }}</span></div>
                        </div>

                        <progress class="progress progress-info w-full" value="{{ $syncTotal > 0 ? ($syncProcessed / $syncTotal) * 100 : 0 }}" max="100"></progress>
                        
                        <p class="text-xs text-base-content/50 mt-4">Mohon jangan tutup jendela ini.</p>
                    </div>
                </div>
                @elseif($syncCompleted)
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-s-check-circle class="w-10 h-10 text-success" />
                    </div>
                    <h4 class="font-bold text-base-content text-xl mb-2">Sinkronisasi Selesai!</h4>
                    
                    <div class="bg-base-100 rounded-lg p-4 text-center border border-base-200 shadow-sm mx-auto w-full max-w-sm mb-4">
                        <div class="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-1">Total Diperbarui</div>
                        <div class="text-3xl font-bold text-success">{{ number_format($syncUpdatedCount, 0, ',', '.') }} <span class="text-sm text-base-content/40 font-normal">Toko</span></div>
                    </div>
                    
                    <p class="text-sm text-base-content/70 mt-2">{{ $syncMessage }}</p>
                </div>
                
                <div class="flex justify-center mt-6">
                    <button type="button" wire:click="closeSyncModal" class="btn btn-primary btn-sm px-8">Tutup</button>
                </div>
                @endif
            </div>
        </x-ui.modal>
    </div>
