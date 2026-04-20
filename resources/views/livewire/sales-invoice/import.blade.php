<div class="mx-auto px-6 py-8">
    <x-slot name="title">Import Sales Invoice Distributor</x-slot>
    {{--
        ==================================================================
        | View untuk Komponen Livewire SalesInvoiceImport                |
        | Didesain dengan Tailwind CSS & Alpine.js                       |
        | Mendukung proses unggah file dan polling log secara real-time. |
        ==================================================================
    --}}

    <div class="space-y-8">
        <!-- Baris 1: Area Proses Import -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Import Sales Invoice Distributor</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Unggah file Excel Anda untuk memulai proses impor data penjualan.</p>
            </div>
            

            <div class="p-6">
                <form wire:submit.prevent="import">
                    <div x-data="{ isUploading: false, progress: 0 }" 
                         x-on:livewire-upload-start="isUploading = true"
                         x-on:livewire-upload-finish="isUploading = false"
                         x-on:livewire-upload-error="isUploading = false"
                         x-on:livewire-upload-progress="progress = $event.detail.progress">

                        <!-- Input File -->
                    <div class="mb-4">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih File Excel</label>
                        <input type="file" id="excel_file" wire:model="excel_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/40 dark:file:text-blue-300 dark:hover:file:bg-blue-900/60 transition duration-150">
                        
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-semibold">Format Nama File:</span> KODECABANG_namafile.xlsx (contoh: DIBDG001_Bandung.xlsx)
                        </p>
                        @error('excel_file') <span class="mt-1 text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Progress Bar Unggah -->
                    <div x-show="isUploading" x-cloak class="w-full bg-gray-200 rounded-full h-2.5 mb-4 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full" :style="`width: ${progress}%`"></div>
                    </div>

                    <!-- Container Tombol (Flexbox) -->
                    <div class="flex flex-row items-center gap-3">
                        <!-- Tombol Submit (Lebih Panjang) -->
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="import"
                                class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-blue-300 disabled:cursor-not-allowed dark:disabled:bg-blue-800 transition duration-150 ease-in-out">
                            <div wire:loading wire:target="import" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <span wire:loading.remove wire:target="import">Mulai Proses Import</span>
                            <span wire:loading wire:target="import">Memproses...</span>
                        </button>

                        <!-- Tombol Config (Ukuran Cukup) -->
                        <a href="{{ route('sales-configs.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-amber-500 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-amber-600 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-amber-500 transition-all shadow-sm shrink-0">
                            <svg class="w-5 h-5 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.983 5.5a1.5 1.5 0 012.034 0l.651.608a1.5 1.5 0 001.554.31l.862-.287a1.5 1.5 0 011.82 1.82l-.287.862a1.5 1.5 0 00.31 1.554l.608.651a1.5 1.5 0 010 2.034l-.608.651a1.5 1.5 0 00-.31 1.554l.287.862a1.5 1.5 0 01-1.82 1.82l-.862-.287a1.5 1.5 0 00-1.554.31l-.651.608a1.5 1.5 0 01-2.034 0l-.651-.608a1.5 1.5 0 00-1.554-.31l-.862.287a1.5 1.5 0 01-1.82-1.82l.287-.862a1.5 1.5 0 00-.31-1.554l-.608-.651a1.5 1.5 0 010-2.034l.608-.651a1.5 1.5 0 00.31-1.554l-.287-.862a1.5 1.5 0 011.82-1.82l.862.287a1.5 1.5 0 001.554-.31l.651-.608z">
                                </path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            Config
                        </a>
                    </div>

                </div>
            </form>
            </div>
        </div>

        <!-- Baris 2: Area Log Proses -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden" 
             wire:poll.2s="syncLog">
             
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">Log Proses</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Status dan hasil dari proses impor.</p>
                    </div>
                    <div x-data="{ status: @entangle('batchStatus') }" class="flex items-center space-x-2">
                        <span class="relative flex h-3 w-3">
                            <span x-show="status === 'processing'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3"
                                  :class="{
                                    'bg-gray-400': !status || status === 'pending',
                                    'bg-sky-500': status === 'processing',
                                    'bg-green-500': status === 'completed',
                                    'bg-red-500': status === 'failed'
                                  }"></span>
                        </span>
                        <span class="text-sm font-medium capitalize text-gray-700 dark:text-gray-300" x-text="status || 'Menunggu'"></span>
                    </div>
                </div>
            </div>

            <div class="h-96 bg-gray-900 p-4 overflow-y-auto font-mono text-sm relative" x-ref="logContainer" x-init="$watch('$wire.logLines', value => { $nextTick(() => $refs.logContainer.scrollTop = $refs.logContainer.scrollHeight) })">
                
                <!-- [BARU] Info Progres di dalam Kotak Log -->
                <div x-data="{ progress: @entangle('processedRows'), total: @entangle('totalRows') }"
                     x-show="total > 0 && ['processing'].includes('{{ $batchStatus }}')"
                     x-cloak
                     class="sticky top-0 -mx-4 -mt-4 mb-4 p-2 bg-gray-800 border-b border-gray-700 text-center z-10">
                    <span class="text-sm font-semibold text-white" x-text="`Memproses: ${progress} dari ${total} baris...`"></span>
                </div>

                @if (empty($logLines))
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-400">Belum ada proses</h3>
                            <p class="mt-1 text-sm text-gray-500">Silakan unggah file untuk memulai.</p>
                        </div>
                    </div>
                @else
                    @foreach ($logLines as $log)
                        <div class="flex items-start mb-2">
                            <span class="mr-3"
                                :class="{
                                    'text-cyan-400': '{{ $log['type'] }}' === 'info',
                                    'text-green-400': '{{ $log['type'] }}' === 'success',
                                    'text-red-400': '{{ $log['type'] }}' === 'error'
                                }">[ {{ strtoupper($log['type']) }} ]</span>
                            <span class="flex-1 text-gray-300">{{ $log['message'] }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

