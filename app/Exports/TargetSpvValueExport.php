<?php

namespace App\Exports;

use App\Models\TargetPerDepo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TargetSpvValueExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $yearFilter;

    public function __construct($yearFilter = null)
    {
        $this->yearFilter = $yearFilter;
    }

    public function query()
    {
        $query = TargetPerDepo::query()
            ->select('bulan', 'region', 'area', 'cabang')
            ->selectRaw("SUM(CASE WHEN reg_fest = 'REG' THEN target ELSE 0 END) as target_reg")
            ->selectRaw("SUM(CASE WHEN reg_fest = 'FEST' THEN target ELSE 0 END) as target_fest")
            ->selectRaw("SUM(target) as total_target")
            ->groupBy('bulan', 'region', 'area', 'cabang');

        if (!empty($this->yearFilter)) {
            $query->where('bulan', 'like', $this->yearFilter . '-%');
        }

        return $query->orderBy('bulan')->orderBy('region')->orderBy('area')->orderBy('cabang');
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Region',
            'Area',
            'Cabang',
            'Target Reg',
            'Target Fest',
            'Total Target',
        ];
    }

    public function map($row): array
    {
        return [
            $row->bulan,
            $row->region,
            $row->area,
            $row->cabang,
            $row->target_reg,
            $row->target_fest,
            $row->total_target,
        ];
    }
}
