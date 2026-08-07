<x-ui.tab-menu>
    <x-ui.tab-item href="{{ route('qceskalink.index') }}" :active="request()->routeIs('qceskalink.index')" class="gap-1.5">
        <x-heroicon-s-clipboard-document-check class="w-3.5 h-3.5" /> QC Eskalink
    </x-ui.tab-item>
    <x-ui.tab-item href="{{ route('dashboard.sales-comparison') }}" :active="request()->routeIs('dashboard.sales-comparison')" class="gap-1.5">
        <x-heroicon-s-chart-bar class="w-3.5 h-3.5" /> Sales Comparison
    </x-ui.tab-item>
    <x-ui.tab-item href="{{ route('dashboard.extractor-config') }}" :active="request()->routeIs('dashboard.extractor-config')" class="gap-1.5">
        <x-heroicon-s-cog-6-tooth class="w-3.5 h-3.5" /> Extractor Config
    </x-ui.tab-item>
    <x-ui.tab-item href="{{ route('dashboard.extractor-process') }}" :active="request()->routeIs('dashboard.extractor-process')" class="gap-1.5">
        <x-heroicon-s-cpu-chip class="w-3.5 h-3.5" /> Proses Ekstraksi
    </x-ui.tab-item>
</x-ui.tab-menu>
