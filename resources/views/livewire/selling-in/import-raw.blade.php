<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Import Raw & Mapping Selling-In</x-slot>

    <!-- Top Tab Menu -->
    <x-ui.tab-menu class="mb-6 md:mb-8">
        <x-ui.tab-item active="{{ $currentTab == 'import' }}" wire:click.prevent="switchTab('import')" :navigate="false">
            <x-heroicon-o-document-arrow-up class="w-4 h-4 inline-block mr-1" />
            Import Raw
        </x-ui.tab-item>
        
        <x-ui.tab-item active="{{ $currentTab == 'generate' }}" wire:click.prevent="switchTab('generate')" :navigate="false">
            <x-heroicon-o-circle-stack class="w-4 h-4 inline-block mr-1" />
            Validasi & Generate
            @if($isGenerateLocked)
                <span class="badge badge-error badge-xs ml-1">!</span>
            @endif
        </x-ui.tab-item>

        <x-ui.tab-item active="{{ $currentTab == 'mapping' }}" wire:click.prevent="switchTab('mapping')" :navigate="false">
            <x-heroicon-o-map class="w-4 h-4 inline-block mr-1" />
            Master Mapping Distributor
        </x-ui.tab-item>
        
        <x-ui.tab-item active="{{ $currentTab == 'lama' }}" wire:click.prevent="switchTab('lama')" :navigate="false">
            <x-heroicon-o-server-stack class="w-4 h-4 inline-block mr-1" />
            Job Sell In Lama
        </x-ui.tab-item>
    </x-ui.tab-menu>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 pb-6">
            
            <!-- TAB 1: IMPORT RAW -->
            @if($currentTab == 'import')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 min-h-0">
                <!-- Left Column: Upload Form -->
                <div class="lg:col-span-1 flex flex-col">
                    <x-card title="Import Data Selling-In" icon="document-arrow-up" class="h-full flex flex-col relative overflow-hidden">
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
                                    <input type="month" id="importMonth" wire:model="importMonth"
                                           class="input input-bordered input-primary w-full text-sm" required />
                                    @error('importMonth')
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
                <div class="lg:col-span-2 flex flex-col min-h-[500px]">
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

                    <div class="mockup-code bg-neutral text-neutral-content w-full flex-1 flex flex-col shadow-lg border border-neutral-focus"
                         wire:poll.2s="syncLog">
                        <div class="px-5 pb-4 pt-2 flex-1 overflow-y-auto custom-scrollbar font-mono text-[13px] leading-relaxed max-h-[600px]"
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

            <!-- TAB 2: VALIDASI & GENERATE -->
            @if($currentTab == 'generate')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 min-h-0 pb-6">
                
                <!-- Left Column: Validation / Action -->
                <div class="lg:col-span-1 flex flex-col space-y-6">
                    <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
                        <label for="selectedMonthGen" class="block font-bold text-sm mb-2">Pilih Periode Validasi & Generate:</label>
                        <input type="month" id="selectedMonthGen" wire:model.live="generateMonth"
                               class="input input-bordered input-primary w-full text-lg font-bold" required />
                    </div>

                    @if(empty($generateMonth))
                        <!-- STATE: BELUM PILIH BULAN -->
                        <x-card title="Menunggu Pemilihan Periode" icon="calendar" class="flex-1 flex flex-col relative overflow-hidden h-full">
                            <div class="flex flex-col items-center justify-center h-full text-center p-8 bg-base-200/30 rounded-xl border border-base-200 border-dashed">
                                <div class="w-20 h-20 bg-base-200 text-base-content/40 rounded-full flex items-center justify-center mb-4 shadow-inner">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <h3 class="text-lg font-bold mb-2">Pilih Periode Validasi</h3>
                                <p class="text-sm text-base-content/60 max-w-sm mx-auto">
                                    Silakan pilih bulan dan tahun pada form di atas terlebih dahulu untuk memulai proses validasi data.
                                </p>
                            </div>
                        </x-card>
                    @elseif($isGenerateLocked)
                        <!-- INVALID STATE -->
                        <div class="alert alert-error bg-error/10 border-error text-error-content shadow-sm flex flex-col items-start gap-4 p-5">
                            <div class="flex items-center gap-3 w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-8 w-8 text-error" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <div>
                                    <h3 class="font-bold text-lg text-error">Validasi Gagal</h3>
                                    <p class="text-sm opacity-80 mt-1 leading-tight">Sistem terkunci. Selesaikan pemetaan (mapping) distributor dan pendaftaran produk di bawah.</p>
                                </div>
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
                            <p class="text-xs text-base-content/60 mb-3">Map langsung di sini:</p>
                            
                            <div class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                                @foreach($unmappedDistributors as $index => $unmapped)
                                <div class="bg-base-200/50 p-3 rounded-lg border border-base-200">
                                    <div class="font-bold text-sm mb-1">{{ $unmapped->distributor }}</div>
                                    <div class="text-[10px] text-base-content/60 mb-2">
                                        Div: {{ $unmapped->divisi }} | Wil: {{ $unmapped->wilayah }} | Kode: {{ $unmapped->kode_distributor }}
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <input type="text" list="masterList" wire:model.defer="quickMapSelections.{{ $index }}" class="input input-sm input-bordered w-full" placeholder="Ketik kode/nama Master...">
                                        @error('quickmap.'.$index) <span class="text-xs text-error">{{ $message }}</span> @enderror
                                        <button type="button" wire:click="saveQuickMapping({{ $index }}, '{{ addslashes($unmapped->divisi) }}', '{{ addslashes($unmapped->wilayah) }}', '{{ addslashes($unmapped->kode_distributor) }}', '{{ addslashes($unmapped->distributor) }}')" class="btn btn-sm btn-primary w-full">Simpan Map</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Datalist Master Distributor -->
                            <datalist id="masterList">
                                @foreach($masterDistributorsList as $master)
                                    <option value="{{ $master->distributor_code }}">{{ $master->distributor_code }} - {{ $master->distributor_name }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        @endif

                        <!-- PANEL VALIDASI: PRODUK TIDAK TERDAFTAR -->
                        @if(count($unregisteredProducts) > 0)
                        <div class="p-5 rounded-xl border border-error bg-base-100 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-error"></div>
                            <h4 class="font-bold text-lg flex items-center gap-2 mb-2">
                                <span class="badge badge-error badge-lg">{{ count($unregisteredProducts) }}</span>
                                Produk Belum Terdaftar
                            </h4>
                            <p class="text-xs text-base-content/60 mb-3">Produk ini tidak ada di master:</p>
                            
                            <div class="overflow-x-auto max-h-[300px] custom-scrollbar">
                                <table class="table table-xs table-pin-rows w-full">
                                    <thead class="bg-base-200">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Produk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unregisteredProducts as $unprod)
                                        <tr>
                                            <td class="font-mono">{{ $unprod->kode_barang }}</td>
                                            <td class="whitespace-normal leading-tight">{{ $unprod->nama_barang }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <button type="button" wire:click="checkValidation" class="btn btn-outline w-full mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Refresh Pengecekan
                        </button>
                    @else
                        <!-- VALID & READY STATE -->
                        <x-card title="Tahap Akhir: Sinkronisasi" icon="circle-stack" class="flex-1 flex flex-col relative overflow-hidden">
                            <div class="flex flex-col items-center justify-center p-6 bg-success/10 border border-success/30 rounded-2xl text-center mb-6">
                                <div class="w-16 h-16 bg-success text-success-content rounded-full flex items-center justify-center mb-4 shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <h3 class="text-xl font-black text-success mb-2">Validasi Lolos (100% Bersih)</h3>
                                <p class="text-xs text-base-content/70">Data siap disinkronisasikan ke tabel utama.</p>
                            </div>

                            <button type="button" wire:click="generateClean" wire:loading.attr="disabled" 
                                    class="btn btn-secondary btn-lg w-full text-secondary-content shadow-xl shadow-secondary/30">
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
                        </x-card>
                    @endif
                </div>

                <!-- Right Column: Log Terminal Generate -->
                <div class="lg:col-span-2 flex flex-col min-h-[500px]">
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

                    <div class="mockup-code bg-neutral text-neutral-content w-full flex-1 flex flex-col shadow-lg border border-neutral-focus"
                         wire:poll.2s="syncLog">
                        <div class="px-5 pb-4 pt-2 flex-1 overflow-y-auto custom-scrollbar font-mono text-[13px] leading-relaxed max-h-[600px]"
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

            <!-- TAB 3: MAPPING DISTRIBUTOR MASTER -->
            @if($currentTab == 'mapping')
            <div class="w-full h-full min-h-0 bg-base-100 rounded-xl shadow-sm border border-base-200 overflow-hidden">
                <!-- Render DistributorMapping Component inside the tab -->
                <div class="p-6 h-full overflow-y-auto custom-scrollbar">
                    <livewire:selling-in.distributor-mapping />
                </div>
            </div>
            @endif

            <!-- TAB 4: JOB SELL IN LAMA -->
            @if($currentTab == 'lama')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 min-h-0 pb-6">
                
                <!-- Left Column: Action -->
                <div class="lg:col-span-1 flex flex-col space-y-6">
                    <div class="bg-base-100 p-4 rounded-xl shadow-sm border border-base-200">
                        <label for="lamaMonth" class="block font-bold text-sm mb-2">Pilih Periode Job Lama:</label>
                        <input type="month" id="lamaMonth" wire:model="lamaMonth"
                               class="input input-bordered input-primary w-full text-lg font-bold" required />
                    </div>

                    <x-card title="Tahap Akhir: Sinkronisasi ke Tabel Lama" icon="server-stack" class="flex-1 flex flex-col justify-between">
                        <div class="mb-6 flex-1 flex flex-col items-center justify-center text-center p-4 bg-info/10 rounded-xl border border-info/20 text-info">
                            <div class="w-16 h-16 bg-info/20 text-info rounded-full flex items-center justify-center mb-4">
                                <x-heroicon-o-server-stack class="w-8 h-8" />
                            </div>
                            <h3 class="font-bold text-lg mb-1">Siap Dieksekusi</h3>
                            <p class="text-sm opacity-80">Data akan disalin dari tabel clean baru ke tabel selling_in lama.</p>
                        </div>
                        
                        <button type="button" wire:click="runJobLama" class="btn btn-primary w-full shadow-md shadow-primary/20"
                            wire:loading.attr="disabled"
                            @if($lamaStatus === 'processing') disabled @endif>
                            
                            <span wire:loading.remove wire:target="runJobLama">
                                @if($lamaStatus === 'processing')
                                    <span class="loading loading-spinner loading-sm mr-2"></span>
                                    Memproses...
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    Jalankan Job Lama
                                @endif
                            </span>
                            <span wire:loading wire:target="runJobLama">
                                <span class="loading loading-spinner loading-sm mr-2"></span>
                                Menyiapkan...
                            </span>
                        </button>
                    </x-card>
                </div>

                <!-- Right Column: Terminal Log -->
                <div class="lg:col-span-2 flex flex-col min-h-0 bg-base-300 rounded-xl overflow-hidden shadow-inner font-mono text-sm relative" wire:poll.1000ms="syncLog">
                    
                    <div class="bg-base-300 px-4 py-2 flex items-center justify-between border-b border-base-content/10 shrink-0">
                        <div class="flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-error"></div>
                                <div class="w-3 h-3 rounded-full bg-warning"></div>
                                <div class="w-3 h-3 rounded-full bg-success"></div>
                            </div>
                            <span class="ml-2 font-bold text-base-content/70">Job_Lama_Terminal</span>
                        </div>
                        <div class="text-xs text-base-content/50">
                            @if($lamaStatus === 'processing')
                                RUNNING...
                            @elseif($lamaStatus === 'completed')
                                DONE
                            @elseif($lamaStatus === 'failed')
                                ERROR
                            @else
                                IDLE
                            @endif
                        </div>
                    </div>

                    @if($lamaStatus === 'processing')
                        @php
                            $progressPercent = $lamaTotal > 0 ? round(($lamaProgress / $lamaTotal) * 100) : 0;
                        @endphp
                        <div class="px-4 py-3 bg-base-300/80 border-b border-base-content/10 shrink-0">
                            <div class="flex justify-between text-xs mb-1 font-bold">
                                <span class="text-info">MENYALIN DATA...</span>
                                <span class="text-info">{{ $progressPercent }}%</span>
                            </div>
                            <progress class="progress progress-info w-full" value="{{ $lamaProgress }}" max="{{ $lamaTotal ?: 100 }}"></progress>
                            <div class="text-right text-[10px] opacity-50 mt-1">{{ number_format($lamaProgress) }} / {{ number_format($lamaTotal) }} rows</div>
                        </div>
                    @endif

                    <!-- Terminal Content -->
                    <div class="p-4 overflow-y-auto flex-1 flex flex-col custom-scrollbar bg-[#1e1e1e] text-gray-300 space-y-1">
                        @forelse($logLines as $log)
                            <div class="flex gap-3">
                                <span class="text-gray-500 shrink-0">[{{ now()->format('H:i:s') }}]</span>
                                @if($log['type'] === 'error')
                                    <span class="text-error whitespace-pre-wrap">{{ $log['message'] }}</span>
                                @elseif($log['type'] === 'success')
                                    <span class="text-success">{{ $log['message'] }}</span>
                                @else
                                    <span class="text-info">{{ $log['message'] }}</span>
                                @endif
                            </div>
                        @empty
                            <div class="text-gray-500 italic flex h-full items-center justify-center opacity-50">
                                Menunggu instruksi...
                            </div>
                        @endforelse
                        
                        @if($lamaStatus === 'processing')
                            <div class="flex gap-3 animate-pulse mt-2">
                                <span class="text-gray-500 shrink-0">[{{ now()->format('H:i:s') }}]</span>
                                <span class="text-warning">Processing data... <span class="loading loading-dots loading-xs"></span></span>
                            </div>
                        @endif
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
