<?php

namespace App\Exports;

use App\Models\ListOutletAudit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ListOutletAuditExport extends StringValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithCustomValueBinder
{
    protected $search;

    public function __construct($search = '')
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = ListOutletAudit::query();

        if (!empty($this->search)) {
            $q = '%' . trim($this->search) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('customer_name', 'like', $q)
                    ->orWhere('customer_code', 'like', $q)
                    ->orWhere('distributor_code', 'like', $q);
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Distributor',
            'Kode Toko',
            'Nama Toko',
            'Alamat',
            'Latitude',
            'Longitude',
            'Dibuat Pada',
            'Diupdate Pada',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->distributor_code,
            $row->customer_code,
            $row->customer_name,
            $row->customer_address,
            $row->latitude,
            $row->longitude,
            $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
            $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
            'B' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            'C' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            'F' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            'G' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
        ];
    }
}
