<div>
    <x-slot name="title">List Toko Pareto (Team Elite)</x-slot>

    <div class="mx-auto p-4 sm:p-6 lg:p-8">
        
        <!-- Notifikasi -->
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
            <!-- Header Panel -->
            <div class="px-6 py-4 border-b bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Kiri: Search -->
                <div class="w-full md:w-1/3 relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode/Nama/Alamat/Pilar..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors text-sm">
                </div>

                <!-- Kanan: Aksi (Tambah, Filter, Import, Export) -->
                <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto">
                    <!-- TOMBOL TAMBAH CUSTOMER BARU -->
                    <button wire:click="openFilterModal" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-semibold rounded-lg transition shadow-sm whitespace-nowrap">
                        <i class="fas fa-filter mr-2"></i> Filter
                        @if($filterRegion || $filterArea || $filterSupervisor)
                            <span class="ml-2 bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">!</span>
                        @endif
                    </button>                    
                    <button wire:click="openCreateModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm whitespace-nowrap">
                        <i class="fas fa-plus mr-2"></i> Tambah Customer
                    </button>
                    

                    <button wire:click="openImportModal" class="inline-flex items-center px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition shadow-sm whitespace-nowrap">
                        <i class="fas fa-file-import mr-2"></i> Import
                    </button>
                    <button wire:click="export" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition shadow-sm whitespace-nowrap" wire:loading.attr="disabled" wire:target="export">
                        <span wire:loading.remove wire:target="export"><i class="fas fa-file-export mr-2"></i> Export</span>
                        <span wire:loading wire:target="export"><i class="fas fa-spinner fa-spin mr-2"></i> Proses...</span>
                    </button>
                </div>
            </div>

            <!-- Tabel -->
            <div class="overflow-x-auto custom-scroll max-h-[65vh]">
                <table class="min-w-full divide-y divide-gray-200">
                   <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-20">Aksi</th>
                            
                            <th wire:click="sortBy('m.region_name')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Region</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'm.region_name' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('m.area_name')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Area</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'm.area_name' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('m.distributor_name')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Distributor</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'm.distributor_name' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('ms.description')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Supervisor</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'ms.description' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('l.customer_code_prc')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Kode PRC</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'l.customer_code_prc' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('l.customer_name')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Toko</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'l.customer_name' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('l.customer_address')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Alamat</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'l.customer_address' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('l.kecamatan')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Kecamatan</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'l.kecamatan' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('l.desa')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-between gap-2">
                                    <span>Desa</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'l.desa' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lat, Lng</th>
                            
                            <th wire:click="sortBy('l.pilar')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Pilar</span>
                                    <i class="fas fa-sort{{ $sortColumn === 'l.pilar' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                </div>
                            </th>
                            
                            <th wire:click="sortBy('l.target')" class="cursor-pointer hover:bg-gray-200 px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider select-none transition-colors">
                                <div class="flex items-center justify-end gap-2">
                                    <i class="fas fa-sort{{ $sortColumn === 'l.target' ? ($sortDirection === 'asc' ? '-up text-blue-600' : '-down text-blue-600') : ' text-gray-300' }}"></i>
                                    <span>Target</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($data as $item)
                        <tr class="hover:bg-blue-50/50">
                            <td class="px-4 py-3 whitespace-nowrap flex gap-2">
                                <button wire:click="edit({{ $item->id }})" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-1.5 rounded" title="Edit"><i class="fas fa-edit"></i></button>
                                <button wire:click="confirmDelete({{ $item->id }})" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded" title="Hapus"><i class="fas fa-trash"></i></button>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $item->region_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $item->area_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                <b>{{ $item->distributor_name }}</b><br><span class="text-[10px] text-gray-400">{{ $item->distributor_code }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $item->supervisor_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-gray-600">{{ $item->customer_code_prc }}</td>
                            <td class="px-4 py-3 min-w-[200px] text-gray-800 font-bold">{{ $item->customer_name }}</td>
                            <td class="px-4 py-3 min-w-[250px] text-gray-500 text-xs">{{ $item->customer_address }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->kecamatan }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->desa }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500 text-xs">
                                {{ $item->latitude ?? '-' }}, <br> {{ $item->longitude ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 py-1 rounded bg-gray-100 text-xs font-bold">{{ $item->pilar }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right font-mono font-bold">{{ number_format($item->target, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-folder-open text-4xl mb-3 text-gray-300 block"></i>
                                Tidak ada data ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $data->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL FILTER -->
    @if($isFilterModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Filter Data</h3>
                <button wire:click="closeFilterModal" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Region</label>
                    <select wire:model.live="filterRegion" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 text-sm">
                        <option value="">-- Semua Region --</option>
                        @foreach($regions as $r) <option value="{{ $r->region_code }}">{{ $r->region_name }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Area</label>
                    <select wire:model.live="filterArea" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 text-sm" @if(!$filterRegion) disabled @endif>
                        <option value="">-- Semua Area --</option>
                        @foreach($areas as $a) <option value="{{ $a->area_code }}">{{ $a->area_name }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Supervisor</label>
                    <select wire:model.live="filterSupervisor" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 text-sm" @if(!$filterArea) disabled @endif>
                        <option value="">-- Semua Supervisor --</option>
                        @foreach($supervisors as $s) <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option> @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
                <button wire:click="resetFilter" class="px-4 py-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg font-semibold text-sm">Reset</button>
                <button wire:click="applyFilter" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm">Terapkan</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL IMPORT -->
    @if($isImportModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <form wire:submit.prevent="import">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Import Excel (Full Sync)</h3>
                    <button type="button" wire:click="$set('isImportModalOpen', false)" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 text-blue-800 text-xs p-3 rounded mb-4 border border-blue-200">
                        <b>Info Full Sync:</b><br>Jika "Kode PRC + Distributor" sudah ada, data akan di-Update. Jika belum ada, akan di-Insert.
                    </div>

                    <!-- TOMBOL DOWNLOAD TEMPLATE -->
                    <button type="button" wire:click="downloadTemplate" class="mb-4 w-full flex items-center justify-center gap-2 px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-sm font-bold rounded-lg border border-green-300 transition">
                        <i class="fas fa-file-excel"></i> Download Template Format
                    </button>

                    <input type="file" wire:model="importFile" class="w-full border border-gray-300 rounded p-2 text-sm" accept=".xlsx,.xls,.csv" required>
                    @error('importFile') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
                    <button type="button" wire:click="$set('isImportModalOpen', false)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold text-sm">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-semibold text-sm flex items-center">
                        <span wire:loading.remove wire:target="import">Upload & Sync</span>
                        <span wire:loading wire:target="import"><i class="fas fa-spinner fa-spin mr-2"></i> Proses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL TAMBAH CUSTOMER BARU -->
    @if($isCreateModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all">
            <form wire:submit.prevent="store">
                <div class="px-6 py-4 border-b bg-gray-50 sticky top-0 z-10 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Tambah Customer Baru</h3>
                    <button type="button" wire:click="$set('isCreateModalOpen', false)" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Distributor Code <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="distributor_code" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500" placeholder="Contoh: SBY01">
                        @error('distributor_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Customer Code PRC <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="customer_code_prc" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500" placeholder="Contoh: CUST-991">
                        @error('customer_code_prc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Toko</label>
                        <input type="text" wire:model="customer_name" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alamat</label>
                        <textarea wire:model="customer_address" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kecamatan</label>
                        <input type="text" wire:model="kecamatan" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Desa</label>
                        <input type="text" wire:model="desa" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Latitude</label>
                        <input type="text" wire:model="latitude" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                        @error('latitude') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Longitude</label>
                        <input type="text" wire:model="longitude" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                        @error('longitude') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pilar</label>
                        <input type="text" wire:model="pilar" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Target</label>
                        <input type="number" step="0.01" wire:model="target" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2 sticky bottom-0">
                    <button type="button" wire:click="$set('isCreateModalOpen', false)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL EDIT -->
    @if($isEditModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all">
            <form wire:submit.prevent="update">
                <div class="px-6 py-4 border-b bg-gray-50 sticky top-0 z-10 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Edit Toko Pareto</h3>
                    <button type="button" wire:click="$set('isEditModalOpen', false)" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Distributor Code <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="distributor_code" class="w-full border-gray-300 rounded text-sm bg-gray-100" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Customer Code PRC <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="customer_code_prc" class="w-full border-gray-300 rounded text-sm bg-gray-100" readonly>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Toko</label>
                        <input type="text" wire:model="customer_name" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alamat</label>
                        <textarea wire:model="customer_address" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Kecamatan</label>
                        <input type="text" wire:model="kecamatan" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Desa</label>
                        <input type="text" wire:model="desa" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Latitude</label>
                        <input type="text" wire:model="latitude" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                        @error('latitude') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Longitude</label>
                        <input type="text" wire:model="longitude" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                        @error('longitude') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pilar</label>
                        <input type="text" wire:model="pilar" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Target</label>
                        <input type="number" step="0.01" wire:model="target" class="w-full border-gray-300 rounded text-sm focus:ring-blue-500">
                    </div>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2 sticky bottom-0">
                    <button type="button" wire:click="$set('isEditModalOpen', false)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL DELETE -->
    @if($isDeleteModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden transform transition-all text-center">
            <div class="p-6">
                <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4 block"></i>
                <h3 class="font-bold text-gray-800 text-lg mb-2">Hapus Data</h3>
                <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus data toko ini? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-center gap-3">
                <button wire:click="$set('isDeleteModalOpen', false)" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold text-sm">Batal</button>
                <button wire:click="delete" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold text-sm">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif

</div>