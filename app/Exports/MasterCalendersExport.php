<?php

namespace App\Exports;

use App\Models\MasterCalender;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class MasterCalendersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = MasterCalender::query();

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('date', 'ilike', '%' . $search . '%')
                  ->orWhere('day', 'ilike', '%' . $search . '%');
            });
        }

        if (!empty($this->filters['yearFilter'])) {
            $query->where('year', $this->filters['yearFilter']);
        }

        if (!empty($this->filters['monthFilter'])) {
            $query->where('month', $this->filters['monthFilter']);
        }

        if (isset($this->filters['liburFilter']) && $this->filters['liburFilter'] !== '' && $this->filters['liburFilter'] !== null) {
            $query->where('libur_nasional', (bool) $this->filters['liburFilter']);
        }

        if (!empty($this->filters['weekMonthFilter'])) {
            $query->where('week_month', $this->filters['weekMonthFilter']);
        }

        return $query->orderBy('date', 'asc');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'Nomor Hari',
            'Minggu ke (Tahun)',
            'Minggu ke (Bulan)',
            'Bulan',
            'Kuartal',
            'Semester',
            'Tahun',
            'Libur Nasional',
            'Dibuat Pada',
            'Diperbarui Pada',
        ];
    }

    /**
     * @param MasterCalender $calender
     * @return array
     */
    public function map($calender): array
    {
        $dateStr = $calender->date instanceof Carbon 
            ? $calender->date->format('Y-m-d') 
            : substr((string)$calender->date, 0, 10);

        return [
            $dateStr,
            $calender->day,
            $calender->day_number,
            $calender->week_year,
            $calender->week_month,
            $calender->month,
            $calender->quarter,
            $calender->semester,
            $calender->year,
            $calender->libur_nasional ? 'Ya' : 'Tidak',
            $calender->created_at ? $calender->created_at->format('Y-m-d H:i:s') : null,
            $calender->updated_at ? $calender->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
