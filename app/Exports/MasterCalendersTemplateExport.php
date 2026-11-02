<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterCalendersTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            ['2027-01-01', 'Jumat', 5, 1, 1, 1, 1, 1, 2027, 1],
            ['2027-01-02', 'Sabtu', 6, 1, 1, 1, 1, 1, 2027, 0],
            ['2027-01-03', 'Minggu', 7, 2, 2, 1, 1, 1, 2027, 1],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Tanggal (YYYY-MM-DD)',
            'Hari',
            'Nomor Hari (1=Senin..7=Minggu)',
            'Minggu Tahun',
            'Minggu Bulan',
            'Bulan (1-12)',
            'Kuartal (1-4)',
            'Semester (1-2)',
            'Tahun',
            'Libur Nasional (1/0)',
        ];
    }
}
