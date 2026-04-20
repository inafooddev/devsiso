<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ListTokoParetoExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Region Code',
            'Region Name',
            'Area Code',
            'Area Name',
            'Supervisor Code',
            'Supervisor Name',
            'Distributor Code',
            'Distributor Name',
            'Customer Code PRC',
            'Customer Name',
            'Customer Address',
            'Kecamatan',
            'Desa',
            'Latitude',
            'Longitude',
            'Pilar',
            'Target',
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_code,
            $row->region_name,
            $row->area_code,
            $row->area_name,
            $row->supervisor_code,
            $row->supervisor_name,
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_code_prc,
            $row->customer_name,
            $row->customer_address,
            $row->kecamatan,
            $row->desa,
            $row->latitude,
            $row->longitude,
            $row->pilar,
            $row->target,
        ];
    }
}