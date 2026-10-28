{{-- STANDAR UI FILTER TOOLBAR SISO ANALYTICS --}}
<x-ui.filter-toolbar class="sticky top-0 z-30 rounded-2xl shadow-sm border border-base-300 bg-base-200/60 backdrop-blur-md">

    {{-- 1. Kapsul Periode Bulan & Tahun --}}
    <x-ui.filter-breadcrumb>
        {{-- Bulan Segment --}}
        <div class="relative flex items-center pl-2 pr-1.5 w-32 sm:w-36 shrink-0 min-w-0" title="Bulan Penjualan">
            <x-heroicon-m-calendar class="w-3.5 h-3.5 text-primary/80 shrink-0 mr-1 pointer-events-none" />
            <select wire:model.live="selectedMonth" 
                    class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs font-semibold text-primary w-full min-w-0 truncate pl-0 pr-4 py-1 cursor-pointer">
                @foreach($months as $mNum => $mName)
                    <option value="{{ $mNum }}" class="bg-base-100 text-base-content">{{ $mName }}</option>
                @endforeach
            </select>
            <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2" />
        </div>

        {{-- Tahun Segment --}}
        <div class="relative flex items-center pl-2 pr-1.5 w-24 sm:w-28 shrink-0 min-w-0" title="Tahun Penjualan">
            <x-heroicon-m-calendar-days class="w-3.5 h-3.5 text-base-content/50 shrink-0 mr-1 pointer-events-none" />
            <select wire:model.live="selectedYear" 
                    class="appearance-none bg-none bg-transparent border-none focus:outline-none text-xs font-mono font-bold text-base-content w-full min-w-0 truncate pl-0 pr-4 py-1 cursor-pointer">
                @foreach($years as $y)
                    <option value="{{ $y }}" class="bg-base-100 text-base-content">{{ $y }}</option>
                @endforeach
            </select>
            <x-heroicon-m-chevron-down class="w-3 h-3 text-base-content/40 shrink-0 pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2" />
        </div>
    </x-ui.filter-breadcrumb>

    {{-- 2. Kapsul Hierarki Wilayah (Region > Area > Distributor > Brand) --}}
    <x-ui.filter-breadcrumb>
        {{-- Region Segment --}}
        <x-ui.filter-select 
            wire:model.live="selectedRegion" 
            :options="$regionOptions" 
            placeholder="Semua Region" 
            icon="globe" 
            width="w-28 sm:w-36"
            :disabled="count($regionOptions) <= 1" />

        {{-- Area Segment --}}
        <x-ui.filter-select 
            wire:model.live="selectedArea" 
            :options="$areaOptions" 
            placeholder="Semua Area" 
            icon="map-pin" 
            width="w-28 sm:w-36" 
            :disabled="empty($selectedRegion) || count($areaOptions) <= 1" />

        {{-- Distributor Segment (Micro-Searchable Popover) --}}
        <x-ui.filter-searchable 
            wire:key="dist-capsule-{{ $selectedRegion }}-{{ $selectedArea }}-{{ $selectedDistributor }}"
            wire:model.live="selectedDistributor" 
            :options="$distributorOptions" 
            valueField="distributor_code" 
            labelField="distributor_name" 
            placeholder="Semua Distributor" 
            :allowAll="true"
            searchPlaceholder="Cari nama distributor / kode..." 
            icon="building" 
            width="w-36 sm:w-48" />

        {{-- Brand Segment (Berlaku untuk Grafik & Tabel di Semua Mode) --}}
        <x-ui.filter-select 
            wire:model.live="selectedBrand" 
            :options="$brandOptions" 
            placeholder="Semua Brand" 
            icon="tag" 
            width="w-28 sm:w-36" />
    </x-ui.filter-breadcrumb>

    {{-- 3. Segmented Toggle Tipe: Semua | REG | FEST --}}
    <div class="inline-flex items-center bg-base-100 p-0.5 rounded-xl border border-base-300 shadow-2xs text-xs shrink-0">
        @foreach([['id' => 'ALL', 'label' => 'Semua'], ['id' => 'REG', 'label' => 'REG'], ['id' => 'FEST', 'label' => 'FEST']] as $rf)
            <button type="button"
                    wire:click="setRegFest('{{ $rf['id'] }}')"
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $selectedRegFest === $rf['id'] ? 'bg-primary text-primary-content shadow-xs font-bold' : 'text-base-content/70 hover:text-base-content hover:bg-base-200' }}">
                {{ $rf['label'] }}
            </button>
        @endforeach
    </div>

    {{-- 4. Segmented Toggle Satuan: Karton (KTN) | Rupiah (IDR) --}}
    <div class="inline-flex items-center bg-base-100 p-0.5 rounded-xl border border-base-300 shadow-2xs text-xs shrink-0">
        <button type="button"
                wire:click="setUnitMode('ktn')"
                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $unitMode === 'ktn' ? 'bg-primary text-primary-content shadow-xs font-bold' : 'text-base-content/70 hover:text-base-content hover:bg-base-200' }}">
            Karton (KTN)
        </button>
        <button type="button"
                wire:click="setUnitMode('val')"
                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer {{ $unitMode === 'val' ? 'bg-primary text-primary-content shadow-xs font-bold' : 'text-base-content/70 hover:text-base-content hover:bg-base-200' }}">
            Rupiah (IDR)
        </button>
    </div>

    {{-- Actions Slot: Hanya Tombol Reset (Tanpa Tombol Terapkan yang tidak dibutuhkan) --}}
    <x-slot name="actions">
        @php
            $hasUnlockedRegion = count($regionOptions) > 1 && !empty($selectedRegion);
            $hasUnlockedArea = count($areaOptions) > 1 && !empty($selectedArea);
            $hasUnlockedDist = count($distributorOptions) > 1 && !empty($selectedDistributor);
            $hasActiveFilters = $hasUnlockedRegion || $hasUnlockedArea || $hasUnlockedDist || $selectedBrand || $selectedRegFest !== 'ALL' || ($selectedStatus ?? 'ALL') !== 'ALL' || $search;
        @endphp
        
        @if($hasActiveFilters)
            <button type="button" 
                    wire:click="resetFilters" 
                    class="btn btn-sm btn-ghost border border-error/30 text-error hover:bg-error/10 hover:border-error rounded-xl text-xs font-semibold gap-1.5 cursor-pointer shadow-2xs transition-colors"
                    title="Reset semua filter">
                <x-heroicon-m-arrow-path class="w-3.5 h-3.5" />
                <span>Reset</span>
            </button>
        @endif
    </x-slot>

</x-ui.filter-toolbar>
