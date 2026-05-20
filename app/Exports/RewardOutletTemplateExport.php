<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;

class RewardOutletTemplateExport implements FromArray, WithHeadings, WithEvents, WithColumnFormatting
{
    public function headings(): array
    {
        return [
            'Region Code',
            'Region Name',
            'Area Code',
            'Area Name',
            'Branch Name',
            'Eskalink Code',
            'Customer Code',
            'Customer Name',
            'Alamat',
            'No HP',
            'Latitude',
            'Longitude',
            'Nama Pemilik Toko',
            'Nama KTP',
            'NIK KTP',
            'Nama Bank',
            'No Rekening',
            'Nama Pemilik Norek',
            'Keterangan',
            'Status Validasi',
        ];
    }

    public function array(): array
    {
        // Menyediakan 1 baris contoh/dummy agar user paham format pengisiannya
        return [
            [
                'REG-1',
                'REGION 1',
                'AREA-01',
                'Jakarta Barat',
                'Daan Mogot',
                'ESKA-001',
                'CUST-001',
                'Toko Makmur Jaya',
                'Jl. Raya Daan Mogot No. 12, Jakarta Barat',
                "'081234567890",
                "'-6.123456",
                "'106.123456",
                'Budi Santoso',
                'Budi Santoso',
                "'3171012345678901",
                'BCA',
                "'1234567890",
                'Budi Santoso',
                'Toko buka dan ramai',
                'Valid (Toko Ada)',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // No HP
            'K' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // Latitude
            'L' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // Longitude
            'O' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // NIK KTP
            'Q' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT, // No Rekening
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                
                // Auto-fit column widths
                foreach (range('A', 'T') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Alignment
                $sheet->getStyle('A1:T2')
                      ->getAlignment()
                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
        ];
    }
}
