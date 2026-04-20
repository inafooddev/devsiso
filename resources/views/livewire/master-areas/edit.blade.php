<div>
    <x-slot name="title">Edit Master Area</x-slot>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="update">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-800">Form Edit Area</h2>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Kode Area -->
                    <div>
                        <label for="area_code" class="block text-sm font-medium text-gray-700">Kode Area</label>
                        <input wire:model.live="area_code" type="text" id="area_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('area_code') border-red-500 @enderror" placeholder="Contoh: AREA-001">
                        @error('area_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Area -->
                    <div>
                        <label for="area_name" class="block text-sm font-medium text-gray-700">Nama Area</label>
                        <input wire:model.live="area_name" type="text" id="area_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('area_name') border-red-500 @enderror" placeholder="Contoh: Area Bandung Kota">
                        @error('area_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Region -->
                    <div>
                        <label for="region_code" class="block text-sm font-medium text-gray-700">Region</label>
                        <select wire:model.live="region_code" id="region_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('region_code') border-red-500 @enderror">
                            <option value="">-- Pilih Region --</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                        @error('region_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t text-right space-x-2">
                    <a href="{{ route('master-areas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Kembali</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
