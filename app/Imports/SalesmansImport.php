<?php

namespace App\Imports;

use App\Models\Salesman;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use App\Models\MasterDistributor;

class SalesmansImport implements OnEachRow, WithStartRow
{
    private $distributorCodes;
    public $importedCount = 0;
    public $skippedCount = 0;

    public function __construct()
    {
        // Cache distributor codes for validation
        $this->distributorCodes = MasterDistributor::pluck('distributor_code')->flip();
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Mulai impor dari baris kedua
    }

    /**
    * @param Row $row
    */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $salesmanCode = $data[0] ?? null;
        $distributorCode = $data[1] ?? null;
        $salesmanName = $data[2] ?? null;
        $isActive = $data[3] ?? 'AKTIF'; // Default 'AKTIF' if blank

        // Validasi
        if (
            empty($salesmanCode) || 
            empty($distributorCode) ||
            empty($salesmanName) ||
            !isset($this->distributorCodes[$distributorCode])
        ) {
            $this->skippedCount++;
            return; // Lewati baris
        }

        // Konversi status
        $status = (strtoupper($isActive) === 'AKTIF' || $isActive === '1' || $isActive === 1);

        Salesman::updateOrCreate(
            [
                'salesman_code' => $salesmanCode,
            ],
            [
                'distributor_code' => $distributorCode,
                'salesman_name' => $salesmanName,
                'is_active' => $status,
            ]
        );

        $this->importedCount++;
    }
}
