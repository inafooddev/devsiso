<?php

namespace App\Livewire\MasterData\Product\SubBrandProduct;

use Livewire\Component;
use App\Models\ProductSubBrand;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductSubBrandsExport;
use App\Exports\ProductSubBrandsTemplateExport;
use App\Imports\ProductSubBrandsImport;

class Index extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'product-sub-brands.index';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $subBrandIdToDelete;

    // Form Fields
    public $sub_brand_id;
    public $sub_brand_name;
    public $old_sub_brand_id;
    public $importFile;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'sub_brand_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_sub_brands')->ignore($this->old_sub_brand_id, 'sub_brand_id')
                    : Rule::unique('product_sub_brands', 'sub_brand_id'),
            ],
            'sub_brand_name' => 'required|string|max:150',
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

    public function openEditModal($subBrandId)
    {
        $this->resetValidation();
        $subBrand = ProductSubBrand::findOrFail($subBrandId);
        
        $this->old_sub_brand_id = $subBrand->sub_brand_id;
        $this->sub_brand_id = $subBrand->sub_brand_id;
        $this->sub_brand_name = $subBrand->sub_brand_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->sub_brand_id = null;
        $this->sub_brand_name = null;
        $this->old_sub_brand_id = null;
    }

    public function save()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate();

        if ($this->isEditing) {
            $subBrand = ProductSubBrand::where('sub_brand_id', $this->old_sub_brand_id)->first();
            $subBrand->update([
                'sub_brand_id' => $this->sub_brand_id,
                'sub_brand_name' => $this->sub_brand_name,
            ]);
            \App\Helpers\ActivityLogger::log('Update Product Sub-Brand', "Memperbarui sub-brand produk: {$this->old_sub_brand_id} menjadi {$this->sub_brand_id} - {$this->sub_brand_name}");
            session()->flash('message', 'Product Sub-Brand berhasil diperbarui.');
        } else {
            ProductSubBrand::create([
                'sub_brand_id' => $this->sub_brand_id,
                'sub_brand_name' => $this->sub_brand_name,
            ]);
            \App\Helpers\ActivityLogger::log('Create Product Sub-Brand', "Menambahkan sub-brand produk baru: {$this->sub_brand_id} - {$this->sub_brand_name}");
            session()->flash('message', 'Product Sub-Brand berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $subBrands = ProductSubBrand::where('sub_brand_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('sub_brand_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('sub_brand_id')
            ->paginate(50);

        return view('livewire.master-data.product.sub-brand.index', [
            'subBrands' => $subBrands,
        ])->layout('layouts.app');
    }

    public function confirmDelete($subBrandId)
    {
        $this->subBrandIdToDelete = $subBrandId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->authorizeAction('can_edit');

        $subBrand = ProductSubBrand::where('sub_brand_id', $this->subBrandIdToDelete)->first();
        if ($subBrand) {
            \App\Helpers\ActivityLogger::log('Delete Product Sub-Brand', "Menghapus sub-brand produk: {$subBrand->sub_brand_id} - {$subBrand->sub_brand_name}");
            $subBrand->delete();
            session()->flash('message', 'Product Sub-Brand berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }

    public function export()
    {
        $this->authorizeAction('can_export');
        \App\Helpers\ActivityLogger::log('Export Product Sub-Brand', "Mengekspor data product sub-brand");
        return Excel::download(new ProductSubBrandsExport(), 'product_sub_brands.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductSubBrandsTemplateExport(), 'template_import_product_sub_brands.xlsx');
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
            Excel::import(new ProductSubBrandsImport, $this->importFile);
            \App\Helpers\ActivityLogger::log('Import Product Sub-Brand', "Mengimpor data product sub-brand dari Excel");
            session()->flash('message', 'Data Product Sub-Brand berhasil diimport.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
        $this->importFile = null;
    }
}
