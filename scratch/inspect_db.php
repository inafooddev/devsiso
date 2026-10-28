<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== TABLES MATCHING 'selling' OR 'stock' ===\n";
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND (table_name LIKE '%selling%' OR table_name LIKE '%stock%') ORDER BY table_name");
foreach ($tables as $t) {
    echo "- " . $t->table_name . "\n";
}

echo "\n=== COLUMNS OF selling_ins ===\n";
if (Schema::hasTable('selling_ins')) {
    $cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'selling_ins' ORDER BY ordinal_position");
    foreach ($cols as $c) {
        echo "  " . str_pad($c->column_name, 25) . " : " . $c->data_type . "\n";
    }
} else {
    echo "Table 'selling_ins' does not exist.\n";
}

echo "\n=== COLUMNS OF selling_in ===\n";
if (Schema::hasTable('selling_in')) {
    $cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'selling_in' ORDER BY ordinal_position");
    foreach ($cols as $c) {
        echo "  " . str_pad($c->column_name, 25) . " : " . $c->data_type . "\n";
    }
} else {
    echo "Table 'selling_in' does not exist.\n";
}

echo "\n=== COLUMNS OF t_sellingout ===\n";
if (Schema::hasTable('t_sellingout')) {
    $cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 't_sellingout' ORDER BY ordinal_position");
    foreach ($cols as $c) {
        echo "  " . str_pad($c->column_name, 25) . " : " . $c->data_type . "\n";
    }
} else {
    echo "Table 't_sellingout' does not exist.\n";
}
