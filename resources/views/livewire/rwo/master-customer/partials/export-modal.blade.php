{{-- ========== MODAL EXPORT OPTIONS ========== --}}
    <div x-data="{ open: @entangle('isExportModalOpen') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
        
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" @click="open = false"></div>

        <!-- Modal Panel -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-md bg-base-100 rounded-[2rem] shadow-2xl border border-base-200/50 overflow-hidden ring-1 ring-black/5">
            
            <div class="p-6 sm:p-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center text-success">
                            <x-heroicon-s-arrow-down-tray class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-base-content">Export Excel</h3>
                            <p class="text-sm text-base-content/60 mt-1">Pilih wilayah & lampiran foto</p>
                        </div>
                    </div>
                    <button @click="open = false" class="btn btn-circle btn-ghost btn-sm text-base-content/40 hover:text-base-content hover:bg-base-200 transition-colors">
                        <x-heroicon-s-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <!-- Content -->
                <div class="space-y-4 mb-6">
                    {{-- Filter Wilayah untuk Export --}}
                    <div class="p-3.5 bg-base-200/60 rounded-2xl border border-base-300 space-y-3">
                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-0.5">Filter Region</label>
                            <select wire:model.live="export_region_code"
                                    class="select select-sm select-bordered w-full rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all">
                                <option value="">Semua Region</option>
                                @foreach($this->getFilterRegions() as $reg)
                                    <option value="{{ $reg->region_code }}">{{ $reg->region_name }} ({{ $reg->region_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-0.5">Filter Area</label>
                            <select wire:model.live="export_area_code"
                                    class="select select-sm select-bordered w-full rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all">
                                <option value="">Semua Area</option>
                                @foreach($this->getExportFilterAreas() as $ar)
                                    <option value="{{ $ar->area_code }}">{{ $ar->area_name }} ({{ $ar->area_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Checklist Lampiran Foto --}}
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/60 ml-0.5">Lampiran Foto</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                                <input type="checkbox" wire:model="export_foto_ktp" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                                <span class="text-sm font-medium text-base-content">Sertakan Foto KTP</span>
                            </label>
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                                <input type="checkbox" wire:model="export_foto_toko" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                                <span class="text-sm font-medium text-base-content">Sertakan Foto Toko (GPS)</span>
                            </label>
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                                <input type="checkbox" wire:model="export_foto_toko2" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                                <span class="text-sm font-medium text-base-content">Sertakan Foto Toko (Tampak Depan)</span>
                            </label>
                            <label class="flex items-center gap-3 p-2.5 rounded-xl border border-base-300 hover:bg-base-200/50 cursor-pointer transition-colors">
                                <input type="checkbox" wire:model="export_foto_toko3" class="checkbox checkbox-primary checkbox-sm rounded-lg" />
                                <span class="text-sm font-medium text-base-content">Sertakan Foto Toko (Tampak Dalam)</span>
                            </label>
                        </div>
                    </div>

                    <p class="text-xs text-warning/80 italic">
                        <x-heroicon-s-information-circle class="w-3.5 h-3.5 inline mr-1" />
                        Disarankan memilih filter Area jika menyertakan foto agar proses export cepat dan lancar.
                    </p>
                </div>

                <!-- Footer -->
                <div class="flex gap-3">
                    <button @click="open = false" class="btn btn-ghost rounded-xl flex-1 border-base-300 border hover:bg-base-200 normal-case font-semibold">
                        Batal
                    </button>
                    <button wire:click="export" wire:loading.attr="disabled" wire:target="export" class="btn btn-success rounded-xl flex-1 text-white shadow-lg shadow-success/20 normal-case font-semibold gap-2">
                        <span wire:loading.remove wire:target="export"><x-heroicon-s-arrow-down-tray class="w-5 h-5" /> Export Sekarang</span>
                        <span wire:loading wire:target="export" class="flex items-center gap-2"><span class="loading loading-spinner loading-sm"></span> Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>