<?php

namespace App\Imports;

use App\Models\ProductBrand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductBrandsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (!isset($row['brand_id']) || !isset($row['brand_name'])) {
            return null; // Skip baris jika kolom penting kosong
        }

        return ProductBrand::updateOrCreate(
            ['brand_id' => $row['brand_id']],
            ['brand_name' => $row['brand_name']]
        );
    }

    public function rules(): array
    {
        return [
            'brand_id' => 'required',
            'brand_name' => 'required',
        ];
    }
}
