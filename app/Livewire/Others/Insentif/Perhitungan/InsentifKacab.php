<?php

namespace App\Livewire\Others\Insentif\Perhitungan;

use Livewire\Component;
use App\Models\InsentifMasterDistributor;
use App\Models\TargetKacab;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Livewire\Attributes\On;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InsentifKacabExport;

class InsentifKacab extends Component
{
    public $filterBulan;
    public $filterRegion;
    public $filterArea;
    public $search = '';

    public function mount()
    {
        // Default to latest month available or current month
        $latest = InsentifMasterDistributor::max('bulan');
        $this->filterBulan = $latest ?: date('Y-m');

        $firstRegion = DB::table('insentif_master_distributors')
            ->whereNotNull('region_name')
            ->orderBy('region_name')
            ->value('region_name');
            
        $this->filterRegion = $firstRegion ?? '';
    }

    public function updatedFilterRegion()
    {
        $this->filterArea = '';
    }

    #[On('refreshKacabData')]
    public function refreshData()
    {
        // This will trigger a re-render automatically
    }

    public function render()
    {
        $yearFilter = Carbon::parse($this->filterBulan . '-01')->format('Y');

        // 1. Get Master Distributors for this month
        $query = InsentifMasterDistributor::where('bulan', $this->filterBulan);

        if ($this->filterRegion) {
            $query->where('region_name', $this->filterRegion);
        }

        if (!empty($this->filterArea)) {
            if (is_array($this->filterArea)) {
                $query->whereIn('area_name', $this->filterArea);
            } else {
                $query->where('area_name', $this->filterArea);
            }
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('cabang', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('area_name', 'ilike', '%' . $this->search . '%');
            });
        }

        $masterData = $query->orderBy('area_name')->orderBy('cabang')->get();

        // 2. Get Targets for the year
        $targets = TargetKacab::where('tahun', $yearFilter)->get()->keyBy(function($item) {
            return strtoupper(trim($item->cabang));
        });

        // 3. Get Actuals (Sell Out) for the month per distributor
        $actuals = DB::table('insentif_value_per_salesmans')
            ->select('distributor_code', DB::raw('SUM(actual) as total_actual'))
            ->where('bulan', $this->filterBulan)
            ->groupBy('distributor_code')
            ->get()
            ->keyBy(function($item) {
                return strtoupper(trim($item->distributor_code));
            });

        // Build map of cabang -> distCode from masterData
        $cabangToDistCode = [];
        foreach ($masterData as $md) {
            $c = strtoupper(trim($md->cabang));
            $dc = strtoupper(trim($md->distributor_code));
            $cabangToDistCode[$c] = $dc;
        }

        // 4. Apply Mappings
        $mappings = \App\Models\InsentifKacabMapping::all();
        $childToParentMap = $mappings->pluck('parent_cabang', 'child_cabang')->toArray();
        $parentToChildrenMap = [];
        foreach ($mappings as $m) {
            $parentToChildrenMap[$m->parent_cabang][] = $m->child_cabang;
        }

        foreach ($childToParentMap as $child => $parent) {
            $childDistCode = $cabangToDistCode[$child] ?? null;
            $parentDistCode = $cabangToDistCode[$parent] ?? null;

            if ($childDistCode && $actuals->has($childDistCode)) {
                $childActual = $actuals->get($childDistCode)->total_actual;
                if ($parentDistCode) {
                    if ($actuals->has($parentDistCode)) {
                        $actuals->get($parentDistCode)->total_actual += $childActual;
                    } else {
                        $actuals->put($parentDistCode, (object)['distributor_code' => $parentDistCode, 'total_actual' => $childActual]);
                    }
                }
            }
        }

        $kacabData = [];
        $totalTarget = 0;
        $totalInsentif = 0;
        $totalSellOut = 0;
        $totalNilaiInsentif = 0;
        $totalPph = 0;
        $totalTrf = 0;

        foreach ($masterData as $md) {
            $cabang = strtoupper(trim($md->cabang));
            $distCode = strtoupper(trim($md->distributor_code));
            
            // Skip rendering child cabangs
            if (array_key_exists($cabang, $childToParentMap)) {
                continue;
            }

            $targetData = $targets->get($cabang);
            $target = $targetData ? (float) $targetData->target : 0;
            $insentif = $targetData ? (float) $targetData->insentif : 0;
            $namaKacab = $targetData ? $targetData->nama_kacab : '-';

            $actualData = $actuals->get($distCode);
            $sellOut = $actualData ? (float) $actualData->total_actual : 0;

            // Rename cabang if it has children mapped to it
            $displayCabang = $cabang;
            if (isset($parentToChildrenMap[$cabang])) {
                $displayCabang .= ', ' . implode(', ', $parentToChildrenMap[$cabang]);
            }

            $percentage = $target > 0 ? ($sellOut / $target) * 100 : 0;
            
            // Logic: Jika % >= 100, maka full insentif
            $nilaiInsentif = $percentage >= 100 ? $insentif : 0;
            $pph = $nilaiInsentif * 0.05;
            $trf = $nilaiInsentif - $pph;

            $kacabData[] = [
                'area_name' => $md->area_name,
                'distributor_name' => $md->distributor_name,
                'cabang' => $displayCabang,
                'nama_kacab' => $namaKacab,
                'target' => $target,
                'insentif' => $insentif,
                'sell_out' => $sellOut,
                'percentage' => $percentage,
                'nilai_insentif' => $nilaiInsentif,
                'pph' => $pph,
                'trf' => $trf,
            ];

            $totalTarget += $target;
            $totalInsentif += $insentif;
            $totalSellOut += $sellOut;
            $totalNilaiInsentif += $nilaiInsentif;
            $totalPph += $pph;
            $totalTrf += $trf;
        }

        // List Bulan for Filter
        $listBulan = InsentifMasterDistributor::select('bulan')->distinct()->orderBy('bulan', 'desc')->pluck('bulan');
        if ($listBulan->isEmpty()) {
            $listBulan = collect([$this->filterBulan]);
        }

        $listRegions = InsentifMasterDistributor::select('region_name')->whereNotNull('region_name')->distinct()->orderBy('region_name')->pluck('region_name');
        
        $listAreas = collect();
        if ($this->filterRegion) {
            $listAreas = InsentifMasterDistributor::select('area_name')
                ->where('region_name', $this->filterRegion)
                ->whereNotNull('area_name')
                ->distinct()
                ->orderBy('area_name')
                ->pluck('area_name');
        }

        return view('livewire.others.insentif.perhitungan.insentif-kacab', [
            'kacabData' => collect($kacabData),
            'listBulan' => $listBulan,
            'listRegions' => $listRegions,
            'listAreas' => $listAreas,
            'totals' => [
                'target' => $totalTarget,
                'insentif' => $totalInsentif,
                'sell_out' => $totalSellOut,
                'nilai_insentif' => $totalNilaiInsentif,
                'pph' => $totalPph,
                'trf' => $totalTrf,
            ]
        ]);
    }
}
