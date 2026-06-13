<?php

namespace App\Exports;

use App\Models\MasterArea;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterAreasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $query = MasterArea::with('region')->where('region_code', '!=', 'HOINA');

        // Apply region access based on user role
        $user = auth()->user();
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('area_code', 'ilike', '%' . $search . '%')
                  ->orWhere('area_name', 'ilike', '%' . $search . '%')
                  ->orWhereHas('region', function ($subQuery) use ($search) {
                      $subQuery->where('region_name', 'ilike', '%' . $search . '%');
                  });
            });
        }

        // Apply region dropdown filter
        if (!empty($this->filters['regionFilter'])) {
            $query->where('region_code', $this->filters['regionFilter']);
        }

        return $query->orderBy('region_code', 'asc');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Region',
            'Nama Region',
            'Kode Area',
            'Nama Area',
            'Dibuat Pada',
        ];
    }

    /**
     * @param mixed $area
     * @return array
     */
    public function map($area): array
    {
        return [
            $area->region->region_code ?? '-',
            $area->region->region_name ?? 'N/A',
            $area->area_code,
            $area->area_name,
            $area->created_at ? $area->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
