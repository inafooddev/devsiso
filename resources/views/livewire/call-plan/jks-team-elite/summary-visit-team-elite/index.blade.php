<div>
    @if($isFiltered)
    <!-- KPI CARDS -->
    <div class="grid grid-cols-7 gap-4 mb-6">
        
        <!-- Total Visit -->
        <div class="rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 hover:border-base-300 flex flex-col relative overflow-hidden bg-gradient-to-br from-base-100 to-base-200/50">
            <div class="absolute -right-4 -top-4 opacity-5 text-primary"><x-heroicon-s-check-circle class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10" title="Jumlah toko yang telah dikunjungi">Total Visit</div>
            <div class="text-2xl xl:text-3xl font-bold mt-1 z-10 text-primary">{{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Total Toko:</span>
                    <span class="text-base-content font-bold">
                        {{ number_format($kpiData['total_toko'] ?? 0, 0, ',', '.') }}
                        <span class="text-[10px] font-normal text-base-content/60 ml-1">| {{ number_format(($kpiData['total_toko'] ?? 0) > 0 ? (($kpiData['total_visit'] ?? 0) / ($kpiData['total_toko'] ?? 1) * 100) : 0, 1) }}%</span>
                    </span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_toko'] ?? 0) - ($kpiData['total_visit'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_toko'] ?? 0) - ($kpiData['total_visit'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Total Toko Order -->
        <div class="rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 hover:border-base-300 flex flex-col relative overflow-hidden bg-gradient-to-br from-base-100 to-base-200/50">
            <div class="absolute -right-4 -top-4 opacity-5 text-success"><x-heroicon-s-shopping-bag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10" title="Jumlah toko yang melakukan pemesanan (Order > 0)">Toko Order</div>
            <div class="text-2xl xl:text-3xl font-bold mt-1 z-10 text-success">{{ number_format($kpiData['total_toko_order'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Total Visit:</span>
                    <span class="text-base-content font-bold">
                        {{ number_format($kpiData['total_visit'] ?? 0, 0, ',', '.') }}
                        <span class="text-[10px] font-normal text-base-content/60 ml-1">| {{ number_format(($kpiData['total_visit'] ?? 0) > 0 ? (($kpiData['total_toko_order'] ?? 0) / ($kpiData['total_visit'] ?? 1) * 100) : 0, 1) }}%</span>
                    </span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_visit'] ?? 0) - ($kpiData['total_toko_order'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_visit'] ?? 0) - ($kpiData['total_toko_order'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Total Order -->
        <div class="rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 hover:border-base-300 flex flex-col relative overflow-hidden bg-gradient-to-br from-base-100 to-base-200/50">
            <div class="absolute -right-4 -top-4 opacity-5 text-success"><x-heroicon-s-currency-dollar class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10" title="Total nilai pemesanan keseluruhan">Total Order</div>
            <div class="text-2xl xl:text-3xl font-bold mt-1 z-10 text-success">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Target:</span>
                    <span class="text-base-content font-bold">
                        {{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}
                        <span class="text-[10px] font-normal text-base-content/60 ml-1">| {{ number_format(($kpiData['total_target'] ?? 0) > 0 ? (($kpiData['total_order'] ?? 0) / ($kpiData['total_target'] ?? 1) * 100) : 0, 1) }}%</span>
                    </span>
                </span>
                <span class="flex justify-between pt-0.5 {{ (($kpiData['total_target'] ?? 0) - ($kpiData['total_order'] ?? 0)) > 0 ? 'text-error' : 'text-success' }}">
                    <span>Gap:</span>
                    <span class="font-bold">{{ number_format(($kpiData['total_target'] ?? 0) - ($kpiData['total_order'] ?? 0), 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Visit 1. RWO -->
        <div class="rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 hover:border-base-300 flex flex-col relative overflow-hidden bg-gradient-to-br from-base-100 to-base-200/50">
            <div class="absolute -right-4 -top-4 opacity-5 text-info"><x-heroicon-s-tag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10" title="Kinerja toko pada pilar 1. RWO">Total 1. RWO</div>
            <div class="text-2xl xl:text-3xl font-bold mt-1 z-10 text-info">{{ number_format($kpiData['total_rwo'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Toko Order:</span>
                    <span class="text-base-content font-bold">
                        {{ number_format($kpiData['toko_order_rwo'] ?? 0, 0, ',', '.') }}
                        <span class="text-[10px] font-normal text-base-content/60 ml-1">| {{ number_format(($kpiData['total_rwo'] ?? 0) > 0 ? (($kpiData['toko_order_rwo'] ?? 0) / ($kpiData['total_rwo'] ?? 1) * 100) : 0, 1) }}%</span>
                    </span>
                </span>
                <span class="flex justify-between pt-0.5">
                    <span>Val Order:</span>
                    <span class="font-bold text-success">{{ number_format($kpiData['total_order_rwo'] ?? 0, 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Visit 2. PNR -->
        <div class="rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 hover:border-base-300 flex flex-col relative overflow-hidden bg-gradient-to-br from-base-100 to-base-200/50">
            <div class="absolute -right-4 -top-4 opacity-5 text-warning"><x-heroicon-s-tag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10" title="Kinerja toko pada pilar 2. PNR">Total 2. PNR</div>
            <div class="text-2xl xl:text-3xl font-bold mt-1 z-10 text-warning">{{ number_format($kpiData['total_pnr'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Toko Order:</span>
                    <span class="text-base-content font-bold">
                        {{ number_format($kpiData['toko_order_pnr'] ?? 0, 0, ',', '.') }}
                        <span class="text-[10px] font-normal text-base-content/60 ml-1">| {{ number_format(($kpiData['total_pnr'] ?? 0) > 0 ? (($kpiData['toko_order_pnr'] ?? 0) / ($kpiData['total_pnr'] ?? 1) * 100) : 0, 1) }}%</span>
                    </span>
                </span>
                <span class="flex justify-between pt-0.5">
                    <span>Val Order:</span>
                    <span class="font-bold text-success">{{ number_format($kpiData['total_order_pnr'] ?? 0, 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Visit 3. NGVO -->
        <div class="rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 hover:border-base-300 flex flex-col relative overflow-hidden bg-gradient-to-br from-base-100 to-base-200/50">
            <div class="absolute -right-4 -top-4 opacity-5 text-secondary"><x-heroicon-s-tag class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10" title="Kinerja toko pada pilar 3. NGVO">Total 3. NGVO</div>
            <div class="text-2xl xl:text-3xl font-bold mt-1 z-10 text-secondary">{{ number_format($kpiData['total_ngvo'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1">
                    <span>Toko Order:</span>
                    <span class="text-base-content font-bold">
                        {{ number_format($kpiData['toko_order_ngvo'] ?? 0, 0, ',', '.') }}
                        <span class="text-[10px] font-normal text-base-content/60 ml-1">| {{ number_format(($kpiData['total_ngvo'] ?? 0) > 0 ? (($kpiData['toko_order_ngvo'] ?? 0) / ($kpiData['total_ngvo'] ?? 1) * 100) : 0, 1) }}%</span>
                    </span>
                </span>
                <span class="flex justify-between pt-0.5">
                    <span>Val Order:</span>
                    <span class="font-bold text-success">{{ number_format($kpiData['total_order_ngvo'] ?? 0, 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <!-- Total NOO -->
        <div class="rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 border border-base-200 hover:border-base-300 flex flex-col relative overflow-hidden bg-gradient-to-br from-base-100 to-base-200/50">
            <div class="absolute -right-4 -top-4 opacity-5 text-accent"><x-heroicon-s-user-plus class="w-24 h-24" /></div>
            <div class="text-sm text-base-content/70 font-medium z-10" title="Jumlah toko berstatus NOO">Total NOO</div>
            <div class="text-2xl xl:text-3xl font-bold mt-1 z-10 text-accent">{{ number_format($kpiData['total_noo'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-base-content/60 mt-3 z-10 flex flex-col gap-1 font-medium">
                <span class="flex justify-between border-b border-base-200 pb-1 opacity-0">
                    <span>-</span>
                    <span>-</span>
                </span>
                <span class="flex justify-between pt-0.5 opacity-0">
                    <span>-</span>
                    <span>-</span>
                </span>
            </div>
        </div>

    </div>
    @endif

    <x-card title="Summary Visit Team Elite" icon="document-text" class="mb-4" flush="true">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="selectedLevel" class="select select-sm select-bordered">
                    <option value="">Semua Level</option>
                    @foreach($levels as $level)
                        <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
                <select wire:model.live="selectedTeam" class="select select-sm select-bordered min-w-[150px]" @if(empty($teams)) disabled @endif>
                    <option value="" disabled>Pilih Team...</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->team_code }}">{{ $team->team_name }}</option>
                    @endforeach
                </select>
                
                <select wire:model="selectedKeterangan" class="select select-sm select-bordered">
                    <option value="">Semua Keterangan</option>
                    <option value="RO">RO</option>
                    <option value="NOO">NOO</option>
                </select>
                <input type="month" wire:model="selectedMonth" class="input input-sm input-bordered min-w-[150px]" />
                <x-ui.button size="sm" variant="primary" icon="magnifying-glass" wire:click="applyFilter">
                    Filter
                </x-ui.button>
            </div>
        </x-slot:actions>

        @if($isFiltered)
        <x-ui.table striped hover sticky="true" empty="Tidak ada data." class="max-h-[60vh] overflow-y-auto border-x-0 border-b-0 rounded-none shadow-none mt-2">
            <x-slot:head>
                <tr>
                    <th>Region</th>
                    <th>Area</th>
                    <th>Team</th>
                    <th>Cust No</th>
                    <th>Uniq ID</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Ket</th>
                    <th>Visit</th>
                    <th>Pilar</th>
                    <th class="text-right">Target</th>
                    <th class="text-right">Order Val</th>
                    <th class="text-right">Invoice</th>
                    <th class="text-right">Selisih</th>
                </tr>
            </x-slot:head>
            
            @foreach($dataKunjungan as $row)
                <tr class="{{ str_contains(strtoupper($row->keterangan ?? ''), 'NOO') ? '!bg-warning/20 hover:!bg-warning/30' : '' }}">
                    <td class="whitespace-nowrap">{{ $row->region_name }}</td>
                    <td class="whitespace-nowrap">{{ $row->area_name }}</td>
                    <td class="max-w-[150px] truncate" title="{{ $row->team_name }}">{{ $row->team_name }}</td>
                    <td class="max-w-[100px] truncate" title="{{ $row->custno }}">{{ $row->custno }}</td>
                    <td class="max-w-[120px] truncate" title="{{ $row->uniq_id }}">{{ $row->uniq_id }}</td>
                    <td class="max-w-[145px] truncate" title="{{ $row->custname }}">{{ $row->custname }}</td>
                    <td class="max-w-[110px] text-xs truncate" title="{{ $row->address }}">{{ $row->address }}</td>
                    <td class="whitespace-nowrap">{{ $row->keterangan }}</td>
                    <td class="whitespace-nowrap text-center">
                        @if($row->status_visit == 'Y')
                            <x-ui.badge variant="success">Yes</x-ui.badge>
                        @elseif($row->status_visit == 'N')
                            <x-ui.badge variant="error">No</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">{{ $row->status_visit }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-center">
                        @if($row->pilar)
                            <x-ui.badge variant="neutral">{{ $row->pilar }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->target ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right">{{ number_format($row->order_val ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right font-bold">{{ number_format($row->invoice ?? 0, 0, ',', '.') }}</td>
                    <td class="whitespace-nowrap font-mono text-right {{ (($row->invoice ?? 0) - ($row->order_val ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($row->invoice ?? 0) - ($row->order_val ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <x-slot:foot>
                <tr class="font-bold border-t-2 border-base-300">
                    <td colspan="10" class="text-right uppercase tracking-wider">Subtotal</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_target'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_order'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm text-base-content">{{ number_format($kpiData['total_invoice'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-sm {{ (($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0)) < 0 ? 'text-error' : 'text-success' }}">{{ number_format(($kpiData['total_invoice'] ?? 0) - ($kpiData['total_order'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            </x-slot:foot>
        </x-ui.table>
        @else
        <div class="p-8 text-center text-base-content/60">
            <x-heroicon-o-funnel class="w-12 h-12 mx-auto mb-3 opacity-50" />
            <p>Silakan pilih <strong>Team</strong> secara spesifik dan sesuaikan filter lainnya, lalu klik tombol <strong>Filter</strong> untuk menampilkan data.</p>
        </div>
        @endif
    </x-card>
</div>
