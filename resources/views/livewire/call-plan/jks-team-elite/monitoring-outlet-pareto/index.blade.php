<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Monitoring Outlet Pareto</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <button wire:click="$set('activeTab', 'summary')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'summary' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">Summary</button>
            <button wire:click="$set('activeTab', 'detail')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'detail' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">Detail</button>
        </div>
    </div>

    {{-- 5 KPI Cards Section --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 md:gap-4 lg:gap-6 shrink-0">
        <!-- Total Outlet -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-primary/10 transition-transform group-hover:scale-150"></div>
            
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Outlet</h3>
                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <x-heroicon-s-building-storefront class="w-4 h-4" />
                </div>
            </div>
            
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-primary">{{ number_format($kpi->total_outlets ?? 0, 0, ',', '.') }}</div>
            <div class="text-[10px] md:text-xs text-base-content/50 mt-1 md:mt-2 z-10">Seluruh Outlet Pareto</div>
        </div>

        <!-- Total Dikunjungi -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">Total Dikunjungi</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-check-circle class="w-4 h-4" />
                </div>
            </div>
            
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">{{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }}</div>
            <div class="text-[10px] md:text-xs text-success mt-1 md:mt-2 font-bold z-10">
                Visit Rate: {{ ($kpi->total_outlets ?? 0) > 0 ? number_format(($kpi->visited_outlets / $kpi->total_outlets) * 100, 1) : 0 }}%
            </div>
        </div>

        <!-- 1. RWO -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-info/10 transition-transform group-hover:scale-150"></div>
            
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">1. RWO</h3>
                <div class="w-8 h-8 rounded-xl bg-info/10 flex items-center justify-center text-info shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-info">
                {{ number_format($kpi->visited_rwo ?? 0, 0, ',', '.') }}
                <span class="text-[10px] md:text-xs font-normal text-base-content/50">/ {{ number_format($kpi->total_rwo ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="text-[10px] md:text-xs text-info mt-1 md:mt-2 font-bold z-10">
                Visit: {{ ($kpi->total_rwo ?? 0) > 0 ? number_format(($kpi->visited_rwo / $kpi->total_rwo) * 100, 1) : 0 }}%
            </div>
        </div>

        <!-- 2. PNR -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-success/10 transition-transform group-hover:scale-150"></div>
            
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">2. PNR</h3>
                <div class="w-8 h-8 rounded-xl bg-success/10 flex items-center justify-center text-success shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-success">
                {{ number_format($kpi->visited_pnr ?? 0, 0, ',', '.') }}
                <span class="text-[10px] md:text-xs font-normal text-base-content/50">/ {{ number_format($kpi->total_pnr ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="text-[10px] md:text-xs text-success mt-1 md:mt-2 font-bold z-10">
                Visit: {{ ($kpi->total_pnr ?? 0) > 0 ? number_format(($kpi->visited_pnr / $kpi->total_pnr) * 100, 1) : 0 }}%
            </div>
        </div>

        <!-- 3. NGVO -->
        <div class="bg-base-100 p-3 lg:p-4 rounded-xl shadow-sm border border-base-300 flex flex-col relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-12 h-12 md:w-16 md:h-16 rounded-full bg-warning/10 transition-transform group-hover:scale-150"></div>
            
            <div class="flex items-start justify-between relative z-10">
                <h3 class="text-[10px] md:text-xs font-bold text-base-content/50 uppercase tracking-wider truncate pr-2 mt-1">3. NGVO</h3>
                <div class="w-8 h-8 rounded-xl bg-warning/10 flex items-center justify-center text-warning shrink-0">
                    <x-heroicon-s-tag class="w-4 h-4" />
                </div>
            </div>
            
            <div class="text-lg md:text-xl font-bold leading-none mt-1 md:mt-2 truncate relative z-10 text-warning">
                {{ number_format($kpi->visited_ngvo ?? 0, 0, ',', '.') }}
                <span class="text-[10px] md:text-xs font-normal text-base-content/50">/ {{ number_format($kpi->total_ngvo ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="text-[10px] md:text-xs text-warning mt-1 md:mt-2 font-bold z-10">
                Visit: {{ ($kpi->total_ngvo ?? 0) > 0 ? number_format(($kpi->visited_ngvo / $kpi->total_ngvo) * 100, 1) : 0 }}%
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Pareto</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Monitoring Outlet Pareto</p>
            </div>

            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <div class="relative">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/50" />
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode, Toko..." class="input input-sm input-bordered w-full sm:w-48 lg:w-56 pl-9 rounded-xl bg-base-100 border-base-300">
                </div>

                <select wire:model.live="filterPilar" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">-- Semua Pilar --</option>
                    <option value="1. RWO">1. RWO</option>
                    <option value="2. PNR">2. PNR</option>
                    <option value="3. NGVO">3. NGVO</option>
                </select>

                <select wire:model.live="filterStatusVisit" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">-- Semua Status Visit --</option>
                    <option value="Y">Tervisit (Y)</option>
                    <option value="N">Belum Tervisit (N)</option>
                </select>

                <div class="flex items-center gap-2 bg-base-100 border border-base-300 rounded-xl px-3 h-8 grow sm:grow-0">
                    <span class="font-semibold text-[10px] md:text-xs text-base-content/60">Tgl:</span>
                    <input type="date" wire:model.live="startDate" class="input input-xs input-ghost p-0 focus:bg-base-200 text-xs w-24 md:w-28" />
                    <span class="text-base-content/40">&mdash;</span>
                    <input type="date" wire:model.live="endDate" class="input input-xs input-ghost p-0 focus:bg-base-200 text-xs w-24 md:w-28" />
                </div>

                <div class="flex flex-wrap items-center gap-1 md:gap-2">
                    <button wire:click="openFilterModal" class="btn btn-sm btn-square btn-ghost bg-base-100 border border-base-300 rounded-xl relative" title="Filter Wilayah">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        @if($filterRegion || $filterArea || $filterSupervisor)
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-primary rounded-full ring-2 ring-base-100"></span>
                        @endif
                    </button>
                    
                    @if($filterRegion || $filterArea || $filterSupervisor || $search || $filterPilar || $filterStatusVisit)
                        <button wire:click="resetFilter" class="btn btn-sm btn-square btn-ghost bg-base-100 border border-base-300 rounded-xl text-error" title="Reset Filter">
                            <x-heroicon-s-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                    
                    <button wire:click="export" wire:loading.attr="disabled" wire:target="export" class="btn btn-sm btn-square btn-ghost bg-base-100 border border-base-300 rounded-xl" title="Export">
                        <x-heroicon-s-arrow-up-on-square class="w-4 h-4" wire:loading.remove wire:target="export" />
                        <span class="loading loading-spinner loading-xs" wire:loading wire:target="export"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Body Card --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative" wire:key="table-wrapper-{{ $activeTab }}-{{ md5($search . $filterRegion . $filterArea . $filterSupervisor . $filterPilar . $filterStatusVisit . $startDate . $endDate) }}">
            @if($activeTab === 'summary')
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-[10px] md:text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th>Region</th>
                            <th>Area</th>
                            <th>Supervisor</th>
                            <th>Distributor</th>
                            <th class="text-center w-24">Pilar</th>
                            <th class="text-center w-24">Total</th>
                            <th class="text-center w-24">Visited</th>
                            <th class="text-center w-24">Not Visited</th>
                            <th class="text-center w-24">Visit Rate</th>
                            <th class="text-center w-20">RSM</th>
                            <th class="text-center w-20">ASM</th>
                            <th class="text-center w-20">SPV</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs md:text-sm">
                        @forelse($data as $item)
                            <tr class="hover:bg-base-200/50 transition-colors">
                                <td class="truncate max-w-[80px]" title="{{ $item->region_name }}">{{ $item->region_name }}</td>
                                <td class="truncate max-w-[80px]" title="{{ $item->area_name }}">{{ $item->area_name }}</td>
                                <td class="truncate max-w-[120px]" title="{{ $item->supervisor_name ?? '-' }}">
                                    <span class="font-semibold">{{ $item->supervisor_name ?? '-' }}</span>
                                </td>
                                <td class="truncate max-w-[150px]" title="{{ $item->distributor_name }}">
                                    <span class="font-semibold">{{ $item->distributor_name }}</span>
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    @if($item->pilar === '1. RWO')
                                        <div class="badge badge-info badge-sm text-[10px]">1. RWO</div>
                                    @elseif($item->pilar === '2. PNR')
                                        <div class="badge badge-success badge-sm text-[10px]">2. PNR</div>
                                    @elseif($item->pilar === '3. NGVO')
                                        <div class="badge badge-warning badge-sm text-[10px]">3. NGVO</div>
                                    @else
                                        <div class="badge badge-neutral badge-sm text-[10px]">{{ $item->pilar ?? '-' }}</div>
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
                    <tfoot class="sticky bottom-0 bg-base-300 border-t-2 border-base-300 shadow-[0_-1px_3px_rgba(0,0,0,0.05)] z-20">
                        <tr class="font-bold text-xs md:text-sm">
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
                    </tfoot>
                </table>
            @else
                <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                    <thead class="text-[10px] md:text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                        <tr>
                            <th>Region</th>
                            <th>Area</th>
                            <th>Supervisor</th>
                            <th>Distributor</th>
                            <th>Customer Code</th>
                            <th>Uniq KD</th>
                            <th>Customer Name</th>
                            <th class="text-center w-24">Pilar</th>
                            <th class="text-center w-20">RSM</th>
                            <th class="text-center w-20">ASM</th>
                            <th class="text-center w-20">SPV</th>
                            <th class="text-center w-24">Status Visit</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs md:text-sm">
                        @forelse($data as $item)
                            <tr class="hover:bg-base-200/50 transition-colors">
                                <td class="truncate max-w-[80px]" title="{{ $item->region_name }}">{{ $item->region_name }}</td>
                                <td class="truncate max-w-[80px]" title="{{ $item->area_name }}">{{ $item->area_name }}</td>
                                <td class="truncate max-w-[120px]" title="{{ $item->supervisor_name ?? '-' }}">
                                    <span class="font-semibold">{{ $item->supervisor_name ?? '-' }}</span>
                                </td>
                                <td class="truncate max-w-[150px]" title="{{ $item->distributor_name }}">
                                    <span class="font-semibold">{{ $item->distributor_name }}</span>
                                </td>
                                <td class="truncate max-w-[100px] font-mono font-semibold" title="{{ $item->customer_code }}">{{ $item->customer_code }}</td>
                                <td class="truncate max-w-[100px] font-mono" title="{{ $item->uniq_kd }}">{{ $item->uniq_kd }}</td>
                                <td class="truncate max-w-[200px] font-bold" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                                <td class="whitespace-nowrap text-center">
                                    @if($item->pilar === '1. RWO')
                                        <div class="badge badge-info badge-sm text-[10px]">1. RWO</div>
                                    @elseif($item->pilar === '2. PNR')
                                        <div class="badge badge-success badge-sm text-[10px]">2. PNR</div>
                                    @elseif($item->pilar === '3. NGVO')
                                        <div class="badge badge-warning badge-sm text-[10px]">3. NGVO</div>
                                    @else
                                        <div class="badge badge-neutral badge-sm text-[10px]">{{ $item->pilar ?? '-' }}</div>
                                    @endif
                                </td>
                                <td class="text-center transition-colors {{ $item->visit_region > 0 ? 'bg-info/20 text-info font-bold' : '' }}">
                                    {{ $item->visit_region }}
                                </td>
                                <td class="text-center transition-colors {{ $item->visit_area > 0 ? 'bg-success/20 text-success font-bold' : '' }}">
                                    {{ $item->visit_area }}
                                </td>
                                <td class="text-center transition-colors {{ $item->visit_supervisor > 0 ? 'bg-warning/20 text-warning-content font-bold' : '' }}">
                                    {{ $item->visit_supervisor }}
                                </td>
                                <td class="text-center transition-colors {{ $item->status_visit === 'Y' ? 'bg-success/20 text-success font-bold' : 'bg-error/20 text-error font-bold' }}">
                                    @if($item->status_visit === 'Y')
                                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded bg-success text-success-content font-bold text-[10px] md:text-xs">Y</span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded bg-error text-error-content font-bold text-[10px] md:text-xs">N</span>
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
                    <tfoot class="sticky bottom-0 bg-base-300 border-t-2 border-base-300 shadow-[0_-1px_3px_rgba(0,0,0,0.05)] z-20">
                        <tr class="font-bold text-xs md:text-sm">
                            <td colspan="8" class="text-right uppercase tracking-wider">Subtotal</td>
                            <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_region ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_area ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_supervisor ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center font-bold text-success bg-success/15">
                                {{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }} Visited
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>

        {{-- Footer Card (Pagination) --}}
        @if($activeTab === 'detail' && $data->hasPages())
            <div class="p-3 md:p-4 lg:p-5 border-t border-base-300 shrink-0 bg-base-200">
                {{ $data->links() }}
            </div>
        @endif
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
