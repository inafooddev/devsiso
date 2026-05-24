<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;

class ActivityLogManagement extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::when($this->search, function($q) {
            $q->where('user_name', 'like', '%' . $this->search . '%')
              ->orWhere('action', 'like', '%' . $this->search . '%')
              ->orWhere('description', 'like', '%' . $this->search . '%')
              ->orWhere('user_id', 'like', '%' . $this->search . '%');
        })->latest()->paginate(20);

        return view('livewire.activity-log-management', compact('logs'))->layout('layouts.app');
    }
}
