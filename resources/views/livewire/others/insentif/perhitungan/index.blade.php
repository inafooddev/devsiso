<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Perhitungan Insentif</x-slot>

    <x-ui.tab-menu>
        <x-ui.tab-item :active="$activeTab === 'insentif-se'" wire:click.prevent="setTab('insentif-se')" :navigate="false">
            Insentif SE
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'insentif-spv'" wire:click.prevent="setTab('insentif-spv')" :navigate="false">
            Insentif SPV
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'insentif-kacab'" wire:click.prevent="setTab('insentif-kacab')" :navigate="false">
            Insentif Kacab
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'jobs'" wire:click.prevent="setTab('jobs')" :navigate="false">
            Processing Jobs
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'setting-header'" wire:click.prevent="setTab('setting-header')" :navigate="false">
            Setting Header
        </x-ui.tab-item>
    </x-ui.tab-menu>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
        @if($activeTab === 'insentif-se')
            <livewire:others.insentif.perhitungan.insentif-se />
        @elseif($activeTab === 'insentif-spv')
            <livewire:others.insentif.perhitungan.insentif-spv />
        @elseif($activeTab === 'insentif-kacab')
            <livewire:others.insentif.perhitungan.insentif-kacab />
        @elseif($activeTab === 'jobs')
            <livewire:others.insentif.perhitungan.jobs />
        @elseif($activeTab === 'setting-header')
            <livewire:others.insentif.perhitungan.setting-header />
        @endif
    </div>
</div>
