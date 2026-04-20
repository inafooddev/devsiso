<div>
    <x-slot name="title">Import Sales Invoice Distributor</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

            <!-- Left Column: Upload Form -->
            <div class="lg:col-span-1 flex flex-col">
                <x-card title="Import Sales Invoice" icon="document-arrow-up" class="h-full flex flex-col">
                    <form wire:submit.prevent="import" class="space-y-5 flex-1 flex flex-col">

                        <div x-data="{ isUploading: false, progress: 0 }"
                             x-on:livewire-upload-start="isUploading = true"
                             x-on:livewire-upload-finish="isUploading = false"
                             x-on:livewire-upload-error="isUploading = false"
                             x-on:livewire-upload-progress="progress = $event.detail.progress"
                             class="flex-1 flex flex-col space-y-5">

                            <!-- File Input -->
                            <div>
                                <label for="excel_file" class="block text-sm font-medium text-base-content/70 mb-2">
                                    Pilih File Excel (.xlsx)
                                </label>
                                <input type="file" id="excel_file" wire:model="excel_file"
                                       class="file-input file-input-bordered file-input-primary w-full text-sm" />
                                <p class="mt-2 text-xs text-base-content/50 leading-relaxed">
                                    <span class="font-semibold text-base-content/70">Format Nama File:</span> KODECABANG_namafile.xlsx
                                    <span class="text-base-content/40">(contoh: DIBDG001_Bandung.xlsx)</span>
                                </p>
                                @error('excel_file')
                                    <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Upload Progress Bar -->
                            <div x-show="isUploading" x-cloak class="space-y-1">
                                <div class="flex justify-between text-xs text-base-content/60 font-medium">
                                    <span>Mengunggah file...</span>
                                    <span x-text="progress + '%'"></span>
                                </div>
                                <progress class="progress progress-primary w-full h-2" :value="progress" max="100"></progress>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3 pt-2">
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        wire:target="import"
                                        class="btn btn-primary flex-1 rounded-xl shadow-lg shadow-primary/20 normal-case">
                                    <span wire:loading.remove wire:target="import" class="flex items-center gap-2">
                                        <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                                        Mulai Proses Import
                                    </span>
                                    <span wire:loading wire:target="import" class="flex items-center gap-2">
                                        <span class="loading loading-spinner loading-sm"></span>
                                        Memproses...
                                    </span>
                                </button>

                                <a href="{{ route('sales-configs.index') }}"
                                   class="btn btn-ghost border border-base-300 hover:bg-base-200 hover:text-base-content rounded-xl normal-case shrink-0"
                                   title="Kelola Config Distributor">
                                    <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-base-content/60" />
                                    Config
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Processing Progress (shown after import starts) -->
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

            <!-- Right Column: Log Terminal -->
            <div class="lg:col-span-2">
                <!-- Log Header -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <div>
                        <h3 class="text-base font-bold text-base-content">Log Proses</h3>
                        <p class="text-xs text-base-content/50 mt-0.5">Status dan hasil dari proses impor secara real-time.</p>
                    </div>

                    <!-- Status Indicator -->
                    <div x-data="{ status: @entangle('batchStatus') }" class="flex items-center gap-2.5 bg-base-200 rounded-xl px-4 py-2 border border-base-300">
                        <span class="relative flex h-2.5 w-2.5">
                            <span x-show="status === 'processing'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                                  :class="{
                                    'bg-base-content/20': !status || status === 'pending',
                                    'bg-sky-500': status === 'processing',
                                    'bg-success': status === 'completed',
                                    'bg-error': status === 'failed'
                                  }"></span>
                        </span>
                        <span class="text-xs font-semibold capitalize text-base-content/70" x-text="status || 'Menunggu'"></span>
                    </div>
                </div>

                <!-- Terminal mockup -->
                <div class="mockup-code bg-neutral text-neutral-content w-full h-[400px] flex flex-col shadow-lg border border-neutral-focus"
                     wire:poll.2s="syncLog">
                    <div class="px-5 pb-4 pt-2 flex-1 overflow-y-auto custom-scrollbar font-mono text-[13px] leading-relaxed"
                         x-ref="logContainer"
                         x-init="$watch('$wire.logLines', () => { $nextTick(() => $refs.logContainer.scrollTop = $refs.logContainer.scrollHeight) })">

                        <!-- Processing Banner -->
                        @if($totalRows > 0 && $batchStatus === 'processing')
                            <pre class="sticky top-0 -mx-5 px-5 py-2 bg-sky-900/50 border-b border-sky-700/50 text-sky-300 text-center text-xs font-semibold mb-3">
<code>Memproses: {{ $processedRows }} dari {{ $totalRows }} baris...</code></pre>
                        @endif

                        @if(empty($logLines))
                            <pre data-prefix="~" class="text-neutral-content/50"><code>Menunggu berkas diunggah untuk memulai pencatatan...</code></pre>
                        @else
                            @foreach ($logLines as $log)
                                @php
                                    $logColor = match($log['type']) {
                                        'error'   => 'text-error',
                                        'success' => 'text-success',
                                        default   => 'text-info'
                                    };
                                @endphp
                                <pre data-prefix=">" class="{{ $logColor }} whitespace-pre-wrap"><code>[{{ strtoupper($log['type']) }}] {{ $log['message'] }}</code></pre>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: oklch(var(--n) / 0.5); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: oklch(var(--n) / 0.8); }
    </style>
</div>

