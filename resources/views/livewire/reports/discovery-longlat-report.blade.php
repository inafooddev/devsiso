<div class="flex h-full min-h-0 flex-col gap-4 md:gap-5 p-4 md:p-5 lg:p-6 pb-24 overflow-hidden relative">

    {{-- Top Tier KPI Cards: Analisis Kualitas, Duplikasi & Anomali Depo --}}
    @if($isReady)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4 shrink-0">
        {{-- Total Penemuan Titik --}}
        <div wire:key="kpi-total-found" class="bg-base-100 p-3 md:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-14 h-14 rounded-full bg-base-content/5 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-[10px] md:text-xs font-bold text-base-content/60 uppercase tracking-wider">Total Penemuan</span>
                <div class="w-7 h-7 rounded-lg bg-base-200 flex items-center justify-center text-base-content/70">
                    <x-heroicon-s-map-pin class="w-4 h-4" />
                </div>
            </div>
            <div class="text-xl md:text-2xl font-black leading-none mt-2 text-base-content relative z-10">
                {{ number_format($kpiStats['total_found'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-base-content/50 mt-1 truncate">Kunjungan sales tercatat</span>
        </div>

        {{-- Titik Clean (Unik, di Luar Depo) --}}
        <div wire:key="kpi-total-clean" class="bg-emerald-500/10 border border-emerald-500/30 p-3 md:p-4 rounded-xl shadow-sm flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-14 h-14 rounded-full bg-emerald-500/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-[10px] md:text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Clean (Siap Update)</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <x-heroicon-s-check-badge class="w-4 h-4" />
                </div>
            </div>
            <div class="text-xl md:text-2xl font-black leading-none mt-2 text-emerald-600 dark:text-emerald-400 relative z-10">
                {{ number_format($kpiStats['total_clean'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-emerald-700/70 dark:text-emerald-400/70 font-medium mt-1 truncate">100% Unik &amp; Luar Depo</span>
        </div>

        {{-- Titik di Distributor / Depo (<= 100 Meter) --}}
        <div wire:key="kpi-total-at-distributor" class="bg-purple-500/10 border border-purple-500/30 p-3 md:p-4 rounded-xl shadow-sm flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-14 h-14 rounded-full bg-purple-500/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-[10px] md:text-xs font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider">Di Depo (&le;100m)</span>
                <div class="w-7 h-7 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <x-heroicon-s-building-office-2 class="w-4 h-4" />
                </div>
            </div>
            <div class="text-xl md:text-2xl font-black leading-none mt-2 text-purple-600 dark:text-purple-400 relative z-10">
                {{ number_format($kpiStats['total_at_distributor'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-purple-700/70 dark:text-purple-400/70 font-medium mt-1 truncate">Anomali check-in di depo</span>
        </div>

        {{-- Duplikat Antar Kunjungan Sales --}}
        <div wire:key="kpi-total-dup-visit" class="bg-amber-500/10 border border-amber-500/30 p-3 md:p-4 rounded-xl shadow-sm flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-14 h-14 rounded-full bg-amber-500/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-[10px] md:text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Dup Kunjungan</span>
                <div class="w-7 h-7 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-xl md:text-2xl font-black leading-none mt-2 text-amber-600 dark:text-amber-400 relative z-10">
                {{ number_format($kpiStats['total_dup_visit'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-amber-700/70 dark:text-amber-400/70 font-medium mt-1 truncate">Titik sama &gt; 1 toko</span>
        </div>

        {{-- Duplikat Terhadap Master Customer --}}
        <div wire:key="kpi-total-dup-master" class="bg-rose-500/10 border border-rose-500/30 p-3 md:p-4 rounded-xl shadow-sm flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-14 h-14 rounded-full bg-rose-500/10 transition-transform group-hover:scale-150"></div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-[10px] md:text-xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Dup Master</span>
                <div class="w-7 h-7 rounded-lg bg-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-400">
                    <x-heroicon-s-x-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="text-xl md:text-2xl font-black leading-none mt-2 text-rose-600 dark:text-rose-400 relative z-10">
                {{ number_format($kpiStats['total_dup_master'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-rose-700/70 dark:text-rose-400/70 font-medium mt-1 truncate">Titik bentrok master lain</span>
        </div>
    </div>
    @endif

    {{-- KPI Cards Breakdown per Region --}}
    @if($isReady && count($summaryData) > 0)
    <div class="flex flex-nowrap overflow-x-auto gap-3 shrink-0 pb-1 w-full [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        @foreach($summaryData as $row)
        <div wire:key="kpi-region-{{ $row->region_code ?? 'null' }}" 
             class="flex-1 min-w-[130px] md:min-w-[150px] bg-base-100 p-2.5 md:p-3 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group shrink-0">
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] font-bold text-base-content/60 uppercase tracking-wider truncate pr-1">{{ $row->region_code ?? 'NO REGION' }}</h3>
                <span class="badge badge-xs badge-success text-[9px] text-white font-bold">Clean</span>
            </div>
            <div class="text-base md:text-lg font-bold leading-none mt-1.5 truncate relative z-10 text-primary">
                {{ number_format($row->total, 0, ',', '.') }}
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Main Card (Tabel & Filter) --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Actions Toolbar --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-wrap items-center justify-between gap-2.5 bg-base-200/30">
            <div class="flex flex-wrap items-center gap-2 md:gap-2.5 flex-1 min-w-0">
                {{-- Filter Region --}}
                <select wire:key="filter-select-region" wire:model.live="selectedRegion" class="select select-sm select-bordered bg-base-100 w-full sm:w-auto">
                    <option value="">-- Semua Region --</option>
                    @foreach($regions as $r)
                        <option wire:key="opt-region-{{ $r->region_code }}" value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Area --}}
                <select wire:key="filter-select-area-{{ $selectedRegion ?: 'none' }}" wire:model="selectedArea" class="select select-sm select-bordered bg-base-100 w-full sm:w-auto" @if(!$selectedRegion) disabled @endif>
                    <option value="">-- Semua Area --</option>
                    @foreach($areas as $a)
                        <option wire:key="opt-area-{{ $a->area_code }}" value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                    @endforeach
                </select>

                {{-- Filter Status Koordinat --}}
                <select wire:key="filter-select-status" wire:model.live="filterStatus" class="select select-sm select-bordered bg-base-100 font-medium w-full sm:w-auto">
                    <option value="clean">✨ Hanya Titik Clean (Unik &amp; Luar Depo)</option>
                    <option value="all">🌐 Semua Hasil (Termasuk Anomali)</option>
                    <option value="at_distributor">🏢 Hanya Titik di Depo (&le; 100m)</option>
                    <option value="dup_visit">⚠️ Hanya Duplikat Kunjungan</option>
                    <option value="dup_master">❌ Hanya Duplikat Master Customer</option>
                    <option value="dup_all">🔍 Semua Jenis Anomali (Audit Lengkap)</option>
                </select>

                {{-- Smart Filter Range Tanggal (1 s.d 6 Bulan) --}}
                <div wire:key="container-smart-date-filter" class="flex flex-wrap items-center gap-1.5 bg-base-100 p-1 rounded-xl border border-base-300 shadow-2xs">
                    <span class="text-xs font-semibold text-base-content/70 pl-1.5 flex items-center gap-1">
                        <x-heroicon-m-calendar-days class="w-4 h-4 text-primary" />
                        <span class="hidden sm:inline">Periode:</span>
                    </span>

                    <select wire:key="filter-select-date-preset" wire:model.live="datePreset" class="select select-xs select-ghost font-medium focus:outline-none pl-1 pr-6 text-xs text-base-content">
                        @foreach($datePresets as $key => $label)
                            <option wire:key="opt-date-preset-{{ $key }}" value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <div class="hidden sm:block w-px h-4 bg-base-300"></div>

                    <div class="flex items-center gap-1">
                        <input type="date" 
                               wire:key="input-start-date"
                               wire:model.live="startDate" 
                               class="input input-xs bg-transparent border-none text-xs w-[108px] focus:outline-none p-0 text-center font-medium font-mono" 
                               title="Tanggal Mulai">
                        <span class="text-xs text-base-content/40 font-semibold">s/d</span>
                        <input type="date" 
                               wire:key="input-end-date"
                               wire:model.live="endDate" 
                               class="input input-xs bg-transparent border-none text-xs w-[108px] focus:outline-none p-0 text-center font-medium font-mono" 
                               title="Tanggal Akhir">
                        
                        <div class="tooltip tooltip-bottom" data-tip="{{ $durationLabel }} (Batas maksimal 6 bulan)">
                            <span class="badge badge-xs {{ ($diffDays ?? 0) > 186 ? 'badge-error text-white font-bold' : (($diffDays ?? 0) >= 150 ? 'badge-warning text-black font-semibold' : 'badge-ghost text-base-content/70') }} font-mono px-1.5 cursor-default">
                                {{ $diffDays ?? 0 }}h
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                {{-- Tombol Cari --}}
                <button wire:click="processData" wire:loading.attr="disabled" class="btn btn-sm btn-primary rounded-xl">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                    Cari
                </button>

                {{-- Tombol Reset --}}
                <button wire:click="resetFilter" wire:loading.attr="disabled" class="btn btn-sm btn-ghost rounded-xl text-base-content/70" title="Reset semua filter">
                    <x-heroicon-s-arrow-path class="w-4 h-4" />
                    Reset
                </button>
                
                <div class="hidden sm:block w-px h-6 bg-base-300 mx-0.5"></div>
                
                {{-- Tombol Download Excel --}}
                <button wire:click="downloadExcel" wire:loading.attr="disabled" @if(!$isReady || !empty($isRangeTooLong)) disabled @endif class="btn btn-sm btn-success rounded-xl text-white">
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                    Download Excel
                </button>
            </div>
        </div>

        {{-- Loading State --}}
        <div wire:loading wire:target="processData, downloadExcel, selectedRegion, filterStatus, resetFilter, datePreset" class="p-2.5 bg-info/10 text-info text-center font-bold text-xs uppercase tracking-wider border-b border-info/20">
            Sedang memproses pengecekan duplikasi &amp; radius depo... Mohon tunggu.
        </div>
        
        {{-- Range Warning State --}}
        @if(!empty($isRangeTooLong))
        <div class="p-3 bg-warning/15 text-warning-content border-b border-warning/30 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2">
                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-warning shrink-0" />
                <span>
                    <strong>Rentang tanggal terlalu panjang ({{ $diffDays }} hari).</strong>
                    Sistem membatasi maksimal <strong>6 bulan (186 hari)</strong> untuk menjaga performa. Silakan pilih preset 1 s.d 6 bulan atau perpendek rentang tanggal.
                </span>
            </div>
            <button wire:click="applyDatePreset('6_months')" class="btn btn-xs btn-warning text-black font-semibold shrink-0">
                Pilih 6 Bulan Terakhir
            </button>
        </div>
        @endif
        
        {{-- Error State --}}
        @error('startDate') <div class="p-2 bg-error/10 text-error text-center text-xs font-bold">{{ $message }}</div> @enderror
        @error('endDate') <div class="p-2 bg-error/10 text-error text-center text-xs font-bold">{{ $message }}</div> @enderror

        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative transition-all duration-300" 
             wire:loading.class="opacity-50 pointer-events-none" 
             wire:target="processData, gotoPage, nextPage, previousPage, selectedRegion, filterStatus, resetFilter, datePreset">
            @if($isReady)
                @if(count($data) > 0)
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="sticky top-0 z-20 text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th class="w-14 pl-4 bg-base-300">No</th>
                            <th class="bg-base-300">Status Titik</th>
                            <th class="bg-base-300">Keterangan / Detail Anomali</th>
                            <th class="bg-base-300">Region / Area</th>
                            <th class="bg-base-300">Cabang</th>
                            <th class="bg-base-300">Cust No</th>
                            <th class="bg-base-300">Cust Name</th>
                            <th class="bg-base-300">Titik Latitude</th>
                            <th class="bg-base-300">Titik Longitude</th>
                            <th class="bg-base-300">Waktu Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($data as $index => $row)
                        <tr wire:key="row-coord-{{ $row->kodecabang }}-{{ $row->custno }}" class="hover:bg-base-200/50 transition-colors">
                            <th class="pl-4 font-mono text-xs text-base-content/60">{{ $data->firstItem() + $index }}</th>
                            <td>
                                @if(($row->status_clean ?? '') === 'CLEAN')
                                    <span class="badge badge-sm badge-success gap-1 text-white font-semibold">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                        Clean (Unik)
                                    </span>
                                @elseif(($row->status_clean ?? '') === 'AT_DISTRIBUTOR')
                                    <span class="badge badge-sm badge-secondary gap-1 text-white font-semibold">
                                        <x-heroicon-s-building-office-2 class="w-3.5 h-3.5" />
                                        Di Depo (&le;100m)
                                    </span>
                                @elseif(($row->status_clean ?? '') === 'DUP_VISIT')
                                    <span class="badge badge-sm badge-warning gap-1 text-black font-semibold">
                                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                        Dup Kunjungan
                                    </span>
                                @elseif(($row->status_clean ?? '') === 'DUP_MASTER')
                                    <span class="badge badge-sm badge-error gap-1 text-white font-semibold">
                                        <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                        Dup Master
                                    </span>
                                @else
                                    <span class="badge badge-sm badge-error gap-1 text-white font-semibold">
                                        <x-heroicon-s-x-circle class="w-3.5 h-3.5" />
                                        Dup Ganda
                                    </span>
                                @endif
                            </td>
                            <td class="text-xs">
                                @if(($row->status_clean ?? '') === 'CLEAN')
                                    <span class="text-success font-medium">Bebas duplikat &amp; luar depo (siap update)</span>
                                @elseif(($row->status_clean ?? '') === 'AT_DISTRIBUTOR')
                                    <span class="text-secondary font-medium">
                                        {{ $row->dist_to_depo_meters }}m dari {{ $row->near_dist_name ?? 'Gudang/Depo' }}
                                    </span>
                                @elseif(($row->status_clean ?? '') === 'DUP_VISIT')
                                    <span class="text-warning font-medium">Titik sama dengan {{ $row->visit_coord_count }} toko di kunjungan</span>
                                @elseif(($row->status_clean ?? '') === 'DUP_MASTER')
                                    <span class="text-error font-medium">Sudah dipakai toko lain di master customer</span>
                                @else
                                    <span class="text-error font-medium">Duplikat kunjungan ({{ $row->visit_coord_count }} toko) &amp; master</span>
                                @endif
                            </td>
                            <td class="text-xs">
                                <span class="font-bold block">{{ $row->region_code ?? '-' }}</span>
                                <span class="text-base-content/60">{{ $row->area_code ?? '-' }}</span>
                            </td>
                            <td class="font-mono text-xs">{{ $row->kodecabang }}</td>
                            <td class="font-mono text-xs text-base-content/70">{{ $row->custno }}</td>
                            <td class="font-bold max-w-[200px] truncate" title="{{ $row->custname }}">{{ $row->custname }}</td>
                            <td class="font-mono text-xs font-bold {{ ($row->status_clean ?? '') === 'CLEAN' ? 'text-success' : 'text-base-content/60' }}">{{ $row->v_la }}</td>
                            <td class="font-mono text-xs font-bold {{ ($row->status_clean ?? '') === 'CLEAN' ? 'text-success' : 'text-base-content/60' }}">{{ $row->v_lg }}</td>
                            <td class="text-xs">{{ \Carbon\Carbon::parse($row->visit_date)->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="flex flex-col items-center justify-center h-full p-10 text-base-content/50">
                    <x-heroicon-o-face-frown class="w-12 h-12 mb-3 opacity-30" />
                    <p class="font-semibold">Nihil!</p>
                    <p class="text-sm">Tidak ditemukan data penemuan koordinat dengan filter yang dipilih.</p>
                </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center h-full p-10 text-base-content/50">
                    <x-heroicon-o-map class="w-16 h-16 mb-4 opacity-20" />
                    <p class="font-semibold">Siap Menemukan Koordinat Hilang</p>
                    <p class="text-sm">Tentukan filter dan tanggal, lalu klik "Cari" untuk memuat data.</p>
                </div>
            @endif
        </div>
        
        {{-- Footer Card (Pagination & Summary) --}}
        @if($isReady && count($data) > 0)
        <div class="p-3 md:p-4 border-t border-base-300 shrink-0 bg-base-200/50 flex flex-col sm:flex-row justify-between items-center gap-3">
            <span class="text-xs text-base-content/70">
                Menampilkan <strong>{{ $data->firstItem() }}</strong> - <strong>{{ $data->lastItem() }}</strong> dari <strong>{{ number_format($data->total(), 0, ',', '.') }}</strong> toko
                @if($filterStatus === 'clean')
                    <span class="badge badge-xs badge-success text-white ml-1">Semua Unik &amp; Luar Depo</span>
                @elseif($filterStatus === 'at_distributor')
                    <span class="badge badge-xs badge-secondary text-white ml-1">Anomali Depo &le;100m</span>
                @endif
            </span>
            <div>
                {{ $data->links() }}
            </div>
        </div>
        @endif

    </div>
</div>
