<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;

class MappingDistributorImplementasiEskalinkExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $search;
    protected $region;
    protected $area;
    protected $isActive;
    protected $isImplementasi;
    protected $allowed_regions;

    public function __construct($search, $region, $area, $isActive, $isImplementasi, $allowed_regions = [])
    {
        $this->search = $search;
        $this->region = $region;
        $this->area = $area;
        $this->isActive = $isActive;
        $this->isImplementasi = $isImplementasi;
        $this->allowed_regions = $allowed_regions;
    }

    public function query()
    {
        $query = \App\Models\DistributorImplementasiEskalink::query()
            ->select([
                'master_distributors.region_name',
                'master_distributors.area_name',
                'distributor_implementasi_eskalink.distributor_code',
                'distributor_implementasi_eskalink.distributor_name',
                'master_distributors.branch_name',
                'distributor_implementasi_eskalink.eskalink_code',
                'distributor_implementasi_eskalink.eskalink_code_dist',
                'distributor_implementasi_eskalink.implementasi',
                'master_distributors.is_active'
            ])
            ->leftJoin('master_distributors', 'distributor_implementasi_eskalink.distributor_code', '=', 'master_distributors.distributor_code')
            ->where('distributor_implementasi_eskalink.distributor_code', '!=', 'HOINA');

        if (!empty($this->allowed_regions)) {
            $query->whereIn('master_distributors.region_code', $this->allowed_regions);
        }

        if ($this->search !== '') {
            $query->where(function($q) {
                $q->where('distributor_implementasi_eskalink.distributor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_implementasi_eskalink.distributor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_implementasi_eskalink.eskalink_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_implementasi_eskalink.eskalink_code_dist', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->region !== '') {
            $query->where('master_distributors.region_name', $this->region);
        }

        if ($this->area !== '') {
            $query->where('master_distributors.area_name', $this->area);
        }

        if ($this->isActive !== '') {
            if ($this->isActive === '1') {
                $query->where('master_distributors.is_active', true);
            } elseif ($this->isActive === '0') {
                $query->where(function($q) {
                    $q->where('master_distributors.is_active', false)
                      ->orWhereNull('master_distributors.is_active');
                });
            }
        }

        if ($this->isImplementasi !== '') {
            if ($this->isImplementasi === '1') {
                $query->where('distributor_implementasi_eskalink.implementasi', 'Y');
            } elseif ($this->isImplementasi === '0') {
                $query->where(function($q) {
                    $q->where('distributor_implementasi_eskalink.implementasi', '!=', 'Y')
                      ->orWhereNull('distributor_implementasi_eskalink.implementasi');
                });
            }
        }

        return $query->orderBy('master_distributors.region_name', 'asc')
                     ->orderBy('master_distributors.area_name', 'asc')
                     ->orderBy('master_distributors.branch_name', 'asc')
                     ->orderBy('distributor_implementasi_eskalink.distributor_name', 'asc');
    }

    public function headings(): array
    {
        return [
            'Region Name',
            'Area Name',
            'Dist Code',
            'Dist Name',
            'Branch Name',
            'Eskalink Code',
            'Eskalink Code Dist',
            'Is Implementasi',
            'Dist Active'
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_name,
            $row->area_name,
            $row->distributor_code,
            $row->distributor_name,
            $row->branch_name,
            $row->eskalink_code,
            $row->eskalink_code_dist,
            $row->implementasi == 'Y' ? 'Yes' : 'No',
            $row->is_active ? 'Yes' : 'No',
        ];
    }
}
