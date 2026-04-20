{{--
    Table Component — DaisyUI
    ==========================
    Usage:
        <x-ui.table loading="false" empty="Tidak ada data">
            <x-slot:head>
                <tr><th>No</th><th>Nama</th></tr>
            </x-slot:head>
            
            [looping data di sini, hindari forelse/empty jika ada built-in empty state]
            <tr>
                <td>1</td>
                <td>Item</td>
            </tr>
        </x-ui.table>

    Props:
        striped  : bool — row zebra
        hover    : bool — row highlight on hover (default true)
        compact  : bool — padding lebih kecil (table-xs)
        sticky   : bool — sticky header
        loading  : bool — tampilkan skeleton loader
        empty    : string — pesan ketika slot kosong
        emptyIcon: heroicon name untuk empty state
--}}

@props([
    'striped'   => false,
    'hover'     => true,
    'compact'   => false,
    'sticky'    => false,
    'loading'   => false,
    'empty'     => 'Tidak ada data ditemukan.',
    'emptyIcon' => 'inbox',
])

@php
    $tableClass = collect([
        'table',
        $striped ? 'table-zebra' : '',
        $compact ? 'table-xs'    : '',
        $sticky  ? 'table-pin-rows' : '',
    ])->filter()->join(' ');
@endphp

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl border border-base-300 bg-base-100 shadow-md shadow-base-300/20']) }}>
    <table class="{{ $tableClass }}">

        {{-- Head --}}
        @if(isset($head))
            <thead class="text-[11px] font-semibold uppercase tracking-wider bg-base-300 text-base-content/70 [&_th]:bg-base-300 border-b border-base-300">
                {{ $head }}
            </thead>
        @endif

        {{-- Body --}}
        <tbody class="{{ $hover ? '[&_tr:hover]:bg-base-200 [&_tr]:transition-colors [&_tr]:duration-200' : '' }} text-base-content text-sm divide-y divide-base-300/50">
            @if($loading)
                {{-- Skeleton Loader --}}
                @for($i = 0; $i < 5; $i++)
                    <tr>
                        @for($j = 0; $j < 4; $j++)
                            <td><div class="skeleton h-4 w-full rounded"></div></td>
                        @endfor
                    </tr>
                @endfor
            @elseif($slot->isEmpty())
                {{-- Empty State --}}
                <tr>
                    <td colspan="99">
                        <div class="flex flex-col items-center justify-center py-12 gap-3 text-base-content/40">
                            <x-dynamic-component :component="'heroicon-o-' . $emptyIcon" class="w-10 h-10" />
                            <p class="text-sm">{{ $empty }}</p>
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>

        {{-- Foot (optional) --}}
        @if(isset($foot))
            <tfoot class="text-xs bg-base-300 text-base-content [&_th]:bg-base-300">
                {{ $foot }}
            </tfoot>
        @endif

    </table>
</div>
