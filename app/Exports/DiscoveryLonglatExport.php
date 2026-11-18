<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use App\Livewire\Reports\DiscoveryLonglatReport;

class DiscoveryLonglatExport extends StringValueBinder implements FromQuery, WithHeadings, WithMapping, WithCustomValueBinder, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $selectedRegion;
    protected $selectedArea;
    protected $filterStatus;

    public function __construct($startDate, $endDate, $selectedRegion = '', $selectedArea = '', $filterStatus = 'clean')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->selectedRegion = $selectedRegion;
        $this->selectedArea = $selectedArea;
        $this->filterStatus = $filterStatus;
    }

    public function query()
    {
        $query = DiscoveryLonglatReport::getBaseQuery($this->startDate, $this->endDate);

        if ($this->selectedRegion) {
            $query->where('region_code', $this->selectedRegion);
        }
        if ($this->selectedArea) {
            $query->where('area_code', $this->selectedArea);
        }

        if ($this->filterStatus === 'clean') {
            $query->where('status_clean', 'CLEAN');
        } elseif ($this->filterStatus === 'at_distributor') {
            $query->where('status_clean', 'AT_DISTRIBUTOR');
        } elseif ($this->filterStatus === 'dup_visit') {
            $query->whereIn('status_clean', ['DUP_VISIT', 'DUP_BOTH']);
        } elseif ($this->filterStatus === 'dup_master') {
            $query->whereIn('status_clean', ['DUP_MASTER', 'DUP_BOTH']);
        } elseif ($this->filterStatus === 'dup_all') {
            $query->whereIn('status_clean', ['AT_DISTRIBUTOR', 'DUP_VISIT', 'DUP_MASTER', 'DUP_BOTH']);
        }

        return $query
            ->orderBy('region_code')
            ->orderBy('area_code')
            ->orderBy('custno');
    }

    public function headings(): array
    {
        return [
            'Status Titik',
            'Keterangan Titik',
            'Jarak ke Depo (Meter)',
            'Nama Depo/Distributor Terdekat',
            'Region Code',
            'Area Code',
            'Kode Cabang',
            'Cust No',
            'Cust Name',
            'Cust Address',
            'City',
            'Term',
            'Type Out',
            'Grup Out',
            'Harga',
            'New Latitude',
            'New Longitude',
            'Visit Date'
        ];
    }

    public function map($row): array
    {
        $statusLabel = match($row->status_clean ?? '') {
            'CLEAN' => 'CLEAN (Unik & di Luar Depo)',
            'AT_DISTRIBUTOR' => 'TITIK DI DEPO / DISTRIBUTOR (<=100m)',
            'DUP_VISIT' => 'DUPLIKAT KUNJUNGAN',
            'DUP_MASTER' => 'DUPLIKAT MASTER CUSTOMER',
            'DUP_BOTH' => 'DUPLIKAT GANDA (Kunjungan & Master)',
            default => 'UNKNOWN'
        };

        $desc = match($row->status_clean ?? '') {
            'CLEAN' => 'Koordinat baru, unik, dan di luar depo (siap update)',
            'AT_DISTRIBUTOR' => 'Titik anomali di kantor/gudang depo (' . ($row->dist_to_depo_meters ?? 0) . 'm dari ' . ($row->near_dist_name ?? 'Depo') . ')',
            'DUP_VISIT' => 'Titik sama dengan ' . ($row->visit_coord_count ?? 1) . ' toko pada hasil kunjungan',
            'DUP_MASTER' => 'Titik sudah terdaftar pada toko lain di master customer',
            'DUP_BOTH' => 'Duplikat kunjungan (' . ($row->visit_coord_count ?? 1) . ' toko) dan sudah ada di master customer',
            default => '-'
        };

        $distMeters = ($row->dist_to_depo_meters !== null) ? (string) $row->dist_to_depo_meters : '-';
        $nearDepo = $row->near_dist_name ?? '-';

        return [
            $statusLabel,
            $desc,
            $distMeters,
            $nearDepo,
            $row->region_code,
            $row->area_code,
            $row->kodecabang,
            $row->custno,
            $row->custname,
            $row->custadd1,
            $row->ccity,
            $row->cterm,
            $row->typeout,
            $row->grupout,
            $row->gharga,
            str_replace(',', '.', (string) $row->v_la),
            str_replace(',', '.', (string) $row->v_lg),
            $row->visit_date,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
