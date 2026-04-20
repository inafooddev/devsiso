<div>
    <x-slot name="title">Sales Invoices Distributor</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">
        <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 rounded-2xl overflow-hidden">
            
            <!-- Header Card & Actions -->
            <div class="px-6 py-5 border-b border-slate-100 bg-white flex flex-col xl:flex-row justify-between items-center gap-4">

                <!-- LEFT ACTION BUTTON -->
                <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">

                    <!-- FILTER -->
                    <button wire:click="$set('isFilterModalOpen', true)"
                        class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-medium text-xs text-slate-700 uppercase tracking-wider hover:bg-slate-50 hover:text-blue-600 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Filter
                    </button>

                    <!-- IMPORT -->
                    <a href="{{ route('sales-invoices.import') }}"
                        class="inline-flex items-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-xl font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v12m0 0l-3-3m3 3l3-3M4 20h16">
                            </path>
                        </svg>
                        Import
                    </a>

                    <!-- EXPORT -->
                    <button wire:click="$set('isExportModalOpen', true)"
                        class="inline-flex items-center px-4 py-2.5 bg-emerald-600 border border-transparent rounded-xl font-medium text-xs text-white uppercase tracking-wider hover:bg-emerald-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                            </path>
                        </svg>
                        Export
                    </button>

                    <!-- CONFIG -->
                    <a href="{{ route('sales-configs.index') }}"
                        class="inline-flex items-center px-4 py-2.5 bg-amber-500 border border-transparent rounded-xl font-medium text-xs text-white uppercase tracking-wider hover:bg-amber-600 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-amber-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.983 5.5a1.5 1.5 0 012.034 0l.651.608a1.5 1.5 0 001.554.31l.862-.287a1.5 1.5 0 011.82 1.82l-.287.862a1.5 1.5 0 00.31 1.554l.608.651a1.5 1.5 0 010 2.034l-.608.651a1.5 1.5 0 00-.31 1.554l.287.862a1.5 1.5 0 01-1.82 1.82l-.862-.287a1.5 1.5 0 00-1.554.31l-.651.608a1.5 1.5 0 01-2.034 0l-.651-.608a1.5 1.5 0 00-1.554-.31l-.862.287a1.5 1.5 0 01-1.82-1.82l.287-.862a1.5 1.5 0 00-.31-1.554l-.608-.651a1.5 1.5 0 010-2.034l.608-.651a1.5 1.5 0 00.31-1.554l-.287-.862a1.5 1.5 0 011.82-1.82l.862.287a1.5 1.5 0 001.554-.31l.651-.608z">
                            </path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Config
                    </a>

                </div>

                <!-- SEARCH -->
                <div class="w-full xl:w-auto">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Cari Region, Area, Distributor..."
                            class="w-full xl:w-72 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl pl-10 pr-4 py-2.5 shadow-sm focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all placeholder-slate-400">
                    </div>
                </div>

            </div>

            <!-- Table Wrapper with Fixed Header & Footer -->
            <div class="relative bg-slate-50/50">
                @if (!$hasAppliedFilters)
                    <div class="flex items-center justify-center text-center text-slate-500" style="height: 400px;">
                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 max-w-sm mx-auto">
                            <div class="h-16 w-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-800">Silakan Terapkan Filter</h3>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed">Klik tombol "Filter" di atas untuk memilih kriteria dan menampilkan data penjualan.</p>
                        </div>
                    </div>
                @else
                    <!-- Container scroll horizontal dan vertikal -->
                    <div class="overflow-x-auto">
                        <div class="overflow-y-auto custom-scrollbar" style="max-height: 650px;">
                            <table class="min-w-full divide-y divide-slate-200">
                                <colgroup>
                                    <col style="width: 6%;"> <!-- Region -->
                                    <col style="width: 6%;"> <!-- Area -->
                                    <col style="width: 10%;"> <!-- Distributor -->
                                    <col style="width: 5%;"> <!-- Mapping -->
                                    <col style="width: 8%;"> <!-- Last Update -->
                                    <col style="width: 6%;"> <!-- Jml Baris -->
                                    <col style="width: 6%;"> <!-- Karton -->
                                    <col style="width: 5%;"> <!-- Pack -->
                                    <col style="width: 5%;"> <!-- PCS -->
                                    <col style="width: 5%;"> <!-- qty -->
                                    <col style="width: 6%;"> <!-- Gross -->
                                    <col style="width: 6%;"> <!-- Cashback -->
                                    <col style="width: 6%;"> <!-- Bonus Barang -->
                                    <col style="width: 6%;"> <!-- DPP -->
                                    <col style="width: 6%;"> <!-- Tax -->
                                    <col style="width: 5%;"> <!-- Nett -->
                                </colgroup>

                                <!-- Modern Sticky Header with Glassmorphism -->
                                <thead class="sticky top-0 bg-slate-50/90 backdrop-blur-sm z-10 shadow-sm">
                                    <tr>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Region</th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Area</th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Distributor</th>
                                        <th class="px-4 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Config</th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Last Update</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Row</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">CTN</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Pak</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Pcs</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Qty</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Gross</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Disc4</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Disc8</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">DPP</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Tax</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Nett</th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white divide-y divide-slate-100">
                                    @forelse ($summaryData as $data)
                                        @php
                                            $isZeroNett = ($data->nett_raw ?? 0) == 0;
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition-colors duration-150 {{ $isZeroNett ? 'bg-red-50/40 hover:bg-red-50/80' : '' }}">
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">{{ $data->region_name }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">{{ $data->area_name }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-slate-800">
                                                <div>{{ $data->distributor_name }}</div>
                                                <div class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $data->distributor_code }}</div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-center">
                                                @if ($data->kodemaping)
                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-emerald-50 ring-1 ring-inset ring-emerald-600/20 text-emerald-600" title="Telah di-mapping">
                                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-red-50 ring-1 ring-inset ring-red-600/10 text-red-600" title="Belum di-mapping">
                                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">
                                                {{ $data->last_up ? \Carbon\Carbon::parse($data->last_up)->format('d M Y') : '-' }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->baris ?? 0, 0) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->ktn ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->pak ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->pcs ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->qty ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->gross_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->discount4_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->discount8_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->dpp_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right">{{ number_format($data->tax_raw ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs font-bold text-right {{ $isZeroNett ? 'text-red-600' : 'text-slate-800' }}">
                                                {{ number_format($data->nett_raw ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="16" class="px-6 py-12 text-center text-slate-500 bg-slate-50/30">
                                                <h3 class="text-sm font-medium text-slate-800">Tidak Ada Data</h3>
                                                <p class="mt-1 text-sm text-slate-500">Tidak ada data yang cocok dengan kriteria filter Anda.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if ($summaryData->isNotEmpty() && $grandTotals)
                                    <!-- Modern Sticky Footer -->
                                    <tfoot class="sticky bottom-0 bg-slate-50/90 backdrop-blur-sm border-t border-slate-200 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
                                        <tr>
                                            <td colspan="5" class="px-4 py-3.5 text-right text-xs font-bold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                                TOTAL KESELURUHAN
                                            </td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_baris'] ?? 0, 0) }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_ktn'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_pak'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_pcs'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_quantity'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_gross'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_cashback'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_bonusbarang'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_dpp'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap">{{ number_format($grandTotals['total_tax'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3.5 text-right text-sm font-black text-blue-700 whitespace-nowrap">{{ number_format($grandTotals['total_nett'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Filter -->
    <div x-data="{ open: @entangle('isFilterModalOpen') }" x-show="open" x-cloak class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Modern Backdrop Blur -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                
                <form wire:submit.prevent="applyFilters">
                    <div class="bg-white px-6 pt-6 pb-6 rounded-t-2xl">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-xl font-semibold text-slate-800">Filter Laporan</h3>
                            <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="space-y-5">
                            <!-- Status Distributor -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status Distributor</label>
                                <select wire:model.defer="statusFilter"
                                    class="block w-full pl-3 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl transition-all">
                                    <option value="">Semua Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Region Dropdown Custom -->
                            <div x-data="{ openRegion: false }" class="relative">
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Region</label>
                                <button type="button" @click="openRegion = !openRegion"
                                    class="relative w-full bg-slate-50 border border-slate-200 rounded-xl shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all">
                                    <span class="block truncate text-slate-700">
                                        @if (count($regionFilter) == count($regions))
                                            Semua Region
                                        @elseif(count($regionFilter) > 0)
                                            <span class="font-semibold text-blue-600">{{ count($regionFilter) }}</span> Region terpilih
                                        @else
                                            Pilih Region
                                        @endif
                                    </span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                <div x-show="openRegion" @click.away="openRegion = false"
                                    class="absolute z-20 mt-1 w-full bg-white shadow-xl rounded-xl border border-slate-100 overflow-hidden">
                                    <div class="px-3 py-2.5 flex justify-between border-b border-slate-100 bg-slate-50">
                                        <button type="button" wire:click="selectAllRegions" class="text-xs font-medium text-blue-600 hover:text-blue-800">Pilih Semua</button>
                                        <button type="button" wire:click="clearRegions" class="text-xs font-medium text-slate-500 hover:text-slate-700">Hapus Pilihan</button>
                                    </div>
                                    <div class="max-h-40 overflow-y-auto p-2 space-y-1">
                                        @foreach ($regions as $region)
                                            <label class="flex items-center p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors">
                                                <input type="checkbox" wire:model.live="regionFilter" value="{{ $region->region_code }}" class="rounded bg-slate-100 border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                <span class="ml-2.5 text-sm text-slate-700">{{ $region->region_name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Area Dropdown Custom -->
                            <div x-data="{ openArea: false }" class="relative">
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Area</label>
                                <button type="button" @click="openArea = !openArea"
                                    class="relative w-full border border-slate-200 rounded-xl shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all @if (empty($regionFilter)) bg-slate-100 cursor-not-allowed text-slate-400 @else bg-slate-50 text-slate-700 @endif"
                                    @if (empty($regionFilter)) disabled @endif>
                                    <span class="block truncate">
                                        @if (count($areaFilter) == count($areas) && count($areas) > 0)
                                            Semua Area
                                        @elseif(count($areaFilter) > 0)
                                            <span class="font-semibold text-blue-600">{{ count($areaFilter) }}</span> Area terpilih
                                        @else
                                            Pilih Area
                                        @endif
                                    </span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>
                                <div x-show="openArea" @click.away="openArea = false"
                                    class="absolute z-10 mt-1 w-full bg-white shadow-xl rounded-xl border border-slate-100 overflow-hidden">
                                    <div class="px-3 py-2.5 flex justify-between border-b border-slate-100 bg-slate-50">
                                        <button type="button" wire:click="selectAllAreas" class="text-xs font-medium text-blue-600 hover:text-blue-800">Pilih Semua</button>
                                        <button type="button" wire:click="clearAreas" class="text-xs font-medium text-slate-500 hover:text-slate-700">Hapus Pilihan</button>
                                    </div>
                                    <div class="max-h-40 overflow-y-auto p-2 space-y-1">
                                        @forelse($areas as $area)
                                            <label class="flex items-center p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors">
                                                <input type="checkbox" wire:model.live="areaFilter" value="{{ $area->area_code }}" class="rounded bg-slate-100 border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                <span class="ml-2.5 text-sm text-slate-700">{{ $area->area_name }}</span>
                                            </label>
                                        @empty
                                            <p class="text-sm text-slate-400 p-2 text-center">Tidak ada area untuk region yang dipilih</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tanggal (Bulan & Tahun) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="monthFilter" class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                                    <select wire:model.defer="monthFilter" id="monthFilter"
                                        class="block w-full pl-3 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl transition-all">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="yearFilter" class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                                    <select wire:model.defer="yearFilter" id="yearFilter"
                                        class="block w-full pl-3 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl transition-all">
                                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 rounded-b-2xl border-t border-slate-100 flex flex-col sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-transparent px-5 py-2.5 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 shadow-sm transition-all">
                            Terapkan
                        </button>
                        <button wire:click="resetFilters" type="button"
                            class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-slate-300 px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-slate-200 shadow-sm transition-all">
                            Reset
                        </button>
                        <button @click="open = false" type="button"
                            class="w-full sm:w-auto mt-2 sm:mt-0 sm:mr-auto inline-flex justify-center items-center rounded-xl px-5 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Filter untuk Ekspor -->
    <div x-data="{ open: @entangle('isExportModalOpen') }" x-show="open" x-cloak class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Modern Backdrop Blur -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
                 
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div x-show="open"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                 
                <form wire:submit.prevent="export">
                    <div class="bg-white px-6 pt-6 pb-6 rounded-t-2xl">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-xl font-semibold text-slate-800">Filter Data Ekspor</h3>
                            <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <div class="space-y-5">
                            <!-- Region -->
                            <div>
                                <label for="exportRegionFilter" class="block text-sm font-medium text-slate-700 mb-1.5">Region</label>
                                <select wire:model.live="exportRegionFilter" id="exportRegionFilter"
                                    class="block w-full pl-3 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 rounded-xl transition-all">
                                    <option value="">Pilih Region</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Area -->
                            <div>
                                <label for="exportAreaFilter" class="block text-sm font-medium text-slate-700 mb-1.5">Area</label>
                                <select wire:model.live="exportAreaFilter" id="exportAreaFilter"
                                    class="block w-full pl-3 pr-10 py-2.5 text-sm border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 rounded-xl transition-all @if (!$exportRegionFilter) bg-slate-100 cursor-not-allowed text-slate-400 @else bg-slate-50 @endif"
                                    @if (!$exportRegionFilter) disabled @endif>
                                    <option value="">Pilih Area</option>
                                    @foreach ($exportAreas as $area)
                                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Distributor -->
                            <div>
                                <label for="exportDistributorFilter" class="block text-sm font-medium text-slate-700 mb-1.5">Distributor</label>
                                <select wire:model.live="exportDistributorFilter" id="exportDistributorFilter"
                                    class="block w-full pl-3 pr-10 py-2.5 text-sm border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 rounded-xl transition-all @if (!$exportAreaFilter) bg-slate-100 cursor-not-allowed text-slate-400 @else bg-slate-50 @endif"
                                    @if (!$exportAreaFilter) disabled @endif>
                                    <option value="">Pilih Distributor</option>
                                    @foreach ($exportDistributors as $distributor)
                                        <option value="{{ $distributor->distributor_code }}">{{ $distributor->distributor_name }} ({{ $distributor->distributor_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Bulan & Tahun -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="exportMonthFilter" class="block text-sm font-medium text-slate-700 mb-1.5">Bulan</label>
                                    <select wire:model.defer="exportMonthFilter" id="exportMonthFilter"
                                        class="block w-full pl-3 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 rounded-xl transition-all">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label for="exportYearFilter" class="block text-sm font-medium text-slate-700 mb-1.5">Tahun</label>
                                    <select wire:model.defer="exportYearFilter" id="exportYearFilter"
                                        class="block w-full pl-3 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 rounded-xl transition-all">
                                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 rounded-b-2xl border-t border-slate-100 flex flex-col sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-transparent px-5 py-2.5 bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-500 shadow-sm transition-all"
                            wire:loading.attr="disabled">
                            <svg wire:loading wire:target="export" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Ekspor Sekarang
                        </button>
                        <button @click="open = false" type="button"
                            class="w-full sm:w-auto mt-2 sm:mt-0 inline-flex justify-center items-center rounded-xl border border-slate-300 px-5 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-slate-200 shadow-sm transition-all">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>