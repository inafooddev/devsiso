<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
    <x-slot name="title">Referensi Number (Nota vs Order)</x-slot>

    {{-- Tabs Navigation --}}
    @include('livewire.others.qceskalink._tabs')

    {{-- Notifikasi Toast --}}
    <div class="toast toast-top toast-center z-[100000] mt-16" style="z-index: 100000;">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                    <div class="text-sm">{{ session('message') }}</div>
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                    <div class="text-sm">{{ session('error') }}</div>
                </div>
            </div>
        @endif
    </div>

    @php
        $totInv     = (int) ($kpi->total_invoices ?? 0);
        $withOrd    = (int) ($kpi->invoices_with_ord ?? 0);
        $withoutOrd = (int) ($kpi->invoices_without_ord ?? 0);
        $discNoOrd  = (int) ($kpi->disc_without_ord_inv ?? 0);
        $disc4Val   = (float) ($kpi->disc4_val_without_ord ?? 0);
        $disc8Qty   = (float) ($kpi->disc8_qty_without_ord ?? $kpi->disc8_val_without_ord ?? 0);
        $totDiscVal = $disc4Val; // Nilai klaim finansial (Rp) murni dari Disc 4, Disc 8 berupa Qty (Pcs)

        $pctWithOrd = $totInv > 0 ? round(($withOrd / $totInv) * 100) : 0;
        $pctWithout = $totInv > 0 ? round(($withoutOrd / $totInv) * 100) : 0;
        $pctDiscNo  = $totInv > 0 ? round(($discNoOrd / $totInv) * 100) : 0;
    @endphp

    {{-- 1. Summary KPI Cards (5 Kartu Interaktif + Persentase & Progress) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 shrink-0">
        {{-- Card 1: Total Nota --}}
        <button type="button" wire:click="quickFilter('ALL')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $statusFilter === 'ALL' ? 'border-primary ring-2 ring-primary/30' : 'border-base-300' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/50">Total Nota (Invoice)</p>
                    <p class="text-2xl font-black text-base-content mt-0.5">{{ number_format($totInv) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-primary/10 text-primary group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-document-text class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/50 font-medium">
                <span>Total Invoice SO</span>
                <span class="badge badge-xs badge-ghost font-bold">100%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden">
                <div class="bg-primary h-full rounded-full" style="width: 100%"></div>
            </div>
        </button>

        {{-- Card 2: Nota Ber-Order (Lengkap) --}}
        <button type="button" wire:click="quickFilter('WITH_ORD')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $statusFilter === 'WITH_ORD' ? 'border-success ring-2 ring-success/30' : 'border-success/25' }}">
            <div class="absolute inset-0 bg-success/[0.03]"></div>
            <div class="flex items-start justify-between gap-2 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-success/80">Nota Ada No Order</p>
                    <p class="text-2xl font-black text-success mt-0.5">{{ number_format($withOrd) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-success/10 text-success group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-check-circle class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/60 font-medium relative z-10">
                <span>Kepatuhan Referensi</span>
                <span class="badge badge-xs badge-success text-white font-bold">{{ $pctWithOrd }}%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden relative z-10">
                <div class="bg-success h-full rounded-full transition-all duration-500" style="width: {{ $pctWithOrd }}%"></div>
            </div>
        </button>

        {{-- Card 3: Nota Tanpa No Order --}}
        <button type="button" wire:click="quickFilter('WITHOUT_ORD')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $statusFilter === 'WITHOUT_ORD' ? 'border-warning ring-2 ring-warning/30' : 'border-warning/25' }}">
            <div class="absolute inset-0 bg-warning/[0.03]"></div>
            <div class="flex items-start justify-between gap-2 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-warning/80">Nota Tanpa No Order</p>
                    <p class="text-2xl font-black text-warning mt-0.5">{{ number_format($withoutOrd) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-warning/10 text-warning group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-exclamation-circle class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/60 font-medium relative z-10">
                <span>Tidak Ada Ref Order</span>
                <span class="badge badge-xs badge-warning text-warning-content font-bold">{{ $pctWithout }}%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden relative z-10">
                <div class="bg-warning h-full rounded-full transition-all duration-500" style="width: {{ $pctWithout }}%"></div>
            </div>
        </button>

        {{-- Card 4: Kritis Klaim (Ada Diskon Tanpa Order) --}}
        <button type="button" wire:click="quickFilter('DISC_WITHOUT_ORD')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $statusFilter === 'DISC_WITHOUT_ORD' ? 'border-error ring-2 ring-error/30' : 'border-error/25' }}">
            <div class="absolute inset-0 bg-error/[0.03]"></div>
            <div class="flex items-start justify-between gap-2 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-error/80">Diskon Tanpa Order</p>
                    <p class="text-2xl font-black text-error mt-0.5">{{ number_format($discNoOrd) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-error/10 text-error group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-shield-exclamation class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/60 font-medium relative z-10">
                <span>Kritis Terancam Klaim</span>
                <span class="badge badge-xs badge-error text-white font-bold">{{ $pctDiscNo }}%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden relative z-10">
                <div class="bg-error h-full rounded-full transition-all duration-500" style="width: {{ $pctDiscNo }}%"></div>
            </div>
        </button>

        {{-- Card 5: Potensi Nominal Gagal Klaim --}}
        <button type="button" wire:click="quickFilter('DISC_WITHOUT_ORD')"
                class="text-left bg-base-100 rounded-xl shadow-sm border border-rose-300 p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden bg-rose-50/20">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-rose-700">Potensi Nilai Klaim (Disc 4)</p>
                    <p class="text-xl font-black text-rose-600 mt-0.5 truncate">Rp {{ number_format($totDiscVal, 0, ',', '.') }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-600 group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-banknotes class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[10px] text-base-content/60 font-medium">
                <span>LD 4: Rp {{ number_format($disc4Val, 0, ',', '.') }}</span>
                <span class="text-amber-700 font-bold">LD 8: {{ number_format($disc8Qty, 0, ',', '.') }} Pcs</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden">
                <div class="bg-rose-500 h-full rounded-full" style="width: 100%"></div>
            </div>
        </button>
    </div>

    {{-- 2. Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">

        {{-- Header Card & Actions Bar --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-col gap-3 bg-base-200/30">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="shrink-0">
                    <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <x-heroicon-s-identification class="w-5 h-5" />
                        </div>
                        Audit Referensi Nomor Order (Klaim Diskon)
                    </h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">
                        Memvalidasi keberadaan Nomor Order (ord_no) pada faktur penjualan sebagai syarat klaim Line Discount 4 & Line Discount 8
                    </p>
                </div>

                {{-- Mode Switcher & Export Button --}}
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    {{-- Toggle Summary / Detail --}}
                    <div class="join bg-base-100 p-0.5 rounded-xl border border-base-300 shadow-xs">
                        <button type="button" wire:click="$set('viewMode', 'summary')"
                                class="btn btn-xs join-item rounded-lg normal-case font-bold gap-1 {{ $viewMode === 'summary' ? 'btn-primary text-white shadow-xs' : 'btn-ghost text-base-content/70' }}">
                            <x-heroicon-s-table-cells class="w-3.5 h-3.5" />
                            Rekap Cabang
                        </button>
                        <button type="button" wire:click="$set('viewMode', 'detail')"
                                class="btn btn-xs join-item rounded-lg normal-case font-bold gap-1 {{ $viewMode === 'detail' ? 'btn-primary text-white shadow-xs' : 'btn-ghost text-base-content/70' }}">
                            <x-heroicon-s-list-bullet class="w-3.5 h-3.5" />
                            Detail Transaksi
                        </button>
                    </div>

                    {{-- Export Excel --}}
                    <button type="button" wire:click="export"
                            class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20"
                            wire:loading.attr="disabled" wire:target="export">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        <span>Export Excel</span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 pt-0.5">
                {{-- Search --}}
                <div class="relative lg:col-span-2">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari no nota, no order, cabang, customer..."
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
                        @foreach($regions as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Branch Filter --}}
                <div>
                    <select wire:model.live="branchFilter"
                            class="select select-sm select-bordered w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                        <option value="">Semua Cabang / Depo</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->branch_code }}">{{ $b->branch_code }} - {{ $b->distributor_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter + Reset Button --}}
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="statusFilter"
                            class="select select-sm select-bordered flex-1 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 font-medium">
                        <option value="ALL">Semua Cabang (76 Master)</option>
                        <option value="PULLED">Sudah Tarik Data SO</option>
                        <option value="NOT_PULLED">Belum Tarik Data SO</option>
                        <option value="DISC_WITHOUT_ORD">Kritis Klaim (Ada Diskon)</option>
                        <option value="WITHOUT_ORD">Semua Tanpa No Order</option>
                        <option value="WITH_ORD">Lengkap Ada No Order</option>
                    </select>

                    @if($search || $regionFilter || $branchFilter || $statusFilter !== 'ALL' || ($viewMode === 'summary' && $sortField !== 'region_name') || ($viewMode === 'detail' && $sortField !== 'invoice_date'))
                        <button type="button" wire:click="resetFilters"
                                class="btn btn-sm btn-ghost btn-square text-error hover:bg-error/10 rounded-xl shrink-0"
                                title="Reset Filter & Urutan">
                            <x-heroicon-s-arrow-path class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Active Filter Drilldown Indicator Bar --}}
        @if ($branchFilter && $viewMode === 'detail')
        <div class="px-4 py-2 bg-info/10 border-b border-info/20 flex items-center justify-between gap-3 text-xs text-info shrink-0">
            <div class="flex items-center gap-2">
                <x-heroicon-s-funnel class="w-4 h-4 shrink-0" />
                <span>Menampilkan rincian transaksi cabang: <strong>{{ $branchFilter }}</strong></span>
            </div>
            <button type="button" wire:click="backToSummary" class="btn btn-xs btn-outline btn-info rounded-lg normal-case gap-1 font-bold">
                <x-heroicon-s-arrow-left class="w-3 h-3" />
                Kembali ke Rekap Cabang
            </button>
        </div>
        @endif

        {{-- 3. Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative flex flex-col">
            <div wire:loading.flex wire:target="search,monthFilter,regionFilter,branchFilter,statusFilter,quickFilter,resetFilters,sortBy,drillDownBranch,backToSummary,viewMode"
                 class="absolute inset-0 bg-base-100/60 backdrop-blur-[1px] z-30 items-center justify-center">
                <div class="flex items-center gap-2 bg-base-100 px-4 py-2.5 rounded-2xl shadow-lg border border-base-300">
                    <span class="loading loading-spinner loading-sm text-primary"></span>
                    <span class="text-xs font-bold text-base-content/70">Memuat data referensi number...</span>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
                {{-- TABEL MODE 1: REKAP DISTRIBUTOR (SUMMARY) --}}
                @if ($viewMode === 'summary')
                <table class="table table-sm table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm select-none">
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th wire:click="sortBy('region_name')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Region</span>
                                    @if($sortField === 'region_name')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('area_name')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Area</span>
                                    @if($sortField === 'area_name')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('branch_code')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Branch Code</span>
                                    @if($sortField === 'branch_code')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('branch_name')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Distributor Name</span>
                                    @if($sortField === 'branch_name')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('total_invoices')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th border-l border-base-content/10">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Total Nota</span>
                                    @if($sortField === 'total_invoices')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('invoices_with_ord')" class="text-right cursor-pointer bg-success/10 text-success hover:bg-success/20 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Ada No Order</span>
                                    @if($sortField === 'invoices_with_ord')
                                        <span class="text-success font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-success/40 group-hover/th:text-success text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('invoices_without_ord')" class="text-right cursor-pointer bg-warning/10 text-warning hover:bg-warning/20 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Tanpa No Order</span>
                                    @if($sortField === 'invoices_without_ord')
                                        <span class="text-warning font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-warning/40 group-hover/th:text-warning text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th class="text-center w-24 border-l border-base-300">% Kepatuhan</th>
                            <th wire:click="sortBy('disc_without_ord_inv')" class="text-right cursor-pointer bg-error/10 text-error hover:bg-error/20 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Diskon Tanpa Order</span>
                                    @if($sortField === 'disc_without_ord_inv')
                                        <span class="text-error font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-error/40 group-hover/th:text-error text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('disc4_val_without_ord')" class="text-right cursor-pointer bg-rose-500/10 text-rose-600 hover:bg-rose-500/20 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Disc 4 (Rp)</span>
                                    @if($sortField === 'disc4_val_without_ord')
                                        <span class="text-rose-600 font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-rose-400 group-hover/th:text-rose-600 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('disc8_qty_without_ord')" class="text-right cursor-pointer bg-amber-500/10 text-amber-700 hover:bg-amber-500/20 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Disc 8 (Qty)</span>
                                    @if($sortField === 'disc8_qty_without_ord' || $sortField === 'disc8_val_without_ord')
                                        <span class="text-amber-700 font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-amber-500 group-hover/th:text-amber-700 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th class="text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-base-200">
                        @forelse ($rows as $row)
                            @php
                                $totI  = (int) ($row->total_invoices ?? 0);
                                $wOrd  = (int) ($row->invoices_with_ord ?? 0);
                                $noOrd = (int) ($row->invoices_without_ord ?? 0);
                                $critI = (int) ($row->disc_without_ord_inv ?? 0);
                                $pctK  = $totI > 0 ? round(($wOrd / $totI) * 100, 1) : 0;
                                $isCritical = ($totI > 0 && $critI > 0);
                                $isNotPulled = ($totI === 0);
                                $rowBg = $isCritical ? 'bg-error/[0.04] hover:bg-error/[0.09]' : ($loop->even ? 'bg-base-200/25 hover:bg-base-200/60' : 'bg-base-100 hover:bg-base-200/50');
                            @endphp
                            <tr class="{{ $rowBg }} transition-colors group text-sm">
                                <td class="text-center text-base-content/50 font-medium {{ $isCritical ? 'border-l-4 border-l-error' : '' }}">
                                    {{ $rows->firstItem() + $loop->index }}
                                </td>
                                <td><div class="font-bold text-base-content/80">{{ $row->region_name }}</div></td>
                                <td><div class="text-base-content/70">{{ $row->area_name }}</div></td>
                                <td><span class="font-mono text-[11px] font-semibold px-1.5 py-0.5 rounded bg-base-200 text-base-content/70 uppercase tracking-wider">{{ $row->branch_code }}</span></td>
                                <td>
                                    <span class="font-semibold {{ $isCritical ? 'text-error font-bold' : ($isNotPulled ? 'text-base-content/60' : 'text-base-content/80') }}">{{ $row->branch_name }}</span>
                                </td>
                                <td class="text-right font-mono font-bold border-l border-base-200 {{ $isNotPulled ? 'text-base-content/30' : 'text-base-content/80' }}">
                                    {{ number_format($totI, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-medium border-l border-base-200 {{ $isNotPulled ? 'text-base-content/25' : 'text-success' }}">
                                    {{ $isNotPulled ? '-' : number_format($wOrd, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-medium border-l border-base-200 {{ $isNotPulled ? 'text-base-content/25' : 'text-warning' }}">
                                    {{ $isNotPulled ? '-' : number_format($noOrd, 0, ',', '.') }}
                                </td>
                                <td class="text-center font-mono border-l border-base-200">
                                    @if($isNotPulled)
                                        <span class="badge badge-sm bg-base-200 text-base-content/40 border-0 font-medium">
                                            Belum Tarik
                                        </span>
                                    @else
                                        <span class="badge badge-sm font-bold {{ $pctK >= 90 ? 'badge-success text-white' : ($pctK >= 60 ? 'badge-warning text-warning-content' : 'badge-error text-white') }}">
                                            {{ $pctK }}%
                                        </span>
                                    @endif
                                </td>
                                <td class="text-right font-mono font-bold border-l border-base-200 {{ $isNotPulled ? 'text-base-content/25' : ($critI > 0 ? 'text-error bg-error/10' : 'text-base-content/30') }}">
                                    {{ $isNotPulled ? '-' : number_format($critI, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-bold border-l border-base-200 {{ $isNotPulled ? 'text-base-content/25' : ((float)$row->disc4_val_without_ord > 0 ? 'text-rose-600 bg-rose-50/40' : 'text-base-content/30') }}">
                                    {{ $isNotPulled ? '-' : number_format((float)$row->disc4_val_without_ord, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-bold border-l border-base-200 {{ $isNotPulled ? 'text-base-content/25' : ((float)($row->disc8_qty_without_ord ?? $row->disc8_val_without_ord) > 0 ? 'text-amber-700 bg-amber-50/40' : 'text-base-content/30') }}">
                                    {{ $isNotPulled ? '-' : number_format((float)($row->disc8_qty_without_ord ?? $row->disc8_val_without_ord), 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($isNotPulled)
                                        <span class="text-[11px] text-base-content/35 font-medium italic">No Data</span>
                                    @else
                                        <button type="button" wire:click="drillDownBranch('{{ $row->branch_code }}')"
                                                class="btn btn-xs btn-outline btn-primary rounded-lg normal-case gap-1 font-bold">
                                            <x-heroicon-s-magnifying-glass class="w-3 h-3" />
                                            Detail
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-12 text-base-content/40">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-heroicon-o-inbox class="w-10 h-10 text-base-content/20" />
                                        <span class="font-semibold text-xs">Tidak ada data rekap referensi yang ditemukan untuk kriteria filter ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- Sticky Grand Total Footer --}}
                    @if ($rows->count() > 0 && $totals)
                    <tfoot class="bg-base-300 font-bold text-xs sticky bottom-0 z-20 shadow-[inset_0_1px_0_rgba(0,0,0,0.12)]">
                        <tr>
                            <td colspan="5" class="text-right uppercase tracking-wider text-base-content/70 pr-4 py-2.5">
                                GRAND TOTAL ({{ number_format($rows->total()) }} CABANG)
                            </td>
                            <td class="text-right font-mono py-2.5 border-l border-base-content/10">{{ number_format($totals->total_invoices ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 text-success border-l border-base-300">{{ number_format($totals->invoices_with_ord ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 text-warning border-l border-base-300">{{ number_format($totals->invoices_without_ord ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center font-mono py-2.5 border-l border-base-300">
                                @php
                                    $gtTot = (int)($totals->total_invoices ?? 0);
                                    $gtWith = (int)($totals->invoices_with_ord ?? 0);
                                    $gtPct = $gtTot > 0 ? round(($gtWith / $gtTot) * 100, 1) : 0;
                                @endphp
                                <span class="badge badge-sm font-bold {{ $gtPct >= 90 ? 'badge-success text-white' : ($gtPct >= 60 ? 'badge-warning text-warning-content' : 'badge-error text-white') }}">
                                    {{ $gtPct }}%
                                </span>
                            </td>
                            <td class="text-right font-mono py-2.5 text-error bg-error/10 border-l border-base-300">{{ number_format($totals->disc_without_ord_inv ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 text-rose-600 bg-rose-50/40 border-l border-base-300 font-bold">{{ number_format((float)($totals->disc4_val_without_ord ?? 0), 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 text-amber-700 bg-amber-50/40 border-l border-base-300 font-bold">{{ number_format((float)($totals->disc8_qty_without_ord ?? $totals->disc8_val_without_ord ?? 0), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>

                {{-- TABEL MODE 2: DETAIL TRANSAKSI --}}
                @else
                <table class="table table-sm table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm select-none">
                        <tr>
                            <th class="w-10 text-center">No</th>
                            <th wire:click="sortBy('region_name')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Region</span>
                                    @if($sortField === 'region_name')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('branch_name')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Cabang</span>
                                    @if($sortField === 'branch_name')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('invoice_date')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Tgl Invoice</span>
                                    @if($sortField === 'invoice_date')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('invoice_no')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>No Invoice</span>
                                    @if($sortField === 'invoice_no')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th class="text-center w-14">Tipe</th>
                            <th wire:click="sortBy('ord_no')" class="cursor-pointer hover:bg-base-200 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center gap-1">
                                    <span>No Order (Referensi)</span>
                                    @if($sortField === 'ord_no')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th>Customer (Toko)</th>
                            <th>Produk</th>
                            <th wire:click="sortBy('qty3_pcs')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th border-l border-base-200">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Qty (Pcs)</span>
                                    @if($sortField === 'qty3_pcs')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('line_discount_4')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th border-l border-base-200">
                                <div class="flex items-center justify-end gap-1">
                                    <span>LD 4 (Rp)</span>
                                    @if($sortField === 'line_discount_4')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('line_discount_8')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th border-l border-base-200">
                                <div class="flex items-center justify-end gap-1">
                                    <span>LD 8 (Qty)</span>
                                    @if($sortField === 'line_discount_8')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('nett_amount')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th border-l border-base-200">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Nett (Rp)</span>
                                    @if($sortField === 'nett_amount')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th class="text-center w-36 border-l border-base-300">Status Klaim</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-base-200">
                        @forelse ($rows as $row)
                            @php
                                $hasDisc = ((float)($row->line_discount_4 ?? 0) != 0 || (float)($row->line_discount_8 ?? 0) != 0);
                                $isOrdEmpty = (empty($row->ord_no) || in_array(trim((string)$row->ord_no), ['', '0', '-']));
                                $isCritical = ($hasDisc && $isOrdEmpty);
                                $rowBg = $isCritical ? 'bg-error/[0.06] hover:bg-error/[0.11]' : ($loop->even ? 'bg-base-200/25 hover:bg-base-200/60' : 'bg-base-100 hover:bg-base-200/50');
                            @endphp
                            <tr class="{{ $rowBg }} transition-colors group text-sm">
                                <td class="text-center text-base-content/50 font-medium {{ $isCritical ? 'border-l-4 border-l-error' : '' }}">
                                    {{ $rows->firstItem() + $loop->index }}
                                </td>
                                <td><div class="font-bold text-base-content/80">{{ $row->region_name }}</div></td>
                                <td>
                                    <div class="font-semibold text-base-content/80">{{ $row->branch_name }}</div>
                                    <div class="font-mono text-[10px] text-base-content/50">{{ $row->branch_code }}</div>
                                </td>
                                <td class="font-mono text-xs">{{ $row->invoice_date }}</td>
                                <td class="font-mono text-xs font-semibold text-base-content/90">{{ $row->invoice_no }}</td>
                                <td class="text-center">
                                    <span class="badge badge-xs {{ $row->invoice_type === 'RET' ? 'badge-error text-white' : 'badge-ghost' }} font-bold">
                                        {{ $row->invoice_type ?? 'INV' }}
                                    </span>
                                </td>
                                <td class="border-l border-base-200">
                                    @if ($isOrdEmpty)
                                        <span class="badge badge-error badge-outline badge-sm font-bold text-[10px] gap-1">
                                            <x-heroicon-s-x-mark class="w-3 h-3" /> KOSONG
                                        </span>
                                    @else
                                        <span class="font-mono text-xs font-semibold text-success">{{ $row->ord_no }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-medium text-xs text-base-content/90 truncate max-w-[200px]" title="{{ $row->cust_name }}">{{ $row->cust_name }}</div>
                                    <div class="font-mono text-[10px] text-base-content/40">{{ $row->cust_code_dist }}</div>
                                </td>
                                <td>
                                    <div class="font-medium text-xs text-base-content/90 truncate max-w-[220px]" title="{{ $row->prd_name }}">{{ $row->prd_name }}</div>
                                    <div class="font-mono text-[10px] text-base-content/40">{{ $row->prd_code }}</div>
                                </td>
                                <td class="text-right font-mono font-medium border-l border-base-200">
                                    {{ number_format((float)$row->qty3_pcs, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-medium border-l border-base-200 {{ (float)$row->line_discount_4 != 0 && $isOrdEmpty ? 'text-error font-bold bg-error/10' : 'text-base-content/70' }}">
                                    {{ number_format((float)$row->line_discount_4, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-medium border-l border-base-200 {{ (float)$row->line_discount_8 != 0 && $isOrdEmpty ? 'text-amber-700 font-bold bg-amber-50/40' : 'text-base-content/70' }}">
                                    {{ (float)$row->line_discount_8 != 0 ? number_format((float)$row->line_discount_8, 0, ',', '.') . ' pcs' : '-' }}
                                </td>
                                <td class="text-right font-mono font-bold border-l border-base-200 text-base-content/80">
                                    {{ number_format((float)$row->nett_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center border-l border-base-300">
                                    @if ($isCritical)
                                        <span class="badge badge-error badge-sm text-white font-bold px-2.5 shadow-xs">KRITIS KLAIM</span>
                                    @elseif ($isOrdEmpty)
                                        <span class="badge badge-warning badge-sm text-warning-content font-bold px-2">TANPA ORDER</span>
                                    @else
                                        <span class="badge badge-success badge-sm text-white font-bold px-2.5">LENGKAP</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-12 text-base-content/40">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-heroicon-o-inbox class="w-10 h-10 text-base-content/20" />
                                        <span class="font-semibold text-xs">Tidak ada rincian data transaksi yang ditemukan.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- Sticky Grand Total Detail --}}
                    @if ($rows->count() > 0 && $totals)
                    <tfoot class="bg-base-300 font-bold text-xs sticky bottom-0 z-20 shadow-[inset_0_1px_0_rgba(0,0,0,0.12)]">
                        <tr>
                            <td colspan="9" class="text-right uppercase tracking-wider text-base-content/70 pr-4 py-2.5">
                                TOTAL TRANSAKSI ({{ number_format($rows->total()) }} BARIS)
                            </td>
                            <td class="text-right font-mono py-2.5 border-l border-base-content/10">{{ number_format($totals->total_qty_pcs ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 border-l border-base-content/10 text-rose-600 font-bold bg-rose-50/20">{{ number_format($totals->total_line_discount_4 ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 border-l border-base-content/10 text-amber-700 font-bold bg-amber-50/20">{{ number_format($totals->total_line_discount_8 ?? 0, 0, ',', '.') }} pcs</td>
                            <td class="text-right font-mono py-2.5 border-l border-base-content/10 font-bold">{{ number_format($totals->total_nett_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="border-l border-base-300"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
                @endif
            </div>
        </div>

        {{-- 4. Status Bar & Pagination Footer --}}
        <div class="px-4 py-2.5 border-t border-base-300 bg-base-200/40 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-base-content/60">
            <div class="flex flex-wrap items-center gap-2">
                <span>
                    Menampilkan <strong class="text-base-content">{{ $rows->count() }}</strong> dari <strong class="text-base-content">{{ $rows->total() }}</strong> {{ $viewMode === 'summary' ? 'cabang' : 'transaksi' }} terfilter
                </span>
                <span class="hidden sm:inline text-base-content/30">|</span>
                <span class="badge badge-ghost badge-sm font-medium text-[11px] gap-1">
                    <x-heroicon-s-shield-check class="w-3 h-3 text-info" />
                    Klaim Valid: No Order Wajib Terisi saat ada Line Discount 4 / 8
                </span>
            </div>

            @if ($rows->hasPages())
                <div>
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
