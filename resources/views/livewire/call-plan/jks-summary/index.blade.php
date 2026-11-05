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
            
            {{-- Header Card & Filters --}}
            <div class="p-3 border-b border-base-300 shrink-0 flex flex-col gap-2.5 bg-base-200/40">
                
                {{-- Baris 1: Filter Hierarki Wilayah & Periode --}}
                <div class="flex flex-wrap items-center gap-2">
                    @php $accessLevel = auth()->user() ? auth()->user()->getAccessLevel() : 'nasional'; @endphp
                    
                    {{-- Filter Bulan --}}
                    <div class="w-full sm:w-36">
                        <select wire:model.live="appliedBulan" class="select select-sm select-bordered w-full bg-base-100 text-xs font-semibold" title="Pilih Bulan">
                            @foreach($availableMonths as $ym)
                                <option value="{{ $ym }}">{{ \Carbon\Carbon::parse($ym . '-01')->translatedFormat('F Y') }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Region --}}
                    <div class="w-full sm:w-40">
                        <select wire:model.live="appliedRegion" class="select select-sm select-bordered w-full bg-base-100 text-xs font-medium" @if($accessLevel != 'nasional' && count($regionOptions) <= 1) disabled @endif title="Filter Region">
                            @if(count($regionOptions) != 1) <option value="">-- Semua Region --</option> @endif
                            @foreach($regionOptions as $region)
                                <option value="{{ $region->region_code }}">{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Area --}}
                    <div class="w-full sm:w-44">
                        <select wire:model.live="appliedArea" class="select select-sm select-bordered w-full bg-base-100 text-xs font-medium" @if(empty($appliedRegion) && $accessLevel == 'nasional') disabled @endif title="Filter Area">
                            @if(count($areaOptions) != 1) <option value="">-- Semua Area --</option> @endif
                            @foreach($areaOptions as $area)
                                <option value="{{ $area->area_code }}">{{ $area->area_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Supervisor --}}
                    <div class="w-full sm:w-48">
                        <select wire:model.live="appliedSupervisor" class="select select-sm select-bordered w-full bg-base-100 text-xs font-medium" @if($accessLevel == 'supervisor') disabled @endif title="Filter Supervisor">
                            @if(count($supervisorOptions) != 1) <option value="">-- Semua Supervisor --</option> @endif
                            @foreach($supervisorOptions as $spv)
                                <option value="{{ $spv->supervisor_code }}">{{ $spv->description ?? $spv->supervisor_code }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Distributor --}}
                    <div class="w-full sm:w-56">
                        <select wire:model.live="appliedDistributor" class="select select-sm select-bordered w-full bg-base-100 text-xs font-medium truncate" title="Filter Distributor">
                            <option value="">-- Semua Distributor --</option>
                            @foreach($distributorOptions as $dist)
                                <option value="{{ $dist->distributor_code }}">{{ $dist->distributor_code }} - {{ $dist->distributor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Baris 2: Quick Filter Standpro, Pencarian, & Aksi --}}
                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-base-200">
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Standpro Quick Filter --}}
                        <div class="join shadow-2xs border border-base-300">
                            <button type="button" wire:click="setStandpro('')" class="btn btn-xs join-item {{ empty($appliedStandpro) ? 'btn-active btn-neutral' : 'btn-ghost' }}">Semua</button>
                            <button type="button" wire:click="setStandpro('sudah')" class="btn btn-xs join-item {{ $appliedStandpro === 'sudah' ? 'btn-active btn-success text-white font-bold' : 'btn-ghost text-success' }}" title="Toko JKS >= 300">Standar (≥ 300)</button>
                            <button type="button" wire:click="setStandpro('belum')" class="btn btn-xs join-item {{ $appliedStandpro === 'belum' ? 'btn-active btn-error text-white font-bold' : 'btn-ghost text-error' }}" title="Toko JKS < 300">Belum (&lt; 300)</button>
                        </div>

                        {{-- Per Page --}}
                        <div class="flex items-center gap-1.5 text-xs text-base-content/60">
                            <span>Baris:</span>
                            <select wire:model.live="perPage" class="select select-2xs select-bordered bg-base-100 font-medium">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                        </div>
                    </div>

                    {{-- Sisi Kanan: Search, Reset, Export --}}
                    <div class="flex items-center gap-2 ml-auto">
                        {{-- Search Input --}}
                        <div class="relative w-48 sm:w-64">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search" 
                                placeholder="Cari Salesman / Dist..." 
                                class="input input-sm input-bordered w-full pr-8 text-xs bg-base-100"
                            />
                            @if(!empty($search))
                                <button type="button" wire:click="$set('search', '')" class="absolute right-2 top-2 text-base-content/40 hover:text-base-content">
                                    <x-heroicon-m-x-mark class="w-4 h-4" />
                                </button>
                            @else
                                <span class="absolute right-2.5 top-2.5 text-base-content/40 pointer-events-none">
                                    <x-heroicon-m-magnifying-glass class="w-4 h-4" />
                                </span>
                            @endif
                        </div>

                        {{-- Tombol Export Excel --}}
                        <button class="btn btn-sm btn-success text-white gap-1.5 shadow-2xs" wire:click="exportExcel" wire:loading.class="opacity-50 pointer-events-none" wire:target="exportExcel" title="Export Rekapitulasi ke Excel">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" wire:loading.remove wire:target="exportExcel" />
                            <span wire:loading wire:target="exportExcel" class="loading loading-spinner loading-xs"></span>
                            <span class="hidden sm:inline text-xs font-semibold">Excel</span>
                        </button>

                        {{-- Tombol Reset --}}
                        <button class="btn btn-sm btn-ghost text-base-content/70 hover:text-error gap-1" wire:click="resetFilters" title="Reset Semua Filter">
                            <x-heroicon-o-arrow-path class="w-4 h-4" />
                            <span class="hidden sm:inline text-xs">Reset</span>
                        </button>
                    </div>
                </div>

            </div>

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
