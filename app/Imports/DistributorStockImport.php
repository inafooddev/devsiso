<?php

namespace App\Imports;

use App\Services\SalesDiagnosticService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistributorStockImport implements ToCollection, WithHeadingRow
{
    protected int $bulan;
    protected int $tahun;
    protected ?int $uploadedBy;
    protected ?string $fileName;
    protected array $result = [];

    public function __construct(int $bulan, int $tahun, ?int $uploadedBy = null, ?string $fileName = null)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->uploadedBy = $uploadedBy;
        $this->fileName = $fileName;
    }

    public function collection(Collection $rows): void
    {
        $parsedRows = [];

        foreach ($rows as $row) {
            $distCode = null;
            $productId = null;
            $stockKtn = 0.0;
            $stockPcs = 0.0;

            foreach ($row as $key => $val) {
                $cleanKey = strtolower(trim((string)$key));
                $cleanKey = str_replace([' ', '-', '_', '.'], '', $cleanKey);

                // 1. Cek Kode Distributor
                if (in_array($cleanKey, ['kodedistributor', 'distributorcode', 'kddist', 'kodedist', 'distributor', 'kodedistribus'])) {
                    $distCode = strtoupper(trim((string)$val));
                }

                // 2. Cek Kode Produk
                if (in_array($cleanKey, ['kodeproduk', 'productid', 'kdprod', 'kditemprc', 'kodebarang', 'sku', 'itemcode', 'kditem'])) {
                    $productId = strtoupper(trim((string)$val));
                }

                // 3. Cek Stok Karton
                if (in_array($cleanKey, ['stokkarton', 'stokktn', 'stockktn', 'stok', 'stock', 'qtyktn', 'closingstock', 'stokakhir', 'stokakhirkarton', 'closingktn'])) {
                    $stockKtn = $this->parseNumeric($val);
                }

                // 4. Cek Stok Pecahan Pcs
                if (in_array($cleanKey, ['stokpcs', 'stockpcs', 'qtypcs', 'pcs', 'stokakhirpcs', 'closingpcs'])) {
                    $stockPcs = $this->parseNumeric($val);
                }
            }

            if ($distCode && $productId) {
                $parsedRows[] = [
                    'distributor_code' => $distCode,
                    'product_id'       => $productId,
                    'stock_ktn'        => $stockKtn,
                    'stock_pcs'        => $stockPcs,
                ];
            }
        }

        $service = app(SalesDiagnosticService::class);
        $this->result = $service->processBatchUpload(
            $parsedRows,
            $this->bulan,
            $this->tahun,
            $this->uploadedBy,
            $this->fileName
        );
    }

    public function getResult(): array
    {
        return $this->result;
    }

    private function parseNumeric($value): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        if (is_string($value)) {
            $clean = str_replace(',', '.', trim($value));
            $clean = preg_replace('/[^0-9.-]/', '', $clean);
            return empty($clean) ? 0.0 : (float)$clean;
        }

        return 0.0;
    }
}
