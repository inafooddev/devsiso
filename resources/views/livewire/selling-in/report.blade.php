<div>
    <x-slot name="title">Import Selling In</x-slot>

    <!-- Full Page Loading Overlay -->
    <div wire:loading wire:target="search" class="fixed inset-0 z-50 bg-slate-900/40 flex items-center justify-center backdrop-blur-sm transition-opacity" style="z-index: 9999;">
        <div class="bg-white p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex flex-col items-center border border-slate-100">
            <svg class="animate-spin h-10 w-10 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <h3 class="text-lg font-bold text-slate-800">Menarik Data...</h3>
            <p class="text-sm text-slate-500 mt-1">Mengkalkulasi total, mohon tunggu sebentar.</p>
        </div>
    </div>

    <div class="mx-auto px-4 sm:px-6 py-8">
        <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 rounded-2xl overflow-hidden">
            
            <!-- Header Card & Actions -->
            <div class="px-6 py-5 border-b border-slate-100 bg-white flex flex-col xl:flex-row justify-between items-center gap-4">

                <!-- LEFT / TITLE -->
                <!-- RIGHT ACTION BUTTONS -->
                <div class="flex items-center gap-3 w-full xl:w-auto">
                    @if($hasSearched && !empty($startMonth) && !empty($endMonth))
                        <!-- Active Filters Badge -->
                        <div class="hidden lg:flex items-center gap-2 bg-blue-50 border border-blue-200 px-3 py-2 rounded-xl text-xs text-blue-800 font-medium shadow-sm">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $this->activeFilters['period'] ?? '' }}
                            <span class="border-l border-blue-300 h-4 mx-1"></span>
                            <span>{{ $this->activeFilters['regions'] ?? 0 }} Reg, {{ $this->activeFilters['areas'] ?? 0 }} Area, {{ $this->activeFilters['distributors'] ?? 0 }} Dist</span>
                        </div>
                    @endif

                    <!-- FILTER BUTTON -->
                    <button wire:click="$set('isFilterModalOpen', true)"
                        class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 rounded-xl font-medium text-xs text-slate-700 uppercase tracking-wider hover:bg-slate-50 hover:text-blue-600 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        {{ $hasSearched ? 'Ubah Filter' : 'Filter' }}
                    </button>
                    <a href="{{ route('selling-in.index') }}"
                        class="inline-flex items-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-xl font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v12m0 0l-3-3m3 3l3-3M4 20h16">
                            </path>
                        </svg>
                        Import
                    </a>
                </div>
            </div>

            <!-- Table Wrapper with Fixed Header & Footer -->
            <div class="relative bg-slate-50/50">
                @if (!$hasSearched)
                    <div class="flex items-center justify-center text-center text-slate-500" style="height: 400px;">
                        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 max-w-sm mx-auto">
                            <div class="h-16 w-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-800">Silakan Terapkan Filter</h3>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed">Klik tombol "Filter" di atas untuk memilih kriteria dan menampilkan data laporan.</p>
                        </div>
                    </div>
                @else
                    <!-- Container scroll horizontal dan vertikal -->
                    <div class="overflow-x-auto">
                        <div class="overflow-y-auto custom-scrollbar" style="max-height: 650px;">
                            <table class="min-w-full divide-y divide-slate-200">
                                <colgroup>
                                    <col style="width: 10%;"> <!-- Region -->
                                    <col style="width: 10%;"> <!-- Area -->
                                    <col style="width: 15%;"> <!-- Cabang -->
                                    <col style="width: 15%;"> <!-- Kode Distributor -->
                                    <col style="width: 30%;"> <!-- Nama Distributor -->
                                    <col style="width: 10%;"> <!-- Qty (KTN) -->
                                    <col style="width: 10%;"> <!-- Value Net -->
                                </colgroup>

                                <!-- Modern Sticky Header with Glassmorphism -->
                                <thead class="sticky top-0 bg-slate-50/90 backdrop-blur-sm z-10 shadow-sm">
                                    <tr>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Region</th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Area</th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Cabang</th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Kode Distributor</th>
                                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Nama Distributor</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Qty (KTN)</th>
                                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap border-b border-slate-200">Value Net</th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white divide-y divide-slate-100">
                                    @forelse($data as $row)
                                        <tr class="hover:bg-slate-50 transition-colors duration-150">
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">{{ $row->region }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">{{ $row->area }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">{{ $row->cabang }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500 font-mono">{{ $row->kd_distributor }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-slate-800">{{ $row->nama_distributor_fix }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700 text-right font-mono">{{ number_format($row->qty_ktn, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-blue-700 font-bold text-right font-mono">{{ number_format($row->value_net, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 bg-slate-50/30">
                                                <h3 class="text-sm font-medium text-slate-800">Tidak Ada Data</h3>
                                                <p class="mt-1 text-sm text-slate-500">Tidak ada transaksi penjualan untuk filter yang dipilih.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                @if (!empty($data))
                                    <!-- Modern Sticky Footer -->
                                    <tfoot class="sticky bottom-0 bg-slate-50/90 backdrop-blur-sm border-t border-slate-200 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
                                        <tr>
                                            <th colspan="5" class="px-4 py-3.5 text-right text-xs font-bold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                                GRAND TOTAL :
                                            </th>
                                            <th class="px-4 py-3.5 text-right text-xs font-bold text-slate-800 whitespace-nowrap font-mono">
                                                {{ number_format($totalQtyKtn ?? 0, 2, ',', '.') }}
                                            </th>
                                            <th class="px-4 py-3.5 text-right text-sm font-black text-blue-700 whitespace-nowrap font-mono">
                                                Rp {{ number_format($totalValueNet ?? 0, 2, ',', '.') }}
                                            </th>
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

    <!-- Modal Filter Laporan -->
    <div x-data="{ open: @entangle('isFilterModalOpen') }" x-show="open" x-cloak class="fixed z-50 inset-0 overflow-y-auto" style="z-index: 9999;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Modern Backdrop Blur -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div x-show="open" @click.away="open = false"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-100">
                
                <form wire:submit.prevent="search">
                    <div class="bg-white px-6 pt-6 pb-6 rounded-t-2xl">
                        <div class="flex justify-between items-center mb-5 border-b border-slate-100 pb-4">
                            <h3 class="text-xl font-semibold text-slate-800">Filter Laporan</h3>
                            <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <!-- Modal Error Handling -->
                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 p-4 mb-5 rounded-xl">
                                <div class="text-sm text-red-700 font-semibold mb-1"><i class="fas fa-exclamation-circle"></i> Perhatikan kesalahan berikut:</div>
                                <ul class="list-disc list-inside text-xs text-red-600">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- KIRI: Chain Dropdowns -->
                            <div class="space-y-5 md:border-r md:pr-6 border-slate-100">
                                <h4 class="font-bold text-sm text-slate-800 border-b border-slate-100 pb-2">1. Pilih Area Geografis</h4>
                                
                                <!-- Region Dropdown Custom -->
                                <div x-data="{ openRegion: false }" class="relative">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Region <span class="text-red-500">*</span></label>
                                    <button type="button" @click="openRegion = !openRegion"
                                        class="relative w-full bg-slate-50 border border-slate-200 rounded-xl shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all">
                                        <span class="block truncate text-slate-700">
                                            @if (count($selectedRegions) == count($regionOptions) && count($regionOptions) > 0)
                                                Semua Region
                                            @elseif(count($selectedRegions) > 0)
                                                <span class="font-semibold text-blue-600">{{ count($selectedRegions) }}</span> Region terpilih
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
                                    <div x-show="openRegion" @click.away="openRegion = false" x-cloak
                                        class="absolute z-20 mt-1 w-full bg-white shadow-xl rounded-xl border border-slate-100 overflow-hidden">
                                        <div class="px-3 py-2.5 flex justify-between border-b border-slate-100 bg-slate-50">
                                            <button type="button" wire:click="$set('selectedRegions', @js($regionOptions))" class="text-xs font-medium text-blue-600 hover:text-blue-800">Pilih Semua</button>
                                            <button type="button" wire:click="$set('selectedRegions', [])" class="text-xs font-medium text-slate-500 hover:text-slate-700">Hapus Pilihan</button>
                                        </div>
                                        <div class="max-h-40 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                                            @forelse ($regionOptions as $r)
                                                <label class="flex items-center p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors">
                                                    <input type="checkbox" wire:model.live="selectedRegions" value="{{ $r }}" class="rounded bg-slate-100 border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                    <span class="ml-2.5 text-sm text-slate-700">{{ $r }}</span>
                                                </label>
                                            @empty
                                                <span class="text-xs text-slate-400 italic block p-2">Data region kosong.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Area Dropdown Custom -->
                                <div x-data="{ openArea: false }" class="relative">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Area <span class="text-red-500">*</span></label>
                                    <button type="button" @click="openArea = !openArea"
                                        class="relative w-full border border-slate-200 rounded-xl shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all @if(empty($selectedRegions)) bg-slate-100 cursor-not-allowed text-slate-400 @else bg-slate-50 text-slate-700 @endif"
                                        @if(empty($selectedRegions)) disabled @endif>
                                        <span class="block truncate">
                                            @if (count($selectedAreas) == count($areaOptions) && count($areaOptions) > 0)
                                                Semua Area
                                            @elseif(count($selectedAreas) > 0)
                                                <span class="font-semibold text-blue-600">{{ count($selectedAreas) }}</span> Area terpilih
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
                                    <div x-show="openArea" @click.away="openArea = false" x-cloak
                                        class="absolute z-10 mt-1 w-full bg-white shadow-xl rounded-xl border border-slate-100 overflow-hidden">
                                        <div class="px-3 py-2.5 flex justify-between border-b border-slate-100 bg-slate-50">
                                            <button type="button" wire:click="$set('selectedAreas', @js($areaOptions))" class="text-xs font-medium text-blue-600 hover:text-blue-800">Pilih Semua</button>
                                            <button type="button" wire:click="$set('selectedAreas', [])" class="text-xs font-medium text-slate-500 hover:text-slate-700">Hapus Pilihan</button>
                                        </div>
                                        <div class="max-h-40 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                                            @forelse($areaOptions as $a)
                                                <label class="flex items-center p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors">
                                                    <input type="checkbox" wire:model.live="selectedAreas" value="{{ $a }}" class="rounded bg-slate-100 border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                    <span class="ml-2.5 text-sm text-slate-700">{{ $a }}</span>
                                                </label>
                                            @empty
                                                <p class="text-sm text-slate-400 p-2 text-center">Pilih Region terlebih dahulu.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KANAN: Distributor & Date Range -->
                            <div class="space-y-5">
                                <h4 class="font-bold text-sm text-slate-800 border-b border-slate-100 pb-2">2. Pilih Distributor & Waktu</h4>

                                <!-- Distributor Dropdown Custom -->
                                <div x-data="{ openDist: false }" class="relative">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Distributor <span class="text-red-500">*</span></label>
                                    <button type="button" @click="openDist = !openDist"
                                        class="relative w-full border border-slate-200 rounded-xl shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all @if(empty($selectedAreas)) bg-slate-100 cursor-not-allowed text-slate-400 @else bg-slate-50 text-slate-700 @endif"
                                        @if(empty($selectedAreas)) disabled @endif>
                                        <span class="block truncate">
                                            @if (count($selectedDistributors) == count($distributorOptions) && count($distributorOptions) > 0)
                                                Semua Distributor
                                            @elseif(count($selectedDistributors) > 0)
                                                <span class="font-semibold text-blue-600">{{ count($selectedDistributors) }}</span> Distributor terpilih
                                            @else
                                                Pilih Distributor
                                            @endif
                                        </span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>
                                    <div x-show="openDist" @click.away="openDist = false" x-cloak
                                        class="absolute z-10 mt-1 w-full bg-white shadow-xl rounded-xl border border-slate-100 overflow-hidden">
                                        <div class="px-3 py-2.5 flex justify-between border-b border-slate-100 bg-slate-50">
                                            <button type="button" wire:click="$set('selectedDistributors', @js(collect($distributorOptions)->pluck('kd_distributor')->toArray()))" class="text-xs font-medium text-blue-600 hover:text-blue-800">Pilih Semua</button>
                                            <button type="button" wire:click="$set('selectedDistributors', [])" class="text-xs font-medium text-slate-500 hover:text-slate-700">Hapus Pilihan</button>
                                        </div>
                                        <div class="max-h-60 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                                            @forelse($distributorOptions as $dist)
                                                <label class="flex items-start p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors">
                                                    <input type="checkbox" wire:model.defer="selectedDistributors" value="{{ $dist->kd_distributor }}" class="mt-1 rounded bg-slate-100 border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                                    <div class="ml-2.5 flex flex-col">
                                                        <span class="text-sm text-slate-700 font-semibold">{{ $dist->nama_distributor_fix }}</span>
                                                        <span class="text-[10px] text-slate-500">{{ $dist->kd_distributor }} | {{ $dist->cabang }}</span>
                                                    </div>
                                                </label>
                                            @empty
                                                <p class="text-sm text-slate-400 p-2 text-center">Pilih Area terlebih dahulu.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Tanggal (Bulan & Tahun) -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Bulan Awal <span class="text-red-500">*</span></label>
                                        <input type="month" wire:model.defer="startMonth" 
                                            class="block w-full pl-3 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Bulan Akhir <span class="text-red-500">*</span></label>
                                        <input type="month" wire:model.defer="endMonth" 
                                            class="block w-full pl-3 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl transition-all">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-slate-50 px-6 py-4 rounded-b-2xl border-t border-slate-100 flex flex-col sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl border border-transparent px-5 py-2.5 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 shadow-sm transition-all">
                            Tampilkan / Search
                        </button>
                        <button @click="$wire.set('hasSearched', false); $wire.set('data', []); $wire.set('selectedRegions', []); $wire.set('selectedAreas', []); $wire.set('selectedDistributors', []); $wire.set('startMonth', ''); $wire.set('endMonth', '');" type="button"
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

    <!-- Styling Tambahan Khusus Halaman Ini -->
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>