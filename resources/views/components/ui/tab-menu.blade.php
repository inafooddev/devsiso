<div {{ $attributes->merge(['class' => 'shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex justify-between items-center shadow-sm relative z-10 -mb-1 md:-mb-2 gap-4']) }}>
    <div class="tabs tabs-boxed w-fit max-w-full flex-nowrap overflow-x-auto bg-base-200 p-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        {{ $slot }}
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
