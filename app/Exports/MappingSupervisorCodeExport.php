<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;

class MappingSupervisorCodeExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    protected $regionFilter;
    protected $areaFilter;
    protected $levelFilter;
    protected $search;

    public function __construct($regionFilter, $areaFilter, $levelFilter, $search)
    {
        $this->regionFilter = $regionFilter;
        $this->areaFilter = $areaFilter;
        $this->levelFilter = $levelFilter;
        $this->search = $search;
    }

    public function query()
    {
        $query = DB::table('team_elite_code_mappings as tecm')
            ->leftJoin('fsalesman as f', DB::raw('f."SLSNO"'), '=', 'tecm.team_elite_code')
            ->leftJoin('master_supervisors as ms', 'tecm.siso_code', '=', 'ms.supervisor_code')
            ->leftJoin('master_regions as mr', 'tecm.region_code', '=', 'mr.region_code')
            ->leftJoin('master_areas as ma', 'tecm.area_code', '=', 'ma.area_code')
            ->select(
                'mr.region_name',
                'ma.area_name',
                'tecm.team_elite_code as kode_eska',
                DB::raw('f."SLSNAME" as nama_eska'),
                'tecm.siso_code as kode_siso',
                'ms.description as nama_siso',
                'tecm.level'
            );

        if ($this->regionFilter) {
            $query->where('tecm.region_code', $this->regionFilter);
        }
        
        if ($this->areaFilter) {
            $query->where('tecm.area_code', $this->areaFilter);
        }

        if ($this->levelFilter) {
            $query->where('tecm.level', $this->levelFilter);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('mr.region_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ma.area_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('tecm.team_elite_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere(DB::raw('f."SLSNAME"'), 'ilike', '%' . $this->search . '%')
                  ->orWhere('tecm.siso_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ms.description', 'ilike', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('mr.region_name')->orderBy('ma.area_name');
    }

    public function headings(): array
    {
        return [
            'Region',
            'Area',
            'Kode Eska (Team Elite)',
            'Nama Eska',
            'Kode Siso (Supervisor)',
            'Nama Siso',
            'Level'
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_name ?? '-',
            $row->area_name ?? '-',
            (string) ($row->kode_eska ?? '-'),
            $row->nama_eska ?? '-',
            (string) ($row->kode_siso ?? '-'),
            $row->nama_siso ?? '-',
            $row->level ? ucfirst($row->level) : '-'
        ];
    }
}
