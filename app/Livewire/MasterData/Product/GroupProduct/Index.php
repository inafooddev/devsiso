<?php

namespace App\Livewire\MasterData\Product\GroupProduct;

use Livewire\Component;
use App\Models\ProductGroup;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductGroupsExport;
use App\Exports\ProductGroupsTemplateExport;
use App\Imports\ProductGroupsImport;

class Index extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'product-groups.index';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $groupIdToDelete;

    // Form Fields
    public $product_group_id;
    public $brand_unit_name;
    public $old_group_id;
    public $importFile;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'product_group_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_groups')->ignore($this->old_group_id, 'product_group_id')
                    : Rule::unique('product_groups', 'product_group_id'),
            ],
            'brand_unit_name' => 'required|string|max:150',
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

    public function openEditModal($groupId)
    {
        $this->resetValidation();
        $group = ProductGroup::findOrFail($groupId);
        
        $this->old_group_id = $group->product_group_id;
        $this->product_group_id = $group->product_group_id;
        $this->brand_unit_name = $group->brand_unit_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->product_group_id = null;
        $this->brand_unit_name = null;
        $this->old_group_id = null;
    }

    public function save()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate();

        if ($this->isEditing) {
            $group = ProductGroup::where('product_group_id', $this->old_group_id)->first();
            $group->update([
                'product_group_id' => $this->product_group_id,
                'brand_unit_name' => $this->brand_unit_name,
            ]);
            \App\Helpers\ActivityLogger::log('Update Product Group', "Memperbarui group produk: {$this->old_group_id} menjadi {$this->product_group_id} - {$this->brand_unit_name}");
            session()->flash('message', 'Product Group berhasil diperbarui.');
        } else {
            ProductGroup::create([
                'product_group_id' => $this->product_group_id,
                'brand_unit_name' => $this->brand_unit_name,
            ]);
            \App\Helpers\ActivityLogger::log('Create Product Group', "Menambahkan group produk baru: {$this->product_group_id} - {$this->brand_unit_name}");
            session()->flash('message', 'Product Group berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $groups = ProductGroup::where('product_group_id', 'ILIKE', '%' . $this->search . '%')
            ->orWhere('brand_unit_name', 'ILIKE', '%' . $this->search . '%')
            ->latest('product_group_id')
            ->paginate(50);

        return view('livewire.master-data.product.group.index', [
            'groups' => $groups,
        ])->layout('layouts.app');
    }

    public function confirmDelete($groupId)
    {
        $this->groupIdToDelete = $groupId;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->authorizeAction('can_edit');

        $group = ProductGroup::where('product_group_id', $this->groupIdToDelete)->first();
        if ($group) {
            \App\Helpers\ActivityLogger::log('Delete Product Group', "Menghapus group produk: {$group->product_group_id} - {$group->brand_unit_name}");
            $group->delete();
            session()->flash('message', 'Product Group berhasil dihapus.');
        }
        $this->isDeleteModalOpen = false;
    }

    public function export()
    {
        $this->authorizeAction('can_export');
        \App\Helpers\ActivityLogger::log('Export Product Group', "Mengekspor data product group");
        return Excel::download(new ProductGroupsExport(), 'product_groups.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductGroupsTemplateExport(), 'template_import_product_groups.xlsx');
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
            Excel::import(new ProductGroupsImport, $this->importFile);
            \App\Helpers\ActivityLogger::log('Import Product Group', "Mengimpor data product group dari Excel");
            session()->flash('message', 'Data Product Group berhasil diimport.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
        $this->importFile = null;
    }
}
