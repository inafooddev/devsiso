<div>
    <x-slot name="title">Edit Master Region</x-slot>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="max-w-xl mx-auto">
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-xl font-semibold text-gray-800">Edit Region: <span class="font-bold">{{ $region_code }}</span></h2>
                </div>
                
                <form wire:submit.prevent="update">
                    <div class="p-6 space-y-6">
                        <!-- Kode Region -->
                        <div>
                            <label for="region_code" class="block text-sm font-medium text-gray-700 mb-1">Kode Region</label>
                            <input wire:model.live="region_code" type="text" id="region_code"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('region_code') border-red-500 @enderror">
                            @error('region_code') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nama Region -->
                        <div>
                            <label for="region_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Region</label>
                            <input wire:model.live="region_name" type="text" id="region_name"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('region_name') border-red-500 @enderror">
                            @error('region_name') <span class="text-red-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end items-center space-x-4 border-t">
                        <a href="{{ route('master-regions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">Batal</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

