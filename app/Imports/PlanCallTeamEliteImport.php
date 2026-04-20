<?php

namespace App\Imports;

use App\Models\PlanCallTeamElite;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\DB;
use Exception;

class PlanCallTeamEliteImport implements OnEachRow, WithEvents
{
    protected $rows = [];
    protected $syncMode = 'N';

    /**
     * Baca setiap baris Excel
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $rowArray = $row->toArray();

        // Skip header
        if ($rowIndex == 1) return;

        // Skip jika baris kosong (misal kode sales kosong)
        if (empty($rowArray[3])) return;

        try {
            // Ambil sync mode dari baris pertama data (baris 2)
            if ($rowIndex == 2) {
                $this->syncMode = strtoupper(trim($rowArray[9] ?? 'N'));
            }

            // Convert tanggal
            $tanggal = null;
            if (!empty($rowArray[0])) {
                if (is_numeric($rowArray[0])) {
                    $tanggal = Carbon::instance(
                        ExcelDate::excelToDateTimeObject($rowArray[0])
                    )->format('Y-m-d');
                } else {
                    $tanggal = Carbon::parse($rowArray[0])->format('Y-m-d');
                }
            }

            // Gunakan trim() untuk menghindari mismatch akibat spasi di Excel
            $this->rows[] = [
                'tanggal'    => $tanggal,
                'minggu'     => trim($rowArray[1] ?? ''),
                'level'      => trim($rowArray[2] ?? ''),
                'kode_sales' => trim($rowArray[3] ?? ''),
                'cabang'     => trim($rowArray[4] ?? ''),
                'kode_toko'  => trim($rowArray[5] ?? ''),
                'nama_toko'  => trim($rowArray[6] ?? ''),
                'pilar'      => trim($rowArray[7] ?? ''),
                'target'     => isset($rowArray[8]) 
                                ? (float) str_replace(',', '.', $rowArray[8]) 
                                : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

        } catch (Exception $e) {
            throw new Exception("Error di baris {$rowIndex}: " . $e->getMessage());
        }
    }

    /**
     * Event setelah import selesai
     */
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                $this->handleAfterImport();
            },
        ];
    }

    /**
     * Logic utama setelah semua row dibaca
     */
    public function handleAfterImport()
    {
        if (empty($this->rows)) return;

        // Validasi mode
        if (!in_array($this->syncMode, ['Y','N'])) {
            throw new Exception("sync_mode harus Y atau N (Ditemukan: {$this->syncMode})");
        }

        $collection = collect($this->rows);

        // Bungkus dalam transaksi agar aman
        DB::transaction(function () use ($collection) {
            
            // 1. Jalankan UPSERT (Insert atau Update jika kunci unik sudah ada)
            // Penting: Database HARUS memiliki UNIQUE INDEX pada kolom di bawah ini
            PlanCallTeamElite::upsert(
                $this->rows,
                ['tanggal', 'minggu', 'level', 'kode_sales', 'cabang', 'kode_toko'],
                ['nama_toko', 'pilar', 'target', 'updated_at']
            );

            // 2. Jika FULL SYNC aktif, hapus data di DB yang tidak ada di file Excel
            if ($this->syncMode === 'Y') {
                
                $tanggalList = $collection->pluck('tanggal')->unique()->filter()->toArray();
                $mingguList  = $collection->pluck('minggu')->unique()->filter()->toArray();
                $salesList   = $collection->pluck('kode_sales')->unique()->filter()->toArray();

                // Buat unique keys dari file excel untuk pembanding
                $keysInFile = $collection->map(function ($item) {
                    return $this->generateKeyString($item['tanggal'], $item['minggu'], $item['level'], $item['kode_sales'], $item['cabang'], $item['kode_toko']);
                })->toArray();

                // Ambil data existing sesuai scope (filter berdasarkan tanggal/minggu/sales agar tidak scan seluruh tabel)
                PlanCallTeamElite::whereIn('tanggal', $tanggalList)
                    ->whereIn('minggu', $mingguList)
                    ->whereIn('kode_sales', $salesList)
                    ->get()
                    ->each(function ($row) use ($keysInFile) {
                        
                        // Normalisasi format tanggal dari database (pastikan string Y-m-d)
                        $dbDate = $row->tanggal instanceof \Carbon\Carbon 
                                  ? $row->tanggal->format('Y-m-d') 
                                  : $row->tanggal;

                        $rowKey = $this->generateKeyString($dbDate, $row->minggu, $row->level, $row->kode_sales, $row->cabang, $row->kode_toko);

                        // Jika data di database tidak ditemukan di dalam file excel -> Hapus
                        if (!in_array($rowKey, $keysInFile)) {
                            $row->delete();
                        }
                    });
            }
        });
    }

    /**
     * Helper untuk membuat string unik pembanding
     */
    private function generateKeyString($tgl, $minggu, $level, $sales, $cabang, $toko)
    {
        return implode('|', [
            trim($tgl),
            trim($minggu),
            trim($level),
            trim($sales),
            trim($cabang),
            trim($toko)
        ]);
    }
}