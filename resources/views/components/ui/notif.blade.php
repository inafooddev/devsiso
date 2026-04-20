{{--
    Notif (Alert) Component — DaisyUI + Alpine.js
    ===============================================
    Usage:

        Static:
            <x-ui.notif type="success">Data berhasil disimpan.</x-ui.notif>
            <x-ui.notif type="error" title="Gagal">Koneksi terputus.</x-ui.notif>
            <x-ui.notif type="warning" :dismissible="true">Sesi akan berakhir.</x-ui.notif>

        Dari session flash (letakkan di layout):
            <x-ui.notif-flash />

        Toast (di sudut layar):
            <x-ui.notif type="success" toast>Disimpan!</x-ui.notif>

    Props:
        type        : info | success | warning | error
        title       : string (optional, default sesuai type)
        dismissible : bool — bisa ditutup (default false)
        toast       : bool — posisi fixed di pojok kanan atas
        icon        : override heroicon name
--}}

@props([
    'type'        => 'info',
    'title'       => null,
    'dismissible' => false,
    'toast'       => false,
    'icon'        => null,
])

@php
    $config = match($type) {
        'success' => [
            'alertClass' => 'alert-success',
            'icon'       => 'check-circle',
            'title'      => $title ?? 'Berhasil',
        ],
        'warning' => [
            'alertClass' => 'alert-warning',
            'icon'       => 'exclamation-triangle',
            'title'      => $title ?? 'Perhatian',
        ],
        'error' => [
            'alertClass' => 'alert-error',
            'icon'       => 'x-circle',
            'title'      => $title ?? 'Error',
        ],
        default => [
            'alertClass' => 'alert-info',
            'icon'       => 'information-circle',
            'title'      => $title ?? 'Informasi',
        ],
    };

    $iconName = $icon ?? $config['icon'];
    $toastClass = $toast ? 'toast toast-end toast-top z-50' : '';
@endphp

@if($dismissible)
<div x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="{{ $toastClass }}">
@endif

<div {{ $attributes->merge(['class' => "alert {$config['alertClass']} rounded-xl border-0 shadow-sm"]) }}>
    {{-- Icon --}}
    <x-dynamic-component :component="'heroicon-s-' . $iconName" class="w-5 h-5 flex-shrink-0" />

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        @if($config['title'])
            <p class="font-semibold text-sm leading-snug">{{ $config['title'] }}</p>
        @endif
        <p class="text-sm opacity-90 leading-snug">{{ $slot }}</p>
    </div>

    {{-- Dismiss Button --}}
    @if($dismissible)
        <button
            @click="show = false"
            class="btn btn-sm btn-circle btn-ghost opacity-60 hover:opacity-100 flex-shrink-0"
            aria-label="Tutup"
        >
            <x-heroicon-s-x-mark class="w-4 h-4" />
        </button>
    @endif
</div>

@if($dismissible)
</div>
@endif
