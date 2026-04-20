@props([
    'title' => null,
    'icon' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow p-6']) }}>
    @if($title || $icon)
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            @if($icon)
            <div class="flex-shrink-0">
                {!! $icon !!}
            </div>
            @endif
            @if($title)
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            @endif
        </div>
        @if(isset($actions))
        <div>
            {{ $actions }}
        </div>
        @endif
    </div>
    @endif
    
    <div>
        {{ $slot }}
    </div>
    
    @if($footer || isset($cardFooter))
    <div class="mt-4 pt-4 border-t border-gray-200">
        {{ $footer ?? $cardFooter }}
    </div>
    @endif
</div>