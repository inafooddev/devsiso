<div class="flex-1 flex flex-col w-full h-full min-h-0">
    <x-slot name="title">Data Master Area</x-slot>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
        {{-- Notifikasi --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0">
                <x-heroicon-s-check-circle class="w-5 h-5" />
                <div>
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                    <div class="text-xs">{{ session('message') }}</div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0">
                <x-heroicon-s-x-circle class="w-5 h-5" />
                <div>
                    <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                    <div class="text-xs">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full sm:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Master Area</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data area cakupan distribusi</p>
                </div>
                
                <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                    <!-- Search Component -->
                    <x-ui.search-input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari Area atau Region..." 
                    />

                    <!-- Filter Region Component -->
                    <select wire:model.live="regionFilter" class="select select-sm select-bordered w-36 sm:w-40 rounded-xl bg-base-200 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <option value="">Semua Region</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>

                    @canEdit('master-areas.index')
                    <x-ui.action-button type="import" wire:click="$set('isImportModalOpen', true)" />
                    <x-ui.action-button type="export" wire:click="export" />
                    <div class="hidden md:block w-px h-6 bg-base-300 mx-1"></div>
                    <x-ui.action-button
                        type="add"
                        wire:click="openCreateModal"
                    />
                    @endcanEdit
                </div>
            </div>

            {{-- Body Card (Tabel Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative">
                @if($areas->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full text-base-content/50 p-6">
                        <x-heroicon-o-inbox class="w-12 h-12 mb-2 opacity-50" />
                        <p>Tidak ada data area ditemukan.</p>
                    </div>
                @else
                    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                        <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                            <tr>
                                <th class="w-16">No</th>
                                <th>Kode Region</th>
                                <th>Nama Region</th>
                                <th>Kode Area</th>
                                <th>Nama Area</th>
                                <th class="hidden md:table-cell">Dibuat Pada</th>
                                <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach ($areas as $index => $area)
                                <tr wire:key="area-{{ $area->area_code }}" class="hover:bg-base-200/50 transition-colors">
                                    <th>{{ $areas->firstItem() + $index }}</th>
                                    <td>
                                        <span class="badge badge-sm badge-outline border-base-300 text-base-content/70 font-bold px-2 py-3 rounded-lg">{{ $area->region->region_code ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-s-map class="w-3.5 h-3.5 text-base-content/30" />
                                            <span class="text-sm text-base-content/80">{{ $area->region->region_name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm badge-outline border-base-300 text-primary font-bold px-2 py-3 rounded-lg">{{ $area->area_code }}</span>
                                    </td>
                                    <td class="font-bold text-base-content/90">{{ $area->area_name }}</td>
                                    <td class="hidden md:table-cell text-base-content/60">
                                        {{ $area->created_at->translatedFormat('d M Y') }}
                                        <span class="text-xs opacity-70 ml-1">{{ $area->created_at->format('H:i') }}</span>
                                    </td>
                                    <th class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                                        @canEdit('master-areas.index')
                                        <div class="flex items-center justify-center gap-1">
                                            <x-ui.action-button 
                                                type="edit" 
                                                class="btn-square" 
                                                title="Edit" 
                                                wire:click="openEditModal('{{ $area->area_code }}')" 
                                            />
                                            <x-ui.action-button 
                                                type="delete" 
                                                class="btn-square" 
                                                title="Hapus" 
                                                wire:click="confirmDelete('{{ $area->area_code }}')" 
                                            />
                                        </div>
                                        @else
                                        <span class="text-xs text-base-content/50 italic font-normal">View Only</span>
                                        @endcanEdit
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if($areas->hasPages())
                <div class="border-t border-base-300 p-3 md:p-4 bg-base-200/30 shrink-0">
                    {{ $areas->links() }}
                </div>
            @endif
        </div>
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
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden ring-1 ring-base-content/5">
            
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
                        <h3 class="font-bold text-lg text-base-content">{{ $isEditing ? 'Edit Area' : 'Tambah Area Baru' }}</h3>
                        <p class="text-xs text-base-content/50">{{ $isEditing ? 'Perbarui data area cakupan operasional' : 'Daftarkan area baru ke dalam sistem' }}</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-5 bg-base-100">
                    {{-- Region --}}
                    <div class="space-y-1.5">
                        <label for="region_code" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                        <div class="relative group">
                            <select wire:model.blur="region_code" id="region_code" 
                                    class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('region_code') select-error @enderror">
                                <option value="">-- Pilih Region --</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('region_code') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    {{-- Kode Area --}}
                    <div class="space-y-1.5">
                        <label for="area_code" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Kode Area</label>
                        <div class="relative group">
                            <input wire:model.blur="area_code" type="text" id="area_code" placeholder="Contoh: INA01"
                                   class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('area_code') input-error @enderror"
                                   {{ $isEditing ? 'disabled' : '' }}>
                            @if($isEditing)
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-base-content/30">
                                    <x-heroicon-s-lock-closed class="w-4 h-4" />
                                </div>
                            @endif
                        </div>
                        @error('area_code') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>

                    {{-- Nama Area --}}
                    <div class="space-y-1.5">
                        <label for="area_name" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Nama Area</label>
                        <input wire:model.blur="area_name" type="text" id="area_name" placeholder="Contoh: INA JABODETABEK"
                               class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('area_name') input-error @enderror">
                        @error('area_name') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Simpan Perubahan' : 'Tambahkan Area' }}</span>
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
                <h3 class="text-xl font-bold text-base-content mb-2">Hapus Area?</h3>
                <p class="text-sm text-base-content/60 leading-relaxed px-4">Apakah Anda yakin ingin menghapus area ini? Tindakan ini <span class="text-error font-bold italic">tidak dapat dibatalkan</span>.</p>
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
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
                        <h3 class="font-bold text-lg text-base-content">Import Area</h3>
                        <p class="text-xs text-base-content/50">Unggah file Excel berisi data area</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 space-y-5 bg-base-100">
                    <div class="alert alert-info shadow-sm rounded-xl bg-info/10 text-info border-none p-4">
                        <x-heroicon-s-information-circle class="w-5 h-5 shrink-0" />
                        <div class="text-xs">
                            Pastikan format file sesuai dengan template. 
                            <button type="button" wire:click="downloadTemplate" class="link font-bold text-info hover:text-info/80 ml-1">
                                Unduh Template
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="importFile" class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">File Excel</label>
                        <input wire:model="importFile" type="file" id="importFile" accept=".xlsx,.xls,.csv"
                               class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('importFile') file-input-error @enderror">
                        @error('importFile') <span class="text-error text-xs font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" /> {{ $message }}</span> @enderror
                        
                        <div wire:loading wire:target="importFile" class="text-xs text-info mt-2">
                            Sedang mengunggah file...
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/50">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Batal</button>
                    <button type="submit" class="btn btn-success rounded-xl px-8 normal-case shadow-sm shadow-success/20 text-white gap-2" wire:loading.attr="disabled" wire:target="import, importFile">
                        <span wire:loading.remove wire:target="import">Proses Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-arrow-up-tray wire:loading.remove wire:target="import" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
