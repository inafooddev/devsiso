{{-- ============================================================
     MODAL: Filter Wilayah — menggunakan x-show (bukan native dialog)
     ============================================================ --}}
<div
    x-show="$wire.showFilterModal"
    x-transition.opacity.duration.200ms
    x-cloak
    class="fixed inset-0 z-[2000] flex items-start justify-center pt-16 px-4"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
         @click="$wire.set('showFilterModal', false)"></div>

    {{-- Modal Box --}}
    <div class="relative bg-base-100 rounded-2xl shadow-2xl w-full max-w-md border border-base-300 overflow-hidden z-10">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-primary/10">
                    <x-heroicon-s-adjustments-horizontal class="w-5 h-5 text-primary" />
                </div>
                <h3 class="font-bold text-lg text-base-content">Filter Wilayah</h3>
            </div>
            <button wire:click="$set('showFilterModal', false)"
                    class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:text-base-content">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4">

            {{-- Region --}}
            <div class="form-control">
                <label class="label py-1">
                    <span class="label-text text-xs font-bold">Region</span>
                </label>
                <select wire:model.live="selectedRegion" class="select select-sm select-bordered w-full">
                    <option value="">Pilih Region</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg->region_code }}">{{ $reg->region_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Area --}}
            <div class="form-control {{ !$selectedRegion ? 'opacity-50 pointer-events-none' : '' }}">
                <label class="label py-1">
                    <span class="label-text text-xs font-bold">Area</span>
                </label>
                <select wire:model.live="selectedArea" class="select select-sm select-bordered w-full">
                    <option value="">Pilih Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Distributor --}}
            <div class="form-control {{ !$selectedArea ? 'opacity-50 pointer-events-none' : '' }}">
                <label class="label py-1">
                    <span class="label-text text-xs font-bold">Distributor</span>
                </label>
                <select wire:model.live="selectedDistributor" class="select select-sm select-bordered w-full">
                    <option value="">Pilih Distributor</option>
                    @foreach($distributors as $dist)
                        <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-base-300 bg-base-200/50">
            <x-ui.button variant="neutral" outline wire:click="$set('showFilterModal', false)">Batal</x-ui.button>
            <x-ui.button variant="primary" wire:click="applyAdvancedFilter">
                <x-heroicon-s-check class="w-4 h-4" />
                Simpan &amp; Pilih Salesman
            </x-ui.button>
        </div>
    </div>
</div>
