{{-- ============================================================
     MODAL: Export Call Plan — x-show
     ============================================================ --}}
<div
    x-show="$wire.showExportModal"
    x-transition.opacity.duration.200ms
    x-cloak
    class="fixed inset-0 z-[3000] flex items-center justify-center px-4"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
         @click="$wire.set('showExportModal', false)"></div>

    {{-- Modal Box --}}
    <div class="relative bg-base-100 rounded-2xl shadow-2xl w-full max-w-lg border border-base-300 overflow-hidden z-10">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-success/10">
                    <x-heroicon-s-arrow-up-tray class="w-5 h-5 text-success" />
                </div>
                <h3 class="font-bold text-lg text-base-content">Export Call Plan</h3>
            </div>
            <button wire:click="$set('showExportModal', false)"
                    class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:text-base-content">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4">

            {{-- Region --}}
            <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs font-bold">Region</span></label>
                <select wire:model.live="selectedExpRegion" class="select select-sm select-bordered w-full">
                    <option value="">Pilih Region</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg->region_code }}">{{ $reg->region_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Area --}}
            <div class="form-control transition-opacity {{ !$selectedExpRegion ? 'opacity-40 pointer-events-none' : '' }}">
                <label class="label py-1"><span class="label-text text-xs font-bold">Pilih Area</span></label>
                <select wire:model.live="selectedExpEntity" class="select select-sm select-bordered w-full">
                    <option value="">Pilih Area</option>
                    @foreach($exportEntities as $ent)
                        <option value="{{ $ent->area_code }}">{{ $ent->area_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Branch --}}
            <div class="form-control transition-opacity {{ !$selectedExpEntity ? 'opacity-40 pointer-events-none' : '' }}">
                <label class="label py-1"><span class="label-text text-xs font-bold">Branch (Distributor)</span></label>
                <select wire:model.live="selectedExpBranch" class="select select-sm select-bordered w-full">
                    <option value="">Pilih Branch</option>
                    @foreach($exportBranches as $br)
                        <option value="{{ $br->distributor_code }}">{{ $br->distributor_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Salesman --}}
            <div class="form-control transition-opacity {{ !$selectedExpBranch ? 'opacity-40 pointer-events-none' : '' }}">
                <label class="label py-1">
                    <span class="label-text text-xs font-bold">Pilih Salesman</span>
                    <button wire:click="selectAllExportSls" class="label-text-alt link link-primary text-xs font-bold">
                        {{ (count($selectedExpSls) > 0 && count($selectedExpSls) === count($exportSalesmen)) ? 'Unselect All' : 'Select All' }}
                    </button>
                </label>
                <div class="max-h-[180px] overflow-y-auto border border-base-300 rounded-xl p-3 bg-base-200/30 custom-scroll grid grid-cols-2 gap-2">
                    @forelse($exportSalesmen as $sls)
                        <label wire:key="sls-exp-{{ $sls->slsno }}"
                               class="flex items-center gap-2 p-2 bg-base-100 rounded-lg border border-base-200 cursor-pointer hover:border-success hover:shadow-sm transition-all group">
                            <input type="checkbox" wire:model="selectedExpSls" value="{{ $sls->slsno }}"
                                   class="checkbox checkbox-xs checkbox-success">
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-semibold truncate group-hover:text-success">{{ $sls->slsname }}</span>
                                <span class="text-[9px] text-base-content/40 font-mono">{{ $sls->slsno }}</span>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-2 flex flex-col items-center justify-center py-6 text-base-content/30">
                            <x-heroicon-o-user-minus class="w-8 h-8 mb-2" />
                            <p class="text-[11px] italic">Pilih Branch terlebih dahulu</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col gap-2 px-6 py-4 border-t border-base-300 bg-base-200/50">
            <x-ui.button variant="success" block wire:click="exportExcel"
                         wire:loading.attr="disabled" wire:target="exportExcel">
                <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-2">
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" /> Download Excel Report
                </span>
                <span wire:loading wire:target="exportExcel" class="flex items-center gap-2">
                    <span class="loading loading-spinner loading-sm"></span> Generating Sheets...
                </span>
            </x-ui.button>
            <p class="text-[10px] text-center text-base-content/40">Pastikan semua filter telah terisi sebelum mendownload.</p>
        </div>
    </div>
</div>
