<?php

namespace App\Imports;

use App\Models\NominalQcDist;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class NominalQcDistImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    protected $tanggal;
    protected $fileSuratPath;

    public function __construct($tanggal, $fileSuratPath)
    {
        $this->tanggal = $tanggal;
        $this->fileSuratPath = $fileSuratPath;
    }

    private function parseNumber($val, $isInt = false)
    {
        if (is_null($val) || $val === '') return 0;
        if (is_int($val) || is_float($val)) return $isInt ? (int) $val : (float) $val;
        
        $val = trim((string) $val);
        // Strip out currencies, spaces, NBSP, etc. Keep only digits, minus, dot, comma.
        $val = preg_replace('/[^-0-9.,]/', '', $val);

        $lastDot = strrpos($val, '.');
        $lastComma = strrpos($val, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                // English format: 1,234.56
                $val = str_replace(',', '', $val);
            } else {
                // Indo format: 1.234,56
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            }
        } elseif ($lastComma !== false) {
            $parts = explode(',', $val);
            if (count($parts) > 2) {
                // Multiple commas. If last part is not 3 digits, it's likely a decimal (typo)
                if (strlen(end($parts)) !== 3) {
                    $decimalPart = array_pop($parts);
                    $val = implode('', $parts) . '.' . $decimalPart;
                } else {
                    $val = str_replace(',', '', $val);
                }
            } else {
                // 1 comma, in Indo context usually means decimal
                $val = str_replace(',', '.', $val);
            }
        } elseif ($lastDot !== false) {
            $parts = explode('.', $val);
            if (count($parts) > 2) {
                // Multiple dots. If last part is not 3 digits, it's likely a decimal (typo e.g. 275.572.096.31)
                if (strlen(end($parts)) !== 3) {
                    $decimalPart = array_pop($parts);
                    $val = implode('', $parts) . '.' . $decimalPart;
                } else {
                    $val = str_replace('.', '', $val);
                }
            } else {
                // 1 dot. If exactly 3 digits AND part before dot is <= 3 digits, assume thousands separator (Indo)
                // e.g. 1.234 -> thousands
                // e.g. 17492094.416 -> decimal (part before is 8 digits)
                if (strlen(end($parts)) === 3 && strlen($parts[0]) <= 3) {
                    $val = str_replace('.', '', $val);
                }
                // Else leave it (standard decimal)
            }
        }

        return $isInt ? (int) (float) $val : (float) $val;
    }

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        if (!isset($data['kode_dist']) || empty(trim($data['kode_dist']))) {
            return;
        }

        $updateData = [
            'qty'              => $this->parseNumber($data['qty'] ?? 0, true),
            'discount_4'       => $this->parseNumber($data['disc_4'] ?? 0),
            'discount_8'       => $this->parseNumber($data['disc_8'] ?? 0),
            'neto'             => $this->parseNumber($data['nett'] ?? 0),
            'nominal_surat'    => $this->parseNumber($data['nominal_surat'] ?? 0),
        ];

        if ($this->fileSuratPath) {
            $updateData['file_surat'] = $this->fileSuratPath;
        }

        NominalQcDist::updateOrCreate(
            [
                'tanggal'          => $this->tanggal,
                'distributor_code' => $data['kode_dist'],
            ],
            $updateData
        );
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
