<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Selling Out Summary</x-slot>

    {{-- Notifikasi Toast --}}
    <div class="toast toast-top toast-center z-[100] mt-16">
        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                    <div class="text-sm">{{ session('success') }}</div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                    <div class="text-sm">{{ session('error') }}</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <x-heroicon-s-shopping-bag class="w-5 h-5" />
                    </div>
                    Selling Out Summary
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Ringkasan transaksi penjualan distributor ke outlet</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                @if ($isFiltered)
                <div class="relative group grow sm:grow-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.500ms="search" type="text"
                           placeholder="Cari Branch..."
                           class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>
                @endif
                
                {{-- Actions Button --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <button wire:click="$set('showFilterModal', true)" class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        Filter
                        @if($isFiltered)
                            <span class="badge badge-xs badge-primary rounded-full">ON</span>
                        @endif
                    </button>

                    @canImport('selling-out-eskalink.index')
                    <button wire:click="$set('showImportModal', true)" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20 transition-all duration-200">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import
                    </button>
                    @endcanImport

                    @if ($isFiltered)
                    @canExport('selling-out-eskalink.index')
                    <button wire:click="export" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-down-tray wire:loading.remove wire:target="export" class="w-4 h-4" />
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export
                    </button>
                    @endcanExport
                    @endif
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            @if (!$isFiltered)
                <div class="flex flex-col items-center justify-center py-20 text-base-content/40 absolute inset-0">
                    <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                        <x-heroicon-s-shopping-bag class="w-10 h-10" />
                    </div>
                    <h3 class="text-base font-bold text-base-content/60 mb-1">Data Belum Ditampilkan</h3>
                    <p class="text-sm text-center max-w-xs">Silakan gunakan tombol filter untuk melihat ringkasan data.</p>
                    <p class="mt-4 text-xs font-mono bg-base-200/50 px-3 py-1 rounded-lg">Total Transaksi DB: {{ number_format($totalRecords) }}</p>
                    <button wire:click="$set('showFilterModal', true)"
                            class="btn btn-sm btn-primary rounded-xl normal-case gap-2 mt-6 shadow-sm shadow-primary/20">
                        <x-heroicon-s-funnel class="w-4 h-4" /> Buka Filter Summary
                    </button>
                </div>
            @else
                @if($sellouts->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-base-content/40 absolute inset-0">
                        <div class="w-20 h-20 rounded-full bg-base-200 flex items-center justify-center mb-5">
                            <x-heroicon-s-magnifying-glass class="w-10 h-10" />
                        </div>
                        <h3 class="text-base font-bold text-base-content/60 mb-1">Data Tidak Ditemukan</h3>
                        <p class="text-sm text-center max-w-xs">Tidak ada data summary yang ditemukan untuk kriteria filter ini.</p>
                    </div>
                @else
                    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                        <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                            <tr>
                                <th>Region</th>
                                <th>Area</th>
                                <th>Branch Code</th>
                                <th>Branch Name</th>
                                <th class="text-center">Row</th>
                                <th class="text-right">Qty (Pcs)</th>
                                <th class="text-right">Gross Amt</th>
                                <th class="text-right">Line Disc 4</th>
                                <th class="text-right">Line Disc 8</th>
                                <th class="text-right">DPP</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Nett Amount</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($sellouts as $row)
                                <tr class="hover:bg-base-200/50 transition-colors group text-sm">
                                    <td><div class="font-bold text-base-content/80">{{ $row->region_name }}</div></td>
                                    <td><div class="text-base-content/70">{{ $row->entity_name }}</div></td>
                                    <td><span class="font-mono text-[11px] text-base-content/50 uppercase tracking-widest">{{ $row->branch_code }}</span></td>
                                    <td><span class="font-semibold text-base-content/80">{{ $row->branch_name }}</span></td>
                                    <td class="text-center font-bold text-base-content/60">{{ number_format($row->row_count, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono">{{ number_format($row->qty_pcs, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono text-base-content/70">{{ number_format($row->gross, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono text-base-content/70">{{ number_format($row->ld4, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono text-base-content/70">{{ number_format($row->bb, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono text-base-content/70">{{ number_format($row->dpp, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono text-base-content/70">{{ number_format($row->tax, 0, ',', '.') }}</td>
                                    <td class="text-right font-mono font-bold text-success bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                        {{ number_format($row->nett_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
        </div>

        {{-- Pagination Footer --}}
        @if ($isFiltered && method_exists($sellouts, 'links') && $sellouts->hasPages())
        <div class="p-3 border-t border-base-300 bg-base-50 shrink-0">
            {{ $sellouts->links() }}
        </div>
        @endif
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
