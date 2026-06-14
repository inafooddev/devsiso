<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full" x-data="{ init() { $el.classList.remove('opacity-0', 'translate-y-4'); $el.classList.add('opacity-100', 'translate-y-0'); } }" class="opacity-0 translate-y-4 transition-all duration-700 ease-out">
    <x-slot name="title">Mapping Eskalink</x-slot>

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0 flex items-start">
            <x-heroicon-s-check-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                <div class="text-xs">{{ session('message') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-success/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0 flex items-start">
            <x-heroicon-s-x-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                <div class="text-xs">{{ session('error') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-error/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    {{-- 4 KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 shrink-0">
        <!-- KPI 1 -->
        <div class="bg-base-100 p-3 md:p-4 lg:p-5 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-info/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-users class="w-5 h-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate">Total Distributor</h3>
                    <div class="text-lg md:text-xl font-bold leading-none mt-1 truncate">{{ number_format($kpi['total_dist'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="bg-base-100 p-3 md:p-4 lg:p-5 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-badge class="w-5 h-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate">Total Dist Active</h3>
                    <div class="text-lg md:text-xl font-bold leading-none mt-1 truncate">{{ number_format($kpi['total_dist_active'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="bg-base-100 p-3 md:p-4 lg:p-5 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-secondary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                    <x-heroicon-s-rocket-launch class="w-5 h-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate">Sudah Implementasi</h3>
                    <div class="text-lg md:text-xl font-bold leading-none mt-1 truncate">{{ number_format($kpi['total_implementasi'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- KPI 4 -->
        <div class="bg-base-100 p-3 md:p-4 lg:p-5 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-star class="w-5 h-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate">Impl. Dist Aktif</h3>
                    <div class="text-lg md:text-xl font-bold leading-none mt-1 truncate">{{ number_format($kpi['total_implementasi_active'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full lg:w-auto">
                <h2 class="text-base md:text-lg font-bold">Mapping Eskalink</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola status pemetaan implementasi sistem</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2 md:gap-3 w-full lg:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari Kode/Nama..." />

                {{-- Filter Region --}}
                <select wire:model.live="filterRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>

                {{-- Filter Area --}}
                <select wire:model.live="filterArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                    @endforeach
                </select>

                {{-- Filter IsActive --}}
                <select wire:model.live="filterIsActive" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Aktif: Semua</option>
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>

                {{-- Filter IsImplementasi --}}
                <select wire:model.live="filterIsImplementasi" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Impl: Semua</option>
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <button wire:click="resetFilters" class="btn btn-sm btn-ghost rounded-xl text-base-content/70 hover:bg-base-200">Reset</button>
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    <x-ui.action-button type="add" wire:click="create" />
                    <x-ui.action-button type="export" wire:click="export" />
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th class="w-16">No</th>
                        <th>Wilayah</th>
                        <th>Distributor</th>
                        <th>Cabang</th>
                        <th>Mapping Eskalink</th>
                        <th>Status Impl</th>
                        <th>Status Aktif</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($data as $index => $row)
                        <tr wire:key="row-{{ $row->id }}" class="hover:bg-base-200/50 transition-colors group">
                            <th>{{ $data->firstItem() + $index }}</th>
                            
                            {{-- Wilayah --}}
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-[11px] text-base-content/90">{{ $row->region_name }}</span>
                                    <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $row->area_name }}</span>
                                </div>
                            </td>
                            
                            {{-- Distributor --}}
                            <td>
                                <div class="flex flex-col gap-0.5 w-full">
                                    <span class="font-bold text-[11px] text-base-content/80 group-hover:text-primary transition-colors truncate w-full">{{ $row->distributor_name }}</span>
                                    <span class="font-mono text-[10px] font-semibold text-primary truncate">{{ $row->distributor_code }}</span>
                                </div>
                            </td>

                            {{-- Cabang --}}
                            <td>
                                <span class="font-bold text-[11px] text-base-content/80">{{ $row->branch_name }}</span>
                            </td>

                            {{-- Mapping Eskalink --}}
                            <td>
                                <div class="flex flex-col gap-0.5 w-full">
                                    <span class="font-bold text-[11px] truncate w-full
                                        @if($row->eskalink_code && $row->eskalink_code !== $row->distributor_code) 
                                            text-error
                                        @else 
                                            text-base-content/80
                                        @endif">
                                        {{ $row->eskalink_code ?: 'Belum diisi' }}
                                    </span>
                                    <span class="font-mono text-[10px] font-semibold text-primary truncate">
                                        {{ $row->eskalink_code_dist ?: '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Status Impl --}}
                            <td>
                                @if($row->implementasi === 'Y')
                                    <span class="badge badge-sm badge-success badge-outline bg-success/10 font-bold border-success/30">Yes</span>
                                @else
                                    <span class="badge badge-sm badge-error badge-outline opacity-70">No</span>
                                @endif
                            </td>

                            {{-- Status Aktif --}}
                            <td>
                                @if($row->is_active)
                                    <span class="badge badge-sm badge-success badge-outline bg-success/10 font-bold border-success/30">Yes</span>
                                @else
                                    <span class="badge badge-sm badge-error badge-outline opacity-70">No</span>
                                @endif
                            </td>

                            <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                <div class="flex items-center justify-center gap-1">
                                    <x-ui.action-button type="edit" wire:click="edit({{ $row->id }})" class="btn-square" title="Edit" />
                                    <x-ui.action-button type="delete" wire:click="delete({{ $row->id }})" onclick="confirm('Yakin ingin menghapus mapping distributor ini secara permanen?') || event.stopImmediatePropagation()" class="btn-square" title="Hapus" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="flex flex-col items-center justify-center py-12 text-base-content/40">
                                    <x-heroicon-o-inbox class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="text-sm font-medium">Tidak ada data ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form (Create/Edit) --}}
    <div x-data="{ open: @entangle('isModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden ring-1 ring-base-content/5">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEdit)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEdit ? 'Edit Mapping Distributor' : 'Tambah Mapping Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ $isEdit ? 'Perbarui data pemetaan' : 'Masukkan pemetaan distributor ke sistem' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="store">
                <div class="p-6 space-y-5 bg-base-100">
                    
                    {{-- Distributor --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor <span class="text-error">*</span></label>
                        @if($isEdit)
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30">
                                    <x-heroicon-s-lock-closed class="h-4 w-4" />
                                </div>
                                <input type="text" value="{{ $distributor_code }} - {{ $distributor_name }}" disabled class="input input-bordered w-full pl-9 bg-base-200/60 text-base-content/60 font-medium cursor-not-allowed border-dashed rounded-2xl">
                            </div>
                            <p class="text-[10px] text-base-content/50 mt-1 ml-1">Distributor tidak dapat diubah pada mode Edit. Silakan hapus dan buat ulang jika salah.</p>
                        @else
                            <div class="relative group">
                                <select wire:model="distributor_code" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('distributor_code') select-error @enderror">
                                    <option value="">-- Pilih Distributor --</option>
                                    @foreach($unmappedDistributors as $dist)
                                        <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_code }} - {{ $dist->distributor_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('distributor_code') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 rounded-xl bg-base-200/30 border border-base-200">
                        {{-- Kode Eskalink --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Eskalink</label>
                            <input type="text" wire:model="eskalink_code" placeholder="Misal: DIST001" class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('eskalink_code') input-error @enderror">
                            @error('eskalink_code') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>
                        
                        {{-- Kode Eskalink Dist --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Eskalink Dist</label>
                            <input type="text" wire:model="eskalink_code_dist" placeholder="Misal: DC-001" class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('eskalink_code_dist') input-error @enderror">
                            @error('eskalink_code_dist') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Status Implementasi --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Status Implementasi</label>
                        <select wire:model="implementasi" class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('implementasi') select-error @enderror">
                            <option value="Y">Yes (Sudah Implementasi)</option>
                            <option value="N">No (Belum)</option>
                        </select>
                        @error('implementasi') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="store">{{ $isEdit ? 'Simpan Perubahan' : 'Tambahkan Mapping' }}</span>
                        <span wire:loading wire:target="store" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-paper-airplane wire:loading.remove wire:target="store" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
