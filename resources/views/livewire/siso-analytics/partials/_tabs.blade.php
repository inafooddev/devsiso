@php
    $currentRoute = Route::currentRouteName();
    if (in_array($currentRoute, ['livewire.update', 'livewire.message']) || empty($currentRoute)) {
        $referer = request()->header('referer', '');
        if (str_contains($referer, '/siso-analytics/rekon-stok')) {
            $currentRoute = 'siso-analytics.rekon-stok';
        } else {
            $currentRoute = 'siso-analytics.index';
        }
    }
@endphp

<x-ui.tab-menu>
    <x-ui.tab-item :href="route('siso-analytics.index')" :active="$currentRoute === 'siso-analytics.index'" :navigate="false" class="inline-flex items-center gap-1.5 whitespace-nowrap">
        <x-heroicon-o-chart-bar class="w-3.5 h-3.5 shrink-0" />
        <span>Sell In vs Out</span>
    </x-ui.tab-item>

    <x-ui.tab-item :href="route('siso-analytics.rekon-stok')" :active="$currentRoute === 'siso-analytics.rekon-stok'" :navigate="false" class="inline-flex items-center gap-1.5 whitespace-nowrap">
        <x-heroicon-o-clipboard-document-check class="w-3.5 h-3.5 shrink-0" />
        <span>Stok</span>
    </x-ui.tab-item>

    <span class="tab tab-xs px-4 inline-flex items-center gap-1.5 whitespace-nowrap text-base-content/40 cursor-not-allowed select-none" title="Segera hadir (Tahap 3)">
        <x-heroicon-o-bolt class="w-3.5 h-3.5 shrink-0" />
        <span>Fast / Slow</span>
    </span>

    <span class="tab tab-xs px-4 inline-flex items-center gap-1.5 whitespace-nowrap text-base-content/40 cursor-not-allowed select-none" title="Segera hadir (Tahap 4)">
        <x-heroicon-o-shopping-cart class="w-3.5 h-3.5 shrink-0" />
        <span>Rekomendasi PO</span>
    </span>

    <span class="tab tab-xs px-4 inline-flex items-center gap-1.5 whitespace-nowrap text-base-content/40 cursor-not-allowed select-none" title="Segera hadir (Tahap 5)">
        <x-heroicon-o-arrow-up-tray class="w-3.5 h-3.5 shrink-0" />
        <span>Stok Opname</span>
    </span>
</x-ui.tab-menu>
