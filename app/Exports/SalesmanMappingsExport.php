<?php

namespace App\Exports;

use App\Models\SalesmanMapping;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesmanMappingsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = SalesmanMapping::query()
            ->with(['masterDistributor.area.region', 'principalSalesman'])
            ->join('master_distributors', 'salesman_mappings.distributor_code', '=', 'master_distributors.distributor_code')
            ->join('master_areas', 'master_distributors.area_code', '=', 'master_areas.area_code')
            ->join('master_regions', 'master_distributors.region_code', '=', 'master_regions.region_code');

        if (!empty($this->filters['regionFilter'])) {
            $query->where('master_distributors.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('master_distributors.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('salesman_mappings.distributor_code', $this->filters['distributorFilter']);
        }
        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('salesman_mappings.salesman_code_dist', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('salesman_mappings.salesman_name_dist', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('salesman_mappings.salesman_code_prc', 'ILIKE', '%' . $this->filters['search'] . '%');
            });
        }

        return $query->select('salesman_mappings.*', 'master_distributors.distributor_name', 'master_areas.area_name', 'master_regions.region_name')
            ->latest('salesman_mappings.created_at');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Distributor',
            'Nama Distributor',
            'Kode Salesman Dist.',
            'Nama Salesman Dist.',
            'Kode Salesman Prc.',
            'Nama Salesman Prc.',
        ];
    }

    /**
     * @param SalesmanMapping $mapping
     * @return array
     */
    public function map($mapping): array
    {
        return [
            $mapping->distributor_code,
            $mapping->distributor_name,
            $mapping->salesman_code_dist,
            $mapping->salesman_name_dist,
            $mapping->salesman_code_prc,
            $mapping->principalSalesman->salesman_name ?? 'N/A',
        ];
    }
}
