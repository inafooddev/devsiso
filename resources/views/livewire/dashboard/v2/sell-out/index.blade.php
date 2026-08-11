<div class="flex flex-col flex-1 w-full h-full min-h-0">
    <!-- Global Tab Navigation -->
    <x-ui.tab-menu>
        <x-ui.tab-item href="{{ route('dashboard.v2.sellin') }}" :active="false" icon="o-chart-bar">
            Sell In
        </x-ui.tab-item>
        <x-ui.tab-item href="{{ route('dashboard.v2.sellout') }}" :active="true" icon="o-shopping-cart">
            Sell Out
        </x-ui.tab-item>
    </x-ui.tab-menu>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto pt-6 space-y-6 pb-10 pr-2">
        <div class="flex items-center justify-center min-h-[50vh]">
            <div class="text-center space-y-4">
                <x-icon name="o-rocket-launch" class="w-16 h-16 text-primary mx-auto animate-bounce" />
                <h2 class="text-2xl font-bold">Sell Out Dashboard</h2>
                <p class="text-base-content/60">Module ini sedang dalam tahap pengembangan (Coming Soon).</p>
            </div>
        </div>
    </div>
</div>
