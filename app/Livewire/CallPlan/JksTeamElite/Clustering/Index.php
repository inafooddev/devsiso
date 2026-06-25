<?php

namespace App\Livewire\CallPlan\JksTeamElite\Clustering;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\MasterCluster;
use App\Models\MasterClusterItem;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'call-plan.jks-team-elite.clustering'; 

    public $clusterName = '';
    public $filterTeam = '';
    public $teams = [];
    
    public $searchCenterText = '';
    public $searchCenterResults = [];
    public $centerStoreId = null;
    public $centerStore = null;
    
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

    public function updatedSearchCenterText()
    {
        if (strlen($this->searchCenterText) > 2) {
            $this->searchCenterResults = DB::table('list_toko_pareto_team_elite')
                ->where(function($q) {
                    $q->where('customer_name', 'ilike', '%' . $this->searchCenterText . '%')
                      ->orWhere('customer_code_prc', 'ilike', '%' . $this->searchCenterText . '%');
                })
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->take(10)
                ->get()
                ->toArray();
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
            $this->analyzeRoute();
        }
        $this->searchAddText = '';
        $this->searchAddResults = [];
    }

    public function removeStore($index)
    {
        if (isset($this->clusterStores[$index])) {
            array_splice($this->clusterStores, $index, 1);
            $this->analyzeRoute();
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

        // 2. Define the Local Area Pool (radius geografis wajar)
        $poolSize = max(50, $this->candidateCount * 3);
        $localPool = array_slice($distances, 0, $poolSize);

        // 3. Pisahkan Center Store agar PASTI masuk ke dalam cluster
        $centerCandidate = null;
        $otherCandidates = [];

        foreach ($localPool as $item) {
            if ($item['store']->id == $this->centerStore->id) {
                $centerCandidate = $item;
            } else {
                $otherCandidates[] = $item;
            }
        }

        // 4. Sort Other Candidates by Pilar Priority, then Distance
        usort($otherCandidates, function($a, $b) {
            $pilarA = $a['store']->pilar ?? 'Z';
            $pilarB = $b['store']->pilar ?? 'Z';
            
            // Prioritas: '1. RWO' < '2. PNR' < '3. NGVO' < '4. GRO' < 'Z'
            if ($pilarA !== $pilarB) {
                return strcmp($pilarA, $pilarB); 
            }
            // Jika Pilar sama, ambil yang terdekat
            return $a['distance'] <=> $b['distance'];
        });

        // 5. Gabungkan Center Store dengan N-1 kandidat teratas berdasarkan prioritas
        $topCandidates = [];
        if ($centerCandidate) {
            $topCandidates[] = $centerCandidate;
        }
        
        $needed = $this->candidateCount - count($topCandidates);
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
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get("http://router.project-osrm.org/trip/v1/driving/{$coordsString}", [
                'roundtrip' => 'false',
                'source' => 'first',
                'geometries' => 'geojson'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['code'] === 'Ok') {
                    $trip = $data['trips'][0];
                    $waypoints = $data['waypoints']; 
                    
                    $orderedStores = [];
                    foreach ($waypoints as $originalIndex => $wp) {
                        $newPosition = $wp['waypoint_index'];
                        $orderedStores[$newPosition] = $stores[$originalIndex];
                    }
                    
                    ksort($orderedStores);
                    $orderedStores = array_values($orderedStores); 

                    $legs = $trip['legs'];
                    foreach ($orderedStores as $i => &$store) {
                        if ($i < count($legs)) {
                            $distKm = round($legs[$i]['distance'] / 1000, 2);
                            $store['distance_to_next'] = $distKm;
                            $store['duration_to_next'] = round(($distKm / 40 * 60) * 1.30);
                        } else {
                            $store['distance_to_next'] = 0;
                            $store['duration_to_next'] = 0;
                        }
                    }

                    $this->clusterStores = $orderedStores;
                    $this->totalDistance = round($trip['distance'] / 1000, 2); 
                    
                    $drivingMinutes = round(($this->totalDistance / 40 * 60) * 1.30);
                    $visitMinutes = count($orderedStores) * 30;
                    $totalMinutes = $drivingMinutes + $visitMinutes;
                    
                    $this->drivingDurationFormatted = $this->formatMinutesToHours($drivingMinutes);
                    $this->visitDurationFormatted = $this->formatMinutesToHours($visitMinutes);
                    $this->totalDurationFormatted = $this->formatMinutesToHours($totalMinutes);
                    
                    $numEdges = count($orderedStores) - 1;
                    $this->averageDistance = $numEdges > 0 ? round($this->totalDistance / $numEdges, 2) : 0;

                    if ($this->totalDistance > 80) {
                        $this->efficiencyStatus = 'Terlalu Tersebar (> 80 Km)';
                    } elseif ($this->totalDistance > 40) {
                        $this->efficiencyStatus = 'Wajar (40-80 Km)';
                    } else {
                        $this->efficiencyStatus = 'Sangat Efisien (< 40 Km)';
                    }

                    $this->apiGeometry = $trip['geometry'];

                    $this->dispatch('route-analyzed', route: $this->clusterStores, geometry: $this->apiGeometry);
                } else {
                    $this->calculateTSP($stores);
                }
            } else {
                $this->calculateTSP($stores);
            }
        } catch (\Exception $e) {
            $this->calculateTSP($stores);
        }
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
        $this->validate([
            'clusterName' => 'required|string|max:255',
            'filterTeam' => 'required|string',
            'centerStoreId' => 'required'
        ]);

        if (count($this->clusterStores) === 0) {
            session()->flash('error', 'Cluster harus memiliki setidaknya 1 toko.');
            return;
        }

        DB::beginTransaction();
        try {
            $cluster = MasterCluster::create([
                'name' => $this->clusterName,
                'team_sales' => $this->filterTeam,
                'center_store_id' => $this->centerStoreId,
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

            DB::commit();
            
            $this->isSaveModalOpen = false;
            $this->clusterName = '';
            
            $this->dispatch('close-modal'); // trigger UI response if needed
            session()->flash('message', 'Cluster berhasil disimpan!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan cluster: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.call-plan.jks-team-elite.clustering.index')->layout('layouts.app');
    }
}
