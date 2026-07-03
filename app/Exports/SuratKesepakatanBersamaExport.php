<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SuratKesepakatanBersamaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Kuartal',
            'Region Code',
            'Region Name',
            'Area Code',
            'Area Name',
            'Supervisor Code',
            'Distributor Code',
            'Distributor Name',
            'Customer Code',
            'Customer Name',
            'Status Approval',
            'Alasan Penolakan',
        ];
    }

    public function map($row): array
    {
        $status = 'Pending';
        if ($row->is_approved === true) $status = 'Approve';
        if ($row->is_approved === false) $status = 'Reject';

        return [
            $row->kuartal,
            $row->region_code,
            $row->region_name,
            $row->area_code,
            $row->area_name,
            $row->supervisor_code,
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_code,
            $row->customer_name,
            $status,
            $row->reason ?? '-',
        ];
    }
}
