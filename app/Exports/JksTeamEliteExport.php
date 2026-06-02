<?php

namespace App\Exports;

use App\Models\JksTeamElite;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class JksTeamEliteExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filterTeam;
    protected $filterStartDate;
    protected $filterEndDate;

    public function __construct($filterTeam, $filterStartDate, $filterEndDate)
    {
        $this->filterTeam = $filterTeam;
        $this->filterStartDate = $filterStartDate;
        $this->filterEndDate = $filterEndDate;
    }
    public function collection()
    {
        $query = JksTeamElite::query()
            ->select(
                'jks_team_elite.tanggal',
                'jks_team_elite.kode_team',
                'jks_team_elite.nama_team',
                'jks_team_elite.kode_region',
                'jks_team_elite.nama_region',
                'jks_team_elite.kode_area',
                'jks_team_elite.nama_area',
                'jks_team_elite.distributor_code',
                'jks_team_elite.distributor_name',
                'jks_team_elite.custno',
                'jks_team_elite.custname',
                'jks_team_elite.addres',
                'l.pilar',
                'l.target'
            )
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            });

        if (!empty($this->filterTeam) && is_array($this->filterTeam)) {
            $query->whereIn('jks_team_elite.kode_team', $this->filterTeam);
        }

        if (!empty($this->filterStartDate) && !empty($this->filterEndDate)) {
            $query->whereBetween('jks_team_elite.tanggal', [$this->filterStartDate, $this->filterEndDate]);
        }

        // Apply hierarchy access
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $query->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md')
                        ->whereColumn('md.distributor_code', 'jks_team_elite.distributor_code')
                        ->where('md.supervisor_code', $user->supervisor_code);
                });
            }
            if (!empty($user->area_code) && count((array) $user->area_code) > 0) {
                $query->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md')
                        ->whereColumn('md.distributor_code', 'jks_team_elite.distributor_code')
                        ->whereIn('md.area_code', (array) $user->area_code);
                });
            }
            if (!empty($user->region_code) && count((array) $user->region_code) > 0) {
                $query->whereIn('jks_team_elite.kode_region', (array) $user->region_code);
            }
        }

        return $query->get();
    }

    public function map($row): array
    {
        return [
            $row->tanggal ? Carbon::parse($row->tanggal)->format('Y-m-d') : '',
            $row->kode_team,
            $row->nama_team,
            $row->kode_region,
            $row->nama_region,
            $row->kode_area,
            $row->nama_area,
            $row->distributor_code,
            $row->distributor_name,
            $row->custno,
            $row->custname,
            $row->addres,
            $row->pilar,
            $row->target,
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Team',
            'Nama Team',
            'Kode Region',
            'Nama Region',
            'Kode Area',
            'Nama Area',
            'Distributor Code',
            'Distributor Name',
            'CustNo',
            'CustName',
            'Address',
            'Pilar',
            'Target'
        ];
    }
}
