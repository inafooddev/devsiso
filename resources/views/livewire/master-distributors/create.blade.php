<div>
    <x-slot name="title">Tambah Master Distributor</x-slot>

    <div class="mx-auto px-6 py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="save">
                <!-- Header -->
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-700">Formulir Tambah Distributor</h3>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                            <p class="font-bold">Oops! Ada beberapa kesalahan:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Distributor Code -->
                        <div>
                            <label for="distributor_code" class="block text-sm font-medium text-gray-700 mb-1">Kode Distributor <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="distributor_code" id="distributor_code" class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Distributor Name -->
                        <div>
                            <label for="distributor_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Distributor <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="distributor_name" id="distributor_name" class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Join Date -->
                        <div>
                            <label for="join_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Bergabung</label>
                            <input type="date" wire:model.defer="join_date" id="join_date" class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Resign Date -->
                        <div>
                            <label for="resign_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Berhenti</label>
                            <input type="date" wire:model.defer="resign_date" id="resign_date" class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Latitude -->
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                            <input type="text" wire:model.defer="latitude" id="latitude" class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Longitude -->
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                            <input type="text" wire:model.defer="longitude" id="longitude" class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        
                        <!-- Searchable Branch Dropdown -->
                        <div class="md:col-span-2" x-data="{ open: false }" @click.away="open = false">
                            <label for="branch_search" class="block text-sm font-medium text-gray-700 mb-1">Pilih Cabang <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" 
                                    wire:model.live.debounce.300ms="branchSearch"
                                    x-show="!$wire.branch_code"
                                    @focus="open = true"
                                    id="branch_search"
                                    placeholder="Ketik untuk mencari cabang..."
                                    class="w-full form-input border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">

                                <div x-show="$wire.branch_code" class="w-full flex items-center justify-between form-input bg-gray-100 border-blue-300 shadow-sm p-1">
                                    <span x-text="$wire.selectedBranchName"></span>
                                    <button type="button" @click="$wire.set('branch_code', ''); $wire.set('selectedBranchName', '')" class="text-red-500 hover:text-red-700">
                                        &times;
                                    </button>
                                </div>
                                
                                <div x-show="open && $wire.branchSearch.length >= 2" x-transition class="absolute z-10 w-full mt-1 bg-white border border-gray-300 shadow-lg max-h-60 overflow-y-auto">
                                    @if($branches->isNotEmpty())
                                        @foreach($branches as $branch)
                                            <div wire:click="selectBranch('{{ $branch->branch_code }}', '{{ $branch->branch_name }}')" 
                                                 @click="open = false"
                                                 class="cursor-pointer p-2 hover:bg-gray-100">
                                                {{ $branch->branch_name }} ({{ $branch->branch_code }})
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-2 text-gray-500">Tidak ada cabang ditemukan.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Region Name (Disabled) -->
                        <div>
                            <label for="region_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Region</label>
                            <input type="text" wire:model="region_name" id="region_name" class="w-full form-input border bg-gray-100 border-blue-300 shadow-sm p-1" disabled>
                        </div>

                        <!-- Area Name (Disabled) -->
                        <div>
                            <label for="area_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Area</label>
                            <input type="text" wire:model="area_name" id="area_name" class="w-full form-input border bg-gray-100 border-blue-300 shadow-sm p-1" disabled>
                        </div>
                        
                        <!-- Supervisor Name (Disabled) -->
                        <div class="md:col-span-2">
                            <label for="supervisor_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Supervisor</label>
                            <input type="text" wire:model="supervisor_name" id="supervisor_name" class="w-full border form-input bg-gray-100 border-blue-300 shadow-sm p-1" disabled>
                        </div>

                        <!-- Is Active -->
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model.defer="is_active" id="is_active" class="w-full form-select border border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end items-center space-x-4">
                    <a href="{{ route('master-distributors.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

