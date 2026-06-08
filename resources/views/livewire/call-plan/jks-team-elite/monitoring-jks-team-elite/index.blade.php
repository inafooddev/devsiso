<div>
    <!-- TABS -->
    <div class="tabs tabs-boxed mb-4 w-fit bg-base-100 shadow-sm border border-base-200 p-1">
        <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab px-8 tab-active font-bold" wire:navigate>Summary</a>
        <a href="{{ route('jks-team-elite.index') }}" class="tab px-8 text-base-content/70 hover:text-base-content" wire:navigate>Detail</a>
    </div>
    <x-card title="Monitoring JKS Team Elite" icon="clipboard-document-check" class="mb-4" flush="true">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Tim..." class="input input-bordered input-sm w-36 lg:w-48" />
                <select wire:model.live="filterRegion" class="select select-bordered select-sm">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                    @endforeach
                </select>
                <label class="text-sm font-medium ml-2">Bulan:</label>
                <input type="month" wire:model.live="filterMonth" class="input input-bordered input-sm" />
            </div>
        </x-slot:actions>

        <style>
            /* Matrix Table UI/UX Revamp */
            .matrix-table { border-collapse: separate; border-spacing: 0; width: 100%; }
            
            /* 2-Axis Sticky Base */
            .matrix-th-top-1 { position: sticky; top: 0; z-index: 40; }
            .matrix-th-top-2 { position: sticky; top: 34px; z-index: 40; }
            .matrix-col-1 { position: sticky; left: 0; z-index: 30; min-width: 56px; max-width: 56px; }
            .matrix-col-2 { position: sticky; left: 56px; z-index: 30; min-width: 68px; max-width: 68px; }
            .matrix-col-3 { position: sticky; left: 124px; z-index: 30; min-width: 67px; max-width: 67px; }
            .matrix-col-4 { position: sticky; left: 191px; z-index: 30; min-width: 105px; max-width: 105px; }
            
            /* Top-Left Intersections */
            .matrix-th-top-1.matrix-col-1, .matrix-th-top-1.matrix-col-2, .matrix-th-top-1.matrix-col-3, .matrix-th-top-1.matrix-col-4,
            .matrix-th-top-2.matrix-col-1, .matrix-th-top-2.matrix-col-2, .matrix-th-top-2.matrix-col-3, .matrix-th-top-2.matrix-col-4 {
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
        <div class="overflow-auto border border-base-300 bg-base-100 rounded-xl mt-4 mb-4 relative shadow-sm" style="height: 550px;">
            <table class="matrix-table text-xs">
                <thead>
                    <tr>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-1 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Region</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-2 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Area</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-3 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Kode Team</th>
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-4 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Nama Team</th>
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
                        <th class="matrix-th-top-2 text-center p-0 bg-base-300 border-b border-r border-base-200 last:border-r-0 {{ $dayData['is_sunday'] ? 'text-error font-bold bg-error/10' : 'text-base-content/70' }} {{ $dayData['is_end_of_week'] && !$loop->last ? 'matrix-week-border' : '' }}">
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
                        @foreach($monthDates as $date => $dayData)
                        <td class="text-center p-0 border-b border-r border-base-200 last:border-r-0 {{ $dayData['is_sunday'] ? 'bg-error/5' : '' }} {{ $dayData['is_end_of_week'] && !$loop->last ? 'matrix-week-border' : '' }}">
                            <div class="flex items-center justify-center h-full w-full min-h-[32px]">
                                @if(isset($jksData[$team->kode_team][$date]))
                                    @php
                                        $tokoCount = $jksData[$team->kode_team][$date];
                                        $isRed = $dayData['is_weekday'] && $tokoCount < 10;
                                    @endphp
                                    <span wire:click="showStoreDetails('{{ $team->kode_team }}', '{{ $date }}')" class="text-[11px] font-bold cursor-pointer hover:underline {{ $isRed ? 'text-error' : 'text-success' }}">{{ $tokoCount }}</span>
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
    </x-card>

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
