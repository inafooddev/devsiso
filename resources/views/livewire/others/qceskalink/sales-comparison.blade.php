<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 w-full h-full">
    <x-slot name="title">Komparasi Sales (Eska vs SID)</x-slot>

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
        $totalBranch = (int) ($summary->total_branch ?? 0);
        $sudahTarik  = (int) ($summary->net_siso_non_zero ?? 0);
        $belumTarik  = (int) ($summary->net_siso_zero ?? 0);
        $totalOk     = (int) ($summary->total_ok ?? 0);
        $totalNotOk  = (int) ($summary->total_not_ok ?? 0);

        $pctSudah = $totalBranch > 0 ? round(($sudahTarik / $totalBranch) * 100) : 0;
        $pctBelum = $totalBranch > 0 ? round(($belumTarik / $totalBranch) * 100) : 0;
        $pctOk    = $sudahTarik > 0 ? round(($totalOk / $sudahTarik) * 100) : 0;
        $pctNotOk = $sudahTarik > 0 ? round(($totalNotOk / $sudahTarik) * 100) : 0;
    @endphp

    {{-- 1. Summary Section (5 Kartu KPI Interaktif + Persentase Progress) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 shrink-0">
        {{-- Card 1: Total Depo --}}
        <button type="button" wire:click="quickFilter('ALL', 'ALL')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $implementasiFilter === 'ALL' && $statusFilter === 'ALL' ? 'border-primary ring-2 ring-primary/30' : 'border-base-300' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-base-content/50">Total Depo</p>
                    <p class="text-2xl font-black text-base-content mt-0.5">{{ number_format($totalBranch) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-primary/10 text-primary group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-home-modern class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/50 font-medium">
                <span>Cabang Implementasi</span>
                <span class="badge badge-xs badge-ghost font-bold">100%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden">
                <div class="bg-primary h-full rounded-full" style="width: 100%"></div>
            </div>
        </button>

        {{-- Card 2: Sudah Tarik Data --}}
        <button type="button" wire:click="quickFilter('Y', 'ALL')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $implementasiFilter === 'Y' && $statusFilter === 'ALL' ? 'border-success ring-2 ring-success/30' : 'border-success/25' }}">
            <div class="absolute inset-0 bg-success/[0.03]"></div>
            <div class="flex items-start justify-between gap-2 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-success/80">Sudah Tarik SO</p>
                    <p class="text-2xl font-black text-success mt-0.5">{{ number_format($sudahTarik) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-success/10 text-success group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-cloud-arrow-down class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/60 font-medium relative z-10">
                <span>Progres Tarik Data</span>
                <span class="badge badge-xs badge-success text-white font-bold">{{ $pctSudah }}%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden relative z-10">
                <div class="bg-success h-full rounded-full transition-all duration-500" style="width: {{ $pctSudah }}%"></div>
            </div>
        </button>

        {{-- Card 3: Belum Tarik Data --}}
        <button type="button" wire:click="quickFilter('N', 'ALL')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $implementasiFilter === 'N' ? 'border-warning ring-2 ring-warning/30' : 'border-warning/25' }}">
            <div class="absolute inset-0 bg-warning/[0.03]"></div>
            <div class="flex items-start justify-between gap-2 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-warning/80">Belum Tarik SO</p>
                    <p class="text-2xl font-black text-warning mt-0.5">{{ number_format($belumTarik) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-warning/10 text-warning group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-clock class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/60 font-medium relative z-10">
                <span>Menunggu Penarikan</span>
                <span class="badge badge-xs badge-warning text-warning-content font-bold">{{ $pctBelum }}%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden relative z-10">
                <div class="bg-warning h-full rounded-full transition-all duration-500" style="width: {{ $pctBelum }}%"></div>
            </div>
        </button>

        {{-- Card 4: Status OK (Balance) --}}
        <button type="button" wire:click="quickFilter('Y', 'OK')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $statusFilter === 'OK' ? 'border-info ring-2 ring-info/30' : 'border-info/25' }}">
            <div class="absolute inset-0 bg-info/[0.03]"></div>
            <div class="flex items-start justify-between gap-2 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-info/80">Status OK (Balance)</p>
                    <p class="text-2xl font-black text-info mt-0.5">{{ number_format($totalOk) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-info/10 text-info group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-check-badge class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/60 font-medium relative z-10">
                <span>Selisih &lt; Rp 1.000</span>
                <span class="badge badge-xs badge-info text-white font-bold">{{ $pctOk }}%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden relative z-10">
                <div class="bg-info h-full rounded-full transition-all duration-500" style="width: {{ $pctOk }}%"></div>
            </div>
        </button>

        {{-- Card 5: Total NOT OK (Selisih) --}}
        <button type="button" wire:click="quickFilter('Y', 'NOT_OK')"
                class="text-left bg-base-100 rounded-xl shadow-sm border p-3.5 lg:p-4 transition-all hover:shadow-md group relative overflow-hidden {{ $statusFilter === 'NOT_OK' ? 'border-error ring-2 ring-error/30' : 'border-error/25' }}">
            <div class="absolute inset-0 bg-error/[0.03]"></div>
            <div class="flex items-start justify-between gap-2 relative z-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-error/80">Total NOT OK</p>
                    <p class="text-2xl font-black text-error mt-0.5">{{ number_format($totalNotOk) }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-error/10 text-error group-hover:scale-110 transition-transform shrink-0">
                    <x-heroicon-s-exclamation-triangle class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-[10px] text-base-content/60 font-medium relative z-10">
                <span>Selisih &ge; Rp 1.000</span>
                <span class="badge badge-xs badge-error text-white font-bold">{{ $pctNotOk }}%</span>
            </div>
            <div class="w-full bg-base-200 h-1 rounded-full mt-1 overflow-hidden relative z-10">
                <div class="bg-error h-full rounded-full transition-all duration-500" style="width: {{ $pctNotOk }}%"></div>
            </div>
        </button>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Inline Filters --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-col gap-3 bg-base-200/30">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="shrink-0">
                    <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <x-heroicon-s-chart-bar class="w-5 h-5" />
                        </div>
                        Sales Comparison Dashboard
                    </h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Komparasi data Selling Out sistem ESKA dengan data Sales Invoice sistem SID</p>
                </div>

                {{-- Actions Button: Import & Export --}}
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    @canImport('dashboard.sales-comparison')
                    <button wire:click="$set('isImportModalOpen', true)" class="btn btn-sm btn-info text-white rounded-xl normal-case gap-2 shadow-sm shadow-info/20">
                        <x-heroicon-s-arrow-up-tray class="w-4 h-4" />
                        <span>Import SO Eska</span>
                    </button>
                    @endcanImport

                    <button type="button" wire:click="export"
                            class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20"
                            wire:loading.attr="disabled" wire:target="export">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        <span>Export Excel</span>
                        <span wire:loading wire:target="export" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </div>

            {{-- Inline Filter Bar --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 pt-0.5">
                {{-- Search --}}
                <div class="relative lg:col-span-2">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30">
                        <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Cari distributor, kode cabang, area..."
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
                        @foreach($regionsOption as $r)
                            <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Penarikan Data --}}
                <div>
                    <select wire:model.live="implementasiFilter"
                            class="select select-sm select-bordered w-full rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                        <option value="ALL">Semua Penarikan Data</option>
                        <option value="Y">Sudah Tarik Data SO</option>
                        <option value="N">Belum Tarik Data SO</option>
                    </select>
                </div>

                {{-- Status Selisih + Reset --}}
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="statusFilter"
                            class="select select-sm select-bordered flex-1 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50">
                        <option value="ALL">Semua Status Selisih</option>
                        <option value="OK">Hanya OK (Balance)</option>
                        <option value="NOT_OK">Hanya NOT OK (Selisih)</option>
                    </select>

                    @if($search || $regionFilter || $implementasiFilter !== 'ALL' || $statusFilter !== 'ALL' || $sortField !== 'region_name' || $sortDirection !== 'asc')
                        <button type="button" wire:click="resetFilters"
                                class="btn btn-sm btn-ghost btn-square text-error hover:bg-error/10 rounded-xl shrink-0"
                                title="Reset Filter & Urutan">
                            <x-heroicon-s-arrow-path class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative flex flex-col">
            <div wire:loading.flex wire:target="search,monthFilter,regionFilter,implementasiFilter,statusFilter,quickFilter,resetFilters,sortBy"
                 class="absolute inset-0 bg-base-100/60 backdrop-blur-[1px] z-30 items-center justify-center">
                <div class="flex items-center gap-2 bg-base-100 px-4 py-2.5 rounded-2xl shadow-lg border border-base-300">
                    <span class="loading loading-spinner loading-sm text-primary"></span>
                    <span class="text-xs font-bold text-base-content/70">Memuat komparasi sales...</span>
                </div>
            </div>

            <div class="flex-1 overflow-auto">
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
                            <th wire:click="sortBy('entity_name')" class="cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center gap-1">
                                    <span>Area</span>
                                    @if($sortField === 'entity_name')
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
                                    <span>Branch Name</span>
                                    @if($sortField === 'branch_name')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('qty_pcs')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th border-l border-base-content/10">
                                <div class="flex items-center justify-end gap-1">
                                    <span>QTY3 (Pcs)</span>
                                    @if($sortField === 'qty_pcs')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('ld4')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center justify-end gap-1">
                                    <span>LD 4</span>
                                    @if($sortField === 'ld4')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('bb')" class="text-right cursor-pointer hover:bg-base-200 transition-colors group/th">
                                <div class="flex items-center justify-end gap-1">
                                    <span>LD 8</span>
                                    @if($sortField === 'bb')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/25 group-hover/th:text-base-content/60 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('net_eska')" class="text-right cursor-pointer bg-info/10 text-info hover:bg-info/20 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Netto ESKA</span>
                                    @if($sortField === 'net_eska')
                                        <span class="text-info font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-info/40 group-hover/th:text-info text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('net_siso')" class="text-right cursor-pointer bg-success/10 text-success hover:bg-success/20 transition-colors group/th border-l border-base-300">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Netto SID</span>
                                    @if($sortField === 'net_siso')
                                        <span class="text-success font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-success/40 group-hover/th:text-success text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th wire:click="sortBy('selisih')" class="text-right bg-base-200/80 cursor-pointer hover:bg-base-200 transition-colors group/th border-x border-base-300" title="Klik untuk mengurutkan berdasarkan nilai selisih terbesar/terkecil">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Selisih Netto</span>
                                    @if($sortField === 'selisih')
                                        <span class="text-primary font-black">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    @else
                                        <span class="text-base-content/30 group-hover/th:text-base-content/70 text-[10px]">⇅</span>
                                    @endif
                                </div>
                            </th>
                            <th class="text-center w-32 whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-base-200">
                        @forelse ($comparisons as $row)
                            @php
                                $isBelumTarik = ($row->net_eska ?? 0) == 0;
                                $isNotOk      = !$isBelumTarik && abs($row->selisih) >= 1000;
                                $rowBgClass   = $isNotOk
                                    ? 'bg-error/[0.06] hover:bg-error/[0.11]'
                                    : ($loop->even ? 'bg-base-200/25 hover:bg-base-200/60' : 'bg-base-100 hover:bg-base-200/50');
                            @endphp
                            <tr class="{{ $rowBgClass }} transition-colors group text-sm">
                                <td class="text-center text-base-content/50 font-medium {{ $isNotOk ? 'border-l-4 border-l-error' : '' }}">
                                    {{ $comparisons->firstItem() + $loop->index }}
                                </td>
                                <td><div class="font-bold text-base-content/80">{{ $row->region_name }}</div></td>
                                <td><div class="text-base-content/70">{{ $row->entity_name }}</div></td>
                                <td><span class="font-mono text-[11px] font-semibold px-1.5 py-0.5 rounded bg-base-200 text-base-content/70 uppercase tracking-wider">{{ $row->branch_code }}</span></td>
                                <td><span class="font-semibold {{ $isNotOk ? 'text-error font-bold' : 'text-base-content/80' }}">{{ $row->branch_name }}</span></td>

                                {{-- ESKA Metrics --}}
                                <td class="text-right font-mono font-medium border-l border-base-200 {{ $isBelumTarik ? 'text-base-content/25' : 'text-base-content/70' }}">
                                    {{ number_format($row->qty_pcs ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-medium {{ $isBelumTarik ? 'text-base-content/25' : 'text-base-content/70' }}">
                                    {{ number_format($row->ld4 ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-right font-mono font-medium {{ $isBelumTarik ? 'text-base-content/25' : 'text-base-content/70' }}">
                                    {{ number_format($row->bb ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Netto ESKA (Blue Tint) --}}
                                <td class="text-right font-mono font-semibold border-l border-base-200 {{ $isBelumTarik ? 'bg-base-200/20 text-base-content/30' : 'bg-info/[0.04] text-base-content/90' }}">
                                    {{ number_format($row->net_eska ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Netto SID (Green Tint) --}}
                                <td class="text-right font-mono font-semibold border-l border-base-200 bg-success/[0.04] {{ ($row->net_siso ?? 0) == 0 ? 'text-base-content/30' : 'text-base-content/90' }}">
                                    {{ number_format($row->net_siso ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Selisih Netto --}}
                                <td class="text-right font-mono font-bold border-x border-base-300 {{ $isBelumTarik ? 'bg-base-200/20 text-base-content/30' : ($isNotOk ? 'bg-error/10 text-error' : ($row->selisih != 0 ? 'bg-warning/5 text-warning-content' : 'bg-base-200/10 text-success')) }}">
                                    {{ number_format($row->selisih, 0, ',', '.') }}
                                </td>

                                {{-- Status Badge --}}
                                <td class="text-center">
                                    @if ($isBelumTarik)
                                        <span class="badge badge-warning badge-sm text-warning-content rounded-lg font-bold px-2.5">BELUM TARIK</span>
                                    @elseif ($isNotOk)
                                        <span class="badge badge-error badge-sm text-white rounded-lg font-bold px-3 shadow-xs">NOT OK</span>
                                    @else
                                        <span class="badge badge-success badge-sm text-white rounded-lg font-bold px-3">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-12 text-base-content/40">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-heroicon-o-inbox class="w-10 h-10 text-base-content/20" />
                                        <span class="font-semibold text-xs">Tidak ada data komparasi yang ditemukan untuk kriteria filter ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- Sticky Grand Total Footer --}}
                    @if ($comparisons->count() > 0 && $totals)
                    <tfoot class="bg-base-300 font-bold text-xs sticky bottom-0 z-20 shadow-[inset_0_1px_0_rgba(0,0,0,0.12)]">
                        <tr>
                            <td colspan="5" class="text-right uppercase tracking-wider text-base-content/70 pr-4 py-2.5">
                                GRAND TOTAL ({{ number_format($comparisons->total()) }} CABANG)
                            </td>
                            <td class="text-right font-mono py-2.5 border-l border-base-content/10">{{ number_format($totals->total_qty_pcs ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5">{{ number_format($totals->total_ld4 ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5">{{ number_format($totals->total_bb ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 bg-info/10 text-info border-l border-base-300">{{ number_format($totals->total_net_eska ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 bg-success/10 text-success border-l border-base-300">{{ number_format($totals->total_net_siso ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-mono py-2.5 border-x border-base-300 {{ abs($totals->total_selisih ?? 0) >= 1000 ? 'bg-error/15 text-error' : (($totals->total_selisih ?? 0) != 0 ? 'text-warning' : 'text-success') }}">
                                {{ number_format($totals->total_selisih ?? 0, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- 5. Status Bar & Pagination Footer (Selalu Tampil) --}}
        <div class="px-4 py-2.5 border-t border-base-300 bg-base-200/40 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-base-content/60">
            <div class="flex flex-wrap items-center gap-2">
                <span>
                    Menampilkan <strong class="text-base-content">{{ $comparisons->count() }}</strong> dari <strong class="text-base-content">{{ $comparisons->total() }}</strong> cabang terfilter
                    (Total Depo: <strong class="text-base-content">{{ $totalBranch }}</strong>)
                </span>
                <span class="hidden sm:inline text-base-content/30">|</span>
                <span class="badge badge-ghost badge-sm font-medium text-[11px] gap-1">
                    <x-heroicon-s-scale class="w-3 h-3 text-info" />
                    Batas Wajar Selisih: &lt; Rp 1.000
                </span>
            </div>

            @if ($comparisons->hasPages())
                <div>
                    {{ $comparisons->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ========== MODAL IMPORT ========== --}}
    <div x-data="{ open: @entangle('isImportModalOpen') }"
         x-show="open" x-cloak
         style="z-index: 99999;"
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-base-100/60 backdrop-blur-sm" @click="open = false"></div>

        <div x-show="open"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-base-100 rounded-3xl shadow-2xl border border-base-300 w-full max-w-lg ring-1 ring-base-content/5 text-base-content">

            <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-success/10 text-success">
                        <x-heroicon-s-arrow-up-tray class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">Import Selling Out ESKA</h3>
                        <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Unggah file excel untuk memuat data SO</p>
                    </div>
                </div>
                <button @click="open = false" class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form wire:submit.prevent="import">
                <div class="p-8">
                    <div class="flex flex-col items-center justify-center border-2 border-dashed border-base-300 rounded-3xl p-10 hover:border-success/50 transition-all group bg-base-200/50">
                        <x-heroicon-o-cloud-arrow-up class="w-16 h-16 text-base-content/20 group-hover:text-success/50 transition-colors mb-4" />
                        <label class="btn btn-sm btn-success text-white rounded-xl cursor-pointer">
                            Pilih File Excel
                            <input type="file" wire:model="importFile" class="hidden" accept=".xlsx,.xls">
                        </label>
                        <p class="text-[11px] text-base-content/40 mt-3 font-medium">Format: .xlsx atau .xls (Maks 100MB)</p>
                        
                        @if ($importFile)
                            <div class="mt-6 p-3 bg-success/10 rounded-2xl border border-success/20 flex items-center gap-3 w-full">
                                <x-heroicon-s-document-text class="w-8 h-8 text-success" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-success truncate">{{ $importFile->getClientOriginalName() }}</p>
                                    <p class="text-[10px] text-success/60">{{ number_format($importFile->getSize() / 1024, 2) }} KB</p>
                                </div>
                                <button type="button" wire:click="$set('importFile', null)" class="text-success/40 hover:text-error transition-colors">
                                    <x-heroicon-s-x-circle class="w-5 h-5" />
                                </button>
                            </div>
                        @endif

                        @error('importFile') 
                            <div class="mt-4 p-3 bg-error/10 rounded-2xl border border-error/20 flex items-center gap-3 w-full">
                                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-error" />
                                <p class="text-[10px] font-bold text-error leading-tight">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mt-6 bg-info/5 rounded-2xl p-4 border border-info/20 flex gap-3">
                        <x-heroicon-s-information-circle class="w-5 h-5 text-info shrink-0" />
                        <div class="text-[10px] text-info/70 leading-relaxed">
                            Pastikan format kolom file excel sesuai dengan standar template yang telah ditentukan untuk menghindari kegagalan saat proses import data.
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                    <button type="button" @click="open = false" class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                    <button type="submit" class="btn btn-success rounded-xl px-10 normal-case text-white shadow-sm shadow-success/20 gap-2"
                            wire:loading.attr="disabled" wire:target="import">
                        <span wire:loading.remove wire:target="import">Konfirmasi Import</span>
                        <span wire:loading wire:target="import" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading wire:target="import">Proses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
