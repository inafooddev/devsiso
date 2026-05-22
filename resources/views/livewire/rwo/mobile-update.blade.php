<div class="w-full max-w-md mx-auto px-4 py-6 flex-1 flex flex-col gap-6" x-data="mobileRwoApp()" x-init="init()">
    {{-- Toast Notification --}}
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-full max-w-xs px-4" 
         x-cloak>
        <div :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'" class="shadow-2xl rounded-2xl p-4 text-xs font-bold flex items-center gap-2 text-white border border-black/10">
            <template x-if="toast.type === 'success'">
                <x-heroicon-s-check-circle class="w-5 h-5 text-white flex-shrink-0" />
            </template>
            <template x-if="toast.type === 'error'">
                <x-heroicon-s-x-circle class="w-5 h-5 text-white flex-shrink-0" />
            </template>
            <span x-text="toast.message" class="flex-1 text-white"></span>
        </div>
    </div>

    {{-- Header --}}
    <header class="flex items-center justify-between bg-base-100/60 backdrop-blur-md border border-base-300 rounded-3xl p-4 shadow-lg shadow-base-300/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                <x-heroicon-s-camera class="w-6 h-6 animate-pulse" />
            </div>
            <div>
                <h1 class="text-sm font-black uppercase tracking-wider text-base-content/90">Sales RWO</h1>
                <p class="text-[10px] font-bold text-primary tracking-widest uppercase">Offline Photo Upload</p>
            </div>
        </div>
    </header>

    {{-- Banner / Instructions --}}
    <div class="card bg-gradient-to-tr from-primary/15 via-secondary/10 to-base-100 border border-primary/20 rounded-3xl p-5 shadow-md">
        <h2 class="text-sm font-extrabold text-base-content flex items-center gap-2">
            <x-heroicon-s-information-circle class="w-5 h-5 text-primary" />
            Panduan Sales Team (Bisa Offline)
        </h2>
        <p class="text-xs text-base-content/70 mt-2 leading-relaxed font-medium">
            Gunakan filter wilayah atau cari nama/kode toko. Jika Anda berada di daerah bersinyal buruk (Offline), foto akan disimpan di HP secara aman dan dapat disinkronkan saat sinyal bagus.
        </p>
    </div>

    {{-- Connection status & Offline alerts --}}
    <template x-if="isOffline">
        <div class="alert alert-warning shadow-lg border border-warning/30 rounded-2xl flex items-start gap-2.5 p-3 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-warning flex-shrink-0 mt-0.5">
                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.401 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>
            <div class="text-xs font-bold text-warning-content/90">
                Mode Offline Aktif: Anda sedang bekerja tanpa internet. Foto akan disimpan lokal dan dapat disinkronkan saat online.
            </div>
        </div>
    </template>

    {{-- Sync Status Bar --}}
    <template x-if="!isOffline && pendingQueueCount > 0">
        <div class="card bg-gradient-to-tr from-info/20 via-primary/10 to-base-100 border border-info/30 rounded-3xl p-5 shadow-lg flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-s-arrow-path class="w-5 h-5 text-info animate-spin" x-show="isSyncing" />
                    <x-heroicon-s-cloud-arrow-up class="w-5 h-5 text-info" x-show="!isSyncing" />
                    <h3 class="text-sm font-black text-base-content" x-text="pendingQueueCount + ' Foto Menunggu Sinkronisasi'"></h3>
                </div>
            </div>
            <p class="text-xs text-base-content/70 font-medium">
                Ada foto yang Anda ambil saat offline. Silakan klik tombol di bawah untuk mengunggah ke server sekarang.
            </p>
            <button @click="startSync()" :disabled="isSyncing" class="btn btn-info w-full h-11 rounded-2xl text-xs normal-case shadow-md text-info-content font-bold">
                <span class="loading loading-spinner loading-xs" x-show="isSyncing"></span>
                <span x-text="isSyncing ? 'Menyinkronkan (' + syncCurrent + '/' + syncTotal + ')...' : 'Sinkronisasi Sekarang'"></span>
            </button>
            <template x-if="isSyncing">
                <div class="w-full mt-1">
                    <progress class="progress progress-info w-full" :value="syncProgress" max="100"></progress>
                </div>
            </template>
        </div>
    </template>

    {{-- Session Message Toasts (Blade Fallback) --}}
    @if (session()->has('message'))
        <div class="alert alert-success shadow-lg border border-success/30 rounded-2xl flex items-start gap-2.5 p-3">
            <x-heroicon-s-check-circle class="w-5 h-5 text-success flex-shrink-0 mt-0.5" />
            <div class="text-xs font-semibold text-success-content/90">
                {{ session('message') }}
            </div>
        </div>
    @endif

    {{-- Filters Card --}}
    <div class="card bg-base-100 border border-base-300 rounded-3xl p-5 shadow-xl shadow-base-300/5 flex flex-col gap-4">
        <h3 class="text-xs font-black uppercase tracking-wider text-base-content/40 flex items-center gap-1.5">
            <x-heroicon-s-adjustments-horizontal class="w-4 h-4" />
            Filter Wilayah
        </h3>

        {{-- Region Dropdown --}}
        <div class="form-control w-full">
            <label class="label py-1"><span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Region</span></label>
            <select x-model="selectedRegion" class="select select-bordered select-sm h-11 w-full rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">-- Semua Region --</option>
                <template x-for="r in regionsList" :key="r.region_code">
                    <option :value="r.region_code" x-text="r.region_name + ' (' + r.region_code + ')'"></option>
                </template>
            </select>
        </div>

        {{-- Area Dropdown --}}
        <div class="form-control w-full">
            <label class="label py-1"><span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Area</span></label>
            <select x-model="selectedArea" class="select select-bordered select-sm h-11 w-full rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20" :disabled="!selectedRegion">
                <option value="">-- Semua Area --</option>
                <template x-for="a in getFilteredAreas()" :key="a.area_code">
                    <option :value="a.area_code" x-text="a.area_name"></option>
                </template>
            </select>
        </div>

        {{-- Branch Dropdown --}}
        <div class="form-control w-full">
            <label class="label py-1"><span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Cabang</span></label>
            <select x-model="selectedBranch" class="select select-bordered select-sm h-11 w-full rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20" :disabled="!selectedArea">
                <option value="">-- Semua Cabang --</option>
                <template x-for="b in getFilteredBranches()" :key="b.branch_code">
                    <option :value="b.branch_name" x-text="b.branch_name"></option>
                </template>
            </select>
        </div>

        <div class="divider my-1 border-base-300"></div>

        {{-- Search Shop Name/Code --}}
        <div class="form-control w-full">
            <label class="label py-1"><span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Cari Toko</span></label>
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                </span>
                <input x-model="search" @input.debounce.300ms="queryOutlets()"
                       type="text" 
                       placeholder="Cari Kode atau Nama Toko..." 
                       class="input input-bordered input-sm h-11 w-full pl-9 rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                <button x-show="search" @click="search = ''; queryOutlets()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-base-content/40 hover:text-base-content">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>

    {{-- Shop Lists / Active Upload Form --}}
    <div class="flex-1 flex flex-col gap-4">
        {{-- Active Upload Panel --}}
        <template x-if="activeOutlet">
            <div id="active-upload-panel" class="card bg-base-100 border border-primary/20 rounded-3xl p-5 shadow-2xl shadow-primary/5 flex flex-col gap-5">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="badge badge-primary badge-sm font-mono font-bold rounded-lg px-2" x-text="activeOutlet.customer_code"></span>
                        <h4 class="text-sm font-extrabold text-base-content mt-1" x-text="activeOutlet.customer_name"></h4>
                        <p class="text-xs text-base-content/40 mt-1 leading-relaxed" x-text="activeOutlet.alamat"></p>
                    </div>
                    <button @click="cancelUpload()" class="btn btn-ghost btn-circle btn-sm text-base-content/40 hover:bg-base-200">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="divider my-0"></div>

                {{-- Upload Fields Container --}}
                <div class="flex flex-col gap-5">
                    
                    {{-- FOTO TAMPAK DEPAN --}}
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Foto Tampak Depan</span>
                        </label>
                        
                        {{-- Dropzone / Capture Button --}}
                        <div class="relative border-2 border-dashed rounded-2xl transition-all duration-200 overflow-hidden min-h-[140px] flex flex-col items-center justify-center p-4"
                             :class="(fotoDepanPreview || activeOutlet.foto_toko2) ? 'border-success/40 bg-success/5' : 'border-base-300 bg-base-200'">
                            <input type="file" 
                                   accept="image/*" 
                                   capture="environment" 
                                   @change="handleFileSelect($event, 'foto_depan')" 
                                   class="absolute inset-0 opacity-0 cursor-pointer z-10" />
                            
                            <template x-if="fotoDepanPreview">
                                <div class="w-full flex flex-col items-center">
                                    <img :src="fotoDepanPreview" class="w-full h-32 object-contain rounded-xl" />
                                    <span class="text-[10px] font-bold text-success mt-2 flex items-center gap-1">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Siap diunggah
                                    </span>
                                </div>
                            </template>
                            <template x-if="!fotoDepanPreview && activeOutlet.foto_toko2">
                                <div class="w-full flex flex-col items-center">
                                    <img :src="getExistingPhotoUrl(activeOutlet.foto_toko2)" class="w-full h-32 object-contain rounded-xl opacity-60" />
                                    <span class="text-[10px] font-semibold text-base-content/50 mt-2" x-text="activeOutlet.foto_toko2.startsWith('pending') ? 'Foto Tersimpan Offline (Siap Sinkron)' : 'Foto Saat Ini (Tampak Depan)'"></span>
                                </div>
                            </template>
                            <template x-if="!fotoDepanPreview && !activeOutlet.foto_toko2">
                                <div class="w-full flex flex-col items-center">
                                    <x-heroicon-s-camera class="w-8 h-8 text-base-content/30" />
                                    <span class="text-xs font-bold text-base-content/65 mt-2">Ambil Foto Kamera</span>
                                    <span class="text-[9px] text-base-content/40 mt-0.5">Wajib langsung dari Kamera</span>
                                </div>
                            </template>

                            {{-- Upload Progress Indicator --}}
                            <div x-show="fotoDepanState.isUploading" class="absolute inset-0 bg-base-100/90 flex flex-col items-center justify-center p-4 z-20 transition-all duration-300" x-cloak>
                                <span class="loading loading-spinner loading-md text-primary"></span>
                                <span class="text-xs font-bold text-base-content/70 mt-2 flex flex-col items-center gap-1">
                                    <span>Mengunggah...</span>
                                    <span x-text="fotoDepanState.progress + '%'"></span>
                                </span>
                                <progress class="progress progress-primary w-2/3 mt-2" :value="fotoDepanState.progress" max="100"></progress>
                            </div>
                        </div>
                        <template x-if="fotoDepanState.errorMessage">
                            <span class="text-error text-[10px] font-semibold mt-1 ml-1" x-text="fotoDepanState.errorMessage"></span>
                        </template>
                    </div>

                    {{-- FOTO TAMPAK DALAM --}}
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Foto Tampak Dalam</span>
                        </label>
                        
                        {{-- Dropzone / Capture Button --}}
                        <div class="relative border-2 border-dashed rounded-2xl transition-all duration-200 overflow-hidden min-h-[140px] flex flex-col items-center justify-center p-4"
                             :class="(fotoDalamPreview || activeOutlet.foto_toko3) ? 'border-success/40 bg-success/5' : 'border-base-300 bg-base-200'">
                            <input type="file" 
                                   accept="image/*" 
                                   capture="environment" 
                                   @change="handleFileSelect($event, 'foto_dalam')" 
                                   class="absolute inset-0 opacity-0 cursor-pointer z-10" />
                            
                            <template x-if="fotoDalamPreview">
                                <div class="w-full flex flex-col items-center">
                                    <img :src="fotoDalamPreview" class="w-full h-32 object-contain rounded-xl" />
                                    <span class="text-[10px] font-bold text-success mt-2 flex items-center gap-1">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Siap diunggah
                                    </span>
                                </div>
                            </template>
                            <template x-if="!fotoDalamPreview && activeOutlet.foto_toko3">
                                <div class="w-full flex flex-col items-center">
                                    <img :src="getExistingPhotoUrl(activeOutlet.foto_toko3)" class="w-full h-32 object-contain rounded-xl opacity-60" />
                                    <span class="text-[10px] font-semibold text-base-content/50 mt-2" x-text="activeOutlet.foto_toko3.startsWith('pending') ? 'Foto Tersimpan Offline (Siap Sinkron)' : 'Foto Saat Ini (Tampak Dalam)'"></span>
                                </div>
                            </template>
                            <template x-if="!fotoDalamPreview && !activeOutlet.foto_toko3">
                                <div class="w-full flex flex-col items-center">
                                    <x-heroicon-s-camera class="w-8 h-8 text-base-content/30" />
                                    <span class="text-xs font-bold text-base-content/65 mt-2">Ambil Foto Kamera</span>
                                    <span class="text-[9px] text-base-content/40 mt-0.5">Wajib langsung dari Kamera</span>
                                </div>
                            </template>

                            {{-- Upload Progress Indicator --}}
                            <div x-show="fotoDalamState.isUploading" class="absolute inset-0 bg-base-100/90 flex flex-col items-center justify-center p-4 z-20 transition-all duration-300" x-cloak>
                                <span class="loading loading-spinner loading-md text-primary"></span>
                                <span class="text-xs font-bold text-base-content/70 mt-2 flex flex-col items-center gap-1">
                                    <span>Mengunggah...</span>
                                    <span x-text="fotoDalamState.progress + '%'"></span>
                                </span>
                                <progress class="progress progress-primary w-2/3 mt-2" :value="fotoDalamState.progress" max="100"></progress>
                            </div>
                        </div>
                        <template x-if="fotoDalamState.errorMessage">
                            <span class="text-error text-[10px] font-semibold mt-1 ml-1" x-text="fotoDalamState.errorMessage"></span>
                        </template>
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 mt-2">
                    <button @click="cancelUpload()" class="btn btn-outline border-base-300 hover:bg-base-200 flex-1 h-12 rounded-2xl text-xs normal-case">
                        Batal
                    </button>
                    <button @click="savePhotos()" 
                            class="btn btn-primary flex-1 h-12 rounded-2xl text-xs normal-case shadow-lg shadow-primary/20"
                            :disabled="fotoDepanState.isUploading || fotoDalamState.isUploading">
                        Simpan Foto
                    </button>
                </div>
            </div>
        </template>

        {{-- Outlet Cards --}}
        <div class="flex flex-col gap-3">
            <template x-for="outlet in outletsList" :key="outlet.id">
                <div class="card bg-base-100 border border-base-300 rounded-3xl p-4 shadow-sm hover:shadow-md transition-all duration-200"
                     :class="(activeOutlet && activeOutlet.id === outlet.id) ? 'ring-2 ring-primary ring-offset-2 bg-primary/5' : ''">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <span class="badge badge-sm badge-outline border-base-300 text-secondary font-mono font-bold rounded-lg px-2" x-text="outlet.customer_code"></span>
                            <h4 class="text-xs font-black text-base-content/85 mt-1.5 truncate" x-text="outlet.customer_name"></h4>
                            <p class="text-[11px] text-base-content/40 mt-1 leading-normal truncate" x-text="outlet.alamat"></p>
                            
                            {{-- Photo Status Badges --}}
                            <div class="flex items-center gap-3 mt-3">
                                <div class="flex items-center gap-1">
                                    <template x-if="outlet.foto_toko2">
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-s-check-circle class="w-4 h-4 text-success" />
                                            <span class="text-[10px] font-bold text-base-content/50" x-text="outlet.foto_toko2.startsWith('pending') ? 'Depan (Offline)' : 'Depan'"></span>
                                        </div>
                                    </template>
                                    <template x-if="!outlet.foto_toko2">
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-s-x-circle class="w-4 h-4 text-error/40" />
                                            <span class="text-[10px] font-semibold text-base-content/40">Depan</span>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <template x-if="outlet.foto_toko3">
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-s-check-circle class="w-4 h-4 text-success" />
                                            <span class="text-[10px] font-bold text-base-content/50" x-text="outlet.foto_toko3.startsWith('pending') ? 'Dalam (Offline)' : 'Dalam'"></span>
                                        </div>
                                    </template>
                                    <template x-if="!outlet.foto_toko3">
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-s-x-circle class="w-4 h-4 text-error/40" />
                                            <span class="text-[10px] font-semibold text-base-content/40">Dalam</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="flex-shrink-0 self-center">
                            <button x-show="!activeOutlet || activeOutlet.id !== outlet.id" 
                                    @click="selectOutlet(outlet)" 
                                    class="btn btn-primary btn-sm rounded-xl text-[10px] uppercase font-bold normal-case tracking-wider py-1 px-3">
                                Upload Foto
                            </button>
                            <button x-show="activeOutlet && activeOutlet.id === outlet.id" 
                                    @click="cancelUpload()" 
                                    class="btn btn-outline border-base-300 btn-sm rounded-xl text-[10px] uppercase font-bold normal-case tracking-wider py-1 px-3">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="outletsList.length === 0">
                <div class="card bg-base-100 border border-base-300 rounded-3xl py-12 px-6 text-center shadow-sm">
                    <div class="flex flex-col items-center gap-3 text-base-content/30" x-show="!selectedRegion && !selectedArea && !selectedBranch && !search">
                        <x-heroicon-o-map class="w-12 h-12 stroke-[1.5]" />
                        <h4 class="text-xs font-bold uppercase tracking-wider">Pilih Wilayah Terlebih Dahulu</h4>
                        <p class="text-[10px] text-base-content/40 max-w-[200px] mx-auto leading-normal">
                            Silakan gunakan filter wilayah di atas atau cari nama toko untuk menampilkan data outlet.
                        </p>
                    </div>
                    <div class="flex flex-col items-center gap-3 text-base-content/30" x-show="selectedRegion || selectedArea || selectedBranch || search">
                        <x-heroicon-o-magnifying-glass class="w-12 h-12 stroke-[1.5]" />
                        <h4 class="text-xs font-bold uppercase tracking-wider">Toko Tidak Ditemukan</h4>
                        <p class="text-[10px] text-base-content/40 max-w-[200px] mx-auto leading-normal">
                            Tidak ada data outlet yang cocok dengan filter atau pencarian Anda.
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="text-center py-4 text-[10px] text-base-content/30 font-semibold tracking-wider uppercase mt-auto">
        &copy; {{ date('Y') }} DevSiso &bull; RWO Mobile Photo Upload
    </footer>
</div>

{{-- Offline Master Data Script --}}
<script id="offline-master-data" type="application/json">
    {!! $offlineMasterDataJson !!}
</script>

<script>
    function mobileRwoApp() {
        return {
            isOffline: !navigator.onLine,
            regionsList: [],
            areasList: [],
            branchesList: [],
            outletsList: [],
            
            selectedRegion: '',
            selectedArea: '',
            selectedBranch: '',
            search: '',
            
            activeOutlet: null,
            fotoDepanBlob: null,
            fotoDalamBlob: null,
            fotoDepanPreview: null,
            fotoDalamPreview: null,
            
            fotoDepanState: { isUploading: false, progress: 0, errorMessage: '' },
            fotoDalamState: { isUploading: false, progress: 0, errorMessage: '' },
            
            pendingQueueCount: 0,
            isSyncing: false,
            syncProgress: 0,
            syncTotal: 0,
            syncCurrent: 0,
            
            toast: { show: false, message: '', type: 'success' },
            
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                }, 3000);
            },
            
            async init() {
                // Monitor connection status
                window.addEventListener('online', () => { this.isOffline = false; this.updateQueueCount(); });
                window.addEventListener('offline', () => { this.isOffline = true; });
                
                await this.initIndexedDB();
                this.seedMasterData();
                this.loadDropdowns();
                await this.updateQueueCount();
                
                // Watchers for filtering
                this.$watch('selectedRegion', () => {
                    this.selectedArea = '';
                    this.selectedBranch = '';
                    this.activeOutlet = null;
                    this.queryOutlets();
                });
                this.$watch('selectedArea', () => {
                    this.selectedBranch = '';
                    this.activeOutlet = null;
                    this.queryOutlets();
                });
                this.$watch('selectedBranch', () => {
                    this.activeOutlet = null;
                    this.queryOutlets();
                });
                this.$watch('search', () => {
                    this.activeOutlet = null;
                    this.queryOutlets();
                });
                
                this.queryOutlets();
            },
            
            // IndexedDB init
            initIndexedDB() {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open('RWOOfflineDB', 2);
                    request.onupgradeneeded = (e) => {
                        const db = e.target.result;
                        if (!db.objectStoreNames.contains('outlets')) {
                            db.createObjectStore('outlets', { keyPath: 'id' });
                        }
                        if (!db.objectStoreNames.contains('uploadQueue')) {
                            db.createObjectStore('uploadQueue', { keyPath: 'id', autoIncrement: true });
                        }
                    };
                    request.onsuccess = (e) => {
                        window.dbInstance = e.target.result;
                        resolve(e.target.result);
                    };
                    request.onerror = (e) => reject(e.target.error);
                });
            },
            
            getDB() {
                if (window.dbInstance) return Promise.resolve(window.dbInstance);
                return this.initIndexedDB();
            },
            
            seedMasterData() {
                if (this.isOffline) return;
                const scriptEl = document.getElementById('offline-master-data');
                if (!scriptEl) return;
                
                try {
                    const data = JSON.parse(scriptEl.textContent);
                    this.getDB().then(db => {
                        const transaction = db.transaction(['outlets'], 'readwrite');
                        const store = transaction.objectStore('outlets');
                        store.clear();
                        data.outlets.forEach(o => store.add(o));
                        
                        transaction.oncomplete = () => {
                            localStorage.setItem('rwo_regions', JSON.stringify(data.regions));
                            localStorage.setItem('rwo_areas', JSON.stringify(data.areas));
                            localStorage.setItem('rwo_supervisors', JSON.stringify(data.supervisors));
                            localStorage.setItem('rwo_branches', JSON.stringify(data.branches));
                            localStorage.setItem('rwo_last_seed', Date.now());
                            this.loadDropdowns();
                            this.queryOutlets();
                            console.log('IndexedDB seeded successfully.');
                        };
                    });
                } catch (e) {
                    console.error('Failed to seed master data:', e);
                }
            },
            
            loadDropdowns() {
                this.regionsList = JSON.parse(localStorage.getItem('rwo_regions') || '[]');
                this.areasList = JSON.parse(localStorage.getItem('rwo_areas') || '[]');
                this.branchesList = JSON.parse(localStorage.getItem('rwo_branches') || '[]');
            },
            
            getFilteredAreas() {
                if (!this.selectedRegion) return [];
                return this.areasList.filter(a => a.region_code === this.selectedRegion);
            },
            
            getFilteredBranches() {
                if (!this.selectedArea) return [];
                const supervisors = JSON.parse(localStorage.getItem('rwo_supervisors') || '[]');
                const activeSpvCodes = supervisors
                    .filter(s => s.area_code === this.selectedArea)
                    .map(s => s.supervisor_code);
                
                return this.branchesList.filter(b => activeSpvCodes.includes(b.supervisor_code));
            },
            
            queryOutlets() {
                // If nothing is selected, empty the list
                if (!this.selectedRegion && !this.selectedArea && !this.selectedBranch && !this.search) {
                    this.outletsList = [];
                    return;
                }
                
                this.getDB().then(db => {
                    const transaction = db.transaction(['outlets'], 'readonly');
                    const store = transaction.objectStore('outlets');
                    const request = store.getAll();
                    
                    request.onsuccess = (e) => {
                        let outlets = e.target.result;
                        
                        if (this.selectedRegion) {
                            outlets = outlets.filter(o => o.region_code === this.selectedRegion);
                        }
                        if (this.selectedArea) {
                            outlets = outlets.filter(o => o.area_code === this.selectedArea);
                        }
                        if (this.selectedBranch) {
                            outlets = outlets.filter(o => o.branch_name === this.selectedBranch);
                        }
                        if (this.search) {
                            const q = this.search.toLowerCase();
                            outlets = outlets.filter(o => 
                                (o.customer_code && o.customer_code.toLowerCase().includes(q)) || 
                                (o.customer_name && o.customer_name.toLowerCase().includes(q))
                            );
                        }
                        this.outletsList = outlets;
                    };
                });
            },
            
            selectOutlet(outlet) {
                if (this.fotoDepanPreview) URL.revokeObjectURL(this.fotoDepanPreview);
                if (this.fotoDalamPreview) URL.revokeObjectURL(this.fotoDalamPreview);
                
                this.activeOutlet = outlet;
                this.fotoDepanBlob = null;
                this.fotoDalamBlob = null;
                this.fotoDepanPreview = null;
                this.fotoDalamPreview = null;
                
                this.fotoDepanState = { isUploading: false, progress: 0, errorMessage: '' };
                this.fotoDalamState = { isUploading: false, progress: 0, errorMessage: '' };
                
                this.$nextTick(() => {
                    const el = document.getElementById('active-upload-panel');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
            
            cancelUpload() {
                if (this.fotoDepanPreview) URL.revokeObjectURL(this.fotoDepanPreview);
                if (this.fotoDalamPreview) URL.revokeObjectURL(this.fotoDalamPreview);
                
                this.activeOutlet = null;
                this.fotoDepanBlob = null;
                this.fotoDalamBlob = null;
                this.fotoDepanPreview = null;
                this.fotoDalamPreview = null;
            },
            
            getExistingPhotoUrl(path) {
                if (!path) return '';
                if (path.startsWith('pending')) {
                    return '';
                }
                return '/storage/' + path;
            },
            
            handleFileSelect(event, propertyName) {
                const file = event.target.files[0];
                if (!file) return;
                
                const localData = propertyName === 'foto_depan' ? this.fotoDepanState : this.fotoDalamState;
                localData.isUploading = false;
                localData.progress = 100;
                localData.errorMessage = '';
                
                if (propertyName === 'foto_depan') {
                    this.fotoDepanBlob = file;
                    this.fotoDepanPreview = URL.createObjectURL(file);
                } else {
                    this.fotoDalamBlob = file;
                    this.fotoDalamPreview = URL.createObjectURL(file);
                }
            },
            
            savePhotos() {
                if (!this.activeOutlet) return;
                this.saveOfflinePhotos();
            },
            
            saveOfflinePhotos() {
                if (!this.fotoDepanBlob && !this.fotoDalamBlob) {
                    this.showToast('Silakan pilih/ambil foto terlebih dahulu.', 'error');
                    return;
                }
                
                this.getDB().then(db => {
                    const transaction = db.transaction(['uploadQueue', 'outlets'], 'readwrite');
                    const queueStore = transaction.objectStore('uploadQueue');
                    const outletsStore = transaction.objectStore('outlets');
                    
                    const item = {
                        outlet_id: this.activeOutlet.id,
                        customer_code: this.activeOutlet.customer_code,
                        customer_name: this.activeOutlet.customer_name,
                        foto_depan_blob: this.fotoDepanBlob,
                        foto_dalam_blob: this.fotoDalamBlob,
                        timestamp: Date.now()
                    };
                    
                    queueStore.add(item);
                    
                    // Update local outlets representation
                    const getReq = outletsStore.get(this.activeOutlet.id);
                    getReq.onsuccess = (e) => {
                        const o = e.target.result;
                        if (o) {
                            if (this.fotoDepanBlob) o.foto_toko2 = 'pending_depan';
                            if (this.fotoDalamBlob) o.foto_toko3 = 'pending_dalam';
                            outletsStore.put(o);
                        }
                    };
                    
                    transaction.oncomplete = () => {
                        this.cancelUpload();
                        this.updateQueueCount();
                        this.queryOutlets();
                        
                        if (this.isOffline) {
                            this.showToast('Foto disimpan secara offline.');
                        } else {
                            this.showToast('Foto disimpan. Menyinkronkan...');
                            this.startSync();
                        }
                    };
                });
            },
            
            updateQueueCount() {
                return this.getDB().then(db => {
                    return new Promise((resolve) => {
                        const transaction = db.transaction(['uploadQueue'], 'readonly');
                        const store = transaction.objectStore('uploadQueue');
                        const countReq = store.count();
                        countReq.onsuccess = () => {
                            this.pendingQueueCount = countReq.result;
                            resolve();
                        };
                    });
                });
            },
            
            uploadFilePromise(propertyName, blob) {
                return new Promise((resolve, reject) => {
                    @this.upload(
                        propertyName,
                        blob,
                        () => resolve(),
                        (err) => reject(err),
                        (event) => {
                            // Can update detailed progress if needed
                        }
                    );
                });
            },
            
            async startSync() {
                if (this.isOffline || this.isSyncing) return;
                
                this.isSyncing = true;
                
                try {
                    const db = await this.getDB();
                    const transaction = db.transaction(['uploadQueue'], 'readonly');
                    const store = transaction.objectStore('uploadQueue');
                    const queue = await new Promise((resolve, reject) => {
                        const req = store.getAll();
                        req.onsuccess = () => resolve(req.result);
                        req.onerror = () => reject(req.error);
                    });
                    
                    if (queue.length === 0) {
                        this.isSyncing = false;
                        return;
                    }
                    
                    this.syncTotal = queue.length;
                    this.syncCurrent = 0;
                    this.syncProgress = 0;
                    
                    for (const item of queue) {
                        this.syncCurrent++;
                        this.syncProgress = Math.round(((this.syncCurrent - 1) / this.syncTotal) * 100);
                        
                        // Clear active files first on component
                        @this.set('foto_depan', null);
                        @this.set('foto_dalam', null);
                        
                        // Upload foto_depan if exists
                        if (item.foto_depan_blob) {
                            let fileToUpload = item.foto_depan_blob;
                            if (!(fileToUpload instanceof File) || !fileToUpload.name) {
                                const mimeType = item.foto_depan_blob.type || 'image/jpeg';
                                const ext = mimeType === 'image/png' ? 'png' : 'jpg';
                                fileToUpload = new File([item.foto_depan_blob], 'depan_' + item.outlet_id + '_' + Date.now() + '.' + ext, { type: mimeType });
                            }
                            await this.uploadFilePromise('foto_depan', fileToUpload);
                        }
                        
                        // Upload foto_dalam if exists
                        if (item.foto_dalam_blob) {
                            let fileToUpload = item.foto_dalam_blob;
                            if (!(fileToUpload instanceof File) || !fileToUpload.name) {
                                const mimeType = item.foto_dalam_blob.type || 'image/jpeg';
                                const ext = mimeType === 'image/png' ? 'png' : 'jpg';
                                fileToUpload = new File([item.foto_dalam_blob], 'dalam_' + item.outlet_id + '_' + Date.now() + '.' + ext, { type: mimeType });
                            }
                            await this.uploadFilePromise('foto_dalam', fileToUpload);
                        }
                        
                        // Trigger backend save offline photos
                        await new Promise((resolve, reject) => {
                            @this.call('savePhotosForOutletOffline', item.outlet_id)
                                .then(resolve)
                                .catch(reject);
                        });
                        
                        // Delete from offline queue
                        await new Promise((resolve, reject) => {
                            const delTx = db.transaction(['uploadQueue'], 'readwrite');
                            const delStore = delTx.objectStore('uploadQueue');
                            const delReq = delStore.delete(item.id);
                            delReq.onsuccess = () => resolve();
                            delReq.onerror = () => reject(delReq.error);
                        });
                    }
                    
                    this.syncProgress = 100;
                    this.showToast('Semua foto offline berhasil disinkronisasi!');
                    
                } catch (e) {
                    console.error('Sync failed:', e);
                    this.showToast('Gagal sinkronisasi: ' + e.message, 'error');
                } finally {
                    this.isSyncing = false;
                    await this.updateQueueCount();
                    this.queryOutlets();
                    @this.call('$refresh');
                    
                    // Auto-trigger next sync loop if new items were added during sync
                    if (!this.isOffline && this.pendingQueueCount > 0) {
                        this.startSync();
                    }
                }
            }
        };
    }
</script>
