<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;
use App\Models\InsentifMasterDistributor;
use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InsentifGlobalExport;

class ExportModal extends Component
{
    public $isOpen = false;
    public $filterBulan = '';
    public $filterRegion = '';
    public $filterArea = []; // Multi-select array
    public $selectedSheets = ['SE', 'SPV', 'KACAB', 'SUMMARY'];

    #[On('openExportModal')]
    public function openModal()
    {
        $this->isOpen = true;
        
        if (!$this->filterBulan) {
            $latest = InsentifMasterDistributor::max('bulan');
            $this->filterBulan = $latest ?: date('Y-m');
        }

        if (!$this->filterRegion) {
            $firstRegion = InsentifMasterDistributor::whereNotNull('region_name')->orderBy('region_name')->value('region_name');
            $this->filterRegion = $firstRegion ?? '';
        }
        
        $this->filterArea = [];
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
        $listBulan = InsentifMasterDistributor::select('bulan')->distinct()->orderBy('bulan', 'desc')->pluck('bulan');
        $listRegions = InsentifMasterDistributor::select('region_name')->whereNotNull('region_name')->distinct()->orderBy('region_name')->pluck('region_name');
        
        $listAreas = collect();
        if ($this->filterRegion) {
            $listAreas = InsentifMasterDistributor::where('region_name', $this->filterRegion)
                ->whereNotNull('area_name')
                ->select('area_name')
                ->distinct()
                ->orderBy('area_name')
                ->pluck('area_name');
        }

        return view('livewire.others.insentif.perhitungan.export-modal', [
            'listBulan' => $listBulan,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
        ]);
    }
}
