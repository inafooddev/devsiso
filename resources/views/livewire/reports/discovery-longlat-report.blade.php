<div class="flex h-full min-h-0 flex-col gap-4 md:gap-5 p-4 md:p-5 lg:p-6 pb-24 overflow-hidden relative" 
     x-data="{ showHeader: true }" 
     @scroll.window="showHeader = window.scrollY < 50">

    {{-- KPI Cards Summary per Region (Diletakkan di LUAR tabel utama) --}}
    @if($isReady && count($summaryData) > 0)
    <div class="flex flex-nowrap overflow-x-auto gap-3 md:gap-4 shrink-0 pb-1 w-full [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        @foreach($summaryData as $row)
        <div class="flex-1 min-w-[150px] md:min-w-[180px] bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group shrink-0">
            {{-- Dekorasi KPI --}}
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">{{ $row->region_code ?? 'NO REGION' }}</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-map-pin class="w-4 h-4" />
                </div>
            </div>
            
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($row->total, 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full xl:w-auto">
                <h2 class="text-base md:text-lg font-bold">Laporan Penemuan Koordinat (Discovery LongLat)</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Mencari titik toko (0,0) dari riwayat kunjungan valid</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                
                {{-- Filter Region --}}
                <select wire:model.live="selectedRegion" class="select select-sm select-bordered bg-base-100 w-full sm:w-auto">
                    <option value="">-- Semua Region --</option>
                    @foreach($regions as $r)
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Area --}}
                <select wire:model="selectedArea" class="select select-sm select-bordered bg-base-100 w-full sm:w-auto" @if(!$selectedRegion) disabled @endif>
                    <option value="">-- Semua Area --</option>
                    @foreach($areas as $a)
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-base-content/70 hidden md:block">Mulai:</span>
                    <input type="date" wire:model="startDate" class="input input-sm input-bordered bg-base-100 w-32 md:w-auto">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-base-content/70 hidden md:block">Sampai:</span>
                    <input type="date" wire:model="endDate" class="input input-sm input-bordered bg-base-100 w-32 md:w-auto">
                </div>

                <button wire:click="processData" class="btn btn-sm btn-primary rounded-xl">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    Cari
                </button>
                
                <div class="hidden xl:block w-px h-6 bg-base-300 mx-1"></div>
                
                <button wire:click="downloadExcel" @if(!$isReady) disabled @endif class="btn btn-sm btn-success rounded-xl text-white">
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                    Download Excel
                </button>
            </div>
        </div>

        {{-- Loading State --}}
        <div wire:loading wire:target="processData, downloadExcel, selectedRegion" class="p-3 bg-info/10 text-info text-center font-bold text-xs uppercase tracking-wider border-b border-info/20">
            Sedang memproses data... Mohon tunggu.
        </div>
        
        {{-- Error Validation State --}}
        @error('startDate') <div class="p-2 bg-error/10 text-error text-center text-xs font-bold">{{ $message }}</div> @enderror
        @error('endDate') <div class="p-2 bg-error/10 text-error text-center text-xs font-bold">{{ $message }}</div> @enderror

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative transition-all duration-300" 
             wire:loading.class="opacity-50 pointer-events-none" 
             wire:target="processData, gotoPage, nextPage, previousPage, selectedRegion">
            @if($isReady)
                @if(count($data) > 0)
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th class="w-16 pl-4">No</th>
                            <th>Region / Area</th>
                            <th>Cabang</th>
                            <th>Cust No</th>
                            <th>Cust Name</th>
                            <th>Titik Latitude</th>
                            <th>Titik Longitude</th>
                            <th>Waktu Penemuan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($data as $index => $row)
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th class="pl-4">{{ $data->firstItem() + $index }}</th>
                            <td class="text-xs">
                                <span class="font-bold block">{{ $row->region_code }}</span>
                                <span class="text-base-content/60">{{ $row->area_code }}</span>
                            </td>
                            <td>{{ $row->kodecabang }}</td>
                            <td class="font-mono text-base-content/70">{{ $row->custno }}</td>
                            <td class="font-bold">{{ $row->custname }}</td>
                            <td class="font-mono text-success font-bold">{{ $row->V_LA }}</td>
                            <td class="font-mono text-success font-bold">{{ $row->V_LG }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->TANGGAL)->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="flex flex-col items-center justify-center h-full p-10 text-base-content/50">
                    <x-heroicon-o-face-frown class="w-12 h-12 mb-3 opacity-30" />
                    <p class="font-semibold">Nihil!</p>
                    <p class="text-sm">Tidak ditemukan koordinat kunjungan untuk pelanggan '0' dengan filter yang dipilih.</p>
                </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center h-full p-10 text-base-content/50">
                    <x-heroicon-o-map class="w-16 h-16 mb-4 opacity-20" />
                    <p class="font-semibold">Siap Menemukan Koordinat Hilang</p>
                    <p class="text-sm">Tentukan filter dan tanggal, lalu klik "Cari" untuk memuat data.</p>
                </div>
            @endif
        </div>
        
        {{-- Footer Card (Pagination) --}}
        @if($isReady && count($data) > 0)
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
            {{ $data->links() }}
        </div>
        @endif

    </div>
</div>
