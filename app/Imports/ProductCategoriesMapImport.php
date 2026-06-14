<?php

namespace App\Imports;

use App\Models\ProductCategory;
use App\Models\ProductMaster;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductCategoriesMapImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        if (empty($row['product_id']) || empty($row['category_id'])) {
            return null;
        }

        // Validasi relasi
        $product = ProductMaster::find($row['product_id']);
        $category = Category::find($row['category_id']);

        if (!$product || !$category) {
            return null;
        }

        return ProductCategory::updateOrCreate(
            [
                'product_id' => $row['product_id'],
                'category_id' => $row['category_id']
            ]
        );
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required',
            'category_id' => 'required',
        ];
    }
}
