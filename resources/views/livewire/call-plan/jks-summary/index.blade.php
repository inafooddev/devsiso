<div class="flex-1 flex flex-col min-h-0 w-full h-full">
    <x-slot name="title">Summary JKS</x-slot>

    <x-ui.tab-menu>
        @php
            $pendingApprovalsCount = \Illuminate\Support\Facades\DB::table('jks_approvals')->where('status', 'PENDING')->count();
        @endphp
        <a href="#" class="tab">Dashboard</a>
        <a href="{{ route('call-plan.jks-summary') }}" class="tab tab-active bg-primary text-primary-content">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab">Detail</a>
        <a href="#" class="tab">Maps</a>
        <a href="{{ route('call-plan.jks-non-route') }}" class="tab">Outlet non JKS</a>
        <a href="{{ route('call-plan.jks-approvals') }}" class="tab">
            Persetujuan
            @if($pendingApprovalsCount > 0)
                <span class="badge badge-sm badge-error ml-2">{{ $pendingApprovalsCount }}</span>
            @endif
        </a>
    </x-ui.tab-menu>

    <div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full pt-4">
        
        {{-- Main Card --}}
        <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
            
            {{-- Header Card & Actions --}}
            <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-base-200/30">
                <div class="shrink-0 w-full xl:w-auto">
                    <h2 class="text-base md:text-lg font-bold">Summary JKS</h2>
                    <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Rekapitulasi call plan salesman</p>
                </div>
                
                {{-- Hierarchy Filters --}}
                <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 md:gap-3 w-full xl:w-auto">
                    @php $accessLevel = auth()->user() ? auth()->user()->getAccessLevel() : 'nasional'; @endphp
                    
                    <select wire:model.live="appliedBulan" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0">
                        <option value="">Bulan Ini</option>
                        @for($i = -2; $i <= 2; $i++)
                            @php 
                                $date = date('Y-m-01', strtotime("$i months"));
                                $label = date('M Y', strtotime($date));
                            @endphp
                            <option value="{{ $date }}">{{ $label }}</option>
                        @endfor
                    </select>

                    <select wire:model.live="appliedRegion" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0" @if($accessLevel != 'nasional') disabled @endif>
                        @if(count($regionOptions) != 1) <option value="">Semua Region</option> @endif
                        @foreach($regionOptions as $region)
                            <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="appliedArea" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0" @if(empty($appliedRegion) || $accessLevel == 'supervisor') disabled @endif>
                        @if(count($areaOptions) != 1) <option value="">Semua Area</option> @endif
                        @foreach($areaOptions as $area)
                            <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="appliedSupervisor" class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300 grow sm:grow-0" @if(empty($appliedArea) || $accessLevel == 'supervisor') disabled @endif>
                        @if(count($supervisorOptions) != 1) <option value="">Semua Supervisor</option> @endif
                        @foreach($supervisorOptions as $spv)
                            <option value="{{ $spv->supervisor_code }}">{{ $spv->description ?? $spv->supervisor_code }}</option>
                        @endforeach
                    </select>



                    {{-- Actions (Export & Reset) --}}
                    <div class="border-l border-base-300 pl-2 hidden sm:flex items-center gap-2">
                        <button class="btn btn-sm btn-success text-white" wire:click="exportExcel" wire:loading.class="opacity-50 pointer-events-none" wire:target="exportExcel" title="Export to Excel">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" wire:loading.remove wire:target="exportExcel" />
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                        </button>
                        <button class="btn btn-sm btn-ghost text-base-content/70" wire:click="resetFilters" title="Reset Filters & Cache">
                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            {{-- Message if no filter applied --}}
            @if(empty($appliedRegion) && empty($appliedArea) && empty($appliedSupervisor))
                <div class="p-3 bg-info/10 text-info text-xs font-semibold flex items-center justify-center gap-2 border-b border-info/20">
                    <x-heroicon-o-information-circle class="w-5 h-5 shrink-0" />
                    Pilih minimal satu filter (Region/Area/Spv) untuk memuat rekapitulasi data.
                </div>
            @endif

            {{-- Body Card (Table Scrollable area) --}}
            <div class="flex-1 overflow-auto bg-base-100 w-full relative shadow-inner">
                <table class="table table-sm w-full whitespace-nowrap border-separate border-spacing-0">
                    @php
                        $days = [
                            'senin' => 'Senin', 
                            'selasa' => 'Selasa', 
                            'rabu' => 'Rabu', 
                            'kamis' => 'Kamis', 
                            'jumat' => 'Jumat', 
                            'sabtu' => 'Sabtu'
                        ];
                    @endphp
                    
                    {{-- Thead --}}
                    <thead class="text-xs uppercase bg-base-200 text-base-content shadow-sm sticky top-0 z-40 border-b-2 border-base-300">
                        {{-- Baris 1: Parent Headers --}}
                        <tr>
                            <th rowspan="2" class="bg-base-300 sticky left-0 z-50 border-b-2 border-r border-base-100 w-[100px] min-w-[100px] max-w-[100px] text-base-content/80 font-bold tracking-wider">Region</th>
                            <th rowspan="2" class="bg-base-300 sticky left-[100px] z-50 border-b-2 border-r border-base-100 w-[120px] min-w-[120px] max-w-[120px] text-base-content/80 font-bold tracking-wider">Area</th>
                            <th rowspan="2" class="bg-base-300 sticky left-[220px] z-50 border-b-2 border-r border-base-100 w-[150px] min-w-[150px] max-w-[150px] text-base-content/80 font-bold tracking-wider">Supervisor</th>
                            <th rowspan="2" class="bg-base-300 sticky left-[370px] z-50 border-b-2 border-r border-base-100 w-[150px] min-w-[150px] max-w-[150px] text-base-content/80 font-bold tracking-wider">Distributor</th>
                            <th rowspan="2" class="bg-base-300 sticky left-[520px] z-50 border-b-2 border-r-2 border-base-100 shadow-[5px_0_15px_rgba(0,0,0,0.1)] w-[150px] min-w-[150px] max-w-[150px] font-bold text-primary tracking-wider">Salesman</th>
                            
                            <th rowspan="2" class="text-right pr-4 border-b-2 border-r border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">Total RO</th>
                            <th rowspan="2" class="text-right pr-4 border-b-2 border-r border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">JKS</th>
                            <th rowspan="2" class="text-right pr-4 border-b-2 border-r border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">Non Rute</th>
                            <th rowspan="2" class="text-right pr-4 border-b-2 border-r-2 border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">Non GPS</th>
                            
                            <th colspan="2" class="text-center border-b-2 border-r-2 border-base-100 bg-base-300 text-base-content/90 font-bold tracking-wider">Perubahan JKS</th>
                            
                            @foreach($days as $key => $label)
                                <th colspan="3" class="text-center border-b-2 border-r-2 border-base-100 bg-base-300 text-base-content/90 font-bold tracking-wider">{{ $label }}</th>
                            @endforeach
                        </tr>
                        
                        {{-- Baris 2: Child Headers --}}
                        <tr>
                            <th class="text-right pr-3 bg-base-200 text-[10px] tracking-wide text-base-content/80 border-b-2 border-r border-base-300">Penambahan</th>
                            <th class="text-right pr-3 bg-base-200 text-[10px] border-b-2 border-r-2 border-base-100 tracking-wide text-base-content/80">Delete</th>
                            
                            @foreach($days as $key => $label)
                                <th class="text-right pr-3 bg-base-200 text-[10px] text-primary tracking-wide border-b-2 border-r border-base-300">Gjl</th>
                                <th class="text-right pr-3 bg-base-200 text-[10px] text-secondary tracking-wide border-b-2 border-r border-base-300">Gnp</th>
                                <th class="text-right pr-3 bg-base-200 text-[10px] text-error tracking-wide border-b-2 border-r-2 border-base-100">Non Gps</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($summaryData as $row)
                            <tr class="hover:bg-base-300 transition-colors odd:bg-base-100 even:bg-base-200 group text-xs">
                                <td title="{{ $row->region_name ?: $row->region_code }}" class="bg-inherit sticky left-0 z-30 border-r border-base-300 truncate w-[100px] min-w-[100px] max-w-[100px] text-base-content/70">{{ $row->region_name ?: $row->region_code }}</td>
                                <td title="{{ $row->area_name ?: $row->area_code }}" class="bg-inherit sticky left-[100px] z-30 border-r border-base-300 truncate w-[120px] min-w-[120px] max-w-[120px] text-base-content/70">{{ $row->area_name ?: $row->area_code }}</td>
                                <td title="{{ $row->supervisor_name ?: $row->supervisor_code }}" class="bg-inherit sticky left-[220px] z-30 border-r border-base-300 truncate w-[150px] min-w-[150px] max-w-[150px] font-medium">{{ $row->supervisor_name ?: $row->supervisor_code }}</td>
                                <td title="{{ $row->distributor_name ?: $row->distributor_code }}" class="bg-inherit sticky left-[370px] z-30 border-r border-base-300 truncate w-[150px] min-w-[150px] max-w-[150px] font-medium">{{ $row->distributor_name ?: $row->distributor_code }}</td>
                                <td title="{{ $row->salesman_name ?: $row->salesman_code }}" class="bg-inherit sticky left-[520px] z-30 border-r-2 border-base-300 shadow-[5px_0_15px_rgba(0,0,0,0.1)] truncate w-[150px] min-w-[150px] max-w-[150px] font-semibold text-primary/90">{{ $row->salesman_name ?: $row->salesman_code }}</td>
                                
                                <td class="text-right pr-4 border-r border-base-200 {{ $row->total_ro < 300 ? 'bg-error/10 text-error font-bold' : 'bg-info/5 font-semibold' }}">{{ number_format($row->total_ro) }}</td>
                                <td class="text-right pr-4 border-r border-base-200 bg-info/5 font-semibold">{{ number_format($row->total_jks) }}</td>
                                <td class="text-right pr-4 border-r border-base-200 bg-info/5 {{ $row->non_rute > 0 ? 'text-warning font-bold' : 'text-base-content/40' }}">{{ number_format($row->non_rute) }}</td>
                                <td class="text-right pr-4 border-r border-base-200 bg-info/5 {{ $row->non_gps > 0 ? 'text-error font-bold' : 'text-base-content/40' }}">{{ number_format($row->non_gps) }}</td>
                                
                                <td class="text-right pr-3 {{ $row->penambahan > 0 ? 'text-info font-medium' : 'text-base-content/30' }}">{{ number_format($row->penambahan) }}</td>
                                <td class="text-right pr-3 border-r-2 border-base-300 {{ $row->deleted > 0 ? 'text-error font-medium' : 'text-base-content/30' }}">{{ number_format($row->deleted) }}</td>
                                
                                @foreach($days as $key => $label)
                                    @php
                                        $gjlField = $key . '_gjl';
                                        $gnpField = $key . '_gnp';
                                        $gpsField = $key . '_non_gps';
                                        $bgClass = $loop->even ? 'bg-base-200/30' : '';
                                    @endphp
                                    <td class="text-right pr-3 {{ $bgClass }} {{ $row->$gjlField > 0 ? 'text-base-content font-medium' : 'text-base-content/30' }}">{{ number_format($row->$gjlField) }}</td>
                                    <td class="text-right pr-3 {{ $bgClass }} {{ $row->$gnpField > 0 ? 'text-base-content font-medium' : 'text-base-content/30' }}">{{ number_format($row->$gnpField) }}</td>
                                    <td class="text-right pr-3 border-r-2 border-base-300 {{ $bgClass }} {{ $row->$gpsField > 0 ? 'text-error font-bold' : 'text-base-content/30' }}">{{ number_format($row->$gpsField) }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="32" class="text-center py-10 text-base-content/50">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-heroicon-o-inbox class="w-12 h-12 text-base-300" />
                                        <p>Tidak ada data summary untuk filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    @if(count($summaryData) > 0)
                    <tfoot class="sticky bottom-0 z-40 bg-base-300 text-base-content font-bold shadow-[0_-5px_15px_rgba(0,0,0,0.1)] text-xs uppercase">
                        <tr>
                            <td colspan="5" class="bg-base-300 sticky left-0 z-50 border-t-2 border-r-2 border-base-100 text-right pr-4 tracking-wider">TOTAL PAGE</td>
                            <td class="text-right pr-4 border-t-2 border-r border-base-100">{{ number_format(collect($summaryData->items())->sum('total_ro')) }}</td>
                            <td class="text-right pr-4 border-t-2 border-r border-base-100">{{ number_format(collect($summaryData->items())->sum('total_jks')) }}</td>
                            <td class="text-right pr-4 border-t-2 border-r border-base-100 text-warning">{{ number_format(collect($summaryData->items())->sum('non_rute')) }}</td>
                            <td class="text-right pr-4 border-t-2 border-r-2 border-base-100 text-error">{{ number_format(collect($summaryData->items())->sum('non_gps')) }}</td>
                            
                            <td class="text-right pr-3 border-t-2 border-base-100 text-info">{{ number_format(collect($summaryData->items())->sum('penambahan')) }}</td>
                            <td class="text-right pr-3 border-t-2 border-r-2 border-base-100 text-error">{{ number_format(collect($summaryData->items())->sum('deleted')) }}</td>
                            
                            @foreach($days as $key => $label)
                                @php
                                    $gjlField = $key . '_gjl';
                                    $gnpField = $key . '_gnp';
                                    $gpsField = $key . '_non_gps';
                                @endphp
                                <td class="text-right pr-3 border-t-2 border-base-100 text-primary">{{ number_format(collect($summaryData->items())->sum($gjlField)) }}</td>
                                <td class="text-right pr-3 border-t-2 border-base-100 text-secondary">{{ number_format(collect($summaryData->items())->sum($gnpField)) }}</td>
                                <td class="text-right pr-3 border-t-2 border-r-2 border-base-100 text-error">{{ number_format(collect($summaryData->items())->sum($gpsField)) }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Footer Pagination --}}
            @if($summaryData->hasPages())
                <div class="p-3 border-t border-base-300 bg-base-100 flex justify-end">
                    {{ $summaryData->links(data: ['scrollTo' => false]) }}
                </div>
            @endif

        </div>
    </div>
</div>
