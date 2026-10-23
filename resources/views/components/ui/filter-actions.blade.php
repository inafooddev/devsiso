@props([
    'applyAction' => 'applyFilter',
    'resetAction' => 'resetFilter',
    'canApply' => true,
    'showReset' => true,
    'applyLabel' => 'Terapkan',
    'resetLabel' => 'Reset',
    'disabledTip' => 'Lengkapi filter terlebih dahulu',
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5 shrink-0 ml-auto sm:ml-0']) }}>
    {{-- Tombol Terapkan --}}
    @if($applyAction)
        <button type="button" 
            wire:click="{{ $applyAction }}" 
            wire:loading.attr="disabled"
            @disabled(!$canApply)
            class="btn btn-sm btn-primary rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer {{ !$canApply ? 'opacity-50 cursor-not-allowed' : '' }}"
            title="{{ !$canApply ? $disabledTip : 'Terapkan filter' }}">
            <span wire:loading.remove wire:target="{{ $applyAction }}" class="flex items-center gap-1">
                <x-heroicon-m-funnel class="w-4 h-4" />
                <span>{{ $applyLabel }}</span>
            </span>
            <span wire:loading wire:target="{{ $applyAction }}" class="loading loading-spinner loading-xs"></span>
        </button>
    @endif

    {{-- Tombol Reset --}}
    @if($showReset && $resetAction)
        <button type="button" 
            wire:click="{{ $resetAction }}" 
            class="btn btn-sm btn-ghost border border-base-300 rounded-xl text-base-content/70 hover:text-base-content text-xs cursor-pointer"
            title="Reset semua filter">
            <span class="flex items-center gap-1">
                <x-heroicon-m-arrow-path class="w-3.5 h-3.5" />
                <span class="hidden sm:inline">{{ $resetLabel }}</span>
            </span>
        </button>
    @endif

    {{-- Extra Slot (Peta, Excel, Cetak, Tambah, dll.) --}}
    {{ $slot }}
</div>
