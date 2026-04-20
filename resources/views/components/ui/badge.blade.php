{{--
    Badge Component — DaisyUI
    ==========================
    Usage:
        <x-ui.badge>Default</x-ui.badge>
        <x-ui.badge variant="success">Aktif</x-ui.badge>
        <x-ui.badge variant="error" outline>Nonaktif</x-ui.badge>
        <x-ui.badge variant="warning" size="lg">Pending</x-ui.badge>

    Props:
        variant : primary | secondary | accent | ghost | info | success | warning | error | neutral
        size    : xs | sm | md | lg
        outline : bool
--}}

@props([
    'variant' => 'neutral',
    'size'    => 'sm',
    'outline' => false,
])

@php
    $variantClass = match($variant) {
        'primary'   => 'badge-primary',
        'secondary' => 'badge-secondary',
        'accent'    => 'badge-accent',
        'ghost'     => 'badge-ghost',
        'info'      => 'badge-info',
        'success'   => 'badge-success',
        'warning'   => 'badge-warning',
        'error'     => 'badge-error',
        default     => 'badge-neutral',
    };

    $sizeClass = match($size) {
        'xs'  => 'badge-xs',
        'md'  => 'badge-md',
        'lg'  => 'badge-lg',
        default => 'badge-sm',
    };

    $outlineClass = $outline ? 'badge-outline' : '';
@endphp

<span {{ $attributes->merge(['class' => "badge {$variantClass} {$sizeClass} {$outlineClass} gap-1 font-medium"]) }}>
    {{ $slot }}
</span>
