<?php

namespace App\Imports;

use App\Models\SalesmanMapping;
use App\Models\MasterDistributor;
use App\Models\Salesman;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class SalesmanMappingsImport implements OnEachRow, WithStartRow
{
    private $distributorCodes;
    private $principalSalesmanCodes;
    public $importedCount = 0;
    public $skippedCount = 0;

    public function __construct()
    {
        $this->distributorCodes = MasterDistributor::pluck('distributor_code')->flip();
        $this->principalSalesmanCodes = Salesman::pluck('salesman_code')->flip();
    }

    public function startRow(): int
    {
        return 2; // Mulai dari baris kedua
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $distributorCode = $data[0] ?? null;
        $salesmanCodeDist = $data[2] ?? null;
        $salesmanNameDist = $data[3] ?? null;
        $salesmanCodePrc = $data[4] ?? null;

        // Validasi
        if (
            empty($distributorCode) || 
            empty($salesmanCodeDist) ||
            !isset($this->distributorCodes[$distributorCode]) ||
            ($salesmanCodePrc && !isset($this->principalSalesmanCodes[$salesmanCodePrc]))
        ) {
            $this->skippedCount++;
            return; // Lewati baris
        }

        SalesmanMapping::updateOrCreate(
            [
                'distributor_code' => $distributorCode,
                'salesman_code_dist' => $salesmanCodeDist,
            ],
            [
                'salesman_name_dist' => $salesmanNameDist,
                'salesman_code_prc' => $salesmanCodePrc,
            ]
        );

        $this->importedCount++;
    }
}
