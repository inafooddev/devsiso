<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ValidasiGeotagDetailExport extends DefaultValueBinder implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithCustomValueBinder
{
    protected array $stores;

    public function __construct(array $stores)
    {
        $this->stores = $stores;
    }

    /**
     * Memastikan kolom angka dengan leading zero (custno & kodecabang) tersimpan sebagai text murni.
     */
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && preg_match('/^0[0-9]+$/', $value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING2);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return [
            'No',
            'Region',
            'Area',
            'Kode Cabang',
            'Nama Distributor',
            'Kode Toko',
            'Nama Toko',
            'Alamat Toko',
            'Kota',
            'Tipe Outlet',
            'Grup Outlet',
            'Latitude Salesman',
            'Longitude Salesman',
            'Latitude Depo',
            'Longitude Depo',
            'Jarak ke Depo (Meter)',
            'Kategori Anomali',
            'Duplikat Toko Serupa',
            'Status Validasi',
            'Latitude Tervalidasi',
            'Longitude Tervalidasi',
            'Tanggal Validasi',
            'Verifikator',
            'Catatan Verifikator',
        ];
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->stores as $item) {
            $la = $item->la !== null ? (float) $item->la : null;
            $lg = $item->lg !== null ? (float) $item->lg : null;

            // Terjemahkan nama anomali ke bahasa Indonesia yang ramah
            $anomalyLabel = match ($item->primary_anomaly ?? '') {
                'at_distributor' => 'Di Depo Distributor (<=100m)',
                'duplicate' => 'Duplikasi Titik Lapangan',
                'point_changed' => 'Titik Bergeser (Drift)',
                'missing_coordinate' => 'Tanpa Titik Koordinat',
                default => 'Normal / Siap Validasi',
            };

            // Terjemahkan status validasi
            $valStatus = match ($item->validation_status ?? '') {
                'VALID' => 'VALID',
                'TIDAK_VALID' => 'TIDAK VALID',
                default => 'BELUM DIVALIDASI',
            };

            $distance = $item->distance_to_dist_meters !== null ? (float) $item->distance_to_dist_meters : null;

            $rows[] = [
                $no++,
                $item->region_name ?? '-',
                $item->area_name ?? '-',
                $item->kodecabang ?? '-',
                $item->distributor_name ?? '-',
                $item->custno ?? '-',
                $item->custname ?? '-',
                $item->custadd1 ?? '-',
                $item->ccity ?? '-',
                $item->typeout ?? '-',
                $item->grupout ?? '-',
                $la,
                $lg,
                $item->dist_latitude !== null ? (float) $item->dist_latitude : null,
                $item->dist_longitude !== null ? (float) $item->dist_longitude : null,
                $distance,
                $anomalyLabel,
                ($item->duplicate_count ?? 0) > 1 ? $item->duplicate_count : 0,
                $valStatus,
                $item->validated_la !== null ? (float) $item->validated_la : null,
                $item->validated_lg !== null ? (float) $item->validated_lg : null,
                $item->validated_at ? date('d/m/Y H:i', strtotime($item->validated_at)) : '-',
                $item->validator_name ?? '-',
                $item->validation_notes ?? '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->stores) + 1;

        // Header Styling: Navy blue background, white bold text, centered
        $sheet->getStyle('A1:X1')->applyFromArray([
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

        if ($lastRow > 1) {
            // Border tipis ke seluruh data
            $sheet->getStyle("A1:X{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'], // Slate 300
                    ],
                ],
            ]);

            // Alignment untuk kolom spesifik
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D2:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L2:O{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("P2:P{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("R2:S{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("T2:V{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
