<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
    <x-slot name="title">Data Master Supervisor</x-slot>

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

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Master Supervisor</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data supervisor</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari Supervisor..." />

                {{-- Filter Region --}}
                <select wire:model.live="regionFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    @canEdit('master-supervisors.index')
                    <x-ui.action-button type="import" wire:click="openImportModal" />
                    <x-ui.action-button type="add" wire:click="openCreateModal" />
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    @endcanEdit
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
                        <th>Region</th>
                        <th>Area</th>
                        <th>Supervisor</th>
                        <th class="hidden md:table-cell">Keterangan</th>
                        <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($supervisors as $index => $supervisor)
                        <tr wire:key="supervisor-{{ $supervisor->supervisor_code }}" class="hover:bg-base-200/50 transition-colors group">
                            <th>{{ $supervisors->firstItem() + $index }}</th>
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-base-content/90">{{ $supervisor->area->region->region_name ?? 'N/A' }}</span>
                                    <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $supervisor->area->region_code ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-base-content/90">{{ $supervisor->area->area_name ?? 'N/A' }}</span>
                                    <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $supervisor->area_code ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-base-content/90 group-hover:text-primary transition-colors">{{ $supervisor->supervisor_name }}</span>
                                    <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $supervisor->supervisor_code }}</span>
                                </div>
                            </td>
                            <td class="hidden md:table-cell">
                                <span class="text-base-content/50 italic line-clamp-1" title="{{ $supervisor->description }}">{{ $supervisor->description ?: '-' }}</span>
                            </td>
                            <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                @canEdit('master-supervisors.index')
                                <div class="flex items-center justify-center gap-1">
                                    <x-ui.action-button type="edit" class="btn-square" title="Edit" wire:click="openEditModal('{{ $supervisor->supervisor_code }}')" />
                                    <x-ui.action-button type="delete" class="btn-square" title="Hapus" wire:click="confirmDelete('{{ $supervisor->supervisor_code }}')" />
                                </div>
                                @else
                                <span class="text-xs text-base-content/50 italic">View Only</span>
                                @endcanEdit
                            </th>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-base-content/50">
                                <div class="flex flex-col items-center justify-center">
                                    <x-heroicon-o-users class="w-12 h-12 mb-3 opacity-20" />
                                    <p class="font-medium">Tidak ada data supervisor ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($supervisors->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $supervisors->links() }}
            </div>
        @endif
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-xl overflow-hidden ring-1 ring-base-content/5">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        @if($isEditing)
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        @else
                            <x-heroicon-s-plus-circle class="w-6 h-6" />
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Supervisor' : 'Tambah Supervisor Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ $isEditing ? 'Perbarui data supervisor area operasional' : 'Daftarkan supervisor baru ke dalam sistem' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5 bg-base-100">
                    {{-- Kode Supervisor --}}
                    <div class="space-y-1.5">
                        <label for="supervisor_code" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Supervisor</label>
                        <div class="relative group">
                            <input wire:model.blur="supervisor_code" type="text" id="supervisor_code" placeholder="Contoh: SPV-01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('supervisor_code') input-error @enderror"
                                   {{ $isEditing ? 'disabled' : '' }}>
                            @if($isEditing)
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                    <x-heroicon-s-lock-closed class="w-4 h-4" />
                                </div>
                            @endif
                        </div>
                        @error('supervisor_code') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    {{-- Nama Supervisor --}}
                    <div class="space-y-1.5">
                        <label for="supervisor_name" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Supervisor</label>
                        <input wire:model.blur="supervisor_name" type="text" id="supervisor_name" placeholder="Contoh: Budi Santoso"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('supervisor_name') input-error @enderror">
                        @error('supervisor_name') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    {{-- Area --}}
                    <div class="md:col-span-2 space-y-1.5">
                        <label for="area_code" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area Penugasan</label>
                        <select wire:model.blur="area_code" id="area_code" 
                                class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('area_code') select-error @enderror">
                            <option value="">-- Pilih Area --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->area_code }}">{{ $area->area_name }} ({{ $area->region->region_name ?? '-' }})</option>
                            @endforeach
                        </select>
                        @error('area_code') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    {{-- Keterangan --}}
                    <div class="md:col-span-2 space-y-1.5">
                        <label for="description" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Keterangan (Opsional)</label>
                        <textarea wire:model.blur="description" id="description" rows="2" placeholder="Informasi tambahan penugasan..."
                                  class="textarea textarea-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Tambahkan Supervisor' }}</span>
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
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm overflow-hidden ring-1 ring-base-content/5">
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-s-trash class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Supervisor?</h3>
                <p class="text-sm text-base-content/60 leading-relaxed px-4">Apakah Anda yakin ingin menghapus supervisor ini? Tindakan ini <span class="text-error font-bold italic">tidak dapat dibatalkan</span>.</p>
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

    {{-- Modal Import --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-success/10 text-success">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-base-content">Import Supervisor</h3>
                        <p class="text-xs text-base-content/50">Upload file Excel data master supervisor</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 bg-base-100">
                    <div class="alert bg-info/10 text-info border-none rounded-xl text-xs mb-4">
                        <x-heroicon-s-information-circle class="w-5 h-5" />
                        <div>Pastikan file menggunakan format template standar. Anda bisa mendownload template di bawah ini.</div>
                    </div>

                    <div class="form-control w-full space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">File Excel (.xlsx)</label>
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="file-input file-input-bordered file-input-primary w-full bg-base-200 rounded-2xl" required />
                        @error('importFile') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>
                    
                    <button type="button" wire:click="downloadTemplate" class="btn btn-sm btn-outline btn-info w-full mt-4 rounded-xl normal-case">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Download Template Import
                    </button>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="import">Proses Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
