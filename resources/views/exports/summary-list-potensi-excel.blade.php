<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #E2E8F0;">Region</th>
            <th style="font-weight: bold; background-color: #E2E8F0;">Area</th>
            <th style="font-weight: bold; background-color: #E2E8F0;">Supervisor / Cabang</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Total Toko</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Masuk JKS</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Sudah SKB</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Approve</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Reject</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Lengkap</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Belum</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Total Target</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Target Prorata</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Pencapaian</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">%</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Toko Trx</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Hijau</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Kuning</th>
            <th style="font-weight: bold; background-color: #E2E8F0; text-align: right;">Merah</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $region)
            @foreach($region['areas'] as $area)
                @foreach($area['supervisors'] as $supervisor)
                    @php
                        $spvPct = $supervisor['target_prorata'] > 0 ? round(($supervisor['total_achievement'] / $supervisor['target_prorata']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td style="font-weight: bold;">{{ $region['name'] }}</td>
                        <td style="font-weight: bold;">{{ $area['name'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['name'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['total_toko'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['total_jks'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['sudah_skb'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['skb_approve'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['skb_reject'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['data_lengkap'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['data_belum'] }}</td>
                        <td style="font-weight: bold;">{{ round($supervisor['total_target']) }}</td>
                        <td style="font-weight: bold;">{{ round($supervisor['target_prorata']) }}</td>
                        <td style="font-weight: bold;">{{ round($supervisor['total_achievement']) }}</td>
                        <td style="font-weight: bold;">{{ $spvPct }}%</td>
                        <td style="font-weight: bold;">{{ $supervisor['toko_transaksi'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['toko_hijau'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['toko_kuning'] }}</td>
                        <td style="font-weight: bold;">{{ $supervisor['toko_merah'] }}</td>
                    </tr>

                    @foreach($supervisor['cabang'] as $cabang)
                        @php
                            $cabPct = $cabang['target_prorata'] > 0 ? round(($cabang['total_achievement'] / $cabang['target_prorata']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>{{ $region['name'] }}</td>
                            <td>{{ $area['name'] }}</td>
                            <td>   - {{ $cabang['distributor_name'] }}</td>
                            <td>{{ $cabang['total_toko'] }}</td>
                            <td>{{ $cabang['total_jks'] }}</td>
                            <td>{{ $cabang['sudah_skb'] }}</td>
                            <td>{{ $cabang['skb_approve'] }}</td>
                            <td>{{ $cabang['skb_reject'] }}</td>
                            <td>{{ $cabang['data_lengkap'] }}</td>
                            <td>{{ $cabang['data_belum'] }}</td>
                            <td>{{ round($cabang['total_target']) }}</td>
                            <td>{{ round($cabang['target_prorata']) }}</td>
                            <td>{{ round($cabang['total_achievement']) }}</td>
                            <td>{{ $cabPct }}%</td>
                            <td>{{ $cabang['toko_transaksi'] }}</td>
                            <td>{{ $cabang['toko_hijau'] }}</td>
                            <td>{{ $cabang['toko_kuning'] }}</td>
                            <td>{{ $cabang['toko_merah'] }}</td>
                        </tr>
                    @endforeach
                @endforeach

                @php
                    $areaPct = $area['target_prorata'] > 0 ? round(($area['total_achievement'] / $area['target_prorata']) * 100, 1) : 0;
                @endphp
                <tr>
                    <td colspan="3" style="font-weight: bold; background-color: #E0F2FE; text-align: right;">SUBTOTAL AREA {{ strtoupper($area['name']) }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['total_toko'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['total_jks'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['sudah_skb'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['skb_approve'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['skb_reject'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['data_lengkap'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['data_belum'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ round($area['total_target']) }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ round($area['target_prorata']) }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ round($area['total_achievement']) }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $areaPct }}%</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['toko_transaksi'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['toko_hijau'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['toko_kuning'] }}</td>
                    <td style="font-weight: bold; background-color: #E0F2FE;">{{ $area['toko_merah'] }}</td>
                </tr>
            @endforeach

            @php
                $regPct = $region['target_prorata'] > 0 ? round(($region['total_achievement'] / $region['target_prorata']) * 100, 1) : 0;
            @endphp
            <tr>
                <td colspan="3" style="font-weight: bold; background-color: #DBEAFE; text-align: right;">SUBTOTAL REGION {{ strtoupper($region['name']) }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['total_toko'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['total_jks'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['sudah_skb'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['skb_approve'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['skb_reject'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['data_lengkap'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['data_belum'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ round($region['total_target']) }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ round($region['target_prorata']) }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ round($region['total_achievement']) }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $regPct }}%</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['toko_transaksi'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['toko_hijau'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['toko_kuning'] }}</td>
                <td style="font-weight: bold; background-color: #DBEAFE;">{{ $region['toko_merah'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="18" style="text-align: center;">Tidak ada data summary ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
    @if(count($records) > 0)
        @php
            $recordsColl = collect($records);
            $grandTargetProrata = $recordsColl->sum('target_prorata');
            $grandAchievement = $recordsColl->sum('total_achievement');
            $grandPct = $grandTargetProrata > 0 ? round(($grandAchievement / $grandTargetProrata) * 100, 1) : 0;
        @endphp
        <tfoot>
            <tr>
                <td colspan="3" style="font-weight: bold; background-color: #CBD5E1; text-align: right;">GRAND TOTAL NASIONAL</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('total_toko') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('total_jks') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('sudah_skb') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('skb_approve') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('skb_reject') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('data_lengkap') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('data_belum') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ round($recordsColl->sum('total_target')) }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ round($grandTargetProrata) }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ round($grandAchievement) }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $grandPct }}%</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('toko_transaksi') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('toko_hijau') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('toko_kuning') }}</td>
                <td style="font-weight: bold; background-color: #CBD5E1;">{{ $recordsColl->sum('toko_merah') }}</td>
            </tr>
        </tfoot>
    @endif
</table>
