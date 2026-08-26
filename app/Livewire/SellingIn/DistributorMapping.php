<?php

namespace App\Livewire\SellingIn;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\SellingInDistributorMapping;
use App\Models\MasterDistributor;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SellingInDistributorMappingExport;
use App\Imports\SellingInDistributorMappingImport;
use Exception;

class DistributorMapping extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // Search & Filter
    public $search = '';
    
    // Form Properties
    public $mapping_id;
    public $divisi = '';
    public $wilayah = '';
    public $kode_distributor = '';
    public $distributor = '';
    public $distributor_code = '';
    
    // UI State
    public $isModalOpen = false;
    public $isImportModalOpen = false;
    
    // Import Property
    public $importFile;

    protected function rules()
    {
        return [
            'divisi' => 'required|string|max:255',
            'wilayah' => 'required|string|max:255',
            'kode_distributor' => 'required|string|max:255',
            'distributor' => 'required|string|max:255',
            'distributor_code' => 'required|exists:master_distributors,distributor_code',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['divisi', 'wilayah', 'kode_distributor', 'distributor', 'distributor_code', 'mapping_id']);

        if ($id) {
            $mapping = SellingInDistributorMapping::findOrFail($id);
            $this->mapping_id = $mapping->id;
            $this->divisi = $mapping->divisi;
            $this->wilayah = $mapping->wilayah;
            $this->kode_distributor = $mapping->kode_distributor;
            $this->distributor = $mapping->distributor;
            $this->distributor_code = $mapping->distributor_code;
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->isImportModalOpen = false;
    }

    public function save()
    {
        $this->validate();

        // Validasi kombinasi unik
        $existing = SellingInDistributorMapping::where('divisi', $this->divisi)
            ->where('wilayah', $this->wilayah)
            ->where('kode_distributor', $this->kode_distributor)
            ->where('distributor', $this->distributor);

        if ($this->mapping_id) {
            $existing->where('id', '!=', $this->mapping_id);
        }

        if ($existing->exists()) {
            $this->addError('distributor', 'Kombinasi Divisi, Wilayah, Kode, dan Distributor ini sudah ada di database.');
            return;
        }

        try {
            SellingInDistributorMapping::updateOrCreate(
                ['id' => $this->mapping_id],
                [
                    'divisi' => $this->divisi,
                    'wilayah' => $this->wilayah,
                    'kode_distributor' => $this->kode_distributor,
                    'distributor' => $this->distributor,
                    'distributor_code' => $this->distributor_code
                ]
            );

            session()->flash('success', $this->mapping_id ? 'Mapping berhasil diperbarui.' : 'Mapping berhasil ditambahkan.');
            $this->closeModal();
        } catch (Exception $e) {
            session()->flash('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            SellingInDistributorMapping::findOrFail($id)->delete();
            session()->flash('success', 'Mapping berhasil dihapus.');
        } catch (Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function openImportModal()
    {
        $this->resetValidation();
        $this->reset('importFile');
        $this->isImportModalOpen = true;
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xls,xlsx|max:10240', // 10MB
        ]);

        try {
            Excel::import(new SellingInDistributorMappingImport, $this->importFile->getRealPath());
            session()->flash('success', 'Data Mapping berhasil di-import (Upsert).');
            $this->closeModal();
        } catch (Exception $e) {
            session()->flash('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new SellingInDistributorMappingExport, 'Selling_In_Distributor_Mapping.xlsx');
    }

    public function render()
    {
        $query = SellingInDistributorMapping::query()
            ->with('masterDistributor');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('kode_distributor', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor', 'ilike', '%' . $this->search . '%')
                  ->orWhere('wilayah', 'ilike', '%' . $this->search . '%')
                  ->orWhere('divisi', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_code', 'ilike', '%' . $this->search . '%');
            });
        }

        $mappings = $query->orderBy('updated_at', 'desc')->paginate(100);
        $masterDistributors = MasterDistributor::select('distributor_code', 'distributor_name')->orderBy('distributor_name')->get();

        return view('livewire.selling-in.distributor-mapping', [
            'mappings' => $mappings,
            'masterDistributors' => $masterDistributors
        ])->layout('layouts.app');
    }
}
