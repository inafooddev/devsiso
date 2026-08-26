<?php

namespace App\Imports;

use App\Models\ImportBatch;
use App\Models\SellingInRaw;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Carbon\Carbon;

class SellingInRawImport implements OnEachRow
{
    protected $batch;
    protected $selectedMonth;
    protected $headerRowIndex = null;
    protected $columnIndexMap = [];
    protected $rowIndex = 0;
    protected $processedCount = 0;
    
    // Cache keys
    protected $cacheProgressKey;
    protected $cacheLogsKey;

    // Daftar header yang WAJIB ada di file Excel sesuai format
    protected $requiredHeaders = [
        'TANGGAL FAKTUR'   => 'invoice_date',
        'KODE'             => 'kode',
        'NO INVOICE'       => 'invoice_no',
        'JENIS PENJUALAN'  => 'jenis_penjualan',
        'DIVISI'           => 'divisi',
        'WILAYAH'          => 'wilayah',
        'KODE DISTRIBUTOR' => 'kode_distributor',
        'DISTRIBUTOR'      => 'distributor',
        'KODE BARANG'      => 'kode_barang',
        'NAMA BARANG'      => 'nama_barang',
        'QTY'              => 'qty',
        'SATUAN'           => 'satuan',
        'HARGA SATUAN'     => 'harga_satuan',
        'SUBTOTAL'         => 'subtotal',
        'QTY BONUS'        => 'qty_bonus',
        'NILAI BONUS'      => 'nilai_bonus',
        'DISKON 1'         => 'diskon_1',
        'DISKON 2'         => 'diskon_2',
        'DISKON 3'         => 'diskon_3',
        'DPP'              => 'dpp',
        'PPN'              => 'ppn',
        'TOTAL'            => 'total',
        'TOTAL IDR'        => 'total_idr',
    ];

    // Kolom-kolom yang TIDAK BOLEH NULL (Indexed columns)
    protected $notNullColumns = [
        'invoice_date'     => 'TANGGAL FAKTUR',
        'divisi'           => 'DIVISI',
        'wilayah'          => 'WILAYAH',
        'kode_distributor' => 'KODE DISTRIBUTOR',
        'distributor'      => 'DISTRIBUTOR',
        'kode_barang'      => 'KODE BARANG',
    ];

    public function __construct(ImportBatch $batch, $selectedMonth)
    {
        $this->batch = $batch;
        $this->selectedMonth = $selectedMonth;
        
        // Inisialisasi Cache Keys
        $this->cacheProgressKey = "import_batch_{$batch->id}_progress";
        $this->cacheLogsKey = "import_batch_{$batch->id}_logs";
    }

    public function onRow(Row $row)
    {
        $this->rowIndex = $row->getIndex();
        $rowData = $row->toArray();

        // 1. PENCARIAN HEADER DINAMIS
        if ($this->headerRowIndex === null) {
            $rowString = strtoupper(implode('||', array_map('trim', $rowData)));
            
            // Hitung berapa banyak kolom header yang cocok di baris ini
            $matchCount = 0;
            foreach ($this->requiredHeaders as $headerText => $dbColumn) {
                if (strpos($rowString, $headerText) !== false) {
                    $matchCount++;
                }
            }
            
            // Jika minimal ada 3 kolom header yang cocok, kita asumsikan ini adalah baris Header
            if ($matchCount >= 3) {
                $this->headerRowIndex = $this->rowIndex;
                
                // Buat mapping index untuk setiap sel di baris ini
                foreach ($rowData as $index => $cellValue) {
                    $cellUpper = strtoupper(trim((string)$cellValue));
                    if (isset($this->requiredHeaders[$cellUpper])) {
                        $dbColumn = $this->requiredHeaders[$cellUpper];
                        $this->columnIndexMap[$dbColumn] = $index;
                    }
                }

                // Cek secara spesifik kolom apa saja yang hilang dari format Excel
                $missingHeaders = [];
                foreach ($this->requiredHeaders as $headerText => $dbCol) {
                    if (!isset($this->columnIndexMap[$dbCol])) {
                        $missingHeaders[] = "'{$headerText}'";
                    }
                }
                
                // Jika ada yang hilang, langsung lemparkan error spesifik
                if (!empty($missingHeaders)) {
                    $missingList = implode(', ', $missingHeaders);
                    throw new \Exception("Baris {$this->rowIndex} terdeteksi sebagai Header, namun file Anda kekurangan kolom berikut: {$missingList}. Pastikan semua 23 kolom tersedia.");
                }
                
                // Tambahkan log bahwa header ditemukan
                $this->logInfo("Header lengkap ditemukan di baris {$this->headerRowIndex}. Mulai memproses data...");
                return; // Jangan proses baris header sebagai data
            }
            
            return; // Lewati baris sebelum header
        }

        // 2. PROSES DATA BARIS (Setelah Header Ditemukan)
        
        // Lewati baris kosong
        if (empty(array_filter($rowData))) {
            return;
        }

        // Ambil data berdasarkan mapping header
        $data = [];
        foreach ($this->columnIndexMap as $dbColumn => $index) {
            $data[$dbColumn] = isset($rowData[$index]) ? trim((string)$rowData[$index]) : null;
            if ($data[$dbColumn] === '') {
                $data[$dbColumn] = null;
            }
        }

        // 3. VALIDASI NOT NULL (Indexed Columns)
        foreach ($this->notNullColumns as $dbCol => $headerName) {
            if ($data[$dbCol] === null) {
                // Jangan error jika ternyata semua kolom utama kosong (asumsi baris sisa/sampah di excel)
                if ($this->isRowBasicallyEmpty($data)) {
                    return; 
                }
                throw new \Exception("Baris {$this->rowIndex}: Kolom '{$headerName}' tidak boleh kosong.");
            }
        }

        // 4. PARSING & VALIDASI TANGGAL (Pencocokan Periode)
        try {
            $parsedDate = $this->parseDate($data['invoice_date']);
            $data['invoice_date'] = $parsedDate;
            
            // Validasi apakah bulan di baris ini sesuai dengan inputan User (Opsi A: Tolak Keras)
            $rowMonthYear = Carbon::parse($parsedDate)->format('Y-m');
            if ($rowMonthYear !== $this->selectedMonth) {
                throw new \Exception("Tanggal Faktur ({$parsedDate}) tidak sesuai dengan periode yang dipilih ({$this->selectedMonth}).");
            }

        } catch (\Exception $e) {
            throw new \Exception("Baris {$this->rowIndex}: " . $e->getMessage());
        }

        // 5. PARSING NUMERIK
        $numericCols = ['qty', 'harga_satuan', 'subtotal', 'qty_bonus', 'nilai_bonus', 'diskon_1', 'diskon_2', 'diskon_3', 'dpp', 'ppn', 'total', 'total_idr'];
        foreach ($numericCols as $col) {
            if (isset($data[$col])) {
                $data[$col] = $this->parseNumeric($data[$col]);
            }
        }

        // 6. INSERT KE DATABASE
        SellingInRaw::create([
            'import_batch_id'  => $this->batch->id,
            'row_number'       => $this->rowIndex,
            'invoice_date'     => $data['invoice_date'],
            'kode'             => $data['kode'],
            'invoice_no'       => $data['invoice_no'],
            'jenis_penjualan'  => $data['jenis_penjualan'],
            'divisi'           => $data['divisi'],
            'wilayah'          => $data['wilayah'],
            'kode_distributor' => $data['kode_distributor'],
            'distributor'      => $data['distributor'],
            'kode_barang'      => $data['kode_barang'],
            'nama_barang'      => $data['nama_barang'],
            'qty'              => $data['qty'],
            'satuan'           => $data['satuan'],
            'harga_satuan'     => $data['harga_satuan'],
            'subtotal'         => $data['subtotal'],
            'qty_bonus'        => $data['qty_bonus'],
            'nilai_bonus'      => $data['nilai_bonus'],
            'diskon_1'         => $data['diskon_1'],
            'diskon_2'         => $data['diskon_2'],
            'diskon_3'         => $data['diskon_3'],
            'dpp'              => $data['dpp'],
            'ppn'              => $data['ppn'],
            'total'            => $data['total'],
            'total_idr'        => $data['total_idr'],
        ]);

        // 7. INCREMENT PROGRESS (Update ke Cache setiap 50 baris untuk efisiensi)
        $this->processedCount++;
        if ($this->processedCount % 50 === 0) {
            \Illuminate\Support\Facades\Cache::put($this->cacheProgressKey, $this->processedCount, 3600);
        }
    }

    /**
     * Memparsing tanggal dari format Excel Numerik maupun String
     */
    private function parseDate($value)
    {
        if (is_numeric($value)) {
            // Excel Serial Date
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        // Coba parsing Carbon multi-format
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            // Fallback terakhir, coba format d/m/Y atau d-m-Y spesifik
            $value = str_replace('/', '-', $value);
            return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
        }
    }

    /**
     * Parsing string angka dengan koma/titik menjadi desimal valid untuk database
     */
    private function parseNumeric($value)
    {
        if ($value === null || $value === '') return null;
        
        // Hapus pemisah ribuan (biasanya koma di format US atau titik di Indo)
        // Kita gunakan logika sederhana: simpan angka, minus, dan satu titik desimal.
        $value = str_replace(',', '', $value); // hapus koma
        if (is_numeric($value)) {
            return (float) $value;
        }
        return 0; // Fallback
    }

    /**
     * Cek apakah baris ini sebenarnya kosong (hanya tersisa karakter spasi dll di bbrp sel)
     */
    private function isRowBasicallyEmpty(array $data)
    {
        $hasData = false;
        foreach (['invoice_no', 'qty', 'kode', 'subtotal'] as $check) {
            if (!empty($data[$check])) {
                $hasData = true;
                break;
            }
        }
        return !$hasData;
    }

    private function logInfo($message)
    {
        $logs = \Illuminate\Support\Facades\Cache::get($this->cacheLogsKey, []);
        $logs[] = ['type' => 'info', 'message' => $message];
        \Illuminate\Support\Facades\Cache::put($this->cacheLogsKey, $logs, 3600);
    }
    
    /**
     * Helper untuk mengambil total baris yang sukses di proses saat class ini selesai berjalan
     */
    public function getProcessedCount()
    {
        return $this->processedCount;
    }

    /**
     * Helper untuk mengecek apakah header berhasil ditemukan
     */
    public function isHeaderFound()
    {
        return $this->headerRowIndex !== null;
    }
}
