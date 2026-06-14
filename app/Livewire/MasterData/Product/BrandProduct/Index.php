<?php

namespace App\Livewire\MasterData\Product\BrandProduct;

use Livewire\Component;
use App\Models\ProductBrand;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductBrandsExport;
use App\Exports\ProductBrandsTemplateExport;
use App\Imports\ProductBrandsImport;

class Index extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'product-brands.index';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $brandIdToDelete;

    // Form Fields
    public $brand_id;
    public $brand_name;
    public $old_brand_id;
    public $importFile;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'brand_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_brands')->ignore($this->old_brand_id, 'brand_id')
                    : Rule::unique('product_brands', 'brand_id'),
            ],
            'brand_name' => 'required|string|max:150',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * CRUD Modal Operations.
     */
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function openEditModal($brandId)
    {
        $this->resetValidation();
        $brand = ProductBrand::findOrFail($brandId);
        
        $this->old_brand_id = $brand->brand_id;
        $this->brand_id = $brand->brand_id;
        $this->brand_name = $brand->brand_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->brand_id = null;
        $this->brand_name = null;
        $this->old_brand_id = null;
    }

    public function save()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate();

        if ($this->isEditing) {
            $brand = ProductBrand::where('brand_id', $this->old_brand_id)->first();
            $brand->update([
                'brand_id' => $this->brand_id,
                'brand_name' => $this->brand_name,
            ]);
            \App\Helpers\ActivityLogger::log('Update Product Brand', "Memperbarui brand produk: {$this->old_brand_id} menjadi {$this->brand_id} - {$this->brand_name}");
            session()->flash('message', 'Product Brand berhasil diperbarui.');
        } else {
            ProductBrand::create([
                'brand_id' => $this->brand_id,
                'brand_name' => $this->brand_name,
            ]);
            \App\Helpers\ActivityLogger::log('Create Product Brand', "Menambahkan brand produk baru: {$this->brand_id} - {$this->brand_name}");
            session()->flash('message', 'Product Brand berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $brands = ProductBrand::where('brand_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('brand_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('brand_id')
            ->paginate(50);

        return view('livewire.master-data.product.brand.index', [
            'brands' => $brands,
        ])->layout('layouts.app');
    }

    public function confirmDelete($brandId)
    {
        $this->brandIdToDelete = $brandId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->authorizeAction('can_edit');

        $brand = ProductBrand::where('brand_id', $this->brandIdToDelete)->first();
        if ($brand) {
            \App\Helpers\ActivityLogger::log('Delete Product Brand', "Menghapus brand produk: {$brand->brand_id} - {$brand->brand_name}");
            $brand->delete();
            session()->flash('message', 'Product Brand berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }

    public function export()
    {
        $this->authorizeAction('can_export');
        \App\Helpers\ActivityLogger::log('Export Product Brand', "Mengekspor data product brand");
        return Excel::download(new ProductBrandsExport(), 'product_brands.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductBrandsTemplateExport(), 'template_import_product_brands.xlsx');
    }

    public function openImportModal()
    {
        $this->importFile = null;
        $this->isImportModalOpen = true;
    }

    public function import()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new ProductBrandsImport, $this->importFile);
            \App\Helpers\ActivityLogger::log('Import Product Brand', "Mengimpor data product brand dari Excel");
            session()->flash('message', 'Data Product Brand berhasil diimport.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
        $this->importFile = null;
    }
}

