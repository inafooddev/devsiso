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
                    <x-ui.action-button type="export" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                        <span wire:loading wire:target="export" class="ml-1">
                            <span class="loading loading-spinner loading-xs"></span>
                        </span>
                    </x-ui.action-button>
                    @endcanExport
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
                        
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($data as $item)
                    <tr wire:key="cust-row-{{ $item->customer_code }}-{{ $item->distributor_code }}" class="hover:bg-base-200/50 transition-colors">
                        <td class="text-xs text-base-content/70">{{ $item->region_name }}</td>
                        <td class="text-xs text-base-content/70">{{ $item->area_name }}</td>
                        <td class="text-xs text-base-content/70">{{ $item->supervisor_name ?? '-' }}</td>
                        <td class="text-xs">
                            <div class="max-w-[150px] truncate text-base-content/80 font-medium" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</div>
                            <div class="text-[10px] text-base-content/50 font-mono mt-0.5">{{ $item->distributor_code }}</div>
                        </td>
                        <td class="max-w-[120px] truncate font-mono text-xs text-base-content/70" title="{{ $item->customer_code }}">{{ $item->customer_code }}</td>
                        <td class="font-mono text-xs">{{ $item->uniq_kd ?? '-' }}</td>
                        <td class="min-w-[200px] font-bold text-base-content/90">{{ $item->customer_name }}</td>
                        <td class="max-w-[200px] truncate text-xs text-base-content/60" title="{{ $item->customer_address }}">{{ $item->customer_address }}</td>
                        <td class="text-xs text-base-content/70">{{ $item->kecamatan }}</td>
                        <td class="text-xs text-base-content/70">{{ $item->desa }}</td>
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
                        <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                            <div class="flex items-center justify-center gap-1">
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
                        <td colspan="13" class="text-center py-8 text-base-content/50">Tidak ada data ditemukan.</td>
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
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full">
                    <option value="">-- Semua Region --</option>
                    @foreach($regions as $r) 
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Area</span></label>
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full" @if(!$filterRegion) disabled @endif>
                    <option value="">-- Semua Area --</option>
                    @foreach($areas as $a) 
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Supervisor</span></label>
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered w-full" @if(!$filterArea) disabled @endif>
                    <option value="">-- Semua Supervisor --</option>
                    @foreach($supervisors as $s) 
                        <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option> 
                    @endforeach
                </select>
            </div>

            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Distributor</span></label>
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered w-full" @if(!$filterRegion) disabled @endif>
                    <option value="">-- Semua Distributor --</option>
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
                <x-input-text label="Keterangan *" wire:model="keterangan" />
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
                <x-input-text label="Keterangan *" wire:model="keterangan" />
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

    <!-- MODAL IMPORT CUSTOMER -->
    <x-ui.modal wire:key="modal-import-key" id="modal-import" title="Import Master Customer" icon="arrow-down-on-square" :open="$isImportModalOpen" wire:close="closeImportModal">
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

        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" outline wire:click="closeImportModal">Batal</x-ui.button>
            <x-ui.button type="button" variant="primary" icon="arrow-up-tray" wire:click="import" wire:loading.attr="disabled" wire:target="import">
                <span wire:loading.remove wire:target="import">Mulai Import</span>
                <span wire:loading wire:target="import" class="flex items-center gap-1">
                    <span class="loading loading-spinner loading-xs"></span> Memproses...
                </span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

</div>
