<div>
    <x-slot name="title">Tambah Salesman</x-slot>

    <div class="mx-auto px-6 py-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="save">
                <!-- Header -->
                <div class="px-6 py-4 border-b bg-gray-50 sticky top-0 z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">Formulir Tambah Salesman</h3>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('salesmans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
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
                            <select wire:model.live="regionFilter" id="regionFilter" class="w-full form-select border-blue-300 shadow-sm p-1">
                                <option value="">-- Pilih Region --</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="areaFilter" class="block text-sm font-medium text-gray-700 mb-1">Area <span class="text-red-500">*</span></label>
                            <select wire:model.live="areaFilter" id="areaFilter" class="w-full form-select border-blue-300 shadow-sm p-1" @if(!$regionFilter) disabled @endif>
                                <option value="">-- Pilih Area --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="distributor_code" class="block text-sm font-medium text-gray-700 mb-1">Distributor <span class="text-red-500">*</span></label>
                            <select wire:model.live="distributor_code" id="distributor_code" class="w-full form-select border-blue-300 shadow-sm p-1" @if(!$areaFilter) disabled @endif>
                                <option value="">-- Pilih Distributor --</option>
                                @foreach($distributors as $distributor)
                                    <option
                                        value="{{ $distributor->distributor_code }}"
                                        class="{{ $distributor->is_active ? 'text-green-600' : 'text-red-600' }}"
                                    >
                                        {{ $distributor->distributor_code }} - {{ $distributor->distributor_name }}                                        
                                    </option>
                                @endforeach
                            </select>
                            @error('distributor_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <hr class="md:col-span-2 my-2">

                        <!-- Input Nomor Manual -->
                        <div>
                            <label for="manual_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor Urut Kode <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.live="manual_number" id="manual_number" placeholder="Contoh: 01" class="w-full form-input border-blue-300 shadow-sm p-1">
                            @error('manual_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-500 italic">Masukkan angka atau akhiran unik untuk salesman.</p>
                        </div>

                        <!-- Hasil Kode Salesman (Read-only) -->
                        <div>
                            <label for="salesman_code" class="block text-sm font-medium text-gray-700 mb-1">Salesman Code</label>
                            <input type="text" wire:model="salesman_code" id="salesman_code" readonly class="w-full form-input bg-gray-100 border-gray-300 shadow-sm p-1 cursor-not-allowed font-mono font-bold text-blue-700">
                            @error('salesman_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="salesman_name" class="block text-sm font-medium text-gray-700 mb-1">Salesman Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="salesman_name" id="salesman_name" class="w-full form-input border-blue-300 shadow-sm p-1">
                            @error('salesman_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                             <select wire:model.defer="is_active" id="is_active" class="w-full form-select border-blue-300 shadow-sm p-1">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                             @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>