<?php

namespace App\Exports;

use App\Services\JksSalesmanService;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JksSalesmanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $filters;

    public function __construct(string $search, array $filters)
    {
        $this->search = $search;
        $this->filters = $filters;
    }

    public function query()
    {
        $service = new JksSalesmanService();
        return $service->getExportQuery($this->search, $this->filters);
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Region Name',
            'Area Name',
            'Supervisor',
            'Distributor Code',
            'Distributor Name',
            'Salesman Code',
            'Salesman Name',
            'Customer Code',
            'Eskalink Code',
            'Customer Name',
            'Address',
            'Kecamatan',
            'Desa',
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu',
            'Minggu 1',
            'Minggu 2',
            'Minggu 3',
            'Minggu 4',
        ];
    }

    public function map($row): array
    {
        return [
            $row->bulan,
            $row->region_name,
            $row->area_name,
            $row->supervisor_name,
            $row->distributor_code,
            $row->distributor_name,
            $row->salesman_code,
            $row->salesman_name,
            $row->uniq_kode,
            $row->customer_code,
            $row->customer_name,
            $row->customer_address,
            $row->kecamatan,
            $row->desa,
            $row->h1,
            $row->h2,
            $row->h3,
            $row->h4,
            $row->h5,
            $row->h6,
            $row->h7,
            $row->w1,
            $row->w2,
            $row->w3,
            $row->w4,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Default text style for entire header row
        $sheet->getStyle('A1:Y1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        // Group 1: Reference Data (A to N) - Green
        $sheet->getStyle('A1:N1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF10B981');

        // Group 2: Hari / Days (O to U) - Blue
        $sheet->getStyle('O1:U1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF3B82F6');

        // Group 3: Minggu / Weeks (V to Y) - Purple
        $sheet->getStyle('V1:Y1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF8B5CF6');

        // Freeze header row
        $sheet->freezePane('A2');

        return [];
    }
}
