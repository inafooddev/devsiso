<?php

namespace App\Imports;

use App\Models\SellingInDistributorMapping;
use App\Models\MasterDistributor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class SellingInDistributorMappingImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // Baris excel adalah 1-indexed, ditambah 1 karena header
                $excelRow = $index + 2; 

                $divisi = trim($row['divisi'] ?? '');
                $wilayah = trim($row['wilayah'] ?? '');
                $kodeDistributor = trim($row['kode_distributor'] ?? '');
                $distributor = trim($row['distributor'] ?? '');
                $distributorCode = trim($row['distributor_code_master'] ?? ''); // Header dari Excel

                // Lewati baris kosong
                if (empty($divisi) && empty($wilayah) && empty($kodeDistributor) && empty($distributor)) {
                    continue;
                }

                // Validasi external ke tabel master_distributors jika distributor_code diisi
                if (!empty($distributorCode)) {
                    $exists = MasterDistributor::where('distributor_code', $distributorCode)->exists();
                    if (!$exists) {
                        throw new \Exception("Baris {$excelRow}: Distributor Code (Master) '{$distributorCode}' tidak ditemukan di tabel Master Distributor.");
                    }
                } else {
                    $distributorCode = null; // Pastikan null jika kosong
                }

                // Upsert Logic (Update or Create) berdasarkan kombinasi 4 kolom
                SellingInDistributorMapping::updateOrCreate(
                    [
                        'divisi' => $divisi,
                        'wilayah' => $wilayah,
                        'kode_distributor' => $kodeDistributor,
                        'distributor' => $distributor,
                    ],
                    [
                        'distributor_code' => $distributorCode
                    ]
                );
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
