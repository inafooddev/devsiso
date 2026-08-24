{{-- ========== MODAL IMPORT ========== --}}
<div x-data="{ open: @entangle('isImportModalOpen') }"
        x-show="open" x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 pt-10 overflow-y-auto">

    {{-- Backdrop --}}
    <div x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-base-100/70 backdrop-blur-sm" @click="open = false"></div>

    {{-- Modal Panel --}}
    <div x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg ring-1 ring-base-content/5 text-base-content my-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-success/10 text-success">
                    <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-none">Import Produk Lama</h3>
                    <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Upload file Excel untuk import masal</p>
                </div>
            </div>
            <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Body --}}
        <form wire:submit.prevent="import">
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto bg-base-100 custom-scrollbar">
                
                {{-- Download Template Info --}}
                <div class="alert bg-info/10 text-info border border-info/20 rounded-2xl shadow-sm text-xs mb-4">
                    <x-heroicon-s-information-circle class="w-5 h-5" />
                    <div class="flex-1">
                        <h3 class="font-bold">Info Template</h3>
                        <div class="text-[11px] mt-1 opacity-90">
                            Harap pastikan format file sesuai dengan template.
                            <br/> 
                            <button type="button" wire:click="downloadTemplate" class="link link-primary font-bold inline-flex items-center gap-1 mt-1">
                                <x-heroicon-s-arrow-down-tray class="w-3 h-3" /> Unduh Template Import
                            </button>
                        </div>
                    </div>
                </div>

                {{-- File Input --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">File Upload <span class="text-error">*</span></label>
                    <input type="file" wire:model="importFile" accept=".xlsx, .xls, .csv"
                            class="file-input file-input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 @error('importFile') file-input-error @enderror" />
                    @error('importFile') <span class="text-error text-[10px] font-medium ml-1 flex items-center gap-1 mt-1"><x-heroicon-s-exclamation-circle class="w-3 h-3" />{{ $message }}</span> @enderror
                </div>

                {{-- Loading Indicator --}}
                <div wire:loading wire:target="import" class="w-full text-center">
                    <div class="flex items-center justify-center gap-2 text-primary font-medium text-sm p-4">
                        <span class="loading loading-spinner loading-md"></span>
                        Sedang mengimpor data, mohon tunggu...
                    </div>
                </div>

                {{-- Error Logs Display --}}
                @if(!empty($importErrors))
                    <div class="mt-4">
                        <div class="alert bg-error/10 text-error border border-error/20 rounded-2xl shadow-sm mb-3">
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5 shrink-0" />
                            <div>
                                <h3 class="font-bold text-sm">Terdapat Kesalahan pada Baris Data</h3>
                                <p class="text-[11px] opacity-80 mt-1">Hanya data yang valid yang telah berhasil diimpor.</p>
                            </div>
                        </div>
                        <div class="bg-base-200/50 rounded-xl p-3 border border-base-300 max-h-40 overflow-y-auto custom-scrollbar">
                            <ul class="list-disc list-inside text-xs text-error/80 space-y-1">
                                @foreach($importErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl shrink-0">
                <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Tutup</button>
                <button type="submit" class="btn btn-primary rounded-xl normal-case shadow-sm shadow-primary/20 text-white transition-all duration-200" wire:loading.attr="disabled" wire:target="import, importFile">
                    <span wire:loading.remove wire:target="import">Upload & Import</span>
                    <span wire:loading wire:target="import" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>
    </div>
</div>
