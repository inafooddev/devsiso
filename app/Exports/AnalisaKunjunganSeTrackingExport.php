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

class AnalisaKunjunganSeTrackingExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $stores;
    protected $weeksMap;
    protected $meta;

    public function __construct($stores, $weeksMap, $meta = [])
    {
        $this->stores = $stores;
        $this->weeksMap = $weeksMap;
        $this->meta = $meta;
    }

    public function title(): string
    {
        return 'Tracking Mingguan Toko';
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Distributor',
            'Kode SE',
            'Nama SE',
            'Kode Toko',
            'Nama Toko',
            'Alamat Toko',
            'Master GPS',
        ];

        foreach ($this->weeksMap as $wNum => $wInfo) {
            $headers[] = "{$wInfo['label']} ({$wInfo['range_label']})";
        }

        $headers[] = 'Status Tren Perbaikan';
        $headers[] = 'Total Order (Rp)';

        return $headers;
    }

    public function collection()
    {
        $result = collect();
        $no = 1;

        $trendLabels = [
            'solved' => '🟢 Membaik (Solved)',
            'recurrent' => '🔴 Kronis / Berulang',
            'new' => '🟡 Anomali Baru',
            'normal' => '✅ Selalu Normal',
            'skip' => '⚪ Tidak Dikunjungi',
        ];

        foreach ($this->stores as $item) {
            $row = [
                $no++,
                $item->distributor_name ?? '-',
                $item->salesman_code ?? '-',
                $item->salesman_name ?? '-',
                $item->custno ?? '-',
                $item->custname ?? '-',
                $item->address ?? '-',
                $item->has_master_gps ? 'Ada' : 'Kosong (0, 0)',
            ];

            foreach ($this->weeksMap as $wNum => $wInfo) {
                $wData = $item->weeks[$wNum] ?? null;
                if (!$wData || $wData->status === 'skip') {
                    $row[] = '-';
                } elseif ($wData->status === 'normal') {
                    $row[] = '✅ ' . $wData->label;
                } else {
                    $row[] = '⚠️ ' . $wData->label . ' (' . ($wData->detail ?? '-') . ')';
                }
            }

            $row[] = $trendLabels[$item->trend_status] ?? ucfirst($item->trend_status);
            $row[] = $item->total_val_order ?? 0;

            $result->push($row);
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        // Header styling
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Slate 800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Data styling
        if ($lastRow > 1) {
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Format angka total order
            $sheet->getStyle("{$lastCol}2:{$lastCol}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Zebra striping
            for ($r = 2; $r <= $lastRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ],
                    ]);
                }
            }
        }

        // Auto filter
        $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");

        return [];
    }
}
