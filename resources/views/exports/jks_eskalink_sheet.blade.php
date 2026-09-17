<table>
    @if($schedules->isNotEmpty())
        @php
            $first = $schedules->first();
        @endphp
        
        {{-- Header Block --}}
        <tr>
            <td>REGION:</td>
            <td>{{ $first->region }}</td>
        </tr>
        <tr>
            <td>ENTITY:</td>
            <td>{{ $first->entity }}</td>
        </tr>
        <tr>
            <td>BRANCH:</td>
            <td>{{ $first->branch }}</td>
        </tr>
        <tr>
            <td>SLSNO:</td>
            <td>{{ $first->slsno }}</td>
        </tr>
        <tr>
            <td>FLAG DELETE</td>
            <td>{{ $flagDelete }}</td>
        </tr>
        
        {{-- Table Header --}}
        <tr>
            <th>NORUTE</th>
            <th>CUSTNO</th>
            <th>H1</th>
            <th>H2</th>
            <th>H3</th>
            <th>H4</th>
            <th>H5</th>
            <th>H6</th>
            <th>H7</th>
            <th>M1</th>
            <th>M2</th>
            <th>M3</th>
            <th>M4</th>
        </tr>
        
        {{-- Table Data --}}
        @foreach($schedules as $row)
            <tr>
                <td>{{ $row->calculated_norute }}</td>
                <td>{{ $row->custno }}</td>
                <td>{{ $row->h1 }}</td>
                <td>{{ $row->h2 }}</td>
                <td>{{ $row->h3 }}</td>
                <td>{{ $row->h4 }}</td>
                <td>{{ $row->h5 }}</td>
                <td>{{ $row->h6 }}</td>
                <td>{{ $row->h7 }}</td>
                <td>{{ $row->m1 }}</td>
                <td>{{ $row->m2 }}</td>
                <td>{{ $row->m3 }}</td>
                <td>{{ $row->m4 }}</td>
            </tr>
        @endforeach
    @else
        <tr><td>Data tidak ditemukan</td></tr>
    @endif
</table>
