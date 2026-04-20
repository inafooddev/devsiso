<?php

namespace App\Imports;

use App\Models\SalesInvoiceDistributor;
use App\Models\ImportBatch;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;

class SalesInvoiceImport implements OnEachRow
{
    protected $config;
    protected $distributorCodeFromFilename;
    protected $batch;
    protected $hasDeletedExistingData = false;

    /**
     * Constructor sekarang menerima model ImportBatch untuk pelaporan progres.
     */
    public function __construct(array $config, string $distributorCodeFromFilename, ImportBatch $batch)
    {
        $this->config = $config;
        $this->distributorCodeFromFilename = $distributorCodeFromFilename;
        $this->batch = $batch;
    }

    /**
     * Memproses setiap baris dari file Excel.
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $rowArray = $row->toArray();

        // Baris 1 adalah header, validasi lalu lewati
        if ($rowIndex == 1) {
            $this->validateHeader($rowArray);
            return;
        }

        // Proses baris data dan teruskan nomor baris untuk logging
        $this->processRow($rowArray, $rowIndex);
    }

    /**
     * Memproses dan menyimpan satu baris data, serta melaporkan progres.
     */
    protected function processRow(array $rowArray, int $rowIndex)
    {
        $getValue = function($columnName) use ($rowArray) {
            if (isset($this->config[$columnName]) && $this->config[$columnName]['index'] > 0) {
                $index = $this->config[$columnName]['index'] - 1;
                return $rowArray[$index] ?? null;
            }
            return null;
        };

        $toFloat = function($value) {
            if (empty($value)) return 0.0;
            $normalizedValue = str_replace(',', '.', (string)$value);
            if (substr_count($normalizedValue, '.') > 1) {
                $parts = explode('.', $normalizedValue);
                $lastPart = array_pop($parts);
                $firstPart = implode('', $parts);
                $normalizedValue = $firstPart . '.' . $lastPart;
            }
            return is_numeric($normalizedValue) ? (float) $normalizedValue : 0.0;
        };

        // Logika untuk menghapus data lama (hanya berjalan sekali)
        if (!$this->hasDeletedExistingData) {
            $invoiceDateValue = $getValue('invoice_date');
            $invoiceDate = $this->transformDate($invoiceDateValue);
            if ($invoiceDate) {
                $dateObject = Carbon::parse($invoiceDate);
                
                $query = SalesInvoiceDistributor::where('distributor_code', $this->distributorCodeFromFilename)
                    ->whereMonth('invoice_date', $dateObject->month)
                    ->whereYear('invoice_date', $dateObject->year);

                $recordCountToDelete = $query->count();
                $monthName = $dateObject->translatedFormat('F');
                $year = $dateObject->year;
                
                if ($recordCountToDelete > 0) {
                    $this->batch->addLog('info', "Mendeteksi {$recordCountToDelete} data lama untuk periode {$monthName} {$year}. Menghapus...");
                    
                    $deletedRows = $query->delete(); // Lakukan penghapusan
                    
                    $this->batch->addLog('success', "Berhasil menghapus {$deletedRows} data lama.");
                } else {
                    $this->batch->addLog('info', "Tidak ditemukan data lama untuk periode {$monthName} {$year}. Proses impor dilanjutkan.");
                }

                $this->hasDeletedExistingData = true;
            }
        }

        // [PERUBAHAN] Validasi branch code dengan pesan error yang lebih jelas
        $distributorCodeInCell = $getValue('distributor_code');

        // Pertama, periksa apakah selnya kosong
        if (empty($distributorCodeInCell)) {
            throw new Exception(
                "Kesalahan pada baris ke-{$rowIndex}: Kolom 'distributor_code' ditemukan kosong di dalam file Excel. Pastikan kolom ini terisi dan konfigurasinya benar."
            );
        }

        // Kedua, bandingkan dengan nama file
        if ($distributorCodeInCell !== $this->distributorCodeFromFilename) {
            throw new Exception(
                "Kesalahan pada baris ke-{$rowIndex}: Kode cabang di file ('{$distributorCodeInCell}') tidak cocok dengan nama file ('{$this->distributorCodeFromFilename}')."
            );
        }
        // Invoice type
        $netAmountValue = $getValue('net_amount');
        $netAmountFloat = $toFloat($netAmountValue);
        $invoiceType = $netAmountFloat >= 0 ? 'inv' : 'ret';

        // Logika bonus
        $netAmountValue = $getValue('net_amount');
        $finalBonusValue = 0;
        $isDiscount8Mapped = isset($this->config['discount8']) && $this->config['discount8']['index'] > 0;
        if ($isDiscount8Mapped) {
            $finalBonusValue = $getValue('discount8');
        } else {
            if ($toFloat($netAmountValue) == 0) {
                $finalBonusValue = $toFloat($getValue('carton_qty')) + $toFloat($getValue('pack_qty')) + $toFloat($getValue('pcs_qty')) + $toFloat($getValue('quantity'));
            }
        }
        $isBonusValue = $toFloat($getValue('discount8')) != 0 || $toFloat($netAmountValue) == 0;

        // Simpan data ke database
        SalesInvoiceDistributor::create([
            'distributor_code'  => $distributorCodeInCell,
            'invoice_type'      => $invoiceType,
            'invoice_no'        => $getValue('invoice_no'),
            'invoice_date'      => $this->transformDate($getValue('invoice_date')),
            'order_no'       => $getValue('order_no'),
            'order_date'     => $this->transformDate($getValue('order_date')),
            'salesman_code'  => $getValue('salesman_code'),
            'salesman_name'  => $getValue('salesman_name'),
            'customer_code'  => $getValue('customer_code'),
            'customer_name'  => $getValue('customer_name'),
            'address'        => $getValue('address'),
            'product_code'   => $getValue('product_code'),
            'product_name'   => $getValue('product_name'),
            'carton_qty'     => $toFloat($getValue('carton_qty')),
            'pack_qty'       => $toFloat($getValue('pack_qty')),
            'pcs_qty'        => $toFloat($getValue('pcs_qty')),
            'quantity'       => $toFloat($getValue('quantity')),
            'unit'           => trim((string) $getValue('unit')),
            'bonus'          => $toFloat($finalBonusValue), // Diisi dari hasil logika baru
            'is_bonus'       => $isBonusValue,   // Diisi dari hasil logika terpisah
            'unit_price'     => $toFloat($getValue('unit_price')),
            'gross_amount'   => $toFloat($getValue('gross_amount')),
            'discount1'      => $toFloat($getValue('discount1')),
            'discount2'      => $toFloat($getValue('discount2')),
            'discount3'      => $toFloat($getValue('discount3')),
            'discount4'      => $toFloat($getValue('discount4')),
            'discount5'      => $toFloat($getValue('discount5')),
            'discount6'      => $toFloat($getValue('discount6')),
            'discount7'      => $toFloat($getValue('discount7')),
            'discount8'      => $toFloat($finalBonusValue), // Diisi dari hasil logika baru
            'total_discount' => $toFloat($getValue('total_discount')),
            'dpp'            => $toFloat($getValue('dpp')),
            'tax'            => $toFloat($getValue('tax')),
            'net_amount'     => $toFloat($netAmountValue),
        ]);

        // Perbarui jumlah baris yang diproses
        $this->batch->increment('processed_rows');
    }

    /**
     * Memvalidasi header dari file Excel.
     */
    public function validateHeader(array $headerRow)
    {
        $errors = [];
        foreach ($this->config as $dbColumn => $columnConfig) {
            if (isset($columnConfig['index'], $columnConfig['header_inv_dist']) && $columnConfig['index'] > 0) {
                $index = $columnConfig['index'] - 1;
                $expectedHeader = $columnConfig['header_inv_dist'];
                $actualHeader = $headerRow[$index] ?? null;

                if ($actualHeader !== $expectedHeader) {
                    $errors[] = "Header tidak sesuai: Kolom ke-{$columnConfig['index']} seharusnya '{$expectedHeader}', tetapi di file terbaca '{$actualHeader}'.";
                }
            }
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
     * Mengubah berbagai format tanggal menjadi Y-m-d.
     */
    private function transformDate($value): ?string
    {
        if (empty($value)) return null;
        if (is_numeric($value)) {
            try { return Date::excelToDateTimeObject($value)->format('Y-m-d'); } catch (\Exception $e) {}
        }
        $formatsToTry = ['d-M-Y', 'd/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'Y/m/d', 'd.m.Y'];
        foreach ($formatsToTry as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date) return $date->format('Y-m-d');
            } catch (\Exception $e) { continue; }
        }
        return null;
    }
}

