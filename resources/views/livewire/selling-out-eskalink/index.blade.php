<div>
    <x-slot name="title">Selling Out Summary</x-slot>

    <div class="mx-auto px-6 py-8">

        {{-- Notification --}}
        <div x-data="{ show: false, message: '', type: '' }" x-init="@if (session()->has('success')) show = true; message = '{{ session('success') }}'; type = 'success'; setTimeout(() => show = false, 3000); @endif
        @if (session()->has('error')) show = true; message = '{{ session('error') }}'; type = 'error'; setTimeout(() => show = false, 5000); @endif" x-show="show" x-transition.duration.300ms
            class="fixed top-4 right-4 z-50 p-4 rounded shadow-lg text-white"
            :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'" style="display: none;">
            <span x-text="message"></span>
        </div>

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b bg-gray-50 flex flex-col sm:flex-row justify-between items-center">
                <div class="flex items-center space-x-2">

                    <button wire:click="$set('showFilterModal', true)"
                        class="px-4 py-2 bg-white border rounded hover:bg-gray-50 text-sm font-medium text-gray-700">
                        Filter
                    </button>

                    <button wire:click="$set('showImportModal', true)"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm font-medium">
                        Import
                    </button>

                    @if ($isFiltered)
                        <button wire:click="export" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center text-sm font-medium">
                            <svg wire:loading wire:target="export" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Export Summary
                        </button>
                    @endif
                </div>

                @if ($isFiltered)
                    <div>
                        <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari Branch..."
                            class="border rounded px-3 py-2 text-sm w-64 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                @endif
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                @if (!$isFiltered)
                    <div class="p-12 text-center text-gray-500">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900">Data Summary Belum Ditampilkan</h3>
                        <p class="mt-2 text-sm text-gray-500">Silakan gunakan tombol <b>Filter</b> untuk melihat
                            ringkasan data.</p>
                        <div class="mt-4 text-xs text-gray-400">Total Transaksi di DB:
                            {{ number_format($totalRecords) }}</div>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Region</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Area</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Branch Code</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Branch Name</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Row</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Qty (Pcs)</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Gross Amt</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Line Disc 4</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Line Disc 8</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    DPP</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Tax</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-600 uppercase whitespace-nowrap">
                                    Nett Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sellouts as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        <div class="font-bold">{{ $row->region_name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        <div class="">{{ $row->entity_name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap font-mono">
                                        {{ $row->branch_code }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        {{ $row->branch_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-center font-bold">
                                        {{ number_format($row->row_count, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">
                                        {{ number_format($row->qty_pcs, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">
                                        {{ number_format($row->gross, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">
                                        {{ number_format($row->ld4, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">
                                        {{ number_format($row->bb, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">
                                        {{ number_format($row->dpp, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-mono">
                                        {{ number_format($row->tax, 0, ',', '.') }}</td>
                                    <td
                                        class="px-4 py-3 text-sm text-green-700 text-right font-mono font-bold bg-green-50">
                                        {{ number_format($row->nett_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-gray-500">Data tidak ditemukan
                                        untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if (method_exists($sellouts, 'links'))
                        <div class="px-6 py-4 bg-gray-50 border-t">
                            {{ $sellouts->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- FILTER MODAL --}}
        <div x-data="{ show: @entangle('showFilterModal') }" x-show="show" x-cloak class="fixed z-50 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="show = false"></div>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filter Summary Data</h3>
                        <div class="space-y-4">
                            {{-- Periode --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Periode (Invoice Date)</label>
                                <input type="month" wire:model="selectedMonth"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Region --}}
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Region</label>
                                    <button type="button" wire:click="selectAllRegions"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">Pilih
                                        Semua</button>
                                </div>
                                <select wire:model.live="selectedRegions" multiple
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border h-24">
                                    @foreach ($regionsOption as $r)
                                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Tahan CTRL untuk pilih banyak.</p>
                            </div>

                            {{-- Area --}}
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Area</label>
                                    @if (!empty($areasOption) && !$areasOption->isEmpty())
                                        <button type="button" wire:click="selectAllAreas"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">Pilih
                                            Semua</button>
                                    @endif
                                </div>
                                <select wire:model.live="selectedAreas" multiple
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border h-24"
                                    @if (empty($selectedRegions)) disabled @endif>
                                    @foreach ($areasOption as $a)
                                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Distributor --}}
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Distributor</label>
                                    @if (!empty($distributorsOption) && !$distributorsOption->isEmpty())
                                        <button type="button" wire:click="selectAllDistributors"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">Pilih
                                            Semua</button>
                                    @endif
                                </div>
                                <select wire:model.live="selectedDistributors" multiple
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm p-2 border h-24"
                                    @if (empty($selectedAreas)) disabled @endif>
                                    @foreach ($distributorsOption as $d)
                                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="filter"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Terapkan Filter
                        </button>
                        <button wire:click="$set('showFilterModal', false)"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- IMPORT MODAL (SAME AS BEFORE) --}}
        <div x-data="{ show: @entangle('showImportModal') }" x-show="show" x-cloak class="fixed z-50 inset-0 overflow-y-auto">
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
