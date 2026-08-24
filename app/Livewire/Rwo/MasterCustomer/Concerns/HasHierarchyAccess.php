<?php

namespace App\Livewire\Rwo\MasterCustomer\Concerns;

use App\Models\MasterRegion;
use App\Models\MasterArea;
use App\Models\MasterBranch;
use App\Models\MasterDistributor;
use App\Models\MasterSupervisor;

trait HasHierarchyAccess
{
    public $filter_region_code = '';
    public $filter_area_code = '';
    public $filter_branch_name = '';

    public $temp_filter_region_code = '';
    public $temp_filter_area_code = '';
    public $temp_filter_branch_name = '';

    public function updatedFilterRegionCode()
    {
        $this->filter_area_code = '';
        $this->filter_branch_name = '';
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatedFilterAreaCode()
    {
        $this->filter_branch_name = '';
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatedFilterBranchName()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Terapkan pembatasan akses wilayah berdasarkan level user pada query builder RewardOutlet
     */
    protected function applyHierarchyAccess($query)
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $query;
        }

        if (!empty($user->supervisor_code)) {
            return $query->whereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md')
                    ->whereColumn('md.branch_name', 'reward_outlet.branch_name')
                    ->where('md.supervisor_code', $user->supervisor_code);
            });
        }

        if (!empty($user->area_code) && count((array) $user->area_code) > 0) {
            return $query->whereExists(function ($sub) use ($user) {
                $sub->selectRaw('1')
                    ->from('master_distributors as md')
                    ->whereColumn('md.branch_name', 'reward_outlet.branch_name')
                    ->whereIn('md.area_code', (array) $user->area_code);
            });
        }

        if (!empty($user->region_code) && count((array) $user->region_code) > 0) {
            return $query->whereIn('region_code', (array) $user->region_code);
        }

        return $query;
    }

    /**
     * Get list of regions for dropdown filter
     */
    public function getFilterRegions()
    {
        $user = auth()->user();

        if (!$user->hasRole('admin') && !empty($user->supervisor_code)) {
            $regionCodes = MasterDistributor::where('supervisor_code', $user->supervisor_code)
                ->whereNotNull('region_code')
                ->pluck('region_code')
                ->unique();
            return MasterRegion::whereIn('region_code', $regionCodes)->orderBy('region_name')->get();
        }

        if (!$user->hasRole('admin') && !empty($user->area_code) && count((array) $user->area_code) > 0) {
            $regionCodes = MasterDistributor::whereIn('area_code', (array) $user->area_code)
                ->whereNotNull('region_code')
                ->pluck('region_code')
                ->unique();
            return MasterRegion::whereIn('region_code', $regionCodes)->orderBy('region_name')->get();
        }

        $query = MasterRegion::query();
        if (!$user->hasRole('admin') && !empty($user->region_code) && count((array) $user->region_code) > 0) {
            $query->whereIn('region_code', (array) $user->region_code);
        }
        return $query->orderBy('region_name')->get();
    }

    /**
     * Get list of areas for dropdown filter
     */
    public function getFilterAreas()
    {
        $user = auth()->user();
        $query = MasterArea::query();
        
        $regionCode = property_exists($this, 'temp_filter_region_code') && !empty($this->temp_filter_region_code) 
            ? $this->temp_filter_region_code 
            : $this->filter_region_code;

        if (!empty($regionCode)) {
            $query->where('region_code', $regionCode);
        } elseif (!$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $areaCodes = MasterDistributor::where('supervisor_code', $user->supervisor_code)
                    ->whereNotNull('area_code')
                    ->pluck('area_code')
                    ->unique();
                $query->whereIn('area_code', $areaCodes);
            } elseif (!empty($user->area_code) && count((array) $user->area_code) > 0) {
                $query->whereIn('area_code', (array) $user->area_code);
            } elseif (!empty($user->region_code) && count((array) $user->region_code) > 0) {
                $query->whereIn('region_code', (array) $user->region_code);
            }
        }

        return $query->orderBy('area_name')->get();
    }

    /**
     * Get list of branches for dropdown filter
     */
    public function getFilterBranches()
    {
        $user = auth()->user();
        $query = MasterBranch::query();

        if (!$user->hasRole('admin')) {
            if (!empty($user->supervisor_code)) {
                $branchNames = MasterDistributor::where('supervisor_code', $user->supervisor_code)
                    ->whereNotNull('branch_name')
                    ->pluck('branch_name')
                    ->unique();
                $query->whereIn('branch_name', $branchNames);
            } elseif (!empty($user->area_code) && count((array) $user->area_code) > 0) {
                $query->whereHas('supervisor', function ($q) use ($user) {
                    $q->whereIn('area_code', (array) $user->area_code);
                });
            } elseif (!empty($user->region_code) && count((array) $user->region_code) > 0) {
                $query->whereHas('supervisor.area', function ($q) use ($user) {
                    $q->whereIn('region_code', (array) $user->region_code);
                });
            }
        }

        $areaCode = property_exists($this, 'temp_filter_area_code') && !empty($this->temp_filter_area_code) 
            ? $this->temp_filter_area_code 
            : $this->filter_area_code;
            
        $regionCode = property_exists($this, 'temp_filter_region_code') && !empty($this->temp_filter_region_code) 
            ? $this->temp_filter_region_code 
            : $this->filter_region_code;

        if (!empty($areaCode)) {
            $query->whereHas('supervisor', function ($q) use ($areaCode) {
                $q->where('area_code', $areaCode);
            });
        } elseif (!empty($regionCode)) {
            $query->whereHas('supervisor.area', function ($q) use ($regionCode) {
                $q->where('region_code', $regionCode);
            });
        }

        return $query->orderBy('branch_name')->get();
    }
}
