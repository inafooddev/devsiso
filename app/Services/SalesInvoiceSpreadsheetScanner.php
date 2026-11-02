<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesInvoiceSpreadsheetScanner
{
    /**
     * Kata kunci umum untuk mendeteksi baris header faktur penjualan
     */
    protected static array $coreKeywords = [
        'faktur', 'invoice', 'inv', 'tanggal', 'tgl', 'date',
        'customer', 'pelanggan', 'langganan', 'toko', 'outlet',
        'item', 'produk', 'product', 'barang', 'sku',
        'netto', 'total', 'jumlah', 'nominal', 'amount', 'harga', 'qty', 'jumlah'
    ];

    /**
     * Kata kunci umum untuk mendeteksi baris ringkasan / total / footer yang harus diabaikan
     */
    protected static array $footerKeywords = [
        'grand total', 'sub total', 'subtotal', 'total:', 'total :', 'jumlah total',
        'halaman', 'page ', 'dicetak', 'printed by', 'user:', 'date printed'
    ];

    /**
     * Menemukan Worksheet terbaik yang berisi data transaksi (jika file memiliki banyak sheet)
     */
    public static function findBestWorksheet(Spreadsheet $spreadsheet, array $config): Worksheet
    {
        $allSheets = $spreadsheet->getAllSheets();
        if (count($allSheets) <= 1) {
            return $spreadsheet->getActiveSheet();
        }

        $bestSheet = $spreadsheet->getActiveSheet();
        $highestScore = -1;

        foreach ($allSheets as $sheet) {
            $score = self::scoreWorksheet($sheet, $config);
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestSheet = $sheet;
            }
        }

        return $bestSheet;
    }

    /**
     * Menghitung skor kecocokan suatu worksheet terhadap kolom faktur penjualan
     */
    protected static function scoreWorksheet(Worksheet $sheet, array $config): int
    {
        $highestRow = min((int)$sheet->getHighestDataRow(), 6);
        $highestCol = $sheet->getHighestDataColumn();
        $score = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $cells = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}")[0] ?? [];
            foreach ($cells as $val) {
                $norm = self::normalize((string)$val);
                if ($norm === '') continue;

                foreach (self::$coreKeywords as $keyword) {
                    if (str_contains($norm, $keyword)) {
                        $score += 2;
                        break;
                    }
                }
            }
        }

        return $score;
    }

    /**
     * Menemukan letak baris header (Header Row Index) dari baris 1 s/d 6
     * (Berguna jika file memiliki Kop Surat / Judul Laporan di baris 1-3)
     */
    public static function findHeaderRowIndex(Worksheet $sheet, array $config, int $maxScanRows = 6): int
    {
        $highestRow = min((int)$sheet->getHighestDataRow(), $maxScanRows);
        $highestCol = $sheet->getHighestDataColumn();

        $bestRow = 1;
        $highestScore = 0;

        // Kumpulkan daftar nama header yang diharapkan dari konfigurasi
        $expectedHeaders = [];
        foreach ($config as $colConfig) {
            if (!empty($colConfig['header_inv_dist'])) {
                $expectedHeaders[] = self::normalize((string)$colConfig['header_inv_dist']);
            }
        }

        for ($row = 1; $row <= $highestRow; $row++) {
            $cells = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}")[0] ?? [];
            $rowScore = 0;

            foreach ($cells as $val) {
                $norm = self::normalize((string)$val);
                if ($norm === '') continue;

                // Cek kecocokan persis dengan konfigurasi distributor
                if (in_array($norm, $expectedHeaders)) {
                    $rowScore += 5;
                } else {
                    // Cek kata kunci umum faktur
                    foreach (self::$coreKeywords as $keyword) {
                        if (str_contains($norm, $keyword)) {
                            $rowScore += 2;
                            break;
                        }
                    }
                }
            }

            if ($rowScore > $highestScore) {
                $highestScore = $rowScore;
                $bestRow = $row;
            }
        }

        return $bestRow;
    }

    /**
     * Memeriksa apakah suatu baris data merupakan baris Footer / Summary / Total / Kosong
     * yang harus dilewati (*auto-skip*) agar tidak menyebabkan error di akhir import
     */
    /**
     * Memeriksa alasan mengapa suatu baris data harus dilewati (skip).
     * Mengembalikan pesan alasan jika harus di-skip, atau null jika merupakan baris data transaksi valid.
     */
    public static function getSkipReason(array $rowArray, ?int $invoiceNoIdx = null, ?int $productCodeIdx = null): ?string
    {
        // 1. Jika seluruh sel pada baris kosong
        $nonEmptyCells = array_filter($rowArray, fn($v) => $v !== null && trim((string)$v) !== '');
        if (empty($nonEmptyCells)) {
            return 'Baris kosong tanpa data';
        }

        // 2. Jika nomor faktur DAN kode produk keduanya terisi dengan nilai riil:
        // Maka baris ini 100% adalah BARIS TRANSAKSI VALID! Jangan di-skip.
        if ($invoiceNoIdx !== null && $productCodeIdx !== null) {
            $invVal = trim((string)($rowArray[$invoiceNoIdx] ?? ''));
            $prodVal = trim((string)($rowArray[$productCodeIdx] ?? ''));

            if ($invVal !== '' && $prodVal !== '') {
                $normInv = strtolower($invVal);
                if (in_array($normInv, ['total', 'grand total', 'sub total', 'subtotal', 'jumlah', 'ringkasan'])) {
                    return "Terdeteksi teks ringkasan pada kolom faktur ('{$invVal}')";
                }

                // Deteksi pengulangan header halaman (misal pada file hasil ekspor cetak multi-halaman)
                if (preg_match('/^(no\.?\s*faktur|no\.?\s*inv|invoice\s*no|nomor\s*faktur|faktur)/i', $normInv)
                    || preg_match('/^(kode\s*barang|kode\s*produk|item\s*code|product\s*code)/i', strtolower($prodVal))) {
                    return "Terdeteksi pengulangan header halaman ('{$invVal}')";
                }

                return null; // TRANSAKSI VALID!
            }
        }

        // 3. Jika nomor faktur kosong, periksa apakah baris ini memuat kata kunci footer
        $joinedText = strtolower(implode(' ', array_slice($rowArray, 0, 10)));
        foreach (self::$footerKeywords as $kw) {
            if (str_contains($joinedText, $kw)) {
                return "Terdeteksi catatan kaki/footer ('{$kw}')";
            }
        }

        // 4. Jika kolom nomor faktur kosong tanpa nomor faktur yang valid
        if ($invoiceNoIdx !== null && trim((string)($rowArray[$invoiceNoIdx] ?? '')) === '') {
            return 'Nomor faktur tidak terisi (kosong)';
        }

        return null;
    }

    /**
     * Memeriksa apakah suatu baris data merupakan baris Footer / Summary / Total / Kosong
     * yang harus dilewati (*auto-skip*) agar tidak menyebabkan error di akhir import
     */
    public static function isSummaryOrFooterRow(array $rowArray, ?int $invoiceNoIdx = null, ?int $productCodeIdx = null): bool
    {
        return self::getSkipReason($rowArray, $invoiceNoIdx, $productCodeIdx) !== null;
    }

    /**
     * Normalisasi teks header untuk pencocokan toleran
     */
    public static function normalize(string $str): string
    {
        $str = trim(strtolower($str));
        $str = preg_replace('/\s+/', ' ', $str);
        return preg_replace('/[^a-z0-9]/', '', $str);
    }
}
