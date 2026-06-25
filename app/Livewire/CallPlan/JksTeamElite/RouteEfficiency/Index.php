<?php

namespace App\Livewire\CallPlan\JksTeamElite\RouteEfficiency;

use Livewire\Component;
use App\Models\JksTeamElite;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\EnforcesMenuPermissions;

class Index extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'jks-team-elite.route-efficiency'; 

    public $filterTeam = '';
    public $filterDate = '';
    public $teams = [];

    // Map and Analysis Data
    public $optimalRoute = [];
    public $totalDistance = 0;
    public $averageDistance = 0;
    public $efficiencyStatus = '';
    public $drivingDurationFormatted = '-';
    public $visitDurationFormatted = '-';
    public $totalDurationFormatted = '-';
    public $apiGeometry = null;

    public function mount()
    {
        $this->loadTeams();
        $this->filterDate = Carbon::now()->format('Y-m-d');
        if (count($this->teams) > 0) {
            $this->filterTeam = $this->teams[0]->kode_team;
        }
    }

    private function loadTeams()
    {
        try {
            $query = DB::table('team_elite_code_mappings as tecm')
                ->select(
                    'tecm.team_elite_code as kode_team',
                    'f.SLSNAME as nama_team'
                )
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

    public function updatedFilterTeam() { $this->analyzeRoute(); }
    public function updatedFilterDate() { $this->analyzeRoute(); }

    public function analyzeRoute()
    {
        if (empty($this->filterTeam) || empty($this->filterDate)) {
            $this->resetAnalysis();
            return;
        }

        $stores = JksTeamElite::query()
            ->select('jks_team_elite.custno', 'jks_team_elite.custname', 'jks_team_elite.distributor_code', 'l.latitude', 'l.longitude', 'l.customer_address')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->where('jks_team_elite.tanggal', $this->filterDate)
            ->where('jks_team_elite.kode_team', $this->filterTeam)
            ->whereNotNull('l.latitude')
            ->whereNotNull('l.longitude')
            ->where('l.latitude', '!=', 0)
            ->where('l.longitude', '!=', 0)
            ->get()
            ->toArray();

        if (count($stores) < 2) {
            $this->resetAnalysis();
            $this->optimalRoute = $stores;
            $this->dispatch('route-analyzed', route: $this->optimalRoute, geometry: null);
            return;
        }

        $this->fetchRealRouteData($stores);
    }

    private function resetAnalysis()
    {
        $this->optimalRoute = [];
        $this->totalDistance = 0;
        $this->averageDistance = 0;
        $this->efficiencyStatus = '-';
        $this->drivingDurationFormatted = '-';
        $this->visitDurationFormatted = '-';
        $this->totalDurationFormatted = '-';
        $this->apiGeometry = null;
        $this->dispatch('route-analyzed', route: [], geometry: null);
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

        $this->optimalRoute = $route;
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

        $this->dispatch('route-analyzed', route: $this->optimalRoute, geometry: null);
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

                    $this->optimalRoute = $orderedStores;
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

                    $this->dispatch('route-analyzed', route: $this->optimalRoute, geometry: $this->apiGeometry);
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

    public function render()
    {
        return view('livewire.call-plan.jks-team-elite.route-efficiency.index')->layout('layouts.app');
    }
}
