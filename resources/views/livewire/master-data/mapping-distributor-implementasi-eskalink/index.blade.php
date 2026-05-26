<div class="space-y-6 animate-fade-in-up" x-data="{ init() { $el.classList.remove('opacity-0', 'translate-y-4'); $el.classList.add('opacity-100', 'translate-y-0'); } }" class="opacity-0 translate-y-4 transition-all duration-700 ease-out">
    
    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="animate-bounce-in">
            <x-ui.notif type="success" :dismissible="true" class="shadow-md shadow-success/10">{{ session('message') }}</x-ui.notif>
        </div>
    @endif
    
    <!-- Header / Actions / Filters Toolbar -->
    <div class="card bg-base-100 shadow-sm border border-base-200/60 backdrop-blur-xl bg-base-100/90 sticky top-0 z-10">
        <div class="card-body p-4 sm:p-5">
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                <!-- Title Area -->
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-base-content flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-primary/10 text-primary"><x-heroicon-s-rectangle-group class="w-5 h-5"/></div>
                        Mapping Eskalink
                    </h2>
                    <p class="text-sm text-base-content/60 mt-1.5 max-w-2xl">Pantau dan kelola status pemetaan implementasi sistem Eskalink pada seluruh distributor aktif.</p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2 shrink-0">
                    <x-ui.button wire:click="resetFilters" icon="arrow-path" variant="ghost" class="text-base-content/70 hover:text-base-content hover:bg-base-200">Reset</x-ui.button>
                    <x-ui.button wire:click="export" icon="arrow-down-tray" variant="outline" class="border-success/30 text-success hover:bg-success hover:border-success hover:text-white">Export</x-ui.button>
                    <x-ui.button wire:click="create" icon="plus" variant="primary" class="shadow-sm shadow-primary/20 hover:shadow-md hover:shadow-primary/40 hover:-translate-y-0.5 transition-all">Tambah Data</x-ui.button>
                </div>
            </div>

            <div class="divider my-3 opacity-50"></div>

            <!-- Filter Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-primary">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4 text-base-content/40 group-focus-within:text-primary" />
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Kode / Nama..." class="input input-bordered w-full pl-9 bg-base-200/40 focus:bg-base-100 transition-colors shadow-sm focus:shadow-primary/10">
                </div>
                <div>
                    <select wire:model.live="filterRegion" class="select select-bordered w-full bg-base-200/40 focus:bg-base-100 transition-colors shadow-sm focus:shadow-primary/10">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterArea" class="select select-bordered w-full bg-base-200/40 focus:bg-base-100 transition-colors shadow-sm focus:shadow-primary/10">
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterIsActive" class="select select-bordered w-full bg-base-200/40 focus:bg-base-100 transition-colors shadow-sm focus:shadow-primary/10">
                        <option value="">Status Active: Semua</option>
                        <option value="1">Active: Yes</option>
                        <option value="0">Active: No</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterIsImplementasi" class="select select-bordered w-full bg-base-200/40 focus:bg-base-100 transition-colors shadow-sm focus:shadow-primary/10">
                        <option value="">Status Impl: Semua</option>
                        <option value="1">Impl: Yes</option>
                        <option value="0">Impl: No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- KPI 1 -->
        <div class="card bg-base-100 shadow-sm border border-base-200 hover:shadow-md hover:border-info/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-info/5 group-hover:bg-info/10 transition-colors duration-500 blur-2xl"></div>
            <div class="card-body p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-s-users class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Total Distributor</p>
                        <h3 class="text-2xl font-bold text-base-content mt-0.5">{{ number_format($kpi['total_dist']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="card bg-base-100 shadow-sm border border-base-200 hover:shadow-md hover:border-success/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-success/5 group-hover:bg-success/10 transition-colors duration-500 blur-2xl"></div>
            <div class="card-body p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-s-check-badge class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Total Dist Active</p>
                        <h3 class="text-2xl font-bold text-base-content mt-0.5">{{ number_format($kpi['total_dist_active']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="card bg-base-100 shadow-sm border border-base-200 hover:shadow-md hover:border-secondary/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-secondary/5 group-hover:bg-secondary/10 transition-colors duration-500 blur-2xl"></div>
            <div class="card-body p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-s-rocket-launch class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Sudah Implementasi</p>
                        <h3 class="text-2xl font-bold text-base-content mt-0.5">{{ number_format($kpi['total_implementasi']) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI 4 -->
        <div class="card bg-base-100 shadow-sm border border-base-200 hover:shadow-md hover:border-primary/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden relative group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-primary/5 group-hover:bg-primary/10 transition-colors duration-500 blur-2xl"></div>
            <div class="card-body p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-s-star class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-base-content/50 uppercase tracking-wider">Impl. Dist Aktif</p>
                        <h3 class="text-2xl font-bold text-base-content mt-0.5">{{ number_format($kpi['total_implementasi_active']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden relative">
        <div wire:loading.delay.shorter class="absolute inset-0 bg-base-100/50 backdrop-blur-[2px] z-20 flex items-center justify-center">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>
        
        <div class="overflow-x-auto" style="max-height: calc(100vh - 280px);">
            <x-ui.table sticky hover class="whitespace-nowrap w-full">
                <x-slot:head>
                    <tr>
                        <th class="bg-base-200/80">Wilayah</th>
                        <th class="bg-base-200/80">Distributor</th>
                        <th class="bg-base-200/80">Cabang</th>
                        <th class="bg-base-200/80">Mapping Eskalink</th>
                        <th class="bg-base-200/80">Status Impl</th>
                        <th class="bg-base-200/80">Status Aktif</th>
                        <th class="text-center bg-base-200/80 w-24">Aksi</th>
                    </tr>
                </x-slot:head>
                
                @forelse($data as $row)
                    <tr wire:key="row-{{ $row->id }}" class="group/row transition-colors">
                        <td>
                            <div class="font-medium text-base-content">{{ $row->region_name }}</div>
                            <div class="text-xs text-base-content/50 mt-0.5">{{ $row->area_name }}</div>
                        </td>
                        <td>
                            <div class="font-semibold text-base-content group-hover/row:text-primary transition-colors">{{ $row->distributor_name }}</div>
                            <div class="text-xs font-mono text-base-content/50 mt-0.5 bg-base-200 inline-block px-1.5 py-0.5 rounded">{{ $row->distributor_code }}</div>
                        </td>
                        <td>
                            <div class="text-sm text-base-content/80">{{ $row->branch_name }}</div>
                        </td>
                        <td>
                            <div class="font-medium text-base-content">{{ $row->eskalink_code_dist ?: '-' }}</div>
                            <div class="text-[11px] font-mono mt-1 
                                @if($row->eskalink_code && $row->eskalink_code !== $row->distributor_code) 
                                    text-error font-bold bg-error/10 inline-block px-1.5 py-0.5 rounded border border-error/20
                                @else 
                                    text-base-content/50 
                                @endif">
                                {{ $row->eskalink_code ?: 'Belum diisi' }}
                            </div>
                        </td>
                        <td>
                            @if($row->implementasi === 'Y')
                                <x-ui.badge variant="success" class="shadow-sm shadow-success/10"><x-heroicon-s-check class="w-3 h-3 mr-0.5"/>Yes</x-ui.badge>
                            @else
                                <x-ui.badge variant="error" outline class="bg-base-100 opacity-80">No</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            @if($row->is_active)
                                <x-ui.badge variant="success" class="shadow-sm shadow-success/10"><x-heroicon-s-check class="w-3 h-3 mr-0.5"/>Yes</x-ui.badge>
                            @else
                                <x-ui.badge variant="error" outline class="bg-base-100 opacity-80">No</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1 opacity-60 group-hover/row:opacity-100 transition-opacity">
                                <button wire:click="edit({{ $row->id }})" class="btn btn-xs btn-ghost btn-circle text-primary hover:bg-primary/10" title="Edit Data">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                <button wire:click="delete({{ $row->id }})" class="btn btn-xs btn-ghost btn-circle text-error hover:bg-error/10" title="Hapus Data" onclick="confirm('Yakin ingin menghapus mapping distributor ini secara permanen?') || event.stopImmediatePropagation()">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="flex flex-col items-center justify-center py-16 gap-3 text-base-content/40">
                                <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-2">
                                    <x-heroicon-o-magnifying-glass class="w-8 h-8 text-base-content/30" />
                                </div>
                                <p class="text-base font-medium text-base-content/60">Tidak ada data ditemukan</p>
                                <p class="text-sm">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
        
        <div class="p-4 border-t border-base-200 bg-base-100/50">
            {{ $data->links() }}
        </div>
    </div>

    <!-- Interactive Form Modal -->
    <x-ui.modal id="modal-form" :open="$isModalOpen" wire:close="closeModal" :title="$isEdit ? 'Edit Mapping Distributor' : 'Tambah Mapping Baru'" boxClass="overflow-visible shadow-2xl">
        <div class="space-y-5 py-2">
            
            <div class="form-control w-full">
                <label class="label pb-1"><span class="label-text font-semibold text-base-content/80">Distributor <span class="text-error">*</span></span></label>
                @if($isEdit)
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-heroicon-s-lock-closed class="h-4 w-4 text-base-content/30" />
                        </div>
                        <input type="text" value="{{ $distributor_code }} - {{ $distributor_name }}" disabled class="input input-bordered w-full pl-9 bg-base-200/60 text-base-content/60 font-medium cursor-not-allowed border-dashed">
                    </div>
                    <p class="text-[11px] text-base-content/50 mt-1.5 ml-1">Distributor tidak dapat diubah pada mode Edit. Silakan hapus dan buat ulang jika salah.</p>
                @else
                    <select wire:model="distributor_code" class="select select-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary/30 transition-shadow bg-base-100">
                        <option value="">-- Pilih Distributor --</option>
                        @foreach($unmappedDistributors as $dist)
                            <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_code }} - {{ $dist->distributor_name }}</option>
                        @endforeach
                    </select>
                    @error('distributor_code') <span class="text-error text-xs mt-1.5 flex items-center gap-1"><x-heroicon-m-exclamation-circle class="w-3.5 h-3.5"/>{{ $message }}</span> @enderror
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 rounded-xl bg-base-200/30 border border-base-200">
                <div class="form-control w-full">
                    <label class="label pb-1"><span class="label-text font-semibold text-base-content/80">Kode Eskalink</span></label>
                    <input type="text" wire:model="eskalink_code" placeholder="Misal: DIST001" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary/30 transition-shadow">
                    @error('eskalink_code') <span class="text-error text-xs mt-1.5 flex items-center gap-1"><x-heroicon-m-exclamation-circle class="w-3.5 h-3.5"/>{{ $message }}</span> @enderror
                </div>
                <div class="form-control w-full">
                    <label class="label pb-1"><span class="label-text font-semibold text-base-content/80">Kode Eskalink Dist</span></label>
                    <input type="text" wire:model="eskalink_code_dist" placeholder="Misal: DC-001" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary/30 transition-shadow">
                    @error('eskalink_code_dist') <span class="text-error text-xs mt-1.5 flex items-center gap-1"><x-heroicon-m-exclamation-circle class="w-3.5 h-3.5"/>{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-control w-full">
                <label class="label pb-1"><span class="label-text font-semibold text-base-content/80">Status Implementasi</span></label>
                <select wire:model="implementasi" class="select select-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary/30 transition-shadow">
                    <option value="Y">Yes (Sudah Implementasi)</option>
                    <option value="N">No (Belum)</option>
                </select>
                @error('implementasi') <span class="text-error text-xs mt-1.5 flex items-center gap-1"><x-heroicon-m-exclamation-circle class="w-3.5 h-3.5"/>{{ $message }}</span> @enderror
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" wire:click="closeModal" class="hover:bg-base-200 transition-colors">Batal</x-ui.button>
            <x-ui.button variant="primary" wire:click="store" icon="check" class="shadow-sm shadow-primary/20 hover:shadow-md hover:shadow-primary/40 hover:-translate-y-0.5 transition-all">Simpan Perubahan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
    
    <style>
        /* Small custom animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.95); }
            50% { opacity: 1; transform: scale(1.02); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-bounce-in { animation: bounceIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    </style>
</div>
