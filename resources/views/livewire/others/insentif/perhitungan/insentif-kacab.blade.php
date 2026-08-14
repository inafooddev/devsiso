<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full relative" x-data="{ }">

    {{-- Toolbar --}}
    <div class="p-4 md:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-100 rounded-t-xl z-20 relative">
        <div class="flex flex-wrap items-center justify-start sm:justify-start gap-2 md:gap-3 w-full sm:w-auto">
            
            {{-- Month Filter --}}
            <div class="relative group grow sm:grow-0">
                <select wire:model.live="filterBulan" class="select select-sm select-bordered w-full sm:w-48 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 font-semibold transition-all duration-300 text-sm">
                    @foreach($listBulan as $bln)
                        <option value="{{ $bln }}">{{ \Carbon\Carbon::parse($bln . '-01')->translatedFormat('F Y') }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Region Filter --}}
            <div class="relative group grow sm:grow-0">
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full sm:w-48 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 font-semibold transition-all duration-300 text-sm">
                    <option value="">Semua Region</option>
                    @foreach($listRegions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Area Filter --}}
            <div class="relative group grow sm:grow-0">
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full sm:w-48 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 font-semibold transition-all duration-300 text-sm" {{ empty($filterRegion) ? 'disabled' : '' }}>
                    <option value="">Semua Area</option>
                    @foreach($listAreas as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Search --}}
            <div class="relative group grow sm:grow-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" 
                       placeholder="Cari cabang/distributor..." 
                       class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
            </div>

            {{-- Loading Indicator --}}
            <div wire:loading class="text-sm font-semibold text-primary ml-2 flex items-center gap-2 bg-primary/10 px-3 py-1 rounded-full">
                <span class="loading loading-spinner loading-xs"></span>
                Memuat data...
            </div>
        </div>
    </div>

    {{-- Main Table Area --}}
    <div class="flex-1 overflow-hidden bg-base-100 relative flex flex-col rounded-b-xl border border-base-300/50">
        
        <style>
            .table-border-fix {
                border-collapse: separate !important;
                border-spacing: 0 !important;
                border-top: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
                border-left: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
            }
            .table-border-fix th, .table-border-fix td {
                border-right: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
                border-bottom: 1px solid var(--fallback-b3,oklch(var(--b3)/1)) !important;
            }
        </style>

        <div class="flex-1 overflow-auto custom-scrollbar js-table-scroll">
            <table class="table table-xs w-full table-border-fix">
                <thead class="bg-base-200">
                    <!-- Header Baris 1: Super Header -->
                    <tr class="text-xs h-10">
                        <th rowspan="2" class="border border-base-300 text-center font-semibold sticky top-0 left-0 z-30 bg-base-200 text-base-content w-[40px] min-w-[40px] max-w-[40px]">No</th>
                        <th rowspan="2" class="border border-base-300 text-center font-semibold sticky top-0 z-20 bg-base-200 text-base-content min-w-[120px]">Area</th>
                        <th rowspan="2" class="border border-base-300 text-center font-semibold sticky top-0 z-20 bg-base-200 text-base-content min-w-[200px]">Distributor</th>
                        <th rowspan="2" class="border border-base-300 text-center font-semibold sticky top-0 z-20 bg-base-200 text-base-content min-w-[120px]">Cabang</th>
                        <th rowspan="2" class="border border-base-300 text-center font-semibold sticky top-0 z-20 bg-base-200 text-base-content min-w-[150px]">Nama Kacab</th>
                        
                        <th rowspan="2" class="border border-indigo-200 text-center font-semibold sticky top-0 z-20 bg-indigo-50/80 text-indigo-800 min-w-[120px]">Target</th>
                        <th rowspan="2" class="border border-indigo-200 text-center font-semibold sticky top-0 z-20 bg-indigo-50/80 text-indigo-800 min-w-[120px]">Insentif</th>

                        @php
                            $monthName = \Carbon\Carbon::parse($filterBulan . '-01')->translatedFormat("F'y");
                        @endphp
                        <th colspan="5" class="border border-emerald-200 text-center font-bold sticky top-0 z-20 bg-emerald-100/80 text-emerald-800 uppercase tracking-wider shadow-sm">
                            Pencapaian Bulan {{ mb_strtoupper($monthName) }}
                        </th>
                    </tr>

                    <!-- Header Baris 2: Sub-headers -->
                    <tr class="text-xs h-10">
                        <th class="border border-emerald-200 text-center font-semibold sticky top-10 z-10 bg-emerald-50 text-emerald-700 min-w-[120px]">Sell Out</th>
                        <th class="border border-emerald-200 text-center font-semibold sticky top-10 z-10 bg-emerald-50 text-emerald-700 min-w-[70px]">%</th>
                        <th class="border border-sky-200 text-center font-semibold sticky top-10 z-10 bg-sky-50 text-sky-700 min-w-[120px]">Nilai Insentif</th>
                        <th class="border border-rose-200 text-center font-semibold sticky top-10 z-10 bg-rose-50 text-rose-700 min-w-[120px]">PPH 5%</th>
                        <th class="border border-emerald-300 text-center font-bold sticky top-10 z-10 bg-emerald-100 text-emerald-800 min-w-[120px]">TOTAL TRF</th>
                    </tr>
                </thead>

                <tbody class="text-xs">
                    @forelse ($kacabData as $index => $row)
                        <tr class="hover:bg-base-200 transition-colors group">
                            <td class="text-center border border-base-300 sticky left-0 z-10 bg-base-100 group-hover:bg-base-200/50 w-[40px] min-w-[40px] max-w-[40px]">{{ $index + 1 }}</td>
                            <td class="text-center border border-base-300">{{ $row['area_name'] }}</td>
                            <td class="font-semibold border border-base-300 text-left pl-2">{{ $row['distributor_name'] }}</td>
                            <td class="text-center font-mono font-semibold border border-base-300">{{ $row['cabang'] }}</td>
                            <td class="text-center font-semibold border border-base-300">{{ $row['nama_kacab'] }}</td>
                            
                            <td class="text-right font-bold text-indigo-700 bg-indigo-50/30 border border-indigo-100 pr-2">
                                {{ number_format($row['target'], 0, ',', '.') }}
                            </td>
                            <td class="text-right font-bold text-indigo-700 bg-indigo-50/30 border border-indigo-100 pr-2">
                                {{ number_format($row['insentif'], 0, ',', '.') }}
                            </td>
                            
                            <td class="text-right font-bold text-emerald-700 bg-emerald-50/30 border border-emerald-100 pr-2">
                                {{ number_format($row['sell_out'], 0, ',', '.') }}
                            </td>
                            <td class="text-center font-bold {{ $row['percentage'] >= 100 ? 'text-emerald-600' : 'text-rose-500' }} border border-emerald-100">
                                {{ number_format($row['percentage'], 1, ',', '.') }}%
                            </td>
                            <td class="text-right font-bold text-sky-700 bg-sky-50/30 border border-sky-100 pr-2">
                                {{ number_format($row['nilai_insentif'], 0, ',', '.') }}
                            </td>
                            <td class="text-right font-medium text-rose-600 bg-rose-50/30 border border-rose-100 pr-2">
                                {{ number_format($row['pph'], 0, ',', '.') }}
                            </td>
                            <td class="text-right font-bold text-emerald-800 bg-emerald-50/60 border border-emerald-200 pr-2">
                                {{ number_format($row['trf'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-8 text-base-content/50 italic border border-base-300">
                                Belum ada data distributor / cabang untuk bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($kacabData->count() > 0)
                <tfoot class="bg-base-300 font-bold text-xs sticky bottom-0 z-20 shadow-[0_-4px_6px_-1px_rgb(0,0,0,0.05)]">
                    <tr>
                        <td colspan="5" class="text-right py-3 pr-4 uppercase tracking-wider sticky left-0 z-30 bg-base-300 border border-base-300">
                            Grand Total
                        </td>
                        <td class="text-right text-indigo-800 border border-indigo-200 bg-indigo-100/50 pr-2">
                            {{ number_format($totals['target'], 0, ',', '.') }}
                        </td>
                        <td class="text-right text-indigo-800 border border-indigo-200 bg-indigo-100/50 pr-2">
                            {{ number_format($totals['insentif'], 0, ',', '.') }}
                        </td>
                        <td class="text-right text-emerald-800 border border-emerald-200 bg-emerald-100/50 pr-2">
                            {{ number_format($totals['sell_out'], 0, ',', '.') }}
                        </td>
                        <td class="text-center border border-emerald-200 bg-emerald-100/50">
                            @php
                                $totalPercentage = $totals['target'] > 0 ? ($totals['sell_out'] / $totals['target']) * 100 : 0;
                            @endphp
                            {{ number_format($totalPercentage, 1, ',', '.') }}%
                        </td>
                        <td class="text-right text-sky-800 border border-sky-200 bg-sky-100/50 pr-2">
                            {{ number_format($totals['nilai_insentif'], 0, ',', '.') }}
                        </td>
                        <td class="text-right text-rose-700 border border-rose-200 bg-rose-100/50 pr-2">
                            {{ number_format($totals['pph'], 0, ',', '.') }}
                        </td>
                        <td class="text-right text-emerald-900 border border-emerald-300 bg-emerald-200/60 pr-2 text-sm">
                            {{ number_format($totals['trf'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
