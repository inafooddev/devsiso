<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Insentif Mingguan</x-slot>

    <x-ui.tab-menu>
        <x-ui.tab-item :active="$activeTab === 'summary'" wire:click.prevent="setTab('summary')" :navigate="false">
            <x-heroicon-o-chart-pie class="w-4 h-4 inline-block mr-1" /> Summary
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'insentif-se'" wire:click.prevent="setTab('insentif-se')" :navigate="false">
            Insentif SE
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'insentif-spv'" wire:click.prevent="setTab('insentif-spv')" :navigate="false">
            Insentif SPV
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'insentif-kacab'" wire:click.prevent="setTab('insentif-kacab')" :navigate="false">
            Insentif Kacab
        </x-ui.tab-item>
        @role('admin')
        <x-ui.tab-item :active="$activeTab === 'setting-header'" wire:click.prevent="setTab('setting-header')" :navigate="false">
            Setting Header
        </x-ui.tab-item>
        @endrole

        <x-slot name="actions">
            <button wire:click="$dispatch('openExportModal')" class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20 group hover:-translate-y-0.5 transition-all">
                <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                <span>Export Global</span>
            </button>
        </x-slot>
    </x-ui.tab-menu>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
        <div class="mt-4 flex-1 min-h-0 flex flex-col w-full h-full">
            @if ($activeTab === 'summary')
                <livewire:others.insentif.mingguan.summary />
            @elseif ($activeTab === 'insentif-se')
                <livewire:others.insentif.mingguan.insentif-se />
            @elseif ($activeTab === 'insentif-spv')
                <livewire:others.insentif.mingguan.insentif-spv />
            @elseif ($activeTab === 'insentif-kacab')
                <livewire:others.insentif.mingguan.insentif-kacab />
            @endif

            @role('admin')
            @if($activeTab === 'setting-header')
                <livewire:others.insentif.mingguan.setting-header />
            @endif
            @endrole
        </div>
    
    <livewire:others.insentif.mingguan.export-modal />
</div>
