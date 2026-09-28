<div class="overflow-x-auto w-full max-w-full my-4 border border-base-300 rounded-lg">
    <table class="table table-xs kpi-table bg-base-100 border-collapse m-0">
        <thead x-data="{ row1Height: 36 }" x-init="$nextTick(() => { row1Height = $refs.row1.offsetHeight; })" @resize.window="row1Height = $refs.row1.offsetHeight">
            <tr class="bg-neutral text-neutral-content" x-ref="row1">
                <th class="sticky-col bg-neutral align-middle" rowspan="2">Indikator (KPI & PI)</th>
                <th class="bobot-col bg-neutral align-middle" rowspan="2">Bobot</th>
                @foreach($months as $monthName)
                    <th colspan="4" class="border-l border-neutral-content/30">{{ $monthName }}</th>
                @endforeach
            </tr>
            <tr class="bg-neutral text-neutral-content">
                @foreach($months as $monthName)
                    <th class="border-l border-neutral-content/30 text-[10px]" :style="`top: ${row1Height}px;`">TARGET</th>
                    <th class="text-[10px]" :style="`top: ${row1Height}px;`">ACTUAL</th>
                    <th class="text-[10px]" :style="`top: ${row1Height}px;`">%ACH</th>
                    <th class="text-[10px]" :style="`top: ${row1Height}px;`">SCORE</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($kpis as $key => $kpi)
                <tr class="hover:bg-base-200/50">
                    <td class="sticky-col bg-base-100 font-medium whitespace-normal">{{ $kpi['label'] }}</td>
                    <td class="bobot-col font-bold text-primary">{{ $kpi['bobot'] }}%</td>
                    
                    @for($m = 1; $m <= 12; $m++)
                        @php 
                            $cell = $nodeData['months'][$m][$key]; 
                            $format = in_array($key, ['so', 'rwo']) ? 'number' : 'decimal';
                        @endphp
                        <td class="border-l border-base-300">
                            {{ number_format($cell['target'], 0, ',', '.') }}
                        </td>
                        <td>
                            {{ number_format($cell['actual'], 0, ',', '.') }}
                        </td>
                        <td class="{{ getColorClass($cell['ach']) }}">
                            {{ number_format($cell['ach'], 1, ',', '.') }}%
                        </td>
                        <td class="font-bold">
                            {{ number_format($cell['score'], 1, ',', '.') }}%
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-base-200/80">
            <tr>
                <td class="sticky-col bg-base-200 font-bold">Skor Akhir</td>
                <td class="bobot-col bg-base-200 font-bold text-primary">100%</td>
                @for($m = 1; $m <= 12; $m++)
                    @php
                        $totalScore = 0;
                        foreach($kpis as $k => $v) {
                            $totalScore += $nodeData['months'][$m][$k]['score'];
                        }
                    @endphp
                    <td class="border-l border-base-300"></td>
                    <td></td>
                    <td class="text-center font-bold text-primary">
                        {{ number_format($totalScore, 1, ',', '.') }}%
                    </td>
                    <td class="text-center font-bold text-primary">
                        {{ number_format($totalScore, 1, ',', '.') }}%
                    </td>
                @endfor
            </tr>
        </tfoot>
    </table>
</div>
