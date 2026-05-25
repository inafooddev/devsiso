<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class JksTeamEliteEskaExport implements WithMultipleSheets
{
    protected $filterTeam;
    protected $filterStartDate;
    protected $filterEndDate;
    protected $flagDelete;

    public function __construct($filterTeam, $filterStartDate, $filterEndDate, $flagDelete = 'Y')
    {
        $this->filterTeam = $filterTeam;
        $this->filterStartDate = $filterStartDate;
        $this->filterEndDate = $filterEndDate;
        $this->flagDelete = $flagDelete;
    }

    /**
     * Generate sheets for each salesman/team
     */
    public function sheets(): array
    {
        $query = DB::table('jks_team_elite')
            ->select('kode_team')
            ->distinct();

        if (!empty($this->filterTeam)) {
            $query->whereIn('kode_team', $this->filterTeam);
        }

        if (!empty($this->filterStartDate) && !empty($this->filterEndDate)) {
            $query->whereBetween('tanggal', [$this->filterStartDate, $this->filterEndDate]);
        }

        $teams = $query->orderBy('kode_team')->pluck('kode_team')->toArray();

        $sheets = [];
        $index = 1;

        if (empty($teams)) {
            $firstTeam = !empty($this->filterTeam) ? $this->filterTeam[0] : 'HOINA';
            $sheets[] = new JksTeamEliteEskaSheet("RUTE 1", $firstTeam, $this->filterStartDate, $this->filterEndDate, $this->flagDelete);
        } else {
            foreach ($teams as $team) {
                $sheets[] = new JksTeamEliteEskaSheet("RUTE " . $index++, $team, $this->filterStartDate, $this->filterEndDate, $this->flagDelete);
            }
        }

        return $sheets;
    }
}
