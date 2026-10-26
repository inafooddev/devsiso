@if($kpiSummary && $kpiSummary->isNotEmpty())
<div class="bg-base-100 rounded-xl shadow-sm border border-base-300 shrink-0 overflow-hidden">
    {{-- Header Toggle Bar --}}
    <div class="px-3.5 py-2.5 flex items-center justify-between cursor-pointer hover:bg-base-200/50 transition-colors select-none bg-base-200/20"
         @click="showKpiSummary = !showKpiSummary">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                <x-heroicon-s-chart-bar class="w-4 h-4" />
            </div>
            <div class="flex items-center gap-2">
                <span class="font-bold text-xs md:text-sm text-base-content/90">Ringkasan KPI Salesman</span>
                <span class="badge badge-sm badge-ghost font-semibold">{{ $kpiSummary->count() }} SE</span>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button type="button" 
                    class="btn btn-xs gap-1.5 font-semibold transition-all"
                    :class="showKpiSummary ? 'btn-ghost text-base-content/70' : 'btn-primary btn-outline'">
                <span x-text="showKpiSummary ? 'Sembunyikan' : 'Tampilkan Summary'"></span>
                <x-heroicon-m-chevron-down class="w-3.5 h-3.5 transition-transform duration-200" 
                                           ::class="showKpiSummary ? 'rotate-180' : ''" />
            </button>
        </div>
    </div>

    {{-- Collapsible Table Content --}}
    <div x-show="showKpiSummary" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="border-t border-base-300 overflow-auto max-h-[40vh] relative">
        <table class="table table-sm w-full whitespace-nowrap border-separate border-spacing-0">
            @php
                $days = [
                    'h1' => 'Senin', 
                    'h2' => 'Selasa', 
                    'h3' => 'Rabu', 
                    'h4' => 'Kamis', 
                    'h5' => 'Jumat', 
                    'h6' => 'Sabtu'
                ];
            @endphp
            
            {{-- Thead --}}
            <thead class="text-xs uppercase bg-base-200 text-base-content shadow-sm sticky top-0 z-40 border-b-2 border-base-300 font-semibold">
                {{-- Baris 1: Parent Headers --}}
                <tr>
                    <th rowspan="2" class="bg-base-300 sticky left-0 z-50 border-b-2 border-r border-base-100 w-[80px] min-w-[80px] max-w-[80px] text-base-content font-bold tracking-wider">Kode SE</th>
                    <th rowspan="2" class="bg-base-300 sticky left-[80px] z-50 border-b-2 border-r-2 border-base-100 shadow-[5px_0_15px_rgba(0,0,0,0.1)] w-[180px] min-w-[180px] max-w-[180px] text-base-content font-bold tracking-wider">Salesman</th>
                    
                    <th rowspan="2" class="text-right pr-3 border-b-2 border-r border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">Total RO</th>
                    <th rowspan="2" class="text-right pr-3 border-b-2 border-r border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">Plan</th>
                    <th rowspan="2" class="text-right pr-3 border-b-2 border-r border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">Non Rute</th>
                    <th rowspan="2" class="text-right pr-3 border-b-2 border-r-2 border-base-100 bg-base-300 text-base-content/80 font-bold tracking-wider">Non GPS</th>
                    
                    <th colspan="2" class="text-center border-b-2 border-r-2 border-base-100 bg-base-300 text-base-content/90 font-bold tracking-wider">Perubahan JKS</th>
                    
                    @foreach($days as $key => $label)
                        <th colspan="3" class="text-center border-b-2 border-r-2 border-base-100 bg-base-300 text-base-content/90 font-bold tracking-wider">{{ $label }}</th>
                    @endforeach
                </tr>
                
                {{-- Baris 2: Child Headers --}}
                <tr>
                    <th class="text-right pr-3 bg-base-200 text-xs font-semibold tracking-wide text-base-content/80 border-b-2 border-r border-base-300">Penambahan</th>
                    <th class="text-right pr-3 bg-base-200 text-xs font-semibold tracking-wide text-base-content/80 border-b-2 border-r-2 border-base-100">Delete</th>
                    
                    @foreach($days as $key => $label)
                        <th class="text-right pr-2 bg-base-200 text-xs font-semibold text-primary tracking-wide border-b-2 border-r border-base-300">Gjl</th>
                        <th class="text-right pr-2 bg-base-200 text-xs font-semibold text-secondary tracking-wide border-b-2 border-r border-base-300">Gnp</th>
                        <th class="text-right pr-2 bg-base-200 text-xs font-semibold text-error tracking-wide border-b-2 border-r-2 border-base-100">Non Gps</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($kpiSummary as $row)
                    @php
                        $plan = $row->total_toko ?? 0;
                        $totalRo = $row->all_ro ?? $row->total_toko ?? 0;
                        $nonRute = $row->non_rute ?? 0;
                    @endphp
                    <tr class="hover:bg-base-300 transition-colors odd:bg-base-100 even:bg-base-200 group text-xs">
                        <td title="{{ $row->salesman_code }}" class="bg-inherit sticky left-0 z-30 border-r border-base-300 truncate w-[80px] min-w-[80px] max-w-[80px] text-base-content/70 font-mono">
                            {{ $row->salesman_code }}
                        </td>
                        <td title="{{ $row->salesman_name ?: $row->salesman_code }}" class="bg-inherit sticky left-[80px] z-30 border-r-2 border-base-300 truncate w-[180px] min-w-[180px] max-w-[180px] text-base-content/90 font-medium">
                            {{ $row->salesman_name ?: $row->salesman_code }}
                        </td>
                        
                        <td class="text-right pr-3 border-r border-base-300 font-bold {{ $totalRo < 300 ? 'text-error' : '' }}">
                            {{ number_format($totalRo, 0, ',', '.') }}
                        </td>
                        <td class="text-right pr-3 border-r border-base-300 font-bold {{ $plan < 300 ? 'text-error' : 'text-success' }}">
                            {{ number_format($plan, 0, ',', '.') }}
                        </td>
                        <td class="text-right pr-3 border-r border-base-300 font-bold {{ $nonRute > 0 ? 'text-warning' : 'text-base-content/40 font-normal' }}">
                            {{ number_format($nonRute, 0, ',', '.') }}
                        </td>
                        <td class="text-right pr-3 border-r-2 border-base-300 text-error font-bold">{{ number_format($row->non_gps ?? 0, 0, ',', '.') }}</td>
                        
                        <td class="text-right pr-3 border-r border-base-300 {{ ($row->penambahan ?? 0) > 0 ? 'text-info font-bold' : 'text-base-content/40' }}">{{ number_format($row->penambahan ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right pr-3 border-r-2 border-base-300 {{ ($row->deleted ?? 0) > 0 ? 'text-error font-bold' : 'text-base-content/40' }}">{{ number_format($row->deleted ?? 0, 0, ',', '.') }}</td>
                        
                        @foreach($days as $key => $label)
                            @php
                                $vGjl = $row->{$key.'_ganjil'} ?? 0;
                                $vGnp = $row->{$key.'_genap'} ?? 0;
                                $vNgps = $row->{$key.'_non_gps'} ?? 0;
                                
                                $clsGjl = ($vGjl > 0 && ($vGjl < 20 || $vGjl > 35)) ? 'bg-warning/40 text-warning-content font-bold' : 'text-primary';
                                $clsGnp = ($vGnp > 0 && ($vGnp < 20 || $vGnp > 35)) ? 'bg-warning/40 text-warning-content font-bold' : 'text-secondary';
                            @endphp
                            <td class="text-right pr-2 border-r border-base-300 {{ $clsGjl }}">{{ number_format($vGjl, 0, ',', '.') }}</td>
                            <td class="text-right pr-2 border-r border-base-300 {{ $clsGnp }}">{{ number_format($vGnp, 0, ',', '.') }}</td>
                            <td class="text-right pr-2 border-r-2 border-base-300 text-error">{{ number_format($vNgps, 0, ',', '.') }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            
            <tfoot class="text-xs font-bold bg-base-300 text-base-content sticky bottom-0 z-40 border-t-2 border-base-300">
                <tr>
                    <td colspan="2" class="bg-base-300 sticky left-0 z-50 border-t-2 border-r-2 border-base-100 shadow-[5px_0_15px_rgba(0,0,0,0.1)] text-right pr-3 uppercase">Grand Total</td>
                    
                    @php
                        $sumPlan = $kpiSummary->sum('total_toko');
                        $sumTotalRo = $kpiSummary->sum('all_ro') ?: $sumPlan;
                        $sumNonRute = $kpiSummary->sum('non_rute');
                        $totalSe = $kpiSummary->count();
                        $minTargetTotal = $totalSe * 300;
                    @endphp
                    
                    <td class="text-right pr-3 border-t-2 border-r border-base-100 font-bold {{ $sumTotalRo < $minTargetTotal ? 'text-error' : '' }}" title="Target Minimum RO: {{ number_format($minTargetTotal, 0, ',', '.') }} ({{ $totalSe }} SE × 300)">{{ number_format($sumTotalRo, 0, ',', '.') }}</td>
                    <td class="text-right pr-3 border-t-2 border-r border-base-100 font-bold {{ $sumPlan < $minTargetTotal ? 'text-error' : 'text-success' }}" title="Target Minimum Plan: {{ number_format($minTargetTotal, 0, ',', '.') }} ({{ $totalSe }} SE × 300)">{{ number_format($sumPlan, 0, ',', '.') }}</td>
                    <td class="text-right pr-3 border-t-2 border-r border-base-100 font-bold {{ $sumNonRute > 0 ? 'text-warning' : 'text-base-content/40 font-normal' }}">{{ number_format($sumNonRute, 0, ',', '.') }}</td>
                    <td class="text-right pr-3 border-t-2 border-r-2 border-base-100 text-error">{{ number_format($kpiSummary->sum('non_gps'), 0, ',', '.') }}</td>
                    
                    <td class="text-right pr-3 border-t-2 border-r border-base-100 text-info">{{ number_format($kpiSummary->sum('penambahan'), 0, ',', '.') }}</td>
                    <td class="text-right pr-3 border-t-2 border-r-2 border-base-100 text-error">{{ number_format($kpiSummary->sum('deleted'), 0, ',', '.') }}</td>
                    
                    @foreach($days as $key => $label)
                        <td class="text-right pr-2 border-t-2 border-r border-base-100 text-primary">{{ number_format($kpiSummary->sum($key.'_ganjil'), 0, ',', '.') }}</td>
                        <td class="text-right pr-2 border-t-2 border-r border-base-100 text-secondary">{{ number_format($kpiSummary->sum($key.'_genap'), 0, ',', '.') }}</td>
                        <td class="text-right pr-2 border-t-2 border-r-2 border-base-100 text-error">{{ number_format($kpiSummary->sum($key.'_non_gps'), 0, ',', '.') }}</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
