<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-5 w-full h-full">
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
                <div class="flex items-center justify-between gap-2">
                    <span class="text-indigo-100 text-[10px] font-bold uppercase tracking-wider">Total Outlet RWO</span>
                    @if($filterJenisReward || $filterRewardPercent || $filterStatusVisit)
                        <button type="button" wire:click="clearQuickFilters" class="text-[10px] bg-white/20 hover:bg-white/30 px-2 py-0.5 rounded transition-colors cursor-pointer" title="Tampilkan Semua">
                            Reset Quick Filter
                        </button>
                    @endif
                </div>
                <div class="mt-1 text-2xl font-extrabold">{{ number_format($kpi->total_outlets ?? 0, 0, ',', '.') }}</div>
                <div class="mt-auto pt-2 flex flex-wrap items-center gap-2 text-[11px] font-medium text-indigo-100">
                    <button type="button"
                            wire:click="toggleJenisReward('CB')"
                            title="Klik untuk filter Jenis Reward CB"
                            class="px-2 py-0.5 rounded-md transition-all cursor-pointer {{ $filterJenisReward === 'CB' ? 'bg-white text-indigo-700 font-bold shadow-xs ring-2 ring-white/50' : 'bg-white/15 hover:bg-white/25' }}">
                        CB: <strong>{{ number_format($kpi->total_cb ?? 0, 0, ',', '.') }}</strong>
                    </button>
                    <button type="button"
                            wire:click="toggleJenisReward('BB')"
                            title="Klik untuk filter Jenis Reward BB"
                            class="px-2 py-0.5 rounded-md transition-all cursor-pointer {{ $filterJenisReward === 'BB' ? 'bg-white text-indigo-700 font-bold shadow-xs ring-2 ring-white/50' : 'bg-white/15 hover:bg-white/25' }}">
                        BB: <strong>{{ number_format($kpi->total_bb ?? 0, 0, ',', '.') }}</strong>
                    </button>
                </div>
            </div>
        </div>

        <!-- Total Dikunjungi RWO -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
            <div class="relative z-10 flex flex-col h-full">
                <span class="text-blue-100 text-[10px] font-bold uppercase tracking-wider">Total Dikunjungi RWO</span>
                <div class="mt-1 text-2xl font-extrabold">{{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }}</div>
                <div class="mt-auto pt-2 flex flex-wrap items-center justify-between gap-2 text-[11px] font-medium text-blue-100">
                    <button type="button"
                            wire:click="toggleStatusVisit('Y')"
                            title="Klik untuk filter Outlet Tervisit (Y)"
                            class="px-2 py-0.5 rounded-md transition-all cursor-pointer {{ $filterStatusVisit === 'Y' ? 'bg-white text-blue-700 font-bold shadow-xs ring-2 ring-white/50' : 'bg-white/15 hover:bg-white/25' }}">
                        ✓ Tervisit: <strong>{{ number_format($kpi->visited_outlets ?? 0, 0, ',', '.') }}</strong>
                    </button>
                    <button type="button"
                            wire:click="toggleStatusVisit('N')"
                            title="Klik untuk filter Outlet Belum Tervisit (N)"
                            class="px-2 py-0.5 rounded-md transition-all cursor-pointer {{ $filterStatusVisit === 'N' ? 'bg-white text-blue-700 font-bold shadow-xs ring-2 ring-white/50' : 'bg-white/15 hover:bg-white/25' }}">
                        ✕ Belum: <strong>{{ number_format($kpi->unvisited_outlets ?? 0, 0, ',', '.') }}</strong>
                    </button>
                </div>
            </div>
        </div>

        <!-- Rasio Kunjungan RWO -->
        @php
            $visitRate = ($kpi->total_outlets ?? 0) > 0 ? (($kpi->visited_outlets ?? 0) / $kpi->total_outlets) * 100 : 0;
        @endphp
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 text-white p-4 rounded-xl shadow-md relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 transition-transform group-hover:scale-150"></div>
            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-emerald-100 text-[10px] font-bold uppercase tracking-wider">Rasio Kunjungan RWO</span>
                    <span class="text-[10px] text-emerald-100 font-mono">
                        RSM: {{ number_format($kpi->total_visit_region ?? 0, 0, ',', '.') }} •
                        ASM: {{ number_format($kpi->total_visit_area ?? 0, 0, ',', '.') }} •
                        SPV: {{ number_format($kpi->total_visit_supervisor ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-1 text-2xl font-extrabold">{{ number_format($visitRate, 1, ',', '.') }}%</div>
                <div class="mt-auto pt-2 flex flex-col gap-1 text-[11px] font-medium">
                    <div class="w-full bg-white/20 rounded-full h-1.5 mt-0.5">
                        <div class="bg-white h-1.5 rounded-full transition-all duration-300" style="width: {{ min($visitRate, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-3 bg-base-200/30">
            <div class="shrink-0">
                <h2 class="text-base font-bold leading-tight">Data Visit RWO</h2>
                <p class="text-[10px] text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Monitoring Kunjungan Outlet Pareto RWO</p>
            </div>

            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 w-full xl:w-auto">
                {{-- Search --}}
                <div class="relative grow sm:grow-0">
                    <x-heroicon-s-magnifying-glass class="w-4 h-4 absolute left-3 top-2 text-base-content/50" />
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Cari Kode, Uniq KD, Toko, SPV..."
                           class="input input-sm input-bordered w-full sm:w-52 pl-9 pr-7 rounded-xl bg-base-100 border-base-300 text-xs">
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content text-xs">
                            ✕
                        </button>
                    @endif
                </div>

                {{-- Filter Jenis Reward --}}
                <select wire:model.live="filterJenisReward" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs grow sm:grow-0">
                    <option value="">-- Semua Jenis Reward --</option>
                    <option value="CB">CB (Cashback)</option>
                    <option value="BB">BB (Bonus Barang)</option>
                </select>

                {{-- Filter Besaran Reward --}}
                <select wire:model.live="filterRewardPercent" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs grow sm:grow-0">
                    <option value="">-- Semua Reward --</option>
                    @if($filterJenisReward !== 'BB')
                        <option value="0.025">2.5% (CB)</option>
                        <option value="0.020">2.0% (CB)</option>
                        <option value="0.015">1.5% (CB)</option>
                    @endif
                    @if($filterJenisReward !== 'CB')
                        <option value="BB">1 KTN (BB)</option>
                    @endif
                </select>

                {{-- Filter Status Visit --}}
                <select wire:model.live="filterStatusVisit" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 text-xs grow sm:grow-0">
                    <option value="">-- Semua Status Visit --</option>
                    <option value="Y">Tervisit (Y)</option>
                    <option value="N">Belum Tervisit (N)</option>
                </select>

                {{-- Filter Tanggal --}}
                <div class="flex items-center gap-1.5 bg-base-100 border border-base-300 rounded-xl px-2.5 h-8 grow sm:grow-0">
                    <span class="font-semibold text-[11px] text-base-content/60">Tgl:</span>
                    <input type="date" wire:model.live="startDate" class="input input-xs input-ghost p-0 focus:bg-base-200 text-xs w-24 md:w-28" />
                    <span class="text-base-content/40">&mdash;</span>
                    <input type="date" wire:model.live="endDate" class="input input-xs input-ghost p-0 focus:bg-base-200 text-xs w-24 md:w-28" />
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-1.5 ml-auto sm:ml-0">
                    <button wire:click="openFilterModal" class="btn btn-sm btn-square btn-ghost bg-base-100 border border-base-300 rounded-xl relative" title="Filter Wilayah (Region, Area, Supervisor)">
                        <x-heroicon-s-funnel class="w-4 h-4" />
                        @if($filterRegion || $filterArea || $filterSupervisor)
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-primary rounded-full ring-2 ring-base-100"></span>
                        @endif
                    </button>
                    
                    @if($filterRegion || $filterArea || $filterSupervisor || $search || $filterStatusVisit || $filterJenisReward || $filterRewardPercent)
                        <button wire:click="resetFilter" class="btn btn-sm btn-square btn-ghost bg-base-100 border border-base-300 rounded-xl text-error" title="Reset Semua Filter">
                            <x-heroicon-s-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                    
                    <button wire:click="export" wire:loading.attr="disabled" wire:target="export" class="btn btn-sm btn-success text-white rounded-xl gap-1.5 px-3 text-xs shadow-xs" title="Export Excel">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" wire:loading.remove wire:target="export" />
                        <span class="loading loading-spinner loading-xs" wire:loading wire:target="export"></span>
                        <span class="hidden sm:inline">Export</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Body Card --}}
        <div class="flex-1 overflow-auto bg-base-100 w-full relative" wire:key="table-wrapper-detail-{{ md5($search . $filterRegion . $filterArea . $filterSupervisor . $filterStatusVisit . $filterJenisReward . $filterRewardPercent . $startDate . $endDate) }}">
            {{-- Loading Overlay --}}
            <div wire:loading.flex
                 wire:target="search, startDate, endDate, filterRegion, filterArea, filterSupervisor, filterStatusVisit, filterJenisReward, filterRewardPercent, toggleJenisReward, toggleStatusVisit, clearQuickFilters, resetFilter, applyFilter, gotoPage, nextPage, previousPage"
                 class="absolute inset-0 items-center justify-center bg-base-100/60 z-30 backdrop-blur-[1px]">
                <div class="bg-base-100 px-4 py-2.5 rounded-xl shadow-lg border border-base-300 flex items-center gap-3">
                    <span class="loading loading-spinner loading-sm text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/80">Memuat data monitoring visit...</span>
                </div>
            </div>

            <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                <thead class="text-[10px] md:text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm sticky top-0 z-20">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Region / Area</th>
                        <th>Supervisor</th>
                        <th>Distributor</th>
                        <th>Customer Code</th>
                        <th>Uniq KD</th>
                        <th>Customer Name</th>
                        <th class="text-center w-20">Jenis</th>
                        <th class="text-center w-24">Reward</th>
                        <th class="text-center w-16">PIC</th>
                        <th class="text-center w-16">RSM</th>
                        <th class="text-center w-16">ASM</th>
                        <th class="text-center w-16">SPV</th>
                        <th class="text-center w-24">Status Visit</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($data as $index => $item)
                        @php
                            $isBB = ($item->jenis_reward ?? 'CB') === 'BB';
                            $pic = $item->pic ?? 'SPV';

                            $rsmCount = (int) ($item->count_region ?? 0);
                            $rsmDate = !empty($item->last_visit_region) ? \Carbon\Carbon::parse($item->last_visit_region)->format('d/m/Y') : null;
                            $rsmTitle = $item->visit_region > 0
                                ? "RSM: Sudah Visit ({$rsmCount}x" . ($rsmDate ? " — Terakhir: {$rsmDate}" : '') . ")"
                                : 'RSM: Belum Visit';

                            $asmCount = (int) ($item->count_area ?? 0);
                            $asmDate = !empty($item->last_visit_area) ? \Carbon\Carbon::parse($item->last_visit_area)->format('d/m/Y') : null;
                            $asmTitle = $item->visit_area > 0
                                ? "ASM: Sudah Visit ({$asmCount}x" . ($asmDate ? " — Terakhir: {$asmDate}" : '') . ")"
                                : 'ASM: Belum Visit';

                            $spvCount = (int) ($item->count_supervisor ?? 0);
                            $spvDate = !empty($item->last_visit_supervisor) ? \Carbon\Carbon::parse($item->last_visit_supervisor)->format('d/m/Y') : null;
                            $spvTitle = $item->visit_supervisor > 0
                                ? "SPV: Sudah Visit ({$spvCount}x" . ($spvDate ? " — Terakhir: {$spvDate}" : '') . ")"
                                : 'SPV: Belum Visit';
                        @endphp
                        <tr class="hover:bg-base-200/50 transition-colors">
                            <td class="text-center font-semibold text-base-content/50">
                                {{ $data->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="flex flex-col leading-tight max-w-[130px]">
                                    <span class="font-bold text-base-content/85 truncate" title="{{ $item->region_name }}">{{ $item->region_name ?: '-' }}</span>
                                    <span class="text-[10px] text-base-content/55 truncate" title="{{ $item->area_name }}">{{ $item->area_name ?: '-' }}</span>
                                </div>
                            </td>
                            <td class="truncate max-w-[140px]" title="{{ $item->supervisor_name }}">
                                <span class="font-medium text-base-content/80">{{ $item->supervisor_name ?: '-' }}</span>
                            </td>
                            <td class="truncate max-w-[160px]" title="{{ $item->distributor_name }}">
                                <div class="flex flex-col leading-tight">
                                    <span class="font-semibold truncate">{{ $item->distributor_name ?: '-' }}</span>
                                    <span class="text-[10px] font-mono text-base-content/50">{{ $item->distributor_code }}</span>
                                </div>
                            </td>
                            <td class="truncate max-w-[100px] font-mono font-semibold" title="{{ $item->customer_code }}">{{ $item->customer_code }}</td>
                            <td class="truncate max-w-[100px] font-mono text-base-content/75" title="{{ $item->uniq_kd }}">{{ $item->uniq_kd }}</td>
                            <td class="truncate max-w-[200px] font-bold" title="{{ $item->customer_name }}">{{ $item->customer_name }}</td>
                            <td class="text-center">
                                @if($isBB)
                                    <span class="badge badge-xs badge-secondary font-bold px-2 py-2">BB</span>
                                @else
                                    <span class="badge badge-xs badge-primary badge-outline font-bold px-2 py-2">CB</span>
                                @endif
                            </td>
                            <td class="text-center font-bold font-mono {{ $isBB ? 'text-secondary' : 'text-primary' }}">
                                @if($isBB)
                                    1 KTN
                                @else
                                    {{ rtrim(rtrim(number_format(($item->reward_percent ?? 0.015) * 100, 2, ',', '.'), '0'), ',') }}%
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-xs font-bold px-2 py-2 {{ $pic === 'RSM' ? 'badge-info text-white' : ($pic === 'ASM' ? 'badge-success text-white' : 'badge-ghost') }}">
                                    {{ $pic }}
                                </span>
                            </td>
                            <td class="text-center transition-colors {{ $item->visit_region > 0 ? 'bg-info/15 text-info font-bold' : 'text-base-content/25' }}"
                                title="{{ $rsmTitle }}">
                                @if($item->visit_region > 0)
                                    <div class="inline-flex items-center justify-center gap-0.5">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-info shrink-0" />
                                        @if($rsmCount > 1)
                                            <span class="text-[9px] font-mono text-info">{{ $rsmCount }}x</span>
                                        @endif
                                    </div>
                                @else
                                    <span>&mdash;</span>
                                @endif
                            </td>
                            <td class="text-center transition-colors {{ $item->visit_area > 0 ? 'bg-success/15 text-success font-bold' : 'text-base-content/25' }}"
                                title="{{ $asmTitle }}">
                                @if($item->visit_area > 0)
                                    <div class="inline-flex items-center justify-center gap-0.5">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-success shrink-0" />
                                        @if($asmCount > 1)
                                            <span class="text-[9px] font-mono text-success">{{ $asmCount }}x</span>
                                        @endif
                                    </div>
                                @else
                                    <span>&mdash;</span>
                                @endif
                            </td>
                            <td class="text-center transition-colors {{ $item->visit_supervisor > 0 ? 'bg-warning/15 text-warning font-bold' : 'text-base-content/25' }}"
                                title="{{ $spvTitle }}">
                                @if($item->visit_supervisor > 0)
                                    <div class="inline-flex items-center justify-center gap-0.5">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-warning shrink-0" />
                                        @if($spvCount > 1)
                                            <span class="text-[9px] font-mono text-warning-content">{{ $spvCount }}x</span>
                                        @endif
                                    </div>
                                @else
                                    <span>&mdash;</span>
                                @endif
                            </td>
                            <td class="text-center transition-colors {{ $item->status_visit === 'Y' ? 'bg-success/15 text-success font-bold' : 'bg-error/10 text-error font-bold' }}">
                                @if($item->status_visit === 'Y')
                                    <span class="badge badge-xs badge-success text-white font-bold gap-1 px-2 py-2" title="Sudah Dikunjungi">
                                        ✓ Tervisit
                                    </span>
                                @else
                                    <span class="badge badge-xs badge-error badge-outline font-bold px-2 py-2" title="Belum Dikunjungi">
                                        ✕ Belum
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-12 text-base-content/40 bg-base-100">
                                <x-heroicon-o-inbox class="w-10 h-10 mx-auto mb-3 opacity-50" />
                                <p>Tidak ada data monitoring ditemukan untuk filter dan rentang tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="sticky bottom-0 bg-base-300 border-t-2 border-base-300 shadow-[0_-1px_3px_rgba(0,0,0,0.05)] z-20">
                    <tr class="font-bold text-xs md:text-sm">
                        <td colspan="10" class="text-right uppercase tracking-wider">Subtotal</td>
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
        <div class="p-3 border-t border-base-300 shrink-0 bg-base-100 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="text-xs text-base-content/50 font-medium">
                @if($data->total() > 0)
                    Menampilkan <span class="font-bold text-base-content/70">{{ $data->firstItem() }}</span> –
                    <span class="font-bold text-base-content/70">{{ $data->lastItem() }}</span>
                    dari <span class="font-bold text-primary">{{ number_format($data->total(), 0, ',', '.') }}</span> outlet
                @else
                    Tidak ada data ditemukan
                @endif
            </div>
            @if($data->hasPages())
                {{ $data->links() }}
            @endif
        </div>
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

