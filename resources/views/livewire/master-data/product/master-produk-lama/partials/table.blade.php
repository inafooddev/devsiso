<div class="flex-1 overflow-auto bg-base-100 w-full relative">
    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
        <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
            <tr>
                <th class="w-12">No</th>
                <th>Kode & Nama</th>
                <th class="text-center">Status</th>
                <th>Kategori / Brand</th>
                <th>UOM (1/2/3)</th>
                <th>Konversi (CRT/PAK/PCS)</th>
                <th class="text-right">Harga (Price HRT)</th>
                <th class="text-center bg-base-200 shadow-[inset_1px_0_0_rgba(0,0,0,0.1)] w-24">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            @forelse ($this->products as $index => $product)
                <tr wire:key="product-{{ $product->pcode_prc }}" class="hover:bg-base-200/50 transition-colors group">
                    <th>{{ $this->products->firstItem() + $index }}</th>
                    
                    {{-- Produk --}}
                    <td>
                        <div class="flex flex-col gap-0.5">
                            <span class="font-bold text-[11px] text-base-content/90">{{ $product->nama_produk }}</span>
                            <span class="text-[10px] text-base-content/50 font-mono uppercase">{{ $product->pcode_prc }}</span>
                        </div>
                    </td>
                    
                    {{-- Status --}}
                    <td class="text-center">
                        @if ($product->status_product == '1')
                            <span class="badge badge-sm badge-success/20 text-success border-success/30 px-3 rounded-full font-semibold">Aktif</span>
                        @else
                            <span class="badge badge-sm badge-error/20 text-error border-error/30 px-3 rounded-full font-semibold">Nonaktif</span>
                        @endif
                    </td>

                    {{-- Kategori & Brand --}}
                    <td>
                        <div class="flex flex-col gap-0.5 text-[10px] text-base-content/60 font-medium">
                            <span>{{ $product->kategory ?? '-' }}</span>
                            <span>{{ $product->brand ?? '-' }}</span>
                        </div>
                    </td>

                    {{-- UOM --}}
                    <td>
                        <div class="flex flex-col gap-0.5 text-[10px] text-base-content/60 font-medium font-mono">
                            <span>{{ $product->uom1 ?? '-' }} / {{ $product->uom2 ?? '-' }} / {{ $product->uom3 ?? '-' }}</span>
                        </div>
                    </td>

                    {{-- Konversi --}}
                    <td>
                        <div class="flex flex-col gap-0.5 text-[10px] text-base-content/60 font-medium font-mono">
                            <span>CRT->PCS: {{ $product->crttopcs ?? '-' }}</span>
                            <span>CRT->PAK: {{ $product->crttopack ?? '-' }}</span>
                            <span>PAK->PCS: {{ $product->packtopcs ?? '-' }}</span>
                        </div>
                    </td>

                    {{-- Harga --}}
                    <td class="text-right font-mono text-[11px] text-base-content/80">{{ $product->pricehrt ? number_format($product->pricehrt, 0, ',', '.') : '-' }}</td>

                    {{-- Aksi --}}
                    <td class="text-center bg-base-200/40 border-l border-base-300 shadow-[inset_1px_0_0_rgba(0,0,0,0.02)]">
                        <div class="flex items-center justify-center gap-1 transition-opacity duration-200">
                            <x-ui.action-button type="view" wire:click="viewDetail('{{ $product->pcode_prc }}')" class="btn-square" title="Detail" />
                            <x-ui.action-button type="edit" wire:click="edit('{{ $product->pcode_prc }}')" class="btn-square" title="Edit" />
                            <x-ui.action-button type="delete" wire:click="confirmDelete('{{ $product->pcode_prc }}')" class="btn-square" title="Hapus" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="flex flex-col items-center justify-center py-12 text-base-content/40">
                            <x-heroicon-o-inbox class="w-12 h-12 mb-3 opacity-20" />
                            <p class="text-sm font-medium">Tidak ada data ditemukan.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($this->products->hasPages())
    <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
        {{ $this->products->links() }}
    </div>
@endif
