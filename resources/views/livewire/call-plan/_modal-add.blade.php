{{-- ============================================================
     MODAL: Tambah Customer ke Rute — x-show
     ============================================================ --}}
<div
    x-show="$wire.showAddModal"
    x-transition.opacity.duration.200ms
    x-cloak
    class="fixed inset-0 z-[3500] flex items-start justify-center pt-10 px-4"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"
         @click="$wire.set('showAddModal', false)"></div>

    {{-- Modal Box --}}
    <div class="relative bg-base-100 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col border border-base-300 z-10">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-primary/10">
                    <x-heroicon-s-user-plus class="w-5 h-5 text-primary" />
                </div>
                <h3 class="font-bold text-lg text-base-content">Tambah Customer ke Rute</h3>
            </div>
            <button wire:click="$set('showAddModal', false)"
                    class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:text-base-content">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-5 overflow-y-auto custom-scroll flex-1">

            {{-- Bagian 1: Wilayah & Salesman --}}
            <div class="bg-base-200/50 p-4 rounded-xl border border-base-300 space-y-4">
                <div class="flex items-center gap-2 text-primary">
                    <x-heroicon-s-map class="w-4 h-4" />
                    <span class="text-[11px] font-bold uppercase tracking-wider">Informasi Wilayah &amp; Salesman</span>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-[10px] font-bold uppercase">Region</span></label>
                        <select wire:model.live="newRouteRegion" class="select select-xs select-bordered w-full">
                            <option value="">Pilih Region</option>
                            @foreach($regions as $reg)
                                <option value="{{ $reg->region_code }}">{{ $reg->region_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-[10px] font-bold uppercase">Area</span></label>
                        <select wire:model.live="newRouteArea" class="select select-xs select-bordered w-full">
                            <option value="">Pilih Area</option>
                            @foreach($exportEntities as $ent)
                                <option value="{{ $ent->area_code }}">{{ $ent->area_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label py-1"><span class="label-text text-[10px] font-bold uppercase">Distributor</span></label>
                        <select wire:model.live="newRouteDistributor" class="select select-xs select-bordered w-full">
                            <option value="">Pilih Distributor</option>
                            @foreach($exportBranches as $br)
                                <option value="{{ $br->distributor_code }}">{{ $br->distributor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-control border-t border-base-300 pt-3">
                    <label class="label py-1"><span class="label-text text-[10px] font-bold uppercase">Pilih Salesman (SE)</span></label>
                    <select wire:model.live="newRouteSalesman" class="select select-sm select-bordered w-full font-semibold text-primary">
                        <option value="">-- Pilih Salesman Terlebih Dahulu --</option>
                        @foreach($exportSalesmen as $s)
                            <option value="{{ $s->slsno }}" wire:key="sls-opt-{{ $s->slsno }}">
                                {{ $s->slsname }} ({{ $s->slsno }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Bagian 2: Pilih Outlet --}}
            <div class="space-y-4 {{ !$newRouteSalesman ? 'opacity-40 pointer-events-none' : '' }} transition-opacity duration-300">

                <div class="relative">
                    <div class="flex items-center gap-2 mb-2 text-base-content">
                        <x-heroicon-s-building-storefront class="w-4 h-4 text-primary" />
                        <span class="text-[11px] font-bold uppercase tracking-wider">Cari &amp; Pilih Outlet</span>
                    </div>
                    <label class="input input-sm input-bordered flex items-center gap-2 w-full">
                        <x-heroicon-s-magnifying-glass class="w-3.5 h-3.5 text-base-content/40" />
                        <input wire:model.live.debounce.300ms="searchCustomer" type="text"
                               class="grow text-xs outline-none bg-transparent"
                               placeholder="Cari nama toko atau kode outlet...">
                    </label>

                    @if(count($this->masterCustomers) > 0)
                        <div class="absolute z-[5000] w-full bg-base-100 border border-base-300 shadow-2xl rounded-xl mt-1 max-h-60 overflow-y-auto custom-scroll">
                            @foreach($this->masterCustomers as $mc)
                                <div wire:click="addCustomerToSelection('{{ $mc->custno }}', '{{ $mc->custname }}')"
                                     class="p-3 text-xs hover:bg-primary/5 cursor-pointer border-b border-base-200 flex justify-between items-start group transition">
                                    <div class="flex-1 pr-3">
                                        <div class="font-bold flex items-center gap-2 flex-wrap">
                                            <span class="text-primary font-mono">{{ $mc->custno }}</span>
                                            <span class="text-base-content/30">|</span>
                                            <span class="text-base-content group-hover:text-primary transition-colors">{{ $mc->custname }}</span>
                                        </div>
                                        <div class="text-[10px] text-base-content/50 flex items-center gap-1 mt-0.5">
                                            <x-heroicon-s-map-pin class="w-2.5 h-2.5" />
                                            <span class="italic line-clamp-1">{{ $mc->custadd1 ?? 'Alamat tidak tersedia' }}</span>
                                        </div>
                                    </div>
                                    <x-ui.badge variant="primary" size="xs" class="self-center opacity-0 group-hover:opacity-100 transition">
                                        <x-heroicon-s-plus class="w-2.5 h-2.5" />
                                    </x-ui.badge>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tags --}}
                <div class="flex flex-wrap gap-2 min-h-[28px]">
                    @foreach($selectedCustomers as $index => $sc)
                        <x-ui.badge variant="primary">
                            {{ $sc['name'] }}
                            <button wire:click="removeCustomerFromSelection({{ $index }})" class="ml-1 hover:text-error">
                                <x-heroicon-s-x-mark class="w-3 h-3" />
                            </button>
                        </x-ui.badge>
                    @endforeach
                </div>

                {{-- Hari & Minggu --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-[10px] font-bold uppercase flex items-center gap-1">
                                <x-heroicon-s-calendar-days class="w-3.5 h-3.5 text-primary" /> Hari Kunjungan
                            </span>
                        </label>
                        <select wire:model="newRouteDay" class="select select-sm select-bordered w-full">
                            <option value="">Pilih Hari</option>
                            @foreach($options['days'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label py-1">
                            <span class="label-text text-[10px] font-bold uppercase flex items-center gap-1">
                                <x-heroicon-s-calendar class="w-3.5 h-3.5 text-primary" /> Pilih Minggu
                            </span>
                        </label>
                        <div class="grid grid-cols-2 gap-1.5">
                            @foreach($options['weeks'] as $w)
                                <label class="flex items-center gap-2 text-[10px] border border-base-300 p-2 rounded-lg cursor-pointer hover:border-primary hover:bg-primary/5 transition bg-base-100">
                                    <input type="checkbox" wire:model="newRouteWeeks" value="{{ $w }}" class="checkbox checkbox-xs checkbox-primary">
                                    <span class="font-medium">{{ $w }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-base-300 bg-base-200/50">
            <x-ui.button variant="neutral" outline wire:click="$set('showAddModal', false)">Batal</x-ui.button>
            <x-ui.button variant="primary" icon="bookmark-square" wire:click="storeCustomRoute">Simpan Rute Baru</x-ui.button>
        </div>
    </div>
</div>
