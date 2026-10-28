<div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 w-full flex flex-col overflow-hidden shrink-0">
    
    {{-- Header Tabel & Kontrol --}}
    <div class="px-5 py-4 border-b border-base-200 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3 shrink-0">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold uppercase tracking-wider text-base-content/70">
                    Rincian Transaksi Sell In vs Sell Out
                </span>
                <span class="px-2 py-0.5 rounded-md bg-base-200 text-base-content/70 text-xs font-semibold tabular-nums">
                    {{ number_format($items->total(), 0, ',', '.') }} {{ $viewMode === 'product' ? 'SKU' : 'Distributor' }}
                </span>
                @if($selectedRegFest !== 'ALL')
                    <span class="px-2 py-0.5 rounded-md bg-secondary/10 text-secondary text-xs font-semibold">
                        Tipe: {{ $selectedRegFest }}
                    </span>
                @endif
                @if($selectedStatus !== 'ALL')
                    <button type="button" 
                            wire:click="setStatus('ALL')" 
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-xs font-semibold"
                            title="Klik untuk reset filter status">
                        Status: {{ match($selectedStatus) {
                            'balanced' => 'Seimbang',
                            'high_out' => 'Sell Out Tinggi',
                            'low_out'  => 'Sell In Tinggi',
                            'no_out'   => '0 Sell Out',
                            'no_in'    => '0 Sell In',
                            default    => $selectedStatus
                        } }}
                        <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif
            </div>
            <p class="text-xs text-base-content/50 mt-0.5">
                Periode {{ $months[$selectedMonth] }} {{ $selectedYear }} • Klik judul kolom angka untuk mengurutkan data
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap w-full lg:w-auto justify-start lg:justify-end">
            {{-- Filter Status Dropdown --}}
            <div class="flex items-center">
                <select wire:model.live="selectedStatus" class="select select-sm h-8 min-h-0 text-xs rounded-xl bg-base-100 border-base-300 font-medium focus:border-primary focus:outline-hidden {{ $selectedStatus !== 'ALL' ? 'border-primary text-primary font-semibold' : 'text-base-content/70' }}">
                    <option value="ALL">Semua Status</option>
                    <option value="balanced">🟢 Seimbang (75% - 125%)</option>
                    <option value="high_out">🔵 Sell Out Tinggi (> 125%)</option>
                    <option value="low_out">🟡 Sell In Tinggi (&lt; 75%)</option>
                    <option value="no_out">⚪ 0 Sell Out</option>
                    <option value="no_in">🟣 0 Sell In</option>
                </select>
            </div>

            {{-- Segmented Toggle View: Produk vs Distributor --}}
            <div class="inline-flex items-center p-0.5 rounded-xl bg-base-200/80 border border-base-300">
                <button type="button"
                        wire:click="setViewMode('product')" 
                        class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 {{ $viewMode === 'product' ? 'bg-base-100 text-base-content font-bold shadow-xs' : 'text-base-content/60 hover:text-base-content font-medium' }}">
                    <x-heroicon-o-cube class="w-3.5 h-3.5" />
                    Per Produk (SKU)
                </button>
                <button type="button"
                        wire:click="setViewMode('distributor')" 
                        class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 {{ $viewMode === 'distributor' ? 'bg-base-100 text-base-content font-bold shadow-xs' : 'text-base-content/60 hover:text-base-content font-medium' }}">
                    <x-heroicon-o-building-storefront class="w-3.5 h-3.5" />
                    Per Distributor
                </button>
            </div>

            {{-- Search Box --}}
            <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari {{ $viewMode === 'product' ? 'kode / nama SKU...' : 'kode / nama distributor...' }}" />

            {{-- Export Button --}}
            <button wire:click="export" class="btn btn-sm h-8 min-h-0 px-3 rounded-xl btn-outline border-base-300 hover:border-emerald-500 hover:bg-emerald-500/10 hover:text-emerald-600 text-xs font-semibold gap-1.5">
                <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 text-emerald-600" />
                Export Excel
            </button>
        </div>
    </div>

    {{-- Main Financial-Grade Table Area --}}
    <div class="overflow-x-auto w-full relative">
        <table class="table table-sm w-full whitespace-nowrap">
            <thead>
                <tr class="bg-base-200/50 text-xs font-bold uppercase tracking-wider text-base-content/60 border-b border-base-200">
                    @if($viewMode === 'product')
                        <th class="py-3 pl-5">Produk / SKU</th>
                        <th class="py-3">Brand / Sub Brand</th>
                        <th wire:click="sortBy('reg_fest')" class="py-3 text-center cursor-pointer hover:text-base-content select-none">
                            Tipe
                            @if($sortField === 'reg_fest') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                    @else
                        <th class="py-3 pl-5">Distributor & Depo</th>
                        <th class="py-3">Region / Area</th>
                    @endif

                    <th wire:click="sortBy('qty_in_ktn')" class="py-3 text-right cursor-pointer hover:text-base-content select-none">
                        Sell In (Ktn)
                        @if($sortField === 'qty_in_ktn') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th wire:click="sortBy('qty_out_ktn')" class="py-3 text-right cursor-pointer hover:text-base-content select-none">
                        Sell Out (Ktn)
                        @if($sortField === 'qty_out_ktn') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th wire:click="sortBy('value_in')" class="py-3 text-right cursor-pointer hover:text-base-content select-none">
                        Sell In (Rp)
                        @if($sortField === 'value_in') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th wire:click="sortBy('value_out')" class="py-3 text-right cursor-pointer hover:text-base-content select-none">
                        Sell Out (Rp)
                        @if($sortField === 'value_out') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>

                    @if($viewMode === 'distributor')
                        <th wire:click="sortBy('target_val')" class="py-3 text-right cursor-pointer hover:text-base-content select-none">
                            Target Depo (Rp)
                            @if($sortField === 'target_val') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th wire:click="sortBy('ach_in_pct')" class="py-3 text-center cursor-pointer hover:text-base-content select-none">
                            % Ach
                            @if($sortField === 'ach_in_pct') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                    @endif

                    <th wire:click="sortBy('{{ $unitMode === 'val' ? 'gap_val' : 'gap_qty' }}')" class="py-3 text-right cursor-pointer hover:text-base-content select-none">
                        Selisih {{ $unitMode === 'val' ? '(Rp)' : '(Ktn)' }}
                        @if(in_array($sortField, ['gap_qty', 'gap_val'])) <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>

                    <th wire:click="sortBy('ratio')" class="py-3 text-right cursor-pointer hover:text-base-content select-none min-w-[130px]">
                        Rasio Out/In
                        @if($sortField === 'ratio') <span class="text-primary">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>

                    <th class="py-3 pr-5 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-200/70 text-xs">
                @forelse($items as $row)
                    <tr class="hover:bg-base-200/30 transition-colors">
                        
                        {{-- Kolom Identitas --}}
                        @if($viewMode === 'product')
                            <td class="py-3 pl-5">
                                <div class="font-semibold text-base-content text-xs">{{ $row->product_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/50 font-mono tabular-nums mt-0.5">{{ $row->product_id }}</div>
                            </td>
                            <td class="py-3">
                                <div class="font-medium text-base-content/90">{{ $row->brand_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/50 mt-0.5">{{ $row->sub_brand_name ?? '-' }}</div>
                            </td>
                            <td class="py-3 text-center">
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $row->reg_fest === 'FEST' ? 'bg-secondary/10 text-secondary' : 'bg-base-200 text-base-content/70' }}">
                                    {{ $row->reg_fest ?? '-' }}
                                </span>
                            </td>
                        @else
                            <td class="py-3 pl-5">
                                <div class="font-semibold text-base-content text-xs">{{ $row->distributor_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/50 mt-0.5">
                                    <span class="font-mono tabular-nums">{{ $row->distributor_code }}</span> • Depo {{ $row->branch_name ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="font-medium text-base-content/90">{{ $row->region_name ?? '-' }}</div>
                                <div class="text-xs text-base-content/50 mt-0.5">{{ $row->area_name ?? '-' }}</div>
                            </td>
                        @endif

                        {{-- Sell In (Ktn) - Neutral Clean Number --}}
                        <td class="py-3 text-right font-semibold text-base-content tabular-nums">
                            {{ number_format($row->qty_in_ktn, 1, ',', '.') }}
                        </td>

                        {{-- Sell Out (Ktn) - Neutral Clean Number --}}
                        <td class="py-3 text-right font-semibold text-base-content tabular-nums">
                            {{ number_format($row->qty_out_ktn, 1, ',', '.') }}
                        </td>

                        {{-- Sell In (Rp) - Neutral Clean Number --}}
                        <td class="py-3 text-right font-medium text-base-content/80 tabular-nums">
                            {{ number_format($row->value_in, 0, ',', '.') }}
                        </td>

                        {{-- Sell Out (Rp) - Neutral Clean Number --}}
                        <td class="py-3 text-right font-medium text-base-content/80 tabular-nums">
                            {{ number_format($row->value_out, 0, ',', '.') }}
                        </td>

                        @if($viewMode === 'distributor')
                            {{-- Target Depo (Rp) --}}
                            <td class="py-3 text-right font-medium text-base-content/80 tabular-nums">
                                @if(($row->target_val ?? 0) > 0)
                                    <div>{{ number_format($row->target_val, 0, ',', '.') }}</div>
                                    @if(($row->branch_dist_count ?? 1) > 1)
                                        <div class="text-xs text-base-content/45 mt-0.5" title="Target Depo {{ $row->branch_name }} dibagi bersama {{ $row->branch_dist_count }} distributor aktif">
                                            Target Depo ({{ $row->branch_dist_count }} Dist)
                                        </div>
                                    @endif
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>

                            {{-- % Ach Target (Semantic Badge) --}}
                            <td class="py-3 text-center tabular-nums">
                                @if(($row->target_val ?? 0) > 0)
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ ($row->ach_in_pct ?? 0) >= 100 ? 'bg-emerald-500/10 text-emerald-600' : (($row->ach_in_pct ?? 0) >= 80 ? 'bg-amber-500/10 text-amber-600' : 'bg-base-200 text-base-content/70') }}">
                                            {{ number_format($row->ach_in_pct ?? 0, 1, ',', '.') }}%
                                        </span>
                                        @if(($row->branch_dist_count ?? 1) > 1)
                                            <span class="text-xs text-base-content/50 font-medium" title="Total Pencapaian Gabungan Cabang {{ $row->branch_name }}">
                                                Depo: {{ number_format($row->branch_ach_pct ?? 0, 1, ',', '.') }}%
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                        @endif

                        {{-- Selisih (In - Out) - Semantic Variance --}}
                        @php
                            $activeGap = $unitMode === 'val' ? $row->gap_val : $row->gap_qty;
                        @endphp
                        <td class="py-3 text-right font-semibold tabular-nums {{ $activeGap > 0 ? 'text-amber-600' : ($activeGap < 0 ? 'text-sky-600' : 'text-base-content/50') }}">
                            @if($unitMode === 'val')
                                {{ $row->gap_val > 0 ? '+' : '' }}{{ number_format($row->gap_val, 0, ',', '.') }}
                            @else
                                {{ $row->gap_qty > 0 ? '+' : '' }}{{ number_format($row->gap_qty, 1, ',', '.') }}
                            @endif
                        </td>

                        {{-- Rasio Out/In + Mini Visual Progress Bar --}}
                        <td class="py-3 text-right tabular-nums">
                            @if(($row->status ?? '') === 'no_in')
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">100% Out (0 In)</span>
                            @elseif(($row->status ?? '') === 'no_out')
                                <span class="text-xs font-medium text-base-content/40">0,0%</span>
                            @else
                                @php
                                    $barWidth = min(max($row->ratio, 0), 100);
                                    $ratioColor = $row->ratio > 125 ? 'text-sky-600' : ($row->ratio < 75 ? 'text-amber-600' : 'text-emerald-600');
                                    $barColor = $row->ratio > 125 ? 'bg-sky-500' : ($row->ratio < 75 ? 'bg-amber-500' : 'bg-emerald-500');
                                @endphp
                                <div class="flex flex-col items-end gap-1">
                                    <span class="font-bold text-xs {{ $ratioColor }}">
                                        {{ number_format($row->ratio, 1, ',', '.') }}%
                                    </span>
                                    <div class="w-20 bg-base-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ $barWidth }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </td>

                        {{-- Status Pill Badge (Clickable to Filter) --}}
                        <td class="py-3 pr-5 text-center">
                            @php
                                $statusClasses = match($row->status ?? '') {
                                    'balanced' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20 hover:bg-emerald-500/20',
                                    'high_out' => 'bg-sky-500/10 text-sky-600 border-sky-500/20 hover:bg-sky-500/20',
                                    'low_out'  => 'bg-amber-500/10 text-amber-600 border-amber-500/20 hover:bg-amber-500/20',
                                    'no_in'    => 'bg-purple-500/10 text-purple-600 border-purple-500/20 hover:bg-purple-500/20',
                                    'no_out'   => 'bg-slate-500/10 text-slate-600 border-slate-500/20 hover:bg-slate-500/20',
                                    default    => 'bg-base-200 text-base-content/60 border-base-300 hover:bg-base-300',
                                };
                            @endphp
                            <button type="button" 
                                    wire:click="setStatus('{{ $row->status }}')"
                                    title="{{ $row->analysis_narrative }} (Klik untuk filter status ini)"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border transition-all cursor-pointer {{ $statusClasses }}">
                                {{ $row->status_label }}
                            </button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $viewMode === 'distributor' ? 11 : 10 }}" class="text-center py-12 text-base-content/50">
                            <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-2 opacity-30" />
                            <p class="font-semibold text-sm text-base-content/70">Tidak ada data transaksi yang cocok dengan kriteria filter</p>
                            <p class="text-xs mt-0.5">Coba ubah filter periode, tipe Reg/Fest, status, atau kata kunci pencarian.</p>
                            @if($selectedStatus !== 'ALL')
                                <button type="button" wire:click="setStatus('ALL')" class="btn btn-xs btn-ghost text-primary mt-2">
                                    Reset Filter Status
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    @if($items->hasPages())
        <div class="px-5 py-3 border-t border-base-200 bg-base-100 shrink-0">
            {{ $items->links() }}
        </div>
    @endif

</div>
