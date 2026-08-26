<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Import Raw Selling-In</x-slot>

    <!-- Top Stepper -->
    <div class="mb-6 flex justify-center w-full px-4">
        <ul class="steps steps-horizontal w-full max-w-3xl">
            <li class="step {{ $currentStep >= 1 ? 'step-primary font-bold' : '' }}">Import Raw</li>
            <li class="step {{ $currentStep >= 2 ? 'step-primary font-bold' : '' }}">Validasi Data</li>
            <li class="step {{ $currentStep >= 3 ? 'step-primary font-bold' : '' }}">Sinkronisasi (Clean)</li>
        </ul>
    </div>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
        
        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
            
            <!-- STEP 1: IMPORT RAW -->
            @if($currentStep == 1)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 min-h-0 pb-6">
                <!-- Left Column: Upload Form -->
                <div class="lg:col-span-1 flex flex-col">
                    <x-card title="Import Data Selling-In" icon="document-arrow-up" class="h-full flex flex-col relative overflow-hidden">
                        @if($batchStatus === 'completed' && $processedRows > 0)
                            <div class="absolute inset-0 bg-base-100/95 backdrop-blur-sm flex flex-col items-center justify-center p-6 z-10 text-center">
                                <div class="w-16 h-16 bg-success/20 text-success rounded-full flex items-center justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Import Berhasil!</h3>
                                <p class="text-sm text-base-content/70 mb-6">File Excel telah berhasil diproses. Sebanyak {{ number_format($totalRows, 0, ',', '.') }} baris telah diimpor.</p>
                                <button type="button" wire:click="setStep(2)" class="btn btn-primary shadow-lg w-full">
                                    Lanjut ke Validasi Data
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </button>
                                <button type="button" wire:click="$set('batchStatus', null)" class="btn btn-ghost btn-sm mt-3 text-xs">Upload File Lain</button>
                            </div>
                        @endif

                        <form wire:submit.prevent="import" class="space-y-5 flex-1 flex flex-col">
                            <div x-data="{ isUploading: false, progress: 0 }"
                                 x-on:livewire-upload-start="isUploading = true"
                                 x-on:livewire-upload-finish="isUploading = false"
                                 x-on:livewire-upload-error="isUploading = false"
                                 x-on:livewire-upload-progress="progress = $event.detail.progress"
                                 class="flex-1 flex flex-col space-y-5">

                                <!-- Periode Input -->
                                <div>
                                    <label for="selectedMonth" class="block text-sm font-medium text-base-content/70 mb-2">
                                        Periode Import (Bulan & Tahun)
                                    </label>
                                    <input type="month" id="selectedMonth" wire:model="selectedMonth"
                                           class="input input-bordered input-primary w-full text-sm" required />
                                    @error('selectedMonth')
                                        <p class="mt-2 text-xs text-error font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- File Input -->
                                <div>
                                    <label for="excel_file" class="block text-sm font-medium text-base-content/70 mb-2">
                                        Pilih File Excel (.xls, .xlsx)
                                    </label>
                                    <input type="file" id="excel_file" wire:model="excel_file"
                                           class="file-input file-input-bordered file-input-primary w-full text-sm" />
                                    
                                    <div class="mt-3 p-3 bg-base-200 rounded-lg text-xs text-base-content/70 leading-relaxed border border-base-300">
                                        <p class="font-bold mb-1"><x-heroicon-s-information-circle class="w-4 h-4 inline text-info"/> Info Penting:</p>
                                        <ul class="list-disc pl-4 space-y-1">
                                            <li>Nama file <strong>bebas</strong> (tidak ada aturan penamaan).</li>
                                            <li>Kolom header akan dideteksi secara <strong>otomatis</strong>.</li>
                                        </ul>
                                    </div>

                                    @error('excel_file')
                                        <p class="mt-2 text-xs text-error font-medium">{{ $message }}</p>
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
                                <div class="pt-2">
                                    <x-ui.action-button
                                        type="submit"
                                        class="w-full !btn-primary text-primary-content shadow-lg shadow-primary/20"
                                        label=""
                                        icon=""
                                        wire:loading.attr="disabled"
                                        wire:target="import, excel_file"
                                        x-bind:disabled="isUploading || progress > 0 && progress < 100"
                                    >
                                        <span x-show="!isUploading" wire:loading.remove wire:target="import, excel_file" class="flex items-center gap-2 justify-center w-full">
                                            <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                            Mulai Proses Import
                                        </span>
                                        <span x-show="isUploading" x-cloak class="flex items-center gap-2 justify-center w-full text-primary-content/80">
                                            <span class="loading loading-spinner loading-sm"></span>
                                            Menunggu File Terupload...
                                        </span>
                                        <span wire:loading wire:target="import" class="flex items-center gap-2 justify-center w-full">
                                            <span class="loading loading-spinner loading-sm"></span>
                                            Memulai Proses...
                                        </span>
                                    </x-ui.action-button>
                                </div>
                            </div>
                        </form>

                        @if($totalRows > 0)
                        <div class="pt-5 border-t border-base-300 mt-6">
                            <div class="flex justify-between items-end mb-2 text-xs font-bold uppercase tracking-wider">
                                <span class="text-base-content/60">Progres Import: <span class="text-base-content">{{ $processedRows }} / {{ $totalRows }}</span></span>
                                <span class="text-primary bg-primary/10 px-2 py-0.5 rounded-md">{{ $batchStatus }}</span>
                            </div>
                            <progress class="progress progress-primary w-full h-2.5" value="{{ $processedRows }}" max="{{ $totalRows }}"></progress>
                        </div>
                        @endif
                    </x-card>
                </div>

                <!-- Right Column: Log Terminal -->
                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div>
                            <h3 class="text-base font-bold text-base-content">Log Proses (Real-time)</h3>
                            <p class="text-xs text-base-content/50 mt-0.5">Pantau status dan hasil dari proses impor data.</p>
                        </div>
                        <div x-data="{ status: @entangle('batchStatus') }" class="flex items-center gap-2.5 bg-base-200 rounded-xl px-4 py-2 border border-base-300">
                            <span class="relative flex h-2.5 w-2.5">
                                <span x-show="status === 'processing'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                                      :class="{
                                        'bg-base-content/20': (!status || status === 'pending'),
                                        'bg-sky-500': status === 'processing',
                                        'bg-success': status === 'completed',
                                        'bg-error': status === 'failed'
                                      }"></span>
                            </span>
                            <span class="text-xs font-semibold capitalize text-base-content/70" x-text="status || 'Menunggu'"></span>
                        </div>
                    </div>

                    <div class="mockup-code bg-neutral text-neutral-content w-full h-[600px] flex flex-col shadow-lg border border-neutral-focus"
                         wire:poll.2s="syncLog">
                        <div class="px-5 pb-4 pt-2 flex-1 overflow-y-auto custom-scrollbar font-mono text-[13px] leading-relaxed"
                             x-ref="logContainer"
                             x-init="$watch('$wire.logLines', () => { $nextTick(() => $refs.logContainer.scrollTop = $refs.logContainer.scrollHeight) })">

                            @if($totalRows > 0 && $batchStatus === 'processing')
                                <pre class="sticky top-0 -mx-5 px-5 py-2 bg-sky-900/50 border-b border-sky-700/50 text-sky-300 text-center text-xs font-semibold mb-3">
<code>Memproses Import: {{ $processedRows }} dari {{ $totalRows }} baris...</code></pre>
                            @endif

                            @if(empty($logLines))
                                <pre data-prefix="~" class="text-neutral-content/50"><code>Menunggu berkas diunggah untuk memulai pencatatan...</code></pre>
                            @else
                                @foreach ($logLines as $log)
                                    @php
                                        $logColor = match($log['type']) {
                                            'error'   => 'text-error font-bold',
                                            'success' => 'text-success font-bold',
                                            'warning' => 'text-warning',
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
            @endif

            <!-- STEP 2: VALIDASI DAPODIK -->
            @if($currentStep == 2)
            <div class="w-full max-w-5xl mx-auto space-y-6 pb-6">
                
                <div class="flex items-center justify-between bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
                    <div>
                        <h2 class="text-lg font-bold">Validasi Data: {{ Carbon\Carbon::parse($selectedMonth)->translatedFormat('F Y') }}</h2>
                        <p class="text-sm text-base-content/60">Sistem mengecek kelengkapan referensi (Distributor & Produk) sebelum sinkronisasi.</p>
                    </div>
                    <button type="button" wire:click="setStep(1)" class="btn btn-ghost btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali ke Upload
                    </button>
                </div>

                @if(!$isGenerateLocked)
                    <!-- ALL VALID STATE -->
                    <div class="flex flex-col items-center justify-center py-16 bg-success/10 border border-success/30 rounded-2xl text-center">
                        <div class="w-24 h-24 bg-success text-success-content rounded-full flex items-center justify-center mb-6 shadow-lg shadow-success/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-success mb-2">Validasi Lolos (100% Bersih)</h3>
                        <p class="text-base-content/70 max-w-md mx-auto mb-8">Tidak ditemukan data distributor maupun produk yang belum terdaftar. Data siap disinkronisasikan ke tabel utama Dashboard.</p>
                        
                        <button type="button" wire:click="setStep(3)" class="btn btn-primary btn-lg shadow-xl px-12">
                            Lanjut ke Sinkronisasi (Tahap Akhir)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </div>
                @else
                    <!-- INVALID STATE -->
                    <div class="alert alert-error bg-error/10 border-error text-error-content shadow-sm flex items-start gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6 text-error mt-0.5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <div>
                            <h3 class="font-bold text-lg text-error">Validasi Gagal (Sistem Terkunci)</h3>
                            <p class="text-sm opacity-80 mt-1">Sistem mencegah proses sinkronisasi karena ada data yang tidak dikenali di master. Silakan perbaiki di bawah ini.</p>
                        </div>
                    </div>

                    <!-- PANEL VALIDASI: DISTRIBUTOR UNMAPPED -->
                    @if(count($unmappedDistributors) > 0)
                    <div class="p-5 rounded-xl border border-error bg-base-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-error"></div>
                        <h4 class="font-bold text-lg flex items-center gap-2 mb-2">
                            <span class="badge badge-error badge-lg">{{ count($unmappedDistributors) }}</span>
                            Distributor Belum Ter-Map
                        </h4>
                        <p class="text-sm text-base-content/60 mb-4">
                            Silakan petakan (Map) distributor berikut ke Master Distributor.
                        </p>
                        
                        <div class="overflow-x-auto border border-base-200 rounded-lg max-h-80 custom-scrollbar">
                            <table class="table table-sm table-pin-rows w-full">
                                <thead class="bg-base-200 text-base-content/80">
                                    <tr>
                                        <th class="w-1/2">Data Mentah (Excel)</th>
                                        <th class="w-1/2">Pilih Master Distributor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unmappedDistributors as $index => $unmapped)
                                    <tr class="hover:bg-base-200/50">
                                        <td class="whitespace-normal leading-tight">
                                            <div class="font-bold text-sm">{{ $unmapped->distributor }}</div>
                                            <div class="text-xs text-base-content/50 mt-1">
                                                Div: {{ $unmapped->divisi }} &bull; Wilayah: {{ $unmapped->wilayah }} &bull; Kode: <span class="font-mono bg-base-200 px-1 rounded">{{ $unmapped->kode_distributor }}</span>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="flex gap-2 items-start">
                                                <div class="flex-1">
                                                    <input type="text" list="masterList" wire:model.defer="quickMapSelections.{{ $index }}" class="input input-sm input-bordered w-full" placeholder="Ketik kode/nama Master...">
                                                    @error('quickmap.'.$index) <span class="text-xs text-error block mt-1">{{ $message }}</span> @enderror
                                                </div>
                                                <button type="button" wire:click="saveQuickMapping({{ $index }}, '{{ addslashes($unmapped->divisi) }}', '{{ addslashes($unmapped->wilayah) }}', '{{ addslashes($unmapped->kode_distributor) }}', '{{ addslashes($unmapped->distributor) }}')" class="btn btn-sm btn-primary">Map & Simpan</button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- PANEL VALIDASI: PRODUK TIDAK TERDAFTAR -->
                    @if(count($unregisteredProducts) > 0)
                    <div class="p-5 rounded-xl border border-error bg-base-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-error"></div>
                        <h4 class="font-bold text-lg flex items-center gap-2 mb-2">
                            <span class="badge badge-error badge-lg">{{ count($unregisteredProducts) }}</span>
                            Produk Tidak Terdaftar
                        </h4>
                        <p class="text-sm text-base-content/60 mb-4">
                            Kode Barang berikut tidak ditemukan di Master Produk Lama. Silakan tambahkan ke Master (di menu lain) terlebih dahulu.
                        </p>
                        
                        <div class="overflow-x-auto border border-base-200 rounded-lg max-h-80 custom-scrollbar">
                            <table class="table table-sm table-pin-rows w-full">
                                <thead class="bg-base-200 text-base-content/80">
                                    <tr>
                                        <th class="w-1/3">Kode Barang (Raw)</th>
                                        <th class="w-2/3">Nama Barang (Raw)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unregisteredProducts as $unprod)
                                    <tr class="hover:bg-base-200/50">
                                        <td class="font-mono text-sm font-bold">{{ $unprod->kode_barang }}</td>
                                        <td class="text-sm">{{ $unprod->nama_barang }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="flex justify-between items-center pt-4 border-t border-base-200 mt-4">
                        <button type="button" wire:click="checkValidation" class="btn btn-outline btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Refresh Validasi
                        </button>
                        <button type="button" class="btn btn-disabled opacity-50 cursor-not-allowed">
                            Lanjut ke Sinkronisasi (Terkunci)
                        </button>
                    </div>

                @endif

                <!-- Datalist Master Distributor (Render Once) -->
                <datalist id="masterList">
                    @foreach($masterDistributorsList as $master)
                        <option value="{{ $master->distributor_code }}">{{ $master->distributor_code }} - {{ $master->distributor_name }}</option>
                    @endforeach
                </datalist>
            </div>
            @endif

            <!-- STEP 3: SINKRONISASI (GENERATE) -->
            @if($currentStep == 3)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 min-h-0 pb-6">
                <!-- Left Column: Generate Card -->
                <div class="lg:col-span-1 flex flex-col space-y-6">
                    <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-200 flex items-center justify-between">
                        <div class="font-bold text-sm">Periode: {{ Carbon\Carbon::parse($selectedMonth)->translatedFormat('F Y') }}</div>
                        <button type="button" wire:click="setStep(2)" class="btn btn-ghost btn-xs">Kembali</button>
                    </div>

                    <x-card title="Tahap Akhir: Sinkronisasi" icon="circle-stack" class="flex-1 flex flex-col relative overflow-hidden">
                        
                        @if($generateStatus === 'completed' && $generateTotal > 0)
                            <div class="absolute inset-0 bg-base-100/95 backdrop-blur-sm flex flex-col items-center justify-center p-6 z-10 text-center">
                                <div class="w-16 h-16 bg-success/20 text-success rounded-full flex items-center justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Sinkronisasi Selesai!</h3>
                                <p class="text-sm text-base-content/70 mb-6">Data Clean telah berhasil di-generate. Dashboard kini menampilkan data terbaru.</p>
                                <button type="button" wire:click="$set('currentStep', 1)" class="btn btn-outline shadow-sm w-full">
                                    Mulai Proses Baru
                                </button>
                            </div>
                        @endif

                        <div class="space-y-6 flex-1 flex flex-col justify-center">
                            <div class="text-center">
                                <div class="w-20 h-20 bg-secondary/10 text-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </div>
                                <h3 class="font-bold text-lg mb-2">Siap Melakukan Generate!</h3>
                                <p class="text-sm text-base-content/70 leading-relaxed mx-auto">
                                    Data Anda sudah 100% valid dan siap ditransformasikan ke tabel <b>Clean</b> untuk disajikan di Dashboard.
                                </p>
                            </div>

                            <button type="button" wire:click="generateClean" wire:loading.attr="disabled" 
                                    class="btn btn-secondary btn-lg w-full text-secondary-content shadow-xl shadow-secondary/30 mt-4">
                                <span wire:loading.remove wire:target="generateClean" class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    Jalankan Sinkronisasi
                                </span>
                                <span wire:loading wire:target="generateClean" class="flex items-center gap-2">
                                    <span class="loading loading-spinner loading-md"></span>
                                    Memproses Generate...
                                </span>
                            </button>

                            <!-- Generate Progress -->
                            @if($generateTotal > 0)
                            <div class="pt-6 border-t border-base-200 mt-6 w-full">
                                <div class="flex justify-between items-end mb-2 text-xs font-bold uppercase tracking-wider">
                                    <span class="text-base-content/60">Progres Generate:</span>
                                    <span class="text-secondary bg-secondary/10 px-2 py-0.5 rounded-md">{{ $generateStatus }}</span>
                                </div>
                                <progress class="progress progress-secondary w-full h-3" value="{{ $generateProgress }}" max="{{ $generateTotal }}"></progress>
                            </div>
                            @endif
                        </div>
                    </x-card>
                </div>

                <!-- Right Column: Log Terminal -->
                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div>
                            <h3 class="text-base font-bold text-base-content">Log Sinkronisasi (Real-time)</h3>
                            <p class="text-xs text-base-content/50 mt-0.5">Pantau status generate data ke tabel utama.</p>
                        </div>
                        <div x-data="{ genStatus: @entangle('generateStatus') }" class="flex items-center gap-2.5 bg-base-200 rounded-xl px-4 py-2 border border-base-300">
                            <span class="relative flex h-2.5 w-2.5">
                                <span x-show="genStatus === 'processing'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                                      :class="{
                                        'bg-base-content/20': !genStatus || genStatus === 'pending',
                                        'bg-secondary': genStatus === 'processing',
                                        'bg-success': genStatus === 'completed',
                                        'bg-error': genStatus === 'failed'
                                      }"></span>
                            </span>
                            <span class="text-xs font-semibold capitalize text-base-content/70" x-text="genStatus || 'Menunggu'"></span>
                        </div>
                    </div>

                    <div class="mockup-code bg-neutral text-neutral-content w-full h-[600px] flex flex-col shadow-lg border border-neutral-focus"
                         wire:poll.2s="syncLog">
                        <div class="px-5 pb-4 pt-2 flex-1 overflow-y-auto custom-scrollbar font-mono text-[13px] leading-relaxed"
                             x-ref="logContainer"
                             x-init="$watch('$wire.logLines', () => { $nextTick(() => $refs.logContainer.scrollTop = $refs.logContainer.scrollHeight) })">

                            @if($generateTotal > 0 && $generateStatus === 'processing')
                                <pre class="sticky top-0 -mx-5 px-5 py-2 bg-secondary/20 border-b border-secondary/30 text-secondary text-center text-xs font-semibold mb-3">
<code>Memproses Generate Data Clean ke Database...</code></pre>
                            @endif

                            @if(empty($logLines) || ($generateTotal == 0 && $generateStatus !== 'completed' && $generateStatus !== 'failed'))
                                <pre data-prefix="~" class="text-neutral-content/50"><code>Menunggu perintah Sinkronisasi dijalankan...</code></pre>
                            @else
                                @foreach ($logLines as $log)
                                    @php
                                        $logColor = match($log['type']) {
                                            'error'   => 'text-error font-bold',
                                            'success' => 'text-success font-bold',
                                            'warning' => 'text-warning',
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
            @endif

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: oklch(var(--n) / 0.5); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: oklch(var(--n) / 0.8); }
    </style>
</div>
