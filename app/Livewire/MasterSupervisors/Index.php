<?php

namespace App\Livewire\MasterSupervisors;

use Livewire\Component;
use App\Models\MasterSupervisor;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $supervisorIdToDelete;

    protected $queryString = ['search'];

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     * Karena tabel master_supervisors tidak memiliki region_code langsung, 
     * kita memfilternya melalui relasi 'area'.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('area', function ($areaQuery) use ($user) {
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
        // Tambahkan leftJoin ke master_areas agar kita bisa mengurutkan berdasarkan region_code
        $query = MasterSupervisor::with(['area', 'area.region'])
            ->select('master_supervisors.*') // Penting agar data area tidak menimpa model supervisor
            ->leftJoin('master_areas', 'master_supervisors.area_code', '=', 'master_areas.area_code')
            ->where('master_supervisors.supervisor_code', '!=', 'HOINA');

        // 1. Terapkan proteksi hak akses region terlebih dahulu
        $this->applyRegionAccess($query);

        // 2. Terapkan filter pencarian (Ditambahkan prefix tabel agar tidak ambiguous akibat join)
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

        // 3. Urutkan berdasarkan region_code dari tabel area, lalu kode supervisor
        $supervisors = $query->orderBy('master_areas.region_code', 'asc')
                                ->orderBy('master_supervisors.supervisor_name', 'asc')
                             ->paginate(10);

        return view('livewire.master-supervisors.index', [
            'supervisors' => $supervisors,
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
        // Security Check: Pastikan user hanya bisa menghapus data yang ada di dalam hak aksesnya
        $query = MasterSupervisor::query();
        $this->applyRegionAccess($query);
        
        // Cari data supervisor, handle kemungkinan primary key berupa 'id' atau 'supervisor_code'
        $supervisor = $query->find($this->supervisorIdToDelete) 
                   ?? $query->where('supervisor_code', $this->supervisorIdToDelete)->first();

        if ($supervisor) {
            $supervisor->delete();
            session()->flash('message', 'Supervisor berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus supervisor ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}