<?php

namespace App\Exports;

use App\Models\MasterRegion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MasterRegionsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($filters)
    {
        $this->search = $filters['search'] ?? '';
    }

    public function query()
    {
        $query = MasterRegion::query()
            ->where('region_code', '!=', 'HOINA')
            ->orderBy('region_code', 'asc');

        // Batasi berdasarkan hak akses
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('region_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('region_name', 'ilike', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Kode Region',
            'Nama Region',
            'Dibuat Pada',
        ];
    }

    public function map($region): array
    {
        return [
            $region->region_code,
            $region->region_name,
            $region->created_at ? $region->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
