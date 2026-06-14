<?php

namespace App\Exports;

use App\Models\ProductGroup;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductGroupsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return ProductGroup::query()->latest('product_group_id');
    }

    public function headings(): array
    {
        return [
            'Group ID',
            'Group Name (Brand Unit)',
            'Created At',
            'Updated At',
        ];
    }

    public function map($group): array
    {
        return [
            $group->product_group_id,
            $group->brand_unit_name,
            $group->created_at ? $group->created_at->format('Y-m-d H:i:s') : '',
            $group->updated_at ? $group->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }
}
