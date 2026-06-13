<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MasterAreasTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            // Contoh baris data, bisa dibiarkan kosong atau dikasih contoh
            ['INA01', 'INA JABODETABEK', 'R01'],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Area',
            'Nama Area',
            'Kode Region',
        ];
    }
}
