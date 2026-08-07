<x-ui.tab-menu>
    <x-ui.tab-item href="{{ route('qceskalink.index') }}" :active="request()->routeIs('qceskalink.index')" :navigate="false" class="gap-1.5">
        <x-heroicon-s-clipboard-document-check class="w-3.5 h-3.5" /> QC Eskalink
    </x-ui.tab-item>
    
    <x-ui.tab-item href="{{ route('dashboard.sales-comparison') }}" :active="request()->routeIs('dashboard.sales-comparison')" :navigate="false" class="gap-1.5">
        <x-heroicon-s-chart-bar class="w-3.5 h-3.5" /> Sales Comparison
    </x-ui.tab-item>

    <x-ui.tab-item href="{{ route('dashboard.extractor-process') }}" :active="request()->routeIs('dashboard.extractor-process')" :navigate="false" class="gap-1.5">
        <x-heroicon-s-cpu-chip class="w-3.5 h-3.5" /> Proses Ekstraksi
    </x-ui.tab-item>

    <x-ui.tab-item href="{{ route('dashboard.ocr-scanner') }}" :active="request()->routeIs('dashboard.ocr-scanner')" :navigate="false" class="gap-1.5">
        <x-heroicon-s-camera class="w-3.5 h-3.5" /> OCR Surat
    </x-ui.tab-item>

    <x-ui.tab-item href="{{ route('dashboard.extractor-config') }}" :active="request()->routeIs('dashboard.extractor-config')" :navigate="false" class="gap-1.5">
        <x-heroicon-s-cog-6-tooth class="w-3.5 h-3.5" /> Config
    </x-ui.tab-item>
</x-ui.tab-menu>
