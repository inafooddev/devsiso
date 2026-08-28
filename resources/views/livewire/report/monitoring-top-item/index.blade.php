<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Monitoring Top Item</x-slot>

    <!-- Tab Menu -->
    <x-ui.tab-menu>
        <x-ui.tab-item :active="$activeTab === 'summary'" wire:click.prevent="$set('activeTab', 'summary')" :navigate="false">
            Summary
        </x-ui.tab-item>
        <x-ui.tab-item :active="$activeTab === 'monitoring'" wire:click.prevent="$set('activeTab', 'monitoring')" :navigate="false">
            Monitoring
        </x-ui.tab-item>
        @if(auth()->user()->hasRole('admin'))
        <x-ui.tab-item :active="$activeTab === 'jobs'" wire:click.prevent="$set('activeTab', 'jobs')" :navigate="false">
            Jobs
        </x-ui.tab-item>
        @endif
    </x-ui.tab-menu>

    <!-- Tab Content -->
    <div class="flex-1 p-4 overflow-y-auto">
        <!-- Content will be placed here -->
        @if($activeTab === 'summary')
            @livewire('report.monitoring-top-item.summary')
        @elseif($activeTab === 'monitoring')
            @livewire('report.monitoring-top-item.monitoring')
        @elseif($activeTab === 'jobs' && auth()->user()->hasRole('admin'))
            @livewire('report.monitoring-top-item.jobs')
        @endif
    </div>
</div>
