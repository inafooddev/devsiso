<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TargetPerSeUntukEskaTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * Data contoh yang ada di template.
     */
    public function array(): array
    {
        return [
            ['2026', '08', 'INAJWA1', 'INA03', 'DIBKL001', 'SEIBKL01', 'CIBKL00002', 300000],
            ['2026', '08', 'INAJWA1', 'INA03', 'DIBKL001', 'SEIBKL01', 'CIBKL00004', 6480000],
        ];
    }

    /**
     * Header kolom acuan import.
     */
    public function headings(): array
    {
        return [
            'tahun',
            'bulan',
            'region',
            'branch',
            'sellingpoint',
            'salesman',
            'outlet',
            'value',
        ];
    }
}
