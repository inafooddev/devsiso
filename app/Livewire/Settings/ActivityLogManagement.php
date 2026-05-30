<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;

class ActivityLogManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $actionFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'actionFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'dateFrom', 'dateTo', 'actionFilter']);
        $this->resetPage();
    }

    /**
     * Get the filtered query for activity logs.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredLogsQuery()
    {
        return ActivityLog::query()
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('user_name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('action', 'ilike', '%' . $this->search . '%')
                      ->orWhere('description', 'ilike', '%' . $this->search . '%')
                      ->orWhere('user_id', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->actionFilter, function($q) {
                $q->where('action', $this->actionFilter);
            })
            ->when($this->dateFrom, function($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            });
    }

    /**
     * Export the filtered logs to an Excel file.
     */
    public function export()
    {
        $filters = [
            'search' => $this->search,
            'actionFilter' => $this->actionFilter,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ActivityLogExport($filters),
            'activity_logs_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function render()
    {
        $logs = $this->getFilteredLogsQuery()->latest()->paginate(20);

        // Fetch unique actions to populate the filter dropdown
        $actions = ActivityLog::select('action')
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.settings.activity-log-management', compact('logs', 'actions'))
            ->layout('layouts.app');
    }
}
