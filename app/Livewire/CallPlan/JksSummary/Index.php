<?php

namespace App\Livewire\CallPlan\JksSummary;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Traits\WithCallPlanFilters;

class Index extends Component
{
    use WithPagination;
    use WithCallPlanFilters;

    #[Title('Summary JKS')]
    #[Layout('layouts.app')]

    
    public function mount()
    {
        if (session()->has('jks_summary_state')) {
            $state = session()->get('jks_summary_state');
            $this->appliedRegion = $state['appliedRegion'] ?? '';
            $this->appliedArea = $state['appliedArea'] ?? '';
            $this->appliedSupervisor = $state['appliedSupervisor'] ?? '';
            $this->appliedDistributor = $state['appliedDistributor'] ?? '';
            $this->appliedBulan = $state['appliedBulan'] ?? date('Y-m-01');
            
            $this->selectedRegion = $state['selectedRegion'] ?? '';
            $this->selectedArea = $state['selectedArea'] ?? '';
            $this->selectedSupervisor = $state['selectedSupervisor'] ?? '';
            $this->selectedDistributor = $state['selectedDistributor'] ?? '';
            $this->selectedBulan = $state['selectedBulan'] ?? date('Y-m-01');

            if (isset($state['page'])) {
                $this->setPage($state['page']);
            }
        }

        $this->applyDefaultFiltersForRestrictedUser();
    }

    protected function applyDefaultFiltersForRestrictedUser()
    {
        $user = auth()->user();
        if ($user && !$user->hasRole(['admin', 'spm'])) {
            $accessLevel = $user->getAccessLevel();
            
            if (in_array($accessLevel, ['region', 'area', 'supervisor'])) {
                // Determine region from user profile
                if (!empty($user->region_code) && count((array)$user->region_code) == 1 && empty($this->appliedRegion)) {
                    $this->appliedRegion = ((array)$user->region_code)[0];
                }
            }
            if (in_array($accessLevel, ['area', 'supervisor'])) {
                // Determine area from user profile
                if (!empty($user->area_code) && count((array)$user->area_code) == 1 && empty($this->appliedArea)) {
                    $this->appliedArea = ((array)$user->area_code)[0];
                    
                    if ($accessLevel === 'area' && empty($this->appliedRegion)) {
                        $areaData = \Illuminate\Support\Facades\DB::table('master_distributors')
                            ->where('area_code', $this->appliedArea)
                            ->select('region_code')
                            ->first();
                        if ($areaData) {
                            $this->appliedRegion = $areaData->region_code;
                        }
                    }
                }
            }
            if ($accessLevel === 'supervisor') {
                if (!empty($user->supervisor_code) && empty($this->appliedSupervisor)) {
                    $this->appliedSupervisor = $user->supervisor_code;
                    
                    // Fetch supervisor's area and region from master_distributors
                    $spvData = \Illuminate\Support\Facades\DB::table('master_distributors as md')
                        ->join('team_elite_code_mappings as te', 'md.supervisor_code', '=', 'te.siso_code')
                        ->where('te.team_elite_code', $user->supervisor_code)
                        ->select('md.region_code', 'md.area_code')
                        ->first();
                        
                    if ($spvData) {
                        if (empty($this->appliedRegion)) {
                            $this->appliedRegion = $spvData->region_code;
                        }
                        if (empty($this->appliedArea)) {
                            $this->appliedArea = $spvData->area_code;
                        }
                    }
                }
            }
        }
    }

    public function dehydrate()
    {
        session()->put('jks_summary_state', [
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedSupervisor' => $this->appliedSupervisor,
            'appliedDistributor' => $this->appliedDistributor,
            'appliedBulan' => $this->appliedBulan,
            'selectedRegion' => $this->selectedRegion,
            'selectedArea' => $this->selectedArea,
            'selectedSupervisor' => $this->selectedSupervisor,
            'selectedDistributor' => $this->selectedDistributor,
            'selectedBulan' => $this->selectedBulan,
            'page' => $this->paginators['page'] ?? $this->getPage(),
        ]);
    }

    public function resetFilters()
    {
        $this->reset([
            'selectedRegion', 'selectedArea', 'selectedSupervisor', 'selectedBulan',
            'appliedRegion', 'appliedArea', 'appliedSupervisor', 'appliedBulan'
        ]);
        
        $this->appliedBulan = date('Y-m-01');

        if (session()->has('jks_summary_state')) {
            session()->forget('jks_summary_state');
        }

        $this->applyDefaultFiltersForRestrictedUser();

        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.call-plan.jks-summary.index', [
            'regionOptions' => $this->filterRegions,
            'areaOptions' => $this->filterAreas,
            'supervisorOptions' => $this->filterSupervisors,
            'supervisorOptions' => $this->filterSupervisors,
            'summaryData' => $this->summaryData,
        ]);
    }

    public function updatedAppliedBulan()
    {
        $this->reset(['appliedRegion', 'appliedArea', 'appliedSupervisor']);
        $this->applyDefaultFiltersForRestrictedUser();
        $this->resetPage();
    }

    public function updatedAppliedRegion()
    {
        $this->reset(['appliedArea', 'appliedSupervisor']);
        $this->resetPage();
    }

    public function updatedAppliedArea()
    {
        $this->reset(['appliedSupervisor']);
        $this->resetPage();
    }

    public function updatedAppliedSupervisor()
    {
        $this->resetPage();
    }

    protected function applyHierarchyAccess($query)
    {
        $user = auth()->user();
        if (!$user) return $query;
        
        if ($user->hasRole(['admin', 'spm'])) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            // Map the team_elite_code (from users table) to siso_code (used in master_distributors)
            $sisoCodes = \Illuminate\Support\Facades\DB::table('team_elite_code_mappings')
                           ->where('team_elite_code', $user->supervisor_code)
                           ->pluck('siso_code');
            return $query->whereIn('md.supervisor_code', $sisoCodes);
        }

        if (!empty($user->area_code) && count((array) $user->area_code) > 0) {
            return $query->whereIn('md.area_code', (array) $user->area_code);
        }

        if (!empty($user->region_code) && count((array) $user->region_code) > 0) {
            return $query->whereIn('md.region_code', (array) $user->region_code);
        }

        return $query;
    }

    #[Computed]
    public function filterRegions()
    {
        $query = DB::table('master_regions as mr')
            ->select('mr.region_code', 'mr.region_name')
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('master_distributors as md')
                  ->whereColumn('md.region_code', 'mr.region_code')
                  ->where('md.is_active', true);
                
                $this->applyHierarchyAccess($q);
            });
            
        return $query->orderBy('mr.region_name')->get();
    }

    #[Computed]
    public function filterAreas()
    {
        $query = DB::table('master_areas as ma')
            ->select('ma.area_code', 'ma.area_name');
            
        if ($this->appliedRegion) {
            $query->where('ma.region_code', $this->appliedRegion);
        }
        
        $query->whereExists(function($q) {
            $q->select(DB::raw(1))
                  ->from('master_distributors as md')
                  ->whereColumn('md.area_code', 'ma.area_code')
                  ->where('md.is_active', true);
                  
            $this->applyHierarchyAccess($q);
        });
            
        return $query->orderBy('ma.area_name')->get();
    }

    #[Computed]
    public function filterSupervisors()
    {
        $query = DB::table('team_elite_code_mappings as te')
            ->join('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->select('te.team_elite_code as supervisor_code', 'f.SLSNAME as description')
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('master_distributors as md')
                  ->whereColumn('md.supervisor_code', 'te.siso_code')
                  ->where('md.is_active', true);
                
                // Dependency Filter
                if ($this->appliedRegion) $q->where('md.region_code', $this->appliedRegion);
                if ($this->appliedArea) $q->where('md.area_code', $this->appliedArea);
                
                // Hierarki/RBAC
                $this->applyHierarchyAccess($q);
            });

        return $query->orderBy('f.SLSNAME')->get();
    }


    
    #[Computed]
    public function summaryData()
    {
        $bulan = $this->appliedBulan ?: date('Y-m-01');

        $query = DB::table('master_distributors as md')
            ->leftJoin('team_elite_code_mappings as te', 'md.supervisor_code', '=', 'te.siso_code')
            ->leftJoin('fsalesman as f', 'f.SLSNO', '=', 'te.team_elite_code')
            ->join('salesmans as s', 'md.distributor_code', '=', 's.distributor_code')
            ->leftJoin('jks_salesmans as js', function($join) use ($bulan) {
                $join->on('s.salesman_code', '=', 'js.salesman_code')
                     ->where('js.bulan', 'like', $bulan . '%');
            })
            ->leftJoin('list_toko_pareto_team_elite as lt', function($join) {
                $join->on('lt.uniq_kd', '=', 'js.customer_code')
                     ->on('lt.distributor_code', '=', 'js.distributor_code');
            })
            ->select(
                'md.region_code', 'md.region_name',
                'md.area_code', 'md.area_name',
                'te.team_elite_code as supervisor_code',
                'f.SLSNAME as supervisor_name',
                'md.distributor_code', 'md.distributor_name',
                's.salesman_code', 's.salesman_name',
                
                // Total RO & JKS
                DB::raw('COUNT(DISTINCT js.customer_code) as total_ro'),
                DB::raw("COUNT(DISTINCT CASE WHEN (js.h1 = 'Y' OR js.h2 = 'Y' OR js.h3 = 'Y' OR js.h4 = 'Y' OR js.h5 = 'Y' OR js.h6 = 'Y' OR js.h7 = 'Y') AND (js.w1 = 'Y' OR js.w2 = 'Y' OR js.w3 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as total_jks"),
                DB::raw("COUNT(DISTINCT CASE WHEN NOT ((js.h1 = 'Y' OR js.h2 = 'Y' OR js.h3 = 'Y' OR js.h4 = 'Y' OR js.h5 = 'Y' OR js.h6 = 'Y' OR js.h7 = 'Y') AND (js.w1 = 'Y' OR js.w2 = 'Y' OR js.w3 = 'Y' OR js.w4 = 'Y')) THEN js.customer_code END) as non_rute"),
                DB::raw("COUNT(DISTINCT CASE WHEN (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as non_gps"),
                
                // Senin
                DB::raw("COUNT(DISTINCT CASE WHEN js.h1 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN js.customer_code END) as senin_gjl"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h1 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as senin_gnp"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h1 = 'Y' AND (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as senin_non_gps"),
                
                // Selasa
                DB::raw("COUNT(DISTINCT CASE WHEN js.h2 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN js.customer_code END) as selasa_gjl"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h2 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as selasa_gnp"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h2 = 'Y' AND (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as selasa_non_gps"),

                // Rabu
                DB::raw("COUNT(DISTINCT CASE WHEN js.h3 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN js.customer_code END) as rabu_gjl"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h3 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as rabu_gnp"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h3 = 'Y' AND (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as rabu_non_gps"),

                // Kamis
                DB::raw("COUNT(DISTINCT CASE WHEN js.h4 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN js.customer_code END) as kamis_gjl"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h4 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as kamis_gnp"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h4 = 'Y' AND (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as kamis_non_gps"),

                // Jumat
                DB::raw("COUNT(DISTINCT CASE WHEN js.h5 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN js.customer_code END) as jumat_gjl"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h5 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as jumat_gnp"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h5 = 'Y' AND (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as jumat_non_gps"),

                // Sabtu
                DB::raw("COUNT(DISTINCT CASE WHEN js.h6 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN js.customer_code END) as sabtu_gjl"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h6 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as sabtu_gnp"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h6 = 'Y' AND (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as sabtu_non_gps"),

                // Minggu
                DB::raw("COUNT(DISTINCT CASE WHEN js.h7 = 'Y' AND (js.w1 = 'Y' OR js.w3 = 'Y') THEN js.customer_code END) as minggu_gjl"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h7 = 'Y' AND (js.w2 = 'Y' OR js.w4 = 'Y') THEN js.customer_code END) as minggu_gnp"),
                DB::raw("COUNT(DISTINCT CASE WHEN js.h7 = 'Y' AND (lt.latitude IS NULL OR lt.latitude = 0 OR lt.longitude IS NULL OR lt.longitude = 0) THEN js.customer_code END) as minggu_non_gps")
            )
            ->where('md.is_active', true)
            ->where('s.salesman_code', 'not ilike', '%OFI%')
            ->groupBy(
                'md.region_code', 'md.region_name',
                'md.area_code', 'md.area_name',
                'te.team_elite_code', 'f.SLSNAME',
                'md.distributor_code', 'md.distributor_name',
                's.salesman_code', 's.salesman_name'
            )
            ->orderBy('md.region_name')
            ->orderBy('md.area_name')
            ->orderBy('f.SLSNAME')
            ->orderBy('md.distributor_name')
            ->orderBy('s.salesman_name');

        $query = $this->applyHierarchyAccess($query);

        // Apply Filters
        if ($this->appliedRegion) $query->where('md.region_code', $this->appliedRegion);
        if ($this->appliedArea) $query->where('md.area_code', $this->appliedArea);
        if ($this->appliedSupervisor) $query->where('te.team_elite_code', $this->appliedSupervisor);


        $jksData = $query->paginate(50);
        
        // --- Approvals ---
        // Get salesman codes in current page to fetch their approvals
        $salesmanCodes = collect($jksData->items())->pluck('salesman_code')->unique()->toArray();
        
        $approvals = collect();
        if (!empty($salesmanCodes)) {
            $rawApprovals = DB::table('jks_approvals')
                ->where('status', 'APPROVED')
                ->where('payload->bulan', $bulan)
                ->orderBy('created_at', 'asc')
                ->get();
                
            $storeStates = [];
            $rawCounts = [];
            
            foreach ($salesmanCodes as $sCode) {
                $storeStates[$sCode] = [];
                $rawCounts[$sCode] = ['penambahan' => 0, 'perubahan' => 0, 'deleted' => 0];
            }

            foreach ($rawApprovals as $app) {
                $payload = json_decode($app->payload, true);
                if (!$payload) continue;
                
                $action = $app->action_type;
                $tokoCount = isset($payload['tokos']) && is_array($payload['tokos']) ? count($payload['tokos']) : 1;

                if ($action === 'TAMBAH_JADWAL') {
                    $sCode = $payload['salesman_code'] ?? null;
                    if ($sCode && in_array($sCode, $salesmanCodes)) {
                        if (isset($payload['tokos']) && is_array($payload['tokos'])) {
                            foreach ($payload['tokos'] as $toko) {
                                $cCode = $toko['code'] ?? $toko['customer_code'] ?? null;
                                if ($cCode) {
                                    $currentState = $storeStates[$sCode][$cCode] ?? null;
                                    if ($currentState === 'DELETED') {
                                        $storeStates[$sCode][$cCode] = 'PERUBAHAN'; // Input kembali
                                    } else {
                                        $storeStates[$sCode][$cCode] = 'PENAMBAHAN';
                                    }
                                } else {
                                    $rawCounts[$sCode]['penambahan'] += 1;
                                }
                            }
                        } else {
                            $rawCounts[$sCode]['penambahan'] += $tokoCount;
                        }
                    }
                } elseif (in_array($action, ['HAPUS_JADWAL', 'DELETE_MASSAL', 'DELETE_INDIVIDU'])) {
                    if (isset($payload['records']) && is_array($payload['records'])) {
                        foreach ($payload['records'] as $record) {
                            $sCode = $record['salesman_code'] ?? null;
                            if ($sCode && in_array($sCode, $salesmanCodes)) {
                                $cCode = $record['customer_code'] ?? null;
                                if ($cCode) {
                                    $currentState = $storeStates[$sCode][$cCode] ?? null;
                                    if ($currentState === 'PENAMBAHAN') {
                                        unset($storeStates[$sCode][$cCode]); // Cancel out
                                    } else {
                                        $storeStates[$sCode][$cCode] = 'DELETED';
                                    }
                                } else {
                                    $rawCounts[$sCode]['deleted'] += 1;
                                }
                            }
                        }
                    } else {
                        // Sangat lama fallback
                        $sCodes = [];
                        if (isset($payload['salesman_code'])) $sCodes[] = $payload['salesman_code'];
                        foreach ($sCodes as $sCode) {
                            if (in_array($sCode, $salesmanCodes)) {
                                $rawCounts[$sCode]['deleted'] += $tokoCount;
                            }
                        }
                    }
                } elseif (in_array($action, ['UBAH_JADWAL', 'TUKAR_JADWAL', 'TUKAR_HARI', 'TUKAR_MINGGU', 'TUKAR_SALESMAN'])) {
                    $sCodes = [];
                    if (isset($payload['salesman_code'])) $sCodes[] = $payload['salesman_code'];
                    if (isset($payload['salesman_asal'])) $sCodes[] = $payload['salesman_asal'];
                    if (isset($payload['salesman_tujuan'])) $sCodes[] = $payload['salesman_tujuan'];
                    if (isset($payload['records']) && is_array($payload['records'])) {
                        foreach ($payload['records'] as $record) {
                            if (isset($record['salesman_code'])) $sCodes[] = $record['salesman_code'];
                        }
                    }
                    foreach (array_unique($sCodes) as $sCode) {
                        if (in_array($sCode, $salesmanCodes)) {
                            $rawCounts[$sCode]['perubahan'] += $tokoCount;
                        }
                    }
                }
            }

            $appCounts = [];
            foreach ($salesmanCodes as $sCode) {
                $penambahan = $rawCounts[$sCode]['penambahan'];
                $perubahan = $rawCounts[$sCode]['perubahan'];
                $deleted = $rawCounts[$sCode]['deleted'];

                foreach ($storeStates[$sCode] as $cCode => $state) {
                    if ($state === 'PENAMBAHAN') $penambahan++;
                    elseif ($state === 'PERUBAHAN') $perubahan++;
                    elseif ($state === 'DELETED') $deleted++;
                }

                $appCounts[$sCode] = [
                    'penambahan' => $penambahan,
                    'perubahan' => $perubahan,
                    'deleted' => $deleted,
                ];
            }
            $approvals = collect($appCounts);
        }

        // Attach approvals
        foreach ($jksData->items() as $item) {
            $app = $approvals->get($item->salesman_code);
            $item->penambahan = $app['penambahan'] ?? 0;
            $item->perubahan = $app['perubahan'] ?? 0;
            $item->deleted = $app['deleted'] ?? 0;
        }

        return $jksData;
    }
}
