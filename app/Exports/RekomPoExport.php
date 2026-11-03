<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekomPoExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    protected string $mode; // 'summary' or 'detail'
    protected int $year;
    protected int $month;
    protected ?string $distributorName;

    public function __construct($data, string $mode, int $year, int $month, ?string $distributorName = null)
    {
        $this->data = $data;
        $this->mode = $mode;
        $this->year = $year;
        $this->month = $month;
        $this->distributorName = $distributorName;
    }

    public function collection()
    {
        if ($this->mode === 'detail') {
            return $this->data->map(function ($row) {
                $urgensi = match ($row['urgensi_status'] ?? 'SAFE') {
                    'URGENT'   => '🔴 Kritis / Urgent',
                    'REORDER'  => '🟡 Reorder Normal',
                    'INACTIVE' => '⛔ Nonaktif',
                    'DEAD'     => '⛔ Dead SKU',
                    default    => '🟢 Stok Cukup',
                };

                return [
                    'product_id'              => $row['product_id'],
                    'product_name'            => $row['product_name'] ?? '-',
                    'stock_awal_ktn'          => (float)round($row['stock_awal_ktn'] ?? 0, 1),
                    'sellin_ktn'              => (float)round($row['sellin_ktn'] ?? 0, 1),
                    'total_stok_ketersediaan' => (float)round($row['total_stok_ketersediaan'] ?? 0, 1),
                    'sellout_ktn'             => (float)round($row['sellout_ktn'] ?? 0, 1),
                    'sisa_stok_berjalan'      => (float)round($row['sisa_stok_berjalan'] ?? 0, 1),
                    'avg_3lm_sellout'         => (float)round($row['avg_3lm_sellout'] ?? 0, 1),
                    'ratio_stok'              => (int)round($row['ratio_stok'] ?? 0) . '%',
                    'doi_days'                => ($row['doi_days'] >= 999) ? 'Macet' : ((int)round($row['doi_days'] ?? 0) . ' hr'),
                    'target_stok_120'         => (float)round($row['target_stok_120'] ?? 0, 1),
                    'rekom_po_ktn'            => (int)($row['rekom_po_ktn'] ?? 0),
                    'price_zone1'             => (float)round($row['price_zone1'] ?? 0, 2),
                    'total_rekom_po_value'    => (float)round($row['total_rekom_po_value'] ?? 0, 2),
                    'urgensi_status'          => $urgensi,
                ];
            });
        }

        // Summary Mode
        return $this->data->map(function ($row) {
            $speed = match ($row->speed_status ?? 'SLOW') {
                'FAST'   => 'Fast Moving',
                'MEDIUM' => 'Medium Moving',
                default  => 'Slow Moving',
            };

            $stock = match ($row->stock_status ?? 'SAFE') {
                'UNDER_STOCK' => 'Stok Kurang',
                'OVER_STOCK'  => 'Over Stock',
                default       => 'Stok Aman',
            };

            return [
                'region_name'             => $row->region_name ?? '-',
                'area_name'               => $row->area_name ?? '-',
                'distributor_code'        => $row->distributor_code,
                'distributor_name'        => $row->distributor_name ?? '-',
                'branch_name'             => $row->branch_name ?? '-',
                'total_stok_awal'         => (int)round($row->total_stok_awal ?? 0),
                'total_sellin'            => (int)round($row->total_sellin ?? 0),
                'total_stok_ketersediaan' => (int)round($row->total_stok_ketersediaan ?? 0),
                'total_sellout'           => (int)round($row->total_sellout ?? 0),
                'total_sisa_stok'         => (int)round($row->total_sisa_stok ?? 0),
                'total_avg_3lm'           => (int)round($row->total_avg_3lm ?? 0),
                'doi_distributor'         => ($row->doi_distributor >= 999) ? 'Macet' : ((int)round($row->doi_distributor ?? 0)),
                'speed_status'            => $speed,
                'stock_status'            => $stock,
                'count_sku_butuh_po'      => (int)($row->count_sku_butuh_po ?? 0),
                'total_rekom_po_ktn'      => (int)($row->total_rekom_po_ktn ?? 0),
                'total_rekom_po_value'    => (float)round($row->total_rekom_po_value ?? 0, 2),
            ];
        });
    }

    public function headings(): array
    {
        if ($this->mode === 'detail') {
            return [
                'Kode SKU',
                'Nama Produk',
                'Stok Awal Dist (Ktn)',
                'Sell In Pabrik (Ktn)',
                'Total Ketersediaan Stok (Ktn)',
                'Sell Out Toko (Ktn)',
                'Sisa Stok Berjalan (Ktn)',
                'AVG Sell Out 3LM (Ktn)',
                '% Stok Saat Ini',
                'DOI (Hari)',
                'Target Stok 120% (Ktn)',
                'Rekomendasi PO (Ktn)',
                'Harga Satuan (Rp)',
                'Estimasi Nilai PO (Rp)',
                'Status Urgensi',
            ];
        }

        return [
            'Region',
            'Area',
            'Kode Distributor',
            'Nama Distributor',
            'Cabang / Depo',
            'Stok Awal Dist (Ktn)',
            'Sell In Pabrik (Ktn)',
            'Total Ketersediaan Stok (Ktn)',
            'Sell Out Toko (Ktn)',
            'Total Sisa Stok (Ktn)',
            'Total AVG 3LM (Ktn)',
            'DOI Agregat (Hari)',
            'Kecepatan Penjualan',
            'Status Stok',
            'Jml SKU Butuh PO',
            'Total Rekom PO (Ktn)',
            'Estimasi Total Nilai PO (Rp)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        $isDetail = ($this->mode === 'detail');
        $lastCol = $isDetail ? 'O' : 'Q';
        $lastRow = $this->data->count() + 1;

        // Header Style
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // slate-800
            ],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        if ($lastRow > 1) {
            $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['rgb' => 'E2E8F0'],
            ]);

            if ($isDetail) {
                // Detail Mode Formatting
                // C to H: stock numbers (Awal, Sell In, Total Stok, Sell Out, Sisa Stok, AVG 3LM)
                $sheet->getStyle("C2:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.0');
                // K: Target Stok 120%
                $sheet->getStyle("K2:K{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.0');
                // L: Rekom PO (Ktn)
                $sheet->getStyle("L2:L{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                // M to N: Price & Value (Rp)
                $sheet->getStyle("M2:N{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                // Centered columns: % Stok (I), DOI (J), Urgensi (O)
                $sheet->getStyle("I2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("O2:O{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Highlight Rekom PO Column (L)
                $sheet->getStyle("L2:L{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECFDF5'], // soft emerald
                    ],
                    'font' => [
                        'bold'  => true,
                        'color' => ['rgb' => '047857'],
                    ],
                ]);

                // Highlight rows based on urgensi
                $rowIndex = 2;
                foreach ($this->data as $row) {
                    $urgensi = $row['urgensi_status'] ?? 'SAFE';
                    if ($urgensi === 'URGENT') {
                        $sheet->getStyle("O{$rowIndex}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']],
                            'font' => ['bold' => true, 'color' => ['rgb' => 'BE123C']],
                        ]);
                    } elseif ($urgensi === 'REORDER') {
                        $sheet->getStyle("O{$rowIndex}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                            'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                        ]);
                    }
                    $rowIndex++;
                }
            } else {
                // Summary Mode Formatting
                // F to K: Awal, Sell In, Total Stok, Sell Out, Sisa Stok, AVG 3LM
                $sheet->getStyle("F2:K{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                // L: DOI
                $sheet->getStyle("L2:L{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                // O to P: Count SKU & Total Rekom PO (Ktn)
                $sheet->getStyle("O2:P{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                // Q: Total Rekom Nilai PO (Rp)
                $sheet->getStyle("Q2:Q{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                // Centered columns: DOI (L), Speed (M), Stock Status (N)
                $sheet->getStyle("L2:N{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Highlight PO Columns (P & Q)
                $sheet->getStyle("P2:Q{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EEF2FF'], // soft indigo
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Highlight speed and stock health
                $rowIndex = 2;
                foreach ($this->data as $row) {
                    $stock = $row->stock_status ?? 'SAFE';
                    if ($stock === 'UNDER_STOCK') {
                        $sheet->getStyle("N{$rowIndex}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']],
                            'font' => ['bold' => true, 'color' => ['rgb' => 'BE123C']],
                        ]);
                    } elseif ($stock === 'OVER_STOCK') {
                        $sheet->getStyle("N{$rowIndex}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                            'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                        ]);
                    }
                    $rowIndex++;
                }
            }
        }

        return [];
    }
}
