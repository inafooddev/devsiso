<div>
    <x-slot name="title">Geotagging Reverse</x-slot>

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">
        
        @if (session()->has('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Card Upload -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden mb-8 border border-gray-200">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Upload File Excel
                </h2>
                <div class="text-xs text-white bg-blue-600 px-3 py-1 rounded-full font-bold shadow-sm">
                    Spatial Process
                </div>
            </div>

            <div class="p-6">
                <form wire:submit.prevent="processFile" class="flex flex-col md:flex-row items-end gap-3">

                    <!-- INPUT FILE -->
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            File (.xlsx)
                        </label>

                        <input type="file" wire:model="file"
                            class="block w-full text-sm text-gray-500 
                            file:mr-3 file:py-2 file:px-3 
                            file:rounded-md file:border-0 
                            file:text-xs file:font-semibold 
                            file:bg-blue-100 file:text-blue-700 
                            hover:file:bg-blue-200 
                            border border-gray-300 rounded-md cursor-pointer transition">

                        @error('file')
                            <span class="text-red-500 text-xs mt-1 block">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- BUTTON PROSES -->
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-md shadow flex items-center gap-2 whitespace-nowrap"
                        wire:loading.attr="disabled">

                        <span wire:loading.remove wire:target="processFile">
                            <i class="fas fa-upload"></i> Proses
                        </span>

                        <span wire:loading wire:target="processFile">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>

                    <!-- BUTTON TEMPLATE -->
                    <button type="button"
                        wire:click="downloadTemplate"
                        class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold py-2 px-4 rounded-md shadow flex items-center gap-2 whitespace-nowrap">

                        <i class="fas fa-download"></i>
                        Template
                    </button>

                </form>
            </div>
        </div>

        <!-- Tabel Antrean & Riwayat -->
        <!-- Bagian ini akan otomatis refresh setiap 3 detik untuk mengecek progress bar -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200" wire:poll.3s="loadJobs">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Antrean & Riwayat Proses</h3>
                <span class="text-xs text-gray-500 flex items-center gap-1">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Progress
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu Mulai</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama File</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status & Progress</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($jobs as $job)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-500">{{ $job->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 font-medium text-gray-800 truncate max-w-xs">{{ $job->original_filename }}</td>
                                <td class="px-6 py-4 text-center">
                                    
                                    @if($job->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-clock mr-1.5 text-gray-500"></i> Antrean
                                        </span>
                                        
                                    @elseif($job->status === 'processing')
                                        <div class="flex flex-col items-center w-full min-w-[180px]">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mb-2">
                                                <i class="fas fa-circle-notch fa-spin mr-1.5 text-yellow-600"></i> Sedang Diproses
                                            </span>
                                            
                                            <!-- PROGRESS BAR REALTIME -->
                                            @if(isset($job->progress_data) && isset($job->progress_data['total']) && $job->progress_data['total'] > 0)
                                                <div class="w-full px-2">
                                                    <div class="flex justify-between text-[10px] text-gray-500 mb-1 font-bold">
                                                        <span>{{ number_format($job->progress_data['processed']) }} / {{ number_format($job->progress_data['total']) }} Baris</span>
                                                        <span class="text-blue-600">ETA: {{ $job->progress_data['eta_seconds'] }} dtk</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2 shadow-inner overflow-hidden">
                                                        <div class="bg-yellow-400 h-2 rounded-full transition-all duration-500" 
                                                             style="width: {{ ($job->progress_data['processed'] / $job->progress_data['total']) * 100 }}%">
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-[10px] text-gray-400 italic">Menyiapkan memori Polygon...</span>
                                            @endif
                                        </div>
                                        
                                    @elseif($job->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1.5 text-green-600"></i> Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1.5 text-red-600"></i> Error
                                        </span>
                                    @endif
                                    
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($job->status === 'completed')
                                        <button wire:click="downloadResult('{{ $job->system_filename }}')" class="text-white bg-emerald-500 hover:bg-emerald-600 font-bold px-4 py-1.5 rounded shadow-sm text-xs transition">
                                            <i class="fas fa-download mr-1"></i> Unduh Hasil (.xlsx)
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Menunggu...</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                    <i class="fas fa-inbox text-3xl mb-3 text-gray-300"></i>
                                    <p>Belum ada riwayat pemrosesan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>