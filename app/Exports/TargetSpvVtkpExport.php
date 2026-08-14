<?php

namespace App\Exports;

use App\Models\TargetSpvVtkp;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TargetSpvVtkpExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $yearFilter;
    protected $quarterFilter;
    protected $months;

    public function __construct($yearFilter = null, $quarterFilter = 'Q1')
    {
        $this->yearFilter = $yearFilter ?: date('Y');
        $this->quarterFilter = $quarterFilter;
        
        $y = $this->yearFilter;
        $q = $this->quarterFilter;
        
        if ($q == 'Q1') $this->months = [sprintf('%s-01', $y), sprintf('%s-02', $y), sprintf('%s-03', $y)];
        elseif ($q == 'Q2') $this->months = [sprintf('%s-04', $y), sprintf('%s-05', $y), sprintf('%s-06', $y)];
        elseif ($q == 'Q3') $this->months = [sprintf('%s-07', $y), sprintf('%s-08', $y), sprintf('%s-09', $y)];
        else $this->months = [sprintf('%s-10', $y), sprintf('%s-11', $y), sprintf('%s-12', $y)];
    }

    public function query()
    {
        $m1 = $this->months[0];
        $m2 = $this->months[1];
        $m3 = $this->months[2];

        $query = TargetSpvVtkp::query()
            ->select('cabang', 'produk_grup')
            ->selectRaw("SUM(CASE WHEN bulan = ? THEN target ELSE 0 END) as target_m1", [$m1])
            ->selectRaw("SUM(CASE WHEN bulan = ? THEN target ELSE 0 END) as target_m2", [$m2])
            ->selectRaw("SUM(CASE WHEN bulan = ? THEN target ELSE 0 END) as target_m3", [$m3])
            ->selectRaw("SUM(CASE WHEN bulan IN (?, ?, ?) THEN target ELSE 0 END) as total_target", [$m1, $m2, $m3])
            ->whereIn('bulan', $this->months)
            ->groupBy('cabang', 'produk_grup');

        return $query->orderBy('cabang')->orderBy('produk_grup');
    }

    public function headings(): array
    {
        $q = $this->quarterFilter;
        if ($q == 'Q1') $names = ['Target Januari', 'Target Februari', 'Target Maret'];
        elseif ($q == 'Q2') $names = ['Target April', 'Target Mei', 'Target Juni'];
        elseif ($q == 'Q3') $names = ['Target Juli', 'Target Agustus', 'Target September'];
        else $names = ['Target Oktober', 'Target November', 'Target Desember'];

        return [
            'Cabang',
            'Produk Grup',
            $names[0],
            $names[1],
            $names[2],
            'Total Target',
        ];
    }

    public function map($row): array
    {
        return [
            $row->cabang,
            $row->produk_grup,
            $row->target_m1,
            $row->target_m2,
            $row->target_m3,
            $row->total_target,
        ];
    }
}
