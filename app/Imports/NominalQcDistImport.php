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

        if (strpos($val, ',') === false) {
            if (substr_count($val, '.') > 1) {
                $val = str_replace('.', '', $val);
            } else if (preg_match('/^\-?[0-9]+\.[0-9]{3}$/', $val)) {
                $val = str_replace('.', '', $val);
            }
        } else {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
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
