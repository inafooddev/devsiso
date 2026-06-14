<?php

namespace App\Exports;

use App\Models\ProductSubBrand;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductSubBrandsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return ProductSubBrand::query()->latest('sub_brand_id');
    }

    public function headings(): array
    {
        return [
            'Sub-Brand ID',
            'Sub-Brand Name',
            'Created At',
            'Updated At',
        ];
    }

    public function map($subBrand): array
    {
        return [
            $subBrand->sub_brand_id,
            $subBrand->sub_brand_name,
            $subBrand->created_at ? $subBrand->created_at->format('Y-m-d H:i:s') : '',
            $subBrand->updated_at ? $subBrand->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
