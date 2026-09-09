<div class="dropdown dropdown-end">
    <div tabindex="0" role="button" class="btn btn-ghost btn-circle btn-sm relative" title="Global Notifications">
        <x-heroicon-o-bell class="w-5 h-5 transition-colors {{ $totalNotifications > 0 ? 'text-error animate-pulse' : 'text-base-content/70' }}" />
        @if($totalNotifications > 0)
            <span class="absolute top-0 right-0 flex h-4 min-w-[1rem] px-1 items-center justify-center rounded-full bg-error text-[10px] font-bold text-error-content shadow-sm border border-base-100">
                {{ $totalNotifications > 99 ? '99+' : $totalNotifications }}
            </span>
        @endif
    </div>
    
    <div tabindex="0" class="dropdown-content menu bg-base-100 rounded-box shadow-xl border border-base-300 w-80 p-2 mt-4 z-[9999]">
        <div class="px-4 py-3 border-b border-base-200">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <x-heroicon-s-bell-alert class="w-4 h-4 text-primary" />
                Notifications Center
            </h3>
            <p class="text-xs text-base-content/50 mt-1">
                @if($totalNotifications > 0)
                    You have {{ $totalNotifications }} items requiring attention
                @else
                    All caught up! No new notifications.
                @endif
            </p>
        </div>
        
        <div class="max-h-80 overflow-y-auto">
            {{-- 1. JKS Approvals --}}
            @if(auth()->user()->hasAnyRole(['admin', 'edp']) && $jksCount > 0)
            <a href="{{ route('call-plan.jks-approvals') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Perubahan JKS SE</p>
                    <p class="text-xs text-base-content/60">{{ $jksCount }} antrean persetujuan</p>
                </div>
                <div class="badge badge-primary badge-sm font-bold">{{ $jksCount }}</div>
            </a>
            @endif

            {{-- 2. Perbaikan Tikor --}}
            @if($tikorCount > 0)
            <a href="{{ route('others.perbaikantikor') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-map-pin class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Perbaikan Tikor</p>
                    <p class="text-xs text-base-content/60">{{ $tikorCount }} pengajuan pending</p>
                </div>
                <div class="badge badge-error badge-sm font-bold">{{ $tikorCount }}</div>
            </a>
            @endif

            {{-- 3. Mapping: Products --}}
            @if($productCount > 0)
            <a href="{{ route('mapping.unmapped-products') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-shopping-bag class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Unmapped Products</p>
                    <p class="text-xs text-base-content/60">{{ $productCount }} items found</p>
                </div>
                <div class="badge badge-info badge-sm font-bold">{{ $productCount }}</div>
            </a>
            @endif

            {{-- 4. Mapping: Salesman --}}
            @if($salesmanCount > 0)
            <a href="{{ route('mapping.unmapped-salesmans') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-user-group class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Unmapped Salesman</p>
                    <p class="text-xs text-base-content/60">{{ $salesmanCount }} items found</p>
                </div>
                <div class="badge badge-success badge-sm font-bold">{{ $salesmanCount }}</div>
            </a>
            @endif

            {{-- 5. Mapping: Unit --}}
            @if($unitCount > 0)
            <a href="{{ route('product-unit-mappings.index') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-square-3-stack-3d class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Unit Unmapping</p>
                    <p class="text-xs text-base-content/60">{{ $unitCount }} items found</p>
                </div>
                <div class="badge badge-warning badge-sm font-bold">{{ $unitCount }}</div>
            </a>
            @endif
        </div>

        @if($totalNotifications === 0)
            <div class="p-8 text-center">
                <div class="bg-base-200 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-success" />
                </div>
                <p class="text-sm font-medium">All systems clear!</p>
                <p class="text-xs text-base-content/50 mt-1">Excellent work.</p>
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
