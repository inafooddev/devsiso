<div>
    <x-slot name="title">Data Master Distributor</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content">
        {{-- Notifikasi --}}
        <div class="mb-6">
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">{{ session('message') }}</div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                </div>
            @endif
        </div>

        <x-card flush title="Master Distributor" icon="truck" subtitle="Kelola data mitra distributor dan mapping wilayah operasional" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Export & Sync --}}
                    @if(auth()->user()->hasRole('admin'))
                        <button wire:click="synchronize" wire:loading.attr="disabled" class="btn btn-sm btn-ghost rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200">
                            <x-heroicon-s-arrow-path wire:loading.class="animate-spin" wire:target="synchronize" class="w-4 h-4" />
                            Sync
                        </button>
                    @endif
                    
                    <button wire:click="export" wire:loading.attr="disabled" class="btn btn-sm btn-ghost rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200">
                        <x-heroicon-s-document-arrow-down wire:loading.remove wire:target="export" class="w-4 h-4 text-success" />
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                        Export
                    </button>

                    <div class="divider divider-horizontal mx-0 h-8"></div>

                    {{-- Tombol Tambah --}}
                    @unless(auth()->user()->hasRole('guest'))
                    <button wire:click="openCreateModal" class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah
                    </button>
                    @endunless
                </div>
            </x-slot:actions>

            {{-- Filter Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 px-6 py-4 bg-base-200/30 border-b border-base-300">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Distributor..." 
                           class="input input-sm input-bordered pl-10 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>

                <select wire:model.live="statusFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs font-medium focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>

                <select wire:model.live="regionFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs font-medium focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="areaFilter" @disabled(!$regionFilter) class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs font-medium focus:ring-2 focus:ring-primary/50 transition-all duration-300 disabled:opacity-50">
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tabel Data --}}
            <x-ui.table loading="{{ false }}" empty="Tidak ada data distributor ditemukan.">
                <x-slot:head>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Kode</th>
                        <th>Distributor</th>
                        <th>Cabang / Supervisor</th>
                        <th>Area / Region</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </x-slot:head>

                @foreach ($distributors as $index => $distributor)
                    <tr wire:key="distributor-{{ $distributor->distributor_code }}" class="group text-sm">
                        <td>
                            <span class="text-xs font-semibold text-base-content/40">{{ $distributors->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <span class="badge badge-sm badge-outline border-base-300 text-primary font-mono px-2 py-3 rounded-lg">{{ $distributor->distributor_code }}</span>
                        </td>
                        <td>
                            <div class="flex flex-col">
                                <span class="font-bold text-base-content/80 group-hover:text-primary transition-colors">{{ $distributor->distributor_name }}</span>
                                <span class="text-[10px] text-base-content/40 italic">{{ $distributor->created_at->format('d M Y') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-1.5 text-xs text-base-content/70">
                                    <x-heroicon-s-building-office-2 class="w-3.5 h-3.5 text-base-content/30" />
                                    <span>{{ $distributor->branch_name }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] text-base-content/50 font-medium">
                                    <x-heroicon-s-user-circle class="w-3.5 h-3.5 text-base-content/30" />
                                    <span>{{ $distributor->supervisor->description ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-col gap-0.5">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-s-map-pin class="w-3.5 h-3.5 text-base-content/30" />
                                    <span class="text-xs text-base-content/60 font-medium">{{ $distributor->area_name ?? 'N/A' }}</span>
                                </div>
                                <span class="text-[9px] uppercase tracking-wider text-base-content/30 ml-5 font-bold">{{ $distributor->region_name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>
                            @if ($distributor->is_active)
                                <span class="badge badge-sm border-none bg-success/20 text-success font-bold px-3 py-2 rounded-lg">Aktif</span>
                            @else
                                <span class="badge badge-sm border-none bg-error/20 text-error font-bold px-3 py-2 rounded-lg">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="showMap('{{ $distributor->distributor_code }}')" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-info hover:bg-info/10 transition-all duration-200" title="Peta">
                                    <x-heroicon-s-map class="w-4 h-4" />
                                </button>
                                @unless(auth()->user()->hasRole('guest'))
                                <button wire:click="openEditModal('{{ $distributor->distributor_code }}')" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-primary hover:bg-primary/10 transition-all duration-200" title="Edit">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete('{{ $distributor->distributor_code }}')" 
                                        class="btn btn-ghost btn-xs btn-square rounded-lg text-error hover:bg-error/10 transition-all duration-200" title="Hapus">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>

            @if($distributors->hasPages())
                <div class="mt-4 px-6">
                    {{ $distributors->links() }}
                </div>
            @endif
        </x-card>
    </div>

    {{-- Modal Form (Create/Edit) --}}
    <div x-data="{ open: @entangle('isFormModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-3xl overflow-hidden ring-1 ring-base-content/5 max-h-[90vh] flex flex-col text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $isEditing ? 'Edit Distributor' : 'Tambah Distributor Baru' }}</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">{{ $isEditing ? 'Perbarui data kemitraan distributor' : 'Daftarkan mitra distributor baru' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save" class="overflow-y-auto">
                <div class="p-6 space-y-8 bg-base-100">
                    {{-- Section: Profil Distributor --}}
                    <div>
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary/60 mb-5 px-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary/40"></span> Profil Distributor
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label for="distributor_code" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Distributor</label>
                                <div class="relative group">
                                    <input wire:model.blur="distributor_code" type="text" id="distributor_code" placeholder="Cth: DIST-001"
                                           class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('distributor_code') input-error @enderror"
                                           {{ $isEditing ? 'disabled' : '' }}>
                                    @if($isEditing)
                                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                            <x-heroicon-s-lock-closed class="w-4 h-4" />
                                        </div>
                                    @endif
                                </div>
                                @error('distributor_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="distributor_name" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Distributor</label>
                                <input wire:model.blur="distributor_name" type="text" id="distributor_name" placeholder="Cth: PT. Sukses Jaya"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('distributor_name') input-error @enderror">
                                @error('distributor_name') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="join_date" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal Bergabung</label>
                                <input wire:model.blur="join_date" type="date" id="join_date"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>

                            <div class="space-y-1.5">
                                <label for="resign_date" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Tanggal Berhenti</label>
                                <input wire:model.blur="resign_date" type="date" id="resign_date"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Penugasan Cabang --}}
                    <div class="border-t border-base-200 pt-6">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary/60 mb-5 px-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary/40"></span> Penugasan Cabang & Wilayah
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5 relative">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cari Cabang</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base-content/30">
                                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                                    </div>
                                    <input wire:model.live.debounce.300ms="branchSearch" type="text" placeholder="Ketik nama atau kode cabang..."
                                           class="input input-bordered w-full pl-11 bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                                    
                                    @if(count($this->branchesSearch) > 0)
                                        <div class="absolute z-50 w-full mt-2 bg-base-100 border border-base-300 rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                            @foreach($this->branchesSearch as $branch)
                                                <button type="button" wire:click="selectBranch('{{ $branch->branch_code }}', '{{ $branch->branch_name }}')"
                                                        class="w-full px-4 py-3 text-left hover:bg-base-200 flex items-center justify-between border-b border-base-200 last:border-0 transition-colors">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-bold text-base-content/80">{{ $branch->branch_name }}</span>
                                                        <span class="text-[10px] text-base-content/40 font-mono">{{ $branch->branch_code }}</span>
                                                    </div>
                                                    <x-heroicon-s-chevron-right class="w-4 h-4 text-base-content/20" />
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif(strlen($branchSearch) >= 2)
                                        <div class="absolute z-50 w-full mt-2 p-4 bg-base-100 border border-base-300 rounded-2xl shadow-xl text-center text-xs text-base-content/40 italic">
                                            Cabang tidak ditemukan
                                        </div>
                                    @endif
                                </div>
                                
                                @if($selectedBranchName)
                                    <div class="mt-3 p-4 rounded-2xl bg-primary/5 border border-primary/10 flex items-center justify-between group/sel animate-in slide-in-from-top-2 duration-300">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                                <x-heroicon-s-building-office-2 class="w-4 h-4" />
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-primary">{{ $selectedBranchName }}</span>
                                                <span class="text-[10px] text-base-content/40 font-mono tracking-tighter">{{ $branch_code }}</span>
                                            </div>
                                        </div>
                                        <button type="button" wire:click="$set('branch_code', '')" class="btn btn-ghost btn-xs btn-circle text-base-content/20 hover:text-error hover:bg-error/10">
                                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                @endif
                                @error('branch_code') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                            </div>

                            <div class="bg-base-200/50 rounded-2xl p-5 border border-base-300 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-base-content/30">Auto Info</span>
                                    <x-heroicon-s-information-circle class="w-4 h-4 text-base-content/20" />
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-base-content/40">Region</span>
                                        <span class="font-bold text-base-content/70">{{ $region_name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-base-content/40">Area</span>
                                        <span class="font-bold text-base-content/70">{{ $area_name }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-base-content/40">Supervisor</span>
                                        <span class="font-bold text-base-content/70 italic">{{ $supervisor_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Geolocation --}}
                    <div class="border-t border-base-200 pt-6">
                        <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-primary/60 mb-5 px-1 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary/40"></span> Titik Lokasi (Geotagging)
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label for="latitude" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Latitude</label>
                                <input wire:model.blur="latitude" type="text" id="latitude" placeholder="Cth: -6.123456"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>

                            <div class="space-y-1.5">
                                <label for="longitude" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Longitude</label>
                                <input wire:model.blur="longitude" type="text" id="longitude" placeholder="Cth: 106.123456"
                                       class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Status --}}
                    <div class="border-t border-base-200 pt-6">
                        <div class="flex items-center justify-between bg-base-200/50 p-4 rounded-2xl border border-base-300">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-base-content/70">Status Distributor</h4>
                                <p class="text-[10px] text-base-content/40">Kontrol aktifasi distributor dalam sistem</p>
                            </div>
                            <input type="checkbox" wire:model="is_active" class="toggle toggle-primary toggle-lg shadow-sm" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 shrink-0">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Daftarkan Distributor' }}</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="save" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-data="{ open: @entangle('isDeleteModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5">
            
            <div class="p-8 text-center text-base-content">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold mb-2 leading-none text-base-content">Hapus Distributor?</h3>
                <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Seluruh data riwayat yang terkait dengan distributor ini akan terdampak. Tindakan ini <span class="text-error font-bold italic">permanen</span>.</p>
            </div>

            <div class="flex items-center justify-center gap-3 px-6 pb-8">
                <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case transition-all duration-200">Batal</button>
                <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 transition-all duration-200 text-white">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Map --}}
    <div x-data="{ 
        open: @entangle('isMapModalOpen'),
        latitude: @entangle('mapLatitude'),
        longitude: @entangle('mapLongitude'),
        distributorName: @entangle('mapDistributorName'),
        initMap() {
            if (!this.open) return;
            const waitForLeaflet = setInterval(() => {
                if (typeof L !== 'undefined') {
                    clearInterval(waitForLeaflet);
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const mapElement = document.getElementById('distributorMap');
                            if (!mapElement) return;
                            
                            const lat = parseFloat(this.latitude);
                            const lng = parseFloat(this.longitude);
                            
                            if (isNaN(lat) || isNaN(lng)) return;
                            
                            if (window.distributorMapInstance) {
                                window.distributorMapInstance.remove();
                                window.distributorMapInstance = null;
                            }
                            
                            try {
                                const map = L.map('distributorMap').setView([lat, lng], 16);
                                window.distributorMapInstance = map;
                                
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors &copy; <a href=\'https://carto.com/attributions\'>CARTO</a>'
                                }).addTo(map);
                                
                                const customIcon = L.divIcon({
                                    className: 'custom-pin',
                                    html: `<svg class='w-8 h-8 text-primary drop-shadow-lg' fill='currentColor' viewBox='0 0 24 24'><path d='M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z'/></svg>`,
                                    iconSize: [32, 32],
                                    iconAnchor: [16, 32],
                                    popupAnchor: [0, -32]
                                });

                                L.marker([lat, lng], {icon: customIcon}).addTo(map)
                                 .bindPopup(`<div class='font-sans px-1 py-0.5 text-base-content'><strong class='text-base-content'>${this.distributorName}</strong><br><span class='text-[10px] text-base-content/40 font-mono'>${lat}, ${lng}</span></div>`)
                                 .openPopup();
                                
                                setTimeout(() => { map.invalidateSize(); }, 200);
                            } catch (error) { console.error('Map error:', error); }
                        }, 300);
                    });
                }
            }, 100);
            setTimeout(() => { clearInterval(waitForLeaflet); }, 5000);
        }
    }" 
    x-show="open" 
    @open-map.window="initMap()"
    x-cloak 
    class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-2xl overflow-hidden ring-1 ring-base-content/5 flex flex-col text-base-content">
            
            <div class="px-6 py-5 border-b border-base-300 flex items-center justify-between bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                        <x-heroicon-s-map class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none" x-text="distributorName || 'Lokasi Distributor'"></h3>
                        <p class="text-[10px] text-base-content/50 mt-1 font-mono">LAT: <span x-text="latitude"></span>, LONG: <span x-text="longitude"></span></p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-4 bg-base-100">
                <div id="distributorMap" style="height: 400px; width: 100%;" class="rounded-2xl shadow-inner border border-base-300 bg-base-200 relative overflow-hidden z-0">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-base-content/20">
                        <span class="loading loading-spinner loading-md mb-2"></span>
                        <p class="text-[10px] font-bold uppercase tracking-widest">Memuat Peta...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        Livewire.on('map-opened', () => {
            setTimeout(() => { window.dispatchEvent(new CustomEvent('open-map')); }, 100);
        });
    </script>
    @endscript
</div>