<div class="contents">
    {{-- Trigger Button (Rendered wherever the component is placed) --}}
    <button type="button" onclick="document.getElementById('modal-import-jks').showModal()" class="btn btn-sm btn-info text-white gap-1 shadow-sm">
        <x-heroicon-s-document-arrow-up class="w-4 h-4" />
        Import
    </button>

    {{-- The Modal --}}
    <dialog id="modal-import-jks" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
        <div class="modal-box p-0 overflow-hidden relative">
            
            {{-- Header --}}
            <div class="bg-base-200/50 p-4 border-b border-base-300 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg text-base-content/90 flex items-center gap-2">
                        <x-heroicon-s-document-arrow-up class="w-5 h-5 text-info" />
                        Import Jadwal JKS Massal
                    </h3>
                    <p class="text-xs text-base-content/60 font-medium mt-1">Unggah file Excel hasil export (maksimal 20.000 baris).</p>
                    <a href="{{ route('call-plan.jks-salesmans.export-template') }}" class="text-[10px] text-info hover:underline inline-flex items-center gap-1 mt-1 font-bold">
                        <x-heroicon-s-arrow-down-tray class="w-3 h-3" />
                        Download Template Standar
                    </a>
                </div>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost" wire:click="resetState">✕</button>
                </form>
            </div>

            <div class="p-5">
                {{-- PHASE: IDLE (Upload Form) --}}
                @if($currentPhase === 'idle')
                    <div class="form-control mb-5">
                        <label class="label pb-1">
                            <span class="label-text font-bold text-base-content/70 uppercase text-xs">Pilih File Excel</span>
                        </label>
                        <input type="file" wire:model="importFile" class="file-input file-input-bordered file-input-info w-full" accept=".xlsx,.xls,.csv" />
                        <div wire:loading wire:target="importFile" class="text-xs text-info mt-2 font-medium">
                            <span class="loading loading-spinner loading-xs inline-block align-middle mr-1"></span> Mengunggah file ke server...
                        </div>
                        @error('importFile') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control mb-2">
                        <label class="label pb-1">
                            <span class="label-text font-bold text-base-content/70 uppercase text-xs">Metode Import</span>
                        </label>
                    </div>
                    
                    <div class="grid gap-3">
                        {{-- Mode: Delsert --}}
                        <label class="cursor-pointer border rounded-xl p-4 transition-all duration-200 hover:bg-info/5 {{ $importMode === 'delsert' ? 'border-info bg-info/5 ring-1 ring-info shadow-sm' : 'border-base-300 bg-base-100' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio" wire:model="importMode" value="delsert" class="radio radio-info mt-1" />
                                <div>
                                    <h4 class="font-bold text-sm {{ $importMode === 'delsert' ? 'text-info' : 'text-base-content/80' }}">Timpa Total (Delsert) <div class="badge badge-sm badge-info badge-outline ml-1">Direkomendasikan</div></h4>
                                    <p class="text-xs text-base-content/60 mt-1 leading-relaxed">
                                        Menghapus jadwal lama milik SE yang terdaftar di Excel, dan menimpanya dengan jadwal baru dari Excel. (Sangat aman, data SE lain tidak tersentuh).
                                    </p>
                                </div>
                            </div>
                        </label>
                        
                        {{-- Mode: Upsert --}}
                        <label class="cursor-pointer border rounded-xl p-4 transition-all duration-200 hover:bg-info/5 {{ $importMode === 'upsert' ? 'border-info bg-info/5 ring-1 ring-info shadow-sm' : 'border-base-300 bg-base-100' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio" wire:model="importMode" value="upsert" class="radio radio-info mt-1" />
                                <div>
                                    <h4 class="font-bold text-sm {{ $importMode === 'upsert' ? 'text-info' : 'text-base-content/80' }}">Tambah Baru (Upsert)</h4>
                                    <p class="text-xs text-base-content/60 mt-1 leading-relaxed">
                                        Menyisipkan jadwal toko yang belum pernah ada sebelumnya berdasarkan kombinasi persis Hari dan Minggu. (Jadwal lama tidak dihapus/diupdate).
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <form method="dialog"><button class="btn btn-ghost" wire:click="resetState">Batal</button></form>
                        <button class="btn btn-info text-white" wire:click="startImport" wire:loading.attr="disabled" @if(!$importFile) disabled @endif>
                            <span wire:loading wire:target="startImport" class="loading loading-spinner loading-sm"></span>
                            <span wire:loading.remove wire:target="startImport"><x-heroicon-s-play class="w-5 h-5" /></span>
                            Mulai Import
                        </button>
                    </div>

                {{-- PHASE: UPLOADING / VALIDATING / EXECUTING --}}
                @elseif(in_array($currentPhase, ['uploading', 'validating', 'executing']))
                    <div class="py-6 text-center" wire:poll.1000ms="{{ $currentPhase === 'validating' ? 'processValidationChunk' : ($currentPhase === 'executing' ? 'processExecutionChunk' : '') }}">
                        
                        <div class="mb-4">
                            @if($currentPhase === 'uploading')
                                <div class="w-16 h-16 rounded-full bg-info/10 flex items-center justify-center mx-auto mb-3 text-info">
                                    <x-heroicon-s-document-arrow-up class="w-8 h-8 animate-bounce" />
                                </div>
                                <h4 class="font-bold text-lg">Membaca File Excel...</h4>
                                <p class="text-xs text-base-content/60">Mohon tunggu, sedang memuat baris ke sistem.</p>
                            @elseif($currentPhase === 'validating')
                                <div class="w-16 h-16 rounded-full bg-warning/10 flex items-center justify-center mx-auto mb-3 text-warning">
                                    <span class="loading loading-ring loading-lg"></span>
                                </div>
                                <h4 class="font-bold text-lg">Memvalidasi Data Master...</h4>
                                <p class="text-xs text-base-content/60">Mengecek Distributor dan Salesman ke sistem.</p>
                            @elseif($currentPhase === 'executing')
                                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3 text-primary">
                                    <span class="loading loading-spinner loading-lg"></span>
                                </div>
                                <h4 class="font-bold text-lg">Mengeksekusi Data ke Database...</h4>
                                <p class="text-xs text-base-content/60">Menyimpan jadwal. Mohon jangan tutup jendela ini.</p>
                            @endif
                        </div>

                        {{-- Progress Bar --}}
                        @php
                            $percentage = $totalRows > 0 ? min(100, round(($processedRows / $totalRows) * 100)) : 0;
                        @endphp
                        <div class="w-full bg-base-200 rounded-full h-4 mb-2 overflow-hidden border border-base-300">
                            <div class="bg-info h-4 rounded-full transition-all duration-500 ease-out flex items-center justify-center relative overflow-hidden" style="width: {{ $percentage }}%">
                                <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-base-content/60">
                            <span>{{ number_format($percentage) }}% Selesai</span>
                            <span>{{ number_format($processedRows) }} / {{ number_format($totalRows) }} Baris</span>
                        </div>
                    </div>

                {{-- PHASE: COMPLETED --}}
                @elseif($currentPhase === 'completed')
                    <div class="py-4 text-center">
                        <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-4 text-success">
                            <x-heroicon-s-check-circle class="w-12 h-12" />
                        </div>
                        <h4 class="font-black text-2xl mb-1 text-base-content/90">Import Selesai!</h4>
                        <p class="text-sm text-base-content/60 mb-6">Proses {{ $importMode === 'delsert' ? 'Timpa Ulang' : 'Tambah Baru' }} jadwal telah selesai.</p>
                        
                        <div class="flex gap-4 mb-6">
                            <div class="flex-1 bg-success/10 border border-success/20 rounded-xl p-4 text-center">
                                <p class="text-xs font-bold text-success/70 uppercase mb-1">Berhasil</p>
                                <div class="text-3xl font-black text-success">{{ number_format($successCount) }}</div>
                                <p class="text-[10px] mt-1 text-success/60">Baris tersimpan</p>
                            </div>
                            <div class="flex-1 bg-error/10 border border-error/20 rounded-xl p-4 text-center relative overflow-hidden">
                                <p class="text-xs font-bold text-error/70 uppercase mb-1">Gagal (Error)</p>
                                <div class="text-3xl font-black text-error">{{ number_format($failedCount) }}</div>
                                <p class="text-[10px] mt-1 text-error/60">Baris dilewati</p>
                            </div>
                        </div>

                        @if($failedCount > 0)
                            <div class="alert alert-warning text-sm mb-6 flex flex-col items-start gap-2 shadow-sm text-left">
                                <div class="flex items-center gap-2 font-bold w-full">
                                    <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                                    Perhatian! Ada {{ number_format($failedCount) }} baris yang gagal.
                                </div>
                                <p class="text-xs leading-relaxed opacity-90">Sistem mengabaikan baris yang gagal dan tidak membatalkan proses yang sukses. Anda dapat mengunduh log baris yang gagal ini, memperbaikinya, dan meng-uploadnya kembali.</p>
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 mt-4">
                            @if($failedCount > 0)
                                <button class="btn btn-warning gap-2" wire:click="downloadErrorLog">
                                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                                    Download Log Error
                                </button>
                            @endif
                            <form method="dialog"><button class="btn btn-ghost" wire:click="resetState">Tutup</button></form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </dialog>
</div>
