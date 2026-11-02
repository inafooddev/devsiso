<?php

namespace App\Livewire\SalesConfig;

use Livewire\Component;
use App\Models\ConfigSalesInvoiceDistributor;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;

class Edit extends Component
{
    use EnforcesMenuPermissions, HasSalesConfigMapping;

    protected string $menuRoute = 'sales-configs.index';

    public ConfigSalesInvoiceDistributor $config_sale;
    public $distributor_code;
    public $config_name;

    /**
     * Inisialisasi state komponen dengan mendekripsi ID dari URL.
     *
     * @param string $encodedId
     */
    public function mount($encodedId)
    {
        try {
            $id = base64_decode($encodedId);
            $config_sale = ConfigSalesInvoiceDistributor::findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
        
        $this->config_sale = $config_sale;
        $this->distributor_code = $config_sale->distributor_code;
        $this->config_name = $config_sale->config_name;
        
        $decodedConfig = is_array($config_sale->config) 
            ? $config_sale->config 
            : (json_decode($config_sale->config, true) ?? []);

        // Inisialisasi form dengan semua header, lalu isi dengan data yang ada
        $this->initializeConfig();
        foreach ($decodedConfig as $dbField => $values) {
            if (isset($this->config[$dbField])) {
                $this->config[$dbField] = [
                    'header_inv_dist' => $values['header_inv_dist'] ?? '',
                    'index' => $values['index'] ?? ''
                ];
            }
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

        return array_merge($rules, $this->getMappingRules());
    }

    /**
     * Memperbarui data konfigurasi di database.
     */
    public function update()
    {
        $this->validate();
        $this->validateBusinessRules();

        $filteredConfig = $this->getFilteredConfigToSave();
        
        $this->config_sale->update([
            'distributor_code' => $this->distributor_code,
            'config_name' => $this->config_name,
            'config' => json_encode($filteredConfig, JSON_PRETTY_PRINT),
        ]);

        \App\Helpers\ActivityLogger::log('Update Sales Config', "Memperbarui konfigurasi sales invoice untuk distributor: {$this->distributor_code} - {$this->config_name}");

        session()->flash('message', 'Konfigurasi berhasil diperbarui!');

        return redirect()->route('sales-configs.index');
    }

    /**
     * Merender view untuk form edit konfigurasi.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.sell-out.sales-config.edit', [
            'staticHeaders' => $this->headers
        ])->layout('layouts.app');
    }
}
