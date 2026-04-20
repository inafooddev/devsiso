<?php

namespace App\Exports;

use App\Models\MasterDistributor;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterDistributorsExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        $query = MasterDistributor::query();

        // Terapkan filter yang sama seperti di halaman index
        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('distributor_code', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('distributor_name', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('branch_name', 'like', '%' . $this->filters['search'] . '%')
                    ->orWhere('supervisor_name', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        if (isset($this->filters['statusFilter']) && $this->filters['statusFilter'] !== '') {
            $query->where('is_active', $this->filters['statusFilter']);
        }

        if (!empty($this->filters['regionFilter'])) {
            $query->where('region_code', $this->filters['regionFilter']);
        }

        if (!empty($this->filters['areaFilter'])) {
            $query->where('area_code', $this->filters['areaFilter']);
        }

        return $query->latest();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Distributor',
            'Nama Distributor',
            'Tanggal Bergabung',
            'Tanggal Berhenti',
            'Latitude',
            'Longitude',
            'Status Aktif',
            'Kode Region',
            'Nama Region',
            'Kode Area',
            'Nama Area',
            'Kode Supervisor',
            'Nama Supervisor',
            'Kode Cabang',
            'Nama Cabang',
        ];
    }

    /**
     * @param mixed $distributor
     * @return array
     */
    public function map($distributor): array
    {
        return [
            $distributor->distributor_code,
            $distributor->distributor_name,
            $distributor->join_date ? Date::dateTimeToExcel(\Carbon\Carbon::parse($distributor->join_date)) : null,
            $distributor->resign_date ? Date::dateTimeToExcel(\Carbon\Carbon::parse($distributor->resign_date)) : null,
            $distributor->latitude,
            $distributor->longitude,
            $distributor->is_active ? 'Ya' : 'Tidak',
            $distributor->region_code,
            $distributor->region_name,
            $distributor->area_code,
            $distributor->area_name,
            $distributor->supervisor_code,
            $distributor->supervisor_name,
            $distributor->branch_code,
            $distributor->branch_name,
        ];
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
