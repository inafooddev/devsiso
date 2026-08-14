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

    protected $yearFilter;

    public function __construct($yearFilter = null)
    {
        $this->yearFilter = $yearFilter;
    }

    public function query()
    {
        $query = TargetKacab::query();

        if (!empty($this->yearFilter)) {
            $query->where('tahun', $this->yearFilter);
        }

        return $query->orderBy('tahun', 'desc')->orderBy('cabang');
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Cabang',
            'Nama Kacab',
            'Target',
            'Insentif',
        ];
    }

    public function map($row): array
    {
        return [
            $row->tahun,
            $row->cabang,
            $row->nama_kacab,
            $row->target,
            $row->insentif,
        ];
    }
}
