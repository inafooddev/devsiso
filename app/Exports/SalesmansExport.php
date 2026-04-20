<?php

namespace App\Exports;

use App\Models\Salesman;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesmansExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
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
        $query = Salesman::query()
            ->with('masterDistributor.area.region')
            ->join('master_distributors', 'salesmans.distributor_code', '=', 'master_distributors.distributor_code')
            ->join('master_areas', 'master_distributors.area_code', '=', 'master_areas.area_code')
            ->join('master_regions', 'master_distributors.region_code', '=', 'master_regions.region_code');

        // Terapkan filter
        if (!empty($this->filters['regionFilter'])) {
            $query->where('master_distributors.region_code', $this->filters['regionFilter']);
        }
        if (!empty($this->filters['areaFilter'])) {
            $query->where('master_distributors.area_code', $this->filters['areaFilter']);
        }
        if (!empty($this->filters['distributorFilter'])) {
            $query->where('salesmans.distributor_code', $this->filters['distributorFilter']);
        }
        if (!empty($this->filters['search'])) {
            // Gunakan ILIKE untuk PostgreSQL
            $query->where(function ($q) {
                $q->where('salesmans.salesman_code', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('salesmans.salesman_name', 'ILIKE', '%' . $this->filters['search'] . '%')
                    ->orWhere('master_distributors.distributor_name', 'ILIKE', '%' . $this->filters['search'] . '%');
            });
        }

        return $query->select('salesmans.*', 'master_distributors.distributor_name', 'master_areas.area_name', 'master_regions.region_name')
            ->latest('salesmans.created_at');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Salesman Code',
            'Salesman Name',
            'Distributor Code',
            'Distributor Name',
            'Area Name',
            'Region Name',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    /**
     * @param Salesman $salesman
     * @return array
     */
    public function map($salesman): array
    {
        return [
            $salesman->salesman_code,
            $salesman->salesman_name,
            $salesman->distributor_code,
            $salesman->distributor_name, // Dari join
            $salesman->area_name, // Dari join
            $salesman->region_name, // Dari join
            $salesman->is_active ? 'Aktif' : 'Tidak Aktif',
            $salesman->created_at->format('d M Y H:i'),
        ];
    }
}
