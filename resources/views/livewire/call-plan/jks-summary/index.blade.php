<div class="flex-1 flex flex-col min-h-0 w-full h-full">
    <x-slot name="title">Summary JKS</x-slot>

    <x-ui.tab-menu>
        @php
            $pendingApprovalsCount = \App\Livewire\CallPlan\JksApprovals\Index::getPendingCount();
        @endphp
        <a href="#" class="tab">Dashboard</a>
        <a href="{{ route('call-plan.jks-summary') }}" class="tab tab-active bg-primary text-primary-content">Summary</a>
        <a href="{{ route('call-plan.jks-salesmans') }}" class="tab">Detail</a>
        <a href="{{ route('call-plan.jks-maps') }}" class="tab">Maps</a>
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
            
            {{-- Filter Toolbar (Standard UI Components) --}}
            <x-ui.filter-toolbar>
                {{-- Hierarchy Filter Breadcrumb Capsule --}}
                <x-ui.filter-breadcrumb>
                    @if(in_array($accessLevel, ['nasional', 'region']))
                        <x-ui.filter-select 
                            wire:model.live="appliedRegion" 
                            :options="$regionOptions" 
                            placeholder="Semua Region" 
                            valueField="region_code" 
                            labelField="region_name" 
                            icon="region" 
                            width="w-32 sm:w-36" 
                        />
                    @endif

                    @if(!in_array($accessLevel, ['supervisor', 'area']))
                        <x-ui.filter-select 
                            wire:model.live="appliedArea" 
                            :options="$areaOptions" 
                            placeholder="Semua Area" 
                            valueField="area_code" 
                            labelField="area_name" 
                            icon="area" 
                            width="w-32 sm:w-36" 
                            :disabled="empty($appliedRegion)" 
                        />
                    @endif

                    <x-ui.filter-select 
                        wire:model.live="appliedSupervisor" 
                        :options="$supervisorOptions" 
                        placeholder="Semua Supervisor" 
                        valueField="supervisor_code" 
                        labelField="description" 
                        icon="user" 
                        width="w-36 sm:w-44" 
                        :disabled="empty($appliedArea)" 
                    />

                    {{-- Distributor Searchable Capsule --}}
                    <x-ui.filter-searchable 
                        wire:key="summary-dist-capsule-{{ $appliedRegion }}-{{ $appliedArea }}-{{ $appliedSupervisor }}"
                        wire:model.live="appliedDistributor" 
                        :options="$distributorOptions" 
                        valueField="distributor_code" 
                        labelField="distributor_name" 
                        placeholder="Semua Distributor" 
                        searchPlaceholder="Cari nama / kode distributor..." 
                        icon="distributor" 
                        width="w-44 sm:w-56" 
                        popoverWidth="w-72 sm:w-84" 
                        allowAll="true"
                        allLabel="Semua Distributor"
                    />
                </x-ui.filter-breadcrumb>

                {{-- Month Filter Capsule --}}
                <div class="inline-flex items-center gap-1.5 bg-base-100 px-2.5 py-1 rounded-xl border border-base-300 shadow-2xs text-xs font-medium shrink-0" title="Pilih Periode Bulan Evaluasi">
                    <x-heroicon-m-calendar-days class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                    <input type="month" wire:model.live="appliedBulan" class="bg-transparent border-none focus:outline-none text-xs font-semibold cursor-pointer w-28 text-base-content" />
                </div>

                {{-- Standpro Quick Filter Segmented Pills --}}
                <div class="inline-flex items-center bg-base-200/60 p-0.5 rounded-xl border border-base-300 shadow-2xs text-xs shrink-0" title="Filter Pencapaian Standar Pareto (300 Toko)">
                    <button type="button" wire:click="setStandpro('')" class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ empty($appliedStandpro) ? 'bg-primary text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}">Semua</button>
                    <button type="button" wire:click="setStandpro('sudah')" class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $appliedStandpro === 'sudah' ? 'bg-success text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Toko JKS >= 300">Standar (≥ 300)</button>
                    <button type="button" wire:click="setStandpro('belum')" class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors cursor-pointer {{ $appliedStandpro === 'belum' ? 'bg-error text-white font-bold shadow-2xs' : 'text-base-content/70 hover:text-base-content' }}" title="Toko JKS < 300">Belum (&lt; 300)</button>
                </div>

                <x-slot:actions>
                    {{-- Per Page Dropdown --}}
                    <div class="flex items-center gap-1.5 text-xs text-base-content/60">
                        <span>Baris:</span>
                        <select wire:model.live="perPage" class="select select-2xs select-bordered bg-base-100 rounded-lg font-medium">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200">200</option>
                        </select>
                    </div>

                    {{-- Search Component --}}
                    <x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Cari Salesman / Dist..." />

                    {{-- Reset Button --}}
                    <button type="button" class="btn btn-sm btn-ghost border border-base-300 rounded-xl text-base-content/70 hover:text-base-content text-xs cursor-pointer" wire:click="resetFilters" title="Reset Semua Filter">
                        <x-heroicon-m-arrow-path class="w-3.5 h-3.5" />
                        <span class="hidden sm:inline">Reset</span>
                    </button>

                    {{-- Export Excel Button --}}
                    <button type="button" class="btn btn-sm btn-success text-white rounded-xl gap-1 text-xs font-semibold shadow-xs cursor-pointer" wire:click="exportExcel" wire:loading.class="opacity-50 pointer-events-none" wire:target="exportExcel" title="Export Rekapitulasi ke Excel">
                        <span wire:loading.remove wire:target="exportExcel" class="flex items-center gap-1">
                            <x-heroicon-s-arrow-down-tray class="w-4 h-4" /> 
                            <span class="hidden sm:inline">Excel</span>
                        </span>
                        <span wire:loading wire:target="exportExcel" class="flex items-center gap-1">
                            <span class="loading loading-spinner loading-xs"></span>
                            <span class="hidden sm:inline">Loading...</span>
                        </span>
                    </button>
                </x-slot:actions>
            </x-ui.filter-toolbar>

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
                    <thead class="text-xs uppercase bg-base-200 text-base-content shadow-sm sticky top-0 z-40 border-b border-base-300">
                        {{-- Baris 1: Parent Headers --}}
                        <tr>
                            <th rowspan="2" class="bg-base-200 sticky left-0 z-50 border-b border-r border-base-300 w-[100px] min-w-[100px] max-w-[100px] text-base-content/60 font-semibold tracking-wider">Region</th>
                            <th rowspan="2" class="bg-base-200 sticky left-[100px] z-50 border-b border-r border-base-300 w-[120px] min-w-[120px] max-w-[120px] text-base-content/60 font-semibold tracking-wider">Area</th>
                            <th rowspan="2" class="bg-base-200 sticky left-[220px] z-50 border-b border-r border-base-300 w-[150px] min-w-[150px] max-w-[150px] text-base-content/60 font-semibold tracking-wider">Supervisor</th>
                            <th rowspan="2" class="bg-base-200 sticky left-[370px] z-50 border-b border-r border-base-300 w-[150px] min-w-[150px] max-w-[150px] text-base-content/60 font-semibold tracking-wider">Distributor</th>
                            <th rowspan="2" class="bg-base-200 sticky left-[520px] z-50 border-b border-r border-base-300 shadow-[4px_0_12px_-2px_rgba(0,0,0,0.1)] w-[150px] min-w-[150px] max-w-[150px] font-semibold text-base-content/60 tracking-wider">Salesman</th>
                            
                            <th rowspan="2" class="text-right pr-4 border-b border-r border-base-300 bg-base-200 text-base-content/60 font-semibold tracking-wider">Total RO</th>
                            <th rowspan="2" class="text-right pr-4 border-b border-r border-base-300 bg-base-200 text-base-content/60 font-semibold tracking-wider">JKS</th>
                            <th rowspan="2" class="text-right pr-4 border-b border-r border-base-300 bg-base-200 text-base-content/60 font-semibold tracking-wider">Non Rute</th>
                            <th rowspan="2" class="text-right pr-4 border-b border-r border-base-300 bg-base-200 text-base-content/60 font-semibold tracking-wider">Non GPS</th>
                            
                            <th colspan="2" class="text-center border-b border-r border-base-300 bg-base-200 text-base-content/60 font-semibold tracking-wider">Perubahan JKS</th>
                            
                            @foreach($days as $key => $label)
                                @php
                                    $colGroupBg = in_array($key, ['selasa', 'kamis', 'sabtu']) ? 'bg-base-200/50' : 'bg-base-200';
                                @endphp
                                <th colspan="3" class="text-center border-b border-r border-base-300 {{ $colGroupBg }} text-base-content/60 font-semibold tracking-wider">{{ $label }}</th>
                            @endforeach
                        </tr>
                        
                        {{-- Baris 2: Child Headers --}}
                        <tr>
                            <th class="text-right pr-3 bg-base-200 text-xs font-semibold tracking-wider text-base-content/60 border-b border-r border-base-300">Penambahan</th>
                            <th class="text-right pr-3 bg-base-200 text-xs font-semibold tracking-wider text-base-content/60 border-b border-r border-base-300">Delete</th>
                            
                            @foreach($days as $key => $label)
                                @php
                                    $colGroupBg = in_array($key, ['selasa', 'kamis', 'sabtu']) ? 'bg-base-200/50' : 'bg-base-200';
                                @endphp
                                <th class="text-right pr-3 {{ $colGroupBg }} text-xs font-semibold text-primary tracking-wider border-b border-r border-base-300">Gjl</th>
                                <th class="text-right pr-3 {{ $colGroupBg }} text-xs font-semibold text-secondary tracking-wider border-b border-r border-base-300">Gnp</th>
                                <th class="text-right pr-3 {{ $colGroupBg }} text-xs font-semibold text-error tracking-wider border-b border-r border-base-300">Non Gps</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($summaryData as $row)
                            <tr class="hover:bg-base-200/50 transition-colors group text-xs">
                                <td title="{{ $row->region_name ?: $row->region_code }}" class="bg-base-100 group-hover:bg-base-200/50 sticky left-0 z-30 border-b border-r border-base-200 truncate w-[100px] min-w-[100px] max-w-[100px] text-base-content/70">{{ $row->region_name ?: $row->region_code }}</td>
                                <td title="{{ $row->area_name ?: $row->area_code }}" class="bg-base-100 group-hover:bg-base-200/50 sticky left-[100px] z-30 border-b border-r border-base-200 truncate w-[120px] min-w-[120px] max-w-[120px] text-base-content/70">{{ $row->area_name ?: $row->area_code }}</td>
                                <td title="{{ $row->supervisor_name ?: $row->supervisor_code }}" class="bg-base-100 group-hover:bg-base-200/50 sticky left-[220px] z-30 border-b border-r border-base-200 truncate w-[150px] min-w-[150px] max-w-[150px] text-base-content/90">{{ $row->supervisor_name ?: $row->supervisor_code }}</td>
                                <td title="{{ $row->distributor_name ?: $row->distributor_code }}" class="bg-base-100 group-hover:bg-base-200/50 sticky left-[370px] z-30 border-b border-r border-base-200 truncate w-[150px] min-w-[150px] max-w-[150px] text-base-content/90">{{ $row->distributor_name ?: $row->distributor_code }}</td>
                                <td title="{{ $row->salesman_name ?: $row->salesman_code }}" class="bg-base-100 group-hover:bg-base-200/50 sticky left-[520px] z-30 border-b border-r border-base-200 shadow-[4px_0_12px_-2px_rgba(0,0,0,0.1)] truncate w-[150px] min-w-[150px] max-w-[150px] font-medium text-base-content/90">{{ $row->salesman_name ?: $row->salesman_code }}</td>
                                
                                <td class="text-right pr-4 border-b border-r border-base-200 {{ $row->total_ro < 300 ? 'bg-error/5 text-error font-medium' : 'text-base-content/80' }}">{{ number_format($row->total_ro) }}</td>
                                <td class="text-right pr-4 border-b border-r border-base-200 {{ $row->total_jks < 300 ? 'bg-error/5 text-error font-medium' : 'text-base-content/80' }}">{{ number_format($row->total_jks) }}</td>
                                <td class="text-right pr-4 border-b border-r border-base-200 {{ $row->non_rute > 0 ? 'text-warning font-medium' : 'text-base-content/40' }}">{{ number_format($row->non_rute) }}</td>
                                <td class="text-right pr-4 border-b border-r border-base-200 {{ $row->non_gps > 0 ? 'text-error font-medium' : 'text-base-content/40' }}">{{ number_format($row->non_gps) }}</td>
                                
                                <td class="text-right pr-3 border-b border-r border-base-200 {{ $row->penambahan > 0 ? 'text-info font-medium' : 'text-base-content/30' }}">{{ number_format($row->penambahan) }}</td>
                                <td class="text-right pr-3 border-b border-r border-base-200 {{ $row->deleted > 0 ? 'text-error font-medium' : 'text-base-content/30' }}">{{ number_format($row->deleted) }}</td>
                                
                                @foreach($days as $key => $label)
                                    @php
                                        $gjlField = $key . '_gjl';
                                        $gnpField = $key . '_gnp';
                                        $gpsField = $key . '_non_gps';
                                        $bgClass = in_array($key, ['selasa', 'kamis', 'sabtu']) ? 'bg-base-200/30' : '';
                                        
                                        $getColor = function($val) {
                                            if ($val == 0) return 'text-base-content/30';
                                            if ($val < 20) return 'bg-error/10 text-error font-medium';
                                            if ($val > 35) return 'bg-warning/10 text-warning font-medium';
                                            return 'text-base-content/80';
                                        };
                                    @endphp
                                    <td class="text-right pr-3 border-b border-r border-base-200 {{ $bgClass }} {{ $getColor($row->$gjlField) }}">{{ number_format($row->$gjlField) }}</td>
                                    <td class="text-right pr-3 border-b border-r border-base-200 {{ $bgClass }} {{ $getColor($row->$gnpField) }}">{{ number_format($row->$gnpField) }}</td>
                                    <td class="text-right pr-3 border-b border-r border-base-200 {{ $bgClass }} {{ $row->$gpsField > 0 ? 'text-error font-medium' : 'text-base-content/30' }}">{{ number_format($row->$gpsField) }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="32" class="text-center py-10 text-base-content/50">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <x-heroicon-o-inbox class="w-12 h-12 text-base-300" />
                                        <p class="font-semibold text-sm">Tidak ada data summary untuk filter yang dipilih.</p>
                                        <p class="text-xs text-base-content/60">Coba ubah filter Region, Area, Supervisor, Distributor, atau kata kunci pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    
                    @if(count($summaryData) > 0)
                    <tfoot class="sticky bottom-0 z-40 bg-base-200 text-base-content/70 shadow-[0_-4px_12px_rgba(0,0,0,0.05)] text-xs uppercase">
                        <tr>
                            <td colspan="5" class="bg-base-200 sticky left-0 z-50 border-t border-r border-base-300 text-right pr-4 tracking-widest font-semibold">TOTAL PAGE</td>
                            <td class="text-right pr-4 border-t border-r border-base-300">{{ number_format(collect($summaryData->items())->sum('total_ro')) }}</td>
                            <td class="text-right pr-4 border-t border-r border-base-300">{{ number_format(collect($summaryData->items())->sum('total_jks')) }}</td>
                            <td class="text-right pr-4 border-t border-r border-base-300 text-warning-content">{{ number_format(collect($summaryData->items())->sum('non_rute')) }}</td>
                            <td class="text-right pr-4 border-t border-r border-base-300 text-error">{{ number_format(collect($summaryData->items())->sum('non_gps')) }}</td>
                            
                            <td class="text-right pr-3 border-t border-r border-base-300 text-info">{{ number_format(collect($summaryData->items())->sum('penambahan')) }}</td>
                            <td class="text-right pr-3 border-t border-r border-base-300 text-error">{{ number_format(collect($summaryData->items())->sum('deleted')) }}</td>
                            
                            @foreach($days as $key => $label)
                                @php
                                    $gjlField = $key . '_gjl';
                                    $gnpField = $key . '_gnp';
                                    $gpsField = $key . '_non_gps';
                                    $colGroupBg = in_array($key, ['selasa', 'kamis', 'sabtu']) ? 'bg-base-200/50' : '';
                                @endphp
                                <td class="text-right pr-3 border-t border-r border-base-300 {{ $colGroupBg }}">{{ number_format(collect($summaryData->items())->sum($gjlField)) }}</td>
                                <td class="text-right pr-3 border-t border-r border-base-300 {{ $colGroupBg }}">{{ number_format(collect($summaryData->items())->sum($gnpField)) }}</td>
                                <td class="text-right pr-3 border-t border-r border-base-300 {{ $colGroupBg }} text-error">{{ number_format(collect($summaryData->items())->sum($gpsField)) }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Footer Pagination --}}
            @if($summaryData->hasPages())
                <div class="p-3 border-t border-base-300 bg-base-100 flex items-center justify-between">
                    <span class="text-xs text-base-content/60">
                        Menampilkan {{ $summaryData->firstItem() ?? 0 }} - {{ $summaryData->lastItem() ?? 0 }} dari {{ number_format($summaryData->total()) }} salesman
                    </span>
                    <div>
                        {{ $summaryData->links(data: ['scrollTo' => false]) }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
