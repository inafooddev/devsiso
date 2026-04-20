<div>
    <x-slot name="title">Customer Eska Unmap</x-slot>

    <div class="mx-auto px-6 py-8" x-data="{ showFilterModal: false }">

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">

            {{-- Header & Actions --}}
            <div
                class="px-6 py-4 border-b bg-gray-50 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                <div class="flex items-center space-x-2 flex-wrap">

                    {{-- Tombol Filter --}}
                    <button @click="showFilterModal = true"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus:border-blue-300 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Filter
                    </button>

                    @if ($isFiltered)
                        <button wire:click="export" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-50 transition ease-in-out duration-150 shadow-sm">
                            <svg wire:loading wire:target="export" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg wire:loading.remove wire:target="export" class="w-4 h-4 mr-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Export Excel
                        </button>
                    @endif
                </div>

                @if ($isFiltered)
                    <div class="w-full sm:w-auto">
                        <input wire:model.live.debounce.500ms="search" type="text"
                            placeholder="Cari Kode / Nama Customer..."
                            class="w-full sm:w-64 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                    </div>
                @endif
            </div>

            {{-- Tabel Data --}}
            <div class="overflow-x-auto">
                @if (!$isFiltered)
                    <div class="p-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Data Belum Dimuat</h3>
                        <p class="mt-1 text-sm text-gray-500">Silakan klik tombol <strong>Filter</strong> untuk memilih
                            Periode, Region, Area, dan Distributor.</p>
                        <button @click="showFilterModal = true"
                            class="mt-4 text-indigo-600 hover:text-indigo-500 font-medium text-sm">Buka Filter
                            &rarr;</button>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Region</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Area</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    DistID</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Branch Dist</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider bg-blue-50">
                                    Cust Dist Code</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider bg-blue-50">
                                    Cust Dist Name</th>

                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Branch PRC</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-green-600 uppercase tracking-wider bg-green-50">
                                    Cust PRC Code</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-green-600 uppercase tracking-wider bg-green-50">
                                    Cust PRC Name</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($customers as $row)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-4 py-3 whitespace-nowrap text-xs font-bold text-gray-900">
                                        {{ $row->region_name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">{{ $row->area_name }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs font-bold text-gray-900">
                                        {{ $row->distid }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                        {{ $row->branch_dist }}</td>
                                    <td class="px-4 py-3 text-sm bg-blue-50/30 text-blue-600 font-mono">
                                        {{ $row->custno_dist }}</td>
                                    <td class="px-4 py-3 text-sm bg-blue-50/30 font-medium text-gray-900">
                                        {{ $row->dist_cust_name ?? '-' }}</td>

                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">{{ $row->branch }}
                                    </td>
                                    <td class="px-4 py-3 text-sm bg-green-50/30 text-green-600 font-mono">
                                        {{ $row->custno }}</td>
                                    <td class="px-4 py-3 text-sm bg-green-50/30 font-medium text-gray-900">
                                        {{ $row->prc_cust_name ?? '-' }}</td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                                        <h3 class="text-sm font-medium text-gray-900">Tidak Ada Data</h3>
                                        <p class="mt-1 text-sm text-gray-500">Tidak ada data yang cocok dengan kriteria
                                            filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if (method_exists($customers, 'links'))
                        <div class="px-6 py-4 bg-gray-50 border-t">
                            {{ $customers->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Modal Filter --}}
        <div x-show="showFilterModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showFilterModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showFilterModal = false">
                </div>

                <div x-show="showFilterModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Filter
                                    Analysis</h3>
                                <div class="mt-4 space-y-4">
                                    {{-- Periode --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Periode (Bulan)</label>
                                        <input type="month" wire:model.live="selectedMonth"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
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
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border h-24">
                                            @foreach ($regionsOption as $r)
                                                <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Area --}}
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <label class="block text-sm font-medium text-gray-700">Area</label>
                                            @if (!empty($areasOption))
                                                <button type="button" wire:click="selectAllAreas"
                                                    class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">Pilih
                                                    Semua</button>
                                            @endif
                                        </div>
                                        <select wire:model.live="selectedAreas" multiple
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border h-24"
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
                                            @if (!empty($distributorsOption))
                                                <button type="button" wire:click="selectAllDistributors"
                                                    class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">Pilih
                                                    Semua</button>
                                            @endif
                                        </div>
                                        <select wire:model.live="selectedDistributors" multiple
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border h-24"
                                            @if (empty($selectedAreas)) disabled @endif>
                                            @foreach ($distributorsOption as $d)
                                                <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="filter" @click="showFilterModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Tampilkan
                            Data</button>
                        <button type="button" @click="showFilterModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
