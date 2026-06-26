<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Monitoring JKS Team Elite</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Summary</a>
            <a href="{{ route('jks-team-elite.index') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Detail</a>
            <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SISO vs ESKA</a>
        </div>
    </div>

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Monitoring JKS</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Ringkasan kalender JKS Team Elite</p>
            </div>
            
            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Tim..." class="input input-bordered input-sm w-full sm:w-36 lg:w-48 rounded-xl bg-base-100" />
                <select wire:model.live="filterRegion" class="select select-bordered select-sm w-full sm:w-auto rounded-xl bg-base-100" @if(count($regions) <= 1) disabled @endif>
                    @if(count($regions) > 1) <option value="">Semua Region</option> @endif
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">Bulan</label>
                    <input type="month" wire:model.live="filterMonth" class="input input-bordered input-sm rounded-xl bg-base-100 w-full sm:w-auto" />
                </div>
            </div>
        </div>

        <style>
            /* Matrix Table UI/UX Revamp */
            .matrix-table { border-collapse: separate; border-spacing: 0; width: 100%; }
            
            /* 2-Axis Sticky Base */
            .matrix-th-top-1 { position: sticky; top: 0; z-index: 40; }
            .matrix-th-top-2 { position: sticky; top: 2.125rem; z-index: 40; }
            .matrix-col-1 { position: sticky; left: 0; z-index: 30; min-width: 3.5rem; max-width: 3.5rem; }
            .matrix-col-2 { position: sticky; left: 3.5rem; z-index: 30; min-width: 4.25rem; max-width: 4.25rem; }
            .matrix-col-3 { position: sticky; left: 7.75rem; z-index: 30; min-width: 4.25rem; max-width: 4.25rem; }
            .matrix-col-4 { position: sticky; left: 12rem; z-index: 30; min-width: 6.5rem; max-width: 6.5rem; }
            .matrix-col-5 { position: sticky; left: 18.5rem; z-index: 30; min-width: 2.8rem; max-width: 2.8rem; }
            
            /* Top-Left Intersections */
            .matrix-th-top-1.matrix-col-1, .matrix-th-top-1.matrix-col-2, .matrix-th-top-1.matrix-col-3, .matrix-th-top-1.matrix-col-4, .matrix-th-top-1.matrix-col-5,
            .matrix-th-top-2.matrix-col-1, .matrix-th-top-2.matrix-col-2, .matrix-th-top-2.matrix-col-3, .matrix-th-top-2.matrix-col-4, .matrix-th-top-2.matrix-col-5 {
                z-index: 50; /* Above both sticky rows and columns */
            }
            
            /* Week Boundaries */
            .matrix-week-border { border-right: 2px solid var(--fallback-bc, oklch(var(--bc)/0.15)) !important; }
            .dark .matrix-week-border { border-right: 2px solid var(--fallback-bc, oklch(var(--bc)/0.25)) !important; }
            
            /* Row Hover for Sticky Columns */
            .matrix-row:hover td { background-color: var(--fallback-b2, oklch(var(--b2)/1)) !important; }
        </style>

        @if(empty($filterMonth))
        <div class="flex flex-col items-center justify-center py-24 text-base-content/50 border border-base-200 border-dashed rounded-xl mt-4 bg-base-100/50">
            <x-heroicon-o-calendar-days class="w-16 h-16 mb-4 opacity-30" />
            <h3 class="text-lg font-semibold text-base-content/70">Pilih Bulan</h3>
            <p class="text-sm mt-1">Silakan pilih bulan pada kalender di atas untuk menampilkan data matriks.</p>
        </div>
        @else
        <div class="flex-1 overflow-auto bg-base-100 w-full relative">
            <table class="matrix-table text-xs">
                <thead>
                    <tr>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-1 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Region</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-2 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Area</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-3 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Kode Team</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-4 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Nama Team</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-5 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1 text-center">Total<br/>Toko</th>
                        @foreach($weekSpans as $span)
                        <th colspan="{{ $span['colspan'] }}" class="matrix-th-top-1 text-center bg-base-300 border-b border-base-200 {{ $loop->last ? '' : 'matrix-week-border' }} p-0">
                            <div class="flex items-center justify-center h-[34px] w-full uppercase tracking-widest text-[10px] font-bold text-base-content/60">
                                W{{ $span['week_month'] }}
                            </div>
                        </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($monthDates as $date => $dayData)
                        <th class="matrix-th-top-2 text-center p-0 bg-base-300 border-b border-r border-base-200 last:border-r-0 {{ $dayData['is_sunday'] || $dayData['is_libur_nasional'] ? 'text-error font-bold bg-error/10' : 'text-base-content/70' }} {{ $dayData['is_end_of_week'] && !$loop->last ? 'matrix-week-border' : '' }}">
                            <div class="flex items-center justify-center h-full w-full whitespace-nowrap">{{ $dayData['label'] }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-base-content/90">
                    @forelse($teams as $index => $team)
                    <tr class="matrix-row transition-colors">
                        <td class="matrix-col-1 text-base-content/80 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}" title="{{ $team->region_name ?? '-' }}">{{ $team->region_name ?? '-' }}</td>
                        <td class="matrix-col-2 text-base-content/80 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}" title="{{ $team->area_name ?? '-' }}">{{ $team->area_name ?? '-' }}</td>
                        <td class="matrix-col-3 font-medium border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}" title="{{ $team->kode_team }}">{{ $team->kode_team }}</td>
                        <td class="matrix-col-4 text-base-content/80 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }} uppercase" title="{{ $team->nama_team }}">{{ $team->nama_team }}</td>
                        <td class="matrix-col-5 text-center font-bold text-base-content/90 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}">{{ $totalTokoPerTeam[$team->kode_team] ?? 0 }}</td>
                        @foreach($monthDates as $date => $dayData)
                        @php
                            $cellBg = '';
                            $textColor = '';
                            $tokoCount = null;
                            $hasData = isset($jksData[$team->kode_team][$date]);
                            
                            if ($hasData) {
                                $cellData = $jksData[$team->kode_team][$date];
                                $tokoCount = $cellData['count'];
                                $isRed = ($dayData['is_weekday'] && $tokoCount < 10) || (isset($dayData['is_saturday']) && $dayData['is_saturday'] && $tokoCount < 5);
                                $textColor = $isRed ? 'text-error' : 'text-success';
                                
                                if (!$isRed && !$cellData['has_bri_eva']) {
                                    $cellBg = 'bg-warning/30';
                                }
                            }
                        @endphp
                        <td class="text-center p-0 border-b border-r border-base-200 last:border-r-0 {{ $dayData['is_sunday'] || $dayData['is_libur_nasional'] ? 'bg-error/5' : '' }} {{ $dayData['is_end_of_week'] && !$loop->last ? 'matrix-week-border' : '' }} {{ $cellBg }}">
                            <div class="flex items-center justify-center h-full w-full min-h-[32px]">
                                @if($hasData)
                                    <span wire:click="showStoreDetails('{{ $team->kode_team }}', '{{ $date }}')" class="text-[11px] font-bold cursor-pointer hover:underline {{ $textColor }}">{{ $tokoCount }}</span>
                                @else
                                    <span class="text-base-content/20 text-[10px] font-bold">&middot;</span>
                                @endif
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="99" class="text-center py-12 text-base-content/40 bg-base-100">
                            <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                            <p>Tidak ada data ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- DETAIL MODAL TOKO (Standalone DaisyUI) -->
    <dialog id="modal-detail" class="modal modal-bottom sm:modal-middle {{ $isDetailModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box bg-base-100 border border-base-300 !w-11/12 !max-w-6xl p-0">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 p-2 rounded-xl bg-primary/10">
                        <x-heroicon-s-building-storefront class="w-5 h-5 text-primary" />
                    </div>
                    <h3 class="font-bold text-lg text-base-content">Detail Toko Kunjungan</h3>
                </div>
                <button wire:click="closeDetailModal" class="btn btn-sm btn-circle btn-ghost text-base-content/50 hover:text-base-content">
                    <x-heroicon-s-x-mark class="w-4 h-4" />
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 text-base-content space-y-4">
                <div class="flex flex-col md:flex-row justify-between bg-base-200/50 p-4 rounded-xl text-xs gap-2">
                    <div>
                        <span class="font-semibold text-base-content/50 uppercase tracking-wider block">Tim Sales</span>
                        <span class="font-bold text-sm text-base-content uppercase">{{ $selectedTeamCode }} - {{ $selectedTeamName }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-base-content/50 uppercase tracking-wider block">Tanggal Kunjungan</span>
                        <span class="font-bold text-sm text-base-content">{{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-base-content/50 uppercase tracking-wider block">Total Toko</span>
                        <span class="font-bold text-sm text-success">{{ count($storeDetails) }} Toko</span>
                    </div>
                </div>

                <div class="overflow-x-auto border border-base-200 rounded-xl">
                    <table class="table table-xs table-zebra w-full">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Distributor</th>
                                <th>Kode Toko</th>
                                <th>Nama Toko</th>
                                <th>Alamat</th>
                                <th>Pilar</th>
                                <th class="text-right">Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storeDetails as $index => $store)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="max-w-[200px] truncate" title="{{ $store->distributor_name }}">{{ $store->distributor_name }}</td>
                                    <td class="font-mono font-bold whitespace-nowrap">{{ $store->custno }}</td>
                                    <td class="font-semibold max-w-[250px] truncate" title="{{ $store->custname }}">{{ $store->custname }}</td>
                                    <td class="max-w-[400px] truncate" title="{{ $store->addres }}">{{ $store->addres }}</td>
                                    <td class="whitespace-nowrap"><x-ui.badge variant="neutral">{{ $store->pilar ?? '-' }}</x-ui.badge></td>
                                    <td class="font-mono font-bold text-right whitespace-nowrap">{{ $store->target ? number_format($store->target, 0, ',', '.') : '0' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-base-content/50">Tidak ada data toko ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-base-300 bg-base-200/50">
                <x-ui.button type="button" variant="neutral" outline wire:click="closeDetailModal">Tutup</x-ui.button>
            </div>
        </div>

        {{-- Backdrop click to close --}}
        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeDetailModal">close</button>
        </form>
    </dialog>
</div>
