<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Monitoring Visit RWO</x-slot>

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 -mb-1 md:-mb-2">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <a href="{{ route('rwo.dashboard') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Dashboard</a>
            <a href="{{ route('rwo.summarylistpotensi') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Summary</a>
            <a href="{{ route('rwo.pencapaian') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Pencapaian</a>
            <a href="{{ route('rwo.listpotensirwo') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>List Potensi</a>
            <a href="{{ route('rwo.surat-kesepakatan-bersama') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>SKB</a>
            <a href="{{ route('rwo.plan-kunjungan') }}" class="tab tab-xs px-4 text-base-content/70 hover:text-base-content transition-colors" wire:navigate>Plan Kunjungan</a>
            <a href="{{ route('rwo.monitoring-pareto') }}" class="tab tab-xs px-4 tab-active font-bold shadow-sm bg-base-100" wire:navigate>Monitoring Visit</a>
        </div>
    </div>

    {{-- 3 KPI Cards Section --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 shrink-0">
        <!-- Total Outlet RWO -->
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
            <div class="relative z-10 flex flex-col h-full">
                <span class="text-indigo-100 text-[10px] font-bold uppercase tracking-wider">Total Outlet RWO</span>
                <div class="mt-1 text-2xl font-extrabold">{{ number_format($kpi->total_outlets ?? 0, 0, ',', '.') }}</div>
                <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                    <span class="text-indigo-100">Seluruh Outlet RWO Pareto</span>
                </div>
            </div>
        </div>

        <!-- Total Dikunjungi RWO -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
            <div class="relative z-10 flex flex-col h-full">
                <span class="text-blue-100 text-[10px] font-bold uppercase tracking-wider">Total Dikunjungi RWO</span>
                <div class="mt-1 text-2xl font-extrabold">{{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }}</div>
                <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                    <span class="text-blue-100">Outlet RWO Pareto Tervisit</span>
                </div>
            </div>
        </div>

        <!-- Rasio Kunjungan RWO -->
        @php
            $visitRate = ($kpi->total_outlets ?? 0) > 0 ? ($kpi->visited_outlets / $kpi->total_outlets) * 100 : 0;
        @endphp
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
            <div class="relative z-10 flex flex-col h-full">
                <span class="text-emerald-100 text-[10px] font-bold uppercase tracking-wider">Rasio Kunjungan RWO</span>
                <div class="mt-1 text-2xl font-extrabold">{{ number_format($visitRate, 1, ',', '.') }}%</div>
                <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                    <div class="w-full bg-white/20 rounded-full h-1 mt-0.5">
                        <div class="bg-white h-1 rounded-full" style="width: {{ min($visitRate, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold">Data Visit RWO</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Monitoring Visit RWO</p>
            </div>

            <div class="flex flex-wrap items-center justify-start sm:justify-end gap-2 md:gap-3 w-full sm:w-auto">
                <div class="relative">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2.5 text-base-content/50" />
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode, Toko..." class="input input-sm input-bordered w-full sm:w-48 lg:w-56 pl-9 rounded-xl bg-base-100 border-base-300">
                </div>

                <select wire:model.live="filterRewardPercent" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                    <option value="">-- Semua Reward --</option>
                    <option value="0.025">2.5%</option>
                    <option value="0.020">2.0%</option>
                    <option value="0.015">1.5%</option>
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
                    
                    @if($filterRegion || $filterArea || $filterSupervisor || $search || $filterStatusVisit || $filterRewardPercent)
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
        <div class="flex-1 overflow-auto bg-base-100 w-full relative" wire:key="table-wrapper-detail-{{ md5($search . $filterRegion . $filterArea . $filterSupervisor . $filterStatusVisit . $startDate . $endDate) }}">
            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-[10px] md:text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                    <tr>
                        <th>Region</th>
                        <th>Area</th>
                        <th>Distributor</th>
                        <th>Customer Code</th>
                        <th>Uniq KD</th>
                        <th>Customer Name</th>
                        <th class="text-center w-24">Reward %</th>
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
                            <td class="truncate max-w-[150px]" title="{{ $item->distributor_name }}">
                                <span class="font-semibold">{{ $item->distributor_name }}</span>
                            </td>
                            <td class="truncate max-w-[100px] font-mono font-semibold" title="{{ $item->customer_code }}">{{ $item->customer_code }}</td>
                            <td class="truncate max-w-[100px] font-mono" title="{{ $item->uniq_kd }}">{{ $item->uniq_kd }}</td>
                            <td class="truncate max-w-[200px] font-bold" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                            <td class="text-center font-bold font-mono text-primary">
                                {{ rtrim(rtrim(number_format(($item->reward_percent ?? 0.015) * 100, 2, ',', '.'), '0'), ',') }}%
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
                            <td colspan="11" class="text-center py-12 text-base-content/40 bg-base-100">
                                <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                                <p>Tidak ada data monitoring ditemukan untuk filter dan range tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="sticky bottom-0 bg-base-300 border-t-2 border-base-300 shadow-[0_-1px_3px_rgba(0,0,0,0.05)] z-20">
                    <tr class="font-bold text-xs md:text-sm">
                        <td colspan="7" class="text-right uppercase tracking-wider">Subtotal</td>
                        <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_region ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_area ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center font-mono font-bold">{{ number_format($kpi->total_visit_supervisor ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center font-bold text-success bg-success/15">
                            {{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }} Visited
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Footer Card (Pagination) --}}
        @if($data->hasPages())
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
                <select wire:model.live="filterRegion" class="select select-sm select-bordered w-full" @if(count($regions) <= 1) disabled @endif>
                    @if(count($regions) > 1) <option value="">-- Semua Region --</option> @endif
                    @foreach($regions as $r)
                        <option value="{{ $r->region_code }}">{{ $r->region_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Area Select -->
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold text-xs">Area</span></label>
                <select wire:model.live="filterArea" class="select select-sm select-bordered w-full" @if(!$filterRegion || count($areas) <= 1) disabled @endif>
                    @if(count($areas) > 1 || count($areas) == 0) <option value="">-- Semua Area --</option> @endif
                    @foreach($areas as $a)
                        <option value="{{ $a->area_code }}">{{ $a->area_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Supervisor Select -->
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-semibold text-xs">Supervisor</span></label>
                <select wire:model.live="filterSupervisor" class="select select-sm select-bordered w-full" @if(!$filterArea || count($supervisors) <= 1) disabled @endif>
                    @if(count($supervisors) > 1 || count($supervisors) == 0) <option value="">-- Semua Supervisor --</option> @endif
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
