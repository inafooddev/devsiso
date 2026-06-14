<?php

namespace App\Exports;

use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductCategoriesMapExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return ProductCategory::query()->with(['productMaster', 'category'])->latest();
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Category ID',
            'Category Name',
            'Created At',
        ];
    }

    public function map($mapping): array
    {
        return [
            $mapping->product_id,
            $mapping->productMaster ? $mapping->productMaster->product_name : '',
            $mapping->category_id,
            $mapping->category ? $mapping->category->category_name : '',
            $mapping->created_at ? $mapping->created_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
