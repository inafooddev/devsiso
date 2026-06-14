<?php

namespace App\Imports;

use App\Models\ProductSubBrand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductSubBrandsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (!isset($row['sub_brand_id']) || !isset($row['sub_brand_name'])) {
            return null; // Skip baris jika kolom penting kosong
        }

        return ProductSubBrand::updateOrCreate(
            ['sub_brand_id' => $row['sub_brand_id']],
            ['sub_brand_name' => $row['sub_brand_name']]
        );
    }

    public function rules(): array
    {
        return [
            'sub_brand_id' => 'required',
            'sub_brand_name' => 'required',
        ];
    }
}
