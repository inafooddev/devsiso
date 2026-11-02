<?php

namespace App\Imports;

use App\Models\SalesInvoiceDistributor;
use App\Models\ImportBatch;
use App\Services\SalesInvoiceSpreadsheetScanner;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;
use App\Helpers\UnitHelper;

class SalesInvoiceImport implements OnEachRow
{
    protected array $config;
    protected string $distributorCode;
    protected ImportBatch $batch;
    protected ?int $targetMonth;
    protected ?int $targetYear;
    protected int $headerRowIndex;
    protected bool $hasDeletedExistingData = false;

    // Pemetaan dinamis posisi kolom berdasarkan nama header di file
    public array $resolvedColumnIndexes = [];
    public int $realignedCount = 0;

    // Rincian baris yang dilewati (skipped)
    public array $skippedRowsSummary = [];
    public array $skippedRowsData = [];
    public int $skippedCount = 0;

    // Buffer untuk bulk insert (chunking)
    protected array $rowsBuffer = [];
    protected int $chunkSize = 500;
    protected int $insertedCount = 0;

    /**
     * Constructor
     */
    public function __construct(
        array $config, 
        string $distributorCode, 
        ImportBatch $batch, 
        ?int $targetMonth = null, 
        ?int $targetYear = null,
        int $headerRowIndex = 1
    ) {
        $this->config = $config;
        $this->distributorCode = strtoupper(trim($distributorCode));
        $this->batch = $batch;
        $this->targetMonth = $targetMonth;
        $this->targetYear = $targetYear;
        $this->headerRowIndex = max(1, $headerRowIndex);
    }

    /**
     * Memproses setiap baris dari file Excel
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();

        // Ambil nilai sel dengan evaluasi rumus Excel otomatis (mengambil hasil kalkulasi formula)
        try {
            $rowArray = $row->toArray(null, true, false);
        } catch (\Throwable $e) {
            $rowArray = $row->toArray(null, false, false);
        }

        // Lewati baris sebelum baris header (misal baris kop judul laporan pada file)
        if ($rowIndex < $this->headerRowIndex) {
            return;
        }

        // Baris header: lakukan validasi & penyelarasan posisi kolom dinamis
        if ($rowIndex == $this->headerRowIndex) {
            $this->validateHeader($rowArray);
            return;
        }

        // Auto-Skip baris ringkasan / Grand Total / Footer / Baris Kosong
        $invIdx = $this->resolvedColumnIndexes['invoice_no'] ?? null;
        $prodIdx = $this->resolvedColumnIndexes['product_code'] ?? null;
        $skipReason = SalesInvoiceSpreadsheetScanner::getSkipReason($rowArray, $invIdx, $prodIdx);

        if ($skipReason !== null) {
            $this->skippedCount++;
            if (count($this->skippedRowsSummary) < 10) {
                $sampleText = trim(implode(' ', array_slice(array_filter($rowArray, fn($v) => $v !== null && trim((string)$v) !== ''), 0, 3)));
                $this->skippedRowsSummary[] = "Baris ke-{$rowIndex}: {$skipReason}" . ($sampleText !== '' ? " [cuplikan: '{$sampleText}']" : "");
            }

            // Simpan detail baris yang dilewati untuk fitur ekspor/unduh Excel
            $dateIdx = $this->resolvedColumnIndexes['invoice_date'] ?? null;
            $custIdx = $this->resolvedColumnIndexes['customer_name'] ?? null;
            $prodNameIdx = $this->resolvedColumnIndexes['product_name'] ?? null;

            $this->skippedRowsData[] = [
                'row_number'   => $rowIndex,
                'skip_reason'  => $skipReason,
                'invoice_no'   => $invIdx !== null ? (string)($rowArray[$invIdx] ?? '') : '',
                'invoice_date' => $dateIdx !== null ? (string)($rowArray[$dateIdx] ?? '') : '',
                'customer'     => $custIdx !== null ? (string)($rowArray[$custIdx] ?? '') : '',
                'product_code' => $prodIdx !== null ? (string)($rowArray[$prodIdx] ?? '') : '',
                'product_name' => $prodNameIdx !== null ? (string)($rowArray[$prodNameIdx] ?? '') : '',
                'raw_sample'   => implode(' | ', array_slice(array_filter(array_map('strval', $rowArray), fn($v) => trim($v) !== ''), 0, 6)),
            ];

            return;
        }

        // Hapus data periode lama sebelum mulai memasukkan baris pertama data
        if (!$this->hasDeletedExistingData) {
            $this->deleteExistingDataForPeriod($rowArray);
        }

        // Proses baris data dan masukkan ke buffer
        $this->processRow($rowArray, $rowIndex);
    }

    /**
     * Menghapus data lama dengan aman pada periode yang ditentukan
     */
    protected function deleteExistingDataForPeriod(array $firstRowArray)
    {
        $month = $this->targetMonth;
        $year = $this->targetYear;

        // Jika user tidak menentukan bulan/tahun target di UI, ambil dari tanggal baris pertama
        if (!$month || !$year) {
            $getValue = function($columnName) use ($firstRowArray) {
                if (isset($this->resolvedColumnIndexes[$columnName])) {
                    return $firstRowArray[$this->resolvedColumnIndexes[$columnName]] ?? null;
                }
                if (isset($this->config[$columnName]) && $this->config[$columnName]['index'] > 0) {
                    $index = $this->config[$columnName]['index'] - 1;
                    return $firstRowArray[$index] ?? null;
                }
                return null;
            };

            $invoiceDateValue = $getValue('invoice_date');
            $parsedDate = $this->transformDate($invoiceDateValue);
            if ($parsedDate) {
                $d = Carbon::parse($parsedDate);
                $month = (int)$d->month;
                $year = (int)$d->year;
            }
        }

        if ($month && $year) {
            $query = SalesInvoiceDistributor::where('distributor_code', $this->distributorCode)
                ->whereMonth('invoice_date', $month)
                ->whereYear('invoice_date', $year);

            $recordCountToDelete = $query->count();
            $monthName = Carbon::create($year, $month, 1)->translatedFormat('F');

            if ($recordCountToDelete > 0) {
                $this->batch->addLog('info', "Mendeteksi {$recordCountToDelete} data lama untuk periode {$monthName} {$year}. Menghapus...");
                $deletedRows = $query->delete();
                $this->batch->addLog('success', "Berhasil menghapus {$deletedRows} data lama periode {$monthName} {$year}.");
            } else {
                $this->batch->addLog('info', "Tidak ditemukan data lama untuk periode {$monthName} {$year}. Memulai import...");
            }
        }

        $this->hasDeletedExistingData = true;
    }

    /**
     * Memproses dan menyusun array satu baris data
     */
    protected function processRow(array $rowArray, int $rowIndex)
    {
        $getValue = function($columnName) use ($rowArray) {
            if (isset($this->resolvedColumnIndexes[$columnName])) {
                $idx = $this->resolvedColumnIndexes[$columnName];
                return $rowArray[$idx] ?? null;
            }
            if (isset($this->config[$columnName]) && $this->config[$columnName]['index'] > 0) {
                $index = $this->config[$columnName]['index'] - 1;
                return $rowArray[$index] ?? null;
            }
            return null;
        };

        $toFloat = function($value) {
            if (empty($value) && $value !== 0 && $value !== '0') return 0.0;
            $val = trim((string)$value);

            // Buang prefix mata uang (Rp, IDR) dan spasi non-breaking
            $val = preg_replace('/^(rp\.?|idr\.?)\s*/i', '', $val);
            $val = trim($val);

            // Deteksi format negatif akuntansi berkurung: (150.000) atau (150,000.00)
            $isNegative = false;
            if (str_starts_with($val, '(') && str_ends_with($val, ')')) {
                $isNegative = true;
                $val = trim(substr($val, 1, -1));
            } elseif (str_ends_with($val, '-')) {
                // Format minus di akhir (contoh: 150.000-)
                $isNegative = true;
                $val = trim(substr($val, 0, -1));
            } elseif (str_starts_with($val, '-')) {
                $isNegative = true;
                $val = trim(substr($val, 1));
            }

            // Normalisasi pemisah ribuan dan desimal
            if (str_contains($val, '.') && str_contains($val, ',')) {
                $lastDot = strrpos($val, '.');
                $lastComma = strrpos($val, ',');
                if ($lastComma > $lastDot) {
                    // Format ID: 1.000,50 -> 1000.50
                    $val = str_replace('.', '', $val);
                    $val = str_replace(',', '.', $val);
                } else {
                    // Format US: 1,000.50 -> 1000.50
                    $val = str_replace(',', '', $val);
                }
            } elseif (str_contains($val, ',')) {
                $parts = explode(',', $val);
                if (count($parts) == 2 && strlen($parts[1]) <= 2) {
                    $val = str_replace(',', '.', $val);
                } else {
                    $val = str_replace(',', '', $val);
                }
            } elseif (substr_count($val, '.') > 1) {
                // Ribuan titik tanpa koma: 1.500.000
                $val = str_replace('.', '', $val);
            }

            $val = preg_replace('/[^0-9.-]/', '', $val);
            $floatVal = is_numeric($val) ? (float) $val : 0.0;
            return $isNegative ? -$floatVal : $floatVal;
        };

        $cleanString = function($value) {
            if ($value === null) return null;
            $str = (string)$value;
            // Bersihkan Unicode non-breaking spaces (\u{00A0}, \xc2\xa0) dan whitespace tak terlihat lainnya
            $str = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{FEFF}]/u', ' ', $str);
            $str = trim($str);
            return $str !== '' ? $str : null;
        };

        // Kode distributor selalu diambil dari distributor yang dipilih pada UI
        // Hal ini menangani kasus file hasil unduhan langsung (WhatsApp/ERP) yang tidak memiliki kolom kode distributor.
        $finalDistCode = $this->distributorCode;

        $netAmountValue = $getValue('net_amount');
        $netAmountFloat = $toFloat($netAmountValue);
        $invoiceType = $netAmountFloat >= 0 ? 'inv' : 'ret';

        // Logika bonus
        $finalBonusValue = 0;
        $isDiscount8Mapped = isset($this->config['discount8']) && $this->config['discount8']['index'] > 0;
        if ($isDiscount8Mapped) {
            $finalBonusValue = $getValue('discount8');
        } else {
            if ($netAmountFloat == 0) {
                $finalBonusValue = $toFloat($getValue('carton_qty')) + $toFloat($getValue('pack_qty')) + $toFloat($getValue('pcs_qty')) + $toFloat($getValue('quantity'));
            }
        }
        $isBonusValue = $toFloat($getValue('discount8')) != 0 || $netAmountFloat == 0;

        $rawInvoiceDate = $getValue('invoice_date');
        $invoiceDate = $this->transformDate($rawInvoiceDate);
        if (!$invoiceDate) {
            throw new Exception("Baris ke-{$rowIndex}: Nilai tanggal faktur ('{$rawInvoiceDate}') tidak valid atau kosong.");
        }
        $orderDate = $this->transformDate($getValue('order_date')) ?? $invoiceDate;

        $now = now()->toDateTimeString();

        $this->rowsBuffer[] = [
            'distributor_code'  => $this->distributorCode, // Selalu gunakan distributor yang valid
            'invoice_type'      => $invoiceType,
            'invoice_no'        => $cleanString($getValue('invoice_no')),
            'invoice_date'      => $invoiceDate,
            'order_no'          => $cleanString($getValue('order_no')),
            'order_date'        => $orderDate,
            'salesman_code'     => $cleanString($getValue('salesman_code')),
            'salesman_name'     => $cleanString($getValue('salesman_name')),
            'customer_code'     => $cleanString($getValue('customer_code')),
            'customer_name'     => $cleanString($getValue('customer_name')),
            'address'           => $cleanString($getValue('address')),
            'product_code'      => $cleanString($getValue('product_code')),
            'product_name'      => $cleanString($getValue('product_name')),
            'carton_qty'        => $toFloat($getValue('carton_qty')),
            'pack_qty'          => $toFloat($getValue('pack_qty')),
            'pcs_qty'           => $toFloat($getValue('pcs_qty')),
            'quantity'          => $toFloat($getValue('quantity')),
            'unit'              => UnitHelper::normalize($cleanString($getValue('unit')), $this->distributorCode),
            'bonus'             => $toFloat($finalBonusValue),
            'is_bonus'          => $isBonusValue,
            'unit_price'        => $toFloat($getValue('unit_price')),
            'gross_amount'      => $toFloat($getValue('gross_amount')),
            'discount1'         => $toFloat($getValue('discount1')),
            'discount2'         => $toFloat($getValue('discount2')),
            'discount3'         => $toFloat($getValue('discount3')),
            'discount4'         => $toFloat($getValue('discount4')),
            'discount5'         => $toFloat($getValue('discount5')),
            'discount6'         => $toFloat($getValue('discount6')),
            'discount7'         => $toFloat($getValue('discount7')),
            'discount8'         => $toFloat($finalBonusValue),
            'total_discount'    => $toFloat($getValue('total_discount')),
            'dpp'               => $toFloat($getValue('dpp')),
            'tax'               => $toFloat($getValue('tax')),
            'net_amount'        => $netAmountFloat,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        // Jika buffer mencapai ukuran chunk (500 baris), lakukan bulk insert
        if (count($this->rowsBuffer) >= $this->chunkSize) {
            $this->flushBuffer();
        }
    }

    /**
     * Melakukan bulk insert untuk data yang terkumpul di buffer
     */
    public function flushBuffer()
    {
        if (empty($this->rowsBuffer)) {
            return;
        }

        $count = count($this->rowsBuffer);
        SalesInvoiceDistributor::insert($this->rowsBuffer);
        $this->rowsBuffer = [];

        $this->insertedCount += $count;
        $this->batch->increment('processed_rows', $count);
    }

    /**
     * Normalisasi string header untuk toleransi huruf kapital, spasi ganda, dan karakter pemisah
     */
    public function normalizeHeader(string $header): string
    {
        $header = trim($header);
        $header = preg_replace('/\s+/', ' ', $header);
        $header = strtolower($header);
        return str_replace(['_', '-'], ' ', $header);
    }

    /**
     * Memvalidasi header dari file Excel dengan Penyelarasan Posisi Dinamis (Dynamic Column Re-alignment)
     */
    public function validateHeader(array $headerRow)
    {
        $errors = [];
        $this->resolvedColumnIndexes = [];
        $this->realignedCount = 0;

        // 1. Buat peta posisi seluruh header yang ada di baris 1 file Excel
        $fileHeaderMap = [];
        foreach ($headerRow as $idxZero => $cellVal) {
            $normalized = $this->normalizeHeader((string)$cellVal);
            if ($normalized !== '') {
                $fileHeaderMap[$normalized] = $idxZero;
            }
        }

        // 2. Selaraskan setiap kolom konfigurasi ke posisi riil di file
        foreach ($this->config as $dbColumn => $columnConfig) {
            if (empty($columnConfig['header_inv_dist'])) {
                continue;
            }

            // distributor_code: jika di file tidak ada, otomatis diisi dari pilihan UI
            if ($dbColumn === 'distributor_code') {
                $expected = $this->normalizeHeader((string)$columnConfig['header_inv_dist']);
                if (isset($fileHeaderMap[$expected])) {
                    $this->resolvedColumnIndexes[$dbColumn] = $fileHeaderMap[$expected];
                } elseif (isset($fileHeaderMap['kode cabang']) || isset($fileHeaderMap['kode distributor'])) {
                    $this->resolvedColumnIndexes[$dbColumn] = $fileHeaderMap['kode cabang'] ?? $fileHeaderMap['kode distributor'];
                }
                continue; // Jangan gagalkan jika distributor_code tidak ada di file
            }

            $expectedHeader = trim((string)$columnConfig['header_inv_dist']);
            $normalizedExpected = $this->normalizeHeader($expectedHeader);
            $configuredIndex = isset($columnConfig['index']) && $columnConfig['index'] > 0 ? ((int)$columnConfig['index'] - 1) : -1;

            // Prioritas 1: Cek apakah di posisi index konfigurasi terdapat nama header yang cocok
            $headerAtIndex = isset($headerRow[$configuredIndex]) ? $this->normalizeHeader((string)$headerRow[$configuredIndex]) : '';

            if ($configuredIndex >= 0 && $headerAtIndex === $normalizedExpected) {
                $this->resolvedColumnIndexes[$dbColumn] = $configuredIndex;
                continue;
            }

            // Prioritas 2: Posisi kolom bergeser! Cari nama header di mana pun pada baris 1 file Excel
            if (isset($fileHeaderMap[$normalizedExpected])) {
                $realIndex = $fileHeaderMap[$normalizedExpected];
                $this->resolvedColumnIndexes[$dbColumn] = $realIndex;
                $this->realignedCount++;
                continue;
            }

            // Prioritas 3: Toleransi karakter tanda baca (misal "NO. FAKTUR" vs "NO FAKTUR", "T. DISC" vs "T DISC")
            $cleanExpected = preg_replace('/[^a-z0-9]/', '', $normalizedExpected);
            $foundFuzzy = false;
            foreach ($fileHeaderMap as $normFileHeader => $realIdx) {
                $cleanFile = preg_replace('/[^a-z0-9]/', '', $normFileHeader);
                if ($cleanExpected === $cleanFile && !empty($cleanExpected)) {
                    $this->resolvedColumnIndexes[$dbColumn] = $realIdx;
                    $this->realignedCount++;
                    $foundFuzzy = true;
                    break;
                }
            }
            if ($foundFuzzy) {
                continue;
            }

            // Jika benar-benar tidak ditemukan di mana pun pada file:
            // Cek apakah ini kolom wajib inti
            $coreRequired = ['invoice_no', 'invoice_date', 'customer_code', 'product_code', 'net_amount'];
            if (in_array($dbColumn, $coreRequired)) {
                $errors[] = "Kolom wajib '{$expectedHeader}' (untuk {$dbColumn}) tidak ditemukan di dalam file Excel.";
            }
        }

        // Catat info jika terjadi pergeseran kolom yang berhasil diselaraskan otomatis
        if ($this->realignedCount > 0) {
            $this->batch->addLog('info', "Menyelaraskan otomatis {$this->realignedCount} kolom yang bergeser posisinya berdasarkan nama header file.");
        }

        if (!empty($errors)) {
            $validator = Validator::make([], []);
            foreach ($errors as $error) {
                $validator->errors()->add('header_error', $error);
            }
            throw new ValidationException($validator);
        }
    }

    /**
     * Mengubah berbagai format tanggal menjadi Y-m-d dengan pencegahan ambigu dan strict parsing
     */
    public function transformDate($value): ?string
    {
        if (empty($value) && $value !== 0 && $value !== '0') return null;

        // 1. Jika serial number numerik bawaan Excel (contoh: 45580)
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        $valStr = trim((string)$value);

        // 2. Bersihkan komponen jam jika ada (misal "2026-10-15 14:30:00" -> "2026-10-15")
        if (str_contains($valStr, ' ')) {
            $parts = explode(' ', $valStr);
            $valStr = trim($parts[0]);
        }
        if (str_contains($valStr, 'T')) {
            $parts = explode('T', $valStr);
            $valStr = trim($parts[0]);
        }

        // 3. Format ISO standar (YYYY-MM-DD atau YYYY/MM/DD)
        if (preg_match('/^\d{4}[-\/]\d{1,2}[-\/]\d{1,2}$/', $valStr)) {
            try {
                return Carbon::parse($valStr)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        // 4. Deteksi cerdas jika ada target bulan yang diketahui (misal targetMonth = 10)
        if ($this->targetMonth && preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $valStr, $matches)) {
            $part1 = (int)$matches[1];
            $part2 = (int)$matches[2];
            $year = (int)$matches[3];

            // Jika part1 adalah bulan target dan part2 adalah tanggal (contoh: 10/05/2026 -> part1=10, part2=5) -> Format US (m/d/Y)
            if ($part1 === $this->targetMonth && $part2 !== $this->targetMonth && $part2 >= 1 && $part2 <= 31) {
                try {
                    return Carbon::create($year, $part1, $part2)->format('Y-m-d');
                } catch (\Exception $e) {}
            }

            // Jika part2 adalah bulan target dan part1 adalah tanggal (contoh: 05/10/2026 -> part1=5, part2=10) -> Format ID (d/m/Y)
            if ($part2 === $this->targetMonth && $part1 !== $this->targetMonth && $part1 >= 1 && $part1 <= 31) {
                try {
                    return Carbon::create($year, $part2, $part1)->format('Y-m-d');
                } catch (\Exception $e) {}
            }

            // Jika tanggal dan bulan sama (contoh: 10/10/2026)
            if ($part1 === $this->targetMonth && $part2 === $this->targetMonth) {
                try {
                    return Carbon::create($year, $part1, $part2)->format('Y-m-d');
                } catch (\Exception $e) {}
            }
        }

        // 5. Strict parsing untuk format standar (dengan validasi format kebalikan untuk mencegah rollover bulan ke tahun lain)
        $formatsToTry = ['d-M-Y', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y-m-d', 'd.m.Y'];
        foreach ($formatsToTry as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $valStr);
                if ($date && $date->format($format) === $valStr) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // 6. Fallback Carbon::parse dengan batas tahun valid
        try {
            $parsed = Carbon::parse($valStr);
            if ($parsed && $parsed->year >= 2000 && $parsed->year <= 2100) {
                return $parsed->format('Y-m-d');
            }
        } catch (\Exception $e) {}

        return null;
    }
}
