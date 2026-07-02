<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ListPotensiRwoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Supervisor Name',
            'Distributor Code',
            'Distributor Name',
            'Customer PRC',
            'Customer Code',
            'Customer Name',
            'Alamat',
            'Total Target',
            'Reward Percent',
            'PIC',
            'Status SKB',
        ];
    }

    public function map($row): array
    {
        return [
            $row->kuartal,
            $row->region_code,
            $row->region_name,
            $row->area_code,
            $row->area_name,
            $row->supervisor_code,
            $row->supervisor_name,
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_prc ?? '-',
            $row->customer_code,
            $row->customer_name,
            $row->alamat,
            $row->total_target,
            ($row->reward_percent * 100) . '%',
            $row->pic,
            $row->status_skb,
        ];
    }
}
