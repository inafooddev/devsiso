<?php

namespace App\Imports;

use App\Models\SellingIn;
use App\Models\ImportBatch;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Exception;

class SellingInImport implements OnEachRow
{
    protected $batch;
    protected $hasDeletedExistingData = false;

    public function __construct(ImportBatch $batch)
    {
        $this->batch = $batch;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $rowArray = $row->toArray();

        // Lewati baris 1 (Header) tanpa validasi ketat
        if ($rowIndex === 1) return;

        try {
            $this->processRow($rowArray, $rowIndex);
        } catch (Exception $e) {
            $this->batch->addLog('error', "Gagal pada baris {$rowIndex}: " . $e->getMessage());
        }
    }

    protected function processRow(array $cells, int $rowIndex)
    {
        $toFloat = fn($val) => is_numeric($val) ? (float)$val : 0;
        
        // Ambil Tanggal dari kolom pertama (index 0)
        $tanggalRaw = $cells[0] ?? null;
        $bulanDate = null;

        if (!empty($tanggalRaw)) {
            $bulanDate = is_numeric($tanggalRaw) 
                ? Carbon::instance(ExcelDate::excelToDateTimeObject($tanggalRaw)) 
                : Carbon::parse($tanggalRaw);
        }

        if (!$bulanDate) {
            throw new Exception("Format tanggal tidak valid di kolom pertama.");
        }

        // Logika Hapus Data Lama (Hanya dijalankan pada baris data pertama yang valid)
        if (!$this->hasDeletedExistingData) {
            $bulanNum = $bulanDate->month;
            $tahunNum = $bulanDate->year;
            $namaBulan = $bulanDate->translatedFormat('F');

            $query = SellingIn::whereMonth('bulan', $bulanNum)
                              ->where('tahun', $tahunNum);
            
            $existingCount = $query->count();

            if ($existingCount > 0) {
                $this->batch->addLog('info', "Mendeteksi {$existingCount} data lama untuk periode {$namaBulan} {$tahunNum}. Menghapus...");
                $query->delete();
                $this->batch->addLog('success', "Data lama periode {$namaBulan} {$tahunNum} berhasil dibersihkan.");
            } else {
                $this->batch->addLog('info', "Tidak ada data lama untuk periode {$namaBulan} {$tahunNum}.");
            }

            $this->hasDeletedExistingData = true;
        }

        // Simpan Data Baru
        SellingIn::create([
            'bulan'                => $bulanDate->format('Y-m-01'),
            'tahun'                => $cells[1] ?? $bulanDate->year,
            'rsm'                  => $cells[2] ?? null,
            'region'               => $cells[3] ?? null,
            'area'                 => $cells[4] ?? null,
            'kd_spv'               => $cells[5] ?? null,
            'nama_spv'             => $cells[6] ?? null,
            'cabang'               => $cells[7] ?? null,
            'kd_distributor'       => $cells[8] ?? null,
            'nama_distributor'     => $cells[9] ?? null,
            'nama_distributor_fix' => $cells[10] ?? null,
            'nama_produk'          => $cells[11] ?? null,
            'nama_produk_mapping'  => $cells[12] ?? null,
            'jenis'                => $cells[13] ?? null,
            'reg_fes'              => $cells[14] ?? null,
            'kategori'             => $cells[15] ?? null,
            'top_item'             => $cells[16] ?? null,
            'brand'                => $cells[17] ?? null,
            'sub_brand'            => $cells[18] ?? null,
            'ktn_jual'             => $toFloat($cells[19] ?? 0),
            'pcs_jual'             => $toFloat($cells[20] ?? 0),
            'value_jual'           => $toFloat($cells[21] ?? 0),
            'ktn_retur'            => $toFloat($cells[22] ?? 0),
            'pcs_retur'            => $toFloat($cells[23] ?? 0),
            'value_retur'          => $toFloat($cells[24] ?? 0),
            'ktn_net'              => $toFloat($cells[25] ?? 0),
            'pcs_net'              => $toFloat($cells[26] ?? 0),
            'value_net'            => $toFloat($cells[27] ?? 0),
        ]);

        $this->batch->increment('processed_rows');
    }
}