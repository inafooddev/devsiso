@if($kpiSummary && $kpiSummary->isNotEmpty())
<div class="overflow-auto bg-base-100 w-full h-fit relative shadow-sm border border-base-300 rounded-xl mb-4 shrink-0 max-h-[40vh]">
    <table class="table table-sm w-full whitespace-nowrap border-separate border-spacing-0">
        @php
            $days = [
                'h1' => 'Senin', 
                'h2' => 'Selasa', 
                'h3' => 'Rabu', 
                'h4' => 'Kamis', 
                'h5' => 'Jumat', 
                'h6' => 'Sabtu',
                'h7' => 'Minggu'
            ];
        @endphp
        
        {{-- Thead --}}
        <thead class="text-[10px] uppercase bg-base-200 text-base-content shadow-sm sticky top-0 z-40 border-b-2 border-base-300">
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
                <th class="text-right pr-3 bg-base-200 text-[9px] tracking-wide text-base-content/80 border-b-2 border-r border-base-300">Penambahan</th>
                <th class="text-right pr-3 bg-base-200 text-[9px] tracking-wide text-base-content/80 border-b-2 border-r-2 border-base-100">Delete</th>
                
                @foreach($days as $key => $label)
                    <th class="text-right pr-2 bg-base-200 text-[9px] text-primary tracking-wide border-b-2 border-r border-base-300">Gjl</th>
                    <th class="text-right pr-2 bg-base-200 text-[9px] text-secondary tracking-wide border-b-2 border-r border-base-300">Gnp</th>
                    <th class="text-right pr-2 bg-base-200 text-[9px] text-error tracking-wide border-b-2 border-r-2 border-base-100">Non Gps</th>
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
                <tr class="hover:bg-base-300 transition-colors odd:bg-base-100 even:bg-base-200 group text-[11px]">
                    <td title="{{ $row->salesman_code }}" class="bg-inherit sticky left-0 z-30 border-r border-base-300 truncate w-[80px] min-w-[80px] max-w-[80px] text-base-content/70">
                        {{ $row->salesman_code }}
                    </td>
                    <td title="{{ $row->salesman_name ?: $row->salesman_code }}" class="bg-inherit sticky left-[80px] z-30 border-r-2 border-base-300 truncate w-[180px] min-w-[180px] max-w-[180px] text-base-content/90 font-medium">
                        {{ $row->salesman_name ?: $row->salesman_code }}
                    </td>
                    
                    <td class="text-right pr-3 border-r border-base-300 font-bold {{ $totalRo < 300 ? 'text-error' : '' }}">
                        {{ number_format($totalRo, 0, ',', '.') }}
                    </td>
                    <td class="text-right pr-3 border-r border-base-300 text-success font-bold">{{ number_format($plan, 0, ',', '.') }}</td>
                    <td class="text-right pr-3 border-r border-base-300 text-warning-content font-bold">{{ number_format($nonRute, 0, ',', '.') }}</td>
                    <td class="text-right pr-3 border-r-2 border-base-300 text-error font-bold">{{ number_format($row->non_gps ?? 0, 0, ',', '.') }}</td>
                    
                    <td class="text-right pr-3 border-r border-base-300 {{ ($row->penambahan ?? 0) > 0 ? 'text-info font-bold' : 'text-base-content/40' }}">{{ number_format($row->penambahan ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right pr-3 border-r-2 border-base-300 {{ ($row->deleted ?? 0) > 0 ? 'text-error font-bold' : 'text-base-content/40' }}">{{ number_format($row->deleted ?? 0, 0, ',', '.') }}</td>
                    
                    @foreach($days as $key => $label)
                        @php
                            $vGjl = $row->{$key.'_ganjil'} ?? 0;
                            $vGnp = $row->{$key.'_genap'} ?? 0;
                            $vNgps = $row->{$key.'_non_gps'} ?? 0;
                            
                            // Kita abaikan 0 supaya hari libur (Minggu) tidak ikut kuning semua
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
        
        <tfoot class="text-[11px] font-bold bg-base-300 text-base-content sticky bottom-0 z-40 border-t-2 border-base-300">
            <tr>
                <td colspan="2" class="bg-base-300 sticky left-0 z-50 border-t-2 border-r-2 border-base-100 shadow-[5px_0_15px_rgba(0,0,0,0.1)] text-right pr-3 uppercase">Grand Total</td>
                
                @php
                    $sumPlan = $kpiSummary->sum('total_toko');
                    $sumTotalRo = $kpiSummary->sum('all_ro') ?: $sumPlan;
                    $sumNonRute = $kpiSummary->sum('non_rute');
                @endphp
                
                <td class="text-right pr-3 border-t-2 border-r border-base-100">{{ number_format($sumTotalRo, 0, ',', '.') }}</td>
                <td class="text-right pr-3 border-t-2 border-r border-base-100 text-success">{{ number_format($sumPlan, 0, ',', '.') }}</td>
                <td class="text-right pr-3 border-t-2 border-r border-base-100 text-warning-content">{{ number_format($sumNonRute, 0, ',', '.') }}</td>
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
@endif
