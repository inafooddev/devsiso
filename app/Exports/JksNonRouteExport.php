<?php

namespace App\Exports;

use App\Services\JksNonRouteService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class JksNonRouteExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $search;
    protected $filters;

    public function __construct($search = '', $filters = [])
    {
        $this->search = $search;
        $this->filters = $filters;
    }

    public function query()
    {
        // Using an anonymous class to easily access the protected method of the service
        // Since getBaseQuery is protected in JksNonRouteService, we can instantiate it and use reflection,
        // or just recreate the query logic here, but using the service method is cleaner if we use a helper.
        // Let's create a proxy to the protected method:
        $service = new class extends JksNonRouteService {
            public function getQuery($search, $filters) {
                return $this->getBaseQuery($search, $filters);
            }
        };

        return $service->getQuery($this->search, $this->filters)
            ->orderBy('mto.distributor_name')
            ->orderBy('mto.customer_name');
    }

    public function headings(): array
    {
        return [
            'Distributor Code',
            'Distributor Name',
            'Customer Kode',
            'Customer Kode PRC',
            'Nama Cust',
            'Alamat',
            'Avg',
            'Remark'
        ];
    }

    public function map($row): array
    {
        return [
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_code,
            $row->customer_eska ?? '-',
            $row->customer_name,
            $row->alamat ?: '-',
            $row->avg_value_net ? (float) $row->avg_value_net : 0,
            $row->remark ?: '-'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '"Rp" #,##0_-', // Format Rupiah yang bisa dijumlahkan di Excel
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
