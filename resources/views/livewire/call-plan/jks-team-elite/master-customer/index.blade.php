<div>
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

    <div class="mx-auto p-4 sm:p-6 lg:p-8">
        
        <!-- Notifikasi -->
        @if (session()->has('message'))
            <x-ui.notif type="success" dismissible class="mb-6">
                {{ session('message') }}
            </x-ui.notif>
        @endif
        @if (session()->has('error'))
            <x-ui.notif type="error" dismissible class="mb-6">
                {{ session('error') }}
            </x-ui.notif>
        @endif

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <!-- Total Customer -->
            <div class="bg-base-100 rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-between h-24 relative transition-all hover:shadow-md hover:bg-base-200/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-base-content/60 font-bold uppercase tracking-wider">Total Customer</span>
                    <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                        <x-heroicon-s-users class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-bold mt-auto text-primary tracking-tight">{{ number_format($kpi->total_customer ?? 0, 0, ',', '.') }}</div>
            </div>
            
            <!-- Total Pareto -->
            <div class="bg-base-100 rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-between h-24 relative transition-all hover:shadow-md hover:bg-base-200/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-base-content/60 font-bold uppercase tracking-wider">Total Pareto</span>
                    <div class="p-1.5 rounded-lg bg-secondary/10 text-secondary">
                        <x-heroicon-s-tag class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-bold mt-auto text-secondary tracking-tight">{{ number_format($kpi->total_pareto ?? 0, 0, ',', '.') }}</div>
            </div>

            <!-- Total RWO -->
            <div class="bg-base-100 rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-between h-24 relative transition-all hover:shadow-md hover:bg-base-200/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-base-content/60 font-bold uppercase tracking-wider">Total RWO</span>
                    <div class="p-1.5 rounded-lg bg-error/10 text-error">
                        <x-heroicon-s-tag class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-bold mt-auto text-error tracking-tight">{{ number_format($kpi->total_rwo ?? 0, 0, ',', '.') }}</div>
            </div>

            <!-- Total PNR -->
            <div class="bg-base-100 rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-between h-24 relative transition-all hover:shadow-md hover:bg-base-200/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-base-content/60 font-bold uppercase tracking-wider">Total PNR</span>
                    <div class="p-1.5 rounded-lg bg-warning/10 text-warning">
                        <x-heroicon-s-tag class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-bold mt-auto text-warning tracking-tight">{{ number_format($kpi->total_pnr ?? 0, 0, ',', '.') }}</div>
            </div>

            <!-- Total NGVO -->
            <div class="bg-base-100 rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-between h-24 relative transition-all hover:shadow-md hover:bg-base-200/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-base-content/60 font-bold uppercase tracking-wider">Total NGVO</span>
                    <div class="p-1.5 rounded-lg bg-success/10 text-success">
                        <x-heroicon-s-tag class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-bold mt-auto text-success tracking-tight">{{ number_format($kpi->total_ngvo ?? 0, 0, ',', '.') }}</div>
            </div>

            <!-- Total GRO -->
            <div class="bg-base-100 rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-between h-24 relative transition-all hover:shadow-md hover:bg-base-200/20">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-base-content/60 font-bold uppercase tracking-wider">Total GRO</span>
                    <div class="p-1.5 rounded-lg bg-info/10 text-info">
                        <x-heroicon-s-tag class="w-4 h-4" />
                    </div>
                </div>
                <div class="text-2xl font-bold mt-auto text-info tracking-tight">{{ number_format($kpi->total_gro ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Panel Table -->
        <div class="bg-base-100 shadow-xl rounded-2xl overflow-hidden border border-base-200">
            <!-- Header Panel -->
            <div class="px-6 py-4 border-b border-base-200 bg-base-200/30 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Kiri: Search & Direct Dropdowns -->
                <div class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto flex-1">
                    <div class="relative w-full sm:w-64">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/50" />
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode/Nama/Alamat/Pilar..." 
                               class="input input-sm input-bordered w-full pl-9 focus:input-primary">
                    </div>
                    
                    <select wire:model.live="filterPareto" class="select select-sm select-bordered w-full sm:w-36 focus:select-primary">
                        <option value="">-- Semua Status --</option>
                        <option value="PARETO">Pareto</option>
                        <option value="NON PARETO">Non-Pareto</option>
                    </select>

                    <select wire:model.live="filterPilar" class="select select-sm select-bordered w-full sm:w-36 focus:select-primary">
                        <option value="">-- Semua Pilar --</option>
                        <option value="1. RWO">1. RWO</option>
                        <option value="2. PNR">2. PNR</option>
                        <option value="3. NGVO">3. NGVO</option>
                        <option value="4. GRO">4. GRO</option>
                    </select>
                </div>

                <!-- Kanan: Aksi (Filter, Export) -->
                <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto justify-end">
                    <!-- TOMBOL FILTER -->
                    <x-ui.button variant="neutral" size="sm" outline wire:click="openFilterModal">
                        <x-heroicon-s-funnel class="w-4 h-4 mr-1" /> Filter
                        @if($filterRegion || $filterArea || $filterSupervisor || $filterDistributor || $filterPareto || $filterPilar)
                            <div class="badge badge-primary badge-sm ml-2">!</div>
                        @endif
                    </x-ui.button>                    
                    
                    <!-- TOMBOL TAMBAH TOKO -->
                    @canEdit('call-plan.jks-team-elite.master-customer')
                    <x-ui.button variant="primary" size="sm" wire:click="openCreateModal">
                        <x-heroicon-s-plus class="w-4 h-4 mr-1" /> Tambah Toko
                    </x-ui.button>
                    @endcanEdit
                    
                    <!-- TOMBOL EXPORT -->
                    @canExport('call-plan.jks-team-elite.master-customer')
                    <x-ui.button variant="info" size="sm" wire:click="export" wire:loading.attr="disabled" wire:target="export">
                        <span wire:loading.remove wire:target="export" class="flex items-center gap-1">
                            <x-heroicon-s-arrow-up-on-square class="w-4 h-4" /> Export Excel
                        </span>
                        <span wire:loading wire:target="export" class="flex items-center gap-1">
                            <span class="loading loading-spinner loading-xs"></span> Proses...
                        </span>
                    </x-ui.button>
                    @endcanExport
                </div>
            </div>

            <!-- Tabel -->
            <div wire:key="table-wrapper-{{ md5($search . $filterRegion . $filterArea . $filterSupervisor . $filterDistributor . $filterPareto . $data->currentPage()) }}">
                <x-ui.table hover striped sticky loading="{{ false }}" empty="Tidak ada data ditemukan." class="max-h-[60vh] overflow-y-auto border-x-0 border-b-0 rounded-none shadow-none">
                <x-slot:head>
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

                    </tr>
                </x-slot:head>

                @foreach($data as $item)
                <tr wire:key="cust-row-{{ $item->customer_code }}-{{ $item->distributor_code }}">
                    <td class="whitespace-nowrap text-xs text-base-content/50 font-normal">{{ $item->region_name }}</td>
                    <td class="whitespace-nowrap text-xs text-base-content/50 font-normal">{{ $item->area_name }}</td>
                    <td class="whitespace-nowrap text-xs text-base-content/50 font-normal">{{ $item->supervisor_name ?? '-' }}</td>
                    <td class="text-xs font-normal">
                        <div class="max-w-[120px] truncate text-base-content/60" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</div>
                        <div class="text-[9px] text-base-content/40 font-mono">{{ $item->distributor_code }}</div>
                    </td>
                    <td class="max-w-[95px] truncate font-mono text-xs" title="{{ $item->customer_code }}">{{ $item->customer_code }}</td>
                    <td class="whitespace-nowrap">{{ $item->uniq_kd ?? '-' }}</td>
                    <td class="min-w-[200px] font-bold">{{ $item->customer_name }}</td>
                    <td class="max-w-[125px] truncate text-xs opacity-70" title="{{ $item->customer_address }}">{{ $item->customer_address }}</td>
                    <td>{{ $item->kecamatan }}</td>
                    <td>{{ $item->desa }}</td>
                    <td class="whitespace-nowrap text-center">
                        <x-ui.badge variant="neutral">{{ $item->pilar ?? '-' }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap text-right font-mono font-bold">{{ number_format($item->target, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </x-ui.table>
            </div>
            
            @if($data->hasPages())
                <div class="px-6 py-4 border-t border-base-200 bg-base-200/30">
                    {{ $data->links() }}
                </div>
            @endif
        </div>
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
                <x-input-text label="Uniq Kd" wire:model="uniq_kd" placeholder="Contoh: LMG-0001" />
                
                <div class="md:col-span-2">
                    <x-input-text label="Nama Toko" wire:model="customer_name" />
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
                        <span class="label-text text-xs font-medium text-base-content/85">Pilar</span>
                    </label>
                    <select wire:model="pilar" class="select select-sm select-bordered w-full focus:select-primary text-xs">
                        <option value="">-- Pilih Pilar --</option>
                        <option value="1. RWO">1. RWO</option>
                        <option value="2. PNR">2. PNR</option>
                        <option value="3. NGVO">3. NGVO</option>
                        <option value="4. GRO">4. GRO</option>
                    </select>
                </div>
                <x-input-text label="Target" wire:model="target" type="number" step="0.01" />
                <x-input-text label="Keterangan" wire:model="keterangan" />
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <x-ui.button type="button" variant="neutral" outline wire:click="$set('isCreateModalOpen', false)">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check-circle">Simpan Customer</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

</div>
