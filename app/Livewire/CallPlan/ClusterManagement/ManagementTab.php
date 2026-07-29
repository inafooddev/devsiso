<?php

namespace App\Livewire\CallPlan\ClusterManagement;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\MasterCluster;
use App\Models\MasterClusterItem;

class ManagementTab extends Component
{
    public $managementSelectedTeam = '';
    public $managementSelectedDistributor = '';
    public $managementTeams = [];
    public $managementDistributors = [];
    public $managementClusterStores = [];
    public $unclusteredStores = [];
    
    public $paretoTotalStores = 0;
    public $paretoPilarCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];

    public $isViewModalOpen = false;
    public $selectedCluster = null;
    public $selectedClusterItems = [];

    // Action 1: Add to JKS Team Elite Modal
    public $isJksModalOpen = false;
    public $jksClusterId = null;
    public $jksTanggal = '';

    // Action 2: Join / Merge Cluster Modal
    public $isMergeModalOpen = false;
    public $sourceClusterId = null;
    public $targetClusterId = null;

    // Action 3: Move Store to Another Cluster Modal
    public $isMoveStoreModalOpen = false;
    public $movingItemId = null;
    public $movingUnclusteredStoreId = null;
    public $movingStoreName = '';
    public $targetClusterForStore = null;

    protected $listeners = ['apply-management-filter' => 'handleFilterApplied'];

    public function mount()
    {
        $this->loadManagementTeams();
    }

    public function handleFilterApplied($params)
    {
        $this->managementSelectedTeam = $params['team'] ?? '';
        $this->managementSelectedDistributor = $params['distributor'] ?? '';
        $this->loadManagementClusters();
    }

    public function updatedManagementSelectedTeam()
    {
        $this->managementSelectedDistributor = '';
        $this->loadManagementDistributors();
    }

    public function applyFilter()
    {
        $this->loadManagementClusters();
    }

    public function loadManagementTeams()
    {
        try {
            $query = DB::table('master_clusters as mc')
                ->select('mc.team_sales as kode_team', 'f.SLSNAME as nama_team')
                ->join('fsalesman as f', 'mc.team_sales', '=', 'f.SLSNO')
                ->groupBy('mc.team_sales', 'f.SLSNAME')
                ->orderBy('f.SLSNAME', 'asc');

            $user = auth()->user();
            if ($user && !$user->hasRole('admin')) {
                $query->join('team_elite_code_mappings as tecm', 'mc.team_sales', '=', 'tecm.team_elite_code');
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

            $this->managementTeams = $query->get()->toArray();
        } catch (\Exception $e) {
            $this->managementTeams = [];
        }
    }

    public function loadManagementDistributors()
    {
        $this->managementDistributors = [];
        if (empty($this->managementSelectedTeam)) return;

        try {
            $distCodes = DB::table('master_distributors as md')
                ->leftJoin('team_elite_code_mappings as tecm', 'md.supervisor_code', '=', 'tecm.siso_code')
                ->where(function($q) {
                    $q->where('tecm.team_elite_code', $this->managementSelectedTeam)
                      ->orWhere('md.supervisor_code', $this->managementSelectedTeam);
                })
                ->select('md.distributor_code', 'md.distributor_name')
                ->distinct()
                ->orderBy('md.distributor_name')
                ->get()
                ->toArray();

            $clusterDistCodes = DB::table('master_clusters')
                ->where('team_sales', $this->managementSelectedTeam)
                ->whereNotNull('distributor_code')
                ->select('distributor_code')
                ->distinct()
                ->pluck('distributor_code')
                ->toArray();

            $existingCodes = array_column($distCodes, 'distributor_code');
            $missingCodes = array_diff($clusterDistCodes, $existingCodes);
            if (!empty($missingCodes)) {
                $extraDists = DB::table('master_distributors')
                    ->whereIn('distributor_code', $missingCodes)
                    ->select('distributor_code', 'distributor_name')
                    ->get()
                    ->toArray();
                $distCodes = array_merge($distCodes, $extraDists);
            }

            $this->managementDistributors = $distCodes;
        } catch (\Exception $e) {
            $this->managementDistributors = [];
        }
    }

    public function loadManagementClusters()
    {
        $this->loadManagementTeams();
        $this->loadManagementDistributors();

        $this->managementClusterStores = [];
        $this->unclusteredStores = [];
        $this->paretoTotalStores = 0;
        $this->paretoPilarCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];

        if (empty($this->managementSelectedTeam)) {
            $this->dispatch('management-clusters-generated', stores: []);
            return;
        }

        // Determine distributor filter
        if (!empty($this->managementSelectedDistributor)) {
            $allDistCodes = [$this->managementSelectedDistributor];
        } else {
            $distCodes = array_column($this->managementDistributors, 'distributor_code');
            $clusterDistCodes = MasterCluster::where('team_sales', $this->managementSelectedTeam)
                ->whereNotNull('distributor_code')
                ->pluck('distributor_code')
                ->toArray();

            $allDistCodes = array_unique(array_merge($distCodes, $clusterDistCodes));
        }

        if (!empty($allDistCodes)) {
            $paretoQuery = DB::table('list_toko_pareto_team_elite')
                ->whereIn('distributor_code', $allDistCodes);
            
            $this->paretoTotalStores = (clone $paretoQuery)->count();
            
            $pilarRows = (clone $paretoQuery)
                ->select('pilar', DB::raw('count(*) as total'))
                ->groupBy('pilar')
                ->get();
            
            $pilarCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];
            foreach ($pilarRows as $r) {
                $pStr = (string)$r->pilar;
                if (str_contains($pStr, '1.')) $pilarCounts['1'] += $r->total;
                elseif (str_contains($pStr, '2.')) $pilarCounts['2'] += $r->total;
                elseif (str_contains($pStr, '3.')) $pilarCounts['3'] += $r->total;
                elseif (str_contains($pStr, '4.')) $pilarCounts['4'] += $r->total;
            }
            $this->paretoPilarCounts = $pilarCounts;
        }

        $clusterQuery = MasterCluster::where('team_sales', $this->managementSelectedTeam);
        if (!empty($this->managementSelectedDistributor)) {
            $clusterQuery->where('distributor_code', $this->managementSelectedDistributor);
        }
        $clusters = $clusterQuery->get();
        $clusterIds = $clusters->pluck('id')->toArray();

        $stores = [];
        $clusteredStoreIds = [];

        if (!empty($clusterIds)) {
            $items = DB::table('master_cluster_items as mci')
                ->join('list_toko_pareto_team_elite as pareto', 'mci.store_id', '=', 'pareto.id')
                ->whereIn('mci.master_cluster_id', $clusterIds)
                ->select(
                    'mci.id as item_id',
                    'mci.master_cluster_id',
                    'mci.store_id',
                    'mci.routing_order',
                    'pareto.customer_code_prc',
                    'pareto.customer_name',
                    'pareto.customer_address',
                    'pareto.distributor_code',
                    'pareto.latitude',
                    'pareto.longitude',
                    'pareto.pilar',
                    'pareto.kecamatan as kecamatan',
                    'pareto.desa as kelurahan'
                )
                ->orderBy('mci.master_cluster_id')
                ->orderBy('mci.routing_order')
                ->get();

            foreach ($items as $item) {
                $clusteredStoreIds[] = $item->store_id;
                $stores[] = [
                    'id' => $item->store_id,
                    'item_id' => $item->item_id,
                    'cluster_id' => $item->master_cluster_id,
                    'latitude' => (float)$item->latitude,
                    'longitude' => (float)$item->longitude,
                    'customer_code_prc' => $item->customer_code_prc,
                    'customer_name' => $item->customer_name,
                    'customer_address' => $item->customer_address,
                    'distributor_code' => $item->distributor_code,
                    'kecamatan' => $item->kecamatan,
                    'kelurahan' => $item->kelurahan,
                    'pilar' => $item->pilar,
                    'routing_order' => $item->routing_order
                ];
            }
        }

        $this->managementClusterStores = $stores;

        // Fetch Unclustered Pareto stores for this team/distributor
        if (!empty($allDistCodes)) {
            $unclusteredQuery = DB::table('list_toko_pareto_team_elite as pareto')
                ->whereIn('pareto.distributor_code', $allDistCodes);

            if (!empty($clusteredStoreIds)) {
                $unclusteredQuery->whereNotIn('pareto.id', array_unique($clusteredStoreIds));
            }

            $unclusteredItems = $unclusteredQuery->select(
                'pareto.id',
                'pareto.customer_code_prc',
                'pareto.customer_name',
                'pareto.customer_address',
                'pareto.distributor_code',
                'pareto.latitude',
                'pareto.longitude',
                'pareto.pilar',
                'pareto.kecamatan as kecamatan',
                'pareto.desa as kelurahan'
            )->orderBy('pareto.customer_name')->get();

            $unclustered = [];
            foreach ($unclusteredItems as $uItem) {
                $unclustered[] = [
                    'id' => $uItem->id,
                    'item_id' => null,
                    'cluster_id' => 0, // 0 means unclustered
                    'latitude' => (float)$uItem->latitude,
                    'longitude' => (float)$uItem->longitude,
                    'customer_code_prc' => $uItem->customer_code_prc,
                    'customer_name' => $uItem->customer_name,
                    'customer_address' => $uItem->customer_address,
                    'distributor_code' => $uItem->distributor_code,
                    'kecamatan' => $uItem->kecamatan,
                    'kelurahan' => $uItem->kelurahan,
                    'pilar' => $uItem->pilar,
                    'routing_order' => 0
                ];
            }
            $this->unclusteredStores = $unclustered;
        }

        $allStoresForMap = array_merge($stores, $this->unclusteredStores);
        $this->dispatch('management-clusters-generated', stores: $allStoresForMap);
    }

    // --- Action 1: Add Cluster to JKS Team Elite ---
    public function openJksModal($clusterId)
    {
        $this->jksClusterId = $clusterId;
        $this->jksTanggal = date('Y-m-d');
        $this->isJksModalOpen = true;
    }

    public function closeJksModal()
    {
        $this->isJksModalOpen = false;
        $this->jksClusterId = null;
    }

    public function saveClusterToJks()
    {
        $this->validate([
            'jksTanggal' => 'required|date',
        ]);

        if (!$this->jksClusterId || empty($this->managementSelectedTeam)) {
            return;
        }

        $cluster = MasterCluster::find($this->jksClusterId);
        if (!$cluster) return;

        $teamInfo = DB::table('fsalesman')->where('SLSNO', $this->managementSelectedTeam)->first();
        $teamName = $teamInfo ? $teamInfo->SLSNAME : $this->managementSelectedTeam;

        $items = DB::table('master_cluster_items as mci')
            ->join('list_toko_pareto_team_elite as pareto', 'mci.store_id', '=', 'pareto.id')
            ->leftJoin('master_distributors as md', 'pareto.distributor_code', '=', 'md.distributor_code')
            ->where('mci.master_cluster_id', $this->jksClusterId)
            ->select('pareto.*', 'md.distributor_name', 'md.region_code', 'md.region_name', 'md.area_code', 'md.area_name')
            ->get();

        $insertedCount = 0;
        foreach ($items as $item) {
            $exists = DB::table('jks_team_elite')
                ->where('custno', $item->customer_code_prc)
                ->where('tanggal', $this->jksTanggal)
                ->where('kode_team', $this->managementSelectedTeam)
                ->exists();

            if (!$exists) {
                DB::table('jks_team_elite')->insert([
                    'tanggal' => $this->jksTanggal,
                    'kode_team' => $this->managementSelectedTeam,
                    'nama_team' => $teamName,
                    'kode_region' => $item->region_code ?? null,
                    'nama_region' => $item->region_name ?? null,
                    'kode_area' => $item->area_code ?? null,
                    'nama_area' => $item->area_name ?? null,
                    'distributor_code' => $item->distributor_code,
                    'distributor_name' => $item->distributor_name ?? null,
                    'custno' => $item->customer_code_prc,
                    'custname' => $item->customer_name,
                    'addres' => $item->customer_address ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertedCount++;
            }
        }

        session()->flash('message', "{$insertedCount} toko dari Cluster berhasil ditambahkan ke JKS Team Elite tanggal {$this->jksTanggal}!");
        $this->closeJksModal();
    }

    // --- Action 2: Join / Merge Cluster ---
    public function openMergeModal($clusterId)
    {
        $this->sourceClusterId = $clusterId;
        $this->targetClusterId = null;
        $this->isMergeModalOpen = true;
    }

    public function closeMergeModal()
    {
        $this->isMergeModalOpen = false;
        $this->sourceClusterId = null;
        $this->targetClusterId = null;
    }

    public function mergeCluster()
    {
        if (!$this->sourceClusterId || !$this->targetClusterId || $this->sourceClusterId == $this->targetClusterId) {
            return;
        }

        MasterClusterItem::where('master_cluster_id', $this->sourceClusterId)
            ->update(['master_cluster_id' => $this->targetClusterId]);

        $targetCluster = MasterCluster::find($this->targetClusterId);
        if ($targetCluster) {
            $targetCluster->items_count = MasterClusterItem::where('master_cluster_id', $this->targetClusterId)->count();
            $targetCluster->save();
        }

        MasterCluster::destroy($this->sourceClusterId);

        session()->flash('message', "Cluster berhasil digabungkan!");
        $this->closeMergeModal();
        $this->loadManagementClusters();
    }

    // --- Action 3: Move Single Store to Another Cluster / Assign Unclustered ---
    public function openMoveStoreModal($itemId, $storeName)
    {
        $this->movingItemId = $itemId;
        $this->movingUnclusteredStoreId = null;
        $this->movingStoreName = $storeName;
        $this->targetClusterForStore = null;
        $this->isMoveStoreModalOpen = true;
    }

    public function openAssignUnclusteredModal($storeId, $storeName)
    {
        $this->movingItemId = null;
        $this->movingUnclusteredStoreId = $storeId;
        $this->movingStoreName = $storeName;
        $this->targetClusterForStore = null;
        $this->isMoveStoreModalOpen = true;
    }

    public function closeMoveStoreModal()
    {
        $this->isMoveStoreModalOpen = false;
        $this->movingItemId = null;
        $this->movingUnclusteredStoreId = null;
        $this->movingStoreName = '';
        $this->targetClusterForStore = null;
    }

    public function moveStoreToCluster()
    {
        if (!$this->targetClusterForStore) {
            return;
        }

        if ($this->movingItemId) {
            // Move existing clustered store
            $item = MasterClusterItem::find($this->movingItemId);
            if (!$item) return;

            $oldClusterId = $item->master_cluster_id;
            $item->master_cluster_id = $this->targetClusterForStore;
            $item->save();

            // Update target cluster count
            $targetCluster = MasterCluster::find($this->targetClusterForStore);
            if ($targetCluster) {
                $targetCluster->items_count = MasterClusterItem::where('master_cluster_id', $this->targetClusterForStore)->count();
                $targetCluster->save();
            }

            // Update old cluster count (delete old cluster if 0)
            $oldCluster = MasterCluster::find($oldClusterId);
            if ($oldCluster) {
                $oldCount = MasterClusterItem::where('master_cluster_id', $oldClusterId)->count();
                if ($oldCount === 0) {
                    $oldCluster->delete();
                } else {
                    $oldCluster->items_count = $oldCount;
                    $oldCluster->save();
                }
            }

            session()->flash('message', "Toko {$this->movingStoreName} berhasil dipindahkan!");
        } elseif ($this->movingUnclusteredStoreId) {
            // Assign unclustered store to target cluster
            $maxOrder = MasterClusterItem::where('master_cluster_id', $this->targetClusterForStore)->max('routing_order') ?? 0;
            MasterClusterItem::create([
                'master_cluster_id' => $this->targetClusterForStore,
                'store_id' => $this->movingUnclusteredStoreId,
                'routing_order' => $maxOrder + 1,
            ]);

            $targetCluster = MasterCluster::find($this->targetClusterForStore);
            if ($targetCluster) {
                $targetCluster->items_count = MasterClusterItem::where('master_cluster_id', $this->targetClusterForStore)->count();
                $targetCluster->save();
            }

            session()->flash('message', "Toko {$this->movingStoreName} berhasil dimasukkan ke Cluster!");
        }

        $this->closeMoveStoreModal();
        $this->loadManagementClusters();
    }

    public function createClusterFromUnclustered($storeId)
    {
        $store = DB::table('list_toko_pareto_team_elite')->where('id', $storeId)->first();
        if (!$store || empty($this->managementSelectedTeam)) return;

        $clusterCount = MasterCluster::where('team_sales', $this->managementSelectedTeam)->count();
        $newCluster = MasterCluster::create([
            'name' => 'Cluster ' . ($clusterCount + 1),
            'team_sales' => $this->managementSelectedTeam,
            'distributor_code' => $store->distributor_code,
            'center_store_id' => $store->id,
            'items_count' => 1,
        ]);

        MasterClusterItem::create([
            'master_cluster_id' => $newCluster->id,
            'store_id' => $store->id,
            'routing_order' => 1,
        ]);

        session()->flash('message', "Cluster baru berhasil dibuat untuk toko {$store->customer_name}!");
        $this->loadManagementClusters();
    }

    public function deleteCluster($id)
    {
        $cluster = MasterCluster::find($id);
        if ($cluster) {
            MasterClusterItem::where('master_cluster_id', $id)->delete();
            $cluster->delete();
            session()->flash('message', 'Cluster berhasil dihapus!');
            $this->loadManagementClusters();
        }
    }

    public function deleteAllClusters()
    {
        if (empty($this->managementSelectedTeam)) {
            return;
        }

        $clusters = MasterCluster::where('team_sales', $this->managementSelectedTeam);
        if (!empty($this->managementSelectedDistributor)) {
            $clusters->where('distributor_code', $this->managementSelectedDistributor);
        }
        $clusterList = $clusters->get();

        if ($clusterList->isNotEmpty()) {
            $clusterIds = $clusterList->pluck('id')->toArray();
            MasterClusterItem::whereIn('master_cluster_id', $clusterIds)->delete();
            MasterCluster::whereIn('id', $clusterIds)->delete();

            session()->flash('message', 'Semua cluster untuk tim / distributor ini berhasil dihapus!');
            $this->loadManagementClusters();
        }
    }

    public function removeStoreFromCluster($itemId)
    {
        $item = MasterClusterItem::find($itemId);
        if ($item) {
            $clusterId = $item->master_cluster_id;
            $item->delete();
            
            $cluster = MasterCluster::find($clusterId);
            if ($cluster) {
                $oldCount = MasterClusterItem::where('master_cluster_id', $clusterId)->count();
                if ($oldCount === 0) {
                    $cluster->delete();
                } else {
                    $cluster->items_count = $oldCount;
                    $cluster->save();
                }
            }
            
            session()->flash('message', 'Toko berhasil dihapus dari cluster!');
            $this->loadManagementClusters();
        }
    }

    public function render()
    {
        return view('livewire.call-plan.cluster-management.management-tab');
    }
}
