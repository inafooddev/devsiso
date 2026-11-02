<?php

namespace App\Livewire\SalesConfig;

use Livewire\Component;
use App\Models\ConfigSalesInvoiceDistributor;
use App\Models\MasterDistributor;
use App\Traits\EnforcesMenuPermissions;

class Create extends Component
{
    use EnforcesMenuPermissions, HasSalesConfigMapping;

    protected string $menuRoute = 'sales-configs.index';

    public $distributor_code = '';
    public $distributorSearch = '';
    public $selectedDistributorName = '';

    /**
     * Inisialisasi state saat komponen dimuat
     */
    public function mount()
    {
        $this->initializeConfig();
    }

    /**
     * Aturan validasi
     */
    protected function rules()
    {
        $rules = [
            'distributor_code' => 'required|string|max:255|unique:config_sales_invoice_distributor,distributor_code',
        ];

        return array_merge($rules, $this->getMappingRules());
    }

    /**
     * Pilih distributor dari hasil autocomplete pencarian
     */
    public function selectDistributor($code, $name)
    {
        $this->distributor_code = $code;
        $this->selectedDistributorName = $name;
        $this->distributorSearch = '';
    }

    /**
     * Reset nama saat distributor_code dikosongkan
     */
    public function updatedDistributorCode($value)
    {
        if (empty($value)) {
            $this->selectedDistributorName = '';
        }
    }

    /**
     * Simpan konfigurasi baru ke database
     */
    public function save()
    {
        $this->validate();

        $selectedDistributor = MasterDistributor::find($this->distributor_code);
        if (!$selectedDistributor) {
            $this->addError('distributor_code', 'Distributor yang dipilih tidak valid.');
            return;
        }

        $this->validateBusinessRules();

        $filteredConfig = $this->getFilteredConfigToSave();

        ConfigSalesInvoiceDistributor::create([
            'distributor_code' => $selectedDistributor->distributor_code,
            'config_name' => $selectedDistributor->distributor_name,
            'config' => json_encode($filteredConfig, JSON_PRETTY_PRINT),
        ]);

        \App\Helpers\ActivityLogger::log('Create Sales Config', "Membuat konfigurasi sales invoice untuk distributor: {$selectedDistributor->distributor_code} - {$selectedDistributor->distributor_name}");

        session()->flash('message', 'Konfigurasi berhasil disimpan!');
        return redirect()->route('sales-configs.index');
    }

    /**
     * Render view
     */
    public function render()
    {
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

        return view('livewire.sell-out.sales-config.create', [
            'staticHeaders' => $this->headers,
            'distributors' => $distributors,
        ])->layout('layouts.app');
    }
}
