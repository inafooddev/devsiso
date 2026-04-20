<div>
    <x-slot name="title">Edit Salesman</x-slot>

    <div class="mx-auto px-6 py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="update">
                <!-- Header -->
                <div class="px-6 py-4 border-b bg-gray-50 sticky top-0 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">Formulir Edit Salesman</h3>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('salesmans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-50 transition ease-in-out duration-150">
                                <span wire:loading.remove>Perbarui</span>
                                <span wire:loading>Memproses...</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    @if ($errors->any())
                        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md">
                            <p class="font-bold text-sm">Terjadi Kesalahan:</p>
                            <ul class="mt-1 list-disc list-inside text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Region -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                            <select wire:model.live="regionFilter" class="w-full form-select border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Region --</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Area -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Area <span class="text-red-500">*</span></label>
                            <select wire:model.live="areaFilter" class="w-full form-select border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled(!$regionFilter)>
                                <option value="">-- Pilih Area --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Distributor -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Distributor <span class="text-red-500">*</span></label>
                            <select wire:model.live="distributor_code" class="w-full form-select border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" @disabled(!$areaFilter)>
                                <option value="">-- Pilih Distributor --</option>
                                @foreach($distributors as $distributor)
                                    <option value="{{ $distributor->distributor_code }}">[{{ $distributor->distributor_code }}] {{ $distributor->distributor_name }}</option>
                                @endforeach
                            </select>
                            @error('distributor_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2"><hr></div>

                        <!-- Form Utama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salesman Code <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="salesman_code" class="w-full form-input border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('salesman_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salesman Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="salesman_name" class="w-full form-input border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('salesman_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model="is_active" class="w-full form-select border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>