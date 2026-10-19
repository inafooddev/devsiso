<div>
    {{-- Modal Import Excel --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
        
        <div x-show="open" class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" 
             wire:loading.class="pointer-events-none" 
             wire:target="previewImport,executeImport" 
             @click="if(confirm('Batalkan import? File yang dipilih akan hilang.')) open = false"></div>

        <div x-show="open" class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-xl overflow-hidden flex flex-col">
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
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-sm text-error mb-1">Import Gagal</h4>
                                        <p class="text-xs text-base-content/70 mb-2">
                                            Ditemukan <strong>{{ $totalErrorCount > 0 ? $totalErrorCount : count($importErrors) }}</strong> baris data yang bermasalah.
                                            @if($totalErrorCount > 50)
                                                <span class="text-[0.7rem] text-base-content/60 italic block mt-0.5">(Menampilkan 50 error pertama. Unduh Log Error untuk rincian lengkap).</span>
                                            @endif
                                        </p>

                                        {{-- Ringkasan Error Berkategori --}}
                                        @if(!empty($previewCategorizedErrors))
                                            <div class="my-2.5 space-y-1.5 text-xs bg-base-100/70 p-3 rounded-xl border border-error/20">
                                                @if(($previewCategorizedErrors['mismatch_cust'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['mismatch_cust'] }} baris:</strong> Customer & Distributor tidak cocok di master pareto.</span>
                                                    </div>
                                                @endif
                                                @if(($previewCategorizedErrors['mismatch_team_dist'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['mismatch_team_dist'] }} baris:</strong> Distributor bukan wilayah wewenang Tim/SPV yang dipilih.</span>
                                                    </div>
                                                @endif
                                                @if(($previewCategorizedErrors['out_of_range_date'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['out_of_range_date'] }} baris:</strong> Tanggal di luar rentang filter.</span>
                                                    </div>
                                                @endif
                                                @if(($previewCategorizedErrors['missing_team'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['missing_team'] }} baris:</strong> Kode Team tidak ditemukan di master fsalesman.</span>
                                                    </div>
                                                @endif
                                                @if(($previewCategorizedErrors['missing_dist'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['missing_dist'] }} baris:</strong> Distributor tidak ditemukan di master distributor.</span>
                                                    </div>
                                                @endif
                                                @if(($previewCategorizedErrors['authority_dist'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['authority_dist'] }} baris:</strong> Distributor di luar wilayah otoritas Anda.</span>
                                                    </div>
                                                @endif
                                                @if(($previewCategorizedErrors['empty_required'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['empty_required'] }} baris:</strong> Kolom wajib kosong.</span>
                                                    </div>
                                                @endif
                                                @if(($previewCategorizedErrors['invalid_date'] ?? 0) > 0)
                                                    <div class="flex items-center gap-2 text-error font-medium">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-error shrink-0"></span>
                                                        <span><strong>{{ $previewCategorizedErrors['invalid_date'] }} baris:</strong> Format tanggal tidak valid.</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <button type="button" wire:click="downloadErrorLog" class="btn btn-sm btn-error text-white rounded-lg mt-1">
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
                                    <span>Rentang Tanggal di File Excel:</span>
                                    <span class="font-bold text-primary">{{ $previewMinDate ?? '-' }} s/d {{ $previewMaxDate ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span>Rentang Tanggal (Filter {{ $importMethod === 'full_sync' ? 'Hapus' : 'Target' }}):</span>
                                    <span class="font-bold">{{ $importStartDate }} s/d {{ $importEndDate }}</span>
                                </div>
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span>Total Data Bersih di Excel:</span>
                                    <span class="font-bold text-success">{{ $previewTotalRows }} Baris</span>
                                </div>
                                @if($previewDuplicateRows > 0)
                                    <div class="flex justify-between border-b border-base-300 pb-1 text-warning">
                                        <span>Baris Duplikat Tereliminasi:</span>
                                        <span class="font-bold">{{ $previewDuplicateRows }} Baris</span>
                                    </div>
                                @endif
                                <div class="flex justify-between border-b border-base-300 pb-1">
                                    <span>Total Tim Terpengaruh:</span>
                                    <span class="font-bold">{{ $previewTotalTeams }} Tim</span>
                                </div>
                                @if($importMethod === 'full_sync')
                                    <div class="flex justify-between text-error font-bold mt-2">
                                        <span>Data Lama yang Akan Dihapus:</span>
                                        <span>~{{ $previewExistingRows }} Baris</span>
                                    </div>

                                    @if($isDateSpanMismatch)
                                        <div class="bg-warning/15 border border-warning/40 rounded-xl p-3 my-2 text-warning-content">
                                            <div class="flex items-start gap-2">
                                                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-warning shrink-0 mt-0.5" />
                                                <div class="text-xs">
                                                    <strong class="text-warning font-bold">Peringatan Disparitas Rentang Tanggal:</strong>
                                                    <p class="mt-0.5 text-base-content/80">
                                                        File Excel hanya memuat jadwal dari tanggal <strong>{{ $previewMinDate }}</strong> s/d <strong>{{ $previewMaxDate }}</strong>, namun metode <strong>Full Sync</strong> akan <strong>menghapus seluruh jadwal</strong> dari tanggal <strong>{{ $importStartDate }}</strong> s/d <strong>{{ $importEndDate }}</strong> untuk tim terkait. Pastikan jadwal di luar tanggal tersebut memang dimaksudkan untuk dihapus.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <p class="text-xs text-error/80 mt-2 bg-error/10 p-2 rounded">
                                        <x-heroicon-s-exclamation-triangle class="w-3 h-3 inline mb-0.5" /> 
                                        <strong>Peringatan Full Sync:</strong> Semua data grup JKS pada rentang tanggal dan tim yang ada di excel <strong>akan dihapus secara permanen</strong> dan ditimpa dengan data baru dari excel.
                                    </p>
                                @else
                                    <p class="text-xs text-info/80 mt-2 bg-info/10 p-2 rounded">
                                        <strong>Partial Update:</strong> Data lama tidak dihapus. Sistem hanya akan menambah data baru dan memperbarui data lama jika ada kecocokan kunci (Tanggal, Team, Customer).
                                    </p>
                                @endif

                                {{-- Pratinjau Sampel Data (5 Baris Pertama) --}}
                                @if(!empty($previewSampleData) && count($previewSampleData) > 0)
                                    <div class="mt-3 pt-3 border-t border-base-300">
                                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/60 block mb-2">Pratinjau Sampel Data ({{ count($previewSampleData) }} Baris Pertama):</span>
                                        <div class="overflow-x-auto rounded-lg border border-base-300 bg-base-100 max-h-40">
                                            <table class="table table-xs w-full text-[0.7rem]">
                                                <thead>
                                                    <tr class="bg-base-200 text-base-content/70">
                                                        <th>Tanggal</th>
                                                        <th>Tim</th>
                                                        <th>Distributor</th>
                                                        <th>CustNo</th>
                                                        <th>Nama Toko</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($previewSampleData as $sample)
                                                        <tr class="hover">
                                                            <td class="font-mono whitespace-nowrap">{{ $sample['tanggal'] }}</td>
                                                            <td class="font-semibold whitespace-nowrap">{{ $sample['kode_team'] }}</td>
                                                            <td class="whitespace-nowrap">{{ $sample['distributor_code'] }}</td>
                                                            <td class="font-mono whitespace-nowrap">{{ $sample['custno'] }}</td>
                                                            <td class="truncate max-w-[140px]" title="{{ $sample['custname'] }}">{{ $sample['custname'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
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
