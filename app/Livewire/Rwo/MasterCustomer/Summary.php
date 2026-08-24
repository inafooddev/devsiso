<?php

namespace App\Livewire\Rwo\MasterCustomer;

use Livewire\Component;
use App\Models\RewardOutlet;
use Illuminate\Support\Facades\DB;
use App\Traits\EnforcesMenuPermissions;
use App\Livewire\Rwo\MasterCustomer\Concerns\HasHierarchyAccess;
use App\Livewire\Rwo\MasterCustomer\Queries\SummaryQueryBuilder;

class Summary extends Component
{
    use EnforcesMenuPermissions;
    use HasHierarchyAccess;

    protected string $menuRoute = 'rwo.index'; 

    public $search = '';

    public function getSummaryDataProperty()
    {
        return app(SummaryQueryBuilder::class)->get([
            'search' => $this->search,
            'filter_region_code' => $this->filter_region_code,
            'filter_area_code' => $this->filter_area_code,
        ]);
    }

    public function render()
    {
        return view('livewire.rwo.master-customer.summary', [
            'records' => $this->summaryData
        ])->layout('layouts.app');
    }
}
