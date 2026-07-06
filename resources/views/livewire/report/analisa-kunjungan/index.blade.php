<div class="flex-1 min-h-0 min-w-0 flex flex-col w-full h-full">
    <x-slot name="title">Analisa Kunjungan</x-slot>

    @push('styles')
    <link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
    <style>

        #visit-map {
            height: 400px;
            width: 100%;
            z-index: 10;
        }
        #all-visit-map {
            height: 500px;
            width: 100%;
            z-index: 10;
        }
    </style>
    @endpush

    {{-- TABS --}}
    <div class="shrink-0 -mx-3 md:-mx-4 lg:-mx-6 -mt-3 md:-mt-4 lg:-mt-6 px-3 md:px-4 lg:px-6 py-2 bg-base-100 border-b border-base-300 flex items-center shadow-sm relative z-10 mb-3 md:mb-4">
        <div class="tabs tabs-boxed w-fit bg-base-200 p-1">
            <button wire:click="setTab('summary')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'summary' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">Summary</button>
            <button wire:click="setTab('detail')" class="tab tab-xs px-4 transition-colors {{ $activeTab === 'detail' ? 'tab-active font-bold shadow-sm bg-base-100' : 'text-base-content/70 hover:text-base-content' }}">Detail Data</button>
        </div>
    </div>

    @if($activeTab === 'detail')
        @include('livewire.report.analisa-kunjungan.kpi-cards')
    @endif

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
    @php
        $user = auth()->user();
        $canAdd = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_add');
        $canEdit = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_edit');
        $canDelete = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_delete');
        $canExport = $user->hasMenuAccess('report.analisa-kunjungan.index', 'can_export');
    @endphp
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full xl:w-auto">
                <h2 class="text-base md:text-lg font-bold">Analisa Kunjungan</h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Filter data analisa kunjungan</p>
            </div>
            
            @if($activeTab === 'detail')
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-2">
                    <input type="date" wire:model="startDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                    <span class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">s/d</span>
                    <input type="date" wire:model="endDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                </div>
                
                <div class="flex items-center gap-2">
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedArea" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]" @if(!$selectedRegion) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="selectedSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]" @if(!$selectedArea) disabled @endif>
                        <option value="">Semua Supervisor</option>
                        @foreach($supervisors as $spv)
                            <option value="{{ $spv->supervisor_code }}">{{ $spv->supervisor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="map-points-data" data-points="{{ json_encode($this->mapPointsData) }}"></div>
                <div class="flex items-center gap-2">
                    @if(empty($selectedSupervisor))
                        <div class="tooltip tooltip-bottom" data-tip="Pilih Supervisor terlebih dahulu">
                            <x-ui.button class="rounded-xl" variant="primary" icon="magnifying-glass" size="sm" disabled>Terapkan</x-ui.button>
                        </div>
                    @else
                        <x-ui.button class="rounded-xl" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                    @endif
                    <div class="tooltip tooltip-bottom" data-tip="Reset Filter">
                        <x-ui.button class="rounded-xl" variant="neutral" icon="arrow-path" size="sm" wire:click="resetFilter" spinner="resetFilter"></x-ui.button>
                    </div>
                    <button type="button" x-data @click="$dispatch('open-all-maps-modal', JSON.parse(document.getElementById('map-points-data').dataset.points))" class="btn btn-sm btn-info rounded-xl text-white" @if(empty($this->mapPointsData)) disabled @endif>
                        <x-heroicon-o-map class="w-4 h-4" /> Maps
                    </button>
                    @if($canExport)
                        @php
                            $isDetailExportDisabled = empty($appliedRegion) && empty($appliedArea) && empty($appliedSupervisor);
                            $hasPendingDetailFilters = $selectedRegion !== $appliedRegion || 
                                                       $selectedArea !== $appliedArea || 
                                                       $selectedSupervisor !== $appliedSupervisor || 
                                                       $startDate !== $appliedStartDate || 
                                                       $endDate !== $appliedEndDate;
                        @endphp
                        <div class="tooltip tooltip-left" data-tip="{{ $isDetailExportDisabled || $hasPendingDetailFilters ? 'Klik Terapkan terlebih dahulu' : 'Export ke Excel' }}">
                            <x-ui.button variant="success" size="sm" class="rounded-xl text-white" wire:click="export" spinner="export" icon="arrow-down-tray" :disabled="$isDetailExportDisabled || $hasPendingDetailFilters">
                                Export
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>
            @elseif($activeTab === 'summary')
            <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                <div class="flex items-center gap-2">
                    <input type="date" wire:model="summaryStartDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                    <span class="text-[10px] font-semibold text-base-content/60 uppercase tracking-wider">s/d</span>
                    <input type="date" wire:model="summaryEndDate" class="input input-sm input-bordered rounded-xl bg-base-100 w-[120px] sm:w-[130px]" />
                </div>
                
                <div class="flex items-center gap-2">
                    <select wire:model="summaryRegion" class="select select-sm select-bordered rounded-xl bg-base-100 w-[140px]">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-sm btn-outline border-base-300 hover:bg-base-200 hover:text-base-content rounded-xl bg-base-100 font-normal w-[140px] flex justify-between px-3">
                            <span class="truncate">
                                @if(count($summaryLevels) === 0)
                                    Semua Level
                                @else
                                    {{ count($summaryLevels) }} Dipilih
                                @endif
                            </span>
                            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 opacity-60 shrink-0" />
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[50] menu p-2 shadow-lg bg-base-100 rounded-xl w-48 mt-1 border border-base-300">
                            <li>
                                <label class="label cursor-pointer justify-start gap-3 px-2 py-1.5">
                                    <input type="checkbox" wire:model="summaryLevels" value="region" class="checkbox checkbox-sm checkbox-primary rounded-md" />
                                    <span class="label-text">Region</span>
                                </label>
                            </li>
                            <li>
                                <label class="label cursor-pointer justify-start gap-3 px-2 py-1.5">
                                    <input type="checkbox" wire:model="summaryLevels" value="area" class="checkbox checkbox-sm checkbox-primary rounded-md" />
                                    <span class="label-text">Area</span>
                                </label>
                            </li>
                            <li>
                                <label class="label cursor-pointer justify-start gap-3 px-2 py-1.5">
                                    <input type="checkbox" wire:model="summaryLevels" value="supervisor" class="checkbox checkbox-sm checkbox-primary rounded-md" />
                                    <span class="label-text">Supervisor</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.button class="rounded-xl" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                    <div class="tooltip tooltip-bottom" data-tip="Reset Filter">
                        <x-ui.button class="rounded-xl" variant="neutral" icon="arrow-path" size="sm" wire:click="resetFilter" spinner="resetFilter"></x-ui.button>
                    </div>
                    @if($canExport)
                        @php
                            $isSummaryExportDisabled = empty($appliedSummaryRegion) && empty($appliedSummaryStartDate) && empty($appliedSummaryEndDate) && empty($appliedSummaryLevels);
                            $hasPendingSummaryFilters = $summaryRegion !== $appliedSummaryRegion || 
                                                        $summaryStartDate !== $appliedSummaryStartDate || 
                                                        $summaryEndDate !== $appliedSummaryEndDate ||
                                                        $summaryLevels !== $appliedSummaryLevels;
                        @endphp
                        <div class="tooltip tooltip-left" data-tip="{{ $isSummaryExportDisabled || $hasPendingSummaryFilters ? 'Klik Terapkan terlebih dahulu' : 'Export Summary ke Excel' }}">
                            <x-ui.button variant="success" size="sm" class="rounded-xl text-white" wire:click="exportSummary" spinner="exportSummary" icon="arrow-down-tray" :disabled="$isSummaryExportDisabled || $hasPendingSummaryFilters">
                                Export
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        @if($activeTab === 'detail')
        {{-- Table Container --}}
        <div class="overflow-x-auto min-h-0 flex-1 relative">
            <table class="table table-xs table-zebra table-pin-rows w-full whitespace-nowrap text-[10px]">
                <thead class="text-[10px] uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm [&_th]:bg-base-300">
                    <tr class="[&>th]:align-middle">
                        <th class="align-middle">Tanggal</th>
                        <th class="align-middle">SPV Code</th>
                        <th class="align-middle">SPV Name</th>
                        <th class="align-middle">Cust No</th>
                        <th class="align-middle">Cust Name</th>
                        <th class="align-middle">Pilar</th>
                        <th class="align-middle">Target</th>
                        <th class="align-middle text-center min-w-[80px]">Start</th>
                        <th class="align-middle text-center min-w-[80px]">End</th>
                        <th class="align-middle text-center min-w-[80px] whitespace-normal leading-tight">Minute<br>per outlet</th>
                        <th class="align-middle text-center min-w-[80px] whitespace-normal leading-tight">Time<br>Travel</th>
                        <th class="align-middle text-center min-w-[80px] whitespace-normal leading-tight">Time<br>Pause</th>
                        <th class="align-middle text-center">Visit</th>
                        <th class="align-middle text-center">Order</th>
                        <th class="align-middle text-center">Distance</th>
                        <th class="align-middle text-center">Reason</th>
                        <th class="align-middle text-center">Remark</th>
                    </tr>
                </thead>
                <tbody class="text-[10px] relative" wire:loading.class="opacity-50 pointer-events-none transition-opacity duration-200" wire:target="applyFilter, resetFilter, previousPage, nextPage, gotoPage">
                @php
                    $groupedData = collect($dataKunjungan->items())->groupBy('tanggal');
                @endphp
                @forelse($groupedData as $tanggal => $rows)
                    @foreach($rows as $row)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td class="whitespace-nowrap">{{ $row->tanggal }}</td>
                        <td class="whitespace-nowrap">{{ $row->supervisor_code }}</td>
                        <td class="max-w-[150px] truncate" title="{{ $row->supervisor_name }}">{{ $row->supervisor_name }}</td>
                        <td class="whitespace-nowrap">{{ $row->custno }}</td>
                        <td class="max-w-[150px] truncate {{ $row->flag_pjp == 'N' ? 'text-error font-bold' : '' }}" title="{{ $row->custname }}">{{ $row->custname }}</td>
                        <td class="whitespace-nowrap">{{ $row->pilar }}</td>
                        <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->target ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_in }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_out }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_consume }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_travel }}</td>
                        <td class="text-center whitespace-nowrap">{{ $row->time_pause }}</td>
                        
                        <td class="text-center whitespace-nowrap">
                            @if($row->flag_visit == 'Y')
                                <button type="button" class="btn btn-xs btn-success text-white w-8 h-8 rounded-full p-0 cursor-default">Y</button>
                            @else
                                <button type="button" class="btn btn-xs btn-error text-white w-8 h-8 rounded-full p-0 cursor-default">N</button>
                            @endif
                        </td>

                        <td class="text-center whitespace-nowrap">
                            @if($row->flag_ec == 'Y')
                                <button type="button" x-data="{ qty: {{ (float)($row->qty_order ?? 0) }}, val: {{ (float)($row->val_order ?? 0) }} }" @click="$dispatch('open-order-modal', {qty, val})" class="btn btn-xs btn-success text-white w-8 h-8 rounded-full p-0">Y</button>
                            @else
                                <button type="button" class="btn btn-xs btn-error text-white w-8 h-8 rounded-full p-0 cursor-default">N</button>
                            @endif
                        </td>
                        
                        <td class="text-center whitespace-nowrap">
                            @php
                                $dist = $this->getDistance($row->master_lat, $row->master_lon, $row->visit_lat, $row->visit_lon);
                            @endphp
                            <button type="button" x-data @click="$dispatch('open-map-modal', {masterLat: '{{ $row->master_lat }}', masterLon: '{{ $row->master_lon }}', visitLat: '{{ $row->visit_lat }}', visitLon: '{{ $row->visit_lon }}'})" class="btn btn-xs rounded-xl {{ $dist > 50 ? 'btn-error text-white border-none shadow-sm shadow-error/50' : 'btn-outline btn-info' }}">
                                {{ $dist }}m
                            </button>
                        </td>

                        <td class="text-center whitespace-nowrap">
                            @if($row->reason_type || $row->reason_desc)
                            <button type="button" x-data="{{ json_encode(['type' => $row->reason_type, 'desc' => $row->reason_desc]) }}" @click="$dispatch('open-reason-modal', {type: type, desc: desc})" class="btn btn-xs btn-outline btn-warning rounded-xl">Detail</button>
                            @else
                            -
                            @endif
                        </td>
                        
                        <td class="text-center whitespace-nowrap">
                            @if($row->action_remark)
                                <div class="flex items-center justify-center gap-1">
                                    @if($canEdit)
                                    <button type="button" x-data="{{ json_encode(['remark' => $row->action_remark]) }}" @click="$wire.openRemarkModal('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}', remark)" class="btn btn-xs btn-ghost text-info p-0 w-6 h-6 rounded-full" title="Lihat/Edit Remark" wire:loading.attr="disabled" wire:target="deleteRemark('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}')"><x-heroicon-s-eye class="w-4 h-4" /></button>
                                    @endif
                                    @if($canDelete)
                                    <button type="button" wire:click="deleteRemark('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}')" wire:confirm="Yakin ingin menghapus remark ini?" class="btn btn-xs btn-ghost text-error p-0 w-6 h-6 rounded-full" title="Hapus Remark" wire:loading.attr="disabled">
                                        <x-heroicon-s-trash class="w-4 h-4" wire:loading.remove wire:target="deleteRemark('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}')" />
                                        <span class="loading loading-spinner w-4 h-4" wire:loading wire:target="deleteRemark('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}')"></span>
                                    </button>
                                    @endif
                                </div>
                            @else
                                @if($canAdd)
                                <button type="button" wire:click="openRemarkModal('{{ $row->id }}', '{{ $row->supervisor_code }}', '{{ $row->custno }}', '{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}', '')" class="btn btn-xs btn-ghost text-base-content/40 hover:text-primary p-0 w-6 h-6 rounded-full" title="Isi Remark"><x-heroicon-s-pencil class="w-4 h-4" /></button>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="!bg-primary/10 font-bold border-t-2 border-primary/20">
                        <td colspan="6" class="text-right py-2 text-primary">Subtotal Tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}:</td>
                        <td class="whitespace-nowrap font-mono text-right text-primary py-2">{{ number_format($rows->sum('target'), 0, ',', '.') }}</td>
                        <td colspan="6"></td>
                        <td class="whitespace-nowrap font-mono text-center text-primary py-2" title="Total Value Order">Rp {{ number_format($rows->sum('val_order'), 0, ',', '.') }}</td>
                        <td colspan="3"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center py-12 text-base-content/40">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <x-heroicon-o-inbox class="w-10 h-10" />
                                <p class="text-sm">Tidak ada data ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-2 md:p-3 border-t border-base-300 bg-base-200/30 shrink-0">
            @if(empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor))
                <div class="text-center text-xs text-base-content/50 italic py-2">
                    Gunakan filter untuk menampilkan data dan paginasi.
                </div>
            @else
                {{ $dataKunjungan->links() }}
            @endif
        </div>
        @elseif($activeTab === 'summary')
        {{-- Summary Table Container --}}
        <div class="overflow-x-auto min-h-0 flex-1 relative">
            
            {{-- Loading Overlay --}}
            <div wire:loading wire:target="applyFilter, resetFilter, setTab, exportSummary" class="absolute inset-0 bg-base-100/50 backdrop-blur-sm z-50 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 bg-base-100 p-4 rounded-xl shadow-xl border border-base-300">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-xs font-semibold text-base-content/70">Memuat Data...</span>
                </div>
            </div>

            <table class="table table-xs table-pin-rows w-full text-[11px] [&_th]:px-2 [&_td]:px-2 [&_th]:py-1.5 [&_td]:py-1.5">
                <thead class="bg-base-200 text-base-content z-10 sticky top-0">
                    <tr>
                        <th class="align-middle text-center" rowspan="2">No</th>
                        <th class="align-middle" rowspan="2">Region</th>
                        <th class="align-middle" rowspan="2">Area</th>
                        <th class="align-middle" rowspan="2">Level</th>
                        <th class="align-middle border-r border-base-300" rowspan="2">Supervisor</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300" colspan="5">Kunjungan (Visit)</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300 bg-base-200/50" colspan="3">Order (Value)</th>
                        <th class="align-middle text-center border-b-0 border-r border-base-300" colspan="8">Pilar Actual</th>
                        <th class="align-middle text-center border-b-0 border-base-300 bg-base-200/50" colspan="2">Out of Area <span class="text-[9px] font-normal opacity-75">(> 50m)</span></th>
                    </tr>
                    <tr>
                        <th class="align-middle text-center bg-base-200/90">PC</th>
                        <th class="align-middle text-center bg-base-200/90">AC</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90">EC</th>
                        <th class="align-middle text-center bg-base-200/90 border-r border-base-300">%</th>
                        <th class="align-middle text-center bg-base-200/70">Target</th>
                        <th class="align-middle text-center bg-base-200/70">Order</th>
                        <th class="align-middle text-center bg-base-200/70 border-r border-base-300">%</th>
                        <th class="align-middle text-center bg-base-200/90">RWO</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90">PNR</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90">NGVO</th>
                        <th class="align-middle text-center bg-base-200/90">%</th>
                        <th class="align-middle text-center bg-base-200/90" title="RWO + PNR + NGVO">Pareto</th>
                        <th class="align-middle text-center bg-base-200/90 border-r border-base-300">%</th>
                        <th class="align-middle text-center bg-base-200/70">Toko</th>
                        <th class="align-middle text-center bg-base-200/70">%</th>
                    </tr>
                </thead>
                <tbody class="text-[11px] relative">
                @php
                    $sumData = $this->summaryData;
                    
                    $totPc = 0; $totAc = 0; $totEc = 0; $totTarget = 0; $totOrder = 0;
                    $totRwo = 0; $totPnr = 0; $totNgvo = 0; $totOoa = 0;
                @endphp
                @forelse($sumData as $i => $row)
                    @php
                        $totPc += $row['pc'];
                        $totAc += $row['ac'];
                        $totEc += $row['ec'];
                        $totTarget += $row['target'];
                        $totOrder += $row['order'];
                        $totRwo += $row['rwo'];
                        $totPnr += $row['pnr'];
                        $totNgvo += $row['ngvo'];
                        $totOoa += $row['out_of_area'];
                    @endphp
                    <tr class="hover">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row['region_name'] }}</td>
                        <td>{{ $row['area_name'] }}</td>
                        <td class="font-semibold">{{ $row['level'] }}</td>
                        <td class="border-r border-base-300">
                            <div class="font-bold uppercase">{{ $row['supervisor_name'] }}</div>
                        </td>
                        <td class="text-center font-medium">{{ number_format($row['pc'], 0, ',', '.') }}</td>
                        <td class="text-center font-medium">{{ number_format($row['ac'], 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($row['pc_ac_pct'] < 50) <span class="text-error font-bold">{{ number_format($row['pc_ac_pct'], 1, ',', '.') }}%</span>
                            @elseif($row['pc_ac_pct'] < 80) <span class="text-warning font-bold">{{ number_format($row['pc_ac_pct'], 1, ',', '.') }}%</span>
                            @else <span class="text-success font-bold">{{ number_format($row['pc_ac_pct'], 1, ',', '.') }}%</span> @endif
                        </td>
                        <td class="text-center font-medium">{{ number_format($row['ec'], 0, ',', '.') }}</td>
                        <td class="text-center border-r border-base-300">
                            @if($row['ec_pct'] < 50) <span class="text-error font-bold">{{ number_format($row['ec_pct'], 1, ',', '.') }}%</span>
                            @elseif($row['ec_pct'] < 80) <span class="text-warning font-bold">{{ number_format($row['ec_pct'], 1, ',', '.') }}%</span>
                            @else <span class="text-success font-bold">{{ number_format($row['ec_pct'], 1, ',', '.') }}%</span> @endif
                        </td>
                        <td class="text-right text-base-content/80 bg-base-200/20">Rp {{ number_format($row['target'], 0, ',', '.') }}</td>
                        <td class="text-right font-medium bg-base-200/20">Rp {{ number_format($row['order'], 0, ',', '.') }}</td>
                        <td class="text-center border-r border-base-300 bg-base-200/20">
                            @if($row['target_order_pct'] < 50) <span class="text-error font-bold">{{ number_format($row['target_order_pct'], 1, ',', '.') }}%</span>
                            @elseif($row['target_order_pct'] < 80) <span class="text-warning font-bold">{{ number_format($row['target_order_pct'], 1, ',', '.') }}%</span>
                            @else <span class="text-success font-bold">{{ number_format($row['target_order_pct'], 1, ',', '.') }}%</span> @endif
                        </td>
                        <td class="text-center">{{ number_format($row['rwo'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $row['rwo_pct'] < 50 ? 'text-error' : ($row['rwo_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['rwo_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center">{{ number_format($row['pnr'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $row['pnr_pct'] < 50 ? 'text-error' : ($row['pnr_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['pnr_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center">{{ number_format($row['ngvo'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $row['ngvo_pct'] < 50 ? 'text-error' : ($row['ngvo_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['ngvo_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center font-bold text-base-content/80">{{ number_format($row['pareto'], 0, ',', '.') }}</td>
                        <td class="text-center border-r border-base-300 font-bold {{ $row['pareto_pct'] < 50 ? 'text-error' : ($row['pareto_pct'] < 80 ? 'text-warning' : 'text-success') }}">{{ number_format($row['pareto_pct'], 1, ',', '.') }}%</td>
                        <td class="text-center bg-base-200/20">
                            @if($row['out_of_area'] > 0)
                                <span class="badge badge-error badge-sm text-[10px]">{{ number_format($row['out_of_area'], 0, ',', '.') }}</span>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                        <td class="text-center font-bold bg-base-200/20 {{ $row['out_of_area_pct'] > 0 ? 'text-error' : 'text-base-content/40' }}">{{ number_format($row['out_of_area_pct'], 1, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center py-10">
                            <x-heroicon-o-inbox class="w-10 h-10 mx-auto text-base-content/20 mb-3" />
                            <p class="text-base-content/50 font-medium">Tidak ada data untuk summary</p>
                            <p class="text-[10px] text-base-content/40 mt-1">Silakan sesuaikan filter di atas</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
                @if(count($sumData) > 0)
                <tfoot class="bg-base-200/80 text-base-content font-bold sticky bottom-0 z-10">
                    <tr>
                        <td colspan="5" class="text-right uppercase tracking-wider text-[11px] border-r border-base-300">Total Kumulatif</td>
                        <td class="text-center">{{ number_format($totPc, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($totAc, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totAc / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totEc, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px] border-r border-base-300">{{ $totPc > 0 ? number_format(($totEc / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-right bg-base-200/50">Rp {{ number_format($totTarget, 0, ',', '.') }}</td>
                        <td class="text-right text-primary text-[11px] bg-base-200/50">Rp {{ number_format($totOrder, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px] border-r border-base-300 bg-base-200/50">{{ $totTarget > 0 ? number_format(($totOrder / $totTarget) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totRwo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totRwo / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totPnr, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totPnr / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totNgvo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px]">{{ $totPc > 0 ? number_format(($totNgvo / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center">{{ number_format($totRwo + $totPnr + $totNgvo, 0, ',', '.') }}</td>
                        <td class="text-center text-primary text-[11px] border-r border-base-300">{{ $totPc > 0 ? number_format((($totRwo + $totPnr + $totNgvo) / $totPc) * 100, 1, ',', '.') : '0,0' }}%</td>
                        <td class="text-center text-error bg-base-200/50">{{ number_format($totOoa, 0, ',', '.') }}</td>
                        <td class="text-center text-error text-[11px] bg-base-200/50">{{ $totAc > 0 ? number_format(($totOoa / $totAc) * 100, 1, ',', '.') : '0,0' }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @endif
    </div>

    {{-- Modal Order --}}
    <div x-data="{ open: false, qty: 0, val: 0 }" 
         @open-order-modal.window="qty = $event.detail.qty; val = $event.detail.val; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Detail Order</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Qty Order</p>
                        <p class="text-xl font-bold" x-text="Number(qty).toLocaleString('id-ID')"></p>
                    </div>
                    <div class="p-4 bg-base-200 rounded-xl">
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Val Order</p>
                        <p class="text-xl font-bold font-mono">Rp <span x-text="Number(val).toLocaleString('id-ID')"></span></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Reason --}}
    <div x-data="{ open: false, type: '', desc: '' }"
         @open-reason-modal.window="type = $event.detail.type; desc = $event.detail.desc; open = true">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Detail Reason</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Type</p>
                        <p class="text-sm font-medium" x-text="type || '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/60 font-semibold mb-1">Reason Desc</p>
                        <p class="text-sm" x-text="desc || '-'"></p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Action Remark --}}
    <div x-data="{ open: false }" 
         @open-modal.window="if (Array.isArray($event.detail) ? $event.detail[0] === 'modal-action-remark' : $event.detail === 'modal-action-remark') { open = true; }"
         @close-modal.window="if (Array.isArray($event.detail) ? $event.detail[0] === 'modal-action-remark' : $event.detail === 'modal-action-remark') { open = false; }">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Form Remark</h3>
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">Isi Remark</span>
                    </label>
                    <textarea wire:model="modalRemarkText" class="textarea textarea-bordered h-24 @error('modalRemarkText') textarea-error @enderror" placeholder="Ketik remark di sini..."></textarea>
                    @error('modalRemarkText')
                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-outline" @click="open = false">Batal</button>
                    <button type="button" wire:click="saveRemark" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveRemark" class="loading loading-spinner"></span>
                        Simpan
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal Map --}}
    <div x-data="{ 
            open: false,
            mapData: null,
            initMap(data) {
                if(data) this.mapData = data;
                else data = this.mapData;
                
                this.open = true;

                if (typeof maplibregl === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js';
                    script.onload = () => this.initMap(data);
                    document.head.appendChild(script);

                    const css = document.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css';
                    document.head.appendChild(css);
                    return;
                }

                setTimeout(() => {
                    if (this.mapInstance) {
                        this.mapInstance.remove();
                        this.mapInstance = null;
                    }
                    
                    this.mapInstance = new maplibregl.Map({
                        container: 'visit-map',
                        style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                        center: [118.0148, -2.5489],
                        zoom: 5
                    });

                    let mLat = parseFloat(data.masterLat);
                    let mLon = parseFloat(data.masterLon);
                    let vLat = parseFloat(data.visitLat);
                    let vLon = parseFloat(data.visitLon);

                    // Deteksi jika koordinat master terbalik (lat > 90)
                    if (Math.abs(mLat) > 90) {
                        const temp = mLat; mLat = mLon; mLon = temp;
                    }
                    // Deteksi jika koordinat visit terbalik (lat > 90)
                    if (Math.abs(vLat) > 90) {
                        const temp = vLat; vLat = vLon; vLon = temp;
                    }

                    const bounds = new maplibregl.LngLatBounds();

                    let hasMaster = !isNaN(mLat) && !isNaN(mLon);
                    let hasVisit = !isNaN(vLat) && !isNaN(vLon);

                    if (hasMaster) {
                        new maplibregl.Marker({ color: '#ef4444' })
                            .setLngLat([mLon, mLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Master Point</strong><br><span class='text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary' onclick='window.open(\`https://www.google.com/maps/search/?api=1&query=${mLat},${mLon}\`, \`_blank\`);' title='Buka di Google Maps'>📍 ${mLat}, ${mLon}</span></div>`))
                            .addTo(this.mapInstance);
                        bounds.extend([mLon, mLat]);
                    }

                    if (hasVisit) {
                        new maplibregl.Marker({ color: '#3b82f6' })
                            .setLngLat([vLon, vLat])
                            .setPopup(new maplibregl.Popup({ offset: 25 }).setHTML(`<div class='text-black'><strong>Visit Point</strong><br><span class='text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary' onclick='window.open(\`https://www.google.com/maps/search/?api=1&query=${vLat},${vLon}\`, \`_blank\`);' title='Buka di Google Maps'>📍 ${vLat}, ${vLon}</span></div>`))
                            .addTo(this.mapInstance);
                        bounds.extend([vLon, vLat]);
                    }

                    if (!bounds.isEmpty()) {
                        this.mapInstance.fitBounds(bounds, { padding: 50, maxZoom: 18, animate: false });
                    }

                    const initRoute = () => {
                        if (hasMaster && hasVisit) {
                            this.mapInstance.addSource('route', {
                                'type': 'geojson',
                                'data': {
                                    'type': 'Feature',
                                    'geometry': {
                                        'type': 'LineString',
                                        'coordinates': [
                                            [mLon, mLat],
                                            [vLon, vLat]
                                        ]
                                    }
                                }
                            });
                            this.mapInstance.addLayer({
                                'id': 'route',
                                'type': 'line',
                                'source': 'route',
                                'layout': {
                                    'line-join': 'round',
                                    'line-cap': 'round'
                                },
                                'paint': {
                                    'line-color': '#22c55e',
                                    'line-width': 3,
                                    'line-dasharray': [2, 2]
                                }
                            });
                        }
                    };

                    if (this.mapInstance.loaded()) {
                        initRoute();
                    } else {
                        this.mapInstance.on('load', initRoute);
                    }
                    
                    setTimeout(() => { if (this.mapInstance) this.mapInstance.resize(); }, 300);
                }, 300);
            }
         }"
         @open-map-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Peta Kunjungan vs Master</h3>
                <div id="visit-map" wire:ignore class="rounded-xl border border-base-300"></div>
                <div class="flex gap-4 mt-4 items-center">
                    <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 bg-red-500 rounded-full"></div> Master Point</div>
                    <div class="flex items-center gap-2 text-sm"><div class="w-3 h-3 bg-blue-500 rounded-full"></div> Visit Point</div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-info text-white" @click="initMap()" title="Klik jika peta tidak sejajar/blank">
                        <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh Peta
                    </button>
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    {{-- Modal All Maps --}}
    <div x-data="{ 
            open: false,
            mapPoints: null,
            initMap(points) {
                if(points) this.mapPoints = points;
                else points = this.mapPoints;
                
                this.open = true;

                if (typeof maplibregl === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js';
                    script.onload = () => this.initMap(points);
                    document.head.appendChild(script);

                    const css = document.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = 'https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css';
                    document.head.appendChild(css);
                    return;
                }

                setTimeout(() => {
                    try {
                        if (window.allVisitMapInstance) {
                            window.allVisitMapInstance.remove();
                            window.allVisitMapInstance = null;
                        }
                        
                        const pointsArray = Array.isArray(points) ? points : (points ? Object.values(points) : []);
                        
                        const features = pointsArray.map(pt => {
                            let lat = parseFloat(pt.lat);
                            let lon = parseFloat(pt.lon);
                            
                            if (Math.abs(lat) > 90) {
                                const temp = lat;
                                lat = lon;
                                lon = temp;
                            }

                            if (isNaN(lat) || isNaN(lon)) return null;
                            
                            return {
                                'type': 'Feature',
                                'properties': {
                                    'description': `<div class='text-black'><strong>${pt.name}</strong><br>Tgl: ${pt.date}<br>SPV: ${pt.spv}<br><span class='text-[10px] text-gray-500 font-mono cursor-pointer hover:text-primary' onclick='window.open(\`https://www.google.com/maps/search/?api=1&query=${lat},${lon}\`, \`_blank\`);' title='Buka di Google Maps'>📍 ${lat}, ${lon}</span></div>`
                                },
                                'geometry': {
                                    'type': 'Point',
                                    'coordinates': [lon, lat]
                                }
                            };
                        }).filter(f => f !== null);

                        window.allVisitMapInstance = new maplibregl.Map({
                            container: 'all-visit-map',
                            style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
                            center: [118.0148, -2.5489],
                            zoom: 5
                        });

                        const initSimpleCluster = () => {
                            try {
                                if (!window.allVisitMapInstance.getSource('visits')) {
                                    window.allVisitMapInstance.addSource('visits', {
                                        type: 'geojson',
                                        data: {
                                            type: 'FeatureCollection',
                                            features: features
                                        },
                                        cluster: true,
                                        clusterMaxZoom: 14,
                                        clusterRadius: 50
                                    });
                                }

                                if (!window.allVisitMapInstance.getLayer('clusters')) {
                                    window.allVisitMapInstance.addLayer({
                                        id: 'clusters',
                                        type: 'circle',
                                        source: 'visits',
                                        filter: ['has', 'point_count'],
                                        paint: {
                                            'circle-color': [
                                                'step',
                                                ['get', 'point_count'],
                                                '#51bbd6',
                                                10,
                                                '#f59e0b',
                                                20,
                                                '#ef4444'
                                            ],
                                            'circle-radius': [
                                                'step',
                                                ['get', 'point_count'],
                                                20,
                                                10,
                                                25,
                                                20,
                                                30
                                            ],
                                            'circle-stroke-width': 2,
                                            'circle-stroke-color': '#fff'
                                        }
                                    });
                                }

                                if (!window.allVisitMapInstance.getLayer('cluster-count')) {
                                    window.allVisitMapInstance.addLayer({
                                        id: 'cluster-count',
                                        type: 'symbol',
                                        source: 'visits',
                                        filter: ['has', 'point_count'],
                                        layout: {
                                            'text-field': '{point_count_abbreviated}',
                                            'text-font': ['Open Sans Regular'],
                                            'text-size': 12,
                                            'text-allow-overlap': true
                                        }
                                    });
                                }

                                if (!window.allVisitMapInstance.getLayer('unclustered-point')) {
                                    window.allVisitMapInstance.addLayer({
                                        id: 'unclustered-point',
                                        type: 'circle',
                                        source: 'visits',
                                        filter: ['!', ['has', 'point_count']],
                                        paint: {
                                            'circle-color': '#3b82f6',
                                            'circle-radius': 8,
                                            'circle-stroke-width': 2,
                                            'circle-stroke-color': '#ffffff'
                                        }
                                    });
                                }
                            } catch(err) {
                                console.error('Cluster Error:', err);
                            }
                        };

                        if (window.allVisitMapInstance.loaded()) {
                            initSimpleCluster();
                        } else {
                            window.allVisitMapInstance.on('load', initSimpleCluster);
                        }

                        const bounds = new maplibregl.LngLatBounds();
                        features.forEach(f => bounds.extend(f.geometry.coordinates));

                        if (!bounds.isEmpty()) {
                            window.allVisitMapInstance.fitBounds(bounds, { padding: 50, maxZoom: 18, animate: false });
                        }

                        window.allVisitMapInstance.on('click', 'clusters', (e) => {
                            const mapFeatures = window.allVisitMapInstance.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                            const clusterId = mapFeatures[0].properties.cluster_id;
                            window.allVisitMapInstance.getSource('visits').getClusterExpansionZoom(clusterId).then((zoom) => {
                                window.allVisitMapInstance.easeTo({
                                    center: mapFeatures[0].geometry.coordinates,
                                    zoom: zoom
                                });
                            });
                        });

                        window.allVisitMapInstance.on('click', 'unclustered-point', (e) => {
                            const coordinates = e.features[0].geometry.coordinates.slice();
                            const description = e.features[0].properties.description;
                            
                            while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                                coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                            }

                            new maplibregl.Popup({ offset: 10 })
                                .setLngLat(coordinates)
                                .setHTML(description)
                                .addTo(window.allVisitMapInstance);
                        });

                        window.allVisitMapInstance.on('mouseenter', 'clusters', () => { window.allVisitMapInstance.getCanvas().style.cursor = 'pointer'; });
                        window.allVisitMapInstance.on('mouseleave', 'clusters', () => { window.allVisitMapInstance.getCanvas().style.cursor = ''; });
                        window.allVisitMapInstance.on('mouseenter', 'unclustered-point', () => { window.allVisitMapInstance.getCanvas().style.cursor = 'pointer'; });
                        window.allVisitMapInstance.on('mouseleave', 'unclustered-point', () => { window.allVisitMapInstance.getCanvas().style.cursor = ''; });

                        setTimeout(() => { if (window.allVisitMapInstance) window.allVisitMapInstance.resize(); }, 300);
                        
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan saat memuat peta: ' + e.message);
                    }
                }, 300);
            }
         }"
         @open-all-maps-modal.window="initMap($event.detail)">
        <div class="modal" :class="{'modal-open': open}">
            <div class="modal-box w-11/12 max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Sebaran Titik Visit Kunjungan</h3>
                <div id="all-visit-map" wire:ignore class="rounded-xl border border-base-300"></div>
                <div class="modal-action">
                    <button type="button" class="btn btn-info text-white" @click="initMap()" title="Klik jika peta tidak sejajar/blank">
                        <x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh Peta
                    </button>
                    <button type="button" class="btn" @click="open = false">Tutup</button>
                </div>
            </div>
            <div class="modal-backdrop" @click="open = false"></div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    @endpush

    @script
    <script>
        $wire.on('notify', (event) => {
            // Handle differences between Livewire 2 (Array) and Livewire 3 (Object) payload
            const data = Array.isArray(event) ? event[0] : event;
            const msg = data?.msg || data?.message || (typeof data === 'string' ? data : JSON.stringify(data));
            const type = data?.type || 'info';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
                    title: msg,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            } else {
                alert(msg);
            }
        });
    </script>
    @endscript
</div>
