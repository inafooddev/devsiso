<div class="w-full max-w-md mx-auto px-4 py-6 flex-1 flex flex-col gap-6" x-data="{ 
    scrollToActive() {
        $nextTick(() => {
            const el = document.getElementById('active-upload-panel');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    },
    compressAndUpload(event, propertyName, localData) {
        const file = event.target.files[0];
        if (!file) return;

        localData.isUploading = true;
        localData.progress = 0;
        localData.errorMessage = '';

        if (!file.type.startsWith('image/')) {
            this.uploadToLivewire(file, propertyName, localData);
            return;
        }

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (e) => {
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const maxDimension = 1200;

                if (width > maxDimension || height > maxDimension) {
                    if (width > height) {
                        height = Math.round((height * maxDimension) / width);
                        width = maxDimension;
                    } else {
                        width = Math.round((width * maxDimension) / height);
                        height = maxDimension;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (blob) {
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        this.uploadToLivewire(compressedFile, propertyName, localData);
                    } else {
                        this.uploadToLivewire(file, propertyName, localData);
                    }
                }, 'image/jpeg', 0.75);
            };
            img.onerror = () => {
                this.uploadToLivewire(file, propertyName, localData);
            };
        };
        reader.onerror = () => {
            this.uploadToLivewire(file, propertyName, localData);
        };
    },
    uploadToLivewire(file, propertyName, localData) {
        @this.upload(
            propertyName,
            file,
            () => {
                localData.isUploading = false;
                localData.progress = 100;
            },
            () => {
                localData.isUploading = false;
                localData.progress = 0;
                localData.errorMessage = 'Gagal mengunggah foto. Silakan coba lagi.';
            },
            (event) => {
                localData.progress = event.detail.progress;
            }
        );
    }
}">
    {{-- Header --}}
    <header class="flex items-center justify-between bg-base-100/60 backdrop-blur-md border border-base-300 rounded-3xl p-4 shadow-lg shadow-base-300/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                <x-heroicon-s-camera class="w-6 h-6 animate-pulse" />
            </div>
            <div>
                <h1 class="text-sm font-black uppercase tracking-wider text-base-content/90">Sales RWO</h1>
                <p class="text-[10px] font-bold text-primary tracking-widest uppercase">Photo Upload</p>
            </div>
        </div>
        <button @click="toggleTheme()" class="btn btn-ghost btn-circle btn-sm text-base-content/75 hover:bg-base-200/50">
            <template x-if="isDark">
                <x-heroicon-s-sun class="w-5 h-5" />
            </template>
            <template x-if="!isDark">
                <x-heroicon-s-moon class="w-5 h-5" />
            </template>
        </button>
    </header>

    {{-- Banner / Instructions --}}
    <div class="card bg-gradient-to-tr from-primary/15 via-secondary/10 to-base-100 border border-primary/20 rounded-3xl p-5 shadow-md">
        <h2 class="text-sm font-extrabold text-base-content flex items-center gap-2">
            <x-heroicon-s-information-circle class="w-5 h-5 text-primary" />
            Panduan Sales Team
        </h2>
        <p class="text-xs text-base-content/70 mt-2 leading-relaxed">
            Gunakan filter wilayah atau cari nama/kode toko untuk menemukan outlet. Klik <strong>Upload Foto</strong> untuk mengambil foto tampak depan dan dalam toko secara real-time.
        </p>
    </div>

    {{-- Session Message Toasts --}}
    @if (session()->has('message'))
        <div class="alert alert-success shadow-lg border border-success/30 rounded-2xl flex items-start gap-2.5 p-3">
            <x-heroicon-s-check-circle class="w-5 h-5 text-success flex-shrink-0 mt-0.5" />
            <div class="text-xs font-semibold text-success-content/90">
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error shadow-lg border border-error/30 rounded-2xl flex items-start gap-2.5 p-3">
            <x-heroicon-s-x-circle class="w-5 h-5 text-error flex-shrink-0 mt-0.5" />
            <div class="text-xs font-semibold text-error-content/90">
                {{ session('error') }}
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
            <select wire:model.live="selectedRegion" class="select select-bordered select-sm h-11 w-full rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">-- Semua Region --</option>
                @foreach($regions as $r)
                    <option value="{{ $r->region_code }}">{{ $r->region_name }} ({{ $r->region_code }})</option>
                @endforeach
            </select>
        </div>

        {{-- Area Dropdown --}}
        <div class="form-control w-full">
            <label class="label py-1"><span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Area</span></label>
            <select wire:model.live="selectedArea" class="select select-bordered select-sm h-11 w-full rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20" {{ empty($selectedRegion) ? 'disabled' : '' }}>
                <option value="">-- Semua Area --</option>
                @foreach($areas as $a)
                    <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Branch Dropdown --}}
        <div class="form-control w-full">
            <label class="label py-1"><span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Cabang</span></label>
            <select wire:model.live="selectedBranch" class="select select-bordered select-sm h-11 w-full rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20" {{ empty($selectedArea) ? 'disabled' : '' }}>
                <option value="">-- Semua Cabang --</option>
                @foreach($branches as $b)
                    <option value="{{ $b->branch_name }}">{{ $b->branch_name }}</option>
                @endforeach
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
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Cari Kode atau Nama Toko..." 
                       class="input input-bordered input-sm h-11 w-full pl-9 rounded-2xl text-xs bg-base-200 border-base-300 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                @if(!empty($search))
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-base-content/40 hover:text-base-content">
                        <x-heroicon-s-x-mark class="w-4 h-4" />
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Shop Lists / Active Upload Form --}}
    <div class="flex-1 flex flex-col gap-4">
        {{-- If form is active, display the Upload Panel --}}
        @if($activeOutletId)
            <div id="active-upload-panel" x-init="scrollToActive()" class="card bg-base-100 border border-primary/20 rounded-3xl p-5 shadow-2xl shadow-primary/5 flex flex-col gap-5">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="badge badge-primary badge-sm font-mono font-bold rounded-lg px-2">{{ $outletCode }}</span>
                        <h4 class="text-sm font-extrabold text-base-content mt-1">{{ $outletName }}</h4>
                        <p class="text-xs text-base-content/40 mt-1 leading-relaxed">{{ $outletAlamat }}</p>
                    </div>
                    <button wire:click="cancelUpload" class="btn btn-ghost btn-circle btn-sm text-base-content/40 hover:bg-base-200">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="divider my-0"></div>

                {{-- Upload Fields Container --}}
                <div class="flex flex-col gap-5">
                    
                    {{-- FOTO TAMPAK DEPAN --}}
                    <div class="form-control"
                         x-data="{ isUploading: false, progress: 0, errorMessage: '' }">
                        <label class="label py-1">
                            <span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Foto Tampak Depan</span>
                        </label>
                        
                        {{-- Dropzone / Capture Button --}}
                        <div class="relative border-2 border-dashed {{ $foto_depan ? 'border-success/40 bg-success/5' : 'border-base-300 bg-base-200' }} hover:bg-base-200/50 rounded-2xl transition-all duration-200 overflow-hidden min-h-[140px] flex flex-col items-center justify-center p-4">
                            <input type="file" 
                                   accept="image/*" 
                                   capture="environment" 
                                   @change="compressAndUpload($event, 'foto_depan', $data)" 
                                   class="absolute inset-0 opacity-0 cursor-pointer z-10" />
                            
                            @if ($this->getFotoDepanPreview())
                                <img src="{{ $this->getFotoDepanPreview() }}" class="w-full h-32 object-contain rounded-xl" />
                                <span class="text-[10px] font-bold text-success mt-2 flex items-center gap-1">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Siap diunggah (Terkompres)
                                </span>
                            @elseif ($existing_foto_depan)
                                <img src="{{ asset('storage/' . $existing_foto_depan) }}" class="w-full h-32 object-contain rounded-xl opacity-60" />
                                <span class="text-[10px] font-semibold text-base-content/50 mt-2">Foto Saat Ini (Tampak Depan)</span>
                            @else
                                <x-heroicon-s-camera class="w-8 h-8 text-base-content/30" />
                                <span class="text-xs font-bold text-base-content/65 mt-2">Ambil Foto Kamera</span>
                                <span class="text-[9px] text-base-content/40 mt-0.5">Wajib langsung dari Kamera</span>
                            @endif

                            {{-- Upload Progress Indicator --}}
                            <div x-show="isUploading" class="absolute inset-0 bg-base-100/90 flex flex-col items-center justify-center p-4 z-20 transition-all duration-300" x-cloak>
                                <span class="loading loading-spinner loading-md text-primary"></span>
                                <span class="text-xs font-bold text-base-content/70 mt-2 flex flex-col items-center gap-1">
                                    <span>Mengompres &amp; Mengunggah...</span>
                                    <span x-text="progress + '%'"></span>
                                </span>
                                <progress class="progress progress-primary w-2/3 mt-2" :value="progress" max="100"></progress>
                            </div>
                        </div>
                        <template x-if="errorMessage">
                            <span class="text-error text-[10px] font-semibold mt-1 ml-1" x-text="errorMessage"></span>
                        </template>
                        @error('foto_depan') <span class="text-error text-[10px] font-semibold mt-1 ml-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- FOTO TAMPAK DALAM --}}
                    <div class="form-control"
                         x-data="{ isUploading: false, progress: 0, errorMessage: '' }">
                        <label class="label py-1">
                            <span class="label-text text-[11px] font-bold uppercase tracking-wider text-base-content/50">Foto Tampak Dalam</span>
                        </label>
                        
                        {{-- Dropzone / Capture Button --}}
                        <div class="relative border-2 border-dashed {{ $foto_dalam ? 'border-success/40 bg-success/5' : 'border-base-300 bg-base-200' }} hover:bg-base-200/50 rounded-2xl transition-all duration-200 overflow-hidden min-h-[140px] flex flex-col items-center justify-center p-4">
                            <input type="file" 
                                   accept="image/*" 
                                   capture="environment" 
                                   @change="compressAndUpload($event, 'foto_dalam', $data)" 
                                   class="absolute inset-0 opacity-0 cursor-pointer z-10" />
                            
                            @if ($this->getFotoDalamPreview())
                                <img src="{{ $this->getFotoDalamPreview() }}" class="w-full h-32 object-contain rounded-xl" />
                                <span class="text-[10px] font-bold text-success mt-2 flex items-center gap-1">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Siap diunggah (Terkompres)
                                </span>
                            @elseif ($existing_foto_dalam)
                                <img src="{{ asset('storage/' . $existing_foto_dalam) }}" class="w-full h-32 object-contain rounded-xl opacity-60" />
                                <span class="text-[10px] font-semibold text-base-content/50 mt-2">Foto Saat Ini (Tampak Dalam)</span>
                            @else
                                <x-heroicon-s-camera class="w-8 h-8 text-base-content/30" />
                                <span class="text-xs font-bold text-base-content/65 mt-2">Ambil Foto Kamera</span>
                                <span class="text-[9px] text-base-content/40 mt-0.5">Wajib langsung dari Kamera</span>
                            @endif

                            {{-- Upload Progress Indicator --}}
                            <div x-show="isUploading" class="absolute inset-0 bg-base-100/90 flex flex-col items-center justify-center p-4 z-20 transition-all duration-300" x-cloak>
                                <span class="loading loading-spinner loading-md text-primary"></span>
                                <span class="text-xs font-bold text-base-content/70 mt-2 flex flex-col items-center gap-1">
                                    <span>Mengompres &amp; Mengunggah...</span>
                                    <span x-text="progress + '%'"></span>
                                </span>
                                <progress class="progress progress-primary w-2/3 mt-2" :value="progress" max="100"></progress>
                            </div>
                        </div>
                        <template x-if="errorMessage">
                            <span class="text-error text-[10px] font-semibold mt-1 ml-1" x-text="errorMessage"></span>
                        </template>
                        @error('foto_dalam') <span class="text-error text-[10px] font-semibold mt-1 ml-1">{{ $message }}</span> @enderror
                    </div>

                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 mt-2">
                    <button wire:click="cancelUpload" class="btn btn-outline border-base-300 hover:bg-base-200 flex-1 h-12 rounded-2xl text-xs normal-case">
                        Batal
                    </button>
                    <button wire:click="savePhotos" 
                            class="btn btn-primary flex-1 h-12 rounded-2xl text-xs normal-case shadow-lg shadow-primary/20"
                            wire:loading.attr="disabled"
                            wire:target="savePhotos, foto_depan, foto_dalam">
                        <span wire:loading wire:target="savePhotos" class="loading loading-spinner loading-xs mr-1"></span>
                        Simpan Foto
                    </button>
                </div>
            </div>
        @endif

        {{-- Outlet Cards --}}
        <div class="flex flex-col gap-3">
            @forelse($outlets as $outlet)
                <div class="card bg-base-100 border border-base-300 rounded-3xl p-4 shadow-sm hover:shadow-md transition-all duration-200 {{ $activeOutletId === $outlet->id ? 'ring-2 ring-primary ring-offset-2 bg-primary/5' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <span class="badge badge-sm badge-outline border-base-300 text-secondary font-mono font-bold rounded-lg px-2">
                                {{ $outlet->customer_code }}
                            </span>
                            <h4 class="text-xs font-black text-base-content/85 mt-1.5 truncate">{{ $outlet->customer_name }}</h4>
                            <p class="text-[11px] text-base-content/40 mt-1 leading-normal truncate">{{ $outlet->alamat }}</p>
                            
                            {{-- Photo Status Badges --}}
                            <div class="flex items-center gap-3 mt-3">
                                <div class="flex items-center gap-1">
                                    @if($outlet->foto_toko2)
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-success" />
                                        <span class="text-[10px] font-bold text-base-content/50">Depan</span>
                                    @else
                                        <x-heroicon-s-x-circle class="w-4 h-4 text-error/40" />
                                        <span class="text-[10px] font-semibold text-base-content/40">Depan</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1">
                                    @if($outlet->foto_toko3)
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-success" />
                                        <span class="text-[10px] font-bold text-base-content/50">Dalam</span>
                                    @else
                                        <x-heroicon-s-x-circle class="w-4 h-4 text-error/40" />
                                        <span class="text-[10px] font-semibold text-base-content/40">Dalam</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="flex-shrink-0 self-center">
                            @if($activeOutletId !== $outlet->id)
                                <button wire:click="selectOutlet({{ $outlet->id }})" 
                                        class="btn btn-primary btn-sm rounded-xl text-[10px] uppercase font-bold normal-case tracking-wider py-1 px-3">
                                    Upload Foto
                                </button>
                            @else
                                <button wire:click="cancelUpload" 
                                        class="btn btn-outline border-base-300 btn-sm rounded-xl text-[10px] uppercase font-bold normal-case tracking-wider py-1 px-3">
                                    Tutup
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="card bg-base-100 border border-base-300 rounded-3xl py-12 px-6 text-center shadow-sm">
                    @if(empty($selectedRegion) && empty($selectedArea) && empty($selectedBranch) && empty($search))
                        <div class="flex flex-col items-center gap-3 text-base-content/30">
                            <x-heroicon-o-map class="w-12 h-12 stroke-[1.5]" />
                            <h4 class="text-xs font-bold uppercase tracking-wider">Pilih Wilayah Terlebih Dahulu</h4>
                            <p class="text-[10px] text-base-content/40 max-w-[200px] mx-auto leading-normal">
                                Silakan gunakan filter wilayah di atas atau cari nama toko untuk menampilkan data outlet.
                            </p>
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-3 text-base-content/30">
                            <x-heroicon-o-magnifying-glass class="w-12 h-12 stroke-[1.5]" />
                            <h4 class="text-xs font-bold uppercase tracking-wider">Toko Tidak Ditemukan</h4>
                            <p class="text-[10px] text-base-content/40 max-w-[200px] mx-auto leading-normal">
                                Tidak ada data outlet yang cocok dengan filter atau pencarian Anda.
                            </p>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    {{-- Footer --}}
    <footer class="text-center py-4 text-[10px] text-base-content/30 font-semibold tracking-wider uppercase mt-auto">
        &copy; {{ date('Y') }} DevSiso &bull; RWO Mobile Photo Upload
    </footer>
</div>
