<?php

namespace App\Livewire\MasterBranches;

use Livewire\Component;
use App\Models\MasterBranch;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $branchIdToDelete;

    protected $queryString = ['search'];

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     * Karena tabel master_branches tidak memiliki region_code, 
     * kita memfilternya melalui relasi bertingkat 'supervisor.area'.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('supervisor.area', function ($areaQuery) use ($user) {
                $areaQuery->whereIn('region_code', $user->region_code);
            });
        }

        return $query;
    }

    /**
     * Reset paginasi saat pencarian diketik
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Tambahkan leftJoin bertingkat agar kita bisa mengurutkan berdasarkan region_code
        $query = MasterBranch::with(['supervisor.area.region'])
            ->select('master_branches.*') // Penting agar data relasi tidak menimpa model branch
            ->leftJoin('master_supervisors', 'master_branches.supervisor_code', '=', 'master_supervisors.supervisor_code')
            ->leftJoin('master_areas', 'master_supervisors.area_code', '=', 'master_areas.area_code')
            ->where('master_branches.branch_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan cabang yang terkait dengan region 'national'

        // 1. Terapkan proteksi hak akses region terlebih dahulu
        $this->applyRegionAccess($query);

        // 2. Terapkan filter pencarian (Ditambahkan prefix tabel agar tidak ambiguous akibat join)
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('master_branches.branch_code', 'ilike', '%' . $this->search . '%')
                    ->orWhere('master_branches.branch_name', 'ilike', '%' . $this->search . '%')
                    ->orWhereHas('supervisor', function ($supervisorQuery) {
                        $supervisorQuery
                            ->where('supervisor_name', 'ilike', '%' . $this->search . '%')
                            ->orWhere('description', 'ilike', '%' . $this->search . '%')
                            ->orWhereHas('area', function ($areaQuery) {
                                $areaQuery
                                    ->where('area_name', 'ilike', '%' . $this->search . '%')
                                    ->orWhereHas('region', function ($regionQuery) {
                                        $regionQuery->where(
                                            'region_name',
                                            'ilike',
                                            '%' . $this->search . '%'
                                        );
                                    });
                            });
                    });
            });
        }

        // 3. Urutkan berdasarkan region_code dari tabel area, lalu kode cabang
        $branches = $query->orderBy('master_areas.region_code', 'asc')
                          ->orderBy('master_areas.area_name', 'asc')
                          ->orderBy('master_supervisors.supervisor_name', 'asc')
                          ->orderBy('master_supervisors.description', 'asc')
                          ->paginate(10);

        return view('livewire.master-branches.index', [
            'branches' => $branches,
        ])->layout('layouts.app');
    }


    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($branchId)
    {
        $this->branchIdToDelete = $branchId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data cabang.
     */
    public function delete()
    {
        // Security Check: Pastikan user hanya bisa menghapus data yang ada di dalam hak aksesnya
        $query = MasterBranch::query();
        $this->applyRegionAccess($query);
        
        // Cari data cabang, handle kemungkinan primary key berupa 'id' atau 'branch_code'
        $branch = $query->find($this->branchIdToDelete) 
               ?? $query->where('branch_code', $this->branchIdToDelete)->first();

        if ($branch) {
            $branch->delete();
            session()->flash('message', 'Cabang berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus cabang ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}