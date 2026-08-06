<?php

namespace App\Exports;

use App\Models\TargetPerSeUntukEska;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TargetPerSeUntukEskaExport implements WithMultipleSheets
{
    protected $tahunFilter;
    protected $bulanFilter;
    protected $regionFilter;
    protected $branchFilter;
    protected $sellingPointFilter;

    public function __construct($tahunFilter = '', $bulanFilter = '', $regionFilter = '', $branchFilter = [], $sellingPointFilter = [])
    {
        $this->tahunFilter = $tahunFilter;
        $this->bulanFilter = $bulanFilter;
        $this->regionFilter = $regionFilter;
        $this->branchFilter = is_array($branchFilter) ? array_filter($branchFilter) : [];
        $this->sellingPointFilter = is_array($sellingPointFilter) ? array_filter($sellingPointFilter) : [];
    }

    /**
     * Menghasilkan array sheet per Salesman (SE).
     */
    public function sheets(): array
    {
        $sheets = [];

        $query = TargetPerSeUntukEska::query();

        if (!empty($this->tahunFilter)) {
            $query->where('tahun', $this->tahunFilter);
        }

        if (!empty($this->bulanFilter)) {
            $query->where('bulan', $this->bulanFilter);
        }

        if (!empty($this->regionFilter)) {
            $query->where('region', $this->regionFilter);
        }

        if (!empty($this->branchFilter)) {
            $query->whereIn('branch', $this->branchFilter);
        }

        if (!empty($this->sellingPointFilter)) {
            $query->whereIn('sellingpoint', $this->sellingPointFilter);
        }

        $allData = $query->orderBy('salesman')->orderBy('outlet')->get();

        if ($allData->isEmpty()) {
            $sheets[] = new TargetPerSeUntukEskaSalesmanSheet('SALESMAN', [
                'region'       => $this->regionFilter ?: '-',
                'branch'       => !empty($this->branchFilter) ? implode(', ', $this->branchFilter) : '-',
                'sellingpoint' => !empty($this->sellingPointFilter) ? implode(', ', $this->sellingPointFilter) : '-',
                'tahun'        => $this->tahunFilter ?: '-',
                'bulan'        => $this->bulanFilter ?: '-',
            ], collect());
            return $sheets;
        }

        // Kelompokkan data per Salesman
        $grouped = $allData->groupBy('salesman');

        foreach ($grouped as $salesman => $rows) {
            $first = $rows->first();
            $meta = [
                'region'       => $first->region ?? '-',
                'branch'       => $first->branch ?? '-',
                'sellingpoint' => $first->sellingpoint ?? '-',
                'tahun'        => $first->tahun ?? '-',
                'bulan'        => $first->bulan ?? '-',
            ];

            $sheets[] = new TargetPerSeUntukEskaSalesmanSheet((string) ($salesman ?: 'NO_SALESMAN'), $meta, $rows);
        }

        return $sheets;
    }
}
