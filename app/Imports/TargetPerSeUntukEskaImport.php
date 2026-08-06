<?php

namespace App\Imports;

use App\Models\TargetPerSeUntukEska;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;

class TargetPerSeUntukEskaImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected static $cleanedPeriods = [];
    protected $truncatePeriod;
    protected $importId;

    public $importedCount = 0;
    public $skippedCount = 0;
    public $truncatedCount = 0;
    public $totalValue = 0;
    public $errorLogs = [];
    private $currentRow = 1; // Header = row 1

    public function __construct(bool $truncatePeriod = true, string $importId = null)
    {
        $this->truncatePeriod = $truncatePeriod;
        $this->importId = $importId;
    }

    /**
     * Membaca dan memproses per 2.000 baris (Chunking).
     */
    public function chunkSize(): int
    {
        return 2000;
    }

    /**
     * Helper untuk parse string angka menjadi float desimal.
     */
    private function parseNumber($val): float
    {
        if (is_null($val) || $val === '') return 0.0;
        if (is_int($val) || is_float($val)) return (float) $val;

        $val = trim((string) $val);
        $val = preg_replace('/[^-0-9.,]/', '', $val);

        $lastDot = strrpos($val, '.');
        $lastComma = strrpos($val, ',');

        if ($lastDot !== false && $lastComma !== false) {
            if ($lastDot > $lastComma) {
                $val = str_replace(',', '', $val);
            } else {
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            }
        } elseif ($lastComma !== false) {
            $val = str_replace(',', '.', $val);
        }

        return (float) $val;
    }

    /**
     * Update progres real-time ke Cache agar Livewire dapat membaca secara polling.
     */
    protected function updateProgress(int $currentCount)
    {
        if ($this->importId) {
            Cache::put("import_progress_{$this->importId}", [
                'current' => $currentCount,
                'status'  => 'processing',
            ], 300);
        }
    }

    /**
     * Pengolahan koleksi baris secara Batch per 2.000 baris dengan DB Commit & Real-time Progress.
     */
    public function collection(Collection $rows)
    {
        $now = now();
        $insertBuffer = [];

        DB::transaction(function () use ($rows, $now, &$insertBuffer) {
            foreach ($rows as $row) {
                $this->currentRow++;
                $data = $row->toArray();

                $tahun = trim((string) ($data['tahun'] ?? $data[0] ?? ''));
                $bulanRaw = trim((string) ($data['bulan'] ?? $data[1] ?? ''));
                $region = trim((string) ($data['region'] ?? $data[2] ?? ''));
                $branch = trim((string) ($data['branch'] ?? $data[3] ?? ''));
                $sellingpoint = trim((string) ($data['sellingpoint'] ?? $data[4] ?? ''));
                $salesman = trim((string) ($data['salesman'] ?? $data[5] ?? ''));
                $outlet = trim((string) ($data['outlet'] ?? $data[6] ?? ''));
                $valueRaw = $data['value'] ?? $data[7] ?? 0;

                if (empty($tahun) || empty($bulanRaw)) {
                    $this->skippedCount++;
                    if (count($this->errorLogs) < 50) {
                        $this->errorLogs[] = "Baris {$this->currentRow}: GAGAL - Kolom 'tahun' atau 'bulan' tidak terisi.";
                    }
                    continue;
                }

                $bulan = str_pad($bulanRaw, 2, '0', STR_PAD_LEFT);

                // Truncate data lama per periode (sekali per tahun + bulan)
                if ($this->truncatePeriod) {
                    $periodKey = "{$tahun}_{$bulan}";
                    if (!isset(self::$cleanedPeriods[$periodKey])) {
                        $deleted = TargetPerSeUntukEska::where('tahun', $tahun)
                            ->where('bulan', $bulan)
                            ->delete();
                        $this->truncatedCount += $deleted;
                        self::$cleanedPeriods[$periodKey] = true;
                    }
                }

                $value = $this->parseNumber($valueRaw);
                $this->totalValue += $value;

                $insertBuffer[] = [
                    'tahun'        => $tahun,
                    'bulan'        => $bulan,
                    'region'       => $region ?: null,
                    'branch'       => $branch ?: null,
                    'sellingpoint' => $sellingpoint ?: null,
                    'salesman'     => $salesman ?: null,
                    'outlet'       => $outlet ?: null,
                    'value'        => $value,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];

                $this->importedCount++;

                // Commit & insert sekaligus per 2.000 baris data
                if (count($insertBuffer) >= 2000) {
                    DB::table('target_per_se_untuk_eska')->insert($insertBuffer);
                    $insertBuffer = [];
                    $this->updateProgress($this->importedCount);
                }
            }

            // Flush sisa buffer terakhir di bawah 2.000 baris
            if (!empty($insertBuffer)) {
                DB::table('target_per_se_untuk_eska')->insert($insertBuffer);
                $insertBuffer = [];
                $this->updateProgress($this->importedCount);
            }
        });
    }
}
