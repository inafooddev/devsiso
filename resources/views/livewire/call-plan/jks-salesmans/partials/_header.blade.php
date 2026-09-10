{{-- Header Card & Actions --}}
<div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
    <div class="shrink-0 w-full sm:w-auto">
        <h2 class="text-base md:text-lg font-bold">Jadwal Kunjungan (JKS)</h2>
        <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Manajemen Rute Kunjungan Salesman</p>
    </div>
    
    <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
        @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor)
            {{-- Day Filter (Active Button) --}}
            <div class="join shadow-sm border border-base-300">
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'h1' ? 'btn-active btn-primary' : '' }}" wire:click="toggleHari('h1')">Sen</button>
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'h2' ? 'btn-active btn-primary' : '' }}" wire:click="toggleHari('h2')">Sel</button>
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'h3' ? 'btn-active btn-primary' : '' }}" wire:click="toggleHari('h3')">Rab</button>
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'h4' ? 'btn-active btn-primary' : '' }}" wire:click="toggleHari('h4')">Kam</button>
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'h5' ? 'btn-active btn-primary' : '' }}" wire:click="toggleHari('h5')">Jum</button>
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'h6' ? 'btn-active btn-primary' : '' }}" wire:click="toggleHari('h6')">Sab</button>
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'h7' ? 'btn-active btn-primary' : '' }}" wire:click="toggleHari('h7')">Min</button>
            </div>

            {{-- Week Filter (Active Button) --}}
            <div class="join shadow-sm border border-base-300">
                <button type="button" class="btn btn-sm join-item {{ $selectedMinggu === 'ganjil' ? 'btn-active btn-secondary' : '' }}" wire:click="toggleMinggu('ganjil')">Ganjil</button>
                <button type="button" class="btn btn-sm join-item {{ $selectedMinggu === 'genap' ? 'btn-active btn-secondary' : '' }}" wire:click="toggleMinggu('genap')">Genap</button>
            </div>
            {{-- Salesman Dropdown --}}
            <div class="w-full sm:w-56 z-30">
                @php
                    $salesmanOptions = $this->headerSalesmans->map(fn($s) => ['value' => $s->salesman_code, 'label' => $s->salesman_code . ' - ' . $s->salesman_name])->toArray();
                    if ($appliedDistributor) {
                        array_unshift($salesmanOptions, ['value' => '', 'label' => '-- Semua Salesman --']);
                    }
                @endphp
                <x-searchable-select 
                    wire:key="header-salesman-{{ $appliedDistributor ?: 'empty' }}"
                    wire:model.live="headerSalesman"
                    :options="$salesmanOptions"
                    placeholder="{{ $appliedDistributor ? '-- Pilih Salesman --' : '-- Pilih Distributor --' }}"
                />
            </div>
        @endif

        {{-- Search --}}
        <div class="join">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Cari Customer / Salesman..." 
                class="input input-sm input-bordered join-item w-full max-w-xs"
            />
            <div class="btn btn-sm btn-square join-item">
                <x-heroicon-s-magnifying-glass class="w-4 h-4" />
            </div>
        </div>

        {{-- Filter Button --}}
        <button class="btn btn-sm btn-outline gap-2" @click="$wire.syncFiltersToSelected().then(() => { document.getElementById('filter_modal').showModal() })">
            <x-heroicon-s-funnel class="w-4 h-4" />
            Filter
            @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor)
                <div class="badge badge-xs badge-primary">!</div>
            @endif
        </button>

        {{-- Actions Button --}}
        @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor)
            <button type="button" wire:click="exportExcel" class="btn btn-sm btn-success text-white gap-1 shadow-sm" wire:loading.class="opacity-50 pointer-events-none" wire:target="exportExcel">
                <span wire:loading.remove wire:target="exportExcel">
                    <x-heroicon-s-document-arrow-down class="w-4 h-4" />
                </span>
                <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                Export
            </button>
        @endif
        
        @if($appliedDistributor)
            @livewire('call-plan.jks-salesmans.create-modal', [
                'appliedBulan' => $appliedBulan,
                'appliedDistributor' => $appliedDistributor
            ], key('create-modal-' . $appliedDistributor))
        @endif
        @livewire('call-plan.jks-salesmans.import-modal')

        @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor)
            <button type="button" wire:click="openSwapModal" class="btn btn-sm btn-primary btn-outline gap-1 shadow-sm">
                <x-heroicon-s-arrows-right-left class="w-4 h-4" />
                Tukar Jadwal
            </button>
            <button type="button" wire:click="openBulkDeleteModal" class="btn btn-sm btn-error btn-outline gap-1 shadow-sm">
                <x-heroicon-s-trash class="w-4 h-4" />
                Hapus Massal
            </button>
        @endif
    </div>
</div>
