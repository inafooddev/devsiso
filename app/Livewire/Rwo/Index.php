<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use App\Models\RewardOutlet;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RewardOutletExport;
use App\Imports\RewardOutletImport;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    
    // Modal & Form States
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isDetailModalOpen = false;
    public $isImportModalOpen = false;
    
    // Form Fields
    public $outletId;
    public $region_code;
    public $region_name;
    public $area_code;
    public $area_name;
    public $branch_name;
    public $eskalink_code;
    public $customer_code;
    public $customer_name;
    public $alamat;
    public $no_hp;
    public $latitude;
    public $longitude;
    public $nama_pemilik_toko;
    public $nama_ktp;
    public $nik_ktp;
    public $foto_ktp; // File upload object
    public $existing_foto_ktp; // Path string
    public $nama_bank;
    public $no_rekening;
    public $nama_pemilik_norek;
    public $foto_toko; // File upload object
    public $existing_foto_toko; // Path string
    public $foto_toko2; // File upload object
    public $existing_foto_toko2; // Path string
    public $foto_toko3; // File upload object
    public $existing_foto_toko3; // Path string
    public $keterangan;
    public $is_valid = false;
    
    public $outletIdToDelete;
    
    // Detail View State
    public $selectedOutlet;
    
    // Import File State
    public $importFile;

    public $filter_type = '';
    public $filter_region_code = '';
    public $filter_area_code = '';
    public $filter_branch_name = '';
    public $isFilterModalOpen = false;

    protected $queryString = [
        'search', 
        'filter_type', 
        'filter_region_code', 
        'filter_area_code', 
        'filter_branch_name'
    ];

    /**
     * Aturan validasi
     */
    protected function rules()
    {
        return [
            'region_code' => 'required|string|max:50',
            'region_name' => 'required|string|max:100',
            'area_code' => 'required|string|max:50',
            'area_name' => 'required|string|max:100',
            'branch_name' => 'required|string|max:100',
            'eskalink_code' => 'nullable|string|max:50',
            'customer_code' => [
                'required',
                'string',
                'max:50',
                $this->isEditing 
                    ? Rule::unique('reward_outlet', 'customer_code')->ignore($this->outletId)
                    : Rule::unique('reward_outlet', 'customer_code'),
            ],
            'customer_name' => 'required|string|max:100',
            'alamat' => 'required|string',
            'no_hp' => 'nullable|string|max:20',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'nama_pemilik_toko' => 'nullable|string|max:100',
            'nama_ktp' => 'nullable|string|max:100',
            'nik_ktp' => 'nullable|string|max:50',
            'foto_ktp' => 'nullable|image|max:2048', // 2MB Max
            'nama_bank' => 'nullable|string|max:100',
            'no_rekening' => 'nullable|string|max:50',
            'nama_pemilik_norek' => 'nullable|string|max:100',
            'foto_toko' => 'nullable|image|max:2048', // 2MB Max
            'foto_toko2' => 'nullable|image|max:2048', // 2MB Max
            'foto_toko3' => 'nullable|image|max:2048', // 2MB Max
            'keterangan' => 'nullable|string',
            'is_valid' => 'nullable|boolean',
        ];
    }

    /**
     * Filter data region access
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }
        return $query;
    }

    /**
     * Get list of regions for select
     */
    public function getRegions()
    {
        $user = auth()->user();
        $query = \App\Models\MasterRegion::query();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }
        return $query->orderBy('region_name')->get();
    }

    /**
     * Get list of areas for select based on selected region
     */
    public function getAreas()
    {
        if (empty($this->region_code)) {
            return collect();
        }
        return \App\Models\MasterArea::where('region_code', $this->region_code)
            ->orderBy('area_name')
            ->get();
    }

    /**
     * Get list of branches for select
     */
    public function getBranches()
    {
        return \App\Models\MasterBranch::orderBy('branch_name')->get();
    }

    public function getFilterRegions()
    {
        $user = auth()->user();
        $query = \App\Models\MasterRegion::query();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }
        return $query->orderBy('region_name')->get();
    }

    public function getFilterAreas()
    {
        $query = \App\Models\MasterArea::query();
        if (!empty($this->filter_region_code)) {
            $query->where('region_code', $this->filter_region_code);
        } else {
            // Apply region access restrictions if no region is selected
            $user = auth()->user();
            if (!$user->hasRole('admin') && !empty($user->region_code)) {
                $query->whereIn('region_code', $user->region_code);
            }
        }
        return $query->orderBy('area_name')->get();
    }

    public function getFilterBranches()
    {
        $query = \App\Models\MasterBranch::query();
        
        if (!empty($this->filter_area_code)) {
            // Filter by Area: get supervisor codes for the selected area
            $supervisorCodes = \App\Models\MasterSupervisor::where('area_code', $this->filter_area_code)
                ->pluck('supervisor_code');
            $query->whereIn('supervisor_code', $supervisorCodes);
        } elseif (!empty($this->filter_region_code)) {
            // Filter by Region: get areas under region, then supervisors, then branches
            $areaCodes = \App\Models\MasterArea::where('region_code', $this->filter_region_code)
                ->pluck('area_code');
            $supervisorCodes = \App\Models\MasterSupervisor::whereIn('area_code', $areaCodes)
                ->pluck('supervisor_code');
            $query->whereIn('supervisor_code', $supervisorCodes);
        } else {
            // Apply region access restrictions if no region/area is selected
            $user = auth()->user();
            if (!$user->hasRole('admin') && !empty($user->region_code)) {
                $areaCodes = \App\Models\MasterArea::whereIn('region_code', $user->region_code)
                    ->pluck('area_code');
                $supervisorCodes = \App\Models\MasterSupervisor::whereIn('area_code', $areaCodes)
                    ->pluck('supervisor_code');
                $query->whereIn('supervisor_code', $supervisorCodes);
            }
        }
        
        return $query->orderBy('branch_name')->get();
    }

    public function updatedFilterRegionCode($value)
    {
        $this->filter_area_code = '';
        $this->filter_branch_name = '';
        $this->resetPage();
    }

    public function updatedFilterAreaCode($value)
    {
        $this->filter_branch_name = '';
        $this->resetPage();
    }

    public function updatedFilterBranchName($value)
    {
        $this->resetPage();
    }

    public function openFilterModal()
    {
        $this->isFilterModalOpen = true;
    }

    public function closeFilterModal()
    {
        $this->isFilterModalOpen = false;
    }

    public function resetFilters()
    {
        $this->filter_region_code = '';
        $this->filter_area_code = '';
        $this->filter_branch_name = '';
        $this->resetPage();
    }

    /**
     * Handler when region_code is updated
     */
    public function updatedRegionCode($value)
    {
        if ($value) {
            $region = \App\Models\MasterRegion::where('region_code', $value)->first();
            $this->region_name = $region ? $region->region_name : '';
        } else {
            $this->region_name = '';
        }
        $this->area_code = '';
        $this->area_name = '';
    }

    /**
     * Handler when area_code is updated
     */
    public function updatedAreaCode($value)
    {
        if ($value) {
            $area = \App\Models\MasterArea::where('area_code', $value)->first();
            $this->area_name = $area ? $area->area_name : '';
        } else {
            $this->area_name = '';
        }
    }

    /**
     * Get standard Indonesian banks list
     */
    public function getBanksList()
    {
        return [
            'Bank Central Asia (BCA)',
            'Bank Mandiri',
            'Bank Rakyat Indonesia (BRI)',
            'Bank Negara Indonesia (BNI)',
            'Bank Syariah Indonesia (BSI)',
            'Bank Tabungan Negara (BTN)',
            'Bank Danamon',
            'Bank CIMB Niaga',
            'Bank Permata',
            'OCBC NISP',
            'Bank Mega',
            'Bank Bukopin',
            'Bank Panin',
            'Bank Jago',
            'Allo Bank',
            'Bank BJB',
            'Bank DKI',
            'Bank Jatim',
            'Bank Jateng',
            'Bank Nobu',
            'SeaBank',
            'Bank Neo Commerce',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function setFilter($type)
    {
        $this->filter_type = $type;
        $this->resetPage();
    }

    /**
     * Open form to create new
     */
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    /**
     * Open form to edit
     */
    public function openEditModal($id)
    {
        $this->resetValidation();
        $outlet = RewardOutlet::findOrFail($id);
        
        $this->outletId = $outlet->id;
        $this->region_code = $outlet->region_code;
        $this->region_name = $outlet->region_name;
        $this->area_code = $outlet->area_code;
        $this->area_name = $outlet->area_name;
        $this->branch_name = $outlet->branch_name;
        $this->eskalink_code = $outlet->eskalink_code;
        $this->customer_code = $outlet->customer_code;
        $this->customer_name = $outlet->customer_name;
        $this->alamat = $outlet->alamat;
        $this->no_hp = $outlet->no_hp;
        $this->latitude = $outlet->latitude;
        $this->longitude = $outlet->longitude;
        $this->nama_pemilik_toko = $outlet->nama_pemilik_toko;
        $this->nama_ktp = $outlet->nama_ktp;
        $this->nik_ktp = $outlet->nik_ktp;
        $this->existing_foto_ktp = $outlet->foto_ktp;
        $this->nama_bank = $outlet->nama_bank;
        $this->no_rekening = $outlet->no_rekening;
        $this->nama_pemilik_norek = $outlet->nama_pemilik_norek;
        $this->existing_foto_toko = $outlet->foto_toko;
        $this->existing_foto_toko2 = $outlet->foto_toko2;
        $this->existing_foto_toko3 = $outlet->foto_toko3;
        $this->keterangan = $outlet->keterangan;
        $this->is_valid = (bool) $outlet->is_valid;
        
        $this->foto_ktp = null;
        $this->foto_toko = null;
        $this->foto_toko2 = null;
        $this->foto_toko3 = null;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    /**
     * Open detail modal
     */
    public function openDetailModal($id)
    {
        $this->selectedOutlet = RewardOutlet::findOrFail($id);
        $this->isDetailModalOpen = true;
    }

    private function resetForm()
    {
        $this->outletId = null;
        $this->region_code = '';
        $this->region_name = '';
        $this->area_code = '';
        $this->area_name = '';
        $this->branch_name = '';
        $this->eskalink_code = '';
        $this->customer_code = '';
        $this->customer_name = '';
        $this->alamat = '';
        $this->no_hp = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->nama_pemilik_toko = '';
        $this->nama_ktp = '';
        $this->nik_ktp = '';
        $this->foto_ktp = null;
        $this->existing_foto_ktp = null;
        $this->nama_bank = '';
        $this->no_rekening = '';
        $this->nama_pemilik_norek = '';
        $this->foto_toko = null;
        $this->existing_foto_toko = null;
        $this->foto_toko2 = null;
        $this->existing_foto_toko2 = null;
        $this->foto_toko3 = null;
        $this->existing_foto_toko3 = null;
        $this->keterangan = '';
        $this->is_valid = false;
    }

    /**
     * Save data (create/update)
     */
    public function save()
    {
        $this->validate();

        $data = [
            'region_code' => $this->region_code,
            'region_name' => $this->region_name,
            'area_code' => $this->area_code,
            'area_name' => $this->area_name,
            'branch_name' => $this->branch_name,
            'eskalink_code' => $this->eskalink_code,
            'customer_code' => $this->customer_code,
            'customer_name' => $this->customer_name,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'nama_pemilik_toko' => $this->nama_pemilik_toko,
            'nama_ktp' => $this->nama_ktp,
            'nik_ktp' => $this->nik_ktp,
            'nama_bank' => $this->nama_bank,
            'no_rekening' => $this->no_rekening,
            'nama_pemilik_norek' => $this->nama_pemilik_norek,
            'keterangan' => $this->keterangan,
            'is_valid' => $this->is_valid,
        ];

        // Handle Foto KTP
        if ($this->foto_ktp) {
            // Delete old file if exists
            if ($this->isEditing && $this->existing_foto_ktp) {
                Storage::disk('public')->delete($this->existing_foto_ktp);
            }
            $data['foto_ktp'] = $this->foto_ktp->store('rwo/ktp', 'public');
        }

        // Handle Foto Toko (by GPS)
        if ($this->foto_toko) {
            // Delete old file if exists
            if ($this->isEditing && $this->existing_foto_toko) {
                Storage::disk('public')->delete($this->existing_foto_toko);
            }
            $data['foto_toko'] = $this->foto_toko->store('rwo/toko', 'public');
        }

        // Handle Foto Toko 2 (Tampak Depan)
        if ($this->foto_toko2) {
            // Delete old file if exists
            if ($this->isEditing && $this->existing_foto_toko2) {
                Storage::disk('public')->delete($this->existing_foto_toko2);
            }
            $data['foto_toko2'] = $this->foto_toko2->store('rwo/toko', 'public');
        }

        // Handle Foto Toko 3 (Tampak Dalam)
        if ($this->foto_toko3) {
            // Delete old file if exists
            if ($this->isEditing && $this->existing_foto_toko3) {
                Storage::disk('public')->delete($this->existing_foto_toko3);
            }
            $data['foto_toko3'] = $this->foto_toko3->store('rwo/toko', 'public');
        }

        if ($this->isEditing) {
            $outlet = RewardOutlet::findOrFail($this->outletId);
            $outlet->update($data);
            session()->flash('message', 'Data RWO berhasil diperbarui.');
        } else {
            RewardOutlet::create($data);
            session()->flash('message', 'Data RWO baru berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->outletIdToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Delete item
     */
    public function delete()
    {
        $outlet = RewardOutlet::findOrFail($this->outletIdToDelete);
        
        // Delete files
        if ($outlet->foto_ktp) {
            Storage::disk('public')->delete($outlet->foto_ktp);
        }
        if ($outlet->foto_toko) {
            Storage::disk('public')->delete($outlet->foto_toko);
        }
        if ($outlet->foto_toko2) {
            Storage::disk('public')->delete($outlet->foto_toko2);
        }
        if ($outlet->foto_toko3) {
            Storage::disk('public')->delete($outlet->foto_toko3);
        }

        $outlet->delete();
        session()->flash('message', 'Data RWO berhasil dihapus.');
        $this->isDeleteModalOpen = false;
    }

    public function openImportModal()
    {
        $this->importFile = null;
        $this->isImportModalOpen = true;
    }

    /**
     * Import Excel
     */
    public function import()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB Max
        ]);

        try {
            $importer = new RewardOutletImport();
            Excel::import($importer, $this->importFile->getRealPath());

            session()->flash('message', "Berhasil mengimpor {$importer->importedCount} data RWO. (Lewat: {$importer->skippedCount} baris).");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }

        $this->isImportModalOpen = false;
    }

    /**
     * Export Excel
     */
    public function export()
    {
        $filters = [
            'search' => $this->search,
            'filter_type' => $this->filter_type,
            'filter_region_code' => $this->filter_region_code,
            'filter_area_code' => $this->filter_area_code,
            'filter_branch_name' => $this->filter_branch_name,
        ];
        return Excel::download(new RewardOutletExport($filters), 'reward_outlet_export.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\RewardOutletTemplateExport, 'template_import_rwo.xlsx');
    }

    public function getFotoKtpPreview()
    {
        if ($this->foto_ktp && method_exists($this->foto_ktp, 'temporaryUrl')) {
            try {
                return $this->foto_ktp->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function getFotoTokoPreview()
    {
        if ($this->foto_toko && method_exists($this->foto_toko, 'temporaryUrl')) {
            try {
                return $this->foto_toko->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function getFotoToko2Preview()
    {
        if ($this->foto_toko2 && method_exists($this->foto_toko2, 'temporaryUrl')) {
            try {
                return $this->foto_toko2->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function getFotoToko3Preview()
    {
        if ($this->foto_toko3 && method_exists($this->foto_toko3, 'temporaryUrl')) {
            try {
                return $this->foto_toko3->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function render()
    {
        $query = RewardOutlet::query();
        $this->applyRegionAccess($query);

        // Apply region, area, branch filters
        if (!empty($this->filter_region_code)) {
            $query->where('region_code', $this->filter_region_code);
        }
        if (!empty($this->filter_area_code)) {
            $query->where('area_code', $this->filter_area_code);
        }
        if (!empty($this->filter_branch_name)) {
            $query->where('branch_name', $this->filter_branch_name);
        }

        // Fetch KPI counts (based on region access & active filters, but before search & filter_type query)
        $kpiQuery = clone $query;
        $totalToko = $kpiQuery->count();
        
        $tanpaKtp = (clone $kpiQuery)->where(function($q) {
            $q->whereNull('nik_ktp')->orWhere('nik_ktp', '');
        })->count();
        
        $tanpaFotoKtp = (clone $kpiQuery)->where(function($q) {
            $q->whereNull('foto_ktp')->orWhere('foto_ktp', '');
        })->count();
        
        $tanpaRekening = (clone $kpiQuery)->where(function($q) {
            $q->whereNull('no_rekening')->orWhere('no_rekening', '');
        })->count();
        
        $tanpaFotoToko = (clone $kpiQuery)->where(function($q) {
            $q->whereNull('foto_toko')->orWhere('foto_toko', '')
              ->orWhereNull('foto_toko2')->orWhere('foto_toko2', '')
              ->orWhereNull('foto_toko3')->orWhere('foto_toko3', '');
        })->count();

        $tanpaTikor = (clone $kpiQuery)->where(function($q) {
            $q->whereNull('latitude')->orWhere('latitude', '')
              ->orWhereNull('longitude')->orWhere('longitude', '');
        })->count();

        $tidakValid = (clone $kpiQuery)->where('is_valid', false)->count();

        // Apply filter types
        if ($this->filter_type === 'tanpa_ktp') {
            $query->where(function($q) {
                $q->whereNull('nik_ktp')->orWhere('nik_ktp', '');
            });
        } elseif ($this->filter_type === 'tanpa_foto_ktp') {
            $query->where(function($q) {
                $q->whereNull('foto_ktp')->orWhere('foto_ktp', '');
            });
        } elseif ($this->filter_type === 'tanpa_rekening') {
            $query->where(function($q) {
                $q->whereNull('no_rekening')->orWhere('no_rekening', '');
            });
        } elseif ($this->filter_type === 'tanpa_foto_toko') {
            $query->where(function($q) {
                $q->whereNull('foto_toko')->orWhere('foto_toko', '')
                  ->orWhereNull('foto_toko2')->orWhere('foto_toko2', '')
                  ->orWhereNull('foto_toko3')->orWhere('foto_toko3', '');
            });
        } elseif ($this->filter_type === 'tanpa_tikor') {
            $query->where(function($q) {
                $q->whereNull('latitude')->orWhere('latitude', '')
                  ->orWhereNull('longitude')->orWhere('longitude', '');
            });
        } elseif ($this->filter_type === 'tidak_valid') {
            $query->where('is_valid', false);
        } elseif ($this->filter_type === 'valid') {
            $query->where('is_valid', true);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('region_code', 'ilike', '%' . $search . '%')
                  ->orWhere('region_name', 'ilike', '%' . $search . '%')
                  ->orWhere('area_code', 'ilike', '%' . $search . '%')
                  ->orWhere('area_name', 'ilike', '%' . $search . '%')
                  ->orWhere('branch_name', 'ilike', '%' . $search . '%')
                  ->orWhere('customer_code', 'ilike', '%' . $search . '%')
                  ->orWhere('customer_name', 'ilike', '%' . $search . '%')
                  ->orWhere('eskalink_code', 'ilike', '%' . $search . '%')
                  ->orWhere('nama_pemilik_toko', 'ilike', '%' . $search . '%');
            });
        }

        $outlets = $query->orderBy('id', 'desc')->paginate(10);

        return view('livewire.rwo.index', [
            'outlets' => $outlets,
            'kpis' => [
                'total_toko' => $totalToko,
                'tanpa_ktp' => $tanpaKtp,
                'tanpa_foto_ktp' => $tanpaFotoKtp,
                'tanpa_rekening' => $tanpaRekening,
                'tanpa_foto_toko' => $tanpaFotoToko,
                'tanpa_tikor' => $tanpaTikor,
                'tidak_valid' => $tidakValid,
            ],
        ])->layout('layouts.app');
    }
}
