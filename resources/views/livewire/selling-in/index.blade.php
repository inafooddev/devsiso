<div>
    <x-slot name="title">Import Data Selling In</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">
        
        <!-- Notifikasi Utama -->
        @if (session()->has('message'))
            <x-ui.notif type="success" dismissible class="mb-8">
                {{ session('message') }}
            </x-ui.notif>
        @endif

        <!-- Area Import Langsung -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            
            <!-- Kolom Kiri: Form Upload -->
            <div class="lg:col-span-1 flex flex-col">
                <x-card title="Data Selling In" icon="document-arrow-up" class="h-full flex flex-col">
                    <form wire:submit.prevent="import" class="space-y-5 flex-1 flex flex-col">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-base-content/70 mb-2">Pilih File Excel (.xlsx)</label>
                            <input type="file" wire:model="excel_file" class="file-input file-input-bordered file-input-primary w-full text-sm" />
                            @error('excel_file') <p class="text-error text-xs mt-2 font-medium">{{ $message }}</p> @enderror
                        </div>
                        
                        <x-ui.button type="submit" block wire:loading.attr="disabled" wire:target="import">
                            <span wire:loading.remove wire:target="import">Mulai Proses Import</span>
                            <span wire:loading wire:target="import" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-sm"></span> Sedang Memproses...
                            </span>
                        </x-ui.button>
                    </form>

                    <!-- Progress Bar Dinamis -->
                    @if($totalRows > 0)
                    <div class="pt-5 border-t border-base-300 mt-6">
                        <div class="flex justify-between items-end mb-2 text-xs font-bold uppercase tracking-wider">
                            <span class="text-base-content/60">Progres: <span class="text-base-content">{{ $processedRows }} / {{ $totalRows }}</span></span>
                            <span class="text-primary bg-primary/10 px-2 py-0.5 rounded-md">{{ $batchStatus }}</span>
                        </div>
                        <progress class="progress progress-primary w-full h-2.5" value="{{ $processedRows }}" max="{{ $totalRows }}"></progress>
                    </div>
                    @endif
                </x-card>
            </div>

            <!-- Kolom Kanan: Log Terminal -->
            <div class="lg:col-span-2">
                <div class="mockup-code bg-neutral text-neutral-content w-full h-[380px] flex flex-col shadow-lg border border-neutral-focus">
                    <div class="px-5 pb-4 pt-2 flex-1 overflow-y-auto custom-scrollbar font-mono text-[13px] leading-relaxed" 
                         wire:poll.2s="syncLog" 
                         x-ref="logContainer" 
                         x-init="$watch('$wire.logLines', () => { $nextTick(() => $refs.logContainer.scrollTop = $refs.logContainer.scrollHeight) })">
                        
                        @if(empty($logLines))
                            <pre data-prefix="~" class="text-neutral-content/50"><code>Menunggu berkas diunggah untuk memulai pencatatan...</code></pre>
                        @else
                            @foreach($logLines as $log)
                                @php
                                    $logColor = match($log['type']) {
                                        'error' => 'text-error',
                                        'success' => 'text-success',
                                        default => 'text-info'
                                    };
                                @endphp
                                <pre data-prefix=">" class="{{ $logColor }} whitespace-pre-wrap"><code>[{{ strtoupper($log['type']) }}] {{ $log['message'] }}</code></pre>
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
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: oklch(var(--n) / 0.5); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: oklch(var(--n) / 0.8); }
    </style>
</div>