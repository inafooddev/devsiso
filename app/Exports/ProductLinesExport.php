<?php

namespace App\Exports;

use App\Models\ProductLine;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductLinesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return ProductLine::query()->latest('line_id');
    }

    public function headings(): array
    {
        return [
            'Line ID',
            'Line Name',
            'Created At',
            'Updated At',
        ];
    }

    public function map($line): array
    {
        return [
            $line->line_id,
            $line->line_name,
            $line->created_at ? $line->created_at->format('Y-m-d H:i:s') : '',
            $line->updated_at ? $line->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
