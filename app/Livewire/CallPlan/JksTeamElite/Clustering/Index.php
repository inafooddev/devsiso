<?php

namespace App\Livewire\CallPlan\JksTeamElite\Clustering;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\MasterCluster;
use App\Models\MasterClusterItem;
use App\Models\JksTeamElite;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'call-plan.jks-team-elite.clustering'; 

    public $clusterName = '';
    public $filterTeam = '';
    public $saveType = 'clustering';
    public $jksDate = '';
    public $jksSyncMethod = 'skip';
    public $teams = [];
    
    public $searchCenterText = '';
    public $searchCenterResults = [];
    public $centerStoreId = null;
    public $centerStore = null;
    
    public $searchDistributor = '';
    public $distributorOptions = [];
    public $selectedDistributorCode = '';
    public $selectedDistributorName = '';
    
    public $candidateCount = 15;
    
    public $searchAddText = '';
    public $searchAddResults = [];

    // Modal State
    public $isSaveModalOpen = false;

    // Map and Analysis Data
    public $clusterStores = [];
    public $totalDistance = 0;
    public $averageDistance = 0;
    public $efficiencyStatus = '-';
    public $drivingDurationFormatted = '-';
    public $visitDurationFormatted = '-';
    public $totalDurationFormatted = '-';
    public $apiGeometry = null;

    public function mount()
    {
        $this->loadTeams();
        if (count($this->teams) > 0) {
            $this->filterTeam = $this->teams[0]->kode_team;
        }
    }

    private function loadTeams()
    {
        try {
            $query = DB::table('team_elite_code_mappings as tecm')
                ->select('tecm.team_elite_code as kode_team', 'f.SLSNAME as nama_team')
                ->leftJoin('fsalesman as f', 'tecm.team_elite_code', '=', 'f.SLSNO')
                ->groupBy('tecm.team_elite_code', 'f.SLSNAME');

            $user = auth()->user();
            if ($user && !$user->hasRole('admin')) {
                if (!empty($user->supervisor_code)) {
                    $query->where('tecm.team_elite_code', $user->supervisor_code);
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    $query->whereIn('tecm.area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    $query->whereIn('tecm.region_code', $user->region_code);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $this->teams = $query->get()->toArray();
        } catch (\Exception $e) {
            $this->teams = [];
        }
    }

    public function updatedSearchDistributor()
    {
        if (strlen($this->searchDistributor) >= 2) {
            $this->distributorOptions = DB::table('master_distributors')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('distributor_code', 'ilike', '%' . $this->searchDistributor . '%')
                      ->orWhere('distributor_name', 'ilike', '%' . $this->searchDistributor . '%');
                })
                ->select('distributor_code', 'distributor_name')
                ->limit(20)
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        } else {
            $this->distributorOptions = [];
        }
    }

    public function selectDistributor($code, $name)
    {
        $this->selectedDistributorCode = $code;
        $this->selectedDistributorName = $name;
        $this->searchDistributor = $code . ' - ' . $name;
        $this->distributorOptions = [];
        
        $this->searchCenterText = '';
        $this->searchCenterResults = [];
        $this->centerStoreId = null;
        $this->centerStore = null;
    }

    public function clearDistributor()
    {
        $this->selectedDistributorCode = '';
        $this->selectedDistributorName = '';
        $this->searchDistributor = '';
        $this->distributorOptions = [];
        
        $this->searchCenterText = '';
        $this->searchCenterResults = [];
        $this->centerStoreId = null;
        $this->centerStore = null;
    }

    public function updatedSearchCenterText()
    {
        if (strlen($this->searchCenterText) > 2) {
            $query = DB::table('list_toko_pareto_team_elite')
                ->where(function($q) {
                    $q->where('customer_name', 'ilike', '%' . $this->searchCenterText . '%')
                      ->orWhere('customer_code_prc', 'ilike', '%' . $this->searchCenterText . '%');
                })
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0);
                
            if (!empty($this->selectedDistributorCode)) {
                $query->where('distributor_code', $this->selectedDistributorCode);
            }
            
            $this->searchCenterResults = $query->take(10)->get()->toArray();
        } else {
            $this->searchCenterResults = [];
        }
    }

    public function selectCenterStore($id)
    {
        $this->centerStoreId = $id;
        $this->centerStore = DB::table('list_toko_pareto_team_elite')->where('id', $id)->first();
        if ($this->centerStore) {
            $this->searchCenterText = $this->centerStore->customer_code_prc . ' - ' . $this->centerStore->customer_name;
        }
        $this->searchCenterResults = [];
    }

    public function updatedSearchAddText()
    {
        if (strlen($this->searchAddText) > 2) {
            // Exclude already added stores
            $existingIds = array_column($this->clusterStores, 'id');
            
            $this->searchAddResults = DB::table('list_toko_pareto_team_elite')
                ->whereNotIn('id', $existingIds)
                ->where(function($q) {
                    $q->where('customer_name', 'ilike', '%' . $this->searchAddText . '%')
                      ->orWhere('customer_code_prc', 'ilike', '%' . $this->searchAddText . '%');
                })
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('latitude', '!=', 0)
                ->where('longitude', '!=', 0)
                ->take(10)
                ->get()
                ->toArray();
        } else {
            $this->searchAddResults = [];
        }
    }

    public function selectAddStore($id)
    {
        $store = DB::table('list_toko_pareto_team_elite')->where('id', $id)->first();
        if ($store) {
            $this->clusterStores[] = (array) $store;
            $this->fetchGeometryOnly();
        }
        $this->searchAddText = '';
        $this->searchAddResults = [];
    }

    public function removeStore($index)
    {
        if (isset($this->clusterStores[$index])) {
            array_splice($this->clusterStores, $index, 1);
            $this->fetchGeometryOnly();
        }
    }

    public function moveStoreUp($index)
    {
        if ($index > 0 && isset($this->clusterStores[$index]) && isset($this->clusterStores[$index - 1])) {
            $this->reorderStore($index, $index - 1);
        }
    }

    public function moveStoreDown($index)
    {
        if ($index < count($this->clusterStores) - 1 && isset($this->clusterStores[$index]) && isset($this->clusterStores[$index + 1])) {
            $this->reorderStore($index, $index + 1);
        }
    }

    public function reorderStore($fromIndex, $toIndex)
    {
        if (isset($this->clusterStores[$fromIndex]) && isset($this->clusterStores[$toIndex])) {
            $item = array_splice($this->clusterStores, $fromIndex, 1)[0];
            array_splice($this->clusterStores, $toIndex, 0, [$item]);
            $this->fetchGeometryOnly();
        }
    }

    public function generateCluster()
    {
        if (!$this->centerStore) return;

        $allStores = DB::table('list_toko_pareto_team_elite')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->get();

        $distances = [];
        foreach ($allStores as $store) {
            $dist = $this->haversineGreatCircleDistance($this->centerStore->latitude, $this->centerStore->longitude, $store->latitude, $store->longitude);
            $distances[] = [
                'store' => $store,
                'distance' => $dist
            ];
        }

        // 1. Sort by purely distance to get the "Local Area Pool"
        usort($distances, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // 2. Define the Local Area Pool (radius geografis wajar, max 99 toko untuk OSRM)
        $poolSize = min(99, max(50, $this->candidateCount * 3));
        $localPool = array_slice($distances, 0, $poolSize);

        // 3. Ambil jarak asli via OSRM Distance Matrix (Table API)
        $coords = [$this->centerStore->longitude . ',' . $this->centerStore->latitude];
        $poolStores = [];
        
        foreach ($localPool as $item) {
            if ($item['store']->id != $this->centerStore->id) {
                $coords[] = $item['store']->longitude . ',' . $item['store']->latitude;
                $poolStores[] = $item;
            }
        }
        
        $coordsString = implode(';', $coords);
        $osrmDistances = [];
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get("http://router.project-osrm.org/table/v1/driving/{$coordsString}", [
                'sources' => '0'
            ]);
            
            if ($response->successful() && isset($response->json()['distances'][0])) {
                $osrmDistances = $response->json()['distances'][0]; // index 0 is center, index 1..N is poolStores
            }
        } catch (\Exception $e) {
            // fallback to haversine if OSRM fails
        }

        $otherCandidates = [];
        foreach ($poolStores as $idx => $item) {
            // index in OSRM response is $idx + 1 because $idx 0 in OSRM is the center store itself
            $realDistance = (isset($osrmDistances[$idx + 1]) && $osrmDistances[$idx + 1] !== null) 
                            ? ($osrmDistances[$idx + 1] / 1000) // convert meters to km
                            : $item['distance']; // fallback to haversine
                            
            $otherCandidates[] = [
                'store' => $item['store'],
                'distance' => $realDistance
            ];
        }

        // 4. Sort Other Candidates by Pilar Priority, then Real Road Distance
        usort($otherCandidates, function($a, $b) {
            $pilarA = $a['store']->pilar ?? 'Z';
            $pilarB = $b['store']->pilar ?? 'Z';
            
            // Prioritas: '1. RWO' < '2. PNR' < '3. NGVO' < '4. GRO' < 'Z'
            if ($pilarA !== $pilarB) {
                return strcmp($pilarA, $pilarB); 
            }
            // Jika Pilar sama, ambil yang terdekat jarak jalannya
            return $a['distance'] <=> $b['distance'];
        });

        // 5. Gabungkan Center Store dengan N-1 kandidat teratas berdasarkan prioritas
        $topCandidates = [ ['store' => $this->centerStore] ];
        
        $needed = $this->candidateCount - 1;
        if ($needed > 0) {
            $topCandidates = array_merge($topCandidates, array_slice($otherCandidates, 0, $needed));
        }

        $this->clusterStores = array_map(function($item) {
            return (array) $item['store'];
        }, $topCandidates);

        $this->analyzeRoute();
    }

    public function analyzeRoute()
    {
        if (count($this->clusterStores) < 2) {
            $this->resetAnalysis();
            $this->dispatch('route-analyzed', route: $this->clusterStores, geometry: null);
            return;
        }

        $this->fetchRealRouteData($this->clusterStores);
    }

    private function resetAnalysis()
    {
        $this->totalDistance = 0;
        $this->averageDistance = 0;
        $this->efficiencyStatus = '-';
        $this->drivingDurationFormatted = '-';
        $this->visitDurationFormatted = '-';
        $this->totalDurationFormatted = '-';
        $this->apiGeometry = null;
        $this->dispatch('route-analyzed', route: $this->clusterStores, geometry: null);
    }

    private function calculateTSP($stores)
    {
        foreach ($stores as &$store) {
            $store['latitude'] = (float) $store['latitude'];
            $store['longitude'] = (float) $store['longitude'];
        }
        unset($store);

        $unvisited = $stores;
        $currentStore = array_shift($unvisited);
        $route = [$currentStore];
        $totalDist = 0;

        while (count($unvisited) > 0) {
            $nearestIdx = -1;
            $minDist = PHP_FLOAT_MAX;

            foreach ($unvisited as $idx => $candidate) {
                $dist = $this->haversineGreatCircleDistance(
                    $currentStore['latitude'], $currentStore['longitude'],
                    $candidate['latitude'], $candidate['longitude']
                );

                if ($dist < $minDist) {
                    $minDist = $dist;
                    $nearestIdx = $idx;
                }
            }

            $currentStore = $unvisited[$nearestIdx];
            $distKm = round($minDist, 2);
            $route[count($route) - 1]['distance_to_next'] = $distKm;
            $route[count($route) - 1]['duration_to_next'] = round(($distKm / 40 * 60) * 1.30);
            
            $route[] = $currentStore;
            $totalDist += $minDist;

            array_splice($unvisited, $nearestIdx, 1);
        }

        $route[count($route) - 1]['distance_to_next'] = 0;
        $route[count($route) - 1]['duration_to_next'] = 0;

        $this->clusterStores = $route;
        $this->totalDistance = round($totalDist, 2);
        
        $numEdges = count($route) - 1;
        $this->averageDistance = $numEdges > 0 ? round($totalDist / $numEdges, 2) : 0;

        if ($this->totalDistance > 80) {
            $this->efficiencyStatus = 'Terlalu Tersebar (> 80 Km)';
        } elseif ($this->totalDistance > 40) {
            $this->efficiencyStatus = 'Wajar (40-80 Km)';
        } else {
            $this->efficiencyStatus = 'Sangat Efisien (< 40 Km)';
        }

        $drivingMinutes = round(($this->totalDistance / 40 * 60) * 1.30);
        $visitMinutes = count($route) * 30;
        $totalMinutes = $drivingMinutes + $visitMinutes;
        
        $this->drivingDurationFormatted = $this->formatMinutesToHours($drivingMinutes) . ' (Manual)';
        $this->visitDurationFormatted = $this->formatMinutesToHours($visitMinutes);
        $this->totalDurationFormatted = $this->formatMinutesToHours($totalMinutes);
        
        $this->apiGeometry = null;

        $this->dispatch('route-analyzed', route: $this->clusterStores, geometry: null);
    }

    private function formatMinutesToHours($totalMinutes)
    {
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        if ($hours > 0) {
            return "{$hours} J {$minutes} M";
        } else {
            return "{$minutes} Menit";
        }
    }

    private function fetchRealRouteData($stores)
    {
        $coords = [];
        foreach ($stores as $store) {
            $coords[] = $store['longitude'] . ',' . $store['latitude'];
        }
        $coordsString = implode(';', $coords);

        if (count($stores) > 80) {
            $this->calculateTSP($stores);
            return;
        }

        try {
            // Gunakan algoritma bawaan OSRM Trip API untuk mencari rute TSP terbaik
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get("http://router.project-osrm.org/trip/v1/driving/{$coordsString}", [
                'roundtrip' => 'true',
                'source' => 'first',
                'geometries' => 'geojson'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['code'] === 'Ok') {
                    $waypoints = $data['waypoints']; 
                    
                    $orderedStores = [];
                    foreach ($waypoints as $originalIndex => $wp) {
                        $newPosition = $wp['waypoint_index'];
                        $orderedStores[$newPosition] = $stores[$originalIndex];
                    }
                    
                    ksort($orderedStores);
                    $orderedStores = array_values($orderedStores); 

                    $this->clusterStores = $orderedStores;
                    // Minta gambar jalur dan hitung jarak detailnya via fetchGeometryOnly
                    $this->fetchGeometryOnly();
                } else {
                    $this->calculateTSP($stores);
                }
            } else {
                $this->calculateTSP($stores);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OSRM API Error: ' . $e->getMessage());
            $this->calculateTSP($stores);
        }
    }

    private function fetchGeometryOnly()
    {
        if (count($this->clusterStores) < 2) {
            $this->resetAnalysis();
            return;
        }

        $orderedCoords = [];
        foreach ($this->clusterStores as $store) {
            $orderedCoords[] = $store['longitude'] . ',' . $store['latitude'];
        }
        $orderedCoordsString = implode(';', $orderedCoords);
        
        try {
            $routeResponse = \Illuminate\Support\Facades\Http::timeout(10)->get("http://router.project-osrm.org/route/v1/driving/{$orderedCoordsString}", [
                'overview' => 'full',
                'geometries' => 'geojson'
            ]);
            
            if ($routeResponse->successful() && $routeResponse->json()['code'] === 'Ok') {
                $routeData = $routeResponse->json()['routes'][0];
                $legs = $routeData['legs'];
                
                foreach ($this->clusterStores as $i => &$store) {
                    if ($i < count($legs)) {
                        $distKm = round($legs[$i]['distance'] / 1000, 2);
                        $store['distance_to_next'] = $distKm;
                        $store['duration_to_next'] = round(($distKm / 40 * 60) * 1.30);
                    } else {
                        $store['distance_to_next'] = 0;
                        $store['duration_to_next'] = 0;
                    }
                }
                unset($store);
                
                $this->totalDistance = round($routeData['distance'] / 1000, 2);
                
                $numEdges = count($this->clusterStores) - 1;
                $this->averageDistance = $numEdges > 0 ? round($this->totalDistance / $numEdges, 2) : 0;
                
                if ($this->totalDistance > 80) {
                    $this->efficiencyStatus = 'Terlalu Tersebar (> 80 Km)';
                } elseif ($this->totalDistance > 40) {
                    $this->efficiencyStatus = 'Wajar (40-80 Km)';
                } else {
                    $this->efficiencyStatus = 'Sangat Efisien (< 40 Km)';
                }
                
                $drivingMinutes = round(($this->totalDistance / 40 * 60) * 1.30);
                $visitMinutes = count($this->clusterStores) * 30;
                $totalMinutes = $drivingMinutes + $visitMinutes;
                
                $this->drivingDurationFormatted = $this->formatMinutesToHours($drivingMinutes);
                $this->visitDurationFormatted = $this->formatMinutesToHours($visitMinutes);
                $this->totalDurationFormatted = $this->formatMinutesToHours($totalMinutes);
                
                $this->apiGeometry = $routeData['geometry'];

                $this->dispatch('route-analyzed', route: $this->clusterStores, geometry: $this->apiGeometry);
            } else {
                $this->calculateStraightLinesFallback();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OSRM Geometry API Error: ' . $e->getMessage());
            $this->calculateStraightLinesFallback();
        }
    }

    private function calculateStraightLinesFallback()
    {
        $stores = $this->clusterStores;
        $totalDist = 0;
        $n = count($stores);

        for ($i = 0; $i < $n; $i++) {
            $stores[$i]['latitude'] = (float) $stores[$i]['latitude'];
            $stores[$i]['longitude'] = (float) $stores[$i]['longitude'];
        }

        for ($i = 0; $i < $n - 1; $i++) {
            $dist = $this->haversineGreatCircleDistance(
                $stores[$i]['latitude'], $stores[$i]['longitude'],
                $stores[$i+1]['latitude'], $stores[$i+1]['longitude']
            );
            $distKm = round($dist, 2);
            $stores[$i]['distance_to_next'] = $distKm;
            $stores[$i]['duration_to_next'] = round(($distKm / 40 * 60) * 1.30);
            $totalDist += $dist;
        }

        $stores[$n - 1]['distance_to_next'] = 0;
        $stores[$n - 1]['duration_to_next'] = 0;

        $this->clusterStores = $stores;
        $this->totalDistance = round($totalDist, 2);
        
        $numEdges = $n - 1;
        $this->averageDistance = $numEdges > 0 ? round($this->totalDistance / $numEdges, 2) : 0;

        if ($this->totalDistance > 80) {
            $this->efficiencyStatus = 'Terlalu Tersebar (> 80 Km)';
        } elseif ($this->totalDistance > 40) {
            $this->efficiencyStatus = 'Wajar (40-80 Km)';
        } else {
            $this->efficiencyStatus = 'Sangat Efisien (< 40 Km)';
        }

        $drivingMinutes = round(($this->totalDistance / 40 * 60) * 1.30);
        $visitMinutes = $n * 30;
        $totalMinutes = $drivingMinutes + $visitMinutes;
        
        $this->drivingDurationFormatted = $this->formatMinutesToHours($drivingMinutes) . ' (Manual)';
        $this->visitDurationFormatted = $this->formatMinutesToHours($visitMinutes);
        $this->totalDurationFormatted = $this->formatMinutesToHours($totalMinutes);
        
        $this->apiGeometry = null;

        $this->dispatch('route-analyzed', route: $this->clusterStores, geometry: null);
    }

    private function haversineGreatCircleDistance($latFrom, $lonFrom, $latTo, $lonTo, $earthRadius = 6371)
    {
        $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($lonFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($lonTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }

    public function openSaveModal()
    {
        if (count($this->clusterStores) === 0) {
            session()->flash('error', 'Cluster harus memiliki setidaknya 1 toko.');
            return;
        }
        $this->isSaveModalOpen = true;
    }

    public function closeSaveModal()
    {
        $this->isSaveModalOpen = false;
    }

    public function confirmSaveCluster()
    {
        if ($this->saveType === 'clustering') {
            $this->validate([
                'clusterName' => 'required|string|max:255',
                'filterTeam' => 'required|string',
            ]);
            $centerId = $this->centerStoreId ?: ($this->clusterStores[0]['id'] ?? null);
        } else {
            $this->validate([
                'jksDate' => 'required|date',
                'filterTeam' => 'required|string',
            ]);
            $centerId = null;
        }

        if (count($this->clusterStores) === 0) {
            session()->flash('error', 'Cluster harus memiliki setidaknya 1 toko.');
            return;
        }

        DB::beginTransaction();
        try {
            if ($this->saveType === 'clustering') {
                $cluster = MasterCluster::create([
                    'name' => $this->clusterName,
                    'team_sales' => $this->filterTeam,
                    'center_store_id' => $centerId,
                    'total_distance' => $this->totalDistance,
                    'total_duration_minutes' => round(($this->totalDistance / 40 * 60) * 1.30)
                ]);

                foreach ($this->clusterStores as $idx => $store) {
                    MasterClusterItem::create([
                        'master_cluster_id' => $cluster->id,
                        'store_id' => $store['id'],
                        'routing_order' => $idx + 1
                    ]);
                }
                $message = 'Cluster berhasil disimpan!';
            } else {
                $team = DB::table('fsalesman')->where('SLSNO', $this->filterTeam)->first();
                $namaTeam = $team ? $team->SLSNAME : $this->filterTeam;
                
                if ($this->jksSyncMethod === 'sync') {
                    // Full Sync: Hapus seluruh jadwal tim ini pada tanggal tersebut sebelum menimpa dengan yang baru
                    JksTeamElite::where('kode_team', $this->filterTeam)
                        ->where('tanggal', $this->jksDate)
                        ->delete();
                }

                $inserts = [];
                foreach ($this->clusterStores as $idx => $store) {
                    $exists = false;
                    if ($this->jksSyncMethod === 'skip') {
                        $exists = JksTeamElite::where('kode_team', $this->filterTeam)
                            ->where('tanggal', $this->jksDate)
                            ->where('custno', $store['customer_code_prc'])
                            ->exists();
                    }

                    if (!$exists) {
                        // Ambil region dan area dari master_distributor
                        $storeDetails = DB::table('list_toko_pareto_team_elite as l')
                            ->leftJoin('master_distributors as md', 'l.distributor_code', '=', 'md.distributor_code')
                            ->where('l.customer_code_prc', $store['customer_code_prc'])
                            ->where('l.distributor_code', $store['distributor_code'])
                            ->select(
                                'md.region_code as kode_region',
                                'md.region_name as nama_region',
                                'md.area_code as kode_area',
                                'md.area_name as nama_area',
                                'l.distributor_code',
                                'md.distributor_name',
                                'l.customer_code_prc as custno',
                                'l.customer_name as custname',
                                'l.customer_address as addres'
                            )
                            ->first();

                        if ($storeDetails) {
                            $inserts[] = [
                                'tanggal'          => $this->jksDate,
                                'kode_team'        => $this->filterTeam,
                                'nama_team'        => $namaTeam,
                                'kode_region'      => $storeDetails->kode_region,
                                'nama_region'      => $storeDetails->nama_region,
                                'kode_area'        => $storeDetails->kode_area,
                                'nama_area'        => $storeDetails->nama_area,
                                'distributor_code' => $storeDetails->distributor_code,
                                'distributor_name' => $storeDetails->distributor_name,
                                'custno'           => $storeDetails->custno,
                                'custname'         => $storeDetails->custname,
                                'addres'           => $storeDetails->addres,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ];
                        }
                    }
                }
                
                if (count($inserts) > 0) {
                    JksTeamElite::insert($inserts);
                    if (class_exists('\App\Helpers\ActivityLogger')) {
                        \App\Helpers\ActivityLogger::log('Generate JKS Team Elite', "Menambahkan ".count($inserts)." toko dari Clustering ke jadwal team: {$namaTeam} ({$this->jksDate})");
                    }
                    $message = count($inserts) . ' toko berhasil ditambahkan ke jadwal JKS!';
                } else {
                    $message = 'Semua toko dalam cluster sudah ada di jadwal JKS untuk tanggal tersebut.';
                }
            }

            DB::commit();
            
            $this->isSaveModalOpen = false;
            $this->clusterName = '';
            
            $this->dispatch('close-modal'); // trigger UI response if needed
            session()->flash('message', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.call-plan.jks-team-elite.clustering.index')->layout('layouts.app');
    }
}
