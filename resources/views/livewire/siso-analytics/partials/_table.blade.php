<div class="bg-base-100 rounded-xl shadow-sm border border-base-300 w-full flex flex-col overflow-hidden shrink-0">
    
    {{-- Header Tabel & Kontrol --}}
    <div class="p-3 md:p-4 border-b border-base-300 bg-base-200/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 shrink-0">
        <div>
            <h3 class="font-bold text-sm md:text-base text-base-content flex items-center gap-2">
                <x-heroicon-s-table-cells class="w-4 h-4 text-primary" />
                Data Detail: Sell In vs Sell Out (Bulan {{ $months[$selectedMonth] }} {{ $selectedYear }})
            </h3>
            <p class="text-xs text-base-content/60 mt-0.5">
                Rincian angka transaksi Sell In dan Sell Out per {{ $viewMode === 'product' ? 'Produk (SKU)' : 'Distributor' }}.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap w-full md:w-auto justify-start md:justify-end">
            {{-- Toggle View --}}
            <div class="join border border-base-300">
                <button wire:click="setViewMode('product')" 
                        class="join-item btn btn-xs {{ $viewMode === 'product' ? 'btn-primary font-bold' : 'btn-ghost text-base-content/70' }}">
                    <x-heroicon-s-cube class="w-3.5 h-3.5 mr-1" /> Per Produk (SKU)
                </button>
                <button wire:click="setViewMode('distributor')" 
                        class="join-item btn btn-xs {{ $viewMode === 'distributor' ? 'btn-primary font-bold' : 'btn-ghost text-base-content/70' }}">
                    <x-heroicon-s-building-storefront class="w-3.5 h-3.5 mr-1" /> Per Distributor
                </button>
            </div>

            {{-- Search Box --}}
            <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari {{ $viewMode === 'product' ? 'SKU / Produk...' : 'Distributor...' }}" />

            {{-- Export Button --}}
            <button wire:click="export" class="btn btn-xs btn-outline btn-success gap-1 font-bold">
                <x-heroicon-s-arrow-down-tray class="w-3.5 h-3.5" />
                Excel
            </button>
        </div>
    </div>

    {{-- Main Table Area --}}
    <div class="overflow-x-auto w-full relative">
        <table class="table table-sm w-full table-pin-rows whitespace-nowrap">
            <thead class="text-xs uppercase bg-base-200 text-base-content font-bold border-b border-base-300">
                <tr>
                    @if($viewMode === 'product')
                        <th class="bg-base-200 z-20">Produk / SKU</th>
                        <th class="bg-base-200">Brand</th>
                    @else
                        <th class="bg-base-200 z-20">Distributor</th>
                        <th class="bg-base-200">Wilayah</th>
                    @endif
                    <th wire:click="sortBy('qty_in_ktn')" class="bg-base-200 text-right cursor-pointer hover:bg-base-300 select-none">
                        Sell In {{ $unitMode === 'val' ? '(Rp)' : '(Ktn)' }}
                        @if($sortField === 'qty_in_ktn') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th wire:click="sortBy('qty_out_ktn')" class="bg-base-200 text-right cursor-pointer hover:bg-base-300 select-none">
                        Sell Out {{ $unitMode === 'val' ? '(Rp)' : '(Ktn)' }}
                        @if($sortField === 'qty_out_ktn') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    @if($viewMode === 'distributor' && $unitMode === 'val')
                        <th class="bg-base-200 text-right text-indigo-600 font-bold">
                            Target Depo (Rp)
                        </th>
                        <th class="bg-base-200 text-center text-cyan-600 font-bold">
                            % Ach
                        </th>
                    @endif
                    <th wire:click="sortBy('gap_qty')" class="bg-base-200 text-right cursor-pointer hover:bg-base-300 select-none">
                        Selisih (In - Out)
                        @if($sortField === 'gap_qty') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th wire:click="sortBy('ratio')" class="bg-base-200 text-center cursor-pointer hover:bg-base-300 select-none">
                        Rasio (%)
                        @if($sortField === 'ratio') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th class="bg-base-200 text-center">Status</th>
                    <th class="bg-base-200 min-w-[280px]">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-200 text-xs">
                @forelse($items as $row)
                    <tr class="hover:bg-base-200/40 transition-colors">
                        
                        {{-- Kolom Identitas --}}
                        @if($viewMode === 'product')
                            <td>
                                <div class="font-bold text-base-content text-xs md:text-sm">{{ $row->product_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60 font-medium">{{ $row->product_id }}</div>
                            </td>
                            <td>
                                <div class="font-semibold">{{ $row->brand_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60">{{ $row->sub_brand_name ?? '-' }}</div>
                            </td>
                        @else
                            <td>
                                <div class="font-bold text-base-content text-xs md:text-sm">{{ $row->distributor_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60 font-medium">{{ $row->distributor_code }} ({{ $row->branch_name ?? '-' }})</div>
                            </td>
                            <td>
                                <div class="font-semibold">{{ $row->area_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60">{{ $row->region_name ?? '-' }}</div>
                            </td>
                        @endif

                        {{-- Sell In --}}
                        <td class="text-right">
                            <span class="font-bold text-primary text-xs md:text-sm">
                                {{ number_format($row->qty_in_ktn, 1, ',', '.') }}
                            </span>
                            <span class="block text-xs text-base-content/60">
                                Rp {{ number_format($row->value_in, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Sell Out --}}
                        <td class="text-right">
                            <span class="font-bold text-secondary text-xs md:text-sm">
                                {{ number_format($row->qty_out_ktn, 1, ',', '.') }}
                            </span>
                            <span class="block text-xs text-base-content/60">
                                Rp {{ number_format($row->value_out, 0, ',', '.') }}
                            </span>
                        </td>

                        @if($viewMode === 'distributor' && $unitMode === 'val')
                            {{-- Target Depo --}}
                            <td class="text-right">
                                @if(($row->target_val ?? 0) > 0)
                                    <span class="font-bold text-indigo-600 text-xs md:text-sm">
                                        Rp {{ number_format($row->target_val, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>

                            {{-- % Ach Target --}}
                            <td class="text-center">
                                @if(($row->target_val ?? 0) > 0)
                                    <span class="badge badge-sm {{ ($row->ach_in_pct ?? 0) >= 100 ? 'badge-success text-success-content font-bold' : (($row->ach_in_pct ?? 0) >= 80 ? 'badge-warning text-warning-content font-bold' : 'badge-ghost text-base-content/70 font-semibold') }}">
                                        {{ $row->ach_in_pct ?? 0 }}%
                                    </span>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                        @endif

                        {{-- Gap Ktn --}}
                        <td class="text-right font-bold text-xs md:text-sm {{ $row->gap_qty > 0 ? 'text-warning' : ($row->gap_qty < 0 ? 'text-info' : 'text-base-content/60') }}">
                            {{ $row->gap_qty > 0 ? '+' : '' }}{{ number_format($row->gap_qty, 1, ',', '.') }}
                            <span class="block text-xs font-normal text-base-content/60">
                                {{ $row->gap_val > 0 ? '+' : '' }}Rp {{ number_format($row->gap_val, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Rasio Serapan --}}
                        <td class="text-center">
                            @if($row->qty_in_ktn == 0 && $row->qty_out_ktn > 0)
                                <span class="badge badge-sm badge-info font-bold">100% Out (0 In)</span>
                            @elseif($row->qty_out_ktn == 0)
                                <span class="badge badge-sm badge-ghost font-bold">0% Out</span>
                            @else
                                <span class="font-bold text-xs md:text-sm {{ $row->ratio > 125 ? 'text-info' : ($row->ratio < 75 ? 'text-warning' : 'text-success') }}">
                                    {{ number_format($row->ratio, 1, ',', '.') }}%
                                </span>
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td class="text-center">
                            <span class="badge badge-sm {{ $row->badge_class }} font-bold py-2 px-2.5">
                                {{ $row->status_label }}
                            </span>
                        </td>

                        {{-- Keterangan --}}
                        <td class="whitespace-normal text-xs leading-normal font-medium max-w-[340px] text-base-content/80">
                            {{ $row->analysis_narrative }}
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-base-content/60">
                            <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-2 opacity-40" />
                            <p class="font-bold text-sm">Tidak ada data transaksi yang cocok dengan kriteria filter.</p>
                            <p class="text-xs mt-1">Coba ubah filter periode atau wilayah.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    @if($items->hasPages())
        <div class="px-4 py-3 border-t border-base-300 bg-base-100 shrink-0">
            {{ $items->links() }}
        </div>
    @endif

</div>