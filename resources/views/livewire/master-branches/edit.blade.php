<div>
    <x-slot name="title">Edit Master Cabang</x-slot>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="update">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-800">Form Edit Cabang</h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode Cabang -->
                    <div>
                        <label for="branch_code" class="block text-sm font-medium text-gray-700">Kode Cabang</label>
                        <input wire:model.live="branch_code" type="text" id="branch_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('branch_code') border-red-500 @enderror" placeholder="Contoh: CAB-001">
                        @error('branch_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Cabang -->
                    <div>
                        <label for="branch_name" class="block text-sm font-medium text-gray-700">Nama Cabang</label>
                        <input wire:model.live="branch_name" type="text" id="branch_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('branch_name') border-red-500 @enderror" placeholder="Contoh: Cabang Jakarta Pusat">
                        @error('branch_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Region -->
                    <div class="md:col-span-2">
                        <label for="selectedRegion" class="block text-sm font-medium text-gray-700">Region</label>
                        <select wire:model.live="selectedRegion" id="selectedRegion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('selectedRegion') border-red-500 @enderror">
                            <option value="">-- Pilih Region --</option>
                            @foreach($this->regions as $region)
                                <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                        @error('selectedRegion') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Area -->
                    <div class="md:col-span-2">
                        <label for="selectedArea" class="block text-sm font-medium text-gray-700">Area</label>
                        <select wire:model.live="selectedArea" id="selectedArea" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('selectedArea') border-red-500 @enderror" @if(count($this->areas) == 0) disabled @endif>
                            <option value="">-- Pilih Area --</option>
                            @foreach($this->areas as $area)
                                <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                            @endforeach
                        </select>
                        @error('selectedArea') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Supervisor -->
                    <div class="md:col-span-2">
                        <label for="supervisor_code" class="block text-sm font-medium text-gray-700">Supervisor</label>
                        <select wire:model.live="supervisor_code" id="supervisor_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('supervisor_code') border-red-500 @enderror" @if(count($this->supervisors) == 0) disabled @endif>
                            <option value="">-- Pilih Supervisor --</option>
                            @foreach($this->supervisors as $supervisor)
                                <option value="{{ $supervisor->supervisor_code }}">
                                    {{ $supervisor->supervisor_name }}
                                    @if($supervisor->description)
                                        ({{ $supervisor->description }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('supervisor_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t text-right space-x-2">
                    <a href="{{ route('master-branches.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Kembali</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

