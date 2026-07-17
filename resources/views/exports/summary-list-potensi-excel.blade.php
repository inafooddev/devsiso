<table>
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: left; background-color: #f3f4f6;">REGION</th>
            <th style="font-weight: bold; text-align: left; background-color: #f3f4f6;">AREA</th>
            <th style="font-weight: bold; text-align: left; background-color: #f3f4f6;">SUPERVISOR</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">TOTAL TOKO</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #0284c7;">MASUK JKS</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">SUDAH SKB</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #f59e0b;">SKB YG HARUS DI UPLOAD</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #16a34a;">APPROVE</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #dc2626;">REJECT</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #16a34a;">LENGKAP</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #dc2626;">BELUM</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">TOTAL TARGET</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">TARGET PRORATA</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">PENCAPAIAN</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">%</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6;">TOKO TRX</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #16a34a;">HIJAU</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #eab308;">KUNING</th>
            <th style="font-weight: bold; text-align: right; background-color: #f3f4f6; color: #dc2626;">MERAH</th>
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
                        <td style="text-align: right; color: #0284c7;">{{ $supervisor['total_jks'] }}</td>
                        <td style="text-align: right;">{{ $supervisor['sudah_skb'] }}</td>
                        <td style="text-align: right; color: #f59e0b; font-weight: bold;">{{ max(0, $supervisor['total_jks'] - $supervisor['sudah_skb']) }}</td>
                        <td style="text-align: right; color: #16a34a;">{{ $supervisor['skb_approve'] }}</td>
                        <td style="text-align: right; color: #dc2626;">{{ $supervisor['skb_reject'] }}</td>
                        <td style="text-align: right; color: #16a34a;">{{ $supervisor['data_lengkap'] }}</td>
                        <td style="text-align: right; color: #dc2626;">{{ $supervisor['data_belum'] }}</td>
                        <td style="text-align: right;">{{ $supervisor['total_target'] }}</td>
                        <td style="text-align: right;">{{ $supervisor['target_prorata'] }}</td>
                        <td style="text-align: right; font-weight: bold; color: #16a34a;">{{ $supervisor['total_achievement'] }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $supervisor['target_prorata'] > 0 ? round(($supervisor['total_achievement'] / $supervisor['target_prorata']) * 100, 1) : 0 }}%</td>
                        <td style="text-align: right; font-weight: bold;">{{ $supervisor['toko_transaksi'] }}</td>
                        <td style="text-align: right; font-weight: bold; color: #16a34a;">{{ $supervisor['toko_hijau'] }}</td>
                        <td style="text-align: right; font-weight: bold; color: #eab308;">{{ $supervisor['toko_kuning'] }}</td>
                        <td style="text-align: right; font-weight: bold; color: #dc2626;">{{ $supervisor['toko_merah'] }}</td>
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
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #0284c7;">{{ $area['total_jks'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['sudah_skb'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #f59e0b;">{{ max(0, $area['total_jks'] - $area['sudah_skb']) }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #16a34a;">{{ $area['skb_approve'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #dc2626;">{{ $area['skb_reject'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #16a34a;">{{ $area['data_lengkap'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #dc2626;">{{ $area['data_belum'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['total_target'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['target_prorata'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #16a34a;">{{ $area['total_achievement'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['target_prorata'] > 0 ? round(($area['total_achievement'] / $area['target_prorata']) * 100, 1) : 0 }}%</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe;">{{ $area['toko_transaksi'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #16a34a;">{{ $area['toko_hijau'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #eab308;">{{ $area['toko_kuning'] }}</td>
                    <td style="font-weight: bold; text-align: right; background-color: #e0f2fe; color: #dc2626;">{{ $area['toko_merah'] }}</td>
                </tr>
            @endforeach

            {{-- Region Subtotal --}}
            <tr>
                <td colspan="3" style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #7e22ce;">SUBTOTAL REGION {{ strtoupper($region['name']) }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['total_toko'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #0284c7;">{{ $region['total_jks'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['sudah_skb'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #f59e0b;">{{ max(0, $region['total_jks'] - $region['sudah_skb']) }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #16a34a;">{{ $region['skb_approve'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #dc2626;">{{ $region['skb_reject'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #16a34a;">{{ $region['data_lengkap'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #dc2626;">{{ $region['data_belum'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['total_target'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['target_prorata'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #16a34a;">{{ $region['total_achievement'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['target_prorata'] > 0 ? round(($region['total_achievement'] / $region['target_prorata']) * 100, 1) : 0 }}%</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff;">{{ $region['toko_transaksi'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #16a34a;">{{ $region['toko_hijau'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #eab308;">{{ $region['toko_kuning'] }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #f3e8ff; color: #dc2626;">{{ $region['toko_merah'] }}</td>
            </tr>

        @empty
            <tr>
                <td colspan="19" style="text-align: center;">Tidak ada data</td>
            </tr>
        @endforelse

        @if(count($records) > 0)
            @php
                $recordsColl = collect($records);
                $sumJks = collect($recordsColl)->sum('total_jks');
                $sumSkb = collect($recordsColl)->sum('sudah_skb');
            @endphp
            <tr>
                <td colspan="3" style="font-weight: bold; text-align: right; background-color: #cbd5e1;">GRAND TOTAL</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('total_toko') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #0284c7;">{{ $sumJks }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ $sumSkb }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #d97706;">{{ max(0, $sumJks - $sumSkb) }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #16a34a;">{{ collect($recordsColl)->sum('skb_approve') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #dc2626;">{{ collect($recordsColl)->sum('skb_reject') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #16a34a;">{{ collect($recordsColl)->sum('data_lengkap') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #dc2626;">{{ collect($recordsColl)->sum('data_belum') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('total_target') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('target_prorata') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #16a34a;">{{ collect($recordsColl)->sum('total_achievement') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('target_prorata') > 0 ? round((collect($recordsColl)->sum('total_achievement') / collect($recordsColl)->sum('target_prorata')) * 100, 1) : 0 }}%</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1;">{{ collect($recordsColl)->sum('toko_transaksi') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #16a34a;">{{ collect($recordsColl)->sum('toko_hijau') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #eab308;">{{ collect($recordsColl)->sum('toko_kuning') }}</td>
                <td style="font-weight: bold; text-align: right; background-color: #cbd5e1; color: #dc2626;">{{ collect($recordsColl)->sum('toko_merah') }}</td>
            </tr>
        @endif
    </tbody>
</table>
