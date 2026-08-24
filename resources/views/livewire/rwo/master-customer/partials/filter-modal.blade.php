{{-- ========== MODAL FILTER WILAYAH (CHAINED) ========== --}}
    <div x-data="{ open: @entangle('isFilterModalOpen') }" 
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
                        <x-heroicon-s-funnel class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Filter Wilayah</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Saring data secara bertingkat</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Region Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Region</label>
                    <select wire:model.live="temp_filter_region_code"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Region</option>
                        @foreach($this->getFilterRegions() as $reg)
                            <option value="{{ $reg->region_code }}">{{ $reg->region_name }} ({{ $reg->region_code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Area Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Area</label>
                    <select wire:model.live="temp_filter_area_code"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Area</option>
                        @foreach($this->getFilterAreas() as $ar)
                            <option value="{{ $ar->area_code }}">{{ $ar->area_name }} ({{ $ar->area_code }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Cabang Select --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Cabang</label>
                    <select wire:model.live="temp_filter_branch_name"
                            class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300 text-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($this->getFilterBranches() as $br)
                            <option value="{{ $br->branch_name }}">{{ $br->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                <button type="button" wire:click="resetFilters" class="btn btn-ghost text-error hover:bg-error/10 rounded-xl normal-case font-bold">Reset Filter</button>
                <div class="flex gap-2">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case font-bold">Batal</button>
                    <button type="button" wire:click="applyFilters" class="btn btn-primary rounded-xl px-8 normal-case shadow-sm shadow-primary/20 font-bold">Terapkan</button>
                </div>
            </div>
        </div>
    </div>