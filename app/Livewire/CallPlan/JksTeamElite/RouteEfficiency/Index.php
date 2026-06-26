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
    public $filterStartDate = '';
    public $filterEndDate = '';
    public $teams = [];

    // Map and Analysis Data
    public $routesByDate = [];
    public $totalDistance = 0;
    public $averageDistance = 0;
    public $efficiencyStatus = '';
    public $drivingDurationFormatted = '-';
    public $visitDurationFormatted = '-';
    public $totalDurationFormatted = '-';

    public function mount()
    {
        $this->loadTeams();
        $this->filterStartDate = Carbon::now()->format('Y-m-d');
        $this->filterEndDate = Carbon::now()->format('Y-m-d');
        if (count($this->teams) > 0) {
            $this->filterTeam = $this->teams[0]->kode_team;
        }
        $this->analyzeRoute();
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
    public function updatedFilterStartDate() { $this->analyzeRoute(); }
    public function updatedFilterEndDate() { $this->analyzeRoute(); }

    public function analyzeRoute()
    {
        if (empty($this->filterTeam) || empty($this->filterStartDate) || empty($this->filterEndDate)) {
            $this->resetAnalysis();
            return;
        }

        $stores = JksTeamElite::query()
            ->select('jks_team_elite.tanggal', 'jks_team_elite.custno', 'jks_team_elite.custname', 'jks_team_elite.distributor_code', 'l.latitude', 'l.longitude', 'l.customer_address')
            ->leftJoin('list_toko_pareto_team_elite as l', function($join) {
                $join->on('jks_team_elite.custno', '=', 'l.customer_code_prc')
                     ->on('jks_team_elite.distributor_code', '=', 'l.distributor_code');
            })
            ->whereBetween('jks_team_elite.tanggal', [$this->filterStartDate, $this->filterEndDate])
            ->where('jks_team_elite.kode_team', $this->filterTeam)
            ->whereNotNull('l.latitude')
            ->whereNotNull('l.longitude')
            ->where('l.latitude', '!=', 0)
            ->where('l.longitude', '!=', 0)
            ->orderBy('jks_team_elite.tanggal', 'asc')
            ->get()
            ->groupBy('tanggal')
            ->toArray();

        if (empty($stores)) {
            $this->resetAnalysis();
            return;
        }

        $this->routesByDate = [];
        $totalDistAll = 0;
        $totalVisitMinutesAll = 0;
        $totalDrivingMinutesAll = 0;
        $routeSegments = 0;

        $palette = ['#3b82f6', '#22c55e', '#ef4444', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6'];
        $colorIndex = 0;

        foreach ($stores as $date => $dayStores) {
            if (count($dayStores) < 2) {
                $this->routesByDate[] = [
                    'date' => $date,
                    'color' => $palette[$colorIndex % count($palette)],
                    'route' => $dayStores,
                    'geometry' => null,
                    'distance' => 0,
                    'visitMinutes' => count($dayStores) * 30,
                    'drivingMinutes' => 0,
                ];
                $colorIndex++;
                continue;
            }

            $result = $this->fetchRealRouteData($dayStores);
            
            $result['date'] = $date;
            $result['color'] = $palette[$colorIndex % count($palette)];
            $this->routesByDate[] = $result;
            
            $totalDistAll += $result['distance'];
            $totalVisitMinutesAll += count($result['route']) * 30;
            $totalDrivingMinutesAll += $result['drivingMinutes'];
            $routeSegments += count($result['route']) - 1;

            $colorIndex++;
        }

        $this->totalDistance = round($totalDistAll, 2);
        $this->averageDistance = $routeSegments > 0 ? round($totalDistAll / $routeSegments, 2) : 0;
        
        $daysCount = count($this->routesByDate);
        $avgDailyDist = $daysCount > 0 ? ($this->totalDistance / $daysCount) : 0;

        if ($avgDailyDist > 80) {
            $this->efficiencyStatus = 'Terlalu Tersebar (> 80 Km/Hari)';
        } elseif ($avgDailyDist > 40) {
            $this->efficiencyStatus = 'Wajar (40-80 Km/Hari)';
        } else {
            $this->efficiencyStatus = 'Sangat Efisien (< 40 Km/Hari)';
        }

        $totalMinutesAll = $totalVisitMinutesAll + $totalDrivingMinutesAll;
        
        $this->drivingDurationFormatted = $this->formatMinutesToHours($totalDrivingMinutesAll);
        $this->visitDurationFormatted = $this->formatMinutesToHours($totalVisitMinutesAll);
        $this->totalDurationFormatted = $this->formatMinutesToHours($totalMinutesAll);

        $this->dispatch('route-analyzed', routesByDate: $this->routesByDate);
    }

    private function resetAnalysis()
    {
        $this->routesByDate = [];
        $this->totalDistance = 0;
        $this->averageDistance = 0;
        $this->efficiencyStatus = '-';
        $this->drivingDurationFormatted = '-';
        $this->visitDurationFormatted = '-';
        $this->totalDurationFormatted = '-';
        $this->dispatch('route-analyzed', routesByDate: []);
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

        $dist = round($totalDist, 2);
        return [
            'route' => $route,
            'geometry' => null,
            'distance' => $dist,
            'drivingMinutes' => round(($dist / 40 * 60) * 1.30)
        ];
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
            return $this->calculateTSP($stores);
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

                    $dist = round($trip['distance'] / 1000, 2);
                    
                    return [
                        'route' => $orderedStores,
                        'geometry' => $trip['geometry'],
                        'distance' => $dist,
                        'drivingMinutes' => round(($dist / 40 * 60) * 1.30)
                    ];
                }
            }
        } catch (\Exception $e) {
            // fallback handled below
        }

        return $this->calculateTSP($stores);
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
