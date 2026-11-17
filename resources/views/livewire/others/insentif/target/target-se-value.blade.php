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
                       placeholder="Cari kode dist/sls..."
                       class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
            </div>

            {{-- Month Filter --}}
            <input wire:model.live="monthFilter" type="month" class="input input-sm input-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 grow sm:grow-0">
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
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Distributor Code</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Salesman Code</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10">Target (Value)</th>
                    <th class="border border-base-300 bg-base-200 text-center align-middle sticky top-0 z-10 w-24">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $index => $item)
                    <tr wire:key="row-{{ $item->id }}" class="hover:bg-base-200 transition-colors">
                        <td class="border border-base-300 text-center">{{ $data->firstItem() + $index }}</td>
                        <td class="border border-base-300 text-center font-semibold">{{ $item->bulan }}</td>
                        <td class="border border-base-300 font-mono text-center">{{ $item->distributor_code }}</td>
                        <td class="border border-base-300 font-mono text-center">{{ $item->salesman_code }}</td>
                        <td class="border border-base-300 text-right text-success font-bold">
                            Rp {{ number_format($item->target, 0, ',', '.') }}
                        </td>
                        <td class="border border-base-300 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if(auth()->check() && auth()->user()->hasMenuAccess('others.insentif.target.index', 'can_edit'))
                                <button wire:click="openSwapModal({{ $item->id }})" 
                                        class="btn btn-xs btn-square btn-outline btn-warning" 
                                        title="Tukar Target (Swap)">
                                    <x-heroicon-s-arrows-right-left class="w-4 h-4" />
                                </button>
                                
                                <button wire:click="openEditModal({{ $item->id }})" 
                                        class="btn btn-xs btn-square btn-outline btn-primary" 
                                        title="Edit Manual">
                                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                                </button>
                                @endif
                                
                                @if(auth()->check() && auth()->user()->hasMenuAccess('others.insentif.target.index', 'can_delete'))
                                <button wire:click="deleteData({{ $item->id }})" 
                                        onclick="return confirm('Yakin ingin menghapus data ini?')"
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
                        <td colspan="6" class="text-center py-8 text-base-content/50">Tidak ada data ditemukan.</td>
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
    <x-ui.modal id="modal-import-se-value" wire:key="modal-import-se-value" title="Import Target SE Value" icon="arrow-up-tray" size="md" open="true" wire:close="closeImportModal">
        <form wire:submit.prevent="processImport" id="form-import-se-value">
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
            <button form="form-import-se-value" type="submit" class="btn btn-info text-white rounded-xl px-8 normal-case shadow-sm shadow-info/20 gap-2" wire:loading.attr="disabled" wire:target="processImport, importExcel">
                <span wire:loading.remove wire:target="processImport">Import Data</span>
                <span wire:loading wire:target="processImport" class="loading loading-spinner loading-xs"></span>
                <x-heroicon-s-arrow-up-tray wire:loading.remove wire:target="processImport" class="w-4 h-4" />
            </button>
        </x-slot:footer>
    </x-ui.modal>
    @endif

    {{-- ========== MODAL EDIT ========== --}}
    @if($isEditModalOpen)
    <x-ui.modal id="modal-edit-se-value" wire:key="modal-edit-se-value-{{ $editId }}" title="Edit Target SE Value" icon="pencil-square" size="md" open="true" wire:close="closeEditModal">
        <form wire:submit.prevent="saveEdit" id="form-edit-se-value">
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Bulan</label>
                    <input type="text" class="input input-sm input-bordered w-full bg-base-200/50" wire:model="editBulan" readonly>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Distributor</label>
                        <input type="text" class="input input-sm input-bordered w-full bg-base-200/50" wire:model="editDistributorCode" readonly>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Salesman</label>
                        <input type="text" class="input input-sm input-bordered w-full bg-base-200/50" wire:model="editSalesmanCode" readonly>
                    </div>
                </div>

                <div class="space-y-1.5 pt-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Target Value <span class="text-error">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-base-content/50">Rp</span>
                        <input wire:model="editTarget" type="number" step="any" min="0" class="input input-bordered w-full bg-base-100 border-base-300 rounded-xl focus:ring-2 focus:ring-primary/50 pl-10" required>
                    </div>
                    @error('editTarget') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" wire:click="closeEditModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
            <button form="form-edit-se-value" type="submit" class="btn btn-primary text-white rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2" wire:loading.attr="disabled" wire:target="saveEdit">
                <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                <span wire:loading wire:target="saveEdit" class="loading loading-spinner loading-xs"></span>
            </button>
        </x-slot:footer>
    </x-ui.modal>
    @endif

    {{-- ========== MODAL SWAP TARGET ========== --}}
    @if($isSwapModalOpen)
    <x-ui.modal id="modal-swap-se-value" wire:key="modal-swap-se-value-{{ $swapSourceId }}" title="Tukar Target (Swap)" icon="arrows-right-left" size="lg" open="true" wire:close="closeSwapModal">
        <form wire:submit.prevent="processSwap" id="form-swap-se-value">
            <div class="space-y-5">
                @if($swapSourceData)
                <div class="bg-base-200 p-4 rounded-xl space-y-2 border border-base-300">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-base-content/70">Bulan</span>
                        <span class="font-bold">{{ $swapSourceData->bulan }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-base-content/70">Distributor</span>
                        <span class="font-bold">{{ $swapSourceData->distributor_code }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-base-content/70">SE Sumber (A)</span>
                        <span class="font-bold text-primary">{{ $swapSourceData->salesman_code }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-base-content/70">Target SE A</span>
                        <span class="font-bold text-success">Rp {{ number_format($swapSourceData->target, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="flex justify-center -my-2 relative z-10">
                    <div class="bg-base-100 border border-base-300 p-2 rounded-full text-warning shadow-sm">
                        <x-heroicon-s-arrows-up-down class="w-5 h-5" />
                    </div>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Pilih SE Tujuan (B) <span class="text-error">*</span></label>
                    <select wire:model.live="swapTargetId" class="select select-bordered w-full rounded-xl focus:outline-none focus:ring-2 focus:ring-warning/50">
                        <option value="">-- Pilih Salesman (SE) --</option>
                        @foreach($swapListSE as $se)
                            <option value="{{ $se->id }}">{{ $se->salesman_code }} (Target: Rp {{ number_format($se->target, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                    @error('swapTargetId') <span class="text-error text-[10px] font-medium ml-1">{{ $message }}</span> @enderror
                </div>
                
                <p class="text-xs text-base-content/60 text-center mt-2">
                    Nilai target <strong>SE A</strong> akan diberikan ke <strong>SE B</strong>, dan sebaliknya.
                </p>
                @endif
            </div>
        </form>

        <x-slot:footer>
            <button type="button" wire:click="closeSwapModal" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
            <button form="form-swap-se-value" type="submit" class="btn btn-warning text-warning-content rounded-xl px-8 normal-case shadow-sm shadow-warning/20 gap-2" {{ empty($swapListSE) ? 'disabled' : '' }} wire:loading.attr="disabled" wire:target="processSwap">
                <span wire:loading.remove wire:target="processSwap">Proses Tukar</span>
                <span wire:loading wire:target="processSwap" class="loading loading-spinner loading-xs"></span>
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
