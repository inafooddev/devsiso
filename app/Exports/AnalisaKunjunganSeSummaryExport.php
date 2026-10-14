<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaKunjunganSeSummaryExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Distributor',
            'Kode SE',
            'Nama SE',
            'Hari Kerja',
            'Total PC',
            'Total AC',
            'Total % AC',
            'Total EC',
            'Total % EC',
            'Exclude XC PC',
            'Exclude XC AC',
            'Exclude XC % AC',
            'Exclude XC EC',
            'Exclude XC % EC',
            'Extra Call PC',
            'Extra Call AC',
            'Extra Call % AC',
            'Extra Call EC',
            'Extra Call % EC',
            'Total Order (Rp)',
            'Total Jam Toko',
            'Avg Jam Toko',
            'Total Jam Jalan',
            'Avg Jam Jalan',
        ];
    }

    public function collection()
    {
        $result = collect();
        $no = 1;

        foreach ($this->rows as $row) {
            $result->push([
                $no++,
                $row->distributor_name,
                $row->salesman_code,
                $row->salesman_name,
                $row->total_days,
                $row->total_pc,
                $row->total_ac,
                $row->total_ac_percent . '%',
                $row->total_ec,
                $row->total_ec_percent . '%',
                $row->exc_pc,
                $row->exc_ac,
                $row->exc_ac_percent . '%',
                $row->exc_ec,
                $row->exc_ec_percent . '%',
                $row->just_pc,
                $row->just_ac,
                $row->just_ac_percent . '%',
                $row->just_ec,
                $row->just_ec_percent . '%',
                $row->total_order_val,
                $row->total_jam_toko_str,
                $row->avg_jam_toko_str,
                $row->total_jam_jalan_str,
                $row->avg_jam_jalan_str,
            ]);
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }
}
