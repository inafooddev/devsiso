<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Jobs - SO Per Cabang</x-slot>

    <x-ui.tab-menu class="!mx-0 !mt-0 !mb-0 rounded-xl">
        <a href="{{ route('jobs.zv-summary-team-elite') }}" wire:navigate class="tab {{ request()->routeIs('jobs.zv-summary-team-elite') ? 'tab-active' : '' }}">Analisa Kunjungan</a>
        <a href="{{ route('jobs.update-sellin-per-cabang') }}" wire:navigate class="tab {{ request()->routeIs('jobs.update-sellin-per-cabang') ? 'tab-active' : '' }}">SI Per Cabang</a>
        <a href="{{ route('jobs.update-salesmans') }}" wire:navigate class="tab {{ request()->routeIs('jobs.update-salesmans') ? 'tab-active' : '' }}">Salesmans</a>
        <a href="{{ route('jobs.so-full-join') }}" wire:navigate class="tab {{ request()->routeIs('jobs.so-full-join') ? 'tab-active' : '' }}">SO Full Join</a>
        <a href="{{ route('jobs.zv-so-per-toko-2026') }}" wire:navigate class="tab {{ request()->routeIs('jobs.zv-so-per-toko-2026') ? 'tab-active' : '' }}">SO Per Toko</a>
        <a href="{{ route('jobs.sellout-per-cabang-sqlserver') }}" wire:navigate class="tab {{ request()->routeIs('jobs.sellout-per-cabang-sqlserver') ? 'tab-active' : '' }}">SO Per Cabang</a>
        <a href="{{ route('jobs.update-ao-percabang') }}" wire:navigate class="tab {{ request()->routeIs('jobs.update-ao-percabang') ? 'tab-active' : '' }}">Ao Per Cabang</a>
        <a href="{{ route('jobs.join-so-eska-non-eksa') }}" wire:navigate class="tab {{ request()->routeIs('jobs.join-so-eska-non-eksa') ? 'tab-active' : '' }}">Join SO Eska</a>
    </x-ui.tab-menu>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            <!-- Header & Actions -->
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full lg:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Pemrosesan Data Sellout Per Cabang (SQL Server)</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Migrasi Data dari SQL Server ke PostgreSQL</p>
                </div>

                <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 md:gap-3 w-full lg:w-auto">
                    <button wire:click="startProcess" wire:loading.attr="disabled" wire:target="startProcess"
                        class="btn btn-sm btn-primary rounded-xl normalcase shadow-sm shadow-primary/20">
                        <span wire:loading.remove wire:target="startProcess" class="flex items-center gap-2">
                            <x-heroicon-o-play class="w-4 h-4" />
                            Mulai Proses
                        </span>
                        <span wire:loading wire:target="startProcess" class="flex items-center gap-2">
                            <span class="loading loading-spinner loading-xs"></span>
                            Memproses...
                        </span>
                    </button>

                    @if (session()->has('error'))
                        <div class="alert alert-error py-2 px-4 rounded-xl text-xs w-full sm:w-auto shadow-sm ml-auto">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4" />
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Log Proses Console -->
            <div class="p-4 md:p-5 bg-base-100 flex-1 flex flex-col overflow-hidden" wire:poll.1500ms="syncLog">
                <div class="flex items-center justify-between mb-4 shrink-0">
                    <h4 class="text-sm font-bold text-base-content/70 flex items-center gap-2">
                        <x-heroicon-o-command-line class="w-4 h-4 text-primary" />
                        Terminal Eksekusi Job ETL
                    </h4>
                    <div class="flex gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-error/40"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-warning/40"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-success/40"></div>
                    </div>
                </div>

                @if(!empty($logLines))
                <div class="mb-4 shrink-0 p-4 bg-base-200/50 rounded-2xl border border-base-300">
                    <div class="flex justify-between items-end mb-2">
                        <div>
                            <span class="text-xs font-bold text-base-content/50 uppercase tracking-wider block mb-1">Status Progress</span>
                            <span class="text-sm font-semibold text-base-content line-clamp-1 truncate max-w-md" title="{{ $this->currentTask }}">{{ $this->currentTask }}</span>
                        </div>
                        <div class="text-right shrink-0 ml-4">
                            <span class="text-lg font-black {{ $batchStatus === 'failed' ? 'text-error' : ($this->progress == 100 ? 'text-success' : 'text-primary') }}">{{ $this->progress }}%</span>
                        </div>
                    </div>
                    <div class="w-full bg-base-300 rounded-full h-2.5 overflow-hidden">
                        <div class="h-full transition-all duration-500 {{ $batchStatus === 'failed' ? 'bg-error' : ($this->progress == 100 ? 'bg-success' : 'bg-primary') }}" style="width: {{ $this->progress }}%"></div>
                    </div>
                </div>
                @endif

                <div class="relative group flex-1 overflow-hidden flex flex-col">
                    <!-- Glass effect overlay -->
                    <div class="absolute -inset-0.5 bg-gradient-to-b from-primary/10 to-transparent rounded-2xl blur opacity-20 transition duration-1000 group-hover:opacity-30"></div>
                    
                    <div class="relative flex-1 w-full bg-slate-950 text-slate-300 rounded-2xl shadow-2xl p-6 font-mono text-[13px] leading-relaxed overflow-y-auto custom-scrollbar border border-white/5" id="terminal-console">
                        @if(empty($logLines))
                            <div class="flex flex-col items-center justify-center h-full text-slate-500 space-y-3">
                                <x-heroicon-o-cpu-chip class="w-12 h-12 opacity-20" />
                                <div class="text-center">
                                    <p class="font-bold text-slate-400">Idle - Menunggu Instruksi</p>
                                    <p class="text-xs mt-1">Silakan klik "Mulai Proses" untuk menarik data dari SQL Server.</p>
                                </div>
                            </div>
                        @else
                            <div class="space-y-1.5">
                                @foreach($logLines as $log)
                                    <div class="flex gap-3 items-start animate-in fade-in slide-in-from-left-2 duration-300">
                                        <span class="text-slate-600 shrink-0 select-none">[{{ now()->format('H:i:s') }}]</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider shrink-0 mt-0.5
                                            {{ ($log['type'] ?? 'info') == 'error' ? 'bg-error/20 text-error border border-error/20' : 
                                               (($log['type'] ?? 'info') == 'success' ? 'bg-success/20 text-success border border-success/20' : 
                                               (($log['type'] ?? 'info') == 'warning' ? 'bg-warning/20 text-warning border border-warning/20' : 
                                               'bg-info/20 text-info border border-info/20')) }}">
                                            {{ strtoupper($log['type'] ?? 'INFO') }}
                                        </span>
                                        <span class="{{ ($log['type'] ?? 'info') == 'error' ? 'text-error/90' : 
                                                     (($log['type'] ?? 'info') == 'success' ? 'text-success/90' : 
                                                     (($log['type'] ?? 'info') == 'warning' ? 'text-warning/90' : 
                                                     'text-slate-300')) }}">
                                            {{ $log['message'] }}
                                        </span>
                                    </div>
                                @endforeach
                                <div class="h-4"></div> <!-- Spacer at bottom -->
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    </div>
</div>

