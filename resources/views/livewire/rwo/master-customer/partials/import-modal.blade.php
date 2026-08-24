{{-- ========== MODAL IMPORT ========== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5 text-base-content">
            
            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Import Data RWO</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Unggah File Excel (.xlsx / .csv)</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-6 space-y-4">
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/50">Pilih File</label>
                            <button type="button" wire:click="downloadTemplate" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                                <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" /> Download Template
                            </button>
                        </div>
                        <input wire:model="importFile" type="file"
                               class="file-input file-input-bordered file-input-primary w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                        <div class="text-[10px] text-base-content/40 leading-tight mt-1">Ukuran maksimal file: 10MB</div>
                        @error('importFile') <span class="text-error text-xs font-medium ml-1 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-base-200/50 p-4 rounded-xl border border-base-300 space-y-2">
                        <h5 class="text-xs font-bold text-base-content/70">Format Urutan Kolom Excel:</h5>
                        <ol class="list-decimal list-inside text-[11px] text-base-content/60 space-y-1 font-medium">
                            <li>Region Code</li>
                            <li>Region Name</li>
                            <li>Area Code</li>
                            <li>Area Name</li>
                            <li>Cabang (Branch Name)</li>
                            <li>Eskalink Code</li>
                            <li>Customer Code (Wajib & Unik)</li>
                            <li>Customer Name (Wajib)</li>
                            <li>Alamat</li>
                            <li>No HP</li>
                            <li>Latitude</li>
                            <li>Longitude</li>
                            <li>Nama Pemilik Toko</li>
                            <li>Nama KTP</li>
                            <li>NIK KTP</li>
                            <li>[Foto KTP - Dilewati saat import]</li>
                            <li>Nama Bank</li>
                            <li>No Rekening</li>
                            <li>Nama Pemilik Norek</li>
                        </ol>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 gap-2">
                        <span wire:loading.remove wire:target="import">Unggah & Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-s-check-circle wire:loading.remove wire:target="import" class="w-4 h-4" />
                    </button>
                </div>
            </form>
        </div>
    </div>