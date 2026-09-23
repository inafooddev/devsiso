<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ArStockTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        // Ambil distributor aktif saja
        $distributors = DB::table('master_distributors')
            ->where('is_active', true)
            ->select('region_name', 'area_name', 'distributor_code', 'distributor_name', 'branch_name')
            ->orderBy('region_name')
            ->orderBy('area_name')
            ->orderBy('branch_name')
            ->orderBy('distributor_name')
            ->get();

        return $distributors->map(function ($distributor) {
            return [
                'region_name' => $distributor->region_name,
                'area_name' => $distributor->area_name,
                'distributor_code' => $distributor->distributor_code,
                'distributor_name' => $distributor->distributor_name,
                'branch_name' => $distributor->branch_name,
                'ar' => '',    // Dikosongkan agar diisi user
                'stock' => '', // Dikosongkan agar diisi user
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Region Name',
            'Area Name',
            'Distributor Code',
            'Distributor Name',
            'Branch Name',
            'AR (Late > 7)',
            'Stock (%)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Style untuk baris header
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'] // Dark Gray (mirip tema aplikasi)
            ],
        ]);

        // Style kolom master data (Read-Only Info)
        $sheet->getStyle('A2:E' . $highestRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF3F4F6'] // Light Gray
            ]
        ]);

        // Highlight kuning untuk kolom inputan agar user tahu mana yang harus diisi
        $sheet->getStyle('F2:G' . $highestRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFBEB'] // Amber 50
            ]
        ]);

        // Border all
        $sheet->getStyle('A1:G' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
        ]);

        return [];
    }
}
