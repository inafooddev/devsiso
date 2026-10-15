<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisaKunjunganSeTrendExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $rows;
    protected $totals;
    protected $year;
    protected $callType;

    public function __construct($rows, $totals, $year, $callType)
    {
        $this->rows = $rows;
        $this->totals = $totals;
        $this->year = $year;
        $this->callType = $callType;
    }

    public function headings(): array
    {
        return [
            'No',
            'Bulan',
            'Hari Aktif',
            'Jumlah SE',
            'Plan Call (PC)',
            'Actual Call (AC)',
            '% Call Compliance (AC)',
            'Drop Call',
            'Effective Call (EC)',
            '% Effective Call Rate (EC)',
            '% Total Konversi (EC/PC)',
            'Unproductive Call',
            'Total Order (Rp)',
        ];
    }

    public function collection()
    {
        $result = collect();

        foreach ($this->rows as $r) {
            $result->push([
                $r->month_num,
                $r->month_name,
                $r->active_days,
                $r->active_se,
                $r->pc,
                $r->ac,
                $r->ac_percent !== null ? $r->ac_percent . '%' : '-',
                $r->drop_call,
                $r->ec,
                $r->ec_percent !== null ? $r->ec_percent . '%' : '-',
                $r->ec_pc_percent !== null ? $r->ec_pc_percent . '%' : '-',
                $r->unproductive_call,
                $r->order_val,
            ]);
        }

        // Grand Total row
        $result->push([
            'TOTAL',
            'Tahun ' . $this->year,
            '-',
            '-',
            $this->totals->total_pc,
            $this->totals->total_ac,
            $this->totals->ac_percent . '%',
            $this->totals->total_drop,
            $this->totals->total_ec,
            $this->totals->ec_percent . '%',
            $this->totals->ec_pc_percent . '%',
            $this->totals->total_unproductive,
            $this->totals->total_val,
        ]);

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->rows) + 2;

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
            $lastRow => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ],
            ]
        ];
    }
}
