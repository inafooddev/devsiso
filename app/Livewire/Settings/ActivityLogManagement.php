<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ActivityLogManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $actionFilter = '';
    public $activityCategory = 'all'; // 'all', 'mutation', 'menu', 'auth'
    public $sortBy = 'last_access'; // 'last_access', 'anomalies', 'total_activities', 'first_access'
    public $expandedUserId = null; // ID user yang sedang di-expand (Accordion Tunggal)
    public $childSearch = ''; // Pencarian lokal di dalam user yang sedang di-expand
    public $perPage = 15;
    public $selectedLogId = null; // ID log untuk modal detail

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'activityCategory' => ['except' => 'all'],
        'sortBy' => ['except' => 'last_access'],
    ];

    public function mount()
    {
        if (empty($this->dateFrom)) {
            $this->dateFrom = now()->toDateString();
        }
        if (empty($this->dateTo)) {
            $this->dateTo = now()->toDateString();
        }
    }

    public function updatingSearch()
    {
        $this->expandedUserId = null;
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->expandedUserId = null;
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->expandedUserId = null;
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->expandedUserId = null;
        $this->resetPage();
    }

    public function updatingActivityCategory()
    {
        $this->expandedUserId = null;
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->expandedUserId = null;
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->expandedUserId = null;
        $this->resetPage();
    }

    /**
     * Toggle expand/collapse accordion tunggal untuk user tertentu.
     */
    public function toggleUser($userId)
    {
        if ($this->expandedUserId === $userId) {
            $this->expandedUserId = null;
            $this->childSearch = '';
        } else {
            $this->expandedUserId = $userId;
            $this->childSearch = '';
        }
    }

    /**
     * Tampilkan modal detail untuk log tertentu.
     */
    public function showLogDetail($logId)
    {
        $this->selectedLogId = $logId;
    }

    /**
     * Tutup modal detail log.
     */
    public function closeLogDetail()
    {
        $this->selectedLogId = null;
    }

    /**
     * Preset cepat filter rentang tanggal.
     */
    public function setDatePreset($preset)
    {
        $this->expandedUserId = null;
        $this->resetPage();

        switch ($preset) {
            case 'today':
                $this->dateFrom = now()->toDateString();
                $this->dateTo = now()->toDateString();
                break;
            case 'yesterday':
                $this->dateFrom = now()->subDay()->toDateString();
                $this->dateTo = now()->subDay()->toDateString();
                break;
            case 'this_week':
                $this->dateFrom = now()->startOfWeek()->toDateString();
                $this->dateTo = now()->endOfWeek()->toDateString();
                break;
            case 'this_month':
                $this->dateFrom = now()->startOfMonth()->toDateString();
                $this->dateTo = now()->endOfMonth()->toDateString();
                break;
            case 'all':
                $this->dateFrom = '';
                $this->dateTo = '';
                break;
        }
    }

    /**
     * Filter jalan pintas saat kartu KPI diklik (Click-to-Filter).
     */
    public function applyKpiFilter($filterType)
    {
        $this->expandedUserId = null;
        $this->resetPage();

        switch ($filterType) {
            case 'users':
                $this->activityCategory = 'all';
                $this->sortBy = 'last_access';
                break;
            case 'activities':
                $this->activityCategory = 'all';
                $this->sortBy = 'total_activities';
                break;
            case 'mutations':
                $this->activityCategory = 'mutation';
                $this->sortBy = 'total_activities';
                break;
            case 'failed':
                $this->activityCategory = 'auth';
                $this->sortBy = 'anomalies';
                break;
        }
    }

    /**
     * Ganti filter kategori aktivitas (Noise Reduction).
     */
    public function setActivityCategory($category)
    {
        $this->activityCategory = $category;
        $this->expandedUserId = null;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'search', 
            'actionFilter', 
            'activityCategory', 
            'sortBy',
            'expandedUserId', 
            'childSearch',
            'selectedLogId'
        ]);
        $this->dateFrom = now()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->resetPage();
    }

    /**
     * Hitung ringkasan statistik KPI (Active Users, Total Activities, Mutations, Failed).
     */
    public function getKpiStatsProperty()
    {
        $baseQuery = ActivityLog::query()
            ->when($this->dateFrom, fn($q) => $q->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->dateTo, fn($q) => $q->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()));

        $stats = (clone $baseQuery)->selectRaw("
            COUNT(DISTINCT user_id) as total_users,
            COUNT(id) as total_activities,
            COUNT(CASE WHEN action ILIKE '%create%' OR action ILIKE '%update%' OR action ILIKE '%delete%' OR action ILIKE '%import%' OR action ILIKE '%process%' OR action ILIKE '%map%' THEN 1 END) as total_mutations,
            COUNT(CASE WHEN action ILIKE '%failed%' THEN 1 END) as total_failed
        ")->first();

        return [
            'total_users' => $stats->total_users ?? 0,
            'total_activities' => $stats->total_activities ?? 0,
            'total_mutations' => $stats->total_mutations ?? 0,
            'total_failed' => $stats->total_failed ?? 0,
        ];
    }

    /**
     * Query data list user yang memiliki log sesuai kriteria filter dan pengurutan audit.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredUsersQuery()
    {
        $query = ActivityLog::query()
            ->selectRaw("
                user_id,
                MAX(user_name) as user_name,
                MIN(created_at) as first_access,
                MAX(created_at) as last_access,
                COUNT(id) as total_activities,
                COUNT(DISTINCT ip_address) as ip_count,
                COUNT(CASE WHEN action ILIKE '%failed%' THEN 1 END) as failed_count
            ")
            ->when($this->activityCategory && $this->activityCategory !== 'all', function($q) {
                if ($this->activityCategory === 'mutation') {
                    $q->where(function($sub) {
                        $sub->where('action', 'ilike', '%create%')
                            ->orWhere('action', 'ilike', '%update%')
                            ->orWhere('action', 'ilike', '%delete%')
                            ->orWhere('action', 'ilike', '%import%')
                            ->orWhere('action', 'ilike', '%store%')
                            ->orWhere('action', 'ilike', '%edit%')
                            ->orWhere('action', 'ilike', '%hapus%')
                            ->orWhere('action', 'ilike', '%process%')
                            ->orWhere('action', 'ilike', '%map%');
                    });
                } elseif ($this->activityCategory === 'menu') {
                    $q->where('action', 'ilike', '%Access Menu%');
                } elseif ($this->activityCategory === 'auth') {
                    $q->where(function($sub) {
                        $sub->where('action', 'ilike', '%login%')
                            ->orWhere('action', 'ilike', '%logout%')
                            ->orWhere('action', 'ilike', '%auth%');
                    });
                }
            })
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
                $q->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            })
            ->when($this->dateTo, function($q) {
                $q->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            })
            ->groupBy('user_id');

        // Dynamic Sorting based on Audit Priority
        switch ($this->sortBy) {
            case 'anomalies':
                $query->orderByRaw("(COUNT(CASE WHEN action ILIKE '%failed%' THEN 1 END) * 10 + COUNT(DISTINCT ip_address)) DESC")
                      ->orderByDesc('last_access');
                break;
            case 'total_activities':
                $query->orderByDesc('total_activities')->orderByDesc('last_access');
                break;
            case 'first_access':
                $query->orderBy('first_access')->orderByDesc('last_access');
                break;
            case 'last_access':
            default:
                $query->orderByDesc('last_access');
                break;
        }

        return $query;
    }

    /**
     * Ambil rincian log aktivitas untuk user yang sedang di-expand.
     * Mendukung childSearch lokal khusus user tersebut.
     *
     * @param string $userKey
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChildLogs($userKey)
    {
        $query = ActivityLog::query();

        if ($userKey === '__SYSTEM__') {
            $query->whereNull('user_id');
        } else {
            $query->where('user_id', $userKey);
        }

        return $query
            ->when($this->activityCategory && $this->activityCategory !== 'all', function($q) {
                if ($this->activityCategory === 'mutation') {
                    $q->where(function($sub) {
                        $sub->where('action', 'ilike', '%create%')
                            ->orWhere('action', 'ilike', '%update%')
                            ->orWhere('action', 'ilike', '%delete%')
                            ->orWhere('action', 'ilike', '%import%')
                            ->orWhere('action', 'ilike', '%store%')
                            ->orWhere('action', 'ilike', '%edit%')
                            ->orWhere('action', 'ilike', '%hapus%')
                            ->orWhere('action', 'ilike', '%process%')
                            ->orWhere('action', 'ilike', '%map%');
                    });
                } elseif ($this->activityCategory === 'menu') {
                    $q->where('action', 'ilike', '%Access Menu%');
                } elseif ($this->activityCategory === 'auth') {
                    $q->where(function($sub) {
                        $sub->where('action', 'ilike', '%login%')
                            ->orWhere('action', 'ilike', '%logout%')
                            ->orWhere('action', 'ilike', '%auth%');
                    });
                }
            })
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('user_name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('action', 'ilike', '%' . $this->search . '%')
                      ->orWhere('description', 'ilike', '%' . $this->search . '%')
                      ->orWhere('user_id', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->childSearch, function($q) {
                $q->where(function($sub) {
                    $sub->where('action', 'ilike', '%' . $this->childSearch . '%')
                      ->orWhere('description', 'ilike', '%' . $this->childSearch . '%')
                      ->orWhere('ip_address', 'ilike', '%' . $this->childSearch . '%')
                      ->orWhere('user_agent', 'ilike', '%' . $this->childSearch . '%');
                });
            })
            ->when($this->actionFilter, function($q) {
                $q->where('action', $this->actionFilter);
            })
            ->when($this->dateFrom, function($q) {
                $q->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            })
            ->when($this->dateTo, function($q) {
                $q->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            })
            ->latest()
            ->take(100)
            ->get();
    }

    /**
     * Export all filtered logs to an Excel file.
     */
    public function export()
    {
        $filters = [
            'search' => $this->search,
            'actionFilter' => $this->actionFilter,
            'activityCategory' => $this->activityCategory,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ActivityLogExport($filters),
            'activity_logs_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /**
     * Export logs exclusively for a single user to Excel.
     */
    public function exportUserLogs($userKey)
    {
        $filters = [
            'search' => $this->search,
            'actionFilter' => $this->actionFilter,
            'activityCategory' => $this->activityCategory,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'userId' => $userKey,
        ];

        $cleanName = $userKey === '__SYSTEM__' ? 'system' : preg_replace('/[^A-Za-z0-9_-]/', '_', $userKey);
        $filename = 'audit_log_' . $cleanName . '_' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ActivityLogExport($filters),
            $filename
        );
    }

    public function render()
    {
        $users = $this->getFilteredUsersQuery()->paginate($this->perPage);

        // Eager load User metadata (Role & Region) in a single query to prevent N+1
        $userIds = $users->pluck('user_id')->filter()->unique();
        if ($userIds->isNotEmpty()) {
            $userModels = User::with('roles')->whereIn('userid', $userIds)->get()->keyBy('userid');
            $users->getCollection()->transform(function($u) use ($userModels) {
                $matched = $userModels->get($u->user_id);
                $u->role_name = $matched?->roles->first()?->name;
                $u->region_code = $matched?->region_code;
                return $u;
            });
        }

        $childLogs = [];
        if ($this->expandedUserId) {
            $childLogs = $this->getChildLogs($this->expandedUserId);
        }

        // Selected log for modal inspection
        $selectedLog = null;
        if ($this->selectedLogId) {
            $selectedLog = ActivityLog::find($this->selectedLogId);
        }

        // Fetch unique actions to populate filter dropdown (Cached 1 hour)
        $actions = Cache::remember('activity_log_actions_list', 3600, function() {
            return ActivityLog::select('action')
                ->whereNotNull('action')
                ->where('action', '!=', '')
                ->distinct()
                ->orderBy('action')
                ->pluck('action');
        });

        return view('livewire.settings.activity-log-management', [
            'users' => $users,
            'childLogs' => $childLogs,
            'actions' => $actions,
            'kpiStats' => $this->kpiStats,
            'selectedLog' => $selectedLog,
        ])->layout('layouts.app');
    }
}
