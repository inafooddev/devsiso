{{-- ============================================================
     MAP CONTAINER + LEGEND
     ============================================================ --}}
<div class="flex-1 relative bg-base-300">

    {{-- Peta Leaflet --}}
    <div id="map" class="h-full w-full outline-none" wire:ignore></div>



    {{-- Legenda Warna (kanan atas) --}}
    <div class="absolute top-4 right-4 z-[1000] bg-base-100 p-3 rounded-xl shadow-lg border border-base-200 text-[10px] space-y-2 max-h-[70%] overflow-y-auto custom-scroll w-44" wire:ignore>
        <div class="font-bold border-b border-base-200 pb-1.5 mb-1 text-base-content flex justify-between items-center">
            <span>Legenda Warna</span>
            <x-heroicon-s-information-circle class="w-3.5 h-3.5 text-base-content/30" />
        </div>

        {{-- Legenda: Hari --}}
        <template x-if="legendType === 'day'">
            <div class="space-y-1.5">
                @foreach($options['days'] as $day)
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shadow-sm flex-shrink-0"
                              style="background-color: {{ $dayColors[$day]['ganjil'] }}"></span>
                        <span class="text-base-content/70">{{ $day }}</span>
                    </div>
                @endforeach
            </div>
        </template>

        {{-- Legenda: Minggu --}}
        <template x-if="legendType === 'week'">
            <div class="space-y-1.5">
                <template x-for="(color, name) in weekColors" :key="name">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shadow-sm flex-shrink-0"
                              :style="'background-color:' + color"></span>
                        <span class="text-base-content/70" x-text="name"></span>
                    </div>
                </template>
            </div>
        </template>

        {{-- Legenda: Salesman --}}
        <template x-if="legendType === 'salesman'">
            <div class="space-y-1.5">
                <template x-for="item in salesmanLegend" :key="item.name">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shadow-sm flex-shrink-0"
                              :style="'background-color:' + item.color"></span>
                        <span class="text-base-content/70 truncate" x-text="item.name"></span>
                    </div>
                </template>
            </div>
        </template>
    </div>

</div>
