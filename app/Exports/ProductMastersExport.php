<?php

namespace App\Exports;

use App\Models\ProductMaster;
use App\Models\Category; // [DITAMBAHKAN] Import model Category
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection; // [DITAMBAHKAN]

class ProductMastersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    protected $allCategories; // [DITAMBAHKAN] Properti untuk menyimpan semua kategori

    /**
     * [DITAMBAHKAN] Constructor untuk mengambil semua kategori
     */
    public function __construct()
    {
        // Ambil semua kategori sekali saja untuk digunakan di headings() dan map()
        $this->allCategories = Category::orderBy('category_name')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        // [PERUBAHAN] Eager load relasi categories untuk optimasi N+1 query
        return ProductMaster::query()
            ->with('categories')
            ->latest('product_id');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // [PERUBAHAN] Gabungkan heading statis dengan nama-nama kategori
        $baseHeadings = [
            'line_id',
            'Line Name',
            'brand_id',
            'Brand Name',
            'group_id',
            'Group Name',
            'sub_brand_id',
            'Sub-Brand Name',
            'Product ID',
            'Nama Produk',
            'Status',
            'Base Unit',
            'UOM 1',
            'UOM 2',
            'UOM 3',
            'Conv 1',
            'Conv 2',
            'Conv 3',
            'Price Zone 1',
            'Price Zone 2',
            'Price Zone 3',
            'Price Zone 4',
            'Price Zone 5',
            'NPD',
            'TOP ITEM',
            'VTKP',
            'category_ids_comma_separated',
        ];

        // Ambil nama kategori sebagai array (kecualikan yang sudah di-hardcode)
        $categoryHeadings = $this->allCategories
            ->whereNotIn('category_name', ['NPD', 'TOP ITEM', 'VTKP'])
            ->pluck('category_name')
            ->toArray();

        // Gabungkan kedua array
        return array_merge($baseHeadings, $categoryHeadings);
    }

    /**
     * @param ProductMaster $product
     * @return array
     */
    public function map($product): array
    {
        // [PERUBAHAN] Tambahkan logika 'Y' / 'N' untuk setiap kategori
        $productCategoryNames = $product->categories->pluck('category_name')->toArray();
        $productCategoryIds = $product->categories->pluck('category_id')->flip();

        $baseMapping = [
            $product->line_id,
            $product->line_name,
            $product->brand_id,
            $product->brand_name,
            $product->product_group_id,
            $product->brand_unit_name,
            $product->sub_brand_id,
            $product->sub_brand_name,
            $product->product_id,
            $product->product_name,
            $product->is_active ? 1 : 0, // Menggunakan 1/0 agar sesuai import
            $product->base_unit,
            $product->uom1,
            $product->uom2,
            $product->uom3,
            $product->conv_unit1,
            $product->conv_unit2,
            $product->conv_unit3,
            $product->price_zone1,
            $product->price_zone2,
            $product->price_zone3,
            $product->price_zone4,
            $product->price_zone5,
            in_array('NPD', $productCategoryNames) ? 'Y' : 'N',
            in_array('TOP ITEM', $productCategoryNames) ? 'Y' : 'N',
            in_array('VTKP', $productCategoryNames) ? 'Y' : 'N',
            $product->categories->pluck('category_id')->implode(','),
        ];

        $categoryMapping = [];
        // Loop melalui SEMUA kategori yang ada (kecuali yang di-hardcode)
        foreach ($this->allCategories->whereNotIn('category_name', ['NPD', 'TOP ITEM', 'VTKP']) as $category) {
            if (isset($productCategoryIds[$category->category_id])) {
                $categoryMapping[] = 'Y';
            } else {
                $categoryMapping[] = 'N';
            }
        }

        return array_merge($baseMapping, $categoryMapping);
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        // Format kolom statis (tidak perlu diubah)
        return [
            'P' => NumberFormat::FORMAT_NUMBER, // Conv 1
            'Q' => NumberFormat::FORMAT_NUMBER, // Conv 2
            'R' => NumberFormat::FORMAT_NUMBER, // Conv 3
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 1
            'T' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 2
            'U' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 3
            'V' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 4
            'W' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 5
            // Kolom X dst tidak memerlukan format khusus
        ];
    }
}
