<div>
    <x-slot name="title">Import Plan Call Team Elite</x-slot>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Notifikasi -->
        @if (session()->has('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm">
                <p class="font-bold">Sukses</p>
                <p class="text-sm">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm">
                <p class="font-bold">Gagal</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Card Upload -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden max-w-3xl mx-auto">
            <div class="px-6 py-4 border-b bg-gray-50 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <h2 class="text-lg font-bold text-gray-800">Upload Data Plan Call (Team Elite)</h2>
            </div>

            <div class="p-6">
                <!-- Info Format Excel -->
                <div class="mb-6 bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800 rounded-lg flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <strong>Format Kolom Excel yang Dibutuhkan (Secara Berurutan):</strong>
                        <ol class="list-decimal ml-4 mt-2 space-y-1">
                            <li>Tanggal (YYYY-MM-DD)</li>
                            <li>Minggu (W1/W2/dst)</li>
                            <li>Level</li>
                            <li>Kode Sales</li>
                            <li>Cabang</li>
                            <li>Kode Toko</li>
                            <li>Nama Toko</li>
                            <li>Pilar</li>
                            <li>Target</li>
                        </ol>
                        <p class="mt-2 text-xs italic text-gray-500">* Pastikan baris pertama adalah header dan data dimulai dari baris kedua.</p>
                    </div>
                </div>

                <!-- Form Livewire -->
                <form wire:submit.prevent="importData">
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File Excel (.xls, .xlsx)</label>
                        <input type="file" wire:model="excel_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-md cursor-pointer transition">
                        @error('excel_file') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-md flex items-center gap-2" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="importData">
                                <i class="fas fa-upload mr-1"></i> Proses Import
                            </span>
                            <span wire:loading wire:target="importData">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Sedang Mengimpor...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>