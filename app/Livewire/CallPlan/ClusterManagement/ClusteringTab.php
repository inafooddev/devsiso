<?php

namespace App\Livewire\CallPlan\ClusterManagement;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\MasterCluster;
use App\Models\MasterClusterItem;

class ClusteringTab extends Component
{
    public $clusterName = '';
    public $filterTeam = '';
    public $saveType = 'clustering';
    public $teams = [];
    
    public $searchDistributor = '';
    public $distributorOptions = [];
    public $selectedDistributorCode = '';
    public $selectedDistributorName = '';
    
    // Balanced Clustering Inputs
    public $targetClusters = 24;
    public $maxStoresPerCluster = 30;
    public $maxRadiusKm = 15;
    public $useSpatialPenalty = false;

    // Modal State
    public $isSaveModalOpen = false;

    // Map Data
    public $clusterStores = [];
    
    // Bulk Actions
    public $selectedStoreIds = [];

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
            $query = DB::table('master_distributors')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('distributor_code', 'ilike', '%' . $this->searchDistributor . '%')
                      ->orWhere('distributor_name', 'ilike', '%' . $this->searchDistributor . '%');
                });
                
            $user = auth()->user();
            if ($user && !$user->hasRole('admin')) {
                if (!empty($user->supervisor_code)) {
                    $query->where('supervisor_code', $user->supervisor_code);
                } elseif (!empty($user->area_code) && is_array($user->area_code) && count($user->area_code) > 0) {
                    $query->whereIn('area_code', $user->area_code);
                } elseif (!empty($user->region_code) && is_array($user->region_code) && count($user->region_code) > 0) {
                    $query->whereIn('region_code', $user->region_code);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $this->distributorOptions = $query->select('distributor_code', 'distributor_name')
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
        $this->clusterStores = [];
    }

    public function clearDistributor()
    {
        $this->selectedDistributorCode = '';
        $this->selectedDistributorName = '';
        $this->searchDistributor = '';
        $this->distributorOptions = [];
        $this->clusterStores = [];
    }

    public function generateMasterClusters()
    {
        if (empty($this->selectedDistributorCode)) {
            session()->flash('error', 'Pilih Distributor terlebih dahulu.');
            return;
        }

        $sql = "
            SELECT DISTINCT ON (t.id) t.*, b.wadmkc as kecamatan, b.wadmkd as kelurahan
            FROM list_toko_pareto_team_elite t
            LEFT JOIN batas_wilayah b
            ON ST_Contains(b.geom, ST_SetSRID(ST_MakePoint(CAST(t.longitude AS double precision), CAST(t.latitude AS double precision)), 4326))
            WHERE t.distributor_code = :distributor
            AND t.latitude IS NOT NULL AND t.longitude IS NOT NULL
            AND t.latitude != '0' AND t.longitude != '0'
            ORDER BY t.id
        ";
        
        $allStores = collect(DB::select($sql, ['distributor' => $this->selectedDistributorCode]))
            ->map(fn($item) => (array) $item)
            ->toArray();

        if (count($allStores) === 0) {
            session()->flash('error', 'Tidak ada toko dengan koordinat valid untuk distributor ini.');
            return;
        }

        $savedStoreIds = DB::table('master_cluster_items')->pluck('store_id')->toArray();
        $savedStoreIds = array_flip($savedStoreIds);

        $activeStores = [];
        $bypassedStores = [];

        foreach ($allStores as $store) {
            if (isset($savedStoreIds[$store['id']])) {
                $bypassedStores[] = $store;
            } else {
                $activeStores[] = $store;
            }
        }

        if (count($activeStores) === 0) {
            session()->flash('error', 'Semua toko pada distributor ini sudah masuk ke dalam cluster. Anda tetap bisa menariknya secara manual di peta.');
        }

        $k = max(1, (int) $this->targetClusters);
        $maxCapacity = max(1, (int) $this->maxStoresPerCluster);
        $maxRadius = (float) $this->maxRadiusKm;

        $centroids = [];
        if (count($activeStores) > 0) {
            $keys = array_rand($activeStores, min($k, count($activeStores)));
            if (!is_array($keys)) $keys = [$keys];
            
            foreach ($keys as $i => $key) {
                $centroids[$i] = [
                    'lat' => (float) $activeStores[$key]['latitude'],
                    'lon' => (float) $activeStores[$key]['longitude'],
                    'kecamatan' => $activeStores[$key]['kecamatan'] ?? '',
                    'kelurahan' => $activeStores[$key]['kelurahan'] ?? '',
                    'stores' => []
                ];
            }
        }

        // Pre-compute Kelurahan center coordinates for Neighborhood Adjacency Penalty
        $kelurahanCentroids = [];
        foreach ($activeStores as $s) {
            $kel = trim((string)($s['kelurahan'] ?? ''));
            if (!empty($kel)) {
                if (!isset($kelurahanCentroids[$kel])) {
                    $kelurahanCentroids[$kel] = ['lats' => [], 'lons' => []];
                }
                $kelurahanCentroids[$kel]['lats'][] = (float)$s['latitude'];
                $kelurahanCentroids[$kel]['lons'][] = (float)$s['longitude'];
            }
        }

        $kelMap = [];
        foreach ($kelurahanCentroids as $kel => $coords) {
            $kelMap[$kel] = [
                'lat' => array_sum($coords['lats']) / count($coords['lats']),
                'lon' => array_sum($coords['lons']) / count($coords['lons']),
            ];
        }

        $maxIterations = 15;
        for ($iter = 0; $iter < $maxIterations; $iter++) {
            foreach ($centroids as &$c) {
                $c['stores'] = [];
            }
            unset($c);

            $unclustered = [];
            $storeAssignments = [];

            foreach ($activeStores as $storeIdx => $store) {
                $lat = (float) $store['latitude'];
                $lon = (float) $store['longitude'];
                
                $distances = [];
                foreach ($centroids as $cIdx => $c) {
                    $dist = $this->haversineGreatCircleDistance($lat, $lon, $c['lat'], $c['lon']);
                    
                    if ($this->useSpatialPenalty && !empty($store['kelurahan']) && !empty($c['kelurahan'])) {
                        $storeKel = trim((string)$store['kelurahan']);
                        $cKel = trim((string)$c['kelurahan']);

                        if ($storeKel === $cKel) {
                            $dist += 0.0; // Kelurahan Sama: Ideal (0 km penalty)
                        } else {
                            $kelDist = 999;
                            if (isset($kelMap[$storeKel]) && isset($kelMap[$cKel])) {
                                $kelDist = $this->haversineGreatCircleDistance(
                                    $kelMap[$storeKel]['lat'], $kelMap[$storeKel]['lon'],
                                    $kelMap[$cKel]['lat'], $kelMap[$cKel]['lon']
                                );
                            }

                            if ($kelDist <= 2.5) {
                                $dist += 0.8; // Kelurahan Tetangga Bersebelahan (<= 2.5 km penalty)
                            } else {
                                $dist += 4.0; // Beda Kelurahan Jauh / Tidak Menempel (4.0 km penalty)
                            }
                        }
                    }

                    $distances[] = [
                        'centroid_idx' => $cIdx,
                        'distance' => $dist
                    ];
                }
                usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);
                
                $storeAssignments[] = [
                    'store_idx' => $storeIdx,
                    'distances' => $distances
                ];
            }

            usort($storeAssignments, function($a, $b) use ($activeStores) {
                $pilarA = (int) ($activeStores[$a['store_idx']]['pilar'] ?? 999);
                $pilarB = (int) ($activeStores[$b['store_idx']]['pilar'] ?? 999);
                
                if ($pilarA !== $pilarB) {
                    return $pilarA <=> $pilarB;
                }
                
                return $a['distances'][0]['distance'] <=> $b['distances'][0]['distance'];
            });

            foreach ($storeAssignments as $sa) {
                $storeIdx = $sa['store_idx'];
                $assigned = false;

                foreach ($sa['distances'] as $dInfo) {
                    $cIdx = $dInfo['centroid_idx'];
                    $dist = $dInfo['distance'];

                    if ($dist <= $maxRadius && count($centroids[$cIdx]['stores']) < $maxCapacity) {
                        $centroids[$cIdx]['stores'][] = $activeStores[$storeIdx];
                        $assigned = true;
                        break;
                    }
                }

                if (!$assigned) {
                    $unclustered[] = $activeStores[$storeIdx];
                }
            }

            foreach ($centroids as $cIdx => &$c) {
                if (count($c['stores']) > 0) {
                    $sumLat = 0;
                    $sumLon = 0;
                    $kecamatans = [];
                    $kelurahans = [];
                    foreach ($c['stores'] as $s) {
                        $sumLat += (float) $s['latitude'];
                        $sumLon += (float) $s['longitude'];
                        if (!empty($s['kecamatan'])) {
                            $kecamatans[$s['kecamatan']] = ($kecamatans[$s['kecamatan']] ?? 0) + 1;
                        }
                        if (!empty($s['kelurahan'])) {
                            $kelurahans[$s['kelurahan']] = ($kelurahans[$s['kelurahan']] ?? 0) + 1;
                        }
                    }
                    $c['lat'] = $sumLat / count($c['stores']);
                    $c['lon'] = $sumLon / count($c['stores']);
                    
                    if (!empty($kecamatans)) {
                        arsort($kecamatans);
                        $c['kecamatan'] = array_key_first($kecamatans);
                    }
                    if (!empty($kelurahans)) {
                        arsort($kelurahans);
                        $c['kelurahan'] = array_key_first($kelurahans);
                    }
                }
            }
            unset($c);
        }

        $formattedClusters = [];
        foreach ($centroids as $cIdx => $c) {
            if (count($c['stores']) > 0) {
                foreach ($c['stores'] as &$s) {
                    $s['cluster_id'] = $cIdx + 1;
                }
                unset($s);
                $formattedClusters = array_merge($formattedClusters, $c['stores']);
            }
        }

        foreach ($unclustered as &$s) {
            $s['cluster_id'] = 0;
        }
        unset($s);
        $formattedClusters = array_merge($formattedClusters, $unclustered);
        
        foreach ($bypassedStores as &$s) {
            $s['cluster_id'] = -1;
        }
        unset($s);
        $formattedClusters = array_merge($formattedClusters, $bypassedStores);

        $this->clusterStores = $formattedClusters;
        $this->dispatch('clusters-generated', stores: $this->clusterStores);
        session()->flash('message', 'Master clustering berhasil di-generate (' . count($activeStores) . ' toko terproses otomatis).');
    }

    public function reassignStore($storeId, $newClusterId)
    {
        if ($newClusterId === '' || $newClusterId === null) return;
        
        foreach ($this->clusterStores as $idx => $store) {
            if ($store['id'] == $storeId) {
                $this->clusterStores[$idx]['cluster_id'] = (int)$newClusterId;
                break;
            }
        }
        
        $this->dispatch('clusters-generated', stores: $this->clusterStores);
    }

    public function dissolveCluster($clusterId)
    {
        foreach ($this->clusterStores as $idx => $store) {
            if ($store['cluster_id'] == $clusterId) {
                $this->clusterStores[$idx]['cluster_id'] = 0;
            }
        }
        $this->dispatch('clusters-generated', stores: $this->clusterStores);
    }

    public function mergeCluster($fromClusterId, $toClusterId)
    {
        if ($toClusterId === '' || $toClusterId === null) return;
        
        foreach ($this->clusterStores as $idx => $store) {
            if ($store['cluster_id'] == $fromClusterId) {
                $this->clusterStores[$idx]['cluster_id'] = (int)$toClusterId;
            }
        }
        $this->dispatch('clusters-generated', stores: $this->clusterStores);
    }

    public function toggleSelectClusterStores($clusterId)
    {
        $storesInCluster = array_filter($this->clusterStores, fn($s) => $s['cluster_id'] == $clusterId);
        $storeIds = array_map(fn($s) => 'store-' . $s['id'], $storesInCluster);
        
        $alreadySelected = count(array_intersect($storeIds, $this->selectedStoreIds)) === count($storeIds) && count($storeIds) > 0;
        
        if ($alreadySelected) {
            $this->selectedStoreIds = array_values(array_diff($this->selectedStoreIds, $storeIds));
        } else {
            $this->selectedStoreIds = array_values(array_unique(array_merge($this->selectedStoreIds, $storeIds)));
        }
    }

    public function clearSelectedStores()
    {
        $this->selectedStoreIds = [];
    }

    public function bulkReassignStores($newClusterId)
    {
        if ($newClusterId === '' || $newClusterId === null) return;
        
        $targetIds = array_map(fn($s) => str_replace('store-', '', $s), $this->selectedStoreIds);
        
        foreach ($this->clusterStores as $idx => $store) {
            if (in_array($store['id'], $targetIds)) {
                $this->clusterStores[$idx]['cluster_id'] = (int)$newClusterId;
            }
        }
        
        $this->clearSelectedStores();
        $this->dispatch('clusters-generated', stores: $this->clusterStores);
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
            session()->flash('error', 'Tidak ada cluster yang bisa disimpan.');
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
        $validStores = array_filter($this->clusterStores, fn($s) => $s['cluster_id'] > 0);
        
        if (count($validStores) === 0) {
            session()->flash('error', 'Tidak ada data cluster valid untuk disimpan.');
            $this->closeSaveModal();
            return;
        }

        DB::beginTransaction();
        try {
            $groups = [];
            foreach ($validStores as $store) {
                $groups[$store['cluster_id']][] = $store;
            }

            foreach ($groups as $clusterId => $storesInCluster) {
                $centerStore = $storesInCluster[0]['id'];

                $kecamatanList = [];
                foreach ($storesInCluster as $store) {
                    if (!empty($store['kecamatan'])) {
                        $kecamatanList[] = $store['kecamatan'];
                    }
                }
                $kecamatanStr = count($kecamatanList) > 0 ? ' (' . implode(', ', array_unique($kecamatanList)) . ')' : '';

                $clusterName = 'Cluster ' . $clusterId . $kecamatanStr . ' - ' . $this->selectedDistributorName . ' (' . now()->format('Ymd') . ')';
                if (strlen($clusterName) > 255) {
                    $clusterName = substr($clusterName, 0, 250) . '...';
                }

                $cluster = MasterCluster::create([
                    'name' => $clusterName,
                    'team_sales' => $this->filterTeam,
                    'distributor_code' => $this->selectedDistributorCode,
                    'center_store_id' => $centerStore,
                    'created_by' => auth()->id() ?? 1,
                    'items_count' => count($storesInCluster)
                ]);

                foreach ($storesInCluster as $index => $store) {
                    MasterClusterItem::where('store_id', $store['id'])->delete();
                    
                    MasterClusterItem::create([
                        'master_cluster_id' => $cluster->id,
                        'store_id' => $store['id'],
                        'routing_order' => $index + 1
                    ]);
                }
            }

            DB::commit();
            session()->flash('message', 'Semua Master Cluster berhasil disimpan!');
            $this->closeSaveModal();
            $this->clusterStores = [];
            $this->dispatch('clusters-generated', stores: []);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan cluster: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.call-plan.cluster-management.clustering-tab');
    }
}
