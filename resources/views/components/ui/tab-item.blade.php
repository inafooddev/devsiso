@props([
    'active' => false,
    'href' => '#',
    'navigate' => true
])

<a href="{{ $href }}" 
   @if($navigate) wire:navigate @endif
   {{ $attributes->class([
       'tab tab-xs px-4 transition-colors',
       'tab-active font-bold shadow-sm bg-base-100' => $active,
       'text-base-content/70 hover:text-base-content' => !$active,
   ]) }}>
    {{ $slot }}
</a>
