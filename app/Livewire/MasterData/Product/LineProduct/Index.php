<?php

namespace App\Livewire\MasterData\Product\LineProduct;

use Livewire\Component;
use App\Models\ProductLine;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductLinesExport;
use App\Exports\ProductLinesTemplateExport;
use App\Imports\ProductLinesImport;

class Index extends Component
{
    use WithPagination, WithFileUploads, EnforcesMenuPermissions;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'product-lines.index';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $lineIdToDelete;

    // Form Fields
    public $line_id;
    public $line_name;
    public $old_line_id;
    public $importFile;

    protected $queryString = ['search' => ['except' => '']];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'line_id' => [
                'required', 'string', 'max:15',
                $this->isEditing 
                    ? Rule::unique('product_lines')->ignore($this->old_line_id, 'line_id')
                    : Rule::unique('product_lines', 'line_id'),
            ],
            'line_name' => 'required|string|max:100',
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

    public function openEditModal($lineId)
    {
        $this->resetValidation();
        $line = ProductLine::findOrFail($lineId);
        
        $this->old_line_id = $line->line_id;
        $this->line_id = $line->line_id;
        $this->line_name = $line->line_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->line_id = null;
        $this->line_name = null;
        $this->old_line_id = null;
    }

    public function save()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate();

        if ($this->isEditing) {
            $line = ProductLine::where('line_id', $this->old_line_id)->first();
            $line->update([
                'line_id' => $this->line_id,
                'line_name' => $this->line_name,
            ]);
            \App\Helpers\ActivityLogger::log('Update Product Line', "Memperbarui line produk: {$this->old_line_id} menjadi {$this->line_id} - {$this->line_name}");
            session()->flash('message', 'Product Line berhasil diperbarui.');
        } else {
            ProductLine::create([
                'line_id' => $this->line_id,
                'line_name' => $this->line_name,
            ]);
            \App\Helpers\ActivityLogger::log('Create Product Line', "Menambahkan line produk baru: {$this->line_id} - {$this->line_name}");
            session()->flash('message', 'Product Line berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $lines = ProductLine::where('line_id', 'ilike', '%' . $this->search . '%')
            ->orWhere('line_name', 'ilike', '%' . $this->search . '%')
            ->latest('line_id')
            ->paginate(50);

        return view('livewire.master-data.product.line.index', [
            'lines' => $lines,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($lineId)
    {
        $this->lineIdToDelete = $lineId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data product line.
     */
    public function delete()
    {
        $this->authorizeAction('can_edit');

        $line = ProductLine::where('line_id', $this->lineIdToDelete)->first();
        if ($line) {
            \App\Helpers\ActivityLogger::log('Delete Product Line', "Menghapus line produk: {$line->line_id} - {$line->line_name}");
            $line->delete();
            session()->flash('message', 'Product Line berhasil dihapus.');
        }

        $this->isDeleteModalOpen = false;
    }

    public function export()
    {
        $this->authorizeAction('can_export');
        \App\Helpers\ActivityLogger::log('Export Product Line', "Mengekspor data product line");
        return Excel::download(new ProductLinesExport(), 'product_lines.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductLinesTemplateExport(), 'template_import_product_lines.xlsx');
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
            Excel::import(new ProductLinesImport, $this->importFile);
            \App\Helpers\ActivityLogger::log('Import Product Line', "Mengimpor data product line dari Excel");
            session()->flash('message', 'Data Product Line berhasil diimport.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
        $this->importFile = null;
    }
}
