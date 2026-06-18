<?php

namespace App\Livewire\Report\AnalisaKunjungan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Livewire\WithPagination;
use App\Models\RemarkAnalisaKunjungan;

class Index extends Component
{
    use EnforcesMenuPermissions, WithPagination;

    #[Title('Analisa Kunjungan')]
    protected string $menuRoute = 'report.analisa-kunjungan.index';

    public $regions = [];
    public $areas = [];
    public $supervisors = [];

    public $selectedRegion = '';
    public $selectedArea = '';
    public $selectedSupervisor = '';
    
    public $startDate = '';
    public $endDate = '';

    public $appliedRegion = '';
    public $appliedArea = '';
    public $appliedSupervisor = '';
    public $appliedStartDate = '';
    public $appliedEndDate = '';

    public $modalOrderQty = 0;
    public $modalOrderVal = 0;

    public $modalReasonType = '';
    public $modalReasonDesc = '';

    public $mapMasterLat = '';
    public $mapMasterLon = '';
    public $mapVisitLat = '';
    public $mapVisitLon = '';

    public $modalRemarkVisitId = '';
    public $modalRemarkMuid = '';
    public $modalRemarkCustno = '';
    public $modalRemarkTanggal = '';
    public $modalRemarkText = '';

    public function mount()
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        $this->loadRegions();
    }

    public function loadRegions()
    {
        $this->regions = DB::table('team_elite_code_mappings as t')
            ->leftJoin('master_regions as mr', 't.region_code', '=', 'mr.region_code')
            ->select('mr.region_code', 'mr.region_name')
            ->whereNotNull('mr.region_code')
            ->distinct()
            ->orderBy('mr.region_name')
            ->get()
            ->toArray();
    }

    public function updatedSelectedRegion($value)
    {
        $this->selectedArea = '';
        $this->selectedSupervisor = '';
        $this->areas = [];
        $this->supervisors = [];

        if ($value) {
            $this->areas = DB::table('team_elite_code_mappings as t')
                ->leftJoin('master_areas as ma', 't.area_code', '=', 'ma.area_code')
                ->where('t.region_code', $value)
                ->whereNotNull('ma.area_code')
                ->select('ma.area_code', 'ma.area_name')
                ->distinct()
                ->orderBy('ma.area_name')
                ->get()
                ->toArray();
        }
    }

    public function updatedSelectedArea($value)
    {
        $this->selectedSupervisor = '';
        $this->supervisors = [];

        if ($value) {
            $this->supervisors = DB::table('team_elite_code_mappings as t')
                ->leftJoin('fsalesman as f', 't.team_elite_code', '=', 'f.SLSNO')
                ->where('t.region_code', $this->selectedRegion)
                ->where('t.area_code', $value)
                ->whereNotNull('t.team_elite_code')
                ->select('t.team_elite_code as supervisor_code', 'f.SLSNAME as supervisor_name')
                ->distinct()
                ->orderBy('f.SLSNAME')
                ->get()
                ->toArray();
        }
    }

    public function applyFilter()
    {
        $this->appliedRegion = $this->selectedRegion;
        $this->appliedArea = $this->selectedArea;
        $this->appliedSupervisor = $this->selectedSupervisor;
        $this->appliedStartDate = $this->startDate;
        $this->appliedEndDate = $this->endDate;
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->selectedRegion = '';
        $this->selectedArea = '';
        $this->selectedSupervisor = '';
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-d');
        
        $this->appliedRegion = '';
        $this->appliedArea = '';
        $this->appliedSupervisor = '';
        $this->appliedStartDate = '';
        $this->appliedEndDate = '';
        
        $this->areas = [];
        $this->supervisors = [];
        $this->resetPage();
    }

    public function showOrderDetail($qty, $val)
    {
        $this->modalOrderQty = $qty;
        $this->modalOrderVal = $val;
        $this->dispatch('open-modal', 'modal-order');
    }

    public function showReasonDetail($type, $desc)
    {
        $this->modalReasonType = $type;
        $this->modalReasonDesc = $desc;
        $this->dispatch('open-modal', 'modal-reason');
    }

    public function showMap($masterLat, $masterLon, $visitLat, $visitLon)
    {
        $this->mapMasterLat = $masterLat;
        $this->mapMasterLon = $masterLon;
        $this->mapVisitLat = $visitLat;
        $this->mapVisitLon = $visitLon;
        $this->dispatch('open-modal', 'modal-map');
        $this->dispatch('init-map', [
            'masterLat' => $masterLat,
            'masterLon' => $masterLon,
            'visitLat' => $visitLat,
            'visitLon' => $visitLon
        ]);
    }

    public function openRemarkModal($visitId, $muid, $custno, $tanggal, $text)
    {
        $this->modalRemarkVisitId = $visitId;
        $this->modalRemarkMuid = $muid;
        $this->modalRemarkCustno = $custno;
        $this->modalRemarkTanggal = $tanggal;
        $this->modalRemarkText = $text;
        $this->dispatch('open-modal', 'modal-action-remark');
    }

    public function saveRemark()
    {
        RemarkAnalisaKunjungan::updateOrCreate(
            [
                'visit_id' => $this->modalRemarkVisitId,
                'muid' => $this->modalRemarkMuid,
                'custno' => $this->modalRemarkCustno,
                'tanggal' => $this->modalRemarkTanggal,
            ],
            [
                'remark' => $this->modalRemarkText,
                'created_by' => auth()->user()->username ?? 'system'
            ]
        );

        $this->dispatch('close-modal', 'modal-action-remark');
    }

    public function deleteRemark($visitId, $muid, $custno, $tanggal)
    {
        RemarkAnalisaKunjungan::where('visit_id', $visitId)
            ->where('muid', $muid)
            ->where('custno', $custno)
            ->where('tanggal', $tanggal)
            ->delete();
    }

    public function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;
        
        $earthRadius = 6371000; // in meters
        $latFrom = deg2rad((float)$lat1);
        $lonFrom = deg2rad((float)$lon1);
        $latTo = deg2rad((float)$lat2);
        $lonTo = deg2rad((float)$lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return round($angle * $earthRadius); // in meters
    }

    #[Computed]
    public function dataKunjungan()
    {
        if (empty($this->appliedRegion) && empty($this->appliedArea) && empty($this->appliedSupervisor)) {
            return collect();
        }

        $query = DB::table('rpt_visit_an_h as rvah')
            ->select(
                'rvah.ID as id',
                'rvah.MUID as supervisor_code',
                'rvah.MUNAME as supervisor_name',
                'rvah.CUSTNO as custno',
                'rvah.CUSTNAME as custname',
                'rvah.CUSTADD1 as address',
                'l.pilar as pilar',
                'l.target as target',
                DB::raw('rvah."TANGGAL"::date as tanggal'),
                DB::raw('(rvah."TIME_IN"::timestamp)::time as time_in'),
                DB::raw('(rvah."TIME_OUT"::timestamp)::time as time_out'),
                DB::raw('rvah."TIME_CONSUME"::time as time_consume'),
                DB::raw('rvah."TIME_TRAVEL"::time as time_travel'),
                DB::raw('rvah."TIME_PAUSE"::time as time_pause'),
                'rvah.ORDER_QTY as qty_order',
                'rvah.ORDER_VAL as val_order',
                'rvah.FLAG_PJP as flag_pjp',
                'rvah.FLAG_VISIT as flag_visit',
                'rvah.FLAG_EC as flag_ec',
                'rvah.FLAG_BUY as flag_buy',
                'rvah.FLAG_PAUSE as flag_pause',
                'rvah.M_LA as master_lat',
                'rvah.M_LG as master_lon',
                'rvah.V_LA as visit_lat',
                'rvah.V_LG as visit_lon',
                'rvar.REASON_TYPE as reason_type',
                'rvar.REASON_DESC as reason_desc',
                'rak.remark as action_remark'
            )
            ->leftJoin('rpt_visit_an_r as rvar', function($join) {
                $join->on('rvah.ID', '=', 'rvar.HID')
                     ->on('rvah.MUID', '=', 'rvar.MUID')
                     ->on('rvah.CUSTNO', '=', 'rvar.CUSTNO')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rvar.TANGGAL');
            })
            ->leftJoin('remark_analisa_kunjungan as rak', function($join) {
                $join->on('rvah.ID', '=', 'rak.visit_id')
                     ->on('rvah.MUID', '=', 'rak.muid')
                     ->on('rvah.CUSTNO', '=', 'rak.custno')
                     ->on(DB::raw('rvah."TANGGAL"::date'), '=', 'rak.tanggal');
            })
            ->leftJoin('list_toko_pareto_team_elite as l', 'l.customer_code_prc', '=', 'rvah.CUSTNO')
            ->leftJoin('team_elite_code_mappings as t', 't.team_elite_code', '=', 'rvah.MUID');

        if ($this->appliedRegion) {
            $query->where('t.region_code', $this->appliedRegion);
        }

        if ($this->appliedArea) {
            $query->where('t.area_code', $this->appliedArea);
        }

        if ($this->appliedSupervisor) {
            $query->where('rvah.MUID', $this->appliedSupervisor);
        }

        if ($this->appliedStartDate && $this->appliedEndDate) {
            $query->whereBetween(DB::raw('rvah."TANGGAL"::date'), [$this->appliedStartDate, $this->appliedEndDate]);
        } elseif ($this->appliedStartDate) {
            $query->where(DB::raw('rvah."TANGGAL"::date'), '>=', $this->appliedStartDate);
        } elseif ($this->appliedEndDate) {
            $query->where(DB::raw('rvah."TANGGAL"::date'), '<=', $this->appliedEndDate);
        }

        $query->orderBy(DB::raw('rvah."TANGGAL"::date'), 'asc')
              ->orderBy('rvah.ID', 'asc');

        return $query->get();
    }

    public function render()
    {
        return view('livewire.report.analisa-kunjungan.index', [
            'dataKunjungan' => $this->dataKunjungan
        ])->layout('layouts.app');
    }
}
