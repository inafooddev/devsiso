<?php

namespace App\Imports;

use App\Models\ProductLine;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductLinesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (!isset($row['line_id']) || !isset($row['line_name'])) {
            return null; // Skip baris jika kolom penting kosong
        }

        return ProductLine::updateOrCreate(
            ['line_id' => $row['line_id']],
            ['line_name' => $row['line_name']]
        );
    }

    public function rules(): array
    {
        return [
            'line_id' => 'required',
            'line_name' => 'required',
        ];
    }
}
