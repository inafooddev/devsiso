<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AnalisaKunjunganSeRaportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $meta;

    public function __construct($data, $meta = [])
    {
        $this->data = $data;
        $this->meta = $meta;
    }

    public function title(): string
    {
        return 'Raport Kunjungan SE';
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Kode SE',
            'Nama Salesman',
            'Kode Distributor',
            'Nama Distributor',
            'Area',
            'Region',
            'Target PC',
            'Realisasi AC',
            '% AC/PC',
            'Hari Aktif',
            'Skor P1 (20%)',
            'Total EC',
            'Strike Rate %',
            'Value Order (Rp)',
            'Drop Size (Rp)',
            'Skor P2 (40%)',
            'Temuan Depo',
            'Temuan Kembar',
            'Temuan Luar Radius',
            'Temuan Kilat',
            'Skor P3 (25%)',
            'Rata2 Jam Kerja (Jam/Hari)',
            'Rata2 Durasi (Menit)',
            'Skor P4 (15%)',
            'Skor Total (0-100)',
            'Grade',
            'Predikat',
            'Rekomendasi Supervisor'
        ];
    }

    public function collection()
    {
        $result = collect();
        $rank = 1;

        foreach ($this->data as $item) {
            $row = (object) $item;
            $result->push([
                $rank++,
                $row->salesman_code ?? '-',
                $row->salesman_name ?? '-',
                $row->distributor_code ?? '-',
                $row->distributor_name ?? '-',
                $row->area_name ?? $row->area_code ?? '-',
                $row->region_name ?? $row->region_code ?? '-',
                $row->total_pc ?? 0,
                $row->total_ac ?? 0,
                number_format($row->pct_ac_pc ?? 0, 1) . '%',
                $row->hari_aktif ?? 0,
                number_format($row->score_pilar_1 ?? 0, 1),
                $row->total_ec ?? 0,
                number_format($row->strike_rate ?? 0, 1) . '%',
                $row->total_value ?? 0,
                $row->drop_size ?? 0,
                number_format($row->score_pilar_2 ?? 0, 1),
                $row->count_depo ?? 0,
                $row->count_kembar ?? 0,
                $row->count_radius ?? 0,
                $row->count_kilat ?? 0,
                number_format($row->score_pilar_3 ?? 0, 1),
                number_format($row->avg_work_hours ?? 0, 1) . ' Jam',
                number_format($row->avg_minutes_per_store ?? 0, 1),
                number_format($row->score_pilar_4 ?? 0, 1),
                number_format($row->skor_total ?? 0, 1),
                $row->grade ?? '-',
                $row->grade_label ?? '-',
                $row->spv_recommendation ?? '-'
            ]);
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // Header style
        $sheet->getStyle("A1:{$highestCol}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Slate-800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Center alignment for specific columns
        if ($highestRow > 1) {
            $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D2:D{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("AA2:AA{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Number formats for currency columns (Value Order, Drop Size)
            $sheet->getStyle("O2:P{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Border
            $sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
            ]);

            // Alternating row colors
            for ($i = 2; $i <= $highestRow; $i++) {
                if ($i % 2 === 0) {
                    $sheet->getStyle("A{$i}:{$highestCol}{$i}")->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ]);
                }
            }
        }

        return [];
    }
}
