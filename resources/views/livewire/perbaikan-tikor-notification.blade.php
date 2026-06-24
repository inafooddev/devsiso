<div class="dropdown dropdown-end">
    <div tabindex="0" role="button" class="btn btn-ghost btn-circle btn-sm relative" title="Perbaikan Tikor Notifications">
        <div class="indicator">
            <x-heroicon-o-map-pin class="w-5 h-5 text-base-content/70" />
            @if($pendingCount > 0)
                <span class="badge badge-xs badge-error indicator-item"></span>
            @endif
        </div>
    </div>
    <div tabindex="0" class="dropdown-content menu bg-base-100 rounded-box shadow-xl border border-base-300 w-80 p-2 mt-4 z-[9999]">
        <div class="px-4 py-3 border-b border-base-200">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <x-heroicon-s-map-pin class="w-4 h-4 text-primary" />
                Perbaikan Titik Koordinat
            </h3>
            <p class="text-xs text-base-content/50 mt-1">Pengajuan yang perlu ditinjau</p>
        </div>
        
        <div class="max-h-64 overflow-y-auto">
            <a href="{{ route('others.perbaikantikor') }}" class="flex items-center gap-3 p-3 hover:bg-base-200 transition-colors rounded-lg group">
                <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-map class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Toko Pending</p>
                    <p class="text-xs text-base-content/60">{{ $pendingCount }} pengajuan pending</p>
                </div>
                @if($pendingCount > 0)
                    <div class="badge badge-error badge-sm font-bold">{{ $pendingCount }}</div>
                @endif
            </a>
        </div>

        @if($pendingCount === 0)
            <div class="p-8 text-center">
                <div class="bg-base-200 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-success" />
                </div>
                <p class="text-sm font-medium">Semua pengajuan telah diproses!</p>
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
