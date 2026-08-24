<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
    <x-slot name="title">Data Master Produk Lama</x-slot>

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-success shadow-sm rounded-xl border-none bg-success/20 text-success shrink-0 flex items-start">
            <x-heroicon-s-check-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Sukses</h3>
                <div class="text-xs">{{ session('message') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-success/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" class="alert alert-error shadow-sm rounded-xl border-none bg-error/20 text-error shrink-0 flex items-start">
            <x-heroicon-s-x-circle class="w-5 h-5 mt-0.5 shrink-0" />
            <div class="flex-1">
                <h3 class="font-bold text-[10px] uppercase tracking-wider">Error</h3>
                <div class="text-xs">{{ session('error') }}</div>
            </div>
            <button @click="show = false" class="btn btn-ghost btn-xs btn-circle shrink-0 mt-0.5 opacity-70 hover:opacity-100 hover:bg-error/20 transition-all">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    @endif

    {{-- Main Card (Tabel) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Master Produk Lama</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Kelola data master produk lama</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari produk..." />

                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>

                {{-- Kategori Filter --}}
                <select wire:model.live="kategoriFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Kategori</option>
                    @foreach($this->kategories as $kat)
                        <option value="{{ $kat }}">{{ $kat }}</option>
                    @endforeach
                </select>

                {{-- Top Item Filter --}}
                <select wire:model.live="topItemFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Top Item</option>
                    @foreach($this->topItems as $item)
                        <option value="{{ $item }}">{{ $item }}</option>
                    @endforeach
                </select>

                {{-- Subbrand Filter --}}
                <select wire:model.live="subbrandFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Subbrand</option>
                    @foreach($this->subbrands as $subbrand)
                        <option value="{{ $subbrand }}">{{ $subbrand }}</option>
                    @endforeach
                </select>

                {{-- Divisi Filter --}}
                <select wire:model.live="divisiFilter" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300 font-medium text-xs">
                    <option value="">Semua Divisi</option>
                    @foreach($this->divisis as $divisi)
                        <option value="{{ $divisi }}">{{ $divisi }}</option>
                    @endforeach
                </select>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    @if($search || $statusFilter !== '' || $kategoriFilter !== '' || $topItemFilter !== '' || $subbrandFilter !== '' || $divisiFilter !== '')
                        <button wire:click="resetFilters" class="btn btn-sm btn-ghost text-error/80 hover:text-error hover:bg-error/10 transition-all duration-300 mr-2" title="Reset semua filter">
                            <x-heroicon-s-arrow-path class="w-4 h-4 mr-1" />
                            <span class="text-xs font-semibold hidden sm:inline">Reset</span>
                        </button>
                    @endif
                    <x-ui.action-button type="import" wire:click="openImportModal" />
                    <x-ui.action-button type="export" wire:click="export" />
                    <x-ui.action-button type="add" wire:click="openCreateModal" />
                </div>
            </div>
        </div>

        {{-- Table Partial --}}
        @include('livewire.master-data.product.master-produk-lama.partials.table')

    </div>

    {{-- Modals --}}
    @include('livewire.master-data.product.master-produk-lama.partials.form-modal')
    @include('livewire.master-data.product.master-produk-lama.partials.delete-modal')
    @include('livewire.master-data.product.master-produk-lama.partials.detail-modal')
    @include('livewire.master-data.product.master-produk-lama.partials.import-modal')

</div>
