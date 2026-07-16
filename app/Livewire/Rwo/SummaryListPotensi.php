<?php

namespace App\Livewire\Rwo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SummaryListPotensiExport;

class SummaryListPotensi extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'rwo.listpotensirwo';
    protected string $menuPermission = 'RWO / Summary List Potensi';
    public $title = 'Summary List Potensi';

    public $kuartals = [];
    public $regions = [];
    public $areas = [];

    public $kuartal = '';
    public $region = '';
    public $area = '';
    
    public $jksDateStart = '';
    public $jksDateEnd = '';

    public function mount()
    {
        $this->kuartals = DB::table('master_calender')->select('quarter')->whereNotNull('quarter')->distinct()->orderBy('quarter')->get();
        
        $user = auth()->user();
        $regionQuery = DB::table('master_regions')->orderBy('region_name');
        if ($user && !$user->hasRole('admin') && !empty($user->region_code)) {
            $regionQuery->whereIn('region_code', (array) $user->region_code);
        }
        $this->regions = $regionQuery->get();

        $currentMonth = (int) date('n');
        $this->kuartal = (string) ceil($currentMonth / 3);
        
        // Default JKS date filter to current month
        $this->jksDateStart = date('Y-m-01');
        $this->jksDateEnd = date('Y-m-t');
    }

    public function updatedRegion($value)
    {
        $this->area = '';
        $this->areas = empty($value) ? [] : DB::table('master_areas')
            ->where('region_code', $value)
            ->orderBy('area_name')
            ->get();
    }

    private function applyAccessScope($query)
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            $query->where("md.supervisor_code", $user->supervisor_code);
        } elseif (!empty($user->area_code)) {
            $query->whereIn("md.area_code", (array) $user->area_code);
        } elseif (!empty($user->region_code)) {
            $query->whereIn("md.region_code", (array) $user->region_code);
        }

        return $query;
    }

    public function getRecords()
    {
        // Subquery to find unique customers in JKS within the date range
        $jksSubquery = DB::table('jks_team_elite')
            ->select('custno', 'kode_team')
            ->when($this->jksDateStart, fn($q) => $q->whereDate('tanggal', '>=', $this->jksDateStart))
            ->when($this->jksDateEnd, fn($q) => $q->whereDate('tanggal', '<=', $this->jksDateEnd))
            ->groupBy('custno', 'kode_team');

        $query = DB::table('list_potensi_rwo as l')
            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
            ->leftJoin('team_elite_code_mappings as te', 'te.siso_code', '=', 'md.supervisor_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->leftJoin('reward_outlet as r', 'r.customer_code', '=', 'l.customer_code')
            ->leftJoin('surat_kesepakatan_bersama_rwo as skb', function($join) {
                $join->on('skb.customer_code', '=', 'l.customer_code')
                     ->on('skb.kuartal', '=', 'l.kuartal');
            })
            ->leftJoinSub($jksSubquery, 'jks', function($join) {
                $join->on('jks.custno', '=', 'r.eskalink_code')
                     ->on('jks.kode_team', '=', 'te.team_elite_code');
            });

        $this->applyAccessScope($query);

        if ($this->kuartal) {
            $query->where('l.kuartal', $this->kuartal);
        }
        if ($this->region) {
            $query->where('md.region_code', $this->region);
        }
        if ($this->area) {
            $query->where('md.area_code', $this->area);
        }

        $query->select(
            'md.region_name',
            'md.area_name',
            'f.SLSNAME as supervisor_name',
            'md.distributor_code',
            'md.distributor_name',
            DB::raw('COUNT(DISTINCT l.customer_code) as total_toko'),
            DB::raw('SUM(l.total_target) as total_target'),
            DB::raw('COUNT(DISTINCT jks.custno) as total_jks'),
            DB::raw('COUNT(DISTINCT CASE WHEN skb.customer_code IS NOT NULL THEN skb.customer_code END) as sudah_skb'),
            DB::raw('COUNT(DISTINCT CASE WHEN skb.is_approved = true THEN skb.customer_code END) as skb_approve'),
            DB::raw('COUNT(DISTINCT CASE WHEN skb.is_approved = false THEN skb.customer_code END) as skb_reject'),
            DB::raw("COUNT(DISTINCT CASE WHEN 
                NULLIF(TRIM(r.no_hp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_pemilik_toko), '') IS NOT NULL AND
                NULLIF(TRIM(r.nik_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_ktp), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_bank), '') IS NOT NULL AND
                NULLIF(TRIM(r.no_rekening), '') IS NOT NULL AND
                NULLIF(TRIM(r.nama_pemilik_norek), '') IS NOT NULL AND
                NULLIF(TRIM(r.latitude), '') IS NOT NULL AND
                NULLIF(TRIM(r.longitude), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_toko2), '') IS NOT NULL AND
                NULLIF(TRIM(r.foto_toko3), '') IS NOT NULL
                THEN l.customer_code END) as data_lengkap")
        )
        ->groupBy('md.region_name', 'md.area_name', 'f.SLSNAME', 'md.distributor_code', 'md.distributor_name')
        ->orderBy('md.region_name')
        ->orderBy('md.area_name')
        ->orderBy('f.SLSNAME')
        ->orderBy('md.distributor_name');

        $rawRecords = $query->get();
        
        $groupedRecords = [];
        foreach ($rawRecords as $row) {
            $rKey = $row->region_name ?? '-';
            $aKey = $row->area_name ?? '-';
            $sKey = $row->supervisor_name ?? '-';

            // Init Region
            if (!isset($groupedRecords[$rKey])) {
                $groupedRecords[$rKey] = [
                    'name' => $rKey,
                    'total_toko' => 0, 'total_target' => 0, 'total_jks' => 0, 'sudah_skb' => 0, 'skb_approve' => 0, 'skb_reject' => 0,
                    'data_lengkap' => 0, 'data_belum' => 0,
                    'areas' => []
                ];
            }
            // Init Area
            if (!isset($groupedRecords[$rKey]['areas'][$aKey])) {
                $groupedRecords[$rKey]['areas'][$aKey] = [
                    'name' => $aKey,
                    'total_toko' => 0, 'total_target' => 0, 'total_jks' => 0, 'sudah_skb' => 0, 'skb_approve' => 0, 'skb_reject' => 0,
                    'data_lengkap' => 0, 'data_belum' => 0,
                    'supervisors' => []
                ];
            }
            // Init Supervisor
            if (!isset($groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey])) {
                $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey] = [
                    'name' => $sKey,
                    'total_toko' => 0, 'total_target' => 0, 'total_jks' => 0, 'sudah_skb' => 0, 'skb_approve' => 0, 'skb_reject' => 0,
                    'data_lengkap' => 0, 'data_belum' => 0,
                    'cabang' => []
                ];
            }

            $dataBelum = $row->total_toko - $row->data_lengkap;

            // Accumulate metrics for Region
            $groupedRecords[$rKey]['total_toko'] += $row->total_toko;
            $groupedRecords[$rKey]['total_target'] += $row->total_target;
            $groupedRecords[$rKey]['total_jks'] += $row->total_jks;
            $groupedRecords[$rKey]['sudah_skb'] += $row->sudah_skb;
            $groupedRecords[$rKey]['skb_approve'] += $row->skb_approve;
            $groupedRecords[$rKey]['skb_reject'] += $row->skb_reject;
            $groupedRecords[$rKey]['data_lengkap'] += $row->data_lengkap;
            $groupedRecords[$rKey]['data_belum'] += $dataBelum;

            // Accumulate metrics for Area
            $groupedRecords[$rKey]['areas'][$aKey]['total_toko'] += $row->total_toko;
            $groupedRecords[$rKey]['areas'][$aKey]['total_target'] += $row->total_target;
            $groupedRecords[$rKey]['areas'][$aKey]['total_jks'] += $row->total_jks;
            $groupedRecords[$rKey]['areas'][$aKey]['sudah_skb'] += $row->sudah_skb;
            $groupedRecords[$rKey]['areas'][$aKey]['skb_approve'] += $row->skb_approve;
            $groupedRecords[$rKey]['areas'][$aKey]['skb_reject'] += $row->skb_reject;
            $groupedRecords[$rKey]['areas'][$aKey]['data_lengkap'] += $row->data_lengkap;
            $groupedRecords[$rKey]['areas'][$aKey]['data_belum'] += $dataBelum;

            // Accumulate metrics for Supervisor
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['total_toko'] += $row->total_toko;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['total_target'] += $row->total_target;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['total_jks'] += $row->total_jks;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['sudah_skb'] += $row->sudah_skb;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['skb_approve'] += $row->skb_approve;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['skb_reject'] += $row->skb_reject;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['data_lengkap'] += $row->data_lengkap;
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['data_belum'] += $dataBelum;

            // Add Cabang
            $groupedRecords[$rKey]['areas'][$aKey]['supervisors'][$sKey]['cabang'][] = [
                'distributor_code' => $row->distributor_code,
                'distributor_name' => $row->distributor_name ?? '-',
                'total_toko' => $row->total_toko,
                'total_target' => $row->total_target,
                'total_jks' => $row->total_jks,
                'sudah_skb' => $row->sudah_skb,
                'skb_approve' => $row->skb_approve,
                'skb_reject' => $row->skb_reject,
                'data_lengkap' => $row->data_lengkap,
                'data_belum' => $dataBelum
            ];
        }

        return $groupedRecords;
    }

    public function render()
    {
        return view('livewire.rwo.summary-list-potensi', [
            'records' => $this->getRecords()
        ])->layout('layouts.app');
    }

    public function exportExcel()
    {
        $records = $this->getRecords();
        return Excel::download(new SummaryListPotensiExport($records), 'Summary_List_Potensi_RWO.xlsx');
    }
}
