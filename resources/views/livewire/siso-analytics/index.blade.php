<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">SISO Analytics — Sell In vs Sell Out</x-slot>

    {{-- Loading Overlay --}}
    <div wire:loading wire:target="selectedYear, selectedMonth, selectedRegFest, selectedStatus, selectedRegion, selectedArea, selectedDistributor, selectedBrand, setViewMode, setUnitMode, setRegFest, setStatus, resetFilters, sortBy, export" 
         class="fixed inset-0 z-50 bg-base-300/30 flex items-center justify-center backdrop-blur-[2px] transition-opacity" style="z-index: 9999;">
        <div class="bg-base-100 px-6 py-5 rounded-2xl shadow-2xl flex items-center gap-4 border border-base-200">
            <span class="loading loading-spinner loading-md text-primary"></span>
            <div>
                <h3 class="text-sm font-bold text-base-content">Memperbarui Analisa SISO</h3>
                <p class="text-xs text-base-content/60">Menyinkronkan grafik & metrik penjualan...</p>
            </div>
        </div>
    </div>

    {{-- Global Tab Navigation --}}
    @include('livewire.siso-analytics.partials._tabs')

    {{-- MAIN SCROLLABLE CONTAINER --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden -mx-3 md:-mx-4 lg:-mx-6 -mb-3 md:-mb-4 lg:-mb-6 px-3 md:px-4 lg:px-6 pb-8 space-y-5 relative">
        
        {{-- 1. TOP CONTROL & FILTER BAR (STICKY) --}}
        @include('livewire.siso-analytics.partials._filter')

        {{-- 2. EXECUTIVE KPI SUMMARY CARDS --}}
        @include('livewire.siso-analytics.partials._kpi_cards')

        {{-- 3. ANALYTICAL CHARTS SUITE --}}
        @include('livewire.siso-analytics.partials._charts')

        {{-- 4. FINANCIAL-GRADE DATA TABLE --}}
        @include('livewire.siso-analytics.partials._table')

    </div>
</div>
