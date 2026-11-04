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

class SemarakRejekiRsmSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected array $regions;
    protected int $year;
    protected int $rowNum = 0;

    public function __construct(array $regions, int $year = 2026)
    {
        $this->regions = $regions;
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->regions);
    }

    public function title(): string
    {
        return 'Ringkasan Region (RSM)';
    }

    public function headings(): array
    {
        return [
            ['SEMARAK REJEKI - MONITORING REWARD REGION & RSM (Q4 ' . $this->year . ')'],
            ['Catatan: Penentuan kelayakan pencairan reward mengacu pada klausul proposal resmi (Okt ≥70%, Okt-Nov ≥80%, Okt-Des ≥100%).'],
            [
                'No',
                'Nama Region',
                'Nomor Proposal',
                'MAX LY (Ktn)',
                'Target L1 / Bln (Ktn)',
                'Target L2 / Bln (Ktn)',
                'Realisasi Okt (Ktn)',
                'Realisasi Nov (Ktn)',
                'Realisasi Des (Ktn)',
                'Total Realisasi (Ktn)',
                '% vs L1',
                '% vs L2',
                'Gap L1 (Ktn)',
                'Gap L2 (Ktn)',
                'Status Ketercapaian Region',
                'Estimasi Reward RSM (Closing)',
                'Nilai Omset (Rp)',
            ],
        ];
    }

    public function map($r): array
    {
        $this->rowNum++;
        return [
            $this->rowNum,
            $r['region_name'] ?? '-',
            $r['nomor_proposal'] ?? '-',
            (int)($r['max_ly'] ?? 0),
            (float)($r['target_l1_bln'] ?? 0),
            (float)($r['target_l2_bln'] ?? 0),
            (float)($r['okt_ktn'] ?? 0),
            (float)($r['nov_ktn'] ?? 0),
            (float)($r['des_ktn'] ?? 0),
            (float)($r['total_ktn'] ?? 0),
            ($r['pct_l1'] ?? 0) . '%',
            ($r['pct_l2'] ?? 0) . '%',
            (float)($r['gap_l1'] ?? 0),
            (float)($r['gap_l2'] ?? 0),
            $r['status_label'] ?? '-',
            (float)($r['reward_rsm_hak'] ?? 0),
            (float)($r['total_val'] ?? 0),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 16,
            'C' => 32,
            'D' => 14,
            'E' => 18,
            'F' => 18,
            'G' => 16,
            'H' => 16,
            'I' => 16,
            'J' => 18,
            'K' => 12,
            'L' => 12,
            'M' => 14,
            'N' => 14,
            'O' => 28,
            'P' => 22,
            'Q' => 22,
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
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
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

                $ws->mergeCells('A1:Q1');
                $ws->mergeCells('A2:Q2');

                $ws->getStyle("A3:Q{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // Number formatting
                $ws->getStyle("D4:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle("M4:N{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $ws->getStyle("P4:Q{$lastRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
            },
        ];
    }
}
