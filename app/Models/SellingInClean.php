<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellingInClean extends Model
{
    use HasFactory;

    protected $table = 'selling_ins';

    protected $guarded = []; // Allow mass assignment for all columns during generation

    protected $casts = [
        'invoice_date' => 'date',
        'qty' => 'float',
        'harga_satuan' => 'float',
        'subtotal' => 'float',
        'qty_bonus' => 'float',
        'nilai_bonus' => 'float',
        'diskon_1' => 'float',
        'diskon_2' => 'float',
        'diskon_3' => 'float',
        'dpp' => 'float',
        'ppn' => 'float',
        'total' => 'float',
        'total_idr' => 'float',
    ];
}
