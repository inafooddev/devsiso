<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Master Customer RWO</x-slot>

    {{-- Notifikasi Toast --}}
    <div 
        x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            timer: null,
            notify(event) {
                this.type = event.detail.type ?? 'success';
                this.message = event.detail.message ?? '';
                this.show = true;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.show = false, this.type === 'error' ? 5000 : 3500);
            }
        }"
        @notify.window="notify($event)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[200] w-[calc(100%-2rem)] max-w-sm pointer-events-none"
        x-cloak
    >
        <div :class="{
                'bg-success/20 text-success border-success/30': type === 'success',
                'bg-error/20 text-error border-error/30': type === 'error',
             }"
             class="alert shadow-lg rounded-2xl border backdrop-blur-sm flex items-center gap-3 pointer-events-auto">
            <template x-if="type === 'success'">
                <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
            </template>
            <template x-if="type === 'error'">
                <x-heroicon-s-x-circle class="w-5 h-5 shrink-0" />
            </template>
            <span class="text-sm font-semibold" x-text="message"></span>
        </div>
    </div>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.summary') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('rwo.index') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Detail</a>
        </div>
    </div>

    {{-- KPI Cards Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3 sm:gap-4 shrink-0">
        {{-- Card 1: Total Toko --}}
        <div wire:click="setFilter('')" 
             class="relative overflow-hidden cursor-pointer group p-3.5 bg-base-100 rounded-2xl shadow-sm border transition-all duration-300 hover:-translate-y-1 {{ empty($filter_type) ? 'border-primary shadow-lg shadow-primary/10 ring-1 ring-primary' : 'border-base-300 hover:shadow-md' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-base-content/50 line-clamp-2 leading-tight">Total Toko</span>
                    <h3 class="text-xl font-black mt-1.5 text-base-content">{{ number_format($kpis['total_toko']) }}</h3>
                </div>
                <div class="p-2 rounded-xl transition-all duration-300 {{ empty($filter_type) ? 'bg-primary/20 text-primary' : 'bg-base-200 text-base-content/40 group-hover:bg-primary/10 group-hover:text-primary' }}">
                    <x-heroicon-s-building-storefront class="w-5 h-5 shrink-0" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px]">
                <span class="font-medium text-base-content/50 truncate">Semua Data</span>
                <span class="font-bold text-primary opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap ml-1">&rarr;</span>
            </div>
        </div>

        {{-- Card 2: Belum Ada NIK KTP --}}
        <div wire:click="setFilter('tanpa_ktp')" 
             class="relative overflow-hidden cursor-pointer group p-3.5 bg-base-100 rounded-2xl shadow-sm border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_ktp' ? 'border-warning shadow-lg shadow-warning/10 ring-1 ring-warning' : 'border-base-300 hover:shadow-md' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-base-content/50 line-clamp-2 leading-tight">Belum Ada NIK KTP</span>
                    <h3 class="text-xl font-black mt-1.5 text-base-content">{{ number_format($kpis['tanpa_ktp']) }}</h3>
                </div>
                <div class="p-2 rounded-xl transition-all duration-300 {{ $filter_type === 'tanpa_ktp' ? 'bg-warning/20 text-warning' : 'bg-base-200 text-base-content/40 group-hover:bg-warning/10 group-hover:text-warning' }}">
                    <x-heroicon-s-identification class="w-5 h-5 shrink-0" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px]">
                <span class="font-medium text-base-content/50 truncate">Tanpa NIK</span>
                <span class="font-bold text-warning opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap ml-1">&rarr;</span>
            </div>
        </div>

        {{-- Card 3: Belum Ada Foto KTP --}}
        <div wire:click="setFilter('tanpa_foto_ktp')" 
             class="relative overflow-hidden cursor-pointer group p-3.5 bg-base-100 rounded-2xl shadow-sm border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_foto_ktp' ? 'border-error shadow-lg shadow-error/10 ring-1 ring-error' : 'border-base-300 hover:shadow-md' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-base-content/50 line-clamp-2 leading-tight">Belum Ada Foto KTP</span>
                    <h3 class="text-xl font-black mt-1.5 text-base-content">{{ number_format($kpis['tanpa_foto_ktp']) }}</h3>
                </div>
                <div class="p-2 rounded-xl transition-all duration-300 {{ $filter_type === 'tanpa_foto_ktp' ? 'bg-error/20 text-error' : 'bg-base-200 text-base-content/40 group-hover:bg-error/10 group-hover:text-error' }}">
                    <x-heroicon-s-camera class="w-5 h-5 shrink-0" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px]">
                <span class="font-medium text-base-content/50 truncate">Tanpa Foto KTP</span>
                <span class="font-bold text-error opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap ml-1">&rarr;</span>
            </div>
        </div>

        {{-- Card 4: Rekening Belum di Validasi --}}
        <div wire:click="setFilter('tanpa_rekening')" 
             class="relative overflow-hidden cursor-pointer group p-3.5 bg-base-100 rounded-2xl shadow-sm border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_rekening' ? 'border-info shadow-lg shadow-info/10 ring-1 ring-info' : 'border-base-300 hover:shadow-md' }}">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-base-content/50 line-clamp-2 leading-tight">Rekening Belum Validasi</span>
                    <h3 class="text-xl font-black mt-1.5 text-base-content">{{ number_format($kpis['tanpa_rekening']) }}</h3>
                </div>
                <div class="p-2 rounded-xl transition-all duration-300 {{ $filter_type === 'tanpa_rekening' ? 'bg-info/20 text-info' : 'bg-base-200 text-base-content/40 group-hover:bg-info/10 group-hover:text-info' }}">
                    <x-heroicon-s-credit-card class="w-5 h-5 shrink-0" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <div class="w-1.5 h-1.5 rounded-full {{ $filter_type === 'tanpa_rekening' ? 'bg-info animate-pulse' : 'bg-info/40' }}"></div>
                    <span class="text-[10px] font-medium text-base-content/50 truncate">Belum divalidasi</span>
                </div>
                <span class="font-bold text-info opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap ml-1">&rarr;</span>
            </div>
        </div>

        {{-- Card 5: Belum Ada Foto Toko --}}
        <div wire:click="setFilter('tanpa_foto_toko')" 
             class="relative overflow-hidden cursor-pointer group p-3.5 bg-base-100 rounded-2xl shadow-sm border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_foto_toko' ? 'border-accent shadow-lg shadow-accent/10 ring-1 ring-accent' : 'border-base-300 hover:shadow-md' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-base-content/50 line-clamp-2 leading-tight">Belum Ada Foto Toko</span>
                    <h3 class="text-xl font-black mt-1.5 text-base-content">{{ number_format($kpis['tanpa_foto_toko']) }}</h3>
                </div>
                <div class="p-2 rounded-xl transition-all duration-300 {{ $filter_type === 'tanpa_foto_toko' ? 'bg-accent/20 text-accent' : 'bg-base-200 text-base-content/40 group-hover:bg-accent/10 group-hover:text-accent' }}">
                    <x-heroicon-s-photo class="w-5 h-5 shrink-0" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px]">
                <span class="font-medium text-base-content/50 truncate">Tanpa Foto Toko</span>
                <span class="font-bold text-accent opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap ml-1">&rarr;</span>
            </div>
        </div>

        {{-- Card 6: Belum Ada Tikor --}}
        <div wire:click="setFilter('tanpa_tikor')" 
             class="relative overflow-hidden cursor-pointer group p-3.5 bg-base-100 rounded-2xl shadow-sm border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tanpa_tikor' ? 'border-secondary shadow-lg shadow-secondary/10 ring-1 ring-secondary' : 'border-base-300 hover:shadow-md' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-base-content/50 line-clamp-2 leading-tight">Belum Ada Geotag</span>
                    <h3 class="text-xl font-black mt-1.5 text-base-content">{{ number_format($kpis['tanpa_tikor']) }}</h3>
                </div>
                <div class="p-2 rounded-xl transition-all duration-300 {{ $filter_type === 'tanpa_tikor' ? 'bg-secondary/20 text-secondary' : 'bg-base-200 text-base-content/40 group-hover:bg-secondary/10 group-hover:text-secondary' }}">
                    <x-heroicon-s-map-pin class="w-5 h-5 shrink-0" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px]">
                <span class="font-medium text-base-content/50 truncate">Tanpa Lat/Long</span>
                <span class="font-bold text-secondary opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap ml-1">&rarr;</span>
            </div>
        </div>

        {{-- Card 7: Toko Tidak Valid --}}
        <div wire:click="setFilter('tidak_valid')" 
             class="relative overflow-hidden cursor-pointer group p-3.5 bg-base-100 rounded-2xl shadow-sm border transition-all duration-300 hover:-translate-y-1 {{ $filter_type === 'tidak_valid' ? 'border-error shadow-lg shadow-error/10 ring-1 ring-error' : 'border-base-300 hover:shadow-md' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-base-content/50 line-clamp-2 leading-tight">Toko Tidak Valid</span>
                    <h3 class="text-xl font-black mt-1.5 text-base-content">{{ number_format($kpis['tidak_valid']) }}</h3>
                </div>
                <div class="p-2 rounded-xl transition-all duration-300 {{ $filter_type === 'tidak_valid' ? 'bg-error/20 text-error' : 'bg-base-200 text-base-content/40 group-hover:bg-error/10 group-hover:text-error' }}">
                    <x-heroicon-s-x-circle class="w-5 h-5 shrink-0" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px]">
                <span class="font-medium text-base-content/50 truncate">Tidak Valid</span>
                <span class="font-bold text-error opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap ml-1">&rarr;</span>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-200/30">
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                {{-- Search --}}
                <div class="relative group grow md:grow-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.500ms="search" type="text"
                           placeholder="Cari RWO..."
                           class="input input-sm input-bordered pl-10 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>
                
                {{-- Filter Dropdown --}}
                <select wire:model.live="filter_type"
                        class="select select-sm select-bordered grow sm:grow-0 w-full sm:w-auto rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Data</option>
                    <option value="tanpa_ktp">Tanpa NIK KTP</option>
                    <option value="tanpa_foto_ktp">Tanpa Foto KTP</option>
                    <option value="tanpa_rekening">Rekening Belum Validasi</option>
                    <option value="tanpa_foto_toko">Tanpa Foto Toko</option>
                    <option value="tanpa_tikor">Tanpa Tikor (Lat/Long)</option>
                    <option value="tidak_valid">Outlet Tidak Valid</option>
                    <option value="valid">Outlet Valid</option>
                    <option value="complete">Status Complete</option>
                    <option value="not_complete">Status Not Complete</option>
                </select>
            </div>
            
            <div class="flex flex-wrap items-center justify-start md:justify-end gap-2 md:gap-3 w-full md:w-auto">
                {{-- Desktop Actions (Hidden on mobile) --}}
                <div class="hidden md:flex items-center gap-2">
                    {{-- Chained Wilayah Filter Button --}}
                    <button wire:click="openFilterModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200 relative {{ (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name)) ? 'border-primary text-primary hover:bg-primary/5' : '' }}">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        <span>Filter</span>
                        @if (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name))
                            <span class="absolute -top-1.5 -right-1.5 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                            </span>
                        @endif
                    </button>

                    {{-- Export --}}
                    @canExport('rwo.index')
                    <button wire:click="openExportModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export
                    </button>
                    @endcanExport

                    {{-- Import --}}
                    @canImport('rwo.index')
                    <button wire:click="openImportModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import
                    </button>
                    @endcanImport

                    {{-- Sync Pareto --}}
                    @canAdd('rwo.index')
                    <button wire:click="syncTikorPareto"
                            wire:loading.attr="disabled" wire:target="syncTikorPareto"
                            class="btn btn-sm btn-outline btn-info rounded-xl normal-case gap-2 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" wire:loading.class="animate-spin" wire:target="syncTikorPareto" />
                        Sync Tikor
                    </button>
                    @endcanAdd
                </div>

                {{-- Mobile Actions Menu (Hidden on Desktop) --}}
                <div class="dropdown dropdown-bottom dropdown-end w-full sm:w-auto md:hidden">
                    <label tabindex="0" class="btn btn-sm btn-outline rounded-xl w-full normal-case gap-2 border-base-300 hover:bg-base-200">
                        <x-heroicon-s-ellipsis-horizontal class="w-4 h-4" />
                        Opsi Lainnya
                    </label>
                    <ul tabindex="0" class="dropdown-content z-50 menu p-2 shadow-lg bg-base-100 rounded-box w-full sm:w-52 mt-1 border border-base-200">
                        <li>
                            <button wire:click="openFilterModal" class="gap-3">
                                <x-heroicon-s-funnel class="w-4 h-4 text-base-content/70" />
                                Filter
                                @if (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name))
                                    <span class="badge badge-primary badge-xs ml-auto"></span>
                                @endif
                            </button>
                        </li>
                        @canExport('rwo.index')
                        <li>
                            <button wire:click="openExportModal" class="gap-3">
                                <x-heroicon-s-arrow-down-tray class="w-4 h-4 text-base-content/70" />
                                Export
                            </button>
                        </li>
                        @endcanExport
                        @canImport('rwo.index')
                        <li>
                            <button wire:click="openImportModal" class="gap-3">
                                <x-heroicon-s-arrow-up-tray class="w-4 h-4 text-base-content/70" />
                                Import
                            </button>
                        </li>
                        @endcanImport
                        @canAdd('rwo.index')
                        <li>
                            <button wire:click="syncTikorPareto" class="gap-3 text-info">
                                <x-heroicon-s-arrow-path class="w-4 h-4" wire:loading.class="animate-spin" wire:target="syncTikorPareto" />
                                Sync Tikor
                            </button>
                        </li>
                        @endcanAdd
                    </ul>
                </div>

                {{-- Tambah --}}
                @canAdd('rwo.index')
                <button wire:click="openCreateModal"
                        class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20 w-full sm:w-auto">
                    <x-heroicon-s-plus class="w-4 h-4" />
                    Tambah
                </button>
                @endcanAdd
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto w-full relative" style="isolation: auto;" wire:loading.class="opacity-60 pointer-events-none">
            <div wire:loading wire:target="search, filter_type, setFilter, updatingSearch, updatingFilterType, filter_region_code, filter_area_code, filter_branch_name" 
                 class="absolute inset-0 flex items-center justify-center bg-base-100/70 z-30 backdrop-blur-[1px]">
                <div class="flex flex-col items-center gap-2">
                    <span class="loading loading-dots loading-lg text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/50">Memuat data...</span>
                </div>
            </div>
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Region</th>
                        <th>Cabang</th>
                        <th class="text-center">Custno</th>
                        <th>Customer</th>
                        <th class="text-center">Foto KTP</th>
                        <th class="text-center">Foto Toko</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Validasi</th>
                        <th>Keterangan</th>
                        <th class="text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">

                @foreach ($outlets as $index => $row)
                    <tr class="group text-[11px] hover:relative hover:z-40" wire:key="rwo-{{ $row->id }}">
                        <td class="text-center">
                            <span class="font-semibold text-base-content/40">{{ $outlets->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <div class="max-w-[120px]">
                                <span class="font-bold text-base-content/85 group-hover:text-primary transition-colors block truncate" title="{{ $row->region_name }}">
                                    {{ $row->region_name }}
                                </span>
                                <div class="text-[10px] text-base-content/40 font-semibold uppercase tracking-wider mt-0.5 truncate" title="{{ $row->area_name }}">
                                    {{ $row->area_name }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="max-w-[160px]">
                                <span class="font-medium text-base-content/70 block truncate" title="{{ $row->branch_name }}">
                                    {{ $row->branch_name }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="flex flex-col items-center gap-0.5">
                                <span class="badge badge-sm badge-outline border-base-300 text-secondary font-mono font-bold rounded-lg px-2 text-[11px]">
                                    {{ $row->customer_code }}
                                </span>
                                @if($row->eskalink_code)
                                    <span class="text-[9px] text-base-content/50 font-mono mt-0.5">{{ $row->eskalink_code }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="max-w-[200px]">
                                <span class="font-bold text-base-content/80 block truncate uppercase" title="{{ $row->customer_name }}">{{ $row->customer_name }}</span>
                                <p class="text-[10px] text-base-content/40 truncate" title="{{ $row->alamat }}">{{ $row->alamat }}</p>
                            </div>
                        </td>

                          <td class="text-center">
                             @if($row->foto_ktp)
                                  <div class="flex justify-center">
                                      <div class="w-8 h-8 rounded-xl bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto KTP" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_ktp) }}', title: 'Foto KTP' })">
                                          <x-heroicon-s-photo class="w-5 h-5" />
                                      </div>
                                  </div>
                             @else
                                 <span class="text-[11px] text-base-content/30 italic">Tidak ada</span>
                             @endif
                          </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- GPS --}}
                                @if($row->foto_toko)
                                    <div class="w-7 h-7 rounded-lg bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto GPS" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_toko) }}', title: 'Foto Toko (GPS)' })">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-pointer" data-tip="Foto Toko by GPS (Belum ada)" wire:click="openDetailModal({{ $row->id }})">G</div>
                                @endif

                                {{-- Depan --}}
                                @if($row->foto_toko2)
                                    <div class="w-7 h-7 rounded-lg bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto Tampak Depan" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_toko2) }}', title: 'Foto Toko (Tampak Depan)' })">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-pointer" data-tip="Foto Tampak Depan (Belum ada)" wire:click="openDetailModal({{ $row->id }})">D</div>
                                @endif

                                {{-- Dalam --}}
                                @if($row->foto_toko3)
                                    <div class="w-7 h-7 rounded-lg bg-success/10 border border-success/30 flex items-center justify-center text-success tooltip cursor-pointer hover:bg-success/20 transition-colors" data-tip="Lihat Foto Tampak Dalam" @click="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $row->foto_toko3) }}', title: 'Foto Toko (Tampak Dalam)' })">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                    </div>
                                @else
                                    <div class="w-7 h-7 rounded-lg bg-base-200 border border-base-300 flex items-center justify-center text-[9px] text-base-content/30 italic font-mono tooltip cursor-pointer" data-tip="Foto Tampak Dalam (Belum ada)" wire:click="openDetailModal({{ $row->id }})">Di</div>
                                @endif
                            </div>
                         </td>
                         <td class="text-center">
                              @if($row->status === 'Complete')
                                   <span class="inline-flex items-center gap-1 text-[10px] font-bold text-success bg-success/15 rounded-lg py-1 px-2">
                                       <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                       <span>Complete</span>
                                   </span>
                              @else
                                   <span class="inline-flex items-center gap-1 text-[10px] font-bold text-warning bg-warning/15 rounded-lg py-1 px-2">
                                       <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5" />
                                       <span>Not Complete</span>
                                   </span>
                              @endif
                          </td>
                          <td class="text-center">
                              @if($row->is_valid)
                                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-success bg-success/15 rounded-lg py-1 px-2">
                                      <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                      <span>Valid</span>
                                  </span>
                              @else
                                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-error bg-error/15 rounded-lg py-1 px-2">
                                      <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                      <span>Tidak Valid</span>
                                  </span>
                              @endif
                          </td>
                         <td>
                             @if($row->keterangan)
                                 <div class="max-w-[120px] truncate text-[11px] text-base-content/60" title="{{ $row->keterangan }}">
                                     {{ $row->keterangan }}
                                 </div>
                             @else
                                 <span class="text-[11px] text-base-content/30 italic">-</span>
                             @endif
                         </td>
                         <td>
                             <div class="flex items-center justify-center gap-1">
                                <button wire:click="openDetailModal({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-secondary hover:bg-secondary/10 transition-all duration-200" title="Detail">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </button>
                                @if($row->latitude && $row->longitude)
                                <a href="https://www.google.com/maps?q={{ (float)$row->latitude }},{{ (float)$row->longitude }}" target="_blank"
                                   class="btn btn-ghost btn-xs btn-square rounded-lg text-accent hover:bg-accent/10 transition-all duration-200" title="Buka Google Maps">
                                    <x-heroicon-s-map-pin class="w-4 h-4" />
                                </a>
                                @endif
                                @canEdit('rwo.index')
                                <button wire:click="openEditModal({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                @endcanEdit
                                @canDelete('rwo.index')
                                <button wire:click="confirmDelete({{ $row->id }})" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                                @endcanDelete
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if(count($outlets) === 0)
                    <tr>
                        <td colspan="11" class="text-center py-8 text-base-content/40">Tidak ada data RWO ditemukan.</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="p-3 border-t border-base-300 bg-base-100 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="text-xs text-base-content/50 font-medium">
                @if($outlets->total() > 0)
                    Menampilkan <span class="font-bold text-base-content/70">{{ $outlets->firstItem() }}</span> –
                    <span class="font-bold text-base-content/70">{{ $outlets->lastItem() }}</span>
                    dari <span class="font-bold text-primary">{{ number_format($outlets->total()) }}</span> data
                @else
                    Tidak ada data ditemukan
                @endif
            </div>
            @if($outlets->hasPages())
                {{ $outlets->links() }}
            @endif
        </div>
    </div>

    {{-- ========== MODAL FORM (Create/Edit) ========== --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Reward Outlet (RWO)' : 'Tambah RWO Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ $isEditing ? 'Perbarui data outlet program RWO' : 'Daftarkan outlet program RWO baru' }}</p>
                    </div>
                </div>
                <button @click="$wire.closeFormModal()" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 overflow-y-auto max-h-[calc(100vh-15rem)] bg-base-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        
                        {{-- HIERARKI WILAYAH --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Hierarki & Kode</h4>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region <span class="text-error">*</span></label>
                            <select wire:model.live="region_code"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('region_code') select-error @enderror">
                                <option value="">Pilih Region</option>
                                @foreach($this->getRegions() as $reg)
                                    <option value="{{ $reg->region_code }}">{{ $reg->region_code }} - {{ $reg->region_name }}</option>
                                @endforeach
                            </select>
                            @error('region_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area <span class="text-error">*</span></label>
                            <select wire:model.live="area_code"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('area_code') select-error @enderror"
                                    {{ empty($region_code) ? 'disabled' : '' }}>
                                <option value="">Pilih Area</option>
                                @foreach($this->getAreas() as $ar)
                                    <option value="{{ $ar->area_code }}">{{ $ar->area_code }} - {{ $ar->area_name }}</option>
                                @endforeach
                            </select>
                            @error('area_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang (Branch) <span class="text-error">*</span></label>
                            <select wire:model.live="branch_name"
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('branch_name') select-error @enderror">
                                <option value="">Pilih Cabang</option>
                                @foreach($this->getBranches() as $br)
                                    <option value="{{ $br->branch_name }}">{{ $br->branch_name }}</option>
                                @endforeach
                            </select>
                            @error('branch_name') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Customer Code <span class="text-error">*</span></label>
                            <input wire:model="customer_code" type="text" placeholder="Contoh: CUST-01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('customer_code') input-error @enderror">
                            @error('customer_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Eskalink Code</label>
                            <input wire:model="eskalink_code" type="text" placeholder="Contoh: ESKA-01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('eskalink_code') input-error @enderror">
                            @error('eskalink_code') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- OUTLET DATA --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Informasi Toko / Outlet</h4>
                        </div>

                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Customer / Toko <span class="text-error">*</span></label>
                            <input wire:model="customer_name" type="text" placeholder="Nama Toko"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('customer_name') input-error @enderror">
                            @error('customer_name') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Pemilik Toko</label>
                            <input wire:model="nama_pemilik_toko" type="text" placeholder="Nama Pemilik Toko"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('nama_pemilik_toko') input-error @enderror">
                            @error('nama_pemilik_toko') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-3 space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Alamat Lengkap <span class="text-error">*</span></label>
                            <textarea wire:model="alamat" placeholder="Tulis alamat toko secara detail..."
                                      class="textarea textarea-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('alamat') textarea-error @enderror" rows="3"></textarea>
                            @error('alamat') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">No HP</label>
                            <input wire:model="no_hp" type="text" placeholder="Contoh: 08123456789"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('no_hp') input-error @enderror">
                            @error('no_hp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Latitude</label>
                            <input wire:model="latitude" type="number" step="any" min="-90" max="90" placeholder="Contoh: -6.12345"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('latitude') input-error @enderror">
                            @error('latitude') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Longitude</label>
                            <input wire:model="longitude" type="number" step="any" min="-180" max="180" placeholder="Contoh: 106.12345"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('longitude') input-error @enderror">
                            @error('longitude') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- KTP & IDENTITY --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Identitas & KTP</h4>
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Sesuai KTP / NPWP</label>
                            <input wire:model="nama_ktp" type="text" placeholder="Nama Sesuai KTP / NPWP"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('nama_ktp') input-error @enderror">
                            @error('nama_ktp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">NIK / NPWP</label>
                            <input wire:model="nik_ktp" type="text" minlength="15" maxlength="25" placeholder="Contoh: 1234567890123456 atau 12.345.678.9-012.000"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('nik_ktp') input-error @enderror">
                            @error('nik_ktp') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- BANK & REKENING --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Informasi Bank & Transfer</h4>
                        </div>

                        <div class="space-y-1.5" x-data="{
                            open: false,
                            search: '',
                            selectedBank: @entangle('nama_bank'),
                            banks: @js($this->getBanksList())
                        }">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Bank</label>
                            <div @click.away="open = false" class="relative">
                                <button type="button" @click="open = !open" 
                                        :class="!selectedBank ? 'border-error' : 'border-base-300'"
                                        class="input input-bordered w-full text-left flex justify-between items-center bg-base-200 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 pr-2">
                                    <span x-text="selectedBank || 'Pilih Bank / Cari...'" class="truncate" :class="!selectedBank ? 'text-base-content/50' : ''"></span>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <div x-show="selectedBank" 
                                             @click.stop="selectedBank = ''; open = false" 
                                             class="p-1 hover:bg-error/10 rounded-lg transition-colors cursor-pointer text-base-content/40 hover:text-error"
                                             title="Kosongkan pilihan">
                                            <x-heroicon-s-x-mark class="w-4 h-4" />
                                        </div>
                                        <div class="p-1">
                                            <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/40" />
                                        </div>
                                    </div>
                                </button>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-[60] mt-1 w-full bg-base-200 border border-base-300 rounded-2xl shadow-xl max-h-60 overflow-y-auto" 
                                     x-cloak>
                                    <div class="p-2 sticky top-0 bg-base-200 border-b border-base-300">
                                        <input type="text" x-model="search" placeholder="Cari nama bank..." 
                                               class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-1 focus:ring-primary" 
                                               @click.stop>
                                    </div>
                                    <ul class="py-1">
                                        <template x-for="bank in banks" :key="bank">
                                            <li x-show="bank.toLowerCase().includes(search.toLowerCase())"
                                                @click="selectedBank = bank; open = false; search = ''"
                                                class="px-4 py-2.5 hover:bg-primary hover:text-primary-content cursor-pointer text-sm transition-colors duration-150">
                                                <span x-text="bank"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            @error('nama_bank') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">No Rekening</label>
                            <input wire:model="no_rekening" type="text" placeholder="Nomor Rekening"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('no_rekening') input-error @enderror">
                            @error('no_rekening') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Pemilik Rekening</label>
                            <input wire:model="nama_pemilik_norek" type="text" placeholder="Nama Pemilik Rekening"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 placeholder-shown:border-error @error('nama_pemilik_norek') input-error @enderror">
                            @error('nama_pemilik_norek') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- VALIDASI & KETERANGAN TOKO --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Validasi & Keterangan</h4>
                        </div>

                        <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            {{-- Validasi Checkbox --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status Toko</label>
                                <div class="form-control bg-base-200 border border-base-300 rounded-2xl p-3 flex flex-row items-center justify-between gap-3 hover:bg-base-200/80 transition-all duration-200 cursor-pointer">
                                    <div class="flex flex-col select-none" @click="$refs.isValidCheckbox.click()">
                                        <span class="text-xs font-bold text-base-content/80">Toko Ada / Valid</span>
                                        <span class="text-[10px] text-base-content/40">Centang jika toko terverifikasi ada</span>
                                    </div>
                                    <input x-ref="isValidCheckbox" type="checkbox" wire:model="is_valid" class="checkbox checkbox-primary rounded-lg">
                                </div>
                                @error('is_valid') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Validasi Rekening Checkbox --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Validasi Rekening</label>
                                <div class="form-control bg-base-200 border border-base-300 rounded-2xl p-3 flex flex-row items-center justify-between gap-3 hover:bg-base-200/80 transition-all duration-200 cursor-pointer">
                                    <div class="flex flex-col select-none" @click="$refs.isValidRekeningCheckbox.click()">
                                        <span class="text-xs font-bold text-base-content/80">Rekening Valid</span>
                                        <span class="text-[10px] text-base-content/40">Centang jika rekening diverifikasi</span>
                                    </div>
                                    <input x-ref="isValidRekeningCheckbox" type="checkbox" wire:model="validasi_rekening" class="checkbox checkbox-primary rounded-lg">
                                </div>
                                @error('validasi_rekening') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Keterangan Text --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Keterangan</label>
                                <input wire:model="keterangan" type="text" placeholder="Masukkan keterangan tambahan tentang toko..."
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('keterangan') input-error @enderror">
                                @error('keterangan') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- FOTO TOKO & KTP --}}
                        <div class="md:col-span-3 border-b border-base-200 pb-3 mt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary">Media / Foto</h4>
                        </div>

                        <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            {{-- Foto KTP --}}
                            <x-ui.upload-image 
                                wireModel="foto_ktp" 
                                label="Upload Foto KTP" 
                                :previewUrl="$this->getFotoKtpPreview()" 
                                :existingUrl="$existing_foto_ktp ? asset('storage/' . $existing_foto_ktp) : null" 
                                minHeight="110px"
                            />

                            {{-- Foto Toko by GPS --}}
                            <x-ui.upload-image 
                                wireModel="foto_toko" 
                                label="Foto Toko by GPS" 
                                :previewUrl="$this->getFotoTokoPreview()" 
                                :existingUrl="$existing_foto_toko ? asset('storage/' . $existing_foto_toko) : null" 
                                minHeight="110px"
                            />

                            {{-- Foto Toko Tampak Depan --}}
                            <x-ui.upload-image 
                                wireModel="foto_toko2" 
                                label="Foto Tampak Depan" 
                                :previewUrl="$this->getFotoToko2Preview()" 
                                :existingUrl="$existing_foto_toko2 ? asset('storage/' . $existing_foto_toko2) : null" 
                                minHeight="110px"
                            />

                            {{-- Foto Toko Tampak Dalam --}}
                            <x-ui.upload-image 
                                wireModel="foto_toko3" 
                                label="Foto Tampak Dalam" 
                                :previewUrl="$this->getFotoToko3Preview()" 
                                :existingUrl="$existing_foto_toko3 ? asset('storage/' . $existing_foto_toko3) : null" 
                                minHeight="110px"
                            />
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Tambahkan RWO' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL DETAIL (View) ========== --}}
    <div x-data="{ open: @entangle('isDetailModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-3xl overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-secondary/10 text-secondary">
                        <x-heroicon-s-eye class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">Detail Reward Outlet (RWO)</h3>
                        <p class="text-xs text-base-content/50">Tinjau informasi lengkap tentang RWO ini</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            @if($selectedOutlet)
            <div class="p-6 overflow-y-auto max-h-[calc(100vh-15rem)] bg-base-100 space-y-6">
                {{-- Data Outlet Utama --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Informasi Toko</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Customer:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->customer_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Kode Customer:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->customer_code }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Kode Eskalink:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->eskalink_code ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Pemilik Toko:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_pemilik_toko }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">No HP:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->no_hp ?? '-' }}</span>
                            </div>
                            <div class="flex flex-col pt-0.5">
                                <span class="text-xs font-semibold text-base-content/60 mb-0.5">Alamat Outlet:</span>
                                <span class="text-xs font-medium text-base-content leading-relaxed">{{ $selectedOutlet->alamat }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Hierarki Wilayah & Lokasi</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Region:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->region_code }} - {{ $selectedOutlet->region_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Area:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->area_code }} - {{ $selectedOutlet->area_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Cabang (Branch):</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->branch_name }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Latitude:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->latitude ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">Longitude:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->longitude ?? '-' }}</span>
                            </div>
                            @if($selectedOutlet->latitude && $selectedOutlet->longitude)
                            <div class="pt-2 flex justify-end">
                                <a href="https://www.google.com/maps?q={{ (float)$selectedOutlet->latitude }},{{ (float)$selectedOutlet->longitude }}" target="_blank"
                                   class="btn btn-xs btn-outline btn-accent rounded-lg normal-case gap-1.5">
                                    <x-heroicon-s-map-pin class="w-3.5 h-3.5" /> Buka Google Maps
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- KTP & Rekening --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Identitas KTP</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama di KTP:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_ktp ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">NIK KTP:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->nik_ktp ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40">Informasi Bank</span>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nama Bank:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_bank ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Nomor Rekening:</span>
                                <span class="text-xs font-bold text-base-content font-mono">{{ $selectedOutlet->no_rekening ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-base-200 pb-1.5">
                                <span class="text-xs font-semibold text-base-content/60">Pemilik Rekening:</span>
                                <span class="text-xs font-bold text-base-content">{{ $selectedOutlet->nama_pemilik_norek ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs font-semibold text-base-content/60">Status Rekening:</span>
                                @if($selectedOutlet->validasi_rekening)
                                    <span class="text-xs font-bold text-success flex items-center gap-1"><x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Valid</span>
                                @else
                                    <span class="text-xs font-bold text-base-content/40 flex items-center gap-1"><x-heroicon-s-clock class="w-3.5 h-3.5" /> Belum Validasi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Validasi & Keterangan --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Status Validasi -->
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300 flex flex-col justify-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 mb-2">Status Validasi</span>
                        @if($selectedOutlet->is_valid)
                            <div class="flex items-start gap-2 text-success bg-success/10 p-2.5 rounded-xl border border-success/20">
                                <x-heroicon-s-check-circle class="w-5 h-5 shrink-0 mt-0.5" />
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold leading-tight">Outlet Valid</span>
                                    <span class="text-[11px] font-medium text-success/70">(Toko Ada)</span>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-2 text-error bg-error/10 p-2.5 rounded-xl border border-error/20">
                                <x-heroicon-s-x-circle class="w-5 h-5 shrink-0 mt-0.5" />
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold leading-tight">Outlet Tidak Valid</span>
                                    <span class="text-[11px] font-medium text-error/70">(Toko Tidak Ada)</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Kelengkapan Data -->
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300 flex flex-col justify-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 mb-2">Kelengkapan Data</span>
                        @if($selectedOutlet->status === 'Complete')
                            <div class="flex items-center gap-2 text-success bg-success/10 p-2.5 rounded-xl border border-success/20">
                                <x-heroicon-s-check-badge class="w-5 h-5 shrink-0" />
                                <span class="text-sm font-bold">Complete</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-warning bg-warning/10 p-2.5 rounded-xl border border-warning/20">
                                <x-heroicon-s-exclamation-triangle class="w-5 h-5 shrink-0" />
                                <span class="text-sm font-bold">Not Complete</span>
                            </div>
                        @endif
                    </div>

                    <!-- Keterangan -->
                    <div class="bg-base-200/50 p-4 rounded-2xl border border-base-300 flex flex-col justify-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 mb-2">Keterangan</span>
                        <div class="bg-base-100/50 p-2.5 rounded-xl border border-base-200/50 h-full">
                            <p class="text-[11px] font-medium text-base-content/80 leading-relaxed">
                                {{ $selectedOutlet->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Gambar-gambar --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- KTP --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto KTP</span>
                        @if ($selectedOutlet->foto_ktp)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_ktp) }}', title: 'Foto KTP' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_ktp) }}" alt="Foto KTP" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto KTP
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by GPS --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Toko by GPS</span>
                        @if ($selectedOutlet->foto_toko)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_toko) }}', title: 'Foto Toko GPS' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko) }}" alt="Foto Toko GPS" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto GPS
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by team Elite (Tampak Depan) --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Tampak Depan</span>
                        @if ($selectedOutlet->foto_toko2)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_toko2) }}', title: 'Foto Tampak Depan' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko2) }}" alt="Foto Tampak Depan" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto Depan
                            </div>
                        @endif
                    </div>

                    {{-- Foto Toko by team Elite (Tampak Dalam) --}}
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/40 ml-1">Foto Tampak Dalam</span>
                        @if ($selectedOutlet->foto_toko3)
                            <div @click.prevent="$dispatch('open-photo-modal', { url: '{{ asset('storage/' . $selectedOutlet->foto_toko3) }}', title: 'Foto Tampak Dalam' })" class="block group relative rounded-2xl overflow-hidden border border-base-300 bg-base-200 cursor-pointer">
                                <img src="{{ asset('storage/' . $selectedOutlet->foto_toko3) }}" alt="Foto Tampak Dalam" class="w-full h-32 object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-200 gap-1 text-[10px] font-bold">
                                    <x-heroicon-s-magnifying-glass-plus class="w-3 h-3" /> Perbesar
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center border border-dashed border-base-300 rounded-2xl h-32 text-xs text-base-content/30 italic bg-base-200/20 text-center px-2">
                                Tidak ada foto Dalam
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between px-6 py-5 border-t border-base-300 bg-base-200/50">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Tutup</button>
                @canEdit('rwo.index')
                @if($selectedOutlet)
                <button type="button" 
                        @click="open = false; $wire.openEditModal({{ $selectedOutlet->id }})"
                        class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                    Edit Data Ini
                </button>
                @endif
                @endcanEdit
            </div>
        </div>
    </div>

    {{-- ========== MODAL IMPORT ========== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Import Data RWO</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Unggah File Excel (.xlsx / .csv)</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">Pilih File</label>
                            <button type="button" wire:click="downloadTemplate" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                                <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" /> Download Template
                            </button>
                        </div>
                        <input wire:model="importFile" type="file"
                               class="file-input file-input-bordered file-input-primary w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <div class="text-[10px] text-base-content/40 leading-tight mt-1">Ukuran maksimal file: 10MB</div>
                        @error('importFile') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-300 space-y-2">
                        <h5 class="text-xs font-bold text-base-content/70">Format Urutan Kolom Excel:</h5>
                        <ol class="list-decimal list-inside text-[11px] text-base-content/60 space-y-1 font-medium">
                            <li>Region Code</li>
                            <li>Region Name</li>
                            <li>Area Code</li>
                            <li>Area Name</li>
                            <li>Cabang (Branch Name)</li>
                            <li>Eskalink Code</li>
                            <li>Customer Code (Wajib & Unik)</li>
                            <li>Customer Name (Wajib)</li>
                            <li>Alamat</li>
                            <li>No HP</li>
                            <li>Latitude</li>
                            <li>Longitude</li>
                            <li>Nama Pemilik Toko</li>
                            <li>Nama KTP</li>
                            <li>NIK KTP</li>
                            <li>[Foto KTP - Dilewati saat import]</li>
                            <li>Nama Bank</li>
                            <li>No Rekening</li>
                            <li>Nama Pemilik Norek</li>
                        </ol>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="import">Unggah & Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-check-circle wire:loading.remove wire:target="import" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL KONFIRMASI HAPUS ========== --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Data RWO?</h3>
                <p class="text-sm text-base-content/60 leading-relaxed px-4">Apakah Anda yakin ingin menghapus data Reward Outlet ini? File foto yang tersimpan juga akan <span class="text-error font-bold italic">dihapus permanen</span>.</p>
            </div>

            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case transition-all duration-200">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 transition-all duration-200 text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL FILTER WILAYAH (CHAINED) ========== --}}
    <div x-data="{ open: @entangle('isFilterModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Wilayah</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Saring data secara bertingkat</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Region Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                    <select wire:model.live="filter_region_code"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Region</option>
                        @foreach($this->getFilterRegions() as $reg)
                            <option value="{{ $reg->region_code }}">{{ $reg->region_name }} ({{ $reg->region_code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Area Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                    <select wire:model.live="filter_area_code"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Area</option>
                        @foreach($this->getFilterAreas() as $ar)
                            <option value="{{ $ar->area_code }}">{{ $ar->area_name }} ({{ $ar->area_code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Cabang Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang</label>
                    <select wire:model.live="filter_branch_name"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($this->getFilterBranches() as $br)
                            <option value="{{ $br->branch_name }}">{{ $br->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                <button type="button" wire:click="resetFilters" class="btn btn-ghost text-error hover:bg-error/10 rounded-xl normal-case font-bold">Reset Filter</button>
                <button type="button" @click="open = false" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 font-bold">Tutup Filter</button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL EXPORT OPTIONS ========== --}}
    <div x-data="{ open: @entangle('isExportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
        
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" @click="open = false"></div>

        <!-- Modal Panel -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-md bg-base-100 rounded-[2rem] shadow-2xl border border-base-200/50 overflow-hidden ring-1 ring-black/5">
            
            <div class="p-6 sm:p-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center text-success">
                            <x-heroicon-s-arrow-down-tray class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-base-content">Export Excel</h3>
                            <p class="text-sm text-base-content/60 mt-1">Pilih foto yang ingin disertakan</p>
                        </div>
                    </div>
                    <button @click="open = false" class="btn btn-circle btn-ghost btn-sm text-base-content/40 hover:text-base-content hover:bg-base-200 transition-colors">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <!-- Content -->
                <div class="space-y-3 mb-8">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="export_foto_ktp" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                        <span class="text-sm font-medium text-base-content">Sertakan Foto KTP</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="export_foto_toko" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                        <span class="text-sm font-medium text-base-content">Sertakan Foto Toko (GPS)</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="export_foto_toko2" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                        <span class="text-sm font-medium text-base-content">Sertakan Foto Toko (Tampak Depan)</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="export_foto_toko3" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                        <span class="text-sm font-medium text-base-content">Sertakan Foto Toko (Tampak Dalam)</span>
                    </label>
                    <p class="text-xs text-warning/80 italic mt-2">
                        <x-heroicon-s-information-circle class="w-3.5 h-3.5 inline mr-1" />
                        Semakin banyak foto yang dipilih, ukuran file export akan semakin besar.
                    </p>
                </div>

                <!-- Footer -->
                <div class="flex gap-3">
                    <button @click="open = false" class="btn btn-ghost rounded-xl flex-1 border-base-300 border hover:bg-base-200 normal-case font-semibold">
                        Batal
                    </button>
                    <button wire:click="export" wire:loading.attr="disabled" wire:target="export" class="btn btn-success rounded-xl flex-1 text-white shadow-lg shadow-success/20 normal-case font-semibold gap-2">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-5 h-5" /> Export Sekarang</span>
                        <span wire:loading wire:target="export" class="flex items-center gap-2"><span class="loading loading-spinner loading-sm"></span> Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== MODAL PHOTO VIEWER ========== --}}
    <div x-data="{ 
            open: false, 
            imageUrl: '', 
            title: '', 
            scale: 1, 
            rotation: 0,
            panX: 0,
            panY: 0,
            isDragging: false,
            startX: 0,
            startY: 0,
            reset() {
                this.scale = 1;
                this.rotation = 0;
                this.panX = 0;
                this.panY = 0;
            },
            handleWheel(e) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -0.15 : 0.15;
                this.scale = Math.min(Math.max(0.25, this.scale + delta), 4);
            },
            startDrag(e) {
                if (this.scale <= 1) return; // Allow dragging only when zoomed in
                this.isDragging = true;
                this.startX = e.clientX - this.panX;
                this.startY = e.clientY - this.panY;
            },
            doDrag(e) {
                if (!this.isDragging) return;
                this.panX = e.clientX - this.startX;
                this.panY = e.clientY - this.startY;
            },
            endDrag() {
                this.isDragging = false;
            }
         }" 
         @open-photo-modal.window="open = true; imageUrl = $event.detail.url; title = $event.detail.title; reset();"
         @mousemove.window="doDrag($event)"
         @mouseup.window="endDrag()"
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6">
        
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/90 backdrop-blur-sm cursor-zoom-out" @click="open = false"></div>

        <!-- Modal Panel -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative w-full max-w-4xl flex flex-col items-center justify-center pointer-events-none">
            
            <div class="w-full flex justify-between items-center mb-4 pointer-events-auto">
                <div class="flex items-center gap-1 bg-black/40 rounded-xl p-1 backdrop-blur-sm border border-white/10">
                    <button @click="scale = Math.max(0.25, scale - 0.25)" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Zoom Out">
                        <x-heroicon-s-minus class="w-4 h-4" />
                    </button>
                    <button @click="scale = Math.min(4, scale + 0.25)" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Zoom In">
                        <x-heroicon-s-plus class="w-4 h-4" />
                    </button>
                    <button @click="reset()" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Reset">
                        <x-heroicon-s-arrow-path class="w-4 h-4" />
                    </button>
                    <div class="w-px h-5 bg-white/20 mx-1"></div>
                    <button @click="rotation -= 90" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Putar Kiri">
                        <x-heroicon-s-arrow-uturn-left class="w-4 h-4" />
                    </button>
                    <button @click="rotation += 90" class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20" title="Putar Kanan">
                        <x-heroicon-s-arrow-uturn-right class="w-4 h-4" />
                    </button>
                </div>
                <button @click="open = false" class="btn btn-circle btn-ghost text-white/70 hover:text-white hover:bg-white/20">
                    <x-heroicon-s-x-mark class="w-6 h-6" />
                </button>
            </div>
            
            <div class="overflow-hidden flex items-center justify-center pointer-events-auto max-h-[75vh] w-full rounded-xl ring-1 ring-white/10 bg-base-200/20 relative"
                 @wheel="handleWheel($event)"
                 @mousedown="startDrag($event)">
                <img :src="imageUrl" :alt="title" 
                     :style="`transform: translate(${panX}px, ${panY}px) scale(${scale}) rotate(${rotation}deg); transition: ${isDragging ? 'none' : 'transform 0.2s ease-in-out'}; cursor: ${scale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'default'};`"
                     class="max-h-[75vh] w-auto object-contain select-none" draggable="false" />
            </div>
            <div class="mt-4 text-white font-semibold tracking-wider uppercase text-sm pointer-events-auto" x-text="title"></div>
        </div>
    </div>
</div>
