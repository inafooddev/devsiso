<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full relative">

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
                       placeholder="Cari cabang/region/area..."
                       class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
            </div>

            {{-- Year Filter --}}
            <select wire:model.live="yearFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 grow sm:grow-0">
                <option value="">Semua Tahun</option>
                @for ($y = date('Y') + 1; $y >= 2020; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->check() && auth()->user()->hasMenuAccess('others.insentif.target.index', 'can_import'))
            <button wire:click="openImportModal" class="btn btn-sm btn-info text-white rounded-xl normal-case gap-2 shadow-sm shadow-info/20">
                <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                <span class="hidden sm:inline">Import</span>
            </button>
            @endif
            
            @if(auth()->check() && auth()->user()->hasMenuAccess('others.insentif.target.index', 'can_export'))
            <button wire:click="export" class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20">
                <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                <span class="hidden sm:inline">Export</span>
                <span wire:loading wire:target="export" class="loading loading-spinner loading-xs ml-1"></span>
            </button>
            @endif
        </div>
    </div>

    {{-- Data Table --}}
    <div class="flex-1 overflow-auto bg-base-100 relative">
        <table class="table table-xs w-full border-collapse">
            <thead class="bg-base-200">
                <tr class="h-10">
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10 w-16">No</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Bulan</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Region</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Area</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Cabang</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Target Reg</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Target Fest</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10 bg-success/10 text-success">Total Target</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10 w-24">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $item)
                    <tr wire:key="row-{{ $item->bulan }}-{{ $item->cabang }}" class="hover:bg-base-200 transition-colors">
                        <td class="border border-base-300 text-center">{{ $data->firstItem() + $index }}</td>
                        <td class="border border-base-300 text-center font-semibold">{{ $item->bulan }}</td>
                        <td class="border border-base-300 text-center">{{ $item->region }}</td>
                        <td class="border border-base-300 text-center">{{ $item->area }}</td>
                        <td class="border border-base-300 font-mono text-center font-semibold text-primary">{{ $item->cabang }}</td>
                        <td class="border border-base-300 text-right">
                            {{ number_format($item->target_reg, 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 text-right">
                            {{ number_format($item->target_fest, 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 text-right text-success font-bold bg-success/5">
                            Rp {{ number_format($item->total_target, 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if(auth()->check() && auth()->user()->hasMenuAccess('others.insentif.target.index', 'can_edit'))
                                <button wire:click="openEditModal('{{ $item->bulan }}', '{{ $item->cabang }}')" 
                                        class="btn btn-xs btn-square btn-outline btn-primary" 
                                        title="Edit Manual">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                @endif
                                
                                @if(auth()->check() && auth()->user()->hasMenuAccess('others.insentif.target.index', 'can_delete'))
                                <button wire:click="deleteData('{{ $item->bulan }}', '{{ $item->cabang }}')" 
                                        onclick="return confirm('Yakin ingin menghapus data ini? (Akan menghapus target REG & FEST secara bersamaan)')"
                                        class="btn btn-xs btn-square btn-outline btn-error" 
                                        title="Hapus Data">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-base-content/50">Tidak ada data ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-3 bg-base-100 border-t border-base-300 rounded-b-xl">
        {{ $data->links() }}
    </div>

    {{-- ========== MODAL IMPORT ========== --}}
    @if($isImportModalOpen)
    <x-ui.modal id="modal-import-spv-value" wire:key="modal-import-spv-value" title="Import Target SPV Value" icon="arrow-up-tray" size="md" open="true" wire:close="closeImportModal">
        <form wire:submit.prevent="processImport" id="form-import-spv-value">
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between ml-1">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">File Excel <span class="text-error">*</span></label>
                        <button type="button" wire:click="downloadFormat" class="text-xs text-info hover:underline flex items-center gap-1 font-semibold">
                            <x-heroicon-s-arrow-down-tray class="w-3 h-3"/> Download Format
                        </button>
                    </div>
                    <input wire:model="importExcel" type="file" accept=".xlsx, .xls, .csv" class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('importExcel') file-input-error @enderror" required>
                    <div wire:loading wire:target="importExcel" class="text-xs text-info mt-1 font-medium flex items-center gap-1">
                        <span class="loading loading-spinner loading-xs"></span> Mengunggah file...
                    </div>
                    <span class="text-[10px] text-base-content/40 ml-1">Format: xlsx, xls, csv (Max: 10MB)</span>
                    @error('importExcel') <span class="block text-error text-[10px] font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" wire:click="closeImportModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
            <button form="form-import-spv-value" type="submit" class="btn btn-info text-white rounded-xl px-8 normal-case shadow-sm shadow-info/20 gap-2" wire:loading.attr="disabled" wire:target="processImport, importExcel">
                <span wire:loading.remove wire:target="processImport">Import Data</span>
                <span wire:loading wire:target="processImport" class="loading loading-spinner loading-xs"></span>
                <x-heroicon-s-arrow-up-tray wire:loading.remove wire:target="processImport" class="w-4 h-4" />
            </button>
        </x-slot:footer>
    </x-ui.modal>
    @endif

    {{-- ========== MODAL EDIT ========== --}}
    @if($isEditModalOpen)
    <x-ui.modal id="modal-edit-spv-value" wire:key="modal-edit-spv-value-{{ $editBulan }}-{{ $editCabang }}" title="Edit Target SPV Value" icon="pencil-square" size="md" open="true" wire:close="closeEditModal">
        <form wire:submit.prevent="saveEdit" id="form-edit-spv-value">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Bulan</label>
                        <input type="text" class="input input-sm input-bordered w-full bg-base-200/50" wire:model="editBulan" readonly>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang</label>
                        <input type="text" class="input input-sm input-bordered w-full bg-base-200/50 font-mono font-semibold" wire:model="editCabang" readonly>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                        <input type="text" class="input input-sm input-bordered w-full bg-base-200/50" wire:model="editRegion" readonly>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                        <input type="text" class="input input-sm input-bordered w-full bg-base-200/50" wire:model="editArea" readonly>
                    </div>
                </div>

                <div class="space-y-1.5 pt-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Target REG <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-base-content/50">Rp</span>
                        <input wire:model="editTargetReg" type="number" step="any" min="0" class="input input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 pl-10" required>
                    </div>
                    @error('editTargetReg') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5 pt-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Target FEST <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-base-content/50">Rp</span>
                        <input wire:model="editTargetFest" type="number" step="any" min="0" class="input input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 pl-10" required>
                    </div>
                    @error('editTargetFest') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" wire:click="closeEditModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
            <button form="form-edit-spv-value" type="submit" class="btn btn-primary text-white rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2" wire:loading.attr="disabled" wire:target="saveEdit">
                <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                <span wire:loading wire:target="saveEdit" class="loading loading-spinner loading-xs"></span>
            </button>
        </x-slot:footer>
    </x-ui.modal>
    @endif

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
