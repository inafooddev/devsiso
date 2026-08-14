<div>
    <dialog id="export_modal" class="modal modal-bottom sm:modal-middle" {{ $isOpen ? 'open' : '' }}>
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-primary">
                <x-heroicon-s-arrow-down-tray class="w-5 h-5" />
                Export Global Insentif
            </h3>
            
            <p class="py-2 text-sm text-base-content/70 mb-4">
                Pilih filter untuk mengekspor data Insentif SE, SPV, dan Kacab sekaligus ke dalam satu file Excel.
            </p>

            <div class="space-y-4">
                <!-- Filter Bulan -->
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Bulan</span></label>
                    <select wire:model="filterBulan" class="select select-bordered select-sm w-full">
                        <option value="">Pilih Bulan</option>
                        @foreach($listBulan as $bulan)
                            <option value="{{ $bulan }}">{{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Region -->
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Region</span></label>
                    <select wire:model.live="filterRegion" class="select select-bordered select-sm w-full">
                        <option value="">Pilih Region (Wajib 1)</option>
                        @foreach($listRegions as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Area -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Area <span class="text-xs text-base-content/50 font-normal">(Kosongkan untuk ekspor semua area)</span></span>
                    </label>
                    <div class="bg-base-200 p-3 rounded-lg max-h-40 overflow-y-auto custom-scrollbar border border-base-300">
                        @if($filterRegion && count($listAreas) > 0)
                            <div class="flex flex-col gap-2">
                                @foreach($listAreas as $area)
                                <label class="cursor-pointer label justify-start gap-3 py-1">
                                    <input type="checkbox" wire:model="filterArea" value="{{ $area }}" class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="label-text">{{ $area }}</span>
                                </label>
                                @endforeach
                            </div>
                        @elseif(!$filterRegion)
                            <p class="text-xs text-center text-base-content/50 py-2">Pilih Region terlebih dahulu</p>
                        @else
                            <p class="text-xs text-center text-base-content/50 py-2">Tidak ada data area di region ini</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button wire:click="closeModal" class="btn btn-sm btn-ghost">Batal</button>
                <button wire:click="download" class="btn btn-sm btn-primary gap-2" wire:loading.attr="disabled" wire:target="download">
                    <span wire:loading.remove wire:target="download">
                        <x-heroicon-s-document-arrow-down class="w-4 h-4" /> Export Sekarang
                    </span>
                    <span wire:loading wire:target="download">
                        <span class="loading loading-spinner loading-xs"></span> Mengekspor...
                    </span>
                </button>
            </div>
        </div>
        
        <!-- Backdrop -->
        <div class="modal-backdrop bg-neutral/40" wire:click="closeModal"></div>
    </dialog>
</div>
