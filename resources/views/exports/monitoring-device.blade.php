<table>
    <thead>
        <tr>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Region</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Area</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Depo/Cabang</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Distributor</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Sales Code</th>
            <th rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Nama Sales</th>
            @foreach($months as $m)
                <th colspan="4" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #e0e7ff; border: 1px solid #000000;">{{ $monthHeaders[$m] }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach($months as $m)
                <th style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000; width: 150px;">Foto Depan</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000; width: 150px;">Foto Belakang</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Kondisi HP</th>
                <th style="font-weight: bold; text-align: center; vertical-align: middle; background-color: #f3f4f6; border: 1px solid #000000;">Kondisi Kartu</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($salesData as $row)
            <tr>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $row->region_name }}</td>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $row->area_name }}</td>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $row->branch_name }}</td>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $row->distributor_name }}</td>
                <td style="vertical-align: middle; text-align: center; border: 1px solid #000000;">{{ $row->sales_code }}</td>
                <td style="vertical-align: middle; border: 1px solid #000000;">{{ $row->sales_name }}</td>
                @foreach($months as $m)
                    @php
                        $mData = $monitoringData[$row->distributor_code . '_' . $row->sales_code][$m] ?? null;
                    @endphp
                    <td style="vertical-align: middle; text-align: center; border: 1px solid #000000;"></td>
                    <td style="vertical-align: middle; text-align: center; border: 1px solid #000000;"></td>
                    <td style="vertical-align: middle; text-align: center; border: 1px solid #000000;">{{ $mData['kondisi_hp'] ?? '-' }}</td>
                    <td style="vertical-align: middle; text-align: center; border: 1px solid #000000;">{{ $mData['kondisi_kartu'] ?? '-' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
