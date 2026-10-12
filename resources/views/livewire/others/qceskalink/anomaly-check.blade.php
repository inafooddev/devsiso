<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-5 w-full h-full">
    <x-slot name="title">Cek Anomali SO Eskalink</x-slot>

    {{-- Tabs Navigation --}}
    @include('livewire.others.qceskalink._tabs')

    {{-- KPI Cards Section (Klik untuk Filter Cepat) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-7 gap-2.5 md:gap-3 shrink-0">
        {{-- Card 1: Total Semua Anomali --}}
        <button type="button" wire:click="filterByAnomalyType('ALL')"
                class="text-left bg-base-100 p-3 rounded-xl shadow-sm border transition-all flex flex-col justify-between relative overflow-hidden group {{ $anomalyType === 'ALL' ? 'border-primary ring-2 ring-primary/30' : 'border-base-300 hover:border-primary/40' }}">
            <div class="flex items-start justify-between relative z-10 w-full">
                <div>
                    <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider">Semua Anomali</h3>
                    <div class="text-lg md:text-xl font-black leading-none mt-1 text-primary">
                        {{ number_format($kpi->total_rows ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-2 pt-1.5 border-t border-base-200 flex items-center justify-between text-[10px] text-base-content/60">
                <span>Depo:</span>
                <span class="font-bold text-base-content">{{ number_format($kpi->affected_branches ?? 0, 0, ',', '.') }} Cabang</span>
            </div>
        </button>

        {{-- Card 2: QTY Negatif (-) tapi Nett Positif (+) --}}
        <button type="button" wire:click="filterByAnomalyType('QTY_NEG_NET_POS')"
                class="text-left bg-base-100 p-3 rounded-xl shadow-sm border transition-all flex flex-col justify-between relative overflow-hidden group {{ $anomalyType === 'QTY_NEG_NET_POS' ? 'border-error ring-2 ring-error/30' : 'border-base-300 hover:border-error/40' }}">
            <div class="flex items-start justify-between relative z-10 w-full">
                <div>
                    <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider">QTY (-) Nett (+)</h3>
                    <div class="text-lg md:text-xl font-black leading-none mt-1 text-error">
                        {{ number_format($kpi->cnt_qty_neg_net_pos ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-arrow-trending-down class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-2 pt-1.5 border-t border-base-200 flex items-center justify-between text-[10px] text-base-content/60">
                <span>Nett:</span>
                <span class="font-mono font-bold text-error truncate ml-1" title="Rp {{ number_format($kpi->sum_qty_neg_net_pos ?? 0, 0, ',', '.') }}">Rp {{ number_format($kpi->sum_qty_neg_net_pos ?? 0, 0, ',', '.') }}</span>
            </div>
        </button>

        {{-- Card 3: QTY Positif (+) tapi Nett Negatif (-) --}}
        <button type="button" wire:click="filterByAnomalyType('QTY_POS_NET_NEG')"
                class="text-left bg-base-100 p-3 rounded-xl shadow-sm border transition-all flex flex-col justify-between relative overflow-hidden group {{ $anomalyType === 'QTY_POS_NET_NEG' ? 'border-warning ring-2 ring-warning/30' : 'border-base-300 hover:border-warning/40' }}">
            <div class="flex items-start justify-between relative z-10 w-full">
                <div>
                    <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider">QTY (+) Nett (-)</h3>
                    <div class="text-lg md:text-xl font-black leading-none mt-1 text-warning">
                        {{ number_format($kpi->cnt_qty_pos_net_neg ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-arrow-trending-up class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-2 pt-1.5 border-t border-base-200 flex items-center justify-between text-[10px] text-base-content/60">
                <span>Nett:</span>
                <span class="font-mono font-bold text-warning truncate ml-1" title="Rp {{ number_format($kpi->sum_qty_pos_net_neg ?? 0, 0, ',', '.') }}">Rp {{ number_format($kpi->sum_qty_pos_net_neg ?? 0, 0, ',', '.') }}</span>
            </div>
        </button>

        {{-- Card 4: QTY Pcs (0) tapi Nett != 0 atau Karton/Pack != 0 --}}
        <button type="button" wire:click="filterByAnomalyType('QTY_ZERO_NET_VAL')"
                class="text-left bg-base-100 p-3 rounded-xl shadow-sm border transition-all flex flex-col justify-between relative overflow-hidden group {{ $anomalyType === 'QTY_ZERO_NET_VAL' ? 'border-info ring-2 ring-info/30' : 'border-base-300 hover:border-info/40' }}">
            <div class="flex items-start justify-between relative z-10 w-full">
                <div>
                    <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider" title="QTY Pcs = 0 tapi ada nilai Nett atau Karton/Pack">QTY Pcs=0 &bull; Ada Nilai</h3>
                    <div class="text-lg md:text-xl font-black leading-none mt-1 text-info">
                        {{ number_format($kpi->cnt_qty_zero_net_val ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-cube-transparent class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-2 pt-1.5 border-t border-base-200 flex items-center justify-between text-[10px] text-base-content/60">
                <span>Nett:</span>
                <span class="font-mono font-bold text-info truncate ml-1">Rp {{ number_format($kpi->sum_qty_zero_net_val ?? 0, 0, ',', '.') }}</span>
            </div>
        </button>

        {{-- Card 5: Non-Bonus tapi Nett = 0 (Poin 4 Prioritas) --}}
        <button type="button" wire:click="filterByAnomalyType('NON_BONUS_NET_ZERO')"
                class="text-left bg-base-100 p-3 rounded-xl shadow-sm border transition-all flex flex-col justify-between relative overflow-hidden group {{ $anomalyType === 'NON_BONUS_NET_ZERO' ? 'border-secondary ring-2 ring-secondary/30' : 'border-base-300 hover:border-secondary/40' }}">
            <div class="flex items-start justify-between relative z-10 w-full">
                <div>
                    <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider" title="Barang Non-Bonus (flag_bonus != Y) tapi Nett Amount = 0">Non-Bonus &bull; Nett=0</h3>
                    <div class="text-lg md:text-xl font-black leading-none mt-1 text-secondary">
                        {{ number_format($kpi->cnt_non_bonus_net_zero ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-2 pt-1.5 border-t border-base-200 flex items-center justify-between text-[10px] text-base-content/60">
                <span>Total QTY:</span>
                <span class="font-mono font-bold text-secondary">{{ number_format($kpi->sum_qty_non_bonus_zero ?? 0, 0, ',', '.') }} Pcs</span>
            </div>
        </button>

        {{-- Card 6: Barang Bonus (Y) tapi Nett != 0 (Poin 5 Prioritas) --}}
        <button type="button" wire:click="filterByAnomalyType('BONUS_HAS_NET')"
                class="text-left bg-base-100 p-3 rounded-xl shadow-sm border transition-all flex flex-col justify-between relative overflow-hidden group {{ $anomalyType === 'BONUS_HAS_NET' ? 'border-accent ring-2 ring-accent/30' : 'border-base-300 hover:border-accent/40' }}">
            <div class="flex items-start justify-between relative z-10 w-full">
                <div>
                    <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider" title="Barang Bonus (flag_bonus = Y) tapi Nett Amount != 0">Bonus (Y) &bull; Nett&ne;0</h3>
                    <div class="text-lg md:text-xl font-black leading-none mt-1 text-accent">
                        {{ number_format($kpi->cnt_bonus_has_net ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-accent/10 flex items-center justify-center text-accent shrink-0">
                    <x-heroicon-s-gift class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-2 pt-1.5 border-t border-base-200 flex items-center justify-between text-[10px] text-base-content/60">
                <span>Nett:</span>
                <span class="font-mono font-bold text-accent truncate ml-1">Rp {{ number_format($kpi->sum_bonus_has_net ?? 0, 0, ',', '.') }}</span>
            </div>
        </button>

        {{-- Card 7: Tipe Faktur RET tapi Positif (+) (Poin 5 Daftar Utama) --}}
        <button type="button" wire:click="filterByAnomalyType('RET_POSITIVE')"
                class="text-left bg-base-100 p-3 rounded-xl shadow-sm border transition-all flex flex-col justify-between relative overflow-hidden group {{ $anomalyType === 'RET_POSITIVE' ? 'border-error ring-2 ring-error/30' : 'border-base-300 hover:border-error/40' }}">
            <div class="flex items-start justify-between relative z-10 w-full">
                <div>
                    <h3 class="text-[10px] font-bold text-base-content/50 uppercase tracking-wider" title="Invoice Type = RET (Retur) tapi QTY atau Nett bernilai positif (+)">Retur (RET) &bull; Nilai (+)</h3>
                    <div class="text-lg md:text-xl font-black leading-none mt-1 text-error">
                        {{ number_format($kpi->cnt_ret_positive ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-error/10 flex items-center justify-center text-error shrink-0">
                    <x-heroicon-s-arrow-uturn-left class="w-4 h-4" />
                </div>
            </div>
            <div class="mt-2 pt-1.5 border-t border-base-200 flex items-center justify-between text-[10px] text-base-content/60">
                <span>Nett:</span>
                <span class="font-mono font-bold text-error truncate ml-1">Rp {{ number_format($kpi->sum_ret_positive ?? 0, 0, ',', '.') }}</span>
            </div>
        </button>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">

        {{-- Header Card & Filters --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col gap-3 bg-base-200/30">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-error/10 flex items-center justify-center text-error">
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                        </div>
                        Deteksi Anomali Selling Out Eskalink (QTY vs Nett Amount)
                    </h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">
                        Mengecek ketidaksesuaian tanda, konversi satuan, status bonus, dan tipe retur antara <span class="font-mono text-base-content">qty3_pcs</span> dan <span class="font-mono text-base-content">nett_amount</span>
                    </p>
                </div>

                {{-- View Mode Toggle & Export --}}
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @if($viewMode === 'detail')
                        <button type="button" wire:click="backToSummary"
                                class="btn btn-sm btn-outline btn-primary rounded-xl normal-case gap-1.5">
                            <x-heroicon-s-arrow-left class="w-4 h-4" /> Kembali ke Rekap Distributor
                        </button>
                    @endif

                    <div class="join bg-base-200 p-0.5 rounded-xl border border-base-300">
                        <button type="button" wire:click="$set('viewMode', 'summary')"
                                class="btn btn-xs join-item rounded-lg normal-case gap-1.5 {{ $viewMode === 'summary' ? 'btn-primary text-white shadow-sm' : 'btn-ghost text-base-content/60' }}">
                            <x-heroicon-s-building-storefront class="w-3.5 h-3.5" /> Rekap Distributor
                        </button>
                        <button type="button" wire:click="$set('viewMode', 'detail')"
                                class="btn btn-xs join-item rounded-lg normal-case gap-1.5 {{ $viewMode === 'detail' ? 'btn-primary text-white shadow-sm' : 'btn-ghost text-base-content/60' }}">
                            <x-heroicon-s-list-bullet class="w-3.5 h-3.5" /> Detail Baris
                        </button>
                    </div>

                    <button type="button" wire:click="export"
                            class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20"
                            wire:loading.attr="disabled" wire:target="export">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        <span class="hidden sm:inline">Export Detail Excel</span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </div>

            {{-- Filter Controls Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 pt-1">
                {{-- Search --}}
                <div class="relative lg:col-span-2">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.350ms="search" type="text"
                           placeholder="Cari No. Invoice, Produk, Customer, Cabang..."
                           class="input input-sm input-bordered pl-9 w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                </div>

                {{-- Month Filter --}}
                <div>
                    <input wire:model.live="monthFilter" type="month"
                           class="input input-sm input-bordered w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                </div>

                {{-- Region Filter --}}
                <div>
                    <select wire:model.live="regionFilter"
                            class="select select-sm select-bordered w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Branch / Distributor Filter --}}
                <div>
                    <select wire:model.live="branchFilter"
                            class="select select-sm select-bordered w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                        <option value="">Semua Distributor</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->branch_code }}">{{ $b->branch_code }} - {{ $b->distributor_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Anomaly Type Filter + Reset --}}
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="anomalyType"
                            class="select select-sm select-bordered flex-1 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                        <option value="ALL">Semua Kategori Anomali</option>
                        <option value="QTY_NEG_NET_POS">1. QTY (-) tapi Nett (+)</option>
                        <option value="QTY_POS_NET_NEG">2. QTY (+) tapi Nett (-)</option>
                        <option value="QTY_ZERO_NET_VAL">3. QTY Pcs (0) tapi Nett/Karton &ne; 0</option>
                        <option value="NON_BONUS_NET_ZERO">4. Non-Bonus tapi Nett = 0</option>
                        <option value="BONUS_HAS_NET">5. Barang Bonus (Y) tapi Nett &ne; 0</option>
                        <option value="RET_POSITIVE">6. Tipe Retur (RET) tapi Nilai (+)</option>
                    </select>

                    @if($regionFilter || $branchFilter || $anomalyType !== 'ALL' || $search)
                        <button type="button" wire:click="resetFilters"
                                class="btn btn-sm btn-ghost btn-square text-error hover:bg-error/10 rounded-xl shrink-0"
                                title="Reset Filter">
                            <x-heroicon-s-arrow-path class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Body Card: Table Area --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <div wire:loading.flex wire:target="monthFilter,regionFilter,branchFilter,anomalyType,search,viewMode,filterByAnomalyType,drillDownBranch,backToSummary,resetFilters"
                 class="absolute inset-0 bg-base-100/60 backdrop-blur-[1px] z-30 items-center justify-center">
                <div class="flex items-center gap-2 bg-base-100 px-4 py-2.5 rounded-2xl shadow-lg border border-base-300">
                    <span class="loading loading-spinner loading-sm text-primary"></span>
                    <span class="text-xs font-bold text-base-content/70">Memuat data anomali...</span>
                </div>
            </div>

            @if($viewMode === 'summary')
                {{-- ========================================== --}}
                {{-- TABEL MODE REKAP PER DISTRIBUTOR           --}}
                {{-- ========================================== --}}
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300">
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th>Region</th>
                            <th>Area</th>
                            <th>Branch Code</th>
                            <th>Distributor Name</th>
                            <th class="text-center">QTY (-) Nett (+)</th>
                            <th class="text-center">QTY (+) Nett (-)</th>
                            <th class="text-center">QTY Pcs=0</th>
                            <th class="text-center">Non-Bonus Nett=0</th>
                            <th class="text-center">Bonus Nett&ne;0</th>
                            <th class="text-center">RET Nilai (+)</th>
                            <th class="text-center">Total Anomali</th>
                            <th class="text-right">Total QTY (Pcs)</th>
                            <th class="text-right">Total Nett Amount</th>
                            <th class="text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($rows as $row)
                            <tr class="hover:bg-base-200/60 transition-colors">
                                <td class="text-center text-base-content/50 font-mono text-xs">{{ $rows->firstItem() + $loop->index }}</td>
                                <td class="font-semibold">{{ $row->region_name ?? '-' }}</td>
                                <td class="text-base-content/70">{{ $row->area_name ?? '-' }}</td>
                                <td class="font-mono text-xs font-bold">{{ $row->branch_code ?? '-' }}</td>
                                <td class="font-bold">{{ $row->branch_name ?? '-' }}</td>
                                <td class="text-center">
                                    @if(($row->cnt_qty_neg_net_pos ?? 0) > 0)
                                        <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}', 'QTY_NEG_NET_POS')"
                                                class="badge badge-error badge-sm text-white font-bold hover:scale-105 transition-transform cursor-pointer" title="Klik untuk lihat detail">
                                            {{ number_format($row->cnt_qty_neg_net_pos, 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(($row->cnt_qty_pos_net_neg ?? 0) > 0)
                                        <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}', 'QTY_POS_NET_NEG')"
                                                class="badge badge-warning badge-sm font-bold hover:scale-105 transition-transform cursor-pointer" title="Klik untuk lihat detail">
                                            {{ number_format($row->cnt_qty_pos_net_neg, 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(($row->cnt_qty_zero_net_val ?? 0) > 0)
                                        <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}', 'QTY_ZERO_NET_VAL')"
                                                class="badge badge-info badge-sm text-white font-bold hover:scale-105 transition-transform cursor-pointer" title="Klik untuk lihat detail">
                                            {{ number_format($row->cnt_qty_zero_net_val, 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(($row->cnt_non_bonus_net_zero ?? 0) > 0)
                                        <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}', 'NON_BONUS_NET_ZERO')"
                                                class="badge badge-secondary badge-sm text-white font-bold hover:scale-105 transition-transform cursor-pointer" title="Klik untuk lihat detail">
                                            {{ number_format($row->cnt_non_bonus_net_zero, 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(($row->cnt_bonus_has_net ?? 0) > 0)
                                        <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}', 'BONUS_HAS_NET')"
                                                class="badge badge-accent badge-sm text-white font-bold hover:scale-105 transition-transform cursor-pointer" title="Klik untuk lihat detail">
                                            {{ number_format($row->cnt_bonus_has_net, 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(($row->cnt_ret_positive ?? 0) > 0)
                                        <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}', 'RET_POSITIVE')"
                                                class="badge badge-error badge-outline badge-sm font-bold hover:scale-105 transition-transform cursor-pointer" title="Klik untuk lihat detail">
                                            {{ number_format($row->cnt_ret_positive, 0, ',', '.') }}
                                        </button>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                </td>
                                <td class="text-center font-mono font-black text-error">
                                    {{ number_format($row->total_anomaly ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono {{ ($row->total_qty_pcs ?? 0) < 0 ? 'text-error font-bold' : '' }}">
                                    {{ number_format($row->total_qty_pcs ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-semibold">
                                    Rp {{ number_format($row->total_nett_amount ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}')"
                                            class="btn btn-xs btn-primary text-white rounded-lg normal-case gap-1 shadow-sm">
                                        <x-heroicon-s-eye class="w-3.5 h-3.5" /> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-14 text-base-content/50">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-heroicon-o-check-badge class="w-12 h-12 text-success/60" />
                                        <p class="font-bold text-sm text-base-content/70">Tidak ditemukan anomali QTY vs Nett Amount</p>
                                        <p class="text-xs text-base-content/40">Semua data transaksi pada filter periode ini sudah konsisten.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                {{-- ========================================== --}}
                {{-- TABEL MODE DETAIL BARIS FAKTUR             --}}
                {{-- ========================================== --}}
                <table class="table table-xs table-zebra table-pin-rows w-full whitespace-nowrap border-collapse">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300">
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th>Region</th>
                            <th>Branch / Distributor</th>
                            <th>Tgl Invoice</th>
                            <th>No. Invoice</th>
                            <th class="text-center">Tipe</th>
                            <th>Customer</th>
                            <th>Produk</th>
                            <th class="text-center">Bonus</th>
                            <th class="text-right">QTY1 (Car)</th>
                            <th class="text-right">QTY2 (Pck)</th>
                            <th class="text-right bg-error/10 text-error">QTY3 (Pcs)</th>
                            <th class="text-right">Gross Amount</th>
                            <th class="text-right">Disc 4</th>
                            <th class="text-right">Disc 8</th>
                            <th class="text-right">DPP</th>
                            <th class="text-right">Tax</th>
                            <th class="text-right bg-error/10 text-error">Nett Amount</th>
                            <th class="text-center">Status Anomali</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        @forelse($rows as $row)
                            <tr class="hover:bg-base-200/60 transition-colors">
                                <td class="text-center text-base-content/50 font-mono">{{ $rows->firstItem() + $loop->index }}</td>
                                <td class="font-medium">{{ $row->region_name ?? '-' }}</td>
                                <td>
                                    <div class="font-bold text-base-content/90 truncate max-w-[200px]" title="{{ $row->branch_name }}">
                                        {{ $row->branch_name ?? '-' }}
                                    </div>
                                    <div class="font-mono text-[10px] text-base-content/50">{{ $row->branch_code ?? '-' }}</div>
                                </td>
                                <td class="font-mono">{{ $row->invoice_date ? \Carbon\Carbon::parse($row->invoice_date)->format('d/m/Y') : '-' }}</td>
                                <td class="font-mono font-bold">{{ $row->invoice_no ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ strtoupper($row->invoice_type ?? '') === 'RET' ? 'badge-error text-white font-bold' : 'badge-ghost' }} badge-xs font-mono">
                                        {{ $row->invoice_type ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-medium truncate max-w-[180px]" title="{{ $row->cust_name }}">{{ $row->cust_name ?? '-' }}</div>
                                    <div class="font-mono text-[10px] text-base-content/50">{{ $row->cust_code_dist ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="font-medium truncate max-w-[200px]" title="{{ $row->prd_name }}">{{ $row->prd_name ?? '-' }}</div>
                                    <div class="font-mono text-[10px] text-base-content/50">{{ $row->prd_code ?? '-' }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ strtoupper($row->flag_bonus ?? '') === 'Y' ? 'badge-accent text-white font-bold' : 'badge-ghost' }} badge-xs font-mono">
                                        {{ $row->flag_bonus ?? '-' }}
                                    </span>
                                </td>

                                <td class="text-right font-mono {{ ($row->qty1_car ?? 0) != 0 && ($row->qty3_pcs ?? 0) == 0 ? 'text-warning font-bold' : 'text-base-content/60' }}">
                                    {{ number_format($row->qty1_car ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono {{ ($row->qty2_pck ?? 0) != 0 && ($row->qty3_pcs ?? 0) == 0 ? 'text-warning font-bold' : 'text-base-content/60' }}">
                                    {{ number_format($row->qty2_pck ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Highlighted QTY3_PCS --}}
                                <td class="text-right font-mono font-bold bg-error/10 border-x border-error/20 {{ ($row->qty3_pcs ?? 0) < 0 ? 'text-error' : (($row->qty3_pcs ?? 0) > 0 ? 'text-success' : 'text-info') }}">
                                    {{ number_format($row->qty3_pcs ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-right font-mono">{{ number_format($row->gross_amount ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right font-mono">{{ number_format($row->line_discount_4 ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right font-mono">{{ number_format($row->line_discount_8 ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right font-mono">{{ number_format($row->dpp ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right font-mono">{{ number_format($row->tax ?? 0, 0, ',', '.') }}</td>

                                {{-- Highlighted NETT_AMOUNT --}}
                                <td class="text-right font-mono font-bold bg-error/10 border-x border-error/20 {{ ($row->nett_amount ?? 0) < 0 ? 'text-error' : (($row->nett_amount ?? 0) > 0 ? 'text-success' : 'text-secondary') }}">
                                    {{ number_format($row->nett_amount ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Anomaly Badge --}}
                                <td class="text-center">
                                    @if($row->anomaly_code === 'QTY_NEG_NET_POS')
                                        <span class="badge badge-error badge-sm text-white font-bold gap-1 px-2.5">
                                            QTY (-) &bull; Nett (+)
                                        </span>
                                    @elseif($row->anomaly_code === 'QTY_POS_NET_NEG')
                                        <span class="badge badge-warning badge-sm font-bold gap-1 px-2.5">
                                            QTY (+) &bull; Nett (-)
                                        </span>
                                    @elseif($row->anomaly_code === 'QTY_ZERO_NET_VAL')
                                        <span class="badge badge-info badge-sm text-white font-bold gap-1 px-2.5">
                                            QTY Pcs (0) &bull; Nett/Karton &ne; 0
                                        </span>
                                    @elseif($row->anomaly_code === 'NON_BONUS_NET_ZERO')
                                        <span class="badge badge-secondary badge-sm text-white font-bold gap-1 px-2.5">
                                            Non-Bonus &bull; Nett = 0
                                        </span>
                                    @elseif($row->anomaly_code === 'BONUS_HAS_NET')
                                        <span class="badge badge-accent badge-sm text-white font-bold gap-1 px-2.5">
                                            Bonus (Y) &bull; Nett &ne; 0
                                        </span>
                                    @elseif($row->anomaly_code === 'RET_POSITIVE')
                                        <span class="badge badge-error badge-outline badge-sm font-bold gap-1 px-2.5">
                                            Retur (RET) &bull; Nilai (+)
                                        </span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">Anomali</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="19" class="text-center py-14 text-base-content/50">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-heroicon-o-check-badge class="w-12 h-12 text-success/60" />
                                        <p class="font-bold text-sm text-base-content/70">Tidak ditemukan anomali QTY vs Nett Amount</p>
                                        <p class="text-xs text-base-content/40">Semua baris transaksi pada filter saat ini memiliki nilai QTY dan Nett Amount yang selaras.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Pagination Footer --}}
        @if($rows->hasPages())
            <div class="p-3 border-t border-base-300 bg-base-50 shrink-0">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
