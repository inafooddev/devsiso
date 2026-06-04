<div class="dropdown dropdown-end">
    <div tabindex="0" role="button" class="btn btn-ghost btn-circle btn-sm relative" title="Mapping Notifications">
        <div class="indicator">
            <x-heroicon-o-bell class="w-5 h-5 text-base-content/70" />
            @if($totalCount > 0)
                <span class="badge badge-xs badge-error indicator-item"></span>
            @endif
        </div>
    </div>
    <div tabindex="0" class="dropdown-content menu bg-base-100 rounded-box shadow-xl border border-base-300 w-80 p-2 mt-4 z-[9999]">
        <div class="px-4 py-3 border-b border-base-200">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <x-heroicon-s-bell-alert class="w-4 h-4 text-primary" />
                Mapping Notifications
            </h3>
            <p class="text-xs text-base-content/50 mt-1">Items requiring your attention</p>
        </div>
        
        <div class="max-h-64 overflow-y-auto">
            {{-- Product Unmapping --}}
            <a href="{{ route('mapping.unmapped-products') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-shopping-bag class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Unmapped Products</p>
                    <p class="text-xs text-base-content/60">{{ $productCount }} items found</p>
                </div>
                @if($productCount > 0)
                    <div class="badge badge-primary badge-sm font-bold">{{ $productCount }}</div>
                @endif
            </a>

            {{-- Salesman Unmapping --}}
            <a href="{{ route('mapping.unmapped-salesmans') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-user-group class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Unmapped Salesman</p>
                    <p class="text-xs text-base-content/60">{{ $salesmanCount }} items found</p>
                </div>
                @if($salesmanCount > 0)
                    <div class="badge badge-accent badge-sm font-bold">{{ $salesmanCount }}</div>
                @endif
            </a>

            {{-- Unit Unmapping --}}
            <a href="{{ route('product-unit-mappings.index') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-square-3-stack-3d class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Unit Unmapping</p>
                    <p class="text-xs text-base-content/60">{{ $unitCount }} items found</p>
                </div>
                @if($unitCount > 0)
                    <div class="badge badge-warning badge-sm font-bold">{{ $unitCount }}</div>
                @endif
            </a>
        </div>

        @if($totalCount === 0)
            <div class="p-8 text-center">
                <div class="bg-base-200 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-success" />
                </div>
                <p class="text-sm font-medium">All mappings clear!</p>
                <p class="text-xs text-base-content/50 mt-1">Good job keeping data clean.</p>
            </div>
        @endif

        <div class="p-2 border-t border-base-200 mt-1 text-center">
             <button wire:click="updateCounts" class="btn btn-ghost btn-xs text-primary gap-2">
                 <x-heroicon-s-arrow-path class="w-3 h-3" wire:loading.class="animate-spin" />
                 Refresh Counts
             </button>
        </div>
    </div>
</div>
