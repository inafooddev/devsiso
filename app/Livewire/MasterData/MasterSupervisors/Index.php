<?php

namespace App\Livewire\MasterData\MasterSupervisors;

use Livewire\Component;
use App\Models\MasterSupervisor;
use App\Models\MasterArea;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use App\Imports\MasterSupervisorsImport;
use App\Exports\MasterSupervisorsTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions, WithFileUploads;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'master-supervisors.index';

    public $search = '';
    public $regionFilter = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    
    public $importFile;
    
    // Form Fields
    public $supervisorId;
    public $supervisor_code;
    public $supervisor_name;
    public $description;
    public $area_code = '';
    public $supervisorIdToDelete;

    protected $queryString = ['search', 'regionFilter'];

    /**
     * Aturan validasi.
     */
    protected function rules()
    {
        return [
            'supervisor_code' => [
                'required',
                'string',
                'max:15',
                $this->isEditing 
                    ? Rule::unique('master_supervisors')->ignore($this->supervisorId, 'supervisor_code')
                    : Rule::unique('master_supervisors', 'supervisor_code'),
            ],
            'supervisor_name' => 'required|string|max:50',
            'description'     => 'nullable|string|max:100',
            'area_code'       => 'required|exists:master_areas,area_code',
        ];
    }

    /**
     * Pesan validasi kustom.
     */
    protected function messages()
    {
        return [
            'area_code.required' => 'Silakan pilih salah satu area.',
        ];
    }

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query, $type = 'supervisor')
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            if ($type === 'supervisor') {
                $query->whereHas('area', function ($areaQuery) use ($user) {
                    $areaQuery->whereIn('region_code', $user->region_code);
                });
            } elseif ($type === 'area') {
                $query->whereIn('region_code', $user->region_code);
            }
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
    public function openEditModal($supervisorCode)
    {
        $this->resetValidation();
        $supervisor = MasterSupervisor::findOrFail($supervisorCode);
        
        $this->supervisorId    = $supervisor->supervisor_code;
        $this->supervisor_code = $supervisor->supervisor_code;
        $this->supervisor_name = $supervisor->supervisor_name;
        $this->description     = $supervisor->description;
        $this->area_code       = $supervisor->area_code;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->supervisorId = null;
        $this->supervisor_code = null;
        $this->supervisor_name = null;
        $this->description = null;
        $this->area_code = '';
    }

    /**
     * Menyimpan atau memperbarui data supervisor.
     */
    public function save()
    {
        $this->authorizeAction('can_edit');
        
        $this->validate();

        if ($this->isEditing) {
            $supervisor = MasterSupervisor::find($this->supervisorId);
            $supervisor->update([
                'supervisor_code' => $this->supervisor_code,
                'supervisor_name' => $this->supervisor_name,
                'description'     => $this->description,
                'area_code'       => $this->area_code,
            ]);
            \App\Helpers\ActivityLogger::log('Update Supervisor', "Memperbarui supervisor: {$this->supervisor_code} - {$this->supervisor_name}");
            session()->flash('message', 'Data supervisor berhasil diperbarui.');
        } else {
            MasterSupervisor::create([
                'supervisor_code' => $this->supervisor_code,
                'supervisor_name' => $this->supervisor_name,
                'description'     => $this->description,
                'area_code'       => $this->area_code,
            ]);
            \App\Helpers\ActivityLogger::log('Create Supervisor', "Menambahkan supervisor baru: {$this->supervisor_code} - {$this->supervisor_name}");
            session()->flash('message', 'Supervisor baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = MasterSupervisor::with(['area', 'area.region'])
            ->select('master_supervisors.*')
            ->leftJoin('master_areas', 'master_supervisors.area_code', '=', 'master_areas.area_code')
            ->where('master_supervisors.supervisor_code', '!=', 'HOINA');

        $this->applyRegionAccess($query, 'supervisor');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('master_supervisors.supervisor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('master_supervisors.supervisor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('master_supervisors.description', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('area', function($areaQuery) {
                      $areaQuery->where('area_name', 'ilike', '%' . $this->search . '%')
                                ->orWhereHas('region', function($regionQuery) {
                                    $regionQuery->where('region_name', 'ilike', '%' . $this->search . '%');
                                });
                  });
            });
        }

        if (!empty($this->regionFilter)) {
            $query->whereHas('area', function ($q) {
                $q->where('region_code', $this->regionFilter);
            });
        }

        $supervisors = $query->orderBy('master_areas.region_code', 'asc')
                             ->orderBy('master_supervisors.supervisor_name', 'asc')
                             ->paginate(50);
                             
        // Ambil data area untuk dropdown form
        $areasQuery = MasterArea::orderBy('area_name', 'asc');
        $this->applyRegionAccess($areasQuery, 'area');
        $areas = $areasQuery->get();

        // Ambil data region untuk filter dropdown
        $regionsQuery = \App\Models\MasterRegion::orderBy('region_name', 'asc')->where('region_code', '!=', 'HOINA');
        $this->applyRegionAccess($regionsQuery, 'region');
        $regions = $regionsQuery->get();

        return view('livewire.master-data.master-supervisors.index', [
            'supervisors' => $supervisors,
            'areas' => $areas,
            'regions' => $regions,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($supervisorId)
    {
        $this->supervisorIdToDelete = $supervisorId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data supervisor.
     */
    public function delete()
    {
        $this->authorizeAction('can_edit');

        $query = MasterSupervisor::query();
        $this->applyRegionAccess($query, 'supervisor');
        
        $supervisor = $query->where('supervisor_code', $this->supervisorIdToDelete)->first();

        if ($supervisor) {
            try {
                $supervisor->delete();
                \App\Helpers\ActivityLogger::log('Delete Supervisor', "Menghapus supervisor: {$supervisor->supervisor_code} - {$supervisor->supervisor_name}");
                session()->flash('message', 'Supervisor berhasil dihapus.');
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23503) {
                    session()->flash('error', 'Gagal menghapus! Supervisor ini masih terhubung dengan data Distributor.');
                } else {
                    session()->flash('error', 'Terjadi kesalahan database: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus supervisor ini.');
        }

        $this->isDeleteModalOpen = false;
        $this->reset('supervisorIdToDelete');
    }

    public function openImportModal()
    {
        $this->resetValidation('importFile');
        $this->importFile = null;
        $this->isImportModalOpen = true;
    }

    public function downloadTemplate()
    {
        $timestamp = date('Ymd_His');
        return Excel::download(new MasterSupervisorsTemplateExport, "template_import_supervisor_{$timestamp}.xlsx");
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $import = new MasterSupervisorsImport;
            Excel::import($import, $this->importFile);

            $timestamp = date('Ymd_His');
            $logFileName = "log_master_supervisor_{$timestamp}.txt";
            $logContent = "Hasil Import Master Supervisor:\n";
            $logContent .= "Waktu: " . date('Y-m-d H:i:s') . "\n";
            $logContent .= "Berhasil: {$import->importedCount}\n";
            $logContent .= "Gagal / Dilewati: {$import->skippedCount}\n\n";
            $logContent .= "Rincian Log:\n";
            $logContent .= implode("\n", $import->logs);

            $this->isImportModalOpen = false;
            $this->reset('importFile');
            
            session()->flash('message', "Import selesai! {$import->importedCount} berhasil, {$import->skippedCount} gagal.");
            
            return response()->streamDownload(function () use ($logContent) {
                echo $logContent;
            }, $logFileName);

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $filters = [
            'search' => $this->search,
        ];
        
        $timestamp = date('Ymd_His');
        return Excel::download(new \App\Exports\MasterSupervisorsExport($filters), "master_supervisors_{$timestamp}.xlsx");
    }
}
