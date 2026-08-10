<?php
use Illuminate\Support\Facades\Schema;
$cols = Schema::getColumns('produk_lama');
foreach($cols as $col) {
    echo $col['name'] . ' : ' . $col['type_name'] . "\n";
}
