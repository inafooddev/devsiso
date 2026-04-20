{{--
    Modal Component — DaisyUI + Alpine.js
    =======================================
    Usage:
        [Trigger]
        <button onclick="document.getElementById('modal-hapus').showModal()">Hapus</button>

        [Modal]
        <x-ui.modal id="modal-hapus" title="Konfirmasi" icon="trash">
            <p>Yakin hapus?</p>
            <x-slot:footer>
                <button onclick="...">Batal</button>
            </x-slot:footer>
        </x-ui.modal>

    Props:
        id          : string  — unik per halaman
        title       : string
        icon        : heroicon name (optional)
        size        : sm | md | lg | xl | full
        dismissible : bool — klik backdrop menutup modal (default true)
--}}

@props([
    'id'          => 'modal',
    'title'       => '',
    'icon'        => null,
    'size'        => 'md',
    'dismissible' => true,
])

@php
    $sizeClass = match($size) {
        'sm'   => 'max-w-sm',
        'lg'   => 'max-w-2xl',
        'xl'   => 'max-w-4xl',
        'full' => 'w-[95vw] max-w-[95vw]',
        default => 'max-w-lg',
    };
@endphp

<dialog id="{{ $id }}" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-base-100 border border-base-300 {{ $sizeClass }} p-0">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
            <div class="flex items-center gap-3">
                @if($icon)
                    <div class="flex-shrink-0 p-2 rounded-xl bg-primary/10">
                        <x-dynamic-component :component="'heroicon-s-' . $icon" class="w-5 h-5 text-primary" />
                    </div>
                @endif
                <h3 class="font-bold text-lg text-base-content">{{ $title }}</h3>
            </div>
            <button
                onclick="document.getElementById('{{ $id }}').close()"
                class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:text-base-content"
            >
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 text-base-content">
            {{ $slot }}
        </div>

        {{-- Footer (optional slot) --}}
        @if(isset($footer))
            <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-base-300 bg-base-200/50">
                {{ $footer }}
            </div>
        @endif

    </div>

    {{-- Backdrop --}}
    @if($dismissible)
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    @endif
</dialog>
