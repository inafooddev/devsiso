<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class SellingOverviewNew extends Component
{
    public function render()
    {
        return view('livewire.dashboard.selling-overview-new')
               ->layout('layouts.app');
    }
}
