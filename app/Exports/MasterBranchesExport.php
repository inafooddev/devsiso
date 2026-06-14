<?php

namespace App\Exports;

use App\Models\MasterBranch;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterBranchesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = MasterBranch::with(['supervisor.area.region']);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('supervisor.area', function($q) use ($user) {
                $q->whereIn('region_code', $user->region_code);
            });
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('branch_code', 'ilike', '%' . $search . '%')
                  ->orWhere('branch_name', 'ilike', '%' . $search . '%')
                  ->orWhereHas('supervisor', function ($subQuery) use ($search) {
                      $subQuery->where('supervisor_name', 'ilike', '%' . $search . '%')
                               ->orWhereHas('area', function ($q2) use ($search) {
                                   $q2->where('area_name', 'ilike', '%' . $search . '%')
                                     ->orWhereHas('region', function ($q3) use ($search) {
                                         $q3->where('region_name', 'ilike', '%' . $search . '%');
                                     });
                               });
                  });
            });
        }

        if (!empty($this->filters['regionFilter'])) {
            $query->whereHas('supervisor.area', function ($q) {
                $q->where('region_code', $this->filters['regionFilter']);
            });
        }

        return $query->orderBy('branch_code', 'asc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Region',
            'Nama Region',
            'Kode Area',
            'Nama Area',
            'Kode Supervisor',
            'Nama Supervisor',
            'Kode Cabang',
            'Nama Cabang',
        ];
    }

    public function map($branch): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $branch->supervisor->area->region_code ?? '-',
            $branch->supervisor->area->region->region_name ?? 'N/A',
            $branch->supervisor->area_code ?? '-',
            $branch->supervisor->area->area_name ?? 'N/A',
            $branch->supervisor_code ?? '-',
            $branch->supervisor->supervisor_name ?? 'N/A',
            $branch->branch_code,
            $branch->branch_name,
        ];
    }
}
