<?php

namespace App\Livewire\SalesConfig;

use Livewire\Component;
use App\Models\ConfigSalesInvoiceDistributor;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $isDeleteModalOpen = false;
    public $configToDeleteId;

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     * Melakukan join ke master_distributors berdasarkan distributor_code.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();
        $tableName = (new ConfigSalesInvoiceDistributor())->getTable();

        // Jika bukan admin dan memiliki batasan region_code
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->join('master_distributors', "$tableName.distributor_code", '=', 'master_distributors.distributor_code')
                  ->whereIn('master_distributors.region_code', $user->region_code);
        }

        return $query;
    }

    /**
     * Merender view daftar konfigurasi dengan paginasi dan fungsionalitas pencarian.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $tableName = (new ConfigSalesInvoiceDistributor())->getTable();

        // Inisialisasi query dengan pemilihan kolom yang spesifik
        $query = ConfigSalesInvoiceDistributor::query()
            ->select("$tableName.*");

        // Terapkan keamanan wilayah
        $this->applyRegionAccess($query);

        // Terapkan filter pencarian
        if ($this->search) {
            $query->where(function($q) use ($tableName) {
                $q->where("$tableName.config_name", 'ilike', '%' . $this->search . '%')
                  ->orWhere("$tableName.distributor_code", 'ilike', '%' . $this->search . '%');
            });
        }

        $configs = $query->latest("$tableName.created_at")
            ->paginate(10);

        return view('livewire.sales-config.index', [
            'configs' => $configs,
        ])->layout('layouts.app');
    }
    
    /**
     * Hook ini akan dijalankan saat properti $search diubah untuk mereset paginasi.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Membuka modal konfirmasi sebelum menghapus data.
     *
     * @param int $id
     */
    public function confirmDelete($id)
    {
        $this->configToDeleteId = $id;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menutup modal konfirmasi tanpa menghapus data.
     */
    public function closeDeleteModal()
    {
        $this->isDeleteModalOpen = false;
        $this->configToDeleteId = null;
    }

    /**
     * Menghapus data dari database setelah konfirmasi.
     */
    public function delete()
    {
        if ($this->configToDeleteId) {
            $tableName = (new ConfigSalesInvoiceDistributor())->getTable();
            
            // Kita pastikan user yang menghapus memang punya akses ke data tersebut (Security Check)
            $query = ConfigSalesInvoiceDistributor::query();
            $this->applyRegionAccess($query);
            
            $data = $query->where("$tableName.id", $this->configToDeleteId)->first();

            if ($data) {
                $data->delete();
                session()->flash('message', 'Konfigurasi berhasil dihapus!');
            } else {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus data ini.');
            }
        }
        $this->isDeleteModalOpen = false;
    }
}