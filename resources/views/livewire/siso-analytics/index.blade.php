<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 w-full h-full">
    <x-slot name="title">SISO Analytics - Grafik Komparasi Penjualan</x-slot>

    {{-- Loading Overlay --}}
    <div wire:loading wire:target="selectedYear, selectedMonth, selectedRegion, selectedArea, selectedDistributor, selectedBrand, setViewMode, setUnitMode, sortBy, export" 
         class="fixed inset-0 z-50 bg-base-300/40 flex items-center justify-center backdrop-blur-sm transition-opacity" style="z-index: 9999;">
        <div class="bg-base-100 p-8 rounded-2xl shadow-xl flex flex-col items-center border border-base-200">
            <span class="loading loading-spinner loading-lg text-primary mb-4"></span>
            <h3 class="text-lg font-bold text-base-content">Memuat Grafik & Data SISO...</h3>
            <p class="text-sm text-base-content/60 mt-1">Menyusun visualisasi tren Sell In vs Sell Out.</p>
        </div>
    </div>

    {{-- Multi-Tabs Navigation (Tetap di Atas) --}}
    <div class="shrink-0">
        <x-ui.tab-menu>
            <a href="{{ route('siso-analytics.index') }}" class="tab tab-active bg-primary text-primary-content font-bold">
                <x-heroicon-s-chart-bar class="w-4 h-4 mr-2" />
                1. Analisa Grafik Sell In vs Sell Out
            </a>
            <a href="#" class="tab opacity-50 cursor-not-allowed select-none">
                <x-heroicon-s-clipboard-document-check class="w-4 h-4 mr-2" />
                2. Rekonsiliasi Stok
                <span class="badge badge-xs badge-ghost ml-2">Tahap 2</span>
            </a>
            <a href="#" class="tab opacity-50 cursor-not-allowed select-none">
                <x-heroicon-s-bolt class="w-4 h-4 mr-2" />
                3. Fast / Slow Moving
                <span class="badge badge-xs badge-ghost ml-2">Tahap 3</span>
            </a>
            <a href="#" class="tab opacity-50 cursor-not-allowed select-none">
                <x-heroicon-s-shopping-cart class="w-4 h-4 mr-2" />
                4. Rekomendasi PO
                <span class="badge badge-xs badge-ghost ml-2">Tahap 4</span>
            </a>
            <a href="#" class="tab opacity-50 cursor-not-allowed select-none">
                <x-heroicon-s-arrow-up-tray class="w-4 h-4 mr-2" />
                5. Upload Stok Opname
                <span class="badge badge-xs badge-ghost ml-2">Tahap 5</span>
            </a>
        </x-ui.tab-menu>
    </div>

    {{-- MAIN CARD WRAPPER DENGAN OVERFLOW SCROLL --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- BODY SCROLL CONTAINER --}}
        <div class="flex-1 overflow-y-auto overflow-x-hidden bg-base-200/40 w-full relative p-3 md:p-5 flex flex-col gap-4 md:gap-6">
            
            {{-- 4 Kartu Ringkasan Penjualan Komersial --}}
            @include('livewire.siso-analytics.partials._kpi_cards')

            {{-- Filter Bar (Periode & Wilayah) --}}
            @include('livewire.siso-analytics.partials._filter')

            {{-- SUITE GRAFIK UTAMA APEXCHARTS --}}
            @include('livewire.siso-analytics.partials._charts')

            {{-- Data Rincian Pendukung di Bawah Grafik --}}
            @include('livewire.siso-analytics.partials._table')

        </div>

    </div>

</div>
