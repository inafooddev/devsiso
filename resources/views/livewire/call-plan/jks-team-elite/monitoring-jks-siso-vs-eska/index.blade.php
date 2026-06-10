<div>
    <!-- TABS (optional, can be adapted if needed) -->
    <div class="tabs tabs-boxed mb-4 w-fit bg-base-100 shadow-sm border border-base-200 p-1">
        <a href="{{ route('call-plan.jks-team-elite.monitoring') }}" class="tab px-8 text-base-content/70 hover:text-base-content" wire:navigate>Summary</a>
        <a href="{{ route('jks-team-elite.index') }}" class="tab px-8 text-base-content/70 hover:text-base-content" wire:navigate>Detail</a>
        <a href="{{ route('call-plan.jks-team-elite.monitoring-siso-vs-eska') }}" class="tab px-8 tab-active font-bold" wire:navigate>SISO vs ESKA</a>
    </div>

    <x-card title="Monitoring JKS SISO vs ESKA" icon="clipboard-document-check" class="mb-4" flush="true">
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
            .matrix-col-3 { position: sticky; left: 124px; z-index: 30; min-width: 150px; max-width: 150px; }
            
            /* Top-Left Intersections */
            .matrix-th-top-1.matrix-col-1, .matrix-th-top-1.matrix-col-2, .matrix-th-top-1.matrix-col-3,
            .matrix-th-top-2.matrix-col-1, .matrix-th-top-2.matrix-col-2, .matrix-th-top-2.matrix-col-3 {
                z-index: 50; /* Above both sticky rows and columns */
            }
            
            /* Date Boundaries */
            .matrix-date-border { border-right: 2px solid var(--fallback-bc, oklch(var(--bc)/0.15)) !important; }
            .dark .matrix-date-border { border-right: 2px solid var(--fallback-bc, oklch(var(--bc)/0.25)) !important; }
            
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
                        <th rowspan="2" class="matrix-th-top-1 matrix-col-3 align-middle bg-base-300 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] uppercase tracking-wider text-[10px] font-bold text-base-content/80 px-2 py-1">Nama Team</th>
                        @foreach($monthDates as $date => $dayData)
                        <th colspan="3" class="matrix-th-top-1 text-center bg-base-300 border-b border-base-200 matrix-date-border p-0 {{ $dayData['is_sunday'] ? 'text-error font-bold bg-error/10' : 'text-base-content/80' }}">
                            <div class="flex items-center justify-center h-[34px] w-full uppercase tracking-widest text-[10px] font-bold">
                                {{ $dayData['label'] }}
                            </div>
                        </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($monthDates as $date => $dayData)
                        <th class="matrix-th-top-2 text-center p-0 bg-base-300 border-b border-r border-base-200 {{ $dayData['is_sunday'] ? 'text-error bg-error/10' : 'text-base-content/70' }}">
                            <div class="flex items-center justify-center h-full w-full min-w-[36px] text-[9px] font-semibold uppercase">SISO</div>
                        </th>
                        <th class="matrix-th-top-2 text-center p-0 bg-base-300 border-b border-r border-base-200 {{ $dayData['is_sunday'] ? 'text-error bg-error/10' : 'text-base-content/70' }}">
                            <div class="flex items-center justify-center h-full w-full min-w-[36px] text-[9px] font-semibold uppercase">ESKA</div>
                        </th>
                        <th class="matrix-th-top-2 text-center p-0 bg-base-300 border-b border-base-200 matrix-date-border {{ $dayData['is_sunday'] ? 'text-error bg-error/10' : 'text-base-content/70' }}">
                            <div class="flex items-center justify-center h-full w-full min-w-[40px] text-[9px] font-semibold uppercase">SLSH</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-base-content/90">
                    @forelse($teams as $index => $team)
                    <tr class="matrix-row transition-colors">
                        <td class="matrix-col-1 text-base-content/80 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}" title="{{ $team->region_name ?? '-' }}">{{ $team->region_name ?? '-' }}</td>
                        <td class="matrix-col-2 text-base-content/80 border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }}" title="{{ $team->area_name ?? '-' }}">{{ $team->area_name ?? '-' }}</td>
                        <td class="matrix-col-3 text-base-content/80 font-medium border-b border-r border-base-200 shadow-[1px_0_0_0_oklch(var(--bc)/0.05)] truncate px-2 py-1 {{ $index % 2 === 0 ? 'bg-base-100' : 'bg-base-200' }} uppercase" title="{{ $team->supervisor_name }}">{{ $team->supervisor_name }}</td>
                        @foreach($monthDates as $date => $dayData)
                            @php
                                $siso = $dataMatrix[$team->supervisor_code][$date]['siso'] ?? 0;
                                $eska = $dataMatrix[$team->supervisor_code][$date]['eska'] ?? 0;
                                $selisih = $dataMatrix[$team->supervisor_code][$date]['selisih'] ?? 0;
                            @endphp
                            <td class="text-center p-0 border-b border-r border-base-200 {{ $dayData['is_sunday'] ? 'bg-error/5' : '' }}">
                                <div class="flex items-center justify-center h-full w-full min-h-[32px] hover:bg-base-200">
                                    <span @if($siso > 0) wire:click="showStoreDetails('{{ $team->supervisor_code }}', '{{ $date }}', 'SISO')" class="text-[10px] cursor-pointer hover:underline font-bold" @else class="text-[10px] text-base-content/30" @endif>{{ $siso > 0 ? $siso : '-' }}</span>
                                </div>
                            </td>
                            <td class="text-center p-0 border-b border-r border-base-200 {{ $dayData['is_sunday'] ? 'bg-error/5' : '' }}">
                                <div class="flex items-center justify-center h-full w-full min-h-[32px] hover:bg-base-200">
                                    <span @if($eska > 0) wire:click="showStoreDetails('{{ $team->supervisor_code }}', '{{ $date }}', 'ESKA')" class="text-[10px] cursor-pointer hover:underline font-bold text-info" @else class="text-[10px] text-base-content/30" @endif>{{ $eska > 0 ? $eska : '-' }}</span>
                                </div>
                            </td>
                            <td class="text-center p-0 border-b border-base-200 matrix-date-border {{ $dayData['is_sunday'] ? 'bg-error/5' : '' }}">
                                <div class="flex items-center justify-center h-full w-full min-h-[32px] {{ $selisih != 0 ? 'bg-error/10' : '' }} hover:bg-base-200">
                                    <span wire:click="showStoreDetails('{{ $team->supervisor_code }}', '{{ $date }}', 'SELISIH')" class="text-[10px] cursor-pointer hover:underline font-bold {{ $selisih == 0 ? 'text-success' : 'text-error' }}">{{ $selisih }}</span>
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

    <!-- DETAIL MODAL TOKO -->
    <dialog id="modal-detail" class="modal modal-bottom sm:modal-middle {{ $isDetailModalOpen ? 'modal-open' : '' }}">
        <div class="modal-box bg-base-100 border border-base-300 !w-11/12 !max-w-6xl p-0">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 p-2 rounded-xl bg-primary/10">
                        <x-heroicon-s-building-storefront class="w-5 h-5 text-primary" />
                    </div>
                    <h3 class="font-bold text-lg text-base-content">Detail Toko Kunjungan ({{ $selectedType }})</h3>
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
                        <span class="font-semibold text-base-content/50 uppercase tracking-wider block">Sumber Data</span>
                        <span class="font-bold text-sm text-primary">{{ $selectedType }}</span>
                    </div>
                    @if($selectedType !== 'SELISIH')
                    <div>
                        <span class="font-semibold text-base-content/50 uppercase tracking-wider block">Total Toko</span>
                        <span class="font-bold text-sm text-success">{{ count($storeDetails) }} Toko</span>
                    </div>
                    @endif
                </div>

                <div class="overflow-x-auto border border-base-200 rounded-xl max-h-[500px]">
                    @if($selectedType === 'SELISIH')
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        {{-- Tabel SISO --}}
                        <div class="border-r border-base-200 flex flex-col">
                            <div class="bg-base-200 px-4 py-2 font-bold text-sm border-b border-base-200 flex justify-between">
                                <span>Terjadwal (SISO)</span>
                                <span class="badge badge-sm">{{ count($storeDetailsSiso) }}</span>
                            </div>
                            <table class="table table-xs w-full table-pin-rows">
                                <thead>
                                    <tr>
                                        <th>Kode Toko</th>
                                        <th>Nama Toko</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($storeDetailsSiso as $store)
                                        @php
                                            $isMatch = in_array($store->custno, $eskaCustnos);
                                        @endphp
                                        <tr class="{{ !$isMatch ? 'bg-error/20 text-error' : '' }}">
                                            <td class="font-mono whitespace-nowrap">{{ $store->custno }}</td>
                                            <td class="max-w-[200px] truncate" title="{{ $store->custname }}">{{ $store->custname }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center py-4 text-base-content/50">Kosong</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Tabel ESKA --}}
                        <div class="flex flex-col">
                            <div class="bg-info/10 text-info px-4 py-2 font-bold text-sm border-b border-base-200 flex justify-between">
                                <span>Realisasi (ESKA)</span>
                                <span class="badge badge-info badge-sm">{{ count($storeDetailsEska) }}</span>
                            </div>
                            <table class="table table-xs w-full table-pin-rows">
                                <thead>
                                    <tr>
                                        <th>Kode Toko</th>
                                        <th>Nama Toko</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($storeDetailsEska as $store)
                                        @php
                                            $isMatch = in_array($store->custno, $sisoCustnos);
                                        @endphp
                                        <tr class="{{ !$isMatch ? 'bg-error/20 text-error' : '' }}">
                                            <td class="font-mono whitespace-nowrap">{{ $store->custno }}</td>
                                            <td class="max-w-[200px] truncate" title="{{ $store->custname }}">{{ $store->custname }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center py-4 text-base-content/50">Kosong</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <table class="table table-xs table-zebra w-full table-pin-rows">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Distributor</th>
                                <th>Kode Toko</th>
                                <th>Nama Toko</th>
                                <th>Alamat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($storeDetails as $index => $store)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="max-w-[200px] truncate" title="{{ $store->distributor_name ?? '-' }}">{{ $store->distributor_name ?? '-' }}</td>
                                    <td class="font-mono font-bold whitespace-nowrap">{{ $store->custno }}</td>
                                    <td class="font-semibold max-w-[250px] truncate" title="{{ $store->custname }}">{{ $store->custname }}</td>
                                    <td class="max-w-[400px] truncate" title="{{ $store->addres }}">{{ $store->addres }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-base-content/50">Tidak ada data toko ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @endif
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
