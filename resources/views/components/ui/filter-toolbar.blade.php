<div {{ $attributes->merge(['class' => 'p-2.5 sm:p-3 border-b border-base-300 shrink-0 bg-base-200/30']) }}>
    <div class="flex flex-wrap items-center justify-between gap-2 w-full">
        {{-- Filters Group (Breadcrumb Capsule, Date Pickers, Custom Filter Slots) --}}
        <div class="flex flex-wrap items-center gap-2 max-w-full">
            {{ $slot }}
        </div>

        {{-- Action Buttons Group (Terapkan, Reset, Ekspor, dsb.) --}}
        @if(isset($actions))
            <div class="flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
