<div>
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
            <div class="absolute -right-4 -top-4 opacity-5 text-info"><x-heroicon-s-tag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10">Visit 1. RWO</div>
            <div class="text-3xl font-bold mt-1 z-10 text-info">{{ number_format($kpiData['visit_rwo'] ?? 0, 0, ',', '.') }}</div>
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

    <!-- MAIN DATA CARD -->
    <x-card title="Summary Kunjungan" icon="document-text" class="mb-4" flush="true">
        <x-slot:actions>
            <x-ui.button size="sm" variant="primary" icon="adjustments-horizontal" wire:click="$set('showFilterModal', true)">
                Filter Data
            </x-ui.button>
        </x-slot:actions>

        <x-ui.table striped hover empty="Tidak ada data kunjungan." class="border-x-0 border-b-0 rounded-none shadow-none mt-2">
            <x-slot:head>
                <tr>
                    <th>Tanggal</th>
                    <th>Region</th>
                    <th>Area</th>
                    <th>Team</th>
                    <th>Distributor</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Pilar</th>
                    <th>Target</th>
                    <th>Visit</th>
                    <th>Order Val</th>
                </tr>
            </x-slot:head>
            
            @foreach($dataKunjungan as $row)
                <tr>
                    <td class="whitespace-nowrap">{{ $row->tanggal }}</td>
                    <td class="whitespace-nowrap">{{ $row->kode_region }} - {{ $row->nama_region }}</td>
                    <td class="whitespace-nowrap">{{ $row->kode_area }} - {{ $row->nama_area }}</td>
                    <td class="whitespace-nowrap">{{ $row->kode_team }} - {{ $row->nama_team }}</td>
                    <td class="whitespace-nowrap">{{ $row->distributor_code }} - {{ $row->distributor_name }}</td>
                    <td class="whitespace-nowrap">{{ $row->custno }} - {{ $row->custname }}</td>
                    <td class="max-w-xs truncate" title="{{ $row->addres }}">{{ $row->addres }}</td>
                    <td class="whitespace-nowrap text-center">
                        <x-ui.badge variant="neutral">{{ $row->pilar }}</x-ui.badge>
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
        </x-ui.table>
    </x-card>

    <x-ui.modal id="modal-filter" title="Filter Data Kunjungan" icon="adjustments-horizontal" wire:close="$set('showFilterModal', false)" :open="$showFilterModal">
        <div class="space-y-4">
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-medium text-base-content">Region</span></label>
                <select wire:model.live="selectedRegion" class="select select-bordered w-full">
                    <option value="">Semua Region</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->kode_region }}">{{ $region->nama_region }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-medium text-base-content">Area</span></label>
                <select wire:model.live="selectedArea" class="select select-bordered w-full" @if(empty($areas)) disabled @endif>
                    <option value="">Semua Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->kode_area }}">{{ $area->nama_area }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label"><span class="label-text font-medium text-base-content">Team</span></label>
                <select wire:model.live="selectedTeam" class="select select-bordered w-full" @if(empty($teams)) disabled @endif>
                    <option value="">Semua Team</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->kode_team }}">{{ $team->nama_team }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="primary" wire:click="$set('showFilterModal', false)">Selesai</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
