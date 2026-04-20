<div>
    <x-slot name="title">Komparasi Sales (Eska vs SID)</x-slot>

    <div class="mx-auto px-6 py-8" x-data="{ showFilterModal: @entangle('showFilterModal') }">

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-8">

            {{-- Header & Toolbar --}}
            <div class="px-6 py-4 border-b bg-gray-50 flex items-center">
                <div class="flex items-center gap-4">
                    {{-- Filter --}}
                    <button @click="showFilterModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 rounded-md font-semibold text-xs uppercase
                   text-white hover:bg-blue-700 focus:ring ring-blue-300 transition shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter Data
                    </button>

                    {{-- Import --}}
                    <button wire:click="$set('showImportModal', true)"
                        class="inline-flex items-center px-4 py-2 bg-green-600 rounded-md font-semibold text-xs uppercase
                   text-white hover:bg-green-700 transition shadow-sm">
                        Import
                    </button>
                </div>
            </div>


            {{-- TABLE SECTION --}}
            <div class="overflow-x-auto">
                @if (!$isFiltered)
                    <div class="p-12 text-center text-gray-500">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900">Data Belum Ditampilkan</h3>
                        <p class="mt-2 text-sm text-gray-500">Silakan klik tombol <b>Filter Data</b> untuk memilih
                            Periode dan Region.</p>
                    </div>
                @else
                    {{-- Summary Box --}}
                    <div class="bg-white rounded-xl shadow px-6 py-4 mb-6 m-4 border">
                        <div class="flex items-center justify-between divide-x divide-gray-200">

                            <div class="flex-1 text-center">
                                <p class="text-sm text-gray-500">Total Depo</p>
                                <p class="text-2xl font-bold">
                                    {{ number_format($summary->total_branch ?? 0) }}
                                </p>
                            </div>

                            <div class="flex-1 text-center">
                                <p class="text-sm text-gray-500">Sudah</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ number_format($summary->net_siso_non_zero ?? 0) }}
                                </p>
                            </div>

                            <div class="flex-1 text-center">
                                <p class="text-sm text-gray-500">Belum</p>
                                <p class="text-2xl font-bold text-red-600">
                                    {{ number_format($summary->net_siso_zero ?? 0) }}
                                </p>
                            </div>

                        </div>
                    </div>


                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Region</th>
                                <th
                                    class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Entity</th>
                                <th
                                    class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Branch</th>
                                <th
                                    class="px-4 py-3 text-center font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Row</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Qty (Pcs)</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Gross</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    LD4(CB)</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    LD8(BB)</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    DPP</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Tax</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-blue-600 uppercase tracking-wider bg-blue-50 whitespace-nowrap">
                                    Net Eska</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-green-600 uppercase tracking-wider bg-green-50 whitespace-nowrap">
                                    Net SISO</th>
                                <th
                                    class="px-4 py-3 text-right font-medium text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                    Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($comparisons as $row)
                                @php
                                    // Cek kondisi apakah baris harus merah (misal: belum ada sales eska)
                                    $isRedRow = $row->net_eska == 0;
                                @endphp
                                <tr class="{{ $isRedRow ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50' }}">
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $row->region_name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $row->entity_name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $row->branch_code }} -
                                        {{ $row->branch_name }}
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ number_format($row->row_count, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format($row->qty_pcs, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format($row->gross, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($row->ld4, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($row->bb, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($row->dpp, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($row->tax, 0, ',', '.') }}</td>

                                    {{-- Kolom Net Eska --}}
                                    <td
                                        class="px-4 py-3 text-right font-bold text-blue-700 {{ $isRedRow ? '' : 'bg-blue-50' }}">
                                        {{ number_format($row->net_eska, 0, ',', '.') }}
                                    </td>

                                    {{-- Kolom Net SISO --}}
                                    <td
                                        class="px-4 py-3 text-right font-bold text-green-700 {{ $isRedRow ? '' : 'bg-green-50' }}">
                                        {{ number_format($row->net_siso, 0, ',', '.') }}
                                    </td>

                                    <td
                                        class="px-4 py-3 text-right font-bold {{ $row->selisih != 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ number_format($row->selisih, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-6 py-10 text-center text-gray-500">
                                        Tidak ada data ditemukan untuk periode dan region ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-6 py-4 bg-gray-50 border-t">
                        {{ $comparisons->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- MODAL FILTER --}}
        {{-- Fixed: Import Modal dipindah keluar dari sini --}}
        <div x-show="showFilterModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                {{-- Backdrop --}}
                <div x-show="showFilterModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showFilterModal = false">
                </div>


                {{-- Modal Panel --}}
                <div x-show="showFilterModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                    </path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Filter
                                    Komparasi Sales</h3>
                                <div class="mt-4 space-y-4">
                                    {{-- Filter Periode --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode
                                            Invoice</label>
                                        <input type="month" wire:model="selectedMonth"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                                        @error('selectedMonth')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Filter Implementasi --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status
                                        </label>
                                        <select wire:model="selectedImplementasi"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border">
                                            <option value="ALL">Semua</option>
                                            <option value="Y">Sudah</option>
                                            <option value="N">Belum</option>
                                        </select>
                                        @error('selectedImplementasi')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Filter Region (Multi-Select) --}}
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <label class="block text-sm font-medium text-gray-700">Region</label>
                                            <button type="button" wire:click="selectAllRegions"
                                                class="text-xs text-blue-600 hover:text-blue-800 hover:underline">Pilih
                                                Semua</button>
                                        </div>
                                        <select wire:model="selectedRegions" multiple
                                            size="{{ $regionsOption->count() }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-2 border h-32">
                                            @foreach ($regionsOption as $r)
                                                <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Tahan tombol CTRL untuk memilih banyak.
                                        </p>
                                        @error('selectedRegions')
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="filter" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading wire:target="filter" class="animate-spin mr-2">
                                <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                            Tampilkan Data
                        </button>
                        <button @click="showFilterModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- IMPORT MODAL (SEKARANG SUDAH DIPISAH) --}}
        <div x-data="{ show: @entangle('showImportModal') }" x-show="show" x-cloak class="fixed z-50 inset-0 overflow-y-auto"
            style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="show = false"></div>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Import Data Excel</h3>

                        <div class="space-y-4">
                            <div class="border-2 border-dashed border-gray-300 rounded-md p-6 flex flex-col items-center justify-center relative"
                                x-bind:class="$wire.importFile ? 'bg-indigo-50 border-indigo-300' : ''">

                                <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>

                                <div class="text-sm text-gray-600 text-center">
                                    <label for="file-upload"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                        <span>Upload file</span>
                                        <input id="file-upload" wire:model="importFile" type="file"
                                            class="sr-only">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">XLSX max 100MB</p>

                                @if ($importFile)
                                    <div class="mt-4 flex items-center text-sm text-green-600 font-semibold">
                                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        File Terpilih: {{ $importFile->getClientOriginalName() }}
                                    </div>
                                @endif

                                <div wire:loading wire:target="importFile"
                                    class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="animate-spin h-8 w-8 text-indigo-600 mb-2"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span class="text-indigo-600 text-sm font-medium">Mengupload...</span>
                                    </div>
                                </div>
                            </div>
                            @error('importFile')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="import" wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <span wire:loading wire:target="import" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memproses...
                            </span>
                            <span wire:loading.remove wire:target="import">Mulai Import</span>
                        </button>
                        <button wire:click="$set('showImportModal', false)"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
