<table>
    <!-- Top Metadata Block (Baris 1 - 7) -->
    <tr>
        <td style="font-weight: bold;">REGION</td>
        <td style="font-weight: bold;">{{ $meta['region'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">BRANCH</td>
        <td style="font-weight: bold;">{{ $meta['branch'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">SELLINGPOINT</td>
        <td style="font-weight: bold;">{{ $meta['sellingpoint'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">SALESMAN</td>
        <td style="font-weight: bold;">{{ $salesman }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">TAHUN</td>
        <td style="font-weight: bold;">{{ $meta['tahun'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">BULAN</td>
        <td style="font-weight: bold;">{{ $meta['bulan'] ?? '' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">TYPE</td>
        <td style="font-weight: bold;">PERCUSTOMER</td>
    </tr>

    <!-- Table Header (Baris 8 & 9, Tanpa Baris Kosong) -->
    <thead>
        <tr>
            <th rowspan="2" style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">RAYON</th>
            <th rowspan="2" style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">DISTRICT</th>
            <th rowspan="2" style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">MATERIAL GROUP</th>
            <th rowspan="2" style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">OUTLET</th>
            <th colspan="5" style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">TARGET</th>
        </tr>
        <tr>
            <th style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">VOLUME/QTY</th>
            <th style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">NOTA</th>
            <th style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">EC</th>
            <th style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">ITEM PER NOTA</th>
            <th style="background-color: #1F3864; color: #FFFFFF; font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000;">VALUE</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td style="border: 1px solid #D9D9D9;"></td>
                <td style="border: 1px solid #D9D9D9;"></td>
                <td style="border: 1px solid #D9D9D9;"></td>
                <td style="border: 1px solid #D9D9D9; text-align: left;">{{ $row->outlet }}</td>
                <td style="border: 1px solid #D9D9D9;"></td>
                <td style="border: 1px solid #D9D9D9;"></td>
                <td style="border: 1px solid #D9D9D9;"></td>
                <td style="border: 1px solid #D9D9D9;"></td>
                <td style="border: 1px solid #D9D9D9; text-align: right; font-weight: bold;">{{ (float) $row->value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
