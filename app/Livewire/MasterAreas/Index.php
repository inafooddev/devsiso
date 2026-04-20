<?php

namespace App\Livewire\MasterAreas;

use Livewire\Component;
use App\Models\MasterArea;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $areaIdToDelete;

    protected $queryString = ['search'];

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            // Karena tabel master_areas memiliki relasi dan kolom region_code
            $query->whereIn('region_code', $user->region_code);
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
        $query = MasterArea::with('region')
        ->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan area yang terkait dengan region 'national'

        // 1. Terapkan proteksi hak akses region terlebih dahulu
        $this->applyRegionAccess($query);

        // 2. Terapkan filter pencarian (Sudah aman karena dibungkus closure oleh Anda)
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('area_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('area_name', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('region', function($subQuery) {
                      $subQuery->where('region_name', 'ilike', '%' . $this->search . '%');
                  });
            });
        }

        $areas = $query->latest('area_code')->paginate(10);

        return view('livewire.master-areas.index', [
            'areas' => $areas,
        ])->layout('layouts.app');
    }

    /**
     * Membuka modal konfirmasi hapus.
     */
    public function confirmDelete($areaId)
    {
        $this->areaIdToDelete = $areaId;
        $this->isDeleteModalOpen = true;
    }

    /**
     * Menghapus data area.
     */
    public function delete()
    {
        // Security Check: Pastikan user hanya bisa menghapus data yang ada di dalam hak aksesnya
        $query = MasterArea::query();
        $this->applyRegionAccess($query);
        
        // Cari data area, handle kemungkinan primary key berupa 'id' atau 'area_code'
        $area = $query->find($this->areaIdToDelete) 
             ?? $query->where('area_code', $this->areaIdToDelete)->first();

        if ($area) {
            $area->delete();
            session()->flash('message', 'Area berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus area ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}