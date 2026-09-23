<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JksSummaryExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Region',
            'Area',
            'Supervisor',
            'Distributor',
            'Kode Salesman',
            'Nama Salesman',
            'Total RO',
            'Total JKS',
            'Non Rute',
            'Non GPS',
            'Senin Ganjil',
            'Senin Genap',
            'Senin Non GPS',
            'Selasa Ganjil',
            'Selasa Genap',
            'Selasa Non GPS',
            'Rabu Ganjil',
            'Rabu Genap',
            'Rabu Non GPS',
            'Kamis Ganjil',
            'Kamis Genap',
            'Kamis Non GPS',
            'Jumat Ganjil',
            'Jumat Genap',
            'Jumat Non GPS',
            'Sabtu Ganjil',
            'Sabtu Genap',
            'Sabtu Non GPS',
            'Minggu Ganjil',
            'Minggu Genap',
            'Minggu Non GPS',
        ];
    }

    public function map($row): array
    {
        return [
            $row->region_name ?? '-',
            $row->area_name ?? '-',
            $row->supervisor_name ?? '-',
            $row->distributor_name ?? '-',
            $row->salesman_code ?? '-',
            $row->salesman_name ?? '-',
            $row->total_ro,
            $row->total_jks,
            $row->non_rute,
            $row->non_gps,
            $row->senin_gjl,
            $row->senin_gnp,
            $row->senin_non_gps,
            $row->selasa_gjl,
            $row->selasa_gnp,
            $row->selasa_non_gps,
            $row->rabu_gjl,
            $row->rabu_gnp,
            $row->rabu_non_gps,
            $row->kamis_gjl,
            $row->kamis_gnp,
            $row->kamis_non_gps,
            $row->jumat_gjl,
            $row->jumat_gnp,
            $row->jumat_non_gps,
            $row->sabtu_gjl,
            $row->sabtu_gnp,
            $row->sabtu_non_gps,
            $row->minggu_gjl,
            $row->minggu_gnp,
            $row->minggu_non_gps,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
