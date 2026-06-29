<div>
    {{-- Modal Import Excel --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" 
             wire:loading.class="pointer-events-none" 
             wire:target="previewImport,executeImport" 
             @click="if(confirm('Batalkan import? File yang dipilih akan hilang.')) open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
                <h3 class="font-bold text-lg text-base-content">Import Data Excel</h3>
                <button @click="if(confirm('Batalkan import? File yang dipilih akan hilang.')) open = false" class="btn btn-sm btn-circle btn-ghost">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
            <form wire:submit.prevent="import">
                <div class="p-6">
                    @if($importStep === 1)
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Start Date</label>
                                <input type="date" wire:model="importStartDate" class="input input-bordered input-sm w-full rounded-xl">
                                @error('importStartDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">End Date</label>
                                <input type="date" wire:model="importEndDate" class="input input-bordered input-sm w-full rounded-xl">
                                @error('importEndDate') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-1.5 mb-4">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Import Method</label>
                            <select wire:model="importMethod" class="select select-bordered select-sm w-full rounded-xl">
                                <option value="full_sync">Full Sync (Hapus & Timpa Data Terkait)</option>
                                <option value="partial_update">Partial Update (Hanya Tambah/Update Data Baru)</option>
                            </select>
                            @error('importMethod') <span class="text-error text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">File Excel (xls, xlsx, csv)</label>
                            <input type="file" wire:model="excel_file" class="file-input file-input-bordered file-input-sm w-full rounded-xl" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            @error('excel_file') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            
                            <div wire:loading wire:target="excel_file" class="text-xs text-info mt-2">Mengunggah...</div>
                        </div>

                        @if(count($importErrors) > 0)
                            <div class="mt-4 bg-error/10 border border-error/20 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-error shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="font-bold text-sm text-error mb-1">Import Gagal</h4>
                                        <p class="text-xs text-base-content/70 mb-3">
                                            Ditemukan {{ count($importErrors) }} baris data yang bermasalah. Silakan perbaiki file Excel Anda dan coba lagi.
                                        </p>
                                        <button type="button" wire:click="downloadErrorLog" class="btn btn-sm btn-error text-white rounded-lg">
                                            <x-heroicon-s-arrow-down-tray class="w-4 h-4" /> Download Log Error
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @elseif($importStep === 2)
                        <div class="bg-info/10 border border-info/20 rounded-xl p-4">
                            <h4 class="font-bold text-sm text-info mb-2">Konfirmasi Import</h4>
                            <div class="space-y-2 text-sm text-base-content/80">
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span>Metode Import:</span>
                                    <span class="font-bold">{{ $importMethod === 'full_sync' ? 'Full Sync' : 'Partial Update' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span>Rentang Tanggal (Filter Hapus):</span>
                                    <span class="font-bold">{{ $importStartDate }} s/d {{ $importEndDate }}</span>
                                </div>
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span>Total Data di Excel:</span>
                                    <span class="font-bold">{{ $previewTotalRows }} Baris</span>
                                </div>
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span>Total Tim Terpengaruh:</span>
                                    <span class="font-bold">{{ $previewTotalTeams }} Tim</span>
                                </div>
                                @if($importMethod === 'full_sync')
                                    <div class="flex justify-between text-error font-bold mt-2">
                                        <span>Data Lama yang Akan Dihapus:</span>
                                        <span>~{{ $previewExistingRows }} Baris</span>
                                    </div>
                                    <p class="text-xs text-error/80 mt-2 mt-2 bg-error/10 p-2 rounded">
                                        <x-heroicon-s-exclamation-triangle class="w-3 h-3 inline mb-0.5" /> 
                                        <strong>Peringatan Full Sync:</strong> Semua data grup JKS pada rentang tanggal dan tim yang ada di excel <strong>akan dihapus secara permanen</strong> dan ditimpa dengan data baru dari excel.
                                    </p>
                                @else
                                    <p class="text-xs text-info/80 mt-2 bg-info/10 p-2 rounded">
                                        <strong>Partial Update:</strong> Data lama tidak dihapus. Sistem hanya akan menambah data baru dan memperbarui data lama jika ada kecocokan kunci (Tanggal, Team, Customer).
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between px-6 py-4 border-t border-base-300 bg-base-200/50">
                    <button type="button" wire:click="downloadTemplate" class="btn btn-ghost text-primary text-xs normal-case hover:bg-primary/10">
                        <x-heroicon-s-document-arrow-down class="w-4 h-4 mr-1" /> Template Excel
                    </button>
                    
                    <div class="flex gap-2">
                        @if($importStep === 1)
                            <button type="button" @click="if(confirm('Batalkan import? File yang dipilih akan hilang.')) open = false" class="btn btn-ghost normal-case text-base-content/70">Batal</button>
                            <button type="button" wire:click="previewImport" class="btn btn-primary rounded-xl px-6 normal-case" wire:loading.attr="disabled" wire:target="excel_file,previewImport">
                                <span wire:loading.remove wire:target="previewImport">Preview</span>
                                <span wire:loading wire:target="previewImport" class="loading loading-spinner loading-sm"></span>
                            </button>
                        @elseif($importStep === 2)
                            <button type="button" wire:click="$set('importStep', 1)" class="btn btn-ghost normal-case text-base-content/70" wire:loading.attr="disabled">Kembali</button>
                            <button type="button" wire:click="executeImport" class="btn btn-primary rounded-xl px-6 normal-case shadow-lg shadow-primary/20" wire:loading.attr="disabled" wire:target="executeImport">
                                <span wire:loading.remove wire:target="executeImport">Jalankan Import</span>
                                <span wire:loading wire:target="executeImport" class="loading loading-spinner loading-sm"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
