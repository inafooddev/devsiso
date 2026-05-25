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
    protected $dateFrom;
    protected $dateTo;

    public function __construct($filters)
    {
        $this->search = $filters['search'] ?? null;
        $this->actionFilter = $filters['actionFilter'] ?? null;
        $this->dateFrom = $filters['dateFrom'] ?? null;
        $this->dateTo = $filters['dateTo'] ?? null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
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
