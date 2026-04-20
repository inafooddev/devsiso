<div>
    <x-slot name="title">Edit Master Product</x-slot>

    <div class="mx-auto px-6 py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            {{-- Pastikan wire:model.defer mengikat ke properti publik di komponen --}}
            <form wire:submit.prevent="update">
                 <!-- Header -->
                <div class="px-6 py-4 border-b bg-gray-50 sticky top-0 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">Formulir Edit Master Product</h3>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('product-masters.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Perbarui
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
                    
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Product ID -->
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product ID <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="product_id" id="product_id" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('product_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <!-- Product Name -->
                        <div class="md:col-span-2">
                            <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="product_name" id="product_name" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('product_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                     <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <!-- Product Line -->
                        <div>
                            <label for="line_id" class="block text-sm font-medium text-gray-700 mb-1">Line <span class="text-red-500">*</span></label>
                            <select wire:model.defer="line_id" id="line_id" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Line --</option>
                                @foreach($productLines as $line) <option value="{{ $line->line_id }}">{{ $line->line_name }}</option> @endforeach
                            </select>
                             @error('line_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                         <!-- Product Brand -->
                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">Brand <span class="text-red-500">*</span></label>
                            <select wire:model.defer="brand_id" id="brand_id" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Brand --</option>
                                @foreach($productBrands as $brand) <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option> @endforeach
                            </select>
                             @error('brand_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <!-- Product Group -->
                        <div>
                            <label for="product_group_id" class="block text-sm font-medium text-gray-700 mb-1">Group <span class="text-red-500">*</span></label>
                            <select wire:model.defer="product_group_id" id="product_group_id" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Group --</option>
                                @foreach($productGroups as $group) <option value="{{ $group->product_group_id }}">{{ $group->brand_unit_name }}</option> @endforeach
                            </select>
                             @error('product_group_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <!-- Product Sub-Brand -->
                        <div>
                            <label for="sub_brand_id" class="block text-sm font-medium text-gray-700 mb-1">Sub-Brand</label>
                            <select wire:model.defer="sub_brand_id" id="sub_brand_id" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">-- Pilih Sub-Brand (Opsional) --</option>
                                @foreach($productSubBrands as $subBrand) <option value="{{ $subBrand->sub_brand_id }}">{{ $subBrand->sub_brand_name }}</option> @endforeach
                            </select>
                             @error('sub_brand_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <div class="mt-2 p-2 border border-gray-300 rounded-md max-h-40 overflow-y-auto space-y-2">
                             @foreach($allCategories as $category)
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.defer="selectedCategories" value="{{ $category->category_id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">{{ $category->category_name }}</span>
                                </label>
                            @endforeach
                        </div>
                         @error('selectedCategories') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                        <!-- Base Unit -->
                        <div>
                            <label for="base_unit" class="block text-sm font-medium text-gray-700 mb-1">Base Unit <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="base_unit" id="base_unit" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('base_unit') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <!-- UOM 1 -->
                        <div>
                            <label for="uom1" class="block text-sm font-medium text-gray-700 mb-1">UOM 1</label>
                            <input type="text" wire:model.defer="uom1" id="uom1" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('uom1') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                         <!-- Conv Unit 1 -->
                        <div>
                            <label for="conv_unit1" class="block text-sm font-medium text-gray-700 mb-1">Conv. 1</label>
                            <input type="number" step="0.01" wire:model.defer="conv_unit1" id="conv_unit1" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('conv_unit1') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <!-- UOM 2 -->
                        <div>
                            <label for="uom2" class="block text-sm font-medium text-gray-700 mb-1">UOM 2</label>
                            <input type="text" wire:model.defer="uom2" id="uom2" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('uom2') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                         <!-- Conv Unit 2 -->
                        <div>
                            <label for="conv_unit2" class="block text-sm font-medium text-gray-700 mb-1">Conv. 2</label>
                            <input type="number" step="0.01" wire:model.defer="conv_unit2" id="conv_unit2" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('conv_unit2') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <!-- UOM 3 -->
                         <div>
                            <label for="uom3" class="block text-sm font-medium text-gray-700 mb-1">UOM 3</label>
                            <input type="text" wire:model.defer="uom3" id="uom3" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('uom3') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                         <!-- Conv Unit 3 -->
                        <div>
                            <label for="conv_unit3" class="block text-sm font-medium text-gray-700 mb-1">Conv. 3</label>
                            <input type="number" step="0.01" wire:model.defer="conv_unit3" id="conv_unit3" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('conv_unit3') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                         <!-- Status -->
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                             <select wire:model.defer="is_active" id="is_active" class="w-full form-select border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                             @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <fieldset class="border rounded-md p-4">
                        <legend class="text-sm font-medium text-gray-700 px-2">Harga Jual</legend>
                         <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                             <!-- Price Zone 1 -->
                            <div>
                                <label for="price_zone1" class="block text-sm font-medium text-gray-700 mb-1">Zone 1</label>
                                <input type="number" step="0.01" wire:model.defer="price_zone1" id="price_zone1" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('price_zone1') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <!-- Price Zone 2 -->
                            <div>
                                <label for="price_zone2" class="block text-sm font-medium text-gray-700 mb-1">Zone 2</label>
                                <input type="number" step="0.01" wire:model.defer="price_zone2" id="price_zone2" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('price_zone2') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <!-- Price Zone 3 -->
                            <div>
                                <label for="price_zone3" class="block text-sm font-medium text-gray-700 mb-1">Zone 3</label>
                                <input type="number" step="0.01" wire:model.defer="price_zone3" id="price_zone3" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('price_zone3') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <!-- Price Zone 4 -->
                            <div>
                                <label for="price_zone4" class="block text-sm font-medium text-gray-700 mb-1">Zone 4</label>
                                <input type="number" step="0.01" wire:model.defer="price_zone4" id="price_zone4" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('price_zone4') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                             <!-- Price Zone 5 -->
                            <div>
                                <label for="price_zone5" class="block text-sm font-medium text-gray-700 mb-1">Zone 5</label>
                                <input type="number" step="0.01" wire:model.defer="price_zone5" id="price_zone5" class="w-full form-input border-blue-300 shadow-sm p-1 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('price_zone5') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                         </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </div>
</div>

