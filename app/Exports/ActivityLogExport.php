<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ActivityLogExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;
    protected $actionFilter;
    protected $activityCategory;
    protected $dateFrom;
    protected $dateTo;
    protected $userId;

    public function __construct($filters)
    {
        $this->search = $filters['search'] ?? null;
        $this->actionFilter = $filters['actionFilter'] ?? null;
        $this->activityCategory = $filters['activityCategory'] ?? null;
        $this->dateFrom = $filters['dateFrom'] ?? null;
        $this->dateTo = $filters['dateTo'] ?? null;
        $this->userId = $filters['userId'] ?? null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return ActivityLog::query()
            ->when($this->userId, function($q) {
                if ($this->userId === '__SYSTEM__') {
                    $q->whereNull('user_id');
                } else {
                    $q->where('user_id', $this->userId);
                }
            })
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
                $q->where('created_at', '>=', \Carbon\Carbon::parse($this->dateFrom)->startOfDay());
            })
            ->when($this->dateTo, function($q) {
                $q->where('created_at', '<=', \Carbon\Carbon::parse($this->dateTo)->endOfDay());
            })
            ->latest();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Waktu',
            'User ID',
            'Nama User',
            'Aksi',
            'Deskripsi',
            'IP Address',
            'User Agent',
        ];
    }

    /**
     * @param ActivityLog $log
     * @return array
     */
    public function map($log): array
    {
        return [
            $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
            $log->user_id,
            $log->user_name ?: 'System/Guest',
            $log->action,
            $log->description,
            $log->ip_address,
            $log->user_agent,
        ];
    }
}
