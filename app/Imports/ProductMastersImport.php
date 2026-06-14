<?php

namespace App\Imports;

use App\Models\ProductMaster;
use App\Models\ProductLine;
use App\Models\ProductBrand;
use App\Models\ProductGroup;
use App\Models\ProductSubBrand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductMastersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['product_id']) || empty($row['product_name']) || empty($row['line_id']) || empty($row['brand_id']) || empty($row['group_id'])) {
            return null; // Skip if essential columns are missing
        }

        // Ambil nama relasi untuk disimpan ke master
        $line = ProductLine::find($row['line_id']);
        $brand = ProductBrand::find($row['brand_id']);
        $group = ProductGroup::find($row['group_id']);
        $subBrand = !empty($row['sub_brand_id']) ? ProductSubBrand::find($row['sub_brand_id']) : null;

        // Skip jika relasi utama tidak ditemukan
        if (!$line || !$brand || !$group) {
            return null;
        }

        $product = ProductMaster::updateOrCreate(
            ['product_id' => $row['product_id']],
            [
                'product_name' => $row['product_name'],
                'line_id' => $row['line_id'],
                'line_name' => $line->line_name,
                'brand_id' => $row['brand_id'],
                'brand_name' => $brand->brand_name,
                'product_group_id' => $row['group_id'],
                'brand_unit_name' => $group->brand_unit_name,
                'sub_brand_id' => $row['sub_brand_id'] ?? null,
                'sub_brand_name' => $subBrand ? $subBrand->sub_brand_name : null,
                'is_active' => isset($row['is_active_10']) ? (bool)$row['is_active_10'] : 1,
                'base_unit' => $row['base_unit'] ?? 'PCS',
                'uom1' => $row['uom_1'] ?? null,
                'uom2' => $row['uom_2'] ?? null,
                'uom3' => $row['uom_3'] ?? null,
                'conv_unit1' => $row['conv_1'] ?? null,
                'conv_unit2' => $row['conv_2'] ?? null,
                'conv_unit3' => $row['conv_3'] ?? null,
                'price_zone1' => $row['price_zone_1'] ?? 0,
                'price_zone2' => $row['price_zone_2'] ?? 0,
                'price_zone3' => $row['price_zone_3'] ?? 0,
                'price_zone4' => $row['price_zone_4'] ?? 0,
                'price_zone5' => $row['price_zone_5'] ?? 0,
            ]
        );

        // Handle Categories
        if (!empty($row['category_ids_comma_separated'])) {
            $catIds = array_map('trim', explode(',', $row['category_ids_comma_separated']));
            $product->categories()->sync($catIds);
        }

        return $product;
    }
}
