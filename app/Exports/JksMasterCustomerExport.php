<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JksMasterCustomerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        // Remove limit/offset if pagination was applied
        $this->query->limit = null;
        $this->query->offset = null;
        return $this->query->get();
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
            'Customer Code',
            'Uniq Kd',
            'Customer Name',
            'Customer Address',
            'Kecamatan',
            'Desa',
            'Latitude',
            'Longitude',
            'Pilar',
            'Target',
            'Keterangan',
            'On Plan',
            'Pareto',
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
            $row->customer_code,
            $row->uniq_kd,
            $row->customer_name,
            $row->customer_address,
            $row->kecamatan,
            $row->desa,
            $row->latitude,
            $row->longitude,
            $row->pilar,
            $row->target,
            $row->keterangan,
            $row->on_plan,
            $row->pareto,
        ];
    }
}
