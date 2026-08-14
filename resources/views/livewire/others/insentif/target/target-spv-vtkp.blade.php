<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full relative" x-data="{ isEditOpen: @entangle('isEditModalOpen'), isImportOpen: @entangle('isImportModalOpen') }">

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success shrink-0 mb-4">
            <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                <div class="text-sm">{{ session('message') }}</div>
            </div>
        </div>
    @endif
    @if (session()->has('warning'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="alert alert-warning shadow-lg rounded-2xl border-none bg-warning/20 text-warning shrink-0 mb-4">
            <x-heroicon-s-exclamation-triangle class="w-6 h-6 shrink-0" />
            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider">Perhatian</h3>
                <div class="text-sm">{{ session('warning') }}</div>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error shrink-0 mb-4">
            <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider">Gagal</h3>
                <div class="text-sm">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="p-4 md:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-100 rounded-t-xl">
        <div class="flex flex-wrap items-center justify-start sm:justify-start gap-2 md:gap-3 w-full sm:w-auto">
            {{-- Search --}}
            <div class="relative group grow sm:grow-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                </div>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Cari cabang..."
                       class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
            </div>

            {{-- Year & Quarter Filter --}}
            <div class="relative group grow sm:grow-0 flex items-center gap-2">
                <select wire:model.live="yearFilter" class="select select-sm select-bordered w-full sm:w-32 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>

                <select wire:model.live="quarterFilter" class="select select-sm select-bordered w-full sm:w-24 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="Q1">Q1</option>
                    <option value="Q2">Q2</option>
                    <option value="Q3">Q3</option>
                    <option value="Q4">Q4</option>
                </select>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="openImportModal" class="btn btn-sm btn-info text-white rounded-xl normal-case gap-2 shadow-sm shadow-info/20">
                <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                <span class="hidden sm:inline">Import</span>
            </button>
            
            <button wire:click="export" class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20">
                <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                <span class="hidden sm:inline">Export</span>
                <span wire:loading wire:target="export" class="loading loading-spinner loading-xs ml-1"></span>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="flex-1 overflow-auto bg-base-100 relative">
        <table class="table table-xs w-full border-collapse">
            <thead class="bg-base-200">
                <tr class="h-10">
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10 w-16">No</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Cabang</th>
                    
                    @foreach ($productGroups as $pg)
                        <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10 text-primary">{{ $pg }}</th>
                    @endforeach

                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10 w-24">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $item)
                    <tr wire:key="row-{{ $index }}" class="hover:bg-base-200 transition-colors">
                        <td class="border border-base-300 text-center">{{ $data->firstItem() + $index }}</td>
                        <td class="border border-base-300 font-mono text-center font-semibold">{{ $item->cabang }}</td>
                        
                        @foreach ($productGroups as $idx => $pg)
                            @php
                                $col = 'target_' . $idx;
                            @endphp
                            <td class="border border-base-300 text-right">
                                {{ number_format($item->$col, 0, ',', '.') }}
                            </td>
                        @endforeach

                        <td class="border border-base-300 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="openEditModal('{{ addslashes($item->cabang) }}')" 
                                        class="btn btn-xs btn-square btn-outline btn-primary" 
                                        title="Edit Manual">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                
                                <button wire:click="deleteData('{{ addslashes($item->cabang) }}')" 
                                        onclick="return confirm('Yakin ingin menghapus data cabang {{ addslashes($item->cabang) }} di kuartal ini?')"
                                        class="btn btn-xs btn-square btn-outline btn-error" 
                                        title="Hapus Data">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($productGroups) + 3 }}" class="text-center py-8 text-base-content/50">Tidak ada data ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-3 bg-base-100 border-t border-base-300 rounded-b-xl">
        {{ $data->links() }}
    </div>

    {{-- ========== MODAL IMPORT ========== --}}
    <div x-show="isImportOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="isImportOpen"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="isImportOpen = false"></div>

            <div x-show="isImportOpen"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative text-left bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg ring-1 ring-base-content/5 text-base-content my-8">

                <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                            <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-none">Import Target SPV VTKP</h3>
                            <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Upload Excel Format List</p>
                        </div>
                    </div>
                    <button wire:click="closeImportModal" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="processImport">
                    <div class="p-6 space-y-5">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between ml-1">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">File Excel <span class="text-error">*</span></label>
                                <button type="button" wire:click="downloadFormat" class="text-xs text-info hover:underline flex items-center gap-1 font-semibold">
                                    <x-heroicon-s-arrow-down-tray class="w-3 h-3"/> Download Format
                                </button>
                            </div>
                            <input wire:model="importExcel" type="file" accept=".xlsx, .xls, .csv" class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('importExcel') file-input-error @enderror" required>
                            <span class="text-[10px] text-base-content/40 ml-1">Format: xlsx, xls, csv (Max: 10MB)</span>
                            @error('importExcel') <span class="block text-error text-[10px] font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                        <button type="button" wire:click="closeImportModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-info text-white rounded-xl px-10 normal-case shadow-sm shadow-info/20 gap-2">
                            <span wire:loading.remove wire:target="processImport">Import Data</span>
                            <span wire:loading wire:target="processImport" class="loading loading-spinner loading-xs"></span>
                            <x-heroicon-s-arrow-up-tray wire:loading.remove wire:target="processImport" class="w-4 h-4" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========== MODAL EDIT ========== --}}
    <div x-show="isEditOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="isEditOpen"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="isEditOpen = false"></div>

            <div x-show="isEditOpen"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative text-left bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md ring-1 ring-base-content/5 text-base-content my-8">

                <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                            <x-heroicon-s-pencil-square class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-none">Edit Target SPV VTKP</h3>
                            <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Ubah Data Target {{ $quarterFilter }} {{ $yearFilter }}</p>
                        </div>
                    </div>
                    <button wire:click="closeEditModal" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="saveEdit">
                    <div class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang</label>
                            <input type="text" class="input input-sm input-bordered w-full bg-base-200/50 font-mono font-semibold" wire:model="editCabang" readonly>
                        </div>

                        <div class="divider text-xs uppercase tracking-widest text-base-content/30 font-bold my-2">TARGET QTY</div>

                        <div class="grid grid-cols-2 gap-4 max-h-[40vh] overflow-y-auto px-1 pb-1">
                            @foreach($productGroups as $idx => $pg)
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">{{ $pg }} <span class="text-error">*</span></label>
                                    <input wire:model="editTargets.{{ $idx }}" type="number" step="any" min="0" class="input input-sm input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50" required>
                                    @error("editTargets.{$idx}") <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                        
                        @if(empty($productGroups))
                            <div class="text-center py-4 text-xs text-base-content/50 italic">
                                Belum ada Produk Grup di Kuartal ini.
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                        <button type="button" wire:click="closeEditModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                        <button type="submit" class="btn btn-primary text-white rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2" {{ empty($productGroups) ? 'disabled' : '' }}>
                            Simpan Target
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('download-error-file', (event) => {
            const data = event[0];
            const link = document.createElement('a');
            link.href = data.content;
            link.download = data.name;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
    @endscript
</div>
