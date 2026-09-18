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
                <button type="button" class="btn btn-sm join-item {{ $selectedHari === 'non_rute' ? 'btn-active btn-warning' : '' }}" wire:click="toggleHari('non_rute')">Non Rute</button>
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
            @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                <button type="button" wire:click="exportExcel" class="btn btn-sm btn-success text-white gap-1 shadow-sm" wire:loading.class="opacity-50 pointer-events-none" wire:target="exportExcel">
                    <span wire:loading.remove wire:target="exportExcel">
                        <x-heroicon-s-document-arrow-down class="w-4 h-4" />
                    </span>
                    <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                    Export
                </button>
            @endif
        @endif
        
        @if($appliedDistributor)
            @livewire('call-plan.jks-salesmans.create-modal', [
                'appliedBulan' => $appliedBulan,
                'appliedDistributor' => $appliedDistributor
            ], key('create-modal-' . $appliedDistributor))
        @endif
        @livewire('call-plan.jks-salesmans.import-modal')

        @if($appliedRegion || $appliedArea || $appliedSupervisor || $appliedDistributor)
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-sm btn-ghost border border-base-300 shadow-sm gap-1 font-semibold">
                    <x-heroicon-s-cog-6-tooth class="w-4 h-4" />
                    Aksi Lainnya
                    <x-heroicon-s-chevron-down class="w-3 h-3 opacity-50" />
                </div>
                <ul tabindex="0" class="dropdown-content z-50 menu p-2 shadow-xl bg-base-100 rounded-box w-52 border border-base-200 mt-1">
                    @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                        <li>
                            <a wire:click="openSwapModal">
                                <x-heroicon-s-arrows-right-left class="w-4 h-4 text-primary" />
                                Tukar Jadwal
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('user'))
                        <li>
                            <a wire:click="openCopyModal">
                                <x-heroicon-s-document-duplicate class="w-4 h-4 text-info" />
                                Salin Periode
                            </a>
                        </li>
                        @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                            <li>
                                <a wire:click="openCleansingModal" class="text-warning hover:bg-warning/10 hover:text-warning">
                                    <x-heroicon-s-sparkles class="w-4 h-4" />
                                    Cleansing Duplicate
                                </a>
                            </li>
                        @endif
                    @endif
                    @if(auth()->check() && auth()->user()->hasRole(['admin', 'spm', 'admspm', 'spvlapangan', 'asm', 'rsm', 'spvspm']))
                        <li>
                            <a wire:click="openBulkDeleteModal" class="text-error hover:bg-error/10 hover:text-error">
                                <x-heroicon-s-trash class="w-4 h-4" />
                                Hapus Massal
                            </a>
                        </li>
                    @endif
                    <div class="divider my-1"></div>
                    <li>
                        <a wire:click="openExportEskalinkModal">
                            <x-heroicon-s-document-arrow-down class="w-4 h-4 text-success" />
                            Export Eskalink
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </div>
</div>
