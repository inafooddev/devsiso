<?php

namespace App\Imports;

use App\Models\ProductGroup;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductGroupsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (!isset($row['group_id']) || !isset($row['group_name_brand_unit'])) {
            return null; // Skip baris jika kolom penting kosong
        }

        return ProductGroup::updateOrCreate(
            ['product_group_id' => $row['group_id']],
            ['brand_unit_name' => $row['group_name_brand_unit']]
        );
    }

    public function rules(): array
    {
        return [
            'group_id' => 'required',
            'group_name_brand_unit' => 'required',
        ];
    }
}
