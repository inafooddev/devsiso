<?php

namespace App\Livewire\SalesConfig;

use Livewire\Component;
use App\Models\ConfigSalesInvoiceDistributor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Encryption\DecryptException;

class Edit extends Component
{
    public ConfigSalesInvoiceDistributor $config_sale;
    public $distributor_code, $config_name;
    public $config = [];

    /**
     * Daftar header yang tersedia untuk konfigurasi.
     *
     * @var array
     */
    private $headers = [
        "distributor_code"   => "Kode Cabang",
        "invoice_no"    => "Nomor Invoice",
        "invoice_date"  => "Tanggal Invoice",
        "order_no"      => "Nomor Order",
        "order_date"    => "Tanggal Order",
        "salesman_code" => "Kode Salesman",
        "salesman_name" => "Nama Salesman",
        "customer_code" => "Kode Customer",
        "customer_name" => "Nama Customer",
        "address"       => "Alamat",
        "product_code"  => "Kode Produk",
        "product_name"  => "Nama Produk",
        "carton_qty"    => "Qty Karton",
        "pack_qty"      => "Qty Pack",
        "pcs_qty"       => "Qty PCS",
        "quantity"      => "Qty",
        "unit"          => "Satuan",
        "unit_price"    => "Harga Satuan",
        "gross_amount"  => "Gross",
        "discount1"     => "Diskon Dist 1",
        "discount2"     => "Diskon Dist 2",
        "discount3"     => "Diskon Dist 3",
        "discount4"     => "Diskon Prc 1 (Cashback)",
        "discount5"     => "Diskon Prc 2",
        "discount6"     => "Diskon Prc 3",
        "discount7"     => "Diskon Prc 4",
        "discount8"     => "Bonus Barang",
        "total_discount"=> "Total Diskon",
        "dpp"           => "DPP",
        "tax"           => "PPN",
        "net_amount"    => "Net Amount",
    ];

    /**
     * Daftar field yang wajib dipetakan secara lengkap.
     */
    private $requiredMappingFields = [
        'distributor_code', 'invoice_no', 'invoice_date', 'salesman_code', 'salesman_name',
        'customer_code', 'customer_name', 'product_code', 'product_name', 'net_amount'
    ];

    /**
     * Menginisialisasi state komponen dengan mendekripsi ID dari URL.
     *
     * @param string $encodedId
     */
    public function mount($encodedId)
    {
        try {
            // PERUBAHAN: Mendekode ID dari base64
            $id = base64_decode($encodedId);
            $config_sale = ConfigSalesInvoiceDistributor::findOrFail($id);
        } catch (\Exception $e) {
            // Jika ID tidak valid atau gagal didekode, tampilkan halaman not found
            abort(404);
        }
        
        $this->config_sale = $config_sale;
        $this->distributor_code = $config_sale->distributor_code;
        $this->config_name = $config_sale->config_name;
        
        $decodedConfig = json_decode($config_sale->config, true) ?? [];

        // Inisialisasi form dengan semua header, lalu isi dengan data yang ada
        $this->initializeConfig();
        foreach ($decodedConfig as $dbField => $values) {
            if (isset($this->config[$dbField])) {
                $this->config[$dbField] = $values;
            }
        }
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
     * Aturan validasi untuk form edit.
     */
    protected function rules()
    {
        $rules = [
            'distributor_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('config_sales_invoice_distributor')->ignore($this->config_sale->id),
            ],
            'config_name' => 'required|string|max:255',
        ];

        // Membedakan aturan untuk field wajib dan opsional
        foreach ($this->headers as $field => $alias) {
            if (in_array($field, $this->requiredMappingFields)) {
                // Untuk field wajib, kedua bagian (nama kolom dan nomor urut) HARUS diisi
                $rules["config.{$field}.header_inv_dist"] = "required|string";
                $rules["config.{$field}.index"] = "required|integer|min:0";
            } else {
                // Untuk field opsional, jika salah satu diisi, pasangannya menjadi wajib
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
    
    /**
     * Memperbarui data konfigurasi di database.
     */
    public function update()
    {
        // Menjalankan semua aturan validasi, termasuk untuk field yang wajib diisi
        $this->validate();

        // Menjalankan validasi kustom tambahan setelah validasi dasar lolos
        Validator::make($this->all(), [])
            ->after(function ($validator) {
                $configData = $this->config;

                $qtyFields = ['carton_qty', 'pack_qty', 'pcs_qty', 'quantity'];
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
        
        $filteredConfig = [];
        foreach ($this->config as $dbField => $values) {
            if (!empty($values['header_inv_dist']) || ($values['index'] !== '' && $values['index'] !== null)) {
                $filteredConfig[$dbField] = [
                    'header_inv_dist' => $values['header_inv_dist'],
                    'index' => (int)($values['index'] ?? 0)
                ];
            }
        }
        
        $this->config_sale->update([
            'distributor_code' => $this->distributor_code,
            'config_name' => $this->config_name,
            'config' => json_encode($filteredConfig, JSON_PRETTY_PRINT),
        ]);

        session()->flash('message', 'Konfigurasi berhasil diperbarui!');

        // Redirect ke halaman index setelah berhasil memperbarui
        return redirect()->route('sales-configs.index');
    }

    /**
     * Merender view untuk form edit konfigurasi.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.sales-config.edit', [
            'staticHeaders' => $this->headers
        ])->layout('layouts.app'); // Menambahkan layout untuk menghindari error
    }
}

