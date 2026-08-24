{{-- ========== MODAL STATUS KELENGKAPAN DOKUMEN ========== --}}
@if($isStatusModalOpen && $statusOutlet)
@php
    $outlet = $statusOutlet;

    $checks = [
        'identitas' => [
            'label' => 'Identitas Pemilik',
            'icon'  => 'heroicon-o-user',
            'items' => [
                ['label' => 'Nomor HP',           'ok' => !empty(trim($outlet->no_hp ?? ''))],
                ['label' => 'Nama Pemilik Toko',  'ok' => !empty(trim($outlet->nama_pemilik_toko ?? ''))],
                ['label' => 'Nama Sesuai KTP',    'ok' => !empty(trim($outlet->nama_ktp ?? ''))],
                ['label' => 'NIK KTP',            'ok' => !empty(trim($outlet->nik_ktp ?? ''))],
                ['label' => 'Foto KTP',           'ok' => !empty(trim($outlet->foto_ktp ?? ''))],
            ],
        ],
        'rekening' => [
            'label' => 'Rekening Bank',
            'icon'  => 'heroicon-o-banknotes',
            'items' => [
                ['label' => 'No. Rekening',           'ok' => !empty(trim($outlet->no_rekening ?? ''))],
                ['label' => 'Nama Bank',              'ok' => !empty(trim($outlet->nama_bank ?? ''))],
                ['label' => 'Nama Pemilik Rekening',  'ok' => !empty(trim($outlet->nama_pemilik_norek ?? ''))],
            ],
        ],
        'foto' => [
            'label' => 'Foto Toko',
            'icon'  => 'heroicon-o-camera',
            'items' => [
                ['label' => 'Foto Tampak Depan', 'ok' => !empty(trim($outlet->foto_toko2 ?? ''))],
                ['label' => 'Foto Tampak Dalam', 'ok' => !empty(trim($outlet->foto_toko3 ?? ''))],
            ],
        ],
        'lokasi' => [
            'label' => 'Lokasi Toko',
            'icon'  => 'heroicon-o-map-pin',
            'items' => [
                ['label' => 'Koordinat Tikor (GPS)', 'ok' => !empty(trim($outlet->latitude ?? '')) && !empty(trim($outlet->longitude ?? ''))],
            ],
        ],
    ];

    $totalItems = collect($checks)->sum(fn($g) => count($g['items']));
    $doneItems  = collect($checks)->sum(fn($g) => collect($g['items'])->filter(fn($i) => $i['ok'])->count());
    $pct        = $totalItems > 0 ? round(($doneItems / $totalItems) * 100) : 0;
    $isComplete = $doneItems === $totalItems;
@endphp

<div x-data="{ open: @entangle('isStatusModalOpen') }"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="$wire.closeStatusModal()"
         class="fixed inset-0 bg-base-100/60 backdrop-blur-sm"></div>

    {{-- Modal Panel --}}
    <div x-show="open"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-md overflow-hidden ring-1 ring-base-content/5 text-base-content">

        {{-- Header --}}
        <div class="flex items-start justify-between px-6 py-5 border-b border-base-300 bg-base-200/30">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl {{ $isComplete ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                    @if($isComplete)
                        <x-heroicon-s-clipboard-document-check class="w-6 h-6" />
                    @else
                        <x-heroicon-s-clipboard-document-list class="w-6 h-6" />
                    @endif
                </div>
                <div>
                    <h3 class="font-bold text-base text-base-content">{{ $outlet->customer_name }}</h3>
                    <p class="text-xs text-base-content/50">{{ $outlet->customer_code }} &middot; {{ $outlet->branch_name }}</p>
                </div>
            </div>
            <button @click="$wire.closeStatusModal()"
                    class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200 mt-0.5">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Progress Bar --}}
        <div class="px-6 pt-5 pb-3">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">Kelengkapan Dokumen</span>
                <span class="text-sm font-bold {{ $isComplete ? 'text-success' : ($pct >= 60 ? 'text-warning' : 'text-error') }}">
                    {{ $doneItems }} / {{ $totalItems }}
                    <span class="text-xs font-normal text-base-content/40">({{ $pct }}%)</span>
                </span>
            </div>
            <div class="w-full h-2.5 bg-base-300 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700
                    {{ $isComplete ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-error') }}"
                     style="width: {{ $pct }}%"></div>
            </div>
        </div>

        {{-- Checklist Body --}}
        <div class="overflow-y-auto max-h-[55vh] px-6 pb-4 space-y-5">
            @foreach($checks as $group)
                <div>
                    {{-- Group Header --}}
                    <div class="flex items-center gap-2 mb-2.5">
                        <x-dynamic-component :component="$group['icon']" class="w-4 h-4 text-primary/70" />
                        <span class="text-[11px] font-bold uppercase tracking-wider text-primary/70">{{ $group['label'] }}</span>
                    </div>

                    {{-- Items --}}
                    <div class="space-y-1.5">
                        @foreach($group['items'] as $item)
                            <div class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors
                                {{ $item['ok'] ? 'bg-success/5 border border-success/10' : 'bg-error/5 border border-error/10' }}">
                                @if($item['ok'])
                                    <div class="shrink-0 w-5 h-5 rounded-full bg-success/15 flex items-center justify-center">
                                        <x-heroicon-s-check class="w-3 h-3 text-success" />
                                    </div>
                                    <span class="text-sm text-base-content/80 font-medium">{{ $item['label'] }}</span>
                                    <span class="ml-auto text-[10px] font-semibold text-success/70 uppercase tracking-wide">Ada</span>
                                @else
                                    <div class="shrink-0 w-5 h-5 rounded-full bg-error/15 flex items-center justify-center">
                                        <x-heroicon-s-x-mark class="w-3 h-3 text-error" />
                                    </div>
                                    <span class="text-sm text-base-content/50">{{ $item['label'] }}</span>
                                    <span class="ml-auto text-[10px] font-semibold text-error/70 uppercase tracking-wide">Belum</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-base-300 bg-base-200/30 flex items-center justify-between gap-3">
            @canEdit('rwo.index')
                <button wire:click="openEditModal({{ $outlet->id }})" @click="$wire.closeStatusModal()"
                        class="btn btn-sm btn-primary rounded-xl gap-2">
                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                    Edit Data
                </button>
            @endcanEdit
            <button @click="$wire.closeStatusModal()"
                    class="btn btn-sm btn-ghost rounded-xl ml-auto">
                Tutup
            </button>
        </div>
    </div>
</div>
@endif
