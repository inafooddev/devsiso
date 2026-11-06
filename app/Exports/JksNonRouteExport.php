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
        $service = app(JksNonRouteService::class);

        return $service->getBaseQueryBuilder($this->search, $this->filters)
            ->orderBy('mto.distributor_name')
            ->orderBy('mto.customer_name');
    }

    public function headings(): array
    {
        $bulan = $this->filters['bulan'] ?? date('Y-m-01');
        $baseBulan = \Carbon\Carbon::parse($bulan);
        
        $headings = [
            'Distributor Code',
            'Distributor Name',
            'Customer Kode',
            'Customer Kode PRC',
            'Nama Cust',
            'Alamat',
            'Avg 2026',
        ];

        for ($i = 1; $i <= 6; $i++) {
            $headings[] = $baseBulan->copy()->subMonths($i)->translatedFormat('M y');
        }

        $headings[] = 'Remark';

        return $headings;
    }

    public function map($row): array
    {
        $data = [
            $row->distributor_code,
            $row->distributor_name,
            $row->customer_code,
            $row->customer_eska ?? '-',
            $row->customer_name,
            $row->alamat ?: '-',
            $row->avg_value_net ? (float) $row->avg_value_net : 0,
        ];

        for ($i = 1; $i <= 6; $i++) {
            $histField = 'history_' . $i;
            $val = $row->$histField ?? 0;
            $data[] = $val > 0 ? (float) $val : 0;
        }

        $data[] = $row->remark ?: '-';

        return $data;
    }

    public function columnFormats(): array
    {
        return [
            'G' => '"Rp" #,##0_-', // Avg
            'H' => '#,##0_-',
            'I' => '#,##0_-',
            'J' => '#,##0_-',
            'K' => '#,##0_-',
            'L' => '#,##0_-',
            'M' => '#,##0_-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
