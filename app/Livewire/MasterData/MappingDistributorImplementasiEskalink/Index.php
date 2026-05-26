<?php

namespace App\Livewire\MasterData\MappingDistributorImplementasiEskalink;

use Livewire\Component;

class Index extends Component
{
    use \Livewire\WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterRegion = '';
    public $filterArea = '';
    public $filterIsActive = '';
    public $filterIsImplementasi = '';

    public $isModalOpen = false;
    public $isEdit = false;
    public $editId = null;

    public $distributor_code;
    public $distributor_name;
    public $eskalink_code;
    public $eskalink_code_dist;
    public $implementasi = 'N';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterRegion() { $this->resetPage(); }
    public function updatingFilterArea() { $this->resetPage(); }
    public function updatingFilterIsActive() { $this->resetPage(); }
    public function updatingFilterIsImplementasi() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterRegion', 'filterArea', 'filterIsActive', 'filterIsImplementasi']);
        $this->resetPage();
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MappingDistributorImplementasiEskalinkExport(
                $this->search,
                $this->filterRegion,
                $this->filterArea,
                $this->filterIsActive,
                $this->filterIsImplementasi
            ), 
            'mapping_distributor_eskalink.xlsx'
        );
    }

    public function create()
    {
        $this->reset(['distributor_code', 'distributor_name', 'eskalink_code', 'eskalink_code_dist', 'implementasi', 'editId']);
        $this->isEdit = false;
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $data = \App\Models\DistributorImplementasiEskalink::findOrFail($id);
        $this->editId = $data->id;
        $this->distributor_code = $data->distributor_code;
        $this->distributor_name = $data->distributor_name;
        $this->eskalink_code = $data->eskalink_code;
        $this->eskalink_code_dist = $data->eskalink_code_dist;
        $this->implementasi = $data->implementasi ?? 'N';

        $this->isEdit = true;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate([
            'distributor_code' => 'required|max:15',
            'eskalink_code' => 'nullable|max:15',
            'eskalink_code_dist' => 'nullable|max:100',
            'implementasi' => 'nullable|max:15',
        ]);

        if (!$this->isEdit) {
            $master = \App\Models\MasterDistributor::where('distributor_code', $this->distributor_code)->first();
            $this->distributor_name = $master ? $master->distributor_name : null;
        }

        \App\Models\DistributorImplementasiEskalink::updateOrCreate(
            ['id' => $this->editId],
            [
                'distributor_code' => $this->distributor_code,
                'distributor_name' => $this->distributor_name,
                'eskalink_code' => $this->eskalink_code,
                'eskalink_code_dist' => $this->eskalink_code_dist,
                'implementasi' => $this->implementasi,
            ]
        );

        $this->closeModal();
        session()->flash('message', $this->isEdit ? 'Data berhasil diubah.' : 'Data berhasil ditambahkan.');
    }

    public function delete($id)
    {
        \App\Models\DistributorImplementasiEskalink::find($id)?->delete();
        session()->flash('message', 'Data berhasil dihapus.');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->reset(['distributor_code', 'distributor_name', 'eskalink_code', 'eskalink_code_dist', 'implementasi', 'editId']);
    }

    public function render()
    {
        $unmappedDistributors = [];
        if ($this->isModalOpen && !$this->isEdit) {
            $unmappedDistributors = \App\Models\MasterDistributor::select('master_distributors.distributor_code', 'master_distributors.distributor_name')
                ->leftJoin('distributor_implementasi_eskalink as die', 'die.distributor_code', '=', 'master_distributors.distributor_code')
                ->whereNull('die.distributor_code')
                ->where('master_distributors.is_active', true)
                ->orderBy('master_distributors.distributor_name')
                ->get();
        }

        $query = \App\Models\DistributorImplementasiEskalink::query()
            ->select([
                'distributor_implementasi_eskalink.id',
                'master_distributors.region_code',
                'master_distributors.region_name',
                'master_distributors.area_code',
                'master_distributors.area_name',
                'distributor_implementasi_eskalink.distributor_code',
                'distributor_implementasi_eskalink.distributor_name',
                'master_distributors.branch_code',
                'master_distributors.branch_name',
                'distributor_implementasi_eskalink.eskalink_code',
                'distributor_implementasi_eskalink.eskalink_code_dist',
                'distributor_implementasi_eskalink.implementasi',
                'master_distributors.is_active'
            ])
            ->leftJoin('master_distributors', 'distributor_implementasi_eskalink.distributor_code', '=', 'master_distributors.distributor_code')
            ->where('distributor_implementasi_eskalink.distributor_code', '!=', 'HOINA');

        if ($this->search !== '') {
            $query->where(function($q) {
                $q->where('distributor_implementasi_eskalink.distributor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_implementasi_eskalink.distributor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_implementasi_eskalink.eskalink_code', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_implementasi_eskalink.eskalink_code_dist', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->filterRegion !== '') {
            $query->where('master_distributors.region_name', $this->filterRegion);
        }

        if ($this->filterArea !== '') {
            $query->where('master_distributors.area_name', $this->filterArea);
        }

        if ($this->filterIsActive !== '') {
            if ($this->filterIsActive === '1') {
                $query->where('master_distributors.is_active', true);
            } elseif ($this->filterIsActive === '0') {
                $query->where(function($q) {
                    $q->where('master_distributors.is_active', false)
                      ->orWhereNull('master_distributors.is_active');
                });
            }
        }

        if ($this->filterIsImplementasi !== '') {
            if ($this->filterIsImplementasi === '1') {
                $query->where('distributor_implementasi_eskalink.implementasi', 'Y');
            } elseif ($this->filterIsImplementasi === '0') {
                $query->where(function($q) {
                    $q->where('distributor_implementasi_eskalink.implementasi', '!=', 'Y')
                      ->orWhereNull('distributor_implementasi_eskalink.implementasi');
                });
            }
        }

        // Hitung KPI menggunakan clone dari query setelah filter diterapkan (sebelum pagination)
        $kpiQuery = clone $query;
        $totalDist = $kpiQuery->count();

        $kpiQueryActive = clone $query;
        $totalDistActive = $kpiQueryActive->where('master_distributors.is_active', true)->count();

        $kpiQueryImpl = clone $query;
        $totalImplementasi = $kpiQueryImpl->where('distributor_implementasi_eskalink.implementasi', 'Y')->count();

        $kpiQueryImplActive = clone $query;
        $totalImplementasiActive = $kpiQueryImplActive
            ->where('distributor_implementasi_eskalink.implementasi', 'Y')
            ->where('master_distributors.is_active', true)
            ->count();

        $regions = \App\Models\MasterDistributor::select('region_name')->distinct()->whereNotNull('region_name')->orderBy('region_name')->pluck('region_name');
        $areas = \App\Models\MasterDistributor::select('area_name')->distinct()->whereNotNull('area_name')->orderBy('area_name')->pluck('area_name');

        return view('livewire.master-data.mapping-distributor-implementasi-eskalink.index', [
            'data' => $query->orderBy('master_distributors.region_name', 'asc')
              ->orderBy('master_distributors.area_name', 'asc')
              ->orderBy('master_distributors.branch_name', 'asc')
              ->orderBy('distributor_implementasi_eskalink.distributor_name', 'asc')
              ->paginate(10),
            'regions' => $regions,
            'areas' => $areas,
            'kpi' => [
                'total_dist' => $totalDist,
                'total_dist_active' => $totalDistActive,
                'total_implementasi' => $totalImplementasi,
                'total_implementasi_active' => $totalImplementasiActive
            ],
            'unmappedDistributors' => $unmappedDistributors
        ])->layout('layouts.app');
    }
}
