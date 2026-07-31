<?php

namespace App\Livewire\CallPlan\ClusterManagement;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Traits\EnforcesMenuPermissions;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class Index extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'call-plan.cluster-management'; 

    #[Url(as: 'tab')]
    public $activeTab = 'management';

    public $managementSelectedTeam = '';
    public $managementSelectedDistributor = '';
    public $managementTeams = [];
    public $managementDistributors = [];

    public function mount()
    {
        if (!in_array($this->activeTab, ['management', 'clustering'])) {
            $this->activeTab = 'management';
        }
        $this->loadManagementTeams();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedManagementSelectedTeam()
    {
        $this->managementSelectedDistributor = '';
        $this->loadManagementDistributors();
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

    public function applyManagementFilter()
    {
        $this->dispatch('apply-management-filter', [
            'team' => $this->managementSelectedTeam, 
            'distributor' => $this->managementSelectedDistributor
        ]);
    }

    public function render()
    {
        return view('livewire.call-plan.cluster-management.index');
    }
}
