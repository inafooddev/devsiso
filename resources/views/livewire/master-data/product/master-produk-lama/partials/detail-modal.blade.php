{{-- ========== MODAL DETAIL PRODUK ========== --}}
<div x-data="{ open: @entangle('isDetailModalOpen') }"
        x-show="open" x-cloak
        class="fixed inset-0 z-[60] flex items-start justify-center p-4 pt-10 overflow-y-auto">

    {{-- Backdrop --}}
    <div x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-base-100/70 backdrop-blur-sm" @click="open = false"></div>

    {{-- Modal Panel --}}
    <div x-show="open"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-4xl ring-1 ring-base-content/5 text-base-content my-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-info/10 text-info">
                    <x-heroicon-s-information-circle class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-none">Detail Produk Lama</h3>
                    <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Informasi lengkap data produk</p>
                </div>
            </div>
            <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300 transition-all duration-200">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto bg-base-100 custom-scrollbar">
            @if($productDetail)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    {{-- Identitas --}}
                    <div class="space-y-4 lg:col-span-3 bg-base-200/30 p-4 rounded-2xl border border-base-300">
                        <h4 class="text-[11px] font-bold uppercase tracking-widest text-base-content/40 border-b border-base-300 pb-2">Identitas Produk</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Kode Produk</p>
                                <p class="font-mono text-sm font-semibold">{{ $productDetail->pcode_prc }}</p>
                            </div>
                            <div class="lg:col-span-2">
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Nama Produk</p>
                                <p class="text-sm font-bold">{{ $productDetail->nama_produk }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Status</p>
                                <div>
                                    @if ($productDetail->status_product == '1')
                                        <span class="badge badge-sm badge-success/20 text-success border-success/30 font-semibold mt-1">Aktif</span>
                                    @else
                                        <span class="badge badge-sm badge-error/20 text-error border-error/30 font-semibold mt-1">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Klasifikasi --}}
                    <div class="space-y-4 lg:col-span-3 bg-base-200/30 p-4 rounded-2xl border border-base-300">
                        <h4 class="text-[11px] font-bold uppercase tracking-widest text-base-content/40 border-b border-base-300 pb-2">Klasifikasi</h4>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Kategori</p>
                                <p class="text-sm font-medium">{{ $productDetail->kategory ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Brand</p>
                                <p class="text-sm font-medium">{{ $productDetail->brand ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Sub Brand</p>
                                <p class="text-sm font-medium">{{ $productDetail->subbrand ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Line Produk</p>
                                <p class="text-sm font-medium">{{ $productDetail->produk_line ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Divisi</p>
                                <p class="text-sm font-medium">{{ $productDetail->divisi ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Promo Group</p>
                                <p class="text-sm font-medium">{{ $productDetail->promo_group ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50">Top Item</p>
                                <p class="text-sm font-medium">{{ $productDetail->topitem ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Satuan & Harga --}}
                    <div class="space-y-4 lg:col-span-3 bg-base-200/30 p-4 rounded-2xl border border-base-300">
                        <h4 class="text-[11px] font-bold uppercase tracking-widest text-base-content/40 border-b border-base-300 pb-2">Satuan, Konversi, & Harga</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50 mb-2">Satuan (UOM)</p>
                                <ul class="space-y-1 text-sm font-mono">
                                    <li>UOM 1: <span class="font-bold">{{ $productDetail->uom1 ?? '-' }}</span></li>
                                    <li>UOM 2: <span class="font-bold">{{ $productDetail->uom2 ?? '-' }}</span></li>
                                    <li>UOM 3: <span class="font-bold">{{ $productDetail->uom3 ?? '-' }}</span></li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50 mb-2">Konversi</p>
                                <ul class="space-y-1 text-sm font-mono">
                                    <li>CRT -> PCS: <span class="font-bold">{{ $productDetail->crttopcs ?? '-' }}</span></li>
                                    <li>CRT -> PAK: <span class="font-bold">{{ $productDetail->crttopack ?? '-' }}</span></li>
                                    <li>PAK -> PCS: <span class="font-bold">{{ $productDetail->packtopcs ?? '-' }}</span></li>
                                </ul>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-base-content/50 mb-2">Harga</p>
                                <div class="bg-base-100 p-3 rounded-xl border border-base-300">
                                    <p class="text-[10px] text-base-content/60">Price HRT</p>
                                    <p class="text-lg font-mono font-bold text-primary">
                                        Rp {{ $productDetail->pricehrt ? number_format($productDetail->pricehrt, 0, ',', '.') : '0' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl shrink-0">
            <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300 transition-all duration-200">Tutup</button>
        </div>
    </div>
</div>
