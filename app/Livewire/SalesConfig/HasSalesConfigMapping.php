<?php

namespace App\Livewire\SalesConfig;

use App\Models\ConfigSalesInvoiceDistributor;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Validator;

trait HasSalesConfigMapping
{
    use WithFileUploads;

    public $config = [];
    public $activeTab = 'all'; // 'all', 'transactions', 'customer_salesman', 'products_qty', 'financial'
    public $showOnlyFilled = false;
    public $searchColumn = '';

    // State untuk Modal Salin/Copy dari Distributor Lain
    public $isCopyModalOpen = false;
    public $selectedSourceConfigId = '';
    public $copySearch = '';

    // State untuk Deteksi Sample File Excel
    public $sampleFile;
    public $detectedHeaders = []; // [1 => 'Nama Header Kolom 1', 2 => '...']
    public $sampleRowData = [];   // [1 => 'Contoh Data Baris 2', 2 => '...']
    public $sampleFileName = '';

    /**
     * Daftar 31 header standar sistem
     */
    public array $headers = [
        "distributor_code"  => "Kode Distributor",
        "invoice_no"        => "Nomor Invoice",
        "invoice_date"      => "Tanggal Invoice",
        "order_no"          => "Nomor Order",
        "order_date"        => "Tanggal Order",
        "salesman_code"     => "Kode Salesman",
        "salesman_name"     => "Nama Salesman",
        "customer_code"     => "Kode Customer",
        "customer_name"     => "Nama Customer",
        "address"           => "Alamat",
        "product_code"      => "Kode Produk",
        "product_name"      => "Nama Produk",
        "carton_qty"        => "Qty Karton",
        "pack_qty"          => "Qty Pack",
        "pcs_qty"           => "Qty PCS",
        "quantity"          => "Qty",
        "unit"              => "Satuan",
        "unit_price"        => "Harga Satuan",
        "gross_amount"      => "Gross",
        "discount1"         => "Diskon Dist 1",
        "discount2"         => "Diskon Dist 2",
        "discount3"         => "Diskon Dist 3",
        "discount4"         => "Diskon Prc 1 (Cashback)",
        "discount5"         => "Diskon Prc 2",
        "discount6"         => "Diskon Prc 3",
        "discount7"         => "Diskon Prc 4",
        "discount8"         => "Bonus Barang",
        "total_discount"    => "Total Diskon",
        "dpp"               => "DPP",
        "tax"               => "PPN",
        "net_amount"        => "Net Amount",
    ];

    /**
     * Daftar 10 field wajib
     */
    public array $requiredMappingFields = [
        'distributor_code', 'invoice_no', 'invoice_date', 'salesman_code', 'salesman_name',
        'customer_code', 'customer_name', 'product_code', 'product_name', 'net_amount'
    ];

    /**
     * Daftar field kuantitas (minimal salah satu harus terisi)
     */
    public array $qtyFields = ['carton_qty', 'pack_qty', 'pcs_qty', 'quantity'];

    /**
     * Pengelompokan field per kategori untuk tab & navigasi
     */
    public array $categories = [
        'transactions' => [
            'key' => 'transactions',
            'title' => 'Transaksi & Dokumen',
            'description' => 'Nomor invoice, tanggal transaksi, & nomor order',
            'icon' => 'document-text',
            'fields' => ['distributor_code', 'invoice_no', 'invoice_date', 'order_no', 'order_date'],
        ],
        'customer_salesman' => [
            'key' => 'customer_salesman',
            'title' => 'Pelanggan & Salesman',
            'description' => 'Identitas customer, salesman toko, & alamat',
            'icon' => 'user-group',
            'fields' => ['salesman_code', 'salesman_name', 'customer_code', 'customer_name', 'address'],
        ],
        'products_qty' => [
            'key' => 'products_qty',
            'title' => 'Produk & Kuantitas',
            'description' => 'Kode barang, nama barang, dan satuan kuantitas (Karton/Pack/Pcs/Qty)',
            'icon' => 'cube',
            'fields' => ['product_code', 'product_name', 'carton_qty', 'pack_qty', 'pcs_qty', 'quantity', 'unit'],
        ],
        'financial' => [
            'key' => 'financial',
            'title' => 'Harga, Diskon & Nilai',
            'description' => 'Harga satuan, gross, diskon 1-8, DPP, PPN, & net amount',
            'icon' => 'banknotes',
            'fields' => [
                'unit_price', 'gross_amount',
                'discount1', 'discount2', 'discount3', 'discount4',
                'discount5', 'discount6', 'discount7', 'discount8',
                'total_discount', 'dpp', 'tax', 'net_amount',
            ],
        ],
    ];

    /**
     * Inisialisasi awal seluruh baris konfigurasi
     */
    public function initializeConfig(): void
    {
        $this->config = [];
        foreach ($this->headers as $dbField => $label) {
            $this->config[$dbField] = [
                'header_inv_dist' => '',
                'index' => ''
            ];
        }
    }

    /**
     * Konversi string input (bisa huruf kolom Excel seperti 'A', 'C', 'AA' atau angka) menjadi integer
     */
    public function parseColumnIndex($val)
    {
        if ($val === null || $val === '') return '';
        if (is_numeric($val)) {
            $num = (int)$val;
            return $num > 0 ? $num : '';
        }
        $val = strtoupper(trim((string)$val));
        if (preg_match('/^[A-Z]+$/', $val)) {
            try {
                return Coordinate::columnIndexFromString($val);
            } catch (\Exception $e) {
                return '';
            }
        }
        return '';
    }

    /**
     * Ambil huruf kolom Excel dari angka index (1 => A, 27 => AA)
     */
    public function getColumnLetter($index): string
    {
        if (empty($index) || !is_numeric($index) || (int)$index <= 0) return '';
        try {
            return Coordinate::stringFromColumnIndex((int)$index);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Hook Livewire ketika properti diubah
     */
    public function updated($propertyName)
    {
        // Otomatis konversi jika user mengetik huruf di kolom index
        if (preg_match('/^config\.([a-zA-Z0-9_]+)\.index$/', $propertyName, $matches)) {
            $field = $matches[1];
            $val = $this->config[$field]['index'] ?? '';
            $num = $this->parseColumnIndex($val);
            if ($num !== $val) {
                $this->config[$field]['index'] = $num;
            }
        }

        $this->validateOnly($propertyName);
    }

    /**
     * Bersihkan mapping untuk satu field tertentu
     */
    public function clearField(string $field): void
    {
        if (isset($this->config[$field])) {
            $this->config[$field] = [
                'header_inv_dist' => '',
                'index' => ''
            ];
        }
    }

    /**
     * Kosongkan seluruh mapping kolom
     */
    public function clearAllFields(): void
    {
        $this->initializeConfig();
        session()->flash('info_message', 'Semua pemetaan kolom telah dikosongkan.');
    }

    // =========================================================================
    // FITUR COPY DARI CONFIG DISTRIBUTOR LAIN
    // =========================================================================

    public function openCopyModal(): void
    {
        $this->isCopyModalOpen = true;
        $this->selectedSourceConfigId = '';
        $this->copySearch = '';
    }

    public function closeCopyModal(): void
    {
        $this->isCopyModalOpen = false;
        $this->selectedSourceConfigId = '';
    }

    public function applyCopyConfig(): void
    {
        if (empty($this->selectedSourceConfigId)) {
            $this->addError('selectedSourceConfigId', 'Silakan pilih distributor sumber terlebih dahulu.');
            return;
        }

        $source = ConfigSalesInvoiceDistributor::find($this->selectedSourceConfigId);
        if (!$source) {
            $this->addError('selectedSourceConfigId', 'Konfigurasi sumber tidak ditemukan.');
            return;
        }

        $sourceConfig = is_array($source->config) ? $source->config : json_decode($source->config, true);
        if (!is_array($sourceConfig)) {
            $this->addError('selectedSourceConfigId', 'Format konfigurasi distributor sumber tidak valid.');
            return;
        }

        $copiedCount = 0;
        foreach ($this->headers as $field => $label) {
            if (isset($sourceConfig[$field])) {
                $header = $sourceConfig[$field]['header_inv_dist'] ?? '';
                $idx = $sourceConfig[$field]['index'] ?? '';
                $this->config[$field] = [
                    'header_inv_dist' => $header,
                    'index' => $idx
                ];
                if (!empty($header) && $idx !== '' && $idx !== null) {
                    $copiedCount++;
                }
            } else {
                $this->config[$field] = [
                    'header_inv_dist' => '',
                    'index' => ''
                ];
            }
        }

        $this->isCopyModalOpen = false;
        $this->selectedSourceConfigId = '';
        session()->flash('copy_success', "Berhasil menyalin {$copiedCount} pemetaan kolom dari {$source->config_name} ({$source->distributor_code})!");
    }

    /**
     * Daftar pilihan distributor sumber untuk disalin
     */
    public function getSourceConfigsProperty()
    {
        $query = ConfigSalesInvoiceDistributor::query();
        
        // Pada form edit, kecualikan diri sendiri
        if (isset($this->config_sale) && $this->config_sale->id) {
            $query->where('id', '!=', $this->config_sale->id);
        }

        if (strlen($this->copySearch) >= 2) {
            $query->where(function($q) {
                $q->where('config_name', 'ilike', '%' . $this->copySearch . '%')
                  ->orWhere('distributor_code', 'ilike', '%' . $this->copySearch . '%');
            });
        }

        return $query->orderBy('config_name')->take(30)->get();
    }

    // =========================================================================
    // FITUR SAMPLE FILE DETECTOR & AUTO-MATCH
    // =========================================================================

    public function updatedSampleFile(): void
    {
        $this->validate([
            'sampleFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'sampleFile.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV (.csv).',
            'sampleFile.max' => 'Ukuran file sample maksimal 10 MB.',
        ]);

        try {
            $path = $this->sampleFile->getRealPath();
            $this->sampleFileName = $this->sampleFile->getClientOriginalName();

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $highestColumn = $sheet->getHighestColumn();

            // Membaca baris pertama (header) dan baris kedua (contoh data isian)
            $rows = $sheet->rangeToArray('A1:' . $highestColumn . '2', null, true, true, false);
            $headerValues = $rows[0] ?? [];
            $sampleRowValues = $rows[1] ?? [];

            $this->detectedHeaders = [];
            $this->sampleRowData = [];

            foreach ($headerValues as $idxZero => $cellValue) {
                $colIndex = $idxZero + 1; // 1-based index
                $headerText = trim((string)$cellValue);
                if ($headerText !== '') {
                    $this->detectedHeaders[$colIndex] = $headerText;
                    $sampleVal = isset($sampleRowValues[$idxZero]) ? trim((string)$sampleRowValues[$idxZero]) : '';
                    $this->sampleRowData[$colIndex] = $sampleVal;
                }
            }

            if (empty($this->detectedHeaders)) {
                session()->flash('sample_error', 'Tidak ditemukan teks header pada baris pertama file ini.');
                return;
            }

            session()->flash('sample_success', 'Terdeteksi ' . count($this->detectedHeaders) . ' kolom dan contoh data isian dari ' . $this->sampleFileName . '. Anda dapat memilih kolom dari dropdown atau klik "Auto-Match Kolom".');
        } catch (\Exception $e) {
            session()->flash('sample_error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    /**
     * Memilih salah satu header yang terdeteksi dari file sample untuk field tertentu
     */
    public function selectDetectedHeader(string $field, $colIndex): void
    {
        if (empty($colIndex)) return;
        $colIndex = (int)$colIndex;
        if (isset($this->detectedHeaders[$colIndex])) {
            $this->config[$field]['header_inv_dist'] = $this->detectedHeaders[$colIndex];
            $this->config[$field]['index'] = $colIndex;
        }
    }

    /**
     * Pencocokan cerdas otomatis antara kolom file sample dan field database
     */
    public function autoMatchFromSample(): void
    {
        if (empty($this->detectedHeaders)) {
            session()->flash('sample_error', 'Silakan upload file sample terlebih dahulu.');
            return;
        }

        $aliases = [
            'distributor_code' => ['kode cabang', 'kode dist', 'kd cabang', 'distributor code', 'branch code', 'dist code'],
            'invoice_no'       => ['nomor invoice', 'no invoice', 'no nota', 'nomor nota', 'no faktur', 'nomor faktur', 'inv no'],
            'invoice_date'     => ['tanggal invoice', 'tgl invoice', 'tanggal nota', 'tgl nota', 'tanggal faktur', 'tgl faktur', 'inv date', 'date', 'tanggal'],
            'order_no'         => ['nomor order', 'no order', 'no po', 'nomor po', 'order no'],
            'order_date'       => ['tanggal order', 'tgl order', 'tanggal po', 'tgl po', 'order date'],
            'salesman_code'    => ['kode salesman', 'kd salesman', 'kode sales', 'kd sales', 'sales code'],
            'salesman_name'    => ['nama salesman', 'nama sales', 'sales name', 'salesman'],
            'customer_code'    => ['kode customer', 'kd customer', 'kode pelanggan', 'kd pelanggan', 'kode toko', 'kd toko', 'cust code'],
            'customer_name'    => ['nama customer', 'nama pelanggan', 'nama toko', 'customer name', 'cust name'],
            'address'          => ['alamat', 'address', 'alamat customer', 'alamat toko'],
            'product_code'     => ['kode produk', 'kd produk', 'kode barang', 'kd barang', 'item code', 'product code', 'sku'],
            'product_name'     => ['nama produk', 'nama barang', 'product name', 'item name', 'deskripsi'],
            'carton_qty'       => ['qty karton', 'carton qty', 'ctn qty', 'karton', 'sat-1', 'satuan 1'],
            'pack_qty'         => ['qty pack', 'pack qty', 'pack', 'sat-2', 'satuan 2'],
            'pcs_qty'          => ['qty pcs', 'pcs qty', 'pcs', 'sat-3', 'satuan 3'],
            'quantity'         => ['quantity', 'qty', 'jumlah'],
            'unit'             => ['satuan', 'unit', 'uom'],
            'unit_price'       => ['harga satuan', 'unit price', 'harga / sat', 'harga', 'price'],
            'gross_amount'     => ['gross', 'gross amount', 'bruto', 'jumlah kotor', 'sub total'],
            'discount1'        => ['diskon dist 1', 'disc 1', 'discount 1', 'discount (1)'],
            'discount2'        => ['diskon dist 2', 'disc 2', 'discount 2', 'discount (2)'],
            'discount3'        => ['diskon dist 3', 'disc 3', 'discount 3', 'discount (3)'],
            'discount4'        => ['diskon prc 1', 'cashback', 'disc 4', 'discount 4', 'discount (4)'],
            'discount8'        => ['bonus barang', 'bonus', 'bns'],
            'total_discount'   => ['total diskon', 'total discount', 'tot disc', 'tot discount'],
            'dpp'              => ['dpp', 'netto :', 'dasar pengenaan pajak'],
            'tax'              => ['ppn', 'ppn :', 'tax', 'pajak'],
            'net_amount'       => ['net amount', 'netto + ppn', 'grand total', 'total net', 'net', 'total'],
        ];

        $matchedCount = 0;
        foreach ($this->headers as $field => $label) {
            $fieldKeywords = array_merge([strtolower($label), strtolower(str_replace('_', ' ', $field))], $aliases[$field] ?? []);
            
            foreach ($this->detectedHeaders as $colIdx => $colName) {
                $normalizedCol = strtolower(trim($colName));
                foreach ($fieldKeywords as $keyword) {
                    if ($normalizedCol === $keyword || str_contains($normalizedCol, $keyword)) {
                        $this->config[$field]['header_inv_dist'] = $colName;
                        $this->config[$field]['index'] = $colIdx;
                        $matchedCount++;
                        break 2;
                    }
                }
            }
        }

        session()->flash('sample_success', "Auto-Match berhasil memetakan {$matchedCount} kolom dari file sample!");
    }

    public function removeSampleFile(): void
    {
        $this->sampleFile = null;
        $this->detectedHeaders = [];
        $this->sampleRowData = [];
        $this->sampleFileName = '';
    }

    // =========================================================================
    // PROGRESS & VALIDATION HELPERS
    // =========================================================================

    public function getProgressStatsProperty(): array
    {
        $configData = $this->config;
        $requiredTotal = count($this->requiredMappingFields);
        $requiredCompleted = 0;
        $missingRequiredLabels = [];

        foreach ($this->requiredMappingFields as $field) {
            $hasHeader = !empty($configData[$field]['header_inv_dist'] ?? '');
            $hasIndex = isset($configData[$field]['index']) && $configData[$field]['index'] !== '' && $configData[$field]['index'] !== null;
            if ($hasHeader && $hasIndex) {
                $requiredCompleted++;
            } else {
                $missingRequiredLabels[] = $this->headers[$field] ?? $field;
            }
        }

        $isAnyQtyComplete = collect($this->qtyFields)->some(function ($field) use ($configData) {
            return !empty($configData[$field]['header_inv_dist'] ?? '') && 
                   isset($configData[$field]['index']) && 
                   $configData[$field]['index'] !== '' && 
                   $configData[$field]['index'] !== null;
        });

        $quantityStarted = !empty($configData['quantity']['header_inv_dist'] ?? '') || 
                           (isset($configData['quantity']['index']) && $configData['quantity']['index'] !== '' && $configData['quantity']['index'] !== null);
        $unitComplete = !empty($configData['unit']['header_inv_dist'] ?? '') && 
                        (isset($configData['unit']['index']) && $configData['unit']['index'] !== '' && $configData['unit']['index'] !== null);
        
        $isUnitValid = !$quantityStarted || $unitComplete;
        $isAllValid = ($requiredCompleted === $requiredTotal) && $isAnyQtyComplete && $isUnitValid;
        
        $stepsTotal = $requiredTotal + 1; // 10 field wajib + 1 aturan Qty
        $stepsCompleted = $requiredCompleted + ($isAnyQtyComplete ? 1 : 0);
        $percent = round(($stepsCompleted / $stepsTotal) * 100);

        return [
            'required_total' => $requiredTotal,
            'required_completed' => $requiredCompleted,
            'is_qty_valid' => $isAnyQtyComplete,
            'is_unit_valid' => $isUnitValid,
            'is_all_valid' => $isAllValid,
            'percent' => $percent,
            'missing_labels' => $missingRequiredLabels,
        ];
    }

    public function getCategoryStatsProperty(): array
    {
        $stats = [];
        foreach ($this->categories as $catKey => $cat) {
            $filledCount = 0;
            $totalFields = count($cat['fields']);
            $hasMissingRequired = false;

            foreach ($cat['fields'] as $field) {
                $hasHeader = !empty($this->config[$field]['header_inv_dist'] ?? '');
                $hasIndex = isset($this->config[$field]['index']) && $this->config[$field]['index'] !== '' && $this->config[$field]['index'] !== null;
                if ($hasHeader && $hasIndex) {
                    $filledCount++;
                } elseif (in_array($field, $this->requiredMappingFields)) {
                    $hasMissingRequired = true;
                }
            }

            if ($catKey === 'products_qty') {
                $hasQty = collect($this->qtyFields)->some(function($f) {
                    return !empty($this->config[$f]['header_inv_dist'] ?? '') && 
                           isset($this->config[$f]['index']) && 
                           $this->config[$f]['index'] !== '' && 
                           $this->config[$f]['index'] !== null;
                });
                if (!$hasQty) {
                    $hasMissingRequired = true;
                }
            }

            $stats[$catKey] = [
                'filled' => $filledCount,
                'total' => $totalFields,
                'is_complete' => !$hasMissingRequired,
            ];
        }
        return $stats;
    }

    public function getFilteredFieldsProperty(): array
    {
        $activeFields = [];
        if ($this->activeTab === 'all') {
            $activeFields = array_keys($this->headers);
        } else {
            $activeFields = $this->categories[$this->activeTab]['fields'] ?? [];
        }

        $filtered = [];
        foreach ($activeFields as $field) {
            $alias = $this->headers[$field] ?? $field;
            $headerVal = $this->config[$field]['header_inv_dist'] ?? '';
            $indexVal = $this->config[$field]['index'] ?? '';
            $isFilled = !empty($headerVal) || ($indexVal !== '' && $indexVal !== null);

            if ($this->showOnlyFilled && !$isFilled) {
                continue;
            }

            if (!empty($this->searchColumn)) {
                $q = strtolower($this->searchColumn);
                $matchAlias = str_contains(strtolower($alias), $q);
                $matchField = str_contains(strtolower($field), $q);
                $matchHeader = str_contains(strtolower($headerVal), $q);
                if (!$matchAlias && !$matchField && !$matchHeader) {
                    continue;
                }
            }

            $filtered[$field] = $alias;
        }

        return $filtered;
    }

    /**
     * Aturan validasi pemetaan kolom
     */
    protected function getMappingRules(): array
    {
        $rules = [];
        foreach ($this->headers as $field => $alias) {
            if (in_array($field, $this->requiredMappingFields)) {
                $rules["config.{$field}.header_inv_dist"] = "required|string";
                $rules["config.{$field}.index"] = "required|integer|min:1";
            } else {
                $rules["config.{$field}.header_inv_dist"] = "required_with:config.{$field}.index|nullable|string";
                $rules["config.{$field}.index"] = "required_with:config.{$field}.header_inv_dist|nullable|integer|min:1";
            }
        }
        return $rules;
    }

    /**
     * Validasi aturan bisnis tambahan (syarat minimal 1 Qty dan Unit)
     */
    protected function validateBusinessRules(): void
    {
        Validator::make($this->all(), [])
            ->after(function ($validator) {
                $configData = $this->config;

                $qtyFields = $this->qtyFields;
                $isAnyQtyComplete = collect($qtyFields)->some(function ($field) use ($configData) {
                    return !empty($configData[$field]['header_inv_dist']) && $configData[$field]['index'] !== '' && $configData[$field]['index'] !== null;
                });

                if (!$isAnyQtyComplete) {
                    $validator->errors()->add(
                        'config.carton_qty.header_inv_dist',
                        'Minimal salah satu dari pemetaan kolom Qty (Karton, Pack, Pcs, atau Quantity) harus diisi lengkap.'
                    );
                }

                $quantityStarted = !empty($configData['quantity']['header_inv_dist']) || ($configData['quantity']['index'] !== '' && $configData['quantity']['index'] !== null);
                $unitComplete = !empty($configData['unit']['header_inv_dist']) && ($configData['unit']['index'] !== '' && $configData['unit']['index'] !== null);

                if ($quantityStarted && !$unitComplete) {
                    $validator->errors()->add(
                        'config.unit.header_inv_dist',
                        "Jika kolom 'Quantity' dipetakan, maka kolom 'Satuan (Unit)' juga wajib dipetakan secara lengkap."
                    );
                }
            })->validate();
    }

    /**
     * Format data config untuk disimpan ke database
     */
    protected function getFilteredConfigToSave(): array
    {
        $filteredConfig = [];
        foreach ($this->config as $dbField => $values) {
            if (!empty($values['header_inv_dist']) || ($values['index'] !== '' && $values['index'] !== null)) {
                $filteredConfig[$dbField] = [
                    'header_inv_dist' => trim((string)$values['header_inv_dist']),
                    'index' => (int)($values['index'] ?? 0)
                ];
            }
        }
        return $filteredConfig;
    }
}
