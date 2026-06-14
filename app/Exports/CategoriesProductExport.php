<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CategoriesProductExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return Category::query()->latest('category_id');
    }

    public function headings(): array
    {
        return [
            'Category ID',
            'Category Name',
            'Created At',
            'Updated At',
        ];
    }

    public function map($category): array
    {
        return [
            $category->category_id,
            $category->category_name,
            $category->created_at ? $category->created_at->format('Y-m-d H:i:s') : '',
            $category->updated_at ? $category->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
