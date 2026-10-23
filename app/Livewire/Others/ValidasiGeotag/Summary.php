<?php

namespace App\Livewire\Others\ValidasiGeotag;

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Traits\EnforcesMenuPermissions;
use App\Services\ValidasiGeotagService;

class Summary extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'others.validasi-geotag';

    public bool $canManage = false;
    public bool $canExport = false;

    #[Url]
    public string $search = '';

    #[Url]
    public string $regionFilter = '';

    #[Url]
    public string $areaFilter = '';

    #[Url]
    public string $groupBy = 'distributor'; // 'distributor' | 'area' | 'region'

    public array $regions = [];
    public array $areas = [];

    protected $service;

    public function boot(ValidasiGeotagService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->authorizeAction('can_view');
        $user = auth()->user();
        // Seluruh user terautentikasi yang mengakses halaman ini diizinkan melakukan validasi / export
        $this->canManage = (bool) $user;
        $this->canExport = (bool) $user;

        $this->loadFilterChain();
    }

    /**
     * Muat opsi filter cascading berdasarkan territory scope user.
     */
    public function loadFilterChain()
    {
        $user = auth()->user();
        $this->regions = $this->service->getAccessibleRegions($user);

        // Jika user hanya punya 1 region (misal role region), otomatis pilihkan
        if (count($this->regions) === 1 && empty($this->regionFilter)) {
            $this->regionFilter = (string) array_key_first($this->regions);
        }

        if (!empty($this->regionFilter)) {
            $this->areas = $this->service->getAccessibleAreas($user, $this->regionFilter);
            if (count($this->areas) === 1 && empty($this->areaFilter)) {
                $this->areaFilter = (string) array_key_first($this->areas);
            }
        } else {
            $this->areas = [];
        }
    }

    /**
     * Handler saat Region diubah: cascade ke Area.
     */
    public function updatedRegionFilter($value)
    {
        $this->areaFilter = '';
        $user = auth()->user();

        if (!empty($value)) {
            $this->areas = $this->service->getAccessibleAreas($user, $value);
            if (count($this->areas) === 1) {
                $this->areaFilter = (string) array_key_first($this->areas);
            }
        } else {
            $this->areas = [];
        }
    }

    public function setGroupBy(string $group)
    {
        if (in_array($group, ['distributor', 'area', 'region'])) {
            $this->groupBy = $group;
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'regionFilter', 'areaFilter']);
        $this->groupBy = 'distributor';
        $this->loadFilterChain();
    }

    /**
     * Export rekapitulasi matriks summary sesuai grouping & filter ke file Excel (.xlsx)
     */
    public function export()
    {
        $user = auth()->user();

        $filters = [
            'search' => $this->search,
            'region' => $this->regionFilter,
            'area' => $this->areaFilter,
        ];

        $matrix = $this->service->getSummaryMatrix($user, $filters, $this->groupBy);

        if (empty($matrix)) {
            $this->dispatch('show-toast', type: 'warning', message: 'Tidak ada data ringkasan yang dapat diekspor.');
            return;
        }

        $filename = 'validasi_geotag_summary_' . $this->groupBy . '_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ValidasiGeotagSummaryExport($matrix, $this->groupBy),
            $filename
        );
    }

    public function render()
    {
        $user = auth()->user();

        $filters = [
            'search' => $this->search,
            'region' => $this->regionFilter,
            'area' => $this->areaFilter,
        ];

        $kpi = $this->service->getSummaryMetrics($user, $filters);
        $matrix = $this->service->getSummaryMatrix($user, $filters, $this->groupBy);

        return view('livewire.others.validasi-geotag.summary', [
            'kpi' => $kpi,
            'matrix' => $matrix,
            'regions' => $this->regions,
            'areas' => $this->areas,
            'groupBy' => $this->groupBy,
        ])->layout('layouts.app');
    }
}
