<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$table = 'selling_out_eskalink';

echo "=== TABEL: $table ===\n\n";

if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
    echo "Tabel tidak ditemukan!\n";
    exit;
}

$columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
echo "KOLOM:\n";
foreach ($columns as $col) {
    $type = \Illuminate\Support\Facades\Schema::getColumnType($table, $col);
    echo "- $col ($type)\n";
}

echo "\nJUMLAH BARIS:\n";
echo \Illuminate\Support\Facades\DB::table($table)->count() . "\n";

echo "\nSAMPEL DATA (1 baris):\n";
$sample = \Illuminate\Support\Facades\DB::table($table)->first();
print_r($sample);
