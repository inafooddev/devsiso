<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekonStokTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?string $distributorCode;

    public function __construct(?string $distributorCode = null)
    {
        $this->distributorCode = $distributorCode;
    }

    public function collection()
    {
        // Jika ada distributor tertentu yang dipilih, kita siapkan daftar SKU aktif milik distributor tsb
        if (!empty($this->distributorCode)) {
            $dist = DB::table('master_distributors')
                ->where('distributor_code', $this->distributorCode)
                ->first();

            $products = DB::table('product_masters')
                ->where('is_active', true)
                ->orderBy('brand_name')
                ->orderBy('product_id')
                ->get();

            return $products->map(function ($p) use ($dist) {
                return [
                    'kode_distributor' => $dist->distributor_code ?? $this->distributorCode,
                    'nama_distributor' => $dist->distributor_name ?? '',
                    'kode_produk'      => $p->product_id,
                    'nama_produk'      => $p->product_name,
                    'stok_akhir_ktn'   => '',
                    'stok_akhir_pcs'   => '',
                ];
            });
        }

        // Default: Contoh baris pengisian template
        return collect([
            [
                'kode_distributor' => 'DIST001',
                'nama_distributor' => 'CONTOH NAMA DISTRIBUTOR',
                'kode_produk'      => 'PROD-A001',
                'nama_produk'      => 'CONTOH NAMA PRODUK A',
                'stok_akhir_ktn'   => 150,
                'stok_akhir_pcs'   => 0,
            ],
            [
                'kode_distributor' => 'DIST001',
                'nama_distributor' => 'CONTOH NAMA DISTRIBUTOR',
                'kode_produk'      => 'PROD-A002',
                'nama_produk'      => 'CONTOH NAMA PRODUK B',
                'stok_akhir_ktn'   => 45,
                'stok_akhir_pcs'   => 12,
            ],
            [
                'kode_distributor' => 'DIST002',
                'nama_distributor' => 'DISTRIBUTOR LAIN',
                'kode_produk'      => 'PROD-B001',
                'nama_produk'      => 'CONTOH NAMA PRODUK C',
                'stok_akhir_ktn'   => 300,
                'stok_akhir_pcs'   => 0,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Kode Distributor',
            'Nama Distributor (Opsional)',
            'Kode Produk',
            'Nama Produk (Opsional)',
            'Stok Akhir Karton',
            'Stok Akhir Pcs',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        // Header Style (Primary Emerald/Teal look)
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'], // Teal-700
            ],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('E2:F100')->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }
}
