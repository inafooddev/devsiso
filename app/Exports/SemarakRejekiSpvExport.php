<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SemarakRejekiSpvExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, WithColumnWidths, WithEvents
{
    protected array $data;
    protected int $year;
    protected int $rowIndex = 0;

    public function __construct(array $data, int $year = 2026)
    {
        $this->data = array_values($data);
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            // Row 1: Main Title
            ['SEMARAK REJEKI - MONITORING & ACTION PLAN REWARD SPV (Q4 ' . $this->year . ')'],
            // Row 2: Official Eskalink Disclaimer
            ['Catatan Penting (Disclaimer): Seluruh angka pada laporan ini disajikan sebagai acuan monitoring berjalan (estimasi real-time). Penentuan kelulusan milestone dan hak klaim hadiah akhir sepenuhnya mengacu pada angka closing final resmi dari sistem Eskalink.'],
            // Row 3: Group Headers (Level 1)
            [
                'PROFIL SUPERVISOR & TARGET PAKET AWAL', '', '', '', '', '', '', '', '',
                'TAHAP 1: OKT (WAJIB ≥ 70%)', '', '',
                'TAHAP 2: OKT-NOV (WAJIB ≥ 80%)', '', '',
                'TAHAP 3: OKT-DES (TARGET 100%)', '', '',
                'STATUS KELAYAKAN & PANDUAN TINDAK LANJUT', ''
            ],
            // Row 4: Column Sub-Headers (Level 2)
            [
                'No',
                'Wilayah',
                'Region',
                'Area',
                'Kode SPV',
                'Nama Supervisor',
                'Jml Depo',
                'Paket Awal & Unit Hadiah',
                'Target 3 Bln (Ktn)',
                'Syarat Min 70% (Ktn)',
                'Realisasi Okt (Ktn)',
                '% Okt & Status',
                'Syarat Min 80% (Ktn)',
                'Realisasi Okt-Nov (Ktn)',
                '% Okt-Nov & Status',
                'Realisasi Okt-Des (Ktn)',
                '% Total 3 Bln',
                'Selisih vs 100% (Ktn)',
                'Status Kelayakan',
                'Panduan & Estimasi Posisi Hadiah',
            ],
        ];
    }

    public function map($row): array
    {
        $this->rowIndex++;

        $wilayah = strtoupper($row['kategori_wilayah'] ?? '-');
        $paketAwal = 'Paket ' . ($row['kode_paket_awal'] ?? '-') . ' - ' . ($row['nama_hadiah_awal'] ?? '-');

        $pctM1Fmt = number_format((float) ($row['pct_m1'] ?? 0), 1, ',', '.');
        $statusM1Text = !empty($row['is_m1_pass']) ? "{$pctM1Fmt}% (Lolos)" : "{$pctM1Fmt}% (Gugur)";

        $pctKum2Fmt = number_format((float) ($row['pct_kum2'] ?? 0), 1, ',', '.');
        $statusKum2Text = !empty($row['is_kum2_pass']) ? "{$pctKum2Fmt}% (Lolos)" : "{$pctKum2Fmt}% (Kejar 80%)";

        $pctTotalFmt = number_format((float) ($row['pct_total'] ?? 0), 1, ',', '.') . '%';
        $diff100 = round((float) ($row['total_realisasi_3bln'] ?? 0) - (float) ($row['target_3bln'] ?? 0), 1);

        $statusKey = $row['status_key'] ?? 'on_track';
        if ($statusKey === 'hangus') {
            $statusKelayakan = 'GUGUR (<70% OKT)';
            $panduan = '[Tidak Berhak Klaim Hadiah] ' . ($row['action_advice'] ?? '-');
        } elseif ($statusKey === 'warning') {
            $statusKelayakan = 'KEJAR TARGET 80%';
            $panduan = "[Target: Paket {$row['kode_paket_awal']} ({$row['nama_hadiah_awal']})] " . ($row['action_advice'] ?? '-');
        } elseif ($statusKey === 'upgrade_ready') {
            $statusKelayakan = "AUTO UPGRADE (PAKET {$row['paket_tercapai']})";
            $panduan = "[Berhak Upgrade: Paket {$row['paket_tercapai']} ({$row['hadiah_tercapai']})] " . ($row['action_advice'] ?? '-');
        } else {
            $statusKelayakan = 'AMAN (ON TRACK)';
            $panduan = "[On Track: Paket {$row['kode_paket_awal']} ({$row['nama_hadiah_awal']})] " . ($row['action_advice'] ?? '-');
        }

        return [
            $this->rowIndex,
            $wilayah,
            $row['region_name'] ?? '-',
            $row['area_name'] ?? '-',
            $row['supervisor_code'] ?? '-',
            $row['supervisor_name'] ?? '-',
            (int) ($row['distributor_count'] ?? 0),
            $paketAwal,
            round((float) ($row['target_3bln'] ?? 0), 1),
            round((float) ($row['m1_min_70'] ?? 0), 1),
            round((float) ($row['realisasi_m1'] ?? 0), 1),
            $statusM1Text,
            round((float) ($row['kum2_min_80'] ?? 0), 1),
            round((float) ($row['realisasi_kum2'] ?? 0), 1),
            $statusKum2Text,
            round((float) ($row['total_realisasi_3bln'] ?? 0), 1),
            $pctTotalFmt,
            $diff100,
            $statusKelayakan,
            $panduan,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0.0',
            'K' => '#,##0.0',
            'M' => '#,##0.0',
            'N' => '#,##0.0',
            'P' => '#,##0.0',
            'R' => '+#,##0.0;-#,##0.0;0.0',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 11,
            'C' => 18,
            'D' => 22,
            'E' => 16,
            'F' => 28,
            'G' => 11,
            'H' => 36,
            'I' => 17,
            'J' => 19,
            'K' => 18,
            'L' => 18,
            'M' => 19,
            'N' => 20,
            'O' => 20,
            'P' => 20,
            'Q' => 14,
            'R' => 19,
            'S' => 24,
            'T' => 54,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataCount = count($this->data);
                $firstDataRow = 5;
                $lastDataRow = max($firstDataRow, $firstDataRow + $dataCount - 1);
                $totalRow = $lastDataRow + 1;

                // 1. Merge Banner & Group Headers
                $sheet->mergeCells('A1:T1');
                $sheet->mergeCells('A2:T2');
                $sheet->mergeCells('A3:I3');
                $sheet->mergeCells('J3:L3');
                $sheet->mergeCells('M3:O3');
                $sheet->mergeCells('P3:R3');
                $sheet->mergeCells('S3:T3');

                // Row Heights
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension(4)->setRowHeight(26);

                // 2. Style Row 1: Title Banner
                $sheet->getStyle('A1:T1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // 3. Style Row 2: Disclaimer Banner
                $sheet->getStyle('A2:T2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '78350F']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // 4. Style Row 3 & Row 4: Group Headers & Column Headers
                $groupColors = [
                    'A3:I3' => '334155', // Slate-700
                    'A4:I4' => '475569', // Slate-600
                    'J3:L3' => '1D4ED8', // Blue-700
                    'J4:L4' => '2563EB', // Blue-600
                    'M3:O3' => '4338CA', // Indigo-700
                    'M4:O4' => '4F46E5', // Indigo-600
                    'P3:R3' => '047857', // Emerald-700
                    'P4:R4' => '059669', // Emerald-600
                    'S3:T3' => 'B45309', // Amber-700
                    'S4:T4' => 'D97706', // Amber-600
                ];

                foreach ($groupColors as $range => $hex) {
                    $sheet->getStyle($range)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $hex]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                }

                // 5. Data Rows Alignment & Conditional Row Styling
                if ($dataCount > 0) {
                    $sheet->getStyle("A{$firstDataRow}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$firstDataRow}:E{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G{$firstDataRow}:G{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("L{$firstDataRow}:L{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("O{$firstDataRow}:O{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("Q{$firstDataRow}:Q{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("S{$firstDataRow}:S{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A{$firstDataRow}:T{$lastDataRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("H{$firstDataRow}:H{$lastDataRow}")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("T{$firstDataRow}:T{$lastDataRow}")->getAlignment()->setWrapText(true);

                    foreach ($this->data as $idx => $item) {
                        $r = $firstDataRow + $idx;
                        $sheet->getRowDimension($r)->setRowHeight(26);

                        // Zebra stripe background
                        if ($idx % 2 === 1) {
                            $sheet->getStyle("A{$r}:T{$r}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F8FAFC');
                        }

                        // Wilayah badge color (Col B)
                        if (($item['kategori_wilayah'] ?? '') === 'jawa') {
                            $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB('1D4ED8');
                        } else {
                            $sheet->getStyle("B{$r}")->getFont()->setBold(true)->getColor()->setRGB('047857');
                        }

                        // Okt status cell (Col L)
                        if (!empty($item['is_m1_pass'])) {
                            $sheet->getStyle("L{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '047857']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ECFDF5']],
                            ]);
                        } else {
                            $sheet->getStyle("L{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => 'BE123C']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF1F2']],
                            ]);
                        }

                        // Okt-Nov status cell (Col O)
                        if (!empty($item['is_kum2_pass'])) {
                            $sheet->getStyle("O{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '047857']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ECFDF5']],
                            ]);
                        } else {
                            $sheet->getStyle("O{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => 'B45309']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBEB']],
                            ]);
                        }

                        // Final Status cell (Col S)
                        $stKey = $item['status_key'] ?? 'on_track';
                        if ($stKey === 'hangus') {
                            $sheet->getStyle("S{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => 'BE123C']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']],
                            ]);
                        } elseif ($stKey === 'warning') {
                            $sheet->getStyle("S{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => 'B45309']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                            ]);
                        } else {
                            $sheet->getStyle("S{$r}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '047857']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
                            ]);
                        }
                    }

                    // 6. Grand Total Row at Bottom
                    $col = collect($this->data);
                    $sumTarget3Bln = (float) $col->sum('target_3bln');
                    $sumTargetM1 = (float) $col->sum('target_m1');
                    $sumRealM1 = (float) $col->sum('realisasi_m1');
                    $sumTargetKum2 = (float) $col->sum('target_kum2');
                    $sumRealKum2 = (float) $col->sum('realisasi_kum2');
                    $sumReal3Bln = (float) $col->sum('total_realisasi_3bln');

                    $avgPctM1 = $sumTargetM1 > 0 ? number_format(($sumRealM1 / $sumTargetM1) * 100, 1, ',', '.') . '%' : '0%';
                    $avgPctKum2 = $sumTargetKum2 > 0 ? number_format(($sumRealKum2 / $sumTargetKum2) * 100, 1, ',', '.') . '%' : '0%';
                    $avgPctTotal = $sumTarget3Bln > 0 ? number_format(($sumReal3Bln / $sumTarget3Bln) * 100, 1, ',', '.') . '%' : '0%';

                    $amanCnt = $col->whereIn('status_key', ['on_track', 'upgrade_ready'])->count();
                    $kejarCnt = $col->where('status_key', 'warning')->count();
                    $gugurCnt = $col->where('status_key', 'hangus')->count();

                    $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
                    $sheet->setCellValue("A{$totalRow}", "GRAND TOTAL ({$dataCount} SUPERVISOR)");
                    $sheet->setCellValue("G{$totalRow}", "=SUM(G{$firstDataRow}:G{$lastDataRow})");
                    $sheet->setCellValue("H{$totalRow}", '-');
                    $sheet->setCellValue("I{$totalRow}", "=SUM(I{$firstDataRow}:I{$lastDataRow})");
                    $sheet->setCellValue("J{$totalRow}", "=SUM(J{$firstDataRow}:J{$lastDataRow})");
                    $sheet->setCellValue("K{$totalRow}", "=SUM(K{$firstDataRow}:K{$lastDataRow})");
                    $sheet->setCellValue("L{$totalRow}", $avgPctM1);
                    $sheet->setCellValue("M{$totalRow}", "=SUM(M{$firstDataRow}:M{$lastDataRow})");
                    $sheet->setCellValue("N{$totalRow}", "=SUM(N{$firstDataRow}:N{$lastDataRow})");
                    $sheet->setCellValue("O{$totalRow}", $avgPctKum2);
                    $sheet->setCellValue("P{$totalRow}", "=SUM(P{$firstDataRow}:P{$lastDataRow})");
                    $sheet->setCellValue("Q{$totalRow}", $avgPctTotal);
                    $sheet->setCellValue("R{$totalRow}", "=SUM(R{$firstDataRow}:R{$lastDataRow})");
                    $sheet->setCellValue("S{$totalRow}", "{$amanCnt} Aman • {$kejarCnt} Kejar • {$gugurCnt} Gugur");
                    $sheet->setCellValue("T{$totalRow}", 'Acuan Monitoring Real-Time (Final Mengacu Closing Eskalink)');

                    $sheet->getRowDimension($totalRow)->setRowHeight(26);
                    $sheet->getStyle("A{$totalRow}:T{$totalRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getStyle("A{$totalRow}:H{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("L{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("O{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("Q{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("S{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $sheet->getStyle("G{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("J{$totalRow}:K{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.0');
                    $sheet->getStyle("M{$totalRow}:N{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.0');
                    $sheet->getStyle("P{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.0');
                    $sheet->getStyle("R{$totalRow}")->getNumberFormat()->setFormatCode('+#,##0.0;-#,##0.0;0.0');
                }

                // 7. Table Borders, AutoFilter, and Freeze Pane
                $endBorderRow = $dataCount > 0 ? $totalRow : 4;
                $sheet->getStyle("A3:T{$endBorderRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                $sheet->setAutoFilter("A4:T{$lastDataRow}");
                $sheet->freezePane('G5');
            },
        ];
    }
}
