<?php

namespace App\Exports;

use App\Models\TargetKacab;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TargetKacabExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $monthFilter;

    public function __construct($monthFilter = null)
    {
        $this->monthFilter = $monthFilter;
    }

    public function query()
    {
        $query = TargetKacab::query();

        if (!empty($this->monthFilter)) {
            $query->where('bulan', $this->monthFilter);
        }

        return $query->orderBy('bulan', 'desc')->orderBy('cabang');
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Cabang',
            'Target',
        ];
    }

    public function map($row): array
    {
        return [
            $row->bulan,
            $row->cabang,
            $row->target,
        ];
    }
}
