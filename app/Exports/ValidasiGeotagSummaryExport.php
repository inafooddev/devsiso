<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ValidasiGeotagSummaryExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected array $matrix;
    protected string $groupBy;

    public function __construct(array $matrix, string $groupBy = 'distributor')
    {
        $this->matrix = $matrix;
        $this->groupBy = $groupBy;
    }

    public function headings(): array
    {
        if ($this->groupBy === 'region') {
            return [
                'No',
                'Region',
                'Total Toko',
                'Ada Titik',
                'Tanpa Titik',
                'Di Depo (<=100m)',
                'Duplikat Lapangan',
                'Drift (Bergeser)',
                'Siap Validasi',
                'VALID',
                'TIDAK VALID',
                'Belum Validasi',
                'Progress Validasi (%)',
            ];
        }

        if ($this->groupBy === 'area') {
            return [
                'No',
                'Region',
                'Area',
                'Total Toko',
                'Ada Titik',
                'Tanpa Titik',
                'Di Depo (<=100m)',
                'Duplikat Lapangan',
                'Drift (Bergeser)',
                'Siap Validasi',
                'VALID',
                'TIDAK VALID',
                'Belum Validasi',
                'Progress Validasi (%)',
            ];
        }

        return [
            'No',
            'Region',
            'Area',
            'Kode Cabang',
            'Nama Distributor',
            'Total Toko',
            'Ada Titik',
            'Tanpa Titik',
            'Di Depo (<=100m)',
            'Duplikat Lapangan',
            'Drift (Bergeser)',
            'Siap Validasi',
            'VALID',
            'TIDAK VALID',
            'Belum Validasi',
            'Progress Validasi (%)',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        $totStores = 0;
        $totHasCoord = 0;
        $totNoCoord = 0;
        $totAtDist = 0;
        $totDup = 0;
        $totDrift = 0;
        $totNormal = 0;
        $totValid = 0;
        $totInvalid = 0;
        $totUnvalidated = 0;

        foreach ($this->matrix as $item) {
            $totStores += (int) $item->total_stores;
            $totHasCoord += (int) $item->has_coordinate;
            $totNoCoord += (int) $item->no_coordinate;
            $totAtDist += (int) $item->at_distributor;
            $totDup += (int) $item->duplicate_stores;
            $totDrift += (int) $item->drift_stores;
            $totNormal += (int) $item->normal_stores;
            $totValid += (int) $item->valid_stores;
            $totInvalid += (int) $item->invalid_stores;
            $totUnvalidated += (int) $item->unvalidated_stores;

            $pct = (float) $item->validation_progress_pct;

            if ($this->groupBy === 'region') {
                $rows[] = [
                    $no++,
                    $item->region_name ?? '-',
                    (int) $item->total_stores,
                    (int) $item->has_coordinate,
                    (int) $item->no_coordinate,
                    (int) $item->at_distributor,
                    (int) $item->duplicate_stores,
                    (int) $item->drift_stores,
                    (int) $item->normal_stores,
                    (int) $item->valid_stores,
                    (int) $item->invalid_stores,
                    (int) $item->unvalidated_stores,
                    round($pct, 1) . '%',
                ];
            } elseif ($this->groupBy === 'area') {
                $rows[] = [
                    $no++,
                    $item->region_name ?? '-',
                    $item->area_name ?? '-',
                    (int) $item->total_stores,
                    (int) $item->has_coordinate,
                    (int) $item->no_coordinate,
                    (int) $item->at_distributor,
                    (int) $item->duplicate_stores,
                    (int) $item->drift_stores,
                    (int) $item->normal_stores,
                    (int) $item->valid_stores,
                    (int) $item->invalid_stores,
                    (int) $item->unvalidated_stores,
                    round($pct, 1) . '%',
                ];
            } else {
                $rows[] = [
                    $no++,
                    $item->region_name ?? '-',
                    $item->area_name ?? '-',
                    $item->kodecabang ?? '-',
                    $item->distributor_name ?? '-',
                    (int) $item->total_stores,
                    (int) $item->has_coordinate,
                    (int) $item->no_coordinate,
                    (int) $item->at_distributor,
                    (int) $item->duplicate_stores,
                    (int) $item->drift_stores,
                    (int) $item->normal_stores,
                    (int) $item->valid_stores,
                    (int) $item->invalid_stores,
                    (int) $item->unvalidated_stores,
                    round($pct, 1) . '%',
                ];
            }
        }

        // Baris Total Keseluruhan
        $totValidated = $totValid + $totInvalid;
        $totalProgressPct = $totStores > 0 ? round(($totValidated / $totStores) * 100, 1) : 0;

        if ($this->groupBy === 'region') {
            $rows[] = [
                'TOTAL',
                'TOTAL KESELURUHAN (' . count($this->matrix) . ' Region)',
                $totStores,
                $totHasCoord,
                $totNoCoord,
                $totAtDist,
                $totDup,
                $totDrift,
                $totNormal,
                $totValid,
                $totInvalid,
                $totUnvalidated,
                $totalProgressPct . '%',
            ];
        } elseif ($this->groupBy === 'area') {
            $rows[] = [
                'TOTAL',
                'TOTAL KESELURUHAN',
                count($this->matrix) . ' Area',
                $totStores,
                $totHasCoord,
                $totNoCoord,
                $totAtDist,
                $totDup,
                $totDrift,
                $totNormal,
                $totValid,
                $totInvalid,
                $totUnvalidated,
                $totalProgressPct . '%',
            ];
        } else {
            $rows[] = [
                'TOTAL',
                'TOTAL KESELURUHAN',
                '',
                count($this->matrix) . ' Cabang',
                '',
                $totStores,
                $totHasCoord,
                $totNoCoord,
                $totAtDist,
                $totDup,
                $totDrift,
                $totNormal,
                $totValid,
                $totInvalid,
                $totUnvalidated,
                $totalProgressPct . '%',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->groupBy === 'region' ? 'M' : ($this->groupBy === 'area' ? 'N' : 'P');
        $lastRow = count($this->matrix) + 2; // Headings (1) + data (N) + Total row (1)

        // Header Styling: Navy blue background, white bold text, centered
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Slate 800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        // Border tipis ke seluruh tabel
        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        // Alignment kolom No
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Baris Total Styling
        $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F1F5F9'], // Slate 100
            ],
        ]);

        return [];
    }
}
