<?php

namespace App\Exports;

use App\Models\TargetSeValue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TargetSeValueExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $bulanFilter;

    public function __construct($bulanFilter = null)
    {
        $this->bulanFilter = $bulanFilter;
    }

    public function query()
    {
        $query = TargetSeValue::query();

        if (!empty($this->bulanFilter)) {
            $query->where('bulan', $this->bulanFilter);
        }

        return $query->orderBy('bulan')->orderBy('distributor_code')->orderBy('salesman_code');
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Distributor Code',
            'Salesman Code',
            'Target',
        ];
    }

    public function map($row): array
    {
        return [
            $row->bulan,
            $row->distributor_code,
            $row->salesman_code,
            $row->target,
        ];
    }

    public function title(): string
    {
        return 'Target SE Value';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
