<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class JksEskalinkExport implements WithMultipleSheets
{
    use Exportable;

    protected $bulan;
    protected $distributorCode;
    protected $flagDelete;
    protected $salesmanCode;

    public function __construct($bulan, $distributorCode, $flagDelete, $salesmanCode = '')
    {
        $this->bulan = $bulan;
        $this->distributorCode = $distributorCode;
        $this->flagDelete = $flagDelete;
        $this->salesmanCode = $salesmanCode;
    }

    public function sheets(): array
    {
        // 1. Ambil semua data
        $query = DB::table('jks_salesmans as js')
            ->leftJoin('master_distributors as md', 'md.distributor_code', '=', 'js.distributor_code')
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.distributor_code', '=', 'js.distributor_code')
            ->leftJoin('list_toko_pareto_team_elite as lt', function($join) {
                $join->on('lt.uniq_kd', '=', 'js.customer_code')
                     ->on('lt.distributor_code', '=', 'js.distributor_code');
            })
            ->select(
                'md.region_code as region',
                'md.area_code as entity',
                'die.eskalink_code as branch',
                'js.salesman_code as slsno',
                'lt.customer_code_prc as custno',
                'js.customer_code as original_custno',
                'js.h1', 'js.h2', 'js.h3', 'js.h4', 'js.h5', 'js.h6', 'js.h7',
                'js.w1 as m1', 'js.w2 as m2', 'js.w3 as m3', 'js.w4 as m4'
            )
            ->where('js.bulan', 'like', $this->bulan . '%');

        if (!empty($this->distributorCode)) {
            $query->where('js.distributor_code', $this->distributorCode);
        }

        if (!empty($this->salesmanCode)) {
            $query->where('js.salesman_code', $this->salesmanCode);
        }

        $data = $query->orderBy('js.distributor_code')
                      ->orderBy('js.salesman_code')
                      ->orderBy('js.customer_code')
                      ->get();

        // 2. Group per Salesman
        $groupedData = $data->groupBy('slsno');
        $sheets = [];
        $sheetIndex = 1;

        foreach ($groupedData as $slsno => $schedules) {
            $sheetName = 'RUTE ' . $sheetIndex;
            $sheets[] = new JksEskalinkSheetExport($schedules, $sheetName, $this->flagDelete);
            $sheetIndex++;
        }

        // Jika tidak ada data, buat 1 sheet kosong agar tidak error
        if (empty($sheets)) {
            $sheets[] = new JksEskalinkSheetExport(collect([]), 'RUTE 1', $this->flagDelete);
        }

        return $sheets;
    }
}
