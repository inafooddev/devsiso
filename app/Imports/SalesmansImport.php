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

    private $allowedRegions;

    public function __construct(array $allowedRegions = [])
    {
        $this->allowedRegions = $allowedRegions;
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
        $type = $data[4] ?? 'Distributor';
        $joinDate = $data[5] ?? null;
        $bank = $data[6] ?? null;
        $bankName = $data[7] ?? null;
        $bankNo = $data[8] ?? null;

        $distributor = MasterDistributor::find($distributorCode);
        if (!$distributor) {
            $this->skippedCount++;
            return;
        }

        if (!empty($this->allowedRegions) && !in_array($distributor->region_code, $this->allowedRegions)) {
            $this->skippedCount++;
            return;
        }

        // Konversi tipe & status
        $isPrinciple = (strtolower(trim($type)) === 'principal' || $type === '1' || $type === 1);
        $status = (strtoupper($isActive) === 'AKTIF' || $isActive === '1' || $isActive === 1);

        // Jika join_date kosong atau bukan format yang valid, biarkan null. 
        // Excel terkadang mengirimkan integer (hari sejak 1900), jadi lebih aman dibiarkan jika parsing gagal.
        if (is_numeric($joinDate)) {
            $joinDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($joinDate)->format('Y-m-d');
        }

        Salesman::updateOrCreate(
            [
                'salesman_code' => $salesmanCode,
                'distributor_code' => $distributorCode,
            ],
            [
                'salesman_name' => $salesmanName,
                'is_active' => $status,
                'is_principle' => $isPrinciple,
                'join_date' => $joinDate ?: null,
                'bank' => $bank,
                'bank_name' => $bankName,
                'bank_no' => $bankNo,
            ]
        );

        $this->importedCount++;
    }
}
