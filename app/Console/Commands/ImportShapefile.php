<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Shapefile\ShapefileReader;
use Shapefile\Shapefile;
use Illuminate\Support\Facades\DB;

class ImportShapefile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shapefile {path? : Path to the shapefile} {--provinsi= : Filter by province name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import large shapefile to batas_wilayah_kelurahan table via streaming';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path') ?? storage_path('app/private/spatial_data/Batas_Wilayah_KelurahanDesa_10K_AR.shp');
        $provinsiFilter = $this->option('provinsi');

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return;
        }

        $this->info("Membuka Shapefile: {$path}");
        if ($provinsiFilter) {
            $this->info("Filter aktif untuk Provinsi: {$provinsiFilter}");
        }

        try {
            $options = [
                Shapefile::OPTION_ENFORCE_POLYGON_CLOSED_RINGS => false,
            ];
            $shapefile = new ShapefileReader($path, $options);
            
            $totalRecords = $shapefile->getTotRecords();
            $this->info("Total Records di Shapefile: {$totalRecords}");

            $bar = $this->output->createProgressBar($totalRecords);
            $bar->start();

            $batch = [];
            $batchSize = 500;
            $inserted = 0;
            $skipped = 0;

            // Kita harus menonaktifkan statement preparation strictness untuk PostGIS EWKT kadang-kadang, 
            // tapi raw query insert lebih aman dengan binding.
            
            while ($geometry = $shapefile->fetchRecord()) {
                $bar->advance();

                if ($geometry->isDeleted()) {
                    continue;
                }

                $data = $geometry->getDataArray();
                $wkt = $geometry->getWKT();

                $prov = $data['WADMPR'] ?? null;
                
                if ($provinsiFilter && stripos($prov, $provinsiFilter) === false) {
                    $skipped++;
                    continue;
                }

                // Beberapa WKT mungkin valid tapi kalau tidak ada, skip
                if (!$wkt) {
                    continue;
                }
                
                // Pastikan geometri adalah MultiPolygon. Jika Polygon, kita force bungkus ke MultiPolygon (jika perlu)
                // WKT dari php-shapefile biasanya sesuai dengan tipe data shapefile aslinya.
                
                $batch[] = [
                    'provinsi' => $prov,
                    'kabupaten' => $data['WADMKK'] ?? null,
                    'kecamatan' => $data['WADMKC'] ?? null,
                    'kelurahan' => $data['WADMKD'] ?? null,
                    'wkt' => $wkt
                ];

                if (count($batch) >= $batchSize) {
                    $this->insertBatch($batch);
                    $inserted += count($batch);
                    $batch = [];
                }
            }

            if (count($batch) > 0) {
                $this->insertBatch($batch);
                $inserted += count($batch);
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("Selesai! Berhasil insert: {$inserted} records, Skipped: {$skipped} records.");

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    private function insertBatch($batch)
    {
        DB::beginTransaction();
        try {
            foreach ($batch as $item) {
                // Konversi Polygon ke MultiPolygon jika perlu di sisi SQL,
                // ST_Multi akan aman digunakan baik untuk Polygon maupun MultiPolygon
                DB::insert("
                    INSERT INTO batas_wilayah_kelurahan 
                    (provinsi, kabupaten, kecamatan, kelurahan, geom, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ST_Multi(ST_GeomFromText(?, 4326)), NOW(), NOW())
                ", [
                    $item['provinsi'],
                    $item['kabupaten'],
                    $item['kecamatan'],
                    $item['kelurahan'],
                    $item['wkt']
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
