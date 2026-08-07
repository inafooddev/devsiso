<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">QC Eskalink</x-slot>

    {{-- Tabs Navigation --}}
    @include('livewire.others.qceskalink._tabs')

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success shrink-0">
            <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                <div class="text-sm">{{ session('message') }}</div>
            </div>
        </div>
    @endif

    {{-- 4 KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0">
        {{-- QTY --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-info/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total QTY Selisih</h3>
                <div class="w-8 h-8 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-archive-box class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-info">{{ number_format($data->sum('qty_selisih'), 0, ',', '.') }}</div>
        </div>
        
        {{-- Discount 4 --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Disc 4 Selisih</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">Rp {{ number_format($data->sum('disc4_selisih'), 0, ',', '.') }}</div>
        </div>

        {{-- Discount 8 --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-error/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Disc 8 Selisih</h3>
                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-error">Rp {{ number_format($data->sum('disc8_selisih'), 0, ',', '.') }}</div>
        </div>

        {{-- Neto --}}
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Neto Selisih</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-banknotes class="w-4 h-4" />
                </div>
            </div>
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">Rp {{ number_format($data->sum('neto_selisih'), 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <x-heroicon-s-clipboard-document-check class="w-5 h-5 text-primary" />
                    QC Eskalink
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Data Quality Control Eskalink</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative group grow sm:grow-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari distributor..."
                           class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>

                {{-- Region Filter --}}
                <select wire:model.live="regionFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 grow sm:grow-0">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>

                {{-- Month Filter --}}
                <input wire:model.live="monthFilter" type="month" class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 grow sm:grow-0">

                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    @canImport('qceskalink.index')
                    <button wire:click="openImportModal" class="btn btn-sm btn-info text-white rounded-xl normal-case gap-2 shadow-sm shadow-info/20">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        <span class="hidden sm:inline">Import</span>
                    </button>
                    @endcanImport
                    
                    @canExport('qceskalink.index')
                    <button wire:click="export" class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        <span class="hidden sm:inline">Export</span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs ml-1"></span>
                    </button>
                    @endcanExport
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-xs w-full border-collapse">
                    <thead class="bg-base-200">
                        <tr class="h-10">
                            <th rowspan="2" class="border border-white bg-base-200 text-center align-middle sticky top-0 left-0 z-50 min-w-[100px] max-w-[100px] truncate">REGION</th>
                            <th rowspan="2" class="border border-white bg-base-200 text-center align-middle sticky top-0 left-[100px] z-50 min-w-[130px] max-w-[130px] truncate">AREA</th>
                            <th rowspan="2" class="border border-white bg-base-200 text-center align-middle sticky top-0 left-[230px] z-50 min-w-[100px] max-w-[100px] whitespace-normal">DIST CODE</th>
                            <th rowspan="2" class="border border-white bg-base-200 text-center align-middle sticky top-0 left-[330px] z-50 min-w-[250px] max-w-[250px] whitespace-normal shadow-[4px_0_6px_-2px_rgba(0,0,0,0.1)]">DISTRIBUTOR NAME</th>
                            <th colspan="3" class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">ROW</th>
                            <th colspan="3" class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">QTY</th>
                            <th colspan="3" class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">GROSS AMOUNT</th>
                            <th colspan="3" class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">LINE DISCOUNT 4</th>
                            <th colspan="3" class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">LINE DISCOUNT 8</th>
                            <th colspan="3" class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">DPP</th>
                            <th colspan="3" class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">TAX</th>
                            <th colspan="3" class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">NETO</th>
                            <th colspan="2" class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-0 z-40">SURAT</th>
                            <th rowspan="2" class="border border-white bg-base-200 text-center align-middle whitespace-nowrap px-4 py-2 sticky top-0 z-40">FILE SURAT</th>
                            <th rowspan="2" class="border border-white bg-base-200 text-center align-middle whitespace-nowrap px-4 py-2 sticky top-0 z-40">ACTION</th>
                        </tr>
                        <tr>
                            {{-- ROW --}}
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>
                            
                            {{-- QTY --}}
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>

                            {{-- GROSS AMOUNT --}}
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>

                            {{-- LINE DISCOUNT 4 --}}
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>

                            {{-- LINE DISCOUNT 8 --}}
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>

                            {{-- DPP --}}
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>

                            {{-- TAX --}}
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>

                            {{-- NETO --}}
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">CORE (INPUT)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">ESKA (SLO)</th>
                            <th class="border border-white bg-base-200 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>

                            {{-- SURAT --}}
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">NOMINAL</th>
                            <th class="border border-white bg-base-300 text-center text-xs whitespace-nowrap px-4 py-2 sticky top-10 z-40">SELISIH</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $item)
                            <tr wire:key="row-{{ $index }}" class="hover:bg-base-200 transition-colors">
                                <td class="border border-base-300 bg-base-100 sticky left-0 z-20 min-w-[100px] max-w-[100px] truncate" title="{{ $item->region_name ?? '-' }}">{{ $item->region_name ?? '-' }}</td>
                                <td class="border border-base-300 bg-base-100 sticky left-[100px] z-20 min-w-[130px] max-w-[130px] truncate" title="{{ $item->area_name ?? '-' }}">{{ $item->area_name ?? '-' }}</td>
                                <td class="border border-base-300 bg-base-100 font-mono sticky left-[230px] z-20 min-w-[100px] max-w-[100px] whitespace-normal">{{ $item->distributor_code ?? '-' }}</td>
                                <td class="border border-base-300 bg-base-100 font-semibold sticky left-[330px] z-20 min-w-[250px] max-w-[250px] truncate shadow-[4px_0_6px_-2px_rgba(0,0,0,0.1)]" title="{{ $item->distributor_name ?? '-' }}">{{ $item->distributor_name ?? '-' }}</td>
                                
                                {{-- ROW --}}
                                <td class="border border-base-300 text-right {{ ($item->row_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->row_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->row_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->row_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->row_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->row_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- QTY --}}
                                <td class="border border-base-300 text-right {{ ($item->qty_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->qty_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->qty_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->qty_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->qty_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->qty_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- GROSS AMOUNT --}}
                                <td class="border border-base-300 text-right {{ ($item->gross_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->gross_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->gross_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->gross_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->gross_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->gross_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- LINE DISCOUNT 4 --}}
                                <td class="border border-base-300 text-right {{ ($item->disc4_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->disc4_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->disc4_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->disc4_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->disc4_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->disc4_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- LINE DISCOUNT 8 --}}
                                <td class="border border-base-300 text-right {{ ($item->disc8_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->disc8_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->disc8_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->disc8_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->disc8_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->disc8_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- DPP --}}
                                <td class="border border-base-300 text-right {{ ($item->dpp_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->dpp_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->dpp_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->dpp_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->dpp_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->dpp_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- TAX --}}
                                <td class="border border-base-300 text-right {{ ($item->tax_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->tax_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->tax_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->tax_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->tax_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->tax_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- NETO --}}
                                <td class="border border-base-300 text-right {{ ($item->neto_core ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->neto_core ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->neto_eska ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->neto_eska ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->neto_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->neto_selisih ?? 0, 0, ',', '.') }}</td>
                                
                                {{-- SURAT --}}
                                <td class="border border-base-300 text-right {{ ($item->surat_nominal ?? 0) == 0 ? 'bg-error/10 text-error font-medium' : '' }}">{{ number_format($item->surat_nominal ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-base-300 text-right {{ ($item->surat_selisih ?? 0) != 0 ? 'text-error font-bold' : '' }}">{{ number_format($item->surat_selisih ?? 0, 0, ',', '.') }}</td>

                                {{-- FILE SURAT --}}
                                <td class="border border-base-300 text-center">
                                    @if($item->file_surat)
                                        <a href="{{ Storage::url($item->file_surat) }}" target="_blank" class="btn btn-xs btn-outline btn-primary">Lihat</a>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- ACTION --}}
                                <td class="border border-base-300 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @canEdit('qceskalink.index')
                                        <button wire:click="openEditModal('{{ $item->distributor_code }}', '{{ addslashes($item->distributor_name) }}')" 
                                                class="btn btn-xs btn-square btn-outline btn-primary" 
                                                title="Edit/Isi Manual">
                                            <x-heroicon-s-pencil-square class="w-4 h-4" />
                                        </button>
                                        @endcanEdit
                                        
                                        @canDelete('qceskalink.index')
                                        <button wire:click="deleteData('{{ $item->distributor_code }}')" 
                                                onclick="return confirm('Yakin ingin menghapus semua data CORE untuk distributor ini?')"
                                                class="btn btn-xs btn-square btn-outline btn-error" 
                                                title="Hapus Data">
                                            <x-heroicon-s-trash class="w-4 h-4" />
                                        </button>
                                        @endcanDelete
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="30" class="text-center py-8 text-base-content/50">Tidak ada data yang tersedia untuk filter saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-base-300 font-bold sticky bottom-0 z-40">
                        <tr>
                            <td class="border border-base-300 bg-base-300 sticky left-0 z-50 min-w-[100px] max-w-[100px]"></td>
                            <td class="border border-base-300 bg-base-300 sticky left-[100px] z-50 min-w-[130px] max-w-[130px]"></td>
                            <td class="border border-base-300 bg-base-300 sticky left-[230px] z-50 min-w-[100px] max-w-[100px]"></td>
                            <td class="border border-base-300 bg-base-300 sticky left-[330px] z-50 min-w-[250px] max-w-[250px] text-right pr-4 shadow-[4px_0_6px_-2px_rgba(0,0,0,0.1)]">GRAND TOTAL</td>
                            
                            {{-- ROW --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('row_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('row_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('row_selisih'), 0, ',', '.') }}</td>
                            
                            {{-- QTY --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('qty_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('qty_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('qty_selisih'), 0, ',', '.') }}</td>

                            {{-- GROSS AMOUNT --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('gross_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('gross_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('gross_selisih'), 0, ',', '.') }}</td>

                            {{-- LINE DISCOUNT 4 --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('disc4_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('disc4_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('disc4_selisih'), 0, ',', '.') }}</td>

                            {{-- LINE DISCOUNT 8 --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('disc8_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('disc8_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('disc8_selisih'), 0, ',', '.') }}</td>

                            {{-- DPP --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('dpp_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('dpp_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('dpp_selisih'), 0, ',', '.') }}</td>

                            {{-- TAX --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('tax_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('tax_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('tax_selisih'), 0, ',', '.') }}</td>

                            {{-- NETO --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('neto_core'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('neto_eska'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('neto_selisih'), 0, ',', '.') }}</td>

                            {{-- SURAT --}}
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('surat_nominal'), 0, ',', '.') }}</td>
                            <td class="border border-base-300 text-right">{{ number_format($data->sum('surat_selisih'), 0, ',', '.') }}</td>

                            <td class="border border-base-300"></td>
                            <td class="border border-base-300"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    {{-- ========== MODAL IMPORT ========== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto">
         
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative text-left bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg ring-1 ring-base-content/5 text-base-content my-8">

                <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                            <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-none">Import Nominal QC</h3>
                            <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Upload Excel & File Surat</p>
                        </div>
                    </div>
                    <button wire:click="closeImportModal" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="processImport">
                    <div class="p-6 space-y-5">
                        {{-- Month Filter untuk Import --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih Bulan <span class="text-error">*</span></label>
                            <input wire:model="importMonth" type="month" class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('importMonth') input-error @enderror" required>
                            @error('importMonth') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Excel File --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between ml-1">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">File Excel Data QC <span class="text-error">*</span></label>
                                <button type="button" wire:click="downloadFormat" class="text-xs text-info hover:underline flex items-center gap-1 font-semibold">
                                    <x-heroicon-s-arrow-down-tray class="w-3 h-3"/> Download Format
                                </button>
                            </div>
                            <input wire:model="importExcel" type="file" accept=".xlsx, .xls, .csv" class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('importExcel') file-input-error @enderror" required>
                            <span class="text-[10px] text-base-content/40 ml-1">Format: xlsx, xls, csv (Max: 10MB)</span>
                            @error('importExcel') <span class="block text-error text-[10px] font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                        <button type="button" wire:click="closeImportModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-info text-white rounded-xl px-10 normal-case shadow-sm shadow-info/20 gap-2">
                            <span wire:loading.remove wire:target="processImport">Import Data</span>
                            <span wire:loading wire:target="processImport" class="loading loading-spinner loading-xs"></span>
                            <x-heroicon-s-arrow-up-tray wire:loading.remove wire:target="processImport" class="w-4 h-4" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========== MODAL EDIT / UPSERT ========== --}}
    <div x-data="{ open: @entangle('isEditModalOpen') }"
         x-show="open" x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto">
         
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative text-left bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-5xl ring-1 ring-base-content/5 text-base-content my-8">

                <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-none">Isi / Edit Data Core</h3>
                            <p class="text-[11px] text-base-content/50 mt-1 tracking-wider font-semibold">{{ $editDistName }} ({{ $editDistCode }})</p>
                        </div>
                    </div>
                    <button wire:click="closeEditModal" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="saveEdit" x-data="{ tick: 0 }" @input="tick++">
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- KOLOM 1: DATA CORE --}}
                        <div class="space-y-4">
                            <h4 class="font-bold text-primary border-b border-base-300 pb-2 mb-2">Data Core (Input Manual)</h4>
                            
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">QTY</label>
                                <input x-ref="inputQty" wire:model="editQty" type="number" step="any" class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300" required>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Discount 4</label>
                                <input x-ref="inputDisc4" wire:model="editDisc4" type="number" step="any" class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300" required>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Discount 8</label>
                                <input x-ref="inputDisc8" wire:model="editDisc8" type="number" step="any" class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300" required>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Neto</label>
                                <input x-ref="inputNeto" wire:model="editNeto" type="number" step="any" class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300" required>
                            </div>

                            <div class="space-y-1.5 pt-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nominal Surat</label>
                                <input wire:model="editNominalSurat" type="number" step="any" class="input input-sm input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300" required>
                            </div>

                            <div class="space-y-1.5 pt-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">File Surat (Opsional)</label>
                                @if($existingFileSurat)
                                    <div class="mb-2 flex items-center justify-between bg-base-200/50 p-2 rounded-xl border border-base-300">
                                        <a href="{{ Storage::url($existingFileSurat) }}" target="_blank" class="text-xs text-primary hover:underline flex items-center gap-1">
                                            <x-heroicon-s-document-check class="w-4 h-4"/> Lihat file saat ini
                                        </a>
                                        <button type="button" wire:click="deleteFileSurat" onclick="return confirm('Yakin ingin menghapus file surat ini?')" class="text-xs text-error hover:text-error/70 flex items-center gap-1 font-bold transition-colors">
                                            <x-heroicon-s-trash class="w-3 h-3"/> Hapus File
                                        </button>
                                    </div>
                                @endif
                                <input wire:model="editFileSurat" type="file" accept=".pdf, image/*" class="file-input file-input-sm file-input-bordered w-full bg-base-200 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('editFileSurat') file-input-error @enderror">
                                <span class="text-[10px] text-base-content/40 ml-1">Format: PDF, JPG, PNG (Max: 10MB). Biarkan kosong jika tidak ingin mengubah.</span>
                                @error('editFileSurat') <span class="block text-error text-[10px] font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- KOLOM 2: DATA ESKA --}}
                        <div class="space-y-4">
                            <h4 class="font-bold text-info border-b border-base-300 pb-2 mb-2">Data Eska (Sistem)</h4>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">QTY</label>
                                <div class="px-3 py-1.5 bg-base-200/50 border border-base-300 rounded-xl text-sm font-mono opacity-80 h-[32px] flex items-center justify-between">
                                    <span>Pcs</span>
                                    <span>{{ number_format($eskaQty, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Discount 4</label>
                                <div class="px-3 py-1.5 bg-base-200/50 border border-base-300 rounded-xl text-sm font-mono opacity-80 h-[32px] flex items-center justify-between">
                                    <span>Rp</span>
                                    <span>{{ number_format($eskaDisc4, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Discount 8</label>
                                <div class="px-3 py-1.5 bg-base-200/50 border border-base-300 rounded-xl text-sm font-mono opacity-80 h-[32px] flex items-center justify-between">
                                    <span>Rp</span>
                                    <span>{{ number_format($eskaDisc8, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Neto</label>
                                <div class="px-3 py-1.5 bg-base-200/50 border border-base-300 rounded-xl text-sm font-mono opacity-80 h-[32px] flex items-center justify-between">
                                    <span>Rp</span>
                                    <span>{{ number_format($eskaNeto, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-8 p-4 bg-info/10 rounded-2xl border border-info/20 text-info">
                                <div class="flex gap-2">
                                    <x-heroicon-s-information-circle class="w-5 h-5 shrink-0" />
                                    <p class="text-[11px] leading-relaxed">
                                        Data Eska dihitung otomatis dari sistem berdasarkan transaksi di bulan berjalan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM 3: SELISIH --}}
                        <div class="space-y-4">
                            <h4 class="font-bold text-warning border-b border-base-300 pb-2 mb-2">Selisih (Core - Eska)</h4>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Selisih QTY</label>
                                <div class="px-3 py-1.5 border rounded-xl text-sm font-mono font-bold h-[32px] flex items-center justify-between transition-colors"
                                     :class="((Number($refs.inputQty?.value) || 0) - {{ $eskaQty ?? 0 }}) != 0 ? 'bg-error/10 border-error/30 text-error' : 'bg-success/10 border-success/30 text-success'">
                                    <span>Pcs</span>
                                    <span x-text="(tick || 1) ? new Intl.NumberFormat('id-ID').format((Number($refs.inputQty?.value) || 0) - {{ $eskaQty ?? 0 }}) : 0"></span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Selisih Disc 4</label>
                                <div class="px-3 py-1.5 border rounded-xl text-sm font-mono font-bold h-[32px] flex items-center justify-between transition-colors"
                                     :class="((Number($refs.inputDisc4?.value) || 0) - {{ $eskaDisc4 ?? 0 }}) != 0 ? 'bg-error/10 border-error/30 text-error' : 'bg-success/10 border-success/30 text-success'">
                                    <span>Rp</span>
                                    <span x-text="(tick || 1) ? new Intl.NumberFormat('id-ID').format((Number($refs.inputDisc4?.value) || 0) - {{ $eskaDisc4 ?? 0 }}) : 0"></span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Selisih Disc 8</label>
                                <div class="px-3 py-1.5 border rounded-xl text-sm font-mono font-bold h-[32px] flex items-center justify-between transition-colors"
                                     :class="((Number($refs.inputDisc8?.value) || 0) - {{ $eskaDisc8 ?? 0 }}) != 0 ? 'bg-error/10 border-error/30 text-error' : 'bg-success/10 border-success/30 text-success'">
                                    <span>Rp</span>
                                    <span x-text="(tick || 1) ? new Intl.NumberFormat('id-ID').format((Number($refs.inputDisc8?.value) || 0) - {{ $eskaDisc8 ?? 0 }}) : 0"></span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Selisih Neto</label>
                                <div class="px-3 py-1.5 border rounded-xl text-sm font-mono font-bold h-[32px] flex items-center justify-between transition-colors"
                                     :class="((Number($refs.inputNeto?.value) || 0) - {{ $eskaNeto ?? 0 }}) != 0 ? 'bg-error/10 border-error/30 text-error' : 'bg-success/10 border-success/30 text-success'">
                                    <span>Rp</span>
                                    <span x-text="(tick || 1) ? new Intl.NumberFormat('id-ID').format((Number($refs.inputNeto?.value) || 0) - {{ $eskaNeto ?? 0 }}) : 0"></span>
                                </div>
                            </div>
                            
                            <div class="mt-8 p-4 bg-warning/10 rounded-2xl border border-warning/20 text-warning-content">
                                <div class="flex gap-2">
                                    <x-heroicon-s-calculator class="w-5 h-5 shrink-0" />
                                    <p class="text-[11px] leading-relaxed">
                                        Selisih dihitung seketika saat Anda mengetik. Pastikan seluruh selisih bernilai 0 (hijau) jika data sudah sesuai.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                        <button type="button" wire:click="closeEditModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary text-white rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                            <span wire:loading.remove wire:target="saveEdit">Simpan Data</span>
                            <span wire:loading wire:target="saveEdit" class="loading loading-spinner loading-xs"></span>
                            <x-heroicon-s-check wire:loading.remove wire:target="saveEdit" class="w-4 h-4" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
