<?php

namespace App\Livewire\CallPlan\JksMaps;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Traits\WithCallPlanFilters;
use App\Services\JksMapService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    use WithCallPlanFilters;

    #[Title('Maps JKS')]
    #[Layout('layouts.app')]

    // Layer and View Controls
    public $filterWeek = ''; // '', 'w1', 'w2', 'w3', 'w4'
    public $filterDay = '';  // '', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7'
    public $colorMode = 'salesman'; // 'salesman', 'day'
    public $activeSalesmanCodes = [];
    public $showRouteLines = true;
    public $showNonRoute = false;
    public $showDepot = true;

    // Palet Warna Legend & Titik Peta (By Hari)
    public const DAY_PALETTE = [
        'h1' => ['label' => 'Senin (H1)', 'color' => '#2563EB', 'short' => 'Sen'],
        'h2' => ['label' => 'Selasa (H2)', 'color' => '#059669', 'short' => 'Sel'],
        'h3' => ['label' => 'Rabu (H3)', 'color' => '#D97706', 'short' => 'Rab'],
        'h4' => ['label' => 'Kamis (H4)', 'color' => '#EA580C', 'short' => 'Kam'],
        'h5' => ['label' => 'Jumat (H5)', 'color' => '#7C3AED', 'short' => 'Jum'],
        'h6' => ['label' => 'Sabtu (H6)', 'color' => '#E11D48', 'short' => 'Sab'],
        'h7' => ['label' => 'Minggu (H7)', 'color' => '#64748B', 'short' => 'Min'],
        'multi' => ['label' => 'Multi-Hari', 'color' => '#0D9488', 'short' => 'Multi'],
        'none' => ['label' => 'Tanpa Hari', 'color' => '#94A3B8', 'short' => '-'],
    ];

    // Search and Drawer State
    public $drawerTab = 'salesman'; // 'salesman', 'anomali', 'store'
    public $selectedCustomerCode = '';
    public $selectedStore = null;
    public $selectedStoreType = 'jks'; // 'jks', 'non_jks'

    // Interactive Action Modals
    public $showReassignModal = false;
    public $reassignTargetSalesman = '';
    public $reassignReason = '';

    public $showEditScheduleModal = false;
    public $editHari = ['h1' => false, 'h2' => false, 'h3' => false, 'h4' => false, 'h5' => false, 'h6' => false, 'h7' => false];
    public $editMinggu = ['w1' => false, 'w2' => false, 'w3' => false, 'w4' => false];
    public $editReason = '';

    public $showAddNonRouteModal = false;
    public $addSalesman = '';
    public $addHari = [];
    public $addMinggu = [];

    // Access control
    public $accessLevel = 'nasional';
    public $canEdit = false;
    public $canAdd = false;

    #[Computed]
    public function isCurrentRunningMonth(): bool
    {
        if (empty($this->appliedBulan)) {
            return false;
        }
        return substr($this->appliedBulan, 0, 7) === now()->format('Y-m');
    }

    #[Computed]
    public function canManipulate(): bool
    {
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return true;
        }
        return $this->isCurrentRunningMonth();
    }

    public function mount()
    {
        $this->canEdit = auth()->check() && auth()->user()->hasMenuAccess('call-plan.jks-salesmans', 'can_edit');
        $this->canAdd = auth()->check() && auth()->user()->hasMenuAccess('call-plan.jks-salesmans', 'can_add');

        $user = auth()->user();
        if ($user) {
            $this->accessLevel = $user->getAccessLevel();
        }

        // Default: Selalu tampilkan bulan berjalan (Format YYYY-MM)
        $this->appliedBulan = now()->format('Y-m');
        $this->selectedBulan = now()->format('Y-m');

        if (session()->has('jks_maps_state')) {
            $state = session()->get('jks_maps_state');
            $this->appliedRegion = $state['appliedRegion'] ?? '';
            $this->appliedArea = $state['appliedArea'] ?? '';
            $this->appliedSupervisor = $state['appliedSupervisor'] ?? '';
            $this->appliedDistributor = $state['appliedDistributor'] ?? '';
            $this->filterWeek = $state['filterWeek'] ?? '';
            $this->filterDay = $state['filterDay'] ?? '';
            $this->showRouteLines = $state['showRouteLines'] ?? true;
            $this->showNonRoute = $state['showNonRoute'] ?? false;
        }

        $this->applyDefaultFiltersForRestrictedUser();
        $this->syncActiveSalesmen();
    }

    public function resetFilters()
    {
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->appliedSupervisor = '';
        $this->selectedSupervisor = '';
        $this->appliedArea = '';
        $this->selectedArea = '';
        $this->appliedRegion = '';
        $this->selectedRegion = '';
        $this->appliedBulan = now()->format('Y-m');
        $this->selectedBulan = now()->format('Y-m');
        $this->filterWeek = '';
        $this->filterDay = '';
        $this->colorMode = 'salesman';
        $this->activeSalesmanCodes = [];
        $this->selectedStore = null;
        $this->selectedCustomerCode = '';

        if (session()->has('jks_maps_state')) {
            session()->forget('jks_maps_state');
        }

        $this->dispatchMapRefresh();
    }

    protected function applyDefaultFiltersForRestrictedUser()
    {
        $user = auth()->user();
        if ($user && !$user->hasRole(['admin', 'spm'])) {
            $accessLevel = $user->getAccessLevel();
            if (in_array($accessLevel, ['region', 'area', 'supervisor'])) {
                if (!empty($user->region_code) && count((array)$user->region_code) == 1 && empty($this->appliedRegion)) {
                    $this->appliedRegion = ((array)$user->region_code)[0];
                    $this->selectedRegion = $this->appliedRegion;
                }
            }
            if (in_array($accessLevel, ['area', 'supervisor'])) {
                if (!empty($user->area_code) && count((array)$user->area_code) == 1 && empty($this->appliedArea)) {
                    $this->appliedArea = ((array)$user->area_code)[0];
                    $this->selectedArea = $this->appliedArea;
                }
            }
        }

        // Auto select first distributor if empty
        if (empty($this->appliedDistributor)) {
            $distributors = $this->filterDistributors();
            if ($distributors->count() > 0) {
                $this->appliedDistributor = $distributors->first()->distributor_code;
                $this->selectedDistributor = $this->appliedDistributor;
            }
        }
    }

    public function dehydrate()
    {
        session()->put('jks_maps_state', [
            'appliedRegion' => $this->appliedRegion,
            'appliedArea' => $this->appliedArea,
            'appliedSupervisor' => $this->appliedSupervisor,
            'appliedDistributor' => $this->appliedDistributor,
            'appliedBulan' => $this->appliedBulan,
            'filterWeek' => $this->filterWeek,
            'filterDay' => $this->filterDay,
            'showRouteLines' => $this->showRouteLines,
            'showNonRoute' => $this->showNonRoute,
        ]);
    }

    public function dispatchMapRefresh()
    {
        $this->dispatch('refresh-map', data: $this->mapGeoJson());
    }

    public function updatedAppliedRegion()
    {
        $this->selectedRegion = $this->appliedRegion;
        $this->appliedArea = '';
        $this->selectedArea = '';
        $this->appliedSupervisor = '';
        $this->selectedSupervisor = '';
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->syncActiveSalesmen();
        $this->selectedStore = null;
        $this->selectedCustomerCode = '';
        $this->dispatchMapRefresh();
    }

    public function updatedAppliedArea()
    {
        $this->selectedArea = $this->appliedArea;
        $this->appliedSupervisor = '';
        $this->selectedSupervisor = '';
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->syncActiveSalesmen();
        $this->selectedStore = null;
        $this->selectedCustomerCode = '';
        $this->dispatchMapRefresh();
    }

    public function updatedAppliedSupervisor()
    {
        $this->selectedSupervisor = $this->appliedSupervisor;
        $this->appliedDistributor = '';
        $this->selectedDistributor = '';
        $this->syncActiveSalesmen();
        $this->selectedStore = null;
        $this->selectedCustomerCode = '';
        $this->dispatchMapRefresh();
    }

    public function updatedAppliedDistributor()
    {
        $this->selectedDistributor = $this->appliedDistributor;

        // Auto-sync hierarki jika distributor dipilih langsung
        if (!empty($this->appliedDistributor)) {
            $distInfo = DB::table('master_distributors')
                ->where('distributor_code', $this->appliedDistributor)
                ->first();

            if ($distInfo) {
                if (!empty($distInfo->region_code)) {
                    $this->appliedRegion = $distInfo->region_code;
                    $this->selectedRegion = $this->appliedRegion;
                }
                if (!empty($distInfo->area_code)) {
                    $this->appliedArea = $distInfo->area_code;
                    $this->selectedArea = $this->appliedArea;
                }
                if (!empty($distInfo->supervisor_code)) {
                    $this->appliedSupervisor = $distInfo->supervisor_code;
                    $this->selectedSupervisor = $this->appliedSupervisor;
                }
            }
        }

        $this->syncActiveSalesmen();
        $this->selectedStore = null;
        $this->selectedCustomerCode = '';
        $this->dispatchMapRefresh();
    }

    public function updatedAppliedBulan()
    {
        $this->selectedBulan = $this->appliedBulan;
        $this->syncActiveSalesmen();
        $this->selectedStore = null;
        $this->selectedCustomerCode = '';
        $this->dispatchMapRefresh();
    }

    public function updatedFilterWeek()
    {
        $this->dispatchMapRefresh();
    }

    public function updatedFilterDay()
    {
        $this->dispatchMapRefresh();
    }

    public function updatedShowRouteLines()
    {
        $this->dispatchMapRefresh();
    }

    public function updatedShowNonRoute()
    {
        $this->dispatchMapRefresh();
    }

    public function updatedActiveSalesmanCodes()
    {
        $this->dispatchMapRefresh();
    }

    public function updatedColorMode()
    {
        $this->dispatchMapRefresh();
    }

    public function setColorMode($mode)
    {
        if (in_array($mode, ['salesman', 'day'])) {
            $this->colorMode = $mode;
            $this->dispatchMapRefresh();
        }
    }

    public function syncActiveSalesmen()
    {
        if (empty($this->appliedDistributor)) {
            $this->activeSalesmanCodes = [];
            return;
        }

        $service = app(JksMapService::class);
        $territories = $service->getSalesmanTerritories($this->appliedDistributor, $this->appliedBulan);
        $this->activeSalesmanCodes = array_keys($territories);
    }

    public function toggleSalesman($code)
    {
        if (in_array($code, $this->activeSalesmanCodes)) {
            $this->activeSalesmanCodes = array_values(array_diff($this->activeSalesmanCodes, [$code]));
        } else {
            $this->activeSalesmanCodes[] = $code;
        }
        $this->dispatchMapRefresh();
    }

    public function toggleAllSalesmen($enable = true)
    {
        if ($enable) {
            $service = app(JksMapService::class);
            $territories = $service->getSalesmanTerritories($this->appliedDistributor, $this->appliedBulan);
            $this->activeSalesmanCodes = array_keys($territories);
        } else {
            $this->activeSalesmanCodes = [];
        }
        $this->dispatchMapRefresh();
    }

    #[Computed]
    public function filterRegions()
    {
        $query = DB::table('master_regions')->select('region_code', 'region_name');
        $query = $this->applyHierarchyAccess($query, 'master_regions.');
        return $query->orderBy('region_name')->get();
    }

    #[Computed]
    public function filterAreas()
    {
        $query = DB::table('master_areas')->select('area_code', 'area_name');
        $region = $this->appliedRegion ?: $this->selectedRegion;
        if ($region) {
            $query->where('region_code', $region);
        }
        $query = $this->applyHierarchyAccess($query, 'master_areas.');
        return $query->orderBy('area_name')->get();
    }

    #[Computed]
    public function filterSupervisors()
    {
        $query = DB::table('master_supervisors')->select('supervisor_code', 'description');
        $area = $this->appliedArea ?: $this->selectedArea;
        $region = $this->appliedRegion ?: $this->selectedRegion;

        if ($area) {
            $query->where('area_code', $area);
        } elseif ($region) {
            $areaCodes = DB::table('master_areas')->where('region_code', $region)->pluck('area_code');
            $query->whereIn('area_code', $areaCodes);
        }

        $user = auth()->user();
        if ($user && !$user->hasRole(['admin', 'spm'])) {
            $accessLevel = $user->getAccessLevel();
            if ($accessLevel === 'region' && !empty($user->region_code)) {
                $areaCodes = DB::table('master_areas')->whereIn('region_code', (array)$user->region_code)->pluck('area_code');
                $query->whereIn('area_code', $areaCodes);
            } elseif ($accessLevel === 'area' && !empty($user->area_code)) {
                $query->whereIn('area_code', (array)$user->area_code);
            } elseif ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
                $query->whereIn('supervisor_code', (array)$user->supervisor_code);
            }
        }

        return $query->orderBy('description')->get();
    }

    #[Computed]
    public function filterDistributors()
    {
        $targetBulan = substr($this->appliedBulan, 0, 7);

        $query = DB::table('master_distributors as md')
            ->select('md.distributor_code', 'md.distributor_name', 'md.region_code', 'md.area_code', 'md.supervisor_code')
            ->where('md.is_active', true)
            ->whereExists(function($q) use ($targetBulan) {
                $q->select(DB::raw(1))
                  ->from('jks_salesmans as js')
                  ->whereColumn('js.distributor_code', 'md.distributor_code');
                if (!empty($targetBulan)) {
                    $q->where('js.bulan', 'like', $targetBulan . '%');
                }
            });

        $query = $this->applyHierarchyAccess($query, 'md.');
        return $query->orderBy('md.distributor_name')->get();
    }

    protected function applyHierarchyAccess($query, $prefix = '')
    {
        $user = auth()->user();
        if (!$user || $user->hasRole(['admin', 'spm'])) return $query;

        $accessLevel = $user->getAccessLevel();

        if ($accessLevel === 'region' && !empty($user->region_code)) {
            return $query->whereIn($prefix . 'region_code', (array) $user->region_code);
        }
        if ($accessLevel === 'area' && !empty($user->area_code)) {
            return $query->whereIn($prefix . 'area_code', (array) $user->area_code);
        }
        if ($accessLevel === 'supervisor' && !empty($user->supervisor_code)) {
            return $query->whereIn($prefix . 'supervisor_code', (array) $user->supervisor_code);
        }

        return $query;
    }

    #[Computed]
    public function salesmanTerritories(): array
    {
        if (empty($this->appliedDistributor)) {
            return [];
        }
        $service = app(JksMapService::class);
        return $service->getSalesmanTerritories($this->appliedDistributor, $this->appliedBulan);
    }

    #[Computed]
    public function depot(): ?object
    {
        if (empty($this->appliedDistributor)) {
            return null;
        }
        $service = app(JksMapService::class);
        return $service->getDistributorDepot($this->appliedDistributor);
    }

    #[Computed]
    public function anomalies(): array
    {
        if (empty($this->appliedDistributor)) {
            return [
                'at_distributor' => [],
                'missing_coordinate' => [],
                'duplicate' => [],
                'dobel_cover' => [],
                'non_route' => [],
                'has_depot_coords' => false,
                'total_stores' => 0,
                'with_coords' => 0,
                'non_route_counts' => [
                    'no_schedule' => 0,
                    'no_week' => 0,
                    'no_day' => 0,
                    'sunday' => 0,
                ],
            ];
        }

        $service = app(JksMapService::class);
        // Query semua jadwal toko distributor pada bulan aktif (audit komprehensif seluruh salesman tanpa terpotong filter week/day/salesman)
        $stores = $service->getJksStores($this->appliedDistributor, $this->appliedBulan, null, null, []);
        $depot = $service->getDistributorDepot($this->appliedDistributor);

        $depotLat = ($depot && !empty($depot->latitude) && (float)$depot->latitude != 0) ? (float)$depot->latitude : null;
        $depotLon = ($depot && !empty($depot->longitude) && (float)$depot->longitude != 0) ? (float)$depot->longitude : null;

        $missingCoordinate = [];
        $storesWithCoords = [];

        // Pengelompokan per customer_code untuk deteksi Dobel Cover
        $storesByCustomer = [];

        // Tracking Non Rute
        $nonRoute = [];
        $countNoSchedule = 0;
        $countNoWeek = 0;
        $countNoDay = 0;
        $countSunday = 0;

        foreach ($stores as $s) {
            $storesByCustomer[$s->customer_code][] = $s;

            // 1. Klasifikasi Geotag: Belum ada titik (missing coordinate) vs Berkoordinat
            $lat = ($s->latitude !== null && $s->latitude !== '') ? (float)$s->latitude : null;
            $lon = ($s->longitude !== null && $s->longitude !== '') ? (float)$s->longitude : null;

            // Anomali 2: Belum ada titik (NULL / 0 / dummy 1,1)
            $isMissing = ($lat === null || $lon === null || ($lat == 0 && $lon == 0) || ($lat == 1 && $lon == 1));

            if ($isMissing) {
                $missingCoordinate[] = [
                    'id' => $s->id,
                    'customer_code' => $s->customer_code,
                    'customer_name' => $s->customer_name,
                    'salesman_code' => $s->salesman_code,
                    'salesman_name' => $s->salesman_name,
                    'alamat' => $s->alamat ?? '-',
                    'latitude' => null,
                    'longitude' => null,
                ];
            } else {
                $storesWithCoords[] = $s;
            }

            // 2. Anomali Non Rute:
            // a. Toko tidak ada jadwal (h1-h7 kosong & w1-w4 kosong)
            // b. Ada hari tidak ada week (ada h1-h7 tapi w1-w4 kosong)
            // c. Ada week tidak ada hari (ada w1-w4 tapi h1-h7 kosong)
            // d. Toko jadwal hari minggu (h7 = 'Y')
            $hasDay = ($s->h1 === 'Y' || $s->h2 === 'Y' || $s->h3 === 'Y' || $s->h4 === 'Y' || $s->h5 === 'Y' || $s->h6 === 'Y' || $s->h7 === 'Y');
            $hasWeek = ($s->w1 === 'Y' || $s->w2 === 'Y' || $s->w3 === 'Y' || $s->w4 === 'Y');
            $isSunday = ($s->h7 === 'Y');

            $isNoSchedule = (!$hasDay && !$hasWeek);
            $isNoWeek = ($hasDay && !$hasWeek);
            $isNoDay = (!$hasDay && $hasWeek);

            if ($isNoSchedule || $isNoWeek || $isNoDay || $isSunday) {
                if ($isNoSchedule) $countNoSchedule++;
                if ($isNoWeek) $countNoWeek++;
                if ($isNoDay) $countNoDay++;
                if ($isSunday) $countSunday++;

                $labels = [];
                if ($isSunday) $labels[] = 'Jadwal Hari Minggu (H7)';
                if ($isNoSchedule) $labels[] = 'Tanpa Jadwal';
                if ($isNoWeek) $labels[] = 'Ada Hari Tanpa Week';
                if ($isNoDay) $labels[] = 'Ada Week Tanpa Hari';

                $nonRoute[] = [
                    'id' => $s->id,
                    'customer_code' => $s->customer_code,
                    'customer_name' => $s->customer_name,
                    'salesman_code' => $s->salesman_code,
                    'salesman_name' => $s->salesman_name,
                    'alamat' => $s->alamat ?? '-',
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'labels' => $labels,
                    'is_no_schedule' => $isNoSchedule,
                    'is_no_week' => $isNoWeek,
                    'is_no_day' => $isNoDay,
                    'is_sunday' => $isSunday,
                ];
            }
        }

        // Kategori 1: Titik di Distributor (<= 100 Meter dari Gudang/Depot)
        $atDistributor = [];
        if ($depotLat !== null && $depotLon !== null) {
            foreach ($storesWithCoords as $s) {
                $sLat = (float)$s->latitude;
                $sLon = (float)$s->longitude;
                $distanceMeters = $this->haversineMeters($depotLat, $depotLon, $sLat, $sLon);
                if ($distanceMeters <= 100) {
                    $atDistributor[] = [
                        'id' => $s->id,
                        'customer_code' => $s->customer_code,
                        'customer_name' => $s->customer_name,
                        'salesman_code' => $s->salesman_code,
                        'salesman_name' => $s->salesman_name,
                        'alamat' => $s->alamat ?? '-',
                        'latitude' => $sLat,
                        'longitude' => $sLon,
                        'distance_meters' => round($distanceMeters, 1),
                    ];
                }
            }
            usort($atDistributor, fn($a, $b) => $a['distance_meters'] <=> $b['distance_meters']);
        }

        // Kategori 3: Duplikasi Titik (Koordinat sama persis / menumpuk)
        $coordGroups = [];
        foreach ($storesWithCoords as $s) {
            $sLat = (float)$s->latitude;
            $sLon = (float)$s->longitude;
            // Presisi 5 desimal (~1.1 meter di lapangan)
            $coordKey = number_format($sLat, 5, '.', '') . ',' . number_format($sLon, 5, '.', '');
            $coordGroups[$coordKey][] = $s;
        }

        $duplicate = [];
        foreach ($coordGroups as $coordKey => $group) {
            if (count($group) > 1) {
                $dupCount = count($group);
                foreach ($group as $s) {
                    $duplicate[] = [
                        'id' => $s->id,
                        'customer_code' => $s->customer_code,
                        'customer_name' => $s->customer_name,
                        'salesman_code' => $s->salesman_code,
                        'salesman_name' => $s->salesman_name,
                        'alamat' => $s->alamat ?? '-',
                        'latitude' => (float)$s->latitude,
                        'longitude' => (float)$s->longitude,
                        'coord_key' => $coordKey,
                        'duplicate_count' => $dupCount,
                    ];
                }
            }
        }
        usort($duplicate, function($a, $b) {
            if ($a['coord_key'] === $b['coord_key']) {
                return strcmp($a['customer_name'], $b['customer_name']);
            }
            return strcmp($a['coord_key'], $b['coord_key']);
        });

        // Kategori 4: Dobel Cover (1 Toko dijadwalkan oleh > 1 Salesman berbeda)
        $dobelCover = [];
        foreach ($storesByCustomer as $custCode => $list) {
            $distinctSalesmen = array_unique(array_map(fn($item) => $item->salesman_code, $list));
            if (count($distinctSalesmen) > 1) {
                $first = $list[0];
                $salesmenInfo = [];
                foreach ($list as $item) {
                    $activeDays = [];
                    foreach (['h1'=>'Sen','h2'=>'Sel','h3'=>'Rab','h4'=>'Kam','h5'=>'Jum','h6'=>'Sab','h7'=>'Min'] as $hk=>$hl) {
                        if (($item->{$hk} ?? '') === 'Y') $activeDays[] = $hl;
                    }
                    $activeWeeks = [];
                    foreach (['w1'=>'W1','w2'=>'W2','w3'=>'W3','w4'=>'W4'] as $wk=>$wl) {
                        if (($item->{$wk} ?? '') === 'Y') $activeWeeks[] = $wl;
                    }

                    $scheduleStr = '';
                    if (!empty($activeDays)) {
                        $scheduleStr .= implode(',', $activeDays);
                    }
                    if (!empty($activeWeeks)) {
                        $scheduleStr .= ' (' . implode(',', $activeWeeks) . ')';
                    }

                    $salesmenInfo[] = [
                        'code' => $item->salesman_code,
                        'name' => $item->salesman_name,
                        'schedule' => $scheduleStr ?: 'Tanpa Jadwal',
                    ];
                }

                $dobelCover[] = [
                    'id' => $first->id,
                    'customer_code' => $first->customer_code,
                    'customer_name' => $first->customer_name,
                    'alamat' => $first->alamat ?? '-',
                    'latitude' => $first->latitude ? (float)$first->latitude : null,
                    'longitude' => $first->longitude ? (float)$first->longitude : null,
                    'count' => count($distinctSalesmen),
                    'salesmen' => $salesmenInfo,
                ];
            }
        }
        usort($dobelCover, fn($a, $b) => strcmp($a['customer_name'], $b['customer_name']));

        return [
            'at_distributor' => $atDistributor,
            'missing_coordinate' => $missingCoordinate,
            'duplicate' => $duplicate,
            'dobel_cover' => $dobelCover,
            'non_route' => $nonRoute,
            'has_depot_coords' => ($depotLat !== null && $depotLon !== null),
            'total_stores' => count($stores),
            'with_coords' => count($storesWithCoords),
            'non_route_counts' => [
                'no_schedule' => $countNoSchedule,
                'no_week' => $countNoWeek,
                'no_day' => $countNoDay,
                'sunday' => $countSunday,
            ],
        ];
    }

    /**
     * Hitung jarak Haversine antara dua koordinat GPS dalam satuan meter.
     */
    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Tentukan warna titik toko berdasarkan mode pewarnaan aktif (Salesman atau Day).
     */
    public function getStoreColor($store, $territories): string
    {
        if ($this->colorMode === 'day') {
            $activeDays = [];
            foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7'] as $hk) {
                if (($store->{$hk} ?? '') === 'Y') {
                    $activeDays[] = $hk;
                }
            }
            if (count($activeDays) === 1) {
                return self::DAY_PALETTE[$activeDays[0]]['color'] ?? '#2563EB';
            } elseif (count($activeDays) > 1) {
                if (!empty($this->filterDay) && in_array($this->filterDay, $activeDays)) {
                    return self::DAY_PALETTE[$this->filterDay]['color'] ?? '#0D9488';
                }
                return self::DAY_PALETTE['multi']['color'] ?? '#0D9488';
            }
            return self::DAY_PALETTE['none']['color'] ?? '#94A3B8';
        }

        // Default: By Salesman (SE)
        return $territories[$store->salesman_code]['color'] ?? '#3B82F6';
    }

    /**
     * Dapatkan data legenda terkompilasi sesuai mode pewarnaan aktif.
     */
    #[Computed]
    public function legendData(): array
    {
        if (empty($this->appliedDistributor)) {
            return [
                'mode' => $this->colorMode,
                'title' => 'Legenda',
                'items' => [],
            ];
        }

        if ($this->colorMode === 'day') {
            $service = app(JksMapService::class);
            $stores = $service->getJksStores(
                $this->appliedDistributor,
                $this->appliedBulan,
                $this->filterWeek,
                $this->filterDay,
                $this->activeSalesmanCodes
            );

            $counts = ['h1' => 0, 'h2' => 0, 'h3' => 0, 'h4' => 0, 'h5' => 0, 'h6' => 0, 'h7' => 0, 'multi' => 0, 'none' => 0];
            foreach ($stores as $s) {
                $activeDays = [];
                foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7'] as $hk) {
                    if (($s->{$hk} ?? '') === 'Y') $activeDays[] = $hk;
                }
                if (count($activeDays) === 1) {
                    $counts[$activeDays[0]]++;
                } elseif (count($activeDays) > 1) {
                    $counts['multi']++;
                } else {
                    $counts['none']++;
                }
            }

            $items = [];
            foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'multi'] as $key) {
                $p = self::DAY_PALETTE[$key];
                $items[] = [
                    'key' => $key,
                    'label' => $p['label'],
                    'short' => $p['short'],
                    'color' => $p['color'],
                    'count' => $counts[$key],
                    'active' => empty($this->filterDay) || $this->filterDay === $key,
                ];
            }

            return [
                'mode' => 'day',
                'title' => 'Hari Kunjungan',
                'items' => $items,
            ];
        }

        // Default: By Salesman
        $territories = $this->salesmanTerritories();
        $items = [];
        foreach ($territories as $sCode => $t) {
            $items[] = [
                'key' => $sCode,
                'label' => $t['name'],
                'short' => $sCode,
                'color' => $t['color'],
                'count' => $t['total_stores'] ?? $t['total_toko'] ?? 0,
                'active' => in_array($sCode, $this->activeSalesmanCodes),
            ];
        }

        return [
            'mode' => 'salesman',
            'title' => 'Salesman (SE)',
            'items' => $items,
        ];
    }

    /**
     * Ambil payload GeoJSON terkompilasi untuk MapLibre.
     */
    #[Computed]
    public function mapGeoJson(): array
    {
        if (empty($this->appliedDistributor)) {
            return [
                'jks_geojson' => ['type' => 'FeatureCollection', 'features' => []],
                'non_route_geojson' => ['type' => 'FeatureCollection', 'features' => []],
                'routes_geojson' => ['type' => 'FeatureCollection', 'features' => []],
                'depot' => null,
            ];
        }

        $service = app(JksMapService::class);
        $territories = $this->salesmanTerritories();
        $depot = $this->depot();

        // 1. JKS Stores GeoJSON
        $jksStores = $service->getJksStores(
            $this->appliedDistributor,
            $this->appliedBulan,
            $this->filterWeek,
            $this->filterDay,
            $this->activeSalesmanCodes
        );

        $jksFeatures = [];
        $salesmanRoutes = []; // [salesman_code => [[lng, lat], ...]]

        foreach ($jksStores as $index => $store) {
            $lat = (float) $store->latitude;
            $lng = (float) $store->longitude;

            if ($lat == 0 || $lng == 0 || empty($lat) || empty($lng)) {
                continue;
            }

            $color = $this->getStoreColor($store, $territories);

            // Extract active days & weeks string
            $activeDays = [];
            foreach (['h1'=>'Sen', 'h2'=>'Sel', 'h3'=>'Rab', 'h4'=>'Kam', 'h5'=>'Jum', 'h6'=>'Sab', 'h7'=>'Min'] as $hk => $hl) {
                if ($store->{$hk} === 'Y') $activeDays[] = $hl;
            }
            $activeWeeks = [];
            foreach (['w1'=>'W1', 'w2'=>'W2', 'w3'=>'W3', 'w4'=>'W4'] as $wk => $wl) {
                if ($store->{$wk} === 'Y') $activeWeeks[] = $wl;
            }

            $jksFeatures[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$lng, $lat]
                ],
                'properties' => [
                    'id' => $store->id,
                    'customer_code' => $store->customer_code,
                    'customer_name' => $store->customer_name,
                    'alamat' => $store->alamat ?: '-',
                    'kecamatan' => $store->kecamatan ?: '-',
                    'desa' => $store->desa ?: '-',
                    'salesman_code' => $store->salesman_code,
                    'salesman_name' => $store->salesman_name,
                    'color' => $color,
                    'color_mode' => $this->colorMode,
                    'days_label' => implode(', ', $activeDays),
                    'weeks_label' => implode(', ', $activeWeeks),
                    'type' => 'jks'
                ]
            ];

            // Add to route line
            if (!isset($salesmanRoutes[$store->salesman_code])) {
                $salesmanRoutes[$store->salesman_code] = [];
            }
            $salesmanRoutes[$store->salesman_code][] = [$lng, $lat];
        }

        // 2. Non Route Stores GeoJSON
        $nonRouteFeatures = [];
        if ($this->showNonRoute) {
            $nonRouteStores = $service->getNonRouteStores($this->appliedDistributor, $this->appliedBulan);
            foreach ($nonRouteStores as $nr) {
                $lat = (float) $nr->latitude;
                $lng = (float) $nr->longitude;
                if ($lat == 0 || $lng == 0) continue;

                $nonRouteFeatures[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lng, $lat]
                    ],
                    'properties' => [
                        'id' => $nr->id,
                        'customer_code' => $nr->customer_code,
                        'customer_name' => $nr->customer_name,
                        'customer_eska' => $nr->customer_eska ?: '-',
                        'alamat' => $nr->alamat ?: '-',
                        'avg_value_net' => (float) $nr->avg_value_net,
                        'pending_approval_id' => $nr->pending_approval_id,
                        'color' => '#F59E0B', // Amber
                        'type' => 'non_jks'
                    ]
                ];
            }
        }

        // 3. Route Lines GeoJSON (Polylines)
        $routeFeatures = [];
        if ($this->showRouteLines) {
            $depotLng = $depot && (float)$depot->longitude != 0 ? (float)$depot->longitude : null;
            $depotLat = $depot && (float)$depot->latitude != 0 ? (float)$depot->latitude : null;

            foreach ($salesmanRoutes as $sCode => $coords) {
                if (count($coords) < 2) continue;

                $lineCoords = $coords;
                // If depot exists and showDepot is true, start route from depot
                if ($this->showDepot && $depotLng && $depotLat) {
                    array_unshift($lineCoords, [$depotLng, $depotLat]);
                }

                $color = $territories[$sCode]['color'] ?? '#3B82F6';

                $routeFeatures[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $lineCoords
                    ],
                    'properties' => [
                        'salesman_code' => $sCode,
                        'salesman_name' => $territories[$sCode]['name'] ?? $sCode,
                        'color' => $color
                    ]
                ];
            }
        }

        return [
            'jks_geojson' => ['type' => 'FeatureCollection', 'features' => $jksFeatures],
            'non_route_geojson' => ['type' => 'FeatureCollection', 'features' => $nonRouteFeatures],
            'routes_geojson' => ['type' => 'FeatureCollection', 'features' => $routeFeatures],
            'depot' => ($depot && (float)$depot->latitude != 0) ? [
                'name' => $depot->distributor_name,
                'code' => $depot->distributor_code,
                'coordinates' => [(float)$depot->longitude, (float)$depot->latitude]
            ] : null,
        ];
    }

    public function selectStore($customerCode, $type = 'jks')
    {
        $this->selectedCustomerCode = $customerCode;
        $this->selectedStoreType = $type;

        if ($type === 'jks') {
            $this->selectedStore = DB::table('jks_salesmans as js')
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                    $join->on('js.customer_code', '=', 'ltpte.uniq_kd')
                         ->on('js.distributor_code', '=', 'ltpte.distributor_code');
                })
                ->leftJoin('salesmans as s', function($join) {
                    $join->on('js.salesman_code', '=', 's.salesman_code')
                         ->on('js.distributor_code', '=', 's.distributor_code');
                })
                ->where('js.distributor_code', $this->appliedDistributor)
                ->where('js.customer_code', $customerCode)
                ->where('js.bulan', 'like', substr($this->appliedBulan, 0, 7) . '%')
                ->select(
                    'js.*',
                    DB::raw("COALESCE(ltpte.customer_name, js.customer_code) as customer_name"),
                    'ltpte.customer_address as alamat',
                    'ltpte.kecamatan',
                    'ltpte.desa',
                    'ltpte.latitude',
                    'ltpte.longitude',
                    DB::raw("COALESCE(s.salesman_name, js.salesman_code) as salesman_name")
                )
                ->first();
        } else {
            $this->selectedStore = DB::table('jks_se_master_toko_ool as mto')
                ->leftJoin('list_toko_pareto_team_elite as ltpte', function($join) {
                    $join->on('mto.customer_code', '=', 'ltpte.uniq_kd')
                         ->on('mto.distributor_code', '=', 'ltpte.distributor_code');
                })
                ->where('mto.distributor_code', $this->appliedDistributor)
                ->where('mto.customer_code', $customerCode)
                ->select(
                    'mto.*',
                    DB::raw("COALESCE(ltpte.customer_name, mto.name, mto.customer_code) as customer_name"),
                    DB::raw("COALESCE(ltpte.customer_address, mto.address, '-') as alamat"),
                    'ltpte.kecamatan',
                    'ltpte.desa',
                    'ltpte.latitude',
                    'ltpte.longitude'
                )
                ->first();
        }

        $this->drawerTab = 'store';

        if ($this->selectedStore && !empty($this->selectedStore->latitude) && !empty($this->selectedStore->longitude) && (float)$this->selectedStore->latitude != 0) {
            $activeDays = [];
            foreach (['h1'=>'Sen', 'h2'=>'Sel', 'h3'=>'Rab', 'h4'=>'Kam', 'h5'=>'Jum', 'h6'=>'Sab', 'h7'=>'Min'] as $hk => $hl) {
                if (($this->selectedStore->{$hk} ?? '') === 'Y') $activeDays[] = $hl;
            }
            $activeWeeks = [];
            foreach (['w1'=>'W1', 'w2'=>'W2', 'w3'=>'W3', 'w4'=>'W4'] as $wk => $wl) {
                if (($this->selectedStore->{$wk} ?? '') === 'Y') $activeWeeks[] = $wl;
            }

            $this->dispatch('fly-to-store', [
                'lat' => (float)$this->selectedStore->latitude,
                'lng' => (float)$this->selectedStore->longitude,
                'customer_code' => $this->selectedStore->customer_code,
                'customer_name' => $this->selectedStore->customer_name ?? $this->selectedStore->customer_code,
                'salesman_name' => $this->selectedStore->salesman_name ?? '-',
                'salesman_code' => $this->selectedStore->salesman_code ?? '',
                'alamat' => $this->selectedStore->alamat ?? '-',
                'days_label' => !empty($activeDays) ? implode(', ', $activeDays) : 'Tanpa Hari',
                'weeks_label' => !empty($activeWeeks) ? implode(', ', $activeWeeks) : 'Tanpa Week',
                'type' => $type,
            ]);
        }
    }

    public function openReassignModal()
    {
        if (!$this->canManipulate) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Akses Ditolak: Perubahan hanya diizinkan untuk periode bulan berjalan.']);
            return;
        }

        if (!$this->selectedStore || $this->selectedStoreType !== 'jks') return;

        $this->reassignTargetSalesman = '';
        $this->reassignReason = '';
        $this->showReassignModal = true;
    }

    public function submitReassignSalesman()
    {
        if (!$this->canManipulate) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Akses Ditolak: Perubahan hanya diizinkan untuk periode bulan berjalan.']);
            return;
        }

        $this->validate([
            'reassignTargetSalesman' => 'required',
            'reassignReason' => 'required|min:3',
        ], [
            'reassignTargetSalesman.required' => 'Pilih salesman tujuan.',
            'reassignReason.required' => 'Alasan wajib diisi.',
        ]);

        $store = $this->selectedStore;
        $user = auth()->user();

        $payload = [
            'id' => $store->id,
            'bulan' => $this->appliedBulan,
            'update' => [
                'salesman_code' => $this->reassignTargetSalesman,
                'h1' => $store->h1, 'h2' => $store->h2, 'h3' => $store->h3, 'h4' => $store->h4, 'h5' => $store->h5, 'h6' => $store->h6, 'h7' => $store->h7,
                'w1' => $store->w1, 'w2' => $store->w2, 'w3' => $store->w3, 'w4' => $store->w4,
            ]
        ];

        $targetName = DB::table('salesmans')->where('salesman_code', $this->reassignTargetSalesman)->value('salesman_name') ?? $this->reassignTargetSalesman;
        $reasonFull = "Pindah Wilayah Salesman Toko: {$store->customer_name} dari {$store->salesman_name} ke {$targetName} | Alasan: {$this->reassignReason}";

        if ($user && $user->hasRole('admin')) {
            // Direct update for admin
            DB::table('jks_salesmans')->where('id', $store->id)->update([
                'salesman_code' => $this->reassignTargetSalesman,
                'updated_at' => now(),
            ]);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Toko berhasil dipindahkan ke salesman baru!']);
        } else {
            // Submit to approval
            DB::table('jks_approvals')->insert([
                'distributor_code' => $this->appliedDistributor,
                'action_type' => 'EDIT_INDIVIDU',
                'status' => 'PENDING',
                'payload' => json_encode($payload),
                'reason' => $reasonFull,
                'maker_id' => $user->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan pindah salesman berhasil dikirim ke Approval!']);
        }

        $this->showReassignModal = false;
        $this->selectStore($store->customer_code, 'jks');
        $this->dispatchMapRefresh();
    }

    public function openEditScheduleModal()
    {
        if (!$this->canManipulate) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Akses Ditolak: Perubahan hanya diizinkan untuk periode bulan berjalan.']);
            return;
        }

        if (!$this->selectedStore || $this->selectedStoreType !== 'jks') return;

        $store = $this->selectedStore;
        $this->editHari = [
            'h1' => $store->h1 === 'Y', 'h2' => $store->h2 === 'Y', 'h3' => $store->h3 === 'Y',
            'h4' => $store->h4 === 'Y', 'h5' => $store->h5 === 'Y', 'h6' => $store->h6 === 'Y', 'h7' => $store->h7 === 'Y',
        ];
        $this->editMinggu = [
            'w1' => $store->w1 === 'Y', 'w2' => $store->w2 === 'Y', 'w3' => $store->w3 === 'Y', 'w4' => $store->w4 === 'Y',
        ];
        $this->editReason = '';
        $this->showEditScheduleModal = true;
    }

    public function submitEditSchedule()
    {
        if (!$this->canManipulate) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Akses Ditolak: Perubahan hanya diizinkan untuk periode bulan berjalan.']);
            return;
        }

        $activeH = array_filter($this->editHari);
        $activeW = array_filter($this->editMinggu);

        if (empty($activeH)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih minimal satu hari kunjungan.']);
            return;
        }
        if (empty($activeW)) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Pilih minimal satu minggu kunjungan.']);
            return;
        }

        $this->validate(['editReason' => 'required|min:3'], ['editReason.required' => 'Alasan wajib diisi.']);

        $store = $this->selectedStore;
        $user = auth()->user();

        $updateData = [
            'salesman_code' => $store->salesman_code,
            'h1' => $this->editHari['h1'] ? 'Y' : 'T',
            'h2' => $this->editHari['h2'] ? 'Y' : 'T',
            'h3' => $this->editHari['h3'] ? 'Y' : 'T',
            'h4' => $this->editHari['h4'] ? 'Y' : 'T',
            'h5' => $this->editHari['h5'] ? 'Y' : 'T',
            'h6' => $this->editHari['h6'] ? 'Y' : 'T',
            'h7' => $this->editHari['h7'] ? 'Y' : 'T',
            'w1' => $this->editMinggu['w1'] ? 'Y' : 'T',
            'w2' => $this->editMinggu['w2'] ? 'Y' : 'T',
            'w3' => $this->editMinggu['w3'] ? 'Y' : 'T',
            'w4' => $this->editMinggu['w4'] ? 'Y' : 'T',
        ];

        $payload = [
            'id' => $store->id,
            'bulan' => $this->appliedBulan,
            'update' => $updateData
        ];

        if ($user && $user->hasRole('admin')) {
            DB::table('jks_salesmans')->where('id', $store->id)->update(array_merge($updateData, ['updated_at' => now()]));
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Jadwal hari/minggu toko berhasil diperbarui!']);
        } else {
            DB::table('jks_approvals')->insert([
                'distributor_code' => $this->appliedDistributor,
                'action_type' => 'EDIT_INDIVIDU',
                'status' => 'PENDING',
                'payload' => json_encode($payload),
                'reason' => "Ubah Jadwal Hari/Minggu Toko: {$store->customer_name} | Alasan: {$this->editReason}",
                'maker_id' => $user->id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan ubah jadwal berhasil dikirim ke Approval!']);
        }

        $this->showEditScheduleModal = false;
        $this->selectStore($store->customer_code, 'jks');
        $this->dispatchMapRefresh();
    }

    public function openAddNonRouteModal()
    {
        if (!$this->canManipulate) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Akses Ditolak: Penambahan hanya diizinkan untuk periode bulan berjalan.']);
            return;
        }

        if (!$this->selectedStore || $this->selectedStoreType !== 'non_jks') return;

        $this->addSalesman = '';
        $this->addHari = [];
        $this->addMinggu = [];
        $this->showAddNonRouteModal = true;
    }

    public function submitAddNonRouteToJks()
    {
        if (!$this->canManipulate) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Akses Ditolak: Penambahan hanya diizinkan untuk periode bulan berjalan.']);
            return;
        }

        $this->validate([
            'addSalesman' => 'required',
            'addHari' => 'required|array|min:1',
            'addMinggu' => 'required|array|min:1',
        ], [
            'addSalesman.required' => 'Pilih salesman.',
            'addHari.required' => 'Pilih minimal 1 hari kunjungan.',
            'addMinggu.required' => 'Pilih minimal 1 minggu kunjungan.',
        ]);

        $store = $this->selectedStore;

        $hariArray = array_map(fn($h) => 'h' . $h, $this->addHari);
        $mingguArray = array_map(fn($w) => 'w' . $w, $this->addMinggu);

        $payload = [
            'distributor_code' => $this->appliedDistributor,
            'customer_code' => $store->customer_code,
            'salesman_code' => $this->addSalesman,
            'bulan' => \Carbon\Carbon::parse($this->appliedBulan)->format('Y-m-01'),
            'hari' => $hariArray,
            'minggu' => $mingguArray,
            'tokos' => [
                ['code' => $store->customer_code, 'name' => $store->customer_name]
            ]
        ];

        DB::table('jks_approvals')->insert([
            'distributor_code' => $this->appliedDistributor,
            'action_type' => 'TAMBAH_JADWAL',
            'status' => 'PENDING',
            'payload' => json_encode($payload),
            'reason' => "Pengajuan penambahan outlet Non-JKS via Maps: {$store->customer_name}",
            'maker_id' => auth()->user()->id ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->showAddNonRouteModal = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Pengajuan penambahan toko ke JKS berhasil dikirim!']);
        $this->selectStore($store->customer_code, 'non_jks');
        $this->dispatchMapRefresh();
    }

    public function render()
    {
        $distributorOptions = $this->filterDistributors();
        
        return view('livewire.call-plan.jks-maps.index', [
            'distributorOptions' => $distributorOptions,
            'territories' => $this->salesmanTerritories(),
            'anomalies' => $this->anomalies(),
            'mapGeoJson' => $this->mapGeoJson(),
            'legendData' => $this->legendData(),
            'depot' => $this->depot(),
        ]);
    }
}
