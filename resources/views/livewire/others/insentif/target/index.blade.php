<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Target Insentif</x-slot>

    <x-ui.tab-menu>
        <x-ui.tab-item :active="$activeTab === 'target-se-value'" wire:click.prevent="setTab('target-se-value')" :navigate="false">
            Target SE Value
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'target-se-vtkp'" wire:click.prevent="setTab('target-se-vtkp')" :navigate="false">
            Target SE VTKP
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'target-spv-value'" wire:click.prevent="setTab('target-spv-value')" :navigate="false">
            Target SPV Value
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'target-spv-vtkp'" wire:click.prevent="setTab('target-spv-vtkp')" :navigate="false">
            Target SPV VTKP
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'target-kacab'" wire:click.prevent="setTab('target-kacab')" :navigate="false">
            Target Kacab
        </x-ui.tab-item>
    </x-ui.tab-menu>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
        @if($activeTab === 'target-se-value')
            <livewire:others.insentif.target.target-se-value />
        @elseif($activeTab === 'target-se-vtkp')
            <livewire:others.insentif.target.target-se-vtkp />
        @elseif($activeTab === 'target-spv-value')
            <livewire:others.insentif.target.target-spv-value />
        @elseif($activeTab === 'target-spv-vtkp')
            <livewire:others.insentif.target.target-spv-vtkp />
        @elseif($activeTab === 'target-kacab')
            <livewire:others.insentif.target.target-kacab />
        @endif
    </div>
</div>
