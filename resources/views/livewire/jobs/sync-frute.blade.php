<div class="py-12" wire:poll.2s="syncLog">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-900 rounded-xl shadow-2xl overflow-hidden border border-gray-700">
            <!-- Header / Status Bar -->
            <div class="bg-gray-800 px-6 py-4 border-b border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                        Sinkronisasi Frute (Rute Kunjungan)
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Mengambil data dari 6 API Blok dan melakukan Truncate & Insert tabel frute</p>
                </div>
                
                <div class="flex items-center gap-4">
                    @if(in_array($batchStatus, ['processing']))
                        <span class="flex items-center gap-2 text-blue-400 bg-blue-400/10 px-3 py-1.5 rounded-full text-sm font-medium border border-blue-400/20">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Sedang Berjalan
                        </span>
                    @elseif($batchStatus === 'completed')
                        <span class="flex items-center gap-2 text-emerald-400 bg-emerald-400/10 px-3 py-1.5 rounded-full text-sm font-medium border border-emerald-400/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Selesai
                        </span>
                    @elseif($batchStatus === 'failed')
                        <span class="flex items-center gap-2 text-rose-400 bg-rose-400/10 px-3 py-1.5 rounded-full text-sm font-medium border border-rose-400/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Gagal
                        </span>
                    @endif
                    
                    <button 
                        wire:click="startProcess" 
                        class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-lg shadow-blue-500/30 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(in_array($batchStatus, ['processing'])) disabled @endif
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Mulai Proses
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="bg-gray-800/50 px-6 py-4 border-b border-gray-700">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-400 font-medium">Progress</span>
                    <span class="text-blue-400 font-bold">{{ $this->progress }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-blue-500 h-2.5 rounded-full transition-all duration-500 ease-out relative" style="width: {{ $this->progress }}%">
                        <div class="absolute top-0 left-0 right-0 bottom-0 bg-white/20 animate-pulse"></div>
                    </div>
                </div>
                <p class="text-gray-500 text-xs mt-2 font-mono flex items-center gap-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    {{ $this->currentTask }}
                </p>
            </div>

            <!-- Terminal Window -->
            <div class="p-6 bg-[#0d1117]">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-800 pb-2">
                    <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span class="ml-2 text-xs font-mono text-gray-500">Console Log - SyncFruteJob</span>
                </div>
                
                <div class="font-mono text-sm space-y-2 h-[400px] overflow-y-auto custom-scrollbar" id="terminal-container">
                    @forelse($logLines as $index => $log)
                        <div class="flex gap-3 hover:bg-white/5 px-2 py-1 rounded transition-colors group">
                            <span class="text-gray-600 select-none">[{{ str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}]</span>
                            @if(($log['type'] ?? '') === 'error')
                                <span class="text-rose-400 font-bold">ERROR</span>
                                <span class="text-rose-300">{{ $log['message'] }}</span>
                            @elseif(($log['type'] ?? '') === 'success')
                                <span class="text-emerald-400 font-bold">SUCCESS</span>
                                <span class="text-emerald-300">{{ $log['message'] }}</span>
                            @elseif(($log['type'] ?? '') === 'warning')
                                <span class="text-amber-400 font-bold">WARN</span>
                                <span class="text-amber-300">{{ $log['message'] }}</span>
                            @else
                                <span class="text-blue-400 font-bold">INFO</span>
                                <span class="text-gray-300">{{ $log['message'] }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="text-gray-500 italic px-2">Belum ada log. Klik 'Mulai Proses' untuk menjalankan sinkronisasi.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', (el, component) => {
                const container = document.getElementById('terminal-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0d1117; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #30363d; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #484f58; 
        }
    </style>
</div>
