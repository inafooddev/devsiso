<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            <!-- Header & Actions -->
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full lg:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Pemrosesan Data ETL Insentif</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Memecah Data Master & Pencapaian Insentif</p>
                </div>

                <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 md:gap-3 w-full lg:w-auto">
                    <x-ui.action-button
                        type="filter"
                        label="Filter Periode"
                        wire:click="$set('isFilterModalOpen', true)"
                    />
                    
                    <button wire:click="startProcess" wire:loading.attr="disabled" wire:target="startProcess"
                        @if(!$hasAppliedFilters) disabled @endif
                        class="btn btn-sm btn-primary rounded-xl normal-case shadow-sm shadow-primary/20 {{ !$hasAppliedFilters ? 'btn-disabled opacity-50' : '' }}">
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

                <div class="relative group flex-1 overflow-hidden flex flex-col">
                    <!-- Glass effect overlay -->
                    <div class="absolute -inset-0.5 bg-gradient-to-b from-primary/10 to-transparent rounded-2xl blur opacity-20 transition duration-1000 group-hover:opacity-30"></div>
                    
                    <div class="relative flex-1 w-full bg-slate-950 text-slate-300 rounded-2xl shadow-2xl p-6 font-mono text-[13px] leading-relaxed overflow-y-auto custom-scrollbar border border-white/5">
                        @if(empty($logLines))
                            <div class="flex flex-col items-center justify-center h-full text-slate-500 space-y-3">
                                <x-heroicon-o-cpu-chip class="w-12 h-12 opacity-20" />
                                <div class="text-center">
                                    <p class="font-bold text-slate-400">Idle - Menunggu Instruksi</p>
                                    <p class="text-xs mt-1">Silakan terapkan filter dan klik "Mulai Proses" untuk memecah data insentif.</p>
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

    <!-- Modal Filter -->
    <div x-data="{ open: @entangle('isFilterModalOpen') }" x-show="open" x-cloak wire:ignore.self class="fixed z-50 inset-0 flex items-center justify-center">
        <!-- Backdrop Blur -->
        <div x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-200 rounded-2xl shadow-2xl ring-1 ring-base-300 w-full max-w-md mx-4 overflow-visible">

            <form wire:submit.prevent="applyFilters">
                <div class="px-6 pt-6 pb-4">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-bold text-base-content flex items-center gap-2">
                            <x-heroicon-o-funnel class="w-5 h-5 text-primary" />
                            Filter Data Proses
                        </h3>
                        <button @click="open = false" type="button" class="btn btn-ghost btn-sm btn-square rounded-xl">
                            <x-heroicon-o-x-mark class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Periode (Month & Year) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="monthFilter" class="block text-sm font-medium text-base-content/70 mb-1.5">Bulan</label>
                                <select wire:model.defer="monthFilter" id="monthFilter" 
                                    class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="yearFilter" class="block text-sm font-medium text-base-content/70 mb-1.5">Tahun</label>
                                <select wire:model.defer="yearFilter" id="yearFilter" 
                                    class="select select-bordered select-sm w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50">
                                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-base-300 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <button @click="open = false" type="button" class="btn btn-ghost rounded-xl normal-case sm:mr-auto">Batal</button>
                    <button wire:click="resetFilters" type="button" class="btn btn-ghost border border-base-300 hover:bg-base-300 rounded-xl normal-case">Reset</button>
                    <button type="submit" class="btn btn-primary rounded-xl normal-case shadow-sm shadow-primary/20">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
