<div class="-mt-6 pt-0">
    <x-slot name="title">Monitoring Outlet Pareto</x-slot>

    <!-- Outer container has p-4 pt-0 mt-0 to remove top padding/margin -->
    <div class="mx-auto p-4 pt-0 mt-0 sm:px-6 lg:px-8">

        <!-- Tabular buttons di atas KPI card -->
        <div class="flex justify-start mb-4">
            <div class="join bg-base-200 p-1 rounded-xl">
                <button wire:click="$set('activeTab', 'summary')" class="btn btn-sm join-item rounded-lg {{ $activeTab === 'summary' ? 'btn-primary shadow-sm font-bold' : 'btn-ghost text-base-content/60' }}">Summary</button>
                <button wire:click="$set('activeTab', 'detail')" class="btn btn-sm join-item rounded-lg {{ $activeTab === 'detail' ? 'btn-primary shadow-sm font-bold' : 'btn-ghost text-base-content/60' }}">Detail</button>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
            <!-- Total Outlet -->
            <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-center relative overflow-hidden bg-base-100">
                <div class="absolute -right-4 -top-4 opacity-5 text-primary"><x-heroicon-s-building-storefront class="w-24 h-24" /></div>
                <div class="text-sm text-base-content/70 font-medium z-10">Total Outlet</div>
                <div class="text-2xl font-bold mt-1 z-10 text-base-content">{{ number_format($kpi->total_outlets ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-base-content/50 mt-2 z-10">Seluruh Outlet Pareto</div>
            </div>
            <!-- Total Dikunjungi -->
            <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-center relative overflow-hidden bg-base-100">
                <div class="absolute -right-4 -top-4 opacity-5 text-success"><x-heroicon-s-check-circle class="w-24 h-24" /></div>
                <div class="text-sm text-base-content/70 font-medium z-10">Total Dikunjungi</div>
                <div class="text-2xl font-bold mt-1 z-10 text-success">{{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-success mt-2 font-bold z-10">
                    Visit Rate: {{ ($kpi->total_outlets ?? 0) > 0 ? number_format(($kpi->visited_outlets / $kpi->total_outlets) * 100, 1) : 0 }}%
                </div>
            </div>
            <!-- KPI 1. RWO -->
            <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-center relative overflow-hidden bg-base-100">
                <div class="absolute -right-4 -top-4 opacity-5 text-info"><x-heroicon-s-tag class="w-24 h-24" /></div>
                <div class="text-sm text-base-content/70 font-medium z-10">1. RWO</div>
                <div class="text-2xl font-bold mt-1 z-10 text-info">{{ number_format($kpi->visited_rwo ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-base-content/50">/ {{ number_format($kpi->total_rwo ?? 0, 0, ',', '.') }}</span></div>
                <div class="text-xs text-info mt-2 font-bold z-10">
                    Visit: {{ ($kpi->total_rwo ?? 0) > 0 ? number_format(($kpi->visited_rwo / $kpi->total_rwo) * 100, 1) : 0 }}%
                </div>
            </div>
            <!-- KPI 2. PNR -->
            <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-center relative overflow-hidden bg-base-100">
                <div class="absolute -right-4 -top-4 opacity-5 text-success"><x-heroicon-s-tag class="w-24 h-24" /></div>
                <div class="text-sm text-base-content/70 font-medium z-10">2. PNR</div>
                <div class="text-2xl font-bold mt-1 z-10 text-success">{{ number_format($kpi->visited_pnr ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-base-content/50">/ {{ number_format($kpi->total_pnr ?? 0, 0, ',', '.') }}</span></div>
                <div class="text-xs text-success mt-2 font-bold z-10">
                    Visit: {{ ($kpi->total_pnr ?? 0) > 0 ? number_format(($kpi->visited_pnr / $kpi->total_pnr) * 100, 1) : 0 }}%
                </div>
            </div>
            <!-- KPI 3. NGVO -->
            <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col justify-center relative overflow-hidden bg-base-100">
                <div class="absolute -right-4 -top-4 opacity-5 text-warning"><x-heroicon-s-tag class="w-24 h-24" /></div>
                <div class="text-sm text-base-content/70 font-medium z-10">3. NGVO</div>
                <div class="text-2xl font-bold mt-1 z-10 text-warning">{{ number_format($kpi->visited_ngvo ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-base-content/50">/ {{ number_format($kpi->total_ngvo ?? 0, 0, ',', '.') }}</span></div>
                <div class="text-xs text-warning mt-2 font-bold z-10">
                    Visit: {{ ($kpi->total_ngvo ?? 0) > 0 ? number_format(($kpi->visited_ngvo / $kpi->total_ngvo) * 100, 1) : 0 }}%
                </div>
            </div>
        </div>

        <!-- MAIN CARD CONTAINER -->
        <div class="bg-base-100 shadow-xl rounded-2xl overflow-hidden border border-base-200">
            
            <!-- Rapih & Clean Search and Filters Toolbar in 1 single row (strictly no wrapping) -->
            <div class="px-6 py-3 border-b border-base-200 bg-base-200/30 flex flex-nowrap items-center justify-between gap-3 overflow-x-auto w-full whitespace-nowrap">
                
                <!-- Left: 1. Search Input -->
                <div class="relative w-60 flex-shrink-0">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/50" />
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode, Toko, Distributor..." 
                           class="input input-sm input-bordered w-full pl-9 focus:input-primary text-xs h-[32px]">
                </div>

                <!-- Right: Other Filters -->
                <div class="flex flex-nowrap items-center gap-3 flex-shrink-0">
                    <!-- 2. Pilar Dropdown Selector -->
                    <div class="w-40 flex-shrink-0">
                        <select wire:model.live="filterPilar" class="select select-sm select-bordered w-full text-xs h-[32px]">
                            <option value="">-- Semua Pilar --</option>
                            <option value="1. RWO">1. RWO</option>
                            <option value="2. PNR">2. PNR</option>
                            <option value="3. NGVO">3. NGVO</option>
                        </select>
                    </div>

                    <!-- 3. Status Visit Dropdown Selector -->
                    <div class="w-44 flex-shrink-0">
                        <select wire:model.live="filterStatusVisit" class="select select-sm select-bordered w-full text-xs h-[32px]">
                            <option value="">-- Semua Status Visit --</option>
                            <option value="Y">Tervisit (Y)</option>
                            <option value="N">Belum Tervisit (N)</option>
                        </select>
                    </div>

                    <!-- 4. Filter Wilayah Button (Region, Area, Supervisor) -->
                    <div class="flex-shrink-0">
                        <x-ui.button variant="neutral" size="sm" outline wire:click="openFilterModal" class="text-xs h-[32px]">
                            <x-heroicon-s-funnel class="w-3.5 h-3.5 mr-1" /> Filter Wilayah
                            @if($filterRegion || $filterArea || $filterSupervisor)
                                <div class="badge badge-primary badge-sm ml-2">!</div>
                            @endif
                        </x-ui.button>
                    </div>

                    <!-- 5. Range Waktu / Date Range Input -->
                    <div class="flex items-center gap-2 bg-base-100 border border-base-300 rounded-lg px-2 h-[32px] text-xs flex-shrink-0">
                        <span class="font-semibold text-base-content/60 flex-shrink-0">Tgl:</span>
                        <input type="date" wire:model.live="startDate" class="input input-xs input-ghost p-0 focus:bg-base-200 text-xs w-28" />
                        <span class="text-base-content/40">&mdash;</span>
                        <input type="date" wire:model.live="endDate" class="input input-xs input-ghost p-0 focus:bg-base-200 text-xs w-28" />
                    </div>

                    <!-- 6. Reset Active Filters -->
                    @if($filterRegion || $filterArea || $filterSupervisor || $search || $filterPilar || $filterStatusVisit)
                        <div class="flex-shrink-0">
                            <x-ui.button variant="error" size="sm" outline wire:click="resetFilter" class="text-xs h-[32px]">
                                Reset
                            </x-ui.button>
                        </div>
                    @endif

                    <!-- 7. Export Button -->
                    <div class="flex-shrink-0">
                        <x-ui.button variant="info" size="sm" wire:click="export" wire:loading.attr="disabled" wire:target="export" class="text-xs h-[32px]">
                            <span wire:loading.remove wire:target="export" class="flex items-center gap-1">
                                <x-heroicon-s-arrow-up-on-square class="w-3.5 h-3.5" /> Export
                            </span>
                            <span wire:loading wire:target="export" class="flex items-center gap-1">
                                <span class="loading loading-spinner loading-xs"></span> Proses...
                            </span>
                        </x-ui.button>
                    </div>
                </div>

            </div>

            <!-- Ellipsis and max width override stylesheet -->
            <style>
                .pareto-table th, .pareto-table td {
                    font-size: 11px !important;
                    white-space: nowrap !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                }
                .col-region { max-width: 55px; }
                .col-area { max-width: 55px; }
                .col-spv { max-width: 84px; }
                .col-dist { max-width: 160px; }
                .pareto-table td.col-dist, .pareto-table th.col-dist { font-size: 10px !important; }
                .col-code { max-width: 90px; }
                .col-name { max-width: 200px; }

                /* Custom Scrollbar for Pareto Table */
                .pareto-table::-webkit-scrollbar {
                    width: 6px;
                    height: 6px;
                }
                .pareto-table::-webkit-scrollbar-track {
                    background: transparent;
                }
                .pareto-table::-webkit-scrollbar-thumb {
                    background: oklch(var(--bc) / 0.2);
                    border-radius: 4px;
                }
                .pareto-table::-webkit-scrollbar-thumb:hover {
                    background: oklch(var(--bc) / 0.4);
                }
            </style>

            <!-- Table View Container -->
            <div wire:key="table-wrapper-{{ $activeTab }}-{{ md5($search . $filterRegion . $filterArea . $filterSupervisor . $filterPilar . $filterStatusVisit . $startDate . $endDate) }}">
                @if($activeTab === 'summary')
                    <x-ui.table hover striped sticky empty="Tidak ada data summary ditemukan." class="pareto-table max-h-[60vh] overflow-y-auto border-x-0 border-b-0 rounded-none shadow-none">
                        <x-slot:head>
                            <tr>
                                <th class="col-region">Region</th>
                                <th class="col-area">Area</th>
                                <th class="col-spv">Supervisor</th>
                                <th class="col-dist">Distributor</th>
                                <th class="text-center w-24">Pilar</th>
                                <th class="text-center w-24">Total</th>
                                <th class="text-center w-24">Visited</th>
                                <th class="text-center w-24">Not Visited</th>
                                <th class="text-center w-24">Visit Rate</th>
                                <th class="text-center w-20">RSM</th>
                                <th class="text-center w-20">ASM</th>
                                <th class="text-center w-20">SPV</th>
                            </tr>
                        </x-slot:head>

                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    <td class="col-region truncate font-medium" title="{{ $item->region_name }}">{{ $item->region_name }}</td>
                                    <td class="col-area truncate" title="{{ $item->area_name }}">{{ $item->area_name }}</td>
                                    <td class="col-spv truncate" title="{{ $item->supervisor_name ?? '-' }}">
                                        <span class="font-semibold">{{ $item->supervisor_name ?? '-' }}</span>
                                    </td>
                                    <td class="col-dist truncate" title="{{ $item->distributor_name }}">
                                        <span class="font-semibold">{{ $item->distributor_name }}</span>
                                    </td>
                                    <td class="whitespace-nowrap text-center">
                                        @if($item->pilar === '1. RWO')
                                            <x-ui.badge variant="info" size="xs">1. RWO</x-ui.badge>
                                        @elseif($item->pilar === '2. PNR')
                                            <x-ui.badge variant="success" size="xs">2. PNR</x-ui.badge>
                                        @elseif($item->pilar === '3. NGVO')
                                            <x-ui.badge variant="warning" size="xs">3. NGVO</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral" size="xs">{{ $item->pilar ?? '-' }}</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="text-center font-bold font-mono">{{ number_format($item->total_outlets, 0, ',', '.') }}</td>
                                    <td class="text-center font-bold font-mono text-success">{{ number_format($item->visited_outlets, 0, ',', '.') }}</td>
                                    <td class="text-center font-bold font-mono text-error bg-error/5">
                                        {{ number_format($item->total_outlets - $item->visited_outlets, 0, ',', '.') }}
                                    </td>
                                    @php
                                        $itemRate = $item->total_outlets > 0 ? ($item->visited_outlets / $item->total_outlets) * 100 : 0;
                                        $itemRateClass = $itemRate >= 100 ? 'bg-success/20 text-success' : ($itemRate >= 80 ? 'bg-warning/20 text-warning-content' : 'bg-error/20 text-error');
                                    @endphp
                                    <td class="text-center font-bold font-mono {{ $itemRateClass }}">
                                        {{ number_format($itemRate, 1) }}%
                                    </td>
                                    <!-- Highlight warna di kolom RSM, ASM, SPV jika ada isinya -->
                                    <td class="text-center transition-colors {{ $item->visit_region > 0 ? 'bg-info/20 text-info font-bold' : '' }}">
                                        {{ $item->visit_region }}
                                    </td>
                                    <td class="text-center transition-colors {{ $item->visit_area > 0 ? 'bg-success/20 text-success font-bold' : '' }}">
                                        {{ $item->visit_area }}
                                    </td>
                                    <td class="text-center transition-colors {{ $item->visit_supervisor > 0 ? 'bg-warning/20 text-warning-content font-bold' : '' }}">
                                        {{ $item->visit_supervisor }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-12 text-base-content/40 bg-base-100">
                                        <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                                        <p>Tidak ada data summary ditemukan untuk filter dan range tanggal ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <x-slot:foot>
                            <tr class="font-bold border-t-2 border-base-300 bg-base-300 text-xs">
                                <td colspan="5" class="text-right uppercase tracking-wider">Subtotal</td>
                                <td class="text-center font-mono font-bold">{{ number_format($kpi->total_outlets ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center font-mono font-bold text-success">{{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center font-mono font-bold text-error bg-error/15">
                                    {{ number_format(($kpi->total_outlets ?? 0) - ($kpi->visited_outlets ?? 0), 0, ',', '.') }}
                                </td>
                                @php
                                    $subtotalRate = ($kpi->total_outlets ?? 0) > 0 ? ($kpi->visited_outlets / $kpi->total_outlets) * 100 : 0;
                                    $subtotalRateClass = $subtotalRate >= 100 ? 'bg-success/20 text-success' : ($subtotalRate >= 80 ? 'bg-warning/20 text-warning-content' : 'bg-error/20 text-error');
                                @endphp
                                <td class="text-center font-mono font-bold {{ $subtotalRateClass }}">
                                    {{ number_format($subtotalRate, 1) }}%
                                </td>
                                <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_region ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_area ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_supervisor ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </x-slot:foot>
                    </x-ui.table>
                @else
                    <x-ui.table hover striped sticky empty="Tidak ada data monitoring ditemukan." class="pareto-table max-h-[60vh] overflow-y-auto border-x-0 border-b-0 rounded-none shadow-none">
                        <x-slot:head>
                            <tr>
                                <th class="col-region">Region</th>
                                <th class="col-area">Area</th>
                                <th class="col-spv">Supervisor</th>
                                <th class="col-dist">Distributor</th>
                                <th class="col-code">Customer Code</th>
                                <th class="col-code">Uniq KD</th>
                                <th class="col-name">Customer Name</th>
                                <th class="text-center w-24">Pilar</th>
                                <th class="text-center w-20">RSM</th>
                                <th class="text-center w-20">ASM</th>
                                <th class="text-center w-20">SPV</th>
                                <th class="text-center w-24">Status Visit</th>
                            </tr>
                        </x-slot:head>

                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    <td class="col-region truncate font-medium" title="{{ $item->region_name }}">{{ $item->region_name }}</td>
                                    <td class="col-area truncate" title="{{ $item->area_name }}">{{ $item->area_name }}</td>
                                    <td class="col-spv truncate" title="{{ $item->supervisor_name ?? '-' }}">
                                        <span class="font-semibold">{{ $item->supervisor_name ?? '-' }}</span>
                                    </td>
                                    <td class="col-dist truncate" title="{{ $item->distributor_name }}">
                                        <span class="font-semibold">{{ $item->distributor_name }}</span>
                                    </td>
                                    <td class="col-code truncate font-mono font-semibold" title="{{ $item->customer_code }}">{{ $item->customer_code }}</td>
                                    <td class="col-code truncate font-mono" title="{{ $item->uniq_kd }}">{{ $item->uniq_kd }}</td>
                                    <td class="col-name truncate font-bold" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                                    <td class="whitespace-nowrap text-center">
                                        @if($item->pilar === '1. RWO')
                                            <x-ui.badge variant="info" size="xs">1. RWO</x-ui.badge>
                                        @elseif($item->pilar === '2. PNR')
                                            <x-ui.badge variant="success" size="xs">2. PNR</x-ui.badge>
                                        @elseif($item->pilar === '3. NGVO')
                                            <x-ui.badge variant="warning" size="xs">3. NGVO</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral" size="xs">{{ $item->pilar ?? '-' }}</x-ui.badge>
                                        @endif
                                    </td>
                                    <!-- Highlight warna di kolom RSM, ASM, SPV jika ada isinya -->
                                    <td class="text-center transition-colors {{ $item->visit_region > 0 ? 'bg-info/20 text-info font-bold' : '' }}">
                                        {{ $item->visit_region }}
                                    </td>
                                    <td class="text-center transition-colors {{ $item->visit_area > 0 ? 'bg-success/20 text-success font-bold' : '' }}">
                                        {{ $item->visit_area }}
                                    </td>
                                    <td class="text-center transition-colors {{ $item->visit_supervisor > 0 ? 'bg-warning/20 text-warning-content font-bold' : '' }}">
                                        {{ $item->visit_supervisor }}
                                    </td>
                                    <!-- Warna status visit merah jika belum tervisit (N) and green if tervisit (Y) -->
                                    <td class="text-center transition-colors {{ $item->status_visit === 'Y' ? 'bg-success/20 text-success font-bold' : 'bg-error/20 text-error font-bold' }}">
                                        @if($item->status_visit === 'Y')
                                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded bg-success text-success-content font-bold">Y</span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded bg-error text-error-content font-bold">N</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-12 text-base-content/40 bg-base-100">
                                        <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                                        <p>Tidak ada data monitoring ditemukan untuk filter dan range tanggal ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <x-slot:foot>
                            <tr class="font-bold border-t-2 border-base-300 bg-base-300 text-xs">
                                <td colspan="8" class="text-right uppercase tracking-wider">Subtotal</td>
                                <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_region ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_area ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_supervisor ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center font-bold text-success bg-success/15">
                                    {{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }} Visited
                                </td>
                            </tr>
                        </x-slot:foot>
                    </x-ui.table>
                @endif
            </div>

            @if($activeTab === 'detail' && $data->hasPages())
                <div class="px-6 py-4 border-t border-base-200 bg-base-200/30">
                    {{ $data->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL FILTER WILAYAH -->
    <x-ui.modal wire:key="modal-filter-key" id="modal-filter" title="Filter Wilayah (Region, Area, Supervisor)" icon="funnel" :open="$isFilterModalOpen" wire:close="closeFilterModal">
        <div class="space-y-4">
            <!-- Region Select -->
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold text-xs">Region</span></label>
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full">
                    <option value="">-- Semua Region --</option>
                    @foreach($regions as $r)
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Area Select -->
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold text-xs">Area</span></label>
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full" @if(!$filterRegion) disabled @endif>
                    <option value="">-- Semua Area --</option>
                    @foreach($areas as $a)
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Supervisor Select -->
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold text-xs">Supervisor</span></label>
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered w-full" @if(!$filterArea) disabled @endif>
                    <option value="">-- Semua Supervisor --</option>
                    @foreach($supervisors as $s)
                        <option value="{{ $s->supervisor_code }}">{{ $s->supervisor_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="error" outline wire:click="resetFilter">Reset Filter</x-ui.button>
            <x-ui.button variant="primary" wire:click="applyFilter">Terapkan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

</div>
