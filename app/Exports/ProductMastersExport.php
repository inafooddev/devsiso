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
            'Line Name',
            'Brand Name',
            'Group Name', // (brand_unit_name)
            'Sub-Brand Name',
            'Product ID',
            'Nama Produk',
            'Status', // (is_active)
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
        ];

        // Ambil nama kategori sebagai array
        $categoryHeadings = $this->allCategories->pluck('category_name')->toArray();

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
        $baseMapping = [
            $product->line_name,
            $product->brand_name,
            $product->brand_unit_name, // Group Name
            $product->sub_brand_name,
            $product->product_id,
            $product->product_name,
            $product->is_active ? 'Aktif' : 'Tidak Aktif',
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
        ];

        // Buat lookup yang efisien untuk kategori milik produk ini
        // Ini cepat karena relasi 'categories' sudah di-eager-load di query()
        $productCategoryIds = $product->categories->pluck('category_id')->flip();

        $categoryMapping = [];
        // Loop melalui SEMUA kategori yang ada
        foreach ($this->allCategories as $category) {
            // Cek apakah produk ini memiliki kategori tersebut
            if (isset($productCategoryIds[$category->category_id])) {
                $categoryMapping[] = 'Y';
            } else {
                $categoryMapping[] = 'N';
            }
        }

        // Gabungkan data dasar dengan data kategori Y/N
        return array_merge($baseMapping, $categoryMapping);
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        // Format kolom statis (tidak perlu diubah)
        return [
            'L' => NumberFormat::FORMAT_NUMBER, // Conv 1
            'M' => NumberFormat::FORMAT_NUMBER, // Conv 2
            'N' => NumberFormat::FORMAT_NUMBER, // Conv 3
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 1
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 2
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 3
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 4
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price Zone 5
            // Kolom T, U, V, dst (kategori) tidak memerlukan format khusus (akan jadi 'Y'/'N')
        ];
    }
}
