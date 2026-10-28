@php
    $currentRoute = Route::currentRouteName();
    // Fallback jika XHR Livewire request
    if (in_array($currentRoute, ['livewire.update', 'livewire.message']) || empty($currentRoute)) {
        $referer = request()->header('referer', '');
        if (str_contains($referer, '/report/fundamental-sales/ranking')) {
            $currentRoute = 'report.fundamental-sales.ranking';
        } elseif (str_contains($referer, '/report/fundamental-sales-hierarchy')) {
            $currentRoute = 'report.fundamental-sales-hierarchy.index';
        } elseif (str_contains($referer, '/report/fundamental-sales')) {
            $currentRoute = 'report.fundamental-sales.index';
        }
    }
@endphp

<x-ui.tab-menu class="mb-3">
    <x-ui.tab-item :active="$currentRoute === 'report.fundamental-sales.index'" :href="route('report.fundamental-sales.index')" :navigate="true">
        <x-heroicon-o-table-cells class="w-4 h-4 mr-1.5 inline-block" />
        Matriks Tahunan
    </x-ui.tab-item>

    <x-ui.tab-item :active="$currentRoute === 'report.fundamental-sales.ranking'" :href="route('report.fundamental-sales.ranking')" :navigate="true">
        <x-heroicon-o-trophy class="w-4 h-4 mr-1.5 inline-block" />
        Ranking SPV (Bulanan)
    </x-ui.tab-item>

    @if(Route::has('report.fundamental-sales-hierarchy.index'))
    <x-ui.tab-item :active="$currentRoute === 'report.fundamental-sales-hierarchy.index'" :href="route('report.fundamental-sales-hierarchy.index')" :navigate="true">
        <x-heroicon-o-bars-3-bottom-left class="w-4 h-4 mr-1.5 inline-block" />
        Hirarki Nasional
    </x-ui.tab-item>
    @endif
</x-ui.tab-menu>
