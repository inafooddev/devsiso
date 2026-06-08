<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MonitoringOutletParetoExport implements FromArray, WithHeadings, WithMapping
{
    protected $data;
    protected $type;

    public function __construct(array $data, string $type = 'summary')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        if ($this->type === 'summary') {
            return [
                'Region Code',
                'Region Name',
                'Area Code',
                'Area Name',
                'Supervisor Code',
                'Supervisor Name',
                'Distributor Code',
                'Distributor Name',
                'Pilar',
                'Total',
                'Visited',
                'Not Visited',
                'Visit Rate (%)',
                'RSM (Visit Region)',
                'ASM (Visit Area)',
                'SPV (Visit Supervisor)',
            ];
        }

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
            'Uniq KD',
            'Customer Name',
            'Pilar',
            'RSM (Visit Region)',
            'ASM (Visit Area)',
            'SPV (Visit Supervisor)',
            'Status Visit',
        ];
    }

    public function map($row): array
    {
        if ($this->type === 'summary') {
            $total = (int) ($row->total_outlets ?? 0);
            $visited = (int) ($row->visited_outlets ?? 0);
            $notVisited = $total - $visited;
            $rate = $total > 0 ? round(($visited / $total) * 100, 1) : 0;

            return [
                $row->region_code,
                $row->region_name,
                $row->area_code,
                $row->area_name,
                $row->supervisor_code,
                $row->supervisor_name,
                $row->distributor_code,
                $row->distributor_name,
                $row->pilar,
                $total,
                $visited,
                $notVisited,
                $rate,
                $row->visit_region,
                $row->visit_area,
                $row->visit_supervisor,
            ];
        }

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
            $row->pilar,
            $row->visit_region,
            $row->visit_area,
            $row->visit_supervisor,
            $row->status_visit,
        ];
    }
}
