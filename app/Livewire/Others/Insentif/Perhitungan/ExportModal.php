<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;
use App\Models\InsentifMasterDistributor;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InsentifGlobalExport;
use Illuminate\Support\Facades\Auth;

class ExportModal extends Component
{
    public $isOpen = false;
    public $filterBulan = '';
    public $filterRegion = '';
    public $filterArea = []; // Multi-select array
    public $selectedSheets = ['SE', 'SPV', 'KACAB', 'SUMMARY'];

    protected $lockedRegions = null;
    protected $lockedAreas = null;

    private function loadAccessRights()
    {
        $user = Auth::user();
        if (!$user) return;
        
        $level = $user->getAccessLevel();

        if ($level === 'region') {
            $regionCodes = (array) $user->region_code;
            $this->lockedRegions = InsentifMasterDistributor::whereIn('region_code', $regionCodes)
                ->whereNotNull('region_name')
                ->distinct()
                ->pluck('region_name')
                ->toArray();
        } elseif ($level === 'area') {
            $areaCodes = (array) $user->area_code;
            $rows = InsentifMasterDistributor::whereIn('area_code', $areaCodes)
                ->whereNotNull('area_name')
                ->distinct()
                ->get(['region_name', 'area_name']);

            $this->lockedAreas   = $rows->pluck('area_name')->unique()->values()->toArray();
            $this->lockedRegions = $rows->pluck('region_name')->unique()->values()->toArray();
        }
    }

    #[On('openExportModal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->loadAccessRights();
        
        if (!$this->filterBulan) {
            $latest = InsentifMasterDistributor::max('bulan');
            $this->filterBulan = $latest ?: date('Y-m');
        }

        if (!$this->filterRegion) {
            if ($this->lockedRegions !== null && count($this->lockedRegions) > 0) {
                $this->filterRegion = $this->lockedRegions[0];
            } else {
                $firstRegion = InsentifMasterDistributor::whereNotNull('region_name')->orderBy('region_name')->value('region_name');
                $this->filterRegion = $firstRegion ?? '';
            }
        }
        
        $this->filterArea = [];
        if ($this->lockedAreas !== null && count($this->lockedAreas) === 1) {
            $this->filterArea = [$this->lockedAreas[0]];
        }
        
        $this->selectedSheets = ['SE', 'SPV', 'KACAB', 'SUMMARY'];
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function updatedFilterRegion()
    {
        $this->filterArea = []; // Reset areas when region changes
    }

    public function download()
    {
        $this->validate([
            'filterBulan' => 'required',
            'filterRegion' => 'required',
            'selectedSheets' => 'required|array|min:1',
        ], [
            'selectedSheets.required' => 'Anda harus memilih minimal 1 lembar laporan (Sheet).',
            'selectedSheets.min' => 'Anda harus memilih minimal 1 lembar laporan (Sheet).',
        ]);

        $this->isOpen = false;

        return Excel::download(new InsentifGlobalExport($this->filterBulan, $this->filterRegion, $this->filterArea, $this->selectedSheets), "Insentif_All_{$this->filterRegion}_{$this->filterBulan}.xlsx");
    }

    public function render()
    {
        $this->loadAccessRights();
        $user = Auth::user();
        $accessLevel = $user ? $user->getAccessLevel() : '';

        $listBulan = InsentifMasterDistributor::select('bulan')->distinct()->orderBy('bulan', 'desc')->pluck('bulan');
        
        $regionQuery = InsentifMasterDistributor::select('region_name')->whereNotNull('region_name')->distinct()->orderBy('region_name');
        if ($this->lockedRegions !== null) {
            $regionQuery->whereIn('region_name', $this->lockedRegions);
        }
        $listRegions = $regionQuery->pluck('region_name');
        
        $listAreas = collect();
        if ($this->filterRegion) {
            $areaQuery = InsentifMasterDistributor::where('region_name', $this->filterRegion)
                ->whereNotNull('area_name')
                ->select('area_name')
                ->distinct()
                ->orderBy('area_name');
            
            if ($this->lockedAreas !== null) {
                $areaQuery->whereIn('area_name', $this->lockedAreas);
            }
            $listAreas = $areaQuery->pluck('area_name');
        }

        return view('livewire.others.insentif.perhitungan.export-modal', [
            'listBulan' => $listBulan,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
            'accessLevel' => $accessLevel,
        ]);
    }
}
