<?php

namespace App\Exports;

use App\Models\MasterSupervisor;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterSupervisorsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = MasterSupervisor::with('area.region');

        // Apply region access based on user role
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('area', function($q) use ($user) {
                $q->whereIn('region_code', $user->region_code);
            });
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('supervisor_code', 'ilike', '%' . $search . '%')
                  ->orWhere('supervisor_name', 'ilike', '%' . $search . '%')
                  ->orWhereHas('area', function ($subQuery) use ($search) {
                      $subQuery->where('area_name', 'ilike', '%' . $search . '%')
                               ->orWhereHas('region', function ($q2) use ($search) {
                                   $q2->where('region_name', 'ilike', '%' . $search . '%');
                               });
                  });
            });
        }

        return $query->orderBy('area_code', 'asc')->orderBy('supervisor_code', 'asc');
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
            'Keterangan',
        ];
    }

    public function map($supervisor): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $supervisor->area->region_code ?? '-',
            $supervisor->area->region->region_name ?? 'N/A',
            $supervisor->area_code ?? '-',
            $supervisor->area->area_name ?? 'N/A',
            $supervisor->supervisor_code,
            $supervisor->supervisor_name,
            $supervisor->description ?: '-',
        ];
    }
}
