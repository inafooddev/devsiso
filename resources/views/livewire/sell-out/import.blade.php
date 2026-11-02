<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Import Sales Invoice Distributor</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
        @include('livewire.sell-out._tabs')
        
        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 min-h-0 pb-6">

                <!-- Left Column: Upload Form & Configuration -->
                <div class="lg:col-span-1 flex flex-col space-y-4">
                    <x-card title="Import Sales Invoice" icon="document-arrow-up" class="h-full flex flex-col">
                        <form wire:submit.prevent="import" class="space-y-4 flex-1 flex flex-col">

                            <div x-data="{ 
                                    isUploading: false, 
                                    progress: 0, 
                                    isDragging: false,
                                    handleDrop(e) {
                                        this.isDragging = false;
                                        if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                                            const fileInput = $refs.fileInput;
                                            fileInput.files = e.dataTransfer.files;
                                            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                                        }
                                    }
                                 }"
                                 x-on:livewire-upload-start="isUploading = true"
                                 x-on:livewire-upload-finish="isUploading = false"
                                 x-on:livewire-upload-error="isUploading = false"
                                 x-on:livewire-upload-progress="progress = $event.detail.progress"
                                 class="flex-1 flex flex-col space-y-4">

                                <!-- 1. File Upload Dropzone (Modern & Drag-and-Drop) -->
                                <div>
                                    <label class="block text-xs md:text-sm font-semibold text-base-content/80 mb-1.5 flex items-center justify-between">
                                        <span>1. File Excel (.xlsx / .xls) <span class="text-error">*</span></span>
                                        @if($excel_file)
                                            <span class="text-[11px] text-success font-medium flex items-center gap-1">
                                                <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Berkas Terpilih
                                            </span>
                                        @endif
                                    </label>

                                    <!-- Hidden Native File Input -->
                                    <input type="file" 
                                           id="excel_file" 
                                           x-ref="fileInput" 
                                           wire:model="excel_file"
                                           accept=".xlsx,.xls"
                                           class="hidden" />

                                    @if($excel_file)
                                        <!-- File Selected Card Preview -->
                                        @php
                                            $filename = $excel_file->getClientOriginalName();
                                            $filesize = $excel_file->getSize();
                                            $sizeFormatted = $filesize > 1048576 
                                                ? number_format($filesize / 1048576, 2) . ' MB' 
                                                : number_format($filesize / 1024, 1) . ' KB';
                                        @endphp
                                        <div class="relative group p-3.5 rounded-xl border-2 border-primary/40 bg-primary/5 hover:border-primary transition-all duration-200">
                                            <div class="flex items-center gap-3">
                                                <!-- Excel Document Icon -->
                                                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20 shadow-xs">
                                                    <x-heroicon-o-document-text class="w-6 h-6" />
                                                </div>

                                                <!-- File Information -->
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-xs md:text-sm font-bold text-base-content truncate" title="{{ $filename }}">
                                                        {{ $filename }}
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-[11px] font-mono text-base-content/60">{{ $sizeFormatted }}</span>
                                                        <span class="text-base-content/30">•</span>
                                                        <span class="badge badge-success badge-xs font-semibold text-white">Siap Diproses</span>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons: Change / Clear -->
                                                <div class="flex items-center gap-1">
                                                    <button type="button" 
                                                            @click="$refs.fileInput.click()"
                                                            class="btn btn-ghost btn-xs text-primary font-medium hover:bg-primary/10"
                                                            title="Ganti berkas dengan file lain">
                                                        Ganti
                                                    </button>
                                                    <button type="button" 
                                                            wire:click="clearSelectedFile"
                                                            class="btn btn-ghost btn-xs btn-circle text-error hover:bg-error/10"
                                                            title="Hapus file terpilih">
                                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Interactive Drag & Drop Area -->
                                        <div @dragover.prevent="isDragging = true"
                                             @dragleave.prevent="isDragging = false"
                                             @drop.prevent="handleDrop($event)"
                                             @click="$refs.fileInput.click()"
                                             :class="{
                                                 'border-primary bg-primary/10 ring-4 ring-primary/20 scale-[1.01]': isDragging,
                                                 'border-base-300 hover:border-primary/50 hover:bg-base-200/50 bg-base-100': !isDragging
                                             }"
                                             class="cursor-pointer border-2 border-dashed rounded-xl p-5 md:p-6 text-center transition-all duration-200 flex flex-col items-center justify-center gap-2.5 group select-none">
                                            
                                            <!-- Cloud / Upload Icon -->
                                            <div :class="isDragging ? 'scale-110 text-primary' : 'text-base-content/40 group-hover:text-primary group-hover:scale-105'"
                                                 class="w-12 h-12 rounded-2xl bg-base-200/80 flex items-center justify-center transition-all duration-200 shadow-xs">
                                                <x-heroicon-o-arrow-up-tray class="w-6 h-6 transition-transform" />
                                            </div>

                                            <div class="space-y-1">
                                                <p class="text-xs md:text-sm font-bold text-base-content">
                                                    <span class="text-primary group-hover:underline">Klik untuk telusuri</span> atau seret file ke sini
                                                </p>
                                                <p class="text-[11px] text-base-content/50">
                                                    Dapat langsung diseret dari WhatsApp Desktop atau Windows Explorer
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-2 pt-1 text-[10px] text-base-content/60 font-mono">
                                                <span class="bg-base-200 px-2 py-0.5 rounded">.XLSX</span>
                                                <span class="bg-base-200 px-2 py-0.5 rounded">.XLS</span>
                                                <span>Maks. 20 MB</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Upload Progress Bar -->
                                    <div x-show="isUploading" x-cloak class="mt-2.5 space-y-1">
                                        <div class="flex justify-between text-xs text-base-content/60 font-medium">
                                            <span class="flex items-center gap-1.5">
                                                <span class="loading loading-spinner loading-xs text-primary"></span>
                                                Mengunggah file ke server...
                                            </span>
                                            <span class="font-mono text-primary font-bold" x-text="progress + '%'"></span>
                                        </div>
                                        <progress class="progress progress-primary w-full h-2" :value="progress" max="100"></progress>
                                    </div>

                                    <p class="mt-2 text-[11px] text-base-content/50 leading-relaxed">
                                        💡 <span class="font-semibold text-base-content/70">Tips:</span> Jika nama file diawali kode cabang (contoh: <code>DIBDG001_sales.xlsx</code>), distributor otomatis terpilih.
                                    </p>

                                    @error('excel_file')
                                        <div class="mt-2.5 alert alert-error p-3 text-xs rounded-xl shadow-sm flex items-start gap-2 text-error-content bg-error">
                                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 mt-0.5 text-white" />
                                            <span class="font-semibold text-white leading-relaxed">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>

                                <!-- 2. Pemilihan Distributor (Wajib) -->
                                <div class="bg-base-200/50 p-3 rounded-xl border border-base-300">
                                    <label class="block text-xs font-bold text-base-content mb-1.5 flex items-center justify-between">
                                        <span class="flex items-center gap-1">
                                            <x-heroicon-o-building-storefront class="w-3.5 h-3.5 text-primary" />
                                            2. Distributor Target <span class="text-error">*</span>
                                        </span>
                                        @if($isDistributorAutoDetected)
                                            <span class="badge badge-success badge-xs font-semibold text-white gap-1" title="Kode terdeteksi otomatis dari nama file">
                                                <x-heroicon-o-check class="w-2.5 h-2.5" /> Otomatis Terdeteksi
                                            </span>
                                        @endif
                                    </label>

                                    @if($selectedDistributorCode)
                                        @php
                                            $selectedDist = $distributors->firstWhere('distributor_code', $selectedDistributorCode);
                                            $selectedName = $selectedDist ? $selectedDist->distributor_name : $selectedDistributorCode;
                                        @endphp
                                        <div class="flex items-center justify-between p-2.5 bg-base-100 border border-primary/30 rounded-lg shadow-sm">
                                            <div class="truncate pr-2">
                                                <div class="text-xs font-bold text-base-content truncate">{{ $selectedName }}</div>
                                                <div class="text-[10px] font-mono text-primary font-semibold">{{ $selectedDistributorCode }}</div>
                                            </div>
                                            <button type="button" 
                                                    wire:click="clearDistributor"
                                                    class="btn btn-ghost btn-xs btn-circle text-error hover:bg-error/10"
                                                    title="Ganti distributor">
                                                <x-heroicon-s-x-mark class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @else
                                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.250ms="distributorSearch"
                                                   @focus="open = true"
                                                   placeholder="Ketik untuk mencari distributor..."
                                                   class="input input-sm w-full bg-base-100 border border-base-300 text-xs text-base-content rounded-lg placeholder:text-base-content/40" />
                                            
                                            <div x-show="open" 
                                                 x-transition
                                                 class="absolute z-30 w-full mt-1.5 bg-base-100 border border-base-300 rounded-xl shadow-2xl max-h-48 overflow-y-auto custom-scrollbar">
                                                @if($distributors->isNotEmpty())
                                                    <div class="py-1 divide-y divide-base-200">
                                                        @foreach($distributors as $dist)
                                                            <div wire:click="selectDistributor('{{ $dist->distributor_code }}')"
                                                                 @click="open = false"
                                                                 class="cursor-pointer px-3 py-2 text-xs text-base-content/80 hover:text-primary hover:bg-primary/10 transition-colors flex justify-between items-center">
                                                                <span class="font-medium truncate">{{ $dist->distributor_name }}</span>
                                                                <span class="font-mono text-[10px] bg-base-200 px-1.5 py-0.5 rounded ml-1">{{ $dist->distributor_code }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="p-3 text-xs text-base-content/50 text-center">Tidak ditemukan distributor.</div>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-base-content/50 mt-1">
                                            Wajib dipilih sebagai acuan konfigurasi kolom dan identitas cabang.
                                        </p>
                                    @endif
                                    @error('selectedDistributorCode')
                                        <p class="mt-1 text-xs text-error font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- 3. Periode Target Transaksi -->
                                <div class="bg-base-200/50 p-3 rounded-xl border border-base-300">
                                    <label class="block text-xs font-bold text-base-content mb-1.5 flex items-center gap-1">
                                        <x-heroicon-o-calendar-days class="w-3.5 h-3.5 text-primary" />
                                        3. Periode Transaksi Penjualan <span class="text-error">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            @php
                                                $months = [
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ];
                                            @endphp
                                            <select wire:model.live="targetMonth" class="select select-bordered select-sm w-full bg-base-100 text-xs">
                                                @foreach($months as $num => $name)
                                                    <option value="{{ $num }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            @php
                                                $currentYear = (int)date('Y');
                                                $years = range($currentYear - 2, $currentYear + 1);
                                            @endphp
                                            <select wire:model.live="targetYear" class="select select-bordered select-sm w-full bg-base-100 text-xs">
                                                @foreach($years as $yr)
                                                    <option value="{{ $yr }}">{{ $yr }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-base-content/50 mt-1.5">
                                        Mencegah tanggal acak/berantakan. Data lama cabang pada periode ini akan diperbarui.
                                    </p>
                                </div>

                                <!-- 4. Action Button (Disabled jika belum siap) -->
                                @php
                                    $isReadyToImport = !empty($excel_file) && !empty($selectedDistributorCode);
                                @endphp
                                <div class="pt-2">
                                    <button type="submit"
                                            @if(!$isReadyToImport) disabled @endif
                                            wire:loading.attr="disabled"
                                            wire:target="import"
                                            class="btn btn-primary w-full shadow-lg shadow-primary/20 normal-case gap-2 {{ !$isReadyToImport ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        <span wire:loading.remove wire:target="import" class="flex items-center gap-2">
                                            <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                            Mulai Proses Import
                                        </span>
                                        <span wire:loading wire:target="import" class="flex items-center gap-2">
                                            <span class="loading loading-spinner loading-sm"></span>
                                            Memvalidasi & Memproses...
                                        </span>
                                    </button>

                                    @if(!$isReadyToImport)
                                        <p class="text-[11px] text-warning text-center mt-1.5 flex items-center justify-center gap-1">
                                            <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5" />
                                            <span>
                                                @if(empty($excel_file) && empty($selectedDistributorCode))
                                                    Pilih file Excel dan distributor terlebih dahulu.
                                                @elseif(empty($excel_file))
                                                    Pilih file Excel terlebih dahulu.
                                                @elseif(empty($selectedDistributorCode))
                                                    Pilih distributor target terlebih dahulu.
                                                @endif
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <!-- Processing Progress (shown after import starts) -->
                        @if($totalRows > 0)
                        <div class="pt-4 border-t border-base-300 mt-4">
                            <div class="flex justify-between items-end mb-2 text-xs font-bold uppercase tracking-wider">
                                <span class="text-base-content/60">Progres: <span class="text-base-content">{{ $processedRows }} / {{ $totalRows }}</span></span>
                                <span class="text-primary bg-primary/10 px-2 py-0.5 rounded-md font-mono">{{ $batchStatus }}</span>
                            </div>
                            <progress class="progress progress-primary w-full h-2.5" value="{{ $processedRows }}" max="{{ $totalRows }}"></progress>
                        </div>
                        @endif
                    </x-card>
                </div>

                <!-- Right Column: Log Terminal & History Table -->
                <div class="lg:col-span-2 flex flex-col space-y-4">
                    
                    <!-- Terminal Section -->
                    <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm">
                        <!-- Log Header -->
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div>
                                <h3 class="text-sm md:text-base font-bold text-base-content">Log Proses Real-Time</h3>
                                <p class="text-xs text-base-content/50 mt-0.5">Pantauan status validasi dan hasil import berkas secara langsung.</p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($batchId && !empty($batchesWithSkippedRows[$batchId]))
                                    <button type="button" 
                                            wire:click="downloadSkippedRows({{ $batchId }})"
                                            class="btn btn-warning btn-xs text-white font-semibold gap-1.5 shadow-xs animate-in fade-in"
                                            title="Unduh daftar baris yang dilewati dalam format Excel (.xlsx)">
                                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                        <span>Unduh Baris Dilewati (.xlsx)</span>
                                    </button>
                                @endif

                                <!-- Status Indicator -->
                                <div x-data="{ status: @entangle('batchStatus') }" class="flex items-center gap-2 bg-base-200 rounded-xl px-3 py-1.5 border border-base-300">
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
                        </div>

                        <!-- Terminal Mockup -->
                        <div class="mockup-code bg-neutral text-neutral-content w-full h-[320px] flex flex-col shadow-inner border border-neutral-focus"
                             wire:poll.2s="syncLog">
                            <div class="px-5 pb-4 pt-2 flex-1 overflow-y-auto custom-scrollbar font-mono text-xs leading-relaxed"
                                 x-ref="logContainer"
                                 x-init="$watch('$wire.logLines', () => { $nextTick(() => $refs.logContainer.scrollTop = $refs.logContainer.scrollHeight) })">

                                <!-- Processing Banner -->
                                @if($totalRows > 0 && $batchStatus === 'processing')
                                    <pre class="sticky top-0 -mx-5 px-5 py-1.5 bg-sky-900/60 border-b border-sky-700/50 text-sky-300 text-center text-xs font-semibold mb-2">
<code>Memproses: {{ $processedRows }} dari {{ $totalRows }} baris...</code></pre>
                                @endif

                                @if(empty($logLines))
                                    <pre data-prefix="~" class="text-neutral-content/40"><code>Menunggu berkas diunggah untuk memulai pencatatan aktivitas...</code></pre>
                                @else
                                    @foreach ($logLines as $log)
                                        @php
                                            $logColor = match($log['type'] ?? 'info') {
                                                'error'   => 'text-error',
                                                'warning' => 'text-warning font-semibold',
                                                'success' => 'text-success',
                                                default   => 'text-info'
                                            };
                                        @endphp
                                        <pre data-prefix=">" class="{{ $logColor }} whitespace-pre-wrap"><code>[{{ strtoupper($log['type'] ?? 'info') }}] {{ $log['message'] }}</code></pre>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Import Terbaru (History Table) -->
                    <div class="bg-base-100 rounded-xl border border-base-300 overflow-hidden shadow-sm">
                        <div class="p-3 bg-base-200/50 border-b border-base-300 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-clock class="w-4 h-4 text-primary" />
                                <h4 class="text-xs md:text-sm font-bold text-base-content">Riwayat Import Terakhir</h4>
                            </div>
                            <span class="text-[11px] text-base-content/60 font-medium">1 Hari Terakhir ({{ $recentBatches->count() }} proses)</span>
                        </div>

                        <div class="overflow-x-auto max-h-72 overflow-y-auto custom-scrollbar">
                            <table class="table table-xs w-full whitespace-nowrap text-xs">
                                <thead class="bg-base-200/95 sticky top-0 z-10 backdrop-blur-xs text-[10px] uppercase text-base-content/60 shadow-xs">
                                    <tr>
                                        <th>Nama Berkas</th>
                                        <th class="text-center">Total Baris</th>
                                        <th class="text-center">Status</th>
                                        <th>Waktu</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-base-200">
                                    @forelse($recentBatches as $batch)
                                        @php
                                            $isPartial = $batch->status === 'completed' && $batch->total_rows > 0 && $batch->processed_rows < $batch->total_rows;
                                            $statusBadge = match($batch->status) {
                                                'completed'  => $isPartial ? 'badge-warning text-white' : 'badge-success text-white',
                                                'failed'     => 'badge-error text-white',
                                                'processing' => 'badge-info text-white',
                                                default      => 'badge-ghost text-base-content/60',
                                            };
                                        @endphp
                                        <tr class="hover:bg-base-200/30 {{ $batchId == $batch->id ? 'bg-primary/5 font-semibold' : '' }}">
                                            <td class="font-medium text-base-content max-w-xs truncate" title="{{ $batch->file_name }}">
                                                {{ $batch->file_name }}
                                            </td>
                                            <td class="text-center font-mono">
                                                {{ $batch->processed_rows }} / {{ $batch->total_rows }}
                                                @if($isPartial)
                                                    <span class="text-[10px] text-warning font-semibold block" title="{{ $batch->total_rows - $batch->processed_rows }} baris dilewati">
                                                        (-{{ $batch->total_rows - $batch->processed_rows }})
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $statusBadge }} badge-xs uppercase font-mono font-bold"
                                                      title="{{ $isPartial ? ($batch->total_rows - $batch->processed_rows) . ' baris dilewati (cek log)' : 'Selesai 100%' }}">
                                                    {{ $isPartial ? 'Catatan' : $batch->status }}
                                                </span>
                                            </td>
                                            <td class="text-base-content/60 font-mono text-[11px]">
                                                {{ $batch->created_at->format('d M Y H:i') }}
                                            </td>
                                            <td class="text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button type="button" 
                                                            wire:click="viewBatchLog({{ $batch->id }})"
                                                            class="btn btn-ghost btn-xs text-primary normal-case gap-1"
                                                            title="Tampilkan log aktivitas batch ini di terminal">
                                                        <x-heroicon-o-command-line class="w-3.5 h-3.5" />
                                                        <span>Lihat Log</span>
                                                    </button>

                                                    @if(!empty($batchesWithSkippedRows[$batch->id]))
                                                        <button type="button" 
                                                                wire:click="downloadSkippedRows({{ $batch->id }})"
                                                                class="btn btn-ghost btn-xs text-warning hover:bg-warning/10 normal-case gap-1 font-semibold"
                                                                title="Unduh baris yang dilewati untuk batch ini dalam format Excel (.xlsx)">
                                                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                                            <span>Unduh Dilewati</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-base-content/40 text-xs">
                                                Belum ada riwayat proses impor.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SELF-HEALING MODAL 1: PEMETAAN HEADER CEPAT -->
    <!-- ========================================== -->
    @if($showQuickHeaderModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-base-100 rounded-2xl shadow-2xl border border-warning/30 max-w-xl w-full p-5 space-y-4 animate-in fade-in zoom-in duration-200">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 bg-warning/10 text-warning rounded-xl shrink-0">
                        <x-heroicon-o-wrench-screwdriver class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-base-content flex items-center gap-2">
                            <span>Penyesuaian Kolom Terdeteksi</span>
                            <span class="badge badge-warning badge-sm font-mono font-bold">{{ $selectedDistributorCode }}</span>
                        </h3>
                        <p class="text-xs text-base-content/60 mt-0.5 leading-relaxed">
                            Nama kolom berikut di konfigurasi tidak ditemukan di file Excel Anda. Silakan cocokkan dengan kolom yang ada di file agar proses dapat langsung dilanjutkan:
                        </p>
                    </div>
                </div>

                <div class="divide-y divide-base-200 bg-base-200/40 rounded-xl p-3 border border-base-300 max-h-72 overflow-y-auto custom-scrollbar space-y-3">
                    @foreach($unresolvedHeaders as $colKey => $fieldData)
                        <div class="pt-2 first:pt-0 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-base-content flex items-center gap-1">
                                    <x-heroicon-o-table-cells class="w-3.5 h-3.5 text-primary" />
                                    {{ $fieldData['label'] }}
                                </span>
                                <span class="text-[11px] text-base-content/50">
                                    Di config: <code class="text-error font-semibold font-mono">{{ $fieldData['config_value'] ?: '(kosong)' }}</code>
                                </span>
                            </div>
                            <div>
                                <select wire:model="unresolvedHeaders.{{ $colKey }}.selected_header"
                                        class="select select-bordered select-sm w-full text-xs bg-base-100">
                                    <option value="">-- Lewati kolom ini (isi nilai kosong) --</option>
                                    @foreach($availableFileHeaders as $optHeader)
                                        <option value="{{ $optHeader }}">{{ $optHeader }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Checkbox Simpan Permanen -->
                <div class="p-2.5 bg-base-200/60 rounded-xl border border-base-300 flex items-start gap-2.5">
                    <input type="checkbox" id="saveHeaderToConfig" wire:model="saveHeaderToConfig" 
                           class="checkbox checkbox-primary checkbox-sm mt-0.5" />
                    <label for="saveHeaderToConfig" class="text-xs text-base-content/80 cursor-pointer select-none leading-relaxed">
                        <span class="font-bold text-base-content">Simpan perubahan ini secara permanen</span> ke konfigurasi distributor <code class="font-bold text-primary">{{ $selectedDistributorCode }}</code> agar impor bulan berikutnya otomatis lancar.
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-2 pt-2 border-t border-base-200">
                    <button type="button" wire:click="cancelQuickModal" class="btn btn-ghost btn-sm text-xs font-semibold">
                        Batal
                    </button>
                    <button type="button" wire:click="applyQuickHeaderMapping" 
                            wire:loading.attr="disabled"
                            class="btn btn-primary btn-sm text-xs font-bold gap-1 text-white shadow-sm shadow-primary/20">
                        <span wire:loading.remove wire:target="applyQuickHeaderMapping">Terapkan & Lanjutkan Impor</span>
                        <span wire:loading wire:target="applyQuickHeaderMapping" class="flex items-center gap-1">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span>Menyimpan & Memproses...</span>
                        </span>
                        <x-heroicon-o-arrow-right class="w-3.5 h-3.5" wire:loading.remove wire:target="applyQuickHeaderMapping" />
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- SELF-HEALING MODAL 2: PEMETAAN SATUAN CEPAT -->
    <!-- ========================================== -->
    @if($showQuickUnitModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-base-100 rounded-2xl shadow-2xl border border-info/30 max-w-lg w-full p-5 space-y-4 animate-in fade-in zoom-in duration-200">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 bg-info/10 text-info rounded-xl shrink-0">
                        <x-heroicon-o-cube class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-base-content flex items-center gap-2">
                            <span>Satuan Baru Terdeteksi</span>
                            <span class="badge badge-info badge-sm font-mono font-bold">{{ $selectedDistributorCode }}</span>
                        </h3>
                        <p class="text-xs text-base-content/60 mt-0.5 leading-relaxed">
                            Ditemukan satuan barang pada file Excel yang belum terdaftar di database Unit Mapping. Silakan pilih satuan standarnya:
                        </p>
                    </div>
                </div>

                <div class="divide-y divide-base-200 bg-base-200/40 rounded-xl p-3 border border-base-300 max-h-64 overflow-y-auto custom-scrollbar space-y-3">
                    @foreach($pendingUnmappedUnits as $rawUnit => $data)
                        <div class="pt-2 first:pt-0 flex items-center justify-between gap-3">
                            <div>
                                <span class="badge badge-neutral font-mono font-bold text-xs uppercase px-2.5 py-1">
                                    {{ $rawUnit }}
                                </span>
                                <span class="text-[11px] text-base-content/50 ml-1.5">satuan di file</span>
                            </div>
                            <div class="w-44">
                                <select wire:model="pendingUnmappedUnits.{{ $rawUnit }}.mapped_unit"
                                        class="select select-bordered select-sm w-full text-xs bg-base-100 font-semibold font-mono">
                                    @foreach($availableStandardUnits as $std)
                                        <option value="{{ $std }}">{{ $std }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Checkbox Simpan Unit -->
                <div class="p-2.5 bg-base-200/60 rounded-xl border border-base-300 flex items-start gap-2.5">
                    <input type="checkbox" id="saveUnitToDatabase" wire:model="saveUnitToDatabase" 
                           class="checkbox checkbox-info checkbox-sm mt-0.5" />
                    <label for="saveUnitToDatabase" class="text-xs text-base-content/80 cursor-pointer select-none leading-relaxed">
                        <span class="font-bold text-base-content">Simpan ke Master Unit Mapping</span> untuk distributor <code class="font-bold text-info">{{ $selectedDistributorCode }}</code> agar tidak perlu dipetakan lagi di masa mendatang.
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-2 pt-2 border-t border-base-200">
                    <button type="button" wire:click="cancelQuickModal" class="btn btn-ghost btn-sm text-xs font-semibold">
                        Batal
                    </button>
                    <button type="button" wire:click="applyQuickUnitMapping" 
                            wire:loading.attr="disabled"
                            class="btn btn-info btn-sm text-xs font-bold gap-1 text-white shadow-sm shadow-info/20">
                        <span wire:loading.remove wire:target="applyQuickUnitMapping">Simpan & Lanjutkan Impor</span>
                        <span wire:loading wire:target="applyQuickUnitMapping" class="flex items-center gap-1">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span>Menyimpan & Memproses...</span>
                        </span>
                        <x-heroicon-o-arrow-right class="w-3.5 h-3.5" wire:loading.remove wire:target="applyQuickUnitMapping" />
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: oklch(var(--n) / 0.5); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: oklch(var(--n) / 0.8); }
    </style>
</div>
