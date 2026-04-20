<div>
    <x-slot name="title">Tambah Pemetaan Produk</x-slot>

    <div class="mx-auto px-6 py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="save">
                <!-- Header -->
                <div class="px-6 py-4 border-b bg-gray-50 sticky top-0 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">Formulir Tambah Pemetaan</h3>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('product-mappings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                            <p class="font-bold">Oops! Ada beberapa kesalahan:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Filter Berjenjang -->
                        <div>
                            <label for="regionFilter" class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                            <select wire:model.live="regionFilter" id="regionFilter" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Region --</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="areaFilter" class="block text-sm font-medium text-gray-700 mb-1">Area <span class="text-red-500">*</span></label>
                            <select wire:model.live="areaFilter" id="areaFilter" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" @if(!$regionFilter) disabled @endif>
                                <option value="">-- Pilih Area --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="distributor_code" class="block text-sm font-medium text-gray-700 mb-1">Distributor <span class="text-red-500">*</span></label>
                            <select wire:model.defer="distributor_code" id="distributor_code" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" @if(!$areaFilter) disabled @endif>
                                <option value="">-- Pilih Distributor --</option>
                                @foreach($distributors as $distributor)
                                    <option value="{{ $distributor->distributor_code }}">{{ $distributor->distributor_name }} ({{ $distributor->distributor_code }})</option>
                                @endforeach
                            </select>
                            @error('distributor_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <hr class="md:col-span-2 my-4">

                        <!-- Info Produk Distributor -->
                        <div>
                            <label for="product_code_dist" class="block text-sm font-medium text-gray-700 mb-1">Kode Produk (Distributor)</label>
                            <input type="text" wire:model.defer="product_code_dist" id="product_code_dist" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Opsional">
                            @error('product_code_dist') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="product_name_dist" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk (Distributor)</label>
                            <input type="text" wire:model.defer="product_name_dist" id="product_name_dist" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Opsional">
                            @error('product_name_dist') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Info Produk Principal (Searchable) -->
                        <div class="md:col-span-2" x-data="{ open: false }" @click.away="open = false">
                            <label for="product_search" class="block text-sm font-medium text-gray-700 mb-1">Kode Produk (Principal)</label>
                            <div class="relative">
                                <input type="text" 
                                    wire:model.live.debounce.300ms="productSearch"
                                    @focus="open = true"
                                    id="product_search"
                                    placeholder="Ketik untuk cari kode/nama produk principal..."
                                    class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    x-show="!$wire.product_code_prc">

                                <div x-show="$wire.product_code_prc" class="w-full flex items-center justify-between form-input bg-gray-100 border-blue-300 shadow-sm p-1">
                                    <span>{{ $product_code_prc }}</span>
                                    <button type="button" wire:click="selectProduct(null)" class="text-red-500 hover:text-red-700 font-bold text-lg leading-none">&times;</button>
                                </div>
                                
                                <div x-show="open && $wire.productSearch.length >= 2 && !$wire.product_code_prc" x-transition class="relative z-10 w-full mt-1 bg-white border border-gray-300 shadow-lg max-h-60 overflow-y-auto">
                                    @forelse($principalProducts as $product)
                                        <div wire:click="selectProduct('{{ $product->product_id }}')" 
                                             @click="open = false"
                                             class="cursor-pointer p-2 hover:bg-gray-100">
                                            {{ $product->product_name }} ({{ $product->product_id }})
                                        </div>
                                    @empty
                                        <div class="p-2 text-gray-500">Tidak ada produk ditemukan.</div>
                                    @endforelse
                                </div>
                            </div>
                            @error('product_code_prc') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
