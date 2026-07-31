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
    
    protected array $managementClusterStores = [];
    protected array $unclusteredStores = [];
    protected array $clusterSummary = [];
    
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

    // Action 4: Modern Confirm Delete Modals State
    public $isConfirmDeleteStoreOpen = false;
    public $deletingItemId = null;

    public $isConfirmDeleteClusterOpen = false;
    public $deletingClusterId = null;

    public $isConfirmDeleteAllClustersOpen = false;

    // Action 5: Bulk Operations State
    public $selectedStoreIds = [];
    public $isBulkMoveModalOpen = false;
    public $isConfirmBulkDeleteOpen = false;
    public $bulkTargetClusterId = null;

    protected $listeners = ['apply-management-filter' => 'handleFilterApplied'];

    public function mount()
    {
        $this->isMergeModalOpen = false;
        $this->isMoveStoreModalOpen = false;
        $this->isConfirmDeleteStoreOpen = false;
        $this->isConfirmDeleteClusterOpen = false;
        $this->isConfirmDeleteAllClustersOpen = false;
        $this->isBulkMoveModalOpen = false;
        $this->isConfirmBulkDeleteOpen = false;
        $this->selectedStoreIds = [];
        $this->bulkTargetClusterId = null;
        $this->deletingItemId = null;
        $this->deletingClusterId = null;
        $this->movingItemId = null;
        $this->movingUnclusteredStoreId = null;

        $this->loadManagementTeams();
    }

    public function handleFilterApplied($params)
    {
        $this->managementSelectedTeam = $params['team'] ?? '';
        $this->managementSelectedDistributor = $params['distributor'] ?? '';
        $this->loadManagementClusters(isInitialFilter: true);
    }

    public function updatedManagementSelectedTeam()
    {
        $this->managementSelectedDistributor = '';
        $this->loadManagementDistributors();
    }

    public function applyFilter()
    {
        $this->loadManagementClusters(isInitialFilter: true);
    }

    public function openConfirmDeleteStoreModal($itemId)
    {
        $this->deletingItemId = $itemId;
        $this->isConfirmDeleteStoreOpen = true;
    }

    public function closeConfirmDeleteStoreModal()
    {
        $this->isConfirmDeleteStoreOpen = false;
        $this->deletingItemId = null;
    }

    public function openConfirmDeleteAllClustersModal()
    {
        $this->isConfirmDeleteAllClustersOpen = true;
    }

    public function closeConfirmDeleteAllClustersModal()
    {
        $this->isConfirmDeleteAllClustersOpen = false;
    }

    public function openConfirmDeleteClusterModal($clusterId)
    {
        $this->deletingClusterId = $clusterId;
        $this->isConfirmDeleteClusterOpen = true;
    }

    public function closeConfirmDeleteClusterModal()
    {
        $this->isConfirmDeleteClusterOpen = false;
        $this->deletingClusterId = null;
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

    public function loadManagementClusters($isInitialFilter = false)
    {
        $this->loadManagementTeams();
        $this->loadManagementDistributors();
        $this->fetchManagementStores();

        $allStoresForMap = array_merge($this->managementClusterStores, $this->unclusteredStores);
        $this->dispatch('management-clusters-generated', stores: $allStoresForMap, isInitialFilter: $isInitialFilter);
    }

    protected function fetchManagementStores()
    {
        $this->managementClusterStores = [];
        $this->unclusteredStores = [];
        $this->clusterSummary = [];
        $this->paretoTotalStores = 0;
        $this->paretoPilarCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];

        if (empty($this->managementSelectedTeam)) {
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
            // Optimized single-scan SQL aggregation for Pareto total and pilar counts
            $stats = DB::table('list_toko_pareto_team_elite')
                ->whereIn('distributor_code', $allDistCodes)
                ->selectRaw("
                    COUNT(*) as total,
                    COUNT(CASE WHEN CAST(pilar AS text) LIKE '1.%' THEN 1 END) as p1,
                    COUNT(CASE WHEN CAST(pilar AS text) LIKE '2.%' THEN 1 END) as p2,
                    COUNT(CASE WHEN CAST(pilar AS text) LIKE '3.%' THEN 1 END) as p3,
                    COUNT(CASE WHEN CAST(pilar AS text) LIKE '4.%' THEN 1 END) as p4
                ")
                ->first();

            $this->paretoTotalStores = (int) ($stats->total ?? 0);
            $this->paretoPilarCounts = [
                '1' => (int) ($stats->p1 ?? 0),
                '2' => (int) ($stats->p2 ?? 0),
                '3' => (int) ($stats->p3 ?? 0),
                '4' => (int) ($stats->p4 ?? 0),
            ];
        }

        $clusterQuery = MasterCluster::where('team_sales', $this->managementSelectedTeam);
        if (!empty($this->managementSelectedDistributor)) {
            $clusterQuery->where('distributor_code', $this->managementSelectedDistributor);
        }
        $clusters = $clusterQuery->get();
        $clusterIds = $clusters->pluck('id')->toArray();

        $stores = [];
        $clusterSummary = [];

        if (!empty($clusterIds)) {
            $items = DB::table('master_cluster_items as mci')
                ->join('list_toko_pareto_team_elite as pareto', 'mci.store_id', '=', 'pareto.id')
                ->leftJoin('master_distributors as md', 'pareto.distributor_code', '=', 'md.distributor_code')
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
                    'md.distributor_name',
                    'pareto.latitude',
                    'pareto.longitude',
                    'pareto.pilar',
                    'pareto.target',
                    'pareto.keterangan',
                    'pareto.kecamatan as kecamatan',
                    'pareto.desa as kelurahan'
                )
                ->orderBy('mci.master_cluster_id')
                ->orderBy('mci.routing_order')
                ->get();

            // First pass: Build mSummary & clusterSeqMap based on clusters that actually exist in items
            $rawSummary = [];
            foreach ($items as $item) {
                $cId = $item->master_cluster_id;
                if (!isset($rawSummary[$cId])) {
                    $rawSummary[$cId] = [
                        'count' => 0,
                        'stores' => [],
                        'kecamatan' => [],
                        'pilar' => ['1' => 0, '2' => 0, '3' => 0, '4' => 0]
                    ];
                }
                $rawSummary[$cId]['count']++;
                if (!empty($item->kecamatan)) {
                    $rawSummary[$cId]['kecamatan'][] = $item->kecamatan;
                }
                $pilarRaw = (string)($item->pilar ?? '');
                if (str_contains($pilarRaw, '1.')) $rawSummary[$cId]['pilar']['1']++;
                elseif (str_contains($pilarRaw, '2.')) $rawSummary[$cId]['pilar']['2']++;
                elseif (str_contains($pilarRaw, '3.')) $rawSummary[$cId]['pilar']['3']++;
                elseif (str_contains($pilarRaw, '4.')) $rawSummary[$cId]['pilar']['4']++;
            }

            $seq = 1;
            $clusterSeqMap = [];
            foreach ($rawSummary as $cId => &$data) {
                $clusterSeqMap[$cId] = $seq;
                $uniqueKec = array_values(array_unique($data['kecamatan']));
                $totalKec = count($uniqueKec);
                $kecStr = '';
                if ($totalKec > 2) {
                    $kecStr = ' (' . $uniqueKec[0] . ', ' . $uniqueKec[1] . ' +' . ($totalKec - 2) . ' Kec)';
                } elseif ($totalKec > 0) {
                    $kecStr = ' (' . implode(', ', $uniqueKec) . ')';
                }

                $data['seq'] = $seq++;
                $data['kec_str'] = $kecStr;
                $data['kec_str_full'] = $totalKec > 0 ? ' (' . implode(', ', $uniqueKec) . ')' : '';
            }
            unset($data);

            foreach ($items as $item) {
                $st = [
                    'id' => $item->store_id,
                    'item_id' => $item->item_id,
                    'cluster_id' => $item->master_cluster_id,
                    'cluster_seq' => $clusterSeqMap[$item->master_cluster_id] ?? $item->master_cluster_id,
                    'latitude' => (float)$item->latitude,
                    'longitude' => (float)$item->longitude,
                    'customer_code_prc' => $item->customer_code_prc,
                    'customer_name' => $item->customer_name,
                    'customer_address' => $item->customer_address,
                    'distributor_code' => $item->distributor_code,
                    'distributor_name' => $item->distributor_name ?? $item->distributor_code,
                    'kecamatan' => $item->kecamatan,
                    'kelurahan' => $item->kelurahan,
                    'pilar' => $item->pilar,
                    'target' => $item->target ?? null,
                    'keterangan' => $item->keterangan ?? null,
                    'routing_order' => $item->routing_order
                ];
                $stores[] = $st;
                $rawSummary[$item->master_cluster_id]['stores'][] = $st;
            }

            $clusterSummary = $rawSummary;
        }

        $this->managementClusterStores = $stores;
        $this->clusterSummary = $clusterSummary;

        // Fetch Unclustered Pareto stores for this team/distributor using high performance WHERE NOT EXISTS
        if (!empty($allDistCodes)) {
            $unclusteredItems = DB::table('list_toko_pareto_team_elite as pareto')
                ->leftJoin('master_distributors as md', 'pareto.distributor_code', '=', 'md.distributor_code')
                ->whereIn('pareto.distributor_code', $allDistCodes)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('master_cluster_items as mci')
                          ->whereColumn('mci.store_id', 'pareto.id');
                })
                ->select(
                    'pareto.id',
                    'pareto.customer_code_prc',
                    'pareto.customer_name',
                    'pareto.customer_address',
                    'pareto.distributor_code',
                    'md.distributor_name',
                    'pareto.latitude',
                    'pareto.longitude',
                    'pareto.pilar',
                    'pareto.target',
                    'pareto.keterangan',
                    'pareto.kecamatan as kecamatan',
                    'pareto.desa as kelurahan'
                )
                ->orderBy('pareto.customer_name')
                ->get();

            $unclustered = [];
            foreach ($unclusteredItems as $uItem) {
                $unclustered[] = [
                    'id' => $uItem->id,
                    'item_id' => null,
                    'cluster_id' => 0,
                    'latitude' => (float)$uItem->latitude,
                    'longitude' => (float)$uItem->longitude,
                    'customer_code_prc' => $uItem->customer_code_prc,
                    'customer_name' => $uItem->customer_name,
                    'customer_address' => $uItem->customer_address,
                    'distributor_code' => $uItem->distributor_code,
                    'distributor_name' => $uItem->distributor_name ?? $uItem->distributor_code,
                    'kecamatan' => $uItem->kecamatan,
                    'kelurahan' => $uItem->kelurahan,
                    'pilar' => $uItem->pilar,
                    'target' => $uItem->target ?? null,
                    'keterangan' => $uItem->keterangan ?? null,
                    'routing_order' => 0
                ];
            }
            $this->unclusteredStores = $unclustered;
        }
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

        $msg = "{$insertedCount} toko dari Cluster berhasil ditambahkan ke JKS Team Elite tanggal {$this->jksTanggal}!";
        session()->flash('message', $msg);
        $this->dispatch('notify', type: 'success', message: $msg);
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

        $msg = "Cluster berhasil digabungkan!";
        session()->flash('message', $msg);
        $this->dispatch('notify', type: 'success', message: $msg);
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

            $msg = "Toko {$this->movingStoreName} berhasil dipindahkan!";
            session()->flash('message', $msg);
            $this->dispatch('notify', type: 'success', message: $msg);
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

            $msg = "Toko {$this->movingStoreName} berhasil dimasukkan ke Cluster!";
            session()->flash('message', $msg);
            $this->dispatch('notify', type: 'success', message: $msg);
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

        $msg = "Cluster baru berhasil dibuat untuk toko {$store->customer_name}!";
        session()->flash('message', $msg);
        $this->dispatch('notify', type: 'success', message: $msg);
        $this->loadManagementClusters();
    }

    public function deleteCluster($id = null)
    {
        $targetId = $id ?? $this->deletingClusterId;
        if ($targetId) {
            $cluster = MasterCluster::find($targetId);
            if ($cluster) {
                MasterClusterItem::where('master_cluster_id', $targetId)->delete();
                $cluster->delete();
                $msg = 'Cluster berhasil dihapus!';
                session()->flash('message', $msg);
                $this->dispatch('notify', type: 'success', message: $msg);
                $this->closeConfirmDeleteClusterModal();
                $this->loadManagementClusters();
            }
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

            $msg = 'Semua cluster untuk tim / distributor ini berhasil dihapus!';
            session()->flash('message', $msg);
            $this->closeConfirmDeleteAllClustersModal();
            return $this->redirect(request()->header('Referer'));
        }
    }

    public function removeStoreFromCluster($itemId = null)
    {
        $targetId = $itemId ?? $this->deletingItemId;
        if ($targetId) {
            $item = MasterClusterItem::find($targetId);
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
                
                $msg = 'Toko berhasil dihapus dari cluster!';
                session()->flash('message', $msg);
                $this->dispatch('notify', type: 'success', message: $msg);
                $this->closeConfirmDeleteStoreModal();
                $this->loadManagementClusters();
            }
        }
    }

    public function openAddUnclusteredStoreModal($storeId, $storeName)
    {
        $this->openAssignUnclusteredModal($storeId, $storeName);
    }

    // --- Bulk Action Methods ---
    public function clearSelectedStores()
    {
        $this->selectedStoreIds = [];
    }

    public function toggleSelectClusterStores($clusterId)
    {
        $items = DB::table('master_cluster_items')
            ->where('master_cluster_id', $clusterId)
            ->pluck('id')
            ->toArray();

        if (empty($items)) return;

        $itemKeys = array_map(fn($id) => 'item-' . $id, $items);
        $intersect = array_intersect($itemKeys, $this->selectedStoreIds);

        if (count($intersect) === count($itemKeys)) {
            // Deselect all from this cluster
            $this->selectedStoreIds = array_values(array_diff($this->selectedStoreIds, $itemKeys));
        } else {
            // Select all from this cluster
            $this->selectedStoreIds = array_values(array_unique(array_merge($this->selectedStoreIds, $itemKeys)));
        }
    }

    public function toggleSelectUnclusteredStores()
    {
        if (empty($this->unclusteredStores)) return;

        $unclusteredIds = array_column($this->unclusteredStores, 'id');
        $storeKeys = array_map(fn($id) => 'store-' . $id, $unclusteredIds);
        $intersect = array_intersect($storeKeys, $this->selectedStoreIds);

        if (count($intersect) === count($storeKeys)) {
            // Deselect all unclustered
            $this->selectedStoreIds = array_values(array_diff($this->selectedStoreIds, $storeKeys));
        } else {
            // Select all unclustered
            $this->selectedStoreIds = array_values(array_unique(array_merge($this->selectedStoreIds, $storeKeys)));
        }
    }

    public function openBulkMoveModal()
    {
        if (empty($this->selectedStoreIds)) return;
        $this->bulkTargetClusterId = null;
        $this->isBulkMoveModalOpen = true;
    }

    public function closeBulkMoveModal()
    {
        $this->isBulkMoveModalOpen = false;
        $this->bulkTargetClusterId = null;
    }

    public function bulkMoveStores()
    {
        if (empty($this->selectedStoreIds) || !$this->bulkTargetClusterId) {
            return;
        }

        $targetCluster = MasterCluster::find($this->bulkTargetClusterId);
        if (!$targetCluster) return;

        $movedCount = 0;
        $affectedOldClusterIds = [];

        $maxOrder = MasterClusterItem::where('master_cluster_id', $this->bulkTargetClusterId)->max('routing_order') ?? 0;

        foreach ($this->selectedStoreIds as $val) {
            if (str_starts_with($val, 'item-')) {
                $itemId = (int) str_replace('item-', '', $val);
                $item = MasterClusterItem::find($itemId);
                if ($item && $item->master_cluster_id != $this->bulkTargetClusterId) {
                    $affectedOldClusterIds[] = $item->master_cluster_id;
                    $item->master_cluster_id = $this->bulkTargetClusterId;
                    $item->save();
                    $movedCount++;
                }
            } elseif (str_starts_with($val, 'store-')) {
                $storeId = (int) str_replace('store-', '', $val);
                // Check if already in target cluster
                $exists = MasterClusterItem::where('master_cluster_id', $this->bulkTargetClusterId)
                    ->where('store_id', $storeId)
                    ->exists();

                if (!$exists) {
                    $maxOrder++;
                    MasterClusterItem::create([
                        'master_cluster_id' => $this->bulkTargetClusterId,
                        'store_id' => $storeId,
                        'routing_order' => $maxOrder,
                    ]);
                    $movedCount++;
                }
            }
        }

        // Recalculate target cluster items count
        $targetCluster->items_count = MasterClusterItem::where('master_cluster_id', $this->bulkTargetClusterId)->count();
        $targetCluster->save();

        // Recalculate and clean up old clusters
        $affectedOldClusterIds = array_unique($affectedOldClusterIds);
        foreach ($affectedOldClusterIds as $oldId) {
            $oldCluster = MasterCluster::find($oldId);
            if ($oldCluster) {
                $oldCount = MasterClusterItem::where('master_cluster_id', $oldId)->count();
                if ($oldCount === 0) {
                    $oldCluster->delete();
                } else {
                    $oldCluster->items_count = $oldCount;
                    $oldCluster->save();
                }
            }
        }

        $this->selectedStoreIds = [];
        $this->closeBulkMoveModal();

        $msg = "{$movedCount} toko berhasil dipindahkan ke Cluster tujuan!";
        session()->flash('message', $msg);
        $this->dispatch('notify', type: 'success', message: $msg);
        $this->loadManagementClusters();
    }

    public function openConfirmBulkDeleteModal()
    {
        if (empty($this->selectedStoreIds)) return;
        $this->isConfirmBulkDeleteOpen = true;
    }

    public function closeConfirmBulkDeleteModal()
    {
        $this->isConfirmBulkDeleteOpen = false;
    }

    public function bulkDeleteStores()
    {
        if (empty($this->selectedStoreIds)) return;

        $deletedCount = 0;
        $affectedClusterIds = [];

        foreach ($this->selectedStoreIds as $val) {
            if (str_starts_with($val, 'item-')) {
                $itemId = (int) str_replace('item-', '', $val);
                $item = MasterClusterItem::find($itemId);
                if ($item) {
                    $affectedClusterIds[] = $item->master_cluster_id;
                    $item->delete();
                    $deletedCount++;
                }
            }
        }

        // Recalculate and clean up affected clusters
        $affectedClusterIds = array_unique($affectedClusterIds);
        foreach ($affectedClusterIds as $cId) {
            $cluster = MasterCluster::find($cId);
            if ($cluster) {
                $count = MasterClusterItem::where('master_cluster_id', $cId)->count();
                if ($count === 0) {
                    $cluster->delete();
                } else {
                    $cluster->items_count = $count;
                    $cluster->save();
                }
            }
        }

        $this->selectedStoreIds = [];
        $this->closeConfirmBulkDeleteModal();

        $msg = "{$deletedCount} toko berhasil dikeluarkan dari Cluster!";
        session()->flash('message', $msg);
        $this->dispatch('notify', type: 'success', message: $msg);
        $this->loadManagementClusters();
    }

    public function render()
    {
        if (!empty($this->managementSelectedTeam) && empty($this->managementClusterStores) && empty($this->unclusteredStores)) {
            $this->fetchManagementStores();
        }

        return view('livewire.call-plan.cluster-management.management-tab', [
            'managementClusterStores' => $this->managementClusterStores,
            'unclusteredStores' => $this->unclusteredStores,
            'clusterSummary' => $this->clusterSummary,
        ]);
    }
}
