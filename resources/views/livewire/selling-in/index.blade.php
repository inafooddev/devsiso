<div>
    <x-slot name="title">Import Data Selling In</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">
        
        <!-- Notifikasi Utama -->
        @if (session()->has('message'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="mb-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 bg-emerald-100 rounded-full p-1">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition-colors p-1 hover:bg-emerald-100 rounded-lg">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        @endif

        <!-- Area Import Langsung (Bukan Modal) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            
            <!-- Kolom Kiri: Form Upload -->
            <div class="lg:col-span-1 flex flex-col">
                <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl overflow-hidden border border-slate-100 flex flex-col h-full">
                    
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/80">
                        <h3 class="text-lg font-semibold text-slate-800 flex items-center">
                            <div class="p-1.5 bg-blue-100 text-blue-600 rounded-lg mr-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                            </div>
                            Data Selling In
                        </h3>
                    </div>
                    
                    <div class="p-6 flex-1 flex flex-col space-y-6">
                        <form wire:submit.prevent="import" class="space-y-5 flex-1 flex flex-col">
                            
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Pilih File Excel (.xlsx)</label>
                                <!-- Modern File Input -->
                                <input type="file" wire:model="excel_file" 
                                       class="block w-full text-sm text-slate-500 border border-slate-200 rounded-xl bg-slate-50 cursor-pointer transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                              file:cursor-pointer file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:border-r file:border-slate-200 file:text-sm file:font-semibold file:bg-white file:text-blue-600 hover:file:bg-blue-50">
                                @error('excel_file') <p class="text-rose-500 text-xs mt-2 font-medium">{{ $message }}</p> @enderror
                            </div>
                            
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    wire:target="import"
                                    class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-semibold rounded-xl shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 disabled:opacity-70 disabled:cursor-not-allowed transition-all duration-200">
                                <div wire:loading wire:target="import" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <span wire:loading.remove wire:target="import">Mulai Proses Import</span>
                                <span wire:loading wire:target="import">Sedang Memproses...</span>
                            </button>
                        </form>

                        <!-- Progress Bar Dinamis -->
                        @if($totalRows > 0)
                        <div class="pt-5 border-t border-slate-100">
                            <div class="flex justify-between items-end mb-2 text-xs font-bold uppercase tracking-wider">
                                <span class="text-slate-500">Progres: <span class="text-slate-700">{{ $processedRows }} / {{ $totalRows }}</span></span>
                                <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">{{ $batchStatus }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full transition-all duration-500 relative" 
                                     style="width: {{ $totalRows > 0 ? ($processedRows / $totalRows) * 100 : 0 }}%">
                                     <!-- Subtle shine effect on progress bar -->
                                     <div class="absolute top-0 left-0 bottom-0 right-0 bg-gradient-to-r from-transparent via-white/30 to-transparent w-full"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Log Terminal -->
            <div class="lg:col-span-2">
                <div class="bg-[#0f172a] shadow-[0_8px_30px_rgb(0,0,0,0.1)] rounded-2xl overflow-hidden flex flex-col border border-slate-800" style="height: 380px;">
                    
                    <!-- Mac-like Terminal Header -->
                    <div class="px-5 py-3 border-b border-slate-800 bg-[#1e293b] flex justify-between items-center">
                        <div class="flex space-x-2">
                            <div class="w-3 h-3 rounded-full bg-rose-500 hover:bg-rose-400 transition-colors cursor-default"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500 hover:bg-amber-400 transition-colors cursor-default"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-500 hover:bg-emerald-400 transition-colors cursor-default"></div>
                        </div>
                        <h5 class="text-slate-400 uppercase text-[11px] font-bold tracking-widest flex items-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.6)]"></span>
                            Terminal Log
                        </h5>
                        <!-- Spacer to balance the dots -->
                        <div class="w-10"></div> 
                    </div>
                    
                    <!-- Log Content -->
                    <div class="flex-1 p-5 overflow-y-auto font-mono text-[13px] leading-relaxed space-y-1.5 custom-scrollbar bg-[#0B1120]" 
                         wire:poll.2s="syncLog" 
                         x-ref="logContainer" 
                         x-init="$watch('$wire.logLines', () => { $nextTick(() => $refs.logContainer.scrollTop = $refs.logContainer.scrollHeight) })">
                        
                        @if(empty($logLines))
                            <div class="text-slate-500 italic flex items-center mt-2">
                                <svg class="w-4 h-4 mr-2 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menunggu berkas diunggah untuk memulai pencatatan...
                            </div>
                        @else
                            @foreach($logLines as $log)
                                <div class="flex items-start">
                                    <span class="mr-3 shrink-0 font-bold {{ $log['type'] === 'error' ? 'text-rose-500' : ($log['type'] === 'success' ? 'text-emerald-400' : 'text-cyan-400') }}">
                                        [{{ strtoupper($log['type']) }}]
                                    </span>
                                    <span class="text-slate-300">{{ $log['message'] }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Tabel Data (History/List) -->
        
    </div>

    <!-- Custom Scrollbar styling only for terminal to match dark theme -->
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0B1120; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</div>