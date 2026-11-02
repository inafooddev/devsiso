<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class SalesInvoicePeriodGuard
{
    /**
     * Memeriksa kesesuaian periode antara pilihan UI dengan sampel data di dalam file spreadsheet.
     *
     * @param Worksheet $sheet
     * @param array $config
     * @param int $targetMonth (1-12)
     * @param int $targetYear (misal 2026)
     * @param int $maxSampleRows
     * @return array
     */
    public static function checkConsistency(
        Worksheet $sheet,
        array $config,
        int $targetMonth,
        int $targetYear,
        int $maxSampleRows = 100
    ): array {
        $targetPeriodName = self::formatPeriodName($targetYear, $targetMonth);

        // 1. Tentukan index kolom untuk tanggal faktur (1-based)
        $dateColIndex = self::resolveDateColumnIndex($sheet, $config);

        if ($dateColIndex === null) {
            // Jika kolom tanggal tidak terdeteksi pada baris 1, lewati guard agar validasi header yang menangani
            return [
                'is_valid'             => true,
                'dominant_month'       => null,
                'dominant_year'        => null,
                'dominant_period_name' => null,
                'target_period_name'   => $targetPeriodName,
                'match_percentage'     => 100.0,
                'sample_count'         => 0,
                'error_message'        => null,
            ];
        }

        // 2. Ambil sampel baris data (dari baris 2 sampai maks $maxSampleRows)
        $highestRow = min((int)$sheet->getHighestDataRow(), $maxSampleRows + 1);
        $frequencies = [];
        $totalSampled = 0;
        $targetMatches = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $cell = $sheet->getCell([$dateColIndex, $row]);
            $cellValue = $cell->isFormula() 
                ? (function() use ($cell) {
                    try { return $cell->getCalculatedValue(); } 
                    catch (\Throwable $e) { return $cell->getOldCalculatedValue(); }
                })() 
                : $cell->getValue();
            if ($cellValue === null || trim((string)$cellValue) === '') {
                continue;
            }

            $detected = self::extractMonthAndYear($cellValue, $targetMonth, $targetYear);
            if ($detected !== null) {
                $m = $detected['month'];
                $y = $detected['year'];
                $key = sprintf('%04d-%02d', $y, $m);

                $frequencies[$key] = ($frequencies[$key] ?? 0) + 1;
                $totalSampled++;

                if ($m === $targetMonth && $y === $targetYear) {
                    $targetMatches++;
                }
            }
        }

        if ($totalSampled === 0) {
            return [
                'is_valid'             => true,
                'dominant_month'       => null,
                'dominant_year'        => null,
                'dominant_period_name' => null,
                'target_period_name'   => $targetPeriodName,
                'match_percentage'     => 100.0,
                'sample_count'         => 0,
                'error_message'        => null,
            ];
        }

        // 3. Tentukan periode dominan
        arsort($frequencies);
        $dominantKey = array_key_first($frequencies);
        list($dominantYearStr, $dominantMonthStr) = explode('-', $dominantKey);
        $dominantYear = (int)$dominantYearStr;
        $dominantMonth = (int)$dominantMonthStr;
        $dominantCount = $frequencies[$dominantKey];

        $dominantPercentage = round(($dominantCount / $totalSampled) * 100, 1);
        $matchPercentage = round(($targetMatches / $totalSampled) * 100, 1);
        $dominantPeriodName = self::formatPeriodName($dominantYear, $dominantMonth);

        // 4. Evaluasi Kesesuaian Periode:
        // Dianggap TIDAK SESUAI jika:
        // - Tahun dominan tidak sama dengan tahun target, ATAU
        // - Bulan dominan tidak sama dengan bulan target DAN kecocokan dengan bulan target di bawah 50%
        $isValid = true;
        $errorMessage = null;

        if ($dominantYear !== $targetYear) {
            $isValid = false;
            $errorMessage = "PERIODE TIDAK SESUAI: Anda memilih tahun {$targetYear}, tetapi data transaksi di dalam berkas terdeteksi tahun {$dominantYear} ({$dominantPercentage}% data).";
        } elseif ($dominantMonth !== $targetMonth && $matchPercentage < 50.0) {
            $isValid = false;
            $errorMessage = "PERIODE TIDAK SESUAI: Anda memilih periode {$targetPeriodName}, tetapi isi berkas terdeteksi adalah transaksi periode {$dominantPeriodName} ({$dominantPercentage}% data). Proses dibatalkan untuk mencegah salah timpa data.";
        }

        return [
            'is_valid'             => $isValid,
            'dominant_month'       => $dominantMonth,
            'dominant_year'        => $dominantYear,
            'dominant_period_name' => $dominantPeriodName,
            'target_period_name'   => $targetPeriodName,
            'match_percentage'     => $matchPercentage,
            'sample_count'         => $totalSampled,
            'error_message'        => $errorMessage,
        ];
    }

    /**
     * Menemukan posisi index kolom tanggal pada baris 1
     */
    protected static function resolveDateColumnIndex(Worksheet $sheet, array $config): ?int
    {
        $expectedHeader = trim((string)($config['invoice_date']['header_inv_dist'] ?? ''));
        $configIndex = isset($config['invoice_date']['index']) && $config['invoice_date']['index'] > 0 
            ? (int)$config['invoice_date']['index'] 
            : null;

        $highestCol = $sheet->getHighestDataColumn();
        $headerCells = $sheet->rangeToArray("A1:{$highestCol}1")[0] ?? [];

        // Prioritas 1: Cocokkan nama header yang dikonfigurasi
        if ($expectedHeader !== '') {
            $cleanExpected = self::normalizeHeader($expectedHeader);
            foreach ($headerCells as $idx0 => $cellVal) {
                if (self::normalizeHeader((string)$cellVal) === $cleanExpected) {
                    return $idx0 + 1;
                }
            }
        }

        // Prioritas 2: Cari kata kunci umum header tanggal jika nama persis tidak ketemu
        $commonDateKeywords = ['tanggal', 'tgl', 'tgl faktur', 'tgl invoice', 'invoice date', 'inv date'];
        foreach ($headerCells as $idx0 => $cellVal) {
            $norm = self::normalizeHeader((string)$cellVal);
            if (in_array($norm, $commonDateKeywords)) {
                return $idx0 + 1;
            }
        }

        // Prioritas 3: Gunakan nomor index dari konfigurasi jika valid
        return $configIndex;
    }

    /**
     * Mengekstrak bulan dan tahun dari berbagai format sel tanggal
     */
    public static function extractMonthAndYear($value, int $targetMonth, int $targetYear): ?array
    {
        if (empty($value) && $value !== 0 && $value !== '0') {
            return null;
        }

        // 1. Serial number numerik Excel
        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject($value);
                return [
                    'month' => (int)$dt->format('n'),
                    'year'  => (int)$dt->format('Y'),
                ];
            } catch (\Exception $e) {}
        }

        $valStr = trim((string)$value);

        // Bersihkan jam
        if (str_contains($valStr, ' ')) {
            $valStr = trim(explode(' ', $valStr)[0]);
        }
        if (str_contains($valStr, 'T')) {
            $valStr = trim(explode('T', $valStr)[0]);
        }

        // 2. Format ISO YYYY-MM-DD atau YYYY/MM/DD
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $valStr, $m)) {
            return [
                'year'  => (int)$m[1],
                'month' => (int)$m[2],
            ];
        }

        // 3. Format DD-MM-YYYY atau MM-DD-YYYY
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $valStr, $m)) {
            $p1 = (int)$m[1];
            $p2 = (int)$m[2];
            $year = (int)$m[3];

            // Jika part1 > 12, part1 pasti tanggal dan part2 pasti bulan (Format ID)
            if ($p1 > 12 && $p2 >= 1 && $p2 <= 12) {
                return ['month' => $p2, 'year' => $year];
            }

            // Jika part2 > 12, part2 pasti tanggal dan part1 pasti bulan (Format US)
            if ($p2 > 12 && $p1 >= 1 && $p1 <= 12) {
                return ['month' => $p1, 'year' => $year];
            }

            // Jika keduanya <= 12 (ambigu):
            // Cek apakah salah satunya cocok dengan targetMonth
            if ($p2 === $targetMonth) {
                return ['month' => $p2, 'year' => $year];
            }
            if ($p1 === $targetMonth) {
                return ['month' => $p1, 'year' => $year];
            }

            // Jika tidak ada yang cocok dengan targetMonth, default ke format Indonesia (d/m/Y)
            return ['month' => $p2, 'year' => $year];
        }

        // 4. Format dengan nama bulan (misal 15-Okt-2026 atau 15-Oct-2026)
        $indoMonths = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
            'mei' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'agu' => 'Aug',
            'agt' => 'Aug', 'sep' => 'Sep', 'okt' => 'Oct', 'nov' => 'Nov', 'des' => 'Dec'
        ];
        $normalizedDateStr = strtolower($valStr);
        foreach ($indoMonths as $indo => $eng) {
            $normalizedDateStr = str_replace($indo, $eng, $normalizedDateStr);
        }

        try {
            $parsed = Carbon::parse($normalizedDateStr);
            if ($parsed && $parsed->year >= 2000 && $parsed->year <= 2100) {
                return [
                    'month' => (int)$parsed->month,
                    'year'  => (int)$parsed->year,
                ];
            }
        } catch (\Exception $e) {}

        return null;
    }

    public static function formatPeriodName(int $year, int $month): string
    {
        $indoMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $indoMonths[$month] ?? Carbon::create($year, $month, 1)->translatedFormat('F');
        return "{$monthName} {$year}";
    }

    protected static function normalizeHeader(string $str): string
    {
        $str = trim(strtolower($str));
        $str = preg_replace('/\s+/', ' ', $str);
        return preg_replace('/[^a-z0-9]/', '', $str);
    }
}
