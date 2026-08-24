<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Master Customer RWO</x-slot>

    {{-- Notifikasi Toast --}}
    <div 
        x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            timer: null,
            notify(event) {
                this.type = event.detail.type ?? 'success';
                this.message = event.detail.message ?? '';
                this.show = true;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.show = false, this.type === 'error' ? 5000 : 3500);
            }
        }"
        @notify.window="notify($event)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[200] w-[calc(100%-2rem)] max-w-sm pointer-events-none"
        x-cloak
    >
        <div :class="{
                'bg-success/20 text-success border-success/30': type === 'success',
                'bg-error/20 text-error border-error/30': type === 'error',
             }"
             class="alert shadow-lg rounded-2xl border backdrop-blur-sm flex items-center gap-3 pointer-events-auto">
            <template x-if="type === 'success'">
                <x-heroicon-s-check-circle class="w-5 h-5 shrink-0" />
            </template>
            <template x-if="type === 'error'">
                <x-heroicon-s-x-circle class="w-5 h-5 shrink-0" />
            </template>
            <span class="text-sm font-semibold" x-text="message"></span>
        </div>
    </div>

    {{-- TABS --}}
    <x-ui.tab-menu>
        <x-ui.tab-item href="{{ route('rwo.summary') }}">Summary</x-ui.tab-item>
        <x-ui.tab-item href="{{ route('rwo.index') }}" :active="true">Detail</x-ui.tab-item>
    </x-ui.tab-menu>

        @include('livewire.rwo.master-customer.partials.kpi-cards')

{{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-base-200/30">
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                {{-- Search --}}
                <div class="relative group grow md:grow-0">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.500ms="search" type="text"
                           placeholder="Cari RWO..."
                           class="input input-sm input-bordered pl-10 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                </div>
                
                {{-- Validasi Finance --}}
                <select wire:model.live="filter_finance_status" class="select select-sm select-bordered grow sm:grow-0 w-full sm:w-auto rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Validasi</option>
                    <option value="belum">Belum Validasi</option>
                    <option value="revisi">Revisi</option>
                    <option value="final">Finalisasi (Kunci)</option>
                </select>

                {{-- Check SPM --}}
                <select wire:model.live="filter_check_spm" class="select select-sm select-bordered grow sm:grow-0 w-full sm:w-auto rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Check SPM</option>
                    <option value="sudah">Sudah</option>
                    <option value="belum">Belum</option>
                </select>

                {{-- Status Data --}}
                <select wire:model.live="filter_status_data" class="select select-sm select-bordered grow sm:grow-0 w-full sm:w-auto rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Status Data</option>
                    <option value="complete">Complete</option>
                    <option value="not_complete">Not Complete</option>
                </select>

                {{-- KTP --}}
                <select wire:model.live="filter_ktp" class="select select-sm select-bordered grow sm:grow-0 w-full sm:w-auto rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua KTP</option>
                    <option value="lengkap">Lengkap</option>
                    <option value="belum_lengkap">Belum Lengkap</option>
                </select>

                {{-- Rekening --}}
                <select wire:model.live="filter_rekening" class="select select-sm select-bordered grow sm:grow-0 w-full sm:w-auto rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Rekening</option>
                    <option value="lengkap">Lengkap</option>
                    <option value="belum_lengkap">Belum Lengkap</option>
                </select>

                {{-- Geotag --}}
                <select wire:model.live="filter_geotag" class="select select-sm select-bordered grow sm:grow-0 w-full sm:w-auto rounded-xl bg-base-100 border-base-300 text-xs font-semibold focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    <option value="">Semua Geotag</option>
                    <option value="lengkap">Lengkap</option>
                    <option value="belum_lengkap">Belum Lengkap</option>
                </select>

                {{-- Reset Button --}}
                @if(!empty($search) || !empty($filter_finance_status) || !empty($filter_check_spm) || !empty($filter_status_data) || !empty($filter_ktp) || !empty($filter_rekening) || !empty($filter_geotag) || !empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name))
                <button wire:click="resetFilters" class="btn btn-sm btn-ghost text-error hover:bg-error/10 px-2 rounded-xl normal-case" title="Reset semua pencarian & filter">
                    <x-heroicon-o-x-mark class="w-4 h-4" /> Reset
                </button>
                @endif
            </div>
            
            <div class="flex flex-wrap items-center justify-start md:justify-end gap-2 md:gap-3 w-full md:w-auto">
                {{-- Desktop Actions (Hidden on mobile) --}}
                <div class="hidden md:flex items-center gap-2">
                    {{-- Chained Wilayah Filter Button --}}
                    <button wire:click="openFilterModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200 relative {{ (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name)) ? 'border-primary text-primary hover:bg-primary/5' : '' }}">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        <span>Filter</span>
                        @if (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name))
                            <span class="absolute -top-1.5 -right-1.5 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                            </span>
                        @endif
                    </button>

                    {{-- Export --}}
                    @canExport('rwo.index')
                    <button wire:click="openExportModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export
                    </button>
                    @endcanExport

                    {{-- Import --}}
                    @canImport('rwo.index')
                    <button wire:click="openImportModal"
                            class="btn btn-sm btn-outline rounded-xl normal-case gap-2 border-base-300 hover:bg-base-200 transition-all duration-200">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        Import
                    </button>
                    @endcanImport

                    {{-- Sync Pareto --}}
                    @canAdd('rwo.index')
                    <button wire:click="syncTikorPareto"
                            wire:loading.attr="disabled" wire:target="syncTikorPareto"
                            class="btn btn-sm btn-outline btn-info rounded-xl normal-case gap-2 transition-all duration-200">
                        <x-heroicon-s-arrow-path class="w-4 h-4" wire:loading.class="animate-spin" wire:target="syncTikorPareto" />
                        Sync Tikor
                    </button>
                    @endcanAdd
                </div>

                {{-- Mobile Actions Menu (Hidden on Desktop) --}}
                <div class="dropdown dropdown-bottom dropdown-end w-full sm:w-auto md:hidden">
                    <label tabindex="0" class="btn btn-sm btn-outline rounded-xl w-full normal-case gap-2 border-base-300 hover:bg-base-200">
                        <x-heroicon-s-ellipsis-horizontal class="w-4 h-4" />
                        Opsi Lainnya
                    </label>
                    <ul tabindex="0" class="dropdown-content z-50 menu p-2 shadow-lg bg-base-100 rounded-box w-full sm:w-52 mt-1 border border-base-200">
                        <li>
                            <button wire:click="openFilterModal" class="gap-3">
                                <x-heroicon-s-funnel class="w-4 h-4 text-base-content/70" />
                                Filter
                                @if (!empty($filter_region_code) || !empty($filter_area_code) || !empty($filter_branch_name))
                                    <span class="badge badge-primary badge-xs ml-auto"></span>
                                @endif
                            </button>
                        </li>
                        @canExport('rwo.index')
                        <li>
                            <button wire:click="openExportModal" class="gap-3">
                                <x-heroicon-s-arrow-down-tray class="w-4 h-4 text-base-content/70" />
                                Export
                            </button>
                        </li>
                        @endcanExport
                        @canImport('rwo.index')
                        <li>
                            <button wire:click="openImportModal" class="gap-3">
                                <x-heroicon-s-arrow-up-tray class="w-4 h-4 text-base-content/70" />
                                Import
                            </button>
                        </li>
                        @endcanImport
                        @canAdd('rwo.index')
                        <li>
                            <button wire:click="syncTikorPareto" class="gap-3 text-info">
                                <x-heroicon-s-arrow-path class="w-4 h-4" wire:loading.class="animate-spin" wire:target="syncTikorPareto" />
                                Sync Tikor
                            </button>
                        </li>
                        @endcanAdd
                    </ul>
                </div>

                {{-- Tambah --}}
                @canAdd('rwo.index')
                <button wire:click="openCreateModal"
                        class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20 w-full sm:w-auto">
                    <x-heroicon-s-plus class="w-4 h-4" />
                    Tambah
                </button>
                @endcanAdd
            </div>
        </div>

        @include('livewire.rwo.master-customer.partials.data-table')

{{-- Pagination Footer --}}
        <div class="p-3 border-t border-base-300 bg-base-100 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="text-xs text-base-content/50 font-medium">
                @if($outlets->total() > 0)
                    Menampilkan <span class="font-bold text-base-content/70">{{ $outlets->firstItem() }}</span> –
                    <span class="font-bold text-base-content/70">{{ $outlets->lastItem() }}</span>
                    dari <span class="font-bold text-primary">{{ number_format($outlets->total()) }}</span> data
                @else
                    Tidak ada data ditemukan
                @endif
            </div>
            @if($outlets->hasPages())
                {{ $outlets->links() }}
            @endif
        </div>
    </div>

        @include('livewire.rwo.master-customer.partials.form-modal')

@include('livewire.rwo.master-customer.partials.detail-modal')

@include('livewire.rwo.master-customer.partials.import-modal')

@include('livewire.rwo.master-customer.partials.delete-modal')

@include('livewire.rwo.master-customer.partials.filter-modal')

@include('livewire.rwo.master-customer.partials.export-modal')

@include('livewire.rwo.master-customer.partials.photo-modal')

@include('livewire.rwo.master-customer.partials.status-modal')

</div>
