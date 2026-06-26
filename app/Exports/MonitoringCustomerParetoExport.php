<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonitoringCustomerParetoExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'Region',
            'Area',
            'Supervisor',
            'Distributor Code',
            'Distributor Name',
            'Customer Code',
            'Uniq Kd',
            'Customer Name',
            'Address',
            'Pilar',
            'Target',
            'RSM (Plan)',
            'ASM (Plan)',
            'SPV (Plan)',
            'Status',
        ];
    }

    public function map($row): array
    {
        $hasVisit = $row->rsm > 0 || $row->asm > 0 || $row->spv > 0;
        $status = $hasVisit ? 'Masuk Plan' : 'Belum Diplan';

        return [
            $row->region_name,
            $row->area_name,
            $row->supervisor_name ?? '-',
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_code_prc,
            $row->uniq_kd ?? '-',
            $row->customer_name,
            $row->customer_address,
            $row->pilar ?? '-',
            $row->target ?? 0,
            $row->rsm ?? 0,
            $row->asm ?? 0,
            $row->spv ?? 0,
            $status,
        ];
    }
}
