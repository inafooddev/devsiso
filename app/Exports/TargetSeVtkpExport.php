<?php

namespace App\Exports;

use App\Models\TargetSeVtkp;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TargetSeVtkpExport implements FromQuery, WithHeadings, WithMapping
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
            $query->where('bulan', $this->bulanFilter);
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
}
