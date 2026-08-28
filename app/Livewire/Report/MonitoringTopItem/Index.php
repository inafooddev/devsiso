<?php

namespace App\Livewire\Report\MonitoringTopItem;

use Livewire\Component;

class Index extends Component
{
    public $activeTab = 'summary';

    public function updatingActiveTab($value)
    {
        if ($value === 'jobs' && !auth()->user()->hasRole('admin')) {
            $this->activeTab = 'summary'; // fallback
            return false; // prevent update
        }
    }

    public function render()
    {
        return view('livewire.report.monitoring-top-item.index')->layout('layouts.app');
    }
}
