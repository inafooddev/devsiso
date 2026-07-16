<table>
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: left; background-color: #f3f4f6;">REGION</th>
            <th style="font-weight: bold; text-align: left; background-color: #f3f4f6;">AREA</th>
            <th style="font-weight: bold; text-align: left; background-color: #f3f4f6;">SUPERVISOR</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">TOTAL TOKO</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">TOTAL TARGET</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #0284c7;">MASUK JKS</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">SUDAH SKB</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #16a34a;">APPROVE</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #dc2626;">REJECT</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $region)
            @php $isFirstRegionRow = true; @endphp
            @foreach($region['areas'] as $area)
                @php $isFirstAreaRow = true; @endphp
                @foreach($area['supervisors'] as $supervisor)
                    <tr>
                        <td>{{ $isFirstRegionRow ? $region['name'] : '' }}</td>
                        <td>{{ $isFirstAreaRow ? $area['name'] : '' }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['name'] }}</td>
                        <td style="text-align: right;">{{ $supervisor['total_toko'] }}</td>
                        <td style="text-align: right;">{{ $supervisor['total_target'] }}</td>
                        <td style="text-align: right; color: #0284c7;">{{ $supervisor['total_jks'] }}</td>
                        <td style="text-align: right;">{{ $supervisor['sudah_skb'] }}</td>
                        <td style="text-align: right; color: #16a34a;">{{ $supervisor['skb_approve'] }}</td>
                        <td style="text-align: right; color: #dc2626;">{{ $supervisor['skb_reject'] }}</td>
                    </tr>
                    @php 
                        $isFirstRegionRow = false; 
                        $isFirstAreaRow = false;
                    @endphp
                @endforeach

                {{-- Area Subtotal --}}
                <tr>
                    <td colspan="3" style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #0284c7;">SUBTOTAL AREA {{ strtoupper($area['name']) }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['total_toko'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['total_target'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #0284c7;">{{ $area['total_jks'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['sudah_skb'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #16a34a;">{{ $area['skb_approve'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #dc2626;">{{ $area['skb_reject'] }}</td>
                </tr>
            @endforeach

            {{-- Region Subtotal --}}
            <tr>
                <td colspan="3" style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #7e22ce;">SUBTOTAL REGION {{ strtoupper($region['name']) }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['total_toko'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['total_target'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #0284c7;">{{ $region['total_jks'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['sudah_skb'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #16a34a;">{{ $region['skb_approve'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #dc2626;">{{ $region['skb_reject'] }}</td>
            </tr>

        @empty
            <tr>
                <td colspan="9" style="text-align: center;">Tidak ada data</td>
            </tr>
        @endforelse

        @if(count($records) > 0)
            @php
                $recordsColl = collect($records);
            @endphp
            <tr>
                <td colspan="3" style="font-weight: bold; text-align: right; background-color: #cbd5e1;">GRAND TOTAL</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('total_toko') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('total_target') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #0284c7;">{{ collect($recordsColl)->sum('total_jks') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('sudah_skb') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #16a34a;">{{ collect($recordsColl)->sum('skb_approve') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #dc2626;">{{ collect($recordsColl)->sum('skb_reject') }}</td>
            </tr>
        @endif
    </tbody>
</table>
