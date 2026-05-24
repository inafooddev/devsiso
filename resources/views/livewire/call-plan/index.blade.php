<div>
    {{-- ============================================================
         CSS STYLES & LEAFLET CUSTOMIZATION
         ============================================================ --}}
    @push('styles')
    <style>
        /* Scrollbar kustom */
        .custom-scroll::-webkit-scrollbar       { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: oklch(var(--b2)); }
        .custom-scroll::-webkit-scrollbar-thumb { background: oklch(var(--bc) / 0.2); border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: oklch(var(--bc) / 0.4); }

        /* Leaflet UI Overrides */
        .leaflet-div-icon              { background: transparent; border: none; }
        .leaflet-popup-content-wrapper { border-radius: 12px; padding: 0; overflow: hidden; }
        .leaflet-popup-content         { margin: 12px; width: auto !important; }

        /* Tooltip marker */
        .custom-tooltip {
            background: white !important;
            border: 1px solid oklch(var(--p)) !important;
            border-radius: 4px !important;
            padding: 1px 6px !important;
            font-size: 10px !important;
            font-weight: bold !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
            pointer-events: none !important;
        }
        .leaflet-tooltip-top:before { display: none !important; }
        [x-cloak] { display: none !important; }
    </style>
    @endpush

    {{-- ============================================================
         MAIN CONTAINER
         Modal sekarang memakai x-show div biasa (bukan native dialog),
         sehingga tidak ada backdrop yang "tertahan".
         ============================================================ --}}
    <div
        x-data="mapComponent($wire)"
        x-init="initMap()"
        class="flex h-screen w-full overflow-hidden"
    >
        {{-- Panel Kiri: Sidebar --}}
        @include('livewire.call-plan._sidebar')

        {{-- Panel Kanan: Peta + Legenda --}}
        @include('livewire.call-plan._map')

        {{-- MODALS (x-show div — tidak pakai native <dialog>) --}}
        @include('livewire.call-plan._modal-add')
        @include('livewire.call-plan._modal-export')
        @include('livewire.call-plan._modal-edit')
        @include('livewire.call-plan._modal-filter')

    </div>

    {{-- SCRIPTS: Alpine.js mapComponent + Leaflet --}}
    @include('livewire.call-plan._scripts')

</div>