<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">List Toko Pareto (Team Elite)</x-slot>

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
        <x-ui.notif type="success" dismissible class="shrink-0">
            {{ session('message') }}
        </x-ui.notif>
    @endif
    @if (session()->has('error'))
        <x-ui.notif type="error" dismissible class="shrink-0">
            {{ session('error') }}
        </x-ui.notif>
    @endif

    {{-- KPI Cards Section --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 md:gap-3 shrink-0">
        <!-- Total Toko -->
        <div wire:click="setFilterKpi('')" class="bg-base-100 p-3 rounded-xl shadow-sm border flex flex-col relative overflow-hidden group cursor-pointer transition-all {{ $filterKpi === '' ? 'ring-2 ring-primary border-primary/50' : 'border-base-300 hover:border-primary/50' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="absolute right-2 top-2 text-primary opacity-20 group-hover:opacity-40 transition-opacity">
                <x-heroicon-s-building-storefront class="w-10 h-10" />
            </div>
            <div class="relative z-10 flex flex-col">
                <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider truncate">Total Toko</h3>
                <div class="text-lg font-bold leading-none mt-1 truncate">{{ number_format($kpi->total_toko ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-success mt-1.5 font-bold flex items-center gap-1">
                    <x-heroicon-s-check-circle class="w-3 h-3"/> On JKS: {{ number_format($kpi->total_toko_jks_y ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Total Target -->
        <div class="bg-base-100 p-3 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden transition-all">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-primary/10 transition-transform"></div>
            <div class="absolute right-2 top-2 text-primary opacity-20 transition-opacity">
                <x-heroicon-s-currency-dollar class="w-10 h-10" />
            </div>
            <div class="relative z-10 flex flex-col">
                <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider truncate">Total Target</h3>
                <div class="text-lg font-bold leading-none mt-1 truncate">{{ number_format($kpi->total_target ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-success mt-1.5 font-bold flex items-center gap-1">
                    <x-heroicon-s-check-circle class="w-3 h-3"/> On JKS: {{ number_format($kpi->total_target_jks_y ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Toko RWO -->
        <div wire:click="setFilterKpi('rwo')" class="bg-base-100 p-3 rounded-xl shadow-sm border flex flex-col relative overflow-hidden group cursor-pointer transition-all {{ $filterKpi === 'rwo' ? 'ring-2 ring-primary border-primary/50' : 'border-base-300 hover:border-primary/50' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="absolute right-2 top-2 text-primary opacity-20 group-hover:opacity-40 transition-opacity">
                <x-heroicon-s-tag class="w-10 h-10" />
            </div>
            <div class="relative z-10 flex flex-col">
                <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider truncate">Toko RWO</h3>
                <div class="text-lg font-bold leading-none mt-1 truncate">{{ number_format($kpi->total_rwo ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-success mt-1.5 font-bold flex items-center gap-1">
                    <x-heroicon-s-check-circle class="w-3 h-3"/> On JKS: {{ number_format($kpi->total_rwo_jks_y ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Toko PNR -->
        <div wire:click="setFilterKpi('pnr')" class="bg-base-100 p-3 rounded-xl shadow-sm border flex flex-col relative overflow-hidden group cursor-pointer transition-all {{ $filterKpi === 'pnr' ? 'ring-2 ring-primary border-primary/50' : 'border-base-300 hover:border-primary/50' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="absolute right-2 top-2 text-primary opacity-20 group-hover:opacity-40 transition-opacity">
                <x-heroicon-s-tag class="w-10 h-10" />
            </div>
            <div class="relative z-10 flex flex-col">
                <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider truncate">Toko PNR</h3>
                <div class="text-lg font-bold leading-none mt-1 truncate">{{ number_format($kpi->total_pnr ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-success mt-1.5 font-bold flex items-center gap-1">
                    <x-heroicon-s-check-circle class="w-3 h-3"/> On JKS: {{ number_format($kpi->total_pnr_jks_y ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Toko NGVO -->
        <div wire:click="setFilterKpi('ngvo')" class="bg-base-100 p-3 rounded-xl shadow-sm border flex flex-col relative overflow-hidden group cursor-pointer transition-all {{ $filterKpi === 'ngvo' ? 'ring-2 ring-primary border-primary/50' : 'border-base-300 hover:border-primary/50' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="absolute right-2 top-2 text-primary opacity-20 group-hover:opacity-40 transition-opacity">
                <x-heroicon-s-tag class="w-10 h-10" />
            </div>
            <div class="relative z-10 flex flex-col">
                <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider truncate">Toko NGVO</h3>
                <div class="text-lg font-bold leading-none mt-1 truncate">{{ number_format($kpi->total_ngvo ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-success mt-1.5 font-bold flex items-center gap-1">
                    <x-heroicon-s-check-circle class="w-3 h-3"/> On JKS: {{ number_format($kpi->total_ngvo_jks_y ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Total No Geotag -->
        <div wire:click="setFilterKpi('no_geotag')" class="bg-base-100 p-3 rounded-xl shadow-sm border flex flex-col relative overflow-hidden group cursor-pointer transition-all {{ $filterKpi === 'no_geotag' ? 'ring-2 ring-primary border-primary/50' : 'border-base-300 hover:border-primary/50' }}">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="absolute right-2 top-2 text-primary opacity-20 group-hover:opacity-40 transition-opacity">
                <x-heroicon-s-map-pin class="w-10 h-10" />
            </div>
            <div class="relative z-10 flex flex-col">
                <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider truncate">Total No Geotag</h3>
                <div class="text-lg font-bold leading-none mt-1 truncate">{{ number_format($kpi->total_no_geotag ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-success mt-1.5 font-bold flex items-center gap-1">
                    <x-heroicon-s-check-circle class="w-3 h-3"/> On JKS: {{ number_format($kpi->total_no_geotag_jks_y ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full md:w-auto">
                <h2 class="text-base md:text-lg font-bold">Daftar Toko Pareto</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data toko pareto</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start md:justify-end gap-2 md:gap-3 w-full md:w-auto">
                <div class="relative grow md:grow-0 group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input type="text" wire:model.live.debounce.500ms="search" placeholder="Cari Kode/Nama/Alamat/Pilar..." 
                           class="input input-sm input-bordered pl-10 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300" />
                </div>

                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <!-- TOMBOL FILTER -->
                    <button type="button" class="btn btn-sm btn-ghost border border-base-300 bg-base-100" wire:click="openFilterModal">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Filter
                        @php $activeFilterCount = (int)(bool)$filterRegion + (int)(bool)$filterArea + (int)(bool)$filterSupervisor; @endphp
                        @if($activeFilterCount > 0)
                            <div class="badge badge-primary badge-sm ml-1">{{ $activeFilterCount }}</div>
                        @endif
                    </button>

                    @canEdit('plan-call-team-elite.toko-pareto')
                    <x-ui.action-button type="default" icon="map-pin" label="Sync Geotag" wire:click="syncGeotag" wire:loading.attr="disabled" />
                    @endcanEdit

                    @canImport('plan-call-team-elite.toko-pareto')
                    <x-ui.action-button type="import" wire:click="openImportModal" />
                    @endcanImport

                    @canAdd('plan-call-team-elite.toko-pareto')
                    <x-ui.action-button type="add" label="Tambah" wire:click="openCreateModal" />
                    @endcanAdd

                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>

                    @canExport('plan-call-team-elite.toko-pareto')
                    <x-ui.action-button type="export" wire:click="export" wire:loading.attr="disabled" />
                    @endcanExport
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto w-full relative" wire:key="table-wrapper-{{ md5($search . $filterRegion . $filterArea . $filterSupervisor . ($data?->currentPage() ?? 0)) }}">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm relative z-20">
                    <tr>
                        <th class="w-24 text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                        
                        <th wire:click="sortBy('m.region_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Region</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('m.region_name')" class="{{ $getSortClass('m.region_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('m.area_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Area</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('m.area_name')" class="{{ $getSortClass('m.area_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('m.distributor_name')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Distributor</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('m.distributor_name')" class="{{ $getSortClass('m.distributor_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('ms.description')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Supervisor</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('ms.description')" class="{{ $getSortClass('ms.description') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_code_prc')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Kode PRC</span>
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
                                <span>Toko</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.customer_name')" class="{{ $getSortClass('l.customer_name') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.customer_address')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Alamat</span>
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
                        
                        <th>Lat, Lng</th>
                        
                        <th wire:click="sortBy('l.pilar')" class="cursor-pointer hover:bg-base-200 text-center select-none transition-colors">
                            <div class="flex items-center justify-center gap-2">
                                <span>Pilar</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.pilar')" class="{{ $getSortClass('l.pilar') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.target')" class="cursor-pointer hover:bg-base-200 text-right select-none transition-colors">
                            <div class="flex items-center justify-end gap-2">
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.target')" class="{{ $getSortClass('l.target') }}" />
                                <span>Target</span>
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('l.keterangan')" class="cursor-pointer hover:bg-base-200 select-none transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <span>Keterangan</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('l.keterangan')" class="{{ $getSortClass('l.keterangan') }}" />
                            </div>
                        </th>
                        
                        <th wire:click="sortBy('on_jks')" class="cursor-pointer hover:bg-base-200 text-center select-none transition-colors">
                            <div class="flex items-center justify-center gap-2">
                                <span>on JKS</span>
                                <x-dynamic-component :component="'heroicon-s-' . $getSortIcon('on_jks')" class="{{ $getSortClass('on_jks') }}" />
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data as $item)
                <tr wire:key="toko-row-{{ $item->id }}">
                    <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                        <div class="flex items-center justify-center gap-1">
                            @canEdit('plan-call-team-elite.toko-pareto')
                            <x-ui.action-button type="edit" class="btn-square" title="Edit" wire:click="edit({{ $item->id }})" />
                            @endcanEdit
                            
                            @canDelete('plan-call-team-elite.toko-pareto')
                            @if ($item->on_jks === 'Y')
                            <x-ui.action-button type="delete" class="btn-square opacity-50 cursor-not-allowed" disabled title="Toko sudah terdaftar di JKS (tidak dapat dihapus)" />
                            @else
                            <x-ui.action-button type="delete" class="btn-square" title="Hapus" wire:click="confirmDelete({{ $item->id }})" />
                            @endif
                            @endcanDelete
                            
                            @canEdit('plan-call-team-elite.toko-pareto')
                            <x-ui.action-button type="default" icon="plus-circle" label="" class="btn-square btn-ghost text-success hover:bg-success/10 border-success/30" title="Add to JKS" wire:click="addToJks({{ $item->id }})" />
                            @endcanEdit
                        </div>
                    </th>
                    <td class="whitespace-nowrap">{{ $item->region_name }}</td>
                    <td class="whitespace-nowrap">{{ $item->area_name }}</td>
                    <td class="max-w-[150px] truncate" title="{{ $item->distributor_name }}">{{ $item->distributor_name }}</td>
                    <td class="whitespace-nowrap">{{ $item->supervisor_name }}</td>
                    <td class="whitespace-nowrap font-mono">{{ $item->customer_code_prc }}</td>
                    <td class="whitespace-nowrap">{{ $item->uniq_kd }}</td>
                    <td class="max-w-[180px] truncate font-bold" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                    <td class="max-w-[220px] truncate text-xs opacity-70" title="{{ $item->customer_address }}">{{ $item->customer_address }}</td>
                    <td>{{ $item->kecamatan }}</td>
                    <td>{{ $item->desa }}</td>
                    <td class="whitespace-nowrap text-xs opacity-70">
                        {{ $item->latitude ?? '-' }}, <br> {{ $item->longitude ?? '-' }}
                    </td>
                    <td class="whitespace-nowrap text-center">
                        <x-ui.badge variant="neutral">{{ $item->pilar }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap text-right font-mono font-bold">{{ number_format($item->target, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap text-xs opacity-70">{{ $item->keterangan }}</td>
                    <td class="whitespace-nowrap text-center">
                        @if($item->on_jks === 'Y')
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-slate-100 text-emerald-600 font-bold border border-slate-300">Y</span>
                        @else
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-slate-100 text-rose-600 font-bold border border-slate-300">T</span>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="16" class="text-center py-8 text-base-content/50">
                            <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto mb-3 opacity-30" />
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
            
        @if($data->hasPages())
            {{-- Pagination --}}
            <div class="p-3 md:p-4 border-t border-base-300 bg-base-200/30 shrink-0">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL FILTER -->
    <x-ui.modal wire:key="modal-filter-key" id="modal-filter" title="Filter Data" icon="funnel" :open="$isFilterModalOpen" wire:close="closeFilterModal">
        <div class="space-y-4">
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Region</span></label>
                {{-- ✅ FIX #5: Gunakan wire:model biasa (bukan .live) agar tidak trigger
                     re-render setiap kali pilihan berubah. Update dikirim saat klik Terapkan. --}}
                <select wire:model="filterRegion" class="select select-sm select-bordered w-full">
                    <option value="">-- Semua Region --</option>
                    @foreach($regions as $r) <option value="{{ $r->region_code }}">{{ $r->region_name }}</option> @endforeach
                </select>
            </div>
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Area</span></label>
                <select wire:model="filterArea" class="select select-sm select-bordered w-full" @if(!$filterRegion) disabled @endif>
                    <option value="">-- Semua Area --</option>
                    @foreach($areas as $a) <option value="{{ $a->area_code }}">{{ $a->area_name }}</option> @endforeach
                </select>
            </div>
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold">Supervisor</span></label>
                <select wire:model="filterSupervisor" class="select select-sm select-bordered w-full" @if(!$filterArea) disabled @endif>
                    <option value="">-- Semua Supervisor --</option>
                    @foreach($supervisors as $s) <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option> @endforeach
                </select>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="error" outline wire:click="resetFilter">Reset</x-ui.button>
            <x-ui.button variant="primary" wire:click="applyFilter">Terapkan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL IMPORT -->
    <x-ui.modal wire:key="modal-import-key" id="modal-import" title="Import Excel (Full Sync)" icon="arrow-down-on-square" :open="$isImportModalOpen" wire:close="$set('isImportModalOpen', false)">
        <form id="form-import" wire:submit.prevent="import">
            <x-ui.notif type="info" class="mb-4 text-xs">
                <b>Info Full Sync:</b><br>Jika "Kode PRC + Distributor" sudah ada, data akan di-Update. Jika belum ada, akan di-Insert.
            </x-ui.notif>

            <x-ui.button type="button" variant="success" outline block wire:click="downloadTemplate" class="mb-4">
                <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-2" /> Download Template Format
            </x-ui.button>

            <div class="form-control w-full">
                <input type="file" wire:model="importFile" class="file-input file-input-bordered file-input-sm w-full" accept=".xlsx,.xls,.csv" required>
                <span wire:loading wire:target="importFile" class="text-xs text-info mt-1 font-medium flex items-center gap-1">
                    <span class="loading loading-spinner loading-xs"></span> Mengunggah file ke server...
                </span>
                @error('importFile') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </form>
        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" outline wire:click="$set('isImportModalOpen', false)">Batal</x-ui.button>
            <x-ui.button form="form-import" type="submit" variant="primary" icon="cloud-arrow-up" wire:loading.attr="disabled" wire:target="import, importFile">
                <span wire:loading.remove wire:target="import">Upload & Sync</span>
                <span wire:loading wire:target="import">Proses...</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL TAMBAH CUSTOMER BARU -->
    <x-ui.modal wire:key="modal-create-key" id="modal-create" title="Tambah Customer Baru" icon="plus-circle" size="lg" :open="$isCreateModalOpen" wire:close="$set('isCreateModalOpen', false)">
        <form id="form-create" wire:submit.prevent="store">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                <x-input-text label="Distributor Code *" wire:model="distributor_code" placeholder="Contoh: SBY01" />
                <x-input-text label="Customer Code PRC *" wire:model="customer_code_prc" placeholder="Contoh: CUST-991" />
                
                <div class="md:col-span-2">
                    <x-input-text label="Nama Toko" wire:model="customer_name" />
                </div>
                <div class="md:col-span-2">
                    <x-input-text label="Uniq Kd" wire:model="uniq_kd" />
                </div>
                <div class="md:col-span-2 form-control mb-4">
                    <label class="label pb-1"><span class="label-text text-xs font-medium">Alamat</span></label>
                    <textarea wire:model="customer_address" class="textarea textarea-bordered focus:textarea-primary w-full" rows="2"></textarea>
                </div>
                
                <x-input-text label="Kecamatan" wire:model="kecamatan" />
                <x-input-text label="Desa" wire:model="desa" />
                <x-input-text label="Latitude" wire:model="latitude" />
                <x-input-text label="Longitude" wire:model="longitude" />
                <x-input-text label="Pilar" wire:model="pilar" />
                <x-input-text label="Target" wire:model="target" type="number" step="0.01" />
                <x-input-text label="Keterangan" wire:model="keterangan" />
            </div>
        </form>
        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" outline wire:click="$set('isCreateModalOpen', false)">Batal</x-ui.button>
            <x-ui.button form="form-create" type="submit" variant="primary" icon="check-circle">Simpan Customer</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL EDIT -->
    <x-ui.modal wire:key="modal-edit-key" id="modal-edit" title="Edit Toko Pareto" icon="pencil-square" size="lg" :open="$isEditModalOpen" wire:close="$set('isEditModalOpen', false)">
        <form id="form-edit" wire:submit.prevent="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                <div class="form-control mb-4">
                    <label class="label pb-1"><span class="label-text text-xs font-medium">Distributor Code *</span></label>
                    <input type="text" wire:model="distributor_code" class="input input-sm input-bordered w-full bg-base-200" readonly>
                </div>
                <div class="form-control mb-4">
                    <label class="label pb-1"><span class="label-text text-xs font-medium">Customer Code PRC *</span></label>
                    <input type="text" wire:model="customer_code_prc" class="input input-sm input-bordered w-full bg-base-200" readonly>
                </div>
                
                <div class="md:col-span-2">
                    <x-input-text label="Nama Toko" wire:model="customer_name" />
                </div>
                <div class="md:col-span-2">
                    <x-input-text label="Uniq Kd" wire:model="uniq_kd" />
                </div>
                <div class="md:col-span-2 form-control mb-4">
                    <label class="label pb-1"><span class="label-text text-xs font-medium">Alamat</span></label>
                    <textarea wire:model="customer_address" class="textarea textarea-bordered focus:textarea-primary w-full" rows="2"></textarea>
                </div>
                
                <x-input-text label="Kecamatan" wire:model="kecamatan" />
                <x-input-text label="Desa" wire:model="desa" />
                <x-input-text label="Latitude" wire:model="latitude" />
                <x-input-text label="Longitude" wire:model="longitude" />
                <x-input-text label="Pilar" wire:model="pilar" />
                <x-input-text label="Target" wire:model="target" type="number" step="0.01" />
                <x-input-text label="Keterangan" wire:model="keterangan" />
            </div>
        </form>
        <x-slot:footer>
            <x-ui.button type="button" variant="neutral" outline wire:click="$set('isEditModalOpen', false)">Batal</x-ui.button>
            <x-ui.button form="form-edit" type="submit" variant="primary" icon="check">Simpan Perubahan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL DELETE -->
    <x-ui.modal wire:key="modal-delete-key" id="modal-delete" title="Hapus Data" icon="exclamation-triangle" :open="$isDeleteModalOpen" wire:close="$set('isDeleteModalOpen', false)">
        <div class="text-center py-4">
            <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-error mx-auto mb-4" />
            <p class="text-base-content/70">Apakah Anda yakin ingin menghapus data toko ini? Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <x-slot:footer>
            <div class="w-full flex justify-center gap-3">
                <x-ui.button type="button" variant="neutral" outline wire:click="$set('isDeleteModalOpen', false)">Batal</x-ui.button>
                <x-ui.button type="button" variant="error" wire:click="delete">Ya, Hapus</x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.modal>

    <!-- MODAL ADD TO JKS -->
    <x-ui.modal wire:key="modal-add-jks-key" id="modal-add-jks" title="Add to JKS Team Elite" icon="plus-circle" :open="$isAddToJksModalOpen" wire:close="$set('isAddToJksModalOpen', false)" boxClass="overflow-visible">
        <form wire:submit.prevent="storeToJks">
            <div class="space-y-4">
                <x-input-text label="Tanggal *" wire:model="jksTanggal" type="date" required />
                
                <div class="form-control w-full">
                    <label class="label pb-1"><span class="label-text text-xs font-medium">Nama Team *</span></label>
                    <div wire:ignore wire:key="jks-team-select-wrapper">
                        <div x-data="{
                                searchTeam: '',
                                open: false,
                                kodeTeam: @entangle('jksKodeTeam'),
                                options: [
                                    @foreach($teams as $team)
                                    { id: String(@js($team->kode_team ?? '')), name: String(@js($team->nama_team ?? '')) },
                                    @endforeach
                                ],
                                get selectedName() {
                                    let selected = this.options.find(o => o.id === String(this.kodeTeam || ''));
                                    return selected ? selected.name : '';
                                },
                                get filteredOptions() {
                                    if (this.searchTeam === '') return this.options;
                                    let s = this.searchTeam.toLowerCase();
                                    return this.options.filter(o => o.name.toLowerCase().includes(s) || o.id.toLowerCase().includes(s));
                                },
                                selectOption(option) {
                                    this.kodeTeam = option.id;
                                    this.open = false;
                                    this.searchTeam = '';
                                }
                            }" 
                            class="relative w-full">
                            
                            <!-- Select Trigger -->
                            <div @click="open = !open" @click.away="open = false" 
                                 class="input input-sm input-bordered w-full flex items-center justify-between cursor-pointer bg-base-100"
                                 :class="open ? 'border-primary ring-1 ring-primary' : ''">
                                <span x-text="selectedName || '-- Pilih Team --'" :class="selectedName ? '' : 'text-base-content/50'"></span>
                                <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/50 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                            </div>

                            <!-- Dropdown Options -->
                            <div x-show="open" x-transition.opacity.duration.200ms
                                 class="absolute z-[60] w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-xl max-h-60 flex flex-col"
                                 style="display: none;">
                                <div class="p-2 border-b border-base-200">
                                    <input type="text" x-model="searchTeam" placeholder="Cari nama/kode team..." 
                                           class="input input-sm input-bordered w-full focus:input-primary" 
                                           @click.stop>
                                </div>
                                <ul class="overflow-y-auto flex-1 p-1">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                        <li @click="selectOption(option)" 
                                            class="px-3 py-2 text-sm cursor-pointer rounded transition-colors"
                                            :class="String(kodeTeam) === option.id ? 'bg-primary text-primary-content font-bold' : 'hover:bg-base-200 text-base-content'">
                                            <div class="flex justify-between items-center">
                                                <span x-text="option.name"></span>
                                                <span x-text="option.id" class="text-[10px] opacity-60 font-mono"></span>
                                            </div>
                                        </li>
                                    </template>
                                    <li x-show="filteredOptions.length === 0" class="px-3 py-4 text-sm text-base-content/50 text-center">
                                        Tidak ada team yang cocok
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @error('jksKodeTeam')
                        <label class="label pt-1"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <x-ui.button type="button" variant="neutral" outline wire:click="$set('isAddToJksModalOpen', false)">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">Simpan ke JKS</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

</div>