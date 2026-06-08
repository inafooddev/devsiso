<div>
    <!-- TABS -->
    <div class="tabs tabs-boxed mb-4 w-fit bg-base-100 shadow-sm border border-base-200 p-1">
        <button class="tab px-8 {{ $currentTab === 'summary' ? 'tab-active font-bold' : '' }}" wire:click="setTab('summary')">Summary</button>
        <button class="tab px-8 {{ $currentTab === 'detail' ? 'tab-active font-bold' : '' }}" wire:click="setTab('detail')">Detail</button>
    </div>

    @if($currentTab === 'summary')
    <x-card class="mb-4" flush="true">
        <div class="p-4 border-b border-base-200 bg-base-50/50">
            <div class="flex flex-row items-center gap-2">
                <input type="date" wire:model.live="startDate" class="input input-sm input-bordered" />
                <span class="text-sm font-medium">s/d</span>
                <input type="date" wire:model.live="endDate" class="input input-sm input-bordered" />
                <button wire:click="applyFilter" class="btn btn-sm btn-primary">Terapkan</button>
                <button wire:click="resetFilter" class="btn btn-sm btn-ghost">Reset</button>
            </div>
        </div>
        
        <x-ui.table striped hover class="h-[500px] overflow-auto border-x-0 border-b-0 rounded-none shadow-none text-xs [&_thead]:sticky [&_thead]:top-0 [&_thead]:z-20 [&_thead]:shadow-sm">
                <x-slot:head>
                    <tr>
                        <th rowspan="2" class="align-middle border-b border-r border-base-200">Region</th>
                        <th rowspan="2" class="align-middle border-b border-r border-base-200">Area</th>
                        <th rowspan="2" class="align-middle border-b border-r border-base-200">Team (Supervisor)</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">Total Toko</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">Value</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">1. RWO</th>
                        <th colspan="3" class="text-center border-b border-r border-base-200">2. PNR</th>
                        <th colspan="3" class="text-center border-b border-base-200">3. NGVO</th>
                    </tr>
                    <tr>
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-right border-b border-base-200">Target</th>
                        <th class="text-right border-b border-base-200">Order</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-r border-base-200">%</th>
                        
                        <th class="text-center border-b border-base-200">Plan</th>
                        <th class="text-center border-b border-base-200">Act</th>
                        <th class="text-center border-b border-base-200">%</th>
                    </tr>
                </x-slot:head>
                
                @foreach($dataSummary as $row)
                    <tr>
                        <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->nama_region }}</td>
                        <td class="whitespace-nowrap border-r border-base-200/50">{{ $row->nama_area }}</td>
                        <td class="whitespace-nowrap font-medium border-r border-base-200/50">{{ $row->nama_team }}</td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->total_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->total_visit, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $pct = $row->total_plan > 0 ? ($row->total_visit / $row->total_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($pct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->total_target ?? 0, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->total_order ?? 0, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $valPct = $row->total_target > 0 ? ($row->total_order / $row->total_target) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $valPct >= 80 ? 'success' : ($valPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($valPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->rwo_actual, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $rwoPct = $row->rwo_plan > 0 ? ($row->rwo_actual / $row->rwo_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $rwoPct >= 80 ? 'success' : ($rwoPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($rwoPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->pnr_actual, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center border-r border-base-200/50">
                            @php
                                $pnrPct = $row->pnr_plan > 0 ? ($row->pnr_actual / $row->pnr_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $pnrPct >= 80 ? 'success' : ($pnrPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($pnrPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                        
                        <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_plan, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">{{ number_format($row->ngvo_actual, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center">
                            @php
                                $ngvoPct = $row->ngvo_plan > 0 ? ($row->ngvo_actual / $row->ngvo_plan) * 100 : 0;
                            @endphp
                            <x-ui.badge variant="{{ $ngvoPct >= 80 ? 'success' : ($ngvoPct >= 50 ? 'warning' : 'error') }}">
                                {{ number_format($ngvoPct, 1, ',', '.') }}%
                            </x-ui.badge>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
    </x-card>
    @endif

    @if($currentTab === 'detail')
    <!-- KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        
        <!-- Total Visit -->
        <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col relative overflow-hidden bg-base-100">
            <div class="absolute -right-4 -top-4 opacity-5 text-primary"><x-heroicon-s-check-circle class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10">Total Visit</div>
            <div class="text-3xl font-bold mt-1 z-10 text-primary">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Total JKS:</span>
                    <span class="text-base-content font-bold">{{ number_format($kpiData['total_jks'] ?? 0, 0, ',', '.') }}</span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_jks'] ?? 0) - ($kpiData['total_visit'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_jks'] ?? 0) - ($kpiData['total_visit'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Total Order -->
        <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col relative overflow-hidden bg-base-100">
            <div class="absolute -right-4 -top-4 opacity-5 text-success"><x-heroicon-s-currency-dollar class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10">Total Order</div>
            <div class="text-3xl font-bold mt-1 z-10 text-success">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Target:</span>
                    <span class="text-base-content font-bold">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_target'] ?? 0) - ($kpiData['total_order'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_target'] ?? 0) - ($kpiData['total_order'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Visit 1. RWO -->
        <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col relative overflow-hidden bg-base-100">
            <div class="absolute -right-4 -top-4 opacity-5 text-success"><x-heroicon-s-tag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10">Visit 1. RWO</div>
            <div class="text-3xl font-bold mt-1 z-10 text-success">{{ number_format($kpiData['visit_rwo'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Total JKS:</span>
                    <span class="text-base-content font-bold">{{ number_format($kpiData['total_rwo'] ?? 0, 0, ',', '.') }}</span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_rwo'] ?? 0) - ($kpiData['visit_rwo'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_rwo'] ?? 0) - ($kpiData['visit_rwo'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Visit 2. PNR -->
        <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col relative overflow-hidden bg-base-100">
            <div class="absolute -right-4 -top-4 opacity-5 text-warning"><x-heroicon-s-tag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10">Visit 2. PNR</div>
            <div class="text-3xl font-bold mt-1 z-10 text-warning">{{ number_format($kpiData['visit_pnr'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Total JKS:</span>
                    <span class="text-base-content font-bold">{{ number_format($kpiData['total_pnr'] ?? 0, 0, ',', '.') }}</span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_pnr'] ?? 0) - ($kpiData['visit_pnr'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_pnr'] ?? 0) - ($kpiData['visit_pnr'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Visit 3. NGVO -->
        <div class="rounded-2xl p-4 shadow-sm border border-base-200 flex flex-col relative overflow-hidden bg-base-100">
            <div class="absolute -right-4 -top-4 opacity-5 text-secondary"><x-heroicon-s-tag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10">Visit 3. NGVO</div>
            <div class="text-3xl font-bold mt-1 z-10 text-secondary">{{ number_format($kpiData['visit_ngvo'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Total JKS:</span>
                    <span class="text-base-content font-bold">{{ number_format($kpiData['total_ngvo'] ?? 0, 0, ',', '.') }}</span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_ngvo'] ?? 0) - ($kpiData['visit_ngvo'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_ngvo'] ?? 0) - ($kpiData['visit_ngvo'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>
    </div>
    <x-card class="mb-4" flush="true">
        <div class="p-4 border-b border-base-200 bg-base-50/50">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-4 items-end">
                <div class="form-control w-full lg:col-span-2">
                    <label class="label pb-1"><span class="label-text font-medium text-base-content">Tanggal Awal</span></label>
                    <input type="date" wire:model="startDate" class="input input-sm input-bordered w-full" />
                </div>
                <div class="form-control w-full lg:col-span-2">
                    <label class="label pb-1"><span class="label-text font-medium text-base-content">Tanggal Akhir</span></label>
                    <input type="date" wire:model="endDate" class="input input-sm input-bordered w-full" />
                </div>
                <div class="form-control w-full lg:col-span-2">
                    <label class="label pb-1"><span class="label-text font-medium text-base-content">Region</span></label>
                    <select wire:model.live="selectedRegion" class="select select-sm select-bordered w-full">
                        <option value="">Semua Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->kode_region }}">{{ $region->nama_region }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control w-full lg:col-span-2">
                    <label class="label pb-1"><span class="label-text font-medium text-base-content">Area</span></label>
                    <select wire:model.live="selectedArea" class="select select-sm select-bordered w-full" @if(empty($areas)) disabled @endif>
                        <option value="">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->kode_area }}">{{ $area->nama_area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control w-full lg:col-span-2">
                    <label class="label pb-1"><span class="label-text font-medium text-base-content">Team</span></label>
                    <select wire:model.live="selectedTeam" class="select select-sm select-bordered w-full" @if(empty($teams)) disabled @endif>
                        <option value="">Semua Team</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->kode_team }}">{{ $team->nama_team }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 w-full lg:col-span-2">
                    <x-ui.button class="flex-1" variant="neutral" icon="arrow-path" size="sm" wire:click="resetFilter" spinner="resetFilter">Reset</x-ui.button>
                    <x-ui.button class="flex-1" variant="primary" icon="magnifying-glass" size="sm" wire:click="applyFilter" spinner="applyFilter">Terapkan</x-ui.button>
                </div>
            </div>
        </div>

        <div class="h-[400px] overflow-auto">
            <x-ui.table striped hover empty="Silakan pilih filter Region terlebih dahulu untuk menampilkan data." class="border-x-0 border-b-0 rounded-none shadow-none text-[10px]">
                <x-slot:head>
                    <tr>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Tanggal</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Region</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Area</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Team</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Distributor</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Customer</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Address</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Pilar</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Target</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Visit</th>
                        <th class="sticky top-0 bg-base-100 z-10 shadow-sm">Order Val</th>
                    </tr>
                </x-slot:head>
                
                @php
                    $groupedData = collect($dataKunjungan)->groupBy('tanggal');
                @endphp
                @foreach($groupedData as $tanggal => $rows)
                    @foreach($rows as $row)
                        <tr>
                            <td class="whitespace-nowrap">{{ $row->tanggal }}</td>
                            <td class="whitespace-nowrap">{{ $row->nama_region }}</td>
                            <td class="whitespace-nowrap">{{ $row->nama_area }}</td>
                            <td class="max-w-[150px] truncate" title="{{ $row->nama_team }}">{{ $row->nama_team }}</td>
                            <td class="max-w-[150px] truncate" title="{{ $row->distributor_code }} - {{ $row->distributor_name }}">{{ $row->distributor_code }} - {{ $row->distributor_name }}</td>
                            <td class="whitespace-nowrap">{{ $row->custno }} - {{ $row->custname }}</td>
                            <td class="max-w-[160px] truncate" title="{{ $row->addres }}">{{ $row->addres }}</td>
                            <td class="whitespace-nowrap text-center">
                                @php
                                    $pilarVal = strtoupper($row->pilar ?? '');
                                    $badgeVariant = 'neutral';
                                    if (str_contains($pilarVal, '1. RWO')) $badgeVariant = 'success';
                                    elseif (str_contains($pilarVal, '2. PNR')) $badgeVariant = 'warning';
                                    elseif (str_contains($pilarVal, '3. NGVO')) $badgeVariant = 'secondary';
                                @endphp
                                <x-ui.badge variant="{{ $badgeVariant }}">{{ $row->pilar }}</x-ui.badge>
                            </td>
                            <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->target ?? 0, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap text-center">
                                @if($row->flag_visit == 'Y')
                                    <x-ui.badge variant="success">Yes</x-ui.badge>
                                @else
                                    <x-ui.badge variant="error">No</x-ui.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->order_val ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="!bg-primary/10 font-bold border-t-2 border-primary/20">
                        <td colspan="8" class="text-right py-2 text-primary">Subtotal Tanggal {{ $tanggal }}:</td>
                        <td class="whitespace-nowrap font-mono text-right text-primary py-2">{{ number_format($rows->sum('target'), 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap text-center text-primary py-2" title="Total Plan / Total Visit">{{ $rows->count() }} / {{ $rows->filter(fn($r) => $r->flag_visit == 'Y')->count() }}</td>
                        <td class="whitespace-nowrap font-mono text-right text-primary py-2">{{ number_format($rows->sum('order_val'), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </div>
    </x-card>
    @endif
</div>
