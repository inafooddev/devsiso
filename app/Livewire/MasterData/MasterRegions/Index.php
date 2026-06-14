<?php

namespace App\Livewire\MasterData\MasterRegions;

use Livewire\Component;
use App\Models\MasterRegion;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MasterRegionsExport;
use App\Exports\MasterRegionsTemplateExport;
use App\Imports\MasterRegionsImport;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions, WithFileUploads;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'master-regions.index';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    
    // Form Fields
    public $regionId;
    public $region_code;
    public $region_name;
    public $regionIdToDelete;
    public $importFile;

    protected $queryString = ['search'];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'region_code' => [
                'required',
                'string',
                'max:15',
                $this->isEditing 
                    ? Rule::unique('master_regions')->ignore($this->regionId, 'region_code')
                    : Rule::unique('master_regions', 'region_code'),
            ],
            'region_name' => 'required|string|max:50',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'region_code.required' => 'Kode Region wajib diisi.',
            'region_code.unique'   => 'Kode Region ini sudah digunakan.',
            'region_name.required' => 'Nama Region wajib diisi.',
        ];
    }

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }

        return $query;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Membuka modal untuk tambah data.
     */
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    /**
     * Membuka modal untuk edit data.
     */
    public function openEditModal($regionCode)
    {
        $this->resetValidation();
        $region = MasterRegion::findOrFail($regionCode);
        
        $this->regionId = $region->region_code;
        $this->region_code = $region->region_code;
        $this->region_name = $region->region_name;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->regionId = null;
        $this->region_code = null;
        $this->region_name = null;
    }

    /**
     * Menyimpan atau memperbarui data region.
     */
    public function save()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate();

        if ($this->isEditing) {
            $region = MasterRegion::find($this->regionId);
            $region->update([
                'region_code' => $this->region_code,
                'region_name' => $this->region_name,
            ]);
            \App\Helpers\ActivityLogger::log('Update Region', "Memperbarui region: {$this->region_code} - {$this->region_name}");
            session()->flash('message', 'Region berhasil diperbarui.');
        } else {
            MasterRegion::create([
                'region_code' => $this->region_code,
                'region_name' => $this->region_name,
            ]);
            \App\Helpers\ActivityLogger::log('Create Region', "Menambahkan region baru: {$this->region_code} - {$this->region_name}");
            session()->flash('message', 'Region baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = MasterRegion::query()
            ->where('region_code', '!=', 'HOINA')
            ->orderBy('region_code', 'asc');

        $this->applyRegionAccess($query);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('region_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('region_name', 'ilike', '%' . $this->search . '%');
            });
        }

        $regions = $query->paginate(50);

        return view('livewire.master-data.master-regions.index', [
            'regions' => $regions,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($regionId)
    {
        $this->regionIdToDelete = $regionId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data region.
     */
    public function delete()
    {
        $this->authorizeAction('can_edit'); // Use can_edit as the permission to delete

        $query = MasterRegion::query();
        $this->applyRegionAccess($query);
        
        $region = $query->where('region_code', $this->regionIdToDelete)->first();

        if ($region) {
            try {
                $region->delete();
                \App\Helpers\ActivityLogger::log('Delete Region', "Menghapus region: {$region->region_code} - {$region->region_name}");
                session()->flash('message', 'Region berhasil dihapus.');
            } catch (\Illuminate\Database\QueryException $e) {
                $this->isDeleteModalOpen = false;

                if ($e->getCode() === '23503' || $e->getCode() == 23000) {
                    $errorMsg = 'Gagal menghapus! Region ini tidak bisa dihapus karena masih digunakan atau terhubung dengan data Master Area.';
                    $systemMsg = "FOREIGN KEY CONSTRAINT VIOLATION\nData Region ({$region->region_code}) ditolak untuk dihapus oleh Database sistem. Alasannya karena data region ini masih dipakai (direferensikan) oleh entitas lain. Anda harus menghapus atau mengubah region pada area yang bersangkutan terlebih dahulu.";
                } else {
                    $errorMsg = 'Terjadi kesalahan sistem saat menghapus data.';
                    $systemMsg = $e->getMessage();
                }
                session()->flash('error', $errorMsg);

                $logContent = "=================================================\n";
                $logContent .= "      LOG ERROR DELETE MASTER REGION\n";
                $logContent .= "=================================================\n\n";
                $logContent .= "Waktu         : " . now()->format('Y-m-d H:i:s') . "\n";
                $logContent .= "Kode Region   : {$region->region_code}\n";
                $logContent .= "Nama Region   : {$region->region_name}\n";
                $logContent .= "Kode Error    : {$e->getCode()}\n\n";
                $logContent .= "Pesan Sistem  :\n{$systemMsg}\n\n";
                $logContent .= "=================================================\n";

                $fileName = 'log_master_region_' . date('Ymd_His') . '.txt';

                return response()->streamDownload(function () use ($logContent) {
                    echo $logContent;
                }, $fileName);
            }
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus region ini.');
        }

        $this->isDeleteModalOpen = false;
    }

    public function export()
    {
        $this->authorizeAction('can_view');
        return Excel::download(new MasterRegionsExport([
            'search' => $this->search,
        ]), 'Data_Master_Region_' . date('Ymd_His') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $this->authorizeAction('can_view');
        return Excel::download(new MasterRegionsTemplateExport, 'Template_Import_Master_Region.xlsx');
    }

    public function import()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new MasterRegionsImport();
            Excel::import($import, $this->importFile);

            $message = "Proses Import Selesai. Berhasil: {$import->importedCount}. Gagal/Dilewati: {$import->skippedCount}.";
            \App\Helpers\ActivityLogger::log('Import Master Region', "Import data Region. Sukses: {$import->importedCount}, Skip: {$import->skippedCount}");
            
            if ($import->skippedCount > 0) {
                session()->flash('error', $message . ' (Silakan periksa file log untuk detailnya)');
            } else {
                session()->flash('message', $message . ' (Semua data berhasil diimpor)');
            }

            $logContent = "=================================================\n";
            $logContent .= "           LOG IMPORT MASTER REGION\n";
            $logContent .= "=================================================\n\n";
            $logContent .= "Waktu         : " . now()->format('Y-m-d H:i:s') . "\n";
            $logContent .= "Total Sukses  : {$import->importedCount}\n";
            $logContent .= "Total Gagal   : {$import->skippedCount}\n\n";
            $logContent .= "Rincian Proses:\n";
            $logContent .= "-------------------------------------------------\n";
            if (empty($import->logs)) {
                $logContent .= "Tidak ada data yang diproses.\n";
            } else {
                foreach ($import->logs as $log) {
                    $logContent .= $log . "\n";
                }
            }
            $logContent .= "=================================================\n";

            $fileName = 'log_import_region_' . date('Ymd_His') . '.txt';

            $this->isImportModalOpen = false;
            $this->reset('importFile');

            return response()->streamDownload(function () use ($logContent) {
                echo $logContent;
            }, $fileName);

        } catch (\Exception $e) {
            $this->isImportModalOpen = false;
            $this->reset('importFile');
            session()->flash('error', 'Sistem Gagal memproses file import: ' . $e->getMessage());
        }
    }
}
