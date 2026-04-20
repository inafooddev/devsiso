<div>
    <x-slot name="title">Mapping Produk Eska</x-slot>

    <div class="mx-auto px-6 py-8" x-data="{ 
        showFilterModal: @entangle('showFilterModal'), 
        showExportModal: @entangle('showExportModal') 
    }">
        
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-8">
            
            {{-- Header & Toolbar --}}
            <div class="px-6 py-4 border-b bg-gray-50 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                 <div class="flex items-center space-x-2">
                    
                    {{-- Tombol Filter --}}
                    <button @click="showFilterModal = true" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring ring-blue-300 shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filter Data
                    </button>

                    {{-- Tombol Export --}}
                    @if($isFiltered)
                    <button wire:click="openExportModal" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring ring-green-300 shadow-sm transition">
                         <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export Excel
                    </button>
                    @endif
                 </div>

                 @if($isFiltered)
                 <div class="w-full sm:w-auto">
                    <input wire:model.live.debounce.500ms="search" type="text" placeholder="Cari Produk..." class="border rounded px-3 py-2 text-sm w-64 focus:ring-indigo-500 focus:border-indigo-500">
                 </div>
                 @endif
            </div>

            {{-- Table Content --}}
             <div class="overflow-x-auto">
                @if (!$isFiltered)
                    <div class="p-12 text-center text-gray-500">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <h3 class="text-lg font-medium text-gray-900">Data Belum Ditampilkan</h3>
                        <p class="mt-2 text-sm text-gray-500">Silakan gunakan tombol <b>Filter Data</b> untuk memilih Region, Area, dan Distributor.</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase whitespace-nowrap">Kode Dist</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase whitespace-nowrap">Kode Produk Dist</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase whitespace-nowrap">Nama Produk Dist</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase whitespace-nowrap">Kode Produk PRC</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase whitespace-nowrap">Nama Produk PRC</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($products as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-700">{{ $row->eskalink_code_dist }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-blue-600">{{ $row->product_code_dist }}</td>
                                <td class="px-4 py-3">{{ $row->product_name_dist }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-mono text-green-600">{{ $row->product_code_prc }}</td>
                                <td class="px-4 py-3">{{ $row->product_name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">Data tidak ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        {{ $products->links() }}
                    </div>
                @endif
             </div>
        </div>

        {{-- MODAL FILTER --}}
        <div x-show="showFilterModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
             <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showFilterModal = false"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filter Mapping Produk</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Region</label>
                                <select wire:model.live="selectedRegion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                                    <option value="">-- Pilih Region --</option>
                                    @foreach($regions as $r)
                                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Area</label>
                                <select wire:model.live="selectedArea" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" @if(empty($selectedRegion)) disabled @endif>
                                    <option value="">-- Pilih Area --</option>
                                    @foreach($areas as $a)
                                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Distributor</label>
                                <select wire:model.live="selectedDistributor" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm" @if(empty($selectedArea)) disabled @endif>
                                    <option value="">-- Pilih Distributor --</option>
                                    @foreach($distributors as $d)
                                        <option value="{{ $d->distributor_code }}">{{ $d->distributor_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="filter" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Tampilkan Data
                        </button>
                        <button @click="showFilterModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
             </div>
        </div>

        {{-- MODAL EXPORT --}}
        <div x-show="showExportModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
             <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showExportModal = false"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl 
                    transform transition-all sm:my-8 sm:align-middle 
                    w-full sm:max-w-3xl">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Export Data Excel</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Filter Produk (Opsional)</label>
                                    @if(!empty($productOptions) && count($productOptions) > 0)
                                        <button type="button" wire:click="selectAllProducts" class="text-xs text-blue-600 hover:text-blue-800 hover:underline">Pilih Semua</button>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mb-2">Pilih produk spesifik atau biarkan kosong untuk semua produk yang ditampilkan.</div>
                                
                                <select wire:model="selectedProducts" multiple class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm p-2 border h-96">
                                    @foreach($productOptions as $prod)
                                        <option value="{{ $prod->product_code_dist }}">{{ $prod->product_code_dist }} - {{ $prod->product_name_dist }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Tahan tombol CTRL (Windows) atau CMD (Mac) untuk memilih banyak.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="export" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading wire:target="export" class="animate-spin mr-2">
                                <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </span>
                            Download Excel
                        </button>
                        <button @click="showExportModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
             </div>
        </div>

    </div>
</div>