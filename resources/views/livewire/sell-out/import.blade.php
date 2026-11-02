<div class="flex-1 flex flex-col w-full h-full min-h-0"
     @if($isQueueRunning && $hasPendingQueueItems) wire:poll.1500ms="processNextQueueItem" @endif>
    <x-slot name="title">Import Sales Invoice Distributor</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
        @include('livewire.sell-out._tabs')
        
        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 min-h-0 pb-6">

                <!-- Left Column: Upload Form & Configuration -->
                <div class="lg:col-span-1 flex flex-col space-y-4">
                    <x-card title="Import Sales Invoice" icon="document-arrow-up" class="h-full flex flex-col">
                        <form wire:submit.prevent="startQueueImport" class="space-y-4 flex-1 flex flex-col">

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

                                <!-- 1. Periode Target Transaksi -->
                                <div class="bg-base-200/50 p-3 rounded-xl border border-base-300">
                                    <label class="block text-xs font-bold text-base-content mb-1.5 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <x-heroicon-o-calendar-days class="w-4 h-4 text-primary" />
                                            1. Periode Transaksi Penjualan <span class="text-error">*</span>
                                        </span>
                                        <span class="text-[10px] text-base-content/50">Acuan untuk seluruh berkas</span>
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
                                        Mencegah tanggal acak. Data lama cabang pada periode ini akan diperbarui secara bersih.
                                    </p>
                                </div>

                                <!-- 2. Berkas Excel (Multi-Upload Support) -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs md:text-sm font-semibold text-base-content/80">
                                            <span>2. Berkas Excel (.xlsx / .xls) <span class="text-error">*</span></span>
                                        </label>
                                        @if(!empty($stagedFiles))
                                            <span class="badge badge-primary badge-xs font-mono font-bold">
                                                {{ count($stagedFiles) }} Berkas Terpilih
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Hidden Native Multiple File Input -->
                                    <input type="file" 
                                           id="excel_files" 
                                           x-ref="fileInput" 
                                           wire:model="excel_files"
                                           accept=".xlsx,.xls"
                                           multiple
                                           class="hidden" />

                                    @if(!empty($stagedFiles))
                                        <!-- Staged Files Queue Cards -->
                                        <div class="space-y-2.5 bg-base-200/50 p-3 rounded-xl border border-base-300">
                                            <div class="flex items-center justify-between text-xs pb-1 border-b border-base-300">
                                                <span class="font-bold text-base-content flex items-center gap-1.5">
                                                    <x-heroicon-o-queue-list class="w-4 h-4 text-primary" />
                                                    Daftar Berkas Terpilih
                                                </span>
                                                <button type="button" 
                                                        wire:click="clearAllStagedFiles"
                                                        class="text-[11px] text-error hover:underline font-semibold"
                                                        title="Hapus semua berkas dari antrian">
                                                    Hapus Semua
                                                </button>
                                            </div>

                                            @php
                                                $unassignedCount = collect($stagedFiles)->where('distributor_code', '')->count();
                                            @endphp

                                            @if($unassignedCount > 0)
                                                <!-- Bulk Assign Toolbar -->
                                                <div class="p-2 bg-warning/10 border border-warning/30 rounded-lg flex items-center justify-between gap-1.5 flex-wrap">
                                                    <div class="text-[11px] text-warning-content font-medium flex items-center gap-1">
                                                        <x-heroicon-o-sparkles class="w-3.5 h-3.5 text-warning shrink-0" />
                                                        <span>Terapkan 1 cabang ke <strong>{{ $unassignedCount }} berkas</strong> tanpa kode:</span>
                                                    </div>
                                                    <div class="flex items-center gap-1 w-full sm:w-auto">
                                                        <select wire:model="bulkDistributorCode" class="select select-bordered select-xs text-[11px] bg-base-100 flex-1 sm:w-44">
                                                            <option value="">-- Pilih Cabang --</option>
                                                            @foreach($configuredDistributors as $cDist)
                                                                <option value="{{ $cDist->distributor_code }}">{{ $cDist->distributor_name }} ({{ $cDist->distributor_code }})</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" wire:click="applyBulkDistributor" class="btn btn-warning btn-xs text-warning-content font-bold shrink-0">
                                                            Terapkan
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="space-y-2 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                                                @foreach($stagedFiles as $fId => $staged)
                                                    @php
                                                        $isEditing = !empty($staged['is_editing']);
                                                        $hasDist = !empty($staged['distributor_code']);
                                                        $hasCfg = !empty($staged['has_config']);
                                                        $isReady = $hasDist && $hasCfg;
                                                    @endphp
                                                    <div class="p-2.5 bg-base-100 rounded-lg border {{ !$isReady ? 'border-warning/60 bg-warning/5' : 'border-base-300' }} shadow-xs flex flex-col gap-1.5 transition-all">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                <x-heroicon-o-document-text class="w-4 h-4 text-emerald-600 shrink-0" />
                                                                <span class="text-xs font-bold text-base-content truncate" title="{{ $staged['original_name'] }}">
                                                                    {{ $staged['original_name'] }}
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center gap-1 shrink-0">
                                                                <span class="text-[10px] text-base-content/50 font-mono">{{ $staged['size_formatted'] }}</span>
                                                                <button type="button" 
                                                                        wire:click="removeStagedFile('{{ $fId }}')"
                                                                        class="btn btn-ghost btn-xs btn-circle text-base-content/40 hover:text-error"
                                                                        title="Hapus berkas ini">
                                                                    <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Distributor row per file -->
                                                        <div class="pt-1 border-t border-base-200 text-xs">
                                                            @if($isReady && !$isEditing)
                                                                <div class="flex items-center justify-between gap-1.5">
                                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                                        <span class="badge badge-success badge-xs text-white font-mono font-bold shrink-0">
                                                                            {{ $staged['distributor_code'] }}
                                                                        </span>
                                                                        <span class="text-[11px] font-bold text-base-content truncate" title="{{ $staged['distributor_name'] }}">
                                                                            {{ $staged['distributor_name'] }}
                                                                        </span>
                                                                        @if(!empty($staged['is_auto_detected']))
                                                                            <span class="badge badge-ghost badge-xs text-success font-semibold shrink-0" title="Terdeteksi otomatis dari nama file">
                                                                                Otomatis ✓
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    <button type="button" 
                                                                            wire:click="toggleEditDistributor('{{ $fId }}')"
                                                                            class="text-[10px] text-primary hover:underline font-semibold shrink-0">
                                                                        Ganti
                                                                    </button>
                                                                </div>
                                                            @else
                                                                <div class="flex items-center gap-1.5">
                                                                    <select wire:change="setFileDistributor('{{ $fId }}', $event.target.value)" 
                                                                            class="select select-bordered select-xs text-[11px] bg-base-100 {{ !$hasDist ? 'border-warning text-warning-content font-bold' : '' }} flex-1">
                                                                        <option value="">-- Pilih Cabang Distributor --</option>
                                                                        @foreach($configuredDistributors as $cDist)
                                                                            <option value="{{ $cDist->distributor_code }}" @if($staged['distributor_code'] === $cDist->distributor_code) selected @endif>
                                                                                {{ $cDist->distributor_name }} ({{ $cDist->distributor_code }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @if($isEditing)
                                                                        <button type="button" 
                                                                                wire:click="toggleEditDistributor('{{ $fId }}')"
                                                                                class="btn btn-ghost btn-xs text-base-content/50">
                                                                            Batal
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <!-- Tambah Berkas Button -->
                                            <button type="button" 
                                                    @click="$refs.fileInput.click()"
                                                    class="btn btn-outline btn-primary btn-xs w-full normal-case gap-1.5">
                                                <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                                Tambah Berkas Lain
                                            </button>
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
                                                    Bisa pilih / seret banyak file sekaligus dari WhatsApp atau Explorer
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-2 pt-1 text-[10px] text-base-content/60 font-mono">
                                                <span class="bg-base-200 px-2 py-0.5 rounded">.XLSX</span>
                                                <span class="bg-base-200 px-2 py-0.5 rounded">.XLS</span>
                                                <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded font-bold">Multi-Upload</span>
                                                <span>Maks. 20 MB/file</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Upload Progress Bar -->
                                    <div x-show="isUploading" x-cloak class="mt-2.5 space-y-1">
                                        <div class="flex justify-between text-xs text-base-content/60 font-medium">
                                            <span class="flex items-center gap-1.5">
                                                <span class="loading loading-spinner loading-xs text-primary"></span>
                                                Mengunggah berkas ke server...
                                            </span>
                                            <span class="font-mono text-primary font-bold" x-text="progress + '%'"></span>
                                        </div>
                                        <progress class="progress progress-primary w-full h-2" :value="progress" max="100"></progress>
                                    </div>

                                    <p class="mt-2 text-[11px] text-base-content/50 leading-relaxed">
                                        💡 <span class="font-semibold text-base-content/70">Tips:</span> Nama file dengan awalan kode cabang (contoh: <code>DICJR001_sales.xls</code>) otomatis terpetakan ke distributor terkait.
                                    </p>

                                    @error('excel_files')
                                        <div class="mt-2.5 alert alert-error p-3 text-xs rounded-xl shadow-sm flex items-start gap-2 text-error-content bg-error">
                                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 mt-0.5 text-white" />
                                            <span class="font-semibold text-white leading-relaxed">{{ $message }}</span>
                                        </div>
                                    @enderror
                                    @error('queue')
                                        <div class="mt-2.5 alert alert-error p-3 text-xs rounded-xl shadow-sm flex items-start gap-2 text-error-content bg-error">
                                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 mt-0.5 text-white" />
                                            <span class="font-semibold text-white leading-relaxed">{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>

                                <!-- 3. Action Button: Mulai Import Antrian -->
                                @php
                                    $stagedCount = count($stagedFiles);
                                    $allReady = $stagedCount > 0 && collect($stagedFiles)->every(fn($f) => !empty($f['distributor_code']) && !empty($f['has_config']));
                                @endphp
                                <div class="pt-2">
                                    <button type="submit"
                                            @if(!$allReady || $isQueueRunning) disabled @endif
                                            wire:loading.attr="disabled"
                                            wire:target="startQueueImport"
                                            class="btn btn-primary w-full shadow-lg shadow-primary/20 normal-case gap-2 {{ (!$allReady || $isQueueRunning) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        @if($isQueueRunning)
                                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                                            <span>Sedang Memproses Antrian...</span>
                                        @else
                                            <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                            <span>Mulai Import Antrian {{ $stagedCount > 0 ? "({$stagedCount} Berkas)" : '' }}</span>
                                        @endif
                                    </button>

                                    @if(!$allReady && $stagedCount > 0)
                                        <p class="text-[11px] text-warning text-center mt-1.5 flex items-center justify-center gap-1">
                                            <x-heroicon-o-exclamation-triangle class="w-3.5 h-3.5 shrink-0" />
                                            <span>Pastikan setiap berkas memiliki distributor target yang terkonfigurasi.</span>
                                        </p>
                                    @elseif($stagedCount === 0 && !$isQueueRunning)
                                        <p class="text-[11px] text-base-content/50 text-center mt-1.5 flex items-center justify-center gap-1">
                                            <x-heroicon-o-information-circle class="w-3.5 h-3.5 shrink-0" />
                                            <span>Pilih atau seret berkas Excel untuk memulai.</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <!-- Processing Progress (shown after import starts) -->
                        @if($totalRows > 0)
                        <div class="pt-4 border-t border-base-300 mt-4">
                            <div class="flex justify-between items-end mb-2 text-xs font-bold uppercase tracking-wider">
                                <span class="text-base-content/60">Progres Berkas Aktif: <span class="text-base-content">{{ $processedRows }} / {{ $totalRows }}</span></span>
                                <span class="text-primary bg-primary/10 px-2 py-0.5 rounded-md font-mono">{{ $batchStatus }}</span>
                            </div>
                            <progress class="progress progress-primary w-full h-2.5" value="{{ $processedRows }}" max="{{ $totalRows }}"></progress>
                        </div>
                        @endif
                    </x-card>
                </div>

                <!-- Right Column: Log Terminal & History Table -->
                <div class="lg:col-span-2 flex flex-col space-y-4">

                    <!-- Active Queue Tracker Card (Multi-Import Queue) -->
                    @if(!empty($queueBatches))
                        @php
                            $completedCount = collect($queueBatches)->whereIn('status', ['completed', 'failed'])->count();
                            $totalQueue = count($queueBatches);
                        @endphp
                        <div class="bg-base-100 rounded-xl border border-primary/30 p-3.5 shadow-sm space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="relative flex h-3 w-3">
                                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $isQueueRunning ? 'bg-primary' : 'bg-success' }}"></span>
                                    </span>
                                    <h4 class="text-xs md:text-sm font-bold text-base-content">
                                        {{ $isQueueRunning ? 'Antrian Multi-Import Sedang Berjalan' : 'Seluruh Antrian Selesai Diproses' }}
                                    </h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-primary">{{ $completedCount }} / {{ $totalQueue }} Selesai</span>
                                    @if(!$isQueueRunning)
                                        <button type="button" wire:click="dismissQueueTracker" class="btn btn-ghost btn-xs btn-circle text-base-content/40 hover:text-base-content" title="Tutup tracker antrian">
                                            <x-heroicon-s-x-mark class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <progress class="progress progress-primary w-full h-2" value="{{ $completedCount }}" max="{{ $totalQueue }}"></progress>

                            <!-- Clickable Distributor Pills in Queue -->
                            <div class="flex flex-wrap gap-1.5 pt-0.5">
                                @foreach($queueBatches as $qId => $qItem)
                                    @php
                                        $isActive = $batchId == $qId;
                                        $pillClass = match($qItem['status']) {
                                            'processing' => 'border-primary bg-primary/10 text-primary font-bold shadow-xs',
                                            'completed'  => 'border-success/40 bg-success/10 text-success font-semibold',
                                            'failed'     => 'border-error/40 bg-error/10 text-error font-semibold',
                                            default      => 'border-base-300 bg-base-200/80 text-base-content/60 font-medium',
                                        };
                                    @endphp
                                    <button type="button" 
                                            wire:click="viewBatchLog({{ $qId }})"
                                            class="btn btn-xs rounded-lg border {{ $pillClass }} {{ $isActive ? 'ring-2 ring-primary ring-offset-1' : '' }} normal-case gap-1.5 text-xs"
                                            title="Klik untuk melihat log: {{ $qItem['distributor_name'] }}">
                                        @if($qItem['status'] === 'processing')
                                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-primary" />
                                        @elseif($qItem['status'] === 'completed')
                                            <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-success" />
                                        @elseif($qItem['status'] === 'failed')
                                            <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-error" />
                                        @else
                                            <x-heroicon-o-clock class="w-3.5 h-3.5 text-base-content/40" />
                                        @endif
                                        <span class="font-mono">{{ $qItem['distributor_code'] }}</span>
                                        <span class="truncate max-w-[110px] hidden sm:inline">{{ $qItem['distributor_name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Terminal Section -->
                    <div class="bg-base-100 rounded-xl border border-base-300 p-4 shadow-sm">
                        <!-- Log Header -->
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div>
                                <h3 class="text-sm md:text-base font-bold text-base-content">Log Proses Real-Time</h3>
                                <p class="text-xs text-base-content/50 mt-0.5">Pantauan status validasi dan hasil import berkas secara langsung per distributor.</p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($batchId && !empty($batchesWithSkippedRows[$batchId]))
                                    <button type="button" 
                                            wire:click="downloadSkippedRows({{ $batchId }})"
                                            class="btn btn-warning btn-xs text-white font-semibold gap-1.5 shadow-xs"
                                            title="Unduh daftar baris yang dilewati dalam format Excel (.xlsx)">
                                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                        <span>Unduh Baris Dilewati (.xlsx)</span>
                                    </button>
                                @endif

                                <!-- Status Indicator -->
                                <div x-data="{ status: @entangle('batchStatus') }" class="flex items-center gap-2 bg-base-200 rounded-xl px-3 py-1.5 border border-base-300">
                                    <span class="relative flex h-2.5 w-2.5">
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
                             @if($batchStatus === 'processing') wire:poll.2s="syncLog" @endif>
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
                            wire:target="applyQuickHeaderMapping"
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
                            wire:target="applyQuickUnitMapping"
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
