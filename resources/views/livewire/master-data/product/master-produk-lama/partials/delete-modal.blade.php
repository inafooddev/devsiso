{{-- ========== MODAL KONFIRMASI HAPUS ========== --}}
<div x-data="{ open: @entangle('isDeleteModalOpen') }"
        x-show="open" x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4">
    <div x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>
    <div x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-sm ring-1 ring-base-content/5 text-base-content">
        <div class="p-8 text-center">
            <div class="w-20 h-20 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                <x-heroicon-s-trash class="w-10 h-10" />
            </div>
            <h3 class="text-xl font-bold mb-2 leading-none">Hapus Produk?</h3>
            <p class="text-[13px] text-base-content/50 leading-relaxed px-4">Data produk lama ini akan dihapus secara <span class="text-error font-bold italic">permanen</span>.</p>
        </div>
        <div class="flex items-center justify-center gap-3 px-6 pb-8">
            <button type="button" @click="open = false" class="btn btn-ghost flex-1 rounded-xl normal-case transition-all duration-200">Batal</button>
            <button wire:click="delete" class="btn btn-error flex-1 rounded-xl normal-case shadow-sm shadow-error/20 text-white transition-all duration-200">
                <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
            </button>
        </div>
    </div>
</div>
