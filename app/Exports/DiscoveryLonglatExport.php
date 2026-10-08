<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class DiscoveryLonglatExport extends StringValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $selectedRegion;
    protected $selectedArea;

    public function __construct($startDate, $endDate, $selectedRegion = '', $selectedArea = '')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->selectedRegion = $selectedRegion;
        $this->selectedArea = $selectedArea;
    }

    public function query()
    {
        $safeStart = \Carbon\Carbon::parse($this->startDate)->format('Y-m-d');
        $safeEnd = \Carbon\Carbon::parse($this->endDate)->format('Y-m-d');

        $query = DB::table('customer_prc_eska as cpe')
            ->join(DB::raw("(
                SELECT 
                    \"BID\",
                    \"CUSTNO\",
                    \"V_LA\",
                    \"V_LG\",
                    \"TANGGAL\",
                    ROW_NUMBER() OVER (PARTITION BY \"BID\", \"CUSTNO\" ORDER BY \"TANGGAL\" DESC) as rn
                FROM rpt_visit_an_h
                WHERE \"FLAG_PJP\" = 'R' 
                  AND \"FLAG_VISIT\" = 'Y'
                  AND \"TANGGAL\"::date BETWEEN '{$safeStart}' AND '{$safeEnd}'
                  AND \"V_LA\" IS NOT NULL AND \"V_LA\" <> 0 AND \"V_LA\" BETWEEN -90 AND 90
                  AND \"V_LG\" IS NOT NULL AND \"V_LG\" <> 0 AND \"V_LG\" BETWEEN -180 AND 180
            ) as lv"), function($join) {
                $join->on('cpe.kodecabang', '=', 'lv.BID')
                     ->on('cpe.custno', '=', 'lv.CUSTNO');
            })
            ->where(function($q) {
                $q->where('cpe.la', '0')
                  ->orWhereNull('cpe.la')
                  ->orWhere('cpe.la', '');
            })
            ->where(function($q) {
                $q->where('cpe.lg', '0')
                  ->orWhereNull('cpe.lg')
                  ->orWhere('cpe.lg', '');
            })
            ->where('lv.rn', 1)
            ->select(
                'cpe.region_code',
                'cpe.area_code',
                'cpe.kodecabang',
                'cpe.custno',
                'cpe.custname',
                'cpe.custadd1',
                'cpe.ccity',
                'cpe.cterm',
                'cpe.typeout',
                'cpe.grupout',
                'cpe.gharga',
                'lv.V_LA',
                'lv.V_LG',
                'lv.TANGGAL'
            )
            ->orderBy('cpe.region_code')
            ->orderBy('cpe.area_code');

        if ($this->selectedRegion) {
            $query->where('cpe.region_code', $this->selectedRegion);
        }
        if ($this->selectedArea) {
            $query->where('cpe.area_code', $this->selectedArea);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Region Code',
            'Area Code',
            'Kode Cabang',
            'Cust No',
            'Cust Name',
            'Cust Address',
            'City',
            'Term',
            'Type Out',
            'Grup Out',
            'Harga',
            'New Latitude',
            'New Longitude',
            'Visit Date'
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_code,
            $row->area_code,
            $row->kodecabang,
            $row->custno,
            $row->custname,
            $row->custadd1,
            $row->ccity,
            $row->cterm,
            $row->typeout,
            $row->grupout,
            $row->gharga,
            str_replace(',', '.', (string) $row->V_LA),
            str_replace(',', '.', (string) $row->V_LG),
            $row->TANGGAL,
        ];
    }
}
