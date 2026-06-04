<div class="w-full max-w-md mx-auto min-h-screen bg-slate-50 text-slate-800 flex flex-col shadow-sm border-x border-slate-100 relative" x-data="mobileRwoApp($wire)" x-init="init()">
    
    {{-- Toast Notification --}}
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-[320px] px-4" 
         x-cloak>
        <div :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'" class="shadow-xl rounded-2xl p-3.5 text-[11px] font-extrabold flex items-center gap-2 text-white border border-black/10">
            <template x-if="toast.type === 'success'">
                <x-heroicon-s-check-circle class="w-5 h-5 text-white flex-shrink-0" />
            </template>
            <template x-if="toast.type === 'error'">
                <x-heroicon-s-x-circle class="w-5 h-5 text-white flex-shrink-0" />
            </template>
            <span x-text="toast.message" class="flex-1 text-white leading-snug"></span>
        </div>
    </div>

    {{-- Sticky Top Header & Search Wrapper --}}
    <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm shrink-0">
        {{-- Header --}}
        <header class="px-4 py-3 flex items-center justify-between" style="padding-top: calc(0.75rem + env(safe-area-inset-top, 0px));">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-sm shadow-primary/10">
                    <x-heroicon-s-camera class="w-5 h-5 animate-pulse" />
                </div>
                <div>
                    <h1 class="text-xs font-black uppercase tracking-wider text-slate-900 leading-tight">Sales RWO</h1>
                    <p class="text-[8px] font-bold text-primary tracking-widest uppercase leading-none">Photo Portal</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- Connection Status Badge -->
                <div :class="isOffline ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200'" 
                     class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-[9px] font-bold tracking-wider uppercase transition-all duration-300">
                    <span class="w-1.5 h-1.5 rounded-full" :class="isOffline ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></span>
                    <span x-text="isOffline ? 'Offline' : 'Online'"></span>
                </div>

                <!-- Panduan Button -->
                <button @click="showGuideSheet = true" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border border-primary/20 bg-primary/5 text-primary text-[9px] font-extrabold tracking-wider uppercase hover:bg-primary/10 transition-all duration-200">
                    <x-heroicon-s-information-circle class="w-3.5 h-3.5" />
                    <span>Panduan</span>
                </button>
                
                <!-- Reset Filters Button if any filters active -->
                <button x-show="selectedRegion || selectedArea || selectedBranch || search" 
                        @click="resetAllFilters()" 
                        class="btn btn-ghost btn-circle btn-xs text-rose-500" 
                        title="Reset Filter"
                        x-cloak>
                    <x-heroicon-s-arrow-path class="w-4 h-4" />
                </button>
            </div>
        </header>

        {{-- Search & Filter Bar --}}
        <div class="px-4 pb-3 flex items-center gap-2">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-s-magnifying-glass class="w-5 h-5" />
                </span>
                <input x-model="search" @input.debounce.300ms="queryOutlets()"
                       type="text" 
                       placeholder="Cari nama / kode toko..." 
                       class="input input-bordered input-sm h-10 w-full pl-9 pr-8 rounded-xl text-base bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all duration-200" />
                <button x-show="search" @click="search = ''; queryOutlets()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>
            
            <!-- Filter Button -->
            <button @click="showFiltersSheet = true" 
                    :class="selectedRegion || selectedArea || selectedBranch ? 'btn-primary text-white shadow-md shadow-primary/20 border-primary' : 'bg-slate-50 text-slate-600 border-slate-200'"
                    class="btn btn-sm btn-square h-10 w-10 rounded-xl border flex items-center justify-center transition-all duration-200 relative">
                <x-heroicon-s-adjustments-horizontal class="w-5 h-5" />
                <span x-show="selectedRegion || selectedArea || selectedBranch" class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full animate-bounce" x-cloak></span>
            </button>
        </div>
    </div>

    {{-- Main Content Area --}}
    <main class="flex-1 px-4 py-4 space-y-4 flex flex-col bg-slate-50/50">

        {{-- Mode Offline Alert --}}
        <template x-if="isOffline">
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3 shadow-xs flex items-start gap-2.5 animate-pulse">
                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <div class="text-[11px] font-bold text-amber-800 leading-tight">
                    Mode Offline Aktif: Bekerja tanpa internet. Foto akan disimpan di HP & disinkronkan saat online.
                </div>
            </div>
        </template>

        {{-- Sync Status Bar --}}
        <template x-if="!isOffline && (pendingQueueCount > 0 || pendingEditQueueCount > 0)">
            <div class="bg-gradient-to-tr from-info/15 via-primary/5 to-white border border-info/20 rounded-2xl p-4 shadow-sm flex flex-col gap-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-s-arrow-path class="w-4 h-4 text-info animate-spin" x-show="isSyncing" />
                        <x-heroicon-s-cloud-arrow-up class="w-4 h-4 text-info" x-show="!isSyncing" />
                        <h3 class="text-xs font-bold text-slate-800" x-text="getPendingSyncMessage()"></h3>
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 font-medium">
                    Terdapat data atau foto yang diambil saat offline. Silakan klik tombol di bawah untuk sinkronisasi.
                </p>
                <div class="flex flex-col gap-2 mt-1">
                    <button @click="startSync()" :disabled="isSyncing" class="btn btn-info btn-sm h-9 rounded-xl text-[10px] uppercase font-black text-white tracking-wider w-full shadow-xs">
                        <span class="loading loading-spinner loading-xs" x-show="isSyncing"></span>
                        <span x-text="isSyncing ? 'Menyinkronkan (' + syncCurrent + '/' + syncTotal + ')...' : 'Sinkronisasi Sekarang'"></span>
                    </button>
                    <button @click="clearSyncQueue()" :disabled="isSyncing" class="text-[9px] text-error/80 font-black hover:underline py-1 text-center">
                        Hapus Antrean Data Offline
                    </button>
                </div>
                <template x-if="isSyncing">
                    <div class="w-full mt-1">
                        <progress class="progress progress-info h-1 w-full rounded-full" :value="syncProgress" max="100"></progress>
                    </div>
                </template>
            </div>
        </template>

        {{-- Session Message Toasts (Blade Fallback) --}}
        @if (session()->has('message'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3 shadow-xs flex items-start gap-2.5">
                <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" />
                <div class="text-[11px] font-bold text-emerald-800 leading-tight">
                    {{ session('message') }}
                </div>
            </div>
        @endif

        {{-- Outlet Cards List --}}
        <div class="flex-1 flex flex-col gap-3">
            <div class="flex flex-col gap-2.5">
                <template x-for="outlet in outletsList" :key="outlet.id">
                    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.05)] hover:shadow-md transition-all duration-300 flex flex-col gap-3.5"
                         :class="(activeOutlet && activeOutlet.id === outlet.id) ? 'ring-2 ring-primary ring-offset-1 bg-primary/[0.02]' : ''">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[9px] px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold font-mono tracking-wider w-fit" x-text="outlet.customer_code"></span>
                                    
                                    <!-- Status Kelengkapan -->
                                    <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border transition-colors duration-200"
                                          :class="outlet.status === 'Complete' ? 'bg-emerald-50 text-emerald-600 border-emerald-100/80' : 'bg-rose-50 text-rose-600 border-rose-100/80'"
                                          x-text="outlet.status === 'Complete' ? 'Complete' : 'Not Complete'"></span>
                                    
                                    <!-- Status Validasi -->
                                    <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider border transition-colors duration-200"
                                          :class="outlet.is_valid ? 'bg-blue-50 text-blue-600 border-blue-100/80' : 'bg-slate-50 text-slate-500 border-slate-200/80'"
                                          x-text="outlet.is_valid ? 'Terverifikasi' : 'Belum Verifikasi'"></span>
                                </div>
                                <h4 class="text-xs font-black text-slate-800 mt-2 tracking-tight truncate" x-text="outlet.customer_name"></h4>
                                <p class="text-[10px] text-slate-400 font-semibold leading-normal mt-0.5 line-clamp-2" x-text="outlet.alamat"></p>
                            </div>
                        </div>
                        
                        <!-- Status and Action Row -->
                        <div class="flex flex-wrap items-center justify-between gap-2.5 border-t border-slate-100 pt-3">
                            <!-- Photo Status Caps -->
                            <div class="flex items-center gap-1.5">
                                <!-- Foto Depan -->
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-extrabold tracking-wider transition-colors duration-200"
                                     :class="outlet.foto_toko2 ? (outlet.foto_toko2.startsWith('pending') ? 'bg-amber-50 text-amber-600 border border-amber-100/50' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/50') : 'bg-slate-50 text-slate-400 border border-slate-100/50'">
                                    <template x-if="outlet.foto_toko2 && !outlet.foto_toko2.startsWith('pending')">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-500" />
                                    </template>
                                    <template x-if="outlet.foto_toko2 && outlet.foto_toko2.startsWith('pending')">
                                        <x-heroicon-s-arrow-path class="w-3.5 h-3.5 text-amber-500 animate-spin" />
                                    </template>
                                    <template x-if="!outlet.foto_toko2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    </template>
                                    <span x-text="outlet.foto_toko2 ? (outlet.foto_toko2.startsWith('pending') ? 'Depan (Offline)' : 'Tampak Depan') : 'Tampak Depan'"></span>
                                </div>
                                
                                <!-- Foto Dalam -->
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-extrabold tracking-wider transition-colors duration-200"
                                     :class="outlet.foto_toko3 ? (outlet.foto_toko3.startsWith('pending') ? 'bg-amber-50 text-amber-600 border border-amber-100/50' : 'bg-emerald-50 text-emerald-600 border border-emerald-100/50') : 'bg-slate-50 text-slate-400 border border-slate-100/50'">
                                    <template x-if="outlet.foto_toko3 && !outlet.foto_toko3.startsWith('pending')">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5 text-emerald-500" />
                                    </template>
                                    <template x-if="outlet.foto_toko3 && outlet.foto_toko3.startsWith('pending')">
                                        <x-heroicon-s-arrow-path class="w-3.5 h-3.5 text-amber-500 animate-spin" />
                                    </template>
                                    <template x-if="!outlet.foto_toko3">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    </template>
                                    <span x-text="outlet.foto_toko3 ? (outlet.foto_toko3.startsWith('pending') ? 'Dalam (Offline)' : 'Tampak Dalam') : 'Tampak Dalam'"></span>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex items-center gap-1.5">
                                <button @click="detailOutlet = outlet" 
                                        class="btn btn-xs btn-outline border-slate-200 hover:bg-slate-100 h-8 rounded-lg text-[9px] uppercase font-black text-slate-700 tracking-wider flex items-center gap-1 py-1 px-2.5 shadow-xs">
                                    <x-heroicon-s-information-circle class="w-3.5 h-3.5 text-slate-400" />
                                    <span>Detail</span>
                                </button>
                                <button @click="startEdit(outlet)" 
                                        class="btn btn-xs btn-outline border-slate-200 hover:bg-slate-100 h-8 rounded-lg text-[9px] uppercase font-black text-slate-700 tracking-wider flex items-center gap-1 py-1 px-2.5 shadow-xs">
                                    <x-heroicon-s-pencil-square class="w-3.5 h-3.5 text-slate-400" />
                                    <span>Edit</span>
                                </button>
                                <button @click="selectOutlet(outlet)" 
                                        class="btn btn-xs btn-primary h-8 rounded-lg text-[9px] uppercase font-black text-white tracking-wider flex items-center gap-1 py-1 px-2.5 shadow-xs">
                                    <x-heroicon-s-camera class="w-3.5 h-3.5" />
                                    <span>Upload</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty State --}}
            <template x-if="outletsList.length === 0">
                <div class="bg-white border border-slate-100 rounded-3xl py-12 px-6 text-center shadow-xs flex-1 flex flex-col items-center justify-center">
                    <div class="flex flex-col items-center gap-3 text-slate-300" x-show="!selectedRegion && !selectedArea && !selectedBranch && !search">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                            <x-heroicon-o-map class="w-8 h-8 stroke-[1.5]" />
                        </div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-700">Pilih Wilayah Terlebih Dahulu</h4>
                        <p class="text-[10px] text-slate-400 max-w-[200px] mx-auto leading-normal font-semibold">
                            Gunakan filter wilayah atau cari nama toko untuk menampilkan data outlet.
                        </p>
                    </div>
                    <div class="flex flex-col items-center gap-3 text-slate-300" x-show="selectedRegion || selectedArea || selectedBranch || search">
                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mb-2">
                            <x-heroicon-o-magnifying-glass class="w-8 h-8 stroke-[1.5]" />
                        </div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-700">Toko Tidak Ditemukan</h4>
                        <p class="text-[10px] text-slate-400 max-w-[200px] mx-auto leading-normal font-semibold">
                            Tidak ada data outlet yang cocok dengan filter atau pencarian Anda.
                        </p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <footer class="text-center py-4 text-[9px] text-slate-400 font-semibold tracking-wider uppercase shrink-0">
            &copy; {{ date('Y') }} DevSiso &bull; RWO Mobile Photo Upload
        </footer>
    </main>

    {{-- Bottom Sheet: Filters --}}
    <div x-show="showFiltersSheet" 
         class="fixed inset-0 z-40" 
         x-cloak>
        <!-- Backdrop overlay -->
        <div x-show="showFiltersSheet"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showFiltersSheet = false"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>
        
        <!-- Sheet Body -->
        <div x-show="showFiltersSheet"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50">
             
             <!-- Handle -->
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <!-- Header -->
             <div class="px-5 pb-3 flex items-center justify-between border-b border-slate-100 shrink-0">
                 <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                     <x-heroicon-s-adjustments-horizontal class="w-4 h-4 text-primary" />
                     Filter Wilayah
                 </h3>
                 <button @click="showFiltersSheet = false" class="btn btn-ghost btn-circle btn-xs text-slate-400 hover:text-slate-600">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <!-- Scrollable Filters Container -->
             <div class="flex-1 overflow-y-auto p-5 space-y-4">
                 <!-- Region Dropdown -->
                 <div class="form-control w-full">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Region</span></label>
                     <select x-model="selectedRegion" class="select select-bordered select-sm h-11 w-full rounded-xl text-base bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20">
                         <option value="">-- Semua Region --</option>
                         <template x-for="r in regionsList" :key="r.region_code">
                             <option :value="r.region_code" x-text="r.region_name + ' (' + r.region_code + ')'"></option>
                         </template>
                     </select>
                 </div>

                 <!-- Area Dropdown -->
                 <div class="form-control w-full">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Area</span></label>
                     <select x-model="selectedArea" class="select select-bordered select-sm h-11 w-full rounded-xl text-base bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" :disabled="!selectedRegion">
                         <option value="">-- Semua Area --</option>
                         <template x-for="a in getFilteredAreas()" :key="a.area_code">
                             <option :value="a.area_code" x-text="a.area_name"></option>
                         </template>
                     </select>
                 </div>

                 <!-- Branch Dropdown -->
                 <div class="form-control w-full">
                     <label class="label py-1"><span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Cabang</span></label>
                     <select x-model="selectedBranch" class="select select-bordered select-sm h-11 w-full rounded-xl text-base bg-slate-50 border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" :disabled="!selectedArea">
                         <option value="">-- Semua Cabang --</option>
                         <template x-for="b in getFilteredBranches()" :key="b.branch_code">
                             <option :value="b.branch_name" x-text="b.branch_name"></option>
                         </template>
                     </select>
                 </div>
             </div>
             
             <!-- Footer Actions -->
             <div class="p-5 border-t border-slate-100 shrink-0 bg-slate-50 rounded-b-none flex items-center gap-3" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));">
                 <button @click="resetAllFilters(); showFiltersSheet = false" class="btn btn-outline border-slate-200 hover:bg-slate-200 flex-1 h-11 rounded-xl text-xs font-bold normal-case">
                     Reset
                 </button>
                 <button @click="showFiltersSheet = false" class="btn btn-primary flex-1 h-11 rounded-xl text-xs font-bold text-white normal-case shadow-md shadow-primary/20">
                     Terapkan
                 </button>
             </div>
        </div>
    </div>

    {{-- Bottom Sheet: Active Photo Upload --}}
    <div x-show="activeOutlet" 
         class="fixed inset-0 z-40" 
         x-cloak>
        <!-- Backdrop overlay -->
        <div x-show="activeOutlet"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cancelUpload()"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>
        
        <!-- Sheet Body -->
        <div x-show="activeOutlet"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50 overflow-hidden">
             
             <!-- Handle -->
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <!-- Header -->
             <div class="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                 <div class="min-w-0 pr-4">
                     <span class="badge badge-primary badge-xs font-mono font-bold rounded-lg px-2 text-[9px]" x-text="activeOutlet ? activeOutlet.customer_code : ''"></span>
                     <h4 class="text-xs font-black text-slate-900 mt-1 truncate" x-text="activeOutlet ? activeOutlet.customer_name : ''"></h4>
                     <p class="text-[9px] text-slate-400 font-semibold leading-normal truncate mt-0.5" x-text="activeOutlet ? activeOutlet.alamat : ''"></p>
                 </div>
                 <button @click="cancelUpload()" class="btn btn-ghost btn-circle btn-xs text-slate-400 hover:text-slate-600 flex-shrink-0">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <!-- Scrollable Content Form -->
             <div class="flex-1 overflow-y-auto p-5 space-y-4">
                 
                 <!-- FOTO TAMPAK DEPAN -->
                 <div class="form-control">
                     <label class="label py-1">
                         <span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Foto Tampak Depan</span>
                     </label>
                     
                     <!-- Upload Container -->
                     <div class="relative border border-dashed rounded-2xl transition-all duration-200 overflow-hidden min-h-[120px] flex flex-col items-center justify-center p-3"
                          :class="(fotoDepanPreview || (activeOutlet && activeOutlet.foto_toko2)) ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50 hover:bg-slate-100/50'">
                         <input type="file" 
                                accept="image/*" 
                                capture="environment" 
                                @change="handleFileSelect($event, 'foto_depan')" 
                                class="absolute inset-0 opacity-0 cursor-pointer z-10" />
                         
                         <template x-if="fotoDepanPreview">
                             <div class="w-full flex flex-col items-center">
                                 <img :src="fotoDepanPreview" class="w-full h-24 object-contain rounded-lg" />
                                 <span class="text-[9px] font-bold text-emerald-600 mt-1.5 flex items-center gap-1">
                                     <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Siap disimpan
                                 </span>
                             </div>
                         </template>
                         <template x-if="!fotoDepanPreview && activeOutlet && activeOutlet.foto_toko2">
                             <div class="w-full flex flex-col items-center">
                                 <img :src="getExistingPhotoUrl(activeOutlet.foto_toko2)" class="w-full h-24 object-contain rounded-lg opacity-80" />
                                 <span class="text-[9px] font-semibold text-slate-500 mt-1.5" x-text="activeOutlet.foto_toko2.startsWith('pending') ? 'Foto Tersimpan Offline (Siap Sinkron)' : 'Foto Saat Ini (Tampak Depan)'"></span>
                             </div>
                         </template>
                         <template x-if="!fotoDepanPreview && activeOutlet && !activeOutlet.foto_toko2">
                             <div class="w-full flex flex-col items-center py-2">
                                 <div class="w-9 h-9 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500">
                                     <x-heroicon-s-camera class="w-5 h-5" />
                                 </div>
                                 <span class="text-[11px] font-bold text-slate-700 mt-1.5">Ambil Foto Depan</span>
                                 <span class="text-[8px] text-slate-400 mt-0.5">Wajib langsung dari Kamera</span>
                             </div>
                         </template>

                         <!-- Upload Progress Indicator -->
                         <div x-show="fotoDepanState.isUploading" class="absolute inset-0 bg-white/95 flex flex-col items-center justify-center p-3 z-20 transition-all duration-300" x-cloak>
                             <span class="loading loading-spinner loading-sm text-primary"></span>
                             <span class="text-[10px] font-bold text-slate-600 mt-2 flex flex-col items-center gap-0.5">
                                 <span x-text="fotoDepanState.progress === 0 ? 'Memproses foto...' : 'Mengunggah...'"></span>
                                 <span x-show="fotoDepanState.progress > 0" x-text="fotoDepanState.progress + '%'"></span>
                             </span>
                             <progress x-show="fotoDepanState.progress > 0" class="progress progress-primary w-2/3 mt-2 h-1" :value="fotoDepanState.progress" max="100"></progress>
                         </div>
                     </div>
                     <template x-if="fotoDepanState.errorMessage">
                         <span class="text-error text-[9px] font-semibold mt-1 ml-1" x-text="fotoDepanState.errorMessage"></span>
                     </template>
                 </div>

                 <!-- FOTO TAMPAK DALAM -->
                 <div class="form-control">
                     <label class="label py-1">
                         <span class="label-text text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Foto Tampak Dalam</span>
                     </label>
                     
                     <!-- Upload Container -->
                     <div class="relative border border-dashed rounded-2xl transition-all duration-200 overflow-hidden min-h-[120px] flex flex-col items-center justify-center p-3"
                          :class="(fotoDalamPreview || (activeOutlet && activeOutlet.foto_toko3)) ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50 hover:bg-slate-100/50'">
                         <input type="file" 
                                accept="image/*" 
                                capture="environment" 
                                @change="handleFileSelect($event, 'foto_dalam')" 
                                class="absolute inset-0 opacity-0 cursor-pointer z-10" />
                         
                         <template x-if="fotoDalamPreview">
                             <div class="w-full flex flex-col items-center">
                                 <img :src="fotoDalamPreview" class="w-full h-24 object-contain rounded-lg" />
                                 <span class="text-[9px] font-bold text-emerald-600 mt-1.5 flex items-center gap-1">
                                     <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Siap disimpan
                                 </span>
                             </div>
                         </template>
                         <template x-if="!fotoDalamPreview && activeOutlet && activeOutlet.foto_toko3">
                             <div class="w-full flex flex-col items-center">
                                 <img :src="getExistingPhotoUrl(activeOutlet.foto_toko3)" class="w-full h-24 object-contain rounded-lg opacity-80" />
                                 <span class="text-[9px] font-semibold text-slate-500 mt-1.5" x-text="activeOutlet.foto_toko3.startsWith('pending') ? 'Foto Tersimpan Offline (Siap Sinkron)' : 'Foto Saat Ini (Tampak Dalam)'"></span>
                             </div>
                         </template>
                         <template x-if="!fotoDalamPreview && activeOutlet && !activeOutlet.foto_toko3">
                             <div class="w-full flex flex-col items-center py-2">
                                 <div class="w-9 h-9 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500">
                                     <x-heroicon-s-camera class="w-5 h-5" />
                                 </div>
                                 <span class="text-[11px] font-bold text-slate-700 mt-1.5">Ambil Foto Dalam</span>
                                 <span class="text-[8px] text-slate-400 mt-0.5">Wajib langsung dari Kamera</span>
                             </div>
                         </template>

                         <!-- Upload Progress Indicator -->
                         <div x-show="fotoDalamState.isUploading" class="absolute inset-0 bg-white/95 flex flex-col items-center justify-center p-3 z-20 transition-all duration-300" x-cloak>
                             <span class="loading loading-spinner loading-sm text-primary"></span>
                             <span class="text-[10px] font-bold text-slate-600 mt-2 flex flex-col items-center gap-0.5">
                                 <span x-text="fotoDalamState.progress === 0 ? 'Memproses foto...' : 'Mengunggah...'"></span>
                                 <span x-show="fotoDalamState.progress > 0" x-text="fotoDalamState.progress + '%'"></span>
                             </span>
                             <progress x-show="fotoDalamState.progress > 0" class="progress progress-primary w-2/3 mt-2 h-1" :value="fotoDalamState.progress" max="100"></progress>
                         </div>
                     </div>
                     <template x-if="fotoDalamState.errorMessage">
                         <span class="text-error text-[9px] font-semibold mt-1 ml-1" x-text="fotoDalamState.errorMessage"></span>
                     </template>
                 </div>
             </div>
             
             <!-- Footer Actions -->
             <div class="p-5 border-t border-slate-100 shrink-0 bg-slate-50 flex items-center gap-3" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));">
                 <button @click="cancelUpload()" class="btn btn-outline border-slate-200 hover:bg-slate-200 flex-1 h-11 rounded-xl text-xs font-bold normal-case">
                     Batal
                 </button>
                 <button @click="savePhotos()" 
                         class="btn btn-primary flex-1 h-11 rounded-xl text-xs font-bold text-white normal-case shadow-md shadow-primary/20"
                         :disabled="fotoDepanState.isUploading || fotoDalamState.isUploading || (!fotoDepanBlob && !fotoDalamBlob)">
                     Simpan Foto
                 </button>
             </div>
        </div>
    </div>

    {{-- Bottom Sheet: Outlet Details --}}
    <div x-show="detailOutlet" 
         class="fixed inset-0 z-40" 
         x-cloak>
        <!-- Backdrop overlay -->
        <div x-show="detailOutlet"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="detailOutlet = null"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>
        
        <!-- Sheet Body -->
        <div x-show="detailOutlet"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50 overflow-hidden">
             
             <!-- Handle -->
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <!-- Header -->
             <div class="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                 <div class="min-w-0 pr-4">
                     <span class="badge badge-secondary badge-xs font-mono font-bold rounded-lg px-2 text-[9px]" x-text="detailOutlet ? detailOutlet.customer_code : ''"></span>
                     <h4 class="text-xs font-black text-slate-900 mt-1 truncate" x-text="detailOutlet ? detailOutlet.customer_name : ''"></h4>
                 </div>
                 <button @click="detailOutlet = null" class="btn btn-ghost btn-circle btn-xs text-slate-400 hover:text-slate-600 flex-shrink-0">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <!-- Scrollable Content -->
             <div class="flex-1 overflow-y-auto p-5 space-y-5 text-xs font-medium text-slate-600">
                 
                 <!-- Section 1: Informasi Dasar -->
                 <div class="space-y-2">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-map class="w-3.5 h-3.5" />
                         Informasi Dasar Toko
                     </h5>
                     <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kode Toko</span>
                             <span class="font-mono font-bold text-slate-800" x-text="detailOutlet ? detailOutlet.customer_code : ''"></span>
                         </div>
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Status Validasi</span>
                             <span class="font-bold text-slate-800" :class="detailOutlet && detailOutlet.is_valid ? 'text-emerald-600' : 'text-slate-500'" x-text="detailOutlet && detailOutlet.is_valid ? 'Terverifikasi' : 'Belum Verifikasi'"></span>
                         </div>
                         <div class="col-span-2">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Toko</span>
                             <span class="font-extrabold text-slate-800" x-text="detailOutlet ? detailOutlet.customer_name : ''"></span>
                         </div>
                         <div class="col-span-2">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Alamat</span>
                             <span class="text-slate-700 leading-normal font-semibold" x-text="detailOutlet ? detailOutlet.alamat : ''"></span>
                         </div>
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Eskalink Code</span>
                             <span class="text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.eskalink_code) ? detailOutlet.eskalink_code : '-'"></span>
                         </div>
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">No. HP</span>
                             <span class="text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.no_hp) ? detailOutlet.no_hp : '-'"></span>
                         </div>
                         <div class="col-span-2 grid grid-cols-3 gap-2 border-t border-slate-100/50 pt-2.5 mt-1">
                             <div>
                                 <span class="text-[8px] font-extrabold uppercase tracking-wider text-slate-400 block">Region</span>
                                 <span class="text-slate-700 font-bold" x-text="detailOutlet ? detailOutlet.region_code : ''"></span>
                             </div>
                             <div>
                                 <span class="text-[8px] font-extrabold uppercase tracking-wider text-slate-400 block">Area</span>
                                 <span class="text-slate-700 font-bold" x-text="detailOutlet ? detailOutlet.area_code : ''"></span>
                             </div>
                             <div>
                                 <span class="text-[8px] font-extrabold uppercase tracking-wider text-slate-400 block">Cabang</span>
                                 <span class="text-slate-700 font-bold truncate block" x-text="detailOutlet ? detailOutlet.branch_name : ''"></span>
                             </div>
                         </div>
                     </div>
                 </div>

                 <!-- Section 2: Identitas & Pemilik -->
                 <div class="space-y-2">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-user-circle class="w-3.5 h-3.5" />
                         Data Pemilik & Identitas (KTP)
                     </h5>
                     <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                         <div class="col-span-2">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Pemilik Toko</span>
                             <span class="text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.nama_pemilik_toko) ? detailOutlet.nama_pemilik_toko : '-'"></span>
                         </div>
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama KTP</span>
                             <span class="text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.nama_ktp) ? detailOutlet.nama_ktp : '-'"></span>
                         </div>
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">NIK KTP</span>
                             <span class="font-mono text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.nik_ktp) ? detailOutlet.nik_ktp : '-'"></span>
                         </div>
                     </div>
                 </div>

                 <!-- Section 3: Data Bank & Rekening -->
                 <div class="space-y-2">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-credit-card class="w-3.5 h-3.5" />
                         Data Rekening & Bank
                     </h5>
                     <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Bank</span>
                             <span class="text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.nama_bank) ? detailOutlet.nama_bank : '-'"></span>
                         </div>
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">No. Rekening</span>
                             <span class="font-mono text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.no_rekening) ? detailOutlet.no_rekening : '-'"></span>
                         </div>
                         <div class="col-span-2">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Nama Pemilik Rekening</span>
                             <span class="text-slate-800 font-bold" x-text="(detailOutlet && detailOutlet.nama_pemilik_norek) ? detailOutlet.nama_pemilik_norek : '-'"></span>
                         </div>
                     </div>
                 </div>

                 <!-- Section 4: Validasi & Keterangan -->
                 <div class="space-y-2">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                         Status Validasi & Keterangan
                     </h5>
                     <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                         <div>
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Kelengkapan Data</span>
                             <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-bold tracking-wider uppercase border border-slate-200 mt-0.5"
                                   :class="detailOutlet && detailOutlet.status === 'Complete' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'"
                                   x-text="detailOutlet ? detailOutlet.status : 'Not Complete'"></span>
                         </div>
                         <div class="col-span-2">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 block mb-0.5">Keterangan</span>
                             <span class="text-slate-700 leading-normal" x-text="(detailOutlet && detailOutlet.keterangan) ? detailOutlet.keterangan : '-'"></span>
                         </div>
                     </div>
                 </div>

                 <!-- Section 5: Dokumentasi Foto -->
                 <div class="space-y-2">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-camera class="w-3.5 h-3.5" />
                         Dokumentasi Foto
                     </h5>
                     
                     <div class="grid grid-cols-2 gap-3">
                         <!-- Photo Toko (Spans 2 columns) -->
                         <div class="col-span-2 border border-slate-100 rounded-2xl p-3 bg-white flex flex-col items-center justify-center text-center">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Foto Toko Hasil Validasi</span>
                             <div class="w-full h-32 bg-slate-50 rounded-xl flex items-center justify-center overflow-hidden border border-slate-100">
                                 <template x-if="detailOutlet && detailOutlet.foto_toko">
                                     <img :src="getExistingPhotoUrl(detailOutlet.foto_toko)" class="w-full h-full object-contain hover:scale-105 transition-transform duration-300" />
                                 </template>
                                 <template x-if="detailOutlet && !detailOutlet.foto_toko">
                                     <div class="flex flex-col items-center text-slate-300">
                                         <x-heroicon-s-camera class="w-8 h-8" />
                                         <span class="text-[8px] font-bold text-slate-400 mt-1">Belum Ada Foto</span>
                                     </div>
                                 </template>
                             </div>
                         </div>

                         <!-- Photo Depan -->
                         <div class="border border-slate-100 rounded-2xl p-3 bg-white flex flex-col items-center justify-center text-center">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Tampak Depan</span>
                             <div class="w-full h-28 bg-slate-50 rounded-xl flex items-center justify-center overflow-hidden border border-slate-100">
                                 <template x-if="detailOutlet && detailOutlet.foto_toko2">
                                     <div class="relative w-full h-full">
                                         <template x-if="!detailOutlet.foto_toko2.startsWith('pending')">
                                             <img :src="getExistingPhotoUrl(detailOutlet.foto_toko2)" class="w-full h-full object-contain hover:scale-105 transition-transform duration-300" />
                                         </template>
                                         <template x-if="detailOutlet.foto_toko2.startsWith('pending')">
                                             <div class="flex flex-col items-center justify-center text-amber-600 h-full p-2">
                                                 <x-heroicon-s-clock class="w-7 h-7 text-amber-500 animate-pulse" />
                                                 <span class="text-[8px] font-bold mt-1 text-center">Offline (Siap Sinkron)</span>
                                             </div>
                                         </template>
                                     </div>
                                 </template>
                                 <template x-if="detailOutlet && !detailOutlet.foto_toko2">
                                     <div class="flex flex-col items-center text-slate-300">
                                         <x-heroicon-s-camera class="w-8 h-8" />
                                         <span class="text-[8px] font-bold text-slate-400 mt-1">Belum Ada Foto</span>
                                     </div>
                                 </template>
                             </div>
                         </div>

                         <!-- Photo Dalam -->
                         <div class="border border-slate-100 rounded-2xl p-3 bg-white flex flex-col items-center justify-center text-center">
                             <span class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Tampak Dalam</span>
                             <div class="w-full h-28 bg-slate-50 rounded-xl flex items-center justify-center overflow-hidden border border-slate-100">
                                 <template x-if="detailOutlet && detailOutlet.foto_toko3">
                                     <div class="relative w-full h-full">
                                         <template x-if="!detailOutlet.foto_toko3.startsWith('pending')">
                                             <img :src="getExistingPhotoUrl(detailOutlet.foto_toko3)" class="w-full h-full object-contain hover:scale-105 transition-transform duration-300" />
                                         </template>
                                         <template x-if="detailOutlet.foto_toko3.startsWith('pending')">
                                             <div class="flex flex-col items-center justify-center text-amber-600 h-full p-2">
                                                 <x-heroicon-s-clock class="w-7 h-7 text-amber-500 animate-pulse" />
                                                 <span class="text-[8px] font-bold mt-1 text-center">Offline (Siap Sinkron)</span>
                                             </div>
                                         </template>
                                     </div>
                                 </template>
                                 <template x-if="detailOutlet && !detailOutlet.foto_toko3">
                                     <div class="flex flex-col items-center text-slate-300">
                                         <x-heroicon-s-camera class="w-8 h-8" />
                                         <span class="text-[8px] font-bold text-slate-400 mt-1">Belum Ada Foto</span>
                                     </div>
                                 </template>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             
             <!-- Footer Actions -->
             <div class="p-5 border-t border-slate-100 shrink-0 bg-slate-50 flex items-center gap-3" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));">
                 <button @click="detailOutlet = null" class="btn btn-outline border-slate-200 hover:bg-slate-200 w-full h-11 rounded-xl text-xs font-bold normal-case">
                     Tutup
                 </button>
             </div>
        </div>
    </div>

    {{-- Bottom Sheet: Edit Outlet Data --}}
    <div x-show="editingOutlet" 
         class="fixed inset-0 z-40" 
         x-cloak>
        <!-- Backdrop overlay -->
        <div x-show="editingOutlet"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="cancelEdit()"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>
        
        <!-- Sheet Body -->
        <div x-show="editingOutlet"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50 overflow-hidden">
             
             <!-- Handle -->
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <!-- Header -->
             <div class="px-5 pb-3 pt-2 flex items-start justify-between border-b border-slate-100 shrink-0">
                 <div class="min-w-0 pr-4">
                     <span class="badge badge-primary badge-xs font-mono font-bold rounded-lg px-2 text-[9px]" x-text="editingOutlet ? editingOutlet.customer_code : ''"></span>
                     <h4 class="text-xs font-black text-slate-900 mt-1 truncate" x-text="editingOutlet ? 'Edit Data: ' + editingOutlet.customer_name : ''"></h4>
                 </div>
                 <button @click="cancelEdit()" class="btn btn-ghost btn-circle btn-xs text-slate-400 hover:text-slate-600 flex-shrink-0">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <!-- Scrollable Content Form -->
             <div class="flex-1 overflow-y-auto p-5 space-y-5">
                 
                 <!-- Section 1: Identitas Pemilik -->
                 <div class="space-y-3">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-user-circle class="w-3.5 h-3.5" />
                         Identitas Pemilik
                     </h5>
                     <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50 space-y-3">
                         <!-- Nama Pemilik Toko -->
                         <div class="form-control w-full">
                             <label class="label py-0.5"><span class="label-text text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Nama Pemilik Toko</span></label>
                             <input type="text" x-model="editNamaPemilikToko" class="input input-bordered input-sm h-10 w-full rounded-xl text-base bg-white border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                         </div>
                         
                         <!-- Nama KTP -->
                         <div class="form-control w-full">
                             <label class="label py-0.5"><span class="label-text text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Nama KTP</span></label>
                             <input type="text" x-model="editNamaKtp" class="input input-bordered input-sm h-10 w-full rounded-xl text-base bg-white border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                         </div>

                         <!-- NIK KTP -->
                         <div class="form-control w-full">
                             <label class="label py-0.5"><span class="label-text text-[9px] font-extrabold uppercase tracking-wider text-slate-400">NIK KTP</span></label>
                             <input type="text" 
                                    inputmode="numeric" 
                                    maxlength="16" 
                                    x-model="editNikKtp" 
                                    @input="editNikKtp = editNikKtp.replace(/[^\dxX]/g, '')"
                                    class="input input-bordered input-sm h-10 w-full rounded-xl text-base bg-white border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                         </div>

                         <!-- No HP -->
                         <div class="form-control w-full">
                             <label class="label py-0.5"><span class="label-text text-[9px] font-extrabold uppercase tracking-wider text-slate-400">No. HP</span></label>
                             <input type="text" 
                                    inputmode="tel" 
                                    x-model="editNoHp" 
                                    @input="editNoHp = editNoHp.replace(/[^\dxX]/g, '')"
                                    class="input input-bordered input-sm h-10 w-full rounded-xl text-base bg-white border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                         </div>
                     </div>
                 </div>

                 <!-- Section 2: Rekening & Bank -->
                 <div class="space-y-3">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-credit-card class="w-3.5 h-3.5" />
                         Rekening Bank
                     </h5>
                     <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100/50 space-y-3">
                         <!-- Nama Bank -->
                         <div class="form-control w-full relative" x-data="{ 
                              open: false, 
                              searchQuery: '',
                              banks: [
                                  'BANK BCA', 'BANK MANDIRI', 'BANK BNI', 'BANK BRI', 'BANK SYARIAH INDONESIA (BSI)',
                                  'BANK DANAMON', 'BANK CIMB NIAGA', 'BANK PERMATA', 'BANK BTN', 'BANK BUKOPIN',
                                  'BANK MEGA', 'BANK OCBC NISP', 'BANK MAYBANK', 'BANK BTPN / JENIUS', 'BANK JAGO', 
                                  'BANK ALLOBANK', 'BANK NEO COMMERCE', 'SEABANK', 'BANK SINARMAS', 
                                  'BPD JAWA TIMUR (BANK JATIM)', 'BPD JAWA TENGAH (BANK JATENG)', 'BPD JAWA BARAT BANTEN (BJB)', 
                                  'BPD DKI (BANK DKI)', 'BPD BALI', 'BPD D.I. YOGYAKARTA (BPD DIY)'
                              ],
                              get filteredBanks() {
                                  if (!this.searchQuery) return this.banks;
                                  return this.banks.filter(b => b.toLowerCase().includes(this.searchQuery.toLowerCase()));
                              }
                          }" @click.away="open = false">
                              <label class="label py-0.5"><span class="label-text text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Nama Bank</span></label>
                              <div class="relative">
                                  <input type="text" 
                                         placeholder="Pilih atau cari bank..."
                                         x-model="editNamaBank" 
                                         @focus="open = true; searchQuery = editNamaBank"
                                         @input="open = true; searchQuery = editNamaBank"
                                         class="input input-bordered input-sm h-10 w-full rounded-xl text-base bg-white border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20 pr-8" />
                                  <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                      <x-heroicon-s-chevron-down class="w-4 h-4" />
                                  </span>
                              </div>
                              <div x-show="open" 
                                   x-transition 
                                   class="absolute z-50 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white border border-slate-100 rounded-xl shadow-lg"
                                   style="top: 100%;"
                                   x-cloak>
                                  <template x-for="bank in filteredBanks" :key="bank">
                                      <button type="button"
                                              @click="editNamaBank = bank; open = false" 
                                              class="w-full text-left px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0"
                                              x-text="bank"></button>
                                  </template>
                                  <template x-if="filteredBanks.length === 0">
                                      <div class="px-4 py-2.5 text-xs text-slate-400 italic">
                                          Bank tidak ditemukan. Tekan luar untuk menggunakan teks khusus.
                                      </div>
                                  </template>
                              </div>
                          </div>

                         <!-- No Rekening -->
                         <div class="form-control w-full">
                             <label class="label py-0.5"><span class="label-text text-[9px] font-extrabold uppercase tracking-wider text-slate-400">No. Rekening</span></label>
                             <input type="text" x-model="editNoRekening" class="input input-bordered input-sm h-10 w-full rounded-xl text-base bg-white border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                         </div>

                         <!-- Nama Pemilik Rekening -->
                         <div class="form-control w-full">
                             <label class="label py-0.5"><span class="label-text text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Nama Pemilik Rekening</span></label>
                             <input type="text" x-model="editNamaPemilikNorek" class="input input-bordered input-sm h-10 w-full rounded-xl text-base bg-white border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                         </div>
                     </div>
                 </div>

                 <!-- Section 3: Foto KTP -->
                 <div class="space-y-3">
                     <h5 class="text-[10px] font-extrabold uppercase tracking-widest text-primary pl-1 flex items-center gap-1.5">
                         <x-heroicon-s-camera class="w-3.5 h-3.5" />
                         Foto KTP
                     </h5>
                     
                     <div class="relative border border-dashed rounded-2xl transition-all duration-200 overflow-hidden min-h-[120px] flex flex-col items-center justify-center p-3"
                          :class="(fotoKtpPreview || (editingOutlet && editingOutlet.foto_ktp)) ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200 bg-slate-50 hover:bg-slate-100/50'">
                         <input type="file" 
                                accept="image/*" 
                                capture="environment" 
                                @change="handleFileSelect($event, 'foto_ktp')" 
                                class="absolute inset-0 opacity-0 cursor-pointer z-10" />
                         
                         <template x-if="fotoKtpPreview">
                             <div class="w-full flex flex-col items-center">
                                 <img :src="fotoKtpPreview" class="w-full h-24 object-contain rounded-lg" />
                                 <span class="text-[9px] font-bold text-emerald-600 mt-1.5 flex items-center gap-1">
                                     <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Foto KTP siap disimpan
                                 </span>
                             </div>
                         </template>
                         <template x-if="!fotoKtpPreview && editingOutlet && editingOutlet.foto_ktp">
                             <div class="w-full flex flex-col items-center">
                                 <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                     <x-heroicon-s-shield-check class="w-5 h-5" />
                                 </div>
                                 <span class="text-[10px] font-bold text-emerald-600 mt-1.5">Foto KTP Sudah Terunggah</span>
                                 <span class="text-[8px] text-slate-400 mt-0.5">Ketuk untuk mengambil ulang / mengganti foto KTP</span>
                             </div>
                         </template>
                         <template x-if="!fotoKtpPreview && editingOutlet && !editingOutlet.foto_ktp">
                             <div class="w-full flex flex-col items-center py-2">
                                 <div class="w-9 h-9 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500">
                                     <x-heroicon-s-camera class="w-5 h-5" />
                                 </div>
                                 <span class="text-[11px] font-bold text-slate-700 mt-1.5">Ambil Foto KTP</span>
                                 <span class="text-[8px] text-slate-400 mt-0.5">Wajib langsung dari Kamera</span>
                             </div>
                         </template>

                         <!-- Upload Progress Indicator -->
                         <div x-show="fotoKtpState.isUploading" class="absolute inset-0 bg-white/95 flex flex-col items-center justify-center p-3 z-20 transition-all duration-300" x-cloak>
                             <span class="loading loading-spinner loading-sm text-primary"></span>
                             <span class="text-[10px] font-bold text-slate-600 mt-2 flex flex-col items-center gap-0.5">
                                 <span x-text="fotoKtpState.progress === 0 ? 'Memproses foto...' : 'Mengunggah...'"></span>
                                 <span x-show="fotoKtpState.progress > 0" x-text="fotoKtpState.progress + '%'"></span>
                             </span>
                             <progress x-show="fotoKtpState.progress > 0" class="progress progress-primary w-2/3 mt-2 h-1" :value="fotoKtpState.progress" max="100"></progress>
                         </div>
                     </div>
                     <template x-if="fotoKtpState.errorMessage">
                         <span class="text-error text-[9px] font-semibold mt-1 ml-1" x-text="fotoKtpState.errorMessage"></span>
                     </template>
                 </div>

             </div>
             
             <!-- Footer Actions -->
             <div class="p-5 border-t border-slate-100 shrink-0 bg-slate-50 flex items-center gap-3" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));">
                 <button @click="cancelEdit()" class="btn btn-outline border-slate-200 hover:bg-slate-200 flex-1 h-11 rounded-xl text-xs font-bold normal-case">
                     Batal
                 </button>
                 <button @click="saveEdits()" 
                         class="btn btn-primary flex-1 h-11 rounded-xl text-xs font-bold text-white normal-case shadow-md shadow-primary/20"
                         :disabled="fotoKtpState.isUploading">
                     Simpan Edit
                 </button>
             </div>
        </div>
    </div>

    {{-- Bottom Sheet: Panduan Penggunaan --}}
    <div x-show="showGuideSheet" 
         class="fixed inset-0 z-40" 
         x-cloak>
        <!-- Backdrop overlay -->
        <div x-show="showGuideSheet"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showGuideSheet = false"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"></div>
        
        <!-- Sheet Body -->
        <div x-show="showGuideSheet"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white rounded-t-[32px] shadow-[0_-10px_25px_-5px_rgba(0,0,0,0.15)] max-h-[85%] flex flex-col z-50 overflow-hidden">
             
             <!-- Handle -->
             <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0"></div>
             
             <!-- Header -->
             <div class="px-5 pb-3 pt-2 flex items-center justify-between border-b border-slate-100 shrink-0">
                 <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                     <x-heroicon-s-information-circle class="w-5 h-5 text-primary" />
                     Panduan Penggunaan
                 </h3>
                 <button @click="showGuideSheet = false" class="btn btn-ghost btn-circle btn-xs text-slate-400 hover:text-slate-600 flex-shrink-0">
                     <x-heroicon-s-x-mark class="w-5 h-5" />
                 </button>
             </div>
             
             <!-- Scrollable Content -->
             <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs font-medium text-slate-600 leading-relaxed">
                 
                 <!-- Step 1 -->
                 <div class="flex gap-3">
                     <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px] shrink-0">1</div>
                     <div class="space-y-1">
                         <h4 class="font-extrabold text-slate-800 text-[11px] uppercase tracking-wide">Cari & Saring Toko</h4>
                         <p class="text-slate-500">
                             Gunakan kolom pencarian di bagian atas untuk menemukan toko berdasarkan <strong>Nama</strong> atau <strong>Kode Toko</strong>. Anda juga bisa menekan tombol <strong>Filter (ikon slider)</strong> untuk memfilter daftar toko berdasarkan Region, Area, atau Cabang tertentu.
                         </p>
                     </div>
                 </div>

                 <!-- Step 2 -->
                 <div class="flex gap-3">
                     <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px] shrink-0">2</div>
                     <div class="space-y-1">
                         <h4 class="font-extrabold text-slate-800 text-[11px] uppercase tracking-wide">Lihat Detail Toko</h4>
                         <p class="text-slate-500">
                             Klik tombol <strong>Detail</strong> pada kartu toko untuk melihat informasi dasar, data identitas pemilik (NIK disensor demi keamanan), nomor rekening bank, dan semua dokumentasi foto toko yang telah terunggah ke server.
                         </p>
                     </div>
                 </div>

                 <!-- Step 3 -->
                 <div class="flex gap-3">
                     <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px] shrink-0">3</div>
                     <div class="space-y-1">
                         <h4 class="font-extrabold text-slate-800 text-[11px] uppercase tracking-wide">Ambil & Simpan Foto</h4>
                         <p class="text-slate-500">
                             Klik tombol <strong>Upload</strong> untuk membuka panel kamera. Pilih area <em>Foto Tampak Depan</em> or <em>Tampak Dalam</em> untuk mengaktifkan kamera HP Anda, ambil foto secara tegak lurus dan jelas, lalu klik <strong>Simpan Foto</strong>.
                         </p>
                     </div>
                 </div>

                 <!-- Step 4 -->
                 <div class="flex gap-3">
                     <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-[10px] shrink-0">
                         <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                     </div>
                     <div class="space-y-1">
                         <h4 class="font-extrabold text-amber-700 text-[11px] uppercase tracking-wide">Fitur Offline (Tanpa Sinyal)</h4>
                         <p class="text-slate-500">
                             Jika Anda berada di daerah bersinyal buruk (Offline), foto akan <strong>disimpan secara aman di memori HP Anda</strong> secara otomatis. Label status foto pada daftar toko akan berubah menjadi <strong>(Offline)</strong>.
                         </p>
                     </div>
                 </div>

                 <!-- Step 5 -->
                 <div class="flex gap-3">
                     <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-[10px] shrink-0">
                         <x-heroicon-s-arrow-path class="w-3.5 h-3.5" />
                     </div>
                     <div class="space-y-1">
                         <h4 class="font-extrabold text-emerald-700 text-[11px] uppercase tracking-wide">Sinkronisasi Data</h4>
                         <p class="text-slate-500">
                             Begitu HP Anda kembali mendapatkan sinyal internet (Online), bilah status <strong>Foto Menunggu Sinkronisasi</strong> akan otomatis muncul di bagian atas. Klik <strong>Sinkronisasi Sekarang</strong> untuk mengunggah semua foto offline ke server pusat.
                         </p>
                     </div>
                 </div>

                 <div class="bg-rose-50 border border-rose-100 rounded-2xl p-3.5 mt-2 flex items-start gap-2">
                     <x-heroicon-s-shield-exclamation class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" />
                     <div class="space-y-0.5">
                         <h5 class="text-[10px] font-black text-rose-800 uppercase tracking-wider">Perhatian Penting</h5>
                         <p class="text-[10px] text-rose-700 leading-normal">
                             Jangan menghapus cache browser smartphone Anda jika masih memiliki antrean foto offline yang belum terunggah agar data tidak hilang.
                         </p>
                     </div>
                 </div>

             </div>
             
             <!-- Footer Actions -->
             <div class="p-5 border-t border-slate-100 shrink-0 bg-slate-50 flex items-center gap-3" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));">
                 <button @click="showGuideSheet = false" class="btn btn-primary w-full h-11 rounded-xl text-xs font-bold text-white normal-case shadow-md shadow-primary/20">
                     Saya Mengerti
                 </button>
             </div>
        </div>
    </div>

</div>

{{-- Offline Master Data Script --}}
<script id="offline-master-data" type="application/json">
    {!! $offlineMasterDataJson !!}
</script>

<script>
    function mobileRwoApp(wire) {
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
            
            // Edit Outlet state variables
            editingOutlet: null,
            editNamaPemilikToko: '',
            editNamaKtp: '',
            editNikKtp: '',
            editNoHp: '',
            editNamaBank: '',
            editNoRekening: '',
            editNamaPemilikNorek: '',
            fotoKtpBlob: null,
            fotoKtpPreview: null,
            fotoKtpState: { isUploading: false, progress: 0, errorMessage: '' },
            pendingEditQueueCount: 0,
            
            // UI helper states
            showFiltersSheet: false,
            detailOutlet: null,
            showGuideSheet: false,
            
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                }, 3000);
            },
            
            getPendingSyncMessage() {
                let msg = '';
                if (this.pendingQueueCount > 0) {
                    msg += this.pendingQueueCount + ' Foto';
                }
                if (this.pendingEditQueueCount > 0) {
                    if (msg) msg += ' & ';
                    msg += this.pendingEditQueueCount + ' Perubahan Data';
                }
                return msg + ' Menunggu Sinkronisasi';
            },
            
            resetAllFilters() {
                this.selectedRegion = '';
                this.selectedArea = '';
                this.selectedBranch = '';
                this.search = '';
                this.activeOutlet = null;
                this.queryOutlets();
            },

            async init() {
                // Monitor connection status
                window.addEventListener('online', () => { 
                    this.isOffline = false; 
                    this.updateQueueCount(); 
                    this.showToast('Koneksi internet terhubung kembali. Memuat ulang halaman untuk memperbarui token keamanan...', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                });
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
                    const request = indexedDB.open('RWOOfflineDB', 3);
                    request.onupgradeneeded = (e) => {
                        const db = e.target.result;
                        if (!db.objectStoreNames.contains('outlets')) {
                            db.createObjectStore('outlets', { keyPath: 'id' });
                        }
                        if (!db.objectStoreNames.contains('uploadQueue')) {
                            db.createObjectStore('uploadQueue', { keyPath: 'id', autoIncrement: true });
                        }
                        if (!db.objectStoreNames.contains('editQueue')) {
                            db.createObjectStore('editQueue', { keyPath: 'id', autoIncrement: true });
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
                const scriptEl = document.getElementById('offline-master-data');
                if (!scriptEl) return;
                
                this.getDB().then(db => {
                    const transaction = db.transaction(['outlets'], 'readonly');
                    const store = transaction.objectStore('outlets');
                    const countReq = store.count();
                    
                    countReq.onsuccess = () => {
                        const count = countReq.result;
                        const hasRegions = localStorage.getItem('rwo_regions') !== null;
                        
                        // Seed if online OR if the DB is empty OR if localStorage is missing master data
                        if (!this.isOffline || count === 0 || !hasRegions) {
                            try {
                                const data = JSON.parse(scriptEl.textContent);
                                
                                // Perform the seeding in a write transaction
                                const writeTx = db.transaction(['outlets'], 'readwrite');
                                const writeStore = writeTx.objectStore('outlets');
                                writeStore.clear();
                                data.outlets.forEach(o => writeStore.add(o));
                                
                                writeTx.oncomplete = () => {
                                    localStorage.setItem('rwo_regions', JSON.stringify(data.regions));
                                    localStorage.setItem('rwo_areas', JSON.stringify(data.areas));
                                    localStorage.setItem('rwo_supervisors', JSON.stringify(data.supervisors));
                                    localStorage.setItem('rwo_branches', JSON.stringify(data.branches));
                                    localStorage.setItem('rwo_last_seed', Date.now());
                                    
                                    this.loadDropdowns();
                                    this.queryOutlets();
                                    console.log('IndexedDB seeded successfully' + (this.isOffline ? ' (Offline Mode Recovery)' : ''));
                                };
                            } catch (e) {
                                console.error('Failed to seed master data:', e);
                            }
                        }
                    };
                }).catch(err => {
                    console.error('Failed to check database for seeding:', err);
                });
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
            
            startEdit(outlet) {
                if (this.fotoKtpPreview) URL.revokeObjectURL(this.fotoKtpPreview);
                
                this.editingOutlet = outlet;
                this.editNamaPemilikToko = outlet.nama_pemilik_toko || '';
                this.editNamaKtp = outlet.nama_ktp || '';
                this.editNikKtp = outlet.nik_ktp || '';
                this.editNoHp = outlet.no_hp || '';
                this.editNamaBank = outlet.nama_bank || '';
                this.editNoRekening = outlet.no_rekening || '';
                this.editNamaPemilikNorek = outlet.nama_pemilik_norek || '';
                
                this.fotoKtpBlob = null;
                this.fotoKtpPreview = null;
                this.fotoKtpState = { isUploading: false, progress: 0, errorMessage: '' };
            },
            
            cancelEdit() {
                if (this.fotoKtpPreview) URL.revokeObjectURL(this.fotoKtpPreview);
                
                this.editingOutlet = null;
                this.fotoKtpBlob = null;
                this.fotoKtpPreview = null;
            },
            
            saveEdits() {
                if (!this.editingOutlet) return;
                
                // Form validation
                if (!this.editNamaPemilikToko.trim() || !this.editNamaKtp.trim() || 
                    !this.editNikKtp.trim() || !this.editNoHp.trim() || 
                    !this.editNamaBank.trim() || !this.editNoRekening.trim() || 
                    !this.editNamaPemilikNorek.trim()) {
                    this.showToast('Semua field identitas dan rekening harus diisi!', 'error');
                    return;
                }
                
                // NIK KTP validation: exactly 16 characters
                const nik = this.editNikKtp.trim();
                if (nik.length !== 16) {
                    this.showToast('NIK KTP harus tepat 16 digit!', 'error');
                    return;
                }
                const isMaskedNik = /^\d{12}xxxx$/i.test(nik);
                const isNumericNik = /^\d{16}$/.test(nik);
                if (!isMaskedNik && !isNumericNik) {
                    this.showToast('NIK KTP harus berupa 16 digit angka!', 'error');
                    return;
                }
                
                // Phone number validation: only digits (or masked)
                const noHp = this.editNoHp.trim();
                const isMaskedHp = /^\d+x+$/i.test(noHp);
                const isNumericHp = /^\d+$/.test(noHp);
                if (!isMaskedHp && !isNumericHp) {
                    this.showToast('Nomor HP hanya boleh berisi angka!', 'error');
                    return;
                }
                
                this.saveOfflineEdits();
            },
            
            saveOfflineEdits() {
                this.getDB().then(db => {
                    const transaction = db.transaction(['editQueue', 'outlets'], 'readwrite');
                    const queueStore = transaction.objectStore('editQueue');
                    const outletsStore = transaction.objectStore('outlets');
                    
                    const item = {
                        outlet_id: this.editingOutlet.id,
                        customer_code: this.editingOutlet.customer_code,
                        customer_name: this.editingOutlet.customer_name,
                        nama_pemilik_toko: this.editNamaPemilikToko,
                        nama_ktp: this.editNamaKtp,
                        nik_ktp: this.editNikKtp,
                        no_hp: this.editNoHp,
                        nama_bank: this.editNamaBank,
                        no_rekening: this.editNoRekening,
                        nama_pemilik_norek: this.editNamaPemilikNorek,
                        foto_ktp_blob: this.fotoKtpBlob,
                        timestamp: Date.now()
                    };
                    
                    queueStore.add(item);
                    
                    // Update local outlets representation in database so it reflects changes immediately
                    const getReq = outletsStore.get(this.editingOutlet.id);
                    getReq.onsuccess = (e) => {
                        const o = e.target.result;
                        if (o) {
                            o.nama_pemilik_toko = this.editNamaPemilikToko;
                            o.nama_ktp = this.editNamaKtp;
                            o.nik_ktp = this.editNikKtp;
                            o.no_hp = this.editNoHp;
                            o.nama_bank = this.editNamaBank;
                            o.no_rekening = this.editNoRekening;
                            o.nama_pemilik_norek = this.editNamaPemilikNorek;
                            
                            // Recalculate status completeness locally
                            const isComplete = this.editNamaPemilikToko.trim() && 
                                               this.editNamaKtp.trim() && 
                                               this.editNikKtp.trim() && 
                                               this.editNamaBank.trim() && 
                                               this.editNoRekening.trim() && 
                                               this.editNamaPemilikNorek.trim();
                            o.status = isComplete ? 'Complete' : 'Not Complete';
                            
                            if (this.fotoKtpBlob) {
                                o.foto_ktp = 'pending_ktp';
                            }
                            
                            outletsStore.put(o);
                        }
                    };
                    
                    transaction.oncomplete = () => {
                        this.cancelEdit();
                        this.updateQueueCount();
                        this.queryOutlets();
                        
                        if (this.isOffline) {
                            this.showToast('Perubahan data disimpan secara offline.');
                        } else {
                            this.showToast('Perubahan data disimpan. Menyinkronkan...');
                            this.startSync();
                        }
                    };
                });
            },
            
            getExistingPhotoUrl(path) {
                if (!path) return '';
                if (path.startsWith('pending')) {
                    return '';
                }
                return '/storage/' + path;
            },
            
            compressImage(file, maxDimension = 1000, quality = 0.7) {
                return new Promise((resolve, reject) => {
                    if (!file || !file.type.startsWith('image/')) {
                        return resolve(file);
                    }
                    
                    const img = new Image();
                    const objectUrl = URL.createObjectURL(file);
                    
                    img.onload = () => {
                        URL.revokeObjectURL(objectUrl);
                        let width = img.width;
                        let height = img.height;
                        
                        if (width > maxDimension || height > maxDimension) {
                            if (width > height) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            } else {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }
                        
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        
                        canvas.toBlob((blob) => {
                            if (blob) {
                                const filename = file.name ? file.name.replace(/\.[^/.]+$/, "") + ".jpg" : "photo.jpg";
                                const compressedFile = new File([blob], filename, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(compressedFile);
                            } else {
                                resolve(file);
                            }
                        }, 'image/jpeg', quality);
                    };
                    
                    img.onerror = () => {
                        URL.revokeObjectURL(objectUrl);
                        reject(new Error('Failed to load image for compression'));
                    };
                    
                    img.src = objectUrl;
                });
            },

            async handleFileSelect(event, propertyName) {
                const file = event.target.files[0];
                if (!file) return;
                
                let localData;
                if (propertyName === 'foto_depan') {
                    localData = this.fotoDepanState;
                } else if (propertyName === 'foto_dalam') {
                    localData = this.fotoDalamState;
                } else {
                    localData = this.fotoKtpState;
                }
                
                localData.isUploading = true;
                localData.progress = 0;
                localData.errorMessage = '';
                
                try {
                    const compressed = await this.compressImage(file);
                    
                    if (propertyName === 'foto_depan') {
                        this.fotoDepanBlob = compressed;
                        if (this.fotoDepanPreview) URL.revokeObjectURL(this.fotoDepanPreview);
                        this.fotoDepanPreview = URL.createObjectURL(compressed);
                    } else if (propertyName === 'foto_dalam') {
                        this.fotoDalamBlob = compressed;
                        if (this.fotoDalamPreview) URL.revokeObjectURL(this.fotoDalamPreview);
                        this.fotoDalamPreview = URL.createObjectURL(compressed);
                    } else if (propertyName === 'foto_ktp') {
                        this.fotoKtpBlob = compressed;
                        if (this.fotoKtpPreview) URL.revokeObjectURL(this.fotoKtpPreview);
                        this.fotoKtpPreview = URL.createObjectURL(compressed);
                    }
                } catch (err) {
                    console.error(err);
                    localData.errorMessage = 'Gagal memproses gambar. Menggunakan file asli.';
                    
                    if (propertyName === 'foto_depan') {
                        this.fotoDepanBlob = file;
                        if (this.fotoDepanPreview) URL.revokeObjectURL(this.fotoDepanPreview);
                        this.fotoDepanPreview = URL.createObjectURL(file);
                    } else if (propertyName === 'foto_dalam') {
                        this.fotoDalamBlob = file;
                        if (this.fotoDalamPreview) URL.revokeObjectURL(this.fotoDalamPreview);
                        this.fotoDalamPreview = URL.createObjectURL(file);
                    } else if (propertyName === 'foto_ktp') {
                        this.fotoKtpBlob = file;
                        if (this.fotoKtpPreview) URL.revokeObjectURL(this.fotoKtpPreview);
                        this.fotoKtpPreview = URL.createObjectURL(file);
                    }
                } finally {
                    localData.isUploading = false;
                }
            },
            
            getCurrentLocation() {
                return new Promise((resolve) => {
                    if (!navigator.geolocation) {
                        console.warn('Geolocation is not supported by this browser.');
                        resolve(null);
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            resolve({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            });
                        },
                        (error) => {
                            console.warn('Geolocation error:', error);
                            resolve(null);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                });
            },

            async savePhotos() {
                if (!this.activeOutlet) return;
                
                let lat = null;
                let lon = null;
                
                // Retrieve coordinates only if ONLINE and outlet is NOT valid (not true)
                if (!this.isOffline && !this.activeOutlet.is_valid) {
                    this.showToast('Mengambil koordinat GPS lokasi Anda saat ini...', 'success');
                    const loc = await this.getCurrentLocation();
                    if (loc) {
                        lat = loc.latitude;
                        lon = loc.longitude;
                        this.showToast('Lokasi GPS berhasil diambil.');
                    } else {
                        this.showToast('Gagal mengambil lokasi GPS. Melanjutkan simpan tanpa koordinat...', 'error');
                    }
                }
                
                this.saveOfflinePhotos(lat, lon);
            },
            
            saveOfflinePhotos(lat, lon) {
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
                        latitude: lat,
                        longitude: lon,
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
                    return Promise.all([
                        new Promise((resolve) => {
                            const transaction = db.transaction(['uploadQueue'], 'readonly');
                            const store = transaction.objectStore('uploadQueue');
                            const countReq = store.count();
                            countReq.onsuccess = () => {
                                this.pendingQueueCount = countReq.result;
                                resolve();
                            };
                        }),
                        new Promise((resolve) => {
                            const transaction = db.transaction(['editQueue'], 'readonly');
                            const store = transaction.objectStore('editQueue');
                            const countReq = store.count();
                            countReq.onsuccess = () => {
                                this.pendingEditQueueCount = countReq.result;
                                resolve();
                            };
                        })
                    ]);
                });
            },
            
            clearSyncQueue() {
                if (confirm('Apakah Anda yakin ingin menghapus antrean data offline? Semua foto dan perubahan data yang belum disinkronkan akan hilang.')) {
                    this.getDB().then(db => {
                        const transaction = db.transaction(['uploadQueue', 'editQueue', 'outlets'], 'readwrite');
                        transaction.objectStore('uploadQueue').clear();
                        transaction.objectStore('editQueue').clear();
                        
                        const outletsStore = transaction.objectStore('outlets');
                        outletsStore.getAll().onsuccess = (e) => {
                            const outlets = e.target.result;
                            outlets.forEach(o => {
                                if (o.foto_toko2 === 'pending_depan') o.foto_toko2 = null;
                                if (o.foto_toko3 === 'pending_dalam') o.foto_toko3 = null;
                                if (o.foto_ktp === 'pending_ktp') o.foto_ktp = null;
                                outletsStore.put(o);
                            });
                        };
                        
                        transaction.oncomplete = () => {
                            this.showToast('Antrean data offline berhasil dihapus.');
                            this.updateQueueCount();
                            this.queryOutlets();
                        };
                    }).catch(err => {
                        console.error('Failed to clear queue:', err);
                        indexedDB.deleteDatabase('RWOOfflineDB');
                        location.reload();
                    });
                }
            },
            
            uploadFilePromise(propertyName, blob) {
                return new Promise((resolve, reject) => {
                    const timeout = setTimeout(() => {
                        reject(new Error('Batas waktu unggah (35 detik) terlampaui.'));
                    }, 35000);
                    
                    wire.upload(
                        propertyName,
                        blob,
                        () => {
                            clearTimeout(timeout);
                            resolve();
                        },
                        (err) => {
                            clearTimeout(timeout);
                            reject(new Error(err || 'Gagal mengunggah ke server.'));
                        },
                        (event) => {
                            let localState;
                            if (propertyName === 'foto_depan') {
                                localState = this.fotoDepanState;
                            } else if (propertyName === 'foto_dalam') {
                                localState = this.fotoDalamState;
                            } else {
                                localState = this.fotoKtpState;
                            }
                            localState.progress = event.detail.progress;
                        }
                    );
                });
            },
            
            async startSync() {
                if (this.isOffline || this.isSyncing) return;
                
                this.isSyncing = true;
                let syncSuccess = false;
                
                try {
                    const db = await this.getDB();
                    
                    // 1. Process uploadQueue (photos)
                    const uploadTx = db.transaction(['uploadQueue'], 'readonly');
                    const uploadStore = uploadTx.objectStore('uploadQueue');
                    const uploadQueueList = await new Promise((resolve, reject) => {
                        const req = uploadStore.getAll();
                        req.onsuccess = () => resolve(req.result);
                        req.onerror = () => reject(req.error);
                    });
                    
                    // 2. Process editQueue (owner/bank details and KTP photos)
                    const editTx = db.transaction(['editQueue'], 'readonly');
                    const editStore = editTx.objectStore('editQueue');
                    const editQueueList = await new Promise((resolve, reject) => {
                        const req = editStore.getAll();
                        req.onsuccess = () => resolve(req.result);
                        req.onerror = () => reject(req.error);
                    });
                    
                    if (uploadQueueList.length === 0 && editQueueList.length === 0) {
                        this.isSyncing = false;
                        return;
                    }
                    
                    this.syncTotal = uploadQueueList.length + editQueueList.length;
                    this.syncCurrent = 0;
                    this.syncProgress = 0;
                    
                    // Process photos first
                    for (const item of uploadQueueList) {
                        this.syncCurrent++;
                        this.syncProgress = Math.round(((this.syncCurrent - 1) / this.syncTotal) * 100);
                        
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
                        // Trigger backend save offline photos directly with coordinates as arguments
                        await wire.savePhotosForOutletOffline(item.outlet_id, item.latitude, item.longitude);
                        
                        // Delete from offline queue
                        await new Promise((resolve, reject) => {
                            const delTx = db.transaction(['uploadQueue'], 'readwrite');
                            const delStore = delTx.objectStore('uploadQueue');
                            const delReq = delStore.delete(item.id);
                            delReq.onsuccess = () => resolve();
                            delReq.onerror = () => reject(delReq.error);
                        });
                    }
                    
                    // Process text edits & KTP photos next
                    for (const item of editQueueList) {
                        this.syncCurrent++;
                        this.syncProgress = Math.round(((this.syncCurrent - 1) / this.syncTotal) * 100);
                        
                        // Upload KTP if exists
                        if (item.foto_ktp_blob) {
                            let fileToUpload = item.foto_ktp_blob;
                            if (!(fileToUpload instanceof File) || !fileToUpload.name) {
                                const mimeType = item.foto_ktp_blob.type || 'image/jpeg';
                                const ext = mimeType === 'image/png' ? 'png' : 'jpg';
                                fileToUpload = new File([item.foto_ktp_blob], 'ktp_' + item.outlet_id + '_' + Date.now() + '.' + ext, { type: mimeType });
                            }
                            await this.uploadFilePromise('foto_ktp', fileToUpload);
                        }
                        
                        // Save edits offline backend call
                        await wire.saveEditsForOutletOffline(
                            item.outlet_id,
                            item.nama_pemilik_toko,
                            item.nama_ktp,
                            item.nik_ktp,
                            item.no_hp,
                            item.nama_bank,
                            item.no_rekening,
                            item.nama_pemilik_norek
                        );
                        
                        // Delete from offline edits queue
                        await new Promise((resolve, reject) => {
                            const delTx = db.transaction(['editQueue'], 'readwrite');
                            const delStore = delTx.objectStore('editQueue');
                            const delReq = delStore.delete(item.id);
                            delReq.onsuccess = () => resolve();
                            delReq.onerror = () => reject(delReq.error);
                        });
                    }
                    
                    this.syncProgress = 100;
                    syncSuccess = true;
                    this.showToast('Semua data offline berhasil disinkronisasi!');
                    
                } catch (e) {
                    console.error('Sync failed:', e);
                    this.showToast('Gagal sinkronisasi: ' + e.message, 'error');
                } finally {
                    this.isSyncing = false;
                    await this.updateQueueCount();
                    this.queryOutlets();
                    await wire.$refresh();
                    
                    // Auto-trigger next sync loop only if sync succeeded
                    if (syncSuccess && !this.isOffline && (this.pendingQueueCount > 0 || this.pendingEditQueueCount > 0)) {
                        this.startSync();
                    }
                }
            }
        };
    }
</script>
