@props([
    'type' => 'default', // add, save, export, import, filter, edit, delete
    'label' => null,     // Opsional, akan diisi default jika null
    'icon' => null,      // Opsional, akan override icon default jika diisi
])

@php
    // Default konfigurasi berdasarkan tipe
    $defaults = match($type) {
        'add' => [
            'class' => 'btn-primary text-primary-content shadow-sm shadow-primary/20',
            'icon'  => 'plus',
            'label' => 'Tambah Data',
        ],
        'save' => [
            'class' => 'btn-primary text-primary-content shadow-sm shadow-primary/20',
            'icon'  => 'check',
            'label' => 'Simpan',
        ],
        'export' => [
            'class' => 'btn-success text-white shadow-sm shadow-success/20',
            'icon'  => 'arrow-down-tray',
            'label' => 'Export',
        ],
        'import' => [
            'class' => 'btn-outline border-base-300 text-base-content/80 hover:bg-base-200 hover:border-base-300 hover:text-base-content',
            'icon'  => 'arrow-up-tray',
            'label' => 'Import',
        ],
        'filter' => [
            'class' => 'btn-outline border-base-300 text-base-content/70 hover:bg-base-200 hover:border-base-300 hover:text-base-content',
            'icon'  => 'funnel',
            'label' => 'Filter',
        ],
        'edit' => [
            'class' => 'btn-ghost text-warning hover:bg-warning/10',
            'icon'  => 'pencil-square',
            'label' => '', // Biasanya icon saja kalau di dalam baris
        ],
        'delete' => [
            'class' => 'btn-ghost text-error hover:bg-error/10',
            'icon'  => 'trash',
            'label' => '', // Biasanya icon saja kalau di dalam baris
        ],
        'view' => [
            'class' => 'btn-ghost text-info hover:bg-info/10',
            'icon'  => 'eye',
            'label' => '', // Biasanya icon saja kalau di dalam baris
        ],
        default => [
            'class' => 'btn-neutral',
            'icon'  => null,
            'label' => 'Button',
        ],
    };

    $finalLabel = $label ?? $defaults['label'];
    $finalIcon = $icon ?? $defaults['icon'];
    
    // Base classes baku untuk semua tombol: ukuran small (btn-sm), sudut rounded-xl, text no-wrap
    $baseClasses = 'btn btn-sm rounded-xl gap-1 md:gap-2 whitespace-nowrap';
    $finalClasses = trim("{$baseClasses} {$defaults['class']}");
@endphp

@php
    $tag = $attributes->has('href') ? 'a' : 'button';
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => $finalClasses]) }}>
    @if($finalIcon)
        <x-dynamic-component :component="'heroicon-s-' . $finalIcon" class="w-4 h-4" />
    @endif
    @if($finalLabel)
        <span>{{ $finalLabel }}</span>
    @endif
    {{ $slot }}
</{{ $tag }}>
