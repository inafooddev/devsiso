<?php

namespace App\Livewire\SalesConfig;

use Livewire\Component;
use App\Models\ConfigSalesInvoiceDistributor;
use App\Models\MasterDistributor; // [DITAMBAHKAN]
use Illuminate\Support\Facades\Validator;

class Create extends Component
{
    // [DIUBAH] Hanya properti distributor_code yang diperlukan untuk input
    public $distributor_code = '';
    public $config = [];

    // [DITAMBAHKAN] Properti untuk fungsionalitas pencarian
    public $distributorSearch = '';
    public $selectedDistributorName = '';


    /**
     * Daftar header yang tersedia untuk konfigurasi.
     */
    private $headers = [
        "distributor_code"  => "Kode Distributor", // Label diubah untuk konsistensi
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
     * Daftar field yang wajib dipetakan secara lengkap.
     */
    private $requiredMappingFields = [
        'distributor_code', 'invoice_no', 'invoice_date', 'salesman_code', 'salesman_name',
        'customer_code', 'customer_name', 'product_code', 'product_name', 'net_amount'
    ];

    /**
     * Menginisialisasi state komponen saat pertama kali dimuat.
     */
    public function mount()
    {
        $this->initializeConfig();
    }

    /**
     * Menginisialisasi array config dengan semua header yang tersedia.
     */
    private function initializeConfig()
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
     * Aturan validasi yang sekarang mencakup field yang wajib diisi.
     */
    protected function rules()
    {
        $rules = [
            // [DIUBAH] Validasi sekarang hanya untuk distributor_code
            'distributor_code' => 'required|string|max:255|unique:config_sales_invoice_distributor,distributor_code',
        ];

        // [TETAP SAMA] Logika validasi untuk pemetaan kolom dipertahankan
        foreach ($this->headers as $field => $alias) {
            if (in_array($field, $this->requiredMappingFields)) {
                $rules["config.{$field}.header_inv_dist"] = "required|string";
                $rules["config.{$field}.index"] = "required|integer|min:0";
            } else {
                $rules["config.{$field}.header_inv_dist"] = "required_with:config.{$field}.index|nullable|string";
                $rules["config.{$field}.index"] = "required_with:config.{$field}.header_inv_dist|nullable|integer|min:0";
            }
        }
        
        return $rules;
    }

    /**
     * Hook 'updated' untuk memicu validasi real-time.
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // [DITAMBAHKAN] Metode untuk memilih distributor dari hasil pencarian
    public function selectDistributor($code, $name)
    {
        $this->distributor_code = $code;
        $this->selectedDistributorName = $name;
        $this->distributorSearch = ''; // Kosongkan pencarian setelah memilih
    }

    // [DITAMBAHKAN] Hook untuk mereset nama saat pilihan dikosongkan
    public function updatedDistributorCode($value)
    {
        if (empty($value)) {
            $this->selectedDistributorName = '';
        }
    }

    /**
     * Menyimpan data konfigurasi baru ke database dengan validasi kustom.
     */
    public function save()
    {
        // Menjalankan semua aturan validasi dari metode rules()
        $this->validate();
        
        // [DITAMBAHKAN] Ambil data distributor yang dipilih
        $selectedDistributor = MasterDistributor::find($this->distributor_code);
        if (!$selectedDistributor) {
            $this->addError('distributor_code', 'Distributor yang dipilih tidak valid.');
            return;
        }

        // [TETAP SAMA] Validasi kustom tambahan dipertahankan
        Validator::make($this->all(), [])
            ->after(function ($validator) {
                $configData = $this->config;

                $qtyFields = ['carton_qty', 'pack_qty', 'pcs_qty', 'quantity'];
                $isAnyQtyComplete = collect($qtyFields)->some(function ($field) use ($configData) {
                    return !empty($configData[$field]['header_inv_dist']) && $configData[$field]['index'] !== '' && $configData[$field]['index'] !== null;
                });

                if (!$isAnyQtyComplete) {
                    $validator->errors()->add('config.carton_qty.header_inv_dist', 'Minimal salah satu dari pemetaan kolom Qty (Karton, Pack, Pcs, atau Quantity) harus diisi lengkap.');
                }

                $quantityStarted = !empty($configData['quantity']['header_inv_dist']) || ($configData['quantity']['index'] !== '' && $configData['quantity']['index'] !== null);
                $unitComplete = !empty($configData['unit']['header_inv_dist']) && ($configData['unit']['index'] !== '' && $configData['unit']['index'] !== null);

                if ($quantityStarted && !$unitComplete) {
                    $validator->errors()->add('config.unit.header_inv_dist', "Jika kolom 'Quantity' dipetakan, maka kolom 'Satuan (Unit)' juga wajib dipetakan secara lengkap.");
                }
            })->validate();

        $filteredConfig = [];
        foreach ($this->config as $dbField => $values) {
            if (!empty($values['header_inv_dist']) || ($values['index'] !== '' && $values['index'] !== null)) {
                $filteredConfig[$dbField] = [
                    'header_inv_dist' => $values['header_inv_dist'],
                    'index' => (int)($values['index'] ?? 0)
                ];
            }
        }
        
        ConfigSalesInvoiceDistributor::create([
            // [DIUBAH] Gunakan data dari distributor yang dipilih
            'distributor_code' => $selectedDistributor->distributor_code,
            'config_name' => $selectedDistributor->distributor_name,
            'config' => json_encode($filteredConfig, JSON_PRETTY_PRINT),
        ]);

        session()->flash('message', 'Konfigurasi berhasil disimpan!');
        return redirect()->route('sales-configs.index');
    }

    /**
     * Merender view untuk form pembuatan konfigurasi.
     */
    public function render()
    {
        // [DIPERBARUI] Logika untuk mengambil dan mencari distributor
        $configuredDistributors = ConfigSalesInvoiceDistributor::pluck('distributor_code');
        
        $distributorsQuery = MasterDistributor::whereNotIn('distributor_code', $configuredDistributors)
            ->orderBy('distributor_name');

        if (strlen($this->distributorSearch) >= 2) {
            $distributorsQuery->where(function ($query) {
                $query->where('distributor_name', 'ilike', '%' . $this->distributorSearch . '%')
                      ->orWhere('distributor_code', 'ilike', '%' . $this->distributorSearch . '%');
            });
        }

        $distributors = $distributorsQuery->take(50)->get();

        return view('livewire.sales-config.create', [
            'staticHeaders' => $this->headers,
            'distributors' => $distributors, // Kirim data ke view
        ])->layout('layouts.app');
    }
}

