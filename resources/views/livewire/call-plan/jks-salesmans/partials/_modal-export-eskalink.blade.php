{{-- Modal Export Eskalink --}}
@if($showExportEskalinkModal)
    <x-ui.modal 
        id="modal_export_eskalink"
        wire:key="modal-export-eskalink-{{ $appliedDistributor ?: 'all' }}"
        title="Export Eskalink" 
        icon="document-arrow-down" 
        size="md" 
        open="true" 
        wire:close="closeExportEskalinkModal" 
        position="top"
    >
        <div class="flex flex-col gap-4 text-xs">
            <div class="alert alert-info shadow-xs py-2.5 px-3.5 border border-info/30 rounded-xl flex items-start gap-2.5">
                <x-heroicon-s-information-circle class="w-5 h-5 shrink-0 text-info mt-0.5" />
                <div>
                    <h4 class="font-bold text-xs text-base-content">Persiapan Export</h4>
                    <p class="text-[11px] text-base-content/70">Pilih metode Flag Delete dan Salesman untuk file Excel Eskalink.</p>
                </div>
            </div>

            <div class="form-control w-full" wire:key="wrap-flag-delete-{{ $appliedDistributor ?: 'all' }}">
                <label class="label pt-0 pb-1.5">
                    <span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">Metode Flag Delete</span>
                </label>
                <select 
                    wire:key="select-flag-delete-{{ $appliedDistributor ?: 'all' }}"
                    wire:model.live="eskalinkFlagDelete" 
                    class="select select-bordered select-sm w-full font-semibold focus:border-success focus:ring-1 focus:ring-success/50 transition-all text-xs"
                >
                    <option value="N">N - Tidak Menghapus (Update/Insert)</option>
                    <option value="Y">Y - Hapus Data Existing</option>
                </select>
                <label class="label pt-1 pb-0">
                    <span class="label-text-alt text-base-content/50 text-[10px]">Pilih 'Y' jika ingin data lama di Eskalink ditimpa/dihapus terlebih dahulu.</span>
                </label>
            </div>
            
            <div class="form-control w-full" wire:key="wrap-salesman-eskalink-{{ $appliedDistributor ?: 'all' }}">
                <label class="label pt-0 pb-1.5">
                    <span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">Filter Salesman</span>
                </label>
                <select 
                    wire:key="select-salesman-eskalink-{{ $appliedDistributor ?: 'all' }}"
                    wire:model.live="exportEskalinkSalesman" 
                    class="select select-bordered select-sm w-full font-semibold focus:border-success focus:ring-1 focus:ring-success/50 transition-all text-xs"
                >
                    <option value="">Semua Salesman</option>
                    @foreach($this->headerSalesmans as $sls)
                        <option value="{{ $sls->salesman_code }}">{{ $sls->salesman_code }} - {{ $sls->salesman_name }}</option>
                    @endforeach
                </select>
                <label class="label pt-1 pb-0">
                    <span class="label-text-alt text-base-content/50 text-[10px]">Kosongkan untuk mengexport seluruh salesman dari distributor ini.</span>
                </label>
            </div>
        </div>

        <x-slot:footer>
            <button type="button" wire:click="closeExportEskalinkModal" class="btn btn-sm btn-ghost font-bold px-4">Batal</button>
            <button type="button" wire:click="executeExportEskalink" class="btn btn-sm btn-success text-white font-bold px-5 shadow-sm shadow-success/20">
                <span wire:loading.remove wire:target="executeExportEskalink" class="flex items-center gap-1.5">
                    <x-heroicon-s-document-arrow-down class="w-4 h-4" />
                    Download Excel
                </span>
                <span wire:loading wire:target="executeExportEskalink" class="flex items-center gap-2">
                    <span class="loading loading-spinner loading-xs"></span> Memproses...
                </span>
            </button>
        </x-slot:footer>
    </x-ui.modal>
@endif
