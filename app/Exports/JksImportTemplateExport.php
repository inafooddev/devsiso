<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class JksImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            // Baris ke-2: Keterangan (Wajib vs Opsional)
            [
                '(Wajib) Format YYYY-MM-DD', '(Opsional)', '(Opsional)', '(Opsional)', 
                '(Wajib) Kode Distributor', '(Opsional)', 
                '(Wajib) Kode Sales', '(Opsional)', 
                '(Wajib) Kode Toko', '(Opsional)', '(Opsional)', '(Opsional)', '(Opsional)', '(Opsional)', 
                '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', 
                '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', '(Wajib) Isi Y/T', '(Wajib) Isi Y/T'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Bulan', 'Region Name', 'Area Name', 'Supervisor', 'Distributor Code', 'Distributor Name', 'Salesman Code', 'Salesman Name', 'Customer Code', 'Eskalink Code', 'Customer Name', 'Address', 'Kecamatan', 'Desa', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu', 'Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header Utama (Baris 1)
        $sheet->getStyle('A1:Y1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937'], // Dark Gray
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Styling Header Keterangan (Baris 2)
        $sheet->getStyle('A2:Y2')->applyFromArray([
            'font' => [
                'italic' => true,
                'color' => ['argb' => 'FF4B5563'], // Text Gray
                'size' => 9
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF3F4F6'], // Light Gray
            ],
        ]);

        // Highlight Kolom Wajib (Bulan, Dist Code, Sales Code, Cust Code, Hari, Minggu) di Baris 1 & 2
        $wajibColumns = ['A', 'E', 'G', 'I', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];
        foreach ($wajibColumns as $col) {
            $sheet->getStyle($col . '1')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDC2626'], // Red / Merah (Wajib)
                ]
            ]);
            $sheet->getStyle($col . '2')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFDC2626'], // Red Text
                ]
            ]);
        }

        // Freeze baris 1 & 2 agar saat di-scroll ke bawah tetap terlihat
        $sheet->freezePane('A3');

        return [];
    }
}
