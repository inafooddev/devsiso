<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankGaransiTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'Kode Distributor',
            'Nama Distributor',
            'Nama Bank',
            'Nomor Jaminan',
            'Nomor Seri',
            'Nilai Jaminan',
            'Tanggal Terbit',
            'Tanggal Jatuh Tempo',
            'Status Perpanjangan',
            'Keterangan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
