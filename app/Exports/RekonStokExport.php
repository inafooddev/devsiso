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

class RekonStokExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    protected string $viewMode;
    protected int $year;
    protected int $month;

    public function __construct($data, string $viewMode, int $year, int $month)
    {
        $this->data = $data;
        $this->viewMode = $viewMode;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return $this->data->map(function ($row) {
            $statusLabel = match ($row->sales_flow_status) {
                'PHANTOM_SALES' => 'Penjualan Aneh',
                'OVER_STOCK'    => 'Over Stock',
                'UNDER_STOCK'   => 'Under Stock',
                default         => 'Ideal',
            };

            $isMacetRatio = ($row->ratio_sisa_avg >= 999999 || ($row->avg_3lm_sellout <= 0 && $row->sisa_stok_berjalan > 0));
            $ratioText    = $isMacetRatio ? 'Macet' : ((int)round($row->ratio_sisa_avg) . '%');

            $isMacetDoi   = ($row->doi_days >= 999999 || ($row->avg_3lm_sellout <= 0 && $row->sisa_stok_berjalan > 0));
            $doiText      = $isMacetDoi ? 'Macet' : (($row->doi_days < 0) ? 'Minus' : (int)round($row->doi_days));

            if ($this->viewMode === 'distributor') {
                return [
                    'region_name'             => $row->region_name ?? '-',
                    'area_name'               => $row->area_name ?? '-',
                    'distributor_code'        => $row->distributor_code,
                    'distributor_name'        => $row->distributor_name,
                    'branch_name'             => $row->branch_name ?? '-',
                    'stok_awal_sistem'        => (int)round($row->stok_awal_sistem ?? 0),
                    'stok_closing_lalu'       => (int)round($row->stok_closing_lalu ?? 0),
                    'sellin_ktn'              => (int)round($row->sellin_ktn),
                    'total_stok_ketersediaan' => (int)round($row->total_stok_ketersediaan ?? 0),
                    'sellout_ktn'             => (int)round($row->sellout_ktn),
                    'sisa_stok_berjalan'      => (int)round($row->sisa_stok_berjalan ?? 0),
                    'avg_3lm_sellout'         => (int)round($row->avg_3lm_sellout ?? 0),
                    'ratio_sisa_avg'          => $ratioText,
                    'doi_days'                => $doiText,
                    'sales_flow_status'       => $statusLabel,
                ];
            }

            return [
                'distributor_code'        => $row->distributor_code,
                'distributor_name'        => $row->distributor_name ?? '-',
                'branch_name'             => $row->branch_name ?? '-',
                'product_id'              => $row->product_id,
                'product_name'            => $row->product_name ?? '-',
                'stok_awal_sistem'        => (int)round($row->stok_awal_sistem ?? 0),
                'stok_closing_lalu'       => (int)round($row->stok_closing_lalu ?? 0),
                'sellin_ktn'              => (int)round($row->sellin_ktn),
                'total_stok_ketersediaan' => (int)round($row->total_stok_ketersediaan ?? 0),
                'sellout_ktn'             => (int)round($row->sellout_ktn),
                'sisa_stok_berjalan'      => (int)round($row->sisa_stok_berjalan ?? 0),
                'avg_3lm_sellout'         => (int)round($row->avg_3lm_sellout ?? 0),
                'ratio_sisa_avg'          => $ratioText,
                'doi_days'                => $doiText,
                'sales_flow_status'       => $statusLabel,
            ];
        });
    }

    public function headings(): array
    {
        if ($this->viewMode === 'distributor') {
            return [
                'Region',
                'Area',
                'Kode Distributor',
                'Nama Distributor',
                'Cabang / Depo',
                'Stok Awal (Sistem)',
                'Awal (Dist)',
                'Sell In Bulan Ini',
                'Total Stok (Awal Dist + In)',
                'Sell Out Bulan Ini',
                'Sisa Stok',
                'AVG Sell Out',
                '% Stock',
                'DOI (Hari)',
                'Diagnosa',
            ];
        }

        return [
            'Kode Distributor',
            'Nama Distributor',
            'Cabang / Depo',
            'Kode Produk',
            'Nama Produk',
            'Stok Awal (Sistem)',
            'Awal (Dist)',
            'Sell In Bulan Ini',
            'Total Stok (Awal Dist + In)',
            'Sell Out Bulan Ini',
            'Sisa Stok',
            'AVG Sell Out',
            '% Stock',
            'DOI (Hari)',
            'Diagnosa',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        $lastCol = 'O';
        $numStartCol = 'F';
        $numEndCol = 'L';

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'],
            ],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $lastRow = $this->data->count() + 1;
        if ($lastRow > 1) {
            $sheet->getStyle("{$numStartCol}2:{$numEndCol}{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("M2:O{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['rgb' => 'E2E8F0'],
            ]);

            // Highlight fokus view untuk kolom Sisa Stok (Kolom K)
            $sheet->getStyle("K2:K{$lastRow}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EEF2FF'], // soft indigo focus
                ],
                'font' => [
                    'bold' => true,
                ],
            ]);

            // Highlight merah untuk persentase di bawah 80% & highlight kuning untuk DOI > 30 hari
            $rowIndex = 2;
            foreach ($this->data as $row) {
                $isMacet = ($row->ratio_sisa_avg >= 999999 || ($row->avg_3lm_sellout <= 0 && $row->sisa_stok_berjalan > 0));
                if (!$isMacet && $row->ratio_sisa_avg < 80) {
                    $sheet->getStyle("M{$rowIndex}")->applyFromArray([
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFE4E6'], // light rose red
                        ],
                        'font' => [
                            'bold'  => true,
                            'color' => ['rgb' => 'BE123C'], // dark rose red
                        ],
                    ]);
                }

                $isMacetDoi = ($row->doi_days >= 999999 || ($row->avg_3lm_sellout <= 0 && $row->sisa_stok_berjalan > 0));
                if (!$isMacetDoi) {
                    if ($row->doi_days > 50 || $row->doi_days < 0) {
                        $sheet->getStyle("N{$rowIndex}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFE4E6'], // light rose red
                            ],
                            'font' => [
                                'bold'  => true,
                                'color' => ['rgb' => 'BE123C'], // dark rose red
                            ],
                        ]);
                    } elseif ($row->doi_days > 30) {
                        $sheet->getStyle("N{$rowIndex}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FEF3C7'], // light amber/yellow
                            ],
                            'font' => [
                                'bold'  => true,
                                'color' => ['rgb' => '92400E'], // amber-800
                            ],
                        ]);
                    }
                }

                $rowIndex++;
            }
        }

        return [];
    }
}
