<?php

namespace App\Exports;

use App\Models\ProductBrand;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductBrandsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return ProductBrand::query()->latest('brand_id');
    }

    public function headings(): array
    {
        return [
            'Brand ID',
            'Brand Name',
            'Created At',
            'Updated At',
        ];
    }

    public function map($brand): array
    {
        return [
            $brand->brand_id,
            $brand->brand_name,
            $brand->created_at ? $brand->created_at->format('Y-m-d H:i:s') : '',
            $brand->updated_at ? $brand->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
