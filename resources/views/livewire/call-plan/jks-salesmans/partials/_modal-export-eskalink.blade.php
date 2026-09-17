<input type="checkbox" id="modal_export_eskalink" class="modal-toggle" wire:model.live="showExportEskalinkModal" />
<div class="modal modal-bottom sm:modal-middle bg-base-300/50 backdrop-blur-sm">
    <div class="modal-box p-0 overflow-hidden shadow-2xl bg-base-100 rounded-2xl flex flex-col max-h-[85vh]">
        <div class="bg-gradient-to-r from-success to-emerald-600 p-5 shrink-0 flex items-center justify-between text-white">
            <h3 class="font-black text-xl tracking-wide flex items-center gap-2">
                <x-heroicon-s-document-arrow-down class="w-6 h-6" />
                Export Eskalink
            </h3>
            <button wire:click="closeExportEskalinkModal" class="btn btn-sm btn-circle btn-ghost text-white">
                <x-heroicon-s-x-mark class="w-5 h-5"/>
            </button>
        </div>

        <div class="p-6 overflow-y-auto grow bg-base-50 flex flex-col gap-5">
            <div class="alert alert-info shadow-sm py-3 px-4 border border-info/30">
                <x-heroicon-s-information-circle class="w-5 h-5 shrink-0" />
                <div>
                    <h3 class="font-bold text-sm">Persiapan Export</h3>
                    <div class="text-xs">Pilih metode Flag Delete dan Salesman untuk file Excel Eskalink.</div>
                </div>
            </div>

            <div class="form-control w-full">
                <label class="label pt-0 pb-1.5"><span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">Metode Flag Delete</span></label>
                <select wire:model="eskalinkFlagDelete" class="select select-bordered select-md w-full font-semibold focus:border-success focus:ring-1 focus:ring-success/50 transition-all">
                    <option value="N">N - Tidak Menghapus (Update/Insert)</option>
                    <option value="Y">Y - Hapus Data Existing</option>
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label pt-0 pb-1.5"><span class="label-text font-bold text-base-content/80 text-xs uppercase tracking-wider">Filter Salesman</span></label>
                <select wire:model="exportEskalinkSalesman" class="select select-bordered select-md w-full font-semibold focus:border-success focus:ring-1 focus:ring-success/50 transition-all">
                    <option value="">Semua Salesman</option>
                    @foreach($this->headerSalesmans as $sls)
                        <option value="{{ $sls->salesman_code }}">{{ $sls->salesman_code }} - {{ $sls->salesman_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-base-200/50 p-5 shrink-0 border-t border-base-300 flex justify-end gap-2">
            <button wire:click="closeExportEskalinkModal" class="btn btn-md font-bold px-6">Batal</button>
            <button wire:click="executeExportEskalink" class="btn btn-md btn-success text-white font-bold px-6 shadow-md shadow-success/20">
                <span wire:loading.remove wire:target="executeExportEskalink">Download Excel</span>
                <span wire:loading wire:target="executeExportEskalink" class="flex items-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span> Memproses...
                </span>
            </button>
        </div>
    </div>
</div>
