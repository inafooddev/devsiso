<?php

namespace App\Exports;

use App\Models\JksImportTemp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JksImportErrorExport implements FromCollection, WithHeadings, WithMapping
{
    protected $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    public function collection()
    {
        return JksImportTemp::where('batch_id', $this->batchId)
            ->where('status', 'failed')
            ->orderBy('row_number')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Baris Excel',
            'Bulan',
            'Distributor Code',
            'Salesman Code',
            'Customer Code',
            'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu',
            'Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4',
            'ALASAN ERROR'
        ];
    }

    public function map($row): array
    {
        return [
            $row->row_number,
            $row->bulan,
            $row->distributor_code,
            $row->salesman_code,
            $row->customer_code,
            $row->h1, $row->h2, $row->h3, $row->h4, $row->h5, $row->h6, $row->h7,
            $row->w1, $row->w2, $row->w3, $row->w4,
            $row->error_message
        ];
    }
}
