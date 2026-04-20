<div>
    <x-slot name="title">Tambah Master Supervisor</x-slot>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form wire:submit.prevent="save">
                <div class="px-6 py-4 border-b bg-gray-50">
                    <h2 class="text-lg font-bold text-gray-800">Form Tambah Supervisor</h2>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode Supervisor -->
                    <div>
                        <label for="supervisor_code" class="block text-sm font-medium text-gray-700">Kode Supervisor</label>
                        <input wire:model.live="supervisor_code" type="text" id="supervisor_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('supervisor_code') border-red-500 @enderror" placeholder="Contoh: SPV-001">
                        @error('supervisor_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nama Supervisor -->
                    <div>
                        <label for="supervisor_name" class="block text-sm font-medium text-gray-700">Nama Supervisor</label>
                        <input wire:model.live="supervisor_name" type="text" id="supervisor_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('supervisor_name') border-red-500 @enderror" placeholder="Contoh: Budi Santoso">
                        @error('supervisor_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Area -->
                    <div class="md:col-span-2">
                        <label for="area_code" class="block text-sm font-medium text-gray-700">Area</label>
                        <select wire:model.live="area_code" id="area_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('area_code') border-red-500 @enderror">
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                            @endforeach
                        </select>
                        @error('area_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Keterangan (Opsional)</label>
                        <textarea wire:model.live="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-500 @enderror" placeholder="Informasi tambahan..."></textarea>
                        @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t text-right space-x-2">
                    <a href="{{ route('master-supervisors.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">Kembali</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
