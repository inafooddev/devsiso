{{--
    Button Component — DaisyUI + Heroicons
    =======================================
    Usage:
        <x-ui.button>Simpan</x-ui.button>
        <x-ui.button variant="secondary" size="sm" icon="pencil">Edit</x-ui.button>
        <x-ui.button variant="error" outline>Hapus</x-ui.button>
        <x-ui.button :loading="$isLoading">Proses</x-ui.button>
        <x-ui.button href="/url" tag="a">Link Button</x-ui.button>

    Props:
        variant  : primary | secondary | accent | ghost | error | warning | success | neutral | link
        size     : xs | sm | md | lg
        icon     : heroicon name e.g. 'pencil', 'trash', 'plus' (optional)
        iconPos  : left | right
        outline  : bool — gunakan style outline
        loading  : bool — tampilkan spinner
        disabled : bool
        block    : bool — full width
        tag      : 'button' | 'a' (default: button)
--}}

@props([
    'variant'  => 'primary',
    'size'     => 'md',
    'type'     => 'button',
    'icon'     => null,
    'iconPos'  => 'left',
    'outline'  => false,
    'loading'  => false,
    'disabled' => false,
    'block'    => false,
    'tag'      => 'button',
])

@php
    $sizeClass = match($size) {
        'xs'    => 'btn-xs',
        'sm'    => 'btn-sm',
        'lg'    => 'btn-lg',
        default => '',
    };

    $variantClass = match($variant) {
        'secondary' => 'btn-secondary',
        'accent'    => 'btn-accent',
        'ghost'     => 'btn-ghost',
        'error'     => 'btn-error',
        'warning'   => 'btn-warning',
        'success'   => 'btn-success',
        'neutral'   => 'btn-neutral',
        'link'      => 'btn-link',
        default     => 'btn-primary',
    };

    $outlineClass = $outline ? 'btn-outline' : '';
    $blockClass   = $block   ? 'w-full'      : '';
    $classes = trim("btn {$variantClass} {$sizeClass} {$outlineClass} {$blockClass}");

    $iconSize = match($size) {
        'xs', 'sm' => 'w-3.5 h-3.5',
        'lg'       => 'w-5 h-5',
        default    => 'w-4 h-4',
    };

    $spinnerSize = match($size) {
        'xs', 'sm' => 'loading-xs',
        'lg'       => 'loading-md',
        default    => 'loading-sm',
    };
@endphp

@if($tag === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <span class="loading loading-spinner {{ $spinnerSize }}"></span>
        @elseif($icon && $iconPos === 'left')
            <x-dynamic-component :component="'heroicon-s-' . $icon" :class="$iconSize" />
        @endif

        {{ $slot }}

        @if(!$loading && $icon && $iconPos === 'right')
            <x-dynamic-component :component="'heroicon-s-' . $icon" :class="$iconSize" />
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @if($disabled || $loading) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <span class="loading loading-spinner {{ $spinnerSize }}"></span>
        @elseif($icon && $iconPos === 'left')
            <x-dynamic-component :component="'heroicon-s-' . $icon" :class="$iconSize" />
        @endif

        {{ $slot }}

        @if(!$loading && $icon && $iconPos === 'right')
            <x-dynamic-component :component="'heroicon-s-' . $icon" :class="$iconSize" />
        @endif
    </button>
@endif
