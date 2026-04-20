{{--
    Card Component — DaisyUI
    =========================
    Usage:

        Basic:
            <x-card title="Judul Card">
                Isi konten di sini.
            </x-card>

        Dengan Icon + Actions slot:
            <x-card title="Revenue" icon="chart-bar" subtitle="Bulan ini">
                <x-slot:actions>
                    <x-ui.button size="xs" variant="ghost" icon="ellipsis-horizontal"></x-ui.button>
                </x-slot:actions>
                <p class="text-3xl font-bold text-primary">Rp 12.500.000</p>
            </x-card>

        Dengan Footer:
            <x-card title="Form">
                <input type="text" class="input input-bordered w-full" />
                <x-slot:footer>
                    <x-ui.button>Simpan</x-ui.button>
                </x-slot:footer>
            </x-card>

    Props:
        title    : string
        subtitle : string
        icon     : heroicon name (string, misal 'chart-bar')
        compact  : bool — padding lebih kecil
        bordered : bool — tampilkan border (default true)
        flush    : bool — hapus padding (misal untuk tabel di dalam card)
--}}

@props([
    'title'    => null,
    'subtitle' => null,
    'icon'     => null,
    'compact'  => false,
    'bordered' => true,
    'flush'    => false,
])

<div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-sm ' . ($bordered ? 'border border-base-300' : '')]) }}>
    <div class="{{ $flush ? '' : ($compact ? 'card-body p-4' : 'card-body p-6') }}">

        {{-- Card Header --}}
        @if($title || $icon || isset($actions))
            <div class="flex items-start justify-between gap-4 {{ $flush ? 'px-6 pt-5 pb-4' : 'mb-4' }}">
                <div class="flex items-center gap-3 min-w-0">
                    @if($icon)
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-primary/10">
                            <x-dynamic-component :component="'heroicon-s-' . $icon" class="w-5 h-5 text-primary" />
                        </div>
                    @endif
                    <div class="min-w-0">
                        @if($title)
                            <h2 class="card-title text-base-content font-semibold text-base truncate">{{ $title }}</h2>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-base-content/50 mt-0.5">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @if(isset($actions))
                    <div class="flex items-center gap-1 flex-shrink-0">
                        {{ $actions }}
                    </div>
                @endif
            </div>
            @if(!$flush)
                <div class="divider mt-0 mb-4"></div>
            @endif
        @endif

        {{-- Card Content --}}
        <div class="{{ $flush ? 'px-6' : '' }}">
            {{ $slot }}
        </div>

        {{-- Card Footer --}}
        @if(isset($footer))
            <div class="{{ $flush ? 'px-6 pb-5' : '' }} {{ ($title || $icon) ? 'mt-5 pt-4 border-t border-base-300' : 'mt-4 pt-4 border-t border-base-300' }} flex items-center justify-end gap-2">
                {{ $footer }}
            </div>
        @endif

    </div>
</div>