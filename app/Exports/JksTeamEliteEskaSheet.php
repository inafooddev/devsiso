<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JksTeamEliteEskaSheet implements FromCollection, WithTitle, WithStyles, ShouldAutoSize
{
    protected $title;
    protected $slsno;
    protected $startDate;
    protected $endDate;
    protected $flagDelete;

    public function __construct($title, $slsno, $startDate, $endDate, $flagDelete = 'Y')
    {
        $this->title = $title;
        $this->slsno = $slsno;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->flagDelete = $flagDelete;
    }

    /**
     * Set sheet title (e.g. RUTE 1, RUTE 2, etc.)
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Prepare data collection for the sheet
     */
    public function collection()
    {
        $rows = [];
        $rows[] = ['REGION:', 'HOINA'];
        $rows[] = ['ENTITY:', 'HOINA'];
        $rows[] = ['BRANCH:', 'HOINA'];
        $rows[] = ['SLSNO:', $this->slsno];
        $rows[] = ['FLAG DELETE:', $this->flagDelete];
        $rows[] = ['NORUTE', 'CUSTNO', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'H7', 'M1', 'M2', 'M3', 'M4'];

        $dbDataQuery = DB::table('jks_team_elite as j')
            ->leftJoin('master_calender as mc', 'j.tanggal', '=', 'mc.date')
            ->select([
                DB::raw('ROW_NUMBER() OVER (PARTITION BY j.custno ORDER BY j.custno) AS nourut'),
                'j.custno',
                DB::raw("case when mc.day_number = 1 then 'Y' else 'T' end  as h1"),
                DB::raw("case when mc.day_number = 2 then 'Y' else 'T' end  as h2"),
                DB::raw("case when mc.day_number = 3 then 'Y' else 'T' end  as h3"),
                DB::raw("case when mc.day_number = 4 then 'Y' else 'T' end  as h4"),
                DB::raw("case when mc.day_number = 5 then 'Y' else 'T' end  as h5"),
                DB::raw("case when mc.day_number = 6 then 'Y' else 'T' end  as h6"),
                DB::raw("case when mc.day_number = 7 then 'Y' else 'T' end  as h7"),
                DB::raw("case when mc.week_month = 1 then 'Y' else 'T' end  as w1"),
                DB::raw("case when mc.week_month = 2 then 'Y' else 'T' end  as w2"),
                DB::raw("case when mc.week_month = 3 then 'Y' else 'T' end  as w3"),
                DB::raw("case when mc.week_month = 4 then 'Y' else 'T' end  as w4"),
            ])
            ->where('j.kode_team', $this->slsno)
            ->whereBetween('j.tanggal', [$this->startDate, $this->endDate]);

        // Apply hierarchy access
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $dbDataQuery->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md')
                        ->whereColumn('md.distributor_code', 'j.distributor_code')
                        ->where('md.supervisor_code', $user->supervisor_code);
                });
            }
            if (!empty($user->area_code) && count((array) $user->area_code) > 0) {
                $dbDataQuery->whereExists(function ($sub) use ($user) {
                    $sub->selectRaw('1')
                        ->from('master_distributors as md')
                        ->whereColumn('md.distributor_code', 'j.distributor_code')
                        ->whereIn('md.area_code', (array) $user->area_code);
                });
            }
            if (!empty($user->region_code) && count((array) $user->region_code) > 0) {
                $dbDataQuery->whereIn('j.kode_region', (array) $user->region_code);
            }
        }

        $dbData = $dbDataQuery->orderBy('j.custno')
            ->get();

        foreach ($dbData as $row) {
            $rows[] = [
                $row->nourut,
                $row->custno,
                $row->h1,
                $row->h2,
                $row->h3,
                $row->h4,
                $row->h5,
                $row->h6,
                $row->h7,
                $row->w1,
                $row->w2,
                $row->w3,
                $row->w4,
            ];
        }

        return collect($rows);
    }

    /**
     * Styles for the spreadsheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Bold label columns
            'A1:A5' => [
                'font' => [
                    'bold' => true,
                ]
            ],
            // Style table header row (Row 6)
            6 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F4E78'], // Navy Blue
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }
}
