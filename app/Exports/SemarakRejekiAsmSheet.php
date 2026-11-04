<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SemarakRejekiAsmSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected array $asms;
    protected int $year;
    protected int $rowNum = 0;

    public function __construct(array $asms, int $year = 2026)
    {
        $this->asms = $asms;
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->asms);
    }

    public function title(): string
    {
        return 'Detail Area & ASM';
    }

    public function headings(): array
    {
        return [
            ['SEMARAK REJEKI - MONITORING & TACTICAL TOOL REWARD ASM (Q4 ' . $this->year . ')'],
            ['Catatan: Berlaku ketentuan kelompok wajib sama dengan layer region, kecuali independen jika region gagal.'],
            [
                'No',
                'Region',
                'Area ASM',
                'Nama ASM di Database',
                'MAX LY (Ktn)',
                'Target L1/Bln',
                'Target L2/Bln',
                'Okt (Ktn)',
                '% Okt (Min 70%)',
                'Nov (Ktn)',
                'Des (Ktn)',
                'Total Realisasi (Ktn)',
                '% vs L1',
                '% vs L2',
                'Gap L1 (Ktn)',
                'Gap L2 (Ktn)',
                'Jalur Reward',
                'Status Ketercapaian',
                'Estimasi Reward ASM (Closing)',
                'Nilai Omset (Rp)',
            ],
        ];
    }

    public function map($a): array
    {
        $this->rowNum++;
        return [
            $this->rowNum,
            $a['region_name'] ?? '-',
            $a['area_name'] ?? '-',
            $a['asm_db_name'] ?? '-',
            (int)($a['max_ly'] ?? 0),
            (float)($a['target_l1_bln'] ?? 0),
            (float)($a['target_l2_bln'] ?? 0),
            (float)($a['okt_ktn'] ?? 0),
            ($a['m1_pct'] ?? 0) . '%',
            (float)($a['nov_ktn'] ?? 0),
            (float)($a['des_ktn'] ?? 0),
            (float)($a['total_ktn'] ?? 0),
            ($a['pacing_pct_l1'] ?? 0) . '%',
            ($a['pacing_pct_l2'] ?? 0) . '%',
            (float)($a['gap_l1'] ?? 0),
            (float)($a['gap_l2'] ?? 0),
            $a['jalur'] ?? '-',
            $a['status_label'] ?? '-',
            (float)($a['estimasi_reward'] ?? $a['hak_reward'] ?? 0),
            (float)($a['total_val'] ?? 0),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 14,
            'C' => 18,
            'D' => 24,
            'E' => 14,
            'F' => 16,
            'G' => 16,
            'H' => 14,
            'I' => 16,
            'J' => 14,
            'K' => 14,
            'L' => 18,
            'M' => 12,
            'N' => 12,
            'O' => 14,
            'P' => 14,
            'Q' => 16,
            'R' => 28,
            'S' => 22,
            'T' => 22,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E293B']],
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748B']],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $lastRow = $this->rowNum + 3;

                $ws->mergeCells('A1:T1');
                $ws->mergeCells('A2:T2');

                $ws->getStyle("A3:T{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // Number formatting
                $ws->getStyle("E4:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle("J4:L{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle("O4:P{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle("S4:T{$lastRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
            },
        ];
    }
}
