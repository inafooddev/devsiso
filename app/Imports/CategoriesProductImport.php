<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoriesProductImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (!isset($row['category_id']) || !isset($row['category_name'])) {
            return null; // Skip baris jika kolom penting kosong
        }

        return Category::updateOrCreate(
            ['category_id' => $row['category_id']],
            ['category_name' => $row['category_name']]
        );
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required',
            'category_name' => 'required',
        ];
    }
}
