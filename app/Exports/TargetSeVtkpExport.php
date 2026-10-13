<?php

namespace App\Exports;

use App\Models\TargetSeVtkp;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TargetSeVtkpExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles
{
    use Exportable;

    protected $bulanFilter;

    public function __construct($bulanFilter = null)
    {
        $this->bulanFilter = $bulanFilter;
    }

    public function query()
    {
        $query = TargetSeVtkp::query();

        if (!empty($this->bulanFilter)) {
            if (is_array($this->bulanFilter)) {
                $query->whereIn('bulan', $this->bulanFilter);
            } else {
                $query->where('bulan', $this->bulanFilter);
            }
        }

        return $query->orderBy('bulan')->orderBy('distributor_code')->orderBy('salesman_code')->orderBy('produk_grup');
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Distributor Code',
            'Salesman Code',
            'Produk Grup',
            'Target',
        ];
    }

    public function map($row): array
    {
        return [
            $row->bulan,
            $row->distributor_code,
            $row->salesman_code,
            $row->produk_grup,
            $row->target,
        ];
    }

    public function title(): string
    {
        return 'Target SE VTKP';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
