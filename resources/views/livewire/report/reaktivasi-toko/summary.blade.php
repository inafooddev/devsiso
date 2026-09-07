<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Report Reaktivasi Toko - Summary</x-slot>

    <x-ui.tab-menu>
        <a href="{{ Route::has('report.reaktivasi-toko.dashboard') ? route('report.reaktivasi-toko.dashboard') : '#' }}" class="tab text-xs md:text-sm h-8 min-h-8 hover:text-primary transition-colors">Dashboard</a>
        <a href="#" class="tab tab-active bg-base-100 shadow-sm text-xs md:text-sm font-bold h-8 min-h-8">Summary</a>
        <a href="{{ Route::has('report.reaktivasi-toko.index') ? route('report.reaktivasi-toko.index') : route('report.reaktivasi-toko') ?? '#' }}" class="tab text-xs md:text-sm h-8 min-h-8 hover:text-primary transition-colors">Detail</a>
    </x-ui.tab-menu>

    {{-- KPI Cards (Terpisah dari Tabel) --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
        <!-- 1. Total Toko -->
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 p-3 md:p-4 flex flex-col">
            <div class="text-[10px] md:text-xs font-semibold text-base-content/60 uppercase tracking-wide">Total Toko</div>
            <div class="text-xl md:text-2xl font-bold text-base-content mt-1">{{ number_format($this->kpiStats['total_toko'], 0, ',', '.') }}</div>
            <div class="text-[9px] xl:text-[10px] mt-2 flex justify-between items-center text-base-content/60">
                <div>Aktif: <span class="font-bold text-success">{{ number_format($this->kpiStats['total_aktif'], 0, ',', '.') }} ({{ $this->kpiStats['pct_aktif'] }}%)</span></div>
                <div>Gap: <span class="font-bold text-error">{{ number_format($this->kpiStats['gap'], 0, ',', '.') }} ({{ $this->kpiStats['pct_gap'] }}%)</span></div>
            </div>
        </div>
        
        <!-- 2. Type SO -->
        <div class="bg-base-100 rounded-xl shadow-xl border border-primary/30 p-3 md:p-4 flex flex-col">
            <div class="text-[10px] md:text-xs font-bold text-primary uppercase tracking-wide">Type SO</div>
            <div class="text-lg md:text-xl font-bold text-base-content mt-1">{{ number_format($this->kpiStats['types']['SO']['total'], 0, ',', '.') }}</div>
            <div class="text-[9px] xl:text-[10px] mt-2 flex justify-between items-center text-base-content/60">
                <div>Aktif: <span class="font-bold text-success">{{ number_format($this->kpiStats['types']['SO']['aktif'], 0, ',', '.') }} ({{ $this->kpiStats['types']['SO']['pct_aktif'] }}%)</span></div>
                <div>Gap: <span class="font-bold text-error">{{ number_format($this->kpiStats['types']['SO']['gap'], 0, ',', '.') }} ({{ $this->kpiStats['types']['SO']['pct_gap'] }}%)</span></div>
            </div>
        </div>
        
        <!-- 3. Type G -->
        <div class="bg-base-100 rounded-xl shadow-xl border border-info/30 p-3 md:p-4 flex flex-col">
            <div class="text-[10px] md:text-xs font-bold text-info uppercase tracking-wide">Type G</div>
            <div class="text-lg md:text-xl font-bold text-base-content mt-1">{{ number_format($this->kpiStats['types']['G']['total'], 0, ',', '.') }}</div>
            <div class="text-[9px] xl:text-[10px] mt-2 flex justify-between items-center text-base-content/60">
                <div>Aktif: <span class="font-bold text-success">{{ number_format($this->kpiStats['types']['G']['aktif'], 0, ',', '.') }} ({{ $this->kpiStats['types']['G']['pct_aktif'] }}%)</span></div>
                <div>Gap: <span class="font-bold text-error">{{ number_format($this->kpiStats['types']['G']['gap'], 0, ',', '.') }} ({{ $this->kpiStats['types']['G']['pct_gap'] }}%)</span></div>
            </div>
        </div>
        
        <!-- 4. Type SG -->
        <div class="bg-base-100 rounded-xl shadow-xl border border-warning/30 p-3 md:p-4 flex flex-col">
            <div class="text-[10px] md:text-xs font-bold text-warning uppercase tracking-wide">Type SG</div>
            <div class="text-lg md:text-xl font-bold text-base-content mt-1">{{ number_format($this->kpiStats['types']['SG']['total'], 0, ',', '.') }}</div>
            <div class="text-[9px] xl:text-[10px] mt-2 flex justify-between items-center text-base-content/60">
                <div>Aktif: <span class="font-bold text-success">{{ number_format($this->kpiStats['types']['SG']['aktif'], 0, ',', '.') }} ({{ $this->kpiStats['types']['SG']['pct_aktif'] }}%)</span></div>
                <div>Gap: <span class="font-bold text-error">{{ number_format($this->kpiStats['types']['SG']['gap'], 0, ',', '.') }} ({{ $this->kpiStats['types']['SG']['pct_gap'] }}%)</span></div>
            </div>
        </div>
        
        <!-- 5. Type R -->
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 p-3 md:p-4 flex flex-col">
            <div class="text-[10px] md:text-xs font-bold text-base-content/70 uppercase tracking-wide">Type R</div>
            <div class="text-lg md:text-xl font-bold text-base-content mt-1">{{ number_format($this->kpiStats['types']['R']['total'], 0, ',', '.') }}</div>
            <div class="text-[9px] xl:text-[10px] mt-2 flex justify-between items-center text-base-content/60">
                <div>Aktif: <span class="font-bold text-success">{{ number_format($this->kpiStats['types']['R']['aktif'], 0, ',', '.') }} ({{ $this->kpiStats['types']['R']['pct_aktif'] }}%)</span></div>
                <div>Gap: <span class="font-bold text-error">{{ number_format($this->kpiStats['types']['R']['gap'], 0, ',', '.') }} ({{ $this->kpiStats['types']['R']['pct_gap'] }}%)</span></div>
            </div>
        </div>
    </div>

    {{-- Main Card (Tabel) yang mengambil sisa ruang flex --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Summary Reaktivasi Toko</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Rekapitulasi level distributor</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <x-ui.search-input wire:model.live.debounce.500ms="search" placeholder="Cari distributor/spv..." />
                
                {{-- Filter Region --}}
                <select wire:model.live="filterRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Region</option>
                    @foreach($this->filterOptions['regions'] as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>

                {{-- Filter Area --}}
                <select wire:model.live="filterArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="">Semua Area</option>
                    @foreach($this->filterOptions['areas'] as $area)
                        <option value="{{ $area }}">{{ $area }}</option>
                    @endforeach
                </select>

                {{-- Filter Supervisor --}}
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 max-w-[120px]">
                    <option value="">Semua SPV</option>
                    @foreach($this->filterOptions['supervisors'] as $spv)
                        <option value="{{ $spv }}">{{ Str::limit($spv, 15) }}</option>
                    @endforeach
                </select>

                {{-- Filter Distributor --}}
                <select wire:model.live="filterDistributor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 max-w-[150px]">
                    <option value="">Semua Distributor</option>
                    @foreach($this->filterOptions['distributors'] as $dist)
                        <option value="{{ $dist }}">{{ Str::limit($dist, 15) }}</option>
                    @endforeach
                </select>
                
                {{-- Filter Bulan --}}
                <select wire:model.live="filterBulan" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>

                {{-- Filter Tahun --}}
                <select wire:model.live="filterTahun" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                </select>
                
                {{-- Reset Filter Button --}}
                <button wire:click="resetFilters" class="btn btn-sm btn-ghost btn-circle text-base-content/60 hover:text-primary" title="Reset Filter" wire:loading.attr="disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </div>
        </div>


        {{-- Body Card (Tabel Scrollable area) --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="summary-table table table-sm table-zebra w-full whitespace-nowrap">
                <thead class="text-xs tracking-wider text-center align-middle">
                    {{-- ROW 1: Group Labels --}}
                    <tr style="height:34px;">
                        <th rowspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--b3));border-right:2px solid #fff;" class="w-16 text-base-content/80 border-b border-base-400">No</th>
                        <th rowspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--b3));" class="text-left text-base-content/80 border-b border-base-400">Region &amp; Area</th>
                        <th rowspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--b3));border-right:2px solid #fff;" class="text-left text-base-content/80 border-b border-base-400">Distributor &amp; SPV</th>

                        <th colspan="4" style="position:sticky;top:0;z-index:20;background:oklch(var(--b3));border-right:2px solid #fff;" class="text-base-content/80 border-b border-base-400">TOTAL KESELURUHAN</th>

                        <th rowspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--b3));border-right:2px solid #fff;" class="text-right text-base-content/80 border-b border-base-400">Total Transaksi (Rp)</th>

                        <th colspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--p));border-right:2px solid #fff;" class="text-primary-content border-b border-primary">TYPE SO</th>
                        <th colspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--in));border-right:2px solid #fff;" class="text-info-content border-b border-info">TYPE G</th>
                        <th colspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--wa));border-right:2px solid #fff;" class="text-warning-content border-b border-warning">TYPE SG</th>
                        <th colspan="2" style="position:sticky;top:0;z-index:20;background:oklch(var(--b2));" class="text-base-content/80 border-b border-base-400">TYPE R</th>
                    </tr>
                    {{-- ROW 2: Sub-column Labels --}}
                    <tr class="text-xs">
                        <th style="position:sticky;top:34px;z-index:20;background:oklch(var(--b3));" class="text-right text-base-content/80 border-b-2 border-base-400">Toko</th>
                        <th style="position:sticky;top:34px;z-index:20;background:oklch(var(--b3));" class="text-right text-success border-b-2 border-base-400">Aktif</th>
                        <th style="position:sticky;top:34px;z-index:20;background:oklch(var(--b3));" class="text-right text-error border-b-2 border-base-400">Gap</th>
                        <th style="position:sticky;top:34px;z-index:20;background:oklch(var(--b3));border-right:2px solid #fff;" class="text-right text-base-content/80 border-b-2 border-base-400">% Aktif</th>

                        <th style="position:sticky;top:34px;z-index:20;background:color-mix(in oklch,oklch(var(--p)) 20%,oklch(var(--b1)));" class="text-right text-primary border-b-2 border-primary/40">Tot</th>
                        <th style="position:sticky;top:34px;z-index:20;background:color-mix(in oklch,oklch(var(--p)) 20%,oklch(var(--b1)));border-right:2px solid #fff;" class="text-right text-primary border-b-2 border-primary/40">Akt</th>

                        <th style="position:sticky;top:34px;z-index:20;background:color-mix(in oklch,oklch(var(--in)) 20%,oklch(var(--b1)));" class="text-right text-info border-b-2 border-info/40">Tot</th>
                        <th style="position:sticky;top:34px;z-index:20;background:color-mix(in oklch,oklch(var(--in)) 20%,oklch(var(--b1)));border-right:2px solid #fff;" class="text-right text-info border-b-2 border-info/40">Akt</th>

                        <th style="position:sticky;top:34px;z-index:20;background:color-mix(in oklch,oklch(var(--wa)) 20%,oklch(var(--b1)));" class="text-right text-warning border-b-2 border-warning/40">Tot</th>
                        <th style="position:sticky;top:34px;z-index:20;background:color-mix(in oklch,oklch(var(--wa)) 20%,oklch(var(--b1)));border-right:2px solid #fff;" class="text-right text-warning border-b-2 border-warning/40">Akt</th>

                        <th style="position:sticky;top:34px;z-index:20;background:oklch(var(--b2));" class="text-right text-base-content/70 border-b-2 border-base-400">Tot</th>
                        <th style="position:sticky;top:34px;z-index:20;background:oklch(var(--b2));" class="text-right text-base-content/70 border-b-2 border-base-400">Akt</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($this->paginatedSummaries as $index => $row)
                        @php 
                            $pctAktif = $row->total_toko > 0 ? round(($row->total_aktif / $row->total_toko) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <th class="text-center">{{ $this->paginatedSummaries->firstItem() + $index }}</th>
                            <td>
                                <div class="font-bold truncate max-w-[120px] md:max-w-[150px]" title="{{ $row->region }}">{{ $row->region }}</div>
                                <div class="text-xs text-base-content/60 truncate max-w-[120px] md:max-w-[150px]" title="{{ $row->area }}">{{ $row->area }}</div>
                            </td>
                            <td>
                                <div class="font-bold truncate max-w-[150px] md:max-w-[180px]" title="{{ $row->distributor }}">{{ $row->distributor }}</div>
                                <div class="text-xs text-base-content/60 truncate max-w-[150px] md:max-w-[180px]" title="{{ $row->supervisor }}">{{ $row->supervisor }}</div>
                            </td>
                            
                            {{-- TOTAL --}}
                            <td class="text-right font-bold">{{ number_format((float)$row->total_toko, 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-success">{{ number_format((float)$row->total_aktif, 0, ',', '.') }}</td>
                            <td class="text-right font-bold text-error">{{ number_format((float)$row->gap, 0, ',', '.') }}</td>
                            <td class="text-right font-bold {{ $pctAktif >= 50 ? 'text-success' : 'text-error' }}">{{ $pctAktif }}%</td>
                            
                            {{-- TRANSAKSI --}}
                            <td class="text-right font-mono text-success">{{ number_format((float)$row->total_transaksi_rp, 0, ',', '.') }}</td>
                            
                            {{-- TYPE SO --}}
                            <td class="text-right font-semibold {{ $row->total_so == 0 ? 'text-base-content/40' : 'text-base-content' }}">{{ number_format((float)$row->total_so, 0, ',', '.') }}</td>
                            <td class="text-right font-bold {{ $row->aktif_so == $row->total_so && $row->total_so > 0 ? 'text-success' : ($row->aktif_so < $row->total_so ? 'text-error' : 'text-base-content/40') }}">
                                {{ number_format((float)$row->aktif_so, 0, ',', '.') }}
                            </td>
                            
                            {{-- TYPE G --}}
                            <td class="text-right font-semibold {{ $row->total_g == 0 ? 'text-base-content/40' : 'text-base-content' }}">{{ number_format((float)$row->total_g, 0, ',', '.') }}</td>
                            <td class="text-right font-bold {{ $row->aktif_g == $row->total_g && $row->total_g > 0 ? 'text-success' : ($row->aktif_g < $row->total_g ? 'text-error' : 'text-base-content/40') }}">
                                {{ number_format((float)$row->aktif_g, 0, ',', '.') }}
                            </td>
                            
                            {{-- TYPE SG --}}
                            <td class="text-right font-semibold {{ $row->total_sg == 0 ? 'text-base-content/40' : 'text-base-content' }}">{{ number_format((float)$row->total_sg, 0, ',', '.') }}</td>
                            <td class="text-right font-bold {{ $row->aktif_sg == $row->total_sg && $row->total_sg > 0 ? 'text-success' : ($row->aktif_sg < $row->total_sg ? 'text-error' : 'text-base-content/40') }}">
                                {{ number_format((float)$row->aktif_sg, 0, ',', '.') }}
                            </td>
                            
                            {{-- TYPE R --}}
                            <td class="text-right font-semibold {{ $row->total_r == 0 ? 'text-base-content/40' : 'text-base-content' }}">{{ number_format((float)$row->total_r, 0, ',', '.') }}</td>
                            <td class="text-right font-bold {{ $row->aktif_r == $row->total_r && $row->total_r > 0 ? 'text-success' : ($row->aktif_r < $row->total_r ? 'text-error' : 'text-base-content/40') }}">
                                {{ number_format((float)$row->aktif_r, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-6 text-base-content/50">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- GRAND TOTAL ROW --}}
                <tfoot>
                    @php
                        $gt = $this->kpiStats;
                        $gtPct = $gt['total_toko'] > 0 ? round(($gt['total_aktif'] / $gt['total_toko']) * 100, 1) : 0;
                    @endphp
                    <tr class="text-xs font-bold" style="position:sticky;bottom:0;z-index:15;background:oklch(var(--b3));">
                        <th colspan="3" style="background:oklch(var(--b3));border-top:2px solid oklch(var(--b3)/0.5);border-right:2px solid #fff;" class="text-left text-base-content/80 uppercase tracking-wider py-2 px-3">
                            GRAND TOTAL
                        </th>

                        {{-- Total Keseluruhan --}}
                        <td style="background:oklch(var(--b3));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right">
                            {{ number_format($gt['total_toko'], 0, ',', '.') }}
                        </td>
                        <td style="background:oklch(var(--b3));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right text-success">
                            {{ number_format($gt['total_aktif'], 0, ',', '.') }}
                        </td>
                        <td style="background:oklch(var(--b3));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right text-error">
                            {{ number_format($gt['gap'], 0, ',', '.') }}
                        </td>
                        <td style="background:oklch(var(--b3));border-top:2px solid oklch(var(--b3)/0.5);border-right:2px solid #fff;" class="text-right {{ $gtPct >= 50 ? 'text-success' : 'text-error' }}">
                            {{ $gtPct }}%
                        </td>

                        {{-- Total Transaksi --}}
                        <td style="background:oklch(var(--b3));border-top:2px solid oklch(var(--b3)/0.5);border-right:2px solid #fff;" class="text-right font-mono text-success">
                            {{ number_format($gt['total_transaksi'], 0, ',', '.') }}
                        </td>

                        {{-- SO --}}
                        <td style="background:color-mix(in oklch,oklch(var(--p)) 20%,oklch(var(--b1)));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right">
                            {{ number_format($gt['types']['SO']['total'], 0, ',', '.') }}
                        </td>
                        <td style="background:color-mix(in oklch,oklch(var(--p)) 20%,oklch(var(--b1)));border-top:2px solid oklch(var(--b3)/0.5);border-right:2px solid #fff;" class="text-right {{ $gt['types']['SO']['aktif'] == $gt['types']['SO']['total'] && $gt['types']['SO']['total'] > 0 ? 'text-success' : 'text-error' }}">
                            {{ number_format($gt['types']['SO']['aktif'], 0, ',', '.') }}
                        </td>

                        {{-- G --}}
                        <td style="background:color-mix(in oklch,oklch(var(--in)) 20%,oklch(var(--b1)));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right">
                            {{ number_format($gt['types']['G']['total'], 0, ',', '.') }}
                        </td>
                        <td style="background:color-mix(in oklch,oklch(var(--in)) 20%,oklch(var(--b1)));border-top:2px solid oklch(var(--b3)/0.5);border-right:2px solid #fff;" class="text-right {{ $gt['types']['G']['aktif'] == $gt['types']['G']['total'] && $gt['types']['G']['total'] > 0 ? 'text-success' : 'text-error' }}">
                            {{ number_format($gt['types']['G']['aktif'], 0, ',', '.') }}
                        </td>

                        {{-- SG --}}
                        <td style="background:color-mix(in oklch,oklch(var(--wa)) 20%,oklch(var(--b1)));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right">
                            {{ number_format($gt['types']['SG']['total'], 0, ',', '.') }}
                        </td>
                        <td style="background:color-mix(in oklch,oklch(var(--wa)) 20%,oklch(var(--b1)));border-top:2px solid oklch(var(--b3)/0.5);border-right:2px solid #fff;" class="text-right {{ $gt['types']['SG']['aktif'] == $gt['types']['SG']['total'] && $gt['types']['SG']['total'] > 0 ? 'text-success' : 'text-error' }}">
                            {{ number_format($gt['types']['SG']['aktif'], 0, ',', '.') }}
                        </td>

                        {{-- R --}}
                        <td style="background:oklch(var(--b2));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right">
                            {{ number_format($gt['types']['R']['total'], 0, ',', '.') }}
                        </td>
                        <td style="background:oklch(var(--b2));border-top:2px solid oklch(var(--b3)/0.5);" class="text-right {{ $gt['types']['R']['aktif'] == $gt['types']['R']['total'] && $gt['types']['R']['total'] > 0 ? 'text-success' : 'text-error' }}">
                            {{ number_format($gt['types']['R']['aktif'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        {{-- Footer Card (Pagination) --}}
        <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
            {{ $this->paginatedSummaries->links() }}
        </div>
    </div>
</div>


