<?php

namespace App\Exports;

use App\Models\SellingInDistributorMapping;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SellingInDistributorMappingExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return SellingInDistributorMapping::query()->orderBy('divisi')->orderBy('wilayah');
    }

    public function headings(): array
    {
        return [
            'DIVISI',
            'WILAYAH',
            'KODE DISTRIBUTOR',
            'DISTRIBUTOR',
            'DISTRIBUTOR CODE (MASTER)'
        ];
    }

    public function map($row): array
    {
        return [
            $row->divisi,
            $row->wilayah,
            $row->kode_distributor,
            $row->distributor,
            $row->distributor_code
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
