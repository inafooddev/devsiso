<?php

namespace App\Imports;

use App\Models\ListTokoParetoTeamElite;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Exception;

class ListTokoParetoImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Validasi Wajib
        if (empty($row['customer_code_prc']) || empty($row['distributor_code'])) {
            return null; // Lewati baris jika key kosong
        }

        $customerCodePrc = trim($row['customer_code_prc']);
        $distributorCode = trim($row['distributor_code']);

        // Data Cleaning & Casting untuk Lat/Lng
        $lat = null;
        if (isset($row['latitude']) && trim($row['latitude']) !== '') {
            $latStr = str_replace(',', '.', trim($row['latitude']));
            if (is_numeric($latStr)) {
                $latFloat = (float) $latStr;
                // Validasi Range Latitude
                if ($latFloat >= -90 && $latFloat <= 90) {
                    $lat = $latFloat;
                }
            }
        }

        $lng = null;
        if (isset($row['longitude']) && trim($row['longitude']) !== '') {
            $lngStr = str_replace(',', '.', trim($row['longitude']));
            if (is_numeric($lngStr)) {
                $lngFloat = (float) $lngStr;
                // Validasi Range Longitude
                if ($lngFloat >= -180 && $lngFloat <= 180) {
                    $lng = $lngFloat;
                }
            }
        }

        // FULL SYNC LOGIC (UPSERT)
        return ListTokoParetoTeamElite::updateOrCreate(
            [
                'customer_code_prc' => $customerCodePrc,
                'distributor_code'  => $distributorCode,
            ],
            [
                'customer_name'    => isset($row['customer_name']) ? trim($row['customer_name']) : null,
                'customer_address' => isset($row['customer_address']) ? trim($row['customer_address']) : null,
                'kecamatan'        => isset($row['kecamatan']) ? trim($row['kecamatan']) : null,
                'desa'             => isset($row['desa']) ? trim($row['desa']) : null,
                'latitude'         => $lat,
                'longitude'        => $lng,
                'pilar'            => isset($row['pilar']) ? trim($row['pilar']) : null,
                'target'           => isset($row['target']) ? (float) str_replace(',', '.', trim($row['target'])) : 0,
            ]
        );
    }
}