<?php

namespace App\Livewire\CallPlan\ClusterManagement;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\MasterCluster;
use App\Models\MasterClusterItem;

#[Layout('layouts.app')]
class Index extends Component
{
    public $isViewModalOpen = false;
    public $selectedCluster = null;
    public $selectedClusterItems = [];

    public function viewCluster($id)
    {
        $this->selectedCluster = MasterCluster::find($id);
        if ($this->selectedCluster) {
            $items = MasterClusterItem::where('master_cluster_id', $id)
                ->orderBy('routing_order', 'asc')
                ->get();
            
            $storeIds = $items->pluck('store_id')->toArray();
            
            $stores = \Illuminate\Support\Facades\DB::table('list_toko_pareto_team_elite')
                ->whereIn('id', $storeIds)
                ->get()
                ->keyBy('id');
            
            $formattedItems = [];
            foreach ($items as $item) {
                $store = $stores->get($item->store_id);
                $formattedItems[] = [
                    'route_order' => $item->routing_order,
                    'toko_id' => $store ? $store->customer_code_prc : $item->store_id,
                    'toko_name' => $store ? $store->customer_name : 'Unknown Store',
                    'distance_from_prev_km' => 0, // Belum disimpan di DB
                    'duration_from_prev_min' => 0, // Belum disimpan di DB
                ];
            }
            
            $this->selectedClusterItems = $formattedItems;
            $this->isViewModalOpen = true;
        }
    }

    public function closeViewModal()
    {
        $this->isViewModalOpen = false;
        $this->selectedCluster = null;
        $this->selectedClusterItems = [];
    }

    public function deleteCluster($id)
    {
        $cluster = MasterCluster::find($id);
        if ($cluster) {
            MasterClusterItem::where('master_cluster_id', $id)->delete();
            $cluster->delete();
            session()->flash('message', 'Cluster berhasil dihapus!');
        }
    }

    public function render()
    {
        $clusters = MasterCluster::withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.call-plan.cluster-management.index', [
            'clusters' => $clusters
        ]);
    }
}
