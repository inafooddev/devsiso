<?php

namespace App\Livewire\MasterRegions;

use Livewire\Component;
use App\Models\MasterRegion;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Menambahkan baris ini untuk menggunakan tema paginasi Tailwind
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $isDeleteModalOpen = false;
    public $regionIdToDelete;

    protected $queryString = ['search'];

    /**
     * Helper untuk memfilter Query berdasarkan hak akses region user.
     */
    private function applyRegionAccess($query)
    {
        $user = auth()->user();

        // Jika bukan admin dan memiliki batasan region_code (array)
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }

        return $query;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = MasterRegion::query()
        ->where('region_code', '!=', 'HOINA'); // Pastikan untuk mengecualikan region 'national' dari query utama

        // 1. Terapkan proteksi hak akses region terlebih dahulu
        $this->applyRegionAccess($query);

        // 2. Terapkan filter pencarian (Harus dibungkus closure agar tidak merusak proteksi)
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('region_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('region_name', 'ilike', '%' . $this->search . '%');
            });
        }

        $regions = $query->latest('region_code')->paginate(10);

        return view('livewire.master-regions.index', [
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
        // Security Check: Pastikan user hanya bisa menghapus data yang ada di dalam hak aksesnya
        $query = MasterRegion::query();
        $this->applyRegionAccess($query);
        
        $region = $query->where('region_code', $this->regionIdToDelete)->first() 
               ?? $query->find($this->regionIdToDelete);

        if ($region) {
            $region->delete();
            session()->flash('message', 'Region berhasil dihapus.');
        } else {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus region ini.');
        }

        $this->isDeleteModalOpen = false;
    }
}